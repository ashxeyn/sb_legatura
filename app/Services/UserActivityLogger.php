<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * UserActivityLogger
 *
 * Central service for writing to the `user_activity_logs` table.
 * Call UserActivityLogger::log() from any controller after a user action.
 *
 * Usage examples are in hooks_guide.md
 */
class UserActivityLogger
{
    // ── Valid activity types ───────────────────────────────────────────────
    const USER_REGISTERED        = 'user_registered';
    const FAILED_LOGIN_ATTEMPT   = 'failed_login_attempt';
    const PROJECT_REPORTED       = 'project_reported';
    const PROFILE_UPDATED        = 'profile_updated';
    const PASSWORD_RESET         = 'password_reset';
    const EMAIL_VERIFIED         = 'email_verified';
    const ACCOUNT_STATUS_CHANGED = 'account_status_changed';

    /**
     * Write a user activity log entry.
     *
     * @param  string      $activityType  One of the class constants above
     * @param  int|null    $userId        The user who triggered the event (users.user_id)
     * @param  int|null    $subjectId     Related entity id (dispute_id, project_id, etc.)
     * @param  string|null $subjectType   'dispute' | 'project' | 'user' | null
     * @param  array       $meta          Extra context: ['ip' => ..., 'attempts' => ..., etc.]
     */
    public static function log(
        string  $activityType,
        ?int    $userId      = null,
        ?int    $subjectId   = null,
        ?string $subjectType = null,
        array   $meta        = []
    ): void {
        try {
            // Auto-append the request IP if not already in meta
            if (!isset($meta['ip'])) {
                $meta['ip'] = Request::ip();
            }

            if (!isset($meta['source'])) {
                $meta['source'] = self::detectSource();
            }

            $activityId = DB::table('user_activity_logs')->insertGetId([
                'activity_type' => $activityType,
                'user_id'       => $userId,
                'subject_id'    => $subjectId,
                'subject_type'  => $subjectType,
                'meta'          => !empty($meta) ? json_encode($meta) : null,
                'is_read'       => 0,
                'created_at'    => now(),
            ]);

            self::recordAdminNotificationGeneration(
                $activityType,
                $activityId,
                $userId,
                $meta
            );
        } catch (\Throwable $e) {
            // Never let logging crash the main request
            Log::warning('UserActivityLogger::log failed: ' . $e->getMessage());
        }
    }

    // ── Convenience wrappers ──────────────────────────────────────────────

    /**
     * New user registered.
     *
     * @param int $userId  The newly created users.user_id
     */
    public static function userRegistered(int $userId): void
    {
        self::log(self::USER_REGISTERED, $userId);
    }

    /**
     * Failed login attempt.
     *
     * @param int|null $userId   Resolved user id (null if username didn't match)
     * @param int      $attempts Number of consecutive failures
     * @param string   $ip       Request IP
     */
    public static function failedLogin(?int $userId, int $attempts = 1, string $ip = ''): void
    {
        self::log(self::FAILED_LOGIN_ATTEMPT, $userId, null, null, [
            'attempts' => $attempts,
            'ip'       => $ip ?: Request::ip(),
        ]);
    }

    /**
     * A project was reported (dispute filed against a project).
     *
     * @param int $userId     User who filed the report
     * @param int $disputeId  The disputes.dispute_id
     * @param int $projectId  The projects.project_id
     */
    public static function projectReported(int $userId, int $disputeId, int $projectId): void
    {
        self::log(self::PROJECT_REPORTED, $userId, $disputeId, 'dispute', [
            'project_id' => $projectId,
        ]);
    }

    /**
     * User updated their profile (name, bio, phone, etc.).
     *
     * @param int    $userId  The user who made the change
     * @param string $field   Optional: which field changed (e.g. 'email', 'phone')
     */
    public static function profileUpdated(int $userId, string $field = ''): void
    {
        $meta = [];
        if ($field) $meta['field'] = $field;
        self::log(self::PROFILE_UPDATED, $userId, null, null, $meta);
    }

    /**
     * Password reset requested or completed.
     *
     * @param int    $userId  The user requesting the reset
     * @param string $stage   'requested' | 'completed'
     */
    public static function passwordReset(int $userId, string $stage = 'requested'): void
    {
        self::log(self::PASSWORD_RESET, $userId, null, null, ['stage' => $stage]);
    }

    /**
     * User verified their email address.
     *
     * @param int $userId
     */
    public static function emailVerified(int $userId): void
    {
        self::log(self::EMAIL_VERIFIED, $userId);
    }

    /**
     * Account was suspended or unsuspended (or any status change).
     *
     * @param int    $userId     The affected user
     * @param string $newStatus  'suspended' | 'active' | 'unsuspended' | 'deleted' | etc.
     * @param string $reason     Optional moderation reason
     */
    public static function accountStatusChanged(int $userId, string $newStatus, string $reason = ''): void
    {
        $meta = ['new_status' => $newStatus];
        if ($reason) $meta['reason'] = $reason;
        self::log(self::ACCOUNT_STATUS_CHANGED, $userId, null, null, $meta);
    }

    private static function detectSource(): string
    {
        $explicit = Request::header('X-Client-Source') ?: Request::header('X-App-Platform');
        if (is_string($explicit) && trim($explicit) !== '') {
            $value = strtolower(trim($explicit));
            if (str_contains($value, 'mobile') || str_contains($value, 'android') || str_contains($value, 'ios')) {
                return 'mobile';
            }
        }

        $userAgent = strtolower((string) Request::header('User-Agent', ''));
        if (
            str_contains($userAgent, 'okhttp') ||
            str_contains($userAgent, 'flutter') ||
            str_contains($userAgent, 'dart') ||
            str_contains($userAgent, 'android') ||
            str_contains($userAgent, 'iphone')
        ) {
            return 'mobile';
        }

        return 'web';
    }

    private static function recordAdminNotificationGeneration(string $activityType, int $activityId, ?int $userId, array $meta): void
    {
        try {
            $source = strtolower((string) ($meta['source'] ?? 'web'));
            $adminIds = DB::table('admin_users')
                ->where('is_active', 1)
                ->pluck('admin_id')
                ->all();

            if (empty($adminIds)) {
                return;
            }

            $prefRows = DB::table('admin_notification_preferences')
                ->whereIn('admin_id', $adminIds)
                ->where('setting_key', $activityType)
                ->get(['admin_id', 'is_enabled']);

            $prefMap = [];
            foreach ($prefRows as $row) {
                $prefMap[$row->admin_id] = (bool) $row->is_enabled;
            }

            $now = now();
            $ip = Request::ip();
            $rows = [];

            foreach ($adminIds as $adminId) {
                if (array_key_exists($adminId, $prefMap) && $prefMap[$adminId] === false) {
                    continue;
                }

                $rows[] = [
                    'admin_id' => $adminId,
                    'action' => 'user_activity_notification_generated',
                    'details' => json_encode([
                        'activity_type' => $activityType,
                        'activity_id' => $activityId,
                        'user_id' => $userId,
                        'source' => $source,
                    ]),
                    'ip_address' => $ip,
                    'created_at' => $now,
                ];
            }

            if (!empty($rows)) {
                DB::table('admin_activity_logs')->insert($rows);
            }
        } catch (\Throwable $e) {
            Log::warning('UserActivityLogger::recordAdminNotificationGeneration failed: ' . $e->getMessage());
        }
    }
}