// @ts-nocheck
import React, { useState, useCallback, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  StyleSheet,
  ScrollView,
  Dimensions,
  StatusBar,
  Modal,
  NativeScrollEvent,
  NativeSyntheticEvent,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import { Image } from 'expo-image';
import { WebView } from 'react-native-webview';
import ImageFallback from '../../components/ImageFallback';
import ReportPostModal from '../../components/reportPostModal';
import { api_config, api_request } from '../../config/api';
import { storage_service } from '../../utils/storage';
import { post_service } from '../../services/post_service';

const { width: SCREEN_WIDTH, height: SCREEN_HEIGHT } = Dimensions.get('window');

const defaultOwnerAvatar = require('../../../assets/images/pictures/property_owner_default.png');
const watermarkImage = require('../../../assets/images/pictures/legatura_watermark.png');

interface Project {
  project_id: number;
  project_title: string;
  project_description: string;
  project_location: string;
  budget_range_min: number;
  budget_range_max: number;
  lot_size?: number;
  floor_area?: number;
  property_type: string;
  type_name: string;
  project_status: string;
  bidding_deadline?: string;
  created_at: string;
  owner_name?: string;
  owner_profile_pic?: string;
  bids_count?: number;
  files?: any[];
}

interface ProjectPostDetailProps {
  project: Project;
  onClose: () => void;
  onPlaceBid?: () => void;
  userRole?: 'owner' | 'contractor';
  canBid?: boolean; // Whether user has permission to bid (owner/representative only)
  onViewOwnerProfile?: () => void;
}

/**
 * Classify whether a file is an important/protected document (building permit, land title).
 * These are shown ONLY in the expanded detail view with watermark overlay.
 */
const classifyImportant = (fileType: string, rawPath: string): boolean => {
  const type = (fileType || '').toLowerCase();
  const path = (rawPath || '').toLowerCase();
  if (/building.?permit|title_of_land|title-of-land|land.?title/i.test(type)) return true;
  if (type === 'building permit' || type === 'title') return true;
  // paths like project_files/titles/... or project_files/title/... should be treated as title
  if (/(\/(titles?)\/)|\btitle(s?)\b/.test(path)) return true;
  const normalized = path.replace(/[^a-z0-9]+/g, ' ');
  const tokens = normalized.split(/\s+/).filter(Boolean);
  const has = (w: string) => tokens.includes(w);
  if (has('building') && has('permit')) return true;
  if (has('title') && has('land')) return true;
  if (/(building_permit|building-permit|title_of_land|title-of-land)/.test(path)) return true;
  return false;
};

export default function ProjectPostDetail({ project, onClose, onPlaceBid, userRole = 'contractor', canBid = true, onViewOwnerProfile }: ProjectPostDetailProps) {
  const insets = useSafeAreaInsets();
  const [imageViewerVisible, setImageViewerVisible] = useState(false);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [docViewerVisible, setDocViewerVisible] = useState(false);
  const [docViewerIndex, setDocViewerIndex] = useState(0);
  const [menuVisible, setMenuVisible] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);
  const [reportModalVisible, setReportModalVisible] = useState(false);

  const submitReport = useCallback(async (reason: string, details?: string) => {
    const res = await post_service.report_post('project', project.project_id, reason, details);
    return {
      success: !!res.success,
      message: res.message || (res.success ? 'Report submitted.' : 'Unable to submit report right now.'),
    };
  }, [project.project_id]);

  const openReportReasons = useCallback(() => {
    setMenuVisible(false);
    setReportModalVisible(true);
  }, [submitReport]);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
  };

  const getDaysRemaining = (deadline?: string) => {
    if (!deadline) return null;
    const now = new Date();
    const deadlineDate = new Date(deadline);
    const diffTime = deadlineDate.getTime() - now.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
  };

  // Local owner details fetched if project lacks owner/user info
  const [ownerDetails, setOwnerDetails] = useState<any>(null);
  const [loadingOwnerDetails, setLoadingOwnerDetails] = useState(false);

  // Build owner profile image URL with robust fallbacks (include fetched ownerDetails)
  let ownerProfilePath: any = null;
  ownerProfilePath = project?.owner_profile_pic || project?.profile_pic || project?.user?.profile_pic || project?.user_profile_pic || project?.user?.profilePic || project?.profilePic || project?.avatar || project?.user?.avatar || project?.owner?.profile_pic || project?.owner_profile || project?.ownerProfile || project?.owner?.user?.profile_pic || null;
  if (!ownerProfilePath && ownerDetails) {
    ownerProfilePath = ownerDetails.profile_pic || ownerDetails.owner_profile_pic || ownerDetails.avatar || ownerDetails.profilePic || ownerDetails.user?.profile_pic || ownerDetails.user?.profilePic || ownerDetails.profile || null;
  }
  if (!ownerProfilePath && project?.owner && typeof project.owner === 'object') {
    ownerProfilePath = project.owner.profile_pic || project.owner.avatar || project.owner.profilePic || project.owner.profile || null;
  }
  const ownerProfileUrl = ownerProfilePath ? (String(ownerProfilePath).startsWith('http') ? String(ownerProfilePath) : `${api_config.base_url}/storage/${String(ownerProfilePath)}`) : null;

  const daysRemaining = getDaysRemaining(project.bidding_deadline);

  useEffect(() => {
    let isMounted = true;
    const fetchProjectDetails = async () => {
      if (!project || !project.project_id) return;
      // If meaningful owner info is already present, skip fetching
      const hasOwnerInfo = !!(
        project.owner_full_name ||
        project.owner_name ||
        project.owner_profile_pic ||
        project.profile_pic ||
        project.user?.profile_pic ||
        project.user?.username ||
        project.user?.first_name ||
        (project.owner && (project.owner.first_name || project.owner.profile_pic || project.owner.name))
      );
      if (hasOwnerInfo) return;
      try {
        setLoadingOwnerDetails(true);
        // Try to read stored user id and pass as query param because
        // the owner project details endpoint requires `user_id` query param.
        let userId = null;
        try {
          const saved = await storage_service.get_user_data();
          userId = saved ? (saved.user_id || saved.id || saved.user?.id || null) : null;
        } catch (e) {
          console.warn('Could not read stored user data for owner fetch', e);
        }

        const endpoint = userId
          ? `/api/owner/projects/${project.project_id}?user_id=${userId}`
          : `/api/owner/projects/${project.project_id}`;

        const resp = await api_request(endpoint);
        let handled = false;
        if (resp && resp.success && resp.data) {
          const data = resp.data.data || resp.data;
          console.log('[ProjectPostDetail] fetched project details:', data);
          // Preferred shape: { owner: {...} } or { user: {...} }
          if (isMounted) {
            if (data.owner || data.user) {
              const mergedOwner = {
                ...(data.owner || data.user),
                type_name: data.type_name || null,
                type_id: data.type_id || null,
                owner_profile_pic: data.owner_profile_pic || null,
              };
              setOwnerDetails(mergedOwner);
              handled = true;
            } else {
              // Some endpoints return flat owner fields (first_name, last_name, owner_id)
              const maybeFirst = data.first_name || data.owner_first_name || null;
              const maybeLast = data.last_name || data.owner_last_name || null;
              if (maybeFirst || maybeLast) {
                const constructed: any = {
                  first_name: maybeFirst,
                  last_name: maybeLast,
                  owner_id: data.owner_id || data.ownerId || null,
                  user_id: data.owner_user_id || data.user_id || null,
                  profile_pic: data.owner_profile_pic || data.profile_pic || null,
                };
                constructed.owner_full_name = [constructed.first_name, constructed.last_name].filter(Boolean).join(' ').trim();
                constructed.type_name = data.type_name || null;
                constructed.type_id = data.type_id || null;
                setOwnerDetails(constructed);
                handled = true;
              } else {
                setOwnerDetails(null);
              }
            }
          }
        }

        // If owner details were not provided or request was unauthorized, try public endpoint
        if (!handled) {
          try {
            const publicResp = await api_request(`/api/projects/${project.project_id}/public`);
            if (publicResp && publicResp.success && publicResp.data) {
              const pdata = publicResp.data.data || publicResp.data;
              console.log('[ProjectPostDetail] fetched public project details:', pdata);
              // public endpoint returns owner_full_name and owner_profile_pic
              const constructed: any = {
                owner_full_name: pdata.owner_full_name || pdata.owner_name || null,
                profile_pic: pdata.owner_profile_pic || null,
                owner_profile_pic: pdata.owner_profile_pic || null,
              };
              // also attach type_name if present
              if (pdata.type_name) constructed.type_name = pdata.type_name;
              if (isMounted) setOwnerDetails(constructed);
            } else {
              // if publicResp returns failure, log message
              console.warn('Public project details not available', publicResp?.message || publicResp);
            }
          } catch (err) {
            console.warn('Failed to fetch public project details', err?.message || err);
          }
        }
      } catch (e) {
        console.warn('Failed to fetch project owner details', e);
      } finally {
        if (isMounted) setLoadingOwnerDetails(false);
      }
    };

    fetchProjectDetails();
    return () => { isMounted = false; };
  }, [project]);

  // Debug: log project owner/user keys to diagnose missing avatar/name/type
  console.log('[ProjectPostDetail] project summary:', {
    project_id: project.project_id,
    owner_name: project.owner_name,
    owner_full_name: project.owner_full_name,
    owner_profile_pic: project.owner_profile_pic,
    profile_pic: project.profile_pic,
    user: project.user,
    owner: project.owner,
    keys: Object.keys(project || {}),
  });

  // Check if file is an image by extension only (required docs can now be docs/pdf).
  const isImageFile = (path: string, fileType?: string) => {
    if (!path) return false;
    const imagePath = path.toLowerCase();
    return imagePath.match(/\.(jpg|jpeg|png|gif|webp|bmp)(\?|$)/i) !== null;
  };

  // Process files for display — classify into optional (design) and important (protected)
  const processFiles = () => {
    if (!project.files || project.files.length === 0) return [];

    console.log('[ProjectPostDetail] Raw project.files:', JSON.stringify(project.files).substring(0, 500));

    return project.files.map((file: any) => {
      const raw = typeof file === 'string' ? file : (file.file_path || '');
      let fileType = typeof file === 'object' ? (file.file_type || '') : '';

      // Infer file type from path when backend returns only a string path
      const pathLower = (raw || '').toLowerCase();
      if (!fileType) {
        if (/(\/(titles?)\/)|\btitle(s?)\b/.test(pathLower) || pathLower.includes('title_of_land') || pathLower.includes('title-of-land')) {
          fileType = 'title';
        } else if (pathLower.includes('building_permit') || pathLower.includes('building-permit') || pathLower.includes('building permit')) {
          fileType = 'building permit';
        } else if (pathLower.includes('blueprint') || pathLower.includes('blueprints')) {
          fileType = 'blueprint';
        } else if (pathLower.includes('design') || pathLower.includes('desired_design') || pathLower.includes('designs')) {
          fileType = 'desired design';
        } else {
          fileType = '';
        }
      }

      const url = raw.startsWith('http')
        ? raw
        : `${api_config.base_url}/storage/${raw}`;

      const isProtected = classifyImportant(fileType, raw);
      const isDesign = !isProtected; // Everything that isn't protected is optional/design

      return {
        raw,
        url,
        isImage: isImageFile(raw, fileType),
        fileType,
        isProtected,
        isDesign,
      };
    });
  };

  const allFiles = processFiles();
  console.log('[ProjectPostDetail] allFiles:', allFiles.length, '| design:', allFiles.filter(f => f.isDesign).length, '| protected:', allFiles.filter(f => f.isProtected).length);
  // Optional documents — shown in the main Facebook-style collage (images only)
  const designImages = allFiles.filter(f => f.isDesign && f.isImage);
  // Important/protected documents — can be images OR files
  const requiredDocuments = allFiles.filter(f => f.isProtected);
  const [currentGallery, setCurrentGallery] = useState<any[]>([]);

  const openImageViewer = (index: number, gallery: 'design' | 'docs' = 'design') => {
    if (gallery === 'docs') {
      // Important documents use the dedicated overlay viewer
      setDocViewerIndex(index);
      setDocViewerVisible(true);
      return;
    }
    const galleryItems = designImages;
    setCurrentGallery(galleryItems);
    setSelectedImageIndex(index);
    setImageViewerVisible(true);
  };

  /** Get friendly label for a document based on fileType */
  const getDocumentLabel = (fileType: string): string => {
    const t = (fileType || '').toLowerCase();
    if (t.includes('building') && t.includes('permit')) return 'Building Permit';
    if (t.includes('title')) return 'Land Title';
    if (t.includes('permit')) return 'Building Permit';
    return 'Important Document';
  };

  const getFileExtension = (rawPath: string): string => {
    const path = (rawPath || '').split('?')[0] || '';
    const fileName = path.split('/').pop() || '';
    const dotIdx = fileName.lastIndexOf('.');
    return dotIdx >= 0 ? fileName.substring(dotIdx + 1).toLowerCase() : '';
  };

  const getDocumentIcon = (rawPath: string) => {
    const ext = getFileExtension(rawPath);
    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(ext)) return 'image';
    if (ext === 'pdf') return 'picture-as-pdf';
    if (ext === 'doc' || ext === 'docx') return 'description';
    if (ext === 'xls' || ext === 'xlsx') return 'table-chart';
    if (ext === 'txt') return 'notes';
    return 'insert-drive-file';
  };

  const getDocumentFileName = (rawPath: string): string => {
    const path = (rawPath || '').split('?')[0] || '';
    return path.split('/').pop() || 'document';
  };

  const getDocumentDisplayName = (fileType: string, rawPath: string): string => {
    const ext = getFileExtension(rawPath);
    const extText = ext ? ext.toUpperCase() : 'FILE';
    const base = getDocumentLabel(fileType);
    return `${base} (${extText})`;
  };

  /**
   * Build a server-side viewer URL. The Laravel endpoint serves HTML that
   * fetches the file from the same origin, avoiding WebView CORS issues.
   * Returns null for unsupported extensions so the fallback card shows.
   */
  const getDocViewerUrl = (doc: any): string | null => {
    const ext = getFileExtension(doc?.raw || '');
    if (!doc?.raw) return null;
    if (!['pdf', 'docx', 'doc', 'txt', 'csv', 'rtf'].includes(ext)) return null;
    return `${api_config.base_url}/document-viewer?file=${encodeURIComponent(doc.raw)}`;
  };

  /** Navigate to prev/next document in the viewer */
  const navigateDoc = (direction: 'prev' | 'next') => {
    const newIndex = direction === 'next'
      ? Math.min(docViewerIndex + 1, requiredDocuments.length - 1)
      : Math.max(docViewerIndex - 1, 0);
    setDocViewerIndex(newIndex);
  };

  // Collage sizing
  const H_PADDING = 16; // matches surrounding padding
  const GAP = 2; // small gap between images
  const usableWidth = SCREEN_WIDTH - H_PADDING * 2;
  const halfSize = Math.floor((usableWidth - GAP) / 2);
  const largeWidth = Math.floor(usableWidth * 0.66);
  const smallColumnWidth = usableWidth - largeWidth - GAP;
  const singleHeight = Math.floor(usableWidth * 0.56);

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color="#1A1A1A" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Project Post</Text>
        <View style={styles.cardMenuWrap}>
          <TouchableOpacity style={styles.menuButton} onPress={() => setMenuVisible(v => !v)}>
            <MaterialIcons name="more-vert" size={24} color="#1A1A1A" />
          </TouchableOpacity>
          {menuVisible && (
            <View style={styles.cardMenuDropdown}>
              <TouchableOpacity style={styles.cardMenuItem} onPress={openReportReasons} disabled={actionLoading}>
                <Text style={styles.cardMenuDangerText}>Report</Text>
              </TouchableOpacity>
              {actionLoading && (
                <View style={styles.menuLoadingRow}>
                  <ActivityIndicator size="small" color="#EEA24B" />
                </View>
              )}
            </View>
          )}
        </View>
      </View>

      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {/* Post Header - Owner Info */}
        <View style={styles.postHeader}>
          <TouchableOpacity
            style={styles.ownerInfo}
            activeOpacity={onViewOwnerProfile ? 0.7 : 1}
            onPress={onViewOwnerProfile}
            disabled={!onViewOwnerProfile}
          >
            <ImageFallback
              uri={ownerProfileUrl}
              defaultImage={defaultOwnerAvatar}
              style={styles.ownerAvatar}
              resizeMode="cover"
            />
              <View>
                {/* Owner display name - prefer explicit owner_full_name or assembled name from owner/user payloads */}
                {(() => {
                  const first = project.owner_first_name || project.first_name || project.owner?.first_name || project.user?.first_name || ownerDetails?.first_name || '';
                  const middle = project.owner_middle_name || project.middle_name || project.owner?.middle_name || project.user?.middle_name || ownerDetails?.middle_name || '';
                  const last = project.owner_last_name || project.last_name || project.owner?.last_name || project.user?.last_name || ownerDetails?.last_name || '';
                  const assembled = [first, middle, last].filter(Boolean).join(' ').trim();
                  const displayName = project.owner_full_name || project.owner_name || assembled || ownerDetails?.owner_full_name || ownerDetails?.name || project.user?.username || 'Property Owner';
                  return <Text style={styles.ownerName}>{displayName}</Text>;
                })()}

                <Text style={styles.postDate}>{formatDate(project.post_created_at || project.posted_at || project.created_at)}</Text>

                {/* contractor/type removed from header (shown in Project Details badge) */}
              </View>
          </TouchableOpacity>
        </View>

        {/* Project Title */}
        <View style={styles.titleSection}>
          <Text style={styles.projectTitle}>{project.project_title}</Text>

        </View>

        {/* Project Description */}
        <View style={styles.descriptionSection}>
          <Text style={styles.descriptionText}>{project.project_description}</Text>
        </View>



        {/* Project Details */}
        <View style={styles.detailsSection}>
          <Text style={styles.sectionTitle}>Project Details</Text>
          <View style={{ marginBottom: 8 }}>
            <View style={styles.typeBadge}>
              <MaterialIcons name="business" size={14} color="#EC7E00" />
              <Text style={styles.typeText}>{ownerDetails?.type_name || project.type_name || 'General'}</Text>
            </View>
          </View>

          <View style={styles.detailRow}>
            <MaterialIcons name="location-on" size={20} color="#EC7E00" />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Location</Text>
              <Text style={styles.detailValue}>{project.project_location}</Text>
            </View>
          </View>

          <View style={styles.detailRow}>
            <MaterialIcons name="account-balance-wallet" size={20} color="#EC7E00" />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Budget Range</Text>
              <Text style={styles.detailValue}>
                {formatCurrency(project.budget_range_min)} - {formatCurrency(project.budget_range_max)}
              </Text>
            </View>
          </View>

          {project.lot_size && (
            <View style={styles.detailRow}>
              <MaterialIcons name="straighten" size={20} color="#EC7E00" />
              <View style={styles.detailContent}>
                <Text style={styles.detailLabel}>Lot Size</Text>
                <Text style={styles.detailValue}>{project.lot_size} sqm</Text>
              </View>
            </View>
          )}

          {project.floor_area && (
            <View style={styles.detailRow}>
              <MaterialIcons name="home" size={20} color="#EC7E00" />
              <View style={styles.detailContent}>
                <Text style={styles.detailLabel}>Floor Area</Text>
                <Text style={styles.detailValue}>{project.floor_area} sqm</Text>
              </View>
            </View>
          )}

          <View style={styles.detailRow}>
            <MaterialIcons name="domain" size={20} color="#EC7E00" />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Property Type</Text>
              <Text style={styles.detailValue}>{project.property_type}</Text>
            </View>
          </View>

          {daysRemaining !== null && (
            <View style={styles.detailRow}>
              <MaterialIcons name="access-time" size={20} color="#EC7E00" />
              <View style={styles.detailContent}>
                <Text style={styles.detailLabel}>Bidding Deadline</Text>
                <Text style={[styles.detailValue, daysRemaining <= 3 && styles.urgentText]}>
                  {daysRemaining > 0 ? `${daysRemaining} days remaining` : 'Due today'}
                </Text>
              </View>
            </View>
          )}

          <View style={styles.detailRow}>
            <MaterialIcons name="gavel" size={20} color="#EC7E00" />
            <View style={styles.detailContent}>
              <Text style={styles.detailLabel}>Bids Received</Text>
              <Text style={styles.detailValue}>{project.bids_count || 0} bids</Text>
            </View>
          </View>
        </View>

        {/* Design Images — Facebook-style collage using expo-image */}
        {designImages.length > 0 && (
          <View style={[styles.imagesSection, { paddingHorizontal: H_PADDING }]}>
            {designImages.length === 1 && (
              <TouchableOpacity onPress={() => openImageViewer(0, 'design')} activeOpacity={0.9}>
                <Image
                  source={{ uri: designImages[0].url }}
                  style={{ width: usableWidth, height: singleHeight, borderRadius: 8 }}
                  contentFit="cover"
                  transition={200}
                  cachePolicy="memory-disk"
                />
              </TouchableOpacity>
            )}

            {designImages.length === 2 && (
              <View style={{ flexDirection: 'row' }}>
                {designImages.map((file, index) => (
                  <TouchableOpacity key={index} onPress={() => openImageViewer(index, 'design')} activeOpacity={0.9}>
                    <Image
                      source={{ uri: file.url }}
                      style={{ width: halfSize, height: halfSize, borderRadius: 8, marginLeft: index === 1 ? GAP : 0 }}
                      contentFit="cover"
                      transition={200}
                      cachePolicy="memory-disk"
                    />
                  </TouchableOpacity>
                ))}
              </View>
            )}

            {designImages.length === 3 && (
              <View style={{ flexDirection: 'row' }}>
                <TouchableOpacity onPress={() => openImageViewer(0, 'design')} activeOpacity={0.9}>
                  <Image
                    source={{ uri: designImages[0].url }}
                    style={{ width: largeWidth, height: halfSize * 2 + GAP, borderRadius: 8, marginRight: GAP }}
                    contentFit="cover"
                    transition={200}
                    cachePolicy="memory-disk"
                  />
                </TouchableOpacity>
                <View>
                  {designImages.slice(1).map((file, idx) => (
                    <TouchableOpacity key={idx + 1} onPress={() => openImageViewer(idx + 1, 'design')} activeOpacity={0.9}>
                      <Image
                        source={{ uri: file.url }}
                        style={{ width: smallColumnWidth, height: halfSize, borderRadius: 8, marginTop: idx === 1 ? GAP : 0 }}
                        contentFit="cover"
                        transition={200}
                        cachePolicy="memory-disk"
                      />
                    </TouchableOpacity>
                  ))}
                </View>
              </View>
            )}

            {designImages.length >= 4 && (
              <View style={{ flexDirection: 'row', flexWrap: 'wrap', width: usableWidth }}>
                {designImages.slice(0, 4).map((file, index) => (
                  <TouchableOpacity
                    key={index}
                    onPress={() => openImageViewer(index, 'design')}
                    activeOpacity={0.9}
                    style={{
                      width: halfSize,
                      height: halfSize,
                      marginLeft: index % 2 === 1 ? GAP : 0,
                      marginTop: index >= 2 ? GAP : 0,
                      borderRadius: 8,
                      overflow: 'hidden',
                    }}
                  >
                    <Image
                      source={{ uri: file.url }}
                      style={{ width: halfSize, height: halfSize }}
                      contentFit="cover"
                      transition={200}
                      cachePolicy="memory-disk"
                    />
                    {index === 3 && designImages.length > 4 && (
                      <View style={styles.moreOverlay}>
                        <Text style={styles.moreText}>+{designImages.length - 4}</Text>
                      </View>
                    )}
                  </TouchableOpacity>
                ))}
              </View>
            )}
          </View>
        )}

        {/* Required Documents (Building Permit & Title) — overlay-style viewer */}
        {/* Important documents are shown AFTER optional images, with watermark overlay */}
        {requiredDocuments.length > 0 && (
          <View style={styles.documentsSection}>
            <Text style={styles.sectionTitle}>Important Documents</Text>
            <Text style={styles.documentNotice}>
              Protected files from project posting. Tap to view securely.
            </Text>
            <View style={styles.importantDocsList}>
              {requiredDocuments.slice(0, 22).map((doc, index) => (
                <TouchableOpacity
                  key={index}
                  style={styles.importantDocCard}
                  onPress={() => openImageViewer(index, 'docs')}
                  activeOpacity={0.9}
                >
                  <View style={styles.importantDocPreview}>
                    {doc.isImage ? (
                      <View style={styles.importantDocImageWrap}>
                        <Image source={{ uri: doc.url }} style={styles.importantDocImage} contentFit="cover" transition={200} cachePolicy="memory-disk" />
                        <View style={styles.thumbnailWatermarkWrapper} pointerEvents="none">
                          <Image source={watermarkImage} style={styles.importantDocWatermarkThumb} contentFit="cover" />
                        </View>
                      </View>
                    ) : (
                      <View style={styles.importantDocFileIconWrap}>
                        <MaterialIcons name={getDocumentIcon(doc.raw)} size={30} color="#D97706" />
                        <Text style={styles.importantDocExt}>{(getFileExtension(doc.raw) || 'FILE').toUpperCase()}</Text>
                      </View>
                    )}
                  </View>

                  <View style={styles.importantDocMeta}>
                    <View style={styles.importantDocTitleRow}>
                      <MaterialIcons name="lock-outline" size={14} color="#B45309" />
                      <Text style={styles.importantDocType}>{getDocumentLabel(doc.fileType)}</Text>
                    </View>
                    <Text style={styles.importantDocName} numberOfLines={1}>{getDocumentDisplayName(doc.fileType, doc.raw)}</Text>
                    <View style={styles.importantDocActions}>
                      <View style={styles.importantDocTag}>
                        <Text style={styles.importantDocTagText}>Watermarked</Text>
                      </View>
                      <View style={styles.importantDocViewBtn}>
                        <MaterialIcons name="visibility" size={16} color="#EC7E00" />
                        <Text style={styles.importantDocViewText}>View</Text>
                      </View>
                    </View>
                  </View>
                </TouchableOpacity>
              ))}
            </View>
            {requiredDocuments.length > 6 && (
              <Text style={styles.documentCountHint}>
                {requiredDocuments.length} document{requiredDocuments.length > 1 ? 's' : ''} available
              </Text>
            )}
          </View>
        )}
      </ScrollView>

      {/* Bottom Action Button - Only show Place Bid if user has permission */}
      {userRole === 'contractor' && (
        <View style={styles.bottomBar}>
          {canBid && onPlaceBid ? (
            <TouchableOpacity style={styles.bidButton} onPress={onPlaceBid} activeOpacity={0.8}>
              <MaterialIcons name="gavel" size={20} color="#FFFFFF" />
              <Text style={styles.bidButtonText}>Place Bid</Text>
            </TouchableOpacity>
          ) : (
            <View style={[styles.bidButton, { backgroundColor: '#94A3B8' }]}>
              <MaterialIcons name="visibility" size={20} color="#FFFFFF" />
              <Text style={styles.bidButtonText}>View Only</Text>
            </View>
          )}
        </View>
      )}

      {/* Image Viewer Modal — for optional/design images */}
      <Modal visible={imageViewerVisible} transparent={true} animationType="fade">
        <View style={styles.imageViewerContainer}>
          <TouchableOpacity style={styles.imageViewerClose} onPress={() => setImageViewerVisible(false)} accessibilityLabel="Close image viewer">
            <Ionicons name="close" size={36} color="#FFFFFF" />
          </TouchableOpacity>
          <ScrollView
            horizontal
            pagingEnabled
            showsHorizontalScrollIndicator={false}
            contentOffset={{ x: selectedImageIndex * SCREEN_WIDTH, y: 0 }}
            onMomentumScrollEnd={(e: NativeSyntheticEvent<NativeScrollEvent>) => {
              const idx = Math.round(e.nativeEvent.contentOffset.x / SCREEN_WIDTH);
              const gallery = currentGallery.length > 0 ? currentGallery : designImages;
              if (idx >= 0 && idx < gallery.length) {
                setSelectedImageIndex(idx);
              }
            }}
          >
            {(currentGallery.length > 0 ? currentGallery : designImages).map((file, index) => (
              <View key={index} style={styles.imageViewerPage}>
                <View style={{ width: SCREEN_WIDTH, alignItems: 'center', justifyContent: 'center' }}>
                  <Image source={{ uri: file.url }} style={styles.fullScreenImage} contentFit="contain" transition={200} cachePolicy="memory-disk" />
                </View>
              </View>
            ))}
          </ScrollView>
          {(() => {
            const gallery = currentGallery.length > 0 ? currentGallery : designImages;
            if (gallery.length > 1 && gallery.length <= 12) {
              return (
                <View style={styles.imageViewerDots}>
                  {gallery.map((_, i) => (
                    <View key={i} style={[styles.imageViewerDot, i === selectedImageIndex && styles.imageViewerDotActive]} />
                  ))}
                </View>
              );
            } else if (gallery.length > 12) {
              return (
                <View style={styles.imageCounter}>
                  <Text style={styles.imageCounterText}>
                    {selectedImageIndex + 1} / {gallery.length}
                  </Text>
                </View>
              );
            }
            return null;
          })()}
        </View>
      </Modal>

      {/* ============================================================ */}
      {/* Document Viewer Modal — overlay-style for important documents */}
      {/* Single-doc view with prev/next navigation, view-only, watermark */}
      {/* ============================================================ */}
      <Modal
        visible={docViewerVisible}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setDocViewerVisible(false)}
      >
        <StatusBar hidden />
        <View style={styles.docViewerContainer}>
          {/* Close button */}
          <TouchableOpacity
            style={styles.docViewerClose}
            onPress={() => setDocViewerVisible(false)}
            accessibilityLabel="Close document viewer"
          >
            <View style={styles.docViewerCloseCircle}>
              <Ionicons name="close" size={28} color="#FFFFFF" />
            </View>
          </TouchableOpacity>

          {/* Document label at top */}
          <View style={styles.docViewerHeader}>
            <MaterialIcons name="lock-outline" size={16} color="#FFFFFF" style={{ marginRight: 6 }} />
            <Text style={styles.docViewerHeaderText}>
              {requiredDocuments[docViewerIndex]
                ? getDocumentLabel(requiredDocuments[docViewerIndex].fileType)
                : 'Important Document'}
            </Text>
          </View>

          {/* View-only notice */}
          <View style={styles.docViewerNotice}>
            <MaterialIcons name="info-outline" size={14} color="rgba(255,255,255,0.7)" />
            <Text style={styles.docViewerNoticeText}>Protected preview with watermark</Text>
          </View>

          {/* Single document content area */}
          {(() => {
            const doc = requiredDocuments[docViewerIndex];
            if (!doc) return null;

            if (doc.isImage) {
              return (
                <View style={styles.docViewerPage}>
                  <Image
                    source={{ uri: doc.url }}
                    style={styles.docViewerImage}
                    contentFit="contain"
                    transition={200}
                    cachePolicy="memory-disk"
                  />
                  <Image
                    source={watermarkImage}
                    style={styles.docViewerWatermark}
                    contentFit="cover"
                  />
                </View>
              );
            }

            const viewerUrl = getDocViewerUrl(doc);
            if (viewerUrl) {
              return (
                <View style={styles.docViewerPage}>
                  <View style={styles.docViewerWebWrap}>
                    <WebView
                      key={`webview-${docViewerIndex}`}
                      source={{ uri: viewerUrl }}
                      style={styles.docViewerWebView}
                      originWhitelist={['*']}
                      javaScriptEnabled
                      domStorageEnabled
                      scalesPageToFit
                      mixedContentMode="always"
                      allowFileAccess
                      allowUniversalAccessFromFileURLs
                      startInLoadingState
                      cacheEnabled
                      setSupportMultipleWindows={false}
                      renderLoading={() => (
                        <View style={styles.docViewerWebLoading}>
                          <ActivityIndicator size="large" color="#EC7E00" />
                          <Text style={styles.docViewerWebLoadingText}>Loading document...</Text>
                        </View>
                      )}
                      renderError={() => (
                        <View style={styles.docViewerWebLoading}>
                          <MaterialIcons name="error-outline" size={40} color="#D97706" />
                          <Text style={styles.docViewerWebLoadingText}>Unable to load document</Text>
                          <Text style={[styles.docViewerWebLoadingText, { fontSize: 11, marginTop: 4 }]}>Tap the arrows to view other files</Text>
                        </View>
                      )}
                    />
                    {/* Watermark overlay on top of WebView */}
                    <View style={styles.docViewerWebWatermark} pointerEvents="none">
                      <Text style={styles.docViewerTextWatermarkLine}>LEGATURA CONFIDENTIAL</Text>
                      <Text style={styles.docViewerTextWatermarkLine}>LEGATURA CONFIDENTIAL</Text>
                      <Text style={styles.docViewerTextWatermarkLine}>LEGATURA CONFIDENTIAL</Text>
                      <Text style={styles.docViewerTextWatermarkLine}>LEGATURA CONFIDENTIAL</Text>
                    </View>
                  </View>
                </View>
              );
            }

            // Fallback for unsupported file types
            return (
              <View style={styles.docViewerPage}>
                <View style={styles.docViewerFileCard}>
                  <MaterialIcons name={getDocumentIcon(doc.raw)} size={46} color="#D97706" />
                  <Text style={styles.docViewerFileType}>{(getFileExtension(doc.raw) || 'FILE').toUpperCase()}</Text>
                  <Text style={styles.docViewerFileName} numberOfLines={2}>{getDocumentDisplayName(doc.fileType, doc.raw)}</Text>
                  <Text style={styles.docViewerFileHint}>Preview not available for this file type.</Text>
                  <View style={styles.docViewerTextWatermark} pointerEvents="none">
                    <Text style={styles.docViewerTextWatermarkLine}>LEGATURA CONFIDENTIAL</Text>
                    <Text style={styles.docViewerTextWatermarkLine}>LEGATURA CONFIDENTIAL</Text>
                  </View>
                </View>
              </View>
            );
          })()}

          {/* Bottom navigation bar */}
          <View style={styles.docNavBar}>
            {/* Previous button */}
            <TouchableOpacity
              style={[styles.docNavBtn, docViewerIndex === 0 && styles.docNavBtnDisabled]}
              onPress={() => navigateDoc('prev')}
              disabled={docViewerIndex === 0}
              accessibilityLabel="Previous document"
            >
              <Ionicons name="chevron-back" size={22} color={docViewerIndex === 0 ? 'rgba(255,255,255,0.25)' : '#FFFFFF'} />
              <Text style={[styles.docNavBtnText, docViewerIndex === 0 && styles.docNavBtnTextDisabled]}>Prev</Text>
            </TouchableOpacity>

            {/* Counter */}
            <View style={styles.docNavCounter}>
              <Text style={styles.docNavCounterText}>
                {docViewerIndex + 1} / {requiredDocuments.length}
              </Text>
            </View>

            {/* Next button */}
            <TouchableOpacity
              style={[styles.docNavBtn, docViewerIndex >= requiredDocuments.length - 1 && styles.docNavBtnDisabled]}
              onPress={() => navigateDoc('next')}
              disabled={docViewerIndex >= requiredDocuments.length - 1}
              accessibilityLabel="Next document"
            >
              <Text style={[styles.docNavBtnText, docViewerIndex >= requiredDocuments.length - 1 && styles.docNavBtnTextDisabled]}>Next</Text>
              <Ionicons name="chevron-forward" size={22} color={docViewerIndex >= requiredDocuments.length - 1 ? 'rgba(255,255,255,0.25)' : '#FFFFFF'} />
            </TouchableOpacity>
          </View>
        </View>
      </Modal>

      <ReportPostModal
        visible={reportModalVisible}
        onClose={() => setReportModalVisible(false)}
        onSubmit={submitReport}
      />

    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E5E5',
  },
  backButton: {
    padding: 4,
  },
  menuButton: {
    padding: 4,
    width: 40,
    alignItems: 'flex-end',
  },
  cardMenuWrap: {
    position: 'relative',
    width: 40,
    alignItems: 'flex-end',
    zIndex: 30,
  },
  cardMenuDropdown: {
    position: 'absolute',
    top: 24,
    right: 0,
    minWidth: 108,
    maxWidth: 132,
    backgroundColor: '#FFFFFF',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    paddingVertical: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.14,
    shadowRadius: 8,
    elevation: 6,
    zIndex: 20,
  },
  cardMenuItem: {
    paddingVertical: 8,
    paddingHorizontal: 12,
  },
  cardMenuDangerText: {
    fontSize: 13,
    color: '#B91C1C',
    fontWeight: '600',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1A1A1A',
  },
  scrollView: {
    flex: 1,
  },
  postHeader: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  ownerInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  ownerAvatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    marginRight: 12,
    backgroundColor: '#E5E5E5',
  },
  ownerName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1A1A1A',
    marginBottom: 2,
  },
  postDate: {
    fontSize: 13,
    color: '#666666',
  },
  titleSection: {
    paddingHorizontal: 16,
    paddingTop: 16,
    paddingBottom: 8,
  },
  projectTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#1A1A1A',
    marginBottom: 8,
  },
  typeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    backgroundColor: '#FFF3E6',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  typeText: {
    fontSize: 13,
    color: '#EC7E00',
    fontWeight: '500',
    marginLeft: 4,
  },
  descriptionSection: {
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  descriptionText: {
    fontSize: 15,
    lineHeight: 22,
    color: '#333333',
  },
  imagesSection: {
    marginVertical: 8,
  },
  singleImage: {
    width: SCREEN_WIDTH,
    height: SCREEN_WIDTH * 0.75,
  },
  twoImagesRow: {
    flexDirection: 'row',
  },
  halfImage: {
    width: SCREEN_WIDTH / 2,
    height: SCREEN_WIDTH / 2,
  },
  threeImagesContainer: {
    flexDirection: 'row',
    height: SCREEN_WIDTH * 0.75,
  },
  largeImage: {
    width: SCREEN_WIDTH * 0.67,
    height: '100%',
  },
  twoImagesColumn: {
    flex: 1,
  },
  smallImage: {
    width: SCREEN_WIDTH * 0.33,
    height: '100%',
  },
  gridContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
  },
  gridItem: {
    width: '50%',
    height: SCREEN_WIDTH / 2,
  },
  gridImage: {
    width: '100%',
    height: '100%',
  },
  watermark: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    opacity: 0.3,
  },
  watermarkSmall: {
    position: 'absolute',
    top: '18%',
    left: '18%',
    right: '18%',
    bottom: '18%',
    opacity: 0.35,
  },
  thumbnailWatermarkWrapper: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    justifyContent: 'center',
    alignItems: 'center',
  },
  thumbnailWatermark: {
    width: '100%',
    height: '100%',
    opacity: 0.45,
    zIndex: 3,
  },
  moreOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  moreText: {
    fontSize: 32,
    fontWeight: '700',
    color: '#FFFFFF',
  },
  detailsSection: {
    paddingHorizontal: 16,
    paddingVertical: 16,
    borderTopWidth: 8,
    borderTopColor: '#F0F0F0',
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1A1A1A',
    marginBottom: 16,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 16,
  },
  detailContent: {
    flex: 1,
    marginLeft: 12,
  },
  detailLabel: {
    fontSize: 13,
    color: '#666666',
    marginBottom: 2,
  },
  detailValue: {
    fontSize: 15,
    color: '#1A1A1A',
    fontWeight: '500',
  },
  urgentText: {
    color: '#E74C3C',
  },
  // ── Important Documents section ──
  documentsSection: {
    paddingHorizontal: 16,
    paddingVertical: 16,
    borderTopWidth: 8,
    borderTopColor: '#F0F0F0',
  },
  documentNotice: {
    fontSize: 13,
    color: '#888888',
    marginTop: -8,
    marginBottom: 14,
    fontStyle: 'italic',
  },
  documentsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  importantDocsList: {
    gap: 10,
  },
  importantDocCard: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E7E7E7',
    borderRadius: 10,
    padding: 10,
    flexDirection: 'row',
    alignItems: 'center',
  },
  importantDocPreview: {
    width: 78,
    height: 78,
    borderRadius: 8,
    overflow: 'hidden',
    marginRight: 12,
    backgroundColor: '#F8FAFC',
    borderWidth: 1,
    borderColor: '#ECECEC',
  },
  importantDocImageWrap: {
    width: '100%',
    height: '100%',
  },
  importantDocImage: {
    width: '100%',
    height: '100%',
  },
  importantDocWatermarkThumb: {
    width: '100%',
    height: '100%',
    opacity: 0.45,
  },
  importantDocFileIconWrap: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFF6EA',
  },
  importantDocExt: {
    marginTop: 4,
    fontSize: 10,
    fontWeight: '700',
    color: '#A15C1A',
    letterSpacing: 0.3,
  },
  importantDocMeta: {
    flex: 1,
  },
  importantDocTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  importantDocType: {
    fontSize: 13,
    fontWeight: '700',
    color: '#1A1A1A',
  },
  importantDocName: {
    marginTop: 4,
    fontSize: 12,
    color: '#6B7280',
  },
  importantDocActions: {
    marginTop: 9,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  importantDocTag: {
    backgroundColor: '#FEF3C7',
    borderWidth: 1,
    borderColor: '#FDE68A',
    borderRadius: 999,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  importantDocTagText: {
    fontSize: 10,
    fontWeight: '700',
    color: '#92400E',
  },
  importantDocViewBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    backgroundColor: '#FFF7ED',
    borderWidth: 1,
    borderColor: '#FDBA74',
    borderRadius: 6,
    paddingHorizontal: 8,
    paddingVertical: 4,
  },
  importantDocViewText: {
    fontSize: 11,
    fontWeight: '700',
    color: '#C2410C',
  },
  documentLabel: {
    padding: 8,
    fontSize: 13,
    fontWeight: '500',
    color: '#666666',
    textAlign: 'center',
  },
  documentLabelOverlay: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0,0,0,0.6)',
    paddingVertical: 8,
    paddingHorizontal: 8,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  documentLabelText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '600',
  },
  documentViewIcon: {
    position: 'absolute',
    top: 8,
    right: 8,
    backgroundColor: 'rgba(0,0,0,0.45)',
    borderRadius: 14,
    width: 28,
    height: 28,
    justifyContent: 'center',
    alignItems: 'center',
  },
  documentCountHint: {
    fontSize: 12,
    color: '#999999',
    textAlign: 'center',
    marginTop: 8,
  },
  // ── Bottom bar ──
  bottomBar: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderTopWidth: 1,
    borderTopColor: '#E5E5E5',
  },
  bidButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EC7E00',
    paddingVertical: 14,
    borderRadius: 8,
  },
  bidButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
    marginLeft: 8,
  },
  // ── Optional image viewer (design images) ──
  imageViewerContainer: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.95)',
    justifyContent: 'center',
  },
  imageViewerClose: {
    position: 'absolute',
    top: 30,
    right: 16,
    zIndex: 999,
    padding: 10,
    backgroundColor: 'rgba(0,0,0,0.45)',
    borderRadius: 24,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.12)',
    elevation: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.35,
    shadowRadius: 6,
  },
  imageViewerPage: {
    width: SCREEN_WIDTH,
    justifyContent: 'center',
    alignItems: 'center',
  },
  fullScreenImage: {
    width: SCREEN_WIDTH,
    height: '100%',
  },
  fullScreenWatermark: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: SCREEN_WIDTH,
    height: '100%',
    opacity: 0.28,
    zIndex: 5,
    pointerEvents: 'none',
  },
  imageCounter: {
    position: 'absolute',
    bottom: 40,
    alignSelf: 'center',
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
  },
  imageCounterText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  imageViewerDots: {
    position: 'absolute',
    bottom: 40,
    alignSelf: 'center',
    flexDirection: 'row',
    gap: 6,
    zIndex: 10,
  },
  imageViewerDot: {
    width: 7,
    height: 7,
    borderRadius: 4,
    backgroundColor: 'rgba(255,255,255,0.3)',
  },
  imageViewerDotActive: {
    backgroundColor: '#EC7E00',
    width: 20,
    borderRadius: 4,
  },
  // ── Important Document Viewer (overlay modal) ──
  docViewerContainer: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.97)',
    justifyContent: 'center',
  },
  docViewerClose: {
    position: 'absolute',
    top: 16,
    right: 16,
    zIndex: 999,
  },
  docViewerCloseCircle: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(255,255,255,0.15)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.2)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  docViewerHeader: {
    position: 'absolute',
    top: 24,
    left: 50,
    right: 70,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 10,
  },
  docViewerHeaderText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  docViewerNotice: {
    position: 'absolute',
    top: 52,
    left: 0,
    right: 0,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 10,
    gap: 4,
  },
  docViewerNoticeText: {
    color: 'rgba(255,255,255,0.6)',
    fontSize: 12,
  },
  docViewerPage: {
    flex: 1,
    marginTop: 76,
    marginBottom: 70,
    justifyContent: 'center',
    alignItems: 'center',
  },
  docViewerImage: {
    width: SCREEN_WIDTH - 32,
    height: '100%',
    maxHeight: SCREEN_HEIGHT * 0.72,
  },
  docViewerWatermark: {
    position: 'absolute',
    top: 0,
    left: 16,
    right: 16,
    bottom: 0,
    width: SCREEN_WIDTH - 32,
    opacity: 0.2,
    zIndex: 5,
  },
  docNavBar: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 14,
    paddingBottom: 28,
    backgroundColor: 'rgba(0,0,0,0.55)',
    zIndex: 10,
  },
  docNavBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.15)',
    gap: 4,
  },
  docNavBtnDisabled: {
    backgroundColor: 'rgba(255,255,255,0.06)',
  },
  docNavBtnText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  docNavBtnTextDisabled: {
    color: 'rgba(255,255,255,0.25)',
  },
  docNavCounter: {
    backgroundColor: 'rgba(0,0,0,0.5)',
    paddingHorizontal: 16,
    paddingVertical: 6,
    borderRadius: 16,
  },
  docNavCounterText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '700',
  },
  docViewerFileCard: {
    width: SCREEN_WIDTH - 44,
    minHeight: SCREEN_HEIGHT * 0.46,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.12)',
    backgroundColor: 'rgba(255,255,255,0.08)',
    paddingHorizontal: 22,
    paddingVertical: 26,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  docViewerFileType: {
    marginTop: 10,
    color: '#FFD29C',
    fontSize: 11,
    fontWeight: '700',
    letterSpacing: 0.7,
  },
  docViewerFileName: {
    marginTop: 8,
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
    textAlign: 'center',
  },
  docViewerFileHint: {
    marginTop: 6,
    color: 'rgba(255,255,255,0.72)',
    fontSize: 12,
    textAlign: 'center',
  },
  docViewerWebWrap: {
    width: SCREEN_WIDTH - 16,
    flex: 1,
    borderRadius: 10,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.18)',
    backgroundColor: '#FFFFFF',
  },
  docViewerWebView: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  docViewerWebLoading: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
  },
  docViewerWebLoadingText: {
    marginTop: 8,
    color: '#6B7280',
    fontSize: 12,
    fontWeight: '600',
  },
  docViewerWebWatermark: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    alignItems: 'center',
    justifyContent: 'center',
  },
  docViewerTextWatermark: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    alignItems: 'center',
    justifyContent: 'center',
  },
  docViewerTextWatermarkLine: {
    color: 'rgba(255,255,255,0.12)',
    fontSize: 20,
    fontWeight: '700',
    letterSpacing: 1.2,
    transform: [{ rotate: '-22deg' }],
    marginVertical: 12,
  },
  menuOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.15)',
  },
  menuCard: {
    position: 'absolute',
    right: 12,
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    minWidth: 170,
    paddingVertical: 6,
    shadowColor: '#000',
    shadowOpacity: 0.18,
    shadowRadius: 10,
    shadowOffset: { width: 0, height: 6 },
    elevation: 8,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  menuItemText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  menuLoadingRow: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderTopWidth: 1,
    borderTopColor: '#F3F4F6',
    alignItems: 'flex-start',
  },
});
