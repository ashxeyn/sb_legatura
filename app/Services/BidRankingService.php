<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Centralized Bid Ranking Service
 * 
 * This service provides a single location for all bid ranking logic.
 * Modify weights and scoring rules here - all controllers will use these rules automatically.
 */
class bidRankingService
{
    /**
     * CONFIGURATION - Adjust these weights to change ranking priorities
     * All weights should add up to 100 for percentage-based scoring
     */
    private const WEIGHTS = [
        'price' => 35,           // How much does price matter? (35%)
        'experience' => 30,      // How much does experience matter? (30%)
        'reputation' => 25,      // How much do reviews matter? (25%)
        'subscription' => 10,    // Premium subscription boost (10%)
    ];

    /**
     * SUBSCRIPTION TIERS - Based on platform_payments
     * Define scoring boost for each subscription level
     */
    private const SUBSCRIPTION_SCORES = [
        'premium' => 100,        // Full boost for premium subscribers
        'professional' => 60,    // Medium boost for professional
        'free' => 0,             // No boost for free users
    ];

    /**
     * EXPERIENCE SCORING - Define points for experience metrics
     */
    private const EXPERIENCE_CONFIG = [
        'completed_projects_multiplier' => 2,  // Each completed project = 2 points
        'years_experience_multiplier' => 3,    // Each year of experience = 3 points
        'max_score' => 100,                    // Cap at 100 to prevent over-scoring
    ];

    /**
     * PRICE SCORING - How to evaluate bid prices
     */
    private const PRICE_CONFIG = [
        'within_budget_score' => 100,          // Perfect score if within budget
        'below_budget_penalty' => 20,          // Deduct if too cheap (suspicious)
        'above_budget_penalty_rate' => 100,    // Penalty rate for over budget bids
    ];

    /**
     * Rank all bids for a project
     * 
     * @param int $projectId The project to rank bids for
     * @param Collection $bids Collection of bid objects (optional - will fetch if not provided)
     * @return Collection Bids sorted by ranking score (highest first)
     */
    public function rankBids(int $projectId, ?Collection $bids = null): Collection
    {
        // Get project budget for price scoring
        $project = DB::table('projects')
            ->select('budget_range_min', 'budget_range_max')
            ->where('project_id', $projectId)
            ->first();

        if (!$project) {
            throw new \Exception("Project not found: {$projectId}");
        }

        // Fetch bids if not provided
        if ($bids === null) {
            $bids = $this->fetchProjectBids($projectId);
        }

        // Calculate ranking score for each bid
        foreach ($bids as $bid) {
            $bid->ranking_score = $this->calculateBidScore($bid, $project);
            
            // Add breakdown for debugging (optional - remove in production if not needed)
            $bid->score_breakdown = [
                'price_score' => $this->calculatePriceScore($bid->proposed_cost, $project->budget_range_min, $project->budget_range_max),
                'experience_score' => $this->calculateExperienceScore($bid->completed_projects ?? 0, $bid->years_of_experience ?? 0),
                'reputation_score' => $this->calculateReputationScore($bid->contractor_id),
                'subscription_score' => $this->calculateSubscriptionScore($bid->contractor_id),
            ];
        }

        // Sort by ranking score (highest first)
        return $bids->sortByDesc('ranking_score')->values();
    }

    /**
     * Calculate total score for a single bid
     * 
     * @param object $bid The bid object
     * @param object $project The project object
     * @return float Total weighted score (0-100)
     */
    private function calculateBidScore($bid, $project): float
    {
        $priceScore = $this->calculatePriceScore(
            $bid->proposed_cost,
            $project->budget_range_min,
            $project->budget_range_max
        );

        $experienceScore = $this->calculateExperienceScore(
            $bid->completed_projects ?? 0,
            $bid->years_of_experience ?? 0
        );

        $reputationScore = $this->calculateReputationScore($bid->contractor_id);
        
        $subscriptionScore = $this->calculateSubscriptionScore($bid->contractor_id);

        // Apply weights and return total score
        $totalScore = 
            ($priceScore * self::WEIGHTS['price'] / 100) +
            ($experienceScore * self::WEIGHTS['experience'] / 100) +
            ($reputationScore * self::WEIGHTS['reputation'] / 100) +
            ($subscriptionScore * self::WEIGHTS['subscription'] / 100);

        return round($totalScore, 2);
    }

    /**
     * PRICE SCORING
     * Bids within budget score highest
     * Too cheap bids are penalized (might be suspicious)
     * Over budget bids are heavily penalized
     * 
     * @param float $proposedCost The bid amount
     * @param float $budgetMin Minimum budget
     * @param float $budgetMax Maximum budget
     * @return float Score (0-100)
     */
    private function calculatePriceScore(float $proposedCost, float $budgetMin, float $budgetMax): float
    {
        // Case 1: Bid is below minimum budget (too cheap - suspicious)
        if ($proposedCost < $budgetMin) {
            $percentBelow = (($budgetMin - $proposedCost) / $budgetMin) * 100;
            return max(0, self::PRICE_CONFIG['within_budget_score'] - ($percentBelow * 0.5));
        }
        
        // Case 2: Bid is within budget range (ideal)
        if ($proposedCost >= $budgetMin && $proposedCost <= $budgetMax) {
            // Give slightly higher score to bids closer to budget_min
            $rangePosition = ($proposedCost - $budgetMin) / ($budgetMax - $budgetMin);
            return self::PRICE_CONFIG['within_budget_score'] - ($rangePosition * self::PRICE_CONFIG['below_budget_penalty']);
        }
        
        // Case 3: Bid is over budget (penalize heavily)
        $percentOver = (($proposedCost - $budgetMax) / $budgetMax) * 100;
        $penalty = $percentOver * self::PRICE_CONFIG['above_budget_penalty_rate'] / 100;
        return max(0, 80 - $penalty);
    }

    /**
     * EXPERIENCE SCORING
     * Based on completed projects and years of experience
     * 
     * @param int $completedProjects Number of completed projects
     * @param int $yearsExperience Years of experience
     * @return float Score (0-100)
     */
    private function calculateExperienceScore(int $completedProjects, int $yearsExperience): float
    {
        $score = 
            ($completedProjects * self::EXPERIENCE_CONFIG['completed_projects_multiplier']) +
            ($yearsExperience * self::EXPERIENCE_CONFIG['years_experience_multiplier']);

        // Cap at maximum score
        return min(self::EXPERIENCE_CONFIG['max_score'], $score);
    }

    /**
     * REPUTATION SCORING
     * Based on reviews from completed projects
     * Uses existing reviews table
     * 
     * @param int $contractorId The contractor ID
     * @return float Score (0-100)
     */
    private function calculateReputationScore(int $contractorId): float
    {
        // Get contractor's user_id
        $contractor = DB::table('contractors')
            ->where('contractor_id', $contractorId)
            ->first();

        if (!$contractor) {
            return 0;
        }

        // Get all reviews where this contractor was reviewed
        $reviews = DB::table('reviews')
            ->where('reviewee_user_id', $contractor->user_id)
            ->select('rating')
            ->get();

        if ($reviews->isEmpty()) {
            // No reviews yet - return neutral score (50)
            return 50;
        }

        // Calculate average rating
        $averageRating = $reviews->avg('rating');
        
        // Convert 1-5 star rating to 0-100 score
        $baseScore = ($averageRating / 5) * 100;

        // Bonus for having many reviews (trust factor)
        $reviewCount = $reviews->count();
        $trustBonus = 0;
        
        if ($reviewCount >= 20) {
            $trustBonus = 10;
        } elseif ($reviewCount >= 10) {
            $trustBonus = 5;
        } elseif ($reviewCount >= 5) {
            $trustBonus = 2;
        }

        return min(100, $baseScore + $trustBonus);
    }

    /**
     * SUBSCRIPTION SCORING
     * Premium contractors get ranking boost
     * Based on platform_payments table
     * 
     * @param int $contractorId The contractor ID
     * @return float Score (0-100)
     */
    private function calculateSubscriptionScore(int $contractorId): float
    {
        // Check for active subscription in platform_payments
        // Looking for boosted_post payments that indicate premium subscription
        $activeSubscription = DB::table('platform_payments')
            ->where('contractor_id', $contractorId)
            ->where('payment_for', 'boosted_post')
            ->where('is_approved', 1)
            ->whereRaw('DATE_ADD(transaction_date, INTERVAL 30 DAY) >= NOW()') // Active within last 30 days
            ->orderBy('transaction_date', 'desc')
            ->first();

        if ($activeSubscription) {
            // Determine tier based on payment amount
            // Adjust these thresholds based on your actual subscription pricing
            if ($activeSubscription->amount >= 5000) {
                return self::SUBSCRIPTION_SCORES['premium'];
            } elseif ($activeSubscription->amount >= 2000) {
                return self::SUBSCRIPTION_SCORES['professional'];
            }
        }

        // No active subscription
        return self::SUBSCRIPTION_SCORES['free'];
    }

    /**
     * Fetch all bids for a project with contractor details
     * 
     * @param int $projectId The project ID
     * @return Collection Collection of bid objects
     */
    private function fetchProjectBids(int $projectId): Collection
    {
        return DB::table('bids as b')
            ->join('contractors as c', 'b.contractor_id', '=', 'c.contractor_id')
            ->join('users as u', 'c.user_id', '=', 'u.user_id')
            ->where('b.project_id', $projectId)
            ->whereNotIn('b.bid_status', ['cancelled'])
            ->select(
                'b.*',
                'c.contractor_id',
                'c.company_name',
                'c.years_of_experience',
                'c.completed_projects',
                'c.company_email',
                'c.company_phone',
                'c.company_website',
                'u.username',
                'u.profile_pic'
            )
            ->get();
    }

    /**
     * Get contractor's current subscription tier
     * Useful for displaying badges in UI
     * 
     * @param int $contractorId The contractor ID
     * @return string 'premium', 'professional', or 'free'
     */
    public function getContractorSubscriptionTier(int $contractorId): string
    {
        $activeSubscription = DB::table('platform_payments')
            ->where('contractor_id', $contractorId)
            ->where('payment_for', 'boosted_post')
            ->where('is_approved', 1)
            ->whereRaw('DATE_ADD(transaction_date, INTERVAL 30 DAY) >= NOW()')
            ->orderBy('transaction_date', 'desc')
            ->first();

        if ($activeSubscription) {
            if ($activeSubscription->amount >= 5000) {
                return 'premium';
            } elseif ($activeSubscription->amount >= 2000) {
                return 'professional';
            }
        }

        return 'free';
    }

    /**
     * Get contractor's average rating
     * 
     * @param int $contractorId The contractor ID
     * @return float Average rating (0-5)
     */
    public function getContractorAverageRating(int $contractorId): float
    {
        $contractor = DB::table('contractors')
            ->where('contractor_id', $contractorId)
            ->first();

        if (!$contractor) {
            return 0;
        }

        $reviews = DB::table('reviews')
            ->where('reviewee_user_id', $contractor->user_id)
            ->select('rating')
            ->get();

        if ($reviews->isEmpty()) {
            return 0;
        }

        return round($reviews->avg('rating'), 2);
    }

    /**
     * Get contractor's total review count
     * 
     * @param int $contractorId The contractor ID
     * @return int Number of reviews
     */
    public function getContractorReviewCount(int $contractorId): int
    {
        $contractor = DB::table('contractors')
            ->where('contractor_id', $contractorId)
            ->first();

        if (!$contractor) {
            return 0;
        }

        return DB::table('reviews')
            ->where('reviewee_user_id', $contractor->user_id)
            ->count();
    }
}

