<?php

namespace App\Jobs;

use App\Services\notificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendDeadlineNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('SendDeadlineNotifications job started');

        $this->checkBiddingDeadlines();
        $this->checkMilestoneStartReminders();
        $this->checkMilestoneItemDueReminders();
        $this->checkOverdueAlerts();
        $this->checkPaymentDueReminders();
        $this->checkDisputeResponseDeadlines();

        Log::info('SendDeadlineNotifications job finished');
    }

    /**
     * Notify contractors who have submitted bids when a bidding deadline is approaching.
     * Windows: 48h, 24h, 6h before bidding_due.
     */
    private function checkBiddingDeadlines(): void
    {
        $windows = [
            ['hours' => 48, 'label' => '48h'],
            ['hours' => 24, 'label' => '24h'],
            ['hours' => 6,  'label' => '6h'],
        ];

        foreach ($windows as $window) {
            $from = now()->addHours($window['hours'] - 1);
            $to   = now()->addHours($window['hours']);

            $projects = DB::table('project_relationships as pr')
                ->join('projects as p', 'pr.rel_id', '=', 'p.relationship_id')
                ->where('p.project_status', 'open')
                ->where('pr.project_post_status', 'approved')
                ->whereBetween('pr.bidding_due', [$from, $to])
                ->select('p.project_id', 'p.project_title', 'pr.bidding_due')
                ->get();

            foreach ($projects as $project) {
                // Find all contractors who bid on this project
                $bidders = DB::table('bids as b')
                    ->join('contractors as c', 'b.contractor_id', '=', 'c.contractor_id')
                    ->join('contractor_users as cu', function ($join) {
                        $join->on('c.contractor_id', '=', 'cu.contractor_id')
                            ->where('cu.is_active', 1)
                            ->where('cu.is_deleted', 0);
                    })
                    ->where('b.project_id', $project->project_id)
                    ->whereIn('b.bid_status', ['submitted', 'under_review'])
                    ->select('cu.user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->all();

                if (empty($bidders)) continue;

                notificationService::createForUsers(
                    $bidders,
                    'bid_received',
                    "Bidding closes in {$window['label']}",
                    "The bidding deadline for \"{$project->project_title}\" is approaching. Make sure your bid is final.",
                    'high',
                    'project',
                    $project->project_id,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => $project->project_id]],
                    "bidding_{$window['label']}_{$project->project_id}"
                );
            }
        }
    }

    /**
     * Remind contractor when a milestone is about to start (24h before start_date).
     */
    private function checkMilestoneStartReminders(): void
    {
        $from = now()->addHours(23);
        $to   = now()->addHours(24);

        $milestones = DB::table('milestones as m')
            ->join('projects as p', 'm.project_id', '=', 'p.project_id')
            ->join('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
            ->join('contractor_users as cu', function ($join) {
                $join->on('c.contractor_id', '=', 'cu.contractor_id')
                    ->where('cu.is_active', 1)
                    ->where('cu.is_deleted', 0);
            })
            ->where('m.milestone_status', 'not_started')
            ->where('m.setup_status', 'approved')
            ->whereBetween('m.start_date', [$from, $to])
            ->select('m.milestone_id', 'm.milestone_name', 'p.project_id', 'p.project_title', 'cu.user_id')
            ->get();

        foreach ($milestones as $ms) {
            notificationService::create(
                $ms->user_id,
                'milestone_submitted',
                'Milestone Starting Soon',
                "Milestone \"{$ms->milestone_name}\" for \"{$ms->project_title}\" starts tomorrow.",
                'normal',
                'milestone',
                $ms->milestone_id,
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => $ms->project_id, 'tab' => 'milestones']],
                "milestone_start_24h_{$ms->milestone_id}"
            );
        }
    }

    /**
     * Remind both parties when a milestone item is due in 48h or 24h.
     */
    private function checkMilestoneItemDueReminders(): void
    {
        $windows = [
            ['hours' => 48, 'label' => '48h'],
            ['hours' => 24, 'label' => '24h'],
        ];

        foreach ($windows as $window) {
            $from = now()->addHours($window['hours'] - 1);
            $to   = now()->addHours($window['hours']);

            $items = DB::table('milestone_items as mi')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->leftJoin('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
                ->leftJoin('contractor_users as cu', function ($join) {
                    $join->on('c.contractor_id', '=', 'cu.contractor_id')
                        ->where('cu.is_active', 1)
                        ->where('cu.is_deleted', 0);
                })
                ->where('m.setup_status', 'approved')
                ->whereNotIn('mi.item_status', ['completed', 'cancelled', 'deleted'])
                ->whereBetween('mi.date_to_finish', [$from, $to])
                ->select(
                    'mi.item_id', 'mi.milestone_item_title',
                    'p.project_id', 'p.project_title',
                    'po.user_id as owner_user_id',
                    'cu.user_id as contractor_user_id'
                )
                ->get();

            foreach ($items as $item) {
                $recipients = array_filter([$item->owner_user_id, $item->contractor_user_id]);
                $recipients = array_unique($recipients);

                notificationService::createForUsers(
                    $recipients,
                    'milestone_completed',
                    "Item due in {$window['label']}",
                    "Milestone item \"{$item->milestone_item_title}\" for \"{$item->project_title}\" is due in {$window['label']}.",
                    'high',
                    'milestone_item',
                    $item->item_id,
                    ['screen' => 'ProjectDetails', 'params' => ['projectId' => $item->project_id, 'tab' => 'milestones']],
                    "item_due_{$window['label']}_{$item->item_id}"
                );
            }
        }
    }

    /**
     * Alert both parties when a milestone item is overdue (past date_to_finish, still active).
     */
    private function checkOverdueAlerts(): void
    {
        $items = DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->join('projects as p', 'm.project_id', '=', 'p.project_id')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->leftJoin('contractors as c', 'p.selected_contractor_id', '=', 'c.contractor_id')
            ->leftJoin('contractor_users as cu', function ($join) {
                $join->on('c.contractor_id', '=', 'cu.contractor_id')
                    ->where('cu.is_active', 1)
                    ->where('cu.is_deleted', 0);
            })
            ->where('m.setup_status', 'approved')
            ->whereNotIn('mi.item_status', ['completed', 'cancelled', 'deleted'])
            ->where('mi.date_to_finish', '<', now())
            ->select(
                'mi.item_id', 'mi.milestone_item_title',
                'p.project_id', 'p.project_title',
                'po.user_id as owner_user_id',
                'cu.user_id as contractor_user_id'
            )
            ->get();

        foreach ($items as $item) {
            $recipients = array_filter(array_unique([$item->owner_user_id, $item->contractor_user_id]));

            notificationService::createForUsers(
                $recipients,
                'milestone_completed',
                'Milestone Item Overdue',
                "Milestone item \"{$item->milestone_item_title}\" for \"{$item->project_title}\" is overdue.",
                'critical',
                'milestone_item',
                $item->item_id,
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => $item->project_id, 'tab' => 'milestones']],
                "item_overdue_{$item->item_id}"
            );
        }
    }

    /**
     * Remind owner when a milestone item is completed but no approved/submitted payment exists.
     * Fires 24h after item completion.
     */
    private function checkPaymentDueReminders(): void
    {
        $from = now()->subHours(25);
        $to   = now()->subHours(24);

        $items = DB::table('milestone_items as mi')
            ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
            ->join('projects as p', 'm.project_id', '=', 'p.project_id')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->leftJoin('milestone_payments as mp', function ($join) {
                $join->on('mi.item_id', '=', 'mp.item_id')
                    ->whereIn('mp.payment_status', ['submitted', 'approved']);
            })
            ->where('mi.item_status', 'completed')
            ->whereNull('mp.payment_id')  // no successful payment exists
            ->where('m.setup_status', 'approved')
            ->whereBetween('mi.updated_at', [$from, $to])
            ->select(
                'mi.item_id', 'mi.milestone_item_title',
                'p.project_id', 'p.project_title',
                'po.user_id as owner_user_id'
            )
            ->get();

        foreach ($items as $item) {
            notificationService::create(
                $item->owner_user_id,
                'payment_due',
                'Payment Reminder',
                "Milestone item \"{$item->milestone_item_title}\" for \"{$item->project_title}\" has been completed. Please submit payment.",
                'high',
                'milestone_item',
                $item->item_id,
                ['screen' => 'ProjectDetails', 'params' => ['projectId' => $item->project_id, 'tab' => 'payments']],
                "payment_due_{$item->item_id}"
            );
        }
    }

    /**
     * Alert the accused party when a dispute has been open for 5+ days without status change.
     */
    private function checkDisputeResponseDeadlines(): void
    {
        $fiveDaysAgo = now()->subDays(5);
        $fiveDaysAndOneHourAgo = now()->subDays(5)->subHour();

        $disputes = DB::table('disputes as d')
            ->join('projects as p', 'd.project_id', '=', 'p.project_id')
            ->where('d.dispute_status', 'open')
            ->whereBetween('d.created_at', [$fiveDaysAndOneHourAgo, $fiveDaysAgo])
            ->select('d.dispute_id', 'd.against_user_id', 'd.dispute_type', 'p.project_title')
            ->get();

        foreach ($disputes as $dispute) {
            notificationService::create(
                $dispute->against_user_id,
                'dispute_opened',
                'Dispute Pending Response',
                "A {$dispute->dispute_type} dispute on \"{$dispute->project_title}\" has been open for 5 days.",
                'high',
                'dispute',
                $dispute->dispute_id,
                ['screen' => 'DisputeDetails', 'params' => ['disputeId' => $dispute->dispute_id]],
                "dispute_response_5d_{$dispute->dispute_id}"
            );
        }
    }
}
