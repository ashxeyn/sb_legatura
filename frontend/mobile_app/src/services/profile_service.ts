import { api_request } from '../config/api';

/* ─── Response types matching ProfileService.php ───────────────────── */

export interface ProfileHeader {
  user_id: number;
  display_name: string;
  username: string;
  role: string;
  role_badge: string;
  verification_status: string;
  avg_rating: number;
  total_reviews: number;
  profile_pic: string | null;
  cover_photo: string | null;
  completed_projects: number;
  ongoing_projects: number;
  total_projects: number;
  member_since: string | null;
}

export interface PostImage {
  image_id: number;
  post_id: number;
  file_path: string;
  original_name: string | null;
  sort_order: number;
}

export interface SocialPost {
  post_id: number;
  user_id: number;
  post_type: string;
  content: string;
  title: string | null;
  budget_min: number | null;
  budget_max: number | null;
  location: string | null;
  contractor_type_required: number | null;
  contractor_type_name: string | null;
  property_type: string | null;
  status: string;
  linked_project_id: number | null;
  linked_project_title: string | null;
  linked_project_status: string | null;
  created_at: string;
  updated_at: string;
  images: PostImage[];
  source: 'social';
}

export interface TraditionalProject {
  project_id: number;
  project_title: string;
  project_description: string | null;
  project_location: string | null;
  budget_range_min: number | null;
  budget_range_max: number | null;
  property_type: string | null;
  project_status: string;
  contractor_type_name: string | null;
  post_created_at: string;
}

export interface PostsTab {
  showcase_posts: SocialPost[];
}

export interface ReviewItem {
  review_id: number;
  reviewer_user_id: number;
  reviewee_user_id: number;
  project_id: number;
  rating: number;
  comment: string;
  created_at: string;
  reviewer_name?: string;
  project_title?: string;
}

export interface ReviewStats {
  avg_rating: number;
  total_reviews: number;
  distribution: Record<string, number>;
}

export interface ReviewsData {
  reviews: ReviewItem[];
  stats: ReviewStats;
  pagination: {
    current_page: number;
    per_page: number;
    total: number;
    total_pages: number;
    has_more: boolean;
  };
}

export interface ContractorAbout {
  company_name: string;
  bio: string | null;
  company_description: string | null;
  type_name: string | null;
  years_of_experience: number;
  completed_projects: number;
  services_offered: string | null;
  business_address: string | null;
  company_website: string | null;
  company_email: string | null;
  verification_status: string;
  picab_category: string | null;
  subscription_tier: string;
}

export interface OwnerAbout {
  first_name: string;
  middle_name: string | null;
  last_name: string;
  bio: string | null;
  phone_number: string | null;
  address: string | null;
  date_of_birth: string | null;
  occupation: string | null;
  verification_status: string;
}

export interface AboutTab {
  username: string | null;
  email: string | null;
  user_type: string | null;
  member_since: string | null;
  contractor?: ContractorAbout;
  owner?: OwnerAbout;
}

export interface ProfileData {
  header: ProfileHeader;
  posts: PostsTab;
  reviews: ReviewsData;
  about: AboutTab;
  is_own_profile: boolean;
  user_id: number;
  role: string;
}

interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  status: number;
}

/* ─── Service ──────────────────────────────────────────────────────── */

export const profile_service = {
  /**
   * Fetch the full aggregated profile for a user.
   *
   * Endpoint: GET /api/profile/view/{userId}?role=contractor|owner
   */
  get_profile: async (
    userId: number,
    role?: string,
  ): Promise<ApiResponse<ProfileData>> => {
    try {
      const params = new URLSearchParams();
      if (role) params.append('role', role);
      const qs = params.toString();
      const endpoint = `/api/profile/view/${userId}${qs ? '?' + qs : ''}`;

      const response = await api_request(endpoint, {
        method: 'GET',
        headers: { Accept: 'application/json' },
      });

      // The backend wraps the payload in { success, data }
      if (response.success && response.data?.data) {
        return {
          success: true,
          data: response.data.data as ProfileData,
          status: response.status,
        };
      }

      return {
        success: false,
        message: response.data?.message || response.message || 'Failed to fetch profile',
        status: response.status,
      };
    } catch (error) {
      console.error('[profile_service] get_profile error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },

  /**
   * Fetch paginated reviews for a user.
   *
   * Endpoint: GET /api/reviews/user/{userId}?page=&per_page=
   */
  get_reviews: async (
    userId: number,
    page: number = 1,
    perPage: number = 10,
  ): Promise<ApiResponse<ReviewsData>> => {
    try {
      const endpoint = `/api/reviews/user/${userId}?page=${page}&per_page=${perPage}`;
      const response = await api_request(endpoint, {
        method: 'GET',
        headers: { Accept: 'application/json' },
      });

      if (response.success && response.data?.data) {
        return {
          success: true,
          data: response.data.data as ReviewsData,
          status: response.status,
        };
      }

      return {
        success: false,
        message: response.data?.message || 'Failed to fetch reviews',
        status: response.status,
      };
    } catch (error) {
      console.error('[profile_service] get_reviews error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },

  /**
   * Fetch rating stats for a user.
   *
   * Endpoint: GET /api/reviews/stats/{userId}
   */
  get_review_stats: async (userId: number): Promise<ApiResponse<ReviewStats>> => {
    try {
      const response = await api_request(`/api/reviews/stats/${userId}`, {
        method: 'GET',
        headers: { Accept: 'application/json' },
      });

      if (response.success && response.data?.data) {
        return {
          success: true,
          data: response.data.data as ReviewStats,
          status: response.status,
        };
      }

      return {
        success: false,
        message: response.data?.message || 'Failed to fetch stats',
        status: response.status,
      };
    } catch (error) {
      console.error('[profile_service] get_review_stats error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },

  /**
   * Report a user.
   * Endpoint: POST /api/user-reports
   */
  report_user: async (
    reportedUserId: number,
    reasons: string | string[],
    description?: string,
  ): Promise<ApiResponse<null>> => {
    try {
      const reasonString = Array.isArray(reasons)
        ? (reasons as string[]).map((r) => String(r).trim()).filter(Boolean).join('|')
        : String(reasons || '').trim();

      const payload = {
        reported_user_id: reportedUserId,
        reason: reasonString,
        description: description || null,
      };

      const response = await api_request('/api/user-reports', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(payload),
      });

      if (response.success) {
        return {
          success: true,
          message: response.data?.message || 'Report submitted',
          status: response.status,
        };
      }

      return {
        success: false,
        message: response.data?.message || 'Failed to submit report',
        status: response.status,
      };
    } catch (error) {
      console.error('[profile_service] report_user error:', error);
      return {
        success: false,
        message: error instanceof Error ? error.message : 'Network error',
        status: 0,
      };
    }
  },

  /**
   * Get the current user's most recent report for a specific reported user (if any).
   * Endpoint: GET /api/user-reports/mine
   */
  get_my_report_for_user: async (
    reportedUserId: number,
  ): Promise<ApiResponse<{ reasons: string[]; description?: string | null; report_id?: number; status?: string; created_at?: string }>> => {
    try {
      const response = await api_request('/api/user-reports/mine', {
        method: 'GET',
        headers: { Accept: 'application/json' },
      });

      if (!response.success || !response.data?.data) {
        return { success: false, message: response.data?.message || 'No reports', status: response.status };
      }

      const reports = Array.isArray(response.data.data) ? response.data.data : [];
      const found = (reports as any[]).find((r) => Number(r.reported_user_id) === Number(reportedUserId));
      if (!found) {
        return { success: false, message: 'No previous report', status: response.status };
      }

      const parseReasons = (raw: any): string[] => {
        if (!raw) return [];
        const s = String(raw).trim();
        // Try JSON
        if (s.startsWith('[') || s.startsWith('{')) {
          try {
            const j = JSON.parse(s);
            if (Array.isArray(j)) return j.map((x) => String(x).trim()).filter(Boolean);
          } catch (e) {
            // ignore
          }
        }
        if (s.includes('|')) return s.split('|').map((x) => x.trim()).filter(Boolean);
        if (s.includes(',')) return s.split(',').map((x) => x.trim()).filter(Boolean);
        return [s];
      };

      const reasons = parseReasons(found.reason);

      return {
        success: true,
        data: {
          reasons,
          description: found.description ?? null,
          report_id: found.report_id,
          status: found.status ?? null,
          created_at: found.created_at ?? null,
        },
        status: response.status,
      };
    } catch (error) {
      console.error('[profile_service] get_my_report_for_user error:', error);
      return { success: false, message: error instanceof Error ? error.message : 'Network error', status: 0 };
    }
  },
};
