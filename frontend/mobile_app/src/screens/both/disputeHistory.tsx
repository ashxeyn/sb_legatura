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
  RefreshControl,
  Modal,
  Image,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { dispute_service } from '../../services/dispute_service';
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

const STATUS_CONFIG = {
  open: {
    label: 'Open',
    icon: 'alert-circle',
    color: COLORS.info,
    bg: COLORS.infoLight,
  },
  under_review: {
    label: 'Under Review',
    icon: 'eye',
    color: COLORS.warning,
    bg: COLORS.warningLight,
  },
  resolved: {
    label: 'Resolved',
    icon: 'check-circle',
    color: COLORS.success,
    bg: COLORS.successLight,
  },
  closed: {
    label: 'Closed',
    icon: 'x-circle',
    color: COLORS.textSecondary,
    bg: COLORS.borderLight,
  },
  cancelled: {
    label: 'Cancelled',
    icon: 'slash',
    color: COLORS.error,
    bg: COLORS.errorLight,
  },
};

const TYPE_CONFIG = {
  Payment: { icon: 'dollar-sign', color: COLORS.success },
  Delay: { icon: 'clock', color: COLORS.warning },
  Quality: { icon: 'alert-triangle', color: COLORS.error },
  'Request to Halt': { icon: 'pause-circle', color: COLORS.error },
  Others: { icon: 'more-horizontal', color: COLORS.info },
};

interface DisputeHistoryProps {
  onClose: () => void;
}

export default function DisputeHistory({ onClose }: DisputeHistoryProps) {
  const insets = useSafeAreaInsets();
  const [disputes, setDisputes] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [selectedDispute, setSelectedDispute] = useState<any | null>(null);
  const [selectedImage, setSelectedImage] = useState<string | null>(null);

  useEffect(() => {
    fetchDisputes();
  }, []);

  const fetchDisputes = async (isRefresh = false) => {
    if (isRefresh) {
      setRefreshing(true);
    } else {
      setLoading(true);
    }

    try {
      const response = await dispute_service.get_disputes();
      
      if (response.success && response.data) {
        // The api_request wraps the backend response, so the actual data is nested
        let disputesArray = [];
        
        // Check if response.data.data exists (wrapped response)
        if (response.data.data && Array.isArray(response.data.data.disputes)) {
          disputesArray = response.data.data.disputes;
        }
        // Or check if response.data.disputes exists (direct response)
        else if (Array.isArray(response.data.disputes)) {
          disputesArray = response.data.disputes;
        }
        // Or if response.data itself is an array
        else if (Array.isArray(response.data)) {
          disputesArray = response.data;
        }
        
        setDisputes(disputesArray);
      } else {
        console.error('Failed to fetch disputes:', response.message);
        setDisputes([]);
      }
    } catch (error) {
      console.error('Error fetching disputes:', error);
      setDisputes([]);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const handleRefresh = () => {
    fetchDisputes(true);
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  };

  const formatDateTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true,
    });
  };

  const getFileUrl = (filePath: string) => {
    if (!filePath) return '';
    // Use the API file serving endpoint to bypass Apache symlink issues
    return `${api_config.base_url}/api/files/${filePath}`;
  };

  const handleDisputePress = async (dispute: any) => {
    try {
      const response = await dispute_service.get_dispute_details(dispute.dispute_id);
      
      if (response.success && response.data) {
        // Handle nested response structure
        const disputeData = response.data.data || response.data;
        setSelectedDispute(disputeData);
      } else {
        console.error('Failed to fetch dispute details:', response.message);
      }
    } catch (error) {
      console.error('Error fetching dispute details:', error);
    }
  };

  const renderDisputeCard = (dispute: any, index: number) => {
    const status = STATUS_CONFIG[dispute.dispute_status] || STATUS_CONFIG.open;
    const type = TYPE_CONFIG[dispute.dispute_type] || TYPE_CONFIG.Others;

    return (
      <TouchableOpacity
        key={dispute.dispute_id}
        style={styles.disputeCard}
        onPress={() => handleDisputePress(dispute)}
        activeOpacity={0.7}
      >
        <View style={styles.disputeCardHeader}>
          <View style={styles.disputeTypeContainer}>
            <View style={[styles.typeIconContainer, { backgroundColor: type.color + '20' }]}>
              <Feather name={type.icon} size={18} color={type.color} />
            </View>
            <View style={styles.disputeHeaderText}>
              <Text style={styles.disputeType}>{dispute.dispute_type}</Text>
              <Text style={styles.disputeDate}>{formatDate(dispute.dispute_created_at || dispute.created_at)}</Text>
            </View>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: status.bg }]}>
            <Feather name={status.icon} size={14} color={status.color} />
            <Text style={[styles.statusText, { color: status.color }]}>{status.label}</Text>
          </View>
        </View>

        <Text style={styles.disputeDesc} numberOfLines={2}>
          {dispute.dispute_desc}
        </Text>

        {dispute.project_title && (
          <View style={styles.projectInfo}>
            <Feather name="folder" size={14} color={COLORS.textSecondary} />
            <Text style={styles.projectTitle} numberOfLines={1}>
              {dispute.project_title}
            </Text>
          </View>
        )}

        {dispute.milestone_item_title && (
          <View style={styles.milestoneInfo}>
            <Feather name="flag" size={14} color={COLORS.textSecondary} />
            <Text style={styles.milestoneTitle} numberOfLines={1}>
              {dispute.milestone_item_title}
            </Text>
          </View>
        )}

        <View style={styles.disputeCardFooter}>
          <Text style={styles.disputeId}>ID: #{dispute.dispute_id}</Text>
          <View style={styles.viewDetailsButton}>
            <Text style={styles.viewDetailsText}>View Details</Text>
            <Feather name="chevron-right" size={16} color={COLORS.accent} />
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  if (loading) {
    return (
      <View style={[styles.container, { paddingTop: insets.top }]}>
        <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />
        <View style={styles.header}>
          <TouchableOpacity onPress={onClose} style={styles.backButton}>
            <Feather name="x" size={24} color={COLORS.text} />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Report History</Text>
          <View style={styles.headerSpacer} />
        </View>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.accent} />
          <Text style={styles.loadingText}>Loading reports...</Text>
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
          <Feather name="x" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Report History</Text>
        <TouchableOpacity onPress={handleRefresh} style={styles.refreshButton}>
          <Feather name="refresh-cw" size={20} color={COLORS.accent} />
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={handleRefresh}
            tintColor={COLORS.accent}
            colors={[COLORS.accent]}
          />
        }
      >
        {disputes.length === 0 ? (
          <View style={styles.emptyContainer}>
            <View style={styles.emptyIconContainer}>
              <Feather name="inbox" size={64} color={COLORS.textMuted} />
            </View>
            <Text style={styles.emptyTitle}>No Reports Yet</Text>
            <Text style={styles.emptyText}>
              You haven't filed any disputes or reports yet. When you do, they will appear here.
            </Text>
          </View>
        ) : (
          <>
            <View style={styles.statsContainer}>
              <View style={styles.statCard}>
                <Text style={styles.statValue}>{disputes.length}</Text>
                <Text style={styles.statLabel}>Total Reports</Text>
              </View>
              <View style={styles.statCard}>
                <Text style={[styles.statValue, { color: COLORS.info }]}>
                  {disputes.filter(d => d.dispute_status === 'open').length}
                </Text>
                <Text style={styles.statLabel}>Open</Text>
              </View>
              <View style={styles.statCard}>
                <Text style={[styles.statValue, { color: COLORS.success }]}>
                  {disputes.filter(d => d.dispute_status === 'resolved').length}
                </Text>
                <Text style={styles.statLabel}>Resolved</Text>
              </View>
            </View>

            <Text style={styles.sectionTitle}>All Reports ({disputes.length})</Text>
            {disputes.map((dispute, index) => renderDisputeCard(dispute, index))}
          </>
        )}
      </ScrollView>

      {/* Dispute Detail Modal */}
      {selectedDispute && (
        <Modal
          visible={!!selectedDispute}
          animationType="slide"
          presentationStyle="fullScreen"
          onRequestClose={() => setSelectedDispute(null)}
        >
          <View style={[styles.modalContainer, { paddingTop: insets.top }]}>
            <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

            {/* Detail Header */}
            <View style={styles.detailHeader}>
              <TouchableOpacity onPress={() => setSelectedDispute(null)} style={styles.backButton}>
                <Feather name="chevron-left" size={24} color={COLORS.text} />
              </TouchableOpacity>
              <Text style={styles.headerTitle}>Report Details</Text>
              <View style={styles.headerSpacer} />
            </View>

            <ScrollView
              style={styles.detailScrollView}
              contentContainerStyle={styles.detailScrollContent}
              showsVerticalScrollIndicator={false}
            >
              {/* Status Banner */}
              {(() => {
                const status = STATUS_CONFIG[selectedDispute.dispute?.dispute_status] || STATUS_CONFIG.open;
                return (
                  <View style={[styles.statusBanner, { backgroundColor: status.bg }]}>
                    <Feather name={status.icon} size={24} color={status.color} />
                    <Text style={[styles.statusBannerText, { color: status.color }]}>
                      {status.label}
                    </Text>
                  </View>
                );
              })()}

              {/* Report Info */}
              <View style={styles.detailCard}>
                <Text style={styles.detailCardTitle}>Report Information</Text>
                <View style={styles.detailRow}>
                  <Text style={styles.detailLabel}>Report ID:</Text>
                  <Text style={styles.detailValue}>#{selectedDispute.dispute?.dispute_id}</Text>
                </View>
                <View style={styles.detailRow}>
                  <Text style={styles.detailLabel}>Type:</Text>
                  <View style={styles.typeTag}>
                    <Feather
                      name={TYPE_CONFIG[selectedDispute.dispute?.dispute_type]?.icon || 'info'}
                      size={14}
                      color={TYPE_CONFIG[selectedDispute.dispute?.dispute_type]?.color || COLORS.text}
                    />
                    <Text style={styles.typeTagText}>{selectedDispute.dispute?.dispute_type}</Text>
                  </View>
                </View>
                {selectedDispute.dispute?.if_others_distype && (
                  <View style={styles.detailRow}>
                    <Text style={styles.detailLabel}>Specified Type:</Text>
                    <Text style={styles.detailValue}>{selectedDispute.dispute?.if_others_distype}</Text>
                  </View>
                )}
                <View style={styles.detailRow}>
                  <Text style={styles.detailLabel}>Filed On:</Text>
                  <Text style={styles.detailValue}>{formatDateTime(selectedDispute.dispute?.dispute_created_at || selectedDispute.dispute?.created_at)}</Text>
                </View>
                {selectedDispute.dispute?.resolved_at && (
                  <View style={styles.detailRow}>
                    <Text style={styles.detailLabel}>Resolved On:</Text>
                    <Text style={styles.detailValue}>{formatDateTime(selectedDispute.dispute?.resolved_at)}</Text>
                  </View>
                )}
              </View>

              {/* Project Context */}
              {selectedDispute.dispute?.project_title && (
                <View style={styles.detailCard}>
                  <Text style={styles.detailCardTitle}>Project Context</Text>
                  <View style={styles.contextRow}>
                    <Feather name="folder" size={18} color={COLORS.accent} />
                    <View style={styles.contextText}>
                      <Text style={styles.contextLabel}>Project</Text>
                      <Text style={styles.contextValue}>{selectedDispute.dispute?.project_title}</Text>
                    </View>
                  </View>
                  {selectedDispute.dispute?.milestone_item_title && (
                    <View style={styles.contextRow}>
                      <Feather name="flag" size={18} color={COLORS.accent} />
                      <View style={styles.contextText}>
                        <Text style={styles.contextLabel}>Milestone Item</Text>
                        <Text style={styles.contextValue}>{selectedDispute.dispute?.milestone_item_title}</Text>
                      </View>
                    </View>
                  )}
                </View>
              )}

              {/* Description */}
              <View style={styles.detailCard}>
                <Text style={styles.detailCardTitle}>Description</Text>
                <Text style={styles.descriptionText}>{selectedDispute.dispute?.dispute_desc}</Text>
              </View>

              {/* Evidence Files */}
              {selectedDispute.evidence_files && selectedDispute.evidence_files.length > 0 && (
                <View style={styles.detailCard}>
                  <Text style={styles.detailCardTitle}>Evidence Files ({selectedDispute.evidence_files.length})</Text>
                  <View style={styles.evidenceContainer}>
                    {selectedDispute.evidence_files.map((file: any, index: number) => {
                      const filePath = file.storage_path || file.file_path;
                      const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(file.original_name || filePath);
                      const fileUrl = getFileUrl(filePath);
                      
                      return (
                        <TouchableOpacity
                          key={index}
                          style={styles.evidenceItem}
                          onPress={() => {
                            if (isImage) {
                              setSelectedImage(fileUrl);
                            }
                          }}
                        >
                          {isImage ? (
                            <View style={styles.evidenceImageContainer}>
                              <Image
                                source={{ uri: fileUrl }}
                                style={styles.evidenceImage}
                                resizeMode="cover"
                                onError={(error) => {
                                  console.error('Failed to load image:', fileUrl, error);
                                }}
                              />
                              <View style={styles.imageOverlay}>
                                <Feather name="maximize-2" size={16} color={COLORS.surface} />
                              </View>
                            </View>
                          ) : (
                            <View style={styles.evidenceFileIcon}>
                              <Feather name="file" size={32} color={COLORS.primary} />
                            </View>
                          )}
                          <Text style={styles.evidenceFileName} numberOfLines={2}>
                            {file.original_name}
                          </Text>
                        </TouchableOpacity>
                      );
                    })}
                  </View>
                </View>
              )}

              {/* Admin Response */}
              {selectedDispute.dispute?.admin_response && (
                <View style={[styles.detailCard, { backgroundColor: COLORS.successLight }]}>
                  <View style={styles.adminResponseHeader}>
                    <Feather name="message-square" size={20} color={COLORS.success} />
                    <Text style={[styles.detailCardTitle, { color: COLORS.success, marginBottom: 0 }]}>
                      Admin Response
                    </Text>
                  </View>
                  <Text style={styles.adminResponseText}>{selectedDispute.dispute?.admin_response}</Text>
                </View>
              )}

              <View style={{ height: 40 }} />
            </ScrollView>
          </View>
        </Modal>
      )}

      {/* Full Screen Image Modal */}
      {selectedImage && (
        <Modal
          visible={!!selectedImage}
          animationType="fade"
          transparent={true}
          onRequestClose={() => setSelectedImage(null)}
        >
          <View style={styles.imageModalContainer}>
            <TouchableOpacity
              style={styles.imageModalClose}
              onPress={() => setSelectedImage(null)}
            >
              <Feather name="x" size={28} color={COLORS.surface} />
            </TouchableOpacity>
            <Image
              source={{ uri: selectedImage }}
              style={styles.fullScreenImage}
              resizeMode="contain"
              onError={(error) => {
                console.error('Failed to load full screen image:', selectedImage, error);
              }}
            />
            <View style={styles.imageHint}>
              <Text style={styles.imageHintText}>Pinch to zoom • Swipe to pan</Text>
            </View>
          </View>
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
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    backgroundColor: COLORS.surface,
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
  headerSpacer: {
    width: 44,
  },
  refreshButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 16,
    fontSize: 16,
    color: COLORS.textSecondary,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingVertical: 80,
    paddingHorizontal: 32,
  },
  emptyIconContainer: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: COLORS.borderLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 24,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  emptyText: {
    fontSize: 15,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 22,
  },
  statsContainer: {
    flexDirection: 'row',
    gap: 12,
    marginBottom: 24,
  },
  statCard: {
    flex: 1,
    backgroundColor: COLORS.primaryLight,
    borderRadius: 12,
    padding: 16,
    alignItems: 'center',
  },
  statValue: {
    fontSize: 28,
    fontWeight: '700',
    color: COLORS.primary,
    marginBottom: 4,
  },
  statLabel: {
    fontSize: 13,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 16,
  },
  disputeCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
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
  disputeCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  disputeTypeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
    gap: 12,
  },
  typeIconContainer: {
    width: 36,
    height: 36,
    borderRadius: 18,
    justifyContent: 'center',
    alignItems: 'center',
  },
  disputeHeaderText: {
    flex: 1,
  },
  disputeType: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
  },
  disputeDate: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginTop: 2,
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 12,
    gap: 4,
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
  },
  disputeDesc: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: 12,
  },
  projectInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 6,
  },
  projectTitle: {
    flex: 1,
    fontSize: 13,
    color: COLORS.textSecondary,
  },
  milestoneInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  milestoneTitle: {
    flex: 1,
    fontSize: 13,
    color: COLORS.textSecondary,
  },
  disputeCardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },
  disputeId: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textMuted,
  },
  viewDetailsButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  viewDetailsText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.accent,
  },

  // Detail Modal Styles
  modalContainer: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  detailHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    backgroundColor: COLORS.surface,
  },
  detailScrollView: {
    flex: 1,
  },
  detailScrollContent: {
    padding: 16,
  },
  statusBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 16,
    borderRadius: 12,
    marginBottom: 16,
    gap: 12,
  },
  statusBannerText: {
    fontSize: 18,
    fontWeight: '700',
  },
  detailCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  detailCardTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
  },
  detailLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },
  detailValue: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    flex: 1,
    textAlign: 'right',
  },
  typeTag: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 8,
    backgroundColor: COLORS.primaryLight,
  },
  typeTagText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
  },
  contextRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    marginBottom: 12,
  },
  contextText: {
    flex: 1,
  },
  contextLabel: {
    fontSize: 12,
    fontWeight: '500',
    color: COLORS.textSecondary,
    marginBottom: 4,
  },
  contextValue: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
  },
  descriptionText: {
    fontSize: 15,
    color: COLORS.text,
    lineHeight: 22,
  },
  evidenceContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  evidenceItem: {
    width: '31%',
    aspectRatio: 1,
    borderRadius: 8,
    overflow: 'hidden',
    backgroundColor: COLORS.borderLight,
  },
  evidenceImageContainer: {
    width: '100%',
    height: '80%',
    position: 'relative',
  },
  evidenceImage: {
    width: '100%',
    height: '100%',
  },
  imageOverlay: {
    position: 'absolute',
    bottom: 4,
    right: 4,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    borderRadius: 4,
    padding: 4,
  },
  evidenceFileIcon: {
    width: '100%',
    height: '80%',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.primaryLight,
  },
  evidenceFileName: {
    fontSize: 10,
    color: COLORS.textSecondary,
    padding: 4,
    textAlign: 'center',
  },
  adminResponseHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  adminResponseText: {
    fontSize: 15,
    color: COLORS.success,
    lineHeight: 22,
  },

  // Full Screen Image Modal
  imageModalContainer: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.95)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  imageModalClose: {
    position: 'absolute',
    top: 50,
    right: 20,
    zIndex: 10,
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    borderRadius: 22,
  },
  fullScreenImage: {
    width: '100%',
    height: '100%',
  },
  imageHint: {
    position: 'absolute',
    bottom: 40,
    left: 0,
    right: 0,
    alignItems: 'center',
  },
  imageHintText: {
    color: COLORS.surface,
    fontSize: 13,
    opacity: 0.7,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
  },
});
