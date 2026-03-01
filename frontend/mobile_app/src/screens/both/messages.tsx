import React, { useState, useEffect, useRef, useCallback, useMemo } from 'react';
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
  Dimensions,
} from 'react-native';
import { StatusBar } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons, Feather } from '@expo/vector-icons';
import * as DocumentPicker from 'expo-document-picker';
import {
  messages_service,
  InboxItem,
  ChatMessage,
  UserInfo,
  Attachment,
} from '../../services/messages_service';
import { api_config } from '../../config/api';
import {
  initPusher,
  subscribeToChatChannel,
  unsubscribeFromChannel,
  disconnectPusher,
} from '../../config/pusher';
import { storage_service } from '../../utils/storage';

/* =====================================================================
 * Constants & Types
 * ===================================================================== */

const { width: SCREEN_WIDTH } = Dimensions.get('window');

const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
  secondary: '#1A1A2E',
  success: '#10B981',
  error: '#EF4444',
  warning: '#F59E0B',
  bg: '#F8F9FA',
  surface: '#FFFFFF',
  text: '#1A1A2E',
  textSecondary: '#6B7280',
  textMuted: '#9CA3AF',
  border: '#E5E7EB',
  borderLight: '#F3F4F6',
  unreadBg: '#FFFBEB',
  ownBubble: '#EC7E00',
  otherBubble: '#FFFFFF',
  suspended: '#FEF2F2',
  chatBg: '#F0F2F5',
};

const AVATAR_COLORS = [
  '#3B82F6', '#10B981', '#EF4444', '#F59E0B',
  '#8B5CF6', '#14B8A6', '#F97316', '#6366F1',
];

const POLL_INTERVAL_MS = 15_000;
const MAX_ATTACHMENTS = 5;

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
 * Helper functions
 * ===================================================================== */

const getInitials = (name: string): string =>
  name
    ? name
        .split(' ')
        .map((w: string) => w[0])
        .join('')
        .substring(0, 2)
        .toUpperCase()
    : 'U';

const getAvatarColor = (id: number): string => AVATAR_COLORS[id % AVATAR_COLORS.length];

const getAvatarUri = (avatar: string | null): string | null => {
  if (!avatar) return null;
  if (avatar.startsWith('http')) return avatar;
  return `${api_config.base_url}/storage/${avatar}`;
};

const resolveFileUrl = (url: string): string => {
  if (!url) return '';
  if (url.startsWith('http')) return url;
  return `${api_config.base_url}${url.startsWith('/') ? '' : '/'}${url}`;
};

const getRoleLabel = (type: string): string => {
  switch (type) {
    case 'contractor':
      return 'Contractor';
    case 'property_owner':
      return 'Property Owner';
    case 'admin':
      return 'Admin';
    default:
      return type || 'User';
  }
};

/* =====================================================================
 * Sub-components
 * ===================================================================== */

const Avatar = React.memo(({
  uri,
  name,
  id,
  size = 48,
}: {
  uri: string | null;
  name: string;
  id: number;
  size?: number;
}) => {
  const avatarUrl = getAvatarUri(uri);
  const color = getAvatarColor(id);
  const radius = size / 2;
  const fontSize = size * 0.36;

  if (avatarUrl) {
    return (
      <Image
        source={{ uri: avatarUrl }}
        style={{ width: size, height: size, borderRadius: radius }}
      />
    );
  }

  return (
    <View
      style={{
        width: size,
        height: size,
        borderRadius: radius,
        backgroundColor: color,
        justifyContent: 'center',
        alignItems: 'center',
      }}
    >
      <Text style={{ fontSize, fontWeight: '600', color: '#FFF' }}>
        {getInitials(name)}
      </Text>
    </View>
  );
});

/* =====================================================================
 * Main Component
 * ===================================================================== */

export default function MessagesScreen({ userData }: MessagesScreenProps) {
  const insets = useSafeAreaInsets();
  const userId = userData?.user_id;

  /* ─── View state ─────────────────────────────────────────────── */
  const [activeConversation, setActiveConversation] = useState<InboxItem | null>(null);

  /* ─── Inbox state ────────────────────────────────────────────── */
  const [inbox, setInbox] = useState<InboxItem[]>([]);
  const [inboxLoading, setInboxLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [inboxFilter, setInboxFilter] = useState<'all' | 'unread'>('all');

  /* ─── Chat state ─────────────────────────────────────────────── */
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [chatLoading, setChatLoading] = useState(false);
  const [messageText, setMessageText] = useState('');
  const [sending, setSending] = useState(false);
  const [pendingAttachments, setPendingAttachments] = useState<any[]>([]);
  const chatListRef = useRef<FlatList>(null);

  /* ─── Compose modal ──────────────────────────────────────────── */
  const [composeVisible, setComposeVisible] = useState(false);
  const [availableUsers, setAvailableUsers] = useState<UserInfo[]>([]);
  const [usersLoading, setUsersLoading] = useState(false);
  const [composeSearch, setComposeSearch] = useState('');
  const [selectedRecipient, setSelectedRecipient] = useState<UserInfo | null>(null);
  const [composeText, setComposeText] = useState('');
  const [composeSending, setComposeSending] = useState(false);

  /* ─── Report modal ───────────────────────────────────────────── */
  const [reportModalVisible, setReportModalVisible] = useState(false);
  const [reportMessageId, setReportMessageId] = useState<number | null>(null);
  const [reportReason, setReportReason] = useState('');

  /* ─── Image preview modal ────────────────────────────────────── */
  const [previewImage, setPreviewImage] = useState<string | null>(null);

  /* ─── Pusher ─────────────────────────────────────────────────── */
  const pusherRef = useRef<any>(null);
  const channelRef = useRef<any>(null);
  const [pusherConnected, setPusherConnected] = useState(false);

  /* ─── Polling ────────────────────────────────────────────────── */
  const pollRef = useRef<ReturnType<typeof setInterval> | null>(null);
  const activeConvRef = useRef<InboxItem | null>(null);

  // Keep ref in sync so polling callbacks read current value
  useEffect(() => {
    activeConvRef.current = activeConversation;
  }, [activeConversation]);

  /* ─── Derived data ───────────────────────────────────────────── */
  const totalUnread = useMemo(
    () => inbox.reduce((s: number, c: InboxItem) => s + c.unread_count, 0),
    [inbox],
  );

  const filteredInbox = useMemo(() => {
    let items = [...inbox];
    if (inboxFilter === 'unread') items = items.filter((c: InboxItem) => c.unread_count > 0);
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      items = items.filter(
        (c: InboxItem) =>
          c.other_user.name.toLowerCase().includes(q) ||
          (c.last_message?.content || '').toLowerCase().includes(q),
      );
    }
    return items;
  }, [inbox, inboxFilter, searchQuery]);

  /* =================================================================
   * Effects
   * ================================================================= */

  useEffect(() => {
    loadInbox();
    initializePusherConnection();

    return () => {
      clearPoll();
      if (channelRef.current && pusherRef.current) {
        unsubscribeFromChannel(pusherRef.current, `private-chat.${userId}`);
      }
      if (pusherRef.current) disconnectPusher(pusherRef.current);
    };
  }, []);

  useEffect(() => {
    clearPoll();
    if (activeConversation) {
      pollRef.current = setInterval(() => {
        silentRefreshChat(activeConversation.conversation_id);
      }, POLL_INTERVAL_MS);
    }
    return clearPoll;
  }, [activeConversation?.conversation_id]);

  /* =================================================================
   * Pusher initialization
   * ================================================================= */

  const initializePusherConnection = async () => {
    if (!userId) return;
    try {
      const authToken = await storage_service.get_auth_token();
      if (!authToken) return;

      const pusher = await initPusher(authToken);
      if (!pusher) return;

      pusherRef.current = pusher;

      pusher.connection.bind('connected', () => setPusherConnected(true));
      pusher.connection.bind('disconnected', () => setPusherConnected(false));
      pusher.connection.bind('error', () => setPusherConnected(false));

      const channel = subscribeToChatChannel(
        pusher,
        userId,
        handlePusherMessage,
        handlePusherReadReceipt,
      );
      channelRef.current = channel;
    } catch (err) {
      console.error('Pusher init error:', err);
    }
  };

  /* =================================================================
   * Pusher event handlers
   * ================================================================= */

  const handlePusherMessage = useCallback(
    (event: any) => {
      const incoming: ChatMessage = {
        message_id: event.message_id,
        conversation_id: event.conversation_id,
        content: event.content,
        sender: event.sender,
        is_read: event.is_read ?? false,
        is_flagged: event.is_flagged ?? false,
        flag_reason: event.flag_reason ?? null,
        sent_at_human: 'Just now',
        sent_at: event.sent_at,
        attachments: event.attachments || [],
      };

      setActiveConversation((prev: InboxItem | null) => {
        if (prev && prev.conversation_id === event.conversation_id) {
          setMessages((msgs: ChatMessage[]) => {
            if (msgs.some((m: ChatMessage) => m.message_id === incoming.message_id)) return msgs;
            return [...msgs, incoming];
          });
          setTimeout(() => chatListRef.current?.scrollToEnd({ animated: true }), 200);
          messages_service.get_conversation(event.conversation_id).catch(() => {});
        }
        return prev;
      });

      setInbox((prev: InboxItem[]) => {
        const exists = prev.find((c: InboxItem) => c.conversation_id === event.conversation_id);
        if (exists) {
          return prev.map((c: InboxItem) =>
            c.conversation_id === event.conversation_id
              ? {
                  ...c,
                  last_message: {
                    content: event.content || 'Attachment',
                    sent_at: 'Just now',
                    sent_at_timestamp: event.sent_at,
                  },
                  unread_count: c.unread_count + 1,
                }
              : c,
          );
        }
        loadInbox();
        return prev;
      });
    },
    [],
  );

  const handlePusherReadReceipt = useCallback(
    (event: any) => {
      const { conversation_id } = event;
      setActiveConversation((prev: InboxItem | null) => {
        if (prev && prev.conversation_id === conversation_id) {
          setMessages((msgs: ChatMessage[]) =>
            msgs.map((m: ChatMessage) =>
              m.sender.id === userId && !m.is_read ? { ...m, is_read: true } : m,
            ),
          );
        }
        return prev;
      });
    },
    [userId],
  );

  /* =================================================================
   * Data loading
   * ================================================================= */

  const loadInbox = async () => {
    try {
      setInboxLoading(true);
      const res = await messages_service.get_inbox();
      if (res.success && res.data) setInbox(res.data);
      else setInbox([]);
    } catch (err) {
      console.error('loadInbox error:', err);
    } finally {
      setInboxLoading(false);
    }
  };

  const refreshInbox = async () => {
    setRefreshing(true);
    await loadInbox();
    setRefreshing(false);
  };

  const openConversation = async (item: InboxItem) => {
    setActiveConversation(item);
    setChatLoading(true);
    setMessages([]);
    try {
      const res = await messages_service.get_conversation(item.conversation_id);
      if (res.success && res.data) {
        setMessages(res.data.messages || []);
        setInbox((prev: InboxItem[]) =>
          prev.map((c: InboxItem) =>
            c.conversation_id === item.conversation_id ? { ...c, unread_count: 0 } : c,
          ),
        );
      }
    } catch (err) {
      console.error('openConversation error:', err);
    } finally {
      setChatLoading(false);
      setTimeout(() => chatListRef.current?.scrollToEnd({ animated: false }), 300);
    }
  };

  const silentRefreshChat = async (conversationId: number) => {
    try {
      const res = await messages_service.get_conversation(conversationId);
      if (res.success && res.data) {
        setMessages((prev: ChatMessage[]) => {
          const newMsgs = res.data!.messages || [];
          if (newMsgs.length !== prev.length) return newMsgs;
          const lastNew = newMsgs[newMsgs.length - 1];
          const lastOld = prev[prev.length - 1];
          if (lastNew?.message_id !== lastOld?.message_id) return newMsgs;
          return prev;
        });
      }
    } catch {}
  };

  const clearPoll = () => {
    if (pollRef.current) {
      clearInterval(pollRef.current);
      pollRef.current = null;
    }
  };

  /* =================================================================
   * Actions
   * ================================================================= */

  const handleSendMessage = async () => {
    if ((!messageText.trim() && pendingAttachments.length === 0) || !activeConversation) return;

    const text = messageText.trim();
    const attachments = [...pendingAttachments];
    const receiverId = activeConversation.other_user.id;

    setMessageText('');
    setPendingAttachments([]);
    setSending(true);
    Keyboard.dismiss();

    try {
      const res = await messages_service.send_message(
        receiverId,
        text,
        activeConversation.conversation_id,
        attachments.length > 0 ? attachments : undefined,
      );

      if (res.success && res.data) {
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

        setMessages((prev: ChatMessage[]) => {
          if (prev.some((m: ChatMessage) => m.message_id === newMsg.message_id)) return prev;
          return [...prev, newMsg];
        });

        setInbox((prev: InboxItem[]) =>
          prev.map((c: InboxItem) =>
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

        setTimeout(() => chatListRef.current?.scrollToEnd({ animated: true }), 150);
      } else {
        setMessageText(text);
        setPendingAttachments(attachments);
        Alert.alert('Send Failed', res.message || 'Could not send message. Please try again.');
      }
    } catch (err: any) {
      setMessageText(text);
      setPendingAttachments(attachments);
      Alert.alert('Error', err.message || 'Failed to send message');
    } finally {
      setSending(false);
    }
  };

  const handlePickAttachment = async () => {
    if (pendingAttachments.length >= MAX_ATTACHMENTS) {
      Alert.alert('Limit Reached', `You can attach up to ${MAX_ATTACHMENTS} files.`);
      return;
    }
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: [
          'image/*',
          'application/pdf',
          'application/msword',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
          'text/plain',
        ],
        multiple: true,
      });

      if (!result.canceled && result.assets) {
        const slotsLeft = MAX_ATTACHMENTS - pendingAttachments.length;
        const newFiles = result.assets.slice(0, slotsLeft);
        setPendingAttachments((prev: any[]) => [...prev, ...newFiles]);
      }
    } catch (err) {
      console.error('Attachment pick error:', err);
    }
  };

  const removeAttachment = (index: number) =>
    setPendingAttachments((prev: any[]) => prev.filter((_: any, i: number) => i !== index));

  /* =================================================================
   * Compose new message
   * ================================================================= */

  const openCompose = async () => {
    setComposeVisible(true);
    setUsersLoading(true);
    try {
      const res = await messages_service.get_available_users();
      if (res.success && res.data) setAvailableUsers(res.data);
    } catch {} finally {
      setUsersLoading(false);
    }
  };

  const closeCompose = () => {
    setComposeVisible(false);
    setSelectedRecipient(null);
    setComposeText('');
    setComposeSearch('');
  };

  const handleComposeSend = async () => {
    if (!selectedRecipient || !composeText.trim()) return;
    setComposeSending(true);
    try {
      const res = await messages_service.send_message(selectedRecipient.id, composeText.trim());
      if (res.success && res.data) {
        closeCompose();
        await loadInbox();
        const convId = res.data.conversation_id;
        const newItem: InboxItem = {
          conversation_id: convId,
          other_user: res.data.receiver,
          last_message: {
            content: res.data.content,
            sent_at: 'Just now',
            sent_at_timestamp: res.data.sent_at,
          },
          unread_count: 0,
          is_flagged: false,
          status: 'active',
          is_suspended: false,
          suspended_until: null,
          reason: null,
        };
        openConversation(newItem);
      } else {
        Alert.alert('Error', res.message || 'Failed to send message');
      }
    } catch (err: any) {
      Alert.alert('Error', err.message || 'Failed to send');
    } finally {
      setComposeSending(false);
    }
  };

  /* =================================================================
   * Report
   * ================================================================= */

  const openReport = (messageId: number) => {
    setReportMessageId(messageId);
    setReportReason('');
    setReportModalVisible(true);
  };

  const handleReport = async () => {
    if (!reportMessageId || !reportReason.trim()) return;
    try {
      const res = await messages_service.report_message(reportMessageId, reportReason.trim());
      Alert.alert(res.success ? 'Reported' : 'Error', res.message);
    } catch (err: any) {
      Alert.alert('Error', err.message || 'Failed to report');
    }
    setReportModalVisible(false);
    setReportMessageId(null);
    setReportReason('');
  };

  /* =================================================================
   * Back from chat to inbox
   * ================================================================= */

  const goBackToInbox = () => {
    setActiveConversation(null);
    setMessages([]);
    setPendingAttachments([]);
    setMessageText('');
    loadInbox();
  };

  /* =================================================================
   * Render: Conversation list item
   * ================================================================= */

  const renderConversationItem = useCallback(
    ({ item }: { item: InboxItem }) => {
      const hasUnread = item.unread_count > 0;
      const isSuspended = item.is_suspended;

      return (
        <TouchableOpacity
          style={[
            styles.convItem,
            hasUnread && styles.convItemUnread,
            isSuspended && styles.convItemSuspended,
          ]}
          onPress={() => openConversation(item)}
          activeOpacity={0.65}
        >
          <View style={styles.convAvatarWrap}>
            <Avatar
              uri={item.other_user.avatar}
              name={item.other_user.name}
              id={item.other_user.id}
              size={52}
            />
            {hasUnread && <View style={styles.unreadDot} />}
          </View>

          <View style={styles.convBody}>
            <View style={styles.convTopRow}>
              <Text style={[styles.convName, hasUnread && styles.convNameBold]} numberOfLines={1}>
                {item.other_user.name}
              </Text>
              <Text style={[styles.convTime, hasUnread && { color: COLORS.primary }]}>
                {item.last_message?.sent_at || ''}
              </Text>
            </View>

            <View style={styles.convBadgeRow}>
              <View
                style={[
                  styles.roleBadge,
                  item.other_user.type === 'contractor'
                    ? styles.roleBadgeContractor
                    : styles.roleBadgeOwner,
                ]}
              >
                <Text style={styles.roleBadgeText}>{getRoleLabel(item.other_user.type)}</Text>
              </View>
              {isSuspended && (
                <View style={styles.suspBadge}>
                  <Ionicons name="ban" size={10} color={COLORS.error} />
                  <Text style={styles.suspBadgeText}>Suspended</Text>
                </View>
              )}
            </View>

            <Text
              style={[styles.convPreview, hasUnread && styles.convPreviewBold]}
              numberOfLines={1}
            >
              {item.last_message?.content || 'No messages yet'}
            </Text>
          </View>

          {hasUnread && (
            <View style={styles.unreadBadge}>
              <Text style={styles.unreadBadgeText}>
                {item.unread_count > 99 ? '99+' : item.unread_count}
              </Text>
            </View>
          )}
        </TouchableOpacity>
      );
    },
    [],
  );

  /* =================================================================
   * Render: Chat message bubble
   * ================================================================= */

  const renderMessageBubble = useCallback(
    ({ item }: { item: ChatMessage }) => {
      const isOwn = item.sender?.id === userId;
      const hasAttachments = item.attachments && item.attachments.length > 0;

      return (
        <View style={[styles.bubbleRow, isOwn ? styles.bubbleRowOwn : styles.bubbleRowOther]}>
          {!isOwn && (
            <View style={styles.bubbleAvatarWrap}>
              <Avatar
                uri={item.sender?.avatar}
                name={item.sender?.name || 'U'}
                id={item.sender?.id || 0}
                size={30}
              />
            </View>
          )}

          <TouchableOpacity
            activeOpacity={0.85}
            onLongPress={() => {
              if (!isOwn) openReport(item.message_id);
            }}
            style={[
              styles.bubble,
              isOwn ? styles.bubbleOwn : styles.bubbleOther,
              item.is_flagged && styles.bubbleFlagged,
            ]}
          >
            {!isOwn && (
              <Text style={styles.bubbleSender}>{item.sender?.name || 'Unknown'}</Text>
            )}

            {item.content ? (
              <Text
                style={[
                  styles.bubbleText,
                  isOwn ? styles.bubbleTextOwn : styles.bubbleTextOther,
                ]}
              >
                {item.content}
              </Text>
            ) : null}

            {hasAttachments && (
              <View style={styles.bubbleAttachments}>
                {item.attachments.map((att: Attachment) => {
                  const fileUrl = resolveFileUrl(att.file_url);
                  if (att.is_image) {
                    return (
                      <TouchableOpacity
                        key={att.attachment_id}
                        onPress={() => setPreviewImage(fileUrl)}
                        activeOpacity={0.8}
                      >
                        <Image
                          source={{ uri: fileUrl }}
                          style={styles.attachImage}
                          resizeMode="cover"
                        />
                      </TouchableOpacity>
                    );
                  }
                  return (
                    <TouchableOpacity
                      key={att.attachment_id}
                      style={styles.attachFile}
                      onPress={() => Linking.openURL(fileUrl).catch(() => {})}
                    >
                      <Ionicons
                        name="document-outline"
                        size={16}
                        color={isOwn ? 'rgba(255,255,255,0.85)' : COLORS.primary}
                      />
                      <Text
                        style={[
                          styles.attachFileName,
                          isOwn && { color: 'rgba(255,255,255,0.9)' },
                        ]}
                        numberOfLines={1}
                      >
                        {att.file_name}
                      </Text>
                      <Ionicons
                        name="download-outline"
                        size={14}
                        color={isOwn ? 'rgba(255,255,255,0.7)' : COLORS.textMuted}
                      />
                    </TouchableOpacity>
                  );
                })}
              </View>
            )}

            {item.is_flagged && (
              <View style={styles.flaggedRow}>
                <Ionicons name="flag" size={10} color={COLORS.error} />
                <Text style={styles.flaggedLabel}>Flagged</Text>
              </View>
            )}

            <View style={styles.bubbleFooter}>
              <Text
                style={[
                  styles.bubbleTime,
                  isOwn ? styles.bubbleTimeOwn : styles.bubbleTimeOther,
                ]}
              >
                {item.sent_at_human ||
                  new Date(item.sent_at).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                  })}
              </Text>
              {isOwn && (
                <Ionicons
                  name={item.is_read ? 'checkmark-done' : 'checkmark'}
                  size={14}
                  color={isOwn ? 'rgba(255,255,255,0.7)' : COLORS.textMuted}
                  style={{ marginLeft: 4 }}
                />
              )}
            </View>
          </TouchableOpacity>
        </View>
      );
    },
    [userId],
  );

  /* =================================================================
   * CHAT VIEW
   * ================================================================= */

  if (activeConversation) {
    const other = activeConversation.other_user;
    const isSuspended = activeConversation.is_suspended;

    return (
      <KeyboardAvoidingView
        style={styles.flex1}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : 0}
      >
        <StatusBar hidden={true} />

        {/* Chat top bar */}
        <View style={styles.chatTopBar}>
          <TouchableOpacity
            style={styles.chatBackBtn}
            onPress={goBackToInbox}
            hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
          >
            <Ionicons name="chevron-back" size={26} color={COLORS.text} />
          </TouchableOpacity>

          <TouchableOpacity style={styles.chatTopUser} activeOpacity={0.7}>
            <Avatar uri={other.avatar} name={other.name} id={other.id} size={38} />
            <View style={styles.chatTopTextWrap}>
              <Text style={styles.chatTopName} numberOfLines={1}>
                {other.name}
              </Text>
              <Text style={styles.chatTopRole}>{getRoleLabel(other.type)}</Text>
            </View>
          </TouchableOpacity>

          <View style={{ width: 38 }} />
        </View>

        {/* Suspended banner */}
        {isSuspended && (
          <View style={styles.suspBanner}>
            <Ionicons name="alert-circle" size={16} color={COLORS.error} />
            <Text style={styles.suspBannerText}>
              Conversation suspended
              {activeConversation.suspended_until
                ? ` until ${activeConversation.suspended_until}`
                : ''}
              {activeConversation.reason ? `. ${activeConversation.reason}` : ''}
            </Text>
          </View>
        )}

        {/* Messages list */}
        {chatLoading ? (
          <View style={styles.chatLoadingWrap}>
            <ActivityIndicator size="large" color={COLORS.primary} />
            <Text style={styles.chatLoadingText}>Loading messages...</Text>
          </View>
        ) : (
          <FlatList
            ref={chatListRef}
            data={messages}
            keyExtractor={(item: ChatMessage) => String(item.message_id)}
            renderItem={renderMessageBubble}
            contentContainerStyle={[
              styles.chatListContent,
              messages.length === 0 && { flex: 1 },
            ]}
            showsVerticalScrollIndicator={false}
            onContentSizeChange={() =>
              chatListRef.current?.scrollToEnd({ animated: false })
            }
            ListEmptyComponent={
              <View style={styles.chatEmptyWrap}>
                <View style={styles.chatEmptyCircle}>
                  <Ionicons
                    name="chatbubble-ellipses-outline"
                    size={40}
                    color={COLORS.textMuted}
                  />
                </View>
                <Text style={styles.chatEmptyTitle}>Start the conversation</Text>
                <Text style={styles.chatEmptySubtitle}>
                  Send a message to {other.name}
                </Text>
              </View>
            }
          />
        )}

        {/* Pending attachments */}
        {pendingAttachments.length > 0 && (
          <View style={styles.pendingBar}>
            <FlatList
              data={pendingAttachments}
              horizontal
              showsHorizontalScrollIndicator={false}
              keyExtractor={(_: any, i: number) => String(i)}
              renderItem={({ item: file, index }: { item: any; index: number }) => (
                <View style={styles.pendingChip}>
                  <Ionicons name="document" size={14} color={COLORS.primary} />
                  <Text style={styles.pendingChipName} numberOfLines={1}>
                    {file.name || 'File'}
                  </Text>
                  <TouchableOpacity
                    onPress={() => removeAttachment(index)}
                    hitSlop={{ top: 6, bottom: 6, left: 6, right: 6 }}
                  >
                    <Ionicons name="close-circle" size={16} color={COLORS.error} />
                  </TouchableOpacity>
                </View>
              )}
              contentContainerStyle={{ paddingHorizontal: 12, gap: 8 }}
            />
          </View>
        )}

        {/* Input bar */}
        {!isSuspended ? (
          <View style={[styles.inputBar, { paddingBottom: Math.max(insets.bottom, 8) }]}>
            <TouchableOpacity
              style={styles.inputAttachBtn}
              onPress={handlePickAttachment}
              hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
            >
              <Ionicons name="add-circle-outline" size={26} color={COLORS.textSecondary} />
            </TouchableOpacity>

            <TextInput
              style={styles.inputField}
              placeholder="Message..."
              placeholderTextColor={COLORS.textMuted}
              value={messageText}
              onChangeText={setMessageText}
              multiline
              maxLength={5000}
            />

            <TouchableOpacity
              style={[
                styles.sendBtn,
                ((!messageText.trim() && pendingAttachments.length === 0) || sending)
                  ? styles.sendBtnDisabled
                  : null,
              ]}
              onPress={handleSendMessage}
              disabled={(!messageText.trim() && pendingAttachments.length === 0) || sending}
            >
              {sending ? (
                <ActivityIndicator size="small" color="#FFF" />
              ) : (
                <Ionicons name="send" size={18} color="#FFF" />
              )}
            </TouchableOpacity>
          </View>
        ) : (
          <View
            style={[styles.suspInputBar, { paddingBottom: Math.max(insets.bottom, 8) }]}
          >
            <Ionicons name="lock-closed" size={16} color={COLORS.textMuted} />
            <Text style={styles.suspInputText}>Messaging is suspended</Text>
          </View>
        )}

        {/* Report modal */}
        <Modal visible={reportModalVisible} transparent animationType="fade">
          <View style={styles.modalOverlay}>
            <View style={styles.modalCard}>
              <Text style={styles.modalTitle}>Report Message</Text>
              <Text style={styles.modalDesc}>
                Tell us why you're reporting this message.
              </Text>
              <TextInput
                style={styles.modalInput}
                placeholder="Describe the issue..."
                placeholderTextColor={COLORS.textMuted}
                value={reportReason}
                onChangeText={setReportReason}
                multiline
                maxLength={500}
              />
              <View style={styles.modalActions}>
                <TouchableOpacity
                  style={styles.modalCancelBtn}
                  onPress={() => {
                    setReportModalVisible(false);
                    setReportReason('');
                  }}
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

        {/* Image preview modal */}
        <Modal visible={!!previewImage} transparent animationType="fade">
          <View style={styles.previewOverlay}>
            <TouchableOpacity
              style={styles.previewCloseBtn}
              onPress={() => setPreviewImage(null)}
            >
              <Ionicons name="close" size={28} color="#FFF" />
            </TouchableOpacity>
            {previewImage && (
              <Image
                source={{ uri: previewImage }}
                style={styles.previewImage}
                resizeMode="contain"
              />
            )}
          </View>
        </Modal>
      </KeyboardAvoidingView>
    );
  }

  /* =================================================================
   * INBOX VIEW
   * ================================================================= */

  if (inboxLoading && inbox.length === 0) {
    return (
      <View style={[styles.flex1, styles.centerContent, { backgroundColor: COLORS.bg }]}>
        <StatusBar hidden={true} />
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading messages...</Text>
      </View>
    );
  }

  return (
    <View style={[styles.flex1, { backgroundColor: COLORS.bg }]}>
      <StatusBar hidden={true} />

      {/* Inbox header */}
      <View style={styles.inboxHeader}>
        <View style={styles.inboxHeaderLeft}>
          <Text style={styles.inboxTitle}>Messages</Text>
          {totalUnread > 0 && (
            <View style={styles.inboxBadge}>
              <Text style={styles.inboxBadgeText}>
                {totalUnread > 99 ? '99+' : totalUnread}
              </Text>
            </View>
          )}
        </View>
        <View style={styles.inboxHeaderRight}>
          <TouchableOpacity
            style={styles.inboxIconBtn}
            onPress={() => {
              setIsSearchOpen((v: boolean) => !v);
              if (isSearchOpen) setSearchQuery('');
            }}
          >
            <Ionicons
              name={isSearchOpen ? 'close' : 'search-outline'}
              size={22}
              color={COLORS.text}
            />
          </TouchableOpacity>
          <TouchableOpacity style={styles.inboxIconBtn} onPress={openCompose}>
            <Feather name="edit" size={20} color={COLORS.primary} />
          </TouchableOpacity>
        </View>
      </View>

      {/* Search */}
      {isSearchOpen && (
        <View style={styles.searchBarWrap}>
          <View style={styles.searchBar}>
            <Ionicons name="search" size={18} color={COLORS.textMuted} />
            <TextInput
              style={styles.searchInput}
              placeholder="Search conversations..."
              placeholderTextColor={COLORS.textMuted}
              value={searchQuery}
              onChangeText={setSearchQuery}
              autoFocus
              returnKeyType="search"
            />
            {searchQuery.length > 0 && (
              <TouchableOpacity onPress={() => setSearchQuery('')}>
                <Ionicons name="close-circle" size={18} color={COLORS.textMuted} />
              </TouchableOpacity>
            )}
          </View>
        </View>
      )}

      {/* Filter tabs */}
      <View style={styles.filterRow}>
        {(['all', 'unread'] as const).map((f: 'all' | 'unread') => (
          <TouchableOpacity
            key={f}
            style={[styles.filterTab, inboxFilter === f && styles.filterTabActive]}
            onPress={() => setInboxFilter(f)}
          >
            <Text
              style={[
                styles.filterTabLabel,
                inboxFilter === f && styles.filterTabLabelActive,
              ]}
            >
              {f === 'all'
                ? 'All'
                : `Unread${totalUnread > 0 ? ` (${totalUnread})` : ''}`}
            </Text>
          </TouchableOpacity>
        ))}
        <View style={styles.filterRowRight}>
          <View
            style={[
              styles.connectionDot,
              pusherConnected ? styles.connectionDotOn : styles.connectionDotOff,
            ]}
          />
        </View>
      </View>

      {/* Conversation list */}
      <FlatList
        data={filteredInbox}
        keyExtractor={(item: InboxItem) => String(item.conversation_id)}
        renderItem={renderConversationItem}
        contentContainerStyle={
          filteredInbox.length === 0 ? { flex: 1 } : { paddingBottom: 20 }
        }
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={refreshInbox}
            colors={[COLORS.primary]}
            tintColor={COLORS.primary}
          />
        }
        showsVerticalScrollIndicator={false}
        ListEmptyComponent={
          <View style={styles.emptyWrap}>
            <View style={styles.emptyCircle}>
              <Ionicons name="chatbubbles-outline" size={48} color={COLORS.textMuted} />
            </View>
            <Text style={styles.emptyTitle}>
              {inboxFilter === 'unread' ? 'All Caught Up!' : 'No Messages Yet'}
            </Text>
            <Text style={styles.emptySubtitle}>
              {inboxFilter === 'unread'
                ? 'You have no unread messages.'
                : 'Start a conversation with contractors or property owners.'}
            </Text>
            {inboxFilter === 'all' && (
              <TouchableOpacity style={styles.emptyBtn} onPress={openCompose}>
                <Feather name="edit" size={16} color="#FFF" />
                <Text style={styles.emptyBtnText}>New Message</Text>
              </TouchableOpacity>
            )}
          </View>
        }
      />

      {/* Compose modal */}
      <Modal visible={composeVisible} animationType="slide" presentationStyle="pageSheet">
        <View style={[styles.flex1, { backgroundColor: COLORS.bg }]}>
          <View style={styles.composeHeader}>
            <TouchableOpacity onPress={closeCompose}>
              <Text style={styles.composeCancel}>Cancel</Text>
            </TouchableOpacity>
            <Text style={styles.composeTitle}>New Message</Text>
            <TouchableOpacity
              onPress={handleComposeSend}
              disabled={!selectedRecipient || !composeText.trim() || composeSending}
            >
              <Text
                style={[
                  styles.composeSend,
                  (!selectedRecipient || !composeText.trim()) && { opacity: 0.4 },
                ]}
              >
                {composeSending ? 'Sending...' : 'Send'}
              </Text>
            </TouchableOpacity>
          </View>

          <View style={styles.recipientBar}>
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
                autoFocus
              />
            )}
          </View>

          {!selectedRecipient && (
            <FlatList
              data={availableUsers.filter((u: UserInfo) =>
                composeSearch
                  ? u.name.toLowerCase().includes(composeSearch.toLowerCase())
                  : true,
              )}
              keyExtractor={(item: UserInfo) => String(item.id)}
              style={{ flex: 1 }}
              ListHeaderComponent={
                usersLoading ? (
                  <ActivityIndicator
                    size="small"
                    color={COLORS.primary}
                    style={{ marginVertical: 20 }}
                  />
                ) : null
              }
              renderItem={({ item: usr }: { item: UserInfo }) => (
                <TouchableOpacity
                  style={styles.userRow}
                  onPress={() => {
                    setSelectedRecipient(usr);
                    setComposeSearch('');
                  }}
                >
                  <Avatar uri={usr.avatar} name={usr.name} id={usr.id} size={44} />
                  <View style={{ flex: 1, marginLeft: 12 }}>
                    <Text style={styles.userRowName}>{usr.name}</Text>
                    <Text style={styles.userRowType}>{getRoleLabel(usr.type)}</Text>
                  </View>
                </TouchableOpacity>
              )}
              ListEmptyComponent={
                !usersLoading ? (
                  <View style={styles.centerContent}>
                    <Text style={styles.noUsersText}>No users found</Text>
                  </View>
                ) : null
              }
            />
          )}

          {selectedRecipient && (
            <View style={styles.composeTextWrap}>
              <TextInput
                style={styles.composeTextInput}
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
  flex1: { flex: 1 },
  centerContent: { justifyContent: 'center', alignItems: 'center' },

  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: COLORS.textSecondary,
  },

  /* Inbox header */
  inboxHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 6,
    paddingBottom: 12,
    backgroundColor: COLORS.surface,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: COLORS.border,
  },
  inboxHeaderLeft: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  inboxTitle: {
    fontSize: 28,
    fontWeight: '800',
    color: COLORS.text,
    letterSpacing: -0.5,
  },
  inboxBadge: {
    backgroundColor: COLORS.primary,
    borderRadius: 10,
    minWidth: 22,
    height: 22,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 6,
    marginLeft: 8,
  },
  inboxBadgeText: {
    color: '#FFF',
    fontSize: 11,
    fontWeight: '700',
  },
  inboxHeaderRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 2,
  },
  inboxIconBtn: {
    padding: 8,
    borderRadius: 20,
  },

  /* Search */
  searchBarWrap: {
    paddingHorizontal: 16,
    paddingTop: 8,
    paddingBottom: 4,
    backgroundColor: COLORS.surface,
  },
  searchBar: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.bg,
    paddingHorizontal: 12,
    paddingVertical: 10,
    borderRadius: 12,
  },
  searchInput: {
    flex: 1,
    marginLeft: 8,
    fontSize: 15,
    color: COLORS.text,
    paddingVertical: 0,
  },

  /* Filter tabs */
  filterRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingTop: 10,
    paddingBottom: 6,
    gap: 8,
  },
  filterTab: {
    paddingHorizontal: 16,
    paddingVertical: 7,
    borderRadius: 18,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  filterTabActive: {
    backgroundColor: COLORS.primaryLight,
    borderColor: COLORS.primary,
  },
  filterTabLabel: {
    fontSize: 13,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },
  filterTabLabelActive: {
    color: COLORS.primary,
    fontWeight: '600',
  },
  filterRowRight: {
    flex: 1,
    alignItems: 'flex-end',
  },
  connectionDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  connectionDotOn: {
    backgroundColor: COLORS.success,
  },
  connectionDotOff: {
    backgroundColor: COLORS.textMuted,
    opacity: 0.4,
  },

  /* Conversation item */
  convItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 14,
    backgroundColor: COLORS.surface,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: COLORS.borderLight,
  },
  convItemUnread: {
    backgroundColor: COLORS.unreadBg,
  },
  convItemSuspended: {
    backgroundColor: COLORS.suspended,
  },
  convAvatarWrap: {
    position: 'relative',
    marginRight: 14,
  },
  unreadDot: {
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
  convBody: {
    flex: 1,
  },
  convTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 2,
  },
  convName: {
    fontSize: 15,
    fontWeight: '500',
    color: COLORS.textSecondary,
    flex: 1,
    marginRight: 8,
  },
  convNameBold: {
    fontWeight: '700',
    color: COLORS.text,
  },
  convTime: {
    fontSize: 11,
    color: COLORS.textMuted,
  },
  convBadgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 3,
    gap: 6,
  },
  roleBadge: {
    paddingHorizontal: 6,
    paddingVertical: 2,
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
  suspBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 2,
  },
  suspBadgeText: {
    fontSize: 10,
    fontWeight: '500',
    color: COLORS.error,
  },
  convPreview: {
    fontSize: 13,
    color: COLORS.textMuted,
    lineHeight: 18,
  },
  convPreviewBold: {
    color: COLORS.textSecondary,
    fontWeight: '600',
  },
  unreadBadge: {
    backgroundColor: COLORS.primary,
    borderRadius: 12,
    minWidth: 24,
    height: 24,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 6,
    marginLeft: 8,
  },
  unreadBadgeText: {
    color: '#FFF',
    fontSize: 11,
    fontWeight: '700',
  },

  /* Empty state */
  emptyWrap: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 60,
    paddingHorizontal: 40,
  },
  emptyCircle: {
    width: 88,
    height: 88,
    borderRadius: 44,
    backgroundColor: COLORS.borderLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  emptySubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 24,
  },
  emptyBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primary,
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 24,
    gap: 8,
    elevation: 2,
    shadowColor: COLORS.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
  },
  emptyBtnText: {
    color: '#FFF',
    fontSize: 15,
    fontWeight: '600',
  },

  /* Chat top bar */
  chatTopBar: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 10,
    backgroundColor: COLORS.surface,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: COLORS.border,
  },
  chatBackBtn: {
    padding: 6,
    marginRight: 2,
  },
  chatTopUser: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  chatTopTextWrap: {
    marginLeft: 10,
    flex: 1,
  },
  chatTopName: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
  },
  chatTopRole: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 1,
  },

  /* Suspended banner */
  suspBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.suspended,
    paddingHorizontal: 16,
    paddingVertical: 10,
    gap: 8,
  },
  suspBannerText: {
    flex: 1,
    fontSize: 12,
    color: COLORS.error,
    fontWeight: '500',
    lineHeight: 16,
  },

  /* Chat loading / empty */
  chatLoadingWrap: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  chatLoadingText: {
    marginTop: 12,
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  chatEmptyWrap: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 60,
  },
  chatEmptyCircle: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: COLORS.borderLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  chatEmptyTitle: {
    fontSize: 17,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 6,
  },
  chatEmptySubtitle: {
    fontSize: 13,
    color: COLORS.textSecondary,
  },

  /* Chat list */
  chatListContent: {
    padding: 12,
    paddingBottom: 8,
  },

  /* Message bubble */
  bubbleRow: {
    flexDirection: 'row',
    marginBottom: 8,
    alignItems: 'flex-end',
  },
  bubbleRowOwn: {
    justifyContent: 'flex-end',
  },
  bubbleRowOther: {
    justifyContent: 'flex-start',
  },
  bubbleAvatarWrap: {
    marginRight: 8,
    marginBottom: 2,
  },
  bubble: {
    maxWidth: '78%',
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 18,
  },
  bubbleOwn: {
    backgroundColor: COLORS.ownBubble,
    borderBottomRightRadius: 6,
  },
  bubbleOther: {
    backgroundColor: COLORS.otherBubble,
    borderBottomLeftRadius: 6,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 3,
    elevation: 1,
  },
  bubbleFlagged: {
    borderWidth: 1,
    borderColor: COLORS.error,
  },
  bubbleSender: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.primary,
    marginBottom: 3,
  },
  bubbleText: {
    fontSize: 15,
    lineHeight: 21,
  },
  bubbleTextOwn: {
    color: '#FFF',
  },
  bubbleTextOther: {
    color: COLORS.text,
  },
  bubbleFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
    marginTop: 4,
  },
  bubbleTime: {
    fontSize: 10,
  },
  bubbleTimeOwn: {
    color: 'rgba(255,255,255,0.7)',
  },
  bubbleTimeOther: {
    color: COLORS.textMuted,
  },
  flaggedRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    marginTop: 4,
  },
  flaggedLabel: {
    fontSize: 10,
    color: COLORS.error,
    fontWeight: '500',
  },

  /* Attachments in bubble */
  bubbleAttachments: {
    marginTop: 8,
    gap: 6,
  },
  attachImage: {
    width: 200,
    height: 150,
    borderRadius: 10,
  },
  attachFile: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 6,
    paddingHorizontal: 8,
    backgroundColor: 'rgba(0,0,0,0.04)',
    borderRadius: 8,
    gap: 6,
  },
  attachFileName: {
    fontSize: 12,
    color: COLORS.primary,
    fontWeight: '500',
    flex: 1,
  },

  /* Pending attachments bar */
  pendingBar: {
    paddingVertical: 8,
    backgroundColor: COLORS.surface,
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: COLORS.border,
  },
  pendingChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 16,
    gap: 4,
  },
  pendingChipName: {
    fontSize: 12,
    color: COLORS.primary,
    fontWeight: '500',
    maxWidth: 100,
  },

  /* Input bar */
  inputBar: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    paddingHorizontal: 10,
    paddingTop: 10,
    backgroundColor: COLORS.surface,
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: COLORS.border,
  },
  inputAttachBtn: {
    padding: 6,
    marginRight: 4,
    marginBottom: 4,
  },
  inputField: {
    flex: 1,
    maxHeight: 100,
    paddingHorizontal: 14,
    paddingVertical: 10,
    backgroundColor: COLORS.bg,
    borderRadius: 22,
    fontSize: 15,
    color: COLORS.text,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
  },
  sendBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginLeft: 8,
    marginBottom: 2,
  },
  sendBtnDisabled: {
    backgroundColor: COLORS.border,
  },
  suspInputBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    backgroundColor: COLORS.surface,
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: COLORS.border,
    gap: 6,
  },
  suspInputText: {
    fontSize: 14,
    color: COLORS.textMuted,
    fontWeight: '500',
  },

  /* Modals */
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  modalCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 24,
    width: '100%',
    maxWidth: 360,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 6,
  },
  modalDesc: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginBottom: 16,
    lineHeight: 20,
  },
  modalInput: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 12,
    padding: 12,
    fontSize: 14,
    color: COLORS.text,
    minHeight: 80,
    textAlignVertical: 'top',
    marginBottom: 16,
    backgroundColor: COLORS.bg,
  },
  modalActions: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: 10,
  },
  modalCancelBtn: {
    paddingVertical: 10,
    paddingHorizontal: 18,
    borderRadius: 10,
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
    borderRadius: 10,
    backgroundColor: COLORS.error,
  },
  modalConfirmText: {
    fontSize: 14,
    color: '#FFF',
    fontWeight: '600',
  },

  /* Image preview */
  previewOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.92)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  previewCloseBtn: {
    position: 'absolute',
    top: 50,
    right: 20,
    zIndex: 10,
    padding: 8,
  },
  previewImage: {
    width: SCREEN_WIDTH - 32,
    height: SCREEN_WIDTH - 32,
  },

  /* Compose modal */
  composeHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 14,
    backgroundColor: COLORS.surface,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: COLORS.border,
  },
  composeCancel: {
    fontSize: 15,
    color: COLORS.textSecondary,
  },
  composeTitle: {
    fontSize: 17,
    fontWeight: '600',
    color: COLORS.text,
  },
  composeSend: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.primary,
  },
  recipientBar: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 10,
    backgroundColor: COLORS.surface,
    borderBottomWidth: StyleSheet.hairlineWidth,
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
  userRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: COLORS.surface,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: COLORS.borderLight,
  },
  userRowName: {
    fontSize: 15,
    fontWeight: '500',
    color: COLORS.text,
  },
  userRowType: {
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
  composeTextWrap: {
    flex: 1,
    padding: 16,
  },
  composeTextInput: {
    flex: 1,
    backgroundColor: COLORS.surface,
    borderRadius: 14,
    padding: 14,
    fontSize: 15,
    color: COLORS.text,
    textAlignVertical: 'top',
    borderWidth: 1,
    borderColor: COLORS.borderLight,
  },
});
