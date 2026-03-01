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
import ProjectView from '../both/projectView';
import MilestoneApproval from '../both/milestoneApproval';
import MilestoneSetup from './milestoneSetup';
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
  payment_plan?: {
    plan_id: number;
    payment_mode: string;
    total_project_cost: number;
    downpayment_amount: number;
  };
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
  estimated_timeline: number | string;
  contractor_notes?: string;
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
  type_name: string;
  type_id?: number;
  project_status: string;
  project_post_status: string;
  selected_contractor_id?: number;
  bidding_deadline?: string;
  bidding_due?: string;
  created_at: string;
  display_status?: string;
  milestones?: Milestone[];
  milestones_count?: number;
  accepted_bid?: AcceptedBid;
  owner_info?: OwnerInfo;
  owner_name?: string;
  owner_profile_pic?: string;
}

interface ContractorProjectDetailsProps {
  project: Project;
  userId?: number;
  onClose: () => void;
  onProjectUpdated?: (updatedProject: Project) => void;
  initialView?: 'milestones' | null;
  initialItemId?: number | null;
  initialItemTab?: 'payments' | null;
}

export default function ContractorProjectDetails({ project, userId, onClose, onProjectUpdated, initialView, initialItemId, initialItemTab }: ContractorProjectDetailsProps) {
  const insets = useSafeAreaInsets();
  const [currentProject, setCurrentProject] = useState(project);
  const [expandedSummary, setExpandedSummary] = useState(false);
  const [showMilestones, setShowMilestones] = useState(false);
  const [showMilestoneApproval, setShowMilestoneApproval] = useState(initialView === 'milestones');
  const [showMilestoneSetup, setShowMilestoneSetup] = useState(false);

  const milestones: Milestone[] = currentProject.milestones || [];

  // ── Formatters ────────────────────────────────────────────────────

  const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 0 }).format(amount || 0);

  const formatDate = (ds: string) => {
    if (!ds) return '';
    return new Date(ds).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  };

  // ── Refresh ───────────────────────────────────────────────────────

  const refreshProjectData = async () => {
    if (!userId) return;
    try {
      const response = await projects_service.get_contractor_projects(userId);
      if (response.success) {
        const list = response.data?.data || response.data || [];
        const updated = list.find((p: Project) => p.project_id === currentProject.project_id);
        if (updated) {
          setCurrentProject(updated);
          if (onProjectUpdated) onProjectUpdated(updated);
        }
      }
    } catch (_) {}
  };

  // ── Status config ─────────────────────────────────────────────────

  const getProjectStatusConfig = () => {
    const ds = (currentProject.display_status || currentProject.project_status || '').toLowerCase();
    if (ds === 'waiting_milestone_setup')
      return { color: COLORS.warning, bg: COLORS.warningLight, label: 'Project Setup Required', icon: 'settings' };
    if (ds === 'waiting_for_approval')
      return { color: COLORS.info, bg: COLORS.infoLight, label: 'Awaiting Owner Approval', icon: 'clock' };
    if (ds === 'in_progress')
      return { color: COLORS.info, bg: COLORS.infoLight, label: 'In Progress', icon: 'trending-up' };
    if (ds === 'completed')
      return { color: COLORS.success, bg: COLORS.successLight, label: 'Completed', icon: 'check-circle' };
    if (ds === 'on_hold')
      return { color: COLORS.warning, bg: COLORS.warningLight, label: 'On Hold', icon: 'pause-circle' };
    return { color: COLORS.textMuted, bg: COLORS.borderLight, label: ds || 'Unknown', icon: 'circle' };
  };

  // ── Status banner ─────────────────────────────────────────────────

  const renderStatusBanner = () => {
    const ds = (currentProject.display_status || '').toLowerCase();
    const rejected = milestones.filter(m => m.setup_status === 'rejected');
    const pending  = milestones.filter(m => m.setup_status === 'submitted');

    if (ds === 'waiting_milestone_setup') {
      return (
        <View style={styles.banner}>
          <View style={[styles.bannerInner, { borderLeftColor: COLORS.warning, backgroundColor: COLORS.warningLight }]}>
            <Feather name="settings" size={18} color={COLORS.warning} />
            <View style={styles.bannerText}>
              <Text style={[styles.bannerTitle, { color: COLORS.warning }]}>Project Setup Required</Text>
              <Text style={styles.bannerMsg}>Complete the project setup by creating a milestone plan and payment breakdown.</Text>
            </View>
          </View>
        </View>
      );
    }

    if (rejected.length > 0) {
      return (
        <View style={styles.banner}>
          <View style={[styles.bannerInner, { borderLeftColor: COLORS.error, backgroundColor: COLORS.errorLight }]}>
            <Feather name="alert-circle" size={18} color={COLORS.error} />
            <View style={styles.bannerText}>
              <Text style={[styles.bannerTitle, { color: COLORS.error }]}>Changes Requested</Text>
              <Text style={styles.bannerMsg}>The owner requested changes to your project setup. Please review and resubmit.</Text>
            </View>
          </View>
        </View>
      );
    }

    if (pending.length > 0) {
      return (
        <View style={styles.banner}>
          <View style={[styles.bannerInner, { borderLeftColor: COLORS.info, backgroundColor: COLORS.infoLight }]}>
            <Feather name="clock" size={18} color={COLORS.info} />
            <View style={styles.bannerText}>
              <Text style={[styles.bannerTitle, { color: COLORS.info }]}>Awaiting Approval</Text>
              <Text style={styles.bannerMsg}>Your project setup is pending approval from the property owner.</Text>
            </View>
          </View>
        </View>
      );
    }

    return null;
  };

  // ── Milestone card config ─────────────────────────────────────────

  const getMilestoneCardConfig = () => {
    const hasApproved  = milestones.some(m => m.setup_status === 'approved');
    const hasPending   = milestones.some(m => m.setup_status === 'submitted');
    const hasRejected  = milestones.some(m => m.setup_status === 'rejected');
    const hasNone      = milestones.length === 0;

    if (hasApproved) return { title: 'Project Progress', desc: 'Track milestone completion, upload progress reports, and manage payments.', label: 'View Progress', icon: 'trending-up', color: COLORS.success };
    if (hasRejected) return { title: 'Project Setup Rejected', desc: 'The owner requested changes. Review the feedback and resubmit your project setup.', label: 'Review & Modify', icon: 'alert-triangle', color: COLORS.error };
    if (hasPending)  return { title: 'Project Setup Under Review', desc: 'Your project setup has been submitted and is awaiting owner approval.', label: 'View Proposal', icon: 'clock', color: COLORS.warning };
    if (hasNone)     return { title: 'Project Setup', desc: 'Create a milestone plan and payment breakdown to get the project started.', label: 'Start Project Setup', icon: 'settings', color: COLORS.primary };
    return { title: 'Project Setup', desc: 'Manage your project setup.', label: 'View Details', icon: 'settings', color: COLORS.primary };
  };

  const statusConfig = getProjectStatusConfig();
  const milestoneConfig = getMilestoneCardConfig();

  // ── Owner info helpers ────────────────────────────────────────────

  const ownerName = currentProject.owner_info
    ? `${currentProject.owner_info.first_name} ${currentProject.owner_info.last_name}`
    : currentProject.owner_name || 'Property Owner';

  const ownerProfilePic = currentProject.owner_info?.profile_pic || currentProject.owner_profile_pic;

  // ── Sub-screens ───────────────────────────────────────────────────

  if (showMilestones) {
    return (
      <ProjectView
        project={currentProject as any}
        userId={userId}
        userRole="contractor"
        onClose={() => { setShowMilestones(false); refreshProjectData(); }}
      />
    );
  }

  if (showMilestoneSetup) {
    return (
      <MilestoneSetup
        project={currentProject as any}
        userId={userId}
        onClose={() => { setShowMilestoneSetup(false); }}
        onSave={async () => { setShowMilestoneSetup(false); await refreshProjectData(); }}
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
        <View style={styles.headerBtn} />
      </View>

      <ScrollView style={styles.scroll} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>

        {/* ── Collapsible gradient summary card ── */}
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
                    { label: 'Category', value: currentProject.type_name },
                    { label: 'Lot Size', value: currentProject.lot_size ? `${currentProject.lot_size} sqm` : null },
                    { label: 'Floor Area', value: currentProject.floor_area ? `${currentProject.floor_area} sqm` : null },
                  ].filter(s => s.value).map((s, i) => (
                    <View key={i} style={styles.specCell}>
                      <Text style={styles.specLbl}>{s.label}</Text>
                      <Text style={styles.specVal}>{s.value}</Text>
                    </View>
                  ))}
                </View>

                <Text style={[styles.expLabel, { marginTop: 10 }]}>Posted On</Text>
                <Text style={styles.expText}>{formatDate(currentProject.created_at)}</Text>

                {/* Property Owner & Agreement */}
                <>
                  <View style={styles.gradDivider} />
                  <Text style={styles.expLabel}>Property Owner &amp; Agreement</Text>
                  <View style={styles.ctRow}>
                    {ownerProfilePic ? (
                      <Image
                        source={{ uri: `${api_config.base_url}/api/files/${ownerProfilePic}` }}
                        style={styles.ctAvatar}
                      />
                    ) : (
                      <View style={styles.ctAvatarPh}>
                        <Feather name="user" size={16} color="rgba(255,255,255,0.7)" />
                      </View>
                    )}
                    <View style={{ flex: 1 }}>
                      <Text style={styles.ctName}>{ownerName}</Text>
                      {currentProject.owner_info?.username && (
                        <Text style={styles.ctUser}>@{currentProject.owner_info.username}</Text>
                      )}
                    </View>
                  </View>
                  {currentProject.accepted_bid && (
                    <View style={styles.agreedRow}>
                      <View style={{ flex: 1 }}>
                        <Text style={styles.agreedLbl}>Your Proposed Cost</Text>
                        <Text style={styles.agreedVal}>{formatCurrency(currentProject.accepted_bid.proposed_cost)}</Text>
                      </View>
                      <View style={{ flex: 1 }}>
                        <Text style={styles.agreedLbl}>Timeline</Text>
                        <Text style={styles.agreedVal}>{currentProject.accepted_bid.estimated_timeline}</Text>
                      </View>
                    </View>
                  )}
                </>
              </View>
            )}
          </LinearGradient>
        </TouchableOpacity>

        {/* ── Action / status banner ── */}
        {renderStatusBanner()}

        {/* ── Milestone action card ── */}
        <TouchableOpacity style={styles.actionCard} onPress={() => { const hasNone = milestones.length === 0; hasNone ? setShowMilestoneSetup(true) : setShowMilestoneApproval(true); }} activeOpacity={0.8}>
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

        <View style={{ height: 32 }} />
      </ScrollView>

      {/* Milestone Approval - full-screen Modal */}
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
              contractorName: ownerName,
              propertyType: currentProject.type_name || currentProject.property_type,
              projectStartDate: (milestones[0])?.start_date || currentProject.created_at,
              projectEndDate: (milestones[milestones.length - 1])?.end_date || currentProject.created_at,
              totalCost: (milestones[0])?.payment_plan?.total_project_cost || currentProject.accepted_bid?.proposed_cost || 0,
              paymentMethod: (milestones[0])?.payment_plan?.payment_mode || 'milestone',
              milestones: milestones,
              userId: userId || 0,
              userRole: 'contractor',
              projectStatus: currentProject.project_status,
              initialItemId: initialItemId ?? undefined,
              initialItemTab: initialItemTab ?? undefined,
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
  headerBtn: { padding: 4, width: 30 },
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
});
