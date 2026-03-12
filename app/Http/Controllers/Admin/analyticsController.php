<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\authController;

class analyticsController extends authController
{
    /**
     * Show the unified project analytics page (merged projectAnalytics + projectPerformance)
     */
    public function analytics()
    {
        $projectsAnalytics      = $this->getProjectsAnalytics();
        $projectsTimeline       = $this->getProjectsTimeline();
        $projectSuccessRate     = $this->getProjectSuccessRate();
        $projectPerformance     = $this->getProjectPerformanceMetrics();   // NEW
        $topContractors         = $this->getTopContractors();              // NEW
        $bidMetrics             = $this->getBidMetrics();                  // NEW

        return view('admin.home.projectAnalytics', [
            'projectsAnalytics'   => $projectsAnalytics,
            'projectsTimeline'    => $projectsTimeline,
            'projectSuccessRate'  => $projectSuccessRate,
            'projectPerformance'  => $projectPerformance,
            'topContractors'      => $topContractors,
            'bidMetrics'          => $bidMetrics,
        ]);
    }

    // =============================================
    // EXISTING METHODS (unchanged)
    // =============================================

    public function subscriptionAnalytics(\Illuminate\Http\Request $request)
    {
        $subscriptionMetrics = $this->getSubscriptionMetrics();
        $subscriptionTiers   = $this->getSubscriptionTiers();
        $subscriptionRevenue = $this->getSubscriptionRevenue();
        $subscribers         = $this->getSubscribers($request);

        return view('admin.home.subscriptionAnalytics', [
            'subscriptionMetrics' => $subscriptionMetrics,
            'subscriptionTiers'   => $subscriptionTiers,
            'subscriptionRevenue' => $subscriptionRevenue,
            'subscribers'         => $subscribers,
            'filters'             => [
                'search'  => $request->input('search', ''),
                'plan'    => $request->input('plan', ''),
                'status'  => $request->input('status', ''),
                'sort'    => $request->input('sort', 'newest'),
            ],
        ]);
    }

    private function applyTierCondition($query, $tier)
    {
        switch ($tier) {
            case 'gold':
                $query->where('platform_payments.amount', '>=', 2000);
                break;
            case 'silver':
                $query->whereBetween('platform_payments.amount', [1000, 1999]);
                break;
            default:
                $query->where('platform_payments.amount', '<', 1000);
                break;
        }
        return $query;
    }

   // ── Revenue chart data (per tier, current vs previous year) ──────────────
   private function getSubscriptionRevenue(string $tier = 'all'): array
    {
        $currentYear      = (int) date('Y');
        $previousYear     = $currentYear - 1;
        $months           = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $currentYearData  = array_fill(0, 12, 0.0);
        $previousYearData = array_fill(0, 12, 0.0);

        $base = fn() => DB::table('platform_payments as pp')
            ->join('subscription_plans as sp', 'sp.id', '=', 'pp.subscriptionPlanId')
            ->where('sp.for_contractor', 1)
            ->where('sp.plan_key', '!=', 'boost')
            ->where('pp.is_approved',  1)
            ->where('pp.is_cancelled', 0);

        $cur  = $base()->select(
                    DB::raw('MONTH(pp.transaction_date) as m'),
                    DB::raw('IFNULL(SUM(pp.amount), 0) as sum')
                )->whereYear('pp.transaction_date', $currentYear);

        $prev = $base()->select(
                    DB::raw('MONTH(pp.transaction_date) as m'),
                    DB::raw('IFNULL(SUM(pp.amount), 0) as sum')
                )->whereYear('pp.transaction_date', $previousYear);

        // Use plan_key for precision, not amount ranges
        if ($tier !== 'all') {
            $cur->where('sp.plan_key',  $tier);
            $prev->where('sp.plan_key', $tier);
        }

        foreach ($cur->groupByRaw('MONTH(pp.transaction_date)')
                     ->orderByRaw('MONTH(pp.transaction_date)')
                     ->get() as $r) {
            $currentYearData[(int)$r->m - 1] = (float) $r->sum;
        }

        foreach ($prev->groupByRaw('MONTH(pp.transaction_date)')
                      ->orderByRaw('MONTH(pp.transaction_date)')
                      ->get() as $r) {
            $previousYearData[(int)$r->m - 1] = (float) $r->sum;
        }

        return [
            'tier'             => $tier,
            'months'           => $months,
            // BUG 2 FIX: key names now match what JS expects
            'currentYearData'  => $currentYearData,
            'previousYearData' => $previousYearData,
            'dateRange'        => 'Jan – ' . $months[(int)date('n') - 1] . ' ' . $currentYear,
            'currentYear'      => $currentYear,
            'previousYear'     => $previousYear,
        ];
    }

    // ── AJAX endpoint for revenue chart tier switching ────────────────────────
    public function subscriptionRevenue(\Illuminate\Http\Request $request)
    {
        $tier = strtolower($request->input('tier', 'gold'));
        if (!in_array($tier, ['gold', 'silver', 'bronze'])) {
            return response()->json(['error' => 'Invalid tier'], 400);
        }
        return response()->json($this->getSubscriptionRevenue($tier));
    }

    public function getSubscribersJson(\Illuminate\Http\Request $request)
    {
        $paginator = $this->getSubscribers($request);

        $items = collect($paginator->items())->map(function ($s) {
            $expCar = $s->expiration_date ? \Carbon\Carbon::parse($s->expiration_date) : null;
            $subCar = \Carbon\Carbon::parse($s->transaction_date);

            return [
                'platform_payment_id'  => $s->platform_payment_id,
                'subscriber_name'      => $s->subscriber_name,
                'rep_name'             => $s->rep_name,
                'subscriber_email'     => $s->subscriber_email,
                'subscriber_type'      => $s->subscriber_type,
                'avatar'               => $s->avatar ? asset('storage/' . $s->avatar) : null,
                'initials'             => $s->initials,
                'plan_key'             => $s->plan_key,
                'plan_name'            => $s->plan_name,
                'billing_cycle'        => $s->billing_cycle,
                'amount_fmt'           => '₱' . number_format($s->amount, 2),
                'payment_type'         => $s->payment_type,
                'subscription_status'  => $s->subscription_status,
                'deactivation_reason'  => $s->deactivation_reason,
                'transaction_date_fmt' => $subCar->format('M j, Y'),
                'transaction_date_rel' => $subCar->diffForHumans(),
                'expiration_fmt'       => $expCar ? $expCar->format('M j, Y') : null,
                'expiration_rel'       => $expCar
                    ? ($expCar->isPast()
                        ? 'Expired ' . $expCar->diffForHumans()
                        : 'Expires ' . $expCar->diffForHumans())
                    : null,
                'expiration_past'      => $expCar ? $expCar->isPast() : false,
                'expiring_soon'        => $expCar
                    && !$expCar->isPast()
                    && $expCar->diffInDays(now()) <= 7,
                'transaction_number'   => $s->transaction_number,
            ];
        });

        return response()->json([
            'data'         => $items,
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'from'         => $paginator->firstItem() ?? 0,
            'to'           => $paginator->lastItem()  ?? 0,
        ]);
    }

    // ── Tier bar chart counts ─────────────────────────────────────────────────
     private function getSubscriptionTiers(): array
    {
        $counts = DB::table('platform_payments as pp')
            ->join('subscription_plans as sp', 'sp.id', '=', 'pp.subscriptionPlanId')
            ->where('pp.is_approved',  1)
            ->where('pp.is_cancelled', 0)
            ->whereIn('sp.plan_key', ['gold', 'silver', 'bronze'])
            ->select('sp.plan_key', DB::raw('COUNT(*) as cnt'))
            ->groupBy('sp.plan_key')
            ->pluck('cnt', 'plan_key');

        $gold   = (int)($counts['gold']   ?? 0);
        $silver = (int)($counts['silver'] ?? 0);
        $bronze = (int)($counts['bronze'] ?? 0);

        return [
            'tiers' => [
                ['name' => 'Gold Tier',   'label' => 'Gold',   'count' => $gold,   'color' => '#fbbf24', 'gradient' => 'linear-gradient(180deg,#fde047 0%,#fbbf24 100%)'],
                ['name' => 'Silver Tier', 'label' => 'Silver', 'count' => $silver, 'color' => '#60a5fa', 'gradient' => 'linear-gradient(180deg,#93c5fd 0%,#60a5fa 100%)'],
                ['name' => 'Bronze Tier', 'label' => 'Bronze', 'count' => $bronze, 'color' => '#fb923c', 'gradient' => 'linear-gradient(180deg,#fdba74 0%,#fb923c 100%)'],
            ],
            'total'    => $gold + $silver + $bronze,
            'maxCount' => max($gold, $silver, $bronze, 1),
        ];
    }

    // ── Hero KPI metrics ──────────────────────────────────────────────────────
    private function getSubscriptionMetrics(): array
    {
        $base = fn() => DB::table('platform_payments as pp')
            ->join('subscription_plans as sp', 'sp.id', '=', 'pp.subscriptionPlanId')
            ->where('sp.for_contractor', 1)
            ->where('sp.plan_key', '!=', 'boost')
            ->where('pp.is_approved',  1)
            ->where('pp.is_cancelled', 0);

        $total   = $base()->count();
        $revenue = (float) $base()->sum('pp.amount');

        $active  = $base()->where(fn($q) =>
                       $q->whereNull('pp.expiration_date')
                         ->orWhereRaw('pp.expiration_date >= NOW()')
                   )->count();

        $expiring = $base()
            ->whereNotNull('pp.expiration_date')
            ->whereRaw('pp.expiration_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)')
            ->count();

        $expired  = $base()
            ->whereNotNull('pp.expiration_date')
            ->whereRaw('pp.expiration_date < NOW()')
            ->count();

        // BUG 3 FIX: freeze into one Carbon object; no accidental double-mutation
        $prevMonth    = now()->subMonthNoOverflow();
        $thisMonthNew = $base()
            ->whereYear('pp.transaction_date',  now()->year)
            ->whereMonth('pp.transaction_date', now()->month)
            ->count();
        $lastMonthNew = $base()
            ->whereYear('pp.transaction_date',  $prevMonth->year)
            ->whereMonth('pp.transaction_date', $prevMonth->month)
            ->count();

        $momGrowth = $lastMonthNew > 0
            ? round((($thisMonthNew - $lastMonthNew) / $lastMonthNew) * 100, 1)
            : ($thisMonthNew > 0 ? 100.0 : 0.0);

        // BUG 3 FIX: single clean return, no duplicate keys
        return [
            'total'      => $total,
            'active'     => $active,
            'revenue'    => $revenue,
            'expiring'   => $expiring,
            'expired'    => $expired,
            'this_month' => $thisMonthNew,
            'mom_growth' => $momGrowth,
        ];
    }

    // ── NEW: Subscriber list with search, filter, sort, pagination ────────────
    private function getSubscribers(\Illuminate\Http\Request $request)
    {
        $search = trim($request->input('search', ''));
        $plan   = $request->input('plan',   '');
        $status = $request->input('status', '');
        $sort   = $request->input('sort',   'newest');

        // Pre-join subquery: get owner name for each contractor via property_owners→users
        $cuMin = DB::table('contractor_staff')
            ->select('contractor_id', DB::raw('MIN(staff_id) as min_cu_id'))
            ->where('is_active', 1)
            ->where('company_role', 'representative')
            ->groupBy('contractor_id');

        $query = DB::table('platform_payments as pp')
            ->join('subscription_plans as sp', 'sp.id', '=', 'pp.subscriptionPlanId')
            // Contractor chain
            ->leftJoin('contractors as c',      'c.contractor_id',     '=', 'pp.contractor_id')
            ->leftJoin('property_owners as c_po', 'c.owner_id', '=', 'c_po.owner_id')
            ->leftJoin('users as c_u', 'c_po.user_id', '=', 'c_u.user_id')
            ->leftJoinSub($cuMin, 'cu_min',    'cu_min.contractor_id','=', 'c.contractor_id')
            ->leftJoin('contractor_staff as cs','cs.staff_id','=', 'cu_min.min_cu_id')
            ->leftJoin('property_owners as rep_po', 'cs.owner_id', '=', 'rep_po.owner_id')
            ->leftJoin('users as rep_u', 'rep_po.user_id', '=', 'rep_u.user_id')
            // Property owner chain
            ->leftJoin('property_owners as po', 'po.owner_id',         '=', 'pp.owner_id')
            ->leftJoin('users as u',            'u.user_id',           '=', 'po.user_id')
            ->select(
                'pp.platform_payment_id',
                'pp.amount',
                'pp.transaction_date',
                'pp.transaction_number',
                'pp.expiration_date',
                'pp.is_approved',
                'pp.is_cancelled',
                'pp.payment_type',
                'pp.deactivation_reason',
                'sp.plan_key',
                'sp.name as plan_name',
                'sp.billing_cycle',
                'sp.for_contractor',
                DB::raw("CASE
                    WHEN pp.contractor_id IS NOT NULL THEN c.company_name
                    WHEN pp.owner_id      IS NOT NULL THEN CONCAT(u.first_name, ' ', u.last_name)
                    ELSE 'Unknown'
                END as subscriber_name"),
                DB::raw("CASE
                    WHEN pp.contractor_id IS NOT NULL
                        THEN CONCAT(rep_u.first_name, ' ', rep_u.last_name)
                    ELSE NULL
                END as rep_name"),
                DB::raw("CASE
                    WHEN pp.contractor_id IS NOT NULL THEN c.company_email
                    WHEN pp.owner_id      IS NOT NULL THEN u.email
                    ELSE NULL
                END as subscriber_email"),
                DB::raw("CASE
                    WHEN pp.contractor_id IS NOT NULL THEN 'Contractor'
                    WHEN pp.owner_id      IS NOT NULL THEN 'Property Owner'
                    ELSE 'Unknown'
                END as subscriber_type"),
                DB::raw("CASE
                    WHEN pp.contractor_id IS NOT NULL THEN c.company_logo
                    WHEN pp.owner_id      IS NOT NULL THEN po.profile_pic
                    ELSE NULL
                END as avatar"),
                DB::raw("UPPER(LEFT(CASE
                    WHEN pp.contractor_id IS NOT NULL THEN c.company_name
                    WHEN pp.owner_id      IS NOT NULL THEN CONCAT(u.first_name,' ',u.last_name)
                    ELSE '??'
                END, 2)) as initials"),
                DB::raw("CASE
                    WHEN pp.is_cancelled = 1                   THEN 'cancelled'
                    WHEN pp.is_approved  = 0                   THEN 'pending'
                    WHEN pp.expiration_date IS NULL             THEN 'active'
                    WHEN pp.expiration_date >= NOW()           THEN 'active'
                    ELSE 'expired'
                END as subscription_status")
            );

        // Search
        if ($search !== '') {
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                $q->where('c.company_name',  'LIKE', $like)
                  ->orWhere('c.company_email','LIKE', $like)
                  ->orWhere(DB::raw("CONCAT(po.first_name,' ',po.last_name)"), 'LIKE', $like)
                  ->orWhere('u.email',        'LIKE', $like)
                  ->orWhere('pp.transaction_number', 'LIKE', $like)
                  ->orWhere('sp.name',        'LIKE', $like);
            });
        }

        // Plan
        if ($plan !== '') {
            $query->where('sp.plan_key', $plan);
        }

        // Status
        if ($status !== '') {
            switch ($status) {
                case 'active':
                    $query->where('pp.is_approved', 1)->where('pp.is_cancelled', 0)
                          ->where(fn($q) => $q->whereNull('pp.expiration_date')
                                             ->orWhereRaw('pp.expiration_date >= NOW()'));
                    break;
                case 'expired':
                    $query->where('pp.is_approved', 1)->where('pp.is_cancelled', 0)
                          ->whereNotNull('pp.expiration_date')
                          ->whereRaw('pp.expiration_date < NOW()');
                    break;
                case 'pending':
                    $query->where('pp.is_approved', 0)->where('pp.is_cancelled', 0);
                    break;
                case 'cancelled':
                    $query->where('pp.is_cancelled', 1);
                    break;
            }
        }

        // Sort
        switch ($sort) {
            case 'oldest':      $query->orderBy('pp.transaction_date', 'asc');  break;
            case 'amount_desc': $query->orderBy('pp.amount', 'desc');           break;
            case 'amount_asc':  $query->orderBy('pp.amount', 'asc');            break;
            case 'name_asc':
                $query->orderByRaw("CASE
                    WHEN pp.contractor_id IS NOT NULL THEN c.company_name
                    ELSE CONCAT(po.first_name,' ',po.last_name)
                END ASC");
                break;
            default: $query->orderBy('pp.transaction_date', 'desc');
        }

        return $query->paginate(15)->withQueryString();
    }


    // ── AJAX: subscriber list (for live search/filter without page reload) ────
    public function getSubscribersAjax(\Illuminate\Http\Request $request)
    {
        $subscribers = $this->getSubscribers($request);

        $items = $subscribers->map(function ($s) {
            return [
                'id'                  => $s->platform_payment_id,
                'subscriber_name'     => $s->subscriber_name,
                'rep_name'            => $s->rep_name,
                'subscriber_email'    => $s->subscriber_email,
                'subscriber_type'     => $s->subscriber_type,
                'plan_key'            => $s->plan_key,
                'plan_name'           => $s->plan_name,
                'billing_cycle'       => $s->billing_cycle,
                'amount'              => $s->amount,
                'transaction_date'    => $s->transaction_date,
                'expiration_date'     => $s->expiration_date,
                'transaction_number'  => $s->transaction_number,
                'subscription_status' => $s->subscription_status,
                'avatar'              => $s->avatar,
                'initials'            => $s->initials,
            ];
        });

        return response()->json([
            'data'         => $items,
            'current_page' => $subscribers->currentPage(),
            'last_page'    => $subscribers->lastPage(),
            'total'        => $subscribers->total(),
            'per_page'     => $subscribers->perPage(),
        ]);
    }

    private function getProjectsAnalytics()
    {
        $statuses    = ['completed' => 'Completed Projects', 'in_progress' => 'Ongoing', 'open' => 'On Hold', 'terminated' => 'Cancelled'];
        $projectData = [];
        $total       = 0;

        foreach ($statuses as $status => $label) {
            $count         = DB::table('projects')->where('project_status', $status)->count();
            $projectData[] = ['status' => $status, 'label' => $label, 'count' => $count];
            $total        += $count;
        }

        return ['total' => $total, 'data' => $projectData];
    }

    private function getProjectSuccessRate()
    {
        $statuses    = [
            'completed'   => ['label' => 'Completed',   'color' => '#10b981'],
            'in_progress' => ['label' => 'In progress',  'color' => '#3b82f6'],
            'open'        => ['label' => 'On Hold',      'color' => '#f59e0b'],
            'terminated'  => ['label' => 'Cancelled',    'color' => '#ef4444'],
        ];
        $successData = [];
        $total       = 0;

        foreach ($statuses as $status => $info) {
            $count  = DB::table('projects')->where('project_status', $status)->count();
            if ($count > 0) {
                $successData[] = ['status' => $status, 'label' => $info['label'], 'color' => $info['color'], 'count' => $count];
            }
            $total += $count;
        }
        foreach ($successData as &$item) {
            $item['percentage'] = $total > 0 ? round(($item['count'] / $total) * 100, 1) : 0;
        }

        return ['total' => $total, 'data' => $successData];
    }

    private function getProjectsTimeline()
    {
        $months = $newProjects = $completedProjects = [];

        for ($i = 5; $i >= 0; $i--) {
            $date         = date('Y-m-01', strtotime("-$i months"));
            $months[]     = date('M Y', strtotime($date));

            if (Schema::hasColumn('projects', 'created_at')) {
                $newCount = DB::table('projects')->whereYear('created_at', date('Y', strtotime($date)))->whereMonth('created_at', date('m', strtotime($date)))->count();
            } else {
                $newCount = DB::table('project_relationships')->whereYear('created_at', date('Y', strtotime($date)))->whereMonth('created_at', date('m', strtotime($date)))->count();
            }
            $newProjects[] = $newCount;

            if (Schema::hasColumn('projects', 'updated_at')) {
                $completedCount = DB::table('projects')->where('project_status', 'completed')->whereYear('updated_at', date('Y', strtotime($date)))->whereMonth('updated_at', date('m', strtotime($date)))->count();
            } else {
                $completedCount = DB::table('projects')->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')->where('project_status', 'completed')->whereYear('project_relationships.created_at', date('Y', strtotime($date)))->whereMonth('project_relationships.created_at', date('m', strtotime($date)))->count();
            }
            $completedProjects[] = $completedCount;
        }

        return [
            'months'            => $months,
            'newProjects'       => $newProjects,
            'completedProjects' => $completedProjects,
            'dateRange'         => date('M d', strtotime('-5 months')) . ' - ' . date('d Y'),
        ];
    }

    // =============================================
    // NEW: PROJECT PERFORMANCE METRICS (replaces hardcoded projectPerformance blade)
    // =============================================

    /**
     * Aggregate KPI stats for the performance hero cards.
     * Uses real DB tables: projects, bids, milestones, project_relationships.
     */
    private function getProjectPerformanceMetrics(): array
    {
        // ---- Counts ----
        $totalProjects     = DB::table('projects')->whereNotIn('project_status', ['deleted', 'deleted_post'])->count();
        $completedProjects = DB::table('projects')->where('project_status', 'completed')->count();
        $totalBids         = DB::table('bids')->count();
        $acceptedBids      = DB::table('bids')->where('bid_status', 'accepted')->count();

        // ---- Total contracted value (sum of accepted bid proposed_cost) ----
        $totalValue = DB::table('bids')
            ->where('bid_status', 'accepted')
            ->sum('proposed_cost');

        // ---- Average bids per project ----
        $avgBidsPerProject = DB::table('bids')
            ->selectRaw('AVG(bid_count) as avg')
            ->from(DB::raw('(SELECT COUNT(*) as bid_count FROM bids GROUP BY project_id) as t'))
            ->value('avg') ?? 0;

        // ---- Average project duration in days
        // Derived from milestones: avg(DATEDIFF(end_date, start_date)) per project
        $avgDuration = DB::table('milestones')
            ->where('setup_status', 'approved')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->selectRaw('AVG(DATEDIFF(end_date, start_date)) as avg_days')
            ->value('avg_days') ?? 0;

        // ---- Completion rate (%) ----
        $completionRate = $totalProjects > 0
            ? round(($completedProjects / $totalProjects) * 100, 1)
            : 0;

        // ---- On-time delivery: milestones completed before or on end_date ----
        $totalCompletedMilestones = DB::table('milestones')
            ->where('milestone_status', 'completed')
            ->count();

        $onTimeMilestones = DB::table('milestones')
            ->where('milestone_status', 'completed')
            ->whereRaw('updated_at <= end_date')
            ->count();

        $onTimeRate = $totalCompletedMilestones > 0
            ? round(($onTimeMilestones / $totalCompletedMilestones) * 100, 1)
            : 0;

        // ---- Month-over-month project growth ----
        $lastMonthProjects = DB::table('project_relationships')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();
        $thisMonthProjects = DB::table('project_relationships')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $momGrowth = $lastMonthProjects > 0
            ? round((($thisMonthProjects - $lastMonthProjects) / $lastMonthProjects) * 100, 1)
            : 0;

        // ---- Total value MoM ----
        $lastMonthValue = DB::table('bids')
            ->where('bid_status', 'accepted')
            ->whereYear('decision_date', now()->subMonth()->year)
            ->whereMonth('decision_date', now()->subMonth()->month)
            ->sum('proposed_cost');
        $thisMonthValue = DB::table('bids')
            ->where('bid_status', 'accepted')
            ->whereYear('decision_date', now()->year)
            ->whereMonth('decision_date', now()->month)
            ->sum('proposed_cost');
        $valueMomGrowth = $lastMonthValue > 0
            ? round((($thisMonthValue - $lastMonthValue) / $lastMonthValue) * 100, 1)
            : 0;

        // ---- Completion trends: last 12 months (new vs completed) ----
        $completionTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $d     = now()->subMonths($i)->startOfMonth();
            $label = $d->format('M Y');

            $newCount  = DB::table('project_relationships')->whereYear('created_at', $d->year)->whereMonth('created_at', $d->month)->count();

            if (Schema::hasColumn('projects', 'updated_at')) {
                $compCount = DB::table('projects')->where('project_status', 'completed')->whereYear('updated_at', $d->year)->whereMonth('updated_at', $d->month)->count();
            } else {
                $compCount = DB::table('projects')
                    ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->where('project_status', 'completed')
                    ->whereYear('project_relationships.created_at', $d->year)
                    ->whereMonth('project_relationships.created_at', $d->month)
                    ->count();
            }

            $completionTrends[] = ['month' => $label, 'new' => $newCount, 'completed' => $compCount];
        }

        // ---- Projects by property_type (for category chart) ----
        $byPropertyType = DB::table('projects')
            ->whereNotIn('project_status', ['deleted', 'deleted_post'])
            ->select('property_type', DB::raw('COUNT(*) as count'))
            ->groupBy('property_type')
            ->get()
            ->pluck('count', 'property_type')
            ->toArray();

        return [
            'total_projects'     => $totalProjects,
            'completed_projects' => $completedProjects,
            'total_bids'         => $totalBids,
            'accepted_bids'      => $acceptedBids,
            'total_value'        => $totalValue,
            'avg_bids_per_project' => round($avgBidsPerProject, 1),
            'avg_duration'       => round($avgDuration),
            'completion_rate'    => $completionRate,
            'on_time_rate'       => $onTimeRate,
            'mom_growth'         => $momGrowth,
            'value_mom_growth'   => $valueMomGrowth,
            'completion_trends'  => $completionTrends,
            'by_property_type'   => $byPropertyType,
        ];
    }

    /**
     * Top 5 contractors ranked by completed_projects (from contractors table),
     * enriched with accepted bid count, average rating, and avg bid duration.
     */
    private function getTopContractors(): array
{
    // ── 1. Base: projects assigned to this contractor ─────────────
    $assignedSub = DB::table('projects')
        ->select('selected_contractor_id', DB::raw('COUNT(*) as assigned_count'))
        ->whereNotNull('selected_contractor_id')
        ->whereNotIn('project_status', ['deleted', 'deleted_post'])
        ->groupBy('selected_contractor_id');

    // ── 2. Disputes raised AGAINST the contractor (by type & status)
    //    dispute_type weights: Quality=1.5x, Delay=1.2x, Halt=1.2x, others=1.0x
    //    dispute_status weights: open/under_review=1.0 (active), resolved/closed=0.4 (mitigated)
    //    Formula: SUM(type_weight * status_weight) capped at 20
    $disputeSub = DB::table('disputes as d')
        ->join('users as du', 'du.user_id', '=', 'd.against_user_id')
        ->join('property_owners as dpo', 'du.user_id', '=', 'dpo.user_id')
        ->join('contractors as dc', 'dpo.owner_id', '=', 'dc.owner_id')
        ->select(
            'dc.contractor_id',
            DB::raw("
                SUM(
                    CASE d.dispute_type
                        WHEN 'Quality' THEN 1.5
                        WHEN 'Delay'   THEN 1.2
                        WHEN 'Halt'    THEN 1.2
                        ELSE 1.0
                    END
                    *
                    CASE d.dispute_status
                        WHEN 'open'         THEN 1.0
                        WHEN 'under_review' THEN 1.0
                        WHEN 'resolved'     THEN 0.4
                        WHEN 'closed'       THEN 0.4
                        ELSE 0.2   -- cancelled
                    END
                ) as dispute_score
            ")
        )
        ->whereIn('d.dispute_status', ['open', 'under_review', 'resolved', 'closed'])
        ->groupBy('dc.contractor_id');

    // ── 3. Delay pacing from milestone_items ──────────────────────
    //    Counts total items + items that were extended or are delayed
    $delaySub = DB::table('milestone_items as mi')
        ->join('milestones as m', 'm.milestone_id', '=', 'mi.milestone_id')
        ->select(
            'm.contractor_id',
            DB::raw('COUNT(mi.item_id) as total_items'),
            DB::raw('SUM(mi.was_extended) as extended_items'),
            DB::raw("SUM(CASE WHEN mi.item_status = 'delayed' THEN 1 ELSE 0 END) as delayed_items"),
            DB::raw('SUM(mi.extension_count) as total_extensions')
        )
        ->where('m.setup_status', 'approved')
        ->groupBy('m.contractor_id');

    // ── 4. Contract terminations ───────────────────────────────────
    $terminationSub = DB::table('contract_terminations')
        ->select('contractor_id', DB::raw('COUNT(*) as termination_count'))
        ->groupBy('contractor_id');

    // ── 5. Average rating from reviews ────────────────────────────
    $ratingSub = DB::table('reviews as r')
        ->join('property_owners as rev_po', 'rev_po.user_id', '=', 'r.reviewee_user_id')
        ->join('contractors as rc', 'rc.owner_id', '=', 'rev_po.owner_id')
        ->select(
            'rc.contractor_id',
            DB::raw('ROUND(AVG(r.rating), 2) as avg_rating'),
            DB::raw('COUNT(r.review_id) as review_count')
        )
        ->groupBy('rc.contractor_id');

    // ── Main query ─────────────────────────────────────────────────
    $rows = DB::table('contractors as c')
        ->join('property_owners as c_po', 'c.owner_id', '=', 'c_po.owner_id')
        ->join('users as c_u', 'c_po.user_id', '=', 'c_u.user_id')
        ->leftJoinSub($assignedSub,     'ap',  'ap.selected_contractor_id', '=', 'c.contractor_id')
        ->leftJoinSub($disputeSub,      'dp',  'dp.contractor_id',          '=', 'c.contractor_id')
        ->leftJoinSub($delaySub,        'dl',  'dl.contractor_id',          '=', 'c.contractor_id')
        ->leftJoinSub($terminationSub,  'tr',  'tr.contractor_id',          '=', 'c.contractor_id')
        ->leftJoinSub($ratingSub,       'rev', 'rev.contractor_id',         '=', 'c.contractor_id')
        ->where('c.verification_status', 'approved')
        ->where('c.is_active', 1)
        ->select(
            'c.contractor_id',
            'c.company_name',
            'c.completed_projects',
            'c.years_of_experience',
            'c.company_logo',
            DB::raw("CONCAT(c_u.first_name, ' ', c_u.last_name) as rep_name"),
            DB::raw('IFNULL(ap.assigned_count,      0) as assigned_count'),
            DB::raw('IFNULL(dp.dispute_score,        0) as dispute_score'),
            DB::raw('IFNULL(dl.total_items,          0) as total_items'),
            DB::raw('IFNULL(dl.extended_items,       0) as extended_items'),
            DB::raw('IFNULL(dl.delayed_items,        0) as delayed_items'),
            DB::raw('IFNULL(dl.total_extensions,     0) as total_extensions'),
            DB::raw('IFNULL(tr.termination_count,    0) as termination_count'),
            DB::raw('IFNULL(rev.avg_rating,          0) as avg_rating'),
            DB::raw('IFNULL(rev.review_count,        0) as review_count')
        )
        ->orderByDesc('c.completed_projects')
        ->limit(5)
        ->get();

    return $rows->map(function ($r, $i) {

        // ── A. Base completion rate (0–50) ─────────────────────────
        $baseScore = $r->assigned_count > 0
            ? min(($r->completed_projects / $r->assigned_count) * 50, 50)
            : ($r->completed_projects > 0 ? 50 : 0);

        // ── B. Dispute penalty (0–20) ──────────────────────────────
        // Each unit of dispute_score costs 4 pts, capped at 20
        $disputePenalty = min($r->dispute_score * 4, 20);

        // ── C. Delay penalty (0–15) ────────────────────────────────
        // Ratio of problematic items (extended OR delayed) to total items
        // Each 10% bad-item rate costs 1.5 pts
        $delayPenalty = 0;
        if ($r->total_items > 0) {
            $badItems   = min($r->extended_items + $r->delayed_items, $r->total_items);
            $badRatio   = $badItems / $r->total_items;          // 0.0 – 1.0
            $delayPenalty = min($badRatio * 15, 15);
        }

        // ── D. Termination penalty (0–15) ──────────────────────────
        // Each termination costs 7.5 pts, capped at 15
        $terminationPenalty = min($r->termination_count * 7.5, 15);

        // ── E. Rating bonus (0–10) ─────────────────────────────────
        // Normalize avg_rating (1–5 scale) to 0–10
        $ratingBonus = $r->avg_rating > 0
            ? round((($r->avg_rating - 1) / 4) * 10, 1)
            : 0;

        // ── Final composite score (0–100) ──────────────────────────
        $rawScore = $baseScore - $disputePenalty - $delayPenalty - $terminationPenalty + $ratingBonus;
        $finalScore = round(max(0, min(100, $rawScore)), 1);

        // ── Human-readable breakdown for tooltip/display ───────────
        $breakdown = [
            'base_completion'    => round($baseScore, 1),
            'dispute_penalty'    => round($disputePenalty, 1),
            'delay_penalty'      => round($delayPenalty, 1),
            'termination_penalty'=> round($terminationPenalty, 1),
            'rating_bonus'       => $ratingBonus,
        ];

        return [
            'rank'                => $i + 1,
            'contractor_id'       => $r->contractor_id,
            'company_name'        => $r->company_name,
            'rep_name'            => $r->rep_name,
            'completed_projects'  => $r->completed_projects,
            'assigned_count'      => $r->assigned_count,
            'success_rate'        => $finalScore,          // replaces old broken field
            'breakdown'           => $breakdown,           // for blade tooltip
            'avg_rating'          => $r->avg_rating,
            'review_count'        => $r->review_count,
            'years_of_experience' => $r->years_of_experience,
            'company_logo'        => $r->company_logo,
            'initials'            => strtoupper(substr($r->company_name, 0, 2)),
            // Extra detail fields for expanded view
            'dispute_score'       => round($r->dispute_score, 1),
            'delayed_items'       => (int)$r->delayed_items,
            'extended_items'      => (int)$r->extended_items,
            'termination_count'   => (int)$r->termination_count,
        ];
    })->toArray();
}

    /**
     * Bid metrics for the bid completion section.
     */
    private function getBidMetrics(): array
    {
        $total    = DB::table('bids')->count();
        $accepted = DB::table('bids')->where('bid_status', 'accepted')->count();
        $rejected = DB::table('bids')->where('bid_status', 'rejected')->count();
        $pending  = DB::table('bids')->whereIn('bid_status', ['submitted', 'under_review'])->count();
        $cancelled= DB::table('bids')->where('bid_status', 'cancelled')->count();

        $avgPerProject = DB::table('bids')
            ->selectRaw('AVG(bid_count) as avg')
            ->from(DB::raw('(SELECT COUNT(*) as bid_count FROM bids GROUP BY project_id) as t'))
            ->value('avg') ?? 0;

        return [
            'total'           => $total,
            'accepted'        => $accepted,
            'rejected'        => $rejected,
            'pending'         => $pending,
            'cancelled'       => $cancelled,
            'avg_per_project' => round($avgPerProject, 1),
            'acceptance_rate' => $total > 0 ? round(($accepted / $total) * 100, 1) : 0,
        ];
    }

    // =============================================
    // AJAX / API ENDPOINTS
    // =============================================

    public function getProjectsTimelineData(\Illuminate\Http\Request $request)
    {
        $range     = $request->input('range', 'last6months');
        $numMonths = match($range) {
            'last3months' => 3,
            'thisyear'    => (int)date('n'),
            'lastyear'    => 12,
            default       => 6,
        };

        $months = $newProjects = $completedProjects = [];

        for ($i = $numMonths - 1; $i >= 0; $i--) {
            $date = match($range) {
                'lastyear'  => date('Y-m-01', strtotime("-$i months -1 year")),
                'thisyear'  => date('Y-m-01', strtotime('+' . (($numMonths - 1 - $i)) . ' months', strtotime(date('Y-01-01')))),
                default     => date('Y-m-01', strtotime("-$i months")),
            };

            $months[] = date('M Y', strtotime($date));

            if (Schema::hasColumn('projects', 'created_at')) {
                $newCount = DB::table('projects')->whereYear('created_at', date('Y', strtotime($date)))->whereMonth('created_at', date('m', strtotime($date)))->count();
            } else {
                $newCount = DB::table('project_relationships')->whereYear('created_at', date('Y', strtotime($date)))->whereMonth('created_at', date('m', strtotime($date)))->count();
            }
            $newProjects[] = $newCount;

            if (Schema::hasColumn('projects', 'updated_at')) {
                $completedCount = DB::table('projects')->where('project_status', 'completed')->whereYear('updated_at', date('Y', strtotime($date)))->whereMonth('updated_at', date('m', strtotime($date)))->count();
            } else {
                $completedCount = DB::table('projects')->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')->where('project_status', 'completed')->whereYear('project_relationships.created_at', date('Y', strtotime($date)))->whereMonth('project_relationships.created_at', date('m', strtotime($date)))->count();
            }
            $completedProjects[] = $completedCount;
        }

        $dateRange = match($range) {
            'last3months' => date('M d', strtotime('-2 months')) . ' - ' . date('d Y'),
            'thisyear'    => date('M d', strtotime('January 1')) . ' - ' . date('d Y'),
            'lastyear'    => date('M d', strtotime('January 1 last year')) . ' - ' . date('d Y', strtotime('December 31 last year')),
            default       => date('M d', strtotime('-5 months')) . ' - ' . date('d Y'),
        };

        return response()->json(compact('months', 'newProjects', 'completedProjects', 'dateRange'));
    }

    public function getProjectsAnalyticsApi()
    {
        return response()->json($this->getProjectsAnalytics());
    }

    public function getProjectPerformanceAnalyticsApi()
    {
        return response()->json($this->getProjectPerformanceMetrics());
    }

    public function getTopContractorsApi()
    {
        return response()->json($this->getTopContractors());
    }

    public function getBidCompletionAnalyticsApi()
    {
        return response()->json($this->getBidMetrics());
    }

    // =============================================
    // USER ACTIVITY (unchanged from original)
    // =============================================

    public function userActivityAnalytics()
    {
        return view('admin.home.userActivity_Analytics', [
            'userMetrics'    => $this->getUserMetrics(),
            'userGrowth'     => $this->getUserGrowthData(),
            'recentActivity' => $this->getRecentUserActivity(),
        ]);
    }

    private function getUserMetrics(): array
    {
        $totalUsers     = DB::table('users')->count();
        $propertyOwners = DB::table('property_owners')->count();
        $contractors    = DB::table('contractors')->count();
        $activeProjects = DB::table('projects')->where('project_status', 'in_progress')->count();
        $newThisMonth   = DB::table('users')->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
        $newLastMonth   = DB::table('users')->whereYear('created_at', now()->subMonth()->year)->whereMonth('created_at', now()->subMonth()->month)->count();

        $activeUsers = DB::table('users')->where(function ($q) {
            $q->whereExists(fn($s) => $s->select(DB::raw(1))->from('property_owners')->whereColumn('property_owners.user_id', 'users.user_id')->where('property_owners.is_active', 1))
              ->orWhereExists(fn($s) => $s->select(DB::raw(1))->from('contractors')->join('property_owners as cp', 'contractors.owner_id', '=', 'cp.owner_id')->whereColumn('cp.user_id', 'users.user_id')->where('contractors.is_active', 1));
        })->count();

        $suspendedUsers = DB::table('users')->where(function ($q) {
            $q->whereExists(fn($s) => $s->select(DB::raw(1))->from('property_owners')->whereColumn('property_owners.user_id', 'users.user_id')->where('property_owners.is_active', 0))
              ->orWhereExists(fn($s) => $s->select(DB::raw(1))->from('contractors')->join('property_owners as cp2', 'contractors.owner_id', '=', 'cp2.owner_id')->whereColumn('cp2.user_id', 'users.user_id')->where('contractors.is_active', 0));
        })->count();

        $prevTotal  = DB::table('users')->where('created_at', '<', now()->startOfMonth())->count();
        $momChange  = $prevTotal > 0 ? round((($totalUsers - $prevTotal) / $prevTotal) * 100, 1) : 0;

        return compact('totalUsers', 'propertyOwners', 'contractors', 'activeProjects', 'newThisMonth', 'newLastMonth', 'activeUsers', 'suspendedUsers', 'momChange') + [
            'total_users'     => $totalUsers,
            'property_owners' => $propertyOwners,
            'active_projects' => $activeProjects,
            'new_this_month'  => $newThisMonth,
            'new_last_month'  => $newLastMonth,
            'active_users'    => $activeUsers,
            'suspended_users' => $suspendedUsers,
            'mom_change'      => $momChange,
        ];
    }

    private function getUserGrowthData(): array
    {
        $months = $ownersData = $contractorsData = $totalData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date     = now()->subMonths($i)->startOfMonth();
            $months[] = $date->format('M Y');
            $owners   = DB::table('users')->whereIn('user_type', ['property_owner', 'both'])->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count();
            $contrs   = DB::table('users')->whereIn('user_type', ['contractor', 'both'])->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->count();
            $ownersData[]      = $owners;
            $contractorsData[] = $contrs;
            $totalData[]       = $owners + $contrs;
        }

        return [
            'months'       => $months,
            'owners'       => $ownersData,
            'contractors'  => $contractorsData,
            'totals'       => $totalData,
            'distribution' => [
                'Property Owner' => DB::table('users')->where('user_type', 'property_owner')->count(),
                'Contractor'     => DB::table('users')->where('user_type', 'contractor')->count(),
                'Both'           => DB::table('users')->where('user_type', 'both')->count(),
                'Staff'          => DB::table('users')->where('user_type', 'staff')->count(),
            ],
        ];
    }

    private function getRecentUserActivity(): array
    {
        $recentBids = DB::table('bids')
            ->join('contractors', 'contractors.contractor_id', '=', 'bids.contractor_id')
            ->join('property_owners as bid_po', 'contractors.owner_id', '=', 'bid_po.owner_id')
            ->join('users', 'bid_po.user_id', '=', 'users.user_id')
            ->select('users.user_id', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as full_name"), 'users.email', 'users.user_type', DB::raw("'Submitted a bid' as action"), 'bids.submitted_at as activity_time', 'bid_po.profile_pic', 'contractors.is_active')
            ->orderByDesc('bids.submitted_at')->limit(5)->get();

        $recentProjects = DB::table('project_relationships')
            ->join('property_owners', 'property_owners.owner_id', '=', 'project_relationships.owner_id')
            ->join('users', 'users.user_id', '=', 'property_owners.user_id')
            ->select('users.user_id', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as full_name"), 'users.email', 'users.user_type', DB::raw("'Posted a project' as action"), 'project_relationships.created_at as activity_time', 'property_owners.profile_pic', 'property_owners.is_active')
            ->orderByDesc('project_relationships.created_at')->limit(5)->get();

        return collect($recentBids)->concat($recentProjects)->sortByDesc('activity_time')->take(10)->values()->toArray();
    }

    public function getUserActivityAnalyticsApi()
    {
        return response()->json(['metrics' => $this->getUserMetrics(), 'growth' => $this->getUserGrowthData()]);
    }

    public function getUserActivityFeed()
    {
        return response()->json($this->getRecentUserActivity());
    }

    // =============================================
    // OTHER PAGE ROUTES (kept for backwards compat)
    // =============================================

    public function projectPerformanceAnalytics()
    {
        // Now redirects to unified page
        return redirect()->route('admin.analytics');
    }

    public function bidCompletionAnalytics()
    {
        $data = $this->getBidCompletionData();

        return view('admin.home.bidCompletion_Analytics', $data);
    }

    /**
     * All data for the Bid Completion Analytics page.
     * Sources: bids, projects, project_relationships, contractors,
     *          contractor_users, milestone_payments, platform_payments,
     *          subscription_plans
     */
    private function getBidCompletionData(): array
    {
        // ── HERO CARDS ────────────────────────────────────────────────

        // Total non-deleted projects
        $totalProjects = DB::table('projects')
            ->whereNotIn('project_status', ['deleted', 'deleted_post'])
            ->count();

        // MoM project growth via project_relationships.created_at
        $thisMonthProjects = DB::table('project_relationships')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $lastMonthProjects = DB::table('project_relationships')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();
        $projectsMoM = $lastMonthProjects > 0
            ? round((($thisMonthProjects - $lastMonthProjects) / $lastMonthProjects) * 100, 1)
            : 0;

        // Active contractors (approved + active)
        $activeContractors = DB::table('contractors')
            ->where('verification_status', 'approved')
            ->where('is_active', 1)
            ->count();

        // Contractor completion rate = avg(completed_projects / max(completed_projects)) * 100
        // Simpler: sum(completed_projects) / count(active contractors)
        $totalCompleted = DB::table('contractors')
            ->where('verification_status', 'approved')
            ->where('is_active', 1)
            ->sum('completed_projects');
        $contractorCompletionRate = $activeContractors > 0
            ? round(($totalCompleted / ($activeContractors * max(
                DB::table('contractors')->where('is_active', 1)->max('completed_projects'), 1
            ))) * 100, 1)
            : 0;

        // Total bids count
        $totalBids = DB::table('bids')->count();

        // Total contracted value (sum of accepted bids' proposed_cost, in millions)
        $totalContractedValue = DB::table('bids')
            ->where('bid_status', 'accepted')
            ->sum('proposed_cost');
        $totalValueM = round($totalContractedValue / 1_000_000, 1); // in millions

        // Bid completion/acceptance rate
        $acceptedBids = DB::table('bids')->where('bid_status', 'accepted')->count();
        $completionRate = $totalBids > 0
            ? round(($acceptedBids / $totalBids) * 100, 1)
            : 0;

        // MoM completion rate change (this month vs last month acceptance rate)
        $thisMonthBids     = DB::table('bids')->whereYear('submitted_at', now()->year)->whereMonth('submitted_at', now()->month)->count();
        $thisMonthAccepted = DB::table('bids')->where('bid_status', 'accepted')->whereYear('submitted_at', now()->year)->whereMonth('submitted_at', now()->month)->count();
        $lastMonthBids     = DB::table('bids')->whereYear('submitted_at', now()->subMonth()->year)->whereMonth('submitted_at', now()->subMonth()->month)->count();
        $lastMonthAccepted = DB::table('bids')->where('bid_status', 'accepted')->whereYear('submitted_at', now()->subMonth()->year)->whereMonth('submitted_at', now()->subMonth()->month)->count();

        $thisRate = $thisMonthBids > 0 ? ($thisMonthAccepted / $thisMonthBids) * 100 : 0;
        $lastRate = $lastMonthBids > 0 ? ($lastMonthAccepted / $lastMonthBids) * 100 : 0;
        $completionRateMoM = $lastRate > 0 ? round($thisRate - $lastRate, 1) : 0;

        // ── BID TIMELINE CHART (last 12 months: submitted vs accepted) ─

        $timelineMonths    = [];
        $timelineSubmitted = [];
        $timelineAccepted  = [];

        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i)->startOfMonth();
            $timelineMonths[]    = $d->format('M');
            $timelineSubmitted[] = DB::table('bids')
                ->whereYear('submitted_at', $d->year)
                ->whereMonth('submitted_at', $d->month)
                ->count();
            $timelineAccepted[]  = DB::table('bids')
                ->where('bid_status', 'accepted')
                ->whereYear('submitted_at', $d->year)
                ->whereMonth('submitted_at', $d->month)
                ->count();
        }

        // ── BID STATUS DISTRIBUTION (doughnut chart) ─────────────────

        $bidStatusCounts = [
            'Accepted'     => DB::table('bids')->where('bid_status', 'accepted')->count(),
            'Submitted'    => DB::table('bids')->where('bid_status', 'submitted')->count(),
            'Under Review' => DB::table('bids')->where('bid_status', 'under_review')->count(),
            'Rejected'     => DB::table('bids')->where('bid_status', 'rejected')->count(),
            'Cancelled'    => DB::table('bids')->where('bid_status', 'cancelled')->count(),
        ];
        // Remove zero entries
        $bidStatusCounts = array_filter($bidStatusCounts, fn($v) => $v > 0);

        // ── BID METRICS (3 cards) ─────────────────────────────────────

        // Average bid value (proposed_cost) in thousands
        $avgBidValue = DB::table('bids')->avg('proposed_cost') ?? 0;
        $avgBidValueK = round($avgBidValue / 1000, 1); // in thousands

        // Average response time in hours (decision_date - submitted_at for decided bids)
        $avgResponseHours = DB::table('bids')
            ->whereNotNull('decision_date')
            ->whereIn('bid_status', ['accepted', 'rejected'])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, submitted_at, decision_date)) as avg_hours')
            ->value('avg_hours') ?? 0;
        $avgResponseHours = round($avgResponseHours, 1);

        // Bid win rate = accepted / total * 100
        $bidWinRate = $totalBids > 0 ? round(($acceptedBids / $totalBids) * 100, 1) : 0;

        // Win rate progress bar: normalize to max 100
        $winRateBarWidth = min($bidWinRate, 100);

        // Response time bar: lower is better, normalize inversely (0hr=100%, 72hr+=0%)
        $responseBarWidth = max(0, round(100 - ($avgResponseHours / 72) * 100));

        // Avg bid value bar: normalize vs max bid value
        $maxBidValue = DB::table('bids')->max('proposed_cost') ?? 1;
        $avgBidBarWidth = min(round(($avgBidValue / $maxBidValue) * 100), 100);

        // ── GEOGRAPHIC DISTRIBUTION ───────────────────────────────────
        // project_location is free-text. Extract known districts via LIKE.
        // Known Zamboanga City districts from blade: Tetuan, Tumaga, Malagutay + others
        $districts = ['Tetuan', 'Tumaga', 'Sinunuc', 'Malagutay', 'Baliwasan', 'Upper Calarian'];
        $geoLabels  = [];
        $geoCounts  = [];
        $districtCards = []; // for the 4 summary cards

        foreach ($districts as $district) {
            $count = DB::table('projects')
                ->whereNotIn('project_status', ['deleted', 'deleted_post'])
                ->where('project_location', 'LIKE', "%{$district}%")
                ->count();
            $geoLabels[] = $district;
            $geoCounts[] = $count;
        }

        // "Others" = all projects not matching any known district
        $knownCount = DB::table('projects')
            ->whereNotIn('project_status', ['deleted', 'deleted_post'])
            ->where(function ($q) use ($districts) {
                foreach ($districts as $d) {
                    $q->orWhere('project_location', 'LIKE', "%{$d}%");
                }
            })
            ->count();
        $othersCount  = $totalProjects - $knownCount;
        $geoLabels[]  = 'Others';
        $geoCounts[]  = $othersCount;

        // District value (sum of budget_range_max for accepted-bid projects per district)
        foreach ($districts as $district) {
            $value = DB::table('projects')
                ->join('bids', 'bids.project_id', '=', 'projects.project_id')
                ->where('bids.bid_status', 'accepted')
                ->whereNotIn('projects.project_status', ['deleted', 'deleted_post'])
                ->where('projects.project_location', 'LIKE', "%{$district}%")
                ->sum('bids.proposed_cost');
            $districtCards[$district] = [
                'count' => DB::table('projects')
                    ->whereNotIn('project_status', ['deleted', 'deleted_post'])
                    ->where('project_location', 'LIKE', "%{$district}%")
                    ->count(),
                'value' => round($value / 1_000_000, 1),
            ];
        }
        // Only show first 4 in card grid (Tetuan, Tumaga, Malagutay, Others)
        $fourDistricts = [
            'Tetuan'    => $districtCards['Tetuan']    ?? ['count' => 0, 'value' => 0],
            'Tumaga'    => $districtCards['Tumaga']    ?? ['count' => 0, 'value' => 0],
            'Malagutay' => $districtCards['Malagutay'] ?? ['count' => 0, 'value' => 0],
            'Others'    => ['count' => $othersCount, 'value' => round(
                DB::table('projects')
                    ->join('bids', 'bids.project_id', '=', 'projects.project_id')
                    ->where('bids.bid_status', 'accepted')
                    ->whereNotIn('projects.project_status', ['deleted', 'deleted_post'])
                    ->where(function ($q) use ($districts) {
                        foreach ($districts as $d) { $q->where('projects.project_location', 'NOT LIKE', "%{$d}%"); }
                    })
                    ->sum('bids.proposed_cost') / 1_000_000, 1
            )],
        ];

        // ── RECENT BIDS TABLE (last 10 bids with project + contractor info) ─

        $recentBids = DB::table('bids as b')
            ->join('projects as p',          'p.project_id',     '=', 'b.project_id')
            ->join('contractors as c',       'c.contractor_id',  '=', 'b.contractor_id')
            ->join('property_owners as bid_c_po', 'c.owner_id', '=', 'bid_c_po.owner_id')
            ->join('users as bid_c_u', 'bid_c_po.user_id', '=', 'bid_c_u.user_id')
            ->leftJoin('platform_payments as pp', function ($j) {
                $j->on('pp.contractor_id', '=', 'c.contractor_id')
                  ->where('pp.is_approved', 1)
                  ->where('pp.is_cancelled', 0);
            })
            ->leftJoin('subscription_plans as sp', 'sp.id', '=', 'pp.subscriptionPlanId')
            ->select(
                'b.bid_id',
                'b.proposed_cost',
                'b.bid_status',
                'b.submitted_at',
                'p.project_title',
                'p.project_location',
                'c.company_name',
                'c.company_logo',
                DB::raw("CONCAT(bid_c_u.first_name, ' ', bid_c_u.last_name) as rep_name"),
                DB::raw("UPPER(LEFT(c.company_name, 2)) as initials"),
                DB::raw("COALESCE(MAX(sp.name), 'No Subscription') as subscription_tier")
            )
            ->groupBy(
                'b.bid_id', 'b.proposed_cost', 'b.bid_status', 'b.submitted_at',
                'p.project_title', 'p.project_location',
                'c.company_name', 'c.company_logo',
                'bid_c_u.first_name', 'bid_c_u.last_name'
            )
            ->orderByDesc('b.submitted_at')
            ->limit(10)
            ->get();

        // ── PROPERTY OWNERS ACTIVITY (latest accepted/pending bids with owner info) ─

        $ownerActivity = DB::table('bids as b')
            ->join('projects as p',             'p.project_id',    '=', 'b.project_id')
            ->join('project_relationships as pr','pr.rel_id',       '=', 'p.relationship_id')
            ->join('contractors as c',           'c.contractor_id', '=', 'b.contractor_id')
            ->select(
                'p.project_title',
                'p.project_location',
                'c.company_name',
                'b.proposed_cost',
                'b.bid_status'
            )
            ->whereIn('b.bid_status', ['accepted', 'submitted', 'under_review'])
            ->orderByDesc('b.submitted_at')
            ->limit(6)
            ->get();

        // ── PAYMENT ANALYTICS (from milestone_payments) ───────────────

        // Total payments released this month (approved)
        $paymentsReleasedThisMonth = DB::table('milestone_payments')
            ->where('payment_status', 'approved')
            ->whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        // Pending payments (submitted, not yet approved)
        $pendingPayments = DB::table('milestone_payments')
            ->where('payment_status', 'submitted')
            ->sum('amount');

        // Avg payment processing time in days (updated_at - transaction_date for approved)
        $avgPaymentDays = DB::table('milestone_payments')
            ->where('payment_status', 'approved')
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(DATEDIFF(updated_at, transaction_date)) as avg_days')
            ->value('avg_days') ?? 0;
        $avgPaymentDays = round($avgPaymentDays, 1);

        // Payment success rate = approved / (approved + rejected)
        $approvedPayments = DB::table('milestone_payments')->where('payment_status', 'approved')->count();
        $rejectedPayments = DB::table('milestone_payments')->where('payment_status', 'rejected')->count();
        $paymentSuccessRate = ($approvedPayments + $rejectedPayments) > 0
            ? round(($approvedPayments / ($approvedPayments + $rejectedPayments)) * 100, 1)
            : 0;

        return [
            // Hero cards
            'totalProjects'           => $totalProjects,
            'projectsMoM'             => $projectsMoM,
            'activeContractors'       => $activeContractors,
            'contractorCompletionRate'=> $contractorCompletionRate,
            'totalBids'               => $totalBids,
            'totalValueM'             => $totalValueM,
            'completionRate'          => $completionRate,
            'completionRateMoM'       => $completionRateMoM,

            // Charts
            'timelineMonths'          => $timelineMonths,
            'timelineSubmitted'       => $timelineSubmitted,
            'timelineAccepted'        => $timelineAccepted,
            'bidStatusCounts'         => $bidStatusCounts,

            // Bid metric cards
            'avgBidValueK'            => $avgBidValueK,
            'avgResponseHours'        => $avgResponseHours,
            'bidWinRate'              => $bidWinRate,
            'avgBidBarWidth'          => $avgBidBarWidth,
            'responseBarWidth'        => $responseBarWidth,
            'winRateBarWidth'         => $winRateBarWidth,

            // Geographic
            'geoLabels'               => $geoLabels,
            'geoCounts'               => $geoCounts,
            'fourDistricts'           => $fourDistricts,

            // Tables
            'recentBids'              => $recentBids,
            'ownerActivity'           => $ownerActivity,

            // Payment analytics
            'paymentsReleasedM'       => round($paymentsReleasedThisMonth / 1_000_000, 1),
            'pendingPaymentsM'        => round($pendingPayments / 1_000_000, 1),
            'avgPaymentDays'          => $avgPaymentDays,
            'paymentSuccessRate'      => $paymentSuccessRate,
        ];
    }

    public function reportsAnalytics()
    {
        return view('admin.home.reportsAnalytics');
    }

    // =============================================
    // AJAX ENDPOINTS: DATE-FILTERED PAGE DATA
    // =============================================

    /**
     * Project Performance Analytics – return all page data filtered by date range.
     */
    public function getProjectAnalyticsData(\Illuminate\Http\Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        return response()->json([
            'projectsAnalytics'  => $this->getProjectsAnalyticsFiltered($dateFrom, $dateTo),
            'projectSuccessRate' => $this->getProjectSuccessRateFiltered($dateFrom, $dateTo),
            'projectsTimeline'   => $this->getProjectsTimelineFiltered($dateFrom, $dateTo),
            'projectPerformance' => $this->getProjectPerformanceFiltered($dateFrom, $dateTo),
            'bidMetrics'         => $this->getBidMetricsFiltered($dateFrom, $dateTo),
            'topContractors'     => $this->getTopContractorsFiltered($dateFrom, $dateTo),
        ]);
    }

    /**
     * Top Contractors with own search + date filter.
     */
    public function getTopContractorsData(\Illuminate\Http\Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $search   = $request->input('search', '');

        return response()->json([
            'topContractors' => $this->getTopContractorsFiltered($dateFrom, $dateTo, $search),
        ]);
    }

    /**
     * Subscription Analytics – return all page data filtered by date range.
     */
    public function getSubscriptionAnalyticsData(\Illuminate\Http\Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        return response()->json([
            'subscriptionMetrics' => $this->getSubscriptionMetricsFiltered($dateFrom, $dateTo),
            'subscriptionTiers'   => $this->getSubscriptionTiersFiltered($dateFrom, $dateTo),
            'subscriptionRevenue' => $this->getSubscriptionRevenueFiltered($dateFrom, $dateTo),
        ]);
    }

    /**
     * User Activity Analytics – return all page data filtered by date range.
     */
    public function getUserAnalyticsData(\Illuminate\Http\Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        return response()->json([
            'userMetrics' => $this->getUserMetricsFiltered($dateFrom, $dateTo),
            'userGrowth'  => $this->getUserGrowthDataFiltered($dateFrom, $dateTo),
        ]);
    }

    /**
     * User Activity Feed with search + date + pagination.
     */
    public function getUserActivityFeedData(\Illuminate\Http\Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $search   = $request->input('search', '');
        $page     = max(1, (int) $request->input('page', 1));
        $perPage  = 10;

        $data = $this->getRecentUserActivityFiltered($dateFrom, $dateTo, $search, $page, $perPage);

        return response()->json($data);
    }

    /**
     * Bid Completion Analytics – return all page data filtered by date range.
     */
    public function getBidAnalyticsData(\Illuminate\Http\Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        return response()->json($this->getBidCompletionDataFiltered($dateFrom, $dateTo));
    }

    // =============================================
    // DATE-FILTERED PRIVATE HELPERS
    // =============================================

    private function getProjectsAnalyticsFiltered($dateFrom, $dateTo)
    {
        $statuses    = ['completed' => 'Completed Projects', 'in_progress' => 'Ongoing', 'open' => 'On Hold', 'terminated' => 'Cancelled'];
        $projectData = [];
        $total       = 0;

        foreach ($statuses as $status => $label) {
            $q = DB::table('projects')->where('project_status', $status);
            if ($dateFrom) $q->where('created_at', '>=', $dateFrom);
            if ($dateTo)   $q->where('created_at', '<=', $dateTo . ' 23:59:59');
            $count         = $q->count();
            $projectData[] = ['status' => $status, 'label' => $label, 'count' => $count];
            $total        += $count;
        }

        return ['total' => $total, 'data' => $projectData];
    }

    private function getProjectSuccessRateFiltered($dateFrom, $dateTo)
    {
        $statuses    = [
            'completed'   => ['label' => 'Completed',   'color' => '#10b981'],
            'in_progress' => ['label' => 'In progress',  'color' => '#3b82f6'],
            'open'        => ['label' => 'On Hold',      'color' => '#f59e0b'],
            'terminated'  => ['label' => 'Cancelled',    'color' => '#ef4444'],
        ];
        $successData = [];
        $total       = 0;

        foreach ($statuses as $status => $info) {
            $q = DB::table('projects')->where('project_status', $status);
            if ($dateFrom) $q->where('created_at', '>=', $dateFrom);
            if ($dateTo)   $q->where('created_at', '<=', $dateTo . ' 23:59:59');
            $count = $q->count();
            if ($count > 0) {
                $successData[] = ['status' => $status, 'label' => $info['label'], 'color' => $info['color'], 'count' => $count];
            }
            $total += $count;
        }
        foreach ($successData as &$item) {
            $item['percentage'] = $total > 0 ? round(($item['count'] / $total) * 100, 1) : 0;
        }

        return ['total' => $total, 'data' => $successData];
    }

    private function getProjectsTimelineFiltered($dateFrom, $dateTo)
    {
        // Determine range from dates or default to 6 months
        if ($dateFrom && $dateTo) {
            $start = \Carbon\Carbon::parse($dateFrom)->startOfMonth();
            $end   = \Carbon\Carbon::parse($dateTo)->startOfMonth();
        } else {
            $start = now()->subMonths(5)->startOfMonth();
            $end   = now()->startOfMonth();
        }

        $months = $newProjects = $completedProjects = [];
        $current = $start->copy();

        while ($current <= $end) {
            $months[] = $current->format('M Y');

            $newCount = DB::table('projects')
                ->whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->count();
            $newProjects[] = $newCount;

            $completedCount = DB::table('projects')
                ->where('project_status', 'completed')
                ->whereYear('updated_at', $current->year)
                ->whereMonth('updated_at', $current->month)
                ->count();
            $completedProjects[] = $completedCount;

            $current->addMonth();
        }

        return [
            'months'            => $months,
            'newProjects'       => $newProjects,
            'completedProjects' => $completedProjects,
            'dateRange'         => ($dateFrom ? $dateFrom : $start->format('Y-m-d')) . ' — ' . ($dateTo ? $dateTo : now()->format('Y-m-d')),
        ];
    }

    private function getProjectPerformanceFiltered($dateFrom, $dateTo): array
    {
        $dateCondition = function ($q, $col = 'created_at') use ($dateFrom, $dateTo) {
            if ($dateFrom) $q->where($col, '>=', $dateFrom);
            if ($dateTo)   $q->where($col, '<=', $dateTo . ' 23:59:59');
        };

        $totalQ = DB::table('projects')->whereNotIn('project_status', ['deleted', 'deleted_post']);
        $dateCondition($totalQ);
        $totalProjects = $totalQ->count();

        $compQ = DB::table('projects')->where('project_status', 'completed');
        $dateCondition($compQ);
        $completedProjects = $compQ->count();

        $bidsQ = DB::table('bids');
        $dateCondition($bidsQ, 'submitted_at');
        $totalBids = $bidsQ->count();

        $accQ = DB::table('bids')->where('bid_status', 'accepted');
        $dateCondition($accQ, 'submitted_at');
        $acceptedBids = $accQ->count();

        $valQ = DB::table('bids')->where('bid_status', 'accepted');
        $dateCondition($valQ, 'submitted_at');
        $totalValue = $valQ->sum('proposed_cost');

        $durationQ = DB::table('milestones')->where('setup_status', 'approved')->whereNotNull('start_date')->whereNotNull('end_date');
        if ($dateFrom) $durationQ->where('start_date', '>=', $dateFrom);
        if ($dateTo)   $durationQ->where('start_date', '<=', $dateTo . ' 23:59:59');
        $avgDuration = $durationQ->selectRaw('AVG(DATEDIFF(end_date, start_date)) as avg_days')->value('avg_days') ?? 0;

        $completionRate = $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100, 1) : 0;

        $totalCompletedMilestones = DB::table('milestones')->where('milestone_status', 'completed');
        $dateCondition($totalCompletedMilestones, 'updated_at');
        $totalCompletedMilestones = $totalCompletedMilestones->count();

        $onTimeMilestones = DB::table('milestones')->where('milestone_status', 'completed')->whereRaw('updated_at <= end_date');
        $dateCondition($onTimeMilestones, 'updated_at');
        $onTimeMilestones = $onTimeMilestones->count();

        $onTimeRate = $totalCompletedMilestones > 0 ? round(($onTimeMilestones / $totalCompletedMilestones) * 100, 1) : 0;

        // Completion trends within date range
        if ($dateFrom && $dateTo) {
            $start = \Carbon\Carbon::parse($dateFrom)->startOfMonth();
            $end   = \Carbon\Carbon::parse($dateTo)->startOfMonth();
        } else {
            $start = now()->subMonths(11)->startOfMonth();
            $end   = now()->startOfMonth();
        }

        $completionTrends = [];
        $trendCurrent = $start->copy();
        while ($trendCurrent <= $end) {
            $newCount = DB::table('projects')
                ->whereYear('created_at', $trendCurrent->year)
                ->whereMonth('created_at', $trendCurrent->month)
                ->count();
            $compCount = DB::table('projects')
                ->where('project_status', 'completed')
                ->whereYear('updated_at', $trendCurrent->year)
                ->whereMonth('updated_at', $trendCurrent->month)
                ->count();
            $completionTrends[] = ['month' => $trendCurrent->format('M Y'), 'new' => $newCount, 'completed' => $compCount];
            $trendCurrent->addMonth();
        }

        $byPropQ = DB::table('projects')->whereNotIn('project_status', ['deleted', 'deleted_post']);
        $dateCondition($byPropQ);
        $byPropertyType = $byPropQ->select('property_type', DB::raw('COUNT(*) as count'))
            ->groupBy('property_type')->get()->pluck('count', 'property_type')->toArray();

        // Average bids per project
        $avgBidsQ = DB::table('bids');
        $dateCondition($avgBidsQ, 'submitted_at');
        $avgBidsPerProject = $avgBidsQ->selectRaw('AVG(bid_count) as avg')
            ->from(DB::raw('(SELECT COUNT(*) as bid_count FROM bids' .
                ($dateFrom ? ' WHERE submitted_at >= \'' . addslashes($dateFrom) . '\'' : '') .
                ($dateTo ? ($dateFrom ? ' AND' : ' WHERE') . ' submitted_at <= \'' . addslashes($dateTo) . ' 23:59:59\'' : '') .
                ' GROUP BY project_id) as t'))
            ->value('avg') ?? 0;

        // MoM project growth
        $lastMonth = now()->subMonth();
        $lastMonthProjects = DB::table('project_relationships')
            ->whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->count();
        $thisMonthProjects = DB::table('project_relationships')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $momGrowth = $lastMonthProjects > 0
            ? round((($thisMonthProjects - $lastMonthProjects) / $lastMonthProjects) * 100, 1)
            : 0;

        // Value MoM growth
        $lastMonthValue = DB::table('bids')->where('bid_status', 'accepted')
            ->whereYear('decision_date', $lastMonth->year)
            ->whereMonth('decision_date', $lastMonth->month)
            ->sum('proposed_cost');
        $thisMonthValue = DB::table('bids')->where('bid_status', 'accepted')
            ->whereYear('decision_date', now()->year)
            ->whereMonth('decision_date', now()->month)
            ->sum('proposed_cost');
        $valueMomGrowth = $lastMonthValue > 0
            ? round((($thisMonthValue - $lastMonthValue) / $lastMonthValue) * 100, 1)
            : 0;

        return [
            'total_projects'       => $totalProjects,
            'completed_projects'   => $completedProjects,
            'total_bids'           => $totalBids,
            'accepted_bids'        => $acceptedBids,
            'total_value'          => $totalValue,
            'avg_bids_per_project' => round($avgBidsPerProject, 1),
            'avg_duration'         => round($avgDuration),
            'completion_rate'      => $completionRate,
            'on_time_rate'         => $onTimeRate,
            'mom_growth'           => $momGrowth,
            'value_mom_growth'     => $valueMomGrowth,
            'completion_trends'    => $completionTrends,
            'by_property_type'     => $byPropertyType,
        ];
    }

    private function getBidMetricsFiltered($dateFrom, $dateTo): array
    {
        $dc = function ($q, $col = 'submitted_at') use ($dateFrom, $dateTo) {
            if ($dateFrom) $q->where($col, '>=', $dateFrom);
            if ($dateTo)   $q->where($col, '<=', $dateTo . ' 23:59:59');
        };

        $tQ = DB::table('bids'); $dc($tQ); $total = $tQ->count();
        $aQ = DB::table('bids')->where('bid_status', 'accepted'); $dc($aQ); $accepted = $aQ->count();
        $rQ = DB::table('bids')->where('bid_status', 'rejected'); $dc($rQ); $rejected = $rQ->count();
        $pQ = DB::table('bids')->whereIn('bid_status', ['submitted', 'under_review']); $dc($pQ); $pending = $pQ->count();
        $cQ = DB::table('bids')->where('bid_status', 'cancelled'); $dc($cQ); $cancelled = $cQ->count();

        // Average bids per project
        $avgQ = DB::table('bids');
        $dc($avgQ);
        $avgPerProject = $avgQ->selectRaw('AVG(bid_count) as avg')
            ->from(DB::raw('(SELECT COUNT(*) as bid_count FROM bids' .
                ($dateFrom ? ' WHERE submitted_at >= \'' . addslashes($dateFrom) . '\'' : '') .
                ($dateTo ? ($dateFrom ? ' AND' : ' WHERE') . ' submitted_at <= \'' . addslashes($dateTo) . ' 23:59:59\'' : '') .
                ' GROUP BY project_id) as t'))
            ->value('avg') ?? 0;

        return [
            'total'           => $total,
            'accepted'        => $accepted,
            'rejected'        => $rejected,
            'pending'         => $pending,
            'cancelled'       => $cancelled,
            'avg_per_project' => round($avgPerProject, 1),
            'acceptance_rate' => $total > 0 ? round(($accepted / $total) * 100, 1) : 0,
        ];
    }

    private function getTopContractorsFiltered($dateFrom, $dateTo, $search = ''): array
    {
        $assignedSub = DB::table('projects')
            ->select('selected_contractor_id', DB::raw('COUNT(*) as assigned_count'))
            ->whereNotNull('selected_contractor_id')
            ->whereNotIn('project_status', ['deleted', 'deleted_post']);
        if ($dateFrom) $assignedSub->where('created_at', '>=', $dateFrom);
        if ($dateTo)   $assignedSub->where('created_at', '<=', $dateTo . ' 23:59:59');
        $assignedSub = $assignedSub->groupBy('selected_contractor_id');

        $ratingSub = DB::table('reviews as r')
            ->join('property_owners as rev_po', 'rev_po.user_id', '=', 'r.reviewee_user_id')
            ->join('contractors as rc', 'rc.owner_id', '=', 'rev_po.owner_id')
            ->select(
                'rc.contractor_id',
                DB::raw('ROUND(AVG(r.rating), 2) as avg_rating'),
                DB::raw('COUNT(r.review_id) as review_count')
            );
        if ($dateFrom) $ratingSub->where('r.created_at', '>=', $dateFrom);
        if ($dateTo)   $ratingSub->where('r.created_at', '<=', $dateTo . ' 23:59:59');
        $ratingSub = $ratingSub->groupBy('rc.contractor_id');

        $rows = DB::table('contractors as c')
            ->join('property_owners as c_po', 'c.owner_id', '=', 'c_po.owner_id')
            ->join('users as c_u', 'c_po.user_id', '=', 'c_u.user_id')
            ->leftJoinSub($assignedSub, 'ap', 'ap.selected_contractor_id', '=', 'c.contractor_id')
            ->leftJoinSub($ratingSub,   'rev', 'rev.contractor_id',         '=', 'c.contractor_id')
            ->where('c.verification_status', 'approved')
            ->where('c.is_active', 1)
            ->select(
                'c.contractor_id',
                'c.company_name',
                'c.completed_projects',
                'c.years_of_experience',
                'c.company_logo',
                DB::raw("CONCAT(c_u.first_name, ' ', c_u.last_name) as rep_name"),
                DB::raw('IFNULL(ap.assigned_count, 0) as assigned_count'),
                DB::raw('IFNULL(rev.avg_rating, 0) as avg_rating'),
                DB::raw('IFNULL(rev.review_count, 0) as review_count')
            );

        if ($search !== '') {
            $like = "%{$search}%";
            $rows->where(function ($q) use ($like) {
                $q->where('c.company_name', 'LIKE', $like)
                  ->orWhere(DB::raw("CONCAT(c_u.first_name, ' ', c_u.last_name)"), 'LIKE', $like);
            });
        }

        $rows = $rows->orderByDesc('c.completed_projects')->limit(5)->get();

        return $rows->map(function ($r, $i) {
            $baseScore = $r->assigned_count > 0
                ? min(($r->completed_projects / $r->assigned_count) * 50, 50)
                : ($r->completed_projects > 0 ? 50 : 0);
            $ratingBonus = $r->avg_rating > 0 ? round((($r->avg_rating - 1) / 4) * 10, 1) : 0;
            $finalScore = round(max(0, min(100, $baseScore + $ratingBonus)), 1);

            return [
                'rank'                => $i + 1,
                'contractor_id'       => $r->contractor_id,
                'company_name'        => $r->company_name,
                'rep_name'            => $r->rep_name,
                'completed_projects'  => $r->completed_projects,
                'success_rate'        => $finalScore,
                'avg_rating'          => $r->avg_rating,
                'review_count'        => $r->review_count,
                'years_of_experience' => $r->years_of_experience,
                'company_logo'        => $r->company_logo ? asset('storage/' . $r->company_logo) : null,
                'initials'            => strtoupper(substr($r->company_name, 0, 2)),
            ];
        })->toArray();
    }

    private function getSubscriptionMetricsFiltered($dateFrom, $dateTo): array
    {
        $base = function () use ($dateFrom, $dateTo) {
            $q = DB::table('platform_payments as pp')
                ->join('subscription_plans as sp', 'sp.id', '=', 'pp.subscriptionPlanId')
                ->where('sp.for_contractor', 1)
                ->where('sp.plan_key', '!=', 'boost')
                ->where('pp.is_approved', 1)
                ->where('pp.is_cancelled', 0);
            if ($dateFrom) $q->where('pp.transaction_date', '>=', $dateFrom);
            if ($dateTo)   $q->where('pp.transaction_date', '<=', $dateTo . ' 23:59:59');
            return $q;
        };

        $total   = $base()->count();
        $revenue = (float) $base()->sum('pp.amount');
        $active  = $base()->where(fn($q) => $q->whereNull('pp.expiration_date')->orWhereRaw('pp.expiration_date >= NOW()'))->count();
        $expiring = $base()->whereNotNull('pp.expiration_date')->whereRaw('pp.expiration_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)')->count();
        $expired = $base()->whereNotNull('pp.expiration_date')->whereRaw('pp.expiration_date < NOW()')->count();

        // This month's subscriptions
        $thisMonth = DB::table('platform_payments as pp')
            ->join('subscription_plans as sp', 'sp.id', '=', 'pp.subscriptionPlanId')
            ->where('sp.for_contractor', 1)->where('sp.plan_key', '!=', 'boost')
            ->where('pp.is_approved', 1)->where('pp.is_cancelled', 0)
            ->whereYear('pp.transaction_date', now()->year)
            ->whereMonth('pp.transaction_date', now()->month)
            ->count();

        // Last month's subscriptions
        $lastMonthDate = now()->subMonth();
        $lastMonth = DB::table('platform_payments as pp')
            ->join('subscription_plans as sp', 'sp.id', '=', 'pp.subscriptionPlanId')
            ->where('sp.for_contractor', 1)->where('sp.plan_key', '!=', 'boost')
            ->where('pp.is_approved', 1)->where('pp.is_cancelled', 0)
            ->whereYear('pp.transaction_date', $lastMonthDate->year)
            ->whereMonth('pp.transaction_date', $lastMonthDate->month)
            ->count();

        $momGrowth = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : 0;

        return [
            'total'      => $total,
            'active'     => $active,
            'revenue'    => $revenue,
            'expiring'   => $expiring,
            'expired'    => $expired,
            'this_month' => $thisMonth,
            'mom_growth' => $momGrowth,
        ];
    }

    private function getSubscriptionTiersFiltered($dateFrom, $dateTo): array
    {
        $q = DB::table('platform_payments as pp')
            ->join('subscription_plans as sp', 'sp.id', '=', 'pp.subscriptionPlanId')
            ->where('pp.is_approved', 1)
            ->where('pp.is_cancelled', 0)
            ->whereIn('sp.plan_key', ['gold', 'silver', 'bronze']);
        if ($dateFrom) $q->where('pp.transaction_date', '>=', $dateFrom);
        if ($dateTo)   $q->where('pp.transaction_date', '<=', $dateTo . ' 23:59:59');
        $counts = $q->select('sp.plan_key', DB::raw('COUNT(*) as cnt'))->groupBy('sp.plan_key')->pluck('cnt', 'plan_key');

        $gold   = (int)($counts['gold']   ?? 0);
        $silver = (int)($counts['silver'] ?? 0);
        $bronze = (int)($counts['bronze'] ?? 0);

        return [
            'tiers' => [
                ['name' => 'Gold Tier',   'label' => 'Gold',   'count' => $gold,   'color' => 'yellow', 'gradient' => 'from-yellow-400 to-yellow-600'],
                ['name' => 'Silver Tier', 'label' => 'Silver', 'count' => $silver, 'color' => 'blue',   'gradient' => 'from-gray-300 to-gray-500'],
                ['name' => 'Bronze Tier', 'label' => 'Bronze', 'count' => $bronze, 'color' => 'orange', 'gradient' => 'from-orange-400 to-orange-600'],
            ],
            'total'    => $gold + $silver + $bronze,
            'maxCount' => max($gold, $silver, $bronze, 1),
        ];
    }

    private function getSubscriptionRevenueFiltered($dateFrom, $dateTo): array
    {
        $currentYear      = (int) date('Y');
        $previousYear     = $currentYear - 1;
        $months           = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $currentYearData  = array_fill(0, 12, 0.0);
        $previousYearData = array_fill(0, 12, 0.0);

        $base = fn() => DB::table('platform_payments as pp')
            ->join('subscription_plans as sp', 'sp.id', '=', 'pp.subscriptionPlanId')
            ->where('sp.for_contractor', 1)
            ->where('sp.plan_key', '!=', 'boost')
            ->where('pp.is_approved', 1)
            ->where('pp.is_cancelled', 0);

        // Current year — only apply date filter, no extra whereYear
        $cur = $base()->select(
            DB::raw('MONTH(pp.transaction_date) as m'),
            DB::raw('IFNULL(SUM(pp.amount), 0) as sum')
        )->whereYear('pp.transaction_date', $currentYear);

        // Previous year — only apply date filter, no extra whereYear
        $prev = $base()->select(
            DB::raw('MONTH(pp.transaction_date) as m'),
            DB::raw('IFNULL(SUM(pp.amount), 0) as sum')
        )->whereYear('pp.transaction_date', $previousYear);

        foreach ($cur->groupByRaw('MONTH(pp.transaction_date)')
                     ->orderByRaw('MONTH(pp.transaction_date)')
                     ->get() as $r) {
            $currentYearData[(int)$r->m - 1] = (float) $r->sum;
        }

        foreach ($prev->groupByRaw('MONTH(pp.transaction_date)')
                      ->orderByRaw('MONTH(pp.transaction_date)')
                      ->get() as $r) {
            $previousYearData[(int)$r->m - 1] = (float) $r->sum;
        }

        return [
            'months'           => $months,
            'currentYearData'  => $currentYearData,
            'previousYearData' => $previousYearData,
            'currentYear'      => $currentYear,
            'previousYear'     => $previousYear,
            'dateRange'        => ($dateFrom ?? now()->startOfYear()->format('Y-m-d')) . ' — ' . ($dateTo ?? now()->format('Y-m-d')),
        ];
    }

    private function getUserMetricsFiltered($dateFrom, $dateTo): array
    {
        $dc = function ($q, $col = 'created_at') use ($dateFrom, $dateTo) {
            if ($dateFrom) $q->where($col, '>=', $dateFrom);
            if ($dateTo)   $q->where($col, '<=', $dateTo . ' 23:59:59');
        };

        $totalQ = DB::table('users'); $dc($totalQ); $totalUsers = $totalQ->count();
        $poQ    = DB::table('property_owners'); $dc($poQ, 'created_at'); $propertyOwners = $poQ->count();
        $cntQ   = DB::table('contractors'); $dc($cntQ, 'created_at');    $contractors = $cntQ->count();

        $activeProjectsQ = DB::table('projects')->where('project_status', 'in_progress');
        $dc($activeProjectsQ, 'created_at');
        $activeProjects = $activeProjectsQ->count();

        $activeUsers = DB::table('users')->where(function ($q) {
            $q->whereExists(fn($s) => $s->select(DB::raw(1))->from('property_owners')->whereColumn('property_owners.user_id', 'users.user_id')->where('property_owners.is_active', 1))
              ->orWhereExists(fn($s) => $s->select(DB::raw(1))->from('contractors')->join('property_owners as cp', 'contractors.owner_id', '=', 'cp.owner_id')->whereColumn('cp.user_id', 'users.user_id')->where('contractors.is_active', 1));
        });
        $dc($activeUsers);
        $activeUsers = $activeUsers->count();

        $suspendedUsers = DB::table('users')->where(function ($q) {
            $q->whereExists(fn($s) => $s->select(DB::raw(1))->from('property_owners')->whereColumn('property_owners.user_id', 'users.user_id')->where('property_owners.is_active', 0))
              ->orWhereExists(fn($s) => $s->select(DB::raw(1))->from('contractors')->join('property_owners as cp2', 'contractors.owner_id', '=', 'cp2.owner_id')->whereColumn('cp2.user_id', 'users.user_id')->where('contractors.is_active', 0));
        });
        $dc($suspendedUsers);
        $suspendedUsers = $suspendedUsers->count();

        // New users this month and last month
        $newThisMonth = DB::table('users')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $lastMonthDate = now()->subMonth();
        $newLastMonth = DB::table('users')
            ->whereYear('created_at', $lastMonthDate->year)
            ->whereMonth('created_at', $lastMonthDate->month)
            ->count();
        $momChange = $newLastMonth > 0
            ? round((($newThisMonth - $newLastMonth) / $newLastMonth) * 100, 1)
            : 0;

        return [
            'total_users'     => $totalUsers,
            'property_owners' => $propertyOwners,
            'contractors'     => $contractors,
            'active_projects' => $activeProjects,
            'active_users'    => $activeUsers,
            'suspended_users' => $suspendedUsers,
            'new_this_month'  => $newThisMonth,
            'new_last_month'  => $newLastMonth,
            'mom_change'      => $momChange,
        ];
    }

    private function getUserGrowthDataFiltered($dateFrom, $dateTo): array
    {
        if ($dateFrom && $dateTo) {
            $start = \Carbon\Carbon::parse($dateFrom)->startOfMonth();
            $end   = \Carbon\Carbon::parse($dateTo)->startOfMonth();
        } else {
            $start = now()->subMonths(11)->startOfMonth();
            $end   = now()->startOfMonth();
        }

        $months = $ownersData = $contractorsData = $totalData = [];
        $current = $start->copy();

        while ($current <= $end) {
            $months[] = $current->format('M Y');
            $owners   = DB::table('users')->whereIn('user_type', ['property_owner', 'both'])->whereYear('created_at', $current->year)->whereMonth('created_at', $current->month)->count();
            $contrs   = DB::table('users')->whereIn('user_type', ['contractor', 'both'])->whereYear('created_at', $current->year)->whereMonth('created_at', $current->month)->count();
            $ownersData[]      = $owners;
            $contractorsData[] = $contrs;
            $totalData[]       = $owners + $contrs;
            $current->addMonth();
        }

        $distBase = function ($type) use ($dateFrom, $dateTo) {
            $q = DB::table('users')->where('user_type', $type);
            if ($dateFrom) $q->where('created_at', '>=', $dateFrom);
            if ($dateTo)   $q->where('created_at', '<=', $dateTo . ' 23:59:59');
            return $q->count();
        };

        return [
            'months'       => $months,
            'owners'       => $ownersData,
            'contractors'  => $contractorsData,
            'totals'       => $totalData,
            'distribution' => [
                'Property Owner' => $distBase('property_owner'),
                'Contractor'     => $distBase('contractor'),
                'Both'           => $distBase('both'),
                'Staff'          => $distBase('staff'),
            ],
        ];
    }

    private function getRecentUserActivityFiltered($dateFrom, $dateTo, $search = '', $page = 1, $perPage = 10): array
    {
        $like = $search !== '' ? "%{$search}%" : null;

        $recentBids = DB::table('bids')
            ->join('contractors', 'contractors.contractor_id', '=', 'bids.contractor_id')
            ->join('property_owners as bid_po', 'contractors.owner_id', '=', 'bid_po.owner_id')
            ->join('users', 'bid_po.user_id', '=', 'users.user_id')
            ->select('users.user_id', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as full_name"), 'users.email', 'users.user_type', DB::raw("'Submitted a bid' as action"), 'bids.submitted_at as activity_time', 'bid_po.profile_pic', 'contractors.is_active');
        if ($dateFrom) $recentBids->where('bids.submitted_at', '>=', $dateFrom);
        if ($dateTo)   $recentBids->where('bids.submitted_at', '<=', $dateTo . ' 23:59:59');
        if ($like) $recentBids->where(function ($q) use ($like) {
            $q->where(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'LIKE', $like)->orWhere('users.email', 'LIKE', $like);
        });

        $recentProjects = DB::table('project_relationships')
            ->join('property_owners', 'property_owners.owner_id', '=', 'project_relationships.owner_id')
            ->join('users', 'users.user_id', '=', 'property_owners.user_id')
            ->select('users.user_id', DB::raw("CONCAT(users.first_name, ' ', users.last_name) as full_name"), 'users.email', 'users.user_type', DB::raw("'Posted a project' as action"), 'project_relationships.created_at as activity_time', 'property_owners.profile_pic', 'property_owners.is_active');
        if ($dateFrom) $recentProjects->where('project_relationships.created_at', '>=', $dateFrom);
        if ($dateTo)   $recentProjects->where('project_relationships.created_at', '<=', $dateTo . ' 23:59:59');
        if ($like) $recentProjects->where(function ($q) use ($like) {
            $q->where(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'LIKE', $like)->orWhere('users.email', 'LIKE', $like);
        });

        $allActivities = collect(
            DB::query()->fromSub(
                $recentBids->unionAll($recentProjects),
                'combined'
            )->orderByDesc('activity_time')->get()
        );

        $total   = $allActivities->count();
        $sliced  = $allActivities->slice(($page - 1) * $perPage, $perPage)->values();

        $items = $sliced->map(function ($a) {
            $initials = collect(explode(' ', $a->full_name ?? 'U N'))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
            $timeAgo  = \Carbon\Carbon::parse($a->activity_time)->diffForHumans();
            $typeLabel = match($a->user_type ?? '') {
                'property_owner' => 'Property Owner',
                'contractor'     => 'Contractor',
                'both'           => 'Both',
                default          => ucfirst($a->user_type ?? 'User'),
            };
            return [
                'full_name'   => $a->full_name,
                'email'       => $a->email,
                'user_type'   => $a->user_type,
                'type_label'  => $typeLabel,
                'action'      => $a->action,
                'time_ago'    => $timeAgo,
                'is_active'   => (bool) $a->is_active,
                'initials'    => $initials,
                'profile_pic' => $a->profile_pic ? asset('storage/' . $a->profile_pic) : null,
            ];
        });

        return [
            'data'         => $items->toArray(),
            'current_page' => $page,
            'last_page'    => max(1, (int) ceil($total / $perPage)),
            'per_page'     => $perPage,
            'total'        => $total,
            'from'         => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to'           => min($page * $perPage, $total),
        ];
    }

    private function getBidCompletionDataFiltered($dateFrom, $dateTo): array
    {
        $dc = function ($q, $col = 'submitted_at') use ($dateFrom, $dateTo) {
            if ($dateFrom) $q->where($col, '>=', $dateFrom);
            if ($dateTo)   $q->where($col, '<=', $dateTo . ' 23:59:59');
        };

        $totalProjQ = DB::table('projects')->whereNotIn('project_status', ['deleted', 'deleted_post']);
        if ($dateFrom) $totalProjQ->where('created_at', '>=', $dateFrom);
        if ($dateTo)   $totalProjQ->where('created_at', '<=', $dateTo . ' 23:59:59');
        $totalProjects = $totalProjQ->count();

        // MoM project growth
        $lastMonth = now()->subMonth();
        $thisMonthProjects = DB::table('project_relationships')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $lastMonthProjects = DB::table('project_relationships')
            ->whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->count();
        $projectsMoM = $lastMonthProjects > 0
            ? round((($thisMonthProjects - $lastMonthProjects) / $lastMonthProjects) * 100, 1)
            : 0;

        // Active contractors — apply date filter
        $activeContractorsQ = DB::table('contractors')->where('verification_status', 'approved')->where('is_active', 1);
        if ($dateFrom) $activeContractorsQ->where('created_at', '>=', $dateFrom);
        if ($dateTo)   $activeContractorsQ->where('created_at', '<=', $dateTo . ' 23:59:59');
        $activeContractors = $activeContractorsQ->count();

        // Contractor completion rate
        $totalCompleted = DB::table('contractors')
            ->where('verification_status', 'approved')
            ->where('is_active', 1)
            ->sum('completed_projects');
        $maxCompleted = DB::table('contractors')->where('is_active', 1)->max('completed_projects') ?: 1;
        $contractorCompletionRate = $activeContractors > 0
            ? round(($totalCompleted / ($activeContractors * $maxCompleted)) * 100, 1)
            : 0;

        $bidsQ = DB::table('bids'); $dc($bidsQ); $totalBids = $bidsQ->count();
        $accQ  = DB::table('bids')->where('bid_status', 'accepted'); $dc($accQ); $acceptedBids = $accQ->count();
        $completionRate = $totalBids > 0 ? round(($acceptedBids / $totalBids) * 100, 1) : 0;

        $totalValQ = DB::table('bids')->where('bid_status', 'accepted'); $dc($totalValQ);
        $totalContractedValue = $totalValQ->sum('proposed_cost');
        $totalValueM = round($totalContractedValue / 1_000_000, 1);

        // Timeline
        if ($dateFrom && $dateTo) {
            $start = \Carbon\Carbon::parse($dateFrom)->startOfMonth();
            $end   = \Carbon\Carbon::parse($dateTo)->startOfMonth();
        } else {
            $start = now()->subMonths(11)->startOfMonth();
            $end   = now()->startOfMonth();
        }

        $timelineMonths = $timelineSubmitted = $timelineAccepted = [];
        $tc = $start->copy();
        while ($tc <= $end) {
            $timelineMonths[] = $tc->format('M');
            $tsQ = DB::table('bids')->whereYear('submitted_at', $tc->year)->whereMonth('submitted_at', $tc->month);
            $timelineSubmitted[] = $tsQ->count();
            $taQ = DB::table('bids')->where('bid_status', 'accepted')->whereYear('submitted_at', $tc->year)->whereMonth('submitted_at', $tc->month);
            $timelineAccepted[] = $taQ->count();
            $tc->addMonth();
        }

        // Bid Status Distribution
        $bidStatusCounts = [];
        foreach (['accepted' => 'Accepted', 'submitted' => 'Submitted', 'under_review' => 'Under Review', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'] as $status => $label) {
            $q = DB::table('bids')->where('bid_status', $status); $dc($q);
            $cnt = $q->count();
            if ($cnt > 0) $bidStatusCounts[$label] = $cnt;
        }

        // Bid metrics
        $avgBidQ = DB::table('bids'); $dc($avgBidQ);
        $avgBidValue = $avgBidQ->avg('proposed_cost') ?? 0;
        $avgBidValueK = round($avgBidValue / 1000, 1);

        $respQ = DB::table('bids')->whereNotNull('decision_date')->whereIn('bid_status', ['accepted', 'rejected']); $dc($respQ);
        $avgResponseHours = round($respQ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, submitted_at, decision_date)) as avg_hours')->value('avg_hours') ?? 0, 1);

        $bidWinRate = $totalBids > 0 ? round(($acceptedBids / $totalBids) * 100, 1) : 0;
        $winRateBarWidth = min($bidWinRate, 100);
        $responseBarWidth = max(0, round(100 - ($avgResponseHours / 72) * 100));
        $maxBidVal = DB::table('bids')->max('proposed_cost') ?? 1;
        $avgBidBarWidth = min(round(($avgBidValue / $maxBidVal) * 100), 100);

        // Geographic distribution
        $districts = ['Tetuan', 'Tumaga', 'Sinunuc', 'Malagutay', 'Baliwasan', 'Upper Calarian'];
        $geoLabels = $geoCounts = [];
        foreach ($districts as $district) {
            $q = DB::table('projects')->whereNotIn('project_status', ['deleted', 'deleted_post'])->where('project_location', 'LIKE', "%{$district}%");
            if ($dateFrom) $q->where('created_at', '>=', $dateFrom);
            if ($dateTo)   $q->where('created_at', '<=', $dateTo . ' 23:59:59');
            $geoLabels[] = $district;
            $geoCounts[] = $q->count();
        }
        $geoLabels[] = 'Others';
        $geoCounts[] = max(0, $totalProjects - array_sum($geoCounts));

        // Recent bids table
        $recentBidsQ = DB::table('bids as b')
            ->join('projects as p', 'p.project_id', '=', 'b.project_id')
            ->join('contractors as c', 'c.contractor_id', '=', 'b.contractor_id')
            ->join('property_owners as bid_c_po', 'c.owner_id', '=', 'bid_c_po.owner_id')
            ->join('users as bid_c_u', 'bid_c_po.user_id', '=', 'bid_c_u.user_id')
            ->select(
                'b.bid_id', 'b.proposed_cost', 'b.bid_status', 'b.submitted_at',
                'p.project_title', 'p.project_location',
                'c.company_name', 'c.company_logo',
                DB::raw("CONCAT(bid_c_u.first_name, ' ', bid_c_u.last_name) as rep_name"),
                DB::raw("UPPER(LEFT(c.company_name, 2)) as initials")
            );
        $dc($recentBidsQ, 'b.submitted_at');
        $recentBids = $recentBidsQ->orderByDesc('b.submitted_at')->limit(10)->get()->map(function ($b) {
            return [
                'bid_id'           => $b->bid_id,
                'proposed_cost'    => $b->proposed_cost,
                'bid_status'       => $b->bid_status,
                'submitted_at'     => $b->submitted_at,
                'submitted_ago'    => \Carbon\Carbon::parse($b->submitted_at)->diffForHumans(),
                'project_title'    => $b->project_title,
                'project_location' => $b->project_location,
                'company_name'     => $b->company_name,
                'company_logo'     => $b->company_logo ? asset('storage/' . $b->company_logo) : null,
                'rep_name'         => $b->rep_name,
                'initials'         => $b->initials,
            ];
        });

        // Owner activity
        $ownerActQ = DB::table('bids as b')
            ->join('projects as p', 'p.project_id', '=', 'b.project_id')
            ->join('contractors as c', 'c.contractor_id', '=', 'b.contractor_id')
            ->select('p.project_title', 'p.project_location', 'c.company_name', 'b.proposed_cost', 'b.bid_status')
            ->whereIn('b.bid_status', ['accepted', 'submitted', 'under_review']);
        $dc($ownerActQ, 'b.submitted_at');
        $ownerActivity = $ownerActQ->orderByDesc('b.submitted_at')->limit(6)->get();

        // Payment analytics
        $pmQ1 = DB::table('milestone_payments')->where('payment_status', 'approved');
        if ($dateFrom) $pmQ1->where('transaction_date', '>=', $dateFrom);
        if ($dateTo)   $pmQ1->where('transaction_date', '<=', $dateTo . ' 23:59:59');
        $paymentsReleasedM = round($pmQ1->sum('amount') / 1_000_000, 1);

        $pendingPaymentsQ = DB::table('milestone_payments')->where('payment_status', 'submitted');
        if ($dateFrom) $pendingPaymentsQ->where('transaction_date', '>=', $dateFrom);
        if ($dateTo)   $pendingPaymentsQ->where('transaction_date', '<=', $dateTo . ' 23:59:59');
        $pendingPaymentsM = round($pendingPaymentsQ->sum('amount') / 1_000_000, 1);

        $avgPayDaysQ = DB::table('milestone_payments')->where('payment_status', 'approved')->whereNotNull('updated_at');
        if ($dateFrom) $avgPayDaysQ->where('transaction_date', '>=', $dateFrom);
        if ($dateTo)   $avgPayDaysQ->where('transaction_date', '<=', $dateTo . ' 23:59:59');
        $avgPaymentDays = round($avgPayDaysQ->selectRaw('AVG(DATEDIFF(updated_at, transaction_date)) as avg_days')->value('avg_days') ?? 0, 1);

        $approvedPaymentsQ = DB::table('milestone_payments')->where('payment_status', 'approved');
        if ($dateFrom) $approvedPaymentsQ->where('transaction_date', '>=', $dateFrom);
        if ($dateTo)   $approvedPaymentsQ->where('transaction_date', '<=', $dateTo . ' 23:59:59');
        $approvedPayments = $approvedPaymentsQ->count();

        $rejectedPaymentsQ = DB::table('milestone_payments')->where('payment_status', 'rejected');
        if ($dateFrom) $rejectedPaymentsQ->where('transaction_date', '>=', $dateFrom);
        if ($dateTo)   $rejectedPaymentsQ->where('transaction_date', '<=', $dateTo . ' 23:59:59');
        $rejectedPayments = $rejectedPaymentsQ->count();

        $paymentSuccessRate = ($approvedPayments + $rejectedPayments) > 0
            ? round(($approvedPayments / ($approvedPayments + $rejectedPayments)) * 100, 1) : 0;

        // MoM completion rate change
        $thisMonthBids     = DB::table('bids')->whereYear('submitted_at', now()->year)->whereMonth('submitted_at', now()->month)->count();
        $thisMonthAccepted = DB::table('bids')->where('bid_status', 'accepted')->whereYear('submitted_at', now()->year)->whereMonth('submitted_at', now()->month)->count();
        $lastMonthBids     = DB::table('bids')->whereYear('submitted_at', $lastMonth->year)->whereMonth('submitted_at', $lastMonth->month)->count();
        $lastMonthAccepted = DB::table('bids')->where('bid_status', 'accepted')->whereYear('submitted_at', $lastMonth->year)->whereMonth('submitted_at', $lastMonth->month)->count();
        $thisRate = $thisMonthBids > 0 ? ($thisMonthAccepted / $thisMonthBids) * 100 : 0;
        $lastRate = $lastMonthBids > 0 ? ($lastMonthAccepted / $lastMonthBids) * 100 : 0;
        $completionRateMoM = $lastRate > 0 ? round($thisRate - $lastRate, 1) : 0;

        // Four districts
        $fourDistrictNames = ['Tetuan', 'Tumaga', 'Malagutay', 'Others'];
        $fourDistricts = [];
        foreach ($fourDistrictNames as $name) {
            if ($name === 'Others') {
                $idx = array_search('Others', $geoLabels);
                $fourDistricts[$name] = ['count' => $idx !== false ? $geoCounts[$idx] : 0, 'value' => 0];
            } else {
                $idx = array_search($name, $geoLabels);
                $count = $idx !== false ? $geoCounts[$idx] : 0;
                $valQ = DB::table('projects')->whereNotIn('project_status', ['deleted', 'deleted_post'])->where('project_location', 'LIKE', "%{$name}%");
                if ($dateFrom) $valQ->where('created_at', '>=', $dateFrom);
                if ($dateTo)   $valQ->where('created_at', '<=', $dateTo . ' 23:59:59');
                $value = DB::table('bids')->where('bid_status', 'accepted')
                    ->whereIn('project_id', $valQ->select('project_id'))
                    ->sum('proposed_cost');
                $fourDistricts[$name] = ['count' => $count, 'value' => round($value / 1_000_000, 1)];
            }
        }

        return [
            'totalProjects'           => $totalProjects,
            'projectsMoM'             => $projectsMoM,
            'activeContractors'       => $activeContractors,
            'contractorCompletionRate'=> $contractorCompletionRate,
            'totalBids'               => $totalBids,
            'totalValueM'             => $totalValueM,
            'completionRate'          => $completionRate,
            'completionRateMoM'       => $completionRateMoM,
            'timelineMonths'          => $timelineMonths,
            'timelineSubmitted'       => $timelineSubmitted,
            'timelineAccepted'        => $timelineAccepted,
            'bidStatusCounts'         => $bidStatusCounts,
            'avgBidValueK'            => $avgBidValueK,
            'avgResponseHours'        => $avgResponseHours,
            'bidWinRate'              => $bidWinRate,
            'avgBidBarWidth'          => $avgBidBarWidth,
            'responseBarWidth'        => $responseBarWidth,
            'winRateBarWidth'         => $winRateBarWidth,
            'geoLabels'               => $geoLabels,
            'geoCounts'               => $geoCounts,
            'fourDistricts'           => $fourDistricts,
            'recentBids'              => $recentBids,
            'ownerActivity'           => $ownerActivity,
            'paymentsReleasedM'       => $paymentsReleasedM,
            'pendingPaymentsM'        => $pendingPaymentsM,
            'avgPaymentDays'          => $avgPaymentDays,
            'paymentSuccessRate'      => $paymentSuccessRate,
        ];
    }
}

