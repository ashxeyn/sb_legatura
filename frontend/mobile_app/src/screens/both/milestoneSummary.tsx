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
  RefreshControl,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import {
  summary_service,
  MilestoneSummaryData,
  DateHistoryRecord,
  PaymentRecord,
  ProgressReport,
} from '../../services/summary_service';

// ─────────────────────────────────────────────────────────────────────────────
// Colors
// ─────────────────────────────────────────────────────────────────────────────
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

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────
const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 }).format(amount);

const formatDate = (dateString: string | null | undefined) => {
  if (!dateString) return '—';
  return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatDateTime = (dateString: string | null | undefined) => {
  if (!dateString) return '—';
  const d = new Date(dateString);
  return `${d.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' })} ${d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })}`;
};

const statusColor = (status: string) => {
  const map: Record<string, { bg: string; fg: string }> = {
    completed: { bg: COLORS.successLight, fg: COLORS.success },
    approved: { bg: COLORS.successLight, fg: COLORS.success },
    pending: { bg: COLORS.warningLight, fg: COLORS.warning },
    submitted: { bg: COLORS.warningLight, fg: COLORS.warning },
    active: { bg: COLORS.infoLight, fg: COLORS.info },
    in_progress: { bg: COLORS.infoLight, fg: COLORS.info },
    rejected: { bg: COLORS.errorLight, fg: COLORS.error },
    withdrawn: { bg: COLORS.borderLight, fg: COLORS.textMuted },
    revision_requested: { bg: COLORS.accentLight, fg: COLORS.accent },
  };
  return map[status?.toLowerCase()] ?? { bg: COLORS.borderLight, fg: COLORS.textSecondary };
};

// ─────────────────────────────────────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────────────────────────────────────
interface MilestoneSummaryProps {
  route: {
    params: {
      projectId: number;
      itemId: number;
      projectTitle?: string;
    };
  };
  navigation: any;
}

// ─────────────────────────────────────────────────────────────────────────────
// Component
// ─────────────────────────────────────────────────────────────────────────────
export default function MilestoneSummary({ route, navigation }: MilestoneSummaryProps) {
  const insets = useSafeAreaInsets();
  const { projectId, itemId, projectTitle } = route.params;

  const [data, setData] = useState<MilestoneSummaryData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [refreshing, setRefreshing] = useState(false);

  const [expandedSections, setExpandedSections] = useState<Record<string, boolean>>({
    financial: true,
    dateHistory: true,
    payments: false,
    progress: false,
  });

  const toggleSection = (key: string) =>
    setExpandedSections(prev => ({ ...prev, [key]: !prev[key] }));

  // ── Fetch ──
  const fetchSummary = useCallback(async () => {
    try {
      setError(null);
      const res = await summary_service.getMilestoneSummary(projectId, itemId);
      // api_request wraps: { success, data: serverJson } where serverJson = { success, data: payload }
      const payload = res.data?.data ?? res.data;
      if (res.success && payload) {
        setData(payload);
      } else {
        setError(res.data?.message || res.message || 'Failed to load milestone summary');
      }
    } catch (e: any) {
      setError(e.message || 'Unknown error');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [projectId, itemId]);

  useEffect(() => {
    fetchSummary();
  }, [fetchSummary]);

  const onRefresh = () => { setRefreshing(true); fetchSummary(); };

  // ── Loading / Error ──
  if (loading) {
    return (
      <View style={[styles.centered, { paddingTop: insets.top }]}>
        <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading milestone summary…</Text>
      </View>
    );
  }

  if (error || !data) {
    return (
      <View style={[styles.centered, { paddingTop: insets.top }]}>
        <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />
        <Feather name="alert-circle" size={40} color={COLORS.error} />
        <Text style={styles.errorText}>{error || 'No data available'}</Text>
        <TouchableOpacity style={styles.retryBtn} onPress={fetchSummary}>
          <Text style={styles.retryBtnText}>Retry</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const { header, financial, date_history, payments, progress_reports } = data;

  const paidPercent = financial.allocated_budget > 0
    ? Math.round((financial.paid_amount / financial.allocated_budget) * 100)
    : 0;

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

      {/* ── Top Bar ── */}
      <View style={styles.topBar}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn} hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }}>
          <Feather name="arrow-left" size={20} color={COLORS.text} />
        </TouchableOpacity>
        <View style={styles.topBarTitleWrap}>
          <Text style={styles.topBarTitle} numberOfLines={1}>Milestone Summary</Text>
          {projectTitle ? <Text style={styles.topBarSubtitle} numberOfLines={1}>{projectTitle}</Text> : null}
        </View>
        <TouchableOpacity onPress={onRefresh} style={styles.backBtn} hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }}>
          <Feather name="refresh-cw" size={18} color={COLORS.textSecondary} />
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={[styles.scrollContent, { paddingBottom: insets.bottom + 24 }]}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[COLORS.primary]} />}
      >
        {/* ═══════ HEADER CARD ═══════ */}
        <View style={styles.headerCard}>
          <View style={styles.headerTopRow}>
            <View style={styles.seqCircle}>
              <Text style={styles.seqText}>{header.sequence_order}</Text>
            </View>
            <View style={{ flex: 1, marginLeft: 10 }}>
              <Text style={styles.headerTitle}>{header.title}</Text>
              <Text style={styles.headerGroup}>{header.milestone_name}</Text>
            </View>
            <View style={[styles.statusBadge, { backgroundColor: statusColor(header.status).bg }]}>
              <Text style={[styles.statusBadgeText, { color: statusColor(header.status).fg }]}>
                {header.status?.replace(/_/g, ' ')}
              </Text>
            </View>
          </View>

          {header.description ? (
            <Text style={styles.headerDesc}>{header.description}</Text>
          ) : null}

          <View style={styles.divider} />

          {/* Dates row */}
          <View style={styles.datesRow}>
            <View style={styles.dateItem}>
              <Text style={styles.dateLabel}>{header.was_extended ? 'ORIGINAL DUE' : 'DUE DATE'}</Text>
              <Text style={styles.dateValue}>{formatDate(header.original_due_date)}</Text>
            </View>
            {header.was_extended ? (
              <>
                <Feather name="arrow-right" size={14} color={COLORS.textMuted} />
                <View style={styles.dateItem}>
                  <Text style={styles.dateLabel}>CURRENT DUE</Text>
                  <Text style={styles.dateValue}>{formatDate(header.current_due_date)}</Text>
                </View>
                <View style={[styles.statusBadge, { backgroundColor: COLORS.warningLight }]}>
                  <Feather name="clock" size={10} color={COLORS.warning} />
                  <Text style={[styles.statusBadgeText, { color: COLORS.warning, marginLeft: 3 }]}>
                    {header.extension_count}×
                  </Text>
                </View>
              </>
            ) : null}
          </View>

          {header.settlement_due_date ? (
            <View style={{ marginTop: 6 }}>
              <Text style={styles.dateLabel}>SETTLEMENT DUE</Text>
              <Text style={styles.dateValue}>{formatDate(header.settlement_due_date)}</Text>
            </View>
          ) : null}

          {/* Progress */}
          <View style={[styles.progressBarWrap, { marginTop: 10 }]}>
            <View style={styles.progressBarLabelRow}>
              <Text style={styles.progressBarLabel}>Progress</Text>
              <Text style={styles.progressBarPercent}>{header.percentage_progress}%</Text>
            </View>
            <View style={styles.progressBarTrack}>
              <View style={[styles.progressBarFill, { width: `${header.percentage_progress}%`, backgroundColor: COLORS.success }]} />
            </View>
          </View>
        </View>

        {/* ═══════ FINANCIAL ═══════ */}
        <SectionHeader title="Financial Overview" icon="dollar-sign" expanded={expandedSections.financial} onToggle={() => toggleSection('financial')} />
        {expandedSections.financial && (
          <View style={styles.sectionBody}>
            {/* Payment progress bar */}
            <View style={styles.progressBarWrap}>
              <View style={styles.progressBarLabelRow}>
                <Text style={styles.progressBarLabel}>Payment Progress</Text>
                <Text style={styles.progressBarPercent}>{paidPercent}%</Text>
              </View>
              <View style={styles.progressBarTrack}>
                <View style={[styles.progressBarFill, {
                  width: `${Math.min(paidPercent, 100)}%`,
                  backgroundColor: paidPercent > 100 ? COLORS.error : COLORS.info,
                }]} />
              </View>
            </View>

            <View style={styles.finGrid}>
              <FinCell label="Allocated Budget" value={formatCurrency(financial.allocated_budget)} />
              <FinCell label="Original Budget" value={formatCurrency(financial.original_budget)}
                dimmed={financial.original_budget === financial.allocated_budget} />
              <FinCell label="Total Paid" value={formatCurrency(financial.paid_amount)} color={COLORS.success} />
              <FinCell label="Pending" value={formatCurrency(financial.pending_amount)} color={COLORS.warning} />
              <FinCell label="Remaining" value={formatCurrency(financial.remaining_balance)} />
              {financial.over_amount > 0 ? (
                <FinCell label="Over Budget" value={formatCurrency(financial.over_amount)} color={COLORS.error} />
              ) : null}
            </View>

            {header.carry_forward_amount > 0 ? (
              <View style={styles.carryForwardBanner}>
                <Feather name="corner-down-right" size={13} color={COLORS.info} />
                <Text style={styles.carryForwardText}>
                  Carry Forward: {formatCurrency(header.carry_forward_amount)}
                </Text>
              </View>
            ) : null}
          </View>
        )}

        {/* ═══════ DATE HISTORY ═══════ */}
        {date_history.length > 0 && (
          <>
            <SectionHeader title={`Date History (${date_history.length})`} icon="calendar" expanded={expandedSections.dateHistory} onToggle={() => toggleSection('dateHistory')} />
            {expandedSections.dateHistory && (
              <View style={styles.sectionBody}>
                {date_history.map((dh, idx) => (
                  <View key={dh.id ?? idx} style={styles.dateHistoryRow}>
                    <View style={styles.dateHistoryDot} />
                    <View style={{ flex: 1 }}>
                      <Text style={styles.dateHistoryDates}>
                        {formatDate(dh.previous_date)} → {formatDate(dh.new_date)}
                      </Text>
                      {dh.change_reason || dh.extension_reason ? (
                        <Text style={styles.dateHistoryReason}>
                          {dh.change_reason || dh.extension_reason}
                        </Text>
                      ) : null}
                      <View style={styles.dateHistoryMeta}>
                        {dh.changed_by_name ? <Text style={styles.dateHistoryBy}>by {dh.changed_by_name}</Text> : null}
                        <Text style={styles.dateHistoryAt}>{formatDateTime(dh.changed_at)}</Text>
                      </View>
                    </View>
                  </View>
                ))}
              </View>
            )}
          </>
        )}

        {/* ═══════ PAYMENTS ═══════ */}
        <SectionHeader title={`Payments (${payments.length})`} icon="credit-card" expanded={expandedSections.payments} onToggle={() => toggleSection('payments')} />
        {expandedSections.payments && (
          <View style={styles.sectionBody}>
            {payments.length === 0 ? (
              <Text style={styles.emptyText}>No payment records yet.</Text>
            ) : (
              payments.map((p) => (
                <View key={p.payment_id} style={styles.paymentRow}>
                  <View style={styles.paymentRowTop}>
                    <Text style={styles.paymentType}>{p.payment_type?.replace(/_/g, ' ')}</Text>
                    <Text style={styles.paymentAmount}>{formatCurrency(p.amount)}</Text>
                  </View>
                  <View style={styles.paymentRowBottom}>
                    <View style={[styles.statusBadge, { backgroundColor: statusColor(p.payment_status).bg }]}>
                      <Text style={[styles.statusBadgeText, { color: statusColor(p.payment_status).fg }]}>
                        {p.payment_status}
                      </Text>
                    </View>
                    <Text style={styles.paymentDate}>{formatDate(p.transaction_date)}</Text>
                  </View>
                  {p.transaction_number ? <Text style={styles.paymentRef}>Ref: {p.transaction_number}</Text> : null}
                  {p.reason ? <Text style={styles.paymentReason}>{p.reason}</Text> : null}
                </View>
              ))
            )}
          </View>
        )}

        {/* ═══════ PROGRESS REPORTS ═══════ */}
        {progress_reports.length > 0 && (
          <>
            <SectionHeader title={`Progress Reports (${progress_reports.length})`} icon="file-text" expanded={expandedSections.progress} onToggle={() => toggleSection('progress')} />
            {expandedSections.progress && (
              <View style={styles.sectionBody}>
                {progress_reports.map((pr) => (
                  <View key={pr.progress_id} style={styles.reportRow}>
                    <View style={{ flex: 1 }}>
                      <Text style={styles.reportTitle}>{pr.purpose || 'Progress Report'}</Text>
                    </View>
                    <View style={{ alignItems: 'flex-end' }}>
                      <View style={[styles.statusBadge, { backgroundColor: statusColor(pr.progress_status).bg }]}>
                        <Text style={[styles.statusBadgeText, { color: statusColor(pr.progress_status).fg }]}>
                          {pr.progress_status?.replace(/_/g, ' ')}
                        </Text>
                      </View>
                      <Text style={styles.reportDate}>{formatDate(pr.submitted_at)}</Text>
                    </View>
                  </View>
                ))}
              </View>
            )}
          </>
        )}

        <Text style={styles.generatedAt}>
          Report generated {formatDateTime(data.generated_at)}
        </Text>
      </ScrollView>
    </View>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// Sub-components
// ─────────────────────────────────────────────────────────────────────────────
function SectionHeader({ title, icon, expanded, onToggle }: {
  title: string; icon: string; expanded: boolean; onToggle: () => void;
}) {
  return (
    <TouchableOpacity style={styles.sectionHeader} onPress={onToggle} activeOpacity={0.7}>
      <Feather name={icon as any} size={16} color={COLORS.primary} />
      <Text style={styles.sectionHeaderText}>{title}</Text>
      <Feather name={expanded ? 'chevron-up' : 'chevron-down'} size={16} color={COLORS.textSecondary} />
    </TouchableOpacity>
  );
}

function FinCell({ label, value, color, dimmed }: {
  label: string; value: string; color?: string; dimmed?: boolean;
}) {
  return (
    <View style={[styles.finGridItem, dimmed && { opacity: 0.5 }]}>
      <Text style={styles.finGridLabel}>{label}</Text>
      <Text style={[styles.finGridValue, color ? { color } : null]} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.7}>
        {value}
      </Text>
    </View>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// Styles
// ─────────────────────────────────────────────────────────────────────────────
const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: COLORS.background },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: COLORS.background, padding: 24 },
  scrollView: { flex: 1 },
  scrollContent: { paddingHorizontal: 20, paddingTop: 8 },
  loadingText: { marginTop: 12, fontSize: 13, color: COLORS.textSecondary },
  errorText: { marginTop: 12, fontSize: 14, color: COLORS.error, textAlign: 'center' },
  retryBtn: { marginTop: 16, paddingHorizontal: 20, paddingVertical: 10, backgroundColor: COLORS.primary, borderRadius: 6 },
  retryBtnText: { color: '#fff', fontWeight: '600', fontSize: 13 },

  // Top bar
  topBar: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: COLORS.border },
  backBtn: { width: 32, height: 32, justifyContent: 'center', alignItems: 'center' },
  topBarTitleWrap: { flex: 1, marginHorizontal: 8 },
  topBarTitle: { fontSize: 16, fontWeight: '700', color: COLORS.text },
  topBarSubtitle: { fontSize: 11, color: COLORS.textSecondary, marginTop: 1 },

  // Header card
  headerCard: { backgroundColor: COLORS.surface, borderRadius: 3, padding: 16, marginTop: 16, marginBottom: 8, borderWidth: 1, borderColor: COLORS.border },
  headerTopRow: { flexDirection: 'row', alignItems: 'center' },
  seqCircle: { width: 32, height: 32, borderRadius: 16, backgroundColor: COLORS.primaryLight, justifyContent: 'center', alignItems: 'center' },
  seqText: { fontSize: 14, fontWeight: '700', color: COLORS.primary },
  headerTitle: { fontSize: 16, fontWeight: '700', color: COLORS.text },
  headerGroup: { fontSize: 12, color: COLORS.textMuted, marginTop: 1 },
  headerDesc: { fontSize: 12, color: COLORS.textSecondary, marginTop: 8, lineHeight: 17 },

  divider: { height: 1, backgroundColor: COLORS.border, marginVertical: 10 },

  datesRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  dateItem: {},
  dateLabel: { fontSize: 9, fontWeight: '700', color: COLORS.textMuted, letterSpacing: 0.8, textTransform: 'uppercase' },
  dateValue: { fontSize: 13, fontWeight: '600', color: COLORS.text },

  // Status badge
  statusBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 4 },
  statusBadgeText: { fontSize: 10, fontWeight: '700', textTransform: 'uppercase', letterSpacing: 0.5 },

  // Progress bars
  progressBarWrap: { marginBottom: 4 },
  progressBarLabelRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 4 },
  progressBarLabel: { fontSize: 12, fontWeight: '600', color: COLORS.text },
  progressBarPercent: { fontSize: 12, fontWeight: '700', color: COLORS.text },
  progressBarTrack: { height: 6, backgroundColor: COLORS.borderLight, borderRadius: 3, overflow: 'hidden' },
  progressBarFill: { height: '100%', borderRadius: 3 },

  // Sections
  sectionHeader: { flexDirection: 'row', alignItems: 'center', paddingVertical: 12, paddingHorizontal: 4, marginTop: 8, gap: 8, borderBottomWidth: 1, borderBottomColor: COLORS.borderLight },
  sectionHeaderText: { flex: 1, fontSize: 14, fontWeight: '700', color: COLORS.text },
  sectionBody: { paddingVertical: 8 },

  // Financial grid
  finGrid: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 10, gap: 1, backgroundColor: COLORS.border, borderRadius: 3, overflow: 'hidden' },
  finGridItem: { width: '49%', flexGrow: 1, backgroundColor: COLORS.surface, padding: 10 },
  finGridLabel: { fontSize: 9, fontWeight: '700', color: COLORS.textMuted, letterSpacing: 0.6, textTransform: 'uppercase', marginBottom: 2 },
  finGridValue: { fontSize: 14, fontWeight: '700', color: COLORS.text },

  carryForwardBanner: { flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 8, paddingVertical: 6, paddingHorizontal: 10, backgroundColor: COLORS.infoLight, borderRadius: 4 },
  carryForwardText: { fontSize: 12, fontWeight: '600', color: COLORS.info },

  // Date history
  dateHistoryRow: { flexDirection: 'row', paddingVertical: 8, paddingLeft: 4, borderBottomWidth: 1, borderBottomColor: COLORS.borderLight },
  dateHistoryDot: { width: 8, height: 8, borderRadius: 4, backgroundColor: COLORS.warning, marginTop: 5, marginRight: 10 },
  dateHistoryDates: { fontSize: 13, fontWeight: '600', color: COLORS.text },
  dateHistoryReason: { fontSize: 11, color: COLORS.textMuted, fontStyle: 'italic', marginTop: 2 },
  dateHistoryMeta: { flexDirection: 'row', gap: 10, marginTop: 3 },
  dateHistoryBy: { fontSize: 10, color: COLORS.textSecondary },
  dateHistoryAt: { fontSize: 10, color: COLORS.textMuted },

  // Payments
  paymentRow: { borderBottomWidth: 1, borderBottomColor: COLORS.borderLight, paddingVertical: 8 },
  paymentRowTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  paymentType: { fontSize: 13, fontWeight: '600', color: COLORS.text, textTransform: 'capitalize' },
  paymentAmount: { fontSize: 14, fontWeight: '700', color: COLORS.text },
  paymentRowBottom: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 4 },
  paymentDate: { fontSize: 10, color: COLORS.textMuted },
  paymentRef: { fontSize: 10, color: COLORS.textSecondary, marginTop: 2 },
  paymentReason: { fontSize: 11, color: COLORS.textMuted, fontStyle: 'italic', marginTop: 2 },
  emptyText: { fontSize: 12, color: COLORS.textMuted, fontStyle: 'italic', paddingVertical: 8 },

  // Progress reports
  reportRow: { flexDirection: 'row', borderBottomWidth: 1, borderBottomColor: COLORS.borderLight, paddingVertical: 8, alignItems: 'center' },
  reportTitle: { fontSize: 13, fontWeight: '600', color: COLORS.text },
  reportDate: { fontSize: 10, color: COLORS.textMuted, marginTop: 4 },

  // Footer
  generatedAt: { textAlign: 'center', fontSize: 10, color: COLORS.textMuted, marginTop: 20, marginBottom: 8 },
});
