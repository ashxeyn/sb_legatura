<?php

namespace App\Http\Controllers\owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Dedicated controller for downpayment payments.
 *
 * Downpayments live in the `downpayment_payments` table and have a simpler
 * flow than milestone-item payments — no sequential enforcement, no progress
 * reports, no cost-allocation cascading.
 */
class downpaymentController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // AUTH HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Resolve the authenticated user from session, bearer token, or request.
     */
    private function resolveUser(Request $request): ?object
    {
        $user = Session::get('user');
        if ($user) return $user;

        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $token = PersonalAccessToken::findToken($bearerToken);
            if ($token) {
                $user = $token->tokenable;
                if ($user) {
                    Session::put('user', $user);
                    return $user;
                }
            }
        }

        if ($request->user()) {
            $user = $request->user();
            Session::put('user', $user);
            return $user;
        }

        return null;
    }

    /**
     * Resolve the authenticated user and ensure they are an owner.
     * Returns [user, errorResponse].
     */
    private function requireOwner(Request $request): array
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return [null, response()->json(['success' => false, 'message' => 'Authentication required.'], 401)];
        }

        if (!in_array($user->user_type, ['property_owner', 'both'])) {
            return [null, response()->json(['success' => false, 'message' => 'Access denied. Only owners can perform this action.'], 403)];
        }

        if ($user->user_type === 'both') {
            $currentRole = Session::get('current_role', 'owner');
            if ($currentRole !== 'owner') {
                return [null, response()->json(['success' => false, 'message' => 'Please switch to owner role.'], 403)];
            }
        }

        return [$user, null];
    }

    /**
     * Resolve the authenticated user and ensure they are a contractor.
     * Returns [user, errorResponse].
     */
    private function requireContractor(Request $request): array
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return [null, response()->json(['success' => false, 'message' => 'Authentication required.'], 401)];
        }

        if (!in_array($user->user_type, ['contractor', 'both'])) {
            return [null, response()->json(['success' => false, 'message' => 'Access denied. Only contractors can perform this action.'], 403)];
        }

        if ($user->user_type === 'both') {
            $currentRole = Session::get('current_role', $user->preferred_role ?? 'contractor');
            if ($currentRole !== 'contractor') {
                return [null, response()->json(['success' => false, 'message' => 'Please switch to contractor role.'], 403)];
            }
        }

        return [$user, null];
    }

    /**
     * Resolve contractor_user_id for a project (from selected_contractor or payment_plan).
     */
    private function resolveContractorUser(int $projectId): ?object
    {
        return DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->leftJoin('payment_plans as pp', 'p.project_id', '=', 'pp.project_id')
            ->leftJoin('contractors as c', DB::raw('COALESCE(pr.selected_contractor_id, pp.contractor_id)'), '=', 'c.contractor_id')
            ->leftJoin('contractor_users as cu', function ($join) {
                $join->on('c.contractor_id', '=', 'cu.contractor_id')
                    ->where('cu.is_deleted', '=', 0);
            })
            ->where('p.project_id', $projectId)
            ->select(
                DB::raw("COALESCE(
                    MAX(CASE WHEN cu.is_active = 1 AND cu.role = 'owner' THEN cu.contractor_user_id END),
                    MAX(CASE WHEN cu.is_active = 1 THEN cu.contractor_user_id END),
                    MAX(CASE WHEN cu.role = 'owner' THEN cu.contractor_user_id END),
                    MIN(cu.contractor_user_id)
                ) as contractor_user_id"),
                DB::raw("COALESCE(
                    MAX(CASE WHEN cu.is_active = 1 AND cu.role = 'owner' THEN cu.user_id END),
                    MAX(CASE WHEN cu.is_active = 1 THEN cu.user_id END),
                    MAX(CASE WHEN cu.role = 'owner' THEN cu.user_id END),
                    MIN(cu.user_id)
                ) as contractor_notify_user_id")
            )
            ->groupBy('p.project_id')
            ->first();
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPLOAD — Owner submits a downpayment receipt
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /api/downpayment/upload
     */
    public function upload(Request $request)
    {
        try {
            [$user, $error] = $this->requireOwner($request);
            if ($error) return $error;

            $validated = $request->validate([
                'project_id'         => 'required|integer',
                'amount'             => 'required|numeric|min:0.01',
                'payment_type'       => 'required|string|in:cash,check,bank_transfer,online_payment',
                'transaction_number' => 'nullable|string|max:100',
                'receipt_photo'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'transaction_date'   => 'nullable|date',
            ]);

            // Resolve owner_id
            $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
            if (!$owner) {
                return response()->json(['success' => false, 'message' => 'Owner record not found.'], 403);
            }

            // Resolve contractor_user_id for this project
            $contractorInfo = $this->resolveContractorUser($validated['project_id']);
            if (!$contractorInfo || !$contractorInfo->contractor_user_id) {
                return response()->json(['success' => false, 'message' => 'Contractor not found for this project.'], 403);
            }

            // Verify owner has access to this project
            $project = DB::table('projects as p')
                ->leftJoin('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->where('p.project_id', $validated['project_id'])
                ->select('p.project_id', 'p.project_title', 'pr.owner_id')
                ->first();

            if (!$project || $project->owner_id != $owner->owner_id) {
                return response()->json(['success' => false, 'message' => 'Project not found or access denied.'], 403);
            }

            // Handle receipt photo upload
            $receiptPath = null;
            if ($request->hasFile('receipt_photo')) {
                $file = $request->file('receipt_photo');
                $filename = time() . '_dp_' . uniqid() . '.' . $file->getClientOriginalExtension();
                Storage::disk('public')->makeDirectory('payments/downpayment');
                $receiptPath = $file->storeAs('payments/downpayment', $filename, 'public');
            }

            // Insert into downpayment_payments
            $dpPaymentId = DB::table('downpayment_payments')->insertGetId([
                'project_id'         => $validated['project_id'],
                'owner_id'           => $owner->owner_id,
                'contractor_user_id' => $contractorInfo->contractor_user_id,
                'amount'             => $validated['amount'],
                'payment_type'       => $validated['payment_type'],
                'transaction_number' => $validated['transaction_number'] ?? null,
                'receipt_photo'      => $receiptPath ?? '',
                'transaction_date'   => $validated['transaction_date'] ?? now()->toDateString(),
                'payment_status'     => 'submitted',
            ], 'dp_payment_id');

            // Log cumulative vs required for awareness
            $plan = DB::table('payment_plans')->where('project_id', $validated['project_id'])->first();
            $requiredAmount = $plan ? (float) $plan->downpayment_amount : 0;
            $existingApproved = (float) DB::table('downpayment_payments')
                ->where('project_id', $validated['project_id'])
                ->where('payment_status', 'approved')
                ->sum('amount');

            \Log::info('Downpayment receipt uploaded', [
                'dp_payment_id'   => $dpPaymentId,
                'project_id'      => $validated['project_id'],
                'amount'          => $validated['amount'],
                'required'        => $requiredAmount,
                'total_approved'  => $existingApproved,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Downpayment receipt uploaded successfully.',
                'data'    => ['dp_payment_id' => $dpPaymentId],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Downpayment upload error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'An error occurred while uploading the receipt.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // LIST — Get downpayment receipts for a project
    // ─────────────────────────────────────────────────────────────────────

    /**
     * GET /api/projects/{projectId}/downpayment-payments
     */
    public function list(Request $request, int $projectId)
    {
        try {
            $user = $this->resolveUser($request);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
            }

            $payments = DB::table('downpayment_payments as dp')
                ->leftJoin('property_owners as po', 'dp.owner_id', '=', 'po.owner_id')
                ->where('dp.project_id', $projectId)
                ->whereNotIn('dp.payment_status', ['deleted'])
                ->select(
                    'dp.dp_payment_id as payment_id',
                    'dp.amount',
                    'dp.payment_type',
                    'dp.transaction_number',
                    'dp.receipt_photo',
                    'dp.transaction_date',
                    'dp.payment_status',
                    'dp.reason',
                    'dp.updated_at',
                    DB::raw('CONCAT(po.first_name, " ", po.last_name) as owner_name')
                )
                ->orderBy('dp.transaction_date', 'desc')
                ->orderBy('dp.dp_payment_id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'payments'    => $payments,
                    'total_count' => $payments->count(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Downpayment list error: ' . $e->getMessage(), ['project_id' => $projectId]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching downpayment receipts.',
                'data'    => ['payments' => [], 'total_count' => 0],
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // APPROVE — Contractor approves a downpayment receipt
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /api/downpayment/{id}/approve
     */
    public function approve(Request $request, int $dpPaymentId)
    {
        try {
            [$user, $error] = $this->requireContractor($request);
            if ($error) return $error;

            $payment = DB::table('downpayment_payments')->where('dp_payment_id', $dpPaymentId)->first();
            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
            }

            if ($payment->payment_status !== 'submitted') {
                return response()->json(['success' => false, 'message' => 'Only submitted payments can be approved.'], 422);
            }

            DB::table('downpayment_payments')
                ->where('dp_payment_id', $dpPaymentId)
                ->update([
                    'payment_status' => 'approved',
                    'updated_at'     => now(),
                ]);

            \Log::info('Downpayment receipt approved', [
                'dp_payment_id' => $dpPaymentId,
                'approved_by'   => $user->user_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Downpayment receipt approved.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Downpayment approve error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // REJECT — Contractor rejects a downpayment receipt
    // ─────────────────────────────────────────────────────────────────────

    /**
     * POST /api/downpayment/{id}/reject
     */
    public function reject(Request $request, int $dpPaymentId)
    {
        try {
            [$user, $error] = $this->requireContractor($request);
            if ($error) return $error;

            $validated = $request->validate([
                'reason' => 'required|string|max:1000',
            ], [
                'reason.required' => 'Please provide a reason for rejection.',
            ]);

            $payment = DB::table('downpayment_payments')->where('dp_payment_id', $dpPaymentId)->first();
            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
            }

            if ($payment->payment_status !== 'submitted') {
                return response()->json(['success' => false, 'message' => 'Only submitted payments can be rejected.'], 422);
            }

            DB::table('downpayment_payments')
                ->where('dp_payment_id', $dpPaymentId)
                ->update([
                    'payment_status' => 'rejected',
                    'reason'         => $validated['reason'],
                    'updated_at'     => now(),
                ]);

            \Log::info('Downpayment receipt rejected', [
                'dp_payment_id' => $dpPaymentId,
                'rejected_by'   => $user->user_id,
                'reason'         => $validated['reason'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Downpayment receipt rejected.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Downpayment reject error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }
}
