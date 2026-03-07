<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
class profileService
{
    protected reviewService $reviewService;

    public function __construct(?reviewService $reviewService = null)
    {
        $this->reviewService = $reviewService ?? new reviewService();
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
        if ($role === 'contractor') {
            $contractor = DB::table('contractors')->where('user_id', $userId)->first();
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
            $contractor = DB::table('contractors')->where('user_id', $userId)->first();
            if ($contractor && !empty($contractor->company_name)) {
                $displayName = $contractor->company_name;
            }
        } else {
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($owner) {
                $fullName = trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? ''));
                if (!empty($fullName)) $displayName = $fullName;
            }
        }

        // Project counts
        $projectStats = $this->getProjectCounts($userId, $role);

        // Profile/cover images
        $profilePic = $user->profile_pic;
        $coverPhoto = $user->cover_photo ?? null;
        if ($role === 'contractor') {
            $contractor = $contractor ?? DB::table('contractors')->where('user_id', $userId)->first();
            if ($contractor) {
                if (empty($profilePic) && !empty($contractor->company_logo)) $profilePic = $contractor->company_logo;
                if (empty($coverPhoto) && !empty($contractor->company_banner)) $coverPhoto = $contractor->company_banner;
            }
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
        // Showcase posts from showcases table
        $query = DB::table('showcases as pp')
            ->leftJoin('projects as lp', 'pp.linked_project_id', '=', 'lp.project_id')
            ->where('pp.user_id', $userId)
            ->select(
                'pp.*',
                'lp.project_title as linked_project_title',
                'lp.project_status as linked_project_status',
                DB::raw("'social' as source")
            );

        if ($isOwner) {
            $query->where('pp.status', '!=', 'deleted');
        } else {
            $query->where('pp.status', 'approved');
        }

        $showcasePosts = $query
            ->orderByDesc('pp.created_at')
            ->get();

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
            $contractor = DB::table('contractors as c')
                ->leftJoin('contractor_types as ct', 'c.type_id', '=', 'ct.type_id')
                ->where('c.user_id', $userId)
                ->select('c.*', 'ct.type_name')
                ->first();

            if ($contractor) {
                $about['contractor'] = [
                    'company_name'         => $contractor->company_name,
                    'bio'                  => $contractor->bio,
                    'company_description'  => $contractor->company_description ?? null,
                    'type_name'            => $contractor->type_name,
                    'years_of_experience'  => $contractor->years_of_experience,
                    'completed_projects'   => $contractor->completed_projects ?? 0,
                    'services_offered'     => $contractor->services_offered ?? null,
                    'business_address'     => $contractor->business_address ?? null,
                    'company_website'      => $contractor->company_website ?? null,
                    'company_email'        => $contractor->company_email ?? null,
                    'company_phone'        => $contractor->company_phone ?? null,
                    'verification_status'  => $contractor->verification_status,
                    'picab_category'       => $contractor->picab_category ?? null,
                    'subscription_tier'    => $this->getSubscriptionTier($contractor->contractor_id),
                ];
            }
        } else {
            $owner = DB::table('property_owners as po')
                ->leftJoin('occupations as o', 'po.occupation_id', '=', 'o.id')
                ->where('po.user_id', $userId)
                ->select('po.*', 'o.occupation_name')
                ->first();

            if ($owner) {
                $about['owner'] = [
                    'first_name'          => $owner->first_name,
                    'middle_name'         => $owner->middle_name ?? null,
                    'last_name'           => $owner->last_name,
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
            $query->join('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
                  ->where('c.user_id', $userId);
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
            $query->join('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
                  ->where('c.user_id', $userId);
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
}
