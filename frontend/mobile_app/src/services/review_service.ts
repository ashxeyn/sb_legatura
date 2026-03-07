import { api_request } from '../config/api';

export interface ReviewPayload {
  project_id: number;
  reviewee_user_id: number;
  rating: number;
  comment: string;
}

export interface CanReviewResponse {
  can_review: boolean;
  reason?: string;
  reviewee_user_id?: number;
  reviewee_name?: string;
}

export interface ReviewData {
  review_id: number;
  project_id: number;
  reviewer_user_id: number;
  reviewee_user_id: number;
  rating: number;
  comment: string;
  created_at: string;
}

export interface RevieweeInfo {
  user_id: number;
  username: string;
  profile_pic?: string;
  company_name?: string;
  role: 'contractor' | 'property_owner';
}

export const review_service = {
  /**
   * Check if the current user can review the other party on a project.
   */
  can_review: async (projectId: number): Promise<{ success: boolean; data?: CanReviewResponse }> => {
    try {
      const response: any = await api_request(
        `/api/reviews/can-review?project_id=${projectId}`,
        { method: 'GET', headers: { Accept: 'application/json' } }
      );

      if (!response?.success) {
        return { success: false };
      }

      // api_request wraps the JSON in { success, data, status, message }
      // Backend returns { success: true, data: { can_review, reason?, reviewee_user_id? } }
      // So the actual payload is at response.data.data (double-nested)
      const backendBody = response.data || {};
      const innerData = backendBody.data || backendBody;

      return {
        success: true,
        data: {
          can_review: !!innerData?.can_review,
          reason: innerData?.reason,
          reviewee_user_id: innerData?.reviewee_user_id,
          reviewee_name: innerData?.reviewee_name,
        },
      };
    } catch (error) {
      console.error('review_service.can_review error:', error);
      return { success: false };
    }
  },

  /**
   * Submit a review.
   */
  submit_review: async (payload: ReviewPayload): Promise<{ success: boolean; message: string; data?: ReviewData }> => {
    try {
      const response: any = await api_request('/api/reviews', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(payload),
      });

      // api_request returns { success (HTTP ok), data (parsed JSON body), status, message }
      // Backend returns { success: true/false, message, review? }
      const backendBody = response.data || {};
      return {
        success: response.success && (backendBody.success !== false),
        message: backendBody.message || response.message || '',
        data: backendBody.review || backendBody.data,
      };
    } catch (error: any) {
      console.error('review_service.submit_review error:', error);
      return { success: false, message: error?.message || 'Failed to submit review.' };
    }
  },

  /**
   * Get info about the reviewee (the other party on the project).
   */
  get_reviewee_info: async (projectId: number, revieweeUserId: number): Promise<{ success: boolean; data?: RevieweeInfo }> => {
    try {
      // Use the profile/view endpoint to get basic info about the reviewee
      const response = await api_request(
        `/api/profile/view/${revieweeUserId}`,
        { method: 'GET', headers: { Accept: 'application/json' } }
      );
      if (response?.success) {
        const profileData = response.data?.data || response.data || {};
        const header = profileData.header || {};
        return {
          success: true,
          data: {
            user_id: revieweeUserId,
            username: header.display_name || profileData.username || 'User',
            profile_pic: header.profile_pic || profileData.profile_pic,
            company_name: header.company_name || profileData.company_name,
            role: profileData.role || 'contractor',
          },
        };
      }
      return { success: false };
    } catch (error) {
      console.error('review_service.get_reviewee_info error:', error);
      return { success: false };
    }
  },
};
