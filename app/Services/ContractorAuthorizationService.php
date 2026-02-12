<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for contractor member authorization.
 *
 * ROLE PERMISSIONS (based on contractor_users.role):
 *
 * FULL ACCESS ROLES (can do everything):
 * - owner: contractor primary account - full privileges
 * - representative: can manage members, bid, milestones
 *
 * LIMITED ACCESS ROLES:
 * - manager, engineer, architect, others:
 *   - CAN: View projects, upload progress, approve payment validations, view property owners
 *   - CANNOT: Bid, create/edit/add milestones, manage members
 */
class ContractorAuthorizationService
{
    /**
     * Roles that have full contractor access (bid, milestones, manage members).
     */
    public const FULL_ACCESS_ROLES = ['owner', 'representative'];

    /**
     * Roles that are allowed to manage contractor members (same as FULL_ACCESS_ROLES).
     */
    public const MEMBER_MANAGEMENT_ROLES = ['owner', 'representative'];

    /**
     * Roles with limited access (view, upload progress, approve payments only).
     */
    public const LIMITED_ACCESS_ROLES = ['manager', 'engineer', 'architect', 'others'];

    /**
     * All valid contractor member roles.
     */
    public const ALL_ROLES = ['owner', 'representative', 'manager', 'engineer', 'architect', 'others'];

    /**
     * Get the contractor record for a user (either direct owner or via staff membership).
     * This is useful for API endpoints that need to get the contractor_id for data lookups.
     *
     * @param int $userId The user ID
     * @return object|null The contractor record or null if not found
     */
    public function getContractorForUser(int $userId): ?object
    {
        // First check if user is a direct contractor owner
        $contractor = DB::table('contractors')
            ->where('user_id', $userId)
            ->first();

        if ($contractor) {
            return $contractor;
        }

        // Check if user is a staff member
        $contractorUser = DB::table('contractor_users')
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->first();

        if ($contractorUser) {
            // Get the contractor this staff member belongs to
            return DB::table('contractors')
                ->where('contractor_id', $contractorUser->contractor_id)
                ->first();
        }

        return null;
    }

    /**
     * Get the contractor member context for a user.
     * Returns member record with role, is_active, contractor info.
     */
    public function getMemberContext(int $userId): ?object
    {
        // First, check if user is a contractor owner (main account)
        $contractor = DB::table('contractors')
            ->where('user_id', $userId)
            ->first();

        if ($contractor) {
            // User is the primary contractor account owner
            $memberRecord = DB::table('contractor_users')
                ->where('contractor_id', $contractor->contractor_id)
                ->where('user_id', $userId)
                ->where('is_deleted', 0)
                ->first();

            if ($memberRecord) {
                // If the parent contractor is approved, ensure owner is treated as active
                if (isset($contractor->verification_status) && $contractor->verification_status === 'approved' && ($memberRecord->role ?? null) === 'owner') {
                    $memberRecord->is_active = 1;
                }
                $memberRecord->contractor_name = $contractor->company_name ?? null;
                $memberRecord->is_contractor_owner = true;
                return $memberRecord;
            }

            // Virtual owner context if no member record exists
            return (object) [
                'contractor_user_id' => 0,
                'contractor_id' => $contractor->contractor_id,
                'user_id' => $userId,
                'role' => 'owner',
                'is_active' => 1,
                'is_deleted' => 0,
                'contractor_name' => $contractor->company_name ?? null,
                'is_contractor_owner' => true,
            ];
        }

        // Check if user is a team member
        $memberRecord = DB::table('contractor_users')
            ->join('contractors', 'contractor_users.contractor_id', '=', 'contractors.contractor_id')
            ->where('contractor_users.user_id', $userId)
            ->where('contractor_users.is_deleted', 0)
            ->select(
                'contractor_users.*',
                'contractors.company_name as contractor_name',
                'contractors.user_id as contractor_owner_user_id'
            )
            ->first();

        if ($memberRecord) {
            $memberRecord->is_contractor_owner = false;
        }

        return $memberRecord;
    }

    /**
     * Check if a user is an active contractor member.
     */
    public function isActiveMember(int $userId): bool
    {
        $context = $this->getMemberContext($userId);
        return $context && $context->is_active;
    }

    /**
     * Check if a user can manage contractor members (add/remove/edit).
     */
    public function canManageMembers(int $userId): bool
    {
        $context = $this->getMemberContext($userId);

        if (!$context || !$context->is_active) {
            return false;
        }

        return in_array($context->role, self::MEMBER_MANAGEMENT_ROLES);
    }

    /**
     * Get the user's role within their contractor organization.
     */
    public function getUserRole(int $userId): ?string
    {
        $context = $this->getMemberContext($userId);
        return $context ? $context->role : null;
    }

    /**
     * Get contractor member context formatted for API response.
     * Used to extend login response with member authorization info.
     */
    public function getAuthorizationContext(int $userId): ?array
    {
        $context = $this->getMemberContext($userId);

        if (!$context) {
            return null;
        }

        $isActive = (bool) $context->is_active;
        $hasFullAccess = $isActive && in_array($context->role, self::FULL_ACCESS_ROLES);

        return [
            'contractor_member_id' => $context->contractor_user_id ?? null,
            'contractor_id' => $context->contractor_id,
            'contractor_name' => $context->contractor_name ?? null,
            'role' => $context->role,
            'is_active' => $isActive,
            'is_contractor_owner' => $context->is_contractor_owner ?? false,
            'has_full_access' => $hasFullAccess,
            'permissions' => [
                // Member management - owner/representative only
                'can_manage_members' => $hasFullAccess,
                'can_view_members' => $isActive,

                // Bidding & Milestones - owner/representative only
                'can_bid' => $hasFullAccess,
                'can_manage_milestones' => $hasFullAccess, // create, edit, add milestones

                // All active members can do these
                'can_upload_progress' => $isActive,
                'can_approve_payments' => $isActive,
                'can_view_property_owners' => $isActive,
            ],
        ];
    }

    /**
     * Validate that requesting user can perform member management action.
     * Returns error message if not authorized, null if authorized.
     */
    public function validateMemberManagementAccess(int $userId): ?string
    {
        $context = $this->getMemberContext($userId);

        if (!$context) {
            return 'Contractor member record not found';
        }

        if (!$context->is_active) {
            return 'Your contractor member account is inactive';
        }

        if (!in_array($context->role, self::MEMBER_MANAGEMENT_ROLES)) {
            return 'You do not have permission to manage contractor members. Only owners and representatives can perform this action.';
        }

        return null; // Authorized
    }

    /**
     * Check if user can bid on projects.
     * Only owner and representative can bid.
     */
    public function canBid(int $userId): bool
    {
        $context = $this->getMemberContext($userId);
        return $context && $context->is_active && in_array($context->role, self::FULL_ACCESS_ROLES);
    }

    /**
     * Check if user can manage milestones (create, edit, add).
     * Only owner and representative can manage milestones.
     */
    public function canManageMilestones(int $userId): bool
    {
        $context = $this->getMemberContext($userId);
        return $context && $context->is_active && in_array($context->role, self::FULL_ACCESS_ROLES);
    }

    /**
     * Validate that requesting user can place bids.
     * Returns error message if not authorized, null if authorized.
     */
    public function validateBiddingAccess(int $userId): ?string
    {
        $context = $this->getMemberContext($userId);

        if (!$context) {
            return 'Contractor member record not found';
        }

        if (!$context->is_active) {
            return 'Your contractor member account is inactive';
        }

        if (!in_array($context->role, self::FULL_ACCESS_ROLES)) {
            return 'You do not have permission to place bids. Only owners and representatives can bid on projects.';
        }

        return null; // Authorized
    }

    /**
     * Validate that requesting user can manage milestones.
     * Returns error message if not authorized, null if authorized.
     */
    public function validateMilestoneAccess(int $userId): ?string
    {
        $context = $this->getMemberContext($userId);

        if (!$context) {
            return 'Contractor member record not found';
        }

        if (!$context->is_active) {
            return 'Your contractor member account is inactive';
        }

        if (!in_array($context->role, self::FULL_ACCESS_ROLES)) {
            return 'You do not have permission to manage milestones. Only owners and representatives can create, edit, or add milestones.';
        }

        return null; // Authorized
    }

    /**
     * Get all members for a contractor (used for listing).
     */
    public function getContractorMembers(int $contractorId): array
    {
        return DB::table('contractor_users')
            ->join('users', 'contractor_users.user_id', '=', 'users.user_id')
            ->where('contractor_users.contractor_id', $contractorId)
            ->where('contractor_users.is_deleted', 0)
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
                'contractor_users.created_at',
                'users.email',
                'users.username',
                'users.profile_pic',
                'users.updated_at'
            )
            ->orderByRaw("FIELD(contractor_users.role, 'owner', 'representative', 'manager', 'engineer', 'architect', 'others')")
            ->get()
            ->toArray();
    }
}
