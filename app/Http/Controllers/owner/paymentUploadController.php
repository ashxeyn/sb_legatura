<?php

namespace App\Http\Controllers\owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\owner\paymentUploadRequest;
use App\Models\owner\paymentUploadClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\NotificationService;

class paymentUploadController extends Controller
{
	protected $paymentClass;

	public function __construct()
	{
		$this->paymentClass = new paymentUploadClass();
	}

	private function checkOwnerAccess(Request $request)
	{
		\Log::info('checkOwnerAccess called', ['bearer_token' => $request->bearerToken() ? 'present' : 'missing']);
		
		// Support both session-based auth (web) and token-based auth (mobile API)
		$user = Session::get('user');
		
		// If no session user, try to authenticate via Sanctum token
		if (!$user) {
			$bearerToken = $request->bearerToken();
			\Log::info('checkOwnerAccess: No session user, checking bearer token', ['token_present' => $bearerToken ? 'yes' : 'no']);
			
			if ($bearerToken) {
				// Find the token in the database
				$token = PersonalAccessToken::findToken($bearerToken);
				if ($token) {
					// Get the user associated with the token
					$user = $token->tokenable;
					\Log::info('checkOwnerAccess: Token found, user authenticated', ['user_id' => $user->user_id ?? null]);
					// Store user in session for downstream code that expects it there
					if ($user && !Session::has('user')) {
						Session::put('user', $user);
					}
				} else {
					\Log::warning('checkOwnerAccess: Token not found in database');
				}
			}
			
			// Fallback to request->user() if available (when middleware is applied)
			if (!$user && $request->user()) {
				$user = $request->user();
				\Log::info('checkOwnerAccess: Using request->user()', ['user_id' => $user->user_id ?? null]);
				if (!Session::has('user')) {
					Session::put('user', $user);
				}
			}
		} else {
			\Log::info('checkOwnerAccess: Using session user', ['user_id' => $user->user_id ?? null]);
		}
		
		if (!$user) {
			\Log::warning('checkOwnerAccess: No user found, returning 401');
			if ($request->expectsJson()) {
				return response()->json(['success' => false, 'message' => 'Authentication required', 'redirect_url' => '/accounts/login'], 401);
			}
			return redirect('/accounts/login');
		}
		
		// Store user in session for downstream code that expects it there
		if (!Session::has('user')) {
			Session::put('user', $user);
		}

		if (!in_array($user->user_type, ['property_owner', 'both'])) {
			if ($request->expectsJson()) {
				return response()->json(['success' => false, 'message' => 'Access denied. Only owners can upload payments.'], 403);
			}
			return redirect('/dashboard')->with('error', 'Access denied. Only owners can upload payments.');
		}

		if ($user->user_type === 'both') {
			$currentRole = Session::get('current_role', 'owner');
			if ($currentRole !== 'owner') {
				if ($request->expectsJson()) {
					return response()->json(['success' => false, 'message' => 'Please switch to owner role to upload payments.'], 403);
				}
				return redirect('/dashboard')->with('error', 'Please switch to owner role to upload payments.');
			}
		}

		return null;
	}

	public function uploadPayment(paymentUploadRequest $request)
	{
		try {
			\Log::info('uploadPayment called', ['bearer_token' => $request->bearerToken() ? 'present' : 'missing']);
			
			$auth = $this->checkOwnerAccess($request);
			if ($auth) return $auth;

			$user = Session::get('user');
			if (!$user) {
				\Log::error('uploadPayment: No user found after checkOwnerAccess');
				return response()->json(['success' => false, 'message' => 'Authentication failed'], 401);
			}
			
			\Log::info('uploadPayment: User authenticated', ['user_id' => $user->user_id ?? null]);
			
			$validated = $request->validated();
			\Log::info('uploadPayment: Request validated', ['item_id' => $validated['item_id'] ?? null, 'project_id' => $validated['project_id'] ?? null]);

			$receiptPath = null;
			if ($request->hasFile('receipt_photo')) {
				$file = $request->file('receipt_photo');
				$filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
				Storage::disk('public')->makeDirectory('payments/receipts');
				$receiptPath = $file->storeAs('payments/receipts', $filename, 'public');
			}

			// Get contractor user id from project
		// Get primary contractor user (owner role preferred, or first active user, then any user)
			$project = DB::table('projects as p')
				->leftJoin('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
				->leftJoin('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
			->leftJoin('contractor_users as cu', function($join) {
				$join->on('c.contractor_id', '=', 'cu.contractor_id')
					->where('cu.is_deleted', '=', 0);
			})
				->where('p.project_id', $validated['project_id'])
			->select(
				'p.project_id',
				'p.project_title',
				'pr.owner_id', 
				DB::raw('COALESCE(
					MAX(CASE WHEN cu.is_active = 1 AND cu.role = "owner" THEN cu.contractor_user_id END),
					MAX(CASE WHEN cu.is_active = 1 THEN cu.contractor_user_id END),
					MAX(CASE WHEN cu.role = "owner" THEN cu.contractor_user_id END),
					MIN(cu.contractor_user_id)
				) as contractor_user_id'),
				DB::raw('COALESCE(
					MAX(CASE WHEN cu.is_active = 1 AND cu.role = "owner" THEN cu.user_id END),
					MAX(CASE WHEN cu.is_active = 1 THEN cu.user_id END),
					MAX(CASE WHEN cu.role = "owner" THEN cu.user_id END),
					MIN(cu.user_id)
				) as contractor_notify_user_id')
			)
			->groupBy('p.project_id', 'p.project_title', 'pr.owner_id')
				->first();

			// Get owner_id from property_owners table
			$owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
			$ownerId = $owner ? $owner->owner_id : null;

		if (!$ownerId) {
			return response()->json(['success' => false, 'message' => 'Owner record not found. Please contact support.'], 403);
		}

			$hasAccess = false;
			if ($ownerId && $project && $project->owner_id) {
				$hasAccess = ($project->owner_id == $ownerId);
			} else if ($project) {
				// Legacy: compare user_id directly
				$hasAccess = ($project->owner_id == $user->user_id);
			}

			if (!$project || !$hasAccess) {
				return response()->json(['success' => false, 'message' => 'Project not found or access denied'], 403);
			}

		// Validate contractor_user_id exists
		if (!$project->contractor_user_id) {
			return response()->json(['success' => false, 'message' => 'Contractor user not found for this project. Please contact support.'], 403);
		}

			// Allow multiple partial payments for the same milestone item.
			// Log a warning if cumulative amount exceeds milestone item cost, but still allow submission.
			$existingTotal = DB::table('milestone_payments')
				->where('item_id', $validated['item_id'])
				->where('owner_id', $ownerId)
				->whereNotIn('payment_status', ['rejected', 'deleted'])
				->sum('amount');

			// Get the expected cost for this milestone item
			$milestoneItem = DB::table('milestone_items')
				->where('item_id', $validated['item_id'])
				->first();

			$expectedAmount = $milestoneItem ? (float) ($milestoneItem->milestone_item_cost ?? 0) : 0;
			$newTotal = $existingTotal + (float) $validated['amount'];

			if ($expectedAmount > 0 && $newTotal > $expectedAmount) {
				\Log::warning('Payment exceeds milestone item cost', [
					'item_id' => $validated['item_id'],
					'expected_amount' => $expectedAmount,
					'existing_total' => $existingTotal,
					'new_payment' => (float) $validated['amount'],
					'new_total' => $newTotal,
				]);
			}

			// ── Sequential enforcement: previous item must be completed first ──
			$currentItemDetail = DB::table('milestone_items')
				->where('item_id', $validated['item_id'])
				->first();
			if ($currentItemDetail) {
				$prevItem = DB::table('milestone_items')
					->where('milestone_id', $currentItemDetail->milestone_id)
					->where('sequence_order', '<', $currentItemDetail->sequence_order)
					->orderBy('sequence_order', 'desc')
					->first();
				if ($prevItem && ($prevItem->item_status ?? '') !== 'completed') {
					return response()->json(['success' => false, 'message' => 'You must complete the previous milestone item first before uploading payment for this one.'], 422);
				}
			}

			// Ensure that a contractor progress report for this milestone item exists and has been approved by the owner
			$approvedProgress = DB::table('progress as pr')
				->join('milestone_items as mi', 'pr.milestone_item_id', '=', 'mi.item_id')
				->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
				->join('projects as p', 'm.project_id', '=', 'p.project_id')
				->where('pr.milestone_item_id', $validated['item_id'])
				->where('p.project_id', $project->project_id)
				->where('pr.progress_status', 'approved')
				->first();

			if (!$approvedProgress) {
				return response()->json(['success' => false, 'message' => 'Cannot upload payment validation. Contractor must submit a progress report that has been approved before payments can be uploaded.'], 403);
			}

			// Normalize transaction_date to DATE format (table uses DATE)
			$transactionDate = null;
			if (!empty($validated['transaction_date'])) {
				$transactionDate = date('Y-m-d', strtotime($validated['transaction_date']));
			}

			$data = [
				'item_id' => $validated['item_id'],
				'project_id' => $validated['project_id'],
			'owner_id' => $ownerId,
			'contractor_user_id' => $project->contractor_user_id,
				'amount' => $validated['amount'],
				'payment_type' => $validated['payment_type'],
				'transaction_number' => $validated['transaction_number'] ?? null,
			'receipt_photo' => $receiptPath ?? '',
				'transaction_date' => $transactionDate,
				'payment_status' => 'submitted'
			];

			$paymentId = $this->paymentClass->createPayment($data);
			\Log::info('uploadPayment: Payment created successfully', ['payment_id' => $paymentId]);

			// Auto-advance item_status from not_started to in_progress
			DB::table('milestone_items')
				->where('item_id', $validated['item_id'])
				->where('item_status', 'not_started')
				->update(['item_status' => 'in_progress', 'updated_at' => now()]);

			// Notify contractor about payment upload
			// Use contractor_notify_user_id (users.user_id) not contractor_user_id (contractor_users PK)
			$contractorUserId = $project->contractor_notify_user_id ?? null;
			if ($contractorUserId) {
				$projTitle = $project->project_title ?? '';
				NotificationService::create((int)$contractorUserId, 'payment_submitted', 'Payment Uploaded', "Owner uploaded a payment for \"{$projTitle}\". Please review.", 'normal', 'payment', (int)$paymentId, ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int)$validated['project_id'], 'tab' => 'payments']]);
			}

			return response()->json(['success' => true, 'message' => 'Payment validation uploaded', 'payment_id' => $paymentId], 201);
		} catch (\Exception $e) {
			$userId = Session::get('user')->user_id ?? null;
			\Log::error('uploadPayment error: ' . $e->getMessage(), [
				'user_id' => $userId,
				'trace' => $e->getTraceAsString(),
				'request_data' => $request->except(['receipt_photo']) // Exclude file from log
			]);
			
			return response()->json([
				'success' => false,
				'message' => 'Error uploading payment: ' . $e->getMessage()
			], 500);
		}
	}

	public function updatePayment(Request $request, $paymentId)
	{
		try {
			$auth = $this->checkOwnerAccess($request);
			if ($auth) return $auth;

			$user = Session::get('user');

			// Get owner_id from property_owners table
			$owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
			$ownerId = $owner ? $owner->owner_id : null;

			if (!$ownerId) {
				return response()->json(['success' => false, 'message' => 'Owner record not found. Please contact support.'], 403);
			}

			$payment = $this->paymentClass->getPaymentById($paymentId);
			if (!$payment) {
				return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
			}
			if ($payment->owner_id != $ownerId) {
				return response()->json(['success' => false, 'message' => 'Access denied'], 403);
			}
			if ($payment->payment_status === 'approved') {
				return response()->json(['success' => false, 'message' => 'Cannot edit approved payment validations'], 403);
			}

			$rules = [
				'amount' => 'nullable|numeric',
				'payment_type' => 'nullable|string',
				'transaction_number' => 'nullable|string|max:100',
				'receipt_photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
				'transaction_date' => 'nullable|date'
			];
			$validated = $request->validate($rules);

			$updateData = [];
			if (isset($validated['amount'])) $updateData['amount'] = $validated['amount'];
			if (isset($validated['payment_type'])) $updateData['payment_type'] = $validated['payment_type'];
			if (isset($validated['transaction_number'])) $updateData['transaction_number'] = $validated['transaction_number'];
			if (isset($validated['transaction_date'])) {
				$updateData['transaction_date'] = date('Y-m-d', strtotime($validated['transaction_date']));
			}

			if ($request->hasFile('receipt_photo')) {
				// delete old
				if ($payment->receipt_photo && Storage::disk('public')->exists($payment->receipt_photo)) {
					Storage::disk('public')->delete($payment->receipt_photo);
				}
				$file = $request->file('receipt_photo');
				$filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
				Storage::disk('public')->makeDirectory('payments/receipts');
				$receiptPath = $file->storeAs('payments/receipts', $filename, 'public');
				$updateData['receipt_photo'] = $receiptPath;
			}

			// set updated_at timestamp
			$updateData['updated_at'] = date('Y-m-d H:i:s');

			$this->paymentClass->updatePayment($paymentId, $updateData);

			return response()->json(['success' => true, 'message' => 'Payment updated successfully']);
		} catch (\Exception $e) {
			return response()->json(['success' => false, 'message' => 'Error updating payment: ' . $e->getMessage()], 500);
		}
	}

	public function deletePayment(Request $request, $paymentId)
	{
		try {
			$auth = $this->checkOwnerAccess($request);
			if ($auth) return $auth;

			$user = Session::get('user');

			// Get owner_id from property_owners table
			$owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
			$ownerId = $owner ? $owner->owner_id : null;

			if (!$ownerId) {
				return response()->json(['success' => false, 'message' => 'Owner record not found. Please contact support.'], 403);
			}

			$payment = $this->paymentClass->getPaymentById($paymentId);
			if (!$payment) {
				return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
			}
			if ($payment->owner_id != $ownerId) {
				return response()->json(['success' => false, 'message' => 'Access denied'], 403);
			}
			if ($payment->payment_status === 'approved') {
				return response()->json(['success' => false, 'message' => 'Cannot delete approved payment validations'], 403);
			}

			// Validate deletion reason
			$validated = $request->validate([
				'reason' => 'required|string|max:500'
			]);

			// perform soft-delete (mark as deleted) with reason
			$this->paymentClass->deletePayment($paymentId, $validated['reason']);

			return response()->json(['success' => true, 'message' => 'Payment deleted successfully']);
		} catch (\Exception $e) {
			return response()->json(['success' => false, 'message' => 'Error deleting payment: ' . $e->getMessage()], 500);
		}
	}

	public function getPaymentsByProject(Request $request, $projectId)
	{
		try {
			\Log::info('getPaymentsByProject called', ['project_id' => $projectId, 'bearer_token' => $request->bearerToken() ? 'present' : 'missing']);
			
			// Support both session-based auth (web) and token-based auth (mobile API)
			$user = Session::get('user');
			
			// If no session user, try to authenticate via Sanctum token
			if (!$user) {
				$bearerToken = $request->bearerToken();
				\Log::info('getPaymentsByProject: No session user, checking bearer token', ['token_present' => $bearerToken ? 'yes' : 'no']);
				
				if ($bearerToken) {
					// Find the token in the database
					$token = PersonalAccessToken::findToken($bearerToken);
					if ($token) {
						// Get the user associated with the token
						$user = $token->tokenable;
						\Log::info('getPaymentsByProject: Token found, user authenticated', ['user_id' => $user->user_id ?? null]);
						// Store user in session for downstream code that expects it there
						if ($user && !Session::has('user')) {
							Session::put('user', $user);
						}
					} else {
						\Log::warning('getPaymentsByProject: Token not found in database');
					}
				}
				
				// Fallback to request->user() if available (when middleware is applied)
				if (!$user && $request->user()) {
					$user = $request->user();
					\Log::info('getPaymentsByProject: Using request->user()', ['user_id' => $user->user_id ?? null]);
					if (!Session::has('user')) {
						Session::put('user', $user);
					}
				}
			} else {
				\Log::info('getPaymentsByProject: Using session user', ['user_id' => $user->user_id ?? null]);
			}

			if (!$user) {
				\Log::warning('getPaymentsByProject: No user found, returning 401');
				return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
			}

		// Get payments for the project with related data
		$payments = DB::table('milestone_payments as mp')
			->join('milestone_items as mi', 'mp.item_id', '=', 'mi.item_id')
			->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
			->leftJoin('property_owners as po', 'mp.owner_id', '=', 'po.owner_id')
			->where('mp.project_id', $projectId)
			->whereNotIn('mp.payment_status', ['deleted'])
			->select(
				'mp.payment_id',
				'mp.item_id',
				'mp.amount',
				'mp.payment_type',
				'mp.transaction_number',
				'mp.receipt_photo',
				'mp.transaction_date',
				'mp.payment_status',
				'mp.reason',
				'mp.updated_at',
				'mi.milestone_item_title',
				'mi.milestone_item_cost',
				'mi.milestone_id',
				'mi.sequence_order',
				'mi.percentage_progress',
				'mi.settlement_due_date',
				'mi.extension_date',
				'm.milestone_name',
				DB::raw('CONCAT(po.first_name, " ", po.last_name) as owner_name')
			)
			->orderBy('mp.transaction_date', 'desc')
			->orderBy('mp.payment_id', 'desc')
			->get();

			return response()->json([
				'success' => true,
				'payments' => $payments,
				'total_count' => $payments->count()
			]);
		} catch (\Exception $e) {
			\Log::error('getPaymentsByProject error: ' . $e->getMessage(), [
				'project_id' => $projectId,
				'trace' => $e->getTraceAsString()
			]);
			return response()->json([
				'success' => false,
				'message' => 'Error fetching payments: ' . $e->getMessage(),
				'payments' => [],
				'total_count' => 0
			], 500);
		}
	}

	public function getPaymentsByItem(Request $request, $itemId)
	{
		try {
			\Log::info('getPaymentsByItem called', ['item_id' => $itemId, 'bearer_token' => $request->bearerToken() ? 'present' : 'missing']);
			
			// Support both session-based auth (web) and token-based auth (mobile API)
			$user = Session::get('user');
			
			// If no session user, try to authenticate via Sanctum token
			if (!$user) {
				$bearerToken = $request->bearerToken();
				\Log::info('getPaymentsByItem: No session user, checking bearer token', ['token_present' => $bearerToken ? 'yes' : 'no']);
				
				if ($bearerToken) {
					// Find the token in the database
					$token = PersonalAccessToken::findToken($bearerToken);
					if ($token) {
						// Get the user associated with the token
						$user = $token->tokenable;
						\Log::info('getPaymentsByItem: Token found, user authenticated', ['user_id' => $user->user_id ?? null]);
						// Store user in session for downstream code that expects it there
						if ($user && !Session::has('user')) {
							Session::put('user', $user);
						}
					} else {
						\Log::warning('getPaymentsByItem: Token not found in database');
					}
				}
				
				// Fallback to request->user() if available (when middleware is applied)
				if (!$user && $request->user()) {
					$user = $request->user();
					\Log::info('getPaymentsByItem: Using request->user()', ['user_id' => $user->user_id ?? null]);
					if (!Session::has('user')) {
						Session::put('user', $user);
					}
				}
			} else {
				\Log::info('getPaymentsByItem: Using session user', ['user_id' => $user->user_id ?? null]);
			}

			if (!$user) {
				\Log::warning('getPaymentsByItem: No user found, returning 401');
				return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
			}

			// Get payments for the milestone item
			$payments = DB::table('milestone_payments as mp')
				->join('milestone_items as mi', 'mp.item_id', '=', 'mi.item_id')
				->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
				->leftJoin('property_owners as po', 'mp.owner_id', '=', 'po.owner_id')
				->where('mp.item_id', $itemId)
				->whereNotIn('mp.payment_status', ['deleted'])
				->select(
					'mp.payment_id',
					'mp.item_id',
					'mp.amount',
					'mp.payment_type',
					'mp.transaction_number',
					'mp.receipt_photo',
					'mp.transaction_date',
					'mp.payment_status',
					'mp.reason',
					'mp.updated_at',
					'mi.milestone_item_title',
					'mi.sequence_order',
					'mi.percentage_progress',
					'mi.milestone_item_cost',
					DB::raw('CONCAT(po.first_name, " ", po.last_name) as owner_name')
				)
				->orderBy('mp.transaction_date', 'desc')
				->orderBy('mp.payment_id', 'desc')
				->get();

			// Calculate payment summary using centralized service
			$milestoneService = new \App\Services\milestoneService();
			$summary = $milestoneService->getItemPaymentSummary((int) $itemId);

			return response()->json([
				'success' => true,
				'data' => [
					'payments' => $payments,
					'total_count' => $payments->count(),
					// Original cost before any carry-forward adjustment
					'expected_amount' => $summary['effective_required'] ?? 0,
					'original_cost' => $summary['original_cost'] ?? 0,
					'adjusted_cost' => $summary['adjusted_cost'] ?? null,
					'carry_forward_amount' => $summary['carry_forward_amount'] ?? 0,
					'total_paid' => $summary['total_paid'] ?? 0,
					'total_submitted' => $summary['total_submitted'] ?? 0,
					'remaining_balance' => $summary['remaining_balance'] ?? 0,
					'over_amount' => $summary['over_amount'] ?? 0,
					'derived_payment_status' => $summary['derived_status'] ?? 'Unpaid',
					'settlement_due_date' => $summary['settlement_due_date'] ?? null,
					'extension_date' => $summary['extension_date'] ?? null,
				]
			]);
		} catch (\Exception $e) {
			\Log::error('getPaymentsByItem error: ' . $e->getMessage(), [
				'item_id' => $itemId,
				'trace' => $e->getTraceAsString()
			]);
			return response()->json([
				'success' => false,
				'message' => 'Error fetching payments: ' . $e->getMessage(),
				'data' => [
					'payments' => [],
					'total_count' => 0
				]
			], 500);
		}
	}

	public function getDownpaymentReceipts(Request $request, $projectId)
	{
		try {
			\Log::info('getDownpaymentReceipts called', ['project_id' => $projectId, 'bearer_token' => $request->bearerToken() ? 'present' : 'missing']);
			
			// Support both session-based auth (web) and token-based auth (mobile API)
			$user = Session::get('user');
			
			// If no session user, try to authenticate via Sanctum token
			if (!$user) {
				$bearerToken = $request->bearerToken();
				\Log::info('getDownpaymentReceipts: No session user, checking bearer token', ['token_present' => $bearerToken ? 'yes' : 'no']);
				
				if ($bearerToken) {
					// Find the token in the database
					$token = PersonalAccessToken::findToken($bearerToken);
					if ($token) {
						// Get the user associated with the token
						$user = $token->tokenable;
						\Log::info('getDownpaymentReceipts: Token found, user authenticated', ['user_id' => $user->user_id ?? null]);
						// Store user in session for downstream code that expects it there
						if ($user && !Session::has('user')) {
							Session::put('user', $user);
						}
					} else {
						\Log::warning('getDownpaymentReceipts: Token not found in database');
					}
				}
				
				// Fallback to request->user() if available (when middleware is applied)
				if (!$user && $request->user()) {
					$user = $request->user();
					\Log::info('getDownpaymentReceipts: Using request->user()', ['user_id' => $user->user_id ?? null]);
					if (!Session::has('user')) {
						Session::put('user', $user);
					}
				}
			} else {
				\Log::info('getDownpaymentReceipts: Using session user', ['user_id' => $user->user_id ?? null]);
			}

			if (!$user) {
				\Log::warning('getDownpaymentReceipts: No user found, returning 401');
				return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
			}

			// Get downpayment receipts (item_id = -1 indicates downpayment, not a milestone item)
			$payments = DB::table('milestone_payments as mp')
				->leftJoin('property_owners as po', 'mp.owner_id', '=', 'po.owner_id')
				->where('mp.project_id', $projectId)
				->where('mp.item_id', -1) // Special identifier for downpayment
				->whereNotIn('mp.payment_status', ['deleted'])
				->select(
					'mp.payment_id',
					'mp.item_id',
					'mp.amount',
					'mp.payment_type',
					'mp.transaction_number',
					'mp.receipt_photo',
					'mp.transaction_date',
					'mp.payment_status',
					'mp.reason',
					'mp.updated_at',
					DB::raw('CONCAT(po.first_name, " ", po.last_name) as owner_name')
				)
				->orderBy('mp.transaction_date', 'desc')
				->orderBy('mp.payment_id', 'desc')
				->get();

			return response()->json([
				'success' => true,
				'data' => [
					'payments' => $payments,
					'total_count' => $payments->count()
				]
			]);
		} catch (\Exception $e) {
			\Log::error('getDownpaymentReceipts error: ' . $e->getMessage(), [
				'project_id' => $projectId,
				'trace' => $e->getTraceAsString()
			]);
			return response()->json([
				'success' => false,
				'message' => 'Error fetching downpayment receipts: ' . $e->getMessage(),
				'data' => [
					'payments' => [],
					'total_count' => 0
				]
			], 500);
		}
	}

	// ─────────────────────────────────────────────────────────────────────────
	// STATIC HELPER — Derive dynamic payment status per milestone item
	// ─────────────────────────────────────────────────────────────────────────

	/**
	 * Compute a human-readable payment status for a milestone item.
	 *
	 * Returned values: 'Unpaid' | 'Partially Paid' | 'Fully Paid' | 'Overdue'
	 *
	 * Overdue takes precedence when the effective due date (extension_date ?? settlement_due_date)
	 * has passed and the item is not yet fully paid.
	 *
	 * @param  object|null $milestoneItem  Row from milestone_items (must include settlement_due_date, extension_date)
	 * @param  float       $totalPaid      Sum of approved payment amounts
	 * @param  float       $expectedAmount milestone_item_cost
	 * @return string
	 */
	public static function derivePaymentStatus(?object $milestoneItem, float $totalPaid, float $expectedAmount): string
	{
		if (!$milestoneItem || $expectedAmount <= 0) {
			return 'Unpaid';
		}

		$fullyPaid = $totalPaid >= $expectedAmount;

		if ($fullyPaid) {
			return 'Fully Paid';
		}

		// Determine effective due date: extension_date overrides settlement_due_date
		$effectiveDueDate = $milestoneItem->extension_date ?? $milestoneItem->settlement_due_date ?? null;

		if ($effectiveDueDate && now()->startOfDay()->gt(\Carbon\Carbon::parse($effectiveDueDate)->endOfDay())) {
			return 'Overdue';
		}

		if ($totalPaid > 0) {
			return 'Partially Paid';
		}

		return 'Unpaid';
	}
}
