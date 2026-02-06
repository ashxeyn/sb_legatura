/**
 * useContractorAuth Hook
 * 
 * React hook for contractor member authorization.
 * Provides role-based access control for contractor features.
 * 
 * ROLE PERMISSIONS (based on contractor_users.role):
 * - owner: Full access (bid, milestones, manage members, everything)
 * - representative: Full contractor access (bid, milestones, manage members)
 * - manager, engineer, architect, others: Limited access
 *   - CAN: View projects, upload progress, approve payment validations, view property owners
 *   - CANNOT: Bid, create/edit/add milestones
 * 
 * Usage:
 *   const { canBid, canManageMilestones, role, isActive } = useContractorAuth();
 *   
 *   // Conditionally render UI
 *   {canBid && <PlaceBidButton />}
 */

import { useState, useEffect, useCallback } from 'react';
import {
  contractor_authorization_service,
  ContractorMemberContext,
  ContractorRole,
  MEMBER_MANAGEMENT_ROLES,
} from '../services/contractor_authorization_service';

// Roles that can perform full contractor operations (bid, milestones)
export const FULL_ACCESS_ROLES: ContractorRole[] = ['owner', 'representative'];

// Roles that have view-only/limited access
export const LIMITED_ACCESS_ROLES: ContractorRole[] = ['manager', 'engineer', 'architect', 'others'];

export interface UseContractorAuthResult {
  // Loading state while fetching context
  isLoading: boolean;
  
  // Whether the user is an active contractor member
  isActive: boolean;
  
  // User's contractor role (from contractor_users.role)
  role: ContractorRole | null;
  
  // Full member context
  memberContext: ContractorMemberContext | null;
  
  // Permission checks - Member Management
  canManageMembers: boolean;
  canViewMembers: boolean;
  
  // Permission checks - Bidding & Milestones (owner/representative only)
  canBid: boolean;
  canManageMilestones: boolean; // create, edit, add milestones
  
  // Permission checks - All roles can do these
  canUploadProgress: boolean;
  canApprovePayments: boolean;
  canViewPropertyOwners: boolean;
  
  // Convenience checks
  isOwner: boolean;
  isRepresentative: boolean;
  hasFullAccess: boolean; // owner or representative
  
  // Contractor info
  contractorId: number | null;
  contractorName: string | null;
  
  // Methods
  refreshContext: () => Promise<void>;
  hasRole: (role: ContractorRole) => boolean;
  hasAnyRole: (roles: ContractorRole[]) => boolean;
}

export function useContractorAuth(): UseContractorAuthResult {
  const [isLoading, setIsLoading] = useState(true);
  const [memberContext, setMemberContext] = useState<ContractorMemberContext | null>(null);

  const loadContext = useCallback(async () => {
    setIsLoading(true);
    try {
      const context = await contractor_authorization_service.getMemberContext();
      setMemberContext(context);
    } catch (error) {
      console.error('Error loading contractor auth context:', error);
      setMemberContext(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    loadContext();
  }, [loadContext]);

  // Derived values
  const isActive = memberContext?.is_active ?? false;
  const role = memberContext?.role ?? null;
  
  // Check if user has full access (owner or representative)
  const hasFullAccess = isActive && role !== null && FULL_ACCESS_ROLES.includes(role);
  
  // Member management - owner/representative only
  const canManageMembers = memberContext?.permissions?.can_manage_members ?? 
    (isActive && role !== null && MEMBER_MANAGEMENT_ROLES.includes(role));
  
  const canViewMembers = memberContext?.permissions?.can_view_members ?? isActive;
  
  // Bidding & Milestones - owner/representative only
  const canBid = hasFullAccess;
  const canManageMilestones = hasFullAccess; // create, edit, add milestones
  
  // All active contractor members can do these
  const canUploadProgress = isActive;
  const canApprovePayments = isActive;
  const canViewPropertyOwners = isActive;
  
  const isOwner = role === 'owner';
  const isRepresentative = role === 'representative';
  
  const contractorId = memberContext?.contractor_id ?? null;
  const contractorName = memberContext?.contractor_name ?? null;

  // Helper methods
  const hasRole = useCallback((checkRole: ContractorRole): boolean => {
    return role === checkRole;
  }, [role]);

  const hasAnyRole = useCallback((roles: ContractorRole[]): boolean => {
    return role !== null && roles.includes(role);
  }, [role]);

  return {
    isLoading,
    isActive,
    role,
    memberContext,
    canManageMembers,
    canViewMembers,
    canBid,
    canManageMilestones,
    canUploadProgress,
    canApprovePayments,
    canViewPropertyOwners,
    isOwner,
    isRepresentative,
    hasFullAccess,
    contractorId,
    contractorName,
    refreshContext: loadContext,
    hasRole,
    hasAnyRole,
  };
}

export default useContractorAuth;
