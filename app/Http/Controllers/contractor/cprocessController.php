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

class cprocessController extends Controller
{
    protected $contractorClass;

    public function __construct()
    {
        $this->contractorClass = new contractorClass();
    }

    public function showHomepage(Request $request)
    {
        return view('contractor.contractor_Homepage');
    }

    public function showMessages(Request $request)
    {
        return view('contractor.contractor_Messages');
    }

    public function showProfile(Request $request)
    {
        return view('contractor.contractor_Profile');
    }

    public function showDashboard(Request $request)
    {
        return view('contractor.contractor_Dashboard');
    }

    public function showMyProjects(Request $request)
    {
        return view('contractor.contractor_Myprojects');
    }

    public function showMyBids(Request $request)
    {
        return view('contractor.contractor_Mybids');
    }

    public function showMilestoneReport(Request $request)
    {
        return view('contractor.contractor_MilestoneReport');
    }

    public function showMilestoneProgressReport(Request $request)
    {
        return view('contractor.contractor_MilestoneprogressReport');
    }

    private function checkContractorAccess(Request $request)
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
                    'message' => 'Access denied. Only contractors can access milestone setup.',
                    'redirect_url' => '/dashboard'
                ], 403);
            } else {
                return redirect('/dashboard')->with('error', 'Access denied. Only contractors can access milestone setup.');
            }
        }

        // For 'both' users, check current active role
        if ($user->user_type === 'both') {
            $currentRole = Session::get('current_role', 'contractor');
            if ($currentRole !== 'contractor') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied. Please switch to contractor role to access milestone setup.',
                        'redirect_url' => '/dashboard',
                        'suggested_action' => 'switch_to_contractor'
                    ], 403);
                } else {
                    return redirect('/dashboard')->with('error', 'Please switch to contractor role to access milestone setup.');
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
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'redirect_url' => '/accounts/login'
            ], 401);
        }

        if ($user->user_type !== 'both') {
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

        Session::put('current_role', $targetRole);

        if ($request->expectsJson()) {

            return response()->json([
                'success' => true,
                'message' => "Successfully switched to {$targetRole} role",
                'current_role' => $targetRole,
                'redirect_url' => '/dashboard'
            ]);
        } else {

            return redirect('/dashboard')->with('success', "Successfully switched to {$targetRole} role");
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
                // Default: if user_type is 'both', default to contractor, otherwise use their user_type
                $userType = is_object($user) ? $user->user_type : ($user['user_type'] ?? null);
                $currentRole = $userType === 'both' ? 'contractor' : $userType;
            }

            // Normalize current_role format (convert 'owner' to 'contractor' for consistency)
            $normalizedRole = $currentRole;
            if ($currentRole === 'property_owner' || $currentRole === 'owner') {
                $normalizedRole = 'owner';
            } elseif ($currentRole === 'contractor') {
                $normalizedRole = 'contractor';
            }

            $userType = is_object($user) ? $user->user_type : ($user['user_type'] ?? null);

            return response()->json([
                'success' => true,
                'user_type' => $userType,
                'current_role' => $normalizedRole,
                'can_switch_roles' => $userType === 'both'
            ]);
        } catch (\Exception $e) {
            \Log::error('getCurrentRole error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
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
                        'errors' => ["Cannot modify milestone items because {$blockingPaymentsCount} payment(s) are associated with existing milestone items. Remove or resolve those payments before editing the milestone." ]
                    ], 409);
                }

                // Safe to delete existing milestone items
                $this->contractorClass->deleteMilestoneItems($milestoneId);
            } else {
                // Create new milestone
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'milestone_id' => $milestoneId,
                'redirect_url' => '/dashboard'
            ], $isEditing ? 200 : 201);
        } else {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => '/dashboard'
            ]);
        }
    }

    public function apiSubmitMilestones(Request $request, $projectId)
    {
        // Validate user_id from request
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required'
            ], 400);
        }

        // Get contractor by user_id
        $contractor = $this->contractorClass->getContractorByUserId($userId);
        if (!$contractor) {
            return response()->json([
                'success' => false,
                'message' => 'Contractor not found for user_id: ' . $userId
            ], 404);
        }

        // Log contractor data for debugging
        Log::info('Contractor found:', ['contractor' => $contractor]);

        // Validate the request data
        $validated = $request->validate([
            'milestone_name' => 'required|string|max:200',
            'payment_mode' => 'required|in:downpayment,full_payment',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_project_cost' => 'required|numeric|min:0',
            'downpayment_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.percentage' => 'required|numeric|min:0|max:100',
            'items.*.date_to_finish' => 'required|date|after_or_equal:start_date|before_or_equal:end_date'
        ]);

        // Verify project exists and contractor has access (accepted bid)
        $project = DB::table('projects as p')
            ->join('bids as b', function($join) use ($contractor) {
                $join->on('b.project_id', '=', 'p.project_id')
                     ->where('b.contractor_id', '=', $contractor->contractor_id)
                     ->where('b.bid_status', '=', 'accepted');
            })
            ->where('p.project_id', $projectId)
            ->select('p.*')
            ->first();

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found or you do not have access to it'
            ], 404);
        }

        // Validate items percentages sum to 100
        $totalPercentage = array_sum(array_column($validated['items'], 'percentage'));
        if (round($totalPercentage, 2) != 100) {
            return response()->json([
                'success' => false,
                'message' => 'Milestone percentages must add up to exactly 100%'
            ], 400);
        }

        // Format dates for database
        $startDate = date('Y-m-d 00:00:00', strtotime($validated['start_date']));
        $endDate = date('Y-m-d 23:59:59', strtotime($validated['end_date']));

        try {
            DB::beginTransaction();

            // Create payment plan
            $planId = $this->contractorClass->createPaymentPlan([
                'project_id' => $projectId,
                'contractor_id' => $contractor->contractor_id,
                'payment_mode' => $validated['payment_mode'],
                'total_project_cost' => $validated['total_project_cost'],
                'downpayment_amount' => $validated['downpayment_amount'] ?? 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create milestone
            $milestoneId = $this->contractorClass->createMilestone([
                'project_id' => $projectId,
                'contractor_id' => $contractor->contractor_id,
                'plan_id' => $planId,
                'milestone_name' => $validated['milestone_name'],
                'milestone_description' => $validated['milestone_name'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'milestone_status' => 'not_started',
                'setup_status' => 'submitted',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Calculate remaining amount after downpayment
            $remainingAmount = $validated['total_project_cost'] - ($validated['downpayment_amount'] ?? 0);

            // Create milestone items
            foreach ($validated['items'] as $index => $item) {
                $percentage = $item['percentage'];

                // Calculate cost based on payment mode
                $itemCostBase = $validated['payment_mode'] === 'downpayment'
                    ? $remainingAmount
                    : $validated['total_project_cost'];
                $calculatedCost = $itemCostBase * ($percentage / 100);

                $this->contractorClass->createMilestoneItem([
                    'milestone_id' => $milestoneId,
                    'sequence_order' => $index + 1,
                    'percentage_progress' => $percentage,
                    'milestone_item_title' => $item['title'],
                    'milestone_item_description' => $item['description'] ?? '',
                    'milestone_item_cost' => round($calculatedCost, 2),
                    'date_to_finish' => date('Y-m-d 23:59:59', strtotime($item['date_to_finish']))
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Milestone plan created successfully!',
                'data' => [
                    'milestone_id' => $milestoneId,
                    'plan_id' => $planId
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving milestone (API): ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the milestone. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function apiUpdateMilestone(Request $request, $projectId, $milestoneId)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required'], 400);
        }

        $contractor = $this->contractorClass->getContractorByUserId($userId);
        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'milestone_name' => 'required|string|max:200',
            'payment_mode' => 'required|in:downpayment,full_payment',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_project_cost' => 'required|numeric|min:0',
            'downpayment_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.title' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.percentage' => 'required|numeric|min:0|max:100',
            'items.*.date_to_finish' => 'required|date|after_or_equal:start_date|before_or_equal:end_date'
        ]);

        // Verify milestone exists and belongs to contractor
        $milestone = DB::table('milestones')
            ->where('milestone_id', $milestoneId)
            ->where('project_id', $projectId)
            ->where('contractor_id', $contractor->contractor_id)
            ->first();

        if (!$milestone) {
            return response()->json(['success' => false, 'message' => 'Milestone not found'], 404);
        }

        // Validate items percentages sum to 100
        $totalPercentage = array_sum(array_column($validated['items'], 'percentage'));
        if (round($totalPercentage, 2) != 100) {
            return response()->json(['success' => false, 'message' => 'Milestone percentages must add up to exactly 100%'], 400);
        }

        $startDate = date('Y-m-d 00:00:00', strtotime($validated['start_date']));
        $endDate = date('Y-m-d 23:59:59', strtotime($validated['end_date']));

        try {
            DB::beginTransaction();

            // Update payment plan
            DB::table('payment_plans')
                ->where('plan_id', $milestone->plan_id)
                ->update([
                    'payment_mode' => $validated['payment_mode'],
                    'total_project_cost' => $validated['total_project_cost'],
                    'downpayment_amount' => $validated['downpayment_amount'] ?? 0,
                    'updated_at' => now()
                ]);

            // Update milestone - reset to submitted status and clear rejection
            DB::table('milestones')
                ->where('milestone_id', $milestoneId)
                ->update([
                    'milestone_name' => $validated['milestone_name'],
                    'milestone_description' => $validated['milestone_name'],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'setup_status' => 'submitted',
                    'setup_rej_reason' => null,
                    'updated_at' => now()
                ]);

            // Delete existing milestone items
            DB::table('milestone_items')->where('milestone_id', $milestoneId)->delete();

            // Calculate remaining amount after downpayment
            $remainingAmount = $validated['total_project_cost'] - ($validated['downpayment_amount'] ?? 0);

            // Create updated milestone items
            foreach ($validated['items'] as $index => $item) {
                $percentage = $item['percentage'];
                $itemCostBase = $validated['payment_mode'] === 'downpayment' ? $remainingAmount : $validated['total_project_cost'];
                $calculatedCost = $itemCostBase * ($percentage / 100);

                $this->contractorClass->createMilestoneItem([
                    'milestone_id' => $milestoneId,
                    'sequence_order' => $index + 1,
                    'percentage_progress' => $percentage,
                    'milestone_item_title' => $item['title'],
                    'milestone_item_description' => $item['description'] ?? '',
                    'milestone_item_cost' => round($calculatedCost, 2),
                    'date_to_finish' => date('Y-m-d 23:59:59', strtotime($item['date_to_finish']))
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Milestone updated successfully!',
                'data' => ['milestone_id' => $milestoneId]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating milestone (API): ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the milestone.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function deleteMilestone(Request $request, $milestoneId)
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
                'message' => 'Contractor profile not found.'
            ], 404);
        }

        // Validate deletion reason
        $request->validate([
            'reason' => 'required|string|max:500'
        ], [
            'reason.required' => 'Please provide a reason for deletion.',
            'reason.max' => 'Deletion reason cannot exceed 500 characters.'
        ]);

        // Verify milestone belongs to contractor
        $milestone = DB::table('milestones')
            ->where('milestone_id', $milestoneId)
            ->where('contractor_id', $contractor->contractor_id)
            ->first();

        if (!$milestone) {
            return response()->json([
                'success' => false,
                'message' => 'Milestone not found or you do not have permission to delete it.'
            ], 404);
        }

        // Check if milestone is already deleted
        if (isset($milestone->is_deleted) && $milestone->is_deleted) {
            return response()->json([
                'success' => false,
                'message' => 'This milestone has already been deleted.'
            ], 400);
        }

        try {
            // Soft delete: Set is_deleted to true
            $updateData = [
                'is_deleted' => 1,
                'updated_at' => now()
            ];

            // Try to update with reason first
            try {
                $updateDataWithReason = array_merge($updateData, ['reason' => $request->input('reason')]);
                DB::table('milestones')
                    ->where('milestone_id', $milestoneId)
                    ->update($updateDataWithReason);
            } catch (\Exception $e) {
                // If reason column doesn't exist, try without it
                if (strpos($e->getMessage(), 'reason') !== false || strpos($e->getMessage(), 'Unknown column') !== false) {
                    Log::info('reason column does not exist, updating without it: ' . $e->getMessage());
                    DB::table('milestones')
                        ->where('milestone_id', $milestoneId)
                        ->update($updateData);
                } else {
                    // Re-throw if it's a different error
                    throw $e;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Milestone deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting milestone: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the milestone. Please try again.'
            ], 500);
        }
    }

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

            // Get contractor info
            $contractor = DB::table('contractors')
                ->where('user_id', $userId)
                ->first();

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
                ->join('bids as b', function($join) use ($contractor) {
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
                    ->where(function($query) {
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

                    $milestone->payment_plan = DB::table('payment_plans')
                        ->where('plan_id', $milestone->plan_id)
                        ->first();
                }

                $project->milestones = $milestones;
                $project->milestones_count = count($milestones);

                // Add owner_info for contractor view
                $ownerInfo = DB::table('property_owners as po')
                    ->join('users as u', 'po.user_id', '=', 'u.user_id')
                    ->where('po.owner_id', function($query) use ($project) {
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
                        'u.username',
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

                // Determine display status
                if (count($milestones) === 0) {
                    $project->display_status = 'waiting_milestone_setup';
                } else {
                    // Check if all milestones are approved/completed
                    $pendingMilestones = DB::table('milestones')
                        ->where('project_id', $project->project_id)
                        ->where('contractor_id', $contractor->contractor_id)
                        ->whereNotIn('milestone_status', ['approved', 'completed'])
                        ->count();

                    $project->display_status = $pendingMilestones > 0 ? 'in_progress' : 'completed';
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

        $milestones = $this->contractorClass->getProjectMilestones($projectId, $contractor->contractor_id);

        return response()->json([
            'success' => true,
            'data' => $milestones,
        ]);
    }
}
