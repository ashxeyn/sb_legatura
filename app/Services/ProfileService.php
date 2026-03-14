<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * profileService — Aggregated profile data for dynamic profile pages.
 *
 * Provides structured data for:
 *   - Header (name, role, verification, rating summary)
 *   - Posts tab (showcase posts + completed projects, sorted by completion date)
 *   - Reviews tab (paginated reviews with stats — only for completed projects)
 *   - About tab (user details, contractor/owner specific info)
 *
 * Avoids N+1: uses aggregation queries and batch loading.
 */
class ProfileService
{
    protected ReviewService $reviewService;

    public function __construct(?ReviewService $reviewService = null)
    {
        $this->reviewService = $reviewService ?? new ReviewService();
    }

    /**
     * Get full profile data for a user.
     *
     * @param  int         $userId
     * @param  string|null $role      'contractor' or 'owner' (auto-detected if null)
     * @param  int|null    $viewerId  User viewing the profile (for ownership checks)
     * @return array
     */
    public function getProfile(int $userId, ?string $role = null, ?int $viewerId = null): array
    {
        $user = DB::table('users')->where('user_id', $userId)->first();
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        // Resolve role
        $role = $this->resolveRole($user, $role);
        $isOwner = $viewerId && $viewerId === $userId;

        // Build header
        $header = $this->buildHeader($user, $role);

        // Build tabs data
        $posts    = $this->getPostsTab($userId, $role, (bool) $isOwner);
        $reviews  = $this->reviewService->getReviewsForUser($userId, $role, 1, 10);
        $about    = $this->getAboutTab($userId, $role);

        return [
            'success' => true,
            'data'    => [
                'header'         => $header,
                'posts'          => $posts,
                'reviews'        => $reviews,
                'about'          => $about,
                'is_own_profile' => $isOwner,
                'user_id'        => $userId,
                'role'           => $role,
            ],
        ];
    }

    /**
     * Build the header section with name, role badge, verification, rating.
     */
    private function buildHeader(object $user, string $role): array
    {
        $userId = $user->user_id;

        // Rating stats
        $ratingStats = $this->reviewService->getUserRatingStats($userId);

        // Verification status
        $verificationStatus = 'unverified';
        // Use resilient contractor lookup to support schemas using owner_id
        $contractor = null;
        if ($role === 'contractor') {
            $contractor = $this->getContractorByUserId($userId);
            if ($contractor) {
                $verificationStatus = $contractor->verification_status ?? 'pending';
            }
        } else {
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($owner) {
                $verificationStatus = $owner->verification_status ?? 'pending';
            }
        }

        // Display name
        $displayName = $user->username;
        if ($role === 'contractor') {
            if (!$contractor) $contractor = $this->getContractorByUserId($userId);
            if ($contractor && !empty($contractor->company_name)) {
                $displayName = $contractor->company_name;
            }
        } else {
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($owner) {
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                if (!empty($fullName)) $displayName = $fullName;
            }
        }

        // Project counts
        $projectStats = $this->getProjectCounts($userId, $role);

        // Profile/cover images — strict role separation, no cross-contamination.
        // Contractor → company_logo / company_banner from contractors table only.
        //   If absent, return null so the frontend shows the default contractor image.
        // Owner → profile_pic / cover_photo from property_owners only.
        //   If absent, return null so the frontend shows the default owner image.
        if ($role === 'contractor') {
            $contractor = $contractor ?? $this->getContractorByUserId($userId);
            $profilePic = $contractor->company_logo ?? null;
            $coverPhoto = $contractor->company_banner ?? null;
        } else {
            $ownerRow = DB::table('property_owners')->where('user_id', $userId)->first();
            $profilePic = $ownerRow->profile_pic ?? null;
            $coverPhoto = $ownerRow->cover_photo ?? null;
        }

        return [
            'user_id'             => $userId,
            'display_name'        => $displayName,
            'username'            => $user->username,
            'role'                => $role,
            'role_badge'          => $role === 'contractor' ? 'Contractor' : 'Property Owner',
            'verification_status' => $verificationStatus,
            'avg_rating'          => $ratingStats['avg_rating'],
            'total_reviews'       => $ratingStats['total_reviews'],
            'profile_pic'         => $profilePic,
            'cover_photo'         => $coverPhoto,
            'completed_projects'  => $projectStats['completed'],
            'ongoing_projects'    => $projectStats['ongoing'],
            'total_projects'      => $projectStats['total'],
            'member_since'        => $user->created_at ?? null,
        ];
    }

    /**
     * Posts tab: only showcase posts (from showcases table).
     * Traditional projects are NOT listed here — contractors must create
     * a showcase post (optionally linked to a completed project) to appear.
     */
    private function getPostsTab(int $userId, string $role, bool $isOwner): array
    {
        // Showcase posts from showcases table with user information
        $query = DB::table('showcases as pp')
            ->join('users as u', 'pp.user_id', '=', 'u.user_id')
            ->leftJoin('property_owners as po', 'u.user_id', '=', 'po.user_id')
            ->leftJoin('contractors as c', 'po.owner_id', '=', 'c.owner_id')
            ->leftJoin('projects as lp', 'pp.linked_project_id', '=', 'lp.project_id')
            ->leftJoin('project_relationships as pr', 'lp.relationship_id', '=', 'pr.rel_id')
            ->leftJoin('milestones as ms', function ($join) {
                $join->on('ms.project_id', '=', 'lp.project_id')
                     ->on('ms.contractor_id', '=', 'pr.selected_contractor_id')
                     ->where('ms.setup_status', '=', 'approved');
            })
            ->where('pp.user_id', $userId)
            ->select(
                'pp.*',
                'u.username', 'po.profile_pic as profile_pic', 'u.user_type',
                'c.company_name', 'c.company_logo',
                'u.first_name as owner_first_name',
                'u.last_name as owner_last_name',
                'lp.project_title as linked_project_title',
                'lp.project_status as linked_project_status',
                'ms.milestone_name as linked_milestone_name',
                DB::raw("'social' as source")
            );

        if ($isOwner) {
            $query->where('pp.status', '!=', 'deleted');
        } else {
            $query->where('pp.status', 'approved');
        }

        $showcasePosts = $query
            ->orderByDesc('pp.created_at')
            ->get()
            ->map(function ($p) {
                // Set display_name based on user type
                if (!empty($p->company_name)) {
                    $p->display_name = $p->company_name;
                } elseif (!empty($p->owner_first_name)) {
                    $p->display_name = trim($p->owner_first_name . ' ' . ($p->owner_last_name ?? ''));
                } else {
                    $p->display_name = $p->username;
                }
                // Set avatar based on user type
                $p->avatar = !empty($p->company_name) ? ($p->company_logo ?? null) : ($p->profile_pic ?? null);
                return $p;
            });

        // Attach images to each showcase post
        if ($showcasePosts->isNotEmpty()) {
            $postIds = $showcasePosts->pluck('post_id')->toArray();
            $images = DB::table('showcase_images')
                ->whereIn('post_id', $postIds)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('post_id');

            $showcasePosts = $showcasePosts->map(function ($post) use ($images) {
                $post->images = isset($images[$post->post_id]) ? $images[$post->post_id]->values()->toArray() : [];
                return $post;
            });
        }

        return [
            'showcase_posts' => $showcasePosts,
        ];
    }

    /**
     * About tab: detailed user info.
     */
    private function getAboutTab(int $userId, string $role): array
    {
        $user = DB::table('users')->where('user_id', $userId)->first();
        $about = [
            'username'     => $user->username ?? null,
            'email'        => $user->email ?? null,
            'user_type'    => $user->user_type ?? null,
            'member_since' => $user->created_at ?? null,
        ];

        if ($role === 'contractor') {
            // Resolve contractor robustly (supports schemas using owner_id)
            $contractor = $this->getContractorByUserId($userId);
            if ($contractor) {
                // Attempt to resolve type name when available
                $typeName = null;
                if (!empty($contractor->type_id)) {
                    try {
                        $typeName = DB::table('contractor_types')->where('type_id', $contractor->type_id)->value('type_name');
                    } catch (\Throwable $e) {
                        Log::warning('Failed to resolve contractor type_name: ' . $e->getMessage());
                    }
                }

                $about['contractor'] = [
                    'company_name'         => $contractor->company_name,
                    'bio'                  => $contractor->company_description ?? null,
                    'company_description'  => $contractor->company_description ?? null,
                    'type_name'            => ($typeName === 'Others' || $typeName === null)
                        ? ($contractor->contractor_type_other ?? $typeName)
                        : $typeName,
                    'years_of_experience'  => $contractor->years_of_experience,
                    'completed_projects'   => $contractor->completed_projects ?? 0,
                    'services_offered'     => $contractor->services_offered ?? null,
                    'business_address'     => $contractor->business_address ?? null,
                    'company_website'      => $contractor->company_website ?? null,
                    'company_email'        => $contractor->company_email ?? null,
                    'verification_status'  => $contractor->verification_status,
                    'picab_category'       => $contractor->picab_category ?? null,
                    'subscription_tier'    => $this->getSubscriptionTier($contractor->contractor_id),
                ];
            }
        } else {
            $owner = DB::table('property_owners as po')
                ->join('users as po_u', 'po.user_id', '=', 'po_u.user_id')
                ->leftJoin('occupations as o', 'po.occupation_id', '=', 'o.id')
                ->where('po.user_id', $userId)
                ->select('po.*', 'po_u.first_name', 'po_u.middle_name', 'po_u.last_name', 'po_u.phone_number', 'o.occupation_name')
                ->first();

            if ($owner) {
                $about['owner'] = [
                    'first_name'          => $owner->first_name,
                    'middle_name'         => $owner->middle_name ?? null,
                    'last_name'           => $owner->last_name,
                    'bio'                 => $owner->bio ?? null,
                    'phone_number'        => $owner->phone_number ?? null,
                    'address'             => $owner->address ?? null,
                    'date_of_birth'       => $owner->date_of_birth ?? null,
                    'occupation'          => $owner->occupation_name ?? ($owner->occupation_other ?? null),
                    'verification_status' => $owner->verification_status,
                ];
            }
        }

        return $about;
    }

    /* ─── Helpers ──────────────────────────────────────────────────── */

    private function resolveRole(object $user, ?string $role): string
    {
        if ($role) {
            $normalized = strtolower(trim($role));
            return str_contains($normalized, 'contractor') ? 'contractor' : 'owner';
        }

        $userType = strtolower(trim($user->user_type ?? ''));
        if ($userType === 'both') {
            $preferred = strtolower(trim($user->preferred_role ?? ''));
            return str_contains($preferred, 'contractor') ? 'contractor' : 'owner';
        }
        return str_contains($userType, 'contractor') ? 'contractor' : 'owner';
    }

    private function getProjectCounts(int $userId, string $role): array
    {
        $query = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id');

        if ($role === 'contractor') {
            // Resolve contractor and filter by contractor_id to avoid direct user_id assumptions
            $contractor = $this->getContractorByUserId($userId);
            if ($contractor) {
                $query->where('pr.selected_contractor_id', $contractor->contractor_id);
            } else {
                // No contractor -> empty result set
                return ['completed' => 0, 'ongoing' => 0, 'total' => 0];
            }
        } else {
            $query->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                  ->where('po.user_id', $userId);
        }

        $completed = (clone $query)->where('p.project_status', 'completed')->count();
        $ongoing = (clone $query)->whereIn('p.project_status', ['in_progress', 'open', 'bidding_closed'])->count();
        $total = (clone $query)->whereNotIn('p.project_status', ['deleted', 'deleted_post'])->count();

        return compact('completed', 'ongoing', 'total');
    }

    private function getUserProjects(int $userId, string $role)
    {
        $query = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->leftJoin('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
            ->whereNotIn('p.project_status', ['deleted', 'deleted_post']);

        if ($role === 'contractor') {
            $contractor = $this->getContractorByUserId($userId);
            if ($contractor) {
                $query->where('pr.selected_contractor_id', $contractor->contractor_id);
            } else {
                // Return empty collection if contractor record can't be resolved
                return collect();
            }
        } else {
            $query->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                  ->where('po.user_id', $userId);
        }

        return $query->select(
            'p.project_id', 'p.project_title', 'p.project_description', 'p.project_location',
            'p.budget_range_min', 'p.budget_range_max', 'p.property_type',
            'p.project_status',
            'ct.type_name as contractor_type_name',
            'pr.created_at as post_created_at'
        )
        ->orderByRaw("CASE WHEN p.project_status = 'completed' THEN 0 ELSE 1 END")
        ->orderByDesc('pr.created_at')
        ->get();
    }

    /**
     * Get subscription tier for a contractor.
     */
    private function getSubscriptionTier(int $contractorId): string
    {
        $payment = DB::table('platform_payments as pp')
            ->join('subscription_plans as sp', 'pp.subscriptionPlanId', '=', 'sp.id')
            ->where('pp.contractor_id', $contractorId)
            ->where('pp.is_approved', 1)
            ->where('pp.is_cancelled', 0)
            ->where('pp.expiration_date', '>', now())
            ->where('sp.for_contractor', 1)
            ->orderByDesc('sp.amount')
            ->select('sp.plan_key', 'sp.name')
            ->first();

        return $payment ? $payment->plan_key : 'free';
    }

    /**
     * Resolve contractor row for a given user_id using owner_id linkage.
     */
    public function getContractorByUserId(int $userId)
    {
        try {
            // Prefer a join-based lookup: many schemas store the canonical user_id on
            // `property_owners` and link `contractors` via `owner_id`. Join to reliably
            // find the contractor row for a given user_id when that schema is used.
            if (Schema::hasColumn('contractors', 'owner_id')) {
                try {
                    $row = DB::table('contractors as c')
                        ->join('property_owners as po', 'po.owner_id', '=', 'c.owner_id')
                        ->where('po.user_id', $userId)
                        ->select('c.*', 'po.user_id')
                        ->first();

                    if ($row) return $row;
                } catch (\Throwable $e) {
                    // Join failed for some reason — fall through to other strategies
                    Log::warning('ProfileService join-based contractor lookup failed: ' . $e->getMessage());
                }
            }

            // If contractors table exposes user_id directly, use it as a fast path.
            if (Schema::hasColumn('contractors', 'user_id')) {
                try {
                    return DB::table('contractors')->where('user_id', $userId)->first();
                } catch (\Throwable $e) {
                    Log::warning('ProfileService user_id lookup failed: ' . $e->getMessage());
                }
            }

            // As a last resort, resolve the owner_id from property_owners then query contractors.
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            $ownerId = $owner->owner_id ?? null;
            if ($ownerId && Schema::hasColumn('contractors', 'owner_id')) {
                return DB::table('contractors')->where('owner_id', $ownerId)->first();
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('ProfileService::getContractorByUserId failed: ' . $e->getMessage());
            try {
                $owner = DB::table('property_owners')->where('user_id', $userId)->first();
                if ($owner) return DB::table('contractors')->where('owner_id', $owner->owner_id)->first();
            } catch (\Throwable $e2) {
                Log::warning('ProfileService::getContractorByUserId fallback failed: ' . $e2->getMessage());
            }
            return null;
        }
    }
}
