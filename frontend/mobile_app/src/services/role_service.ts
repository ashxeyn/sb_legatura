import { api_request } from '../config/api';

export interface SwitchRoleResponse {
  success: boolean;
  message?: string;
  current_role?: 'contractor' | 'owner';
  redirect_url?: string;
}

export interface CurrentRoleResponse {
  success: boolean;
  user_type?: string;
  current_role?: 'contractor' | 'owner';
  can_switch_roles?: boolean;
}

class RoleService {
  /**
   * Switch user role between contractor and owner
   * @param targetRole - 'contractor' or 'owner'
   */
  static async switch_role(targetRole: 'contractor' | 'owner'): Promise<SwitchRoleResponse> {
    try {
      const response = await api_request('/api/role/switch', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          role: targetRole
        }),
      });

      return response;
    } catch (error) {
      console.error('Role switch error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to switch role'
      };
    }
  }

  /**
   * Get current user role
   */
  static async get_current_role(): Promise<CurrentRoleResponse> {
    try {
      const response = await api_request('/api/role/current', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Get current role error:', error);
      return {
        success: false,
      };
    }
  }

  /**
   * Get switch role form data (for adding new role)
   * Returns form dropdowns and existing user data
   */
  static async get_switch_form_data(): Promise<any> {
    try {
      const response = await api_request('/api/role/switch-form', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Get switch form data error:', error);
      return {
        success: false,
      };
    }
  }
}

export { RoleService as role_service };




