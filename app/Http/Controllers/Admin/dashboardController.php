<?php

namespace App\Http\Controllers\Admin;

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
        $topContractors = $this->getTopContractors();
        $topPropertyOwners = $this->getTopPropertyOwners();
        $activeUsersData = $this->getActiveUsersData();
        $dashboardStats = $this->getDashboardStats();

        // Get chart data for all filters
        $totalUsersChartData = $this->getTotalUsersChartData();
        $newUsersChartData = $this->getNewUsersChartData();
        $activeUsersChartData = $this->getActiveUsersChartData();
        $pendingReviewsChartData = $this->getPendingReviewsChartData();

        // New metric cards: projects, bids, revenue
        $projectsMetrics = $this->getProjectsMetrics();
        $activeBidsMetrics = $this->getActiveBidsMetrics();
        $revenueMetrics = $this->getRevenueMetrics();

        // Get breakdown data for stat cards
        $totalUsersBreakdown = $this->getTotalUsersBreakdown();
        $newUsersBreakdown = $this->getNewUsersBreakdown();
        $activeUsersBreakdown = $this->getActiveUsersBreakdownData();
        $pendingReviewsBreakdown = $this->getPendingReviewsBreakdown();

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
     * Get earnings data for AJAX request (JSON response)
     */
    public function getEarnings(\Illuminate\Http\Request $request)
    {
        $startDate = $request->input('start');
        $endDate = $request->input('end');

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Invalid date range'], 400);
        }

        $earningsData = $this->getEarningsMetricsForRange($startDate, $endDate);

        return response()->json($earningsData);
    }

    /**
     * Get top projects with most bids (limit 4)
     */
    private function getTopProjectsWithBids($limit = 4)
    {
        // Join projects, property_owners, and count bids
        $projects = DB::table('projects')
            ->select(
                'projects.project_id',
                'projects.project_title',
                // No project_image in schema, use NULL as placeholder
                DB::raw('NULL as project_image'),
                'projects.project_status',
                DB::raw("COALESCE(property_owners.first_name, '') as first_name"),
                DB::raw("COALESCE(property_owners.last_name, '') as last_name"),
                DB::raw('COUNT(bids.bid_id) as bid_count')
            )
            ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
            ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
            ->leftJoin('bids', 'projects.project_id', '=', 'bids.project_id')
            ->groupBy(
                'projects.project_id',
                'projects.project_title',
                'projects.project_status',
                'property_owners.first_name',
                'property_owners.last_name'
            )
            ->orderByDesc('bid_count')
            ->limit($limit)
            ->get();

        // Map status to label (example: 'on_bid' => 'On Bid')
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
            case 'on_bid':
                return 'On Bid';
            case 'in_progress':
                return 'In Progress';
            case 'completed':
                return 'Completed';
            default:
                return ucfirst(str_replace('_', ' ', $status));
        }
    }

    /**
     * Get projects metrics and monthly series
     */
    private function getProjectsMetrics()
    {
        $totalProjects = DB::table('projects')->count();

        // projects table has no created_at; use project_relationships.created_at instead
        $monthlyData = DB::table('project_relationships')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereRaw('YEAR(created_at) = YEAR(CURRENT_DATE)')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $monthlyArray = array_fill(0, 12, 0);
        foreach ($monthlyData as $d) {
            $monthlyArray[$d->month - 1] = $d->count;
        }

        $curMonthIndex = (int)date('n') - 1;
        $cur = $monthlyArray[$curMonthIndex];
        $prev = $curMonthIndex > 0 ? $monthlyArray[$curMonthIndex - 1] : 0;
        $pctChange = 0;
        if ($prev == 0) {
            $pctChange = $cur > 0 ? round(100.0, 2) : 0;
        } else {
            $pctChange = round((($cur - $prev) / $prev) * 100, 2);
        }

        return [
            'total' => $totalProjects,
            'months' => $months,
            'data' => $monthlyArray,
            'label' => 'Total Projects',
            'pctChange' => $pctChange,
        ];
    }

    /**
     * Get active bids metrics and monthly series
     */
    private function getActiveBidsMetrics()
    {
        $activeStatuses = ['submitted','under_review'];
        $totalActiveBids = DB::table('bids')
            ->whereIn('bid_status', $activeStatuses)
            ->count();

        $monthlyData = DB::table('bids')
            ->select(
                DB::raw('MONTH(submitted_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereIn('bid_status', $activeStatuses)
            ->whereRaw('YEAR(submitted_at) = YEAR(CURRENT_DATE)')
            ->groupBy(DB::raw('MONTH(submitted_at)'))
            ->orderBy(DB::raw('MONTH(submitted_at)'))
            ->get();

        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $monthlyArray = array_fill(0, 12, 0);
        foreach ($monthlyData as $d) {
            $monthlyArray[$d->month - 1] = $d->count;
        }

        $curMonthIndex = (int)date('n') - 1;
        $cur = $monthlyArray[$curMonthIndex];
        $prev = $curMonthIndex > 0 ? $monthlyArray[$curMonthIndex - 1] : 0;
        $pctChange = 0;
        if ($prev == 0) {
            $pctChange = $cur > 0 ? round(100.0, 2) : 0;
        } else {
            $pctChange = round((($cur - $prev) / $prev) * 100, 2);
        }

        return [
            'total' => $totalActiveBids,
            'months' => $months,
            'data' => $monthlyArray,
            'label' => 'Active Bids',
            'pctChange' => $pctChange,
        ];
    }

    /**
     * Get revenue metrics and monthly series (milestone + platform payments, approved)
     */
    private function getRevenueMetrics()
    {
        // milestone payments approved (payment_status = 'approved')
        $milestoneMonthly = DB::table('milestone_payments')
            ->select(
                DB::raw('MONTH(transaction_date) as month'),
                DB::raw('IFNULL(SUM(amount),0) as sum')
            )
            ->where('payment_status', 'approved')
            ->whereRaw('YEAR(transaction_date) = YEAR(CURRENT_DATE)')
            ->groupBy(DB::raw('MONTH(transaction_date)'))
            ->orderBy(DB::raw('MONTH(transaction_date)'))
            ->get();

        // platform payments approved (is_approved = 1)
        $platformMonthly = DB::table('platform_payments')
            ->select(
                DB::raw('MONTH(transaction_date) as month'),
                DB::raw('IFNULL(SUM(amount),0) as sum')
            )
            ->where('is_approved', 1)
            ->whereRaw('YEAR(transaction_date) = YEAR(CURRENT_DATE)')
            ->groupBy(DB::raw('MONTH(transaction_date)'))
            ->orderBy(DB::raw('MONTH(transaction_date)'))
            ->get();

        $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $monthlyArray = array_fill(0, 12, 0.0);

        foreach ($milestoneMonthly as $m) {
            $monthlyArray[$m->month - 1] += floatval($m->sum);
        }
        foreach ($platformMonthly as $p) {
            $monthlyArray[$p->month - 1] += floatval($p->sum);
        }

        $totalRevenue = array_sum($monthlyArray);

        $curMonthIndex = (int)date('n') - 1;
        $cur = $monthlyArray[$curMonthIndex];
        $prev = $curMonthIndex > 0 ? $monthlyArray[$curMonthIndex - 1] : 0;
        $pctChange = 0;
        if ($prev == 0) {
            $pctChange = $cur > 0 ? round(100.0, 2) : 0;
        } else {
            $pctChange = round((($cur - $prev) / $prev) * 100, 2);
        }

        return [
            'total' => $totalRevenue,
            'months' => $months,
            'data' => $monthlyArray,
            'label' => 'Revenue',
            'pctChange' => $pctChange,
        ];
    }

    /**
     * Get dashboard statistics (Total Users, New Users, Active Users, Pending Reviews)
     */
    private function getDashboardStats()
    {
        // Total users
        $totalUsers = DB::table('users')->count();

        // New users (created today)
        $newUsers = DB::table('users')
            ->whereDate('created_at', DB::raw('CURDATE()'))
            ->count();

        // Active users (is_active = 1)
        $activeUsers = DB::table('users')
            ->where(function ($query) {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('contractor_users')
                        ->whereColumn('contractor_users.user_id', 'users.user_id')
                        ->where('contractor_users.is_active', 1);
                })
                ->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('property_owners')
                        ->whereColumn('property_owners.user_id', 'users.user_id')
                        ->where('property_owners.is_active', 1);
                });
            })
            ->count();

        // Pending reviews (contractors with pending verification)
        $pendingReviews = DB::table('contractors')
            ->where('verification_status', 'pending')
            ->count();

        return [
            'totalUsers' => $totalUsers,
            'newUsers' => $newUsers,
            'activeUsers' => $activeUsers,
            'pendingReviews' => $pendingReviews,
        ];
    }

    /**
     * Get top contractors based on completed projects
     */
    private function getTopContractors($limit = 5)
    {
        return DB::table('contractors')
            ->select(
                'contractors.contractor_id',
                'contractors.company_name',
                'contractors.completed_projects',
                'users.profile_pic',
                'contractor_types.type_name'
            )
            ->join('users', 'contractors.user_id', '=', 'users.user_id')
            ->join('contractor_types', 'contractors.type_id', '=', 'contractor_types.type_id')
            ->orderBy('contractors.completed_projects', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top property owners based on completed projects
     */
    private function getTopPropertyOwners($limit = 5)
    {
        // Build a robust query that adapts to actual DB column names.
        // Some environments use `user_id`/`owner_id` while others use `id`.
        $propTable = 'property_owners';
        $usersTable = 'users';
        $projectsTable = 'projects';

        // Decide user PK column
        $userPk = Schema::hasColumn($usersTable, 'user_id') ? 'user_id' : 'id';

        // Decide property owner PK and FK names
        $ownerPk = Schema::hasColumn($propTable, 'owner_id') ? 'owner_id' : 'id';
        $ownerUserFk = Schema::hasColumn($propTable, 'user_id') ? 'user_id' : null;

        // Decide projects foreign key to property_owners
        $projectsOwnerFk = Schema::hasColumn($projectsTable, 'owner_id') ? 'owner_id' : null;

        $selects = [
            "$propTable.$ownerPk as owner_id",
            "$propTable.first_name",
            "$propTable.last_name",
        ];

        // profile_pic may live on users table
        if (Schema::hasColumn($usersTable, 'profile_pic') && $ownerUserFk) {
            $selects[] = "$usersTable.profile_pic";
        } else {
            // fallback: null as profile_pic
            $selects[] = DB::raw('NULL as profile_pic');
        }

        // Determine if we can count projects.project_id and how to join projects
        $hasProjectId = Schema::hasColumn($projectsTable, 'project_id');
        $countExpr = '0';

        $qb = DB::table($propTable)->select($selects);

        // join users if possible
        if ($ownerUserFk && Schema::hasColumn($usersTable, $userPk)) {
            $qb->join($usersTable, "$propTable.$ownerUserFk", '=', "$usersTable.$userPk");
        }

        if ($hasProjectId) {
            // Prefer direct owner_id on projects if available
            if (Schema::hasColumn($projectsTable, 'owner_id')) {
                $qb->leftJoin($projectsTable, "$propTable.$ownerPk", '=', "$projectsTable.owner_id");
                $countExpr = "COUNT($projectsTable.project_id)";
            } elseif (Schema::hasColumn($projectsTable, 'relationship_id') && Schema::hasColumn('project_relationships', 'rel_id') && Schema::hasColumn('project_relationships', 'owner_id')) {
                // join through project_relationships
                $qb->leftJoin('project_relationships', "project_relationships.owner_id", '=', "$propTable.$ownerPk");
                $qb->leftJoin($projectsTable, "$projectsTable.relationship_id", '=', "project_relationships.rel_id");
                $countExpr = "COUNT($projectsTable.project_id)";
            }
        }

        // Add completed_projects selection
        if ($countExpr === '0') {
            $qb->addSelect(DB::raw('0 as completed_projects'));
        } else {
            $qb->addSelect(DB::raw($countExpr . ' as completed_projects'));
        }

        $qb->groupBy("$propTable.$ownerPk", "$propTable.first_name", "$propTable.last_name");

        // include users.profile_pic in groupBy when joined
        if ($ownerUserFk && Schema::hasColumn($usersTable, $userPk) && Schema::hasColumn($usersTable, 'profile_pic')) {
            $qb->groupBy("$usersTable.profile_pic");
        }

        $qb->orderBy('completed_projects', 'desc')->limit($limit);

        return $qb->get();
    }

    /**
     * Get active users data with monthly breakdown
     */
    private function getActiveUsersData()
    {
        // Get total active users
        $totalActiveUsers = DB::table('users')
            ->where(function ($query) {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('contractor_users')
                        ->whereColumn('contractor_users.user_id', 'users.user_id')
                        ->where('contractor_users.is_active', 1);
                })
                ->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('property_owners')
                        ->whereColumn('property_owners.user_id', 'users.user_id')
                        ->where('property_owners.is_active', 1);
                });
            })
            ->count();

        // Get contractors count
        $contractorsCount = DB::table('users')
            ->where(function($query) {
                $query->where('user_type', 'contractor')
                      ->orWhere('user_type', 'both');
            })
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('contractor_users')
                    ->whereColumn('contractor_users.user_id', 'users.user_id')
                    ->where('contractor_users.is_active', 1);
            })
            ->count();

        // Get property owners count
        $propertyOwnersCount = DB::table('users')
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

        // Get monthly user registrations for the last 12 months
        $monthlyData = DB::table('users')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereRaw('YEAR(created_at) = YEAR(CURRENT_DATE)')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        // Create array with all 12 months, filling missing data with 0
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyArray = array_fill(0, 12, 0);

        foreach ($monthlyData as $data) {
            $monthlyArray[$data->month - 1] = $data->count;
        }

        return [
            'total' => $totalActiveUsers,
            'contractors' => $contractorsCount,
            'property_owners' => $propertyOwnersCount,
            'months' => $months,
            'data' => $monthlyArray,
        ];
    }

    /**
     * Get monthly total users chart data
     */
    private function getTotalUsersChartData()
    {
        $monthlyData = DB::table('users')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereRaw('YEAR(created_at) = YEAR(CURRENT_DATE)')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyArray = array_fill(0, 12, 0);

        foreach ($monthlyData as $data) {
            $monthlyArray[$data->month - 1] = $data->count;
        }

        return [
            'months' => $months,
            'data' => $monthlyArray,
            'label' => 'Total Users',
        ];
    }

    /**
     * Get monthly new users (created that month) chart data - same as total users monthly breakdown
     */
    private function getNewUsersChartData()
    {
        $monthlyData = DB::table('users')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereRaw('YEAR(created_at) = YEAR(CURRENT_DATE)')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyArray = array_fill(0, 12, 0);

        foreach ($monthlyData as $data) {
            $monthlyArray[$data->month - 1] = $data->count;
        }

        return [
            'months' => $months,
            'data' => $monthlyArray,
            'label' => 'New Users (Monthly)',
        ];
    }

    /**
     * Get monthly active users (is_active = 1) chart data
     */
    private function getActiveUsersChartData()
    {
        $monthlyData = DB::table('users')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where(function ($query) {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('contractor_users')
                        ->whereColumn('contractor_users.user_id', 'users.user_id')
                        ->where('contractor_users.is_active', 1);
                })
                ->orWhereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('property_owners')
                        ->whereColumn('property_owners.user_id', 'users.user_id')
                        ->where('property_owners.is_active', 1);
                });
            })
            ->whereRaw('YEAR(created_at) = YEAR(CURRENT_DATE)')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyArray = array_fill(0, 12, 0);

        foreach ($monthlyData as $data) {
            $monthlyArray[$data->month - 1] = $data->count;
        }

        return [
            'months' => $months,
            'data' => $monthlyArray,
            'label' => 'Active Users',
        ];
    }

    /**
     * Get monthly pending reviews (contractors pending verification) chart data
     */
    private function getPendingReviewsChartData()
    {
        $monthlyData = DB::table('contractors')
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('verification_status', 'pending')
            ->whereRaw('YEAR(created_at) = YEAR(CURRENT_DATE)')
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyArray = array_fill(0, 12, 0);

        foreach ($monthlyData as $data) {
            $monthlyArray[$data->month - 1] = $data->count;
        }

        return [
            'months' => $months,
            'data' => $monthlyArray,
            'label' => 'Pending Reviews',
        ];
    }

    /**
     * Get total users breakdown (by user type)
     */
    private function getTotalUsersBreakdown()
    {
        $totalUsers = DB::table('users')->count();

        $contractorsCount = DB::table('users')
            ->where(function($query) {
                $query->where('user_type', 'contractor')
                      ->orWhere('user_type', 'both');
            })
            ->count();

        $propertyOwnersCount = DB::table('users')
            ->where(function($query) {
                $query->where('user_type', 'property_owner')
                      ->orWhere('user_type', 'both');
            })
            ->count();

        return [
            'total' => $totalUsers,
            'contractors' => $contractorsCount,
            'property_owners' => $propertyOwnersCount,
            'type' => 'total-users',
        ];
    }

    /**
     * Get new users breakdown (created today, by type)
     */
    private function getNewUsersBreakdown()
    {
        $totalNewUsers = DB::table('users')
            ->whereDate('created_at', DB::raw('CURDATE()'))
            ->count();

        $contractorsCount = DB::table('users')
            ->whereDate('created_at', DB::raw('CURDATE()'))
            ->where(function($query) {
                $query->where('user_type', 'contractor')
                      ->orWhere('user_type', 'both');
            })
            ->count();

        $propertyOwnersCount = DB::table('users')
            ->whereDate('created_at', DB::raw('CURDATE()'))
            ->where(function($query) {
                $query->where('user_type', 'property_owner')
                      ->orWhere('user_type', 'both');
            })
            ->count();

        return [
            'total' => $totalNewUsers,
            'contractors' => $contractorsCount,
            'property_owners' => $propertyOwnersCount,
            'type' => 'new-users',
        ];
    }

    /**
     * Get active users breakdown (by type)
     */
    private function getActiveUsersBreakdownData()
    {
        $totalActiveUsers = DB::table('users')
            ->where(function ($query) {
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('contractor_users')
                        ->whereColumn('contractor_users.user_id', 'users.user_id')
                        ->where('contractor_users.is_active', 1);
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
            ->where(function($query) {
                $query->where('user_type', 'contractor')
                      ->orWhere('user_type', 'both');
            })
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('contractor_users')
                    ->whereColumn('contractor_users.user_id', 'users.user_id')
                    ->where('contractor_users.is_active', 1);
            })
            ->count();

        $propertyOwnersCount = DB::table('users')
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

        return [
            'total' => $totalActiveUsers,
            'contractors' => $contractorsCount,
            'property_owners' => $propertyOwnersCount,
            'type' => 'active-users',
        ];
    }

    /**
     * Get pending reviews breakdown
     */
    private function getPendingReviewsBreakdown()
    {
        $totalPending = DB::table('contractors')
            ->where('verification_status', 'pending')
            ->count();

        return [
            'total' => $totalPending,
            'contractors' => $totalPending,
            'property_owners' => 0,
            'type' => 'pending-reviews',
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
     * Get earnings metrics for a specific date range
     */
    private function getEarningsMetricsForRange($startDate, $endDate)
    {
        // Get daily earnings from platform_payments (approved only)
        $dailyEarnings = DB::table('platform_payments')
            ->select(
                DB::raw('DAY(transaction_date) as day'),
                DB::raw('SUM(CASE WHEN payment_for = "commission" THEN amount ELSE 0 END) as subscription_amount'),
                DB::raw('SUM(CASE WHEN payment_for = "boosted_post" THEN amount ELSE 0 END) as boost_amount'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('is_approved', 1)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy(DB::raw('DAY(transaction_date)'))
            ->orderBy(DB::raw('DAY(transaction_date)'))
            ->get();

        // Get total earnings for the period
        $totalEarnings = DB::table('platform_payments')
            ->where('is_approved', 1)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        // Calculate number of days in range
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $interval = $start->diff($end);
        $daysInRange = $interval->days + 1;

        // Create array with all days in the range
        $days = range(1, $daysInRange);
        $dailyArray = array_fill(0, $daysInRange, 0.0);

        foreach ($dailyEarnings as $earning) {
            $dailyArray[$earning->day - 1] = floatval($earning->total_amount);
        }

        // Format date range for display
        $dateRange = date('M d', strtotime($startDate)) . ' - ' . date('d Y', strtotime($endDate));

        return [
            'total' => $totalEarnings,
            'days' => $days,
            'data' => $dailyArray,
            'dateRange' => $dateRange,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
}
