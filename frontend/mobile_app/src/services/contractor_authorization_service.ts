/**
 * Contractor Authorization Service
 * 
 * Handles contractor member authorization, role checks, and permissions.
 * 
 * ROLE PERMISSIONS (based on contractor_users.role):
 * 
 * FULL ACCESS ROLES (can do most contractor operations):
 * - owner: contractor primary account - full privileges
 * - representative: can bid, milestones, and core operations
 * 
 * LIMITED ACCESS ROLES:
 * - manager, engineer, architect, others:
 *   - CAN: View projects, upload progress, approve payment validations, view property owners
 *   - CANNOT: Bid, create/edit/add milestones, manage members
 */

import { storage_service } from '../utils/storage';
import { role_service } from './role_service';

// Contractor member roles
export type ContractorRole = 'owner' | 'representative' | 'manager' | 'engineer' | 'architect' | 'others';

// Roles with full access for core contractor operations (bid, milestones)
export const FULL_ACCESS_ROLES: ContractorRole[] = ['owner', 'representative'];

// Roles allowed to manage members (add/remove/edit) — OWNER ONLY per the tier system
export const MEMBER_MANAGEMENT_ROLES: ContractorRole[] = ['owner'];

// Roles with limited access (view, upload progress, approve payments only)
export const LIMITED_ACCESS_ROLES: ContractorRole[] = ['manager', 'engineer', 'architect', 'others'];

// All valid contractor roles
export const ALL_CONTRACTOR_ROLES: ContractorRole[] = ['owner', 'representative', 'manager', 'engineer', 'architect', 'others'];

// Contractor member context from login response
export interface ContractorMemberContext {
  contractor_member_id: number | null;
  contractor_id: number;
  contractor_name: string | null;
  role: ContractorRole;
  is_active: boolean;
  is_contractor_owner: boolean;
  has_full_access: boolean;
  permissions: {
    // Member management - owner/representative only
    can_manage_members: boolean;
    can_view_members: boolean;
    
    // Bidding & Milestones - owner/representative only
    can_bid: boolean;
    can_manage_milestones: boolean;
    can_view_financials: boolean;
    can_manage_company_profile: boolean;
    
    // All active members can do these
    can_upload_progress: boolean;
    can_approve_payments: boolean;
    can_view_property_owners: boolean;
  };
}

export class contractor_authorization_service {
  private static resolveIsActive(value: any): boolean {
    if (typeof value === 'boolean') return value;
    if (typeof value === 'number') return value === 1;
    if (typeof value === 'string') {
      const normalized = value.trim().toLowerCase();
      return normalized === '1' || normalized === 'true' || normalized === 'active' || normalized === 'yes';
    }
    return false;
  }

  private static resolveRole(value: any): ContractorRole {
    const normalized = String(value || 'others').trim().toLowerCase();
    if ((ALL_CONTRACTOR_ROLES as string[]).includes(normalized)) {
      return normalized as ContractorRole;
    }
    return 'others';
  }

  /**
   * Get contractor member context from stored user data
   */
  static async getMemberContext(): Promise<ContractorMemberContext | null> {
    try {
      const userData = await storage_service.get_user_data();
      if (!userData) {
        return null;
      }

      // Check if contractor_member context is present (set during login)
      // Normalize permissions so role updates (e.g., representative member management)
      // are applied even when older cached payloads exist in local storage.
      if (userData.contractor_member) {
        const cached = userData.contractor_member as ContractorMemberContext;
        const isActive = this.resolveIsActive(cached?.is_active);
        const role = this.resolveRole(cached?.role);
        const hasFullAccess = cached?.has_full_access ?? (isActive && FULL_ACCESS_ROLES.includes(role));

        const normalized: ContractorMemberContext = {
          ...cached,
          role,
          is_active: isActive,
          has_full_access: hasFullAccess,
          permissions: {
            can_manage_members: isActive && MEMBER_MANAGEMENT_ROLES.includes(role),
            can_view_members: isActive,
            can_bid: hasFullAccess,
            can_manage_milestones: hasFullAccess,
            can_view_financials: hasFullAccess,
            can_manage_company_profile: hasFullAccess,
            can_upload_progress: isActive,
            can_approve_payments: hasFullAccess,
            can_view_property_owners: isActive,
          },
        };

        userData.contractor_member = normalized;
        await storage_service.save_user_data(userData);
        return normalized;
      }

      // Legacy fallback removed — it incorrectly assumed every user with
      // user_type === 'contractor' was a company owner.  Fall through to
      // the API-based path below which checks the actual DB records.

      const roleResponse: any = await role_service.get_current_role();
      const currentRole = String(roleResponse?.current_role || roleResponse?.data?.current_role || '').toLowerCase();
      const contractorPayload = roleResponse?.contractor || roleResponse?.data?.contractor || null;
      const staffPayload = roleResponse?.staff_record || roleResponse?.data?.staff_record || null;

      if (currentRole === 'contractor') {
        let context: ContractorMemberContext | null = null;

        if (contractorPayload) {
          context = {
            contractor_member_id: contractorPayload.contractor_member_id || null,
            contractor_id: contractorPayload.contractor_id || contractorPayload.contractorId || 0,
            contractor_name: contractorPayload.company_name || contractorPayload.contractor_name || null,
            role: 'owner',
            is_active: contractorPayload.is_active !== undefined
              ? Boolean(contractorPayload.is_active)
              : contractorPayload.verification_status === 'approved',
            is_contractor_owner: true,
            has_full_access: true,
            permissions: {
              can_manage_members: true,
              can_view_members: true,
              can_bid: true,
              can_manage_milestones: true,
              can_view_financials: true,
              can_manage_company_profile: true,
              can_upload_progress: true,
              can_approve_payments: true,
              can_view_property_owners: true,
            },
          };
        } else if (staffPayload) {
          const roleName = this.resolveRole(staffPayload.company_role || staffPayload.role || 'others');
          const isActive = this.resolveIsActive(staffPayload.is_active) && !this.resolveIsActive(staffPayload.is_suspended);
          const hasFullAccess = roleName === 'owner' || roleName === 'representative';

          context = {
            contractor_member_id: staffPayload.staff_id || null,
            contractor_id: staffPayload.contractor_id || 0,
            contractor_name: staffPayload.contractor_name || null,
            role: roleName,
            is_active: isActive,
            is_contractor_owner: false,
            has_full_access: hasFullAccess,
            permissions: {
              can_manage_members: isActive && MEMBER_MANAGEMENT_ROLES.includes(roleName),
              can_view_members: isActive,
              can_bid: hasFullAccess,
              can_manage_milestones: hasFullAccess,
              can_view_financials: hasFullAccess,
              can_manage_company_profile: hasFullAccess,
              can_upload_progress: isActive,
              can_approve_payments: hasFullAccess,
              can_view_property_owners: isActive,
            },
          };
        }

        if (context) {
          userData.contractor_member = context;
          await storage_service.save_user_data(userData);
          return context;
        }
      }

      return null;
    } catch (error) {
      console.error('Error getting contractor member context:', error);
      return null;
    }
  }

  /**
   * Check if the current user can manage contractor members
   * Only owner and representative roles can manage members
   */
  static async canManageMembers(): Promise<boolean> {
    const context = await this.getMemberContext();
    if (!context) {
      return false;
    }

    return context.permissions?.can_manage_members ?? 
           (context.is_active && MEMBER_MANAGEMENT_ROLES.includes(context.role));
  }

  /**
   * Check if the current user can view members list
   * All active members can view the list
   */
  static async canViewMembers(): Promise<boolean> {
    const context = await this.getMemberContext();
    if (!context) {
      return false;
    }

    return context.permissions?.can_view_members ?? context.is_active;
  }

  /**
   * Check if the current user can place bids.
   * Only owner and representative can bid.
   */
  static async canBid(): Promise<boolean> {
    const context = await this.getMemberContext();
    if (!context || !context.is_active) {
      return false;
    }

    return context.permissions?.can_bid ?? FULL_ACCESS_ROLES.includes(context.role);
  }

  /**
   * Check if the current user can manage milestones (create, edit, add).
   * Only owner and representative can manage milestones.
   */
  static async canManageMilestones(): Promise<boolean> {
    const context = await this.getMemberContext();
    if (!context || !context.is_active) {
      return false;
    }

    return context.permissions?.can_manage_milestones ?? FULL_ACCESS_ROLES.includes(context.role);
  }

  /**
   * Check if user has full access (owner or representative)
   */
  static async hasFullAccess(): Promise<boolean> {
    const context = await this.getMemberContext();
    if (!context || !context.is_active) {
      return false;
    }

    return context.has_full_access ?? FULL_ACCESS_ROLES.includes(context.role);
  }

  /**
   * Check if the current user is an active contractor member
   */
  static async isActiveMember(): Promise<boolean> {
    const context = await this.getMemberContext();
    return context?.is_active ?? false;
  }

  /**
   * Get the current user's contractor role
   */
  static async getUserRole(): Promise<ContractorRole | null> {
    const context = await this.getMemberContext();
    return context?.role ?? null;
  }

  /**
   * Check if user has a specific role
   */
  static async hasRole(role: ContractorRole): Promise<boolean> {
    const userRole = await this.getUserRole();
    return userRole === role;
  }

  /**
   * Check if user has any of the specified roles
   */
  static async hasAnyRole(roles: ContractorRole[]): Promise<boolean> {
    const userRole = await this.getUserRole();
    return userRole !== null && roles.includes(userRole);
  }

  /**
   * Save contractor member context to storage (called after login)
   */
  static async saveMemberContext(context: ContractorMemberContext): Promise<boolean> {
    try {
      const userData = await storage_service.get_user_data();
      if (userData) {
        userData.contractor_member = context;
        return await storage_service.save_user_data(userData);
      }
      return false;
    } catch (error) {
      console.error('Error saving contractor member context:', error);
      return false;
    }
  }

  /**
   * Clear contractor member context (called on logout)
   */
  static async clearMemberContext(): Promise<boolean> {
    try {
      const userData = await storage_service.get_user_data();
      if (userData && userData.contractor_member) {
        delete userData.contractor_member;
        return await storage_service.save_user_data(userData);
      }
      return true;
    } catch (error) {
      console.error('Error clearing contractor member context:', error);
      return false;
    }
  }

  /**
   * Get role display name
   */
  static getRoleDisplayName(role: ContractorRole): string {
    const displayNames: Record<ContractorRole, string> = {
      owner: 'Owner',
      representative: 'Representative',
      manager: 'Manager',
      engineer: 'Engineer',
      architect: 'Architect',
      others: 'Team Member',
    };
    return displayNames[role] || role;
  }

  /**
   * Check if role can perform member management
   */
  static roleCanManageMembers(role: ContractorRole): boolean {
    return MEMBER_MANAGEMENT_ROLES.includes(role);
  }
}

export default contractor_authorization_service;
