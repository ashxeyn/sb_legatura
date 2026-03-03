// @ts-nocheck
import React, { useState, useEffect, useCallback, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  RefreshControl,
  Modal,
  Dimensions,
  Alert,
  Platform,
  StatusBar,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons, Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { api_request } from '../../config/api';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

/* ────────────────────── Colors ────────────────────── */
const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
  primaryDeep: '#B35E00',
  secondary: '#1A1A2E',
  accent: '#16213E',
  success: '#10B981',
  successLight: '#D1FAE5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
  errorLight: '#FEE2E2',
  info: '#3B82F6',
  infoLight: '#DBEAFE',
  background: '#F8FAFC',
  surface: '#FFFFFF',
  surfaceHover: '#F1F5F9',
  text: '#0F172A',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
  gold: '#D4A017',
  goldLight: '#FEF9E7',
  goldDark: '#B8860B',
};

/* ────────────────────── Interfaces ────────────────────── */
interface AiAnalyticsProps {
  userData?: {
    user_id?: number;
    username?: string;
    company_name?: string;
  };
  onClose: () => void;
}

interface Project {
  project_id: number;
  project_title: string;
  project_status: string;
}

interface PredictionLog {
  id?: number;
  project_id: number;
  project_title: string;
  prediction: string;
  delay_probability: number;
  weather_severity: number;
  ai_response_snapshot: string;
  created_at: string;
}

interface AiStats {
  total_analyses: number;
  on_time_predictions: number;
  delayed_predictions: number;
  avg_delay_probability: number;
}

interface AnalysisData {
  prediction: {
    prediction: string;
    delay_probability: number;
    reason?: string;
  };
  analysis_report?: {
    conclusion?: string;
    pacing_status?: {
      avg_delay_days?: number;
      details?: Array<{
        title: string;
        status: string;
        days_variance: number;
        pacing_label: string;
      }>;
    };
    contractor_audit?: {
      flagged?: boolean;
      status?: string;
    };
  };
  weather?: {
    total_rain?: number;
    avg_temp?: number;
    condition_text?: string;
  };
  weather_severity?: number;
  dds_recommendations?: string[];
  enso_state?: string;
}

/* ────────────────────── Component ────────────────────── */
export default function AiAnalytics({ userData, onClose }: AiAnalyticsProps) {
  const insets = useSafeAreaInsets();

  // State
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [isGold, setIsGold] = useState(false);
  const [aiStatus, setAiStatus] = useState('Offline');
  const [aiFeatures, setAiFeatures] = useState<string[]>([]);
  const [projects, setProjects] = useState<Project[]>([]);
  const [predictionLogs, setPredictionLogs] = useState<PredictionLog[]>([]);
  const [stats, setStats] = useState<AiStats>({
    total_analyses: 0,
    on_time_predictions: 0,
    delayed_predictions: 0,
    avg_delay_probability: 0,
  });

  // Analysis state
  const [selectedProjectId, setSelectedProjectId] = useState<number | null>(null);
  const [isAnalyzing, setIsAnalyzing] = useState(false);
  const [analysisResult, setAnalysisResult] = useState<AnalysisData | null>(null);
  const [analysisError, setAnalysisError] = useState<string | null>(null);

  // Project picker
  const [showProjectPicker, setShowProjectPicker] = useState(false);

  // Detail modal
  const [showDetailModal, setShowDetailModal] = useState(false);
  const [detailData, setDetailData] = useState<AnalysisData | null>(null);

  /* ───── fetch dashboard data ───── */
  const fetchData = useCallback(async () => {
    try {
      const response = await api_request('/api/contractor/ai-analytics', { method: 'GET' });

      if (response.success && response.data?.success) {
        const d = response.data;

        if (!d.is_gold) {
          setIsGold(false);
          setIsLoading(false);
          return;
        }

        setIsGold(true);
        setAiStatus(d.ai_status || 'Offline');
        setAiFeatures(d.ai_features || []);
        setProjects(d.projects || []);
        setPredictionLogs(d.prediction_logs || []);
        setStats(d.stats || {
          total_analyses: 0,
          on_time_predictions: 0,
          delayed_predictions: 0,
          avg_delay_probability: 0,
        });
      } else {
        console.error('AI Analytics fetch failed:', response.data?.message);
      }
    } catch (err) {
      console.error('AI Analytics error:', err);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchData();
    setRefreshing(false);
  };

  /* ───── Run Analysis ───── */
  const runAnalysis = async () => {
    if (!selectedProjectId) {
      Alert.alert('Select Project', 'Please select a project to analyze.');
      return;
    }

    setIsAnalyzing(true);
    setAnalysisResult(null);
    setAnalysisError(null);

    try {
      const response = await api_request(`/api/contractor/ai-analytics/analyze/${selectedProjectId}`, {
        method: 'POST',
      });

      if (response.success && response.data?.success) {
        setAnalysisResult(response.data.data);
        // Refresh data to get updated history
        fetchData();
      } else {
        setAnalysisError(response.data?.message || 'Analysis failed');
      }
    } catch (err: any) {
      setAnalysisError(err.message || 'Network error');
    } finally {
      setIsAnalyzing(false);
    }
  };

  /* ───── Show Details Modal ───── */
  const showDetails = (log: PredictionLog) => {
    try {
      const snapshot = typeof log.ai_response_snapshot === 'string'
        ? JSON.parse(log.ai_response_snapshot)
        : log.ai_response_snapshot;
      setDetailData(snapshot);
      setShowDetailModal(true);
    } catch (e) {
      Alert.alert('Error', 'Could not parse analysis data.');
    }
  };

  /* ───── Format helpers ───── */
  const timeAgo = (dateStr: string) => {
    const now = new Date();
    const d = new Date(dateStr);
    const diffMs = now.getTime() - d.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    const diffHrs = Math.floor(diffMins / 60);
    if (diffHrs < 24) return `${diffHrs}h ago`;
    const diffDays = Math.floor(diffHrs / 24);
    if (diffDays < 7) return `${diffDays}d ago`;
    return d.toLocaleDateString();
  };

  const formatPercent = (val: number) => `${(val * 100).toFixed(1)}%`;

  /* ───── Loading Screen ───── */
  if (isLoading) {
    return (
      <View style={[styles.container, { paddingTop: insets.top }]}>
        <StatusBar hidden />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Loading AI Analytics...</Text>
        </View>
      </View>
    );
  }

  /* ───── Not Gold Tier — Upgrade Prompt ───── */
  if (!isGold) {
    return (
      <View style={[styles.container, { paddingTop: insets.top }]}>
        <StatusBar hidden />

        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity style={styles.backButton} onPress={onClose}>
            <Feather name="arrow-left" size={24} color={COLORS.text} />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>AI Analytics</Text>
          <View style={{ width: 40 }} />
        </View>

        {/* Upgrade CTA */}
        <ScrollView contentContainerStyle={styles.upgradeContainer}>
          <View style={styles.upgradeCard}>
            <LinearGradient
              colors={['#F9E8A0', '#D4A017', '#B8860B']}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={styles.upgradeIconContainer}
            >
              <MaterialIcons name="auto-awesome" size={48} color="#FFFFFF" />
            </LinearGradient>

            <Text style={styles.upgradeTitle}>Unlock AI Analytics</Text>
            <Text style={styles.upgradeSubtitle}>
              AI-powered delay predictions for your construction projects
            </Text>

            <View style={styles.upgradeFeaturesList}>
              {[
                { icon: 'trending-up', text: 'Predict project delays before they happen' },
                { icon: 'cloud', text: 'Weather-aware risk assessments' },
                { icon: 'bar-chart-2', text: 'Milestone pacing analysis' },
                { icon: 'zap', text: 'AI-powered recommendations' },
                { icon: 'shield', text: 'Contractor performance audits' },
              ].map((item, i) => (
                <View key={i} style={styles.upgradeFeatureRow}>
                  <View style={styles.upgradeFeatureIcon}>
                    <Feather name={item.icon as any} size={18} color={COLORS.gold} />
                  </View>
                  <Text style={styles.upgradeFeatureText}>{item.text}</Text>
                </View>
              ))}
            </View>

            <View style={styles.upgradeBadge}>
              <MaterialIcons name="workspace-premium" size={20} color={COLORS.goldDark} />
              <Text style={styles.upgradeBadgeText}>Gold Tier Exclusive Feature</Text>
            </View>

            <TouchableOpacity
              style={styles.upgradeButton}
              activeOpacity={0.8}
              onPress={() => {
                onClose();
                // Navigate to subscription screen via global setter
                setTimeout(() => {
                  if (global.set_app_state) {
                    global.set_app_state('subscription');
                  }
                }, 100);
              }}
            >
              <LinearGradient
                colors={[COLORS.gold, COLORS.goldDark]}
                start={{ x: 0, y: 0 }}
                end={{ x: 1, y: 0 }}
                style={styles.upgradeButtonGradient}
              >
                <MaterialIcons name="workspace-premium" size={22} color="#FFFFFF" />
                <Text style={styles.upgradeButtonText}>Upgrade to Gold</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        </ScrollView>
      </View>
    );
  }

  /* ───── Gold Tier — Full AI Analytics ───── */
  const selectedProject = projects.find(p => p.project_id === selectedProjectId);

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar hidden />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backButton} onPress={onClose}>
          <Feather name="arrow-left" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>AI Analytics</Text>
        <View style={[styles.statusBadge, aiStatus === 'Online' ? styles.statusOnline : styles.statusOffline]}>
          <View style={[styles.statusDot, aiStatus === 'Online' ? styles.dotOnline : styles.dotOffline]} />
          <Text style={[styles.statusText, aiStatus === 'Online' ? styles.statusTextOnline : styles.statusTextOffline]}>
            {aiStatus}
          </Text>
        </View>
      </View>

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[COLORS.primary]} tintColor={COLORS.primary} />
        }
      >
        {/* Stats Cards */}
        <View style={styles.statsRow}>
          <View style={[styles.statCard, { borderLeftColor: COLORS.info }]}>
            <Text style={styles.statValue}>{stats.total_analyses}</Text>
            <Text style={styles.statLabel}>Analyses</Text>
          </View>
          <View style={[styles.statCard, { borderLeftColor: COLORS.success }]}>
            <Text style={[styles.statValue, { color: COLORS.success }]}>{stats.on_time_predictions}</Text>
            <Text style={styles.statLabel}>On-Time</Text>
          </View>
          <View style={[styles.statCard, { borderLeftColor: COLORS.error }]}>
            <Text style={[styles.statValue, { color: COLORS.error }]}>{stats.delayed_predictions}</Text>
            <Text style={styles.statLabel}>Delayed</Text>
          </View>
          <View style={[styles.statCard, { borderLeftColor: COLORS.warning }]}>
            <Text style={[styles.statValue, { color: COLORS.warning }]}>{stats.avg_delay_probability}%</Text>
            <Text style={styles.statLabel}>Avg Risk</Text>
          </View>
        </View>

        {/* AI Features */}
        {aiFeatures.length > 0 && (
          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.featuresRow} contentContainerStyle={{ paddingHorizontal: 20 }}>
            {aiFeatures.map((f, i) => (
              <View key={i} style={styles.featureBadge}>
                <Text style={styles.featureBadgeText}>{f}</Text>
              </View>
            ))}
          </ScrollView>
        )}

        {/* Run New Analysis */}
        <View style={styles.section}>
          <View style={styles.sectionHeaderRow}>
            <Feather name="activity" size={18} color={COLORS.info} />
            <Text style={styles.sectionTitle}>Run New Analysis</Text>
          </View>

          {projects.length > 0 ? (
            <View style={styles.analysisCard}>
              {/* Project Picker */}
              <TouchableOpacity
                style={styles.projectPicker}
                activeOpacity={0.7}
                onPress={() => setShowProjectPicker(true)}
              >
                <Feather name="folder" size={18} color={COLORS.textSecondary} />
                <Text style={[styles.projectPickerText, !selectedProject && { color: COLORS.textMuted }]} numberOfLines={1}>
                  {selectedProject ? `${selectedProject.project_title} (${selectedProject.project_status})` : 'Select a project...'}
                </Text>
                <Feather name="chevron-down" size={18} color={COLORS.textMuted} />
              </TouchableOpacity>

              {/* Analyze Button */}
              <TouchableOpacity
                style={[styles.analyzeButton, isAnalyzing && styles.analyzeButtonDisabled]}
                activeOpacity={0.8}
                onPress={runAnalysis}
                disabled={isAnalyzing}
              >
                <LinearGradient
                  colors={isAnalyzing ? ['#94A3B8', '#94A3B8'] : [COLORS.info, '#2563EB']}
                  style={styles.analyzeButtonGradient}
                >
                  {isAnalyzing ? (
                    <ActivityIndicator size="small" color="#FFFFFF" />
                  ) : (
                    <MaterialIcons name="psychology" size={20} color="#FFFFFF" />
                  )}
                  <Text style={styles.analyzeButtonText}>
                    {isAnalyzing ? 'Analyzing...' : 'Analyze Now'}
                  </Text>
                </LinearGradient>
              </TouchableOpacity>

              {/* Analysis Result (inline) */}
              {analysisResult && (
                <View style={styles.resultCard}>
                  <Text style={styles.resultTitle}>Analysis Complete</Text>
                  {analysisResult.analysis_report?.conclusion && (
                    <View style={styles.resultConclusion}>
                      <View style={styles.conclusionBar} />
                      <Text style={styles.resultConclusionText}>
                        {analysisResult.analysis_report.conclusion}
                      </Text>
                    </View>
                  )}
                  <View style={styles.resultMetrics}>
                    <View style={styles.resultMetricItem}>
                      <Text style={styles.resultMetricLabel}>Verdict</Text>
                      <Text style={[
                        styles.resultMetricValue,
                        { color: analysisResult.prediction.prediction === 'DELAYED' ? COLORS.error : COLORS.success }
                      ]}>
                        {analysisResult.prediction.prediction}
                      </Text>
                    </View>
                    <View style={styles.resultMetricItem}>
                      <Text style={styles.resultMetricLabel}>Confidence</Text>
                      <Text style={styles.resultMetricValue}>
                        {formatPercent(analysisResult.prediction.delay_probability)}
                      </Text>
                    </View>
                  </View>
                  <TouchableOpacity
                    style={styles.viewFullReportBtn}
                    onPress={() => { setDetailData(analysisResult); setShowDetailModal(true); }}
                  >
                    <Text style={styles.viewFullReportText}>View Full Report</Text>
                    <Feather name="arrow-right" size={14} color={COLORS.info} />
                  </TouchableOpacity>
                </View>
              )}

              {/* Analysis Error */}
              {analysisError && (
                <View style={styles.errorCard}>
                  <Feather name="alert-circle" size={18} color={COLORS.error} />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.errorTitle}>Analysis Failed</Text>
                    <Text style={styles.errorMessage}>{analysisError}</Text>
                  </View>
                </View>
              )}
            </View>
          ) : (
            <View style={styles.emptyProjectsCard}>
              <Feather name="folder" size={32} color={COLORS.border} />
              <Text style={styles.emptyProjectsTitle}>No Projects Yet</Text>
              <Text style={styles.emptyProjectsText}>
                Once you're assigned to a project, you'll be able to run AI analysis.
              </Text>
            </View>
          )}
        </View>

        {/* Prediction History */}
        <View style={styles.section}>
          <View style={styles.sectionHeaderRow}>
            <Feather name="clock" size={18} color={COLORS.textSecondary} />
            <Text style={styles.sectionTitle}>Prediction History</Text>
          </View>

          {predictionLogs.length === 0 ? (
            <View style={styles.emptyHistoryCard}>
              <MaterialIcons name="psychology" size={40} color={COLORS.border} />
              <Text style={styles.emptyHistoryText}>No predictions yet. Run your first analysis above!</Text>
            </View>
          ) : (
            predictionLogs.map((log, idx) => {
              const isDelayed = log.prediction === 'DELAYED';
              return (
                <TouchableOpacity
                  key={log.id || idx}
                  style={styles.historyCard}
                  activeOpacity={0.7}
                  onPress={() => showDetails(log)}
                >
                  <View style={styles.historyCardHeader}>
                    <View style={[styles.verdictBadge, isDelayed ? styles.verdictDelayed : styles.verdictOnTime]}>
                      <Feather
                        name={isDelayed ? 'alert-triangle' : 'check-circle'}
                        size={12}
                        color={isDelayed ? COLORS.error : COLORS.success}
                      />
                      <Text style={[styles.verdictText, { color: isDelayed ? COLORS.error : COLORS.success }]}>
                        {log.prediction}
                      </Text>
                    </View>
                    <Text style={styles.historyTime}>{timeAgo(log.created_at)}</Text>
                  </View>
                  <Text style={styles.historyProject} numberOfLines={1}>{log.project_title}</Text>
                  <View style={styles.historyMeta}>
                    <Text style={styles.historyRisk}>Risk: {formatPercent(log.delay_probability)}</Text>
                    <View style={styles.historyDots}>
                      {[1, 2, 3, 4, 5].map(i => (
                        <View
                          key={i}
                          style={[styles.severityDot, i <= (log.weather_severity || 0) ? styles.dotActive : styles.dotInactive]}
                        />
                      ))}
                    </View>
                  </View>
                  <View style={styles.historyViewBtn}>
                    <Text style={styles.historyViewText}>View Details</Text>
                    <Feather name="chevron-right" size={14} color={COLORS.info} />
                  </View>
                </TouchableOpacity>
              );
            })
          )}
        </View>

        <View style={{ height: 40 }} />
      </ScrollView>

      {/* ─── Project Picker Modal ─── */}
      <Modal visible={showProjectPicker} transparent animationType="slide" onRequestClose={() => setShowProjectPicker(false)}>
        <TouchableOpacity style={styles.pickerOverlay} activeOpacity={1} onPress={() => setShowProjectPicker(false)}>
          <View style={styles.pickerContainer}>
            <View style={styles.pickerHeader}>
              <Text style={styles.pickerTitle}>Select Project</Text>
              <TouchableOpacity onPress={() => setShowProjectPicker(false)}>
                <Feather name="x" size={22} color={COLORS.text} />
              </TouchableOpacity>
            </View>
            <ScrollView style={styles.pickerList}>
              {projects.map(p => (
                <TouchableOpacity
                  key={p.project_id}
                  style={[styles.pickerItem, p.project_id === selectedProjectId && styles.pickerItemSelected]}
                  onPress={() => { setSelectedProjectId(p.project_id); setShowProjectPicker(false); }}
                >
                  <View style={{ flex: 1 }}>
                    <Text style={styles.pickerItemTitle} numberOfLines={1}>{p.project_title}</Text>
                    <Text style={styles.pickerItemStatus}>{p.project_status}</Text>
                  </View>
                  {p.project_id === selectedProjectId && (
                    <Feather name="check-circle" size={20} color={COLORS.info} />
                  )}
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
        </TouchableOpacity>
      </Modal>

      {/* ─── Detail Report Modal ─── */}
      <Modal visible={showDetailModal} transparent animationType="slide" onRequestClose={() => setShowDetailModal(false)}>
        <View style={styles.detailOverlay}>
          <View style={[styles.detailContainer, { paddingTop: insets.top }]}>
            {/* Modal Header */}
            <View style={styles.detailHeader}>
              <View>
                <Text style={styles.detailHeaderTitle}>AI Analysis Report</Text>
                <Text style={styles.detailHeaderSub}>Detailed Insights</Text>
              </View>
              <TouchableOpacity style={styles.detailCloseBtn} onPress={() => setShowDetailModal(false)}>
                <Feather name="x" size={22} color={COLORS.text} />
              </TouchableOpacity>
            </View>

            {detailData && (
              <ScrollView style={styles.detailScroll} showsVerticalScrollIndicator={false}>
                {/* Executive Summary */}
                <View style={styles.execSummary}>
                  <Text style={styles.execLabel}>EXECUTIVE SUMMARY</Text>
                  <Text style={styles.execText}>
                    "{detailData.analysis_report?.conclusion || 'Analysis complete.'}"
                  </Text>
                </View>

                {/* Risk Assessment */}
                <View style={styles.detailSection}>
                  <Text style={styles.detailSectionTitle}>Risk Assessment</Text>
                  <View style={styles.riskCard}>
                    <View style={styles.riskRow}>
                      <Text style={[
                        styles.riskVerdict,
                        { color: detailData.prediction.prediction === 'DELAYED' ? COLORS.error : COLORS.success }
                      ]}>
                        {detailData.prediction.prediction}
                      </Text>
                      <View style={styles.riskRight}>
                        <Text style={styles.riskProbLabel}>Probability</Text>
                        <Text style={styles.riskProbValue}>{formatPercent(detailData.prediction.delay_probability)}</Text>
                      </View>
                    </View>
                    {/* Progress bar */}
                    <View style={styles.riskBar}>
                      <View style={[
                        styles.riskBarFill,
                        {
                          width: `${detailData.prediction.delay_probability * 100}%`,
                          backgroundColor: detailData.prediction.prediction === 'DELAYED' ? COLORS.error : COLORS.success,
                        }
                      ]} />
                    </View>
                    {detailData.prediction.reason && (
                      <Text style={styles.riskReason}>{detailData.prediction.reason}</Text>
                    )}
                  </View>
                </View>

                {/* Environment Context */}
                <View style={styles.detailSection}>
                  <Text style={styles.detailSectionTitle}>Environment Context</Text>
                  <View style={styles.envCard}>
                    <View style={styles.envGrid}>
                      <View style={styles.envItem}>
                        <Feather name="cloud-rain" size={20} color={COLORS.info} />
                        <Text style={styles.envLabel}>Rainfall</Text>
                        <Text style={styles.envValue}>{detailData.weather?.total_rain || 0}mm</Text>
                      </View>
                      <View style={styles.envItem}>
                        <Feather name="thermometer" size={20} color={COLORS.warning} />
                        <Text style={styles.envLabel}>ENSO</Text>
                        <Text style={styles.envValue}>{detailData.enso_state || 'N/A'}</Text>
                      </View>
                    </View>
                    <View style={styles.envSeverityRow}>
                      <Text style={styles.envSeverityLabel}>Weather Severity</Text>
                      <View style={styles.envDots}>
                        {[1, 2, 3, 4, 5].map(i => (
                          <View
                            key={i}
                            style={[styles.severityDotLarge, i <= (detailData.weather_severity || 0) ? styles.dotActiveLarge : styles.dotInactiveLarge]}
                          />
                        ))}
                      </View>
                    </View>
                  </View>
                </View>

                {/* Milestone Pacing */}
                <View style={styles.detailSection}>
                  <Text style={styles.detailSectionTitle}>Milestone Pacing Analysis</Text>
                  {(() => {
                    const details = detailData.analysis_report?.pacing_status?.details || [];
                    if (details.length === 0) {
                      return (
                        <View style={styles.emptyMilestones}>
                          <Text style={styles.emptyMilestonesText}>No milestone data available yet.</Text>
                        </View>
                      );
                    }
                    return (
                      <View style={styles.milestonesCard}>
                        {details.map((item, idx) => {
                          const isRejected = item.status === 'rejected';
                          const isLate = item.days_variance > 0;
                          return (
                            <View key={idx} style={[styles.milestoneRow, idx < details.length - 1 && styles.milestoneRowBorder]}>
                              <View style={styles.milestoneInfo}>
                                <Text style={styles.milestoneName} numberOfLines={1}>{item.title}</Text>
                                <Text style={[styles.milestoneStatus, isRejected && { color: COLORS.error }]}>
                                  {item.status.toUpperCase()}
                                </Text>
                              </View>
                              <View style={styles.milestoneRight}>
                                <Text style={[styles.milestoneVariance, { color: isLate ? COLORS.warning : COLORS.success }]}>
                                  {item.days_variance > 0 ? '+' : ''}{item.days_variance}d
                                </Text>
                                <View style={[
                                  styles.milestoneBadge,
                                  {
                                    backgroundColor: isRejected ? COLORS.errorLight
                                      : isLate ? COLORS.warningLight : COLORS.successLight
                                  }
                                ]}>
                                  <Text style={[
                                    styles.milestoneBadgeText,
                                    {
                                      color: isRejected ? COLORS.error
                                        : isLate ? COLORS.warning : COLORS.success
                                    }
                                  ]}>
                                    {item.pacing_label}
                                  </Text>
                                </View>
                              </View>
                            </View>
                          );
                        })}
                        {/* Avg pacing footer */}
                        <View style={styles.milestoneFooter}>
                          <Text style={styles.milestoneFooterLabel}>Average Pacing:</Text>
                          <Text style={[
                            styles.milestoneFooterValue,
                            {
                              color: (detailData.analysis_report?.pacing_status?.avg_delay_days || 0) > 0
                                ? COLORS.error : COLORS.success
                            }
                          ]}>
                            {detailData.analysis_report?.pacing_status?.avg_delay_days || 0} days{' '}
                            {(detailData.analysis_report?.pacing_status?.avg_delay_days || 0) > 0 ? 'behind' : 'ahead'}
                          </Text>
                        </View>
                      </View>
                    );
                  })()}
                </View>

                {/* AI Recommendations */}
                <View style={styles.detailSection}>
                  <Text style={styles.detailSectionTitle}>AI Recommendations</Text>
                  {(detailData.dds_recommendations || []).length === 0 ? (
                    <Text style={styles.noRecsText}>No recommendations generated.</Text>
                  ) : (
                    detailData.dds_recommendations!.map((rec, idx) => {
                      let iconName = 'zap';
                      let iconColor = '#8B5CF6';
                      if (rec.includes('QUALITY') || rec.includes('REJECT')) {
                        iconName = 'alert-triangle'; iconColor = COLORS.error;
                      } else if (rec.includes('WEATHER') || rec.includes('RAIN')) {
                        iconName = 'cloud-off'; iconColor = COLORS.warning;
                      }

                      return (
                        <View key={idx} style={styles.recCard}>
                          <Feather name={iconName as any} size={18} color={iconColor} />
                          <Text style={styles.recText}>{rec}</Text>
                        </View>
                      );
                    })
                  )}
                </View>

                <View style={{ height: 32 }} />
              </ScrollView>
            )}
          </View>
        </View>
      </Modal>
    </View>
  );
}

/* ────────────────────── Styles ────────────────────── */
const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: COLORS.textSecondary,
    fontWeight: '500',
  },

  /* Header */
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 20,
    gap: 5,
  },
  statusOnline: { backgroundColor: COLORS.successLight },
  statusOffline: { backgroundColor: COLORS.errorLight },
  statusDot: { width: 7, height: 7, borderRadius: 4 },
  dotOnline: { backgroundColor: COLORS.success },
  dotOffline: { backgroundColor: COLORS.error },
  statusText: { fontSize: 11, fontWeight: '700' },
  statusTextOnline: { color: COLORS.success },
  statusTextOffline: { color: COLORS.error },

  /* Scroll */
  scrollView: { flex: 1 },
  scrollContent: { paddingBottom: 20 },

  /* Stats */
  statsRow: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    paddingTop: 16,
    gap: 8,
  },
  statCard: {
    flex: 1,
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 12,
    borderLeftWidth: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 4,
    elevation: 1,
  },
  statValue: {
    fontSize: 20,
    fontWeight: '800',
    color: COLORS.text,
  },
  statLabel: {
    fontSize: 10,
    fontWeight: '600',
    color: COLORS.textMuted,
    marginTop: 2,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },

  /* Features badges */
  featuresRow: {
    marginTop: 12,
  },
  featureBadge: {
    backgroundColor: COLORS.infoLight,
    paddingHorizontal: 12,
    paddingVertical: 5,
    borderRadius: 20,
    marginRight: 8,
  },
  featureBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    color: COLORS.info,
  },

  /* Sections */
  section: {
    paddingHorizontal: 16,
    marginTop: 20,
  },
  sectionHeaderRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
  },

  /* Analysis Card */
  analysisCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  projectPicker: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    borderRadius: 10,
    paddingHorizontal: 14,
    paddingVertical: 12,
    gap: 10,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  projectPickerText: {
    flex: 1,
    fontSize: 14,
    color: COLORS.text,
    fontWeight: '500',
  },
  analyzeButton: {
    borderRadius: 12,
    overflow: 'hidden',
    marginTop: 12,
  },
  analyzeButtonDisabled: { opacity: 0.7 },
  analyzeButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 13,
    gap: 8,
  },
  analyzeButtonText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '700',
  },

  /* Result */
  resultCard: {
    marginTop: 16,
    backgroundColor: '#F0F9FF',
    borderRadius: 12,
    padding: 16,
    borderWidth: 1,
    borderColor: '#BAE6FD',
  },
  resultTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  resultConclusion: {
    flexDirection: 'row',
    marginBottom: 12,
  },
  conclusionBar: {
    width: 3,
    backgroundColor: COLORS.info,
    borderRadius: 2,
    marginRight: 10,
  },
  resultConclusionText: {
    flex: 1,
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 20,
  },
  resultMetrics: {
    flexDirection: 'row',
    gap: 24,
    marginBottom: 12,
  },
  resultMetricItem: {},
  resultMetricLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  resultMetricValue: {
    fontSize: 22,
    fontWeight: '800',
    color: COLORS.text,
    marginTop: 2,
  },
  viewFullReportBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    alignSelf: 'flex-end',
  },
  viewFullReportText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.info,
  },

  /* Error */
  errorCard: {
    marginTop: 12,
    backgroundColor: '#FEF2F2',
    borderRadius: 10,
    padding: 14,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  errorTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.error,
  },
  errorMessage: {
    fontSize: 12,
    color: COLORS.error,
    marginTop: 2,
  },

  /* Empty states */
  emptyProjectsCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 32,
    alignItems: 'center',
  },
  emptyProjectsTitle: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginTop: 12,
  },
  emptyProjectsText: {
    fontSize: 13,
    color: COLORS.textMuted,
    textAlign: 'center',
    marginTop: 6,
    lineHeight: 19,
  },
  emptyHistoryCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 40,
    alignItems: 'center',
  },
  emptyHistoryText: {
    fontSize: 13,
    color: COLORS.textMuted,
    marginTop: 10,
    textAlign: 'center',
  },

  /* History cards */
  historyCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 14,
    padding: 14,
    marginBottom: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 6,
    elevation: 1,
  },
  historyCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  verdictBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
    gap: 4,
  },
  verdictDelayed: { backgroundColor: COLORS.errorLight },
  verdictOnTime: { backgroundColor: COLORS.successLight },
  verdictText: { fontSize: 11, fontWeight: '800' },
  historyTime: { fontSize: 11, color: COLORS.textMuted, fontWeight: '500' },
  historyProject: { fontSize: 14, fontWeight: '700', color: COLORS.text, marginBottom: 6 },
  historyMeta: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  historyRisk: { fontSize: 12, fontWeight: '600', color: COLORS.textSecondary },
  historyDots: { flexDirection: 'row', gap: 3 },
  severityDot: { width: 6, height: 6, borderRadius: 3 },
  dotActive: { backgroundColor: COLORS.warning },
  dotInactive: { backgroundColor: COLORS.border },
  historyViewBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
    gap: 3,
    paddingTop: 6,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },
  historyViewText: { fontSize: 12, fontWeight: '600', color: COLORS.info },

  /* Project Picker Modal */
  pickerOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  pickerContainer: {
    backgroundColor: COLORS.surface,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    maxHeight: '65%',
    paddingBottom: 30,
  },
  pickerHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  pickerTitle: { fontSize: 18, fontWeight: '700', color: COLORS.text },
  pickerList: { paddingHorizontal: 14, paddingTop: 10 },
  pickerItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 14,
    borderRadius: 12,
    marginBottom: 6,
    backgroundColor: COLORS.background,
  },
  pickerItemSelected: { backgroundColor: COLORS.infoLight, borderWidth: 1, borderColor: COLORS.info },
  pickerItemTitle: { fontSize: 14, fontWeight: '600', color: COLORS.text },
  pickerItemStatus: { fontSize: 12, color: COLORS.textMuted, marginTop: 2, textTransform: 'capitalize' },

  /* Upgrade Screen */
  upgradeContainer: {
    flexGrow: 1,
    padding: 20,
    justifyContent: 'center',
  },
  upgradeCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 24,
    padding: 28,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 20,
    elevation: 5,
  },
  upgradeIconContainer: {
    width: 96,
    height: 96,
    borderRadius: 48,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  upgradeTitle: {
    fontSize: 22,
    fontWeight: '800',
    color: COLORS.text,
    textAlign: 'center',
  },
  upgradeSubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    marginTop: 6,
    lineHeight: 20,
    paddingHorizontal: 10,
  },
  upgradeFeaturesList: {
    alignSelf: 'stretch',
    marginTop: 24,
    gap: 12,
  },
  upgradeFeatureRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  upgradeFeatureIcon: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: COLORS.goldLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  upgradeFeatureText: {
    flex: 1,
    fontSize: 14,
    color: COLORS.text,
    fontWeight: '500',
  },
  upgradeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.goldLight,
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 20,
    marginTop: 24,
  },
  upgradeBadgeText: {
    fontSize: 12,
    fontWeight: '700',
    color: COLORS.goldDark,
  },
  upgradeButton: {
    alignSelf: 'stretch',
    borderRadius: 14,
    overflow: 'hidden',
    marginTop: 20,
  },
  upgradeButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 15,
    gap: 8,
  },
  upgradeButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },

  /* Detail Modal */
  detailOverlay: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  detailContainer: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  detailHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 14,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  detailHeaderTitle: { fontSize: 17, fontWeight: '800', color: COLORS.text },
  detailHeaderSub: { fontSize: 11, color: COLORS.textMuted, fontWeight: '600', textTransform: 'uppercase', letterSpacing: 0.5, marginTop: 2 },
  detailCloseBtn: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: COLORS.background,
    justifyContent: 'center',
    alignItems: 'center',
  },
  detailScroll: { flex: 1, paddingHorizontal: 16 },

  /* Executive Summary */
  execSummary: {
    backgroundColor: '#EFF6FF',
    borderLeftWidth: 4,
    borderLeftColor: COLORS.info,
    borderRadius: 8,
    padding: 16,
    marginTop: 16,
  },
  execLabel: {
    fontSize: 10,
    fontWeight: '800',
    color: '#1E40AF',
    letterSpacing: 1,
    marginBottom: 6,
  },
  execText: {
    fontSize: 15,
    color: '#1E3A8A',
    lineHeight: 22,
    fontWeight: '500',
    fontStyle: 'italic',
  },

  /* Detail sections */
  detailSection: { marginTop: 20 },
  detailSectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 10,
  },

  /* Risk Card */
  riskCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 14,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 4,
    elevation: 1,
  },
  riskRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  riskVerdict: { fontSize: 26, fontWeight: '900' },
  riskRight: { alignItems: 'flex-end' },
  riskProbLabel: { fontSize: 11, color: COLORS.textMuted },
  riskProbValue: { fontSize: 20, fontWeight: '800', color: COLORS.text },
  riskBar: {
    height: 6,
    backgroundColor: COLORS.borderLight,
    borderRadius: 3,
    overflow: 'hidden',
  },
  riskBarFill: { height: 6, borderRadius: 3 },
  riskReason: { fontSize: 12, color: COLORS.textMuted, marginTop: 10, fontStyle: 'italic' },

  /* Environment Card */
  envCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 14,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 4,
    elevation: 1,
  },
  envGrid: { flexDirection: 'row', gap: 12 },
  envItem: {
    flex: 1,
    alignItems: 'center',
    backgroundColor: COLORS.background,
    borderRadius: 10,
    paddingVertical: 14,
  },
  envLabel: { fontSize: 11, color: COLORS.textMuted, marginTop: 6 },
  envValue: { fontSize: 15, fontWeight: '700', color: COLORS.text, marginTop: 2 },
  envSeverityRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 14,
    paddingTop: 14,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },
  envSeverityLabel: { fontSize: 12, color: COLORS.textMuted },
  envDots: { flexDirection: 'row', gap: 4 },
  severityDotLarge: { width: 10, height: 10, borderRadius: 5 },
  dotActiveLarge: { backgroundColor: COLORS.warning },
  dotInactiveLarge: { backgroundColor: COLORS.border },

  /* Milestones */
  milestoneRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 14,
  },
  milestoneRowBorder: { borderBottomWidth: 1, borderBottomColor: COLORS.borderLight },
  milestoneInfo: { flex: 1, marginRight: 10 },
  milestoneName: { fontSize: 13, fontWeight: '600', color: COLORS.text },
  milestoneStatus: { fontSize: 10, fontWeight: '700', color: COLORS.textMuted, textTransform: 'uppercase', marginTop: 2 },
  milestoneRight: { alignItems: 'flex-end' },
  milestoneVariance: { fontSize: 13, fontWeight: '700', fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace' },
  milestoneBadge: { paddingHorizontal: 6, paddingVertical: 2, borderRadius: 4, marginTop: 3 },
  milestoneBadgeText: { fontSize: 9, fontWeight: '800' },
  milestonesCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 14,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 4,
    elevation: 1,
  },
  milestoneFooter: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    alignItems: 'center',
    paddingVertical: 10,
    paddingHorizontal: 14,
    backgroundColor: COLORS.background,
    gap: 6,
  },
  milestoneFooterLabel: { fontSize: 11, fontWeight: '700', color: COLORS.textMuted },
  milestoneFooterValue: { fontSize: 13, fontWeight: '700' },
  emptyMilestones: { padding: 24, alignItems: 'center' },
  emptyMilestonesText: { fontSize: 13, color: COLORS.textMuted, fontStyle: 'italic' },

  /* Recommendations */
  recCard: {
    flexDirection: 'row',
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 14,
    gap: 12,
    marginBottom: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.04,
    shadowRadius: 4,
    elevation: 1,
    alignItems: 'flex-start',
  },
  recText: {
    flex: 1,
    fontSize: 13,
    color: COLORS.textSecondary,
    fontWeight: '500',
    lineHeight: 19,
  },
  noRecsText: { fontSize: 13, color: COLORS.textMuted, fontStyle: 'italic' },
});
