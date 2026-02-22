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
 * Pagination metadata
 */
export interface PaginationMeta {
  current_page: number;
  per_page: number;
  total: number;
  total_pages: number;
  has_more: boolean;
}

/**
 * API response wrapper
 */
export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  pagination?: PaginationMeta;
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

      // Add optional blueprint files (send as array fields 'blueprint[]')
      if (projectData.blueprint && projectData.blueprint.length > 0) {
        projectData.blueprint.forEach((file, index) => {
          const uri = file.uri || file.uri;
          const derivedName = (file.fileName as string) || (file.name as string) || uri.split('/').pop() || `blueprint_${index}.jpg`;
          const name = derivedName.includes('.') ? derivedName : `${derivedName}.jpg`;
          const type = file.mimeType || (file.type as string) || (name.endsWith('.png') ? 'image/png' : 'image/jpeg');
          const blueprintFile = { uri, type, name };
          // Append with array notation for Laravel
          formData.append('blueprint[]', blueprintFile as any);
        });
      }

      // Add optional desired design files
      if (projectData.desired_design && projectData.desired_design.length > 0) {
        projectData.desired_design.forEach((file, index) => {
          const uri = file.uri || file.uri;
          const derivedName = (file.fileName as string) || (file.name as string) || uri.split('/').pop() || `design_${index}.jpg`;
          const name = derivedName.includes('.') ? derivedName : `${derivedName}.jpg`;
          const type = file.mimeType || (file.type as string) || (name.endsWith('.png') ? 'image/png' : 'image/jpeg');
          const designFile = { uri, type, name };
          formData.append('desired_design[]', designFile as any);
        });
      }

      // Add optional other files
      if (projectData.others && projectData.others.length > 0) {
        projectData.others.forEach((file, index) => {
          const uri = file.uri || file.uri;
          const derivedName = (file.fileName as string) || (file.name as string) || uri.split('/').pop() || `other_${index}.jpg`;
          const name = derivedName.includes('.') ? derivedName : `${derivedName}.jpg`;
          const type = file.mimeType || (file.type as string) || (name.endsWith('.png') ? 'image/png' : 'image/jpeg');
          const otherFile = { uri, type, name };
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
      // Backend expects 'bidding_due' field; map accordingly
      const payload: any = {
        ...projectData,
      };
      if (projectData.bidding_deadline) {
        payload.bidding_due = projectData.bidding_deadline;
      }
      delete payload.bidding_deadline;

      const response = await api_request(`/api/owner/projects/${projectData.project_id}`, {
        method: 'PUT',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
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
   * Try to fetch files for a specific bid. Some API versions return files inline,
   * but if not present we attempt a few likely endpoints and return an array.
   */
  static async get_bid_files(projectId: number, bidId: number): Promise<ApiResponse<any[]>> {
    const candidates = [
      `/api/owner/projects/${projectId}/bids/${bidId}`,
      `/api/owner/projects/${projectId}/bids/${bidId}/files`,
      `/api/bids/${bidId}/files`,
      `/api/bids/${bidId}`,
    ];

    for (const path of candidates) {
      try {
        const response = await api_request(path, {
          method: 'GET',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response || !response.success) continue;

        // Normalize various shapes: response.data may be array or object with data.files
        const payload = response.data ?? {};
        if (Array.isArray(payload)) {
          return { success: true, data: payload, status: response.status };
        }

        // nested data
        const nested = payload.data ?? payload;
        if (Array.isArray(nested)) return { success: true, data: nested, status: response.status };
        if (Array.isArray(nested.files)) return { success: true, data: nested.files, status: response.status };
        if (Array.isArray(payload.files)) return { success: true, data: payload.files, status: response.status };
      } catch (err) {
        // try next candidate
        continue;
      }
    }

    return { success: false, data: [], message: 'No files found', status: 404 };
  }

  /**
   * Reject a bid for a project
   */
  static async reject_bid(projectId: number, bidId: number, userId: number): Promise<ApiResponse> {
    try {
      // Accept optional rejection reason
      const body: any = { user_id: userId };
      // If a fourth argument 'reason' was provided, include it
      const args = Array.from(arguments) as any[];
      if (args.length >= 4 && args[3]) {
        body.reason = args[3];
      }

      const response = await api_request(`/api/owner/projects/${projectId}/bids/${bidId}/reject`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
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
   * Get approved projects for contractor feed with pagination support
   * These are projects open for bidding
   * 
   * @param page - Page number (default: 1)
   * @param perPage - Items per page (default: 15)
   * @returns Promise with API response containing projects and pagination metadata
   */
  static async get_approved_projects(
    page: number = 1,
    perPage: number = 15
  ): Promise<ApiResponse> {
    try {
      const params = new URLSearchParams();
      params.append('page', page.toString());
      params.append('per_page', perPage.toString());
      
      const response = await api_request(`/api/contractor/projects?${params.toString()}`, {
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
      bidFiles?: Array<any>;
    }
  ): Promise<ApiResponse> {
    try {
      // If files are provided, send as FormData (Laravel expects bid_files[])
      if (bidData.bidFiles && Array.isArray(bidData.bidFiles) && bidData.bidFiles.length > 0) {
        const formData = new FormData();
        formData.append('user_id', userId.toString());
        formData.append('proposed_cost', bidData.proposed_cost.toString());
        formData.append('estimated_timeline', bidData.estimated_timeline);
        if (bidData.contractor_notes) formData.append('contractor_notes', bidData.contractor_notes);

        bidData.bidFiles.forEach((file: any, index: number) => {
          const uri = file.uri;
          const derivedName = file.name || file.fileName || (uri && uri.split('/').pop()) || `bid_file_${index}`;
          const name = derivedName.includes('.') ? derivedName : `${derivedName}.jpg`;
          const type = file.type || file.mimeType || (name.endsWith('.pdf') ? 'application/pdf' : 'image/jpeg');
          const fileObj: any = { uri, name, type };
          formData.append('bid_files[]', fileObj as any);
        });

        const url = `${api_config.base_url}/api/contractor/projects/${projectId}/bid`;
        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: formData,
        });

        const data = await response.json();

        return {
          success: response.ok && (data.success !== false),
          message: data.message || (response.ok ? 'Bid submitted' : 'Failed to submit bid'),
          data: data,
          status: response.status,
        };
      }

      // Otherwise fallback to JSON POST
      const response = await api_request(`/api/contractor/projects/${projectId}/bid`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          user_id: userId,
          proposed_cost: bidData.proposed_cost,
          estimated_timeline: bidData.estimated_timeline,
          contractor_notes: bidData.contractor_notes,
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
   * Update an existing bid (contractor) — only for submitted/under_review bids
   */
  static async update_bid(
    bidId: number,
    userId: number,
    bidData: {
      proposed_cost: number;
      estimated_timeline: string;
      contractor_notes?: string;
      bidFiles?: Array<any>;
      deleteFileIds?: number[];
    }
  ): Promise<ApiResponse> {
    try {
      // Always use FormData to keep it consistent and support file uploads
      const formData = new FormData();
      formData.append('user_id', userId.toString());
      formData.append('bid_id', bidId.toString());
      formData.append('proposed_cost', bidData.proposed_cost.toString());
      formData.append('estimated_timeline', bidData.estimated_timeline);
      if (bidData.contractor_notes) formData.append('contractor_notes', bidData.contractor_notes);

      // Append new files
      if (bidData.bidFiles && Array.isArray(bidData.bidFiles) && bidData.bidFiles.length > 0) {
        bidData.bidFiles.forEach((file: any, index: number) => {
          const uri = file.uri;
          const derivedName = file.name || file.fileName || (uri && uri.split('/').pop()) || `bid_file_${index}`;
          const name = derivedName.includes('.') ? derivedName : `${derivedName}.jpg`;
          const type = file.type || file.mimeType || (name.endsWith('.pdf') ? 'application/pdf' : 'image/jpeg');
          const fileObj: any = { uri, name, type };
          formData.append('bid_files[]', fileObj as any);
        });
      }

      // Append file IDs to delete
      if (bidData.deleteFileIds && bidData.deleteFileIds.length > 0) {
        bidData.deleteFileIds.forEach((fileId) => {
          formData.append('delete_files[]', fileId.toString());
        });
      }

      const url = `${api_config.base_url}/api/contractor/bids/${bidId}`;
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
      });

      const data = await response.json();

      return {
        success: response.ok && (data.success !== false),
        message: data.message || (response.ok ? 'Bid updated' : 'Failed to update bid'),
        data: data,
        status: response.status,
      };
    } catch (error) {
      console.error('Error updating bid:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to update bid',
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

  /**
   * Update an existing milestone (for rejected milestones)
   */
  static async update_milestone(
    userId: number,
    projectId: number,
    milestoneId: number,
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
        date_to_finish: string;
      }>;
    }
  ): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/contractor/projects/${projectId}/milestones/${milestoneId}`, {
        method: 'PUT',
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
      console.error('Error updating milestone:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to update milestone',
        status: 500,
      };
    }
  }

  /**
   * Complete a project (mark as completed)
   * Only available to property owners when all milestone items are completed
   */
  static async complete_project(projectId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/owner/projects/${projectId}/complete`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Error completing project:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to complete project',
        status: 500,
      };
    }
  }
}
