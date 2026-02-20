<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * milestoneService — Unified business logic for milestone setup, approval,
 * rejection, completion, and item management.
 *
 * Previously scattered across:
 *   • owner\projectsController  (apiApproveMilestone, apiRejectMilestone,
 *                                apiSetMilestoneComplete, apiSetMilestoneItemComplete)
 *   • contractor\cprocessController (apiSubmitMilestones, apiUpdateMilestone,
 *                                    deleteMilestone)
 *   • both\disputeController    (approveMilestone, rejectMilestone)
 */
class milestoneService
{
    // ─────────────────────────────────────────────────────────────────────────
    // OWNER — Approve milestone setup
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Approve a milestone setup submitted by the contractor.
     * Also transitions the project to in_progress.
     *
     * @return array{success: bool, message: string, status?: int}
     */
    public function approve(int $milestoneId, int $ownerId): array
    {
        $milestone = DB::table('milestones as m')
            ->join('projects as p', 'm.project_id', '=', 'p.project_id')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->where('m.milestone_id', $milestoneId)
            ->where('pr.owner_id', $ownerId)
            ->select('m.*', 'p.project_id')
            ->first();

        if (!$milestone) {
            return ['success' => false, 'message' => 'Milestone not found or unauthorized.', 'status' => 404];
        }

        DB::table('milestones')
            ->where('milestone_id', $milestoneId)
            ->update(['setup_status' => 'approved', 'updated_at' => now()]);

        // Transition project to in_progress
        DB::table('projects')
            ->where('project_id', $milestone->project_id)
            ->whereNotIn('project_status', ['completed', 'terminated', 'deleted', 'halt'])
            ->update(['project_status' => 'in_progress', 'updated_at' => now()]);

        // Notify contractor
        $cUserId = DB::table('contractor_users')
            ->where('contractor_id', $milestone->contractor_id)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->value('user_id');
        if ($cUserId) {
            $msName = $milestone->milestone_name ?? 'Milestone';
            notificationService::create(
                $cUserId,
                'milestone_approved',
                'Milestone Approved',
                "Your milestone \"{$msName}\" has been approved by the owner.",
                'normal',
                'milestone',
                $milestoneId,
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => $milestone->project_id, 'tab' => 'milestones']]
            );
        }

        return ['success' => true, 'message' => 'Milestone approved successfully.'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OWNER — Reject milestone setup
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Reject a submitted milestone setup.
     *
     * @return array{success: bool, message: string, status?: int}
     */
    public function reject(int $milestoneId, int $ownerId, string $reason = ''): array
    {
        $milestone = DB::table('milestones as m')
            ->join('projects as p', 'm.project_id', '=', 'p.project_id')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->where('m.milestone_id', $milestoneId)
            ->where('pr.owner_id', $ownerId)
            ->select('m.*', 'p.project_id')
            ->first();

        if (!$milestone) {
            return ['success' => false, 'message' => 'Milestone not found or unauthorized.', 'status' => 404];
        }

        DB::table('milestones')
            ->where('milestone_id', $milestoneId)
            ->update([
                'setup_status'    => 'rejected',
                'setup_rej_reason' => $reason,
                'updated_at'      => now(),
            ]);

        // Notify contractor
        $cUserId = DB::table('contractor_users')
            ->where('contractor_id', $milestone->contractor_id)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->value('user_id');
        if ($cUserId) {
            $msName     = $milestone->milestone_name ?? 'Milestone';
            $reasonNote = $reason ? " Reason: {$reason}" : '';
            notificationService::create(
                $cUserId,
                'milestone_rejected',
                'Milestone Rejected',
                "Your milestone \"{$msName}\" was rejected.{$reasonNote}",
                'high',
                'milestone',
                $milestoneId,
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => $milestone->project_id, 'tab' => 'milestones']]
            );
        }

        return ['success' => true, 'message' => 'Milestone rejected successfully.'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OWNER — Mark milestone as complete
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Mark a milestone as completed.
     *
     * @return array{success: bool, message: string, status?: int}
     */
    public function completeMilestone(int $milestoneId): array
    {
        $updated = DB::table('milestones')
            ->where('milestone_id', $milestoneId)
            ->update(['milestone_status' => 'completed']);

        if ($updated === 0) {
            return ['success' => false, 'message' => 'Milestone not found.', 'status' => 404];
        }

        // Notify contractor
        $ms = DB::table('milestones as m')
            ->join('projects as p', 'm.project_id', '=', 'p.project_id')
            ->where('m.milestone_id', $milestoneId)
            ->select('m.milestone_name', 'm.contractor_id', 'p.project_id', 'p.project_title')
            ->first();
        if ($ms) {
            $cUserId = DB::table('contractor_users')
                ->where('contractor_id', $ms->contractor_id)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->value('user_id');
            if ($cUserId) {
                notificationService::create(
                    $cUserId,
                    'milestone_completed',
                    'Milestone Completed',
                    "Milestone \"{$ms->milestone_name}\" for \"{$ms->project_title}\" has been marked as complete.",
                    'normal',
                    'milestone',
                    $milestoneId,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => $ms->project_id, 'tab' => 'milestones']]
                );
            }
        }

        return ['success' => true, 'message' => 'Milestone marked as complete successfully.'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OWNER — Mark milestone item as complete
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Mark a milestone item as completed, with sequential enforcement.
     *
     * @return array{success: bool, message: string, status?: int, data?: array, warning?: string}
     */
    public function completeMilestoneItem(int $itemId): array
    {
        $milestoneItem = DB::table('milestone_items')->where('item_id', $itemId)->first();
        if (!$milestoneItem) {
            $milestoneItem = DB::table('milestone_items')->where('id', $itemId)->first();
        }

        if (!$milestoneItem) {
            return ['success' => false, 'message' => 'Milestone item not found.', 'status' => 404];
        }

        // Already completed
        if (($milestoneItem->item_status ?? '') === 'completed') {
            return [
                'success' => true,
                'message' => 'Milestone item already marked as complete.',
                'data'    => ['item_id' => $milestoneItem->item_id, 'item_status' => 'completed'],
            ];
        }

        // Sequential enforcement
        $prevItem = DB::table('milestone_items')
            ->where('milestone_id', $milestoneItem->milestone_id)
            ->where('sequence_order', '<', $milestoneItem->sequence_order)
            ->orderBy('sequence_order', 'desc')
            ->first();
        if ($prevItem && ($prevItem->item_status ?? '') !== 'completed') {
            return [
                'success' => false,
                'message' => 'You must complete the previous milestone item first before completing this one.',
                'status'  => 422,
            ];
        }

        // Unapproved progress check (warning only, not blocking)
        $nonApprovedCount = DB::table('progress')
            ->where('milestone_item_id', $milestoneItem->item_id)
            ->whereNotIn('progress_status', ['approved', 'deleted'])
            ->count();

        $updated = DB::table('milestone_items')
            ->where('item_id', $milestoneItem->item_id)
            ->update(['item_status' => 'completed']);

        if ($updated === 0) {
            // Re-check in case DB reported 0 but value was already set
            $check = DB::table('milestone_items')->where('item_id', $milestoneItem->item_id)->first();
            if ($check && ($check->item_status ?? '') === 'completed') {
                return [
                    'success' => true,
                    'message' => 'Milestone item marked as complete successfully.',
                    'data'    => ['item_id' => $check->item_id, 'item_status' => 'completed'],
                ];
            }
            return ['success' => false, 'message' => 'Failed to update milestone item status.', 'status' => 500];
        }

        $updatedItem = DB::table('milestone_items')->where('item_id', $milestoneItem->item_id)->first();

        // Notify contractor
        $itemInfo = DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->join('projects as p', 'm.project_id', '=', 'p.project_id')
            ->where('mi.item_id', $updatedItem->item_id)
            ->select('mi.milestone_item_title', 'm.contractor_id', 'p.project_id', 'p.project_title')
            ->first();
        if ($itemInfo) {
            $cUserId = DB::table('contractor_users')
                ->where('contractor_id', $itemInfo->contractor_id)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->value('user_id');
            if ($cUserId) {
                notificationService::create(
                    $cUserId,
                    'milestone_item_completed',
                    'Milestone Complete',
                    "Milestone \"{$itemInfo->milestone_item_title}\" for \"{$itemInfo->project_title}\" has been marked complete.",
                    'normal',
                    'milestone_item',
                    $updatedItem->item_id,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => $itemInfo->project_id, 'tab' => 'milestones']]
                );
            }
        }

        $result = [
            'success' => true,
            'message' => 'Milestone item marked as complete successfully.',
            'data'    => ['item_id' => $updatedItem->item_id, 'item_status' => $updatedItem->item_status ?? 'completed'],
        ];
        if ($nonApprovedCount > 0) {
            $result['warning']               = "There are {$nonApprovedCount} unapproved progress reports for this item.";
            $result['non_approved_count']    = $nonApprovedCount;
            $result['message']               = 'Milestone item marked as complete. Note: ' . $result['warning'];
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONTRACTOR — Submit milestone setup
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create a new milestone plan with items and notify the project owner.
     *
     * @param  array  $data  Validated input
     * @param  object $contractor
     * @param  int    $projectId
     * @return array{success: bool, message: string, status?: int, data?: array}
     */
    public function submit(array $data, object $contractor, int $projectId): array
    {
        $project = DB::table('projects as p')
            ->join('bids as b', function ($join) use ($contractor) {
                $join->on('b.project_id', '=', 'p.project_id')
                     ->where('b.contractor_id', '=', $contractor->contractor_id)
                     ->where('b.bid_status', '=', 'accepted');
            })
            ->where('p.project_id', $projectId)
            ->select('p.*')
            ->first();

        if (!$project) {
            return ['success' => false, 'message' => 'Project not found or you do not have access.', 'status' => 404];
        }

        $totalPercentage = array_sum(array_column($data['items'], 'percentage'));
        if (round($totalPercentage, 2) != 100) {
            return ['success' => false, 'message' => 'Milestone percentages must add up to exactly 100%.', 'status' => 400];
        }

        $startDate = date('Y-m-d 00:00:00', strtotime($data['start_date']));
        $endDate   = date('Y-m-d 23:59:59', strtotime($data['end_date']));

        try {
            DB::beginTransaction();

            $planId = DB::table('payment_plans')->insertGetId([
                'project_id'        => $projectId,
                'contractor_id'     => $contractor->contractor_id,
                'payment_mode'      => $data['payment_mode'],
                'total_project_cost'=> $data['total_project_cost'],
                'downpayment_amount'=> $data['downpayment_amount'] ?? 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $milestoneId = DB::table('milestones')->insertGetId([
                'project_id'            => $projectId,
                'contractor_id'         => $contractor->contractor_id,
                'plan_id'               => $planId,
                'milestone_name'        => $data['milestone_name'],
                'milestone_description' => $data['milestone_name'],
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'milestone_status'      => 'not_started',
                'setup_status'          => 'submitted',
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            $remaining = $data['total_project_cost'] - ($data['downpayment_amount'] ?? 0);
            foreach ($data['items'] as $index => $item) {
                $base = $data['payment_mode'] === 'downpayment' ? $remaining : $data['total_project_cost'];
                DB::table('milestone_items')->insert([
                    'milestone_id'              => $milestoneId,
                    'sequence_order'            => $index + 1,
                    'percentage_progress'       => $item['percentage'],
                    'milestone_item_title'      => $item['title'],
                    'milestone_item_description'=> $item['description'] ?? '',
                    'milestone_item_cost'       => round($base * ($item['percentage'] / 100), 2),
                    'date_to_finish'            => date('Y-m-d 23:59:59', strtotime($item['date_to_finish'])),
                ]);
            }

            DB::commit();

            // Notify owner
            $ownerUserId = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->where('p.project_id', $projectId)
                ->value('po.user_id');
            if ($ownerUserId) {
                notificationService::create(
                    $ownerUserId,
                    'milestone_submitted',
                    'Milestone Submitted',
                    "Contractor submitted a milestone plan for \"{$project->project_title}\". Please review.",
                    'normal',
                    'milestone',
                    (int) $milestoneId,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => $projectId, 'tab' => 'milestones']]
                );
            }

            return [
                'success' => true,
                'message' => 'Milestone plan created successfully!',
                'data'    => ['milestone_id' => $milestoneId, 'plan_id' => $planId],
                'status'  => 201,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('milestoneService::submit — ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while saving the milestone.', 'status' => 500];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONTRACTOR — Update milestone setup
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Update an existing milestone/payment plan and notify the owner.
     *
     * @return array{success: bool, message: string, status?: int, data?: array}
     */
    public function update(int $milestoneId, int $projectId, array $data, object $contractor): array
    {
        $milestone = DB::table('milestones')
            ->where('milestone_id', $milestoneId)
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractor->contractor_id)
            ->first();

        if (!$milestone) {
            return ['success' => false, 'message' => 'Milestone not found.', 'status' => 404];
        }

        $totalPercentage = array_sum(array_column($data['items'], 'percentage'));
        if (round($totalPercentage, 2) != 100) {
            return ['success' => false, 'message' => 'Milestone percentages must add up to exactly 100%.', 'status' => 400];
        }

        $startDate = date('Y-m-d 00:00:00', strtotime($data['start_date']));
        $endDate   = date('Y-m-d 23:59:59', strtotime($data['end_date']));

        try {
            DB::beginTransaction();

            DB::table('payment_plans')->where('plan_id', $milestone->plan_id)->update([
                'payment_mode'       => $data['payment_mode'],
                'total_project_cost' => $data['total_project_cost'],
                'downpayment_amount' => $data['downpayment_amount'] ?? 0,
                'updated_at'         => now(),
            ]);

            DB::table('milestones')->where('milestone_id', $milestoneId)->update([
                'milestone_name'        => $data['milestone_name'],
                'milestone_description' => $data['milestone_name'],
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'setup_status'          => 'submitted',
                'setup_rej_reason'      => null,
                'updated_at'            => now(),
            ]);

            DB::table('milestone_items')->where('milestone_id', $milestoneId)->delete();

            $remaining = $data['total_project_cost'] - ($data['downpayment_amount'] ?? 0);
            foreach ($data['items'] as $index => $item) {
                $base = $data['payment_mode'] === 'downpayment' ? $remaining : $data['total_project_cost'];
                DB::table('milestone_items')->insert([
                    'milestone_id'              => $milestoneId,
                    'sequence_order'            => $index + 1,
                    'percentage_progress'       => $item['percentage'],
                    'milestone_item_title'      => $item['title'],
                    'milestone_item_description'=> $item['description'] ?? '',
                    'milestone_item_cost'       => round($base * ($item['percentage'] / 100), 2),
                    'date_to_finish'            => date('Y-m-d 23:59:59', strtotime($item['date_to_finish'])),
                ]);
            }

            DB::commit();

            // Notify owner
            $ownerUserId = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->where('p.project_id', $projectId)
                ->value('po.user_id');
            if ($ownerUserId) {
                $projTitle = DB::table('projects')->where('project_id', $projectId)->value('project_title');
                notificationService::create(
                    $ownerUserId,
                    'milestone_updated',
                    'Milestone Updated',
                    "Contractor updated a milestone for \"{$projTitle}\". Please review.",
                    'normal',
                    'milestone',
                    $milestoneId,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => $projectId, 'tab' => 'milestones']]
                );
            }

            return ['success' => true, 'message' => 'Milestone updated successfully!', 'data' => ['milestone_id' => $milestoneId]];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('milestoneService::update — ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while updating the milestone.', 'status' => 500];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONTRACTOR — Soft-delete milestone
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Soft-delete a milestone and notify the owner.
     *
     * @return array{success: bool, message: string, status?: int}
     */
    public function delete(int $milestoneId, int $contractorId, string $reason): array
    {
        $milestone = DB::table('milestones')
            ->where('milestone_id', $milestoneId)
            ->where('contractor_id', $contractorId)
            ->first();

        if (!$milestone) {
            return ['success' => false, 'message' => 'Milestone not found or no permission.', 'status' => 404];
        }

        if (isset($milestone->is_deleted) && $milestone->is_deleted) {
            return ['success' => false, 'message' => 'This milestone has already been deleted.', 'status' => 400];
        }

        try {
            $updateData = ['is_deleted' => 1, 'updated_at' => now()];
            try {
                DB::table('milestones')->where('milestone_id', $milestoneId)
                    ->update(array_merge($updateData, ['reason' => $reason]));
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'reason') || str_contains($e->getMessage(), 'Unknown column')) {
                    DB::table('milestones')->where('milestone_id', $milestoneId)->update($updateData);
                } else {
                    throw $e;
                }
            }

            // Notify owner
            $ownerUserId = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->where('p.project_id', $milestone->project_id)
                ->value('po.user_id');
            if ($ownerUserId) {
                $projTitle = DB::table('projects')->where('project_id', $milestone->project_id)->value('project_title');
                notificationService::create(
                    $ownerUserId,
                    'milestone_deleted',
                    'Milestone Deleted',
                    "Contractor deleted a milestone for \"{$projTitle}\".",
                    'normal',
                    'milestone',
                    $milestoneId,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => $milestone->project_id, 'tab' => 'milestones']]
                );
            }

            return ['success' => true, 'message' => 'Milestone deleted successfully.'];
        } catch (\Exception $e) {
            Log::error('milestoneService::delete — ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while deleting the milestone.', 'status' => 500];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve owner_id from user_id.
     *
     * @return object|null
     */
    public function resolveOwner(int $userId): ?object
    {
        return DB::table('property_owners')->where('user_id', $userId)->first();
    }

    /**
     * Resolve active contractor record from user_id (handles owner + staff).
     *
     * @return object|null
     */
    public function resolveContractor(int $userId): ?object
    {
        $contractor = DB::table('contractors')->where('user_id', $userId)->first();
        if (!$contractor) {
            $cu = DB::table('contractor_users')
                ->where('user_id', $userId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->first();
            if ($cu) {
                $contractor = DB::table('contractors')->where('contractor_id', $cu->contractor_id)->first();
            }
        }
        return $contractor;
    }
}
