// @ts-nocheck
import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  StatusBar,
  ActivityIndicator,
  Alert,
  Modal,
  Image,
  TextInput,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { milestones_service } from '../../services/milestones_service';
import { payment_service } from '../../services/payment_service';
import { projects_service } from '../../services/projects_service';
import MilestoneDetail from './milestoneDetail';
import DisputeHistory from './disputeHistory';
import MilestoneSetup from '../contractor/milestoneSetup';
import DownpaymentDetail from './downpaymentDetail';
import ProjectUpdateModal from './projectUpdateModal';
import ProjectSummary from './projectSummary';
import MilestoneSummary from './milestoneSummary';
import { update_service, ExtensionRecord } from '../../services/update_service';
import { api_config } from '../../config/api';

// Color palette
const COLORS = {
  primary: '#1E3A5F',
  primaryLight: '#E8EEF4',
  accent: '#EC7E00',
  accentLight: '#FFF3E6',
  success: '#10B981',
  successLight: '#D1FAE5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
  errorLight: '#FEE2E2',
  info: '#3B82F6',
  infoLight: '#DBEAFE',
  background: '#FFFFFF',
  surface: '#FFFFFF',
  text: '#1E3A5F',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
  darkBlue: '#0A1628',
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
  original_date_to_finish?: string | null;
  was_extended?: boolean;
  extension_count?: number;
  item_status?: string;
  // Status summary fields from backend
  latest_progress_status?: string | null;
  latest_progress_date?: string | null;
  progress_submitted_count?: number;
  progress_rejected_count?: number;
  latest_payment_status?: string | null;
  latest_payment_date?: string | null;
  payment_submitted_count?: number;
  payment_rejected_count?: number;
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

interface MilestoneApprovalProps {
  route: {
    params: {
      projectId: number;
      projectTitle: string;
      projectDescription?: string;
      projectLocation?: string;
      contractorName: string;
      propertyType: string;
      projectStartDate: string;
      projectEndDate: string;
      totalCost: number;
      paymentMethod: string;
      milestones: Milestone[];
      userId: number;
      userRole: 'owner' | 'contractor';
      projectStatus?: string;
      onApprovalComplete: () => void;
    };
  };
  navigation: any;
}

export default function MilestoneApproval({ route, navigation }: MilestoneApprovalProps) {
  const insets = useSafeAreaInsets();
  const {
    projectId,
    projectTitle,
    projectDescription,
    projectLocation,
    contractorName,
    propertyType,
    projectStartDate,
    projectEndDate,
    totalCost,
    paymentMethod,
    milestones,
    userId,
    userRole,
    projectStatus,
    onApprovalComplete,
  } = route.params;

  const [approvingMilestone, setApprovingMilestone] = useState<number | null>(null);
  const [rejectingMilestone, setRejectingMilestone] = useState<number | null>(null);
  const [selectedMilestoneDetail, setSelectedMilestoneDetail] = useState<{
    item: MilestoneItem & { parentMilestoneId: number; parentSetupStatus: string; parentMilestoneStatus: string };
    milestoneNumber: number;
    cumulativePercentage: number;
  } | null>(null);
  const [showPaymentHistory, setShowPaymentHistory] = useState(false);
  const [paymentHistory, setPaymentHistory] = useState<any[]>([]);
  const [loadingPayments, setLoadingPayments] = useState(false);
  const [selectedPayment, setSelectedPayment] = useState<any | null>(null);
  const [showFullScreenImage, setShowFullScreenImage] = useState(false);
  const [showMenu, setShowMenu] = useState(false);
  const [showPaymentHistoryMenu, setShowPaymentHistoryMenu] = useState(false);
  const [showDisputeHistory, setShowDisputeHistory] = useState(false);
  const [showRejectModal, setShowRejectModal] = useState(false);
  const [rejectReason, setRejectReason] = useState('');
  const [showMilestoneRejectModal, setShowMilestoneRejectModal] = useState(false);
  const [milestoneRejectReason, setMilestoneRejectReason] = useState('');
  const [pendingRejectMilestoneId, setPendingRejectMilestoneId] = useState<number | null>(null);
  const [showCompleteProjectModal, setShowCompleteProjectModal] = useState(false);
  const [completingProject, setCompletingProject] = useState(false);
  const [isProjectCompleted, setIsProjectCompleted] = useState(projectStatus === 'completed');
  const [showEditMilestone, setShowEditMilestone] = useState(false);
  const [milestoneToEdit, setMilestoneToEdit] = useState<Milestone | null>(null);
  const [showDownpaymentDetail, setShowDownpaymentDetail] = useState(false);
  const [showUpdateModal, setShowUpdateModal] = useState(false);
  const [pendingUpdate, setPendingUpdate] = useState<ExtensionRecord | null>(null);
  const [approvedUpdates, setApprovedUpdates] = useState<ExtensionRecord[]>([]);
  const [showProjectDateHistory, setShowProjectDateHistory] = useState(false);
  const [showProjectSummary, setShowProjectSummary] = useState(false);
  const [showMilestoneSummary, setShowMilestoneSummary] = useState<number | null>(null);

  // ── Fetch pending update for the owner banner ──
  const fetchPendingUpdate = useCallback(async () => {
    if (userRole !== 'owner') return;
    try {
      const res = await update_service.getContext(projectId);
      if (res.success && res.data?.pending_extension) {
        setPendingUpdate(res.data.pending_extension as unknown as ExtensionRecord);
      } else {
        setPendingUpdate(null);
      }
    } catch {
      setPendingUpdate(null);
    }
  }, [projectId, userRole]);

  useEffect(() => {
    fetchPendingUpdate();
  }, [fetchPendingUpdate]);

  // Fetch approved updates for project-level extension history
  useEffect(() => {
    update_service.list(projectId)
      .then(res => {
        if (res.success && res.data) {
          setApprovedUpdates(res.data.filter((u: ExtensionRecord) => u.status === 'approved'));
        }
      })
      .catch(() => {});
  }, [projectId]);

  const isProjectHalted = projectStatus === 'halt' || projectStatus === 'on_hold' || projectStatus === 'halted';

  // Flatten all milestone items from all milestones into one array for the timeline
  const allMilestoneItems: (MilestoneItem & { parentMilestoneId: number; parentSetupStatus: string; parentMilestoneStatus: string })[] = [];
  milestones.forEach(milestone => {
    if (milestone.items && milestone.items.length > 0) {
      milestone.items.forEach(item => {
        allMilestoneItems.push({
          ...item,
          parentMilestoneId: milestone.milestone_id,
          parentSetupStatus: milestone.setup_status,
          parentMilestoneStatus: milestone.milestone_status,
        });
      });
    }
  });

  // Get payment plan info for downpayment
  const firstMilestone = milestones[0];
  const paymentPlan = firstMilestone?.payment_plan;
  const isDownpaymentMode = paymentPlan?.payment_mode === 'downpayment';
  const downpaymentAmount = paymentPlan?.downpayment_amount || 0;
  const downpaymentPercentage = totalCost > 0 ? (downpaymentAmount / totalCost) * 100 : 0;

  // Sort by sequence order
  allMilestoneItems.sort((a, b) => a.sequence_order - b.sequence_order);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      minimumFractionDigits: 2,
    }).format(amount);
  };

  const handleMilestonePress = (item: MilestoneItem & { parentMilestoneId: number; parentSetupStatus: string; parentMilestoneStatus: string }, milestoneNumber: number, cumulativePercentage: number) => {
    // Show milestone detail view
    setSelectedMilestoneDetail({
      item,
      milestoneNumber,
      cumulativePercentage,
    });
  };

  const handleApproveMilestone = (milestoneId: number) => {
    Alert.alert(
      'Approve Milestone Setup',
      'Are you sure you want to approve this milestone setup?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Approve',
          onPress: async () => {
            setApprovingMilestone(milestoneId);
            try {
              const response = await milestones_service.approve_milestone(milestoneId, userId);

              if (response.success) {
                Alert.alert('Success', 'Milestone setup approved successfully', [
                  {
                    text: 'OK',
                    onPress: () => {
                      if (onApprovalComplete) onApprovalComplete();
                      navigation.goBack();
                    },
                  },
                ]);
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

  const handleRequestChanges = (milestoneId: number) => {
    setPendingRejectMilestoneId(milestoneId);
    setMilestoneRejectReason('');
    setShowMilestoneRejectModal(true);
  };

  const confirmRejectMilestone = async () => {
    if (!milestoneRejectReason.trim()) {
      Alert.alert('Required', 'Please provide a reason for requesting changes');
      return;
    }

    if (!pendingRejectMilestoneId) return;

    setShowMilestoneRejectModal(false);
    setRejectingMilestone(pendingRejectMilestoneId);

    try {
      const response = await milestones_service.reject_milestone(
        pendingRejectMilestoneId,
        userId,
        milestoneRejectReason.trim()
      );

      if (response.success) {
        Alert.alert('Success', 'Change request sent to contractor', [
          {
            text: 'OK',
            onPress: () => {
              if (onApprovalComplete) onApprovalComplete();
              navigation.goBack();
            },
          },
        ]);
      } else {
        Alert.alert('Error', response.message || 'Failed to request changes');
      }
    } catch (error) {
      Alert.alert('Error', 'An unexpected error occurred');
    } finally {
      setRejectingMilestone(null);
      setPendingRejectMilestoneId(null);
      setMilestoneRejectReason('');
    }
  };

  const handleEditRejectionReason = (milestone: Milestone) => {
    setPendingRejectMilestoneId(milestone.milestone_id);
    setMilestoneRejectReason(milestone.setup_rej_reason || '');
    setShowMilestoneRejectModal(true);
  };

  const handleEditMilestoneSetup = (milestone: Milestone) => {
    setMilestoneToEdit(milestone);
    setShowEditMilestone(true);
  };

  // Calculate progress - count completed milestone items
  const completedCount = allMilestoneItems.filter(item => item.item_status === 'completed').length;
  const totalCount = allMilestoneItems.length;
  const progressPercentage = totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0;
  
  // Check if all milestone items are completed
  const allMilestoneItemsCompleted = totalCount > 0 && completedCount === totalCount;

  // Find the first submitted milestone (for approval)
  const submittedMilestone = milestones.find(m => m.setup_status === 'submitted');

  // Fetch payment history
  const fetchPaymentHistory = async () => {
    setLoadingPayments(true);
    try {
      const response = await payment_service.get_payments_by_project(projectId);
      if (response.success) {
        // Handle different response structures
        const payments = response.data?.payments || response.payments || [];
        setPaymentHistory(Array.isArray(payments) ? payments : []);
      } else {
        console.warn('Payment history API returned unsuccessful response:', response.message);
        setPaymentHistory([]); // Set empty array instead of showing alert
      }
    } catch (error) {
      console.error('Error fetching payment history:', error);
      setPaymentHistory([]); // Set empty array instead of showing alert
    } finally {
      setLoadingPayments(false);
    }
  };

  const handleViewPaymentHistory = () => {
    setShowPaymentHistory(true);
    fetchPaymentHistory();
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric' 
    });
  };

  const formatDateTime = (dateString: string) => {
    const date = new Date(dateString);
    const dateStr = date.toLocaleDateString('en-US', { 
      month: '2-digit', 
      day: '2-digit', 
      year: 'numeric' 
    });
    const timeStr = date.toLocaleTimeString('en-US', { 
      hour: '2-digit', 
      minute: '2-digit',
      hour12: true 
    });
    return { date: dateStr, time: timeStr };
  };

  const calculateTotals = () => {
    const approvedPayments = paymentHistory.filter(p => p.payment_status === 'approved');
    const totalPaid = approvedPayments.reduce((sum, p) => sum + parseFloat(p.amount), 0);
    const totalEstimated = totalCost || 0;
    const downpayment = isDownpaymentMode ? downpaymentAmount : 0;
    const remaining = totalEstimated - downpayment - totalPaid;
    return { totalEstimated, totalPaid, remaining, downpayment };
  };

  const getPaymentStatusColor = (status: string) => {
    switch (status) {
      case 'approved': return COLORS.success;
      case 'rejected': return COLORS.error;
      case 'submitted': return COLORS.warning;
      default: return COLORS.textSecondary;
    }
  };

  const getPaymentStatusLabel = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
  };

  const getReceiptUrl = (receiptPath: string) => {
    if (!receiptPath) return '';
    if (receiptPath.startsWith('http://') || receiptPath.startsWith('https://')) {
      return receiptPath;
    }
    // Clean up the path
    let path = receiptPath.replace(/^\/+/, '');
    if (path.startsWith('storage/')) {
      path = path.replace(/^storage\//, '');
    }
    if (path.startsWith('public/')) {
      path = path.replace(/^public\//, '');
    }
    return `${api_config.base_url}/api/files/${path}`;
  };

  const handlePaymentClick = (payment: any) => {
    setSelectedPayment(payment);
  };

  const getPaymentTypeLabel = (type: string) => {
    return type.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase());
  };

  const handleSendReport = () => {
    setShowMenu(false);
    setShowPaymentHistoryMenu(false);
    Alert.alert(
      'File a Dispute',
      'Would you like to file a dispute or report an issue with this project?',
      [
        { text: 'Cancel', style: 'cancel' },
        { 
          text: 'File Dispute', 
          onPress: () => {
            // Navigate to dispute form - would need project/milestone context
            Alert.alert('Info', 'Please select a specific milestone item to file a dispute');
          }
        }
      ]
    );
  };

  const handleReportHistory = () => {
    setShowMenu(false);
    setShowPaymentHistoryMenu(false);
    setShowDisputeHistory(true);
  };

  const handleApprovePayment = (paymentId: number) => {
    Alert.alert(
      'Approve Payment',
      'Confirm that you have received this payment?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Approve',
          onPress: async () => {
            try {
              const response = await payment_service.approve_payment(paymentId);
              
              if (response.success) {
                Alert.alert('Success', 'Payment approved successfully');
                setSelectedPayment(null);
                // Refresh payment history
                fetchPaymentHistory();
              } else {
                Alert.alert('Error', response.message || 'Failed to approve payment');
              }
            } catch (error) {
              console.error('Error approving payment:', error);
              Alert.alert('Error', 'An unexpected error occurred');
            }
          }
        }
      ]
    );
  };

  const handleRejectPayment = (paymentId: number) => {
    setRejectReason('');
    setShowRejectModal(true);
  };

  const submitRejectPayment = async () => {
    if (!rejectReason.trim()) {
      Alert.alert('Required', 'Please provide a reason for declining this payment');
      return;
    }

    if (rejectReason.length > 1000) {
      Alert.alert('Too Long', 'Reason cannot exceed 1000 characters');
      return;
    }

    setShowRejectModal(false);

    try {
      const response = await payment_service.reject_payment(selectedPayment.payment_id, rejectReason);
      
      if (response.success) {
        Alert.alert('Declined', 'Payment has been declined');
        setSelectedPayment(null);
        setRejectReason('');
        // Refresh payment history
        fetchPaymentHistory();
      } else {
        Alert.alert('Error', response.message || 'Failed to decline payment');
      }
    } catch (error) {
      console.error('Error rejecting payment:', error);
      Alert.alert('Error', 'An unexpected error occurred');
    }
  };

  const handleCompleteProject = () => {
    setShowCompleteProjectModal(true);
  };

  const confirmCompleteProject = async () => {
    setCompletingProject(true);
    setShowCompleteProjectModal(false);

    try {
      const response = await projects_service.complete_project(projectId);

      if (response.success) {
        setIsProjectCompleted(true);
        Alert.alert(
          '🎉 Congratulations!',
          'Your project has been successfully completed! Thank you for using our platform to bring your vision to life.',
          [
            {
              text: 'OK',
              onPress: () => {
                // Call the refresh callback if provided
                if (onApprovalComplete) {
                  onApprovalComplete();
                }
              }
            }
          ]
        );
      } else {
        Alert.alert('Error', response.message || 'Failed to complete project');
      }
    } catch (error) {
      console.error('Error completing project:', error);
      Alert.alert('Error', 'An unexpected error occurred while completing the project');
    } finally {
      setCompletingProject(false);
    }
  };

  // Helper: renders small status tag pills for a milestone item
  const renderItemStatusTags = (item: MilestoneItem & { parentMilestoneId: number; parentSetupStatus: string; parentMilestoneStatus: string }) => {
    const tags: { label: string; color: string; bg: string; icon: string }[] = [];
    const isItemCompleted = item.item_status === 'completed' || item.parentMilestoneStatus === 'completed';

    if (item.item_status === 'halt') {
      tags.push({ label: 'Halted', color: COLORS.error, bg: COLORS.errorLight, icon: 'pause-circle' });
    }
    if (item.latest_progress_status === 'rejected') {
      tags.push({ label: 'Report Rejected', color: COLORS.error, bg: COLORS.errorLight, icon: 'x-circle' });
    }
    if (item.latest_payment_status === 'rejected') {
      tags.push({ label: 'Payment Rejected', color: COLORS.error, bg: COLORS.errorLight, icon: 'x-circle' });
    }
    if (!isItemCompleted && item.latest_progress_status === 'submitted') {
      tags.push({ label: 'New Report', color: COLORS.info, bg: COLORS.infoLight, icon: 'file-text' });
    }
    if (!isItemCompleted && item.latest_payment_status === 'submitted') {
      tags.push({ label: 'New Payment', color: COLORS.info, bg: COLORS.infoLight, icon: 'credit-card' });
    }
    if (item.was_extended) {
      tags.push({ label: 'Extended', color: COLORS.warning, bg: COLORS.warningLight, icon: 'clock' });
    }

    if (tags.length === 0) return null;

    return (
      <View style={styles.statusTagsContainer}>
        {tags.map((tag, i) => (
          <View key={i} style={[styles.statusTag, { backgroundColor: tag.bg, borderColor: tag.color + '40' }]}>
            <Feather name={tag.icon as any} size={10} color={tag.color} />
            <Text style={[styles.statusTagText, { color: tag.color }]}>{tag.label}</Text>
          </View>
        ))}
      </View>
    );
  };

  // If a milestone detail is selected, show the detail view
  // ── Inline: Project Summary ──
  if (showProjectSummary) {
    return (
      <ProjectSummary
        route={{
          params: {
            projectId,
            projectTitle,
            userRole,
            userId,
            onMilestonePress: (m: any) => {
              setShowProjectSummary(false);
              setShowMilestoneSummary(m.item_id);
            },
          },
        }}
        navigation={{ goBack: () => setShowProjectSummary(false) }}
      />
    );
  }

  // ── Inline: Milestone Summary ──
  if (showMilestoneSummary !== null) {
    return (
      <MilestoneSummary
        route={{
          params: {
            projectId,
            itemId: showMilestoneSummary,
            projectTitle,
          },
        }}
        navigation={{ goBack: () => setShowMilestoneSummary(null) }}
      />
    );
  }

  if (selectedMilestoneDetail) {
    // Determine if the previous item (by sequence_order) is completed
    const currentSeq = selectedMilestoneDetail.item.sequence_order;
    const prevItem = allMilestoneItems
      .filter(i => i.sequence_order < currentSeq)
      .sort((a, b) => b.sequence_order - a.sequence_order)[0];
    const isPreviousItemComplete = !prevItem || prevItem.item_status === 'completed';

    return (
      <MilestoneDetail
        key={selectedMilestoneDetail.item.item_id}
        route={{
          params: {
            milestoneItem: selectedMilestoneDetail.item,
            milestoneNumber: selectedMilestoneDetail.milestoneNumber,
            cumulativePercentage: selectedMilestoneDetail.cumulativePercentage,
            projectTitle,
            projectId,
            totalMilestones: allMilestoneItems.length,
            isApproved: selectedMilestoneDetail.item.parentSetupStatus === 'approved',
            isCompleted: selectedMilestoneDetail.item.parentMilestoneStatus === 'completed',
            userRole,
            userId,
            isPreviousItemComplete,
            projectStatus,
          },
        }}
        navigation={{
          goBack: () => setSelectedMilestoneDetail(null),
          onShowSummary: (itemId: number) => {
            setSelectedMilestoneDetail(null);
            setShowMilestoneSummary(itemId);
          },
        }}
      />
    );
  }

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
          <Feather name="chevron-left" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Project Timeline</Text>
        <TouchableOpacity style={styles.menuButton} onPress={() => setShowMenu(!showMenu)}>
          <Feather name="more-vertical" size={20} color={COLORS.text} />
        </TouchableOpacity>

        {/* Menu Dropdown */}
        {showMenu && (
          <View style={styles.menuDropdown}>
            <TouchableOpacity style={styles.menuItem} onPress={handleSendReport}>
              <Feather name="file-text" size={16} color={COLORS.text} />
              <Text style={styles.menuItemText}>Send Report</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.menuItem} onPress={handleReportHistory}>
              <Feather name="clock" size={16} color={COLORS.text} />
              <Text style={styles.menuItemText}>Report History</Text>
            </TouchableOpacity>
          </View>
        )}
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Milestone Setup Summary Card */}
        <View style={styles.summaryCard}>
          {/* Title + Update button row */}
          {firstMilestone?.milestone_name && (
            <View style={styles.summaryTitleRow}>
              <View style={{ flex: 1 }}>
                <Text style={styles.summaryTitleLabel}>PROJECT TITLE</Text>
                <Text style={styles.summaryTitleText} numberOfLines={2}>{firstMilestone.milestone_name}</Text>
              </View>
              {userRole === 'contractor' && !isProjectCompleted && (
                <TouchableOpacity
                  style={styles.updateProjectBtn}
                  onPress={() => setShowUpdateModal(true)}
                  activeOpacity={0.8}
                >
                  <Feather name="edit-2" size={12} color={COLORS.accent} />
                  <Text style={styles.updateProjectBtnText}>Update</Text>
                </TouchableOpacity>
              )}
              <TouchableOpacity
                style={[styles.updateProjectBtn, { borderColor: COLORS.info }]}
                onPress={() => setShowProjectSummary(true)}
                activeOpacity={0.8}
              >
                <Feather name="bar-chart-2" size={12} color={COLORS.info} />
                <Text style={[styles.updateProjectBtnText, { color: COLORS.info }]}>Summary</Text>
              </TouchableOpacity>
            </View>
          )}

          {/* Progress — always visible */}
          <View style={styles.progressRow}>
            <Text style={styles.progressLabel}>{completedCount}/{totalCount} milestones</Text>
            <View style={styles.progressBarBg}>
              <View style={[styles.progressBarFill, { width: `${progressPercentage}%` }]} />
            </View>
            <Text style={styles.progressPercent}>{progressPercentage}%</Text>
          </View>

          {/* Key stats row: cost + timeline */}
          <View style={styles.summaryStatsRow}>
            <View style={styles.summaryStat}>
              <Text style={styles.summaryStatValue}>{formatCurrency(totalCost)}</Text>
              <Text style={styles.summaryStatLabel}>Total Cost</Text>
            </View>
            {firstMilestone?.start_date && firstMilestone?.end_date && (
              <>
                <View style={styles.summaryStatDivider} />
                <View style={styles.summaryStat}>
                  <Text style={styles.summaryStatValue}>{formatDate(firstMilestone.start_date)} – {formatDate(firstMilestone.end_date)}</Text>
                  <Text style={styles.summaryStatLabel}>Timeline</Text>
                </View>
              </>
            )}
          </View>

          {/* Project-level extension indicator */}
          {approvedUpdates.length > 0 && (
            <TouchableOpacity
              style={{ marginTop: 8, padding: 10, backgroundColor: COLORS.warningLight, borderRadius: 8, flexDirection: 'row', alignItems: 'center' }}
              onPress={() => setShowProjectDateHistory(!showProjectDateHistory)}
              activeOpacity={0.8}
            >
              <Feather name="clock" size={14} color={COLORS.warning} />
              <Text style={{ fontSize: 12, color: COLORS.warning, fontWeight: '600', marginLeft: 6, flex: 1 }}>
                Timeline extended {approvedUpdates.length > 1 ? `${approvedUpdates.length} times` : 'once'}
              </Text>
              <Feather name={showProjectDateHistory ? 'chevron-up' : 'chevron-down'} size={14} color={COLORS.warning} />
            </TouchableOpacity>
          )}
          {showProjectDateHistory && approvedUpdates.length > 0 && (
            <View style={{ marginTop: 6, padding: 10, backgroundColor: '#FAFAFA', borderRadius: 8, borderWidth: 1, borderColor: COLORS.border }}>
              {approvedUpdates.map((upd, idx) => (
                <View key={upd.extension_id} style={{ marginBottom: idx < approvedUpdates.length - 1 ? 10 : 0 }}>
                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
                    <View style={{ width: 8, height: 8, borderRadius: 4, backgroundColor: COLORS.warning }} />
                    <Text style={{ fontSize: 12, color: COLORS.text, fontWeight: '500' }}>
                      Update #{upd.extension_id}
                    </Text>
                  </View>
                  <View style={{ marginLeft: 14, marginTop: 3 }}>
                    <Text style={{ fontSize: 11, color: COLORS.textSecondary }}>
                      {new Date(upd.current_end_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                      {' → '}
                      {new Date(upd.proposed_end_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                    </Text>
                    {upd.applied_at && (
                      <Text style={{ fontSize: 10, color: COLORS.textMuted, marginTop: 1 }}>
                        Approved on {new Date(upd.applied_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                      </Text>
                    )}
                    {upd.reason && (
                      <Text style={{ fontSize: 10, color: COLORS.textMuted, marginTop: 1 }} numberOfLines={2}>
                        Reason: {upd.reason}
                      </Text>
                    )}
                  </View>
                </View>
              ))}
            </View>
          )}
        </View>

        {/* Owner: Pending Update Banner */}
        {userRole === 'owner' && pendingUpdate && (
          <TouchableOpacity
            style={styles.pendingUpdateBanner}
            onPress={() => setShowUpdateModal(true)}
            activeOpacity={0.8}
          >
            <View style={styles.pendingExtBannerInner}>
              <View style={[
                styles.pendingExtIconCircle,
                { backgroundColor: pendingUpdate.status === 'revision_requested' ? '#FFF3E0' : '#FFF8E1' }
              ]}>
                <Feather
                  name={pendingUpdate.status === 'revision_requested' ? 'edit-3' : 'alert-circle'}
                  size={22}
                  color={pendingUpdate.status === 'revision_requested' ? '#E65100' : '#F9A825'}
                />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={styles.pendingExtTitle}>
                  {pendingUpdate.status === 'revision_requested'
                    ? 'Revision Requested'
                    : 'Pending Update Request'}
                </Text>
                <Text style={styles.pendingExtDesc} numberOfLines={2}>
                  {pendingUpdate.status === 'revision_requested'
                    ? 'You requested changes. Waiting for the contractor to revise.'
                    : 'The contractor has submitted a proposal for review.'}
                </Text>
              </View>
              <Feather name="chevron-right" size={20} color={COLORS.textMuted} />
            </View>
          </TouchableOpacity>
        )}

        {/* Project Halted Banner */}
        {(projectStatus === 'halt' || projectStatus === 'on_hold' || projectStatus === 'halted') && (
          <View style={styles.haltedBanner}>
            <View style={styles.haltedBannerInner}>
              <View style={styles.haltedIconContainer}>
                <Feather name="pause-circle" size={28} color={COLORS.error} />
              </View>
              <View style={styles.haltedTextContainer}>
                <Text style={styles.haltedTitle}>Project Halted</Text>
                <Text style={styles.haltedMessage}>
                  This project has been halted due to a pending dispute or administrative action. Milestone progress is temporarily paused.
                </Text>
              </View>
            </View>
          </View>
        )}

        {/* Timeline Section */}
        <View style={styles.timelineSection}>
          {/* Milestones - displayed from top (highest %) to bottom (lowest %) */}
          {allMilestoneItems.slice().reverse().map((item, index) => {
            const actualIndex = allMilestoneItems.length - 1 - index;
            const milestoneNumber = actualIndex + 1;
            const isLeft = index % 2 === 0; // Alternate left and right
            const isLast = index === allMilestoneItems.length - 1;

            // Safe percentage value (handle undefined/null)
            const itemPercentage = Number(item.percentage_progress) || 0;

            // Calculate cumulative percentage up to this milestone
            const cumulativePercentage = allMilestoneItems
              .slice(0, actualIndex + 1)
              .reduce((sum, m) => sum + (Number(m.percentage_progress) || 0), 0);

            // Round to whole number for display in circle
            const displayPercentage = Math.round(cumulativePercentage);

            const isApproved = item.parentSetupStatus === 'approved';

            // Sequential lock: check if previous item is completed
            const prevItem = allMilestoneItems
              .filter(i => i.sequence_order < item.sequence_order)
              .sort((a, b) => b.sequence_order - a.sequence_order)[0];
            const isLocked = prevItem && prevItem.item_status !== 'completed';

            return (
              <TouchableOpacity
                key={item.item_id}
                style={[styles.timelineItem, isLocked && { opacity: 0.55 }]}
                onPress={() => handleMilestonePress(item, milestoneNumber, cumulativePercentage)}
                activeOpacity={0.7}
              >
                {/* Left Content */}
                <View style={[styles.timelineSide, styles.timelineLeft]}>
                  {isLeft && (
                    <View style={styles.milestoneContent}>
                      <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                        <Text style={styles.milestoneLabel}>Milestone {milestoneNumber}</Text>
                        {isLocked && (
                          <Feather name="lock" size={12} color={COLORS.textMuted} style={{ marginLeft: 6 }} />
                        )}
                      </View>
                      <Text style={styles.milestoneTitle}>{item.milestone_item_title}</Text>
                      {item.adjusted_cost != null && (item.carry_forward_amount ?? 0) > 0 ? (
                        <View>
                          <Text style={[styles.milestoneCost, { textDecorationLine: 'line-through', color: COLORS.textMuted, fontSize: 12 }]}>{formatCurrency(item.milestone_item_cost || 0)}</Text>
                          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                            <Text style={[styles.milestoneCost, { color: '#e74c3c' }]}>{formatCurrency(parseFloat(String(item.adjusted_cost)) || 0)}</Text>
                            <View style={{ backgroundColor: '#fff3e0', borderRadius: 3, paddingHorizontal: 4, paddingVertical: 1 }}>
                              <Text style={{ fontSize: 9, color: '#e74c3c', fontWeight: '700' }}>+CF</Text>
                            </View>
                          </View>
                        </View>
                      ) : (
                        <Text style={styles.milestoneCost}>{formatCurrency(item.milestone_item_cost || 0)}</Text>
                      )}
                      <Text style={styles.milestonePercent}>{itemPercentage.toFixed(2)}%</Text>
                      {renderItemStatusTags(item)}
                    </View>
                  )}
                </View>

                {/* Center - Circle and Line */}
                <View style={styles.timelineCenter}>
                  {/* Status indicator ring */}
                  {(() => {
                    const hasRejectedProgress = item.latest_progress_status === 'rejected';
                    const hasRejectedPayment = item.latest_payment_status === 'rejected';
                    const hasNewProgress = (item.progress_submitted_count ?? 0) > 0;
                    const hasNewPayment = (item.payment_submitted_count ?? 0) > 0;
                    const isHalted = item.item_status === 'halt';
                    const isItemCompleted = item.item_status === 'completed' || item.parentMilestoneStatus === 'completed';

                    // Determine ring color: red for rejected/halted, blue for new submissions, none otherwise
                    let ringColor: string | null = null;
                    let ringIcon: string | null = null;
                    let ringBgColor: string | null = null;
                    if (isHalted) {
                      ringColor = COLORS.error;
                      ringIcon = 'pause-circle';
                      ringBgColor = COLORS.errorLight;
                    } else if (hasRejectedProgress || hasRejectedPayment) {
                      ringColor = COLORS.error;
                      ringIcon = 'alert-circle';
                      ringBgColor = COLORS.errorLight;
                    } else if (!isItemCompleted && (hasNewProgress || hasNewPayment)) {
                      ringColor = COLORS.info;
                      ringIcon = 'arrow-up-circle';
                      ringBgColor = COLORS.infoLight;
                    }

                    return (
                      <View style={styles.circleWrapper}>
                        {ringColor && (
                          <View style={[
                            styles.statusRing,
                            { borderColor: ringColor, backgroundColor: ringBgColor || 'transparent' },
                          ]} />
                        )}
                        <View
                          style={[
                            styles.milestoneCircle,
                            isItemCompleted
                              ? styles.milestoneCircleApproved
                              : styles.milestoneCirclePending,
                          ]}
                        >
                          {item.parentMilestoneStatus === 'completed' ? (
                            <Feather name="check" size={20} color={COLORS.surface} />
                          ) : (
                            <Text
                              style={[
                                styles.circleText,
                                item.item_status === 'completed'
                                  ? styles.circleTextApproved
                                  : styles.circleTextPending,
                              ]}
                            >
                              {displayPercentage}
                            </Text>
                          )}
                        </View>
                        {/* Badge dot */}
                        {ringColor && ringIcon && (
                          <View style={[styles.statusBadgeDot, { backgroundColor: ringColor }]}>
                            <Feather name={ringIcon} size={12} color="#FFFFFF" />
                          </View>
                        )}
                      </View>
                    );
                  })()}
                  {!isLast && <View style={styles.timelineLine} />}
                </View>

                {/* Right Content */}
                <View style={[styles.timelineSide, styles.timelineRight]}>
                  {!isLeft && (
                    <View style={styles.milestoneContent}>
                      <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                        <Text style={styles.milestoneLabel}>Milestone {milestoneNumber}</Text>
                        {isLocked && (
                          <Feather name="lock" size={12} color={COLORS.textMuted} style={{ marginLeft: 6 }} />
                        )}
                      </View>
                      <Text style={styles.milestoneTitle}>{item.milestone_item_title}</Text>
                      {item.adjusted_cost != null && (item.carry_forward_amount ?? 0) > 0 ? (
                        <View>
                          <Text style={[styles.milestoneCost, { textDecorationLine: 'line-through', color: COLORS.textMuted, fontSize: 12 }]}>{formatCurrency(item.milestone_item_cost || 0)}</Text>
                          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                            <Text style={[styles.milestoneCost, { color: '#e74c3c' }]}>{formatCurrency(parseFloat(String(item.adjusted_cost)) || 0)}</Text>
                            <View style={{ backgroundColor: '#fff3e0', borderRadius: 3, paddingHorizontal: 4, paddingVertical: 1 }}>
                              <Text style={{ fontSize: 9, color: '#e74c3c', fontWeight: '700' }}>+CF</Text>
                            </View>
                          </View>
                        </View>
                      ) : (
                        <Text style={styles.milestoneCost}>{formatCurrency(item.milestone_item_cost || 0)}</Text>
                      )}
                      <Text style={styles.milestonePercent}>{itemPercentage.toFixed(2)}%</Text>
                      {renderItemStatusTags(item)}
                    </View>
                  )}
                </View>
              </TouchableOpacity>
            );
          })}

          {/* Start Point */}
          <TouchableOpacity
            style={styles.timelineItem}
            onPress={() => isDownpaymentMode && setShowDownpaymentDetail(true)}
            activeOpacity={0.7}
            disabled={!isDownpaymentMode}
          >
            <View style={[styles.timelineSide, styles.timelineLeft]}>
              <View style={styles.startContent}>
                <Text style={styles.startLabel}>Start</Text>
                {isDownpaymentMode ? (
                  <Text style={styles.startPercent}>
                    {formatCurrency(downpaymentAmount)}
                  </Text>
                ) : (
                  <Text style={styles.startPercent}>0%</Text>
                )}
              </View>
            </View>
            <View style={styles.timelineCenter}>
              <View style={[
                styles.startCircle,
                isDownpaymentMode && styles.startCircleDownpayment
              ]} />
            </View>
            <View style={[styles.timelineSide, styles.timelineRight]} />
          </TouchableOpacity>
        </View>

        {/* Project Completion Status */}
        {isProjectCompleted ? (
          <View style={styles.projectCompletedBanner}>
            <View style={styles.projectCompletedContent}>
              <View style={styles.projectCompletedIconContainer}>
                <Feather name="check-circle" size={32} color={COLORS.success} />
              </View>
              <View style={styles.projectCompletedTextContainer}>
                <Text style={styles.projectCompletedTitle}>🎉 Project Completed!</Text>
                <Text style={styles.projectCompletedMessage}>
                  This project has been successfully completed. Feel free to review the milestones, progress reports, and payment history at any time.
                </Text>
              </View>
            </View>
          </View>
        ) : (
          /* Complete Project Button (Owner only, when all items are completed) */
          !isProjectHalted && userRole === 'owner' && allMilestoneItemsCompleted && (
            <View style={styles.completeProjectSection}>
              <TouchableOpacity
                style={styles.completeProjectButton}
                onPress={handleCompleteProject}
                activeOpacity={0.7}
                disabled={completingProject}
              >
                <View style={styles.completeProjectIconContainer}>
                  <Feather name="check-circle" size={24} color={COLORS.surface} />
                </View>
                <Text style={styles.completeProjectText}>
                  {completingProject ? 'Completing Project...' : 'Complete Project'}
                </Text>
                <Feather name="chevron-right" size={20} color={COLORS.surface} />
              </TouchableOpacity>
            </View>
          )
        )}

        {/* Rejection Reason Indicator - Show for rejected milestones */}
        {!isProjectHalted && userRole === 'owner' && milestones.some(m => m.setup_status === 'rejected' && m.setup_rej_reason) && (
          <View style={styles.rejectionIndicatorSection}>
            {milestones
              .filter(m => m.setup_status === 'rejected' && m.setup_rej_reason)
              .map((rejectedMilestone, index) => (
                <View key={rejectedMilestone.milestone_id} style={styles.rejectionIndicatorCard}>
                  <View style={styles.rejectionIndicatorHeader}>
                    <View style={styles.rejectionIndicatorIconContainer}>
                      <Feather name="alert-circle" size={20} color={COLORS.error} />
                    </View>
                    <View style={styles.rejectionIndicatorTitleContainer}>
                      <Text style={styles.rejectionIndicatorTitle}>
                        Milestone {index + 1} - Changes Requested
                      </Text>
                      <Text style={styles.rejectionIndicatorTimestamp}>
                        {rejectedMilestone.milestone_name}
                      </Text>
                    </View>
                    <TouchableOpacity
                      style={styles.editRejectionButton}
                      onPress={() => handleEditRejectionReason(rejectedMilestone)}
                      activeOpacity={0.7}
                    >
                      <Feather name="edit-2" size={18} color={COLORS.accent} />
                    </TouchableOpacity>
                  </View>
                  <View style={styles.rejectionReasonContainer}>
                    <Text style={styles.rejectionReasonLabel}>Your Feedback:</Text>
                    <Text style={styles.rejectionReasonText}>{rejectedMilestone.setup_rej_reason}</Text>
                  </View>
                  <View style={styles.rejectionStatusBadge}>
                    <View style={styles.rejectionStatusDot} />
                    <Text style={styles.rejectionStatusText}>Awaiting Contractor Response</Text>
                  </View>
                </View>
              ))}
          </View>
        )}

        {/* Contractor Rejection Indicator - Show for rejected milestones */}
        {!isProjectHalted && userRole === 'contractor' && milestones.some(m => m.setup_status === 'rejected' && m.setup_rej_reason) && (
          <View style={styles.rejectionIndicatorSection}>
            {milestones
              .filter(m => m.setup_status === 'rejected' && m.setup_rej_reason)
              .map((rejectedMilestone, index) => (
                <View key={rejectedMilestone.milestone_id} style={styles.contractorRejectionCard}>
                  <View style={styles.contractorRejectionHeader}>
                    <View style={styles.contractorRejectionIconContainer}>
                      <Feather name="x-circle" size={24} color={COLORS.error} />
                    </View>
                    <View style={styles.contractorRejectionTitleContainer}>
                      <Text style={styles.contractorRejectionTitle}>
                        Milestone Setup Rejected
                      </Text>
                      <Text style={styles.contractorRejectionSubtitle}>
                        {rejectedMilestone.milestone_name}
                      </Text>
                    </View>
                  </View>
                  
                  <View style={styles.contractorRejectionReasonBox}>
                    <View style={styles.contractorReasonHeader}>
                      <Feather name="message-square" size={16} color={COLORS.textSecondary} />
                      <Text style={styles.contractorReasonLabel}>Property Owner's Feedback:</Text>
                    </View>
                    <Text style={styles.contractorReasonText}>{rejectedMilestone.setup_rej_reason}</Text>
                  </View>

                  <View style={styles.contractorActionPrompt}>
                    <Feather name="info" size={16} color={COLORS.accent} />
                    <Text style={styles.contractorActionText}>
                      Please review the feedback and update your milestone setup accordingly.
                    </Text>
                  </View>

                  <TouchableOpacity
                    style={styles.contractorEditButton}
                    onPress={() => handleEditMilestoneSetup(rejectedMilestone)}
                    activeOpacity={0.7}
                  >
                    <Feather name="edit-3" size={18} color="#FFFFFF" />
                    <Text style={styles.contractorEditButtonText}>Modify</Text>
                  </TouchableOpacity>
                </View>
              ))}
          </View>
        )}

        {/* Payment History Button */}
        <View style={styles.paymentHistorySection}>
          <TouchableOpacity
            style={styles.paymentHistoryButton}
            onPress={handleViewPaymentHistory}
            activeOpacity={0.7}
          >
            <View style={styles.paymentHistoryIconContainer}>
              <Feather name="credit-card" size={20} color={COLORS.accent} />
            </View>
            <Text style={styles.paymentHistoryText}>View Payment History</Text>
            <Feather name="chevron-right" size={20} color={COLORS.accent} />
          </TouchableOpacity>
        </View>

        <View style={{ height: 140 }} />
      </ScrollView>

      {/* Action Buttons - Fixed at Bottom - Only show for owners */}
      {!isProjectHalted && submittedMilestone && userRole === 'owner' && (
        <View style={[styles.actionButtonsContainer, { paddingBottom: insets.bottom + 16 }]}>
          <TouchableOpacity
            style={styles.requestChangesBtn}
            onPress={() => handleRequestChanges(submittedMilestone.milestone_id)}
            disabled={rejectingMilestone !== null || approvingMilestone !== null}
          >
            {rejectingMilestone === submittedMilestone.milestone_id ? (
              <ActivityIndicator size="small" color={COLORS.textSecondary} />
            ) : (
              <Text style={styles.requestChangesBtnText}>Request Changes</Text>
            )}
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.approveBtn}
            onPress={() => handleApproveMilestone(submittedMilestone.milestone_id)}
            disabled={approvingMilestone !== null || rejectingMilestone !== null}
          >
            {approvingMilestone === submittedMilestone.milestone_id ? (
              <ActivityIndicator size="small" color={COLORS.surface} />
            ) : (
              <Text style={styles.approveBtnText}>Approve Milestone</Text>
            )}
          </TouchableOpacity>
        </View>
      )}

      {/* Payment History Modal */}
      <Modal
        visible={showPaymentHistory}
        animationType="slide"
        presentationStyle="fullScreen"
        onRequestClose={() => setShowPaymentHistory(false)}
      >
        <View style={[styles.modalContainer, { paddingTop: insets.top }]}>
          <StatusBar barStyle="dark-content" backgroundColor="#FAFAFA" />

          {/* Modern Header */}
          <View style={styles.modernHeader}>
            <TouchableOpacity onPress={() => setShowPaymentHistory(false)} style={styles.modernBackButton}>
              <Feather name="chevron-left" size={28} color={COLORS.text} />
            </TouchableOpacity>
            <View style={styles.modernHeaderContent}>
              <Text style={styles.modernHeaderTitle}>Payment History</Text>
            </View>
            <TouchableOpacity style={styles.moreButton} onPress={() => setShowPaymentHistoryMenu(!showPaymentHistoryMenu)}>
              <Feather name="more-vertical" size={24} color={COLORS.text} />
            </TouchableOpacity>

            {/* Menu Dropdown for Payment History */}
            {showPaymentHistoryMenu && (
              <View style={styles.menuDropdown}>
                <TouchableOpacity style={styles.menuItem} onPress={handleSendReport}>
                  <Feather name="file-text" size={18} color={COLORS.text} />
                  <Text style={styles.menuItemText}>Send Report</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.menuItem} onPress={handleReportHistory}>
                  <Feather name="clock" size={18} color={COLORS.text} />
                  <Text style={styles.menuItemText}>Report History</Text>
                </TouchableOpacity>
              </View>
            )}
          </View>

          {loadingPayments ? (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color={COLORS.accent} />
              <Text style={styles.loadingText}>Loading payments...</Text>
            </View>
          ) : paymentHistory.length === 0 ? (
            <View style={styles.emptyContainer}>
              <Feather name="credit-card" size={64} color={COLORS.borderLight} />
              <Text style={styles.emptyTitle}>No Payment History</Text>
              <Text style={styles.emptyText}>
                Payment receipts will appear here once submitted
              </Text>
            </View>
          ) : (
            <ScrollView style={styles.modernPaymentList} contentContainerStyle={styles.modernPaymentListContent}>
              {/* Mark all as read option */}
              <View style={styles.markAllContainer}>
                <TouchableOpacity>
                  <Text style={styles.markAllText}>Mark all as read</Text>
                </TouchableOpacity>
              </View>

              {/* Payment Items */}
              {paymentHistory.map((payment, index) => {
                const dateTime = formatDateTime(payment.transaction_date);
                const statusIcon = payment.payment_status === 'approved' ? 'check' : 
                                  payment.payment_status === 'rejected' ? 'x' : 'minus';
                const statusColor = payment.payment_status === 'approved' ? '#10B981' : 
                                   payment.payment_status === 'rejected' ? '#EF4444' : '#EC7E00';
                
                return (
                  <TouchableOpacity 
                    key={payment.payment_id} 
                    style={styles.modernPaymentItem}
                    onPress={() => handlePaymentClick(payment)}
                    activeOpacity={0.7}
                  >
                    <View style={styles.modernPaymentContent}>
                      {/* Status Icon */}
                      <View style={[styles.modernStatusIcon, { backgroundColor: statusColor + '20' }]}>
                        <Feather name={statusIcon} size={20} color={statusColor} />
                      </View>

                      {/* Main Content */}
                      <View style={styles.modernPaymentMain}>
                        {/* Title and Date Row */}
                        <View style={styles.modernPaymentTopRow}>
                          <View style={styles.modernPaymentTitleContainer}>
                            <Text style={styles.modernPaymentType}>
                              {getPaymentTypeLabel(payment.payment_type)}:{' '}
                              <Text style={styles.modernPaymentMilestone}>{payment.milestone_item_title}</Text>
                            </Text>
                          </View>
                          <View style={styles.modernPaymentDateContainer}>
                            <Text style={styles.modernPaymentDate}>{dateTime.date}</Text>
                            <Text style={styles.modernPaymentTime}>{dateTime.time}</Text>
                          </View>
                        </View>

                        {/* Amount and Details Row */}
                        <View style={styles.modernPaymentBottomRow}>
                          <Text style={styles.modernPaymentAmount}>{formatCurrency(parseFloat(payment.amount))}</Text>
                          <TouchableOpacity onPress={() => handlePaymentClick(payment)}>
                            <Text style={styles.modernDetailsLink}>Details</Text>
                          </TouchableOpacity>
                        </View>
                      </View>
                    </View>
                  </TouchableOpacity>
                );
              })}

              {/* Summary Card */}
              {(() => {
                const totals = calculateTotals();
                return (
                  <View style={styles.modernSummaryCard}>
                    <View style={styles.modernSummaryRow}>
                      <Text style={styles.modernSummaryLabel}>Total Estimated Project Amount:</Text>
                      <Text style={styles.modernSummaryValue}>{formatCurrency(totals.totalEstimated)}</Text>
                    </View>
                    {totals.downpayment > 0 && (
                      <View style={styles.modernSummaryRow}>
                        <Text style={styles.modernSummaryLabel}>Downpayment Amount:</Text>
                        <Text style={styles.modernSummaryValue}>{formatCurrency(totals.downpayment)}</Text>
                      </View>
                    )}
                    <View style={styles.modernSummaryRow}>
                      <Text style={styles.modernSummaryLabel}>Total Amount Paid:</Text>
                      <Text style={[styles.modernSummaryValue, { color: '#10B981' }]}>
                        {formatCurrency(totals.totalPaid)}
                      </Text>
                    </View>
                    <View style={[styles.modernSummaryRow, styles.modernSummaryDivider]} />
                    <View style={[styles.modernSummaryRow, styles.modernSummaryRowLast]}>
                      <Text style={[styles.modernSummaryLabel, { fontWeight: '600' }]}>Total Remaining Amount:</Text>
                      <Text style={[styles.modernSummaryValue, { color: '#EF4444', fontWeight: '700' }]}>
                        {formatCurrency(totals.remaining)}
                      </Text>
                    </View>
                  </View>
                );
              })()}

              <View style={{ height: 32 }} />
            </ScrollView>
          )}
        </View>
      </Modal>

      {/* Payment Detail Modal */}
      {selectedPayment && (
        <Modal
          visible={!!selectedPayment}
          animationType="slide"
          presentationStyle="fullScreen"
          onRequestClose={() => setSelectedPayment(null)}
        >
          <View style={[styles.modalContainer, { paddingTop: insets.top }]}>
            <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

            {/* Modal Header */}
            <View style={styles.modalHeader}>
              <TouchableOpacity onPress={() => setSelectedPayment(null)} style={styles.backButton}>
                <Feather name="arrow-left" size={24} color={COLORS.text} />
              </TouchableOpacity>
              <Text style={styles.modalHeaderTitle}>Payment Details</Text>
              <View style={styles.headerSpacer} />
            </View>

            <ScrollView style={styles.detailScroll} contentContainerStyle={styles.detailContent}>
              {/* Status Badge */}
              <View style={styles.detailStatusContainer}>
                <View style={[styles.detailStatusBadge, { backgroundColor: getPaymentStatusColor(selectedPayment.payment_status) }]}>
                  <Text style={styles.detailStatusText}>{getPaymentStatusLabel(selectedPayment.payment_status)}</Text>
                </View>
              </View>

              {/* Amount Card */}
              <View style={styles.detailAmountCard}>
                <Text style={styles.detailAmountLabel}>Payment Amount</Text>
                <Text style={styles.detailAmountValue}>{formatCurrency(parseFloat(selectedPayment.amount))}</Text>
              </View>

              {/* Milestone Info Card */}
              <View style={styles.detailCard}>
                <View style={styles.detailCardHeader}>
                  <Feather name="flag" size={20} color={COLORS.accent} />
                  <Text style={styles.detailCardTitle}>Milestone Information</Text>
                </View>
                <View style={styles.detailCardContent}>
                  <Text style={styles.detailMilestoneTitle}>{selectedPayment.milestone_item_title}</Text>
                  <View style={styles.detailInfoRow}>
                    <Text style={styles.detailInfoLabel}>Progress:</Text>
                    <Text style={styles.detailInfoValue}>{selectedPayment.percentage_progress}%</Text>
                  </View>
                </View>
              </View>

              {/* Transaction Details Card */}
              <View style={styles.detailCard}>
                <View style={styles.detailCardHeader}>
                  <Feather name="info" size={20} color={COLORS.accent} />
                  <Text style={styles.detailCardTitle}>Transaction Details</Text>
                </View>
                <View style={styles.detailCardContent}>
                  <View style={styles.detailInfoRow}>
                    <Text style={styles.detailInfoLabel}>Date:</Text>
                    <Text style={styles.detailInfoValue}>{formatDate(selectedPayment.transaction_date)}</Text>
                  </View>
                  <View style={styles.detailInfoRow}>
                    <Text style={styles.detailInfoLabel}>Payment Method:</Text>
                    <Text style={styles.detailInfoValue}>{getPaymentTypeLabel(selectedPayment.payment_type)}</Text>
                  </View>
                  {selectedPayment.transaction_number && (
                    <View style={styles.detailInfoRow}>
                      <Text style={styles.detailInfoLabel}>Reference #:</Text>
                      <Text style={styles.detailInfoValue}>{selectedPayment.transaction_number}</Text>
                    </View>
                  )}
                  {selectedPayment.owner_name && (
                    <View style={styles.detailInfoRow}>
                      <Text style={styles.detailInfoLabel}>Submitted By:</Text>
                      <Text style={styles.detailInfoValue}>{selectedPayment.owner_name}</Text>
                    </View>
                  )}
                </View>
              </View>

              {/* Rejection Reason Card (if rejected) */}
              {selectedPayment.payment_status === 'rejected' && selectedPayment.reason && (
                <View style={[styles.detailCard, { backgroundColor: COLORS.errorLight, borderColor: COLORS.error, borderWidth: 1 }]}>
                  <View style={styles.detailCardHeader}>
                    <Feather name="alert-circle" size={20} color={COLORS.error} />
                    <Text style={[styles.detailCardTitle, { color: COLORS.error }]}>Rejection Reason</Text>
                  </View>
                  <View style={styles.detailCardContent}>
                    <Text style={[styles.detailInfoValue, { color: COLORS.error, fontSize: 15, lineHeight: 22 }]}>
                      {selectedPayment.reason}
                    </Text>
                  </View>
                </View>
              )}

              {/* Receipt Photo Card */}
              {selectedPayment.receipt_photo && (
                <View style={styles.detailCard}>
                  <View style={styles.detailCardHeader}>
                    <Feather name="image" size={20} color={COLORS.accent} />
                    <Text style={styles.detailCardTitle}>Payment Receipt</Text>
                  </View>
                  <View style={styles.detailCardContent}>
                    <TouchableOpacity 
                      style={styles.receiptImageContainer}
                      onPress={() => setShowFullScreenImage(true)}
                      activeOpacity={0.8}
                    >
                      <Image
                        source={{ uri: getReceiptUrl(selectedPayment.receipt_photo) }}
                        style={styles.receiptImage}
                        resizeMode="contain"
                        onError={(error) => {
                          console.error('Receipt image failed to load:', error.nativeEvent.error);
                        }}
                      />
                      <View style={styles.imageOverlay}>
                        <Feather name="maximize-2" size={24} color={COLORS.surface} />
                      </View>
                    </TouchableOpacity>
                    <Text style={styles.receiptHint}>Tap to view full size</Text>
                  </View>
                </View>
              )}

              {/* Approve/Decline Buttons for Contractor */}
              {!isProjectHalted && userRole === 'contractor' && selectedPayment.payment_status === 'submitted' && (
                <View style={styles.paymentActionsContainer}>
                  <TouchableOpacity
                    style={[styles.paymentActionButton, styles.declineButton]}
                    onPress={() => handleRejectPayment(selectedPayment.payment_id)}
                  >
                    <Feather name="x-circle" size={20} color={COLORS.surface} />
                    <Text style={styles.paymentActionButtonText}>Decline Payment</Text>
                  </TouchableOpacity>
                  <TouchableOpacity
                    style={[styles.paymentActionButton, styles.approveButton]}
                    onPress={() => handleApprovePayment(selectedPayment.payment_id)}
                  >
                    <Feather name="check-circle" size={20} color={COLORS.surface} />
                    <Text style={styles.paymentActionButtonText}>Approve Payment</Text>
                  </TouchableOpacity>
                </View>
              )}

              <View style={{ height: 32 }} />
            </ScrollView>
          </View>
        </Modal>
      )}

      {/* Dispute History Modal */}
      <Modal
        visible={showDisputeHistory}
        animationType="slide"
        presentationStyle="fullScreen"
        onRequestClose={() => setShowDisputeHistory(false)}
      >
        <DisputeHistory onClose={() => setShowDisputeHistory(false)} />
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
              project_id: projectId,
              project_title: projectTitle,
            }}
            userId={userId}
            onClose={() => {
              setShowEditMilestone(false);
              setMilestoneToEdit(null);
            }}
            onSave={async () => {
              setShowEditMilestone(false);
              setMilestoneToEdit(null);
              if (onApprovalComplete) onApprovalComplete();
            }}
            editMode={true}
            existingMilestone={milestoneToEdit}
          />
        </Modal>
      )}

      {/* Downpayment Detail Modal */}
      {showDownpaymentDetail && (
        <Modal
          visible={showDownpaymentDetail}
          animationType="slide"
          presentationStyle="fullScreen"
          onRequestClose={() => setShowDownpaymentDetail(false)}
        >
          <DownpaymentDetail
            projectId={projectId}
            projectTitle={projectTitle}
            downpaymentAmount={downpaymentAmount}
            totalCost={totalCost}
            userRole={userRole}
            userId={userId}
            onClose={() => setShowDownpaymentDetail(false)}
          />
        </Modal>
      )}

      {/* Milestone Rejection Modal */}
      <Modal
        visible={showMilestoneRejectModal}
        transparent={true}
        animationType="fade"
        onRequestClose={() => {
          setShowMilestoneRejectModal(false);
          setPendingRejectMilestoneId(null);
          setMilestoneRejectReason('');
        }}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.milestoneRejectModalContent}>
            <View style={styles.milestoneRejectModalHeader}>
              <View style={styles.milestoneRejectIconContainer}>
                <Feather name="alert-circle" size={24} color={COLORS.error} />
              </View>
              <Text style={styles.milestoneRejectModalTitle}>Request Changes to Milestone Setup</Text>
              <Text style={styles.milestoneRejectModalSubtitle}>
                Please explain what needs to be changed in this milestone setup. The contractor will review your feedback and make the necessary adjustments.
              </Text>
            </View>

            <View style={styles.milestoneRejectInputContainer}>
              <Text style={styles.milestoneRejectInputLabel}>
                Reason for Changes <Text style={styles.requiredStar}>*</Text>
              </Text>
              <TextInput
                style={styles.milestoneRejectTextInput}
                value={milestoneRejectReason}
                onChangeText={setMilestoneRejectReason}
                placeholder="E.g., Timeline needs adjustment, Cost breakdown unclear, Missing important tasks..."
                placeholderTextColor={COLORS.textMuted}
                multiline
                numberOfLines={5}
                maxLength={500}
                textAlignVertical="top"
              />
              <Text style={styles.milestoneCharacterCount}>
                {milestoneRejectReason.length}/500
              </Text>
            </View>

            <View style={styles.milestoneRejectModalActions}>
              <TouchableOpacity
                style={styles.milestoneRejectCancelButton}
                onPress={() => {
                  setShowMilestoneRejectModal(false);
                  setPendingRejectMilestoneId(null);
                  setMilestoneRejectReason('');
                }}
              >
                <Text style={styles.milestoneRejectCancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[
                  styles.milestoneRejectConfirmButton,
                  !milestoneRejectReason.trim() && styles.milestoneRejectConfirmButtonDisabled
                ]}
                onPress={confirmRejectMilestone}
                disabled={!milestoneRejectReason.trim()}
              >
                <Feather name="send" size={18} color="#FFFFFF" />
                <Text style={styles.milestoneRejectConfirmButtonText}>Send Request</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Reject Payment Reason Modal */}
      <Modal
        visible={showRejectModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowRejectModal(false)}
      >
        <View style={styles.rejectModalOverlay}>
          <View style={styles.rejectModalContent}>
            <View style={styles.rejectModalHeader}>
              <Text style={styles.rejectModalTitle}>Decline Payment</Text>
              <TouchableOpacity onPress={() => setShowRejectModal(false)}>
                <Feather name="x" size={24} color={COLORS.text} />
              </TouchableOpacity>
            </View>

            <Text style={styles.rejectModalDescription}>
              Please provide a reason for declining this payment. The owner will see this reason.
            </Text>

            <TextInput
              style={styles.rejectReasonInput}
              value={rejectReason}
              onChangeText={setRejectReason}
              placeholder="e.g., Payment not received, Incorrect amount, etc."
              placeholderTextColor={COLORS.textMuted}
              multiline
              numberOfLines={4}
              maxLength={1000}
              textAlignVertical="top"
            />

            <Text style={styles.rejectCharCount}>{rejectReason.length} / 1000</Text>

            <View style={styles.rejectModalButtons}>
              <TouchableOpacity
                style={[styles.rejectModalButton, styles.rejectCancelButton]}
                onPress={() => setShowRejectModal(false)}
              >
                <Text style={styles.rejectCancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.rejectModalButton, styles.rejectSubmitButton]}
                onPress={submitRejectPayment}
              >
                <Text style={styles.rejectSubmitButtonText}>Decline Payment</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Complete Project Confirmation Modal */}
      <Modal
        visible={showCompleteProjectModal}
        animationType="fade"
        transparent={true}
        onRequestClose={() => setShowCompleteProjectModal(false)}
      >
        <View style={styles.completeModalOverlay}>
          <View style={styles.completeModalContent}>
            <View style={styles.completeModalIconContainer}>
              <Feather name="alert-circle" size={48} color={COLORS.warning} />
            </View>

            <Text style={styles.completeModalTitle}>Complete This Project?</Text>
            
            <Text style={styles.completeModalDescription}>
              You are about to mark this entire project as completed. This action will:
            </Text>

            <View style={styles.completeModalList}>
              <View style={styles.completeModalListItem}>
                <Feather name="check" size={16} color={COLORS.success} />
                <Text style={styles.completeModalListText}>Mark all milestones as finished</Text>
              </View>
              <View style={styles.completeModalListItem}>
                <Feather name="check" size={16} color={COLORS.success} />
                <Text style={styles.completeModalListText}>Close the project timeline</Text>
              </View>
              <View style={styles.completeModalListItem}>
                <Feather name="check" size={16} color={COLORS.success} />
                <Text style={styles.completeModalListText}>Archive all project data</Text>
              </View>
            </View>

            <Text style={styles.completeModalWarning}>
              This action cannot be undone. Are you sure you want to proceed?
            </Text>

            <View style={styles.completeModalButtons}>
              <TouchableOpacity
                style={[styles.completeModalButton, styles.completeCancelButton]}
                onPress={() => setShowCompleteProjectModal(false)}
              >
                <Text style={styles.completeCancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.completeModalButton, styles.completeConfirmButton]}
                onPress={confirmCompleteProject}
              >
                <Text style={styles.completeConfirmButtonText}>Complete Project</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Full Screen Image Modal */}
      {selectedPayment && showFullScreenImage && (
        <Modal
          visible={showFullScreenImage}
          animationType="fade"
          presentationStyle="fullScreen"
          onRequestClose={() => setShowFullScreenImage(false)}
        >
          <View style={styles.fullScreenImageContainer}>
            <StatusBar barStyle="light-content" backgroundColor="#000" />
            
            {/* Close Button */}
            <TouchableOpacity 
              style={styles.closeImageButton}
              onPress={() => setShowFullScreenImage(false)}
            >
              <Feather name="x" size={28} color="#FFF" />
            </TouchableOpacity>

            <ScrollView
              contentContainerStyle={styles.fullScreenImageScroll}
              maximumZoomScale={3}
              minimumZoomScale={1}
            >
              <Image
                source={{ uri: getReceiptUrl(selectedPayment.receipt_photo) }}
                style={styles.fullScreenImage}
                resizeMode="contain"
              />
            </ScrollView>

            <View style={styles.imageInfoBar}>
              <Text style={styles.imageInfoText}>Pinch to zoom • Swipe to pan</Text>
            </View>
          </View>
        </Modal>
      )}

      {/* Project Update Modal */}
      <ProjectUpdateModal
        visible={showUpdateModal}
        onClose={() => setShowUpdateModal(false)}
        projectId={projectId}
        userId={userId}
        userRole={userRole}
        onActionComplete={() => { setShowUpdateModal(false); fetchPendingUpdate(); }}
      />
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
    paddingHorizontal: 12,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  headerTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    letterSpacing: 0.2,
  },
  backButton: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
  },
  menuButton: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: 20,
    paddingTop: 16,
  },

  // ── Summary Card ──
  summaryCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 3,
    padding: 14,
    marginBottom: 20,
    borderWidth: 1,
    borderColor: COLORS.border,
    gap: 12,
  },
  summaryTitleLabel: {
    fontSize: 9,
    fontWeight: '700',
    color: COLORS.textMuted,
    letterSpacing: 0.8,
    textTransform: 'uppercase',
    marginBottom: 2,
  },
  summaryTitleText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    lineHeight: 22,
  },
  summaryStatsRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  summaryStat: {
    flex: 1,
    alignItems: 'center',
  },
  summaryStatValue: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.text,
    textAlign: 'center',
  },
  summaryStatLabel: {
    fontSize: 10,
    color: COLORS.textMuted,
    fontWeight: '600',
    textTransform: 'uppercase',
    letterSpacing: 0.3,
    marginTop: 2,
  },
  summaryStatDivider: {
    width: 1,
    height: 28,
    backgroundColor: COLORS.border,
    marginHorizontal: 8,
  },

  // Project Section (kept for compat)
  projectSection: {
    marginBottom: 32,
  },
  projectTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  projectDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: 16,
  },
  setupStatusRow: {
    flexDirection: 'row',
    marginBottom: 16,
  },
  setupStatusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 3,
    backgroundColor: COLORS.borderLight,
  },
  setupStatusText: {
    fontSize: 11,
    fontWeight: '700',
    color: COLORS.textSecondary,
    textTransform: 'uppercase',
    letterSpacing: 0.4,
  },
  progressRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: COLORS.borderLight,
    borderRadius: 3,
    paddingHorizontal: 10,
    paddingVertical: 8,
  },
  progressLabel: {
    fontSize: 11,
    color: COLORS.textSecondary,
    fontWeight: '600',
  },
  progressBarBg: {
    flex: 1,
    height: 4,
    backgroundColor: COLORS.border,
    borderRadius: 2,
    overflow: 'hidden',
  },
  progressBarFill: {
    height: 4,
    backgroundColor: COLORS.accent,
    borderRadius: 2,
  },
  progressPercent: {
    fontSize: 11,
    fontWeight: '700',
    color: COLORS.accent,
    minWidth: 30,
    textAlign: 'right',
  },
  budgetRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
    gap: 8,
  },
  budgetIcon: {
    width: 28,
    height: 20,
    backgroundColor: COLORS.accentLight,
    borderRadius: 3,
    justifyContent: 'center',
    alignItems: 'center',
  },
  budgetText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },
  locationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  locationText: {
    fontSize: 14,
    color: COLORS.text,
  },

  // Timeline Section
  timelineSection: {
    paddingVertical: 16,
  },
  timelineItem: {
    flexDirection: 'row',
    minHeight: 120,
  },
  timelineSide: {
    flex: 1,
    justifyContent: 'flex-start',
    paddingTop: 8,
  },
  timelineLeft: {
    alignItems: 'flex-end',
    paddingRight: 16,
  },
  timelineRight: {
    alignItems: 'flex-start',
    paddingLeft: 16,
  },
  timelineCenter: {
    alignItems: 'center',
    width: 70,
  },
  milestoneCircle: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: COLORS.accent,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 1,
  },
  milestoneCircleApproved: {
    backgroundColor: COLORS.accent,
    borderColor: COLORS.accent,
    borderWidth: 2,
  },
  milestoneCirclePending: {
    backgroundColor: COLORS.surface,
    borderColor: COLORS.accent,
    borderWidth: 2,
  },
  circleText: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.surface,
  },
  circleTextApproved: {
    color: COLORS.surface,
    fontWeight: '700',
  },
  circleTextPending: {
    color: COLORS.accent,
    fontWeight: '700',
  },
  timelineLine: {
    width: 3,
    flex: 1,
    backgroundColor: COLORS.border,
    marginTop: -4,
    marginBottom: -4,
    borderStyle: 'dotted',
  },
  milestoneContent: {
    maxWidth: 140,
  },
  milestoneLabel: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 2,
  },
  milestoneTitle: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 18,
    marginBottom: 4,
  },
  milestoneCost: {
    fontSize: 13,
    fontWeight: '500',
    fontStyle: 'italic',
    color: COLORS.text,
    marginBottom: 2,
  },
  milestonePercent: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.accent,
  },

  // Start Point
  startContent: {
    alignItems: 'flex-end',
  },
  startLabel: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
  },
  startPercent: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.accent,
  },
  startCircle: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: COLORS.darkBlue,
    zIndex: 1,
  },
  startCircleDownpayment: {
    borderWidth: 3,
    borderColor: COLORS.accent,
  },

  // Action Buttons
  actionButtonsContainer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: COLORS.surface,
    paddingHorizontal: 16,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    gap: 12,
  },
  approveBtn: {
    backgroundColor: COLORS.accent,
    borderRadius: 3,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  approveBtnText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.surface,
  },
  requestChangesBtn: {
    backgroundColor: COLORS.borderLight,
    borderRadius: 3,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  requestChangesBtnText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.textSecondary,
  },

  // Payment History Section
  paymentHistorySection: {
    paddingTop: 24,
    paddingBottom: 16,
  },
  paymentHistoryButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: 3,
    padding: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  paymentHistoryIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.accentLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  paymentHistoryText: {
    flex: 1,
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
  },

  // Complete Project Section
  completeProjectSection: {
    paddingTop: 24,
    paddingBottom: 8,
  },
  completeProjectButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.success,
    borderRadius: 3,
    padding: 16,
  },
  completeProjectIconContainer: {
    marginRight: 12,
  },
  completeProjectText: {
    flex: 1,
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.surface,
    textAlign: 'center',
  },

  // Project Completed Banner
  projectCompletedBanner: {
    paddingTop: 24,
    paddingBottom: 8,
  },
  projectCompletedContent: {
    backgroundColor: COLORS.successLight,
    borderRadius: 3,
    padding: 20,
    borderWidth: 1,
    borderColor: COLORS.success,
  },
  projectCompletedIconContainer: {
    alignSelf: 'center',
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
    shadowColor: COLORS.success,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 4,
  },
  projectCompletedTextContainer: {
    alignItems: 'center',
  },
  projectCompletedTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.success,
    marginBottom: 8,
    textAlign: 'center',
  },
  projectCompletedMessage: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
  },

  // Complete Project Modal
  completeModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  completeModalContent: {
    backgroundColor: COLORS.surface,
    borderRadius: 3,
    padding: 24,
    width: '100%',
    maxWidth: 400,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 12,
    elevation: 8,
  },
  completeModalIconContainer: {
    alignSelf: 'center',
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: COLORS.warningLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  completeModalTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.text,
    textAlign: 'center',
    marginBottom: 16,
  },
  completeModalDescription: {
    fontSize: 15,
    color: COLORS.textSecondary,
    textAlign: 'center',
    marginBottom: 20,
    lineHeight: 22,
  },
  completeModalList: {
    backgroundColor: COLORS.borderLight,
    borderRadius: 3,
    padding: 16,
    marginBottom: 20,
  },
  completeModalListItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  completeModalListText: {
    fontSize: 14,
    color: COLORS.text,
    marginLeft: 12,
    flex: 1,
  },
  completeModalWarning: {
    fontSize: 14,
    color: COLORS.error,
    textAlign: 'center',
    fontWeight: '600',
    marginBottom: 24,
  },
  completeModalButtons: {
    flexDirection: 'row',
    gap: 12,
  },
  completeModalButton: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 14,
    borderRadius: 3,
  },
  completeCancelButton: {
    backgroundColor: COLORS.borderLight,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  completeCancelButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
  },
  completeConfirmButton: {
    backgroundColor: COLORS.success,
    shadowColor: COLORS.success,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 3,
  },
  completeConfirmButtonText: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.surface,
  },

  // Payment History Modal
  modalContainer: {
    flex: 1,
    backgroundColor: '#FAFAFA',
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    backgroundColor: COLORS.surface,
  },
  modalHeaderTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
  },
  headerSpacer: {
    width: 44,
  },

  // Modern Header Styles
  modernHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 12,
    backgroundColor: '#FAFAFA',
  },
  modernBackButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  modernHeaderContent: {
    flex: 1,
    paddingLeft: 8,
  },
  modernHeaderTitle: {
    fontSize: 28,
    fontWeight: '700',
    color: COLORS.text,
  },
  moreButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    gap: 16,
  },
  loadingText: {
    fontSize: 16,
    color: COLORS.textSecondary,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 48,
    gap: 16,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '600',
    color: COLORS.text,
    textAlign: 'center',
  },
  emptyText: {
    fontSize: 15,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 22,
  },
  paymentList: {
    flex: 1,
  },
  paymentListContent: {
    padding: 16,
    gap: 12,
  },
  paymentCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 3,
    padding: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
    marginBottom: 12,
  },

  // Modern Payment List Styles
  modernPaymentList: {
    flex: 1,
  },
  modernPaymentListContent: {
    paddingBottom: 20,
  },
  markAllContainer: {
    paddingHorizontal: 20,
    paddingVertical: 12,
    alignItems: 'flex-end',
  },
  markAllText: {
    fontSize: 15,
    fontWeight: '500',
    color: COLORS.text,
  },
  modernPaymentItem: {
    backgroundColor: COLORS.surface,
    paddingVertical: 16,
    paddingHorizontal: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  modernPaymentContent: {
    flexDirection: 'row',
    alignItems: 'flex-start',
  },
  modernStatusIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  modernPaymentMain: {
    flex: 1,
  },
  modernPaymentTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  modernPaymentTitleContainer: {
    flex: 1,
    marginRight: 12,
  },
  modernPaymentType: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
  },
  modernPaymentMilestone: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.accent,
  },
  modernPaymentDateContainer: {
    alignItems: 'flex-end',
  },
  modernPaymentDate: {
    fontSize: 13,
    color: COLORS.textSecondary,
  },
  modernPaymentTime: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 2,
  },
  modernPaymentBottomRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  modernPaymentAmount: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.accent,
  },
  modernDetailsLink: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.accent,
  },

  // Modern Summary Card
  modernSummaryCard: {
    backgroundColor: COLORS.surface,
    marginHorizontal: 20,
    marginTop: 24,
    borderRadius: 3,
    padding: 20,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  modernSummaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
  },
  modernSummaryDivider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginVertical: 8,
  },
  modernSummaryRowLast: {
    marginTop: 8,
    paddingTop: 16,
  },
  modernSummaryLabel: {
    fontSize: 15,
    color: COLORS.text,
    flex: 1,
  },
  modernSummaryValue: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.accent,
    marginLeft: 12,
  },

  paymentMilestoneInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
    gap: 8,
  },
  paymentMilestoneIcon: {
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: COLORS.accentLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  paymentMilestoneTitle: {
    flex: 1,
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },
  paymentAmountRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  paymentAmount: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.text,
  },
  paymentStatusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 3,
  },
  paymentStatusText: {
    fontSize: 12,
    fontWeight: '600',
  },
  paymentDetails: {
    gap: 8,
  },
  paymentDetailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  paymentDetailText: {
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  viewDetailsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
    gap: 4,
  },
  viewDetailsText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.accent,
  },

  // Payment Detail Modal Styles
  detailScroll: {
    flex: 1,
  },
  detailContent: {
    padding: 20,
  },
  detailStatusContainer: {
    alignItems: 'center',
    marginBottom: 20,
  },
  detailStatusBadge: {
    paddingHorizontal: 20,
    paddingVertical: 8,
    borderRadius: 3,
  },
  detailStatusText: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.surface,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  detailAmountCard: {
    backgroundColor: COLORS.primaryLight,
    borderRadius: 3,
    padding: 24,
    alignItems: 'center',
    marginBottom: 20,
    borderWidth: 1,
    borderColor: COLORS.primary,
  },
  detailAmountLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginBottom: 8,
    textTransform: 'uppercase',
    letterSpacing: 1,
  },
  detailAmountValue: {
    fontSize: 36,
    fontWeight: '800',
    color: COLORS.primary,
  },
  detailCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 3,
    padding: 20,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  detailCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
    gap: 10,
  },
  detailCardTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
  },
  detailCardContent: {
    gap: 12,
  },
  detailMilestoneTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
    lineHeight: 24,
  },
  detailInfoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
  },
  detailInfoLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  detailInfoValue: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    textAlign: 'right',
    flex: 1,
    marginLeft: 16,
  },
  receiptImageContainer: {
    width: '100%',
    aspectRatio: 4 / 3,
    backgroundColor: COLORS.borderLight,
    borderRadius: 3,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  receiptImage: {
    width: '100%',
    height: '100%',
  },
  receiptHint: {
    fontSize: 12,
    color: COLORS.textMuted,
    textAlign: 'center',
    marginTop: 8,
    fontStyle: 'italic',
  },
  imageOverlay: {
    position: 'absolute',
    top: 12,
    right: 12,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    borderRadius: 20,
    padding: 8,
  },
  paymentActionsContainer: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 24,
    paddingHorizontal: 4,
  },
  paymentActionButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    borderRadius: 3,
    gap: 8,
  },
  approveButton: {
    backgroundColor: COLORS.success,
  },
  declineButton: {
    backgroundColor: COLORS.error,
  },
  paymentActionButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.surface,
  },

  // Full Screen Image Viewer
  fullScreenImageContainer: {
    flex: 1,
    backgroundColor: '#000',
  },
  closeImageButton: {
    position: 'absolute',
    top: 50,
    right: 20,
    zIndex: 10,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    borderRadius: 24,
    padding: 12,
  },
  fullScreenImageScroll: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  fullScreenImage: {
    width: '100%',
    height: '100%',
  },
  imageInfoBar: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.7)',
    paddingVertical: 16,
    alignItems: 'center',
  },
  imageInfoText: {
    fontSize: 14,
    color: '#FFF',
    fontWeight: '500',
  },

  // Menu Dropdown Styles
  menuDropdown: {
    position: 'absolute',
    top: 48,
    right: 8,
    backgroundColor: COLORS.surface,
    borderRadius: 3,
    paddingVertical: 6,
    minWidth: 170,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 6,
    zIndex: 1000,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    gap: 12,
  },
  menuItemText: {
    fontSize: 15,
    fontWeight: '500',
    color: COLORS.text,
  },

  // Reject Payment Modal Styles
  rejectModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  rejectModalContent: {
    backgroundColor: COLORS.surface,
    borderRadius: 3,
    padding: 24,
    width: '100%',
    maxWidth: 500,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 12,
    elevation: 8,
  },
  rejectModalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  rejectModalTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
  },
  rejectModalDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: 16,
  },
  rejectReasonInput: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 3,
    padding: 12,
    fontSize: 15,
    color: COLORS.text,
    backgroundColor: COLORS.borderLight,
    minHeight: 120,
    maxHeight: 200,
  },
  rejectCharCount: {
    fontSize: 12,
    color: COLORS.textMuted,
    textAlign: 'right',
    marginTop: 8,
    marginBottom: 16,
  },
  rejectModalButtons: {
    flexDirection: 'row',
    gap: 12,
  },
  rejectModalButton: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 3,
    alignItems: 'center',
    justifyContent: 'center',
  },
  rejectCancelButton: {
    backgroundColor: COLORS.borderLight,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  rejectCancelButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
  },
  rejectSubmitButton: {
    backgroundColor: COLORS.error,
  },
  rejectSubmitButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.surface,
  },

  // Milestone Rejection Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  milestoneRejectModalContent: {
    backgroundColor: COLORS.surface,
    borderRadius: 3,
    width: '100%',
    maxWidth: 500,
    padding: 24,
    gap: 20,
  },
  milestoneRejectModalHeader: {
    gap: 12,
    alignItems: 'center',
  },
  milestoneRejectIconContainer: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#FEE2E2',
    justifyContent: 'center',
    alignItems: 'center',
  },
  milestoneRejectModalTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    textAlign: 'center',
  },
  milestoneRejectModalSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
  },
  milestoneRejectInputContainer: {
    gap: 8,
  },
  milestoneRejectInputLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },
  requiredStar: {
    color: COLORS.error,
  },
  milestoneRejectTextInput: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 3,
    padding: 12,
    fontSize: 14,
    color: COLORS.text,
    minHeight: 120,
    backgroundColor: COLORS.background,
  },
  milestoneCharacterCount: {
    fontSize: 12,
    color: COLORS.textMuted,
    textAlign: 'right',
  },
  milestoneRejectModalActions: {
    flexDirection: 'row',
    gap: 12,
  },
  milestoneRejectCancelButton: {
    flex: 1,
    paddingVertical: 14,
    paddingHorizontal: 20,
    borderRadius: 3,
    borderWidth: 1,
    borderColor: COLORS.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  milestoneRejectCancelButtonText: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
  },
  milestoneRejectConfirmButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 14,
    paddingHorizontal: 20,
    borderRadius: 3,
    backgroundColor: COLORS.error,
  },
  milestoneRejectConfirmButtonDisabled: {
    opacity: 0.5,
  },
  milestoneRejectConfirmButtonText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#FFFFFF',
  },

  // Rejection Indicator Styles
  rejectionIndicatorSection: {
    paddingHorizontal: 16,
    paddingTop: 8,
    paddingBottom: 8,
    gap: 12,
  },
  rejectionIndicatorCard: {
    backgroundColor: COLORS.errorLight,
    borderRadius: 3,
    padding: 16,
    borderLeftWidth: 3,
    borderLeftColor: COLORS.error,
    gap: 12,
  },
  rejectionIndicatorHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  rejectionIndicatorIconContainer: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
  },
  rejectionIndicatorTitleContainer: {
    flex: 1,
  },
  rejectionIndicatorTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.error,
    marginBottom: 2,
  },
  rejectionIndicatorTimestamp: {
    fontSize: 13,
    color: COLORS.textSecondary,
  },
  editRejectionButton: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
  },
  rejectionReasonContainer: {
    backgroundColor: COLORS.surface,
    borderRadius: 3,
    padding: 12,
    gap: 6,
  },
  rejectionReasonLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textSecondary,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  rejectionReasonText: {
    fontSize: 14,
    color: COLORS.text,
    lineHeight: 20,
  },
  rejectionStatusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingVertical: 6,
  },
  rejectionStatusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.warning,
  },
  rejectionStatusText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.warning,
  },

  // Contractor Rejection Indicator Styles
  contractorRejectionCard: {
    backgroundColor: '#FFF5F5',
    borderRadius: 3,
    padding: 16,
    borderLeftWidth: 3,
    borderLeftColor: COLORS.error,
    gap: 14,
  },
  contractorRejectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
  },
  contractorRejectionIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: COLORS.error,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 4,
    elevation: 2,
  },
  contractorRejectionTitleContainer: {
    flex: 1,
  },
  contractorRejectionTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.error,
    marginBottom: 4,
  },
  contractorRejectionSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    fontWeight: '500',
  },
  contractorRejectionReasonBox: {
    backgroundColor: COLORS.surface,
    borderRadius: 3,
    padding: 14,
    borderWidth: 1,
    borderColor: '#FFE4E6',
    gap: 10,
  },
  contractorReasonHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 2,
  },
  contractorReasonLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.textSecondary,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  contractorReasonText: {
    fontSize: 15,
    color: COLORS.text,
    lineHeight: 22,
  },
  contractorActionPrompt: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    backgroundColor: COLORS.accentLight,
    borderRadius: 3,
    padding: 12,
    borderWidth: 1,
    borderColor: '#FFE5CC',
  },
  contractorActionText: {
    flex: 1,
    fontSize: 13,
    color: COLORS.text,
    lineHeight: 18,
    fontWeight: '500',
  },
  contractorEditButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: COLORS.accent,
    borderRadius: 3,
    paddingVertical: 12,
    paddingHorizontal: 20,
  },
  contractorEditButtonText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFFFFF',
  },

  // ── Pending Update Banner (Owner) ──
  pendingUpdateBanner: {
    marginHorizontal: 0,
    marginBottom: 16,
    borderRadius: 12,
    backgroundColor: '#FFFDE7',
    borderWidth: 1,
    borderColor: '#FFF176',
    overflow: 'hidden',
  },
  pendingExtBannerInner: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    gap: 12,
  },
  pendingExtIconCircle: {
    width: 42,
    height: 42,
    borderRadius: 21,
    alignItems: 'center',
    justifyContent: 'center',
  },
  pendingExtTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 2,
  },
  pendingExtDesc: {
    fontSize: 12,
    color: COLORS.textMuted,
    lineHeight: 17,
  },

  // ── Project Halted Banner ──
  haltedBanner: {
    marginHorizontal: 0,
    marginBottom: 16,
    borderRadius: 3,
    backgroundColor: COLORS.errorLight,
    borderWidth: 1,
    borderColor: '#FECACA',
    overflow: 'hidden',
  },
  haltedBannerInner: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    padding: 16,
    gap: 12,
  },
  haltedIconContainer: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#FEE2E2',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#FECACA',
  },
  haltedTextContainer: {
    flex: 1,
  },
  haltedTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.error,
    marginBottom: 4,
  },
  haltedMessage: {
    fontSize: 13,
    color: '#991B1B',
    lineHeight: 18,
  },

  // ── Circle status indicators ──
  circleWrapper: {
    position: 'relative',
    width: 66,
    height: 66,
    justifyContent: 'center',
    alignItems: 'center',
  },
  statusRing: {
    position: 'absolute',
    width: 66,
    height: 66,
    borderRadius: 33,
    borderWidth: 3,
    zIndex: 0,
  },
  statusBadgeDot: {
    position: 'absolute',
    top: -2,
    right: -2,
    width: 22,
    height: 22,
    borderRadius: 11,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: COLORS.surface,
    zIndex: 3,
  },

  // ── Item status tags ──
  statusTagsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 4,
    marginTop: 6,
  },
  statusTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 3,
    borderWidth: 1,
  },
  statusTagText: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 0.3,
  },

  // ── Title row (title + update button) ──
  summaryTitleRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 8,
    marginBottom: 4,
  },
  // ── Update Project button ──
  updateProjectBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 16,
    borderWidth: 1.5,
    borderColor: COLORS.accent,
    backgroundColor: COLORS.accentLight,
    marginTop: 2,
  },
  updateProjectBtnText: {
    fontSize: 11,
    fontWeight: '700',
    color: COLORS.accent,
  },
});

