<?php

namespace App\Services;

use App\Models\both\ProjectUpdate;
use App\Services\NotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ProjectUpdateService  –  Enhanced v2
 *
 * Features:
 *   - Project context (overview) for the modal
 *   - Budget adjustment preview + validation (increase / decrease / none)
 *   - Manual new milestone item creation
 *   - Flexible allocation: percentage OR exact amount
 *   - Editing / deleting existing milestone items during update
 *   - Financial preview (read-only simulation)
 *   - Submit / Approve / Reject / Withdraw
 *   - Notifications via notificationService
 *
 * RULES (enforced here, never on the frontend):
 *   - Completed or fully-paid items NEVER modified
 *   - Partially paid items cannot be reduced below paid amount
 *   - Proposed budget cannot be lower than total paid
 *   - Historical payment records are NEVER altered
 *   - Only ONE pending update per project at a time
 *   - All financial mutations inside DB::transaction()
 *   - Carry-forward / overpayment logic is preserved (untouched)
 */
class ProjectUpdateService
{
    // ───────────────────────────────────────────────────────────────────────
    // 1. READ HELPERS
    // ───────────────────────────────────────────────────────────────────────

    /**
     * Full project context used by the update modal.
     * Returns: overview, budget breakdown, timeline, milestone items with
     * their payment status, and the pending update (if any).
     */
    public function getProjectContext(int $projectId): array
    {
        $project = DB::table('projects')->where('project_id', $projectId)->first();
        if (!$project) {
            return ['success' => false, 'message' => 'Project not found.'];
        }

        $plan = DB::table('payment_plans')
            ->where('project_id', $projectId)
            ->orderByDesc('plan_id')
            ->first();

        $totalCost = $plan ? (float) $plan->total_project_cost : 0.0;

        $totalPaid = (float) DB::table('milestone_payments as mp')
            ->join('milestone_items as mi', 'mp.item_id', '=', 'mi.item_id')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->where('m.project_id', $projectId)
            ->where('mp.payment_status', 'approved')
            ->sum('mp.amount');

        $timeline = DB::table('milestones')
            ->where('project_id', $projectId)
            ->whereNull('is_deleted')
            ->selectRaw('MIN(start_date) as start_date, MAX(end_date) as end_date')
            ->first();

        $rel = DB::table('project_relationships')
            ->where('rel_id', $project->relationship_id)
            ->first();

        // Resolve owner_id → users.user_id (owner_id references property_owners PK)
        $ownerUserId = null;
        if ($rel?->owner_id) {
            $ownerUserId = DB::table('property_owners')
                ->where('owner_id', $rel->owner_id)
                ->value('user_id');
        }

        // Resolve selected_contractor_id → users.user_id (references contractors PK)
        $contractorUserId = null;
        if ($rel?->selected_contractor_id) {
            $contractorUserId = DB::table('contractors')
                ->where('contractor_id', $rel->selected_contractor_id)
                ->value('user_id');
        }

        // --- Existing milestone items with payment info ---
        $milestoneItems = $this->getMilestoneItemsWithPayments($projectId);
        $totalAllocated = array_sum(array_column($milestoneItems, 'effective_cost'));

        return [
            'success'            => true,
            'project_id'         => $projectId,
            'project_title'      => $project->project_title,
            'project_status'     => $project->project_status,
            'start_date'         => $timeline?->start_date,
            'end_date'           => $timeline?->end_date,
            'total_cost'         => $totalCost,
            'total_paid'         => $totalPaid,
            'total_allocated'    => $totalAllocated,
            'remaining_balance'  => max(0, $totalCost - $totalPaid),
            'remaining_allocatable' => max(0, $totalCost - $totalAllocated),
            'owner_user_id'      => $ownerUserId ? (int) $ownerUserId : null,
            'contractor_user_id' => $contractorUserId ? (int) $contractorUserId : null,
            'milestone_items'    => $milestoneItems,
            'pending_extension'  => $this->getPendingUpdate($projectId),
            'plan_id'            => $plan?->plan_id ?? null,
            'contractor_id'      => $contractorUserId ? (int) $contractorUserId : null,
        ];
    }

    /**
     * Fetch all milestone items for a project with their payment summaries.
     * Each item includes: is_fully_paid, is_partially_paid, total_paid, editable flag.
     */
    public function getMilestoneItemsWithPayments(int $projectId): array
    {
        $items = DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->where('m.project_id', $projectId)
            ->whereNull('m.is_deleted')
            ->whereNotIn('mi.item_status', ['deleted'])
            ->select(
                'mi.item_id', 'mi.milestone_id', 'mi.sequence_order',
                'mi.milestone_item_title', 'mi.milestone_item_description',
                'mi.milestone_item_cost', 'mi.adjusted_cost', 'mi.carry_forward_amount',
                'mi.percentage_progress', 'mi.item_status',
                'mi.start_date', 'mi.date_to_finish', 'mi.settlement_due_date', 'mi.extension_date',
                'm.milestone_name', 'm.milestone_status'
            )
            ->orderBy('m.start_date')
            ->orderBy('mi.sequence_order')
            ->get();

        $result = [];
        foreach ($items as $item) {
            $totalApproved = (float) DB::table('milestone_payments')
                ->where('item_id', $item->item_id)
                ->where('payment_status', 'approved')
                ->sum('amount');

            $effectiveCost = $item->adjusted_cost !== null
                ? (float) $item->adjusted_cost
                : (float) $item->milestone_item_cost;

            $isFullyPaid     = $totalApproved >= $effectiveCost && $effectiveCost > 0;
            $isPartiallyPaid = $totalApproved > 0 && !$isFullyPaid;

            // Auto-correct stale status: if item has activity but is still not_started,
            // advance it to in_progress in the DB so it reflects reality.
            if ($item->item_status === 'not_started') {
                $hasProgress = DB::table('progress')
                    ->where('milestone_item_id', $item->item_id)
                    ->whereNotIn('progress_status', ['deleted'])
                    ->exists();
                if ($hasProgress || $totalApproved > 0) {
                    DB::table('milestone_items')
                        ->where('item_id', $item->item_id)
                        ->update(['item_status' => 'in_progress', 'updated_at' => now()]);
                    $item->item_status = 'in_progress';
                }
            }

            $isCompleted     = in_array($item->item_status, ['completed', 'cancelled']);

            // Determine editability — items are editable unless completed.
            // Even fully paid / in_progress items can have their dates/info edited via Project Update.
            $editable = true;
            $editableReason = null;
            if ($item->item_status === 'completed') {
                $editable = false;
                $editableReason = 'completed';
            }

            $result[] = [
                'item_id'              => $item->item_id,
                'milestone_id'         => $item->milestone_id,
                'milestone_name'       => $item->milestone_name,
                'sequence_order'       => $item->sequence_order,
                'title'                => $item->milestone_item_title,
                'description'          => $item->milestone_item_description,
                'base_cost'            => (float) $item->milestone_item_cost,
                'adjusted_cost'        => $item->adjusted_cost !== null ? (float) $item->adjusted_cost : null,
                'effective_cost'       => $effectiveCost,
                'carry_forward_amount' => (float) $item->carry_forward_amount,
                'percentage'           => (float) $item->percentage_progress,
                'item_status'          => $item->item_status,
                'milestone_status'     => $item->milestone_status,
                'start_date'           => $item->start_date,
                'date_to_finish'       => $item->date_to_finish,
                'settlement_due_date'  => $item->settlement_due_date,
                'total_paid'           => $totalApproved,
                'unpaid_balance'       => max(0, $effectiveCost - $totalApproved),
                'is_fully_paid'        => $isFullyPaid,
                'is_partially_paid'    => $isPartiallyPaid,
                'editable'             => $editable,
                'editable_reason'      => $editableReason,
                'min_cost'             => $isPartiallyPaid ? $totalApproved : 0,
            ];
        }

        return $result;
    }

    public function getPendingUpdate(int $projectId): ?object
    {
        return DB::table('project_updates')
            ->where('project_id', $projectId)
            ->whereIn('status', ['pending', 'revision_requested'])
            ->orderByDesc('extension_id')
            ->first();
    }

    public function listByProject(int $projectId): array
    {
        return DB::table('project_updates')
            ->where('project_id', $projectId)
            ->orderByDesc('extension_id')
            ->get()
            ->toArray();
    }

    // ───────────────────────────────────────────────────────────────────────
    // 2. PREVIEW  (read-only simulation — no DB writes)
    // ───────────────────────────────────────────────────────────────────────

    /**
     * Enhanced preview: supports budget adjustment + milestone changes.
     *
     * $payload keys:
     *   proposed_end_date     – required, YYYY-MM-DD
     *   proposed_budget       – nullable numeric (null = no budget change)
     *   allocation_mode       – 'percentage' | 'exact'
     *   new_items             – array of { title, description?, cost, percentage?, due_date? }
     *   edited_items          – array of { item_id, cost?, percentage?, title?, due_date? }
     *   deleted_item_ids      – array of int
     */
    public function preview(int $projectId, array $payload): array
    {
        $ctx = $this->getProjectContext($projectId);
        if (!$ctx['success']) return $ctx;

        $result = ['success' => true];

        // ── Timeline preview ──
        $currentEnd = $ctx['end_date'] ? Carbon::parse($ctx['end_date']) : null;
        $proposed   = isset($payload['proposed_end_date']) ? Carbon::parse($payload['proposed_end_date']) : null;

        if ($proposed && $currentEnd && $proposed->lte($currentEnd)) {
            return ['success' => false, 'message' => 'Proposed end date must be later than current end date (' . $currentEnd->format('Y-m-d') . ').'];
        }

        $deltaDays = ($proposed && $currentEnd) ? (int) round($currentEnd->floatDiffInDays($proposed)) : 0;
        $result['timeline'] = [
            'current_end_date'  => $ctx['end_date'],
            'proposed_end_date' => $payload['proposed_end_date'] ?? null,
            'delta_days'        => $deltaDays,
        ];

        // ── Budget preview ──
        $currentBudget  = $ctx['total_cost'];
        $proposedBudget = isset($payload['proposed_budget']) && $payload['proposed_budget'] !== null
            ? (float) $payload['proposed_budget']
            : $currentBudget;

        $budgetValidation = $this->validateBudgetChange($currentBudget, $proposedBudget, $ctx['total_paid'], $ctx['milestone_items']);

        if (!$budgetValidation['valid']) {
            return ['success' => false, 'message' => $budgetValidation['message']];
        }

        $result['budget'] = [
            'current_budget'      => $currentBudget,
            'proposed_budget'     => $proposedBudget,
            'budget_change_type'  => $budgetValidation['change_type'],
            'budget_difference'   => $proposedBudget - $currentBudget,
            'total_paid'          => $ctx['total_paid'],
        ];

        // ── Milestone changes preview ──
        $allocationMode   = $payload['allocation_mode'] ?? 'percentage';
        $newItems         = $payload['new_items'] ?? [];
        $editedItems      = $payload['edited_items'] ?? [];
        $deletedItemIds   = $payload['deleted_item_ids'] ?? [];

        // Validate deletions
        foreach ($deletedItemIds as $deleteId) {
            $itemMeta = collect($ctx['milestone_items'])->firstWhere('item_id', $deleteId);
            if (!$itemMeta) continue;
            if (!$itemMeta['editable']) {
                return ['success' => false, 'message' => "Cannot delete \"{$itemMeta['title']}\" — it is {$itemMeta['editable_reason']}."];
            }
            if ($itemMeta['total_paid'] > 0) {
                return ['success' => false, 'message' => "Cannot delete \"{$itemMeta['title']}\" — it has approved payments."];
            }
        }

        // Validate edits
        foreach ($editedItems as $edit) {
            $itemMeta = collect($ctx['milestone_items'])->firstWhere('item_id', $edit['item_id'] ?? 0);
            if (!$itemMeta) {
                return ['success' => false, 'message' => "Edited item ID {$edit['item_id']} not found."];
            }
            if (!$itemMeta['editable']) {
                return ['success' => false, 'message' => "Cannot edit \"{$itemMeta['title']}\" — it is {$itemMeta['editable_reason']}."];
            }
            if (isset($edit['cost'])) {
                $newCost = (float) $edit['cost'];
                if ($newCost < 0) {
                    return ['success' => false, 'message' => "Allocation for \"{$itemMeta['title']}\" cannot be negative."];
                }
                if ($itemMeta['is_partially_paid'] && $newCost < $itemMeta['total_paid']) {
                    return ['success' => false, 'message' => "Cannot reduce \"{$itemMeta['title']}\" below paid amount (₱" . number_format($itemMeta['total_paid'], 2) . ")."];
                }
            }
        }

        // Validate new items
        foreach ($newItems as $ni) {
            if (empty($ni['title'])) {
                return ['success' => false, 'message' => 'Each new milestone item requires a title.'];
            }
            $niCost = (float) ($ni['cost'] ?? 0);
            if ($niCost < 0) {
                return ['success' => false, 'message' => "Allocation for \"{$ni['title']}\" cannot be negative."];
            }
        }

        // ── Date-range validation ──
        // All milestone item dates must fall within the project duration.
        $projStart = $ctx['start_date'] ? Carbon::parse($ctx['start_date'])->startOfDay() : null;
        $projEnd   = $proposed ?? ($ctx['end_date'] ? Carbon::parse($ctx['end_date'])->endOfDay() : null);

        $dateErrors = [];

        // Validate edited items dates
        foreach ($editedItems as $edit) {
            $itemTitle = collect($ctx['milestone_items'])->firstWhere('item_id', $edit['item_id'] ?? 0)['title'] ?? "Item #{$edit['item_id']}";

            if (!empty($edit['start_date'])) {
                $sd = Carbon::parse($edit['start_date']);
                if ($projStart && $sd->lt($projStart)) {
                    $dateErrors[] = "\"{$itemTitle}\" start date is before the project start date.";
                }
                if ($projEnd && $sd->gt($projEnd)) {
                    $dateErrors[] = "\"{$itemTitle}\" start date exceeds the project end date.";
                }
            }
            if (!empty($edit['due_date'])) {
                $dd = Carbon::parse($edit['due_date']);
                if ($projStart && $dd->lt($projStart)) {
                    $dateErrors[] = "\"{$itemTitle}\" due date is before the project start date.";
                }
                if ($projEnd && $dd->gt($projEnd)) {
                    $dateErrors[] = "\"{$itemTitle}\" due date exceeds the project end date.";
                }
            }
            // start_date must be before due_date
            if (!empty($edit['start_date']) && !empty($edit['due_date'])) {
                if (Carbon::parse($edit['start_date'])->gt(Carbon::parse($edit['due_date']))) {
                    $dateErrors[] = "\"{$itemTitle}\" start date must not be after its due date.";
                }
            }
        }

        // Validate new items dates
        foreach ($newItems as $ni) {
            $niTitle = $ni['title'] ?? 'New item';
            if (!empty($ni['start_date'])) {
                $sd = Carbon::parse($ni['start_date']);
                if ($projStart && $sd->lt($projStart)) {
                    $dateErrors[] = "\"{$niTitle}\" start date is before the project start date.";
                }
                if ($projEnd && $sd->gt($projEnd)) {
                    $dateErrors[] = "\"{$niTitle}\" start date exceeds the project end date.";
                }
            }
            if (!empty($ni['due_date'])) {
                $dd = Carbon::parse($ni['due_date']);
                if ($projStart && $dd->lt($projStart)) {
                    $dateErrors[] = "\"{$niTitle}\" due date is before the project start date.";
                }
                if ($projEnd && $dd->gt($projEnd)) {
                    $dateErrors[] = "\"{$niTitle}\" due date exceeds the project end date.";
                }
            }
            if (!empty($ni['start_date']) && !empty($ni['due_date'])) {
                if (Carbon::parse($ni['start_date'])->gt(Carbon::parse($ni['due_date']))) {
                    $dateErrors[] = "\"{$niTitle}\" start date must not be after its due date.";
                }
            }
        }

        // ── Overlap validation ──
        // Build a unified timeline of every item's effective [start, end] range,
        // then check all pairs for overlapping ranges.
        $timeline = [];

        foreach ($ctx['milestone_items'] as $item) {
            if (in_array($item['item_id'], $deletedItemIds)) continue;

            $editEntry = collect($editedItems)->firstWhere('item_id', $item['item_id']);

            // Determine effective start date (edited value takes precedence)
            $startStr = ($editEntry && array_key_exists('start_date', $editEntry))
                ? $editEntry['start_date']
                : ($item['start_date'] ?? null);

            // Determine effective due date
            $endStr = ($editEntry && array_key_exists('due_date', $editEntry))
                ? $editEntry['due_date']
                : ($item['settlement_due_date'] ?? $item['date_to_finish'] ?? null);

            if ($startStr && $endStr) {
                $label = ($editEntry && isset($editEntry['title']))
                    ? $editEntry['title']
                    : ($item['title'] ?? "Item #{$item['item_id']}");

                $timeline[] = [
                    'label' => $label,
                    'start' => Carbon::parse($startStr)->startOfDay(),
                    'end'   => Carbon::parse($endStr)->startOfDay(),
                ];
            }
        }

        foreach ($newItems as $ni) {
            if (!empty($ni['start_date']) && !empty($ni['due_date'])) {
                $timeline[] = [
                    'label' => $ni['title'] ?? 'New item',
                    'start' => Carbon::parse($ni['start_date'])->startOfDay(),
                    'end'   => Carbon::parse($ni['due_date'])->startOfDay(),
                ];
            }
        }

        usort($timeline, fn($a, $b) => $a['start']->timestamp - $b['start']->timestamp);

        $tlCount = count($timeline);
        for ($i = 0; $i < $tlCount - 1; $i++) {
            for ($j = $i + 1; $j < $tlCount; $j++) {
                // Two ranges overlap when: startA <= endB AND startB <= endA
                if ($timeline[$i]['start']->lte($timeline[$j]['end']) && $timeline[$j]['start']->lte($timeline[$i]['end'])) {
                    $dateErrors[] = "Date overlap: \"{$timeline[$i]['label']}\" ("
                        . $timeline[$i]['start']->format('M d, Y') . ' – ' . $timeline[$i]['end']->format('M d, Y')
                        . ") overlaps with \"{$timeline[$j]['label']}\" ("
                        . $timeline[$j]['start']->format('M d, Y') . ' – ' . $timeline[$j]['end']->format('M d, Y') . ').';
                }
            }
        }

        if (!empty($dateErrors)) {
            return ['success' => false, 'message' => implode(' ', $dateErrors)];
        }

        // Compute total allocation
        $keptItems = collect($ctx['milestone_items'])->whereNotIn('item_id', $deletedItemIds);
        $totalAllocation = 0;

        foreach ($keptItems as $item) {
            $editEntry = collect($editedItems)->firstWhere('item_id', $item['item_id']);
            if ($editEntry && isset($editEntry['cost'])) {
                $totalAllocation += (float) $editEntry['cost'];
            } else {
                $totalAllocation += $item['effective_cost'];
            }
        }

        foreach ($newItems as $ni) {
            $totalAllocation += (float) ($ni['cost'] ?? 0);
        }

        $remainingBudget = $proposedBudget - $totalAllocation;

        if ($totalAllocation > $proposedBudget) {
            $result['budget']['allocation_exceeds'] = true;
            $result['budget']['allocation_warning'] = 'Total allocation (₱' . number_format($totalAllocation, 2) . ') exceeds proposed contract value (₱' . number_format($proposedBudget, 2) . ').';
        }

        // Compute percentages for each item
        $previewItems = [];
        foreach ($keptItems as $item) {
            $editEntry = collect($editedItems)->firstWhere('item_id', $item['item_id']);
            $cost = ($editEntry && isset($editEntry['cost'])) ? (float) $editEntry['cost'] : $item['effective_cost'];
            $pct  = $proposedBudget > 0 ? round(($cost / $proposedBudget) * 100, 2) : 0;

            $previewItems[] = [
                'item_id'     => $item['item_id'],
                'title'       => $editEntry['title'] ?? $item['title'],
                'cost'        => $cost,
                'percentage'  => $pct,
                'status'      => $item['item_status'],
                'editable'    => $item['editable'],
                'is_existing' => true,
                'is_edited'   => $editEntry !== null,
            ];
        }
        foreach ($newItems as $idx => $ni) {
            $cost = (float) ($ni['cost'] ?? 0);
            $pct  = $proposedBudget > 0 ? round(($cost / $proposedBudget) * 100, 2) : 0;
            $previewItems[] = [
                'temp_id'     => 'new_' . $idx,
                'title'       => $ni['title'],
                'cost'        => $cost,
                'percentage'  => $pct,
                'status'      => 'not_started',
                'editable'    => true,
                'is_existing' => false,
                'is_edited'   => false,
            ];
        }

        $result['allocation'] = [
            'mode'              => $allocationMode,
            'total_allocated'   => round($totalAllocation, 2),
            'remaining_budget'  => round($remainingBudget, 2),
            'proposed_budget'   => $proposedBudget,
            'items'             => $previewItems,
            'deleted_item_ids'  => $deletedItemIds,
        ];

        return $result;
    }

    // ───────────────────────────────────────────────────────────────────────
    // 3. SUBMIT
    // ───────────────────────────────────────────────────────────────────────

    /**
     * Contractor submits a project update request.
     * $data keys match the preview payload plus 'reason' and 'user_id'.
     */
    public function submit(int $projectId, int $contractorUserId, array $data): array
    {
        try {
            $existing = $this->getPendingUpdate($projectId);

            // If there's an existing request with revision_requested, treat as resubmission
            if ($existing && $existing->status === 'revision_requested') {
                return $this->resubmit($existing, $projectId, $contractorUserId, $data);
            }

            if ($existing) {
                return ['success' => false, 'message' => 'There is already a pending update request for this project. Please wait for the property owner to respond.'];
            }

            $ctx = $this->getProjectContext($projectId);
            if (!$ctx['success']) return $ctx;

            if (!in_array($ctx['project_status'], ['in_progress', 'halt'])) {
                return ['success' => false, 'message' => 'Update requests can only be submitted for in-progress projects.'];
            }

            $currentEndDate  = $ctx['end_date'] ? Carbon::parse($ctx['end_date'])->format('Y-m-d') : null;
            $proposedEndDate = $data['proposed_end_date'] ?? null;

            // Only validate proposed end date when it's actually provided
            if ($proposedEndDate) {
                if (!$currentEndDate) {
                    return ['success' => false, 'message' => 'Cannot determine the project end date. Ensure milestones are set up.'];
                }
                if (Carbon::parse($proposedEndDate)->lte(Carbon::parse($currentEndDate))) {
                    return ['success' => false, 'message' => 'Proposed end date must be later than the current end date (' . $currentEndDate . ').'];
                }
            }

            // Budget — always store a value (default to current when unchanged)
            $currentBudget  = $ctx['total_cost'];
            $proposedBudget = isset($data['proposed_budget']) && $data['proposed_budget'] !== null
                ? (float) $data['proposed_budget']
                : $currentBudget;

            $budgetValidation = $this->validateBudgetChange($currentBudget, $proposedBudget, $ctx['total_paid'], $ctx['milestone_items']);
            if (!$budgetValidation['valid']) {
                return ['success' => false, 'message' => $budgetValidation['message']];
            }
            $budgetChangeType = $budgetValidation['change_type'];

            // Additional cost fields
            $hasAdditionalCost = $proposedBudget > $currentBudget;
            $additionalAmount  = $hasAdditionalCost ? round($proposedBudget - $currentBudget, 2) : null;

            // Milestone changes
            $allocationMode  = $data['allocation_mode'] ?? null;
            $newItems        = $data['new_items'] ?? [];
            $editedItems     = $data['edited_items'] ?? [];
            $deletedItemIds  = $data['deleted_item_ids'] ?? [];

            // Validate milestone changes via preview
            $previewPayload = [
                'proposed_end_date' => $proposedEndDate,
                'proposed_budget'   => $proposedBudget ?? $currentBudget,
                'allocation_mode'   => $allocationMode,
                'new_items'         => $newItems,
                'edited_items'      => $editedItems,
                'deleted_item_ids'  => $deletedItemIds,
            ];
            $previewResult = $this->preview($projectId, $previewPayload);
            if (!$previewResult['success']) {
                return $previewResult;
            }

            // Check total allocation does not exceed proposed budget
            if (isset($previewResult['allocation']['allocation_exceeds']) && $previewResult['allocation']['allocation_exceeds']) {
                return ['success' => false, 'message' => $previewResult['budget']['allocation_warning'] ?? 'Total allocation exceeds contract value.'];
            }

            // ── Build enriched milestone snapshot (single source of truth) ──
            $enrichedEdits = array_map(function ($edit) use ($ctx) {
                $original = collect($ctx['milestone_items'])->firstWhere('item_id', $edit['item_id'] ?? 0);
                if ($original) {
                    $edit['_original'] = [
                        'title'      => $original['title'],
                        'cost'       => $original['effective_cost'],
                        'percentage' => $original['percentage'],
                        'start_date' => $original['start_date'] ?? null,
                        'due_date'   => $original['settlement_due_date'] ?? $original['date_to_finish'] ?? null,
                    ];
                }
                return $edit;
            }, $editedItems);

            $deletedSnapshot = array_map(function ($id) use ($ctx) {
                $original = collect($ctx['milestone_items'])->firstWhere('item_id', $id);
                return [
                    'item_id' => $id,
                    'title'   => $original ? $original['title'] : "Item #{$id}",
                    'cost'    => $original ? $original['effective_cost'] : 0,
                ];
            }, $deletedItemIds);

            $milestoneChangesJson = json_encode([
                'new_items'        => $newItems,
                'edited_items'     => $enrichedEdits,
                'deleted_item_ids' => $deletedItemIds,
                '_deleted_items'   => $deletedSnapshot,
                '_snapshot_meta'   => [
                    'current_budget'  => $currentBudget,
                    'proposed_budget' => $proposedBudget,
                    'budget_change'   => $budgetChangeType,
                    'allocation_mode' => $allocationMode,
                    'snapshot_at'     => now()->toIso8601String(),
                ],
            ]);

            $insertPayload = [
                'project_id'          => $projectId,
                'contractor_user_id'  => $contractorUserId,
                'owner_user_id'       => $ctx['owner_user_id'],
                'current_end_date'    => $currentEndDate,
                'proposed_end_date'   => $proposedEndDate,
                'reason'              => $data['reason'] ?? '',
                'current_budget'      => $currentBudget,
                'proposed_budget'     => $proposedBudget,
                'budget_change_type'  => $budgetChangeType,
                'has_additional_cost' => $hasAdditionalCost ? 1 : 0,
                'additional_amount'   => $additionalAmount,
                'milestone_changes'   => $milestoneChangesJson,
                'allocation_mode'     => $allocationMode,
                'status'              => 'pending',
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            Log::info('ProjectUpdate submit — insert payload', [
                'project_id'      => $projectId,
                'proposed_budget' => $proposedBudget,
                'current_budget'  => $currentBudget,
                'budget_change'   => $budgetChangeType,
                'additional_amt'  => $additionalAmount,
                'has_new_items'   => !empty($newItems),
                'has_edits'       => !empty($editedItems),
                'has_deletions'   => !empty($deletedItemIds),
            ]);

            $extensionId = DB::table('project_updates')->insertGetId($insertPayload);

            // ── Write milestone item updates to normalized table ──
            $this->saveMilestoneItemUpdates($extensionId, $editedItems, $ctx);

            // Notify owner
            $notifTitle = $budgetChangeType !== 'none'
                ? 'Project Budget Adjustment Requested'
                : 'Project Update Request Submitted';
            $notifBody  = $budgetChangeType !== 'none'
                ? "Contractor submitted a budget adjustment (" . ucfirst($budgetChangeType) . ") for \"{$ctx['project_title']}\"."
                : "Contractor submitted a project update request for \"{$ctx['project_title']}\". Please review.";

            if ($ctx['owner_user_id']) {
                NotificationService::create(
                    (int) $ctx['owner_user_id'],
                    'project_update',
                    $notifTitle,
                    $notifBody,
                    'high',
                    'project',
                    $projectId,
                    ['screen' => 'ProjectTimeline', 'params' => ['projectId' => $projectId]]
                );
            }

            Log::info('ProjectUpdate submitted', compact('extensionId', 'projectId', 'contractorUserId', 'proposedEndDate', 'budgetChangeType'));

            return [
                'success'      => true,
                'message'      => 'Update request submitted successfully. The property owner will be notified.',
                'extension_id' => $extensionId,
            ];
        } catch (\Throwable $e) {
            Log::error('ProjectUpdate submit error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['success' => false, 'message' => 'Failed to submit update request: ' . $e->getMessage()];
        }
    }

    // ───────────────────────────────────────────────────────────────────────
    // 4. APPROVE
    // ───────────────────────────────────────────────────────────────────────

    public function approve(int $extensionId, int $ownerUserId, ?string $note = null): array
    {
        $ext = DB::table('project_updates')->where('extension_id', $extensionId)->first();
        if (!$ext) return ['success' => false, 'message' => 'Update request not found.'];
        if ($ext->status !== 'pending') return ['success' => false, 'message' => 'This update request is no longer pending.'];

        $ctx = $this->getProjectContext($ext->project_id);
        if (!$ctx['success']) return $ctx;
        if ((int) $ctx['owner_user_id'] !== $ownerUserId) {
            return ['success' => false, 'message' => 'Unauthorized: you are not the owner of this project.'];
        }

        try {
            DB::transaction(function () use ($ext, $note, $ctx) {
                $changedBy = (int) $ctx['owner_user_id'];
                $appliedAt = now();

                // ── 1. Extend timeline ONLY when a proposed end date was supplied ──
                if ($ext->proposed_end_date && $ext->current_end_date) {
                    $proposedEnd = Carbon::parse($ext->proposed_end_date);
                    $currentEnd  = Carbon::parse($ext->current_end_date);
                    $deltaDays   = $currentEnd->diffInDays($proposedEnd);

                    $milestones = DB::table('milestones')
                        ->where('project_id', $ext->project_id)
                        ->whereNull('is_deleted')
                        ->whereNotIn('milestone_status', ['completed', 'cancelled', 'deleted'])
                        ->get();

                    foreach ($milestones as $m) {
                        $newEnd = Carbon::parse($m->end_date)->addDays($deltaDays);
                        DB::table('milestones')
                            ->where('milestone_id', $m->milestone_id)
                            ->update(['end_date' => $newEnd->format('Y-m-d H:i:s'), 'updated_at' => $appliedAt]);

                        $items = DB::table('milestone_items')
                            ->where('milestone_id', $m->milestone_id)
                            ->whereNotIn('item_status', ['completed', 'cancelled', 'deleted'])
                            ->get();

                        foreach ($items as $item) {
                            $previousDate = $item->date_to_finish;
                            $newDeadline = Carbon::parse($previousDate)->addDays($deltaDays);

                            // Preserve original_date_to_finish on first extension only
                            $updateFields = [
                                'date_to_finish' => $newDeadline->format('Y-m-d H:i:s'),
                                'was_extended'   => true,
                                'extension_count' => DB::raw('extension_count + 1'),
                                'updated_at'     => $appliedAt,
                            ];

                            if ($item->original_date_to_finish === null) {
                                $updateFields['original_date_to_finish'] = $previousDate;
                            }

                            DB::table('milestone_items')
                                ->where('item_id', $item->item_id)
                                ->update($updateFields);

                            // Insert date change history record
                            DB::table('milestone_date_histories')->insert([
                                'item_id'       => $item->item_id,
                                'previous_date' => $previousDate,
                                'new_date'      => $newDeadline->format('Y-m-d H:i:s'),
                                'extension_id'  => $ext->extension_id,
                                'changed_by'    => $changedBy,
                                'changed_at'    => $appliedAt,
                                'change_reason' => "Project update #{$ext->extension_id} approved",
                                'created_at'    => $appliedAt,
                                'updated_at'    => $appliedAt,
                            ]);
                        }
                    }
                }

                // ── 2. Budget adjustment ──
                if ($ext->proposed_budget !== null) {
                    $plan = DB::table('payment_plans')
                        ->where('project_id', $ext->project_id)
                        ->orderByDesc('plan_id')
                        ->first();

                    if ($plan) {
                        DB::table('payment_plans')
                            ->where('plan_id', $plan->plan_id)
                            ->update([
                                'total_project_cost' => (float) $ext->proposed_budget,
                                'updated_at'         => now(),
                            ]);
                    }
                }

                // ── 3. Milestone changes (from JSON — new items + deletions) ──
                $changes = $ext->milestone_changes ? json_decode($ext->milestone_changes, true) : [];
                $this->applyMilestoneChanges($ext->project_id, $changes, $ctx);

                // ── 3a. Apply milestone item updates from normalized table ──
                $this->applyMilestoneItemUpdates($ext->extension_id, (int) $ctx['owner_user_id']);

                // ── 3b. Recalculate percentage_progress for ALL active items ──
                $effectiveBudgetForPct = $ext->proposed_budget !== null
                    ? (float) $ext->proposed_budget
                    : ($ctx['total_cost'] ?? 0);
                if ($effectiveBudgetForPct > 0) {
                    $allActiveItems = DB::table('milestone_items as mi')
                        ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                        ->where('m.project_id', $ext->project_id)
                        ->whereNull('m.is_deleted')
                        ->whereNotIn('mi.item_status', ['cancelled', 'deleted'])
                        ->select('mi.item_id', 'mi.milestone_item_cost', 'mi.adjusted_cost')
                        ->get();

                    foreach ($allActiveItems as $ai) {
                        $effectiveCost = $ai->adjusted_cost !== null
                            ? (float) $ai->adjusted_cost
                            : (float) $ai->milestone_item_cost;
                        $pct = ($effectiveCost / $effectiveBudgetForPct) * 100;

                        DB::table('milestone_items')
                            ->where('item_id', $ai->item_id)
                            ->update([
                                'percentage_progress' => round($pct, 2),
                                'updated_at'          => now(),
                            ]);
                    }

                    Log::info('Recalculated percentage_progress for all items', [
                        'project_id'  => $ext->project_id,
                        'new_budget'  => $effectiveBudgetForPct,
                        'items_count' => $allActiveItems->count(),
                    ]);
                }

                // ── 4. Stamp update record ──
                DB::table('project_updates')
                    ->where('extension_id', $ext->extension_id)
                    ->update([
                        'status'         => 'approved',
                        'owner_response' => $note,
                        'applied_at'     => now(),
                        'updated_at'     => now(),
                    ]);

                Log::info('ProjectUpdate approved + applied', [
                    'extension_id'  => $ext->extension_id,
                    'project_id'    => $ext->project_id,
                    'delta_days'    => ($ext->proposed_end_date && $ext->current_end_date)
                        ? Carbon::parse($ext->current_end_date)->diffInDays(Carbon::parse($ext->proposed_end_date))
                        : 0,
                    'budget_change' => $ext->budget_change_type,
                ]);
            });

            // Notify contractor
            $notifTitle = $ext->budget_change_type !== 'none'
                ? 'Budget Adjustment Approved'
                : 'Project Update Approved';

            NotificationService::create(
                (int) $ext->contractor_user_id,
                'project_update',
                $notifTitle,
                'Your project update request has been approved. The project timeline and budget have been updated.',
                'high',
                'project',
                (int) $ext->project_id,
                ['screen' => 'ProjectTimeline', 'params' => ['projectId' => (int) $ext->project_id]]
            );

            return ['success' => true, 'message' => 'Update approved and applied successfully.'];

        } catch (\Throwable $e) {
            Log::error('ProjectUpdate approve error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['success' => false, 'message' => 'Failed to apply update. Please try again.'];
        }
    }

    // ───────────────────────────────────────────────────────────────────────
    // 5. REJECT
    // ───────────────────────────────────────────────────────────────────────

    public function reject(int $extensionId, int $ownerUserId, string $reason): array
    {
        $ext = DB::table('project_updates')->where('extension_id', $extensionId)->first();
        if (!$ext) return ['success' => false, 'message' => 'Update request not found.'];
        if ($ext->status !== 'pending') return ['success' => false, 'message' => 'This update request is no longer pending.'];

        $ctx = $this->getProjectContext($ext->project_id);
        if (!$ctx['success']) return $ctx;
        if ((int) $ctx['owner_user_id'] !== $ownerUserId) {
            return ['success' => false, 'message' => 'Unauthorized.'];
        }

        DB::table('project_updates')
            ->where('extension_id', $extensionId)
            ->update(['status' => 'rejected', 'owner_response' => $reason, 'updated_at' => now()]);

        $notifTitle = $ext->budget_change_type !== 'none'
            ? 'Budget Adjustment Rejected'
            : 'Project Update Rejected';

        NotificationService::create(
            (int) $ext->contractor_user_id,
            'project_update',
            $notifTitle,
            "Your project update request was rejected. Reason: {$reason}",
            'normal',
            'project',
            (int) $ext->project_id,
            ['screen' => 'ProjectTimeline', 'params' => ['projectId' => (int) $ext->project_id]]
        );

        return ['success' => true, 'message' => 'Update request rejected.'];
    }

    // ───────────────────────────────────────────────────────────────────────
    // 6. WITHDRAW
    // ───────────────────────────────────────────────────────────────────────

    public function withdraw(int $extensionId, int $contractorUserId): array
    {
        $ext = DB::table('project_updates')
            ->where('extension_id', $extensionId)
            ->where('contractor_user_id', $contractorUserId)
            ->first();

        if (!$ext) return ['success' => false, 'message' => 'Update request not found.'];
        if (!in_array($ext->status, ['pending', 'revision_requested'])) {
            return ['success' => false, 'message' => 'Only pending or revision-requested requests can be withdrawn.'];
        }

        DB::table('project_updates')
            ->where('extension_id', $extensionId)
            ->update(['status' => 'withdrawn', 'updated_at' => now()]);

        // Notify owner that the contractor withdrew the update request
        $ctx = $this->getProjectContext($ext->project_id);
        if ($ctx['success'] && $ctx['owner_user_id']) {
            NotificationService::create(
                (int) $ctx['owner_user_id'],
                'project_update',
                'Project Update Withdrawn',
                "The contractor has withdrawn their update request for \"{$ctx['project_title']}\".",
                'normal',
                'project',
                (int) $ext->project_id,
                ['screen' => 'ProjectTimeline', 'params' => ['projectId' => (int) $ext->project_id]]
            );
        }

        return ['success' => true, 'message' => 'Update request withdrawn.'];
    }

    // ───────────────────────────────────────────────────────────────────────
    // 7. REQUEST CHANGES (Owner)
    // ───────────────────────────────────────────────────────────────────────

    /**
     * Owner requests changes on a pending update.
     * Sets status to 'revision_requested' and stores notes for the contractor.
     */
    public function requestChanges(int $extensionId, int $ownerUserId, string $notes): array
    {
        $ext = DB::table('project_updates')->where('extension_id', $extensionId)->first();
        if (!$ext) return ['success' => false, 'message' => 'Update request not found.'];
        if ($ext->status !== 'pending') {
            return ['success' => false, 'message' => 'Only pending update requests can have changes requested.'];
        }

        $ctx = $this->getProjectContext($ext->project_id);
        if (!$ctx['success']) return $ctx;
        if ((int) $ctx['owner_user_id'] !== $ownerUserId) {
            return ['success' => false, 'message' => 'Unauthorized: you are not the owner of this project.'];
        }

        DB::table('project_updates')
            ->where('extension_id', $extensionId)
            ->update([
                'status'         => 'revision_requested',
                'revision_notes' => $notes,
                'updated_at'     => now(),
            ]);

        // Notify contractor
        NotificationService::create(
            (int) $ext->contractor_user_id,
            'project_update',
            'Update Revision Requested',
            "The property owner has requested changes to your update request for \"{$ctx['project_title']}\". Notes: {$notes}",
            'high',
            'project',
            (int) $ext->project_id,
            ['screen' => 'ProjectTimeline', 'params' => ['projectId' => (int) $ext->project_id]]
        );

        Log::info('ProjectUpdate revision requested', [
            'extension_id' => $extensionId,
            'project_id'   => $ext->project_id,
            'owner_id'     => $ownerUserId,
        ]);

        return ['success' => true, 'message' => 'Revision requested. The contractor will be notified.'];
    }

    // ───────────────────────────────────────────────────────────────────────
    // 8. RESUBMIT (Contractor — after revision_requested)
    // ───────────────────────────────────────────────────────────────────────

    /**
     * Contractor resubmits a revised update request.
     * Updates the existing record in-place and resets status to 'pending'.
     */
    private function resubmit(object $existing, int $projectId, int $contractorUserId, array $data): array
    {
        if ((int) $existing->contractor_user_id !== $contractorUserId) {
            return ['success' => false, 'message' => 'Unauthorized: you are not the contractor for this update.'];
        }

        $ctx = $this->getProjectContext($projectId);
        if (!$ctx['success']) return $ctx;

        if (!in_array($ctx['project_status'], ['in_progress', 'halt'])) {
            return ['success' => false, 'message' => 'Update requests can only be submitted for in-progress projects.'];
        }

        $currentEndDate  = $ctx['end_date'] ? Carbon::parse($ctx['end_date'])->format('Y-m-d') : null;
        $proposedEndDate = $data['proposed_end_date'] ?? null;

        if (!$currentEndDate) {
            return ['success' => false, 'message' => 'Cannot determine the project end date. Ensure milestones are set up.'];
        }

        // Only validate proposed_end_date when one is actually provided
        if ($proposedEndDate) {
            if (Carbon::parse($proposedEndDate)->lte(Carbon::parse($currentEndDate))) {
                return ['success' => false, 'message' => 'Proposed end date must be later than the current end date (' . $currentEndDate . ').'];
            }
        }

        // Budget — always store a value (default to current when unchanged)
        $currentBudget  = $ctx['total_cost'];
        $proposedBudget = isset($data['proposed_budget']) && $data['proposed_budget'] !== null
            ? (float) $data['proposed_budget']
            : $currentBudget;

        $budgetValidation = $this->validateBudgetChange($currentBudget, $proposedBudget, $ctx['total_paid'], $ctx['milestone_items']);
        if (!$budgetValidation['valid']) {
            return ['success' => false, 'message' => $budgetValidation['message']];
        }
        $budgetChangeType = $budgetValidation['change_type'];

        // Additional cost fields
        $hasAdditionalCost = $proposedBudget > $currentBudget;
        $additionalAmount  = $hasAdditionalCost ? round($proposedBudget - $currentBudget, 2) : null;

        // Milestone changes
        $allocationMode = $data['allocation_mode'] ?? null;
        $newItems       = $data['new_items'] ?? [];
        $editedItems    = $data['edited_items'] ?? [];
        $deletedItemIds = $data['deleted_item_ids'] ?? [];

        // Validate via preview
        $previewPayload = [
            'proposed_end_date' => $proposedEndDate,
            'proposed_budget'   => $proposedBudget ?? $currentBudget,
            'allocation_mode'   => $allocationMode,
            'new_items'         => $newItems,
            'edited_items'      => $editedItems,
            'deleted_item_ids'  => $deletedItemIds,
        ];
        $previewResult = $this->preview($projectId, $previewPayload);
        if (!$previewResult['success']) {
            return $previewResult;
        }

        if (isset($previewResult['allocation']['allocation_exceeds']) && $previewResult['allocation']['allocation_exceeds']) {
            return ['success' => false, 'message' => $previewResult['budget']['allocation_warning'] ?? 'Total allocation exceeds contract value.'];
        }

        // ── Build enriched milestone snapshot (single source of truth) ──
        $enrichedEdits = array_map(function ($edit) use ($ctx) {
            $original = collect($ctx['milestone_items'])->firstWhere('item_id', $edit['item_id'] ?? 0);
            if ($original) {
                $edit['_original'] = [
                    'title'      => $original['title'],
                    'cost'       => $original['effective_cost'],
                    'percentage' => $original['percentage'],
                    'due_date'   => $original['settlement_due_date'] ?? $original['date_to_finish'] ?? null,
                ];
            }
            return $edit;
        }, $editedItems);

        $deletedSnapshot = array_map(function ($id) use ($ctx) {
            $original = collect($ctx['milestone_items'])->firstWhere('item_id', $id);
            return [
                'item_id' => $id,
                'title'   => $original ? $original['title'] : "Item #{$id}",
                'cost'    => $original ? $original['effective_cost'] : 0,
            ];
        }, $deletedItemIds);

        $milestoneChangesJson = json_encode([
            'new_items'        => $newItems,
            'edited_items'     => $enrichedEdits,
            'deleted_item_ids' => $deletedItemIds,
            '_deleted_items'   => $deletedSnapshot,
            '_snapshot_meta'   => [
                'current_budget'  => $currentBudget,
                'proposed_budget' => $proposedBudget,
                'budget_change'   => $budgetChangeType,
                'allocation_mode' => $allocationMode,
                'snapshot_at'     => now()->toIso8601String(),
            ],
        ]);

        $updatePayload = [
            'current_end_date'    => $proposedEndDate ? $currentEndDate : $existing->current_end_date,
            'proposed_end_date'   => $proposedEndDate,
            'reason'              => $data['reason'] ?? $existing->reason,
            'current_budget'      => $currentBudget,
            'proposed_budget'     => $proposedBudget,
            'budget_change_type'  => $budgetChangeType,
            'has_additional_cost' => $hasAdditionalCost ? 1 : 0,
            'additional_amount'   => $additionalAmount,
            'milestone_changes'   => $milestoneChangesJson,
            'allocation_mode'     => $allocationMode,
            'status'              => 'pending',
            'revision_notes'      => null,
            'updated_at'          => now(),
        ];

        Log::info('ProjectUpdate resubmit — update payload', [
            'extension_id'    => $existing->extension_id,
            'proposed_budget' => $proposedBudget,
            'current_budget'  => $currentBudget,
            'budget_change'   => $budgetChangeType,
            'additional_amt'  => $additionalAmount,
        ]);

        // Update the existing record in-place, reset to pending
        DB::table('project_updates')
            ->where('extension_id', $existing->extension_id)
            ->update($updatePayload);

        // ── Replace milestone item updates for this extension ──
        DB::table('milestone_item_updates')
            ->where('project_update_id', $existing->extension_id)
            ->delete();
        $this->saveMilestoneItemUpdates($existing->extension_id, $editedItems, $ctx);

        // Notify owner
        if ($ctx['owner_user_id']) {
            NotificationService::create(
                (int) $ctx['owner_user_id'],
                'project_update',
                'Revised Update Request Submitted',
                "Contractor has revised and resubmitted the update request for \"{$ctx['project_title']}\". Please review the updated proposal.",
                'high',
                'project',
                $projectId,
                ['screen' => 'ProjectTimeline', 'params' => ['projectId' => $projectId]]
            );
        }

        Log::info('ProjectUpdate resubmitted', [
            'extension_id'     => $existing->extension_id,
            'project_id'       => $projectId,
            'contractor_id'    => $contractorUserId,
            'proposed_end_date' => $proposedEndDate,
            'budget_change'    => $budgetChangeType,
        ]);

        return [
            'success'      => true,
            'message'      => 'Revised update request submitted successfully. The property owner will be notified.',
            'extension_id' => $existing->extension_id,
        ];
    }

    // ───────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ───────────────────────────────────────────────────────────────────────

    /**
     * Validate a proposed budget change.
     * Returns ['valid' => bool, 'change_type' => string, 'message' => ?string]
     */
    private function validateBudgetChange(float $currentBudget, float $proposedBudget, float $totalPaid, array $milestoneItems): array
    {
        if (abs($proposedBudget - $currentBudget) < 0.01) {
            return ['valid' => true, 'change_type' => 'none'];
        }

        if ($proposedBudget > $currentBudget) {
            return ['valid' => true, 'change_type' => 'increase'];
        }

        // Decrease
        if ($proposedBudget < $totalPaid) {
            return [
                'valid'   => false,
                'change_type' => 'decrease',
                'message' => 'Proposed budget cannot be lower than total paid amount (₱' . number_format($totalPaid, 2) . ').',
            ];
        }

        $fullyPaidSum = 0;
        foreach ($milestoneItems as $item) {
            if ($item['is_fully_paid']) {
                $fullyPaidSum += $item['effective_cost'];
            }
        }
        if ($proposedBudget < $fullyPaidSum) {
            return [
                'valid'   => false,
                'change_type' => 'decrease',
                'message' => 'Proposed budget cannot be lower than the sum of fully paid milestones (₱' . number_format($fullyPaidSum, 2) . ').',
            ];
        }

        return ['valid' => true, 'change_type' => 'decrease'];
    }

    /**
     * Apply milestone changes (new items, edits, deletions).
     * Called inside DB::transaction from approve().
     */
    private function applyMilestoneChanges(int $projectId, array $changes, array $ctx): void
    {
        $deletedIds  = $changes['deleted_item_ids'] ?? [];
        $editedItems = $changes['edited_items'] ?? [];
        $newItems    = $changes['new_items'] ?? [];

        // ── Deletions (soft-delete) ──
        if (!empty($deletedIds)) {
            DB::table('milestone_items')
                ->whereIn('item_id', $deletedIds)
                ->whereNotIn('item_status', ['completed', 'cancelled'])
                ->update([
                    'item_status' => 'deleted',
                    'updated_at'  => now(),
                ]);
        }

        // ── Edits ──
        foreach ($editedItems as $edit) {
            $itemId = $edit['item_id'] ?? null;
            if (!$itemId) continue;

            $itemMeta = collect($ctx['milestone_items'])->firstWhere('item_id', $itemId);
            if (!$itemMeta || !$itemMeta['editable']) continue;

            $updateFields = ['updated_at' => now()];

            if (isset($edit['cost'])) {
                $newCost = (float) $edit['cost'];
                if ($itemMeta['is_partially_paid'] && $newCost < $itemMeta['total_paid']) {
                    continue;
                }
                $updateFields['milestone_item_cost'] = $newCost;
                $updateFields['adjusted_cost'] = null;
            }

            if (isset($edit['percentage'])) {
                $updateFields['percentage_progress'] = (float) $edit['percentage'];
            }

            if (isset($edit['title'])) {
                $updateFields['milestone_item_title'] = $edit['title'];
            }

            if (array_key_exists('start_date', $edit)) {
                $updateFields['start_date'] = $edit['start_date'] ?: null;
            }

            if (array_key_exists('due_date', $edit)) {
                $dueVal = $edit['due_date'] ?: null;
                $updateFields['settlement_due_date'] = $dueVal;
                if ($dueVal) {
                    $updateFields['date_to_finish'] = $dueVal;
                }
            }

            DB::table('milestone_items')
                ->where('item_id', $itemId)
                ->update($updateFields);
        }

        // ── New items ──
        if (!empty($newItems)) {
            $lastMilestone = DB::table('milestones')
                ->where('project_id', $projectId)
                ->whereNull('is_deleted')
                ->whereNotIn('milestone_status', ['cancelled', 'deleted'])
                ->orderByDesc('end_date')
                ->first();

            if (!$lastMilestone) return;

            $maxSeq = DB::table('milestone_items')
                ->where('milestone_id', $lastMilestone->milestone_id)
                ->max('sequence_order') ?? 0;

            foreach ($newItems as $ni) {
                $maxSeq++;
                $cost = (float) ($ni['cost'] ?? 0);

                DB::table('milestone_items')->insert([
                    'milestone_id'                => $lastMilestone->milestone_id,
                    'sequence_order'              => $maxSeq,
                    'percentage_progress'         => (float) ($ni['percentage'] ?? 0),
                    'milestone_item_title'        => $ni['title'],
                    'milestone_item_description'  => $ni['description'] ?? null,
                    'milestone_item_cost'         => $cost,
                    'adjusted_cost'               => null,
                    'carry_forward_amount'        => 0.00,
                    'item_status'                 => 'not_started',
                    'start_date'                  => $ni['start_date'] ?? null,
                    'date_to_finish'              => $ni['due_date'] ?? $lastMilestone->end_date,
                    'settlement_due_date'         => $ni['due_date'] ?? null,
                    'updated_at'                  => now(),
                ]);

                if (!empty($ni['attachments'])) {
                    $newItemId = DB::getPdo()->lastInsertId();
                    foreach ($ni['attachments'] as $filePath) {
                        DB::table('item_files')->insert([
                            'item_id'   => $newItemId,
                            'file_path' => $filePath,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Persist per-item proposed changes into the normalised milestone_item_updates table.
     * Called from submit() and resubmit().
     */
    private function saveMilestoneItemUpdates(int $projectUpdateId, array $editedItems, array $ctx): void
    {
        foreach ($editedItems as $edit) {
            $itemId = $edit['item_id'] ?? null;
            if (!$itemId) continue;

            $original = collect($ctx['milestone_items'])->firstWhere('item_id', $itemId);
            if (!$original) continue;

            // Only persist if at least one field actually changed
            $hasDateChange  = isset($edit['start_date']) || isset($edit['due_date']);
            $hasCostChange  = isset($edit['cost']);
            $hasTitleChange = isset($edit['title']);
            if (!$hasDateChange && !$hasCostChange && !$hasTitleChange) continue;

            $previousEndDate = $original['settlement_due_date'] ?? $original['date_to_finish'] ?? null;

            DB::table('milestone_item_updates')->insert([
                'milestone_item_id'  => $itemId,
                'project_update_id'  => $projectUpdateId,
                'proposed_start_date' => $edit['start_date'] ?? null,
                'proposed_end_date'   => $edit['due_date'] ?? null,
                'proposed_cost'       => $hasCostChange ? (float) $edit['cost'] : null,
                'proposed_title'      => $edit['title'] ?? null,
                'previous_start_date' => $original['start_date']
                    ? Carbon::parse($original['start_date'])->format('Y-m-d')
                    : null,
                'previous_end_date'   => $previousEndDate
                    ? Carbon::parse($previousEndDate)->format('Y-m-d')
                    : null,
                'previous_cost'       => $original['effective_cost'],
                'previous_title'      => $original['title'],
                'status'              => 'pending',
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }

    /**
     * Apply milestone item updates from the normalised table on approval.
     * Updates item dates/costs/titles and marks records as approved.
     */
    private function applyMilestoneItemUpdates(int $extensionId, int $approvedBy): void
    {
        $updates = DB::table('milestone_item_updates')
            ->where('project_update_id', $extensionId)
            ->where('status', 'pending')
            ->get();

        foreach ($updates as $upd) {
            $item = DB::table('milestone_items')->where('item_id', $upd->milestone_item_id)->first();
            if (!$item) continue;

            $patch = ['updated_at' => now()];

            // ── Date changes ──
            if ($upd->proposed_start_date !== null) {
                $patch['start_date'] = $upd->proposed_start_date;
            }
            if ($upd->proposed_end_date !== null) {
                // Store extension history
                $currentDue = $item->settlement_due_date ?? $item->date_to_finish;
                $patch['date_to_finish']        = $upd->proposed_end_date;
                $patch['settlement_due_date']   = $upd->proposed_end_date;
                if ($currentDue && Carbon::parse($upd->proposed_end_date)->gt(Carbon::parse($currentDue))) {
                    $patch['was_extended']     = true;
                    $patch['extension_count']  = ($item->extension_count ?? 0) + 1;
                    $patch['extension_date']   = now();
                }

                // Record in date history
                DB::table('milestone_date_histories')->insert([
                    'item_id'       => $item->item_id,
                    'previous_date' => $currentDue,
                    'new_date'      => $upd->proposed_end_date,
                    'extension_id'  => $extensionId,
                    'changed_by'    => $approvedBy,
                    'change_reason' => 'project_update_approved',
                    'changed_at'    => now(),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // ── Cost change ──
            if ($upd->proposed_cost !== null) {
                $patch['adjusted_cost'] = (float) $upd->proposed_cost;
            }

            // ── Title change ──
            if ($upd->proposed_title !== null) {
                $patch['milestone_item_title'] = $upd->proposed_title;
            }

            DB::table('milestone_items')
                ->where('item_id', $upd->milestone_item_id)
                ->update($patch);

            // Mark this record as approved
            DB::table('milestone_item_updates')
                ->where('id', $upd->id)
                ->update([
                    'status'      => 'approved',
                    'approved_by' => $approvedBy,
                    'approved_at' => now(),
                    'updated_at'  => now(),
                ]);
        }
    }

    /**
     * Return eligible items for redistribution (not completed/cancelled/deleted and not fully paid).
     */
    private function getEligibleItemsForRedistribution(int $projectId): array
    {
        $items = DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->where('m.project_id', $projectId)
            ->whereNull('m.is_deleted')
            ->whereNotIn('mi.item_status', ['completed', 'cancelled', 'deleted'])
            ->select('mi.item_id', 'mi.milestone_item_title', 'mi.milestone_item_cost', 'mi.adjusted_cost', 'mi.item_status')
            ->get();

        $result = [];
        foreach ($items as $item) {
            $totalApproved = (float) DB::table('milestone_payments')
                ->where('item_id', $item->item_id)
                ->where('payment_status', 'approved')
                ->sum('amount');

            $effectiveCost = $item->adjusted_cost !== null ? (float) $item->adjusted_cost : (float) $item->milestone_item_cost;

            if ($totalApproved < $effectiveCost) {
                $result[] = [
                    'item_id'      => $item->item_id,
                    'title'        => $item->milestone_item_title,
                    'current_cost' => $effectiveCost,
                    'status'       => $item->item_status,
                ];
            }
        }

        return $result;
    }
}
