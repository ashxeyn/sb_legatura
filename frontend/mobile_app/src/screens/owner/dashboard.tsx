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
  Alert,
  AppState,
} from 'react-native';
import { View as SafeAreaView, StatusBar, Platform, DeviceEventEmitter } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons, Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { projects_service } from '../../services/projects_service';
import { summary_service } from '../../services/summary_service';
import { api_config } from '../../config/api';
import { storage_service } from '../../utils/storage';
import ImageFallback from '../../components/imageFallback';
import ProjectDetails from './projectDetails';
import ProjectList from './projectList';

const defaultOwnerAvatar = require('../../../assets/images/pictures/property_owner_default.png');

import CreateProject from './createProject';
import SearchScreen from '../both/searchScreen';

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
  project_post_status: string;
  bidding_deadline?: string;
  created_at: string;
  bids_count?: number;
}

interface DashboardProps {
  userData?: {
    user_id?: number;
    username?: string;
    email?: string;
    profile_pic?: string;
  };
  onNavigateToMessages?: () => void;
}

// Color palette - modern and professional
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

export default function PropertyOwnerDashboard({ userData, onNavigateToMessages }: DashboardProps) {
  const insets = useSafeAreaInsets();
  const [projects, setProjects] = useState<Project[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [activeFilter, setActiveFilter] = useState('all');
  const [selectedProject, setSelectedProject] = useState<Project | null>(null);
  const [showProjectList, setShowProjectList] = useState(false);
  const [showCreateProject, setShowCreateProject] = useState(false);
  const [showSearchScreen, setShowSearchScreen] = useState(false);
  // pinned project feature removed
  const [avatarError, setAvatarError] = useState(false);
  const [contractorTypes, setContractorTypes] = useState<any[]>([]);
  const [projectInitialSection, setProjectInitialSection] = useState<'bids' | 'milestones' | null>(null);
  const [projectInitialItemId, setProjectInitialItemId] = useState<number | null>(null);
  const [projectInitialItemTab, setProjectInitialItemTab] = useState<'payments' | null>(null);
  const [pendingNavigate, setPendingNavigate] = useState<Record<string, any> | null>(null);
  const [activeMilestoneItems, setActiveMilestoneItems] = useState<any[]>([]);
  const [loadingMilestoneItems, setLoadingMilestoneItems] = useState(false);
  const scrollY = useRef(new Animated.Value(0)).current;
  const hasInitialized = useRef(false);

  // Get status bar height (top inset)
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  useEffect(() => {
    console.log('Dashboard - userData:', userData);
    console.log('Dashboard - user_id:', userData?.user_id);
    console.log('Dashboard - profile_pic URL:', userData?.profile_pic);
    setAvatarError(false); // Reset avatar error when userData changes
    fetchProjects();
    fetchContractorTypes();
    hasInitialized.current = true;
    // pinned project loading removed
  }, [userData?.user_id]);

  // Auto-refresh every 15 seconds (only when on main dashboard, not in sub-screens)
  useEffect(() => {
    if (!userData?.user_id || showProjectList || selectedProject || showCreateProject || showSearchScreen) {
      return;
    }
    
    const interval = setInterval(() => {
      fetchProjects(true); // Silent refresh
    }, 60000);

    return () => clearInterval(interval);
  }, [userData?.user_id, showProjectList, selectedProject, showCreateProject, showSearchScreen]);

  // Auto-refresh owner dashboard data on relevant app events
  useEffect(() => {
    const handleRefresh = () => {
      if (!showProjectList && !selectedProject && !showCreateProject && !showSearchScreen) {
        fetchProjects();
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
  }, [showProjectList, selectedProject, showCreateProject, showSearchScreen, userData?.user_id]);

  // Refresh parent dashboard when coming back from sub-screens
  useEffect(() => {
    if (!hasInitialized.current) return;
    if (!showProjectList && !selectedProject && !showCreateProject && !showSearchScreen) {
      fetchProjects();
    }
  }, [showProjectList, selectedProject, showCreateProject, showSearchScreen]);

  // Listen for navigation events from notifications — just store the intent, don't look up projects here
  // because projects may not be loaded yet when the event fires (fresh tab mount race condition).
  useEffect(() => {
    const sub = DeviceEventEmitter.addListener('dashboardNavigate', (params: Record<string, any>) => {
      setPendingNavigate(params);
    });
    return () => sub.remove();
  }, []);

  // Process pending navigation once projects have finished loading
  useEffect(() => {
    if (!pendingNavigate || isLoading) return;

    const params = pendingNavigate;
    const subScreen = params.sub_screen;

    if (subScreen === 'project_detail' || subScreen === 'projects') {
      const section = params.initial_section === 'bids' ? 'bids'
        : params.initial_section === 'milestones' ? 'milestones'
        : null;

      if (params.project_id) {
        const target = projects.find((p: any) => p.project_id === params.project_id);
        if (target) {
          setPendingNavigate(null);
          setProjectInitialSection(section);
          setProjectInitialItemId(params.initial_item_id ?? null);
          setProjectInitialItemTab(params.initial_item_tab ?? null);
          setSelectedProject(target);
          return;
        }
      }
      // project_id not found (or not provided) — fall back to project list
      setPendingNavigate(null);
      setProjectInitialSection(null);
      setProjectInitialItemId(null);
      setProjectInitialItemTab(null);
      setShowProjectList(true);
    } else {
      setPendingNavigate(null);
    }
  }, [pendingNavigate, projects, isLoading]);

  const fetchContractorTypes = async () => {
    try {
      const response = await projects_service.get_contractor_types();
      if (response.success && response.data) {
        setContractorTypes(response.data);
      }
    } catch (error) {
      console.error('Error fetching contractor types:', error);
    }
  };

  const fetchProjects = async (silent = false) => {
    console.log('fetchProjects called, user_id:', userData?.user_id);

    if (!userData?.user_id) {
      console.log('No user_id, setting error');
      if (!silent) setIsLoading(false);
      setError('User not logged in');
      return;
    }

    try {
      if (!silent) setIsLoading(true);
      setError(null);

      console.log('Calling API with user_id:', userData.user_id);
      const response = await projects_service.get_owner_projects(userData.user_id);
      console.log('API Response:', JSON.stringify(response, null, 2));

      if (response.success) {
        const backendResponse = response.data;
        const projectsData = backendResponse?.data || backendResponse || [];
        const projectsArray = Array.isArray(projectsData) ? projectsData : [];
        console.log('Projects loaded:', projectsArray.length, projectsArray);
        setProjects(projectsArray);
        // Fetch active milestone items for in-progress projects
        fetchMilestoneItems(projectsArray, silent);
      } else {
        console.log('API error:', response.message);
        setError(response.message || 'Failed to load projects');
      }
    } catch (err) {
      console.error('Error fetching projects:', err);
      setError('Failed to load projects');
    } finally {
      if (!silent) setIsLoading(false);
    }
  };

  const fetchMilestoneItems = async (projectsArray: Project[], silent = false) => {
    const inProgressProjects = projectsArray.filter(
      p => p.project_status === 'in_progress' || p.project_status === 'bidding_closed'
    );
    if (inProgressProjects.length === 0) {
      setActiveMilestoneItems([]);
      return;
    }
    if (!silent) setLoadingMilestoneItems(true);
    try {
      const results: any[] = [];
      await Promise.all(
        inProgressProjects.map(async (project) => {
          try {
            const res = await summary_service.getProjectSummary(project.project_id);
            if (res.success && res.data?.milestones) {
              const items = res.data.milestones.filter(
                (m: any) => m.status !== 'completed'
              );
              items.forEach((item: any) => {
                results.push({
                  ...item,
                  project_id: project.project_id,
                  project_title: project.project_title,
                  project_obj: project,
                });
              });
            }
          } catch (e) {
            console.warn('Failed to fetch milestones for project', project.project_id);
          }
        })
      );
      // Sort by sequence_order
      results.sort((a, b) => (a.sequence_order || 0) - (b.sequence_order || 0));
      setActiveMilestoneItems(results);
    } catch (e) {
      console.error('fetchMilestoneItems error:', e);
    } finally {
      if (!silent) setLoadingMilestoneItems(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchProjects();
    setRefreshing(false);
  };

  // Calculate stats
  const stats = {
    total: projects.length,
    pending: projects.filter(p => p.project_post_status === 'under_review').length,
    approved: projects.filter(p => p.project_post_status === 'approved' && p.project_status === 'open').length,
    inProgress: projects.filter(p => p.project_status === 'bidding_closed' || p.project_status === 'in_progress').length,
    completed: projects.filter(p => p.project_status === 'completed').length,
  };

  // Filter projects based on active filter
  const filteredProjects = projects.filter(p => {
    if (activeFilter === 'all') return true;
    if (activeFilter === 'pending') return p.project_post_status === 'under_review';
    if (activeFilter === 'active') return p.project_post_status === 'approved' && p.project_status === 'open';
    if (activeFilter === 'in_progress') return p.project_status === 'bidding_closed' || p.project_status === 'in_progress';
    return true;
  });

  const formatBudget = (min: number, max: number) => {
    const formatNum = (n: number) => {
      if (n >= 1000000) return `₱${(n / 1000000).toFixed(1)}M`;
      if (n >= 1000) return `₱${(n / 1000).toFixed(0)}K`;
      return `₱${n}`;
    };
    return `${formatNum(min)} - ${formatNum(max)}`;
  };

  const getStatusConfig = (status: string, postStatus: string) => {
    if (postStatus === 'under_review') return { color: COLORS.warning, bg: COLORS.warningLight, label: 'Under Review', icon: 'clock' };
    if (status === 'open') return { color: COLORS.success, bg: COLORS.successLight, label: 'Open for Bidding', icon: 'check-circle' };
    if (status === 'bidding_closed') return { color: COLORS.info, bg: COLORS.infoLight, label: 'Bidding Closed', icon: 'lock' };
    if (status === 'in_progress') return { color: COLORS.info, bg: COLORS.infoLight, label: 'In Progress', icon: 'trending-up' };
    if (status === 'completed') return { color: COLORS.success, bg: COLORS.successLight, label: 'Completed', icon: 'check' };
    return { color: COLORS.textMuted, bg: COLORS.borderLight, label: status, icon: 'circle' };
  };

  const getDaysRemaining = (deadline: string) => {
    const now = new Date();
    const deadlineDate = new Date(deadline);
    const diff = Math.ceil((deadlineDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    return diff;
  };

  const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good Morning';
    if (hour < 17) return 'Good Afternoon';
    return 'Good Evening';
  };

  // Header animation
  const headerOpacity = scrollY.interpolate({
    inputRange: [0, 100],
    outputRange: [1, 0.9],
    extrapolate: 'clamp',
  });

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
        {/* Project badge */}
        <Text style={styles.msProjectBadge} numberOfLines={1}>{item.project_title}</Text>

        {/* Row 1: label + title + status badge */}
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

        {/* Row 2: Financial grid */}
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

        {/* Progress bar */}
        <View style={styles.msProgressBg}>
          <View style={[styles.msProgressFill, { width: `${progressPct}%` as any }]} />
        </View>

        {/* Footer: payment status + due date */}
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

        {/* View details link */}
        <TouchableOpacity
          style={styles.msDetailsBtn}
          onPress={() => {
            setProjectInitialSection('milestones');
            setProjectInitialItemId(item.item_id);
            setProjectInitialItemTab(null);
            setSelectedProject(item.project_obj);
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

  const renderFilterChip = (key: string, label: string, count: number) => {
    const isActive = activeFilter === key;
    return (
      <TouchableOpacity
        key={key}
        style={[styles.filterChip, isActive && styles.filterChipActive]}
        onPress={() => setActiveFilter(key)}
        activeOpacity={0.7}
      >
        <Text style={[styles.filterChipText, isActive && styles.filterChipTextActive]}>
          {label}
        </Text>
        {count > 0 && (
          <View style={[styles.filterChipBadge, isActive && styles.filterChipBadgeActive]}>
            <Text style={[styles.filterChipBadgeText, isActive && styles.filterChipBadgeTextActive]}>
              {count}
            </Text>
          </View>
        )}
      </TouchableOpacity>
    );
  };

  const renderProjectCard = (project: Project, index: number) => {
    const statusConfig = getStatusConfig(project.project_status, project.project_post_status);
    const daysRemaining = project.bidding_deadline ? getDaysRemaining(project.bidding_deadline) : null;

    return (
      <TouchableOpacity
        key={project.project_id}
        style={styles.projectCard}
        activeOpacity={0.7}
        onPress={() => setSelectedProject(project)}
      >
        {/* Card Header */}
        <View style={styles.projectCardHeader}>
          <View style={styles.projectTypeTag}>
            <Feather name="briefcase" size={12} color={COLORS.primary} />
            <Text style={styles.projectTypeText}>{project.type_name}</Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: statusConfig.bg }]}>
            <Feather name={statusConfig.icon as any} size={12} color={statusConfig.color} />
            <Text style={[styles.statusText, { color: statusConfig.color }]}>
              {statusConfig.label}
            </Text>
          </View>
        </View>

        {/* Card Content */}
        <Text style={styles.projectTitle}>{project.project_title}</Text>
        <Text style={styles.projectDescription} numberOfLines={2}>
          {project.project_description}
        </Text>

        {/* Project Meta */}
        <View style={styles.projectMeta}>
          <View style={styles.metaItem}>
            <Feather name="map-pin" size={14} color={COLORS.textMuted} />
            <Text style={styles.metaText} numberOfLines={1}>{project.project_location}</Text>
          </View>
          <View style={styles.metaItem}>
            <Feather name="credit-card" size={14} color={COLORS.textMuted} />
            <Text style={styles.metaText}>{formatBudget(project.budget_range_min, project.budget_range_max)}</Text>
          </View>
          {daysRemaining !== null && daysRemaining > 0 && (
            <View style={styles.metaItem}>
              <Feather name="clock" size={14} color={daysRemaining <= 3 ? COLORS.error : COLORS.textMuted} />
              <Text style={[styles.metaText, daysRemaining <= 3 && styles.deadlineTextUrgent]}>
                Bidding ends in {daysRemaining}d
              </Text>
            </View>
          )}
        </View>
      </TouchableOpacity>
    );
  };

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

  // Handle project creation
  const handleProjectSubmit = async (projectData: any) => {
    try {
      console.log('Submitting project data:', projectData);
      const response = await projects_service.create_project(projectData, userData?.user_id);
      console.log('Project creation response:', response);

      if (response.success) {
        Alert.alert('Success', 'Project created successfully!');
        setShowCreateProject(false);
        fetchProjects(); // Refresh the project list
      } else {
        // Show detailed validation errors if available
        let errorMessage = response.message || 'Failed to create project. Please try again.';

        if (response.data?.errors || response.data?.validation_errors) {
          const errors = response.data.errors || response.data.validation_errors;
          const errorList = Object.entries(errors)
            .map(([field, messages]: [string, any]) => {
              const msgs = Array.isArray(messages) ? messages : [messages];
              return `${field}: ${msgs.join(', ')}`;
            })
            .join('\n');
          errorMessage = `Validation failed:\n\n${errorList}`;
        }

        Alert.alert('Error', errorMessage);
      }
    } catch (error) {
      console.error('Error creating project:', error);
      Alert.alert('Error', 'An unexpected error occurred. Please try again.');
    }
  };

  // Show create project screen
  if (showCreateProject) {
    return (
      <CreateProject
        onBackPress={() => {
          setShowCreateProject(false);
          fetchProjects();
        }}
        onSubmit={handleProjectSubmit}
        contractorTypes={contractorTypes}
      />
    );
  }

  // Show search screen
  if (showSearchScreen) {
    return (
      <SearchScreen
        onClose={() => {
          setShowSearchScreen(false);
          fetchProjects();
        }}
        contractors={[]}
      />
    );
  }

  // Show project list if navigated
  if (showProjectList) {
    return (
      <ProjectList
        userData={userData}
        onClose={() => {
          setShowProjectList(false);
          setActiveFilter('all');
          fetchProjects();
        }}
        initialFilter={activeFilter}
      />
    );
  }

  // Project card tap or deep-link: open ProjectDetails (with optional initialSection)
  if (selectedProject) {
    return (
      <ProjectDetails
        project={selectedProject}
        userId={userData?.user_id}
        onClose={() => {
          setSelectedProject(null);
          setProjectInitialSection(null);
          setProjectInitialItemId(null);
          setProjectInitialItemTab(null);
          fetchProjects();
        }}
        initialSection={projectInitialSection}
        initialItemId={projectInitialItemId}
        initialItemTab={projectInitialItemTab}
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
              <Text style={styles.headerName}>{userData?.username || 'Property Owner'}</Text>
              <View style={styles.headerRoleBadge}>
                <Feather name="home" size={10} color={COLORS.primary} />
                <Text style={styles.headerRoleText}>Property Owner</Text>
              </View>
            </View>
          </View>
        </View>


        {/* Stats Row — milestoneDetail summaryMetricsRow style */}
        <View style={styles.statGrid}>
          <View style={styles.statMetricsRow}>
            <View style={styles.statMetric}>
              <Text style={styles.statMetricLabel}>In Progress</Text>
              <Text style={styles.statMetricValue}>{stats.inProgress}</Text>
            </View>
            <View style={styles.statMetricDivider} />
            <View style={styles.statMetric}>
              <Text style={styles.statMetricLabel}>Pending</Text>
              <Text style={styles.statMetricValue}>{stats.pending}</Text>
            </View>
            <View style={styles.statMetricDivider} />
            <View style={styles.statMetric}>
              <Text style={styles.statMetricLabel}>Milestone Items</Text>
              <Text style={styles.statMetricValue}>{activeMilestoneItems.length}</Text>
            </View>
            <View style={styles.statMetricDivider} />
            <View style={styles.statMetric}>
              <Text style={styles.statMetricLabel}>Completed</Text>
              <Text style={styles.statMetricValue}>{stats.completed}</Text>
            </View>
          </View>
        </View>

        {/* Pinned Project feature removed */}

        {/* Projects Navigation Section */}
        <View style={styles.projectsNavSection}>
          <Text style={styles.sectionTitle}>My Projects</Text>

          <View style={styles.navButtonsContainer}>
            <TouchableOpacity
              style={styles.navButton}
              activeOpacity={0.7}
              onPress={() => setShowProjectList(true)}
            >
              <View style={[styles.navButtonIcon, { backgroundColor: COLORS.successLight }]}>
                <Ionicons name="briefcase" size={18} color={COLORS.success} />
              </View>
              <View style={styles.navButtonContent}>
                <Text style={styles.navButtonTitle}>All Projects</Text>
                <Text style={styles.navButtonSubtitle}>{stats.total} projects total</Text>
              </View>
              <Feather name="chevron-right" size={20} color={COLORS.textMuted} />
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.navButton}
              activeOpacity={0.7}
              onPress={() => {
                setActiveFilter('completed');
                setShowProjectList(true);
              }}
            >
              <View style={[styles.navButtonIcon, { backgroundColor: '#EEF4FF', borderWidth: 1, borderColor: '#D6E2F3' }]}>
                <Feather name="check-circle" size={18} color={COLORS.primary} />
              </View>
              <View style={styles.navButtonContent}>
                <Text style={styles.navButtonTitle}>Finished Projects</Text>
                <Text style={styles.navButtonSubtitle}>{stats.completed} completed</Text>
              </View>
              <Feather name="chevron-right" size={20} color={COLORS.textMuted} />
            </TouchableOpacity>
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

      {/* Pinned project modals removed */}
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

  // Quick Actions Section
  quickActionsSection: {
    paddingHorizontal: 20,
    paddingTop: 20,
  },
  sectionTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 14,
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

  // Projects Section
  projectsSection: {
    paddingTop: 24,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    marginBottom: 2,
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

  // Filter Chips
  filterScrollContent: {
    paddingHorizontal: 20,
    paddingVertical: 10,
    gap: 8,
  },
  filterChip: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
    gap: 6,
  },
  filterChipActive: {
    backgroundColor: COLORS.secondary,
    borderColor: COLORS.secondary,
  },
  filterChipText: {
    fontSize: 13,
    color: COLORS.textSecondary,
    fontWeight: '600',
  },
  filterChipTextActive: {
    color: '#FFFFFF',
  },
  filterChipBadge: {
    backgroundColor: COLORS.borderLight,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 10,
  },
  filterChipBadgeActive: {
    backgroundColor: 'rgba(255,255,255,0.2)',
  },
  filterChipBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    color: COLORS.textSecondary,
  },
  filterChipBadgeTextActive: {
    color: '#FFFFFF',
  },

  // Projects List
  projectsList: {
    paddingHorizontal: 20,
    gap: 12,
  },
  projectCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  projectCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  projectTypeTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 8,
    paddingVertical: 5,
    borderRadius: 6,
  },
  projectTypeText: {
    fontSize: 11,
    color: COLORS.primary,
    fontWeight: '600',
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 5,
    borderRadius: 6,
    gap: 4,
  },
  statusText: {
    fontSize: 10,
    fontWeight: '600',
  },
  projectTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 6,
    lineHeight: 21,
  },
  projectDescription: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 19,
    marginBottom: 12,
  },
  projectMeta: {
    gap: 8,
    marginBottom: 2,
  },
  metaItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  metaText: {
    fontSize: 12,
    color: COLORS.textSecondary,
    flex: 1,
  },
  deadlineTextUrgent: {
    color: COLORS.error,
  },

  // Error State
  errorContainer: {
    alignItems: 'center',
    paddingVertical: 48,
    paddingHorizontal: 20,
    marginHorizontal: 20,
    backgroundColor: COLORS.surface,
    borderRadius: 20,
  },
  errorIconContainer: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#FEE2E2',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  errorTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  errorText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    marginBottom: 24,
  },
  retryButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primary,
    paddingHorizontal: 24,
    paddingVertical: 14,
    borderRadius: 12,
    gap: 8,
  },
  retryButtonText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '600',
  },

  // Empty State
  emptyContainer: {
    alignItems: 'center',
    paddingVertical: 48,
    paddingHorizontal: 20,
    marginHorizontal: 20,
    backgroundColor: COLORS.surface,
    borderRadius: 20,
  },
  emptyIllustration: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: COLORS.borderLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 24,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  emptyText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 21,
    marginBottom: 24,
  },
  createButton: {
    borderRadius: 14,
    overflow: 'hidden',
  },
  createButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 24,
    paddingVertical: 14,
    gap: 8,
  },
  createButtonText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '600',
  },

  // Pinned Section
  pinnedSection: {
    paddingTop: 24,
    paddingHorizontal: 20,
  },
  sectionTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
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

  // Pinned Project Card Styles
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

  // Pin Modal Styles
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

  // Pin Options Modal Styles
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
