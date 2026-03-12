<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for contractor member authorization.
 *
 * 3-TIER ROLE SYSTEM:
 *
 * TIER 1 â€” OWNER (defined by contractors.owner_id, virtual role 'owner'):
 *   - Add / remove company members
 *   - Switch or remove the representative
 *   - ALL other contractor functions
 *
 * TIER 2 â€” REPRESENTATIVE (contractor_staff.company_role = 'representative'):
 *   - CANNOT add/remove members
 *   - CANNOT switch/remove representative
 *   - CAN do all other functions (bid, milestones, financials, payments, company profile, etc.)
 *
 * TIER 3 â€” STAFF (manager / engineer / architect / others):
 *   - CANNOT: bid, view financials/bid history, manage milestones, manage company profile, approve payments
 *   - CAN: upload progress reports, track project progress, browse
 *
 * SUSPENSION: The company owner can suspend any staff member with a reason.
 *   A suspended member (is_suspended = 1) cannot switch to the contractor view.
 *   Suspension does NOT affect their property-owner account.
 */
class ContractorAuthorizationService
{
    /** Tier 1 â€” full control including member/representative management. */
    public const OWNER_ROLE = 'owner';

    /** Tier 1 + 2 â€” bidding, milestones, financials, company profile, payments. */
    public const FULL_ACCESS_ROLES = ['owner', 'representative'];

    /** Tier 3 â€” upload progress & browse only. */
    public const LIMITED_ACCESS_ROLES = ['manager', 'engineer', 'architect', 'others'];

    /** All valid contractor_staff.company_role enum values (owner is a virtual role). */
    public const STAFF_ROLES = ['representative', 'manager', 'engineer', 'architect', 'others'];

    // -------------------------------------------------------------------------
    // INTERNAL HELPERS
    // -------------------------------------------------------------------------

    /**
     * Resolve property_owners.owner_id for a given users.user_id.
     */
    private function getOwnerId(int $userId): ?int
    {
        $ownerId = DB::table('property_owners')
            ->where('user_id', $userId)
            ->value('owner_id');

        return $ownerId ? (int) $ownerId : null;
    }

    // -------------------------------------------------------------------------
    // CORE CONTEXT METHODS
    // -------------------------------------------------------------------------

    /**
     * Get the contractor record for a user (either the company owner or active staff).
     * Only active, non-suspended staff are considered for contractor-view access.
     */
    public function getContractorForUser(int $userId): ?object
    {
        $ownerId = $this->getOwnerId($userId);
        if (!$ownerId) return null;

        // Company owner check
        $contractor = DB::table('contractors')
            ->where('owner_id', $ownerId)
            ->first();

        if ($contractor) return $contractor;

        // Active, non-suspended staff member
        $staffRecord = DB::table('contractor_staff')
            ->where('owner_id', $ownerId)
            ->where('is_active', 1)
            ->where('is_suspended', 0)
            ->whereNull('deletion_reason')
            ->first();

        if ($staffRecord) {
            return DB::table('contractors')
                ->where('contractor_id', $staffRecord->contractor_id)
                ->first();
        }

        return null;
    }

    /**
     * Get the member context for a user.
     *
     * Returns an object with:
     *   company_role        â€” actual role ('owner' for company owners, enum value for staff)
     *   role                â€” backward-compat alias of company_role
     *   is_active           â€” 1 = accepted/active, 0 = pending invitation
     *   is_suspended        â€” 1 = suspended by owner (blocks contractor-view access)
     *   suspension_reason   â€” reason given by owner on suspension
     *   contractor_id       â€” contractor this person belongs to
     *   is_contractor_owner â€” true if they own the contractor (via contractors.owner_id)
     */
    public function getMemberContext(int $userId): ?object
    {
        $ownerId = $this->getOwnerId($userId);
        if (!$ownerId) return null;

        // Is this owner the company owner?
        $contractor = DB::table('contractors')
            ->where('owner_id', $ownerId)
            ->first();

        if ($contractor) {
            return (object) [
                'staff_id'            => null,
                'contractor_id'       => $contractor->contractor_id,
                'owner_id'            => $ownerId,
                'user_id'             => $userId,
                'company_role'        => 'owner',
                'role'                => 'owner', // backward-compat alias
                'role_if_others'      => null,
                'is_active'           => 1,
                'is_suspended'        => 0,
                'suspension_reason'   => null,
                'deletion_reason'     => null,
                'contractor_name'     => $contractor->company_name ?? null,
                'is_contractor_owner' => true,
            ];
        }

        // Is this owner a listed staff member?
        $staffRecord = DB::table('contractor_staff')
            ->join('contractors', 'contractor_staff.contractor_id', '=', 'contractors.contractor_id')
            ->where('contractor_staff.owner_id', $ownerId)
            ->whereNull('contractor_staff.deletion_reason')
            ->select(
                'contractor_staff.*',
                'contractors.company_name as contractor_name',
                'contractors.owner_id as contractor_owner_id'
            )
            ->first();

        if ($staffRecord) {
            $staffRecord->role               = $staffRecord->company_role; // backward-compat alias
            $staffRecord->is_contractor_owner = false;
            $staffRecord->user_id            = $userId;
        }

        return $staffRecord ?: null;
    }

    // -------------------------------------------------------------------------
    // STATUS CHECKS
    // -------------------------------------------------------------------------

    /** True if user is an active, non-suspended contractor member. */
    public function isActiveMember(int $userId): bool
    {
        $ctx = $this->getMemberContext($userId);
        return $ctx && (bool) $ctx->is_active && !(bool) ($ctx->is_suspended ?? 0);
    }

    /** True if user is currently suspended from their contractor role. */
    public function isSuspended(int $userId): bool
    {
        $ctx = $this->getMemberContext($userId);
        return $ctx && (bool) ($ctx->is_suspended ?? 0);
    }

    /** Get user's role within their contractor organisation. */
    public function getUserRole(int $userId): ?string
    {
        $ctx = $this->getMemberContext($userId);
        return $ctx ? $ctx->company_role : null;
    }

    // -------------------------------------------------------------------------
    // PERMISSION CHECKS
    // -------------------------------------------------------------------------

    /** True if user can add/remove members. OWNER ONLY (Tier 1). */
    public function canManageMembers(int $userId): bool
    {
        $ctx = $this->getMemberContext($userId);
        return $ctx
            && (bool) $ctx->is_active
            && !(bool) ($ctx->is_suspended ?? 0)
            && $ctx->company_role === self::OWNER_ROLE;
    }

    /** True if user can switch/remove the representative. OWNER ONLY (Tier 1). */
    public function canManageRepresentative(int $userId): bool
    {
        return $this->canManageMembers($userId);
    }

    /** True if user can bid. Tier 1 + 2. */
    public function canBid(int $userId): bool
    {
        $ctx = $this->getMemberContext($userId);
        return $ctx
            && (bool) $ctx->is_active
            && !(bool) ($ctx->is_suspended ?? 0)
            && in_array($ctx->company_role, self::FULL_ACCESS_ROLES);
    }

    /** True if user can manage milestones (create/edit/add). Tier 1 + 2. */
    public function canManageMilestones(int $userId): bool    { return $this->canBid($userId); }

    /** True if user can view financials / bid history. Tier 1 + 2. */
    public function canViewFinancials(int $userId): bool     { return $this->canBid($userId); }

    /** True if user can manage the company profile. Tier 1 + 2. */
    public function canManageCompanyProfile(int $userId): bool { return $this->canBid($userId); }

    /** True if user can approve payments. Tier 1 + 2. */
    public function canApprovePayments(int $userId): bool    { return $this->canBid($userId); }

    // -------------------------------------------------------------------------
    // VALIDATION HELPERS (return error message string, or null if OK)
    // -------------------------------------------------------------------------

    private function baseContextCheck(int $userId): array
    {
        $ctx = $this->getMemberContext($userId);
        if (!$ctx)                       return ['Contractor member record not found', null];
        if (!$ctx->is_active)            return ['Your contractor member account is inactive', $ctx];
        if ($ctx->is_suspended ?? 0)     return ['Your contractor account is currently suspended', $ctx];
        return [null, $ctx];
    }

    /** Validate add/remove member access. OWNER ONLY. */
    public function validateMemberManagementAccess(int $userId): ?string
    {
        [$error, $ctx] = $this->baseContextCheck($userId);
        if ($error) return $error;

        if ($ctx->company_role !== self::OWNER_ROLE) {
            return 'Only the company owner can manage members.';
        }

        return null;
    }

    /** Validate switch/remove representative access. OWNER ONLY. */
    public function validateRepresentativeManagementAccess(int $userId): ?string
    {
        return $this->validateMemberManagementAccess($userId);
    }

    /** Validate bidding access. Owner + Representative. */
    public function validateBiddingAccess(int $userId): ?string
    {
        [$error, $ctx] = $this->baseContextCheck($userId);
        if ($error) return $error;

        if (!in_array($ctx->company_role, self::FULL_ACCESS_ROLES)) {
            return 'Only the owner or representative can place bids.';
        }

        return null;
    }

    /** Validate milestone management access. Owner + Representative. */
    public function validateMilestoneAccess(int $userId): ?string
    {
        [$error, $ctx] = $this->baseContextCheck($userId);
        if ($error) return $error;

        if (!in_array($ctx->company_role, self::FULL_ACCESS_ROLES)) {
            return 'Only the owner or representative can manage milestones.';
        }

        return null;
    }

    /** Validate access to financial/bid history views. Owner + Representative. */
    public function validateFinancialAccess(int $userId): ?string
    {
        [$error, $ctx] = $this->baseContextCheck($userId);
        if ($error) return $error;

        if (!in_array($ctx->company_role, self::FULL_ACCESS_ROLES)) {
            return 'Only the owner or representative can view financial and bid history.';
        }

        return null;
    }

    /** Validate company profile management access. Owner + Representative. */
    public function validateCompanyProfileAccess(int $userId): ?string
    {
        [$error, $ctx] = $this->baseContextCheck($userId);
        if ($error) return $error;

        if (!in_array($ctx->company_role, self::FULL_ACCESS_ROLES)) {
            return 'Only the owner or representative can manage company profile data.';
        }

        return null;
    }

    /** Validate payment approval/rejection access. Owner + Representative. */
    public function validatePaymentApprovalAccess(int $userId): ?string
    {
        [$error, $ctx] = $this->baseContextCheck($userId);
        if ($error) return $error;

        if (!in_array($ctx->company_role, self::FULL_ACCESS_ROLES)) {
            return 'Only the owner or representative can approve or reject payments.';
        }

        return null;
    }

    /** Validate progress upload access. Any active, non-suspended contractor member. */
    public function validateProgressUploadAccess(int $userId): ?string
    {
        [$error, $ctx] = $this->baseContextCheck($userId);
        if ($error) return $error;

        if (!($ctx->is_active ?? false)) {
            return 'Your contractor member account is inactive.';
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // API RESPONSE HELPERS
    // -------------------------------------------------------------------------

    /** Full authorization context for API response (e.g. login payload). */
    public function getAuthorizationContext(int $userId): ?array
    {
        $ctx = $this->getMemberContext($userId);
        if (!$ctx) return null;

        $isActive    = (bool) $ctx->is_active;
        $isSuspended = (bool) ($ctx->is_suspended ?? 0);
        $role        = $ctx->company_role;
        $canAccess   = $isActive && !$isSuspended;
        $isOwner     = $role === self::OWNER_ROLE;
        $hasFullAccess = $canAccess && in_array($role, self::FULL_ACCESS_ROLES);

        return [
            'staff_id'            => $ctx->staff_id ?? null,
            'contractor_id'       => $ctx->contractor_id,
            'contractor_name'     => $ctx->contractor_name ?? null,
            'role'                => $role,
            'is_active'           => $isActive,
            'is_suspended'        => $isSuspended,
            'suspension_reason'   => $ctx->suspension_reason ?? null,
            'is_contractor_owner' => $ctx->is_contractor_owner ?? false,
            'has_full_access'     => $hasFullAccess,
            'permissions' => [
                // Tier 1 â€” Owner only
                'can_manage_members'        => $canAccess && $isOwner,
                'can_manage_representative' => $canAccess && $isOwner,

                // Tier 1 + 2 â€” Owner + Representative
                'can_bid'                    => $hasFullAccess,
                'can_manage_milestones'      => $hasFullAccess,
                'can_view_financials'        => $hasFullAccess,
                'can_manage_company_profile' => $hasFullAccess,
                'can_approve_payments'       => $hasFullAccess,

                // Tier 3 â€” All active non-suspended members
                'can_upload_progress' => $canAccess,
                'can_view_members'    => $canAccess,
                'can_view_projects'   => $canAccess,
            ],
        ];
    }

    /**
     * Get all staff members for a contractor (for listing).
     * Joins contractor_staff with property_owners and users for identity data.
     */
    public function getContractorMembers(int $contractorId): array
    {
        return DB::table('contractor_staff')
            ->join('property_owners', 'contractor_staff.owner_id', '=', 'property_owners.owner_id')
            ->join('users', 'property_owners.user_id', '=', 'users.user_id')
            ->where('contractor_staff.contractor_id', $contractorId)
            ->whereNull('contractor_staff.deletion_reason')
            ->select(
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
                'contractor_staff.role_if_others',
                'contractor_staff.is_active',
                'contractor_staff.is_suspended',
                'contractor_staff.suspension_reason',
                'contractor_staff.created_at'
            )
            ->orderByRaw("FIELD(contractor_staff.company_role, 'representative', 'manager', 'engineer', 'architect', 'others')")
            ->get()
            ->toArray();
    }
}
