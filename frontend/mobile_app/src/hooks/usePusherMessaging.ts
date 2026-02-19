/**
 * React Hook for Pusher Real-time Messaging
 * 
 * This hook manages Pusher connection lifecycle and provides
 * real-time message updates for the messaging screen.
 * 
 * Usage:
 * ```typescript
 * const { isConnected, error } = usePusherMessaging(userId, onNewMessage);
 * ```
 */

import { useEffect, useRef, useState } from 'react';
import { initPusher, subscribeToChatChannel, disconnectPusher } from '../config/pusher';
import { storage_service } from '../utils/storage';

interface UsePusherMessagingResult {
  isConnected: boolean;
  isConnecting: boolean;
  error: string | null;
}

/**
 * Hook to manage Pusher connection for real-time messaging
 * 
 * @param userId - Current user's ID
 * @param onNewMessage - Callback when new message arrives
 * @param enabled - Whether to enable Pusher (default: true)
 */
export const usePusherMessaging = (
  userId: number | undefined,
  onNewMessage: (message: any) => void,
  enabled: boolean = true
): UsePusherMessagingResult => {
  const [isConnected, setIsConnected] = useState(false);
  const [isConnecting, setIsConnecting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const pusherRef = useRef<any>(null);
  const channelRef = useRef<any>(null);

  useEffect(() => {
    if (!enabled || !userId) {
      return;
    }

    let mounted = true;

    const setupPusher = async () => {
      try {
        setIsConnecting(true);
        setError(null);

        // Get auth token
        const authToken = await storage_service.get_auth_token();
        if (!authToken) {
          throw new Error('No authentication token found');
        }

        // Initialize Pusher
        const pusher = await initPusher(authToken);
        if (!pusher) {
          throw new Error('Failed to initialize Pusher');
        }

        if (!mounted) {
          disconnectPusher(pusher);
          return;
        }

        pusherRef.current = pusher;

        // Bind connection events
        pusher.connection.bind('connected', () => {
          if (mounted) {
            console.log('✅ Pusher connected');
            setIsConnected(true);
            setIsConnecting(false);
          }
        });

        pusher.connection.bind('disconnected', () => {
          if (mounted) {
            console.log('⚠️ Pusher disconnected');
            setIsConnected(false);
          }
        });

        pusher.connection.bind('error', (err: any) => {
          if (mounted) {
            console.error('❌ Pusher error:', err);
            setError(err.message || 'Connection error');
            setIsConnecting(false);
          }
        });

        // Subscribe to user's chat channel
        const channel = subscribeToChatChannel(pusher, userId, (message) => {
          if (mounted) {
            console.log('📨 New message received via Pusher:', message);
            onNewMessage(message);
          }
        });

        channelRef.current = channel;

      } catch (err: any) {
        if (mounted) {
          console.error('Failed to setup Pusher:', err);
          setError(err.message || 'Failed to connect');
          setIsConnecting(false);
        }
      }
    };

    setupPusher();

    // Cleanup on unmount
    return () => {
      mounted = false;
      if (pusherRef.current) {
        console.log('🔌 Disconnecting Pusher...');
        disconnectPusher(pusherRef.current);
        pusherRef.current = null;
        channelRef.current = null;
      }
    };
  }, [userId, enabled]);

  return { isConnected, isConnecting, error };
};

/**
 * Hook variant that only connects when a conversation is active
 * Useful for optimizing connection usage
 */
export const usePusherForConversation = (
  userId: number | undefined,
  conversationId: number | null,
  onNewMessage: (message: any) => void
): UsePusherMessagingResult => {
  return usePusherMessaging(
    userId,
    (message) => {
      // Only trigger callback if message is for active conversation
      if (conversationId && message.conversation_id === conversationId) {
        onNewMessage(message);
      }
    },
    !!conversationId // Only connect when conversation is active
  );
};
