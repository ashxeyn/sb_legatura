import { api_request } from '../config/api';

/* ─── Types ────────────────────────────────────────────────────────── */

interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  status: number;
}

export interface HighlightedPost {
  post_id: number;
  user_id: number;
  title: string | null;
  content: string | null;
  is_highlighted: boolean | number;
  highlighted_at: string | null;
  [key: string]: any;
}

export interface HighlightsData {
  highlights: HighlightedPost[];
  count: number;
  max: number;
}

/* ─── Service ──────────────────────────────────────────────────────── */

export const highlightService = {
  /**
   * Highlight (pin) a showcase post.
   * POST /api/posts/{postId}/highlight
   */
  highlightPost: async (postId: number): Promise<ApiResponse> => {
    try {
      const response = await api_request(`/api/posts/${postId}/highlight`, {
        method: 'POST',
        headers: { Accept: 'application/json' },
      });

      return {
        success: response.success ?? false,
        message: response.data?.message || response.message,
        data: response.data,
        status: response.status,
      };
    } catch (error) {
      console.error('[highlightService] highlightPost error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },

  /**
   * Remove highlight from a showcase post.
   * DELETE /api/posts/{postId}/highlight
   */
  unhighlightPost: async (postId: number): Promise<ApiResponse> => {
    try {
      const response = await api_request(`/api/posts/${postId}/highlight`, {
        method: 'DELETE',
        headers: { Accept: 'application/json' },
      });

      return {
        success: response.success ?? false,
        message: response.data?.message || response.message,
        data: response.data,
        status: response.status,
      };
    } catch (error) {
      console.error('[highlightService] unhighlightPost error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },

  /**
   * Get all highlighted posts for the current user.
   * GET /api/posts/highlights
   */
  getHighlights: async (): Promise<ApiResponse<HighlightsData>> => {
    try {
      const response = await api_request('/api/posts/highlights', {
        method: 'GET',
        headers: { Accept: 'application/json' },
      });

      return {
        success: response.success ?? false,
        message: response.data?.message || response.message,
        data: response.data?.data || response.data,
        status: response.status,
      };
    } catch (error) {
      console.error('[highlightService] getHighlights error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },
};
