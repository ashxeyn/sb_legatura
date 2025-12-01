// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  Image,
  FlatList,
  Dimensions,
  ActivityIndicator,
  Alert,
  StatusBar,
  Platform,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import { contractors_service, Contractor as ContractorType } from '../../services/contractors_service';
import { api_config } from '../../config/api';

// Import profile screens
import PropertyOwnerProfile from '../owner/profile';
import ContractorProfile from '../contractor/profile';

// Import dashboard screens
import PropertyOwnerDashboard from '../owner/dashboard';
import ContractorDashboard from '../contractor/dashboard';

// Import messages screen
import MessagesScreen from './messages';

// Default cover photo
const defaultCoverPhoto = require('../../../assets/images/pictures/cp_default.jpg');

const { width } = Dimensions.get('window');

interface UserData {
  user_id?: number;
  username?: string;
  email?: string;
  profile_pic?: string;
  cover_photo?: string;
  user_type?: 'property_owner' | 'contractor' | 'both';
  // Contractor-specific fields
  company_name?: string;
  contractor_type?: string;
  years_of_experience?: number;
}

interface HomepageProps {
  userType?: 'property_owner' | 'contractor';
  userData?: UserData;
  onLogout?: () => void;
}

export default function HomepageScreen({ userType = 'property_owner', userData, onLogout }: HomepageProps) {
  const insets = useSafeAreaInsets();
  const [popularContractors, setPopularContractors] = useState<ContractorType[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('home');
  const [error, setError] = useState<string | null>(null);
  const [profileImageError, setProfileImageError] = useState(false);
  
  // Get status bar height (top inset)
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  // Handle logout - calls the parent callback
  const handleLogout = () => {
    if (onLogout) {
      onLogout();
    }
  };

  /**
   * Fetch active contractors from the backend API
   * This replaces the mock data with real data from the database
   * Uses the contractors_service which calls the backend's getActiveContractors() method
   */
  useEffect(() => {
    const fetchContractors = async () => {
      // Only fetch contractors for property owners (contractors see projects instead)
      if (userType !== 'property_owner') {
        setIsLoading(false);
        return;
      }

      try {
        setIsLoading(true);
        setError(null);

        // Fetch contractors from backend API
        // The backend endpoint should return data from getActiveContractors() method
        const response = await contractors_service.get_active_contractors();

        // API response structure: { success: true, data: { success: true, data: [...contractors] } }
        // The actual contractors array is nested inside response.data.data
        const contractorsData = response.data?.data || response.data;
        
        if (response.success && contractorsData && Array.isArray(contractorsData)) {
          // Transform backend contractor data to frontend format
          const transformedContractors = contractors_service.transform_contractors(contractorsData);
          setPopularContractors(transformedContractors);
        } else {
          // Handle API error response
          const errorMessage = response.message || 'Failed to load contractors';
          setError(errorMessage);
          console.warn('Failed to load contractors:', errorMessage);
        }
      } catch (err) {
        // Handle network or unexpected errors
        const errorMessage = err instanceof Error ? err.message : 'An unexpected error occurred';
        setError(errorMessage);
        console.error('Unexpected error fetching contractors:', err);
        
        Alert.alert(
          'Error',
          'Failed to load contractors. Please check your connection and try again.',
          [{ text: 'OK' }]
        );
      } finally {
        setIsLoading(false);
      }
    };

    fetchContractors();
  }, [userType]);

  /**
   * Generate initials from company name (matching backend logic)
   * Backend uses: strtoupper(substr($contractor->company_name, 0, 2))
   */
  const getCompanyInitials = (companyName: string): string => {
    return companyName.substring(0, 2).toUpperCase();
  };

  /**
   * Generate background color based on user_id or contractor_id (matching backend logic)
   * Backend uses: ($contractor->user_id ?? $contractor->contractor_id) % 8
   */
  const getColorForContractor = (contractorId: number, userId?: number): string => {
    const colors = ['#1877f2', '#42b883', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#3498db'];
    const index = (userId ?? contractorId) % 8;
    return colors[index];
  };

  /**
   * Render a single contractor card
   * Displays contractor information matching the provided design
   */
  const renderContractorCard = ({ item }: { item: ContractorType }) => {
    const hasCoverPhoto = item.cover_photo && !item.cover_photo.includes('placeholder');
    const coverPhotoUri = hasCoverPhoto 
      ? `${api_config.base_url}/storage/${item.cover_photo}`
      : null;
    
    // Generate initials for avatar fallback
    const initials = item.company_name
      ?.split(' ')
      .slice(0, 2)
      .map(word => word[0])
      .join('')
      .toUpperCase() || 'CO';
    
    return (
      <TouchableOpacity style={styles.contractorCard} activeOpacity={0.95}>
        {/* Header: Company Avatar + Info + Action Button */}
        <View style={styles.cardHeader}>
          {/* Company Avatar */}
          <View style={styles.companyAvatar}>
            <Text style={styles.avatarText}>{initials}</Text>
          </View>
          
          {/* Company Info */}
          <View style={styles.companyInfo}>
            <Text style={styles.cardCompanyName} numberOfLines={1}>
              {item.company_name}
            </Text>
            <Text style={styles.contractorTypeBadge} numberOfLines={1}>
              {item.contractor_type || 'General Contractor'}
            </Text>
          </View>
          
          {/* Follow/Contact Button */}
          <TouchableOpacity style={styles.contactButton} activeOpacity={0.7}>
            <Text style={styles.contactButtonText}>Contact</Text>
          </TouchableOpacity>
        </View>
        
        {/* Cover Photo */}
        <View style={styles.cardImageContainer}>
          <Image
            source={coverPhotoUri ? { uri: coverPhotoUri } : defaultCoverPhoto}
            style={styles.cardImage}
            resizeMode="cover"
          />
        </View>
        
        {/* Engagement Bar */}
        <View style={styles.engagementBar}>
          {/* Rating */}
          <View style={styles.engagementItem}>
            <MaterialIcons name="star" size={20} color="#EC7E00" />
            <Text style={styles.engagementValue}>{item.rating?.toFixed(1) || '5.0'}</Text>
            <Text style={styles.engagementLabel}>Rating</Text>
          </View>
          
          {/* Divider */}
          <View style={styles.engagementDivider} />
          
          {/* Reviews */}
          <View style={styles.engagementItem}>
            <MaterialIcons name="rate-review" size={20} color="#666666" />
            <Text style={styles.engagementValue}>{item.reviews_count || 128}</Text>
            <Text style={styles.engagementLabel}>Reviews</Text>
          </View>
          
          {/* Divider */}
          <View style={styles.engagementDivider} />
          
          {/* Projects */}
          <View style={styles.engagementItem}>
            <MaterialIcons name="work" size={20} color="#666666" />
            <Text style={styles.engagementValue}>{item.completed_projects || 0}</Text>
            <Text style={styles.engagementLabel}>Projects</Text>
          </View>
          
          {/* Divider */}
          <View style={styles.engagementDivider} />
          
          {/* Experience */}
          <View style={styles.engagementItem}>
            <MaterialIcons name="schedule" size={20} color="#666666" />
            <Text style={styles.engagementValue}>{item.years_of_experience || 0}y</Text>
            <Text style={styles.engagementLabel}>Exp</Text>
          </View>
        </View>
        
        {/* Footer: Location & Services */}
        <View style={styles.cardFooter}>
          <View style={styles.footerRow}>
            <Ionicons name="location-sharp" size={16} color="#EC7E00" />
            <Text style={styles.footerText} numberOfLines={1}>
              {item.location || 'Location not specified'}
            </Text>
          </View>
          {item.services_offered && (
            <View style={styles.footerRow}>
              <Ionicons name="construct" size={16} color="#666666" />
              <Text style={styles.footerTextSecondary} numberOfLines={1}>
                {item.services_offered}
              </Text>
            </View>
          )}
        </View>
      </TouchableOpacity>
    );
  };

  // Debug: Log userData to console
  useEffect(() => {
    console.log('Homepage userData:', userData);
    console.log('Profile pic:', userData?.profile_pic);
  }, [userData]);

  // Render the home content (contractors feed for property owners)
  const renderHomeContent = () => {
    // Build profile image URL
    const profileImageUrl = userData?.profile_pic 
      ? `${api_config.base_url}/storage/${userData.profile_pic}`
      : null;
    
    console.log('Profile image URL:', profileImageUrl);

    return (
    <ScrollView 
      style={styles.scrollView}
      showsVerticalScrollIndicator={false}
      contentContainerStyle={styles.scrollContent}
    >
      {/* User Profile and Project Input */}
      <View style={styles.profileSection}>
        {profileImageUrl && !profileImageError ? (
          <Image
            source={{ uri: profileImageUrl }}
            style={styles.profileImage}
            onError={(e) => {
              console.log('Image load error:', e.nativeEvent.error);
              setProfileImageError(true);
            }}
          />
        ) : (
          <View style={styles.profileImagePlaceholder}>
            <Ionicons name="person" size={28} color="#999999" />
          </View>
        )}
        <TouchableOpacity style={styles.projectInput}>
          <Text style={styles.projectInputText}>Post your project</Text>
        </TouchableOpacity>
      </View>

      {/* Popular Contractors Section */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Popular Contractors</Text>
        
        {/* Loading State */}
        {isLoading && (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#EC7E00" />
            <Text style={styles.loadingText}>Loading contractors...</Text>
          </View>
        )}

        {/* Error State */}
        {!isLoading && error && (
          <View style={styles.errorContainer}>
            <MaterialIcons name="error-outline" size={48} color="#E74C3C" />
            <Text style={styles.errorText}>{error}</Text>
            <TouchableOpacity 
              style={styles.retryButton}
              onPress={() => {
                // Retry fetching contractors
                setError(null);
                setIsLoading(true);
                contractors_service.get_active_contractors()
                  .then(response => {
                    const contractorsData = response.data?.data || response.data;
                    if (response.success && contractorsData && Array.isArray(contractorsData)) {
                      const transformedContractors = contractors_service.transform_contractors(contractorsData);
                      setPopularContractors(transformedContractors);
                    }
                  })
                  .finally(() => setIsLoading(false));
              }}
            >
              <Text style={styles.retryButtonText}>Retry</Text>
            </TouchableOpacity>
          </View>
        )}

        {/* Empty State */}
        {!isLoading && !error && popularContractors.length === 0 && (
          <View style={styles.emptyContainer}>
            <MaterialIcons name="business" size={48} color="#999999" />
            <Text style={styles.emptyText}>No contractors available at the moment</Text>
          </View>
        )}

        {/* Contractors List */}
        {!isLoading && !error && popularContractors.length > 0 && (
          <>
            {popularContractors.map((contractor, index) => (
              <View key={`contractor-${contractor.contractor_id || index}-${index}`}>
                {renderContractorCard({ item: contractor })}
              </View>
            ))}
          </>
        )}
      </View>
    </ScrollView>
  );
  };

  // Render profile based on user type
  const renderProfileContent = () => {
    // For contractors, show the contractor profile
    if (userType === 'contractor') {
      return (
        <ContractorProfile 
          onLogout={handleLogout}
          userData={{
            username: userData?.username,
            email: userData?.email,
            profile_pic: userData?.profile_pic 
              ? `${api_config.base_url}/storage/${userData.profile_pic}`
              : undefined,
            cover_photo: userData?.cover_photo
              ? `${api_config.base_url}/storage/${userData.cover_photo}`
              : undefined,
            user_type: userData?.user_type,
            company_name: userData?.company_name,
            contractor_type: userData?.contractor_type,
            years_of_experience: userData?.years_of_experience,
          }}
        />
      );
    }
    
    // For property owners (and default), show property owner profile
    return (
      <PropertyOwnerProfile 
        onLogout={handleLogout}
        userData={{
          username: userData?.username,
          email: userData?.email,
          profile_pic: userData?.profile_pic 
            ? `${api_config.base_url}/storage/${userData.profile_pic}`
            : undefined,
          cover_photo: userData?.cover_photo
            ? `${api_config.base_url}/storage/${userData.cover_photo}`
            : undefined,
          user_type: userData?.user_type,
        }}
      />
    );
  };

  // Render dashboard based on user type
  const renderDashboardContent = () => {
    // For contractors, show the contractor dashboard
    if (userType === 'contractor') {
      return (
        <ContractorDashboard 
          userData={{
            user_id: userData?.user_id,
            username: userData?.username,
            email: userData?.email,
            profile_pic: userData?.profile_pic 
              ? `http://192.168.254.131:3000/storage/${userData.profile_pic}`
              : undefined,
            company_name: userData?.company_name,
            contractor_type: userData?.contractor_type,
            years_of_experience: userData?.years_of_experience,
          }}
        />
      );
    }
    
    // For property owners (and default), show property owner dashboard
    return (
      <PropertyOwnerDashboard 
        userData={{
          user_id: userData?.user_id,
          username: userData?.username,
          email: userData?.email,
          profile_pic: userData?.profile_pic 
            ? `http://192.168.254.131:3000/storage/${userData.profile_pic}`
            : undefined,
        }}
      />
    );
  };

  // Render messages screen
  const renderMessagesContent = () => (
    <MessagesScreen 
      userData={{
        user_id: userData?.user_id,
        username: userData?.username,
        email: userData?.email,
        profile_pic: userData?.profile_pic 
          ? `http://192.168.254.131:3000/storage/${userData.profile_pic}`
          : undefined,
        user_type: userData?.user_type,
      }}
    />
  );

  // Render content based on active tab
  const renderContent = () => {
    switch (activeTab) {
      case 'home':
        return renderHomeContent();
      case 'dashboard':
        return renderDashboardContent();
      case 'messages':
        return renderMessagesContent();
      case 'profile':
        return renderProfileContent();
      default:
        return renderHomeContent();
    }
  };

  return (
    <SafeAreaView style={[styles.container, { paddingTop: statusBarHeight }]}>
      <StatusBar hidden={true} />
      {/* Header - only show on non-profile tabs */}
      {activeTab !== 'profile' && (
        <View style={styles.header}>
          <Text style={styles.logoText}>LEGATURA</Text>
          <View style={styles.headerIcons}>
            <TouchableOpacity style={styles.iconButton}>
              <MaterialIcons name="search" size={24} color="#333333" />
            </TouchableOpacity>
            <TouchableOpacity style={styles.iconButton}>
              <MaterialIcons name="notifications" size={24} color="#333333" />
              <View style={styles.notificationBadge}>
                <Text style={styles.badgeText}>3</Text>
              </View>
            </TouchableOpacity>
          </View>
        </View>
      )}

      {/* Main Content */}
      <View style={styles.mainContent}>
        {renderContent()}
      </View>

      {/* Bottom Navigation Bar */}
      <View style={styles.bottomNav}>
        <TouchableOpacity 
          style={styles.navItem}
          onPress={() => setActiveTab('home')}
        >
          <MaterialIcons 
            name="home" 
            size={26} 
            color={activeTab === 'home' ? '#EC7E00' : '#8E8E93'} 
          />
          <Text style={[styles.navText, activeTab === 'home' && styles.navTextActive]}>
            Home
          </Text>
        </TouchableOpacity>

        <TouchableOpacity 
          style={styles.navItem}
          onPress={() => setActiveTab('dashboard')}
        >
          <Ionicons 
            name={activeTab === 'dashboard' ? 'grid' : 'grid-outline'} 
            size={24} 
            color={activeTab === 'dashboard' ? '#EC7E00' : '#8E8E93'} 
          />
          <Text style={[styles.navText, activeTab === 'dashboard' && styles.navTextActive]}>
            Dashboard
          </Text>
        </TouchableOpacity>

        <TouchableOpacity 
          style={styles.navItem}
          onPress={() => setActiveTab('messages')}
        >
          <Ionicons 
            name={activeTab === 'messages' ? 'chatbubble' : 'chatbubble-outline'} 
            size={24} 
            color={activeTab === 'messages' ? '#EC7E00' : '#8E8E93'} 
          />
          <Text style={[styles.navText, activeTab === 'messages' && styles.navTextActive]}>
            Messages
          </Text>
        </TouchableOpacity>

        <TouchableOpacity 
          style={styles.navItem}
          onPress={() => setActiveTab('profile')}
        >
          <Ionicons 
            name={activeTab === 'profile' ? 'person' : 'person-outline'} 
            size={24} 
            color={activeTab === 'profile' ? '#EC7E00' : '#8E8E93'} 
          />
          <Text style={[styles.navText, activeTab === 'profile' && styles.navTextActive]}>
            Profile
          </Text>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FEFEFE',
  },
  mainContent: {
    flex: 1,
  },
  placeholderContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F5F5F5',
  },
  placeholderTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333333',
    marginTop: 16,
  },
  placeholderText: {
    fontSize: 16,
    color: '#999999',
    marginTop: 8,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 16,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E5E5',
  },
  logoText: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#EC7E00',
    letterSpacing: 1,
  },
  headerIcons: {
    flexDirection: 'row',
    gap: 16,
  },
  iconButton: {
    position: 'relative',
    padding: 4,
  },
  notificationBadge: {
    position: 'absolute',
    top: 0,
    right: 0,
    backgroundColor: '#FF0000',
    borderRadius: 10,
    width: 18,
    height: 18,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#FFFFFF',
  },
  badgeText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: 'bold',
  },
  scrollView: {
    flex: 1,
    backgroundColor: '#F0F0F0',
  },
  scrollContent: {
    paddingBottom: 20,
  },
  profileSection: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#EEEEEE',
  },
  profileImage: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#E5E5E5',
  },
  profileImagePlaceholder: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#E5E5E5',
    justifyContent: 'center',
    alignItems: 'center',
  },
  projectInput: {
    flex: 1,
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E5E5',
    borderRadius: 25,
    paddingHorizontal: 20,
    paddingVertical: 12,
  },
  projectInputText: {
    fontSize: 16,
    color: '#999999',
  },
  section: {
    marginTop: 0,
    paddingHorizontal: 0,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1A1A1A',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  // Modern Full-Width Contractor Card (LinkedIn/Indeed inspired)
  contractorCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 0,
    marginVertical: 0,
    marginHorizontal: 0,
    borderBottomWidth: 8,
    borderBottomColor: '#F0F0F0',
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  companyAvatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: '#EC7E00',
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  companyInfo: {
    flex: 1,
    marginLeft: 12,
  },
  cardCompanyName: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1A1A1A',
    marginBottom: 2,
  },
  contractorTypeBadge: {
    fontSize: 13,
    color: '#666666',
  },
  contactButton: {
    backgroundColor: '#EC7E00',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
  },
  contactButtonText: {
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '600',
  },
  cardImageContainer: {
    width: '100%',
    height: 220,
    backgroundColor: '#F5F5F5',
  },
  cardImage: {
    width: '100%',
    height: '100%',
  },
  engagementBar: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 8,
    backgroundColor: '#FAFAFA',
    borderTopWidth: 1,
    borderBottomWidth: 1,
    borderColor: '#EEEEEE',
  },
  engagementItem: {
    alignItems: 'center',
    flex: 1,
  },
  engagementValue: {
    fontSize: 15,
    fontWeight: '700',
    color: '#1A1A1A',
    marginTop: 4,
  },
  engagementLabel: {
    fontSize: 11,
    color: '#888888',
    marginTop: 2,
  },
  engagementDivider: {
    width: 1,
    height: 32,
    backgroundColor: '#E0E0E0',
  },
  cardFooter: {
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  footerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 6,
  },
  footerText: {
    fontSize: 14,
    color: '#1A1A1A',
    marginLeft: 8,
    flex: 1,
  },
  footerTextSecondary: {
    fontSize: 13,
    color: '#666666',
    marginLeft: 8,
    flex: 1,
  },
  // Legacy styles (kept for compatibility)
  cardContent: {
    padding: 16,
  },
  cardLocationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
    gap: 4,
  },
  cardLocation: {
    fontSize: 15,
    color: '#666666',
    flex: 1,
  },
  cardRatingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
    gap: 6,
  },
  ratingText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#1A1A1A',
  },
  starsContainer: {
    flexDirection: 'row',
    gap: 2,
  },
  reviewsText: {
    fontSize: 14,
    color: '#EC7E00',
    marginLeft: 4,
  },
  cardDivider: {
    height: 1,
    backgroundColor: '#E5E5E5',
    marginVertical: 12,
  },
  cardBottomRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  cardServicesContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  cardServicesText: {
    fontSize: 14,
    color: '#666666',
  },
  cardTypeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  cardType: {
    fontSize: 14,
    color: '#EC7E00',
    fontWeight: '500',
  },
  cardServices: {
    fontSize: 14,
    color: '#666',
    marginBottom: 2,
    flexShrink: 1,
  },
  bottomNav: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    alignItems: 'center',
    paddingTop: 8,
    paddingBottom: 24,
    paddingHorizontal: 16,
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 28,
    borderTopRightRadius: 28,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -2 },
    shadowOpacity: 0.06,
    shadowRadius: 16,
    elevation: 12,
  },
  navItem: {
    alignItems: 'center',
    justifyContent: 'center',
    flex: 1,
    paddingVertical: 4,
  },
  navText: {
    fontSize: 11,
    color: '#8E8E93',
    marginTop: 4,
    fontWeight: '500',
  },
  navTextActive: {
    color: '#EC7E00',
    fontWeight: '600',
  },
  loadingContainer: {
    paddingVertical: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: '#666666',
  },
  errorContainer: {
    paddingVertical: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  errorText: {
    marginTop: 12,
    fontSize: 14,
    color: '#E74C3C',
    textAlign: 'center',
    paddingHorizontal: 20,
  },
  retryButton: {
    marginTop: 16,
    paddingHorizontal: 24,
    paddingVertical: 12,
    backgroundColor: '#EC7E00',
    borderRadius: 8,
  },
  retryButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  emptyContainer: {
    paddingVertical: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyText: {
    marginTop: 12,
    fontSize: 14,
    color: '#999999',
    textAlign: 'center',
  },
});
