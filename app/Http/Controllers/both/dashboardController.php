<?php

namespace App\Http\Controllers\both;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * DashboardController — Dedicated controller for dashboard views (per role).
 *
 * Replaces the dashboard methods previously scattered across:
 *   • owner\projectsController     ::showDashboard(), showOwnerDashboard()
 *   • contractor\cprocessController ::showDashboard()
 *
 * Web routes  → Blade views (session auth)
 * API routes  → JSON responses (bearer-token or session auth)
 *
 * NOTE: Admin dashboard routes are NOT managed here — they stay in
 *       Admin\dashboardController.
 */
class dashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /* =====================================================================
     * WEB — Owner Dashboard  (Blade)
     * ===================================================================== */

    /**
     * GET /owner/dashboard
     *
     * Show the property-owner-specific dashboard.
     * The Blade view (owner.propertyOwner_Dashboard) currently receives no
     * PHP variables — it is rendered client-side via JS. This controller
     * preserves that behaviour but the DashboardService payload is available
     * if the view is migrated later.
     */
    public function ownerDashboard(Request $request)
    {
        $isLocalOrTesting = App::environment(['local', 'testing', 'development']);
        $user = Session::get('user');

        if (!$isLocalOrTesting && !$user) {
            return redirect('/accounts/login');
        }

        // Allow unauthenticated access in local/testing
        if ($isLocalOrTesting && !$user) {
            return view('owner.propertyOwner_Dashboard', [
                'stats' => ['total' => 0, 'pending' => 0, 'active' => 0, 'inProgress' => 0, 'completed' => 0],
            ]);
        }

        // Role guard
        $currentRole = session('current_role', $user->user_type ?? null);
        $userType = $user->user_type ?? null;

        // Fetch owner record to check verification status
        $ownerRecord = DB::table('property_owners')->where('user_id', $user->user_id)->first();
        $verificationStatus = $ownerRecord->verification_status ?? null;

        // Require approved owner status for owner dashboard. Allow access when
        // session role is owner and there is an approved property_owner record,
        // even if the user's `user_type` hasn't been updated to 'property_owner'/'both'.
        $hasApprovedOwnerRecord = ($verificationStatus === 'approved');
        $isOwnerStrict = in_array($currentRole, ['owner', 'property_owner']) &&
            ($hasApprovedOwnerRecord || in_array($userType, ['property_owner', 'both']));

        if (!$isOwnerStrict && !$isLocalOrTesting) {
            $msg = ($verificationStatus === 'pending')
                ? 'Your property owner profile is still pending approval.'
                : 'Access denied. Property owner dashboard requires approval.';
            return redirect('/dashboard')->with('error', $msg);
        }

        $isViewOnly = false; // Overriding since we are blocking access anyway

        // Fetch project stats for the dashboard
        $stats = [
            'total'      => 0,
            'pending'    => 0,
            'active'     => 0,
            'inProgress' => 0,
            'completed'  => 0,
        ];

        if ($user) {
            $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
            if ($owner) {
                $projects = DB::table('projects as p')
                    ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                    ->where('pr.owner_id', $owner->owner_id)
                    ->whereNotIn('pr.project_post_status', ['deleted'])
                    ->select('p.project_status', 'pr.project_post_status')
                    ->get();

                $stats['total']      = $projects->count();
                $stats['pending']    = $projects->where('project_post_status', 'under_review')->count();
                $stats['active']     = $projects->filter(fn($p) => $p->project_post_status === 'approved' && $p->project_status === 'open')->count();
                $stats['inProgress'] = $projects->filter(fn($p) => in_array($p->project_status, ['bidding_closed', 'in_progress', 'waiting_milestone_setup']))->count();
                $stats['completed']  = $projects->where('project_status', 'completed')->count();
            }
        }

        return view('owner.propertyOwner_Dashboard', [
            'verificationStatus' => $verificationStatus,
            'isViewOnly'         => $isViewOnly,
            'stats'              => $stats,
        ]);
    }

    /* =====================================================================
     * WEB — Contractor Dashboard  (Blade)
     * ===================================================================== */

    /**
     * GET /contractor/dashboard
     *
     * Show the contractor-specific dashboard with projects & stats.
     */
    public function contractorDashboard(Request $request)
    {
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck;
        }

        $user = Session::get('user') ?: $request->user();
        $userId = is_object($user) ? ($user->user_id ?? null) : ($user['user_id'] ?? null);

        // Resolve display name
        $userName = null;
        if (is_object($user)) {
            if (!empty($user->first_name) || !empty($user->last_name)) {
                $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            } else {
                $userName = $user->username ?? ($user->name ?? null);
            }
        }

        $projects = collect([]);
        $stats = ['total' => 0, 'pending' => 0, 'active' => 0, 'inProgress' => 0, 'completed' => 0];

        if ($userId) {
            try {
                $data = $this->dashboardService->contractorDashboardData($userId);
                $projects = $data['projects'];
                $stats = $data['stats'];
            } catch (\Exception $e) {
                Log::error('DashboardController::contractorDashboard failed: ' . $e->getMessage());
            }
        }

        return view('contractor.contractor_Dashboard', [
            'projects' => $projects,
            'stats' => $stats,
            'userName' => $userName,
        ]);
    }

    /* =====================================================================
     * WEB — Unified Dashboard  (Blade)
     * ===================================================================== */

    /**
     * GET /dashboard
     *
     * Role-aware unified dashboard. Detects owner vs contractor and
     * renders the both.dashboard Blade view with the right feed items.
     */
    public function unifiedDashboard(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/accounts/login');
        }

        $activeRole = session('active_role');
        $currentRole = $activeRole ?? session('current_role', $user->user_type ?? null);

        if ($currentRole === 'owner') {
            // Check verification status before allowing access to owner homepage
            $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
            if (!$owner || strtolower($owner->verification_status) !== 'approved') {
                $statusMsg = ($owner && strtolower($owner->verification_status) === 'pending')
                    ? 'Your property owner profile is still pending admin approval.'
                    : 'A property owner profile is required to access this role.';

                // If blocked, force back to contractor role for safety
                session(['active_role' => 'contractor']);
                session(['current_role' => 'contractor']);
                return redirect()->route('contractor.homepage')->with('error', $statusMsg);
            }
            return redirect()->route('owner.homepage');
        }

        return redirect()->route('contractor.homepage');
    }

    /* =====================================================================
     * API — Dashboard  (JSON, bearer-token or session)
     * ===================================================================== */

    /**
     * GET /api/dashboard
     *
     * Returns the unified dashboard payload as JSON for mobile / SPA clients.
     */
    public function apiDashboard(Request $request)
    {
        $user = Session::get('user') ?: $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $sessionCurrentRole = session('current_role');
        $sessionUserType = session('userType');
        $userType = isset($user->user_type) ? $user->user_type : ($sessionCurrentRole ?? null);
        $currentRole = $sessionCurrentRole ?? $userType ?? $sessionUserType ?? null;

        $viewData = $this->dashboardService->unifiedDashboardData($user, $currentRole, $userType);

        return response()->json([
            'success' => true,
            'data' => $viewData,
        ]);
    }

    /**
     * GET /api/dashboard/owner-stats
     *
     * Quick stats endpoint for mobile owner dashboard.
     */
    public function apiOwnerStats(Request $request)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            $user = $request->user();
            $userId = $user ? ($user->user_id ?? null) : null;
        }

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID required'], 400);
        }

        return response()->json($this->dashboardService->ownerStatsApi((int) $userId));
    }

    /**
     * GET /api/dashboard/contractor-stats
     *
     * Quick stats endpoint for mobile contractor dashboard.
     */
    public function apiContractorStats(Request $request)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            $user = $request->user();
            $userId = $user ? ($user->user_id ?? null) : null;
        }

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID required'], 400);
        }

        return response()->json($this->dashboardService->contractorStatsApi((int) $userId));
    }

    /* =====================================================================
     * AUTH GUARD  (duplicated from cprocessController for independence)
     * ===================================================================== */

    /**
     * Check that the session user has contractor access.
     *
     * For 'both' users auto-switches current_role to 'contractor'.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse|null  null = access granted
     */
    protected function checkContractorAccess(Request $request)
    {
        if (!Session::has('user')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'redirect_url' => '/accounts/login',
                ], 401);
            }
            return redirect('/accounts/login')->with('error', 'Please login first');
        }

        $user = Session::get('user');

        if (!in_array($user->user_type, ['contractor', 'both'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only contractors can access this page.',
                    'redirect_url' => '/dashboard',
                ], 403);
            }
            return redirect('/dashboard')->with('error', 'Access denied. Only contractors can access this page.');
        }

        // Auto-switch role for 'both' users
        if ($user->user_type === 'both') {
            $currentRole = Session::get('current_role', 'contractor');
            if ($currentRole !== 'contractor') {
                Session::put('current_role', 'contractor');
                try {
                    DB::table('users')
                        ->where('user_id', $user->user_id)
                        ->update(['preferred_role' => 'contractor']);
                } catch (\Exception $e) {
                    Log::warning('Failed to update preferred_role', [
                        'user_id' => $user->user_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return null;
    }
}
