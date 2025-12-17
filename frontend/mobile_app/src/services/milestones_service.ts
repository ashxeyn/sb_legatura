import { api_request } from '../config/api';

interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
}

export const milestones_service = {
  /**
   * Mark a milestone as complete (Owner)
   */
  complete_milestone: async (milestoneId: number, userId: number): Promise<ApiResponse> => {
    try {
      const response = await api_request(`/api/owner/milestones/${milestoneId}/complete`, {
        method: 'POST',
        body: JSON.stringify({ user_id: userId }),
      });
      return response;
    } catch (error: any) {
      console.error('Error completing milestone:', error);
      return {
        success: false,
        message: error.message || 'Failed to complete milestone',
      };
    }
  },

  /**
   * Approve a milestone (Owner)
   */
  approve_milestone: async (milestoneId: number, userId: number): Promise<ApiResponse> => {
    try {
      const response = await api_request(`/api/owner/milestones/${milestoneId}/approve`, {
        method: 'POST',
        body: JSON.stringify({
          user_id: userId,
        }),
      });
      return response;
    } catch (error: any) {
      console.error('Error approving milestone:', error);
      return {
        success: false,
        message: error.message || 'Failed to approve milestone',
      };
    }
  },

  /**
   * Reject a milestone (Owner)
   */
  reject_milestone: async (milestoneId: number, userId: number, reason?: string): Promise<ApiResponse> => {
    try {
      const response = await api_request(`/api/owner/milestones/${milestoneId}/reject`, {
        method: 'POST',
        body: JSON.stringify({
          user_id: userId,
          reason: reason || '',
        }),
      });
      return response;
    } catch (error: any) {
      console.error('Error rejecting milestone:', error);
      return {
        success: false,
        message: error.message || 'Failed to reject milestone',
      };
    }
  },

  /**
   * Mark a milestone item as complete (Owner)
   */
  complete_milestone_item: async (itemId: number, userId: number): Promise<ApiResponse> => {
    try {
      const response = await api_request(`/api/owner/milestone-items/${itemId}/complete`, {
        method: 'POST',
        body: JSON.stringify({ user_id: userId }),
      });
      return response;
    } catch (error: any) {
      console.error('Error completing milestone item:', error);
      return {
        success: false,
        message: error.message || 'Failed to complete milestone item',
      };
    }
  },
};
