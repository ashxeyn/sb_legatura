<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\projectsRequest;
use App\Models\Owner\projectsClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class projectsController extends Controller
{
    protected $projectsClass;

    public function __construct(projectsClass $projectsClass)
    {
        $this->projectsClass = $projectsClass;
    }

    public function showDashboard(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect('/accounts/login');
        }

        $currentRole = session('current_role', $user->user_type);
        $userType = $user->user_type;

        // Determine if user is owner
        $isOwner = ($userType === 'property_owner' || $userType === 'both') &&
                   ($currentRole === 'owner' || $currentRole === 'property_owner');

        // Get owner_id if user is owner
        $ownerId = null;
        if ($isOwner) {
            $owner = DB::table('property_owners')
                ->where('user_id', $user->user_id)
                ->first();
            $ownerId = $owner ? $owner->owner_id : null;
        }

        // Get feed data based on role
        $feedItems = [];
        $feedType = 'projects'; // 'projects' or 'contractors'
        $contractorProjectsForMilestone = [];

        if ($isOwner && $ownerId) {
            // Owner view: Show all active contractor profiles (except themselves if both)
            $excludeUserId = ($userType === 'both') ? $user->user_id : null;
            $feedItems = $this->projectsClass->getActiveContractors($excludeUserId);
            $feedType = 'contractors';
        } else {
            // Contractor view: Show all approved projects
            $feedItems = $this->projectsClass->getApprovedProjects();
            $feedType = 'projects';

            // Get contractor projects for milestone setup (projects where contractor is selected and no milestone exists)
            $contractor = DB::table('contractors')->where('user_id', $user->user_id)->first();
            if ($contractor) {
                $contractorClass = new \App\Models\contractor\contractorClass();
                $contractorProjectsForMilestone = $contractorClass->getContractorProjects($contractor->contractor_id);
            }
        }

        // Get contractor types for dropdown
        $contractorTypes = $this->projectsClass->getContractorTypes();

        return view('both.dashboard', compact('feedItems', 'isOwner', 'contractorTypes', 'currentRole', 'userType', 'feedType', 'contractorProjectsForMilestone'));
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
            \Log::error('Project delete error: ' . $e->getMessage(),
            [
                'user_id' => $user->user_id ?? null,
                'project_id' => $projectId,
                'trace' => $e->getTraceAsString()
            ]);

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
     * API endpoint to get active contractors for mobile app
     * Used in property owner's feed/homepage
     */
    public function apiGetContractors(Request $request)
    {
        try {
            // Get active contractors (no authentication required for browsing)
            $contractors = $this->projectsClass->getActiveContractors();

            return response()->json([
                'success' => true,
                'message' => 'Contractors retrieved successfully',
                'data' => $contractors
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving contractors: ' . $e->getMessage()
            ], 500);
        }
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

                // If contractor is selected, get accepted bid details
                $project->accepted_bid = null;
                $project->display_status = $project->project_status;
                $project->contractor_info = null;

                if ($project->selected_contractor_id) {
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
                        ->where('b.contractor_id', $project->selected_contractor_id)
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
                                'milestones.start_date',
                                'milestones.end_date',
                                'milestones.created_at',
                                'milestones.updated_at'
                            )
                            ->where('milestones.project_id', $project->project_id)
                            ->where('milestones.contractor_id', $project->selected_contractor_id)
                            ->where(function($query) {
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
                                    'date_to_finish',
                                    // include item_status so clients know completion state
                                    DB::raw("COALESCE(item_status, '') as item_status")
                                )
                                ->where('milestone_id', $milestone->milestone_id)
                                ->orderBy('sequence_order', 'asc')
                                ->get()
                                ->toArray();

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
     * API endpoint to get contractor types for project creation form
     */
    public function apiGetContractorTypes(Request $request)
    {
        try {
            $contractorTypes = $this->projectsClass->getContractorTypes();

            return response()->json([
                'success' => true,
                'message' => 'Contractor types retrieved successfully',
                'data' => $contractorTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving contractor types: ' . $e->getMessage()
            ], 500);
        }
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
                ->select(
                    'p.*',
                    'ct.type_name',
                    'pr.project_post_status',
                    'pr.bidding_due',
                    'pr.owner_id',
                    'po.first_name',
                    'po.last_name'
                )
                ->where('p.project_id', $projectId)
                ->first();

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found'
                ], 404);
            }

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
     * API endpoint to get approved projects for contractors (feed)
     */
    public function apiGetApprovedProjects(Request $request)
    {
        try {
            $userId = $request->query('user_id');

            // Get contractor info if userId provided (for filtering)
            $contractorTypeId = null;
            if ($userId) {
                $contractor = DB::table('contractors')->where('user_id', $userId)->first();
                if ($contractor) {
                    $contractorTypeId = $contractor->type_id;
                }
            }

            // Get approved projects that are open for bidding
            $query = DB::table('projects as p')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->join('contractor_types as ct', 'p.type_id', '=', 'ct.type_id')
                ->join('property_owners as po', 'pr.owner_id', '=', 'po.owner_id')
                ->join('users as u', 'po.user_id', '=', 'u.user_id')
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
                    'pr.bidding_due as bidding_deadline',
                    DB::raw('DATE(pr.created_at) as created_at'),
                    DB::raw("CONCAT(po.first_name, ' ', po.last_name) as owner_name"),
                    'u.profile_pic as owner_profile_pic',
                    'u.user_id as owner_user_id'
                )
                ->where('pr.project_post_status', 'approved')
                ->where('p.project_status', 'open')
                ->where('pr.bidding_due', '>=', now());

            // Filter by contractor type if available
            if ($contractorTypeId) {
                $query->where('p.type_id', $contractorTypeId);
            }

            $projects = $query->orderBy('pr.created_at', 'desc')->get();

            // Add bids_count for each project
            foreach ($projects as $project) {
                $bidCount = DB::table('bids')
                    ->where('project_id', $project->project_id)
                    ->whereNotIn('bid_status', ['cancelled'])
                    ->count();
                $project->bids_count = $bidCount;
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

    /**
     * API endpoint to approve a milestone
     */
    public function apiApproveMilestone(Request $request, $milestoneId)
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

            // Get milestone with project info
            $milestone = DB::table('milestones as m')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->where('m.milestone_id', $milestoneId)
                ->where('pr.owner_id', $owner->owner_id)
                ->select('m.*', 'p.project_id')
                ->first();

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone not found or unauthorized'
                ], 404);
            }

            // Update milestone setup_status to approved
            DB::table('milestones')
                ->where('milestone_id', $milestoneId)
                ->update([
                    'setup_status' => 'approved',
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Milestone approved successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving milestone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to reject a milestone
     */
    public function apiRejectMilestone(Request $request, $milestoneId)
    {
        try {
            $userId = $request->input('user_id');
            $rejectionReason = $request->input('reason', '');

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

            // Get milestone with project info
            $milestone = DB::table('milestones as m')
                ->join('projects as p', 'm.project_id', '=', 'p.project_id')
                ->join('project_relationships as pr', 'p.relationship_id', '=', 'pr.rel_id')
                ->where('m.milestone_id', $milestoneId)
                ->where('pr.owner_id', $owner->owner_id)
                ->select('m.*', 'p.project_id')
                ->first();

            if (!$milestone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Milestone not found or unauthorized'
                ], 404);
            }

            // Update milestone setup_status to rejected
            DB::table('milestones')
                ->where('milestone_id', $milestoneId)
                ->update([
                    'setup_status' => 'rejected',
                    'rejection_reason' => $rejectionReason,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Milestone rejected successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting milestone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint to mark a milestone as complete
     */
    public function apiSetMilestoneComplete(Request $request, $milestoneId)
    {
        try {
            \Log::info('apiSetMilestoneComplete called', [
                'milestoneId' => $milestoneId,
                'request_data' => $request->all()
            ]);

            // Update milestone status to 'completed'
            $updated = DB::table('milestones')
                ->where('milestone_id', $milestoneId)
                ->update(['milestone_status' => 'completed']);

            \Log::info('apiSetMilestoneComplete update result', ['rows_affected' => $updated]);

            if ($updated === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No milestone found with ID: ' . $milestoneId,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Milestone marked as complete successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('apiSetMilestoneComplete error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark milestone as complete.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API endpoint to mark a milestone item as complete
     */
    public function apiSetMilestoneItemComplete(Request $request, $itemId)
    {
        try {
            \Log::info('apiSetMilestoneItemComplete called', [
                'itemId' => $itemId,
                'request_data' => $request->all()
            ]);

            // Try to fetch the milestone item first (robust lookup)
            $milestoneItem = DB::table('milestone_items')
                ->where('item_id', $itemId)
                ->first();

            // Fallback: check common alternative primary key name
            if (!$milestoneItem) {
                $milestoneItem = DB::table('milestone_items')
                    ->where('id', $itemId)
                    ->first();
            }

            \Log::info('apiSetMilestoneItemComplete lookup result', ['found' => $milestoneItem ? true : false, 'item' => $milestoneItem]);

            if (!$milestoneItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'No milestone item found with ID: ' . $itemId,
                ], 404);
            }

            // Update milestone item status to 'completed'
            // Ensure all progress reports for this milestone item are approved
            $nonApprovedCount = DB::table('progress')
                ->where('milestone_item_id', $milestoneItem->item_id)
                ->whereNotIn('progress_status', ['approved', 'deleted'])
                ->count();

            $warning = null;
            if ($nonApprovedCount > 0) {
                // Allow marking as complete but provide a warning to the caller
                $warning = 'There are ' . $nonApprovedCount . ' unapproved or pending progress reports for this milestone item.';
            }

            $updated = DB::table('milestone_items')
                ->where('item_id', $milestoneItem->item_id)
                ->update(['item_status' => 'completed']);

            \Log::info('apiSetMilestoneItemComplete update result', ['rows_affected' => $updated]);

            if ($updated === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update milestone item status for ID: ' . $milestoneItem->item_id,
                ], 500);
            }

            // Return the updated item info so frontend can update UI without refetch
            $updatedItem = DB::table('milestone_items')
                ->where('item_id', $milestoneItem->item_id)
                ->first();

            $responsePayload = [
                'success' => true,
                'message' => 'Milestone item marked as complete successfully.',
                'data' => [
                    'item_id' => $updatedItem->item_id,
                    'item_status' => $updatedItem->item_status ?? 'completed'
                ]
            ];

            if ($warning) {
                $responsePayload['warning'] = $warning;
                $responsePayload['non_approved_count'] = $nonApprovedCount;
                // include a friendly note for frontend display
                $responsePayload['message'] = 'Milestone item marked as complete. Note: ' . $warning;
            }

            return response()->json($responsePayload);
        } catch (\Exception $e) {
            \Log::error('apiSetMilestoneItemComplete error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark milestone item as complete.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

