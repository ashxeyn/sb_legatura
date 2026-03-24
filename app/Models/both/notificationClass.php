<?php

namespace App\Models\both;

use Illuminate\Support\Facades\DB;

class notificationClass
{
    /**
     * Notification sub-types grouped by role.
     * Used to filter notifications for "both" users based on preferred_role.
     *
     * Sub-types are stored inside the action_link JSON as 'notification_sub_type'.
     * The DB 'type' enum is too broad (e.g. 'Bid Status' applies to both roles),
     * so filtering by sub-type is more accurate.
     */
    public const ROLE_SUB_TYPES = [
        'contractor' => [
            'bid_accepted',
            'bid_rejected',
            'milestone_submitted',
            'milestone_approved',
            'milestone_rejected',
            'milestone_completed',
            'milestone_item_completed',
            'milestone_deleted',
            'milestone_resubmitted',
            'milestone_updated',
            'payment_submitted',
            'payment_approved',
            'payment_rejected',
            'payment_updated',
            'payment_deleted',
            'payment_fully_paid',
            'payment_overpaid',
            'payment_underpaid_carry',
            'team_invite',
            'team_removed',
            'team_role_changed',
            'team_access_changed',
            'dispute_opened',
            'dispute_updated',
            'dispute_cancelled',
            'dispute_under_review',
            'dispute_resolved',
            'dispute_rejected',
            'project_completed',
            'project_halted',
            'project_terminated',
            'showcase_update',
            'subscription_expiring',
            'subscription_expired',
            // Progress outcomes are sent to the contractor, not the owner
            'progress_approved',
            'progress_rejected',
            'progress_updated',
            // Reminder sub-types
            'bid_deadline',
            'milestone_item_due',
            'milestone_item_overdue',
            'payment_due',
            'payment_overdue',
        ],
        'owner' => [
            'bid_received',
            'milestone_submitted',
            'milestone_resubmitted',
            'milestone_updated',
            'progress_submitted',
            'progress_approved',
            'progress_rejected',
            'progress_updated',
            'payment_due',
            'payment_overdue',
            'payment_submitted',
            'payment_approved',
            'payment_rejected',
            'payment_updated',
            'payment_deleted',
            'payment_fully_paid',
            'payment_overpaid',
            'payment_underpaid_carry',
            'project_completed',
            'project_halted',
            'project_terminated',
            'project_update',
            'showcase_update',
            'dispute_opened',
            'dispute_updated',
            'dispute_cancelled',
            'dispute_under_review',
            'dispute_resolved',
            'dispute_rejected',
            'boost_expiring',
            'boost_expired',
            // Reminder sub-types
            'milestone_item_due',
            'milestone_item_overdue',
        ],
    ];
    /**
     * Insert a new notification row and return its ID.
     */
    public function insert(array $data): int
    {
        return DB::table('notifications')->insertGetId($data);
    }

    /**
     * Fetch a single notification by its primary key.
     */
    public function getById(int $notificationId): ?object
    {
        return DB::table('notifications')
            ->where('notification_id', $notificationId)
            ->first();
    }

    /**
     * Paginated notifications for a user (newest first).
     *
     * @param string|null $role  'contractor' or 'owner' — when provided, only
     *                           notifications whose sub-type matches the role are
     *                           returned. Null returns all notifications.
     */
    public function getByUserId(int $userId, int $page = 1, int $perPage = 20, ?string $role = null): array
    {
        $offset = ($page - 1) * $perPage;

        $baseQuery = DB::table('notifications')->where('user_id', $userId);

        if ($role !== null && isset(self::ROLE_SUB_TYPES[$role])) {
            $baseQuery = $this->applyRoleFilter($baseQuery, $role);
        }

        $total = (clone $baseQuery)->count();

        $rows = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        return [
            'notifications' => $rows,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $perPage,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Count unread notifications for a user.
     *
     * @param string|null $role  When provided, only counts unread notifications
     *                           relevant to that role.
     */
    public function getUnreadCount(int $userId, ?string $role = null): int
    {
        $query = DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', 0);

        if ($role !== null && isset(self::ROLE_SUB_TYPES[$role])) {
            $query = $this->applyRoleFilter($query, $role);
        }

        return $query->count();
    }

    /**
     * Mark a single notification as read (only if it belongs to the user).
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $affected = DB::table('notifications')
            ->where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->update(['is_read' => 1]);

        return $affected > 0;
    }

    /**
     * Mark all of a user's notifications as read. Returns affected count.
     *
     * @param string|null $role  When provided, only marks notifications for
     *                           that role as read. The other role's notifications
     *                           remain untouched.
     */
    public function markAllAsRead(int $userId, ?string $role = null): int
    {
        $query = DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', 0);

        if ($role !== null && isset(self::ROLE_SUB_TYPES[$role])) {
            $query = $this->applyRoleFilter($query, $role);
        }

        return $query->update(['is_read' => 1]);
    }

    /**
     * Check if a dedup_key already exists for a user.
     */
    public function dedupExists(int $userId, string $dedupKey): bool
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->where('dedup_key', $dedupKey)
            ->exists();
    }

    /**
     * Delete read notifications older than the given number of days.
     */
    public function cleanupOld(int $days = 90): int
    {
        return DB::table('notifications')
            ->where('is_read', 1)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    // ─── Role-based filtering helper ───────────────────────────────────

    /**
     * Apply a WHERE clause that limits results to notifications whose
     * action_link JSON contains a sub-type belonging to the given role.
     *
     * Sub-types are stored inside action_link as:
     *   {"notification_sub_type":"bid_accepted", ...}
     *
     * We use a set of LIKE conditions to match against the role's sub-types.
     * Notifications without a recognisable sub-type are included for both roles
     * so nothing gets silently hidden.
     */
    private function applyRoleFilter($query, string $role)
    {
        $subTypes = self::ROLE_SUB_TYPES[$role] ?? [];

        if (empty($subTypes)) {
            return $query;
        }

        return $query->where(function ($q) use ($subTypes) {
            foreach ($subTypes as $subType) {
                // Match the JSON fragment: "notification_sub_type":"<subType>"
                $q->orWhere('action_link', 'LIKE', '%"notification_sub_type":"' . $subType . '"%');
            }
            // Include notifications that have no sub-type (legacy / general)
            $q->orWhereNull('action_link');
            $q->orWhere('action_link', '');
            $q->orWhere('action_link', 'NOT LIKE', '%notification_sub_type%');
        });
    }
}
