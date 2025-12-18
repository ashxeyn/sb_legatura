import { api_request } from '../config/api';

interface ApiResponse {
  success: boolean;
  message?: string;
  data?: any;
  status?: number;
  errors?: any;
}

export class dispute_service {
  /**
   * File a new dispute
   */
  static async file_dispute(
    projectId: number,
    milestoneId: number,
    milestoneItemId: number,
    disputeType: 'Payment' | 'Delay' | 'Quality' | 'Others',
    disputeDesc: string,
    ifOthersDistype?: string,
    evidenceFiles?: { uri: string; name: string; type: string }[]
  ): Promise<ApiResponse> {
    try {
      // Validate required parameters
      if (!projectId || !milestoneId || !milestoneItemId) {
        console.error('Missing required IDs:', { projectId, milestoneId, milestoneItemId });
        return {
          success: false,
          message: 'Missing required project, milestone, or milestone item information',
          status: 400,
        };
      }

      const formData = new FormData();
      formData.append('project_id', String(projectId));
      formData.append('milestone_id', String(milestoneId));
      formData.append('milestone_item_id', String(milestoneItemId));
      formData.append('dispute_type', disputeType);
      formData.append('dispute_desc', disputeDesc);
      
      if (ifOthersDistype) {
        formData.append('if_others_distype', ifOthersDistype);
      }

      if (evidenceFiles && evidenceFiles.length > 0) {
        evidenceFiles.forEach((file, index) => {
          formData.append(`evidence_files[${index}]`, {
            uri: file.uri,
            name: file.name,
            type: file.type,
          } as any);
        });
      }

      const response = await api_request('/api/disputes', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
        },
        body: formData,
      });

      return response;
    } catch (error) {
      console.error('Error filing dispute:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to file dispute',
        status: 500,
      };
    }
  }

  /**
   * Get all disputes for the current user
   */
  static async get_disputes(): Promise<ApiResponse> {
    try {
      const response = await api_request('/api/disputes', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching disputes:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch disputes',
        status: 500,
      };
    }
  }

  /**
   * Get dispute details by ID
   */
  static async get_dispute_details(disputeId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/disputes/${disputeId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching dispute details:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch dispute details',
        status: 500,
      };
    }
  }

  /**
   * Update a dispute
   */
  static async update_dispute(
    disputeId: number,
    disputeType?: 'Payment' | 'Delay' | 'Quality' | 'Others',
    disputeDesc?: string,
    evidenceFiles?: { uri: string; name: string; type: string }[],
    deletedFileIds?: number[]
  ): Promise<ApiResponse> {
    try {
      const formData = new FormData();
      
      if (disputeType) {
        formData.append('dispute_type', disputeType);
      }
      if (disputeDesc) {
        formData.append('dispute_desc', disputeDesc);
      }
      if (deletedFileIds && deletedFileIds.length > 0) {
        formData.append('deleted_file_ids', deletedFileIds.join(','));
      }
      if (evidenceFiles && evidenceFiles.length > 0) {
        evidenceFiles.forEach((file, index) => {
          formData.append(`evidence_files[${index}]`, {
            uri: file.uri,
            name: file.name,
            type: file.type,
          } as any);
        });
      }

      const response = await api_request(`/api/disputes/${disputeId}`, {
        method: 'PUT',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
        },
        body: formData,
      });

      return response;
    } catch (error) {
      console.error('Error updating dispute:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to update dispute',
        status: 500,
      };
    }
  }

  /**
   * Cancel a dispute
   */
  static async cancel_dispute(disputeId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/disputes/${disputeId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Error cancelling dispute:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to cancel dispute',
        status: 500,
      };
    }
  }
}
