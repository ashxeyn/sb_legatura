<?php

namespace App\Models\both;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * dashboardClass — Query-builder helper for dashboard data.
 *
 * Centralises every DB query that the owner and contractor dashboards need
 * so that controllers stay thin and dashboard rules live in one place.
 */
class dashboardClass
{
    /* =====================================================================
     * SHARED HELPERS
     * ===================================================================== */

    /**
     * Look up a property_owner row by user_id.
     */
    public function getOwnerByUserId(int $userId): ?object
    {
        return DB::table('property_owners')
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Look up a contractor row by user_id (including staff members).
     */
    public function getContractorByUserId(int $userId): ?object
    {
        // Direct contractor
        $contractor = DB::table('contractors')
            ->where('user_id', $userId)
            ->first();

        if ($contractor) {
            return $contractor;
        }

        // Staff member — get parent contractor
        $staffMember = DB::table('contractor_users')
            ->where('user_id', $userId)
            ->first();

        if ($staffMember) {
            return DB::table('contractors')
                ->where('contractor_id', $staffMember->contractor_id)
                ->first();
        }

        return null;
    }

    /* =====================================================================
     * OWNER DASHBOARD QUERIES
     * ===================================================================== */

    /**
     * Get all owner projects with bid counts and accepted-bid details.
     *
     * Returns a collection of project objects enriched with:
     *  - bids_count
     *  - accepted_bid (object | null)
     *  - contractor_info (object | null)
     *  - milestones (array) with items + payment_plan
     *  - display_status
     */
    public function getOwnerProjects(int $ownerId): \Illuminate\Support\Collection
    {
        $projects = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->where('pr.owner_id', $ownerId)
            ->select(
                'p.project_id',
                'p.project_title',
                'p.project_description',
                'p.project_location',
                'p.budget_range_min',
                'p.budget_range_max',
                'p.lot_size',
                'p.floor_area',
                'p.property_type',
                'p.type_id',
                'p.to_finish',
                'pr.rel_id as relationship_id',
                'pr.project_post_status',
                'pr.bidding_due',
                'pr.created_at',
                'pr.updated_at'
            )
            ->orderBy('pr.created_at', 'desc')
            ->get();

        foreach ($projects as $project) {
            // Bid count
            $project->bids_count = DB::table('bids')
                ->where('project_id', $project->project_id)
                ->count();

            // Accepted bid + contractor info
            $acceptedBid = DB::table('bids')
                ->where('project_id', $project->project_id)
                ->where('bid_status', 'accepted')
                ->first();

            $project->accepted_bid = $acceptedBid;
            $project->contractor_info = null;

            if ($acceptedBid) {
                $project->contractor_info = DB::table('contractors as c')
                    ->join('users as u', 'c.user_id', '=', 'u.user_id')
                    ->where('c.contractor_id', $acceptedBid->contractor_id)
                    ->select(
                        'c.contractor_id',
                        'c.company_name',
                        'u.username',
                        'u.profile_pic'
                    )
                    ->first();
            }

            // Milestones with items + payment plan
            $this->attachMilestones($project);
        }

        return $projects;
    }

    /**
     * Compute owner dashboard statistics from a project collection.
     *
     * Returns: total, pending, active, inProgress
     */
    public function computeOwnerStats(\Illuminate\Support\Collection $projects): array
    {
        $total = $projects->count();
        $pending = $projects->where('project_post_status', 'under_review')->count();
        $active = $projects->where('project_post_status', 'approved')->count();
        $inProgress = $projects->filter(
            fn($p) =>
            isset($p->accepted_bid) && $p->accepted_bid
            && (!isset($p->display_status) || $p->display_status !== 'completed')
        )->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'active' => $active,
            'inProgress' => $inProgress,
        ];
    }

    /* =====================================================================
     * CONTRACTOR DASHBOARD QUERIES
     * ===================================================================== */

    /**
     * Get all contractor projects (projects with accepted bids for this contractor).
     *
     * Returns a collection enriched with: milestones, items, payment_plan,
     * owner_info, display_status, bid_id, bid_status, bid_amount.
     */
    public function getContractorProjects(int $contractorId): \Illuminate\Support\Collection
    {
        // Get bids for this contractor
        $bids = DB::table('bids')
            ->where('contractor_id', $contractorId)
            ->get();

        if ($bids->isEmpty()) {
            return collect([]);
        }

        $projectIds = $bids->pluck('project_id')->unique()->toArray();

        $projects = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->whereIn('p.project_id', $projectIds)
            ->select(
                'p.project_id',
                'p.project_title',
                'p.project_description',
                'p.project_location',
                'p.budget_range_min',
                'p.budget_range_max',
                'p.lot_size',
                'p.floor_area',
                'p.property_type',
                'p.type_id',
                'p.to_finish',
                'pr.rel_id as relationship_id',
                'pr.project_post_status',
                'pr.owner_id',
                'pr.bidding_due',
                'pr.created_at',
                'pr.updated_at'
            )
            ->orderBy('pr.created_at', 'desc')
            ->get();

        foreach ($projects as $project) {
            // Attach bid info
            $bid = $bids->where('project_id', $project->project_id)->first();
            $project->bid_id = $bid->bid_id ?? null;
            $project->bid_status = $bid->bid_status ?? null;
            $project->bid_amount = $bid->bid_amount ?? null;

            // Owner info
            $project->owner_info = null;
            if (isset($project->owner_id)) {
                $owner = DB::table('property_owners as po')
                    ->join('users as u', 'po.user_id', '=', 'u.user_id')
                    ->where('po.owner_id', $project->owner_id)
                    ->select(
                        'po.owner_id',
                        'po.first_name',
                        'po.last_name',
                        'u.profile_pic'
                    )
                    ->first();
                $project->owner_info = $owner;
            }

            // Milestones (only for accepted bids)
            if ($bid && $bid->bid_status === 'accepted') {
                $this->attachMilestones($project);
            } else {
                $project->milestones = [];
                $project->milestones_count = 0;
                $project->display_status = 'pending';
            }
        }

        return $projects;
    }

    /**
     * Compute contractor dashboard statistics from a project collection.
     *
     * Returns: total (bids), pending, active (won), inProgress, completed
     */
    public function computeContractorStats(\Illuminate\Support\Collection $projects): array
    {
        $totalBids = $projects->filter(fn($p) => isset($p->bid_id) && $p->bid_id)->count();
        $pending = $projects->filter(fn($p) => isset($p->bid_status) && in_array($p->bid_status, ['submitted', 'under_review']))->count();
        $won = $projects->filter(fn($p) => isset($p->bid_status) && $p->bid_status === 'accepted')->count();
        $inProgress = $projects->filter(fn($p) => isset($p->display_status) && $p->display_status === 'in_progress')->count();
        $completed = $projects->filter(fn($p) => isset($p->display_status) && $p->display_status === 'completed')->count();

        return [
            'total' => $totalBids,
            'pending' => $pending,
            'active' => $won,
            'inProgress' => $inProgress,
            'completed' => $completed,
        ];
    }

    /* =====================================================================
     * MILESTONE HELPER
     * ===================================================================== */

    /**
     * Attach milestones (with items + payment_plan) to a project and compute display_status.
     */
    protected function attachMilestones(object $project): void
    {
        $milestones = DB::table('milestones')
            ->where('project_id', $project->project_id)
            ->orderBy('milestone_id', 'asc')
            ->get();

        foreach ($milestones as $milestone) {
            // Milestone items
            $milestone->items = DB::table('milestone_items')
                ->select(
                    'item_id',
                    'sequence_order',
                    'percentage_progress',
                    'milestone_item_title',
                    'milestone_item_description',
                    'milestone_item_cost',
                    'date_to_finish',
                    DB::raw("COALESCE(item_status, '') as item_status")
                )
                ->where('milestone_id', $milestone->milestone_id)
                ->orderBy('sequence_order', 'asc')
                ->get()
                ->toArray();

            // Payment plan
            if (isset($milestone->plan_id) && $milestone->plan_id) {
                $milestone->payment_plan = DB::table('payment_plans')
                    ->select(
                        'plan_id',
                        'payment_mode',
                        'total_project_cost',
                        'downpayment_amount',
                        'is_confirmed'
                    )
                    ->where('plan_id', $milestone->plan_id)
                    ->first();
            } else {
                $milestone->payment_plan = null;
            }
        }

        $project->milestones = $milestones->values()->toArray();
        $project->milestones_count = $milestones->count();

        // Determine display_status
        if ($milestones->isEmpty()) {
            $project->display_status = 'waiting_milestone_setup';
        } else {
            $allCompleted = $milestones->every(
                fn($m) => $m->milestone_status === 'completed' || $m->milestone_status === 'approved'
            );
            $project->display_status = $allCompleted ? 'completed' : 'in_progress';
        }
    }
}
