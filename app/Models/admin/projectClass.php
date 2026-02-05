<?php
namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class projectClass
{
    public static function getAllProjects($search = null, $dateFrom = null, $dateTo = null, $verification = null, $progress = null, $perPage = 20)
    {
        $query = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users as owner_users', 'property_owners.user_id', '=', 'owner_users.user_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'projects.*',
                'project_relationships.created_at as submitted_at',
                'project_relationships.updated_at as relationship_updated_at',
                'project_relationships.project_post_status',
                'property_owners.first_name as owner_first_name',
                'property_owners.last_name as owner_last_name',
                'property_owners.middle_name as owner_middle_name',
                'owner_users.profile_pic as owner_profile_pic',
                'contractors.company_name as contractor_company'
            );

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('projects.project_title', 'LIKE', "%{$search}%")
                  ->orWhere('property_owners.first_name', 'LIKE', "%{$search}%")
                  ->orWhere('property_owners.last_name', 'LIKE', "%{$search}%")
                  ->orWhere('contractors.company_name', 'LIKE', "%{$search}%")
                  ->orWhere('projects.project_id', 'LIKE', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->where('project_relationships.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('project_relationships.created_at', '<=', $dateTo . ' 23:59:59');
        }

        if ($verification) {
            $query->where('project_relationships.project_post_status', $verification);
        }

        if ($progress) {
            $query->where('projects.project_status', $progress);
        }

        return $query->orderBy('project_relationships.created_at', 'desc')->paginate($perPage);
    }

    public static function analytics()
    {
        return DB::table('projects')
            ->select('project_status', DB::raw('count(*) as count'))
            ->groupBy('project_status')
            ->get()
            ->toArray();
    }

    public static function successRate()
    {
        return DB::table('projects')
            ->select('property_type', DB::raw('count(*) as count'))
            ->where('project_status', 'completed')
            ->groupBy('property_type')
            ->get()
            ->toArray();
    }

    public static function timeline($months = 12)
    {
        $months = max(1, (int) $months);
        $result = [
            'dateRange' => now()->subMonths($months - 1)->format('M Y') . ' - ' . now()->format('M Y'),
            'months' => [],
            'newProjects' => [],
            'completedProjects' => []
        ];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $result['months'][] = $date->format('M');

            if (Schema::hasColumn('projects', 'created_at')) {
                $newCount = DB::table('projects')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            } else {
                $newCount = DB::table('project_relationships')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            }
            $result['newProjects'][] = $newCount;

            if (Schema::hasColumn('projects', 'updated_at')) {
                $completedCount = DB::table('projects')
                    ->whereYear('updated_at', $date->year)
                    ->whereMonth('updated_at', $date->month)
                    ->where('project_status', 'completed')
                    ->count();
            } else {
                $completedCount = DB::table('projects')
                    ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->where('project_status', 'completed')
                    ->whereYear('project_relationships.created_at', $date->year)
                    ->whereMonth('project_relationships.created_at', $date->month)
                    ->count();
            }
            $result['completedProjects'][] = $completedCount;
        }

        return $result;
    }

    public function fetchProjectFullDetails($id)
    {
        // Main project query with all joins
        $project = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->leftJoin('contractor_users', function($join) {
                $join->on('contractors.contractor_id', '=', 'contractor_users.contractor_id')
                     ->on('contractors.user_id', '=', 'contractor_users.user_id');
            })
            ->select(
                'projects.*',
                'project_relationships.created_at as submitted_at',
                'project_relationships.project_post_status',
                'project_relationships.bidding_due',
                'property_owners.first_name as owner_first_name',
                'property_owners.last_name as owner_last_name',
                'property_owners.phone_number as owner_phone',
                'property_owners.address as owner_address',
                'users.email as owner_email',
                'users.profile_pic as owner_profile_pic',
                'contractors.company_name as contractor_name',
                'contractors.company_email as contractor_email',
                'contractors.picab_number as contractor_pcab',
                'contractors.picab_category as contractor_category',
                'contractors.picab_expiration_date as contractor_pcab_expiry',
                'contractors.business_permit_number as contractor_permit',
                'contractors.business_permit_city as contractor_city',
                'contractors.business_permit_expiration as contractor_permit_expiry',
                'contractors.tin_business_reg_number as contractor_tin',
                'contractor_users.authorized_rep_fname as contractor_rep_fname',
                'contractor_users.authorized_rep_lname as contractor_rep_lname',
                'contractor_users.phone_number as contractor_phone'
            )
            ->where('projects.project_id', $id)
            ->first();

        if (!$project) {
            return null;
        }

        // Format owner name
        $ownerName = trim(($project->owner_first_name ?? '') . ' ' . ($project->owner_last_name ?? ''));
        if (empty($ownerName)) {
            $ownerName = 'Unknown Owner';
        }

        // Get milestones with items
        $milestones = DB::table('milestones')
            ->leftJoin('milestone_items', 'milestones.milestone_id', '=', 'milestone_items.milestone_id')
            ->select(
                'milestones.milestone_id',
                'milestones.milestone_name',
                'milestones.milestone_description',
                'milestones.milestone_status',
                'milestones.start_date',
                'milestones.end_date',
                'milestones.setup_status',
                DB::raw('COUNT(milestone_items.item_id) as item_count')
            )
            ->where('milestones.project_id', $id)
            ->groupBy(
                'milestones.milestone_id',
                'milestones.milestone_name',
                'milestones.milestone_description',
                'milestones.milestone_status',
                'milestones.start_date',
                'milestones.end_date',
                'milestones.setup_status'
            )
            ->orderBy('milestones.milestone_id', 'asc')
            ->get();

        // Get payment details
        $payments = DB::table('milestone_payments')
            ->leftJoin('milestones', 'milestone_payments.item_id', '=', 'milestones.milestone_id')
            ->leftJoin('users', 'milestone_payments.contractor_user_id', '=', 'users.user_id')
            ->select(
                'milestone_payments.payment_id',
                'milestone_payments.amount',
                'milestone_payments.payment_type',
                'milestone_payments.transaction_number',
                'milestone_payments.receipt_photo',
                'milestone_payments.transaction_date',
                'milestone_payments.payment_status',
                'milestones.milestone_name',
                'milestones.start_date',
                'milestones.end_date',
                'users.username as uploader_name'
            )
            ->where('milestone_payments.project_id', $id)
            ->orderBy('milestone_payments.transaction_date', 'desc')
            ->get();

        // Get bids if project is open or bidding_closed status
        $bids = null;
        if (in_array($project->project_status, ['open', 'bidding_closed'])) {
            $bids = DB::table('bids')
                ->leftJoin('contractors', 'bids.contractor_id', '=', 'contractors.contractor_id')
                ->leftJoin('users', 'contractors.user_id', '=', 'users.user_id')
                ->select(
                    'bids.*',
                    'contractors.company_name',
                    'contractors.picab_number',
                    'contractors.picab_category',
                    'users.profile_pic as contractor_profile_pic'
                )
                ->where('bids.project_id', $id)
                ->orderBy('bids.submitted_at', 'desc')
                ->get();
        }

        // Get termination data if project is terminated or halted
        $terminationData = null;
        if (in_array($project->project_status, ['terminated', 'halt', 'cancelled'])) {
            $terminationData = DB::table('contract_terminations')
                ->select(
                    'reason as termination_reason',
                    'terminated_at'
                )
                ->where('project_id', $id)
                ->orderBy('terminated_at', 'desc')
                ->first();
        }

        // Prepare structured response
        $data = [
            'projectId' => $project->project_id,
            'title' => $project->project_title,
            'description' => $project->project_description,
            'projectStatus' => $project->project_status,
            'submittedAt' => $project->submitted_at,
            'projectPostStatus' => $project->project_post_status,
            'biddingDue' => $project->bidding_due,

            // Owner details
            'ownerName' => $ownerName,
            'ownerEmail' => $project->owner_email,
            'ownerPhone' => $project->owner_phone,
            'ownerAddress' => $project->owner_address,
            'ownerProfilePic' => $project->owner_profile_pic,

            // Contractor details
            'contractorName' => $project->contractor_name ?? 'No contractor assigned',
            'contractorEmail' => $project->contractor_email,
            'contractorPhone' => $project->contractor_phone,
            'contractorRepName' => trim(($project->contractor_rep_fname ?? '') . ' ' . ($project->contractor_rep_lname ?? '')),
            'contractorPcab' => $project->contractor_pcab,
            'contractorCategory' => $project->contractor_category,
            'contractorPcabExpiry' => $project->contractor_pcab_expiry,
            'contractorPermit' => $project->contractor_permit,
            'contractorCity' => $project->contractor_city,
            'contractorPermitExpiry' => $project->contractor_permit_expiry,
            'contractorTin' => $project->contractor_tin,

            // Property details
            'propertyAddress' => $project->project_location,
            'propertyType' => $project->property_type,
            'lotSize' => $project->lot_size,
            'floorArea' => $project->floor_area,

            // Project requirements
            'budgetMin' => $project->budget_range_min,
            'budgetMax' => $project->budget_range_max,
            'toFinish' => $project->to_finish,
            'timelineDisplay' => $project->to_finish ? $project->to_finish . ' months' : null,

            // Milestones and payments
            'milestones' => $milestones,
            'payments' => $payments,
            'bids' => $bids,
            'terminationData' => $terminationData
        ];

        return $data;
    }

    public function fetchCompletedProjectDetails($id)
    {
        // Main project query with joins
        $project = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'projects.*',
                'project_relationships.bidding_due',
                'property_owners.first_name as owner_first_name',
                'property_owners.last_name as owner_last_name',
                'users.email as owner_email',
                'users.profile_pic as owner_profile_pic',
                'contractors.company_name as contractor_name',
                'contractors.company_email as contractor_email',
                'contractors.picab_number as contractor_pcab',
                'contractors.picab_category as contractor_category',
                'contractors.picab_expiration_date as contractor_pcab_expiry',
                'contractors.business_permit_number as contractor_permit',
                'contractors.business_permit_city as contractor_city',
                'contractors.business_permit_expiration as contractor_permit_expiry',
                'contractors.tin_business_reg_number as contractor_tin'
            )
            ->where('projects.project_id', $id)
            ->first();

        if (!$project) {
            return null;
        }

        // Format owner name
        $project->owner_name = trim(($project->owner_first_name ?? '') . ' ' . ($project->owner_last_name ?? ''));
        if (empty($project->owner_name)) {
            $project->owner_name = 'Unknown Owner';
        }

        // Calculate target timeline from milestones
        $timelineData = DB::table('milestones')
            ->select(
                DB::raw('MIN(start_date) as timeline_start'),
                DB::raw('MAX(end_date) as timeline_end')
            )
            ->where('project_id', $id)
            ->first();

        $project->timeline_start = $timelineData->timeline_start ?? null;
        $project->timeline_end = $timelineData->timeline_end ?? null;

        // Get milestone items with progress files
        $milestoneItems = DB::table('milestone_items')
            ->leftJoin('milestones', 'milestone_items.milestone_id', '=', 'milestones.milestone_id')
            ->leftJoin('progress', 'milestone_items.item_id', '=', 'progress.milestone_item_id')
            ->leftJoin('progress_files', 'progress.progress_id', '=', 'progress_files.progress_id')
            ->select(
                'milestone_items.item_id',
                'milestone_items.milestone_id',
                'milestone_items.milestone_item_title',
                'milestone_items.milestone_item_description',
                'milestone_items.sequence_order',
                'milestone_items.date_to_finish',
                'milestone_items.percentage_progress',
                'milestone_items.item_status',
                'milestones.milestone_name',
                'milestones.milestone_status',
                'progress.progress_id',
                'progress.purpose',
                'progress.progress_status',
                'progress.submitted_at',
                'progress_files.file_id',
                'progress_files.file_path',
                'progress_files.original_name'
            )
            ->where('milestones.project_id', $id)
            ->orderBy('milestone_items.sequence_order', 'asc')
            ->get();

        // Group milestone items by item_id with their progress files
        $groupedItems = [];
        foreach ($milestoneItems as $item) {
            $itemId = $item->item_id;
            if (!isset($groupedItems[$itemId])) {
                $groupedItems[$itemId] = [
                    'item_id' => $item->item_id,
                    'milestone_id' => $item->milestone_id,
                    'milestone_name' => $item->milestone_name,
                    'milestone_status' => $item->milestone_status,
                    'item_name' => $item->milestone_item_title,
                    'item_description' => $item->milestone_item_description,
                    'sequence_order' => $item->sequence_order,
                    'date_to_finish' => $item->date_to_finish,
                    'percentage_progress' => $item->percentage_progress ?? 0,
                    'item_status' => $item->item_status,
                    'progress' => []
                ];
            }

            if ($item->progress_id) {
                $progressKey = $item->progress_id;
                if (!isset($groupedItems[$itemId]['progress'][$progressKey])) {
                    $groupedItems[$itemId]['progress'][$progressKey] = [
                        'progress_id' => $item->progress_id,
                        'purpose' => $item->purpose,
                        'status' => $item->progress_status,
                        'submitted_at' => $item->submitted_at,
                        'files' => []
                    ];
                }

                // Add files to this specific progress report
                if ($item->file_id) {
                    $fileExists = false;
                    foreach ($groupedItems[$itemId]['progress'][$progressKey]['files'] as $existingFile) {
                        if ($existingFile['file_id'] == $item->file_id) {
                            $fileExists = true;
                            break;
                        }
                    }
                    if (!$fileExists) {
                        $groupedItems[$itemId]['progress'][$progressKey]['files'][] = [
                            'file_id' => $item->file_id,
                            'file_path' => $item->file_path,
                            'original_name' => $item->original_name
                        ];
                    }
                }
            }
        }

        $project->milestone_items = array_values($groupedItems);

        // Payment summary calculations
        $paymentSummary = DB::table('milestone_payments')
            ->select(
                DB::raw('COUNT(DISTINCT item_id) as total_milestones_paid'),
                DB::raw('SUM(amount) as total_amount_paid'),
                DB::raw('MAX(transaction_date) as last_payment_date')
            )
            ->where('project_id', $id)
            ->where('payment_status', 'approved')
            ->first();

        $totalMilestoneItems = count($groupedItems);
        $project->total_milestone_items = $totalMilestoneItems;
        $project->total_milestones_paid = $paymentSummary->total_milestones_paid ?? 0;
        $project->total_amount_paid = $paymentSummary->total_amount_paid ?? 0;
        $project->last_payment_date = $paymentSummary->last_payment_date;
        $project->overall_payment_status = ($project->total_milestones_paid >= $totalMilestoneItems && $totalMilestoneItems > 0)
            ? 'Fully Paid'
            : 'Not Fully Paid';

        // Get payment records for table
        $payments = DB::table('milestone_payments')
            ->leftJoin('milestone_items', 'milestone_payments.item_id', '=', 'milestone_items.item_id')
            ->select(
                'milestone_payments.*',
                'milestone_items.sequence_order',
                'milestone_items.date_to_finish'
            )
            ->where('milestone_payments.project_id', $id)
            ->orderBy('milestone_items.sequence_order', 'asc')
            ->get();

        $project->payments = $payments;

        return $project;
    }

    /**
     * Get completion details for the completion details modal
     * Returns project status, completion date, duration, and reviews
     */
    public function getCompletionDetails($id)
    {
        // Get basic project info
        $project = DB::table('projects')
            ->select('project_status')
            ->where('project_id', $id)
            ->first();

        if (!$project) {
            return null;
        }

        // Get completion date (last approved payment date)
        $completionDate = DB::table('milestone_payments')
            ->where('project_id', $id)
            ->where('payment_status', 'approved')
            ->max('transaction_date');

        // Get project duration from milestones
        $timelineData = DB::table('milestones')
            ->select(
                DB::raw('MIN(start_date) as start_date'),
                DB::raw('MAX(end_date) as end_date')
            )
            ->where('project_id', $id)
            ->first();

        // Calculate duration in days
        $duration = null;
        $startDate = null;
        $durationText = '—';

        if ($timelineData && $timelineData->start_date && $timelineData->end_date) {
            $start = \Carbon\Carbon::parse($timelineData->start_date);
            $end = \Carbon\Carbon::parse($timelineData->end_date);
            $duration = (int) $start->diffInDays($end);
            $startDate = $start->format('F j, Y');
            $durationText = "{$duration} days (Started: {$startDate})";
        }

        // Get reviews with user information
        $reviews = DB::table('reviews')
            ->join('users', 'reviews.reviewer_user_id', '=', 'users.user_id')
            ->leftJoin('property_owners', 'users.user_id', '=', 'property_owners.user_id')
            ->leftJoin('contractors', 'users.user_id', '=', 'contractors.user_id')
            ->select(
                'reviews.review_id',
                'reviews.rating',
                'reviews.comment',
                'reviews.created_at as review_date',
                'users.user_id',
                'users.user_type',
                'property_owners.first_name as owner_first_name',
                'property_owners.last_name as owner_last_name',
                'contractors.company_name as contractor_name'
            )
            ->where('reviews.project_id', $id)
            ->get();

        // Format reviews with role-specific names
        $formattedReviews = $reviews->map(function($review) {
            $name = '';
            $roleLabel = '';

            if ($review->user_type === 'property_owner') {
                $name = trim(($review->owner_first_name ?? '') . ' ' . ($review->owner_last_name ?? ''));
                $roleLabel = 'Property Owner';
            } elseif ($review->user_type === 'contractor') {
                $name = $review->contractor_name ?? 'Unknown Contractor';
                $roleLabel = 'Contractor';
            } else {
                $name = 'Unknown User';
                $roleLabel = ucwords(str_replace('_', ' ', $review->user_type ?? 'User'));
            }

            return [
                'review_id' => $review->review_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'review_date' => $review->review_date,
                'name' => $name ?: 'Unknown',
                'role' => $review->user_type,
                'role_label' => $roleLabel
            ];
        });

        return [
            'project_status' => $project->project_status,
            'completion_date' => $completionDate ? \Carbon\Carbon::parse($completionDate)->format('F j, Y') : '—',
            'duration' => $duration,
            'duration_text' => $durationText,
            'start_date' => $startDate,
            'reviews' => $formattedReviews
        ];
    }

    /**
     * Get ongoing project details for the ongoing project modal
     * Returns all project data including bidding summary and milestones
     */
    public function fetchOngoingProjectDetails($id)
    {
        // Main project query with all joins
        $project = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users as owner_users', 'property_owners.user_id', '=', 'owner_users.user_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'projects.*',
                'project_relationships.bidding_due',
                'project_relationships.created_at as submitted_at',
                'property_owners.first_name as owner_first_name',
                'property_owners.last_name as owner_last_name',
                'owner_users.email as owner_email',
                'owner_users.profile_pic as owner_profile_pic',
                'contractors.company_name as contractor_name',
                'contractors.company_email as contractor_email',
                'contractors.picab_number as contractor_pcab',
                'contractors.picab_category as contractor_category',
                'contractors.picab_expiration_date as contractor_pcab_expiry',
                'contractors.business_permit_number as contractor_permit',
                'contractors.business_permit_city as contractor_city',
                'contractors.business_permit_expiration as contractor_permit_expiry',
                'contractors.tin_business_reg_number as contractor_tin'
            )
            ->where('projects.project_id', $id)
            ->first();

        if (!$project) {
            return null;
        }

        // Format owner name
        $project->owner_name = trim(($project->owner_first_name ?? '') . ' ' . ($project->owner_last_name ?? ''));
        if (empty($project->owner_name)) {
            $project->owner_name = 'Unknown Owner';
        }

        // Fetch timeline from milestones table (MIN start_date and MAX end_date)
        $timeline = DB::table('milestones')
            ->select(
                DB::raw('MIN(start_date) as min_start_date'),
                DB::raw('MAX(end_date) as max_end_date')
            )
            ->where('project_id', $id)
            ->first();

        // Calculate timeline in months if dates exist
        if ($timeline && $timeline->min_start_date && $timeline->max_end_date) {
            $start = \Carbon\Carbon::parse($timeline->min_start_date);
            $end = \Carbon\Carbon::parse($timeline->max_end_date);
            $project->to_finish = (int) $start->diffInMonths($end);
            $project->timeline_start = $timeline->min_start_date;
            $project->timeline_end = $timeline->max_end_date;
        } else {
            $project->timeline_start = null;
            $project->timeline_end = null;
        }

        // Get bidding summary data
        $biddingSummary = DB::table('bids')
            ->select(
                DB::raw('COUNT(bid_id) as total_bids')
            )
            ->where('project_id', $id)
            ->first();

        $project->total_bids = $biddingSummary->total_bids ?? 0;

        // Get accepted bid details (winning bidder, decision date, proposed cost)
        $acceptedBid = DB::table('bids')
            ->leftJoin('contractors', 'bids.contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'bids.proposed_cost',
                'bids.decision_date',
                'contractors.company_name as winning_bidder'
            )
            ->where('bids.project_id', $id)
            ->where('bids.bid_status', 'accepted')
            ->first();

        $project->proposed_cost = $acceptedBid->proposed_cost ?? null;
        $project->decision_date = $acceptedBid->decision_date ?? null;
        $project->winning_bidder = $acceptedBid->winning_bidder ?? null;

        // Get milestone items with progress reports
        $milestoneItems = DB::table('milestone_items')
            ->leftJoin('milestones', 'milestone_items.milestone_id', '=', 'milestones.milestone_id')
            ->leftJoin('progress', 'milestone_items.item_id', '=', 'progress.milestone_item_id')
            ->leftJoin('progress_files', 'progress.progress_id', '=', 'progress_files.progress_id')
            ->select(
                'milestone_items.item_id',
                'milestone_items.milestone_id',
                'milestone_items.milestone_item_title as item_title',
                'milestone_items.milestone_item_description as item_description',
                'milestone_items.percentage_progress',
                'milestone_items.item_status',
                'milestone_items.date_to_finish',
                'milestone_items.sequence_order',
                'milestone_items.milestone_item_cost as amount',
                'milestones.milestone_name',
                'progress.progress_id',
                'progress.purpose',
                'progress.progress_status',
                'progress.submitted_at',
                'progress_files.file_id',
                'progress_files.file_path',
                'progress_files.original_name'
            )
            ->where('milestones.project_id', $id)
            ->orderBy('milestone_items.sequence_order', 'asc')
            ->get();

        // Group milestone items by item_id with their progress and files
        $groupedItems = [];
        foreach ($milestoneItems as $item) {
            $itemId = $item->item_id;
            if (!isset($groupedItems[$itemId])) {
                $groupedItems[$itemId] = (object) [
                    'item_id' => $item->item_id,
                    'milestone_id' => $item->milestone_id,
                    'milestone_name' => $item->milestone_name,
                    'item_title' => $item->item_title,
                    'item_description' => $item->item_description,
                    'sequence_order' => $item->sequence_order,
                    'date_to_finish' => $item->date_to_finish,
                    'percentage_progress' => $item->percentage_progress ?? 0,
                    'item_status' => $item->item_status,
                    'amount' => $item->amount,
                    'progress' => []
                ];
            }

            if ($item->progress_id) {
                $progressKey = $item->progress_id;
                if (!isset($groupedItems[$itemId]->progress[$progressKey])) {
                    $groupedItems[$itemId]->progress[$progressKey] = [
                        'progress_id' => $item->progress_id,
                        'purpose' => $item->purpose,
                        'status' => $item->progress_status,
                        'submitted_at' => $item->submitted_at,
                        'files' => []
                    ];
                }

                // Add files to this specific progress report
                if ($item->file_id) {
                    $fileExists = false;
                    foreach ($groupedItems[$itemId]->progress[$progressKey]['files'] as $existingFile) {
                        if ($existingFile['file_id'] == $item->file_id) {
                            $fileExists = true;
                            break;
                        }
                    }
                    if (!$fileExists) {
                        $groupedItems[$itemId]->progress[$progressKey]['files'][] = [
                            'file_id' => $item->file_id,
                            'file_path' => $item->file_path,
                            'original_name' => $item->original_name
                        ];
                    }
                }
            }
        }

        $project->milestone_items = collect(array_values($groupedItems));

        // Payment summary calculations
        $paymentSummary = DB::table('milestone_payments')
            ->select(
                DB::raw('COUNT(DISTINCT item_id) as approved_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('MAX(transaction_date) as last_payment_date')
            )
            ->where('project_id', $id)
            ->where('payment_status', 'approved')
            ->first();

        $totalMilestoneItems = count($groupedItems);
        $project->total_milestone_items = $totalMilestoneItems;
        $project->approved_payments_count = $paymentSummary->approved_count ?? 0;
        $project->total_amount_paid = $paymentSummary->total_amount ?? 0;
        $project->last_payment_date = $paymentSummary->last_payment_date;

        // Get all payments for the table
        $payments = DB::table('milestone_payments')
            ->leftJoin('milestone_items', 'milestone_payments.item_id', '=', 'milestone_items.item_id')
            ->select(
                'milestone_payments.payment_id',
                'milestone_payments.amount',
                'milestone_payments.transaction_date',
                'milestone_payments.payment_status',
                'milestone_payments.receipt_photo as proof_attachment',
                'milestone_items.milestone_item_title as item_title',
                'milestone_items.sequence_order',
                DB::raw("'N/A' as milestone_period"),
                DB::raw("'Owner' as uploaded_by")
            )
            ->where('milestone_payments.project_id', $id)
            ->orderBy('milestone_items.sequence_order', 'asc')
            ->get();

        $project->payments = $payments;

        return $project;
    }

    public function fetchOpenProjectDetails($id)
    {
        // Main project query with all joins
        $project = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users as owner_users', 'property_owners.user_id', '=', 'owner_users.user_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'projects.*',
                'project_relationships.bidding_due',
                'project_relationships.created_at as relationship_created_at',
                'project_relationships.project_post_status',
                'property_owners.owner_id',
                'property_owners.first_name as owner_first_name',
                'property_owners.middle_name as owner_middle_name',
                'property_owners.last_name as owner_last_name',
                'owner_users.email as owner_email',
                'owner_users.profile_pic as owner_profile_pic',
                'contractors.company_name as winning_bidder_name'
            )
            ->where('projects.project_id', $id)
            ->first();

        if (!$project) {
            return null;
        }

        // Format owner name with middle name
        $ownerNameParts = array_filter([
            $project->owner_first_name,
            $project->owner_middle_name,
            $project->owner_last_name
        ]);
        $project->owner_name = !empty($ownerNameParts) ? implode(' ', $ownerNameParts) : 'Unknown Owner';

        // Submitted Date: Use created_at from project_relationships where project_post_status is approved
        $project->submitted_at = ($project->project_post_status === 'approved')
            ? $project->relationship_created_at
            : null;

        // Bidding Summary Logic
        $project->bid_start_date = $project->relationship_created_at; // Start Date
        $project->bid_end_date = $project->bidding_due; // End Date

        // Status Logic: If project_status == 'open', status is "In Bidding". Otherwise, it is "Closed"
        $project->bidding_status = ($project->project_status === 'open') ? 'In Bidding' : 'Closed';

        // Winning Bidder: If status is open, return "—". If bidding_closed, use company_name from contractors
        $project->winning_bidder = ($project->project_status === 'open')
            ? '—'
            : ($project->winning_bidder_name ?? '—');

        // Get bidding summary data
        $biddingSummary = DB::table('bids')
            ->select(
                DB::raw('COUNT(bid_id) as total_bids'),
                DB::raw('MIN(proposed_cost) as lowest_bid'),
                DB::raw('MAX(proposed_cost) as highest_bid'),
                DB::raw('AVG(proposed_cost) as average_bid')
            )
            ->where('project_id', $id)
            ->first();

        $project->total_bids = $biddingSummary->total_bids ?? 0;
        $project->lowest_bid = $biddingSummary->lowest_bid ?? 0;
        $project->highest_bid = $biddingSummary->highest_bid ?? 0;
        $project->average_bid = $biddingSummary->average_bid ?? 0;

        // Get project files
        $files = DB::table('project_files')
            ->select(
                'file_id',
                'file_type',
                'file_path',
                'uploaded_at'
            )
            ->where('project_id', $id)
            ->orderBy('uploaded_at', 'desc')
            ->get();

        $project->files = $files;

        // Get all bids with contractor details
        $bids = DB::table('bids')
            ->leftJoin('contractors', 'bids.contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'bids.bid_id',
                'bids.proposed_cost',
                'bids.estimated_timeline',
                'bids.submitted_at',
                'bids.bid_status',
                'contractors.company_name as contractor_name',
                'contractors.picab_category as contractor_category',
                'contractors.picab_number as contractor_pcab'
            )
            ->where('bids.project_id', $id)
            ->orderBy('bids.submitted_at', 'desc')
            ->get();

        $project->bids = $bids;

        return $project;
    }

    public function fetchSpecificBidDetails($bidId)
    {
        // Main bid query with all joins
        $bid = DB::table('bids')
            ->leftJoin('contractors', 'bids.contractor_id', '=', 'contractors.contractor_id')
            ->leftJoin('users', 'contractors.user_id', '=', 'users.user_id')
            ->leftJoin('projects', 'bids.project_id', '=', 'projects.project_id')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->select(
                'bids.*',
                // Bidder Information
                'contractors.company_name',
                'contractors.company_email',
                'contractors.picab_number',
                'contractors.picab_category',
                'contractors.picab_expiration_date',
                'contractors.business_permit_number',
                'contractors.business_permit_city',
                'contractors.business_permit_expiration',
                'contractors.tin_business_reg_number',
                'users.email as contractor_email',
                // Project Information
                'projects.project_title',
                'projects.project_location',
                'projects.property_type',
                'projects.lot_size',
                'projects.to_finish',
                'projects.budget_range_min',
                'projects.budget_range_max',
                'project_relationships.bidding_due'
            )
            ->where('bids.bid_id', $bidId)
            ->first();

        if (!$bid) {
            return null;
        }

        // Get project files (changed from "Uploaded Photos" to "Uploaded Files")
        $projectFiles = DB::table('project_files')
            ->select(
                'file_id',
                'file_type',
                'file_path',
                'uploaded_at'
            )
            ->where('project_id', $bid->project_id)
            ->orderBy('uploaded_at', 'desc')
            ->get();

        $bid->project_files = $projectFiles;

        // Get supporting files for this specific bid
        $bidFiles = DB::table('bid_files')
            ->select(
                'bid_files.file_id',
                'bid_files.file_name',
                'bid_files.file_path',
                'bid_files.uploaded_at'
            )
            ->where('bid_files.bid_id', $bidId)
            ->orderBy('bid_files.uploaded_at', 'desc')
            ->get();

        $bid->bid_files = $bidFiles;

        return $bid;
    }

    public function fetchAcceptBidSummary($bidId)
    {
        $bid = DB::table('bids')
            ->leftJoin('contractors', 'bids.contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'bids.bid_id',
                'bids.contractor_id',
                'bids.project_id',
                'bids.proposed_cost',
                'bids.estimated_timeline',
                'contractors.company_name'
            )
            ->where('bids.bid_id', $bidId)
            ->first();

        return $bid;
    }

    public function fetchRejectBidSummary($bidId)
    {
        $bid = DB::table('bids')
            ->leftJoin('contractors', 'bids.contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'bids.bid_id',
                'bids.contractor_id',
                'bids.project_id',
                'bids.proposed_cost',
                'bids.estimated_timeline',
                'contractors.company_name'
            )
            ->where('bids.bid_id', $bidId)
            ->first();

        return $bid;
    }

    public function rejectBid($bidId, $reason = null)
    {
        try {
            // Update the bid status to rejected with reason and decision date
            DB::table('bids')
                ->where('bid_id', $bidId)
                ->update([
                    'bid_status' => 'rejected',
                    'reason' => $reason,
                    'decision_date' => now()
                ]);

            return ['success' => true, 'message' => 'Bid rejected successfully'];

        } catch (\Exception $e) {
            \Log::error('Error rejecting bid: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to reject bid: ' . $e->getMessage()];
        }
    }

    public function acceptBid($bidId)
    {
        try {
            // Get bid details first
            $bid = DB::table('bids')
                ->select('contractor_id', 'project_id')
                ->where('bid_id', $bidId)
                ->first();

            if (!$bid) {
                return ['success' => false, 'message' => 'Bid not found'];
            }

            // Get the project's relationship_id
            $project = DB::table('projects')
                ->select('relationship_id')
                ->where('project_id', $bid->project_id)
                ->first();

            if (!$project) {
                return ['success' => false, 'message' => 'Project not found'];
            }

            // Start transaction
            DB::beginTransaction();

            // 1. Update bids table - set the selected bid to 'accepted' with decision date
            DB::table('bids')
                ->where('bid_id', $bidId)
                ->update([
                    'bid_status' => 'accepted',
                    'decision_date' => now()
                ]);

            // 2. Reject all other bids for the same project
            DB::table('bids')
                ->where('project_id', $bid->project_id)
                ->where('bid_id', '!=', $bidId)
                ->whereIn('bid_status', ['submitted', 'under_review'])
                ->update([
                    'bid_status' => 'rejected',
                    'decision_date' => now()
                ]);

            // 3. Update projects table - set selected contractor and change status
            DB::table('projects')
                ->where('project_id', $bid->project_id)
                ->update([
                    'selected_contractor_id' => $bid->contractor_id,
                    'project_status' => 'bidding_closed'
                ]);

            // Commit transaction
            DB::commit();

            return ['success' => true, 'message' => 'Bid accepted successfully'];

        } catch (\Exception $e) {
            // Rollback on error
            DB::rollBack();
            \Log::error('Error accepting bid: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to accept bid: ' . $e->getMessage()];
        }
    }

    public function fetchTerminatedProjectDetails($id)
    {
        // Get project with termination details
        $project = DB::table('projects')
            ->leftJoin('contract_terminations', 'projects.project_id', '=', 'contract_terminations.project_id')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->leftJoin('contractor_users', function($join) {
                $join->on('contractors.contractor_id', '=', 'contractor_users.contractor_id')
                     ->on('contractors.user_id', '=', 'contractor_users.user_id');
            })
            ->leftJoin('users as contractor_user_email', 'contractors.user_id', '=', 'contractor_user_email.user_id')
            ->leftJoin('admin_users', 'contract_terminations.id', '=', 'admin_users.admin_id')
            ->select(
                'projects.*',
                'contract_terminations.id as termination_id',
                'contract_terminations.reason',
                'contract_terminations.remarks',
                'contract_terminations.terminated_at',
                'admin_users.username as terminated_by',
                // Contractor info
                DB::raw("CONCAT(contractor_users.authorized_rep_fname, ' ', contractor_users.authorized_rep_lname) as contractor_name"),
                'contractors.company_name',
                'contractor_user_email.email as contractor_email',
                'contractors.picab_number as contractor_pcab',
                'contractors.picab_category as contractor_category',
                'contractors.picab_expiration_date as contractor_pcab_expiry',
                'contractors.business_permit_number as contractor_permit',
                'contractors.business_permit_city as contractor_city',
                'contractors.business_permit_expiration as contractor_permit_expiry',
                'contractors.tin_business_reg_number as contractor_tin'
            )
            ->where('projects.project_id', $id)
            ->first();

        if (!$project) {
            return null;
        }

        // Get timeline from milestones
        $timeline = DB::table('milestones')
            ->where('project_id', $id)
            ->selectRaw('MIN(start_date) as timeline_start, MAX(end_date) as timeline_end')
            ->first();

        if ($timeline && $timeline->timeline_start && $timeline->timeline_end) {
            $project->timeline = \Carbon\Carbon::parse($timeline->timeline_start)->format('M j, Y') . ' - ' . \Carbon\Carbon::parse($timeline->timeline_end)->format('M j, Y');
            $project->deadline = \Carbon\Carbon::parse($timeline->timeline_end)->format('M j, Y');
        } else {
            $project->timeline = 'N/A';
            $project->deadline = 'N/A';
        }

        // Get project cost from payment plans
        $paymentPlan = DB::table('payment_plans')
            ->join('milestones', 'payment_plans.plan_id', '=', 'milestones.plan_id')
            ->where('milestones.project_id', $id)
            ->select('payment_plans.total_project_cost')
            ->first();

        $project->project_cost = $paymentPlan ? $paymentPlan->total_project_cost : 0;

        // Get last active milestone (last completed before termination)
        $lastActiveMilestone = DB::table('milestone_items')
            ->join('milestones', 'milestone_items.milestone_id', '=', 'milestones.milestone_id')
            ->where('milestones.project_id', $id)
            ->where('milestone_items.item_status', 'completed')
            ->orderBy('milestone_items.sequence_order', 'desc')
            ->select('milestone_items.milestone_item_title')
            ->first();

        $project->last_active_milestone = $lastActiveMilestone ? $lastActiveMilestone->milestone_item_title : 'None';

        // Get termination proof files
        $terminationFiles = [];
        if ($project->termination_id) {
            $terminationFiles = DB::table('termination_proof')
                ->where('termination_id', $project->termination_id)
                ->select('proof_id', 'file_path', 'uploaded_at')
                ->get();
        }
        $project->termination_files = $terminationFiles;

        // Get project files
        $projectFiles = DB::table('project_files')
            ->where('project_id', $id)
            ->select('file_id', 'file_type', 'file_path', 'uploaded_at')
            ->get();
        $project->project_files = $projectFiles;

        // Get milestone items with progress
        $milestoneItems = DB::table('milestone_items')
            ->join('milestones', 'milestone_items.milestone_id', '=', 'milestones.milestone_id')
            ->leftJoin('progress', 'milestone_items.item_id', '=', 'progress.milestone_item_id')
            ->leftJoin('progress_files', 'progress.progress_id', '=', 'progress_files.progress_id')
            ->where('milestones.project_id', $id)
            ->select(
                'milestone_items.*',
                'milestones.milestone_id',
                'progress.progress_id',
                'progress.purpose',
                'progress.progress_status',
                'progress.submitted_at',
                'progress_files.file_id',
                'progress_files.file_path',
                'progress_files.original_name'
            )
            ->orderBy('milestone_items.sequence_order')
            ->get();

        // Group progress by item_id with files
        $groupedItems = [];
        foreach ($milestoneItems as $item) {
            $itemId = $item->item_id;
            if (!isset($groupedItems[$itemId])) {
                $groupedItems[$itemId] = $item;
                $groupedItems[$itemId]->progress_reports = [];
            }
            if ($item->progress_id) {
                $progressKey = $item->progress_id;
                if (!isset($groupedItems[$itemId]->progress_reports[$progressKey])) {
                    $groupedItems[$itemId]->progress_reports[$progressKey] = [
                        'progress_id' => $item->progress_id,
                        'purpose' => $item->purpose,
                        'progress_status' => $item->progress_status,
                        'submitted_at' => $item->submitted_at,
                        'files' => []
                    ];
                }

                // Add files to this specific progress report
                if ($item->file_id) {
                    $fileExists = false;
                    foreach ($groupedItems[$itemId]->progress_reports[$progressKey]['files'] as $existingFile) {
                        if ($existingFile['file_id'] == $item->file_id) {
                            $fileExists = true;
                            break;
                        }
                    }
                    if (!$fileExists) {
                        $groupedItems[$itemId]->progress_reports[$progressKey]['files'][] = [
                            'file_id' => $item->file_id,
                            'file_path' => $item->file_path,
                            'original_name' => $item->original_name
                        ];
                    }
                }
            }
        }

        // Convert progress_reports associative array to indexed array
        foreach ($groupedItems as $itemId => $item) {
            $groupedItems[$itemId]->progress_reports = array_values($item->progress_reports);
        }

        $project->milestone_items = array_values($groupedItems);

        // Payment summary calculations - using BOTH 'approved' and 'paid' statuses
        $paymentSummary = DB::table('milestone_payments')
            ->select(
                DB::raw('COUNT(DISTINCT item_id) as total_milestones_paid'),
                DB::raw('SUM(amount) as total_amount_paid'),
                DB::raw('MAX(transaction_date) as last_payment_date')
            )
            ->where('project_id', $id)
            ->whereIn('payment_status', ['approved', 'paid'])
            ->first();

        $totalMilestoneItems = count($groupedItems);
        $project->total_milestone_items = $totalMilestoneItems;
        $project->total_milestones_paid = $paymentSummary->total_milestones_paid ?? 0;
        $project->total_amount_paid = $paymentSummary->total_amount_paid ?? 0;
        $project->last_payment_date = $paymentSummary->last_payment_date;
        $project->overall_payment_status = ($project->total_milestones_paid > 0)
            ? 'Partially Paid'
            : 'Terminated';

        // Get payment records for table - filter to approved/paid only
        $payments = DB::table('milestone_payments')
            ->leftJoin('milestone_items', 'milestone_payments.item_id', '=', 'milestone_items.item_id')
            ->select(
                'milestone_payments.*',
                'milestone_items.sequence_order',
                'milestone_items.date_to_finish',
                'milestone_items.milestone_item_title'
            )
            ->where('milestone_payments.project_id', $id)
            ->whereIn('milestone_payments.payment_status', ['approved', 'paid'])
            ->orderBy('milestone_items.sequence_order', 'asc')
            ->get();

        $project->payments = $payments;

        return $project;
    }

    public function fetchHaltedProjectDetails($id)
    {
        // Main project query with all joins (same as completed modal)
        $project = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users as owner_users', 'property_owners.user_id', '=', 'owner_users.user_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->leftJoin('contractor_users', function($join) {
                $join->on('contractors.contractor_id', '=', 'contractor_users.contractor_id')
                     ->on('contractors.user_id', '=', 'contractor_users.user_id');
            })
            ->leftJoin('users as contractor_user_email', 'contractors.user_id', '=', 'contractor_user_email.user_id')
            ->select(
                'projects.*',
                'project_relationships.bidding_due',
                'project_relationships.created_at as relationship_created_at',
                // Owner info
                'property_owners.first_name as owner_first_name',
                'property_owners.middle_name as owner_middle_name',
                'property_owners.last_name as owner_last_name',
                'owner_users.profile_pic as owner_profile_pic',
                // Contractor info
                DB::raw("CONCAT(contractor_users.authorized_rep_fname, ' ', contractor_users.authorized_rep_lname) as contractor_name"),
                'contractors.company_name',
                'contractor_user_email.email as contractor_email',
                'contractors.picab_number as contractor_pcab',
                'contractors.picab_category as contractor_category',
                'contractors.picab_expiration_date as contractor_pcab_expiry',
                'contractors.business_permit_number as contractor_permit',
                'contractors.business_permit_city as contractor_city',
                'contractors.business_permit_expiration as contractor_permit_expiry',
                'contractors.tin_business_reg_number as contractor_tin'
            )
            ->where('projects.project_id', $id)
            ->first();

        if (!$project) {
            return null;
        }

        // Format owner name
        $project->owner_name = trim(($project->owner_first_name ?? '') . ' ' . ($project->owner_middle_name ?? '') . ' ' . ($project->owner_last_name ?? ''));
        if (empty($project->owner_name)) {
            $project->owner_name = 'Unknown Owner';
        }

        // Get halted date (use relationship_created_at as fallback)
        $project->halted_at = $project->relationship_created_at ?? now();

        // Calculate target timeline from milestones
        $timelineData = DB::table('milestones')
            ->where('project_id', $id)
            ->selectRaw('MIN(start_date) as timeline_start, MAX(end_date) as timeline_end')
            ->first();

        $project->timeline_start = $timelineData->timeline_start ?? null;
        $project->timeline_end = $timelineData->timeline_end ?? null;

        // Get project files
        $projectFiles = DB::table('project_files')
            ->where('project_id', $id)
            ->select('file_id', 'file_type', 'file_path', 'uploaded_at')
            ->get();
        $project->project_files = $projectFiles;

        // Get milestone items with progress files
        $milestoneItems = DB::table('milestone_items')
            ->join('milestones', 'milestone_items.milestone_id', '=', 'milestones.milestone_id')
            ->leftJoin('progress', 'milestone_items.item_id', '=', 'progress.milestone_item_id')
            ->leftJoin('progress_files', 'progress.progress_id', '=', 'progress_files.progress_id')
            ->where('milestones.project_id', $id)
            ->select(
                'milestone_items.*',
                'milestones.milestone_id',
                'progress.progress_id',
                'progress.purpose',
                'progress.progress_status',
                'progress.submitted_at',
                'progress_files.file_id as progress_file_id',
                'progress_files.file_path as progress_file_path'
            )
            ->orderBy('milestone_items.sequence_order')
            ->get();

        // Group milestone items by item_id with their progress files
        $groupedItems = [];
        foreach ($milestoneItems as $item) {
            $itemId = $item->item_id;
            if (!isset($groupedItems[$itemId])) {
                $groupedItems[$itemId] = $item;
                $groupedItems[$itemId]->progress_reports = [];
            }

            // Add progress report with files if exists
            if ($item->progress_id) {
                $progressKey = $item->progress_id;
                if (!isset($groupedItems[$itemId]->progress_reports[$progressKey])) {
                    $groupedItems[$itemId]->progress_reports[$progressKey] = [
                        'progress_id' => $item->progress_id,
                        'purpose' => $item->purpose,
                        'progress_status' => $item->progress_status,
                        'submitted_at' => $item->submitted_at,
                        'files' => []
                    ];
                }
                if ($item->progress_file_id) {
                    $groupedItems[$itemId]->progress_reports[$progressKey]['files'][] = [
                        'file_id' => $item->progress_file_id,
                        'file_path' => $item->progress_file_path
                    ];
                }
            }
        }

        // Convert progress_reports associative array to indexed array
        foreach ($groupedItems as $itemId => $item) {
            $groupedItems[$itemId]->progress_reports = array_values($item->progress_reports);
        }

        $project->milestone_items = array_values($groupedItems);

        // Payment summary calculations - approved/paid only
        $paymentSummary = DB::table('milestone_payments')
            ->where('project_id', $id)
            ->whereIn('payment_status', ['approved', 'paid'])
            ->selectRaw('COUNT(*) as total_milestones_paid')
            ->selectRaw('SUM(amount) as total_amount_paid')
            ->selectRaw('MAX(transaction_date) as last_payment_date')
            ->first();

        $totalMilestoneItems = count($groupedItems);
        $project->total_milestone_items = $totalMilestoneItems;
        $project->total_milestones_paid = $paymentSummary->total_milestones_paid ?? 0;
        $project->total_amount_paid = $paymentSummary->total_amount_paid ?? 0;
        $project->last_payment_date = $paymentSummary->last_payment_date;
        $project->overall_payment_status = ($project->total_milestones_paid > 0)
            ? 'Partially Paid'
            : 'No Payments';

        // Get payment records for table - approved/paid only
        $payments = DB::table('milestone_payments')
            ->leftJoin('milestone_items', 'milestone_payments.item_id', '=', 'milestone_items.item_id')
            ->leftJoin('milestones', 'milestone_items.milestone_id', '=', 'milestones.milestone_id')
            ->where('milestone_payments.project_id', $id)
            ->whereIn('milestone_payments.payment_status', ['approved', 'paid'])
            ->select(
                'milestone_payments.*',
                'milestone_items.milestone_item_title',
                'milestone_items.sequence_order',
                'milestone_items.date_to_finish',
                'milestones.start_date',
                'milestones.end_date'
            )
            ->orderBy('milestone_items.sequence_order', 'asc')
            ->get();

        $project->payments = $payments;

        return $project;
    }

    public function fetchHaltDetails($id)
    {
        // Get the most recent dispute for this project that caused the halt
        $haltDispute = DB::table('disputes')
            ->leftJoin('users as raised_user', 'disputes.raised_by_user_id', '=', 'raised_user.user_id')
            ->leftJoin('property_owners', 'raised_user.user_id', '=', 'property_owners.user_id')
            ->leftJoin('contractors', 'raised_user.user_id', '=', 'contractors.user_id')
            ->leftJoin('milestone_items', 'disputes.milestone_item_id', '=', 'milestone_items.item_id')
            ->leftJoin('projects', 'disputes.project_id', '=', 'projects.project_id')
            ->where('disputes.project_id', $id)
            ->whereIn('disputes.dispute_status', ['open', 'under_review'])
            ->select(
                'disputes.*',
                'disputes.project_id',
                'raised_user.user_type',
                'property_owners.first_name as owner_first_name',
                'property_owners.last_name as owner_last_name',
                'contractors.company_name as contractor_name',
                'milestone_items.milestone_item_title',
                'projects.remarks as project_remarks'
            )
            ->orderBy('disputes.created_at', 'desc')
            ->first();

        if (!$haltDispute) {
            return null;
        }

        // Format initiated_by based on user_type
        if ($haltDispute->user_type === 'property_owner') {
            $haltDispute->initiated_by = trim(($haltDispute->owner_first_name ?? '') . ' ' . ($haltDispute->owner_last_name ?? ''));
        } else if ($haltDispute->user_type === 'contractor') {
            $haltDispute->initiated_by = $haltDispute->contractor_name ?? 'Unknown Contractor';
        } else {
            $haltDispute->initiated_by = 'Unknown User';
        }

        // Get supporting files for this dispute
        $supportingFiles = DB::table('dispute_files')
            ->where('dispute_id', $haltDispute->dispute_id)
            ->select('file_id', 'storage_path', 'original_name', 'mime_type', 'size', 'uploaded_at')
            ->get();

        $haltDispute->supporting_files = $supportingFiles;

        return $haltDispute;
    }

    public function cancelHaltedProject($id, $remarks)
    {
        // Update all halt and non-completed milestone items to cancelled
        DB::table('milestone_items')
            ->join('milestones', 'milestone_items.milestone_id', '=', 'milestones.milestone_id')
            ->where('milestones.project_id', $id)
            ->whereIn('milestone_items.item_status', ['halt', 'not_started', 'in_progress', 'delayed'])
            ->update([
                'milestone_items.item_status' => 'cancelled'
            ]);

        // Close all open/under_review disputes for this project
        DB::table('disputes')
            ->where('project_id', $id)
            ->whereIn('dispute_status', ['open', 'under_review'])
            ->update([
                'dispute_status' => 'closed'
            ]);

        // Update project status to terminated with remarks
        $updated = DB::table('projects')
            ->where('project_id', $id)
            ->update([
                'project_status' => 'terminated',
                'stat_reason' => $remarks
            ]);

        return $updated > 0;
    }

    public function resumeHaltedProject($id)
    {
        // Update all halted milestone items to not_started
        DB::table('milestone_items')
            ->join('milestones', 'milestone_items.milestone_id', '=', 'milestones.milestone_id')
            ->where('milestones.project_id', $id)
            ->where('milestone_items.item_status', 'halt')
            ->update([
                'milestone_items.item_status' => 'not_started'
            ]);

        // Close all open/under_review disputes for this project
        DB::table('disputes')
            ->where('project_id', $id)
            ->whereIn('dispute_status', ['open', 'under_review'])
            ->update([
                'dispute_status' => 'closed'
            ]);

        // Update project status to in_progress
        $updated = DB::table('projects')
            ->where('project_id', $id)
            ->update([
                'project_status' => 'in_progress'
            ]);

        return $updated > 0;
    }

    public function fetchProjectForEdit($id)
    {
        // Main project query with current contractor details
        $project = DB::table('projects')
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->select(
                'projects.project_id',
                'projects.project_title',
                'projects.project_description',
                'projects.property_type',
                'projects.lot_size',
                'projects.floor_area',
                'projects.project_location',
                'projects.selected_contractor_id',
                'projects.project_status',
                // Owner details
                DB::raw("CONCAT(property_owners.first_name, ' ', COALESCE(property_owners.middle_name, ''), ' ', property_owners.last_name) as owner_name"),
                // Current contractor details
                'contractors.company_name',
                'contractors.company_email as contractor_email',
                'contractors.company_phone as contractor_phone',
                'contractors.business_address as contractor_address',
                'contractors.picab_number as pcab_license_no',
                'contractors.picab_category',
                'contractors.business_permit_number as business_permit',
                'contractors.business_permit_city',
                'contractors.tin_business_reg_number as tin_no',
                'contractors.years_of_experience',
                'contractor_types.type_name as contractor_type'
            )
            ->where('projects.project_id', $id)
            ->first();

        if (!$project) {
            return null;
        }

        // Get project files
        $files = DB::table('project_files')
            ->select('file_id', 'file_path', 'file_type')
            ->where('project_id', $id)
            ->get();

        $project->files = $files;

        // Get alternative contractors (bidders who were NOT selected)
        $alternativeContractors = DB::table('bids')
            ->join('contractors', 'bids.contractor_id', '=', 'contractors.contractor_id')
            ->leftJoin('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->select(
                'contractors.contractor_id',
                'contractors.company_name',
                'contractors.company_email as email',
                'contractors.company_phone as phone_number',
                'contractors.business_address as address',
                'contractors.picab_number as pcab_license_no',
                'contractors.picab_category',
                'contractors.business_permit_number as business_permit',
                'contractors.business_permit_city',
                'contractors.tin_business_reg_number as tin_no',
                'contractors.years_of_experience',
                'contractor_types.type_name as contractor_type',
                'bids.bid_id',
                'bids.proposed_cost',
                'bids.estimated_timeline',
                'bids.contractor_notes',
                'bids.bid_status'
            )
            ->where('bids.project_id', $id)
            ->where('bids.bid_status', '!=', 'accepted')
            ->get();

        // Fetch bid files for each alternative contractor
        foreach ($alternativeContractors as $contractor) {
            $contractor->bid_files = DB::table('bid_files')
                ->select('file_id', 'file_name', 'file_path', 'description')
                ->where('bid_id', $contractor->bid_id)
                ->get();
        }

        $project->alternative_contractors = $alternativeContractors;

        return $project;
    }

    public function fetchDeleteSummary($id)
    {
        $project = DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'projects.project_id',
                'projects.project_title',
                'projects.project_status',
                DB::raw("CONCAT(property_owners.first_name, ' ', COALESCE(property_owners.middle_name, ''), ' ', property_owners.last_name) as owner_name"),
                'contractors.company_name as contractor_name'
            )
            ->where('projects.project_id', $id)
            ->first();

        return $project;
    }

    public function deleteProject($id, $reason)
    {
        try {
            DB::beginTransaction();

            // Get current project status before deletion
            $project = DB::table('projects')->where('project_id', $id)->first();

            if (!$project) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Project not found'
                ];
            }

            // Save current status and update to deleted
            DB::table('projects')
                ->where('project_id', $id)
                ->update([
                    'previous_status' => $project->project_status,
                    'project_status' => 'deleted',
                    'stat_reason' => $reason
                ]);

            // Save and update all milestones to deleted status
            $milestones = DB::table('milestones')
                ->where('project_id', $id)
                ->get();

            foreach ($milestones as $milestone) {
                DB::table('milestones')
                    ->where('milestone_id', $milestone->milestone_id)
                    ->update([
                        'previous_status' => $milestone->milestone_status,
                        'milestone_status' => 'deleted'
                    ]);
            }

            // Save and update all milestone items to deleted status
            $milestoneIds = $milestones->pluck('milestone_id')->toArray();

            if (!empty($milestoneIds)) {
                $items = DB::table('milestone_items')
                    ->whereIn('milestone_id', $milestoneIds)
                    ->get();

                foreach ($items as $item) {
                    DB::table('milestone_items')
                        ->where('item_id', $item->item_id)
                        ->update([
                            'previous_status' => $item->item_status,
                            'item_status' => 'deleted'
                        ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Project deleted successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error deleting project: ' . $e->getMessage()
            ];
        }
    }

    public function fetchMilestoneItemForEdit($itemId)
    {
        $item = DB::table('milestone_items')
            ->join('milestones', 'milestone_items.milestone_id', '=', 'milestones.milestone_id')
            ->select(
                'milestone_items.*',
                'milestones.milestone_name'
            )
            ->where('milestone_items.item_id', $itemId)
            ->first();

        return $item;
    }

    public function fetchHaltSummary($id)
    {
        $project = DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'projects.project_id',
                'projects.project_title',
                'projects.project_status',
                DB::raw("CONCAT(property_owners.first_name, ' ', COALESCE(property_owners.middle_name, ''), ' ', property_owners.last_name) as owner_name"),
                'contractors.company_name as contractor_name'
            )
            ->where('projects.project_id', $id)
            ->first();

        // Fetch open halt disputes for this project
        $disputes = \App\Models\admin\disputeClass::getOpenHaltDisputesForProject($id);

        return [
            'project' => $project,
            'disputes' => $disputes
        ];
    }

    public function haltProject($id, $data)
    {
        try {
            DB::beginTransaction();

            // Get current project
            $project = DB::table('projects')->where('project_id', $id)->first();

            if (!$project) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Project not found'
                ];
            }

            // Only allow halting in_progress or bidding_closed projects
            if (!in_array($project->project_status, ['in_progress', 'bidding_closed'])) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Only ongoing or bidding closed projects can be halted'
                ];
            }

            // Verify the dispute exists and is valid for halting
            $dispute = DB::table('disputes')
                ->where('dispute_id', $data['dispute_id'])
                ->where('project_id', $id)
                ->where('dispute_type', 'Halt')
                ->where('dispute_status', 'open')
                ->first();

            if (!$dispute) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Invalid or closed dispute selected'
                ];
            }

            // Save current status and update to halted
            DB::table('projects')
                ->where('project_id', $id)
                ->update([
                    'previous_status' => $project->project_status,
                    'project_status' => 'halt',
                    'stat_reason' => $data['halt_reason'],
                    'remarks' => $data['project_remarks'] ?? null
                ]);

            // Update the associated dispute status to under_review
            DB::table('disputes')
                ->where('dispute_id', $data['dispute_id'])
                ->update([
                    'dispute_status' => 'under_review',
                    'admin_response' => 'Project halted based on this dispute. Halt reason: ' . $data['halt_reason']
                ]);

            // Update all milestone items to halt status
            $milestones = DB::table('milestones')
                ->where('project_id', $id)
                ->get();

            if ($milestones->isNotEmpty()) {
                $milestoneIds = $milestones->pluck('milestone_id')->toArray();

                // Get all milestone items for this project that are not completed
                $items = DB::table('milestone_items')
                    ->whereIn('milestone_id', $milestoneIds)
                    ->where('item_status', '!=', 'completed')
                    ->get();

                // Update each non-completed milestone item to halt status
                foreach ($items as $item) {
                    DB::table('milestone_items')
                        ->where('item_id', $item->item_id)
                        ->update([
                            'previous_status' => $item->item_status,
                            'item_status' => 'halt'
                        ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Project halted successfully and dispute updated'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error halting project: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to halt project: ' . $e->getMessage()
            ];
        }
    }

    public function resumeProject($id)
    {
        try {
            DB::beginTransaction();

            // Get current project
            $project = DB::table('projects')->where('project_id', $id)->first();

            if (!$project) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Project not found'
                ];
            }

            // Only allow resuming halted projects
            if ($project->project_status !== 'halt') {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Only halted projects can be resumed'
                ];
            }

            // Check if previous_status exists
            if (empty($project->previous_status)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Cannot resume project: previous status not found'
                ];
            }

            // Restore project to previous status
            DB::table('projects')
                ->where('project_id', $id)
                ->update([
                    'project_status' => $project->previous_status,
                    'previous_status' => null,
                    'stat_reason' => null,
                    'remarks' => null
                ]);

            // Restore all milestone items to their previous status
            $milestones = DB::table('milestones')
                ->where('project_id', $id)
                ->get();

            if ($milestones->isNotEmpty()) {
                $milestoneIds = $milestones->pluck('milestone_id')->toArray();

                // Get all milestone items that are currently halted
                $items = DB::table('milestone_items')
                    ->whereIn('milestone_id', $milestoneIds)
                    ->where('item_status', 'halt')
                    ->get();

                // Restore each halted milestone item to its previous status
                foreach ($items as $item) {
                    if (!empty($item->previous_status)) {
                        DB::table('milestone_items')
                            ->where('item_id', $item->item_id)
                            ->update([
                                'item_status' => $item->previous_status,
                                'previous_status' => null
                            ]);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Project resumed successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error resuming project: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to resume project: ' . $e->getMessage()
            ];
        }
    }

    public function updateMilestoneItem($itemId, $data)
    {
        try {
            DB::beginTransaction();

            $updated = DB::table('milestone_items')
                ->where('item_id', $itemId)
                ->update([
                    'milestone_item_title' => $data['milestone_item_title'],
                    'milestone_item_description' => $data['milestone_item_description'],
                    'date_to_finish' => $data['date_to_finish'],
                    'milestone_item_cost' => $data['milestone_item_cost'],
                    'item_status' => $data['item_status']
                ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Milestone item updated successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error updating milestone item: ' . $e->getMessage()
            ];
        }
    }

    public function fetchRestoreSummary($id)
    {
        $project = DB::table('projects')
            ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('contractors', 'projects.selected_contractor_id', '=', 'contractors.contractor_id')
            ->select(
                'projects.project_id',
                'projects.project_title',
                'projects.project_status',
                'projects.stat_reason',
                DB::raw("CONCAT(property_owners.first_name, ' ', COALESCE(property_owners.middle_name, ''), ' ', property_owners.last_name) as owner_name"),
                'contractors.company_name as contractor_name'
            )
            ->where('projects.project_id', $id)
            ->first();

        return $project;
    }

    public function restoreProject($id)
    {
        try {
            DB::beginTransaction();

            // Get current project to check if it's deleted
            $project = DB::table('projects')->where('project_id', $id)->first();

            if (!$project || $project->project_status !== 'deleted') {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Project is not in deleted status'
                ];
            }

            // Check if previous status exists
            if (!$project->previous_status) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Cannot restore: previous status not found'
                ];
            }

            // Restore project to previous status
            DB::table('projects')
                ->where('project_id', $id)
                ->update([
                    'project_status' => $project->previous_status,
                    'previous_status' => null,
                    'stat_reason' => null
                ]);

            // Restore all milestones to their previous status
            $milestones = DB::table('milestones')
                ->where('project_id', $id)
                ->get();

            foreach ($milestones as $milestone) {
                $restoreStatus = $milestone->previous_status ?? 'pending';
                DB::table('milestones')
                    ->where('milestone_id', $milestone->milestone_id)
                    ->update([
                        'milestone_status' => $restoreStatus,
                        'previous_status' => null
                    ]);
            }

            // Restore all milestone items to their previous status
            $milestoneIds = $milestones->pluck('milestone_id')->toArray();

            if (!empty($milestoneIds)) {
                $items = DB::table('milestone_items')
                    ->whereIn('milestone_id', $milestoneIds)
                    ->get();

                foreach ($items as $item) {
                    $restoreStatus = $item->previous_status ?? 'pending';
                    DB::table('milestone_items')
                        ->where('item_id', $item->item_id)
                        ->update([
                            'item_status' => $restoreStatus,
                            'previous_status' => null
                        ]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Project restored successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error restoring project: ' . $e->getMessage()
            ];
        }
    }

    public function updateProject($id, $data)
    {
        try {
            DB::beginTransaction();

            // Prepare project update data (basic fields only)
            $updateData = [
                'project_title' => $data['project_title'],
                'project_description' => $data['project_description'],
                'property_type' => $data['property_type'],
                'lot_size' => $data['lot_size'],
                'floor_area' => $data['floor_area'],
                'project_location' => $data['project_location']
            ];

            // Only update contractor if explicitly provided and different
            if (isset($data['selected_contractor_id']) && $data['selected_contractor_id'] !== null && $data['selected_contractor_id'] !== '') {
                $updateData['selected_contractor_id'] = $data['selected_contractor_id'];
            }

            // Update project details
            // Note: update() returns 0 if no changes were made (data is identical)
            // This is still a success case - don't treat it as an error
            DB::table('projects')
                ->where('project_id', $id)
                ->update($updateData);

            // Handle contractor change (only if contractor was actually changed)
            if (isset($data['selected_contractor_id']) && $data['selected_contractor_id'] && $data['selected_contractor_id'] != $data['old_contractor_id']) {
                // Update new contractor's bid to accepted
                DB::table('bids')
                    ->where('project_id', $id)
                    ->where('contractor_id', $data['selected_contractor_id'])
                    ->update([
                        'bid_status' => 'accepted',
                        'decision_date' => now()
                    ]);

                // Update old contractor's bid to rejected (if exists)
                if ($data['old_contractor_id']) {
                    DB::table('bids')
                        ->where('project_id', $id)
                        ->where('contractor_id', $data['old_contractor_id'])
                        ->update([
                            'bid_status' => 'rejected',
                            'decision_date' => now(),
                            'reason' => 'Contractor changed by administrator'
                        ]);

                    // Update milestones to new contractor
                    DB::table('milestones')
                        ->where('project_id', $id)
                        ->update(['contractor_id' => $data['selected_contractor_id']]);
                }

                DB::commit();
                return [
                    'success' => true,
                    'message' => 'Project updated successfully and contractor changed'
                ];
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Project updated successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error updating project: ' . $e->getMessage()
            ];
        }
    }
}
