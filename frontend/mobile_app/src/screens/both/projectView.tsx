// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Image,
  StatusBar,
  ActivityIndicator,
  Alert,
  Modal,
  TextInput,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { milestones_service } from '../../services/milestones_service';
import { projects_service } from '../../services/projects_service';
import MilestoneApproval from './milestoneApproval';
import MilestoneSetup from '../contractor/milestoneSetup';

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

interface MilestoneItem {
  item_id: number;
  sequence_order: number;
  percentage_progress: number;
  milestone_item_title: string;
  milestone_item_description: string;
  milestone_item_cost: number;
  adjusted_cost?: number | null;
  carry_forward_amount?: number | null;
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
  setup_rej_reason?: string;
  start_date: string;
  end_date: string;
  created_at: string;
  updated_at: string;
  items?: MilestoneItem[];
  payment_plan?: PaymentPlan;
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
  selected_contractor_id: number;
  bidding_due?: string;
  created_at: string;
  bids_count?: number;
  display_status: string;
  milestones: Milestone[];
  milestones_count: number;
  accepted_bid: AcceptedBid;
  contractor_info: ContractorInfo;
  owner_info?: OwnerInfo;
}

interface ProjectViewProps {
  project: Project;
  userId?: number;
  userRole: 'owner' | 'contractor'; // Role-based view
  onClose: () => void;
}

export default function ProjectView({ project, userId, userRole, onClose }: ProjectViewProps) {
  const insets = useSafeAreaInsets();
  const [currentProject, setCurrentProject] = useState(project);
  const [expandedSummary, setExpandedSummary] = useState(false);
  const [approvingMilestone, setApprovingMilestone] = useState<number | null>(null);
  const [rejectingMilestone, setRejectingMilestone] = useState<number | null>(null);
  const [refreshing, setRefreshing] = useState(false);
  const [showMilestoneApproval, setShowMilestoneApproval] = useState(false);
  const [showMilestoneSetup, setShowMilestoneSetup] = useState(false);
  const [showRejectModal, setShowRejectModal] = useState(false);
  const [rejectionReason, setRejectionReason] = useState('');
  const [pendingRejectMilestoneId, setPendingRejectMilestoneId] = useState<number | null>(null);
  const [showEditMilestone, setShowEditMilestone] = useState(false);
  const [milestoneToEdit, setMilestoneToEdit] = useState<Milestone | null>(null);

  const isOwner = userRole === 'owner';
  const isContractor = userRole === 'contractor';

  // Debug logging
  console.log('ProjectView - Project:', currentProject.project_title);
  console.log('ProjectView - User Role:', userRole);
  console.log('ProjectView - Milestones count:', currentProject.milestones_count);
  console.log('ProjectView - Milestones array:', currentProject.milestones);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  };

  const refreshProjectData = async () => {
    if (!userId) return;

    try {
      setRefreshing(true);
      let response;

      if (isOwner) {
        response = await projects_service.get_owner_projects(userId);
      } else {
        response = await projects_service.get_contractor_projects(userId);
      }

      if (response.success) {
        const projects = response.data?.data || response.data || [];
        const updatedProject = projects.find((p: Project) => p.project_id === currentProject.project_id);

        if (updatedProject) {
          setCurrentProject(updatedProject);
        }
      }
    } catch (error) {
      console.error('Error refreshing project data:', error);
    } finally {
      setRefreshing(false);
    }
  };

  // Auto-refresh on mount to ensure fresh milestone/payment data is shown,
  // especially when navigating here from a notification where the project
  // object may be a shallow dashboard list item without milestones populated.
  useEffect(() => {
    refreshProjectData();
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleApproveMilestone = async (milestoneId: number) => {
    if (!userId) {
      Alert.alert('Error', 'User not authenticated');
      return;
    }

    Alert.alert(
      'Approve Milestone',
      'Are you sure you want to approve this milestone?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Approve',
          onPress: async () => {
            setApprovingMilestone(milestoneId);
            try {
              const response = await milestones_service.approve_milestone(milestoneId, userId);

              if (response.success) {
                Alert.alert('Success', 'Milestone approved successfully');
                await refreshProjectData();
              } else {
                Alert.alert('Error', response.message || 'Failed to approve milestone');
              }
            } catch (error) {
              Alert.alert('Error', 'An unexpected error occurred');
            } finally {
              setApprovingMilestone(null);
            }
          },
        },
      ]
    );
  };

  const handleRejectMilestone = (milestoneId: number) => {
    if (!userId) {
      Alert.alert('Error', 'User not authenticated');
      return;
    }

    // Open modal for rejection reason
    setPendingRejectMilestoneId(milestoneId);
    setRejectionReason('');
    setShowRejectModal(true);
  };

  const confirmRejectMilestone = async () => {
    if (!rejectionReason.trim()) {
      Alert.alert('Error', 'Please provide a reason for rejection');
      return;
    }

    if (!pendingRejectMilestoneId || !userId) return;

    setShowRejectModal(false);
    setRejectingMilestone(pendingRejectMilestoneId);

    try {
      const response = await milestones_service.reject_milestone(
        pendingRejectMilestoneId,
        userId,
        rejectionReason.trim()
      );

      if (response.success) {
        Alert.alert('Success', 'Milestone rejected successfully');
        await refreshProjectData();
      } else {
        Alert.alert('Error', response.message || 'Failed to reject milestone');
      }
    } catch (error) {
      Alert.alert('Error', 'An unexpected error occurred');
    } finally {
      setRejectingMilestone(null);
      setPendingRejectMilestoneId(null);
      setRejectionReason('');
    }
  };

  const handleEditMilestoneSetup = (milestone: Milestone) => {
    setMilestoneToEdit(milestone);
    setShowEditMilestone(true);
  };

  const renderMilestoneStatusBadge = (milestone: Milestone) => {
    const { setup_status, milestone_status } = milestone;

    if (setup_status === 'submitted') {
      return (
        <View style={[styles.milestoneStatusBadge, { backgroundColor: COLORS.infoLight }]}>
          <Feather name="clock" size={10} color={COLORS.info} />
          <Text style={[styles.milestoneStatusText, { color: COLORS.info }]}>Pending Approval</Text>
        </View>
      );
    }

    if (setup_status === 'rejected') {
      return (
        <View style={[styles.milestoneStatusBadge, { backgroundColor: COLORS.errorLight }]}>
          <Feather name="x-circle" size={10} color={COLORS.error} />
          <Text style={[styles.milestoneStatusText, { color: COLORS.error }]}>Rejected</Text>
        </View>
      );
    }

    if (milestone_status === 'completed' || milestone_status === 'approved') {
      return (
        <View style={[styles.milestoneStatusBadge, { backgroundColor: COLORS.successLight }]}>
          <Feather name="check-circle" size={10} color={COLORS.success} />
          <Text style={[styles.milestoneStatusText, { color: COLORS.success }]}>Completed</Text>
        </View>
      );
    }

    if (milestone_status === 'in_progress') {
      return (
        <View style={[styles.milestoneStatusBadge, { backgroundColor: COLORS.warningLight }]}>
          <Feather name="activity" size={10} color={COLORS.warning} />
          <Text style={[styles.milestoneStatusText, { color: COLORS.warning }]}>In Progress</Text>
        </View>
      );
    }

    if (setup_status === 'approved') {
      return (
        <View style={[styles.milestoneStatusBadge, { backgroundColor: COLORS.successLight }]}>
          <Feather name="check-circle" size={10} color={COLORS.success} />
          <Text style={[styles.milestoneStatusText, { color: COLORS.success }]}>Approved</Text>
        </View>
      );
    }

    return null;
  };

  const renderStatusBanner = () => {
    if (currentProject.display_status === 'waiting_milestone_setup') {
      const bannerMessage = isOwner
        ? 'The contractor is preparing the milestone proposal for this project.'
        : 'Please set up milestones for this project.';

      return (
        <View style={styles.statusBanner}>
          <View style={[styles.statusBannerContent, { backgroundColor: COLORS.warningLight, borderLeftColor: COLORS.warning }]}>
            <Feather name="clock" size={20} color={COLORS.warning} style={styles.statusBannerIcon} />
            <View style={styles.statusBannerText}>
              <Text style={[styles.statusBannerTitle, { color: COLORS.warning }]}>
                {isOwner ? 'Waiting for Setup' : 'Setup Required'}
              </Text>
              <Text style={styles.statusBannerMessage}>{bannerMessage}</Text>
            </View>
          </View>
        </View>
      );
    }

    // Show info banner if there are milestones pending approval (regardless of display_status)
    const hasPendingMilestones = currentProject.milestones?.some(m => m.setup_status === 'submitted');
    if (hasPendingMilestones) {
      const pendingCount = (currentProject.milestones || []).filter(m => m.setup_status === 'submitted').length;
      const bannerMessage = isOwner
        ? `${pendingCount} milestone${pendingCount > 1 ? 's' : ''} waiting for your approval. Review and approve below.`
        : `${pendingCount} milestone${pendingCount > 1 ? 's' : ''} pending owner approval.`;

      return (
        <View style={styles.statusBanner}>
          <View style={[styles.statusBannerContent, { backgroundColor: COLORS.infoLight, borderLeftColor: COLORS.info }]}>
            <Feather name="alert-circle" size={20} color={COLORS.info} style={styles.statusBannerIcon} />
            <View style={styles.statusBannerText}>
              <Text style={[styles.statusBannerTitle, { color: COLORS.info }]}>
                {isOwner ? 'Action Required' : 'Pending Approval'}
              </Text>
              <Text style={styles.statusBannerMessage}>{bannerMessage}</Text>
            </View>
          </View>
        </View>
      );
    }

    return null;
  };

  const getMilestoneCardText = () => {
    // Check if any milestones are approved
    const hasApprovedMilestones = (currentProject.milestones || []).some(m => m.setup_status === 'approved');
    const hasPendingMilestones = (currentProject.milestones || []).some(m => m.setup_status === 'submitted');
    
    if (isOwner) {
      if (hasApprovedMilestones) {
      return {
          title: 'Check Project Progress',
          description: 'Track milestone completion, review progress reports, and monitor payment history.',
          tapPrompt: 'Tap to view project progress',
          icon: 'trending-up',
          iconColor: COLORS.success,
        };
      } else if (hasPendingMilestones) {
        return {
          title: 'Review Milestone Setup',
          description: 'The contractor has submitted a milestone proposal. Review and approve the timeline and payment breakdown.',
          tapPrompt: 'Tap to review and approve',
          icon: 'clock',
          iconColor: COLORS.warning,
        };
      } else {
        return {
          title: 'Milestone Setup',
          description: 'The milestone timeline, payment breakdown, and project duration are being prepared by the contractor.',
          tapPrompt: 'Tap to view details',
          icon: 'clipboard',
          iconColor: COLORS.info,
      };
    }
    }
    
    // Contractor view
    return {
      title: 'Milestone Progress',
      description: 'View the milestone timeline, submit progress reports, and track project completion.',
      tapPrompt: 'Tap to view milestones',
      icon: 'target',
      iconColor: COLORS.primary,
    };
  };

  const milestoneCardText = getMilestoneCardText();

  return (
    <View style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.surface} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton} activeOpacity={0.7}>
          <Feather name="arrow-left" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Project Details</Text>
        <TouchableOpacity style={styles.moreButton} activeOpacity={0.7}>
          <Feather name="more-vertical" size={24} color={COLORS.text} />
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* Project Summary Card - Collapsible */}
        <TouchableOpacity
          style={styles.summaryCard}
          onPress={() => setExpandedSummary(!expandedSummary)}
          activeOpacity={0.9}
        >
          <LinearGradient
            colors={[COLORS.primary, COLORS.primaryDark]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.summaryGradient}
          >
            <View style={styles.summaryHeader}>
              <View style={styles.summaryHeaderLeft}>
                <Text style={styles.summaryTitle}>{currentProject.project_title}</Text>
                <View style={styles.summaryLocationRow}>
                  <Feather name="map-pin" size={14} color="rgba(255,255,255,0.9)" />
                  <Text style={styles.summaryLocation}>{currentProject.project_location}</Text>
                </View>
              </View>
              <View style={styles.expandIconContainer}>
                <Feather
                  name={expandedSummary ? "chevron-up" : "chevron-down"}
                  size={24}
                  color="#FFFFFF"
                />
              </View>
            </View>

            {expandedSummary && (
              <View style={styles.summaryExpandedContent}>
                <View style={styles.summaryDivider} />

                {/* Project Description */}
                <View style={styles.summarySection}>
                  <Text style={styles.summarySectionTitle}>Description</Text>
                  <Text style={styles.summarySectionText}>{currentProject.project_description}</Text>
                </View>

                {/* Project Specs */}
                <View style={styles.summarySection}>
                  <Text style={styles.summarySectionTitle}>Specifications</Text>
                  <View style={styles.summarySpecGrid}>
                    <View style={styles.summarySpecItem}>
                      <Text style={styles.summarySpecLabel}>Property Type</Text>
                      <Text style={styles.summarySpecValue}>{currentProject.property_type}</Text>
                    </View>
                    <View style={styles.summarySpecItem}>
                      <Text style={styles.summarySpecLabel}>Category</Text>
                      <Text style={styles.summarySpecValue}>{currentProject.type_name}</Text>
                    </View>
                    <View style={styles.summarySpecItem}>
                      <Text style={styles.summarySpecLabel}>Lot Size</Text>
                      <Text style={styles.summarySpecValue}>{currentProject.lot_size} sqm</Text>
                    </View>
                    <View style={styles.summarySpecItem}>
                      <Text style={styles.summarySpecLabel}>Floor Area</Text>
                      <Text style={styles.summarySpecValue}>{currentProject.floor_area} sqm</Text>
                    </View>
                  </View>
                </View>

                {/* Original Budget */}
                <View style={styles.summarySection}>
                  <Text style={styles.summarySectionTitle}>Original Budget Range</Text>
                  <Text style={styles.summaryBudget}>
                    {formatCurrency(currentProject.budget_range_min)} - {formatCurrency(currentProject.budget_range_max)}
                  </Text>
                </View>

                {/* Contractor Info (for Owner view) */}
                {isOwner && currentProject.contractor_info && (
                  <View style={styles.summarySection}>
                    <Text style={styles.summarySectionTitle}>Contractor & Agreement</Text>

                    <View style={styles.contractorInfoInline}>
                      <View style={styles.contractorHeaderInline}>
                        <View style={styles.contractorAvatarInline}>
                          {currentProject.contractor_info.profile_pic ? (
                            <Image
                              source={{ uri: currentProject.contractor_info.profile_pic }}
                              style={styles.contractorAvatarImage}
                            />
                          ) : (
                            <Feather name="user" size={20} color="rgba(255,255,255,0.7)" />
                          )}
                        </View>
                        <View style={styles.contractorDetailsInline}>
                          <Text style={styles.contractorNameInline}>{currentProject.contractor_info.company_name}</Text>
                          <Text style={styles.contractorUsernameInline}>@{currentProject.contractor_info.username}</Text>
                        </View>
                      </View>

                      <View style={styles.contractorMetaRowInline}>
                        <View style={styles.contractorMetaItemInline}>
                          <Feather name="award" size={12} color="rgba(255,255,255,0.8)" />
                          <Text style={styles.contractorMetaTextInline}>{currentProject.contractor_info.years_of_experience} years exp</Text>
                        </View>
                        <View style={styles.contractorMetaItemInline}>
                          <Feather name="check-circle" size={12} color="rgba(255,255,255,0.8)" />
                          <Text style={styles.contractorMetaTextInline}>{currentProject.contractor_info.completed_projects} projects</Text>
                        </View>
                      </View>

                      {currentProject.accepted_bid && (
                        <View style={styles.bidDetailsInline}>
                          <View style={styles.bidDetailRowInline}>
                            <Text style={styles.bidDetailLabelInline}>Agreed Cost</Text>
                            <Text style={styles.bidDetailValueInline}>{formatCurrency(currentProject.accepted_bid.proposed_cost)}</Text>
                          </View>
                          <View style={styles.bidDetailRowInline}>
                            <Text style={styles.bidDetailLabelInline}>Timeline</Text>
                            <Text style={styles.bidDetailValueInline}>{currentProject.accepted_bid.estimated_timeline}</Text>
                          </View>
                          {currentProject.accepted_bid.contractor_notes && (
                            <View style={styles.bidNotesContainerInline}>
                              <Text style={styles.bidNotesLabelInline}>Contractor Notes</Text>
                              <Text style={styles.bidNotesTextInline}>{currentProject.accepted_bid.contractor_notes}</Text>
                            </View>
                          )}
                        </View>
                      )}
                    </View>
                  </View>
                )}

                {/* Owner Info (for Contractor view) */}
                {isContractor && currentProject.owner_info && (
                  <View style={styles.summarySection}>
                    <Text style={styles.summarySectionTitle}>Property Owner</Text>

                    <View style={styles.contractorInfoInline}>
                      <View style={styles.contractorHeaderInline}>
                        <View style={styles.contractorAvatarInline}>
                          {currentProject.owner_info.profile_pic ? (
                            <Image
                              source={{ uri: currentProject.owner_info.profile_pic }}
                              style={styles.contractorAvatarImage}
                            />
                          ) : (
                            <Feather name="user" size={20} color="rgba(255,255,255,0.7)" />
                          )}
                        </View>
                        <View style={styles.contractorDetailsInline}>
                          <Text style={styles.contractorNameInline}>
                            {currentProject.owner_info.first_name} {currentProject.owner_info.last_name}
                          </Text>
                          <Text style={styles.contractorUsernameInline}>@{currentProject.owner_info.username}</Text>
                        </View>
                      </View>

                      {currentProject.accepted_bid && (
                        <View style={styles.bidDetailsInline}>
                          <View style={styles.bidDetailRowInline}>
                            <Text style={styles.bidDetailLabelInline}>Your Proposed Cost</Text>
                            <Text style={styles.bidDetailValueInline}>{formatCurrency(currentProject.accepted_bid.proposed_cost)}</Text>
                          </View>
                          <View style={styles.bidDetailRowInline}>
                            <Text style={styles.bidDetailLabelInline}>Timeline</Text>
                            <Text style={styles.bidDetailValueInline}>{currentProject.accepted_bid.estimated_timeline}</Text>
                          </View>
                        </View>
                      )}
                    </View>
                  </View>
                )}
              </View>
            )}
          </LinearGradient>
        </TouchableOpacity>

        {/* Status Banner */}
        {renderStatusBanner()}

        {/* Contractor Rejection Notice - Show rejected milestones with reasons */}
        {isContractor && currentProject.milestones?.some(m => m.setup_status === 'rejected' && m.setup_rej_reason) && (
          <View style={styles.contractorRejectionSection}>
            {currentProject.milestones
              .filter(m => m.setup_status === 'rejected' && m.setup_rej_reason)
              .map((rejectedMilestone) => (
                <View key={rejectedMilestone.milestone_id} style={styles.contractorRejectionNotice}>
                  <View style={styles.contractorRejectionNoticeHeader}>
                    <View style={styles.contractorRejectionNoticeIcon}>
                      <Feather name="alert-octagon" size={22} color={COLORS.error} />
                    </View>
                    <View style={styles.contractorRejectionNoticeTitles}>
                      <Text style={styles.contractorRejectionNoticeTitle}>Setup Rejected</Text>
                      <Text style={styles.contractorRejectionNoticeSubtitle}>
                        {rejectedMilestone.milestone_name}
                      </Text>
                    </View>
                  </View>

                  <View style={styles.contractorRejectionNoticeContent}>
                    <View style={styles.contractorFeedbackHeader}>
                      <Feather name="message-circle" size={14} color={COLORS.textSecondary} />
                      <Text style={styles.contractorFeedbackLabel}>Owner's Feedback:</Text>
                    </View>
                    <Text style={styles.contractorFeedbackText}>
                      {rejectedMilestone.setup_rej_reason}
                    </Text>
                  </View>

                  <View style={styles.contractorRejectionNoticeAction}>
                    <Feather name="tool" size={14} color={COLORS.accent} />
                    <Text style={styles.contractorRejectionNoticeActionText}>
                      Update your milestone setup to address the feedback
                    </Text>
                  </View>

                  <TouchableOpacity
                    style={styles.contractorEditSetupButton}
                    onPress={() => handleEditMilestoneSetup(rejectedMilestone)}
                    activeOpacity={0.7}
                  >
                    <Feather name="edit-3" size={18} color="#FFFFFF" />
                    <Text style={styles.contractorEditSetupButtonText}>Modify</Text>
                  </TouchableOpacity>
                </View>
              ))}
          </View>
        )}

        {/* Milestone Setup Review Section */}
        {currentProject.milestones && currentProject.milestones.length > 0 ? (
          <TouchableOpacity
            style={styles.milestoneSetupCard}
            onPress={() => setShowMilestoneApproval(true)}
            activeOpacity={0.7}
          >
            {/* Card Header */}
            <View style={styles.milestoneCardHeader}>
              <View style={[styles.milestoneIconContainer, { backgroundColor: milestoneCardText.iconColor + '20' }]}>
                <Feather name={milestoneCardText.icon} size={24} color={milestoneCardText.iconColor} />
              </View>
              <View style={styles.milestoneHeaderContent}>
                <Text style={styles.milestoneCardTitle}>{milestoneCardText.title}</Text>
              </View>
              <Feather name="chevron-right" size={24} color={COLORS.textMuted} />
            </View>

            {/* Card Description */}
            <View style={styles.milestoneCardBody}>
              <Text style={styles.milestoneDescription}>
                {milestoneCardText.description}
              </Text>

              {/* Project Info Card */}
              <View style={styles.projectInfoCards}>
                <View style={styles.projectInfoCard}>
                  <Feather name="dollar-sign" size={18} color={COLORS.accent} />
                  <View style={styles.projectInfoContent}>
                    <Text style={styles.projectInfoLabel}>Project Budget</Text>
                    <Text style={styles.projectInfoValue}>{formatCurrency(currentProject.accepted_bid?.proposed_cost || 0)}</Text>
                </View>
                </View>
              </View>

              {/* Action Prompt */}
              <View style={[styles.milestoneActionPrompt, { backgroundColor: milestoneCardText.iconColor + '15' }]}>
                <Feather name="arrow-right-circle" size={18} color={milestoneCardText.iconColor} />
                <Text style={[styles.milestoneActionText, { color: milestoneCardText.iconColor }]}>
                  {milestoneCardText.tapPrompt}
                </Text>
              </View>
            </View>

          </TouchableOpacity>
        ) : (
          <View style={styles.emptyMilestones}>
            <Feather name="inbox" size={48} color={COLORS.border} />
            <Text style={styles.emptyMilestonesTitle}>No Milestones Yet</Text>
            <Text style={styles.emptyMilestonesText}>
              {isOwner
                ? "The contractor is preparing the milestone proposal for this project. You'll be notified once it's ready for review."
                : "Set up milestones to define the project timeline and payment schedule."
              }
            </Text>
            {isContractor && (
              <TouchableOpacity
                style={styles.setupMilestoneButton}
                onPress={() => setShowMilestoneSetup(true)}
                activeOpacity={0.8}
              >
                <Feather name="settings" size={18} color="#FFFFFF" />
                <Text style={styles.setupMilestoneButtonText}>Setup Milestone</Text>
              </TouchableOpacity>
            )}
          </View>
        )}

        <View style={{ height: 32 }} />
      </ScrollView>

      {/* Milestone Approval Screen - Full Screen Modal */}
      <Modal
        visible={showMilestoneApproval}
        animationType="slide"
        presentationStyle="fullScreen"
        onRequestClose={() => setShowMilestoneApproval(false)}
      >
        <MilestoneApproval
          route={{
            params: {
              projectId: currentProject.project_id,
              projectTitle: currentProject.project_title,
              projectDescription: currentProject.project_description,
              projectLocation: currentProject.project_location,
              contractorName: currentProject.contractor_info?.company_name || 'Contractor',
              propertyType: currentProject.type_name || currentProject.property_type,
              projectStartDate: (currentProject.milestones && currentProject.milestones[0])?.start_date || currentProject.created_at,
              projectEndDate: (currentProject.milestones && currentProject.milestones[currentProject.milestones.length - 1])?.end_date || currentProject.created_at,
              totalCost: (currentProject.milestones && currentProject.milestones[0])?.payment_plan?.total_project_cost || currentProject.accepted_bid?.proposed_cost || 0,
              paymentMethod: (currentProject.milestones && currentProject.milestones[0])?.payment_plan?.payment_mode || 'milestone',
              milestones: currentProject.milestones || [],
              userId: userId,
              userRole: userRole,
              projectStatus: currentProject.project_status,
              onApprovalComplete: async () => {
                await refreshProjectData();
                setShowMilestoneApproval(false);
              },
            },
          }}
          navigation={{
            goBack: () => setShowMilestoneApproval(false),
          }}
        />
      </Modal>

      {/* Milestone Setup Screen - Full Screen Modal */}
      <Modal
        visible={showMilestoneSetup}
        animationType="slide"
        presentationStyle="fullScreen"
        onRequestClose={() => setShowMilestoneSetup(false)}
      >
        <MilestoneSetup
          project={currentProject}
          userId={userId}
          onClose={() => {
            setShowMilestoneSetup(false);
            refreshProjectData(); // Refresh to show new milestones
          }}
          onSave={() => {
            setShowMilestoneSetup(false);
            refreshProjectData(); // Refresh to show new milestones
          }}
        />
      </Modal>

      {/* Milestone Edit Modal */}
      {showEditMilestone && milestoneToEdit && (
        <Modal
          visible={showEditMilestone}
          animationType="slide"
          presentationStyle="fullScreen"
          onRequestClose={() => {
            setShowEditMilestone(false);
            setMilestoneToEdit(null);
          }}
        >
          <MilestoneSetup
            project={{
              project_id: currentProject.project_id,
              project_title: currentProject.project_title,
            }}
            userId={userId}
            onClose={() => {
              setShowEditMilestone(false);
              setMilestoneToEdit(null);
            }}
            onSave={async () => {
              setShowEditMilestone(false);
              setMilestoneToEdit(null);
              await refreshProjectData();
            }}
            editMode={true}
            existingMilestone={milestoneToEdit}
          />
        </Modal>
      )}

      {/* Milestone Rejection Modal */}
      <Modal
        visible={showRejectModal}
        transparent={true}
        animationType="fade"
        onRequestClose={() => {
          setShowRejectModal(false);
          setPendingRejectMilestoneId(null);
          setRejectionReason('');
        }}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.rejectModalContent}>
            <View style={styles.rejectModalHeader}>
              <View style={styles.rejectIconContainer}>
                <Feather name="alert-circle" size={24} color={COLORS.danger} />
              </View>
              <Text style={styles.rejectModalTitle}>Reject Milestone Setup</Text>
              <Text style={styles.rejectModalSubtitle}>
                Please provide a reason for rejecting this milestone. This will help the contractor understand what needs to be changed.
              </Text>
            </View>

            <View style={styles.rejectInputContainer}>
              <Text style={styles.rejectInputLabel}>
                Rejection Reason <Text style={styles.requiredStar}>*</Text>
              </Text>
              <TextInput
                style={styles.rejectTextInput}
                value={rejectionReason}
                onChangeText={setRejectionReason}
                placeholder="Explain why you're rejecting this milestone setup..."
                placeholderTextColor={COLORS.textMuted}
                multiline
                numberOfLines={5}
                maxLength={500}
                textAlignVertical="top"
              />
              <Text style={styles.characterCount}>
                {rejectionReason.length}/500
              </Text>
            </View>

            <View style={styles.rejectModalActions}>
              <TouchableOpacity
                style={styles.rejectCancelButton}
                onPress={() => {
                  setShowRejectModal(false);
                  setPendingRejectMilestoneId(null);
                  setRejectionReason('');
                }}
              >
                <Text style={styles.rejectCancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[
                  styles.rejectConfirmButton,
                  !rejectionReason.trim() && styles.rejectConfirmButtonDisabled
                ]}
                onPress={confirmRejectMilestone}
                disabled={!rejectionReason.trim()}
              >
                <Feather name="x-circle" size={18} color="#FFFFFF" />
                <Text style={styles.rejectConfirmButtonText}>Reject Milestone</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>
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
  moreButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
  },
  summaryCard: {
    borderRadius: 16,
    overflow: 'hidden',
    marginBottom: 16,
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
  },
  summaryGradient: {
    padding: 20,
  },
  summaryHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
  },
  summaryHeaderLeft: {
    flex: 1,
  },
  summaryTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#FFFFFF',
    marginBottom: 8,
  },
  summaryLocationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  summaryLocation: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.9)',
  },
  expandIconContainer: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: 'rgba(255,255,255,0.2)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  summaryExpandedContent: {
    marginTop: 16,
  },
  summaryDivider: {
    height: 1,
    backgroundColor: 'rgba(255,255,255,0.2)',
    marginBottom: 16,
  },
  summarySection: {
    marginBottom: 16,
  },
  summarySectionTitle: {
    fontSize: 12,
    fontWeight: '600',
    color: 'rgba(255,255,255,0.7)',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 8,
  },
  summarySectionText: {
    fontSize: 14,
    color: '#FFFFFF',
    lineHeight: 20,
  },
  summarySpecGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  summarySpecItem: {
    width: '48%',
  },
  summarySpecLabel: {
    fontSize: 11,
    color: 'rgba(255,255,255,0.7)',
    marginBottom: 4,
  },
  summarySpecValue: {
    fontSize: 14,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  summaryBudget: {
    fontSize: 18,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  contractorAvatarImage: {
    width: '100%',
    height: '100%',
  },
  statusBanner: {
    marginBottom: 16,
  },
  statusBannerContent: {
    flexDirection: 'row',
    padding: 16,
    borderRadius: 12,
    borderLeftWidth: 4,
  },
  statusBannerIcon: {
    marginRight: 12,
    marginTop: 2,
  },
  statusBannerText: {
    flex: 1,
  },
  statusBannerTitle: {
    fontSize: 15,
    fontWeight: '700',
    marginBottom: 4,
  },
  statusBannerMessage: {
    fontSize: 13,
    color: COLORS.text,
    lineHeight: 18,
  },
  sectionIconHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginLeft: 8,
    flex: 1,
  },
  milestoneBadge: {
    backgroundColor: COLORS.primaryLight,
    borderRadius: 10,
    paddingHorizontal: 8,
    paddingVertical: 2,
    marginLeft: 8,
  },
  milestoneBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    color: COLORS.primary,
  },
  milestoneStatusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 8,
    paddingHorizontal: 8,
    paddingVertical: 4,
    gap: 4,
  },
  milestoneStatusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  emptyMilestones: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 32,
    alignItems: 'center',
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
  },
  emptyMilestonesTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginTop: 16,
    marginBottom: 8,
  },
  emptyMilestonesText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 20,
  },
  setupMilestoneButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.primary,
    paddingHorizontal: 24,
    paddingVertical: 14,
    borderRadius: 12,
    marginTop: 8,
    gap: 8,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  setupMilestoneButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  // Inline contractor styles (inside dropdown)
  contractorInfoInline: {
    gap: 12,
  },
  contractorHeaderInline: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 8,
  },
  contractorAvatarInline: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.15)',
    justifyContent: 'center',
    alignItems: 'center',
    overflow: 'hidden',
  },
  contractorDetailsInline: {
    flex: 1,
  },
  contractorNameInline: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFFFFF',
    marginBottom: 2,
  },
  contractorUsernameInline: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.8)',
  },
  contractorMetaRowInline: {
    flexDirection: 'row',
    gap: 16,
    marginBottom: 10,
  },
  contractorMetaItemInline: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  contractorMetaTextInline: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.8)',
  },
  bidDetailsInline: {
    backgroundColor: 'rgba(255,255,255,0.1)',
    borderRadius: 10,
    padding: 12,
    gap: 8,
  },
  bidDetailRowInline: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  bidDetailLabelInline: {
    fontSize: 13,
    color: 'rgba(255,255,255,0.8)',
  },
  bidDetailValueInline: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  bidNotesContainerInline: {
    marginTop: 8,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255,255,255,0.2)',
  },
  bidNotesLabelInline: {
    fontSize: 12,
    fontWeight: '600',
    color: 'rgba(255,255,255,0.8)',
    marginBottom: 4,
  },
  bidNotesTextInline: {
    fontSize: 13,
    color: '#FFFFFF',
    lineHeight: 18,
  },
  // Milestone Setup Card (full-page style)
  milestoneSetupCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 16,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
  },
  // Milestone Summary Preview
  milestoneSummaryPreview: {
    marginTop: 16,
    gap: 16,
  },
  milestoneSummaryText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
  },
  milestoneSummaryStats: {
    flexDirection: 'row',
    backgroundColor: COLORS.background,
    borderRadius: 12,
    padding: 16,
    alignItems: 'center',
  },
  summaryStatItem: {
    flex: 1,
    alignItems: 'center',
  },
  summaryStatLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginBottom: 4,
    textAlign: 'center',
  },
  summaryStatValue: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    textAlign: 'center',
  },
  summaryStatDivider: {
    width: 1,
    height: 32,
    backgroundColor: COLORS.border,
  },
  tapToReviewPrompt: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 12,
    backgroundColor: COLORS.primaryLight,
    borderRadius: 8,
  },
  tapToReviewText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.primary,
  },

  // New Professional Milestone Card Styles
  milestoneCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 16,
  },
  milestoneIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  milestoneHeaderContent: {
    flex: 1,
  },
  milestoneCardTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
  },
  milestoneCardBody: {
    gap: 16,
  },
  milestoneDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
  },
  projectInfoCards: {
    gap: 12,
  },
  projectInfoCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.borderLight,
    borderRadius: 12,
    padding: 14,
    gap: 12,
  },
  projectInfoContent: {
    flex: 1,
  },
  projectInfoLabel: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginBottom: 2,
  },
  projectInfoValue: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
  },
  milestoneActionPrompt: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 10,
  },
  milestoneActionText: {
    fontSize: 14,
    fontWeight: '600',
  },

  // Rejection Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  rejectModalContent: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    width: '100%',
    maxWidth: 500,
    padding: 24,
    gap: 20,
  },
  rejectModalHeader: {
    gap: 12,
    alignItems: 'center',
  },
  rejectIconContainer: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: COLORS.dangerLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  rejectModalTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    textAlign: 'center',
  },
  rejectModalSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
  },
  rejectInputContainer: {
    gap: 8,
  },
  rejectInputLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },
  requiredStar: {
    color: COLORS.error,
  },
  rejectTextInput: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    color: COLORS.text,
    minHeight: 120,
    backgroundColor: COLORS.background,
  },
  characterCount: {
    fontSize: 12,
    color: COLORS.textMuted,
    textAlign: 'right',
  },
  rejectModalActions: {
    flexDirection: 'row',
    gap: 12,
  },
  rejectCancelButton: {
    flex: 1,
    paddingVertical: 14,
    paddingHorizontal: 20,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  rejectCancelButtonText: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
  },
  rejectConfirmButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 14,
    paddingHorizontal: 20,
    borderRadius: 8,
    backgroundColor: COLORS.error,
  },
  rejectConfirmButtonDisabled: {
    opacity: 0.5,
  },
  rejectConfirmButtonText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#FFFFFF',
  },

  // Contractor Rejection Notice Styles
  contractorRejectionSection: {
    paddingHorizontal: 16,
    paddingTop: 8,
    paddingBottom: 8,
    gap: 12,
  },
  contractorRejectionNotice: {
    backgroundColor: '#FFF1F2',
    borderRadius: 12,
    padding: 16,
    borderLeftWidth: 4,
    borderLeftColor: COLORS.error,
    gap: 14,
  },
  contractorRejectionNoticeHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  contractorRejectionNoticeIcon: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
  },
  contractorRejectionNoticeTitles: {
    flex: 1,
  },
  contractorRejectionNoticeTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.error,
    marginBottom: 3,
  },
  contractorRejectionNoticeSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    fontWeight: '500',
  },
  contractorRejectionNoticeContent: {
    backgroundColor: COLORS.surface,
    borderRadius: 8,
    padding: 12,
    gap: 8,
  },
  contractorFeedbackHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  contractorFeedbackLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textSecondary,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  contractorFeedbackText: {
    fontSize: 14,
    color: COLORS.text,
    lineHeight: 20,
  },
  contractorRejectionNoticeAction: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: COLORS.accentLight,
    borderRadius: 6,
    padding: 10,
  },
  contractorRejectionNoticeActionText: {
    flex: 1,
    fontSize: 13,
    color: COLORS.text,
    fontWeight: '500',
    lineHeight: 18,
  },
  contractorEditSetupButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
    backgroundColor: COLORS.primary,
    borderRadius: 8,
    paddingVertical: 14,
    paddingHorizontal: 20,
    marginTop: 4,
  },
  contractorEditSetupButtonText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFFFFF',
  },
});
