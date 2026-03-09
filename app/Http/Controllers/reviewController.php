<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Services\ReviewService;

/**
 * reviewController — API endpoints for the bidirectional star review system.
 *
 * Endpoints:
 *   POST /api/reviews               — Submit a review
 *   GET  /api/reviews/user/{userId} — Get reviews for a user (paginated)
 *   GET  /api/reviews/project/{id}  — Get reviews for a project
 *   GET  /api/reviews/can-review    — Check if user can review a project
 */
class reviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Submit a review for a completed project.
     *
     * POST /api/reviews
     * Body: { project_id, reviewee_user_id, rating (1-5), comment }
     */
    public function store(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $request->validate([
            'project_id'       => 'required|integer',
            'reviewee_user_id' => 'required|integer',
            'rating'           => 'required|integer|min:1|max:5',
            'comment'          => 'required|string|min:5|max:2000',
        ]);

        $result = $this->reviewService->submitReview(
            $userId,
            (int) $request->input('reviewee_user_id'),
            (int) $request->input('project_id'),
            (int) $request->input('rating'),
            $request->input('comment')
        );

        return response()->json($result, $result['success'] ? 201 : 422);
    }

    /**
     * Get paginated reviews for a user.
     *
     * GET /api/reviews/user/{userId}?role=contractor&page=1&per_page=15
     */
    public function forUser(Request $request, int $userId)
    {
        $role    = $request->query('role');
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 15)));

        $data = $this->reviewService->getReviewsForUser($userId, $role, $page, $perPage);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Get reviews for a specific project.
     *
     * GET /api/reviews/project/{projectId}
     */
    public function forProject(Request $request, int $projectId)
    {
        $data = $this->reviewService->getReviewsForProject($projectId);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Check if the authenticated user can leave a review on a project.
     *
     * GET /api/reviews/can-review?project_id=123
     */
    public function canReview(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $projectId = (int) $request->query('project_id');
        if (!$projectId) {
            return response()->json(['success' => false, 'message' => 'project_id is required.'], 400);
        }

        $result = $this->reviewService->canReview($userId, $projectId);
        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * Get aggregate rating stats for a user.
     *
     * GET /api/reviews/stats/{userId}
     */
    public function stats(int $userId)
    {
        $data = $this->reviewService->getUserRatingStats($userId);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /* ─── Auth helper ──────────────────────────────────────────────────── */

    private function resolveUserId(Request $request): ?int
    {
        $user = Session::get('user') ?: $request->user();
        if (!$user && $request->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) $user = $token->tokenable;
            } catch (\Throwable $e) {
                Log::warning('reviewController bearer fallback failed: ' . $e->getMessage());
            }
        }
        if (!$user) return null;
        return is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
    }
}
