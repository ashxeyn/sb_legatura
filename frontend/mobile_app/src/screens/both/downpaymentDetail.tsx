// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  StatusBar,
  ActivityIndicator,
  TextInput,
  Image,
  Alert,
  Modal,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { payment_service } from '../../services/payment_service';
import { progress_service } from '../../services/progress_service';
import PaymentReceiptForm from './paymentReceiptForm';
import ProgressReportForm from './ProgressReportForm';
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
  const [progressReports, setProgressReports] = useState<any[]>([]);
  const [loadingReports, setLoadingReports] = useState(true);
  const [showPaymentForm, setShowPaymentForm] = useState(false);
  const [showProgressForm, setShowProgressForm] = useState(false);

  const isOwner = userRole === 'owner';
  const isContractor = userRole === 'contractor';
  const downpaymentPercentage = totalCost > 0 ? (downpaymentAmount / totalCost) * 100 : 0;

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      minimumFractionDigits: 2,
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
  };

  const fetchPayments = async () => {
    setLoadingPayments(true);
    try {
      const response = await payment_service.get_downpayment_receipts(projectId);
      if (response.success) {
        const receipts = response.data?.payments || response.data || [];
        setPayments(Array.isArray(receipts) ? receipts : []);
      }
    } catch (error) {
      console.error('Error fetching downpayment receipts:', error);
    } finally {
      setLoadingPayments(false);
    }
  };

  const fetchProgressReports = async () => {
    setLoadingReports(true);
    try {
      // Use item_id = -1 for downpayment progress reports
      const response = await progress_service.get_progress_by_item(userId, -1);
      if (response.success) {
        let progressList: any[] | null = null;
        
        if (response.data?.data?.progress_list) {
          progressList = response.data.data.progress_list;
        } else if (response.data?.progress_list) {
          progressList = response.data.progress_list;
        } else if (Array.isArray(response.data?.data)) {
          progressList = response.data.data;
        } else if (Array.isArray(response.data)) {
          progressList = response.data;
        } else if (response.progress_list) {
          progressList = response.progress_list;
        }

        if (progressList && progressList.length > 0) {
          setProgressReports(progressList);
        } else {
          setProgressReports([]);
        }
      }
    } catch (error) {
      console.error('Error fetching progress reports:', error);
    } finally {
      setLoadingReports(false);
    }
  };

  useEffect(() => {
    fetchPayments();
    fetchProgressReports();
  }, []);

  const handleApprovePayment = async (paymentId: number) => {
    Alert.alert(
      'Approve Payment Receipt',
      'Confirm that you have received this downpayment?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Approve',
          onPress: async () => {
            try {
              const response = await payment_service.approve_payment(paymentId, userId);
              if (response.success) {
                Alert.alert('Success', 'Payment receipt approved');
                fetchPayments();
              } else {
                Alert.alert('Error', response.message || 'Failed to approve receipt');
              }
            } catch (error) {
              Alert.alert('Error', 'An error occurred');
            }
          },
        },
      ]
    );
  };

  const handleRejectPayment = async (paymentId: number) => {
    Alert.prompt(
      'Reject Payment Receipt',
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
            try {
              const response = await payment_service.reject_payment(paymentId, userId, reason);
              if (response.success) {
                Alert.alert('Success', 'Payment receipt rejected');
                fetchPayments();
              } else {
                Alert.alert('Error', response.message || 'Failed to reject receipt');
              }
            } catch (error) {
              Alert.alert('Error', 'An error occurred');
            }
          },
        },
      ],
      'plain-text'
    );
  };

  const handleApproveProgress = async (progressId: number) => {
    Alert.alert(
      'Approve Progress Report',
      'Confirm approval of this progress report?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Approve',
          onPress: async () => {
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
            }
          },
        },
      ],
      'plain-text'
    );
  };

  const hasApprovedPayment = payments.some(p => p.payment_status === 'approved');

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.primary} />
      
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="arrow-left" size={24} color={COLORS.surface} />
        </TouchableOpacity>
        <View style={styles.headerContent}>
          <Text style={styles.headerTitle}>Downpayment</Text>
          <Text style={styles.headerSubtitle}>{projectTitle}</Text>
        </View>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent}>
        {/* Downpayment Information Card */}
        <View style={styles.infoCard}>
          <View style={styles.infoHeader}>
            <Feather name="dollar-sign" size={24} color={COLORS.accent} />
            <Text style={styles.infoTitle}>Downpayment Details</Text>
          </View>
          
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Amount:</Text>
            <Text style={styles.infoValue}>{formatCurrency(downpaymentAmount)}</Text>
          </View>
          
          <View style={styles.infoRow}>
            <Text style={styles.infoLabel}>Total Project Cost:</Text>
            <Text style={styles.infoValue}>{formatCurrency(totalCost)}</Text>
          </View>
        </View>

        {/* Payment Receipts Section */}
        <View style={styles.section}>
          {loadingPayments ? (
            <ActivityIndicator size="large" color={COLORS.accent} style={{ marginVertical: 20 }} />
          ) : payments.length > 0 ? (
            payments.map((payment) => {
              const statusColor = payment.payment_status === 'approved' ? COLORS.success :
                                 payment.payment_status === 'rejected' ? COLORS.error :
                                 COLORS.warning;
              
              const statusBg = payment.payment_status === 'approved' ? COLORS.successLight :
                              payment.payment_status === 'rejected' ? COLORS.errorLight :
                              COLORS.warningLight;

              return (
                <View key={payment.payment_id} style={styles.paymentCard}>
                  <View style={styles.paymentHeader}>
                    <Text style={styles.paymentAmount}>{formatCurrency(payment.amount)}</Text>
                    <View style={[styles.statusBadge, { backgroundColor: statusBg }]}>
                      <Text style={[styles.statusText, { color: statusColor }]}>
                        {payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1)}
                      </Text>
                    </View>
                  </View>

                  <Text style={styles.paymentDate}>{formatDate(payment.transaction_date)}</Text>
                  <Text style={styles.paymentType}>
                    {payment.payment_type.replace('_', ' ').replace(/\b\w/g, (l: string) => l.toUpperCase())}
                  </Text>

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
                    <View style={styles.paymentActions}>
                      <TouchableOpacity
                        style={styles.approveButton}
                        onPress={() => handleApprovePayment(payment.payment_id)}
                      >
                        <Feather name="check" size={16} color={COLORS.surface} />
                        <Text style={styles.approveButtonText}>Approve</Text>
                      </TouchableOpacity>
                      <TouchableOpacity
                        style={styles.rejectButton}
                        onPress={() => handleRejectPayment(payment.payment_id)}
                      >
                        <Feather name="x" size={16} color={COLORS.surface} />
                        <Text style={styles.rejectButtonText}>Reject</Text>
                      </TouchableOpacity>
                    </View>
                  )}
                </View>
              );
            })
          ) : null}
        </View>

        {/* Progress Reports Section */}
        <View style={styles.section}>
          {loadingReports ? (
            <ActivityIndicator size="large" color={COLORS.accent} style={{ marginVertical: 20 }} />
          ) : progressReports.length > 0 ? (
            progressReports.map((report) => {
              const statusColor = report.progress_status === 'approved' ? COLORS.success :
                                 report.progress_status === 'rejected' ? COLORS.error :
                                 COLORS.warning;
              
              const statusBg = report.progress_status === 'approved' ? COLORS.successLight :
                              report.progress_status === 'rejected' ? COLORS.errorLight :
                              COLORS.warningLight;

              return (
                <View key={report.progress_id} style={styles.progressCard}>
                  <View style={styles.progressHeader}>
                    <Text style={styles.progressTitle}>{report.progress_title || 'Progress Report'}</Text>
                    <View style={[styles.statusBadge, { backgroundColor: statusBg }]}>
                      <Text style={[styles.statusText, { color: statusColor }]}>
                        {report.progress_status.charAt(0).toUpperCase() + report.progress_status.slice(1)}
                      </Text>
                    </View>
                  </View>

                  <Text style={styles.progressDescription}>{report.progress_description}</Text>
                  <Text style={styles.progressDate}>{formatDate(report.created_at)}</Text>

                  {report.progress_status === 'rejected' && report.progress_rejected_reason && (
                    <View style={styles.rejectionReason}>
                      <Feather name="alert-circle" size={16} color={COLORS.error} />
                      <Text style={styles.rejectionText}>{report.progress_rejected_reason}</Text>
                    </View>
                  )}

                  {isContractor && report.progress_status === 'submitted' && (
                    <View style={styles.paymentActions}>
                      <TouchableOpacity
                        style={styles.approveButton}
                        onPress={() => handleApproveProgress(report.progress_id)}
                      >
                        <Feather name="check" size={16} color={COLORS.surface} />
                        <Text style={styles.approveButtonText}>Approve</Text>
                      </TouchableOpacity>
                      <TouchableOpacity
                        style={styles.rejectButton}
                        onPress={() => handleRejectProgress(report.progress_id)}
                      >
                        <Feather name="x" size={16} color={COLORS.surface} />
                        <Text style={styles.rejectButtonText}>Reject</Text>
                      </TouchableOpacity>
                    </View>
                  )}
                </View>
              );
            })
          ) : null}
        </View>

        <View style={{ height: 100 }} />
      </ScrollView>

      {/* Bottom Action Buttons */}
      <View style={[styles.bottomBar, { paddingBottom: insets.bottom + 16 }]}>
        {/* Contractor: Submit Progress Report */}
        {isContractor && (
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

        {/* Owner: Send payment (show if any approved progress report exists) */}
        {isOwner && hasApprovedPayment && (
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
      </View>

      {/* Payment Form Modal */}
      {showPaymentForm && (
        <Modal
          visible={showPaymentForm}
          animationType="slide"
          presentationStyle="fullScreen"
          onRequestClose={() => setShowPaymentForm(false)}
        >
          <PaymentReceiptForm
            milestoneItemId={-1}
            projectId={projectId}
            milestoneTitle="Downpayment"
            onClose={() => setShowPaymentForm(false)}
            onSuccess={() => {
              setShowPaymentForm(false);
              fetchPayments();
              Alert.alert('Success', 'Payment receipt uploaded successfully');
            }}
          />
        </Modal>
      )}

      {/* Progress Report Form Modal */}
      {showProgressForm && (
        <Modal
          visible={showProgressForm}
          animationType="slide"
          presentationStyle="fullScreen"
          onRequestClose={() => setShowProgressForm(false)}
        >
          <ProgressReportForm
            milestoneItemId={-1}
            projectId={projectId}
            milestoneTitle="Downpayment"
            userId={userId}
            onClose={() => setShowProgressForm(false)}
            onSuccess={() => {
              setShowProgressForm(false);
              fetchProgressReports();
              Alert.alert('Success', 'Progress report submitted successfully');
            }}
          />
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
    backgroundColor: COLORS.primary,
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 16,
  },
  backButton: {
    marginRight: 12,
  },
  headerContent: {
    flex: 1,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.surface,
  },
  headerSubtitle: {
    fontSize: 14,
    color: COLORS.primaryLight,
    marginTop: 2,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
  },
  infoCard: {
    backgroundColor: COLORS.accentLight,
    borderRadius: 12,
    padding: 16,
    marginBottom: 20,
    borderWidth: 2,
    borderColor: COLORS.accent,
  },
  infoHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
    gap: 8,
  },
  infoTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.accent,
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 8,
  },
  infoLabel: {
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  infoValue: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
  },
  section: {
    marginBottom: 24,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
    gap: 8,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
  },
  paymentCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
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
    borderRadius: 6,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
  },
  paymentDate: {
    fontSize: 13,
    color: COLORS.textMuted,
    marginBottom: 4,
  },
  paymentType: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginBottom: 12,
  },
  receiptImage: {
    width: '100%',
    height: 200,
    borderRadius: 8,
    marginBottom: 12,
  },
  rejectionReason: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: COLORS.errorLight,
    padding: 12,
    borderRadius: 8,
    gap: 8,
    marginTop: 8,
  },
  rejectionText: {
    flex: 1,
    fontSize: 13,
    color: COLORS.error,
  },
  paymentActions: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 12,
  },
  approveButton: {
    flex: 1,
    backgroundColor: COLORS.success,
    borderRadius: 8,
    paddingVertical: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
  },
  approveButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.surface,
  },
  rejectButton: {
    flex: 1,
    backgroundColor: COLORS.error,
    borderRadius: 8,
    paddingVertical: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
  },
  rejectButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.surface,
  },
  progressCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  progressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 8,
  },
  progressTitle: {
    flex: 1,
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
    marginRight: 8,
  },
  progressDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginBottom: 8,
    lineHeight: 20,
  },
  progressDate: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  emptyText: {
    fontSize: 14,
    color: COLORS.textMuted,
    textAlign: 'center',
    paddingVertical: 24,
    fontStyle: 'italic',
  },
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
});
