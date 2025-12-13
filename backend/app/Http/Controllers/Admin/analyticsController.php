<?php

namespace App\Http\Controllers\admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\authController;

class analyticsController extends authController
{
    /**
     * Show the project analytics page
     */
    public function analytics()
    {
        $projectsAnalytics = $this->getProjectsAnalytics();
        $projectsTimeline = $this->getProjectsTimeline();
        $projectSuccessRate = $this->getProjectSuccessRate();
        
        return view('admin.home.projectAnalytics', [
            'projectsAnalytics' => $projectsAnalytics,
            'projectsTimeline' => $projectsTimeline,
            'projectSuccessRate' => $projectSuccessRate,
        ]);
    }

    /**
     * Show the subscription analytics page
     */
    public function subscriptionAnalytics()
    {
        $subscriptionMetrics = $this->getSubscriptionMetrics();
        $subscriptionTiers = $this->getSubscriptionTiers();
        $subscriptionRevenue = $this->getSubscriptionRevenue(); // default gold tier
        
        return view('admin.home.subscriptionAnalytics', [
            'subscriptionMetrics' => $subscriptionMetrics,
            'subscriptionTiers' => $subscriptionTiers,
            'subscriptionRevenue' => $subscriptionRevenue,
        ]);
    }

    /**
     * Build tier condition on the query.
     */
    private function applyTierCondition($query, $tier)
    {
        switch ($tier) {
            case 'gold':
                $query->where('amount', '>=', 2000); // >= 2000
                break;
            case 'silver':
                $query->whereBetween('amount', [1000, 1999]);
                break;
            default: // bronze
                $query->where('amount', '<', 1000);
                break;
        }
        return $query;
    }

    /**
     * Get subscription revenue monthly for selected tier (current year + previous year baseline)
     */
    private function getSubscriptionRevenue($tier = 'gold')
    {
        $currentYear = date('Y');
        $previousYear = $currentYear - 1;

        // Months labels Jan .. Dec
        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $currentYearData = array_fill(0, 12, 0.0);
        $previousYearData = array_fill(0, 12, 0.0);

        // Query current year
        $curQuery = DB::table('platform_payments')
            ->select(DB::raw('MONTH(transaction_date) as m'), DB::raw('IFNULL(SUM(amount),0) as sum'))
            ->where('payment_for', 'commission')
            ->where('is_approved', 1)
            ->whereYear('transaction_date', $currentYear);
        $this->applyTierCondition($curQuery, $tier);
        $curRows = $curQuery->groupBy(DB::raw('MONTH(transaction_date)'))->orderBy(DB::raw('MONTH(transaction_date)'))->get();
        foreach ($curRows as $r) {
            $currentYearData[$r->m - 1] = (float)$r->sum;
        }

        // Query previous year baseline
        $prevQuery = DB::table('platform_payments')
            ->select(DB::raw('MONTH(transaction_date) as m'), DB::raw('IFNULL(SUM(amount),0) as sum'))
            ->where('payment_for', 'commission')
            ->where('is_approved', 1)
            ->whereYear('transaction_date', $previousYear);
        $this->applyTierCondition($prevQuery, $tier);
        $prevRows = $prevQuery->groupBy(DB::raw('MONTH(transaction_date)'))->orderBy(DB::raw('MONTH(transaction_date)'))->get();
        foreach ($prevRows as $r) {
            $previousYearData[$r->m - 1] = (float)$r->sum;
        }

        // Date range display (January - current month currentYear)
        $currentMonthIndex = (int)date('n') - 1;
        $dateRange = 'January - ' . $months[$currentMonthIndex] . ' ' . $currentYear;

        return [
            'tier' => $tier,
            'months' => $months,
            'current' => $currentYearData,
            'previous' => $previousYearData,
            'dateRange' => $dateRange,
            'currentYear' => $currentYear,
            'previousYear' => $previousYear,
        ];
    }

    /**
     * AJAX endpoint to fetch subscription revenue data by tier.
     */
    public function subscriptionRevenue(\Illuminate\Http\Request $request)
    {
        $tier = strtolower($request->input('tier', 'gold'));
        if (!in_array($tier, ['gold','silver','bronze'])) {
            return response()->json(['error' => 'Invalid tier'], 400);
        }
        $data = $this->getSubscriptionRevenue($tier);
        return response()->json($data);
    }

    /**
     * Get subscription breakdown by contractor tier (30, 60, 90 contractors)
     */
    private function getSubscriptionTiers()
    {
        // Assuming subscription tiers are based on amount ranges in platform_payments
        // Gold Tier: amount >= 2000
        $goldTier = DB::table('platform_payments')
            ->where('payment_for', 'commission')
            ->where('is_approved', 1)
            ->where('amount', '>=', 2000)
            ->count();

        // Silver Tier: amount between 1000 and 1999
        $silverTier = DB::table('platform_payments')
            ->where('payment_for', 'commission')
            ->where('is_approved', 1)
            ->whereBetween('amount', [1000, 1999])
            ->count();

        // Bronze Tier: amount < 1000
        $bronzeTier = DB::table('platform_payments')
            ->where('payment_for', 'commission')
            ->where('is_approved', 1)
            ->where('amount', '<', 1000)
            ->count();

        return [
            'tiers' => [
                [
                    'name' => 'Gold Tier',
                    'label' => 'Gold Tier',
                    'count' => $goldTier,
                    'color' => '#fbbf24',
                    'gradient' => 'linear-gradient(180deg, #fde047 0%, #fbbf24 100%)'
                ],
                [
                    'name' => 'Silver Tier',
                    'label' => 'Silver Tier',
                    'count' => $silverTier,
                    'color' => '#60a5fa',
                    'gradient' => 'linear-gradient(180deg, #93c5fd 0%, #60a5fa 100%)'
                ],
                [
                    'name' => 'Bronze Tier',
                    'label' => 'Bronze Tier',
                    'count' => $bronzeTier,
                    'color' => '#fb923c',
                    'gradient' => 'linear-gradient(180deg, #fdba74 0%, #fb923c 100%)'
                ]
            ],
            'total' => $goldTier + $silverTier + $bronzeTier,
            'maxCount' => max($goldTier, $silverTier, $bronzeTier)
        ];
    }

    /**
     * Get subscription metrics (total subscriptions, revenue, expiring soon, expired)
     */
    private function getSubscriptionMetrics()
    {
        // Total approved subscriptions (commission payments)
        $totalSubscriptions = DB::table('platform_payments')
            ->where('payment_for', 'commission')
            ->where('is_approved', 1)
            ->count();

        // Total revenue from subscriptions
        $totalRevenue = DB::table('platform_payments')
            ->where('payment_for', 'commission')
            ->where('is_approved', 1)
            ->sum('amount');

        // For subscription duration, let's assume each subscription lasts 30 days from transaction_date
        // Expiring soon: subscriptions expiring in next 7 days
        $expiringSoon = DB::table('platform_payments')
            ->where('payment_for', 'commission')
            ->where('is_approved', 1)
            ->whereRaw('DATE_ADD(transaction_date, INTERVAL 30 DAY) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)')
            ->count();

        // Expired: subscriptions past 30 days
        $expired = DB::table('platform_payments')
            ->where('payment_for', 'commission')
            ->where('is_approved', 1)
            ->whereRaw('DATE_ADD(transaction_date, INTERVAL 30 DAY) < CURDATE()')
            ->count();

        // Active subscriptions (within 30 days)
        $active = DB::table('platform_payments')
            ->where('payment_for', 'commission')
            ->where('is_approved', 1)
            ->whereRaw('DATE_ADD(transaction_date, INTERVAL 30 DAY) >= CURDATE()')
            ->count();

        return [
            'total' => $totalSubscriptions,
            'active' => $active,
            'revenue' => $totalRevenue,
            'expiring' => $expiringSoon,
            'expired' => $expired,
        ];
    }

    /**
     * Get projects breakdown by status
     */
    private function getProjectsAnalytics()
    {
        $statuses = [
            'completed' => 'Completed Projects',
            'in_progress' => 'Ongoing',
            'open' => 'On Hold',
            'terminated' => 'Cancelled'
        ];

        $projectData = [];
        $total = 0;

        foreach ($statuses as $status => $label) {
            $count = DB::table('projects')
                ->where('project_status', $status)
                ->count();
            
            $projectData[] = [
                'status' => $status,
                'label' => $label,
                'count' => $count
            ];
            
            $total += $count;
        }

        return [
            'total' => $total,
            'data' => $projectData
        ];
    }

    /**
     * Get project success rate (completion rate by project status)
     */
    private function getProjectSuccessRate()
    {
        $statuses = [
            'completed' => ['label' => 'Completed', 'color' => '#10b981'],
            'in_progress' => ['label' => 'In progress', 'color' => '#3b82f6'],
            'open' => ['label' => 'On Hold', 'color' => '#f59e0b'],
            'terminated' => ['label' => 'Cancelled', 'color' => '#ef4444']
        ];

        $successData = [];
        $total = 0;

        foreach ($statuses as $status => $info) {
            $count = DB::table('projects')
                ->where('project_status', $status)
                ->count();
            
            if ($count > 0) {
                $successData[] = [
                    'status' => $status,
                    'label' => $info['label'],
                    'color' => $info['color'],
                    'count' => $count
                ];
            }
            
            $total += $count;
        }

        // Calculate percentages
        foreach ($successData as &$item) {
            $item['percentage'] = $total > 0 ? round(($item['count'] / $total) * 100, 1) : 0;
        }

        return [
            'total' => $total,
            'data' => $successData
        ];
    }

    /**
     * Get projects timeline data (new projects vs completed projects by month)
     */
    private function getProjectsTimeline()
    {
        // Get monthly data for the last 6 months
        $months = [];
        $newProjects = [];
        $completedProjects = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-$i months"));
            $monthLabel = date('M Y', strtotime($date));
            $months[] = $monthLabel;
            
            // Count new projects created in this month. Use projects.created_at if present,
            // otherwise use project_relationships.created_at which exists in live schema.
            if (Schema::hasColumn('projects', 'created_at')) {
                $newCount = DB::table('projects')
                    ->whereYear('created_at', date('Y', strtotime($date)))
                    ->whereMonth('created_at', date('m', strtotime($date)))
                    ->count();
            } else {
                $newCount = DB::table('project_relationships')
                    ->whereYear('created_at', date('Y', strtotime($date)))
                    ->whereMonth('created_at', date('m', strtotime($date)))
                    ->count();
            }
            $newProjects[] = $newCount;
            
            // Count projects completed in this month. Prefer `projects.updated_at` if present,
            // otherwise fall back to `project_relationships.created_at` as an approximation.
            if (Schema::hasColumn('projects', 'updated_at')) {
                $completedCount = DB::table('projects')
                    ->where('project_status', 'completed')
                    ->whereYear('updated_at', date('Y', strtotime($date)))
                    ->whereMonth('updated_at', date('m', strtotime($date)))
                    ->count();
            } else {
                $completedCount = DB::table('projects')
                    ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->where('project_status', 'completed')
                    ->whereYear('project_relationships.created_at', date('Y', strtotime($date)))
                    ->whereMonth('project_relationships.created_at', date('m', strtotime($date)))
                    ->count();
            }
            $completedProjects[] = $completedCount;
        }
        
        // Calculate date range for display
        $startDate = date('M d', strtotime('-5 months'));
        $endDate = date('d Y');
        $dateRange = $startDate . ' - ' . $endDate;
        
        return [
            'months' => $months,
            'newProjects' => $newProjects,
            'completedProjects' => $completedProjects,
            'dateRange' => $dateRange,
        ];
    }

    /**
     * Get projects timeline data for AJAX request (JSON response)
     */
    public function getProjectsTimelineData(\Illuminate\Http\Request $request)
    {
        $range = $request->input('range', 'last6months');
        
        $months = [];
        $newProjects = [];
        $completedProjects = [];
        $numMonths = 6;
        
        switch ($range) {
            case 'last3months':
                $numMonths = 3;
                break;
            case 'last6months':
                $numMonths = 6;
                break;
            case 'thisyear':
                $numMonths = date('n'); // Current month number
                break;
            case 'lastyear':
                $numMonths = 12;
                break;
        }
        
        for ($i = $numMonths - 1; $i >= 0; $i--) {
            if ($range === 'lastyear') {
                // For last year, get previous year data
                $date = date('Y-m-01', strtotime("-$i months -1 year"));
            } elseif ($range === 'thisyear') {
                // For this year, start from January
                $date = date('Y-01-01');
                $date = date('Y-m-01', strtotime("+$i months", strtotime($date)));
            } else {
                $date = date('Y-m-01', strtotime("-$i months"));
            }
            
            $monthLabel = date('M Y', strtotime($date));
            $months[] = $monthLabel;
            
            // Count new projects created in this month. Use projects.created_at if present,
            // otherwise use project_relationships.created_at.
            if (Schema::hasColumn('projects', 'created_at')) {
                $newCount = DB::table('projects')
                    ->whereYear('created_at', date('Y', strtotime($date)))
                    ->whereMonth('created_at', date('m', strtotime($date)))
                    ->count();
            } else {
                $newCount = DB::table('project_relationships')
                    ->whereYear('created_at', date('Y', strtotime($date)))
                    ->whereMonth('created_at', date('m', strtotime($date)))
                    ->count();
            }
            $newProjects[] = $newCount;
            
            // Count projects completed in this month. Prefer `projects.updated_at` if present,
            // otherwise fall back to `project_relationships.created_at` as an approximation.
            if (Schema::hasColumn('projects', 'updated_at')) {
                $completedCount = DB::table('projects')
                    ->where('project_status', 'completed')
                    ->whereYear('updated_at', date('Y', strtotime($date)))
                    ->whereMonth('updated_at', date('m', strtotime($date)))
                    ->count();
            } else {
                $completedCount = DB::table('projects')
                    ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->where('project_status', 'completed')
                    ->whereYear('project_relationships.created_at', date('Y', strtotime($date)))
                    ->whereMonth('project_relationships.created_at', date('m', strtotime($date)))
                    ->count();
            }
            $completedProjects[] = $completedCount;
        }
        
        // Calculate date range for display
        if ($range === 'last3months') {
            $startDate = date('M d', strtotime('-2 months'));
            $endDate = date('d Y');
        } elseif ($range === 'last6months') {
            $startDate = date('M d', strtotime('-5 months'));
            $endDate = date('d Y');
        } elseif ($range === 'thisyear') {
            $startDate = date('M d', strtotime('January 1'));
            $endDate = date('d Y');
        } else { // lastyear
            $startDate = date('M d', strtotime('January 1 last year'));
            $endDate = date('d Y', strtotime('December 31 last year'));
        }
        $dateRange = $startDate . ' - ' . $endDate;
        
        return response()->json([
            'months' => $months,
            'newProjects' => $newProjects,
            'completedProjects' => $completedProjects,
            'dateRange' => $dateRange,
        ]);
    }

    /**
     * Show the user activity analytics page
     */
    public function userActivityAnalytics()
    {
        return view('admin.home.userActivity_Analytics');
    }

    /**
     * Show the project performance analytics page
     */
    public function projectPerformanceAnalytics()
    {
        return view('admin.home.projectPerformance_Analytics');
    }

    /**
     * Show the bid completion analytics page
     */
    public function bidCompletionAnalytics()
    {
        return view('admin.home.bidCompletion_Analytics');
    }

    /**
     * Show the reports and analytics page
     */
    public function reportsAnalytics()
    {
        return view('admin.home.reportsAnalytics');
    }

    // =============================================
    // API METHODS FOR AJAX CALLS
    // =============================================

    /**
     * Get projects analytics as JSON
     */
    public function getProjectsAnalyticsApi()
    {
        $projectsAnalytics = $this->getProjectsAnalytics();
        return response()->json($projectsAnalytics);
    }

    /**
     * Get user activity analytics data
     */
    public function getUserActivityAnalyticsApi()
    {
        $userActivity = [
            'total_users' => DB::table('users')->count(),
            'new_users_this_month' => DB::table('users')
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'active_users' => DB::table('users')->where('is_active', 1)->count(),
            'suspended_users' => DB::table('users')->where('is_active', 0)->count(),
        ];

        return response()->json($userActivity);
    }

    /**
     * Get project performance analytics data
     */
    public function getProjectPerformanceAnalyticsApi()
    {
        $performance = [
            'average_project_duration' => 0,  // projects table has no created_at/updated_at
            'on_time_completion' => DB::table('projects')
                ->where('project_status', 'completed')
                ->count(),
            'delayed_completion' => 0,
        ];

        return response()->json($performance);
    }

    /**
     * Get bid completion analytics data
     */
    public function getBidCompletionAnalyticsApi()
    {
        $bidCompletion = [
            'total_bids' => DB::table('bids')->count(),
            'completed_bids' => DB::table('bids')->where('bid_status', 'accepted')->count(),
            'rejected_bids' => DB::table('bids')->where('bid_status', 'rejected')->count(),
            'pending_bids' => DB::table('bids')->where('bid_status', 'pending')->count(),
            'average_bids_per_project' => DB::table('bids')
                ->selectRaw('AVG(bid_count) as avg')
                ->from(DB::raw('(SELECT COUNT(*) as bid_count FROM bids GROUP BY project_id) as t'))
                ->value('avg') ?? 0,
        ];

        return response()->json($bidCompletion);
    }
}
