// @ts-nocheck
import React, { useState } from 'react';
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
} from 'react-native';
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
}

export default function progressReportDetail({
  progressReport,
  milestoneTitle,
  projectTitle,
  userRole,
  onClose,
}: ProgressReportDetailProps) {
  const insets = useSafeAreaInsets();
  const [selectedImage, setSelectedImage] = useState<string | null>(null);
  const [showMenu, setShowMenu] = useState(false);
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

  // Image preview modal
  if (selectedImage) {
    return (
      <View style={[styles.container, { paddingTop: insets.top }]}>
        <StatusBar barStyle="light-content" backgroundColor="#000" />
        <View style={styles.imagePreviewHeader}>
          <TouchableOpacity onPress={() => setSelectedImage(null)} style={styles.closeButton}>
            <Feather name="x" size={28} color="#FFF" />
          </TouchableOpacity>
        </View>
        <View style={styles.imagePreviewContainer}>
          <Image
            source={{ uri: selectedImage }}
            style={styles.previewImage}
            resizeMode="contain"
            onError={(e) => {
              console.error('Image preview failed to load', e.nativeEvent?.error);
              Alert.alert('Preview error', 'Could not load image preview');
            }}
          />
        </View>
      </View>
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
        {/* Status Badge */}
        <View style={[styles.statusBadge, { backgroundColor: statusColors.bg }]}>
          <View style={[styles.statusDot, { backgroundColor: statusColors.text }]} />
          {/* Icon to make status explicit */}
          <View style={{ marginRight: 8 }}>
            {progressReport.progress_status === 'approved' ? (
              <Feather name="check" size={14} color={statusColors.text} />
            ) : progressReport.progress_status === 'rejected' ? (
              <Feather name="x" size={14} color={statusColors.text} />
            ) : (
              <Feather name="clock" size={14} color={statusColors.text} />
            )}
          </View>
          <Text style={[styles.statusText, { color: statusColors.text }]}>
            {getStatusLabel(progressReport.progress_status)}
          </Text>
        </View>

        {/* Hero card: emphasize project and milestone */}
        <View style={styles.heroCard}>
          <Text style={styles.heroProjectTitle} numberOfLines={1}>{projectTitle}</Text>
          <Text style={styles.heroMilestoneTitle} numberOfLines={2}>{milestoneTitle}</Text>
          <View style={styles.heroMetaRow}>
            <Feather name="calendar" size={12} color={COLORS.textMuted} style={{ marginRight: 6 }} />
            <Text style={styles.heroMetaText}>{formatDate(progressReport.submitted_at)}</Text>
          </View>
        </View>

        {/* Purpose/Description Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Description</Text>
          <View style={styles.purposeContainer}>
            <Text style={styles.purposeText}>
              {progressReport.purpose || 'No description provided.'}
            </Text>
          </View>
        </View>

        {/* Attachments Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>
            Attachments {files.length > 0 && `(${files.length})`}
          </Text>

          {files.length === 0 ? (
            <View style={styles.noAttachmentsContainer}>
              <View style={styles.noAttachmentsIcon}>
                <Feather name="paperclip" size={24} color={COLORS.textMuted} />
              </View>
              <Text style={styles.noAttachmentsText}>No attachments</Text>
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

              {/* Other Files List */}
              {otherFiles.length > 0 && (
                <View style={styles.filesList}>
                  {otherFiles.map((file) => (
                    <TouchableOpacity
                      key={file.file_id}
                      style={styles.fileItem}
                      onPress={() => handleFilePress(file)}
                    >
                      <View style={styles.fileIcon}>
                        <Feather name={getFileIcon(file.original_name)} size={20} color={COLORS.primary} />
                      </View>
                      <View style={styles.fileInfo}>
                        <Text style={styles.fileName} numberOfLines={1}>
                          {file.original_name}
                        </Text>
                        <Text style={styles.fileType}>
                          {getFileExtension(file.original_name).toUpperCase()} file
                        </Text>
                      </View>
                      <Feather name="download" size={20} color={COLORS.textSecondary} />
                    </TouchableOpacity>
                  ))}
                </View>
              )}
            </>
          )}
        </View>

        <View style={{ height: 40 }} />

        {/* Owner actions: Approve / Reject (Reject opens modal) */}
        {userRole === 'owner' && localStatus === 'submitted' && (
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
                          // If backend returned 409, show a nicer modal explaining sequential approval requirement
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

        {/* After rejection show badge and reason */}
        {localStatus === 'rejected' && (
          <View style={styles.rejectionWrapper}>
            <View style={styles.rejectionBadge}>
              <Text style={styles.rejectionBadgeText}>Rejection reason sent</Text>
            </View>
            {deleteReason ? (
              <View style={styles.rejectionReasonBox}>
                <Text style={styles.rejectionReasonLabel}>Reason</Text>
                <Text style={styles.rejectionReasonText}>{deleteReason}</Text>
              </View>
            ) : null}
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

        {/* Approval blocked modal (sequential approval) */}
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
      </ScrollView>
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
  scrollContent: {
    padding: 24,
  },

  // Status Badge
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
    marginBottom: 20,
  },
  statusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    marginRight: 8,
  },
  statusText: {
    fontSize: 14,
    fontWeight: '600',
  },

  // Info Section
  infoSection: {
    backgroundColor: COLORS.borderLight,
    borderRadius: 12,
    padding: 16,
    marginBottom: 24,
    gap: 12,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  infoLabel: {
    fontSize: 14,
    color: COLORS.textSecondary,
    fontWeight: '500',
  },
  infoValue: {
    flex: 1,
    fontSize: 14,
    color: COLORS.text,
    fontWeight: '600',
  },

  // Sections
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
  },

  // Purpose
  purposeContainer: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    borderWidth: 0,
  },
  purposeText: {
    fontSize: 15,
    color: COLORS.textSecondary,
    lineHeight: 24,
  },

  // No Attachments
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

  // Image Gallery
  imageGallery: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
    marginBottom: 16,
  },
  imageThumbnail: {
    width: 100,
    height: 100,
    borderRadius: 12,
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
    padding: 6,
    borderTopLeftRadius: 8,
  },

  // Files List
  filesList: {
    gap: 8,
  },
  fileItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  fileIcon: {
    width: 40,
    height: 40,
    borderRadius: 8,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  fileInfo: {
    flex: 1,
  },
  fileName: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 2,
  },
  fileType: {
    fontSize: 12,
    color: COLORS.textMuted,
  },

  // Image Preview
  imagePreviewHeader: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    zIndex: 10,
    padding: 16,
    paddingTop: 50,
  },
  closeButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  imagePreviewContainer: {
    flex: 1,
    backgroundColor: '#000',
    justifyContent: 'center',
    alignItems: 'center',
  },
  previewImage: {
    width: '100%',
    height: '100%',
  },
  actionsContainer: {
    flexDirection: 'row',
    gap: 12,
    paddingHorizontal: 24,
    paddingBottom: 24,
  },
  actionButton: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 12,
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
  rejectionWrapper: {
    paddingHorizontal: 24,
    paddingBottom: 24,
  },
  rejectionBadge: {
    alignSelf: 'flex-start',
    backgroundColor: COLORS.errorLight,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 12,
    marginBottom: 12,
  },
  rejectionBadgeText: {
    color: COLORS.error,
    fontWeight: '700',
  },
  rejectionReasonBox: {
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 12,
    padding: 12,
  },
  rejectionReasonLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 6,
    fontWeight: '600',
  },
  rejectionReasonText: {
    fontSize: 14,
    color: COLORS.text,
  },

  // Approval badge
  approvalWrapper: {
    paddingHorizontal: 24,
    paddingBottom: 24,
  },
  approvalBadge: {
    alignSelf: 'flex-start',
    backgroundColor: COLORS.successLight,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 12,
    marginBottom: 12,
  },
  approvalBadgeText: {
    color: COLORS.success,
    fontWeight: '700',
  },

  // Approval-blocked modal styles
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
  // Hero card to emphasize project/milestone
  heroCard: {
    backgroundColor: COLORS.primaryLight,
    borderRadius: 12,
    paddingVertical: 14,
    paddingHorizontal: 16,
    marginBottom: 16,
  },
  heroProjectTitle: {
    fontSize: 13,
    color: COLORS.textMuted,
    fontWeight: '600',
    marginBottom: 2,
  },
  heroMilestoneTitle: {
    fontSize: 18,
    color: COLORS.text,
    fontWeight: '800',
    marginBottom: 8,
    lineHeight: 22,
  },
  heroMetaRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  heroMetaText: {
    fontSize: 12,
    color: COLORS.textMuted,
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
});
