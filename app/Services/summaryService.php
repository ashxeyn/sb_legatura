<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class summaryService
{
    // ─────────────────────────────────────────────────────────────────────
    // PROJECT SUMMARY
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Full lifecycle summary for a project.
     */
    public function getProjectSummary(int $projectId): array
    {
        // ── A. Header ──
        $header = $this->buildProjectHeader($projectId);
        if (!$header) {
            return ['success' => false, 'message' => 'Project not found.'];
        }

        // ── B. Executive Overview ──
        $overview = $this->buildExecutiveOverview($projectId);

        // ── C. Budget & Allocation History ──
        $budgetHistory = $this->buildBudgetHistory($projectId);

        // ── D. Milestone Breakdown ──
        $milestones = $this->buildMilestoneBreakdown($projectId);

        // ── E. Timeline & Change History (audit log) ──
        $changeHistory = $this->buildChangeHistory($projectId);

        // ── F. Payments History ──
        $payments = $this->buildPaymentsHistory($projectId);

        // ── G. Progress Reports ──
        $progressReports = $this->buildProgressReports($projectId);

        return [
            'success' => true,
            'data'    => [
                'header'          => $header,
                'overview'        => $overview,
                'budget_history'  => $budgetHistory,
                'milestones'      => $milestones,
                'change_history'  => $changeHistory,
                'payments'        => $payments,
                'progress_reports' => $progressReports,
                'generated_at'    => now()->toIso8601String(),
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // MILESTONE SUMMARY
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Full lifecycle summary scoped to a single milestone item.
     */
    public function getMilestoneSummary(int $projectId, int $milestoneItemId): array
    {
        $item = DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->where('m.project_id', $projectId)
            ->where('mi.item_id', $milestoneItemId)
            ->select(
                'mi.*',
                'm.milestone_name',
                'm.milestone_status',
                'm.start_date as milestone_start_date',
                'm.end_date as milestone_end_date',
                'm.setup_status',
                'm.project_id'
            )
            ->first();

        if (!$item) {
            return ['success' => false, 'message' => 'Milestone item not found.'];
        }

        // ── Header ──
        $header = [
            'item_id'              => $item->item_id,
            'title'                => $item->milestone_item_title,
            'description'          => $item->milestone_item_description,
            'status'               => $item->item_status,
            'milestone_name'       => $item->milestone_name,
            'milestone_status'     => $item->milestone_status,
            'original_allocation'  => (float) $item->milestone_item_cost,
            'current_allocation'   => $item->adjusted_cost !== null
                ? (float) $item->adjusted_cost
                : (float) $item->milestone_item_cost,
            'carry_forward_amount' => (float) ($item->carry_forward_amount ?? 0),
            'original_due_date'    => $item->original_date_to_finish ?? $item->date_to_finish,
            'current_due_date'     => $item->date_to_finish,
            'was_extended'         => (bool) $item->was_extended,
            'extension_count'      => (int) ($item->extension_count ?? 0),
            'settlement_due_date'  => $item->settlement_due_date,
            'sequence_order'       => (int) $item->sequence_order,
            'percentage_progress'  => (float) $item->percentage_progress,
        ];

        // ── Financial ──
        $effectiveCost = $header['current_allocation'];
        $totalPaid = (float) DB::table('milestone_payments')
            ->where('item_id', $milestoneItemId)
            ->where('payment_status', 'approved')
            ->sum('amount');
        $totalSubmitted = (float) DB::table('milestone_payments')
            ->where('item_id', $milestoneItemId)
            ->where('payment_status', 'submitted')
            ->sum('amount');

        $financial = [
            'allocated_budget'  => $effectiveCost,
            'original_budget'   => (float) $item->milestone_item_cost,
            'paid_amount'       => $totalPaid,
            'pending_amount'    => $totalSubmitted,
            'remaining_balance' => max(0, $effectiveCost - $totalPaid),
            'over_amount'       => max(0, $totalPaid - $effectiveCost),
        ];

        // ── Date History ──
        $dateHistory = DB::table('milestone_date_histories as h')
            ->leftJoin('project_updates as pu', 'h.extension_id', '=', 'pu.extension_id')
            ->leftJoin('property_owners as po_h', function ($join) {
                $join->on('h.changed_by', '=', 'po_h.user_id');
            })
            ->leftJoin('contractors as c_h', function ($join) {
                $join->on('h.changed_by', '=', 'c_h.user_id');
            })
            ->where('h.item_id', $milestoneItemId)
            ->orderBy('h.changed_at', 'asc')
            ->select(
                'h.id',
                'h.previous_date',
                'h.new_date',
                'h.extension_id',
                'h.changed_at',
                'h.change_reason',
                'pu.reason as extension_reason',
                DB::raw("COALESCE(CONCAT(po_h.first_name, ' ', po_h.last_name), c_h.company_name) as changed_by_name")
            )
            ->get();

        // ── Payment History ──
        $payments = DB::table('milestone_payments')
            ->where('item_id', $milestoneItemId)
            ->orderBy('transaction_date', 'desc')
            ->select(
                'payment_id',
                'amount',
                'payment_type',
                'transaction_number',
                'transaction_date',
                'payment_status',
                'reason'
            )
            ->get();

        // ── Progress Reports ──
        $reports = DB::table('progress as p')
            ->where('p.milestone_item_id', $milestoneItemId)
            ->whereNotIn('p.progress_status', ['deleted'])
            ->orderBy('p.submitted_at', 'desc')
            ->select(
                'p.progress_id',
                'p.purpose',
                'p.progress_status',
                'p.submitted_at',
                'p.updated_at'
            )
            ->get();

        return [
            'success' => true,
            'data'    => [
                'header'        => $header,
                'financial'     => $financial,
                'date_history'  => $dateHistory,
                'payments'      => $payments,
                'progress_reports' => $reports,
                'generated_at'  => now()->toIso8601String(),
            ],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // PRIVATE — Project Section Builders
    // ─────────────────────────────────────────────────────────────────────

    private function buildProjectHeader(int $projectId): ?array
    {
        $project = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->leftJoin('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->leftJoin('users as ou', 'po.user_id', '=', 'ou.user_id')
            ->leftJoin('contractors as c', 'pr.selected_contractor_id', '=', 'c.contractor_id')
            ->leftJoin('users as cu', 'c.user_id', '=', 'cu.user_id')
            ->where('p.project_id', $projectId)
            ->select(
                'p.project_id',
                'p.project_title',
                'p.project_description',
                'p.project_location',
                'p.project_status',
                'p.property_type',
                'p.budget_range_min',
                'p.budget_range_max',
                'pr.created_at as project_created_at',
                DB::raw("CONCAT(po.first_name, ' ', po.last_name) as owner_name"),
                'po.phone_number as owner_phone',
                'ou.email as owner_email',
                'c.company_name as contractor_company',
                'c.company_name as contractor_name',
                'cu.email as contractor_email'
            )
            ->first();

        if (!$project) return null;

        // Get earliest start and latest end from milestones
        $timeline = DB::table('milestones')
            ->where('project_id', $projectId)
            ->whereNull('is_deleted')
            ->selectRaw('MIN(start_date) as start_date, MAX(end_date) as end_date')
            ->first();

        // Determine original end date from the first approved extension,
        // or from `project_updates` if extensions ever changed it.
        $firstApprovedUpdate = DB::table('project_updates')
            ->where('project_id', $projectId)
            ->where('status', 'approved')
            ->orderBy('applied_at', 'asc')
            ->first();

        $originalEndDate = $firstApprovedUpdate
            ? $firstApprovedUpdate->current_end_date
            : ($timeline->end_date ?? null);
        $currentEndDate = $timeline->end_date ?? null;
        $wasExtended = $firstApprovedUpdate !== null;

        return [
            'project_id'        => $project->project_id,
            'project_title'     => $project->project_title,
            'project_description' => $project->project_description,
            'project_location'  => $project->project_location,
            'status'            => $project->project_status,
            'property_type'     => $project->property_type,
            'owner_name'        => $project->owner_name,
            'owner_email'       => $project->owner_email,
            'owner_phone'       => $project->owner_phone,
            'contractor_company' => $project->contractor_company,
            'contractor_name'   => $project->contractor_name,
            'contractor_email'  => $project->contractor_email,
            'original_start_date' => $timeline->start_date ?? null,
            'original_end_date'   => $originalEndDate,
            'current_end_date'    => $currentEndDate,
            'was_extended'        => $wasExtended,
            'extension_approved_at' => $firstApprovedUpdate->applied_at ?? null,
            'project_created_at'    => $project->project_created_at,
        ];
    }

    private function buildExecutiveOverview(int $projectId): array
    {
        $plan = DB::table('payment_plans')
            ->where('project_id', $projectId)
            ->orderByDesc('plan_id')
            ->first();

        $currentBudget = $plan ? (float) $plan->total_project_cost : 0;
        $paymentMode   = $plan->payment_mode ?? 'full_payment';
        $downpayment   = $plan ? (float) $plan->downpayment_amount : 0;

        // Find original budget (before any update changed it)
        $firstApprovedUpdate = DB::table('project_updates')
            ->where('project_id', $projectId)
            ->where('status', 'approved')
            ->whereNotNull('current_budget')
            ->orderBy('applied_at', 'asc')
            ->first();

        $originalBudget = $firstApprovedUpdate
            ? (float) $firstApprovedUpdate->current_budget
            : $currentBudget;

        // Milestone counts
        $milestoneItems = DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->where('m.project_id', $projectId)
            ->whereNull('m.is_deleted')
            ->whereNotIn('mi.item_status', ['deleted', 'cancelled'])
            ->select('mi.item_id', 'mi.item_status', 'mi.milestone_item_cost', 'mi.adjusted_cost')
            ->get();

        $totalMilestones    = $milestoneItems->count();
        $completedMilestones = $milestoneItems->where('item_status', 'completed')->count();

        // Financial totals
        $totalPaid = (float) DB::table('milestone_payments')
            ->where('project_id', $projectId)
            ->where('payment_status', 'approved')
            ->sum('amount');

        $totalSubmitted = (float) DB::table('milestone_payments')
            ->where('project_id', $projectId)
            ->where('payment_status', 'submitted')
            ->sum('amount');

        return [
            'original_budget'      => $originalBudget,
            'current_budget'       => $currentBudget,
            'payment_mode'         => $paymentMode,
            'downpayment'          => $downpayment,
            'total_milestones'     => $totalMilestones,
            'completed_milestones' => $completedMilestones,
            'total_paid'           => $totalPaid,
            'total_pending'        => $totalSubmitted,
            'remaining_balance'    => max(0, $currentBudget - $totalPaid),
        ];
    }

    private function buildBudgetHistory(int $projectId): array
    {
        return DB::table('project_updates')
            ->where('project_id', $projectId)
            ->whereIn('status', ['approved', 'rejected', 'pending', 'revision_requested'])
            ->orderBy('created_at', 'asc')
            ->select(
                'extension_id',
                'budget_change_type as change_type',
                'current_budget as previous_budget',
                'proposed_budget as updated_budget',
                'current_end_date as previous_end_date',
                'proposed_end_date as proposed_end_date',
                'reason',
                'status',
                'created_at as date_proposed',
                'applied_at as date_approved'
            )
            ->get()
            ->toArray();
    }

    private function buildMilestoneBreakdown(int $projectId): array
    {
        $items = DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->where('m.project_id', $projectId)
            ->whereNull('m.is_deleted')
            ->whereNotIn('mi.item_status', ['deleted'])
            ->orderBy('mi.sequence_order', 'asc')
            ->select(
                'mi.item_id',
                'mi.milestone_item_title as title',
                'mi.milestone_item_cost as original_allocation',
                'mi.adjusted_cost',
                'mi.carry_forward_amount',
                'mi.percentage_progress',
                'mi.item_status as status',
                'mi.date_to_finish as current_due_date',
                'mi.original_date_to_finish as original_due_date',
                'mi.was_extended',
                'mi.extension_count',
                'mi.settlement_due_date',
                'mi.sequence_order',
                'm.milestone_name'
            )
            ->get();

        // Enrich with payment status
        return $items->map(function ($item) {
            $effectiveCost = $item->adjusted_cost !== null
                ? (float) $item->adjusted_cost
                : (float) $item->original_allocation;

            $totalPaid = (float) DB::table('milestone_payments')
                ->where('item_id', $item->item_id)
                ->where('payment_status', 'approved')
                ->sum('amount');

            $item->current_allocation = $effectiveCost;
            $item->total_paid = $totalPaid;
            $item->remaining = max(0, $effectiveCost - $totalPaid);

            if ($totalPaid >= $effectiveCost && $effectiveCost > 0) {
                $item->payment_status = 'Fully Paid';
            } elseif ($totalPaid > 0) {
                $item->payment_status = 'Partially Paid';
            } else {
                $item->payment_status = 'Unpaid';
            }

            $item->was_extended = (bool) $item->was_extended;
            $item->extension_count = (int) ($item->extension_count ?? 0);

            return $item;
        })->toArray();
    }

    private function buildChangeHistory(int $projectId): array
    {
        $events = collect();

        // 1. Project update records (extensions, budget changes)
        $updates = DB::table('project_updates as pu')
            ->leftJoin('contractors as c_pu', 'pu.contractor_user_id', '=', 'c_pu.user_id')
            ->leftJoin('property_owners as po_pu', 'pu.owner_user_id', '=', 'po_pu.user_id')
            ->where('pu.project_id', $projectId)
            ->orderBy('pu.created_at', 'asc')
            ->select(
                'pu.extension_id',
                'pu.status',
                'pu.budget_change_type',
                'pu.current_end_date',
                'pu.proposed_end_date',
                'pu.current_budget',
                'pu.proposed_budget',
                'pu.reason',
                'pu.owner_response',
                'pu.revision_notes',
                'pu.created_at',
                'pu.applied_at',
                'c_pu.company_name as submitted_by',
                DB::raw("CONCAT(po_pu.first_name, ' ', po_pu.last_name) as reviewed_by")
            )
            ->get();

        foreach ($updates as $u) {
            // Submission event
            $actionLabel = 'Project Update Submitted';
            if ($u->budget_change_type !== 'none') {
                $actionLabel = 'Budget ' . ucfirst($u->budget_change_type) . ' Requested';
            }

            $events->push([
                'date'        => $u->created_at,
                'action'      => $actionLabel,
                'performed_by' => $u->submitted_by,
                'notes'       => $u->reason,
                'reference'   => "Update #{$u->extension_id}",
            ]);

            // Resolution event (if resolved)
            if ($u->status === 'approved') {
                $events->push([
                    'date'        => $u->applied_at ?? $u->created_at,
                    'action'      => 'Project Update Approved',
                    'performed_by' => $u->reviewed_by,
                    'notes'       => $u->owner_response,
                    'reference'   => "Update #{$u->extension_id}",
                ]);
            } elseif ($u->status === 'rejected') {
                $events->push([
                    'date'        => $u->applied_at ?? $u->created_at,
                    'action'      => 'Project Update Rejected',
                    'performed_by' => $u->reviewed_by,
                    'notes'       => $u->owner_response,
                    'reference'   => "Update #{$u->extension_id}",
                ]);
            } elseif ($u->status === 'revision_requested') {
                $events->push([
                    'date'        => $u->applied_at ?? $u->created_at,
                    'action'      => 'Revision Requested',
                    'performed_by' => $u->reviewed_by,
                    'notes'       => $u->revision_notes,
                    'reference'   => "Update #{$u->extension_id}",
                ]);
            } elseif ($u->status === 'withdrawn') {
                $events->push([
                    'date'        => $u->applied_at ?? $u->created_at,
                    'action'      => 'Update Withdrawn',
                    'performed_by' => $u->submitted_by,
                    'notes'       => null,
                    'reference'   => "Update #{$u->extension_id}",
                ]);
            }
        }

        // 2. Milestone date change history
        $dateChanges = DB::table('milestone_date_histories as h')
            ->join('milestone_items as mi', 'h.item_id', '=', 'mi.item_id')
            ->leftJoin('property_owners as po_dc', 'h.changed_by', '=', 'po_dc.user_id')
            ->leftJoin('contractors as c_dc', 'h.changed_by', '=', 'c_dc.user_id')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->where('m.project_id', $projectId)
            ->orderBy('h.changed_at', 'asc')
            ->select(
                'h.changed_at',
                'h.change_reason',
                'h.previous_date',
                'h.new_date',
                'mi.milestone_item_title',
                DB::raw("COALESCE(CONCAT(po_dc.first_name, ' ', po_dc.last_name), c_dc.company_name) as changed_by_name")
            )
            ->get();

        foreach ($dateChanges as $dc) {
            $events->push([
                'date'        => $dc->changed_at,
                'action'      => "Milestone Date Extended: {$dc->milestone_item_title}",
                'performed_by' => $dc->changed_by_name,
                'notes'       => "{$dc->previous_date} → {$dc->new_date}",
                'reference'   => $dc->change_reason,
            ]);
        }

        // Sort all events chronologically
        return $events->sortBy('date')->values()->toArray();
    }

    private function buildPaymentsHistory(int $projectId): array
    {
        $payments = DB::table('milestone_payments as mp')
            ->join('milestone_items as mi', 'mp.item_id', '=', 'mi.item_id')
            ->where('mp.project_id', $projectId)
            ->whereNotIn('mp.payment_status', ['deleted'])
            ->orderBy('mp.transaction_date', 'desc')
            ->select(
                'mp.payment_id',
                'mi.milestone_item_title as milestone',
                'mp.amount',
                'mp.payment_type',
                'mp.transaction_number',
                'mp.transaction_date',
                'mp.payment_status as status',
                'mp.reason'
            )
            ->get();

        $totalApproved = $payments->where('status', 'approved')->sum('amount');
        $totalPending  = $payments->where('status', 'submitted')->sum('amount');
        $totalRejected = $payments->where('status', 'rejected')->sum('amount');

        return [
            'records'        => $payments->toArray(),
            'total_approved' => (float) $totalApproved,
            'total_pending'  => (float) $totalPending,
            'total_rejected' => (float) $totalRejected,
        ];
    }

    private function buildProgressReports(int $projectId): array
    {
        return DB::table('progress as p')
            ->join('milestone_items as mi', 'p.milestone_item_id', '=', 'mi.item_id')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->where('m.project_id', $projectId)
            ->whereNotIn('p.progress_status', ['deleted'])
            ->orderBy('p.submitted_at', 'desc')
            ->select(
                'p.progress_id',
                'p.purpose as report_title',
                'mi.milestone_item_title as milestone',
                'p.progress_status as status',
                'p.submitted_at',
                'p.updated_at'
            )
            ->get()
            ->toArray();
    }
}
