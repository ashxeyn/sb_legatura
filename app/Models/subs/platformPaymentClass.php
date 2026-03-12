<?php

namespace App\Models\subs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class platformPaymentClass
{
    /**
     * Get latest subscription for user or null
     */
    public static function getSubscriptionForUser(?int $userId)
    {
        if (!$userId)
            return null;

        try {
            // Get contractor ID for the user (schema-agnostic)
            $contractor = (new \App\Services\ProfileService())->getContractorByUserId($userId);

            if ($contractor) {
                // Check for active subscription in platform_payments
                $subscription = DB::table('platform_payments')
                    ->join('subscription_plans', 'platform_payments.subscriptionPlanId', '=', 'subscription_plans.id')
                    ->where('platform_payments.contractor_id', $contractor->contractor_id)
                    ->where('subscription_plans.plan_key', '!=', 'boost')
                    ->where('platform_payments.is_approved', 1)
                    ->where(function ($query) {
                        $query->whereNull('platform_payments.expiration_date')
                            ->orWhere('platform_payments.expiration_date', '>', now());
                    })
                    // Ensure start date is valid
                    ->where(function ($query) {
                        $query->whereNull('platform_payments.transaction_date')
                            ->orWhere('platform_payments.transaction_date', '<=', now());
                    })
                    ->select('platform_payments.*', 'subscription_plans.plan_key', 'subscription_plans.name as plan_name')
                    // Prioritize longest remaining validity to handle overlaps
                    ->orderByDesc('platform_payments.expiration_date')
                    ->orderByDesc('platform_payments.platform_payment_id')
                    ->first();

                if ($subscription) {
                    $tier = $subscription->plan_key;
                    $benefits = [];
                    if (!empty($subscription->benefits)) {
                        $benefits = is_string($subscription->benefits) ? json_decode($subscription->benefits, true) : $subscription->benefits;
                    }

                    return [
                        'plan_key' => $tier,
                        'name' => $subscription->plan_name,
                        'expires_at' => $subscription->expiration_date ? Carbon::parse($subscription->expiration_date)->format('F j, Y') : null,
                        'is_active' => true,
                        'benefits' => $benefits ?: self::getBenefitsForTier($tier)
                    ];
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getSubscriptionForUser Error: ' . $e->getMessage());
            return null;
        }

        return null;
    }

    private static function getBenefitsForTier($tier)
    {
        try {
            $plan = DB::table('subscription_plans')->where('plan_key', strtolower($tier))->first();
            if ($plan && !empty($plan->benefits)) {
                return is_string($plan->benefits) ? json_decode($plan->benefits, true) : $plan->benefits;
            }
        } catch (\Throwable $e) {
        }

        $tier = strtolower($tier);
        if ($tier === 'gold') {
            return ['Unlock AI driven analytics', 'Unlimited Bids', 'Boosted Bids (Stay at the top)'];
        } elseif ($tier === 'silver') {
            return ['25 Bids per month', 'Boosted Bids (Stay at the top)'];
        }
        return ['10 Bids per month'];
    }

    /**
     * Aggregate boost analytics for user (safe, best-effort)
     */
    public static function getBoostAnalytics(?int $userId)
    {
        if (!$userId)
            return null;

        try {
            // Since we don't have a tracking table yet for reach/clicks per individual boost,
            // we'll return a basic structure. If 'boosts' table is missing, we check 'platform_payments'.

            $reach = 0;
            $bids = 0;
            $clicks = 0;

            if (Schema::hasTable('boosts')) {
                $reach = (int) DB::table('boosts')->where('user_id', $userId)->sum('reach');
                $bids = (int) DB::table('boosts')->where('user_id', $userId)->sum('bids');
                $clicks = (int) DB::table('boosts')->where('user_id', $userId)->sum('clicks');
            } else {
                // Fallback: Just show 0 or some base data if we only have payments
                // In a real scenario, we'd join with an analytics table.
            }

            return [
                'reach' => $reach,
                'bids' => $bids,
                'clicks' => $clicks,
                'reach_change' => 0,
                'bids_change' => 0,
                'clicks_change' => 0,
                'period' => '7d',
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Return boosted posts for user as array or collection
     */
    public static function getBoostedPosts(?int $userId)
    {
        if (!$userId)
            return [];

        try {
            // Get property owner ID
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($owner) {
                $posts = DB::table('platform_payments')
                    ->join('projects', 'platform_payments.project_id', '=', 'projects.project_id')
                    ->join('subscription_plans', 'platform_payments.subscriptionPlanId', '=', 'subscription_plans.id')
                    ->where('platform_payments.owner_id', $owner->owner_id)
                    ->where('subscription_plans.plan_key', 'boost')
                    ->where('platform_payments.is_approved', 1)
                    ->select(
                        'projects.project_id as id',
                        'projects.project_title as title',
                        'projects.project_description as description',
                        'projects.project_location as location',
                        'platform_payments.expiration_date as ends_at',
                        'platform_payments.transaction_date as starts_at'
                    )
                    ->orderByDesc('platform_payments.platform_payment_id')
                    ->get();

                return $posts->map(function ($post) {
                    $post->image_url = 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400&h=400&fit=crop';
                    $post->excerpt = strlen($post->description) > 150 ? substr($post->description, 0, 150) . '...' : $post->description;

                    // Calculate percentage (Time elapsed vs Total duration)
                    if ($post->starts_at && $post->ends_at) {
                        $start = Carbon::parse($post->starts_at);
                        $end = Carbon::parse($post->ends_at);
                        $now = Carbon::now();

                        $totalDuration = $start->diffInSeconds($end);
                        $elapsed = $start->diffInSeconds($now);

                        if ($totalDuration > 0) {
                            $post->percentage = max(0, min(100, round(($elapsed / $totalDuration) * 100)));
                        } else {
                            $post->percentage = 0;
                        }
                    } else {
                        $post->percentage = 0;
                    }

                    // Attach any project_files rows if present
                    try {
                        // Only include project files that are either 'desired design' or 'others'
                        $files = DB::table('project_files')
                            ->where('project_id', $post->id)
                            ->whereIn('file_type', ['desired design', 'others'])
                            ->get();
                        $post->project_files = $files->map(function ($f) {
                            return [
                                'file_id' => $f->file_id ?? null,
                                'project_id' => $f->project_id ?? null,
                                'file_type' => $f->file_type ?? null,
                                'file_path' => $f->file_path ?? null,
                                'uploaded_at' => $f->uploaded_at ?? null,
                            ];
                        })->toArray();
                    } catch (\Throwable $e) {
                        $post->project_files = [];
                    }

                    return $post;
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getBoostedPosts Error: ' . $e->getMessage());
            return [];
        }

        return [];
    }
    public static function getBoostableProjects(?int $userId)
    {
        if (!$userId) {
            \Illuminate\Support\Facades\Log::warning('getBoostableProjects: No user ID provided');
            return [];
        }

        try {
            \Illuminate\Support\Facades\Log::info('getBoostableProjects: Fetching for user ' . $userId);

            $projects = DB::table('projects')
                ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                ->join('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
                ->leftJoin('milestones', 'projects.project_id', '=', 'milestones.project_id')
                ->where('property_owners.user_id', $userId)
                ->where('projects.project_status', 'open')
                ->where('project_relationships.project_post_status', 'approved')
                ->whereNull('milestones.milestone_id')
                // Only show projects that don't have an active boost
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('platform_payments')
                        ->join('subscription_plans', 'platform_payments.subscriptionPlanId', '=', 'subscription_plans.id')
                        ->whereColumn('platform_payments.project_id', 'projects.project_id')
                        ->where('subscription_plans.plan_key', 'boost')
                        ->where('platform_payments.is_approved', 1)
                        ->where('platform_payments.expiration_date', '>', now());
                })
                ->select(
                    'projects.project_id as id',
                    'projects.project_title as title',
                    'projects.project_description as description',
                    'projects.project_location as location',
                    'project_relationships.created_at as date',
                    'projects.project_status',
                    'project_relationships.project_post_status'
                )
                ->distinct()
                ->orderBy('project_relationships.created_at', 'desc')
                ->get();

            \Illuminate\Support\Facades\Log::info('getBoostableProjects: Found ' . $projects->count() . ' projects.');

            $projects->transform(function ($project) {
                $project->image = 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=400&h=400&fit=crop';
                $project->date = \Carbon\Carbon::parse($project->date)->format('F Y');
                // Attach project files if any
                try {
                    // Only include project files that are either 'desired design' or 'others'
                    $files = DB::table('project_files')
                        ->where('project_id', $project->id)
                        ->whereIn('file_type', ['desired design', 'others'])
                        ->get();
                    $project->project_files = $files->map(function ($f) {
                        return [
                            'file_id' => $f->file_id ?? null,
                            'project_id' => $f->project_id ?? null,
                            'file_type' => $f->file_type ?? null,
                            'file_path' => $f->file_path ?? null,
                            'uploaded_at' => $f->uploaded_at ?? null,
                        ];
                    })->toArray();
                } catch (\Throwable $e) {
                    $project->project_files = [];
                }

                return $project;
            });

            return $projects;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getBoostableProjects Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get bid limit based on subscription tier.
     * Returns null for unlimited (Gold), or the numeric limit for Silver/Bronze.
     * Note: This returns the SUBSCRIPTION limit only, not including the free tier bonus.
     *
     * @param string|null $planKey The subscription plan key
     * @return int|null Number of bids allowed (null = unlimited)
     */
    public static function getBidLimitForTier(?string $planKey): ?int
    {
        if (!$planKey) {
            return 3; // Non-subscribers get 3 bids per month (free tier)
        }

        $tier = strtolower($planKey);
        switch ($tier) {
            case 'gold':
            case 'premium':
                return null; // Unlimited
            case 'silver':
            case 'standard':
                return 25;
            case 'bronze':
                return 10;
            default:
                return 3; // Default to non-subscriber limit
        }
    }

    /**
     * Get the free tier bid allowance.
     * Users who used the free tier before subscribing get 3 free bids that don't count against their subscription limit.
     */
    public static function getFreeTierAllowance(): int
    {
        return 3;
    }

    /**
     * Check if a contractor used the free tier before subscribing.
     * Returns true if they placed any bids before their first subscription started.
     * Direct subscribers (no bids before subscribing) don't get the free tier bonus.
     *
     * @param int $contractorId The contractor ID
     * @return bool True if user used free tier before subscribing
     */
    public static function usedFreeTierBeforeSubscription(int $contractorId): bool
    {
        try {
            // Get the contractor's first ever subscription start date
            $firstSubscription = DB::table('platform_payments')
                ->join('subscription_plans', 'platform_payments.subscriptionPlanId', '=', 'subscription_plans.id')
                ->where('platform_payments.contractor_id', $contractorId)
                ->where('subscription_plans.plan_key', '!=', 'boost')
                ->where('platform_payments.is_approved', 1)
                ->orderBy('platform_payments.transaction_date', 'asc')
                ->orderBy('platform_payments.created_at', 'asc')
                ->first();

            // If no subscription ever, user is on free tier
            if (!$firstSubscription) {
                return true; // Free tier user gets the free allowance
            }

            // Get the subscription start date
            $subscriptionStartDate = $firstSubscription->transaction_date
                ?? $firstSubscription->created_at
                ?? now();

            // Check if they placed ANY bids before that subscription started
            $bidsBeforeSubscription = DB::table('bids')
                ->where('contractor_id', $contractorId)
                ->where('submitted_at', '<', $subscriptionStartDate)
                ->whereNotIn('bid_status', ['cancelled'])
                ->exists();

            return $bidsBeforeSubscription;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('usedFreeTierBeforeSubscription Error: ' . $e->getMessage());
            // Default to true to be generous in case of errors
            return true;
        }
    }

    /**
     * Count how many bids a contractor has made in the current billing period.
     * For simplicity, we count bids submitted in the current calendar month.
     *
     * @param int $contractorId The contractor ID
     * @return int Number of bids made this month
     */
    public static function getMonthlyBidCount(int $contractorId): int
    {
        try {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            return DB::table('bids')
                ->where('contractor_id', $contractorId)
                ->whereBetween('submitted_at', [$startOfMonth, $endOfMonth])
                ->whereNotIn('bid_status', ['cancelled']) // Don't count cancelled bids
                ->count();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getMonthlyBidCount Error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if a contractor can submit a new bid based on their subscription.
     * Free tier bids (3) are separate from subscription bids - they don't count against subscription limits.
     *
     * @param int $userId The user ID
     * @return array Contains 'can_bid', 'bids_used', 'bids_limit', 'plan_key', 'message'
     */
    public static function checkBidEligibility(int $userId): array
    {
        try {
            // Get contractor info via property_owners
            $po = DB::table('property_owners')->where('user_id', $userId)->first();
            $contractor = null;

            if ($po) {
                $contractor = DB::table('contractors')->where('owner_id', $po->owner_id)->first();

                // Also check staff membership
                if (!$contractor) {
                    $staff = DB::table('contractor_staff')
                        ->where('owner_id', $po->owner_id)
                        ->where('is_active', 1)
                        ->first();

                    if ($staff) {
                        $contractor = DB::table('contractors')
                            ->where('contractor_id', $staff->contractor_id)
                            ->first();
                    }
                }
            }

            if (!$contractor) {
                return [
                    'can_bid' => false,
                    'bids_used' => 0,
                    'bids_limit' => 0,
                    'plan_key' => null,
                    'plan_name' => null,
                    'message' => 'Contractor profile not found'
                ];
            }

            // Get subscription
            $subscription = self::getSubscriptionForUser($userId);
            $planKey = $subscription['plan_key'] ?? null;
            $planName = $subscription['name'] ?? ($planKey ? ucfirst($planKey) : 'Free');

            // Get total bids made this month
            $totalBidsUsed = self::getMonthlyBidCount($contractor->contractor_id);
            $freeAllowance = self::getFreeTierAllowance();

            // For free users (no subscription)
            if (!$planKey) {
                $bidsRemaining = max(0, $freeAllowance - $totalBidsUsed);
                $canBid = $bidsRemaining > 0;

                return [
                    'can_bid' => $canBid,
                    'bids_used' => $totalBidsUsed,
                    'bids_limit' => $freeAllowance,
                    'bids_remaining' => $bidsRemaining,
                    'plan_key' => null,
                    'plan_name' => 'Free',
                    'message' => $canBid
                        ? "You have {$bidsRemaining} free bid(s) remaining this month"
                        : "You have used all your free bids. Subscribe to get more bids."
                ];
            }

            // Get subscription bid limit
            $subscriptionLimit = self::getBidLimitForTier($planKey);

            // Unlimited bids for Gold
            if ($subscriptionLimit === null) {
                return [
                    'can_bid' => true,
                    'bids_used' => $totalBidsUsed,
                    'bids_limit' => null, // null means unlimited
                    'bids_remaining' => null, // null means unlimited
                    'plan_key' => $planKey,
                    'plan_name' => $planName,
                    'message' => 'You have unlimited bids with your Gold subscription'
                ];
            }

            // Check if user used free tier before subscribing
            $usedFreeTierFirst = self::usedFreeTierBeforeSubscription($contractor->contractor_id);

            // For paid subscriptions (Bronze/Silver):
            // - If they used free tier first: free bids don't count against subscription (3 + limit)
            // - If they subscribed directly: they only get the subscription limit
            if ($usedFreeTierFirst) {
                // User used free tier before subscribing - they get free allowance + subscription limit
                $bidsUsedAgainstSubscription = max(0, $totalBidsUsed - $freeAllowance);
                $bidsRemaining = max(0, $subscriptionLimit - $bidsUsedAgainstSubscription);
                $totalCapacity = $freeAllowance + $subscriptionLimit;
            } else {
                // Direct subscriber - they only get subscription limit
                $bidsUsedAgainstSubscription = $totalBidsUsed;
                $bidsRemaining = max(0, $subscriptionLimit - $totalBidsUsed);
                $totalCapacity = $subscriptionLimit;
            }

            $canBid = $totalBidsUsed < $totalCapacity;

            return [
                'can_bid' => $canBid,
                'bids_used' => $bidsUsedAgainstSubscription, // Show usage against subscription only
                'bids_limit' => $subscriptionLimit,
                'bids_remaining' => $bidsRemaining,
                'plan_key' => $planKey,
                'plan_name' => $planName,
                'total_bids_this_month' => $totalBidsUsed, // Include total for reference
                'has_free_bonus' => $usedFreeTierFirst, // True if user gets +3 free bids bonus
                'total_capacity' => $totalCapacity, // Actual total bids allowed this month
                'message' => $canBid
                    ? "You have {$bidsRemaining} bid(s) remaining this month"
                    : "You have reached your monthly bid limit of {$subscriptionLimit}. Upgrade your subscription for more bids."
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('checkBidEligibility Error: ' . $e->getMessage());
            return [
                'can_bid' => false,
                'bids_used' => 0,
                'bids_limit' => 0,
                'plan_key' => null,
                'plan_name' => null,
                'message' => 'Unable to check bid eligibility'
            ];
        }
    }
}
