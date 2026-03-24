// @ts-nocheck
import React, { useState, useEffect, useMemo, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Dimensions,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  RefreshControl,
  ActivityIndicator,
  Alert,
  Platform,
  StatusBar,
  AppState,
  Modal,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { DeviceEventEmitter } from 'react-native';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import Ionicons from 'react-native-vector-icons/Ionicons';
import Feather from 'react-native-vector-icons/Feather';
import { Image } from 'expo-image';
import ImageFallback from '../../components/imageFallback';
import { projects_service, ContractorType as ContractorTypeOption } from '../../services/projects_service';
import { api_config } from '../../config/api';
import { contractors_service } from '../../services/contractors_service';
import { role_service } from '../../services/role_service';
import { useContractorAuth } from '../../hooks/useContractorAuth';
import { storage_service } from '../../utils/storage';

// Helper to build full storage URL for profile/cover images
const getStorageUrl = (filePath?: string, defaultSubfolder = 'profiles') => {
  if (!filePath) return undefined;
  const p = String(filePath).trim();
  // If it's already a full URL, return as-is
  if (p.startsWith('http://') || p.startsWith('https://')) return p;
  // If it already contains /storage, ensure base_url is prepended
  if (p.includes('/storage/')) {
    return p.startsWith('/') ? `${api_config.base_url}${p}` : `${api_config.base_url}/${p}`;
  }
  // If the path already contains a folder segment (e.g., 'profile_pics/..', 'cover_photos/..', 'profiles/...'),
  // treat it as a complete storage path and prefix with /storage/
  if (p.includes('/')) {
    return `${api_config.base_url}/storage/${p}`;
  }
  // Otherwise assume file lives under storage/<defaultSubfolder>/
  return `${api_config.base_url}/storage/${defaultSubfolder}/${p}`;
};

// Import profile screens
import PropertyOwnerProfile from '../owner/profile';
import BoostScreen from '../owner/boostScreen';
import ContractorProfile from '../contractor/profile';
import CheckProfile from './checkProfile';
import CheckOwnerProfile, { OwnerProp } from './checkOwnerProfile';
import WriteReview from './writeReview';

// Import dashboard screens
import PropertyOwnerDashboard from '../owner/dashboard';
import ContractorDashboard from '../contractor/dashboard';

// Import messages screen
import MessagesScreen from './messages';
import { messages_service } from '../../services/messages_service';
import { initPusher, subscribeToChatChannel, disconnectPusher } from '../../config/pusher';

// Import create project screen
import CreateProjectScreen from '../owner/createProject';

// Import search screen
import SearchScreen from './searchScreen';

// Import feed filter modal
import FeedFilterModal from '../../components/feedFilterModal';

// Import place bid screen
import PlaceBid from '../contractor/placeBid';

// Import project post detail screen
import ProjectPostDetail from './projectPostDetail';

// Import create showcase modal and post service for unified feed
import CreateShowcase from './createShowcase';
import { post_service } from '../../services/post_service';
import { highlightService } from '../../services/highlightService';
import ReportPostModal from '../../components/reportPostModal';

// Import showcase post detail screen
import ShowcasePostDetail from './showcasePostDetail';

// Import notifications screen
import Notifications from './notifications';
import { notifications_service } from '../../services/notifications_service';

// Import profile sub-screens
import ChangeOtpScreen from './changeOtpScreen';
import HelpCenterScreen from './helpCenter';
import EditProfileScreen from './editProfile';
import ViewProfileScreen from './viewProfile';
import SubscriptionScreen from '../contractor/subscriptionScreen';

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
  first_name?: string;
  middle_name?: string;
  last_name?: string;
  profile_pic?: string;
  cover_photo?: string;
  user_type?: 'property_owner' | 'contractor' | 'both' | 'staff' | 'owner_staff';
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
  onViewProfile?: (initialTab?: string, showcasePostId?: number, activeRole?: string) => void;
  onEditProfile?: () => void;
  onOpenHelp?: () => void;
  onOpenSwitchRole?: () => void;
  initialTab?: 'home' | 'dashboard' | 'messages' | 'profile';
}

export default function HomepageScreen({ userType = 'property_owner', userData, onLogout, onViewProfile, onEditProfile, onOpenHelp, onOpenSwitchRole, initialTab }: HomepageProps) {
  const insets = useSafeAreaInsets();
  const [popularContractors, setPopularContractors] = useState<ContractorType[]>([]);
  const [availableProjects, setAvailableProjects] = useState<Project[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [activeTab, setActiveTab] = useState(initialTab || 'home');
  const [error, setError] = useState<string | null>(null);
  const [profileImageError, setProfileImageError] = useState(false);
  const [isFullScreenMode, setIsFullScreenMode] = useState(false);
  const [currentRole, setCurrentRole] = useState<'contractor' | 'owner' | null>(null);
  const [profileSubScreen, setProfileSubScreen] = useState<null | 'change_otp' | 'help' | 'edit_profile' | 'subscription' | 'view_profile'>(null);
  const [viewProfileRefreshKey, setViewProfileRefreshKey] = useState(0);
  const [viewProfileInitialTab, setViewProfileInitialTab] = useState<string | undefined>(undefined);
  const [viewProfileInitialShowcasePostId, setViewProfileInitialShowcasePostId] = useState<number | null>(null);

  // Pagination state
  const [contractorsPage, setContractorsPage] = useState(1);
  const [projectsPage, setProjectsPage] = useState(1);
  const [loadingMore, setLoadingMore] = useState(false);
  const [hasMoreContractors, setHasMoreContractors] = useState(true);
  const [hasMoreProjects, setHasMoreProjects] = useState(true);
  const PER_PAGE = 15;

  // Create project screen state
  const [showCreateProject, setShowCreateProject] = useState(false);
  const [showBoosts, setShowBoosts] = useState(false);
  const [contractorTypes, setContractorTypes] = useState<ContractorTypeOption[]>([]);
  const [isSubmittingProject, setIsSubmittingProject] = useState(false);

  // Search screen state
  const [showSearchScreen, setShowSearchScreen] = useState(false);

  // Feed filter modal state
  const [showFeedFilter, setShowFeedFilter] = useState(false);
  const [feedFilters, setFeedFilters] = useState<any>({});

  // View contractor profile state
  const [selectedContractor, setSelectedContractor] = useState<ContractorType | null>(null);
  // View owner profile state
  const [selectedOwner, setSelectedOwner] = useState<OwnerProp | null>(null);
  // Write review state (from notification deep-link)
  const [reviewParams, setReviewParams] = useState<{ projectId: number; revieweeUserId: number } | null>(null);
  // Authenticated contractor profile (company_logo/company_banner)
  const [myContractorProfile, setMyContractorProfile] = useState<any>(null);

  // View project state (for contractors viewing projects)
  const [selectedProject, setSelectedProject] = useState<Project | null>(null);

  // View showcase post detail state
  const [selectedShowcasePost, setSelectedShowcasePost] = useState<any>(null);

  // Place bid screen state
  const [showPlaceBid, setShowPlaceBid] = useState(false);
  const [bidProject, setBidProject] = useState<Project | null>(null);

  // Notifications screen state
  const [showNotifications, setShowNotifications] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);
  const [accessRevokedVisible, setAccessRevokedVisible] = useState(false);
  const [accessRevokedMessage, setAccessRevokedMessage] = useState('Your contractor member access is no longer active.');

  // Unread message count for tab bar badge (updated in real-time by MessagesScreen)
  const [unreadMessageCount, setUnreadMessageCount] = useState(0);

  // Pusher instance for real-time unread badge — lives here so it works on any tab
  const homePusherRef = React.useRef<any>(null);

  useEffect(() => {
    let cancelled = false;
    const setup = async () => {
      try {
        const authToken = await storage_service.get_auth_token();
        const storedUser = await storage_service.get_user_data();
        const uid = storedUser?.user_id ?? storedUser?.id;
        if (!authToken || !uid) return;

        // Fetch initial unread count from inbox
        const res = await messages_service.get_inbox(null);
        if (!cancelled && res.success && res.data) {
          const total = res.data.reduce((s: number, c: any) => s + (c.unread_count || 0), 0);
          setUnreadMessageCount(total);
        }

        // Subscribe to Pusher for real-time increments
        const pusher = await initPusher(authToken);
        if (!pusher || cancelled) return;
        homePusherRef.current = pusher;

        subscribeToChatChannel(pusher, uid, (event: any) => {
          if (cancelled) return;
          // Only increment when the message is from someone else (not sent by us)
          const senderId = event?.sender?.id;
          if (senderId && senderId !== uid) {
            setUnreadMessageCount((prev) => prev + 1);
          }
        });
      } catch (e) {
        console.warn('HomepageScreen Pusher init error:', e);
      }
    };
    setup();
    return () => {
      cancelled = true;
      if (homePusherRef.current) {
        disconnectPusher(homePusherRef.current);
        homePusherRef.current = null;
      }
    };
  }, []);

  // Unified feed state (projects + showcase posts merged)
  const [feedItems, setFeedItems] = useState<any[]>([]);
  const [feedPage, setFeedPage] = useState(1);
  const [hasMoreFeed, setHasMoreFeed] = useState(true);
  const [loadingFeed, setLoadingFeed] = useState(true);
  const [feedRefreshing, setFeedRefreshing] = useState(false);
  const [showCreateChooser, setShowCreateChooser] = useState(false);
  const [showCreateShowcase, setShowCreateShowcase] = useState(false);
  const [activeCardMenu, setActiveCardMenu] = useState<{ type: 'project' | 'showcase'; id: number } | null>(null);
  const [reportModalVisible, setReportModalVisible] = useState(false);
  const [reportTarget, setReportTarget] = useState<{ postType: 'project' | 'showcase'; postId: number } | null>(null);

  // ScrollView ref for scrolling to top on refresh
  const homeScrollViewRef = React.useRef<ScrollView>(null);

  // Local image state — seeded from prop, then refreshed from API
  const [ownerProfilePicPath, setOwnerProfilePicPath] = useState<string | null>(userData?.profile_pic || null);

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

  useEffect(() => {
    let isMounted = true;
    const loadProfile = async () => {
      try {
        const { api_request } = require('../../config/api');
        const res = await api_request('/api/profile/fetch', { method: 'GET' });
        if (res?.success && res.data) {
          const payload = res.data?.data ?? res.data;
          const user = payload?.user ?? payload;
          const pic = user?.profile_pic ?? null;
          if (isMounted) {
            if (pic) setOwnerProfilePicPath(pic);
          }
        }
      } catch (e) {}
    };
    // Fetch profile immediately to ensure we have the latest avatar
    loadProfile();
    return () => { isMounted = false; };
  }, []);

  // Get status bar height (top inset)
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  // Staff-only sessions must always operate in contractor context.
  // Do not infer this from cached contractor_member because dual-role users can
  // retain that cache even while switching back to owner.
  const isStaffContext = useMemo(() => {
    const rawType = String(userData?.user_type || '').toLowerCase();
    return rawType === 'staff' || rawType === 'owner_staff';
  }, [userData?.user_type]);

  // Resolve effective user type: prefer explicit userData.user_type when available
  // IMPORTANT: Staff users should be treated as contractors
  const effectiveUserType = useMemo(() => {
    if (isStaffContext) return 'contractor';
    if (currentRole === 'owner') return 'property_owner';
    if (currentRole === 'contractor') return 'contractor';

    // When API role refresh is delayed, prefer the last explicit role switch intent.
    const preferredRole = String(userData?.preferred_role || '').toLowerCase();
    if (preferredRole === 'owner' || preferredRole === 'property_owner') return 'property_owner';
    if (preferredRole === 'contractor') return 'contractor';

    const rawType = userData?.user_type || userType;
    // Staff users operate in contractor context
    if (rawType === 'staff' || rawType === 'owner_staff' || rawType === 'contractor') {
      return 'contractor';
    }
    return rawType === 'property_owner' || rawType === 'both' ? 'property_owner' : userType;
  }, [isStaffContext, currentRole, userData?.preferred_role, userData?.user_type, userType]);

  const isContractorMemberSession = useMemo(() => {
    const member = userData?.contractor_member;
    if (!member) return false;
    return effectiveUserType === 'contractor' && member.is_contractor_owner === false;
  }, [effectiveUserType, userData?.contractor_member]);

  // If a contractor member is kicked/removed while logged in, block access
  // and allow only explicit logout.
  useEffect(() => {
    if (!isContractorMemberSession) return;

    let mounted = true;

    const checkMemberAccess = async () => {
      try {
        const uid = userData?.user_id;
        const token = await storage_service.get_auth_token();
        if (!uid || !token) return;

        const endpoint = `${api_config.base_url}${api_config.endpoints.contractor_members.list}?user_id=${uid}&_cb=${Date.now()}`;
        const res = await fetch(endpoint, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
            'X-User-Id': String(uid),
          },
        });

        if (res.status !== 403) return;

        const body = await res.json().catch(() => ({} as any));
        const code = String(body?.error_code || body?.data?.error_code || '').toUpperCase();
        if (mounted && ['MEMBER_NOT_FOUND', 'MEMBER_INACTIVE', 'MEMBER_SUSPENDED'].includes(code)) {
          setAccessRevokedMessage(body?.message || 'Your contractor member access is no longer active.');
          setAccessRevokedVisible(true);
        }
      } catch (e) {
        // Do not interrupt UI on transient errors.
      }
    };

    checkMemberAccess();
    const timer = setInterval(checkMemberAccess, 15000);

    return () => {
      mounted = false;
      clearInterval(timer);
    };
  }, [isContractorMemberSession, userData?.user_id]);

  // Refresh current role from API on mount and when app comes to foreground
  useEffect(() => {
    let isMounted = true;
    const fetchCurrentRole = async () => {
      try {
        // Force contractor role for staff/member sessions regardless of role endpoint defaults.
        if (isStaffContext) {
          if (isMounted) setCurrentRole('contractor');
          return;
        }

        const res = await role_service.get_current_role();
        if (res?.success) {
          const roleVal = (res as any).current_role || (res as any).data?.current_role || (res as any).user_type;
          const v = String(roleVal || '').toLowerCase();
          const role = v.includes('owner') ? 'owner' : v.includes('contractor') ? 'contractor' : null;
          if (isMounted) setCurrentRole(role as any);

          // If backend indicates current role is contractor and we don't have
          // a saved contractor_member context, persist a lightweight fallback
          // so `useContractorAuth` and related services enable contractor features.
          try {
            if (role === 'contractor') {
              const stored = await storage_service.get_user_data();
              // Owner payload exists for contractor owners; staff/representative sessions use staff_record.
              const contractorPayload = (res as any).contractor || (res as any).data?.contractor || null;
              const staffPayload = (res as any).staff_record || (res as any).data?.staff_record || null;

              if (stored && !stored.contractor_member) {
                let ctx: any = null;

                if (contractorPayload) {
                  ctx = {
                    contractor_member_id: contractorPayload.contractor_member_id || null,
                    contractor_id: contractorPayload.contractor_id || contractorPayload.contractorId || 0,
                    contractor_name: contractorPayload.company_name || contractorPayload.contractor_name || null,
                    role: 'owner',
                    is_active: (contractorPayload.is_active !== undefined) ? contractorPayload.is_active : (contractorPayload.verification_status === 'approved'),
                    is_contractor_owner: true,
                    has_full_access: true,
                    permissions: {
                      can_manage_members: true,
                      can_view_members: true,
                      can_bid: true,
                      can_manage_milestones: true,
                      can_upload_progress: true,
                      can_approve_payments: true,
                      can_view_property_owners: true,
                    }
                  };
                } else if (staffPayload) {
                  const roleName = String(staffPayload.company_role || staffPayload.role || 'others').toLowerCase();
                  const isActive = Number(staffPayload.is_active ?? 0) === 1 && Number(staffPayload.is_suspended ?? 0) !== 1;
                  const hasFullAccess = roleName === 'owner' || roleName === 'representative';

                  ctx = {
                    contractor_member_id: staffPayload.staff_id || null,
                    contractor_id: staffPayload.contractor_id || 0,
                    contractor_name: staffPayload.contractor_name || null,
                    role: roleName,
                    is_active: isActive,
                    is_contractor_owner: false,
                    has_full_access: hasFullAccess,
                    permissions: {
                      can_manage_members: false,
                      can_view_members: isActive,
                      can_bid: hasFullAccess,
                      can_manage_milestones: hasFullAccess,
                      can_upload_progress: isActive,
                      can_approve_payments: hasFullAccess,
                      can_view_property_owners: isActive,
                    }
                  };
                }

                if (ctx) {
                  stored.contractor_member = ctx;
                  stored.determinedRole = 'contractor';
                  await storage_service.save_user_data(stored);
                }
              }

              // Always sync is_contractor_owner from fresh API data even when
              // contractor_member was already cached (e.g. set during login).
              if (stored?.contractor_member) {
                const isStaff = !!staffPayload &&
                  Number(staffPayload.is_active ?? 0) === 1 &&
                  Number(staffPayload.is_suspended ?? 0) !== 1;
                if (stored.contractor_member.is_contractor_owner !== !isStaff) {
                  stored.contractor_member.is_contractor_owner = !isStaff;
                  await storage_service.save_user_data(stored);
                }
              }
            }
          } catch (err) {
            // ignore storage errors
          }
        }
      } catch (e) {
        // Silent failure; keep existing role
      }
    };

    fetchCurrentRole();

    // Listen for global roleChanged events to refresh immediately
    const roleChangedSub = DeviceEventEmitter.addListener('roleChanged', (payload?: any) => {
      const switched = String(payload?.role || '').toLowerCase();
      if (switched === 'owner' || switched === 'property_owner') {
        setCurrentRole('owner');
      } else if (switched === 'contractor') {
        setCurrentRole('contractor');
      }
      fetchCurrentRole();
    });

    const appStateSub = AppState.addEventListener('change', (state) => {
      if (state === 'active') fetchCurrentRole();
    });

    return () => {
      isMounted = false;
      try { roleChangedSub.remove(); } catch (e) {}
      try { appStateSub.remove(); } catch (e) {}
    };
  }, [isStaffContext]);

  // Handle logout - calls the parent callback
  const handleLogout = () => {
    if (onLogout) {
      onLogout();
    }
  };

  // Trigger child dashboard refresh whenever dashboard tab becomes active
  useEffect(() => {
    if (activeTab === 'dashboard') {
      try {
        DeviceEventEmitter.emit('dashboardRefresh', {
          role: currentRole,
          effectiveUserType,
          ts: Date.now(),
        });
      } catch (e) {
        // no-op
      }
    }
  }, [activeTab, currentRole, effectiveUserType]);

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

  // Fetch authenticated contractor profile when in contractor context (to obtain company_logo/company_banner)
  useEffect(() => {
    let isMounted = true;
    const fetchMyContractor = async () => {
      try {
        const res = await contractors_service.get_my_contractor_profile();
        console.log('[homepage] get_my_contractor_profile response:', res);
        const payload = res?.data?.data || res?.data || res?.contractor || null;
        if (isMounted) setMyContractorProfile(payload);
      } catch (e) {
        console.warn('[homepage] failed to fetch contractor profile', e);
      }
    };
    fetchMyContractor();
    return () => { isMounted = false; };
  }, [effectiveUserType]);

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

  // ── Unified feed fetch (both roles: bidding projects + showcase posts) ──
  const fetchUnifiedFeed = useCallback(async (page: number = 1, append: boolean = false) => {
    try {
      if (!append) {
        setLoadingFeed(true);
        setError(null);
      } else {
        setLoadingMore(true);
      }

      const response = await post_service.get_unified_feed(page, PER_PAGE, feedFilters);

      if (response.success && response.data) {
        const items = response.data.items || [];
        if (append) {
          setFeedItems(prev => [...prev, ...items]);
        } else {
          setFeedItems(items);
        }
        setHasMoreFeed(response.data.pagination?.has_more ?? false);
        setFeedPage(page);
      } else {
        if (!append) {
          setError(response.message || 'Failed to load feed');
        }
      }
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'An unexpected error occurred';
      if (!append) setError(msg);
      console.error('Error fetching unified feed:', err);
    } finally {
      setLoadingFeed(false);
      setLoadingMore(false);
    }
  }, [feedFilters]);

  // Refresh feed function - resets to page 1 and scrolls to top
  const refreshFeed = useCallback(async () => {
    setFeedPage(1);
    setHasMoreFeed(true);
    await fetchUnifiedFeed(1, false);
    // Scroll to top
    if (homeScrollViewRef.current) {
      homeScrollViewRef.current.scrollTo({ y: 0, animated: true });
    }
  }, [fetchUnifiedFeed]);

  const onPullToRefresh = useCallback(async () => {
    setFeedRefreshing(true);
    await refreshFeed();
    setFeedRefreshing(false);
  }, [refreshFeed]);

  useEffect(() => {
    fetchUnifiedFeed(1);
  }, []);

  const loadMoreFeed = useCallback(() => {
    if (loadingMore || !hasMoreFeed) return;
    fetchUnifiedFeed(feedPage + 1, true);
  }, [loadingMore, hasMoreFeed, feedPage, fetchUnifiedFeed]);

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

  // Expose a global callback so App.tsx can trigger a refresh after payment deep-link
  useEffect(() => {
    // @ts-ignore
    global.handlePaymentCallback = async (projectId: string | number) => {
      try {
        // For contractors, refresh the approved projects list
        await refreshProjects();
        // For owners, we may want to refresh owner projects elsewhere (projectList/dashboard)
        // Confirmation handled in UI; no alert popup here.
      } catch (e) {
        console.warn('handlePaymentCallback error:', e);
      }
    };

    // If a pending project id was queued by App.tsx (cold-start), process it now
    // @ts-ignore
    (async () => {
      try {
        // @ts-ignore
        const pending = global.pendingPaymentProjectId;
        if (pending) {
          // @ts-ignore
          await global.handlePaymentCallback(pending);
          // @ts-ignore
          delete global.pendingPaymentProjectId;
        }
      } catch (e) {
        console.warn('Error processing queued pendingPaymentProjectId:', e);
      }
    })();

    return () => {
      // @ts-ignore
      delete global.handlePaymentCallback;
    };
  }, [refreshProjects]);

  /**
   * Handle navigation from notification press.
   * The backend returns { screen, params } indicating where to go.
   *
   * Supported screens: 'dashboard', 'messages', 'profile', 'home'
   * Supported params.sub_screen: 'project_detail', 'project_bids', 'my_bids', 'projects', 'disputes'
   */
  const handleNotificationNavigate = useCallback((screen: string, params: Record<string, any>) => {
    setShowNotifications(false);

    switch (screen) {
      case 'messages':
        setActiveTab('messages');
        // If a specific conversation was targeted, emit event for MessagesScreen to pick up
        if (params.conversation_id) {
          setTimeout(() => {
            DeviceEventEmitter.emit('openConversation', { conversation_id: params.conversation_id });
          }, 300);
        }
        break;

      case 'profile':
        if (params.sub_screen === 'view_profile' || params.showcase_post_id || params.tab) {
          // Navigate to the full view-profile screen at the app level
          onViewProfile?.(
            params.tab || 'Posts',
            params.showcase_post_id ? Number(params.showcase_post_id) : undefined,
            params.active_role || undefined
          );
        } else {
          setActiveTab('profile');
        }
        break;

      case 'dashboard':
        setActiveTab('dashboard');
        // Emit after a short delay so the dashboard component has time to mount
        // and attach its DeviceEventEmitter listener before the event fires.
        if (params.sub_screen) {
          setTimeout(() => {
            DeviceEventEmitter.emit('dashboardNavigate', params);
          }, 500);
        }
        break;

      case 'review':
        // Deep-link from review_prompt notification
        if (params.project_id && params.reviewee_user_id) {
          setReviewParams({
            projectId: Number(params.project_id),
            revieweeUserId: Number(params.reviewee_user_id),
          });
        }
        break;

      case 'home':
      default:
        setActiveTab('home');
        break;
    }
  }, [onViewProfile]);

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
        setPopularContractors(prev => {
          const existingIds = new Set(prev.map(c => c.contractor_id));
          const unique = transformedContractors.filter(c => !existingIds.has(c.contractor_id));
          return [...prev, ...unique];
        });
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
        setAvailableProjects(prev => {
          const existingIds = new Set(prev.map(p => p.project_id));
          const unique = projectsData.filter(p => !existingIds.has(p.project_id));
          return [...prev, ...unique];
        });
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
      // Contractor cards must always show contractor company media.
      // Never fall back to owner profile/cover photos on these cards.
      const logoPath: string | null = (item as any).company_logo || item.logo_url || null;

      const logoUri = logoPath ? getStorageUrl(logoPath, 'profiles') : undefined;

    // Same rule for cover: contractor company banner only.
    // For raw feed items, `company_banner` exists (can be null) and must be authoritative.
    // For transformed contractor-service items, fall back to `cover_photo` (already mapped from company_banner).
    const hasCompanyBannerField = Object.prototype.hasOwnProperty.call(item as any, 'company_banner');
    const coverPath: string | null = hasCompanyBannerField
      ? ((item as any).company_banner || null)
      : (item.cover_photo || null);

      const hasCoverPhoto = !!coverPath && !String(coverPath).includes('placeholder');
      const coverPhotoUri = hasCoverPhoto ? getStorageUrl(coverPath, 'cover_photos') : undefined;

    // Generate initials for avatar fallback
    const initials = item.company_name
      ?.split(' ')
      .slice(0, 2)
      .map(word => word[0])
      .join('')
      .toUpperCase() || 'CO';

    const getProfileImageUrl = () => logoUri;

    console.log('[Profile] Final image URI:', getProfileImageUrl());

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
          style={styles.contractorCover}
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
   *  - Layout: 1 → full-width, 2 → side-by-side, 3 → big+2, 4+ → 2×2 with +N overlay (Facebook-style).
   */
  const renderProjectImages = (files: Array<string | { file_id?: number; file_type?: string; file_path?: string }>) => {
    if (!files || files.length === 0) return null;

    // Check if file is an image by extension or by being from project_files
    const isImage = (filePath: string, fileType?: string) => {
      if (!filePath) return false;
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

    // ── Strict filtering: EXCLUDE important/protected documents ──
    const optionalFiles = displayFiles.filter(
      (d) => !isImportantDocument(d.fileType, d.raw) && d.isImage
    );

    if (optionalFiles.length === 0) return null;

    // Collage sizing
    const GAP = 5;
    const usableWidth = width - 26; // card marginHorizontal 8×2=16 + collage margin 5×2=10
    const halfSize = Math.floor((usableWidth - GAP) / 2);
    const singleHeight = Math.floor(usableWidth * 0.56);

    // 1 image → full-width
    if (optionalFiles.length === 1) {
      return (
        <View style={styles.imageCollageContainer}>
          <Image
            source={{ uri: optionalFiles[0].url }}
            style={{ width: usableWidth, height: singleHeight }}
            contentFit="cover"
            transition={200}
            cachePolicy="memory-disk"
          />
        </View>
      );
    }

    // 2 images → side by side
    if (optionalFiles.length === 2) {
      return (
        <View style={styles.imageCollageContainer}>
          <View style={{ flexDirection: 'row' }}>
            {optionalFiles.map((f, i) => (
              <Image
                key={i}
                source={{ uri: f.url }}
                style={{ width: halfSize, height: Math.floor(usableWidth * 0.42), marginLeft: i === 1 ? GAP : 0 }}
                contentFit="cover"
                transition={200}
                cachePolicy="memory-disk"
              />
            ))}
          </View>
        </View>
      );
    }

    // 3 images → large left, two stacked right
    if (optionalFiles.length === 3) {
      const largeW = Math.floor(usableWidth * 0.66);
      const smallW = usableWidth - largeW - GAP;
      const triH = Math.floor(usableWidth * 0.52);
      const cellH = Math.floor((triH - GAP) / 2);
      return (
        <View style={styles.imageCollageContainer}>
          <View style={{ flexDirection: 'row' }}>
            <Image
              source={{ uri: optionalFiles[0].url }}
              style={{ width: largeW, height: triH, marginRight: GAP }}
              contentFit="cover"
              transition={200}
              cachePolicy="memory-disk"
            />
            <View style={{ width: smallW, height: triH }}>
              {optionalFiles.slice(1).map((f, i) => (
                <Image
                  key={i}
                  source={{ uri: f.url }}
                  style={{ width: smallW, height: cellH, marginTop: i === 1 ? GAP : 0 }}
                  contentFit="cover"
                  transition={200}
                  cachePolicy="memory-disk"
                />
              ))}
            </View>
          </View>
        </View>
      );
    }

    // 4+ images → 2×2 grid, +N overlay on 4th tile
    const grid = optionalFiles.slice(0, 4);
    const extra = optionalFiles.length - 4;
    const gridCellH = Math.floor(usableWidth * 0.40);
    return (
      <View style={styles.imageCollageContainer}>
        <View style={{ flexDirection: 'row', marginBottom: GAP }}>
          <Image source={{ uri: grid[0].url }} style={{ width: halfSize, height: gridCellH, marginRight: GAP }} contentFit="cover" transition={200} cachePolicy="memory-disk" />
          <Image source={{ uri: grid[1].url }} style={{ width: halfSize, height: gridCellH }} contentFit="cover" transition={200} cachePolicy="memory-disk" />
        </View>
        <View style={{ flexDirection: 'row' }}>
          <Image source={{ uri: grid[2].url }} style={{ width: halfSize, height: gridCellH, marginRight: GAP }} contentFit="cover" transition={200} cachePolicy="memory-disk" />
          <View style={{ width: halfSize, height: gridCellH }}>
            {grid[3] && <Image source={{ uri: grid[3].url }} style={{ width: halfSize, height: gridCellH }} contentFit="cover" transition={200} cachePolicy="memory-disk" />}
            {extra > 0 && (
              <View style={styles.imageOverlay}>
                <Text style={styles.imageOverlayText}>+{extra}</Text>
              </View>
            )}
          </View>
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
        onPress={() => {
          setActiveCardMenu(null);
          setSelectedProject(project);
        }}
      >
        {/* Header: Owner Info + Deadline Badge */}
        <View style={styles.projectHeader}>
          <TouchableOpacity
            style={styles.ownerInfo}
            activeOpacity={0.7}
            onPress={(e) => {
              e.stopPropagation?.();
              if (project.owner_user_id && !isOwnProject) {
                setSelectedOwner({
                  owner_id: project.owner_id,
                  user_id: project.owner_user_id,
                  name: project.owner_name || 'Property Owner',
                  profile_pic: project.owner_profile_pic,
                });
              }
            }}
          >
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
          </TouchableOpacity>
          <View style={styles.cardHeaderActions}>
            {daysRemaining !== null && (
              <View style={[styles.deadlineBadge, daysRemaining <= 3 && styles.deadlineUrgent]}>
                <MaterialIcons name="access-time" size={14} color={daysRemaining <= 3 ? '#E74C3C' : '#F39C12'} />
                <Text style={[styles.deadlineText, daysRemaining <= 3 && styles.deadlineTextUrgent]}>
                  {daysRemaining > 0 ? `${daysRemaining}d left` : 'Due today'}
                </Text>
              </View>
            )}
            <View style={styles.cardMenuWrap}>
              <TouchableOpacity
                style={styles.cardMenuButton}
                onPress={(e) => {
                  e.stopPropagation?.();
                  openProjectCardMenu(project.project_id);
                }}
                hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
              >
                <MaterialIcons name="more-vert" size={20} color="#4B5563" />
              </TouchableOpacity>

              {activeCardMenu?.type === 'project' && activeCardMenu.id === project.project_id && (
                <View style={styles.cardMenuDropdown}>
                  <TouchableOpacity
                    style={styles.cardMenuItem}
                    onPress={(e) => {
                      e.stopPropagation?.();
                      setActiveCardMenu(null);
                      openReportReasons('project', project.project_id);
                    }}
                  >
                    <Text style={styles.cardMenuDangerText}>Report</Text>
                  </TouchableOpacity>
                </View>
              )}
            </View>
          </View>
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

        {/* Footer: Manage vs Apply/Place Bid */}
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
              <Text style={styles.placeBidButtonText}>Apply Bid</Text>
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
      company_logo: c.logo_url?.replace(`${api_config.base_url}/storage/`, ''),
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

  const handleSearchShowcasePress = useCallback((showcase: any) => {
    setShowSearchScreen(false);
    setSelectedShowcasePost(showcase);
  }, []);

  const handleSearchContractorPress = useCallback((contractor: any) => {
    setShowSearchScreen(false);
    setSelectedContractor({
      contractor_id: contractor.contractor_id,
      company_name: contractor.company_name,
      location: contractor.business_address || contractor.address,
      contractor_type: contractor.type_name,
      years_of_experience: contractor.years_of_experience,
      completed_projects: contractor.completed_projects,
      user_id: contractor.user_id,
      logo_url: contractor.company_logo ? `${api_config.base_url}/storage/${contractor.company_logo}` : undefined,
      services_offered: contractor.services_offered,
    });
  }, []);

  const handleSearchOwnerPress = useCallback((owner: any) => {
    setShowSearchScreen(false);
    setSelectedOwner({
      owner_id: owner.owner_id,
      user_id: owner.user_id,
      name: owner.display_name || owner.company_name || owner.username || 'Property Owner',
      profile_pic: owner.profile_pic,
      address: owner.address,
    });
  }, []);

  // Handle feed filter apply
  const handleFeedFilterApply = useCallback(async (filters: any) => {
    setFeedFilters(filters);
    setFeedPage(1);
    setHasMoreFeed(true);
    
    // Fetch with new filters
    try {
      setLoadingFeed(true);
      setError(null);
      
      const response = await post_service.get_unified_feed(1, PER_PAGE, filters);
      
      if (response.success && response.data) {
        const items = response.data.items || [];
        setFeedItems(items);
        setHasMoreFeed(response.data.pagination?.has_more ?? false);
        setFeedPage(1);
      } else {
        setError(response.message || 'Failed to load feed');
      }
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'An unexpected error occurred';
      setError(msg);
      console.error('Error applying filters:', err);
    } finally {
      setLoadingFeed(false);
    }
  }, []);

  // Handle feed filter reset
  const handleFeedFilterReset = useCallback(async () => {
    // Clear filters first
    setFeedFilters({});
    setFeedPage(1);
    setHasMoreFeed(true);
    
    // Force a fresh fetch without any filters
    try {
      setLoadingFeed(true);
      setError(null);
      
      // Pass undefined to ensure no filters are sent
      const response = await post_service.get_unified_feed(1, PER_PAGE, undefined);
      
      if (response.success && response.data) {
        const items = response.data.items || [];
        setFeedItems(items);
        setHasMoreFeed(response.data.pagination?.has_more ?? false);
        setFeedPage(1);
      } else {
        setError(response.message || 'Failed to load feed');
      }
    } catch (err) {
      const msg = err instanceof Error ? err.message : 'An unexpected error occurred';
      setError(msg);
      console.error('Error resetting feed:', err);
    } finally {
      setLoadingFeed(false);
    }
  }, []);

  const renderSearchFeedItem = useCallback((feedItem: any, index: number) => {
    if (!feedItem) return null;

    if (feedItem.feed_type === 'project') {
      return renderProjectCard(feedItem.data as any);
    }

    if (feedItem.feed_type === 'showcase') {
      return renderShowcaseCard(feedItem.data, index);
    }

    if (feedItem.feed_type === 'contractor') {
      return renderContractorCard({
        item: {
          ...feedItem.data,
          contractor_type: feedItem.data?.type_name,
          location: feedItem.data?.business_address,
        },
      });
    }

    return null;
  }, [renderProjectCard, renderShowcaseCard, renderContractorCard]);

  const renderSearchContractorCard = useCallback((contractor: any) => {
    return renderContractorCard({
      item: {
        ...contractor,
        contractor_type: contractor?.type_name,
        location: contractor?.business_address,
      },
    });
  }, [renderContractorCard]);

  /* ═══════════════════════════════════════════════════════════════════
   * Showcase Post Card (for unified feed)
   * ═══════════════════════════════════════════════════════════════════ */

  const renderShowcaseImages = (images: Array<{ url: string }>) => {
    if (images.length === 0) return null;

    const GAP = 5;
    const usableWidth = width - 26; // card marginHorizontal 8×2=16 + collage margin 5×2=10
    const halfSize = Math.floor((usableWidth - GAP) / 2);
    const singleHeight = Math.floor(usableWidth * 0.56);

    if (images.length === 1) {
      return (
        <View style={styles.imageCollageContainer}>
          <Image
            source={{ uri: images[0].url }}
            style={{ width: usableWidth, height: singleHeight }}
            contentFit="cover"
            transition={200}
            cachePolicy="memory-disk"
          />
        </View>
      );
    }

    if (images.length === 2) {
      return (
        <View style={styles.imageCollageContainer}>
          <View style={{ flexDirection: 'row' }}>
            {images.map((img, i) => (
              <Image
                key={i}
                source={{ uri: img.url }}
                style={{ width: halfSize, height: Math.floor(usableWidth * 0.42), marginLeft: i === 1 ? GAP : 0 }}
                contentFit="cover"
                transition={200}
                cachePolicy="memory-disk"
              />
            ))}
          </View>
        </View>
      );
    }

    // 3 images: large left + 2 stacked right
    if (images.length === 3) {
      const largeW = Math.floor(usableWidth * 0.66);
      const smallW = usableWidth - largeW - GAP;
      const triH = Math.floor(usableWidth * 0.52);
      const cellH = Math.floor((triH - GAP) / 2);
      return (
        <View style={styles.imageCollageContainer}>
          <View style={{ flexDirection: 'row' }}>
            <Image source={{ uri: images[0].url }} style={{ width: largeW, height: triH, marginRight: GAP }} contentFit="cover" transition={200} cachePolicy="memory-disk" />
            <View style={{ width: smallW, height: triH }}>
              <Image source={{ uri: images[1].url }} style={{ width: smallW, height: cellH }} contentFit="cover" transition={200} cachePolicy="memory-disk" />
              <Image source={{ uri: images[2].url }} style={{ width: smallW, height: cellH, marginTop: GAP }} contentFit="cover" transition={200} cachePolicy="memory-disk" />
            </View>
          </View>
        </View>
      );
    }

    // 4+ images: 2×2 grid with +N overlay
    const grid = images.slice(0, 4);
    const extra = images.length - 4;
    const gridCellH = Math.floor(usableWidth * 0.40);
    return (
      <View style={styles.imageCollageContainer}>
        <View style={{ flexDirection: 'row', marginBottom: GAP }}>
          <Image source={{ uri: grid[0].url }} style={{ width: halfSize, height: gridCellH, marginRight: GAP }} contentFit="cover" transition={200} cachePolicy="memory-disk" />
          <Image source={{ uri: grid[1].url }} style={{ width: halfSize, height: gridCellH }} contentFit="cover" transition={200} cachePolicy="memory-disk" />
        </View>
        <View style={{ flexDirection: 'row' }}>
          <Image source={{ uri: grid[2].url }} style={{ width: halfSize, height: gridCellH, marginRight: GAP }} contentFit="cover" transition={200} cachePolicy="memory-disk" />
          <View style={{ width: halfSize, height: gridCellH }}>
            {grid[3] && <Image source={{ uri: grid[3].url }} style={{ width: halfSize, height: gridCellH }} contentFit="cover" transition={200} cachePolicy="memory-disk" />}
            {extra > 0 && (
              <View style={styles.imageOverlay}>
                <Text style={styles.imageOverlayText}>+{extra}</Text>
              </View>
            )}
          </View>
        </View>
      </View>
    );
  };

  // ── Highlight toggling for showcase posts ──
  const [highlightingPostId, setHighlightingPostId] = useState<number | null>(null);

  const handleToggleHighlight = useCallback(async (postId: number, currentlyHighlighted: boolean) => {
    if (highlightingPostId) return; // debounce
    setHighlightingPostId(postId);
    try {
      const res = currentlyHighlighted
        ? await highlightService.unhighlightPost(postId)
        : await highlightService.highlightPost(postId);
      if (res.success) {
        // Optimistic update in the feed
        setFeedItems(prev => prev.map(item => {
          if (item.data?.post_id === postId) {
            return {
              ...item,
              data: { ...item.data, is_highlighted: currentlyHighlighted ? 0 : 1 },
            };
          }
          return item;
        }));
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
    if (!reportTarget) {
      return { success: false, message: 'No report target selected.' };
    }

    const res = await post_service.report_post(reportTarget.postType, reportTarget.postId, reason, details, attachments);
    return {
      success: !!res.success,
      message: res.message || (res.success ? 'Report submitted.' : 'Unable to submit report right now.'),
    };
  }, [reportTarget]);

  const openReportReasons = useCallback((postType: 'project' | 'showcase', postId: number) => {
    setReportTarget({ postType, postId });
    setReportModalVisible(true);
  }, [submitPostReport]);

  const openProjectCardMenu = useCallback((projectId: number) => {
    setActiveCardMenu(prev => {
      if (prev?.type === 'project' && prev.id === projectId) return null;
      return { type: 'project', id: projectId };
    });
  }, []);

  const openShowcaseCardMenu = useCallback((postId: number) => {
    setActiveCardMenu(prev => {
      if (prev?.type === 'showcase' && prev.id === postId) return null;
      return { type: 'showcase', id: postId };
    });
  }, []);

  const renderShowcaseCard = (post: any, index: number) => {
    const avatarUrl = post.avatar
      ? getStorageUrl(post.avatar)
      : (post.company_logo ? getStorageUrl(post.company_logo) : null);

    const postImages = (post.images || []).map((img: any) => ({
      url: img.file_path?.startsWith('http')
        ? img.file_path
        : `${api_config.base_url}/storage/${img.file_path}`,
    }));

    const isOwn = post.user_id === userData?.user_id;
    const isHighlighted = !!post.is_highlighted;

    // Prefer milestone name, then project title for linked project display
    const linkedName = post.linked_milestone_name || post.linked_project_title || null;

    return (
      <TouchableOpacity
        key={`showcase-${post.post_id}-${index}`}
        style={styles.projectCard}
        activeOpacity={0.8}
        onPress={() => {
          setActiveCardMenu(null);
          setSelectedShowcasePost(post);
        }}
      >
        {/* Header: Author info + linked project tag */}
        <View style={styles.projectHeader}>
          <TouchableOpacity
            style={[styles.ownerInfo, { flex: 1 }]}
            activeOpacity={0.7}
            onPress={() => {
              if (!isOwn && post.user_id) {
                if (post.user_type === 'contractor' || post.company_name) {
                  setSelectedContractor({
                    contractor_id: 0,
                    company_name: post.display_name || post.company_name || post.username,
                    user_id: post.user_id,
                    logo_url: avatarUrl,
                  });
                } else {
                  setSelectedOwner({
                    owner_id: 0,
                    user_id: post.user_id,
                    name: post.display_name || post.username || 'Property Owner',
                    profile_pic: post.avatar || post.profile_pic,
                  });
                }
              }
            }}
          >
            <ImageFallback
              uri={avatarUrl || undefined}
              defaultImage={
                post.user_type === 'contractor' ? defaultContractorAvatar : defaultOwnerAvatar
              }
              style={styles.ownerAvatarImg}
              resizeMode="cover"
            />
            <View style={{ flex: 1 }}>
              <View style={{ flexDirection: 'row', alignItems: 'center', flexWrap: 'wrap', gap: 4 }}>
                <Text style={styles.ownerName} numberOfLines={1}>
                  {post.display_name || post.username || 'User'}
                </Text>
                {linkedName ? (
                  <>
                    <Text style={{ color: '#999', fontSize: 13 }}>—</Text>
                    <MaterialIcons name="label" size={14} color="#1565C0" />
                    <Text style={{ fontSize: 13, color: '#1565C0', fontWeight: '600' }} numberOfLines={1}>
                      {linkedName}
                    </Text>
                  </>
                ) : null}
              </View>
              <Text style={styles.postDate}>
                Posted{' '}
                {new Date(post.created_at).toLocaleDateString('en-US', {
                  month: 'short',
                  day: 'numeric',
                })}
              </Text>
            </View>
          </TouchableOpacity>

          <View style={styles.cardMenuWrap}>
            <TouchableOpacity
              style={styles.cardMenuButton}
              onPress={(e) => {
                e.stopPropagation?.();
                openShowcaseCardMenu(post.post_id);
              }}
              disabled={highlightingPostId === post.post_id}
              hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
            >
              <MaterialIcons name="more-vert" size={20} color="#4B5563" />
            </TouchableOpacity>

            {activeCardMenu?.type === 'showcase' && activeCardMenu.id === post.post_id && (
              <View style={styles.cardMenuDropdown}>
                {isOwn && (
                  <TouchableOpacity
                    style={styles.cardMenuItem}
                    onPress={(e) => {
                      e.stopPropagation?.();
                      setActiveCardMenu(null);
                      handleToggleHighlight(post.post_id, isHighlighted);
                    }}
                  >
                    <Text style={styles.cardMenuItemText}>{isHighlighted ? 'Unhighlight' : 'Highlight'}</Text>
                  </TouchableOpacity>
                )}
                <TouchableOpacity
                  style={styles.cardMenuItem}
                  onPress={(e) => {
                    e.stopPropagation?.();
                    setActiveCardMenu(null);
                    openReportReasons('showcase', post.post_id);
                  }}
                >
                  <Text style={styles.cardMenuDangerText}>Report</Text>
                </TouchableOpacity>
              </View>
            )}
          </View>
        </View>

        {/* Title */}
        {post.title && <Text style={styles.projectTitleText}>{post.title}</Text>}

        {/* Content */}
        <Text style={styles.projectDescriptionText} numberOfLines={3}>
          {post.content}
        </Text>

        {/* Images */}
        {postImages.length > 0 && renderShowcaseImages(postImages)}

        {/* Location (below images) */}
        {post.location ? (
          <View style={[styles.detailRow, { paddingHorizontal: 16, marginTop: 8, marginBottom: 14 }]}>
            <MaterialIcons name="location-on" size={16} color="#666666" />
            <Text style={styles.detailText}>{post.location}</Text>
          </View>
        ) : (
          <View style={{ height: 14 }} />
        )}
      </TouchableOpacity>
    );
  };

  // Render the home content (unified feed for both roles)
  const renderHomeContent = () => {
    // Build profile image URL exactly like profile.tsx
    const profileImageUrl = (() => {
      if (effectiveUserType === 'contractor') {
         const logo = (userData as any)?.company_logo || myContractorProfile?.company_logo || myContractorProfile?.logo_url || ownerProfilePicPath || userData?.profile_pic;
         return getStorageUrl(logo);
      }
      return getStorageUrl(ownerProfilePicPath || userData?.profile_pic || undefined);
    })();

    return (
      <>
      <ScrollView
        ref={homeScrollViewRef}
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        onMomentumScrollEnd={(e) => handleScrollEnd(e, loadMoreFeed)}
        onScrollEndDrag={(e) => handleScrollEnd(e, loadMoreFeed)}
        refreshControl={
          <RefreshControl
            refreshing={feedRefreshing}
            onRefresh={onPullToRefresh}
            colors={['#EEA24B']}
            tintColor="#EEA24B"
          />
        }
      >
        {/* ── Create Post Section ── */}
        <View style={styles.profileSection}>
          <ImageFallback
            uri={profileImageUrl || undefined}
            defaultImage={
              effectiveUserType === 'contractor'
                ? require('../../../assets/images/pictures/contractor_default.png')
                : require('../../../assets/images/pictures/property_owner_default.png')
            }
            style={styles.profileImage}
            resizeMode="cover"
          />
          <TouchableOpacity
            style={styles.projectInput}
            onPress={() => {
              if (effectiveUserType === 'property_owner') {
                setShowCreateChooser(true);
              } else {
                setShowCreateShowcase(true);
              }
            }}
          >
            <Text style={styles.projectInputText}>
              {effectiveUserType === 'contractor'
                ? 'Share your work...'
                : 'Create a post...'}
            </Text>
          </TouchableOpacity>
        </View>

        {/* ── Feed Section ── */}
        <View style={styles.section}>
          <View style={styles.sectionHeaderRow}>
            <Text style={styles.sectionTitle}>Feed</Text>
            <View style={styles.filterContainer}>
              <TouchableOpacity
                style={[styles.filterButton, Object.keys(feedFilters).length > 0 && styles.filterButtonActive]}
                onPress={() => setShowFeedFilter(true)}
                hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
              >
                <Ionicons 
                  name="filter" 
                  size={20} 
                  color={Object.keys(feedFilters).length > 0 ? '#FFFFFF' : '#EC7E00'} 
                />
                {Object.keys(feedFilters).length > 0 && (
                  <View style={styles.filterBadge}>
                    <Text style={styles.filterBadgeText}>{Object.keys(feedFilters).length}</Text>
                  </View>
                )}
              </TouchableOpacity>
              {Object.keys(feedFilters).length > 0 && (
                <TouchableOpacity
                  style={styles.resetButton}
                  onPress={handleFeedFilterReset}
                  hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
                >
                  <Feather name="rotate-ccw" size={14} color="#EF4444" />
                  <Text style={styles.resetButtonText}>Reset</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>

          {/* Loading */}
          {loadingFeed && (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color="#EC7E00" />
              <Text style={styles.loadingText}>Loading feed...</Text>
            </View>
          )}

          {/* Error */}
          {!loadingFeed && error && (
            <View style={styles.errorContainer}>
              <MaterialIcons name="error-outline" size={48} color="#E74C3C" />
              <Text style={styles.errorText}>{error}</Text>
              <TouchableOpacity
                style={styles.retryButton}
                onPress={() => {
                  setError(null);
                  fetchUnifiedFeed(1);
                }}
              >
                <Text style={styles.retryButtonText}>Retry</Text>
              </TouchableOpacity>
            </View>
          )}

          {/* Empty */}
          {!loadingFeed && !error && feedItems.length === 0 && (
            <View style={styles.emptyContainer}>
              <Feather name="inbox" size={48} color="#999999" />
              <Text style={styles.emptyText}>No posts available yet</Text>
              <Text style={styles.emptySubtext}>Be the first to share something!</Text>
            </View>
          )}

          {/* Feed Items */}
          {!loadingFeed && !error && feedItems.length > 0 && (
            <>
              {feedItems.map((item: any, index: number) => (
                <React.Fragment key={`${item.feed_type}-${item.item_id}-${index}`}>
                  {item.feed_type === 'project'
                    ? renderProjectCard(item.data)
                    : item.feed_type === 'contractor'
                      ? renderContractorCard({ item: {
                          ...item.data,
                          contractor_type: item.data.type_name,
                          location: item.data.business_address,
                        }})
                      : renderShowcaseCard(item.data, index)}
                </React.Fragment>
              ))}

              {/* Loading More */}
              {loadingMore && (
                <View style={styles.loadingMoreContainer}>
                  <ActivityIndicator size="small" color="#EC7E00" />
                  <Text style={styles.loadingMoreText}>Loading more...</Text>
                </View>
              )}

              {/* End of List */}
              {!loadingMore && !hasMoreFeed && (
                <View style={styles.endOfListContainer}>
                  <Text style={styles.endOfListText}>You've reached the end</Text>
                </View>
              )}
            </>
          )}
        </View>
      </ScrollView>

      <ReportPostModal
        visible={reportModalVisible}
        onClose={() => {
          setReportModalVisible(false);
          setReportTarget(null);
        }}
        onSubmit={submitPostReport}
      />

      <FeedFilterModal
        visible={showFeedFilter}
        onClose={() => setShowFeedFilter(false)}
        onApply={handleFeedFilterApply}
        userType={effectiveUserType}
      />
      </>
    );
  };

 // Render profile based on user type
const renderProfileContent = () => {
  // Helper to resolve image fields dynamically
  const getProfileAndCover = () => {
    if (!userData) return { profile_pic: null, cover_photo: null };

    // Contractor
    if (userData.user_type === 'contractor') {
      return {
        profile_pic: userData?.company_logo ? getStorageUrl(userData.company_logo) : undefined,
        cover_photo: userData?.company_banner ? getStorageUrl(userData.company_banner) : undefined,
      };
    }

    // Property Owner
    if (userData.user_type === 'property_owner') {
      return {
        profile_pic: userData?.profile_pic ? getStorageUrl(userData.profile_pic) : undefined,
        cover_photo: userData?.cover_photo ? getStorageUrl(userData.cover_photo) : undefined,
      };
    }

    // Both user types
    if (userData.user_type === 'both') {
      if (userData.preferred_role === 'contractor') {
        return {
          profile_pic: userData?.company_logo ? getStorageUrl(userData.company_logo) : undefined,
          cover_photo: userData?.company_banner ? getStorageUrl(userData.company_banner) : undefined,
        };
      } else {
        return {
          profile_pic: userData?.profile_pic ? getStorageUrl(userData.profile_pic) : undefined,
          cover_photo: userData?.cover_photo ? getStorageUrl(userData.cover_photo) : undefined,
        };
      }
    }

    // Default fallback
    return {
      profile_pic: userData?.profile_pic ? getStorageUrl(userData.profile_pic) : undefined,
      cover_photo: userData?.cover_photo ? getStorageUrl(userData.cover_photo) : undefined,
    };
  };

  const { profile_pic, cover_photo } = getProfileAndCover();

  // Contractor Profile
  if (effectiveUserType === 'contractor') {
    return (
      <ContractorProfile
        onLogout={handleLogout}
        onViewProfile={() => setProfileSubScreen('view_profile')}
        onOpenHelp={() => setProfileSubScreen('help')}
        onOpenSwitchRole={onOpenSwitchRole}
        onOpenSubscription={() => setProfileSubScreen('subscription')}
        onOpenChangeOtp={() => setProfileSubScreen('change_otp')}
        onEditProfile={() => setProfileSubScreen('edit_profile')}
        contractorVerified={myContractorProfile?.verification_status === 'approved'}
        userData={{
          username: userData?.username,
          email: userData?.email,
          user_type: userData?.user_type,
          first_name: userData?.first_name,
          middle_name: userData?.middle_name,
          last_name: userData?.last_name,
          profile_pic,
          cover_photo,
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
        onViewProfile={() => setProfileSubScreen('view_profile')}
        onEditProfile={() => setProfileSubScreen('edit_profile')}
        onOpenHelp={() => setProfileSubScreen('help')}
        onOpenSwitchRole={onOpenSwitchRole}
        onOpenBoosts={() => setShowBoosts(true)}
        onOpenChangeOtp={() => setProfileSubScreen('change_otp')}
        contractorVerified={myContractorProfile?.verification_status === 'approved'}
        userData={{
          username: userData?.username,
          email: userData?.email,
          user_type: userData?.user_type,
          profile_pic: userData?.profile_pic ? getStorageUrl(userData.profile_pic) : undefined,
          cover_photo: userData?.cover_photo ? getStorageUrl(userData.cover_photo) : undefined,
        }}
      />
    );
  };

  // If Boosts screen is open, show it full screen
  if (showBoosts) {
    return (
      <BoostScreen
        navigation={{ goBack: () => setShowBoosts(false) }}
      />
    );
  }

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
        // Pass contractor_id when user is in contractor role so inbox is role-scoped
        contractor_id: effectiveUserType === 'contractor'
          ? (myContractorProfile?.contractor_id ?? null)
          : null,
      }}
      onUnreadCountChange={setUnreadMessageCount}
    />
  );

  // Render content based on active tab
  const renderContent = () => {
    // Profile sub-screens render inside the tab (keeps bottom bar visible)
    if (activeTab === 'profile' && profileSubScreen === 'change_otp') {
      return (
        <ChangeOtpScreen
          token={userData?.token || userData?.api_token || ''}
          purpose={'change_password'}
          onBack={() => setProfileSubScreen(null)}
          onSuccess={() => setProfileSubScreen(null)}
        />
      );
    }
    if (activeTab === 'profile' && profileSubScreen === 'help') {
      return (
        <HelpCenterScreen
          onBack={() => setProfileSubScreen(null)}
        />
      );
    }
    if (activeTab === 'profile' && profileSubScreen === 'edit_profile') {
      return (
        <EditProfileScreen
          userData={userData}
          navigation={{ goBack: () => setProfileSubScreen(null) }}
          onBackPress={() => setProfileSubScreen(null)}
          onSaveSuccess={(updatedUser) => {
            if (typeof onEditProfile === 'function') onEditProfile();
            setViewProfileRefreshKey(k => k + 1);
            setProfileSubScreen(null);
          }}
        />
      );
    }
    if (activeTab === 'profile' && profileSubScreen === 'subscription') {
      return (
        <SubscriptionScreen
          onBack={() => setProfileSubScreen(null)}
        />
      );
    }
    if (activeTab === 'profile' && profileSubScreen === 'view_profile') {
      return (
        <ViewProfileScreen
          key={viewProfileRefreshKey}
          onBack={() => {
            setProfileSubScreen(null);
            setViewProfileInitialTab(undefined);
            setViewProfileInitialShowcasePostId(null);
          }}
          initialTab={viewProfileInitialTab as any}
          initialShowcasePostId={viewProfileInitialShowcasePostId}
          activeRole={effectiveUserType}
          userData={{
            ...userData,
            preferred_role: effectiveUserType,
            profile_pic: userData?.profile_pic ? getStorageUrl(userData.profile_pic) : undefined,
            cover_photo: userData?.cover_photo ? getStorageUrl(userData.cover_photo) : undefined,
          }}
        />
      );
    }
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

  // If viewing a showcase post detail, show ShowcasePostDetail screen
  if (selectedShowcasePost) {
    const isOwn = selectedShowcasePost.user_id === userData?.user_id;
    return (
      <ShowcasePostDetail
        post={selectedShowcasePost}
        isOwner={isOwn}
        onClose={() => setSelectedShowcasePost(null)}
        onViewProfile={(!isOwn && selectedShowcasePost.user_id) ? () => {
          const sp = selectedShowcasePost;
          setSelectedShowcasePost(null);
          if (sp.user_type === 'contractor' || sp.company_name) {
            setSelectedContractor({
              contractor_id: 0,
              company_name: sp.display_name || sp.company_name || sp.username,
              user_id: sp.user_id,
              logo_url: sp.avatar
                ? getStorageUrl(sp.avatar)
                : sp.company_logo
                  ? getStorageUrl(sp.company_logo)
                  : undefined,
            });
          } else {
            setSelectedOwner({
              owner_id: 0,
              user_id: sp.user_id,
              name: sp.display_name || sp.username || 'Property Owner',
              profile_pic: sp.avatar || sp.profile_pic,
            });
          }
        } : undefined}
      />
    );
  }

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
        onViewOwnerProfile={(!isOwn && selectedProject.owner_user_id) ? () => {
          const proj = selectedProject;
          setSelectedProject(null);
          setSelectedOwner({
            owner_id: proj.owner_id,
            user_id: proj.owner_user_id!,
            name: proj.owner_name || 'Property Owner',
            profile_pic: proj.owner_profile_pic,
          });
        } : undefined}
      />
    );
  }

  // If the WriteReview screen is active (from notification deep-link)
  if (reviewParams) {
    return (
      <WriteReview
        projectId={reviewParams.projectId}
        revieweeUserId={reviewParams.revieweeUserId}
        onClose={() => setReviewParams(null)}
        onReviewSubmitted={() => {
          setReviewParams(null);
        }}
      />
    );
  }

  // If viewing an owner profile, show CheckOwnerProfile screen
  if (selectedOwner) {
    return (
      <CheckOwnerProfile
        owner={selectedOwner}
        onClose={() => setSelectedOwner(null)}
        onSendMessage={() => {
          const { user_id, name, profile_pic } = selectedOwner;
          setSelectedOwner(null);
          setActiveTab('messages');
          setTimeout(() => {
            DeviceEventEmitter.emit('openConversationWithUser', { user_id, name, avatar: profile_pic });
          }, 200);
        }}
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
          const { user_id, company_name, logo_url } = selectedContractor;
          setSelectedContractor(null);
          setActiveTab('messages');
          setTimeout(() => {
            DeviceEventEmitter.emit('openConversationWithUser', { user_id, name: company_name, avatar: logo_url });
          }, 200);
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
          const submittedProjectId = bidProject?.project_id;

          // Hide the project immediately from contractor lists so no manual reload is needed.
          if (submittedProjectId) {
            setFeedItems(prev =>
              prev.filter(item => !(item?.feed_type === 'project' && item?.data?.project_id === submittedProjectId))
            );
            setAvailableProjects(prev => prev.filter(p => p.project_id !== submittedProjectId));
          }

          // Keep background data in sync for any legacy/non-unified screens.
          refreshProjects();
        }}
        onOpenSubscription={() => {
          if (global.set_app_state) global.set_app_state('subscription');
        }}
      />
    );
  }

  // If Search screen is open, show it full screen (FB-style search)
  if (showSearchScreen) {
    return (
      <SearchScreen
        onClose={handleSearchClose}
        searchType={effectiveUserType === 'contractor' ? 'projects' : 'contractors'}
        onContractorPress={handleSearchContractorPress}
        onOwnerPress={handleSearchOwnerPress}
        onProjectPress={handleSearchProjectPress}
        onShowcasePress={handleSearchShowcasePress}
        renderFeedItemCard={renderSearchFeedItem}
        renderContractorFeedCard={renderSearchContractorCard}
      />
    );
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
        onNavigate={handleNotificationNavigate}
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
            {activeTab !== 'dashboard' && activeTab !== 'messages' && (
              <TouchableOpacity style={styles.iconButton} onPress={() => setShowSearchScreen(true)}>
                <MaterialIcons name="search" size={24} color="#333333" />
              </TouchableOpacity>
            )}
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
            onPress={() => {
              if (activeTab === 'home') {
                // If already on home tab, refresh feed and scroll to top
                refreshFeed();
              } else {
                // Switch to home tab
                setActiveTab('home');
              }
            }}
          >
            <Ionicons
              name={activeTab === 'home' ? 'home' : 'home-outline'}
              size={24}
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
            <View style={{ position: 'relative' }}>
              <Ionicons
                name={activeTab === 'messages' ? 'chatbubble' : 'chatbubble-outline'}
                size={24}
                color={activeTab === 'messages' ? '#EC7E00' : '#8E8E93'}
              />
              {unreadMessageCount > 0 && (
                <View style={{
                  position: 'absolute',
                  top: -4,
                  right: -6,
                  backgroundColor: '#EF4444',
                  borderRadius: 8,
                  minWidth: 16,
                  height: 16,
                  justifyContent: 'center',
                  alignItems: 'center',
                  paddingHorizontal: 3,
                }}>
                  <Text style={{ color: '#fff', fontSize: 9, fontWeight: '700' }}>
                    {unreadMessageCount > 99 ? '99+' : unreadMessageCount}
                  </Text>
                </View>
              )}
            </View>
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

      {/* ── Create Post Chooser Modal (Property Owners) ── */}
      <Modal
        visible={showCreateChooser}
        transparent
        animationType="fade"
        onRequestClose={() => setShowCreateChooser(false)}
      >
        <TouchableOpacity
          style={chooserStyles.overlay}
          activeOpacity={1}
          onPress={() => setShowCreateChooser(false)}
        >
          <View style={chooserStyles.sheet}>
            <Text style={chooserStyles.title}>Create a Post</Text>

            <TouchableOpacity
              style={chooserStyles.option}
              onPress={() => {
                setShowCreateChooser(false);
                setShowCreateProject(true);
              }}
            >
              <View style={[chooserStyles.iconWrap, { backgroundColor: '#FFF3E6' }]}>
                <MaterialIcons name="gavel" size={22} color="#EC7E00" />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={chooserStyles.optionTitle}>Project for Bidding</Text>
                <Text style={chooserStyles.optionDesc}>Post a project and find a contractor</Text>
              </View>
              <MaterialIcons name="chevron-right" size={22} color="#CCC" />
            </TouchableOpacity>

            <TouchableOpacity
              style={chooserStyles.option}
              onPress={() => {
                setShowCreateChooser(false);
                setShowCreateShowcase(true);
              }}
            >
              <View style={[chooserStyles.iconWrap, { backgroundColor: '#E8F5E9' }]}>
                <MaterialIcons name="photo-library" size={22} color="#4CAF50" />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={chooserStyles.optionTitle}>Showcase Post</Text>
                <Text style={chooserStyles.optionDesc}>Share photos and updates from your projects</Text>
              </View>
              <MaterialIcons name="chevron-right" size={22} color="#CCC" />
            </TouchableOpacity>

            <TouchableOpacity
              style={chooserStyles.cancelBtn}
              onPress={() => setShowCreateChooser(false)}
            >
              <Text style={chooserStyles.cancelText}>Cancel</Text>
            </TouchableOpacity>
          </View>
        </TouchableOpacity>
      </Modal>

      {/* ── Create Showcase Modal (both roles) ── */}
      <CreateShowcase
        visible={showCreateShowcase}
        onClose={() => setShowCreateShowcase(false)}
        onCreated={() => fetchUnifiedFeed(1)}
      />

      <Modal
        visible={accessRevokedVisible}
        transparent
        animationType="fade"
        onRequestClose={() => {}}
      >
        <View style={styles.accessRevokedOverlay}>
          <View style={styles.accessRevokedCard}>
            <Ionicons name="lock-closed-outline" size={42} color="#E74C3C" />
            <Text style={styles.accessRevokedTitle}>Access Revoked</Text>
            <Text style={styles.accessRevokedText}>{accessRevokedMessage}</Text>
            <TouchableOpacity
              style={styles.accessRevokedLogoutButton}
              onPress={handleLogout}
              activeOpacity={0.85}
            >
              <Text style={styles.accessRevokedLogoutText}>Log Out</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

/* ── Chooser modal styles ─────────────────────────────────────────── */
const chooserStyles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.45)',
    justifyContent: 'flex-end',
  },
  sheet: {
    backgroundColor: '#fff',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    paddingHorizontal: 20,
    paddingTop: 20,
    paddingBottom: 34,
  },
  title: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1A1A1A',
    marginBottom: 16,
  },
  option: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
    gap: 12,
  },
  iconWrap: {
    width: 42,
    height: 42,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
  optionTitle: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1A1A1A',
  },
  optionDesc: {
    fontSize: 12,
    color: '#999',
    marginTop: 2,
  },
  cancelBtn: {
    alignItems: 'center',
    paddingVertical: 14,
    marginTop: 8,
  },
  cancelText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#999',
  },
});

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
    marginLeft: 12,
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
  sectionHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  filterContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  filterButton: {
    width: 42,
    height: 42,
    borderRadius: 10,
    backgroundColor: '#FFF3E6',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#EC7E00' + '30',
    position: 'relative',
  },
  filterButtonActive: {
    backgroundColor: '#EC7E00',
    borderColor: '#EC7E00',
  },
  filterBadge: {
    position: 'absolute',
    top: -4,
    right: -4,
    backgroundColor: '#EF4444',
    borderRadius: 10,
    minWidth: 18,
    height: 18,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 4,
  },
  filterBadgeText: {
    color: '#FFFFFF',
    fontSize: 10,
    fontWeight: '700',
  },
  resetButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingVertical: 4,
    paddingHorizontal: 8,
  },
  resetButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#EF4444',
  },
  // Contractor Card styles
  contractorCard: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 8,
    marginTop: 6,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    overflow: 'hidden',
    position: 'relative',
  },
  contractorCover: {
    width: '100%',
    height: 110,
    backgroundColor: '#E5E5E5',
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
    flexShrink: 1,
    flexWrap: 'wrap',
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
    paddingHorizontal: 16,
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
    top: 122,
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
    paddingHorizontal: 16,
    paddingBottom: 16,
  },
  contractorCardFooter: {
    flexDirection: 'row',
    marginTop: 4,
    paddingTop: 12,
    paddingHorizontal: 16,
    paddingBottom: 14,
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
  accessRevokedOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.75)',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
  },
  accessRevokedCard: {
    width: '100%',
    maxWidth: 380,
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    paddingVertical: 24,
    paddingHorizontal: 20,
    alignItems: 'center',
  },
  accessRevokedTitle: {
    marginTop: 10,
    fontSize: 20,
    fontWeight: '800',
    color: '#1F2937',
  },
  accessRevokedText: {
    marginTop: 10,
    fontSize: 14,
    lineHeight: 20,
    color: '#4B5563',
    textAlign: 'center',
  },
  accessRevokedLogoutButton: {
    marginTop: 18,
    width: '100%',
    backgroundColor: '#E74C3C',
    borderRadius: 10,
    paddingVertical: 12,
    alignItems: 'center',
  },
  accessRevokedLogoutText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '700',
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
  // Project card styles
  projectCard: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 8,
    marginTop: 6,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    overflow: 'hidden',
  },
  projectHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
    paddingHorizontal: 16,
    paddingTop: 14,
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
  cardHeaderActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  cardMenuWrap: {
    position: 'relative',
    zIndex: 30,
  },
  cardMenuButton: {
    width: 28,
    height: 28,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
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
  projectTitleText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333333',
    marginBottom: 8,
    paddingHorizontal: 16,
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
    marginLeft: 16,
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
    paddingHorizontal: 16,
  },
  projectDetailsContainer: {
    gap: 8,
    paddingHorizontal: 16,
    paddingBottom: 12,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  detailText: {
    fontSize: 13,
    color: '#666666',
    flex: 1,
    flexWrap: 'wrap',
    flexShrink: 1,
  },
  projectCardFooter: {
    flexDirection: 'row',
    marginTop: 4,
    paddingTop: 12,
    paddingHorizontal: 16,
    paddingBottom: 14,
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
    margin: 5,
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
