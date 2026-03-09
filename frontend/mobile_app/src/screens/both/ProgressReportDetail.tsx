// @ts-nocheck
import React, { useState, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  StatusBar,
  Linking,
  Alert,
  Image,
  Modal,
  TextInput,
  ActivityIndicator,
  Animated,
} from 'react-native';
import {
  PinchGestureHandler,
  PanGestureHandler,
  State,
  GestureHandlerRootView,
} from 'react-native-gesture-handler';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { api_config } from '../../config/api';
import { progress_service } from '../../services/progress_service';
import DisputeHistory from './disputeHistory';

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

interface ProgressFile {
  file_id: number;
  progress_id: number;
  file_path: string;
  original_name: string;
}

interface ProgressReport {
  progress_id: number;
  item_id: number;
  purpose: string;
  progress_status: 'submitted' | 'approved' | 'rejected' | 'deleted';
  submitted_at: string;
  files?: ProgressFile[];
  delete_reason?: string;
}

interface ProgressReportDetailProps {
  progressReport: ProgressReport;
  milestoneTitle: string;
  projectTitle: string;
  userRole: 'owner' | 'contractor';
  onClose: () => void;
  projectStatus?: string;
}

export default function progressReportDetail({
  progressReport,
  milestoneTitle,
  projectTitle,
  userRole,
  onClose,
  projectStatus,
}: ProgressReportDetailProps) {
  const insets = useSafeAreaInsets();
  const isProjectHalted = projectStatus === 'halt' || projectStatus === 'on_hold' || projectStatus === 'halted';
  const [selectedImage, setSelectedImage] = useState<string | null>(null);
  const [showMenu, setShowMenu] = useState(false);

  // Zoom / pan state for image preview
  const scale = useRef(new Animated.Value(1)).current;
  const scaleRef = useRef(1);
  const translateX = useRef(new Animated.Value(0)).current;
  const translateY = useRef(new Animated.Value(0)).current;
  const panOffsetX = useRef(0);
  const panOffsetY = useRef(0);

  const resetZoom = () => {
    scaleRef.current = 1;
    Animated.parallel([
      Animated.spring(scale, { toValue: 1, useNativeDriver: true }),
      Animated.spring(translateX, { toValue: 0, useNativeDriver: true }),
      Animated.spring(translateY, { toValue: 0, useNativeDriver: true }),
    ]).start();
    panOffsetX.current = 0;
    panOffsetY.current = 0;
  };

  const onPinchEvent = Animated.event(
    [{ nativeEvent: { scale } }],
    { useNativeDriver: true }
  );

  const onPinchStateChange = (event: any) => {
    if (event.nativeEvent.oldState === State.ACTIVE) {
      scaleRef.current *= event.nativeEvent.scale;
      if (scaleRef.current < 1) scaleRef.current = 1;
      if (scaleRef.current > 5) scaleRef.current = 5;
      Animated.spring(scale, { toValue: scaleRef.current, useNativeDriver: true }).start();
    }
  };

  const onPanEvent = Animated.event(
    [{ nativeEvent: { translationX: translateX, translationY: translateY } }],
    { useNativeDriver: true }
  );

  const onPanStateChange = (event: any) => {
    if (event.nativeEvent.oldState === State.ACTIVE) {
      panOffsetX.current += event.nativeEvent.translationX;
      panOffsetY.current += event.nativeEvent.translationY;
      translateX.setOffset(panOffsetX.current);
      translateX.setValue(0);
      translateY.setOffset(panOffsetY.current);
      translateY.setValue(0);
    }
  };
  const [showDisputeHistory, setShowDisputeHistory] = useState(false);

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'approved':
        return { bg: COLORS.successLight, text: COLORS.success };
      case 'rejected':
        return { bg: COLORS.errorLight, text: COLORS.error };
      case 'submitted':
        return { bg: COLORS.warningLight, text: COLORS.warning };
      default:
        return { bg: COLORS.borderLight, text: COLORS.textMuted };
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'approved':
        return 'Approved';
      case 'rejected':
        return 'Rejected';
      case 'submitted':
        return 'Pending Review';
      default:
        return status;
    }
  };

  const getFileExtension = (filename: string) => {
    return filename?.split('.').pop()?.toLowerCase() || '';
  };

  const getFileIcon = (filename: string) => {
    const ext = getFileExtension(filename);
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) return 'image';
    if (['pdf'].includes(ext)) return 'file-text';
    if (['doc', 'docx'].includes(ext)) return 'file';
    if (['zip'].includes(ext)) return 'archive';
    return 'paperclip';
  };

  const isImageFile = (filename: string) => {
    const ext = getFileExtension(filename);
    return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
  };

  const handleSendReport = () => {
    setShowMenu(false);
    Alert.alert(
      'File a Dispute',
      'Would you like to file a dispute or report an issue?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'File Dispute',
          onPress: () => {
            Alert.alert('Info', 'Please go to milestone detail to file a specific dispute');
          }
        }
      ]
    );
  };

  const handleReportHistory = () => {
    setShowMenu(false);
    setShowDisputeHistory(true);
  };

  const getFileUrl = (filePath: string) => {
    // Normalize file path and construct full URL for the file
    if (!filePath) {
      console.log('getFileUrl: empty filePath');
      return '';
    }
    
    // If it's already a full URL, return as-is
    if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
      console.log('getFileUrl: already full URL ->', filePath);
      return filePath;
    }

    // Remove any leading slashes
    let path = filePath.replace(/^\/+/, '');
    
    // Handle various path formats from Laravel storage:
    // - "progress_uploads/filename.jpg" (most common from storeAs)
    // - "storage/progress_uploads/filename.jpg"
    // - "/storage/progress_uploads/filename.jpg"
    // - "public/progress_uploads/filename.jpg"
    
    // Remove 'storage/' prefix if present
    if (path.startsWith('storage/')) {
      path = path.replace(/^storage\//, '');
    }
    
    // Remove 'public/' prefix if present (Laravel internal path)
    if (path.startsWith('public/')) {
      path = path.replace(/^public\//, '');
    }

    // Use the API file serving endpoint (bypasses CORS/symlink issues)
    const url = `${api_config.base_url}/api/files/${path}`;
    console.log('getFileUrl ->', filePath, '=>', url);
    return url;
  };

  const handleFilePress = (file: ProgressFile) => {
    const fileUrl = getFileUrl(file.file_path);
    console.log('handleFilePress url:', fileUrl, 'file:', JSON.stringify(file));
    
    if (!fileUrl) {
      Alert.alert('Error', 'File URL is invalid');
      return;
    }

    if (isImageFile(file.original_name)) {
      resetZoom();
      setSelectedImage(fileUrl);
    } else {
      // Open in browser for other file types
      Linking.openURL(fileUrl).catch(err => {
        Alert.alert('Error', 'Could not open file');
        console.error('Error opening file:', err);
      });
    }
  };

  const statusColors = getStatusColor(progressReport.progress_status);
  const files = progressReport.files || [];
  const imageFiles = files.filter(f => isImageFile(f.original_name));
  const otherFiles = files.filter(f => !isImageFile(f.original_name));
  const [localStatus, setLocalStatus] = useState(progressReport.progress_status);
  const [showRejectModal, setShowRejectModal] = useState(false);
  const [rejectReason, setRejectReason] = useState('');
  const [actionLoading, setActionLoading] = useState(false);
  const [approveBlockedModal, setApproveBlockedModal] = useState<{ visible: boolean; message: string }>({ visible: false, message: '' });
  const [deleteReason, setDeleteReason] = useState(progressReport.delete_reason || '');

  // Debug: log files data
  console.log('progressReportDetail - files:', JSON.stringify(files));
  console.log('progressReportDetail - imageFiles:', imageFiles.length, 'otherFiles:', otherFiles.length);

  // Image preview modal (zoomable + pannable)
  if (selectedImage) {
    return (
      <GestureHandlerRootView style={{ flex: 1 }}>
        <View style={[styles.previewScreen, { paddingTop: insets.top }]}>
          <StatusBar barStyle="light-content" backgroundColor="#000" />

          {/* Backdrop tap-to-close hint + pinch gesture wrapper */}
          <PanGestureHandler
            onGestureEvent={onPanEvent}
            onHandlerStateChange={onPanStateChange}
            minPointers={1}
            maxPointers={1}
          >
            <Animated.View style={{ flex: 1 }}>
              <PinchGestureHandler
                onGestureEvent={onPinchEvent}
                onHandlerStateChange={onPinchStateChange}
              >
                <Animated.View style={styles.imagePreviewContainer}>
                  <Animated.Image
                    source={{ uri: selectedImage }}
                    style={[
                      styles.previewImage,
                      {
                        transform: [
                          { scale },
                          { translateX },
                          { translateY },
                        ],
                      },
                    ]}
                    resizeMode="contain"
                    onError={(e) => {
                      console.error('Image preview failed to load', e.nativeEvent?.error);
                      Alert.alert('Preview error', 'Could not load image preview');
                    }}
                  />
                </Animated.View>
              </PinchGestureHandler>
            </Animated.View>
          </PanGestureHandler>

          {/* Top bar: close + reset zoom */}
          <View style={[styles.previewTopBar, { top: insets.top + 8 }]}>
            <TouchableOpacity
              onPress={() => { setSelectedImage(null); resetZoom(); }}
              style={styles.previewCloseBtn}
              hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }}
            >
              <Feather name="x" size={22} color="#FFF" />
            </TouchableOpacity>

            <TouchableOpacity
              onPress={resetZoom}
              style={styles.previewResetBtn}
              hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }}
            >
              <Feather name="maximize" size={18} color="#FFF" />
              <Text style={styles.previewResetText}>Reset</Text>
            </TouchableOpacity>
          </View>

          {/* Bottom hint */}
          <View style={[styles.previewHint, { bottom: insets.bottom + 16 }]}>
            <Text style={styles.previewHintText}>Pinch to zoom · Drag to pan · Tap ✕ to close</Text>
          </View>
        </View>
      </GestureHandlerRootView>
    );
  }

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="chevron-left" size={28} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Progress Report</Text>
        <TouchableOpacity style={styles.menuButton} onPress={() => setShowMenu(!showMenu)}>
          <Feather name="more-vertical" size={24} color={COLORS.text} />
        </TouchableOpacity>

        {showMenu && (
          <View style={styles.menuDropdown}>
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
        {/* ── Main Info Card ── */}
        <View style={styles.fdInfoCard}>
          <Text style={styles.fdInfoLabel}>PROGRESS REPORT</Text>

          {/* Status badge */}
          <View style={[styles.fdInfoBadge, { backgroundColor: statusColors.bg, alignSelf: 'flex-start', marginBottom: 14, gap: 5 }]}>
            <View style={[styles.fdInfoBadgeDot, { backgroundColor: statusColors.text }]} />
            <Feather
              name={progressReport.progress_status === 'approved' ? 'check' : progressReport.progress_status === 'rejected' ? 'x' : 'clock'}
              size={11}
              color={statusColors.text}
            />
            <Text style={[styles.fdInfoBadgeText, { color: statusColors.text }]}>
              {getStatusLabel(progressReport.progress_status)}
            </Text>
          </View>

          {/* Milestone title & project */}
          <Text style={styles.fdInfoTitle}>{milestoneTitle}</Text>
          <Text style={styles.fdInfoProject}>{projectTitle}</Text>

          {/* Submitted date */}
          <View style={styles.fdInfoDescSection}>
            <Text style={styles.fdInfoDescLabel}>Submitted</Text>
            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
              <Feather name="calendar" size={13} color={COLORS.textMuted} />
              <Text style={styles.fdInfoDescText}>{formatDate(progressReport.submitted_at)}</Text>
            </View>
          </View>

          {/* Description */}
          <View style={styles.fdInfoDescSection}>
            <Text style={styles.fdInfoDescLabel}>Description</Text>
            <Text style={[styles.fdInfoDescText, !progressReport.purpose && { color: COLORS.textMuted, fontStyle: 'italic' }]}>
              {progressReport.purpose || 'No description provided.'}
            </Text>
          </View>

          {/* Rejection reason (if rejected) */}
          {localStatus === 'rejected' && deleteReason ? (
            <View style={styles.fdInfoDescSection}>
              <Text style={[styles.fdInfoDescLabel, { color: COLORS.error }]}>Rejection Reason</Text>
              <View style={{ backgroundColor: COLORS.errorLight, borderRadius: 6, padding: 10 }}>
                <Text style={{ fontSize: 14, color: COLORS.error, lineHeight: 20 }}>{deleteReason}</Text>
              </View>
            </View>
          ) : null}

          {/* Attachments */}
          <View style={styles.fdInfoAttachSection}>
            <Text style={styles.fdInfoDescLabel}>
              Attachments{files.length > 0 ? ` (${files.length})` : ''}
            </Text>

            {files.length === 0 ? (
              <View style={styles.fdInfoNoAttach}>
                <Feather name="paperclip" size={16} color={COLORS.textMuted} />
                <Text style={{ fontSize: 13, color: COLORS.textMuted }}>No attachments</Text>
              </View>
            ) : (
              <>
                {/* Image Gallery */}
                {imageFiles.length > 0 && (
                  <View style={styles.imageGallery}>
                    {imageFiles.map((file) => {
                      const imageUrl = getFileUrl(file.file_path);
                      return (
                        <TouchableOpacity
                          key={file.file_id}
                          style={styles.imageThumbnail}
                          onPress={() => handleFilePress(file)}
                        >
                          <Image
                            source={{ uri: imageUrl }}
                            style={styles.thumbnailImage}
                            resizeMode="cover"
                            onError={(e) => {
                              console.error('Thumbnail failed to load:', imageUrl, e.nativeEvent?.error);
                            }}
                            onLoad={() => {
                              console.log('Thumbnail loaded successfully:', imageUrl);
                            }}
                          />
                          <View style={styles.imageOverlay}>
                            <Feather name="maximize-2" size={16} color="#FFF" />
                          </View>
                        </TouchableOpacity>
                      );
                    })}
                  </View>
                )}

                {/* Other Files */}
                {otherFiles.length > 0 && (
                  <View style={{ gap: 8, marginTop: imageFiles.length > 0 ? 8 : 0 }}>
                    {otherFiles.map((file) => (
                      <TouchableOpacity
                        key={file.file_id}
                        style={styles.fdInfoAttachItem}
                        onPress={() => handleFilePress(file)}
                      >
                        <View style={styles.fdInfoAttachIcon}>
                          <Feather name={getFileIcon(file.original_name)} size={18} color={COLORS.accent} />
                        </View>
                        <View style={{ flex: 1 }}>
                          <Text style={styles.fdInfoAttachName} numberOfLines={1}>
                            {file.original_name}
                          </Text>
                          <Text style={styles.fdInfoAttachType}>
                            {getFileExtension(file.original_name).toUpperCase()} file
                          </Text>
                        </View>
                        <Feather name="external-link" size={18} color={COLORS.textSecondary} />
                      </TouchableOpacity>
                    ))}
                  </View>
                )}
              </>
            )}
          </View>
        </View>

        <View style={{ height: 20 }} />
      </ScrollView>

      {/* Owner actions: Approve / Reject — fixed at bottom */}
      {!isProjectHalted && userRole === 'owner' && localStatus === 'submitted' && (
        <View style={styles.actionsContainer}>
          <TouchableOpacity
            style={[styles.actionButton, styles.rejectButton]}
            onPress={() => {
              setRejectReason('');
              setShowRejectModal(true);
            }}
          >
            <Text style={styles.actionButtonText}>Reject</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.actionButton, styles.approveButton]}
            onPress={() => {
              Alert.alert('Approve Progress', 'Are you sure you want to approve this progress report?', [
                { text: 'Cancel', style: 'cancel' },
                {
                  text: 'Approve',
                  onPress: async () => {
                    try {
                      setActionLoading(true);
                      const res = await progress_service.approve_progress(progressReport.progress_id);
                      if (res.success) {
                        setLocalStatus('approved');
                        Alert.alert('Approved', 'Progress report approved.');
                      } else {
                        if (res.status === 409 || (res.message && res.message.toLowerCase().includes('previous'))) {
                          setApproveBlockedModal({ visible: true, message: res.message || 'Previous progress must be approved first.' });
                        } else {
                          Alert.alert('Error', res.message || 'Failed to approve progress');
                        }
                      }
                    } catch (e) {
                      Alert.alert('Error', 'Unexpected error approving progress');
                    } finally {
                      setActionLoading(false);
                    }
                  }
                }
              ]);
            }}
          >
            {actionLoading ? <ActivityIndicator color="#fff" /> : <Text style={styles.actionButtonText}>Approve</Text>}
          </TouchableOpacity>
        </View>
      )}

      {/* Reject reason modal */}
      <Modal
        visible={showRejectModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowRejectModal(false)}
      >
        <View style={styles.rejectBackdrop}>
          <View style={styles.rejectContainer}>
            <Text style={styles.rejectTitle}>Reject Progress Report</Text>
            <Text style={styles.rejectLabel}>Reason (visible to contractor)</Text>
            <TextInput
              value={rejectReason}
              onChangeText={setRejectReason}
              placeholder="Enter reason for rejection"
              multiline
              style={styles.rejectInput}
            />
            <View style={styles.rejectButtonsRow}>
              <TouchableOpacity style={styles.rejectCancelBtn} onPress={() => setShowRejectModal(false)}>
                <Text style={styles.rejectCancelText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.rejectConfirmBtn}
                onPress={async () => {
                  try {
                    setActionLoading(true);
                    const reasonToSend = rejectReason || 'Rejected by owner';
                    const res = await progress_service.reject_progress(progressReport.progress_id, reasonToSend);
                    if (res.success) {
                      setLocalStatus('rejected');
                      setDeleteReason(reasonToSend);
                      setShowRejectModal(false);
                      Alert.alert('Rejected', 'Progress report rejected.');
                    } else {
                      Alert.alert('Error', res.message || 'Failed to reject progress');
                    }
                  } catch (e) {
                    Alert.alert('Error', 'Unexpected error rejecting progress');
                  } finally {
                    setActionLoading(false);
                  }
                }}
              >
                {actionLoading ? (
                  <ActivityIndicator color="#fff" />
                ) : (
                  <Text style={styles.rejectConfirmText}>Reject</Text>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Approval blocked modal */}
      <Modal
        visible={approveBlockedModal.visible}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setApproveBlockedModal({ visible: false, message: '' })}
      >
        <View style={styles.blockBackdrop}>
          <View style={styles.blockContainer}>
            <Text style={styles.blockTitle}>Cannot Approve</Text>
            <Text style={styles.blockMessage}>{approveBlockedModal.message}</Text>
            <TouchableOpacity style={styles.blockOkBtn} onPress={() => setApproveBlockedModal({ visible: false, message: '' })}>
              <Text style={styles.blockOkText}>OK</Text>
            </TouchableOpacity>
          </View>
        </View>
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
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  backButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
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
  fullDetailScrollContent: {
    paddingHorizontal: 16,
    paddingTop: 16,
    paddingBottom: 20,
  },

  // ── Info Card (mirrors milestoneDetail fdInfoCard) ──
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
    marginBottom: 10,
  },
  fdInfoBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  fdInfoBadgeDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    marginRight: 5,
  },
  fdInfoBadgeText: {
    fontSize: 10,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginLeft: 4,
  },
  fdInfoTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    lineHeight: 26,
    marginBottom: 4,
  },
  fdInfoProject: {
    fontSize: 12,
    color: COLORS.accent,
    fontWeight: '600',
    marginBottom: 4,
  },
  fdInfoDescSection: {
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
    paddingTop: 14,
    marginTop: 14,
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
    marginBottom: 6,
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

  // Image Gallery
  imageGallery: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 4,
  },
  imageThumbnail: {
    width: 90,
    height: 90,
    borderRadius: 8,
    overflow: 'hidden',
    position: 'relative',
    backgroundColor: COLORS.borderLight,
  },
  thumbnailImage: {
    width: '100%',
    height: '100%',
  },
  imageOverlay: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    backgroundColor: 'rgba(0,0,0,0.5)',
    padding: 5,
    borderTopLeftRadius: 6,
  },

  // Image Preview (full-screen zoomable)
  previewScreen: {
    flex: 1,
    backgroundColor: '#000',
  },
  imagePreviewContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  previewImage: {
    width: '100%',
    height: '100%',
  },
  previewTopBar: {
    position: 'absolute',
    left: 0,
    right: 0,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    zIndex: 20,
  },
  previewCloseBtn: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(0,0,0,0.7)',
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.35)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  previewResetBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 20,
    backgroundColor: 'rgba(0,0,0,0.65)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
  },
  previewResetText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#FFF',
  },
  previewHint: {
    position: 'absolute',
    left: 0,
    right: 0,
    alignItems: 'center',
    zIndex: 20,
  },
  previewHintText: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.5)',
    backgroundColor: 'rgba(0,0,0,0.4)',
    paddingHorizontal: 14,
    paddingVertical: 6,
    borderRadius: 20,
  },

  // Action Buttons (fixed bottom)
  actionsContainer: {
    flexDirection: 'row',
    gap: 12,
    paddingHorizontal: 16,
    paddingVertical: 16,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    backgroundColor: COLORS.surface,
  },
  actionButton: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  approveButton: {
    backgroundColor: COLORS.success,
  },
  rejectButton: {
    backgroundColor: COLORS.error,
  },
  actionButtonText: {
    color: COLORS.surface,
    fontSize: 16,
    fontWeight: '700',
  },

  // Approval-blocked modal
  blockBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.4)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  blockContainer: {
    width: '100%',
    maxWidth: 520,
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 20,
    alignItems: 'center',
  },
  blockTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
  },
  blockMessage: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    marginBottom: 20,
  },
  blockOkBtn: {
    backgroundColor: COLORS.accent,
    paddingHorizontal: 24,
    paddingVertical: 10,
    borderRadius: 8,
  },
  blockOkText: {
    color: COLORS.surface,
    fontWeight: '700',
  },

  // Reject modal
  rejectBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  rejectContainer: {
    width: '100%',
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
  },
  rejectTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  rejectLabel: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginBottom: 8,
  },
  rejectInput: {
    minHeight: 100,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 8,
    padding: 8,
    textAlignVertical: 'top',
    marginBottom: 12,
  },
  rejectButtonsRow: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: 12,
  },
  rejectCancelBtn: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: 8,
    backgroundColor: COLORS.borderLight,
  },
  rejectCancelText: {
    color: COLORS.textSecondary,
    fontWeight: '700',
  },
  rejectConfirmBtn: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: 8,
    backgroundColor: COLORS.error,
  },
  rejectConfirmText: {
    color: COLORS.surface,
    fontWeight: '700',
  },

  // Menu Dropdown
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
});
