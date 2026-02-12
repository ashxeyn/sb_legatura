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
} from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather, MaterialIcons, Ionicons } from '@expo/vector-icons';
import { notifications_service, Notification as ApiNotification } from '../../services/notifications_service';

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
  // Team
  | 'team_invite'
  | 'team_removed'
  | 'team_role_changed'
  | 'team_access_changed'
  // Fallback
  | 'general';

type NotificationCategory = 'all' | 'projects' | 'bids' | 'payments' | 'messages';

interface Notification {
  id: number;
  type: NotificationType;
  title: string;
  message: string;
  timestamp: string;
  is_read: boolean;
  action_url?: string;
  priority?: string;
}

interface NotificationsProps {
  userId: number;
  userType: 'property_owner' | 'contractor' | 'both';
  onClose: () => void;
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

    // ── Team / Members ── primary ──
    case 'team_invite':
    case 'team_role_changed':
    case 'team_access_changed':
      return {
        icon: 'people-outline' as const,
        iconComponent: 'ionicons',
        bgColor: COLORS.primaryLight,
        iconColor: COLORS.primary,
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
  const date = new Date(timestamp.replace(' ', 'T')); // ensure ISO-like parse
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
    const date = new Date((notification.timestamp || '').replace(' ', 'T'));
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
    type.startsWith('milestone_') ||
    type.startsWith('progress_')
  )
    return 'projects';
  if (type.startsWith('dispute_') || type.startsWith('team_')) return 'messages';
  return 'all';
};

export default function Notifications({ userId, userType, onClose }: NotificationsProps) {
  const insets = useSafeAreaInsets();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [filteredNotifications, setFilteredNotifications] = useState<Notification[]>([]);
  const [selectedCategory, setSelectedCategory] = useState<NotificationCategory>('all');
  const [refreshing, setRefreshing] = useState(false);
  const [loading, setLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);

  const categories: { id: NotificationCategory; label: string }[] = [
    { id: 'all', label: 'All' },
    { id: 'projects', label: 'Projects' },
    { id: 'bids', label: 'Bids' },
    { id: 'payments', label: 'Payments' },
    { id: 'messages', label: 'Messages' },
  ];

  useEffect(() => {
    loadNotifications();
  }, []);

  useEffect(() => {
    filterNotifications();
  }, [notifications, selectedCategory]);

  const loadNotifications = async (page: number = 1, append: boolean = false) => {
    if (page === 1) setLoading(true);
    else setLoadingMore(true);
    try {
      const response = await notifications_service.get_notifications(page, 20);
      if (response.success && response.data) {
        const items = response.data.notifications || [];
        const mapped: Notification[] = items.map((n: ApiNotification) => ({
          id: n.id,
          type: (n.type || 'general') as NotificationType,
          title: n.title || '',
          message: n.message,
          timestamp: n.created_at || '',
          is_read: n.is_read,
          action_url: n.action_url || undefined,
          priority: n.priority || 'normal',
        }));
        if (append) {
          setNotifications(prev => [...prev, ...mapped]);
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
    if (selectedCategory === 'all') {
      setFilteredNotifications(notifications);
    } else {
      setFilteredNotifications(
        notifications.filter((n) => getNotificationCategory(n.type) === selectedCategory)
      );
    }
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

  const handleNotificationPress = (notification: Notification) => {
    markAsRead(notification.id);
    // TODO: Navigate to relevant screen based on notification type
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
    const isSelected = selectedCategory === category.id;
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
    const style = getNotificationStyle(notification.type);
    const typeLabel = getTypeLabel(notification.type);

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

  const renderDateGroup = (dateLabel: string, items: Notification[]) => {
    const unreadInGroup = items.filter((n) => !n.is_read).length;

    return (
      <View key={dateLabel} style={styles.dateGroup}>
        <View style={styles.dateGroupHeader}>
          <Text style={styles.dateGroupLabel}>{dateLabel}</Text>
          {unreadInGroup > 0 && (
            <TouchableOpacity onPress={() => markAllAsRead(dateLabel)}>
              <Text style={styles.markAllReadText}>Mark all as read</Text>
            </TouchableOpacity>
          )}
        </View>
        {items.map(renderNotificationItem)}
      </View>
    );
  };

  const renderEmptyState = () => (
    <View style={styles.emptyState}>
      <View style={styles.emptyIconContainer}>
        <Ionicons name="notifications-off-outline" size={64} color={COLORS.textMuted} />
      </View>
      <Text style={styles.emptyTitle}>No Notifications</Text>
      <Text style={styles.emptyMessage}>
        {selectedCategory === 'all'
          ? "You're all caught up! Check back later for updates."
          : `No ${selectedCategory} notifications yet.`}
      </Text>
    </View>
  );

  const groupedNotifications = groupNotificationsByDate(filteredNotifications);
  const dateOrder = ['TODAY', 'YESTERDAY', 'THIS WEEK', 'EARLIER'];

  return (
    <SafeAreaView style={styles.container} edges={['top']}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backButton} onPress={onClose}>
          <Feather name="chevron-left" size={28} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Notifications</Text>
        <TouchableOpacity
          style={styles.settingsButton}
          onPress={() => {
            // TODO: Navigate to notification settings
          }}
        >
          <Feather name="settings" size={22} color={COLORS.textSecondary} />
        </TouchableOpacity>
      </View>

      {/* Category Tabs */}
      <View style={styles.categoryContainer}>
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.categoryScrollContent}
        >
          {categories.map(renderCategoryTab)}
        </ScrollView>
      </View>

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
    borderRadius: 10,
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
});
