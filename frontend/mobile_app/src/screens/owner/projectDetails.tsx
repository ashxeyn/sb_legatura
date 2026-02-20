// @ts-nocheck
import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Image,
  StatusBar,
  Modal,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import EditProject from './editProject';
import ProjectBids from './projectBids';
import ProjectView from '../both/projectView';
import MilestoneApproval from '../both/milestoneApproval';
import { api_config } from '../../config/api';
import { projects_service } from '../../services/projects_service';

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
  background: '#F1F5F9',
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
  milestone_status: string;
  setup_status: string;
  setup_rej_reason?: string;
  start_date?: string;
  end_date?: string;
}

interface AcceptedBid {
  bid_id: number;
  proposed_cost: number;
  estimated_timeline: number | string;
  contractor_notes?: string;
  submitted_at?: string;
  company_name?: string;
  company_phone?: string;
  company_email?: string;
  company_website?: string;
  years_of_experience?: number;
  completed_projects?: number;
  picab_category?: string;
  username?: string;
  profile_pic?: string;
}

interface ContractorInfo {
  company_name: string;
  username: string;
  profile_pic?: string;
  years_of_experience?: number;
  completed_projects?: number;
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
  type_name: string;
  type_id?: number;
  project_status: string;
  project_post_status: string;
  selected_contractor_id?: number;
  bidding_deadline?: string;
  bidding_due?: string;
  created_at: string;
  bids_count?: number;
  display_status?: string;
  milestones?: Milestone[];
  milestones_count?: number;
  accepted_bid?: AcceptedBid;
  contractor_info?: ContractorInfo;
}

interface ProjectDetailsProps {
  project: Project;
  userId?: number;
  onClose: () => void;
  onProjectUpdated?: (updatedProject: Project) => void;
}

export default function ProjectDetails({ project, userId, onClose, onProjectUpdated }: ProjectDetailsProps) {
  const insets = useSafeAreaInsets();
  const [currentProject, setCurrentProject] = useState(project);
  const [expandedSummary, setExpandedSummary] = useState(false);
  const [showEditProject, setShowEditProject] = useState(false);
  const [showBids, setShowBids] = useState(false);
  const [showMilestones, setShowMilestones] = useState(false);
  const [showMilestoneApproval, setShowMilestoneApproval] = useState(false);

  const hasContractor = !!currentProject.selected_contractor_id;
  const milestones: Milestone[] = currentProject.milestones || [];

  // â”€â”€ Formatters â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 0 }).format(amount || 0);

  const formatDate = (ds: string) => {
    if (!ds) return '';
    return new Date(ds).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  };

  const getDaysRemaining = (deadline: string) =>
    Math.ceil((new Date(deadline).getTime() - Date.now()) / 86400000);

  // â”€â”€ Refresh â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  const refreshProjectData = async () => {
    if (!userId) return;
    try {
      const response = await projects_service.get_owner_projects(userId);
      if (response.success) {
        const list = response.data?.data || response.data || [];
        const updated = list.find((p: Project) => p.project_id === currentProject.project_id);
        if (updated) setCurrentProject(updated);
      }
    } catch (_) {}
  };

  // â”€â”€ Status config â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  const getProjectStatusConfig = () => {
    const post = currentProject.project_post_status;
    const ds   = (currentProject.display_status || currentProject.project_status || '').toLowerCase();
    if (post === 'under_review') return { color: COLORS.warning, bg: COLORS.warningLight, label: 'Under Review',                icon: 'clock' };
    if (post === 'rejected')     return { color: COLORS.error,   bg: COLORS.errorLight,   label: 'Rejected',                   icon: 'x-circle' };
    if (ds === 'open')           return { color: COLORS.success, bg: COLORS.successLight, label: 'Open for Bidding',           icon: 'check-circle' };
    if (ds === 'bidding_closed') return { color: COLORS.info,    bg: COLORS.infoLight,    label: 'Bidding Closed',             icon: 'lock' };
    if (ds === 'waiting_milestone_setup' || ds === 'waiting_for_approval')
                                 return { color: COLORS.warning, bg: COLORS.warningLight, label: 'Waiting for Milestone Setup', icon: 'alert-circle' };
    if (ds === 'in_progress')    return { color: COLORS.info,    bg: COLORS.infoLight,    label: 'In Progress',                icon: 'trending-up' };
    if (ds === 'completed')      return { color: COLORS.success, bg: COLORS.successLight, label: 'Completed',                  icon: 'check-circle' };
    if (ds === 'on_hold')        return { color: COLORS.warning, bg: COLORS.warningLight, label: 'On Hold',                   icon: 'pause-circle' };
    return { color: COLORS.textMuted, bg: COLORS.borderLight, label: ds, icon: 'circle' };
  };

  // â”€â”€ Action banner (milestone status) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  const renderStatusBanner = () => {
    if (!hasContractor) return null;
    const ds       = (currentProject.display_status || '').toLowerCase();
    const pending  = milestones.filter(m => m.setup_status === 'submitted');

    if (ds === 'waiting_milestone_setup') {
      return (
        <View style={styles.banner}>
          <View style={[styles.bannerInner, { borderLeftColor: COLORS.warning, backgroundColor: COLORS.warningLight }]}>
            <Feather name="clock" size={18} color={COLORS.warning} />
            <View style={styles.bannerText}>
              <Text style={[styles.bannerTitle, { color: COLORS.warning }]}>Waiting for Setup</Text>
              <Text style={styles.bannerMsg}>The contractor is preparing the milestone proposal for this project.</Text>
            </View>
          </View>
        </View>
      );
    }

    if (pending.length > 0) {
      return (
        <View style={styles.banner}>
          <View style={[styles.bannerInner, { borderLeftColor: COLORS.info, backgroundColor: COLORS.infoLight }]}>
            <Feather name="alert-circle" size={18} color={COLORS.info} />
            <View style={styles.bannerText}>
              <Text style={[styles.bannerTitle, { color: COLORS.info }]}>Action Required</Text>
              <Text style={styles.bannerMsg}>
                {pending.length} milestone{pending.length > 1 ? 's' : ''} waiting for your approval. Tap the card below to review.
              </Text>
            </View>
          </View>
        </View>
      );
    }

    return null;
  };

  // â”€â”€ Milestone card config â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  const getMilestoneCardConfig = () => {
    const hasApproved = milestones.some(m => m.setup_status === 'approved');
    const hasPending  = milestones.some(m => m.setup_status === 'submitted');
    if (hasApproved) return { title: 'Check Project Progress',  desc: 'Track milestone completion, review progress reports, and monitor payment history.',  label: 'View Progress',    icon: 'trending-up', color: COLORS.success };
    if (hasPending)  return { title: 'Review Milestone Setup',  desc: 'The contractor has submitted a milestone proposal. Review and approve the breakdown.', label: 'Review & Approve', icon: 'clock',       color: COLORS.warning };
    return             { title: 'Milestone Setup',              desc: 'The milestone timeline and payment breakdown are being prepared by the contractor.',    label: 'View Details',     icon: 'clipboard',   color: COLORS.info };
  };

  const statusConfig    = getProjectStatusConfig();
  const milestoneConfig = getMilestoneCardConfig();
  const deadline = currentProject.bidding_deadline || currentProject.bidding_due;
  const daysLeft = deadline ? getDaysRemaining(deadline) : null;

  // â”€â”€ Sub-screens â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

  if (showBids) {
    return (
      <ProjectBids
        project={currentProject}
        userId={userId || 0}
        onClose={() => setShowBids(false)}
        onBidAccepted={() => { setShowBids(false); refreshProjectData(); }}
      />
    );
  }

  if (showMilestones) {
    return (
      <ProjectView
        project={currentProject as any}
        userId={userId}
        userRole="owner"
        onClose={() => { setShowMilestones(false); refreshProjectData(); }}
      />
    );
  }

  return (
    <View style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.surface} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.headerBtn} activeOpacity={0.7}>
          <Feather name="arrow-left" size={22} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Project Details</Text>
        <TouchableOpacity style={styles.headerBtn} activeOpacity={0.7} onPress={() => setShowEditProject(true)}>
          <Feather name="edit-2" size={18} color={COLORS.text} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scroll} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

        {/* â”€â”€ Collapsible gradient summary card â”€â”€ */}
        <TouchableOpacity style={styles.summaryCard} onPress={() => setExpandedSummary(!expandedSummary)} activeOpacity={0.92}>
          <LinearGradient
            colors={[COLORS.primary, COLORS.primaryDark]}
            start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}
            style={styles.summaryGradient}
          >
            {/* Title row */}
            <View style={styles.summaryTop}>
              <View style={{ flex: 1, marginRight: 12 }}>
                <View style={styles.typePill}>
                  <Text style={styles.typePillText}>{currentProject.type_name}</Text>
                </View>
                <Text style={styles.summaryTitle}>{currentProject.project_title}</Text>
                <View style={styles.locRow}>
                  <Feather name="map-pin" size={12} color="rgba(255,255,255,0.8)" />
                  <Text style={styles.locText}>{currentProject.project_location}</Text>
                </View>
              </View>
              <Feather name={expandedSummary ? 'chevron-up' : 'chevron-down'} size={22} color="rgba(255,255,255,0.9)" />
            </View>

            {/* Status pills */}
            <View style={styles.pillRow}>
              <View style={styles.pill}>
                <Feather name={statusConfig.icon as any} size={11} color="#FFFFFF" />
                <Text style={styles.pillText}>{statusConfig.label}</Text>
              </View>
              {daysLeft !== null && daysLeft > 0 && (
                <View style={styles.pill}>
                  <Feather name="clock" size={11} color="#FFFFFF" />
                  <Text style={styles.pillText}>{daysLeft}d left</Text>
                </View>
              )}
            </View>

            {/* Expanded details */}
            {expandedSummary && (
              <View style={{ marginTop: 4 }}>
                <View style={styles.gradDivider} />

                <Text style={styles.expLabel}>Description</Text>
                <Text style={styles.expText}>{currentProject.project_description}</Text>

                <Text style={[styles.expLabel, { marginTop: 14 }]}>Budget Range</Text>
                <View style={styles.budgetRow}>
                  <View style={{ flex: 1 }}>
                    <Text style={styles.budgetLbl}>Min</Text>
                    <Text style={styles.budgetVal}>{formatCurrency(currentProject.budget_range_min)}</Text>
                  </View>
                  <View style={styles.budgetSep} />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.budgetLbl}>Max</Text>
                    <Text style={styles.budgetVal}>{formatCurrency(currentProject.budget_range_max)}</Text>
                  </View>
                </View>

                <Text style={[styles.expLabel, { marginTop: 14 }]}>Specifications</Text>
                <View style={styles.specGrid}>
                  {[
                    { label: 'Property Type', value: currentProject.property_type },
                    { label: 'Category',      value: currentProject.type_name },
                    { label: 'Lot Size',      value: currentProject.lot_size ? `${currentProject.lot_size} sqm` : null },
                    { label: 'Floor Area',    value: currentProject.floor_area ? `${currentProject.floor_area} sqm` : null },
                  ].filter(s => s.value).map((s, i) => (
                    <View key={i} style={styles.specCell}>
                      <Text style={styles.specLbl}>{s.label}</Text>
                      <Text style={styles.specVal}>{s.value}</Text>
                    </View>
                  ))}
                </View>

                {deadline && (
                  <>
                    <Text style={[styles.expLabel, { marginTop: 14 }]}>Bidding Deadline</Text>
                    <Text style={styles.expText}>{formatDate(deadline)}</Text>
                  </>
                )}
                <Text style={[styles.expLabel, { marginTop: 10 }]}>Posted On</Text>
                <Text style={styles.expText}>{formatDate(currentProject.created_at)}</Text>

                {/* Contractor & agreement */}
                {hasContractor && currentProject.contractor_info && (
                  <>
                    <View style={styles.gradDivider} />
                    <Text style={styles.expLabel}>Contractor &amp; Agreement</Text>
                    <View style={styles.ctRow}>
                      {currentProject.contractor_info.profile_pic ? (
                        <Image
                          source={{ uri: `${api_config.base_url}/api/files/${currentProject.contractor_info.profile_pic}` }}
                          style={styles.ctAvatar}
                        />
                      ) : (
                        <View style={styles.ctAvatarPh}>
                          <Feather name="user" size={16} color="rgba(255,255,255,0.7)" />
                        </View>
                      )}
                      <View style={{ flex: 1 }}>
                        <Text style={styles.ctName}>{currentProject.contractor_info.company_name}</Text>
                        <Text style={styles.ctUser}>@{currentProject.contractor_info.username}</Text>
                      </View>
                      {currentProject.contractor_info.years_of_experience != null && (
                        <View style={styles.expBadge}>
                          <Text style={styles.expBadgeText}>{currentProject.contractor_info.years_of_experience} yrs exp</Text>
                        </View>
                      )}
                    </View>
                    {currentProject.accepted_bid && (
                      <View style={styles.agreedRow}>
                        <View style={{ flex: 1 }}>
                          <Text style={styles.agreedLbl}>Agreed Cost</Text>
                          <Text style={styles.agreedVal}>{formatCurrency(currentProject.accepted_bid.proposed_cost)}</Text>
                        </View>
                        <View style={{ flex: 1 }}>
                          <Text style={styles.agreedLbl}>Timeline</Text>
                          <Text style={styles.agreedVal}>{currentProject.accepted_bid.estimated_timeline} months</Text>
                        </View>
                      </View>
                    )}
                  </>
                )}
              </View>
            )}
          </LinearGradient>
        </TouchableOpacity>

        {/* â”€â”€ Action / status banner â”€â”€ */}
        {renderStatusBanner()}

        {/* â”€â”€ Under review notice â”€â”€ */}
        {currentProject.project_post_status === 'under_review' && (
          <View style={styles.banner}>
            <View style={[styles.bannerInner, { borderLeftColor: COLORS.warning, backgroundColor: COLORS.warningLight }]}>
              <Feather name="info" size={18} color={COLORS.warning} />
              <View style={styles.bannerText}>
                <Text style={[styles.bannerTitle, { color: COLORS.warning }]}>Under Review</Text>
                <Text style={styles.bannerMsg}>Your project is currently under review. You will be notified once it's approved.</Text>
              </View>
            </View>
          </View>
        )}

        {/* â”€â”€ Milestone action card â”€â”€ */}
        {hasContractor && (
          <TouchableOpacity style={styles.actionCard} onPress={() => setShowMilestoneApproval(true)} activeOpacity={0.8}>
            <View style={[styles.actionIconWrap, { backgroundColor: milestoneConfig.color + '1A' }]}>
              <Feather name={milestoneConfig.icon as any} size={22} color={milestoneConfig.color} />
            </View>
            <View style={styles.actionCardBody}>
              <Text style={styles.actionCardTitle}>{milestoneConfig.title}</Text>
              <Text style={styles.actionCardDesc}>{milestoneConfig.desc}</Text>
              <View style={[styles.actionChip, { backgroundColor: milestoneConfig.color + '1A' }]}>
                <Text style={[styles.actionChipText, { color: milestoneConfig.color }]}>{milestoneConfig.label}</Text>
                <Feather name="arrow-right" size={12} color={milestoneConfig.color} />
              </View>
            </View>
            <Feather name="chevron-right" size={20} color={COLORS.textMuted} />
          </TouchableOpacity>
        )}

        {/* â”€â”€ Bids received â”€â”€ */}
        <TouchableOpacity style={styles.rowCard} onPress={() => setShowBids(true)} activeOpacity={0.8}>
          <View style={[styles.rowIconWrap, { backgroundColor: COLORS.infoLight }]}>
            <Feather name="users" size={20} color={COLORS.info} />
          </View>
          <View style={styles.rowBody}>
            <Text style={styles.rowTitle}>Bids Received</Text>
            <Text style={styles.rowSub}>{currentProject.bids_count || 0} {(currentProject.bids_count || 0) === 1 ? 'bid' : 'bids'} submitted</Text>
          </View>
          <Feather name="chevron-right" size={20} color={COLORS.textMuted} />
        </TouchableOpacity>

        <View style={{ height: 32 }} />
      </ScrollView>

      {/* Edit Project - full-screen Modal so safe area insets work correctly */}
      <Modal
        visible={showEditProject}
        animationType="slide"
        presentationStyle="fullScreen"
        onRequestClose={() => setShowEditProject(false)}
      >
        <EditProject
          project={currentProject}
          userId={userId || 0}
          onClose={() => setShowEditProject(false)}
          onSave={(updated: Project) => {
            setCurrentProject(updated);
            setShowEditProject(false);
            if (onProjectUpdated) onProjectUpdated(updated);
          }}
        />
      </Modal>

      {/* Milestone Approval - full-screen Modal so safe area insets work correctly */}
      <Modal
        visible={showMilestoneApproval}
        animationType="slide"
        presentationStyle="fullScreen"
        onRequestClose={() => { setShowMilestoneApproval(false); refreshProjectData(); }}
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
              projectEndDate: (currentProject.milestones && currentProject.milestones[(currentProject.milestones?.length ?? 1) - 1])?.end_date || currentProject.created_at,
              totalCost: (currentProject.milestones && currentProject.milestones[0])?.payment_plan?.total_project_cost || currentProject.accepted_bid?.proposed_cost || 0,
              paymentMethod: (currentProject.milestones && currentProject.milestones[0])?.payment_plan?.payment_mode || 'milestone',
              milestones: currentProject.milestones || [],
              userId: userId || 0,
              userRole: 'owner',
              projectStatus: currentProject.project_status,
              onApprovalComplete: async () => {
                await refreshProjectData();
                setShowMilestoneApproval(false);
              },
            },
          }}
          navigation={{ goBack: () => { setShowMilestoneApproval(false); refreshProjectData(); } }}
        />
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: COLORS.background },

  // Header
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: COLORS.surface,
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  headerBtn: { padding: 4 },
  headerTitle: { fontSize: 17, fontWeight: '700', color: COLORS.text, letterSpacing: 0.3 },

  scroll: { flex: 1 },
  scrollContent: { paddingBottom: 20 },

  // Summary card
  summaryCard: { marginBottom: 0 },
  summaryGradient: { paddingHorizontal: 20, paddingTop: 18, paddingBottom: 16 },
  summaryTop: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 12 },
  typePill: {
    alignSelf: 'flex-start',
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderRadius: 3,
    paddingHorizontal: 8,
    paddingVertical: 3,
    marginBottom: 8,
  },
  typePillText: { fontSize: 11, fontWeight: '600', color: '#FFFFFF', letterSpacing: 0.5 },
  summaryTitle: { fontSize: 20, fontWeight: '700', color: '#FFFFFF', lineHeight: 26, marginBottom: 6 },
  locRow: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  locText: { fontSize: 13, color: 'rgba(255,255,255,0.85)', flex: 1 },
  pillRow: { flexDirection: 'row', gap: 8, flexWrap: 'wrap' },
  pill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    backgroundColor: 'rgba(255,255,255,0.18)',
    borderRadius: 3,
    paddingHorizontal: 9,
    paddingVertical: 5,
  },
  pillText: { fontSize: 12, fontWeight: '600', color: '#FFFFFF' },

  // Expanded
  gradDivider: { height: 1, backgroundColor: 'rgba(255,255,255,0.2)', marginVertical: 14 },
  expLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: 'rgba(255,255,255,0.6)',
    textTransform: 'uppercase',
    letterSpacing: 0.9,
    marginBottom: 4,
  },
  expText: { fontSize: 14, color: 'rgba(255,255,255,0.9)', lineHeight: 20 },
  budgetRow: { flexDirection: 'row', gap: 12 },
  budgetLbl: { fontSize: 11, color: 'rgba(255,255,255,0.6)', marginBottom: 2 },
  budgetVal: { fontSize: 16, fontWeight: '700', color: '#FFFFFF' },
  budgetSep: { width: 1, backgroundColor: 'rgba(255,255,255,0.2)' },
  specGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  specCell: { width: '47%' },
  specLbl: { fontSize: 10, color: 'rgba(255,255,255,0.6)', marginBottom: 2 },
  specVal: { fontSize: 13, fontWeight: '600', color: '#FFFFFF' },
  ctRow: { flexDirection: 'row', alignItems: 'center', gap: 10, marginTop: 8, marginBottom: 10 },
  ctAvatar: { width: 38, height: 38, borderRadius: 3 },
  ctAvatarPh: {
    width: 38, height: 38, borderRadius: 3,
    backgroundColor: 'rgba(255,255,255,0.15)',
    alignItems: 'center', justifyContent: 'center',
  },
  ctName: { fontSize: 14, fontWeight: '600', color: '#FFFFFF' },
  ctUser: { fontSize: 12, color: 'rgba(255,255,255,0.7)' },
  expBadge: {
    backgroundColor: 'rgba(255,255,255,0.18)',
    borderRadius: 3,
    paddingHorizontal: 8,
    paddingVertical: 4,
  },
  expBadgeText: { fontSize: 11, fontWeight: '600', color: '#FFFFFF' },
  agreedRow: { flexDirection: 'row', gap: 12 },
  agreedLbl: { fontSize: 10, color: 'rgba(255,255,255,0.6)', marginBottom: 2 },
  agreedVal: { fontSize: 15, fontWeight: '700', color: '#FFFFFF' },

  // Banners
  banner: { marginHorizontal: 16, marginTop: 12 },
  bannerInner: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
    borderLeftWidth: 3,
    borderRadius: 4,
    padding: 12,
  },
  bannerText: { flex: 1 },
  bannerTitle: { fontSize: 13, fontWeight: '700', marginBottom: 2 },
  bannerMsg: { fontSize: 12, color: COLORS.textSecondary, lineHeight: 18 },

  // Action card (milestone)
  actionCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    marginHorizontal: 16,
    marginTop: 12,
    padding: 14,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.border,
    gap: 12,
  },
  actionIconWrap: { width: 46, height: 46, borderRadius: 6, alignItems: 'center', justifyContent: 'center' },
  actionCardBody: { flex: 1 },
  actionCardTitle: { fontSize: 14, fontWeight: '700', color: COLORS.text, marginBottom: 3 },
  actionCardDesc: { fontSize: 12, color: COLORS.textSecondary, lineHeight: 17, marginBottom: 8 },
  actionChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    alignSelf: 'flex-start',
    borderRadius: 3,
    paddingHorizontal: 8,
    paddingVertical: 4,
  },
  actionChipText: { fontSize: 12, fontWeight: '600' },

  // Row card (bids)
  rowCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    marginHorizontal: 16,
    marginTop: 10,
    padding: 14,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.border,
    gap: 12,
  },
  rowIconWrap: { width: 40, height: 40, borderRadius: 6, alignItems: 'center', justifyContent: 'center' },
  rowBody: { flex: 1 },
  rowTitle: { fontSize: 14, fontWeight: '700', color: COLORS.text, marginBottom: 2 },
  rowSub: { fontSize: 12, color: COLORS.textSecondary },
});
