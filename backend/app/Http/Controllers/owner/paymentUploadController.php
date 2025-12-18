<?php

namespace App\Http\Controllers\owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\owner\paymentUploadRequest;
use App\Models\owner\paymentUploadClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class paymentUploadController extends Controller
{
	protected $paymentClass;

	public function __construct()
	{
		$this->paymentClass = new paymentUploadClass();
	}

	private function checkOwnerAccess(Request $request)
	{
		// Support both session-based auth (web) and token-based auth (mobile API)
		$user = Session::get('user');
		if (!$user && $request->user()) {
			// Mobile app using Sanctum Bearer token
			$user = $request->user();
		}
		
		if (!$user) {
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
			$auth = $this->checkOwnerAccess($request);
			if ($auth) return $auth;

			$user = Session::get('user');
			$validated = $request->validated();

			$receiptPath = null;
			if ($request->hasFile('receipt_photo')) {
				$file = $request->file('receipt_photo');
				$filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
				Storage::disk('public')->makeDirectory('payments/receipts');
				$receiptPath = $file->storeAs('payments/receipts', $filename, 'public');
			}

		// Get contractor user id from project
		// Get primary contractor user (owner role preferred, or first active user)
		$project = DB::table('projects as p')
			->leftJoin('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
			->leftJoin('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
			->leftJoin('contractor_users as cu', function($join) {
				$join->on('c.contractor_id', '=', 'cu.contractor_id')
					->where('cu.is_active', '=', 1)
					->where('cu.is_deleted', '=', 0);
			})
			->where('p.project_id', $validated['project_id'])
			->select(
				'p.project_id', 
				'pr.owner_id', 
				DB::raw('COALESCE(
					MAX(CASE WHEN cu.role = "owner" THEN cu.contractor_user_id END),
					MIN(cu.contractor_user_id)
				) as contractor_user_id')
			)
			->groupBy('p.project_id', 'pr.owner_id')
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

		// Prevent multiple active submissions for the same milestone item.
		// Only allow a new upload if existing payments for this item are all 'rejected' or 'deleted'.
		$existingCount = DB::table('milestone_payments')
			->where('item_id', $validated['item_id'])
			->where('owner_id', $ownerId)
			->whereNotIn('payment_status', ['rejected', 'deleted'])
			->count();

			if ($existingCount > 0) {
				return response()->json(['success' => false, 'message' => 'You already have a payment validation submitted for this milestone. Only payments with status rejected or deleted can be re-submitted.'], 403);
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

			return response()->json(['success' => true, 'message' => 'Payment validation uploaded', 'payment_id' => $paymentId], 201);
		} catch (\Exception $e) {
			return response()->json(['success' => false, 'message' => 'Error uploading payment: ' . $e->getMessage()], 500);
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
			// Support both session-based auth (web) and token-based auth (mobile API)
			$user = Session::get('user');
			if (!$user && $request->user()) {
				$user = $request->user();
			}

			if (!$user) {
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
				'mi.sequence_order',
				'mi.percentage_progress',
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
			return response()->json(['success' => false, 'message' => 'Error fetching payments: ' . $e->getMessage()], 500);
		}
	}

	public function getPaymentsByItem(Request $request, $itemId)
	{
		try {
			// Support both session-based auth (web) and token-based auth (mobile API)
			$user = Session::get('user');
			if (!$user && $request->user()) {
				$user = $request->user();
			}

			if (!$user) {
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
			return response()->json(['success' => false, 'message' => 'Error fetching payments: ' . $e->getMessage()], 500);
		}
	}
}

