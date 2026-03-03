// @ts-nocheck
import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Dimensions,
  StatusBar,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather, MaterialIcons, Ionicons } from '@expo/vector-icons';
import { api_config } from '../../config/api';
import ImageFallback from '../../components/ImageFallbackFixed';
import { storage_service } from '../../utils/storage';
import {
  profile_service,
  ProfileData,
  ReviewItem,
  ReviewStats,
  OwnerAbout,
} from '../../services/profile_service';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

/* ─── Default images ─────────────────────────────────────────────── */
const defaultCoverPhoto = require('../../../assets/images/pictures/cp_default.jpg');
const defaultOwnerAvatar = require('../../../assets/images/pictures/property_owner_default.png');

/* ─── Design tokens ──────────────────────────────────────────────── */
const BRAND   = '#EEA24B';
const BRAND_L = '#FFF8EE';
const BORDER  = '#E8EAED';
const BG      = '#f0f2f5';
const T1      = '#1a1a1a';
const T2      = '#6b7280';
const CARD_R  = 12;
const COLORS = {
  primary: BRAND, primaryLight: BRAND_L, surface: '#FFFFFF',
  text: T1, textSecondary: T2, textMuted: '#94A3B8',
  border: BORDER, borderLight: '#f3f4f6', star: BRAND,
};

/* ─── Types ──────────────────────────────────────────────────────── */

export interface OwnerProp {
  owner_id?: number;
  user_id: number;
  name: string;
  profile_pic?: string | null;
  address?: string | null;
}

interface CheckOwnerProfileProps {
  owner: OwnerProp;
  onClose: () => void;
  onSendMessage?: () => void;
}

type TabType = 'projects' | 'reviews' | 'about';

/* ─── Helpers ────────────────────────────────────────────────────── */
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

/* ════════════════════════════════════════════════════════════════════ */

export default function CheckOwnerProfile({ owner, onClose, onSendMessage }: CheckOwnerProfileProps) {
  const insets = useSafeAreaInsets();
  const [activeTab, setActiveTab] = useState<TabType>('projects');

  /* ── State ── */
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [profile, setProfile] = useState<ProfileData | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isOwnProfile, setIsOwnProfile] = useState(false);

  useEffect(() => {
    (async () => {
      const user = await storage_service.get_user_data();
      if (user && owner.user_id && user.user_id === owner.user_id) {
        setIsOwnProfile(true);
      }
    })();
  }, [owner.user_id]);

  /* ── Fetch profile ── */
  const fetchProfile = useCallback(async (silent = false) => {
    if (!owner.user_id) {
      setError('No user ID available.');
      setLoading(false);
      return;
    }
    if (!silent) setLoading(true);
    setError(null);
    try {
      const res = await profile_service.get_profile(owner.user_id, 'owner');
      if (res.success && res.data) {
        setProfile(res.data);
      } else {
        setError(res.message || 'Could not load profile.');
      }
    } catch (e) {
      setError('An unexpected error occurred.');
      console.error('[CheckOwnerProfile] fetchProfile:', e);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [owner.user_id]);

  useEffect(() => { fetchProfile(); }, [fetchProfile]);

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchProfile(true);
  }, [fetchProfile]);

  /* ── Derived values ── */
  const header        = profile?.header;
  const displayName   = header?.display_name || owner.name;
  const avgRating     = header?.avg_rating ?? 0;
  const totalReviews  = header?.total_reviews ?? 0;
  const completedProjects = header?.completed_projects ?? 0;
  const ongoingProjects   = header?.ongoing_projects ?? 0;
  const totalProjects     = header?.total_projects ?? 0;
  const coverPhotoUrl = resolveImageUrl(header?.cover_photo);
  const avatarUrl     = resolveImageUrl(header?.profile_pic) || resolveImageUrl(owner.profile_pic);
  const ownerAbout    = profile?.about?.owner;
  const reviewsData   = profile?.reviews;
  const reviews       = reviewsData?.reviews ?? [];
  const reviewStats   = reviewsData?.stats ?? { avg_rating: avgRating, total_reviews: totalReviews, distribution: {} };

  const tabs: { key: TabType; label: string; icon: string; count?: number }[] = [
    { key: 'projects', label: 'Projects', icon: 'layers', count: totalProjects || undefined },
    { key: 'reviews',  label: 'Reviews',  icon: 'star',   count: totalReviews || undefined },
    { key: 'about',    label: 'About',    icon: 'info' },
  ];

  /* ═══════════════ Tabs ═══════════════ */

  const renderProjectsTab = () => {
    return (
      <View style={styles.tabContent}>
        {/* Stats Strip */}
        <View style={styles.statsStrip}>
          {[
            { label: 'Total', value: String(totalProjects) },
            { label: 'Completed', value: String(completedProjects) },
            { label: 'Ongoing', value: String(ongoingProjects) },
            { label: 'Rating', value: avgRating ? avgRating.toFixed(1) : 'N/A' },
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

        {/* Showcase posts (owners may also have posts) */}
        {(profile?.posts?.showcase_posts ?? []).length > 0 ? (
          (profile?.posts?.showcase_posts ?? []).map((post) => (
            <View key={`sp-${post.post_id}`} style={styles.postCard}>
              {post.images && post.images.length > 0 ? (
                <ImageFallback
                  uri={resolveImageUrl(post.images[0]?.file_path)}
                  style={styles.postCardImg}
                  resizeMode="cover"
                />
              ) : (
                <View style={styles.postCardImgPlaceholder}>
                  <MaterialIcons name="image" size={28} color="#c8cbd0" />
                </View>
              )}
              <View style={styles.postCardBody}>
                <Text style={styles.postCardTitle} numberOfLines={1}>
                  {post.title || 'Untitled Post'}
                </Text>
                <Text style={styles.postCardDesc} numberOfLines={3}>{post.content}</Text>
                {post.location && (
                  <View style={styles.metaChip}>
                    <Ionicons name="location-outline" size={12} color={T2} />
                    <Text style={styles.metaChipText}>{post.location}</Text>
                  </View>
                )}
                <Text style={styles.postCardDate}>{formatDate(post.created_at)}</Text>
              </View>
            </View>
          ))
        ) : !loading ? (
          <View style={styles.emptyState}>
            <MaterialIcons name="folder-open" size={48} color="#d1d5db" />
            <Text style={styles.emptyTitle}>No project posts yet</Text>
            <Text style={styles.emptySubtext}>
              This property owner hasn't shared any projects.
            </Text>
          </View>
        ) : null}
      </View>
    );
  };

  const renderReviewsTab = () => {
    const avg   = reviewStats.avg_rating || avgRating;
    const total = reviewStats.total_reviews || totalReviews;
    const dist  = reviewStats.distribution || {};

    return (
      <View style={styles.tabContent}>
        <View style={styles.reviewsSummary}>
          <View style={styles.reviewsSummaryLeft}>
            <Text style={styles.reviewsAvgVal}>{avg ? avg.toFixed(1) : '0.0'}</Text>
            <Text style={styles.reviewsAvgSub}>out of 5</Text>
          </View>
          <View style={styles.reviewsSummaryRight}>
            <View style={styles.starsRow}>
              {[1,2,3,4,5].map((i) => (
                <MaterialIcons key={i} name={i <= Math.round(avg) ? 'star' : 'star-border'} size={18} color={i <= Math.round(avg) ? BRAND : '#d1d5db'} />
              ))}
            </View>
            <Text style={styles.reviewsCountText}>{total} review{total !== 1 ? 's' : ''}</Text>

            {Object.keys(dist).length > 0 && (
              <View style={styles.distributionContainer}>
                {[5,4,3,2,1].map((star) => {
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
        </View>

        <View style={styles.divider} />

        {reviews.length > 0 ? (
          reviews.map((rev) => (
            <View key={rev.review_id} style={styles.reviewCard}>
              <View style={styles.reviewCardHeader}>
                <View style={styles.reviewAvatar}>
                  <Text style={styles.reviewAvatarText}>
                    {(rev.reviewer_name || 'U').substring(0, 2).toUpperCase()}
                  </Text>
                </View>
                <View style={{ flex: 1 }}>
                  <Text style={styles.reviewerName}>{rev.reviewer_name || 'Anonymous'}</Text>
                  <View style={styles.starsRow}>
                    {[1,2,3,4,5].map((i) => (
                      <MaterialIcons key={i} name={i <= rev.rating ? 'star' : 'star-border'} size={13} color={i <= rev.rating ? BRAND : '#d1d5db'} />
                    ))}
                  </View>
                </View>
                <Text style={styles.reviewDate}>{formatDate(rev.created_at)}</Text>
              </View>
              {rev.project_title && (
                <Text style={styles.reviewProjectTitle}>
                  <Feather name="briefcase" size={11} color={T2} /> {rev.project_title}
                </Text>
              )}
              <Text style={styles.reviewComment}>{rev.comment}</Text>
            </View>
          ))
        ) : (
          <View style={styles.emptyState}>
            <MaterialIcons name="rate-review" size={40} color="#d1d5db" />
            <Text style={styles.emptyTitle}>No reviews yet</Text>
            <Text style={styles.emptySubtext}>This property owner hasn't received any reviews.</Text>
          </View>
        )}
      </View>
    );
  };

  const renderAboutTab = () => (
    <View style={styles.tabContent}>
      {/* Personal Info */}
      <View style={styles.aboutSection}>
        <Text style={styles.aboutSectionTitle}>Personal Info</Text>
        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Full Name</Text>
          <Text style={styles.detailValue}>
            {[ownerAbout?.first_name, ownerAbout?.middle_name, ownerAbout?.last_name].filter(Boolean).join(' ') || displayName}
          </Text>
        </View>
        {ownerAbout?.occupation && (
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Occupation</Text>
            <Text style={styles.detailValue}>{ownerAbout.occupation}</Text>
          </View>
        )}
        {ownerAbout?.date_of_birth && (
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Birthday</Text>
            <Text style={styles.detailValue}>{formatDate(ownerAbout.date_of_birth)}</Text>
          </View>
        )}
        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Verification</Text>
          <View style={[
            styles.verificationBadge,
            {
              backgroundColor:
                ownerAbout?.verification_status === 'approved' ? '#dcfce7'
                : ownerAbout?.verification_status === 'rejected' ? '#fef2f2'
                : '#f3f4f6',
            },
          ]}>
            <Text style={[
              styles.verificationText,
              {
                color:
                  ownerAbout?.verification_status === 'approved' ? '#16a34a'
                  : ownerAbout?.verification_status === 'rejected' ? '#dc2626'
                  : '#6b7280',
              },
            ]}>
              {((ownerAbout?.verification_status || 'pending').charAt(0).toUpperCase()
                + (ownerAbout?.verification_status || 'pending').slice(1))}
            </Text>
          </View>
        </View>
        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Member Since</Text>
          <Text style={styles.detailValue}>{formatDate(header?.member_since)}</Text>
        </View>
      </View>

      {/* Contact */}
      <View style={styles.aboutSection}>
        <Text style={styles.aboutSectionTitle}>Contact</Text>
        <View style={styles.contactItem}>
          <Feather name="map-pin" size={18} color={BRAND} />
          <Text style={styles.contactText}>
            {ownerAbout?.address || owner.address || 'Location not specified'}
          </Text>
        </View>
        {ownerAbout?.phone_number && (
          <View style={styles.contactItem}>
            <Feather name="phone" size={18} color={BRAND} />
            <Text style={styles.contactText}>{ownerAbout.phone_number}</Text>
          </View>
        )}
        {profile?.about?.email && (
          <View style={styles.contactItem}>
            <Feather name="mail" size={18} color={BRAND} />
            <Text style={styles.contactText}>{profile.about.email}</Text>
          </View>
        )}

        <TouchableOpacity style={styles.ctaBtn} onPress={onSendMessage} activeOpacity={0.8}>
          <Feather name="message-circle" size={18} color="#fff" />
          <Text style={styles.ctaBtnText}>Send a Message</Text>
        </TouchableOpacity>
      </View>
    </View>
  );

  const renderTabContent = () => {
    switch (activeTab) {
      case 'projects': return renderProjectsTab();
      case 'reviews':  return renderReviewsTab();
      case 'about':    return renderAboutTab();
      default:         return renderProjectsTab();
    }
  };

  /* ═══════════════ Render ═══════════════ */

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.surface} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.headerBtn} activeOpacity={0.7}>
          <Feather name="arrow-left" size={20} color={T1} />
        </TouchableOpacity>
        <View style={styles.headerRight}>
          <TouchableOpacity style={styles.headerBtn} activeOpacity={0.7}>
            <Feather name="share-2" size={18} color={T1} />
          </TouchableOpacity>
          <TouchableOpacity style={styles.headerBtn} activeOpacity={0.7}>
            <Feather name="more-horizontal" size={20} color={T1} />
          </TouchableOpacity>
        </View>
      </View>

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={[BRAND]} tintColor={BRAND} />
        }
      >
        {/* Cover + Avatar */}
        <View style={styles.coverSection}>
          <ImageFallback
            uri={coverPhotoUrl || undefined}
            defaultImage={defaultCoverPhoto}
            style={styles.coverPhoto}
            resizeMode="cover"
          />
          <View style={styles.avatarContainer}>
            <ImageFallback
              uri={avatarUrl || undefined}
              defaultImage={defaultOwnerAvatar}
              style={styles.avatar}
              resizeMode="cover"
            />
          </View>
        </View>

        {/* Name & info */}
        <View style={styles.infoSection}>
          <View style={styles.nameRow}>
            <Text style={styles.displayName}>{displayName}</Text>
            {header?.verification_status === 'approved' && (
              <MaterialIcons name="verified" size={22} color="#3b82f6" style={{ marginLeft: 6 }} />
            )}
          </View>

          <View style={styles.roleBadge}>
            <Feather name="home" size={11} color={BRAND} />
            <Text style={styles.roleBadgeText}>Property Owner</Text>
          </View>

          {/* Chips row */}
          <View style={styles.infoChipsRow}>
            {(ownerAbout?.occupation || ownerAbout?.address || owner.address) && (
              <>
                {ownerAbout?.occupation && (
                  <View style={styles.infoChip}>
                    <Feather name="briefcase" size={12} color={T2} />
                    <Text style={styles.infoChipText}>{ownerAbout.occupation}</Text>
                  </View>
                )}
                {(ownerAbout?.address || owner.address) && (
                  <>
                    {ownerAbout?.occupation && <View style={styles.infoChipDivider} />}
                    <View style={[styles.infoChip, { flexShrink: 1 }]}>
                      <Feather name="map-pin" size={12} color={T2} />
                      <Text style={styles.infoChipText} numberOfLines={1}>
                        {ownerAbout?.address || owner.address}
                      </Text>
                    </View>
                  </>
                )}
              </>
            )}
          </View>

          {/* CTA */}
          <View style={styles.actionRow}>
            <TouchableOpacity style={styles.messageBtn} activeOpacity={0.8} onPress={onSendMessage}>
              <Feather name="message-circle" size={16} color="#fff" />
              <Text style={styles.messageBtnText}>Send Message</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.shareBtn} activeOpacity={0.7}>
              <Feather name="share-2" size={18} color={BRAND} />
            </TouchableOpacity>
          </View>
        </View>

        {/* Tabs */}
        <View style={styles.tabsContainer}>
          <View style={styles.tabsRow}>
            {tabs.map((tab) => {
              const active = activeTab === tab.key;
              return (
                <TouchableOpacity
                  key={tab.key}
                  style={[styles.tab, active && styles.tabActive]}
                  onPress={() => setActiveTab(tab.key)}
                  activeOpacity={0.7}
                >
                  <Feather name={tab.icon} size={14} color={active ? BRAND : '#9ca3af'} />
                  <Text style={[styles.tabText, active && styles.tabTextActive]}>
                    {tab.label}{tab.count != null ? ` (${tab.count})` : ''}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
        </View>

        {/* Tab content */}
        {loading ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color={BRAND} />
            <Text style={styles.loadingText}>Loading profile…</Text>
          </View>
        ) : renderTabContent()}

        <View style={{ height: 40 }} />
      </ScrollView>
    </View>
  );
}

/* ════════════════════════════════════════════════════════════════════ */
/* ─── Styles ─────────────────────────────────────────────────────── */
/* ════════════════════════════════════════════════════════════════════ */

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: COLORS.surface },

  /* Header */
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: 12, paddingVertical: 8, backgroundColor: COLORS.surface,
  },
  headerBtn: {
    width: 38, height: 38, borderRadius: 19, backgroundColor: '#f3f4f6',
    justifyContent: 'center', alignItems: 'center',
  },
  headerRight: { flexDirection: 'row', alignItems: 'center', gap: 6 },

  /* Loading */
  loadingContainer: { alignItems: 'center', justifyContent: 'center', paddingVertical: 60 },
  loadingText: { fontSize: 13, color: T2, marginTop: 12 },

  scrollView: { flex: 1 },
  scrollContent: { paddingBottom: 20 },

  /* Cover + Avatar */
  coverSection: { position: 'relative', height: 215 },
  coverPhoto: { width: '100%', height: 180 },
  avatarContainer: {
    position: 'absolute', bottom: 0, left: 16,
    width: 100, height: 100, borderRadius: 50,
    backgroundColor: COLORS.surface, padding: 3.5,
    shadowColor: '#000', shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.15, shadowRadius: 8, elevation: 5,
  },
  avatar: { width: '100%', height: '100%', borderRadius: 47 },

  /* Info section */
  infoSection: {
    paddingHorizontal: 16, paddingTop: 12, paddingBottom: 4,
    borderBottomWidth: 1, borderBottomColor: BORDER,
  },
  nameRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 4 },
  displayName: { fontSize: 22, fontWeight: '800', color: T1 },
  roleBadge: {
    flexDirection: 'row', alignItems: 'center', alignSelf: 'flex-start',
    backgroundColor: BRAND_L, paddingHorizontal: 10, paddingVertical: 4,
    borderRadius: 6, gap: 5, marginBottom: 8,
  },
  roleBadgeText: { fontSize: 12, fontWeight: '600', color: BRAND },
  infoChipsRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 10, gap: 8 },
  infoChip: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  infoChipText: { fontSize: 13, color: T2 },
  infoChipDivider: { width: 4, height: 4, borderRadius: 2, backgroundColor: '#d1d5db' },

  /* Action row */
  actionRow: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 14 },
  messageBtn: {
    flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    paddingVertical: 12, borderRadius: CARD_R, backgroundColor: BRAND, gap: 8,
    shadowColor: BRAND, shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25, shadowRadius: 4, elevation: 3,
  },
  messageBtnText: { fontSize: 14, fontWeight: '600', color: '#fff' },
  shareBtn: {
    width: 44, height: 44, borderRadius: 22,
    borderWidth: 1.5, borderColor: BORDER,
    justifyContent: 'center', alignItems: 'center', backgroundColor: '#fff',
  },

  /* Tabs */
  tabsContainer: { backgroundColor: '#fff', borderBottomWidth: 1, borderBottomColor: BORDER },
  tabsRow: { flexDirection: 'row' },
  tab: {
    flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    paddingVertical: 14, gap: 5, borderBottomWidth: 3, borderBottomColor: 'transparent',
  },
  tabActive: { borderBottomColor: BRAND },
  tabText: { fontSize: 13, fontWeight: '500', color: '#9ca3af' },
  tabTextActive: { color: BRAND, fontWeight: '700' },

  /* Tab content */
  tabContent: { padding: 16, backgroundColor: BG },

  /* Stats strip */
  statsStrip: {
    flexDirection: 'row', backgroundColor: '#fff', borderRadius: CARD_R,
    borderWidth: 1, borderColor: BORDER, marginBottom: 16, overflow: 'hidden',
  },
  statStripItem: { flex: 1, alignItems: 'center', paddingVertical: 14 },
  statStripValue: { fontSize: 18, fontWeight: '800', color: BRAND },
  statStripLabel: { fontSize: 11, color: T2, marginTop: 2 },
  statStripDivider: { width: 1, backgroundColor: BORDER, marginVertical: 10 },

  /* Post cards */
  postCard: {
    backgroundColor: '#fff', borderRadius: CARD_R, borderWidth: 1,
    borderColor: BORDER, marginBottom: 12, overflow: 'hidden',
    shadowColor: '#000', shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.06, shadowRadius: 3, elevation: 1,
  },
  postCardImg: { width: '100%', height: 220, backgroundColor: '#f3f4f6' },
  postCardImgPlaceholder: {
    height: 120, backgroundColor: '#f3f4f6',
    justifyContent: 'center', alignItems: 'center',
  },
  postCardBody: { padding: 14 },
  postCardTitle: { fontSize: 15, fontWeight: '700', color: T1 },
  postCardDesc: { fontSize: 13, color: T2, marginTop: 4, lineHeight: 18 },
  postCardDate: { fontSize: 11, color: '#9ca3af', marginTop: 6 },
  metaChip: {
    flexDirection: 'row', alignItems: 'center', marginTop: 6,
    backgroundColor: '#f3f4f6', paddingHorizontal: 8, paddingVertical: 4,
    borderRadius: 6, gap: 4, alignSelf: 'flex-start',
  },
  metaChipText: { fontSize: 11, color: T2 },

  /* Reviews */
  reviewsSummary: {
    flexDirection: 'row', alignItems: 'center', gap: 16, padding: 16,
    backgroundColor: '#fff', borderRadius: CARD_R, borderWidth: 1,
    borderColor: BORDER, marginBottom: 8,
  },
  reviewsSummaryLeft: {
    alignItems: 'center', paddingRight: 16, borderRightWidth: 1, borderRightColor: BORDER,
  },
  reviewsAvgVal: { fontSize: 36, fontWeight: '800', color: T1 },
  reviewsAvgSub: { fontSize: 11, color: T2, marginTop: 2 },
  reviewsSummaryRight: { flex: 1, gap: 4 },
  starsRow: { flexDirection: 'row', gap: 2 },
  reviewsCountText: { fontSize: 13, color: T2, marginTop: 4 },
  divider: { height: 1, backgroundColor: BORDER, marginVertical: 10 },
  distributionContainer: { marginTop: 8 },
  distRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 4, gap: 6 },
  distLabel: { fontSize: 12, color: T2, width: 12, textAlign: 'right' },
  distBarBg: { flex: 1, height: 6, backgroundColor: '#f3f4f6', borderRadius: 3, overflow: 'hidden' },
  distBarFill: { height: '100%', backgroundColor: BRAND, borderRadius: 3 },
  distCount: { fontSize: 11, color: '#9ca3af', width: 24, textAlign: 'right' },
  reviewCard: {
    backgroundColor: '#fff', borderRadius: CARD_R, borderWidth: 1,
    borderColor: BORDER, padding: 12, marginBottom: 8,
    shadowColor: '#000', shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05, shadowRadius: 3, elevation: 1,
  },
  reviewCardHeader: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 8 },
  reviewAvatar: {
    width: 36, height: 36, borderRadius: 18,
    backgroundColor: BRAND_L, justifyContent: 'center', alignItems: 'center',
  },
  reviewAvatarText: { fontSize: 12, fontWeight: '700', color: BRAND },
  reviewerName: { fontSize: 13, fontWeight: '600', color: T1, marginBottom: 3 },
  reviewDate: { fontSize: 11, color: '#9ca3af' },
  reviewProjectTitle: { fontSize: 11, color: BRAND, marginBottom: 4 },
  reviewComment: { fontSize: 13, color: T2, lineHeight: 18 },

  /* About */
  aboutSection: {
    marginBottom: 12, backgroundColor: '#fff', borderRadius: CARD_R,
    borderWidth: 1, borderColor: BORDER, padding: 16,
  },
  aboutSectionTitle: {
    fontSize: 13, fontWeight: '700', color: T2,
    textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 10,
  },
  detailRow: {
    flexDirection: 'row', alignItems: 'center', paddingVertical: 9,
    borderBottomWidth: 1, borderBottomColor: '#f3f4f6', gap: 8,
  },
  detailLabel: { fontSize: 13, color: T2, width: 110 },
  detailValue: { fontSize: 13, fontWeight: '500', color: T1, flex: 1 },
  verificationBadge: {
    flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 2,
    paddingHorizontal: 8, paddingVertical: 3, borderRadius: 6,
  },
  verificationText: { fontSize: 12, fontWeight: '500' },
  contactItem: {
    flexDirection: 'row', alignItems: 'center', paddingVertical: 10,
    borderBottomWidth: 1, borderBottomColor: '#f3f4f6', gap: 10,
  },
  contactText: { fontSize: 14, color: T1, flex: 1 },
  ctaBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    backgroundColor: BRAND, paddingVertical: 11, paddingHorizontal: 20,
    borderRadius: CARD_R, marginTop: 14, gap: 8,
  },
  ctaBtnText: { fontSize: 14, fontWeight: '600', color: '#fff' },

  /* Empty */
  emptyState: {
    alignItems: 'center', paddingVertical: 48, backgroundColor: '#fff',
    borderRadius: CARD_R, borderWidth: 1, borderColor: BORDER, marginBottom: 12,
  },
  emptyTitle: { fontSize: 15, fontWeight: '600', color: T2, marginTop: 16, marginBottom: 8 },
  emptySubtext: { fontSize: 13, color: '#9ca3af', textAlign: 'center', paddingHorizontal: 40 },
});
