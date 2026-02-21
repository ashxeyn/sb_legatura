// @ts-nocheck
import React, { useState, useEffect, useCallback, useRef, useMemo } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  FlatList,
  Image,
  ActivityIndicator,
  StatusBar,
  Platform,
  Keyboard,
  Dimensions,
  ScrollView,
} from 'react-native';
import ImageFallback from '../../components/ImageFallbackFixed';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import { api_config } from '../../config/api';
import {
  search_service,
  ContractorFilters,
  ProjectFilters,
} from '../../services/search_service';
import FilterSheet from '../../components/FilterSheet';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

/* ===================================================================
 * Default images (same paths as homepage)
 * =================================================================== */
const defaultCoverPhoto = require('../../../assets/images/pictures/cp_default.jpg');
const defaultContractorAvatar = require('../../../assets/images/pictures/contractor_default.png');
const defaultOwnerAvatar = require('../../../assets/images/pictures/property_owner_default.png');

/* ===================================================================
 * Types
 * =================================================================== */
interface Contractor {
  contractor_id: number;
  company_name: string;
  type_name?: string;
  business_address?: string;
  years_of_experience?: number;
  completed_projects?: number;
  user_id?: number;
  profile_pic?: string;
  cover_photo?: string;
  services_offered?: string;
  rating?: number;
  reviews_count?: number;
  picab_category?: string;
}

interface Project {
  project_id: number;
  project_title: string;
  project_description?: string;
  project_location?: string;
  budget_range_min?: number;
  budget_range_max?: number;
  property_type?: string;
  type_name?: string;
  project_status?: string;
  bidding_deadline?: string;
  owner_name?: string;
  owner_profile_pic?: string;
  bids_count?: number;
  created_at?: string;
  files?: any[];
}

type TabKey = 'all' | 'contractors' | 'projects';

interface SearchScreenProps {
  onClose: () => void;
  searchType?: 'contractors' | 'projects';
  contractors?: Contractor[];
  projects?: Project[];
  onContractorPress?: (contractor: Contractor) => void;
  onProjectPress?: (project: Project) => void;
}

/* ===================================================================
 * Component
 * =================================================================== */
export default function SearchScreen({
  onClose,
  searchType: defaultSearchType = 'contractors',
  contractors = [],
  projects = [],
  onContractorPress,
  onProjectPress,
}: SearchScreenProps) {
  const insets = useSafeAreaInsets();
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  // ── Phase: input vs results ───────────────────────────────────────
  const [showResults, setShowResults] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [submittedQuery, setSubmittedQuery] = useState('');
  const [recentSearches, setRecentSearches] = useState<string[]>([]);
  const inputRef = useRef<TextInput>(null);

  // ── Live suggestions (while typing, before Enter) ─────────────────
  const [suggestions, setSuggestions] = useState<any[]>([]);
  const [loadingSuggestions, setLoadingSuggestions] = useState(false);
  const suggestDebounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  // ── Active tab ────────────────────────────────────────────────────
  const [activeTab, setActiveTab] = useState<TabKey>('all');

  // ── Results (separate state per type for "All" tab) ───────────────
  const [contractorResults, setContractorResults] = useState<Contractor[]>([]);
  const [projectResults, setProjectResults] = useState<Project[]>([]);
  const [isSearching, setIsSearching] = useState(false);

  // Pagination per type
  const [contractorPage, setContractorPage] = useState(1);
  const [projectPage, setProjectPage] = useState(1);
  const [contractorHasMore, setContractorHasMore] = useState(false);
  const [projectHasMore, setProjectHasMore] = useState(false);
  const [contractorTotal, setContractorTotal] = useState(0);
  const [projectTotal, setProjectTotal] = useState(0);
  const [loadingMore, setLoadingMore] = useState(false);

  // ── Filters ───────────────────────────────────────────────────────
  const [showFilters, setShowFilters] = useState(false);
  const [contractorFilters, setContractorFilters] = useState<ContractorFilters>({});
  const [projectFilters, setProjectFilters] = useState<ProjectFilters>({});

  const PER_PAGE = 15;

  // ── Active filter count ───────────────────────────────────────────
  const activeFilterCount = useMemo(() => {
    const filters = activeTab === 'projects' ? projectFilters : contractorFilters;
    return Object.values(filters).filter(
      v => v !== undefined && v !== null && v !== '' && v !== 'open'
    ).length;
  }, [activeTab, contractorFilters, projectFilters]);

  // ── Tab definitions (both roles see All / Users / Posts) ──────────
  const tabs: { key: TabKey; label: string; icon: string }[] = useMemo(() => [
    { key: 'all',          label: 'All',   icon: 'search'      },
    { key: 'contractors',  label: 'Users', icon: 'people'      },
    { key: 'projects',     label: 'Posts', icon: 'article'     },
  ], []);

  // ── Perform search ────────────────────────────────────────────────
  const performSearch = useCallback(async (
    query: string,
    tab: TabKey,
    cFilters: ContractorFilters,
    pFilters: ProjectFilters,
    pageNum: number = 1,
    append: boolean = false,
    type?: 'contractors' | 'projects',
  ) => {
    if (!query.trim()) return;

    // Decide what to fetch based on the active tab (or explicit type for pagination)
    const fetchContractors = type === 'contractors' || (!type && (tab === 'all' || tab === 'contractors'));
    const fetchProjects    = type === 'projects'    || (!type && (tab === 'all' || tab === 'projects'));

    if (pageNum === 1) {
      setIsSearching(true);
    } else {
      setLoadingMore(true);
    }

    try {
      // Contractors ("Users")
      if (fetchContractors) {
        const response = await search_service.search_contractors(
          { ...cFilters, search: query.trim() },
          pageNum,
          PER_PAGE,
        );
        if (response.success) {
          const data = response.data?.data || response.data || [];
          const dataArray = Array.isArray(data) ? data : [];
          if (append) {
            setContractorResults(prev => [...prev, ...dataArray]);
          } else {
            setContractorResults(dataArray);
          }
          setContractorHasMore(response.pagination?.has_more || false);
          setContractorTotal(response.pagination?.total || dataArray.length);
          setContractorPage(pageNum);
        }
      }

      // Projects ("Posts")
      if (fetchProjects) {
        const response = await search_service.search_projects(
          { ...pFilters, search: query.trim() },
          pageNum,
          PER_PAGE,
        );
        if (response.success) {
          const data = response.data?.data || response.data || [];
          const dataArray = Array.isArray(data) ? data : [];
          if (append) {
            setProjectResults(prev => [...prev, ...dataArray]);
          } else {
            setProjectResults(dataArray);
          }
          setProjectHasMore(response.pagination?.has_more || false);
          setProjectTotal(response.pagination?.total || dataArray.length);
          setProjectPage(pageNum);
        }
      }
    } catch (err) {
      console.error('Search error:', err);
    } finally {
      setIsSearching(false);
      setLoadingMore(false);
    }
  }, []);

  // ── Live suggestions: debounced fetch while typing ────────────────
  useEffect(() => {
    if (showResults) return; // Don't fetch suggestions when in results phase

    if (suggestDebounceRef.current) clearTimeout(suggestDebounceRef.current);

    const q = searchQuery.trim();
    if (!q || q.length < 2) {
      setSuggestions([]);
      setLoadingSuggestions(false);
      return;
    }

    setLoadingSuggestions(true);
    suggestDebounceRef.current = setTimeout(async () => {
      try {
        const items: any[] = [];

        // Fetch both contractors (Users) and projects (Posts) for suggestions
        const [cRes, pRes] = await Promise.all([
          search_service.search_contractors({ search: q }, 1, 5),
          search_service.search_projects({ search: q }, 1, 5),
        ]);

        if (cRes.success) {
          const cData = cRes.data?.data || cRes.data || [];
          (Array.isArray(cData) ? cData : []).forEach(c =>
            items.push({ type: 'contractor', data: c })
          );
        }
        if (pRes.success) {
          const pData = pRes.data?.data || pRes.data || [];
          (Array.isArray(pData) ? pData : []).forEach(p =>
            items.push({ type: 'project', data: p })
          );
        }

        setSuggestions(items);
      } catch (err) {
        console.error('Suggestions error:', err);
      } finally {
        setLoadingSuggestions(false);
      }
    }, 350);

    return () => {
      if (suggestDebounceRef.current) clearTimeout(suggestDebounceRef.current);
    };
  }, [searchQuery, showResults]);

  // ── Submit search (Enter key) ─────────────────────────────────────
  const handleSubmitSearch = useCallback(() => {
    const q = searchQuery.trim();
    if (!q) return;

    Keyboard.dismiss();
    setSubmittedQuery(q);
    setShowResults(true);
    setSuggestions([]); // Clear suggestions

    // Save to recent searches
    setRecentSearches(prev => {
      const filtered = prev.filter(s => s !== q);
      return [q, ...filtered.slice(0, 9)];
    });

    // Execute search
    performSearch(q, activeTab, contractorFilters, projectFilters, 1, false);
  }, [searchQuery, activeTab, contractorFilters, projectFilters, performSearch]);

  // ── Re-search when tab or filters change (only in results phase) ──
  useEffect(() => {
    if (showResults && submittedQuery) {
      performSearch(submittedQuery, activeTab, contractorFilters, projectFilters, 1, false);
    }
  }, [activeTab, contractorFilters, projectFilters]);

  // ── Load more (pagination) ────────────────────────────────────────
  const loadMore = useCallback(() => {
    if (loadingMore || !submittedQuery) return;

    if (activeTab === 'contractors' && contractorHasMore) {
      performSearch(submittedQuery, activeTab, contractorFilters, projectFilters, contractorPage + 1, true, 'contractors');
    } else if (activeTab === 'projects' && projectHasMore) {
      performSearch(submittedQuery, activeTab, contractorFilters, projectFilters, projectPage + 1, true, 'projects');
    }
  }, [loadingMore, submittedQuery, activeTab, contractorHasMore, projectHasMore, contractorPage, projectPage, contractorFilters, projectFilters, performSearch]);

  // ── Go back to input phase ────────────────────────────────────────
  const handleBackToInput = useCallback(() => {
    if (showResults) {
      setShowResults(false);
      setTimeout(() => inputRef.current?.focus(), 100);
    } else {
      onClose();
    }
  }, [showResults, onClose]);

  // ── Tap recent search ─────────────────────────────────────────────
  const handleRecentTap = useCallback((query: string) => {
    setSearchQuery(query);
    setSubmittedQuery(query);
    setShowResults(true);
    Keyboard.dismiss();
    performSearch(query, activeTab, contractorFilters, projectFilters, 1, false);
  }, [activeTab, contractorFilters, projectFilters, performSearch]);

  // ── Clear search ──────────────────────────────────────────────────
  const handleClearSearch = useCallback(() => {
    setSearchQuery('');
    setSubmittedQuery('');
    setShowResults(false);
    setContractorResults([]);
    setProjectResults([]);
    setSuggestions([]);
    inputRef.current?.focus();
  }, []);

  // ── Handle filter apply ───────────────────────────────────────────
  const handleFilterApply = useCallback((filters: ContractorFilters | ProjectFilters) => {
    if (activeTab === 'projects') {
      setProjectFilters(filters as ProjectFilters);
    } else {
      setContractorFilters(filters as ContractorFilters);
    }
  }, [activeTab]);

  // ── Helpers ───────────────────────────────────────────────────────
  const getInitials = (name: string): string => {
    if (!name) return 'CO';
    return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
  };

  const formatCurrency = (amount?: number): string => {
    if (!amount) return '₱0';
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(amount);
  };

  const getDaysRemaining = (deadline: string): number | null => {
    if (!deadline) return null;
    const diff = new Date(deadline).getTime() - Date.now();
    return Math.ceil(diff / 86400000);
  };

  // ── Active filter chips ───────────────────────────────────────────
  const getFilterChips = useCallback(() => {
    const chips: { label: string; key: string; filterType: 'contractor' | 'project' }[] = [];
    const cf = contractorFilters as any;
    const pf = projectFilters as any;

    if (activeTab !== 'projects') {
      if (cf.type_id) chips.push({ label: `Type: ${cf.type_id}`, key: 'c_type_id', filterType: 'contractor' });
      if (cf.province) chips.push({ label: cf.province, key: 'c_province', filterType: 'contractor' });
      if (cf.city) chips.push({ label: cf.city, key: 'c_city', filterType: 'contractor' });
      if (cf.min_experience) chips.push({ label: `${cf.min_experience}+ yrs`, key: 'c_min_experience', filterType: 'contractor' });
      if (cf.picab_category) chips.push({ label: `PICAB ${cf.picab_category}`, key: 'c_picab_category', filterType: 'contractor' });
      if (cf.min_completed) chips.push({ label: `${cf.min_completed}+ projects`, key: 'c_min_completed', filterType: 'contractor' });
    }
    if (activeTab !== 'contractors') {
      if (pf.type_id) chips.push({ label: `Type: ${pf.type_id}`, key: 'p_type_id', filterType: 'project' });
      if (pf.property_type) chips.push({ label: pf.property_type, key: 'p_property_type', filterType: 'project' });
      if (pf.province) chips.push({ label: pf.province, key: 'p_province', filterType: 'project' });
      if (pf.city) chips.push({ label: pf.city, key: 'p_city', filterType: 'project' });
      if (pf.project_status && pf.project_status !== 'open') {
        const labels: Record<string, string> = { completed: 'Completed', all: 'All Status' };
        chips.push({ label: labels[pf.project_status] || pf.project_status, key: 'p_project_status', filterType: 'project' });
      }
      if (pf.budget_min || pf.budget_max) {
        chips.push({ label: `${formatCurrency(pf.budget_min)} – ${formatCurrency(pf.budget_max)}`, key: 'p_budget', filterType: 'project' });
      }
    }
    return chips;
  }, [activeTab, contractorFilters, projectFilters]);

  const removeFilterChip = useCallback((chip: { key: string; filterType: 'contractor' | 'project' }) => {
    if (chip.filterType === 'contractor') {
      const realKey = chip.key.replace('c_', '');
      const newFilters = { ...contractorFilters };
      delete (newFilters as any)[realKey];
      setContractorFilters(newFilters);
    } else {
      const realKey = chip.key.replace('p_', '');
      const newFilters = { ...projectFilters };
      if (realKey === 'budget') {
        delete (newFilters as any).budget_min;
        delete (newFilters as any).budget_max;
      } else {
        delete (newFilters as any)[realKey];
      }
      setProjectFilters(newFilters);
    }
  }, [contractorFilters, projectFilters]);

  // ── Status helpers ────────────────────────────────────────────────
  const getStatusStyle = (status: string) => {
    const map: Record<string, { backgroundColor: string; textColor: string }> = {
      open: { backgroundColor: '#E8F5E9', textColor: '#2E7D32' },
      completed: { backgroundColor: '#E3F2FD', textColor: '#1565C0' },
      in_progress: { backgroundColor: '#FFF3E0', textColor: '#E65100' },
      bidding_closed: { backgroundColor: '#FCE4EC', textColor: '#C62828' },
    };
    return map[status] || { backgroundColor: '#F5F5F5', textColor: '#666' };
  };

  const getStatusLabel = (status: string) => {
    const map: Record<string, string> = {
      open: 'Open for Bidding',
      completed: 'Completed',
      in_progress: 'In Progress',
      bidding_closed: 'Bidding Closed',
    };
    return map[status] || status;
  };

  // ==================================================================
  //  RENDER: Contractor Card (feed-style, matching homepage)
  // ==================================================================
  const renderContractorCard = (item: Contractor) => {
    const hasCoverPhoto = item.cover_photo && !item.cover_photo.includes('placeholder');
    const coverPhotoUri = hasCoverPhoto
      ? `${api_config.base_url}/storage/${item.cover_photo}`
      : null;
    const logoUri = item.profile_pic
      ? (item.profile_pic.startsWith('http') ? item.profile_pic : `${api_config.base_url}/storage/${item.profile_pic}`)
      : null;
    const initials = getInitials(item.company_name);

    return (
      <TouchableOpacity
        style={styles.card}
        activeOpacity={0.7}
        onPress={() => onContractorPress?.(item)}
      >
        {/* Cover Photo */}
        <ImageFallback
          uri={coverPhotoUri || undefined}
          defaultImage={defaultCoverPhoto}
          style={styles.coverPhoto}
          resizeMode="cover"
        />

        {/* Type Badge (overlapping cover) */}
        <View style={styles.typeBadgeOverlap}>
          <View style={styles.typeBadgeContainer}>
            <Text style={styles.typeBadgeText}>{item.type_name || 'General'}</Text>
          </View>
        </View>

        {/* Avatar overlapping cover */}
        <View style={styles.avatarRow}>
          <View style={styles.avatarWrapper}>
            <ImageFallback
              uri={logoUri || undefined}
              defaultImage={defaultContractorAvatar}
              style={styles.avatarImg}
              resizeMode="cover"
            />
          </View>
        </View>

        {/* Info */}
        <View style={styles.cardBody}>
          <Text style={styles.cardTitle} numberOfLines={2}>{item.company_name}</Text>
          <Text style={styles.cardSubtitle}>
            {item.years_of_experience || 0} years experience
          </Text>

          {/* Details */}
          <View style={styles.cardDetails}>
            <View style={styles.detailRow}>
              <MaterialIcons name="location-on" size={16} color="#666" />
              <Text style={styles.detailText} numberOfLines={1}>
                {item.business_address || 'Location not specified'}
              </Text>
            </View>
            <View style={styles.detailRow}>
              <MaterialIcons name="star" size={16} color="#EC7E00" />
              <Text style={styles.detailText}>
                {item.rating?.toFixed(1) || '5.0'} rating • {item.reviews_count || 0} reviews
              </Text>
            </View>
            <View style={styles.detailRow}>
              <MaterialIcons name="work" size={16} color="#666" />
              <Text style={styles.detailText}>
                {item.completed_projects || 0} projects completed
                {item.picab_category ? ` • PICAB ${item.picab_category}` : ''}
              </Text>
            </View>
          </View>
        </View>

        {/* Footer */}
        <View style={styles.cardFooter}>
          <TouchableOpacity style={styles.cardActionButton} activeOpacity={0.8}>
            <MaterialIcons name="visibility" size={18} color="#FFFFFF" />
            <Text style={styles.cardActionText}>View Profile</Text>
          </TouchableOpacity>
        </View>
      </TouchableOpacity>
    );
  };

  // ==================================================================
  //  RENDER: Project Card (feed-style, matching homepage)
  // ==================================================================
  const renderProjectCard = (item: Project) => {
    const ownerProfileUrl = item.owner_profile_pic
      ? `${api_config.base_url}/storage/${item.owner_profile_pic}`
      : null;
    const daysRemaining = item.bidding_deadline ? getDaysRemaining(item.bidding_deadline) : null;
    const statusStyle = item.project_status ? getStatusStyle(item.project_status) : null;

    return (
      <TouchableOpacity
        style={styles.card}
        activeOpacity={0.7}
        onPress={() => onProjectPress?.(item)}
      >
        {/* Header: Owner info + deadline */}
        <View style={styles.projectHeader}>
          <View style={styles.ownerInfo}>
            <ImageFallback
              uri={ownerProfileUrl || undefined}
              defaultImage={defaultOwnerAvatar}
              style={styles.ownerAvatar}
              resizeMode="cover"
            />
            <View>
              <Text style={styles.ownerName}>{item.owner_name || 'Property Owner'}</Text>
              <Text style={styles.postDate}>
                {item.created_at
                  ? `Posted ${new Date(item.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`
                  : 'Recently posted'}
              </Text>
            </View>
          </View>
          {daysRemaining !== null && (
            <View style={[styles.deadlineBadge, daysRemaining !== null && daysRemaining <= 3 && styles.deadlineUrgent]}>
              <MaterialIcons
                name="access-time"
                size={14}
                color={daysRemaining <= 3 ? '#E74C3C' : '#F39C12'}
              />
              <Text style={[styles.deadlineText, daysRemaining <= 3 && styles.deadlineTextUrgent]}>
                {daysRemaining > 0 ? `${daysRemaining}d left` : 'Due today'}
              </Text>
            </View>
          )}
        </View>

        {/* Title */}
        <Text style={styles.projectTitle}>{item.project_title}</Text>

        {/* Description */}
        {item.project_description && (
          <Text style={styles.projectDescription} numberOfLines={2}>
            {item.project_description}
          </Text>
        )}

        {/* Badges row */}
        <View style={styles.badgesRow}>
          {item.type_name && (
            <View style={styles.projectTypeBadge}>
              <MaterialIcons name="business" size={14} color="#EC7E00" />
              <Text style={styles.projectTypeText}>{item.type_name}</Text>
            </View>
          )}
          {statusStyle && item.project_status !== 'open' && (
            <View style={[styles.statusBadge, { backgroundColor: statusStyle.backgroundColor }]}>
              <Text style={[styles.statusBadgeText, { color: statusStyle.textColor }]}>
                {getStatusLabel(item.project_status!)}
              </Text>
            </View>
          )}
        </View>

        {/* Details */}
        <View style={styles.cardDetails}>
          {item.project_location && (
            <View style={styles.detailRow}>
              <MaterialIcons name="location-on" size={16} color="#666" />
              <Text style={styles.detailText} numberOfLines={1}>{item.project_location}</Text>
            </View>
          )}
          <View style={styles.detailRow}>
            <MaterialIcons name="account-balance-wallet" size={16} color="#666" />
            <Text style={styles.detailText}>
              {formatCurrency(item.budget_range_min)} – {formatCurrency(item.budget_range_max)}
            </Text>
          </View>
          {item.bids_count !== undefined && (
            <View style={styles.detailRow}>
              <MaterialIcons name="gavel" size={16} color="#666" />
              <Text style={styles.detailText}>{item.bids_count || 0} bids received</Text>
            </View>
          )}
        </View>

        {/* Footer */}
        <View style={styles.cardFooter}>
          <TouchableOpacity style={styles.cardActionButton} activeOpacity={0.8}>
            <MaterialIcons name="visibility" size={18} color="#FFFFFF" />
            <Text style={styles.cardActionText}>View Project</Text>
          </TouchableOpacity>
        </View>
      </TouchableOpacity>
    );
  };

  // ── Build combined data for FlatList ──────────────────────────────
  const listData = useMemo(() => {
    if (activeTab === 'contractors') {
      return contractorResults.map(c => ({ type: 'contractor' as const, data: c }));
    }
    if (activeTab === 'projects') {
      return projectResults.map(p => ({ type: 'project' as const, data: p }));
    }
    // "All" tab: show Users section then Posts section
    const combined: { type: 'contractor' | 'project' | 'section_header' | 'see_all'; data: any }[] = [];
    if (contractorResults.length > 0) {
      combined.push({ type: 'section_header', data: { title: `Users (${contractorTotal})` } });
      contractorResults.slice(0, 5).forEach(c =>
        combined.push({ type: 'contractor', data: c })
      );
      if (contractorResults.length > 5 || contractorHasMore) {
        combined.push({ type: 'see_all', data: { label: 'See all users', tab: 'contractors' } });
      }
    }
    if (projectResults.length > 0) {
      combined.push({ type: 'section_header', data: { title: `Posts (${projectTotal})` } });
      projectResults.slice(0, 5).forEach(p =>
        combined.push({ type: 'project', data: p })
      );
      if (projectResults.length > 5 || projectHasMore) {
        combined.push({ type: 'see_all', data: { label: 'See all posts', tab: 'projects' } });
      }
    }
    return combined;
  }, [activeTab, contractorResults, projectResults, contractorTotal, projectTotal, contractorHasMore, projectHasMore]);

  const totalResults = activeTab === 'contractors' ? contractorTotal
    : activeTab === 'projects' ? projectTotal
    : contractorTotal + projectTotal;

  // ── Render list item ──────────────────────────────────────────────
  const renderItem = useCallback(({ item }: { item: any }) => {
    if (item.type === 'see_all') {
      return (
        <TouchableOpacity
          style={styles.seeAllRow}
          onPress={() => setActiveTab(item.data.tab)}
        >
          <Text style={styles.seeAllText}>{item.data.label}</Text>
          <MaterialIcons name="arrow-forward" size={18} color="#EC7E00" />
        </TouchableOpacity>
      );
    }
    if (item.type === 'section_header') {
      return (
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionHeaderText}>{item.data.title}</Text>
        </View>
      );
    }
    if (item.type === 'contractor') return renderContractorCard(item.data);
    if (item.type === 'project') return renderProjectCard(item.data);
    return null;
  }, [onContractorPress, onProjectPress]);

  const getItemKey = (item: any, index: number) => {
    if (item.type === 'section_header') return `section-${index}`;
    if (item.type === 'see_all') return `seeall-${index}`;
    if (item.type === 'contractor') return `c-${item.data.contractor_id}`;
    return `p-${item.data.project_id}`;
  };

  // ── Handle suggestion tap: go straight to full results ────────────
  const handleSuggestionContractorPress = useCallback((contractor: any) => {
    // Save query to recents, then open results page with the contractor selected
    const q = searchQuery.trim();
    if (q) {
      setRecentSearches(prev => {
        const filtered = prev.filter(s => s !== q);
        return [q, ...filtered.slice(0, 9)];
      });
    }
    setSuggestions([]);
    setShowResults(false);
    onContractorPress?.(contractor);
  }, [searchQuery, onContractorPress]);

  const handleSuggestionProjectPress = useCallback((project: any) => {
    const q = searchQuery.trim();
    if (q) {
      setRecentSearches(prev => {
        const filtered = prev.filter(s => s !== q);
        return [q, ...filtered.slice(0, 9)];
      });
    }
    setSuggestions([]);
    setShowResults(false);
    onProjectPress?.(project);
  }, [searchQuery, onProjectPress]);

  // ── Render a compact suggestion row ───────────────────────────────
  const renderSuggestionItem = ({ item }: { item: any }) => {
    if (item.type === 'contractor') {
      const c = item.data;
      const logoUri = c.profile_pic
        ? (c.profile_pic.startsWith('http') ? c.profile_pic : `${api_config.base_url}/storage/${c.profile_pic}`)
        : null;
      return (
        <TouchableOpacity
          style={styles.suggestionRow}
          onPress={() => handleSuggestionContractorPress(c)}
          activeOpacity={0.7}
        >
          <ImageFallback
            uri={logoUri || undefined}
            defaultImage={defaultContractorAvatar}
            style={styles.suggestionAvatar}
            resizeMode="cover"
          />
          <View style={styles.suggestionInfo}>
            <Text style={styles.suggestionTitle} numberOfLines={1}>{c.company_name}</Text>
            <Text style={styles.suggestionMeta} numberOfLines={1}>
              {c.type_name || 'Contractor'}
              {c.business_address ? ` • ${c.business_address}` : ''}
            </Text>
          </View>
          <View style={styles.suggestionTypeBadge}>
            <MaterialIcons name="engineering" size={14} color="#65676B" />
          </View>
        </TouchableOpacity>
      );
    }

    if (item.type === 'project') {
      const p = item.data;
      const ownerUri = p.owner_profile_pic
        ? `${api_config.base_url}/storage/${p.owner_profile_pic}`
        : null;
      return (
        <TouchableOpacity
          style={styles.suggestionRow}
          onPress={() => handleSuggestionProjectPress(p)}
          activeOpacity={0.7}
        >
          <ImageFallback
            uri={ownerUri || undefined}
            defaultImage={defaultOwnerAvatar}
            style={styles.suggestionAvatar}
            resizeMode="cover"
          />
          <View style={styles.suggestionInfo}>
            <Text style={styles.suggestionTitle} numberOfLines={1}>{p.project_title}</Text>
            <Text style={styles.suggestionMeta} numberOfLines={1}>
              {p.type_name || 'Project'}
              {p.project_location ? ` • ${p.project_location}` : ''}
            </Text>
          </View>
          <View style={styles.suggestionTypeBadge}>
            <MaterialIcons name="business" size={14} color="#65676B" />
          </View>
        </TouchableOpacity>
      );
    }
    return null;
  };

  // ==================================================================
  //  RENDER: Search Input Phase (before pressing Enter)
  // ==================================================================
  const hasSuggestions = suggestions.length > 0;
  const showSuggestions = searchQuery.trim().length >= 2 && (hasSuggestions || loadingSuggestions);

  const renderInputPhase = () => (
    <View style={styles.inputPhaseContainer}>
      {/* ── Live suggestions while typing ─────────────────────────── */}
      {showSuggestions && (
        <View style={styles.suggestionsContainer}>
          {loadingSuggestions && suggestions.length === 0 ? (
            <View style={styles.suggestionsLoading}>
              <ActivityIndicator size="small" color="#EC7E00" />
              <Text style={styles.suggestionsLoadingText}>Searching...</Text>
            </View>
          ) : (
            <>
              <FlatList
                data={suggestions}
                keyExtractor={(item, i) =>
                  item.type === 'contractor'
                    ? `sc-${item.data.contractor_id}`
                    : `sp-${item.data.project_id}`
                }
                renderItem={renderSuggestionItem}
                showsVerticalScrollIndicator={false}
                keyboardShouldPersistTaps="handled"
                ListFooterComponent={
                  <TouchableOpacity
                    style={styles.seeAllSuggestionsRow}
                    onPress={handleSubmitSearch}
                  >
                    <MaterialIcons name="search" size={18} color="#EC7E00" />
                    <Text style={styles.seeAllSuggestionsText}>
                      See all results for "{searchQuery.trim()}"
                    </Text>
                  </TouchableOpacity>
                }
              />
            </>
          )}
        </View>
      )}

      {/* ── Recent searches (only when not typing) ───────────────── */}
      {!showSuggestions && recentSearches.length > 0 && (
        <View style={styles.recentSection}>
          <View style={styles.recentHeader}>
            <Text style={styles.recentTitle}>Recent Searches</Text>
            <TouchableOpacity onPress={() => setRecentSearches([])}>
              <Text style={styles.clearAllText}>Clear All</Text>
            </TouchableOpacity>
          </View>
          {recentSearches.map((q, i) => (
            <TouchableOpacity
              key={i}
              style={styles.recentItem}
              onPress={() => handleRecentTap(q)}
            >
              <View style={styles.recentIconCircle}>
                <MaterialIcons name="history" size={18} color="#65676B" />
              </View>
              <Text style={styles.recentText} numberOfLines={1}>{q}</Text>
              <TouchableOpacity
                onPress={() => setRecentSearches(prev => prev.filter(s => s !== q))}
                hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
              >
                <MaterialIcons name="close" size={18} color="#BCC0C4" />
              </TouchableOpacity>
            </TouchableOpacity>
          ))}
        </View>
      )}

      {/* ── Empty state (no query, no recents) ───────────────────── */}
      {!showSuggestions && recentSearches.length === 0 && (
        <View style={styles.emptyInputState}>
          <MaterialIcons name="search" size={72} color="#E4E6EB" />
          <Text style={styles.emptyTitle}>
            Search users &amp; posts
          </Text>
          <Text style={styles.emptySubtitle}>
            Start typing to see live results
          </Text>
        </View>
      )}
    </View>
  );

  // ==================================================================
  //  RENDER: Results Phase (after pressing Enter)
  // ==================================================================
  const filterChips = getFilterChips();

  const renderResultsPhase = () => (
    <View style={styles.resultsContainer}>
      {/* ── Tab Bar ─────────────────────────────────────────────── */}
      <View style={styles.tabBar}>
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={styles.tabBarContent}
        >
          {tabs.map(tab => {
            const isActive = activeTab === tab.key;
            return (
              <TouchableOpacity
                key={tab.key}
                style={[styles.tab, isActive && styles.tabActive]}
                onPress={() => setActiveTab(tab.key)}
              >
                <MaterialIcons
                  name={tab.icon as any}
                  size={18}
                  color={isActive ? '#EC7E00' : '#65676B'}
                />
                <Text style={[styles.tabLabel, isActive && styles.tabLabelActive]}>
                  {tab.label}
                </Text>
              </TouchableOpacity>
            );
          })}

          {/* Filter button (only for single-type tabs, not "All") */}
          {activeTab !== 'all' && (
            <TouchableOpacity
              style={[styles.filterTab, activeFilterCount > 0 && styles.filterTabActive]}
              onPress={() => setShowFilters(true)}
            >
              <MaterialIcons
                name="tune"
                size={18}
                color={activeFilterCount > 0 ? '#EC7E00' : '#65676B'}
              />
              <Text style={[styles.tabLabel, activeFilterCount > 0 && styles.tabLabelActive]}>
                Filters
              </Text>
              {activeFilterCount > 0 && (
                <View style={styles.filterBadge}>
                  <Text style={styles.filterBadgeText}>{activeFilterCount}</Text>
                </View>
              )}
            </TouchableOpacity>
          )}
        </ScrollView>
      </View>

      {/* ── Active filter chips ─────────────────────────────────── */}
      {filterChips.length > 0 && (
        <View style={styles.chipBar}>
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.chipBarContent}
          >
            {filterChips.map(chip => (
              <View key={chip.key} style={styles.activeChip}>
                <Text style={styles.activeChipText}>{chip.label}</Text>
                <TouchableOpacity
                  onPress={() => removeFilterChip(chip)}
                  hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
                >
                  <Ionicons name="close-circle" size={16} color="#EC7E00" />
                </TouchableOpacity>
              </View>
            ))}
            <TouchableOpacity
              style={styles.clearChipsBtn}
              onPress={() => {
                setContractorFilters({});
                setProjectFilters({});
              }}
            >
              <Text style={styles.clearChipsText}>Clear all</Text>
            </TouchableOpacity>
          </ScrollView>
        </View>
      )}

      {/* ── Loading ─────────────────────────────────────────────── */}
      {isSearching && (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#EC7E00" />
          <Text style={styles.loadingText}>Searching...</Text>
        </View>
      )}

      {/* ── Results Feed ────────────────────────────────────────── */}
      {!isSearching && listData.length > 0 && (
        <FlatList
          data={listData}
          keyExtractor={getItemKey}
          renderItem={renderItem}
          showsVerticalScrollIndicator={false}
          keyboardShouldPersistTaps="handled"
          onEndReached={activeTab !== 'all' ? loadMore : undefined}
          onEndReachedThreshold={0.3}
          ListHeaderComponent={
            <View style={styles.resultsCountBar}>
              <Text style={styles.resultsCountText}>
                {totalResults} result{totalResults !== 1 ? 's' : ''} for "{submittedQuery}"
              </Text>
            </View>
          }
          ListFooterComponent={
            loadingMore ? (
              <View style={styles.footerLoader}>
                <ActivityIndicator size="small" color="#EC7E00" />
              </View>
            ) : null
          }
        />
      )}

      {/* ── No Results ──────────────────────────────────────────── */}
      {!isSearching && listData.length === 0 && (
        <View style={styles.noResultsContainer}>
          <MaterialIcons name="search-off" size={72} color="#E4E6EB" />
          <Text style={styles.noResultsTitle}>No results found</Text>
          <Text style={styles.noResultsSubtitle}>
            No matches for "{submittedQuery}".{'\n'}Try different keywords or adjust filters.
          </Text>
          {activeFilterCount > 0 && (
            <TouchableOpacity
              style={styles.clearFiltersButton}
              onPress={() => {
                setContractorFilters({});
                setProjectFilters({});
              }}
            >
              <Text style={styles.clearFiltersText}>Clear all filters</Text>
            </TouchableOpacity>
          )}
        </View>
      )}
    </View>
  );

  // ==================================================================
  //  MAIN RENDER
  // ==================================================================
  return (
    <SafeAreaView style={[styles.container, { paddingTop: statusBarHeight }]}>
      <StatusBar hidden={true} />

      {/* ── Search Header ─────────────────────────────────────────── */}
      <View style={styles.searchHeader}>
        <TouchableOpacity onPress={handleBackToInput} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color="#1C1E21" />
        </TouchableOpacity>

        <View style={styles.searchInputContainer}>
          <MaterialIcons name="search" size={20} color="#65676B" />
          <TextInput
            ref={inputRef}
            style={styles.searchInput}
            placeholder="Search users, posts..."
            placeholderTextColor="#65676B"
            value={searchQuery}
            onChangeText={setSearchQuery}
            onSubmitEditing={handleSubmitSearch}
            autoFocus={!showResults}
            returnKeyType="search"
          />
          {searchQuery.length > 0 && (
            <TouchableOpacity onPress={handleClearSearch} style={styles.clearButton}>
              <Ionicons name="close-circle" size={20} color="#65676B" />
            </TouchableOpacity>
          )}
        </View>

        {showResults && searchQuery.trim() && (
          <TouchableOpacity onPress={handleSubmitSearch} style={styles.searchActionBtn}>
            <Text style={styles.searchActionText}>Search</Text>
          </TouchableOpacity>
        )}
      </View>

      {/* ── Content ───────────────────────────────────────────────── */}
      {showResults ? renderResultsPhase() : renderInputPhase()}

      {/* ── Filter Sheet (advanced filters) ───────────────────────── */}
      <FilterSheet
        visible={showFilters}
        onClose={() => setShowFilters(false)}
        onApply={handleFilterApply}
        searchType={activeTab === 'projects' ? 'projects' : 'contractors'}
        initialFilters={activeTab === 'projects' ? projectFilters : contractorFilters}
      />
    </SafeAreaView>
  );
}

/* ===================================================================
 * Styles
 * =================================================================== */
const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F0F2F5',
  },

  // ── Search Header ──────────────────────────────────────────────
  searchHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 8,
    paddingVertical: 8,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E4E6EB',
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 2,
  },
  backButton: {
    padding: 8,
  },
  searchInputContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F0F2F5',
    borderRadius: 20,
    paddingHorizontal: 12,
    height: 40,
    marginLeft: 4,
  },
  searchInput: {
    flex: 1,
    fontSize: 16,
    color: '#1C1E21',
    marginLeft: 8,
    paddingVertical: 0,
  },
  clearButton: {
    padding: 4,
  },
  searchActionBtn: {
    marginLeft: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  searchActionText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#EC7E00',
  },

  // ── Tab Bar ────────────────────────────────────────────────────
  tabBar: {
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E4E6EB',
  },
  tabBarContent: {
    paddingHorizontal: 8,
    paddingVertical: 8,
    gap: 6,
  },
  tab: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#F0F2F5',
    gap: 6,
  },
  tabActive: {
    backgroundColor: '#FFF3E0',
  },
  tabLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: '#65676B',
  },
  tabLabelActive: {
    color: '#EC7E00',
    fontWeight: '600',
  },
  filterTab: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#F0F2F5',
    gap: 6,
    borderWidth: 1,
    borderColor: 'transparent',
  },
  filterTabActive: {
    backgroundColor: '#FFF3E0',
    borderColor: '#FFCC80',
  },
  filterBadge: {
    backgroundColor: '#EC7E00',
    borderRadius: 10,
    width: 18,
    height: 18,
    justifyContent: 'center',
    alignItems: 'center',
  },
  filterBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    color: '#FFF',
  },

  // ── Filter Chips Bar ───────────────────────────────────────────
  chipBar: {
    backgroundColor: '#FFFFFF',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#E4E6EB',
  },
  chipBarContent: {
    paddingHorizontal: 12,
    gap: 6,
    flexDirection: 'row',
    alignItems: 'center',
  },
  activeChip: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF3E0',
    borderRadius: 16,
    paddingLeft: 12,
    paddingRight: 6,
    paddingVertical: 6,
    borderWidth: 1,
    borderColor: '#FFCC80',
    gap: 4,
  },
  activeChipText: {
    fontSize: 12,
    color: '#E65100',
    fontWeight: '600',
  },
  clearChipsBtn: {
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  clearChipsText: {
    fontSize: 12,
    color: '#EC7E00',
    fontWeight: '600',
  },

  // ── Results Count ──────────────────────────────────────────────
  resultsCountBar: {
    paddingHorizontal: 16,
    paddingVertical: 10,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E4E6EB',
  },
  resultsCountText: {
    fontSize: 13,
    color: '#65676B',
  },
  resultsContainer: {
    flex: 1,
  },

  // ── Section Headers ("All" tab) ────────────────────────────────
  sectionHeader: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E4E6EB',
    marginTop: 8,
  },
  sectionHeaderText: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1C1E21',
  },
  seeAllRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 8,
    borderBottomColor: '#F0F2F5',
    gap: 6,
  },
  seeAllText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#EC7E00',
  },

  // ── Feed Cards (shared) ────────────────────────────────────────
  card: {
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 16,
    paddingBottom: 0,
    borderBottomWidth: 8,
    borderBottomColor: '#F0F2F5',
    position: 'relative',
    overflow: 'visible',
  },
  coverPhoto: {
    height: 110,
    width: SCREEN_WIDTH,
    backgroundColor: '#E4E6EB',
    alignSelf: 'center',
    marginHorizontal: -16,
  },
  typeBadgeOverlap: {
    position: 'absolute',
    right: 12,
    top: 124,
    zIndex: 20,
    elevation: 20,
  },
  typeBadgeContainer: {
    backgroundColor: '#FFF3E6',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  typeBadgeText: {
    fontSize: 12,
    fontWeight: '500',
    color: '#EC7E00',
  },
  avatarRow: {
    marginTop: -36,
    paddingHorizontal: 0,
    marginBottom: 8,
  },
  avatarWrapper: {
    width: 72,
    height: 72,
  },
  avatarImg: {
    width: 72,
    height: 72,
    borderRadius: 36,
    borderWidth: 3,
    borderColor: '#FFFFFF',
    backgroundColor: '#E4E6EB',
  },
  cardBody: {
    marginTop: -8,
    paddingBottom: 4,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1C1E21',
  },
  cardSubtitle: {
    fontSize: 12,
    color: '#65676B',
    marginTop: 2,
  },
  cardDetails: {
    gap: 6,
    marginTop: 10,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  detailText: {
    fontSize: 13,
    color: '#65676B',
    flex: 1,
  },
  cardFooter: {
    flexDirection: 'row',
    marginTop: 14,
    paddingTop: 12,
    paddingBottom: 14,
    borderTopWidth: 1,
    borderTopColor: '#E4E6EB',
  },
  cardActionButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EC7E00',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 8,
    gap: 6,
    flex: 1,
  },
  cardActionText: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },

  // ── Project-specific card elements ─────────────────────────────
  projectHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingTop: 16,
    marginBottom: 12,
  },
  ownerInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  ownerAvatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    marginRight: 10,
    backgroundColor: '#E4E6EB',
  },
  ownerName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1C1E21',
  },
  postDate: {
    fontSize: 12,
    color: '#65676B',
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
  projectTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1C1E21',
    marginBottom: 6,
  },
  projectDescription: {
    fontSize: 14,
    color: '#65676B',
    lineHeight: 20,
    marginBottom: 10,
  },
  badgesRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: 10,
  },
  projectTypeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF3E6',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 6,
  },
  projectTypeText: {
    fontSize: 12,
    fontWeight: '500',
    color: '#EC7E00',
  },
  statusBadge: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12,
  },
  statusBadgeText: {
    fontSize: 12,
    fontWeight: '600',
  },

  // ── Loading / Empty states ─────────────────────────────────────
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 40,
  },
  loadingText: {
    fontSize: 14,
    color: '#65676B',
    marginTop: 12,
  },
  footerLoader: {
    paddingVertical: 20,
    alignItems: 'center',
  },
  noResultsContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 40,
  },
  noResultsTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#1C1E21',
    marginTop: 16,
  },
  noResultsSubtitle: {
    fontSize: 14,
    color: '#65676B',
    marginTop: 8,
    textAlign: 'center',
    lineHeight: 20,
  },
  clearFiltersButton: {
    marginTop: 16,
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 20,
    backgroundColor: '#FFF3E0',
  },
  clearFiltersText: {
    fontSize: 14,
    color: '#EC7E00',
    fontWeight: '600',
  },

  // ── Input Phase ────────────────────────────────────────────────
  inputPhaseContainer: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  recentSection: {
    paddingTop: 4,
  },
  recentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  recentTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1C1E21',
  },
  clearAllText: {
    fontSize: 14,
    color: '#EC7E00',
    fontWeight: '500',
  },
  recentItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    gap: 12,
  },
  recentIconCircle: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: '#F0F2F5',
    justifyContent: 'center',
    alignItems: 'center',
  },
  recentText: {
    fontSize: 15,
    color: '#1C1E21',
    flex: 1,
  },

  // ── Live Suggestions ───────────────────────────────────────────
  suggestionsContainer: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  suggestionsLoading: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 20,
    gap: 8,
  },
  suggestionsLoadingText: {
    fontSize: 14,
    color: '#65676B',
  },
  suggestionRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F2F5',
  },
  suggestionAvatar: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#E4E6EB',
    marginRight: 12,
  },
  suggestionInfo: {
    flex: 1,
  },
  suggestionTitle: {
    fontSize: 15,
    fontWeight: '600',
    color: '#1C1E21',
  },
  suggestionMeta: {
    fontSize: 13,
    color: '#65676B',
    marginTop: 2,
  },
  suggestionTypeBadge: {
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: '#F0F2F5',
    justifyContent: 'center',
    alignItems: 'center',
    marginLeft: 8,
  },
  seeAllSuggestionsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 14,
    gap: 8,
    borderTopWidth: 1,
    borderTopColor: '#E4E6EB',
  },
  seeAllSuggestionsText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#EC7E00',
  },

  emptyInputState: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 40,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1C1E21',
    marginTop: 16,
  },
  emptySubtitle: {
    fontSize: 14,
    color: '#65676B',
    marginTop: 8,
    textAlign: 'center',
    lineHeight: 20,
  },
});
