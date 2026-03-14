<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class adminNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $adminId = $this->resolveAdminId();
        if (!$adminId || Session::get('userType') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $limit = min(20, max(1, (int) $request->query('limit', 5)));
        $enabledTypes = $this->enabledActivityTypes($adminId);

        if (empty($enabledTypes)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => [],
                    'unread_count' => 0,
                ],
            ]);
        }

        $baseQuery = DB::table('user_activity_logs as ual')
            ->leftJoin('users as u', 'u.user_id', '=', 'ual.user_id')
            ->whereIn('ual.activity_type', $enabledTypes);

        $rows = (clone $baseQuery)
            ->select([
                'ual.id',
                'ual.activity_type',
                'ual.user_id',
                'ual.meta',
                'ual.is_read',
                'ual.created_at',
                'u.username',
                'u.email',
                'u.first_name',
                'u.last_name',
            ])
            ->orderByDesc('ual.created_at')
            ->limit($limit)
            ->get();

        $unreadCount = (clone $baseQuery)
            ->where('ual.is_read', 0)
            ->count();

        $notifications = $rows->map(function ($row) {
            $meta = [];
            if (!empty($row->meta)) {
                $decoded = json_decode($row->meta, true);
                if (is_array($decoded)) {
                    $meta = $decoded;
                }
            }

            $source = strtolower((string) ($meta['source'] ?? 'web'));
            if (!in_array($source, ['mobile', 'web'], true)) {
                $source = 'web';
            }

            $userName = $this->buildUserName(
                $row->first_name ?? null,
                $row->last_name ?? null,
                $row->username ?? null,
                $row->user_id ?? null
            );

            [$title, $message] = $this->formatNotificationText(
                (string) $row->activity_type,
                $userName,
                $source,
                $meta
            );

            return [
                'id' => (int) $row->id,
                'title' => $title,
                'message' => $message,
                'activity_type' => $row->activity_type,
                'user' => [
                    'id' => $row->user_id,
                    'name' => $userName,
                    'username' => $row->username,
                    'email' => $row->email,
                ],
                'source' => $source,
                'is_read' => (bool) $row->is_read,
                'created_at' => $row->created_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $adminId = $this->resolveAdminId();
        if (!$adminId || Session::get('userType') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $enabledTypes = $this->enabledActivityTypes($adminId);
        $ids = $request->input('ids');
        $markAll = (bool) $request->boolean('all', false);

        $query = DB::table('user_activity_logs')
            ->whereIn('activity_type', $enabledTypes);

        if (!$markAll && is_array($ids) && !empty($ids)) {
            $cleanIds = array_values(array_filter(array_map('intval', $ids), fn ($id) => $id > 0));
            if (!empty($cleanIds)) {
                $query->whereIn('id', $cleanIds);
            }
        }

        $query->update(['is_read' => 1]);

        return response()->json(['success' => true, 'message' => 'Notifications marked as read.']);
    }

    public function unreadCount(): JsonResponse
    {
        $adminId = $this->resolveAdminId();
        if (!$adminId || Session::get('userType') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $enabledTypes = $this->enabledActivityTypes($adminId);
        $count = 0;
        if (!empty($enabledTypes)) {
            $count = DB::table('user_activity_logs')
                ->whereIn('activity_type', $enabledTypes)
                ->where('is_read', 0)
                ->count();
        }

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $count],
        ]);
    }

    private function resolveAdminId(): ?string
    {
        $user = Session::get('user');
        if (is_object($user)) {
            return $user->admin_id ?? null;
        }
        if (is_array($user)) {
            return $user['admin_id'] ?? null;
        }
        return null;
    }

    private function enabledActivityTypes(string $adminId): array
    {
        $all = $this->allActivityTypes();

        $prefRows = DB::table('admin_notification_preferences')
            ->where('admin_id', $adminId)
            ->whereIn('setting_key', $all)
            ->get(['setting_key', 'is_enabled']);

        if ($prefRows->isEmpty()) {
            return $all;
        }

        $prefMap = [];
        foreach ($prefRows as $row) {
            $prefMap[$row->setting_key] = (bool) $row->is_enabled;
        }

        $enabled = [];
        foreach ($all as $type) {
            if (!array_key_exists($type, $prefMap) || $prefMap[$type]) {
                $enabled[] = $type;
            }
        }

        return $enabled;
    }

    private function allActivityTypes(): array
    {
        return [
            'user_registered',
            'failed_login_attempt',
            'project_reported',
            'profile_updated',
            'password_reset',
            'email_verified',
            'account_status_changed',
        ];
    }

    private function buildUserName(?string $firstName, ?string $lastName, ?string $username, $userId): string
    {
        $full = trim(((string) $firstName) . ' ' . ((string) $lastName));
        if ($full !== '') {
            return $full;
        }
        if (!empty($username)) {
            return (string) $username;
        }
        if (!empty($userId)) {
            return 'User #' . $userId;
        }
        return 'Unknown User';
    }

    private function formatNotificationText(string $type, string $userName, string $source, array $meta): array
    {
        $sourceLabel = $source === 'mobile' ? 'mobile application' : 'web platform';

        return match ($type) {
            'user_registered' => [
                'New User Registered',
                $userName . ' registered from the ' . $sourceLabel . '.',
            ],
            'failed_login_attempt' => [
                'Failed Login Attempt',
                $userName . ' had a failed login attempt from the ' . $sourceLabel . '.',
            ],
            'project_reported' => [
                'Project Reported',
                $userName . ' reported a project from the ' . $sourceLabel . '.',
            ],
            'profile_updated' => [
                'Profile Updated',
                $userName . ' updated their profile from the ' . $sourceLabel . '.',
            ],
            'password_reset' => [
                'Password Reset Activity',
                $userName . ' initiated a password reset from the ' . $sourceLabel . '.',
            ],
            'email_verified' => [
                'Email Verified',
                $userName . ' verified their email from the ' . $sourceLabel . '.',
            ],
            'account_status_changed' => [
                'Account Status Changed',
                $userName . ' account status was updated from the ' . $sourceLabel . '.',
            ],
            default => [
                'User Activity',
                $userName . ' performed an activity from the ' . $sourceLabel . '.',
            ],
        };
    }
}
