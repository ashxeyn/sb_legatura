import { api_config, api_request } from '../config/api';

/* ===================================================================
 * Types
 * =================================================================== */

export interface ContractorFilters {
  search?: string;
  type_id?: number;
  location?: string;
  province?: string;
  city?: string;
  min_experience?: number;
  max_experience?: number;
  picab_category?: string;
  min_completed?: number;
}

export interface ProjectFilters {
  search?: string;
  type_id?: number;
  property_type?: string;
  location?: string;
  province?: string;
  city?: string;
  budget_min?: number;
  budget_max?: number;
  project_status?: 'open' | 'completed' | 'all';
  min_lot_size?: number;
  max_lot_size?: number;
  min_floor_area?: number;
  max_floor_area?: number;
}

export interface ContractorType {
  type_id: number;
  type_name: string;
}

export interface ProjectStatusOption {
  value: string;
  label: string;
}

export interface FilterOptions {
  contractor_types: ContractorType[];
  property_types: string[];
  project_statuses: ProjectStatusOption[];
  picab_categories: string[];
}

export interface PaginationMeta {
  current_page: number;
  per_page: number;
  total: number;
  total_pages: number;
  has_more: boolean;
}

export interface SearchResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T[];
  pagination?: PaginationMeta;
  filters?: Record<string, string>;
  status: number;
}

/* ===================================================================
 * Service
 * =================================================================== */

export class search_service {
  /**
   * Search/filter contractors (owner view).
   * Hits GET /api/contractors with filter query params.
   */
  static async search_contractors(
    filters: ContractorFilters = {},
    page: number = 1,
    perPage: number = 15,
    excludeUserId?: number,
  ): Promise<SearchResponse> {
    try {
      const params = new URLSearchParams();
      params.append('page', page.toString());
      params.append('per_page', perPage.toString());
      if (excludeUserId) params.append('exclude_user_id', excludeUserId.toString());

      // Append filter params (skip empty values)
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, String(value));
        }
      });

      const endpoint = `${api_config.endpoints.contractors.list}?${params.toString()}`;
      const response = await api_request(endpoint, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return response;
    } catch (error) {
      console.error('search_service.search_contractors error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Search failed',
        status: 0,
      };
    }
  }

  /**
   * Search/filter projects (contractor view).
   * Hits GET /api/contractor/projects with filter query params.
   */
  static async search_projects(
    filters: ProjectFilters = {},
    page: number = 1,
    perPage: number = 15,
    userId?: number,
  ): Promise<SearchResponse> {
    try {
      const params = new URLSearchParams();
      params.append('page', page.toString());
      params.append('per_page', perPage.toString());
      if (userId) params.append('user_id', userId.toString());

      // Append filter params (skip empty values)
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, String(value));
        }
      });

      const endpoint = `/api/contractor/projects?${params.toString()}`;
      const response = await api_request(endpoint, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return response;
    } catch (error) {
      console.error('search_service.search_projects error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Search failed',
        status: 0,
      };
    }
  }

  /**
   * Fetch available filter options (contractor types, property types, etc.)
   * Hits GET /api/search/filter-options
   */
  static async get_filter_options(): Promise<{ success: boolean; data?: FilterOptions; message?: string }> {
    try {
      const response = await api_request('/api/search/filter-options', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      // Handle nested response structure
      const data = response.data?.data || response.data;
      return {
        success: response.success,
        data: data as FilterOptions,
        message: response.message,
      };
    } catch (error) {
      console.error('search_service.get_filter_options error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch filter options',
      };
    }
  }
}
