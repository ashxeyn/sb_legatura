/**
 * Example: Integrating Pusher into the Messages Screen
 * 
 * This file shows how to add real-time messaging support to messages.tsx
 * Copy the relevant parts into your existing messages.tsx file.
 */

// ============================================================
// Step 1: Add imports at the top of messages.tsx
// ============================================================

import { usePusherMessaging } from '../../hooks/usePusherMessaging';

// ============================================================
// Step 2: Add Pusher state in your component
// ============================================================

export default function MessagesScreen({ userData }: MessagesScreenProps) {
  // ... existing state declarations ...
  
  const userId = userData?.user_id;

  // ============================================================
  // Step 3: Add Pusher hook (add this after your existing state)
  // ============================================================
  
  const handleNewMessage = useCallback((messageData: any) => {
    console.log('📨 Real-time message received:', messageData);
    
    // If message is for the currently active conversation, append it
    if (activeConversation && messageData.conversation_id === activeConversation.conversation_id) {
      setMessages(prev => [...prev, {
        message_id: messageData.message_id,
        conversation_id: messageData.conversation_id,
        content: messageData.content,
        sender: messageData.sender,
        is_read: messageData.is_read,
        is_flagged: false,
        flag_reason: null,
        sent_at: messageData.sent_at,
        sent_at_human: 'Just now',
        attachments: messageData.attachments || [],
      }]);
      
      // Auto-scroll to bottom
      setTimeout(() => {
        chatListRef.current?.scrollToEnd({ animated: true });
      }, 100);
    }
    
    // Reload inbox to update conversation list
    loadInbox();
  }, [activeConversation]);

  const { isConnected, isConnecting, error: pusherError } = usePusherMessaging(
    userId,
    handleNewMessage,
    true // Enable Pusher
  );

  // ============================================================
  // Step 4: (Optional) Show connection status in UI
  // ============================================================
  
  // Add this function to render connection indicator
  const renderConnectionStatus = () => {
    if (isConnecting) {
      return (
        <View style={styles.connectionBadge}>
          <ActivityIndicator size="small" color="#FFF" />
          <Text style={styles.connectionText}>Connecting...</Text>
        </View>
      );
    }
    
    if (pusherError) {
      return (
        <View style={[styles.connectionBadge, { backgroundColor: '#EF4444' }]}>
          <MaterialIcons name="error-outline" size={14} color="#FFF" />
          <Text style={styles.connectionText}>Offline</Text>
        </View>
      );
    }
    
    if (isConnected) {
      return (
        <View style={[styles.connectionBadge, { backgroundColor: '#10B981' }]}>
          <View style={styles.connectedDot} />
          <Text style={styles.connectionText}>Live</Text>
        </View>
      );
    }
    
    return null;
  };

  // ============================================================
  // Step 5: Add connection indicator to your header
  // ============================================================
  
  // In your renderInboxHeader or chat header, add:
  // {renderConnectionStatus()}

  // ============================================================
  // Step 6: Update your existing effects to work with Pusher
  // ============================================================
  
  // Modify your polling effect to be less aggressive when Pusher is connected
  useEffect(() => {
    if (activeConversation && !isConnected) {
      // Only poll if Pusher is NOT connected
      pollRef.current = setInterval(() => {
        silentRefreshChat(activeConversation.conversation_id);
      }, POLL_INTERVAL);
    } else {
      if (pollRef.current) clearInterval(pollRef.current);
    }
    return () => {
      if (pollRef.current) clearInterval(pollRef.current);
    };
  }, [activeConversation, isConnected]); // Add isConnected dependency

  // ============================================================
  // Step 7: Add styles for connection indicator
  // ============================================================
  
  // Add these to your StyleSheet.create():
  /*
  connectionBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#EC7E00',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 4,
  },
  connectionText: {
    color: '#FFFFFF',
    fontSize: 11,
    fontWeight: '600',
  },
  connectedDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: '#FFFFFF',
  },
  */

  // ... rest of your component code ...
}

// ============================================================
// Complete Example: Minimal Messages Screen with Pusher
// ============================================================

/*
import React, { useState, useEffect, useCallback } from 'react';
import { View, Text, FlatList } from 'react-native';
import { messages_service } from '../../services/messages_service';
import { usePusherMessaging } from '../../hooks/usePusherMessaging';

export default function SimpleMessagesScreen({ userData }) {
  const [inbox, setInbox] = useState([]);
  const userId = userData?.user_id;

  // Load inbox
  const loadInbox = async () => {
    const res = await messages_service.get_inbox();
    if (res.success) {
      setInbox(res.data);
    }
  };

  // Handle real-time messages
  const handleNewMessage = useCallback((messageData) => {
    console.log('New message:', messageData);
    loadInbox(); // Refresh inbox
  }, []);

  // Initialize Pusher
  const { isConnected } = usePusherMessaging(userId, handleNewMessage);

  useEffect(() => {
    loadInbox();
  }, []);

  return (
    <View>
      <Text>Connected: {isConnected ? 'Yes' : 'No'}</Text>
      <FlatList
        data={inbox}
        renderItem={({ item }) => (
          <Text>{item.other_user.name}: {item.last_message.content}</Text>
        )}
        keyExtractor={(item) => String(item.conversation_id)}
      />
    </View>
  );
}
*/

// ============================================================
// Alternative: Connect Only to Active Conversation
// ============================================================

/*
// If you want to only enable Pusher when a conversation is open:

import { usePusherForConversation } from '../../hooks/usePusherMessaging';

// Instead of usePusherMessaging, use:
const { isConnected } = usePusherForConversation(
  userId,
  activeConversation?.conversation_id || null,
  handleNewMessage
);
*/

// ============================================================
// Testing Real-time Messages
// ============================================================

/*
1. Open the app on your device/emulator
2. Navigate to Messages screen
3. Check console for: "✅ Pusher connected"
4. Open web dashboard and send a message
5. Message should appear instantly in the app without refresh

Debug logs you should see:
- "✅ Pusher connected"
- "✅ Pusher: Subscribed to private-chat.{userId}"
- "📨 Real-time message received via Pusher: {...}"
- "📨 New message received: {...}"
*/
