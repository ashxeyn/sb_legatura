// @ts-nocheck
import React, { useState, useEffect, useCallback, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  RefreshControl,
  Animated,
  Dimensions,
  ActivityIndicator,
  Alert,
  Modal,
  TextInput,
} from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather, MaterialIcons, Ionicons } from '@expo/vector-icons';
import { notifications_service, Notification as ApiNotification } from '../../services/notifications_service';
import { api_config, api_request } from '../../config/api';

const { width } = Dimensions.get('window');

const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
  success: '#10B981',
  successLight: '#D1FAE5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
  errorLight: '#FEE2E2',
  info: '#3B82F6',
  infoLight: '#DBEAFE',
  background: '#F8FAFC',
  surface: '#FFFFFF',
  text: '#0F172A',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
};

// Notification type definitions — matches backend sub-types from NotificationService::$frontendTypeMap
type NotificationType =
  // Bid
  | 'bid_accepted'
  | 'bid_rejected'
  | 'bid_received'
  // Milestone
  | 'milestone_submitted'
  | 'milestone_approved'
  | 'milestone_rejected'
  | 'milestone_completed'
  | 'milestone_item_completed'
  | 'milestone_deleted'
  | 'milestone_resubmitted'
  | 'milestone_updated'
  // Progress
  | 'progress_submitted'
  | 'progress_approved'
  | 'progress_rejected'
  | 'progress_updated'
  // Payment
  | 'payment_submitted'
  | 'payment_approved'
  | 'payment_rejected'
  | 'payment_updated'
  | 'payment_deleted'
  | 'payment_due'
  // Dispute
  | 'dispute_opened'
  | 'dispute_updated'
  | 'dispute_cancelled'
  | 'dispute_under_review'
  | 'dispute_resolved'
  | 'dispute_rejected'
  // Project
  | 'project_completed'
  | 'project_halted'
  | 'project_terminated'
  | 'project_update'
  | 'showcase_update'
  // Team
  | 'team_invite'
  | 'team_removed'
  | 'team_role_changed'
  | 'team_access_changed'
  // Reviews
  | 'review_prompt'
  | 'review_submitted'
  // Payment fully paid
  | 'payment_fully_paid'
  // Admin
  | 'admin_announcement'
  // Fallback
  | 'general';

type NotificationCategory = 'all' | 'projects' | 'bids' | 'payments' | 'messages' | 'announcements';
type StaffActionKind = 'invite' | 'role_change';

interface Notification {
  id: number;
  type: NotificationType;
  title: string;
  message: string;
  timestamp: string;
  is_read: boolean;
  action_url?: string;
  redirect_url?: string;
  reference_type?: string | null;
  reference_id?: number | null;
  priority?: string;
  notification_role?: 'contractor' | 'owner' | 'both';
}

interface NotificationsProps {
  userId: number;
  userType: 'property_owner' | 'contractor' | 'both';
  onClose: () => void;
  /** Called when a notification tap resolves a navigation target. */
  onNavigate?: (screen: string, params: Record<string, any>) => void;
}

// Get notification icon and colors based on type
const getNotificationStyle = (type: NotificationType) => {
  switch (type) {
    // ── Bids ── hammer/gavel icon ──
    case 'bid_accepted':
      return {
        icon: 'hammer-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.successLight,
        iconColor: COLORS.success,
      };
    case 'bid_rejected':
      return {
        icon: 'hammer-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.errorLight,
        iconColor: COLORS.error,
      };
    case 'bid_received':
      return {
        icon: 'hammer-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.infoLight,
        iconColor: COLORS.info,
      };

    // ── Milestones ── briefcase icon ──
    case 'milestone_submitted':
    case 'milestone_resubmitted':
    case 'milestone_updated':
      return {
        icon: 'briefcase-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.infoLight,
        iconColor: COLORS.info,
      };
    case 'milestone_approved':
    case 'milestone_completed':
    case 'milestone_item_completed':
      return {
        icon: 'briefcase-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.successLight,
        iconColor: COLORS.success,
      };
    case 'milestone_rejected':
      return {
        icon: 'briefcase-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.errorLight,
        iconColor: COLORS.error,
      };
    case 'milestone_deleted':
      return {
        icon: 'briefcase-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.errorLight,
        iconColor: COLORS.error,
      };

    // ── Payments ── Peso Sign ₱ ──
    case 'payment_approved':
      return {
        icon: '₱' as const,
        iconComponent: 'text',
        bgColor: COLORS.successLight,
        iconColor: COLORS.success,
      };
    case 'payment_rejected':
      return {
        icon: '₱' as const,
        iconComponent: 'text',
        bgColor: COLORS.errorLight,
        iconColor: COLORS.error,
      };
    case 'payment_submitted':
      return {
        icon: '₱' as const,
        iconComponent: 'text',
        bgColor: COLORS.infoLight,
        iconColor: COLORS.info,
      };
    case 'payment_updated':
      return {
        icon: '₱' as const,
        iconComponent: 'text',
        bgColor: COLORS.primaryLight,
        iconColor: COLORS.primary,
      };
    case 'payment_due':
      return {
        icon: '₱' as const,
        iconComponent: 'text',
        bgColor: COLORS.warningLight,
        iconColor: COLORS.warning,
      };
    case 'payment_deleted':
      return {
        icon: '₱' as const,
        iconComponent: 'text',
        bgColor: COLORS.errorLight,
        iconColor: COLORS.error,
      };

    // ── Other Approved / Accepted / Completed ── green ──
    case 'progress_approved':
    case 'dispute_resolved':
    case 'project_completed':
      return {
        icon: 'checkmark-circle' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.successLight,
        iconColor: COLORS.success,
      };

    // ── Other Rejected / Denied ── red ──
    case 'progress_rejected':
    case 'dispute_rejected':
      return {
        icon: 'close-circle' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.errorLight,
        iconColor: COLORS.error,
      };

    // ── Submitted / Pending review ── blue ──
    case 'progress_submitted':
      return {
        icon: 'document-text-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.infoLight,
        iconColor: COLORS.info,
      };

    // ── Updates / Changes ── primary orange ──
    case 'progress_updated':
    case 'dispute_updated':
    case 'dispute_under_review':
    case 'project_update':
    case 'showcase_update':
      return {
        icon: 'sync-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.primaryLight,
        iconColor: COLORS.primary,
      };

    // ── Deletions / Removals / Terminations ── red outline ──
    case 'project_halted':
    case 'project_terminated':
    case 'team_removed':
      return {
        icon: 'trash-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.errorLight,
        iconColor: COLORS.error,
      };

    // ── Disputes opened / cancelled ── amber ──
    case 'dispute_opened':
      return {
        icon: 'warning-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.warningLight,
        iconColor: COLORS.warning,
      };
    case 'dispute_cancelled':
      return {
        icon: 'ban-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.borderLight,
        iconColor: COLORS.textSecondary,
      };

    // ── Reviews ── star icon ──
    case 'review_prompt':
      return {
        icon: 'star-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.warningLight,
        iconColor: COLORS.warning,
      };
    case 'review_submitted':
      return {
        icon: 'star' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.warningLight,
        iconColor: COLORS.warning,
      };

    // ── Fully Paid ── Peso Sign ₱ ──
    case 'payment_fully_paid':
      return {
        icon: '₱' as const,
        iconComponent: 'text',
        bgColor: COLORS.successLight,
        iconColor: COLORS.success,
      };

    // ── Team / Members ── primary ──
    case 'team_invite':
      return {
        icon: 'person-add-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.primaryLight,
        iconColor: COLORS.primary,
      };
    case 'team_role_changed':
    case 'team_access_changed':
      return {
        icon: 'person-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.primaryLight,
        iconColor: COLORS.primary,
      };

    // ── Admin Announcements ── megaphone ──
    case 'admin_announcement':
      return {
        icon: 'megaphone-outline' as const,
        iconComponent: 'ionicons',
        bgColor: '#EDE9FE',
        iconColor: '#7C3AED',
      };

    // ── Default / General ──
    default:
      return {
        icon: 'notifications-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.borderLight,
        iconColor: COLORS.textSecondary,
      };
  }
};

// Format a readable label from the sub-type key
const getTypeLabel = (type: NotificationType): string => {
  return type
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase());
};

// Format relative time
const formatRelativeTime = (timestamp: string): string => {
  if (!timestamp) return '';
  const now = new Date();
  const date = new Date(timestamp.replace(' ', 'T') + 'Z'); // backend timestamps are UTC
  if (isNaN(date.getTime())) return '';
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / (1000 * 60));
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m`;
  if (diffHours < 24) return `${diffHours}h`;
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays}d`;
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

// Group notifications by date
const groupNotificationsByDate = (notifications: Notification[]) => {
  const groups: { [key: string]: Notification[] } = {};
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  notifications.forEach((notification) => {
    const date = new Date((notification.timestamp || '').replace(' ', 'T') + 'Z');
    if (isNaN(date.getTime())) {
      // unparseable – put in EARLIER
      if (!groups['EARLIER']) groups['EARLIER'] = [];
      groups['EARLIER'].push(notification);
      return;
    }
    date.setHours(0, 0, 0, 0);

    let key: string;
    if (date.getTime() === today.getTime()) {
      key = 'TODAY';
    } else if (date.getTime() === yesterday.getTime()) {
      key = 'YESTERDAY';
    } else if (date.getTime() > today.getTime() - 7 * 24 * 60 * 60 * 1000) {
      key = 'THIS WEEK';
    } else {
      key = 'EARLIER';
    }

    if (!groups[key]) {
      groups[key] = [];
    }
    groups[key].push(notification);
  });

  return groups;
};

// Get notification category
const getNotificationCategory = (type: NotificationType): NotificationCategory => {
  if (type.startsWith('bid_')) return 'bids';
  if (type.startsWith('payment_')) return 'payments';
  if (
    type.startsWith('project_') ||
    type.startsWith('showcase_') ||
    type.startsWith('milestone_') ||
    type.startsWith('progress_')
  )
    return 'projects';
  if (type.startsWith('dispute_') || type.startsWith('team_')) return 'messages';
  if (type.startsWith('review_')) return 'projects';
  if (type === 'payment_fully_paid') return 'payments';
  if (type === 'admin_announcement') return 'announcements';
  return 'all';
};

export default function Notifications({ userId, userType, onClose, onNavigate }: NotificationsProps) {
  const insets = useSafeAreaInsets();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [filteredNotifications, setFilteredNotifications] = useState<Notification[]>([]);
  const [activeFilters, setActiveFilters] = useState<NotificationCategory[]>([]);
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);
  const [activeRole, setActiveRole] = useState<'contractor' | 'owner' | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [searchFocused, setSearchFocused] = useState(false);
  const [filterModalVisible, setFilterModalVisible] = useState(false);
  const [invitationModalVisible, setInvitationModalVisible] = useState(false);
  const [rejectModalVisible, setRejectModalVisible] = useState(false);
  const [selectedInvitation, setSelectedInvitation] = useState<Notification | null>(null);
  const [rejectReason, setRejectReason] = useState('');
  const [processingInvitationAction, setProcessingInvitationAction] = useState(false);

  const getStaffActionKind = (notification: Notification): StaffActionKind | null => {
    if (notification.reference_type !== 'contractor_staff' || !notification.reference_id) {
      return null;
    }

    const type = (notification.type || '').toLowerCase();
    const title = (notification.title || '').toLowerCase();
    const message = (notification.message || '').toLowerCase();
    const needsDecision = message.includes('please accept or decline');

    // Pending invite action only.
    if (type === 'team_invite' && needsDecision) {
      return 'invite';
    }

    // Pending role-change action only.
    if (type === 'team_role_changed' && (title.includes('role change request') || needsDecision)) {
      return 'role_change';
    }

    // All other contractor_staff notifications are informational.
    return null;
  };

  const categories: { id: NotificationCategory; label: string }[] = [
    { id: 'all', label: 'All' },
    { id: 'projects', label: 'Projects' },
    { id: 'bids', label: 'Bids' },
    { id: 'payments', label: 'Payments' },
    { id: 'messages', label: 'Messages' },
    { id: 'announcements', label: 'Announcements' },
  ];

  useEffect(() => {
    loadNotifications();
  }, []);

  useEffect(() => {
    filterNotifications();
  }, [notifications, activeFilters, searchQuery]);

  const loadNotifications = async (page: number = 1, append: boolean = false) => {
    if (page === 1) setLoading(true);
    else setLoadingMore(true);
    try {
      const response = await notifications_service.get_notifications(page, 20);
      if (response.success && response.data) {
        const items = response.data.notifications || [];
        // Track the active role returned by the backend
        if (response.data.active_role) {
          setActiveRole(response.data.active_role);
        }
        const mapped: Notification[] = items.map((n: ApiNotification) => ({
          id: n.id,
          type: (n.type || 'general') as NotificationType,
          title: n.title || '',
          message: n.message,
          timestamp: n.created_at || '',
          is_read: n.is_read,
          action_url: n.action_url || undefined,
          redirect_url: n.redirect_url || undefined,
          reference_type: n.reference_type,
          reference_id: n.reference_id,
          priority: n.priority || 'normal',
          notification_role: n.notification_role || 'both',
        }));
        if (append) {
          setNotifications(prev => {
            const existingIds = new Set(prev.map(n => n.id));
            const unique = mapped.filter(n => !existingIds.has(n.id));
            return [...prev, ...unique];
          });
        } else {
          setNotifications(mapped);
        }
        setCurrentPage(page);
        // API returns flat pagination: current_page, last_page (no nested pagination object)
        const currentPg = response.data.current_page ?? page;
        const lastPg = response.data.last_page ?? 1;
        setHasMore(currentPg < lastPg);
      }
    } catch (error) {
      console.error('Error loading notifications:', error);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  };

  const filterNotifications = () => {
    const q = searchQuery.trim().toLowerCase();
    let result = notifications;
    if (activeFilters.length > 0) {
      result = result.filter((n) => activeFilters.includes(getNotificationCategory(n.type)));
    }
    if (q) {
      result = result.filter(
        (n) =>
          n.title.toLowerCase().includes(q) ||
          n.message.toLowerCase().includes(q)
      );
    }
    setFilteredNotifications(result);
  };

  const toggleFilter = (cat: NotificationCategory) => {
    if (cat === 'all') {
      setActiveFilters([]);
      return;
    }
    setActiveFilters((prev) =>
      prev.includes(cat) ? prev.filter((c) => c !== cat) : [...prev, cat]
    );
  };

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    await loadNotifications();
    setRefreshing(false);
  }, []);

  const markAsRead = async (notificationId: number) => {
    setNotifications((prev) =>
      prev.map((n) => (n.id === notificationId ? { ...n, is_read: true } : n))
    );
    try {
      await notifications_service.mark_as_read(notificationId);
    } catch (e) {
      console.error('Failed to mark notification as read:', e);
    }
  };

  const markAllAsRead = async (dateGroup?: string) => {
    if (dateGroup) {
      const groupedNotifications = groupNotificationsByDate(filteredNotifications);
      const idsToMark = groupedNotifications[dateGroup]?.map((n) => n.id) || [];
      setNotifications((prev) =>
        prev.map((n) => (idsToMark.includes(n.id) ? { ...n, is_read: true } : n))
      );
      // Mark each in the group individually via API
      for (const id of idsToMark) {
        try { await notifications_service.mark_as_read(id); } catch (e) { /* silent */ }
      }
    } else {
      setNotifications((prev) => prev.map((n) => ({ ...n, is_read: true })));
      try {
        await notifications_service.mark_all_as_read();
      } catch (e) {
        console.error('Failed to mark all as read:', e);
      }
    }
  };

  const handleStaffInvitationAction = async (
    notification: Notification,
    action: 'accept' | 'decline',
    reason?: string
  ) => {
    const actionKind = getStaffActionKind(notification);
    if (!notification.reference_id) {
      Alert.alert('Error', 'Invitation reference is missing.');
      return;
    }

    const endpoint =
      action === 'accept'
        ? api_config.endpoints.contractor_members.accept_invitation(String(notification.reference_id))
        : api_config.endpoints.contractor_members.decline_invitation(String(notification.reference_id));

    try {
      setProcessingInvitationAction(true);
      const response = await api_request(`${endpoint}?user_id=${userId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
        },
        body: action === 'decline' ? JSON.stringify({ reason: reason || '' }) : undefined,
      });

      if (response.success) {
        await markAsRead(notification.id);
        setInvitationModalVisible(false);
        setRejectModalVisible(false);
        setSelectedInvitation(null);
        setRejectReason('');
        Alert.alert(
          actionKind === 'role_change'
            ? (action === 'accept' ? 'Role Change Accepted' : 'Role Change Declined')
            : (action === 'accept' ? 'Invitation Accepted' : 'Invitation Declined'),
          response.message ||
            (actionKind === 'role_change'
              ? (action === 'accept' ? 'Your role change was accepted.' : 'You declined the role change.')
              : (action === 'accept' ? 'You are now part of the team.' : 'You declined the invitation.'))
        );
        await loadNotifications();
      } else {
        const backendMsg = (response.message || '').toLowerCase();
        if (backendMsg.includes('not found') || backendMsg.includes('already processed')) {
          await markAsRead(notification.id);
          setInvitationModalVisible(false);
          setRejectModalVisible(false);
          setSelectedInvitation(null);
          setRejectReason('');
          await loadNotifications();
          Alert.alert('Invitation Cancelled', 'This invitation was cancelled or already processed.');
        } else {
          Alert.alert('Error', response.message || 'Failed to process invitation.');
        }
      }
    } catch (error) {
      console.error('Error processing staff invitation action:', error);
      Alert.alert('Error', 'Failed to process invitation. Please try again.');
    } finally {
      setProcessingInvitationAction(false);
    }
  };

  const isContractorStaffInvitation = (notification: Notification): boolean => {
    return getStaffActionKind(notification) !== null;
  };
  const showInvitationCancelledNotice = (message?: string) => {
    Alert.alert(
      'Invitation was Cancelled',
      message || 'This invitation was cancelled by the owner.'
    );
  };

  const isCancelledStaffInvitationNotice = (notification: Notification): boolean => {
    if (notification.reference_type !== 'contractor_staff') {
      return false;
    }

    const type = (notification.type || '').toLowerCase();
    if (type === 'staff_invitation_cancelled') {
      return true;
    }

    const text = `${notification.title || ''} ${notification.message || ''}`.toLowerCase();
    return (
      text.includes('invitation was cancelled') ||
      text.includes('invitation has been cancelled') ||
      text.includes('invitation cancelled') ||
      text.includes('already processed') ||
      text.includes('no longer available')
    );
  };

  const handleNotificationPress = async (notification: Notification) => {
    // 1. Dedicated cancellation notice — always show alert, never open modal or redirect.
    //    The backend always routes staff_invitation_cancelled → members, so we must
    //    intercept it here before any API call.
    if ((notification.type as string) === 'staff_invitation_cancelled') {
      if (!notification.is_read) {
        await markAsRead(notification.id);
      }
      showInvitationCancelledNotice(notification.message || 'This invitation was cancelled by the owner.');
      return;
    }

    // 2. Pending invite / role-change action — check status first, then open modal.
    const staffActionKind = getStaffActionKind(notification);
    if (staffActionKind) {
      // Pre-check: if the staff record is already cancelled, skip the modal entirely.
      if (notification.reference_id) {
        try {
          const statusRes = await api_request(
            `${api_config.endpoints.contractor_members.show(String(notification.reference_id))}?user_id=${userId}`,
            { method: 'GET' }
          );
          const statusData = statusRes?.data?.data ?? statusRes?.data;
          if (!statusRes.success || statusData?.is_cancelled) {
            if (!notification.is_read) await markAsRead(notification.id);
            showInvitationCancelledNotice(
              statusData?.deletion_reason
                ? `This invitation was cancelled. Reason: ${statusData.deletion_reason}`
                : 'This invitation was cancelled by the owner.'
            );
            return;
          }
        } catch {
          // If status check fails, fall through and let the modal handle it
        }
      }
      setSelectedInvitation(notification);
      setInvitationModalVisible(true);
      return;
    }

    // 3. Types with no navigation target — mark as read and exit silently
    if (notification.type === 'admin_announcement' || notification.type === 'general') {
      if (!notification.is_read) void markAsRead(notification.id);
      return;
    }

    // 4. All other notifications — optimistic read + backend redirect
    setNotifications((prev) =>
      prev.map((n) => (n.id === notification.id ? { ...n, is_read: true } : n))
    );

    try {
      const response = await notifications_service.resolve_redirect(notification.id);

      if (response.success && response.data) {
        if (response.data.flash_message) {
          Alert.alert('Notice', response.data.flash_message);
          return;
        }

        const mobile = response.data.mobile;
        if (mobile && onNavigate) {
          onClose();
          onNavigate(mobile.screen, mobile.params || {});
          return;
        }
      }
    } catch (error) {
      console.error('Error resolving notification redirect:', error);
      try {
        await notifications_service.mark_as_read(notification.id);
      } catch (e) {
        // silent
      }
    }
  };

  const getUnreadCount = (category: NotificationCategory): number => {
    if (category === 'all') {
      return notifications.filter((n) => !n.is_read).length;
    }
    return notifications.filter(
      (n) => !n.is_read && getNotificationCategory(n.type) === category
    ).length;
  };

  const renderCategoryTab = (category: { id: NotificationCategory; label: string }) => {
    const isSelected = category.id === 'all' ? activeFilters.length === 0 : activeFilters.includes(category.id);
    const unreadCount = getUnreadCount(category.id);

    return (
      <TouchableOpacity
        key={category.id}
        style={[styles.categoryTab, isSelected && styles.categoryTabActive]}
        onPress={() => setSelectedCategory(category.id)}
      >
        <Text style={[styles.categoryTabText, isSelected && styles.categoryTabTextActive]}>
          {category.label}
        </Text>
        {unreadCount > 0 && (
          <View style={[styles.categoryBadge, isSelected && styles.categoryBadgeActive]}>
            <Text style={[styles.categoryBadgeText, isSelected && styles.categoryBadgeTextActive]}>
              {unreadCount > 99 ? '99+' : unreadCount}
            </Text>
          </View>
        )}
      </TouchableOpacity>
    );
  };

  const renderNotificationItem = (notification: Notification) => {
    const defaultStyle = getNotificationStyle(notification.type);
    const isWelcome = notification.type === 'general' && notification.title.toLowerCase().includes('welcome');
    const staffActionKind = getStaffActionKind(notification);
    const style = isWelcome
      ? { icon: 'gift-outline' as const, iconComponent: 'ionicons', bgColor: '#FEF9C3', iconColor: '#B45309' }
      : !!staffActionKind
      ? {
          icon: 'person-add-outline' as const,
          iconComponent: 'ionicons',
          bgColor: COLORS.primaryLight,
          iconColor: COLORS.primary,
        }
      : defaultStyle;
    const typeLabel = getTypeLabel(notification.type);

    // ── Admin Announcement ── special banner card ──────────────────────────────
    if (notification.type === 'admin_announcement') {
      const priority = notification.priority || 'normal';
      const priorityColor =
        priority === 'critical' ? COLORS.error :
        priority === 'high'     ? COLORS.warning :
                                  '#7C3AED';
      const priorityBg =
        priority === 'critical' ? COLORS.errorLight :
        priority === 'high'     ? COLORS.warningLight :
                                  '#EDE9FE';

      return (
        <TouchableOpacity
          key={notification.id}
          style={[styles.announcementItem, !notification.is_read && styles.announcementItemUnread]}
          onPress={() => handleNotificationPress(notification)}
          activeOpacity={0.7}
        >
          <View style={styles.announcementBadgeRow}>
            <View style={[styles.announcementBadge, { backgroundColor: priorityBg }]}>
              <Ionicons name="megaphone-outline" size={12} color={priorityColor} style={{ marginRight: 4 }} />
              <Text style={[styles.announcementBadgeText, { color: priorityColor }]}>ANNOUNCEMENT</Text>
            </View>
            {priority !== 'normal' && (
              <View style={[styles.announcementPriorityBadge, { backgroundColor: priorityBg }]}>
                <Text style={[styles.announcementBadgeText, { color: priorityColor }]}>
                  {priority.toUpperCase()}
                </Text>
              </View>
            )}
            <Text style={styles.notificationTime}>{formatRelativeTime(notification.timestamp)}</Text>
          </View>

          <View style={styles.announcementBody}>
            <View style={[styles.announcementIcon, { backgroundColor: priorityBg }]}>
              <Ionicons name="megaphone-outline" size={22} color={priorityColor} />
            </View>
            <View style={styles.notificationContent}>
              <Text style={[styles.notificationTitle, { color: COLORS.text }]} numberOfLines={2}>
                {notification.title}
              </Text>
              <Text style={styles.notificationMessage} numberOfLines={3}>
                {notification.message}
              </Text>
            </View>
            {!notification.is_read && <View style={styles.unreadDot} />}
          </View>
        </TouchableOpacity>
      );
    }
    // ─────────────────────────────────────────────────────────────────────────

    return (
      <TouchableOpacity
        key={notification.id}
        style={[
          styles.notificationItem,
          !notification.is_read && styles.notificationItemUnread,
          { borderLeftWidth: 4, borderLeftColor: style.iconColor },
        ]}
        onPress={() => handleNotificationPress(notification)}
        activeOpacity={0.7}
      >
        <View style={[styles.notificationIcon, { backgroundColor: style.bgColor }]}>
          {style.iconComponent === 'text' ? (
            <Text style={{ fontSize: 24, fontWeight: 'bold', color: style.iconColor }}>
              {style.icon}
            </Text>
          ) : (
            <Ionicons name={style.icon} size={22} color={style.iconColor} />
          )}
        </View>
        <View style={styles.notificationContent}>
          <View style={styles.notificationHeader}>
            <Text style={styles.notificationTitle} numberOfLines={2}>
              {notification.title}
            </Text>
            <Text style={styles.notificationTime}>
              {formatRelativeTime(notification.timestamp)}
            </Text>
          </View>
          <Text style={styles.notificationMessage} numberOfLines={2}>
            {notification.message}
          </Text>
          <View style={[styles.typePill, { backgroundColor: style.bgColor }]}>
            {style.iconComponent === 'text' ? (
              <Text style={{ fontSize: 11, fontWeight: 'bold', color: style.iconColor, marginRight: 4 }}>
                {style.icon}
              </Text>
            ) : (
              <Ionicons name={style.icon} size={11} color={style.iconColor} style={{ marginRight: 4 }} />
            )}
            <Text style={[styles.typePillText, { color: style.iconColor }]}>{typeLabel}</Text>
          </View>
        </View>
        {!notification.is_read && <View style={styles.unreadDot} />}
      </TouchableOpacity>
    );
  };

  const renderDateGroup = (dateLabel: string, items: Notification[]) => (
    <View key={dateLabel} style={styles.dateGroup}>
      <View style={styles.dateGroupHeader}>
        <Text style={styles.dateGroupLabel}>{dateLabel}</Text>
      </View>
      {items.map(renderNotificationItem)}
    </View>
  );

  const renderEmptyState = () => (
    <View style={styles.emptyState}>
      <View style={styles.emptyIconContainer}>
        <Ionicons name="notifications-off-outline" size={64} color={COLORS.textMuted} />
      </View>
      <Text style={styles.emptyTitle}>No Notifications</Text>
      <Text style={styles.emptyMessage}>
        {searchQuery.trim()
          ? `No results for "${searchQuery.trim()}".`
          : activeFilters.length === 0
          ? "You're all caught up! Check back later for updates."
          : activeFilters.length === 1 && activeFilters[0] === 'announcements'
          ? 'No announcements from admin yet.'
          : `No ${activeFilters.map((f) => categories.find((c) => c.id === f)?.label ?? f).join(', ')} notifications yet.`}
      </Text>
    </View>
  );

  const groupedNotifications = groupNotificationsByDate(filteredNotifications);
  const dateOrder = ['TODAY', 'YESTERDAY', 'THIS WEEK', 'EARLIER'];
  const selectedInvitationActionKind = selectedInvitation ? getStaffActionKind(selectedInvitation) : null;

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backButton} onPress={onClose}>
          <Feather name="chevron-left" size={28} color={COLORS.text} />
        </TouchableOpacity>
        <View style={{ alignItems: 'center', flex: 1 }}>
          <Text style={styles.headerTitle}>Notifications</Text>
          {userType === 'both' && activeRole && (
            <Text style={{
              fontSize: 11,
              color: COLORS.textMuted,
              marginTop: 1,
              textTransform: 'capitalize',
            }}>
              Viewing as {activeRole === 'owner' ? 'Property Owner' : 'Contractor'}
            </Text>
          )}
        </View>
        <TouchableOpacity
          style={styles.markAllHeaderButton}
          onPress={() => markAllAsRead()}
          disabled={notifications.filter((n) => !n.is_read).length === 0}
        >
          <Text style={[
            styles.markAllHeaderText,
            notifications.filter((n) => !n.is_read).length === 0 && { opacity: 0.3 },
          ]}>
            Mark all as read
          </Text>
        </TouchableOpacity>
      </View>

      {/* Search + Filter Row */}
      <View style={styles.searchContainer}>
        <View style={[styles.searchInputWrapper, searchFocused && styles.searchInputWrapperFocused]}>
          <Feather name="search" size={16} color={searchFocused ? COLORS.primary : COLORS.textMuted} style={{ marginRight: 8 }} />
          <TextInput
            style={styles.searchInput}
            placeholder="Search notifications..."
            placeholderTextColor={COLORS.textMuted}
            value={searchQuery}
            onChangeText={setSearchQuery}
            onFocus={() => setSearchFocused(true)}
            onBlur={() => setSearchFocused(false)}
            returnKeyType="search"
            clearButtonMode="while-editing"
          />
          {searchQuery.length > 0 && (
            <TouchableOpacity onPress={() => setSearchQuery('')} hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}>
              <Feather name="x" size={16} color={COLORS.textMuted} />
            </TouchableOpacity>
          )}
        </View>
        <TouchableOpacity
          style={[styles.filterButton, activeFilters.length > 0 && styles.filterButtonActive]}
          onPress={() => setFilterModalVisible(true)}
          hitSlop={{ top: 4, bottom: 4, left: 4, right: 4 }}
        >
          <Ionicons
            size={20}
            color={activeFilters.length > 0 ? COLORS.surface : COLORS.textSecondary}
          />
          {activeFilters.length > 0 && <View style={styles.filterActiveDot} />}
        </TouchableOpacity>
      </View>

      {/* Active filter chips */}
      {activeFilters.length > 0 && (
        <View style={styles.activeFilterRow}>
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.activeFilterScrollContent}
          >
            <Text style={styles.activeFilterLabel}>Filters:</Text>
            {activeFilters.map((f) => (
              <View key={f} style={styles.activeFilterChip}>
                <Text style={styles.activeFilterChipText}>
                  {categories.find((c) => c.id === f)?.label ?? f}
                </Text>
                <TouchableOpacity
                  onPress={() => setActiveFilters((prev) => prev.filter((c) => c !== f))}
                  hitSlop={{ top: 6, bottom: 6, left: 6, right: 6 }}
                >
                  <Feather name="x" size={12} color={COLORS.primary} style={{ marginLeft: 4 }} />
                </TouchableOpacity>
              </View>
            ))}
            <TouchableOpacity onPress={() => setActiveFilters([])} style={styles.activeFilterClearBtn}>
              <Text style={styles.activeFilterClearText}>Clear all</Text>
            </TouchableOpacity>
          </ScrollView>
          <Text style={styles.activeFilterCount}>
            {filteredNotifications.length} result{filteredNotifications.length !== 1 ? 's' : ''}
          </Text>
        </View>
      )}

      {/* Notifications List */}
      <ScrollView
        style={styles.notificationsList}
        contentContainerStyle={styles.notificationsContent}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={COLORS.primary}
            colors={[COLORS.primary]}
          />
        }
        showsVerticalScrollIndicator={false}
      >
        {loading ? (
          <View style={styles.loadingContainer}>
            <Text style={styles.loadingText}>Loading notifications...</Text>
          </View>
        ) : filteredNotifications.length === 0 ? (
          renderEmptyState()
        ) : (
          <>
            {dateOrder.map((dateLabel) => {
              const items = groupedNotifications[dateLabel];
              if (!items || items.length === 0) return null;
              return renderDateGroup(dateLabel, items);
            })}
            {hasMore && (
              <TouchableOpacity
                style={styles.loadMoreButton}
                onPress={() => loadNotifications(currentPage + 1, true)}
                disabled={loadingMore}
              >
                {loadingMore ? (
                  <ActivityIndicator size="small" color={COLORS.primary} />
                ) : (
                  <Text style={styles.loadMoreText}>Load more</Text>
                )}
              </TouchableOpacity>
            )}
          </>
        )}
      </ScrollView>

      <Modal
        visible={invitationModalVisible}
        transparent
        animationType="fade"
        onRequestClose={() => setInvitationModalVisible(false)}
      >
        <View style={styles.inviteModalOverlay}>
          <View style={styles.inviteModalCard}>
            <Text style={styles.inviteModalTitle}>
              {selectedInvitationActionKind === 'role_change' ? 'Role Change Request' : 'Contractor Team Invitation'}
            </Text>
            <Text style={styles.inviteModalMessage}>
              {selectedInvitation?.message ||
                (selectedInvitationActionKind === 'role_change'
                  ? 'A role change request is waiting for your response.'
                  : 'You have been invited to join a contractor team.')}
            </Text>

            <View style={styles.inviteModalActions}>
              <TouchableOpacity
                style={[styles.inviteModalBtn, styles.inviteLaterBtn]}
                onPress={() => setInvitationModalVisible(false)}
                disabled={processingInvitationAction}
              >
                <Text style={styles.inviteLaterText}>Later</Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.inviteModalBtn, styles.inviteDeclineBtn]}
                onPress={() => {
                  setInvitationModalVisible(false);
                  setRejectModalVisible(true);
                }}
                disabled={processingInvitationAction}
              >
                <Text style={styles.inviteDeclineText}>
                  {selectedInvitationActionKind === 'role_change' ? 'Decline' : 'Reject'}
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.inviteModalBtn, styles.inviteAcceptBtn]}
                onPress={() => {
                  if (selectedInvitation) {
                    void handleStaffInvitationAction(selectedInvitation, 'accept');
                  }
                }}
                disabled={processingInvitationAction}
              >
                {processingInvitationAction ? (
                  <ActivityIndicator size="small" color="#FFFFFF" />
                ) : (
                  <Text style={styles.inviteAcceptText}>
                    {selectedInvitationActionKind === 'role_change' ? 'Accept Change' : 'Accept'}
                  </Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      <Modal
        visible={rejectModalVisible}
        transparent
        animationType="fade"
        onRequestClose={() => setRejectModalVisible(false)}
      >
        <View style={styles.inviteModalOverlay}>
          <View style={styles.inviteModalCard}>
            <Text style={styles.inviteModalTitle}>
              {selectedInvitationActionKind === 'role_change' ? 'Decline Role Change' : 'Reject Invitation'}
            </Text>
            <Text style={styles.inviteModalMessage}>
              {selectedInvitationActionKind === 'role_change'
                ? 'Please provide a reason for declining this role change.'
                : 'Please provide a reason for rejecting this invitation.'}
            </Text>

            <TextInput
              style={styles.rejectReasonInput}
              placeholder="Type your reason"
              placeholderTextColor={COLORS.textMuted}
              value={rejectReason}
              onChangeText={setRejectReason}
              multiline
              numberOfLines={4}
              textAlignVertical="top"
              maxLength={500}
            />

            <View style={styles.inviteModalActions}>
              <TouchableOpacity
                style={[styles.inviteModalBtn, styles.inviteLaterBtn]}
                onPress={() => {
                  setRejectModalVisible(false);
                  setRejectReason('');
                }}
                disabled={processingInvitationAction}
              >
                <Text style={styles.inviteLaterText}>Cancel</Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.inviteModalBtn, styles.inviteDeclineBtn]}
                onPress={() => {
                  if (!rejectReason.trim()) {
                    Alert.alert(
                      'Reason Required',
                      selectedInvitationActionKind === 'role_change'
                        ? 'Please enter a reason before declining this role change.'
                        : 'Please enter a reason before rejecting.'
                    );
                    return;
                  }
                  if (selectedInvitation) {
                    void handleStaffInvitationAction(selectedInvitation, 'decline', rejectReason.trim());
                  }
                }}
                disabled={processingInvitationAction}
              >
                {processingInvitationAction ? (
                  <ActivityIndicator size="small" color="#FFFFFF" />
                ) : (
                  <Text style={styles.inviteDeclineText}>
                    {selectedInvitationActionKind === 'role_change' ? 'Submit Decline' : 'Submit Rejection'}
                  </Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Filter Modal */}
      <Modal
        visible={filterModalVisible}
        transparent
        animationType="fade"
        onRequestClose={() => setFilterModalVisible(false)}
      >
        <TouchableOpacity
          style={styles.filterModalOverlay}
          activeOpacity={1}
          onPress={() => setFilterModalVisible(false)}
        >
          <TouchableOpacity activeOpacity={1} style={styles.filterModalCard}>
            <View style={styles.filterModalHeader}>
              <Text style={styles.filterModalTitle}>Filter by Type</Text>
              <TouchableOpacity onPress={() => setFilterModalVisible(false)}>
                <Feather name="x" size={20} color={COLORS.textSecondary} />
              </TouchableOpacity>
            </View>
            <View style={styles.filterOptionsGrid}>
              {categories.map((cat) => {
                const isActive = cat.id === 'all' ? activeFilters.length === 0 : activeFilters.includes(cat.id);
                const unread = getUnreadCount(cat.id);
                return (
                  <TouchableOpacity
                    key={cat.id}
                    style={[styles.filterOptionItem, isActive && styles.filterOptionItemActive]}
                    onPress={() => toggleFilter(cat.id)}
                  >
                    <Text style={[styles.filterOptionLabel, isActive && styles.filterOptionLabelActive]}>
                      {cat.label}
                    </Text>
                    {unread > 0 && (
                      <View style={[styles.filterOptionBadge, isActive && styles.filterOptionBadgeActive]}>
                        <Text style={[styles.filterOptionBadgeText, isActive && styles.filterOptionBadgeTextActive]}>
                          {unread > 99 ? '99+' : unread}
                        </Text>
                      </View>
                    )}
                    {isActive && (
                      <Ionicons name="checkmark" size={14} color={isActive && cat.id !== 'all' ? COLORS.surface : COLORS.text} style={{ marginLeft: 'auto' }} />
                    )}
                  </TouchableOpacity>
                );
              })}
            </View>
            <TouchableOpacity
              style={styles.filterDoneBtn}
              onPress={() => setFilterModalVisible(false)}
            >
              <Text style={styles.filterDoneBtnText}>Done</Text>
            </TouchableOpacity>
          </TouchableOpacity>
        </TouchableOpacity>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  backButton: {
    width: 40,
    height: 40,
    alignItems: 'flex-start',
    justifyContent: 'center',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
  },
  settingsButton: {
    width: 40,
    height: 40,
    alignItems: 'flex-end',
    justifyContent: 'center',
  },
  markAllHeaderButton: {
    alignItems: 'flex-end',
    justifyContent: 'center',
    paddingRight: 2,
  },
  markAllHeaderText: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.primary,
    flexShrink: 1,
  },
  searchContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
    gap: 10,
  },
  searchInputWrapper: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.border,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  searchInputWrapperFocused: {
    borderColor: COLORS.primary,
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
    color: COLORS.text,
    paddingVertical: 0,
  },
  filterButton: {
    width: 42,
    height: 42,
    borderRadius: 6,
    backgroundColor: COLORS.borderLight,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
    position: 'relative',
  },
  filterButtonActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  filterActiveDot: {
    position: 'absolute',
    top: 6,
    right: 6,
    width: 7,
    height: 7,
    borderRadius: 4,
    backgroundColor: COLORS.surface,
  },
  activeFilterRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
    backgroundColor: COLORS.primaryLight,
    borderBottomWidth: 1,
    borderBottomColor: '#FFD9A8',
  },
  activeFilterScrollContent: {
    alignItems: 'center',
    paddingHorizontal: 16,
    gap: 8,
  },
  activeFilterLabel: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginRight: 6,
  },
  activeFilterChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: 6,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderWidth: 1,
    borderColor: COLORS.primary,
    marginRight: 0,
  },
  activeFilterChipText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.primary,
  },
  activeFilterCount: {
    paddingHorizontal: 12,
    fontSize: 12,
    color: COLORS.textSecondary,
    flexShrink: 0,
  },
  activeFilterClearBtn: {
    paddingHorizontal: 6,
    paddingVertical: 2,
  },
  activeFilterClearText: {
    fontSize: 12,
    color: COLORS.textSecondary,
    textDecorationLine: 'underline',
  },
  // ── Filter Modal ──────────────────────────────────────────────────────────
  filterModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.45)',
    justifyContent: 'center',
    paddingHorizontal: 20,
  },
  filterModalCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 8,
    padding: 18,
  },
  filterModalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  filterModalTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.text,
  },
  filterOptionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  filterOptionItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 6,
    borderWidth: 1.5,
    borderColor: COLORS.border,
    backgroundColor: COLORS.background,
    minWidth: '45%',
    flex: 1,
  },
  filterOptionItemActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  filterOptionLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.text,
  },
  filterOptionLabelActive: {
    color: COLORS.surface,
    fontWeight: '600',
  },
  filterOptionBadge: {
    marginLeft: 8,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 5,
    backgroundColor: COLORS.borderLight,
    minWidth: 22,
    alignItems: 'center',
  },
  filterOptionBadgeActive: {
    backgroundColor: 'rgba(255,255,255,0.3)',
  },
  filterOptionBadgeText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  filterOptionBadgeTextActive: {
    color: COLORS.surface,
  },
  filterDoneBtn: {
    marginTop: 14,
    backgroundColor: COLORS.primary,
    borderRadius: 6,
    paddingVertical: 11,
    alignItems: 'center',
  },
  filterDoneBtnText: {
    color: COLORS.surface,
    fontWeight: '700',
    fontSize: 14,
  },
  categoryContainer: {
    backgroundColor: COLORS.surface,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  categoryScrollContent: {
    paddingHorizontal: 16,
    gap: 8,
  },
  categoryTab: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
    marginRight: 8,
  },
  categoryTabActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  categoryTabText: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },
  categoryTabTextActive: {
    color: COLORS.surface,
  },
  categoryBadge: {
    marginLeft: 6,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 10,
    backgroundColor: COLORS.borderLight,
    minWidth: 20,
    alignItems: 'center',
  },
  categoryBadgeActive: {
    backgroundColor: 'rgba(255, 255, 255, 0.3)',
  },
  categoryBadgeText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  categoryBadgeTextActive: {
    color: COLORS.surface,
  },
  notificationsList: {
    flex: 1,
  },
  notificationsContent: {
    paddingBottom: 24,
  },
  loadingContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingTop: 60,
  },
  loadingText: {
    fontSize: 14,
    color: COLORS.textMuted,
  },
  dateGroup: {
    marginTop: 16,
  },
  dateGroupHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    marginBottom: 8,
  },
  dateGroupLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textMuted,
    letterSpacing: 0.5,
  },
  markAllReadText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
  },
  notificationItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: COLORS.surface,
    paddingHorizontal: 20,
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  notificationItemUnread: {
    backgroundColor: '#FFFBF5',
  },
  notificationIcon: {
    width: 44,
    height: 44,
    borderRadius: 22,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 14,
  },
  notificationContent: {
    flex: 1,
  },
  notificationHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 4,
  },
  notificationTitle: {
    flex: 1,
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
    lineHeight: 20,
    marginRight: 8,
  },
  notificationTime: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  notificationMessage: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: 6,
  },
  typePill: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
    marginTop: 2,
  },
  typePillText: {
    fontSize: 11,
    fontWeight: '600',
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.primary,
    marginLeft: 8,
    marginTop: 6,
  },
  emptyState: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingTop: 80,
    paddingHorizontal: 40,
  },
  emptyIconContainer: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: COLORS.borderLight,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 24,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  emptyMessage: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
  },
  loadMoreButton: {
    alignItems: 'center',
    paddingVertical: 16,
    marginTop: 8,
  },
  loadMoreText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.primary,
  },
  inviteModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.45)',
    justifyContent: 'center',
    paddingHorizontal: 20,
  },
  inviteModalCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
  },
  inviteModalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  inviteModalMessage: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: 14,
  },
  inviteModalActions: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    alignItems: 'center',
  },
  inviteModalBtn: {
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 9,
    marginLeft: 8,
    minWidth: 78,
    alignItems: 'center',
  },
  inviteLaterBtn: {
    backgroundColor: COLORS.borderLight,
  },
  inviteAcceptBtn: {
    backgroundColor: COLORS.success,
  },
  inviteDeclineBtn: {
    backgroundColor: COLORS.error,
  },
  inviteLaterText: {
    color: COLORS.text,
    fontWeight: '600',
    fontSize: 13,
  },
  inviteAcceptText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 13,
  },
  inviteDeclineText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 13,
  },
  rejectReasonInput: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 8,
    minHeight: 96,
    paddingHorizontal: 10,
    paddingVertical: 10,
    color: COLORS.text,
    marginBottom: 12,
    backgroundColor: COLORS.background,
  },
  // ── Announcement banner styles ────────────────────────────────────────────
  announcementItem: {
    backgroundColor: '#FAFAFE',
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
    borderLeftWidth: 4,
    borderLeftColor: '#7C3AED',
  },
  announcementItemUnread: {
    backgroundColor: '#F5F3FF',
  },
  announcementBadgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
    gap: 6,
  },
  announcementBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
  },
  announcementPriorityBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
  },
  announcementBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    letterSpacing: 0.4,
  },
  announcementBody: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  announcementIcon: {
    width: 44,
    height: 44,
    borderRadius: 22,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 14,
  },
});
