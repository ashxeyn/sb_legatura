// @ts-nocheck
import React, { useState, useEffect, useCallback } from 'react';
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
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather, MaterialIcons, Ionicons } from '@expo/vector-icons';
import { api_config } from '../../config/api';
import ImageFallback from '../../components/ImageFallback';
import { storage_service } from '../../utils/storage';
import CreateShowcase from './createShowcase';
import ShowcasePostDetail from './showcasePostDetail';
import { highlightService } from '../../services/highlightService';
import { post_service } from '../../services/post_service';
import ReportPostModal from '../../components/reportPostModal';
import {
  profile_service,
  ProfileData,
  SocialPost,
  ReviewItem,
  ReviewStats,
  ContractorAbout,
} from '../../services/profile_service';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

// Default images
const defaultCoverPhoto = require('../../../assets/images/pictures/cp_default.jpg');
const defaultContractorAvatar = require('../../../assets/images/pictures/contractor_default.png');

// Design tokens
const BRAND   = '#EEA24B';
const BRAND_L = '#FFF8EE';
const BORDER  = '#E8EAED';
const BG      = '#f0f2f5';
const T1      = '#1a1a1a';
const T2      = '#6b7280';
const CARD_R  = 6;
const STATUS_LABELS: Record<string, { label: string; color: string; bg: string }> = {
  completed:      { label: 'Completed',      color: '#16a34a', bg: '#dcfce7' },
  in_progress:    { label: 'In Progress',    color: '#d97706', bg: '#fef3c7' },
  active:         { label: 'Active',         color: '#2563eb', bg: '#dbeafe' },
  open:           { label: 'Open',           color: '#2563eb', bg: '#dbeafe' },
  approved:       { label: 'Approved',       color: '#16a34a', bg: '#dcfce7' },
  closed:         { label: 'Closed',         color: '#6b7280', bg: '#f3f4f6' },
  pending:        { label: 'Pending',        color: '#6b7280', bg: '#f3f4f6' },
  rejected:       { label: 'Rejected',       color: '#dc2626', bg: '#fef2f2' },
  bidding_closed: { label: 'Bidding Closed', color: '#9333ea', bg: '#f3e8ff' },
};
const COLORS = {
  primary: BRAND, primaryLight: BRAND_L, primaryDark: '#C96A00',
  success: '#10B981', successLight: '#D1FAE5', warning: '#F59E0B', warningLight: '#FEF3C7',
  error: '#EF4444', info: '#3B82F6', infoLight: '#DBEAFE',
  background: BG, surface: '#FFFFFF', text: T1, textSecondary: T2,
  textMuted: '#94A3B8', border: BORDER, borderLight: '#f3f4f6', star: BRAND,
};

interface Contractor {
  contractor_id: number;
  company_name: string;
  company_description?: string;
  location?: string;
  rating?: number;
  reviews_count?: number;
  contractor_type?: string;
  logo_url?: string;
  cover_photo?: string;
  years_of_experience?: number;
  services_offered?: string;
  completed_projects?: number;
  user_id?: number;
  created_at?: string;
  company_email?: string;
  company_phone?: string;
  company_website?: string;
  company_social_media?: string;
}

interface CheckProfileProps {
  contractor: Contractor;
  onClose: () => void;
  onSendMessage?: () => void;
}

// Tab options
type TabType = 'portfolio' | 'reviews' | 'about';

/* ─── Helpers ───────────────────────────────────────────────────────── */

const resolveImageUrl = (path: string | null | undefined): string | undefined => {
  if (!path) return undefined;
  if (path.startsWith('http')) return path;
  return `${api_config.base_url}/storage/${path}`;
};

const formatDate = (dateString?: string | null): string => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};

const formatCurrency = (amount: number | null | undefined): string => {
  if (amount == null) return '—';
  return '₱' + Number(amount).toLocaleString('en-PH', { maximumFractionDigits: 0 });
};

export default function CheckProfile({ contractor, onClose, onSendMessage }: CheckProfileProps) {
  const insets = useSafeAreaInsets();
  const [activeTab, setActiveTab] = useState<TabType>('portfolio');

  // ── State ──
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [profile, setProfile] = useState<ProfileData | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [showCreateShowcase, setShowCreateShowcase] = useState(false);
  const [selectedShowcasePost, setSelectedShowcasePost] = useState<any>(null);
  const [isOwnProfile, setIsOwnProfile] = useState(false);
  const [highlightingPostId, setHighlightingPostId] = useState<number | null>(null);
  const [activePostMenuId, setActivePostMenuId] = useState<number | null>(null);
  const [reportModalVisible, setReportModalVisible] = useState(false);
  const [reportPostId, setReportPostId] = useState<number | null>(null);
  const [reportType, setReportType] = useState<'showcase' | 'review'>('showcase');
  const [reviewMenuOpenId, setReviewMenuOpenId] = useState<number | null>(null);
  const [initialShowcaseImageIndex, setInitialShowcaseImageIndex] = useState<number>(0);

  // ── Check if viewing own profile ──
  useEffect(() => {
    (async () => {
      const user = await storage_service.get_user_data();
      if (user && contractor.user_id && user.user_id === contractor.user_id) {
        setIsOwnProfile(true);
      }
    })();
  }, [contractor.user_id]);

  // ── Fetch profile ──
  const fetchProfile = useCallback(async (silent = false) => {
    if (!contractor.user_id) {
      setError('No user ID available for this contractor.');
      setLoading(false);
      return;
    }
    if (!silent) setLoading(true);
    setError(null);
    try {
      const res = await profile_service.get_profile(contractor.user_id, 'contractor');
      if (res.success && res.data) {
        setProfile(res.data);
      } else {
        setError(res.message || 'Could not load profile.');
      }
    } catch (e) {
      setError('An unexpected error occurred.');
      console.error('[CheckProfile] fetchProfile:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [contractor.user_id]);

  useEffect(() => { fetchProfile(); }, [fetchProfile]);

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchProfile(true);
  }, [fetchProfile]);

  // ── Derived values (prefer API data, fallback to prop) ──
  const header       = profile?.header;
  const displayName  = header?.display_name || contractor.company_name;
  const avgRating    = header?.avg_rating ?? contractor.rating ?? 0;
  const totalReviews = header?.total_reviews ?? contractor.reviews_count ?? 0;
  const completedProjects = header?.completed_projects ?? contractor.completed_projects ?? 0;
  const coverPhotoUrl = resolveImageUrl(header?.cover_photo) || resolveImageUrl(contractor.cover_photo);
  const logoUrl       = resolveImageUrl(header?.profile_pic) || resolveImageUrl(contractor.logo_url);
  const description   = profile?.about?.contractor?.bio
    || profile?.about?.contractor?.company_description
    || contractor.company_description;

  // Reviews
  const reviewsData  = profile?.reviews;
  const reviews      = reviewsData?.reviews ?? [];
  const reviewStats  = reviewsData?.stats ?? { avg_rating: avgRating, total_reviews: totalReviews, distribution: {} };

  // Posts (showcase only — no automatic project listing)
  const showcasePosts       = profile?.posts?.showcase_posts ?? [];
  const totalPortfolioItems = showcasePosts.length;

  // Handle highlight toggle
  const handleToggleHighlight = useCallback(async (postId: number, currentlyHighlighted: boolean) => {
    if (highlightingPostId) return;
    setHighlightingPostId(postId);
    try {
      const res = currentlyHighlighted
        ? await highlightService.unhighlightPost(postId)
        : await highlightService.highlightPost(postId);
      if (res.success) {
        // Optimistic update
        setProfile(prev => {
          if (!prev) return prev;
          const updatedPosts = (prev.posts?.showcase_posts ?? []).map((p: any) => {
            if (p.post_id === postId) {
              return { ...p, is_highlighted: currentlyHighlighted ? 0 : 1 };
            }
            return p;
          });
          return {
            ...prev,
            posts: { ...prev.posts, showcase_posts: updatedPosts },
          };
        });
      } else {
        Alert.alert('Highlight', res.message || 'Could not update highlight.');
      }
    } catch {
      Alert.alert('Error', 'Something went wrong.');
    } finally {
      setHighlightingPostId(null);
    }
  }, [highlightingPostId]);

  const submitPostReport = useCallback(async (reason: string, details?: string, attachments?: import('../../../services/post_service').ReportAttachment[]) => {
    if (!reportPostId) {
      return { success: false, message: 'No report target selected.' };
    }

    const res = reportType === 'review'
      ? await post_service.report_review(reportPostId, reason, details, attachments)
      : await post_service.report_post(reportType, reportPostId, reason, details, attachments);
    return {
      success: !!res.success,
      message: res.message || (res.success ? 'Report submitted.' : 'Unable to submit report right now.'),
    };
  }, [reportPostId, reportType]);

  const openReportReasons = useCallback((postId: number) => {
    setReportPostId(postId);
    setReportModalVisible(true);
  }, [submitPostReport]);

  const openReviewReport = useCallback((reviewId: number) => {
    setReviewMenuOpenId(null);
    setReportType('review');
    setReportPostId(reviewId);
    setReportModalVisible(true);
  }, []);

  const openShowcaseCardMenu = useCallback((postId: number) => {
    setActivePostMenuId(prev => (prev === postId ? null : postId));
  }, []);

  // ── Facebook-style image collage for portfolio cards ──
  const renderFbCollage = (images: string[], post: any) => {
    if (!images || images.length === 0) return null;
    const GAP = 5;
    const W = SCREEN_WIDTH - 26; // card marginHorizontal 8×2=16 + collage margin 5×2=10
    const half = Math.floor((W - GAP) / 2);
    const twoThird = Math.floor(W * 0.66);
    const oneThird = W - twoThird - GAP;
    const singleH = Math.floor(W * 0.56);
    const dualH = Math.floor(W * 0.42);
    const triH = Math.floor(W * 0.52);
    const gridCellH = Math.floor(W * 0.40);

    const openAt = (idx: number) => {
      setInitialShowcaseImageIndex(idx);
      setSelectedShowcasePost(post);
    };

    const img = (uri: string, style: any, idx: number, extra?: number) => (
      <TouchableOpacity key={idx} activeOpacity={0.9} onPress={() => openAt(idx)} style={style}>
        <ImageFallback uri={uri} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
        {extra != null && extra > 0 ? (
          <View style={styles.fbCollageOverlay}>
            <Text style={styles.fbCollageOverlayText}>+{extra}</Text>
          </View>
        ) : null}
      </TouchableOpacity>
    );

    if (images.length === 1) {
      return img(images[0], { width: W, height: singleH }, 0);
    }

    if (images.length === 2) {
      return (
        <View style={{ flexDirection: 'row' }}>
          {img(images[0], { width: half, height: dualH, marginRight: GAP }, 0)}
          {img(images[1], { width: half, height: dualH }, 1)}
        </View>
      );
    }

    if (images.length === 3) {
      const cellH = Math.floor((triH - GAP) / 2);
      return (
        <View style={{ flexDirection: 'row' }}>
          {img(images[0], { width: twoThird, height: triH, marginRight: GAP }, 0)}
          <View style={{ width: oneThird, height: triH }}>
            {img(images[1], { width: oneThird, height: cellH, marginBottom: GAP }, 1)}
            {img(images[2], { width: oneThird, height: cellH }, 2)}
          </View>
        </View>
      );
    }

    if (images.length === 4) {
      return (
        <View>
          <View style={{ flexDirection: 'row', marginBottom: GAP }}>
            {img(images[0], { width: half, height: gridCellH, marginRight: GAP }, 0)}
            {img(images[1], { width: half, height: gridCellH }, 1)}
          </View>
          <View style={{ flexDirection: 'row' }}>
            {img(images[2], { width: half, height: gridCellH, marginRight: GAP }, 2)}
            {img(images[3], { width: half, height: gridCellH }, 3)}
          </View>
        </View>
      );
    }

    const extra = images.length - 4;
    return (
      <View>
        <View style={{ flexDirection: 'row', marginBottom: GAP }}>
          {img(images[0], { width: half, height: gridCellH, marginRight: GAP }, 0)}
          {img(images[1], { width: half, height: gridCellH }, 1)}
        </View>
        <View style={{ flexDirection: 'row' }}>
          {img(images[2], { width: half, height: gridCellH, marginRight: GAP }, 2)}
          {img(images[3], { width: half, height: gridCellH }, 3, extra)}
        </View>
      </View>
    );
  };

  // About
  const contractorAbout = profile?.about?.contractor;

  // Tabs (3 tabs)
  const tabs: { key: TabType; label: string; count?: number }[] = [
    { key: 'portfolio', label: 'Portfolio', count: totalPortfolioItems || undefined },
    { key: 'reviews', label: 'Reviews', count: totalReviews || undefined },
    { key: 'about', label: 'About' },
  ];

  const renderPortfolioTab = () => {
    return (
      <View style={styles.tabContent}>
        {/* Add Showcase button (only on own profile) */}
        {isOwnProfile && (
          <TouchableOpacity
            style={styles.addShowcaseBtn}
            onPress={() => setShowCreateShowcase(true)}
            activeOpacity={0.8}
          >
            <Feather name="plus-circle" size={18} color={BRAND} />
            <Text style={styles.addShowcaseBtnText}>Add Showcase Post</Text>
          </TouchableOpacity>
        )}

        {/* Showcase Posts */}
        {showcasePosts.length > 0 ? (
          showcasePosts.map((post) => {
            const isHighlighted = !!post.is_highlighted;
            return (
              <TouchableOpacity
                key={`sp-${post.post_id}`}
                style={styles.socialPostCard}
                activeOpacity={0.85}
                onPress={() => {
                  setActivePostMenuId(null);
                  setSelectedShowcasePost(post);
                }}
              >
                {/* Header: contractor avatar + name + date + menu */}
                <View style={styles.postCardHeader}>
                  <View style={styles.postOwnerInfo}>
                    <ImageFallback
                      uri={logoUrl || undefined}
                      defaultImage={defaultContractorAvatar}
                      style={styles.postOwnerAvatar}
                      resizeMode="cover"
                    />
                    <View style={{ flex: 1 }}>
                      <View style={{ flexDirection: 'row', alignItems: 'center', flexWrap: 'wrap', gap: 4 }}>
                        <Text style={styles.postOwnerName} numberOfLines={1}>{displayName}</Text>
                        {post.linked_project_id && (
                          <>
                            <Text style={{ color: '#999', fontSize: 13 }}>—</Text>
                            <MaterialIcons name="label" size={14} color="#1565C0" />
                            <Text style={{ fontSize: 13, color: '#1565C0', fontWeight: '600' }} numberOfLines={1}>
                              {post.linked_project_title || 'Linked project'}
                            </Text>
                          </>
                        )}
                      </View>
                      <Text style={styles.postOwnerDate}>
                        Posted {new Date(post.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                      </Text>
                    </View>
                  </View>

                  {/* ⋮ menu */}
                  <View style={styles.postMenuWrap}>
                    <TouchableOpacity
                      onPress={(e) => {
                        e.stopPropagation?.();
                        openShowcaseCardMenu(post.post_id);
                      }}
                      activeOpacity={0.7}
                      disabled={highlightingPostId === post.post_id}
                      style={styles.postMenuButton}
                      hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
                    >
                      <MaterialIcons name="more-vert" size={20} color="#4B5563" />
                    </TouchableOpacity>

                    {activePostMenuId === post.post_id && (
                      <View style={styles.postMenuDropdown}>
                        {isOwnProfile && (
                          <TouchableOpacity
                            style={styles.postMenuItem}
                            onPress={(e) => {
                              e.stopPropagation?.();
                              setActivePostMenuId(null);
                              handleToggleHighlight(post.post_id, isHighlighted);
                            }}
                          >
                            <Text style={styles.postMenuItemText}>{isHighlighted ? 'Unhighlight' : 'Highlight'}</Text>
                          </TouchableOpacity>
                        )}
                        <TouchableOpacity
                          style={styles.postMenuItem}
                          onPress={(e) => {
                            e.stopPropagation?.();
                            setActivePostMenuId(null);
                            openReportReasons(post.post_id);
                          }}
                        >
                          <Text style={styles.postMenuDangerText}>Report</Text>
                        </TouchableOpacity>
                      </View>
                    )}
                  </View>
                </View>

                {/* Title */}
                {post.title ? <Text style={styles.postTitleText}>{post.title}</Text> : null}

                {/* Content */}
                <Text style={styles.postContentText} numberOfLines={3}>{post.content}</Text>

                {/* Images collage */}
                {post.images && post.images.length > 0 ? (
                  <View style={styles.fbCollageWrap}>
                    {renderFbCollage(post.images.map((i: any) => resolveImageUrl(i.file_path)).filter(Boolean), post)}
                  </View>
                ) : null}

                {/* Location or spacer */}
                {post.location ? (
                  <View style={[styles.postDetailRow, { paddingHorizontal: 16, marginTop: 8, marginBottom: 14 }]}>
                    <MaterialIcons name="location-on" size={16} color="#666666" />
                    <Text style={styles.postDetailText}>{post.location}</Text>
                  </View>
                ) : (
                  <View style={{ height: 14 }} />
                )}
              </TouchableOpacity>
            );
          })
        ) : !loading ? (
          <View style={styles.emptyState}>
            <MaterialIcons name="photo-library" size={48} color="#d1d5db" />
            <Text style={styles.emptyTitle}>No showcase posts yet</Text>
            <Text style={styles.emptySubtext}>
              {isOwnProfile
                ? 'Tap \"Add Showcase Post\" to highlight your completed work.'
                : 'This contractor hasn\'t showcased any work yet.'}
            </Text>
          </View>
        ) : null}

        {/* Services Offered */}
      </View>
    );
  };

  const renderReviewsTab = () => {
    const avg = reviewStats.avg_rating || avgRating;
    const total = reviewStats.total_reviews || totalReviews;
    const dist = reviewStats.distribution || {};
    const REVIEW_THRESHOLD = 5;
    const hasRating = total >= REVIEW_THRESHOLD;

    return (
      <View style={styles.tabContent}>
        {/* Summary */}
        <View style={styles.reviewsSummary}>
          {hasRating ? (
            <>
              <View style={styles.reviewsSummaryLeft}>
                <Text style={styles.reviewsAvgVal}>{avg ? avg.toFixed(1) : '0.0'}</Text>
                <Text style={styles.reviewsAvgSub}>out of 5</Text>
              </View>
              <View style={styles.reviewsSummaryRight}>
                <View style={styles.starsRow}>
                  {[1,2,3,4,5].map((i) => (
                    <MaterialIcons key={i} name={i<=Math.round(avg)?'star':'star-border'} size={18} color={i<=Math.round(avg)?BRAND:'#d1d5db'} />
                  ))}
                </View>
                <Text style={styles.reviewsCountText}>{total} review{total!==1?'s':''}</Text>

                {/* Distribution bars */}
                {Object.keys(dist).length > 0 && (
                  <View style={styles.distributionContainer}>
                    {[5, 4, 3, 2, 1].map((star) => {
                      const count = dist[String(star)] || 0;
                      const pct = total > 0 ? (count / total) * 100 : 0;
                      return (
                        <View key={star} style={styles.distRow}>
                          <Text style={styles.distLabel}>{star}</Text>
                          <MaterialIcons name="star" size={11} color={BRAND} />
                          <View style={styles.distBarBg}>
                            <View style={[styles.distBarFill, { width: `${pct}%` }]} />
                          </View>
                          <Text style={styles.distCount}>{count}</Text>
                        </View>
                      );
                    })}
                  </View>
                )}
              </View>
            </>
          ) : (
            <View style={{ alignItems: 'center', flex: 1, paddingVertical: 8 }}>
              <MaterialIcons name="star-outline" size={36} color="#d1d5db" />
              <Text style={[styles.reviewsCountText, { marginTop: 8, textAlign: 'center' }]}>
                Rating visible after {REVIEW_THRESHOLD} reviews
              </Text>
              <Text style={[styles.reviewsCountText, { marginTop: 4, textAlign: 'center', color: '#9ca3af' }]}>
                {total} of {REVIEW_THRESHOLD} received
              </Text>
            </View>
          )}
        </View>
        <View style={styles.reviewsDivider} />
        {/* Cards */}
        {reviews.length > 0 ? (
          reviews.map((rev) => (
            <View key={rev.review_id} style={styles.reviewCard}>
              {/* Header: avatar + name + stars on left / date + menu on right */}
              <View style={styles.postCardHeader}>
                <View style={styles.postOwnerInfo}>
                  <View style={styles.reviewAvatar}>
                    <Text style={styles.reviewAvatarText}>
                      {(rev.reviewer_name || 'U').substring(0, 2).toUpperCase()}
                    </Text>
                  </View>
                  <View style={{ flex: 1 }}>
                    <Text style={styles.postOwnerName}>{rev.reviewer_name || 'Anonymous'}</Text>
                    <View style={[styles.starsRow, { marginTop: 2 }]}>
                      {[1,2,3,4,5].map((i) => (
                        <MaterialIcons key={i} name={i <= rev.rating ? 'star' : 'star-border'} size={13} color={i <= rev.rating ? BRAND : '#d1d5db'} />
                      ))}
                    </View>
                  </View>
                </View>
                <View style={{ alignItems: 'flex-end', gap: 4 }}>
                  <View style={styles.reviewMenuWrap}>
                    <TouchableOpacity
                      onPress={() => setReviewMenuOpenId(prev => prev === rev.review_id ? null : rev.review_id)}
                      style={styles.reviewMenuBtn}
                      hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
                    >
                      <MaterialIcons name="more-vert" size={18} color="#9ca3af" />
                    </TouchableOpacity>
                    {reviewMenuOpenId === rev.review_id && (
                      <View style={styles.reviewMenuDropdown}>
                        <TouchableOpacity
                          style={styles.postMenuItem}
                          onPress={() => openReviewReport(rev.review_id)}
                        >
                          <Text style={styles.postMenuDangerText}>Report</Text>
                        </TouchableOpacity>
                      </View>
                    )}
                  </View>
                </View>
              </View>
              {rev.project_title && (
                <Text style={styles.reviewProjectTitle}>
                  <Feather name="briefcase" size={11} color={T2} /> {rev.project_title}
                </Text>
              )}
              <Text style={styles.postContentText}>{rev.comment}</Text>
            </View>
          ))
        ) : (
          <View style={styles.emptyState}>
            <MaterialIcons name="rate-review" size={40} color="#d1d5db" />
            <Text style={styles.emptyTitle}>No reviews yet</Text>
            <Text style={styles.emptySubtext}>This contractor hasn't received any reviews.</Text>
          </View>
        )}
      </View>
    );
  };

  const renderAboutTab = () => (
    <View style={styles.tabContent}>
      {/* Quick Stats Strip */}
      <View style={styles.statsStrip}>
        {[
          { label: 'Experience', value: `${contractorAbout?.years_of_experience ?? contractor.years_of_experience ?? 0} yrs` },
          { label: 'Completed', value: String(completedProjects) },
          { label: 'Rating', value: avgRating ? avgRating.toFixed(1) : 'N/A' },
          { label: 'Reviews', value: String(totalReviews) },
        ].map((stat, i, arr) => (
          <React.Fragment key={stat.label}>
            <View style={styles.statStripItem}>
              <Text style={styles.statStripValue}>{stat.value}</Text>
              <Text style={styles.statStripLabel}>{stat.label}</Text>
            </View>
            {i < arr.length - 1 && <View style={styles.statStripDivider} />}
          </React.Fragment>
        ))}
      </View>

      {/* Description */}
      <View style={styles.aboutSection}>
        <Text style={styles.aboutSectionTitle}>Bio</Text>
        <Text style={styles.aboutText}>
          {description || 'No bio added yet.'}
        </Text>
      </View>

      {/* Services Offered */}
      {(contractorAbout?.services_offered || contractor.services_offered) ? (
        <View style={styles.aboutSection}>
          <Text style={styles.aboutSectionTitle}>Services Offered</Text>
          <Text style={styles.aboutText}>
            {contractorAbout?.services_offered || contractor.services_offered}
          </Text>
        </View>
      ) : null}

      {/* Business Details */}
      <View style={styles.aboutSection}>
        <Text style={styles.aboutSectionTitle}>Business Details</Text>

        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Contractor Type</Text>
          <Text style={styles.detailValue}>
            {contractorAbout?.type_name || contractor.contractor_type || 'General'}
          </Text>
        </View>

        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Experience</Text>
          <Text style={styles.detailValue}>
            {contractorAbout?.years_of_experience ?? contractor.years_of_experience ?? 0} years
          </Text>
        </View>

        {contractorAbout?.picab_category && (
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>PICAB Category</Text>
            <Text style={styles.detailValue}>{contractorAbout.picab_category}</Text>
          </View>
        )}

        {contractorAbout?.subscription_tier && contractorAbout.subscription_tier !== 'free' && (
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Subscription</Text>
            <View style={[styles.tierBadge, { backgroundColor: contractorAbout.subscription_tier === 'gold' ? '#FEF3C7' : '#F3E8FF' }]}>
              <Text style={[styles.tierBadgeText, { color: contractorAbout.subscription_tier === 'gold' ? '#d97706' : '#7c3aed' }]}>
                {contractorAbout.subscription_tier.charAt(0).toUpperCase() + contractorAbout.subscription_tier.slice(1)}
              </Text>
            </View>
          </View>
        )}

        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Verification</Text>
          <View style={[
            styles.verificationBadge,
            {
              backgroundColor:
                (header?.verification_status || contractorAbout?.verification_status) === 'approved' ? '#dcfce7'
                : (header?.verification_status || contractorAbout?.verification_status) === 'rejected' ? '#fef2f2'
                : '#f3f4f6',
            },
          ]}>
            <Text style={[
              styles.verificationText,
              {
                color:
                  (header?.verification_status || contractorAbout?.verification_status) === 'approved' ? '#16a34a'
                  : (header?.verification_status || contractorAbout?.verification_status) === 'rejected' ? '#dc2626'
                  : '#6b7280',
              },
            ]}>
              {((header?.verification_status || contractorAbout?.verification_status || 'pending').charAt(0).toUpperCase()
                + (header?.verification_status || contractorAbout?.verification_status || 'pending').slice(1))}
            </Text>
          </View>
        </View>

        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Member Since</Text>
          <Text style={styles.detailValue}>{formatDate(header?.member_since || contractor.created_at)}</Text>
        </View>
      </View>
    </View>
  );

  const renderTabContent = () => {
    switch (activeTab) {
      case 'portfolio':
        return renderPortfolioTab();
      case 'reviews':
        return renderReviewsTab();
      case 'about':
        return renderAboutTab();
      default:
        return renderPortfolioTab();
    }
  };

  /* ─── Loading state ───────────────────────────────────────────────── */

  const renderLoading = () => (
    <View style={styles.loadingContainer}>
      <ActivityIndicator size="large" color={BRAND} />
      <Text style={styles.loadingText}>Loading profile…</Text>
    </View>
  );

  if (selectedShowcasePost) {
    // Posts in this screen belong to the viewed profile, so ownership should
    // be based on whether the viewer is on their own profile.
    const isOwn = isOwnProfile;
    return (
      <ShowcasePostDetail
        post={selectedShowcasePost}
        isOwner={isOwn}
        initialImageIndex={initialShowcaseImageIndex}
        onClose={() => { setSelectedShowcasePost(null); setInitialShowcaseImageIndex(0); }}
        onViewProfile={(!isOwn && selectedShowcasePost.user_id) ? () => {
          const sp = selectedShowcasePost;
          setSelectedShowcasePost(null);
          // Only contractor profiles are supported here
          setIsOwnProfile(false);
        } : undefined}
      />
    );
  }
  return (
    <View style={[styles.container, { paddingTop: insets.top }]}> 
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.surface} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.headerBtn} activeOpacity={0.7}>
          <Feather name="arrow-left" size={20} color={T1} />
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[BRAND]} tintColor={BRAND} />
        }
      >
        {/* Cover Photo Section */}
        <View style={styles.coverSection}>
          <ImageFallback
            uri={coverPhotoUrl || undefined}
            defaultImage={defaultCoverPhoto}
            style={styles.coverPhoto}
            resizeMode="cover"
          />

          {/* Profile Picture */}
          <View style={styles.profilePicContainer}>
            <ImageFallback
              uri={logoUrl || undefined}
              defaultImage={defaultContractorAvatar}
              style={styles.profilePic}
              resizeMode="cover"
            />
          </View>
        </View>

        {/* Company Info */}
        <View style={styles.companySection}>
          <View style={styles.nameRow}>
            <Text style={styles.companyName}>{displayName}</Text>
            {header?.verification_status === 'approved' && (
              <MaterialIcons name="verified" size={22} color="#3b82f6" style={{ marginLeft: 6 }} />
            )}
          </View>

          {/* Contractor type */}
          {(contractorAbout?.type_name || contractor.contractor_type) && (
            <View style={styles.typeBadge}>
              <Feather name="briefcase" size={11} color={BRAND} />
              <Text style={styles.typeBadgeText}>
                {contractorAbout?.type_name || contractor.contractor_type}
              </Text>
            </View>
          )}

          {/* Rating & Location */}
          <View style={styles.infoChipsRow}>
            <View style={styles.infoChip}>
              <MaterialIcons name="star" size={14} color={BRAND} />
              <Text style={styles.infoChipValue}>{avgRating ? avgRating.toFixed(1) : '0.0'}</Text>
              <Text style={styles.infoChipSub}>({totalReviews})</Text>
            </View>
            <View style={styles.infoChipDivider} />
            <View style={[styles.infoChip, { flexShrink: 1 }]}>
              <Feather name="map-pin" size={12} color={T2} />
              <Text style={styles.infoChipText} numberOfLines={1}>
                {contractorAbout?.business_address || contractor.location || 'Location not set'}
              </Text>
            </View>
          </View>

          {/* Description */}
          <Text style={styles.descriptionPreview} numberOfLines={3}>
            {description || `We're ${displayName} — passionate about building spaces that last.`}
            {' '}
            <Text style={styles.seeMoreText}>See more</Text>
          </Text>

          {/* Action buttons */}
          <View style={styles.profileActions}>
            <TouchableOpacity style={styles.sendMessageBtn} activeOpacity={0.8} onPress={onSendMessage}>
              <Feather name="message-circle" size={16} color="#fff" />
              <Text style={styles.sendMessageText}>Send Message</Text>
            </TouchableOpacity>
          </View>
        </View>

        {/* Tabs */}
        <View style={styles.tabsContainer}>
          <View style={styles.tabsRow}>
            {tabs.map((tab) => {
              const active = activeTab === tab.key;
              const iconMap: Record<TabType, string> = {
                portfolio: 'grid', reviews: 'star', about: 'info',
              };
              return (
                <TouchableOpacity
                  key={tab.key}
                  style={[styles.tab, active && styles.tabActive]}
                  onPress={() => setActiveTab(tab.key)}
                  activeOpacity={0.7}
                >
                  <Feather name={iconMap[tab.key]} size={14} color={active ? BRAND : '#9ca3af'} />
                  <Text style={[styles.tabText, active && styles.tabTextActive]}>
                    {tab.label}{tab.count != null ? ` (${tab.count})` : ''}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
        </View>

        {/* Tab Content */}
        {loading ? renderLoading() : renderTabContent()}

        <View style={{ height: 40 }} />
      </ScrollView>

      {/* Create Showcase Modal */}
      <CreateShowcase
        visible={showCreateShowcase}
        onClose={() => setShowCreateShowcase(false)}
        onCreated={() => fetchProfile(true)}
      />

      <ReportPostModal
        visible={reportModalVisible}
        onClose={() => {
          setReportModalVisible(false);
          setReportPostId(null);
          setReportType('showcase');
        }}
        onSubmit={submitPostReport}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.surface,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 12,
    paddingVertical: 8,
    backgroundColor: COLORS.surface,
  },
  headerBtn: {
    width: 38,
    height: 38,
    borderRadius: 8,
    backgroundColor: '#f3f4f6',
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },

  /* ── Loading ─────────────────────────────── */
  loadingContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 60,
  },
  loadingText: {
    fontSize: 13,
    color: T2,
    marginTop: 12,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 20,
  },

  // Cover Section
  coverSection: {
    position: 'relative',
    height: 215,
  },
  coverPhoto: {
    width: '100%',
    height: 180,
  },
  profilePicContainer: {
    position: 'absolute',
    bottom: 0,
    left: 16,
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: COLORS.surface,
    padding: 3.5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 5,
  },
  profilePic: {
    width: '100%',
    height: '100%',
    borderRadius: 47,
  },
  profilePicPlaceholder: {
    width: '100%',
    height: '100%',
    borderRadius: 40,
    backgroundColor: BRAND_L,
    justifyContent: 'center',
    alignItems: 'center',
  },
  profilePicText: {
    fontSize: 26,
    fontWeight: '700',
    color: BRAND,
  },

  // Company Section
  companySection: {
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 4,
    borderBottomWidth: 1,
    borderBottomColor: BORDER,
  },
  nameRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 4,
  },
  companyName: {
    fontSize: 22,
    fontWeight: '800',
    color: T1,
  },
  typeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    backgroundColor: BRAND_L,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 4,
    gap: 5,
    marginBottom: 8,
  },
  typeBadgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: BRAND,
  },
  infoChipsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
    gap: 8,
  },
  infoChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  infoChipValue: {
    fontSize: 14,
    fontWeight: '700',
    color: T1,
  },
  infoChipText: {
    fontSize: 13,
    color: T2,
  },
  infoChipSub: {
    fontSize: 12,
    color: '#9ca3af',
  },
  infoChipDivider: {
    width: 4,
    height: 4,
    borderRadius: 2,
    backgroundColor: '#d1d5db',
  },
  descriptionPreview: {
    fontSize: 14,
    color: T2,
    lineHeight: 20,
    marginBottom: 12,
  },
  seeMoreText: {
    color: BRAND,
    fontWeight: '500',
  },
  profileActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 14,
  },
  sendMessageBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    borderRadius: 8,
    backgroundColor: BRAND,
    gap: 8,
    shadowColor: BRAND,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
    elevation: 3,
  },
  sendMessageText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#fff',
  },
  shareProfileBtn: {
    width: 44,
    height: 44,
    borderRadius: 22,
    borderWidth: 1.5,
    borderColor: BORDER,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#fff',
  },

  // Tabs
  tabsContainer: {
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: BORDER,
  },
  tabsRow: {
    flexDirection: 'row',
  },
  tab: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    gap: 5,
    borderBottomWidth: 3,
    borderBottomColor: 'transparent',
  },
  tabActive: {
    borderBottomColor: BRAND,
  },
  tabText: {
    fontSize: 13,
    fontWeight: '500',
    color: '#9ca3af',
  },
  tabTextActive: {
    color: BRAND,
    fontWeight: '700',
  },

  // Tab Content wrapper
  tabContent: {
    paddingTop: 6,
    paddingBottom: 20,
    backgroundColor: BG,
  },

  // Portfolio
  addShowcaseBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1.5,
    borderColor: BRAND,
    borderStyle: 'dashed',
    borderRadius: CARD_R,
    paddingVertical: 12,
    marginHorizontal: 8,
    marginBottom: 14,
    gap: 6,
  },
  addShowcaseBtnText: {
    fontSize: 13,
    fontWeight: '600',
    color: BRAND,
  },
  linkedBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#ecfdf5',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
    gap: 4,
  },
  linkedBadgeText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#047857',
  },
  portfolioCardImg: {
    height: 120,
    backgroundColor: '#f3f4f6',
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  portfolioCardTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: T1,
  },
  portfolioCardDesc: {
    fontSize: 13,
    color: T2,
    marginTop: 4,
    lineHeight: 18,
  },
  portfolioCardDate: {
    fontSize: 11,
    color: '#9ca3af',
    marginTop: 6,
  },

  // Highlights tab
  statsStrip: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    borderRadius: CARD_R,
    borderWidth: 1,
    borderColor: BORDER,
    marginHorizontal: 8,
    marginBottom: 16,
    overflow: 'hidden',
  },
  statStripItem: {
    flex: 1,
    alignItems: 'center',
    paddingVertical: 14,
  },
  statStripValue: {
    fontSize: 18,
    fontWeight: '800',
    color: BRAND,
  },
  statStripLabel: {
    fontSize: 11,
    color: T2,
    marginTop: 2,
  },
  statStripDivider: {
    width: 1,
    backgroundColor: BORDER,
    marginVertical: 10,
  },
  hlSectionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T2,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 10,
  },
  servicesSection: {
    marginTop: 16,
    backgroundColor: '#fff',
    borderRadius: CARD_R,
    borderWidth: 1,
    borderColor: BORDER,
    padding: 14,
  },
  servicesSectionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T2,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 8,
  },
  servicesText: {
    fontSize: 14,
    color: T2,
    lineHeight: 20,
  },

  // Reviews
  reviewsSummary: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 16,
    padding: 16,
    backgroundColor: '#fff',
    borderRadius: CARD_R,
    borderWidth: 1,
    borderColor: BORDER,
    marginHorizontal: 8,
    marginBottom: 8,
  },
  reviewsSummaryLeft: {
    alignItems: 'center',
    paddingRight: 16,
    borderRightWidth: 1,
    borderRightColor: BORDER,
  },
  reviewsAvgVal: {
    fontSize: 36,
    fontWeight: '800',
    color: T1,
  },
  reviewsAvgSub: {
    fontSize: 11,
    color: T2,
    marginTop: 2,
  },
  reviewsSummaryRight: {
    flex: 1,
    gap: 4,
  },
  starsRow: {
    flexDirection: 'row',
    gap: 2,
  },
  reviewsCountText: {
    fontSize: 13,
    color: T2,
    marginTop: 4,
  },
  reviewsDivider: {
    height: 1,
    backgroundColor: BORDER,
    marginHorizontal: 8,
    marginVertical: 10,
  },
  reviewCard: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 8,
    marginTop: 6,
    borderRadius: CARD_R,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    overflow: 'hidden',
  },
  reviewCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 8,
  },
  reviewAvatar: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: BRAND_L,
    justifyContent: 'center',
    alignItems: 'center',
  },
  reviewAvatarText: {
    fontSize: 12,
    fontWeight: '700',
    color: BRAND,
  },
  reviewerName: {
    fontSize: 13,
    fontWeight: '600',
    color: T1,
    marginBottom: 3,
  },
  reviewDate: {
    fontSize: 11,
    color: '#9ca3af',
  },
  reviewComment: {
    fontSize: 13,
    color: T2,
    lineHeight: 18,
  },

  // About Tab
  aboutSection: {
    marginHorizontal: 8,
    marginBottom: 12,
    backgroundColor: '#fff',
    borderRadius: CARD_R,
    borderWidth: 1,
    borderColor: BORDER,
    padding: 16,
  },
  aboutSectionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T2,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 10,
  },
  aboutText: {
    fontSize: 14,
    color: T1,
    lineHeight: 21,
  },
  contactItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
    gap: 10,
  },
  contactText: {
    fontSize: 14,
    color: T1,
    flex: 1,
  },
  contactClickable: {
    color: BRAND,
  },
  contactMessageBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: BRAND,
    paddingVertical: 11,
    paddingHorizontal: 20,
    borderRadius: CARD_R,
    marginTop: 14,
    gap: 8,
  },
  contactMessageText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#fff',
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 9,
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
    gap: 8,
  },
  detailLabel: {
    fontSize: 13,
    color: T2,
    width: 120,
  },
  detailValue: {
    fontSize: 13,
    fontWeight: '500',
    color: T1,
    flex: 1,
  },

  // Empty State
  emptyState: {
    alignItems: 'center',
    paddingVertical: 48,
    backgroundColor: '#fff',
    borderRadius: CARD_R,
    borderWidth: 1,
    borderColor: BORDER,
    marginHorizontal: 8,
    marginBottom: 12,
  },
  emptyTitle: {
    fontSize: 15,
    fontWeight: '600',
    color: T2,
    marginTop: 16,
    marginBottom: 8,
  },
  emptySubtext: {
    fontSize: 13,
    color: '#9ca3af',
    textAlign: 'center',
    paddingHorizontal: 40,
  },

  /* ── Collage ─────────────────────────────── */
  fbCollageWrap: {
    overflow: 'hidden',
    margin: 5,
  },
  fbCollageOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0,0,0,0.45)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  fbCollageOverlayText: {
    color: '#fff',
    fontSize: 22,
    fontWeight: '700',
  },

  /* ── Social Post Cards (Portfolio) ──────── */
  socialPostCard: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 8,
    marginTop: 6,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    overflow: 'hidden',
  },
  postCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
    paddingHorizontal: 16,
    paddingTop: 14,
  },
  postOwnerInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  postOwnerAvatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    marginRight: 10,
  },
  postOwnerName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333333',
  },
  postOwnerDate: {
    fontSize: 12,
    color: '#999999',
  },
  postTitleText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333333',
    marginBottom: 8,
    paddingHorizontal: 16,
  },
  postContentText: {
    fontSize: 14,
    color: '#666666',
    lineHeight: 20,
    marginBottom: 12,
    paddingHorizontal: 16,
  },
  postDetailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  postDetailText: {
    fontSize: 13,
    color: '#666666',
  },
  postMenuWrap: {
    position: 'relative',
    zIndex: 30,
  },
  postMenuButton: {
    width: 28,
    height: 28,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  postMenuDropdown: {
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
  postMenuItem: {
    paddingVertical: 8,
    paddingHorizontal: 12,
  },
  postMenuItemText: {
    fontSize: 13,
    color: '#1F2937',
    fontWeight: '500',
  },
  postMenuDangerText: {
    fontSize: 13,
    color: '#B91C1C',
    fontWeight: '600',
  },

  // Review card ⋮ menu
  reviewMenuWrap: {
    position: 'relative',
    marginLeft: 4,
  },
  reviewMenuBtn: {
    width: 28,
    height: 28,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  reviewMenuDropdown: {
    position: 'absolute',
    top: 30,
    right: 0,
    minWidth: 120,
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    paddingVertical: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.14,
    shadowRadius: 8,
    elevation: 8,
    zIndex: 30,
  },
  socialPostImg: {
    width: '100%',
    height: 220,
    backgroundColor: '#f3f4f6',
    borderTopLeftRadius: CARD_R,
    borderTopRightRadius: CARD_R,
  },
  socialPostBody: {
    padding: 14,
  },
  socialPostMeta: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
    marginTop: 8,
  },
  metaChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f3f4f6',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 6,
    gap: 4,
  },
  metaChipText: {
    fontSize: 11,
    color: T2,
  },
  /* ── Star Distribution Bars (Reviews) ──── */
  distributionContainer: {
    marginTop: 8,
  },
  distRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 4,
    gap: 6,
  },
  distLabel: {
    fontSize: 12,
    color: T2,
    width: 12,
    textAlign: 'right',
  },
  distBarBg: {
    flex: 1,
    height: 6,
    backgroundColor: '#f3f4f6',
    borderRadius: 3,
    overflow: 'hidden',
  },
  distBarFill: {
    height: '100%',
    backgroundColor: COLORS.star,
    borderRadius: 3,
  },
  distCount: {
    fontSize: 11,
    color: '#9ca3af',
    width: 24,
    textAlign: 'right',
  },

  /* ── Review extras ─────────────────────── */
  reviewProjectTitle: {
    fontSize: 11,
    color: BRAND,
    marginBottom: 4,
    paddingHorizontal: 16,
  },

  /* ── Tier & Verification badges (About) ─ */
  tierBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fef3c7',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
    gap: 4,
    alignSelf: 'flex-start',
  },
  tierBadgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#92400e',
  },
  verificationBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginTop: 2,
  },
  verificationText: {
    fontSize: 12,
    fontWeight: '500',
  },
});
