/**
 * Contractor Authorization Service
 * 
 * Handles contractor member authorization, role checks, and permissions.
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

import { storage_service } from '../utils/storage';

// Contractor member roles
export type ContractorRole = 'owner' | 'representative' | 'manager' | 'engineer' | 'architect' | 'others';

// Roles with full access (bid, milestones, manage members)
export const FULL_ACCESS_ROLES: ContractorRole[] = ['owner', 'representative'];

// Roles allowed to manage members (add/remove/edit) - same as full access
export const MEMBER_MANAGEMENT_ROLES: ContractorRole[] = ['owner', 'representative'];

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
    
    // All active members can do these
    can_upload_progress: boolean;
    can_approve_payments: boolean;
    can_view_property_owners: boolean;
  };
}

export class contractor_authorization_service {
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
      if (userData.contractor_member) {
        return userData.contractor_member as ContractorMemberContext;
      }

      // Fallback for contractor owners who logged in before this feature was added
      // If user is a contractor type, assume they are the owner with full permissions
      if (userData.user_type === 'contractor' || userData.determinedRole === 'contractor') {
        console.log('Using fallback contractor owner context');
        return {
          contractor_member_id: null,
          contractor_id: userData.contractor_id || 0,
          contractor_name: userData.company_name || null,
          role: 'owner',
          is_active: true,
          is_contractor_owner: true,
          has_full_access: true,
          permissions: {
            can_manage_members: true,
            can_view_members: true,
            can_bid: true,
            can_manage_milestones: true,
            can_upload_progress: true,
            can_approve_payments: true,
            can_view_property_owners: true,
          },
        };
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
