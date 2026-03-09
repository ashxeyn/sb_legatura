<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use App\Models\both\notificationClass;
use App\Services\NotificationService;
use App\Services\NotificationRedirectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\PersonalAccessToken;

class notificationController extends Controller
{
    protected notificationClass $notificationClass;

    public function __construct()
    {
        $this->notificationClass = new notificationClass();
    }

    /**
     * Authenticate user from session or Bearer token.
     * Returns user object or null.
     */
    private function resolveUser(Request $request): ?object
    {
        $user = Session::get('user');

        if (!$user) {
            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                $token = PersonalAccessToken::findToken($bearerToken);
                if ($token) {
                    $user = $token->tokenable;
                    if ($user && !Session::has('user')) {
                        Session::put('user', $user);
                    }
                }
            }

            if (!$user && $request->user()) {
                $user = $request->user();
                if (!Session::has('user')) {
                    Session::put('user', $user);
                }
            }
        }

        return $user;
    }

    /**
     * GET /api/notifications?page=1
     */
    public function index(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $role = $this->resolvePreferredRole($user);

        $result = $this->notificationClass->getByUserId($user->user_id, $page, $perPage, $role);

        // Map each row to frontend shape
        $formatted = collect($result['notifications'])->map(function ($row) {
            return notificationService::formatForFrontend($row);
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $formatted,
                'unread_count'  => $this->notificationClass->getUnreadCount($user->user_id, $role),
                'current_page'  => $result['current_page'],
                'last_page'     => $result['last_page'],
                'per_page'      => $result['per_page'],
                'total'         => $result['total'],
                'active_role'   => $role,
            ],
        ]);
    }

    /**
     * GET /api/notifications/unread-count
     */
    public function unreadCount(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $role = $this->resolvePreferredRole($user);

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $this->notificationClass->getUnreadCount($user->user_id, $role),
                'active_role'  => $role,
            ],
        ]);
    }

    /**
     * POST /api/notifications/{id}/read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $result = $this->notificationClass->markAsRead((int) $id, $user->user_id);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Notification marked as read' : 'Notification not found or already read',
        ]);
    }

    /**
     * POST /api/notifications/read-all
     */
    public function markAllAsRead(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $role = $this->resolvePreferredRole($user);
        $count = $this->notificationClass->markAllAsRead($user->user_id, $role);

        return response()->json([
            'success' => true,
            'message' => "{$count} notification(s) marked as read" . ($role ? " for {$role} role" : ''),
            'data' => ['affected' => $count, 'active_role' => $role],
        ]);
    }

    // ─── Notification Redirect ─────────────────────────────────────────

    /**
     * GET /notifications/{id}/redirect
     *
     * Web endpoint: marks notification as read and issues a 302 redirect
     * to the contextually correct page.
     */
    public function redirect(Request $request, $id)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return redirect('/login');
        }

        $notification = $this->notificationClass->getById((int) $id);

        if (!$notification) {
            return redirect($this->dashboardUrl($user))
                ->with('warning', 'Notification not found.');
        }

        // Ownership check
        if ((int) $notification->user_id !== (int) $user->user_id) {
            abort(403, 'Unauthorized');
        }

        // Mark as read
        $this->notificationClass->markAsRead((int) $id, $user->user_id);

        // Resolve redirect
        $userRole = $this->resolveRole($user);
        $result = notificationRedirectService::resolve($notification, $userRole);

        if ($result['flash']) {
            return redirect($result['url'])->with('warning', $result['flash']);
        }

        return redirect($result['url']);
    }

    /**
     * GET /api/notifications/{id}/redirect
     *
     * API endpoint: marks notification as read and returns the computed
     * redirect URL as JSON (for mobile / SPA clients).
     */
    public function apiResolveRedirect(Request $request, $id)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $notification = $this->notificationClass->getById((int) $id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        // Ownership check
        if ((int) $notification->user_id !== (int) $user->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Mark as read
        $this->notificationClass->markAsRead((int) $id, $user->user_id);

        // Resolve redirect
        $userRole = $this->resolveRole($user);
        $payload = notificationRedirectService::resolveForApi($notification, $userRole);

        return response()->json([
            'success' => true,
            'data'    => $payload,
        ]);
    }

    // ─── Private helpers ───────────────────────────────────────────────

    /**
     * Determine the current user's role string for redirect resolution.
     */
    private function resolveRole(object $user): string
    {
        // Check session first
        $sessionRole = Session::get('current_role') ?? Session::get('userType');
        if ($sessionRole === 'contractor') {
            return 'contractor';
        }
        if ($sessionRole === 'admin') {
            return 'admin';
        }
        if ($sessionRole === 'property_owner' || $sessionRole === 'owner') {
            return 'property_owner';
        }

        // Fallback to preferred_role from DB
        $preferred = $this->resolvePreferredRole($user);
        if ($preferred === 'contractor') {
            return 'contractor';
        }
        if ($preferred === 'owner') {
            return 'property_owner';
        }

        // Final fallback to DB field
        $userType = $user->user_type ?? null;
        if ($userType === 'contractor') {
            return 'contractor';
        }
        if ($userType === 'admin') {
            return 'admin';
        }

        return 'property_owner';
    }

    /**
     * Resolve the user's preferred_role for notification filtering.
     *
     * For 'both' users: returns 'contractor' or 'owner' based on
     *   1. Session current_role
     *   2. DB preferred_role
     *   3. Default 'contractor'
     *
     * For single-role users (contractor/property_owner) returns the
     * corresponding role string, or null for staff/admin.
     */
    private function resolvePreferredRole(object $user): ?string
    {
        $userType = $user->user_type ?? null;

        // Single-role users: always return their fixed role, no filtering needed
        if ($userType === 'contractor') {
            return null; // single-role, show all their notifications
        }
        if ($userType === 'property_owner') {
            return null;
        }
        if ($userType === 'staff' || $userType === 'admin') {
            return null;
        }

        // "both" users: need role-based filtering
        // 1. Check session
        $sessionRole = Session::get('current_role');
        if ($sessionRole === 'contractor') {
            return 'contractor';
        }
        if ($sessionRole === 'property_owner' || $sessionRole === 'owner') {
            return 'owner';
        }

        // 2. Check DB preferred_role
        $userId = $user->user_id ?? null;
        if ($userId) {
            try {
                $preferred = DB::table('users')
                    ->where('user_id', $userId)
                    ->value('preferred_role');
                if (!empty($preferred)) {
                    return $preferred; // 'contractor' or 'owner'
                }
            } catch (\Throwable $e) {
                Log::warning('resolvePreferredRole: DB lookup failed: ' . $e->getMessage());
            }
        }

        // 3. Default to contractor for 'both' users
        return 'contractor';
    }

    /**
     * Role-aware dashboard URL for unauthenticated/error fallbacks.
     */
    private function dashboardUrl(object $user): string
    {
        $role = $this->resolveRole($user);
        return match ($role) {
            'contractor' => '/contractor/dashboard',
            'admin'      => '/admin/dashboard',
            default      => '/owner/dashboard',
        };
    }
}
