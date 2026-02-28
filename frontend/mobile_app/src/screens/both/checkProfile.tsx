// @ts-nocheck
import React, { useState, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Image,
  Dimensions,
  StatusBar,
  Animated,
  FlatList,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather, MaterialIcons, Ionicons } from '@expo/vector-icons';
import { api_config } from '../../config/api';
import ImageFallback from '../../components/ImageFallbackFixed';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

// Default images
const defaultCoverPhoto = require('../../../assets/images/pictures/cp_default.jpg');
const defaultContractorAvatar = require('../../../assets/images/pictures/contractor_default.png');

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
  info: '#3B82F6',
  infoLight: '#DBEAFE',
  background: '#F8FAFC',
  surface: '#FFFFFF',
  text: '#0F172A',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
  star: '#FFC107',
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
  const scrollY = useRef(new Animated.Value(0)).current;

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

  const renderPortfolioItem = ({ item }: { item: PortfolioProject }) => (
    <View style={styles.portfolioItem}>
      {/* Project Header */}
      <View style={styles.portfolioHeader}>
        <View style={styles.portfolioAvatar}>
          {logoUrl ? (
            <Image source={{ uri: logoUrl }} style={styles.portfolioAvatarImage} />
          ) : (
            <View style={styles.portfolioAvatarPlaceholder}>
              <Text style={styles.portfolioAvatarText}>{initials}</Text>
            </View>
          )}
        </View>
        <View style={styles.portfolioInfo}>
          <Text style={styles.portfolioCompany}>{item.company_name}</Text>
          <Text style={styles.portfolioUsername}>{item.username}</Text>
        </View>
        <TouchableOpacity style={styles.portfolioMoreBtn}>
          <Feather name="more-horizontal" size={20} color={COLORS.textMuted} />
        </TouchableOpacity>
      </View>

      {/* Project Title */}
      <Text style={styles.portfolioTitle}>{item.title}</Text>
      <TouchableOpacity>
        <Text style={styles.portfolioMoreDetails}>More details...</Text>
      </TouchableOpacity>

      {/* Project Image */}
      {item.image_url && (
        <Image
          source={{ uri: item.image_url }}
          style={styles.portfolioImage}
          resizeMode="cover"
        />
      )}
    </View>
  );

  const renderPortfolioTab = () => (
    <FlatList
      data={portfolioProjects}
      renderItem={renderPortfolioItem}
      keyExtractor={(item) => item.id.toString()}
      contentContainerStyle={styles.portfolioList}
      scrollEnabled={false}
      ListEmptyComponent={
        <View style={styles.emptyState}>
          <Feather name="image" size={48} color={COLORS.border} />
          <Text style={styles.emptyTitle}>No Portfolio Yet</Text>
          <Text style={styles.emptySubtext}>
            This contractor hasn't added any portfolio projects yet.
          </Text>
        </View>
      }
    />
  );

  const renderHighlightsTab = () => (
    <View style={styles.tabContent}>
      <View style={styles.highlightsGrid}>
        <View style={styles.highlightCard}>
          <View style={[styles.highlightIcon, { backgroundColor: COLORS.primaryLight }]}>
            <Feather name="award" size={24} color={COLORS.primary} />
          </View>
          <Text style={styles.highlightValue}>{contractor.years_of_experience || 0}</Text>
          <Text style={styles.highlightLabel}>Years Experience</Text>
        </View>

        <View style={styles.highlightCard}>
          <View style={[styles.highlightIcon, { backgroundColor: COLORS.successLight }]}>
            <Feather name="check-circle" size={24} color={COLORS.success} />
          </View>
          <Text style={styles.highlightValue}>{contractor.completed_projects || 0}</Text>
          <Text style={styles.highlightLabel}>Projects Completed</Text>
        </View>

        <View style={styles.highlightCard}>
          <View style={[styles.highlightIcon, { backgroundColor: COLORS.warningLight }]}>
            <MaterialIcons name="star" size={24} color={COLORS.star} />
          </View>
          <Text style={styles.highlightValue}>{contractor.rating?.toFixed(1) || '5.0'}</Text>
          <Text style={styles.highlightLabel}>Average Rating</Text>
        </View>

        <View style={styles.highlightCard}>
          <View style={[styles.highlightIcon, { backgroundColor: COLORS.infoLight }]}>
            <Feather name="message-square" size={24} color={COLORS.info} />
          </View>
          <Text style={styles.highlightValue}>{contractor.reviews_count || 0}</Text>
          <Text style={styles.highlightLabel}>Client Reviews</Text>
        </View>
      </View>

      {contractor.services_offered && (
        <View style={styles.servicesSection}>
          <Text style={styles.servicesSectionTitle}>Services Offered</Text>
          <Text style={styles.servicesText}>{contractor.services_offered}</Text>
        </View>
      )}
    </View>
  );

  const renderReviewsTab = () => (
    <View style={styles.tabContent}>
      <View style={styles.reviewsSummary}>
        <View style={styles.ratingBig}>
          <Text style={styles.ratingBigValue}>{contractor.rating?.toFixed(1) || '5.0'}</Text>
          <View style={styles.starsRow}>
            {[1, 2, 3, 4, 5].map((star) => (
              <MaterialIcons
                key={star}
                name="star"
                size={20}
                color={star <= (contractor.rating || 5) ? COLORS.star : COLORS.border}
              />
            ))}
          </View>
          <Text style={styles.reviewsCountText}>
            Based on {contractor.reviews_count || 0} reviews
          </Text>
        </View>
      </View>

      <View style={styles.emptyState}>
        <Feather name="message-circle" size={48} color={COLORS.border} />
        <Text style={styles.emptyTitle}>No Reviews Yet</Text>
        <Text style={styles.emptySubtext}>
          Be the first to leave a review for this contractor.
        </Text>
      </View>
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

          {/* Send Message Button */}
          <TouchableOpacity
            style={styles.sendMessageBtn}
            activeOpacity={0.8}
            onPress={onSendMessage}
          >
            <Feather name="send" size={18} color={COLORS.primary} />
            <Text style={styles.sendMessageText}>Send Message</Text>
          </TouchableOpacity>
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
    left: SCREEN_WIDTH / 2 - 55,
    width: 110,
    height: 110,
    borderRadius: 55,
    backgroundColor: COLORS.surface,
    padding: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 8,
    elevation: 5,
  },
  profilePic: {
    width: '100%',
    height: '100%',
    borderRadius: 53,
  },
  profilePicPlaceholder: {
    width: '100%',
    height: '100%',
    borderRadius: 53,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: COLORS.primary,
  },
  profilePicText: {
    fontSize: 32,
    fontWeight: '700',
    color: COLORS.primary,
  },

  // Company Section
  companySection: {
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 16,
  },
  companyName: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.text,
    textAlign: 'center',
    marginBottom: 8,
  },
  ratingLocationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  ratingText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginLeft: 4,
  },
  dotSeparator: {
    fontSize: 14,
    color: COLORS.textMuted,
    marginHorizontal: 8,
  },
  locationText: {
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  descriptionPreview: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 16,
    paddingHorizontal: 10,
  },
  seeMoreText: {
    color: COLORS.primary,
    fontWeight: '500',
  },
  sendMessageBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    paddingHorizontal: 28,
    borderRadius: 25,
    borderWidth: 2,
    borderColor: COLORS.primary,
    marginBottom: 20,
  },
  sendMessageText: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.primary,
    marginLeft: 8,
  },

  // Tabs
  tabsContainer: {
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  tabsScrollContent: {
    paddingHorizontal: 16,
  },
  tab: {
    paddingVertical: 14,
    paddingHorizontal: 20,
    marginRight: 8,
    borderBottomWidth: 3,
    borderBottomColor: 'transparent',
  },
  tabActive: {
    borderBottomColor: COLORS.primary,
  },
  tabText: {
    fontSize: 15,
    fontWeight: '500',
    color: COLORS.textMuted,
  },
  tabTextActive: {
    color: COLORS.text,
    fontWeight: '600',
  },

  // Tab Content
  tabContent: {
    paddingHorizontal: 16,
    paddingTop: 20,
  },

  // Portfolio Tab
  portfolioList: {
    paddingTop: 12,
  },
  portfolioItem: {
    backgroundColor: COLORS.surface,
    marginBottom: 16,
    paddingHorizontal: 16,
  },
  portfolioHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 10,
  },
  portfolioAvatar: {
    marginRight: 10,
  },
  portfolioAvatarImage: {
    width: 40,
    height: 40,
    borderRadius: 20,
  },
  portfolioAvatarPlaceholder: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  portfolioAvatarText: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.primary,
  },
  portfolioInfo: {
    flex: 1,
  },
  portfolioCompany: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
  },
  portfolioUsername: {
    fontSize: 13,
    color: COLORS.textMuted,
  },
  portfolioMoreBtn: {
    padding: 8,
  },
  portfolioTitle: {
    fontSize: 15,
    fontWeight: '500',
    color: COLORS.text,
    marginBottom: 4,
  },
  portfolioMoreDetails: {
    fontSize: 14,
    color: COLORS.primary,
    marginBottom: 12,
  },
  portfolioImage: {
    width: '100%',
    height: 250,
    borderRadius: 12,
  },

  // Highlights Tab
  highlightsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  highlightCard: {
    width: '48%',
    backgroundColor: COLORS.background,
    borderRadius: 16,
    padding: 16,
    alignItems: 'center',
    marginBottom: 12,
  },
  highlightIcon: {
    width: 50,
    height: 50,
    borderRadius: 25,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
  },
  highlightValue: {
    fontSize: 22,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  highlightLabel: {
    fontSize: 12,
    color: COLORS.textMuted,
    textAlign: 'center',
  },
  servicesSection: {
    marginTop: 20,
    backgroundColor: COLORS.background,
    borderRadius: 16,
    padding: 16,
  },
  servicesSectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  servicesText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
  },

  // Reviews Tab
  reviewsSummary: {
    alignItems: 'center',
    paddingVertical: 24,
    backgroundColor: COLORS.background,
    borderRadius: 16,
    marginBottom: 20,
  },
  ratingBig: {
    alignItems: 'center',
  },
  ratingBigValue: {
    fontSize: 48,
    fontWeight: '700',
    color: COLORS.text,
  },
  starsRow: {
    flexDirection: 'row',
    marginVertical: 8,
  },
  reviewsCountText: {
    fontSize: 14,
    color: COLORS.textMuted,
  },

  // About Tab
  aboutSection: {
    marginBottom: 24,
  },
  aboutSectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
  },
  aboutText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 22,
  },
  contactItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  contactText: {
    fontSize: 14,
    color: COLORS.text,
    marginLeft: 12,
    flex: 1,
  },
  contactClickable: {
    color: COLORS.primary,
  },
  contactMessageBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.primary,
    paddingVertical: 12,
    paddingHorizontal: 20,
    borderRadius: 8,
    marginTop: 16,
    gap: 8,
  },
  contactMessageText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.surface,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  detailLabel: {
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  detailValue: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },

  // Empty State
  emptyState: {
    alignItems: 'center',
    paddingVertical: 40,
  },
  emptyTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginTop: 16,
    marginBottom: 8,
  },
  emptySubtext: {
    fontSize: 14,
    color: COLORS.textMuted,
    textAlign: 'center',
    paddingHorizontal: 40,
  },
});
