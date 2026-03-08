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
import ImageFallback from '../../components/ImageFallback';
import ReportPostModal from '../../components/reportPostModal';
import { api_config } from '../../config/api';
import { highlightService } from '../../services/highlightService';
import { post_service } from '../../services/post_service';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

const defaultContractorAvatar = require('../../../assets/images/pictures/contractor_default.png');
const defaultOwnerAvatar = require('../../../assets/images/pictures/property_owner_default.png');

// Helper to build full storage URL
const getStorageUrl = (filePath?: string) => {
  if (!filePath) return undefined;
  const p = String(filePath).trim();
  if (p.startsWith('http://') || p.startsWith('https://')) return p;
  if (p.includes('/storage/')) {
    return p.startsWith('/') ? `${api_config.base_url}${p}` : `${api_config.base_url}/${p}`;
  }
  if (p.includes('/')) return `${api_config.base_url}/storage/${p}`;
  return `${api_config.base_url}/storage/profiles/${p}`;
};

export interface ShowcasePost {
  post_id: number;
  user_id: number;
  title?: string;
  content?: string;
  location?: string;
  status?: string;
  is_highlighted?: number;
  created_at: string;
  display_name?: string;
  username?: string;
  company_name?: string;
  user_type?: string;
  avatar?: string;
  company_logo?: string;
  profile_pic?: string;
  linked_project_id?: number;
  linked_project_title?: string;
  linked_milestone_name?: string;
  images?: Array<{
    image_id?: number;
    file_path: string;
    original_name?: string;
    sort_order?: number;
  }>;
}

interface ShowcasePostDetailProps {
  post: ShowcasePost;
  onClose: () => void;
  onViewProfile?: () => void;
  isOwner?: boolean;
  initialImageIndex?: number;
}

export default function ShowcasePostDetail({
  post,
  onClose,
  onViewProfile,
  isOwner = false,
  initialImageIndex = 0,
}: ShowcasePostDetailProps) {
  const insets = useSafeAreaInsets();
  const [imageViewerVisible, setImageViewerVisible] = useState(initialImageIndex > 0);
  const [selectedImageIndex, setSelectedImageIndex] = useState(initialImageIndex);
  const [menuVisible, setMenuVisible] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);
  const [reportModalVisible, setReportModalVisible] = useState(false);
  const [isHighlighted, setIsHighlighted] = useState(!!post.is_highlighted);

  useEffect(() => {
    setIsHighlighted(!!post.is_highlighted);
  }, [post.post_id, post.is_highlighted]);

  // Build image URLs
  const postImages = (post.images || []).map((img) => ({
    url: img.file_path?.startsWith('http')
      ? img.file_path
      : `${api_config.base_url}/storage/${img.file_path}`,
    originalName: img.original_name || '',
  }));

  // Author avatar
  const avatarUrl = post.avatar
    ? getStorageUrl(post.avatar)
    : post.company_logo
      ? getStorageUrl(post.company_logo)
      : post.profile_pic
        ? getStorageUrl(post.profile_pic)
        : null;

  const displayName =
    post.display_name || post.company_name || post.username || 'User';

  const linkedName =
    post.linked_milestone_name || post.linked_project_title || null;

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric',
    });
  };

  const openImageViewer = (index: number) => {
    setSelectedImageIndex(index);
    setImageViewerVisible(true);
  };

  const submitReport = useCallback(async (reason: string, details?: string, attachments?: import('../../../services/post_service').ReportAttachment[]) => {
    const res = await post_service.report_post('showcase', post.post_id, reason, details, attachments);
    return {
      success: !!res.success,
      message: res.message || (res.success ? 'Report submitted.' : 'Unable to submit report right now.'),
    };
  }, [post.post_id]);

  const openReportReasons = useCallback(() => {
    setMenuVisible(false);
    setReportModalVisible(true);
  }, [submitReport]);

  const toggleHighlight = useCallback(async () => {
    if (!isOwner) return;

    setMenuVisible(false);
    setActionLoading(true);
    try {
      const res = isHighlighted
        ? await highlightService.unhighlightPost(post.post_id)
        : await highlightService.highlightPost(post.post_id);

      if (res.success) {
        setIsHighlighted(!isHighlighted);
        Alert.alert('Success', isHighlighted ? 'Post unhighlighted.' : 'Post highlighted.');
      } else {
        Alert.alert('Highlight', res.message || 'Unable to update highlight.');
      }
    } finally {
      setActionLoading(false);
    }
  }, [isOwner, isHighlighted, post.post_id]);

  // Image gallery sizing
  const H_PADDING = 16;
  const GAP = 2;
  const usableWidth = SCREEN_WIDTH - H_PADDING * 2;
  const halfSize = Math.floor((usableWidth - GAP) / 2);
  const largeWidth = Math.floor(usableWidth * 0.66);
  const smallColumnWidth = usableWidth - largeWidth - GAP;
  const singleHeight = Math.floor(usableWidth * 0.56);

  const renderImageGallery = () => {
    if (postImages.length === 0) return null;

    if (postImages.length === 1) {
      return (
        <View style={[styles.imagesSection, { paddingHorizontal: H_PADDING }]}>
          <TouchableOpacity onPress={() => openImageViewer(0)} activeOpacity={0.9}>
            <Image
              source={{ uri: postImages[0].url }}
              style={{ width: usableWidth, height: singleHeight, borderRadius: 8 }}
              contentFit="cover"
              transition={200}
              cachePolicy="memory-disk"
            />
          </TouchableOpacity>
        </View>
      );
    }

    if (postImages.length === 2) {
      return (
        <View style={[styles.imagesSection, { paddingHorizontal: H_PADDING }]}>
          <View style={{ flexDirection: 'row' }}>
            {postImages.map((img, index) => (
              <TouchableOpacity
                key={index}
                onPress={() => openImageViewer(index)}
                activeOpacity={0.9}
              >
                <Image
                  source={{ uri: img.url }}
                  style={{
                    width: halfSize,
                    height: halfSize,
                    borderRadius: 8,
                    marginLeft: index === 1 ? GAP : 0,
                  }}
                  contentFit="cover"
                  transition={200}
                  cachePolicy="memory-disk"
                />
              </TouchableOpacity>
            ))}
          </View>
        </View>
      );
    }

    if (postImages.length === 3) {
      return (
        <View style={[styles.imagesSection, { paddingHorizontal: H_PADDING }]}>
          <View style={{ flexDirection: 'row' }}>
            <TouchableOpacity onPress={() => openImageViewer(0)} activeOpacity={0.9}>
              <Image
                source={{ uri: postImages[0].url }}
                style={{
                  width: largeWidth,
                  height: halfSize * 2 + GAP,
                  borderRadius: 8,
                  marginRight: GAP,
                }}
                contentFit="cover"
                transition={200}
                cachePolicy="memory-disk"
              />
            </TouchableOpacity>
            <View>
              {postImages.slice(1).map((img, idx) => (
                <TouchableOpacity
                  key={idx + 1}
                  onPress={() => openImageViewer(idx + 1)}
                  activeOpacity={0.9}
                >
                  <Image
                    source={{ uri: img.url }}
                    style={{
                      width: smallColumnWidth,
                      height: halfSize,
                      borderRadius: 8,
                      marginTop: idx === 1 ? GAP : 0,
                    }}
                    contentFit="cover"
                    transition={200}
                    cachePolicy="memory-disk"
                  />
                </TouchableOpacity>
              ))}
            </View>
          </View>
        </View>
      );
    }

    // 4+ images: 2x2 grid with +N overlay
    return (
      <View style={[styles.imagesSection, { paddingHorizontal: H_PADDING }]}>
        <View style={{ flexDirection: 'row', flexWrap: 'wrap', width: usableWidth }}>
          {postImages.slice(0, 4).map((img, index) => (
            <TouchableOpacity
              key={index}
              onPress={() => openImageViewer(index)}
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
                source={{ uri: img.url }}
                style={{ width: halfSize, height: halfSize }}
                contentFit="cover"
                transition={200}
                cachePolicy="memory-disk"
              />
              {index === 3 && postImages.length > 4 && (
                <View style={styles.moreOverlay}>
                  <Text style={styles.moreText}>+{postImages.length - 4}</Text>
                </View>
              )}
            </TouchableOpacity>
          ))}
        </View>
      </View>
    );
  };

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color="#1A1A1A" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Showcase Post</Text>
        <View style={styles.cardMenuWrap}>
          <TouchableOpacity style={styles.menuButton} onPress={() => setMenuVisible(v => !v)}>
            <MaterialIcons name="more-vert" size={24} color="#1A1A1A" />
          </TouchableOpacity>
          {menuVisible && (
            <View style={styles.cardMenuDropdown}>
              {isOwner && (
                <TouchableOpacity style={styles.cardMenuItem} onPress={toggleHighlight} disabled={actionLoading}>
                  <Text style={styles.cardMenuItemText}>{isHighlighted ? 'Unhighlight' : 'Highlight'}</Text>
                </TouchableOpacity>
              )}
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
        {/* Author Info */}
        <View style={styles.postHeader}>
          <TouchableOpacity
            style={styles.authorInfo}
            activeOpacity={onViewProfile ? 0.7 : 1}
            onPress={onViewProfile}
            disabled={!onViewProfile}
          >
            <ImageFallback
              uri={avatarUrl || undefined}
              defaultImage={
                post.user_type === 'contractor'
                  ? defaultContractorAvatar
                  : defaultOwnerAvatar
              }
              style={styles.authorAvatar}
              resizeMode="cover"
            />
            <View style={{ flex: 1 }}>
              <View
                style={{
                  flexDirection: 'row',
                  alignItems: 'center',
                  flexWrap: 'wrap',
                  gap: 4,
                }}
              >
                <Text style={styles.authorName}>{displayName}</Text>
              </View>
              <Text style={styles.postDate}>
                {formatDate(post.created_at)}
              </Text>
            </View>

            {/* Highlight badge — only visible to the post owner */}
            {isOwner && isHighlighted && (
              <View style={styles.highlightBadge}>
                <MaterialIcons name="push-pin" size={14} color="#EEA24B" />
                <Text style={styles.highlightBadgeText}>Highlighted</Text>
              </View>
            )}
          </TouchableOpacity>
        </View>

        {/* Title */}
        {post.title && (
          <View style={styles.titleSection}>
            <Text style={styles.postTitle}>{post.title}</Text>
          </View>
        )}

        {/* Linked project badge */}
        {linkedName && (
          <View style={styles.linkedSection}>
            <View style={styles.linkedBadge}>
              <MaterialIcons name="verified" size={16} color="#16a34a" />
              <Text style={styles.linkedBadgeText}>{linkedName}</Text>
            </View>
          </View>
        )}

        {/* Content / Description — full text, no truncation */}
        {post.content && (
          <View style={styles.contentSection}>
            <Text style={styles.contentText}>{post.content}</Text>
          </View>
        )}

        {/* Images Gallery */}
        {renderImageGallery()}

        {/* Metadata Section */}
        {(post.location || linkedName) && (
          <View style={styles.metadataSection}>
            {post.location && (
              <View style={styles.metaRow}>
                <MaterialIcons name="location-on" size={20} color="#EC7E00" />
                <View style={styles.metaContent}>
                  <Text style={styles.metaLabel}>Location</Text>
                  <Text style={styles.metaValue}>{post.location}</Text>
                </View>
              </View>
            )}

            {postImages.length > 0 && (
              <View style={styles.metaRow}>
                <MaterialIcons name="photo-library" size={20} color="#EC7E00" />
                <View style={styles.metaContent}>
                  <Text style={styles.metaLabel}>Photos</Text>
                  <Text style={styles.metaValue}>
                    {postImages.length} photo{postImages.length !== 1 ? 's' : ''}
                  </Text>
                </View>
              </View>
            )}
          </View>
        )}
      </ScrollView>

      {/* ─── Full-Screen Image Viewer Modal ─── */}
      <Modal visible={imageViewerVisible} transparent animationType="fade">
        <View style={styles.imageViewerContainer}>
          <TouchableOpacity
            style={styles.imageViewerClose}
            onPress={() => setImageViewerVisible(false)}
            accessibilityLabel="Close image viewer"
          >
            <Ionicons name="close" size={36} color="#FFFFFF" />
          </TouchableOpacity>

          <ScrollView
            horizontal
            pagingEnabled
            showsHorizontalScrollIndicator={false}
            contentOffset={{ x: selectedImageIndex * SCREEN_WIDTH, y: 0 }}
            onMomentumScrollEnd={(
              e: NativeSyntheticEvent<NativeScrollEvent>
            ) => {
              const idx = Math.round(
                e.nativeEvent.contentOffset.x / SCREEN_WIDTH
              );
              if (idx >= 0 && idx < postImages.length) {
                setSelectedImageIndex(idx);
              }
            }}
          >
            {postImages.map((img, index) => (
              <View key={index} style={styles.imageViewerPage}>
                <Image
                  source={{ uri: img.url }}
                  style={styles.fullScreenImage}
                  contentFit="contain"
                  transition={200}
                  cachePolicy="memory-disk"
                />
              </View>
            ))}
          </ScrollView>

          {/* Dot indicators / counter */}
          {postImages.length > 1 && postImages.length <= 12 ? (
            <View style={styles.imageViewerDots}>
              {postImages.map((_, i) => (
                <View
                  key={i}
                  style={[
                    styles.imageViewerDot,
                    i === selectedImageIndex && styles.imageViewerDotActive,
                  ]}
                />
              ))}
            </View>
          ) : postImages.length > 12 ? (
            <View style={styles.imageCounter}>
              <Text style={styles.imageCounterText}>
                {selectedImageIndex + 1} / {postImages.length}
              </Text>
            </View>
          ) : null}
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
  cardMenuItemText: {
    fontSize: 13,
    color: '#1F2937',
    fontWeight: '500',
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
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  authorInfo: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  authorAvatar: {
    width: 44,
    height: 44,
    borderRadius: 22,
    marginRight: 12,
    backgroundColor: '#E5E5E5',
  },
  authorName: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1A1A1A',
  },
  postDate: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 2,
  },
  highlightBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF8EE',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 4,
    marginLeft: 8,
  },
  highlightBadgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#EEA24B',
  },
  titleSection: {
    paddingHorizontal: 16,
    paddingTop: 16,
    paddingBottom: 4,
  },
  postTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: '#1A1A1A',
    lineHeight: 28,
  },
  linkedSection: {
    paddingHorizontal: 16,
    paddingTop: 8,
    paddingBottom: 4,
  },
  linkedBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    backgroundColor: '#ecfdf5',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 12,
    gap: 5,
  },
  linkedBadgeText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#16a34a',
  },
  contentSection: {
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 12,
  },
  contentText: {
    fontSize: 15,
    lineHeight: 23,
    color: '#333333',
  },
  imagesSection: {
    marginVertical: 8,
  },
  metadataSection: {
    paddingHorizontal: 16,
    paddingVertical: 16,
    borderTopWidth: 8,
    borderTopColor: '#F0F0F0',
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 16,
  },
  metaContent: {
    flex: 1,
    marginLeft: 12,
  },
  metaLabel: {
    fontSize: 13,
    color: '#666666',
    marginBottom: 2,
  },
  metaValue: {
    fontSize: 15,
    color: '#1A1A1A',
    fontWeight: '500',
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
  // ─── Image Viewer Modal ───
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
