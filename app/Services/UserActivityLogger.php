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

    // ── Auth activity types ───────────────────────────────────────────────
    const USER_LOGIN             = 'user_login';
    const USER_LOGOUT            = 'user_logout';

    // ── Project activity types ────────────────────────────────────────────
    const PROJECT_CREATED        = 'project_created';
    const PROJECT_UPDATED        = 'project_updated';

    // ── Bid activity types ────────────────────────────────────────────────
    const BID_SUBMITTED          = 'bid_submitted';
    const BID_UPDATED            = 'bid_updated';
    const BID_CANCELLED          = 'bid_cancelled';
    const BID_ACCEPTED           = 'bid_accepted';
    const BID_REJECTED           = 'bid_rejected';

    // ── Project completion ─────────────────────────────────────────────────
    const PROJECT_COMPLETED      = 'project_completed';

    // ── Milestone activity types ──────────────────────────────────────────
    const MILESTONE_SUBMITTED    = 'milestone_submitted';
    const MILESTONE_UPDATED      = 'milestone_updated';
    const MILESTONE_DELETED      = 'milestone_deleted';
    const MILESTONE_APPROVED     = 'milestone_approved';
    const MILESTONE_REJECTED     = 'milestone_rejected';
    const MILESTONE_COMPLETED    = 'milestone_completed';

    // ── Progress & Payment activity types ─────────────────────────────────
    const PROGRESS_UPLOADED      = 'progress_uploaded';
    const PAYMENT_UPLOADED       = 'payment_uploaded';
    const DOWNPAYMENT_UPLOADED   = 'downpayment_uploaded';

    // ── Dispute activity types ────────────────────────────────────────────
    const DISPUTE_FILED          = 'dispute_filed';

    // ── Project update (extension) activity types ─────────────────────────
    const PROJECT_UPDATE_SUBMITTED = 'project_update_submitted';
    const PROJECT_UPDATE_APPROVED  = 'project_update_approved';
    const PROJECT_UPDATE_REJECTED  = 'project_update_rejected';
    const PROJECT_UPDATE_WITHDRAWN = 'project_update_withdrawn';

    // ── Showcase / Post activity types ────────────────────────────────────
    const POST_CREATED           = 'post_created';

    // ── Report activity types ────────────────────────────────────────────
    const USER_REPORTED          = 'user_reported';
    const MESSAGE_REPORTED       = 'message_reported';

    // ── Role resubmission ─────────────────────────────────────────────────
    const ROLE_RESUBMITTED       = 'role_resubmitted';

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

    // ── New convenience wrappers ──────────────────────────────────────────

    public static function userLogin(int $userId): void
    {
        self::log(self::USER_LOGIN, $userId);
    }

    public static function userLogout(int $userId): void
    {
        self::log(self::USER_LOGOUT, $userId);
    }

    public static function projectCreated(int $userId, int $projectId, string $title = ''): void
    {
        self::log(self::PROJECT_CREATED, $userId, $projectId, 'project', $title ? ['title' => $title] : []);
    }

    public static function projectUpdated(int $userId, int $projectId, string $title = ''): void
    {
        self::log(self::PROJECT_UPDATED, $userId, $projectId, 'project', $title ? ['title' => $title] : []);
    }

    public static function bidSubmitted(int $userId, int $bidId, int $projectId): void
    {
        self::log(self::BID_SUBMITTED, $userId, $bidId, 'bid', ['project_id' => $projectId]);
    }

    public static function bidUpdated(int $userId, int $bidId): void
    {
        self::log(self::BID_UPDATED, $userId, $bidId, 'bid');
    }

    public static function bidCancelled(int $userId, int $bidId): void
    {
        self::log(self::BID_CANCELLED, $userId, $bidId, 'bid');
    }

    public static function bidAccepted(int $userId, int $bidId, int $projectId): void
    {
        self::log(self::BID_ACCEPTED, $userId, $bidId, 'bid', ['project_id' => $projectId]);
    }

    public static function bidRejected(int $userId, int $bidId, int $projectId): void
    {
        self::log(self::BID_REJECTED, $userId, $bidId, 'bid', ['project_id' => $projectId]);
    }

    public static function projectCompleted(int $userId, int $projectId, string $title = ''): void
    {
        self::log(self::PROJECT_COMPLETED, $userId, $projectId, 'project', $title ? ['title' => $title] : []);
    }

    public static function milestoneSubmitted(int $userId, int $milestoneId, int $projectId): void
    {
        self::log(self::MILESTONE_SUBMITTED, $userId, $milestoneId, 'milestone', ['project_id' => $projectId]);
    }

    public static function milestoneUpdated(int $userId, int $milestoneId, int $projectId): void
    {
        self::log(self::MILESTONE_UPDATED, $userId, $milestoneId, 'milestone', ['project_id' => $projectId]);
    }

    public static function milestoneDeleted(int $userId, int $milestoneId): void
    {
        self::log(self::MILESTONE_DELETED, $userId, $milestoneId, 'milestone');
    }

    public static function milestoneApproved(int $userId, int $milestoneId): void
    {
        self::log(self::MILESTONE_APPROVED, $userId, $milestoneId, 'milestone');
    }

    public static function milestoneRejected(int $userId, int $milestoneId): void
    {
        self::log(self::MILESTONE_REJECTED, $userId, $milestoneId, 'milestone');
    }

    public static function milestoneCompleted(int $userId, int $milestoneId): void
    {
        self::log(self::MILESTONE_COMPLETED, $userId, $milestoneId, 'milestone');
    }

    public static function progressUploaded(int $userId, int $progressId, int $projectId): void
    {
        self::log(self::PROGRESS_UPLOADED, $userId, $progressId, 'progress', ['project_id' => $projectId]);
    }

    public static function paymentUploaded(int $userId, int $paymentId, int $projectId): void
    {
        self::log(self::PAYMENT_UPLOADED, $userId, $paymentId, 'payment', ['project_id' => $projectId]);
    }

    public static function downpaymentUploaded(int $userId, int $dpPaymentId, int $projectId): void
    {
        self::log(self::DOWNPAYMENT_UPLOADED, $userId, $dpPaymentId, 'payment', ['project_id' => $projectId]);
    }

    public static function disputeFiled(int $userId, int $disputeId, int $projectId, string $disputeType = ''): void
    {
        $meta = ['project_id' => $projectId];
        if ($disputeType) $meta['dispute_type'] = $disputeType;
        self::log(self::DISPUTE_FILED, $userId, $disputeId, 'dispute', $meta);
    }

    public static function projectUpdateSubmitted(int $userId, int $extensionId, int $projectId): void
    {
        self::log(self::PROJECT_UPDATE_SUBMITTED, $userId, $extensionId, 'project_update', ['project_id' => $projectId]);
    }

    public static function projectUpdateApproved(int $userId, int $extensionId, int $projectId): void
    {
        self::log(self::PROJECT_UPDATE_APPROVED, $userId, $extensionId, 'project_update', ['project_id' => $projectId]);
    }

    public static function projectUpdateRejected(int $userId, int $extensionId, int $projectId): void
    {
        self::log(self::PROJECT_UPDATE_REJECTED, $userId, $extensionId, 'project_update', ['project_id' => $projectId]);
    }

    public static function projectUpdateWithdrawn(int $userId, int $extensionId, int $projectId): void
    {
        self::log(self::PROJECT_UPDATE_WITHDRAWN, $userId, $extensionId, 'project_update', ['project_id' => $projectId]);
    }

    public static function postCreated(int $userId, int $postId): void
    {
        self::log(self::POST_CREATED, $userId, $postId, 'post');
    }

    public static function userReported(int $reporterId, int $reportId, int $reportedUserId): void
    {
        self::log(self::USER_REPORTED, $reporterId, $reportId, 'user_report', ['reported_user_id' => $reportedUserId]);
    }

    public static function messageReported(int $userId, int $messageId): void
    {
        self::log(self::MESSAGE_REPORTED, $userId, $messageId, 'message');
    }

    public static function roleResubmitted(int $userId, string $role): void
    {
        self::log(self::ROLE_RESUBMITTED, $userId, null, null, ['role' => $role]);
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