<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use App\Services\milestoneService;
use App\Services\contractorAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * milestoneController — Consolidated milestone management.
 *
 * Owner routes  (API, token-auth via user_id param):
 *   POST   /owner/milestones/{milestoneId}/approve
 *   POST   /owner/milestones/{milestoneId}/reject
 *   POST   /owner/milestones/{milestoneId}/complete
 *   POST   /owner/milestone-items/{itemId}/complete
 *
 * Contractor routes (API, token-auth via user_id param):
 *   POST   /contractor/projects/{projectId}/milestones
 *   PUT    /contractor/projects/{projectId}/milestones/{milestoneId}
 *   DELETE /contractor/milestones/{milestoneId}
 *
 * Payment routes (API, session + token auth):
 *   POST   /payments/{id}/approve
 *   POST   /payments/{id}/reject
 *
 * Web (session-auth, Blade):
 *   POST   /owner/milestones/{milestoneId}/approve  (web)
 *   POST   /owner/milestones/{milestoneId}/reject   (web)
 */
class milestoneController extends Controller
{
    public function __construct(
        protected milestoneService $milestoneService,
        protected contractorAuthorizationService $authService,
    ) {}

    // ───────────────────────────────────────────────────────────────────────
    // OWNER — Approve
    // ───────────────────────────────────────────────────────────────────────

    /** API: approve a submitted milestone (owner). */
    public function apiApproveMilestone(Request $request, int $milestoneId)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 400);
        }

        $owner = $this->milestoneService->resolveOwner((int) $userId);
        if (!$owner) {
            return response()->json(['success' => false, 'message' => 'Property owner record not found.'], 404);
        }

        $result = $this->milestoneService->approve($milestoneId, $owner->owner_id);
        $status = $result['status'] ?? 200;
        unset($result['status']);
        return response()->json($result, $status);
    }

    /** Web (session): approve a submitted milestone (owner). */
    public function webApproveMilestone(Request $request, int $milestoneId)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/accounts/login')->with('error', 'Please log in.');
        }

        $owner = $this->milestoneService->resolveOwner((int) $user['id']);
        if (!$owner) {
            return back()->with('error', 'Property owner record not found.');
        }

        $result = $this->milestoneService->approve($milestoneId, $owner->owner_id);
        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }
        return back()->with('success', $result['message']);
    }

    // ───────────────────────────────────────────────────────────────────────
    // OWNER — Reject
    // ───────────────────────────────────────────────────────────────────────

    /** API: reject a submitted milestone (owner). */
    public function apiRejectMilestone(Request $request, int $milestoneId)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 400);
        }

        $owner = $this->milestoneService->resolveOwner((int) $userId);
        if (!$owner) {
            return response()->json(['success' => false, 'message' => 'Property owner record not found.'], 404);
        }

        $reason = $request->input('rejection_reason', $request->input('reason', ''));

        $result = $this->milestoneService->reject($milestoneId, $owner->owner_id, (string) $reason);
        $status = $result['status'] ?? 200;
        unset($result['status']);
        return response()->json($result, $status);
    }

    /** Web (session): reject a submitted milestone (owner). */
    public function webRejectMilestone(Request $request, int $milestoneId)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/accounts/login')->with('error', 'Please log in.');
        }

        $owner = $this->milestoneService->resolveOwner((int) $user['id']);
        if (!$owner) {
            return back()->with('error', 'Property owner record not found.');
        }

        $reason = $request->input('rejection_reason', $request->input('reason', ''));
        $result = $this->milestoneService->reject($milestoneId, $owner->owner_id, (string) $reason);
        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }
        return back()->with('success', $result['message']);
    }

    // ───────────────────────────────────────────────────────────────────────
    // OWNER — Mark milestone complete
    // ───────────────────────────────────────────────────────────────────────

    /** API: mark a milestone as completed (owner). */
    public function apiSetMilestoneComplete(Request $request, int $milestoneId)
    {
        $result = $this->milestoneService->completeMilestone($milestoneId);
        $status = $result['status'] ?? 200;
        unset($result['status']);
        return response()->json($result, $status);
    }

    // ───────────────────────────────────────────────────────────────────────
    // OWNER — Mark milestone item complete
    // ───────────────────────────────────────────────────────────────────────

    /** API: mark a milestone item as completed (owner). */
    public function apiSetMilestoneItemComplete(Request $request, int $itemId)
    {
        $result = $this->milestoneService->completeMilestoneItem($itemId);
        $status = $result['status'] ?? 200;
        unset($result['status']);
        return response()->json($result, $status);
    }

    // ───────────────────────────────────────────────────────────────────────
    // CONTRACTOR — Submit milestone plan
    // ───────────────────────────────────────────────────────────────────────

    /** API: contractor submits a new milestone plan for a project. */
    public function apiSubmitMilestones(Request $request, int $projectId)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 400);
        }

        // Authorization: only owner/representative can create milestones
        $authError = $this->authService->validateMilestoneAccess($userId);
        if ($authError) {
            return response()->json(['success' => false, 'message' => $authError, 'error_code' => 'UNAUTHORIZED_MILESTONE'], 403);
        }

        $contractor = $this->authService->getContractorForUser($userId);
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor not found for user_id: ' . $userId], 404);
        }

        $validated = $request->validate([
            'milestone_name'         => 'required|string|max:200',
            'payment_mode'           => 'required|in:downpayment,full_payment',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after_or_equal:start_date',
            'total_project_cost'     => 'required|numeric|min:0',
            'downpayment_amount'     => 'nullable|numeric|min:0',
            'items'                  => 'required|array|min:1',
            'items.*.title'          => 'required|string|max:255',
            'items.*.description'    => 'nullable|string',
            'items.*.percentage'     => 'required|numeric|min:0.01|max:100',
            'items.*.start_date'     => 'nullable|date',
            'items.*.date_to_finish' => 'required|date',
        ]);

        // Process uploaded files per milestone item (item_files_{index}[])
        foreach ($validated['items'] as $index => &$item) {
            $itemFiles = $request->file("item_files_{$index}") ?? [];
            $paths = [];
            foreach ((array)$itemFiles as $file) {
                if ($file && $file->isValid()) {
                    $paths[] = $file->store('milestone_items', 'public');
                }
            }
            $item['file_paths'] = $paths;
        }
        unset($item);

        $result = $this->milestoneService->submit($validated, $contractor, $projectId);
        $status = $result['status'] ?? 200;
        unset($result['status']);
        return response()->json($result, $status);
    }

    // ───────────────────────────────────────────────────────────────────────
    // CONTRACTOR — Update milestone plan
    // ───────────────────────────────────────────────────────────────────────

    /** API: contractor updates an existing milestone plan. */
    public function apiUpdateMilestone(Request $request, int $projectId, int $milestoneId)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 400);
        }

        $authError = $this->authService->validateMilestoneAccess($userId);
        if ($authError) {
            return response()->json(['success' => false, 'message' => $authError, 'error_code' => 'UNAUTHORIZED_MILESTONE'], 403);
        }

        $contractor = $this->authService->getContractorForUser($userId);
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor not found for user_id: ' . $userId], 404);
        }

        $validated = $request->validate([
            'milestone_name'         => 'required|string|max:200',
            'payment_mode'           => 'required|in:downpayment,full_payment',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after_or_equal:start_date',
            'total_project_cost'     => 'required|numeric|min:0',
            'downpayment_amount'     => 'nullable|numeric|min:0',
            'items'                  => 'required|array|min:1',
            'items.*.title'          => 'required|string|max:255',
            'items.*.description'    => 'nullable|string',
            'items.*.percentage'     => 'required|numeric|min:0.01|max:100',
            'items.*.start_date'     => 'nullable|date',
            'items.*.date_to_finish' => 'required|date',
        ]);

        // Process uploaded files per milestone item (item_files_{index}[])
        foreach ($validated['items'] as $index => &$item) {
            $itemFiles = $request->file("item_files_{$index}") ?? [];
            $paths = [];
            foreach ((array)$itemFiles as $file) {
                if ($file && $file->isValid()) {
                    $paths[] = $file->store('milestone_items', 'public');
                }
            }
            $item['file_paths'] = $paths;
        }
        unset($item);

        $result = $this->milestoneService->update($milestoneId, $projectId, $validated, $contractor);
        $status = $result['status'] ?? 200;
        unset($result['status']);
        return response()->json($result, $status);
    }

    // ───────────────────────────────────────────────────────────────────────
    // CONTRACTOR — Delete milestone
    // ───────────────────────────────────────────────────────────────────────

    /** API: contractor soft-deletes a milestone. */
    public function deleteMilestone(Request $request, int $milestoneId)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 400);
        }

        $contractor = $this->authService->getContractorForUser($userId);
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor not found.'], 404);
        }

        $reason = (string) $request->input('reason', '');

        $result = $this->milestoneService->delete($milestoneId, (int) $contractor->contractor_id, $reason);
        $status = $result['status'] ?? 200;
        unset($result['status']);
        return response()->json($result, $status);
    }

    // ───────────────────────────────────────────────────────────────────────
    // CONTRACTOR — Set / update settlement due date for a milestone item
    // ───────────────────────────────────────────────────────────────────────

    /**
     * API: contractor sets or updates the settlement_due_date (and optional extension_date)
     * on a milestone item. Both parties can view the deadline; only the contractor can set it.
     *
     * POST /api/contractor/milestone-items/{itemId}/settlement-due-date
     */
    public function setSettlementDueDate(Request $request, int $itemId)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 400);
        }

        $contractor = $this->authService->getContractorForUser($userId);
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor not found.'], 404);
        }

        $validated = $request->validate([
            'settlement_due_date' => 'required|date|after_or_equal:today',
            'extension_date'      => 'nullable|date|after:settlement_due_date',
        ]);

        // Verify the item belongs to a milestone owned by this contractor
        $item = \DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->where('mi.item_id', $itemId)
            ->where('m.contractor_id', $contractor->contractor_id)
            ->select('mi.*', 'm.project_id', 'm.milestone_name')
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Milestone item not found or access denied.'], 404);
        }

        $updateData = [
            'settlement_due_date' => $validated['settlement_due_date'],
            'updated_at'          => now(),
        ];
        if (array_key_exists('extension_date', $validated)) {
            $updateData['extension_date'] = $validated['extension_date'];
        }

        \DB::table('milestone_items')
            ->where('item_id', $itemId)
            ->update($updateData);

        // Notify the project owner about the settlement deadline
        $ownerUserId = \DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->where('p.project_id', $item->project_id)
            ->value('po.user_id');

        if ($ownerUserId) {
            $dueFormatted = date('M d, Y', strtotime($validated['settlement_due_date']));
            \App\Services\notificationService::create(
                (int) $ownerUserId,
                'payment_due',
                'Payment Due Date Set',
                "Contractor set a payment deadline of {$dueFormatted} for \"{$item->milestone_item_title}\".",
                'high',
                'milestone_item',
                $itemId,
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $item->project_id, 'tab' => 'payments']]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Settlement due date updated successfully.',
            'data'    => [
                'item_id'              => $itemId,
                'settlement_due_date'  => $validated['settlement_due_date'],
                'extension_date'       => $validated['extension_date'] ?? null,
            ],
        ]);
    }

    // ───────────────────────────────────────────────────────────────────────
    // OWNER — Set / update settlement due date for a milestone item
    // ───────────────────────────────────────────────────────────────────────

    /**
     * API: owner sets or updates the settlement_due_date (and optional extension_date)
     * on a milestone item.
     *
     * POST /api/owner/milestone-items/{itemId}/settlement-due-date
     */
    public function setSettlementDueDateOwner(Request $request, int $itemId)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required.'], 400);
        }

        $owner = $this->milestoneService->resolveOwner((int) $userId);
        if (!$owner) {
            return response()->json(['success' => false, 'message' => 'Property owner not found.'], 404);
        }

        $validated = $request->validate([
            'settlement_due_date' => 'required|date|after_or_equal:today',
            'extension_date'      => 'nullable|date|after:settlement_due_date',
        ]);

        // Verify the item belongs to a project owned by this owner
        $item = \DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->join('projects as p', 'm.project_id', '=', 'p.project_id')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->where('mi.item_id', $itemId)
            ->where('pr.owner_id', $owner->owner_id)
            ->select('mi.*', 'm.project_id', 'm.milestone_name', 'p.project_title', 'p.selected_contractor_id')
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Milestone item not found or access denied.'], 404);
        }

        $updateData = [
            'settlement_due_date' => $validated['settlement_due_date'],
            'updated_at'          => now(),
        ];
        if (array_key_exists('extension_date', $validated)) {
            $updateData['extension_date'] = $validated['extension_date'];
        }

        \DB::table('milestone_items')
            ->where('item_id', $itemId)
            ->update($updateData);

        // Notify the contractor about the settlement deadline
        if ($item->selected_contractor_id) {
            $contractorUserId = \DB::table('contractors')
                ->where('contractor_id', $item->selected_contractor_id)
                ->value('user_id');

            if (!$contractorUserId) {
                // Try contractor_users (staff)
                $contractorUserId = \DB::table('contractor_users')
                    ->where('contractor_id', $item->selected_contractor_id)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->value('user_id');
            }

            if ($contractorUserId) {
                $dueFormatted = date('M d, Y', strtotime($validated['settlement_due_date']));
                \App\Services\notificationService::create(
                    (int) $contractorUserId,
                    'payment_due',
                    'Payment Due Date Set',
                    "Owner set a payment deadline of {$dueFormatted} for \"{$item->milestone_item_title}\".",
                    'high',
                    'milestone_item',
                    $itemId,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $item->project_id, 'tab' => 'payments']]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settlement due date updated successfully.',
            'data'    => [
                'item_id'              => $itemId,
                'settlement_due_date'  => $validated['settlement_due_date'],
                'extension_date'       => $validated['extension_date'] ?? null,
            ],
        ]);
    }

    // ───────────────────────────────────────────────────────────────────────
    // PAYMENT — Approve (contractor)
    // ───────────────────────────────────────────────────────────────────────

    /**
     * API: contractor approves a submitted milestone payment.
     *
     * POST /api/payments/{id}/approve
     */
    public function apiApprovePayment(Request $request, int $paymentId)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        // Only contractor or both-as-contractor
        if (!in_array($user->user_type, ['contractor', 'both'])) {
            return response()->json(['success' => false, 'message' => 'Access denied. Only contractors can approve payments.'], 403);
        }
        if ($user->user_type === 'both') {
            $currentRole = Session::get('current_role', $user->preferred_role ?? 'contractor');
            if ($currentRole !== 'contractor') {
                return response()->json(['success' => false, 'message' => 'Please switch to contractor role.'], 403);
            }
        }

        $contractor = $this->milestoneService->resolveContractor((int) $user->user_id);
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor profile not found.'], 404);
        }

        $result = $this->milestoneService->approvePayment($paymentId, (int) $contractor->contractor_id, (int) $user->user_id);
        $status = $result['status'] ?? 200;
        unset($result['status']);
        return response()->json($result, $status);
    }

    // ───────────────────────────────────────────────────────────────────────
    // PAYMENT — Reject (contractor)
    // ───────────────────────────────────────────────────────────────────────

    /**
     * API: contractor rejects a submitted milestone payment.
     *
     * POST /api/payments/{id}/reject
     */
    public function apiRejectPayment(Request $request, int $paymentId)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        // Only contractor or both-as-contractor
        if (!in_array($user->user_type, ['contractor', 'both'])) {
            return response()->json(['success' => false, 'message' => 'Access denied. Only contractors can reject payments.'], 403);
        }
        if ($user->user_type === 'both') {
            $currentRole = Session::get('current_role', $user->preferred_role ?? 'contractor');
            if ($currentRole !== 'contractor') {
                return response()->json(['success' => false, 'message' => 'Please switch to contractor role.'], 403);
            }
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'Please provide a reason for rejecting this payment.',
        ]);

        $contractor = $this->milestoneService->resolveContractor((int) $user->user_id);
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor profile not found.'], 404);
        }

        $result = $this->milestoneService->rejectPayment($paymentId, (int) $contractor->contractor_id, $request->input('reason'));
        $status = $result['status'] ?? 200;
        unset($result['status']);
        return response()->json($result, $status);
    }

    // ───────────────────────────────────────────────────────────────────────
    // PAYMENT — Get item payment summary (both roles)
    // ───────────────────────────────────────────────────────────────────────

    /**
     * API: return backend-computed payment summary for a milestone item.
     *
     * GET /api/milestone-items/{itemId}/payment-summary
     */
    public function apiGetItemPaymentSummary(Request $request, int $itemId)
    {
        $user = $this->resolveAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $summary = $this->milestoneService->getItemPaymentSummary($itemId);
        if (isset($summary['error'])) {
            return response()->json(['success' => false, 'message' => $summary['error']], 404);
        }

        return response()->json(['success' => true, 'data' => $summary]);
    }

    // ───────────────────────────────────────────────────────────────────────
    // ─── Date Extension History ──────────────────────────────────────────
    /**
     * GET /milestone-items/{itemId}/date-history
     * Returns the full date change history for a milestone item.
     */
    public function getDateHistory(Request $request, int $itemId)
    {
        $item = DB::table('milestone_items')->where('item_id', $itemId)->first();
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item not found.'], 404);
        }

        $histories = DB::table('milestone_date_histories as h')
            ->leftJoin('project_updates as pu', 'h.extension_id', '=', 'pu.extension_id')
            ->leftJoin('property_owners as po_h', 'h.changed_by', '=', 'po_h.user_id')
            ->leftJoin('contractors as c_h', 'h.changed_by', '=', 'c_h.user_id')
            ->where('h.item_id', $itemId)
            ->orderBy('h.changed_at', 'asc')
            ->select(
                'h.id',
                'h.previous_date',
                'h.new_date',
                'h.extension_id',
                'h.changed_by',
                'h.changed_at',
                'h.change_reason',
                'pu.reason as extension_reason',
                'pu.status as extension_status',
                DB::raw("COALESCE(CONCAT(po_h.first_name, ' ', po_h.last_name), c_h.company_name) as changed_by_name")
            )
            ->get();

        return response()->json([
            'success' => true,
            'item_id' => $itemId,
            'title'   => $item->milestone_item_title,
            'current_date_to_finish'  => $item->date_to_finish,
            'original_date_to_finish' => $item->original_date_to_finish,
            'was_extended'    => (bool) $item->was_extended,
            'extension_count' => (int) $item->extension_count,
            'histories' => $histories,
        ]);
    }

    // HELPERS — Shared auth resolution
    // ───────────────────────────────────────────────────────────────────────

    /**
     * Resolve the authenticated user from session, Sanctum bearer token, or request->user().
     */
    private function resolveAuthenticatedUser(Request $request): ?object
    {
        $user = Session::get('user');
        if ($user) return $user;

        // Try bearer token
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $token = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);
            if ($token) {
                $user = $token->tokenable;
                if ($user) {
                    Session::put('user', $user);
                    return $user;
                }
            }
        }

        // Fallback
        if ($request->user()) {
            $user = $request->user();
            Session::put('user', $user);
            return $user;
        }

        return null;
    }
}
