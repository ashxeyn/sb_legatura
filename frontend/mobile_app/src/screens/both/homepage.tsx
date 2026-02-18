// @ts-nocheck
import React, { useState, useEffect, useMemo, useCallback } from 'react';
import {
  View,
  Text,
  Image,
  TouchableOpacity,
  Dimensions,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  ActivityIndicator,
  Alert,
  Platform,
  StatusBar,
  AppState,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import Ionicons from 'react-native-vector-icons/Ionicons';
import Feather from 'react-native-vector-icons/Feather';
import ImageFallback from '../../components/ImageFallbackFixed';
import { projects_service, ContractorType as ContractorTypeOption } from '../../services/projects_service';
import { api_config } from '../../config/api';
import { contractors_service } from '../../services/contractors_service';
import { role_service } from '../../services/role_service';
import { useContractorAuth } from '../../hooks/useContractorAuth';

// Helper to build full storage URL for profile/cover images
const getStorageUrl = (filePath?: string, defaultSubfolder = 'profiles') => {
  if (!filePath) return undefined;
  // If it's already a full URL, return as-is
  if (filePath.startsWith('http://') || filePath.startsWith('https://')) return filePath;
  // If it already contains /storage, prepend base_url if missing
  if (filePath.includes('/storage/')) {
    return filePath.startsWith('/') ? `${api_config.base_url}${filePath}` : `${api_config.base_url}/${filePath}`;
  }
  // If path already contains the subfolder, use it directly
  if (filePath.startsWith(`${defaultSubfolder}/`) || filePath.includes(`/${defaultSubfolder}/`)) {
    return `${api_config.base_url}/storage/${filePath}`;
  }
  // Otherwise assume file lives under storage/<defaultSubfolder>/
  return `${api_config.base_url}/storage/${defaultSubfolder}/${filePath}`;
};

// Import profile screens
import PropertyOwnerProfile from '../owner/profile';
import ContractorProfile from '../contractor/profile';
import CheckProfile from './checkProfile';

// Import dashboard screens
import PropertyOwnerDashboard from '../owner/dashboard';
import ContractorDashboard from '../contractor/dashboard';

// Import messages screen
import MessagesScreen from './messages';

// Import create project screen
import CreateProjectScreen from '../owner/createProject';

// Import search screen
import SearchScreen from './searchScreen';

// Import place bid screen
import PlaceBid from '../contractor/placeBid';

// Import project post detail screen
import ProjectPostDetail from './projectPostDetail';

// Import notifications screen
import Notifications from './notifications';
import { notifications_service } from '../../services/notifications_service';

// Default cover photo
const defaultCoverPhoto = require('../../../assets/images/pictures/cp_default.jpg');
const defaultContractorAvatar = require('../../../assets/images/pictures/contractor_default.png');
const defaultOwnerAvatar = require('../../../assets/images/pictures/property_owner_default.png');
const watermarkImage = require('../../../assets/images/pictures/legatura_watermark.png');

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

// Project interface for contractor feed
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
  type_id?: number;
  type_name: string;
  project_status: string;
  project_post_status: string;
  bidding_deadline?: string;
  created_at: string;
  owner_name?: string;
  owner_profile_pic?: string;
  owner_id?: number;
  owner_user_id?: number;
  bids_count?: number;
  files?: Array<string | { file_id?: number; file_type?: string; file_path?: string }>;
}

interface HomepageProps {
  userType?: 'property_owner' | 'contractor';
  userData?: UserData;
  onLogout?: () => void;
  onViewProfile?: () => void;
  onEditProfile?: () => void;
  onOpenHelp?: () => void;
  onOpenSwitchRole?: () => void;
}

export default function HomepageScreen({ userType = 'property_owner', userData, onLogout, onViewProfile, onEditProfile, onOpenHelp, onOpenSwitchRole }: HomepageProps) {
  const insets = useSafeAreaInsets();
  const [popularContractors, setPopularContractors] = useState<ContractorType[]>([]);
  const [availableProjects, setAvailableProjects] = useState<Project[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('home');
  const [error, setError] = useState<string | null>(null);
  const [profileImageError, setProfileImageError] = useState(false);
  const [isFullScreenMode, setIsFullScreenMode] = useState(false);
  const [currentRole, setCurrentRole] = useState<'contractor' | 'owner' | null>(null);

  // Pagination state
  const [contractorsPage, setContractorsPage] = useState(1);
  const [projectsPage, setProjectsPage] = useState(1);
  const [loadingMore, setLoadingMore] = useState(false);
  const [hasMoreContractors, setHasMoreContractors] = useState(true);
  const [hasMoreProjects, setHasMoreProjects] = useState(true);
  const PER_PAGE = 15;

  // Create project screen state
  const [showCreateProject, setShowCreateProject] = useState(false);
  const [contractorTypes, setContractorTypes] = useState<ContractorTypeOption[]>([]);
  const [isSubmittingProject, setIsSubmittingProject] = useState(false);

  // Search screen state
  const [showSearchScreen, setShowSearchScreen] = useState(false);

  // View contractor profile state
  const [selectedContractor, setSelectedContractor] = useState<ContractorType | null>(null);

  // View project state (for contractors viewing projects)
  const [selectedProject, setSelectedProject] = useState<Project | null>(null);

  // Place bid screen state
  const [showPlaceBid, setShowPlaceBid] = useState(false);
  const [bidProject, setBidProject] = useState<Project | null>(null);

  // Notifications screen state
  const [showNotifications, setShowNotifications] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);

  // Poll unread notification count every 30 seconds
  useEffect(() => {
    const fetchUnread = async () => {
      try {
        const res = await notifications_service.get_unread_count();
        if (res.success && res.data) {
          setUnreadCount(res.data.unread_count);
        }
      } catch (_) { /* silent */ }
    };
    fetchUnread();
    const interval = setInterval(fetchUnread, 30000);
    return () => clearInterval(interval);
  }, []);

  // Refresh unread count when returning from notifications screen
  useEffect(() => {
    if (!showNotifications) {
      notifications_service.get_unread_count().then(res => {
        if (res.success && res.data) setUnreadCount(res.data.unread_count);
      }).catch(() => {});
    }
  }, [showNotifications]);

  // Contractor authorization - for role-based feature access
  // canBid: only owner/representative can bid
  // canManageMilestones: only owner/representative can manage milestones
  const { canBid, canManageMilestones, role: contractorRole, isLoading: authLoading } = useContractorAuth();

  // Get status bar height (top inset)
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  // Resolve effective user type: prefer explicit userData.user_type when available
  // IMPORTANT: Staff users should be treated as contractors
  const effectiveUserType = useMemo(() => {
    if (currentRole === 'owner') return 'property_owner';
    if (currentRole === 'contractor') return 'contractor';
    
    const rawType = userData?.user_type || userType;
    // Staff users operate in contractor context
    if (rawType === 'staff' || rawType === 'contractor') {
      return 'contractor';
    }
    return rawType === 'property_owner' || rawType === 'both' ? 'property_owner' : userType;
  }, [currentRole, userData?.user_type, userType]);

  // Refresh current role from API on mount and when app comes to foreground
  useEffect(() => {
    let isMounted = true;

    const fetchCurrentRole = async () => {
      try {
        const res = await role_service.get_current_role();
        if (res?.success) {
          const roleVal = (res as any).current_role || (res as any).data?.current_role || (res as any).user_type;
          const v = String(roleVal || '').toLowerCase();
          const role = v.includes('owner') ? 'owner' : v.includes('contractor') ? 'contractor' : null;
          if (isMounted) setCurrentRole(role as any);
        }
      } catch (e) {
        // Silent failure; keep existing role
      }
    };

    fetchCurrentRole();

    const sub = AppState.addEventListener('change', (state) => {
      if (state === 'active') fetchCurrentRole();
    });

    return () => {
      isMounted = false;
      sub.remove();
    };
  }, []);

  // Handle logout - calls the parent callback
  const handleLogout = () => {
    if (onLogout) {
      onLogout();
    }
  };

  // Fetch contractor types for project creation form
  useEffect(() => {
    const fetchContractorTypes = async () => {
      try {
        const response = await projects_service.get_contractor_types();
        console.log('Contractor types response:', response);
        // Handle nested response structure: response.data contains { success, data, message }
        // The actual types are in response.data.data or response.data directly
        if (response.success && response.data) {
          const types = response.data.data || response.data;
          if (Array.isArray(types)) {
            // Sort so "Others" appears last
            const sortedTypes = types.sort((a: any, b: any) => {
              if (a.type_name?.toLowerCase() === 'others') return 1;
              if (b.type_name?.toLowerCase() === 'others') return -1;
              return a.type_name?.localeCompare(b.type_name) || 0;
            });
            setContractorTypes(sortedTypes);
          }
        }
      } catch (error) {
        console.error('Failed to fetch contractor types:', error);
      }
    };
    fetchContractorTypes();
  }, []);

  // Handle project submission
  const handleCreateProject = async (projectData: any) => {
    if (!userData?.user_id) {
      Alert.alert('Error', 'You must be logged in to create a project.');
      return;
    }

    setIsSubmittingProject(true);
    try {
      const response = await projects_service.create_project(projectData, userData.user_id);

      if (response.success) {
        Alert.alert('Success', 'Your project has been submitted for review!', [
          { text: 'OK', onPress: () => setShowCreateProject(false) }
        ]);
      } else {
        Alert.alert('Error', response.message || 'Failed to create project. Please try again.');
      }
    } catch (error) {
      Alert.alert('Error', 'An unexpected error occurred. Please try again.');
    } finally {
      setIsSubmittingProject(false);
    }
  };

  /**
   * Fetch active contractors from the backend API with pagination
   * This replaces the mock data with real data from the database
   * Uses the contractors_service which calls the backend's getActiveContractors() method
   */
  useEffect(() => {
    const fetchContractors = async () => {
      // Only fetch contractors for property owners (contractors see projects instead)
      if (effectiveUserType !== 'property_owner') {
        setIsLoading(false);
        return;
      }

      try {
        setIsLoading(true);
        setError(null);
        setContractorsPage(1);
        setHasMoreContractors(true);

        // Fetch first page of contractors from backend API
        const response = await contractors_service.get_active_contractors(undefined, 1, PER_PAGE);

        // API response structure: { success: true, data: [...contractors], pagination: {...} }
        const contractorsData = response.data?.data || response.data;

        if (response.success && contractorsData && Array.isArray(contractorsData)) {
          // Transform backend contractor data to frontend format
          const transformedContractors = contractors_service.transform_contractors(contractorsData);
          setPopularContractors(transformedContractors);
          
          // Update pagination state
          if (response.pagination) {
            setHasMoreContractors(response.pagination.has_more);
          }
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
  }, [effectiveUserType]);

  /**
   * Fetch available projects for contractors with pagination
   * This fetches approved projects that are open for bidding
   */
  useEffect(() => {
    const fetchProjects = async () => {
      // Only fetch projects for contractors
      if (effectiveUserType !== 'contractor') {
        return;
      }

      try {
        setIsLoading(true);
        setError(null);
        setProjectsPage(1);
        setHasMoreProjects(true);

        const response = await projects_service.get_approved_projects(1, PER_PAGE);
        const projectsData = response.data?.data || response.data;

        if (response.success && projectsData && Array.isArray(projectsData)) {
          setAvailableProjects(projectsData);
          
          // Update pagination state
          if (response.pagination) {
            setHasMoreProjects(response.pagination.has_more);
          }
        } else {
          const errorMessage = response.message || 'Failed to load projects';
          setError(errorMessage);
          console.warn('Failed to load projects:', errorMessage);
        }
      } catch (err) {
        const errorMessage = err instanceof Error ? err.message : 'An unexpected error occurred';
        setError(errorMessage);
        console.error('Unexpected error fetching projects:', err);

        Alert.alert(
          'Error',
          'Failed to load projects. Please check your connection and try again.',
          [{ text: 'OK' }]
        );
      } finally {
        setIsLoading(false);
      }
    };

    fetchProjects();
  }, [effectiveUserType]);

  // Function to refresh available projects (for contractor view)
  const refreshProjects = useCallback(async () => {
    if (userType !== 'contractor') return;

    try {
      const response = await projects_service.get_approved_projects(1, PER_PAGE);
      const projectsData = response.data?.data || response.data;
      if (response.success && projectsData && Array.isArray(projectsData)) {
        setAvailableProjects(projectsData);
        setProjectsPage(1);
        if (response.pagination) {
          setHasMoreProjects(response.pagination.has_more);
        }
      }
    } catch (err) {
      console.error('Error refreshing projects:', err);
    }
  }, [userType]);

  /**
   * Load more contractors (infinite scroll)
   */
  const loadMoreContractors = useCallback(async () => {
    if (loadingMore || !hasMoreContractors || effectiveUserType !== 'property_owner') {
      console.log('Load more contractors skipped:', { loadingMore, hasMoreContractors, effectiveUserType });
      return;
    }

    try {
      setLoadingMore(true);
      const nextPage = contractorsPage + 1;
      console.log('Loading more contractors - page:', nextPage);
      
      const response = await contractors_service.get_active_contractors(undefined, nextPage, PER_PAGE);
      const contractorsData = response.data?.data || response.data;

      if (response.success && contractorsData && Array.isArray(contractorsData)) {
        const transformedContractors = contractors_service.transform_contractors(contractorsData);
        console.log(`Loaded ${transformedContractors.length} more contractors`);
        setPopularContractors(prev => [...prev, ...transformedContractors]);
        setContractorsPage(nextPage);
        
        if (response.pagination) {
          setHasMoreContractors(response.pagination.has_more);
          console.log('Has more contractors:', response.pagination.has_more);
        }
      }
    } catch (err) {
      console.error('Error loading more contractors:', err);
    } finally {
      setLoadingMore(false);
    }
  }, [loadingMore, hasMoreContractors, contractorsPage, effectiveUserType]);

  /**
   * Load more projects (infinite scroll)
   */
  const loadMoreProjects = useCallback(async () => {
    if (loadingMore || !hasMoreProjects || effectiveUserType !== 'contractor') {
      console.log('Load more projects skipped:', { loadingMore, hasMoreProjects, effectiveUserType });
      return;
    }

    try {
      setLoadingMore(true);
      const nextPage = projectsPage + 1;
      console.log('Loading more projects - page:', nextPage);
      
      const response = await projects_service.get_approved_projects(nextPage, PER_PAGE);
      const projectsData = response.data?.data || response.data;

      if (response.success && projectsData && Array.isArray(projectsData)) {
        console.log(`Loaded ${projectsData.length} more projects`);
        setAvailableProjects(prev => [...prev, ...projectsData]);
        setProjectsPage(nextPage);
        
        if (response.pagination) {
          setHasMoreProjects(response.pagination.has_more);
          console.log('Has more projects:', response.pagination.has_more);
        }
      }
    } catch (err) {
      console.error('Error loading more projects:', err);
    } finally {
      setLoadingMore(false);
    }
  }, [loadingMore, hasMoreProjects, projectsPage, effectiveUserType]);

  /**
   * Handle scroll event to detect when user reaches the end
   * Using onMomentumScrollEnd and onScrollEndDrag for better detection
   */
  const handleScrollEnd = useCallback((event: any, loadMoreFn: () => void) => {
    const { layoutMeasurement, contentOffset, contentSize } = event.nativeEvent;
    const paddingToBottom = 100; // Increased threshold for earlier loading
    const isCloseToBottom = layoutMeasurement.height + contentOffset.y >= contentSize.height - paddingToBottom;
    
    console.log('Scroll end detected:', {
      layoutHeight: layoutMeasurement.height,
      scrollY: contentOffset.y,
      contentHeight: contentSize.height,
      isCloseToBottom,
    });
    
    if (isCloseToBottom) {
      console.log('Triggering load more...');
      loadMoreFn();
    }
  }, []);

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
   * Render a single contractor card (matching project card style)
   */
  const renderContractorCard = ({ item }: { item: ContractorType }) => {
    const hasCoverPhoto = item.cover_photo && !item.cover_photo.includes('placeholder');
    const coverPhotoUri = hasCoverPhoto
      ? `${api_config.base_url}/storage/${item.cover_photo}`
      : null;

    const logoUri = item.logo_url
      ? (item.logo_url.startsWith('http') ? item.logo_url : `${api_config.base_url}/storage/${item.logo_url}`)
      : null;

    // Generate initials for avatar fallback
    const initials = item.company_name
      ?.split(' ')
      .slice(0, 2)
      .map(word => word[0])
      .join('')
      .toUpperCase() || 'CO';

    return (
      <TouchableOpacity
        style={styles.contractorCard}
        activeOpacity={0.7}
        onPress={() => setSelectedContractor(item)}
      >
        {/* Cover photo + avatar + info (avatar overlaps cover) */}
        <ImageFallback
          uri={coverPhotoUri || undefined}
          defaultImage={defaultCoverPhoto}
          style={[styles.contractorCover, { width }]}
          resizeMode="cover"
        />

        {/* Overlapping badge below the cover, positioned to the right */}
        <View style={styles.badgeOverlap}>
          <View style={styles.contractorTypeBadgeContainer}>
            <Text style={styles.contractorTypeBadgeText}>{item.contractor_type || 'General'}</Text>
          </View>
        </View>

        <View style={styles.contractorHeaderNew}>
          <View style={styles.leftColumn}>
            <View style={styles.contractorAvatarWrapper}>
              <ImageFallback
                uri={logoUri || undefined}
                defaultImage={defaultContractorAvatar}
                style={styles.contractorAvatarImg}
                resizeMode="cover"
              />
            </View>
          </View>

          <View style={styles.rightColumn} />
        </View>

        <View style={styles.contractorInfoBlock}>
          <Text style={styles.contractorName} numberOfLines={2}>{item.company_name}</Text>
          <Text style={styles.contractorSubtitle}>{item.years_of_experience || 0} years experience</Text>
        </View>

        {/* Description removed per request */}

        {/* Details */}
        <View style={styles.contractorDetailsContainer}>
          <View style={styles.detailRow}>
            <MaterialIcons name="location-on" size={16} color="#666666" />
            <Text style={styles.detailText}>{item.location || 'Location not specified'}</Text>
          </View>
          <View style={styles.detailRow}>
            <MaterialIcons name="star" size={16} color="#EC7E00" />
            <Text style={styles.detailText}>
              {item.rating?.toFixed(1) || '5.0'} rating • {item.reviews_count || 0} reviews
            </Text>
          </View>
          <View style={styles.detailRow}>
            <MaterialIcons name="work" size={16} color="#666666" />
            <Text style={styles.detailText}>{item.completed_projects || 0} projects completed</Text>
          </View>
          {/* badge moved to header right side */}
        </View>

        {/* Footer: Contact Button (hidden for property owners) */}
        {effectiveUserType !== 'property_owner' && (
          <View style={styles.contractorCardFooter}>
            <TouchableOpacity style={styles.contactContractorButton} activeOpacity={0.8}>
              <MaterialIcons name="mail" size={18} color="#FFFFFF" />
              <Text style={styles.contactContractorButtonText}>Contact</Text>
            </TouchableOpacity>
          </View>
        )}
      </TouchableOpacity>
    );
  };

  /**
   * Format currency for display
   */
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(amount);
  };

  /**
   * Get days remaining until bidding deadline
   */
  const getDaysRemaining = (deadline: string) => {
    const now = new Date();
    const deadlineDate = new Date(deadline);
    const diff = Math.ceil((deadlineDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    return diff;
  };

  /**
   * Classify a file as important (protected) based on its type/path.
   * Important documents = building permit, land title — never shown in collapsed card.
   */
  const isImportantDocument = (fileType: string, rawPath: string): boolean => {
    const type = (fileType || '').toLowerCase();
    const path = (rawPath || '').toLowerCase();
    // file_type hint from backend
    if (/building.?permit|title_of_land|title-of-land|land.?title/i.test(type)) return true;
    if (type === 'building permit' || type === 'title') return true;
    // path-based detection
    const normalized = path.replace(/[^a-z0-9]+/g, ' ');
    const tokens = normalized.split(/\s+/).filter(Boolean);
    const has = (w: string) => tokens.includes(w);
    if (has('building') && has('permit')) return true;
    if (has('title') && has('land')) return true;
    if (/(building_permit|building-permit|title_of_land|title-of-land)/.test(path)) return true;
    return false;
  };

  /**
   * Render project images in a Facebook-style collage.
   * RULES:
   *  - Only optional documents (blueprints, desired design, reference/others) are shown
   *    in the collapsed/default card view.
   *  - Important documents (building permit, land title) are NEVER shown here.
   *  - Layout: 1 → full-width, 2-3 → grid, 4+ → 2×2 grid with +N overlay.
   */
  const renderProjectImages = (files: Array<string | { file_id?: number; file_type?: string; file_path?: string }>) => {
    if (!files || files.length === 0) return null;

    // Check if file is an image by extension or by being from project_files
    const isImage = (filePath: string, fileType?: string) => {
      if (!filePath) return false;
      // Files from project_files table are always images (form only accepts images)
      if (fileType && ['building permit', 'title', 'blueprint', 'desired design', 'others'].includes(fileType.toLowerCase())) {
        return true;
      }
      if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
        return /\.(jpg|jpeg|png|gif|webp|bmp)(\?|$)/i.test(filePath);
      }
      const ext = filePath.toLowerCase().split('.').pop() || '';
      return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(ext);
    };

    // Parse every file entry
    const displayFiles = files.map((f) => {
      if (f && typeof f === 'object') {
        const path = (f.file_path || (f as any).file || (f as any).url || '').toString();
        const type = (f.file_type || (f as any).type || '').toString();
        const url = path.startsWith('http') ? path : `${api_config.base_url}/storage/${path}`;
        return { raw: path, url, isImage: isImage(path, type), fileType: type };
      }
      const raw = String(f);
      return {
        raw,
        url: raw.startsWith('http') ? raw : `${api_config.base_url}/storage/${raw}`,
        isImage: isImage(raw),
        fileType: '',
      };
    });

    // Debug: log what files we received and how they are classified
    console.log('[renderProjectImages] Total files received:', files.length);
    console.log('[renderProjectImages] Parsed displayFiles:', displayFiles.map(d => ({ raw: d.raw, fileType: d.fileType, isImage: d.isImage, isImportant: isImportantDocument(d.fileType, d.raw) })));

    // ── Strict filtering: EXCLUDE important/protected documents ──
    const optionalFiles = displayFiles.filter(
      (d) => !isImportantDocument(d.fileType, d.raw) && d.isImage
    );

    console.log('[renderProjectImages] Optional files after filter:', optionalFiles.length, optionalFiles.map(d => d.fileType || d.raw));

    // If there are no optional images to show, render nothing in the card
    if (optionalFiles.length === 0) return null;

    // Collage sizing
    const H_PADDING = 16;
    const GAP = 2;
    const usableWidth = width - H_PADDING * 2;
    const halfSize = Math.floor((usableWidth - GAP) / 2);
    const largeWidth = Math.floor(usableWidth * 0.66);
    const singleHeight = Math.floor(usableWidth * 0.56);

    // 1 image → full-width
    if (optionalFiles.length === 1) {
      const f = optionalFiles[0];
      return (
        <View style={[styles.imageCollageContainer, { paddingHorizontal: H_PADDING }]}>
          <Image source={{ uri: f.url }} style={{ width: usableWidth, height: singleHeight, borderRadius: 8 }} resizeMode="cover" />
        </View>
      );
    }

    // 2 images → side-by-side row
    if (optionalFiles.length === 2) {
      return (
        <View style={[styles.imageCollageContainer, { paddingHorizontal: H_PADDING }]}>
          <View style={{ flexDirection: 'row' }}>
            {optionalFiles.map((f, i) => (
              <View key={i} style={{ flex: 1, height: halfSize, borderRadius: 8, overflow: 'hidden', marginRight: i === 0 ? GAP : 0 }}>
                <Image source={{ uri: f.url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
              </View>
            ))}
          </View>
        </View>
      );
    }

    // 3 images → large left, two stacked right
    if (optionalFiles.length === 3) {
      return (
        <View style={[styles.imageCollageContainer, { paddingHorizontal: H_PADDING }]}>
          <View style={{ flexDirection: 'row' }}>
            <View style={{ flex: 2, height: largeWidth, marginRight: GAP, borderRadius: 8, overflow: 'hidden' }}>
              <Image source={{ uri: optionalFiles[0].url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
            </View>
            <View style={{ flex: 1, height: largeWidth }}>
              {optionalFiles.slice(1).map((f, i) => (
                <View key={i} style={{ width: '100%', height: (largeWidth - GAP) / 2, marginBottom: i === 0 ? GAP : 0, borderRadius: 8, overflow: 'hidden' }}>
                  <Image source={{ uri: f.url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
                </View>
              ))}
            </View>
          </View>
        </View>
      );
    }

    // 4+ images → 2×2 grid with +N overlay on the 4th tile
    const grid = optionalFiles.slice(0, 4);
    const extra = optionalFiles.length - 4;
    return (
      <View style={[styles.imageCollageContainer, { paddingHorizontal: H_PADDING }]}>
        <View style={{ flexDirection: 'row', flexWrap: 'wrap' }}>
          {grid.map((f, i) => (
            <View key={i} style={{ width: '50%', paddingRight: i % 2 === 0 ? GAP : 0, paddingTop: i >= 2 ? GAP : 0 }}>
              <View style={{ width: '100%', height: halfSize, borderRadius: 8, overflow: 'hidden' }}>
                <Image source={{ uri: f.url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
                {i === 3 && extra > 0 && (
                  <View style={styles.imageOverlay}>
                    <Text style={styles.imageOverlayText}>+{extra}</Text>
                  </View>
                )}
              </View>
            </View>
          ))}
        </View>
      </View>
    );
  };

  /**
   * Render a single project card for contractor feed (Dashboard style)
   */
  const renderProjectCard = (project: Project) => {
    // Build owner profile image URL
    const ownerProfileUrl = project.owner_profile_pic
      ? `${api_config.base_url}/storage/${project.owner_profile_pic}`
      : null;

    // Get owner initials
    const ownerInitials = project.owner_name
      ?.split(' ')
      .slice(0, 2)
      .map(word => word[0])
      .join('')
      .toUpperCase() || 'PO';

    // Days remaining
    const daysRemaining = project.bidding_deadline ? getDaysRemaining(project.bidding_deadline) : null;

    const isOwnProject = (
      (typeof project.owner_id === 'number' && userData?.owner_id && project.owner_id === userData.owner_id) ||
      (typeof project.owner_user_id === 'number' && userData?.user_id && project.owner_user_id === userData.user_id)
    );

    return (
      <TouchableOpacity
        key={project.project_id}
        style={styles.projectCard}
        activeOpacity={0.7}
        onPress={() => setSelectedProject(project)}
      >
        {/* Header: Owner Info + Deadline Badge */}
        <View style={styles.projectHeader}>
          <View style={styles.ownerInfo}>
            {ownerProfileUrl ? (
              <ImageFallback
                uri={ownerProfileUrl}
                defaultImage={defaultOwnerAvatar}
                style={styles.ownerAvatarImg}
                resizeMode="cover"
              />
            ) : (
              <ImageFallback
                uri={undefined}
                defaultImage={defaultOwnerAvatar}
                style={styles.ownerAvatarImg}
                resizeMode="cover"
              />
            )}
            <View>
              <Text style={styles.ownerName}>{project.owner_name || 'Property Owner'}</Text>
              <Text style={styles.postDate}>
                Posted {new Date(project.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
              </Text>
            </View>
          </View>
          {daysRemaining !== null && (
            <View style={[styles.deadlineBadge, daysRemaining <= 3 && styles.deadlineUrgent]}>
              <MaterialIcons name="access-time" size={14} color={daysRemaining <= 3 ? '#E74C3C' : '#F39C12'} />
              <Text style={[styles.deadlineText, daysRemaining <= 3 && styles.deadlineTextUrgent]}>
                {daysRemaining > 0 ? `${daysRemaining}d left` : 'Due today'}
              </Text>
            </View>
          )}
        </View>

        {/* Project Title */}
        <Text style={styles.projectTitleText}>{project.project_title}</Text>

        {/* Project Description */}
        <Text style={styles.projectDescriptionText} numberOfLines={2}>
          {project.project_description}
        </Text>

        {/* Project Type Badge (moved below description) */}
        <View style={styles.projectTypeBadge}>
          <MaterialIcons name="business" size={14} color="#EC7E00" />
          <Text style={styles.projectTypeText}>{project.type_name}</Text>
        </View>

        {/* Project Details */}
        <View style={styles.projectDetailsContainer}>
          <View style={styles.detailRow}>
            <MaterialIcons name="location-on" size={16} color="#666666" />
            <Text style={styles.detailText}>{project.project_location}</Text>
          </View>
          <View style={styles.detailRow}>
            <MaterialIcons name="account-balance-wallet" size={16} color="#666666" />
            <Text style={styles.detailText}>
              {formatCurrency(project.budget_range_min)} - {formatCurrency(project.budget_range_max)}
            </Text>
          </View>
          <View style={styles.detailRow}>
            <MaterialIcons name="gavel" size={16} color="#666666" />
            <Text style={styles.detailText}>{project.bids_count || 0} bids received</Text>
          </View>
        </View>

        {/* Project Images Collage */}
        {project.files && project.files.length > 0 && renderProjectImages(project.files)}

        {/* Footer: Manage vs Place Bid */}
        <View style={styles.projectCardFooter}>
          {isOwnProject ? (
            <TouchableOpacity
              style={[styles.placeBidButton, { backgroundColor: '#3B82F6' }]}
              activeOpacity={0.8}
              onPress={() => {
                // Open project as owner for management
                setSelectedProject(project);
              }}
            >
              <MaterialIcons name="edit" size={18} color="#FFFFFF" />
              <Text style={styles.placeBidButtonText}>Manage Project</Text>
            </TouchableOpacity>
          ) : canBid ? (
            <TouchableOpacity
              style={styles.placeBidButton}
              activeOpacity={0.8}
              onPress={() => {
                setBidProject(project);
                setShowPlaceBid(true);
              }}
            >
              <MaterialIcons name="gavel" size={18} color="#FFFFFF" />
              <Text style={styles.placeBidButtonText}>Place Bid</Text>
            </TouchableOpacity>
          ) : (
            <View style={[styles.placeBidButton, { backgroundColor: '#94A3B8' }]}>
              <MaterialIcons name="visibility" size={18} color="#FFFFFF" />
              <Text style={styles.placeBidButtonText}>View Only</Text>
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
    console.log('Contractor role:', contractorRole, 'Can bid:', canBid);
  }, [userData, contractorRole, canBid]);

  // Memoize contractors for search to prevent infinite re-renders
  const searchContractors = useMemo(() => {
    return popularContractors.map(c => ({
      contractor_id: c.contractor_id,
      company_name: c.company_name,
      type_name: c.contractor_type,
      business_address: c.location,
      years_of_experience: c.years_of_experience,
      completed_projects: c.completed_projects,
      user_id: c.user_id,
      profile_pic: c.logo_url?.replace(`${api_config.base_url}/storage/`, ''),
      services_offered: c.services_offered,
    }));
  }, [popularContractors]);

  // Memoize search screen callbacks
  const handleSearchClose = useCallback(() => {
    setShowSearchScreen(false);
  }, []);

  const handleSearchProjectPress = useCallback((project: Project) => {
    setShowSearchScreen(false);
    setSelectedProject(project);
  }, []);

  const handleSearchContractorPress = useCallback((contractor: any) => {
    setShowSearchScreen(false);
    setSelectedContractor({
      contractor_id: contractor.contractor_id,
      company_name: contractor.company_name,
      location: contractor.business_address,
      contractor_type: contractor.type_name,
      years_of_experience: contractor.years_of_experience,
      completed_projects: contractor.completed_projects,
      user_id: contractor.user_id,
      logo_url: contractor.profile_pic ? `${api_config.base_url}/storage/${contractor.profile_pic}` : undefined,
      services_offered: contractor.services_offered,
    });
  }, []);

  // Render the home content (contractors feed for property owners)
  const renderHomeContent = () => {
    // Build profile image URL
    const profileImageUrl = userData?.profile_pic
      ? `${api_config.base_url}/storage/${userData.profile_pic}`
      : null;

    console.log('Profile image URL:', profileImageUrl);

    // For contractors, show projects feed
    if (effectiveUserType === 'contractor') {
      return renderContractorHomeContent();
    }

    // For property owners, show contractors feed
    return (
      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        onMomentumScrollEnd={(e) => handleScrollEnd(e, loadMoreContractors)}
        onScrollEndDrag={(e) => handleScrollEnd(e, loadMoreContractors)}
      >
        {/* User Profile and Project Input */}
        <View style={styles.profileSection}>
          <ImageFallback
            uri={profileImageUrl}
            defaultImage={require('../../../assets/images/pictures/property_owner_default.png')}
            style={styles.profileImage}
            resizeMode="cover"
          />
          <TouchableOpacity
            style={styles.projectInput}
            onPress={() => setShowCreateProject(true)}
          >
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
                  setContractorsPage(1);
                  contractors_service.get_active_contractors(undefined, 1, PER_PAGE)
                    .then(response => {
                      const contractorsData = response.data?.data || response.data;
                      if (response.success && contractorsData && Array.isArray(contractorsData)) {
                        const transformedContractors = contractors_service.transform_contractors(contractorsData);
                        setPopularContractors(transformedContractors);
                        if (response.pagination) {
                          setHasMoreContractors(response.pagination.has_more);
                        }
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
              
              {/* Loading More Indicator */}
              {loadingMore && (
                <View style={styles.loadingMoreContainer}>
                  <ActivityIndicator size="small" color="#EC7E00" />
                  <Text style={styles.loadingMoreText}>Loading more...</Text>
                </View>
              )}
              
              {/* End of List Indicator */}
              {!loadingMore && !hasMoreContractors && (
                <View style={styles.endOfListContainer}>
                  <Text style={styles.endOfListText}>You've reached the end</Text>
                </View>
              )}
            </>
          )}
        </View>
      </ScrollView>
    );
  };

  // Render home content for contractors (projects feed)
  const renderContractorHomeContent = () => {
    const profileImageUrl = userData?.profile_pic
      ? `${api_config.base_url}/storage/${userData.profile_pic}`
      : null;

    return (
      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        onMomentumScrollEnd={(e) => handleScrollEnd(e, loadMoreProjects)}
        onScrollEndDrag={(e) => handleScrollEnd(e, loadMoreProjects)}
      >
        {/* User Profile and Search */}
        <View style={styles.profileSection}>
          <ImageFallback
            uri={profileImageUrl}
            defaultImage={require('../../../assets/images/pictures/contractor_default.png')}
            style={styles.profileImage}
            resizeMode="cover"
          />
          <View style={styles.searchBarContainer}>
            <Feather name="search" size={18} color="#999999" />
            <Text style={styles.searchBarText}>Find projects to bid on...</Text>
          </View>
        </View>

        {/* Available Projects Section */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Available Projects</Text>

          {/* Loading State */}
          {isLoading && (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color="#EC7E00" />
              <Text style={styles.loadingText}>Loading projects...</Text>
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
                  setError(null);
                  setIsLoading(true);
                  setProjectsPage(1);
                  projects_service.get_approved_projects(1, PER_PAGE)
                    .then(response => {
                      const projectsData = response.data?.data || response.data;
                      if (response.success && projectsData && Array.isArray(projectsData)) {
                        setAvailableProjects(projectsData);
                        if (response.pagination) {
                          setHasMoreProjects(response.pagination.has_more);
                        }
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
          {!isLoading && !error && availableProjects.length === 0 && (
            <View style={styles.emptyContainer}>
              <Feather name="inbox" size={48} color="#999999" />
              <Text style={styles.emptyText}>No projects available at the moment</Text>
              <Text style={styles.emptySubtext}>Check back later for new project postings</Text>
            </View>
          )}

          {/* Projects List */}
          {!isLoading && !error && availableProjects.length > 0 && (
            <>
              {availableProjects.map((project) => renderProjectCard(project))}
              
              {/* Loading More Indicator */}
              {loadingMore && (
                <View style={styles.loadingMoreContainer}>
                  <ActivityIndicator size="small" color="#EC7E00" />
                  <Text style={styles.loadingMoreText}>Loading more...</Text>
                </View>
              )}
              
              {/* End of List Indicator */}
              {!loadingMore && !hasMoreProjects && (
                <View style={styles.endOfListContainer}>
                  <Text style={styles.endOfListText}>You've reached the end</Text>
                </View>
              )}
            </>
          )}
        </View>
      </ScrollView>
    );
  };

  // Render profile based on user type
  const renderProfileContent = () => {
    // For contractors, show the contractor profile
    if (effectiveUserType === 'contractor') {
      return (
        <ContractorProfile
          onLogout={handleLogout}
          onViewProfile={onViewProfile}
          onOpenHelp={onOpenHelp}
          onOpenSwitchRole={onOpenSwitchRole}
          userData={{
            username: userData?.username,
            email: userData?.email,
            user_type: userData?.user_type,
            profile_pic: userData?.profile_pic ? getStorageUrl(userData.profile_pic) : undefined,
            cover_photo: userData?.cover_photo ? getStorageUrl(userData.cover_photo) : undefined,
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
        onViewProfile={onViewProfile}
        onEditProfile={onEditProfile}
        onOpenHelp={onOpenHelp}
        onOpenSwitchRole={onOpenSwitchRole}
        userData={{
          username: userData?.username,
          email: userData?.email,
          user_type: userData?.user_type,
          profile_pic: userData?.profile_pic ? getStorageUrl(userData.profile_pic) : undefined,
          cover_photo: userData?.cover_photo ? getStorageUrl(userData.cover_photo) : undefined,
          user_type: userData?.user_type,
        }}
      />
    );
  };

  // Render dashboard based on user type
  const renderDashboardContent = () => {
    // For contractors, show the contractor dashboard
    if (effectiveUserType === 'contractor') {
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
          onNotificationsPress={() => setShowNotifications(true)}
          onNavigateToMessages={() => setActiveTab('messages')}
          onBrowseProjects={() => setActiveTab('home')}
          onFullScreenChange={(isFullScreen) => setIsFullScreenMode(isFullScreen)}
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
            ? (userData.profile_pic.startsWith('http')
              ? userData.profile_pic
              : `${api_config.base_url}/storage/${userData.profile_pic}`)
            : undefined,
        }}
        onNavigateToMessages={() => setActiveTab('messages')}
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

  // If viewing a project detail, show ProjectPostDetail screen
  if (selectedProject) {
    const isOwn = (
      (typeof selectedProject.owner_id === 'number' && userData?.owner_id && selectedProject.owner_id === userData.owner_id) ||
      (typeof selectedProject.owner_user_id === 'number' && userData?.user_id && selectedProject.owner_user_id === userData.user_id)
    );
    return (
      <ProjectPostDetail
        project={selectedProject}
        userRole={isOwn ? 'owner' : (effectiveUserType === 'contractor' ? 'contractor' : 'owner')}
        canBid={canBid}
        onClose={() => setSelectedProject(null)}
        onPlaceBid={canBid ? () => {
          setBidProject(selectedProject);
          setSelectedProject(null);
          setShowPlaceBid(true);
        } : undefined}
      />
    );
  }

  // If viewing a contractor profile, show CheckProfile screen
  if (selectedContractor) {
    return (
      <CheckProfile
        contractor={{
          contractor_id: selectedContractor.contractor_id,
          company_name: selectedContractor.company_name,
          company_description: selectedContractor.company_description,
          location: selectedContractor.location,
          rating: selectedContractor.rating,
          reviews_count: selectedContractor.reviews_count,
          contractor_type: selectedContractor.contractor_type,
          logo_url: selectedContractor.logo_url,
          cover_photo: selectedContractor.cover_photo,
          years_of_experience: selectedContractor.years_of_experience,
          services_offered: selectedContractor.services_offered,
          completed_projects: selectedContractor.completed_projects,
          user_id: selectedContractor.user_id,
          created_at: selectedContractor.created_at,
        }}
        onClose={() => setSelectedContractor(null)}
        onSendMessage={() => {
          setSelectedContractor(null);
          setActiveTab('messages');
        }}
      />
    );
  }

  // If Place Bid screen is open, show it full screen
  if (showPlaceBid && bidProject) {
    return (
      <PlaceBid
        project={bidProject}
        userId={userData?.user_id || 0}
        onClose={() => {
          setShowPlaceBid(false);
          setBidProject(null);
        }}
        onBidSubmitted={() => {
          // Refresh the projects list after submitting a bid
          refreshProjects();
        }}
      />
    );
  }

  // If Search screen is open, show it full screen
  if (showSearchScreen) {
    if (userType === 'contractor') {
      // Contractor searching for projects
      return (
        <SearchScreen
          onClose={handleSearchClose}
          projects={availableProjects}
          searchType="projects"
          onProjectPress={handleSearchProjectPress}
        />
      );
    } else {
      // Property owner searching for contractors
      return (
        <SearchScreen
          onClose={handleSearchClose}
          contractors={searchContractors}
          searchType="contractors"
          onContractorPress={handleSearchContractorPress}
        />
      );
    }
  }

  // If Create Project screen is open, show it full screen
  if (showCreateProject) {
    return (
      <CreateProjectScreen
        onBackPress={() => setShowCreateProject(false)}
        onSubmit={handleCreateProject}
        contractorTypes={contractorTypes}
      />
    );
  }

  // Show notifications screen
  if (showNotifications) {
    return (
      <Notifications
        userId={userData?.user_id || 0}
        userType={userData?.user_type || userType}
        onClose={() => setShowNotifications(false)}
      />
    );
  }

  return (
    <SafeAreaView style={[styles.container, { paddingTop: statusBarHeight }]}>
      <StatusBar hidden={true} />
      {/* Header - only show on non-profile tabs */}
      {activeTab !== 'profile' && (
        <View style={styles.header}>
          <Text style={styles.logoText}>LEGATURA</Text>
          <View style={styles.headerIcons}>
            <TouchableOpacity style={styles.iconButton} onPress={() => setShowSearchScreen(true)}>
              <MaterialIcons name="search" size={24} color="#333333" />
            </TouchableOpacity>
            <TouchableOpacity style={styles.iconButton} onPress={() => setShowNotifications(true)}>
              <MaterialIcons name="notifications" size={24} color="#333333" />
              {unreadCount > 0 && (
                <View style={styles.notificationBadge}>
                  <Text style={styles.badgeText}>{unreadCount > 99 ? '99+' : unreadCount}</Text>
                </View>
              )}
            </TouchableOpacity>
          </View>
        </View>
      )}

      {/* Main Content */}
      <View style={styles.mainContent}>
        {renderContent()}
      </View>

      {/* Bottom Navigation Bar - Hidden in full-screen mode */}
      {!isFullScreenMode && (
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
      )}
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
  // Contractor Card styles (matching project card style)
  contractorCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 0,
    padding: 16,
    marginHorizontal: 0,
    marginBottom: 0,
    borderBottomWidth: 8,
    borderBottomColor: '#F0F0F0',
    position: 'relative',
    overflow: 'visible',
  },
  contractorCover: {
    height: 110,
    backgroundColor: '#E5E5E5',
    marginBottom: 12,
    alignSelf: 'center',
  },
  contractorHeaderNew: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 0,
    marginTop: -40,
    marginBottom: 8,
    paddingHorizontal: 16,
  },
  leftColumn: {
    width: 80,
    alignItems: 'flex-start',
  },
  rightColumn: {
    flex: 1,
    alignItems: 'flex-end',
    justifyContent: 'center',
    paddingRight: 16,
  },
  contractorAvatarWrapper: {
    marginRight: 12,
    alignItems: 'flex-start',
  },
  contractorAvatarImg: {
    width: 72,
    height: 72,
    borderRadius: 36,
    borderWidth: 3,
    borderColor: '#FFFFFF',
    backgroundColor: '#E5E5E5',
  },
  contractorAvatarCircleNew: {
    width: 72,
    height: 72,
    borderRadius: 36,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: '#FFFFFF',
  },
  contractorHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  contractorInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  contractorAvatarCircle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#EC7E00',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 10,
  },
  contractorInitials: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  contractorName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333333',
  },
  contractorSubtitle: {
    fontSize: 12,
    color: '#999999',
  },
  contractorMainInfo: {
    flex: 1,
    justifyContent: 'center',
  },
  contractorInfoBlock: {
    paddingHorizontal: 0,
    marginTop: -8,
    marginBottom: 8,
  },
  nameRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },

  contractorTypeBadgeContainer: {
    backgroundColor: '#FFF3E6',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  contractorTypeBadgeText: {
    fontSize: 12,
    fontWeight: '500',
    color: '#EC7E00',
  },
  badgeOverlap: {
    position: 'absolute',
    right: 12,
    top: 140,
    zIndex: 20,
    elevation: 20,
  },
  badgeRow: {
    marginTop: 6,
  },
  contractorDescription: {
    fontSize: 14,
    color: '#666666',
    lineHeight: 20,
    marginBottom: 12,
  },
  contractorDetailsContainer: {
    gap: 8,
  },
  contractorCardFooter: {
    flexDirection: 'row',
    marginTop: 16,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#F0F0F0',
  },
  contactContractorButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EC7E00',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    gap: 6,
    flex: 1,
  },
  contactContractorButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
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
  loadingMoreContainer: {
    paddingVertical: 20,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    gap: 8,
  },
  loadingMoreText: {
    fontSize: 14,
    color: '#666666',
  },
  endOfListContainer: {
    paddingVertical: 20,
    alignItems: 'center',
    justifyContent: 'center',
  },
  endOfListText: {
    fontSize: 13,
    color: '#999999',
    fontStyle: 'italic',
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
  emptySubtext: {
    marginTop: 6,
    fontSize: 13,
    color: '#BBBBBB',
    textAlign: 'center',
  },
  // Search bar for contractors
  searchBarContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F5F5F5',
    borderRadius: 25,
    paddingHorizontal: 16,
    paddingVertical: 12,
    marginLeft: 12,
  },
  searchBarText: {
    fontSize: 15,
    color: '#999999',
    marginLeft: 10,
  },
  // Project card styles (Dashboard style - full width)
  projectCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 0,
    padding: 16,
    marginHorizontal: 0,
    marginBottom: 0,
    borderBottomWidth: 8,
    borderBottomColor: '#F0F0F0',
  },
  projectHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  ownerInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  ownerAvatarImg: {
    width: 40,
    height: 40,
    borderRadius: 20,
    marginRight: 10,
  },
  ownerAvatarCircle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#EC7E00',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 10,
  },
  ownerInitials: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  ownerName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333333',
  },
  postDate: {
    fontSize: 12,
    color: '#999999',
  },
  deadlineBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF5E5',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8,
    gap: 4,
  },
  deadlineUrgent: {
    backgroundColor: '#FFEBE5',
  },
  deadlineText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#F39C12',
  },
  deadlineTextUrgent: {
    color: '#E74C3C',
  },
  projectTitleText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333333',
    marginBottom: 8,
  },
  projectTypeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    backgroundColor: '#FFF3E6',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 6,
    marginBottom: 12,
  },
  projectTypeText: {
    fontSize: 12,
    fontWeight: '500',
    color: '#EC7E00',
  },
  projectDescriptionText: {
    fontSize: 14,
    color: '#666666',
    lineHeight: 20,
    marginBottom: 12,
  },
  projectDetailsContainer: {
    gap: 8,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  detailText: {
    fontSize: 13,
    color: '#666666',
  },
  projectCardFooter: {
    flexDirection: 'row',
    marginTop: 16,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#F0F0F0',
    gap: 12,
  },
  placeBidButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EC7E00',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    gap: 6,
    flex: 1,
  },
  placeBidButtonText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  // Project images collage styles
  imageCollageContainer: {
    marginTop: 12,
    marginBottom: 0,
    borderRadius: 8,
    overflow: 'hidden',
  },
  imageSingle: {
    width: '100%',
    height: 200,
    backgroundColor: '#E5E5E5',
  },
  imageRowTwo: {
    flexDirection: 'row',
    gap: 2,
  },
  imageHalf: {
    flex: 1,
    height: 180,
    backgroundColor: '#E5E5E5',
  },
  imageRowThree: {
    flexDirection: 'row',
    height: 200,
    gap: 2,
  },
  imageThreeLarge: {
    flex: 2,
    height: 200,
    backgroundColor: '#E5E5E5',
  },
  imageThreeStack: {
    flex: 1,
    gap: 2,
  },
  imageThreeSmall: {
    flex: 1,
    backgroundColor: '#E5E5E5',
  },
  imageGrid: {
    gap: 2,
  },
  imageGridRow: {
    flexDirection: 'row',
    gap: 2,
  },
  imageQuarter: {
    width: '49.5%',
    height: 100,
    backgroundColor: '#E5E5E5',
  },
  imageQuarterWrapper: {
    width: '49.5%',
    position: 'relative',
  },
  imageOverlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(0, 0, 0, 0.6)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  imageOverlayText: {
    color: '#FFFFFF',
    fontSize: 24,
    fontWeight: 'bold',
  },
  documentPlaceholder: {
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F5F5F5',
  },
  documentText: {
    fontSize: 12,
    color: '#999',
    marginTop: 8,
  },
  watermark: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: '100%',
    height: '100%',
    opacity: 0.12,
  },
  watermarkSmall: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    width: '100%',
    height: '100%',
    opacity: 0.12,
  },
});
