<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * feedRankingService — Weighted Feed Scoring System for Contractor Project Feed
 *
 * Calculates a per-post, per-contractor FEED_SCORE to determine display order.
 *
 * FEED_SCORE = Σ (component_score × weight)
 *
 * Components:
 *   1. Type Match Score       (30%) — contractor type vs. project required type
 *   2. Boost Score            (20%) — paid boost via platform_payments
 *   3. Freshness Score        (15%) — time decay from creation date
 *   4. Location Proximity     (10%) — text-based city/province matching
 *   5. Engagement Score       (10%) — bid count sweet-spot (3–10 ideal)
 *   6. Budget Relevance       ( 5%) — alignment with contractor's typical scale
 *   7. Diversity Score        (10%) — anti-monopoly per owner in result set
 *
 * All component scores are normalised to 0.0 – 1.0 before weighting.
 * Final feed_score range: 0.0 – 1.0 (displayed as 0 – 100 when needed).
 *
 * Design principles:
 *   - Relevance-first: type match dominates at 30%
 *   - No pay-to-win: boost (20%) cannot override relevance + freshness + diversity
 *   - Fair rotation: diversity penalty prevents one owner flooding the feed
 *   - Modular: each scorer is a standalone method, easy to extend
 *   - Efficient: batch-loads boost data, bid counts, and contractor context
 */
class feedRankingService
{
    // ─── Configurable weights (must sum to 1.0) ────────────────────────

    private const WEIGHTS = [
        'type_match'   => 0.30,
        'boost'        => 0.20,
        'freshness'    => 0.15,
        'location'     => 0.10,
        'engagement'   => 0.10,
        'budget'       => 0.05,
        'diversity'    => 0.10,
    ];

    // ─── Boost configuration ───────────────────────────────────────────

    /** subscription_plans.id for the "Project Boost" plan */
    private const BOOST_PLAN_ID = 4;

    // ─── Freshness decay thresholds (days → score) ─────────────────────

    private const FRESHNESS_FULL  = 3;   // 100% for first 3 days
    private const FRESHNESS_HIGH  = 7;   // 80% up to 7 days
    private const FRESHNESS_MED   = 14;  // 50% up to 14 days
    private const FRESHNESS_LOW   = 30;  // linear decay to 0.2 at 30 days
    private const FRESHNESS_FLOOR = 0.1; // minimum score for very old posts

    // ─── Engagement sweet-spot ─────────────────────────────────────────

    private const ENGAGEMENT_IDEAL_MIN  = 3;
    private const ENGAGEMENT_IDEAL_MAX  = 10;
    private const ENGAGEMENT_OVERCROWD  = 20;
    private const ENGAGEMENT_ZERO_SCORE = 0.7; // new opportunity bonus

    // ─── Cache TTL (seconds) ───────────────────────────────────────────

    private const CACHE_TTL = 300; // 5 minutes

    /* =====================================================================
     * PUBLIC API
     * ===================================================================== */

    /**
     * Rank a collection of project posts for a specific contractor.
     *
     * @param  int             $contractorId     Contractor viewing the feed
     * @param  int|null        $contractorTypeId Contractor's type_id (for type matching)
     * @param  Collection      $projects         Pre-filtered projects from feedClass
     * @return Collection                        Projects sorted by feed_score DESC, with score metadata attached
     */
    public function rankFeed(int $contractorId, ?int $contractorTypeId, Collection $projects): Collection
    {
        if ($projects->isEmpty()) {
            return $projects;
        }

        $startTime = microtime(true);

        try {
            // ── Batch-load all context needed for scoring ──────────────

            $projectIds = $projects->pluck('project_id')->toArray();
            $ownerIds   = $projects->pluck('owner_id')->unique()->toArray();

            $boostMap       = $this->batchLoadActiveBoosts($projectIds);
            $bidCountMap    = $this->batchLoadBidCounts($projectIds);
            $contractorCtx  = $this->loadContractorContext($contractorId);

            // ── Score each project ─────────────────────────────────────

            $ownerSeenCount = []; // tracks how many posts per owner for diversity

            // First pass: compute raw scores without diversity
            $scored = $projects->map(function ($project) use (
                $contractorTypeId,
                $contractorCtx,
                $boostMap,
                $bidCountMap,
            ) {
                $pid = $project->project_id;

                $typeMatchScore  = $this->scoreTypeMatch($project, $contractorTypeId);
                $boostScore      = $this->scoreBoost($pid, $boostMap);
                $freshnessScore  = $this->scoreFreshness($project);
                $locationScore   = $this->scoreLocation($project, $contractorCtx);
                $engagementScore = $this->scoreEngagement($pid, $bidCountMap);
                $budgetScore     = $this->scoreBudget($project, $contractorCtx);

                // Store component scores for later diversity pass
                $project->_scores = [
                    'type_match'  => $typeMatchScore,
                    'boost'       => $boostScore,
                    'freshness'   => $freshnessScore,
                    'location'    => $locationScore,
                    'engagement'  => $engagementScore,
                    'budget'      => $budgetScore,
                ];

                $project->_owner_id_for_diversity = $project->owner_id ?? $project->owner_user_id ?? 0;

                return $project;
            });

            // Sort by preliminary score to determine diversity ordering
            $scored = $scored->sortByDesc(function ($project) {
                $s = $project->_scores;
                return ($s['type_match']  * self::WEIGHTS['type_match'])
                     + ($s['boost']       * self::WEIGHTS['boost'])
                     + ($s['freshness']   * self::WEIGHTS['freshness'])
                     + ($s['location']    * self::WEIGHTS['location'])
                     + ($s['engagement']  * self::WEIGHTS['engagement'])
                     + ($s['budget']      * self::WEIGHTS['budget'])
                     + (1.0              * self::WEIGHTS['diversity']); // assume full diversity initially
            });

            // Second pass: apply diversity scoring based on position
            $ownerSeenCount = [];
            $ranked = $scored->map(function ($project) use (&$ownerSeenCount) {
                $ownerId = $project->_owner_id_for_diversity;
                $ownerSeenCount[$ownerId] = ($ownerSeenCount[$ownerId] ?? 0) + 1;

                $diversityScore = $this->scoreDiversity($ownerSeenCount[$ownerId]);
                $s = $project->_scores;

                $feedScore = ($s['type_match']  * self::WEIGHTS['type_match'])
                           + ($s['boost']       * self::WEIGHTS['boost'])
                           + ($s['freshness']   * self::WEIGHTS['freshness'])
                           + ($s['location']    * self::WEIGHTS['location'])
                           + ($s['engagement']  * self::WEIGHTS['engagement'])
                           + ($s['budget']      * self::WEIGHTS['budget'])
                           + ($diversityScore   * self::WEIGHTS['diversity']);

                // Clamp to [0, 1]
                $feedScore = max(0.0, min(1.0, $feedScore));

                // Attach metadata to the project object
                $project->feed_score = round($feedScore * 100, 1);
                $project->is_boosted = $s['boost'] > 0;
                $project->score_breakdown = [
                    'type_match'  => round($s['type_match'], 3),
                    'boost'       => round($s['boost'], 3),
                    'freshness'   => round($s['freshness'], 3),
                    'location'    => round($s['location'], 3),
                    'engagement'  => round($s['engagement'], 3),
                    'budget'      => round($s['budget'], 3),
                    'diversity'   => round($diversityScore, 3),
                ];

                // Clean up temp properties
                unset($project->_scores, $project->_owner_id_for_diversity);

                return $project;
            });

            // Final sort by feed_score descending
            $ranked = $ranked->sortByDesc('feed_score')->values();

            $elapsed = round((microtime(true) - $startTime) * 1000, 1);
            Log::debug("feedRankingService::rankFeed scored {$projects->count()} projects in {$elapsed}ms", [
                'contractor_id' => $contractorId,
            ]);

            return $ranked;

        } catch (\Throwable $e) {
            Log::error('feedRankingService::rankFeed failed: ' . $e->getMessage(), [
                'contractor_id' => $contractorId,
                'trace'         => $e->getTraceAsString(),
            ]);

            // Non-fatal: return original order
            return $projects;
        }
    }

    /* =====================================================================
     * INDIVIDUAL SCORERS (each returns 0.0 – 1.0)
     * ===================================================================== */

    /**
     * 1. Type Match Score (30%)
     *
     * Exact match = 1.0, mismatch = 0.4 (still visible, just ranked lower).
     */
    private function scoreTypeMatch(object $project, ?int $contractorTypeId): float
    {
        if (!$contractorTypeId) {
            return 0.6; // unknown contractor type — neutral score
        }

        $projectTypeId = $project->type_id ?? null;

        if ($projectTypeId && (int) $projectTypeId === $contractorTypeId) {
            return 1.0;
        }

        return 0.4;
    }

    /**
     * 2. Boost Score (20%)
     *
     * Checks platform_payments for an active, approved boost.
     * Boosted = 1.0, not boosted = 0.0.
     */
    private function scoreBoost(int $projectId, array $boostMap): float
    {
        return isset($boostMap[$projectId]) ? 1.0 : 0.0;
    }

    /**
     * 3. Freshness Score (15%)
     *
     * Time-decay curve:
     *   ≤ 3 days  → 1.0
     *   ≤ 7 days  → 0.8
     *   ≤ 14 days → 0.5
     *   ≤ 30 days → linear decay to 0.2
     *   > 30 days → 0.1 floor
     */
    private function scoreFreshness(object $project): float
    {
        $createdAt = $project->created_at ?? null;
        if (!$createdAt) {
            return self::FRESHNESS_FLOOR;
        }

        try {
            $created = new \DateTime($createdAt);
            $now     = new \DateTime();
            $days    = max(0, (int) $now->diff($created)->days);
        } catch (\Throwable $e) {
            return self::FRESHNESS_FLOOR;
        }

        if ($days <= self::FRESHNESS_FULL) {
            return 1.0;
        }
        if ($days <= self::FRESHNESS_HIGH) {
            return 0.8;
        }
        if ($days <= self::FRESHNESS_MED) {
            return 0.5;
        }
        if ($days <= self::FRESHNESS_LOW) {
            // Linear decay from 0.5 at 14 days to 0.2 at 30 days
            $range = self::FRESHNESS_LOW - self::FRESHNESS_MED; // 16
            $elapsed = $days - self::FRESHNESS_MED;
            return 0.5 - (0.3 * ($elapsed / $range));
        }

        return self::FRESHNESS_FLOOR;
    }

    /**
     * 4. Location Proximity Score (10%)
     *
     * Text-based matching between project_location and contractor business_address.
     *   Same city    → 1.0
     *   Same province → 0.7
     *   No overlap   → 0.4
     */
    private function scoreLocation(object $project, array $contractorCtx): float
    {
        $projectLocation     = strtolower(trim($project->project_location ?? ''));
        $contractorAddress   = strtolower(trim($contractorCtx['business_address'] ?? ''));

        if (!$projectLocation || !$contractorAddress) {
            return 0.5; // can't determine — neutral
        }

        // Extract meaningful location tokens (city names, province names)
        $projectTokens    = $this->extractLocationTokens($projectLocation);
        $contractorTokens = $this->extractLocationTokens($contractorAddress);

        if (empty($projectTokens) || empty($contractorTokens)) {
            return 0.5;
        }

        // Check for city-level match (tokens with length >= 4 for meaningful matches)
        $commonTokens = array_intersect($projectTokens, $contractorTokens);

        if (!empty($commonTokens)) {
            // Check if a "city" token matches (heuristic: longer tokens = more specific)
            $hasSpecificMatch = false;
            foreach ($commonTokens as $token) {
                if (strlen($token) >= 6) { // e.g. "zamboanga", "manila", "cebu city"
                    $hasSpecificMatch = true;
                    break;
                }
            }

            if ($hasSpecificMatch) {
                return 1.0; // city-level match
            }

            return 0.7; // province-level match (shorter common tokens)
        }

        // Check for partial substring match (e.g. "Zamboanga" in both)
        foreach ($contractorTokens as $cToken) {
            if (strlen($cToken) >= 5 && str_contains($projectLocation, $cToken)) {
                return 0.85;
            }
        }

        return 0.4; // no location overlap
    }

    /**
     * 5. Engagement Score (10%)
     *
     * Sweet-spot model:
     *   0 bids       → 0.7 (fresh opportunity)
     *   1–2 bids     → 0.85 (building interest)
     *   3–10 bids    → 1.0 (ideal range)
     *   11–19 bids   → linear decay 1.0 → 0.5
     *   20+ bids     → 0.5 (overcrowded)
     */
    private function scoreEngagement(int $projectId, array $bidCountMap): float
    {
        $bids = $bidCountMap[$projectId] ?? 0;

        if ($bids === 0) {
            return self::ENGAGEMENT_ZERO_SCORE;
        }
        if ($bids < self::ENGAGEMENT_IDEAL_MIN) {
            // 1-2 bids: interpolate between 0.7 and 1.0
            return 0.7 + (0.3 * ($bids / self::ENGAGEMENT_IDEAL_MIN));
        }
        if ($bids <= self::ENGAGEMENT_IDEAL_MAX) {
            return 1.0;
        }
        if ($bids < self::ENGAGEMENT_OVERCROWD) {
            // 11-19: linear decay from 1.0 to 0.5
            $range = self::ENGAGEMENT_OVERCROWD - self::ENGAGEMENT_IDEAL_MAX;
            $excess = $bids - self::ENGAGEMENT_IDEAL_MAX;
            return 1.0 - (0.5 * ($excess / $range));
        }

        return 0.5; // 20+ bids — overcrowded
    }

    /**
     * 6. Budget Relevance Score (5%)
     *
     * Compares project budget midpoint with contractor's average accepted bid.
     * Closer alignment = higher score.
     */
    private function scoreBudget(object $project, array $contractorCtx): float
    {
        $budgetMin = (float) ($project->budget_range_min ?? 0);
        $budgetMax = (float) ($project->budget_range_max ?? 0);

        if ($budgetMin <= 0 && $budgetMax <= 0) {
            return 0.5; // no budget specified — neutral
        }

        $projectMidpoint = ($budgetMin + $budgetMax) / 2;
        $contractorAvg   = $contractorCtx['avg_bid'] ?? 0;

        if ($contractorAvg <= 0) {
            return 0.5; // no bid history — neutral
        }

        // Ratio of smaller to larger (1.0 = perfect match, approaches 0 for extremes)
        $ratio = min($projectMidpoint, $contractorAvg) / max($projectMidpoint, $contractorAvg);

        // Apply a gentle curve: sqrt makes moderate differences less punishing
        return max(0.2, sqrt($ratio));
    }

    /**
     * 7. Diversity Score (10%)
     *
     * Penalises repeat appearances from the same owner in the feed.
     *   1st post  → 1.0
     *   2nd post  → 0.7
     *   3rd post  → 0.5
     *   4th post  → 0.3
     *   5th+ post → 0.2
     */
    private function scoreDiversity(int $ownerAppearanceCount): float
    {
        return match (true) {
            $ownerAppearanceCount <= 1 => 1.0,
            $ownerAppearanceCount === 2 => 0.7,
            $ownerAppearanceCount === 3 => 0.5,
            $ownerAppearanceCount === 4 => 0.3,
            default => 0.2,
        };
    }

    /* =====================================================================
     * BATCH DATA LOADERS (efficient, single-query per data type)
     * ===================================================================== */

    /**
     * Load active boost status for a set of projects.
     *
     * Returns: [ project_id => true ] for projects with an active, approved boost.
     *
     * A boost is active when:
     *   - platform_payments.subscriptionPlanId = BOOST_PLAN_ID (4)
     *   - is_approved = 1
     *   - is_cancelled = 0
     *   - expiration_date > NOW()
     */
    private function batchLoadActiveBoosts(array $projectIds): array
    {
        if (empty($projectIds)) {
            return [];
        }

        $cacheKey = 'feed_boosts_' . md5(implode(',', $projectIds));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($projectIds) {
            $boosted = DB::table('platform_payments')
                ->whereIn('project_id', $projectIds)
                ->where('subscriptionPlanId', self::BOOST_PLAN_ID)
                ->where('is_approved', 1)
                ->where('is_cancelled', 0)
                ->where('expiration_date', '>', now())
                ->pluck('project_id')
                ->unique()
                ->toArray();

            $map = [];
            foreach ($boosted as $pid) {
                $map[$pid] = true;
            }

            return $map;
        });
    }

    /**
     * Load bid counts for a set of projects (non-cancelled bids only).
     *
     * Returns: [ project_id => count ]
     */
    private function batchLoadBidCounts(array $projectIds): array
    {
        if (empty($projectIds)) {
            return [];
        }

        $counts = DB::table('bids')
            ->selectRaw('project_id, COUNT(*) as cnt')
            ->whereIn('project_id', $projectIds)
            ->whereNotIn('bid_status', ['cancelled'])
            ->groupBy('project_id')
            ->pluck('cnt', 'project_id')
            ->toArray();

        return $counts;
    }

    /**
     * Load contractor context needed for scoring.
     *
     * Returns associative array with:
     *   - business_address: string
     *   - avg_bid: float (average proposed_cost of accepted/submitted bids)
     *   - type_id: int
     */
    private function loadContractorContext(int $contractorId): array
    {
        $cacheKey = "feed_contractor_ctx_{$contractorId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($contractorId) {
            $contractor = DB::table('contractors')
                ->where('contractor_id', $contractorId)
                ->select('business_address', 'type_id')
                ->first();

            $avgBid = DB::table('bids')
                ->where('contractor_id', $contractorId)
                ->whereIn('bid_status', ['accepted', 'submitted'])
                ->avg('proposed_cost');

            return [
                'business_address' => $contractor->business_address ?? '',
                'type_id'          => $contractor->type_id ?? null,
                'avg_bid'          => (float) ($avgBid ?? 0),
            ];
        });
    }

    /* =====================================================================
     * HELPERS
     * ===================================================================== */

    /**
     * Extract meaningful location tokens from a free-text address.
     *
     * Strips common filler words and short tokens, returns cleaned array.
     * E.g. "Street There 143, Purok 67, Arena Blanco, Zamboanga City"
     *   → ['arena', 'blanco', 'zamboanga', 'city']
     */
    private function extractLocationTokens(string $address): array
    {
        // Remove numbers and special characters
        $cleaned = preg_replace('/[^a-z\s]/', ' ', $address);

        // Split into words
        $words = preg_split('/\s+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);

        // Filter out short/common filler words
        $stopWords = [
            'st', 'street', 'ave', 'avenue', 'blvd', 'road', 'rd', 'dr',
            'drive', 'lane', 'ln', 'purok', 'brgy', 'barangay', 'sitio',
            'phase', 'block', 'lot', 'unit', 'floor', 'bldg', 'building',
            'near', 'along', 'corner', 'beside', 'behind', 'front', 'back',
            'the', 'and', 'or', 'in', 'at', 'of', 'to', 'for', 'del', 'sur',
            'norte', 'apt', 'apartment',
        ];

        return array_values(array_filter($words, function ($w) use ($stopWords) {
            return strlen($w) >= 3 && !in_array($w, $stopWords);
        }));
    }

    /* =====================================================================
     * DIAGNOSTICS / ADMIN
     * ===================================================================== */

    /**
     * Get the current weight configuration (for admin UI / debugging).
     */
    public function getWeights(): array
    {
        return self::WEIGHTS;
    }

    /**
     * Explain scoring for a specific project–contractor pair.
     * Useful for admin debug views.
     */
    public function explainScore(int $projectId, int $contractorId): ?array
    {
        $project = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->where('p.project_id', $projectId)
            ->select('p.*', 'pr.created_at', 'pr.owner_id', 'pr.bidding_due')
            ->first();

        if (!$project) {
            return null;
        }

        $contractor = DB::table('contractors')->where('contractor_id', $contractorId)->first();
        if (!$contractor) {
            return null;
        }

        $boostMap    = $this->batchLoadActiveBoosts([$projectId]);
        $bidCountMap = $this->batchLoadBidCounts([$projectId]);
        $ctx         = $this->loadContractorContext($contractorId);

        $scores = [
            'type_match'  => $this->scoreTypeMatch($project, $contractor->type_id),
            'boost'       => $this->scoreBoost($projectId, $boostMap),
            'freshness'   => $this->scoreFreshness($project),
            'location'    => $this->scoreLocation($project, $ctx),
            'engagement'  => $this->scoreEngagement($projectId, $bidCountMap),
            'budget'      => $this->scoreBudget($project, $ctx),
            'diversity'   => 1.0, // assume first appearance for single-project explain
        ];

        $total = 0;
        $breakdown = [];
        foreach (self::WEIGHTS as $component => $weight) {
            $raw = $scores[$component];
            $weighted = $raw * $weight;
            $total += $weighted;
            $breakdown[$component] = [
                'raw_score'      => round($raw, 3),
                'weight'         => $weight,
                'weighted_score' => round($weighted, 4),
            ];
        }

        return [
            'project_id'     => $projectId,
            'contractor_id'  => $contractorId,
            'feed_score'     => round($total * 100, 1),
            'breakdown'      => $breakdown,
            'context'        => [
                'project_type_id'      => $project->type_id,
                'contractor_type_id'   => $contractor->type_id,
                'project_location'     => $project->project_location ?? '',
                'contractor_address'   => $ctx['business_address'],
                'bid_count'            => $bidCountMap[$projectId] ?? 0,
                'is_boosted'           => isset($boostMap[$projectId]),
                'contractor_avg_bid'   => $ctx['avg_bid'],
                'project_budget_mid'   => (($project->budget_range_min ?? 0) + ($project->budget_range_max ?? 0)) / 2,
            ],
        ];
    }
}
