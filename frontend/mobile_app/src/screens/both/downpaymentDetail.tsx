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
  Image,
  Alert,
  Modal,
  TextInput,
  RefreshControl,
  Platform,
  KeyboardAvoidingView,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { payment_service } from '../../services/payment_service';
import PaymentReceiptForm from './paymentReceiptForm';
import { api_config } from '../../config/api';

// Color palette — matches milestoneDetail
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

interface DownpaymentDetailProps {
  projectId: number;
  projectTitle: string;
  downpaymentAmount: number;
  totalCost: number;
  userRole: 'owner' | 'contractor';
  userId: number;
  onClose: () => void;
}

export default function DownpaymentDetail({
  projectId,
  projectTitle,
  downpaymentAmount,
  totalCost,
  userRole,
  userId,
  onClose,
}: DownpaymentDetailProps) {
  const insets = useSafeAreaInsets();
  const [payments, setPayments] = useState<any[]>([]);
  const [loadingPayments, setLoadingPayments] = useState(true);
  const [showPaymentForm, setShowPaymentForm] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [rejectReason, setRejectReason] = useState('');
  const [showRejectModal, setShowRejectModal] = useState(false);
  const [rejectingPaymentId, setRejectingPaymentId] = useState<number | null>(null);
  const [actionLoading, setActionLoading] = useState<number | null>(null);

  const isOwner = userRole === 'owner';
  const isContractor = userRole === 'contractor';
  const downpaymentPercentage = totalCost > 0 ? (downpaymentAmount / totalCost) * 100 : 0;

  const formatCurrency = (amount: number) => {
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
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
  };

  const formatShortDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  };

  const fetchPayments = async () => {
    setLoadingPayments(true);
    try {
      const response = await payment_service.get_downpayment_receipts(projectId);
      if (response.success) {
        const receipts = response.data?.data?.payments || response.data?.payments || response.data || [];
        setPayments(Array.isArray(receipts) ? receipts : []);
      }
    } catch (error) {
      console.error('Error fetching downpayment receipts:', error);
    } finally {
      setLoadingPayments(false);
    }
  };

  useEffect(() => {
    fetchPayments();
  }, []);

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    await fetchPayments();
    setRefreshing(false);
  }, []);

  // ── Payment Actions ──
  const handleApprovePayment = async (paymentId: number) => {
    Alert.alert(
      'Approve Payment Receipt',
      'Confirm that you have received this downpayment?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Approve',
          onPress: async () => {
            setActionLoading(paymentId);
            try {
              const response = await payment_service.approve_downpayment(paymentId, userId);
              if (response.success) {
                Alert.alert('Success', 'Payment receipt approved');
                fetchPayments();
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

  const confirmRejectPayment = async () => {
    if (!rejectReason.trim()) {
      Alert.alert('Error', 'Please provide a rejection reason');
      return;
    }
    if (!rejectingPaymentId) return;
    setActionLoading(rejectingPaymentId);
    try {
      const response = await payment_service.reject_downpayment(rejectingPaymentId, userId, rejectReason);
      if (response.success) {
        Alert.alert('Success', 'Payment receipt rejected');
        fetchPayments();
      } else {
        Alert.alert('Error', response.message || 'Failed to reject receipt');
      }
    } catch (error) {
      Alert.alert('Error', 'An error occurred');
    } finally {
      setActionLoading(null);
      setShowRejectModal(false);
      setRejectingPaymentId(null);
      setRejectReason('');
    }
  };

  // ── Progress Actions ──
  const handleApproveProgress = async (progressId: number) => {
    Alert.alert(
      'Approve Progress Report',
      'Confirm approval of this progress report?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Approve',
          onPress: async () => {
            setActionLoading(progressId);
            try {
              const response = await progress_service.approve_progress(progressId, userId);
              if (response.success) {
                Alert.alert('Success', 'Progress report approved');
                fetchProgressReports();
              } else {
                Alert.alert('Error', response.message || 'Failed to approve progress');
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

  const handleRejectProgress = async (progressId: number) => {
    Alert.prompt(
      'Reject Progress Report',
      'Please provide a reason for rejection:',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Reject',
          onPress: async (reason) => {
            if (!reason || reason.trim() === '') {
              Alert.alert('Error', 'Please provide a rejection reason');
              return;
            }
            setActionLoading(progressId);
            try {
              const response = await progress_service.reject_progress(progressId, userId, reason);
              if (response.success) {
                Alert.alert('Success', 'Progress report rejected');
                fetchProgressReports();
              } else {
                Alert.alert('Error', response.message || 'Failed to reject progress');
              }
            } catch (error) {
              Alert.alert('Error', 'An error occurred');
            } finally {
              setActionLoading(null);
            }
          },
        },
      ],
      'plain-text'
    );
  };

  // ── Computed values ──
  const totalPaid = payments
    .filter(p => p.payment_status === 'approved')
    .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
  const totalSubmitted = payments
    .filter(p => p.payment_status === 'submitted')
    .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
  const remainingBalance = Math.max(0, downpaymentAmount - totalPaid);
  const overAmount = totalPaid > downpaymentAmount ? totalPaid - downpaymentAmount : 0;
  const paymentProgress = downpaymentAmount > 0 ? Math.min(100, (totalPaid / downpaymentAmount) * 100) : 0;
  const isCleared = totalPaid >= downpaymentAmount;
  const pendingPayments = payments.filter(p => p.payment_status === 'submitted').length;

  const derivedStatus = isCleared ? 'Verified' :
    totalPaid > 0 ? 'Partially Paid' :
    totalSubmitted > 0 ? 'Pending Review' : 'Awaiting Payment';

  const statusColor = isCleared ? COLORS.success :
    totalPaid > 0 ? COLORS.warning :
    totalSubmitted > 0 ? COLORS.info : COLORS.textMuted;

  // ── Render Modals (shared between views) ──
  function renderModals() {
    return (
      <>
        {showPaymentForm && (
          <Modal visible={showPaymentForm} animationType="slide" presentationStyle="fullScreen" onRequestClose={() => setShowPaymentForm(false)}>
            <PaymentReceiptForm
              milestoneItemId={-1}
              projectId={projectId}
              milestoneTitle="Downpayment"
              isDownpayment={true}
              expectedAmount={downpaymentAmount}
              originalCost={downpaymentAmount}
              adjustedCost={downpaymentAmount}
              carryForwardAmount={0}
              totalPaid={totalPaid}
              totalSubmitted={totalSubmitted}
              remainingBalance={remainingBalance}
              overAmount={overAmount}
              onClose={() => setShowPaymentForm(false)}
              onSuccess={() => {
                setShowPaymentForm(false);
                fetchPayments();
                Alert.alert('Success', 'Payment receipt uploaded successfully');
              }}
            />
          </Modal>
        )}

        {/* Rejection Reason Modal */}
        <Modal visible={showRejectModal} transparent animationType="fade" onRequestClose={() => setShowRejectModal(false)}>
          <View style={styles.rejectOverlay}>
            <View style={styles.rejectSheet}>
              <View style={styles.rejectHandle} />
              <Text style={styles.rejectTitle}>Reject Payment Receipt</Text>
              <Text style={styles.rejectSubtitle}>Please provide a reason for rejection:</Text>
              <View style={styles.rejectInputWrap}>
                <TextInput
                  style={styles.rejectInput}
                  multiline
                  placeholder="Enter rejection reason..."
                  placeholderTextColor={COLORS.textMuted}
                  value={rejectReason}
                  onChangeText={setRejectReason}
                />
              </View>
              <View style={styles.rejectActions}>
                <TouchableOpacity style={styles.rejectCancelBtn} onPress={() => { setShowRejectModal(false); setRejectingPaymentId(null); }}>
                  <Text style={styles.rejectCancelText}>Cancel</Text>
                </TouchableOpacity>
                <TouchableOpacity style={[styles.rejectConfirmBtn, !rejectReason.trim() && { opacity: 0.5 }]} onPress={confirmRejectPayment} disabled={!rejectReason.trim()}>
                  <Text style={styles.rejectConfirmText}>Reject</Text>
                </TouchableOpacity>
              </View>
            </View>
          </View>
        </Modal>
      </>
    );
  }

  // ── Main View ──
  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="chevron-left" size={28} color={COLORS.text} />
        </TouchableOpacity>
      </View>

      <KeyboardAvoidingView style={{ flex: 1 }} behavior={Platform.OS === 'ios' ? 'padding' : 'height'}>
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[COLORS.accent]} />}
      >
        {/* ─── Title Card ─── */}
        <View style={styles.titleCard}>
          {/* Row 1: Title + Status */}
          <View style={styles.titleCardHeader}>
            <View style={{ flex: 1, marginRight: 10 }}>
              <Text style={styles.titleCardLabel}>DOWNPAYMENT</Text>
              <Text style={styles.titleCardName}>Project Downpayment</Text>
            </View>
            <View style={[styles.titleCardBadge, { backgroundColor: statusColor + '15' }]}>
              <View style={[styles.titleCardBadgeDot, { backgroundColor: statusColor }]} />
              <Text style={[styles.titleCardBadgeText, { color: statusColor }]}>{derivedStatus}</Text>
            </View>
          </View>

          {/* Row 2: Financial Grid — 3 columns */}
          <View style={styles.finGrid}>
            <View style={styles.finGridItem}>
              <Text style={styles.finGridLabel}>REQUIRED</Text>
              <Text style={styles.finGridValue} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
                ₱{downpaymentAmount.toLocaleString('en-US', { minimumFractionDigits: 0 })}
              </Text>
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
              <Text style={[styles.finGridValue, { color: overAmount > 0 ? COLORS.error : remainingBalance > 0 ? COLORS.accent : COLORS.success }]} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
                {overAmount > 0 ? `+₱${overAmount.toLocaleString('en-US', { minimumFractionDigits: 0 })}` : `₱${remainingBalance.toLocaleString('en-US', { minimumFractionDigits: 0 })}`}
              </Text>
              {overAmount > 0 && <Text style={{ fontSize: 9, color: COLORS.error, fontWeight: '700', marginTop: 1 }}>OVER BUDGET</Text>}
            </View>
          </View>

          {/* Progress bar */}
          <View style={styles.titleCardProgressBg}>
            <View style={[styles.titleCardProgressFill, { width: `${paymentProgress}%` }]} />
          </View>

          {/* Row 3: Payment Status */}
          <View style={styles.titleCardFooter}>
            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
              <View style={[styles.titleCardBadgeDot, { backgroundColor: statusColor, width: 7, height: 7 }]} />
              <Text style={{ fontSize: 12, fontWeight: '700', color: statusColor }}>{derivedStatus}</Text>
            </View>
            <Text style={{ fontSize: 11, color: COLORS.textSecondary }}>
              {downpaymentPercentage.toFixed(1)}% of total cost
            </Text>
          </View>
        </View>

        {/* Status Banners */}
        {isCleared && (
          <View style={[styles.alertBanner, { backgroundColor: COLORS.successLight, borderColor: '#6EE7B7' }]}>
            <Feather name="check-circle" size={18} color={COLORS.success} />
            <Text style={[styles.alertBannerText, { color: '#065F46' }]}>
              Downpayment verified — milestones are now unlocked
            </Text>
          </View>
        )}

        {!isCleared && totalSubmitted > 0 && (
          <View style={[styles.alertBanner, { backgroundColor: COLORS.infoLight, borderColor: '#93C5FD' }]}>
            <Feather name="info" size={18} color={COLORS.info} />
            <Text style={[styles.alertBannerText, { color: '#1E40AF' }]}>
              {pendingPayments} payment{pendingPayments !== 1 ? 's' : ''} pending review
            </Text>
          </View>
        )}

        {!isCleared && totalPaid === 0 && totalSubmitted === 0 && (
          <View style={[styles.alertBanner, { backgroundColor: COLORS.warningLight, borderColor: '#FCD34D' }]}>
            <Feather name="alert-circle" size={18} color={COLORS.warning} />
            <Text style={[styles.alertBannerText, { color: '#92400E' }]}>
              {isOwner
                ? 'Upload a downpayment receipt to start working on milestones'
                : 'Waiting for owner to submit downpayment receipt'}
            </Text>
          </View>
        )}

        {/* Divider */}
        <View style={styles.divider} />

        {/* Financial Breakdown */}
        <View style={styles.fdSection}>
          <View style={styles.fdSectionHeader}>
            <Feather name="dollar-sign" size={16} color={COLORS.accent} />
            <Text style={styles.fdSectionTitle}>Payment Breakdown</Text>
          </View>
          <View style={styles.fdFinRow}>
            <Text style={styles.fdFinLabel}>Required Amount</Text>
            <Text style={styles.fdFinValue}>{formatCurrency(downpaymentAmount)}</Text>
          </View>
          <View style={styles.fdFinRow}>
            <Text style={styles.fdFinLabel}>Total Project Cost</Text>
            <Text style={[styles.fdFinValue, { color: COLORS.textSecondary }]}>{formatCurrency(totalCost)}</Text>
          </View>
          <View style={styles.fdFinRow}>
            <Text style={styles.fdFinLabel}>Downpayment Rate</Text>
            <Text style={[styles.fdFinValue, { color: COLORS.accent }]}>{downpaymentPercentage.toFixed(1)}%</Text>
          </View>
          <View style={[styles.fdFinRow, { borderBottomWidth: 0 }]}>
            <Text style={styles.fdFinLabel}>Remaining After DP</Text>
            <Text style={styles.fdFinValue}>{formatCurrency(totalCost - downpaymentAmount)}</Text>
          </View>
        </View>

        {/* Payment Progress */}
        <View style={styles.fdSection}>
          <View style={styles.fdSectionHeader}>
            <Feather name="bar-chart-2" size={16} color={COLORS.accent} />
            <Text style={styles.fdSectionTitle}>Payment Progress</Text>
          </View>
          <View style={styles.fdFinRow}>
            <Text style={styles.fdFinLabel}>Total Paid</Text>
            <Text style={[styles.fdFinValue, { color: COLORS.success }]}>{formatCurrency(totalPaid)}</Text>
          </View>
          <View style={styles.fdFinRow}>
            <Text style={styles.fdFinLabel}>Pending Review</Text>
            <Text style={[styles.fdFinValue, { color: COLORS.info }]}>{formatCurrency(totalSubmitted)}</Text>
          </View>
          <View style={[styles.fdFinRow, { borderBottomWidth: 0 }]}>
            <Text style={styles.fdFinLabel}>Remaining Balance</Text>
            <Text style={[styles.fdFinValue, { color: remainingBalance > 0 ? COLORS.accent : COLORS.success }]}>
              {overAmount > 0 ? `+${formatCurrency(overAmount)}` : formatCurrency(remainingBalance)}
            </Text>
          </View>
          {overAmount > 0 && (
            <Text style={{ fontSize: 11, color: COLORS.error, fontWeight: '600', marginTop: 4 }}>Over budget</Text>
          )}
        </View>

        {/* Payment Receipts */}
        {loadingPayments ? (
          <View style={styles.emptyContainer}>
            <ActivityIndicator size="large" color={COLORS.accent} />
            <Text style={styles.emptyText}>Loading payments...</Text>
          </View>
        ) : payments.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Feather name="credit-card" size={40} color={COLORS.textMuted} />
            <Text style={styles.emptyTitle}>No Payments Yet</Text>
            <Text style={styles.emptyText}>
              {isOwner ? 'Upload your first downpayment receipt.' : 'Waiting for the owner to submit payment.'}
            </Text>
          </View>
        ) : (
          payments.map((payment) => {
            const pStatusColor = payment.payment_status === 'approved' ? COLORS.success :
              payment.payment_status === 'rejected' ? COLORS.error : COLORS.warning;
            const pStatusBg = payment.payment_status === 'approved' ? COLORS.successLight :
              payment.payment_status === 'rejected' ? COLORS.errorLight : COLORS.warningLight;

            return (
              <View key={payment.payment_id} style={styles.paymentCard}>
                <View style={styles.paymentHeader}>
                  <Text style={styles.paymentAmount}>{formatCurrency(parseFloat(payment.amount || 0))}</Text>
                  <View style={[styles.statusBadge, { backgroundColor: pStatusBg }]}>
                    <Text style={[styles.statusText, { color: pStatusColor }]}>
                      {payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1)}
                    </Text>
                  </View>
                </View>

                <Text style={styles.paymentDate}>{formatDate(payment.transaction_date)}</Text>
                <Text style={styles.paymentType}>
                  {(payment.payment_type || 'payment').replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase())}
                </Text>

                {payment.transaction_number && (
                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 4 }}>
                    <Feather name="hash" size={12} color={COLORS.textMuted} />
                    <Text style={{ fontSize: 12, color: COLORS.textSecondary }}>{payment.transaction_number}</Text>
                  </View>
                )}

                {payment.receipt_photo && (
                  <Image
                    source={{ uri: `${api_config.base_url}/api/files/${payment.receipt_photo}` }}
                    style={styles.receiptImage}
                    resizeMode="cover"
                  />
                )}

                {payment.payment_status === 'rejected' && payment.reason && (
                  <View style={styles.rejectionReason}>
                    <Feather name="alert-circle" size={16} color={COLORS.error} />
                    <Text style={styles.rejectionText}>{payment.reason}</Text>
                  </View>
                )}

                {isContractor && payment.payment_status === 'submitted' && (
                  <View style={styles.actionButtons}>
                    <TouchableOpacity
                      style={[styles.approveBtn, actionLoading === payment.payment_id && { opacity: 0.5 }]}
                      onPress={() => handleApprovePayment(payment.payment_id)}
                      disabled={actionLoading === payment.payment_id}
                    >
                      <Feather name="check" size={16} color={COLORS.surface} />
                      <Text style={styles.approveBtnText}>Approve</Text>
                    </TouchableOpacity>
                    <TouchableOpacity
                      style={[styles.rejectBtn, actionLoading === payment.payment_id && { opacity: 0.5 }]}
                      onPress={() => handleRejectPayment(payment.payment_id)}
                      disabled={actionLoading === payment.payment_id}
                    >
                      <Feather name="x" size={16} color={COLORS.surface} />
                      <Text style={styles.rejectBtnText}>Reject</Text>
                    </TouchableOpacity>
                  </View>
                )}
              </View>
            );
          })
        )}

        {isOwner && !isCleared && payments.filter(p => p.payment_status === 'submitted' || p.payment_status === 'approved').length === 0 && (
          <TouchableOpacity
            style={styles.fdSendPaymentBtn}
            onPress={() => setShowPaymentForm(true)}
          >
            <Feather name="plus" size={18} color={COLORS.accent} />
            <Text style={styles.fdSendPaymentBtnText}>Send new payment receipt</Text>
          </TouchableOpacity>
        )}

        <View style={{ height: 120 }} />
      </ScrollView>
      </KeyboardAvoidingView>

      {/* Bottom Bar */}
      <View style={[styles.bottomBar, { paddingBottom: insets.bottom + 16 }]}>
        {isOwner && !isCleared && payments.filter(p => p.payment_status === 'submitted' || p.payment_status === 'approved').length === 0 && (
          <TouchableOpacity
            style={styles.sendPaymentButton}
            onPress={() => setShowPaymentForm(true)}
          >
            <Feather name="credit-card" size={20} color={COLORS.surface} style={{ marginRight: 8 }} />
            <Text style={styles.sendPaymentButtonText}>Send payment receipt</Text>
          </TouchableOpacity>
        )}
      </View>

      {/* Modals */}
      {renderModals()}
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
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: 24,
  },

  // ─── Title Card (matches milestoneDetail) ───
  titleCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 4,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  titleCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 16,
  },
  titleCardLabel: {
    fontSize: 11,
    fontWeight: '700',
    letterSpacing: 1.5,
    color: COLORS.accent,
    marginBottom: 4,
  },
  titleCardName: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    lineHeight: 26,
  },
  titleCardBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 4,
    gap: 6,
  },
  titleCardBadgeDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  titleCardBadgeText: {
    fontSize: 11,
    fontWeight: '700',
  },

  // ─── Financial Grid ───
  finGrid: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.borderLight,
    borderRadius: 4,
    padding: 14,
    marginBottom: 12,
  },
  finGridItem: {
    flex: 1,
    alignItems: 'center',
  },
  finGridDivider: {
    width: 1,
    height: 32,
    backgroundColor: COLORS.border,
    marginHorizontal: 8,
  },
  finGridLabel: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 0.5,
    color: COLORS.textMuted,
    marginBottom: 4,
  },
  finGridValue: {
    fontSize: 16,
    fontWeight: '800',
    color: COLORS.text,
  },

  // ─── Progress Bar ───
  titleCardProgressBg: {
    height: 6,
    backgroundColor: COLORS.borderLight,
    borderRadius: 3,
    overflow: 'hidden',
    marginBottom: 12,
  },
  titleCardProgressFill: {
    height: '100%',
    backgroundColor: COLORS.success,
    borderRadius: 3,
  },
  titleCardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  titleCardDetailsBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    marginTop: 14,
    paddingTop: 14,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },

  // ─── Alert Banners ───
  alertBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 12,
    borderRadius: 4,
    borderWidth: 1,
    marginBottom: 8,
    gap: 10,
  },
  alertBannerText: {
    flex: 1,
    fontSize: 13,
    fontWeight: '600',
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.borderLight,
    marginVertical: 16,
  },

  // ─── Reports Timeline ───
  reportsSection: {
    marginBottom: 16,
  },
  reportsTimeline: {},
  reportItem: {
    flexDirection: 'row',
    paddingLeft: 12,
    marginBottom: 0,
  },
  reportLine: {
    position: 'absolute',
    left: 17,
    top: 20,
    bottom: -4,
    width: 2,
    backgroundColor: COLORS.borderLight,
  },
  reportDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    marginTop: 6,
    marginRight: 12,
    zIndex: 1,
  },
  reportContent: {
    flex: 1,
    backgroundColor: COLORS.surface,
    borderRadius: 4,
    padding: 14,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  reportHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 6,
  },
  reportTitle: {
    flex: 1,
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginRight: 8,
  },
  reportDescription: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 18,
    marginBottom: 6,
  },
  reportDate: {
    fontSize: 11,
    color: COLORS.textMuted,
  },

  // ─── Tab Bar ───
  tabBar: {
    flexDirection: 'row',
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    backgroundColor: COLORS.background,
  },
  tab: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    gap: 6,
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  tabActive: {
    borderBottomColor: COLORS.accent,
  },
  tabText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  tabTextActive: {
    color: COLORS.accent,
  },
  tabBadge: {
    backgroundColor: COLORS.error,
    borderRadius: 9,
    minWidth: 18,
    height: 18,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 5,
  },
  tabBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.surface,
  },

  // ─── Full Detail Info Card ───
  fdInfoCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 4,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  fdInfoLabel: {
    fontSize: 11,
    fontWeight: '700',
    letterSpacing: 1.5,
    color: COLORS.accent,
    marginBottom: 4,
  },
  fdInfoTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 10,
  },
  fdInfoMeta: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  fdInfoBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
    gap: 5,
  },
  fdInfoBadgeDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  fdInfoBadgeText: {
    fontSize: 11,
    fontWeight: '700',
  },
  fdInfoProject: {
    fontSize: 12,
    color: COLORS.textSecondary,
    flex: 1,
  },

  // ─── Full Detail Sections ───
  fdSection: {
    backgroundColor: COLORS.surface,
    borderRadius: 4,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  fdSectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 14,
  },
  fdSectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
  },
  fdFinRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  fdFinLabel: {
    fontSize: 13,
    color: COLORS.textSecondary,
  },
  fdFinValue: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
  },
  fdSendPaymentBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    gap: 8,
    borderWidth: 1.5,
    borderColor: COLORS.accent,
    borderRadius: 4,
    borderStyle: 'dashed',
    marginTop: 8,
  },
  fdSendPaymentBtnText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.accent,
  },

  // ─── Payment Cards ───
  paymentCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 4,
    padding: 16,
    marginBottom: 12,
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
    marginBottom: 8,
  },
  paymentAmount: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
  },
  statusBadge: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 4,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  paymentDate: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginBottom: 4,
  },
  paymentType: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 8,
  },
  receiptImage: {
    width: '100%',
    height: 200,
    borderRadius: 4,
    backgroundColor: COLORS.borderLight,
    marginBottom: 8,
  },
  rejectionReason: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: COLORS.errorLight,
    padding: 12,
    borderRadius: 4,
    gap: 8,
    marginTop: 8,
    borderWidth: 1,
    borderColor: COLORS.error,
  },
  rejectionText: {
    flex: 1,
    fontSize: 12,
    color: COLORS.error,
    lineHeight: 16,
  },

  // ─── Action Buttons ───
  actionButtons: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 12,
  },
  approveBtn: {
    flex: 1,
    backgroundColor: COLORS.success,
    borderRadius: 8,
    paddingVertical: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
  },
  approveBtnText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.surface,
  },
  rejectBtn: {
    flex: 1,
    backgroundColor: COLORS.error,
    borderRadius: 8,
    paddingVertical: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
  },
  rejectBtnText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.surface,
  },

  // ─── Empty States ───
  emptyContainer: {
    alignItems: 'center',
    paddingVertical: 32,
  },
  emptyIcon: {
    marginBottom: 12,
  },
  emptyTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 6,
  },
  emptyText: {
    fontSize: 13,
    color: COLORS.textMuted,
    textAlign: 'center',
    lineHeight: 18,
    paddingHorizontal: 40,
  },

  // ─── Bottom Bar ───
  bottomBar: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: COLORS.surface,
    paddingHorizontal: 24,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    gap: 8,
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

  // ─── Reject Modal ───
  rejectOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  rejectSheet: {
    backgroundColor: COLORS.surface,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    padding: 24,
  },
  rejectHandle: {
    width: 40,
    height: 4,
    backgroundColor: COLORS.border,
    borderRadius: 2,
    alignSelf: 'center',
    marginBottom: 16,
  },
  rejectTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  rejectSubtitle: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginBottom: 16,
  },
  rejectInputWrap: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 4,
    padding: 12,
    minHeight: 100,
    marginBottom: 16,
  },
  rejectInput: {
    fontSize: 14,
    color: COLORS.text,
    textAlignVertical: 'top',
  },
  rejectActions: {
    flexDirection: 'row',
    gap: 12,
  },
  rejectCancelBtn: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
    alignItems: 'center',
  },
  rejectCancelText: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  rejectConfirmBtn: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 8,
    backgroundColor: COLORS.error,
    alignItems: 'center',
  },
  rejectConfirmText: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.surface,
  },
});
