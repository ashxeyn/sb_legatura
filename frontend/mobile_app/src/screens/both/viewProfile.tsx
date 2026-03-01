// @ts-nocheck
import React, { useState, useEffect, useCallback, useMemo, memo } from 'react';
import {
  View,
  Text,
  Image,
  StatusBar,
  ScrollView,
  RefreshControl,
  TouchableOpacity,
  StyleSheet,
  Dimensions,
  Platform,
  Alert,
  ActivityIndicator,
  FlatList,
} from 'react-native';
import { MaterialIcons, Ionicons, FontAwesome5, Feather } from '@expo/vector-icons';
import ImageFallback from '../../components/ImageFallbackFixed';
import ProjectPostDetail from './projectPostDetail';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import * as ImagePicker from 'expo-image-picker';
import { api_config, api_request } from '../../config/api';
import { auth_service } from '../../services/auth_service';

const { width: SCREEN_WIDTH } = Dimensions.get('window');
const COVER_HEIGHT = 200;
const AVATAR_SIZE = 100;

// Color palette from CheckProfile
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

// Default images
const defaultCoverPhoto = require('../../../assets/images/pictures/cp_default.jpg');
const defaultOwnerAvatar = require('../../../assets/images/pictures/property_owner_default.png');
const defaultContractorAvatar = require('../../../assets/images/pictures/contractor_default.png');

// Types
interface UserData {
  user_id?: string | number;
  username?: string;
  first_name?: string;
  last_name?: string;
  bio?: string;
  profile_pic?: string;
  cover_photo?: string;
  occupation_name?: string;
}

interface OwnerInfo {
  first_name?: string;
  middle_name?: string;
  last_name?: string;
  bio?: string;
  address?: string;
  address_display?: string;
  occupation_name?: string;
  occupation_id?: string;
}

interface Project {
  project_id: string | number;
  project_title: string;
  project_description: string;
  project_status: string;
  project_post_status?: string;
  budget_range_min: number;
  budget_range_max: number;
  project_location: string;
  bidding_deadline?: string;
  files?: Array<{ file_path?: string } | string>;
  cover_photo?: string;
  [key: string]: any;
}

interface Review {
  review_id: string | number;
  rating: number;
  comment: string;
  created_at: string;
  reviewer_name?: string;
  reviewer_username?: string;
  reviewer_company_name?: string;
  reviewer_profile_pic?: string;
  reviewer_display_name?: string;
}

type TabType = 'Posts' | 'Projects' | 'About' | 'Reviews' | 'Portfolio' | 'Highlights';

// Utility functions
const formatBudget = (min: number, max: number): string => {
  const formatNum = (n: number): string => {
    if (n >= 1000000) return `₱${(n / 1000000).toFixed(1)}M`;
    if (n >= 1000) return `₱${(n / 1000).toFixed(0)}K`;
    return `₱${n}`;
  };
  return `${formatNum(min)} - ${formatNum(max)}`;
};

const getInitials = (name?: string): string => {
  if (!name) return '?';
  return name
    .split(' ')
    .map(part => part[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

// Memoized Components
const ProjectCard = memo(({ project, onPress, ownerUser }: { project: Project; onPress: (index?: number) => void; ownerUser?: UserData }) => {
  const deadlineDate = project.bidding_deadline ? new Date(project.bidding_deadline) : null;
  const isUrgent = useMemo(() => {
    if (!deadlineDate) return false;
    const daysUntil = (deadlineDate.getTime() - Date.now()) / (1000 * 60 * 60 * 24);
    return daysUntil <= 3 && daysUntil > 0;
  }, [deadlineDate]);

  const status = useMemo(() => {
    const s = project.project_status?.toLowerCase() || '';
    const post = project.project_post_status?.toLowerCase() || '';
    return s === 'open' || post === 'approved' ? 'Open' : 'Closed';
  }, [project.project_status, project.project_post_status]);

  // Prefer profile picture from common fields
  let ownerProfilePath: any = project._owner_profile_pic || project.profile_pic || project.owner_profile_pic || project.user?.profile_pic || project.user_profile_pic || project.user?.profilePic || project.profilePic || project.avatar || project.user?.avatar || project.owner?.profile_pic || project.owner_profile || project.ownerProfile || project.owner?.user?.profile_pic;

  if (!ownerProfilePath && ownerUser?.profile_pic) ownerProfilePath = ownerUser.profile_pic;

  if (!ownerProfilePath) {
    const keysToTry = ['profile_pic', 'profilePic', 'profile_photo', 'profile', 'avatar', 'avatar_url', 'avatarUrl', 'picture', 'photo'];
    for (const k of keysToTry) {
      if ((project as any)[k]) { ownerProfilePath = (project as any)[k]; break; }
      if (project.user && (project.user as any)[k]) { ownerProfilePath = (project.user as any)[k]; break; }
      if (project.owner && (project.owner as any)[k]) { ownerProfilePath = (project.owner as any)[k]; break; }
    }
  }

  const ownerProfileUrl = ownerProfilePath ? (String(ownerProfilePath).startsWith('http') ? String(ownerProfilePath) : `${api_config.base_url}/storage/${String(ownerProfilePath)}`) : null;

  // Owner full name
  const projectFirst = project.first_name || project.owner_first_name || project.owner_firstname || '';
  const projectMiddle = project.middle_name || project.owner_middle_name || project.owner_mname || '';
  const projectLast = project.last_name || project.owner_last_name || project.owner_lastname || '';

  const ownerUserFirst = ownerUser?.first_name || '';
  const ownerUserMiddle = (ownerUser as any)?.middle_name || '';
  const ownerUserLast = ownerUser?.last_name || '';

  const ownerFullName = project.owner_full_name
    || project._owner_display
    || [projectFirst, projectMiddle, projectLast].filter(Boolean).join(' ').trim()
    || project.owner_name
    || [ownerUserFirst, ownerUserMiddle, ownerUserLast].filter(Boolean).join(' ').trim()
    || 'Property Owner';

  const ownerInitials = ownerFullName
    .split(' ')
    .filter(Boolean)
    .map((p: string) => p[0])
    .join('')
    .toUpperCase()
    .slice(0, 2) || 'PO';

  // Render image collage
  const renderCollage = () => {
    const files = project.files || [];
    if (!files || files.length === 0) return null;

    const isImage = (filePath: string, fileType?: string) => {
      if (!filePath) return false;
      if (fileType && ['building permit', 'title', 'blueprint', 'desired design', 'others'].includes(fileType.toLowerCase())) return true;
      if (filePath.startsWith('http://') || filePath.startsWith('https://')) {
        return /\.(jpg|jpeg|png|gif|webp|bmp)(\?|$)/i.test(filePath);
      }
      const ext = filePath.toLowerCase().split('.').pop() || '';
      return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(ext);
    };

    const isImportantDocument = (fileType: string, rawPath: string): boolean => {
      const type = (fileType || '').toLowerCase();
      const path = (rawPath || '').toLowerCase();
      if (/building.?permit|building_permit|building-permit/i.test(type)) return true;
      if (type === 'title' || /(^|\b)title(\b|$)/i.test(type)) return true;
      if (/title_of_land|title-of-land|land.?title/i.test(type)) return true;
      if (/(\/titles\/|project_files\/titles|project_file\/titles|titles\/)/.test(path)) return true;
      const normalized = path.replace(/[^a-z0-9]+/g, ' ');
      const tokens = normalized.split(/\s+/).filter(Boolean);
      const has = (w: string) => tokens.includes(w);
      if (has('building') && has('permit')) return true;
      if (has('title') && has('land')) return true;
      if (/(title_of_land|title-of-land|land_title)/.test(path)) return true;
      return false;
    };

    const displayFiles = files.map((f) => {
      if (f && typeof f === 'object') {
        const path = (f.file_path || (f as any).file || (f as any).url || '').toString();
        const type = (f.file_type || (f as any).type || '').toString();
        const url = path.startsWith('http') ? path : `${api_config.base_url}/storage/${path}`;
        return { raw: path, url, isImage: isImage(path, type), fileType: type };
      }
      const raw = String(f);
      return { raw, url: raw.startsWith('http') ? raw : `${api_config.base_url}/storage/${raw}`, isImage: isImage(raw), fileType: '' };
    });

    const optionalFiles = displayFiles.filter(d => !isImportantDocument(d.fileType, d.raw) && d.isImage);
    if (optionalFiles.length === 0) return null;

    const H_PADDING = 0; // Changed to 0 for full width
    const GAP = 2;
    const usableWidth = SCREEN_WIDTH - H_PADDING * 2;
    const halfSize = Math.floor((usableWidth - GAP) / 2);
    const largeWidth = Math.floor(usableWidth * 0.66);
    const singleHeight = Math.floor(usableWidth * 0.56);

    if (optionalFiles.length === 1) {
      const f = optionalFiles[0];
      return (
        <View style={[styles.imageCollageContainer, { paddingHorizontal: 0 }]}>
          <TouchableOpacity activeOpacity={0.9} onPress={() => onPress(0)}>
            <Image source={{ uri: f.url }} style={{ width: usableWidth, height: singleHeight }} resizeMode="cover" />
          </TouchableOpacity>
        </View>
      );
    }

    if (optionalFiles.length === 2) {
      return (
        <View style={[styles.imageCollageContainer, { paddingHorizontal: 0 }]}>
          <View style={{ flexDirection: 'row' }}>
            {optionalFiles.map((f, i) => (
              <TouchableOpacity key={i} onPress={() => onPress(i)} activeOpacity={0.9} style={{ flex: 1, height: halfSize, marginRight: i === 0 ? GAP : 0 }}>
                <Image source={{ uri: f.url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
              </TouchableOpacity>
            ))}
          </View>
        </View>
      );
    }

    if (optionalFiles.length === 3) {
      return (
        <View style={[styles.imageCollageContainer, { paddingHorizontal: 0 }]}>
          <View style={{ flexDirection: 'row' }}>
            <TouchableOpacity onPress={() => onPress(0)} activeOpacity={0.9} style={{ flex: 2, height: largeWidth, marginRight: GAP }}>
              <Image source={{ uri: optionalFiles[0].url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
            </TouchableOpacity>
            <View style={{ flex: 1, height: largeWidth }}>
              {optionalFiles.slice(1).map((f, i) => (
                <TouchableOpacity key={i} onPress={() => onPress(i + 1)} activeOpacity={0.9} style={{ width: '100%', height: (largeWidth - GAP) / 2, marginBottom: i === 0 ? GAP : 0 }}>
                  <Image source={{ uri: f.url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
                </TouchableOpacity>
              ))}
            </View>
          </View>
        </View>
      );
    }

    const grid = optionalFiles.slice(0, 4);
    const extra = optionalFiles.length - 4;
    return (
      <View style={[styles.imageCollageContainer, { paddingHorizontal: 0 }]}>
        <View style={{ flexDirection: 'row', flexWrap: 'wrap' }}>
          {grid.map((f, i) => (
            <View key={i} style={{ width: '50%', paddingRight: i % 2 === 0 ? GAP : 0, paddingTop: i >= 2 ? GAP : 0 }}>
              <TouchableOpacity onPress={() => onPress(i)} activeOpacity={0.9} style={{ width: '100%', height: halfSize }}>
                <Image source={{ uri: f.url }} style={{ width: '100%', height: '100%' }} resizeMode="cover" />
                {i === 3 && extra > 0 && (
                  <View style={styles.imageOverlay}>
                    <Text style={styles.imageOverlayText}>+{extra}</Text>
                  </View>
                )}
              </TouchableOpacity>
            </View>
          ))}
        </View>
      </View>
    );
  };

  return (
    <TouchableOpacity
      onPress={onPress}
      style={styles.projectCard}
      activeOpacity={0.7}
    >
      <View style={styles.portfolioHeader}>
        <View style={styles.portfolioAvatar}>
          {ownerProfileUrl ? (
            <Image source={{ uri: ownerProfileUrl }} style={styles.portfolioAvatarImage} />
          ) : (
            <View style={styles.portfolioAvatarPlaceholder}>
              <Text style={styles.portfolioAvatarText}>{ownerInitials}</Text>
            </View>
          )}
        </View>
        <View style={styles.portfolioInfo}>
          <Text style={styles.portfolioCompany}>{ownerFullName}</Text>
          <Text style={styles.portfolioUsername}>
            {(project.post_created_at || project.created_at) ? formatDate(project.post_created_at || project.created_at) : ''}
          </Text>
        </View>
        <View style={[
          styles.statusBadge,
          status === 'Open' ? styles.statusOpen : styles.statusClosed
        ]}>
          <Text style={[
            styles.statusText,
            status === 'Open' ? styles.statusTextOpen : styles.statusTextClosed
          ]}>
            {status}
          </Text>
        </View>
      </View>

      <Text style={styles.portfolioTitle} numberOfLines={2}>{project.project_title}</Text>
      <TouchableOpacity onPress={() => onPress()}>
        <Text style={styles.portfolioMoreDetails}>More details...</Text>
      </TouchableOpacity>

      {renderCollage()}

      <Text numberOfLines={2} style={styles.portfolioDescription}>
        {project.project_description}
      </Text>

      <View style={styles.portfolioFooter}>
        <View style={styles.locationBox}>
          <Ionicons name="location-outline" size={14} color={COLORS.textMuted} />
          <Text style={styles.footerText} numberOfLines={1}>
            {project.project_location}
          </Text>
        </View>
        <Text style={styles.budgetText}>
          {formatBudget(project.budget_range_min, project.budget_range_max)}
        </Text>
      </View>

      {deadlineDate && deadlineDate > new Date() && (
        <View style={styles.deadlineRow}>
          <Feather name="clock" size={12} color={isUrgent ? COLORS.error : COLORS.textMuted} />
          <Text style={[styles.deadlineText, isUrgent && styles.urgentText]}>
            Bidding ends: {formatDate(project.bidding_deadline!)}
          </Text>
        </View>
      )}
    </TouchableOpacity>
  );
});

const ReviewCard = memo(({ review }: { review: Review }) => {
  const reviewerName = useMemo(() =>
    review.reviewer_company_name ||
    review.reviewer_name ||
    review.reviewer_display_name ||
    review.reviewer_username ||
    'Anonymous',
    [review]
  );

  return (
    <View style={styles.reviewCard}>
      <View style={styles.reviewHeader}>
        <View style={styles.reviewerInfo}>
          <ImageFallback
            uri={review.reviewer_profile_pic ?
              (review.reviewer_profile_pic.startsWith('http')
                ? review.reviewer_profile_pic
                : `${api_config.base_url}/storage/${review.reviewer_profile_pic}`)
              : undefined}
            defaultImage={defaultOwnerAvatar}
            style={styles.reviewerAvatar}
          />
          <View style={styles.reviewerDetails}>
            <Text style={styles.reviewerName}>{reviewerName}</Text>
            {review.reviewer_username && (
              <Text style={styles.reviewerUsername}>@{review.reviewer_username}</Text>
            )}
          </View>
        </View>
        <View style={styles.ratingContainer}>
          <View style={{ flexDirection: 'row', alignItems: 'center' }}>
            <MaterialIcons name="star" size={14} color={COLORS.star} style={{ marginRight: 4 }} />
            <Text style={styles.ratingText}>{review.rating.toFixed(1)}</Text>
          </View>
          <Text style={styles.reviewDate}>{formatDate(review.created_at)}</Text>
        </View>
      </View>
      <Text style={styles.reviewComment}>{review.comment}</Text>
    </View>
  );
});

// Loading Skeletons
const ProjectCardSkeleton = () => (
  <View style={styles.projectCard}>
    <View style={styles.portfolioHeader}>
      <View style={[styles.portfolioAvatar, styles.skeleton]} />
      <View style={styles.portfolioInfo}>
        <View style={[styles.skeletonText, { width: 150, height: 16, marginBottom: 4 }]} />
        <View style={[styles.skeletonText, { width: 80, height: 12 }]} />
      </View>
    </View>
    <View style={[styles.skeletonText, { width: '60%', height: 16, marginTop: 12, marginBottom: 8 }]} />
    <View style={[styles.skeletonText, { width: 100, height: 14, marginBottom: 12 }]} />
    <View style={[styles.skeleton, { width: '100%', height: 200, borderRadius: 12, marginBottom: 12 }]} />
    <View style={[styles.skeletonText, { width: '100%', height: 40, marginBottom: 12 }]} />
    <View style={styles.portfolioFooter}>
      <View style={[styles.skeletonText, { width: '40%', height: 20 }]} />
      <View style={[styles.skeletonText, { width: '30%', height: 20 }]} />
    </View>
  </View>
);

// Main Component
export default function ViewProfileScreen({ onBack, userData, userToken }) {
  const insets = useSafeAreaInsets();
  const [activeTab, setActiveTab] = useState<TabType>('Posts');
  const [isUploading, setIsUploading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  // Data states
  const [projects, setProjects] = useState<Project[]>([]);
  const [reviews, setReviews] = useState<Review[]>([]);
  const [profilePic, setProfilePic] = useState(userData?.profile_pic);
  const [coverPhoto, setCoverPhoto] = useState(userData?.cover_photo);
  const [userState, setUserData] = useState<UserData>(userData);
  const [ownerInfo, setOwnerInfo] = useState<OwnerInfo | null>(null);
  const [contractorInfo, setContractorInfo] = useState<any | null>(null);
  const [contractorReps, setContractorReps] = useState<any[]>([]);
  const [occupationName, setOccupationName] = useState<string | null>(null);
  const [rating, setRating] = useState<number | null>(null);
  const [projectsDone, setProjectsDone] = useState<number>(0);
  const [ongoingProjects, setOngoingProjects] = useState<number>(0);
  const [activeRoleState, setActiveRoleState] = useState<string | null>(null);
  const [selectedProject, setSelectedProject] = useState<Project | null>(null);
  const [initialImageIndex, setInitialImageIndex] = useState<number>(0);

  const tabs = useMemo<TabType[]>(() => {
    if (activeRoleState === 'contractor') return ['Portfolio', 'Highlights', 'Reviews', 'About'];
    return ['Posts', 'Projects', 'About', 'Reviews'];
  }, [activeRoleState]);

  useEffect(() => {
    if (!tabs.includes(activeTab)) {
      setActiveTab(tabs[0]);
    }
  }, [activeRoleState, activeTab]);

  // Loading states
  const [loading, setLoading] = useState({
    projects: false,
    reviews: false,
    profile: false,
  });

  // Error states
  const [errors, setErrors] = useState({
    projects: null as string | null,
    reviews: null as string | null,
    profile: null as string | null,
  });

  // Memoized values
  const userId = useMemo(() => userState?.user_id, [userState?.user_id]);

  const displayName = useMemo(() => {
    if (activeRoleState === 'contractor' && contractorInfo && contractorInfo.company_name) {
      return contractorInfo.company_name;
    }
    if (ownerInfo) {
      const { first_name = '', middle_name = '', last_name = '' } = ownerInfo;
      return `${first_name}${middle_name ? ' ' + middle_name : ''}${last_name ? ' ' + last_name : ''}`.trim();
    }
    if (userState?.first_name || userState?.last_name) {
      return `${userState.first_name || ''} ${userState.last_name || ''}`.trim();
    }
    return userState?.username || 'Member';
  }, [activeRoleState, contractorInfo, ownerInfo, userState]);

  const userBio = useMemo(() =>
    ownerInfo?.bio || userState?.bio,
    [ownerInfo?.bio, userState?.bio]
  );

  const filteredProjects = useMemo(() => projects.filter(p => (p.project_status || '').toLowerCase() !== 'pending'), [projects]);

  const projectStats = useMemo(() => ({
    total: filteredProjects.length,
    completed: filteredProjects.filter(p => (p.project_status || '').toLowerCase() === 'completed').length,
    ongoing: filteredProjects.filter(p => {
      const s = (p.project_status || '').toLowerCase();
      const post = (p.project_post_status || '').toLowerCase();
      return s === 'in_progress' || s === 'open' || s === 'bidding_closed' || post === 'approved';
    }).length,
  }), [filteredProjects]);

  const activeProjects = useMemo(() =>
    filteredProjects.filter(p => {
      const s = (p.project_status || '').toLowerCase();
      const post = (p.project_post_status || '').toLowerCase();
      return s === 'in_progress' || s === 'open' || s === 'bidding_closed' || post === 'approved';
    }),
    [filteredProjects]
  );

  const ownerVisibleProjects = useMemo(() => {
    if (activeRoleState === 'owner') {
      return filteredProjects;
    }
    return filteredProjects;
  }, [filteredProjects, activeRoleState]);

  const userCity = useMemo(() => {
    const addr = ownerInfo?.address_display || ownerInfo?.address || '';
    if (!addr) return '—';
    const parts = addr.split(',').map(s => s.trim()).filter(Boolean);
    return parts.length >= 2 ? parts[parts.length - 2] : parts[parts.length - 1] || '—';
  }, [ownerInfo?.address_display, ownerInfo?.address]);

  const [contractorCity, setContractorCity] = useState<string>('—');

  useEffect(() => {
    let mounted = true;
    const resolveCity = async () => {
      if (!contractorInfo) {
        if (mounted) setContractorCity('—');
        return;
      }

      // Prefer parsing business_address first (human-readable)
      const addr = contractorInfo.business_address || '';
      if (addr) {
        const parts = String(addr).split(',').map((s: string) => s.trim()).filter(Boolean);
        const parsed = parts.length >= 2 ? parts[parts.length - 2] : (parts[parts.length - 1] || '—');
        if (mounted) setContractorCity(parsed);
        return;
      }

      const permitCity = contractorInfo.business_permit_city || '';
      if (!permitCity) {
        if (mounted) setContractorCity('—');
        return;
      }

      // If business_permit_city is a numeric PSGC code, fetch the city name
      if (/^[0-9]+$/.test(String(permitCity))) {
        try {
          const resp = await auth_service.get_all_cities();
          if (resp?.success && resp.data) {
            const found = (resp.data || []).find((c: any) => String(c.code) === String(permitCity));
            if (found && mounted) {
              setContractorCity(found.name || String(permitCity));
              return;
            }
          }
        } catch (e) {
          console.warn('Failed to resolve PSGC city code to name', e);
        }
        if (mounted) setContractorCity(String(permitCity));
        return;
      }

      // Otherwise assume it's already a name
      if (mounted) setContractorCity(String(permitCity));
    };

    resolveCity();
    return () => { mounted = false; };
  }, [contractorInfo?.business_address, contractorInfo?.business_permit_city]);

  // Helper functions
  const getStorageUrl = useCallback((filePath?: string): string | undefined => {
    if (!filePath) return undefined;
    if (filePath.startsWith('http')) return filePath;
    return `${api_config.base_url}/storage/${filePath}`;
  }, []);

  // Debug: log profile/cover sources and resolved URLs
  useEffect(() => {
    try {
      console.log('[viewProfile] profilePic:', profilePic, 'coverPhoto:', coverPhoto);
      console.log('[viewProfile] resolved profileUrl:', getStorageUrl(profilePic), 'resolved coverUrl:', getStorageUrl(coverPhoto));
      console.log('[viewProfile] userState.user_type:', userState?.user_type, 'activeRoleState:', activeRoleState);
      console.log('[viewProfile] ownerInfo.profile_pic:', ownerInfo?.profile_pic, 'contractorInfo.company_logo:', contractorInfo?.company_logo);
    } catch (e) { /* no-op */ }
  }, [profilePic, coverPhoto, userState?.user_type, activeRoleState, ownerInfo, contractorInfo]);

  const handleError = useCallback((error: any, defaultMessage: string): string => {
    console.error(error);
    return error?.message || defaultMessage;
  }, []);

  // Data fetching
  const fetchProfile = useCallback(async () => {
    const id = userState?.user_id;
    const username = userState?.username;
    if (!id && !username) return;

    try {
      setLoading(prev => ({ ...prev, profile: true }));
      setErrors(prev => ({ ...prev, profile: null }));

      const query = id ? `?user_id=${encodeURIComponent(id)}` : `?username=${encodeURIComponent(username)}`;
      const resp = await api_request(`/api/profile/fetch${query}`);

      if (resp?.success && resp.data) {
        const data = resp.data.data || resp.data;

        if (data.user) {
          setUserData(prev => ({ ...prev, ...data.user }));
        }
        if (data.owner) {
          setOwnerInfo({
            ...data.owner,
            ...(data.address_display ? { address_display: data.address_display } : {})
          });
          setContractorInfo(null);
        }
        if (data.contractor) {
          setContractorInfo(data.contractor);
        }
        if (Array.isArray(data.representatives) && data.representatives.length) {
          setContractorReps(data.representatives);
        } else if (data.representative) {
          setContractorReps([data.representative]);
        } else {
          setContractorReps([]);
        }
        if (typeof data.projects_done !== 'undefined') setProjectsDone(Number(data.projects_done));
        if (typeof data.ongoing_projects !== 'undefined') setOngoingProjects(Number(data.ongoing_projects));
        if (data.role) {
          setActiveRoleState(String(data.role));
        }
        if (data.occupation_name) {
          setOccupationName(data.occupation_name);
        }
        if (data.rating !== undefined && data.rating !== null) {
          setRating(Number(data.rating));
        }
        try {
          const roleArg = data.role ? String(data.role) : undefined;
          fetchReviews(roleArg);
        } catch (e) {}
        // Ensure profile and cover images are populated from available fields.
        // If the response includes a role (preferred_role/current role), respect it:
        // - when role === 'contractor' prefer `contractor.company_logo`/`company_banner`
        // - when role === 'owner' prefer `data.user`/owner images from `users`/`property_owners`
        try {
          const contractorLogo = data.contractor && (data.contractor.company_logo || data.contractor.profile_pic);
          const contractorBanner = data.contractor && (data.contractor.company_banner || data.contractor.cover_photo);

          const roleFromResponse = (data.role || '').toString().toLowerCase();

          let candidateProfile;
          let candidateCover;

          if (roleFromResponse === 'owner') {
            // Strict: prefer user/owner images when preferred role is owner
            candidateProfile =
              (data.user && (data.user.profile_pic || data.user.profilePic)) ||
              (data.owner && (data.owner.profile_pic || data.owner.profilePic)) ||
              data.owner_profile_pic || data.owner_profile || undefined;

            candidateCover =
              (data.user && (data.user.cover_photo || data.user.coverPhoto)) ||
              (data.owner && (data.owner.cover_photo || data.owner.coverPhoto)) ||
              data.cover_photo || undefined;
          } else if (roleFromResponse === 'contractor') {
            // Prefer contractor media when preferred role is contractor
            candidateProfile =
              contractorLogo ||
              (data.user && (data.user.profile_pic || data.user.profilePic)) ||
              (data.owner && (data.owner.profile_pic || data.owner.profilePic)) ||
              data.owner_profile_pic || data.owner_profile || undefined;

            candidateCover =
              contractorBanner ||
              (data.user && (data.user.cover_photo || data.user.coverPhoto)) ||
              (data.owner && (data.owner.cover_photo || data.owner.coverPhoto)) ||
              data.cover_photo || undefined;
          } else {
            // No explicit preferred role: fall back to previous behavior (prefer contractor if present)
            candidateProfile =
              contractorLogo ||
              (data.user && (data.user.profile_pic || data.user.profilePic)) ||
              (data.owner && (data.owner.profile_pic || data.owner.profilePic)) ||
              data.owner_profile_pic || data.owner_profile || undefined;

            candidateCover =
              contractorBanner ||
              (data.user && (data.user.cover_photo || data.user.coverPhoto)) ||
              (data.owner && (data.owner.cover_photo || data.owner.coverPhoto)) ||
              data.cover_photo || undefined;
          }

          if (candidateProfile) setProfilePic(candidateProfile);
          if (candidateCover) setCoverPhoto(candidateCover);
        } catch (e) {
          // non-fatal
        }
      }
    } catch (e) {
      setErrors(prev => ({ ...prev, profile: handleError(e, 'Failed to load profile') }));
    } finally {
      setLoading(prev => ({ ...prev, profile: false }));
    }
  }, [userState?.user_id, userState?.username, handleError]);

  const fetchProjects = useCallback(async () => {
    if (!userId) return;

    try {
      setLoading(prev => ({ ...prev, projects: true }));
      setErrors(prev => ({ ...prev, projects: null }));

      const roleQuery = activeRoleState ? `&role=${encodeURIComponent(activeRoleState)}` : '';
      const query = `?user_id=${encodeURIComponent(userId)}${roleQuery}`;
      const resp = await api_request(`/api/profile/fetch${query}`);

      if (resp?.success && resp.data) {
        const payload = resp.data.data || resp.data || {};
        let projectsData = payload.projects || payload.data?.projects || [];
        if (!Array.isArray(projectsData)) projectsData = [];

        const normalized = projectsData.map((p: any) => {
          const copy = { ...p };
          if (copy.files && typeof copy.files === 'string') {
            try {
              copy.files = JSON.parse(copy.files);
            } catch (e) {
              copy.files = [copy.files];
            }
          }
          return copy;
        });

        // Enrich projects with owner display name and profile picture where missing.
        const enriched = await Promise.all(normalized.map(async (proj: any) => {
          // Already present values
          let ownerDisplay = proj.owner_full_name || proj.owner_name || proj.owner?.full_name || proj.user?.full_name || null;
          let ownerPic = proj.owner_profile_pic || proj.user?.profile_pic || proj.profile_pic || null;

          // Try flattened owner fields
          if (!ownerDisplay) {
            const first = proj.owner_first_name || proj.first_name || proj.owner?.first_name || proj.user?.first_name || '';
            const middle = proj.owner_middle_name || proj.middle_name || proj.owner?.middle_name || proj.user?.middle_name || '';
            const last = proj.owner_last_name || proj.last_name || proj.owner?.last_name || proj.user?.last_name || '';
            const full = [first, middle, last].filter(Boolean).join(' ').trim();
            if (full) ownerDisplay = full;
          }

          // If still missing, try to fetch public profile when owner_user_id is present
          const possibleOwnerUserId = proj.owner_user_id || proj.owner_user || proj.owner?.user_id || proj.user?.user_id || null;
          if ((!ownerDisplay || !ownerPic) && possibleOwnerUserId) {
            try {
              const profileResp = await api_request(`/api/profile/fetch?user_id=${encodeURIComponent(possibleOwnerUserId)}`);
              const profileData = profileResp?.data?.data || profileResp?.data || profileResp;
              const u = profileData?.user || profileData;
              if (u) {
                if (!ownerDisplay) {
                  const fn = u.first_name || u.fname || '';
                  const mn = u.middle_name || u.mname || '';
                  const ln = u.last_name || u.lname || '';
                  ownerDisplay = [fn, mn, ln].filter(Boolean).join(' ').trim() || u.username || null;
                }
                if (!ownerPic) {
                  ownerPic = u.profile_pic || u.profilePic || null;
                }
              }
            } catch (e) {
              console.warn('Failed to fetch owner profile for project', proj.project_id, e?.message || e);
            }
          }

          // If still missing owner info, try public project endpoint which returns owner_full_name and owner_profile_pic
          if ((!ownerDisplay || !ownerPic) && proj.project_id) {
            try {
              const pub = await api_request(`/api/projects/${encodeURIComponent(proj.project_id)}/public`);
              const pdata = pub?.data?.data || pub?.data || pub;
              if (pdata) {
                if (!ownerDisplay) ownerDisplay = pdata.owner_full_name || pdata.owner_name || null;
                if (!ownerPic) ownerPic = pdata.owner_profile_pic || pdata.owner_profile || null;
              }
            } catch (e) {
              console.warn('Failed to fetch public project details for', proj.project_id, e?.message || e);
            }
          }

          // Final fallbacks
          if (!ownerDisplay) ownerDisplay = proj.poster || proj.posted_by || proj.poster_name || proj.username || 'Property Owner';
          proj._owner_display = ownerDisplay;
          proj._owner_profile_pic = ownerPic;

          return proj;
        }));

        setProjects(enriched);
      } else {
        setErrors(prev => ({ ...prev, projects: resp?.message || 'Failed to load projects' }));
      }
    } catch (err) {
      setErrors(prev => ({ ...prev, projects: handleError(err, 'Failed to load projects') }));
    } finally {
      setLoading(prev => ({ ...prev, projects: false }));
    }
  }, [userId, handleError, activeRoleState]);

  const fetchReviews = useCallback(async (roleArg?: string) => {
    if (!userId) return;

    try {
      setLoading(prev => ({ ...prev, reviews: true }));
      setErrors(prev => ({ ...prev, reviews: null }));

      const roleToUse = roleArg ?? activeRoleState;
      const roleQuery = roleToUse ? `&role=${encodeURIComponent(roleToUse)}` : '';
      const response = await fetch(
        `${api_config.base_url}/api/profile/reviews?reviewee_user_id=${encodeURIComponent(userId)}${roleQuery}`
      );
      const data = await response.json();

      if (data?.success) {
        const payload = data.data || {};
        const reviewsData = Array.isArray(payload.reviews) ? payload.reviews : (Array.isArray(payload) ? payload : []);
        setReviews(reviewsData);
        if (payload.stats && typeof payload.stats.avg_rating !== 'undefined' && payload.stats.avg_rating !== null) {
          setRating(Number(payload.stats.avg_rating));
        }
      } else {
        setErrors(prev => ({ ...prev, reviews: data.message || 'Failed to load reviews' }));
      }
    } catch (e) {
      setErrors(prev => ({ ...prev, reviews: handleError(e, 'Failed to load reviews') }));
    } finally {
      setLoading(prev => ({ ...prev, reviews: false }));
    }
  }, [userId, handleError, activeRoleState]);

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    try {
      await Promise.all([
        fetchProfile(),
        fetchProjects(),
        fetchReviews(),
      ]);
    } catch (e) {
      console.error('Refresh failed:', e);
    } finally {
      setRefreshing(false);
    }
  }, [fetchProfile, fetchProjects, fetchReviews]);

  // Image upload
  const pickImage = useCallback(async (type: 'profile' | 'cover') => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert(
        'Permission Denied',
        'Please allow access to your photo library to change your photo.'
      );
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'],
      allowsEditing: true,
      aspect: type === 'profile' ? [1, 1] : [16, 9],
      quality: 0.8,
    });

    if (!result.canceled && result.assets[0]) {
      uploadImage(result.assets[0].uri, type);
    }
  }, []);

  const uploadImage = useCallback(async (uri: string, type: 'profile' | 'cover') => {
    setIsUploading(true);

    const formData = new FormData();
    const filename = uri.split('/').pop() || 'image.jpg';
    const match = /\.(\w+)$/.exec(filename);
    const fileType = match ? `image/${match[1]}` : 'image/jpeg';

    // Determine the effective role for deciding which DB columns to update.
    // Mapping:
    // - Contractor => `company_logo` / `company_banner` (contractors table)
    // - Property Owner => `profile_pic` / `cover_photo` (users table)
    // - Both => depends on `activeRoleState` (preferred/current role)
    const rawUserType = String(userState?.user_type || '').toLowerCase();
    let effectiveRole = (activeRoleState || '').toString().toLowerCase();
    if (!effectiveRole) {
      if (rawUserType === 'both') {
        effectiveRole = (userState?.preferred_role || 'owner').toString().toLowerCase();
      } else {
        effectiveRole = rawUserType.includes('contractor') ? 'contractor' : 'owner';
      }
    }

    let fieldName: string;
    if (effectiveRole === 'contractor') {
      fieldName = type === 'profile' ? 'company_logo' : 'company_banner';
    } else {
      fieldName = type === 'profile' ? 'profile_pic' : 'cover_photo';
    }

    // Append role for backend awareness (optional) and the file under the chosen field
    formData.append('role', effectiveRole);
    formData.append(fieldName, {
      uri: Platform.OS === 'ios' ? uri.replace('file://', '') : uri,
      name: filename,
      type: fileType,
    } as any);

    console.log('[uploadImage] preparing upload', { effectiveRole, fieldName, filename, fileType, uri });

    try {
      // If a token wasn't passed in props, warn — api_request will still try to read stored token.
      if (!userToken) console.warn('[uploadImage] no userToken prop provided — api_request will use stored token if available');

      // Use the shared api_request helper so stored auth token is applied consistently.
      const resp = await api_request('/api/user/profile', {
        method: 'POST',
        body: formData,
      });

      console.log('[uploadImage] api_request response', resp);

      if (resp?.success) {
        const wrapper = resp.data || resp;
        const inner = wrapper.data ?? wrapper.user ?? wrapper;
        const userObj = inner.user ?? inner;
        const contractorObj = inner.contractor ?? null;

        // Update local cached states
        if (userObj && typeof userObj === 'object') setUserData(prev => ({ ...(prev || {}), ...(userObj || {}) }));
        if (contractorObj && typeof contractorObj === 'object') setContractorInfo(contractorObj);

        // Prefer user profile/cover, then contractor media, then top-level fields
        const returnedPath = userObj?.profile_pic || userObj?.cover_photo || contractorObj?.company_logo || contractorObj?.company_banner || inner?.profile_pic || inner?.cover_photo || inner?.company_logo || inner?.company_banner || (wrapper.path ?? null);
        if (returnedPath) {
          if (type === 'profile') setProfilePic(returnedPath);
          else setCoverPhoto(returnedPath);
        }
        Alert.alert('Success', 'Photo updated successfully');
      } else {
        console.error('[uploadImage] upload failed', resp);
        Alert.alert('Error', resp?.message || 'Failed to update photo');
      }
    } catch (error) {
      console.error('[uploadImage] network/error', error);
      Alert.alert('Error', 'Network error. Please try again.');
    } finally {
      setIsUploading(false);
    }
  }, [userToken]);

  // Effects
  useEffect(() => {
    fetchProfile();
  }, [fetchProfile]);

  useEffect(() => {
    const projectTabs = ['Posts', 'Projects', 'Portfolio', 'Highlights'];
    if (projectTabs.includes(activeTab) && !loading.profile) {
      fetchProjects();
    }
  }, [activeTab, fetchProjects]);

  useEffect(() => {
    if (activeTab === 'Reviews') {
      fetchReviews();
    }
  }, [activeTab, fetchReviews]);

  // Render helpers
  const renderHeader = () => (
    <>
      <View style={[styles.header, { paddingTop: insets.top }]}>
        <TouchableOpacity onPress={onBack} style={styles.backButton}>
          <Feather name="chevron-left" size={24} color={COLORS.text} />
          <Text style={styles.backText}>Back</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Profile</Text>
        <TouchableOpacity style={styles.headerIconBtn}>
          <Feather name="more-horizontal" size={24} color={COLORS.text} />
        </TouchableOpacity>
      </View>

      <View style={styles.heroSection}>
        <View style={styles.coverWrapper}>
          <ImageFallback
            uri={getStorageUrl(coverPhoto)}
            defaultImage={defaultCoverPhoto}
            style={styles.coverImg}
            resizeMode="cover"
          />
          <TouchableOpacity
            style={styles.editCoverBtn}
            onPress={() => pickImage('cover')}
            hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
          >
            <Feather name="camera" size={18} color={COLORS.surface} />
          </TouchableOpacity>
        </View>

        <View style={styles.profileInfoContainer}>
          <View style={styles.avatarWrapper}>
            <ImageFallback
              uri={getStorageUrl(profilePic)}
              defaultImage={activeRoleState === 'contractor' ? defaultContractorAvatar : defaultOwnerAvatar}
              style={styles.avatarImg}
              resizeMode="cover"
            />
            <TouchableOpacity
              style={styles.editAvatarBtn}
              onPress={() => pickImage('profile')}
              hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
            >
              <Feather name="edit-2" size={14} color={COLORS.surface} />
            </TouchableOpacity>
          </View>

          <Text style={styles.profileName}>{displayName}</Text>

          {userState?.username && (
            <Text style={styles.username}>@{userState.username}</Text>
          )}

          <View style={styles.ratingLocationRow}>
            <MaterialIcons name="star" size={16} color={COLORS.star} />
            <Text style={styles.ratingText}>{rating !== null ? rating.toFixed(1) : '0'} Rating</Text>
            <Text style={styles.dotSeparator}>•</Text>
            <Text style={styles.locationText}>{
              activeRoleState === 'contractor' ? contractorCity : userCity
            }</Text>
          </View>

          <View style={styles.statsGrid}>
            <View style={styles.statItem}>
              <Text style={styles.statValue}>{rating !== null ? rating.toFixed(1) : '0'}</Text>
              <Text style={styles.statLabel}>Rating</Text>
            </View>
            <View style={styles.statDivider} />
            <View style={styles.statItem}>
              <Text style={styles.statValue}>{projectStats.total}</Text>
              <Text style={styles.statLabel}>{activeRoleState === 'contractor' ? 'Projects' : 'Posts'}</Text>
            </View>
            <View style={styles.statDivider} />
            <View style={styles.statItem}>
              <Text style={styles.statValue}>{reviews.length}</Text>
              <Text style={styles.statLabel}>Reviews</Text>
            </View>
          </View>

          {userBio && (
            <Text style={styles.profileBio} numberOfLines={3}>
              {userBio}
            </Text>
          )}
        </View>
      </View>

      <View style={styles.tabsContainer}>
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.tabsScrollContent}
        >
          {tabs.map((tab) => (
            <TouchableOpacity
              key={tab}
              onPress={() => setActiveTab(tab)}
              style={[styles.tab, activeTab === tab && styles.tabActive]}
            >
              <Text style={[styles.tabText, activeTab === tab && styles.tabTextActive]}>
                {tab}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>
    </>
  );

  const renderPostsTab = () => {
    if (loading.projects) {
      return (
        <View style={styles.tabContent}>
          <ProjectCardSkeleton />
          <ProjectCardSkeleton />
          <ProjectCardSkeleton />
        </View>
      );
    }

    if (errors.projects) {
      return (
        <View style={styles.emptyState}>
          <Feather name="alert-circle" size={48} color={COLORS.border} />
          <Text style={styles.emptyTitle}>Failed to load projects</Text>
          <Text style={styles.emptySubtext}>{errors.projects}</Text>
          <TouchableOpacity style={styles.retryButton} onPress={fetchProjects}>
            <Text style={styles.retryButtonText}>Retry</Text>
          </TouchableOpacity>
        </View>
      );
    }

    if (ownerVisibleProjects.length === 0) {
      return (
        <View style={styles.emptyState}>
          <Feather name="folder" size={48} color={COLORS.border} />
          <Text style={styles.emptyTitle}>No projects yet</Text>
          <Text style={styles.emptySubtext}>This user hasn't posted any projects yet.</Text>
        </View>
      );
    }

    return (
      <View style={styles.tabContent}>
        {ownerVisibleProjects.map(project => (
          <ProjectCard
            key={project.project_id}
            project={project}
            ownerUser={{ ...(ownerInfo || userState), profile_pic: ownerInfo?.profile_pic || userState?.profile_pic }}
            onPress={(index?: number) => {
              setSelectedProject(project);
              setInitialImageIndex(index || 0);
            }}
          />
        ))}
      </View>
    );
  };

  const renderProjectsTab = () => {
    if (loading.projects) {
      return (
        <View style={styles.tabContent}>
          <ProjectCardSkeleton />
          <ProjectCardSkeleton />
          <ProjectCardSkeleton />
        </View>
      );
    }

    if (errors.projects) {
      return (
        <View style={styles.emptyState}>
          <Feather name="alert-circle" size={48} color={COLORS.border} />
          <Text style={styles.emptyTitle}>Failed to load projects</Text>
          <Text style={styles.emptySubtext}>{errors.projects}</Text>
          <TouchableOpacity style={styles.retryButton} onPress={fetchProjects}>
            <Text style={styles.retryButtonText}>Retry</Text>
          </TouchableOpacity>
        </View>
      );
    }

    if (activeProjects.length === 0) {
      return (
        <View style={styles.emptyState}>
          <Feather name="briefcase" size={48} color={COLORS.border} />
          <Text style={styles.emptyTitle}>No active projects</Text>
          <Text style={styles.emptySubtext}>There are no active projects at the moment.</Text>
        </View>
      );
    }

    return (
      <View style={styles.tabContent}>
        {activeProjects.map(project => (
          <ProjectCard
            key={project.project_id}
            project={project}
            ownerUser={{ ...(ownerInfo || userState), profile_pic: ownerInfo?.profile_pic || userState?.profile_pic }}
            onPress={(index?: number) => {
              setSelectedProject(project);
              setInitialImageIndex(index || 0);
            }}
          />
        ))}
      </View>
    );
  };

  const renderAboutTab = () => (
    <View style={styles.tabContent}>
      <View style={styles.aboutCard}>
        <Text style={styles.aboutTitle}>About</Text>

        {activeRoleState === 'contractor' && contractorInfo ? (
          <>
            <View style={styles.aboutSection}>
              <Text style={styles.aboutLabel}>Company Name</Text>
              <Text style={styles.aboutValue}>{contractorInfo.company_name || '—'}</Text>
            </View>

            <View style={styles.aboutSection}>
              <Text style={styles.aboutLabel}>Rating & City</Text>
              <Text style={styles.aboutValue}>
                {rating ? (
                  <Text>
                    <Text style={styles.ratingNumber}>{rating.toFixed(1)}</Text>
                    <Text> · </Text>
                  </Text>
                ) : ''}
                {contractorCity}
              </Text>
            </View>

            <View style={styles.aboutSection}>
              <Text style={styles.aboutLabel}>Bio</Text>
              <Text style={styles.aboutValue}>{contractorInfo.bio || contractorInfo.company_description || 'This contractor hasn\'t added a bio yet.'}</Text>
            </View>

            <View style={styles.aboutSection}>
              <Text style={styles.aboutLabel}>Experience</Text>
              <Text style={styles.aboutValue}>{contractorInfo.years_of_experience ? `${contractorInfo.years_of_experience} years` : '—'}</Text>
            </View>

            <View style={styles.aboutSection}>
              <Text style={styles.aboutLabel}>Services Offered</Text>
              <Text style={styles.aboutValue}>{contractorInfo.services_offered || '—'}</Text>
            </View>

            <View style={styles.highlightsGrid}>
              <View style={styles.highlightCard}>
                <View style={[styles.highlightIcon, { backgroundColor: COLORS.successLight }]}>
                  <Feather name="check-circle" size={24} color={COLORS.success} />
                </View>
                <Text style={styles.highlightValue}>{projectsDone || contractorInfo.completed_projects || 0}</Text>
                <Text style={styles.highlightLabel}>Projects Done</Text>
              </View>

              <View style={styles.highlightCard}>
                <View style={[styles.highlightIcon, { backgroundColor: COLORS.primaryLight }]}>
                  <Feather name="clock" size={24} color={COLORS.primary} />
                </View>
                <Text style={styles.highlightValue}>{ongoingProjects}</Text>
                <Text style={styles.highlightLabel}>Ongoing</Text>
              </View>
            </View>

            {contractorReps && contractorReps.length > 0 && (
              <View style={styles.aboutSection}>
                <Text style={styles.aboutLabel}>Representative</Text>
                {contractorReps.map((rep, idx) => {
                  const img = rep.profile_pic || rep.profilePic || rep.profilePicPath || null;
                  const name = rep.full_name || (`${rep.authorized_rep_fname || ''}${rep.authorized_rep_mname ? ' ' + rep.authorized_rep_mname : ''}${rep.authorized_rep_lname ? ' ' + rep.authorized_rep_lname : ''}`).trim();
                  return (
                    <View key={idx} style={[styles.repCard, idx > 0 && { marginTop: 12 }]}>
                      <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                        <ImageFallback
                          uri={img ? (String(img).startsWith('http') ? String(img) : `${api_config.base_url}/storage/${String(img)}`) : undefined}
                          defaultImage={defaultOwnerAvatar}
                          style={styles.repAvatar}
                        />
                        <View style={{ marginLeft: 12, flex: 1 }}>
                          <Text style={styles.repName}>{name || '—'}</Text>
                          <Text style={styles.aboutValue}>{rep.role || '—'}</Text>
                        </View>
                      </View>
                    </View>
                  );
                })}
              </View>
            )}
          </>
        ) : (
          <>
            <View style={styles.aboutSection}>
              <Text style={styles.aboutLabel}>Full Name</Text>
              <Text style={styles.aboutValue}>{displayName}</Text>
            </View>

            <View style={styles.aboutSection}>
              <Text style={styles.aboutLabel}>Location</Text>
              <Text style={styles.aboutValue}>
                {rating ? (
                  <Text>
                    <Text style={styles.ratingNumber}>{rating.toFixed(1)}</Text>
                    <Text> · {userCity}</Text>
                  </Text>
                ) : userCity}
              </Text>
            </View>

            <View style={styles.aboutSection}>
              <Text style={styles.aboutLabel}>Bio</Text>
              <Text style={styles.aboutValue}>
                {userBio || 'This user hasn\'t added a bio yet.'}
              </Text>
            </View>

            <View style={styles.aboutSection}>
              <Text style={styles.aboutLabel}>Occupation</Text>
              <Text style={styles.aboutValue}>
                {occupationName || ownerInfo?.occupation_name || ownerInfo?.occupation_id || userState?.occupation_name || '—'}
              </Text>
            </View>

            <View style={styles.highlightsGrid}>
              <View style={styles.highlightCard}>
                <View style={[styles.highlightIcon, { backgroundColor: COLORS.successLight }]}>
                  <Feather name="check-circle" size={24} color={COLORS.success} />
                </View>
                <Text style={styles.highlightValue}>{projectStats.completed}</Text>
                <Text style={styles.highlightLabel}>Completed</Text>
              </View>

              <View style={styles.highlightCard}>
                <View style={[styles.highlightIcon, { backgroundColor: COLORS.primaryLight }]}>
                  <Feather name="clock" size={24} color={COLORS.primary} />
                </View>
                <Text style={styles.highlightValue}>{projectStats.ongoing}</Text>
                <Text style={styles.highlightLabel}>Ongoing</Text>
              </View>
            </View>
          </>
        )}
      </View>
    </View>
  );

  const renderReviewsTab = () => {
    if (loading.reviews) {
      return (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
        </View>
      );
    }

    if (errors.reviews) {
      return (
        <View style={styles.emptyState}>
          <Feather name="alert-circle" size={48} color={COLORS.border} />
          <Text style={styles.emptyTitle}>Failed to load reviews</Text>
          <Text style={styles.emptySubtext}>{errors.reviews}</Text>
          <TouchableOpacity style={styles.retryButton} onPress={fetchReviews}>
            <Text style={styles.retryButtonText}>Retry</Text>
          </TouchableOpacity>
        </View>
      );
    }

    if (reviews.length === 0) {
      return (
        <View style={styles.emptyState}>
          <Feather name="message-circle" size={48} color={COLORS.border} />
          <Text style={styles.emptyTitle}>No reviews yet</Text>
          <Text style={styles.emptySubtext}>Be the first to leave a review.</Text>
        </View>
      );
    }

    return (
      <View style={styles.tabContent}>
        <View style={styles.reviewsSummary}>
          <View style={styles.ratingBig}>
            <Text style={styles.ratingBigValue}>{rating?.toFixed(1) || '0'}</Text>
            <View style={styles.starsRow}>
              {[1, 2, 3, 4, 5].map((star) => (
                <MaterialIcons
                  key={star}
                  name="star"
                  size={20}
                  color={star <= (rating || 0) ? COLORS.star : COLORS.border}
                />
              ))}
            </View>
            <Text style={styles.reviewsCountText}>
              Based on {reviews.length} {reviews.length === 1 ? 'review' : 'reviews'}
            </Text>
          </View>
        </View>
        {reviews.map(review => (
          <ReviewCard key={review.review_id} review={review} />
        ))}
      </View>
    );
  };

  const renderHighlightsTab = () => {
    return (
      <View style={styles.tabContent}>
        <View style={styles.highlightsGrid}>
          <View style={styles.highlightCard}>
            <View style={[styles.highlightIcon, { backgroundColor: COLORS.primaryLight }]}>
              <Feather name="award" size={24} color={COLORS.primary} />
            </View>
            <Text style={styles.highlightValue}>{contractorInfo?.years_of_experience || 0}</Text>
            <Text style={styles.highlightLabel}>Years Experience</Text>
          </View>

          <View style={styles.highlightCard}>
            <View style={[styles.highlightIcon, { backgroundColor: COLORS.successLight }]}>
              <Feather name="check-circle" size={24} color={COLORS.success} />
            </View>
            <Text style={styles.highlightValue}>{projectsDone || contractorInfo?.completed_projects || 0}</Text>
            <Text style={styles.highlightLabel}>Projects Completed</Text>
          </View>

          <View style={styles.highlightCard}>
            <View style={[styles.highlightIcon, { backgroundColor: COLORS.warningLight }]}>
              <MaterialIcons name="star" size={24} color={COLORS.star} />
            </View>
            <Text style={styles.highlightValue}>{rating?.toFixed(1) || '0'}</Text>
            <Text style={styles.highlightLabel}>Average Rating</Text>
          </View>

          <View style={styles.highlightCard}>
            <View style={[styles.highlightIcon, { backgroundColor: COLORS.infoLight }]}>
              <Feather name="message-square" size={24} color={COLORS.info} />
            </View>
            <Text style={styles.highlightValue}>{reviews.length}</Text>
            <Text style={styles.highlightLabel}>Client Reviews</Text>
          </View>
        </View>

        {contractorInfo?.services_offered && (
          <View style={styles.servicesSection}>
            <Text style={styles.servicesSectionTitle}>Services Offered</Text>
            <Text style={styles.servicesText}>{contractorInfo.services_offered}</Text>
          </View>
        )}
      </View>
    );
  };

  const renderPortfolioTab = () => renderPostsTab();

  const renderContent = () => {
    switch (activeTab) {
      case 'Posts':
      case 'Portfolio':
        return renderPortfolioTab();
      case 'Projects':
        return renderProjectsTab();
      case 'Highlights':
        return renderHighlightsTab();
      case 'About':
        return renderAboutTab();
      case 'Reviews':
        return renderReviewsTab();
      default:
        return null;
    }
  };

  // If viewing a project detail, show ProjectPostDetail screen
  if (selectedProject) {
    return (
      <ProjectPostDetail
        project={selectedProject}
        userRole={'owner'}
        canBid={false}
        initialImageIndex={initialImageIndex}
        onClose={() => { setSelectedProject(null); setInitialImageIndex(0); }}
      />
    );
  }

  return (
    <View style={styles.container}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.surface} />

      {isUploading && (
        <View style={styles.loadingOverlay}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Uploading...</Text>
        </View>
      )}

      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            colors={[COLORS.primary]}
            tintColor={COLORS.primary}
          />
        }
      >
        {renderHeader()}
        {renderContent()}
        <View style={{ height: 24 }} />
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  scrollContent: {
    paddingBottom: 0,
  },
  loadingOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.5)',
    zIndex: 1000,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 12,
    color: COLORS.surface,
    fontSize: 16,
    fontWeight: '600',
  },

  // Header
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingBottom: 12,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
  },
  backButton: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 8,
  },
  backText: {
    fontSize: 16,
    color: COLORS.text,
    marginLeft: 4,
  },
  headerIconBtn: {
    padding: 8,
  },

  // Hero Section
  heroSection: {
    backgroundColor: COLORS.surface,
    borderBottomLeftRadius: 30,
    borderBottomRightRadius: 30,
    paddingBottom: 24,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 3,
  },
  coverWrapper: {
    height: COVER_HEIGHT,
    width: '100%',
    position: 'relative',
  },
  coverImg: {
    width: '100%',
    height: '100%',
  },
  editCoverBtn: {
    position: 'absolute',
    bottom: 16,
    right: 16,
    backgroundColor: 'rgba(0,0,0,0.5)',
    padding: 10,
    borderRadius: 24,
    borderWidth: 2,
    borderColor: COLORS.surface,
  },
  profileInfoContainer: {
    alignItems: 'center',
    marginTop: -AVATAR_SIZE / 2,
    paddingHorizontal: 20,
  },
  avatarWrapper: {
    width: AVATAR_SIZE,
    height: AVATAR_SIZE,
    borderRadius: AVATAR_SIZE / 2,
    backgroundColor: COLORS.surface,
    padding: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
  },
  avatarImg: {
    width: '100%',
    height: '100%',
    borderRadius: AVATAR_SIZE / 2,
  },
  editAvatarBtn: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    backgroundColor: COLORS.primary,
    padding: 8,
    borderRadius: 20,
    borderWidth: 2,
    borderColor: COLORS.surface,
  },
  profileName: {
    fontSize: 24,
    fontWeight: '900',
    color: COLORS.text,
    marginTop: 12,
    textAlign: 'center',
  },
  username: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginTop: 2,
    marginBottom: 8,
  },
  ratingLocationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
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
  statsGrid: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    width: '100%',
    marginBottom: 16,
  },
  statItem: {
    alignItems: 'center',
    paddingHorizontal: 24,
  },
  statDivider: {
    width: 1,
    height: 30,
    backgroundColor: COLORS.borderLight,
  },
  statValue: {
    fontSize: 20,
    fontWeight: '800',
    color: COLORS.text,
  },
  statLabel: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginTop: 4,
  },
  profileBio: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
    paddingHorizontal: 16,
  },

  // Tabs
  tabsContainer: {
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    marginTop: 8,
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
    padding: 0,
    width: '100%',
  },

  // Portfolio/Project Card - Updated to match CheckProfile design
  projectCard: {
    backgroundColor: COLORS.surface,
    marginBottom: 0,
    paddingHorizontal: 16,
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
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
  portfolioTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  portfolioMoreDetails: {
    fontSize: 14,
    color: COLORS.primary,
    marginBottom: 12,
  },
  portfolioDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginTop: 12,
    marginBottom: 12,
  },
  portfolioFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
    paddingTop: 12,
  },

  // Status Badge
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    marginLeft: 8,
  },
  statusOpen: {
    backgroundColor: COLORS.successLight,
  },
  statusClosed: {
    backgroundColor: '#FEE2E2',
  },
  statusText: {
    fontSize: 12,
    fontWeight: '600',
  },
  statusTextOpen: {
    color: COLORS.success,
  },
  statusTextClosed: {
    color: COLORS.error,
  },

  // Location and Budget
  locationBox: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  footerText: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginLeft: 6,
    flex: 1,
  },
  budgetText: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.primary,
    marginLeft: 12,
  },
  deadlineRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
  },
  deadlineText: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginLeft: 6,
  },
  urgentText: {
    color: COLORS.error,
    fontWeight: '600',
  },

  // Review Card
  reviewCard: {
    backgroundColor: COLORS.surface,
    padding: 16,
    marginBottom: 8,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  reviewHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  reviewerInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  reviewerAvatar: {
    width: 44,
    height: 44,
    borderRadius: 22,
    marginRight: 12,
  },
  reviewerDetails: {
    flex: 1,
  },
  reviewerName: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
  },
  reviewerUsername: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginTop: 2,
  },
  ratingContainer: {
    alignItems: 'flex-end',
  },
  ratingText: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
  },
  reviewDate: {
    fontSize: 11,
    color: COLORS.textMuted,
    marginTop: 2,
  },
  reviewComment: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
  },

  // About Card
  aboutCard: {
    backgroundColor: COLORS.surface,
    padding: 20,
  },
  aboutTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 16,
  },
  aboutSection: {
    marginBottom: 20,
  },
  aboutLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.textSecondary,
    marginBottom: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  aboutValue: {
    fontSize: 15,
    color: COLORS.text,
    lineHeight: 22,
  },
  ratingNumber: {
    color: COLORS.primary,
    fontWeight: '600',
  },

  // Highlights Grid
  highlightsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    marginTop: 8,
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

  // Reviews Summary
  reviewsSummary: {
    alignItems: 'center',
    paddingVertical: 24,
    backgroundColor: COLORS.background,
    marginBottom: 16,
    marginHorizontal: 16,
    borderRadius: 16,
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

  // Representative
  repRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
  },
  repCard: {
    borderWidth: 1,
    borderColor: COLORS.primary,
    borderRadius: 12,
    padding: 14,
    backgroundColor: COLORS.surface,
  },
  repAvatar: {
    width: 56,
    height: 56,
    borderRadius: 28,
  },
  repName: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 2,
  },

  // Empty State
  emptyState: {
    alignItems: 'center',
    paddingVertical: 60,
    paddingHorizontal: 20,
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
  },

  // Error states
  errorText: {
    fontSize: 15,
    color: COLORS.error,
    textAlign: 'center',
    marginTop: 16,
    marginBottom: 8,
  },
  retryButton: {
    marginTop: 16,
    paddingHorizontal: 24,
    paddingVertical: 10,
    backgroundColor: COLORS.primary,
    borderRadius: 20,
  },
  retryButtonText: {
    color: COLORS.surface,
    fontSize: 14,
    fontWeight: '600',
  },

  // Image Collage
  imageCollageContainer: {
    marginTop: 12,
    marginBottom: 12,
    overflow: 'hidden',
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
    color: COLORS.surface,
    fontSize: 24,
    fontWeight: 'bold',
  },

  // Skeletons
  skeleton: {
    backgroundColor: COLORS.border,
  },
  skeletonText: {
    backgroundColor: COLORS.border,
    borderRadius: 4,
  },
});
