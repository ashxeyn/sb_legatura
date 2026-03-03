// @ts-nocheck
import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  RefreshControl,
  Modal,
  Alert,
  Platform,
  StatusBar,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { api_request } from '../../config/api';

/* ── Palette (matches system) ── */
const C = {
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
  bg: '#FFFFFF',
  surface: '#FFFFFF',
  text: '#1E3A5F',
  sub: '#64748B',
  muted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
  gold: '#D4A017',
  goldLight: '#FEF9E7',
  goldDark: '#B8860B',
};

/* ── Interfaces ── */
interface AiAnalyticsProps {
  userData?: { user_id?: number; username?: string; company_name?: string };
  onClose: () => void;
}
interface Project { project_id: number; project_title: string; project_status: string }
interface PredictionLog {
  id?: number; project_id: number; project_title: string; prediction: string;
  delay_probability: number; weather_severity: number; ai_response_snapshot: string; created_at: string;
}
interface AiStats { total_analyses: number; on_time_predictions: number; delayed_predictions: number; avg_delay_probability: number }
interface AnalysisData {
  prediction: { prediction: string; delay_probability: number; reason?: string };
  analysis_report?: {
    conclusion?: string;
    pacing_status?: { avg_delay_days?: number; details?: Array<{ title: string; status: string; days_variance: number; pacing_label: string }> };
    contractor_audit?: { flagged?: boolean; status?: string };
  };
  weather?: { total_rain?: number; avg_temp?: number; condition_text?: string };
  weather_severity?: number;
  dds_recommendations?: string[];
  enso_state?: string;
}

/* ── Helpers ── */
const pct = (v: number) => `${(v * 100).toFixed(1)}%`;
const timeAgo = (dateStr: string) => {
  const ms = Date.now() - new Date(dateStr).getTime();
  const m = Math.floor(ms / 60000);
  if (m < 1) return 'Just now';
  if (m < 60) return `${m}m ago`;
  const h = Math.floor(m / 60);
  if (h < 24) return `${h}h ago`;
  const d = Math.floor(h / 24);
  if (d < 7) return `${d}d ago`;
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

/* ── Reusable section header (collapsible) ── */
function SectionHead({ title, icon, count, expanded, onToggle }: {
  title: string; icon: string; count?: number; expanded?: boolean; onToggle?: () => void;
}) {
  const Wrap: any = onToggle ? TouchableOpacity : View;
  return (
    <Wrap style={s.secHead} onPress={onToggle} activeOpacity={0.7}>
      <Feather name={icon as any} size={15} color={C.primary} />
      <Text style={s.secHeadText}>{title}{count != null ? ` (${count})` : ''}</Text>
      {onToggle && <Feather name={expanded ? 'chevron-up' : 'chevron-down'} size={15} color={C.sub} />}
    </Wrap>
  );
}

/* ━━━━━━━━━━ Component ━━━━━━━━━━ */
export default function AiAnalytics({ userData, onClose }: AiAnalyticsProps) {
  const insets = useSafeAreaInsets();

  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [isGold, setIsGold] = useState(false);
  const [aiStatus, setAiStatus] = useState('Offline');
  const [aiFeatures, setAiFeatures] = useState<string[]>([]);
  const [projects, setProjects] = useState<Project[]>([]);
  const [predictionLogs, setPredictionLogs] = useState<PredictionLog[]>([]);
  const [stats, setStats] = useState<AiStats>({ total_analyses: 0, on_time_predictions: 0, delayed_predictions: 0, avg_delay_probability: 0 });

  const [selectedProjectId, setSelectedProjectId] = useState<number | null>(null);
  const [isAnalyzing, setIsAnalyzing] = useState(false);
  const [analysisResult, setAnalysisResult] = useState<AnalysisData | null>(null);
  const [analysisError, setAnalysisError] = useState<string | null>(null);

  const [showProjectPicker, setShowProjectPicker] = useState(false);
  const [showDetailModal, setShowDetailModal] = useState(false);
  const [detailData, setDetailData] = useState<AnalysisData | null>(null);

  // Collapsible sections in detail modal
  const [detailSections, setDetailSections] = useState<Record<string, boolean>>({ risk: true, env: true, pacing: true, recs: true });
  const toggleDetail = (k: string) => setDetailSections(p => ({ ...p, [k]: !p[k] }));

  /* ── Fetch ── */
  const fetchData = useCallback(async () => {
    try {
      const response = await api_request('/api/contractor/ai-analytics', { method: 'GET' });
      if (response.success && response.data?.success) {
        const d = response.data;
        if (!d.is_gold) { setIsGold(false); setIsLoading(false); return; }
        setIsGold(true);
        setAiStatus(d.ai_status || 'Offline');
        setAiFeatures(d.ai_features || []);
        setProjects(d.projects || []);
        setPredictionLogs(d.prediction_logs || []);
        setStats(d.stats || { total_analyses: 0, on_time_predictions: 0, delayed_predictions: 0, avg_delay_probability: 0 });
      }
    } catch (err) { console.error('AI Analytics error:', err); }
    finally { setIsLoading(false); }
  }, []);

  useEffect(() => { fetchData(); }, [fetchData]);
  const onRefresh = async () => { setRefreshing(true); await fetchData(); setRefreshing(false); };

  /* ── Run Analysis ── */
  const runAnalysis = async () => {
    if (!selectedProjectId) { Alert.alert('Select Project', 'Please select a project to analyze.'); return; }
    setIsAnalyzing(true); setAnalysisResult(null); setAnalysisError(null);
    try {
      const response = await api_request(`/api/contractor/ai-analytics/analyze/${selectedProjectId}`, { method: 'POST' });
      if (response.success && response.data?.success) { setAnalysisResult(response.data.data); fetchData(); }
      else { setAnalysisError(response.data?.message || 'Analysis failed'); }
    } catch (err: any) { setAnalysisError(err.message || 'Network error'); }
    finally { setIsAnalyzing(false); }
  };

  /* ── Show Details ── */
  const showDetails = (log: PredictionLog) => {
    try {
      const snapshot = typeof log.ai_response_snapshot === 'string' ? JSON.parse(log.ai_response_snapshot) : log.ai_response_snapshot;
      setDetailData(snapshot);
      setDetailSections({ risk: true, env: true, pacing: true, recs: true });
      setShowDetailModal(true);
    } catch { Alert.alert('Error', 'Could not parse analysis data.'); }
  };

  const selectedProject = projects.find(p => p.project_id === selectedProjectId);

  /* ━━ LOADING ━━ */
  if (isLoading) {
    return (
      <View style={s.container}>
        <StatusBar barStyle="dark-content" backgroundColor={C.bg} />
        <View style={s.centered}>
          <ActivityIndicator size="large" color={C.primary} />
          <Text style={s.loadingText}>Loading AI Analytics…</Text>
        </View>
      </View>
    );
  }

  /* ━━ NOT GOLD ━━ */
  if (!isGold) {
    return (
      <View style={s.container}>
        <StatusBar barStyle="dark-content" backgroundColor={C.bg} />
        <View style={s.topBar}>
          <TouchableOpacity onPress={onClose} style={s.backBtn} hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }}>
            <Feather name="arrow-left" size={20} color={C.text} />
          </TouchableOpacity>
          <Text style={s.topBarTitle}>AI Analytics</Text>
          <View style={{ width: 32 }} />
        </View>

        <ScrollView contentContainerStyle={s.upgradeScroll}>
          <View style={s.upgradeCard}>
            <LinearGradient colors={['#F9E8A0', '#D4A017', '#B8860B']} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={s.upgradeIcon}>
              <MaterialIcons name="auto-awesome" size={36} color="#FFF" />
            </LinearGradient>

            <Text style={s.upgradeTitle}>Unlock AI Analytics</Text>
            <Text style={s.upgradeSub}>AI-powered delay predictions for your construction projects</Text>

            <View style={s.divider} />

            {['Predict project delays before they happen',
              'Weather-aware risk assessments',
              'Milestone pacing analysis',
              'AI-powered recommendations',
              'Contractor performance audits',
            ].map((t, i) => (
              <View key={i} style={s.upgradeRow}>
                <Feather name="check" size={14} color={C.gold} />
                <Text style={s.upgradeRowText}>{t}</Text>
              </View>
            ))}

            <View style={s.upgradeBadge}>
              <MaterialIcons name="workspace-premium" size={14} color={C.goldDark} />
              <Text style={s.upgradeBadgeText}>Gold Tier Exclusive</Text>
            </View>

            <TouchableOpacity
              style={s.upgradeBtn}
              activeOpacity={0.8}
              onPress={() => { onClose(); setTimeout(() => { if (global.set_app_state) global.set_app_state('subscription'); }, 100); }}
            >
              <MaterialIcons name="workspace-premium" size={18} color="#FFF" />
              <Text style={s.upgradeBtnText}>Upgrade to Gold</Text>
            </TouchableOpacity>
          </View>
        </ScrollView>
      </View>
    );
  }

  /* ━━ GOLD DASHBOARD ━━ */
  return (
    <View style={s.container}>
      <StatusBar barStyle="dark-content" backgroundColor={C.bg} />

      {/* Top Bar */}
      <View style={s.topBar}>
        <TouchableOpacity onPress={onClose} style={s.backBtn} hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }}>
          <Feather name="arrow-left" size={20} color={C.text} />
        </TouchableOpacity>
        <View style={s.topBarCenter}>
          <Text style={s.topBarTitle}>AI Analytics</Text>
          {aiFeatures.length > 0 && (
            <Text style={s.topBarSub} numberOfLines={1}>{aiFeatures.join(' · ')}</Text>
          )}
        </View>
        <View style={[s.statusPill, aiStatus === 'Online' ? s.statusOn : s.statusOff]}>
          <View style={[s.statusDot, { backgroundColor: aiStatus === 'Online' ? C.success : C.error }]} />
          <Text style={[s.statusLabel, { color: aiStatus === 'Online' ? C.success : C.error }]}>{aiStatus}</Text>
        </View>
      </View>

      <ScrollView
        style={s.scroll}
        contentContainerStyle={s.scrollInner}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[C.primary]} tintColor={C.primary} />}
      >

        {/* ═══ OVERVIEW GRID ═══ */}
        <View style={s.overviewCard}>
          <Text style={s.overviewLabel}>OVERVIEW</Text>
          <View style={s.grid}>
            <View style={s.gridCell}>
              <Text style={s.gridNum}>{stats.total_analyses}</Text>
              <Text style={s.gridLabel}>Total Analyses</Text>
            </View>
            <View style={s.gridCell}>
              <Text style={[s.gridNum, { color: C.success }]}>{stats.on_time_predictions}</Text>
              <Text style={s.gridLabel}>On-Time</Text>
            </View>
            <View style={s.gridCell}>
              <Text style={[s.gridNum, { color: C.error }]}>{stats.delayed_predictions}</Text>
              <Text style={s.gridLabel}>Delayed</Text>
            </View>
            <View style={s.gridCell}>
              <Text style={[s.gridNum, { color: C.warning }]}>{stats.avg_delay_probability}%</Text>
              <Text style={s.gridLabel}>Avg. Risk</Text>
            </View>
          </View>
        </View>

        {/* ═══ RUN ANALYSIS ═══ */}
        <SectionHead title="Run Analysis" icon="play-circle" />
        <View style={s.sectionBody}>
          {projects.length > 0 ? (
            <>
              <TouchableOpacity style={s.picker} activeOpacity={0.7} onPress={() => setShowProjectPicker(true)}>
                <Feather name="briefcase" size={16} color={C.sub} />
                <Text style={[s.pickerText, !selectedProject && { color: C.muted }]} numberOfLines={1}>
                  {selectedProject ? selectedProject.project_title : 'Select a project…'}
                </Text>
                <Feather name="chevron-down" size={16} color={C.muted} />
              </TouchableOpacity>

              {selectedProject && (
                <Text style={s.pickerMeta}>Status: {selectedProject.project_status}</Text>
              )}

              <TouchableOpacity
                style={[s.primaryBtn, isAnalyzing && { opacity: 0.6 }]}
                activeOpacity={0.8}
                onPress={runAnalysis}
                disabled={isAnalyzing}
              >
                {isAnalyzing
                  ? <ActivityIndicator size="small" color="#FFF" />
                  : <MaterialIcons name="psychology" size={18} color="#FFF" />}
                <Text style={s.primaryBtnText}>{isAnalyzing ? 'Analyzing…' : 'Analyze Now'}</Text>
              </TouchableOpacity>

              {/* Inline result */}
              {analysisResult && (
                <View style={s.resultCard}>
                  <View style={s.resultRow}>
                    <View>
                      <Text style={s.resultLabel}>VERDICT</Text>
                      <Text style={[s.resultVerdict, { color: analysisResult.prediction.prediction === 'DELAYED' ? C.error : C.success }]}>
                        {analysisResult.prediction.prediction}
                      </Text>
                    </View>
                    <View style={{ alignItems: 'flex-end' }}>
                      <Text style={s.resultLabel}>DELAY PROBABILITY</Text>
                      <Text style={s.resultProb}>{pct(analysisResult.prediction.delay_probability)}</Text>
                    </View>
                  </View>

                  {analysisResult.analysis_report?.conclusion && (
                    <Text style={s.resultConclusion}>{analysisResult.analysis_report.conclusion}</Text>
                  )}

                  <TouchableOpacity
                    style={s.linkBtn}
                    onPress={() => { setDetailData(analysisResult); setDetailSections({ risk: true, env: true, pacing: true, recs: true }); setShowDetailModal(true); }}
                  >
                    <Text style={s.linkBtnText}>View Full Report</Text>
                    <Feather name="arrow-right" size={13} color={C.accent} />
                  </TouchableOpacity>
                </View>
              )}

              {/* Error */}
              {analysisError && (
                <View style={s.errorBanner}>
                  <Feather name="alert-circle" size={15} color={C.error} />
                  <Text style={s.errorBannerText}>{analysisError}</Text>
                </View>
              )}
            </>
          ) : (
            <View style={s.emptyState}>
              <Feather name="briefcase" size={24} color={C.border} />
              <Text style={s.emptyTitle}>No Projects</Text>
              <Text style={s.emptyText}>Once you're assigned to a project, you can run AI analysis.</Text>
            </View>
          )}
        </View>

        {/* ═══ PREDICTION HISTORY ═══ */}
        <SectionHead title="Prediction History" icon="clock" count={predictionLogs.length} />
        <View style={s.sectionBody}>
          {predictionLogs.length === 0 ? (
            <View style={s.emptyState}>
              <Feather name="bar-chart-2" size={24} color={C.border} />
              <Text style={s.emptyText}>No predictions yet — run your first analysis above.</Text>
            </View>
          ) : (
            predictionLogs.map((log, idx) => {
              const delayed = log.prediction === 'DELAYED';
              return (
                <TouchableOpacity key={log.id || idx} style={s.histRow} activeOpacity={0.7} onPress={() => showDetails(log)}>
                  <View style={s.histLeft}>
                    <View style={[s.histDot, { backgroundColor: delayed ? C.error : C.success }]} />
                    <View style={{ flex: 1 }}>
                      <Text style={s.histProject} numberOfLines={1}>{log.project_title}</Text>
                      <View style={s.histMeta}>
                        <Text style={s.histMetaText}>Risk {pct(log.delay_probability)}</Text>
                        <Text style={s.histMetaText}>·</Text>
                        <Text style={s.histMetaText}>Weather {log.weather_severity || 0}/5</Text>
                      </View>
                    </View>
                  </View>
                  <View style={s.histRight}>
                    <View style={[s.badge, { backgroundColor: delayed ? C.errorLight : C.successLight }]}>
                      <Text style={[s.badgeText, { color: delayed ? C.error : C.success }]}>{log.prediction}</Text>
                    </View>
                    <Text style={s.histTime}>{timeAgo(log.created_at)}</Text>
                  </View>
                </TouchableOpacity>
              );
            })
          )}
        </View>

        <View style={{ height: 32 }} />
      </ScrollView>

      {/* ═══ PROJECT PICKER MODAL ═══ */}
      <Modal visible={showProjectPicker} transparent animationType="slide" onRequestClose={() => setShowProjectPicker(false)}>
        <TouchableOpacity style={s.modalOverlay} activeOpacity={1} onPress={() => setShowProjectPicker(false)}>
          <View style={s.modalSheet}>
            <View style={s.modalHandle} />
            <View style={s.modalHeader}>
              <Text style={s.modalTitle}>Select Project</Text>
              <TouchableOpacity onPress={() => setShowProjectPicker(false)} hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}>
                <Feather name="x" size={20} color={C.text} />
              </TouchableOpacity>
            </View>
            <ScrollView style={s.modalList}>
              {projects.map(p => {
                const active = p.project_id === selectedProjectId;
                return (
                  <TouchableOpacity
                    key={p.project_id}
                    style={[s.modalItem, active && s.modalItemActive]}
                    onPress={() => { setSelectedProjectId(p.project_id); setShowProjectPicker(false); }}
                  >
                    <View style={{ flex: 1 }}>
                      <Text style={s.modalItemTitle} numberOfLines={1}>{p.project_title}</Text>
                      <Text style={s.modalItemSub}>{p.project_status}</Text>
                    </View>
                    {active && <Feather name="check" size={18} color={C.primary} />}
                  </TouchableOpacity>
                );
              })}
            </ScrollView>
          </View>
        </TouchableOpacity>
      </Modal>

      {/* ═══ DETAIL REPORT MODAL ═══ */}
      <Modal visible={showDetailModal} transparent animationType="slide" onRequestClose={() => setShowDetailModal(false)}>
        <View style={[s.container, { paddingTop: insets.top, backgroundColor: C.bg }]}>
          {/* Header */}
          <View style={s.topBar}>
            <TouchableOpacity onPress={() => setShowDetailModal(false)} style={s.backBtn} hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }}>
              <Feather name="arrow-left" size={20} color={C.text} />
            </TouchableOpacity>
            <View style={s.topBarCenter}>
              <Text style={s.topBarTitle}>Analysis Report</Text>
              <Text style={s.topBarSub}>Detailed Insights</Text>
            </View>
            <View style={{ width: 32 }} />
          </View>

          {detailData && (
            <ScrollView style={s.scroll} contentContainerStyle={[s.scrollInner, { paddingBottom: insets.bottom + 24 }]} showsVerticalScrollIndicator={false}>

              {/* Executive Summary */}
              {detailData.analysis_report?.conclusion && (
                <View style={s.summaryBanner}>
                  <Text style={s.summaryLabel}>SUMMARY</Text>
                  <Text style={s.summaryText}>{detailData.analysis_report.conclusion}</Text>
                </View>
              )}

              {/* Risk Assessment */}
              <SectionHead title="Risk Assessment" icon="shield" expanded={detailSections.risk} onToggle={() => toggleDetail('risk')} />
              {detailSections.risk && (
                <View style={s.sectionBody}>
                  <View style={s.riskGrid}>
                    <View style={s.riskGridCell}>
                      <Text style={s.gridLabel}>VERDICT</Text>
                      <Text style={[s.riskVerdict, { color: detailData.prediction.prediction === 'DELAYED' ? C.error : C.success }]}>
                        {detailData.prediction.prediction}
                      </Text>
                    </View>
                    <View style={s.riskGridCell}>
                      <Text style={s.gridLabel}>PROBABILITY</Text>
                      <Text style={s.riskProb}>{pct(detailData.prediction.delay_probability)}</Text>
                    </View>
                  </View>
                  {/* Bar */}
                  <View style={s.progressTrack}>
                    <View style={[s.progressFill, {
                      width: `${detailData.prediction.delay_probability * 100}%`,
                      backgroundColor: detailData.prediction.prediction === 'DELAYED' ? C.error : C.success,
                    }]} />
                  </View>
                  {detailData.prediction.reason && (
                    <Text style={s.riskReason}>{detailData.prediction.reason}</Text>
                  )}
                </View>
              )}

              {/* Environment */}
              <SectionHead title="Environment Context" icon="cloud" expanded={detailSections.env} onToggle={() => toggleDetail('env')} />
              {detailSections.env && (
                <View style={s.sectionBody}>
                  <View style={s.envGrid}>
                    <View style={s.envCell}>
                      <Text style={s.gridLabel}>RAINFALL</Text>
                      <Text style={s.envVal}>{detailData.weather?.total_rain || 0} mm</Text>
                    </View>
                    <View style={s.envCell}>
                      <Text style={s.gridLabel}>ENSO STATE</Text>
                      <Text style={s.envVal}>{detailData.enso_state || 'N/A'}</Text>
                    </View>
                    <View style={s.envCell}>
                      <Text style={s.gridLabel}>SEVERITY</Text>
                      <Text style={s.envVal}>{detailData.weather_severity || 0} / 5</Text>
                    </View>
                    {detailData.weather?.avg_temp != null && (
                      <View style={s.envCell}>
                        <Text style={s.gridLabel}>AVG TEMP</Text>
                        <Text style={s.envVal}>{detailData.weather.avg_temp}°C</Text>
                      </View>
                    )}
                  </View>
                </View>
              )}

              {/* Milestone Pacing */}
              <SectionHead title="Milestone Pacing" icon="trending-up" expanded={detailSections.pacing} onToggle={() => toggleDetail('pacing')} />
              {detailSections.pacing && (
                <View style={s.sectionBody}>
                  {(() => {
                    const details = detailData.analysis_report?.pacing_status?.details || [];
                    if (details.length === 0) return <Text style={s.emptyText}>No milestone data available yet.</Text>;
                    return (
                      <>
                        {details.map((item, idx) => {
                          const late = item.days_variance > 0;
                          const rejected = item.status === 'rejected';
                          const fg = rejected ? C.error : late ? C.warning : C.success;
                          return (
                            <View key={idx} style={s.paceRow}>
                              <View style={{ flex: 1 }}>
                                <Text style={s.paceTitle} numberOfLines={1}>{item.title}</Text>
                                <Text style={[s.paceSub, rejected && { color: C.error }]}>{item.status.replace(/_/g, ' ')}</Text>
                              </View>
                              <View style={{ alignItems: 'flex-end' }}>
                                <Text style={[s.paceVar, { color: fg }]}>{item.days_variance > 0 ? '+' : ''}{item.days_variance}d</Text>
                                <View style={[s.badge, { backgroundColor: rejected ? C.errorLight : late ? C.warningLight : C.successLight }]}>
                                  <Text style={[s.badgeText, { color: fg }]}>{item.pacing_label}</Text>
                                </View>
                              </View>
                            </View>
                          );
                        })}
                        <View style={s.paceFooter}>
                          <Text style={s.paceFooterLabel}>Average Pacing</Text>
                          <Text style={[s.paceFooterVal, {
                            color: (detailData.analysis_report?.pacing_status?.avg_delay_days || 0) > 0 ? C.error : C.success,
                          }]}>
                            {detailData.analysis_report?.pacing_status?.avg_delay_days || 0} days{' '}
                            {(detailData.analysis_report?.pacing_status?.avg_delay_days || 0) > 0 ? 'behind' : 'ahead'}
                          </Text>
                        </View>
                      </>
                    );
                  })()}
                </View>
              )}

              {/* Recommendations */}
              <SectionHead title="Recommendations" icon="message-circle" expanded={detailSections.recs} onToggle={() => toggleDetail('recs')} />
              {detailSections.recs && (
                <View style={s.sectionBody}>
                  {(detailData.dds_recommendations || []).length === 0 ? (
                    <Text style={s.emptyText}>No recommendations generated.</Text>
                  ) : (
                    detailData.dds_recommendations!.map((rec, idx) => (
                      <View key={idx} style={s.recRow}>
                        <Text style={s.recNum}>{idx + 1}</Text>
                        <Text style={s.recText}>{rec}</Text>
                      </View>
                    ))
                  )}
                </View>
              )}
            </ScrollView>
          )}
        </View>
      </Modal>
    </View>
  );
}

/* ━━━━━━━━━━ Styles ━━━━━━━━━━ */
const s = StyleSheet.create({
  container: { flex: 1, backgroundColor: C.bg },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 24 },
  loadingText: { marginTop: 12, fontSize: 13, color: C.sub },
  scroll: { flex: 1 },
  scrollInner: { paddingHorizontal: 20, paddingTop: 2, paddingBottom: 20 },

  /* Top Bar */
  topBar: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: C.border },
  backBtn: { width: 32, height: 32, justifyContent: 'center', alignItems: 'center' },
  topBarCenter: { flex: 1, marginHorizontal: 8 },
  topBarTitle: { fontSize: 16, fontWeight: '700', color: C.text },
  topBarSub: { fontSize: 11, color: C.sub, marginTop: 1 },
  statusPill: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 4, gap: 5 },
  statusOn: { backgroundColor: C.successLight },
  statusOff: { backgroundColor: C.errorLight },
  statusDot: { width: 6, height: 6, borderRadius: 3 },
  statusLabel: { fontSize: 10, fontWeight: '700' },

  /* Overview grid */
  overviewCard: { marginTop: 8, marginBottom: 4 },
  overviewLabel: { fontSize: 9, fontWeight: '700', color: C.muted, letterSpacing: 0.8, marginBottom: 8 },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: 1, backgroundColor: C.border, borderRadius: 3, overflow: 'hidden' },
  gridCell: { width: '49%', flexGrow: 1, backgroundColor: C.surface, padding: 12 },
  gridNum: { fontSize: 20, fontWeight: '700', color: C.text, fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace' },
  gridLabel: { fontSize: 9, fontWeight: '700', color: C.muted, letterSpacing: 0.6, textTransform: 'uppercase', marginBottom: 2 },

  /* Section header */
  secHead: { flexDirection: 'row', alignItems: 'center', paddingVertical: 10, paddingHorizontal: 4, marginTop: 6, gap: 8, borderBottomWidth: 1, borderBottomColor: C.borderLight },
  secHeadText: { flex: 1, fontSize: 14, fontWeight: '700', color: C.text },
  sectionBody: { paddingVertical: 8 },

  /* Picker */
  picker: { flexDirection: 'row', alignItems: 'center', gap: 8, borderWidth: 1, borderColor: C.border, borderRadius: 3, paddingHorizontal: 12, paddingVertical: 11 },
  pickerText: { flex: 1, fontSize: 14, fontWeight: '500', color: C.text },
  pickerMeta: { fontSize: 11, color: C.sub, marginTop: 4, marginLeft: 2 },

  /* Primary button */
  primaryBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: C.accent, borderRadius: 3, paddingVertical: 14, marginTop: 10 },
  primaryBtnText: { color: '#FFF', fontSize: 14, fontWeight: '700' },

  /* Result */
  resultCard: { borderWidth: 1, borderColor: C.border, borderRadius: 3, padding: 14, marginTop: 12 },
  resultRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  resultLabel: { fontSize: 9, fontWeight: '700', color: C.muted, letterSpacing: 0.6, textTransform: 'uppercase', marginBottom: 2 },
  resultVerdict: { fontSize: 18, fontWeight: '800' },
  resultProb: { fontSize: 18, fontWeight: '700', color: C.text },
  resultConclusion: { fontSize: 13, color: C.sub, lineHeight: 19, marginTop: 10, borderTopWidth: 1, borderTopColor: C.borderLight, paddingTop: 10 },
  linkBtn: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 10, alignSelf: 'flex-end' },
  linkBtnText: { fontSize: 13, fontWeight: '600', color: C.accent },

  /* Error */
  errorBanner: { flexDirection: 'row', alignItems: 'center', gap: 8, backgroundColor: C.errorLight, borderRadius: 3, padding: 12, marginTop: 10 },
  errorBannerText: { flex: 1, fontSize: 13, color: C.error, fontWeight: '500' },

  /* Empty */
  emptyState: { alignItems: 'center', paddingVertical: 24, gap: 8 },
  emptyTitle: { fontSize: 14, fontWeight: '600', color: C.sub },
  emptyText: { fontSize: 13, color: C.muted, textAlign: 'center', lineHeight: 19 },

  /* History */
  histRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: C.borderLight },
  histLeft: { flexDirection: 'row', alignItems: 'center', flex: 1, gap: 10, marginRight: 12 },
  histDot: { width: 8, height: 8, borderRadius: 4 },
  histProject: { fontSize: 14, fontWeight: '600', color: C.text },
  histMeta: { flexDirection: 'row', gap: 4, marginTop: 2 },
  histMetaText: { fontSize: 11, color: C.muted },
  histRight: { alignItems: 'flex-end', gap: 4 },
  histTime: { fontSize: 10, color: C.muted },

  /* Badge */
  badge: { paddingHorizontal: 6, paddingVertical: 2, borderRadius: 3 },
  badgeText: { fontSize: 10, fontWeight: '700', textTransform: 'uppercase', letterSpacing: 0.4 },

  /* Picker modal */
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.45)', justifyContent: 'flex-end' },
  modalSheet: { backgroundColor: C.surface, borderTopLeftRadius: 8, borderTopRightRadius: 8, maxHeight: '60%', paddingBottom: 28 },
  modalHandle: { width: 36, height: 4, borderRadius: 2, backgroundColor: C.border, alignSelf: 'center', marginTop: 10, marginBottom: 6 },
  modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 20, paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: C.border },
  modalTitle: { fontSize: 16, fontWeight: '700', color: C.text },
  modalList: { paddingHorizontal: 16, paddingTop: 8 },
  modalItem: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: C.borderLight },
  modalItemActive: { backgroundColor: C.primaryLight },
  modalItemTitle: { fontSize: 14, fontWeight: '600', color: C.text },
  modalItemSub: { fontSize: 11, color: C.muted, marginTop: 1, textTransform: 'capitalize' },

  /* Upgrade */
  upgradeScroll: { flexGrow: 1, padding: 20, justifyContent: 'center' },
  upgradeCard: { borderWidth: 1, borderColor: C.border, borderRadius: 3, padding: 24, alignItems: 'center' },
  upgradeIcon: { width: 64, height: 64, borderRadius: 3, justifyContent: 'center', alignItems: 'center', marginBottom: 16 },
  upgradeTitle: { fontSize: 18, fontWeight: '700', color: C.text },
  upgradeSub: { fontSize: 13, color: C.sub, textAlign: 'center', marginTop: 4, lineHeight: 19 },
  divider: { height: 1, backgroundColor: C.border, alignSelf: 'stretch', marginVertical: 16 },
  upgradeRow: { flexDirection: 'row', alignItems: 'center', gap: 10, alignSelf: 'stretch', paddingVertical: 5 },
  upgradeRowText: { flex: 1, fontSize: 13, color: C.text, fontWeight: '500' },
  upgradeBadge: { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: C.goldLight, paddingHorizontal: 10, paddingVertical: 4, borderRadius: 3, marginTop: 14 },
  upgradeBadgeText: { fontSize: 10, fontWeight: '700', color: C.goldDark },
  upgradeBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, backgroundColor: C.gold, borderRadius: 3, paddingVertical: 14, alignSelf: 'stretch', marginTop: 16 },
  upgradeBtnText: { color: '#FFF', fontSize: 14, fontWeight: '700' },

  /* Detail modal */
  summaryBanner: { backgroundColor: C.primaryLight, borderLeftWidth: 3, borderLeftColor: C.primary, borderRadius: 3, padding: 14, marginTop: 12 },
  summaryLabel: { fontSize: 9, fontWeight: '700', color: C.primary, letterSpacing: 0.8, marginBottom: 4 },
  summaryText: { fontSize: 14, color: C.text, lineHeight: 21, fontWeight: '500' },

  /* Risk */
  riskGrid: { flexDirection: 'row', gap: 1, backgroundColor: C.border, borderRadius: 3, overflow: 'hidden' },
  riskGridCell: { flex: 1, backgroundColor: C.surface, padding: 12 },
  riskVerdict: { fontSize: 22, fontWeight: '800', marginTop: 2 },
  riskProb: { fontSize: 22, fontWeight: '700', color: C.text, marginTop: 2 },
  progressTrack: { height: 6, backgroundColor: C.borderLight, borderRadius: 3, overflow: 'hidden', marginTop: 8 },
  progressFill: { height: '100%', borderRadius: 3 },
  riskReason: { fontSize: 12, color: C.sub, marginTop: 8, fontStyle: 'italic', lineHeight: 18 },

  /* Env */
  envGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 1, backgroundColor: C.border, borderRadius: 3, overflow: 'hidden' },
  envCell: { width: '49%', flexGrow: 1, backgroundColor: C.surface, padding: 10 },
  envVal: { fontSize: 15, fontWeight: '700', color: C.text, marginTop: 2 },

  /* Pacing */
  paceRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: C.borderLight },
  paceTitle: { fontSize: 13, fontWeight: '600', color: C.text },
  paceSub: { fontSize: 10, fontWeight: '600', color: C.muted, textTransform: 'uppercase', marginTop: 1 },
  paceVar: { fontSize: 13, fontWeight: '700', fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace' },
  paceFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 8, marginTop: 4, borderTopWidth: 1, borderTopColor: C.border },
  paceFooterLabel: { fontSize: 11, fontWeight: '600', color: C.sub },
  paceFooterVal: { fontSize: 13, fontWeight: '700' },

  /* Recs */
  recRow: { flexDirection: 'row', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: C.borderLight, gap: 10 },
  recNum: { width: 20, height: 20, borderRadius: 10, backgroundColor: C.primaryLight, textAlign: 'center', lineHeight: 20, fontSize: 11, fontWeight: '700', color: C.primary, overflow: 'hidden' },
  recText: { flex: 1, fontSize: 13, color: C.sub, lineHeight: 19, fontWeight: '500' },
});
