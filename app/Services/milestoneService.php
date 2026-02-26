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
            ->update(['project_status' => 'in_progress']);

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

        // ── Carry-forward: if item is underpaid, push shortfall to next item ──
        // Wrapped in a transaction so the item-update + audit-log are atomic
        // and idempotent (uses SET, not ADD, to prevent double-application).
        try {
            DB::transaction(function () use ($updatedItem, $itemInfo, &$result) {
                $expectedAmount = (float) ($updatedItem->adjusted_cost ?? $updatedItem->milestone_item_cost);
                $totalPaid = (float) DB::table('milestone_payments')
                    ->where('item_id', $updatedItem->item_id)
                    ->where('payment_status', 'approved')
                    ->sum('amount');

                // Resolve project_id reliably for audit logs
                $resolvedProjectId = $itemInfo->project_id
                    ?? DB::table('milestones')->where('milestone_id', $updatedItem->milestone_id)->value('project_id')
                    ?? 0;

                if ($expectedAmount > 0 && $totalPaid < $expectedAmount) {
                    $shortfall = $expectedAmount - $totalPaid;

                    // Find next sequential item in the same milestone
                    $nextItem = DB::table('milestone_items')
                        ->where('milestone_id', $updatedItem->milestone_id)
                        ->where('sequence_order', '>', $updatedItem->sequence_order)
                        ->orderBy('sequence_order', 'asc')
                        ->first();

                    if ($nextItem) {
                        // Idempotent: always base off the ORIGINAL cost, not current adjusted
                        $nextOriginalCost = (float) $nextItem->milestone_item_cost;
                        $newAdjustedCost = $nextOriginalCost + $shortfall;

                        DB::table('milestone_items')
                            ->where('item_id', $nextItem->item_id)
                            ->update([
                                'adjusted_cost'        => $newAdjustedCost,
                                'carry_forward_amount' => $shortfall, // SET, not ADD
                                'updated_at'           => now(),
                            ]);

                        // Audit log
                        $this->logPaymentAdjustment([
                            'project_id'           => $resolvedProjectId,
                            'milestone_id'         => $updatedItem->milestone_id,
                            'source_item_id'       => $updatedItem->item_id,
                            'target_item_id'       => $nextItem->item_id,
                            'payment_id'           => null,
                            'adjustment_type'      => 'underpayment',
                            'original_required'    => (float) $updatedItem->milestone_item_cost,
                            'total_paid'           => $totalPaid,
                            'adjustment_amount'    => $shortfall,
                            'target_original_cost' => $nextOriginalCost,
                            'target_adjusted_cost' => $newAdjustedCost,
                            'notes'                => "Shortfall of " . number_format($shortfall, 2) . " carried forward on item completion to item #{$nextItem->sequence_order} ({$nextItem->milestone_item_title}).",
                        ]);

                        Log::info('completeMilestoneItem: underpayment carry-forward', [
                            'source_item' => $updatedItem->item_id,
                            'target_item' => $nextItem->item_id,
                            'shortfall'   => $shortfall,
                            'new_adjusted' => $newAdjustedCost,
                        ]);

                        $result['carry_forward'] = [
                            'shortfall'          => $shortfall,
                            'carried_to_item_id' => $nextItem->item_id,
                            'carried_to_title'   => $nextItem->milestone_item_title,
                            'new_adjusted_cost'  => $newAdjustedCost,
                        ];
                    } else {
                        // Last item — log shortfall but nowhere to carry
                        $this->logPaymentAdjustment([
                            'project_id'           => $resolvedProjectId,
                            'milestone_id'         => $updatedItem->milestone_id,
                            'source_item_id'       => $updatedItem->item_id,
                            'target_item_id'       => null,
                            'payment_id'           => null,
                            'adjustment_type'      => 'underpayment',
                            'original_required'    => (float) $updatedItem->milestone_item_cost,
                            'total_paid'           => $totalPaid,
                            'adjustment_amount'    => $shortfall,
                            'target_original_cost' => null,
                            'target_adjusted_cost' => null,
                            'notes'                => "Shortfall of " . number_format($shortfall, 2) . " on last item at completion. No next item.",
                        ]);

                        $result['carry_forward'] = [
                            'shortfall'          => $shortfall,
                            'carried_to_item_id' => null,
                            'message'            => 'Last milestone item — shortfall recorded but no next item to carry to.',
                        ];
                    }
                }
            });
        } catch (\Throwable $e) {
            // Carry-forward failed but item is already marked complete — log and include warning
            Log::error('completeMilestoneItem: carry-forward transaction failed', [
                'item_id' => $updatedItem->item_id,
                'error'   => $e->getMessage(),
            ]);
            $result['carry_forward_error'] = 'Carry-forward calculation failed: ' . $e->getMessage();
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

        // ── Guard: if a milestone already exists for this project+contractor, update it instead ──
        $existingMilestone = DB::table('milestones')
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractor->contractor_id)
            ->first();

        if ($existingMilestone) {
            Log::info("milestoneService::submit — milestone already exists (id={$existingMilestone->milestone_id}) for project {$projectId}, redirecting to update().");
            return $this->update($existingMilestone->milestone_id, $projectId, $data, $contractor);
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

        // Track whether this is a resubmission after rejection
        $wasRejected = ($milestone->setup_status ?? '') === 'rejected';

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

            // Notify owner — use distinct sub-type when resubmitting a rejected proposal
            $ownerUserId = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->where('p.project_id', $projectId)
                ->value('po.user_id');
            if ($ownerUserId) {
                $projTitle = DB::table('projects')->where('project_id', $projectId)->value('project_title');

                if ($wasRejected) {
                    notificationService::create(
                        $ownerUserId,
                        'milestone_resubmitted',
                        'Milestone Resubmitted',
                        "Contractor has modified and resubmitted the milestone setup for \"{$projTitle}\". Please review the updated proposal.",
                        'high',
                        'milestone',
                        $milestoneId,
                        ['screen' => 'ProjectDetails', 'params' => ['projectId' => $projectId, 'tab' => 'milestones']]
                    );
                } else {
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

    // ─────────────────────────────────────────────────────────────────────────
    // PAYMENT — Approve a milestone payment (contractor)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Approve a submitted milestone payment.
     *
     * After approval, compute over/underpayment and apply allocation rules:
     *   - Overpayment:  record excess, mark item fully paid, do NOT cascade.
     *   - Underpayment: if item is settled (approved by contractor), carry
     *                   shortfall to next sequential item's adjusted_cost.
     *
     * Everything runs inside a DB transaction for atomicity.
     *
     * @return array{success: bool, message: string, status?: int, data?: array}
     */
    public function approvePayment(int $paymentId, int $contractorId, int $userId): array
    {
        // Verify payment exists and belongs to contractor's project
        $payment = DB::table('milestone_payments as mp')
            ->join('projects as p', 'mp.project_id', '=', 'p.project_id')
            ->leftJoin('contractor_users as cu', 'mp.contractor_user_id', '=', 'cu.contractor_user_id')
            ->where('mp.payment_id', $paymentId)
            ->select('mp.*', 'p.selected_contractor_id', 'p.project_title', 'p.project_id as proj_id')
            ->first();

        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found.', 'status' => 404];
        }

        // Access check: contractor_user_id match OR selected_contractor_id match
        $hasAccess = false;
        if ($payment->contractor_user_id) {
            $cu = DB::table('contractor_users')->where('contractor_user_id', $payment->contractor_user_id)->first();
            if ($cu && $cu->contractor_id == $contractorId) {
                $hasAccess = true;
            }
        }
        if (!$hasAccess && $payment->selected_contractor_id && $payment->selected_contractor_id == $contractorId) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            return ['success' => false, 'message' => 'You do not have permission to approve this payment.', 'status' => 403];
        }

        if ($payment->payment_status !== 'submitted') {
            return ['success' => false, 'message' => 'This payment is not in submitted status.', 'status' => 400];
        }

        try {
            DB::beginTransaction();

            // 1. Approve the payment
            DB::table('milestone_payments')
                ->where('payment_id', $paymentId)
                ->update(['payment_status' => 'approved', 'updated_at' => now()]);

            // 2. Allocation logic (only for real milestone items, not downpayment item_id=-1)
            $allocationResult = null;
            if ($payment->item_id && $payment->item_id > 0) {
                $allocationResult = $this->processPaymentAllocation($paymentId, $payment->item_id, $payment->proj_id);
            }

            DB::commit();

            // 3. Notifications (outside transaction — non-critical)
            $this->sendPaymentApprovalNotifications($payment, $allocationResult, $userId);

            $response = ['success' => true, 'message' => 'Payment approved successfully.'];
            if ($allocationResult) {
                $response['data'] = [
                    'allocation' => $allocationResult,
                ];
            }
            return $response;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('milestoneService::approvePayment — ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'An error occurred while approving the payment.', 'status' => 500];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAYMENT — Reject a milestone payment (contractor)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Reject a submitted milestone payment with a reason.
     *
     * @return array{success: bool, message: string, status?: int}
     */
    public function rejectPayment(int $paymentId, int $contractorId, string $reason): array
    {
        $payment = DB::table('milestone_payments as mp')
            ->join('projects as p', 'mp.project_id', '=', 'p.project_id')
            ->where('mp.payment_id', $paymentId)
            ->select('mp.*', 'p.selected_contractor_id', 'p.project_title', 'p.project_id as proj_id')
            ->first();

        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found.', 'status' => 404];
        }

        // Access check
        $hasAccess = false;
        if ($payment->contractor_user_id) {
            $cu = DB::table('contractor_users')->where('contractor_user_id', $payment->contractor_user_id)->first();
            if ($cu && $cu->contractor_id == $contractorId) {
                $hasAccess = true;
            }
        }
        if (!$hasAccess && $payment->selected_contractor_id && $payment->selected_contractor_id == $contractorId) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            return ['success' => false, 'message' => 'You do not have permission to reject this payment.', 'status' => 403];
        }

        if ($payment->payment_status !== 'submitted') {
            return ['success' => false, 'message' => 'This payment is not in submitted status.', 'status' => 400];
        }

        try {
            DB::table('milestone_payments')
                ->where('payment_id', $paymentId)
                ->update([
                    'payment_status' => 'rejected',
                    'reason' => $reason,
                    'updated_at' => now(),
                ]);

            // Notify owner
            $ownerUserId = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->where('p.project_id', $payment->proj_id)
                ->value('po.user_id');

            if ($ownerUserId) {
                $reasonNote = $reason ? " Reason: {$reason}" : '';
                notificationService::create(
                    (int) $ownerUserId,
                    'payment_rejected',
                    'Payment Rejected',
                    "Your payment for \"{$payment->project_title}\" was rejected by the contractor.{$reasonNote}",
                    'high',
                    'payment',
                    $paymentId,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $payment->proj_id, 'tab' => 'payments']]
                );
            }

            return ['success' => true, 'message' => 'Payment rejected successfully.'];

        } catch (\Exception $e) {
            Log::error('milestoneService::rejectPayment — ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'An error occurred while rejecting the payment.', 'status' => 500];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAYMENT — Core allocation logic (called within transaction)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * After a payment is approved, compute the item's financial state and
     * apply allocation rules.
     *
     * Returns: ['status' => 'exact'|'overpaid'|'underpaid'|'partial',
     *           'total_paid', 'expected', 'difference', ...]
     */
    private function processPaymentAllocation(int $paymentId, int $itemId, int $projectId): array
    {
        $item = DB::table('milestone_items')->where('item_id', $itemId)->first();
        if (!$item) {
            return ['status' => 'error', 'message' => 'Milestone item not found.'];
        }

        // Expected = adjusted_cost (if set) or original milestone_item_cost
        $expectedAmount = (float) ($item->adjusted_cost ?? $item->milestone_item_cost);
        $originalCost = (float) $item->milestone_item_cost;

        // Sum all approved payments for this item
        $totalPaid = (float) DB::table('milestone_payments')
            ->where('item_id', $itemId)
            ->where('payment_status', 'approved')
            ->sum('amount');

        $difference = $totalPaid - $expectedAmount;

        // Determine status
        if (abs($difference) < 0.01) {
            // Exact payment — if item was completed with prior carry-forward, clear it
            if ($item->item_status === 'completed') {
                $this->clearCarryForwardOnNextItem($item);
            }
            return [
                'status'     => 'exact',
                'total_paid' => $totalPaid,
                'expected'   => $expectedAmount,
                'difference' => 0,
            ];
        }

        if ($difference > 0) {
            // ── OVERPAYMENT ──
            // If item was completed with prior carry-forward, clear it (fully paid now)
            if ($item->item_status === 'completed') {
                $this->clearCarryForwardOnNextItem($item);
            }
            // Record excess, do NOT cascade to next milestone
            $this->logPaymentAdjustment([
                'project_id'          => $projectId,
                'milestone_id'        => $item->milestone_id,
                'source_item_id'      => $itemId,
                'target_item_id'      => null,
                'payment_id'          => $paymentId,
                'adjustment_type'     => 'overpayment',
                'original_required'   => $originalCost,
                'total_paid'          => $totalPaid,
                'adjustment_amount'   => $difference,
                'target_original_cost'=> null,
                'target_adjusted_cost'=> null,
                'notes'               => "Overpayment of " . number_format($difference, 2) . " recorded. Excess stays on this item.",
            ]);

            return [
                'status'      => 'overpaid',
                'total_paid'  => $totalPaid,
                'expected'    => $expectedAmount,
                'difference'  => $difference,
                'over_amount' => $difference,
            ];
        }

        // $difference < 0 means underpaid, but only carry forward if this item is considered
        // "settled" — meaning the item_status is 'completed'. Otherwise it's just partial.
        if ($item->item_status === 'completed') {
            // ── UNDERPAYMENT on a completed item ──
            // The primary carry-forward happens in completeMilestoneItem().
            // If a payment is approved AFTER completion, recalculate the carry-forward
            // on the next item to avoid double-counting.
            $shortfall = abs($difference);

            // Find next sequential item in same milestone
            $nextItem = DB::table('milestone_items')
                ->where('milestone_id', $item->milestone_id)
                ->where('sequence_order', '>', $item->sequence_order)
                ->orderBy('sequence_order', 'asc')
                ->first();

            if ($nextItem) {
                // Recalculate: set carry_forward_amount to the CURRENT shortfall
                // (not add on top — completeMilestoneItem already set an initial value)
                $nextOriginalCost = (float) $nextItem->milestone_item_cost;
                $newAdjustedCost = $nextOriginalCost + $shortfall;

                DB::table('milestone_items')
                    ->where('item_id', $nextItem->item_id)
                    ->update([
                        'adjusted_cost'         => $newAdjustedCost,
                        'carry_forward_amount'  => $shortfall,
                        'updated_at'            => now(),
                    ]);

                // Log the recalculation
                $this->logPaymentAdjustment([
                    'project_id'          => $projectId,
                    'milestone_id'        => $item->milestone_id,
                    'source_item_id'      => $itemId,
                    'target_item_id'      => $nextItem->item_id,
                    'payment_id'          => $paymentId,
                    'adjustment_type'     => 'underpayment',
                    'original_required'   => $originalCost,
                    'total_paid'          => $totalPaid,
                    'adjustment_amount'   => $shortfall,
                    'target_original_cost'=> $nextOriginalCost,
                    'target_adjusted_cost'=> $newAdjustedCost,
                    'notes'               => "Recalculated carry-forward after post-completion payment. Shortfall now " . number_format($shortfall, 2) . " for item #{$nextItem->sequence_order}.",
                ]);

                return [
                    'status'              => 'underpaid',
                    'total_paid'          => $totalPaid,
                    'expected'            => $expectedAmount,
                    'difference'          => $difference,
                    'shortfall'           => $shortfall,
                    'carried_to_item_id'  => $nextItem->item_id,
                    'carried_to_title'    => $nextItem->milestone_item_title,
                    'new_adjusted_cost'   => $newAdjustedCost,
                ];
            }

            // No next item — last milestone, just log the shortfall
            $this->logPaymentAdjustment([
                'project_id'          => $projectId,
                'milestone_id'        => $item->milestone_id,
                'source_item_id'      => $itemId,
                'target_item_id'      => null,
                'payment_id'          => $paymentId,
                'adjustment_type'     => 'underpayment',
                'original_required'   => $originalCost,
                'total_paid'          => $totalPaid,
                'adjustment_amount'   => abs($difference),
                'target_original_cost'=> null,
                'target_adjusted_cost'=> null,
                'notes'               => "Shortfall of " . number_format(abs($difference), 2) . " on last item. No next item to carry forward.",
            ]);

            return [
                'status'     => 'underpaid',
                'total_paid' => $totalPaid,
                'expected'   => $expectedAmount,
                'difference' => $difference,
                'shortfall'  => abs($difference),
                'carried_to_item_id' => null,
            ];
        }

        // ── Also handle: item was JUST fully paid after completion ──
        // If previous carry-forward was set but now total_paid covers expectedAmount,
        // clear the carry-forward on the next item
        // (This is handled above by the exact/overpaid branches — difference >= 0)

        // Not completed yet — just partial, no carry-forward yet
        return [
            'status'     => 'partial',
            'total_paid' => $totalPaid,
            'expected'   => $expectedAmount,
            'difference' => $difference,
            'remaining'  => abs($difference),
        ];
    }

    /**
     * Insert a row into payment_adjustment_logs.
     */
    private function logPaymentAdjustment(array $data): void
    {
        DB::table('payment_adjustment_logs')->insert(array_merge($data, [
            'created_at' => now(),
        ]));
    }

    /**
     * Clear any carry-forward that was previously applied to the next item
     * from this source item (e.g. when the source item becomes fully paid
     * after initially being completed with a shortfall).
     */
    private function clearCarryForwardOnNextItem(object $item): void
    {
        $nextItem = DB::table('milestone_items')
            ->where('milestone_id', $item->milestone_id)
            ->where('sequence_order', '>', $item->sequence_order)
            ->orderBy('sequence_order', 'asc')
            ->first();

        if ($nextItem && (float) ($nextItem->carry_forward_amount ?? 0) > 0) {
            $nextOriginalCost = (float) $nextItem->milestone_item_cost;

            DB::table('milestone_items')
                ->where('item_id', $nextItem->item_id)
                ->update([
                    'adjusted_cost'        => $nextOriginalCost,
                    'carry_forward_amount' => 0,
                    'updated_at'           => now(),
                ]);

            Log::info('clearCarryForwardOnNextItem: cleared carry-forward', [
                'source_item' => $item->item_id,
                'target_item' => $nextItem->item_id,
                'cleared_amount' => $nextItem->carry_forward_amount,
            ]);
        }
    }

    /**
     * Send notifications after payment approval.
     */
    private function sendPaymentApprovalNotifications(object $payment, ?array $allocation, int $contractorUserId): void
    {
        $ownerUserId = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->where('p.project_id', $payment->proj_id)
            ->value('po.user_id');

        $projTitle = $payment->project_title ?? '';

        // Notify owner: payment approved
        if ($ownerUserId) {
            notificationService::create(
                (int) $ownerUserId,
                'payment_approved',
                'Payment Approved',
                "Your payment for \"{$projTitle}\" has been approved by the contractor.",
                'normal',
                'payment',
                (int) $payment->payment_id,
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $payment->proj_id, 'tab' => 'payments']]
            );
        }

        // If fully paid (exact or overpaid), notify both parties
        if ($allocation && in_array($allocation['status'] ?? '', ['exact', 'overpaid'])) {
            $milestoneItem = DB::table('milestone_items')->where('item_id', $payment->item_id)->first();
            $itemTitle = $milestoneItem->milestone_item_title ?? 'Milestone item';

            notificationService::create(
                (int) $contractorUserId,
                'payment_fully_paid',
                'Fully Paid',
                "Milestone item \"{$itemTitle}\" for \"{$projTitle}\" has been fully paid.",
                'normal',
                'milestone_item',
                (int) $payment->item_id,
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $payment->proj_id, 'tab' => 'payments']],
                "fully_paid_{$payment->item_id}"
            );

            if ($ownerUserId) {
                notificationService::create(
                    (int) $ownerUserId,
                    'payment_fully_paid',
                    'Fully Paid',
                    "Milestone item \"{$itemTitle}\" for \"{$projTitle}\" is now fully paid. Thank you!",
                    'normal',
                    'milestone_item',
                    (int) $payment->item_id,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $payment->proj_id, 'tab' => 'payments']],
                    "fully_paid_{$payment->item_id}_owner"
                );
            }

            // If overpaid, also add an informational note
            if ($allocation['status'] === 'overpaid' && $ownerUserId) {
                $overAmt = number_format($allocation['over_amount'] ?? 0, 2);
                notificationService::create(
                    (int) $ownerUserId,
                    'payment_overpaid',
                    'Overpayment Recorded',
                    "You overpaid {$overAmt} on \"{$itemTitle}\". The excess is recorded on this item and will NOT be auto-applied to the next milestone.",
                    'normal',
                    'milestone_item',
                    (int) $payment->item_id,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $payment->proj_id, 'tab' => 'payments']]
                );
            }
        }

        // If underpayment was carried forward, notify both
        if ($allocation && ($allocation['status'] ?? '') === 'underpaid' && !empty($allocation['carried_to_item_id'])) {
            $shortfall = number_format($allocation['shortfall'] ?? 0, 2);
            $targetTitle = $allocation['carried_to_title'] ?? 'next milestone';
            $newAdj = number_format($allocation['new_adjusted_cost'] ?? 0, 2);

            if ($ownerUserId) {
                notificationService::create(
                    (int) $ownerUserId,
                    'payment_underpaid_carry',
                    'Payment Shortfall Carried',
                    "Shortfall of {$shortfall} from this milestone was added to \"{$targetTitle}\" (new required: {$newAdj}).",
                    'high',
                    'milestone_item',
                    (int) $allocation['carried_to_item_id'],
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $payment->proj_id, 'tab' => 'payments']]
                );
            }

            notificationService::create(
                (int) $contractorUserId,
                'payment_underpaid_carry',
                'Payment Shortfall Carried',
                "Owner underpaid by {$shortfall}. Shortfall carried to \"{$targetTitle}\" (new required: {$newAdj}).",
                'high',
                'milestone_item',
                (int) $allocation['carried_to_item_id'],
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $payment->proj_id, 'tab' => 'payments']]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PAYMENT — Get payment summary for a milestone item (API helper)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Compute the financial summary for a single milestone item.
     * Used by API endpoints to return backend-computed values.
     *
     * @return array
     */
    public function getItemPaymentSummary(int $itemId): array
    {
        $item = DB::table('milestone_items')->where('item_id', $itemId)->first();
        if (!$item) {
            return ['error' => 'Item not found'];
        }

        $originalCost = (float) $item->milestone_item_cost;
        $adjustedCost = $item->adjusted_cost !== null ? (float) $item->adjusted_cost : null;
        $effectiveRequired = $adjustedCost ?? $originalCost;
        $carryForward = (float) ($item->carry_forward_amount ?? 0);

        $totalPaid = (float) DB::table('milestone_payments')
            ->where('item_id', $itemId)
            ->where('payment_status', 'approved')
            ->sum('amount');

        $totalSubmitted = (float) DB::table('milestone_payments')
            ->where('item_id', $itemId)
            ->where('payment_status', 'submitted')
            ->sum('amount');

        $remaining = max(0, $effectiveRequired - $totalPaid);
        $overAmount = $totalPaid > $effectiveRequired ? ($totalPaid - $effectiveRequired) : 0;

        // Derive status
        $derivedStatus = $this->deriveItemPaymentStatus($item, $totalPaid, $effectiveRequired);

        return [
            'original_cost'       => $originalCost,
            'adjusted_cost'       => $adjustedCost,
            'effective_required'   => $effectiveRequired,
            'carry_forward_amount' => $carryForward,
            'total_paid'          => $totalPaid,
            'total_submitted'     => $totalSubmitted,
            'remaining_balance'   => $remaining,
            'over_amount'         => $overAmount,
            'derived_status'      => $derivedStatus,
            'settlement_due_date' => $item->settlement_due_date ?? null,
            'extension_date'      => $item->extension_date ?? null,
        ];
    }

    /**
     * Derive payment status: Unpaid | Partially Paid | Fully Paid | Overdue
     */
    public function deriveItemPaymentStatus(?object $item, float $totalPaid, float $expectedAmount): string
    {
        if (!$item || $expectedAmount <= 0) {
            return 'Unpaid';
        }

        if ($totalPaid >= $expectedAmount) {
            return 'Fully Paid';
        }

        // Check if overdue
        $effectiveDueDate = $item->extension_date ?? $item->settlement_due_date ?? null;
        if ($effectiveDueDate && now()->startOfDay()->gt(\Carbon\Carbon::parse($effectiveDueDate)->endOfDay())) {
            return 'Overdue';
        }

        if ($totalPaid > 0) {
            return 'Partially Paid';
        }

        return 'Unpaid';
    }
}
