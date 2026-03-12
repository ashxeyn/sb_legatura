<?php

namespace App\Http\Controllers\contractor;

use App\Http\Controllers\Controller;
use App\Services\ContractorAuthorizationService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class membersController extends Controller
{
    protected ContractorAuthorizationService $authService;

    public function __construct(ContractorAuthorizationService $authService)
    {
        $this->authService = $authService;
    }

    private function getUserId(Request $request)
    {
        return $request->query('user_id')
            ?? $request->header('X-User-Id')
            ?? null;
    }

    // -----------------------------------------------------------------------
    // SEARCH VERIFIED OWNERS (for invite dropdown)
    // -----------------------------------------------------------------------

    public function searchVerifiedOwners(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id is required'], 400);
            }

            // Only the company owner can invite staff
            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                return response()->json(['success' => false, 'message' => $authError, 'error_code' => 'INSUFFICIENT_PERMISSIONS'], 403);
            }

            $contractor = $this->authService->getContractorForUser($userId);
            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
            }

            $search = trim($request->query('q', ''));
            if (strlen($search) < 2) {
                return response()->json(['success' => true, 'data' => []]);
            }

            // Exclude owner_ids already in this contractor (including the owner themselves)
            $existingOwnerIds = DB::table('contractor_staff')
                ->where('contractor_id', $contractor->contractor_id)
                ->whereNull('deletion_reason')
                ->pluck('owner_id')
                ->toArray();

            $selfOwnerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
            if ($selfOwnerId) {
                $existingOwnerIds[] = (int) $selfOwnerId;
            }

            $owners = DB::table('property_owners')
                ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                ->where('property_owners.verification_status', 'approved')
                ->where('property_owners.is_active', 1)
                ->whereNotIn('property_owners.owner_id', $existingOwnerIds)
                ->where(function ($q) use ($search) {
                    $q->where('users.first_name', 'like', "%{$search}%")
                      ->orWhere('users.last_name', 'like', "%{$search}%")
                      ->orWhere('users.middle_name', 'like', "%{$search}%")
                      ->orWhere('users.email', 'like', "%{$search}%")
                      ->orWhere('users.username', 'like', "%{$search}%");
                })
                ->select(
                    'property_owners.owner_id',
                    'users.user_id',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name',
                    'users.email',
                    'users.username',
                    'property_owners.profile_pic'
                )
                ->limit(20)
                ->get();

            return response()->json(['success' => true, 'data' => $owners]);

        } catch (\Exception $e) {
            Log::error('Error searching verified owners: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // LIST STAFF MEMBERS
    // -----------------------------------------------------------------------

    public function index(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id parameter is required'], 400);
            }

            $memberContext = $this->authService->getMemberContext($userId);
            if (!$memberContext) {
                return response()->json(['success' => false, 'message' => 'Contractor member record not found', 'error_code' => 'MEMBER_NOT_FOUND'], 403);
            }
            if (!$memberContext->is_active) {
                return response()->json(['success' => false, 'message' => 'Your contractor member account is inactive', 'error_code' => 'MEMBER_INACTIVE'], 403);
            }
            if ($memberContext->is_suspended ?? 0) {
                return response()->json(['success' => false, 'message' => 'Your contractor account is currently suspended', 'error_code' => 'MEMBER_SUSPENDED'], 403);
            }

            $contractor = $this->authService->getContractorForUser($userId);
            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
            }

            $query = DB::table('contractor_staff')
                ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
                ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                ->where('contractor_staff.contractor_id', $contractor->contractor_id)
                ->whereNull('contractor_staff.deletion_reason');

            // Search by name/email/username
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('users.first_name', 'like', "%{$search}%")
                      ->orWhere('users.last_name', 'like', "%{$search}%")
                      ->orWhere('users.middle_name', 'like', "%{$search}%")
                      ->orWhere('users.email', 'like', "%{$search}%")
                      ->orWhere('users.username', 'like', "%{$search}%");
                });
            }

            // Role filter
            if ($request->filled('role') && $request->role !== 'all') {
                $query->where('contractor_staff.company_role', $request->role);
            }

            // Status filter: active | suspended | pending | all
            if ($request->filled('status') && $request->status !== 'all') {
                match ($request->status) {
                    'active'    => $query->where('contractor_staff.is_active', 1)->where('contractor_staff.is_suspended', 0),
                    'suspended' => $query->where('contractor_staff.is_suspended', 1),
                    'pending'   => $query->where('contractor_staff.is_active', 0),
                    default     => null,
                };
            }

            $members = $query->select(
                    'contractor_staff.staff_id as id',
                    'contractor_staff.owner_id',
                    'users.user_id',
                    'users.first_name',
                    'users.middle_name',
                    'users.last_name',
                    'users.email',
                    'users.username',
                    'property_owners.profile_pic',
                    'contractor_staff.company_role as role',
                    'contractor_staff.role_if_others as role_other',
                    'contractor_staff.is_active',
                    'contractor_staff.is_suspended',
                    'contractor_staff.suspension_reason',
                    'contractor_staff.created_at'
                )
                ->orderByRaw("FIELD(contractor_staff.company_role, 'representative', 'manager', 'engineer', 'architect', 'others')")
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $members,
                'filters' => [
                    'search' => $request->search ?? '',
                    'role'   => $request->role   ?? 'all',
                    'status' => $request->status ?? 'all',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching contractor members: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // INVITE A VERIFIED OWNER AS STAFF (owner only)
    // -----------------------------------------------------------------------

    public function store(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id parameter is required'], 400);
            }

            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                return response()->json(['success' => false, 'message' => $authError, 'error_code' => 'INSUFFICIENT_PERMISSIONS'], 403);
            }

            $contractor = $this->authService->getContractorForUser($userId);
            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
            }

            $validated = $request->validate([
                'owner_id'   => 'required|integer|exists:property_owners,owner_id',
                'role'       => 'required|in:representative,manager,engineer,architect,others',
                'role_other' => 'nullable|string|max:255|required_if:role,others',
            ]);

            // Target must be a verified, active property owner
            $targetOwner = DB::table('property_owners')
                ->where('owner_id', $validated['owner_id'])
                ->where('verification_status', 'approved')
                ->where('is_active', 1)
                ->first();

            if (!$targetOwner) {
                return response()->json(['success' => false, 'message' => 'The selected user is not a verified property owner.'], 422);
            }

            if ((int) $targetOwner->user_id === (int) $userId) {
                return response()->json(['success' => false, 'message' => 'You cannot invite yourself as a staff member.'], 422);
            }

            // Invitee must not already own their own contractor company
            $targetContractor = DB::table('contractors')->where('owner_id', $validated['owner_id'])->first();
            if ($targetContractor) {
                return response()->json(['success' => false, 'message' => 'This user already owns a contractor company and cannot be added as staff.'], 422);
            }

            // Must not already be a non-deleted member of this company
            $existing = DB::table('contractor_staff')
                ->where('contractor_id', $contractor->contractor_id)
                ->where('owner_id', $validated['owner_id'])
                ->whereNull('deletion_reason')
                ->first();

            if ($existing) {
                return response()->json(['success' => false, 'message' => 'This user is already a member of your company.'], 422);
            }

            // Insert invitation record — is_active = 0 until the owner accepts
            $staffId = DB::table('contractor_staff')->insertGetId([
                'contractor_id'  => $contractor->contractor_id,
                'owner_id'       => $validated['owner_id'],
                'company_role'   => $validated['role'],
                'role_if_others' => $validated['role'] === 'others' ? ($validated['role_other'] ?? null) : null,
                'is_active'      => 0,
                'is_suspended'   => 0,
                'created_at'     => now(),
            ]);

            // Notify the invited owner
            NotificationService::create(
                (int) $targetOwner->user_id,
                'staff_invitation',
                'Company Staff Invitation',
                "You have been invited to join {$contractor->company_name} as {$validated['role']}. Please accept or decline the invitation.",
                'normal',
                'contractor_staff',
                $staffId,
                ['screen' => 'StaffInvitations', 'staff_id' => $staffId]
            );

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent. The user will need to accept before joining the company.',
                'data'    => ['staff_id' => $staffId],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error inviting contractor staff: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // UPDATE STAFF ROLE (owner only)
    // -----------------------------------------------------------------------

    public function update(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id parameter is required'], 400);
            }

            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                return response()->json(['success' => false, 'message' => $authError, 'error_code' => 'INSUFFICIENT_PERMISSIONS'], 403);
            }

            $contractor = $this->authService->getContractorForUser($userId);
            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
            }

            $staffRecord = DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->where('contractor_id', $contractor->contractor_id)
                ->whereNull('deletion_reason')
                ->first();

            if (!$staffRecord) {
                return response()->json(['success' => false, 'message' => 'Staff member not found'], 404);
            }

            $validated = $request->validate([
                'role'       => 'required|in:representative,manager,engineer,architect,others',
                'role_other' => 'nullable|string|max:255|required_if:role,others',
            ]);

            // Track the old role in company_role_before whenever the role actually changes
            $updates = [
                'company_role'   => $validated['role'],
                'role_if_others' => $validated['role'] === 'others' ? ($validated['role_other'] ?? null) : null,
            ];

            if ($staffRecord->company_role !== $validated['role']) {
                $updates['company_role_before'] = $staffRecord->company_role;
            }

            DB::table('contractor_staff')->where('staff_id', $id)->update($updates);

            return response()->json([
                'success' => true,
                'message' => 'Staff role updated successfully',
                'data'    => [
                    'staff_id'            => (int) $id,
                    'role'                => $validated['role'],
                    'company_role_before' => $staffRecord->company_role !== $validated['role'] ? $staffRecord->company_role : ($staffRecord->company_role_before ?? null),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating contractor staff role: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // REMOVE STAFF (soft-delete, owner only)
    // -----------------------------------------------------------------------

    public function delete(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id parameter is required'], 400);
            }

            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                return response()->json(['success' => false, 'message' => $authError, 'error_code' => 'INSUFFICIENT_PERMISSIONS'], 403);
            }

            $contractor = $this->authService->getContractorForUser($userId);
            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
            }

            $staffRecord = DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->where('contractor_id', $contractor->contractor_id)
                ->whereNull('deletion_reason')
                ->first();

            if (!$staffRecord) {
                return response()->json(['success' => false, 'message' => 'Staff member not found'], 404);
            }

            $reason = $request->input('reason', 'Removed by company owner');

            DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->update([
                    'deletion_reason' => $reason,
                    'is_active'       => 0,
                ]);

            // Notify the removed staff member
            $targetOwner = DB::table('property_owners')->where('owner_id', $staffRecord->owner_id)->first();
            if ($targetOwner) {
                NotificationService::create(
                    (int) $targetOwner->user_id,
                    'staff_removed',
                    'Removed from Company',
                    "You have been removed from {$contractor->company_name}. Reason: {$reason}",
                    'high',
                    'contractor_staff',
                    (int) $id,
                    ['screen' => 'Dashboard']
                );
            }

            return response()->json(['success' => true, 'message' => 'Staff member removed successfully']);

        } catch (\Exception $e) {
            Log::error('Error removing contractor staff: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // SUSPEND STAFF (owner only — requires a reason)
    // -----------------------------------------------------------------------

    public function suspend(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id parameter is required'], 400);
            }

            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                return response()->json(['success' => false, 'message' => $authError, 'error_code' => 'INSUFFICIENT_PERMISSIONS'], 403);
            }

            $contractor = $this->authService->getContractorForUser($userId);
            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
            }

            $staffRecord = DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->where('contractor_id', $contractor->contractor_id)
                ->where('is_active', 1)
                ->whereNull('deletion_reason')
                ->first();

            if (!$staffRecord) {
                return response()->json(['success' => false, 'message' => 'Active staff member not found'], 404);
            }

            if ($staffRecord->is_suspended) {
                return response()->json(['success' => false, 'message' => 'Staff member is already suspended'], 400);
            }

            $validated = $request->validate([
                'reason' => 'required|string|min:5|max:500',
            ]);

            DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->update([
                    'is_suspended'     => 1,
                    'suspension_reason' => $validated['reason'],
                ]);

            // Notify the suspended staff member
            $targetOwner = DB::table('property_owners')->where('owner_id', $staffRecord->owner_id)->first();
            if ($targetOwner) {
                NotificationService::create(
                    (int) $targetOwner->user_id,
                    'staff_suspended',
                    'Account Suspended',
                    "Your access to {$contractor->company_name} has been suspended. Reason: {$validated['reason']}",
                    'high',
                    'contractor_staff',
                    (int) $id,
                    ['screen' => 'Dashboard']
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff member suspended successfully',
                'data'    => ['is_suspended' => true, 'suspension_reason' => $validated['reason']],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error suspending contractor staff: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // UNSUSPEND / REACTIVATE STAFF (owner only)
    // -----------------------------------------------------------------------

    public function unsuspend(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id parameter is required'], 400);
            }

            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                return response()->json(['success' => false, 'message' => $authError, 'error_code' => 'INSUFFICIENT_PERMISSIONS'], 403);
            }

            $contractor = $this->authService->getContractorForUser($userId);
            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
            }

            $staffRecord = DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->where('contractor_id', $contractor->contractor_id)
                ->whereNull('deletion_reason')
                ->first();

            if (!$staffRecord) {
                return response()->json(['success' => false, 'message' => 'Staff member not found'], 404);
            }

            if (!$staffRecord->is_suspended) {
                return response()->json(['success' => false, 'message' => 'Staff member is not suspended'], 400);
            }

            DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->update([
                    'is_suspended'     => 0,
                    'suspension_reason' => null,
                ]);

            // Notify the reactivated staff member
            $targetOwner = DB::table('property_owners')->where('owner_id', $staffRecord->owner_id)->first();
            if ($targetOwner) {
                NotificationService::create(
                    (int) $targetOwner->user_id,
                    'staff_reactivated',
                    'Account Reactivated',
                    "Your access to {$contractor->company_name} has been restored.",
                    'normal',
                    'contractor_staff',
                    (int) $id,
                    ['screen' => 'Dashboard']
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff member reactivated successfully',
                'data'    => ['is_suspended' => false],
            ]);

        } catch (\Exception $e) {
            Log::error('Error unsuspending contractor staff: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // ACCEPT INVITATION (called by the invited owner themselves)
    // -----------------------------------------------------------------------

    public function acceptInvitation(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id parameter is required'], 400);
            }

            $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
            if (!$ownerId) {
                return response()->json(['success' => false, 'message' => 'Property owner record not found'], 404);
            }

            $staffRecord = DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->where('owner_id', $ownerId)
                ->where('is_active', 0)
                ->whereNull('deletion_reason')
                ->first();

            if (!$staffRecord) {
                return response()->json(['success' => false, 'message' => 'Invitation not found or already processed'], 404);
            }

            DB::table('contractor_staff')->where('staff_id', $id)->update(['is_active' => 1]);

            // Notify the contractor owner
            $contractor = DB::table('contractors')->where('contractor_id', $staffRecord->contractor_id)->first();
            if ($contractor) {
                $contractorOwnerUser = DB::table('property_owners')
                    ->join('users', 'property_owners.user_id', '=', 'users.user_id')
                    ->where('property_owners.owner_id', $contractor->owner_id)
                    ->select('users.user_id', 'users.first_name', 'users.last_name')
                    ->first();

                if ($contractorOwnerUser) {
                    $invitedUser = DB::table('users')->where('user_id', $userId)->first();
                    $name = $invitedUser ? "{$invitedUser->first_name} {$invitedUser->last_name}" : 'A user';
                    NotificationService::create(
                        (int) $contractorOwnerUser->user_id,
                        'staff_invitation_accepted',
                        'Invitation Accepted',
                        "{$name} has accepted your invitation to join {$contractor->company_name}.",
                        'normal',
                        'contractor_staff',
                        (int) $id,
                        ['screen' => 'CompanyMembers']
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Invitation accepted. You are now an active member of the company.']);

        } catch (\Exception $e) {
            Log::error('Error accepting staff invitation: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // DECLINE INVITATION (called by the invited owner themselves)
    // -----------------------------------------------------------------------

    public function declineInvitation(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id parameter is required'], 400);
            }

            $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
            if (!$ownerId) {
                return response()->json(['success' => false, 'message' => 'Property owner record not found'], 404);
            }

            $staffRecord = DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->where('owner_id', $ownerId)
                ->where('is_active', 0)
                ->whereNull('deletion_reason')
                ->first();

            if (!$staffRecord) {
                return response()->json(['success' => false, 'message' => 'Invitation not found or already processed'], 404);
            }

            $validated = $request->validate([
                'reason' => 'required|string|min:3|max:500',
            ]);

            $declineReason = trim((string) ($validated['reason'] ?? 'Invitation declined by user'));

            DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->update(['deletion_reason' => $declineReason]);

            // Notify the contractor owner that the invitation was declined
            $contractor = DB::table('contractors')->where('contractor_id', $staffRecord->contractor_id)->first();
            if ($contractor) {
                $contractorOwnerUser = DB::table('property_owners')
                    ->where('owner_id', $contractor->owner_id)
                    ->value('user_id');

                if ($contractorOwnerUser) {
                    $invitedUser = DB::table('users')->where('user_id', $userId)->first();
                    $name = $invitedUser ? "{$invitedUser->first_name} {$invitedUser->last_name}" : 'The invited user';

                    NotificationService::create(
                        (int) $contractorOwnerUser,
                        'staff_invitation_cancelled',
                        'Invitation Declined',
                        "{$name} declined your staff invitation. Reason: {$declineReason}",
                        'normal',
                        'contractor_staff',
                        (int) $id,
                        ['screen' => 'CompanyMembers']
                    );
                }
            }

            return response()->json(['success' => true, 'message' => 'Invitation declined.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error declining staff invitation: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // CANCEL INVITATION (owner cancels a pending invitation — requires reason)
    // -----------------------------------------------------------------------

    public function cancelInvitation(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id parameter is required'], 400);
            }

            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                return response()->json(['success' => false, 'message' => $authError, 'error_code' => 'INSUFFICIENT_PERMISSIONS'], 403);
            }

            $contractor = $this->authService->getContractorForUser($userId);
            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor not found'], 404);
            }

            // Must be a pending invitation (is_active = 0) that hasn't already been cancelled
            $staffRecord = DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->where('contractor_id', $contractor->contractor_id)
                ->where('is_active', 0)
                ->whereNull('deletion_reason')
                ->first();

            if (!$staffRecord) {
                return response()->json(['success' => false, 'message' => 'Pending invitation not found'], 404);
            }

            $validated = $request->validate([
                'reason' => 'required|string|min:3|max:500',
            ]);

            DB::table('contractor_staff')
                ->where('staff_id', $id)
                ->update(['deletion_reason' => $validated['reason']]);

            // Notify the invited owner that the invitation was cancelled
            $targetOwner = DB::table('property_owners')->where('owner_id', $staffRecord->owner_id)->first();
            if ($targetOwner) {
                NotificationService::create(
                    (int) $targetOwner->user_id,
                    'staff_invitation_cancelled',
                    'Invitation Cancelled',
                    "Your invitation to join {$contractor->company_name} has been cancelled. Reason: {$validated['reason']}",
                    'normal',
                    'contractor_staff',
                    (int) $id,
                    ['screen' => 'Dashboard']
                );
            }

            return response()->json(['success' => true, 'message' => 'Invitation cancelled successfully']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error cancelling staff invitation: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------
    // CHANGE REPRESENTATIVE (owner only)
    // -----------------------------------------------------------------------

    public function changeRepresentative(Request $request)
    {
        try {
            $userId = $this->getUserId($request);
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'user_id parameter is required'], 400);
            }

            // Only the company owner can change the representative
            $authError = $this->authService->validateMemberManagementAccess($userId);
            if ($authError) {
                return response()->json(['success' => false, 'message' => $authError, 'error_code' => 'INSUFFICIENT_PERMISSIONS'], 403);
            }

            // STEP 1: Validate contractor exists
            $contractor = $this->authService->getContractorForUser($userId);
            if (!$contractor) {
                return response()->json(['success' => false, 'message' => 'Contractor not found.'], 404);
            }

            $validated = $request->validate([
                'staff_id' => 'required|integer',
            ]);

            $newRepStaffId = (int) $validated['staff_id'];

            DB::beginTransaction();
            try {
                // STEP 2: Find current active representative
                $currentRepresentative = DB::table('contractor_staff')
                    ->where('contractor_id', $contractor->contractor_id)
                    ->where('company_role', 'representative')
                    ->where('is_active', 1)
                    ->whereNull('deletion_reason')
                    ->first();

                // STEP 3: Validate new representative — must be a staff member, not suspended
                $newRepStaff = DB::table('contractor_staff')
                    ->where('staff_id', $newRepStaffId)
                    ->where('contractor_id', $contractor->contractor_id)
                    ->where('is_suspended', 0)
                    ->whereNull('deletion_reason')
                    ->first();

                if (!$newRepStaff) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Selected team member not found.'], 404);
                }

                if ($currentRepresentative && (int) $newRepStaff->staff_id === (int) $currentRepresentative->staff_id) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'This member is already the active representative.'], 422);
                }

                // STEP 4: Demote current representative (restore to previous role)
                $demotedRole = null;
                if ($currentRepresentative) {
                    $demotedRole = (!empty($currentRepresentative->company_role_before) && $currentRepresentative->company_role_before !== 'representative')
                        ? $currentRepresentative->company_role_before
                        : 'manager';

                    DB::table('contractor_staff')
                        ->where('staff_id', $currentRepresentative->staff_id)
                        ->update([
                            'company_role'        => $demotedRole,
                            'company_role_before' => 'representative',
                            'role_if_others'      => null,
                        ]);
                }

                // STEP 5: Promote new representative — is_active = 0 until they accept
                $savedCurrentRole = (!empty($newRepStaff->company_role) && $newRepStaff->company_role !== 'representative')
                    ? $newRepStaff->company_role
                    : 'manager';

                DB::table('contractor_staff')
                    ->where('staff_id', $newRepStaffId)
                    ->update([
                        'company_role'        => 'representative',
                        'company_role_before' => $savedCurrentRole,
                        'is_active'           => 0,
                        'role_if_others'      => null,
                    ]);

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            // Notify new representative — they must accept to become active
            $newRepOwner = DB::table('property_owners')->where('owner_id', $newRepStaff->owner_id)->first();
            if ($newRepOwner) {
                NotificationService::create(
                    (int) $newRepOwner->user_id,
                    'representative_assigned',
                    'Representative Role Assigned',
                    "You have been assigned as representative of {$contractor->company_name}. Please accept to activate your new role.",
                    'high',
                    'contractor_staff',
                    $newRepStaffId,
                    ['screen' => 'StaffInvitations', 'staff_id' => $newRepStaffId]
                );
            }

            // Notify demoted representative of their role change
            if ($currentRepresentative && $demotedRole) {
                $oldRepOwner = DB::table('property_owners')->where('owner_id', $currentRepresentative->owner_id)->first();
                if ($oldRepOwner) {
                    NotificationService::create(
                        (int) $oldRepOwner->user_id,
                        'representative_demoted',
                        'Representative Role Changed',
                        "Your representative role at {$contractor->company_name} has been reassigned. You have been restored to {$demotedRole}.",
                        'normal',
                        'contractor_staff',
                        (int) $currentRepresentative->staff_id,
                        ['screen' => 'Dashboard']
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Representative changed. The new representative must accept to activate their role.',
                'data'    => [
                    'new_representative_staff_id' => $newRepStaffId,
                    'demoted_staff_id'            => $currentRepresentative ? (int) $currentRepresentative->staff_id : null,
                    'demoted_to_role'             => $demotedRole,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error changing contractor representative: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @deprecated Kept for route backward-compatibility — maps to suspend().
     */
    public function toggleActive(Request $request, $id)
    {
        return $this->suspend($request, $id);
    }
}
