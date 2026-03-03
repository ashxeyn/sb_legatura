<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Services\profileService;

/**
 * profileApiController — Full profile page API (aggregated, tabbed data).
 *
 * Endpoints:
 *   GET /api/profile/{userId}          — Full profile (header + all tabs)
 *   GET /api/profile/{userId}/posts    — Posts tab only
 *   GET /api/profile/{userId}/about    — About tab only
 */
class profileApiController extends Controller
{
    protected profileService $profileService;

    public function __construct(profileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * GET /api/profile/{userId}
     *
     * Returns full profile with header, posts, highlights, reviews, about.
     * Query params: ?role=contractor|owner
     */
    public function show(Request $request, int $userId)
    {
        $viewerId = $this->resolveUserId($request);
        $role = $request->query('role');

        $result = $this->profileService->getProfile($userId, $role, $viewerId);

        if (!($result['success'] ?? false)) {
            return response()->json($result, 404);
        }

        return response()->json($result);
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
                Log::warning('ProfileApiController bearer fallback: ' . $e->getMessage());
            }
        }
        if (!$user) return null;
        return is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
    }
}
