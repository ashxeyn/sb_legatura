<?php

namespace App\Http\Controllers;

use App\Jobs\sendDeadlineNotifications;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class testNotificationsController extends Controller
{
    private array $checks = [
        'all'                              => 'Run ALL checks',
        'checkBiddingDeadlines'            => 'Bidding Deadlines (48h / 24h / 6h before bidding_due)',
        'checkMilestoneStartReminders'     => 'Milestone Start Reminders (24h before milestone start_date)',
        'checkMilestoneItemDueReminders'   => 'Milestone Item Due (48h / 24h before date_to_finish)',
        'checkOverdueAlerts'               => 'Milestone Item Overdue (past date_to_finish, not completed)',
        'checkPaymentDueReminders'         => 'Payment Due — fires 24h after item is COMPLETED with no payment',
        'checkSettlementDueDateReminders'  => 'Settlement Due Date Approaching (7d / 3d / 48h / 24h before settlement_due_date)',
        'checkOverdueSettlementAlerts'     => 'Settlement Overdue — payment not done past settlement_due_date (fires daily)',
        'checkDisputeResponseDeadlines'    => 'Dispute Response Deadline (5 days after dispute opened)',
        'checkSubscriptionExpiryReminders' => 'Subscription Expiry (7d / 3d / 24h before expiration_date)',
        'checkBoostExpiryReminders'        => 'Boost Expiry (3d / 24h before boost expiration_date)',
    ];

    public function index()
    {
        return view('test-notifications', ['checks' => $this->checks]);
    }

    public function run(Request $request)
    {
        $fakeNow  = $request->input('fake_now');
        $checkKey = $request->input('check');

        $usedTime = 'Real time (' . now()->toDateTimeString() . ')';

        if ($fakeNow) {
            try {
                Carbon::setTestNow(Carbon::parse($fakeNow));
                $usedTime = 'Fake time: ' . Carbon::now()->toDateTimeString();
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format: ' . $e->getMessage(),
                ]);
            }
        }

        // Increase execution time limit and disable real email sending during tests
        @ini_set('max_execution_time', '300');
        Config::set('mail.default', 'log');

        $job  = new SendDeadlineNotifications();
        $logs = [];

        try {
            if ($checkKey === 'all' || !$checkKey) {
                $job->handle();
                $logs[] = 'Ran ALL checks.';
            } elseif (isset($this->checks[$checkKey])) {
                $ref = new \ReflectionMethod(sendDeadlineNotifications::class, $checkKey);
                $ref->setAccessible(true);
                $ref->invoke($job);
                $logs[] = 'Ran: ' . $this->checks[$checkKey];
            } else {
                return response()->json(['success' => false, 'message' => 'Unknown check: ' . $checkKey]);
            }
        } catch (\Throwable $e) {
            Carbon::setTestNow(null);
            return response()->json([
                'success'   => false,
                'message'   => 'Error: ' . $e->getMessage(),
                'time_used' => $usedTime,
            ]);
        }

        Carbon::setTestNow(null);

        return response()->json([
            'success'   => true,
            'time_used' => $usedTime,
            'logs'      => $logs,
            'message'   => 'Check completed. Notifications sent (if conditions matched). Check app notifications or Laravel logs for details.',
        ]);
    }

    public function clearDedup()
    {
        $count = DB::table('notifications')
            ->whereNotNull('dedup_key')
            ->update(['dedup_key' => null]);

        return response()->json([
            'success' => true,
            'message' => "Cleared dedup keys from {$count} notifications. All checks can now fire again.",
        ]);
    }
}
