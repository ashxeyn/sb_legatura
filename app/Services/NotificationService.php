<?php

namespace App\Services;

use App\Models\Both\notificationClass;
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
    ];

    /**
     * Create a single notification for one user.
     *
     * @param int         $userId        Recipient user_id
     * @param string      $subType       Granular type key, e.g. 'bid_accepted'
     * @param string      $title         Short headline
     * @param string      $message       Full notification text
     * @param string      $priority      'critical' | 'high' | 'normal'
     * @param string|null $referenceType e.g. 'bid', 'milestone', 'payment', 'dispute'
     * @param int|null    $referenceId   PK of the related record
     * @param array|null  $actionData    Navigation metadata: ['screen' => ..., 'params' => [...]]
     * @param string|null $dedupKey      Optional dedup key for scheduled notifications
     *
     * @return int|null notification_id or null if skipped (dedup)
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

            return $notificationId;
        } catch (\Illuminate\Database\QueryException $e) {
            // Duplicate dedup_key — silently skip
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'idx_dedup')) {
                Log::info('Notification dedup skip', ['user_id' => $userId, 'dedup_key' => $dedupKey]);
                return null;
            }
            Log::error('notificationService::create failed', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
                'type'    => $subType,
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('notificationService::create failed', [
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
            'created_at'     => $row->created_at,
        ];
    }
}
