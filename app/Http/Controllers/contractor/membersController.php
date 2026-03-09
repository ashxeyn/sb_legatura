<?php

namespace App\Http\Controllers\contractor;

use App\Http\Controllers\Controller;
use App\Services\ContractorAuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\NotificationService;

class membersController extends Controller
{
    protected ContractorAuthorizationService $authService;

    public function __construct(ContractorAuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Helper to get user_id from request (query param or header)
     */
    private function getUserId(Request $request)
    {
        return $request->query('user_id') 
            ?? $request->header('X-User-Id') 
            ?? null;
    }

    /**
     * Get contractor for a user
     */
    private function getContractor($userId)
    {
        return DB::table('contractors')
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get list of contractor team members
     */
    public function index(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'user_id parameter is required'
                ], 400);
            }

            // Authorization: Check if user is an active contractor member
            $memberContext = $this->authService->getMemberContext($userId);
            if (!$memberContext) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor member record not found',
                    'error_code' => 'MEMBER_NOT_FOUND'
                ], 403);
            }

            if (!$memberContext->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your contractor member account is inactive',
                    'error_code' => 'MEMBER_INACTIVE'
                ], 403);
            }

            // Get contractor - works for both owners and team members
            $contractor = $this->authService->getContractorForUser($userId);

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor not found'
                ], 404);
            }

            // Build query with filters
            $query = DB::table('contractor_users')
                ->join('users', 'contractor_users.user_id', '=', 'users.user_id')
                ->where('contractor_users.contractor_id', $contractor->contractor_id)
                ->where('contractor_users.is_deleted', 0);
            
            // Representatives cannot see owners
            if ($memberContext->role === 'representative') {
                $query->where('contractor_users.role', '!=', 'owner');
            }

            // Search filter (name, email, phone)
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('contractor_users.authorized_rep_fname', 'like', "%{$search}%")
                      ->orWhere('contractor_users.authorized_rep_lname', 'like', "%{$search}%")
                      ->orWhere('contractor_users.authorized_rep_mname', 'like', "%{$search}%")
                      ->orWhere('users.email', 'like', "%{$search}%")
                      ->orWhere('contractor_users.phone_number', 'like', "%{$search}%")
                      ->orWhere('users.username', 'like', "%{$search}%");
                });
            }

            // Role filter
            if ($request->has('role') && !empty($request->role) && $request->role !== 'all') {
                $query->where('contractor_users.role', $request->role);
            }

            // Status filter (active/inactive)
            if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
                $isActive = $request->status === 'active' ? 1 : 0;
                $query->where('contractor_users.is_active', $isActive);
            }

            // Get filtered team members
            $members = $query->select(
                    'contractor_users.contractor_user_id as id',
                    'contractor_users.user_id',
                    'contractor_users.authorized_rep_fname as first_name',
                    'contractor_users.authorized_rep_mname as middle_name',
                    'contractor_users.authorized_rep_lname as last_name',
                    'contractor_users.phone_number as phone',
                    'contractor_users.role',
                    'contractor_users.if_others as role_other',
                    'contractor_users.is_active',
                    'contractor_users.created_at',
                    'users.email',
                    'users.username',
                    'users.profile_pic',
                    'users.updated_at'
                )
                ->orderByRaw("FIELD(contractor_users.role, 'owner', 'manager', 'engineer', 'architect', 'representative', 'others')")
                ->get();

            return response()->json([
                'success' => true,
                'data' => $members,
                'filters' => [
                    'search' => $request->search ?? '',
                    'role' => $request->role ?? 'all',
                    'status' => $request->status ?? 'all'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching contractor members: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new team member
     * Only owner and representative roles can create members.
     */
    public function store(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'user_id parameter is required'
                ], 400);
            }

            // Authorization: Only owner and representative can add members
            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                Log::warning('Unauthorized member creation attempt', [
                    'user_id' => $userId,
                    'error' => $authError
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $authError,
                    'error_code' => 'INSUFFICIENT_PERMISSIONS'
                ], 403);
            }

            // Get contractor - works for both owners and team members
            $contractor = $this->authService->getContractorForUser($userId);

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor not found'
                ], 404);
            }

            // Validate request
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone_number' => 'nullable|string|max:20',
                'role' => 'required|in:owner,manager,engineer,architect,representative,others',
                'role_other' => 'nullable|string|max:255',
                'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            
            // Convert empty strings to null
            $validated['middle_name'] = !empty($validated['middle_name']) ? $validated['middle_name'] : null;
            $validated['phone_number'] = !empty($validated['phone_number']) ? $validated['phone_number'] : null;
            $validated['role_other'] = !empty($validated['role_other']) ? $validated['role_other'] : null;

            // Handle profile picture upload
            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                $profilePicPath = $request->file('profile_pic')->store('team_members', 'public');
            }

            // Generate unique username
            do {
                $username = 'staff_' . mt_rand(1000, 9999);
            } while (DB::table('users')->where('username', $username)->exists());

            // Create user record
            // Default password is 'teammember123@!' — login detects this via
            // Hash::check and forces the user to change it (no DB column needed).
            $newUserId = DB::table('users')->insertGetId([
                'profile_pic' => $profilePicPath,
                'username' => $username,
                'email' => $validated['email'],
                'password_hash' => bcrypt('teammember123@!'),
                'OTP_hash' => 'contractor_created',
                'user_type' => 'staff',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create contractor_users record
            $contractorUserId = DB::table('contractor_users')->insertGetId([
                'contractor_id' => $contractor->contractor_id,
                'user_id' => $newUserId,
                'authorized_rep_fname' => $validated['first_name'],
                'authorized_rep_mname' => $validated['middle_name'],
                'authorized_rep_lname' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
                'role' => $validated['role'],
                'if_others' => $validated['role'] === 'others' ? $validated['role_other'] : null,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => now()
            ]);

            // Send email notification
            try {
                Mail::raw(
                    "You have been added as a team member.\n\n" .
                    "Login Credentials:\n" .
                    "Username: " . $username . "\n" .
                    "Password: teammember123@!\n\n" .
                    "Please change your password after logging in.",
                    function ($message) use ($validated) {
                        $message->to($validated['email'])
                                ->subject('Team Member Account Created - Legatura');
                    }
                );
            } catch (\Exception $e) {
                Log::error('Failed to send team member email: ' . $e->getMessage());
            }

            // Notify the new team member
            NotificationService::create((int)$newUserId, 'team_member_added', 'Welcome to the Team', "You have been added as a {$validated['role']} to a contractor team.", 'normal', 'user', (int)$newUserId, ['screen' => 'Dashboard']);

            return response()->json([
                'success' => true,
                'message' => 'Team member created successfully',
                'data' => [
                    'id' => $contractorUserId,
                    'username' => $username,
                    'email' => $validated['email']
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating contractor member: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing team member
     * Only owner and representative roles can update members.
     */
    public function update(Request $request, $id)
    {
        try {
            // Handle JSON body for PUT requests
            // PHP doesn't automatically parse PUT request bodies like POST
            $contentType = $request->header('Content-Type', '');
            
            if ($request->isJson() || str_contains($contentType, 'application/json')) {
                // Try to get JSON from the request
                $jsonData = $request->json()->all();
                
                // If empty, try reading from raw input (fallback for PUT)
                if (empty($jsonData)) {
                    $rawBody = file_get_contents('php://input');
                    if ($rawBody) {
                        $jsonData = json_decode($rawBody, true) ?: [];
                    }
                }
                
                if (!empty($jsonData)) {
                    $request->merge($jsonData);
                }
            }

            $userId = $this->getUserId($request);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'user_id parameter is required'
                ], 400);
            }

            // Authorization: Only owner and representative can update members
            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                Log::warning('Unauthorized member update attempt', [
                    'user_id' => $userId,
                    'member_id' => $id,
                    'error' => $authError
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $authError,
                    'error_code' => 'INSUFFICIENT_PERMISSIONS'
                ], 403);
            }
            
            // Debug: Log incoming request data
            Log::info('Update member request', [
                'id' => $id,
                'method' => $request->method(),
                'content_type' => $contentType,
                'all_input' => $request->all(),
            ]);

            // Get contractor - works for both owners and team members
            $contractor = $this->authService->getContractorForUser($userId);

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor not found'
                ], 404);
            }

            // Check if team member belongs to this contractor
            $contractorUser = DB::table('contractor_users')
                ->where('contractor_user_id', $id)
                ->where('contractor_id', $contractor->contractor_id)
                ->first();

            if (!$contractorUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team member not found'
                ], 404);
            }

            // Get requester's role
            $requesterContext = $this->authService->getMemberContext($userId);
            
            // Prevent representatives from editing owners
            if ($requesterContext && $requesterContext->role === 'representative' && $contractorUser->role === 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'Representatives cannot edit the company owner',
                    'error_code' => 'CANNOT_EDIT_OWNER'
                ], 403);
            }

            // Validate request
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $contractorUser->user_id . ',user_id',
                'phone_number' => 'nullable|string|max:20',
                'role' => 'required|in:owner,manager,engineer,architect,representative,others',
                'role_other' => 'nullable|string|max:255',
                'profile_pic' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            
            // Convert empty strings to null
            $validated['middle_name'] = !empty($validated['middle_name']) ? $validated['middle_name'] : null;
            $validated['phone_number'] = !empty($validated['phone_number']) ? $validated['phone_number'] : null;
            $validated['role_other'] = !empty($validated['role_other']) ? $validated['role_other'] : null;

            // Handle profile picture upload
            $profilePicPath = null;
            if ($request->hasFile('profile_pic')) {
                $profilePicPath = $request->file('profile_pic')->store('team_members', 'public');
            }

            // Update users table
            $userData = [
                'email' => $validated['email'],
                'updated_at' => now()
            ];
            if ($profilePicPath) {
                $userData['profile_pic'] = $profilePicPath;
            }
            DB::table('users')
                ->where('user_id', $contractorUser->user_id)
                ->update($userData);

            // Update contractor_users record (no updated_at column in this table)
            DB::table('contractor_users')
                ->where('contractor_user_id', $id)
                ->update([
                    'authorized_rep_fname' => $validated['first_name'],
                    'authorized_rep_mname' => $validated['middle_name'],
                    'authorized_rep_lname' => $validated['last_name'],
                    'phone_number' => $validated['phone_number'],
                    'role' => $validated['role'],
                    'if_others' => $validated['role'] === 'others' ? $validated['role_other'] : null,
                ]);

            // Fetch and return updated member data with the new profile_pic and updated_at
            $updatedMember = DB::table('contractor_users')
                ->join('users', 'contractor_users.user_id', '=', 'users.user_id')
                ->where('contractor_users.contractor_user_id', $id)
                ->select(
                    'contractor_users.contractor_user_id as id',
                    'contractor_users.user_id',
                    'contractor_users.authorized_rep_fname as first_name',
                    'contractor_users.authorized_rep_mname as middle_name',
                    'contractor_users.authorized_rep_lname as last_name',
                    'contractor_users.phone_number as phone',
                    'contractor_users.role',
                    'contractor_users.if_others as role_other',
                    'contractor_users.is_active',
                    'users.email',
                    'users.username',
                    'users.profile_pic',
                    'users.updated_at'
                )
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Team member updated successfully',
                'data' => $updatedMember
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating contractor member: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete (soft delete) a team member
     * Only owner and representative roles can delete members.
     */
    public function delete(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'user_id parameter is required'
                ], 400);
            }

            // Authorization: Only owner and representative can delete members
            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                Log::warning('Unauthorized member deletion attempt', [
                    'user_id' => $userId,
                    'member_id' => $id,
                    'error' => $authError
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $authError,
                    'error_code' => 'INSUFFICIENT_PERMISSIONS'
                ], 403);
            }

            // Get contractor - works for both owners and team members
            $contractor = $this->authService->getContractorForUser($userId);

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor not found'
                ], 404);
            }

            // Check if team member belongs to this contractor
            $contractorUser = DB::table('contractor_users')
                ->where('contractor_user_id', $id)
                ->where('contractor_id', $contractor->contractor_id)
                ->first();

            if (!$contractorUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team member not found'
                ], 404);
            }

            // Prevent deleting owner
            if ($contractorUser->role === 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the company owner'
                ], 400);
            }

            // Get requester's role
            $requesterContext = $this->authService->getMemberContext($userId);
            
            // Prevent representatives from deleting owners (redundant check but safe)
            if ($requesterContext && $requesterContext->role === 'representative' && $contractorUser->role === 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'Representatives cannot delete the company owner',
                    'error_code' => 'CANNOT_DELETE_OWNER'
                ], 403);
            }

            // Soft delete
            DB::table('contractor_users')
                ->where('contractor_user_id', $id)
                ->update([
                    'is_active' => 0,
                    'is_deleted' => 1,
                    'deletion_reason' => 'Deleted by contractor'
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Team member deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting contractor member: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle member activation status
     * Only owner and representative roles can toggle member status.
     */
    public function toggleActive(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'user_id parameter is required'
                ], 400);
            }

            // Authorization: Only owner and representative can toggle member status
            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                Log::warning('Unauthorized member status toggle attempt', [
                    'user_id' => $userId,
                    'member_id' => $id,
                    'error' => $authError
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $authError,
                    'error_code' => 'INSUFFICIENT_PERMISSIONS'
                ], 403);
            }

            // Get contractor - works for both owners and team members
            $contractor = $this->authService->getContractorForUser($userId);

            if (!$contractor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contractor not found'
                ], 404);
            }

            // Check if team member belongs to this contractor
            $contractorUser = DB::table('contractor_users')
                ->where('contractor_user_id', $id)
                ->where('contractor_id', $contractor->contractor_id)
                ->where('is_deleted', 0)
                ->first();

            if (!$contractorUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Team member not found'
                ], 404);
            }

            // Get requester's role
            $requesterContext = $this->authService->getMemberContext($userId);
            
            // Prevent representatives from toggling owner status
            if ($requesterContext && $requesterContext->role === 'representative' && $contractorUser->role === 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'Representatives cannot change the company owner status',
                    'error_code' => 'CANNOT_TOGGLE_OWNER'
                ], 403);
            }

            // Prevent deactivating owner
            if ($contractorUser->role === 'owner' && $contractorUser->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate the company owner'
                ], 400);
            }

            // Toggle is_active
            $newStatus = $contractorUser->is_active ? 0 : 1;
            
            DB::table('contractor_users')
                ->where('contractor_user_id', $id)
                ->update([
                    'is_active' => $newStatus
                ]);

            // Notify the team member about their status change
            if ($contractorUser->user_id) {
                $statusLabel = $newStatus ? 'activated' : 'deactivated';
                NotificationService::create((int)$contractorUser->user_id, 'team_member_status', "Account {$statusLabel}", "Your team member account has been {$statusLabel}.", $newStatus ? 'normal' : 'high', 'user', (int)$contractorUser->user_id, ['screen' => 'Dashboard']);
            }

            return response()->json([
                'success' => true,
                'message' => $newStatus ? 'Team member activated' : 'Team member deactivated',
                'data' => [
                    'is_active' => (bool) $newStatus
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling contractor member status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
