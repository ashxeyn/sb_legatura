// @ts-nocheck
import React, { useState, useRef, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Image,
  Dimensions,
  StatusBar,
  Modal,
  FlatList,
  NativeScrollEvent,
  NativeSyntheticEvent,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import ImageFallback from '../../components/ImageFallbackFixed';
import { api_config } from '../../config/api';

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
  const normalized = path.replace(/[^a-z0-9]+/g, ' ');
  const tokens = normalized.split(/\s+/).filter(Boolean);
  const has = (w: string) => tokens.includes(w);
  if (has('building') && has('permit')) return true;
  if (has('title') && has('land')) return true;
  if (/(building_permit|building-permit|title_of_land|title-of-land)/.test(path)) return true;
  return false;
};

export default function ProjectPostDetail({ project, onClose, onPlaceBid, userRole = 'contractor', canBid = true }: ProjectPostDetailProps) {
  const insets = useSafeAreaInsets();
  const [imageViewerVisible, setImageViewerVisible] = useState(false);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [docViewerVisible, setDocViewerVisible] = useState(false);
  const [docViewerIndex, setDocViewerIndex] = useState(0);
  const docFlatListRef = useRef<FlatList>(null);

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

  // Build owner profile image URL
  const ownerProfileUrl = project.owner_profile_pic
    ? `${api_config.base_url}/storage/${project.owner_profile_pic}`
    : null;

  const daysRemaining = getDaysRemaining(project.bidding_deadline);

  // Check if file is an image by extension OR by known project file_type
  const isImageFile = (path: string, fileType?: string) => {
    if (!path) return false;
    // Files from project_files table are always images (form only accepts images)
    if (fileType && ['building permit', 'title', 'blueprint', 'desired design', 'others'].includes(fileType.toLowerCase())) {
      return true;
    }
    const imagePath = path.toLowerCase();
    return imagePath.match(/\.(jpg|jpeg|png|gif|webp|bmp)(\?|$)/i) !== null ||
           (imagePath.startsWith('http') && !imagePath.includes('.pdf'));
  };

  // Process files for display — classify into optional (design) and important (protected)
  const processFiles = () => {
    if (!project.files || project.files.length === 0) return [];
    
    console.log('[ProjectPostDetail] Raw project.files:', JSON.stringify(project.files).substring(0, 500));
    
    return project.files.map((file: any) => {
      const raw = typeof file === 'string' ? file : (file.file_path || '');
      const fileType = typeof file === 'object' ? (file.file_type || '') : '';

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

  const allFiles = processFiles().filter(f => f.isImage);
  console.log('[ProjectPostDetail] allFiles:', allFiles.length, '| design:', allFiles.filter(f => f.isDesign).length, '| protected:', allFiles.filter(f => f.isProtected).length);
  // Optional documents — shown in the main Facebook-style collage
  const designImages = allFiles.filter(f => f.isDesign);
  // Important/protected documents — shown ONLY in the expanded section with watermark
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

  /** Handle scroll in document viewer to track current page */
  const onDocViewerScroll = useCallback((e: NativeSyntheticEvent<NativeScrollEvent>) => {
    const offsetX = e.nativeEvent.contentOffset.x;
    const page = Math.round(offsetX / SCREEN_WIDTH);
    setDocViewerIndex(page);
  }, []);

  /** Get friendly label for a document based on fileType */
  const getDocumentLabel = (fileType: string): string => {
    const t = (fileType || '').toLowerCase();
    if (t.includes('building') && t.includes('permit')) return 'Building Permit';
    if (t.includes('title')) return 'Land Title';
    if (t.includes('permit')) return 'Building Permit';
    return 'Important Document';
  };

  // Collage sizing (Facebook-like rules)
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
        <View style={{ width: 40 }} />
      </View>

      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {/* Post Header - Owner Info */}
        <View style={styles.postHeader}>
          <View style={styles.ownerInfo}>
            <ImageFallback
              uri={ownerProfileUrl}
              defaultImage={defaultOwnerAvatar}
              style={styles.ownerAvatar}
              resizeMode="cover"
            />
            <View>
              <Text style={styles.ownerName}>{project.owner_name || 'Property Owner'}</Text>
              <Text style={styles.postDate}>{formatDate(project.created_at)}</Text>
            </View>
          </View>
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
              <Text style={styles.typeText}>{project.type_name}</Text>
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

        {/* Design Images (Blueprint, Desired Design, Others) - Facebook-style collage */}
        {designImages.length > 0 && (
          <View style={[styles.imagesSection, { paddingHorizontal: H_PADDING }]}> 
            {designImages.length === 1 && (
              <TouchableOpacity onPress={() => openImageViewer(0, 'design')} activeOpacity={0.9}>
                <Image source={{ uri: designImages[0].url }} style={{ width: usableWidth, height: singleHeight, borderRadius: 8 }} resizeMode="cover" />
              </TouchableOpacity>
            )}

            {designImages.length === 2 && (
              <View style={{ flexDirection: 'row' }}>
                {designImages.map((file, index) => (
                  <TouchableOpacity
                    key={index}
                    onPress={() => openImageViewer(index, 'design')}
                    activeOpacity={0.9}
                    style={{ width: halfSize, height: halfSize, marginRight: index === 0 ? GAP : 0, borderRadius: 8, overflow: 'hidden' }}
                  >
                    <Image source={{ uri: file.url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
                  </TouchableOpacity>
                ))}
              </View>
            )}

            {designImages.length === 3 && (
              <View style={{ flexDirection: 'row' }}>
                <TouchableOpacity onPress={() => openImageViewer(0, 'design')} activeOpacity={0.9} style={{ width: largeWidth, height: largeWidth, marginRight: GAP, borderRadius: 8, overflow: 'hidden' }}>
                  <Image source={{ uri: designImages[0].url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
                </TouchableOpacity>
                <View style={{ width: smallColumnWidth, height: largeWidth }}>
                  {designImages.slice(1).map((file, idx) => (
                    <TouchableOpacity
                      key={idx + 1}
                      onPress={() => openImageViewer(idx + 1, 'design')}
                      activeOpacity={0.9}
                      style={{ width: '100%', height: (largeWidth - GAP) / 2, marginBottom: idx === 0 ? GAP : 0, borderRadius: 8, overflow: 'hidden' }}
                    >
                      <Image source={{ uri: file.url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
                    </TouchableOpacity>
                  ))}
                </View>
              </View>
            )}

            {designImages.length >= 4 && (
              <View style={{ flexDirection: 'row', flexWrap: 'wrap' }}>
                {designImages.slice(0, 4).map((file, index) => (
                  <TouchableOpacity
                    key={index}
                    onPress={() => openImageViewer(index)}
                    activeOpacity={0.9}
                    style={{
                      width: halfSize,
                      height: halfSize,
                      marginRight: index % 2 === 0 ? GAP : 0,
                      marginTop: index >= 2 ? GAP : 0,
                      borderRadius: 8,
                      overflow: 'hidden'
                    }}
                  >
                    <Image source={{ uri: file.url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
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
              These documents are view-only and protected. Tap to view.
            </Text>
            <View style={styles.documentsGrid}>
              {requiredDocuments.slice(0, 22).map((doc, index) => (
                <TouchableOpacity
                  key={index}
                  style={styles.documentCard}
                  onPress={() => openImageViewer(index, 'docs')}
                  activeOpacity={0.9}
                >
                  <View style={styles.documentImageContainer}>
                    <Image source={{ uri: doc.url }} style={styles.documentImage} resizeMode="cover" />
                    {/* Watermark always rendered on top — scales responsively */}
                    <View style={styles.thumbnailWatermarkWrapper} pointerEvents="none">
                      <Image source={watermarkImage} style={styles.thumbnailWatermark} resizeMode="cover" />
                    </View>
                    {/* Label overlay */}
                    <View style={styles.documentLabelOverlay}>
                      <MaterialIcons name="lock-outline" size={14} color="#FFFFFF" style={{ marginRight: 4 }} />
                      <Text style={styles.documentLabelText}>
                        {getDocumentLabel(doc.fileType)}
                      </Text>
                    </View>
                    {/* View icon indicator */}
                    <View style={styles.documentViewIcon}>
                      <MaterialIcons name="visibility" size={20} color="#FFFFFF" />
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
          >
            {(currentGallery.length > 0 ? currentGallery : designImages).map((file, index) => (
              <View key={index} style={styles.imageViewerPage}>
                <View style={{ width: SCREEN_WIDTH, alignItems: 'center', justifyContent: 'center' }}>
                  <Image source={{ uri: file.url }} style={styles.fullScreenImage} resizeMode="contain" />
                </View>
              </View>
            ))}
          </ScrollView>
          <View style={styles.imageCounter}>
            <Text style={styles.imageCounterText}>
              {selectedImageIndex + 1} / {(currentGallery.length > 0 ? currentGallery.length : designImages.length)}
            </Text>
          </View>
        </View>
      </Modal>

      {/* ============================================================ */}
      {/* Document Viewer Modal — overlay-style for important documents */}
      {/* Supports up to 22 documents, paginated, view-only, watermark */}
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
            <Text style={styles.docViewerNoticeText}>View only — downloading is disabled</Text>
          </View>

          {/* Paginated document viewer — FlatList for performance with many images */}
          <FlatList
            ref={docFlatListRef}
            data={requiredDocuments.slice(0, 22)}
            horizontal
            pagingEnabled
            showsHorizontalScrollIndicator={false}
            initialScrollIndex={docViewerIndex}
            getItemLayout={(_, index) => ({
              length: SCREEN_WIDTH,
              offset: SCREEN_WIDTH * index,
              index,
            })}
            onMomentumScrollEnd={onDocViewerScroll}
            keyExtractor={(_, i) => `doc-${i}`}
            renderItem={({ item: doc }) => (
              <View style={styles.docViewerPage}>
                {/* Document image */}
                <Image
                  source={{ uri: doc.url }}
                  style={styles.docViewerImage}
                  resizeMode="contain"
                />
                {/* Watermark — always fixed on top, scales responsively */}
                <Image
                  source={watermarkImage}
                  style={styles.docViewerWatermark}
                  resizeMode="cover"
                  pointerEvents="none"
                />
              </View>
            )}
          />

          {/* Page counter */}
          <View style={styles.docViewerCounter}>
            <Text style={styles.docViewerCounterText}>
              {Math.min(docViewerIndex + 1, requiredDocuments.length)} / {Math.min(requiredDocuments.length, 22)}
            </Text>
          </View>

          {/* Dot indicators for quick navigation (show max 12 dots, then compress) */}
          {requiredDocuments.length > 1 && requiredDocuments.length <= 12 && (
            <View style={styles.docViewerDots}>
              {requiredDocuments.slice(0, 12).map((_, i) => (
                <View
                  key={i}
                  style={[
                    styles.docViewerDot,
                    i === docViewerIndex && styles.docViewerDotActive,
                  ]}
                />
              ))}
            </View>
          )}
        </View>
      </Modal>
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
  documentCard: {
    width: '48%',
    backgroundColor: '#F8F9FA',
    borderRadius: 8,
    overflow: 'hidden',
    marginBottom: 4,
  },
  documentImageContainer: {
    position: 'relative',
    aspectRatio: 1,
  },
  documentImage: {
    width: '100%',
    height: '100%',
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
    left: 0,
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
    width: SCREEN_WIDTH,
    height: SCREEN_HEIGHT,
    justifyContent: 'center',
    alignItems: 'center',
  },
  docViewerImage: {
    width: SCREEN_WIDTH - 32,
    height: SCREEN_HEIGHT * 0.7,
  },
  docViewerWatermark: {
    position: 'absolute',
    top: '15%',
    left: 16,
    right: 16,
    bottom: '15%',
    width: SCREEN_WIDTH - 32,
    height: SCREEN_HEIGHT * 0.7,
    opacity: 0.2,
    zIndex: 5,
  },
  docViewerCounter: {
    position: 'absolute',
    bottom: 50,
    alignSelf: 'center',
    backgroundColor: 'rgba(0, 0, 0, 0.65)',
    paddingHorizontal: 20,
    paddingVertical: 8,
    borderRadius: 20,
    zIndex: 10,
  },
  docViewerCounterText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  docViewerDots: {
    position: 'absolute',
    bottom: 30,
    alignSelf: 'center',
    flexDirection: 'row',
    gap: 6,
    zIndex: 10,
  },
  docViewerDot: {
    width: 7,
    height: 7,
    borderRadius: 4,
    backgroundColor: 'rgba(255,255,255,0.3)',
  },
  docViewerDotActive: {
    backgroundColor: '#EC7E00',
    width: 20,
    borderRadius: 4,
  },
});
