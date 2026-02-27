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
  ProjectSummaryData,
  MilestoneBreakdownItem,
  ChangeHistoryEvent,
  PaymentRecord,
  BudgetHistoryRecord,
  ProgressReport,
} from '../../services/summary_service';

// ─────────────────────────────────────────────────────────────────────────────
// Colors (matches project palette)
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
  darkBlue: '#0A1628',
};

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────
const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
  }).format(amount);

const formatDate = (dateString: string | null | undefined) => {
  if (!dateString) return '—';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatDateTime = (dateString: string | null | undefined) => {
  if (!dateString) return '—';
  const date = new Date(dateString);
  const d = date.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });
  const t = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
  return `${d} ${t}`;
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
interface ProjectSummaryProps {
  route: {
    params: {
      projectId: number;
      projectTitle?: string;
      userRole?: 'owner' | 'contractor';
      userId?: number;
      /** Optional callback to navigate into a milestone summary */
      onMilestonePress?: (item: MilestoneBreakdownItem) => void;
    };
  };
  navigation: any;
}

// ─────────────────────────────────────────────────────────────────────────────
// Component
// ─────────────────────────────────────────────────────────────────────────────
export default function ProjectSummary({ route, navigation }: ProjectSummaryProps) {
  const insets = useSafeAreaInsets();
  const { projectId, projectTitle, onMilestonePress } = route.params;

  const [data, setData] = useState<ProjectSummaryData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [refreshing, setRefreshing] = useState(false);

  // Collapsible section state
  const [expandedSections, setExpandedSections] = useState<Record<string, boolean>>({
    overview: true,
    milestones: true,
    budget: false,
    timeline: false,
    payments: false,
    progress: false,
  });

  const toggleSection = (key: string) =>
    setExpandedSections(prev => ({ ...prev, [key]: !prev[key] }));

  // ── Fetch ──
  const fetchSummary = useCallback(async () => {
    try {
      setError(null);
      const res = await summary_service.getProjectSummary(projectId);
      // api_request wraps: { success, data: serverJson } where serverJson = { success, data: payload }
      const payload = res.data?.data ?? res.data;
      if (res.success && payload) {
        setData(payload);
      } else {
        setError(res.data?.message || res.message || 'Failed to load summary');
      }
    } catch (e: any) {
      setError(e.message || 'Unknown error');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [projectId]);

  useEffect(() => {
    fetchSummary();
  }, [fetchSummary]);

  const onRefresh = () => {
    setRefreshing(true);
    fetchSummary();
  };

  // ── Renders ──

  if (loading) {
    return (
      <View style={[styles.centered, { paddingTop: insets.top }]}>
        <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Loading project summary…</Text>
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

  const { header, overview, budget_history, milestones, change_history, payments, progress_reports } = data;

  const progressPercent = overview.total_milestones > 0
    ? Math.round((overview.completed_milestones / overview.total_milestones) * 100)
    : 0;

  const budgetUtilization = overview.current_budget > 0
    ? Math.round((overview.total_paid / overview.current_budget) * 100)
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
          <Text style={styles.topBarTitle} numberOfLines={1}>Project Summary</Text>
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
        {/* ═══════ A. PROJECT HEADER ═══════ */}
        <View style={styles.headerCard}>
          <Text style={styles.headerTitle}>{header.project_title}</Text>
          {header.project_description ? (
            <Text style={styles.headerDesc}>{header.project_description}</Text>
          ) : null}

          <View style={styles.headerMetaRow}>
            <View style={styles.headerMetaItem}>
              <Feather name="map-pin" size={13} color={COLORS.textSecondary} />
              <Text style={styles.headerMetaText} numberOfLines={2}>{header.project_location || '—'}</Text>
            </View>
            <View style={[styles.statusBadge, { backgroundColor: statusColor(header.status).bg, flexShrink: 0, marginLeft: 8 }]}>
              <Text style={[styles.statusBadgeText, { color: statusColor(header.status).fg }]}>
                {header.status?.replace(/_/g, ' ').toUpperCase()}
              </Text>
            </View>
          </View>

          <View style={styles.divider} />

          {/* Parties */}
          <View style={styles.partiesRow}>
            <View style={styles.partyBox}>
              <Text style={styles.partyLabel}>PROPERTY OWNER</Text>
              <Text style={styles.partyName}>{header.owner_name}</Text>
              {header.owner_email ? <Text style={styles.partyContact}>{header.owner_email}</Text> : null}
            </View>
            <View style={styles.partyBox}>
              <Text style={styles.partyLabel}>CONTRACTOR</Text>
              <Text style={styles.partyName}>{header.contractor_name}</Text>
              {header.contractor_company ? <Text style={styles.partyContact}>{header.contractor_company}</Text> : null}
            </View>
          </View>

          {/* Timeline strip */}
          <View style={styles.divider} />
          <View style={styles.timelineStrip}>
            <View style={styles.timelineItem}>
              <Text style={styles.timelineLabel}>START</Text>
              <Text style={styles.timelineValue}>{formatDate(header.original_start_date)}</Text>
            </View>
            <Feather name="arrow-right" size={14} color={COLORS.textMuted} />
            <View style={styles.timelineItem}>
              <Text style={styles.timelineLabel}>{header.was_extended ? 'CURRENT END' : 'END'}</Text>
              <Text style={styles.timelineValue}>{formatDate(header.current_end_date)}</Text>
            </View>
            {header.was_extended && header.original_end_date !== header.current_end_date ? (
              <View style={[styles.statusBadge, { backgroundColor: COLORS.warningLight, marginLeft: 8 }]}>
                <Feather name="clock" size={10} color={COLORS.warning} />
                <Text style={[styles.statusBadgeText, { color: COLORS.warning, marginLeft: 3 }]}>Extended</Text>
              </View>
            ) : null}
          </View>
        </View>

        {/* ═══════ B. EXECUTIVE OVERVIEW ═══════ */}
        <SectionHeader
          title="Executive Overview"
          icon="bar-chart-2"
          expanded={expandedSections.overview}
          onToggle={() => toggleSection('overview')}
        />
        {expandedSections.overview && (
          <View style={styles.sectionBody}>
            {/* Progress bar */}
            <View style={styles.progressBarWrap}>
              <View style={styles.progressBarLabelRow}>
                <Text style={styles.progressBarLabel}>Milestone Progress</Text>
                <Text style={styles.progressBarPercent}>{progressPercent}%</Text>
              </View>
              <View style={styles.progressBarTrack}>
                <View style={[styles.progressBarFill, { width: `${progressPercent}%`, backgroundColor: COLORS.success }]} />
              </View>
              <Text style={styles.progressBarSubtext}>
                {overview.completed_milestones} of {overview.total_milestones} milestones completed
              </Text>
            </View>

            {/* Budget utilization bar */}
            <View style={[styles.progressBarWrap, { marginTop: 12 }]}>
              <View style={styles.progressBarLabelRow}>
                <Text style={styles.progressBarLabel}>Budget Utilization</Text>
                <Text style={styles.progressBarPercent}>{budgetUtilization}%</Text>
              </View>
              <View style={styles.progressBarTrack}>
                <View style={[styles.progressBarFill, {
                  width: `${Math.min(budgetUtilization, 100)}%`,
                  backgroundColor: budgetUtilization > 100 ? COLORS.error : COLORS.info,
                }]} />
              </View>
            </View>

            {/* Financial grid */}
            <View style={styles.finGrid}>
              <FinGridItem label="Original Budget" value={formatCurrency(overview.original_budget)} />
              <FinGridItem label="Current Budget" value={formatCurrency(overview.current_budget)}
                highlight={overview.current_budget !== overview.original_budget} />
              <FinGridItem label="Total Paid" value={formatCurrency(overview.total_paid)} color={COLORS.success} />
              <FinGridItem label="Pending" value={formatCurrency(overview.total_pending)} color={COLORS.warning} />
              <FinGridItem label="Remaining" value={formatCurrency(overview.remaining_balance)} />
              <FinGridItem label="Payment Mode" value={overview.payment_mode.replace(/_/g, ' ')} isText />
            </View>
          </View>
        )}

        {/* ═══════ C. MILESTONE BREAKDOWN ═══════ */}
        <SectionHeader
          title={`Milestones (${milestones.length})`}
          icon="layers"
          expanded={expandedSections.milestones}
          onToggle={() => toggleSection('milestones')}
        />
        {expandedSections.milestones && (
          <View style={styles.sectionBody}>
            {milestones.map((m, idx) => (
              <TouchableOpacity
                key={m.item_id}
                style={styles.milestoneRow}
                activeOpacity={onMilestonePress ? 0.7 : 1}
                onPress={() => onMilestonePress?.(m)}
              >
                <View style={styles.milestoneRowHeader}>
                  <View style={styles.milestoneSeq}>
                    <Text style={styles.milestoneSeqText}>{m.sequence_order}</Text>
                  </View>
                  <View style={{ flex: 1, marginLeft: 10 }}>
                    <Text style={styles.milestoneTitle} numberOfLines={2}>{m.title}</Text>
                    <Text style={styles.milestoneSub}>{m.milestone_name}</Text>
                  </View>
                  <View style={[styles.statusBadge, { backgroundColor: statusColor(m.status).bg }]}>
                    <Text style={[styles.statusBadgeText, { color: statusColor(m.status).fg }]}>
                      {m.status?.replace(/_/g, ' ')}
                    </Text>
                  </View>
                </View>

                {/* Mini financial row */}
                <View style={styles.milestoneFinRow}>
                  <View style={styles.milestoneFinItem}>
                    <Text style={styles.milestoneFinLabel}>Budget</Text>
                    <Text style={styles.milestoneFinValue}>{formatCurrency(m.current_allocation)}</Text>
                  </View>
                  <View style={styles.milestoneFinItem}>
                    <Text style={styles.milestoneFinLabel}>Paid</Text>
                    <Text style={[styles.milestoneFinValue, { color: COLORS.success }]}>{formatCurrency(m.total_paid)}</Text>
                  </View>
                  <View style={styles.milestoneFinItem}>
                    <Text style={styles.milestoneFinLabel}>Due</Text>
                    <Text style={styles.milestoneFinValue}>{formatDate(m.current_due_date)}</Text>
                  </View>
                </View>

                {/* Extension badge */}
                {m.was_extended ? (
                  <View style={styles.extBadgeRow}>
                    <Feather name="clock" size={11} color={COLORS.warning} />
                    <Text style={styles.extBadgeText}>
                      Extended {m.extension_count}× (was {formatDate(m.original_due_date)})
                    </Text>
                  </View>
                ) : null}

                {/* Mini progress bar */}
                <View style={[styles.progressBarTrack, { marginTop: 8, height: 4 }]}>
                  <View style={[styles.progressBarFill, { width: `${m.percentage_progress}%`, backgroundColor: COLORS.info }]} />
                </View>
              </TouchableOpacity>
            ))}
          </View>
        )}

        {/* ═══════ D. BUDGET & CHANGE HISTORY ═══════ */}
        {budget_history.length > 0 && (
          <>
            <SectionHeader
              title={`Budget History (${budget_history.length})`}
              icon="trending-up"
              expanded={expandedSections.budget}
              onToggle={() => toggleSection('budget')}
            />
            {expandedSections.budget && (
              <View style={styles.sectionBody}>
                {budget_history.map((bh, idx) => (
                  <View key={idx} style={styles.historyRow}>
                    <View style={styles.historyDot} />
                    <View style={{ flex: 1 }}>
                      <View style={styles.historyRowHeader}>
                        <Text style={styles.historyAction}>
                          {bh.change_type ? `Budget ${bh.change_type}` : 'Timeline Update'}
                        </Text>
                        <View style={[styles.statusBadge, { backgroundColor: statusColor(bh.status).bg }]}>
                          <Text style={[styles.statusBadgeText, { color: statusColor(bh.status).fg }]}>
                            {bh.status}
                          </Text>
                        </View>
                      </View>
                      {bh.previous_budget != null && bh.updated_budget != null ? (
                        <Text style={styles.historyDetail}>
                          {formatCurrency(bh.previous_budget)} → {formatCurrency(bh.updated_budget)}
                        </Text>
                      ) : null}
                      {bh.previous_end_date && bh.proposed_end_date ? (
                        <Text style={styles.historyDetail}>
                          {formatDate(bh.previous_end_date)} → {formatDate(bh.proposed_end_date)}
                        </Text>
                      ) : null}
                      {bh.reason ? <Text style={styles.historyNote}>"{bh.reason}"</Text> : null}
                      <Text style={styles.historyDate}>{formatDate(bh.date_proposed)}</Text>
                    </View>
                  </View>
                ))}
              </View>
            )}
          </>
        )}

        {/* ═══════ E. TIMELINE & CHANGE HISTORY ═══════ */}
        {change_history.length > 0 && (
          <>
            <SectionHeader
              title={`Change Log (${change_history.length})`}
              icon="git-commit"
              expanded={expandedSections.timeline}
              onToggle={() => toggleSection('timeline')}
            />
            {expandedSections.timeline && (
              <View style={styles.sectionBody}>
                {change_history.map((evt, idx) => (
                  <View key={idx} style={styles.historyRow}>
                    <View style={[styles.historyDot, { backgroundColor: COLORS.info }]} />
                    <View style={{ flex: 1 }}>
                      <Text style={styles.historyAction}>{evt.action}</Text>
                      {evt.performed_by ? <Text style={styles.historyPerformer}>by {evt.performed_by}</Text> : null}
                      {evt.notes ? <Text style={styles.historyNote}>"{evt.notes}"</Text> : null}
                      {evt.reference ? <Text style={styles.historyRef}>{evt.reference}</Text> : null}
                      <Text style={styles.historyDate}>{formatDateTime(evt.date)}</Text>
                    </View>
                  </View>
                ))}
              </View>
            )}
          </>
        )}

        {/* ═══════ F. PAYMENTS HISTORY ═══════ */}
        <SectionHeader
          title={`Payments (${payments.records.length})`}
          icon="credit-card"
          expanded={expandedSections.payments}
          onToggle={() => toggleSection('payments')}
        />
        {expandedSections.payments && (
          <View style={styles.sectionBody}>
            {/* Totals strip */}
            <View style={styles.paymentTotals}>
              <PaymentTotalPill label="Approved" value={payments.total_approved} color={COLORS.success} />
              <PaymentTotalPill label="Pending" value={payments.total_pending} color={COLORS.warning} />
              <PaymentTotalPill label="Rejected" value={payments.total_rejected} color={COLORS.error} />
            </View>

            {payments.records.length === 0 ? (
              <Text style={styles.emptyText}>No payment records yet.</Text>
            ) : (
              payments.records.map((p) => (
                <View key={p.payment_id} style={styles.paymentRow}>
                  <View style={styles.paymentRowTop}>
                    <View style={{ flex: 1 }}>
                      <Text style={styles.paymentMilestone}>{p.milestone}</Text>
                      <Text style={styles.paymentType}>{p.payment_type?.replace(/_/g, ' ')}</Text>
                    </View>
                    <View style={{ alignItems: 'flex-end' }}>
                      <Text style={styles.paymentAmount}>{formatCurrency(p.amount)}</Text>
                      <View style={[styles.statusBadge, { backgroundColor: statusColor(p.status).bg, marginTop: 2 }]}>
                        <Text style={[styles.statusBadgeText, { color: statusColor(p.status).fg }]}>
                          {p.status}
                        </Text>
                      </View>
                    </View>
                  </View>
                  <View style={styles.paymentRowBottom}>
                    {p.transaction_number ? <Text style={styles.paymentTxn}>Ref: {p.transaction_number}</Text> : null}
                    <Text style={styles.paymentDate}>{formatDate(p.transaction_date)}</Text>
                  </View>
                </View>
              ))
            )}
          </View>
        )}

        {/* ═══════ G. PROGRESS REPORTS ═══════ */}
        {progress_reports.length > 0 && (
          <>
            <SectionHeader
              title={`Progress Reports (${progress_reports.length})`}
              icon="file-text"
              expanded={expandedSections.progress}
              onToggle={() => toggleSection('progress')}
            />
            {expandedSections.progress && (
              <View style={styles.sectionBody}>
                {progress_reports.map((pr) => (
                  <View key={pr.progress_id} style={styles.reportRow}>
                    <View style={{ flex: 1 }}>
                      <Text style={styles.reportTitle}>{pr.report_title || 'Progress Report'}</Text>
                      <Text style={styles.reportMilestone}>{pr.milestone}</Text>
                    </View>
                    <View style={{ alignItems: 'flex-end' }}>
                      <View style={[styles.statusBadge, { backgroundColor: statusColor(pr.status).bg }]}>
                        <Text style={[styles.statusBadgeText, { color: statusColor(pr.status).fg }]}>
                          {pr.status?.replace(/_/g, ' ')}
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

        {/* ── Generated timestamp ── */}
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

function FinGridItem({ label, value, color, highlight, isText }: {
  label: string; value: string; color?: string; highlight?: boolean; isText?: boolean;
}) {
  return (
    <View style={[styles.finGridItem, highlight && styles.finGridItemHighlight]}>
      <Text style={styles.finGridLabel}>{label}</Text>
      <Text style={[
        isText ? styles.finGridTextValue : styles.finGridValue,
        color ? { color } : null,
      ]} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.7}>
        {value}
      </Text>
    </View>
  );
}

function PaymentTotalPill({ label, value, color }: { label: string; value: number; color: string }) {
  return (
    <View style={[styles.paymentTotalPill, { borderColor: color }]}>
      <Text style={[styles.paymentTotalLabel, { color }]}>{label}</Text>
      <Text style={[styles.paymentTotalValue, { color }]} numberOfLines={1} adjustsFontSizeToFit minimumFontScale={0.6}>
        {formatCurrency(value)}
      </Text>
    </View>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// Styles
// ─────────────────────────────────────────────────────────────────────────────
const styles = StyleSheet.create({
  // Layout
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

  // Header card
  headerCard: { backgroundColor: COLORS.surface, borderRadius: 3, padding: 16, marginTop: 16, marginBottom: 8, borderWidth: 1, borderColor: COLORS.border },
  headerTitle: { fontSize: 17, fontWeight: '700', color: COLORS.text, marginBottom: 4 },
  headerDesc: { fontSize: 12, color: COLORS.textSecondary, marginBottom: 8, lineHeight: 17 },
  headerMetaRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  headerMetaItem: { flexDirection: 'row', alignItems: 'center', gap: 4, flex: 1, flexShrink: 1 },
  headerMetaText: { fontSize: 12, color: COLORS.textSecondary },

  divider: { height: 1, backgroundColor: COLORS.border, marginVertical: 10 },

  partiesRow: { flexDirection: 'row', gap: 12 },
  partyBox: { flex: 1 },
  partyLabel: { fontSize: 9, fontWeight: '700', color: COLORS.textMuted, letterSpacing: 0.8, textTransform: 'uppercase', marginBottom: 2 },
  partyName: { fontSize: 13, fontWeight: '600', color: COLORS.text },
  partyContact: { fontSize: 11, color: COLORS.textSecondary, marginTop: 1 },

  timelineStrip: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  timelineItem: {},
  timelineLabel: { fontSize: 9, fontWeight: '700', color: COLORS.textMuted, letterSpacing: 0.8, textTransform: 'uppercase' },
  timelineValue: { fontSize: 13, fontWeight: '600', color: COLORS.text },

  // Status badge
  statusBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 4 },
  statusBadgeText: { fontSize: 10, fontWeight: '700', textTransform: 'uppercase', letterSpacing: 0.5 },

  // Section header
  sectionHeader: { flexDirection: 'row', alignItems: 'center', paddingVertical: 12, paddingHorizontal: 4, marginTop: 8, gap: 8, borderBottomWidth: 1, borderBottomColor: COLORS.borderLight },
  sectionHeaderText: { flex: 1, fontSize: 14, fontWeight: '700', color: COLORS.text },
  sectionBody: { paddingVertical: 8 },

  // Progress bars
  progressBarWrap: { marginBottom: 4 },
  progressBarLabelRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 4 },
  progressBarLabel: { fontSize: 12, fontWeight: '600', color: COLORS.text },
  progressBarPercent: { fontSize: 12, fontWeight: '700', color: COLORS.text },
  progressBarTrack: { height: 6, backgroundColor: COLORS.borderLight, borderRadius: 3, overflow: 'hidden' },
  progressBarFill: { height: '100%', borderRadius: 3 },
  progressBarSubtext: { fontSize: 11, color: COLORS.textSecondary, marginTop: 3 },

  // Financial grid
  finGrid: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 12, gap: 1, backgroundColor: COLORS.border, borderRadius: 3, overflow: 'hidden' },
  finGridItem: { width: '49%', flexGrow: 1, backgroundColor: COLORS.surface, padding: 10 },
  finGridItemHighlight: { backgroundColor: COLORS.accentLight },
  finGridLabel: { fontSize: 9, fontWeight: '700', color: COLORS.textMuted, letterSpacing: 0.6, textTransform: 'uppercase', marginBottom: 2 },
  finGridValue: { fontSize: 14, fontWeight: '700', color: COLORS.text },
  finGridTextValue: { fontSize: 12, fontWeight: '600', color: COLORS.text, textTransform: 'capitalize' },

  // Milestone rows
  milestoneRow: { backgroundColor: COLORS.surface, borderWidth: 1, borderColor: COLORS.border, borderRadius: 3, padding: 12, marginBottom: 8 },
  milestoneRowHeader: { flexDirection: 'row', alignItems: 'center' },
  milestoneSeq: { width: 28, height: 28, borderRadius: 14, backgroundColor: COLORS.primaryLight, justifyContent: 'center', alignItems: 'center' },
  milestoneSeqText: { fontSize: 12, fontWeight: '700', color: COLORS.primary },
  milestoneTitle: { fontSize: 13, fontWeight: '600', color: COLORS.text },
  milestoneSub: { fontSize: 11, color: COLORS.textMuted, marginTop: 1 },
  milestoneFinRow: { flexDirection: 'row', marginTop: 8, gap: 8 },
  milestoneFinItem: { flex: 1 },
  milestoneFinLabel: { fontSize: 9, fontWeight: '700', color: COLORS.textMuted, textTransform: 'uppercase', letterSpacing: 0.6 },
  milestoneFinValue: { fontSize: 12, fontWeight: '600', color: COLORS.text, marginTop: 1 },
  extBadgeRow: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 6 },
  extBadgeText: { fontSize: 11, color: COLORS.warning, fontWeight: '500' },

  // History / timeline
  historyRow: { flexDirection: 'row', paddingVertical: 8, paddingLeft: 4, borderBottomWidth: 1, borderBottomColor: COLORS.borderLight },
  historyDot: { width: 8, height: 8, borderRadius: 4, backgroundColor: COLORS.accent, marginTop: 5, marginRight: 10 },
  historyRowHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 2 },
  historyAction: { fontSize: 13, fontWeight: '600', color: COLORS.text, flex: 1 },
  historyDetail: { fontSize: 12, color: COLORS.textSecondary, marginTop: 2 },
  historyNote: { fontSize: 11, color: COLORS.textMuted, fontStyle: 'italic', marginTop: 2 },
  historyPerformer: { fontSize: 11, color: COLORS.textSecondary, marginTop: 1 },
  historyRef: { fontSize: 10, color: COLORS.info, marginTop: 2 },
  historyDate: { fontSize: 10, color: COLORS.textMuted, marginTop: 3 },

  // Payments section
  paymentTotals: { flexDirection: 'row', gap: 6, marginBottom: 10 },
  paymentTotalPill: { flex: 1, borderWidth: 1, borderRadius: 4, padding: 8, alignItems: 'center' },
  paymentTotalLabel: { fontSize: 9, fontWeight: '700', textTransform: 'uppercase', letterSpacing: 0.6 },
  paymentTotalValue: { fontSize: 13, fontWeight: '700', marginTop: 2 },
  paymentRow: { borderBottomWidth: 1, borderBottomColor: COLORS.borderLight, paddingVertical: 8 },
  paymentRowTop: { flexDirection: 'row', justifyContent: 'space-between' },
  paymentMilestone: { fontSize: 13, fontWeight: '600', color: COLORS.text },
  paymentType: { fontSize: 11, color: COLORS.textMuted, textTransform: 'capitalize', marginTop: 1 },
  paymentAmount: { fontSize: 14, fontWeight: '700', color: COLORS.text },
  paymentRowBottom: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 4 },
  paymentTxn: { fontSize: 10, color: COLORS.textSecondary },
  paymentDate: { fontSize: 10, color: COLORS.textMuted },
  emptyText: { fontSize: 12, color: COLORS.textMuted, fontStyle: 'italic', paddingVertical: 8 },

  // Progress reports
  reportRow: { flexDirection: 'row', borderBottomWidth: 1, borderBottomColor: COLORS.borderLight, paddingVertical: 8, alignItems: 'center' },
  reportTitle: { fontSize: 13, fontWeight: '600', color: COLORS.text },
  reportMilestone: { fontSize: 11, color: COLORS.textMuted, marginTop: 1 },
  reportDate: { fontSize: 10, color: COLORS.textMuted, marginTop: 4 },

  // Footer
  generatedAt: { textAlign: 'center', fontSize: 10, color: COLORS.textMuted, marginTop: 20, marginBottom: 8 },
});
