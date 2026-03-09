<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to authorize contractor member access based on role.
 * 
 * Role Definitions:
 * - owner: contractor (primary account) - full privileges
 * - representative: can manage members
 * - manager, engineer, architect, others: restricted from member management
 * 
 * This middleware enforces:
 * 1. Active contractor member validation (is_active = true)
 * 2. Role-based access to member management endpoints
 */
class ContractorMemberAuthorization
{
    /**
     * Roles that are allowed to manage contractor members (add/remove/update).
     */
    protected const MEMBER_MANAGEMENT_ROLES = ['owner', 'representative'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  The required permission: 'view', 'manage'
     */
    public function handle(Request $request, Closure $next, string $permission = 'view'): Response
    {
        // Get user_id from query param, header, or authenticated user
        $userId = $this->getUserId($request);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'AUTH_REQUIRED'
            ], 401);
        }

        // Get the contractor member record for this user
        $memberContext = $this->getContractorMemberContext($userId);

        if (!$memberContext) {
            return response()->json([
                'success' => false,
                'message' => 'Contractor member record not found',
                'error_code' => 'MEMBER_NOT_FOUND'
            ], 403);
        }

        // Check if member is active
        if (!$memberContext->is_active) {
            Log::warning('Inactive contractor member attempted access', [
                'user_id' => $userId,
                'contractor_user_id' => $memberContext->contractor_user_id,
                'role' => $memberContext->role
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Your contractor member account is inactive. Please contact the contractor owner.',
                'error_code' => 'MEMBER_INACTIVE'
            ], 403);
        }

        // For 'manage' permission, check if role is authorized
        if ($permission === 'manage') {
            if (!in_array($memberContext->role, self::MEMBER_MANAGEMENT_ROLES)) {
                Log::warning('Unauthorized member management attempt', [
                    'user_id' => $userId,
                    'role' => $memberContext->role,
                    'required_roles' => self::MEMBER_MANAGEMENT_ROLES
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to manage contractor members. Only owners and representatives can perform this action.',
                    'error_code' => 'INSUFFICIENT_PERMISSIONS'
                ], 403);
            }
        }

        // Attach member context to request for use in controllers
        $request->attributes->set('contractor_member', $memberContext);
        $request->attributes->set('contractor_member_role', $memberContext->role);
        $request->attributes->set('can_manage_members', in_array($memberContext->role, self::MEMBER_MANAGEMENT_ROLES));

        return $next($request);
    }

    /**
     * Get user_id from request (query param, header, or authenticated user).
     */
    protected function getUserId(Request $request): ?int
    {
        // Try query parameter first
        $userId = $request->query('user_id');
        
        // Then try header
        if (!$userId) {
            $userId = $request->header('X-User-Id');
        }
        
        // Finally try authenticated user
        if (!$userId && $request->user()) {
            $userId = $request->user()->user_id ?? $request->user()->id;
        }

        return $userId ? (int) $userId : null;
    }

    /**
     * Get the contractor member context for a user.
     * Returns the contractor_users record with contractor info.
     */
    protected function getContractorMemberContext(int $userId): ?object
    {
        // First, try to find the user as a contractor owner (main account)
        $contractor = DB::table('contractors')
            ->where('user_id', $userId)
            ->first();

        if ($contractor) {
            // User is the primary contractor account owner
            // Get their contractor_users record
            $memberRecord = DB::table('contractor_users')
                ->where('contractor_id', $contractor->contractor_id)
                ->where('user_id', $userId)
                ->where('is_deleted', 0)
                ->first();

            if ($memberRecord) {
                $memberRecord->contractor_name = $contractor->company_name ?? null;
                return $memberRecord;
            }

            // If no member record exists but they own the contractor, create a virtual owner context
            return (object) [
                'contractor_user_id' => 0,
                'contractor_id' => $contractor->contractor_id,
                'user_id' => $userId,
                'role' => 'owner',
                'is_active' => 1,
                'is_deleted' => 0,
                'contractor_name' => $contractor->company_name ?? null,
            ];
        }

        // User might be a team member (not the primary owner)
        $memberRecord = DB::table('contractor_users')
            ->join('contractors', 'contractor_users.contractor_id', '=', 'contractors.contractor_id')
            ->where('contractor_users.user_id', $userId)
            ->where('contractor_users.is_deleted', 0)
            ->select(
                'contractor_users.*',
                'contractors.company_name as contractor_name'
            )
            ->first();

        return $memberRecord;
    }
}
