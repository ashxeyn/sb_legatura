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
  Image,
  TextInput,
  Modal,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import ImageFallback from '../../components/ImageFallbackFixed';
import MilestoneSetup from './milestoneSetup';
import ProjectView from '../both/projectView';
import ContractorProjectDetails from './projectDetails';
import { projects_service } from '../../services/projects_service';

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

interface MilestoneItem {
  item_id: number;
  sequence_order: number;
  percentage_progress: number;
  milestone_item_title: string;
  milestone_item_description: string;
  milestone_item_cost: number;
  date_to_finish: string;
}

interface PaymentPlan {
  plan_id: number;
  payment_mode: string;
  total_project_cost: number;
  downpayment_amount: number;
  is_confirmed: number;
}

interface Milestone {
  milestone_id: number;
  plan_id: number;
  milestone_name: string;
  milestone_description: string;
  milestone_status: string;
  setup_status: string;
  start_date: string;
  end_date: string;
  created_at: string;
  updated_at: string;
  items?: MilestoneItem[];
  payment_plan?: PaymentPlan;
}

interface OwnerInfo {
  owner_id: number;
  first_name: string;
  last_name: string;
  username: string;
  profile_pic?: string;
}

interface AcceptedBid {
  bid_id: number;
  proposed_cost: number;
  estimated_timeline: string;
  contractor_notes: string;
  submitted_at?: string;
}

interface Project {
  project_id: number;
  project_title: string;
  project_description: string;
  project_location: string;
  budget_range_min: number;
  budget_range_max: number;
  lot_size?: number;
  floor_area?: number;
  property_type: string;
  type_id: number;
  type_name: string;
  project_status: string;
  project_post_status?: string;
  owner_name?: string;
  owner_profile_pic?: string;
  owner_user_id?: number;
  bid_id?: number;
  proposed_cost?: number;
  estimated_timeline?: number;
  contractor_notes?: string;
  bid_status?: string;
  milestones?: Milestone[];
  milestones_count?: number;
  display_status?: string;
  created_at: string;
  owner_info?: OwnerInfo;
  accepted_bid?: AcceptedBid;
}

interface MyProjectsProps {
  userData?: {
    user_id?: number;
    username?: string;
  };
  onClose: () => void;
}

export default function MyProjects({ userData, onClose }: MyProjectsProps) {
  const insets = useSafeAreaInsets();
  const [projects, setProjects] = useState<Project[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [selectedProject, setSelectedProject] = useState<Project | null>(null);
  const [showMilestoneSetup, setShowMilestoneSetup] = useState(false);
  const [showProjectDetails, setShowProjectDetails] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [activeFilters, setActiveFilters] = useState<string[]>(['all']);
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

      const response = await projects_service.get_contractor_projects(userData.user_id);

      if (response.success) {
        const backendResponse = response.data;
        const projectsData = backendResponse?.data || backendResponse || [];
        const projectsArray = Array.isArray(projectsData) ? projectsData : [];

        // Normalize display_status for each project based on true backend status.
        // project_status (completed / halt) always wins regardless of what the backend set.
        const normalizedProjects = projectsArray.map((project: Project) => {
          const status = (project.project_status || '').toLowerCase();
          const hasMilestones = typeof project.milestones_count === 'number' ? project.milestones_count > 0 : Array.isArray(project.milestones) && project.milestones.length > 0;

          // Hard overrides — these always take priority
          if (status === 'completed') {
            project.display_status = 'completed';
          } else if (status === 'halt' || status === 'halted' || status === 'on_hold') {
            project.display_status = 'on_hold';
          } else if (!project.display_status || project.display_status === '') {
            // Only fill in display_status when the backend didn't provide one
            if (!hasMilestones) {
              project.display_status = 'waiting_milestone_setup';
            } else if (status === 'in_progress') {
              project.display_status = 'in_progress';
            } else {
              project.display_status = status || 'pending';
            }
          }
          return project;
        });

        setProjects(normalizedProjects);
      } else {
        console.log('API failed:', response.message);
        setError(response.message || 'Failed to load projects');
        setProjects([]);
      }
    } catch (err) {
      console.error('Error fetching projects:', err);
      setError('Failed to load projects. Please try again.');
      setProjects([]);
    } finally {
      setIsLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchProjects();
    setRefreshing(false);
  };

  // Calculate stats (use true backend status for in-progress)
  const stats = {
    total: projects.length,
    notStarted: projects.filter(p => p.display_status === 'waiting_milestone_setup').length,
    inProgress: projects.filter(p => {
      const status = (p.project_status || '').toLowerCase();
      const hasMilestones = typeof p.milestones_count === 'number' ? p.milestones_count > 0 : Array.isArray(p.milestones) && p.milestones.length > 0;
      return status === 'in_progress' && hasMilestones;
    }).length,
    completed: projects.filter(p => (p.project_status || '').toLowerCase() === 'completed').length,
    onHold: projects.filter(p => (p.project_status || '').toLowerCase() === 'halt' || p.display_status === 'on_hold').length,
    dueSoon: projects.filter(p => {
      if (!p.expected_completion) return false;
      const status = (p.project_status || '').toLowerCase();
      if (status !== 'in_progress') return false;
      const daysLeft = Math.ceil((new Date(p.expected_completion).getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24));
      return daysLeft > 0 && daysLeft <= 14;
    }).length,
  };

  // Filter projects by phase (multi-select: project matches if ANY selected filter applies)
  const phaseFiltered = projects.filter(p => {
    const status = (p.project_status || '').toLowerCase();
    const hasMilestones = typeof p.milestones_count === 'number' ? p.milestones_count > 0 : Array.isArray(p.milestones) && p.milestones.length > 0;
    if (activeFilters.includes('all')) return true;
    return activeFilters.some(filter => {
      if (filter === 'not_started') return p.display_status === 'waiting_milestone_setup';
      if (filter === 'in_progress') return status === 'in_progress' && hasMilestones;
      if (filter === 'completed') return status === 'completed';
      if (filter === 'on_hold') return status === 'halt' || p.display_status === 'on_hold';
      if (filter === 'due_soon') {
        if (!p.expected_completion || status !== 'in_progress') return false;
        const daysLeft = Math.ceil((new Date(p.expected_completion).getTime() - new Date().getTime()) / (1000 * 60 * 60 * 24));
        return daysLeft > 0 && daysLeft <= 14;
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
      (p.property_type || '').toLowerCase().includes(q) ||
      (p.owner_name || '').toLowerCase().includes(q)
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
        return (b.budget_range_max || b.accepted_bid_amount || 0) - (a.budget_range_max || a.accepted_bid_amount || 0);
      case 'budget_low':
        return (a.budget_range_min || a.accepted_bid_amount || 0) - (b.budget_range_min || b.accepted_bid_amount || 0);
      default:
        return 0;
    }
  });

  const formatBudget = (amount: number) => {
    if (amount >= 1000000) return `₱${(amount / 1000000).toFixed(2)}M`;
    if (amount >= 1000) return `₱${(amount / 1000).toFixed(0)}K`;
    return `₱${amount.toLocaleString()}`;
  };

  const getStatusConfig = (status: string) => {
    const s = (status || '').toLowerCase();
    if (s === 'waiting_milestone_setup' || s === 'not_started' || s === 'pending_setup') {
      return { color: COLORS.warning, bg: COLORS.warningLight, label: 'Project Setup', icon: 'settings' };
    }
    if (s === 'waiting_for_approval') {
      return { color: COLORS.info, bg: COLORS.infoLight, label: 'Pending Approval', icon: 'clock' };
    }
    if (s === 'in_progress' || s === 'ongoing') {
      return { color: COLORS.info, bg: COLORS.infoLight, label: 'In Progress', icon: 'trending-up' };
    }
    if (s === 'completed' || s === 'complete' || s === 'finished' || s === 'done') {
      return { color: COLORS.success, bg: COLORS.successLight, label: 'Completed', icon: 'check-circle' };
    }
    if (s === 'on_hold' || s === 'halt' || s === 'halted' || s === 'paused') {
      return { color: COLORS.warning, bg: COLORS.warningLight, label: 'On Hold', icon: 'pause-circle' };
    }
    return { color: COLORS.textMuted, bg: COLORS.borderLight, label: status, icon: 'circle' };
  };

  const isCompleted = (status?: string) => {
    const s = (status || '').toLowerCase();
    return s === 'completed' || s === 'complete' || s === 'finished' || s === 'done';
  };

  const isInProgress = (status?: string) => {
    const s = (status || '').toLowerCase();
    return s === 'in_progress' || s === 'ongoing';
  };

  const getDaysRemaining = (endDate: string) => {
    const now = new Date();
    const end = new Date(endDate);
    const diff = Math.ceil((end.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
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
    const statusConfig = getStatusConfig(project.display_status || project.project_status);
    const daysRemaining = project.expected_completion ? getDaysRemaining(project.expected_completion) : null;
    const isNotStarted = project.display_status === 'waiting_milestone_setup';
    const isHalted = (project.project_status || '').toLowerCase() === 'halt' || (project.project_status || '').toLowerCase() === 'on_hold' || (project.project_status || '').toLowerCase() === 'halted';

    return (
      <TouchableOpacity
        key={project.project_id}
        style={[styles.projectCard, isNotStarted && styles.projectCardNotStarted, isHalted && styles.projectCardHalted]}
        activeOpacity={0.7}
        onPress={() => {
          setSelectedProject(project);
          setShowProjectDetails(true);
        }}
      >
        {/* Halted Banner */}
        {isHalted && (
          <View style={styles.haltedBanner}>
            <Feather name="alert-octagon" size={16} color="#FFFFFF" />
            <Text style={styles.haltedBannerText}>Project Halted</Text>
          </View>
        )}
        {/* Setup Required Banner for not_started projects */}
        {isNotStarted && !isHalted && (
          <View style={styles.setupBanner}>
            <Feather name="alert-triangle" size={14} color={COLORS.warning} />
            <Text style={styles.setupBannerText}>Project setup required</Text>
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

        {/* Owner Info */}
        {project.owner_name && (
          <View style={styles.ownerSection}>
            <View style={styles.ownerAvatar}>
                {project.owner_profile_pic ? (
                  <ImageFallback
                    uri={project.owner_profile_pic}
                    defaultImage={require('../../../assets/images/pictures/contractor_default.png')}
                    style={styles.ownerImage}
                    resizeMode="cover"
                  />
                ) : (
                  <View style={styles.ownerPlaceholder}>
                    <Text style={styles.ownerInitial}>
                      {project.owner_name.charAt(0).toUpperCase()}
                    </Text>
                  </View>
                )}
            </View>
            <View style={styles.ownerInfo}>
              <Text style={styles.ownerLabel}>Property Owner</Text>
              <Text style={styles.ownerName}>{project.owner_name}</Text>
            </View>
          </View>
        )}

        <View style={styles.projectMeta}>
          <View style={styles.metaItem}>
            <Feather name="map-pin" size={14} color={COLORS.textMuted} />
            <Text style={styles.metaText} numberOfLines={1}>{project.project_location}</Text>
          </View>
          <View style={styles.metaItem}>
            <Feather name="dollar-sign" size={14} color={COLORS.success} />
            <Text style={[styles.metaText, { color: COLORS.success, fontWeight: '600' }]}>
              {formatBudget(project.accepted_bid_amount || project.budget_range_min)}
            </Text>
          </View>
        </View>

        {/* Progress Bar (for in-progress projects) */}
        {isInProgress(project.project_status) && project.progress_percentage !== undefined && (
          <View style={styles.progressSection}>
            <View style={styles.progressHeader}>
              <Text style={styles.progressLabel}>Progress</Text>
              <Text style={styles.progressPercentage}>{project.progress_percentage}%</Text>
            </View>
            <View style={styles.progressBarBg}>
              <View style={[styles.progressBarFill, { width: `${project.progress_percentage}%` }]} />
            </View>
          </View>
        )}

        <View style={styles.projectCardFooter}>
          <View style={styles.footerLeft}>
            {isNotStarted && (
              <View style={styles.newProjectInfo}>
                <Feather name="clock" size={14} color={COLORS.warning} />
                <Text style={styles.newProjectText}>Awaiting Project Setup</Text>
              </View>
            )}
            {daysRemaining !== null && isInProgress(project.project_status) && (
              <View style={[styles.deadlineInfo, daysRemaining <= 7 && styles.deadlineUrgent]}>
                <Feather
                  name="calendar"
                  size={14}
                  color={daysRemaining <= 7 ? COLORS.warning : COLORS.textMuted}
                />
                <Text style={[styles.deadlineText, daysRemaining <= 7 && styles.deadlineTextUrgent]}>
                  {daysRemaining > 0 ? `${daysRemaining}d remaining` : 'Due today'}
                </Text>
              </View>
            )}
            {isCompleted(project.project_status) && (
              <View style={styles.completedInfo}>
                <Feather name="check" size={14} color={COLORS.success} />
                <Text style={styles.completedText}>Completed</Text>
              </View>
            )}
          </View>
          {isNotStarted ? (
            <View style={styles.setupBtn}>
              <Feather name="settings" size={14} color="#FFFFFF" />
              <Text style={styles.setupBtnText}>Project Setup</Text>
            </View>
          ) : (
            <View style={styles.viewDetailsBtn}>
              <Text style={styles.viewDetailsText}>View</Text>
              <Feather name="arrow-right" size={16} color={COLORS.primary} />
            </View>
          )}
        </View>
      </TouchableOpacity>
    );
  };

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
          fetchProjects(); // Refresh projects after saving milestones
        }}
      />
    );
  }

  if (showProjectDetails && selectedProject) {
    return (
      <ContractorProjectDetails
        project={selectedProject}
        userId={userData?.user_id}
        onClose={() => {
          setShowProjectDetails(false);
          setSelectedProject(null);
          fetchProjects(); // Refresh projects after viewing
        }}
        onProjectUpdated={(updated) => {
          setSelectedProject(updated);
        }}
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
                {renderFilterChip('not_started', 'Project Setup', stats.notStarted)}
                {renderFilterChip('in_progress', 'In Progress', stats.inProgress)}
                {renderFilterChip('completed', 'Completed', stats.completed)}
                {renderFilterChip('on_hold', 'On Hold', stats.onHold)}
                {renderFilterChip('due_soon', 'Due Soon', stats.dueSoon)}
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
          <View style={styles.emptyIllustration}>
            <Feather name={activeFilters.includes('all') && !searchQuery.trim() ? 'briefcase' : 'search'} size={64} color={COLORS.border} />
          </View>
          <Text style={styles.emptyTitle}>
            {activeFilters.includes('all') && !searchQuery.trim() ? 'No Projects Yet' : 'No Matching Projects'}
          </Text>
          <Text style={styles.emptyText}>
            {activeFilters.includes('all') && !searchQuery.trim()
              ? 'Once your bids are accepted, your projects will appear here'
              : 'Try adjusting your filters or search query'}
          </Text>
          {isFiltered && (
            <TouchableOpacity style={[styles.browseButton, { marginTop: 16 }]} onPress={clearAllFilters}>
              <View style={[styles.browseButtonGradient, { backgroundColor: COLORS.primary }]}>
                <Feather name="x" size={18} color="#FFFFFF" />
                <Text style={styles.browseButtonText}>Clear Filters</Text>
              </View>
            </TouchableOpacity>
          )}
          {activeFilters.includes('all') && !searchQuery.trim() && (
            <TouchableOpacity style={styles.browseButton} onPress={onClose}>
              <LinearGradient
                colors={[COLORS.primary, COLORS.primaryDark]}
                style={styles.browseButtonGradient}
              >
                <Feather name="search" size={18} color="#FFFFFF" />
                <Text style={styles.browseButtonText}>Browse Projects</Text>
              </LinearGradient>
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
  statsSummary: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 16,
    paddingHorizontal: 20,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  statItem: {
    alignItems: 'center',
    flex: 1,
  },
  statNumber: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.primary,
  },
  statLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginTop: 2,
  },
  statDivider: {
    width: 1,
    height: 36,
    backgroundColor: COLORS.border,
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
  emptyIllustration: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: COLORS.borderLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    marginTop: 8,
  },
  emptyText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginTop: 8,
    textAlign: 'center',
    lineHeight: 20,
    paddingHorizontal: 20,
  },
  browseButton: {
    marginTop: 24,
    borderRadius: 12,
    overflow: 'hidden',
    shadowColor: COLORS.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 4,
  },
  browseButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
    paddingVertical: 14,
  },
  browseButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
    marginLeft: 8,
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
  projectCardNotStarted: {
    borderWidth: 2,
    borderColor: COLORS.warning,
    borderStyle: 'dashed',
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
  setupBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.warningLight,
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 8,
    marginBottom: 12,
  },
  setupBannerText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.warning,
    marginLeft: 6,
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
    marginBottom: 12,
  },
  ownerSection: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    paddingHorizontal: 12,
    backgroundColor: COLORS.borderLight,
    borderRadius: 10,
    marginBottom: 12,
  },
  ownerAvatar: {
    width: 36,
    height: 36,
    borderRadius: 18,
    marginRight: 10,
    overflow: 'hidden',
  },
  ownerImage: {
    width: '100%',
    height: '100%',
  },
  ownerPlaceholder: {
    width: '100%',
    height: '100%',
    backgroundColor: COLORS.primary,
    justifyContent: 'center',
    alignItems: 'center',
  },
  ownerInitial: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
  ownerInfo: {
    flex: 1,
  },
  ownerLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  ownerName: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginTop: 1,
  },
  projectMeta: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 12,
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
  progressSection: {
    marginBottom: 12,
  },
  progressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  progressLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    fontWeight: '500',
  },
  progressPercentage: {
    fontSize: 13,
    color: COLORS.primary,
    fontWeight: '700',
  },
  progressBarBg: {
    height: 8,
    backgroundColor: COLORS.borderLight,
    borderRadius: 4,
    overflow: 'hidden',
  },
  progressBarFill: {
    height: '100%',
    backgroundColor: COLORS.primary,
    borderRadius: 4,
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
    color: COLORS.warning,
  },
  completedInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  completedText: {
    fontSize: 13,
    color: COLORS.success,
    fontWeight: '500',
    marginLeft: 5,
  },
  newProjectInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  newProjectText: {
    fontSize: 13,
    color: COLORS.warning,
    fontWeight: '500',
    marginLeft: 5,
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
  setupBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.warning,
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 8,
  },
  setupBtnText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#FFFFFF',
    marginLeft: 6,
  },
  detailsCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    marginHorizontal: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
  },
  ownerRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  ownerAvatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    marginRight: 12,
  },
  ownerAvatarPlaceholder: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  ownerAvatarText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.primary,
  },
  ownerName: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 2,
  },
  ownerInfo: {
    flex: 1,
  },
  ownerLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  headerGradient: {
    marginHorizontal: 16,
    marginTop: 16,
    marginBottom: 12,
    padding: 20,
    borderRadius: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 6,
  },
  statusBadgeWhite: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.95)',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 20,
    alignSelf: 'flex-start',
    marginBottom: 12,
    gap: 4,
  },
  statusBadgeWhiteText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.primary,
  },
  projectTitleWhite: {
    fontSize: 20,
    fontWeight: '800',
    color: '#FFFFFF',
    marginBottom: 8,
    lineHeight: 28,
  },
  locationRowWhite: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  locationTextWhite: {
    fontSize: 13,
    color: 'rgba(255,255,255,0.9)',
  },
  quickStatsContainer: {
    flexDirection: 'row',
    backgroundColor: COLORS.surface,
    marginHorizontal: 16,
    marginBottom: 12,
    borderRadius: 12,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  quickStatCard: {
    flex: 1,
    alignItems: 'center',
  },
  quickStatDivider: {
    width: 1,
    backgroundColor: COLORS.border,
    marginHorizontal: 12,
  },
  quickStatValue: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
    marginTop: 8,
    marginBottom: 2,
  },
  quickStatLabel: {
    fontSize: 11,
    color: COLORS.textSecondary,
  },
  sectionHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  messageIconButton: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  sectionIconHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  notesBox: {
    backgroundColor: COLORS.warningLight,
    padding: 12,
    borderRadius: 8,
    borderLeftWidth: 3,
    borderLeftColor: COLORS.warning,
  },
  notesText: {
    fontSize: 13,
    color: COLORS.text,
    lineHeight: 20,
    fontStyle: 'italic',
  },
  specItemRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  specIconBox: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: COLORS.background,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  specTextContainer: {
    flex: 1,
  },
  bidDetailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  bidLabel: {
    fontSize: 13,
    color: COLORS.textSecondary,
  },
  bidValue: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.primary,
  },
  descriptionText: {
    fontSize: 14,
    color: COLORS.text,
    lineHeight: 22,
  },
  specRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  specLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 2,
  },
  specValue: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },
  setupCtaBanner: {
    backgroundColor: COLORS.warningLight,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    borderLeftWidth: 4,
    borderLeftColor: COLORS.warning,
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  setupCtaIcon: {
    marginRight: 12,
    marginTop: 2,
  },
  setupCtaContent: {
    flex: 1,
  },
  setupCtaTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.warning,
    marginBottom: 4,
  },
  setupCtaMessage: {
    fontSize: 13,
    color: COLORS.text,
    lineHeight: 18,
  },
  pendingApprovalBanner: {
    backgroundColor: COLORS.infoLight,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    borderLeftWidth: 4,
    borderLeftColor: COLORS.info,
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  milestoneBadge: {
    backgroundColor: COLORS.successLight,
    borderRadius: 10,
    paddingHorizontal: 8,
    paddingVertical: 2,
    marginLeft: 8,
  },
  milestoneBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    color: COLORS.success,
  },
  milestoneItem: {
    flexDirection: 'row',
    marginBottom: 16,
    paddingBottom: 16,
  },
  milestoneLeft: {
    alignItems: 'center',
    marginRight: 16,
    width: 32,
  },
  milestoneNumber: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: COLORS.border,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 1,
  },
  milestoneNumberCompleted: {
    backgroundColor: COLORS.successLight,
    borderWidth: 2,
    borderColor: COLORS.success,
  },
  milestoneNumberInProgress: {
    backgroundColor: COLORS.warningLight,
    borderWidth: 2,
    borderColor: COLORS.warning,
  },
  milestoneNumberPending: {
    backgroundColor: COLORS.infoLight,
    borderWidth: 2,
    borderColor: COLORS.info,
  },
  milestoneNumberRejected: {
    backgroundColor: '#FEE2E2',
    borderWidth: 2,
    borderColor: COLORS.error,
  },
  milestoneNumberText: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.textSecondary,
  },
  milestoneNumberTextCompleted: {
    color: COLORS.success,
  },
  milestoneNumberTextInProgress: {
    color: COLORS.warning,
  },
  milestoneLine: {
    width: 2,
    flex: 1,
    backgroundColor: COLORS.border,
    marginTop: 4,
  },
  milestoneRight: {
    flex: 1,
    paddingTop: 4,
  },
  milestoneName: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  milestoneStatusRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  milestoneStatusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.infoLight,
    borderRadius: 8,
    paddingHorizontal: 8,
    paddingVertical: 4,
    gap: 4,
  },
  milestoneStatusText: {
    fontSize: 11,
    fontWeight: '600',
  },
});
