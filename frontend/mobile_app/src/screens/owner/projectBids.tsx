// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Image,
  Dimensions,
  StatusBar,
  ActivityIndicator,
  RefreshControl,
  Alert,
  Modal,
  Linking,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { projects_service } from '../../services/projects_service';
import { api_config } from '../../config/api';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

// Color palette
const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
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
  text: '#0F172A',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
};

interface BidFile {
  file_id: number;
  bid_id: number;
  file_name: string;
  file_path: string;
  description?: string;
  uploaded_at: string;
}

interface Bid {
  bid_id: number;
  proposed_cost: number;
  estimated_timeline: number;
  contractor_notes?: string;
  bid_status: string;
  submitted_at: string;
  decision_date?: string;
  contractor_id: number;
  company_name: string;
  years_of_experience: number;
  company_email?: string;
  company_phone?: string;
  company_website?: string;
  company_description?: string;
  completed_projects?: number;
  picab_category?: string;
  contractor_type?: string;
  verification_status?: string;
  username: string;
  profile_pic?: string;
  file_count?: number;
  files?: BidFile[];
  // ✨ Ranking system properties
  ranking_score?: number;
  score_breakdown?: {
    price_score: number;
    experience_score: number;
    reputation_score: number;
    subscription_score: number;
  };
}

interface Project {
  project_id: number;
  project_title: string;
  bids_count?: number;
}

interface ProjectBidsProps {
  project: Project;
  userId: number;
  onClose: () => void;
  onBidAccepted?: () => void;
}

export default function ProjectBids({ project, userId, onClose, onBidAccepted }: ProjectBidsProps) {
  const insets = useSafeAreaInsets();
  const [bids, setBids] = useState<Bid[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [processingBidId, setProcessingBidId] = useState<number | null>(null);
  const [selectedBid, setSelectedBid] = useState<Bid | null>(null);
  const [showBidDetails, setShowBidDetails] = useState(false);

  useEffect(() => {
    fetchBids();
  }, [project.project_id]);

  const fetchBids = async () => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await projects_service.get_project_bids(project.project_id, userId);
      console.log('fetchBids response:', response);

      if (response.success && Array.isArray(response.data)) {
        setBids(response.data);
      } else if (response.success && response.data) {
        // Handle case where data might be wrapped
        const bidsArray = Array.isArray(response.data) ? response.data : [];
        setBids(bidsArray);
      } else {
        setError(response.message || 'Failed to load bids');
        setBids([]);
      }
    } catch (err) {
      console.error('Error fetching bids:', err);
      setError('Failed to load bids');
      setBids([]);
    } finally {
      setIsLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchBids();
    setRefreshing(false);
  };

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
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  const getStatusConfig = (status: string) => {
    switch (status) {
      case 'submitted':
        return { color: COLORS.info, bg: COLORS.infoLight, label: 'Submitted', icon: 'send' };
      case 'accepted':
        return { color: COLORS.success, bg: COLORS.successLight, label: 'Accepted', icon: 'check-circle' };
      case 'rejected':
        return { color: COLORS.error, bg: COLORS.errorLight, label: 'Rejected', icon: 'x-circle' };
      case 'cancelled':
        return { color: COLORS.textMuted, bg: COLORS.borderLight, label: 'Cancelled', icon: 'slash' };
      default:
        return { color: COLORS.textMuted, bg: COLORS.borderLight, label: status, icon: 'circle' };
    }
  };

  // ✨ Get rank styling based on position
  const getRankConfig = (rank: number) => {
    if (rank === 1) {
      return { 
        color: '#FFD700', 
        bg: '#FFFEF7', 
        label: 'Best Match', 
        icon: '🥇',
        borderColor: '#FFD700' 
      };
    }
    if (rank === 2) {
      return { 
        color: '#C0C0C0', 
        bg: '#F8F9FA', 
        label: 'Great Match', 
        icon: '🥈',
        borderColor: '#C0C0C0' 
      };
    }
    if (rank === 3) {
      return { 
        color: '#CD7F32', 
        bg: '#FFF5EE', 
        label: 'Good Match', 
        icon: '🥉',
        borderColor: '#CD7F32' 
      };
    }
    return { 
      color: COLORS.textMuted, 
      bg: 'transparent', 
      label: `#${rank}`, 
      icon: null,
      borderColor: 'transparent' 
    };
  };

  // Get score bar color based on score value
  const getScoreColor = (score: number) => {
    if (score >= 80) return COLORS.success;
    if (score >= 60) return COLORS.primary;
    if (score >= 40) return COLORS.warning;
    return COLORS.error;
  };

  const handleAcceptBid = (bid: Bid) => {
    Alert.alert(
      'Accept Bid',
      `Are you sure you want to accept the bid from ${bid.company_name} for ${formatCurrency(bid.proposed_cost)}?\n\nThis will reject all other bids and close bidding for this project.`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Accept',
          style: 'default',
          onPress: async () => {
            setProcessingBidId(bid.bid_id);
            try {
              const response = await projects_service.accept_bid(project.project_id, bid.bid_id, userId);

              if (response.success) {
                Alert.alert('Success', 'Bid accepted successfully!', [
                  {
                    text: 'OK',
                    onPress: () => {
                      fetchBids(); // Refresh to show updated statuses
                      onBidAccepted?.(); // Notify parent
                    },
                  },
                ]);
              } else {
                Alert.alert('Error', response.message || 'Failed to accept bid');
              }
            } catch (err) {
              console.error('Error accepting bid:', err);
              Alert.alert('Error', 'Failed to accept bid. Please try again.');
            } finally {
              setProcessingBidId(null);
            }
          },
        },
      ]
    );
  };

  const handleRejectBid = (bid: Bid) => {
    Alert.alert(
      'Reject Bid',
      `Are you sure you want to reject the bid from ${bid.company_name}?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Reject',
          style: 'destructive',
          onPress: async () => {
            setProcessingBidId(bid.bid_id);
            try {
              const response = await projects_service.reject_bid(project.project_id, bid.bid_id, userId);

              if (response.success) {
                Alert.alert('Success', 'Bid rejected');
                fetchBids(); // Refresh to show updated status
              } else {
                Alert.alert('Error', response.message || 'Failed to reject bid');
              }
            } catch (err) {
              console.error('Error rejecting bid:', err);
              Alert.alert('Error', 'Failed to reject bid. Please try again.');
            } finally {
              setProcessingBidId(null);
            }
          },
        },
      ]
    );
  };

  const getProfilePicUrl = (profilePic: string | undefined) => {
    if (!profilePic) return null;
    // If already a full URL, return as is
    if (profilePic.startsWith('http')) return profilePic;
    // Otherwise, construct from base_url
    return `${api_config.base_url}/storage/${profilePic}`;
  };

  const getFileUrl = (filePath: string) => {
    if (filePath.startsWith('http')) return filePath;
    return `${api_config.base_url}/storage/${filePath}`;
  };

  const openBidDetails = (bid: Bid) => {
    setSelectedBid(bid);
    setShowBidDetails(true);
  };

  const closeBidDetails = () => {
    setShowBidDetails(false);
    setSelectedBid(null);
  };

  const handleCallContractor = (phone: string) => {
    Linking.openURL(`tel:${phone}`);
  };

  const handleEmailContractor = (email: string) => {
    Linking.openURL(`mailto:${email}`);
  };

  const handleOpenWebsite = (website: string) => {
    let url = website;
    if (!website.startsWith('http')) {
      url = `https://${website}`;
    }
    Linking.openURL(url);
  };

  const handleOpenFile = (filePath: string) => {
    const url = getFileUrl(filePath);
    Linking.openURL(url);
  };

  const getRankBadgeStyle = (index: number) => {
    if (index === 0) return { bg: '#FFD700', icon: '🥇', label: 'Best Match' }; // Gold
    if (index === 1) return { bg: '#C0C0C0', icon: '🥈', label: 'Great Match' }; // Silver
    if (index === 2) return { bg: '#CD7F32', icon: '🥉', label: 'Good Match' }; // Bronze
    return { bg: COLORS.textMuted, icon: `#${index + 1}`, label: '' };
  };

  const renderBidCard = (bid: Bid, index: number) => {
    const statusConfig = getStatusConfig(bid.bid_status);
    const isProcessing = processingBidId === bid.bid_id;
    const profilePicUrl = getProfilePicUrl(bid.profile_pic);
    const rankBadge = getRankBadgeStyle(index);
    const isTopThree = index < 3;

    return (
      <TouchableOpacity
        key={bid.bid_id}
        style={[
          styles.bidCard,
          isTopThree && index === 0 && styles.topBidCard, // Special style for #1
        ]}
        activeOpacity={0.7}
        onPress={() => openBidDetails(bid)}
      >
        {/* Rank Badge - Top Right Corner */}
        <View style={[styles.rankBadge, { backgroundColor: rankBadge.bg }]}>
          <Text style={styles.rankBadgeText}>{rankBadge.icon}</Text>
        </View>

        {/* Top Bid Recommended Label */}
        {index === 0 && (
          <View style={styles.recommendedBanner}>
            <Feather name="award" size={14} color={COLORS.primary} />
            <Text style={styles.recommendedText}>RECOMMENDED</Text>
          </View>
        )}

        {/* Contractor Info */}
        <View style={styles.contractorRow}>
          <View style={styles.contractorAvatar}>
            {profilePicUrl ? (
              <Image source={{ uri: profilePicUrl }} style={styles.avatarImage} />
            ) : (
              <View style={styles.avatarPlaceholder}>
                <Text style={styles.avatarText}>
                  {bid.company_name?.charAt(0).toUpperCase() || 'C'}
                </Text>
              </View>
            )}
          </View>
          <View style={styles.contractorInfo}>
            <Text style={styles.companyName} numberOfLines={1}>{bid.company_name}</Text>
            <Text style={styles.contractorMeta}>
              <Feather name="briefcase" size={12} color={COLORS.textMuted} /> {bid.years_of_experience} years exp
              {bid.completed_projects ? ` • ${bid.completed_projects} projects` : ''}
            </Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: statusConfig.bg }]}>
            <Feather name={statusConfig.icon as any} size={12} color={statusConfig.color} />
            <Text style={[styles.statusText, { color: statusConfig.color }]}>{statusConfig.label}</Text>
          </View>
        </View>

        {/* Match Score Progress Bar */}
        {bid.ranking_score !== undefined && (
          <View style={styles.matchScoreContainer}>
            <View style={styles.matchScoreHeader}>
              <Text style={styles.matchScoreLabel}>Match Score</Text>
              <Text style={styles.matchScoreValue}>{Math.round(bid.ranking_score)}/100</Text>
            </View>
            <View style={styles.matchScoreBar}>
              <View 
                style={[
                  styles.matchScoreBarFill, 
                  { 
                    width: `${bid.ranking_score}%`,
                    backgroundColor: bid.ranking_score >= 80 ? COLORS.success : 
                                   bid.ranking_score >= 60 ? COLORS.warning : COLORS.textMuted
                  }
                ]} 
              />
            </View>
          </View>
        )}

        {/* Bid Details */}
        <View style={styles.bidDetails}>
          <View style={styles.bidDetailItem}>
            <Text style={styles.bidDetailLabel}>Proposed Cost</Text>
            <Text style={styles.bidDetailValue}>{formatCurrency(bid.proposed_cost)}</Text>
          </View>
          <View style={styles.bidDetailDivider} />
          <View style={styles.bidDetailItem}>
            <Text style={styles.bidDetailLabel}>Timeline</Text>
            <Text style={styles.bidDetailValue}>{bid.estimated_timeline}</Text>
          </View>
        </View>

        {/* Contractor Notes */}
        {bid.contractor_notes && (
          <View style={styles.notesSection}>
            <Text style={styles.notesLabel}>Notes:</Text>
            <Text style={styles.notesText} numberOfLines={2}>{bid.contractor_notes}</Text>
          </View>
        )}

        {/* Footer */}
        <View style={styles.bidFooter}>
          <Text style={styles.submittedDate}>
            <Feather name="clock" size={12} color={COLORS.textMuted} /> {formatDate(bid.submitted_at)}
          </Text>
          {bid.file_count && bid.file_count > 0 && (
            <View style={styles.filesIndicator}>
              <Feather name="paperclip" size={12} color={COLORS.textSecondary} />
              <Text style={styles.filesText}>{bid.file_count} files</Text>
            </View>
          )}
        </View>

        {/* Action buttons for submitted bids */}
        {bid.bid_status === 'submitted' && (
          <View style={styles.actionButtons}>
            <TouchableOpacity
              style={[styles.acceptButton, isProcessing && styles.buttonDisabled]}
              activeOpacity={0.8}
              onPress={() => handleAcceptBid(bid)}
              disabled={isProcessing}
            >
              {isProcessing ? (
                <ActivityIndicator size="small" color="#FFFFFF" />
              ) : (
                <>
                  <Feather name="check" size={16} color="#FFFFFF" />
                  <Text style={styles.acceptButtonText}>Accept</Text>
                </>
              )}
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.rejectButton, isProcessing && styles.buttonDisabled]}
              activeOpacity={0.8}
              onPress={() => handleRejectBid(bid)}
              disabled={isProcessing}
            >
              <Feather name="x" size={16} color={COLORS.error} />
              <Text style={styles.rejectButtonText}>Reject</Text>
            </TouchableOpacity>
          </View>
        )}
      </TouchableOpacity>
    );
  };

  const renderEmptyState = () => (
    <View style={styles.emptyState}>
      <View style={styles.emptyIconContainer}>
        <Feather name="inbox" size={48} color={COLORS.border} />
      </View>
      <Text style={styles.emptyTitle}>No Bids Yet</Text>
      <Text style={styles.emptySubtext}>
        Contractors haven't submitted any bids for this project yet. Check back later!
      </Text>
    </View>
  );

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.surface} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="arrow-left" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <View style={styles.headerCenter}>
          <Text style={styles.headerTitle}>Project Bids</Text>
          <Text style={styles.headerSubtitle} numberOfLines={1}>{project.project_title}</Text>
        </View>
        <View style={styles.headerSpacer} />
      </View>

      {/* Bids Count Summary */}
      <View style={styles.summaryBar}>
        <View style={styles.summaryItem}>
          <Feather name="users" size={18} color={COLORS.primary} />
          <Text style={styles.summaryText}>
            {bids.length} {bids.length === 1 ? 'Bid' : 'Bids'} Received
          </Text>
        </View>
      </View>

      {/* Content */}
      {isLoading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Loading bids...</Text>
        </View>
      ) : (
        <ScrollView
          style={styles.scrollView}
          contentContainerStyle={styles.scrollContent}
          showsVerticalScrollIndicator={false}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={onRefresh}
              colors={[COLORS.primary]}
              tintColor={COLORS.primary}
            />
          }
        >
          {bids.length === 0 ? (
            renderEmptyState()
          ) : (
            bids.map((bid, index) => renderBidCard(bid, index))
          )}
          <View style={{ height: 40 }} />
        </ScrollView>
      )}

      {/* Bid Details Modal */}
      <Modal
        visible={showBidDetails}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={closeBidDetails}
      >
        {selectedBid && (
          <View style={[styles.modalContainer, { paddingTop: insets.top }]}>
            {/* Modal Header */}
            <View style={styles.modalHeader}>
              <TouchableOpacity style={styles.modalCloseButton} onPress={closeBidDetails}>
                <Feather name="x" size={24} color={COLORS.text} />
              </TouchableOpacity>
              <Text style={styles.modalTitle}>Bid Details</Text>
              <View style={styles.headerSpacer} />
            </View>

            <ScrollView style={styles.modalContent} showsVerticalScrollIndicator={false}>
              {/* Contractor Profile Section */}
              <View style={styles.modalSection}>
                <View style={styles.contractorProfileHeader}>
                  <View style={styles.contractorAvatarLarge}>
                    {getProfilePicUrl(selectedBid.profile_pic) ? (
                      <Image source={{ uri: getProfilePicUrl(selectedBid.profile_pic) }} style={styles.avatarImageLarge} />
                    ) : (
                      <View style={styles.avatarPlaceholderLarge}>
                        <Text style={styles.avatarTextLarge}>
                          {selectedBid.company_name?.charAt(0).toUpperCase() || 'C'}
                        </Text>
                      </View>
                    )}
                  </View>
                  <View style={styles.contractorProfileInfo}>
                    <Text style={styles.companyNameLarge}>{selectedBid.company_name}</Text>
                    <Text style={styles.contractorUsername}>@{selectedBid.username}</Text>
                    {selectedBid.contractor_type && (
                      <View style={styles.contractorTypeBadge}>
                        <Text style={styles.contractorTypeText}>{selectedBid.contractor_type}</Text>
                      </View>
                    )}
                  </View>
                </View>

                {/* Contractor Stats */}
                <View style={styles.statsRow}>
                  <View style={styles.statItem}>
                    <Text style={styles.statValue}>{selectedBid.years_of_experience}</Text>
                    <Text style={styles.statLabel}>Years Exp.</Text>
                  </View>
                  <View style={styles.statDivider} />
                  <View style={styles.statItem}>
                    <Text style={styles.statValue}>{selectedBid.completed_projects || 0}</Text>
                    <Text style={styles.statLabel}>Projects</Text>
                  </View>
                  {selectedBid.picab_category && (
                    <>
                      <View style={styles.statDivider} />
                      <View style={styles.statItem}>
                        <Text style={styles.statValue}>{selectedBid.picab_category}</Text>
                        <Text style={styles.statLabel}>PICAB</Text>
                      </View>
                    </>
                  )}
                </View>
              </View>

              {/* Bid Information */}
              <View style={styles.modalSection}>
                <Text style={styles.sectionTitle}>Bid Information</Text>
                <View style={styles.bidInfoCard}>
                  <View style={styles.bidInfoRow}>
                    <View style={styles.bidInfoItem}>
                      <Feather name="dollar-sign" size={20} color={COLORS.success} />
                      <View style={styles.bidInfoText}>
                        <Text style={styles.bidInfoLabel}>Proposed Cost</Text>
                        <Text style={styles.bidInfoValue}>{formatCurrency(selectedBid.proposed_cost)}</Text>
                      </View>
                    </View>
                  </View>
                  <View style={styles.bidInfoRow}>
                    <View style={styles.bidInfoItem}>
                      <Feather name="calendar" size={20} color={COLORS.info} />
                      <View style={styles.bidInfoText}>
                        <Text style={styles.bidInfoLabel}>Estimated Timeline</Text>
                        <Text style={styles.bidInfoValue}>{selectedBid.estimated_timeline} {selectedBid.estimated_timeline === 1 ? 'month' : 'months'}</Text>
                      </View>
                    </View>
                  </View>
                  <View style={styles.bidInfoRow}>
                    <View style={styles.bidInfoItem}>
                      <Feather name="clock" size={20} color={COLORS.textMuted} />
                      <View style={styles.bidInfoText}>
                        <Text style={styles.bidInfoLabel}>Submitted</Text>
                        <Text style={styles.bidInfoValue}>{formatDate(selectedBid.submitted_at)}</Text>
                      </View>
                    </View>
                  </View>
                  <View style={[styles.bidInfoRow, { borderBottomWidth: 0 }]}>
                    <View style={styles.bidInfoItem}>
                      <Feather name={getStatusConfig(selectedBid.bid_status).icon as any} size={20} color={getStatusConfig(selectedBid.bid_status).color} />
                      <View style={styles.bidInfoText}>
                        <Text style={styles.bidInfoLabel}>Status</Text>
                        <Text style={[styles.bidInfoValue, { color: getStatusConfig(selectedBid.bid_status).color }]}>
                          {getStatusConfig(selectedBid.bid_status).label}
                        </Text>
                      </View>
                    </View>
                  </View>
                </View>
              </View>

              {/* Contractor Notes */}
              {selectedBid.contractor_notes && (
                <View style={styles.modalSection}>
                  <Text style={styles.sectionTitle}>Contractor Notes</Text>
                  <View style={styles.notesCard}>
                    <Text style={styles.notesFullText}>{selectedBid.contractor_notes}</Text>
                  </View>
                </View>
              )}

              {/* Contact Information */}
              <View style={styles.modalSection}>
                <Text style={styles.sectionTitle}>Contact Information</Text>
                <View style={styles.contactCard}>
                  {selectedBid.company_email && (
                    <TouchableOpacity
                      style={styles.contactItem}
                      onPress={() => handleEmailContractor(selectedBid.company_email!)}
                    >
                      <View style={[styles.contactIcon, { backgroundColor: COLORS.infoLight }]}>
                        <Feather name="mail" size={18} color={COLORS.info} />
                      </View>
                      <View style={styles.contactText}>
                        <Text style={styles.contactLabel}>Email</Text>
                        <Text style={styles.contactValue}>{selectedBid.company_email}</Text>
                      </View>
                      <Feather name="external-link" size={16} color={COLORS.textMuted} />
                    </TouchableOpacity>
                  )}
                  {selectedBid.company_phone && (
                    <TouchableOpacity
                      style={styles.contactItem}
                      onPress={() => handleCallContractor(selectedBid.company_phone!)}
                    >
                      <View style={[styles.contactIcon, { backgroundColor: COLORS.successLight }]}>
                        <Feather name="phone" size={18} color={COLORS.success} />
                      </View>
                      <View style={styles.contactText}>
                        <Text style={styles.contactLabel}>Phone</Text>
                        <Text style={styles.contactValue}>{selectedBid.company_phone}</Text>
                      </View>
                      <Feather name="external-link" size={16} color={COLORS.textMuted} />
                    </TouchableOpacity>
                  )}
                  {selectedBid.company_website && (
                    <TouchableOpacity
                      style={[styles.contactItem, { borderBottomWidth: 0 }]}
                      onPress={() => handleOpenWebsite(selectedBid.company_website!)}
                    >
                      <View style={[styles.contactIcon, { backgroundColor: COLORS.primaryLight }]}>
                        <Feather name="globe" size={18} color={COLORS.primary} />
                      </View>
                      <View style={styles.contactText}>
                        <Text style={styles.contactLabel}>Website</Text>
                        <Text style={styles.contactValue} numberOfLines={1}>{selectedBid.company_website}</Text>
                      </View>
                      <Feather name="external-link" size={16} color={COLORS.textMuted} />
                    </TouchableOpacity>
                  )}
                </View>
              </View>

              {/* Attached Documents */}
              {selectedBid.files && selectedBid.files.length > 0 && (
                <View style={styles.modalSection}>
                  <Text style={styles.sectionTitle}>Attached Documents ({selectedBid.files.length})</Text>
                  <View style={styles.filesCard}>
                    {selectedBid.files.map((file, index) => (
                      <TouchableOpacity
                        key={file.file_id}
                        style={[
                          styles.fileItem,
                          index === selectedBid.files!.length - 1 && { borderBottomWidth: 0 }
                        ]}
                        onPress={() => handleOpenFile(file.file_path)}
                      >
                        <View style={styles.fileIcon}>
                          <Feather
                            name={file.file_name.endsWith('.pdf') ? 'file-text' : 'file'}
                            size={20}
                            color={COLORS.primary}
                          />
                        </View>
                        <View style={styles.fileInfo}>
                          <Text style={styles.fileName} numberOfLines={1}>{file.file_name}</Text>
                          {file.description && (
                            <Text style={styles.fileDescription} numberOfLines={1}>{file.description}</Text>
                          )}
                        </View>
                        <Feather name="download" size={18} color={COLORS.textMuted} />
                      </TouchableOpacity>
                    ))}
                  </View>
                </View>
              )}

              {/* Action Buttons */}
              {selectedBid.bid_status === 'submitted' && (
                <View style={styles.modalActions}>
                  <TouchableOpacity
                    style={styles.modalAcceptButton}
                    onPress={() => {
                      closeBidDetails();
                      handleAcceptBid(selectedBid);
                    }}
                  >
                    <Feather name="check-circle" size={20} color="#FFFFFF" />
                    <Text style={styles.modalAcceptText}>Accept This Bid</Text>
                  </TouchableOpacity>
                  <TouchableOpacity
                    style={styles.modalRejectButton}
                    onPress={() => {
                      closeBidDetails();
                      handleRejectBid(selectedBid);
                    }}
                  >
                    <Feather name="x-circle" size={20} color={COLORS.error} />
                    <Text style={styles.modalRejectText}>Reject Bid</Text>
                  </TouchableOpacity>
                </View>
              )}

              <View style={{ height: 40 }} />
            </ScrollView>
          </View>
        )}
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
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerCenter: {
    flex: 1,
    alignItems: 'center',
    paddingHorizontal: 10,
  },
  headerTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.text,
  },
  headerSubtitle: {
    fontSize: 13,
    color: COLORS.textMuted,
    marginTop: 2,
  },
  headerSpacer: {
    width: 40,
  },
  summaryBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    paddingHorizontal: 16,
    backgroundColor: COLORS.primaryLight,
  },
  summaryItem: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  summaryText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.primary,
    marginLeft: 8,
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
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
  },
  emptyState: {
    alignItems: 'center',
    paddingVertical: 60,
    paddingHorizontal: 40,
  },
  emptyIconContainer: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: COLORS.borderLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  emptySubtext: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
  },
  bidCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 3,
    position: 'relative',
  },
  topBidCard: {
    borderWidth: 2,
    borderColor: '#FFD700',
    backgroundColor: '#FFFEF7',
  },
  rankBadge: {
    position: 'absolute',
    top: 12,
    right: 12,
    width: 36,
    height: 36,
    borderRadius: 18,
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.15,
    shadowRadius: 3,
    elevation: 4,
    zIndex: 10,
  },
  rankBadgeText: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  recommendedBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 8,
    alignSelf: 'flex-start',
    marginBottom: 12,
  },
  recommendedText: {
    fontSize: 11,
    fontWeight: '700',
    color: COLORS.primary,
    marginLeft: 4,
    letterSpacing: 0.5,
  },
  matchScoreContainer: {
    marginTop: 12,
    marginBottom: 12,
  },
  matchScoreHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  matchScoreLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  matchScoreValue: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.text,
  },
  matchScoreBar: {
    height: 8,
    backgroundColor: COLORS.borderLight,
    borderRadius: 4,
    overflow: 'hidden',
  },
  matchScoreBarFill: {
    height: '100%',
    borderRadius: 4,
  },
  contractorRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 14,
  },
  contractorAvatar: {
    marginRight: 12,
  },
  avatarImage: {
    width: 46,
    height: 46,
    borderRadius: 23,
  },
  avatarPlaceholder: {
    width: 46,
    height: 46,
    borderRadius: 23,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.primary,
  },
  contractorInfo: {
    flex: 1,
  },
  companyName: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 3,
  },
  contractorMeta: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '600',
    marginLeft: 4,
  },
  bidDetails: {
    flexDirection: 'row',
    backgroundColor: COLORS.background,
    borderRadius: 12,
    padding: 14,
    marginBottom: 12,
  },
  bidDetailItem: {
    flex: 1,
    alignItems: 'center',
  },
  bidDetailLabel: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginBottom: 4,
  },
  bidDetailValue: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
  },
  bidDetailDivider: {
    width: 1,
    backgroundColor: COLORS.border,
    marginHorizontal: 14,
  },
  notesSection: {
    marginBottom: 12,
  },
  notesLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginBottom: 4,
  },
  notesText: {
    fontSize: 13,
    color: COLORS.text,
    lineHeight: 18,
  },
  bidFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },
  submittedDate: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  filesIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  filesText: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginLeft: 4,
  },
  actionButtons: {
    flexDirection: 'row',
    marginTop: 14,
    paddingTop: 14,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },
  acceptButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.success,
    paddingVertical: 12,
    borderRadius: 10,
    marginRight: 8,
  },
  acceptButtonText: {
    color: '#FFFFFF',
    fontWeight: '600',
    fontSize: 14,
    marginLeft: 6,
  },
  rejectButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.errorLight,
    paddingVertical: 12,
    borderRadius: 10,
    marginLeft: 8,
  },
  rejectButtonText: {
    color: COLORS.error,
    fontWeight: '600',
    fontSize: 14,
    marginLeft: 6,
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  // Modal styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContainer: {
    flex: 1,
    backgroundColor: COLORS.surface,
  },
  modalScrollContent: {
    padding: 20,
    paddingBottom: 40,
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  closeModalButton: {
    padding: 4,
  },
  modalCompanyName: {
    fontSize: 22,
    fontWeight: '700',
    color: COLORS.text,
    flex: 1,
    marginRight: 12,
  },
  bidStatusSection: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    padding: 12,
    borderRadius: 12,
    marginBottom: 20,
  },
  detailSection: {
    marginBottom: 20,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
    flexDirection: 'row',
    alignItems: 'center',
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  detailLabel: {
    fontSize: 14,
    color: COLORS.textSecondary,
    flex: 1,
  },
  detailValue: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    flex: 1,
    textAlign: 'right',
  },
  detailValueLink: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.primary,
    flex: 1,
    textAlign: 'right',
    textDecorationLine: 'underline',
  },
  fullNotesText: {
    fontSize: 14,
    color: COLORS.text,
    lineHeight: 22,
    backgroundColor: COLORS.background,
    padding: 14,
    borderRadius: 10,
  },
  filesSection: {
    marginBottom: 20,
  },
  fileItem: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: COLORS.background,
    padding: 14,
    borderRadius: 10,
    marginBottom: 10,
  },
  fileInfo: {
    flex: 1,
    marginRight: 12,
  },
  fileName: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 4,
  },
  fileDescription: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  fileOpenText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.primary,
  },
  modalActionButtons: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 10,
    paddingTop: 20,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  modalAcceptButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.success,
    paddingVertical: 14,
    borderRadius: 12,
  },
  modalRejectButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.errorLight,
    paddingVertical: 14,
    borderRadius: 12,
  },
  // Additional modal styles
  modalCloseButton: {
    padding: 8,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    flex: 1,
    textAlign: 'center',
  },
  headerSpacer: {
    width: 40,
  },
  modalContent: {
    flex: 1,
    paddingHorizontal: 20,
  },
  modalSection: {
    marginBottom: 24,
  },
  contractorProfileHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  contractorAvatarLarge: {
    marginRight: 16,
  },
  avatarImageLarge: {
    width: 72,
    height: 72,
    borderRadius: 36,
  },
  avatarPlaceholderLarge: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarTextLarge: {
    fontSize: 28,
    fontWeight: '700',
    color: COLORS.primary,
  },
  contractorProfileInfo: {
    flex: 1,
  },
  companyNameLarge: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  contractorUsername: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginBottom: 8,
  },
  contractorTypeBadge: {
    alignSelf: 'flex-start',
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  contractorTypeText: {
    fontSize: 12,
    fontWeight: '600',
    color: COLORS.primary,
  },
  statsRow: {
    flexDirection: 'row',
    backgroundColor: COLORS.background,
    borderRadius: 12,
    padding: 16,
    justifyContent: 'space-around',
  },
  statItem: {
    alignItems: 'center',
  },
  statValue: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  statLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
  },
  statDivider: {
    width: 1,
    height: '100%',
    backgroundColor: COLORS.border,
  },
  bidInfoCard: {
    backgroundColor: COLORS.background,
    borderRadius: 12,
    padding: 4,
  },
  bidInfoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  bidInfoItem: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  bidInfoText: {
    marginLeft: 12,
    flex: 1,
  },
  bidInfoLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 2,
  },
  bidInfoValue: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
  },
  notesCard: {
    backgroundColor: COLORS.background,
    borderRadius: 12,
    padding: 16,
  },
  notesFullText: {
    fontSize: 14,
    color: COLORS.text,
    lineHeight: 22,
  },
  contactCard: {
    backgroundColor: COLORS.background,
    borderRadius: 12,
    overflow: 'hidden',
  },
  contactItem: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 14,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  contactIcon: {
    width: 36,
    height: 36,
    borderRadius: 18,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  contactText: {
    flex: 1,
  },
  contactLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 2,
  },
  contactValue: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.text,
  },
  filesCard: {
    backgroundColor: COLORS.background,
    borderRadius: 12,
    overflow: 'hidden',
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
  modalActions: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 8,
  },
  modalAcceptText: {
    color: '#FFFFFF',
    fontWeight: '600',
    fontSize: 15,
    marginLeft: 8,
  },
  modalRejectText: {
    color: COLORS.error,
    fontWeight: '600',
    fontSize: 15,
    marginLeft: 8,
  },
});
