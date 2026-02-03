// @ts-nocheck
import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  RefreshControl,
  Alert,
  Modal,
  Image,
  Linking,
} from 'react-native';
import { StatusBar, Platform } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Feather } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { projects_service } from '../../services/projects_service';
import MilestoneSetup from './milestoneSetup';
import { api_config } from '../../config/api';

interface Bid {
  bid_id: number;
  project_id: number;
  project_title: string;
  project_description?: string;
  project_location?: string;
  budget_range_min?: number;
  budget_range_max?: number;
  lot_size?: number;
  floor_area?: number;
  property_type?: string;
  type_name?: string;
  to_finish?: number;
  bidding_due?: string;
  proposed_cost: number;
  estimated_timeline: number;
  contractor_notes?: string;
  bid_status: 'pending' | 'accepted' | 'rejected' | 'withdrawn' | 'submitted';
  submitted_at: string;
  owner_name?: string;
  project_status?: string;
  project_files?: Array<{
    file_type: string;
    file_path: string;
  }>;
}

interface MyBidsProps {
  userData?: {
    user_id?: number;
    username?: string;
    email?: string;
    profile_pic?: string;
    company_name?: string;
  };
  onClose?: () => void;
}

const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
  secondary: '#1A1A2E',
  accent: '#16213E',
  success: '#10B981',
  successLight: '#D1FAE5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
  errorLight: '#FEE2E2',
  info: '#3B82F6',
  background: '#F8FAFC',
  surface: '#FFFFFF',
  text: '#0F172A',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
};

export default function MyBids({ userData, onClose }: MyBidsProps) {
  const insets = useSafeAreaInsets();
  const [bids, setBids] = useState<Bid[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [filterStatus, setFilterStatus] = useState<string>('all');
  const [selectedBid, setSelectedBid] = useState<Bid | null>(null);
  const [showDetailsModal, setShowDetailsModal] = useState(false);
  const [selectedProject, setSelectedProject] = useState<any>(null);
  const [showMilestoneSetup, setShowMilestoneSetup] = useState(false);
  const [isLoadingProject, setIsLoadingProject] = useState(false);

  // Debug effect to track state changes
  useEffect(() => {
    console.log('MyBids - State update:', {
      showMilestoneSetup,
      hasSelectedProject: !!selectedProject,
      selectedProjectId: selectedProject?.project_id
    });
  }, [showMilestoneSetup, selectedProject]);

  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  useEffect(() => {
    fetchBids();
  }, [userData?.user_id]);

  const fetchBids = async () => {
    if (!userData?.user_id) {
      setIsLoading(false);
      return;
    }

    try {
      setIsLoading(true);
      const response = await projects_service.get_my_bids(userData.user_id);
      console.log('MyBids response:', JSON.stringify(response, null, 2));

      if (response.success) {
        // The API returns data wrapped, so we need to extract it properly
        const apiData = response.data;
        const bidsData = apiData?.data || apiData || [];
        const bidsArray = Array.isArray(bidsData) ? bidsData : [];
        console.log('MyBids array length:', bidsArray.length);
        setBids(bidsArray);
      } else {
        setBids([]);
        Alert.alert('Error', response.message || 'Failed to load bids');
      }
    } catch (error) {
      console.error('Error fetching bids:', error);
      setBids([]);
      Alert.alert('Error', 'Failed to load bids');
    } finally {
      setIsLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await fetchBids();
    setRefreshing(false);
  };

  const handleAcceptedBidClick = async (bid: Bid) => {
    try {
      setIsLoadingProject(true);
      console.log('handleAcceptedBidClick - Fetching project for bid:', bid.bid_id, 'project_id:', bid.project_id);
      
      // Fetch contractor projects to get the full project data
      const response = await projects_service.get_contractor_projects(userData?.user_id || 0);
      console.log('handleAcceptedBidClick - Response:', response);
      
      if (response.success) {
        // Handle nested response structure
        let projectsData = response.data;
        if (projectsData && projectsData.data && Array.isArray(projectsData.data)) {
          projectsData = projectsData.data;
        } else if (!Array.isArray(projectsData)) {
          projectsData = [];
        }
        
        console.log('handleAcceptedBidClick - Projects data:', projectsData);
        console.log('handleAcceptedBidClick - Projects count:', projectsData.length);
        console.log('handleAcceptedBidClick - Looking for project_id:', bid.project_id);
        console.log('handleAcceptedBidClick - Available project IDs:', projectsData.map((p: any) => ({ id: p.project_id, title: p.project_title })));
        
        // Try to find project by project_id (convert to number for comparison)
        const projectIdToFind = Number(bid.project_id);
        const project = projectsData.find((p: any) => Number(p.project_id) === projectIdToFind);
        
        console.log('handleAcceptedBidClick - Found project:', project);
        console.log('handleAcceptedBidClick - Project ID match:', project ? `Match: ${project.project_id} === ${bid.project_id}` : 'No match');
        
        if (project) {
          // Ensure project has the right structure for milestone setup
          const projectForSetup = {
            ...project,
            display_status: project.display_status || project.project_status || 'waiting_milestone_setup',
            milestones: project.milestones || [],
            milestones_count: project.milestones_count || 0
          };
          console.log('handleAcceptedBidClick - Setting project for milestone setup:', projectForSetup);
          console.log('handleAcceptedBidClick - Project display_status:', projectForSetup.display_status);
          console.log('handleAcceptedBidClick - Project milestones:', projectForSetup.milestones);
          
          // Set state directly - this should trigger re-render with MilestoneSetup
          setSelectedProject(projectForSetup);
          setShowMilestoneSetup(true);
          console.log('handleAcceptedBidClick - State set: showMilestoneSetup=true, selectedProject set');
          console.log('handleAcceptedBidClick - Component should now render MilestoneSetup');
        } else {
          console.error('handleAcceptedBidClick - Project not found in projects list');
          console.error('handleAcceptedBidClick - Bid project_id:', bid.project_id, 'Type:', typeof bid.project_id);
          Alert.alert('Error', `Project not found. Looking for project_id: ${bid.project_id}. Please try again.`);
        }
      } else {
        console.error('handleAcceptedBidClick - Failed to fetch projects:', response.message);
        Alert.alert('Error', 'Failed to load project data. Please try again.');
      }
    } catch (error) {
      console.error('Error loading project:', error);
      Alert.alert('Error', 'Failed to load project data. Please try again.');
    } finally {
      setIsLoadingProject(false);
    }
  };

  const formatCost = (cost: number) => {
    if (cost >= 1000000) return `₱${(cost / 1000000).toFixed(2)}M`;
    if (cost >= 1000) return `₱${(cost / 1000).toFixed(0)}K`;
    return `₱${cost.toLocaleString()}`;
  };

  const formatBudget = (min?: number, max?: number) => {
    if (!min || !max) return 'N/A';
    const formatNum = (n: number) => {
      if (n >= 1000000) return `₱${(n / 1000000).toFixed(1)}M`;
      if (n >= 1000) return `₱${(n / 1000).toFixed(0)}K`;
      return `₱${n}`;
    };
    return `${formatNum(min)} - ${formatNum(max)}`;
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  };

  const getFileUrl = (filePath: string) => {
    if (!filePath) return '';
    if (filePath.startsWith('http')) return filePath;
    return `${api_config.base_url}/storage/${filePath}`;
  };

  const handleOpenFile = (filePath: string) => {
    const url = getFileUrl(filePath);
    if (url) Linking.openURL(url);
  };

  const getBidStatusConfig = (status: string) => {
    switch (status) {
      case 'pending':
      case 'submitted':
        return { color: COLORS.warning, bg: COLORS.warningLight, label: 'Pending', icon: 'clock' };
      case 'accepted':
        return { color: COLORS.success, bg: COLORS.successLight, label: 'Accepted', icon: 'check-circle' };
      case 'rejected':
        return { color: COLORS.error, bg: COLORS.errorLight, label: 'Rejected', icon: 'x-circle' };
      case 'withdrawn':
        return { color: COLORS.textMuted, bg: COLORS.border, label: 'Withdrawn', icon: 'minus-circle' };
      default:
        return { color: COLORS.textMuted, bg: COLORS.border, label: status, icon: 'circle' };
    }
  };

  const filteredBids = filterStatus === 'all'
    ? bids
    : bids.filter(bid => bid.bid_status === filterStatus);

  const stats = {
    total: bids.length,
    pending: bids.filter(b => b.bid_status === 'pending' || b.bid_status === 'submitted').length,
    accepted: bids.filter(b => b.bid_status === 'accepted').length,
    rejected: bids.filter(b => b.bid_status === 'rejected').length,
  };

  // Show Milestone Setup screen if selected - MUST be first check before any other rendering
  if (showMilestoneSetup && selectedProject) {
    console.log('MyBids - Rendering MilestoneSetup with project:', selectedProject);
    console.log('MyBids - Project ID:', selectedProject.project_id);
    console.log('MyBids - Project title:', selectedProject.project_title);
    console.log('MyBids - Project display_status:', selectedProject.display_status);
    
    return (
      <MilestoneSetup
        project={selectedProject}
        userId={userData?.user_id}
        onClose={() => {
          console.log('MyBids - MilestoneSetup onClose called');
          setShowMilestoneSetup(false);
          setSelectedProject(null);
        }}
        onSave={() => {
          console.log('MyBids - MilestoneSetup onSave called');
          setShowMilestoneSetup(false);
          setSelectedProject(null);
          fetchBids(); // Refresh bids after saving milestones
        }}
      />
    );
  }

  if (isLoading && !refreshing) {
    return (
      <View style={[styles.container, { paddingTop: statusBarHeight }]}>
        <StatusBar hidden={true} />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Loading your bids...</Text>
        </View>
      </View>
    );
  }

  if (isLoadingProject) {
    return (
      <View style={[styles.container, { paddingTop: statusBarHeight }]}>
        <StatusBar hidden={true} />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Loading project...</Text>
        </View>
      </View>
    );
  }

  return (
    <View style={[styles.container, { paddingTop: statusBarHeight }]}>
      <StatusBar hidden={true} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={onClose}
          activeOpacity={0.7}
        >
          <Feather name="arrow-left" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <View style={styles.headerContent}>
          <Text style={styles.headerTitle}>My Bids</Text>
          <Text style={styles.headerSubtitle}>{stats.total} total bids</Text>
        </View>
      </View>

      {/* Stats Cards */}
      <View style={styles.statsContainer}>
        <View style={styles.statCard}>
          <Text style={styles.statValue}>{stats.pending}</Text>
          <Text style={styles.statLabel}>Pending</Text>
        </View>
        <View style={styles.statCard}>
          <Text style={[styles.statValue, { color: COLORS.success }]}>{stats.accepted}</Text>
          <Text style={styles.statLabel}>Accepted</Text>
        </View>
        <View style={styles.statCard}>
          <Text style={[styles.statValue, { color: COLORS.error }]}>{stats.rejected}</Text>
          <Text style={styles.statLabel}>Rejected</Text>
        </View>
      </View>

      {/* Filter Tabs */}
      <ScrollView
        horizontal
        showsHorizontalScrollIndicator={false}
        style={styles.filterContainer}
        contentContainerStyle={styles.filterContent}
      >
        {['all', 'pending', 'submitted', 'accepted', 'rejected'].map((status) => (
          <TouchableOpacity
            key={status}
            style={[
              styles.filterTab,
              filterStatus === status && styles.filterTabActive,
            ]}
            onPress={() => setFilterStatus(status)}
            activeOpacity={0.7}
          >
            <Text
              style={[
                styles.filterTabText,
                filterStatus === status && styles.filterTabTextActive,
              ]}
            >
              {status === 'all' ? 'All' : status.charAt(0).toUpperCase() + status.slice(1)}
            </Text>
          </TouchableOpacity>
        ))}
      </ScrollView>

      {/* Bids List */}
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
        {filteredBids.length === 0 ? (
          <View style={styles.emptyContainer}>
            <View style={styles.emptyIconContainer}>
              <Feather name="file-text" size={48} color={COLORS.textMuted} />
            </View>
            <Text style={styles.emptyTitle}>No Bids Found</Text>
            <Text style={styles.emptySubtitle}>
              {filterStatus === 'all'
                ? "You haven't submitted any bids yet"
                : `No ${filterStatus} bids`}
            </Text>
          </View>
        ) : (
          filteredBids.map((bid) => {
            const statusConfig = getBidStatusConfig(bid.bid_status);
            return (
              <TouchableOpacity
                key={bid.bid_id}
                style={styles.bidCard}
                activeOpacity={0.7}
                onPress={() => {
                  // Only show details modal for non-accepted bids
                  // Accepted bids use the Setup button
                  if (bid.bid_status !== 'accepted') {
                    setSelectedBid(bid);
                    setShowDetailsModal(true);
                  }
                }}
              >
                <View style={styles.bidHeader}>
                  <View style={styles.bidTitleContainer}>
                    <Text style={styles.bidTitle} numberOfLines={2}>
                      {bid.project_title}
                    </Text>
                    {bid.project_location && (
                      <View style={styles.locationContainer}>
                        <Feather name="map-pin" size={12} color={COLORS.textSecondary} />
                        <Text style={styles.locationText} numberOfLines={1}>
                          {bid.project_location}
                        </Text>
                      </View>
                    )}
                  </View>
                  <View style={[styles.statusBadge, { backgroundColor: statusConfig.bg }]}>
                    <Feather name={statusConfig.icon} size={12} color={statusConfig.color} />
                    <Text style={[styles.statusText, { color: statusConfig.color }]}>
                      {statusConfig.label}
                    </Text>
                  </View>
                </View>

                <View style={styles.bidBody}>
                  <View style={styles.bidInfoRow}>
                    <View style={styles.bidInfoItem}>
                      <Text style={styles.bidInfoLabel}>Your Bid</Text>
                      <Text style={styles.bidInfoValue}>{formatCost(bid.proposed_cost)}</Text>
                    </View>
                    <View style={styles.bidInfoItem}>
                      <Text style={styles.bidInfoLabel}>Timeline</Text>
                      <Text style={styles.bidInfoValue}>{bid.estimated_timeline} months</Text>
                    </View>
                  </View>

                  {(bid.budget_range_min && bid.budget_range_max) && (
                    <View style={styles.budgetContainer}>
                      <Text style={styles.budgetLabel}>Project Budget</Text>
                      <Text style={styles.budgetValue}>
                        {formatBudget(bid.budget_range_min, bid.budget_range_max)}
                      </Text>
                    </View>
                  )}

                  {bid.contractor_notes && (
                    <View style={styles.notesContainer}>
                      <Text style={styles.notesLabel}>Your Notes</Text>
                      <Text style={styles.notesText} numberOfLines={3}>
                        {bid.contractor_notes}
                      </Text>
                    </View>
                  )}
                </View>

                <View style={styles.bidFooter}>
                  <View style={styles.footerLeft}>
                    <View style={styles.dateContainer}>
                      <Feather name="calendar" size={12} color={COLORS.textMuted} />
                      <Text style={styles.dateText}>
                        Submitted {formatDate(bid.submitted_at)}
                      </Text>
                    </View>
                    {bid.owner_name && (
                      <View style={styles.ownerContainer}>
                        <Feather name="user" size={12} color={COLORS.textMuted} />
                        <Text style={styles.ownerText}>{bid.owner_name}</Text>
                      </View>
                    )}
                  </View>
                  {bid.bid_status === 'accepted' && (
                    <TouchableOpacity
                      style={styles.setupButton}
                      onPress={async (e) => {
                        e.stopPropagation(); // Prevent card click
                        await handleAcceptedBidClick(bid);
                      }}
                      activeOpacity={0.8}
                    >
                      <Feather name="settings" size={14} color="#FFFFFF" />
                      <Text style={styles.setupButtonText}>Setup</Text>
                    </TouchableOpacity>
                  )}
                </View>
              </TouchableOpacity>
            );
          })
        )}
      </ScrollView>

      {/* Bid Details Modal */}
      <Modal
        visible={showDetailsModal}
        animationType="slide"
        presentationStyle="pageSheet"
        onRequestClose={() => setShowDetailsModal(false)}
      >
        {selectedBid && (
          <View style={[styles.modalContainer, { paddingTop: statusBarHeight }]}>
            <View style={styles.modalHeader}>
              <TouchableOpacity
                style={styles.modalCloseButton}
                onPress={() => setShowDetailsModal(false)}
                activeOpacity={0.7}
              >
                <Feather name="x" size={24} color={COLORS.text} />
              </TouchableOpacity>
              <Text style={styles.modalTitle}>Bid Details</Text>
              <View style={{ width: 40 }} />
            </View>

            <ScrollView style={styles.modalContent} showsVerticalScrollIndicator={false}>
              {/* Status Badge */}
              <View style={[styles.modalStatusBadge, { backgroundColor: getBidStatusConfig(selectedBid.bid_status).bg }]}>
                <Feather name={getBidStatusConfig(selectedBid.bid_status).icon} size={20} color={getBidStatusConfig(selectedBid.bid_status).color} />
                <Text style={[styles.modalStatusText, { color: getBidStatusConfig(selectedBid.bid_status).color }]}>
                  {getBidStatusConfig(selectedBid.bid_status).label}
                </Text>
              </View>

              {/* Project Information */}
              <View style={styles.modalSection}>
                <Text style={styles.modalSectionTitle}>Project Information</Text>
                <View style={styles.modalCard}>
                  <Text style={styles.modalProjectTitle}>{selectedBid.project_title}</Text>
                  {selectedBid.project_description && (
                    <Text style={styles.modalProjectDescription}>{selectedBid.project_description}</Text>
                  )}
                  {selectedBid.project_location && (
                    <View style={styles.modalInfoRow}>
                      <Feather name="map-pin" size={16} color={COLORS.textSecondary} />
                      <Text style={styles.modalInfoText}>{selectedBid.project_location}</Text>
                    </View>
                  )}
                  {(selectedBid.budget_range_min && selectedBid.budget_range_max) && (
                    <View style={styles.modalInfoRow}>
                      <Feather name="dollar-sign" size={16} color={COLORS.textSecondary} />
                      <Text style={styles.modalInfoText}>
                        Budget: {formatBudget(selectedBid.budget_range_min, selectedBid.budget_range_max)}
                      </Text>
                    </View>
                  )}
                  {selectedBid.owner_name && (
                    <View style={styles.modalInfoRow}>
                      <Feather name="user" size={16} color={COLORS.textSecondary} />
                      <Text style={styles.modalInfoText}>Owner: {selectedBid.owner_name}</Text>
                    </View>
                  )}
                </View>
              </View>

              {/* Project Specifications */}
              <View style={styles.modalSection}>
                <Text style={styles.modalSectionTitle}>Project Specifications</Text>
                <View style={styles.modalCard}>
                  <View style={styles.specGrid}>
                    {selectedBid.property_type && (
                      <View style={styles.specItem}>
                        <Feather name="home" size={18} color={COLORS.primary} />
                        <View style={styles.specContent}>
                          <Text style={styles.specLabel}>Property Type</Text>
                          <Text style={styles.specValue}>{selectedBid.property_type}</Text>
                        </View>
                      </View>
                    )}
                    {selectedBid.type_name && (
                      <View style={styles.specItem}>
                        <Feather name="tool" size={18} color={COLORS.primary} />
                        <View style={styles.specContent}>
                          <Text style={styles.specLabel}>Contractor Type</Text>
                          <Text style={styles.specValue}>{selectedBid.type_name}</Text>
                        </View>
                      </View>
                    )}
                    {selectedBid.lot_size && (
                      <View style={styles.specItem}>
                        <Feather name="maximize" size={18} color={COLORS.primary} />
                        <View style={styles.specContent}>
                          <Text style={styles.specLabel}>Lot Size</Text>
                          <Text style={styles.specValue}>{selectedBid.lot_size} sqm</Text>
                        </View>
                      </View>
                    )}
                    {selectedBid.floor_area && (
                      <View style={styles.specItem}>
                        <Feather name="square" size={18} color={COLORS.primary} />
                        <View style={styles.specContent}>
                          <Text style={styles.specLabel}>Floor Area</Text>
                          <Text style={styles.specValue}>{selectedBid.floor_area} sqm</Text>
                        </View>
                      </View>
                    )}
                    {selectedBid.to_finish && (
                      <View style={styles.specItem}>
                        <Feather name="clock" size={18} color={COLORS.primary} />
                        <View style={styles.specContent}>
                          <Text style={styles.specLabel}>Expected Duration</Text>
                          <Text style={styles.specValue}>{selectedBid.to_finish} months</Text>
                        </View>
                      </View>
                    )}
                    {selectedBid.bidding_due && (
                      <View style={styles.specItem}>
                        <Feather name="calendar" size={18} color={COLORS.primary} />
                        <View style={styles.specContent}>
                          <Text style={styles.specLabel}>Bidding Deadline</Text>
                          <Text style={styles.specValue}>{formatDate(selectedBid.bidding_due)}</Text>
                        </View>
                      </View>
                    )}
                  </View>
                </View>
              </View>

              {/* Project Documents */}
              <View style={styles.modalSection}>
                <Text style={styles.modalSectionTitle}>Project Documents</Text>
                <View style={styles.modalCard}>
                  {selectedBid.project_files && selectedBid.project_files.length > 0 ? (
                    <View style={styles.documentsGrid}>
                      {selectedBid.project_files.map((file, index) => {
                        const fileUrl = `http://192.168.254.113:8083/storage/${file.file_path}`;
                        const isImage = file.file_path.match(/\.(jpg|jpeg|png|gif)$/i);
                        const fileTypeLabel = file.file_type.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');

                        return (
                          <View key={index} style={styles.documentItem}>
                            <View style={styles.documentHeader}>
                              <Feather
                                name={isImage ? "image" : "file-text"}
                                size={16}
                                color={COLORS.primary}
                              />
                              <Text style={styles.documentLabel}>{fileTypeLabel}</Text>
                            </View>
                            {isImage ? (
                              <TouchableOpacity
                                onPress={() => Linking.openURL(fileUrl)}
                                activeOpacity={0.8}
                              >
                                <Image
                                  source={{ uri: fileUrl }}
                                  style={styles.documentImage}
                                  resizeMode="cover"
                                />
                              </TouchableOpacity>
                            ) : (
                              <TouchableOpacity
                                style={styles.documentButton}
                                onPress={() => Linking.openURL(fileUrl)}
                                activeOpacity={0.7}
                              >
                                <Feather name="download" size={16} color={COLORS.primary} />
                                <Text style={styles.documentButtonText}>Open Document</Text>
                              </TouchableOpacity>
                            )}
                          </View>
                        );
                      })}
                    </View>
                  ) : (
                    <View style={styles.noDataContainer}>
                      <Feather name="folder" size={32} color={COLORS.textMuted} />
                      <Text style={styles.noDataText}>No documents uploaded for this project</Text>
                    </View>
                  )}
                </View>
              </View>

              {/* Your Bid Information */}
              <View style={styles.modalSection}>
                <Text style={styles.modalSectionTitle}>Your Bid</Text>
                <View style={styles.modalCard}>
                  <View style={styles.modalBidGrid}>
                    <View style={styles.modalBidItem}>
                      <Text style={styles.modalBidLabel}>Proposed Cost</Text>
                      <Text style={styles.modalBidValue}>{formatCost(selectedBid.proposed_cost)}</Text>
                    </View>
                    <View style={styles.modalBidItem}>
                      <Text style={styles.modalBidLabel}>Timeline</Text>
                      <Text style={styles.modalBidValue}>{selectedBid.estimated_timeline} months</Text>
                    </View>
                  </View>
                  {selectedBid.contractor_notes && (
                    <View style={styles.modalNotesContainer}>
                      <Text style={styles.modalNotesLabel}>Additional Notes</Text>
                      <Text style={styles.modalNotesText}>{selectedBid.contractor_notes}</Text>
                    </View>
                  )}
                  <View style={styles.modalInfoRow}>
                    <Feather name="calendar" size={16} color={COLORS.textMuted} />
                    <Text style={styles.modalInfoTextSmall}>
                      Submitted on {formatDate(selectedBid.submitted_at)}
                    </Text>
                  </View>
                </View>
              </View>

              {/* Attached Documents for this Bid */}
              {selectedBid.files && selectedBid.files.length > 0 && (
                <View style={styles.modalSection}>
                  <Text style={styles.modalSectionTitle}>Attached Documents ({selectedBid.files.length})</Text>
                  <View style={styles.filesCard}>
                    {selectedBid.files.map((file, idx) => (
                      <TouchableOpacity
                        key={file.file_id || idx}
                        style={[styles.fileItem, idx === selectedBid.files.length - 1 && { borderBottomWidth: 0 }]}
                        onPress={() => handleOpenFile(file.file_path)}
                      >
                        <View style={styles.fileIcon}>
                          <Feather name={file.file_name && file.file_name.endsWith('.pdf') ? 'file-text' : 'file'} size={20} color={COLORS.primary} />
                        </View>
                        <View style={styles.fileInfo}>
                          <Text style={styles.fileName} numberOfLines={1}>{file.file_name}</Text>
                          {file.description && <Text style={styles.fileDescription} numberOfLines={1}>{file.description}</Text>}
                        </View>
                        <Feather name="download" size={18} color={COLORS.textMuted} />
                      </TouchableOpacity>
                    ))}
                  </View>
                </View>
              )}

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
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 16,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  backButton: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
    borderRadius: 20,
    backgroundColor: COLORS.background,
  },
  headerContent: {
    flex: 1,
    marginLeft: 12,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
  },
  headerSubtitle: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginTop: 2,
  },
  statsContainer: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    paddingVertical: 16,
    gap: 12,
  },
  statCard: {
    flex: 1,
    backgroundColor: COLORS.surface,
    padding: 16,
    borderRadius: 12,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  statValue: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.warning,
  },
  statLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginTop: 4,
  },
  filterContainer: {
    maxHeight: 50,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    backgroundColor: COLORS.surface,
  },
  filterContent: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    gap: 8,
  },
  filterTab: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  filterTabActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  filterTabText: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  filterTabTextActive: {
    color: COLORS.surface,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 32,
  },
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 60,
  },
  emptyIconContainer: {
    width: 96,
    height: 96,
    borderRadius: 48,
    backgroundColor: COLORS.border,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  emptySubtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
  },
  bidCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
    overflow: 'hidden',
  },
  bidHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  bidTitleContainer: {
    flex: 1,
    marginRight: 12,
  },
  bidTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  locationContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 4,
  },
  locationText: {
    fontSize: 12,
    color: COLORS.textSecondary,
    flex: 1,
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 12,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  bidBody: {
    padding: 16,
  },
  bidInfoRow: {
    flexDirection: 'row',
    gap: 12,
    marginBottom: 12,
  },
  bidInfoItem: {
    flex: 1,
  },
  bidInfoLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginBottom: 4,
    textTransform: 'uppercase',
    fontWeight: '600',
  },
  bidInfoValue: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
  },
  budgetContainer: {
    marginTop: 8,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  budgetLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginBottom: 4,
    textTransform: 'uppercase',
    fontWeight: '600',
  },
  budgetValue: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  notesContainer: {
    marginTop: 12,
    padding: 12,
    backgroundColor: COLORS.background,
    borderRadius: 8,
  },
  notesLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginBottom: 6,
    textTransform: 'uppercase',
    fontWeight: '600',
  },
  notesText: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 18,
  },
  bidFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: COLORS.background,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  footerLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    flex: 1,
  },
  dateContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  dateText: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  ownerContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  ownerText: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  // Modal styles
  modalContainer: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 16,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  modalCloseButton: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
    borderRadius: 20,
    backgroundColor: COLORS.background,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
  },
  modalContent: {
    flex: 1,
    padding: 16,
  },
  modalStatusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderRadius: 12,
    alignSelf: 'flex-start',
    marginBottom: 24,
  },
  modalStatusText: {
    fontSize: 14,
    fontWeight: '600',
  },
  modalSection: {
    marginBottom: 24,
  },
  modalSectionTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    marginBottom: 12,
  },
  modalCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  modalProjectTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  modalProjectDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: 12,
  },
  modalInfoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginTop: 8,
  },
  modalInfoText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    flex: 1,
  },
  modalInfoTextSmall: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  modalBidGrid: {
    flexDirection: 'row',
    gap: 16,
    marginBottom: 16,
  },
  modalBidItem: {
    flex: 1,
  },
  modalBidLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginBottom: 6,
    textTransform: 'uppercase',
    fontWeight: '600',
  },
  modalBidValue: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.primary,
  },
  modalNotesContainer: {
    marginBottom: 16,
    padding: 12,
    backgroundColor: COLORS.background,
    borderRadius: 8,
  },
  modalNotesLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginBottom: 8,
    textTransform: 'uppercase',
    fontWeight: '600',
  },
  modalNotesText: {
    fontSize: 14,
    color: COLORS.text,
    lineHeight: 20,
  },
  specGrid: {
    gap: 12,
  },
  specItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  specContent: {
    flex: 1,
  },
  specLabel: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginBottom: 2,
    textTransform: 'uppercase',
    fontWeight: '600',
  },
  specValue: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
  },
  documentsGrid: {
    gap: 12,
  },
  documentItem: {
    marginBottom: 12,
  },
  documentHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  documentLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
    textTransform: 'capitalize',
  },
  documentImage: {
    width: '100%',
    height: 200,
    borderRadius: 8,
    backgroundColor: COLORS.border,
  },
  documentButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 12,
    paddingHorizontal: 16,
    backgroundColor: COLORS.primaryLight,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: COLORS.primary,
  },
  documentButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.primary,
  },
  noDataContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 32,
  },
  noDataText: {
    fontSize: 14,
    color: COLORS.textMuted,
    marginTop: 12,
    textAlign: 'center',
  },
  setupButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primary,
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 8,
  },
  setupButtonText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#FFFFFF',
    marginLeft: 6,
  },
});
