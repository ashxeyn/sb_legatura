<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Services\HighlightService;

/**
 * highlightController — Manage pinned/highlighted posts and projects.
 *
 * Endpoints:
 *   POST   /api/posts/{id}/highlight     — Highlight a project post
 *   DELETE /api/posts/{id}/highlight     — Remove highlight from a post
 *   GET    /api/posts/highlights         — Get user's highlighted posts
 *   POST   /api/projects/{id}/highlight  — Highlight a project
 *   DELETE /api/projects/{id}/highlight  — Remove highlight from a project
 */
class highlightController extends Controller
{
    protected highlightService $highlightService;

    public function __construct(highlightService $highlightService)
    {
        $this->highlightService = $highlightService;
    }

    /* ─── Project Posts ────────────────────────────────────────────── */

    public function highlightPost(Request $request, int $postId)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        $result = $this->highlightService->highlightPost($userId, $postId);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function unhighlightPost(Request $request, int $postId)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        $result = $this->highlightService->unhighlightPost($userId, $postId);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function getHighlights(Request $request)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        $data = $this->highlightService->getHighlightedPosts($userId);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /* ─── Traditional Projects ─────────────────────────────────────── */

    public function highlightProject(Request $request, int $projectId)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        $result = $this->highlightService->highlightProject($userId, $projectId);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function unhighlightProject(Request $request, int $projectId)
    {
        $userId = $this->resolveUserId($request);
        if (!$userId) return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);

        $result = $this->highlightService->unhighlightProject($userId, $projectId);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /* ─── Auth helper ──────────────────────────────────────────────── */

    private function resolveUserId(Request $request): ?int
    {
        $user = Session::get('user') ?: $request->user();
        if (!$user && $request->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) $user = $token->tokenable;
            } catch (\Throwable $e) {
                Log::warning('highlightController bearer fallback: ' . $e->getMessage());
            }
        }
        if (!$user) return null;
        return is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
    }
}
