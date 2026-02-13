<?php

namespace App\Models\both;

use Illuminate\Support\Facades\DB;

class notificationClass
{
    /**
     * Insert a new notification row and return its ID.
     */
    public function insert(array $data): int
    {
        return DB::table('notifications')->insertGetId($data);
    }

    /**
     * Paginated notifications for a user (newest first).
     */
    public function getByUserId(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $total = DB::table('notifications')
            ->where('user_id', $userId)
            ->count();

        $rows = DB::table('notifications')
            ->where('user_id', $userId)
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
     */
    public function getUnreadCount(int $userId): int
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->count();
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
     */
    public function markAllAsRead(int $userId): int
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
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
}
