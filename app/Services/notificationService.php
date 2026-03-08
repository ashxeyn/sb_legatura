<?php

namespace App\Services;

use App\Models\both\notificationClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class notificationService
{
    /**
     * Map a reference_type + context into a frontend-friendly notification sub-type.
     * The DB stores broad enum categories; this mapping produces the granular
     * type strings the React Native frontend expects.
     */
    private static array $frontendTypeMap = [
        'bid_accepted'        => 'Bid Status',
        'bid_rejected'        => 'Bid Status',
        'bid_received'        => 'Bid Status',
        'milestone_submitted' => 'Milestone Update',
        'milestone_approved'  => 'Milestone Update',
        'milestone_rejected'  => 'Milestone Update',
        'milestone_completed' => 'Milestone Update',
        'milestone_item_completed' => 'Milestone Update',
        'milestone_deleted'   => 'Milestone Update',
        'milestone_resubmitted' => 'Milestone Update',
        'milestone_updated'   => 'Milestone Update',
        'progress_submitted'  => 'Progress Update',
        'progress_approved'   => 'Progress Update',
        'progress_rejected'   => 'Progress Update',
        'progress_updated'    => 'Progress Update',
        'payment_submitted'   => 'Payment Status',
        'payment_approved'    => 'Payment Status',
        'payment_rejected'    => 'Payment Status',
        'payment_updated'     => 'Payment Status',
        'payment_deleted'     => 'Payment Status',
        'payment_due'         => 'Payment Reminder',
        'payment_overdue'     => 'Payment Reminder',
        'payment_fully_paid'  => 'Payment Status',
        'payment_overpaid'    => 'Payment Status',
        'payment_underpaid_carry' => 'Payment Status',
        'dispute_opened'      => 'Dispute Update',
        'dispute_updated'     => 'Dispute Update',
        'dispute_cancelled'   => 'Dispute Update',
        'dispute_under_review' => 'Dispute Update',
        'dispute_resolved'    => 'Dispute Update',
        'dispute_rejected'    => 'Dispute Update',
        'project_completed'   => 'Project Alert',
        'project_halted'      => 'Project Alert',
        'project_terminated'  => 'Project Alert',
        'project_update'      => 'Project Alert',
        'team_invite'         => 'Team Update',
        'team_removed'        => 'Team Update',
        'team_role_changed'   => 'Team Update',
        'team_access_changed' => 'Team Update',
        'review_prompt'       => 'Project Alert',
        'review_submitted'    => 'Project Alert',

        // Subscription & boost expiry
        'subscription_expiring' => 'Payment Reminder',
        'subscription_expired'  => 'Payment Reminder',
        'boost_expiring'        => 'Payment Reminder',
        'boost_expired'         => 'Payment Reminder',

        // Reminder-specific sub-types (distinct from action/event sub-types)
        'bid_deadline'           => 'Bid Status',
        'milestone_item_due'     => 'Milestone Update',
        'milestone_item_overdue' => 'Milestone Update',
    ];

    /**
     * Map each sub-type to its intended recipient role.
     *
     * This tells the system WHO should see each notification type:
     * - 'contractor' → the contractor working on a project
     * - 'owner' → the property owner who posted the project
     * - 'both' → both roles can see it (shared types like disputes, project lifecycle)
     *
     * Used by notificationClass::ROLE_SUB_TYPES for query filtering.
     */
    public static array $subTypeRoleMap = [
        // Bid notifications
        'bid_accepted'        => 'contractor',  // contractor's bid was accepted
        'bid_rejected'        => 'contractor',  // contractor's bid was rejected
        'bid_received'        => 'owner',       // owner received a new bid

        // Milestone notifications — contractor does the work, owner reviews
        'milestone_submitted'      => 'both',
        'milestone_approved'       => 'contractor',
        'milestone_rejected'       => 'contractor',
        'milestone_completed'      => 'contractor',
        'milestone_item_completed' => 'contractor',
        'milestone_deleted'        => 'contractor',
        'milestone_resubmitted'    => 'both',
        'milestone_updated'        => 'both',

        // Progress notifications — owner reviews progress
        'progress_submitted'  => 'owner',
        'progress_approved'   => 'owner',
        'progress_rejected'   => 'owner',
        'progress_updated'    => 'owner',

        // Payment notifications — both roles are involved
        'payment_submitted'       => 'both',
        'payment_approved'        => 'both',
        'payment_rejected'        => 'both',
        'payment_updated'         => 'both',
        'payment_deleted'         => 'both',
        'payment_fully_paid'      => 'both',
        'payment_overpaid'        => 'both',
        'payment_underpaid_carry' => 'both',
        'payment_due'             => 'owner',    // owner needs to pay
        'payment_overdue'         => 'owner',    // owner overdue on payment

        // Dispute notifications — both parties involved
        'dispute_opened'       => 'both',
        'dispute_updated'      => 'both',
        'dispute_cancelled'    => 'both',
        'dispute_under_review' => 'both',
        'dispute_resolved'     => 'both',
        'dispute_rejected'     => 'both',

        // Project lifecycle — both roles are involved
        'project_completed'  => 'both',
        'project_halted'     => 'both',
        'project_terminated' => 'both',
        'project_update'     => 'owner',  // general project updates for owner

        // Team notifications — contractor's team
        'team_invite'         => 'contractor',
        'team_removed'        => 'contractor',
        'team_role_changed'   => 'contractor',
        'team_access_changed' => 'contractor',

        // Subscription & boost expiry
        'subscription_expiring' => 'contractor',
        'subscription_expired'  => 'contractor',
        'boost_expiring'        => 'owner',
        'boost_expired'         => 'owner',

        // Reminder-specific sub-types
        'bid_deadline'           => 'contractor',
        'milestone_item_due'     => 'both',
        'milestone_item_overdue' => 'both',
    ];

    /**
     * Create a single notification for one user, and send email if available.
     */
    public static function create(
        int     $userId,
        string  $subType,
        string  $title,
        string  $message,
        string  $priority = 'normal',
        ?string $referenceType = null,
        ?int    $referenceId = null,
        ?array  $actionData = null,
        ?string $dedupKey = null
    ): ?int {
        try {
            $model = new notificationClass();

            // Deduplication check
            if ($dedupKey !== null) {
                if ($model->dedupExists($userId, $dedupKey)) {
                    return null; // already sent
                }
            }

            // Resolve DB enum type from sub-type
            $dbType = self::$frontendTypeMap[$subType] ?? 'Project Alert';

            $actionLink = $actionData ? json_encode(array_merge(
                $actionData,
                ['notification_sub_type' => $subType]
            )) : json_encode(['notification_sub_type' => $subType]);

            $notificationId = $model->insert([
                'user_id'         => $userId,
                'message'         => $message,
                'title'           => $title,
                'type'            => $dbType,
                'is_read'         => 0,
                'delivery_method' => 'App',
                'priority'        => $priority,
                'reference_type'  => $referenceType,
                'reference_id'    => $referenceId,
                'dedup_key'       => $dedupKey,
                'action_link'     => $actionLink,
                'created_at'      => now(),
            ]);

            Log::info('Notification created', [
                'notification_id' => $notificationId,
                'user_id'         => $userId,
                'sub_type'        => $subType,
                'priority'        => $priority,
            ]);

            // Send email if user has email
            $user = \App\Models\User::find($userId);
            if ($user && $user->email) {
                \Mail::raw($message, function($mailMessage) use ($user, $title) {
                    $mailMessage->to($user->email)
                               ->subject($title);
                });
            }

            return $notificationId;
        } catch (\Illuminate\Database\QueryException $e) {
            // Duplicate dedup_key — silently skip
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'idx_dedup')) {
                Log::info('Notification dedup skip', ['user_id' => $userId, 'dedup_key' => $dedupKey]);
                return null;
            }
            Log::error('NotificationService::create failed', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
                'type'    => $subType,
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('NotificationService::create failed', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
                'type'    => $subType,
            ]);
            return null;
        }
    }

    /**
     * Create the same notification for multiple users.
     */
    public static function createForUsers(
        array   $userIds,
        string  $subType,
        string  $title,
        string  $message,
        string  $priority = 'normal',
        ?string $referenceType = null,
        ?int    $referenceId = null,
        ?array  $actionData = null,
        ?string $dedupKeyPrefix = null
    ): void {
        foreach ($userIds as $userId) {
            $dedupKey = $dedupKeyPrefix ? "{$dedupKeyPrefix}_{$userId}" : null;
            self::create($userId, $subType, $title, $message, $priority, $referenceType, $referenceId, $actionData, $dedupKey);
        }
    }

    /**
     * Map a DB notification row to the frontend-expected JSON shape.
     */
    public static function formatForFrontend(object $row): array
    {
        // Determine the frontend sub-type from action_link JSON
        $subType = 'general';
        $actionData = null;
        if ($row->action_link) {
            $decoded = json_decode($row->action_link, true);
            if (is_array($decoded)) {
                $subType = $decoded['notification_sub_type'] ?? 'general';
                $actionData = $decoded;
                unset($actionData['notification_sub_type']);
            }
        }

        return [
            'id'             => $row->notification_id,
            'type'           => $subType,
            'title'          => $row->title ?? '',
            'message'        => $row->message,
            'is_read'        => (bool) $row->is_read,
            'priority'       => $row->priority ?? 'normal',
            'reference_type' => $row->reference_type,
            'reference_id'   => $row->reference_id,
            'action_url'     => $row->action_link,
            'redirect_url'   => "/api/notifications/{$row->notification_id}/redirect",
            'notification_role' => self::$subTypeRoleMap[$subType] ?? 'both',
            'created_at'     => $row->created_at,
        ];
    }
}
