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
     * Merges public dropdowns from `/api/signup-form` with authenticated
     * prefill data from `/api/role/switch-form` (or `/api/user` fallback).
     */
    static async get_switch_form_data(): Promise<any> {
      try {
        // Prefer authenticated switch-form for both form data and prefill
        let raw: any = {};
        let existing: any = {};
        try {
          const switchRes = await api_request('/api/role/switch-form', { method: 'GET' });
          if (switchRes?.success && switchRes.data) {
            raw = switchRes.data.form_data ?? switchRes.data;
            existing = switchRes.data.existing_data ?? {};
          } else {
            // Fallback to public signup-form for dropdowns
            const formRes = await api_request('/api/signup-form', { method: 'GET' });
            if (formRes?.success && formRes.data) {
              raw = formRes.data;
            }
            // Fallback to basic user info for prefill
            const userRes = await api_request('/api/user', { method: 'GET' });
            if (userRes?.success && userRes.data) existing = { user: userRes.data.user };
          }
        } catch (e) {
          // Silent fallback chain
          try {
            const formRes = await api_request('/api/signup-form', { method: 'GET' });
            if (formRes?.success && formRes.data) raw = formRes.data;
          } catch {}
          try {
            const userRes = await api_request('/api/user', { method: 'GET' });
            if (userRes?.success && userRes.data) existing = { user: userRes.data.user };
          } catch {}
        }

        if (raw && typeof raw === 'object') {
          // Normalize lists to a consistent shape { id, name }
          const normList = (list: any[], idKey: string, nameKeys: string[]): any[] => {
            if (!Array.isArray(list)) return [];
            return list.map((item: any) => {
              const id = item?.[idKey] ?? item?.id;
              let name = '';
              for (const k of nameKeys) {
                if (item?.[k]) { name = item[k]; break; }
              }
              if (!name) name = item?.name ?? item?.type_name ?? item?.occupation_name ?? item?.valid_id_name ?? '';
              return { id: `${id}` , name };
            });
          };

          const normalizedForm = {
            contractor_types: normList(raw.contractor_types || [], 'type_id', ['type_name', 'name']),
            occupations: normList(raw.occupations || [], 'id', ['occupation_name', 'name', 'title']),
            valid_ids: normList(raw.valid_ids || [], 'id', ['valid_id_name', 'name']),
            provinces: Array.isArray(raw.provinces) ? raw.provinces : [],
            picab_categories: Array.isArray(raw.picab_categories) ? raw.picab_categories : [],
          };

          // Return a merged, normalized shape with raw included for backward compatibility
          return {
            success: true,
            data: {
              form_data: normalizedForm,
              form_data_raw: raw,
              existing_data: existing,
              existing_data_raw: existing,
            },
          };
        }

        return { success: false, message: 'Failed to load form data' };
      } catch (error) {
        console.error('get_switch_form_data error:', error);
        return { success: false, message: error instanceof Error ? error.message : 'Failed to load form data' };
      }
    }

    // Add role (Contractor) APIs
    static async add_contractor_step1(payload: any): Promise<any> {
      try {
        return await api_request('/api/role/add/contractor/step1', {
          method: 'POST',
          body: JSON.stringify(payload),
        });
      } catch (error) {
        console.error('add_contractor_step1 error:', error);
        return { success: false, message: 'Network error' };
      }
    }

    static async add_contractor_step2(formData: FormData): Promise<any> {
      try {
        return await api_request('/api/role/add/contractor/step2', {
          method: 'POST',
          body: formData as any,
        });
      } catch (error) {
        console.error('add_contractor_step2 error:', error);
        return { success: false, message: 'Network error' };
      }
    }

    static async add_contractor_final(payload: any): Promise<any> {
      try {
        return await api_request('/api/role/add/contractor/final', {
          method: 'POST',
          body: JSON.stringify(payload),
        });
      } catch (error) {
        console.error('add_contractor_final error:', error);
        return { success: false, message: 'Network error' };
      }
    }

    // Add role (Owner) APIs
    static async add_owner_step1(payload: any): Promise<any> {
      try {
        return await api_request('/api/role/add/owner/step1', {
          method: 'POST',
          body: JSON.stringify(payload),
        });
      } catch (error) {
        console.error('add_owner_step1 error:', error);
        return { success: false, message: 'Network error' };
      }
    }

    static async add_owner_step2(formData: FormData): Promise<any> {
      try {
        return await api_request('/api/role/add/owner/step2', {
          method: 'POST',
          body: formData as any,
        });
      } catch (error) {
        console.error('add_owner_step2 error:', error);
        return { success: false, message: 'Network error' };
      }
    }

    static async add_owner_final(payload: any): Promise<any> {
      try {
        return await api_request('/api/role/add/owner/final', {
          method: 'POST',
          body: JSON.stringify(payload),
        });
      } catch (error) {
        console.error('add_owner_final error:', error);
        return { success: false, message: 'Network error' };
      }
    }
    }

    export { RoleService as role_service };




