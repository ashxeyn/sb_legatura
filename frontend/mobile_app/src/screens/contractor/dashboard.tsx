// @ts-nocheck
import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Image,
  ActivityIndicator,
  RefreshControl,
  Animated,
  Dimensions,
  Modal,
  FlatList,
  AppState,
} from 'react-native';
import { View as SafeAreaView, StatusBar, Platform, DeviceEventEmitter } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, MaterialCommunityIcons, Ionicons, Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { projects_service } from '../../services/projects_service';
import { summary_service } from '../../services/summary_service';
import { api_config } from '../../config/api';
import { useContractorAuth } from '../../hooks/useContractorAuth';
import ImageFallback from '../../components/imageFallback';

const defaultContractorAvatar = require('../../../assets/images/pictures/contractor_default.png');

import MyProjects from './myProjects';
import MyBids from './myBids';
import Members from './members';
import AiAnalytics from './aiAnalytics';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

interface Project {
  project_id: number;
  project_title: string;
  project_description: string;
  project_location: string;
  budget_range_min: number;
  budget_range_max: number;
  property_type: string;
  type_name: string;
  project_status: string;
  bidding_deadline?: string;
  owner_name?: string;
  owner_profile_pic?: string;
  created_at: string;
  display_status?: string;
}

interface Bid {
  bid_id: number;
  project_id: number;
  project_title: string;
  project_location?: string;
  proposed_cost: number;
  estimated_timeline: number;
  contractor_notes?: string;
  bid_status: 'pending' | 'accepted' | 'rejected' | 'withdrawn' | 'cancelled' | 'submitted';
  submitted_at: string;
}

interface DashboardProps {
  userData?: {
    user_id?: number;
    username?: string;
    email?: string;
    profile_pic?: string;
    company_name?: string;
    contractor_type?: string;
    years_of_experience?: number;
  };
  onNotificationsPress?: () => void;
  onNavigateToMessages?: () => void;
  onBrowseProjects?: () => void;
  onFullScreenChange?: (isFullScreen: boolean) => void;
}

// Color palette - modern and professional (matching owner dashboard)
const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
  primaryDeep: '#B35E00',
  secondary: '#1A1A2E',
  accent: '#16213E',
  success: '#10B981',
  successLight: '#D1FAE5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
  info: '#3B82F6',
  infoLight: '#DBEAFE',
  background: '#F8FAFC',
  surface: '#FFFFFF',
  surfaceHover: '#F1F5F9',
  text: '#0F172A',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
};

export default function ContractorDashboard({
  userData,
  onNotificationsPress,
  onNavigateToMessages,
  onBrowseProjects,
  onFullScreenChange
}: DashboardProps) {
  const insets = useSafeAreaInsets();
  const [myBids, setMyBids] = useState<Bid[]>([]);
  const [myProjects, setMyProjects] = useState<Project[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  // pinned bid feature removed
  // pin modals removed
  const [avatarError, setAvatarError] = useState(false);
  const [showMyProjects, setShowMyProjects] = useState(false);
  const [showMyBids, setShowMyBids] = useState(false);
  const [showMembers, setShowMembers] = useState(false);
  const [showAiAnalytics, setShowAiAnalytics] = useState(false);
  const [myProjectsInitialAction, setMyProjectsInitialAction] = useState<{
    type: 'milestone_setup' | 'project_timeline' | 'project_detail';
    project_id: number;
    initial_item_id?: number;
    initial_item_tab?: 'payments';
  } | null>(null);
  const [activeMilestoneItems, setActiveMilestoneItems] = useState<any[]>([]);
  const [loadingMilestoneItems, setLoadingMilestoneItems] = useState(false);
  const scrollY = useRef(new Animated.Value(0)).current;
  const hasInitialized = useRef(false);

  // Contractor member authorization and capability flags for role-based dashboard sections
  const { canManageMembers, canViewMembers, canBid, canViewFinancials } = useContractorAuth();

  // Get status bar height (top inset)
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  // Notify parent when entering/exiting full-screen mode
  useEffect(() => {
    const isFullScreen = showMyProjects || showMyBids || showAiAnalytics;
    onFullScreenChange?.(isFullScreen);
  }, [showMyProjects, showMyBids, showAiAnalytics, onFullScreenChange]);

  useEffect(() => {
    setAvatarError(false);
    fetchData();
    hasInitialized.current = true;
  }, [userData?.user_id, canBid]);

  // Auto-refresh dashboard data on relevant app events
  useEffect(() => {
    const handleRefresh = () => {
      if (!showMyProjects && !showMyBids && !showMembers && !showAiAnalytics) {
        fetchData();
      }
    };

    const roleChangedSub = DeviceEventEmitter.addListener('roleChanged', handleRefresh);
    const dashboardRefreshSub = DeviceEventEmitter.addListener('dashboardRefresh', handleRefresh);
    const appStateSub = AppState.addEventListener('change', (state) => {
      if (state === 'active') handleRefresh();
    });

    return () => {
      try { roleChangedSub.remove(); } catch (e) {}
      try { dashboardRefreshSub.remove(); } catch (e) {}
      try { appStateSub.remove(); } catch (e) {}
    };
  }, [showMyProjects, showMyBids, showMembers, showAiAnalytics, userData?.user_id, canBid]);

  // Refresh parent dashboard when coming back from sub-screens
  useEffect(() => {
    if (!hasInitialized.current) return;
    if (!showMyProjects && !showMyBids && !showMembers && !showAiAnalytics) {
      fetchData();
    }
  }, [showMyProjects, showMyBids, showMembers, showAiAnalytics]);

  // Listen for navigation events from notifications
  useEffect(() => {
    const sub = DeviceEventEmitter.addListener('dashboardNavigate', (params: Record<string, any>) => {
      const subScreen = params.sub_screen;
      if (subScreen === 'my_bids') {
        setShowMyBids(true);
      } else if (subScreen === 'project_detail') {
        // Map initial_action from backend to MyProjects initialAction types
        const actionType = params.initial_action === 'project_timeline'
          ? 'project_timeline'
          : params.initial_action === 'milestone_setup'
            ? 'milestone_setup'
            : 'project_detail';
        if (params.project_id) {
          setMyProjectsInitialAction({
            type: actionType,
            project_id: params.project_id,
            initial_item_id: params.initial_item_id,
            initial_item_tab: params.initial_item_tab,
          });
        } else {
          setMyProjectsInitialAction(null);
        }
        setShowMyProjects(true);
      } else if (subScreen === 'members') {
        setShowMembers(true);
      } else if (subScreen === 'projects') {
        setMyProjectsInitialAction(null);
        setShowMyProjects(true);
      }
    });
    return () => sub.remove();
  }, []);

  const fetchData = async () => {
    if (!userData?.user_id) {
      setIsLoading(false);
      return;
    }

    try {
      setIsLoading(true);
      setError(null);

      // Fetch contractor projects
      const projectsResponse = await projects_service.get_contractor_projects(userData.user_id);
      if (projectsResponse.success) {
        const projectsData = projectsResponse.data?.data || projectsResponse.data || [];
        const projectsArray = Array.isArray(projectsData) ? projectsData : [];
        setMyProjects(projectsArray);
        fetchMilestoneItems(projectsArray);
      } else {
        setMyProjects([]);
      }

      // Fetch contractor bids only for roles that are allowed to bid.
      if (canBid) {
        const bidsResponse = await projects_service.get_my_bids(userData.user_id);
        console.log('Bids response:', JSON.stringify(bidsResponse, null, 2));
        if (bidsResponse.success) {
          // The API returns data wrapped, so we need to extract it properly
          const apiData = bidsResponse.data;
          const bidsData = apiData?.data || apiData || [];
          const bidsArray = Array.isArray(bidsData) ? bidsData : [];
          console.log('Bids array length:', bidsArray.length);
          console.log('Bids array:', bidsArray);
          setMyBids(bidsArray);
        } else {
          console.log('Bids fetch failed:', bidsResponse.message);
          setMyBids([]);
        }
      } else {
        setMyBids([]);
      }

      setIsLoading(false);
    } catch (err) {
      console.error('Error fetching dashboard data:', err);
      setError('Failed to load data');
      setMyProjects([]);
      setMyBids([]);
      setIsLoading(false);
    }
  };

  const fetchMilestoneItems = async (projectsArray: any[]) => {
    if (!projectsArray.length) { setActiveMilestoneItems([]); return; }
    setLoadingMilestoneItems(true);
    try {
      const results: any[] = [];
      await Promise.all(
        projectsArray.map(async (project) => {
          try {
            const res = await summary_service.getProjectSummary(project.project_id);
            if (res.success && res.data?.milestones) {
              const items = res.data.milestones.filter((m: any) => m.status !== 'completed');
              items.forEach((item: any) => {
                results.push({ ...item, project_id: project.project_id, project_title: project.project_title, project_obj: project });
              });
            }
          } catch (e) { console.warn('fetchMilestones err for project', project.project_id); }
        })
      );
      results.sort((a, b) => (a.sequence_order || 0) - (b.sequence_order || 0));
      setActiveMilestoneItems(results);
    } catch (e) { console.error('fetchMilestoneItems error:', e); }
    finally { setLoadingMilestoneItems(false); }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchData();
    setRefreshing(false);
  };

  // Calculate stats
  const stats = {
    totalBids: myBids.length,
    pendingBids: myBids.filter(b => b.bid_status === 'pending' || b.bid_status === 'submitted').length,
    acceptedBids: myBids.filter(b => b.bid_status === 'accepted').length,
    activeProjects: myProjects.length, // Count all projects (in progress + waiting milestone setup)
  };

  const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
  };

  const getPaymentStatusColor = (status: string) => {
    switch (status) {
      case 'Fully Paid': return '#22c55e';
      case 'Partially Paid': return '#f59e0b';
      case 'Overdue': return '#ef4444';
      default: return '#94a3b8';
    }
  };

  const getMilestoneStatusColor = (status: string) => {
    switch (status) {
      case 'completed': return '#22c55e';
      case 'in_progress': return '#3b82f6';
      case 'terminated': return '#b91c1c';
      case 'halt': return '#ef4444';
      default: return '#94a3b8';
    }
  };

  const getDueDateUrgency = (dueDateStr: string | null) => {
    if (!dueDateStr) return null;
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const due = new Date(dueDateStr + 'T00:00:00');
    const diff = Math.ceil((due.getTime() - today.getTime()) / 86400000);
    if (diff < 0) return { label: `${Math.abs(diff)}d overdue`, color: '#dc2626' };
    if (diff === 0) return { label: 'Due today', color: '#dc2626' };
    if (diff <= 3) return { label: `${diff}d left`, color: '#ea580c' };
    if (diff <= 7) return { label: `${diff}d left`, color: '#d97706' };
    return { label: `${diff}d left`, color: '#16a34a' };
  };

  const renderMilestoneItemCard = (item: any, index: number) => {
    const totalPaid = parseFloat(item.total_paid) || 0;
    const expected = parseFloat(item.current_allocation) || parseFloat(item.original_allocation) || 0;
    const remaining = parseFloat(item.remaining) || 0;
    const adjustedCost = item.adjusted_cost != null ? parseFloat(item.adjusted_cost) : null;
    const carryForward = item.carry_forward_amount != null ? parseFloat(item.carry_forward_amount) : 0;
    const progressPct = expected > 0 ? Math.min(100, (totalPaid / expected) * 100) : 0;
    const statusColor = getMilestoneStatusColor(item.status);
    const payStatusColor = getPaymentStatusColor(item.payment_status || 'Unpaid');
    const dueUrgency = getDueDateUrgency(item.settlement_due_date);
    const statusLabel = item.status === 'not_started' ? 'Not Started'
      : item.status === 'in_progress' ? 'In Progress'
      : item.status === 'completed' ? 'Completed'
      : item.status === 'terminated' ? 'Terminated'
      : item.status === 'halt' ? 'Halted' : item.status;

    return (
      <View key={`${item.item_id}-${item.project_id}`} style={styles.msCard}>
        <Text style={styles.msProjectBadge} numberOfLines={1}>{item.project_title}</Text>
        <View style={styles.msTitleRow}>
          <View style={{ flex: 1, marginRight: 8 }}>
            <Text style={styles.msItemLabel}>MILESTONE ITEM {index + 1}</Text>
            <Text style={styles.msItemTitle} numberOfLines={2}>{item.title}</Text>
          </View>
          <View style={[styles.msStatusBadge, { backgroundColor: statusColor + '18' }]}>
            <View style={[styles.msStatusDot, { backgroundColor: statusColor }]} />
            <Text style={[styles.msStatusText, { color: statusColor }]}>{statusLabel}</Text>
          </View>
        </View>
        <View style={styles.msFinGrid}>
          <View style={styles.msFinItem}>
            <Text style={styles.msFinLabel}>REQUIRED</Text>
            {adjustedCost !== null && carryForward > 0 ? (
              <>
                <Text style={[styles.msFinValue, { color: '#dc2626' }]} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
                  ₱{adjustedCost.toLocaleString('en-US', { minimumFractionDigits: 0 })}
                </Text>
                <View style={{ flexDirection: 'row', alignItems: 'center', gap: 3, marginTop: 2 }}>
                  <Text style={{ fontSize: 10, color: '#94a3b8', textDecorationLine: 'line-through' }}>
                    ₱{(parseFloat(item.original_allocation) || 0).toLocaleString('en-US', { minimumFractionDigits: 0 })}
                  </Text>
                  <View style={styles.msCfBadge}><Text style={styles.msCfBadgeText}>+CF</Text></View>
                </View>
              </>
            ) : (
              <Text style={styles.msFinValue} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
                ₱{expected.toLocaleString('en-US', { minimumFractionDigits: 0 })}
              </Text>
            )}
          </View>
          <View style={styles.msFinDivider} />
          <View style={styles.msFinItem}>
            <Text style={styles.msFinLabel}>PAID</Text>
            <Text style={[styles.msFinValue, { color: '#10B981' }]} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
              ₱{totalPaid.toLocaleString('en-US', { minimumFractionDigits: 0 })}
            </Text>
          </View>
          <View style={styles.msFinDivider} />
          <View style={styles.msFinItem}>
            <Text style={styles.msFinLabel}>REMAINING</Text>
            <Text style={[styles.msFinValue, { color: remaining > 0 ? COLORS.primary : '#10B981' }]} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
              ₱{remaining.toLocaleString('en-US', { minimumFractionDigits: 0 })}
            </Text>
          </View>
        </View>
        <View style={styles.msProgressBg}>
          <View style={[styles.msProgressFill, { width: `${progressPct}%` as any }]} />
        </View>
        <View style={styles.msFooter}>
          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 5 }}>
            <View style={[styles.msStatusDot, { backgroundColor: payStatusColor, width: 7, height: 7 }]} />
            <Text style={{ fontSize: 12, fontWeight: '700', color: payStatusColor }}>{item.payment_status || 'Unpaid'}</Text>
          </View>
          {item.settlement_due_date ? (
            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
              <Feather name="calendar" size={11} color={dueUrgency?.color || '#94a3b8'} />
              <Text style={{ fontSize: 11, color: '#64748B' }}>
                Due {new Date(item.settlement_due_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
              </Text>
              {dueUrgency && (
                <View style={{ backgroundColor: dueUrgency.color + '18', borderRadius: 3, paddingHorizontal: 5, paddingVertical: 1 }}>
                  <Text style={{ fontSize: 9, fontWeight: '700', color: dueUrgency.color }}>{dueUrgency.label}</Text>
                </View>
              )}
            </View>
          ) : (
            <Text style={{ fontSize: 11, color: '#94a3b8' }}>No due date</Text>
          )}
        </View>
        <TouchableOpacity
          style={styles.msDetailsBtn}
          onPress={() => {
            setMyProjectsInitialAction({
              type: 'project_detail',
              project_id: item.project_id,
              initial_item_id: item.item_id,
            });
            setShowMyProjects(true);
          }}
          activeOpacity={0.7}
        >
          <Feather name="file-text" size={13} color={COLORS.primary} />
          <Text style={{ fontSize: 12, fontWeight: '600', color: COLORS.primary }}>View full details &amp; payment history</Text>
          <Feather name="chevron-right" size={13} color={COLORS.primary} />
        </TouchableOpacity>
      </View>
    );
  };

  // Header animation
  const headerOpacity = scrollY.interpolate({
    inputRange: [0, 100],
    outputRange: [1, 0.9],
    extrapolate: 'clamp',
  });

  if (isLoading && !refreshing) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar hidden={true} />
        <View style={{ flex: 1, padding: 20 }}>
           <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 24, justifyContent: 'space-between' }}>
             <View style={{ flexDirection: 'row', alignItems: 'center' }}>
               <View style={{ width: 48, height: 48, borderRadius: 24, backgroundColor: '#E5E7EB', marginRight: 12 }} />
               <View>
                 <View style={{ width: 100, height: 14, backgroundColor: '#E5E7EB', borderRadius: 4, marginBottom: 8 }} />
                 <View style={{ width: 140, height: 18, backgroundColor: '#E5E7EB', borderRadius: 4 }} />
               </View>
             </View>
             <View style={{ width: 32, height: 32, borderRadius: 16, backgroundColor: '#E5E7EB' }} />
           </View>

           <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 30 }}>
             <View style={{ width: '48%', height: 90, borderRadius: 12, backgroundColor: '#E5E7EB' }} />
             <View style={{ width: '48%', height: 90, borderRadius: 12, backgroundColor: '#E5E7EB' }} />
           </View>

           <View style={{ width: 150, height: 20, backgroundColor: '#E5E7EB', borderRadius: 6, marginBottom: 16 }} />

           {[1, 2, 3].map((_, i) => (
             <View key={i} style={{ backgroundColor: '#fff', borderRadius: 12, padding: 16, marginBottom: 16, borderColor: '#F3F4F6', borderWidth: 1 }}>
               <View style={{ flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12 }}>
                 <View style={{ width: 120, height: 16, backgroundColor: '#E5E7EB', borderRadius: 4 }} />
                 <View style={{ width: 60, height: 24, backgroundColor: '#E5E7EB', borderRadius: 12 }} />
               </View>
               <View style={{ width: '100%', height: 14, backgroundColor: '#E5E7EB', borderRadius: 4, marginBottom: 8 }} />
               <View style={{ width: '80%', height: 14, backgroundColor: '#E5E7EB', borderRadius: 4, marginBottom: 16 }} />
               <View style={{ flexDirection: 'row', gap: 12 }}>
                 <View style={{ width: 80, height: 14, backgroundColor: '#E5E7EB', borderRadius: 4 }} />
                 <View style={{ width: 80, height: 14, backgroundColor: '#E5E7EB', borderRadius: 4 }} />
               </View>
             </View>
           ))}
        </View>
      </SafeAreaView>
    );
  }

  // Show My Projects screen if selected
  if (showMyProjects) {
    return (
      <MyProjects
        userData={userData}
        initialProjects={myProjects}
        onClose={() => {
          setShowMyProjects(false);
          setMyProjectsInitialAction(null);
          fetchData();
        }}
        initialAction={myProjectsInitialAction}
      />
    );
  }

  // Show My Bids screen if selected
  if (showMyBids) {
    return (
      <MyBids
        userData={userData}
        initialBids={myBids}
        onClose={() => {
          setShowMyBids(false);
          fetchData();
        }}
      />
    );
  }

  // Show Members screen if selected
  if (showMembers) {
    return (
      <Members
        userData={userData}
        onClose={() => {
          setShowMembers(false);
          fetchData();
        }}
      />
    );
  }

  // Show AI Analytics screen if selected
  if (showAiAnalytics) {
    return (
      <AiAnalytics
        userData={userData}
        onClose={() => {
          setShowAiAnalytics(false);
          fetchData();
        }}
      />
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar hidden={true} />

      <Animated.ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        onScroll={Animated.event(
          [{ nativeEvent: { contentOffset: { y: scrollY } } }],
          { useNativeDriver: true }
        )}
        scrollEventThrottle={16}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            colors={[COLORS.primary]}
            tintColor={COLORS.primary}
          />
        }
      >
        {/* Header Card — rectangular, milestoneDetail style */}
        <View style={styles.headerCard}>
          <View style={styles.headerCardInner}>
            <View style={{ flex: 1 }}>
              <Text style={styles.headerGreeting}>{getGreeting()}</Text>
              <Text style={styles.headerName}>{userData?.company_name || userData?.username || 'Contractor'}</Text>
              {userData?.contractor_type ? (
                <View style={styles.headerRoleBadge}>
                  <Feather name="briefcase" size={10} color={COLORS.primary} />
                  <Text style={styles.headerRoleText}>{userData.contractor_type}</Text>
                </View>
              ) : (
                <View style={styles.headerRoleBadge}>
                  <Feather name="briefcase" size={10} color={COLORS.primary} />
                  <Text style={styles.headerRoleText}>Contractor</Text>
                </View>
              )}
            </View>
          </View>
        </View>


        {/* Stats Row — milestoneDetail summaryMetricsRow style */}
        <View style={styles.statGrid}>
          <View style={styles.statMetricsRow}>
            <View style={styles.statMetric}>
              <Text style={styles.statMetricLabel}>Active</Text>
              <Text style={styles.statMetricValue}>{stats.activeProjects}</Text>
            </View>
            <View style={styles.statMetricDivider} />
            <View style={styles.statMetric}>
              <Text style={styles.statMetricLabel}>My Bids</Text>
              <Text style={styles.statMetricValue}>{stats.totalBids}</Text>
            </View>
            <View style={styles.statMetricDivider} />
            <View style={styles.statMetric}>
              <Text style={styles.statMetricLabel}>Won</Text>
              <Text style={styles.statMetricValue}>{stats.acceptedBids}</Text>
            </View>
            <View style={styles.statMetricDivider} />
            <View style={styles.statMetric}>
              <Text style={styles.statMetricLabel}>Pending</Text>
              <Text style={styles.statMetricValue}>{stats.pendingBids}</Text>
            </View>
          </View>
        </View>

        {/* Pinned bid feature removed */}

        {/* My Projects & My Bids Navigation Section */}
        <View style={styles.projectsNavSection}>
          <Text style={styles.sectionTitle}>My Work</Text>

          <View style={styles.navButtonsContainer}>
            <TouchableOpacity
              style={styles.navButton}
              activeOpacity={0.7}
              onPress={() => setShowMyProjects(true)}
            >
              <View style={[styles.navButtonIcon, { backgroundColor: COLORS.successLight }]}>
                <Ionicons name="briefcase" size={18} color={COLORS.success} />
              </View>
              <View style={styles.navButtonContent}>
                <Text style={styles.navButtonTitle}>My Projects</Text>
                <Text style={styles.navButtonSubtitle}>{stats.activeProjects} active projects</Text>
              </View>
              <Feather name="chevron-right" size={20} color={COLORS.textMuted} />
            </TouchableOpacity>

            {canBid && (
              <TouchableOpacity
                style={styles.navButton}
                activeOpacity={0.7}
                onPress={() => setShowMyBids(true)}
              >
                <View style={[styles.navButtonIcon, styles.bidsNavIconFrame]}>
                  <View style={styles.bidsScaleBadge}>
                      <MaterialCommunityIcons name="scale-balance" size={20} color="#F0B35E" />
                  </View>
                </View>
                <View style={styles.navButtonContent}>
                  <Text style={styles.navButtonTitle}>My Bids</Text>
                  <Text style={styles.navButtonSubtitle}>{stats.totalBids} bids submitted</Text>
                </View>
                <Feather name="chevron-right" size={20} color={COLORS.textMuted} />
              </TouchableOpacity>
            )}

            {/* Members button - visible to all active contractor members */}
            {canViewMembers && (
              <TouchableOpacity
                style={styles.navButton}
                activeOpacity={0.7}
                onPress={() => setShowMembers(true)}
              >
                <View style={[styles.navButtonIcon, { backgroundColor: '#E8F6FF' }]}>
                  <Ionicons name="people" size={18} color={COLORS.info} />
                </View>
                <View style={styles.navButtonContent}>
                  <Text style={styles.navButtonTitle}>Members</Text>
                  <Text style={styles.navButtonSubtitle}>{canManageMembers ? 'Manage your team' : 'View your team'}</Text>
                </View>
                <Feather name="chevron-right" size={20} color={COLORS.textMuted} />
              </TouchableOpacity>
            )}

            {canViewFinancials && (
              <TouchableOpacity
                style={styles.navButton}
                activeOpacity={0.7}
                onPress={() => setShowAiAnalytics(true)}
              >
                <View style={[styles.navButtonIcon, styles.navAiIconFrame]}>
                  <Image
                    source={require('../../../assets/images/icons/ai.png')}
                    style={styles.aiIconImage}
                    resizeMode="contain"
                  />
                </View>
                <View style={styles.navButtonContent}>
                  <Text style={styles.navButtonTitle}>AI Analytics</Text>
                  <Text style={styles.navButtonSubtitle}>Predict project delays</Text>
                </View>
                <Feather name="chevron-right" size={20} color={COLORS.textMuted} />
              </TouchableOpacity>
            )}
          </View>
        </View>

        {/* Active Milestone Items */}
        {(loadingMilestoneItems || activeMilestoneItems.length > 0) && (
          <View style={styles.msSection}>
            <Text style={styles.sectionTitle}>Active Milestone Items</Text>
            {loadingMilestoneItems ? (
              <View style={{ alignItems: 'center', paddingVertical: 24 }}>
                <ActivityIndicator size="small" color={COLORS.primary} />
              </View>
            ) : (
              activeMilestoneItems.map((item, idx) => renderMilestoneItemCard(item, idx))
            )}
          </View>
        )}

      </Animated.ScrollView>

      {/* Pinned bid modals removed */}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 100,
  },

  // Loading State
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingSpinner: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 12,
    elevation: 5,
  },
  loadingText: {
    marginTop: 16,
    fontSize: 15,
    color: COLORS.textSecondary,
    fontWeight: '500',
  },

  // Hero Header
  heroHeader: {
    marginTop: -1,
  },
  heroGradient: {
    paddingTop: 12,
    paddingBottom: 24,
    paddingHorizontal: 20,
    borderBottomLeftRadius: 28,
    borderBottomRightRadius: 28,
  },
  heroContent: {
    gap: 20,
  },
  heroTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  userInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  avatarContainer: {
    position: 'relative',
  },
  avatar: {
    width: 46,
    height: 46,
    borderRadius: 23,
    borderWidth: 2,
    borderColor: 'rgba(255,255,255,0.3)',
  },
  avatarPlaceholder: {
    width: 46,
    height: 46,
    borderRadius: 23,
    backgroundColor: 'rgba(255,255,255,0.25)',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: 'rgba(255,255,255,0.4)',
  },
  avatarText: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: '700',
  },
  onlineIndicator: {
    position: 'absolute',
    bottom: 1,
    right: 1,
    width: 12,
    height: 12,
    borderRadius: 6,
    backgroundColor: '#4ADE80',
    borderWidth: 2,
    borderColor: COLORS.primary,
  },
  greetingContainer: {
    gap: 1,
  },
  greeting: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.9)',
    fontWeight: '500',
  },
  userName: {
    fontSize: 18,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  userType: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.8)',
    fontWeight: '500',
    marginTop: 2,
  },

  // Quick Summary
  quickSummary: {
    flexDirection: 'row',
    backgroundColor: 'rgba(255,255,255,0.18)',
    borderRadius: 14,
    paddingVertical: 12,
    paddingHorizontal: 8,
  },
  summaryItem: {
    flex: 1,
    alignItems: 'center',
  },
  summaryValue: {
    fontSize: 20,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  summaryLabel: {
    fontSize: 10,
    color: 'rgba(255,255,255,0.85)',
    marginTop: 2,
    fontWeight: '500',
  },
  summaryDivider: {
    width: 1,
    backgroundColor: 'rgba(255,255,255,0.25)',
    marginVertical: 4,
  },

  // Section Headers
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 2,
  },
  sectionTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  sectionTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 14,
  },
  seeAllBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  seeAllText: {
    fontSize: 14,
    color: COLORS.primary,
    fontWeight: '600',
  },

  // Pinned Section
  pinnedSection: {
    paddingTop: 24,
    paddingHorizontal: 20,
  },
  pinnedCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    marginTop: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  pinnedEmpty: {
    alignItems: 'center',
    paddingVertical: 32,
    paddingHorizontal: 20,
  },
  pinnedEmptyText: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginTop: 12,
  },
  pinnedEmptySubtext: {
    fontSize: 13,
    color: COLORS.textMuted,
    marginTop: 4,
  },
  pinnedProjectContent: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
  },
  pinnedProjectInfo: {
    flex: 1,
  },
  pinnedProjectTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  pinnedProjectLocation: {
    fontSize: 13,
    color: COLORS.textMuted,
    marginBottom: 8,
  },
  pinnedProjectMeta: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  pinnedStatusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
    marginRight: 10,
  },
  pinnedStatusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  pinnedBudget: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.primary,
  },
  pinnedOptionsButton: {
    padding: 8,
  },

  // ── Stats Metrics Row (milestoneDetail style) ──
  statGrid: {
    paddingHorizontal: 16,
    paddingTop: 14,
    paddingBottom: 4,
  },
  statMetricsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 10,
    backgroundColor: '#F1F5F9',
    borderRadius: 4,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  statMetric: {
    flex: 1,
    alignItems: 'center',
  },
  statMetricLabel: {
    fontSize: 10,
    color: '#64748B',
    marginBottom: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    fontWeight: '600',
    textAlign: 'center',
  },
  statMetricValue: {
    fontSize: 16,
    fontWeight: '700',
    color: '#0F172A',
    textAlign: 'center',
  },
  statMetricDivider: {
    width: 1,
    height: 36,
    backgroundColor: '#E2E8F0',
  },

  // ── Header Card (rectangular, milestoneDetail style) ──
  headerCard: {
    marginHorizontal: 16,
    marginTop: 14,
    backgroundColor: '#FFFFFF',
    borderRadius: 4,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.06,
    shadowRadius: 4,
    elevation: 2,
  },
  headerCardInner: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    gap: 12,
  },
  headerGreeting: {
    fontSize: 11,
    color: '#64748B',
    fontWeight: '500',
    marginBottom: 2,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  headerName: {
    fontSize: 20,
    fontWeight: '700',
    color: '#0F172A',
    marginBottom: 6,
  },
  headerRoleBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    alignSelf: 'flex-start',
    backgroundColor: COLORS.primaryLight,
    borderRadius: 4,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  headerRoleText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.primary,
  },

  // Projects Navigation Section
  projectsNavSection: {
    paddingTop: 24,
    paddingHorizontal: 20,
  },

  // Active Milestone Items Section
  msSection: {
    paddingTop: 20,
    paddingHorizontal: 20,
  },
  msCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 4,
    padding: 16,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
    marginBottom: 12,
  },
  msProjectBadge: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.primary,
    letterSpacing: 0.5,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  msTitleRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 14,
    gap: 8,
  },
  msItemLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: '#94A3B8',
    letterSpacing: 1,
    textTransform: 'uppercase',
    marginBottom: 3,
  },
  msItemTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: '#0F172A',
    lineHeight: 22,
  },
  msStatusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  msStatusDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  msStatusText: {
    fontSize: 10,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  msFinGrid: {
    flexDirection: 'row',
    backgroundColor: '#F1F5F9',
    borderRadius: 4,
    paddingVertical: 10,
    paddingHorizontal: 8,
    marginBottom: 10,
  },
  msFinItem: {
    flex: 1,
    alignItems: 'center',
  },
  msFinDivider: {
    width: 1,
    backgroundColor: '#E2E8F0',
    marginVertical: -2,
  },
  msFinLabel: {
    fontSize: 9,
    fontWeight: '700',
    color: '#94A3B8',
    letterSpacing: 0.8,
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  msFinValue: {
    fontSize: 13,
    fontWeight: '700',
    color: '#0F172A',
    textAlign: 'center',
  },
  msProgressBg: {
    height: 4,
    backgroundColor: '#F1F5F9',
    borderRadius: 2,
    overflow: 'hidden',
    marginBottom: 10,
  },
  msProgressFill: {
    height: '100%' as any,
    backgroundColor: '#10B981',
    borderRadius: 2,
  },
  msFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  msCfBadge: {
    backgroundColor: '#dc2626',
    borderRadius: 3,
    paddingHorizontal: 5,
    paddingVertical: 2,
  },
  msCfBadgeText: {
    fontSize: 9,
    fontWeight: '800',
    color: '#fff',
    letterSpacing: 0.3,
  },
  msDetailsBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 8,
    borderTopWidth: 1,
    borderTopColor: '#E2E8F0',
    marginTop: 4,
  },
  navButtonsContainer: {
    marginTop: 0,
  },
  navButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: 8,
    padding: 16,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  navButtonIcon: {
    width: 34,
    height: 34,
    borderRadius: 8,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  navAiIconFrame: {
    backgroundColor: '#FFF2D9',
    borderWidth: 1,
    borderColor: '#F9D8A7',
    width: 34,
    height: 34,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  bidsNavIconFrame: {
    backgroundColor: '#EEF4FF',
    borderWidth: 1,
    borderColor: '#D6E2F3',
    width: 34,
    height: 34,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  bidsScaleBadge: {
    width: 34,
    height: 34,
    borderRadius: 17,
    backgroundColor: '#3E5563',
    alignItems: 'center',
    justifyContent: 'center',
  },
  aiIconImage: {
    width: 28,
    height: 28,
  },
  navButtonContent: {
    flex: 1,
  },
  navButtonTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
  },
  navButtonSubtitle: {
    fontSize: 13,
    color: COLORS.textMuted,
    marginTop: 3,
  },

  // Quick Actions Section
  quickActionsSection: {
    paddingHorizontal: 20,
    paddingTop: 20,
  },
  quickActionsGrid: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  quickActionCard: {
    flex: 1,
    alignItems: 'center',
    paddingHorizontal: 4,
  },
  quickActionGradient: {
    width: 48,
    height: 48,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  quickActionIcon: {
    width: 48,
    height: 48,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  quickActionLabel: {
    fontSize: 11,
    color: COLORS.textSecondary,
    textAlign: 'center',
    fontWeight: '500',
    lineHeight: 14,
  },

  // Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  pinModalContainer: {
    backgroundColor: COLORS.surface,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    maxHeight: '70%',
    paddingBottom: 30,
  },
  pinModalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  pinModalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
  },
  pinModalEmpty: {
    alignItems: 'center',
    paddingVertical: 50,
    paddingHorizontal: 20,
  },
  pinModalEmptyText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginTop: 16,
  },
  pinModalEmptySubtext: {
    fontSize: 14,
    color: COLORS.textMuted,
    marginTop: 6,
  },
  pinModalList: {
    paddingHorizontal: 16,
    paddingTop: 12,
  },
  pinModalItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    borderRadius: 12,
    padding: 14,
    marginBottom: 10,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  pinModalItemSelected: {
    borderColor: COLORS.primary,
    backgroundColor: COLORS.primaryLight,
  },
  pinModalItemContent: {
    flex: 1,
  },
  pinModalItemTitle: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 3,
  },
  pinModalItemLocation: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginBottom: 6,
  },
  pinModalItemStatus: {
    alignSelf: 'flex-start',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  pinModalItemStatusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  pinModalItemAction: {
    marginLeft: 12,
  },
  pinOptionsContainer: {
    position: 'absolute',
    bottom: 50,
    left: 20,
    right: 20,
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    paddingVertical: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 12,
    elevation: 8,
  },
  pinOptionItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 20,
  },
  pinOptionText: {
    fontSize: 16,
    fontWeight: '500',
    color: COLORS.text,
    marginLeft: 14,
  },
  pinOptionDivider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginHorizontal: 16,
  },
});

