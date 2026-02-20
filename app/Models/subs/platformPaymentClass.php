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
            // Get contractor ID for the user
            $contractor = DB::table('contractors')->where('user_id', $userId)->first();

            if ($contractor) {
                // Check for active subscription in platform_payments
                $subscription = DB::table('platform_payments')
                    ->where('contractor_id', $contractor->contractor_id)
                    ->where('payment_for', 'subscription')
                    ->where('is_approved', 1)
                    ->where(function ($query) {
                    $query->whereNull('expiration_date')
                        ->orWhere('expiration_date', '>', now());
                })
                    // Ensure start date is valid (if column exists) - robust check
                    ->where(function ($query) {
                    $query->whereNull('transaction_date')
                        ->orWhere('transaction_date', '<=', now());
                })
                    // Prioritize longest remaining validity to handle overlaps
                    ->orderByDesc('expiration_date')
                    ->orderByDesc('platform_payment_id')
                    ->first();

                if ($subscription) {
                    $tier = $subscription->subscription_tier ?? 'Basic';
                    return [
                        'plan_key' => $tier,
                        'name' => ucfirst($tier) . ' Tier',
                        'expires_at' => $subscription->expiration_date ?Carbon::parse($subscription->expiration_date)->format('F j, Y') : null,
                        'is_active' => true,
                        'benefits' => self::getBenefitsForTier($tier)
                    ];
                }
            }
        }
        catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getSubscriptionForUser Error: ' . $e->getMessage());
            return null;
        }

        return null;
    }

    private static function getBenefitsForTier($tier)
    {
        $tier = strtolower($tier);
        if ($tier === 'gold') {
            return ['Unlock AI driven analytics', 'Unlimited Bids', 'Boosted Bids (Stay at the top)'];
        }
        elseif ($tier === 'silver') {
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
                $reach = (int)DB::table('boosts')->where('user_id', $userId)->sum('reach');
                $bids = (int)DB::table('boosts')->where('user_id', $userId)->sum('bids');
                $clicks = (int)DB::table('boosts')->where('user_id', $userId)->sum('clicks');
            }
            else {
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
        }
        catch (\Throwable $e) {
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
                    ->where('platform_payments.owner_id', $owner->owner_id)
                    ->where('platform_payments.payment_for', 'boosted_post')
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
                        }
                        else {
                            $post->percentage = 0;
                        }
                    }
                    else {
                        $post->percentage = 0;
                    }

                    return $post;
                });
            }
        }
        catch (\Throwable $e) {
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
                    ->whereColumn('platform_payments.project_id', 'projects.project_id')
                    ->where('platform_payments.payment_for', 'boosted_post')
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
                return $project;
            });

            return $projects;
        }
        catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getBoostableProjects Error: ' . $e->getMessage());
            return [];
        }
    }
}
