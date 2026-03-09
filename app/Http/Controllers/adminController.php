<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\user;
use App\Services\NotificationService;
use App\Services\UserActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class adminController extends Controller
{

    // ─── Account Suspension Example (Integration) ─────────────────────────────
    // Example: Call this after suspending a contractor or property owner
    // UserActivityLogger::accountStatusChanged($userId, 'suspended', $reason);
    // Example: Call this after unsuspending
    // UserActivityLogger::accountStatusChanged($userId, 'unsuspended');
    // Example: Call this after deleting/soft-deleting
    // UserActivityLogger::accountStatusChanged($userId, 'deleted', $reason);

    // Add the above calls in your actual suspend/unsuspend/delete logic as needed.

    public function notificationSettings()
    {
        return view('admin.settings.notifications');
    }

    // ─── Load Users for Targeted Dropdown (AJAX) ──────────────────────────────

    /**
     * GET /admin/notifications/users
     * Returns a lightweight list of all users (id, username, email, user_type).
     */
    public function getUsersForNotification(Request $request)
    {
        $this->ensureAdmin();

        $search = $request->query('search', '');

        $query = DB::table('users')
            ->select('user_id', 'username', 'email', 'user_type')
            ->orderBy('username');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->limit(200)->get();

        return response()->json(['success' => true, 'data' => $users]);
    }

    // ─── Send Mass Announcement ────────────────────────────────────────────────

    /**
     * POST /admin/notifications/send-announcement
     */
    public function sendAnnouncement(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'title'           => 'required|string|max:255',
            'message'         => 'required|string',
            'delivery_method' => 'required|in:in-app,email,both',
        ]);

        $title          = $request->input('title');
        $message        = $request->input('message');
        $deliveryMethod = $request->input('delivery_method');

        $users    = DB::table('users')->select('user_id', 'email')->get();
        $userIds  = $users->pluck('user_id')->toArray();
        $count    = count($userIds);

        // In-App
        if (in_array($deliveryMethod, ['in-app', 'both'])) {
            NotificationService::createForUsers(
                $userIds,
                'admin_announcement',
                $title,
                $message,
                'high',
                'announcement',
                null,
                ['screen' => 'Announcements'],
                'admin_announcement_' . time()
            );
        }

        // Email
        if (in_array($deliveryMethod, ['email', 'both'])) {
            foreach ($users as $user) {
                if (!empty($user->email)) {
                    try {
                        Mail::raw($message, function ($mail) use ($user, $title) {
                            $mail->to($user->email)->subject($title);
                        });
                    } catch (\Throwable $e) {
                        Log::warning('Admin announcement email failed for user ' . $user->user_id . ': ' . $e->getMessage());
                    }
                }
            }
        }

        // Log the send
        $this->logSentNotification($title, $message, $deliveryMethod, 'all', $userIds);

        return response()->json([
            'success'          => true,
            'message'          => "Announcement sent to {$count} users.",
            'recipient_count'  => $count,
        ]);
    }

    // ─── Send Targeted Notification ───────────────────────────────────────────

    /**
     * POST /admin/notifications/send-targeted
     */
    public function sendTargetedNotification(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'user_ids'        => 'required|array|min:1',
            'user_ids.*'      => 'integer',
            'title'           => 'required|string|max:255',
            'message'         => 'required|string',
            'delivery_method' => 'required|in:in-app,email,both',
        ]);

        $userIds        = $request->input('user_ids');
        $title          = $request->input('title');
        $message        = $request->input('message');
        $deliveryMethod = $request->input('delivery_method');

        $users = DB::table('users')
            ->whereIn('user_id', $userIds)
            ->select('user_id', 'email')
            ->get();

        $foundIds = $users->pluck('user_id')->toArray();
        $count    = count($foundIds);

        // In-App
        if (in_array($deliveryMethod, ['in-app', 'both'])) {
            NotificationService::createForUsers(
                $foundIds,
                'admin_announcement',
                $title,
                $message,
                'high',
                'targeted',
                null,
                ['screen' => 'Announcements'],
                'admin_targeted_' . time()
            );
        }

        // Email
        if (in_array($deliveryMethod, ['email', 'both'])) {
            foreach ($users as $user) {
                if (!empty($user->email)) {
                    try {
                        Mail::raw($message, function ($mail) use ($user, $title) {
                            $mail->to($user->email)->subject($title);
                        });
                    } catch (\Throwable $e) {
                        Log::warning('Admin targeted email failed for user ' . $user->user_id . ': ' . $e->getMessage());
                    }
                }
            }
        }

        // Log the send
        $this->logSentNotification($title, $message, $deliveryMethod, 'targeted', $foundIds);

        return response()->json([
            'success'         => true,
            'message'         => "Notification sent to {$count} user(s).",
            'recipient_count' => $count,
        ]);
    }

    // ─── Notification Preferences ─────────────────────────────────────────────

    /**
     * GET /admin/notifications/preferences
     * Returns the current admin's preference map as { setting_key: bool }
     */
    public function getPreferences(Request $request)
    {
        $adminId = $this->resolveAdminId();
        if (!$adminId) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $rows = DB::table('admin_notification_preferences')
            ->where('admin_id', $adminId)
            ->get(['setting_key', 'is_enabled']);

        $prefs = [];
        foreach ($rows as $row) {
            $prefs[$row->setting_key] = (bool) $row->is_enabled;
        }

        // Ensure all known keys exist (default true)
        foreach ($this->allSettingKeys() as $key) {
            if (!array_key_exists($key, $prefs)) {
                $prefs[$key] = true;
            }
        }

        return response()->json(['success' => true, 'data' => $prefs]);
    }

    /**
     * POST /admin/notifications/preferences
     * Body: { settings: { setting_key: bool, ... } }
     */
    public function savePreferences(Request $request)
    {
        $adminId = $this->resolveAdminId();
        if (!$adminId) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'boolean',
        ]);

        $settings = $request->input('settings');
        $now      = now();

        foreach ($settings as $key => $enabled) {
            if (!in_array($key, $this->allSettingKeys(), true)) {
                continue; // ignore unknown keys
            }
            DB::table('admin_notification_preferences')
                ->updateOrInsert(
                    ['admin_id' => $adminId, 'setting_key' => $key],
                    ['is_enabled' => $enabled ? 1 : 0, 'updated_at' => $now]
                );
        }

        return response()->json(['success' => true, 'message' => 'Preferences saved.']);
    }

    // ─── Sent Notifications Log ───────────────────────────────────────────────

    /**
     * GET /admin/notifications/sent-log
     * Returns paginated list of notifications sent by this admin.
     */
    public function getSentLog(Request $request)
    {
        $adminId = $this->resolveAdminId();
        if (!$adminId) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $page    = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));

        $total = DB::table('admin_sent_notifications')
            ->where('admin_id', $adminId)
            ->count();

        $rows = DB::table('admin_sent_notifications')
            ->where('admin_id', $adminId)
            ->orderByDesc('sent_at')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $rows,
                'total'         => $total,
                'current_page'  => $page,
                'last_page'     => (int) ceil($total / $perPage),
                'per_page'      => $perPage,
            ],
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function logSentNotification(
        string $title,
        string $message,
        string $deliveryMethod,
        string $targetType,
        array  $userIds
    ): void {
        $adminId = $this->resolveAdminId();
        if (!$adminId) return;

        try {
            DB::table('admin_sent_notifications')->insert([
                'admin_id'        => $adminId,
                'title'           => $title,
                'message'         => $message,
                'delivery_method' => $deliveryMethod,
                'target_type'     => $targetType,
                'target_user_ids' => implode(',', $userIds),
                'recipient_count' => count($userIds),
                'sent_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Admin notification log insert failed: ' . $e->getMessage());
        }
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

    private function ensureAdmin(): void
    {
        $adminId = $this->resolveAdminId();
        if (!$adminId || Session::get('userType') !== 'admin') {
            abort(403, 'Admin access only.');
        }
    }

    private function allSettingKeys(): array
    {
        return [
            'user_registered',
            'failed_login_attempt',
            'project_reported',
            'profile_updated',
            'password_reset',
            'email_verified',
            'account_status_changed',
            'channel_email',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    //  USER ACTIVITY LOG
    // ─────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/notifications/activity
     *
     * Query params:
     *   type        – filter by activity_type (optional)
     *   is_read     – 0 | 1 | '' (optional)
     *   search      – search username/email (optional)
     *   page        – page number (default 1)
     *   per_page    – rows per page (default 20, max 50)
     *
     * Returns paginated activity rows with user info joined.
     */
    public function getUserActivityLogs(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $perPage = min((int) ($request->input('per_page', 20)), 50);
        $page    = max((int) ($request->input('page', 1)), 1);
        $type    = $request->input('type');
        $isRead  = $request->input('is_read');
        $search  = trim($request->input('search', ''));

        $query = DB::table('user_activity_logs AS ual')
            ->leftJoin('users AS u', 'u.user_id', '=', 'ual.user_id')
            ->select([
                'ual.id',
                'ual.activity_type',
                'ual.user_id',
                'ual.subject_id',
                'ual.subject_type',
                'ual.meta',
                'ual.is_read',
                'ual.created_at',
                'u.username',
                'u.email',
                'u.user_type',
            ])
            ->orderBy('ual.created_at', 'desc');

        if ($type && in_array($type, $this->allActivityTypes(), true)) {
            $query->where('ual.activity_type', $type);
        }

        if ($isRead !== null && $isRead !== '') {
            $query->where('ual.is_read', (int) $isRead);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('u.username', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%");
            });
        }

        $total     = $query->count();
        $offset    = ($page - 1) * $perPage;
        $rows      = $query->offset($offset)->limit($perPage)->get();
        $lastPage  = max(1, (int) ceil($total / $perPage));

        // Decode meta JSON for each row
        $rows = $rows->map(function ($row) {
            $row->meta = $row->meta ? json_decode($row->meta, true) : [];
            return $row;
        });

        // Unread count (always fresh, ignores filters)
        $unreadCount = DB::table('user_activity_logs')->where('is_read', 0)->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'activities'   => $rows,
                'total'        => $total,
                'current_page' => $page,
                'last_page'    => $lastPage,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * POST /admin/notifications/activity/mark-read
     *
     * Body: { ids: [1,2,3] }  — or omit `ids` to mark ALL as read.
     */
    public function markActivityRead(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $ids = $request->input('ids');

        $query = DB::table('user_activity_logs');

        if (!empty($ids) && is_array($ids)) {
            $ids   = array_map('intval', $ids);
            $query->whereIn('id', $ids);
        }

        $query->update(['is_read' => 1]);

        return response()->json(['success' => true, 'message' => 'Marked as read.']);
    }

    /**
     * Helper – list of valid activity_type enum values.
     */
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
}