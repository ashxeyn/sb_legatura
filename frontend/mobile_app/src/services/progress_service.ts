import { api_request } from '../config/api';

interface ApiResponse {
  success: boolean;
  message?: string;
  data?: any;
  status?: number;
}

interface ProgressFile {
  file_id: number;
  progress_id: number;
  file_path: string;
  original_name: string;
}

interface Progress {
  progress_id: number;
  item_id: number;
  purpose: string;
  progress_status: 'submitted' | 'approved' | 'rejected' | 'deleted';
  submitted_at: string;
  files?: ProgressFile[];
}

export class progress_service {
  /**
   * Submit a new progress report with files
   */
  static async submit_progress(
    userId: number,
    itemId: number,
    purpose: string,
    files: { uri: string; name: string; type: string }[]
  ): Promise<ApiResponse> {
    try {
      const formData = new FormData();
      formData.append('user_id', userId.toString());
      formData.append('item_id', itemId.toString());
      formData.append('purpose', purpose);

      // Append files
      files.forEach((file, index) => {
        formData.append(`progress_files[${index}]`, {
          uri: file.uri,
          name: file.name,
          type: file.type,
        } as any);
      });

      const response = await api_request('/api/contractor/progress/upload', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          // Don't set Content-Type for FormData - browser will set it automatically with boundary
        },
        body: formData,
      });

      return response;
    } catch (error) {
      console.error('Error submitting progress:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to submit progress',
        status: 500,
      };
    }
  }

  /**
   * Get progress reports for a milestone item (works for both owners and contractors)
   */
  static async get_progress_by_item(userId: number, itemId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/both/progress/files/${itemId}?user_id=${userId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching progress:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch progress',
        status: 500,
      };
    }
  }

  /**
   * Get a specific progress report with files
   */
  static async get_progress(userId: number, progressId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/contractor/progress/files/${progressId}?user_id=${userId}&progress_id=${progressId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
        },
      });

      return response;
    } catch (error) {
      console.error('Error fetching progress:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to fetch progress',
        status: 500,
      };
    }
  }

  /**
   * Update an existing progress report
   */
  static async update_progress(
    userId: number,
    progressId: number,
    purpose?: string,
    newFiles?: { uri: string; name: string; type: string }[],
    deletedFileIds?: number[]
  ): Promise<ApiResponse> {
    try {
      const formData = new FormData();
      formData.append('user_id', userId.toString());

      if (purpose) {
        formData.append('purpose', purpose);
      }

      if (deletedFileIds && deletedFileIds.length > 0) {
        formData.append('deleted_file_ids', deletedFileIds.join(','));
      }

      if (newFiles && newFiles.length > 0) {
        newFiles.forEach((file, index) => {
          formData.append(`progress_files[${index}]`, {
            uri: file.uri,
            name: file.name,
            type: file.type,
          } as any);
        });
      }

      const response = await api_request(`/api/progress/${progressId}`, {
        method: 'PUT',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'multipart/form-data',
        },
        body: formData,
      });

      return response;
    } catch (error) {
      console.error('Error updating progress:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to update progress',
        status: 500,
      };
    }
  }

  /**
   * Delete a progress report
   */
  static async delete_progress(userId: number, progressId: number, reason: string): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/progress/${progressId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          user_id: userId,
          reason: reason,
        }),
      });

      return response;
    } catch (error) {
      console.error('Error deleting progress:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to delete progress',
        status: 500,
      };
    }
  }

  /**
   * Approve a progress report (Owner)
   */
  static async approve_progress(progressId: number): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/progress/${progressId}/approve`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({}),
      });

      return response;
    } catch (error) {
      console.error('Error approving progress:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to approve progress',
        status: 500,
      };
    }
  }

  /**
   * Reject a progress report (Owner)
   */
  static async reject_progress(progressId: number, reason?: string): Promise<ApiResponse> {
    try {
      const response = await api_request(`/api/progress/${progressId}/reject`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reason: reason || '' }),
      });

      return response;
    } catch (error) {
      console.error('Error rejecting progress:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Failed to reject progress',
        status: 500,
      };
    }
  }
}
