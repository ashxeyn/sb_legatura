// @ts-nocheck
import React, { useState } from 'react';
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
  date_to_finish: string;
  item_status?: string;
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
  const [showCompleteProjectModal, setShowCompleteProjectModal] = useState(false);
  const [completingProject, setCompletingProject] = useState(false);
  const [isProjectCompleted, setIsProjectCompleted] = useState(projectStatus === 'completed');

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
    Alert.alert(
      'Request Changes',
      'Are you sure you want to request changes to this milestone setup?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Request Changes',
          style: 'destructive',
          onPress: async () => {
            setRejectingMilestone(milestoneId);
            try {
              const response = await milestones_service.reject_milestone(milestoneId, userId);

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
            }
          },
        },
      ]
    );
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
        setPaymentHistory(response.data?.payments || []);
      } else {
        Alert.alert('Error', response.message || 'Failed to fetch payment history');
      }
    } catch (error) {
      console.error('Error fetching payment history:', error);
      Alert.alert('Error', 'Failed to fetch payment history');
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
    const remaining = totalEstimated - totalPaid;
    return { totalEstimated, totalPaid, remaining };
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

  // If a milestone detail is selected, show the detail view
  if (selectedMilestoneDetail) {
    return (
      <MilestoneDetail
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
          },
        }}
        navigation={{
          goBack: () => setSelectedMilestoneDetail(null),
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
          <Feather name="chevron-left" size={28} color={COLORS.text} />
        </TouchableOpacity>
        <TouchableOpacity style={styles.menuButton} onPress={() => setShowMenu(!showMenu)}>
          <Feather name="more-vertical" size={24} color={COLORS.text} />
        </TouchableOpacity>

        {/* Menu Dropdown */}
        {showMenu && (
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

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Project Info Section */}
        <View style={styles.projectSection}>
          <Text style={styles.projectTitle}>{projectTitle}</Text>
          {projectDescription && (
            <Text style={styles.projectDescription}>
              {projectDescription}
            </Text>
          )}

          {/* Budget */}
          <View style={styles.budgetRow}>
            <View style={styles.budgetIcon}>
              <Feather name="credit-card" size={16} color={COLORS.accent} />
            </View>
            <Text style={styles.budgetText}>{formatCurrency(totalCost)}</Text>
          </View>

          {/* Location */}
          {projectLocation && (
            <View style={styles.locationRow}>
              <Feather name="map-pin" size={16} color={COLORS.accent} />
              <Text style={styles.locationText}>{projectLocation}</Text>
            </View>
          )}
        </View>

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

            return (
              <TouchableOpacity
                key={item.item_id}
                style={styles.timelineItem}
                onPress={() => handleMilestonePress(item, milestoneNumber, cumulativePercentage)}
                activeOpacity={0.7}
              >
                {/* Left Content */}
                <View style={[styles.timelineSide, styles.timelineLeft]}>
                  {isLeft && (
                    <View style={styles.milestoneContent}>
                      <Text style={styles.milestoneLabel}>Milestone {milestoneNumber}</Text>
                      <Text style={styles.milestoneTitle}>{item.milestone_item_title}</Text>
                      <Text style={styles.milestoneCost}>{formatCurrency(item.milestone_item_cost || 0)}</Text>
                      <Text style={styles.milestonePercent}>{itemPercentage.toFixed(2)}%</Text>
                    </View>
                  )}
                </View>

                {/* Center - Circle and Line */}
                <View style={styles.timelineCenter}>
                  <View
                    style={[
                      styles.milestoneCircle,
                      (item.item_status === 'completed' || item.parentMilestoneStatus === 'completed')
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
                  {!isLast && <View style={styles.timelineLine} />}
                </View>

                {/* Right Content */}
                <View style={[styles.timelineSide, styles.timelineRight]}>
                  {!isLeft && (
                    <View style={styles.milestoneContent}>
                      <Text style={styles.milestoneLabel}>Milestone {milestoneNumber}</Text>
                      <Text style={styles.milestoneTitle}>{item.milestone_item_title}</Text>
                      <Text style={styles.milestoneCost}>{formatCurrency(item.milestone_item_cost || 0)}</Text>
                      <Text style={styles.milestonePercent}>{itemPercentage.toFixed(2)}%</Text>
                    </View>
                  )}
                </View>
              </TouchableOpacity>
            );
          })}

          {/* Start Point */}
          <View style={styles.timelineItem}>
            <View style={[styles.timelineSide, styles.timelineLeft]}>
              <View style={styles.startContent}>
                <Text style={styles.startLabel}>Start</Text>
                <Text style={styles.startPercent}>0%</Text>
              </View>
            </View>
            <View style={styles.timelineCenter}>
              <View style={styles.startCircle} />
            </View>
            <View style={[styles.timelineSide, styles.timelineRight]} />
          </View>
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
          userRole === 'owner' && allMilestoneItemsCompleted && (
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

      {/* Action Buttons - Fixed at Bottom */}
      {submittedMilestone && (
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
              <Text style={styles.modernHeaderTitle}>Payment history</Text>
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
                    <View style={styles.modernSummaryRow}>
                      <Text style={styles.modernSummaryLabel}>Total Amount Paid:</Text>
                      <Text style={[styles.modernSummaryValue, { color: '#10B981' }]}>
                        {formatCurrency(totals.totalPaid)}
                      </Text>
                    </View>
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
              {userRole === 'contractor' && selectedPayment.payment_status === 'submitted' && (
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
    paddingHorizontal: 8,
    paddingVertical: 8,
  },
  backButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  menuButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: 24,
  },

  // Project Section
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
    borderRadius: 4,
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
    borderRadius: 12,
    paddingVertical: 16,
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
    borderRadius: 12,
    paddingVertical: 16,
    alignItems: 'center',
    justifyContent: 'center',
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
    borderRadius: 16,
    padding: 20,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
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
    borderRadius: 16,
    padding: 20,
    shadowColor: COLORS.success,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 12,
    elevation: 6,
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
    borderRadius: 16,
    padding: 20,
    borderWidth: 2,
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
    borderRadius: 24,
    padding: 28,
    width: '100%',
    maxWidth: 400,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.25,
    shadowRadius: 20,
    elevation: 10,
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
    borderRadius: 12,
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
    paddingVertical: 16,
    borderRadius: 12,
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
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 1,
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
    borderRadius: 16,
    padding: 20,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 3,
  },
  modernSummaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
  },
  modernSummaryRowLast: {
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
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
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
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
    paddingHorizontal: 24,
    paddingVertical: 10,
    borderRadius: 20,
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
    borderRadius: 20,
    padding: 24,
    alignItems: 'center',
    marginBottom: 20,
    borderWidth: 2,
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
    borderRadius: 16,
    padding: 20,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
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
    borderRadius: 12,
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
    paddingVertical: 16,
    borderRadius: 12,
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
    top: 50,
    right: 8,
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    paddingVertical: 8,
    minWidth: 180,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 12,
    elevation: 8,
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
    borderRadius: 16,
    padding: 24,
    width: '100%',
    maxWidth: 500,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 16,
    elevation: 10,
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
    borderRadius: 12,
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
    borderRadius: 12,
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
});

