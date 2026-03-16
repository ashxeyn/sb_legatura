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

export interface FeedOwner {
  owner_id: number;
  user_id: number;
  username?: string;
  profile_pic?: string | null;
  cover_photo?: string | null;
  address?: string | null;
  display_name?: string;
  created_at: string;
}

export interface FeedItem {
  feed_type: 'project' | 'showcase' | 'contractor' | 'owner';
  item_id: number;
  created_at: string;
  data: FeedProject | FeedShowcase | FeedContractor | FeedOwner;
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

export type ReportPostType = 'project' | 'showcase';

export interface ReportAttachment {
  uri: string;
  name: string;
  type: string;  // MIME type
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
    filters?: any,
  ): Promise<ApiResponse<UnifiedFeedResponse>> => {
    try {
      let url = `/api/unified-feed?page=${page}&per_page=${perPage}`;
      
      // Only add type_id if filters exist and have a type_id
      if (filters && filters.type_id) {
        url += `&type_id=${filters.type_id}`;
      }
      
      // Add property_type filter for contractors
      if (filters && filters.property_type) {
        url += `&property_type=${encodeURIComponent(filters.property_type)}`;
      }
      
      // Add location filters
      if (filters && filters.province) {
        url += `&province=${encodeURIComponent(filters.province)}`;
      }
      
      if (filters && filters.city) {
        url += `&city=${encodeURIComponent(filters.city)}`;
      }
      
      // Add contractor-specific filters (for property owners viewing contractors)
      if (filters && filters.min_experience) {
        url += `&min_experience=${filters.min_experience}`;
      }
      
      if (filters && filters.max_experience) {
        url += `&max_experience=${filters.max_experience}`;
      }
      
      if (filters && filters.picab_category) {
        url += `&picab_category=${encodeURIComponent(filters.picab_category)}`;
      }
      
      if (filters && filters.min_completed) {
        url += `&min_completed=${filters.min_completed}`;
      }
      
      // Add budget range filters (for contractors viewing projects)
      if (filters && filters.budget_min) {
        url += `&budget_min=${filters.budget_min}`;
      }
      
      if (filters && filters.budget_max) {
        url += `&budget_max=${filters.budget_max}`;
      }

      const response = await api_request(
        url,
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

  /**
   * Search role-aware unified feed.
   *
   * GET /api/unified-feed/search?search=...&scope=all|users|posts&page=N&per_page=N
   */
  search_unified_feed: async (
    search: string,
    scope: 'all' | 'users' | 'posts' = 'all',
    page: number = 1,
    perPage: number = 20,
  ): Promise<ApiResponse<UnifiedFeedResponse>> => {
    try {
      const params = new URLSearchParams();
      params.append('search', search.trim());
      params.append('scope', scope);
      params.append('page', String(page));
      params.append('per_page', String(perPage));

      const response = await api_request(
        `/api/unified-feed/search?${params.toString()}`,
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
        message: response.data?.message || 'Failed to search feed',
        status: response.status,
      };
    } catch (error) {
      console.error('[post_service] search_unified_feed error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },

  /**
   * Report a project post or showcase post.
   *
   * POST /api/reports  (multipart/form-data when attachments present)
   */
  report_post: async (
    postType: ReportPostType,
    postId: number,
    reason: string,
    details?: string,
    attachments?: ReportAttachment[],
  ): Promise<ApiResponse<any>> => {
    try {
      let body: FormData | string;
      let headers: Record<string, string> = { Accept: 'application/json' };

      if (attachments && attachments.length > 0) {
        const fd = new FormData();
        fd.append('post_type', postType);
        fd.append('post_id', String(postId));
        fd.append('reason', reason);
        if (details) fd.append('details', details);
        attachments.forEach((file) => {
          fd.append('attachments[]', { uri: file.uri, name: file.name, type: file.type } as any);
        });
        body = fd;
        // Let fetch set Content-Type with boundary automatically
      } else {
        body = JSON.stringify({ post_type: postType, post_id: postId, reason, details: details || null });
        headers['Content-Type'] = 'application/json';
      }

      const response = await api_request('/api/reports', { method: 'POST', headers, body });

      return {
        success: response.success ?? false,
        message: response.data?.message || response.message,
        data: response.data?.data || response.data,
        status: response.status,
      };
    } catch (error) {
      console.error('[post_service] report_post error:', error);
      return { success: false, message: error instanceof Error ? error.message : 'Network error', status: 0 };
    }
  },

  /**
   * Report a review.
   *
   * POST /api/review-reports  (multipart/form-data when attachments present)
   */
  report_review: async (
    reviewId: number,
    reason: string,
    details?: string,
    attachments?: ReportAttachment[],
  ): Promise<ApiResponse<any>> => {
    try {
      let body: FormData | string;
      let headers: Record<string, string> = { Accept: 'application/json' };

      if (attachments && attachments.length > 0) {
        const fd = new FormData();
        fd.append('review_id', String(reviewId));
        fd.append('reason', reason);
        if (details) fd.append('details', details);
        attachments.forEach((file) => {
          fd.append('attachments[]', { uri: file.uri, name: file.name, type: file.type } as any);
        });
        body = fd;
      } else {
        body = JSON.stringify({ review_id: reviewId, reason, details: details || null });
        headers['Content-Type'] = 'application/json';
      }

      const response = await api_request('/api/review-reports', { method: 'POST', headers, body });

      return {
        success: response.success ?? false,
        message: response.data?.message || response.message,
        data: response.data?.data || response.data,
        status: response.status,
      };
    } catch (error) {
      console.error('[post_service] report_review error:', error);
      return { success: false, message: error instanceof Error ? error.message : 'Network error', status: 0 };
    }
  },
};
