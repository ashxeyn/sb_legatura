import { api_request } from '../config/api';

interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  status?: number;
}

export interface Notification {
  id: number;
  type: string;
  title: string;
  message: string;
  is_read: boolean;
  priority: 'critical' | 'high' | 'normal';
  reference_type: string | null;
  reference_id: number | null;
  action_url: string | null;
  created_at: string;
}

export interface PaginatedNotifications {
  notifications: Notification[];
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    has_more: boolean;
  };
  unread_count: number;
}

export const notifications_service = {
  /**
   * Fetch paginated notifications for the authenticated user.
   */
  get_notifications: async (page: number = 1, per_page: number = 20): Promise<ApiResponse<PaginatedNotifications>> => {
    try {
      const response = await api_request(`/api/notifications?page=${page}&per_page=${per_page}`, {
        method: 'GET',
      });

      // api_request wraps the JSON body: response.data = { success, data: { notifications, ... } }
      const payload = response.data?.data || response.data;
      if (response.success && payload) {
        return {
          success: true,
          message: 'Notifications loaded',
          data: payload,
        };
      }

      return {
        success: false,
        message: response.message || 'Failed to load notifications',
      };
    } catch (error: any) {
      console.error('Error fetching notifications:', error);
      return {
        success: false,
        message: error.message || 'Failed to load notifications',
      };
    }
  },

  /**
   * Get the unread notification count.
   */
  get_unread_count: async (): Promise<ApiResponse<{ unread_count: number }>> => {
    try {
      const response = await api_request('/api/notifications/unread-count', {
        method: 'GET',
      });

      const payload = response.data?.data || response.data;
      if (response.success && payload) {
        return {
          success: true,
          message: 'OK',
          data: payload,
        };
      }

      return {
        success: false,
        message: response.message || 'Failed to get unread count',
        data: { unread_count: 0 },
      };
    } catch (error: any) {
      console.error('Error fetching unread count:', error);
      return {
        success: false,
        message: error.message || 'Failed to get unread count',
        data: { unread_count: 0 },
      };
    }
  },

  /**
   * Mark a single notification as read.
   */
  mark_as_read: async (notification_id: number): Promise<ApiResponse> => {
    try {
      const response = await api_request(`/api/notifications/${notification_id}/read`, {
        method: 'POST',
      });

      return {
        success: response.success,
        message: response.message || (response.success ? 'Marked as read' : 'Failed'),
      };
    } catch (error: any) {
      console.error('Error marking notification as read:', error);
      return {
        success: false,
        message: error.message || 'Failed to mark as read',
      };
    }
  },

  /**
   * Mark all notifications as read.
   */
  mark_all_as_read: async (): Promise<ApiResponse> => {
    try {
      const response = await api_request('/api/notifications/read-all', {
        method: 'POST',
      });

      return {
        success: response.success,
        message: response.message || (response.success ? 'All marked as read' : 'Failed'),
      };
    } catch (error: any) {
      console.error('Error marking all as read:', error);
      return {
        success: false,
        message: error.message || 'Failed to mark all as read',
      };
    }
  },
};
