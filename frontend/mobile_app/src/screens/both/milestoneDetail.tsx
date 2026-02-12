// @ts-nocheck
import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Pressable,
  StyleSheet,
  ScrollView,
  StatusBar,
  Modal,
  ActivityIndicator,
  TextInput,
  Image,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';

import ProgressReportForm from './ProgressReportForm';
import ProgressReportDetail from './ProgressReportDetail';
import PaymentReceiptForm from './paymentReceiptForm';
import DisputeForm from './disputeForm';
import DisputeHistory from './disputeHistory';
import { progress_service } from '../../services/progress_service';
import { milestones_service } from '../../services/milestones_service';
import { payment_service } from '../../services/payment_service';
import { useEffect } from 'react';
import { Alert } from 'react-native';
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
};
interface MilestoneDetailProps {
  route: {
    params: {
      milestoneItem: MilestoneItem;
      milestoneNumber: number;
      cumulativePercentage: number;
      projectTitle: string;
      projectId: number;
      totalMilestones: number;
      isApproved: boolean;
      isCompleted?: boolean;
      userRole: 'owner' | 'contractor';
      userId: number;
      isPreviousItemComplete?: boolean;
    };
  };
  navigation: any;
}

export default function MilestoneDetail({ route, navigation }: MilestoneDetailProps) {
  const insets = useSafeAreaInsets();
  const {
    milestoneItem,
    milestoneNumber,
    cumulativePercentage,
    projectTitle,
    projectId,
    totalMilestones,
    isApproved,
    isCompleted,
    userRole,
    userId,
    isPreviousItemComplete = true,
  } = route.params;

  // Debug: log the milestone item to see its structure
  console.log('MilestoneDetail - milestoneItem:', JSON.stringify(milestoneItem));
  console.log('MilestoneDetail - userId:', userId, 'userRole:', userRole, 'projectId:', projectId);

  const [expandedReports, setExpandedReports] = useState<{ [key: number]: boolean }>({});
  const [showFullDetail, setShowFullDetail] = useState(false);
  const [showProgressForm, setShowProgressForm] = useState(false);
  const [showPaymentForm, setShowPaymentForm] = useState(false);
  const [selectedProgressReport, setSelectedProgressReport] = useState<any | null>(null);
  const [selectedProgressLoading, setSelectedProgressLoading] = useState(false);
  const [itemStatus, setItemStatus] = useState<string | undefined>(milestoneItem.item_status);
  const [showMenu, setShowMenu] = useState(false);
  const [showFullDetailMenu, setShowFullDetailMenu] = useState(false);
  const [showDisputeForm, setShowDisputeForm] = useState(false);
  const [showDisputeHistory, setShowDisputeHistory] = useState(false);

  const isContractor = userRole === 'contractor';
  const isOwner = userRole === 'owner';

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
  };


  // Real progress reports from backend
  const [progressReports, setProgressReports] = useState<any[]>([]);
  const [loadingReports, setLoadingReports] = useState(true);
  const [fetchError, setFetchError] = useState<string | null>(null);

  // Payments
  const [payments, setPayments] = useState<any[]>([]);
  const [loadingPayments, setLoadingPayments] = useState(true);
  const [expectedAmount, setExpectedAmount] = useState<number>(0);
  const [totalPaid, setTotalPaid] = useState<number>(0);
  const [totalSubmitted, setTotalSubmitted] = useState<number>(0);
  const [remainingBalance, setRemainingBalance] = useState<number>(0);
  const [overAmount, setOverAmount] = useState<number>(0);
  const [rejectReason, setRejectReason] = useState('');
  const [showRejectModal, setShowRejectModal] = useState(false);
  const [rejectingPaymentId, setRejectingPaymentId] = useState<number | null>(null);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  useEffect(() => {
    let isMounted = true;
    setLoadingReports(true);
    setFetchError(null);
    console.log('Fetching progress reports for item_id:', milestoneItem.item_id, 'userId:', userId);
    progress_service.get_progress_by_item(userId, milestoneItem.item_id)
      .then(res => {
        console.log('Progress API response:', JSON.stringify(res));
        if (isMounted) {
          // Handle error responses gracefully
          if (!res.success) {
            console.warn('Progress API returned unsuccessful response:', res.message);
            setProgressReports([]);
            setFetchError(null); // Don't show error for empty results
            return;
          }

          // Try multiple shapes: api_request wraps JSON as { success, message, data }
          // Backend may wrap data => { data: { progress_list } } or { progress_list }
          let progressList: any[] | null = null;

          // helper to unwrap php stdClass-wrapped items
          const normalizeItem = (it: any) => {
            if (it && typeof it === 'object') {
              const keys = Object.keys(it);
              if (keys.length === 1 && keys[0] === 'stdClass') return it.stdClass;
            }
            return it;
          };

          const tryArray = (candidate: any) => {
            if (Array.isArray(candidate)) return candidate.map(normalizeItem);
            return null;
          };

          // Common locations - handle deeply nested responses
          // Response structure: { success: true, data: { success: true, data: { progress_list: [...] } } }
          // Check the most deeply nested first
          if (res.data?.data?.progress_list) {
            progressList = tryArray(res.data.data.progress_list);
            console.log('Found progress_list at res.data.data.progress_list:', progressList?.length);
          }
          // Check one level up
          if (!progressList && res.data?.progress_list) {
            progressList = tryArray(res.data.progress_list);
            console.log('Found progress_list at res.data.progress_list:', progressList?.length);
          }
          // Check if data.data is the array itself
          if (!progressList && res.data?.data && Array.isArray(res.data.data)) {
            progressList = tryArray(res.data.data);
            console.log('Found progress_list at res.data.data (array):', progressList?.length);
          }
          // Sometimes backend returns data directly as an array
          if (!progressList && Array.isArray(res.data)) {
            progressList = tryArray(res.data);
            console.log('Found progress_list at res.data (array):', progressList?.length);
          }
          // Also check if the entire data object has progress_list at root
          if (!progressList && res.progress_list) {
            progressList = tryArray(res.progress_list);
            console.log('Found progress_list at res.progress_list:', progressList?.length);
          }

          if (progressList && progressList.length > 0) {
            console.log('Setting progress reports from normalized list:', progressList.length);
            console.log('Progress reports data:', JSON.stringify(progressList, null, 2));
            setProgressReports(progressList);
            setFetchError(null);
          } else {
            console.log('No progress reports found in response, clearing list');
            console.log('Response structure:', JSON.stringify(res, null, 2));
            setProgressReports([]);
            setFetchError(null); // Don't show error for empty results
          }
        }
      })
      .catch(err => {
        console.error('Progress fetch error:', err);
        if (isMounted) {
          setProgressReports([]);
          setFetchError(null); // Don't show error, just show empty state
        }
      })
      .finally(() => {
        if (isMounted) setLoadingReports(false);
      });
    return () => { isMounted = false; };
  }, [userId, milestoneItem.item_id]);

  // Helper to refresh payments list
  const refreshPayments = () => {
    setLoadingPayments(true);
    payment_service.get_payments_by_item(milestoneItem.item_id)
      .then(res => {
        if (res.success) {
          const paymentsData = res.data?.payments || res.data?.data?.payments || res.payments || [];
          setPayments(Array.isArray(paymentsData) ? paymentsData : []);
          // Extract payment summary for partial payments
          const data = res.data?.data || res.data || {};
          setExpectedAmount(parseFloat(data.expected_amount) || parseFloat(milestoneItem.milestone_item_cost) || 0);
          setTotalPaid(parseFloat(data.total_paid) || 0);
          setTotalSubmitted(parseFloat(data.total_submitted) || 0);
          setRemainingBalance(parseFloat(data.remaining_balance) || 0);
          setOverAmount(parseFloat(data.over_amount) || 0);
        } else {
          setPayments([]);
        }
      })
      .catch(err => {
        console.error('Payment fetch error:', err);
        setPayments([]);
      })
      .finally(() => setLoadingPayments(false));
  };

  // Contractor approve/reject handlers
  const handleApprovePayment = async (paymentId: number) => {
    Alert.alert(
      'Approve Payment Receipt',
      'Confirm that you have received this payment?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Approve',
          onPress: async () => {
            try {
              setActionLoading(paymentId);
              const response = await payment_service.approve_payment(paymentId, userId);
              if (response.success) {
                Alert.alert('Success', 'Payment receipt approved');
                refreshPayments();
              } else {
                Alert.alert('Error', response.message || 'Failed to approve receipt');
              }
            } catch (error) {
              Alert.alert('Error', 'An error occurred');
            } finally {
              setActionLoading(null);
            }
          },
        },
      ]
    );
  };

  const handleRejectPayment = (paymentId: number) => {
    setRejectingPaymentId(paymentId);
    setRejectReason('');
    setShowRejectModal(true);
  };

  const submitRejectPayment = async () => {
    if (!rejectReason.trim()) {
      Alert.alert('Error', 'Please provide a rejection reason');
      return;
    }
    if (rejectingPaymentId === null) return;
    try {
      setActionLoading(rejectingPaymentId);
      setShowRejectModal(false);
      const response = await payment_service.reject_payment(rejectingPaymentId, userId, rejectReason.trim());
      if (response.success) {
        Alert.alert('Success', 'Payment receipt rejected');
        refreshPayments();
      } else {
        Alert.alert('Error', response.message || 'Failed to reject receipt');
      }
    } catch (error) {
      Alert.alert('Error', 'An error occurred');
    } finally {
      setActionLoading(null);
      setRejectingPaymentId(null);
    }
  };

  // Fetch payments for this milestone item
  useEffect(() => {
    let isMounted = true;
    setLoadingPayments(true);

    console.log('Fetching payments for item_id:', milestoneItem.item_id);
    payment_service.get_payments_by_item(milestoneItem.item_id)
      .then(res => {
        console.log('Payment API response:', JSON.stringify(res));
        if (isMounted) {
          if (res.success) {
            // Handle different response structures
            const paymentsData = res.data?.payments || res.data?.data?.payments || res.payments || [];
            setPayments(Array.isArray(paymentsData) ? paymentsData : []);
            // Extract payment summary for partial payments
            const data = res.data?.data || res.data || {};
            setExpectedAmount(parseFloat(data.expected_amount) || parseFloat(milestoneItem.milestone_item_cost) || 0);
            setTotalPaid(parseFloat(data.total_paid) || 0);
            setTotalSubmitted(parseFloat(data.total_submitted) || 0);
            setRemainingBalance(parseFloat(data.remaining_balance) || 0);
            setOverAmount(parseFloat(data.over_amount) || 0);
          } else {
            console.warn('Payment API returned unsuccessful response:', res.message);
            setPayments([]);
          }
        }
      })
      .catch(err => {
        console.error('Payment fetch error:', err);
        if (isMounted) setPayments([]);
      })
      .finally(() => {
        if (isMounted) setLoadingPayments(false);
      });

    return () => { isMounted = false; };
  }, [milestoneItem.item_id]);

  const toggleReportExpand = (reportId: number) => {
    setExpandedReports(prev => ({
      ...prev,
      [reportId]: !prev[reportId]
    }));
  };

  const handleSendReport = () => {
    setShowMenu(false);
    setShowFullDetailMenu(false);
    
    // Get milestone_id from either milestone_id or parentMilestoneId
    const milestoneId = (milestoneItem as any).milestone_id || (milestoneItem as any).parentMilestoneId;
    
    // Validate required data before opening form
    if (!projectId || !milestoneId || !milestoneItem?.item_id) {
      console.error('Missing required data for dispute:', {
        projectId,
        milestoneId,
        itemId: milestoneItem?.item_id,
        milestoneItem,
      });
      Alert.alert(
        'Error',
        'Unable to file dispute: Missing required information. Please try again or contact support.'
      );
      return;
    }
    
    setShowDisputeForm(true);
  };

  const handleReportHistory = () => {
    setShowMenu(false);
    setShowFullDetailMenu(false);
    setShowDisputeHistory(true);
  };

  // Show milestone complete action when at least one progress report has been approved
  const hasAnyApproved = progressReports.some((p) => p.progress_status === 'approved');
  
  // Check if there's at least one approved payment receipt by contractor
  const hasApprovedPayment = payments.some((p) => p.payment_status === 'approved');
  
  // Allow partial payments: show button if owner, approved, has approved progress, and not completed.
  // Over-balance payments are allowed (frontend shows a warning modal).
  const shouldShowPaymentButton = isOwner && isApproved && progressReports.some(p => p.progress_status === 'approved') && itemStatus !== 'completed';
  console.log('Payment button visibility check:', {
    isOwner,
    isApproved,
    hasApprovedProgress: progressReports.some(p => p.progress_status === 'approved'),
    itemStatus,
    progressReportsCount: progressReports.length,
    progressStatuses: progressReports.map(p => p.progress_status),
    shouldShow: shouldShowPaymentButton,
    hasApprovedPayment,
    paymentsCount: payments.length,
    paymentStatuses: payments.map(p => p.payment_status),
    expectedAmount,
    totalPaid,
    totalSubmitted,
    remainingBalance,
  });

  // Get attachment from milestone item (from database)
  const hasAttachment = milestoneItem.attachment_path && milestoneItem.attachment_name;

  const getFileExtension = (filename: string) => {
    return filename.split('.').pop()?.toLowerCase() || '';
  };

  const getFileIcon = (filename: string) => {
    const ext = getFileExtension(filename);
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return 'image';
    if (['pdf'].includes(ext)) return 'file-text';
    if (['doc', 'docx'].includes(ext)) return 'file';
    return 'paperclip';
  };

  // Full Detail View
  if (showFullDetail) {
    return (
      <View style={[styles.container, { paddingTop: insets.top }]}>
        <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={() => setShowFullDetail(false)} style={styles.backButton}>
            <Feather name="chevron-left" size={28} color={COLORS.text} />
          </TouchableOpacity>
          <TouchableOpacity style={styles.menuButton} onPress={() => setShowFullDetailMenu(!showFullDetailMenu)}>
            <Feather name="more-vertical" size={24} color={COLORS.text} />
          </TouchableOpacity>
          
          {/* Menu Dropdown for Full Detail */}
          {showFullDetailMenu && (
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

        <ScrollView
          style={styles.scrollView}
          contentContainerStyle={styles.fullDetailScrollContent}
          showsVerticalScrollIndicator={false}
        >
          {/* Centered Milestone Title */}
          <Text style={styles.fullDetailTitle}>
            Milestone {milestoneNumber}: {milestoneItem.milestone_item_title}
          </Text>

          {/* Project Name in Orange */}
          <Text style={styles.fullDetailProjectName}>
            Project: {projectTitle}
          </Text>

          {/* Description */}
          <View style={styles.fullDetailDescriptionContainer}>
            <Text style={styles.fullDetailDescription}>
              {milestoneItem.milestone_item_description || 'No description provided for this milestone.'}
            </Text>
          </View>

          {/* Attachments Section */}
          <View style={styles.attachmentsSection}>
            <Text style={styles.attachmentsTitle}>Attachments</Text>

            {!hasAttachment ? (
              <View style={styles.noAttachmentsContainer}>
                <View style={styles.noAttachmentsIcon}>
                  <Feather name="paperclip" size={24} color={COLORS.textMuted} />
                </View>
                <Text style={styles.noAttachmentsText}>No attachments</Text>
              </View>
            ) : (
              <View style={styles.attachmentsList}>
                <TouchableOpacity style={styles.attachmentItem}>
                  <View style={styles.attachmentIcon}>
                    <Feather name={getFileIcon(milestoneItem.attachment_name || '')} size={20} color={COLORS.primary} />
                  </View>
                  <View style={styles.attachmentInfo}>
                    <Text style={styles.attachmentName} numberOfLines={1}>
                      {milestoneItem.attachment_name}
                    </Text>
                    <Text style={styles.attachmentSize}>
                      {getFileExtension(milestoneItem.attachment_name || '').toUpperCase()} file
                    </Text>
                  </View>
                  <Feather name="download" size={20} color={COLORS.textSecondary} />
                </TouchableOpacity>
              </View>
            )}
          </View>

          <View style={{ height: 40 }} />
        </ScrollView>
      </View>
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

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* Milestone Title and Description - Clickable */}
        <TouchableOpacity
          style={styles.milestoneHeader}
          onPress={() => setShowFullDetail(true)}
          activeOpacity={0.7}
        >
          <Text style={styles.milestoneTitle}>
            Milestone {milestoneNumber}: {milestoneItem.milestone_item_title}
          </Text>
          {milestoneItem.milestone_item_description && (
            <Text style={styles.milestoneDescription} numberOfLines={2}>
              {milestoneItem.milestone_item_description}
            </Text>
          )}
          <View style={styles.tapToViewMore}>
            <Text style={styles.tapToViewMoreText}>Tap to view full details</Text>
            <Feather name="chevron-right" size={16} color={COLORS.accent} />
          </View>
        </TouchableOpacity>

        {/* Divider */}
        <View style={styles.divider} />


        {/* Progress Reports Section */}
        <View style={styles.progressReportsSection}>
          {loadingReports ? (
            <View style={styles.noReportsContainer}>
              <View style={styles.noReportsIcon}>
                <Feather name="loader" size={32} color={COLORS.textMuted} />
              </View>
              <Text style={styles.noReportsTitle}>Loading Progress Reports...</Text>
            </View>
          ) : fetchError ? (
            <View style={styles.noReportsContainer}>
              <View style={styles.noReportsIcon}>
                <Feather name="alert-triangle" size={32} color={COLORS.error} />
              </View>
              <Text style={styles.noReportsTitle}>Error</Text>
              <Text style={styles.noReportsText}>{fetchError}</Text>
            </View>
          ) : progressReports.length === 0 ? null : (
            <View style={styles.reportsTimeline}>
              {progressReports.map((report, index) => {
                const isLast = index === progressReports.length - 1;
                return (
                  <Pressable
                    key={report.progress_id}
                    style={({ pressed }) => [styles.reportItem, pressed && styles.reportItemPressed]}
                    onPress={async () => {
                      // Owners already have the progress list (with files) from get_progress_by_item;
                      // only contractors should call the contractor-specific detail endpoint.
                      if (isContractor) {
                        try {
                          setSelectedProgressLoading(true);
                          const res = await progress_service.get_progress(userId, report.progress_id);
                          let prog = null;
                          if (res && res.data) {
                            prog = res.data?.data || res.data || null;
                          }
                          if (prog && prog.progress_id === undefined && prog.progress) {
                            prog = prog.progress;
                          }
                          if (!prog) prog = report;
                          setSelectedProgressReport(prog);
                        } catch (e) {
                          console.error('Failed to load progress details', e);
                          Alert.alert('Error', 'Failed to load progress details');
                          setSelectedProgressReport(report);
                        } finally {
                          setSelectedProgressLoading(false);
                        }
                      } else {
                        // Owner: use the already-fetched report object which includes files
                        setSelectedProgressReport(report);
                      }
                    }}
                  >
                    {/* Timeline indicator */}
                    <View style={styles.reportTimelineLeft}>
                      <View style={[
                        styles.reportDot,
                        report.progress_status === 'approved' && styles.reportDotCompleted,
                        report.progress_status === 'rejected' && styles.reportDotRejected
                      ]}>
                        {report.progress_status === 'approved' ? (
                          <Feather name="check" size={14} color={COLORS.surface} />
                        ) : report.progress_status === 'rejected' ? (
                          <Feather name="x" size={14} color={COLORS.surface} />
                        ) : null}
                      </View>
                      {!isLast && (() => {
                        const next = progressReports[index + 1];
                        const nextStatus = next?.progress_status;
                        const lineStyle = nextStatus === 'approved' 
                          ? styles.reportLineApproved 
                          : nextStatus === 'rejected' 
                          ? styles.reportLineRejected 
                          : styles.reportLinePending;
                        return <View style={[styles.reportLine, lineStyle]} />;
                      })()}
                    </View>

                    {/* Report Content */}
                    <View style={styles.reportContent}>
                      <Text style={styles.reportTitle}>{report.purpose || 'Progress Report'}</Text>
                      <Text
                        style={styles.reportDescription}
                        numberOfLines={3}
                      >
                        {report.purpose}
                      </Text>
                      <Text style={styles.reportDate}>{formatDate(report.submitted_at)}</Text>
                      {/* Small view details indicator (non-interactive now that the whole card is tappable) */}
                      <View style={styles.viewMoreButtonSmall}>
                        <Text style={styles.viewMoreTextSmall}>View details</Text>
                      </View>
                    </View>
                  </Pressable>
                );
              })}
            </View>
          )}
        </View>

        {/* Payments Section */}
        {payments.length > 0 && (
          <>
            {/* Divider */}
            <View style={styles.divider} />

            <View style={styles.paymentsSection}>
              <Text style={styles.sectionTitle}>Payment Receipts</Text>

              {/* Payment Balance Summary */}
              {expectedAmount > 0 && (
                <View style={styles.paymentBalanceSummary}>
                  <View style={styles.paymentBalanceRow}>
                    <Text style={styles.paymentBalanceLabel}>Expected</Text>
                    <Text style={styles.paymentBalanceValue}>
                      ₱{expectedAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                    </Text>
                  </View>
                  <View style={styles.paymentBalanceRow}>
                    <Text style={styles.paymentBalanceLabel}>Paid (Approved)</Text>
                    <Text style={[styles.paymentBalanceValue, { color: COLORS.success }]}>
                      ₱{totalPaid.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                    </Text>
                  </View>
                  {totalSubmitted > 0 && (
                    <View style={styles.paymentBalanceRow}>
                      <Text style={styles.paymentBalanceLabel}>Pending</Text>
                      <Text style={[styles.paymentBalanceValue, { color: COLORS.warning }]}>
                        ₱{totalSubmitted.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                      </Text>
                    </View>
                  )}
                  <View style={[styles.paymentBalanceRow, { borderTopWidth: 1, borderTopColor: COLORS.border, marginTop: 8, paddingTop: 8 }]}>
                    <Text style={[styles.paymentBalanceLabel, { fontWeight: '700' }]}>Remaining</Text>
                    <Text style={[styles.paymentBalanceValue, { fontWeight: '700', color: overAmount > 0 ? '#e74c3c' : remainingBalance > 0 ? COLORS.accent : COLORS.success }]}>
                      {overAmount > 0
                        ? `₱0.00 (₱${overAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })} over)`
                        : `₱${remainingBalance.toLocaleString('en-US', { minimumFractionDigits: 2 })}`}
                    </Text>
                  </View>
                  {/* Progress bar */}
                  <View style={styles.balanceProgressBg}>
                    <View style={[styles.balanceProgressFill, { width: `${Math.min(100, expectedAmount > 0 ? ((totalPaid + totalSubmitted) / expectedAmount) * 100 : 0)}%` }]} />
                  </View>
                </View>
              )}

              {payments.map((payment: any) => {
                const statusColor = payment.payment_status === 'approved' ? COLORS.success :
                                   payment.payment_status === 'rejected' ? COLORS.error :
                                   payment.payment_status === 'submitted' ? COLORS.warning :
                                   COLORS.textMuted;
                
                const statusBg = payment.payment_status === 'approved' ? COLORS.successLight :
                                payment.payment_status === 'rejected' ? COLORS.errorLight :
                                payment.payment_status === 'submitted' ? COLORS.warningLight :
                                COLORS.borderLight;

                return (
                  <View key={payment.payment_id} style={styles.paymentCard}>
                    {/* Payment Header */}
                    <View style={styles.paymentHeader}>
                      <View style={styles.paymentTitleRow}>
                        <Feather name="credit-card" size={20} color={COLORS.accent} />
                        <Text style={styles.paymentTitle}>Payment Receipt</Text>
                      </View>
                      <View style={[styles.paymentStatusBadge, { backgroundColor: statusBg }]}>
                        <Text style={[styles.paymentStatusText, { color: statusColor }]}>
                          {payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1)}
                        </Text>
                      </View>
                    </View>

                    {/* Payment Amount */}
                    <View style={styles.paymentAmountContainer}>
                      <Text style={styles.paymentAmountLabel}>Amount</Text>
                      <Text style={styles.paymentAmountValue}>
                        ₱{parseFloat(payment.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                      </Text>
                    </View>

                    {/* Payment Details */}
                    <View style={styles.paymentDetails}>
                      <View style={styles.paymentDetailRow}>
                        <Text style={styles.paymentDetailLabel}>Date:</Text>
                        <Text style={styles.paymentDetailValue}>{formatDate(payment.transaction_date)}</Text>
                      </View>
                      <View style={styles.paymentDetailRow}>
                        <Text style={styles.paymentDetailLabel}>Method:</Text>
                        <Text style={styles.paymentDetailValue}>
                          {payment.payment_type.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase())}
                        </Text>
                      </View>
                      {payment.transaction_number && (
                        <View style={styles.paymentDetailRow}>
                          <Text style={styles.paymentDetailLabel}>Reference #:</Text>
                          <Text style={styles.paymentDetailValue}>{payment.transaction_number}</Text>
                        </View>
                      )}
                    </View>

                    {/* Rejection Reason (if rejected) */}
                    {payment.payment_status === 'rejected' && payment.reason && (
                      <View style={styles.rejectionReasonContainer}>
                        <View style={styles.rejectionReasonHeader}>
                          <Feather name="alert-circle" size={18} color={COLORS.error} />
                          <Text style={styles.rejectionReasonTitle}>Decline Reason:</Text>
                        </View>
                        <Text style={styles.rejectionReasonText}>{payment.reason}</Text>
                      </View>
                    )}

                    {/* Receipt Photo */}
                    {payment.receipt_photo && (
                      <View style={styles.paymentReceiptContainer}>
                        <Text style={styles.paymentReceiptLabel}>Receipt Photo:</Text>
                        <Image
                          source={{ uri: `${api_config.base_url}/api/files/${payment.receipt_photo}` }}
                          style={styles.paymentReceiptImage}
                          resizeMode="contain"
                        />
                      </View>
                    )}

                    {/* Contractor: Approve/Reject buttons for submitted payments */}
                    {isContractor && payment.payment_status === 'submitted' && (
                      <View style={styles.paymentActionButtons}>
                        <TouchableOpacity
                          style={styles.paymentRejectBtn}
                          onPress={() => handleRejectPayment(payment.payment_id)}
                          disabled={actionLoading === payment.payment_id}
                        >
                          {actionLoading === payment.payment_id ? (
                            <ActivityIndicator size="small" color={COLORS.error} />
                          ) : (
                            <>
                              <Feather name="x" size={16} color={COLORS.error} />
                              <Text style={styles.paymentRejectBtnText}>Reject</Text>
                            </>
                          )}
                        </TouchableOpacity>
                        <TouchableOpacity
                          style={styles.paymentApproveBtn}
                          onPress={() => handleApprovePayment(payment.payment_id)}
                          disabled={actionLoading === payment.payment_id}
                        >
                          {actionLoading === payment.payment_id ? (
                            <ActivityIndicator size="small" color={COLORS.surface} />
                          ) : (
                            <>
                              <Feather name="check" size={16} color={COLORS.surface} />
                              <Text style={styles.paymentApproveBtnText}>Approve</Text>
                            </>
                          )}
                        </TouchableOpacity>
                      </View>
                    )}
                  </View>
                );
              })}
            </View>
          </>
        )}

        <View style={{ height: 100 }} />
      </ScrollView>

      {/* Unified Bottom Bar: stack multiple buttons to avoid overlap */}
      <View style={[styles.bottomBar, { paddingBottom: insets.bottom + 16 }]}>
        {/* Sequential lock banner — shown when previous milestone item is not yet completed */}
        {!isPreviousItemComplete && itemStatus !== 'completed' && (
          <View style={[styles.bottomRow, { marginBottom: 0 }]}>
            <View style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: COLORS.warningLight, borderRadius: 12, padding: 14, width: '100%' }}>
              <Feather name="lock" size={18} color={COLORS.warning} style={{ marginRight: 10 }} />
              <Text style={{ color: COLORS.warning, fontSize: 13, fontWeight: '600', flex: 1 }}>
                Complete the previous milestone item first to unlock this one.
              </Text>
            </View>
          </View>
        )}

        {/* Owner: Send payment (show if any approved) */}
        {shouldShowPaymentButton && isPreviousItemComplete && (
          <View style={styles.bottomRow}>
            <TouchableOpacity 
              style={styles.sendPaymentButton}
              onPress={() => setShowPaymentForm(true)}
            >
              <Feather name="credit-card" size={20} color={COLORS.surface} style={{ marginRight: 8 }} />
              <Text style={styles.sendPaymentButtonText}>Send payment receipt</Text>
            </TouchableOpacity>
          </View>
        )}

        {/* Owner: Set as Complete (appears when at least one progress report is approved AND at least one payment is approved by contractor) */}
        {isOwner && isApproved && hasAnyApproved && hasApprovedPayment && itemStatus !== 'completed' && isPreviousItemComplete && (
          <View style={styles.bottomRow}>
            <TouchableOpacity
              style={styles.completeButton}
              disabled={loadingReports}
              onPress={async () => {
                const itemId = milestoneItem.item_id;

                // Determine if there are any rejected or unapproved progress reports
                const hasRejected = progressReports.some(p => p.progress_status === 'rejected');
                const hasUnapproved = progressReports.some(p => (p.progress_status !== 'approved' && p.progress_status !== 'deleted'));

                const proceedWithCompletion = async () => {
                  try {
                    setLoadingReports(true);
                    const res = await milestones_service.complete_milestone_item(itemId, userId);
                    if (res.success) {
                      if (res.warning) {
                        Alert.alert('Completed with warning', res.warning, [{ text: 'OK' }]);
                      } else {
                        Alert.alert('Success', res.message || 'Milestone item marked as complete.');
                      }
                      setItemStatus('completed');
                    } else {
                      Alert.alert('Error', res.message || 'Failed to mark as complete.');
                    }
                  } catch (e) {
                    Alert.alert('Error', 'Unexpected error.');
                  } finally {
                    setLoadingReports(false);
                  }
                };

                // Choose confirmation message
                if (hasUnapproved || hasRejected) {
                  Alert.alert(
                    'Proceed with completion?',
                    'There are rejected or unapproved progress reports in this milestone, would you like to proceed?',
                    [
                      { text: 'Cancel', style: 'cancel' },
                      { text: 'Yes, proceed', onPress: proceedWithCompletion }
                    ]
                  );
                } else {
                  Alert.alert(
                    'Confirm',
                    'Are you sure you want to mark this milestone item as complete?',
                    [
                      { text: 'Cancel', style: 'cancel' },
                      { text: 'Yes, proceed', onPress: proceedWithCompletion }
                    ]
                  );
                }
              }}
            >
              {loadingReports ? (
                <ActivityIndicator size="small" color={COLORS.surface} />
              ) : (
                <Text style={styles.completeButtonText}>Set as Complete</Text>
              )}
            </TouchableOpacity>
          </View>
        )}

        {/* Owner: Completed badge */}
        {isOwner && isApproved && milestoneItem.item_status === 'completed' && (
          <View style={styles.bottomRow}>
            <View style={styles.completedBadge}>
              <Feather name="check-circle" size={20} color={COLORS.success} style={{ marginRight: 8 }} />
              <Text style={styles.completedBadgeText}>Milestone Item Complete</Text>
            </View>
          </View>
        )}

        {/* Contractor: Submit Progress Report */}
        {isContractor && isApproved && !isCompleted && itemStatus !== 'completed' && isPreviousItemComplete && (
          <View style={styles.bottomRow}>
            <TouchableOpacity
              style={styles.submitReportButton}
              onPress={() => setShowProgressForm(true)}
            >
              <Feather name="upload" size={20} color={COLORS.surface} style={{ marginRight: 8 }} />
              <Text style={styles.submitReportButtonText}>Submit Progress Report</Text>
            </TouchableOpacity>
          </View>
        )}
      </View>

      {/* Progress Report Form Modal */}
      <Modal
        visible={showProgressForm}
        animationType="slide"
        presentationStyle="fullScreen"
        onRequestClose={() => setShowProgressForm(false)}
      >
        <ProgressReportForm
          milestoneItemId={milestoneItem.item_id}
          milestoneTitle={`Milestone ${milestoneNumber}: ${milestoneItem.milestone_item_title}`}
          userId={userId}
          onClose={() => setShowProgressForm(false)}
          onSuccess={() => {
            // Refresh the progress reports or navigate back
            navigation.goBack();
          }}
        />
      </Modal>

      {/* Payment Receipt Form Modal */}
      <Modal
        visible={showPaymentForm}
        animationType="slide"
        presentationStyle="fullScreen"
        onRequestClose={() => setShowPaymentForm(false)}
      >
        <PaymentReceiptForm
          milestoneItemId={milestoneItem.item_id}
          projectId={projectId}
          milestoneTitle={`Milestone ${milestoneNumber}: ${milestoneItem.milestone_item_title}`}
          expectedAmount={expectedAmount}
          totalPaid={totalPaid}
          totalSubmitted={totalSubmitted}
          remainingBalance={remainingBalance}
          overAmount={overAmount}
          onClose={() => setShowPaymentForm(false)}
          onSuccess={() => {
            setShowPaymentForm(false);
            Alert.alert('Success', 'Payment receipt submitted successfully!');
            // Refresh payments list with updated summary
            refreshPayments();
          }}
        />
      </Modal>

      {/* Dispute Form Modal */}
      <Modal
        visible={showDisputeForm}
        animationType="slide"
        presentationStyle="fullScreen"
        onRequestClose={() => setShowDisputeForm(false)}
      >
        <DisputeForm
          projectId={projectId}
          projectTitle={projectTitle || 'Project'}
          milestoneId={(milestoneItem as any).milestone_id || (milestoneItem as any).parentMilestoneId}
          milestoneTitle={`Milestone ${milestoneNumber}`}
          milestoneItemId={milestoneItem.item_id}
          milestoneItemTitle={milestoneItem.milestone_item_title}
          onClose={() => setShowDisputeForm(false)}
          onSuccess={() => {
            setShowDisputeForm(false);
            Alert.alert('Success', 'Your dispute has been filed successfully');
          }}
        />
      </Modal>

      {/* Dispute History Modal */}
      <Modal
        visible={showDisputeHistory}
        animationType="slide"
        presentationStyle="fullScreen"
        onRequestClose={() => setShowDisputeHistory(false)}
      >
        <DisputeHistory onClose={() => setShowDisputeHistory(false)} />
      </Modal>

      {/* Approve/Reject actions are available inside the Progress Report Detail modal */}

      {/* Progress Report Detail Modal */}
      {selectedProgressReport && (
        <Modal
          visible={!!selectedProgressReport}
          animationType="slide"
          presentationStyle="fullScreen"
          onRequestClose={() => setSelectedProgressReport(null)}
        >
          <ProgressReportDetail
            progressReport={selectedProgressReport}
            milestoneTitle={`Milestone ${milestoneNumber}: ${milestoneItem.milestone_item_title}`}
            projectTitle={projectTitle}
            userRole={userRole}
            onClose={() => setSelectedProgressReport(null)}
          />
        </Modal>
      )}

      {/* Reject Payment Reason Modal */}
      <Modal
        visible={showRejectModal}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setShowRejectModal(false)}
      >
        <View style={styles.rejectModalOverlay}>
          <View style={styles.rejectModalContent}>
            <Text style={styles.rejectModalTitle}>Reject Payment</Text>
            <Text style={styles.rejectModalSubtitle}>Please provide a reason for rejection:</Text>
            <TextInput
              style={styles.rejectModalInput}
              value={rejectReason}
              onChangeText={setRejectReason}
              placeholder="Enter rejection reason..."
              placeholderTextColor={COLORS.textMuted}
              multiline
              numberOfLines={3}
              textAlignVertical="top"
            />
            <View style={styles.rejectModalActions}>
              <TouchableOpacity
                style={styles.rejectModalCancelBtn}
                onPress={() => { setShowRejectModal(false); setRejectingPaymentId(null); }}
              >
                <Text style={styles.rejectModalCancelText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.rejectModalConfirmBtn}
                onPress={submitRejectPayment}
              >
                <Text style={styles.rejectModalConfirmText}>Reject</Text>
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
    paddingHorizontal: 8,
    paddingVertical: 12,
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

  // Milestone Header
  milestoneHeader: {
    marginBottom: 24,
  },
  milestoneTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
    lineHeight: 30,
  },
  milestoneDescription: {
    fontSize: 15,
    color: COLORS.textSecondary,
    lineHeight: 24,
  },

  // Divider
  divider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginBottom: 24,
  },

  // Progress Reports Section
  progressReportsSection: {
    flex: 1,
  },

  // No Reports State
  noReportsContainer: {
    alignItems: 'center',
    paddingVertical: 48,
    paddingHorizontal: 24,
  },
  noReportsIcon: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: COLORS.borderLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  noReportsTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  noReportsText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 22,
  },

  // Reports Timeline
  reportsTimeline: {
    paddingLeft: 4,
  },
  reportItem: {
    flexDirection: 'row',
    paddingTop: 0,
    paddingHorizontal: 8,
    borderRadius: 8,
    marginBottom: 0,
    paddingBottom: 0,
  },
  reportItemPressed: {
    backgroundColor: COLORS.borderLight,
    marginBottom: 0,
    paddingBottom: 0,
  },
  reportTimelineLeft: {
    alignItems: 'center',
    marginRight: 16,
    width: 28,
  },
  reportDot: {
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: COLORS.borderLight,
    borderWidth: 2,
    borderColor: COLORS.border,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 1,
  },
  reportDotCompleted: {
    backgroundColor: COLORS.success,
    borderColor: COLORS.success,
  },
  reportDotRejected: {
    backgroundColor: COLORS.error,
    borderColor: COLORS.error,
  },
  reportLine: {
    width: 3,
    flex: 1,
    // slight overlap so the line visually connects between dots
    marginTop: -6,
    marginBottom: -6,
  },
  reportLineApproved: {
    backgroundColor: COLORS.success,
  },
  reportLineRejected: {
    backgroundColor: COLORS.error,
  },
  reportLinePending: {
    backgroundColor: COLORS.border,
  },
  reportContent: {
    flex: 1,
    paddingBottom: 0,
  },
  reportTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  reportDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 22,
    marginBottom: 8,
  },
  reportDate: {
    fontSize: 13,
    color: COLORS.textMuted,
    marginBottom: 8,
  },
  viewMoreButton: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-end',
    gap: 4,
  },
  viewMoreText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.accent,
  },
  viewMoreButtonSmall: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-end',
    gap: 4,
    marginLeft: 12,
  },
  viewMoreTextSmall: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.accent,
  },
  reportContentTouchable: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    paddingBottom: 28,
  },

  // Bottom Button
  bottomButtonContainer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: COLORS.surface,
    paddingHorizontal: 24,
    paddingTop: 16,
  },
  bottomBar: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: COLORS.surface,
    paddingHorizontal: 24,
    paddingTop: 12,
    gap: 12,
  },
  bottomRow: {
    marginBottom: 8,
  },
  sendPaymentButton: {
    backgroundColor: COLORS.accent,
    borderRadius: 12,
    paddingVertical: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  sendPaymentButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.surface,
  },
  completeButton: {
    backgroundColor: COLORS.success,
    borderRadius: 12,
    paddingVertical: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  completeButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.surface,
  },
  completedBadge: {
    backgroundColor: COLORS.successLight,
    borderRadius: 12,
    paddingVertical: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  completedBadgeText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.success,
  },
  submitReportButton: {
    backgroundColor: COLORS.accent,
    borderRadius: 12,
    paddingVertical: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  submitReportButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.surface,
  },

  // Tap to view more
  tapToViewMore: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 12,
  },
  tapToViewMoreText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.accent,
    marginRight: 4,
  },

  // Full Detail View Styles
  fullDetailScrollContent: {
    paddingHorizontal: 24,
    paddingTop: 20,
  },
  fullDetailTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.text,
    textAlign: 'center',
    marginBottom: 8,
    lineHeight: 32,
  },
  fullDetailProjectName: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.accent,
    textAlign: 'center',
    marginBottom: 24,
  },
  fullDetailDescriptionContainer: {
    backgroundColor: COLORS.borderLight,
    borderRadius: 12,
    padding: 16,
    marginBottom: 32,
  },
  fullDetailDescription: {
    fontSize: 15,
    color: COLORS.textSecondary,
    lineHeight: 24,
  },

  // Attachments Section
  attachmentsSection: {
    marginBottom: 24,
  },
  attachmentsTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 16,
  },
  noAttachmentsContainer: {
    alignItems: 'center',
    paddingVertical: 32,
    backgroundColor: COLORS.borderLight,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderStyle: 'dashed',
  },
  noAttachmentsIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  noAttachmentsText: {
    fontSize: 14,
    color: COLORS.textMuted,
  },
  attachmentsList: {
    gap: 12,
  },
  attachmentItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  attachmentIcon: {
    width: 40,
    height: 40,
    borderRadius: 8,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  attachmentInfo: {
    flex: 1,
  },
  attachmentName: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 2,
  },
  attachmentSize: {
    fontSize: 12,
    color: COLORS.textMuted,
  },

  // Full Detail Bottom Buttons
  fullDetailBottomContainer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: COLORS.surface,
    paddingHorizontal: 24,
    paddingTop: 16,
    flexDirection: 'row',
    gap: 12,
  },
  rejectButton: {
    flex: 1,
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    paddingVertical: 16,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: COLORS.error,
  },
  rejectButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.error,
  },
  approveButton: {
    flex: 1,
    backgroundColor: COLORS.success,
    borderRadius: 12,
    paddingVertical: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  approveButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.surface,
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

  // Payments Section
  paymentsSection: {
    marginTop: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 16,
  },
  paymentCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  paymentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  paymentTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  paymentTitle: {
    fontSize: 16,
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
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  paymentAmountContainer: {
    backgroundColor: COLORS.accentLight,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    alignItems: 'center',
  },
  paymentAmountLabel: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginBottom: 4,
  },
  paymentAmountValue: {
    fontSize: 28,
    fontWeight: '700',
    color: COLORS.accent,
  },
  paymentDetails: {
    backgroundColor: COLORS.borderLight,
    borderRadius: 12,
    padding: 12,
    marginBottom: 12,
  },
  paymentDetailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 6,
  },
  paymentDetailLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },
  paymentDetailValue: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },
  rejectionReasonContainer: {
    backgroundColor: COLORS.errorLight,
    borderRadius: 12,
    padding: 12,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.error,
  },
  rejectionReasonHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  rejectionReasonTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.error,
  },
  rejectionReasonText: {
    fontSize: 14,
    color: COLORS.error,
    lineHeight: 20,
  },
  paymentReceiptContainer: {
    marginTop: 4,
  },
  paymentReceiptLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  paymentReceiptImage: {
    width: '100%',
    height: 200,
    borderRadius: 12,
    backgroundColor: COLORS.borderLight,
  },

  // Payment Balance Summary
  paymentBalanceSummary: {
    backgroundColor: COLORS.primaryLight,
    borderRadius: 12,
    padding: 14,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  paymentBalanceRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 4,
  },
  paymentBalanceLabel: {
    fontSize: 13,
    color: COLORS.textSecondary,
  },
  paymentBalanceValue: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
  },
  balanceProgressBg: {
    height: 5,
    backgroundColor: COLORS.border,
    borderRadius: 3,
    marginTop: 10,
    overflow: 'hidden',
  },
  balanceProgressFill: {
    height: '100%',
    backgroundColor: COLORS.success,
    borderRadius: 3,
  },

  // Contractor payment action buttons
  paymentActionButtons: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 12,
  },
  paymentRejectBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    borderRadius: 10,
    paddingVertical: 12,
    borderWidth: 2,
    borderColor: COLORS.error,
    backgroundColor: COLORS.surface,
  },
  paymentRejectBtnText: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.error,
  },
  paymentApproveBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    borderRadius: 10,
    paddingVertical: 12,
    backgroundColor: COLORS.success,
  },
  paymentApproveBtnText: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.surface,
  },

  // Reject modal
  rejectModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  rejectModalContent: {
    backgroundColor: COLORS.surface,
    borderRadius: 20,
    padding: 24,
    width: '100%',
    maxWidth: 400,
  },
  rejectModalTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  rejectModalSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginBottom: 16,
  },
  rejectModalInput: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 12,
    padding: 14,
    fontSize: 15,
    color: COLORS.text,
    backgroundColor: COLORS.borderLight,
    minHeight: 80,
    marginBottom: 20,
  },
  rejectModalActions: {
    flexDirection: 'row',
    gap: 12,
  },
  rejectModalCancelBtn: {
    flex: 1,
    borderRadius: 12,
    paddingVertical: 14,
    alignItems: 'center',
    borderWidth: 2,
    borderColor: COLORS.border,
  },
  rejectModalCancelText: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.textSecondary,
  },
  rejectModalConfirmBtn: {
    flex: 1,
    borderRadius: 12,
    paddingVertical: 14,
    alignItems: 'center',
    backgroundColor: COLORS.error,
  },
  rejectModalConfirmText: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.surface,
  },
});
