// @ts-nocheck
import React, { useState, useEffect, useCallback, useMemo, memo, useRef } from 'react';
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
import ImageFallback from '../../components/imageFallback';
import ProjectPostDetail from './projectPostDetail';
import CreateShowcase from './createShowcase';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import * as ImagePicker from 'expo-image-picker';
import { api_config, api_request } from '../../config/api';
import { storage_service } from '../../utils/storage';
import { auth_service } from '../../services/auth_service';
import { profile_service } from '../../services/profile_service';
import { highlightService } from '../../services/highlightService';
import { post_service } from '../../services/post_service';
import { useContractorAuth } from '../../hooks/useContractorAuth';
import ShowcasePostDetail from './showcasePostDetail';
import ReportPostModal from '../../components/reportPostModal';

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
    <View style={styles.projectCard}>
      {/* ── Header: owner info (left) · deadline badge + status (right) ── */}
      <View style={styles.pcHeader}>
        <View style={styles.pcOwnerInfo}>
          {ownerProfileUrl ? (
            <ImageFallback
              uri={ownerProfileUrl}
              defaultImage={defaultOwnerAvatar}
              style={styles.pcOwnerAvatar}
              resizeMode="cover"
            />
          ) : (
            <ImageFallback
              uri={undefined}
              defaultImage={defaultOwnerAvatar}
              style={styles.pcOwnerAvatar}
              resizeMode="cover"
            />
          )}
          <View>
            <Text style={styles.pcOwnerName}>{ownerFullName}</Text>
            <Text style={styles.pcPostDate}>
              {(project.post_created_at || project.created_at) ? formatDate(project.post_created_at || project.created_at) : ''}
            </Text>
          </View>
        </View>
        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
          {deadlineDate && deadlineDate > new Date() && (
            <View style={[styles.pcDeadlineBadge, isUrgent && styles.pcDeadlineUrgent]}>
              <MaterialIcons name="access-time" size={13} color={isUrgent ? '#E74C3C' : '#F39C12'} />
              <Text style={[styles.pcDeadlineText, isUrgent && { color: '#E74C3C' }]}>
                {Math.ceil((deadlineDate.getTime() - Date.now()) / (1000 * 60 * 60 * 24))}d left
              </Text>
            </View>
          )}
          <View style={[styles.statusBadge, status === 'Open' ? styles.statusOpen : styles.statusClosed]}>
            <Text style={[styles.statusText, status === 'Open' ? styles.statusTextOpen : styles.statusTextClosed]}>
              {status}
            </Text>
          </View>
        </View>
      </View>

      {/* ── Title ── */}
      <Text style={styles.pcTitle} numberOfLines={2}>{project.project_title}</Text>

      {/* ── Description ── */}
      {project.project_description ? (
        <Text numberOfLines={3} style={styles.pcDescription}>
          {project.project_description}
        </Text>
      ) : null}

      {/* ── Type badge ── */}
      {(project.type_name || (project as any).project_type_name) ? (
        <View style={styles.pcTypeBadge}>
          <MaterialIcons name="business" size={13} color="#EC7E00" />
          <Text style={styles.pcTypeText}>{project.type_name || (project as any).project_type_name}</Text>
        </View>
      ) : null}

      {/* ── Detail rows ── */}
      <View style={styles.pcDetails}>
        {project.project_location ? (
          <View style={styles.pcDetailRow}>
            <MaterialIcons name="location-on" size={15} color="#666666" />
            <Text style={styles.pcDetailText} numberOfLines={1}>{project.project_location}</Text>
          </View>
        ) : null}
        {(project.budget_range_min || project.budget_range_max) ? (
          <View style={styles.pcDetailRow}>
            <MaterialIcons name="account-balance-wallet" size={15} color="#666666" />
            <Text style={styles.pcDetailText}>{formatBudget(project.budget_range_min, project.budget_range_max)}</Text>
          </View>
        ) : null}
      </View>

      {/* ── Image collage (full-width) ── */}
      {renderCollage()}

      {/* ── Footer: View Details button ── */}
      <View style={styles.pcFooter}>
        <TouchableOpacity style={styles.pcViewBtn} onPress={() => onPress()} activeOpacity={0.8}>
          <Feather name="eye" size={15} color="#FFFFFF" />
          <Text style={styles.pcViewBtnText}>View Details</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
});

const ReviewCard = memo(({ review, menuOpen, onMenuToggle, onReportPress }: {
  review: Review;
  menuOpen: boolean;
  onMenuToggle: () => void;
  onReportPress: () => void;
}) => {
  const reviewerName = useMemo(() =>
    review.reviewer_company_name ||
    review.reviewer_name ||
    review.reviewer_display_name ||
    review.reviewer_username ||
    'Anonymous',
    [review]
  );

  return (
    <View style={styles.fbCard}>
      <View style={styles.fbCardHeader}>
        <View style={{ flex: 1, flexDirection: 'row', alignItems: 'center' }}>
          <View style={styles.fbCardAvatar}>
            <ImageFallback
              uri={review.reviewer_profile_pic ?
                (review.reviewer_profile_pic.startsWith('http')
                  ? review.reviewer_profile_pic
                  : `${api_config.base_url}/storage/${review.reviewer_profile_pic}`)
                : undefined}
              defaultImage={defaultOwnerAvatar}
              style={styles.fbCardAvatarImg}
            />
          </View>
          <View style={styles.fbCardMeta}>
            <Text style={styles.fbCardAuthor}>{reviewerName}</Text>
            <View style={{ flexDirection: 'row', alignItems: 'center', gap: 2, marginTop: 2 }}>
              {[1,2,3,4,5].map((i) => (
                <MaterialIcons key={i} name={i <= review.rating ? 'star' : 'star-border'} size={13} color={i <= review.rating ? COLORS.star : '#d1d5db'} />
              ))}
            </View>
          </View>
        </View>
        <View style={{ alignItems: 'flex-end', gap: 4 }}>
          {/* ⋮ menu */}
          <View style={styles.reviewMenuWrap}>
            <TouchableOpacity
              onPress={onMenuToggle}
              style={styles.reviewMenuBtn}
              hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
            >
              <MaterialIcons name="more-vert" size={18} color="#9ca3af" />
            </TouchableOpacity>
            {menuOpen && (
              <View style={styles.reviewMenuDropdown}>
                <TouchableOpacity style={styles.cardMenuItem} onPress={onReportPress}>
                  <Text style={[styles.cardMenuItemText, { color: '#DC2626' }]}>Report</Text>
                </TouchableOpacity>
              </View>
            )}
          </View>
          <Text style={styles.reviewDate}>{formatDate(review.created_at)}</Text>
        </View>
      </View>
      <View style={styles.fbCardBody}>
        <Text style={styles.fbCardContent}>{review.comment}</Text>
      </View>
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
export default function ViewProfileScreen({ onBack, userData, userToken, initialTab }) {
  const insets = useSafeAreaInsets();
  const { hasFullAccess: hasFullContractorAccess } = useContractorAuth();
  const [activeTab, setActiveTab] = useState<TabType>(initialTab || 'Posts');
  const [isUploading, setIsUploading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  // Data states
  const [projects, setProjects] = useState<Project[]>([]);
  const [reviews, setReviews] = useState<Review[]>([]);
  const [profilePic, setProfilePic] = useState(userData?.profile_pic);
  const [coverPhoto, setCoverPhoto] = useState(userData?.cover_photo);
  // Local previews so uploads reflect immediately without a full refresh
  const [previewProfileUri, setPreviewProfileUri] = useState<string | null>(null);
  const [previewCoverUri, setPreviewCoverUri] = useState<string | null>(null);
  // Timestamp of the last successful local upload to avoid immediate overwrite
  const recentLocalUpdateRef = useRef<number | null>(null);
  const isMountedRef = useRef(true);

  useEffect(() => {
    isMountedRef.current = true;
    return () => { isMountedRef.current = false; };
  }, []);
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
  const [showCreateShowcase, setShowCreateShowcase] = useState(false);
  const [showcasePosts, setShowcasePosts] = useState<any[]>([]);
  const [highlightingPostId, setHighlightingPostId] = useState<number | null>(null);
  const [selectedShowcasePost, setSelectedShowcasePost] = useState<any>(null);
  const [initialShowcaseImageIndex, setInitialShowcaseImageIndex] = useState<number>(0);
  const [activePostMenuId, setActivePostMenuId] = useState<number | null>(null);
  const [reviewMenuOpenId, setReviewMenuOpenId] = useState<string | number | null>(null);
  const [reportingReview, setReportingReview] = useState<Review | null>(null);

  const tabs = useMemo<TabType[]>(() => {
    if (activeRoleState === 'contractor') return ['Portfolio', 'Highlights', 'Reviews', 'About'];
    return ['Posts', 'Projects', 'Reviews', 'About'];
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
    // Staff: always show their own name from the users table
    if (userState?.user_type === 'staff') {
      const fn = userState?.first_name || '';
      const mn = userState?.middle_name || '';
      const ln = userState?.last_name || '';
      const full = `${fn}${mn ? ' ' + mn : ''}${ln ? ' ' + ln : ''}`.trim();
      return full || userState?.username || 'Staff';
    }

    // Contractors: prefer company name when viewing as contractor
    if (activeRoleState === 'contractor' && contractorInfo && contractorInfo.company_name) {
      return contractorInfo.company_name;
    }

    // Primary source for personal names: users table (`userState`).
    // Use first/middle/last from `users` when available.
    const ufn = userState?.first_name || '';
    const umn = userState?.middle_name || '';
    const uln = userState?.last_name || '';
    if (ufn || umn || uln) {
      return `${ufn}${umn ? ' ' + umn : ''}${uln ? ' ' + uln : ''}`.trim() || userState?.username || 'Member';
    }

    // Fallback to ownerInfo if users table doesn't have name fields populated
    if (ownerInfo) {
      const { first_name = '', middle_name = '', last_name = '' } = ownerInfo;
      const ownerFull = `${first_name}${middle_name ? ' ' + middle_name : ''}${last_name ? ' ' + last_name : ''}`.trim();
      if (ownerFull) return ownerFull;
    }

    return userState?.username || 'Member';
  }, [activeRoleState, contractorInfo, ownerInfo, userState]);

  // Role-aware bio: read from the table that corresponds to the user's current preferred role.
  // Contractor role → contractors.bio, Owner role (or unknown) → property_owners.bio
  const userBio = useMemo(() => {
    if (activeRoleState === 'contractor') {
      return contractorInfo?.bio || '';
    }
    return ownerInfo?.bio || userState?.bio || '';
  }, [activeRoleState, contractorInfo?.bio, ownerInfo?.bio, userState?.bio]);

  const filteredProjects = useMemo(() => projects.filter(p => (p.project_status || '').toLowerCase() !== 'pending'), [projects]);

  const canManageContractorShowcase = useMemo(() => {
    if (activeRoleState !== 'contractor') return false;
    if (userState?.user_type !== 'staff') return true;
    return hasFullContractorAccess;
  }, [activeRoleState, userState?.user_type, hasFullContractorAccess]);

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

  const highlightedPosts = useMemo(() => showcasePosts.filter(p => !!p.is_highlighted), [showcasePosts]);

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

      // Resolve which role to request when the account type is 'both'.
      // Priority: explicit activeRoleState -> user's preferred_role -> user_type -> default 'owner'.
      const userTypeLower = (userState?.user_type || '').toString().toLowerCase();
      const preferredLower = (userState?.preferred_role || '').toString().toLowerCase();
      let resolvedRole: string | null = null;

      if (userTypeLower === 'both') {
        if (activeRoleState) resolvedRole = activeRoleState;
        else if (preferredLower && preferredLower.indexOf('contractor') !== -1) resolvedRole = 'contractor';
        else if (preferredLower && (preferredLower.indexOf('owner') !== -1 || preferredLower.indexOf('property') !== -1)) resolvedRole = 'owner';
        else resolvedRole = 'owner';
      } else if (userTypeLower === 'staff') {
        resolvedRole = 'contractor';
      } else {
        resolvedRole = userTypeLower || null;
      }

      if (resolvedRole && !activeRoleState) {
        setActiveRoleState(resolvedRole);
      }

      const roleQuery = resolvedRole ? `&role=${encodeURIComponent(resolvedRole)}` : '';
      const resp = await api_request(`/api/profile/fetch${query}${roleQuery}`);

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
          // Use the returned user id from the profile response when available
          const responseUserId = (data.user && (data.user.user_id ?? data.user.userId)) || data.user_id || userId;
          console.log('[fetchProfile] calling fetchReviews for', { responseUserId });
          // Do not pass an explicit role here; let the server infer the reviewee role
          // from the provided user id to avoid mismatches.
          fetchReviews(undefined, responseUserId);
        } catch (e) { }
        // Ensure profile and cover images are populated from available fields.
        // If the response includes a role (preferred_role/current role), respect it:
        // - when role === 'contractor' prefer `contractor.company_logo`/`company_banner`
        // - when role === 'owner' prefer `data.user`/owner images from `users`/`property_owners`
        try {
          const contractorLogo = data.contractor && (data.contractor.company_logo || data.contractor.profile_pic);
          const contractorBanner = data.contractor && (data.contractor.company_banner || data.contractor.cover_photo);

          const roleFromResponse = (data.role || '').toString().toLowerCase();
          const isStaff = (data.user?.user_type || '').toString().toLowerCase() === 'staff';

          let candidateProfile;
          let candidateCover;

          if (isStaff) {
            // Staff: use their own personal profile/cover photo from the users table
            candidateProfile =
              (data.user && (data.user.profile_pic || data.user.profilePic)) || undefined;
            candidateCover =
              (data.user && (data.user.cover_photo || data.user.coverPhoto)) || undefined;
          } else if (roleFromResponse === 'owner') {
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

          const nowTs = Date.now();
          if (candidateProfile) {
            const last = recentLocalUpdateRef.current;
            if (!last || (nowTs - last) > 30000) {
              setProfilePic(candidateProfile);
            } else {
              console.log('[fetchProfile] skipping profilePic overwrite due to recent local upload');
            }
          }
          if (candidateCover) {
            const last = recentLocalUpdateRef.current;
            if (!last || (nowTs - last) > 30000) {
              setCoverPhoto(candidateCover);
            } else {
              console.log('[fetchProfile] skipping coverPhoto overwrite due to recent local upload');
            }
          }
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

  const fetchReviews = useCallback(async (roleArg?: string, idArg?: string | number) => {
    const idToUse = idArg ?? userId;
    if (!idToUse) {
      console.log('[fetchReviews] no user id provided, aborting');
      return;
    }

    const roleToUse = roleArg ?? activeRoleState;
    const roleQuery = roleToUse ? `&role=${encodeURIComponent(roleToUse)}` : '';
    const url = `${api_config.base_url}/api/profile/reviews?reviewee_user_id=${encodeURIComponent(idToUse)}${roleQuery}`;
    console.log('[fetchReviews] fetching', { idToUse, roleToUse, url });

    try {
      setLoading(prev => ({ ...prev, reviews: true }));
      setErrors(prev => ({ ...prev, reviews: null }));

      const response = await fetch(url);
      const rawText = await response.text();
      let data: any = null;
      try {
        data = rawText ? JSON.parse(rawText) : null;
      } catch (parseErr) {
        console.error('[fetchReviews] failed to parse JSON', parseErr, rawText);
        throw parseErr;
      }

      console.log('[fetchReviews] response', { status: response.status, ok: response.ok, body: data });

      if (data?.success) {
        const payload = data.data || {};
        // Normalize reviews payload: backend may return an array or an object with numeric keys.
        let reviewsData: any[] = [];
        const rawReviews = payload.reviews ?? payload ?? [];
        if (Array.isArray(rawReviews)) {
          reviewsData = rawReviews;
        } else if (rawReviews && typeof rawReviews === 'object') {
          reviewsData = Object.values(rawReviews);
        } else if (Array.isArray(payload)) {
          reviewsData = payload;
        }
        console.log('[fetchReviews] success - normalized reviews count', reviewsData.length, 'stats', payload.stats);
        setReviews(reviewsData);
        if (payload.stats && typeof payload.stats.avg_rating !== 'undefined' && payload.stats.avg_rating !== null) {
          setRating(Number(payload.stats.avg_rating));
        }
      } else {
        console.warn('[fetchReviews] API returned failure', data);
        setErrors(prev => ({ ...prev, reviews: data?.message || 'Failed to load reviews' }));
      }
    } catch (e) {
      console.error('[fetchReviews] error', e);
      setErrors(prev => ({ ...prev, reviews: handleError(e, 'Failed to load reviews') }));
    } finally {
      setLoading(prev => ({ ...prev, reviews: false }));
    }
  }, [userId, handleError, activeRoleState]);

  const fetchShowcasePosts = useCallback(async () => {
    if (!userId) return;
    try {
      const res = await profile_service.get_profile(userId, 'contractor');
      if (res.success && res.data) {
        const posts = res.data.posts?.showcase_posts ?? [];
        setShowcasePosts(posts);
      }
    } catch (e) {
      // non-fatal
    }
  }, [userId]);

  const handleToggleHighlight = useCallback(async (postId: number, currentlyHighlighted: boolean) => {
    if (highlightingPostId) return;
    setHighlightingPostId(postId);
    try {
      const res = currentlyHighlighted
        ? await highlightService.unhighlightPost(postId)
        : await highlightService.highlightPost(postId);
      if (res.success) {
        setShowcasePosts(prev => prev.map(p =>
          p.post_id === postId ? { ...p, is_highlighted: currentlyHighlighted ? 0 : 1 } : p
        ));
      } else {
        Alert.alert('Highlight', res.message || 'Could not update highlight.');
      }
    } catch {
      Alert.alert('Error', 'Something went wrong.');
    } finally {
      setHighlightingPostId(null);
    }
  }, [highlightingPostId]);

  const onRefresh = useCallback(async () => {
    setRefreshing(true);
    try {
      await Promise.all([
        fetchProfile(),
        fetchProjects(),
        fetchReviews(),
        fetchShowcasePosts(),
      ]);
    } catch (e) {
      console.error('Refresh failed:', e);
    } finally {
      setRefreshing(false);
    }
  }, [fetchProfile, fetchProjects, fetchReviews, fetchShowcasePosts]);

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

    // Keep copies so we can revert UI on failure
    const previousProfile = profilePic;
    const previousCover = coverPhoto;

    // Show a local preview immediately so the user sees the change
    if (type === 'profile') setPreviewProfileUri(uri);
    else setPreviewCoverUri(uri);

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
      // Perform upload via direct fetch to avoid the global `api_request` behavior
      // that injects `X-User-Id` header. Some backend installs expect `owner_id`
      // and will error when `contractors.user_id` is assumed — omitting the
      // header lets the server use the Authorization token (preferred).
      let authTokenLocal = userToken;
      if (!authTokenLocal) {
        try {
          authTokenLocal = await storage_service.get_auth_token();
        } catch (e) {
          authTokenLocal = null;
        }
      }

      // Help servers that accept explicit IDs by including owner/contractor ids
      try {
        if (effectiveRole === 'contractor') {
          if (contractorInfo?.contractor_id) formData.append('contractor_id', String(contractorInfo.contractor_id));
          if (contractorInfo?.owner_id) formData.append('owner_id', String(contractorInfo.owner_id));
          if (ownerInfo?.owner_id) formData.append('owner_id', String(ownerInfo.owner_id));
        } else {
          if (ownerInfo?.owner_id) formData.append('owner_id', String(ownerInfo.owner_id));
        }
      } catch (e) { /* non-fatal */ }

      const headers: any = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Cache-Control': 'no-cache, no-store, must-revalidate',
        'Pragma': 'no-cache',
        'Expires': '0',
      };
      if (authTokenLocal) headers['Authorization'] = `Bearer ${authTokenLocal}`;

      const uploadUrl = `${api_config.base_url}/api/user/profile`;
      console.log('[uploadImage] uploading via fetch', { uploadUrl, effectiveRole, fieldName, filename, tokenPresent: !!authTokenLocal });

      const respFetch = await fetch(uploadUrl, {
        method: 'POST',
        body: formData,
        headers,
        // If we have a bearer token, omit credentials; otherwise include cookies
        credentials: authTokenLocal ? 'omit' : 'include',
      });

      const rawText = await respFetch.text();
      let parsed: any = null;
      try {
        parsed = rawText ? JSON.parse(rawText) : null;
      } catch (e) {
        console.error('[uploadImage] failed to parse JSON response', e, rawText);
        throw new Error('Invalid server response');
      }

      const resp = { success: respFetch.ok, data: parsed, status: respFetch.status, message: parsed?.message };
      console.log('[uploadImage] fetch response', resp);

      if (resp?.success) {
        const wrapper = resp.data || resp;
        const inner = wrapper.data ?? wrapper.user ?? wrapper;
        const userObj = inner.user ?? inner;
        const contractorObj = inner.contractor ?? null;

        // Update local cached states
        if (userObj && typeof userObj === 'object') setUserData(prev => ({ ...(prev || {}), ...(userObj || {}) }));
        if (contractorObj && typeof contractorObj === 'object') setContractorInfo(contractorObj);

        let returnedPath: string | null = null;
        if (type === 'profile') {
          returnedPath = userObj?.profile_pic || contractorObj?.company_logo || inner?.profile_pic || inner?.company_logo || (wrapper.path ?? null);
        } else {
          returnedPath = userObj?.cover_photo || contractorObj?.company_banner || inner?.cover_photo || inner?.company_banner || (wrapper.path ?? null);
        }
        if (returnedPath) {
          if (type === 'profile') setProfilePic(returnedPath);
          else setCoverPhoto(returnedPath);
        }

        // Persist updated paths to storage so other screens stay in sync
        try {
          const stored = await storage_service.get_user_data();
          if (stored) {
            const merged = { ...stored };
            if (type === 'profile' && returnedPath) merged.profile_pic = returnedPath;
            if (type === 'cover' && returnedPath) merged.cover_photo = returnedPath;
            await storage_service.save_user_data(merged);
          }
        } catch (e) { /* non-critical */ }
        if (returnedPath) {
          // Clear local preview now that server returned the stored path
          if (type === 'profile') setPreviewProfileUri(null);
          else setPreviewCoverUri(null);

          // Mark recent local update to avoid immediate overwrite by a concurrent fetch
          try {
            recentLocalUpdateRef.current = Date.now();
            setTimeout(() => { recentLocalUpdateRef.current = null; }, 5000);
          } catch (e) { /* non-fatal */ }

          Alert.alert('Success', 'Photo updated successfully');
        } else {
          // Server did not return a path in the response; keep the local preview
          // visible and poll the profile endpoint briefly until the new path appears.
          // Mark as recent local update so other background fetches don't overwrite
          try {
            recentLocalUpdateRef.current = Date.now();
            setTimeout(() => { recentLocalUpdateRef.current = null; }, 30000);
          } catch (e) { /* non-fatal */ }
          (async () => {
            const maxAttempts = 20;
            let found = false;
            for (let i = 0; i < maxAttempts && isMountedRef.current; i++) {
              try {
                const q = userState?.user_id ? `?user_id=${encodeURIComponent(userState.user_id)}` : (userState?.username ? `?username=${encodeURIComponent(userState.username)}` : '');
                if (!q) break;
                const pf = await api_request(`/api/profile/fetch${q}`);
                if (pf?.success && pf.data) {
                  const d = pf.data.data || pf.data;
                  const contractorLogo = d.contractor && (d.contractor.company_logo || d.contractor.profile_pic);
                  const contractorBanner = d.contractor && (d.contractor.company_banner || d.contractor.cover_photo);
                  const roleFromResponse = (d.role || '').toString().toLowerCase();
                  const isStaff = (d.user?.user_type || '').toString().toLowerCase() === 'staff';
                  let candidateProfile;
                  let candidateCover;
                  if (isStaff) {
                    candidateProfile = (d.user && (d.user.profile_pic || d.user.profilePic)) || undefined;
                    candidateCover = (d.user && (d.user.cover_photo || d.user.coverPhoto)) || undefined;
                  } else if (roleFromResponse === 'owner') {
                    candidateProfile = (d.user && (d.user.profile_pic || d.user.profilePic)) || (d.owner && (d.owner.profile_pic || d.owner.profilePic)) || d.owner_profile_pic || d.owner_profile || undefined;
                    candidateCover = (d.user && (d.user.cover_photo || d.user.coverPhoto)) || (d.owner && (d.owner.cover_photo || d.owner.coverPhoto)) || d.cover_photo || undefined;
                  } else if (roleFromResponse === 'contractor') {
                    candidateProfile = contractorLogo || (d.user && (d.user.profile_pic || d.user.profilePic)) || (d.owner && (d.owner.profile_pic || d.owner.profilePic)) || d.owner_profile_pic || d.owner_profile || undefined;
                    candidateCover = contractorBanner || (d.user && (d.user.cover_photo || d.user.coverPhoto)) || (d.owner && (d.owner.cover_photo || d.owner.coverPhoto)) || d.cover_photo || undefined;
                  } else {
                    candidateProfile = contractorLogo || (d.user && (d.user.profile_pic || d.user.profilePic)) || (d.owner && (d.owner.profile_pic || d.owner.profilePic)) || d.owner_profile_pic || d.owner_profile || undefined;
                    candidateCover = contractorBanner || (d.user && (d.user.cover_photo || d.user.coverPhoto)) || (d.owner && (d.owner.cover_photo || d.owner.coverPhoto)) || d.cover_photo || undefined;
                  }
                  if (type === 'profile' && candidateProfile && candidateProfile !== previousProfile) {
                    if (isMountedRef.current) {
                      setProfilePic(candidateProfile);
                      setPreviewProfileUri(null);
                      recentLocalUpdateRef.current = Date.now();
                      setTimeout(() => { recentLocalUpdateRef.current = null; }, 5000);
                    }
                    found = true;
                    break;
                  }
                  if (type === 'cover' && candidateCover && candidateCover !== previousCover) {
                    if (isMountedRef.current) {
                      setCoverPhoto(candidateCover);
                      setPreviewCoverUri(null);
                      recentLocalUpdateRef.current = Date.now();
                      setTimeout(() => { recentLocalUpdateRef.current = null; }, 5000);
                    }
                    found = true;
                    break;
                  }
                }
              } catch (e) {
                // ignore and retry
              }
              await new Promise(r => setTimeout(r, 1000));
            }
            // If polling finished without finding a new path, keep preview and notify.
            if (isMountedRef.current) {
              if (!found) {
                try {
                  recentLocalUpdateRef.current = Date.now();
                  setTimeout(() => { recentLocalUpdateRef.current = null; }, 30000);
                } catch (e) { /* non-fatal */ }
                Alert.alert('Notice', 'Photo uploaded. The preview is kept locally; pull to refresh if it does not appear elsewhere.');
              } else {
                // Poll succeeded and we updated the profile/cover — show success
                Alert.alert('Success', 'Photo updated successfully');
              }
            }
          })();
        }
      } else {
        console.error('[uploadImage] upload failed', resp);
        // revert preview to previous state
        if (type === 'profile') {
          setProfilePic(previousProfile);
          setPreviewProfileUri(null);
        } else {
          setCoverPhoto(previousCover);
          setPreviewCoverUri(null);
        }
        Alert.alert('Error', resp?.message || 'Failed to update photo');
      }
    } catch (error) {
      console.error('[uploadImage] network/error', error);
      // revert preview to previous state
      if (type === 'profile') {
        setProfilePic(previousProfile);
        setPreviewProfileUri(null);
      } else {
        setCoverPhoto(previousCover);
        setPreviewCoverUri(null);
      }
      Alert.alert('Error', 'Network error. Please try again.');
    } finally {
      setIsUploading(false);
    }
  }, [userToken]);

  // Effects
  useEffect(() => {
    // Seed profile/cover from storage immediately so the latest saved data shows
    // before the API fetch completes (editProfile saves updated data to storage on save)
    storage_service.get_user_data().then(stored => {
      if (stored?.profile_pic) setProfilePic(stored.profile_pic);
      if (stored?.cover_photo) setCoverPhoto(stored.cover_photo);
    }).catch(() => {});
    fetchProfile();
  }, [fetchProfile]);

  useEffect(() => {
    if (activeRoleState === 'contractor') {
      fetchShowcasePosts();
    }
  }, [activeRoleState, fetchShowcasePosts]);

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
          {previewCoverUri ? (
            <Image source={{ uri: previewCoverUri }} style={styles.coverImg} resizeMode="cover" />
          ) : (
            <ImageFallback
              uri={getStorageUrl(coverPhoto)}
              defaultImage={defaultCoverPhoto}
              style={styles.coverImg}
              resizeMode="cover"
            />
          )}
          {userState?.user_type !== 'staff' && (
          <TouchableOpacity
            style={styles.editCoverBtn}
            onPress={() => pickImage('cover')}
            hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
          >
            <Feather name="camera" size={18} color={COLORS.surface} />
          </TouchableOpacity>
          )}
        </View>

        <View style={styles.profileInfoContainer}>
          <View style={styles.avatarWrapper}>
            {previewProfileUri ? (
              <Image source={{ uri: previewProfileUri }} style={styles.avatarImg} resizeMode="cover" />
            ) : (
              <ImageFallback
                uri={getStorageUrl(profilePic)}
                defaultImage={activeRoleState === 'contractor' ? defaultContractorAvatar : defaultOwnerAvatar}
                style={styles.avatarImg}
                resizeMode="cover"
              />
            )}
            {userState?.user_type !== 'staff' && (
            <TouchableOpacity
              style={styles.editAvatarBtn}
              onPress={() => pickImage('profile')}
              hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
            >
              <Feather name="edit-2" size={14} color={COLORS.surface} />
            </TouchableOpacity>
            )}
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
    const postInputBar = canManageContractorShowcase ? (
      <TouchableOpacity
        style={styles.postInputRow}
        activeOpacity={0.7}
        onPress={() => setShowCreateShowcase(true)}
      >
        <View style={styles.postInputAvatar}>
          {previewProfileUri ? (
            <Image source={{ uri: previewProfileUri }} style={{ width: 38, height: 38, borderRadius: 19 }} />
          ) : profilePic ? (
            <Image
              source={{ uri: String(profilePic).startsWith('http') ? String(profilePic) : `${api_config.base_url}/storage/${profilePic}` }}
              style={{ width: 38, height: 38, borderRadius: 19 }}
            />
          ) : (
            <Feather name="user" size={20} color="#9ca3af" />
          )}
        </View>
        <View style={styles.postInputBtn}>
          <Text style={styles.postInputText}>Share your work...</Text>
        </View>
      </TouchableOpacity>
    ) : null;

    if (loading.projects) {
      return (
        <View style={styles.tabContent}>
          {postInputBar}
          <ProjectCardSkeleton />
          <ProjectCardSkeleton />
          <ProjectCardSkeleton />
        </View>
      );
    }

    if (errors.projects) {
      return (
        <View style={styles.tabContent}>
          {postInputBar}
          <View style={styles.emptyState}>
            <Feather name="alert-circle" size={48} color={COLORS.border} />
            <Text style={styles.emptyTitle}>Failed to load projects</Text>
            <Text style={styles.emptySubtext}>{errors.projects}</Text>
            <TouchableOpacity style={styles.retryButton} onPress={fetchProjects}>
              <Text style={styles.retryButtonText}>Retry</Text>
            </TouchableOpacity>
          </View>
        </View>
      );
    }

    if (ownerVisibleProjects.length === 0) {
      return (
        <View style={styles.tabContent}>
          {postInputBar}
          <View style={styles.emptyState}>
            <Feather name="folder" size={48} color={COLORS.border} />
            <Text style={styles.emptyTitle}>No projects yet</Text>
            <Text style={styles.emptySubtext}>Tap above to share your first project!</Text>
          </View>
        </View>
      );
    }

    return (
      <View style={styles.tabContent}>
        {postInputBar}
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

  const renderAboutTab = () => {
    if (activeRoleState === 'contractor' && contractorInfo) {
      const verStatus = contractorInfo.verification_status || 'pending';
      const verColor = verStatus === 'approved' ? '#16a34a' : verStatus === 'rejected' ? '#dc2626' : '#6b7280';
      const verBg = verStatus === 'approved' ? '#dcfce7' : verStatus === 'rejected' ? '#fef2f2' : '#f3f4f6';
      const tier = contractorInfo.subscription_tier;
      return (
        <View style={styles.tabContent}>
          {/* Stats Strip */}
          <View style={styles.statsStrip}>
            {[
              { label: 'Experience', value: `${contractorInfo.years_of_experience ?? 0} yrs` },
              { label: 'Completed', value: String(projectsDone || contractorInfo.completed_projects || 0) },
              { label: 'Rating', value: rating ? rating.toFixed(1) : 'N/A' },
              { label: 'Reviews', value: String(reviews.length) },
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

          {/* Bio */}
          <View style={styles.aboutSectionCard}>
            <Text style={styles.aboutSectionTitle}>Bio</Text>
            <Text style={styles.aboutText}>
              {contractorInfo.bio || contractorInfo.company_description || 'No bio added yet.'}
            </Text>
          </View>

          {/* Services Offered */}
          {contractorInfo.services_offered ? (
            <View style={styles.aboutSectionCard}>
              <Text style={styles.aboutSectionTitle}>Services Offered</Text>
              <Text style={styles.aboutText}>{contractorInfo.services_offered}</Text>
            </View>
          ) : null}

          {/* Business Details */}
          <View style={styles.aboutSectionCard}>
            <Text style={styles.aboutSectionTitle}>Business Details</Text>
            <View style={styles.detailRow}>
              <Text style={styles.detailLabel}>Contractor Type</Text>
              <Text style={styles.detailValue}>{contractorInfo.type_name || contractorInfo.contractor_type || 'General'}</Text>
            </View>
            <View style={styles.detailRow}>
              <Text style={styles.detailLabel}>Experience</Text>
              <Text style={styles.detailValue}>{contractorInfo.years_of_experience ? `${contractorInfo.years_of_experience} years` : '—'}</Text>
            </View>
            {contractorInfo.picab_category ? (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>PICAB Category</Text>
                <Text style={styles.detailValue}>{contractorInfo.picab_category}</Text>
              </View>
            ) : null}
            {contractorCity && contractorCity !== '—' ? (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>City</Text>
                <Text style={styles.detailValue}>{contractorCity}</Text>
              </View>
            ) : null}
            {tier && tier !== 'free' ? (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>Subscription</Text>
                <View style={[styles.tierBadge, { backgroundColor: tier === 'gold' ? '#FEF3C7' : '#F3E8FF' }]}>
                  <Text style={[styles.tierBadgeText, { color: tier === 'gold' ? '#d97706' : '#7c3aed' }]}>
                    {tier.charAt(0).toUpperCase() + tier.slice(1)}
                  </Text>
                </View>
              </View>
            ) : null}
            <View style={styles.detailRow}>
              <Text style={styles.detailLabel}>Verification</Text>
              <View style={[styles.verificationBadge, { backgroundColor: verBg }]}>
                <Text style={[styles.verificationText, { color: verColor }]}>
                  {verStatus.charAt(0).toUpperCase() + verStatus.slice(1)}
                </Text>
              </View>
            </View>
            {userState?.created_at ? (
              <View style={styles.detailRow}>
                <Text style={styles.detailLabel}>Member Since</Text>
                <Text style={styles.detailValue}>{formatDate(userState.created_at)}</Text>
              </View>
            ) : null}
          </View>

          {/* Representatives */}
          {contractorReps && contractorReps.length > 0 && (
            <View style={styles.aboutSectionCard}>
              <Text style={styles.aboutSectionTitle}>Representative</Text>
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
                        <Text style={styles.aboutText}>
                          {rep.role ? rep.role.replace(/_/g, ' ').replace(/\b\w/g, (c: string) => c.toUpperCase()) : '—'}
                        </Text>
                      </View>
                    </View>
                  </View>
                );
              })}
            </View>
          )}
        </View>
      );
    }

    // Owner
    return (
      <View style={styles.tabContent}>
        {/* Stats Strip */}
        <View style={styles.statsStrip}>
          {[
            { label: 'Total', value: String(projectStats.total) },
            { label: 'Completed', value: String(projectStats.completed) },
            { label: 'Ongoing', value: String(projectStats.ongoing) },
            { label: 'Rating', value: rating ? rating.toFixed(1) : 'N/A' },
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

        {/* Bio */}
        <View style={styles.aboutSectionCard}>
          <Text style={styles.aboutSectionTitle}>Bio</Text>
          <Text style={styles.aboutText}>{userBio || 'No bio added yet.'}</Text>
        </View>

        {/* Personal Info */}
        <View style={styles.aboutSectionCard}>
          <Text style={styles.aboutSectionTitle}>Personal Info</Text>
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Full Name</Text>
            <Text style={styles.detailValue}>{displayName}</Text>
          </View>
          {(occupationName || ownerInfo?.occupation_name) ? (
            <View style={styles.detailRow}>
              <Text style={styles.detailLabel}>Occupation</Text>
              <Text style={styles.detailValue}>{occupationName || ownerInfo?.occupation_name}</Text>
            </View>
          ) : null}
          {userCity && userCity !== '—' ? (
            <View style={styles.detailRow}>
              <Text style={styles.detailLabel}>Location</Text>
              <Text style={styles.detailValue}>{userCity}</Text>
            </View>
          ) : null}
          {userState?.created_at ? (
            <View style={styles.detailRow}>
              <Text style={styles.detailLabel}>Member Since</Text>
              <Text style={styles.detailValue}>{formatDate(userState.created_at)}</Text>
            </View>
          ) : null}
        </View>
      </View>
    );
  };

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
          {reviews.length >= 5 ? (
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
          ) : (
            <View style={styles.ratingBig}>
              <MaterialIcons name="star-outline" size={40} color={COLORS.border} />
              <Text style={[styles.reviewsCountText, { marginTop: 8, textAlign: 'center' }]}>
                Rating visible after 5 reviews
              </Text>
              <Text style={[styles.reviewsCountText, { marginTop: 4, textAlign: 'center' }]}>
                {reviews.length} of 5 received
              </Text>
            </View>
          )}
        </View>
        {reviews.map(review => (
          <ReviewCard
            key={review.review_id}
            review={review}
            menuOpen={reviewMenuOpenId === review.review_id}
            onMenuToggle={() => setReviewMenuOpenId(prev => prev === review.review_id ? null : review.review_id)}
            onReportPress={() => {
              setReviewMenuOpenId(null);
              setReportingReview(review);
            }}
          />
        ))}
      </View>
    );
  };

  const renderHighlightsTab = () => {
    if (highlightedPosts.length === 0) {
      return (
        <View style={styles.tabContent}>
          <View style={styles.emptyState}>
            <MaterialIcons name="star-outline" size={48} color={COLORS.border} />
            <Text style={styles.emptyTitle}>No highlighted posts yet</Text>
            <Text style={styles.emptySubtext}>
              In your Portfolio, tap ⋮ on a showcase post and choose "Highlight" to feature it here.
            </Text>
          </View>
        </View>
      );
    }

    return (
      <View style={styles.tabContent}>
        {highlightedPosts.map(post => {
          const allImages = (post.images || []).map((img: any) => {
            const p = String(img.file_path || img || '');
            return p.startsWith('http') ? p : `${api_config.base_url}/storage/${p}`;
          }).filter(Boolean);
          const postDate = post.created_at ? formatDate(post.created_at) : '';
          const authorName = displayName || userState?.username || 'Contractor';
          const authorPicUrl = profilePic
            ? (String(profilePic).startsWith('http') ? String(profilePic) : `${api_config.base_url}/storage/${profilePic}`)
            : null;
          const authorInitials = authorName.split(' ').map((p: string) => p[0]).join('').toUpperCase().slice(0, 2) || 'C';

          return (
            <View key={post.post_id} style={styles.fbCard}>
              {/* ── Header ── */}
              <View style={styles.fbCardHeader}>
                <View style={styles.fbCardAvatar}>
                  {authorPicUrl ? (
                    <Image source={{ uri: authorPicUrl }} style={styles.fbCardAvatarImg} />
                  ) : (
                    <View style={styles.fbCardAvatarPlaceholder}>
                      <Text style={styles.fbCardAvatarText}>{authorInitials}</Text>
                    </View>
                  )}
                </View>
                <View style={styles.fbCardMeta}>
                  <Text style={styles.fbCardAuthor}>{authorName}</Text>
                  {postDate ? <Text style={styles.fbCardDate}>{postDate}</Text> : null}
                </View>
                <View style={[styles.fbChip, { backgroundColor: '#FFF3C4', marginLeft: 'auto' }]}>
                  <MaterialIcons name="star" size={12} color="#92400E" />
                  <Text style={[styles.fbChipText, { color: '#92400E' }]}>Highlighted</Text>
                </View>
              </View>

              {/* ── Text body ── */}
              {(post.title || post.description || post.content) ? (
                <View style={styles.fbCardBody}>
                  {post.title ? <Text style={styles.fbCardTitle}>{post.title}</Text> : null}
                  {(post.description || post.content) ? (
                    <Text style={styles.fbCardContent} numberOfLines={4}>{post.description || post.content}</Text>
                  ) : null}
                </View>
              ) : null}

              {/* ── Image collage (FB-style grid) ── */}
              {allImages.length > 0 ? (
                <View style={styles.fbCollageWrap}>
                  {renderFbCollage(allImages, post)}
                </View>
              ) : null}

              {/* ── Footer ── */}
              <View style={styles.fbCardFooter}>
                <TouchableOpacity
                  style={styles.footerActionBtn}
                  activeOpacity={0.7}
                  onPress={() => setSelectedShowcasePost(post)}
                >
                  <Feather name="eye" size={14} color={COLORS.textSecondary} />
                  <Text style={styles.footerActionText}>View post</Text>
                </TouchableOpacity>
                {canManageContractorShowcase && (
                <TouchableOpacity
                  style={[styles.footerActionBtn, { marginLeft: 8 }]}
                  onPress={() => handleToggleHighlight(post.post_id, true)}
                  disabled={highlightingPostId === post.post_id}
                >
                  {highlightingPostId === post.post_id
                    ? <ActivityIndicator size="small" color={COLORS.textMuted} />
                    : <>
                        <Feather name="x" size={14} color={COLORS.textMuted} />
                        <Text style={[styles.footerActionText, { color: COLORS.textMuted }]}>Remove</Text>
                      </>
                  }
                </TouchableOpacity>
                )}
              </View>
            </View>
          );
        })}
      </View>
    );
  };

  // ── Facebook-style image collage for showcase/highlight cards ──
  const renderFbCollage = (images: string[], post: any) => {
    if (!images || images.length === 0) return null;
    const GAP = 5;
    // Card has marginHorizontal:16 (×2=32) + wrap has margin:5 (×2=10) = 42
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

    // 1 image — full width
    if (images.length === 1) {
      return img(images[0], { width: W, height: singleH }, 0);
    }

    // 2 images — side by side equal halves
    if (images.length === 2) {
      return (
        <View style={{ flexDirection: 'row' }}>
          {img(images[0], { width: half, height: dualH, marginRight: GAP }, 0)}
          {img(images[1], { width: half, height: dualH }, 1)}
        </View>
      );
    }

    // 3 images — 1 large left + 2 stacked right
    // |      |[  ]|
    // |      |[  ]|
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

    // 4 images — 2×2 grid (equal squares)
    // [  ][  ]
    // [  ][  ]
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

    // 5+ images — 2×2 grid showing first 3 + last slot is "+N" overlay
    // [  ][  ]
    // [  ][+6]
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

  const renderPortfolioTab = () => {
    // Owners: fall back to the standard project posts tab
    if (activeRoleState !== 'contractor') {
      return renderPostsTab();
    }

    // Contractors: show own showcase posts only
    const postInputBar = canManageContractorShowcase ? (
      <TouchableOpacity
        style={styles.postInputRow}
        activeOpacity={0.7}
        onPress={() => setShowCreateShowcase(true)}
      >
        <View style={styles.postInputAvatar}>
          {previewProfileUri ? (
            <Image source={{ uri: previewProfileUri }} style={{ width: 38, height: 38, borderRadius: 19 }} />
          ) : profilePic ? (
            <Image
              source={{ uri: String(profilePic).startsWith('http') ? String(profilePic) : `${api_config.base_url}/storage/${profilePic}` }}
              style={{ width: 38, height: 38, borderRadius: 19 }}
            />
          ) : (
            <Feather name="user" size={20} color="#9ca3af" />
          )}
        </View>
        <View style={styles.postInputBtn}>
          <Text style={styles.postInputText}>Share your work...</Text>
        </View>
      </TouchableOpacity>
    ) : null;

    if (showcasePosts.length === 0) {
      return (
        <View style={styles.tabContent}>
          {postInputBar}
          <View style={styles.emptyState}>
            <Feather name="camera" size={48} color={COLORS.border} />
            <Text style={styles.emptyTitle}>No showcase posts yet</Text>
            <Text style={styles.emptySubtext}>Tap above to share your first project!</Text>
          </View>
        </View>
      );
    }

    return (
      <View style={styles.tabContent}>
        {postInputBar}
                {showcasePosts.map(post => {
          const isHighlighted = !!post.is_highlighted;
          const allImages = (post.images || []).map((img: any) => {
            const p = String(img.file_path || img || '');
            return p.startsWith('http') ? p : `${api_config.base_url}/storage/${p}`;
          }).filter(Boolean);
          const firstImage = allImages[0] || null;

          const postDate = post.created_at ? formatDate(post.created_at) : '';
          const authorName = displayName || userState?.username || 'Contractor';
          const authorPicUrl = profilePic
            ? (String(profilePic).startsWith('http') ? String(profilePic) : `${api_config.base_url}/storage/${profilePic}`)
            : null;
          const authorInitials = authorName.split(' ').map((p: string) => p[0]).join('').toUpperCase().slice(0, 2) || 'C';

          return (
            <View key={`sp-${post.post_id}`} style={styles.fbCard}>
              {/* ── Header ── */}
              <View style={styles.fbCardHeader}>
                <View style={styles.fbCardAvatar}>
                  {authorPicUrl ? (
                    <Image source={{ uri: authorPicUrl }} style={styles.fbCardAvatarImg} />
                  ) : (
                    <View style={styles.fbCardAvatarPlaceholder}>
                      <Text style={styles.fbCardAvatarText}>{authorInitials}</Text>
                    </View>
                  )}
                </View>
                <View style={styles.fbCardMeta}>
                  <Text style={styles.fbCardAuthor}>{authorName}</Text>
                  {postDate ? <Text style={styles.fbCardDate}>{postDate}</Text> : null}
                </View>
                {canManageContractorShowcase && (
                <View style={styles.cardMenuWrap}>
                  <TouchableOpacity
                    onPress={(e) => {
                      e.stopPropagation?.();
                      setActivePostMenuId(prev => prev === post.post_id ? null : post.post_id);
                    }}
                    activeOpacity={0.7}
                    style={styles.fbCardMenuBtn}
                    hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
                  >
                    <MaterialIcons name="more-vert" size={20} color={COLORS.textSecondary} />
                  </TouchableOpacity>
                  {activePostMenuId === post.post_id && (
                    <View style={styles.cardMenuDropdown}>
                      <TouchableOpacity
                        style={styles.cardMenuItem}
                        onPress={(e) => {
                          e.stopPropagation?.();
                          setActivePostMenuId(null);
                          handleToggleHighlight(post.post_id, isHighlighted);
                        }}
                        disabled={highlightingPostId === post.post_id}
                      >
                        {highlightingPostId === post.post_id
                          ? <ActivityIndicator size="small" color={COLORS.primary} />
                          : <Text style={styles.cardMenuItemText}>{isHighlighted ? 'Unhighlight' : 'Highlight'}</Text>
                        }
                      </TouchableOpacity>
                    </View>
                  )}
                </View>
                )}
              </View>

              {/* ── Text body ── */}
              {(post.title || post.content) ? (
                <View style={styles.fbCardBody}>
                  {post.title ? <Text style={styles.fbCardTitle}>{post.title}</Text> : null}
                  {post.content ? <Text style={styles.fbCardContent} numberOfLines={4}>{post.content}</Text> : null}
                </View>
              ) : null}

              {/* ── Image collage (FB-style grid) ── */}
              {allImages.length > 0 ? (
                <View style={styles.fbCollageWrap}>
                  {renderFbCollage(allImages, post)}
                </View>
              ) : null}

              {/* ── chips row ── */}
              {(isHighlighted || post.linked_project_id) ? (
                <View style={styles.fbCardChips}>
                  {isHighlighted ? (
                    <View style={[styles.fbChip, { backgroundColor: '#FFF3C4' }]}>
                      <MaterialIcons name="star" size={11} color="#92400E" />
                      <Text style={[styles.fbChipText, { color: '#92400E' }]}>Highlighted</Text>
                    </View>
                  ) : null}
                  {post.linked_project_id ? (
                    <View style={styles.fbChip}>
                      <Feather name="link" size={11} color={COLORS.textSecondary} />
                      <Text style={styles.fbChipText}>{post.linked_project_title || 'Linked project'}</Text>
                    </View>
                  ) : null}
                </View>
              ) : null}

              {/* ── Footer action ── */}
              <View style={styles.fbCardFooter}>
                <TouchableOpacity
                  style={styles.footerActionBtn}
                  activeOpacity={0.7}
                  onPress={() => { setActivePostMenuId(null); setSelectedShowcasePost(post); }}
                >
                  <Feather name="eye" size={14} color={COLORS.textSecondary} />
                  <Text style={styles.footerActionText}>View post</Text>
                </TouchableOpacity>
              </View>
            </View>
          );
        })}
      </View>
    );
  };

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

  // If viewing a showcase post detail, show full-screen detail view
  if (selectedShowcasePost) {
    return (
      <ShowcasePostDetail
        post={selectedShowcasePost}
        onClose={() => { setSelectedShowcasePost(null); setInitialShowcaseImageIndex(0); }}
        isOwner={true}
        initialImageIndex={initialShowcaseImageIndex}
      />
    );
  }

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

      <CreateShowcase
        visible={showCreateShowcase}
        onClose={() => setShowCreateShowcase(false)}
        onCreated={() => {
          setShowCreateShowcase(false);
          fetchProjects();
          fetchShowcasePosts();
        }}
      />

      <ReportPostModal
        visible={!!reportingReview}
        onClose={() => setReportingReview(null)}
        onSubmit={async (reason, details, attachments) => {
          const res = await post_service.report_review(Number(reportingReview!.review_id), reason, details, attachments);
          return { success: !!res.success, message: res.message };
        }}
      />

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
    paddingBottom: 24,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
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
    backgroundColor: 'rgba(0,0,0,0.55)',
    padding: 8,
    borderRadius: 6,
    borderWidth: 1.5,
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
    padding: 7,
    borderRadius: 8,
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
    paddingBottom: 16,
    width: '100%',
  },

  // Portfolio/Project Card - matches projectDetails card style
  projectCard: {
    backgroundColor: COLORS.surface,
    marginHorizontal: 8,
    marginTop: 6,
    marginBottom: 0,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.border,
    overflow: 'hidden',
  },
  // ProjectCard inner styles — matching homepage feed layout
  pcHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingHorizontal: 16,
    paddingTop: 14,
    paddingBottom: 12,
  },
  pcOwnerInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  pcOwnerAvatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    marginRight: 10,
  },
  pcOwnerName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333333',
  },
  pcPostDate: {
    fontSize: 12,
    color: '#999999',
    marginTop: 1,
  },
  pcDeadlineBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF5E5',
    paddingHorizontal: 7,
    paddingVertical: 4,
    borderRadius: 8,
    gap: 3,
  },
  pcDeadlineUrgent: {
    backgroundColor: '#FFEBE5',
  },
  pcDeadlineText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#F39C12',
  },
  pcTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333333',
    paddingHorizontal: 16,
    marginBottom: 8,
  },
  pcDescription: {
    fontSize: 14,
    color: '#666666',
    lineHeight: 20,
    paddingHorizontal: 16,
    marginBottom: 10,
  },
  pcTypeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    backgroundColor: '#FFF3E6',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 5,
    marginBottom: 10,
    marginLeft: 16,
  },
  pcTypeText: {
    fontSize: 12,
    fontWeight: '500',
    color: '#EC7E00',
  },
  pcDetails: {
    gap: 7,
    paddingHorizontal: 16,
    paddingBottom: 12,
  },
  pcDetailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 7,
  },
  pcDetailText: {
    fontSize: 13,
    color: '#666666',
    flex: 1,
  },
  pcFooter: {
    paddingHorizontal: 16,
    paddingBottom: 14,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#F0F0F0',
    marginTop: 4,
  },
  pcViewBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.primary,
    paddingVertical: 12,
    borderRadius: 8,
    gap: 6,
  },
  pcViewBtnText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  portfolioHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingTop: 14,
    paddingBottom: 10,
  },
  portfolioAvatar: {
    marginRight: 10,
  },
  portfolioAvatarImage: {
    width: 42,
    height: 42,
    borderRadius: 21,
  },
  portfolioAvatarPlaceholder: {
    width: 42,
    height: 42,
    borderRadius: 21,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  portfolioAvatarText: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.primary,
  },
  portfolioInfo: {
    flex: 1,
  },
  portfolioCompany: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
  },
  portfolioUsername: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 1,
  },
  portfolioTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
    paddingHorizontal: 14,
    marginBottom: 4,
  },
  portfolioDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    paddingHorizontal: 14,
    marginBottom: 4,
  },
  portfolioMeta: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingTop: 10,
    paddingBottom: 2,
  },
  metaItem: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  metaItemText: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginLeft: 4,
    flex: 1,
  },
  portfolioFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
    marginTop: 10,
    paddingHorizontal: 14,
    paddingVertical: 8,
  },
  footerActionBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingVertical: 4,
    paddingHorizontal: 8,
  },
  footerActionText: {
    fontSize: 13,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },

  // Status Badge
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 4,
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
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.primary,
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

  // Review card ⋮ menu
  reviewMenuWrap: {
    position: 'relative',
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
    minWidth: 130,
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

  // checkProfile-style About cards
  statsStrip: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.border,
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
    color: COLORS.primary,
  },
  statStripLabel: {
    fontSize: 11,
    color: COLORS.textSecondary,
    marginTop: 2,
  },
  statStripDivider: {
    width: 1,
    backgroundColor: COLORS.border,
    marginVertical: 10,
  },
  aboutSectionCard: {
    marginHorizontal: 8,
    marginBottom: 12,
    backgroundColor: '#fff',
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.border,
    padding: 16,
  },
  aboutSectionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.textSecondary,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 10,
  },
  aboutText: {
    fontSize: 14,
    color: COLORS.text,
    lineHeight: 21,
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
    color: COLORS.textSecondary,
    width: 120,
  },
  detailValue: {
    fontSize: 13,
    fontWeight: '500',
    color: COLORS.text,
    flex: 1,
  },
  tierBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
    alignSelf: 'flex-start',
  },
  tierBadgeText: {
    fontSize: 12,
    fontWeight: '600',
  },
  verificationBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6,
  },
  verificationText: {
    fontSize: 12,
    fontWeight: '500',
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
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    padding: 16,
    alignItems: 'center',
    marginBottom: 12,
  },
  highlightIcon: {
    width: 50,
    height: 50,
    borderRadius: 8,
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
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
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
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
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
    borderRadius: 6,
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
    borderRadius: 6,
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

  // Post input bar
  postInputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  postInputAvatar: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: COLORS.borderLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 10,
    overflow: 'hidden',
  },
  postInputBtn: {
    flex: 1,
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 8,
    paddingHorizontal: 16,
    paddingVertical: 9,
  },
  postInputText: {
    fontSize: 14,
    color: COLORS.textMuted,
  },

  // Highlight post cards (Highlights tab) — now uses fbCard
  highlightPostCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 0,
    borderWidth: 0,
    marginBottom: 0,
    overflow: 'hidden',
  },
  highlightPostImg: {
    width: '100%',
    height: 220,
    backgroundColor: COLORS.borderLight,
  },
  highlightPostBody: {
    padding: 14,
  },
  highlightBadgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  highlightBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: '#FFF3C4',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
  },
  highlightBadgeText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#92400E',
  },
  highlightPostTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  highlightPostDesc: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 18,
  },

  // Facebook-style feed card (Portfolio + Highlights tabs) - matches projectDetails card style
  fbCard: {
    backgroundColor: COLORS.surface,
    marginHorizontal: 8,
    marginTop: 6,
    marginBottom: 0,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: COLORS.border,
    overflow: 'hidden',
  },
  fbCardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingTop: 14,
    paddingBottom: 10,
  },
  fbCardAvatar: {
    marginRight: 10,
  },
  fbCardAvatarImg: {
    width: 42,
    height: 42,
    borderRadius: 21,
  },
  fbCardAvatarPlaceholder: {
    width: 42,
    height: 42,
    borderRadius: 21,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  fbCardAvatarText: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.primary,
  },
  fbCardMeta: {
    flex: 1,
  },
  fbCardAuthor: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
  },
  fbCardDate: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 1,
  },
  fbCardMenuBtn: {
    width: 32,
    height: 32,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  fbCardBody: {
    paddingHorizontal: 14,
    paddingBottom: 10,
  },
  fbCardTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  fbCardContent: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
  },
  // Collage wrapper (clips overflow for rounded card corners)
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
  fbCardImage: {
    width: '100%',
    height: 260,
    backgroundColor: COLORS.borderLight,
  },
  fbCardImageOverlay: {
    position: 'absolute',
    bottom: 10,
    right: 12,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: 'rgba(0,0,0,0.55)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 4,
  },
  fbCardImageOverlayText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: '600',
  },
  fbCardChips: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 6,
    paddingHorizontal: 14,
    paddingTop: 10,
  },
  fbChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.borderLight,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 4,
  },
  fbChipText: {
    fontSize: 11,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },
  fbCardFooter: {
    flexDirection: 'row',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
    marginTop: 10,
    paddingHorizontal: 6,
    paddingVertical: 6,
  },
  cardMenuDropdown: {
    position: 'absolute',
    top: 34,
    right: 0,
    minWidth: 130,
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    paddingVertical: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.14,
    shadowRadius: 8,
    elevation: 6,
  },
  cardMenuItem: {
    paddingVertical: 10,
    paddingHorizontal: 14,
  },
  cardMenuItemText: {
    fontSize: 13,
    color: '#1F2937',
    fontWeight: '500',
  },
});
