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
} from 'react-native';
import { View as SafeAreaView, StatusBar, Platform } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons, Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { projects_service } from '../../services/projects_service';
import ProjectDetails from './projectDetails';
import ProjectList from './projectList';
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
  const [pinnedProject, setPinnedProject] = useState<Project | null>(null);
  const [showPinModal, setShowPinModal] = useState(false);
  const [showPinOptions, setShowPinOptions] = useState(false);
  const [avatarError, setAvatarError] = useState(false);
  const [contractorTypes, setContractorTypes] = useState<any[]>([]);
  const scrollY = useRef(new Animated.Value(0)).current;

  // Get status bar height (top inset)
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  useEffect(() => {
    console.log('Dashboard - userData:', userData);
    console.log('Dashboard - user_id:', userData?.user_id);
    console.log('Dashboard - profile_pic URL:', userData?.profile_pic);
    setAvatarError(false); // Reset avatar error when userData changes
    fetchProjects();
    fetchContractorTypes();
  }, [userData?.user_id]);

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

  const fetchProjects = async () => {
    console.log('fetchProjects called, user_id:', userData?.user_id);

    if (!userData?.user_id) {
      console.log('No user_id, setting error');
      setIsLoading(false);
      setError('User not logged in');
      return;
    }

    try {
      setIsLoading(true);
      setError(null);

      console.log('Calling API with user_id:', userData.user_id);
      const response = await projects_service.get_owner_projects(userData.user_id);
      console.log('API Response:', JSON.stringify(response, null, 2));

      if (response.success) {
        // The api_request wraps the response, so response.data contains the backend response
        // Backend returns: { success: true, message: '...', data: [...projects] }
        const backendResponse = response.data;
        const projectsData = backendResponse?.data || backendResponse || [];
        const projectsArray = Array.isArray(projectsData) ? projectsData : [];
        console.log('Projects loaded:', projectsArray.length, projectsArray);
        setProjects(projectsArray);
      } else {
        console.log('API error:', response.message);
        setError(response.message || 'Failed to load projects');
      }
    } catch (err) {
      console.error('Error fetching projects:', err);
      setError('Failed to load projects');
    } finally {
      setIsLoading(false);
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
            <Feather name="dollar-sign" size={14} color={COLORS.textMuted} />
            <Text style={styles.metaText}>{formatBudget(project.budget_range_min, project.budget_range_max)}</Text>
          </View>
        </View>

        {/* Card Footer */}
        <View style={styles.projectCardFooter}>
          <View style={styles.footerLeft}>
            {project.bids_count !== undefined && project.bids_count > 0 && (
              <View style={styles.bidsInfo}>
                <Feather name="users" size={14} color={COLORS.info} />
                <Text style={styles.bidsText}>{project.bids_count} bids</Text>
              </View>
            )}
            {daysRemaining !== null && daysRemaining > 0 && (
              <View style={[styles.deadlineInfo, daysRemaining <= 3 && styles.deadlineUrgent]}>
                <Feather name="clock" size={14} color={daysRemaining <= 3 ? COLORS.error : COLORS.textMuted} />
                <Text style={[styles.deadlineText, daysRemaining <= 3 && styles.deadlineTextUrgent]}>
                  {daysRemaining}d left
                </Text>
              </View>
            )}
          </View>
          <TouchableOpacity style={styles.viewDetailsBtn}>
            <Text style={styles.viewDetailsText}>View Details</Text>
            <Feather name="arrow-right" size={16} color={COLORS.primary} />
          </TouchableOpacity>
        </View>
      </TouchableOpacity>
    );
  };

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
        onBackPress={() => setShowCreateProject(false)}
        onSubmit={handleProjectSubmit}
        contractorTypes={contractorTypes}
      />
    );
  }

  // Show search screen
  if (showSearchScreen) {
    return (
      <SearchScreen
        onClose={() => setShowSearchScreen(false)}
        contractors={[]}
      />
    );
  }

  // Show project list if navigated
  if (showProjectList) {
    return (
      <ProjectList
        userData={userData}
        onClose={() => setShowProjectList(false)}
      />
    );
  }

  // Show project details if a project is selected
  if (selectedProject) {
    return (
      <ProjectDetails
        project={selectedProject}
        userId={userData?.user_id}
        onClose={() => setSelectedProject(null)}
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
                        onError={(e) => {
                          console.log('Avatar load error:', e.nativeEvent.error, 'URL:', userData.profile_pic);
                          setAvatarError(true);
                        }}
                        onLoad={() => console.log('Avatar loaded successfully:', userData.profile_pic)}
                      />
                    ) : (
                      <View style={styles.avatarPlaceholder}>
                        <Text style={styles.avatarText}>
                          {userData?.username?.charAt(0).toUpperCase() || 'U'}
                        </Text>
                      </View>
                    )}
                    <View style={styles.onlineIndicator} />
                  </View>
                  <View style={styles.greetingContainer}>
                    <Text style={styles.greeting}>{getGreeting()}</Text>
                    <Text style={styles.userName}>{userData?.username || 'Property Owner'}</Text>
                  </View>
                </View>
              </View>

              {/* Quick Summary */}
              <View style={styles.quickSummary}>
                <View style={styles.summaryItem}>
                  <Text style={styles.summaryValue}>{stats.total}</Text>
                  <Text style={styles.summaryLabel}>Total</Text>
                </View>
                <View style={styles.summaryDivider} />
                <View style={styles.summaryItem}>
                  <Text style={styles.summaryValue}>{stats.pending}</Text>
                  <Text style={styles.summaryLabel}>Pending</Text>
                </View>
                <View style={styles.summaryDivider} />
                <View style={styles.summaryItem}>
                  <Text style={styles.summaryValue}>{stats.approved}</Text>
                  <Text style={styles.summaryLabel}>Active</Text>
                </View>
                <View style={styles.summaryDivider} />
                <View style={styles.summaryItem}>
                  <Text style={styles.summaryValue}>{stats.inProgress}</Text>
                  <Text style={styles.summaryLabel}>In Progress</Text>
                </View>
              </View>
            </View>
          </LinearGradient>
        </Animated.View>

        {/* Pinned Project Section */}
        <View style={styles.pinnedSection}>
          <View style={styles.sectionHeader}>
            <View style={styles.sectionTitleRow}>
              <Feather name="bookmark" size={18} color={COLORS.primary} />
              <Text style={styles.sectionTitle}>Pinned Project</Text>
            </View>
          </View>

          {/* Show pinned project or empty state */}
          {pinnedProject ? (
            <TouchableOpacity
              style={styles.pinnedCard}
              activeOpacity={0.7}
              onPress={() => setSelectedProject(pinnedProject)}
            >
              <View style={styles.pinnedProjectContent}>
                <View style={styles.pinnedProjectInfo}>
                  <Text style={styles.pinnedProjectTitle} numberOfLines={1}>
                    {pinnedProject.project_title}
                  </Text>
                  <Text style={styles.pinnedProjectLocation} numberOfLines={1}>
                    <Feather name="map-pin" size={12} color={COLORS.textMuted} /> {pinnedProject.project_location}
                  </Text>
                  <View style={styles.pinnedProjectMeta}>
                    <View style={[styles.pinnedStatusBadge, { backgroundColor: getStatusConfig(pinnedProject.project_status, pinnedProject.project_post_status).bg }]}>
                      <Text style={[styles.pinnedStatusText, { color: getStatusConfig(pinnedProject.project_status, pinnedProject.project_post_status).color }]}>
                        {getStatusConfig(pinnedProject.project_status, pinnedProject.project_post_status).label}
                      </Text>
                    </View>
                    <Text style={styles.pinnedBudget}>
                      {formatBudget(pinnedProject.budget_range_min, pinnedProject.budget_range_max)}
                    </Text>
                  </View>
                </View>
                <TouchableOpacity
                  style={styles.pinnedOptionsButton}
                  onPress={() => setShowPinOptions(true)}
                  hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
                >
                  <Feather name="more-vertical" size={20} color={COLORS.textSecondary} />
                </TouchableOpacity>
              </View>
            </TouchableOpacity>
          ) : (
            <TouchableOpacity
              style={styles.pinnedCard}
              activeOpacity={0.7}
              onPress={() => setShowPinModal(true)}
            >
              <View style={styles.pinnedEmpty}>
                <Feather name="bookmark" size={32} color={COLORS.border} />
                <Text style={styles.pinnedEmptyText}>No pinned project</Text>
                <Text style={styles.pinnedEmptySubtext}>Tap to pin a project for quick access</Text>
              </View>
            </TouchableOpacity>
          )}
        </View>

        {/* Projects Navigation Section */}
        <View style={styles.projectsNavSection}>
          <Text style={styles.sectionTitle}>My Projects</Text>

          <View style={styles.navButtonsContainer}>
            <TouchableOpacity
              style={styles.navButton}
              activeOpacity={0.7}
              onPress={() => setShowProjectList(true)}
            >
              <View style={styles.navButtonIcon}>
                <Feather name="folder" size={24} color={COLORS.primary} />
              </View>
              <View style={styles.navButtonContent}>
                <Text style={styles.navButtonTitle}>All Projects</Text>
                <Text style={styles.navButtonSubtitle}>{stats.total} projects total</Text>
              </View>
              <Feather name="chevron-right" size={22} color={COLORS.textMuted} />
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.navButton}
              activeOpacity={0.7}
              onPress={() => {
                setActiveFilter('completed');
                setShowProjectList(true);
              }}
            >
              <View style={[styles.navButtonIcon, { backgroundColor: COLORS.successLight }]}>
                <Feather name="check-circle" size={24} color={COLORS.success} />
              </View>
              <View style={styles.navButtonContent}>
                <Text style={styles.navButtonTitle}>Finished Projects</Text>
                <Text style={styles.navButtonSubtitle}>{stats.completed} completed</Text>
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
              onPress={() => setShowCreateProject(true)}
            >
              <LinearGradient
                colors={[COLORS.primary, COLORS.primaryDark]}
                style={styles.quickActionGradient}
              >
                <Feather name="plus" size={22} color="#FFFFFF" />
              </LinearGradient>
              <Text style={styles.quickActionLabel}>New{"\n"}Project</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.quickActionCard}
              activeOpacity={0.7}
              onPress={() => setShowSearchScreen(true)}
            >
              <View style={[styles.quickActionIcon, { backgroundColor: COLORS.infoLight }]}>
                <Feather name="search" size={22} color={COLORS.info} />
              </View>
              <Text style={styles.quickActionLabel}>Find{"\n"}Contractors</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.quickActionCard}
              activeOpacity={0.7}
              onPress={onNavigateToMessages}
            >
              <View style={[styles.quickActionIcon, { backgroundColor: COLORS.warningLight }]}>
                <Feather name="message-circle" size={22} color={COLORS.warning} />
              </View>
              <Text style={styles.quickActionLabel}>Messages</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Animated.ScrollView>

      {/* Pin Project Modal */}
      <Modal
        visible={showPinModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowPinModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.pinModalContainer}>
            <View style={styles.pinModalHeader}>
              <Text style={styles.pinModalTitle}>Select Project to Pin</Text>
              <TouchableOpacity
                onPress={() => setShowPinModal(false)}
                hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
              >
                <Feather name="x" size={24} color={COLORS.textSecondary} />
              </TouchableOpacity>
            </View>

            {projects.length === 0 ? (
              <View style={styles.pinModalEmpty}>
                <Feather name="folder" size={48} color={COLORS.border} />
                <Text style={styles.pinModalEmptyText}>No projects available</Text>
                <Text style={styles.pinModalEmptySubtext}>Create a project first to pin it</Text>
              </View>
            ) : (
              <FlatList
                data={projects}
                keyExtractor={(item) => item.project_id.toString()}
                showsVerticalScrollIndicator={false}
                contentContainerStyle={styles.pinModalList}
                renderItem={({ item }) => (
                  <TouchableOpacity
                    style={[
                      styles.pinModalItem,
                      pinnedProject?.project_id === item.project_id && styles.pinModalItemSelected
                    ]}
                    activeOpacity={0.7}
                    onPress={() => {
                      setPinnedProject(item);
                      setShowPinModal(false);
                    }}
                  >
                    <View style={styles.pinModalItemContent}>
                      <Text style={styles.pinModalItemTitle} numberOfLines={1}>
                        {item.project_title}
                      </Text>
                      <Text style={styles.pinModalItemLocation} numberOfLines={1}>
                        <Feather name="map-pin" size={11} color={COLORS.textMuted} /> {item.project_location}
                      </Text>
                      <View style={[styles.pinModalItemStatus, { backgroundColor: getStatusConfig(item.project_status, item.project_post_status).bg }]}>
                        <Text style={[styles.pinModalItemStatusText, { color: getStatusConfig(item.project_status, item.project_post_status).color }]}>
                          {getStatusConfig(item.project_status, item.project_post_status).label}
                        </Text>
                      </View>
                    </View>
                    <View style={styles.pinModalItemAction}>
                      <Feather
                        name={pinnedProject?.project_id === item.project_id ? "check-circle" : "circle"}
                        size={22}
                        color={pinnedProject?.project_id === item.project_id ? COLORS.primary : COLORS.border}
                      />
                    </View>
                  </TouchableOpacity>
                )}
              />
            )}
          </View>
        </View>
      </Modal>

      {/* Pin Options Modal (Unpin / Change) */}
      <Modal
        visible={showPinOptions}
        animationType="fade"
        transparent={true}
        onRequestClose={() => setShowPinOptions(false)}
      >
        <TouchableOpacity
          style={styles.modalOverlay}
          activeOpacity={1}
          onPress={() => setShowPinOptions(false)}
        >
          <View style={styles.pinOptionsContainer}>
            <TouchableOpacity
              style={styles.pinOptionItem}
              activeOpacity={0.7}
              onPress={() => {
                setShowPinOptions(false);
                setShowPinModal(true);
              }}
            >
              <Feather name="repeat" size={20} color={COLORS.text} />
              <Text style={styles.pinOptionText}>Change Pinned Project</Text>
            </TouchableOpacity>
            <View style={styles.pinOptionDivider} />
            <TouchableOpacity
              style={styles.pinOptionItem}
              activeOpacity={0.7}
              onPress={() => {
                setPinnedProject(null);
                setShowPinOptions(false);
              }}
            >
              <Feather name="bookmark" size={20} color={COLORS.error} />
              <Text style={[styles.pinOptionText, { color: COLORS.error }]}>Unpin Project</Text>
            </TouchableOpacity>
          </View>
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
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 12,
    elevation: 2,
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
  projectCardFooter: {
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
  bidsInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
  },
  bidsText: {
    fontSize: 12,
    color: COLORS.info,
    fontWeight: '600',
  },
  deadlineInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
  },
  deadlineUrgent: {},
  deadlineText: {
    fontSize: 12,
    color: COLORS.textMuted,
    fontWeight: '500',
  },
  deadlineTextUrgent: {
    color: COLORS.error,
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
  navButtonsContainer: {
    marginTop: 14,
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
