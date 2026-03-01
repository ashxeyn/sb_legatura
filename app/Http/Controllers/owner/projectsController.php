<?php

namespace App\Http\Controllers\owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\owner\projectsRequest;
use App\Models\owner\projectsClass;
use App\Models\subs\platformPaymentClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\NotificationService;

class projectsController extends Controller
{
    protected $projectsClass;

    public function __construct(projectsClass $projectsClass)
    {
        $this->projectsClass = $projectsClass;
    }

    /**
     * @deprecated Moved to \App\Http\Controllers\both\dashboardController::unifiedDashboard()
     */
    public function showDashboard(Request $request)
    {
        return app(\App\Http\Controllers\both\dashboardController::class)->unifiedDashboard($request);
    }

    /**
     * @deprecated Moved to \App\Http\Controllers\both\homepageController::ownerHomepage()
     */
    public function showHomepage(Request $request)
    {
        return app(\App\Http\Controllers\both\homepageController::class)->ownerHomepage($request);
    }

    /**
     * @deprecated Moved to \App\Http\Controllers\both\dashboardController::ownerDashboard()
     */
    public function showOwnerDashboard(Request $request)
    {
        return app(\App\Http\Controllers\both\dashboardController::class)->ownerDashboard($request);
    }

    public function showAllProjects(Request $request)
    {
        // Allow access without login in local/testing environments
        $isLocalOrTesting = App::environment(['local', 'testing', 'development']);

        $user = Session::get('user');

        // Only require login in production/staging environments
        if (!$isLocalOrTesting && !$user) {
            return redirect('/accounts/login');
        }

        // If in testing mode and no user, allow access anyway
        if ($isLocalOrTesting && !$user) {
            return view('owner.propertyOwner_Allprojects', ['projects' => collect()]);
        }

        // Normal authentication flow for logged-in users
        $currentRole = session('current_role', $user->user_type);
        $userType = $user->user_type;

        if ($user) {
            $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                ($currentRole === 'owner' || $currentRole === 'property_owner');

            if (!$isOwner && !$isLocalOrTesting) {
                return redirect('/dashboard')->with('error', 'Only property owners can access this page.');
            }
        }

        // Fetch projects server-side to render cards directly in Blade
        $projects = collect();
        $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();

        if ($owner) {
            $rawProjects = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
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
                    'p.project_status',
                    'p.selected_contractor_id',
                    'ct.type_name',
                    'pr.project_post_status',
                    'pr.bidding_due',
                    DB::raw('DATE(pr.created_at) as created_at')
                )
                ->where('pr.owner_id', $owner->owner_id)
                ->whereNotIn('pr.project_post_status', ['deleted'])
                ->orderBy('pr.created_at', 'desc')
                ->get();

            foreach ($rawProjects as $project) {
                // ── Bids count ──────────────────────────────────────────────────
                $project->bids_count = DB::table('bids')
                    ->where('project_id', $project->project_id)
                    ->whereNotIn('bid_status', ['cancelled'])
                    ->count();

                // ── Files ───────────────────────────────────────────────────────
                $project->files = DB::table('project_files')
                    ->where('project_id', $project->project_id)
                    ->orderBy('uploaded_at', 'asc')
                    ->get();

                // ── Defaults ────────────────────────────────────────────────────
                $project->contractor_info = null;
                $project->accepted_bid    = null;
                $project->milestones      = [];
                $project->milestones_count = 0;
                $project->display_status  = $project->project_status;

                // Determine the contractor: prefer selected_contractor_id,
                // fall back to any accepted bid's contractor.
                $effectiveContractorId = $project->selected_contractor_id;
                if (!$effectiveContractorId) {
                    $effectiveContractorId = DB::table('bids')
                        ->where('project_id', $project->project_id)
                        ->where('bid_status', 'accepted')
                        ->value('contractor_id');
                }

                if ($effectiveContractorId) {
                    $project->selected_contractor_id = $effectiveContractorId;

                    // ── Accepted bid + contractor info ──────────────────────────
                    $acceptedBid = DB::table('bids as b')
                        ->join('contractors as c', 'b.contractor_id', '=', 'c.contractor_id')
                        ->join('users as u', 'c.user_id', '=', 'u.user_id')
                        ->select(
                            'b.bid_id',
                            'b.proposed_cost',
                            'b.estimated_timeline',
                            'b.contractor_notes',
                            'b.submitted_at',
                            'c.company_name',
                            'c.company_phone',
                            'c.company_email',
                            'c.years_of_experience',
                            'c.completed_projects',
                            'u.username',
                            'u.profile_pic'
                        )
                        ->where('b.project_id', $project->project_id)
                        ->where('b.contractor_id', $effectiveContractorId)
                        ->where('b.bid_status', 'accepted')
                        ->first();

                    if ($acceptedBid) {
                        $project->accepted_bid    = $acceptedBid;
                        $project->contractor_info = (object) [
                            'company_name'       => $acceptedBid->company_name,
                            'username'           => $acceptedBid->username,
                            'profile_pic'        => $acceptedBid->profile_pic,
                            'company_phone'      => $acceptedBid->company_phone,
                            'company_email'      => $acceptedBid->company_email,
                            'years_of_experience' => $acceptedBid->years_of_experience,
                            'completed_projects' => $acceptedBid->completed_projects,
                        ];

                        // ── Milestones (setup_status / milestone_status) ─────────
                        $milestones = DB::table('milestones')
                            ->select(
                                'milestone_id',
                                'milestone_name',
                                'milestone_description',
                                'milestone_status',
                                'setup_status',
                                'setup_rej_reason',
                                'start_date',
                                'end_date'
                            )
                            ->where('project_id', $project->project_id)
                            ->where('contractor_id', $project->selected_contractor_id)
                            ->where(function ($q) {
                                $q->whereNull('is_deleted')->orWhere('is_deleted', 0);
                            })
                            ->orderBy('created_at', 'asc')
                            ->get();

                        $project->milestones       = $milestones->values()->toArray();
                        $project->milestones_count = $milestones->count();

                        // ── display_status (matching API logic) ───────────────────
                        if ($milestones->isEmpty()) {
                            $project->display_status = 'waiting_milestone_setup';
                        } else {
                            $allDone = $milestones->every(
                                fn($m) => in_array($m->milestone_status, ['completed', 'approved'])
                            );
                            $project->display_status = $allDone ? 'completed' : 'in_progress';
                        }
                    }
                }
            }

            $projects = $rawProjects;
        }

        return view('owner.propertyOwner_Allprojects', compact('projects'));
    }

    public function showProfile(Request $request)
    {
        // Allow access without login in local/testing environments
        $isLocalOrTesting = App::environment(['local', 'testing', 'development']);

        $user = Session::get('user');

        // Only require login in production/staging environments
        if (!$isLocalOrTesting && !$user) {
            return redirect('/accounts/login');
        }

        // If in testing mode and no user, allow access anyway
        if ($isLocalOrTesting && !$user) {
            // Allow access without authentication for testing
            return view('owner.propertyOwner_Profile');
        }

        // Normal authentication flow for logged-in users
        $currentRole = session('current_role', $user->user_type);
        $userType = $user->user_type;

        // Only owners can access this page (skip in testing if no user)
        if ($user) {
            $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                ($currentRole === 'owner' || $currentRole === 'property_owner');

            if (!$isOwner && !$isLocalOrTesting) {
                return redirect('/dashboard')->with('error', 'Only property owners can access this page.');
            }
        }

        return view('owner.propertyOwner_Profile');
    }

    /**
     * GET /owner/projects/{projectId}/bids
     * Returns JSON list of bids with full contractor info, files, and ranking scores.
     */
    public function getProjectBids($projectId)
    {
        try {
            $user = Session::get('user');
            if (!$user) {
                return response('<p style="text-align:center;color:#ef4444;padding:32px 16px">Authentication required.</p>', 401)
                    ->header('Content-Type', 'text/html');
            }

            // Resolve owner_id from property_owners (same as acceptBid)
            $owner = DB::table('property_owners')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$owner) {
                return response('<p style="text-align:center;color:#ef4444;padding:32px 16px">Owner record not found.</p>', 404)
                    ->header('Content-Type', 'text/html');
            }

            // Verify the project belongs to this owner (owner_id lives in project_relationships)
            $project = DB::table('projects')
                ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                ->where('projects.project_id', $projectId)
                ->where('project_relationships.owner_id', $owner->owner_id)
                ->select('projects.*')
                ->first();

            if (!$project) {
                return response('<p style="text-align:center;color:#ef4444;padding:32px 16px">Project not found.</p>', 404)
                    ->header('Content-Type', 'text/html');
            }

            $bids = DB::table('bids as b')
                ->join('contractors as c', 'c.contractor_id', '=', 'b.contractor_id')
                ->join('users as u', 'u.user_id', '=', 'c.user_id')
                ->leftJoin('contractor_types as ct', 'c.type_id', '=', 'ct.type_id')
                ->where('b.project_id', $projectId)
                ->whereNotIn('b.bid_status', ['cancelled'])
                ->select(
                    'b.bid_id',
                    'b.project_id',
                    'b.contractor_id',
                    'b.proposed_cost',
                    'b.estimated_timeline',
                    'b.contractor_notes',
                    'b.bid_status',
                    'b.reason',
                    'b.submitted_at',
                    'b.decision_date',
                    'c.company_name',
                    'c.company_phone',
                    'c.company_email',
                    'c.company_website',
                    'c.years_of_experience',
                    'c.completed_projects',
                    'c.picab_category',
                    'u.username',
                    'u.profile_pic',
                    'ct.type_name as contractor_type'
                )
                ->get();

            // Attach bid files to each bid
            foreach ($bids as $bid) {
                $files = DB::table('bid_files')
                    ->where('bid_id', $bid->bid_id)
                    ->select('file_id', 'bid_id', 'file_name', 'file_path', 'description', 'uploaded_at')
                    ->get();
                $bid->files      = $files->toArray();
                $bid->file_count = $files->count();
            }

            // Apply ranking scores
            try {
                $ranker = app(\App\Services\BidRankingService::class);
                $bids   = $ranker->rankBids((int) $projectId, $bids);
            } catch (\Exception $re) {
                // Ranking failure is non-fatal — fall back to cost order
                $bids = $bids->sortByDesc(fn($b) => $b->bid_status === 'accepted' ? 1 : 0)
                             ->values();
            }

            return response(
                view('owner.propertyOwner_Modals.partials.bids_list', ['bids' => $bids->values()])->render()
            )->header('Content-Type', 'text/html');

        } catch (\Exception $e) {
            \Log::error('getProjectBids error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            return response('<p style="text-align:center;color:#ef4444;padding:32px 16px">Failed to load bids. Please try again.</p>')
                ->header('Content-Type', 'text/html')
                ->setStatusCode(500);
        }
    }

    /**
     * POST /owner/projects/{projectId}/bids/{bidId}/reject
     * Rejects a single bid (web session-based auth).
     */
    public function rejectBid(Request $request, $projectId, $bidId)
    {
        try {
            $user = Session::get('user');
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
            }

            $owner = DB::table('property_owners')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$owner) {
                return response()->json(['success' => false, 'message' => 'Owner record not found'], 404);
            }

            $project = DB::table('projects')
                ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                ->where('projects.project_id', $projectId)
                ->where('project_relationships.owner_id', $owner->owner_id)
                ->select('projects.*')
                ->first();

            if (!$project) {
                return response()->json(['success' => false, 'message' => 'Project not found'], 404);
            }

            $reason = $request->input('reason') ?: null;

            DB::table('bids')
                ->where('bid_id', $bidId)
                ->where('project_id', $projectId)
                ->update([
                    'bid_status'    => 'rejected',
                    'reason'        => $reason,
                    'decision_date' => now(),
                ]);

            // Notify the contractor
            $rejBid = DB::table('bids')->where('bid_id', $bidId)->first();
            if ($rejBid) {
                $cUserId = DB::table('contractors')
                    ->where('contractor_id', $rejBid->contractor_id)
                    ->value('user_id');
                if ($cUserId) {
                    $projTitle = $project->project_title ?? '';
                    NotificationService::create(
                        (int) $cUserId, 'bid_rejected', 'Bid Rejected',
                        "Your bid for \"{$projTitle}\" was not accepted.",
                        'normal', 'project', (int) $projectId,
                        ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $projectId]]
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Bid rejected successfully']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to reject bid'], 500);
        }
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
        // Allow access without login in local/testing environments
        $isLocalOrTesting = App::environment(['local', 'testing', 'development']);

        $user = Session::get('user');

        // Only require login in production/staging environments
        if (!$isLocalOrTesting && !$user) {
            return redirect('/accounts/login');
        }

        $projectId = Session::get('current_milestone_project_id');

        // Fallback for hardcoded direct links
        if (!$projectId && $request->has('project_id')) {
            $projectId = $request->query('project_id');
            Session::put('current_milestone_project_id', $projectId);
        }

        if (!$isLocalOrTesting && !$projectId) {
            return redirect('/owner/projects')->with('error', 'Project ID is required.');
        }

        // If in testing mode and no user, allow access anyway
        if ($isLocalOrTesting && !$user) {
            // Allow access without authentication for testing
            return view('owner.propertyOwner_MilestoneReport');
        }

        // Normal authentication flow for logged-in users
        $currentRole = session('current_role', $user->user_type);
        $userType = $user->user_type;

        // Only owners can access this page (skip in testing if no user)
        if ($user) {
            $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                ($currentRole === 'owner' || $currentRole === 'property_owner');

            if (!$isOwner && !$isLocalOrTesting) {
                return redirect('/dashboard')->with('error', 'Only property owners can access this page.');
            }
        }

        return view('owner.propertyOwner_MilestoneReport', [
            'projectId' => $projectId // Pass ID to view if needed by JS (but JS currently fetches from URL, will need update)
        ]);
    }

    public function showMilestoneProgressReport(Request $request)
    {
        // Allow access without login in local/testing environments
        $isLocalOrTesting = App::environment(['local', 'testing', 'development']);

        $user = Session::get('user');

        // Only require login in production/staging environments
        if (!$isLocalOrTesting && !$user) {
            return redirect('/accounts/login');
        }

        $itemId = Session::get('current_milestone_item_id');
        $projectId = Session::get('current_milestone_project_id');

        // Fallback for hardcoded direct links
        if (!$itemId && $request->has('item_id')) {
            $itemId = $request->query('item_id');
            Session::put('current_milestone_item_id', $itemId);
        }

        if (!$isLocalOrTesting && !$itemId) {
            return redirect('/owner/projects/milestone-report')->with('error', 'Item ID is required.');
        }

        // If in testing mode and no user, allow access anyway
        if ($isLocalOrTesting && !$user) {
            // Allow access without authentication for testing
            return view('owner.propertyOwner_MilestoneprogressReport');
        }

        // Normal authentication flow for logged-in users
        $currentRole = session('current_role', $user->user_type);
        $userType = $user->user_type;

        // Only owners can access this page (skip in testing if no user)
        if ($user) {
            $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                ($currentRole === 'owner' || $currentRole === 'property_owner');

            if (!$isOwner && !$isLocalOrTesting) {
                return redirect('/dashboard')->with('error', 'Only property owners can access this page.');
            }
        }

        return view('owner.propertyOwner_MilestoneprogressReport', [
            'itemId' => $itemId,
            'projectId' => $projectId
        ]);
    }

    public function showFinishedProjects(Request $request)
    {
        // Allow access without login in local/testing environments
        $isLocalOrTesting = App::environment(['local', 'testing', 'development']);

        $user = Session::get('user');

        // Only require login in production/staging environments
        if (!$isLocalOrTesting && !$user) {
            return redirect('/accounts/login');
        }

        // If in testing mode and no user, allow access anyway
        if ($isLocalOrTesting && !$user) {
            // Allow access without authentication for testing
            return view('owner.propertyOwner_Finishedprojects');
        }

        // Normal authentication flow for logged-in users
        $currentRole = session('current_role', $user->user_type);
        $userType = $user->user_type;

        // Only owners can access this page (skip in testing if no user)
        if ($user) {
            $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                ($currentRole === 'owner' || $currentRole === 'property_owner');

            if (!$isOwner && !$isLocalOrTesting) {
                return redirect('/dashboard')->with('error', 'Only property owners can access this page.');
            }
        }

        return view('owner.propertyOwner_Finishedprojects');
    }

    public function showMessages(Request $request)
    {
        // Allow access without login in local/testing environments
        $isLocalOrTesting = App::environment(['local', 'testing', 'development']);

        $user = Session::get('user');

        // Only require login in production/staging environments
        if (!$isLocalOrTesting && !$user) {
            return redirect('/accounts/login');
        }

        // If in testing mode and no user, allow access anyway
        if ($isLocalOrTesting && !$user) {
            // Allow access without authentication for testing
            return view('both.messages');
        }

        // Normal authentication flow for logged-in users
        $currentRole = session('current_role', $user->user_type);
        $userType = $user->user_type;

        // Only owners can access this page (skip in testing if no user)
        if ($user) {
            $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                ($currentRole === 'owner' || $currentRole === 'property_owner');

            if (!$isOwner && !$isLocalOrTesting) {
                return redirect('/dashboard')->with('error', 'Only property owners can access this page.');
            }
        }

        return view('both.messages');
    }

    public function showCreatePostPage(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/accounts/login');
        }

        $currentRole = session('current_role', $user->user_type);
        $userType = $user->user_type;

        // Only owners can create posts
        $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
            ($currentRole === 'owner' || $currentRole === 'property_owner');

        if (!$isOwner) {
            return redirect('/dashboard')->with('error', 'Only property owners can create project posts.');
        }

        $contractorTypes = $this->projectsClass->getContractorTypes();

        return view('owner.createPost', compact('contractorTypes'));
    }

    public function store(projectsRequest $request)
    {
        try {
            $user = Session::get('user');
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $currentRole = session('current_role', $user->user_type);
            $userType = $user->user_type;

            // Verify user is owner
            $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                ($currentRole === 'owner' || $currentRole === 'property_owner');

            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only property owners can create project posts'
                ], 403);
            }

            // Get owner_id
            $owner = DB::table('property_owners')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$owner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property owner record not found'
                ], 404);
            }

            $validated = $request->validated();

            // Create project relationship first
            $relationshipId = $this->projectsClass->createProjectRelationship(
                $owner->owner_id,
                $validated['bidding_deadline']
            );

            // Create project (include if_others_ctype when provided)
            $projectData = [
                'relationship_id' => $relationshipId,
                'project_title' => $validated['project_title'],
                'project_description' => $validated['project_description'],
                'project_location' => $validated['project_location'],
                'budget_range_min' => $validated['budget_range_min'],
                'budget_range_max' => $validated['budget_range_max'],
                'lot_size' => $validated['lot_size'],
                'floor_area' => $validated['floor_area'],
                'property_type' => $validated['property_type'],
                'type_id' => $validated['type_id']
            ];

            if (!empty($validated['if_others_ctype'])) {
                $projectData['if_others_ctype'] = $validated['if_others_ctype'];
            }

            // Create project
            $projectId = $this->projectsClass->createProject($projectData);

            // Ensure projects directory exists
            if (!Storage::disk('public')->exists('projects')) {
                Storage::disk('public')->makeDirectory('projects');
            }

            // Upload required files
            $fileTypes = [
                'building_permit' => 'building permit',
                'title_of_land' => 'title'
            ];

            foreach ($fileTypes as $inputName => $fileType) {
                if ($request->hasFile($inputName)) {
                    $file = $request->file($inputName);
                    $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $storagePath = $file->storeAs('projects', $fileName, 'public');

                    $this->projectsClass->createProjectFile([
                        'project_id' => $projectId,
                        'file_type' => $fileType,
                        'file_path' => $storagePath
                    ]);
                }
            }

            // Upload optional files (can be multiple)
            if ($request->hasFile('blueprint')) {
                $blueprintFiles = $request->file('blueprint');
                // Handle both single file and array of files
                $files = is_array($blueprintFiles) ? $blueprintFiles : [$blueprintFiles];

                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $storagePath = $file->storeAs('projects', $fileName, 'public');

                        $this->projectsClass->createProjectFile([
                            'project_id' => $projectId,
                            'file_type' => 'blueprint',
                            'file_path' => $storagePath
                        ]);
                    }
                }
            }

            if ($request->hasFile('desired_design')) {
                $designFiles = $request->file('desired_design');
                // Handle both single file and array of files
                $files = is_array($designFiles) ? $designFiles : [$designFiles];

                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $storagePath = $file->storeAs('projects', $fileName, 'public');

                        $this->projectsClass->createProjectFile([
                            'project_id' => $projectId,
                            'file_type' => 'desired design',
                            'file_path' => $storagePath
                        ]);
                    }
                }
            }

            // Upload other files (multiple)
            if ($request->hasFile('others')) {
                foreach ($request->file('others') as $file) {
                    $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $storagePath = $file->storeAs('projects', $fileName, 'public');

                    $this->projectsClass->createProjectFile([
                        'project_id' => $projectId,
                        'file_type' => 'others',
                        'file_path' => $storagePath
                    ]);
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Project posted successfully. It is now under review.',
                    'project_id' => $projectId
                ], 201);
            } else {
                return redirect('/dashboard')->with('success', 'Project posted successfully. It is now under review.');
            }
        } catch (\Exception $e) {
            \Log::error('Project creation error: ' . $e->getMessage(), [
                'user_id' => $user->user_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating project: ' . $e->getMessage()
                ], 500);
            } else {
                return back()->with('error', 'Error creating project: ' . $e->getMessage())->withInput();
            }
        }
    }

    public function showEditPostPage(Request $request, $projectId)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/accounts/login');
        }

        $currentRole = session('current_role', $user->user_type);
        $userType = $user->user_type;

        $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
            ($currentRole === 'owner' || $currentRole === 'property_owner');

        if (!$isOwner) {
            return redirect('/dashboard')->with('error', 'Only property owners can edit project posts.');
        }

        $owner = DB::table('property_owners')
            ->where('user_id', $user->user_id)
            ->first();

        if (!$owner) {
            return redirect('/dashboard')->with('error', 'Property owner record not found.');
        }

        // Verify ownership
        if (!$this->projectsClass->verifyOwnerProject($projectId, $owner->owner_id)) {
            return redirect('/dashboard')->with('error', 'You do not have permission to edit this project.');
        }

        $project = $this->projectsClass->getProjectById($projectId);
        $projectFiles = $this->projectsClass->getProjectFiles($projectId);
        $contractorTypes = $this->projectsClass->getContractorTypes();

        return view('owner.editPost', compact('project', 'projectFiles', 'contractorTypes'));
    }

    public function update(projectsRequest $request, $projectId)
    {
        try {
            $user = Session::get('user');
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $currentRole = session('current_role', $user->user_type);
            $userType = $user->user_type;

            $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                ($currentRole === 'owner' || $currentRole === 'property_owner');

            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only property owners can update project posts'
                ], 403);
            }

            $owner = DB::table('property_owners')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$owner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property owner record not found'
                ], 404);
            }

            // Verify ownership
            if (!$this->projectsClass->verifyOwnerProject($projectId, $owner->owner_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this project'
                ], 403);
            }

            $project = $this->projectsClass->getProjectById($projectId);
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

            $validated = $request->validated();

            // Update project (include if_others_ctype when provided)
            $updateData = [
                'project_title' => $validated['project_title'],
                'project_description' => $validated['project_description'],
                'project_location' => $validated['project_location'],
                'budget_range_min' => $validated['budget_range_min'],
                'budget_range_max' => $validated['budget_range_max'],
                'lot_size' => $validated['lot_size'],
                'floor_area' => $validated['floor_area'],
                'property_type' => $validated['property_type'],
                'type_id' => $validated['type_id']
            ];

            if (array_key_exists('if_others_ctype', $validated)) {
                $updateData['if_others_ctype'] = $validated['if_others_ctype'];
            }

            $this->projectsClass->updateProject($projectId, $updateData);

            // Update relationship bidding deadline
            if ($project->relationship_id) {
                $this->projectsClass->updateProjectRelationship(
                    $project->relationship_id,
                    $validated['bidding_deadline']
                );
            }

            // Handle file updates (delete old and upload new)
            // This is simplified - you may want to add logic to preserve existing files
            // and only update specific ones

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Project updated successfully. It is now under review again.'
                ], 200);
            } else {
                return redirect('/dashboard')->with('success', 'Project updated successfully. It is now under review again.');
            }
        } catch (\Exception $e) {
            \Log::error('Project update error: ' . $e->getMessage(), [
                'user_id' => $user->user_id ?? null,
                'project_id' => $projectId,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating project: ' . $e->getMessage()
                ], 500);
            } else {
                return back()->with('error', 'Error updating project: ' . $e->getMessage())->withInput();
            }
        }
    }

    public function delete(Request $request, $projectId)
    {
        try {
            $user = Session::get('user');
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $currentRole = session('current_role', $user->user_type);
            $userType = $user->user_type;

            $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                ($currentRole === 'owner' || $currentRole === 'property_owner');

            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only property owners can delete project posts'
                ], 403);
            }

            $owner = DB::table('property_owners')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$owner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property owner record not found'
                ], 404);
            }

            // Verify ownership
            if (!$this->projectsClass->verifyOwnerProject($projectId, $owner->owner_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this project'
                ], 403);
            }

            // Soft delete by updating project_post_status to 'deleted'
            $this->projectsClass->deleteProject($projectId);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Project deleted successfully'
                ], 200);
            } else {
                return redirect('/dashboard')->with('success', 'Project deleted successfully');
            }
        } catch (\Exception $e) {
            \Log::error(
                'Project delete error: ' . $e->getMessage(),
                [
                    'user_id' => $user->user_id ?? null,
                    'project_id' => $projectId,
                    'trace' => $e->getTraceAsString()
                ]
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting project: ' . $e->getMessage()
                ], 500);
            } else {
                return back()->with('error', 'Error deleting project: ' . $e->getMessage());
            }
        }
    }

    public function acceptBid(Request $request, $projectId, $bidId)
    {
        try {
            $user = Session::get('user');
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $currentRole = session('current_role', $user->user_type);
            $userType = $user->user_type;

            // Verify user is owner
            $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                ($currentRole === 'owner' || $currentRole === 'property_owner');

            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only property owners can accept bids'
                ], 403);
            }

            // Get owner_id
            $owner = DB::table('property_owners')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$owner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property owner record not found'
                ], 404);
            }

            // Accept the bid
            $this->projectsClass->acceptBid($projectId, $bidId, $owner->owner_id);

            // Notify contractor whose bid was accepted
            $bid = DB::table('bids')->where('bid_id', $bidId)->first();
            if ($bid) {
                $cUserId = DB::table('contractor_users')->where('contractor_id', $bid->contractor_id)->where('is_active', 1)->where('is_deleted', 0)->value('user_id');
                $projTitle = DB::table('projects')->where('project_id', $projectId)->value('project_title');
                if ($cUserId) {
                    NotificationService::create($cUserId, 'bid_accepted', 'Bid Accepted', "Your bid for \"{$projTitle}\" has been accepted!", 'high', 'project', (int) $projectId, ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $projectId]]);
                }

                // Notify all other contractors whose bids were rejected
                $rejectedBids = DB::table('bids')
                    ->where('project_id', $projectId)
                    ->where('bid_id', '!=', $bidId)
                    ->where('bid_status', 'rejected')
                    ->get();
                foreach ($rejectedBids as $rBid) {
                    $rUserId = DB::table('contractor_users')->where('contractor_id', $rBid->contractor_id)->where('is_active', 1)->where('is_deleted', 0)->value('user_id');
                    if ($rUserId) {
                        NotificationService::create((int) $rUserId, 'bid_rejected', 'Bid Not Selected', "The property owner has already chosen a contractor for \"{$projTitle}\". Thank you for your bid.", 'normal', 'bid', (int) $rBid->bid_id, ['screen' => 'MyBids', 'params' => ['projectId' => (int) $projectId]]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bid accepted successfully! Bidding is now closed.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to accept a bid for mobile app
     */
    public function apiAcceptBid(Request $request, $projectId, $bidId)
    {
        try {
            $userId = $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            // Get owner_id
            $owner = DB::table('property_owners')
                ->where('user_id', $userId)
                ->first();

            if (!$owner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property owner record not found'
                ], 404);
            }

            // Verify the project belongs to this owner
            $project = DB::table('projects')
                ->join('project_relationships', 'projects.relationship_id', '=', 'project_relationships.rel_id')
                ->where('projects.project_id', $projectId)
                ->where('project_relationships.owner_id', $owner->owner_id)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or you do not have permission to accept bids for this project'
                ], 403);
            }

            // Accept the bid
            $this->projectsClass->acceptBid($projectId, $bidId, $owner->owner_id);

            // Notify contractor whose bid was accepted
            $bid = DB::table('bids')->where('bid_id', $bidId)->first();
            if ($bid) {
                $cUserId = DB::table('contractor_users')->where('contractor_id', $bid->contractor_id)->where('is_active', 1)->where('is_deleted', 0)->value('user_id');
                if ($cUserId) {
                    NotificationService::create($cUserId, 'bid_accepted', 'Bid Accepted', "Your bid for \"{$project->project_title}\" has been accepted!", 'high', 'project', (int) $projectId, ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $projectId]]);
                }

                // Notify all other contractors whose bids were rejected
                $rejectedBids = DB::table('bids')
                    ->where('project_id', $projectId)
                    ->where('bid_id', '!=', $bidId)
                    ->where('bid_status', 'rejected')
                    ->get();
                foreach ($rejectedBids as $rBid) {
                    $rUserId = DB::table('contractor_users')->where('contractor_id', $rBid->contractor_id)->where('is_active', 1)->where('is_deleted', 0)->value('user_id');
                    if ($rUserId) {
                        NotificationService::create((int) $rUserId, 'bid_rejected', 'Bid Not Selected', "The property owner has already chosen a contractor for \"{$project->project_title}\". Thank you for your bid.", 'normal', 'bid', (int) $rBid->bid_id, ['screen' => 'MyBids', 'params' => ['projectId' => (int) $projectId]]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bid accepted successfully! Bidding is now closed.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @deprecated Moved to \App\Http\Controllers\both\HomepageController::apiGetContractors()
     */
    public function apiGetContractors(Request $request)
    {
        return app(\App\Http\Controllers\both\homepageController::class)->apiGetContractors($request);
    }

    /**
     * API endpoint to get owner's projects for mobile app
     */
    public function apiGetOwnerProjects(Request $request)
    {
        try {
            $userId = $request->query('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            // Get owner record
            $owner = DB::table('property_owners')
                ->where('user_id', $userId)
                ->first();

            if (!$owner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property owner record not found'
                ], 404);
            }

            // Get projects with relationships and contractor type info
            $projects = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
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
                    'p.project_status',
                    'p.selected_contractor_id',
                    'ct.type_name',
                    'pr.project_post_status',
                    'pr.bidding_due',
                    DB::raw('DATE(pr.created_at) as created_at')
                )
                ->where('pr.owner_id', $owner->owner_id)
                ->whereNotIn('pr.project_post_status', ['deleted'])
                ->orderBy('pr.created_at', 'desc')
                ->get();

            // Add bids_count, accepted_bid, and display_status for each project
            foreach ($projects as $project) {
                $bidCount = DB::table('bids')
                    ->where('project_id', $project->project_id)
                    ->whereNotIn('bid_status', ['cancelled'])
                    ->count();
                $project->bids_count = $bidCount;

                // Attach project files (from project_files table) so mobile clients
                // can use these as thumbnails/gallery images.
                $project->files = DB::table('project_files')
                    ->where('project_id', $project->project_id)
                    ->orderBy('uploaded_at', 'asc')
                    ->get();

                // If contractor is selected, get accepted bid details
                $project->accepted_bid = null;
                $project->display_status = $project->project_status;
                $project->contractor_info = null;

                // Determine the contractor: prefer selected_contractor_id,
                // fall back to any accepted bid's contractor (handles data inconsistency
                // where bid was accepted but selected_contractor_id was not set).
                $effectiveContractorId = $project->selected_contractor_id;
                if (!$effectiveContractorId) {
                    $effectiveContractorId = DB::table('bids')
                        ->where('project_id', $project->project_id)
                        ->where('bid_status', 'accepted')
                        ->value('contractor_id');
                }

                if ($effectiveContractorId) {
                    // Back-fill selected_contractor_id on the response object so the
                    // mobile client can rely on it for guards (e.g. hasContractor).
                    $project->selected_contractor_id = $effectiveContractorId;

                    // Get the accepted bid
                    $acceptedBid = DB::table('bids as b')
                        ->join('contractors as c', 'b.contractor_id', '=', 'c.contractor_id')
                        ->join('users as u', 'c.user_id', '=', 'u.user_id')
                        ->select(
                            'b.bid_id',
                            'b.proposed_cost',
                            'b.estimated_timeline',
                            'b.contractor_notes',
                            'b.submitted_at',
                            'c.contractor_id',
                            'c.company_name',
                            'c.company_phone',
                            'c.company_email',
                            'c.company_website',
                            'c.years_of_experience',
                            'c.completed_projects',
                            'c.picab_category',
                            'u.username',
                            'u.profile_pic'
                        )
                        ->where('b.project_id', $project->project_id)
                        ->where('b.contractor_id', $effectiveContractorId)
                        ->where('b.bid_status', 'accepted')
                        ->first();

                    if ($acceptedBid) {
                        $project->accepted_bid = $acceptedBid;
                        $project->contractor_info = (object) [
                            'company_name' => $acceptedBid->company_name,
                            'company_phone' => $acceptedBid->company_phone,
                            'company_email' => $acceptedBid->company_email,
                            'company_website' => $acceptedBid->company_website,
                            'years_of_experience' => $acceptedBid->years_of_experience,
                            'completed_projects' => $acceptedBid->completed_projects,
                            'picab_category' => $acceptedBid->picab_category,
                            'username' => $acceptedBid->username,
                            'profile_pic' => $acceptedBid->profile_pic
                        ];

                        // Get milestones for this project and contractor
                        $milestones = DB::table('milestones')
                            ->select(
                                'milestones.milestone_id',
                                'milestones.plan_id',
                                'milestones.milestone_name',
                                'milestones.milestone_description',
                                'milestones.milestone_status',
                                'milestones.setup_status',
                                'milestones.setup_rej_reason',
                                'milestones.start_date',
                                'milestones.end_date',
                                'milestones.created_at',
                                'milestones.updated_at'
                            )
                            ->where('milestones.project_id', $project->project_id)
                            ->where('milestones.contractor_id', $project->selected_contractor_id)
                            ->where(function ($query) {
                                $query->whereNull('milestones.is_deleted')
                                    ->orWhere('milestones.is_deleted', 0);
                            })
                            ->orderBy('milestones.created_at', 'asc')
                            ->get();

                        // Get milestone items and payment plan for each milestone
                        foreach ($milestones as $milestone) {
                            // Get milestone items
                            $milestone->items = DB::table('milestone_items')
                                ->select(
                                'item_id',
                                'sequence_order',
                                'percentage_progress',
                                'milestone_item_title',
                                'milestone_item_description',
                                'milestone_item_cost',
                                'adjusted_cost',
                                'carry_forward_amount',
                                'date_to_finish',
                                // include item_status so clients know completion state
                                DB::raw("COALESCE(item_status, '') as item_status")
                            )
                                ->where('milestone_id', $milestone->milestone_id)
                                ->orderBy('sequence_order', 'asc')
                                ->get()
                                ->toArray();

                            // Enrich each item with progress report and payment status summaries
                            foreach ($milestone->items as &$item) {
                                $itemId = $item->item_id;

                                // Latest progress report status
                                $latestProgress = DB::table('progress')
                                    ->where('milestone_item_id', $itemId)
                                    ->whereNotIn('progress_status', ['deleted'])
                                    ->orderBy('submitted_at', 'desc')
                                    ->select('progress_status', 'submitted_at')
                                    ->first();
                                $item->latest_progress_status = $latestProgress->progress_status ?? null;
                                $item->latest_progress_date = $latestProgress->submitted_at ?? null;

                                // Progress report counts
                                $item->progress_submitted_count = DB::table('progress')
                                    ->where('milestone_item_id', $itemId)
                                    ->where('progress_status', 'submitted')
                                    ->count();
                                $item->progress_rejected_count = DB::table('progress')
                                    ->where('milestone_item_id', $itemId)
                                    ->where('progress_status', 'rejected')
                                    ->count();

                                // Latest payment status
                                $latestPayment = DB::table('milestone_payments')
                                    ->where('item_id', $itemId)
                                    ->whereNotIn('payment_status', ['deleted'])
                                    ->orderBy('transaction_date', 'desc')
                                    ->select('payment_status', 'transaction_date')
                                    ->first();
                                $item->latest_payment_status = $latestPayment->payment_status ?? null;
                                $item->latest_payment_date = $latestPayment->transaction_date ?? null;

                                // Payment counts
                                $item->payment_submitted_count = DB::table('milestone_payments')
                                    ->where('item_id', $itemId)
                                    ->where('payment_status', 'submitted')
                                    ->count();
                                $item->payment_rejected_count = DB::table('milestone_payments')
                                    ->where('item_id', $itemId)
                                    ->where('payment_status', 'rejected')
                                    ->count();

                                // Attachments
                                $item->files = DB::table('item_files')
                                    ->where('item_id', $itemId)
                                    ->get();
                            }
                            unset($item); // break reference

                            // Get payment plan details
                            $paymentPlan = DB::table('payment_plans')
                                ->select(
                                    'plan_id',
                                    'payment_mode',
                                    'total_project_cost',
                                    'downpayment_amount',
                                    'is_confirmed'
                                )
                                ->where('plan_id', $milestone->plan_id)
                                ->first();

                            $milestone->payment_plan = $paymentPlan;
                        }

                        $project->milestones = $milestones->values()->toArray();
                        $project->milestones_count = $milestones->count();

                        // Determine display_status based on milestone states
                        if ($milestones->isEmpty()) {
                            $project->display_status = 'waiting_milestone_setup';
                        } else {
                            $allCompleted = $milestones->every(fn($m) => $m->milestone_status === 'completed' || $m->milestone_status === 'approved');

                            if ($allCompleted) {
                                $project->display_status = 'completed';
                            } else {
                                // If milestones exist (submitted or approved), project is in progress
                                $project->display_status = 'in_progress';
                            }
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Projects retrieved successfully',
                'data' => $projects
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving projects: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @deprecated Moved to \App\Http\Controllers\both\HomepageController::apiGetContractorTypes()
     */
    public function apiGetContractorTypes(Request $request)
    {
        return app(\App\Http\Controllers\both\homepageController::class)->apiGetContractorTypes($request);
    }

    /**
     * API endpoint to create a new project for mobile app
     */
    public function apiCreateProject(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            // Get owner record
            $owner = DB::table('property_owners')
                ->where('user_id', $userId)
                ->first();

            if (!$owner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property owner record not found'
                ], 404);
            }

            // Validate required fields
            $validated = $request->validate([
                'project_title' => 'required|string|max:200',
                'project_description' => 'required|string',
                'project_location' => 'required|string',
                'budget_range_min' => 'required|numeric|min:0',
                'budget_range_max' => 'required|numeric|min:0',
                'lot_size' => 'required|integer|min:1',
                'floor_area' => 'required|integer|min:1',
                'property_type' => 'required|in:Residential,Commercial,Industrial,Agricultural',
                'type_id' => 'required|integer|exists:contractor_types,type_id',
                'to_finish' => 'nullable|integer|min:1',
                'bidding_due' => 'required|date|after:today'
            ]);

            DB::beginTransaction();

            try {
                // Create project relationship first
                $relId = DB::table('project_relationships')->insertGetId([
                    'owner_id' => $owner->owner_id,
                    'project_post_status' => 'under_review',
                    'bidding_due' => $validated['bidding_due'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Create project
                $projectId = DB::table('projects')->insertGetId([
                    'relationship_id' => $relId,
                    'project_title' => $validated['project_title'],
                    'project_description' => $validated['project_description'],
                    'project_location' => $validated['project_location'],
                    'budget_range_min' => $validated['budget_range_min'],
                    'budget_range_max' => $validated['budget_range_max'],
                    'lot_size' => $validated['lot_size'],
                    'floor_area' => $validated['floor_area'],
                    'property_type' => $validated['property_type'],
                    'type_id' => $validated['type_id'],
                    'to_finish' => $validated['to_finish'] ?? null,
                    'project_status' => 'open'
                ]);

                // Handle file uploads
                // Building permit (required)
                if ($request->hasFile('building_permit')) {
                    $file = $request->file('building_permit');
                    $path = $file->store('project_files/building_permit', 'public');
                    DB::table('project_files')->insert([
                        'project_id' => $projectId,
                        'file_type' => 'building permit',
                        'file_path' => $path,
                        'uploaded_at' => now()
                    ]);
                }

                // Title of land (required)
                if ($request->hasFile('title_of_land')) {
                    $file = $request->file('title_of_land');
                    $path = $file->store('project_files/titles', 'public');
                    DB::table('project_files')->insert([
                        'project_id' => $projectId,
                        'file_type' => 'title',
                        'file_path' => $path,
                        'uploaded_at' => now()
                    ]);
                }

                // Blueprints (optional, multiple)
                if ($request->hasFile('blueprint')) {
                    $blueprints = $request->file('blueprint');
                    foreach ($blueprints as $file) {
                        $path = $file->store('project_files/blueprints', 'public');
                        DB::table('project_files')->insert([
                            'project_id' => $projectId,
                            'file_type' => 'blueprint',
                            'file_path' => $path,
                            'uploaded_at' => now()
                        ]);
                    }
                }

                // Desired designs (optional, multiple)
                if ($request->hasFile('desired_design')) {
                    $designs = $request->file('desired_design');
                    foreach ($designs as $file) {
                        $path = $file->store('project_files/designs', 'public');
                        DB::table('project_files')->insert([
                            'project_id' => $projectId,
                            'file_type' => 'desired design',
                            'file_path' => $path,
                            'uploaded_at' => now()
                        ]);
                    }
                }

                // Other files (optional, multiple)
                if ($request->hasFile('others')) {
                    $others = $request->file('others');
                    foreach ($others as $file) {
                        $path = $file->store('project_files/others', 'public');
                        DB::table('project_files')->insert([
                            'project_id' => $projectId,
                            'file_type' => 'others',
                            'file_path' => $path,
                            'uploaded_at' => now()
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Project created successfully and is under review',
                    'data' => [
                        'project_id' => $projectId,
                        'relationship_id' => $relId
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to get project details for mobile app
     */
    public function apiGetProjectDetails(Request $request, $projectId)
    {
        try {
            $userId = $request->query('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }


            // Get project with full details
            $project = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->leftJoin('users as u', 'po.user_id', '=', 'u.user_id')
                ->select(
                'p.*',
                'ct.type_name',
                'pr.project_post_status',
                'pr.bidding_due',
                'pr.owner_id',
                'po.first_name',
                'po.last_name',
                'po.owner_id as owner_id',
                'u.user_id as owner_user_id',
                'u.profile_pic as owner_profile_pic',
                'u.username as owner_username'
            )
                ->where('p.project_id', $projectId)
                ->first();

            // Verify ownership
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            if (!$owner || $owner->owner_id !== $project->owner_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this project'
                ], 403);
            }

            // Get bids count
            $bidsCount = DB::table('bids')
                ->where('project_id', $projectId)
                ->whereNotIn('bid_status', ['cancelled'])
                ->count();

            $project->bids_count = $bidsCount;

            return response()->json([
                'success' => true,
                'message' => 'Project details retrieved successfully',
                'data' => $project
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving project details: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * @deprecated Moved to \App\Http\Controllers\both\HomepageController::apiGetApprovedProjects()
     */
    public function apiGetApprovedProjects(Request $request)
    {
        return app(\App\Http\Controllers\both\homepageController::class)->apiGetApprovedProjects($request);
    }

    /**
     * API endpoint to reject a bid
     */
    public function apiRejectBid(Request $request, $projectId, $bidId)
    {
        try {
            $userId = $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            // Get owner record
            $owner = DB::table('property_owners')
                ->where('user_id', $userId)
                ->first();

            if (!$owner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property owner record not found'
                ], 404);
            }

            // Verify project belongs to owner
            $project = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->where('p.project_id', $projectId)
                ->where('pr.owner_id', $owner->owner_id)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found or unauthorized'
                ], 404);
            }

            // Update bid status to rejected
            DB::table('bids')
                ->where('bid_id', $bidId)
                ->where('project_id', $projectId)
                ->update([
                    'bid_status' => 'rejected',
                    'updated_at' => now()
                ]);

            // Notify contractor whose bid was rejected
            $rejBid = DB::table('bids')->where('bid_id', $bidId)->first();
            if ($rejBid) {
                $cUserId = DB::table('contractor_users')->where('contractor_id', $rejBid->contractor_id)->where('is_active', 1)->where('is_deleted', 0)->value('user_id');
                $projTitle = $project->project_title ?? DB::table('projects')->where('project_id', $projectId)->value('project_title');
                if ($cUserId) {
                    NotificationService::create($cUserId, 'bid_rejected', 'Bid Rejected', "Your bid for \"{$projTitle}\" was not accepted.", 'normal', 'project', (int) $projectId, ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $projectId]]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bid rejected successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting bid: ' . $e->getMessage()
            ], 500);
        }
    }

    // Milestone approve/reject/complete methods moved to
    // App\Http\Controllers\both\milestoneController

    /**
     * Complete a project (mark as completed)
     * API endpoint for mobile app
     */
    public function completeProject(Request $request, $projectId)
    {
        try {
            \Log::info('completeProject called', [
                'project_id' => $projectId,
                'bearer_token' => $request->bearerToken() ? 'present' : 'missing'
            ]);

            // Support both session-based auth (web) and token-based auth (mobile API)
            $user = Session::get('user');

            // If no session user, try to authenticate via Sanctum token
            if (!$user) {
                $bearerToken = $request->bearerToken();
                \Log::info('completeProject: No session user, checking bearer token', [
                    'token_present' => $bearerToken ? 'yes' : 'no'
                ]);

                if ($bearerToken) {
                    // Find the token in the database
                    $token = PersonalAccessToken::findToken($bearerToken);
                    if ($token) {
                        // Get the user associated with the token
                        $user = $token->tokenable;
                        \Log::info('completeProject: Token found, user authenticated', [
                            'user_id' => $user->user_id ?? null
                        ]);
                        // Store user in session for downstream code that expects it there
                        if ($user && !Session::has('user')) {
                            Session::put('user', $user);
                        }
                    } else {
                        \Log::warning('completeProject: Token not found in database');
                    }
                }

                // Fallback to request->user() if available (when middleware is applied)
                if (!$user && $request->user()) {
                    $user = $request->user();
                    \Log::info('completeProject: Using request->user()', [
                        'user_id' => $user->user_id ?? null
                    ]);
                    if (!Session::has('user')) {
                        Session::put('user', $user);
                    }
                }
            } else {
                \Log::info('completeProject: Using session user', [
                    'user_id' => $user->user_id ?? null
                ]);
            }

            if (!$user) {
                \Log::warning('completeProject: No user found, returning 401');
                return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
            }

            // Verify user is owner
            if (!in_array($user->user_type, ['property_owner', 'both'])) {
                return response()->json(['success' => false, 'message' => 'Access denied. Only owners can complete projects.'], 403);
            }

            // Get owner_id
            $owner = DB::table('property_owners')
                ->where('user_id', $user->user_id)
                ->first();

            if (!$owner) {
                return response()->json(['success' => false, 'message' => 'Owner profile not found'], 404);
            }

            // Verify the project belongs to this owner
            $project = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->where('p.project_id', $projectId)
                ->where('pr.owner_id', $owner->owner_id)
                ->select('p.*')
                ->first();

            if (!$project) {
                return response()->json(['success' => false, 'message' => 'Project not found or access denied'], 404);
            }

            // Check if all milestone items are completed
            $totalItems = DB::table('milestone_items as mi')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->where('m.project_id', $projectId)
                ->where('m.setup_status', 'approved')
                ->count();

            $completedItems = DB::table('milestone_items as mi')
                ->join('milestones as m', 'mi.milestone_id', '=', 'm.milestone_id')
                ->where('m.project_id', $projectId)
                ->where('m.setup_status', 'approved')
                ->where('mi.item_status', 'completed')
                ->count();

            if ($totalItems === 0) {
                return response()->json(['success' => false, 'message' => 'No approved milestones found for this project'], 400);
            }

            if ($completedItems < $totalItems) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot complete project. Not all milestone items are completed.',
                    'completed' => $completedItems,
                    'total' => $totalItems
                ], 400);
            }

            // Update project status to completed
            DB::table('projects')
                ->where('project_id', $projectId)
                ->update([
                    'project_status' => 'completed'
                ]);

            // Notify contractor that the project is completed
            if ($project->selected_contractor_id) {
                $cUserId = DB::table('contractor_users')->where('contractor_id', $project->selected_contractor_id)->where('is_active', 1)->where('is_deleted', 0)->value('user_id');
                if ($cUserId) {
                    NotificationService::create($cUserId, 'project_completed', 'Project Completed', "The project \"{$project->project_title}\" has been marked as completed. Congratulations!", 'high', 'project', (int) $projectId, ['screen' => 'ProjectDetails', 'params' => ['projectId' => (int) $projectId]]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Project completed successfully! Congratulations on finishing this project.',
                'project_id' => $projectId
            ]);

        } catch (\Exception $e) {
            \Log::error('completeProject error', ['error' => $e->getMessage(), 'projectId' => $projectId]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete project.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
