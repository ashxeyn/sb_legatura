import { api_config, api_request } from '../config/api';

/**
 * Project Form Data structure for creating a new project
 * Matches the backend validation in projectsRequest.php
 */
export interface ProjectFormData {
  project_title: string;
  project_description: string;
  barangay: string;
  street_address: string;
  project_location: string;
  budget_range_min: string;
  budget_range_max: string;
  lot_size: string;
  floor_area: string;
  property_type: string; // Residential, Commercial, Industrial, Agricultural
  type_id: string; // Contractor type ID
  if_others_ctype?: string; // Required if type_id is "Others"
  bidding_deadline: string; // Date format: YYYY-MM-DD
  building_permit?: any; // Required - image file
  title_of_land?: any; // Required - image file
  blueprint?: any[]; // Optional - array of files
  desired_design?: any[]; // Optional - array of files
  others?: any[]; // Optional - array of files
}

/**
 * Contractor Type from backend
 */
export interface ContractorType {
  type_id: number;
  type_name: string;
}

/**
 * API response wrapper
 */
export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  status: number;
}

/**
 * Projects Service
 * Handles project-related API calls
 */
export class projects_service {
  /**
   * Fetch contractor types from the backend
   * These are used in the project creation form
   */
  static async get_contractor_types(): Promise<ApiResponse<ContractorType[]>> {
    try {
      const response = await api_request('/api/contractor-types', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching contractor types:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch contractor types',
        status: 500,
      };
    }
  }

  /**
   * Create a new project
   * Uses FormData for file uploads
   */
  static async create_project(projectData: ProjectFormData, userId: number): Promise<ApiResponse> {
    try {
      const formData = new FormData();

      // Add text fields
      formData.append('project_title', projectData.project_title);
      formData.append('project_description', projectData.project_description);
      formData.append('barangay', projectData.barangay);
      formData.append('street_address', projectData.street_address);
      formData.append('project_location', projectData.project_location);
      formData.append('budget_range_min', projectData.budget_range_min);
      formData.append('budget_range_max', projectData.budget_range_max);
      formData.append('lot_size', projectData.lot_size);
      formData.append('floor_area', projectData.floor_area);
      formData.append('property_type', projectData.property_type);
      formData.append('type_id', projectData.type_id);
      formData.append('bidding_due', projectData.bidding_deadline); // API expects bidding_due, not bidding_deadline
      formData.append('user_id', userId.toString());

      // Add conditional "Others" contractor type
      if (projectData.if_others_ctype) {
        formData.append('if_others_ctype', projectData.if_others_ctype);
      }

      // Add building permit (required image)
      if (projectData.building_permit) {
        const permitFile = {
          uri: projectData.building_permit.uri,
          type: projectData.building_permit.mimeType || 'image/jpeg',
          name: projectData.building_permit.fileName || 'building_permit.jpg',
        };
        formData.append('building_permit', permitFile as any);
      }

      // Add title of land (required image)
      if (projectData.title_of_land) {
        const titleFile = {
          uri: projectData.title_of_land.uri,
          type: projectData.title_of_land.mimeType || 'image/jpeg',
          name: projectData.title_of_land.fileName || 'title_of_land.jpg',
        };
        formData.append('title_of_land', titleFile as any);
      }

      // Add optional blueprint files
      if (projectData.blueprint && projectData.blueprint.length > 0) {
        projectData.blueprint.forEach((file, index) => {
          const blueprintFile = {
            uri: file.uri,
            type: file.mimeType || 'application/octet-stream',
            name: file.name || `blueprint_${index}.pdf`,
          };
          formData.append('blueprint[]', blueprintFile as any);
        });
      }

      // Add optional desired design files
      if (projectData.desired_design && projectData.desired_design.length > 0) {
        projectData.desired_design.forEach((file, index) => {
          const designFile = {
            uri: file.uri,
            type: file.mimeType || 'application/octet-stream',
            name: file.name || `design_${index}.pdf`,
          };
          formData.append('desired_design[]', designFile as any);
        });
      }

      // Add optional other files
      if (projectData.others && projectData.others.length > 0) {
        projectData.others.forEach((file, index) => {
          const otherFile = {
            uri: file.uri,
            type: file.mimeType || 'application/octet-stream',
            name: file.name || `other_${index}.pdf`,
          };
          formData.append('others[]', otherFile as any);
        });
      }

      // Make API request with FormData
      const url = `${api_config.base_url}/api/owner/projects`;
      console.log('Creating project at:', url);

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          // Note: Don't set Content-Type when using FormData - it's set automatically with boundary
        },
        body: formData,
      });

      const data = await response.json();
      console.log('Create project response:', data);

      return {
        success: response.ok && (data.success !== false),
        message: data.message || (response.ok ? 'Project created successfully' : 'Failed to create project'),
        data: data,
        status: response.status,
      };
    } catch (error) {
      console.error('Error creating project:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to create project',
        status: 500,
      };
    }
  }

  /**
   * Get projects for a property owner
   */
  static async get_owner_projects(userId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/owner/projects?user_id=${userId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching projects:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch projects',
        status: 500,
      };
    }
  }

  /**
   * Update an existing project
   */
  static async update_project(projectData: {
    project_id: number;
    user_id: number;
    project_title: string;
    project_description: string;
    project_location: string;
    budget_range_min: number;
    budget_range_max: number;
    lot_size?: number | null;
    floor_area?: number | null;
    property_type: string;
    type_id: number | null;
    bidding_deadline: string;
  }): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/owner/projects/${projectData.project_id}`, {
        method: 'PUT',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(projectData),
      });

      return response;
    } catch (error) {
      console.error('Error updating project:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to update project',
        status: 500,
      };
    }
  }

  /**
   * Get bids for a specific project
   */
  static async get_project_bids(projectId: number, userId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/owner/projects/${projectId}/bids?user_id=${userId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      console.log('get_project_bids raw response:', JSON.stringify(response, null, 2));

      // The api_request wraps the response, so response.data contains the API response
      // Extract the actual data array from the nested structure
      if (response.success && response.data) {
        // Check if response.data is already an array (bids array)
        if (Array.isArray(response.data)) {
          return {
            success: true,
            message: response.message,
            data: response.data,
            status: response.status,
          };
        }
        // Otherwise, it's the API response object with nested data
        const bidsData = response.data.data;
        return {
          success: response.data.success ?? response.success,
          message: response.data.message ?? response.message,
          data: Array.isArray(bidsData) ? bidsData : [],
          status: response.status,
        };
      }

      return {
        ...response,
        data: [], // Ensure data is always an array
      };
    } catch (error) {
      console.error('Error fetching project bids:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch bids',
        data: [],
        status: 500,
      };
    }
  }

  /**
   * Accept a bid for a project
   * This will also reject all other bids and close bidding
   */
  static async accept_bid(projectId: number, bidId: number, userId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/owner/projects/${projectId}/bids/${bidId}/accept`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ user_id: userId }),
      });

      // Unwrap the nested response structure
      if (response.data) {
        return {
          success: response.data.success ?? response.success,
          message: response.data.message ?? response.message,
          data: response.data.data ?? response.data,
          status: response.status,
        };
      }

      return response;
    } catch (error) {
      console.error('Error accepting bid:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to accept bid',
        status: 500,
      };
    }
  }

  /**
   * Reject a bid for a project
   */
  static async reject_bid(projectId: number, bidId: number, userId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/owner/projects/${projectId}/bids/${bidId}/reject`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ user_id: userId }),
      });

      // Unwrap the nested response structure
      if (response.data) {
        return {
          success: response.data.success ?? response.success,
          message: response.data.message ?? response.message,
          data: response.data.data ?? response.data,
          status: response.status,
        };
      }

      return response;
    } catch (error) {
      console.error('Error rejecting bid:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to reject bid',
        status: 500,
      };
    }
  }

  /**
   * Get approved projects for contractor feed
   * These are projects open for bidding
   */
  static async get_approved_projects(): Promise<ApiResponse> {
    try {
      const response = await api_request('/api/contractor/projects', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching approved projects:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch projects',
        status: 500,
      };
    }
  }

  /**
   * Submit a bid for a project (contractor)
   */
  static async submit_bid(
    projectId: number,
    userId: number,
    bidData: {
      proposed_cost: number;
      estimated_timeline: string;
      contractor_notes?: string;
    }
  ): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/contractor/projects/${projectId}/bid`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          user_id: userId,
          ...bidData,
        }),
      });

      return response;
    } catch (error) {
      console.error('Error submitting bid:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to submit bid',
        status: 500,
      };
    }
  }

  /**
   * Get contractor's existing bid for a project
   */
  static async get_my_bid(projectId: number, userId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/contractor/projects/${projectId}/my-bid?user_id=${userId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching my bid:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch bid',
        status: 500,
      };
    }
  }

  /**
   * Get all bids for a contractor
   */
  static async get_my_bids(userId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/contractor/my-bids?user_id=${userId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching my bids:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch bids',
        status: 500,
      };
    }
  }

  // ==================== CONTRACTOR MILESTONE METHODS ====================

  /**
   * Get contractor's projects (accepted bids only)
   */
  static async get_contractor_projects(userId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/contractor/my-projects?user_id=${userId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching contractor projects:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch projects',
        status: 500,
      };
    }
  }

  /**
   * Get milestone form data for a project
   */
  static async get_milestone_form_data(projectId: number, userId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/contractor/projects/${projectId}/milestone-form?user_id=${userId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching milestone form data:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch form data',
        status: 500,
      };
    }
  }

  /**
   * Submit milestones for a project
   */
  static async submit_milestones(
    userId: number,
    projectId: number,
    milestoneData: {
      milestone_name: string;
      milestone_description?: string;
      payment_mode: 'full_payment' | 'downpayment';
      start_date: string;
      end_date: string;
      total_project_cost: number;
      downpayment_amount?: number;
      items: Array<{
        title: string;
        description?: string;
        percentage: number;
        duration_days: number;
      }>;
    }
  ): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/contractor/projects/${projectId}/milestones`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          user_id: userId,
          ...milestoneData,
        }),
      });

      return response;
    } catch (error) {
      console.error('Error submitting milestones:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to submit milestones',
        status: 500,
      };
    }
  }
}
