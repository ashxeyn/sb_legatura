// @ts-nocheck
import React, { useState } from 'react';
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
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import ImageFallback from '../../components/ImageFallbackFixed';
import { api_config } from '../../config/api';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

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

export default function ProjectPostDetail({ project, onClose, onPlaceBid, userRole = 'contractor', canBid = true }: ProjectPostDetailProps) {
  const insets = useSafeAreaInsets();
  const [imageViewerVisible, setImageViewerVisible] = useState(false);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);

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

  // Process files for display
  const processFiles = () => {
    if (!project.files || project.files.length === 0) return [];
    
    return project.files.map((file: any) => {
      const raw = typeof file === 'string' ? file : (file.file_path || file);
      const fileType = typeof file === 'object' ? file.file_type : '';
      
      const isImage = (path: string) => {
        if (!path) return false;
        const imagePath = path.toLowerCase();
        return imagePath.match(/\.(jpg|jpeg|png|gif|webp|bmp)(\?|$)/i) !== null ||
               (imagePath.startsWith('http') && !imagePath.includes('.pdf'));
      };

      const url = raw.startsWith('http') 
        ? raw 
        : `${api_config.base_url}/storage/${raw}`;

      const isProtected = fileType && ['building permit', 'title'].includes(fileType.toLowerCase());
      const isDesign = fileType && ['blueprint', 'desired design', 'others'].includes(fileType.toLowerCase());

      return {
        raw,
        url,
        isImage: isImage(raw),
        fileType,
        isProtected,
        isDesign,
      };
    });
  };

  const allFiles = processFiles().filter(f => f.isImage);
  const designImages = allFiles.filter(f => f.isDesign);
  const requiredDocuments = allFiles.filter(f => f.isProtected);
  const [currentGallery, setCurrentGallery] = useState<any[]>([]);

  const openImageViewer = (index: number, gallery: 'design' | 'docs' = 'design') => {
    const galleryItems = gallery === 'design' ? designImages : requiredDocuments;
    setCurrentGallery(galleryItems);
    setSelectedImageIndex(index);
    setImageViewerVisible(true);
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

        {/* Required Documents (Building Permit & Title) */}
        {requiredDocuments.length > 0 && (
          <View style={styles.documentsSection}>
            <Text style={styles.sectionTitle}>Required Documents</Text>
            <View style={styles.documentsGrid}>
              {requiredDocuments.map((doc, index) => (
                  <TouchableOpacity key={index} style={styles.documentCard} onPress={() => openImageViewer(index, 'docs')} activeOpacity={0.9}>
                    <View style={styles.documentImageContainer}>
                        <Image source={{ uri: doc.url }} style={styles.documentImage} resizeMode="cover" />
                        <View style={styles.thumbnailWatermarkWrapper} pointerEvents="none">
                          <Image source={watermarkImage} style={styles.thumbnailWatermark} resizeMode="contain" />
                        </View>
                        <View style={styles.documentLabelOverlay}>
                          <Text style={styles.documentLabelText}>
                            {doc.fileType === 'building permit' ? 'Building Permit' : 'Land Title'}
                          </Text>
                        </View>
                      </View>
                  </TouchableOpacity>
                ))}
            </View>
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

      {/* Image Viewer Modal */}
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
                  {file.isProtected && (
                    <Image source={watermarkImage} style={[styles.fullScreenWatermark]} resizeMode="cover" />
                  )}
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
    width: '140%',
    height: '140%',
    opacity: 0.52,
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
  documentsSection: {
    paddingHorizontal: 16,
    paddingVertical: 16,
    borderTopWidth: 8,
    borderTopColor: '#F0F0F0',
  },
  documentsGrid: {
    flexDirection: 'row',
    gap: 12,
  },
  documentCard: {
    flex: 1,
    backgroundColor: '#F8F9FA',
    borderRadius: 8,
    overflow: 'hidden',
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
    backgroundColor: 'rgba(0,0,0,0.55)',
    paddingVertical: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  documentLabelText: {
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '600',
  },
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
});
