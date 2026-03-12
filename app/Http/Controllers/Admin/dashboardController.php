<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\authController;

class dashboardController extends authController
{
    /**
     * Show the admin dashboard
     */
    public function index()
    {
        // Default period matches the "This Year" pill that is active on page load
        $defaultRange = $this->getRangeStartEnd('thisyear');
        $defaultStart = $defaultRange['start'];
        $defaultEnd   = $defaultRange['end'];

        $topContractors = $this->getTopContractors();
        $topPropertyOwners = $this->getTopPropertyOwners();
        $activeUsersData = $this->getActiveUsersData($defaultStart, $defaultEnd);
        $dashboardStats = $this->getDashboardStats($defaultStart, $defaultEnd);

        // Get chart data for all filters
        $totalUsersChartData = $this->getTotalUsersChartData();
        $newUsersChartData = $this->getNewUsersChartData();
        $activeUsersChartData = $this->getActiveUsersChartData();
        $pendingReviewsChartData = $this->getPendingReviewsChartData();

        // New metric cards: projects, bids, revenue
        $projectsMetrics = $this->getProjectsMetrics();
        $activeBidsMetrics = $this->getActiveBidsMetrics();
        $revenueMetrics = $this->getRevenueMetrics();

        // Get breakdown data for stat cards (all scoped to the default period)
        $totalUsersBreakdown     = $this->getTotalUsersBreakdown($defaultStart, $defaultEnd);
        $newUsersBreakdown       = $this->getNewUsersBreakdown($defaultStart, $defaultEnd);
        $activeUsersBreakdown    = $this->getActiveUsersBreakdownData($defaultStart, $defaultEnd);
        $pendingReviewsBreakdown = $this->getPendingReviewsBreakdown($defaultStart, $defaultEnd);

        // Top Projects with Bids
        $topProjects = $this->getTopProjectsWithBids();

        // Earnings from subscriptions and boosts
        $earningsMetrics = $this->getEarningsMetrics();

        return view('admin.home.dashboard', [
            'topContractors' => $topContractors,
            'topPropertyOwners' => $topPropertyOwners,
            'activeUsersData' => $activeUsersData,
            'dashboardStats' => $dashboardStats,
            'totalUsersChartData' => $totalUsersChartData,
            'newUsersChartData' => $newUsersChartData,
            'activeUsersChartData' => $activeUsersChartData,
            'pendingReviewsChartData' => $pendingReviewsChartData,
            'projectsMetrics' => $projectsMetrics,
            'activeBidsMetrics' => $activeBidsMetrics,
            'revenueMetrics' => $revenueMetrics,
            'totalUsersBreakdown' => $totalUsersBreakdown,
            'newUsersBreakdown' => $newUsersBreakdown,
            'activeUsersBreakdown' => $activeUsersBreakdown,
            'pendingReviewsBreakdown' => $pendingReviewsBreakdown,
            'topProjects' => $topProjects,
            'earningsMetrics' => $earningsMetrics,
        ]);
    }

    /**
     * Get earnings data for AJAX request.
     * Accepts either ?range=<key> (server computes dates) or ?start=Y-m-d&end=Y-m-d.
     */
    public function getEarnings(Request $request)
    {
        if ($request->filled('range')) {
            ['start' => $startDate, 'end' => $endDate] =
                $this->resolveEarningsRange($request->input('range'));
        } elseif ($request->filled('start') && $request->filled('end')) {
            $startDate = $request->input('start');
            $endDate   = $request->input('end');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)
                || $startDate > $endDate) {
                return response()->json(['error' => 'Invalid date range'], 422);
            }
        } else {
            return response()->json(['error' => 'Missing range or start/end parameters'], 400);
        }

        return response()->json($this->getEarningsForGlobalRange($startDate, $endDate));
    }

    /**
     * Map a named earnings range key to concrete start / end dates (server-side).
     */
    private function resolveEarningsRange(string $range): array
    {
        $today = new \DateTime('today');
        switch ($range) {
            case 'today':
                $start = $end = $today->format('Y-m-d');
                break;
            case 'yesterday':
                $start = $end = (clone $today)->modify('-1 day')->format('Y-m-d');
                break;
            case 'last7days':
                $start = (clone $today)->modify('-6 days')->format('Y-m-d');
                $end   = $today->format('Y-m-d');
                break;
            case 'thismonth':
                $start = (clone $today)->modify('first day of this month')->format('Y-m-d');
                $end   = (clone $today)->modify('last day of this month')->format('Y-m-d');
                break;
            case 'lastmonth':
                $start = (clone $today)->modify('first day of last month')->format('Y-m-d');
                $end   = (clone $today)->modify('last day of last month')->format('Y-m-d');
                break;
            case 'last3months':
                $start = (clone $today)->modify('first day of -2 months')->format('Y-m-d');
                $end   = $today->format('Y-m-d');
                break;
            case 'thisyear':
                $start = $today->format('Y-01-01');
                $end   = $today->format('Y-12-31');
                break;
            default:
                $start = (clone $today)->modify('first day of this month')->format('Y-m-d');
                $end   = (clone $today)->modify('last day of this month')->format('Y-m-d');
        }
        return compact('start', 'end');
    }

    /**
     * Get top projects with most bids (limit 4), optionally filtered by date range.
     */
    private function getTopProjectsWithBids($limit = 4, ?string $start = null, ?string $end = null)
    {
        $qb = DB::table('projects')
            ->select(
                'projects.project_id',
                'projects.project_title',
                DB::raw('NULL as project_image'),
                'projects.project_status',
                DB::raw("COALESCE(users.first_name, '') as first_name"),
                DB::raw("COALESCE(users.last_name, '') as last_name"),
                DB::raw('COUNT(bids.bid_id) as bid_count')
            )
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
            ->leftJoin('bids', 'projects.project_id', '=', 'bids.project_id');

        if ($start !== null && $end !== null) {
            $qb->whereBetween('project_relationships.created_at', [$start, $end . ' 23:59:59']);
        }

        $projects = $qb->groupBy(
                'projects.project_id',
                'projects.project_title',
                'projects.project_status',
                'users.first_name',
                'users.last_name'
            )
            ->orderByDesc('bid_count')
            ->limit($limit)
            ->get();

        foreach ($projects as $p) {
            $p->status_label = $this->mapProjectStatus($p->project_status);
        }
        return $projects;
    }

    /**
     * Map project status code to label
     */
    private function mapProjectStatus($status)
    {
        switch ($status) {
            case 'open':
                return 'Open';
            case 'bidding_closed':
                return 'Bidding Closed';
            case 'in_progress':
                return 'In Progress';
            case 'completed':
                return 'Completed';
            case 'terminated':
                return 'Terminated';
            case 'deleted_post':
                return 'Deleted Post';
            case 'halt':
                return 'Halt';
            case 'deleted':
                return 'Deleted';
            default:
                return ucfirst(str_replace('_', ' ', $status));
        }
    }

    /**
     * Get projects metrics and monthly series
     */
    private function getProjectsMetrics(?string $start = null, ?string $end = null)
    {
        [$start, $end] = $this->resolveRange($start, $end);

        $totalProjects = DB::table('projects')->count();

        $buckets = $this->buildMonthBuckets($start, $end);

        // projects table has no created_at; use project_relationships.created_at instead
        $monthlyData = DB::table('project_relationships')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$start, $end . ' 23:59:59'])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->get()
            ->pluck('count', 'ym');

        $months = array_values($buckets);
        $data   = [];
        foreach (array_keys($buckets) as $ym) {
            $data[] = (int) ($monthlyData[$ym] ?? 0);
        }

        $n          = count($data);
        $cur        = $n > 0 ? $data[$n - 1] : 0;
        $prev       = $n > 1 ? $data[$n - 2] : 0;
        $pctChange  = $prev == 0 ? ($cur > 0 ? 100.0 : 0.0) : round((($cur - $prev) / $prev) * 100, 2);

        return [
            'total'     => $totalProjects,
            'months'    => $months,
            'data'      => $data,
            'label'     => 'Total Projects',
            'pctChange' => $pctChange,
        ];
    }

    /**
     * Get active bids metrics and monthly series
     */
    private function getActiveBidsMetrics(?string $start = null, ?string $end = null)
    {
        [$start, $end] = $this->resolveRange($start, $end);

        $activeStatuses  = ['submitted', 'under_review'];
        $totalActiveBids = DB::table('bids')->whereIn('bid_status', $activeStatuses)->count();

        $buckets = $this->buildMonthBuckets($start, $end);

        $monthlyData = DB::table('bids')
            ->select(
                DB::raw("DATE_FORMAT(submitted_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as count')
            )
            ->whereIn('bid_status', $activeStatuses)
            ->whereBetween('submitted_at', [$start, $end . ' 23:59:59'])
            ->groupBy(DB::raw("DATE_FORMAT(submitted_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(submitted_at, '%Y-%m')"))
            ->get()
            ->pluck('count', 'ym');

        $months = array_values($buckets);
        $data   = [];
        foreach (array_keys($buckets) as $ym) {
            $data[] = (int) ($monthlyData[$ym] ?? 0);
        }

        $n         = count($data);
        $cur       = $n > 0 ? $data[$n - 1] : 0;
        $prev      = $n > 1 ? $data[$n - 2] : 0;
        $pctChange = $prev == 0 ? ($cur > 0 ? 100.0 : 0.0) : round((($cur - $prev) / $prev) * 100, 2);

        return [
            'total'     => $totalActiveBids,
            'months'    => $months,
            'data'      => $data,
            'label'     => 'Active Bids',
            'pctChange' => $pctChange,
        ];
    }

    /**
     * Get revenue metrics and monthly series (milestone + platform payments, approved)
     */
    private function getRevenueMetrics(?string $start = null, ?string $end = null)
    {
        [$start, $end] = $this->resolveRange($start, $end);
        $endWithTime   = $end . ' 23:59:59';

        $buckets = $this->buildMonthBuckets($start, $end);

        // milestone payments approved (payment_status = 'approved')
        $milestoneMonthly = DB::table('milestone_payments')
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as ym"),
                DB::raw('IFNULL(SUM(amount),0) as sum')
            )
            ->where('payment_status', 'approved')
            ->whereBetween('transaction_date', [$start, $endWithTime])
            ->groupBy(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m')"))
            ->get()
            ->pluck('sum', 'ym');

        // platform payments approved (is_approved = 1)
        $platformMonthly = DB::table('platform_payments')
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as ym"),
                DB::raw('IFNULL(SUM(amount),0) as sum')
            )
            ->where('is_approved', 1)
            ->whereBetween('transaction_date', [$start, $endWithTime])
            ->groupBy(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m')"))
            ->get()
            ->pluck('sum', 'ym');

        $months = array_values($buckets);
        $data   = [];
        foreach (array_keys($buckets) as $ym) {
            $data[] = floatval($milestoneMonthly[$ym] ?? 0) + floatval($platformMonthly[$ym] ?? 0);
        }

        $totalRevenue = array_sum($data);

        $n         = count($data);
        $cur       = $n > 0 ? $data[$n - 1] : 0;
        $prev      = $n > 1 ? $data[$n - 2] : 0;
        $pctChange = $prev == 0 ? ($cur > 0 ? 100.0 : 0.0) : round((($cur - $prev) / $prev) * 100, 2);

        return [
            'total'     => $totalRevenue,
            'months'    => $months,
            'data'      => $data,
            'label'     => 'Revenue',
            'pctChange' => $pctChange,
        ];
    }

    /**
     * Get dashboard statistics (Total Users, New Users, Active Users, Pending Reviews)
     */
    private function getDashboardStats(?string $start = null, ?string $end = null)
    {
        $hasPeriod = $start && $end;
        $endTime   = $hasPeriod ? $end . ' 23:59:59' : null;

        // Total users = cumulative count up to (and including) the period end date.
        // This reflects "how many users existed by the end of the selected window".
        $totalUsersQb = DB::table('users');
        if ($hasPeriod) {
            $totalUsersQb->where('created_at', '<=', $endTime);
        }
        $totalUsers = $totalUsersQb->count();

        // New users = registered strictly within the period window.
        $newUsersQb = DB::table('users');
        if ($hasPeriod) {
            $newUsersQb->whereBetween('created_at', [$start, $endTime]);
        } else {
            $newUsersQb->whereDate('created_at', DB::raw('CURDATE()'));
        }
        $newUsers = $newUsersQb->count();

        // Active users: account is_active = 1, cumulative up to the period end date.
        // Answers: "how many accounts have the Active status as of the end of this window?"
        $activeUsersQb = DB::table('users')
            ->where(function ($query) {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('contractors')
                        ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                        ->whereColumn('property_owners.user_id', 'users.user_id')
                        ->where('contractors.is_active', 1);
                })
                ->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('property_owners')
                        ->whereColumn('property_owners.user_id', 'users.user_id')
                        ->where('property_owners.is_active', 1);
                });
            });
        if ($hasPeriod) {
            $activeUsersQb->where('users.created_at', '<=', $endTime);
        }
        $activeUsers = $activeUsersQb->count();

        // Pending reviews: contractors still pending verification as of the period end date.
        // Cumulative: includes anyone who applied before the end date and hasn't been resolved.
        $pendingQb = DB::table('contractors')
            ->where('verification_status', 'pending');
        if ($hasPeriod) {
            $pendingQb->where('created_at', '<=', $endTime);
        }
        $pendingReviews = $pendingQb->count();

        return [
            'totalUsers'     => $totalUsers,
            'newUsers'       => $newUsers,
            'activeUsers'    => $activeUsers,
            'pendingReviews' => $pendingReviews,
        ];
    }

    /**
     * Get top contractors based on completed projects
     */
    private function getTopContractors(?string $start = null, ?string $end = null, $limit = 5)
    {
        // Initial page load (no explicit dates): order by all-time completed_projects
        if ($start === null && $end === null) {
            return DB::table('contractors')
                ->select(
                    'contractors.contractor_id',
                    'contractors.company_name',
                    'contractors.completed_projects',
                    'owner_po.profile_pic',
                    'contractor_types.type_name',
                    DB::raw('contractors.completed_projects as period_count')
                )
                ->join('property_owners as owner_po', 'contractors.owner_id', '=', 'owner_po.owner_id')
                ->join('users', 'owner_po.user_id', '=', 'users.user_id')
                ->join('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
                ->orderByDesc('contractors.completed_projects')
                ->limit($limit)
                ->get();
        }

        // Date-filtered: count projects created in the period assigned to each contractor
        $periodCounts = DB::table('projects')
            ->join('project_relationships', 'project_relationships.rel_id', '=', 'projects.relationship_id')
            ->select(
                'project_relationships.selected_contractor_id',
                DB::raw('COUNT(projects.project_id) as period_count')
            )
            ->whereNotNull('project_relationships.selected_contractor_id')
            ->whereBetween('project_relationships.created_at', [$start, $end . ' 23:59:59'])
            ->groupBy('project_relationships.selected_contractor_id');

        return DB::table('contractors')
            ->select(
                'contractors.contractor_id',
                'contractors.company_name',
                'contractors.completed_projects',
                'owner_po.profile_pic',
                'contractor_types.type_name',
                DB::raw('IFNULL(pc.period_count, 0) as period_count')
            )
            ->join('property_owners as owner_po', 'contractors.owner_id', '=', 'owner_po.owner_id')
            ->join('users', 'owner_po.user_id', '=', 'users.user_id')
            ->join('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->leftJoinSub($periodCounts, 'pc', 'pc.selected_contractor_id', '=', 'contractors.contractor_id')
            ->orderByDesc('pc.period_count')
            ->orderByDesc('contractors.completed_projects')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top property owners based on projects in the selected period.
     */
    private function getTopPropertyOwners(?string $start = null, ?string $end = null, $limit = 5)
    {
        // Count projects per owner in the period (via project_relationships)
        $periodCounts = DB::table('project_relationships')
            ->select('owner_id', DB::raw('COUNT(*) as period_projects'));

        if ($start !== null && $end !== null) {
            $periodCounts->whereBetween('created_at', [$start, $end . ' 23:59:59']);
        }

        $periodCounts->groupBy('owner_id');

        $selects = [
            'property_owners.owner_id',
            'users.first_name',
            'users.last_name',
            DB::raw('IFNULL(pc.period_projects, 0) as completed_projects'),
            'property_owners.profile_pic',
        ];

        $qb = DB::table('property_owners')
            ->leftJoinSub($periodCounts, 'pc', 'pc.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->select($selects);

        // No outer GROUP BY needed: the subquery already produces one row per owner_id
        // and both joins are 1-to-1, so ONLY_FULL_GROUP_BY is not triggered.
        return $qb->orderBy('completed_projects', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get active users data with monthly breakdown
     */
    private function getActiveUsersData(?string $start = null, ?string $end = null)
    {
        [$start, $end] = $this->resolveRange($start, $end);

        // Cumulative active-user counts: accounts with is_active=1 registered up to the period end date
        $endTime = $end . ' 23:59:59';

        $totalActiveUsers = DB::table('users')
            ->where('users.created_at', '<=', $endTime)
            ->where(function ($query) {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('contractors')
                        ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                        ->whereColumn('property_owners.user_id', 'users.user_id')
                        ->where('contractors.is_active', 1);
                })
                ->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('property_owners')
                        ->whereColumn('property_owners.user_id', 'users.user_id')
                        ->where('property_owners.is_active', 1);
                });
            })
            ->count();

        $contractorsCount = DB::table('users')
            ->where('users.created_at', '<=', $endTime)
            ->where(function($query) {
                $query->where('user_type', 'contractor')
                      ->orWhere('user_type', 'both');
            })
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('contractors')
                    ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                    ->whereColumn('property_owners.user_id', 'users.user_id')
                    ->where('contractors.is_active', 1);
            })
            ->count();

        $propertyOwnersCount = DB::table('users')
            ->where('users.created_at', '<=', $endTime)
            ->where(function($query) {
                $query->where('user_type', 'property_owner')
                      ->orWhere('user_type', 'both');
            })
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('property_owners')
                    ->whereColumn('property_owners.user_id', 'users.user_id')
                    ->where('property_owners.is_active', 1);
            })
            ->count();

        // Monthly user registrations for the selected period
        $buckets = $this->buildMonthBuckets($start, $end);

        $monthlyData = DB::table('users')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$start, $end . ' 23:59:59'])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->get()
            ->pluck('count', 'ym');

        $months = array_values($buckets);
        $data   = [];
        foreach (array_keys($buckets) as $ym) {
            $data[] = (int) ($monthlyData[$ym] ?? 0);
        }

        return [
            'total'           => $totalActiveUsers,
            'contractors'     => $contractorsCount,
            'property_owners' => $propertyOwnersCount,
            'months'          => $months,
            'data'            => $data,
        ];
    }

    /**
     * Get monthly total users chart data
     */
    private function getTotalUsersChartData(?string $start = null, ?string $end = null)
    {
        [$start, $end] = $this->resolveRange($start, $end);
        $buckets = $this->buildMonthBuckets($start, $end);

        $monthlyData = DB::table('users')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$start, $end . ' 23:59:59'])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->get()
            ->pluck('count', 'ym');

        $months = array_values($buckets);
        $data   = [];
        foreach (array_keys($buckets) as $ym) {
            $data[] = (int) ($monthlyData[$ym] ?? 0);
        }

        return [
            'months' => $months,
            'data'   => $data,
            'label'  => 'Total Users',
        ];
    }

    /**
     * Get monthly new user registrations chart data for the selected period
     */
    private function getNewUsersChartData(?string $start = null, ?string $end = null)
    {
        [$start, $end] = $this->resolveRange($start, $end);
        $buckets = $this->buildMonthBuckets($start, $end);

        $monthlyData = DB::table('users')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$start, $end . ' 23:59:59'])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->get()
            ->pluck('count', 'ym');

        $months = array_values($buckets);
        $data   = [];
        foreach (array_keys($buckets) as $ym) {
            $data[] = (int) ($monthlyData[$ym] ?? 0);
        }

        return [
            'months' => $months,
            'data'   => $data,
            'label'  => 'New Users (Monthly)',
        ];
    }

    /**
     * Get monthly active users (is_active = 1) chart data
     */
    private function getActiveUsersChartData(?string $start = null, ?string $end = null)
    {
        [$start, $end] = $this->resolveRange($start, $end);
        $buckets = $this->buildMonthBuckets($start, $end);

        $monthlyData = DB::table('users')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as count')
            )
            ->where(function ($query) {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('contractors')
                        ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                        ->whereColumn('property_owners.user_id', 'users.user_id')
                        ->where('contractors.is_active', 1);
                })
                ->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('property_owners')
                        ->whereColumn('property_owners.user_id', 'users.user_id')
                        ->where('property_owners.is_active', 1);
                });
            })
            ->whereBetween('created_at', [$start, $end . ' 23:59:59'])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->get()
            ->pluck('count', 'ym');

        $months = array_values($buckets);
        $data   = [];
        foreach (array_keys($buckets) as $ym) {
            $data[] = (int) ($monthlyData[$ym] ?? 0);
        }

        return [
            'months' => $months,
            'data'   => $data,
            'label'  => 'Active Users',
        ];
    }

    /**
     * Get monthly pending reviews (contractors pending verification) chart data
     */
    private function getPendingReviewsChartData(?string $start = null, ?string $end = null)
    {
        [$start, $end] = $this->resolveRange($start, $end);
        $buckets = $this->buildMonthBuckets($start, $end);

        $monthlyData = DB::table('contractors')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as count')
            )
            ->where('verification_status', 'pending')
            ->whereBetween('created_at', [$start, $end . ' 23:59:59'])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->get()
            ->pluck('count', 'ym');

        $months = array_values($buckets);
        $data   = [];
        foreach (array_keys($buckets) as $ym) {
            $data[] = (int) ($monthlyData[$ym] ?? 0);
        }

        return [
            'months' => $months,
            'data'   => $data,
            'label'  => 'Pending Reviews',
        ];
    }

    /**
     * Get total users breakdown (by user type)
     */
    private function getTotalUsersBreakdown(?string $start = null, ?string $end = null)
    {
        // Cumulative: all users registered up to and including the period end date
        $applyCumulative = function ($qb) use ($end) {
            if ($end) {
                $qb->where('created_at', '<=', $end . ' 23:59:59');
            }
        };

        $totalQb = DB::table('users');
        $applyCumulative($totalQb);
        $totalUsers = $totalQb->count();

        $contractorsQb = DB::table('users')->where(function ($q) {
            $q->where('user_type', 'contractor')->orWhere('user_type', 'both');
        });
        $applyCumulative($contractorsQb);
        $contractorsCount = $contractorsQb->count();

        $ownersQb = DB::table('users')->where(function ($q) {
            $q->where('user_type', 'property_owner')->orWhere('user_type', 'both');
        });
        $applyCumulative($ownersQb);
        $propertyOwnersCount = $ownersQb->count();

        return [
            'total'           => $totalUsers,
            'contractors'     => $contractorsCount,
            'property_owners' => $propertyOwnersCount,
            'type'            => 'total-users',
        ];
    }

    /**
     * Get new users breakdown, scoped to the selected period (or today if no period given).
     */
    private function getNewUsersBreakdown(?string $start = null, ?string $end = null)
    {
        $applyPeriod = function ($qb) use ($start, $end) {
            if ($start && $end) {
                $qb->whereBetween('created_at', [$start, $end . ' 23:59:59']);
            } else {
                $qb->whereDate('created_at', DB::raw('CURDATE()'));
            }
        };

        $totalQb = DB::table('users');
        $applyPeriod($totalQb);
        $totalNewUsers = $totalQb->count();

        $contractorsQb = DB::table('users')->where(function ($q) {
            $q->where('user_type', 'contractor')->orWhere('user_type', 'both');
        });
        $applyPeriod($contractorsQb);
        $contractorsCount = $contractorsQb->count();

        $ownersQb = DB::table('users')->where(function ($q) {
            $q->where('user_type', 'property_owner')->orWhere('user_type', 'both');
        });
        $applyPeriod($ownersQb);
        $propertyOwnersCount = $ownersQb->count();

        return [
            'total'           => $totalNewUsers,
            'contractors'     => $contractorsCount,
            'property_owners' => $propertyOwnersCount,
            'type'            => 'new-users',
        ];
    }

    /**
     * Get active users breakdown (by type), scoped to the selected period.
     * Active = registered in period AND currently has is_active = 1 in contractor_users / property_owners.
     */
    private function getActiveUsersBreakdownData(?string $start = null, ?string $end = null)
    {
        $hasPeriod = $start && $end;
        $endTime   = $hasPeriod ? $end . ' 23:59:59' : null;

        // Cumulative: all accounts with is_active=1 that existed by the end of the period
        $baseQuery = function () use ($hasPeriod, $endTime) {
            $qb = DB::table('users')
                ->where(function ($q) {
                    $q->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('contractors')
                            ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                            ->whereColumn('property_owners.user_id', 'users.user_id')
                            ->where('contractors.is_active', 1);
                    })
                    ->orWhereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('property_owners')
                            ->whereColumn('property_owners.user_id', 'users.user_id')
                            ->where('property_owners.is_active', 1);
                    });
                });
            if ($hasPeriod) {
                $qb->where('users.created_at', '<=', $endTime);
            }
            return $qb;
        };

        $totalActiveUsers = (clone $baseQuery())->count();

        $contractorsCount = (clone $baseQuery())
            ->where(function ($q) {
                $q->where('user_type', 'contractor')->orWhere('user_type', 'both');
            })
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('contractors')
                    ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                    ->whereColumn('property_owners.user_id', 'users.user_id')
                    ->where('contractors.is_active', 1);
            })
            ->count();

        $propertyOwnersCount = (clone $baseQuery())
            ->where(function ($q) {
                $q->where('user_type', 'property_owner')->orWhere('user_type', 'both');
            })
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('property_owners')
                    ->whereColumn('property_owners.user_id', 'users.user_id')
                    ->where('property_owners.is_active', 1);
            })
            ->count();

        return [
            'total'           => $totalActiveUsers,
            'contractors'     => $contractorsCount,
            'property_owners' => $propertyOwnersCount,
            'type'            => 'active-users',
        ];
    }

    /**
     * Get pending reviews breakdown, scoped to the selected period.
     */
    private function getPendingReviewsBreakdown(?string $start = null, ?string $end = null)
    {
        // Cumulative: all contractors still in 'pending' state who applied up to the period end date
        $qb = DB::table('contractors')->where('verification_status', 'pending');
        if ($end) {
            $qb->where('created_at', '<=', $end . ' 23:59:59');
        }
        $totalPending = $qb->count();

        return [
            'total'           => $totalPending,
            'contractors'     => $totalPending,
            'property_owners' => 0,
            'type'            => 'pending-reviews',
        ];
    }

    /**
     * Get earnings metrics from subscriptions and boosts (platform_payments)
     * Returns daily data for current month with date range selector
     */
    private function getEarningsMetrics()
    {
        // Default: show current month
        $startDate = date('Y-m-01'); // First day of current month
        $endDate = date('Y-m-t');    // Last day of current month

        return $this->getEarningsMetricsForRange($startDate, $endDate);
    }

    /**
     * Get earnings metrics for a specific date range (daily granularity).
     * Uses DATE() keying to avoid the DAY()-across-month-boundary bug.
     */
    private function getEarningsMetricsForRange($startDate, $endDate)
    {
        // Key earnings by calendar date (not just day-of-month)
        $earningsByDate = DB::table('platform_payments')
            ->select(
                DB::raw('DATE(transaction_date) as date_key'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('is_approved', 1)
            ->whereBetween('transaction_date', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->orderBy(DB::raw('DATE(transaction_date)'))
            ->get()
            ->pluck('total_amount', 'date_key');

        $totalEarnings = DB::table('platform_payments')
            ->where('is_approved', 1)
            ->whereBetween('transaction_date', [$startDate, $endDate . ' 23:59:59'])
            ->sum('amount');

        $startDt    = new \DateTime($startDate);
        $endDt      = new \DateTime($endDate);
        $spanMonths = $startDt->format('Y-m') !== $endDt->format('Y-m');

        $days       = [];
        $dailyArray = [];
        $current    = clone $startDt;
        while ($current <= $endDt) {
            $dateStr      = $current->format('Y-m-d');
            // Same month → plain day number; cross-month → "M d" string
            $days[]       = $spanMonths ? $current->format('M d') : (int) $current->format('j');
            $dailyArray[] = floatval($earningsByDate[$dateStr] ?? 0);
            $current->modify('+1 day');
        }

        $dateRange = date('M d, Y', strtotime($startDate)) . ' – ' . date('M d, Y', strtotime($endDate));

        return [
            'total'     => floatval($totalEarnings),
            'days'      => $days,
            'data'      => $dailyArray,
            'dateRange' => $dateRange,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'format'    => $spanMonths ? 'dated' : 'daily',
        ];
    }

    // =========================================================================
    // GLOBAL DATE FILTER HELPERS & AJAX ENDPOINT
    // =========================================================================

    /**
     * Resolve optional start/end to the current year when not provided.
     */
    private function resolveRange(?string $start, ?string $end): array
    {
        if ($start === null || $end === null) {
            $year = (int) date('Y');
            return ["{$year}-01-01", "{$year}-12-31"];
        }
        return [$start, $end];
    }

    /**
     * Build an ordered map of 'YYYY-MM' => 'Mon YYYY' for every calendar month
     * that exists between $start and $end (inclusive).
     */
    private function buildMonthBuckets(string $start, string $end): array
    {
        $buckets = [];
        $cur     = new \DateTime(date('Y-m-01', strtotime($start)));
        $last    = new \DateTime(date('Y-m-01', strtotime($end)));
        while ($cur <= $last) {
            $buckets[$cur->format('Y-m')] = $cur->format('M Y');
            $cur->modify('+1 month');
        }
        return $buckets;
    }

    /**
     * Convert a named range string into concrete start / end dates and a display label.
     * Accepted ranges: thisyear | lastyear | last6months | last3months
     */
    private function getRangeStartEnd(string $range): array
    {
        $today = new \DateTime('today');
        switch ($range) {
            case 'last3months':
                $start = (clone $today)->modify('first day of -2 months')->format('Y-m-d');
                $end   = $today->format('Y-m-d');
                $label = 'Last 3 Months';
                break;
            case 'last6months':
                $start = (clone $today)->modify('first day of -5 months')->format('Y-m-d');
                $end   = $today->format('Y-m-d');
                $label = 'Last 6 Months';
                break;
            case 'lastyear':
                $year  = ((int) $today->format('Y')) - 1;
                $start = "{$year}-01-01";
                $end   = "{$year}-12-31";
                $label = "Year {$year}";
                break;
            case 'thisyear':
            default:
                $year  = (int) $today->format('Y');
                $start = "{$year}-01-01";
                $end   = "{$year}-12-31";
                $label = "Year {$year}";
                break;
        }
        return ['start' => $start, 'end' => $end, 'label' => $label];
    }

    /**
     * AJAX endpoint — return all dashboard chart data for the requested date range.
     * Route: GET /admin/dashboard/data?range=thisyear|lastyear|last6months|last3months
     */
    public function getDashboardData(Request $request)
    {
        // Custom date range (?start and ?end) takes precedence over named ?range
        if ($request->filled('start') && $request->filled('end')) {
            $start = $request->input('start');
            $end   = $request->input('end');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)
                || $start > $end) {
                return response()->json(['error' => 'Invalid date range'], 422);
            }
            $label = date('M d, Y', strtotime($start)) . ' – ' . date('M d, Y', strtotime($end));
        } else {
            $range   = $request->input('range', 'thisyear');
            $allowed = ['thisyear', 'lastyear', 'last6months', 'last3months'];
            if (!in_array($range, $allowed)) {
                $range = 'thisyear';
            }
            $rangeInfo = $this->getRangeStartEnd($range);
            $start     = $rangeInfo['start'];
            $end       = $rangeInfo['end'];
            $label     = $rangeInfo['label'];
        }

        return response()->json([
            'projectsMetrics'         => $this->getProjectsMetrics($start, $end),
            'activeBidsMetrics'       => $this->getActiveBidsMetrics($start, $end),
            'revenueMetrics'          => $this->getRevenueMetrics($start, $end),
            'activeUsersData'         => $this->getActiveUsersData($start, $end),
            'totalUsersChartData'     => $this->getTotalUsersChartData($start, $end),
            'newUsersChartData'       => $this->getNewUsersChartData($start, $end),
            'activeUsersChartData'    => $this->getActiveUsersChartData($start, $end),
            'pendingReviewsChartData' => $this->getPendingReviewsChartData($start, $end),
            'dashboardStats'          => $this->getDashboardStats($start, $end),
            'totalUsersBreakdown'     => $this->getTotalUsersBreakdown($start, $end),
            'newUsersBreakdown'       => $this->getNewUsersBreakdown($start, $end),
            'activeUsersBreakdown'    => $this->getActiveUsersBreakdownData($start, $end),
            'pendingReviewsBreakdown' => $this->getPendingReviewsBreakdown($start, $end),
            'topContractors'          => $this->getTopContractors($start, $end)->toArray(),
            'topPropertyOwners'       => $this->getTopPropertyOwners($start, $end)->toArray(),
            'topProjects'             => $this->getTopProjectsWithBids(4, $start, $end)->toArray(),
            'earningsMetrics'         => $this->getEarningsForGlobalRange($start, $end),
            'rangeLabel'              => $label,
        ]);
    }

    /**
     * Earnings aggregated for the global date filter range.
     * ≤ 31-day spans use daily grouping; longer spans use monthly grouping.
     */
    private function getEarningsForGlobalRange(string $start, string $end): array
    {
        $dayCount = (new \DateTime($start))->diff(new \DateTime($end))->days + 1;

        if ($dayCount <= 31) {
            return $this->getEarningsMetricsForRange($start, $end);
        }

        // Monthly aggregation for multi-month ranges
        $buckets         = $this->buildMonthBuckets($start, $end);
        $monthlyEarnings = DB::table('platform_payments')
            ->select(
                DB::raw("DATE_FORMAT(transaction_date, '%Y-%m') as ym"),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('is_approved', 1)
            ->whereBetween('transaction_date', [$start, $end . ' 23:59:59'])
            ->groupBy(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(transaction_date, '%Y-%m')"))
            ->get()
            ->pluck('total_amount', 'ym');

        $totalEarnings = DB::table('platform_payments')
            ->where('is_approved', 1)
            ->whereBetween('transaction_date', [$start, $end . ' 23:59:59'])
            ->sum('amount');

        $labels = [];
        $data   = [];
        foreach ($buckets as $ym => $label) {
            $labels[] = $label;
            $data[]   = floatval($monthlyEarnings[$ym] ?? 0);
        }

        $dateRange = date('M d', strtotime($start)) . ' – ' . date('d Y', strtotime($end));

        return [
            'total'     => floatval($totalEarnings),
            'days'      => $labels,
            'data'      => $data,
            'dateRange' => $dateRange,
            'startDate' => $start,
            'endDate'   => $end,
            'format'    => 'monthly',
        ];
    }
}
