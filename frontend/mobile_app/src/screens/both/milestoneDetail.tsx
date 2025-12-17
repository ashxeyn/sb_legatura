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
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';

import ProgressReportForm from './ProgressReportForm';
import ProgressReportDetail from './ProgressReportDetail';
import { progress_service } from '../../services/progress_service';
import { milestones_service } from '../../services/milestones_service';
import { useEffect } from 'react';
import { Alert } from 'react-native';

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
      totalMilestones: number;
      isApproved: boolean;
      isCompleted?: boolean;
      userRole: 'owner' | 'contractor';
      userId: number;
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
    totalMilestones,
    isApproved,
    isCompleted,
    userRole,
    userId,
  } = route.params;

  // Debug: log the milestone item to see its structure
  console.log('MilestoneDetail - milestoneItem:', JSON.stringify(milestoneItem));
  console.log('MilestoneDetail - userId:', userId, 'userRole:', userRole);

  const [expandedReports, setExpandedReports] = useState<{ [key: number]: boolean }>({});
  const [showFullDetail, setShowFullDetail] = useState(false);
  const [showProgressForm, setShowProgressForm] = useState(false);
  const [selectedProgressReport, setSelectedProgressReport] = useState<any | null>(null);
  const [selectedProgressLoading, setSelectedProgressLoading] = useState(false);
  const [itemStatus, setItemStatus] = useState<string | undefined>(milestoneItem.item_status);

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

  useEffect(() => {
    let isMounted = true;
    setLoadingReports(true);
    setFetchError(null);
    console.log('Fetching progress reports for item_id:', milestoneItem.item_id, 'userId:', userId);
    progress_service.get_progress_by_item(userId, milestoneItem.item_id)
      .then(res => {
        console.log('Progress API response:', JSON.stringify(res));
        if (isMounted) {
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

          // Common locations
          progressList = tryArray(res.data?.data?.progress_list) || progressList;
          progressList = tryArray(res.data?.progress_list) || progressList;
          // Sometimes backend returns data directly as an array
          progressList = tryArray(res.data) || progressList;
          // Deep nested wrappers
          progressList = tryArray(res.data?.data) || progressList;

          if (progressList) {
            console.log('Setting progress reports from normalized list:', progressList.length);
            setProgressReports(progressList);
          } else {
            console.log('No progress reports found in response, clearing list');
            setProgressReports([]);
          }
        }
      })
      .catch(err => {
        console.error('Progress fetch error:', err);
        if (isMounted) setFetchError('Failed to fetch progress reports.');
      })
      .finally(() => {
        if (isMounted) setLoadingReports(false);
      });
    return () => { isMounted = false; };
  }, [userId, milestoneItem.item_id]);

  const toggleReportExpand = (reportId: number) => {
    setExpandedReports(prev => ({
      ...prev,
      [reportId]: !prev[reportId]
    }));
  };

  const allProgressApproved = progressReports.length > 0 && progressReports.every((p) => p.progress_status === 'approved');

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
          <TouchableOpacity style={styles.menuButton}>
            <Feather name="more-vertical" size={24} color={COLORS.text} />
          </TouchableOpacity>
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
        <TouchableOpacity style={styles.menuButton}>
          <Feather name="more-vertical" size={24} color={COLORS.text} />
        </TouchableOpacity>
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
          ) : progressReports.length === 0 ? (
            <View style={styles.noReportsContainer}>
              <View style={styles.noReportsIcon}>
                <Feather name="file-text" size={32} color={COLORS.textMuted} />
              </View>
              <Text style={styles.noReportsTitle}>No Progress Reports Yet</Text>
              <Text style={styles.noReportsText}>
                Progress reports from the contractor will appear here once work begins on this milestone.
              </Text>
            </View>
          ) : (
            <View style={styles.reportsTimeline}>
              {progressReports.map((report, index) => {
                const isLast = index === progressReports.length - 1;
                return (
                  <Pressable
                    key={report.progress_id}
                    style={({ pressed }) => [styles.reportItem, pressed && styles.reportItemPressed]}
                    onPress={async () => {
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                      try {
                        setSelectedProgressLoading(true);
                        let prog = null;

                        if (userRole === 'contractor') {
                          const res = await progress_service.get_progress(userId, report.progress_id);
=======
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                      // Owners already have the progress list (with files) from get_progress_by_item;
                      // only contractors should call the contractor-specific detail endpoint.
                      if (isContractor) {
                        try {
                          setSelectedProgressLoading(true);
                          const res = await progress_service.get_progress(userId, report.progress_id);
                          let prog = null;
<<<<<<< Updated upstream
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                          if (res && res.data) {
                            prog = res.data?.data || res.data || null;
                          }
                          if (prog && prog.progress_id === undefined && prog.progress) {
                            prog = prog.progress;
                          }
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                        } else {
                          // owner: avoid hitting contractor-only endpoint; fetch list for the item and find the progress
                          const res = await progress_service.get_progress_by_item(userId, milestoneItem.item_id);
                          if (res && res.data) {
                            const payload = res.data?.data || res.data;
                            // payload might be array or object with progress_list
                            const list = Array.isArray(payload) ? payload : (payload?.progress_list || payload?.data || payload);
                            const normalized = Array.isArray(list) ? list : [];
                            prog = normalized.find((p: any) => Number(p.progress_id) === Number(report.progress_id)) || null;
                          }
                        }

                        // Ensure selected progress always has the item_id (used by detail view)
                        if (!prog) prog = report;
                        if (prog && !prog.item_id) prog.item_id = milestoneItem.item_id;
                        setSelectedProgressReport(prog);
                      } catch (e) {
                        console.error('Failed to load progress details', e);
                        Alert.alert('Error', 'Failed to load progress details');
                        const fallback = { ...report, item_id: milestoneItem.item_id };
                        setSelectedProgressReport(fallback);
                      } finally {
                        setSelectedProgressLoading(false);
=======
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                      }
                    }}
                  >
                    {/* Timeline indicator */}
                    <View style={styles.reportTimelineLeft}>
                      <View style={[
                        styles.reportDot,
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
                        report.progress_status === 'approved' && styles.reportDotCompleted
                      ]}>
                        {report.progress_status === 'approved' && (
                          <Feather name="check" size={14} color={COLORS.surface} />
                        )}
=======
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                        report.progress_status === 'approved' && styles.reportDotCompleted,
                        report.progress_status === 'rejected' && styles.reportDotRejected
                      ]}>
                        {report.progress_status === 'approved' ? (
                          <Feather name="check" size={14} color={COLORS.surface} />
                        ) : report.progress_status === 'rejected' ? (
                          <Feather name="x" size={14} color={COLORS.surface} />
                        ) : null}
<<<<<<< Updated upstream
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
                      </View>
                      {!isLast && (() => {
                        const next = progressReports[index + 1];
                        const nextApproved = next && next.progress_status === 'approved';
                        return <View style={[styles.reportLine, nextApproved ? styles.reportLineApproved : styles.reportLinePending]} />;
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
        {/* Owner: Send payment (show if any approved) */}
        {isOwner && isApproved && progressReports.some(p => p.progress_status === 'approved') && itemStatus !== 'completed' && (
          <View style={styles.bottomRow}>
            <TouchableOpacity style={styles.sendPaymentButton}>
              <Text style={styles.sendPaymentButtonText}>Send payment receipt</Text>
            </TouchableOpacity>
          </View>
        )}

        {/* Owner: Set as Complete (only when all progress approved and not already completed) */}
        {isOwner && isApproved && allProgressApproved && itemStatus !== 'completed' && (
          <View style={styles.bottomRow}>
            <TouchableOpacity
              style={styles.completeButton}
              disabled={loadingReports}
              onPress={async () => {
                const itemId = milestoneItem.item_id;
                try {
                  setLoadingReports(true);
                  const res = await milestones_service.complete_milestone_item(itemId, userId);
                  if (res.success) {
                    Alert.alert('Success', 'Milestone item marked as complete.');
                    setItemStatus('completed');
                  } else {
                    Alert.alert('Error', res.message || 'Failed to mark as complete.');
                  }
                } catch (e) {
                  Alert.alert('Error', 'Unexpected error.');
                } finally {
                  setLoadingReports(false);
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
        {isContractor && isApproved && (
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
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
            userId={userId}
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
            onClose={() => setSelectedProgressReport(null)}
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
<<<<<<< Updated upstream
<<<<<<< Updated upstream
<<<<<<< Updated upstream
=======
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
  reportDotRejected: {
    backgroundColor: COLORS.error,
    borderColor: COLORS.error,
  },
<<<<<<< Updated upstream
<<<<<<< Updated upstream
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
=======
>>>>>>> Stashed changes
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
});
