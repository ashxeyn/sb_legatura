<?php

namespace App\Models\contractor;

use Illuminate\Support\Facades\DB;

class contractorClass
{

	// MILESTONE SETUP FUNCTIONS

	public function getContractorByUserId($userId)
	{
		return DB::table('contractors')
			->where('user_id', $userId)
			->first();
	}

	public function getContractorUserByUserId($userId)
	{
		return DB::table('contractor_users')
			->where('user_id', $userId)
			->first();
	}

	public function projectBelongsToContractor($projectId, $contractorId)
	{
		$project = DB::table('projects')
			->where('project_id', $projectId)
			->where('selected_contractor_id', $contractorId)
			->first();

		if ($project) {
			return true;
		}

		return DB::table('bids')
			->where('project_id', $projectId)
			->where('contractor_id', $contractorId)
			->where('bid_status', 'accepted')
			->exists();
	}

	public function getContractorProjects($contractorId, $excludeMilestoneId = null)
	{
		$query = DB::table('projects as p')
			->select(
				'p.project_id',
				'p.project_title',
				'p.project_description',
				'p.project_status'
			)
			->where('p.selected_contractor_id', $contractorId);

		// If editing a milestone, include projects with that milestone
		// Otherwise, exclude projects that already have milestones
		if ($excludeMilestoneId) {
			$query->where(function ($q) use ($contractorId, $excludeMilestoneId) {
				$q->whereNotExists(
					function ($subQuery) use ($contractorId) {
						$subQuery->select(DB::raw(1))
							->from('milestones')
							->whereColumn('milestones.project_id', 'p.project_id')
							->where('milestones.contractor_id', $contractorId)
							->where(
								function ($mQuery) {
									$mQuery->where('milestones.is_deleted', 0)
										->orWhereNull('milestones.is_deleted');
								}
							);
					}
				)
					->orWhereExists(
						function ($subQuery) use ($excludeMilestoneId) {
							$subQuery->select(DB::raw(1))
								->from('milestones')
								->whereColumn('milestones.project_id', 'p.project_id')
								->where('milestones.milestone_id', $excludeMilestoneId);
						}
					);
			});
		} else {
			$query->whereNotExists(function ($subQuery) use ($contractorId) {
				$subQuery->select(DB::raw(1))
					->from('milestones')
					->whereColumn('milestones.project_id', 'p.project_id')
					->where('milestones.contractor_id', $contractorId)
					->where(
						function ($mQuery) {
							$mQuery->where('milestones.is_deleted', 0)
								->orWhereNull('milestones.is_deleted');
						}
					);
			});
		}

		return $query->orderBy('p.project_title')->get();
	}

	public function contractorHasMilestoneForProject($projectId, $contractorId)
	{
		return DB::table('milestones')
			->where('project_id', $projectId)
			->where('contractor_id', $contractorId)
			->where(function ($query) {
				$query->where('is_deleted', 0)
					->orWhereNull('is_deleted');
			})
			->where(function ($query) {
				// Exclude rejected milestones (contractor can resubmit after rejection)
				$query->whereNull('setup_status')
					->orWhere('setup_status', '!=', 'rejected');
			})
			->exists();
	}



	public function createPaymentPlan($data)
	{
		return DB::table('payment_plans')->insertGetId([
			'project_id' => $data['project_id'],
			'contractor_id' => $data['contractor_id'],
			'payment_mode' => $data['payment_mode'],
			'total_project_cost' => $data['total_project_cost'],
			'downpayment_amount' => $data['downpayment_amount'],
			'is_confirmed' => 0,
			'created_at' => now(),
			'updated_at' => now()
		]);
	}

	public function createMilestone($data)
	{
		$insertData = [
			'project_id' => $data['project_id'],
			'contractor_id' => $data['contractor_id'],
			'plan_id' => $data['plan_id'],
			'milestone_name' => $data['milestone_name'],
			'milestone_description' => $data['milestone_description'],
			'start_date' => $data['start_date'],
			'end_date' => $data['end_date'],
			'created_at' => now(),
			'updated_at' => now()
		];

		// Add setup_status if provided
		if (isset($data['setup_status'])) {
			$insertData['setup_status'] = $data['setup_status'];
		}

		return DB::table('milestones')->insertGetId($insertData);
	}

	public function createMilestoneItem($data)
	{
		return DB::table('milestone_items')->insertGetId([
			'milestone_id' => $data['milestone_id'],
			'sequence_order' => $data['sequence_order'],
			'percentage_progress' => $data['percentage_progress'],
			'milestone_item_title' => $data['milestone_item_title'],
			'milestone_item_description' => $data['milestone_item_description'],
			'milestone_item_cost' => $data['milestone_item_cost'],
			'date_to_finish' => $data['date_to_finish']
		]);
	}

	public function getMilestoneById($milestoneId, $contractorId)
	{
		return DB::table('milestones as m')
			->join('payment_plans as pp', 'm.plan_id', '=', 'pp.plan_id')
			->where('m.milestone_id', $milestoneId)
			->where('m.contractor_id', $contractorId)
			->select(
				'm.milestone_id',
				'm.project_id',
				'm.contractor_id',
				'm.plan_id',
				'm.milestone_name',
				'm.milestone_description',
				'm.milestone_status',
				'm.start_date',
				'm.end_date',
				'pp.payment_mode',
				'pp.total_project_cost',
				'pp.downpayment_amount'
			)
			->first();
	}

	public function getMilestoneItems($milestoneId)
	{
		return DB::table('milestone_items')
			->where('milestone_id', $milestoneId)
			->orderBy('sequence_order')
			->get();
	}

	public function updateMilestone($milestoneId, $data)
	{
		return DB::table('milestones')
			->where('milestone_id', $milestoneId)
			->update($data);
	}

	public function deleteMilestoneItems($milestoneId)
	{
		return DB::table('milestone_items')
			->where('milestone_id', $milestoneId)
			->delete();
	}

	public function updatePaymentPlan($planId, $data)
	{
		return DB::table('payment_plans')
			->where('plan_id', $planId)
			->update($data);
	}

	// ─── MILESTONE REPORT METHODS ───────────────────────────────────────

	/**
	 * Get a project and verify the contractor has access via an accepted bid.
	 */
	public function getProjectForContractor($projectId, $contractorId)
	{
		return DB::table('projects as p')
			->join('bids as b', function ($join) use ($contractorId) {
				$join->on('p.project_id', '=', 'b.project_id')
					->where('b.contractor_id', '=', $contractorId)
					->where('b.bid_status', '=', 'accepted');
			})
			->where('p.project_id', $projectId)
			->select('p.*')
			->first();
	}

	/**
	 * Get milestone plans for a project along with their items, progress reports, and payments.
	 */
	public function getProjectMilestonesWithItems($projectId, $contractorId)
	{
		$milestones = DB::table('milestones as m')
			->join('payment_plans as pp', 'm.plan_id', '=', 'pp.plan_id')
			->where('m.project_id', $projectId)
			->where('m.contractor_id', $contractorId)
			->where(function ($query) {
				$query->whereNull('m.is_deleted')
					->orWhere('m.is_deleted', 0);
			})
			->select(
				'm.*',
				'pp.payment_mode',
				'pp.total_project_cost',
				'pp.downpayment_amount'
			)
			->get();

		$result = [];
		foreach ($milestones as $milestone) {
			$items = DB::table('milestone_items')
				->where('milestone_id', $milestone->milestone_id)
				->orderBy('sequence_order', 'asc')
				->get();

			$itemsArr = [];
			foreach ($items as $item) {
				// Progress reports for this item (using unified class)
				$progressClass = new \App\Models\contractor\progressUploadClass();
				$item->progress_reports = $progressClass->getProgressFilesByItem($item->item_id);

				// Disputes for this item
				$disputeClass = new \App\Models\both\disputeClass();
				$item->disputes = DB::table('disputes')
					->where('milestone_item_id', $item->item_id)
					->orderBy('created_at', 'desc')
					->get();

				// Attach files to each dispute
				foreach ($item->disputes as $dispute) {
					$dispute->files = DB::table('dispute_files')
						->where('dispute_id', $dispute->dispute_id)
						->get();
				}

				// Calculate dispute summary counts for this item
				$item->dispute_summary = [
					'total' => count($item->disputes),
					'open' => collect($item->disputes)->filter(fn($d) => in_array($d->dispute_status, ['open', 'under_review']))->count(),
					'resolved' => collect($item->disputes)->filter(fn($d) => in_array($d->dispute_status, ['resolved', 'closed']))->count(),
				];

				// Payments for this item
				$item->payments = DB::table('milestone_payments')
					->where('item_id', $item->item_id)
					->whereNotIn('payment_status', ['deleted'])
					->orderBy('transaction_date', 'desc')
					->get();

				$itemsArr[] = $item;
			}

			$milestone->items = $itemsArr;
			$result[] = $milestone;
		}

		return $result;
	}

	/**
	 * Get all payments for a project (for the payment history modal).
	 */
	public function getProjectPayments($projectId)
	{
		return DB::table('milestone_payments as mp')
			->leftJoin('milestone_items as mi', 'mp.item_id', '=', 'mi.item_id')
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
				'mi.milestone_item_title',
				'mi.sequence_order'
			)
			->orderBy('mp.transaction_date', 'desc')
			->orderBy('mp.payment_id', 'desc')
			->get();
	}

}
