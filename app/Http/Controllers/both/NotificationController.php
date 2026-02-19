<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use App\Models\Both\notificationClass;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\PersonalAccessToken;

class NotificationController extends Controller
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

        $result = $this->notificationClass->getByUserId($user->user_id, $page, $perPage);

        // Map each row to frontend shape
        $formatted = collect($result['notifications'])->map(function ($row) {
            return NotificationService::formatForFrontend($row);
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $formatted,
                'unread_count'  => $this->notificationClass->getUnreadCount($user->user_id),
                'current_page'  => $result['current_page'],
                'last_page'     => $result['last_page'],
                'per_page'      => $result['per_page'],
                'total'         => $result['total'],
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

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $this->notificationClass->getUnreadCount($user->user_id),
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

        $count = $this->notificationClass->markAllAsRead($user->user_id);

        return response()->json([
            'success' => true,
            'message' => "{$count} notification(s) marked as read",
            'data' => ['affected' => $count],
        ]);
    }
}
