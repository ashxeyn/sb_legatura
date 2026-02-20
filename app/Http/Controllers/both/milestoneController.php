<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use App\Services\milestoneService;
use App\Services\ContractorAuthorizationService;
use Illuminate\Http\Request;
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
 * Web (session-auth, Blade):
 *   POST   /owner/milestones/{milestoneId}/approve  (web)
 *   POST   /owner/milestones/{milestoneId}/reject   (web)
 */
class milestoneController extends Controller
{
    public function __construct(
        protected milestoneService $milestoneService,
        protected ContractorAuthorizationService $authService,
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
            'items.*.date_to_finish' => 'required|date',
        ]);

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
            'items.*.date_to_finish' => 'required|date',
        ]);

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
}
