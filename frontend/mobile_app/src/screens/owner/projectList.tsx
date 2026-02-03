// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  RefreshControl,
  StatusBar,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { projects_service } from '../../services/projects_service';
import ProjectDetails from './projectDetails';
import ProjectView from '../both/projectView';

// Color palette
const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
  success: '#10B981',
  successLight: '#D1FAE5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
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

interface Milestone {
  milestone_id: number;
  milestone_name: string;
  milestone_description: string;
  milestone_status: string;
  setup_status: string;
  start_date: string;
  end_date: string;
  created_at: string;
  updated_at: string;
}

interface ContractorInfo {
  company_name: string;
  company_phone: string;
  company_email: string;
  company_website?: string;
  years_of_experience: number;
  completed_projects: number;
  picab_category: string;
  username: string;
  profile_pic?: string;
}

interface AcceptedBid {
  bid_id: number;
  proposed_cost: number;
  estimated_timeline: string;
  contractor_notes: string;
  submitted_at: string;
}

interface Project {
  project_id: number;
  project_title: string;
  project_description: string;
  project_location: string;
  budget_range_min: number;
  budget_range_max: number;
  lot_size: number;
  floor_area: number;
  property_type: string;
  type_name: string;
  project_status: string;
  project_post_status: string;
  selected_contractor_id?: number;
  bidding_due?: string;
  created_at: string;
  bids_count?: number;
  display_status?: string;
  milestones?: Milestone[];
  milestones_count?: number;
  accepted_bid?: AcceptedBid;
  contractor_info?: ContractorInfo;
}

interface ProjectListProps {
  userData?: {
    user_id?: number;
    username?: string;
  };
  onClose: () => void;
}

export default function ProjectList({ userData, onClose }: ProjectListProps) {
  const insets = useSafeAreaInsets();
  const [projects, setProjects] = useState<Project[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [activeFilter, setActiveFilter] = useState('all');
  const [selectedProject, setSelectedProject] = useState<Project | null>(null);

  useEffect(() => {
    fetchProjects();
  }, [userData?.user_id]);

  const fetchProjects = async () => {
    if (!userData?.user_id) {
      setIsLoading(false);
      setError('User not logged in');
      return;
    }

    try {
      setIsLoading(true);
      setError(null);
      const response = await projects_service.get_owner_projects(userData.user_id);

      if (response.success) {
        const backendResponse = response.data;
        const projectsData = backendResponse?.data || backendResponse || [];
        const projectsArray = Array.isArray(projectsData) ? projectsData : [];

        console.log('=== PROJECTS FETCHED ===');
        console.log('Number of projects:', projectsArray.length);
        projectsArray.forEach((p, i) => {
          console.log(`Project ${i + 1}:`, p.project_title);
          console.log('  - selected_contractor_id:', p.selected_contractor_id);
          console.log('  - display_status:', p.display_status);
          console.log('  - milestones_count:', p.milestones_count);
        });

        setProjects(projectsArray);
      } else {
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

  // Calculate stats - separate bidding projects from active projects
  const stats = {
    total: projects.length,
    bidding: projects.filter(p => !p.selected_contractor_id && p.project_post_status === 'approved' && p.project_status === 'open').length,
    pending_review: projects.filter(p => p.project_post_status === 'under_review').length,
    active: projects.filter(p => p.selected_contractor_id && p.display_status === 'in_progress').length,
    completed: projects.filter(p => p.display_status === 'completed' || p.project_status === 'completed').length,
  };

  // Filter projects
  const filteredProjects = projects.filter(p => {
    if (activeFilter === 'all') return true;
    if (activeFilter === 'bidding') return !p.selected_contractor_id && p.project_post_status === 'approved' && p.project_status === 'open';
    if (activeFilter === 'pending_review') return p.project_post_status === 'under_review';
    if (activeFilter === 'active') return p.selected_contractor_id && p.display_status === 'in_progress';
    if (activeFilter === 'completed') return p.display_status === 'completed' || p.project_status === 'completed';
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

  const getStatusConfig = (project: Project) => {
    const { project_status, project_post_status, display_status, selected_contractor_id } = project;

    // Check if project is completed (priority check)
    if (project_status === 'completed' || display_status === 'completed') {
      return { color: COLORS.success, bg: COLORS.successLight, label: 'Completed', icon: 'check-circle' };
    }

    // If under review, show that status
    if (project_post_status === 'under_review') {
      return { color: COLORS.warning, bg: COLORS.warningLight, label: 'Pending Review', icon: 'clock' };
    }

    // If contractor is selected, use display_status
    if (selected_contractor_id && display_status) {
      if (display_status === 'waiting_milestone_setup') {
        return { color: COLORS.warning, bg: COLORS.warningLight, label: 'In Progress', icon: 'activity' };
      }
      if (display_status === 'in_progress') {
        return { color: COLORS.primary, bg: COLORS.primaryLight, label: 'In Progress', icon: 'activity' };
      }
    }

    // For bidding projects (no contractor selected)
    if (project_status === 'open') {
      return { color: COLORS.success, bg: COLORS.successLight, label: 'Bidding Open', icon: 'users' };
    }

    return { color: COLORS.textMuted, bg: COLORS.borderLight, label: project_status, icon: 'circle' };
  };

  const getDaysRemaining = (deadline: string) => {
    const now = new Date();
    const deadlineDate = new Date(deadline);
    const diff = Math.ceil((deadlineDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    return diff;
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

  const renderProjectCard = (project: Project) => {
    const statusConfig = getStatusConfig(project);
    const daysRemaining = project.bidding_due && !project.selected_contractor_id ? getDaysRemaining(project.bidding_due) : null;
    const isCompleted = project.project_status === 'completed' || project.display_status === 'completed';

    return (
      <TouchableOpacity
        key={project.project_id}
        style={[
          styles.projectCard,
          isCompleted && styles.projectCardCompleted
        ]}
        activeOpacity={0.7}
        onPress={() => setSelectedProject(project)}
      >
        {isCompleted && (
          <View style={styles.completedBanner}>
            <Feather name="check-circle" size={16} color={COLORS.success} />
            <Text style={styles.completedBannerText}>Project Completed</Text>
          </View>
        )}
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

        <Text style={styles.projectTitle}>{project.project_title}</Text>
        <Text style={styles.projectDescription} numberOfLines={2}>
          {project.project_description}
        </Text>

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
          <View style={styles.viewDetailsBtn}>
            <Text style={styles.viewDetailsText}>View</Text>
            <Feather name="arrow-right" size={16} color={COLORS.primary} />
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  // Show appropriate screen based on project type
  if (selectedProject) {
    console.log('=== PROJECT CLICKED ===');
    console.log('Project:', selectedProject.project_title);
    console.log('selected_contractor_id:', selectedProject.selected_contractor_id);
    console.log('Type of selected_contractor_id:', typeof selectedProject.selected_contractor_id);
    console.log('Has contractor?', !!selectedProject.selected_contractor_id);

    // If contractor is selected, show ProjectView (milestone-focused)
    if (selectedProject.selected_contractor_id) {
      console.log('>>> ROUTING TO ProjectView (milestone-focused)');
      return (
        <ProjectView
          project={selectedProject}
          userId={userData?.user_id}
          userRole="owner"
          onClose={() => setSelectedProject(null)}
        />
      );
    }

    // Otherwise show ProjectDetails (bidding screen)
    console.log('>>> ROUTING TO ProjectDetails (bidding screen)');
    return (
      <ProjectDetails
        project={selectedProject}
        userId={userData?.user_id}
        onClose={() => setSelectedProject(null)}
      />
    );
  }

  return (
    <View style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.surface} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="arrow-left" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>My Projects</Text>
        <View style={styles.headerSpacer} />
      </View>

      {/* Filter Chips */}
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.filterScrollContent}
        style={styles.filterScroll}
      >
        {renderFilterChip('all', 'All', stats.total)}
        {renderFilterChip('bidding', 'Bidding', stats.bidding)}
        {renderFilterChip('pending_review', 'Pending Review', stats.pending_review)}
        {renderFilterChip('active', 'Active', stats.active)}
        {renderFilterChip('completed', 'Completed', stats.completed)}
      </ScrollView>

      {/* Content */}
      {isLoading && !refreshing ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Loading projects...</Text>
        </View>
      ) : error ? (
        <View style={styles.errorContainer}>
          <Feather name="alert-circle" size={48} color={COLORS.error} />
          <Text style={styles.errorTitle}>Something went wrong</Text>
          <Text style={styles.errorText}>{error}</Text>
          <TouchableOpacity style={styles.retryButton} onPress={fetchProjects}>
            <Text style={styles.retryButtonText}>Try Again</Text>
          </TouchableOpacity>
        </View>
      ) : filteredProjects.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Feather name="folder" size={64} color={COLORS.border} />
          <Text style={styles.emptyTitle}>
            {activeFilter === 'all' ? 'No Projects Yet' : 'No Projects Found'}
          </Text>
          <Text style={styles.emptyText}>
            {activeFilter === 'all'
              ? 'Create your first project to start finding contractors'
              : 'Try selecting a different filter'}
          </Text>
        </View>
      ) : (
        <ScrollView
          style={styles.scrollView}
          contentContainerStyle={styles.scrollContent}
          showsVerticalScrollIndicator={false}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={onRefresh}
              colors={[COLORS.primary]}
              tintColor={COLORS.primary}
            />
          }
        >
          {filteredProjects.map((project) => renderProjectCard(project))}
          <View style={{ height: 20 }} />
        </ScrollView>
      )}
    </View>
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
    borderBottomColor: COLORS.border,
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
  },
  headerSpacer: {
    width: 40,
  },
  filterScroll: {
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
    flexGrow: 0,
    maxHeight: 60,
  },
  filterScrollContent: {
    paddingHorizontal: 16,
    paddingVertical: 10,
    flexDirection: 'row',
    alignItems: 'center',
  },
  filterChip: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 20,
    backgroundColor: COLORS.surface,
    borderWidth: 1.5,
    borderColor: COLORS.border,
    marginRight: 10,
  },
  filterChipActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  filterChipText: {
    fontSize: 13,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },
  filterChipTextActive: {
    color: '#FFFFFF',
  },
  filterChipBadge: {
    marginLeft: 8,
    backgroundColor: COLORS.borderLight,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 12,
    minWidth: 22,
    alignItems: 'center',
  },
  filterChipBadgeActive: {
    backgroundColor: 'rgba(255,255,255,0.25)',
  },
  filterChipBadgeText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  filterChipBadgeTextActive: {
    color: '#FFFFFF',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
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
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  errorTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
    marginTop: 16,
  },
  errorText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginTop: 8,
    textAlign: 'center',
  },
  retryButton: {
    marginTop: 20,
    paddingHorizontal: 24,
    paddingVertical: 12,
    backgroundColor: COLORS.primary,
    borderRadius: 8,
  },
  retryButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
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
    marginTop: 8,
    textAlign: 'center',
  },
  projectCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  projectCardCompleted: {
    backgroundColor: COLORS.successLight,
    borderWidth: 2,
    borderColor: COLORS.success,
    shadowColor: COLORS.success,
    shadowOpacity: 0.15,
  },
  completedBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.success,
    marginHorizontal: -16,
    marginTop: -16,
    marginBottom: 12,
    paddingVertical: 8,
    borderTopLeftRadius: 12,
    borderTopRightRadius: 12,
    gap: 6,
  },
  completedBannerText: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.surface,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
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
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 8,
  },
  projectTypeText: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.primary,
    marginLeft: 5,
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 8,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
    marginLeft: 5,
  },
  projectTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 6,
  },
  projectDescription: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 18,
    marginBottom: 10,
  },
  projectMeta: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 14,
  },
  metaItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 16,
    marginBottom: 4,
  },
  metaText: {
    fontSize: 13,
    color: COLORS.textMuted,
    marginLeft: 5,
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
  },
  bidsInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 14,
  },
  bidsText: {
    fontSize: 13,
    color: COLORS.info,
    fontWeight: '600',
    marginLeft: 5,
  },
  deadlineInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  deadlineUrgent: {},
  deadlineText: {
    fontSize: 13,
    color: COLORS.textMuted,
    fontWeight: '500',
    marginLeft: 5,
  },
  deadlineTextUrgent: {
    color: COLORS.error,
  },
  viewDetailsBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
  },
  viewDetailsText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.primary,
    marginRight: 4,
  },
});
