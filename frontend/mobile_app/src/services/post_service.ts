import { api_request } from '../config/api';

/* ─── Types ────────────────────────────────────────────────────────── */

export interface CompletedProject {
  project_id: number;
  project_title: string;
  project_description: string | null;
  project_location: string | null;
  budget_range_min: number | null;
  budget_range_max: number | null;
  property_type: string | null;
  contractor_type_name: string | null;
  owner_name: string | null;
  completed_at: string | null;
  already_showcased: boolean;
}

export interface CreateShowcasePayload {
  title: string;
  content: string;
  linked_project_id?: number;
  tagged_user_id?: number;
  location?: string;
}

/* ─── Unified Feed Types ───────────────────────────────────────────── */

export interface FeedProject {
  project_id: number;
  project_title: string;
  project_description: string;
  project_location: string;
  budget_range_min: number;
  budget_range_max: number;
  lot_size?: number;
  floor_area?: number;
  property_type: string;
  type_id?: number;
  type_name: string;
  project_status: string;
  project_post_status?: string;
  bidding_deadline?: string;
  created_at: string;
  owner_name?: string;
  owner_profile_pic?: string;
  owner_id?: number;
  owner_user_id?: number;
  bids_count?: number;
  files?: Array<{ file_id?: number; file_type?: string; file_path?: string }>;
}

export interface FeedShowcase {
  post_id: number;
  user_id: number;
  title: string | null;
  content: string;
  location?: string | null;
  display_name: string;
  avatar?: string | null;
  user_type: string;
  username: string;
  company_name?: string | null;
  company_logo?: string | null;
  images: Array<{ image_id: number; file_path: string; sort_order: number }>;
  created_at: string;
}

export interface FeedContractor {
  contractor_id: number;
  company_name: string;
  company_description?: string;
  business_address?: string;
  years_of_experience?: number;
  services_offered?: string;
  completed_projects?: number;
  company_logo?: string | null;
  company_banner?: string | null;
  type_name?: string;
  type_id?: number;
  user_id?: number;
  username?: string;
  profile_pic?: string | null;
  cover_photo?: string | null;
  created_at: string;
}

export interface FeedItem {
  feed_type: 'project' | 'showcase' | 'contractor';
  item_id: number;
  created_at: string;
  data: FeedProject | FeedShowcase | FeedContractor;
}

export interface UnifiedFeedResponse {
  items: FeedItem[];
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    total_pages: number;
    has_more: boolean;
  };
}

interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  status: number;
}

/* ─── Service ──────────────────────────────────────────────────────── */

export const post_service = {
  /**
   * Fetch the current user's completed projects for the showcase form picker.
   *
   * GET /api/posts/completed-projects
   */
  get_completed_projects: async (): Promise<ApiResponse<CompletedProject[]>> => {
    try {
      const response = await api_request('/api/posts/completed-projects', {
        method: 'GET',
        headers: { Accept: 'application/json' },
      });

      if (response.success && response.data?.data) {
        return {
          success: true,
          data: response.data.data as CompletedProject[],
          status: response.status,
        };
      }

      return {
        success: false,
        message: response.data?.message || 'Failed to fetch completed projects',
        status: response.status,
      };
    } catch (error) {
      console.error('[post_service] get_completed_projects error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },

  /**
   * Create a showcase post, optionally linked to a completed project.
   * Supports multipart/form-data for image uploads.
   *
   * POST /api/posts
   */
  create_showcase: async (
    payload: CreateShowcasePayload,
    images: { uri: string; name: string; type: string }[] = [],
  ): Promise<ApiResponse<any>> => {
    try {
      const formData = new FormData();
      formData.append('content', payload.content);
      formData.append('title', payload.title);

      if (payload.linked_project_id) {
        formData.append('linked_project_id', String(payload.linked_project_id));
      }
      if (payload.tagged_user_id) {
        formData.append('tagged_user_id', String(payload.tagged_user_id));
      }
      if (payload.location) {
        formData.append('location', payload.location);
      }

      images.forEach((img) => {
        formData.append('images[]', {
          uri: img.uri,
          name: img.name,
          type: img.type,
        } as any);
      });

      const response = await api_request('/api/posts', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
        },
        body: formData,
      });

      if (response.success) {
        return {
          success: true,
          data: response.data?.data,
          message: response.data?.message || 'Showcase post created.',
          status: response.status,
        };
      }

      return {
        success: false,
        message: response.data?.message || response.message || 'Failed to create showcase post.',
        status: response.status,
      };
    } catch (error) {
      console.error('[post_service] create_showcase error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },

  /**
   * Delete a showcase post.
   *
   * DELETE /api/posts/{id}
   */
  delete_post: async (postId: number): Promise<ApiResponse<void>> => {
    try {
      const response = await api_request(`/api/posts/${postId}`, {
        method: 'DELETE',
        headers: { Accept: 'application/json' },
      });

      return {
        success: response.success,
        message: response.data?.message || (response.success ? 'Post deleted.' : 'Failed to delete post.'),
        status: response.status,
      };
    } catch (error) {
      console.error('[post_service] delete_post error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },

  /**
   * Fetch the unified feed (bidding projects + showcase posts merged).
   *
   * GET /api/unified-feed?page=N&per_page=N
   */
  get_unified_feed: async (
    page: number = 1,
    perPage: number = 20,
  ): Promise<ApiResponse<UnifiedFeedResponse>> => {
    try {
      const response = await api_request(
        `/api/unified-feed?page=${page}&per_page=${perPage}`,
        {
          method: 'GET',
          headers: { Accept: 'application/json' },
        },
      );

      if (response.success && response.data?.data) {
        return {
          success: true,
          data: response.data.data as UnifiedFeedResponse,
          status: response.status,
        };
      }

      return {
        success: false,
        message: response.data?.message || 'Failed to fetch feed',
        status: response.status,
      };
    } catch (error) {
      console.error('[post_service] get_unified_feed error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },
};
