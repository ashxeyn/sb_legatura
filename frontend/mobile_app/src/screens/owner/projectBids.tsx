// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  TextInput,
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
    // Open rejection reason modal for this bid
    promptRejectBid(bid);
  };

  // Rejection modal state
  const [rejectModalVisible, setRejectModalVisible] = useState(false);
  const [pendingRejectBid, setPendingRejectBid] = useState<Bid | null>(null);
  const [rejectReason, setRejectReason] = useState('');

  const promptRejectBid = (bid: Bid) => {
    setPendingRejectBid(bid);
    setRejectReason('');
    setRejectModalVisible(true);
  };

  const confirmReject = async () => {
    if (!pendingRejectBid) return;
    setProcessingBidId(pendingRejectBid.bid_id);
    try {
      const response = await projects_service.reject_bid(project.project_id, pendingRejectBid.bid_id, userId, rejectReason || null);
      if (response.success) {
        setRejectModalVisible(false);
        Alert.alert('Success', 'Bid rejected');
        fetchBids();
        // close details modal if open
        if (selectedBid && selectedBid.bid_id === pendingRejectBid.bid_id) {
          closeBidDetails();
        }
      } else {
        Alert.alert('Error', response.message || 'Failed to reject bid');
      }
    } catch (err) {
      console.error('Error rejecting bid:', err);
      Alert.alert('Error', 'Failed to reject bid. Please try again.');
    } finally {
      setProcessingBidId(null);
    }
  };

  const getProfilePicUrl = (profilePic: string | undefined) => {
    if (!profilePic) return null;
    // If already a full URL, return as is
    if (profilePic.startsWith('http')) return profilePic;
    // Use the API files endpoint for proper file serving on mobile
    return `${api_config.base_url}/api/files/${profilePic}`;
  };

  const getFileUrl = (filePath: string) => {
    if (filePath.startsWith('http')) return filePath;
    // Use the API files endpoint for proper file serving on mobile
    return `${api_config.base_url}/api/files/${filePath}`;
  };

  const openBidDetails = (bid: Bid) => {
    console.log('Opening bid details for bid:', bid.bid_id);
    console.log('Bid files:', bid.files);
    console.log('File count:', bid.file_count);
    setSelectedBid(bid);
    setShowBidDetails(true);

    // Check if we have files data - files could be an array or undefined
    const hasFilesData = bid.files && Array.isArray(bid.files) && bid.files.length > 0;
    const expectsFiles = (bid.file_count && bid.file_count > 0);
    
    // If backend indicates files exist but we don't have them, fetch on demand
    if (expectsFiles && !hasFilesData) {
      console.log('Files expected but not loaded, fetching on demand...');
      (async () => {
        try {
          const resp = await projects_service.get_bid_files(project.project_id, bid.bid_id);
          console.log('Fetched bid files response:', resp);
          if (resp.success && Array.isArray(resp.data) && resp.data.length > 0) {
            setSelectedBid(prev => prev ? { ...prev, files: resp.data } : prev);
          }
        } catch (err) {
          console.warn('Failed to fetch bid files:', err);
        }
      })();
    }
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
            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
              <Feather name="briefcase" size={12} color={COLORS.textMuted} />
              <Text style={[styles.contractorMeta, { marginLeft: 4 }]}>
                {bid.years_of_experience} years exp{bid.completed_projects ? ` • ${bid.completed_projects} projects` : ''}
              </Text>
            </View>
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
          <View style={{ flexDirection: 'row', alignItems: 'center' }}>
            <Feather name="clock" size={12} color={COLORS.textMuted} />
            <Text style={[styles.submittedDate, { marginLeft: 4 }]}>{formatDate(bid.submitted_at)}</Text>
          </View>
          {bid.file_count != null && bid.file_count > 0 && (
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
    <View style={styles.container}>
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
                <Feather name="arrow-left" size={22} color={COLORS.text} />
              </TouchableOpacity>
              <Text style={styles.modalTitle}>Bid Details</Text>
              <View style={[styles.modalStatusPill, { backgroundColor: getStatusConfig(selectedBid.bid_status).bg }]}>
                <Text style={[styles.modalStatusText, { color: getStatusConfig(selectedBid.bid_status).color }]}>
                  {getStatusConfig(selectedBid.bid_status).label}
                </Text>
              </View>
            </View>

            <ScrollView style={styles.modalContent} showsVerticalScrollIndicator={false}>
              {/* Hero Section - Contractor & Price */}
              <View style={styles.heroSection}>
                <View style={styles.heroContractor}>
                  <View style={styles.heroAvatar}>
                    {getProfilePicUrl(selectedBid.profile_pic) ? (
                      <Image source={{ uri: getProfilePicUrl(selectedBid.profile_pic) }} style={styles.heroAvatarImage} />
                    ) : (
                      <Text style={styles.heroAvatarText}>
                        {selectedBid.company_name?.charAt(0).toUpperCase() || 'C'}
                      </Text>
                    )}
                  </View>
                  <View style={styles.heroInfo}>
                    <Text style={styles.heroCompanyName}>{selectedBid.company_name}</Text>
                    <Text style={styles.heroUsername}>@{selectedBid.username}</Text>
                  </View>
                </View>
                <View style={styles.heroPricing}>
                  <Text style={styles.heroPriceLabel}>Proposed Cost</Text>
                  <Text style={styles.heroPriceValue}>{formatCurrency(selectedBid.proposed_cost)}</Text>
                  <Text style={styles.heroTimeline}>{selectedBid.estimated_timeline} {selectedBid.estimated_timeline === 1 ? 'month' : 'months'} timeline</Text>
                </View>
              </View>

              {/* Quick Stats Row */}
              <View style={styles.quickStats}>
                <View style={styles.quickStatItem}>
                  <Text style={styles.quickStatValue}>{selectedBid.years_of_experience}</Text>
                  <Text style={styles.quickStatLabel}>Years Exp.</Text>
                </View>
                <View style={styles.quickStatDivider} />
                <View style={styles.quickStatItem}>
                  <Text style={styles.quickStatValue}>{selectedBid.completed_projects || 0}</Text>
                  <Text style={styles.quickStatLabel}>Projects</Text>
                </View>
                {selectedBid.picab_category && (
                  <>
                    <View style={styles.quickStatDivider} />
                    <View style={styles.quickStatItem}>
                      <Text style={styles.quickStatValue}>{selectedBid.picab_category}</Text>
                      <Text style={styles.quickStatLabel}>PICAB Cat.</Text>
                    </View>
                  </>
                )}
                {selectedBid.contractor_type && (
                  <>
                    <View style={styles.quickStatDivider} />
                    <View style={styles.quickStatItem}>
                      <Text style={styles.quickStatValue} numberOfLines={1}>{selectedBid.contractor_type}</Text>
                      <Text style={styles.quickStatLabel}>Type</Text>
                    </View>
                  </>
                )}
              </View>

              {/* Bid Details Table */}
              <View style={styles.detailsTable}>
                <Text style={styles.tableHeader}>Bid Information</Text>
                <View style={styles.tableRow}>
                  <Text style={styles.tableLabel}>Submitted On</Text>
                  <Text style={styles.tableValue}>{formatDate(selectedBid.submitted_at)}</Text>
                </View>
                <View style={styles.tableRow}>
                  <Text style={styles.tableLabel}>Proposed Cost</Text>
                  <Text style={[styles.tableValue, styles.tableValueHighlight]}>{formatCurrency(selectedBid.proposed_cost)}</Text>
                </View>
                <View style={styles.tableRow}>
                  <Text style={styles.tableLabel}>Est. Timeline</Text>
                  <Text style={styles.tableValue}>{selectedBid.estimated_timeline} {selectedBid.estimated_timeline === 1 ? 'month' : 'months'}</Text>
                </View>
                <View style={[styles.tableRow, { borderBottomWidth: 0 }]}>
                  <Text style={styles.tableLabel}>Status</Text>
                  <Text style={[styles.tableValue, { color: getStatusConfig(selectedBid.bid_status).color }]}>
                    {getStatusConfig(selectedBid.bid_status).label}
                  </Text>
                </View>
              </View>

              {/* Contractor Notes */}
              {selectedBid.contractor_notes && (
                <View style={styles.notesSection}>
                  <Text style={styles.tableHeader}>Contractor's Notes</Text>
                  <Text style={styles.notesText}>{selectedBid.contractor_notes}</Text>
                </View>
              )}

              {/* Contact Section */}
              <View style={styles.contactSection}>
                <Text style={styles.tableHeader}>Contact</Text>
                <View style={styles.contactRow}>
                  {selectedBid.company_email && (
                    <TouchableOpacity style={styles.contactBtn} onPress={() => handleEmailContractor(selectedBid.company_email!)}>
                      <Feather name="mail" size={18} color={COLORS.primary} />
                      <Text style={styles.contactBtnText}>Email</Text>
                    </TouchableOpacity>
                  )}
                  {selectedBid.company_phone && (
                    <TouchableOpacity style={styles.contactBtn} onPress={() => handleCallContractor(selectedBid.company_phone!)}>
                      <Feather name="phone" size={18} color={COLORS.primary} />
                      <Text style={styles.contactBtnText}>Call</Text>
                    </TouchableOpacity>
                  )}
                  {selectedBid.company_website && (
                    <TouchableOpacity style={styles.contactBtn} onPress={() => handleOpenWebsite(selectedBid.company_website!)}>
                      <Feather name="globe" size={18} color={COLORS.primary} />
                      <Text style={styles.contactBtnText}>Website</Text>
                    </TouchableOpacity>
                  )}
                </View>
              </View>

              {/* Attachments Section */}
              <View style={styles.attachmentsSection}>
                <View style={styles.attachmentsHeaderRow}>
                  <Text style={styles.tableHeader}>Attachments</Text>
                  {selectedBid.files && selectedBid.files.length > 0 && (
                    <Text style={styles.attachmentCount}>{selectedBid.files.length} file{selectedBid.files.length !== 1 ? 's' : ''}</Text>
                  )}
                </View>
                
                {selectedBid.files && selectedBid.files.length > 0 ? (
                  <View style={styles.filesList}>
                    {selectedBid.files.map((file, idx) => (
                      <TouchableOpacity
                        key={file.file_id || idx}
                        style={styles.fileRow}
                        onPress={() => handleOpenFile(file.file_path)}
                      >
                        <Feather 
                          name={file.file_name && (file.file_name.endsWith('.pdf') ? 'file-text' : file.file_name.match(/\.(jpg|jpeg|png|gif)$/i) ? 'image' : 'file')} 
                          size={18} 
                          color={COLORS.textSecondary} 
                        />
                        <Text style={styles.fileName} numberOfLines={1}>{file.file_name || 'Untitled'}</Text>
                        <Feather name="download" size={16} color={COLORS.textMuted} />
                      </TouchableOpacity>
                    ))}
                  </View>
                ) : (
                  <View style={styles.noFilesRow}>
                    <Feather name="inbox" size={18} color={COLORS.textMuted} />
                    <Text style={styles.noFilesText}>No attachments included</Text>
                  </View>
                )}
              </View>

              {/* Action Buttons - Only show for submitted bids */}
              {selectedBid.bid_status === 'submitted' && (
                <View style={styles.actionSection}>
                  <TouchableOpacity
                    style={styles.acceptBtn}
                    onPress={() => {
                      closeBidDetails();
                      handleAcceptBid(selectedBid);
                    }}
                  >
                    <Feather name="check" size={18} color="#FFFFFF" />
                    <Text style={styles.acceptBtnText}>Accept Bid</Text>
                  </TouchableOpacity>
                  <TouchableOpacity
                    style={styles.rejectBtn}
                    onPress={() => {
                      closeBidDetails();
                      handleRejectBid(selectedBid);
                    }}
                  >
                    <Text style={styles.rejectBtnText}>Decline</Text>
                  </TouchableOpacity>
                </View>
              )}

              <View style={{ height: 40 }} />
            </ScrollView>
          </View>
        )}
      </Modal>

      {/* Reject Reason Modal */}
      <Modal
        visible={rejectModalVisible}
        transparent
        animationType="slide"
        onRequestClose={() => setRejectModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContainer, { margin: 20, borderRadius: 12, padding: 16 }]}>
            <Text style={{ fontSize: 18, fontWeight: '700', color: COLORS.text, marginBottom: 6 }}>Reject Bid</Text>
            {pendingRejectBid && (
              <Text style={{ color: COLORS.textSecondary, marginBottom: 12 }}>Rejecting bid from {pendingRejectBid.company_name}</Text>
            )}

            <TextInput
              placeholder="Optional reason for rejection (helpful for contractors)"
              value={rejectReason}
              onChangeText={setRejectReason}
              multiline
              numberOfLines={4}
              style={{
                height: 100,
                borderWidth: 1,
                borderColor: COLORS.border,
                borderRadius: 8,
                padding: 10,
                textAlignVertical: 'top',
                backgroundColor: COLORS.surface,
              }}
            />

            <View style={{ flexDirection: 'row', marginTop: 14 }}>
              <TouchableOpacity
                style={[styles.modalRejectButton, { flex: 1, marginRight: 8 }]}
                onPress={() => setRejectModalVisible(false)}
              >
                <Text style={styles.modalRejectText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.modalAcceptButton, { flex: 1 }]}
                onPress={confirmReject}
              >
                <Text style={styles.modalAcceptText}>Confirm Reject</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
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
  // Modal styles - Clean Professional Design
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContainer: {
    flex: 1,
    backgroundColor: COLORS.surface,
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  modalCloseButton: {
    padding: 4,
    marginRight: 12,
  },
  modalTitle: {
    fontSize: 17,
    fontWeight: '600',
    color: COLORS.text,
    flex: 1,
  },
  modalStatusPill: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 4,
  },
  modalStatusText: {
    fontSize: 12,
    fontWeight: '600',
    textTransform: 'uppercase',
    letterSpacing: 0.3,
  },
  modalContent: {
    flex: 1,
  },
  // Hero Section
  heroSection: {
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  heroContractor: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  heroAvatar: {
    width: 56,
    height: 56,
    borderRadius: 6,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 14,
    overflow: 'hidden',
  },
  heroAvatarImage: {
    width: 56,
    height: 56,
  },
  heroAvatarText: {
    fontSize: 22,
    fontWeight: '600',
    color: COLORS.primary,
  },
  heroInfo: {
    flex: 1,
  },
  heroCompanyName: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 2,
  },
  heroUsername: {
    fontSize: 14,
    color: COLORS.textMuted,
  },
  heroPricing: {
    backgroundColor: COLORS.background,
    padding: 16,
    borderRadius: 6,
  },
  heroPriceLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  heroPriceValue: {
    fontSize: 28,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  heroTimeline: {
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  // Quick Stats
  quickStats: {
    flexDirection: 'row',
    paddingVertical: 16,
    paddingHorizontal: 20,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  quickStatItem: {
    flex: 1,
    alignItems: 'center',
  },
  quickStatValue: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 2,
  },
  quickStatLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.3,
  },
  quickStatDivider: {
    width: 1,
    height: 32,
    backgroundColor: COLORS.border,
  },
  // Details Table
  detailsTable: {
    paddingHorizontal: 20,
    paddingTop: 20,
    paddingBottom: 8,
  },
  tableHeader: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 12,
  },
  tableRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  tableLabel: {
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  tableValue: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.text,
  },
  tableValueHighlight: {
    fontWeight: '600',
    color: COLORS.success,
  },
  // Notes Section
  notesSection: {
    paddingHorizontal: 20,
    paddingTop: 20,
    paddingBottom: 8,
  },
  notesText: {
    fontSize: 14,
    color: COLORS.text,
    lineHeight: 22,
  },
  // Contact Section
  contactSection: {
    paddingHorizontal: 20,
    paddingTop: 20,
    paddingBottom: 8,
  },
  contactRow: {
    flexDirection: 'row',
    gap: 10,
  },
  contactBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    backgroundColor: COLORS.background,
    borderRadius: 6,
    gap: 6,
  },
  contactBtnText: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.primary,
  },
  // Attachments Section
  attachmentsSection: {
    paddingHorizontal: 20,
    paddingTop: 20,
    paddingBottom: 8,
  },
  attachmentsHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  attachmentCount: {
    fontSize: 13,
    color: COLORS.textMuted,
  },
  filesList: {
    gap: 1,
    backgroundColor: COLORS.border,
    borderRadius: 6,
    overflow: 'hidden',
  },
  fileRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 14,
    backgroundColor: COLORS.background,
    gap: 12,
  },
  fileName: {
    flex: 1,
    fontSize: 14,
    color: COLORS.text,
  },
  noFilesRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 16,
    gap: 10,
  },
  noFilesText: {
    fontSize: 14,
    color: COLORS.textMuted,
  },
  // Action Section
  actionSection: {
    paddingHorizontal: 20,
    paddingTop: 24,
    paddingBottom: 8,
    gap: 10,
  },
  acceptBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.success,
    paddingVertical: 14,
    borderRadius: 6,
    gap: 8,
  },
  acceptBtnText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  rejectBtn: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
  },
  rejectBtnText: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.error,
  },
  // Legacy styles kept for compatibility
  modalSection: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
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
    borderRadius: 6,
  },
  modalRejectButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.errorLight,
    paddingVertical: 14,
    borderRadius: 6,
  },
  headerSpacer: {
    width: 40,
  },
  // Legacy card styles for bid cards
  statsRow: {
    flexDirection: 'row',
    backgroundColor: COLORS.background,
    borderRadius: 6,
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
    borderRadius: 6,
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
    borderRadius: 6,
    padding: 16,
  },
  notesFullText: {
    fontSize: 14,
    color: COLORS.text,
    lineHeight: 22,
  },
  contactCard: {
    backgroundColor: COLORS.background,
    borderRadius: 6,
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
    borderRadius: 6,
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
    borderRadius: 6,
    overflow: 'hidden',
  },
  fileIcon: {
    width: 40,
    height: 40,
    borderRadius: 6,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  modalActions: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },
  actionButtonsContainer: {
    flexDirection: 'row',
    gap: 12,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
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
