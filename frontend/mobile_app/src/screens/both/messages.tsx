// @ts-nocheck
import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  FlatList,
  TextInput,
  Image,
  ActivityIndicator,
  RefreshControl,
  Platform,
  KeyboardAvoidingView,
  Alert,
  Modal,
  Keyboard,
  Linking,
} from 'react-native';
import { StatusBar } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons, Feather } from '@expo/vector-icons';
import * as DocumentPicker from 'expo-document-picker';
import {
  messages_service,
  InboxItem,
  ChatMessage,
  ConversationDetail,
  UserInfo,
  Attachment,
} from '../../services/messages_service';
import { api_config } from '../../config/api';

/* =====================================================================
 * Constants
 * ===================================================================== */

const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
  secondary: '#1A1A2E',
  success: '#10B981',
  error: '#EF4444',
  warning: '#F59E0B',
  background: '#F5F5F5',
  surface: '#FFFFFF',
  text: '#333333',
  textSecondary: '#666666',
  textMuted: '#999999',
  border: '#E5E5E5',
  borderLight: '#F0F0F0',
  unreadBg: '#FFF9E6',
  ownBubble: '#EC7E00',
  otherBubble: '#FFFFFF',
  suspended: '#FEE2E2',
};

const AVATAR_COLORS = [
  '#1877f2', '#42b883', '#e74c3c', '#f39c12',
  '#9b59b6', '#1abc9c', '#e67e22', '#3498db',
];

const POLL_INTERVAL = 8000; // 8 seconds

/* =====================================================================
 * Props
 * ===================================================================== */

interface MessagesScreenProps {
  userData?: {
    user_id?: number;
    username?: string;
    email?: string;
    profile_pic?: string;
    user_type?: string;
  };
}

/* =====================================================================
 * Component
 * ===================================================================== */

export default function MessagesScreen({ userData }: MessagesScreenProps) {
  const insets = useSafeAreaInsets();

  // ─── Inbox state ───────────────────────────────────────────────
  const [inbox, setInbox] = useState<InboxItem[]>([]);
  const [filteredInbox, setFilteredInbox] = useState<InboxItem[]>([]);
  const [inboxFilter, setInboxFilter] = useState<'all' | 'unread'>('all');
  const [inboxLoading, setInboxLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [isSearching, setIsSearching] = useState(false);

  // ─── Chat state ────────────────────────────────────────────────
  const [activeConversation, setActiveConversation] = useState<InboxItem | null>(null);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [chatLoading, setChatLoading] = useState(false);
  const [messageText, setMessageText] = useState('');
  const [sending, setSending] = useState(false);
  const [pendingAttachments, setPendingAttachments] = useState<any[]>([]);
  const chatListRef = useRef<FlatList>(null);

  // ─── Compose modal state ──────────────────────────────────────
  const [composeVisible, setComposeVisible] = useState(false);
  const [availableUsers, setAvailableUsers] = useState<UserInfo[]>([]);
  const [usersLoading, setUsersLoading] = useState(false);
  const [composeSearch, setComposeSearch] = useState('');
  const [selectedRecipient, setSelectedRecipient] = useState<UserInfo | null>(null);
  const [composeText, setComposeText] = useState('');
  const [composeSending, setComposeSending] = useState(false);

  // ─── Report modal state ───────────────────────────────────────
  const [reportModalVisible, setReportModalVisible] = useState(false);
  const [reportMessageId, setReportMessageId] = useState<number | null>(null);
  const [reportReason, setReportReason] = useState('');

  // ─── Polling ref ──────────────────────────────────────────────
  const pollRef = useRef<NodeJS.Timer | null>(null);

  const userId = userData?.user_id;

  /* =====================================================================
   * Effects
   * ===================================================================== */

  // Load inbox on mount
  useEffect(() => {
    loadInbox();
    return () => {
      if (pollRef.current) clearInterval(pollRef.current);
    };
  }, []);

  // Poll for new messages when in chat view
  useEffect(() => {
    if (activeConversation) {
      pollRef.current = setInterval(() => {
        silentRefreshChat(activeConversation.conversation_id);
      }, POLL_INTERVAL);
    } else {
      if (pollRef.current) clearInterval(pollRef.current);
    }
    return () => {
      if (pollRef.current) clearInterval(pollRef.current);
    };
  }, [activeConversation]);

  // Filter inbox when filter or search changes
  useEffect(() => {
    let filtered = [...inbox];
    if (inboxFilter === 'unread') {
      filtered = filtered.filter(c => c.unread_count > 0);
    }
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      filtered = filtered.filter(c =>
        c.other_user.name.toLowerCase().includes(q) ||
        (c.last_message?.content || '').toLowerCase().includes(q)
      );
    }
    setFilteredInbox(filtered);
  }, [inbox, inboxFilter, searchQuery]);

  /* =====================================================================
   * Data fetching
   * ===================================================================== */

  const loadInbox = async () => {
    try {
      setInboxLoading(true);
      const res = await messages_service.get_inbox();
      if (res.success && res.data) {
        setInbox(res.data);
      } else {
        setInbox([]);
      }
    } catch (err) {
      console.error('loadInbox error:', err);
    } finally {
      setInboxLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadInbox();
    setRefreshing(false);
  };

  const openConversation = async (item: InboxItem) => {
    setActiveConversation(item);
    setChatLoading(true);
    try {
      const res = await messages_service.get_conversation(item.conversation_id);
      if (res.success && res.data) {
        setMessages(res.data.messages || []);
        // Update unread count in inbox
        setInbox(prev =>
          prev.map(c =>
            c.conversation_id === item.conversation_id ? { ...c, unread_count: 0 } : c,
          ),
        );
      }
    } catch (err) {
      console.error('openConversation error:', err);
    } finally {
      setChatLoading(false);
      setTimeout(() => chatListRef.current?.scrollToEnd({ animated: false }), 200);
    }
  };

  const silentRefreshChat = async (conversationId: number) => {
    try {
      const res = await messages_service.get_conversation(conversationId);
      if (res.success && res.data) {
        setMessages(res.data.messages || []);
      }
    } catch {}
  };

  /* =====================================================================
   * Actions
   * ===================================================================== */

  const handleSendMessage = async () => {
    if ((!messageText.trim() && pendingAttachments.length === 0) || !activeConversation) return;

    const receiverId =
      activeConversation.other_user.id;

    setSending(true);
    Keyboard.dismiss();

    try {
      const res = await messages_service.send_message(
        receiverId,
        messageText.trim(),
        activeConversation.conversation_id,
        pendingAttachments.length > 0 ? pendingAttachments : undefined,
      );

      if (res.success && res.data) {
        // Append sent message to chat, converting StoredMessage → ChatMessage shape
        const sent = res.data;
        const newMsg: ChatMessage = {
          message_id: sent.message_id,
          conversation_id: sent.conversation_id,
          content: sent.content,
          sender: sent.sender,
          is_read: sent.is_read,
          is_flagged: sent.is_flagged,
          flag_reason: sent.flag_reason,
          sent_at_human: 'Just now',
          sent_at: sent.sent_at,
          attachments: sent.attachments || [],
        };
        setMessages(prev => [...prev, newMsg]);
        setMessageText('');
        setPendingAttachments([]);

        // Update inbox preview
        setInbox(prev =>
          prev.map(c =>
            c.conversation_id === activeConversation.conversation_id
              ? {
                  ...c,
                  last_message: {
                    content: sent.content || 'Attachment',
                    sent_at: 'Just now',
                    sent_at_timestamp: sent.sent_at,
                  },
                }
              : c,
          ),
        );

        setTimeout(() => chatListRef.current?.scrollToEnd({ animated: true }), 100);
      } else {
        Alert.alert('Error', res.message || 'Failed to send message');
      }
    } catch (err: any) {
      Alert.alert('Error', err.message || 'Failed to send message');
    } finally {
      setSending(false);
    }
  };

  const handlePickAttachment = async () => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ['image/*', 'application/pdf', 'application/msword',
               'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
               'text/plain'],
        multiple: true,
      });

      if (!result.canceled && result.assets) {
        const newFiles = result.assets.slice(0, 5 - pendingAttachments.length);
        setPendingAttachments(prev => [...prev, ...newFiles].slice(0, 5));
      }
    } catch (err) {
      console.error('Attachment pick error:', err);
    }
  };

  const removeAttachment = (index: number) => {
    setPendingAttachments(prev => prev.filter((_, i) => i !== index));
  };

  /* =====================================================================
   * Compose New Message
   * ===================================================================== */

  const openCompose = async () => {
    setComposeVisible(true);
    setUsersLoading(true);
    try {
      const res = await messages_service.get_available_users();
      if (res.success && res.data) {
        setAvailableUsers(res.data);
      }
    } catch {} finally {
      setUsersLoading(false);
    }
  };

  const handleComposeSend = async () => {
    if (!selectedRecipient || !composeText.trim()) return;

    setComposeSending(true);
    try {
      const res = await messages_service.send_message(selectedRecipient.id, composeText.trim());
      if (res.success && res.data) {
        setComposeVisible(false);
        setSelectedRecipient(null);
        setComposeText('');
        setComposeSearch('');
        // Refresh inbox and open the conversation
        await loadInbox();
        const convId = res.data.conversation_id;
        const inboxItem: InboxItem = {
          conversation_id: convId,
          other_user: res.data.receiver,
          last_message: { content: res.data.content, sent_at: 'Just now', sent_at_timestamp: res.data.sent_at },
          unread_count: 0,
          is_flagged: false,
          status: 'active',
          is_suspended: false,
          suspended_until: null,
          reason: null,
        };
        openConversation(inboxItem);
      } else {
        Alert.alert('Error', res.message || 'Failed to send message');
      }
    } catch (err: any) {
      Alert.alert('Error', err.message || 'Failed to send');
    } finally {
      setComposeSending(false);
    }
  };

  /* =====================================================================
   * Report
   * ===================================================================== */

  const handleReport = async () => {
    if (!reportMessageId || !reportReason.trim()) return;
    const res = await messages_service.report_message(reportMessageId, reportReason.trim());
    Alert.alert(res.success ? 'Reported' : 'Error', res.message);
    setReportModalVisible(false);
    setReportMessageId(null);
    setReportReason('');
  };

  /* =====================================================================
   * Helpers
   * ===================================================================== */

  const getInitials = (name: string) => (name ? name.substring(0, 2).toUpperCase() : 'U');
  const getAvatarColor = (id: number) => AVATAR_COLORS[id % AVATAR_COLORS.length];
  const getAvatarUri = (avatar: string | null) => {
    if (!avatar) return null;
    if (avatar.startsWith('http')) return avatar;
    return `${api_config.base_url}/storage/${avatar}`;
  };

  const totalUnread = inbox.reduce((sum, c) => sum + c.unread_count, 0);

  /* =====================================================================
   * Render: Conversation List Item
   * ===================================================================== */

  const renderConversationItem = ({ item }: { item: InboxItem }) => {
    const hasUnread = item.unread_count > 0;
    const isSuspended = item.is_suspended;
    const avatarUri = getAvatarUri(item.other_user.avatar);
    const color = getAvatarColor(item.other_user.id);

    return (
      <TouchableOpacity
        style={[
          styles.conversationItem,
          hasUnread && styles.conversationItemUnread,
          isSuspended && styles.conversationItemSuspended,
        ]}
        onPress={() => openConversation(item)}
        activeOpacity={0.7}
      >
        {/* Avatar */}
        <View style={styles.conversationAvatar}>
          {avatarUri ? (
            <Image source={{ uri: avatarUri }} style={styles.avatarImage} />
          ) : (
            <View style={[styles.avatarPlaceholder, { backgroundColor: color }]}>
              <Text style={styles.avatarText}>{getInitials(item.other_user.name)}</Text>
            </View>
          )}
          {hasUnread && <View style={styles.onlineDot} />}
        </View>

        {/* Content */}
        <View style={styles.conversationContent}>
          <View style={styles.conversationHeader}>
            <Text
              style={[styles.conversationName, hasUnread && styles.conversationNameBold]}
              numberOfLines={1}
            >
              {item.other_user.name}
            </Text>
            <Text style={styles.conversationTime}>{item.last_message?.sent_at || ''}</Text>
          </View>

          {/* Role badge */}
          <View style={styles.roleBadgeRow}>
            <View style={[styles.roleBadge, item.other_user.type === 'contractor' ? styles.roleBadgeContractor : styles.roleBadgeOwner]}>
              <Text style={styles.roleBadgeText}>
                {item.other_user.type === 'contractor' ? 'Contractor' : item.other_user.type === 'property_owner' ? 'Owner' : item.other_user.type}
              </Text>
            </View>
            {isSuspended && (
              <View style={styles.suspendedBadge}>
                <Ionicons name="ban" size={10} color={COLORS.error} />
                <Text style={styles.suspendedBadgeText}>Suspended</Text>
              </View>
            )}
          </View>

          <Text
            style={[styles.lastMessage, hasUnread && styles.lastMessageBold]}
            numberOfLines={1}
          >
            {item.last_message?.content || 'No messages yet'}
          </Text>
        </View>

        {/* Unread badge */}
        {hasUnread && (
          <View style={styles.unreadCountBadge}>
            <Text style={styles.unreadCountText}>
              {item.unread_count > 99 ? '99+' : item.unread_count}
            </Text>
          </View>
        )}
      </TouchableOpacity>
    );
  };

  /* =====================================================================
   * Render: Chat Message Bubble
   * ===================================================================== */

  const renderMessageBubble = ({ item }: { item: ChatMessage }) => {
    const isOwn = item.sender.id === userId;
    const avatarUri = getAvatarUri(item.sender.avatar);
    const color = getAvatarColor(item.sender.id);

    return (
      <View style={[styles.messageBubble, isOwn ? styles.messageBubbleOwn : styles.messageBubbleOther]}>
        {/* Other user avatar */}
        {!isOwn && (
          <View style={styles.messageAvatarWrap}>
            {avatarUri ? (
              <Image source={{ uri: avatarUri }} style={styles.messageAvatarImage} />
            ) : (
              <View style={[styles.messageAvatarPlaceholder, { backgroundColor: color }]}>
                <Text style={styles.messageAvatarText}>{getInitials(item.sender.name)}</Text>
              </View>
            )}
          </View>
        )}

        <TouchableOpacity
          activeOpacity={0.85}
          onLongPress={() => {
            if (!isOwn) {
              setReportMessageId(item.message_id);
              setReportModalVisible(true);
            }
          }}
          style={[
            styles.messageContent,
            isOwn ? styles.messageContentOwn : styles.messageContentOther,
            item.is_flagged && styles.messageContentFlagged,
          ]}
        >
          {!isOwn && (
            <Text style={styles.messageSenderName}>{item.sender.name}</Text>
          )}

          {item.content ? (
            <Text style={[styles.messageText, isOwn ? styles.messageTextOwn : styles.messageTextOther]}>
              {item.content}
            </Text>
          ) : null}

          {/* Attachments */}
          {item.attachments && item.attachments.length > 0 && (
            <View style={styles.attachmentsContainer}>
              {item.attachments.map(att => (
                <TouchableOpacity
                  key={att.attachment_id}
                  style={styles.attachmentItem}
                  onPress={() => {
                    const url = att.file_url?.startsWith('http')
                      ? att.file_url
                      : `${api_config.base_url}${att.file_url}`;
                    Linking.openURL(url).catch(() => {});
                  }}
                >
                  {att.is_image ? (
                    <Image
                      source={{
                        uri: att.file_url?.startsWith('http')
                          ? att.file_url
                          : `${api_config.base_url}${att.file_url}`,
                      }}
                      style={styles.attachmentImage}
                      resizeMode="cover"
                    />
                  ) : (
                    <View style={styles.attachmentFile}>
                      <Ionicons name="document-outline" size={18} color={isOwn ? '#FFF' : COLORS.primary} />
                      <Text
                        style={[styles.attachmentFileName, isOwn && { color: '#FFF' }]}
                        numberOfLines={1}
                      >
                        {att.file_name}
                      </Text>
                    </View>
                  )}
                </TouchableOpacity>
              ))}
            </View>
          )}

          {/* Flagged indicator */}
          {item.is_flagged && (
            <View style={styles.flaggedRow}>
              <Ionicons name="flag" size={10} color={COLORS.error} />
              <Text style={styles.flaggedText}>Flagged</Text>
            </View>
          )}

          <Text style={[styles.messageTime, isOwn ? styles.messageTimeOwn : styles.messageTimeOther]}>
            {item.sent_at_human || item.sent_at}
            {isOwn && (
              <Text> {item.is_read ? '✓✓' : '✓'}</Text>
            )}
          </Text>
        </TouchableOpacity>
      </View>
    );
  };

  /* =====================================================================
   * Render: Chat View
   * ===================================================================== */

  if (activeConversation) {
    const otherUser = activeConversation.other_user;
    const avatarUri = getAvatarUri(otherUser.avatar);
    const color = getAvatarColor(otherUser.id);
    const isSuspended = activeConversation.is_suspended;

    return (
      <KeyboardAvoidingView
        style={styles.container}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : 0}
      >
        <StatusBar hidden={true} />

        {/* Chat Header */}
        <View style={styles.chatHeader}>
          <TouchableOpacity
            style={styles.backButton}
            onPress={() => {
              setActiveConversation(null);
              setMessages([]);
              setPendingAttachments([]);
              loadInbox(); // refresh inbox unread counts
            }}
          >
            <Ionicons name="arrow-back" size={24} color={COLORS.text} />
          </TouchableOpacity>

          <View style={styles.chatHeaderInfo}>
            {avatarUri ? (
              <Image source={{ uri: avatarUri }} style={styles.chatHeaderAvatar} />
            ) : (
              <View style={[styles.chatHeaderAvatarPlaceholder, { backgroundColor: color }]}>
                <Text style={styles.chatHeaderAvatarText}>{getInitials(otherUser.name)}</Text>
              </View>
            )}
            <View style={{ flex: 1 }}>
              <Text style={styles.chatHeaderName} numberOfLines={1}>{otherUser.name}</Text>
              <Text style={styles.chatHeaderRole}>
                {otherUser.type === 'contractor' ? 'Contractor' : otherUser.type === 'property_owner' ? 'Property Owner' : otherUser.type}
              </Text>
            </View>
          </View>

          {/* More options placeholder */}
          <TouchableOpacity style={styles.moreButton}>
            <Ionicons name="ellipsis-vertical" size={22} color={COLORS.text} />
          </TouchableOpacity>
        </View>

        {/* Suspended banner */}
        {isSuspended && (
          <View style={styles.suspendedBanner}>
            <Ionicons name="ban" size={16} color={COLORS.error} />
            <Text style={styles.suspendedBannerText}>
              This conversation is suspended{activeConversation.suspended_until ? ` until ${activeConversation.suspended_until}` : ''}.
              {activeConversation.reason ? ` Reason: ${activeConversation.reason}` : ''}
            </Text>
          </View>
        )}

        {/* Messages List */}
        {chatLoading ? (
          <View style={styles.chatLoadingContainer}>
            <ActivityIndicator size="large" color={COLORS.primary} />
          </View>
        ) : (
          <FlatList
            ref={chatListRef}
            data={messages}
            keyExtractor={item => String(item.message_id)}
            renderItem={renderMessageBubble}
            contentContainerStyle={styles.messagesListContent}
            showsVerticalScrollIndicator={false}
            onContentSizeChange={() => chatListRef.current?.scrollToEnd({ animated: false })}
            ListEmptyComponent={
              <View style={styles.emptyChatContainer}>
                <Ionicons name="chatbubble-outline" size={48} color="#CCC" />
                <Text style={styles.emptyChatText}>No messages yet. Say hello!</Text>
              </View>
            }
          />
        )}

        {/* Pending attachments preview */}
        {pendingAttachments.length > 0 && (
          <View style={styles.pendingAttachments}>
            {pendingAttachments.map((file, index) => (
              <View key={index} style={styles.pendingFile}>
                <Ionicons name="document" size={14} color={COLORS.primary} />
                <Text style={styles.pendingFileName} numberOfLines={1}>{file.name || 'File'}</Text>
                <TouchableOpacity onPress={() => removeAttachment(index)}>
                  <Ionicons name="close-circle" size={16} color={COLORS.error} />
                </TouchableOpacity>
              </View>
            ))}
          </View>
        )}

        {/* Message Input */}
        {!isSuspended ? (
          <View style={styles.messageInputContainer}>
            <TouchableOpacity style={styles.attachButton} onPress={handlePickAttachment}>
              <Ionicons name="attach" size={24} color={COLORS.textSecondary} />
            </TouchableOpacity>

            <TextInput
              style={styles.messageInput}
              placeholder="Type a message..."
              placeholderTextColor={COLORS.textMuted}
              value={messageText}
              onChangeText={setMessageText}
              multiline
              maxLength={5000}
            />

            <TouchableOpacity
              style={[
                styles.sendButton,
                (!messageText.trim() && pendingAttachments.length === 0 || sending) && styles.sendButtonDisabled,
              ]}
              onPress={handleSendMessage}
              disabled={(!messageText.trim() && pendingAttachments.length === 0) || sending}
            >
              {sending ? (
                <ActivityIndicator size="small" color="#FFF" />
              ) : (
                <Ionicons name="send" size={20} color="#FFF" />
              )}
            </TouchableOpacity>
          </View>
        ) : (
          <View style={styles.suspendedInputBar}>
            <Ionicons name="lock-closed" size={18} color={COLORS.textMuted} />
            <Text style={styles.suspendedInputText}>Messaging is suspended</Text>
          </View>
        )}

        {/* Report Modal */}
        <Modal visible={reportModalVisible} transparent animationType="fade">
          <View style={styles.modalOverlay}>
            <View style={styles.modalBox}>
              <Text style={styles.modalTitle}>Report Message</Text>
              <Text style={styles.modalDesc}>Why are you reporting this message?</Text>
              <TextInput
                style={styles.modalInput}
                placeholder="Enter reason..."
                placeholderTextColor={COLORS.textMuted}
                value={reportReason}
                onChangeText={setReportReason}
                multiline
                maxLength={500}
              />
              <View style={styles.modalButtons}>
                <TouchableOpacity
                  style={styles.modalCancelBtn}
                  onPress={() => { setReportModalVisible(false); setReportReason(''); }}
                >
                  <Text style={styles.modalCancelText}>Cancel</Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={[styles.modalConfirmBtn, !reportReason.trim() && { opacity: 0.5 }]}
                  onPress={handleReport}
                  disabled={!reportReason.trim()}
                >
                  <Text style={styles.modalConfirmText}>Report</Text>
                </TouchableOpacity>
              </View>
            </View>
          </View>
        </Modal>
      </KeyboardAvoidingView>
    );
  }

  /* =====================================================================
   * Render: Conversation List (Inbox)
   * ===================================================================== */

  if (inboxLoading) {
    return (
      <View style={styles.container}>
        <StatusBar hidden={true} />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Loading conversations...</Text>
        </View>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <StatusBar hidden={true} />

      {/* Header */}
      <View style={styles.header}>
        <View style={styles.headerLeft}>
          <Text style={styles.headerTitle}>Messages</Text>
          {totalUnread > 0 && (
            <View style={styles.headerBadge}>
              <Text style={styles.headerBadgeText}>{totalUnread > 99 ? '99+' : totalUnread}</Text>
            </View>
          )}
        </View>
        <View style={styles.headerRight}>
          <TouchableOpacity style={styles.headerIconBtn} onPress={() => setIsSearching(!isSearching)}>
            <Ionicons name={isSearching ? 'close' : 'search-outline'} size={22} color={COLORS.text} />
          </TouchableOpacity>
          <TouchableOpacity style={styles.headerIconBtn} onPress={openCompose}>
            <Ionicons name="create-outline" size={22} color={COLORS.primary} />
          </TouchableOpacity>
        </View>
      </View>

      {/* Search bar */}
      {isSearching && (
        <View style={styles.searchBar}>
          <Ionicons name="search" size={18} color={COLORS.textMuted} />
          <TextInput
            style={styles.searchInput}
            placeholder="Search conversations..."
            placeholderTextColor={COLORS.textMuted}
            value={searchQuery}
            onChangeText={setSearchQuery}
            autoFocus
          />
          {searchQuery.length > 0 && (
            <TouchableOpacity onPress={() => setSearchQuery('')}>
              <Ionicons name="close-circle" size={18} color={COLORS.textMuted} />
            </TouchableOpacity>
          )}
        </View>
      )}

      {/* Filter tabs */}
      <View style={styles.filterRow}>
        <TouchableOpacity
          style={[styles.filterTab, inboxFilter === 'all' && styles.filterTabActive]}
          onPress={() => setInboxFilter('all')}
        >
          <Text style={[styles.filterTabText, inboxFilter === 'all' && styles.filterTabTextActive]}>
            All
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.filterTab, inboxFilter === 'unread' && styles.filterTabActive]}
          onPress={() => setInboxFilter('unread')}
        >
          <Text style={[styles.filterTabText, inboxFilter === 'unread' && styles.filterTabTextActive]}>
            Unread{totalUnread > 0 ? ` (${totalUnread})` : ''}
          </Text>
        </TouchableOpacity>
      </View>

      {/* Conversations List */}
      <FlatList
        data={filteredInbox}
        keyExtractor={item => String(item.conversation_id)}
        renderItem={renderConversationItem}
        contentContainerStyle={filteredInbox.length === 0 ? { flex: 1 } : { paddingBottom: 20 }}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[COLORS.primary]} />
        }
        showsVerticalScrollIndicator={false}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Ionicons name="chatbubbles-outline" size={64} color="#CCC" />
            <Text style={styles.emptyTitle}>
              {inboxFilter === 'unread' ? 'No Unread Messages' : 'No Messages Yet'}
            </Text>
            <Text style={styles.emptyText}>
              {inboxFilter === 'unread'
                ? "You're all caught up!"
                : 'Start a conversation with contractors or property owners'}
            </Text>
            {inboxFilter === 'all' && (
              <TouchableOpacity style={styles.emptyButton} onPress={openCompose}>
                <Ionicons name="create-outline" size={18} color="#FFF" />
                <Text style={styles.emptyButtonText}>New Message</Text>
              </TouchableOpacity>
            )}
          </View>
        }
      />

      {/* Compose Modal */}
      <Modal visible={composeVisible} animationType="slide" presentationStyle="pageSheet">
        <View style={styles.composeContainer}>
          <View style={styles.composeHeader}>
            <TouchableOpacity onPress={() => { setComposeVisible(false); setSelectedRecipient(null); setComposeText(''); setComposeSearch(''); }}>
              <Text style={styles.composeCancelText}>Cancel</Text>
            </TouchableOpacity>
            <Text style={styles.composeTitle}>New Message</Text>
            <TouchableOpacity
              onPress={handleComposeSend}
              disabled={!selectedRecipient || !composeText.trim() || composeSending}
            >
              <Text style={[styles.composeSendText, (!selectedRecipient || !composeText.trim()) && { opacity: 0.4 }]}>
                {composeSending ? 'Sending...' : 'Send'}
              </Text>
            </TouchableOpacity>
          </View>

          {/* Recipient selection */}
          <View style={styles.recipientSection}>
            <Text style={styles.recipientLabel}>To:</Text>
            {selectedRecipient ? (
              <View style={styles.recipientChip}>
                <Text style={styles.recipientChipText}>{selectedRecipient.name}</Text>
                <TouchableOpacity onPress={() => setSelectedRecipient(null)}>
                  <Ionicons name="close-circle" size={18} color={COLORS.textMuted} />
                </TouchableOpacity>
              </View>
            ) : (
              <TextInput
                style={styles.recipientInput}
                placeholder="Search users..."
                placeholderTextColor={COLORS.textMuted}
                value={composeSearch}
                onChangeText={setComposeSearch}
              />
            )}
          </View>

          {/* User list (when no recipient selected) */}
          {!selectedRecipient && (
            <FlatList
              data={availableUsers.filter(u =>
                composeSearch
                  ? u.name.toLowerCase().includes(composeSearch.toLowerCase())
                  : true,
              )}
              keyExtractor={item => String(item.id)}
              style={styles.userList}
              ListHeaderComponent={
                usersLoading ? (
                  <ActivityIndicator size="small" color={COLORS.primary} style={{ marginVertical: 20 }} />
                ) : null
              }
              renderItem={({ item: usr }) => {
                const uAvatar = getAvatarUri(usr.avatar);
                const uColor = getAvatarColor(usr.id);
                return (
                  <TouchableOpacity
                    style={styles.userItem}
                    onPress={() => { setSelectedRecipient(usr); setComposeSearch(''); }}
                  >
                    {uAvatar ? (
                      <Image source={{ uri: uAvatar }} style={styles.userItemAvatar} />
                    ) : (
                      <View style={[styles.userItemAvatarPlaceholder, { backgroundColor: uColor }]}>
                        <Text style={styles.userItemAvatarText}>{getInitials(usr.name)}</Text>
                      </View>
                    )}
                    <View style={{ flex: 1 }}>
                      <Text style={styles.userItemName}>{usr.name}</Text>
                      <Text style={styles.userItemType}>
                        {usr.type === 'contractor' ? 'Contractor' : usr.type === 'property_owner' ? 'Property Owner' : usr.type}
                      </Text>
                    </View>
                  </TouchableOpacity>
                );
              }}
              ListEmptyComponent={
                !usersLoading ? (
                  <Text style={styles.noUsersText}>No users found</Text>
                ) : null
              }
            />
          )}

          {/* Compose text input (when recipient is selected) */}
          {selectedRecipient && (
            <View style={styles.composeInputWrap}>
              <TextInput
                style={styles.composeInput}
                placeholder="Type your message..."
                placeholderTextColor={COLORS.textMuted}
                value={composeText}
                onChangeText={setComposeText}
                multiline
                maxLength={5000}
                autoFocus
              />
            </View>
          )}
        </View>
      </Modal>
    </View>
  );
}

/* =====================================================================
 * Styles
 * ===================================================================== */

const styles = StyleSheet.create({
  // ─── Global ────────────────────────────────────────────────────
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: COLORS.textSecondary,
  },

  // ─── Header ────────────────────────────────────────────────────
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 4,
    paddingBottom: 12,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  headerLeft: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: COLORS.text,
  },
  headerBadge: {
    backgroundColor: COLORS.primary,
    borderRadius: 10,
    minWidth: 20,
    height: 20,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 6,
    marginLeft: 8,
  },
  headerBadgeText: {
    color: '#FFF',
    fontSize: 11,
    fontWeight: '700',
  },
  headerRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  headerIconBtn: {
    padding: 8,
  },

  // ─── Search ────────────────────────────────────────────────────
  searchBar: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    marginHorizontal: 16,
    marginTop: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  searchInput: {
    flex: 1,
    marginLeft: 8,
    fontSize: 15,
    color: COLORS.text,
    paddingVertical: 0,
  },

  // ─── Filter tabs ───────────────────────────────────────────────
  filterRow: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    paddingTop: 10,
    paddingBottom: 6,
    gap: 8,
  },
  filterTab: {
    paddingHorizontal: 16,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  filterTabActive: {
    backgroundColor: COLORS.primaryLight,
    borderColor: COLORS.primary,
  },
  filterTabText: {
    fontSize: 13,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },
  filterTabTextActive: {
    color: COLORS.primary,
    fontWeight: '600',
  },

  // ─── Conversation Item ─────────────────────────────────────────
  conversationItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 14,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  conversationItemUnread: {
    backgroundColor: COLORS.unreadBg,
  },
  conversationItemSuspended: {
    backgroundColor: COLORS.suspended,
  },
  conversationAvatar: {
    position: 'relative',
    marginRight: 12,
  },
  avatarImage: {
    width: 52,
    height: 52,
    borderRadius: 26,
  },
  avatarPlaceholder: {
    width: 52,
    height: 52,
    borderRadius: 26,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: 18,
    fontWeight: '600',
    color: '#FFF',
  },
  onlineDot: {
    position: 'absolute',
    top: 0,
    right: 0,
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: COLORS.primary,
    borderWidth: 2,
    borderColor: COLORS.surface,
  },
  conversationContent: {
    flex: 1,
  },
  conversationHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 2,
  },
  conversationName: {
    fontSize: 15,
    fontWeight: '500',
    color: COLORS.textSecondary,
    flex: 1,
  },
  conversationNameBold: {
    fontWeight: '700',
    color: COLORS.text,
  },
  conversationTime: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginLeft: 8,
  },
  roleBadgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 3,
    gap: 6,
  },
  roleBadge: {
    paddingHorizontal: 6,
    paddingVertical: 1,
    borderRadius: 4,
  },
  roleBadgeContractor: {
    backgroundColor: '#EBF5FF',
  },
  roleBadgeOwner: {
    backgroundColor: '#FFF3E6',
  },
  roleBadgeText: {
    fontSize: 10,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  suspendedBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 2,
  },
  suspendedBadgeText: {
    fontSize: 10,
    fontWeight: '500',
    color: COLORS.error,
  },
  lastMessage: {
    fontSize: 13,
    color: COLORS.textMuted,
  },
  lastMessageBold: {
    color: COLORS.textSecondary,
    fontWeight: '600',
  },
  unreadCountBadge: {
    backgroundColor: COLORS.primary,
    borderRadius: 12,
    minWidth: 22,
    height: 22,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 6,
    marginLeft: 8,
  },
  unreadCountText: {
    color: '#FFF',
    fontSize: 11,
    fontWeight: '700',
  },

  // ─── Empty state ───────────────────────────────────────────────
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 80,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
    marginTop: 16,
  },
  emptyText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    marginTop: 8,
    paddingHorizontal: 40,
  },
  emptyButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primary,
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 20,
    marginTop: 20,
    gap: 6,
  },
  emptyButtonText: {
    color: '#FFF',
    fontSize: 14,
    fontWeight: '600',
  },

  // ─── Chat Header ──────────────────────────────────────────────
  chatHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 10,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  backButton: {
    padding: 8,
    marginRight: 4,
  },
  chatHeaderInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  chatHeaderAvatar: {
    width: 38,
    height: 38,
    borderRadius: 19,
    marginRight: 10,
  },
  chatHeaderAvatarPlaceholder: {
    width: 38,
    height: 38,
    borderRadius: 19,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 10,
  },
  chatHeaderAvatarText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#FFF',
  },
  chatHeaderName: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
  },
  chatHeaderRole: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 1,
  },
  moreButton: {
    padding: 8,
  },

  // ─── Suspended banner ─────────────────────────────────────────
  suspendedBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.suspended,
    paddingHorizontal: 16,
    paddingVertical: 10,
    gap: 8,
  },
  suspendedBannerText: {
    flex: 1,
    fontSize: 12,
    color: COLORS.error,
    fontWeight: '500',
  },

  // ─── Chat Messages ────────────────────────────────────────────
  chatLoadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  messagesListContent: {
    padding: 12,
    paddingBottom: 8,
  },
  emptyChatContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 60,
  },
  emptyChatText: {
    marginTop: 12,
    fontSize: 14,
    color: COLORS.textMuted,
  },
  messageBubble: {
    flexDirection: 'row',
    marginBottom: 12,
    alignItems: 'flex-end',
  },
  messageBubbleOwn: {
    justifyContent: 'flex-end',
  },
  messageBubbleOther: {
    justifyContent: 'flex-start',
  },
  messageAvatarWrap: {
    marginRight: 8,
  },
  messageAvatarImage: {
    width: 30,
    height: 30,
    borderRadius: 15,
  },
  messageAvatarPlaceholder: {
    width: 30,
    height: 30,
    borderRadius: 15,
    justifyContent: 'center',
    alignItems: 'center',
  },
  messageAvatarText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#FFF',
  },
  messageContent: {
    maxWidth: '75%',
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 16,
  },
  messageContentOwn: {
    backgroundColor: COLORS.ownBubble,
    borderBottomRightRadius: 4,
  },
  messageContentOther: {
    backgroundColor: COLORS.otherBubble,
    borderBottomLeftRadius: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 2,
    elevation: 1,
  },
  messageContentFlagged: {
    borderWidth: 1,
    borderColor: COLORS.error,
  },
  messageSenderName: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginBottom: 3,
  },
  messageText: {
    fontSize: 15,
    lineHeight: 20,
  },
  messageTextOwn: {
    color: '#FFF',
  },
  messageTextOther: {
    color: COLORS.text,
  },
  flaggedRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    marginTop: 4,
  },
  flaggedText: {
    fontSize: 10,
    color: COLORS.error,
    fontWeight: '500',
  },
  messageTime: {
    fontSize: 10,
    marginTop: 4,
  },
  messageTimeOwn: {
    color: '#FFF',
    opacity: 0.75,
  },
  messageTimeOther: {
    color: COLORS.textMuted,
  },

  // ─── Attachments ──────────────────────────────────────────────
  attachmentsContainer: {
    marginTop: 6,
    gap: 4,
  },
  attachmentItem: {
    borderRadius: 8,
    overflow: 'hidden',
  },
  attachmentImage: {
    width: 180,
    height: 120,
    borderRadius: 8,
  },
  attachmentFile: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 6,
    gap: 6,
  },
  attachmentFileName: {
    fontSize: 12,
    color: COLORS.primary,
    fontWeight: '500',
    flex: 1,
  },

  // ─── Input Area ───────────────────────────────────────────────
  pendingAttachments: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    paddingHorizontal: 16,
    paddingTop: 8,
    backgroundColor: COLORS.surface,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    gap: 6,
  },
  pendingFile: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 4,
  },
  pendingFileName: {
    fontSize: 12,
    color: COLORS.primary,
    maxWidth: 100,
  },
  messageInputContainer: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    paddingHorizontal: 12,
    paddingVertical: 10,
    backgroundColor: COLORS.surface,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  attachButton: {
    padding: 8,
    marginRight: 4,
  },
  messageInput: {
    flex: 1,
    maxHeight: 100,
    paddingHorizontal: 14,
    paddingVertical: 10,
    backgroundColor: COLORS.background,
    borderRadius: 20,
    fontSize: 15,
    color: COLORS.text,
  },
  sendButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginLeft: 8,
  },
  sendButtonDisabled: {
    backgroundColor: '#CCC',
  },
  suspendedInputBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    backgroundColor: COLORS.surface,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    gap: 6,
  },
  suspendedInputText: {
    fontSize: 14,
    color: COLORS.textMuted,
    fontWeight: '500',
  },

  // ─── Report Modal ─────────────────────────────────────────────
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  modalBox: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 24,
    width: '100%',
    maxWidth: 340,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  modalDesc: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginBottom: 16,
  },
  modalInput: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 10,
    padding: 12,
    fontSize: 14,
    color: COLORS.text,
    minHeight: 80,
    textAlignVertical: 'top',
    marginBottom: 16,
  },
  modalButtons: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: 10,
  },
  modalCancelBtn: {
    paddingVertical: 10,
    paddingHorizontal: 18,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  modalCancelText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    fontWeight: '500',
  },
  modalConfirmBtn: {
    paddingVertical: 10,
    paddingHorizontal: 18,
    borderRadius: 8,
    backgroundColor: COLORS.error,
  },
  modalConfirmText: {
    fontSize: 14,
    color: '#FFF',
    fontWeight: '600',
  },

  // ─── Compose Modal ────────────────────────────────────────────
  composeContainer: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  composeHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 14,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  composeCancelText: {
    fontSize: 15,
    color: COLORS.textSecondary,
  },
  composeTitle: {
    fontSize: 17,
    fontWeight: '600',
    color: COLORS.text,
  },
  composeSendText: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.primary,
  },
  recipientSection: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 10,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  recipientLabel: {
    fontSize: 15,
    color: COLORS.textSecondary,
    fontWeight: '500',
    marginRight: 8,
  },
  recipientChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 14,
    gap: 4,
  },
  recipientChipText: {
    fontSize: 14,
    color: COLORS.primary,
    fontWeight: '500',
  },
  recipientInput: {
    flex: 1,
    fontSize: 15,
    color: COLORS.text,
    paddingVertical: 0,
  },
  userList: {
    flex: 1,
  },
  userItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  userItemAvatar: {
    width: 44,
    height: 44,
    borderRadius: 22,
    marginRight: 12,
  },
  userItemAvatarPlaceholder: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  userItemAvatarText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFF',
  },
  userItemName: {
    fontSize: 15,
    fontWeight: '500',
    color: COLORS.text,
  },
  userItemType: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 1,
  },
  noUsersText: {
    textAlign: 'center',
    color: COLORS.textMuted,
    marginTop: 30,
    fontSize: 14,
  },
  composeInputWrap: {
    flex: 1,
    padding: 16,
  },
  composeInput: {
    flex: 1,
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 14,
    fontSize: 15,
    color: COLORS.text,
    textAlignVertical: 'top',
  },
});
