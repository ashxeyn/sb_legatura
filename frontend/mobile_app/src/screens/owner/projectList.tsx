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
  TextInput,
  Modal,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { projects_service } from '../../services/projects_service';
import ProjectDetails from './projectDetails';

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
  initialFilter?: string;
}

export default function ProjectList({ userData, onClose, initialFilter = 'all' }: ProjectListProps) {
  const insets = useSafeAreaInsets();
  const [projects, setProjects] = useState<Project[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [activeFilters, setActiveFilters] = useState<string[]>([initialFilter]);
  const [selectedProject, setSelectedProject] = useState<Project | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [sortBy, setSortBy] = useState<'latest' | 'oldest' | 'a_z' | 'z_a' | 'budget_high' | 'budget_low'>('latest');
  const [showSortModal, setShowSortModal] = useState(false);

  const toggleFilter = (key: string) => {
    if (key === 'all') {
      setActiveFilters(['all']);
      return;
    }
    setActiveFilters(prev => {
      const withoutAll = prev.filter(f => f !== 'all');
      if (withoutAll.includes(key)) {
        const updated = withoutAll.filter(f => f !== key);
        return updated.length === 0 ? ['all'] : updated;
      } else {
        return [...withoutAll, key];
      }
    });
  };

  const isFiltered = !(activeFilters.length === 1 && activeFilters[0] === 'all') || searchQuery.trim() !== '' || sortBy !== 'latest';

  const clearAllFilters = () => {
    setActiveFilters(['all']);
    setSearchQuery('');
    setSortBy('latest');
  };

  const sortOptions = [
    { key: 'latest', label: 'Latest First', icon: 'arrow-down' },
    { key: 'oldest', label: 'Oldest First', icon: 'arrow-up' },
    { key: 'a_z', label: 'Title A-Z', icon: 'type' },
    { key: 'z_a', label: 'Title Z-A', icon: 'type' },
    { key: 'budget_high', label: 'Budget: High to Low', icon: 'trending-up' },
    { key: 'budget_low', label: 'Budget: Low to High', icon: 'trending-down' },
  ] as const;

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
    needs_setup: projects.filter(p => p.selected_contractor_id && p.display_status === 'waiting_milestone_setup').length,
    has_bids: projects.filter(p => (p.bids_count || 0) > 0).length,
    expiring_soon: projects.filter(p => {
      if (!p.bidding_due || p.selected_contractor_id) return false;
      const daysLeft = Math.ceil((new Date(p.bidding_due).getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24));
      return daysLeft > 0 && daysLeft <= 7;
    }).length,
  };

  // Filter projects by phase (multi-select: project matches if ANY selected filter applies)
  const phaseFiltered = projects.filter(p => {
    if (activeFilters.includes('all')) return true;
    return activeFilters.some(filter => {
      if (filter === 'bidding') return !p.selected_contractor_id && p.project_post_status === 'approved' && p.project_status === 'open';
      if (filter === 'pending_review') return p.project_post_status === 'under_review';
      if (filter === 'active') return p.selected_contractor_id && p.display_status === 'in_progress';
      if (filter === 'needs_setup') return p.selected_contractor_id && p.display_status === 'waiting_milestone_setup';
      if (filter === 'completed') return p.display_status === 'completed' || p.project_status === 'completed';
      if (filter === 'has_bids') return (p.bids_count || 0) > 0;
      if (filter === 'expiring_soon') {
        if (!p.bidding_due || p.selected_contractor_id) return false;
        const daysLeft = Math.ceil((new Date(p.bidding_due).getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24));
        return daysLeft > 0 && daysLeft <= 7;
      }
      return false;
    });
  });

  // Apply search filter
  const searchFiltered = phaseFiltered.filter(p => {
    if (!searchQuery.trim()) return true;
    const q = searchQuery.toLowerCase();
    return (
      (p.project_title || '').toLowerCase().includes(q) ||
      (p.project_description || '').toLowerCase().includes(q) ||
      (p.project_location || '').toLowerCase().includes(q) ||
      (p.type_name || '').toLowerCase().includes(q) ||
      (p.property_type || '').toLowerCase().includes(q)
    );
  });

  // Apply sort
  const filteredProjects = [...searchFiltered].sort((a, b) => {
    switch (sortBy) {
      case 'latest':
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
      case 'oldest':
        return new Date(a.created_at).getTime() - new Date(b.created_at).getTime();
      case 'a_z':
        return (a.project_title || '').localeCompare(b.project_title || '');
      case 'z_a':
        return (b.project_title || '').localeCompare(a.project_title || '');
      case 'budget_high':
        return (b.budget_range_max || 0) - (a.budget_range_max || 0);
      case 'budget_low':
        return (a.budget_range_min || 0) - (b.budget_range_min || 0);
      default:
        return 0;
    }
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

    // Check if project is halted (highest priority)
    if (project_status === 'halt' || project_status === 'on_hold' || project_status === 'halted') {
      return { color: COLORS.error, bg: '#FEE2E2', label: 'Project Halted', icon: 'alert-octagon' };
    }

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
    const isActive = activeFilters.includes(key);
    return (
      <TouchableOpacity
        key={key}
        style={[styles.filterChip, isActive && styles.filterChipActive]}
        onPress={() => toggleFilter(key)}
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
    const isHalted = project.project_status === 'halt' || project.project_status === 'on_hold' || project.project_status === 'halted';

    return (
      <TouchableOpacity
        key={project.project_id}
        style={[
          styles.projectCard,
          isCompleted && styles.projectCardCompleted,
          isHalted && styles.projectCardHalted,
        ]}
        activeOpacity={0.7}
        onPress={() => setSelectedProject(project)}
      >
        {isHalted && (
          <View style={styles.haltedBanner}>
            <Feather name="alert-octagon" size={16} color="#FFFFFF" />
            <Text style={styles.haltedBannerText}>Project Halted</Text>
          </View>
        )}
        {isCompleted && !isHalted && (
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

  // Always show ProjectDetails — milestone navigation is handled from within it
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

      {/* Search Bar */}
      <View style={styles.searchRow}>
        <View style={styles.searchInputContainer}>
          <Feather name="search" size={18} color={COLORS.textMuted} />
          <TextInput
            style={styles.searchInput}
            placeholder="Search projects..."
            placeholderTextColor={COLORS.textMuted}
            value={searchQuery}
            onChangeText={setSearchQuery}
            returnKeyType="search"
          />
          {searchQuery.length > 0 && (
            <TouchableOpacity onPress={() => setSearchQuery('')} hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}>
              <Feather name="x-circle" size={18} color={COLORS.textMuted} />
            </TouchableOpacity>
          )}
        </View>
        <TouchableOpacity
          style={[styles.sortButton, isFiltered && styles.sortButtonActive]}
          onPress={() => setShowSortModal(true)}
          activeOpacity={0.7}
        >
          <Feather name="sliders" size={18} color={isFiltered ? '#FFFFFF' : COLORS.primary} />
          {isFiltered && (
            <View style={styles.filterBadge}>
              <Text style={styles.filterBadgeText}>
                {(activeFilters.includes('all') ? 0 : activeFilters.length) + (sortBy !== 'latest' ? 1 : 0)}
              </Text>
            </View>
          )}
        </TouchableOpacity>
      </View>

      {/* Sort & Filter Modal */}
      <Modal visible={showSortModal} transparent animationType="slide" onRequestClose={() => setShowSortModal(false)}>
        <TouchableOpacity style={styles.sortModalOverlay} activeOpacity={1} onPress={() => setShowSortModal(false)}>
          <View style={styles.sortModalContent} onStartShouldSetResponder={() => true}>
            {/* Modal Header */}
            <View style={styles.modalHeader}>
              <View style={styles.modalDragHandle} />
              <View style={styles.modalTitleRow}>
                <Text style={styles.sortModalTitle}>Sort & Filter</Text>
                {isFiltered && (
                  <TouchableOpacity style={styles.resetButton} onPress={clearAllFilters} activeOpacity={0.7}>
                    <Feather name="rotate-ccw" size={14} color={COLORS.error} />
                    <Text style={styles.resetButtonText}>Reset All</Text>
                  </TouchableOpacity>
                )}
              </View>
            </View>

            <ScrollView showsVerticalScrollIndicator={false} bounces={false}>
              {/* Sort By Section */}
              <Text style={styles.sectionLabel}>Sort By</Text>
              {sortOptions.map(option => (
                <TouchableOpacity
                  key={option.key}
                  style={[styles.sortOption, sortBy === option.key && styles.sortOptionActive]}
                  onPress={() => setSortBy(option.key as any)}
                >
                  <Feather name={option.icon as any} size={18} color={sortBy === option.key ? COLORS.primary : COLORS.textSecondary} />
                  <Text style={[styles.sortOptionText, sortBy === option.key && styles.sortOptionTextActive]}>
                    {option.label}
                  </Text>
                  {sortBy === option.key && <Feather name="check" size={18} color={COLORS.primary} />}
                </TouchableOpacity>
              ))}

              {/* By Phase Section */}
              <View style={styles.sectionDivider} />
              <Text style={styles.sectionLabel}>By Phase</Text>
              <View style={styles.phaseChipsContainer}>
                {renderFilterChip('all', 'All', stats.total)}
                {renderFilterChip('pending_review', 'Pending Review', stats.pending_review)}
                {renderFilterChip('bidding', 'Bidding', stats.bidding)}
                {renderFilterChip('needs_setup', 'Needs Setup', stats.needs_setup)}
                {renderFilterChip('active', 'In Progress', stats.active)}
                {renderFilterChip('completed', 'Completed', stats.completed)}
                {renderFilterChip('has_bids', 'Has Bids', stats.has_bids)}
                {renderFilterChip('expiring_soon', 'Expiring Soon', stats.expiring_soon)}
              </View>
            </ScrollView>

            {/* Apply Button - pinned at bottom */}
            <TouchableOpacity style={styles.applyButton} onPress={() => setShowSortModal(false)} activeOpacity={0.7}>
              <Text style={styles.applyButtonText}>Apply</Text>
            </TouchableOpacity>
          </View>
        </TouchableOpacity>
      </Modal>

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
          <Feather
            name={activeFilters.includes('all') ? 'folder' : 'search'}
            size={64}
            color={COLORS.border}
          />
          <Text style={styles.emptyTitle}>
            {activeFilters.includes('all') && !searchQuery.trim()
              ? 'No Projects Yet'
              : 'No Matching Projects'}
          </Text>
          <Text style={styles.emptyText}>
            {activeFilters.includes('all') && !searchQuery.trim()
              ? 'Create your first project to start finding contractors'
              : 'Try adjusting your filters or search query'}
          </Text>
          {isFiltered && (
            <TouchableOpacity style={styles.retryButton} onPress={clearAllFilters}>
              <Text style={styles.retryButtonText}>Clear Filters</Text>
            </TouchableOpacity>
          )}
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
  searchRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 10,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
    gap: 10,
  },
  searchInputContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    borderRadius: 10,
    paddingHorizontal: 12,
    height: 42,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  searchInput: {
    flex: 1,
    fontSize: 14,
    color: COLORS.text,
    marginLeft: 8,
    paddingVertical: 0,
  },
  sortButton: {
    width: 42,
    height: 42,
    borderRadius: 10,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.primary + '30',
  },
  sortModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.4)',
    justifyContent: 'flex-end',
  },
  sortModalContent: {
    backgroundColor: COLORS.surface,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    paddingHorizontal: 20,
    paddingTop: 12,
    paddingBottom: 36,
    maxHeight: '80%',
  },
  modalHeader: {
    alignItems: 'center',
    marginBottom: 8,
  },
  modalTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    width: '100%',
  },
  modalDragHandle: {
    width: 40,
    height: 4,
    borderRadius: 2,
    backgroundColor: COLORS.border,
    marginBottom: 16,
  },
  sortModalTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  sectionLabel: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
    marginBottom: 10,
    marginTop: 8,
  },
  sectionDivider: {
    height: 1,
    backgroundColor: COLORS.borderLight,
    marginVertical: 16,
  },
  resetButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingVertical: 4,
    paddingHorizontal: 8,
  },
  resetButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.error,
  },
  phaseChipsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: 8,
  },
  applyButton: {
    backgroundColor: COLORS.primary,
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 16,
  },
  applyButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
  sortButtonActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  filterBadge: {
    position: 'absolute',
    top: -4,
    right: -4,
    backgroundColor: COLORS.error,
    borderRadius: 10,
    minWidth: 18,
    height: 18,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 4,
  },
  filterBadgeText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: '700',
  },
  sortOption: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 8,
    borderRadius: 10,
    gap: 12,
  },
  sortOptionActive: {
    backgroundColor: COLORS.primaryLight,
  },
  sortOptionText: {
    flex: 1,
    fontSize: 15,
    color: COLORS.textSecondary,
  },
  sortOptionTextActive: {
    color: COLORS.primary,
    fontWeight: '600',
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
  projectCardHalted: {
    borderWidth: 2,
    borderColor: COLORS.error,
    backgroundColor: '#FFF5F5',
  },
  haltedBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.error,
    marginHorizontal: -16,
    marginTop: -16,
    marginBottom: 12,
    paddingVertical: 8,
    borderTopLeftRadius: 10,
    borderTopRightRadius: 10,
    gap: 6,
  },
  haltedBannerText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#FFFFFF',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
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
