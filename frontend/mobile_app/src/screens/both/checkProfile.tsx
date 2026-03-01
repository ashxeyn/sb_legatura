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
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather, MaterialIcons, Ionicons } from '@expo/vector-icons';
import { api_config } from '../../config/api';
import ImageFallback from '../../components/ImageFallbackFixed';

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
const CARD_R  = 8;
const STATUS_LABELS: Record<string, {label:string;color:string;bg:string}> = {
  completed:   { label: 'Completed',   color: '#16a34a', bg: '#dcfce7' },
  in_progress: { label: 'In Progress', color: '#d97706', bg: '#fef3c7' },
  active:      { label: 'Active',      color: '#2563eb', bg: '#dbeafe' },
  pending:     { label: 'Pending',     color: '#6b7280', bg: '#f3f4f6' },
};
const SAMPLE_PORTFOLIO = [
  { id: 1, title: 'Makati Office Renovation', description: 'Full interior renovation of a 5-storey commercial building.', date: 'Jan 2025', image_url: null, isHighlighted: true },
  { id: 2, title: 'BGC Residential Fit-out', description: 'Luxury condo unit fit-out for a private client.', date: 'Nov 2024', image_url: null, isHighlighted: false },
  { id: 3, title: 'QC School Expansion', description: 'Added two-storey extension to an existing school building.', date: 'Sep 2024', image_url: null, isHighlighted: false },
];
const SAMPLE_HIGHLIGHTS = [
  { id: 1, title: 'SM Aura Food Hall Renovation', status: 'completed', budget: '₱4.2M', duration: '5 months' },
  { id: 2, title: 'Alabang Town Center Expansion', status: 'in_progress', budget: '₱8.7M', duration: '10 months' },
];
const SAMPLE_REVIEWS = [
  { id: 1, reviewer: 'Maria Santos', rating: 5, comment: 'Excellent work! Delivered on time and within budget.', date: 'Feb 2025' },
  { id: 2, reviewer: 'Juan dela Cruz', rating: 4, comment: 'Professional team, clean workmanship.', date: 'Jan 2025' },
];
// Legacy COLORS kept for any remaining references
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

interface PortfolioProject {
  id: number;
  title: string;
  description?: string;
  image_url?: string;
  company_name?: string;
  username?: string;
}

interface CheckProfileProps {
  contractor: Contractor;
  onClose: () => void;
  onSendMessage?: () => void;
}

// Tab options
type TabType = 'portfolio' | 'highlights' | 'reviews' | 'about';

export default function CheckProfile({ contractor, onClose, onSendMessage }: CheckProfileProps) {
  const insets = useSafeAreaInsets();
  const [activeTab, setActiveTab] = useState<TabType>('portfolio');

  // Build image URLs
  const coverPhotoUrl = contractor.cover_photo
    ? (contractor.cover_photo.startsWith('http')
      ? contractor.cover_photo
      : `${api_config.base_url}/storage/${contractor.cover_photo}`)
    : null;

  const logoUrl = contractor.logo_url
    ? (contractor.logo_url.startsWith('http')
      ? contractor.logo_url
      : `${api_config.base_url}/storage/${contractor.logo_url}`)
    : null;

  // Generate initials for avatar fallback
  const initials = contractor.company_name
    ?.split(' ')
    .slice(0, 2)
    .map(word => word[0])
    .join('')
    .toUpperCase() || 'CO';

  // Mock portfolio projects (replace with API data when available)
  const portfolioProjects: PortfolioProject[] = [
    {
      id: 1,
      title: 'Modern Two-Storey Residential House Project',
      company_name: contractor.company_name,
      username: `@${contractor.company_name?.toLowerCase().replace(/\s+/g, '_')}`,
      image_url: 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800',
    },
    {
      id: 2,
      title: 'Commercial Building Renovation',
      company_name: contractor.company_name,
      username: `@${contractor.company_name?.toLowerCase().replace(/\s+/g, '_')}`,
      image_url: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800',
    },
  ];

  // Format date
  const formatDate = (dateString?: string) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  };

  // Render tabs
  const tabs: { key: TabType; label: string }[] = [
    { key: 'portfolio', label: 'Portfolio' },
    { key: 'highlights', label: 'Highlights' },
    { key: 'reviews', label: 'Reviews' },
    { key: 'about', label: 'About' },
  ];

  const renderPortfolioTab = () => (
    <View style={styles.tabContent}>
      <View style={styles.portfolioGrid}>
        {SAMPLE_PORTFOLIO.map((item) => (
          <View key={item.id} style={[styles.portfolioCard, item.isHighlighted && styles.portfolioCardHL]}>
            <View style={styles.portfolioCardImg}>
              <MaterialIcons name="image" size={28} color="#c8cbd0" />
              {item.isHighlighted && (
                <View style={styles.hlBadge}>
                  <MaterialIcons name="star" size={10} color="#fff" />
                  <Text style={styles.hlBadgeText}>Highlighted</Text>
                </View>
              )}
            </View>
            <View style={styles.portfolioCardBody}>
              <Text style={styles.portfolioCardTitle} numberOfLines={1}>{item.title}</Text>
              <Text style={styles.portfolioCardDesc} numberOfLines={2}>{item.description}</Text>
              <Text style={styles.portfolioCardDate}>{item.date}</Text>
            </View>
          </View>
        ))}
      </View>
    </View>
  );

  const renderHighlightsTab = () => (
    <View style={styles.tabContent}>
      {/* Stats strip */}
      <View style={styles.statsStrip}>
        {[
          { label: 'Experience', value: `${contractor.years_of_experience || 0} yrs` },
          { label: 'Completed', value: String(contractor.completed_projects || 0) },
          { label: 'Rating', value: contractor.rating?.toFixed(1) || 'N/A' },
          { label: 'Reviews', value: String(contractor.reviews_count || 0) },
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

      {/* Featured project cards */}
      <Text style={styles.hlSectionTitle}>Featured Projects</Text>
      {SAMPLE_HIGHLIGHTS.map((item) => {
        const st = STATUS_LABELS[item.status] || STATUS_LABELS.pending;
        return (
          <View key={item.id} style={styles.hlProjectCard}>
            <View style={styles.hlProjectImg}>
              <MaterialIcons name="image" size={28} color="#c8cbd0" />
            </View>
            <View style={styles.hlProjectBody}>
              <View style={styles.hlProjectTitleRow}>
                <Text style={styles.hlProjectTitle} numberOfLines={1}>{item.title}</Text>
                <View style={[styles.statusBadge, { backgroundColor: st.bg }]}>
                  <Text style={[styles.statusBadgeText, { color: st.color }]}>{st.label}</Text>
                </View>
              </View>
              <View style={styles.hlProjectMeta}>
                <Ionicons name="cash-outline" size={13} color={T2} />
                <Text style={styles.hlProjectMetaText}>{item.budget}</Text>
                <Ionicons name="time-outline" size={13} color={T2} style={{ marginLeft: 12 }} />
                <Text style={styles.hlProjectMetaText}>{item.duration}</Text>
              </View>
            </View>
          </View>
        );
      })}

      {contractor.services_offered && (
        <View style={styles.servicesSection}>
          <Text style={styles.servicesSectionTitle}>Services Offered</Text>
          <Text style={styles.servicesText}>{contractor.services_offered}</Text>
        </View>
      )}
    </View>
  );

  const avgRating = SAMPLE_REVIEWS.length
    ? Math.round((SAMPLE_REVIEWS.reduce((s, r) => s + r.rating, 0) / SAMPLE_REVIEWS.length) * 10) / 10
    : (contractor.rating || 0);

  const renderReviewsTab = () => (
    <View style={styles.tabContent}>
      {/* Summary */}
      <View style={styles.reviewsSummary}>
        <View style={styles.reviewsSummaryLeft}>
          <Text style={styles.reviewsAvgVal}>{avgRating.toFixed(1)}</Text>
          <Text style={styles.reviewsAvgSub}>out of 5</Text>
        </View>
        <View style={styles.reviewsSummaryRight}>
          <View style={styles.starsRow}>
            {[1,2,3,4,5].map((i) => (
              <MaterialIcons key={i} name={i<=Math.round(avgRating)?'star':'star-border'} size={18} color={i<=Math.round(avgRating)?BRAND:'#d1d5db'} />
            ))}
          </View>
          <Text style={styles.reviewsCountText}>{SAMPLE_REVIEWS.length} review{SAMPLE_REVIEWS.length!==1?'s':''}</Text>
        </View>
      </View>
      <View style={styles.reviewsDivider} />
      {/* Cards */}
      {SAMPLE_REVIEWS.map((rev) => (
        <View key={rev.id} style={styles.reviewCard}>
          <View style={styles.reviewCardHeader}>
            <View style={styles.reviewAvatar}>
              <Text style={styles.reviewAvatarText}>{rev.reviewer.substring(0,2).toUpperCase()}</Text>
            </View>
            <View style={{ flex: 1 }}>
              <Text style={styles.reviewerName}>{rev.reviewer}</Text>
              <View style={styles.starsRow}>
                {[1,2,3,4,5].map((i) => (
                  <MaterialIcons key={i} name={i<=rev.rating?'star':'star-border'} size={13} color={i<=rev.rating?BRAND:'#d1d5db'} />
                ))}
              </View>
            </View>
            <Text style={styles.reviewDate}>{rev.date}</Text>
          </View>
          <Text style={styles.reviewComment}>{rev.comment}</Text>
        </View>
      ))}
    </View>
  );

  const renderAboutTab = () => (
    <View style={styles.tabContent}>
      {/* Description */}
      <View style={styles.aboutSection}>
        <Text style={styles.aboutSectionTitle}>About</Text>
        <Text style={styles.aboutText}>
          {contractor.company_description || 'No description available.'}
        </Text>
      </View>

      {/* Contact Info */}
      <View style={styles.aboutSection}>
        <Text style={styles.aboutSectionTitle}>Contact Information</Text>

        <View style={styles.contactItem}>
          <Feather name="map-pin" size={18} color={COLORS.primary} />
          <Text style={styles.contactText}>{contractor.location || 'Location not specified'}</Text>
        </View>

        {contractor.company_email && (
          <TouchableOpacity style={styles.contactItem} onPress={onSendMessage} activeOpacity={0.7}>
            <Feather name="mail" size={18} color={COLORS.primary} />
            <Text style={[styles.contactText, styles.contactClickable]}>{contractor.company_email}</Text>
            <Feather name="chevron-right" size={16} color={COLORS.textMuted} style={{ marginLeft: 'auto' }} />
          </TouchableOpacity>
        )}

        {contractor.company_phone && (
          <View style={styles.contactItem}>
            <Feather name="phone" size={18} color={COLORS.primary} />
            <Text style={styles.contactText}>{contractor.company_phone}</Text>
          </View>
        )}

        {contractor.company_website && (
          <View style={styles.contactItem}>
            <Feather name="globe" size={18} color={COLORS.primary} />
            <Text style={styles.contactText}>{contractor.company_website}</Text>
          </View>
        )}

        {/* Quick Message Button */}
        <TouchableOpacity style={styles.contactMessageBtn} onPress={onSendMessage} activeOpacity={0.8}>
          <Feather name="message-circle" size={18} color={COLORS.surface} />
          <Text style={styles.contactMessageText}>Send a Message</Text>
        </TouchableOpacity>
      </View>

      {/* Business Details */}
      <View style={styles.aboutSection}>
        <Text style={styles.aboutSectionTitle}>Business Details</Text>

        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Contractor Type</Text>
          <Text style={styles.detailValue}>{contractor.contractor_type || 'General'}</Text>
        </View>

        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Member Since</Text>
          <Text style={styles.detailValue}>{formatDate(contractor.created_at)}</Text>
        </View>
      </View>
    </View>
  );

  const renderTabContent = () => {
    switch (activeTab) {
      case 'portfolio':
        return renderPortfolioTab();
      case 'highlights':
        return renderHighlightsTab();
      case 'reviews':
        return renderReviewsTab();
      case 'about':
        return renderAboutTab();
      default:
        return renderPortfolioTab();
    }
  };

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.surface} />

      {/* Fixed Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="chevron-left" size={24} color={COLORS.text} />
          <Text style={styles.backText}>Back</Text>
        </TouchableOpacity>
        <View style={styles.headerRight}>
          <TouchableOpacity style={styles.headerIconBtn}>
            <Feather name="search" size={22} color={COLORS.text} />
          </TouchableOpacity>
          <TouchableOpacity style={styles.headerIconBtn}>
            <Feather name="bell" size={22} color={COLORS.text} />
            <View style={styles.notificationDot} />
          </TouchableOpacity>
        </View>
      </View>

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
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
          <Text style={styles.companyName}>{contractor.company_name}</Text>

          <View style={styles.ratingLocationRow}>
            <MaterialIcons name="star" size={18} color={COLORS.star} />
            <Text style={styles.ratingText}>{contractor.rating?.toFixed(1) || '5.0'} Rating</Text>
            <Text style={styles.dotSeparator}>•</Text>
            <Text style={styles.locationText}>{contractor.location || 'Location not set'}</Text>
          </View>

          {/* Description Preview */}
          <Text style={styles.descriptionPreview} numberOfLines={3}>
            {contractor.company_description || `We're ${contractor.company_name} — passionate about building spaces that last.`}
            {' '}
            <Text style={styles.seeMoreText}>See more...</Text>
          </Text>

          {/* Action buttons */}
          <View style={styles.profileActions}>
            <TouchableOpacity style={styles.sendMessageBtn} activeOpacity={0.8} onPress={onSendMessage}>
              <Feather name="send" size={16} color={BRAND} />
              <Text style={styles.sendMessageText}>Send Message</Text>
            </TouchableOpacity>
          </View>
        </View>

        {/* Tabs */}
        <View style={styles.tabsContainer}>
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.tabsScrollContent}
          >
            {tabs.map((tab) => (
              <TouchableOpacity
                key={tab.key}
                style={[styles.tab, activeTab === tab.key && styles.tabActive]}
                onPress={() => setActiveTab(tab.key)}
              >
                <Text style={[styles.tabText, activeTab === tab.key && styles.tabTextActive]}>
                  {tab.label}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>

        {/* Tab Content */}
        {renderTabContent()}

        <View style={{ height: 40 }} />
      </ScrollView>
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
    paddingHorizontal: 16,
    paddingVertical: 10,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  backButton: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  backText: {
    fontSize: 1,
    color: COLORS.text,
    marginLeft: 4,
  },
  headerRight: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  headerIconBtn: {
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  notificationDot: {
    position: 'absolute',
    top: 8,
    right: 8,
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.error,
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
    height: 180,
  },
  coverPhoto: {
    width: '100%',
    height: 150,
  },
  profilePicContainer: {
    position: 'absolute',
    bottom: 0,
    left: 16,
    width: 86,
    height: 86,
    borderRadius: 43,
    backgroundColor: COLORS.surface,
    padding: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  profilePic: {
    width: '100%',
    height: '100%',
    borderRadius: 40,
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
  companyName: {
    fontSize: 20,
    fontWeight: '700',
    color: T1,
    marginBottom: 4,
  },
  ratingLocationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
    gap: 4,
  },
  ratingText: {
    fontSize: 13,
    color: T2,
  },
  dotSeparator: {
    fontSize: 13,
    color: '#c8cbd0',
    marginHorizontal: 2,
  },
  locationText: {
    fontSize: 13,
    color: T2,
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
    marginBottom: 14,
  },
  sendMessageBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: CARD_R,
    borderWidth: 1.5,
    borderColor: BRAND,
    alignSelf: 'flex-start',
    gap: 6,
  },
  sendMessageText: {
    fontSize: 13,
    fontWeight: '600',
    color: BRAND,
  },

  // Tabs
  tabsContainer: {
    borderBottomWidth: 1,
    borderBottomColor: BORDER,
    backgroundColor: '#fff',
  },
  tabsScrollContent: {
    paddingHorizontal: 8,
  },
  tab: {
    paddingVertical: 13,
    paddingHorizontal: 18,
    borderBottomWidth: 2,
    borderBottomColor: 'transparent',
  },
  tabActive: {
    borderBottomColor: BRAND,
  },
  tabText: {
    fontSize: 13,
    fontWeight: '500',
    color: T2,
  },
  tabTextActive: {
    color: BRAND,
    fontWeight: '700',
  },

  // Tab Content wrapper
  tabContent: {
    padding: 16,
    backgroundColor: BG,
  },

  // Portfolio grid
  portfolioGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  portfolioCard: {
    width: (SCREEN_WIDTH - 42) / 2,
    backgroundColor: '#fff',
    borderRadius: CARD_R,
    borderWidth: 1,
    borderColor: BORDER,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.06,
    shadowRadius: 3,
    elevation: 1,
  },
  portfolioCardHL: {
    borderColor: BRAND,
    borderWidth: 1.5,
  },
  portfolioCardImg: {
    height: 100,
    backgroundColor: '#f3f4f6',
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  hlBadge: {
    position: 'absolute',
    top: 6,
    left: 6,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    backgroundColor: BRAND,
    borderRadius: 4,
    paddingHorizontal: 6,
    paddingVertical: 3,
  },
  hlBadgeText: {
    fontSize: 10,
    fontWeight: '600',
    color: '#fff',
  },
  portfolioCardBody: {
    padding: 10,
  },
  portfolioCardTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T1,
  },
  portfolioCardDesc: {
    fontSize: 12,
    color: T2,
    marginTop: 3,
    lineHeight: 16,
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
    fontWeight: '700',
    color: T1,
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
  hlProjectCard: {
    backgroundColor: '#fff',
    borderRadius: CARD_R,
    borderWidth: 1,
    borderColor: BORDER,
    marginBottom: 10,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.06,
    shadowRadius: 3,
    elevation: 1,
  },
  hlProjectImg: {
    height: 80,
    backgroundColor: '#f3f4f6',
    justifyContent: 'center',
    alignItems: 'center',
  },
  hlProjectBody: {
    padding: 12,
  },
  hlProjectTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 8,
    marginBottom: 8,
  },
  hlProjectTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: T1,
    flex: 1,
  },
  statusBadge: {
    borderRadius: 4,
    paddingHorizontal: 8,
    paddingVertical: 3,
  },
  statusBadgeText: {
    fontSize: 11,
    fontWeight: '600',
  },
  hlProjectMeta: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  hlProjectMetaText: {
    fontSize: 12,
    color: T2,
    marginLeft: 4,
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
    marginVertical: 10,
  },
  reviewCard: {
    backgroundColor: '#fff',
    borderRadius: CARD_R,
    borderWidth: 1,
    borderColor: BORDER,
    padding: 12,
    marginBottom: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 1,
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
    marginBottom: 16,
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
    paddingVertical: 40,
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
});
