<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

/**
 * reviewService — Business logic for bidirectional star reviews.
 *
 * Rules:
 *   - Both contractors and property owners can review each other
 *   - Only allowed when project.project_status = 'completed'
 *   - Rating: 1–5, comment required
 *   - One review per reviewer per project (UNIQUE constraint enforced)
 *   - Cannot review self
 */
class ReviewService
{
    /**
     * Submit a review for a completed project.
     *
     * @param  int    $reviewerUserId  The user writing the review
     * @param  int    $revieweeUserId  The user being reviewed
     * @param  int    $projectId       The completed project
     * @param  int    $rating          1–5
     * @param  string $comment         Review text
     * @return array{success: bool, message: string, data?: object}
     */
    public function submitReview(int $reviewerUserId, int $revieweeUserId, int $projectId, int $rating, string $comment): array
    {
        // 1. Cannot review self
        if ($reviewerUserId === $revieweeUserId) {
            return ['success' => false, 'message' => 'You cannot review yourself.'];
        }

        // 2. Rating range validation
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Rating must be between 1 and 5.'];
        }

        // 3. Comment required
        $comment = trim($comment);
        if (empty($comment)) {
            return ['success' => false, 'message' => 'Comment is required.'];
        }

        // 4. Project must exist and be completed
        $project = DB::table('projects')->where('project_id', $projectId)->first();
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found.'];
        }
        if ($project->project_status !== 'completed') {
            return ['success' => false, 'message' => 'Reviews are only allowed for completed projects.'];
        }

        // 5. Verify reviewer is involved in this project (as owner or contractor)
        $isInvolved = $this->isUserInvolvedInProject($reviewerUserId, $projectId);
        if (!$isInvolved) {
            return ['success' => false, 'message' => 'You are not involved in this project.'];
        }

        // 6. Verify reviewee is also involved
        $revieweeInvolved = $this->isUserInvolvedInProject($revieweeUserId, $projectId);
        if (!$revieweeInvolved) {
            return ['success' => false, 'message' => 'The reviewed user is not involved in this project.'];
        }

        // 7. Check duplicate (unique constraint also enforces this at DB level)
        $existing = DB::table('reviews')
            ->where('reviewer_user_id', $reviewerUserId)
            ->where('project_id', $projectId)
            ->where('is_deleted', 0)
            ->first();
        if ($existing) {
            return ['success' => false, 'message' => 'You have already reviewed this project.'];
        }

        try {
            $reviewId = DB::table('reviews')->insertGetId([
                'reviewer_user_id' => $reviewerUserId,
                'reviewee_user_id' => $revieweeUserId,
                'project_id'       => $projectId,
                'rating'           => $rating,
                'comment'          => $comment,
                'created_at'       => now(),
            ]);

            $review = DB::table('reviews')->where('review_id', $reviewId)->first();

            Log::info('reviewService: Review submitted', [
                'review_id' => $reviewId,
                'reviewer'  => $reviewerUserId,
                'reviewee'  => $revieweeUserId,
                'project'   => $projectId,
                'rating'    => $rating,
            ]);

            // Notify the reviewee that they received a review
            $reviewer = DB::table('users')->where('user_id', $reviewerUserId)->first();
            $reviewerName = $reviewer->username ?? 'Someone';
            NotificationService::create(
                $revieweeUserId,
                'review_submitted',
                'New Review Received',
                "{$reviewerName} left a review on \"{$project->project_title}\".",
                'normal',
                'review',
                $reviewId,
                ['screen' => 'profile', 'params' => ['tab' => 'reviews']]
            );

            return ['success' => true, 'message' => 'Review submitted successfully.', 'data' => $review];
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'reviews_reviewer_project_unique')) {
                return ['success' => false, 'message' => 'You have already reviewed this project.'];
            }
            Log::error('reviewService: Failed to submit review', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to submit review.'];
        }
    }

    /**
     * Get paginated reviews for a user (as reviewee), with reviewer details.
     *
     * @param  int      $revieweeUserId
     * @param  string|null $role  'contractor' or 'owner' — scopes reviews to role-relevant projects
     * @param  int      $page
     * @param  int      $perPage
     * @return array{reviews: array, stats: array, pagination: array}
     */
    public function getReviewsForUser(int $revieweeUserId, ?string $role = null, int $page = 1, int $perPage = 15): array
    {
        $query = DB::table('reviews as r')
            ->join('users as ru', 'r.reviewer_user_id', '=', 'ru.user_id')
            ->leftJoin('property_owners as rpo', 'ru.user_id', '=', 'rpo.user_id')
            ->leftJoin('contractors as c', 'rpo.owner_id', '=', 'c.owner_id')
            ->leftJoin('projects as p', 'r.project_id', '=', 'p.project_id')
            ->where('r.reviewee_user_id', $revieweeUserId);

        // Scope by role
        if ($role === 'contractor') {
            $contractor = (new \App\Services\ProfileService())->getContractorByUserId($revieweeUserId);
            if ($contractor) {
                $query->where('p.selected_contractor_id', $contractor->contractor_id);
            }
        } elseif ($role === 'owner' || $role === 'property_owner') {
            $owner = DB::table('property_owners')->where('user_id', $revieweeUserId)->first();
            if ($owner) {
                $query->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                      ->where('pr.owner_id', $owner->owner_id);
            }
        }

        // Stats (before pagination)
        $statsQuery = clone $query;
        $stats = $statsQuery->select(
            DB::raw('COUNT(r.review_id) as total_reviews'),
            DB::raw('ROUND(AVG(r.rating), 1) as avg_rating'),
            DB::raw('SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) as five_star'),
            DB::raw('SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) as four_star'),
            DB::raw('SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) as three_star'),
            DB::raw('SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) as two_star'),
            DB::raw('SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) as one_star')
        )->first();

        // Paginated reviews
        $total = intval($stats->total_reviews ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $reviews = $query->select(
            'r.review_id',
            'r.project_id',
            'r.reviewer_user_id',
            'r.reviewee_user_id',
            'r.rating',
            'r.comment',
            'r.created_at',
            'ru.username as reviewer_username',
            'rpo.profile_pic as reviewer_profile_pic',
            'c.company_name as reviewer_company_name',
            'p.project_title',
            DB::raw("COALESCE(c.company_name, ru.username) as reviewer_name")
        )
        ->orderBy('r.created_at', 'desc')
        ->offset($offset)
        ->limit($perPage)
        ->get();

        return [
            'reviews' => $reviews,
            'stats' => [
                'total_reviews' => $total,
                'avg_rating'    => $stats->avg_rating !== null ? round(floatval($stats->avg_rating), 1) : null,
                'distribution'  => [
                    5 => intval($stats->five_star ?? 0),
                    4 => intval($stats->four_star ?? 0),
                    3 => intval($stats->three_star ?? 0),
                    2 => intval($stats->two_star ?? 0),
                    1 => intval($stats->one_star ?? 0),
                ],
            ],
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'total_pages'  => $totalPages,
                'has_more'     => $page < $totalPages,
            ],
        ];
    }

    /**
     * Get reviews for a specific project.
     */
    public function getReviewsForProject(int $projectId): array
    {
        $reviews = DB::table('reviews as r')
            ->join('users as ru', 'r.reviewer_user_id', '=', 'ru.user_id')
            ->leftJoin('property_owners as rpo', 'ru.user_id', '=', 'rpo.user_id')
            ->leftJoin('contractors as c', 'rpo.owner_id', '=', 'c.owner_id')
            ->where('r.project_id', $projectId)
            ->where('r.is_deleted', 0)
            ->select(
                'r.review_id', 'r.project_id', 'r.reviewer_user_id', 'r.reviewee_user_id',
                'r.rating', 'r.comment', 'r.created_at',
                'ru.username as reviewer_username', 'rpo.profile_pic as reviewer_profile_pic',
                'c.company_name as reviewer_company_name',
                DB::raw("COALESCE(c.company_name, ru.username) as reviewer_name")
            )
            ->orderBy('r.created_at', 'desc')
            ->get();

        return ['reviews' => $reviews];
    }

    /**
     * Check if a user can leave a review for a project.
     */
    public function canReview(int $userId, int $projectId): array
    {
        $project = DB::table('projects')->where('project_id', $projectId)->first();
        if (!$project) {
            return ['can_review' => false, 'reason' => 'Project not found.'];
        }
        if ($project->project_status !== 'completed') {
            return ['can_review' => false, 'reason' => 'Project is not completed yet.'];
        }
        if (!$this->isUserInvolvedInProject($userId, $projectId)) {
            return ['can_review' => false, 'reason' => 'You are not involved in this project.'];
        }
        $existing = DB::table('reviews')
            ->where('reviewer_user_id', $userId)
            ->where('project_id', $projectId)
            ->where('is_deleted', 0)
            ->first();
        if ($existing) {
            return ['can_review' => false, 'reason' => 'Already reviewed.'];
        }

        // Determine reviewee (the other party)
        $revieweeId = $this->getOtherParty($userId, $projectId);

        // Resolve a display name for the reviewee
        $revieweeName = null;
        if ($revieweeId) {
            $contractor = (new \App\Services\ProfileService())->getContractorByUserId($revieweeId);
            if ($contractor) {
                $revieweeName = $contractor->company_name;
            }
            if (!$revieweeName) {
                $user = DB::table('users')->where('user_id', $revieweeId)->first();
                $revieweeName = $user->username ?? null;
            }
        }

        return ['can_review' => true, 'reviewee_user_id' => $revieweeId, 'reviewee_name' => $revieweeName];
    }

    /**
     * Get aggregate rating stats for a user.
     */
    public function getUserRatingStats(int $userId): array
    {
        $stats = DB::table('reviews')
            ->where('reviewee_user_id', $userId)
            ->where('is_deleted', 0)
            ->whereNotNull('rating')
            ->select(
                DB::raw('COUNT(review_id) as total_reviews'),
                DB::raw('ROUND(AVG(rating), 1) as avg_rating')
            )
            ->first();

        return [
            'total_reviews' => intval($stats->total_reviews ?? 0),
            'avg_rating'    => $stats->avg_rating !== null ? round(floatval($stats->avg_rating), 1) : null,
        ];
    }

    /* ─── Helpers ─────────────────────────────────────────────────────── */

    /**
     * Check if user is involved in a project (as owner or contractor).
     */
    private function isUserInvolvedInProject(int $userId, int $projectId): bool
    {
        // Check as project owner
        $asOwner = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->where('p.project_id', $projectId)
            ->where('po.user_id', $userId)
            ->exists();

        if ($asOwner) return true;

        // Check as selected contractor (compare against contractor owner's user_id)
        $asContractor = DB::table('projects as p')
            ->join('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
            ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
            ->where('p.project_id', $projectId)
            ->where('po.user_id', $userId)
            ->exists();

        return $asContractor;
    }

    /**
     * Get the other party's user_id in a project.
     */
    private function getOtherParty(int $userId, int $projectId): ?int
    {
        $project = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->leftJoin('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
            ->leftJoin('property_owners as po_c', 'c.owner_id', '=', 'po_c.owner_id')
            ->where('p.project_id', $projectId)
            ->select('po.user_id as owner_user_id', 'po_c.user_id as contractor_user_id')
            ->first();

        if (!$project) return null;

        // If reviewer is the owner, reviewee is contractor and vice versa
        if ((int)$project->owner_user_id === $userId) {
            return $project->contractor_user_id ? (int)$project->contractor_user_id : null;
        }
        return (int)$project->owner_user_id;
    }
}
