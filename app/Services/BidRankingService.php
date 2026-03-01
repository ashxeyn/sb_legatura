<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Bid Ranking Service — Weighted Scoring System
 *
 * Ranks bids using a quality-first approach:
 *   BID_SCORE = Σ (component_score × weight)
 *
 * Components:
 *   1. Cost Score          (20%) — relative to lowest bid in the project
 *   2. Experience Score    (10%) — years of experience, capped at 20
 *   3. Review Score        (15%) — avg rating × log-scaled review count
 *   4. Completed Projects  (10%) — logarithmic scaling
 *   5. Verification Score  (15%) — approved / pending / rejected
 *   6. License Score       (10%) — PCAB category + expiration check
 *   7. Timeline Score      ( 5%) — relative to shortest reasonable timeline
 *   8. Subscription Boost  (10%) — capped additive (silver/gold from platform_payments)
 *   9. Early Bird Score    ( 5%) — rewards earlier bid submissions
 *
 * All component scores are normalised to 0.0 – 1.0 before weighting.
 * Final bid_score range: 0.0 – 1.0 (displayed as 0 – 100 when needed).
 *
 * Design principles:
 *   - Quality-first: subscription boosts visibility, not credibility
 *   - No pay-to-win: a poor Gold contractor cannot outrank a strong Free one
 *   - Modular: each scorer is a standalone method, easy to extend
 *   - Efficient: batch-loads review aggregates and subscription data per project
 */
class bidRankingService
{
    // ─── Configurable weights (must sum to 1.0) ────────────────────────

    private const WEIGHTS = [
        'cost'               => 0.20,
        'experience'         => 0.10,
        'review'             => 0.15,
        'completed_projects' => 0.10,
        'verification'       => 0.15,
        'license'            => 0.10,
        'timeline'           => 0.05,
        'subscription'       => 0.10,
        'early_bird'         => 0.05,
    ];

    // ─── Verification status scores ────────────────────────────────────

    private const VERIFICATION_SCORES = [
        'approved' => 1.0,
        'pending'  => 0.3,
        'rejected' => 0.0,
        'deleted'  => 0.0,
    ];

    // ─── PCAB licence category scores ──────────────────────────────────

    private const LICENSE_SCORES = [
        'AAAA'    => 1.0,
        'AAA'     => 0.9,
        'AA'      => 0.8,
        'A'       => 0.7,
        'B'       => 0.6,
        'C'       => 0.5,
        'D'       => 0.4,
        'Trade/E' => 0.3,
    ];

    // ─── Subscription tier additive bonus ──────────────────────────────

    private const SUBSCRIPTION_BONUS = [
        'gold'   => 0.10,
        'silver' => 0.05,
        'free'   => 0.00,
    ];

    // ─── Tunable caps / thresholds ─────────────────────────────────────

    /** Max years of experience that earn full score */
    private const MAX_EXPERIENCE_YEARS = 20;

    /** Minimum review count for full review confidence */
    private const MIN_REVIEW_COUNT_FULL = 10;

    /** Max log-scaled completed projects (log10(100) ≈ 2.0) */
    private const MAX_COMPLETED_LOG = 2.0;

    /** Cost ratio floor – prevents extremely cheap bids from scoring > 1.0 */
    private const COST_RATIO_CAP = 1.0;

    // ═══════════════════════════════════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Rank all bids for a project.
     *
     * @param  int              $projectId
     * @param  Collection|null  $bids  Pre-fetched bids (optional — will fetch if null)
     * @return Collection       Bids sorted by bid_score DESC, each decorated with
     *                          `ranking_score`, `bid_rank`, and `score_breakdown`.
     */
    public function rankBids(int $projectId, ?Collection $bids = null): Collection
    {
        if ($bids === null) {
            $bids = $this->fetchProjectBids($projectId);
        }

        if ($bids->isEmpty()) {
            return $bids;
        }

        // ── Pre-compute project-wide aggregates ────────────────────────
        $lowestCost       = $bids->min('proposed_cost');
        $shortestTimeline = $bids->min('estimated_timeline');

        // Earliest and latest submission timestamps for early-bird scoring
        $earliestSubmitted = $bids->min('submitted_at');
        $latestSubmitted   = $bids->max('submitted_at');

        // Batch-load contractor IDs for review + subscription look-ups
        $contractorIds = $bids->pluck('contractor_id')->unique()->values()->all();

        $reviewAggregates  = $this->batchLoadReviewAggregates($contractorIds);
        $subscriptionTiers = $this->batchLoadSubscriptionTiers($contractorIds);
        $contractorDetails = $this->batchLoadContractorDetails($contractorIds);

        // ── Score each bid ─────────────────────────────────────────────
        foreach ($bids as $bid) {
            $cid = $bid->contractor_id;

            $contractor = $contractorDetails[$cid] ?? null;
            $reviewData = $reviewAggregates[$cid]  ?? ['avg_rating' => 0, 'review_count' => 0];
            $subTier    = $subscriptionTiers[$cid]  ?? 'free';

            // Individual component scores (each 0.0 – 1.0)
            $costScore       = $this->scoreCost($bid->proposed_cost, $lowestCost);
            $experienceScore = $this->scoreExperience(
                $contractor->years_of_experience ?? ($bid->years_of_experience ?? 0)
            );
            $reviewScore     = $this->scoreReviews($reviewData['avg_rating'], $reviewData['review_count']);
            $completedScore  = $this->scoreCompletedProjects(
                $contractor->completed_projects ?? ($bid->completed_projects ?? 0)
            );
            $verifyScore     = $this->scoreVerification(
                $contractor->verification_status ?? 'pending'
            );
            $licenseScore    = $this->scoreLicense(
                $contractor->picab_category ?? ($bid->picab_category ?? null),
                $contractor->picab_expiration_date ?? null
            );
            $timelineScore   = $this->scoreTimeline($bid->estimated_timeline, $shortestTimeline);
            $subScore        = self::SUBSCRIPTION_BONUS[$subTier] ?? 0.0;
            $earlyBirdScore  = $this->scoreEarlyBird(
                $bid->submitted_at ?? null, $earliestSubmitted, $latestSubmitted
            );

            // Weighted total
            $totalScore =
                ($costScore       * self::WEIGHTS['cost']) +
                ($experienceScore * self::WEIGHTS['experience']) +
                ($reviewScore     * self::WEIGHTS['review']) +
                ($completedScore  * self::WEIGHTS['completed_projects']) +
                ($verifyScore     * self::WEIGHTS['verification']) +
                ($licenseScore    * self::WEIGHTS['license']) +
                ($timelineScore   * self::WEIGHTS['timeline']) +
                ($subScore        * self::WEIGHTS['subscription']) +
                ($earlyBirdScore  * self::WEIGHTS['early_bird']);

            $bid->ranking_score = round($totalScore * 100, 2); // 0–100 scale

            $bid->score_breakdown = [
                'cost'               => round($costScore * 100, 2),
                'experience'         => round($experienceScore * 100, 2),
                'review'             => round($reviewScore * 100, 2),
                'completed_projects' => round($completedScore * 100, 2),
                'verification'       => round($verifyScore * 100, 2),
                'license'            => round($licenseScore * 100, 2),
                'timeline'           => round($timelineScore * 100, 2),
                'early_bird'         => round($earlyBirdScore * 100, 2),
                'subscription'       => round($subScore * 100, 2),
                'subscription_tier'  => $subTier,
            ];
        }

        // Sort highest score first, then assign 1-based rank
        $ranked = $bids->sortByDesc('ranking_score')->values();
        $ranked->each(function ($bid, $index) {
            $bid->bid_rank = $index + 1;
        });

        return $ranked;
    }

    // ═══════════════════════════════════════════════════════════════════
    // INDIVIDUAL SCORING METHODS  (all return 0.0 – 1.0)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * 1. Cost Score (20%)
     * Relative to the lowest bid in the project.
     * cost_score = lowest_bid / this_bid, capped at 1.0
     */
    private function scoreCost(float $proposedCost, float $lowestCost): float
    {
        if ($proposedCost <= 0) {
            return 0.0;
        }
        return min(self::COST_RATIO_CAP, $lowestCost / $proposedCost);
    }

    /**
     * 2. Experience Score (10%)
     * experience_score = min(years / MAX_YEARS, 1.0)
     */
    private function scoreExperience(int $years): float
    {
        if ($years <= 0) {
            return 0.0;
        }
        return min(1.0, $years / self::MAX_EXPERIENCE_YEARS);
    }

    /**
     * 3. Review Score (15%)
     * Combines average rating with review-count confidence.
     *
     * raw = (avg_rating / 5) × confidence_factor
     * confidence_factor = log2(count + 1) / log2(MIN_COUNT + 1)  capped at 1.0
     *
     * A single 5-star review  → ~0.29  (not dominant)
     * 10+ reviews at 5 stars  → 1.0
     * No reviews              → 0.40 (neutral baseline so newcomers aren't buried)
     */
    private function scoreReviews(float $avgRating, int $reviewCount): float
    {
        if ($reviewCount === 0) {
            return 0.40; // neutral baseline for new contractors
        }

        $ratingNorm = $avgRating / 5.0;
        $confidence = min(
            1.0,
            log($reviewCount + 1, 2) / log(self::MIN_REVIEW_COUNT_FULL + 1, 2)
        );

        return $ratingNorm * $confidence;
    }

    /**
     * 4. Completed Projects Score (10%)
     * Logarithmic scaling: log10(n + 1) / MAX_COMPLETED_LOG, capped at 1.0
     */
    private function scoreCompletedProjects(int $count): float
    {
        if ($count <= 0) {
            return 0.0;
        }
        return min(1.0, log10($count + 1) / self::MAX_COMPLETED_LOG);
    }

    /**
     * 5. Verification Score (15%)
     * Direct mapping from verification_status.
     */
    private function scoreVerification(string $status): float
    {
        return self::VERIFICATION_SCORES[strtolower($status)] ?? 0.0;
    }

    /**
     * 6. License / PCAB Score (10%)
     * Returns 0 if licence is expired.
     * Otherwise maps category to tier score.
     */
    private function scoreLicense(?string $category, ?string $expirationDate): float
    {
        if (!$category) {
            return 0.0;
        }

        // Expired licence → zero
        if ($expirationDate && strtotime($expirationDate) < time()) {
            return 0.0;
        }

        return self::LICENSE_SCORES[$category] ?? 0.3;
    }

    /**
     * 7. Timeline Score (5%)
     * Relative to the shortest timeline among bids.
     * timeline_score = shortest / this_timeline, capped at 1.0
     */
    private function scoreTimeline(int $days, int $shortestDays): float
    {
        if ($days <= 0) {
            return 0.0;
        }
        if ($shortestDays <= 0) {
            return 1.0;
        }
        return min(1.0, $shortestDays / $days);
    }

    /**
     * 8. Early Bird Score (5%)
     * Rewards contractors who submitted their bid earlier.
     * The first bid gets 1.0, the last gets 0.0.
     * If all bids were submitted at the same time, everyone gets 1.0.
     */
    private function scoreEarlyBird(?string $submittedAt, ?string $earliest, ?string $latest): float
    {
        if (!$submittedAt || !$earliest || !$latest) {
            return 0.5; // neutral if timestamps are missing
        }

        $earliestTs = strtotime($earliest);
        $latestTs   = strtotime($latest);
        $thisTs     = strtotime($submittedAt);

        // All bids submitted at the same time → everyone gets full score
        if ($latestTs <= $earliestTs) {
            return 1.0;
        }

        // Linear interpolation: earliest = 1.0, latest = 0.0
        return 1.0 - (($thisTs - $earliestTs) / ($latestTs - $earliestTs));
    }

    // ═══════════════════════════════════════════════════════════════════
    // BATCH DATA LOADERS  (minimise N+1 queries)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Load average rating + review count for a set of contractors in one query.
     *
     * @return array<int, array{avg_rating: float, review_count: int}>
     */
    private function batchLoadReviewAggregates(array $contractorIds): array
    {
        if (empty($contractorIds)) {
            return [];
        }

        // Map contractor_id → user_id
        $contractors = DB::table('contractors')
            ->whereIn('contractor_id', $contractorIds)
            ->select('contractor_id', 'user_id')
            ->get()
            ->keyBy('contractor_id');

        $userIds = $contractors->pluck('user_id')->unique()->values()->all();
        if (empty($userIds)) {
            return [];
        }

        $reviews = DB::table('reviews')
            ->whereIn('reviewee_user_id', $userIds)
            ->select('reviewee_user_id')
            ->selectRaw('AVG(rating) as avg_rating')
            ->selectRaw('COUNT(*) as review_count')
            ->groupBy('reviewee_user_id')
            ->get()
            ->keyBy('reviewee_user_id');

        $result = [];
        foreach ($contractors as $cid => $c) {
            $r = $reviews[$c->user_id] ?? null;
            $result[$cid] = [
                'avg_rating'   => $r ? round((float) $r->avg_rating, 2) : 0,
                'review_count' => $r ? (int) $r->review_count : 0,
            ];
        }

        return $result;
    }

    /**
     * Load active subscription tiers for a set of contractors.
     * Checks platform_payments for active subscription or boosted_post entries.
     *
     * @return array<int, string>  contractor_id → 'gold' | 'silver' | 'free'
     */
    private function batchLoadSubscriptionTiers(array $contractorIds): array
    {
        if (empty($contractorIds)) {
            return [];
        }

        $payments = DB::table('platform_payments')
            ->whereIn('contractor_id', $contractorIds)
            ->where('is_approved', 1)
            ->where(function ($q) {
                $q->where('payment_for', 'subscription')
                  ->orWhere('payment_for', 'boosted_post');
            })
            ->where(function ($q) {
                // Active: future expiration_date, or paid in last 30 days if no expiration
                $q->where('expiration_date', '>=', now())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('expiration_date')
                         ->whereRaw('DATE_ADD(transaction_date, INTERVAL 30 DAY) >= NOW()');
                  });
            })
            ->select('contractor_id', 'subscription_tier', 'amount', 'payment_for')
            ->orderByDesc('transaction_date')
            ->get();

        $result = [];
        foreach ($contractorIds as $cid) {
            $result[$cid] = 'free';
        }

        // Take the best active tier per contractor
        foreach ($payments->groupBy('contractor_id') as $cid => $rows) {
            foreach ($rows as $row) {
                $tier = strtolower(trim($row->subscription_tier ?? ''));
                if ($tier === 'gold') {
                    $result[$cid] = 'gold';
                    break;
                }
                if ($tier === 'silver' && $result[$cid] !== 'gold') {
                    $result[$cid] = 'silver';
                }
                // Fallback: infer from amount when subscription_tier is empty
                if (!$tier && $row->payment_for === 'boosted_post') {
                    if ($row->amount >= 5000 && $result[$cid] !== 'gold') {
                        $result[$cid] = 'gold';
                    } elseif ($row->amount >= 2000 && $result[$cid] === 'free') {
                        $result[$cid] = 'silver';
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Load full contractor records for verification_status, PCAB, experience, etc.
     *
     * @return array<int, object>  contractor_id → contractor row
     */
    private function batchLoadContractorDetails(array $contractorIds): array
    {
        if (empty($contractorIds)) {
            return [];
        }

        return DB::table('contractors')
            ->whereIn('contractor_id', $contractorIds)
            ->select(
                'contractor_id',
                'years_of_experience',
                'completed_projects',
                'verification_status',
                'picab_category',
                'picab_expiration_date',
                'is_active'
            )
            ->get()
            ->keyBy('contractor_id')
            ->all();
    }

    // ═══════════════════════════════════════════════════════════════════
    // DEFAULT BID FETCHER
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Fetch all non-cancelled bids for a project with contractor + user info.
     */
    private function fetchProjectBids(int $projectId): Collection
    {
        return DB::table('bids as b')
            ->join('contractors as c', 'b.contractor_id', '=', 'c.contractor_id')
            ->join('users as u', 'c.user_id', '=', 'u.user_id')
            ->leftJoin('contractor_types as ct', 'c.type_id', '=', 'ct.type_id')
            ->where('b.project_id', $projectId)
            ->whereNotIn('b.bid_status', ['cancelled'])
            ->where('c.is_active', 1)
            ->select(
                'b.*',
                'c.contractor_id',
                'c.company_name',
                'c.years_of_experience',
                'c.completed_projects',
                'c.picab_category',
                'c.picab_expiration_date',
                'c.verification_status',
                'c.company_email',
                'c.company_phone',
                'c.company_website',
                'u.username',
                'u.profile_pic',
                'ct.type_name as contractor_type'
            )
            ->get();
    }

    // ═══════════════════════════════════════════════════════════════════
    // PUBLIC UTILITY METHODS
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Get the active subscription tier for a single contractor.
     *
     * @return string 'gold' | 'silver' | 'free'
     */
    public function getContractorSubscriptionTier(int $contractorId): string
    {
        $result = $this->batchLoadSubscriptionTiers([$contractorId]);
        return $result[$contractorId] ?? 'free';
    }

    /**
     * Get average rating for a single contractor.
     */
    public function getContractorAverageRating(int $contractorId): float
    {
        $result = $this->batchLoadReviewAggregates([$contractorId]);
        return $result[$contractorId]['avg_rating'] ?? 0.0;
    }

    /**
     * Get review count for a single contractor.
     */
    public function getContractorReviewCount(int $contractorId): int
    {
        $result = $this->batchLoadReviewAggregates([$contractorId]);
        return $result[$contractorId]['review_count'] ?? 0;
    }

    /**
     * Return the current weight configuration (for API transparency / debugging).
     */
    public static function getWeights(): array
    {
        return self::WEIGHTS;
    }
}
