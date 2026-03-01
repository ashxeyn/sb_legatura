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
  Platform,
} from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';

import ProgressReportForm from './progressReportForm';
import ProgressReportDetail from './progressReportDetail';
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
      projectStatus?: string;
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
    projectStatus,
  } = route.params;

  const isProjectHalted = projectStatus === 'halt' || projectStatus === 'on_hold' || projectStatus === 'halted';

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
  const [originalCost, setOriginalCost] = useState<number>(0);
  const [adjustedCost, setAdjustedCost] = useState<number | null>(null);
  const [carryForwardAmount, setCarryForwardAmount] = useState<number>(0);
  const [totalPaid, setTotalPaid] = useState<number>(0);
  const [totalSubmitted, setTotalSubmitted] = useState<number>(0);
  const [remainingBalance, setRemainingBalance] = useState<number>(0);
  const [overAmount, setOverAmount] = useState<number>(0);
  const [rejectReason, setRejectReason] = useState('');
  const [showRejectModal, setShowRejectModal] = useState(false);
  const [rejectingPaymentId, setRejectingPaymentId] = useState<number | null>(null);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  // Settlement due date & derived payment status
  const [derivedPaymentStatus, setDerivedPaymentStatus] = useState<string>('Unpaid');
  const [settlementDueDate, setSettlementDueDate] = useState<string | null>(null);
  const [extensionDate, setExtensionDate] = useState<string | null>(null);
  const [showDueDateModal, setShowDueDateModal] = useState(false);
  const [dueDateInput, setDueDateInput] = useState('');
  const [extensionInput, setExtensionInput] = useState('');
  const [savingDueDate, setSavingDueDate] = useState(false);
  const [showDuePicker, setShowDuePicker] = useState(false);
  const [showExtPicker, setShowExtPicker] = useState(false);
  const [dueDateObj, setDueDateObj] = useState<Date>(new Date());
  const [extDateObj, setExtDateObj] = useState<Date>(new Date());

  // Work deadline extension history
  const [dateHistories, setDateHistories] = useState<any[]>([]);
  const [showDateHistory, setShowDateHistory] = useState(false);
  const wasExtended = milestoneItem.was_extended || false;
  const extensionCount = milestoneItem.extension_count || 0;
  const originalDateToFinish = milestoneItem.original_date_to_finish || null;

  // Full Detail expandable sections
  const [fdExpandedSections, setFdExpandedSections] = useState<Record<string, boolean>>({
    financial: false,
    dueDate: false,
    payments: false,
    description: false,
    attachments: false,
  });
  const toggleFdSection = (key: string) => {
    setFdExpandedSections(prev => ({ ...prev, [key]: !prev[key] }));
  };

  // Full Detail tab navigation
  const [fdActiveTab, setFdActiveTab] = useState<'info' | 'payments'>('info');

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
          setOriginalCost(parseFloat(data.original_cost) || parseFloat(milestoneItem.milestone_item_cost) || 0);
          setAdjustedCost(data.adjusted_cost != null ? parseFloat(data.adjusted_cost) : null);
          setCarryForwardAmount(parseFloat(data.carry_forward_amount) || 0);
          setTotalPaid(parseFloat(data.total_paid) || 0);
          setTotalSubmitted(parseFloat(data.total_submitted) || 0);
          setRemainingBalance(parseFloat(data.remaining_balance) || 0);
          setOverAmount(parseFloat(data.over_amount) || 0);
          // Derived payment status & settlement dates
          if (data.derived_payment_status) setDerivedPaymentStatus(data.derived_payment_status);
          if (data.settlement_due_date !== undefined) setSettlementDueDate(data.settlement_due_date);
          if (data.extension_date !== undefined) setExtensionDate(data.extension_date);
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
    // Reset all payment state before fetching to prevent stale data from previous item
    setPayments([]);
    setExpectedAmount(0);
    setOriginalCost(0);
    setAdjustedCost(null);
    setCarryForwardAmount(0);
    setTotalPaid(0);
    setTotalSubmitted(0);
    setRemainingBalance(0);
    setOverAmount(0);
    setDerivedPaymentStatus('Unpaid');
    setSettlementDueDate(null);
    setExtensionDate(null);
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
            setOriginalCost(parseFloat(data.original_cost) || parseFloat(milestoneItem.milestone_item_cost) || 0);
            setAdjustedCost(data.adjusted_cost != null ? parseFloat(data.adjusted_cost) : null);
            setCarryForwardAmount(parseFloat(data.carry_forward_amount) || 0);
            setTotalPaid(parseFloat(data.total_paid) || 0);
            setTotalSubmitted(parseFloat(data.total_submitted) || 0);
            setRemainingBalance(parseFloat(data.remaining_balance) || 0);
            setOverAmount(parseFloat(data.over_amount) || 0);
            // Derived payment status & settlement dates
            if (data.derived_payment_status) setDerivedPaymentStatus(data.derived_payment_status);
            if (data.settlement_due_date !== undefined) setSettlementDueDate(data.settlement_due_date);
            if (data.extension_date !== undefined) setExtensionDate(data.extension_date);
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

  // Fetch date extension history when item was extended
  useEffect(() => {
    if (!wasExtended) return;
    milestones_service.get_date_history(milestoneItem.item_id)
      .then(res => {
        if (res.success && res.histories) {
          setDateHistories(res.histories);
        }
      })
      .catch(err => console.error('Date history fetch error:', err));
  }, [milestoneItem.item_id, wasExtended]);

  const toggleReportExpand = (reportId: number) => {
    setExpandedReports(prev => ({
      ...prev,
      [reportId]: !prev[reportId]
    }));
  };

  // Date picker handlers
  const formatDateStr = (d: Date) => {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
  };

  const openDueDateModal = () => {
    if (settlementDueDate) {
      const parsed = new Date(settlementDueDate + 'T00:00:00');
      setDueDateObj(isNaN(parsed.getTime()) ? new Date() : parsed);
      setDueDateInput(settlementDueDate);
    } else {
      setDueDateObj(new Date());
      setDueDateInput('');
    }
    if (extensionDate) {
      const parsed = new Date(extensionDate + 'T00:00:00');
      setExtDateObj(isNaN(parsed.getTime()) ? new Date() : parsed);
      setExtensionInput(extensionDate);
    } else {
      setExtDateObj(new Date());
      setExtensionInput('');
    }
    setShowDueDateModal(true);
  };

  const onDueDateChange = (_event: any, selected?: Date) => {
    setShowDuePicker(Platform.OS === 'ios');
    if (selected) {
      setDueDateObj(selected);
      setDueDateInput(formatDateStr(selected));
    }
  };

  const onExtDateChange = (_event: any, selected?: Date) => {
    setShowExtPicker(Platform.OS === 'ios');
    if (selected) {
      setExtDateObj(selected);
      setExtensionInput(formatDateStr(selected));
    }
  };

  // Handler for saving settlement due date
  const handleSaveDueDate = async () => {
    if (!dueDateInput) {
      Alert.alert('Error', 'Please select a due date.');
      return;
    }
    setSavingDueDate(true);
    try {
      const res = await milestones_service.set_settlement_due_date(
        milestoneItem.item_id,
        userId,
        dueDateInput,
        extensionInput || null,
        isOwner ? 'owner' : 'contractor'
      );
      if (res.success) {
        Alert.alert('Success', 'Settlement due date has been set.');
        setSettlementDueDate(dueDateInput);
        if (extensionInput) setExtensionDate(extensionInput);
        setShowDueDateModal(false);
        setShowDuePicker(false);
        setShowExtPicker(false);
        refreshPayments(); // Refresh to get updated derived status
      } else {
        Alert.alert('Error', res.message || 'Failed to set due date.');
      }
    } catch (err) {
      Alert.alert('Error', 'An error occurred while saving.');
    } finally {
      setSavingDueDate(false);
    }
  };

  // Helper to get badge color for derived payment status
  const getPaymentStatusColor = (status: string) => {
    switch (status) {
      case 'Fully Paid': return '#22c55e';
      case 'Partially Paid': return '#f59e0b';
      case 'Overdue': return '#ef4444';
      case 'Unpaid': default: return '#94a3b8';
    }
  };

  const getStatusBadgeColor = (status: string) => {
    switch (status) {
      case 'completed': return '#22c55e';
      case 'in_progress': return '#3b82f6';
      case 'halt': return '#ef4444';
      case 'not_started': default: return '#94a3b8';
    }
  };

  // Helper: compute urgency info for the settlement due date
  const getDueDateUrgency = (dueDateStr: string | null, extDateStr: string | null) => {
    const effectiveDateStr = extDateStr || dueDateStr;
    if (!effectiveDateStr) return null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const dueDate = new Date(effectiveDateStr + 'T00:00:00');
    const diffMs = dueDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
    if (diffDays < 0) return { label: `${Math.abs(diffDays)} day${Math.abs(diffDays) !== 1 ? 's' : ''} overdue`, color: '#dc2626', bg: '#fef2f2', icon: 'alert-circle' as const, urgent: true };
    if (diffDays === 0) return { label: 'Due today', color: '#dc2626', bg: '#fef2f2', icon: 'alert-circle' as const, urgent: true };
    if (diffDays <= 1) return { label: 'Due tomorrow', color: '#ea580c', bg: '#fff7ed', icon: 'alert-triangle' as const, urgent: true };
    if (diffDays <= 3) return { label: `${diffDays} days left`, color: '#ea580c', bg: '#fff7ed', icon: 'alert-triangle' as const, urgent: true };
    if (diffDays <= 7) return { label: `${diffDays} days left`, color: '#d97706', bg: '#fffbeb', icon: 'clock' as const, urgent: false };
    return { label: `${diffDays} days left`, color: '#16a34a', bg: '#f0fdf4', icon: 'clock' as const, urgent: false };
  };

  const dueDateUrgency = getDueDateUrgency(settlementDueDate, extensionDate);

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

  // Get attachments from item_files (new multi-file system)
  const itemFiles: Array<{ file_id: number; item_id: number; file_path: string }> = milestoneItem.files || [];
  const hasAttachment = itemFiles.length > 0;

  // ── Reusable Due Date Modal ──
  const renderDueDateModal = () => (
    <Modal
      visible={showDueDateModal}
      transparent={true}
      animationType="slide"
      onRequestClose={() => { setShowDueDateModal(false); setShowDuePicker(false); setShowExtPicker(false); }}
    >
      <View style={styles.ddmOverlay}>
        <View style={styles.ddmSheet}>
          {/* Handle bar */}
          <View style={styles.ddmHandle} />

          {/* Header */}
          <View style={styles.ddmHeader}>
            <View style={styles.ddmHeaderIcon}>
              <Feather name="calendar" size={20} color={COLORS.accent} />
            </View>
            <View style={{ flex: 1 }}>
              <Text style={styles.ddmTitle}>
                {settlementDueDate ? 'Update Due Date' : 'Set Payment Due Date'}
              </Text>
              <Text style={styles.ddmSubtitle}>
                Both parties will be notified as the deadline approaches
              </Text>
            </View>
            <TouchableOpacity
              onPress={() => { setShowDueDateModal(false); setShowDuePicker(false); setShowExtPicker(false); }}
              style={styles.ddmCloseBtn}
            >
              <Feather name="x" size={20} color={COLORS.textSecondary} />
            </TouchableOpacity>
          </View>

          {/* Due Date Field */}
          <View style={styles.ddmFieldGroup}>
            <Text style={styles.ddmFieldLabel}>Due Date *</Text>
            <TouchableOpacity
              style={[styles.ddmDateField, showDuePicker && styles.ddmDateFieldActive]}
              onPress={() => { setShowDuePicker(!showDuePicker); setShowExtPicker(false); }}
              activeOpacity={0.7}
            >
              <Feather name="calendar" size={16} color={dueDateInput ? COLORS.text : COLORS.textMuted} />
              <Text style={[styles.ddmDateFieldText, !dueDateInput && { color: COLORS.textMuted }]}>  
                {dueDateInput
                  ? new Date(dueDateInput + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' })
                  : 'Tap to select a date'}
              </Text>
              <Feather name={showDuePicker ? 'chevron-up' : 'chevron-down'} size={16} color={COLORS.textMuted} />
            </TouchableOpacity>
            {showDuePicker && (
              <View style={styles.ddmPickerWrap}>
                <DateTimePicker
                  value={dueDateObj}
                  mode="date"
                  display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                  minimumDate={new Date()}
                  onChange={onDueDateChange}
                  style={{ width: '100%' }}
                />
              </View>
            )}
          </View>

          {/* Extension Date Field */}
          <View style={styles.ddmFieldGroup}>
            <Text style={styles.ddmFieldLabel}>Extension Date <Text style={{ color: COLORS.textMuted, fontWeight: '400' }}>(optional)</Text></Text>
            <TouchableOpacity
              style={[styles.ddmDateField, showExtPicker && styles.ddmDateFieldActive]}
              onPress={() => { setShowExtPicker(!showExtPicker); setShowDuePicker(false); }}
              activeOpacity={0.7}
            >
              <Feather name="arrow-right" size={16} color={extensionInput ? COLORS.warning : COLORS.textMuted} />
              <Text style={[styles.ddmDateFieldText, !extensionInput && { color: COLORS.textMuted }]}>
                {extensionInput
                  ? new Date(extensionInput + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' })
                  : 'No extension date'}
              </Text>
              {extensionInput ? (
                <TouchableOpacity onPress={() => { setExtensionInput(''); }} hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}>
                  <Feather name="x-circle" size={16} color={COLORS.textMuted} />
                </TouchableOpacity>
              ) : (
                <Feather name={showExtPicker ? 'chevron-up' : 'chevron-down'} size={16} color={COLORS.textMuted} />
              )}
            </TouchableOpacity>
            {showExtPicker && (
              <View style={styles.ddmPickerWrap}>
                <DateTimePicker
                  value={extDateObj}
                  mode="date"
                  display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                  minimumDate={dueDateInput ? new Date(dueDateInput + 'T00:00:00') : new Date()}
                  onChange={onExtDateChange}
                  style={{ width: '100%' }}
                />
              </View>
            )}
          </View>

          {/* Actions */}
          <View style={styles.ddmActions}>
            <TouchableOpacity
              style={styles.ddmCancelBtn}
              onPress={() => { setShowDueDateModal(false); setShowDuePicker(false); setShowExtPicker(false); }}
            >
              <Text style={styles.ddmCancelText}>Cancel</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.ddmSaveBtn, !dueDateInput && { opacity: 0.5 }]}
              onPress={handleSaveDueDate}
              disabled={savingDueDate || !dueDateInput}
            >
              {savingDueDate ? (
                <ActivityIndicator size="small" color="#fff" />
              ) : (
                <>
                  <Feather name="check" size={16} color="#fff" />
                  <Text style={styles.ddmSaveText}>{settlementDueDate ? 'Update' : 'Save'}</Text>
                </>
              )}
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );

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
              <TouchableOpacity style={styles.menuItem} onPress={() => {
                setShowFullDetailMenu(false);
                if (navigation.onShowSummary) {
                  navigation.onShowSummary(milestoneItem.item_id);
                }
              }}>
                <Feather name="bar-chart-2" size={18} color={COLORS.info} />
                <Text style={[styles.menuItemText, { color: COLORS.info }]}>View Summary</Text>
              </TouchableOpacity>
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

        {/* ── Tab Bar ── */}
        <View style={styles.fdTabBar}>
          <TouchableOpacity
            style={[styles.fdTab, fdActiveTab === 'info' && styles.fdTabActive]}
            onPress={() => setFdActiveTab('info')}
            activeOpacity={0.7}
          >
            <Feather name="info" size={15} color={fdActiveTab === 'info' ? COLORS.accent : COLORS.textMuted} />
            <Text style={[styles.fdTabText, fdActiveTab === 'info' && styles.fdTabTextActive]}>Milestone Info</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.fdTab, fdActiveTab === 'payments' && styles.fdTabActive]}
            onPress={() => setFdActiveTab('payments')}
            activeOpacity={0.7}
          >
            <Feather name="credit-card" size={15} color={fdActiveTab === 'payments' ? COLORS.accent : COLORS.textMuted} />
            <Text style={[styles.fdTabText, fdActiveTab === 'payments' && styles.fdTabTextActive]}>Payments</Text>
            {payments.filter(p => p.payment_status === 'submitted').length > 0 && (
              <View style={styles.fdTabBadge}>
                <Text style={styles.fdTabBadgeText}>{payments.filter(p => p.payment_status === 'submitted').length}</Text>
              </View>
            )}
          </TouchableOpacity>
        </View>

        <ScrollView
          style={styles.scrollView}
          contentContainerStyle={styles.fullDetailScrollContent}
          showsVerticalScrollIndicator={false}
        >
          {/* ════════════ TAB: MILESTONE INFO ════════════ */}
          {fdActiveTab === 'info' && (
            <>
              {/* Title + Status Header */}
              <View style={styles.fdInfoCard}>
                <Text style={styles.fdInfoLabel}>MILESTONE ITEM {milestoneNumber}</Text>
                <Text style={styles.fdInfoTitle}>{milestoneItem.milestone_item_title}</Text>
                <View style={styles.fdInfoMeta}>
                  <View style={[styles.fdInfoBadge, { backgroundColor: getStatusBadgeColor(itemStatus) + '15' }]}>
                    <View style={[styles.fdInfoBadgeDot, { backgroundColor: getStatusBadgeColor(itemStatus) }]} />
                    <Text style={[styles.fdInfoBadgeText, { color: getStatusBadgeColor(itemStatus) }]}>
                      {itemStatus === 'not_started' ? 'Not Started' : itemStatus === 'in_progress' ? 'In Progress' : itemStatus === 'completed' ? 'Completed' : itemStatus === 'halt' ? 'Halted' : itemStatus}
                    </Text>
                  </View>
                  <Text style={styles.fdInfoProject}>{projectTitle}</Text>
                </View>

                {/* Description — inline with card */}
                {milestoneItem.milestone_item_description ? (
                  <View style={styles.fdInfoDescSection}>
                    <Text style={styles.fdInfoDescLabel}>Description</Text>
                    <Text style={styles.fdInfoDescText}>
                      {milestoneItem.milestone_item_description}
                    </Text>
                  </View>
                ) : (
                  <View style={styles.fdInfoDescSection}>
                    <Text style={[styles.fdInfoDescText, { color: COLORS.textMuted, fontStyle: 'italic' }]}>
                      No description provided.
                    </Text>
                  </View>
                )}

                {/* Attachments — inline with card */}
                <View style={styles.fdInfoAttachSection}>
                  <Text style={styles.fdInfoDescLabel}>Attachments</Text>
                  {!hasAttachment ? (
                    <View style={styles.fdInfoNoAttach}>
                      <Feather name="paperclip" size={16} color={COLORS.textMuted} />
                      <Text style={{ fontSize: 13, color: COLORS.textMuted }}>No attachments</Text>
                    </View>
                  ) : (
                    itemFiles.map((file) => {
                      const fileName = file.file_path.split('/').pop() || file.file_path;
                      const fileUrl = `${api_config.base_url}/api/files/${file.file_path}`;
                      return (
                        <TouchableOpacity
                          key={file.file_id}
                          style={styles.fdInfoAttachItem}
                          activeOpacity={0.7}
                          onPress={() => { const { Linking } = require('react-native'); Linking.openURL(fileUrl); }}
                        >
                          <View style={styles.fdInfoAttachIcon}>
                            <Feather name={getFileIcon(fileName)} size={18} color={COLORS.accent} />
                          </View>
                          <View style={{ flex: 1 }}>
                            <Text style={styles.fdInfoAttachName} numberOfLines={1}>
                              {fileName}
                            </Text>
                            <Text style={styles.fdInfoAttachType}>
                              {getFileExtension(fileName).toUpperCase() || 'FILE'}
                            </Text>
                          </View>
                          <Feather name="external-link" size={18} color={COLORS.textSecondary} />
                        </TouchableOpacity>
                      );
                    })
                  )}
                </View>
              </View>

              {/* Quick financial overview card */}
              <TouchableOpacity
                style={styles.fdQuickFinCard}
                activeOpacity={0.7}
                onPress={() => setFdActiveTab('payments')}
              >
                <View style={styles.fdQuickFinRow}>
                  <View style={{ flex: 1 }}>
                    <Text style={styles.fdQuickFinLabel}>Payment Progress</Text>
                    <Text style={styles.fdQuickFinValue}>
                      ₱{totalPaid.toLocaleString('en-US', { minimumFractionDigits: 0 })} of ₱{expectedAmount.toLocaleString('en-US', { minimumFractionDigits: 0 })}
                    </Text>
                  </View>
                  <View style={{ backgroundColor: getPaymentStatusColor(derivedPaymentStatus) + '18', borderRadius: 4, paddingHorizontal: 8, paddingVertical: 3 }}>
                    <Text style={{ fontSize: 11, fontWeight: '700', color: getPaymentStatusColor(derivedPaymentStatus) }}>{derivedPaymentStatus}</Text>
                  </View>
                </View>
                <View style={styles.fdQuickFinProgressBg}>
                  <View style={[styles.fdQuickFinProgressFill, { width: `${Math.min(100, expectedAmount > 0 ? ((totalPaid + totalSubmitted) / expectedAmount) * 100 : 0)}%` }]} />
                </View>
                <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 4, marginTop: 8 }}>
                  <Text style={{ fontSize: 12, color: COLORS.accent, fontWeight: '600' }}>View payment details</Text>
                  <Feather name="arrow-right" size={14} color={COLORS.accent} />
                </View>
              </TouchableOpacity>
            </>
          )}

          {/* ════════════ TAB: PAYMENTS ════════════ */}
          {fdActiveTab === 'payments' && (
            <>
              {/* ─── Financial Summary (Expandable) ─── */}
              <TouchableOpacity
                style={styles.fdAccordion}
                activeOpacity={0.7}
                onPress={() => toggleFdSection('financial')}
              >
                <View style={styles.fdAccordionHeader}>
                  <View style={styles.fdAccordionLeft}>
                    <View style={[styles.fdAccordionIcon, { backgroundColor: COLORS.accent + '15' }]}>
                      <Feather name="dollar-sign" size={16} color={COLORS.accent} />
                    </View>
                    <View>
                      <Text style={styles.fdAccordionTitle}>Financial Summary</Text>
                      <Text style={styles.fdAccordionSubtitle}>
                        ₱{totalPaid.toLocaleString('en-US', { minimumFractionDigits: 0 })} / ₱{expectedAmount.toLocaleString('en-US', { minimumFractionDigits: 0 })} paid
                      </Text>
                    </View>
                  </View>
                  <View style={{ alignItems: 'flex-end', gap: 4 }}>
                    <View style={{ backgroundColor: getPaymentStatusColor(derivedPaymentStatus) + '18', borderRadius: 4, paddingHorizontal: 8, paddingVertical: 2 }}>
                      <Text style={{ fontSize: 10, fontWeight: '700', color: getPaymentStatusColor(derivedPaymentStatus) }}>{derivedPaymentStatus}</Text>
                    </View>
                    <Feather name={fdExpandedSections.financial ? 'chevron-up' : 'chevron-down'} size={18} color={COLORS.textMuted} />
                  </View>
                </View>

                {/* Slim progress bar */}
                <View style={styles.fdAccordionProgressBg}>
                  <View style={[styles.fdAccordionProgressFill, { width: `${Math.min(100, expectedAmount > 0 ? ((totalPaid + totalSubmitted) / expectedAmount) * 100 : 0)}%` }]} />
                </View>

                {fdExpandedSections.financial && (
                  <View style={styles.fdAccordionBody}>
                    {adjustedCost !== null && carryForwardAmount > 0 ? (
                      <>
                        <View style={styles.fdFinRow}>
                          <Text style={styles.fdFinLabel}>Original Cost</Text>
                          <Text style={[styles.fdFinValue, { color: COLORS.textSecondary }]}>
                            ₱{originalCost.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                          </Text>
                        </View>
                        <View style={styles.fdFinRow}>
                          <Text style={[styles.fdFinLabel, { color: '#dc2626' }]}>Carry-forward</Text>
                          <Text style={[styles.fdFinValue, { color: '#dc2626', fontWeight: '700' }]}>
                            +₱{carryForwardAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                          </Text>
                        </View>
                        <View style={[styles.fdFinRow, { borderTopWidth: 1, borderTopColor: COLORS.border, marginTop: 6, paddingTop: 6 }]}>
                          <Text style={[styles.fdFinLabel, { fontWeight: '700' }]}>Adjusted Total</Text>
                          <Text style={[styles.fdFinValue, { fontWeight: '700' }]}>
                            ₱{adjustedCost.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                          </Text>
                        </View>
                      </>
                    ) : (
                      <View style={styles.fdFinRow}>
                        <Text style={styles.fdFinLabel}>Expected Amount</Text>
                        <Text style={styles.fdFinValue}>
                          ₱{expectedAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                        </Text>
                      </View>
                    )}
                    <View style={styles.fdFinRow}>
                      <Text style={styles.fdFinLabel}>Paid (Approved)</Text>
                      <Text style={[styles.fdFinValue, { color: COLORS.success }]}>
                        ₱{totalPaid.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                      </Text>
                    </View>
                    {totalSubmitted > 0 && (
                      <View style={styles.fdFinRow}>
                        <Text style={styles.fdFinLabel}>Pending Review</Text>
                        <Text style={[styles.fdFinValue, { color: COLORS.warning }]}>
                          ₱{totalSubmitted.toLocaleString('en-US', { minimumFractionDigits: 2 })}
                        </Text>
                      </View>
                    )}
                    <View style={[styles.fdFinRow, { borderTopWidth: 1, borderTopColor: COLORS.border, marginTop: 8, paddingTop: 8 }]}>
                      <Text style={[styles.fdFinLabel, { fontWeight: '700', fontSize: 14 }]}>Remaining Balance</Text>
                      <Text style={[styles.fdFinValue, { fontWeight: '700', fontSize: 15, color: overAmount > 0 ? '#e74c3c' : remainingBalance > 0 ? COLORS.accent : COLORS.success }]}>
                        {overAmount > 0
                          ? `₱${overAmount.toLocaleString('en-US', { minimumFractionDigits: 2 })} over`
                          : `₱${remainingBalance.toLocaleString('en-US', { minimumFractionDigits: 2 })}`}
                      </Text>
                    </View>
                  </View>
                )}
              </TouchableOpacity>

              {/* ─── Work Deadline Card ─── */}
              <View style={styles.fdDueDateCard}>
                <View style={styles.fdDueDateHeader}>
                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, flex: 1 }}>
                    <View style={[styles.fdAccordionIcon, { backgroundColor: wasExtended ? COLORS.warning + '15' : COLORS.accent + '15' }]}>
                      <Feather name="clock" size={16} color={wasExtended ? COLORS.warning : COLORS.accent} />
                    </View>
                    <View style={{ flex: 1 }}>
                      <Text style={{ fontSize: 14, fontWeight: '600', color: COLORS.text }}>Work Deadline</Text>
                      <Text style={{ fontSize: 13, color: COLORS.text, fontWeight: '500', marginTop: 3 }}>
                        {new Date(milestoneItem.date_to_finish).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}
                      </Text>
                      {wasExtended && originalDateToFinish && (
                        <View style={{ marginTop: 4 }}>
                          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                            <Feather name="arrow-right" size={11} color={COLORS.warning} />
                            <Text style={{ fontSize: 11, color: COLORS.textMuted }}>
                              Originally: {new Date(originalDateToFinish).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                            </Text>
                          </View>
                          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 2 }}>
                            <View style={{ backgroundColor: COLORS.warningLight, borderRadius: 4, paddingHorizontal: 6, paddingVertical: 1 }}>
                              <Text style={{ fontSize: 9, fontWeight: '700', color: COLORS.warning }}>
                                Extended{extensionCount > 1 ? ` ${extensionCount}×` : ''}
                              </Text>
                            </View>
                          </View>
                        </View>
                      )}
                    </View>
                  </View>
                  {wasExtended && dateHistories.length > 0 && (
                    <TouchableOpacity
                      style={{ paddingHorizontal: 8, paddingVertical: 4 }}
                      onPress={() => setShowDateHistory(!showDateHistory)}
                    >
                      <Feather name={showDateHistory ? 'chevron-up' : 'chevron-down'} size={16} color={COLORS.textMuted} />
                    </TouchableOpacity>
                  )}
                </View>
                {/* Expandable date history */}
                {showDateHistory && dateHistories.length > 0 && (
                  <View style={{ paddingHorizontal: 16, paddingBottom: 12, borderTopWidth: 1, borderTopColor: COLORS.border, marginTop: 8, paddingTop: 10 }}>
                    <Text style={{ fontSize: 12, fontWeight: '600', color: COLORS.textSecondary, marginBottom: 8 }}>Date History</Text>
                    {dateHistories.map((h: any, i: number) => (
                      <View key={h.id} style={{ flexDirection: 'row', marginBottom: i < dateHistories.length - 1 ? 8 : 0 }}>
                        <View style={{ width: 10, alignItems: 'center', marginRight: 8 }}>
                          <View style={{ width: 8, height: 8, borderRadius: 4, backgroundColor: COLORS.warning, marginTop: 4 }} />
                          {i < dateHistories.length - 1 && (
                            <View style={{ width: 1, flex: 1, backgroundColor: COLORS.border, marginTop: 2 }} />
                          )}
                        </View>
                        <View style={{ flex: 1 }}>
                          <Text style={{ fontSize: 11, color: COLORS.text, fontWeight: '500' }}>
                            {new Date(h.previous_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                            {' → '}
                            {new Date(h.new_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                          </Text>
                          <Text style={{ fontSize: 10, color: COLORS.textMuted, marginTop: 1 }}>
                            {h.change_reason} • {new Date(h.changed_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                          </Text>
                          {h.changed_by_name && (
                            <Text style={{ fontSize: 10, color: COLORS.textMuted }}>
                              Approved by {h.changed_by_name}
                            </Text>
                          )}
                        </View>
                      </View>
                    ))}
                  </View>
                )}
              </View>

              {/* ─── Due Date Card ─── */}
              <View style={styles.fdDueDateCard}>
                <View style={styles.fdDueDateHeader}>
                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, flex: 1 }}>
                    <View style={[styles.fdAccordionIcon, { backgroundColor: (dueDateUrgency?.color || COLORS.accent) + '15' }]}>
                      <Feather name="calendar" size={16} color={dueDateUrgency?.color || COLORS.accent} />
                    </View>
                    <View style={{ flex: 1 }}>
                      <Text style={{ fontSize: 14, fontWeight: '600', color: COLORS.text }}>Payment Due Date</Text>
                      {settlementDueDate ? (
                        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 3 }}>
                          <Text style={{ fontSize: 13, color: COLORS.text, fontWeight: '500' }}>
                            {new Date(settlementDueDate + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}
                          </Text>
                          {dueDateUrgency && (
                            <View style={{ backgroundColor: dueDateUrgency.color + '18', borderRadius: 4, paddingHorizontal: 6, paddingVertical: 1 }}>
                              <Text style={{ fontSize: 9, fontWeight: '700', color: dueDateUrgency.color }}>{dueDateUrgency.label}</Text>
                            </View>
                          )}
                        </View>
                      ) : (
                        <Text style={{ fontSize: 12, color: COLORS.textMuted, marginTop: 2 }}>Not set yet</Text>
                      )}
                      {extensionDate && (
                        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 4 }}>
                          <Feather name="arrow-right" size={11} color={COLORS.warning} />
                          <Text style={{ fontSize: 11, color: COLORS.warning, fontWeight: '500' }}>
                            Extended to {new Date(extensionDate + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                          </Text>
                        </View>
                      )}
                    </View>
                  </View>
                  {(isContractor || isOwner) && derivedPaymentStatus !== 'Fully Paid' && (
                    <TouchableOpacity
                      style={styles.fdDueDateBtn}
                      onPress={openDueDateModal}
                    >
                      <Feather name={settlementDueDate ? 'edit-3' : 'plus'} size={13} color={COLORS.accent} />
                      <Text style={{ fontSize: 12, fontWeight: '600', color: COLORS.accent }}>
                        {settlementDueDate ? 'Edit' : 'Set Date'}
                      </Text>
                    </TouchableOpacity>
                  )}
                </View>
              </View>

              {/* ─── Payment History ─── */}
              <View style={styles.fdPaymentSection}>
                <View style={styles.fdPaymentSectionHeader}>
                  <Text style={styles.fdPaymentSectionTitle}>Payment History</Text>
                  {payments.length > 0 && (
                    <View style={styles.fdTabBadge}>
                      <Text style={styles.fdTabBadgeText}>{payments.length}</Text>
                    </View>
                  )}
                </View>

                {payments.length === 0 ? (
                  <View style={styles.fdPaymentEmpty}>
                    <Feather name="inbox" size={32} color={COLORS.textMuted} />
                    <Text style={{ fontSize: 14, color: COLORS.textMuted, marginTop: 8 }}>No payment receipts yet</Text>
                    <Text style={{ fontSize: 12, color: COLORS.textMuted, marginTop: 2 }}>Payments will appear here once submitted</Text>
                  </View>
                ) : (
                  payments.map((payment: any) => {
                    const statusColor = payment.payment_status === 'approved' ? COLORS.success :
                                       payment.payment_status === 'rejected' ? COLORS.error :
                                       payment.payment_status === 'submitted' ? COLORS.warning :
                                       COLORS.textMuted;
                    
                    const statusBg = payment.payment_status === 'approved' ? COLORS.successLight :
                                    payment.payment_status === 'rejected' ? COLORS.errorLight :
                                    payment.payment_status === 'submitted' ? COLORS.warningLight :
                                    COLORS.borderLight;

                    return (
                      <View key={payment.payment_id} style={styles.fdPaymentCard}>
                        <View style={styles.fdPaymentHeader}>
                          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8 }}>
                            <Text style={styles.fdPaymentAmount}>
                              ₱{parseFloat(payment.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </Text>
                            <View style={[styles.fdPaymentStatusBadge, { backgroundColor: statusBg }]}>
                              <Text style={[styles.fdPaymentStatusText, { color: statusColor }]}>
                                {payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1)}
                              </Text>
                            </View>
                          </View>
                          <Text style={styles.fdPaymentDate}>{formatDate(payment.transaction_date)}</Text>
                        </View>

                        <View style={styles.fdPaymentMeta}>
                          <Text style={styles.fdPaymentMetaText}>
                            {payment.payment_type.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase())}
                          </Text>
                          {payment.transaction_number && (
                            <>
                              <Text style={styles.fdPaymentMetaDot}>·</Text>
                              <Text style={styles.fdPaymentMetaText}>Ref: {payment.transaction_number}</Text>
                            </>
                          )}
                        </View>

                        {payment.payment_status === 'rejected' && payment.reason && (
                          <View style={styles.fdPaymentRejection}>
                            <Feather name="alert-circle" size={14} color={COLORS.error} />
                            <Text style={styles.fdPaymentRejectionText}>{payment.reason}</Text>
                          </View>
                        )}

                        {payment.receipt_photo && (
                          <Image
                            source={{ uri: `${api_config.base_url}/api/files/${payment.receipt_photo}` }}
                            style={styles.fdPaymentReceipt}
                            resizeMode="contain"
                          />
                        )}

                        {!isProjectHalted && isContractor && payment.payment_status === 'submitted' && (
                          <View style={styles.fdPaymentActions}>
                            <TouchableOpacity
                              style={styles.fdPaymentRejectBtn}
                              onPress={() => handleRejectPayment(payment.payment_id)}
                              disabled={actionLoading === payment.payment_id}
                            >
                              {actionLoading === payment.payment_id ? (
                                <ActivityIndicator size="small" color={COLORS.error} />
                              ) : (
                                <>
                                  <Feather name="x" size={15} color={COLORS.error} />
                                  <Text style={{ fontSize: 13, fontWeight: '600', color: COLORS.error }}>Reject</Text>
                                </>
                              )}
                            </TouchableOpacity>
                            <TouchableOpacity
                              style={styles.fdPaymentApproveBtn}
                              onPress={() => handleApprovePayment(payment.payment_id)}
                              disabled={actionLoading === payment.payment_id}
                            >
                              {actionLoading === payment.payment_id ? (
                                <ActivityIndicator size="small" color={COLORS.surface} />
                              ) : (
                                <>
                                  <Feather name="check" size={15} color={COLORS.surface} />
                                  <Text style={{ fontSize: 13, fontWeight: '600', color: COLORS.surface }}>Approve</Text>
                                </>
                              )}
                            </TouchableOpacity>
                          </View>
                        )}
                      </View>
                    );
                  })
                )}

                {/* Owner: Send payment button */}
                {!isProjectHalted && shouldShowPaymentButton && isPreviousItemComplete && (
                  <TouchableOpacity 
                    style={styles.fdSendPaymentBtn}
                    onPress={() => setShowPaymentForm(true)}
                  >
                    <Feather name="plus" size={18} color={COLORS.accent} />
                    <Text style={styles.fdSendPaymentBtnText}>Send new payment receipt</Text>
                  </TouchableOpacity>
                )}
              </View>
            </>
          )}

          <View style={{ height: 40 }} />
        </ScrollView>

        {renderDueDateModal()}
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
            {(isContractor || isOwner) && derivedPaymentStatus !== 'Fully Paid' && (
              <TouchableOpacity style={styles.menuItem} onPress={() => {
                setShowMenu(false);
                openDueDateModal();
              }}>
                <Feather name="calendar" size={18} color={COLORS.text} />
                <Text style={styles.menuItemText}>{settlementDueDate ? 'Edit Due Date' : 'Set Due Date'}</Text>
              </TouchableOpacity>
            )}
            <TouchableOpacity style={styles.menuItem} onPress={() => {
              setShowMenu(false);
              setShowFullDetail(true);
            }}>
              <Feather name="credit-card" size={18} color={COLORS.text} />
              <Text style={styles.menuItemText}>Payment History</Text>
            </TouchableOpacity>
          </View>
        )}
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* ─── Title Card ─── */}
        <View style={styles.titleCard}>
          {/* Row 1: Title + Status */}
          <View style={styles.titleCardHeader}>
            <View style={{ flex: 1, marginRight: 10 }}>
              <Text style={styles.titleCardLabel}>MILESTONE ITEM {milestoneNumber}</Text>
              <Text style={styles.titleCardName} numberOfLines={2}>{milestoneItem.milestone_item_title}</Text>
            </View>
            <View style={[styles.titleCardBadge, { backgroundColor: getStatusBadgeColor(itemStatus) + '15' }]}>
              <View style={[styles.titleCardBadgeDot, { backgroundColor: getStatusBadgeColor(itemStatus) }]} />
              <Text style={[styles.titleCardBadgeText, { color: getStatusBadgeColor(itemStatus) }]}>
                {itemStatus === 'not_started' ? 'Not Started' : itemStatus === 'in_progress' ? 'In Progress' : itemStatus === 'completed' ? 'Completed' : itemStatus === 'halt' ? 'Halted' : itemStatus}
              </Text>
            </View>
          </View>

          {/* Row 2: Financial Grid — 3 columns */}
          <View style={styles.finGrid}>
            <View style={styles.finGridItem}>
              <Text style={styles.finGridLabel}>REQUIRED</Text>
              {adjustedCost !== null && carryForwardAmount > 0 ? (
                <>
                  <Text style={[styles.finGridValue, { color: '#dc2626' }]} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
                    ₱{adjustedCost.toLocaleString('en-US', { minimumFractionDigits: 0 })}
                  </Text>
                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 2 }}>
                    <Text style={{ fontSize: 10, color: COLORS.textMuted, textDecorationLine: 'line-through' }}>
                      ₱{originalCost.toLocaleString('en-US', { minimumFractionDigits: 0 })}
                    </Text>
                    <View style={styles.carryForwardBadge}>
                      <Text style={styles.carryForwardBadgeText}>+CF</Text>
                    </View>
                  </View>
                </>
              ) : (
                <Text style={styles.finGridValue} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
                  ₱{expectedAmount.toLocaleString('en-US', { minimumFractionDigits: 0 })}
                </Text>
              )}
            </View>
            <View style={styles.finGridDivider} />
            <View style={styles.finGridItem}>
              <Text style={styles.finGridLabel}>PAID</Text>
              <Text style={[styles.finGridValue, { color: COLORS.success }]} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
                ₱{totalPaid.toLocaleString('en-US', { minimumFractionDigits: 0 })}
              </Text>
            </View>
            <View style={styles.finGridDivider} />
            <View style={styles.finGridItem}>
              <Text style={styles.finGridLabel}>REMAINING</Text>
              <Text style={[styles.finGridValue, { color: overAmount > 0 ? '#dc2626' : remainingBalance > 0 ? COLORS.accent : COLORS.success }]} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
                {overAmount > 0 ? `+₱${overAmount.toLocaleString('en-US', { minimumFractionDigits: 0 })}` : `₱${remainingBalance.toLocaleString('en-US', { minimumFractionDigits: 0 })}`}
              </Text>
              {overAmount > 0 && <Text style={{ fontSize: 9, color: '#dc2626', fontWeight: '700', marginTop: 1 }}>OVER BUDGET</Text>}
            </View>
          </View>

          {/* Progress bar */}
          <View style={styles.titleCardProgressBg}>
            <View style={[styles.titleCardProgressFill, { width: `${Math.min(100, expectedAmount > 0 ? (totalPaid / expectedAmount) * 100 : 0)}%` }]} />
          </View>

          {/* Row 3: Payment Status + Due Date */}
          <View style={styles.titleCardFooter}>
            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
              <View style={[styles.titleCardBadgeDot, { backgroundColor: getPaymentStatusColor(derivedPaymentStatus), width: 7, height: 7 }]} />
              <Text style={{ fontSize: 12, fontWeight: '700', color: getPaymentStatusColor(derivedPaymentStatus) }}>
                {derivedPaymentStatus}
              </Text>
            </View>
            {settlementDueDate ? (
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                <Feather name={dueDateUrgency?.icon || 'calendar'} size={12} color={dueDateUrgency?.color || COLORS.textMuted} />
                <Text style={{ fontSize: 11, color: COLORS.textSecondary }}>
                  Due {new Date(settlementDueDate + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                </Text>
                {dueDateUrgency && (
                  <View style={{ backgroundColor: dueDateUrgency.color + '15', borderRadius: 3, paddingHorizontal: 5, paddingVertical: 1 }}>
                    <Text style={{ fontSize: 9, fontWeight: '700', color: dueDateUrgency.color }}>{dueDateUrgency.label}</Text>
                  </View>
                )}
              </View>
            ) : (
              <Text style={{ fontSize: 11, color: COLORS.textMuted }}>No due date</Text>
            )}
          </View>

          {/* View Full Details link */}
          <TouchableOpacity
            style={styles.titleCardDetailsBtn}
            onPress={() => setShowFullDetail(true)}
            activeOpacity={0.7}
          >
            <Feather name="file-text" size={13} color={COLORS.accent} />
            <Text style={{ fontSize: 12, fontWeight: '600', color: COLORS.accent }}>View full details & payment history</Text>
            <Feather name="chevron-right" size={13} color={COLORS.accent} />
          </TouchableOpacity>
        </View>

        {/* Status Alert Banners */}
        {milestoneItem.item_status === 'halt' && (
          <View style={styles.alertBanner}>
            <Feather name="pause-circle" size={18} color={COLORS.error} />
            <Text style={styles.alertBannerText}>This milestone item is currently halted</Text>
          </View>
        )}

        {(() => {
          // A rejected report is only an active issue if the latest report is still rejected.
          // Once a new report is submitted or approved, the rejection is resolved.
          const sorted = [...progressReports].sort((a, b) => new Date(b.submitted_at).getTime() - new Date(a.submitted_at).getTime());
          const latestReport = sorted[0];
          if (latestReport && latestReport.progress_status === 'rejected') {
            return (
              <View style={[styles.alertBanner, { backgroundColor: COLORS.errorLight, borderColor: '#FECACA' }]}>
                <Feather name="alert-circle" size={18} color={COLORS.error} />
                <Text style={[styles.alertBannerText, { color: '#991B1B' }]}>
                  Latest progress report rejected — submit a new one
                </Text>
              </View>
            );
          }
          return null;
        })()}

        {(() => {
          // A rejected payment is only an issue if the latest payment is still rejected.
          const sortedPayments = [...payments].sort((a, b) => new Date(b.transaction_date).getTime() - new Date(a.transaction_date).getTime());
          const latestPayment = sortedPayments[0];
          if (latestPayment && latestPayment.payment_status === 'rejected') {
            return (
              <View style={[styles.alertBanner, { backgroundColor: COLORS.errorLight, borderColor: '#FECACA' }]}>
                <Feather name="alert-circle" size={18} color={COLORS.error} />
                <Text style={[styles.alertBannerText, { color: '#991B1B' }]}>
                  Latest payment rejected
                </Text>
              </View>
            );
          }
          return null;
        })()}

        {(() => {
          const pendingReports = progressReports.filter(r => r.progress_status === 'submitted');
          const pendingPayments = payments.filter(p => p.payment_status === 'submitted');
          if (pendingReports.length > 0 || pendingPayments.length > 0) {
            const parts: string[] = [];
            if (pendingReports.length > 0) parts.push(`${pendingReports.length} new report${pendingReports.length > 1 ? 's' : ''}`);
            if (pendingPayments.length > 0) parts.push(`${pendingPayments.length} new payment${pendingPayments.length > 1 ? 's' : ''}`);
            return (
              <View style={[styles.alertBanner, { backgroundColor: '#DBEAFE', borderColor: '#93C5FD' }]}>
                <Feather name="info" size={18} color={COLORS.info} />
                <Text style={[styles.alertBannerText, { color: '#1E40AF' }]}>
                  {parts.join(' and ')} pending review
                </Text>
              </View>
            );
          }
          return null;
        })()}

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
        {!isProjectHalted && shouldShowPaymentButton && isPreviousItemComplete && (
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
        {!isProjectHalted && isOwner && isApproved && hasAnyApproved && hasApprovedPayment && itemStatus !== 'completed' && isPreviousItemComplete && (
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
                      // Build message with carry-forward info if applicable
                      let msg = res.message || 'Milestone item marked as complete.';
                      const cf = res.carry_forward || res.data?.carry_forward;
                      if (cf && cf.shortfall && cf.carried_to_item_id) {
                        const shortfallStr = parseFloat(cf.shortfall).toLocaleString('en-US', { minimumFractionDigits: 2 });
                        msg += `\n\nUnderpayment of ₱${shortfallStr} has been carried forward to the next milestone item${cf.carried_to_title ? ' ("' + cf.carried_to_title + '")' : ''}.`;
                      } else if (cf && cf.shortfall && !cf.carried_to_item_id) {
                        const shortfallStr = parseFloat(cf.shortfall).toLocaleString('en-US', { minimumFractionDigits: 2 });
                        msg += `\n\nNote: ₱${shortfallStr} shortfall recorded. This is the last item — no next item to carry to.`;
                      }
                      if (res.warning) {
                        Alert.alert('Completed with warning', res.warning + (cf ? '\n' + msg : ''), [{ text: 'OK' }]);
                      } else {
                        Alert.alert('Success', msg);
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
        {!isProjectHalted && isContractor && isApproved && !isCompleted && itemStatus !== 'completed' && isPreviousItemComplete && (
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
          originalCost={originalCost}
          adjustedCost={adjustedCost}
          carryForwardAmount={carryForwardAmount}
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
            projectStatus={projectStatus}
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

      {/* Settlement Due Date Modal */}
      {renderDueDateModal()}
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
    borderRadius: 4,
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
    borderRadius: 8,
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
    borderRadius: 8,
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
    borderRadius: 8,
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
    borderRadius: 8,
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
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 20,
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
    borderRadius: 4,
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
    borderRadius: 4,
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
    borderRadius: 4,
    padding: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  attachmentIcon: {
    width: 40,
    height: 40,
    borderRadius: 6,
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
    borderRadius: 8,
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
    borderRadius: 8,
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
    borderRadius: 6,
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
    borderRadius: 4,
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
    borderRadius: 4,
  },
  paymentStatusText: {
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  paymentAmountContainer: {
    backgroundColor: COLORS.accentLight,
    borderRadius: 4,
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
    borderRadius: 4,
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
    borderRadius: 4,
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
    borderRadius: 4,
    backgroundColor: COLORS.borderLight,
  },

  // Payment Balance Summary
  paymentBalanceSummary: {
    backgroundColor: COLORS.primaryLight,
    borderRadius: 4,
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
    borderRadius: 6,
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
    borderRadius: 6,
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
    borderRadius: 6,
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
    borderRadius: 6,
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
    borderRadius: 6,
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
    borderRadius: 6,
    paddingVertical: 14,
    alignItems: 'center',
    backgroundColor: COLORS.error,
  },
  rejectModalConfirmText: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.surface,
  },

  // ── Alert Banners ──
  alertBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginHorizontal: 16,
    marginBottom: 8,
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 4,
    backgroundColor: '#FEE2E2',
    borderWidth: 1,
    borderColor: '#FECACA',
  },
  alertBannerText: {
    flex: 1,
    fontSize: 13,
    fontWeight: '600',
    color: '#991B1B',
    lineHeight: 18,
  },

  // ── Due Date Card ──
  dueDateCard: {
    marginHorizontal: 16,
    marginTop: 12,
    backgroundColor: COLORS.surface,
    borderRadius: 4,
    padding: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },

  // ── Summary Card (Professional Layout) ──
  summaryCard: {
    marginHorizontal: 16,
    marginTop: 12,
    backgroundColor: COLORS.surface,
    borderRadius: 4,
    padding: 14,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
  },
  summaryHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 14,
    gap: 10,
  },
  summaryTitle: {
    flex: 1,
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.text,
    lineHeight: 23,
  },
  summaryStatusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  summaryStatusText: {
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  summaryMetricsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    paddingHorizontal: 8,
    backgroundColor: COLORS.borderLight,
    borderRadius: 4,
    marginBottom: 10,
  },
  summaryMetric: {
    flex: 1,
  },
  summaryMetricLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginBottom: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    fontWeight: '600',
  },
  summaryMetricValue: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
  },
  summaryDivider: {
    width: 1,
    height: '100%',
    backgroundColor: COLORS.border,
    marginHorizontal: 12,
  },
  summaryStatusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
  carryForwardBadge: {
    backgroundColor: '#dc2626',
    borderRadius: 3,
    paddingHorizontal: 5,
    paddingVertical: 2,
  },
  carryForwardBadgeText: {
    fontSize: 9,
    fontWeight: '800',
    color: '#fff',
    letterSpacing: 0.3,
  },
  summaryDueDateRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 8,
    paddingHorizontal: 10,
    backgroundColor: COLORS.borderLight,
    borderRadius: 4,
    marginBottom: 10,
  },
  summaryViewDetailsBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 8,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    marginTop: 4,
  },
  summaryViewDetailsText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.accent,
  },

  // ── Carry-Forward Detail Card ──
  carryForwardCard: {
    marginHorizontal: 16,
    marginTop: 12,
    backgroundColor: '#fff7ed',
    borderRadius: 4,
    padding: 12,
    borderLeftWidth: 3,
    borderLeftColor: '#dc2626',
    borderWidth: 1,
    borderColor: '#fed7aa',
  },
  carryForwardRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 3,
  },
  carryForwardLabel: {
    fontSize: 13,
    color: COLORS.textSecondary,
  },
  carryForwardValue: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
  },

  // ── Title Card (Redesigned) ──
  titleCard: {
    marginHorizontal: 0,
    marginTop: 4,
    backgroundColor: COLORS.surface,
    borderRadius: 4,
    padding: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
  },
  titleCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 14,
    gap: 8,
  },
  titleCardLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.textMuted,
    letterSpacing: 1,
    textTransform: 'uppercase',
    marginBottom: 3,
  },
  titleCardName: {
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.text,
    lineHeight: 22,
  },
  titleCardBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  titleCardBadgeDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  titleCardBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },

  // Financial Grid
  finGrid: {
    flexDirection: 'row',
    backgroundColor: COLORS.borderLight,
    borderRadius: 4,
    paddingVertical: 10,
    paddingHorizontal: 8,
    marginBottom: 10,
  },
  finGridItem: {
    flex: 1,
    alignItems: 'center',
  },
  finGridDivider: {
    width: 1,
    backgroundColor: COLORS.border,
    marginVertical: -2,
  },
  finGridLabel: {
    fontSize: 9,
    fontWeight: '700',
    color: COLORS.textMuted,
    letterSpacing: 0.8,
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  finGridValue: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.text,
    textAlign: 'center',
  },
  titleCardProgressBg: {
    height: 4,
    backgroundColor: COLORS.borderLight,
    borderRadius: 2,
    overflow: 'hidden',
    marginBottom: 10,
  },
  titleCardProgressFill: {
    height: '100%',
    backgroundColor: COLORS.success,
    borderRadius: 2,
  },
  titleCardFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  titleCardDetailsBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 8,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    marginTop: 4,
  },

  // ── Full Detail Sections ──
  fullDetailSection: {
    marginBottom: 20,
  },
  fullDetailSectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginBottom: 12,
  },
  fullDetailSectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
    flex: 1,
  },
  fdInlineBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: COLORS.accent + '15',
    borderRadius: 4,
    paddingVertical: 4,
    paddingHorizontal: 8,
  },
  fdFinSummaryCard: {
    backgroundColor: COLORS.borderLight,
    borderRadius: 4,
    padding: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  fdFinRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 4,
  },
  fdFinLabel: {
    fontSize: 13,
    color: COLORS.textSecondary,
  },
  fdFinValue: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
  },

  // ── Accordion Card ──
  fdAccordion: {
    backgroundColor: COLORS.surface,
    borderRadius: 8,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: COLORS.border,
    overflow: 'hidden',
  },
  fdAccordionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 14,
  },
  fdAccordionLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    flex: 1,
  },
  fdAccordionIcon: {
    width: 34,
    height: 34,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
  },
  fdAccordionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },
  fdAccordionSubtitle: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginTop: 1,
  },
  fdAccordionBody: {
    paddingHorizontal: 14,
    paddingBottom: 14,
    paddingTop: 0,
  },
  fdAccordionProgressBg: {
    height: 3,
    backgroundColor: COLORS.borderLight,
    marginHorizontal: 14,
    marginBottom: 4,
    borderRadius: 2,
    overflow: 'hidden',
  },
  fdAccordionProgressFill: {
    height: '100%',
    backgroundColor: COLORS.success,
    borderRadius: 2,
  },
  fdActionBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    marginTop: 12,
    paddingVertical: 10,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.accent + '40',
    backgroundColor: COLORS.accent + '08',
  },
  fdActionBtnText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.accent,
  },

  // ── Payment Cards ──
  fdPaymentCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 6,
    padding: 14,
    marginBottom: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  fdPaymentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  fdPaymentAmount: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
  },
  fdPaymentStatusBadge: {
    borderRadius: 4,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  fdPaymentStatusText: {
    fontSize: 10,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.3,
  },
  fdPaymentDate: {
    fontSize: 11,
    color: COLORS.textMuted,
  },
  fdPaymentMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 0,
    marginBottom: 8,
  },
  fdPaymentMetaText: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  fdPaymentMetaDot: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginHorizontal: 6,
  },
  fdPaymentRejection: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 6,
    backgroundColor: COLORS.errorLight,
    borderRadius: 4,
    padding: 10,
    marginBottom: 8,
  },
  fdPaymentRejectionText: {
    fontSize: 12,
    color: '#991B1B',
    flex: 1,
    lineHeight: 18,
  },
  fdPaymentReceipt: {
    width: '100%',
    height: 180,
    borderRadius: 6,
    backgroundColor: COLORS.borderLight,
    marginBottom: 8,
  },
  fdPaymentActions: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 4,
  },
  fdPaymentRejectBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 10,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.error + '40',
    backgroundColor: COLORS.errorLight,
  },
  fdPaymentApproveBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 10,
    borderRadius: 6,
    backgroundColor: COLORS.success,
  },
  fdSendPaymentBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 12,
    borderRadius: 6,
    borderWidth: 1.5,
    borderColor: COLORS.accent,
    borderStyle: 'dashed',
    backgroundColor: COLORS.accent + '08',
    marginTop: 4,
  },
  fdSendPaymentBtnText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.accent,
  },

  // ── Tab Bar ──
  fdTabBar: {
    flexDirection: 'row',
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    paddingHorizontal: 16,
  },
  fdTab: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 12,
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  fdTabActive: {
    borderBottomColor: COLORS.accent,
  },
  fdTabText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  fdTabTextActive: {
    color: COLORS.accent,
  },
  fdTabBadge: {
    backgroundColor: COLORS.error,
    borderRadius: 10,
    minWidth: 18,
    height: 18,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 5,
    marginLeft: 2,
  },
  fdTabBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    color: '#fff',
  },

  // ── Info Tab Card ──
  fdInfoCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 6,
    padding: 18,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  fdInfoLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.textMuted,
    letterSpacing: 1.2,
    textTransform: 'uppercase',
    marginBottom: 6,
  },
  fdInfoTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    lineHeight: 26,
    marginBottom: 12,
  },
  fdInfoMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 16,
  },
  fdInfoBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  fdInfoBadgeDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  fdInfoBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  fdInfoProject: {
    fontSize: 12,
    color: COLORS.accent,
    fontWeight: '600',
  },
  fdInfoDescSection: {
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
    paddingTop: 14,
    marginTop: 4,
  },
  fdInfoDescLabel: {
    fontSize: 11,
    fontWeight: '700',
    color: COLORS.textMuted,
    letterSpacing: 0.8,
    textTransform: 'uppercase',
    marginBottom: 8,
  },
  fdInfoDescText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 22,
  },
  fdInfoAttachSection: {
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
    paddingTop: 14,
    marginTop: 14,
  },
  fdInfoNoAttach: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingVertical: 8,
  },
  fdInfoAttachItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    backgroundColor: COLORS.accent + '08',
    borderRadius: 6,
    padding: 12,
    borderWidth: 1,
    borderColor: COLORS.accent + '20',
  },
  fdInfoAttachIcon: {
    width: 36,
    height: 36,
    borderRadius: 6,
    backgroundColor: COLORS.accent + '15',
    alignItems: 'center',
    justifyContent: 'center',
  },
  fdInfoAttachName: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
  },
  fdInfoAttachType: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginTop: 1,
  },

  // ── Quick Financial Overview Card ──
  fdQuickFinCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 6,
    padding: 16,
    borderWidth: 1,
    borderColor: COLORS.accent + '25',
  },
  fdQuickFinRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  fdQuickFinLabel: {
    fontSize: 12,
    color: COLORS.textMuted,
    fontWeight: '500',
  },
  fdQuickFinValue: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginTop: 2,
  },
  fdQuickFinProgressBg: {
    height: 4,
    backgroundColor: COLORS.borderLight,
    borderRadius: 2,
    overflow: 'hidden',
  },
  fdQuickFinProgressFill: {
    height: '100%',
    backgroundColor: COLORS.success,
    borderRadius: 2,
  },

  // ── Due Date Card ──
  fdDueDateCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 6,
    padding: 14,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  fdDueDateHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  fdDueDateBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 4,
    backgroundColor: COLORS.accent + '10',
    borderWidth: 1,
    borderColor: COLORS.accent + '30',
  },

  // ── Payment Section ──
  fdPaymentSection: {
    marginTop: 4,
  },
  fdPaymentSectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 10,
    paddingHorizontal: 2,
  },
  fdPaymentSectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
  },
  fdPaymentEmpty: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 36,
    backgroundColor: COLORS.surface,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderStyle: 'dashed',
  },

  // ── Due Date Modal (ddm) ──
  ddmOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  ddmSheet: {
    backgroundColor: COLORS.surface,
    borderTopLeftRadius: 16,
    borderTopRightRadius: 16,
    paddingHorizontal: 20,
    paddingBottom: 32,
    maxHeight: '85%',
  },
  ddmHandle: {
    width: 36,
    height: 4,
    backgroundColor: COLORS.borderLight,
    borderRadius: 2,
    alignSelf: 'center',
    marginTop: 10,
    marginBottom: 16,
  },
  ddmHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    marginBottom: 24,
  },
  ddmHeaderIcon: {
    width: 40,
    height: 40,
    borderRadius: 10,
    backgroundColor: COLORS.accent + '12',
    alignItems: 'center',
    justifyContent: 'center',
  },
  ddmTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 2,
  },
  ddmSubtitle: {
    fontSize: 13,
    color: COLORS.textMuted,
    lineHeight: 18,
  },
  ddmCloseBtn: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: COLORS.borderLight,
    alignItems: 'center',
    justifyContent: 'center',
  },
  ddmFieldGroup: {
    marginBottom: 16,
  },
  ddmFieldLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  ddmDateField: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    backgroundColor: COLORS.background,
    borderWidth: 1.5,
    borderColor: COLORS.border,
    borderRadius: 8,
    paddingHorizontal: 14,
    paddingVertical: 14,
  },
  ddmDateFieldActive: {
    borderColor: COLORS.accent,
    backgroundColor: COLORS.accent + '06',
  },
  ddmDateFieldText: {
    flex: 1,
    fontSize: 14,
    color: COLORS.text,
    fontWeight: '500',
  },
  ddmPickerWrap: {
    marginTop: 8,
    borderRadius: 8,
    overflow: 'hidden',
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    padding: 4,
  },
  ddmActions: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 8,
  },
  ddmCancelBtn: {
    flex: 1,
    borderRadius: 8,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1.5,
    borderColor: COLORS.border,
    backgroundColor: COLORS.background,
  },
  ddmCancelText: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  ddmSaveBtn: {
    flex: 1,
    flexDirection: 'row',
    borderRadius: 8,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: COLORS.accent,
  },
  ddmSaveText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#fff',
  },
});
