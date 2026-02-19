import { api_request, api_config } from '../config/api';

/* =====================================================================
 * Types
 * ===================================================================== */

export interface UserInfo {
  id: number;
  name: string;
  type: string;
  avatar: string | null;
  online: boolean;
}

export interface Attachment {
  attachment_id: number;
  file_name: string;
  file_type: string;
  file_url: string;
  is_image: boolean;
}

export interface InboxMessage {
  content: string;
  sent_at: string;
  sent_at_timestamp: string;
}

export interface InboxItem {
  conversation_id: number;
  other_user: UserInfo;
  last_message: InboxMessage;
  unread_count: number;
  is_flagged: boolean;
  status: 'active' | 'suspended';
  is_suspended: boolean;
  suspended_until: string | null;
  reason: string | null;
}

export interface ChatMessage {
  message_id: number;
  conversation_id: number;
  content: string;
  sender: UserInfo;
  is_read: boolean;
  is_flagged: boolean;
  flag_reason: string | null;
  sent_at_human: string;
  sent_at: string;
  attachments: Attachment[];
}

export interface ConversationDetail {
  conversation_id: number;
  messages: ChatMessage[];
  count: number;
  conversation: {
    sender_id: number;
    receiver_id: number;
  };
}

export interface StoredMessage {
  message_id: number;
  conversation_id: number;
  content: string;
  sender: UserInfo;
  receiver: UserInfo;
  attachments: Attachment[];
  is_read: boolean;
  is_flagged: boolean;
  flag_reason: string | null;
  sent_at: string;
}

export interface SearchResult {
  message_id: number;
  conversation_id: number;
  from_sender: boolean;
  content: string;
  is_read: number;
  is_flagged: number;
  flag_reason: string | null;
  created_at: string;
  sender_id: number;
  receiver_id: number;
}

interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  status?: number;
}

/* =====================================================================
 * Service
 * ===================================================================== */

export const messages_service = {

  /**
   * Fetch the inbox (conversation list with latest message preview).
   * GET /api/messages
   */
  get_inbox: async (): Promise<ApiResponse<InboxItem[]>> => {
    try {
      const response = await api_request('/api/messages', { method: 'GET' });
      const payload = response.data;

      if (response.success && payload?.success) {
        return {
          success: true,
          message: 'Inbox loaded',
          data: payload.data || [],
        };
      }

      return {
        success: false,
        message: payload?.message || response.message || 'Failed to load inbox',
      };
    } catch (error: any) {
      console.error('Error fetching inbox:', error);
      return { success: false, message: error.message || 'Failed to load inbox' };
    }
  },

  /**
   * Fetch full conversation message history.
   * GET /api/messages/{conversationId}
   */
  get_conversation: async (conversationId: number): Promise<ApiResponse<ConversationDetail>> => {
    try {
      const response = await api_request(`/api/messages/${conversationId}`, { method: 'GET' });
      const payload = response.data;

      if (response.success && payload?.success) {
        return {
          success: true,
          message: 'Conversation loaded',
          data: payload.data,
        };
      }

      return {
        success: false,
        message: payload?.message || response.message || 'Failed to load conversation',
      };
    } catch (error: any) {
      console.error('Error fetching conversation:', error);
      return { success: false, message: error.message || 'Failed to load conversation' };
    }
  },

  /**
   * Send a new message (text and/or attachments).
   * POST /api/messages
   */
  send_message: async (
    receiverId: number,
    content: string,
    conversationId?: number,
    attachments?: any[],
  ): Promise<ApiResponse<StoredMessage>> => {
    try {
      let body: any;
      let headers: Record<string, string> = {};

      if (attachments && attachments.length > 0) {
        // Use FormData for file uploads
        const formData = new FormData();
        formData.append('receiver_id', String(receiverId));
        if (content) formData.append('content', content);
        if (conversationId) formData.append('conversation_id', String(conversationId));

        attachments.forEach((file, index) => {
          formData.append(`attachments[${index}]`, {
            uri: file.uri,
            type: file.mimeType || file.type || 'application/octet-stream',
            name: file.name || `attachment_${index}`,
          } as any);
        });

        body = formData;
        // Don't set Content-Type — fetch will set multipart/form-data with boundary
      } else {
        body = JSON.stringify({
          receiver_id: receiverId,
          content,
          ...(conversationId ? { conversation_id: conversationId } : {}),
        });
      }

      const response = await api_request('/api/messages', {
        method: 'POST',
        body,
        ...(attachments && attachments.length > 0 ? {} : {}),
      });

      const payload = response.data;

      if (response.success && payload?.success) {
        return {
          success: true,
          message: 'Message sent',
          data: payload.data,
        };
      }

      return {
        success: false,
        message: payload?.message || response.message || 'Failed to send message',
      };
    } catch (error: any) {
      console.error('Error sending message:', error);
      return { success: false, message: error.message || 'Failed to send message' };
    }
  },

  /**
   * Search messages by query.
   * GET /api/messages/search?query=...
   */
  search_messages: async (query: string): Promise<ApiResponse<SearchResult[]>> => {
    try {
      const response = await api_request(
        `/api/messages/search?query=${encodeURIComponent(query)}`,
        { method: 'GET' },
      );
      const payload = response.data;

      if (response.success && payload?.success) {
        return {
          success: true,
          message: 'Search completed',
          data: payload.data || [],
        };
      }

      return {
        success: false,
        message: payload?.message || response.message || 'Search failed',
      };
    } catch (error: any) {
      console.error('Error searching messages:', error);
      return { success: false, message: error.message || 'Search failed' };
    }
  },

  /**
   * Get available users to message.
   * GET /api/messages/users
   */
  get_available_users: async (): Promise<ApiResponse<UserInfo[]>> => {
    try {
      const response = await api_request('/api/messages/users', { method: 'GET' });
      const payload = response.data;

      if (response.success && payload?.success) {
        return {
          success: true,
          message: 'Users loaded',
          data: payload.data || [],
        };
      }

      return {
        success: false,
        message: payload?.message || response.message || 'Failed to load users',
      };
    } catch (error: any) {
      console.error('Error fetching available users:', error);
      return { success: false, message: error.message || 'Failed to load users' };
    }
  },

  /**
   * Report a message.
   * POST /api/messages/report
   */
  report_message: async (messageId: number, reason: string): Promise<ApiResponse> => {
    try {
      const response = await api_request('/api/messages/report', {
        method: 'POST',
        body: JSON.stringify({ message_id: messageId, reason }),
      });
      const payload = response.data;

      if (response.success && payload?.success) {
        return { success: true, message: payload.message || 'Message reported' };
      }

      return {
        success: false,
        message: payload?.message || response.message || 'Failed to report message',
      };
    } catch (error: any) {
      console.error('Error reporting message:', error);
      return { success: false, message: error.message || 'Failed to report message' };
    }
  },

  /**
   * Get messaging stats (unread count, etc.).
   * GET /api/messages/stats
   */
  get_stats: async (): Promise<ApiResponse<{ totalSuspended: number; activeConversations: number; flaggedMessages: number }>> => {
    try {
      const response = await api_request('/api/messages/stats', { method: 'GET' });
      const payload = response.data;

      if (response.success && payload?.success) {
        return {
          success: true,
          message: 'Stats loaded',
          data: payload.data,
        };
      }

      return {
        success: false,
        message: payload?.message || response.message || 'Failed to load stats',
      };
    } catch (error: any) {
      console.error('Error fetching stats:', error);
      return { success: false, message: error.message || 'Failed to load stats' };
    }
  },
};
