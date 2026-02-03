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
  Alert,
} from 'react-native';
import { View as SafeAreaView, StatusBar, Platform } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons, Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { projects_service } from '../../services/projects_service';
import MyProjects from './myProjects';
import MyBids from './myBids';
import Members from './members';
import MilestoneSetup from './milestoneSetup';

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
  const [selectedProject, setSelectedProject] = useState<Project | null>(null);
  const [showMilestoneSetup, setShowMilestoneSetup] = useState(false);
  const [isLoadingProject, setIsLoadingProject] = useState(false);
  // pin modals removed
  const [avatarError, setAvatarError] = useState(false);
  const [showMyProjects, setShowMyProjects] = useState(false);
  const [showMyBids, setShowMyBids] = useState(false);
  const [showMembers, setShowMembers] = useState(false);
  const scrollY = useRef(new Animated.Value(0)).current;

  // Get status bar height (top inset)
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  // Notify parent when entering/exiting full-screen mode
  useEffect(() => {
    const isFullScreen = showMyProjects || showMyBids || showMilestoneSetup;
    onFullScreenChange?.(isFullScreen);
  }, [showMyProjects, showMyBids, showMilestoneSetup, onFullScreenChange]);

  useEffect(() => {
    setAvatarError(false);
    fetchData();
  }, [userData?.user_id]);

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
      } else {
        setMyProjects([]);
      }

      // Fetch contractor bids
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

      setIsLoading(false);
    } catch (err) {
      console.error('Error fetching dashboard data:', err);
      setError('Failed to load data');
      setMyProjects([]);
      setMyBids([]);
      setIsLoading(false);
    }
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

  const formatBudget = (min: number, max: number) => {
    const formatNum = (n: number) => {
      if (n >= 1000000) return `₱${(n / 1000000).toFixed(1)}M`;
      if (n >= 1000) return `₱${(n / 1000).toFixed(0)}K`;
      return `₱${n}`;
    };
    return `${formatNum(min)} - ${formatNum(max)}`;
  };

  const handleAcceptedBidClick = async (bid: Bid) => {
    try {
      setIsLoadingProject(true);
      // Fetch contractor projects to get the full project data
      const response = await projects_service.get_contractor_projects(userData?.user_id || 0);
      
      if (response.success) {
        const projectsData = response.data?.data || response.data || [];
        const project = Array.isArray(projectsData) 
          ? projectsData.find((p: Project) => p.project_id === bid.project_id)
          : null;
        
        if (project) {
          setSelectedProject(project);
          setShowMilestoneSetup(true);
        } else {
          Alert.alert('Error', 'Project not found. Please try again.');
        }
      } else {
        Alert.alert('Error', 'Failed to load project data. Please try again.');
      }
    } catch (error) {
      console.error('Error loading project:', error);
      Alert.alert('Error', 'Failed to load project data. Please try again.');
    } finally {
      setIsLoadingProject(false);
    }
  };

  const formatCost = (cost: number) => {
    if (cost >= 1000000) return `₱${(cost / 1000000).toFixed(2)}M`;
    if (cost >= 1000) return `₱${(cost / 1000).toFixed(0)}K`;
    return `₱${cost.toLocaleString()}`;
  };

  const getBidStatusConfig = (status: string) => {
    switch (status) {
      case 'pending':
      case 'submitted':
        return { color: COLORS.warning, bg: COLORS.warningLight, label: 'Pending', icon: 'clock' };
      case 'accepted':
        return { color: COLORS.success, bg: COLORS.successLight, label: 'Accepted', icon: 'check-circle' };
      case 'rejected':
        return { color: COLORS.error, bg: '#FEE2E2', label: 'Rejected', icon: 'x-circle' };
      case 'withdrawn':
      case 'cancelled':
        return { color: COLORS.textMuted, bg: COLORS.borderLight, label: 'Cancelled', icon: 'minus-circle' };
      default:
        return { color: COLORS.textMuted, bg: COLORS.borderLight, label: status, icon: 'circle' };
    }
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

  if (isLoading && !refreshing) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar hidden={true} />
        <View style={styles.loadingContainer}>
          <View style={styles.loadingSpinner}>
            <ActivityIndicator size="large" color={COLORS.primary} />
          </View>
          <Text style={styles.loadingText}>Loading your dashboard...</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (isLoadingProject) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar hidden={true} />
        <View style={styles.loadingContainer}>
          <View style={styles.loadingSpinner}>
            <ActivityIndicator size="large" color={COLORS.primary} />
          </View>
          <Text style={styles.loadingText}>Loading project...</Text>
        </View>
      </SafeAreaView>
    );
  }

  // Show Milestone Setup screen if selected
  if (showMilestoneSetup && selectedProject) {
    return (
      <MilestoneSetup
        project={selectedProject}
        userId={userData?.user_id}
        onClose={() => {
          setShowMilestoneSetup(false);
          setSelectedProject(null);
        }}
        onSave={() => {
          setShowMilestoneSetup(false);
          setSelectedProject(null);
          fetchData(); // Refresh dashboard after saving milestones
        }}
      />
    );
  }

  // Show My Projects screen if selected
  if (showMyProjects) {
    return (
      <MyProjects
        userData={userData}
        onClose={() => setShowMyProjects(false)}
      />
    );
  }

  // Show My Bids screen if selected
  if (showMyBids) {
    return (
      <MyBids
        userData={userData}
        onClose={() => setShowMyBids(false)}
      />
    );
  }

  // Show Members screen if selected
  if (showMembers) {
    return (
      <Members
        userData={userData}
        onClose={() => setShowMembers(false)}
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
        {/* Hero Header */}
        <Animated.View style={[styles.heroHeader, { opacity: headerOpacity }]}>
          <LinearGradient
            colors={[COLORS.primary, COLORS.primaryDeep]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.heroGradient}
          >
            <View style={styles.heroContent}>
              <View style={styles.heroTop}>
                <View style={styles.userInfo}>
                  <View style={styles.avatarContainer}>
                    {userData?.profile_pic && !avatarError ? (
                      <Image
                        source={{ uri: userData.profile_pic }}
                        style={styles.avatar}
                        onError={() => setAvatarError(true)}
                      />
                    ) : (
                      <View style={styles.avatarPlaceholder}>
                        <Text style={styles.avatarText}>
                          {userData?.company_name?.charAt(0).toUpperCase() ||
                            userData?.username?.charAt(0).toUpperCase() || 'C'}
                        </Text>
                      </View>
                    )}
                    <View style={styles.onlineIndicator} />
                  </View>
                  <View style={styles.greetingContainer}>
                    <Text style={styles.greeting}>{getGreeting()}</Text>
                    <Text style={styles.userName}>{userData?.company_name || userData?.username || 'Contractor'}</Text>
                    {userData?.contractor_type && (
                      <Text style={styles.userType}>{userData.contractor_type}</Text>
                    )}
                  </View>
                </View>
              </View>

              {/* Quick Summary */}
              <View style={styles.quickSummary}>
                <View style={styles.summaryItem}>
                  <Text style={styles.summaryValue}>{stats.totalBids}</Text>
                  <Text style={styles.summaryLabel}>Total Bids</Text>
                </View>
                <View style={styles.summaryDivider} />
                <View style={styles.summaryItem}>
                  <Text style={styles.summaryValue}>{stats.pendingBids}</Text>
                  <Text style={styles.summaryLabel}>Pending</Text>
                </View>
                <View style={styles.summaryDivider} />
                <View style={styles.summaryItem}>
                  <Text style={styles.summaryValue}>{stats.acceptedBids}</Text>
                  <Text style={styles.summaryLabel}>Won</Text>
                </View>
                <View style={styles.summaryDivider} />
                <View style={styles.summaryItem}>
                  <Text style={styles.summaryValue}>{stats.activeProjects}</Text>
                  <Text style={styles.summaryLabel}>Active</Text>
                </View>
              </View>
            </View>
          </LinearGradient>
        </Animated.View>

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
                <Feather name="briefcase" size={24} color={COLORS.success} />
              </View>
              <View style={styles.navButtonContent}>
                <Text style={styles.navButtonTitle}>My Projects</Text>
                <Text style={styles.navButtonSubtitle}>{stats.activeProjects} active projects</Text>
              </View>
              <Feather name="chevron-right" size={22} color={COLORS.textMuted} />
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.navButton}
              activeOpacity={0.7}
              onPress={() => setShowMyBids(true)}
            >
              <View style={[styles.navButtonIcon, { backgroundColor: COLORS.primaryLight }]}>
                <Feather name="file-text" size={24} color={COLORS.primary} />
              </View>
              <View style={styles.navButtonContent}>
                <Text style={styles.navButtonTitle}>My Bids</Text>
                <Text style={styles.navButtonSubtitle}>{stats.totalBids} bids submitted</Text>
              </View>
              <Feather name="chevron-right" size={22} color={COLORS.textMuted} />
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.navButton}
              activeOpacity={0.7}
              onPress={() => setShowMembers(true)}
            >
              <View style={[styles.navButtonIcon, { backgroundColor: '#E8F6FF' }]}>
                <Feather name="users" size={24} color={COLORS.info} />
              </View>
              <View style={styles.navButtonContent}>
                <Text style={styles.navButtonTitle}>Members</Text>
                <Text style={styles.navButtonSubtitle}>Manage your team</Text>
              </View>
              <Feather name="chevron-right" size={22} color={COLORS.textMuted} />
            </TouchableOpacity>
          </View>
        </View>

        {/* Quick Actions */}
        <View style={styles.quickActionsSection}>
          <Text style={styles.sectionTitle}>Quick Actions</Text>
          <View style={styles.quickActionsGrid}>
            <TouchableOpacity
              style={styles.quickActionCard}
              activeOpacity={0.7}
              onPress={onBrowseProjects}
            >
              <LinearGradient
                colors={[COLORS.primary, COLORS.primaryDark]}
                style={styles.quickActionGradient}
              >
                <Feather name="search" size={22} color="#FFFFFF" />
              </LinearGradient>
              <Text style={styles.quickActionLabel}>Browse{"\n"}Projects</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.quickActionCard}
              activeOpacity={0.7}
              onPress={() => {
                // Navigate to pending bids
              }}
            >
              <View style={[styles.quickActionIcon, { backgroundColor: COLORS.warningLight }]}>
                <Feather name="clock" size={22} color={COLORS.warning} />
              </View>
              <Text style={styles.quickActionLabel}>Pending{"\n"}Bids</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.quickActionCard}
              activeOpacity={0.7}
              onPress={onNavigateToMessages}
            >
              <View style={[styles.quickActionIcon, { backgroundColor: COLORS.infoLight }]}>
                <Feather name="message-circle" size={22} color={COLORS.info} />
              </View>
              <Text style={styles.quickActionLabel}>Messages</Text>
            </TouchableOpacity>
          </View>
        </View>

        {/* Recent Bids Section */}
        <View style={styles.recentSection}>
          <View style={styles.sectionHeader}>
            <Text style={styles.sectionTitle}>Recent Bids</Text>
            <TouchableOpacity style={styles.seeAllBtn}>
              <Text style={styles.seeAllText}>See All</Text>
              <Feather name="chevron-right" size={16} color={COLORS.primary} />
            </TouchableOpacity>
          </View>

          {myBids.length === 0 ? (
            <View style={styles.emptyContainer}>
              <View style={styles.emptyIllustration}>
                <Feather name="file-text" size={48} color={COLORS.border} />
              </View>
              <Text style={styles.emptyTitle}>No Bids Yet</Text>
              <Text style={styles.emptyText}>
                Browse available projects and submit your first bid to get started.
              </Text>
              <TouchableOpacity style={styles.createButton} onPress={onBrowseProjects}>
                <LinearGradient
                  colors={[COLORS.primary, COLORS.primaryDark]}
                  style={styles.createButtonGradient}
                >
                  <Feather name="search" size={18} color="#FFFFFF" />
                  <Text style={styles.createButtonText}>Browse Projects</Text>
                </LinearGradient>
              </TouchableOpacity>
            </View>
          ) : (
            <View style={styles.bidsList}>
              {myBids.slice(0, 3).map((bid) => {
                const statusConfig = getBidStatusConfig(bid.bid_status);
                return (
                  <TouchableOpacity
                    key={bid.bid_id}
                    style={styles.bidCard}
                    activeOpacity={0.7}
                    onPress={async () => {
                      // If bid is accepted, redirect to milestone setup
                      if (bid.bid_status === 'accepted') {
                        await handleAcceptedBidClick(bid);
                      } else {
                        // For other statuses, navigate to MyBids screen
                        onBrowseProjects?.();
                      }
                    }}
                  >
                    <View style={styles.bidCardHeader}>
                      <View style={styles.bidTypeTag}>
                        <Feather name="file-text" size={12} color={COLORS.primary} />
                        <Text style={styles.bidTypeText}>Bid #{bid.bid_id}</Text>
                      </View>
                      <View style={[styles.statusBadge, { backgroundColor: statusConfig.bg }]}>
                        <Feather name={statusConfig.icon as any} size={12} color={statusConfig.color} />
                        <Text style={[styles.statusText, { color: statusConfig.color }]}>
                          {statusConfig.label}
                        </Text>
                      </View>
                    </View>

                    <Text style={styles.bidTitle}>{bid.project_title}</Text>

                    <View style={styles.bidMeta}>
                      {bid.project_location && (
                        <View style={styles.metaItem}>
                          <Feather name="map-pin" size={14} color={COLORS.textMuted} />
                          <Text style={styles.metaText} numberOfLines={1}>{bid.project_location}</Text>
                        </View>
                      )}
                      <View style={styles.metaItem}>
                        <Feather name="dollar-sign" size={14} color={COLORS.textMuted} />
                        <Text style={styles.metaText}>{formatCost(bid.proposed_cost)}</Text>
                      </View>
                    </View>

                    <View style={styles.bidCardFooter}>
                      <View style={styles.footerLeft}>
                        <View style={styles.timelineInfo}>
                          <Feather name="calendar" size={14} color={COLORS.textMuted} />
                          <Text style={styles.timelineText}>
                            {bid.estimated_timeline} {bid.estimated_timeline === 1 ? 'month' : 'months'}
                          </Text>
                        </View>
                      </View>
                      <TouchableOpacity style={styles.viewDetailsBtn}>
                        <Text style={styles.viewDetailsText}>View Details</Text>
                        <Feather name="arrow-right" size={16} color={COLORS.primary} />
                      </TouchableOpacity>
                    </View>
                  </TouchableOpacity>
                );
              })}
            </View>
          )}
        </View>
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

  // Projects Navigation Section
  projectsNavSection: {
    paddingTop: 24,
    paddingHorizontal: 20,
  },
  navButtonsContainer: {
    marginTop: 0,
  },
  navButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: 14,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 10,
    elevation: 3,
  },
  navButtonIcon: {
    width: 50,
    height: 50,
    borderRadius: 14,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
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
    width: 52,
    height: 52,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  quickActionIcon: {
    width: 52,
    height: 52,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  quickActionLabel: {
    fontSize: 11,
    color: COLORS.textSecondary,
    textAlign: 'center',
    fontWeight: '500',
    lineHeight: 14,
  },

  // Recent Bids Section
  recentSection: {
    paddingTop: 24,
    paddingHorizontal: 20,
  },
  bidsList: {
    gap: 12,
  },
  bidCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 12,
    elevation: 2,
  },
  bidCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  bidTypeTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 8,
    paddingVertical: 5,
    borderRadius: 6,
  },
  bidTypeText: {
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
  bidTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 6,
    lineHeight: 21,
  },
  bidMeta: {
    gap: 8,
    marginBottom: 12,
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
  bidCardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },
  footerLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  timelineInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
  },
  timelineText: {
    fontSize: 12,
    color: COLORS.textMuted,
    fontWeight: '500',
  },
  viewDetailsBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  viewDetailsText: {
    fontSize: 13,
    color: COLORS.primary,
    fontWeight: '600',
  },

  // Empty State
  emptyContainer: {
    alignItems: 'center',
    paddingVertical: 48,
    paddingHorizontal: 20,
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

