<?php

namespace App\Http\Controllers\contractor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contractor\cProcessRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\contractor\contractorClass;
use App\Models\contractor\progressUploadClass;
use App\Services\notificationService;

class cprocessController extends Controller
{
    protected $contractorClass;
    protected $progressUploadClass;

    public function __construct()
    {
        $this->contractorClass = new contractorClass();
        $this->progressUploadClass = new progressUploadClass();
    }

    public function showHomepage(Request $request)
    {
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck; // Return error response
        }

        // Fetch approved projects that are open for bidding
        try {
            $projects = DB::table('projects')
                ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                ->join('contractor_types', 'projects.type_id', '=', 'contractor_types.type_id')
                ->join('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
                ->where('projects.project_status', 'open')
                ->where('project_relationships.project_post_status', 'approved')
                ->select(
                    'projects.project_id',
                    'projects.project_title',
                    'projects.project_description',
                    'projects.project_location',
                    'projects.budget_range_min',
                    'projects.budget_range_max',
                    'projects.lot_size',
                    'projects.floor_area',
                    'projects.property_type',
                    'projects.type_id',
                    'contractor_types.type_name',
                    'projects.project_status',
                    'project_relationships.project_post_status',
                    'project_relationships.bidding_due as bidding_due',
                    'project_relationships.created_at',
                    'property_owners.owner_id as owner_id',
                    DB::raw("CONCAT(property_owners.first_name, ' ', COALESCE(property_owners.middle_name, ''), ' ', property_owners.last_name) as owner_name")
                )
                ->orderBy('project_relationships.created_at', 'desc')
                ->get();

            // attach files for each project (keep objects so Blade can use -> syntax)
            $projects = $projects->map(function ($proj) {
                $files = DB::table('project_files')
                    ->where('project_id', $proj->project_id)
                    ->get();
                $proj->files = $files;
                return $proj;
            });
        } catch (\Throwable $e) {
            \Log::error('Failed to fetch projects for contractor homepage: ' . $e->getMessage());
            $projects = collect([]);
        }
        // Fetch approved projects via the owner projectsClass (centralized logic)
        try {
            $projectsClass = new \App\Models\owner\projectsClass();
            $projects = $projectsClass->getApprovedProjects();

            // attach files for each project using the same class helper
            $projects = $projects->map(function ($proj) use ($projectsClass) {
                $files = $projectsClass->getProjectFiles($proj->project_id);
                $proj->files = $files;
                return $proj;
            });
        } catch (\Throwable $e) {
            \Log::error('Failed to fetch projects for contractor homepage via projectsClass: ' . $e->getMessage());
            $projects = collect([]);
        }

        // Prepare a lightweight JS-friendly payload for the front-end script
        try {
            $jsProjects = $projects->map(function ($p) {
                // Safely get first file path for image (support array or collection)
                $firstFilePath = null;
                if (!empty($p->files)) {
                    if (is_array($p->files) && count($p->files) > 0) {
                        $first = $p->files[0];
                    } elseif (method_exists($p->files, 'first')) {
                        $first = $p->files->first();
                    } else {
                        $first = null;
                    }
                    if (!empty($first)) {
                        $firstFilePath = is_string($first) ? $first : (is_array($first) ? ($first['file_path'] ?? null) : ($first->file_path ?? null));
                    }
                }

                return (object) [
                    'project_id' => $p->project_id,
                    'title' => $p->project_title,
                    'description' => $p->project_description,
                    'city' => $p->project_location,
                    'deadline' => $p->bidding_due ?? $p->bidding_deadline ?? null,
                    'project_type' => $p->type_name ?? $p->property_type ?? null,
                    'budget_min' => $p->budget_range_min ?? null,
                    'budget_max' => $p->budget_range_max ?? null,
                    'status' => $p->project_status ?? 'open',
                    'created_at' => $p->created_at ?? null,
                    'image' => $firstFilePath ? asset('storage/' . ltrim($firstFilePath, '/')) : null,
                    'owner_name' => $p->owner_name ?? null,
                    'project' => $p // keep original project object accessible if needed
                ];
            })->toArray();
        } catch (\Throwable $e) {
            \Log::warning('Failed to prepare jsProjects: ' . $e->getMessage());
            $jsProjects = [];
        }

        return view('contractor.contractor_Homepage', [
            'projects' => $projects,
            'jsProjects' => $jsProjects
        ]);
    }

    public function showMessages(Request $request)
    {
        return view('both.messages');
    }

    public function showProfile(Request $request)
    {
        return view('contractor.contractor_Profile');
    }

    /**
     * @deprecated Moved to \App\Http\Controllers\both\DashboardController::contractorDashboard()
     */
    public function showDashboard(Request $request)
    {
        return app(\App\Http\Controllers\both\dashboardController::class)->contractorDashboard($request);
    }

    public function showMyProjects(Request $request)
    {
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck;
        }

        // Resolve user id from session or sanctum
        $user = Session::get('user') ?: $request->user();
        $userId = is_object($user) ? ($user->user_id ?? null) : ($user['user_id'] ?? null);

        $projects = [];
        if ($userId) {
            try {
                $subReq = new Request();
                $subReq->merge(['user_id' => $userId]);
                $resp = $this->apiGetContractorProjects($subReq);
                if ($resp instanceof \Illuminate\Http\JsonResponse) {
                    $respData = $resp->getData(true);
                    $projects = $respData['data'] ?? [];
                } elseif (is_array($resp)) {
                    $projects = $resp['data'] ?? [];
                }
            } catch (\Throwable $e) {
                \Log::warning('showMyProjects apiGetContractorProjects failed: ' . $e->getMessage());
                $projects = [];
            }
        }

        return view('contractor.contractor_Myprojects', [
            'projects' => $projects,
            'userId' => $userId
        ]);
    }

    public function showMyBids(Request $request)
    {
        $user = Session::get('user');
        $userId = is_object($user) ? ($user->user_id ?? null) : ($user['user_id'] ?? null);
        $bids = collect([]);

        if ($userId) {
            // Resolve contractor — same logic as apiGetMyBids
            $contractor = DB::table('contractors')->where('user_id', $userId)->first();

            if (!$contractor) {
                $contractorUser = DB::table('contractor_users')
                    ->where('user_id', $userId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->first();
                if ($contractorUser) {
                    $contractor = DB::table('contractors')
                        ->where('contractor_id', $contractorUser->contractor_id)
                        ->first();
                }
            }

            if ($contractor) {
                $bids = DB::table('bids')
                    ->join('projects', 'bids.project_id', '=', 'projects.project_id')
                    ->leftJoin('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                    ->leftJoin('property_owners', 'project_relationships.owner_id', '=', 'property_owners.owner_id')
                    ->leftJoin('users', 'property_owners.user_id', '=', 'users.user_id')
                    ->leftJoin('contractor_types', 'projects.type_id', '=', 'contractor_types.type_id')
                    ->where('bids.contractor_id', $contractor->contractor_id)
                    ->whereNotIn('bids.bid_status', ['cancelled'])
                    ->select(
                        'bids.bid_id',
                        'bids.project_id',
                        'bids.proposed_cost',
                        'bids.estimated_timeline',
                        'bids.contractor_notes',
                        'bids.bid_status',
                        'bids.submitted_at',
                        'projects.project_title',
                        'projects.project_description',
                        'projects.project_location',
                        'projects.budget_range_min',
                        'projects.budget_range_max',
                        'projects.lot_size',
                        'projects.floor_area',
                        'projects.property_type',
                        'projects.to_finish',
                        'projects.project_status',
                        'contractor_types.type_name',
                        'project_relationships.bidding_due',
                        'users.username as owner_name'
                    )
                    ->orderBy('bids.submitted_at', 'desc')
                    ->get();

                // Attach project files and bid files for each bid
                foreach ($bids as $bid) {
                    $projectFiles = DB::table('project_files')
                        ->where('project_id', $bid->project_id)
                        ->select('file_type', 'file_path')
                        ->get();
                    $bid->project_files = $projectFiles;

                    $bidFiles = DB::table('bid_files')
                        ->where('bid_id', $bid->bid_id)
                        ->select('file_id', 'file_name', 'file_path')
                        ->get();
                    $bid->bid_files = $bidFiles;
                }
            }
        }

        return view('contractor.contractor_Mybids', [
            'bids' => $bids,
            'userId' => $userId,
        ]);
    }

    public function setMilestoneSession(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer'
        ]);

        Session::put('current_milestone_project_id', $request->project_id);

        return response()->json(['success' => true]);
    }

    public function setMilestoneItemSession(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'project_id' => 'required|integer',
        ]);

        Session::put('current_milestone_item_id', $request->item_id);
        Session::put('current_milestone_project_id', $request->project_id);

        return response()->json(['success' => true]);
    }

    public function showMilestoneReport(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/accounts/login');
        }

        // Use PRG Session pattern to hide ID from URL
        $projectId = Session::get('current_milestone_project_id');

        // Fallback for hardcoded direct links during dev
        if (!$projectId && $request->has('project_id')) {
            $projectId = $request->query('project_id');
            Session::put('current_milestone_project_id', $projectId);
        }

        if (!$projectId) {
            return redirect('/contractor/myprojects')->with('error', 'Project ID is required.');
        }

        // Get contractor via model (supports staff members too)
        $contractor = $this->contractorClass->getContractorByUserId($user->user_id);

        if (!$contractor) {
            $contractorUser = $this->contractorClass->getContractorUserByUserId($user->user_id);
            if ($contractorUser && isset($contractorUser->contractor_id)) {
                $contractor = DB::table('contractors')
                    ->where('contractor_id', $contractorUser->contractor_id)
                    ->first();
            }
        }

        if (!$contractor) {
            return redirect('/contractor/myprojects')->with('error', 'Contractor profile not found.');
        }

        // Fetch project and verify contractor access via accepted bid
        $project = $this->contractorClass->getProjectForContractor($projectId, $contractor->contractor_id);

        if (!$project) {
            return redirect('/contractor/myprojects')->with('error', 'Project not found or access denied.');
        }

        // Fetch milestone plans with items, progress reports, and payments
        $milestones = $this->contractorClass->getProjectMilestonesWithItems($projectId, $contractor->contractor_id);

        // Fetch all payments for the project (for the payment history modal)
        $allPayments = $this->contractorClass->getProjectPayments($projectId);

        return view('contractor.contractor_MilestoneReport', [
            'project' => $project,
            'milestones' => $milestones,
            'allPayments' => $allPayments,
        ]);
    }

    public function showMilestoneProgressReport(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/accounts/login');
        }

        $itemId = Session::get('current_milestone_item_id');
        $projectId = Session::get('current_milestone_project_id');

        // Fallback for hardcoded direct links
        if (!$itemId && $request->has('item_id')) {
            $itemId = $request->query('item_id');
            Session::put('current_milestone_item_id', $itemId);
        }

        if (!$itemId) {
            return redirect('/contractor/projects/milestone-report')->with('error', 'Milestone Item ID is required.');
        }

        return view('contractor.contractor_MilestoneprogressReport', [
            'itemId' => $itemId,
            'projectId' => $projectId
        ]);
    }

    protected function checkContractorAccess(Request $request)
    {
        if (!Session::has('user')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'redirect_url' => '/accounts/login'
                ], 401);
            } else {
                return redirect('/accounts/login')->with('error', 'Please login first');
            }
        }

        $user = Session::get('user');

        // Check if user has contractor role
        if (!in_array($user->user_type, ['contractor', 'both'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only contractors can access this page.',
                    'redirect_url' => '/dashboard'
                ], 403);
            } else {
                return redirect('/dashboard')->with('error', 'Access denied. Only contractors can access this page.');
            }
        }

        // For 'both' users, auto-switch to contractor role when accessing contractor pages
        if ($user->user_type === 'both') {
            $currentRole = Session::get('current_role', 'contractor');
            if ($currentRole !== 'contractor') {
                // Auto-switch to contractor role
                Session::put('current_role', 'contractor');

                // Update preferred_role in database for persistence
                try {
                    DB::table('users')
                        ->where('user_id', $user->user_id)
                        ->update(['preferred_role' => 'contractor']);
                } catch (\Exception $e) {
                    Log::warning('Failed to update preferred_role in database', [
                        'user_id' => $user->user_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return null;
    }

    public function switchRole(Request $request)
    {
        // Support both session and Sanctum token authentication
        // $request->user() is set by Sanctum middleware
        $user = Session::get('user') ?: $request->user();
        // Fallback: resolve user from Bearer token for stateless mobile clients
        if (!$user && $request->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) {
                    $user = $token->tokenable; // Eloquent User model
                }
            } catch (\Throwable $e) {
                \Log::warning('switchRole bearer fallback failed: ' . $e->getMessage());
            }
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'redirect_url' => '/accounts/login'
            ], 401);
        }

        // Allow switching in these cases:
        // - user_type is 'both' (normal multi-role accounts)
        // - switching to 'owner' and an approved owner profile exists for this user
        $userType = is_object($user) ? ($user->user_type ?? null) : ($user['user_type'] ?? null);
        $userId = is_object($user) ? ($user->user_id ?? null) : ($user['user_id'] ?? null);
        // Determine desired role early and validate it before access checks
        $targetRole = $request->input('role');
        if (!in_array($targetRole, ['contractor', 'owner'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid role specified. Must be "contractor" or "owner".'
                ], 400);
            } else {
                return redirect('/dashboard')->with('error', 'Invalid role specified.');
            }
        }

        $canSwitch = false;
        if ($userType === 'both') {
            $canSwitch = true;
        } else {
            // If switching to owner, allow when an approved owner profile exists
            if ($targetRole === 'owner' && $userId) {
                try {
                    $ownerCheck = DB::table('property_owners')->where('user_id', $userId)->first();
                    if ($ownerCheck && strtolower($ownerCheck->verification_status) === 'approved') {
                        $canSwitch = true;
                    }
                } catch (\Throwable $e) {
                    Log::warning('switchRole owner-check failed: ' . $e->getMessage());
                }
            }
            // If switching to contractor and the account is contractor, allow as well
            if ($targetRole === 'contractor' && in_array($userType, ['contractor'])) {
                $canSwitch = true;
            }
        }

        if (!$canSwitch) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role switching not available for your account type.'
                ], 403);
            } else {
                return redirect('/dashboard')->with('error', 'Role switching not available for your account type.');
            }
        }

        $targetRole = $request->input('role');

        if (!in_array($targetRole, ['contractor', 'owner'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid role specified. Must be "contractor" or "owner".'
                ], 400);
            } else {
                return redirect('/dashboard')->with('error', 'Invalid role specified.');
            }
        }

        // If switching to owner, check if the profile is approved
        if ($targetRole === 'owner') {
            $userId = is_object($user) ? ($user->user_id ?? null) : ($user['user_id'] ?? null);
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();

            if (!$owner || strtolower($owner->verification_status) !== 'approved' || intval($owner->is_active) !== 1) {
                $statusMsg = ($owner && strtolower($owner->verification_status) === 'pending')
                    ? 'Your property owner profile is still pending admin approval.'
                    : 'A property owner profile is required to access this role.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $statusMsg
                    ], 403);
                } else {
                    return redirect()->back()->with('error', $statusMsg);
                }
            }
        }

        Session::put('current_role', $targetRole);
        Session::put('active_role', $targetRole);

        // Persist active role for stateless clients using Sanctum
        try {
            if (is_object($user) && method_exists($user, 'save')) {
                // Eloquent model
                $user->preferred_role = $targetRole;
                $user->save();
            } else {
                // Session user (stdClass/array) — update via query builder
                $userId = is_object($user) ? ($user->user_id ?? null) : ($user['user_id'] ?? null);
                if ($userId) {
                    DB::table('users')->where('user_id', $userId)->update([
                        'preferred_role' => $targetRole,
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('switchRole persist preferred_role failed: ' . $e->getMessage());
        }

        // If switching to owner and an approved owner profile exists, redirect to owner homepage
        if ($targetRole === 'owner') {
            $ownerRecord = null;
            try {
                $userId = is_object($user) ? ($user->user_id ?? null) : ($user['user_id'] ?? null);
                if ($userId) {
                    $ownerRecord = DB::table('property_owners')->where('user_id', $userId)->first();
                }
            } catch (\Throwable $e) {
                Log::warning('switchRole: failed to fetch owner record: ' . $e->getMessage());
            }

            // If owner exists and is approved, send them to the owner homepage (primary owner landing)
            if ($ownerRecord && strtolower($ownerRecord->verification_status) === 'approved') {
                $redirectUrl = route('owner.homepage');
            } else {
                $redirectUrl = route('owner.dashboard');
            }
        } else {
            $redirectUrl = route('contractor.homepage');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Successfully switched to {$targetRole} role",
                'current_role' => $targetRole,
                'active_role' => $targetRole,
                'redirect_url' => $redirectUrl
            ]);
        } else {
            return redirect($redirectUrl)->with('success', "Successfully switched to {$targetRole} role");
        }
    }

    public function getCurrentRole(Request $request)
    {
        try {
            // Support both session and Sanctum token authentication
            $user = Session::get('user');

            // If no session user, try Sanctum
            if (!$user && $request->user()) {
                $user = $request->user();
            }

            // If still no user, try to get from token manually
            if (!$user && $request->bearerToken()) {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) {
                    $user = $token->tokenable;
                }
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                    'redirect_url' => '/accounts/login'
                ], 401);
            }

            // For Sanctum-authenticated users, check the database for current_role
            // For session users, get from session
            $currentRole = Session::get('current_role');
            if (!$currentRole) {
                // Try persisted preferred_role for Sanctum/stateless clients
                $userId = is_object($user) ? ($user->user_id ?? null) : ($user['user_id'] ?? null);
                if ($userId) {
                    try {
                        $preferred = DB::table('users')->where('user_id', $userId)->value('preferred_role');
                        if (!empty($preferred)) {
                            $currentRole = $preferred;
                        }
                    } catch (\Throwable $e) {
                        Log::warning('getCurrentRole fetch preferred_role failed: ' . $e->getMessage());
                    }
                }

                // Fallback: if user_type is 'both', default to contractor, otherwise use their user_type
                if (!$currentRole) {
                    $userType = is_object($user) ? $user->user_type : ($user['user_type'] ?? null);
                    $currentRole = $userType === 'both' ? 'contractor' : $userType;
                }
            }

            // Normalize current_role format (convert 'owner' to 'contractor' for consistency)
            $normalizedRole = $currentRole;
            if ($currentRole === 'property_owner' || $currentRole === 'owner') {
                $normalizedRole = 'owner';
            } elseif ($currentRole === 'contractor') {
                $normalizedRole = 'contractor';
            }

            $userType = is_object($user) ? $user->user_type : ($user['user_type'] ?? null);

            // Fetch contractor / owner profile records so the mobile client can show
            // application status (pending/rejected/approved) and rejection reasons.
            $userId = is_object($user) ? ($user->user_id ?? null) : ($user['user_id'] ?? null);
            $contractor = null;
            $owner = null;
            $contractor_pending = false;
            $owner_pending = false;
            $contractor_approved = false;
            $owner_approved = false;
            $contractor_rejected = false;
            $owner_rejected = false;

            try {
                if ($userId) {
                    $contractor = DB::table('contractors')->where('user_id', $userId)->first();
                    $owner = DB::table('property_owners')->where('user_id', $userId)->first();

                    if ($contractor && isset($contractor->verification_status)) {
                        $vs = strtolower($contractor->verification_status);
                        $contractor_approved = $vs === 'approved';
                        $contractor_rejected = $vs === 'rejected';
                        $contractor_pending = !$contractor_approved && !$contractor_rejected;
                    }

                    if ($owner && isset($owner->verification_status)) {
                        $vs2 = strtolower($owner->verification_status);
                        $owner_approved = $vs2 === 'approved';
                        $owner_rejected = $vs2 === 'rejected';
                        $owner_pending = !$owner_approved && !$owner_rejected;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('getCurrentRole: failed to load contractor/owner records: ' . $e->getMessage());
            }

            $pending_role_request = ($contractor_pending || $owner_pending);

            return response()->json([
                'success' => true,
                'user_type' => $userType,
                'current_role' => $normalizedRole,
                'can_switch_roles' => $userType === 'both',
                'pending_role_request' => $pending_role_request,
                'contractor' => $contractor,
                'owner' => $owner,
                'contractor_role_approved' => $contractor_approved,
                'owner_role_approved' => $owner_approved,
            ]);
        } catch (\Exception $e) {
            \Log::error('getCurrentRole error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return the contractor profile for the authenticated user (stateless-friendly)
     */
    public function apiGetMyContractorProfile(Request $request)
    {
        try {
            // Resolve user (session, Sanctum, or bearer token)
            $user = Session::get('user') ?: $request->user();
            if (!$user && $request->bearerToken()) {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) {
                    $user = $token->tokenable;
                }
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                ], 401);
            }

            $userId = is_object($user) ? ($user->user_id ?? null) : ($user['user_id'] ?? null);
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user context',
                ], 400);
            }

            $contractor = $this->contractorClass->getContractorByUserId($userId);
            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor profile not found',
                ], 404);
            }

            // Minimal profile payload (extend later as needed)
            return response()->json([
                'success' => true,
                'data' => [
                    'contractor_id' => $contractor->contractor_id ?? null,
                    'company_name' => $contractor->company_name ?? null,
                    'years_of_experience' => $contractor->years_of_experience ?? null,
                    'business_address' => $contractor->business_address ?? null,
                    'cover_photo' => $contractor->cover_photo ?? null,
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('apiGetMyContractorProfile error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showMilestoneSetupForm(Request $request)
    {

        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck; // Return error response
        }

        $user = Session::get('user');
        $contractor = $this->contractorClass->getContractorByUserId($user->user_id);

        if (!$contractor) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor profile not found.',
                    'redirect_url' => '/dashboard'
                ], 404);
            } else {
                return redirect('/dashboard')->with('error', 'Contractor profile not found.');
            }
        }

        $projectId = $request->query('project_id');
        $milestoneId = $request->query('milestone_id');
        $projects = $this->contractorClass->getContractorProjects($contractor->contractor_id, $milestoneId);

        // If editing, get existing milestone data
        $existingMilestone = null;
        $existingItems = [];
        if ($milestoneId) {
            $existingMilestone = $this->contractorClass->getMilestoneById($milestoneId, $contractor->contractor_id);
            if ($existingMilestone) {
                $existingItems = $this->contractorClass->getMilestoneItems($milestoneId);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Milestone setup form data',
                'data' => [
                    'project_id' => $projectId,
                    'milestone_id' => $milestoneId,
                    'projects' => $projects,
                    'contractor' => $contractor,
                    'existing_milestone' => $existingMilestone,
                    'existing_items' => $existingItems,
                    'current_role' => Session::get('current_role', 'contractor')
                ]
            ], 200);
        } else {
            return view('contractor.milestoneSetup', [
                'projectId' => $projectId,
                'milestoneId' => $milestoneId,
                'projects' => $projects,
                'contractor' => $contractor,
                'existingMilestone' => $existingMilestone,
                'existingItems' => $existingItems
            ]);
        }
    }

    public function milestoneStepOne(cProcessRequest $request)
    {
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck;
        }

        $user = Session::get('user');
        $contractor = $this->contractorClass->getContractorByUserId($user->user_id);

        if (!$contractor) {
            return response()->json([
                'success' => false,
                'errors' => ['Contractor profile not found']
            ], 404);
        }

        $validated = $request->validated();

        if (!$this->contractorClass->projectBelongsToContractor($validated['project_id'], $contractor->contractor_id)) {
            return response()->json([
                'success' => false,
                'errors' => ['Selected project is not assigned to your company']
            ], 403);
        }

        // Check if editing existing milestone
        $milestoneId = $request->input('milestone_id');
        $isEditing = !empty($milestoneId);

        if (!$isEditing && $this->contractorClass->contractorHasMilestoneForProject($validated['project_id'], $contractor->contractor_id)) {
            return response()->json([
                'success' => false,
                'errors' => ['This project already has a milestone plan.']
            ], 409);
        }

        // If editing, verify milestone belongs to contractor
        if ($isEditing) {
            $existingMilestone = $this->contractorClass->getMilestoneById($milestoneId, $contractor->contractor_id);
            if (!$existingMilestone) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Milestone not found or you do not have permission to edit it.']
                ], 404);
            }
        }

        Session::put('milestone_setup_step1', [
            'project_id' => (int) $validated['project_id'],
            'contractor_id' => (int) $contractor->contractor_id,
            'milestone_name' => $validated['milestone_name'],
            'milestone_description' => $request->input('milestone_description', $validated['milestone_name']),
            'payment_mode' => $validated['payment_mode'],
            'milestone_id' => $isEditing ? (int) $milestoneId : null
        ]);

        Session::forget('milestone_setup_step2');
        Session::forget('milestone_setup_items');

        return response()->json([
            'success' => true,
            'step' => 2,
            'payment_mode' => $validated['payment_mode']
        ]);
    }

    public function milestoneStepTwo(cProcessRequest $request)
    {

        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck;
        }

        $step1 = Session::get('milestone_setup_step1');

        if (!$step1) {
            return response()->json([
                'success' => false,
                'errors' => ['Please complete the previous step first']
            ], 400);
        }

        $validated = $request->validated();

        $startDate = strtotime($validated['start_date']);
        $endDate = strtotime($validated['end_date']);

        $totalCost = (float) $validated['total_project_cost'];
        $downpayment = 0.00;

        if ($step1['payment_mode'] === 'downpayment') {
            $downpayment = (float) $validated['downpayment_amount'];
        }

        Session::put('milestone_setup_step2', [
            'start_date' => date('Y-m-d 00:00:00', $startDate),
            'end_date' => date('Y-m-d 23:59:59', $endDate),
            'total_project_cost' => $totalCost,
            'downpayment_amount' => $downpayment
        ]);

        if ($request->expectsJson()) {

            return response()->json([
                'success' => true,
                'message' => 'Milestone step 2 completed',
                'step' => 3,
                'start_date' => date('Y-m-d', $startDate),
                'end_date' => date('Y-m-d', $endDate),
                'payment_mode' => $step1['payment_mode'],
                'next_step' => 'submit_milestone'
            ]);
        } else {

            return response()->json([
                'success' => true,
                'step' => 3,
                'start_date' => date('Y-m-d', $startDate),
                'end_date' => date('Y-m-d', $endDate),
                'payment_mode' => $step1['payment_mode']
            ]);
        }
    }

    public function submitMilestone(cProcessRequest $request)
    {

        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck;
        }

        $step1 = Session::get('milestone_setup_step1');
        $step2 = Session::get('milestone_setup_step2');

        if (!$step1 || !$step2) {
            return response()->json([
                'success' => false,
                'errors' => ['Session expired. Please start again.']
            ], 400);
        }

        $itemsRaw = $request->input('items');
        $items = json_decode($itemsRaw, true);

        if (!is_array($items) || empty($items)) {
            return response()->json([
                'success' => false,
                'errors' => ['Please add at least one milestone item.']
            ], 400);
        }

        $startDate = strtotime($step2['start_date']);
        $endDate = strtotime($step2['end_date']);

        $isEditing = !empty($step1['milestone_id']);
        $milestoneId = $isEditing ? $step1['milestone_id'] : null;

        // Format dates for database (datetime format)
        $startDateFormatted = date('Y-m-d 00:00:00', $startDate);
        $endDateFormatted = date('Y-m-d 23:59:59', $endDate);

        try {
            DB::beginTransaction();

            if ($isEditing && $milestoneId) {
                // Update existing milestone
                $existingMilestone = $this->contractorClass->getMilestoneById($milestoneId, $step1['contractor_id']);
                if (!$existingMilestone) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'errors' => ['Milestone not found or you do not have permission to edit it.']
                    ], 404);
                }

                // Update payment plan
                $this->contractorClass->updatePaymentPlan($existingMilestone->plan_id, [
                    'payment_mode' => $step1['payment_mode'],
                    'total_project_cost' => $step2['total_project_cost'],
                    'downpayment_amount' => $step2['downpayment_amount'],
                    'updated_at' => now()
                ]);

                // Update milestone
                $milestoneUpdateData = [
                    'milestone_name' => $step1['milestone_name'],
                    'start_date' => $startDateFormatted,
                    'end_date' => $endDateFormatted,
                    'setup_status' => 'submitted',
                    'updated_at' => now()
                ];

                // Only update milestone_description if it exists in step1
                if (isset($step1['milestone_description']) && !empty($step1['milestone_description'])) {
                    $milestoneUpdateData['milestone_description'] = $step1['milestone_description'];
                } else {
                    // Use milestone_name as fallback if description is not provided
                    $milestoneUpdateData['milestone_description'] = $step1['milestone_name'];
                }

                $this->contractorClass->updateMilestone($milestoneId, $milestoneUpdateData);

                // Before deleting existing milestone items, ensure there are no
                // milestone_payments that reference those items (foreign key)
                $blockingPaymentsCount = DB::table('milestone_payments as mp')
                    ->join('milestone_items as mi', 'mp.item_id', '=', 'mi.item_id')
                    ->where('mi.milestone_id', $milestoneId)
                    ->count();

                if ($blockingPaymentsCount > 0) {
                    DB::rollBack();
                    Log::warning("Attempt to edit milestone {$milestoneId} blocked: {$blockingPaymentsCount} payment(s) reference its items.");
                    return response()->json([
                        'success' => false,
                        'errors' => ["Cannot modify milestone items because {$blockingPaymentsCount} payment(s) are associated with existing milestone items. Remove or resolve those payments before editing the milestone."]
                    ], 409);
                }

                // Safe to delete existing milestone items
                $this->contractorClass->deleteMilestoneItems($milestoneId);
            } else {
                // ── Guard: check if a milestone already exists for this project+contractor ──
                $existingMilestone = DB::table('milestones')
                    ->where('project_id', $step1['project_id'])
                    ->where('contractor_id', $step1['contractor_id'])
                    ->first();

                if ($existingMilestone) {
                    // Reuse the existing milestone instead of creating a duplicate
                    $milestoneId = $existingMilestone->milestone_id;
                    Log::info("submitMilestone: reusing existing milestone id={$milestoneId} for project {$step1['project_id']}");

                    // Update the existing payment plan
                    $this->contractorClass->updatePaymentPlan($existingMilestone->plan_id, [
                        'payment_mode' => $step1['payment_mode'],
                        'total_project_cost' => $step2['total_project_cost'],
                        'downpayment_amount' => $step2['downpayment_amount'],
                        'updated_at' => now()
                    ]);

                    $milestoneDescription = isset($step1['milestone_description']) && !empty($step1['milestone_description'])
                        ? $step1['milestone_description']
                        : $step1['milestone_name'];

                    // Update existing milestone
                    $this->contractorClass->updateMilestone($milestoneId, [
                        'milestone_name' => $step1['milestone_name'],
                        'milestone_description' => $milestoneDescription,
                        'start_date' => $startDateFormatted,
                        'end_date' => $endDateFormatted,
                        'setup_status' => 'submitted',
                        'updated_at' => now()
                    ]);

                    // Delete old milestone items (will be recreated below)
                    $this->contractorClass->deleteMilestoneItems($milestoneId);
                } else {
                    // Create new milestone (first time for this project)
                    $planId = $this->contractorClass->createPaymentPlan([
                        'project_id' => $step1['project_id'],
                        'contractor_id' => $step1['contractor_id'],
                        'payment_mode' => $step1['payment_mode'],
                        'total_project_cost' => $step2['total_project_cost'],
                        'downpayment_amount' => $step2['downpayment_amount']
                    ]);

                    $milestoneDescription = isset($step1['milestone_description']) && !empty($step1['milestone_description'])
                        ? $step1['milestone_description']
                        : $step1['milestone_name'];

                    $milestoneId = $this->contractorClass->createMilestone([
                        'project_id' => $step1['project_id'],
                        'contractor_id' => $step1['contractor_id'],
                        'plan_id' => $planId,
                        'milestone_name' => $step1['milestone_name'],
                        'milestone_description' => $milestoneDescription,
                        'start_date' => $startDateFormatted,
                        'end_date' => $endDateFormatted,
                        'setup_status' => 'submitted'
                    ]);
                }
            }

            $remainingAmount = $step2['total_project_cost'];
            if ($step1['payment_mode'] === 'downpayment') {
                $remainingAmount -= $step2['downpayment_amount'];
            }

            foreach ($items as $index => $item) {
                $percentage = (float) $item['percentage'];
                $itemCostBase = $step1['payment_mode'] === 'downpayment'
                    ? $remainingAmount
                    : $step2['total_project_cost'];
                $calculatedCost = $itemCostBase * ($percentage / 100);

                $this->contractorClass->createMilestoneItem([
                    'milestone_id' => $milestoneId,
                    'sequence_order' => $index + 1,
                    'percentage_progress' => $percentage,
                    'milestone_item_title' => $item['title'],
                    'milestone_item_description' => $item['description'],
                    'milestone_item_cost' => round($calculatedCost, 2),
                    'date_to_finish' => date('Y-m-d 23:59:59', strtotime($item['date_to_finish']))
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving milestone: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Step1 data: ' . json_encode($step1 ?? []));
            Log::error('Step2 data: ' . json_encode($step2 ?? []));
            Log::error('Items data: ' . json_encode($items ?? []));

            $errorMessage = 'An error occurred while saving the milestone. Please try again.';
            if (config('app.debug')) {
                $errorMessage .= ' Error: ' . $e->getMessage();
            }

            return response()->json([
                'success' => false,
                'errors' => [$errorMessage]
            ], 500);
        }

        Session::forget('milestone_setup_step1');
        Session::forget('milestone_setup_step2');
        Session::forget('milestone_setup_items');

        $message = $isEditing ? 'Milestone plan updated successfully!' : 'Milestone plan created successfully!';

        // Notify project owner about milestone submission/update
        $ownerUserId = DB::table('projects as p')
            ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
            ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
            ->where('p.project_id', $step1['project_id'])
            ->value('po.user_id');
        if ($ownerUserId) {
            $projTitle = DB::table('projects')->where('project_id', $step1['project_id'])->value('project_title');
            $subType = $isEditing ? 'milestone_updated' : 'milestone_submitted';
            $title = $isEditing ? 'Milestone Updated' : 'Milestone Submitted';
            $msg = $isEditing
                ? "Contractor updated a milestone for \"{$projTitle}\". Please review."
                : "Contractor submitted a milestone plan for \"{$projTitle}\". Please review.";
            notificationService::create($ownerUserId, $subType, $title, $msg, 'normal', 'milestone', (int) $milestoneId, ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $step1['project_id'], 'tab' => 'milestones']]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'milestone_id' => $milestoneId,
                'redirect_url' => '/contractor/myprojects'
            ], $isEditing ? 200 : 201);
        } else {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => '/contractor/myprojects'
            ]);
        }
    }

    // apiSubmitMilestones, apiUpdateMilestone, deleteMilestone moved to
    // App\Http\Controllers\both\milestoneController

    // API endpoint for contractor mobile app to get their assigned projects

    // API endpoint for contractor mobile app to get their assigned projects
    public function apiGetContractorProjects(Request $request)
    {
        try {
            $userId = $request->query('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'user_id parameter is required'
                ], 400);
            }

            // Get contractor info - check both direct contractor ownership and staff membership
            $contractor = DB::table('contractors')
                ->where('user_id', $userId)
                ->first();

            // If not a direct contractor owner, check if user is a staff member
            if (!$contractor) {
                $contractorUser = DB::table('contractor_users')
                    ->where('user_id', $userId)
                    ->where('is_active', 1)
                    ->where('is_deleted', 0)
                    ->first();

                if ($contractorUser) {
                    // Get the contractor this staff member belongs to
                    $contractor = DB::table('contractors')
                        ->where('contractor_id', $contractorUser->contractor_id)
                        ->first();
                }
            }

            if (!$contractor) {
                return response()->json([
                    'success' => true,
                    'message' => 'No contractor profile found',
                    'data' => []
                ], 200);
            }

            // Get projects where this contractor is the selected contractor
            $projects = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->join('users as u', 'po.user_id', '=', 'u.user_id')
                ->join('bids as b', function ($join) use ($contractor) {
                    $join->on('p.project_id', '=', 'b.project_id')
                        ->where('b.contractor_id', '=', $contractor->contractor_id)
                        ->where('b.bid_status', '=', 'accepted');
                })
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
                    'ct.type_name',
                    'p.project_status',
                    'pr.project_post_status',
                    DB::raw('DATE(pr.created_at) as created_at'),
                    DB::raw("CONCAT(po.first_name, ' ', po.last_name) as owner_name"),
                    'u.profile_pic as owner_profile_pic',
                    'u.user_id as owner_user_id',
                    'b.bid_id',
                    'b.proposed_cost',
                    'b.estimated_timeline',
                    'b.contractor_notes',
                    'b.bid_status'
                )
                ->orderBy('p.project_id', 'desc')
                ->get();

            // Add milestones info and owner info for each project
            foreach ($projects as $project) {
                // Get milestones with full details
                $milestones = DB::table('milestones')
                    ->where('project_id', $project->project_id)
                    ->where('contractor_id', $contractor->contractor_id)
                    ->where(function ($query) {
                        $query->whereNull('is_deleted')
                            ->orWhere('is_deleted', 0);
                    })
                    ->get();

                // Add items and payment plan for each milestone
                foreach ($milestones as $milestone) {
                    $milestone->items = DB::table('milestone_items')
                        ->where('milestone_id', $milestone->milestone_id)
                        ->orderBy('sequence_order', 'asc')
                        ->get();

                    // Enrich each item with progress report and payment status summaries
                    foreach ($milestone->items as $mItem) {
                        $mItemId = $mItem->item_id;

                        // Latest progress report status
                        $latestProgress = $this->progressUploadClass->getLatestProgressForItem($mItemId);
                        $mItem->latest_progress_status = $latestProgress->progress_status ?? null;
                        $mItem->latest_progress_date = $latestProgress->submitted_at ?? null;

                        // Progress report counts
                        $mItem->progress_submitted_count = DB::table('progress')
                            ->where('milestone_item_id', $mItemId)
                            ->where('progress_status', 'submitted')
                            ->count();
                        $mItem->progress_rejected_count = DB::table('progress')
                            ->where('milestone_item_id', $mItemId)
                            ->where('progress_status', 'rejected')
                            ->count();

                        // Latest payment status
                        $latestPayment = DB::table('milestone_payments')
                            ->where('item_id', $mItemId)
                            ->whereNotIn('payment_status', ['deleted'])
                            ->orderBy('transaction_date', 'desc')
                            ->select('payment_status', 'transaction_date')
                            ->first();
                        $mItem->latest_payment_status = $latestPayment->payment_status ?? null;
                        $mItem->latest_payment_date = $latestPayment->transaction_date ?? null;

                        // Payment counts
                        $mItem->payment_submitted_count = DB::table('milestone_payments')
                            ->where('item_id', $mItemId)
                            ->where('payment_status', 'submitted')
                            ->count();
                        $mItem->payment_rejected_count = DB::table('milestone_payments')
                            ->where('item_id', $mItemId)
                            ->where('payment_status', 'rejected')
                            ->count();
                    }

                    $milestone->payment_plan = DB::table('payment_plans')
                        ->where('plan_id', $milestone->plan_id)
                        ->first();
                }

                $project->milestones = $milestones;
                $project->milestones_count = count($milestones);

                // Add owner_info for contractor view
                $ownerInfo = DB::table('property_owners as po')
                    ->join('users as u', 'po.user_id', '=', 'u.user_id')
                    ->where('po.owner_id', function ($query) use ($project) {
                        $query->select('owner_id')
                            ->from('project_relationships')
                            ->join('projects', 'project_relationships.rel_id', '=', 'projects.relationship_id')
                            ->where('projects.project_id', $project->project_id)
                            ->limit(1);
                    })
                    ->select(
                        'po.owner_id',
                        'po.first_name',
                        'po.last_name',
                        'po.phone_number',
                        'u.username',
                        'u.email',
                        'u.profile_pic'
                    )
                    ->first();

                $project->owner_info = $ownerInfo;

                // Add accepted_bid for consistency with owner view
                $project->accepted_bid = [
                    'bid_id' => $project->bid_id,
                    'proposed_cost' => $project->proposed_cost,
                    'estimated_timeline' => $project->estimated_timeline . ' months',
                    'contractor_notes' => $project->contractor_notes,
                    'submitted_at' => null
                ];

                // Determine display status — project_status always takes priority
                $rawStatus = strtolower($project->project_status ?? '');
                if ($rawStatus === 'completed') {
                    $project->display_status = 'completed';
                } elseif ($rawStatus === 'halt' || $rawStatus === 'halted' || $rawStatus === 'on_hold') {
                    $project->display_status = 'on_hold';
                } elseif (count($milestones) === 0) {
                    $project->display_status = 'waiting_milestone_setup';
                } else {
                    // Determine milestone status breakdown
                    // Use setup_status to check pending owner approval (not milestone_status,
                    // which tracks work progress and stays 'not_started' until work begins).
                    $pendingApproval = DB::table('milestones')
                        ->where('project_id', $project->project_id)
                        ->where('contractor_id', $contractor->contractor_id)
                        ->where('setup_status', 'submitted')
                        ->where(function ($q) {
                            $q->whereNull('is_deleted')->orWhere('is_deleted', 0);
                        })
                        ->count();

                    $approvedMilestones = DB::table('milestones')
                        ->where('project_id', $project->project_id)
                        ->where('contractor_id', $contractor->contractor_id)
                        ->where('setup_status', 'approved')
                        ->where(function ($q) {
                            $q->whereNull('is_deleted')->orWhere('is_deleted', 0);
                        })
                        ->count();

                    if ($pendingApproval > 0 && $approvedMilestones === 0) {
                        // All milestones are still awaiting owner approval
                        $project->display_status = 'waiting_for_approval';
                    } else {
                        // At least one milestone has been approved → project is active
                        $project->display_status = 'in_progress';
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Projects retrieved successfully',
                'data' => $projects
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching contractor projects: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching your projects. Please try again.'
            ], 500);
        }
    }

    public function showMilestonePage(Request $request, $projectId)
    {
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck; // Return error response
        }

        $user = Session::get('user');
        $contractor = $this->contractorClass->getContractorByUserId($user->user_id);

        if (!$contractor) {
            return response()->json([
                'success' => false,
                'message' => 'Contractor profile not found.',
            ], 404);
        }

        $milestones = $this->contractorClass->getProjectMilestonesWithItems($projectId, $contractor->contractor_id);

        return response()->json([
            'success' => true,
            'data' => $milestones,
        ]);
    }

    public function showAIAnalytics(Request $request)
    {
        $accessCheck = $this->checkContractorAccess($request);
        if ($accessCheck) {
            return $accessCheck;
        }
        return view('contractor.AI');
    }
}
