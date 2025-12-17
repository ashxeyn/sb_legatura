// @ts-nocheck
import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Image,
  Dimensions,
  StatusBar,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import EditProject from './editProject';
import ProjectBids from './projectBids';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

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
  type_name: string;
  type_id?: number;
  project_status: string;
  project_post_status: string;
  bidding_deadline?: string;
  created_at: string;
  bids_count?: number;
  display_status?: string;
  accepted_bid?: {
    bid_id: number;
    proposed_cost: number;
    estimated_timeline: number;
    contractor_notes: string;
    submitted_at: string;
    company_name: string;
    company_phone: string;
    company_email: string;
    company_website?: string;
    years_of_experience: number;
    completed_projects: number;
    picab_category: string;
    username: string;
    profile_pic?: string;
  };
  contractor_info?: {
    company_name: string;
    company_phone: string;
    company_email: string;
    company_website?: string;
    years_of_experience: number;
    completed_projects: number;
    picab_category: string;
    username: string;
    profile_pic?: string;
  };
}

interface ProjectDetailsProps {
  project: Project;
  userId?: number;
  onClose: () => void;
  onProjectUpdated?: (updatedProject: Project) => void;
}

export default function ProjectDetails({ project, userId, onClose, onProjectUpdated }: ProjectDetailsProps) {
  const insets = useSafeAreaInsets();
  const [showEditProject, setShowEditProject] = useState(false);
  const [showBids, setShowBids] = useState(false);
  const [currentProject, setCurrentProject] = useState(project);

  const formatBudget = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const getStatusConfig = (status: string, postStatus: string) => {
    if (postStatus === 'under_review') return { color: COLORS.warning, bg: COLORS.warningLight, label: 'Under Review', icon: 'clock' };
    if (postStatus === 'rejected') return { color: COLORS.error, bg: '#FEE2E2', label: 'Rejected', icon: 'x-circle' };
    if (status === 'waiting_milestone_setup') return { color: COLORS.info, bg: COLORS.infoLight, label: 'Waiting for Milestone Setup', icon: 'alert-circle' };
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

  const statusConfig = getStatusConfig(currentProject.display_status || currentProject.project_status, currentProject.project_post_status);
  const daysRemaining = currentProject.bidding_deadline ? getDaysRemaining(currentProject.bidding_deadline) : null;

  const handleEditSave = (updatedProject: Project) => {
    setCurrentProject(updatedProject);
    setShowEditProject(false);
    if (onProjectUpdated) {
      onProjectUpdated(updatedProject);
    }
  };

  // Show bids screen
  if (showBids) {
    return (
      <ProjectBids
        project={currentProject}
        userId={userId || 0}
        onClose={() => setShowBids(false)}
        onBidAccepted={() => {
          // Optionally refresh project data after a bid is accepted
        }}
      />
    );
  }

  // Show edit project screen
  if (showEditProject) {
    return (
      <EditProject
        project={currentProject}
        userId={userId || 0}
        onClose={() => setShowEditProject(false)}
        onSave={handleEditSave}
      />
    );
  }

  const renderInfoRow = (icon: string, label: string, value: string | number | undefined) => {
    if (!value) return null;
    return (
      <View style={styles.infoRow}>
        <View style={styles.infoIconContainer}>
          <Feather name={icon as any} size={18} color={COLORS.primary} />
        </View>
        <View style={styles.infoContent}>
          <Text style={styles.infoLabel}>{label}</Text>
          <Text style={styles.infoValue}>{value}</Text>
        </View>
      </View>
    );
  };

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.surface} />
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="arrow-left" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Project Details</Text>
        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* Status Card */}
        <View style={styles.statusCard}>
          <View style={[styles.statusBadgeLarge, { backgroundColor: statusConfig.bg }]}>
            <Feather name={statusConfig.icon as any} size={20} color={statusConfig.color} />
            <Text style={[styles.statusTextLarge, { color: statusConfig.color }]}>
              {statusConfig.label}
            </Text>
          </View>
          {daysRemaining !== null && daysRemaining > 0 && (
            <View style={styles.deadlineContainer}>
              <Feather
                name="clock"
                size={16}
                color={daysRemaining <= 3 ? COLORS.error : COLORS.textSecondary}
              />
              <Text style={[
                styles.deadlineText,
                daysRemaining <= 3 && styles.deadlineUrgent
              ]}>
                {daysRemaining} days remaining
              </Text>
            </View>
          )}
        </View>

        {/* Project Title & Type */}
        <View style={styles.titleSection}>
          <View style={styles.typeTag}>
            <Feather name="briefcase" size={14} color={COLORS.primary} />
            <Text style={styles.typeText}>{currentProject.type_name}</Text>
          </View>
          <Text style={styles.projectTitle}>{currentProject.project_title}</Text>
        </View>

        {/* Description */}
        <View style={styles.sectionCompact}>
          <Text style={styles.sectionTitle}>Description</Text>
          <View style={styles.descriptionCard}>
            <Text style={styles.descriptionText}>{currentProject.project_description}</Text>
          </View>
        </View>

        {/* Budget */}
        <View style={styles.sectionCompact}>
          <Text style={styles.sectionTitle}>Budget Range</Text>
          <View style={styles.budgetCard}>
            <View style={styles.budgetItem}>
              <Text style={styles.budgetLabel}>Minimum</Text>
              <Text style={styles.budgetValue}>{formatBudget(currentProject.budget_range_min)}</Text>
            </View>
            <View style={styles.budgetDivider} />
            <View style={styles.budgetItem}>
              <Text style={styles.budgetLabel}>Maximum</Text>
              <Text style={styles.budgetValue}>{formatBudget(currentProject.budget_range_max)}</Text>
            </View>
          </View>
        </View>

        {/* Project Details */}
        <View style={styles.sectionCompact}>
          <Text style={styles.sectionTitle}>Project Information</Text>
          <View style={styles.infoCard}>
            {renderInfoRow('map-pin', 'Location', currentProject.project_location)}
            {renderInfoRow('home', 'Property Type', currentProject.property_type)}
            {renderInfoRow('maximize', 'Lot Size', currentProject.lot_size ? `${currentProject.lot_size} sqm` : undefined)}
            {renderInfoRow('square', 'Floor Area', currentProject.floor_area ? `${currentProject.floor_area} sqm` : undefined)}
            {renderInfoRow('calendar', 'Bidding Deadline', currentProject.bidding_deadline ? formatDate(currentProject.bidding_deadline) : undefined)}
            {renderInfoRow('clock', 'Posted On', formatDate(currentProject.created_at))}
          </View>
        </View>

        {/* Accepted Bid Section - Show when contractor is selected */}
        {currentProject.display_status === 'waiting_milestone_setup' && currentProject.accepted_bid && (
          <View style={styles.sectionCompact}>
            <Text style={styles.sectionTitle}>Selected Contractor & Bid</Text>
            <View style={styles.acceptedBidCard}>
              {/* Contractor Info */}
              <View style={styles.contractorSection}>
                <View style={styles.contractorHeader}>
                  {currentProject.accepted_bid.profile_pic ? (
                    <Image
                      source={{ uri: `http://192.168.254.113:8083/storage/${currentProject.accepted_bid.profile_pic}` }}
                      style={styles.contractorAvatar}
                    />
                  ) : (
                    <View style={styles.avatarPlaceholder}>
                      <Text style={styles.avatarText}>
                        {currentProject.accepted_bid.company_name?.charAt(0).toUpperCase() || 'C'}
                      </Text>
                    </View>
                  )}
                  <View style={styles.contractorDetails}>
                    <Text style={styles.contractorName}>{currentProject.accepted_bid.company_name}</Text>
                    <Text style={styles.contractorUsername}>@{currentProject.accepted_bid.username}</Text>
                    <View style={styles.picabBadge}>
                      <Text style={styles.picabText}>{currentProject.accepted_bid.picab_category}</Text>
                    </View>
                  </View>
                </View>

                {/* Contractor Stats */}
                <View style={styles.statsRow}>
                  <View style={styles.statItem}>
                    <Text style={styles.statValue}>{currentProject.accepted_bid.years_of_experience}</Text>
                    <Text style={styles.statLabel}>Yrs Exp</Text>
                  </View>
                  <View style={styles.statDivider} />
                  <View style={styles.statItem}>
                    <Text style={styles.statValue}>{currentProject.accepted_bid.completed_projects}</Text>
                    <Text style={styles.statLabel}>Projects</Text>
                  </View>
                </View>
              </View>

              {/* Bid Details */}
              <View style={styles.bidDetailsSection}>
                <View style={styles.bidDetailRow}>
                  <View>
                    <Text style={styles.bidDetailLabel}>Agreed Price</Text>
                    <Text style={styles.bidDetailValue}>{formatBudget(currentProject.accepted_bid.proposed_cost)}</Text>
                  </View>
                  <View style={{ alignItems: 'flex-end' }}>
                    <Text style={styles.bidDetailLabel}>Timeline</Text>
                    <Text style={styles.bidDetailValue}>{currentProject.accepted_bid.estimated_timeline} months</Text>
                  </View>
                </View>

                {currentProject.accepted_bid.contractor_notes && (
                  <View style={styles.notesSection}>
                    <Text style={styles.notesLabel}>Contractor Notes</Text>
                    <Text style={styles.notesText}>{currentProject.accepted_bid.contractor_notes}</Text>
                  </View>
                )}

                <View style={styles.contactSection}>
                  <Text style={styles.contactLabel}>Contact Information</Text>
                  <View style={styles.contactRow}>
                    <Feather name="mail" size={16} color={COLORS.primary} />
                    <Text style={styles.contactValue}>{currentProject.accepted_bid.company_email}</Text>
                  </View>
                  <View style={styles.contactRow}>
                    <Feather name="phone" size={16} color={COLORS.primary} />
                    <Text style={styles.contactValue}>{currentProject.accepted_bid.company_phone}</Text>
                  </View>
                  {currentProject.accepted_bid.company_website && (
                    <View style={styles.contactRow}>
                      <Feather name="globe" size={16} color={COLORS.primary} />
                      <Text style={styles.contactValue}>{currentProject.accepted_bid.company_website}</Text>
                    </View>
                  )}
                </View>
              </View>
            </View>
          </View>
        )}

        {/* Bids Section - Always show */}
        <View style={styles.sectionCompact}>
          <Text style={styles.sectionTitle}>Bids Received</Text>
          <TouchableOpacity
            style={styles.bidsCard}
            activeOpacity={0.7}
            onPress={() => setShowBids(true)}
          >
            <View style={styles.bidsInfo}>
              <View style={styles.bidsIconContainer}>
                <Feather name="users" size={24} color={COLORS.info} />
              </View>
              <View style={styles.bidsContent}>
                <Text style={styles.bidsCount}>
                  {currentProject.bids_count || 0} {(currentProject.bids_count || 0) === 1 ? 'Bid' : 'Bids'}
                </Text>
                <Text style={styles.bidsSubtext}>Tap to view all bids</Text>
              </View>
            </View>
            <Feather name="chevron-right" size={24} color={COLORS.textMuted} />
          </TouchableOpacity>
        </View>

        {/* Action Buttons */}
        <View style={styles.actionSection}>
          {currentProject.project_post_status === 'under_review' && (
            <View style={styles.pendingNotice}>
              <Feather name="info" size={20} color={COLORS.warning} />
              <Text style={styles.pendingNoticeText}>
                Your project is currently under review. You will be notified once it's approved.
              </Text>
            </View>
          )}

          <TouchableOpacity
            style={styles.secondaryButton}
            activeOpacity={0.7}
            onPress={() => setShowEditProject(true)}
          >
            <Feather name="edit-2" size={18} color={COLORS.primary} />
            <Text style={styles.secondaryButtonText}>Edit Project</Text>
          </TouchableOpacity>
        </View>

        <View style={{ height: 40 }} />
      </ScrollView>
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
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
  },
  statusCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  statusBadgeLarge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    gap: 8,
  },
  statusTextLarge: {
    fontSize: 14,
    fontWeight: '600',
  },
  deadlineContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  deadlineText: {
    fontSize: 13,
    color: COLORS.textSecondary,
    fontWeight: '500',
  },
  deadlineUrgent: {
    color: COLORS.error,
  },
  titleSection: {
    marginBottom: 10,
  },
  typeTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 8,
    alignSelf: 'flex-start',
    marginBottom: 8,
  },
  typeText: {
    fontSize: 13,
    color: COLORS.primary,
    fontWeight: '600',
  },
  projectTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.text,
    lineHeight: 32,
  },
  section: {
    marginBottom: 16,
  },
  sectionCompact: {
    marginBottom: 10,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 6,
  },
  descriptionCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 10,
    padding: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.03,
    shadowRadius: 4,
    elevation: 1,
  },
  descriptionText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 22,
  },
  budgetCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 10,
    padding: 12,
    flexDirection: 'row',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.03,
    shadowRadius: 4,
    elevation: 1,
  },
  budgetItem: {
    flex: 1,
    alignItems: 'center',
  },
  budgetLabel: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginBottom: 4,
    fontWeight: '500',
  },
  budgetValue: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.primary,
  },
  budgetDivider: {
    width: 1,
    backgroundColor: COLORS.border,
    marginHorizontal: 16,
  },
  infoCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 10,
    padding: 6,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.03,
    shadowRadius: 4,
    elevation: 1,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 10,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  infoIconContainer: {
    width: 36,
    height: 36,
    borderRadius: 8,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  infoContent: {
    flex: 1,
  },
  infoLabel: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginBottom: 2,
  },
  infoValue: {
    fontSize: 15,
    color: COLORS.text,
    fontWeight: '500',
  },
  bidsCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.03,
    shadowRadius: 4,
    elevation: 1,
  },
  bidsInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  bidsIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 12,
    backgroundColor: COLORS.infoLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  bidsContent: {},
  bidsCount: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
  },
  bidsSubtext: {
    fontSize: 13,
    color: COLORS.textMuted,
    marginTop: 2,
  },
  actionSection: {
    marginTop: 6,
    gap: 10,
  },
  primaryButton: {
    borderRadius: 12,
    overflow: 'hidden',
  },
  primaryButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 16,
    gap: 10,
  },
  primaryButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  secondaryButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    borderRadius: 12,
    borderWidth: 1.5,
    borderColor: COLORS.primary,
    gap: 8,
  },
  secondaryButtonText: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.primary,
  },
  pendingNotice: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: COLORS.warningLight,
    borderRadius: 12,
    padding: 16,
    gap: 12,
  },
  pendingNoticeText: {
    flex: 1,
    fontSize: 14,
    color: COLORS.warning,
    lineHeight: 20,
  },
  acceptedBidCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 3,
  },
  contractorSection: {
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  contractorHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  contractorAvatar: {
    width: 56,
    height: 56,
    borderRadius: 28,
    marginRight: 12,
  },
  avatarPlaceholder: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  avatarText: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.primary,
  },
  contractorDetails: {
    flex: 1,
  },
  contractorName: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 2,
  },
  contractorUsername: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 6,
  },
  picabBadge: {
    alignSelf: 'flex-start',
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 8,
  },
  picabText: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.primary,
  },
  statsRow: {
    flexDirection: 'row',
    backgroundColor: COLORS.background,
    borderRadius: 10,
    paddingVertical: 12,
    justifyContent: 'space-around',
  },
  statItem: {
    alignItems: 'center',
  },
  statValue: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 2,
  },
  statLabel: {
    fontSize: 11,
    color: COLORS.textSecondary,
  },
  statDivider: {
    width: 1,
    height: '100%',
    backgroundColor: COLORS.border,
  },
  bidDetailsSection: {
    padding: 16,
  },
  bidDetailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  bidDetailLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 4,
  },
  bidDetailValue: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.primary,
  },
  notesSection: {
    marginBottom: 12,
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  notesLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginBottom: 6,
  },
  notesText: {
    fontSize: 13,
    color: COLORS.text,
    lineHeight: 18,
  },
  contactSection: {
    marginTop: 4,
  },
  contactLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginBottom: 8,
  },
  contactRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 6,
  },
  contactValue: {
    fontSize: 13,
    color: COLORS.text,
    marginLeft: 8,
    flex: 1,
  },
});
