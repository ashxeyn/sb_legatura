// @ts-nocheck
import React, { useState, useEffect, useCallback } from 'react';
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
} from 'react-native';
import ImageFallback from '../../components/ImageFallback';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons, Feather } from '@expo/vector-icons';
import { api_config } from '../../config/api';

interface Contractor {
  contractor_id: number;
  company_name: string;
  type_name?: string;
  business_address?: string;
  years_of_experience?: number;
  completed_projects?: number;
  user_id?: number;
  profile_pic?: string;
  services_offered?: string;
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
  bidding_deadline?: string;
  owner_name?: string;
  owner_profile_pic?: string;
  bids_count?: number;
}

interface SearchScreenProps {
  onClose: () => void;
  searchType?: 'contractors' | 'projects';
  contractors?: Contractor[];
  projects?: Project[];
  onContractorPress?: (contractor: Contractor) => void;
  onProjectPress?: (project: Project) => void;
}

export default function SearchScreen({
  onClose,
  searchType = 'contractors',
  contractors = [],
  projects = [],
  onContractorPress,
  onProjectPress
}: SearchScreenProps) {
  const insets = useSafeAreaInsets();
  const [searchQuery, setSearchQuery] = useState('');
  const [filteredContractors, setFilteredContractors] = useState<Contractor[]>([]);
  const [filteredProjects, setFilteredProjects] = useState<Project[]>([]);
  const [recentSearches, setRecentSearches] = useState<string[]>([]);
  const [isSearching, setIsSearching] = useState(false);

  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  const isProjectSearch = searchType === 'projects';

  // Filter based on search query - only trigger on searchQuery change
  useEffect(() => {
    if (searchQuery.trim() === '') {
      setFilteredContractors([]);
      setFilteredProjects([]);
      setIsSearching(false);
      return;
    }

    setIsSearching(true);
    const query = searchQuery.toLowerCase().trim();

    // Simulate slight delay for smoother UX
    const timeoutId = setTimeout(() => {
      if (isProjectSearch) {
        // Filter projects
        const filtered = projects.filter(project => {
          const title = project.project_title?.toLowerCase() || '';
          const description = project.project_description?.toLowerCase() || '';
          const location = project.project_location?.toLowerCase() || '';
          const typeName = project.type_name?.toLowerCase() || '';
          const propertyType = project.property_type?.toLowerCase() || '';
          const ownerName = project.owner_name?.toLowerCase() || '';

          return (
            title.includes(query) ||
            description.includes(query) ||
            location.includes(query) ||
            typeName.includes(query) ||
            propertyType.includes(query) ||
            ownerName.includes(query)
          );
        });
        setFilteredProjects(filtered);
      } else {
        // Filter contractors
        const filtered = contractors.filter(contractor => {
          const companyName = contractor.company_name?.toLowerCase() || '';
          const typeName = contractor.type_name?.toLowerCase() || '';
          const services = contractor.services_offered?.toLowerCase() || '';
          const address = contractor.business_address?.toLowerCase() || '';

          return (
            companyName.includes(query) ||
            typeName.includes(query) ||
            services.includes(query) ||
            address.includes(query)
          );
        });
        setFilteredContractors(filtered);
      }
      setIsSearching(false);
    }, 150);

    return () => clearTimeout(timeoutId);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchQuery]);

  // Generate initials for avatar
  const getInitials = (name: string): string => {
    if (!name) return 'CO';
    return name
      .split(' ')
      .slice(0, 2)
      .map(word => word[0])
      .join('')
      .toUpperCase();
  };

  // Generate color based on ID
  const getColorForId = (id: number): string => {
    const colors = ['#1877f2', '#42b883', '#e74c3c', '#f39c12', '#9b59b6', '#1abc9c', '#e67e22', '#3498db'];
    return colors[id % 8];
  };

  // Handle search submit
  const handleSearchSubmit = () => {
    if (searchQuery.trim() && !recentSearches.includes(searchQuery.trim())) {
      setRecentSearches(prev => [searchQuery.trim(), ...prev.slice(0, 4)]);
    }
    Keyboard.dismiss();
  };

  // Handle recent search tap
  const handleRecentSearchTap = (search: string) => {
    setSearchQuery(search);
  };

  // Clear search
  const clearSearch = () => {
    setSearchQuery('');
    setFilteredContractors([]);
    setFilteredProjects([]);
  };

  // Render project item
  const renderProjectItem = ({ item }: { item: Project }) => {
    const profileImageUrl = item.owner_profile_pic
      ? `${api_config.base_url}/storage/${item.owner_profile_pic}`
      : null;
    const initials = getInitials(item.owner_name || 'Owner');
    const avatarColor = getColorForId(item.project_id);

    const formatBudget = (min?: number, max?: number): string => {
      if (!min && !max) return 'Budget TBD';
      const formatNum = (n: number) => {
        if (n >= 1000000) return `₱${(n / 1000000).toFixed(1)}M`;
        if (n >= 1000) return `₱${(n / 1000).toFixed(0)}K`;
        return `₱${n}`;
      };
      if (!min) return `Up to ${formatNum(max!)}`;
      if (!max) return `From ${formatNum(min)}`;
      return `${formatNum(min)} - ${formatNum(max)}`;
    };

    return (
      <TouchableOpacity
        style={styles.resultItem}
        onPress={() => onProjectPress?.(item)}
        activeOpacity={0.7}
      >
        {/* Owner Profile Picture / Avatar */}
        <View style={styles.avatarContainer}>
          {profileImageUrl ? (
            <ImageFallback
              uri={profileImageUrl}
              defaultImage={require('../../../assets/images/pictures/cp_default.jpg')}
              style={styles.profileImage}
              resizeMode="cover"
            />
          ) : (
            <View style={[styles.avatarPlaceholder, { backgroundColor: avatarColor }]}>
              <Text style={styles.avatarText}>{initials}</Text>
            </View>
          )}
        </View>

        {/* Project Info */}
        <View style={styles.resultInfo}>
          <Text style={styles.companyName} numberOfLines={1}>
            {item.project_title}
          </Text>
          <Text style={styles.contractorType} numberOfLines={1}>
            {item.type_name || 'Project'} • {formatBudget(item.budget_range_min, item.budget_range_max)}
          </Text>
          {item.project_location && (
            <View style={styles.locationRow}>
              <Ionicons name="location-outline" size={12} color="#666" />
              <Text style={styles.locationText} numberOfLines={1}>
                {item.project_location}
              </Text>
            </View>
          )}
        </View>

        {/* Arrow icon */}
        <MaterialIcons name="chevron-right" size={24} color="#CCCCCC" />
      </TouchableOpacity>
    );
  };

  // Render contractor item
  const renderContractorItem = ({ item }: { item: Contractor }) => {
    const profileImageUrl = item.profile_pic
      ? `${api_config.base_url}/storage/${item.profile_pic}`
      : null;
    const initials = getInitials(item.company_name);
    const avatarColor = getColorForId(item.user_id || item.contractor_id);

    return (
      <TouchableOpacity
        style={styles.resultItem}
        onPress={() => onContractorPress?.(item)}
        activeOpacity={0.7}
      >
        {/* Profile Picture / Avatar */}
        <View style={styles.avatarContainer}>
          {profileImageUrl ? (
            <Image
              source={{ uri: profileImageUrl }}
              style={styles.profileImage}
              defaultSource={require('../../../assets/images/pictures/cp_default.jpg')}
            />
          ) : (
            <View style={[styles.avatarPlaceholder, { backgroundColor: avatarColor }]}>
              <Text style={styles.avatarText}>{initials}</Text>
            </View>
          )}
        </View>

        {/* Contractor Info */}
        <View style={styles.resultInfo}>
          <Text style={styles.companyName} numberOfLines={1}>
            {item.company_name}
          </Text>
          <Text style={styles.contractorType} numberOfLines={1}>
            {item.type_name || 'Contractor'}
          </Text>
          {item.business_address && (
            <View style={styles.locationRow}>
              <Ionicons name="location-outline" size={12} color="#666" />
              <Text style={styles.locationText} numberOfLines={1}>
                {item.business_address}
              </Text>
            </View>
          )}
        </View>

        {/* Arrow icon */}
        <MaterialIcons name="chevron-right" size={24} color="#CCCCCC" />
      </TouchableOpacity>
    );
  };

  // Render recent search item
  const renderRecentSearchItem = (search: string, index: number) => (
    <TouchableOpacity
      key={index}
      style={styles.recentItem}
      onPress={() => handleRecentSearchTap(search)}
    >
      <MaterialIcons name="history" size={20} color="#666666" />
      <Text style={styles.recentText}>{search}</Text>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={[styles.container, { paddingTop: statusBarHeight }]}>
      <StatusBar hidden={true} />

      {/* Search Header */}
      <View style={styles.searchHeader}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color="#333333" />
        </TouchableOpacity>

        <View style={styles.searchInputContainer}>
          <MaterialIcons name="search" size={20} color="#999999" />
          <TextInput
            style={styles.searchInput}
            placeholder={isProjectSearch ? "Search projects..." : "Search contractors..."}
            placeholderTextColor="#999999"
            value={searchQuery}
            onChangeText={setSearchQuery}
            onSubmitEditing={handleSearchSubmit}
            autoFocus
            returnKeyType="search"
          />
          {searchQuery.length > 0 && (
            <TouchableOpacity onPress={clearSearch} style={styles.clearButton}>
              <Ionicons name="close-circle" size={20} color="#999999" />
            </TouchableOpacity>
          )}
        </View>
      </View>

      {/* Content */}
      <View style={styles.content}>
        {/* Loading indicator */}
        {isSearching && (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="small" color="#EC7E00" />
          </View>
        )}

        {/* Search Results */}
        {searchQuery.trim() !== '' && !isSearching && (
          <>
            {isProjectSearch ? (
              // Project search results
              filteredProjects.length > 0 ? (
                <FlatList
                  data={filteredProjects}
                  keyExtractor={(item) => `project-${item.project_id}`}
                  renderItem={renderProjectItem}
                  showsVerticalScrollIndicator={false}
                  keyboardShouldPersistTaps="handled"
                  ListHeaderComponent={
                    <Text style={styles.resultsHeader}>
                      {filteredProjects.length} project{filteredProjects.length !== 1 ? 's' : ''} found
                    </Text>
                  }
                />
              ) : (
                <View style={styles.noResultsContainer}>
                  <MaterialIcons name="search-off" size={64} color="#CCCCCC" />
                  <Text style={styles.noResultsTitle}>No projects found</Text>
                  <Text style={styles.noResultsText}>
                    Try searching with different keywords
                  </Text>
                </View>
              )
            ) : (
              // Contractor search results
              filteredContractors.length > 0 ? (
                <FlatList
                  data={filteredContractors}
                  keyExtractor={(item) => `contractor-${item.contractor_id}`}
                  renderItem={renderContractorItem}
                  showsVerticalScrollIndicator={false}
                  keyboardShouldPersistTaps="handled"
                  ListHeaderComponent={
                    <Text style={styles.resultsHeader}>
                      {filteredContractors.length} result{filteredContractors.length !== 1 ? 's' : ''} found
                    </Text>
                  }
                />
              ) : (
                <View style={styles.noResultsContainer}>
                  <MaterialIcons name="search-off" size={64} color="#CCCCCC" />
                  <Text style={styles.noResultsTitle}>No contractors found</Text>
                  <Text style={styles.noResultsText}>
                    Try searching with different keywords
                  </Text>
                </View>
              )
            )}
          </>
        )}

        {/* Recent Searches (when search is empty) */}
        {searchQuery.trim() === '' && recentSearches.length > 0 && (
          <View style={styles.recentSection}>
            <View style={styles.recentHeader}>
              <Text style={styles.recentTitle}>Recent Searches</Text>
              <TouchableOpacity onPress={() => setRecentSearches([])}>
                <Text style={styles.clearAllText}>Clear All</Text>
              </TouchableOpacity>
            </View>
            {recentSearches.map((search, index) => renderRecentSearchItem(search, index))}
          </View>
        )}

        {/* Suggestions (when search is empty and no recent searches) */}
        {searchQuery.trim() === '' && recentSearches.length === 0 && (
          <View style={styles.suggestionsContainer}>
            <MaterialIcons name="search" size={64} color="#EEEEEE" />
            <Text style={styles.suggestionsTitle}>
              {isProjectSearch ? 'Search Projects' : 'Search Contractors'}
            </Text>
            <Text style={styles.suggestionsText}>
              {isProjectSearch
                ? 'Find projects by title, description, location, or type'
                : 'Find contractors by company name, type, services, or location'
              }
            </Text>
          </View>
        )}
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  searchHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#EEEEEE',
    backgroundColor: '#FFFFFF',
  },
  backButton: {
    padding: 8,
    marginRight: 8,
  },
  searchInputContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F5F5F5',
    borderRadius: 20,
    paddingHorizontal: 12,
    height: 40,
  },
  searchInput: {
    flex: 1,
    fontSize: 16,
    color: '#333333',
    marginLeft: 8,
    paddingVertical: 0,
  },
  clearButton: {
    padding: 4,
  },
  content: {
    flex: 1,
  },
  loadingContainer: {
    padding: 20,
    alignItems: 'center',
  },
  resultsHeader: {
    fontSize: 13,
    color: '#666666',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#F9F9F9',
  },
  resultItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  avatarContainer: {
    marginRight: 12,
  },
  profileImage: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#F0F0F0',
  },
  avatarPlaceholder: {
    width: 50,
    height: 50,
    borderRadius: 25,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: 18,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  resultInfo: {
    flex: 1,
  },
  companyName: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1A1A1A',
    marginBottom: 2,
  },
  contractorType: {
    fontSize: 14,
    color: '#666666',
    marginBottom: 2,
  },
  locationRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  locationText: {
    fontSize: 12,
    color: '#999999',
    marginLeft: 4,
    flex: 1,
  },
  noResultsContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 40,
  },
  noResultsTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333333',
    marginTop: 16,
  },
  noResultsText: {
    fontSize: 14,
    color: '#999999',
    marginTop: 8,
    textAlign: 'center',
  },
  recentSection: {
    paddingTop: 8,
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
    fontWeight: '600',
    color: '#333333',
  },
  clearAllText: {
    fontSize: 14,
    color: '#EC7E00',
  },
  recentItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  recentText: {
    fontSize: 15,
    color: '#333333',
    marginLeft: 12,
  },
  suggestionsContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 40,
  },
  suggestionsTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#333333',
    marginTop: 16,
  },
  suggestionsText: {
    fontSize: 14,
    color: '#999999',
    marginTop: 8,
    textAlign: 'center',
    lineHeight: 20,
  },
});
