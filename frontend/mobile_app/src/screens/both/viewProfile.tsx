// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  Image,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  StatusBar,
  Dimensions,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { MaterialIcons, Ionicons, FontAwesome5, Feather } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import * as ImagePicker from 'expo-image-picker';
import { api_config } from '../../config/api';
import { projects_service } from '../../services/projects_service';

const { width } = Dimensions.get('window');

export default function ViewProfileScreen({ onBack, userData, userToken }) {
  const insets = useSafeAreaInsets();
  const [activeTab, setActiveTab] = useState('Posts');
  const [isUploading, setIsUploading] = useState(false);
  const [projects, setProjects] = useState<any[]>([]);
  const [projectsLoading, setProjectsLoading] = useState(false);
  const [projectsError, setProjectsError] = useState<string | null>(null);

  const [profilePic, setProfilePic] = useState(userData?.profile_pic);
  const [coverPhoto, setCoverPhoto] = useState(userData?.cover_photo);

  const getStorageUrl = (filePath?: string) => {
    if (!filePath) return undefined;
    if (filePath.startsWith('http')) return filePath;
    return `${api_config.base_url}/storage/${filePath}`;
  };

  useEffect(() => {
    // fetch projects when Posts or Projects tab is active
    if (activeTab === 'Posts' || activeTab === 'Projects') {
      fetchProjects();
    }
  }, [activeTab, userData?.user_id]);

  const fetchProjects = async () => {
    if (!userData?.user_id) return;
    try {
      setProjectsLoading(true);
      setProjectsError(null);
      const response = await projects_service.get_owner_projects(userData.user_id);
      if (response.success) {
        const projectsData = response.data?.data || response.data || [];
        setProjects(Array.isArray(projectsData) ? projectsData : []);
      } else {
        setProjectsError(response.message || 'Failed to load projects');
      }
    } catch (err) {
      setProjectsError('Failed to load projects');
    } finally {
      setProjectsLoading(false);
    }
  };

  const pickImage = async (type: 'profile' | 'cover') => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission Denied', 'Allow access to gallery to change photos.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'], 
      allowsEditing: true,
      aspect: type === 'profile' ? [1, 1] : [16, 9],
      quality: 0.7,
    });

    if (!result.canceled) {
      uploadImage(result.assets[0].uri, type);
    }
  };

  const uploadImage = async (uri: string, type: 'profile' | 'cover') => {
    setIsUploading(true);
    const formData = new FormData();
    const filename = uri.split('/').pop();
    const match = /\.(\w+)$/.exec(filename);
    const fileType = match ? `image/${match[1]}` : `image`;

    formData.append('image', { uri, name: filename, type: fileType } as any);
    formData.append('type', type);

    try {
      const response = await fetch(`${api_config.base_url}/api/user/update-profile`, {
        method: 'POST',
        body: formData,
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${userToken}`, 
        },
      });

      const data = await response.json();
      if (data.success) {
        if (type === 'profile') setProfilePic(data.path);
        else setCoverPhoto(data.path);
        Alert.alert("Success", "Profile updated successfully");
      }
    } catch (error) {
      Alert.alert("Network Error", "Could not connect to server.");
    } finally {
      setIsUploading(false);
    }
  };

  const formatBudget = (min: number, max: number) => {
    const formatNum = (n: number) => {
      if (n >= 1000000) return `₱${(n / 1000000).toFixed(1)}M`;
      if (n >= 1000) return `₱${(n / 1000).toFixed(0)}K`;
      return `₱${n}`;
    };
    return `${formatNum(min)} - ${formatNum(max)}`;
  };

  const renderProjectCard = (project: any) => {
    const now = new Date();
    const deadlineDate = project.bidding_deadline ? new Date(project.bidding_deadline) : null;
    const isUrgent = deadlineDate && (deadlineDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24) <= 3;

    return (
      <TouchableOpacity key={project.project_id} style={styles.projectCard}>
        <View style={styles.cardHeader}>
          <Text style={styles.projectTitle}>{project.project_title}</Text>
          <View style={[styles.statusBadge, { backgroundColor: '#EBF5FF' }]}>
            <Text style={[styles.statusText, { color: '#1877F2' }]}>Open</Text>
          </View>
        </View>

        <Text numberOfLines={2} style={styles.projectDesc}>{project.project_description}</Text>
        
        <View style={styles.cardFooter}>
          <View style={styles.locationBox}>
            <Ionicons name="location-outline" size={14} color="#666" />
            <Text style={styles.footerText}>{project.project_location}</Text>
          </View>
          <Text style={styles.budgetText}>{formatBudget(project.budget_range_min, project.budget_range_max)}</Text>
        </View>

        {deadlineDate && (
           <View style={styles.deadlineRow}>
              <Feather name="clock" size={12} color={isUrgent ? "#EF4444" : "#666"} />
              <Text style={[styles.deadlineText, isUrgent && {color: '#EF4444'}]}>
                Bidding ends: {deadlineDate.toLocaleDateString()}
              </Text>
           </View>
        )}
      </TouchableOpacity>
    );
  };

  return (
    <View style={styles.container}>
      <StatusBar barStyle="dark-content" />
      
      {isUploading && (
        <View style={styles.loadingOverlay}>
          <ActivityIndicator size="large" color="#EC7E00" />
          <Text style={styles.loadingText}>Saving to database...</Text>
        </View>
      )}

      {/* Modern Header */}
      <View style={[styles.navbar, { paddingTop: insets.top }]}>
        <TouchableOpacity onPress={onBack} style={styles.navBackBtn}>
            <Ionicons name="chevron-back" size={24} color="#333" />
        </TouchableOpacity>
        <Text style={styles.navTitle}>Profile</Text>
        <TouchableOpacity style={styles.navMenuBtn}>
            <Feather name="more-horizontal" size={24} color="#333" />
        </TouchableOpacity>
      </View>

      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: 40 }}>
        {/* Hero Section */}
        <View style={styles.heroSection}>
          <View style={styles.coverWrapper}>
            <Image 
                source={{ uri: getStorageUrl(coverPhoto) || 'https://via.placeholder.com/800' }} 
                style={styles.coverImg} 
            />
              <TouchableOpacity style={styles.editCoverBtn} onPress={() => pickImage('cover')}>
                <Feather name="camera" size={18} color="#FFF" />
              </TouchableOpacity>
          </View>

          <View style={styles.profileInfoContainer}>
            <View style={styles.avatarWrapper}>
                <Image 
                    source={{ uri: getStorageUrl(profilePic) || 'https://via.placeholder.com/150' }} 
                    style={styles.avatarImg} 
                />
                <TouchableOpacity style={styles.editAvatarBtn} onPress={() => pickImage('profile')}>
                  <Feather name="edit-2" size={14} color="#FFF" />
                </TouchableOpacity>
            </View>

            <Text style={styles.profileName}>{userData?.username || 'Member'}</Text>
            
            <View style={styles.statRow}>
                <View style={styles.statItem}>
                    <Text style={styles.statVal}>5.0</Text>
                    <Text style={styles.statLab}>Rating</Text>
                </View>
                <View style={[styles.statItem, styles.statDivider]}>
                    <Text style={styles.statVal}>{projects.length}</Text>
                    <Text style={styles.statLab}>Posts</Text>
                </View>
                <View style={styles.statItem}>
                    <Text style={styles.statVal}>0</Text>
                    <Text style={styles.statLab}>Reviews</Text>
                </View>
            </View>
          </View>
        </View>

        {/* Custom Tabs */}
        <View style={styles.tabBar}>
          {['Posts', 'Projects', 'About', 'Reviews'].map((tab) => (
            <TouchableOpacity 
              key={tab} 
              onPress={() => setActiveTab(tab)} 
              style={[styles.tabBtn, activeTab === tab && styles.tabBtnActive]}
            >
              <Text style={[styles.tabBtnText, activeTab === tab && styles.tabBtnTextActive]}>{tab}</Text>
            </TouchableOpacity>
          ))}
        </View>

        <View style={styles.contentPadding}>
          {activeTab === 'Posts' && (
            <View>
              {projectsLoading ? (
                <ActivityIndicator color="#EC7E00" style={{ marginTop: 20 }} />
              ) : projectsError ? (
                <Text style={styles.errorText}>{projectsError}</Text>
              ) : projects.length === 0 ? (
                <View style={styles.emptyContainer}>
                    <Feather name="folder" size={40} color="#CCC" />
                    <Text style={styles.emptyText}>No active project posts.</Text>
                </View>
              ) : (
                projects.map(p => renderProjectCard(p))
              )}
            </View>
          )}

          {activeTab === 'Projects' && (
            <View>
              {projectsLoading ? (
                <ActivityIndicator color="#EC7E00" style={{ marginTop: 20 }} />
              ) : projectsError ? (
                <Text style={styles.errorText}>{projectsError}</Text>
              ) : (
                (() => {
                  const activeProjects = projects.filter((p: any) => {
                    const s = (p.project_status || '').toLowerCase();
                    const post = (p.project_post_status || '').toLowerCase();
                    return s === 'in_progress' || s === 'open' || s === 'bidding_closed' || post === 'approved';
                  });

                  if (activeProjects.length === 0) {
                    return (
                      <View style={styles.emptyContainer}>
                        <Feather name="briefcase" size={40} color="#CCC" />
                        <Text style={styles.emptyText}>No active/ongoing projects.</Text>
                      </View>
                    );
                  }

                  return activeProjects.map((p: any) => renderProjectCard(p));
                })()
              )}
            </View>
          )}

          {activeTab === 'About' && (
            <View style={styles.aboutCard}>
                <Text style={styles.aboutTitle}>Biography</Text>
                <Text style={styles.aboutText}>{userData?.bio || 'This user hasn’t added a bio yet.'}</Text>
            </View>
          )}
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F8F9FA' },
  loadingOverlay: { ...StyleSheet.absoluteFillObject, backgroundColor: 'rgba(0,0,0,0.35)', zIndex: 1000, justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 10, color: '#FFF', fontWeight: '700' },

  navbar: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 15, paddingBottom: 10, backgroundColor: '#FFF' },
  navTitle: { fontSize: 17, fontWeight: '700', color: '#333' },
  navBackBtn: { padding: 5 },
  navMenuBtn: { padding: 5 },

  heroSection: { backgroundColor: '#FFF', borderBottomLeftRadius: 30, borderBottomRightRadius: 30, elevation: 2, paddingBottom: 26 },
  coverWrapper: { height: 180, width: '100%', overflow: 'hidden' },
  coverImg: { width: '100%', height: '100%', resizeMode: 'cover' },
  editCoverBtn: { position: 'absolute', bottom: 12, right: 18, backgroundColor: 'rgba(0,0,0,0.45)', padding: 8, borderRadius: 22 },
  
  profileInfoContainer: { alignItems: 'center', marginTop: -60 },
  avatarWrapper: { width: 110, height: 110, borderRadius: 55, padding: 6, backgroundColor: '#FFF', justifyContent: 'center', alignItems: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.12, shadowRadius: 12, elevation: 6 },
  avatarImg: { width: 98, height: 98, borderRadius: 49 },
  editAvatarBtn: { position: 'absolute', bottom: 6, right: 6, backgroundColor: '#EC7E00', padding: 6, borderRadius: 16, borderWidth: 2, borderColor: '#FFF' },
  
  profileName: { fontSize: 22, fontWeight: '900', color: '#0F172A', marginTop: 12 },
  statRow: { flexDirection: 'row', marginTop: 20, width: '100%', justifyContent: 'center' },
  statItem: { alignItems: 'center', paddingHorizontal: 25 },
  statDivider: { borderLeftWidth: 1, borderRightWidth: 1, borderColor: '#F1F5F9' },
  statVal: { fontSize: 18, fontWeight: '800', color: '#0F172A' },
  statLab: { fontSize: 12, color: '#64748B', marginTop: 2 },

  tabBar: { flexDirection: 'row', paddingHorizontal: 20, marginTop: 18, gap: 10 },
  tabBtn: { paddingVertical: 8, paddingHorizontal: 16, borderRadius: 20, backgroundColor: '#F1F5F9' },
  tabBtnActive: { backgroundColor: '#EC7E00' },
  tabBtnText: { color: '#475569', fontWeight: '700', fontSize: 14 },
  tabBtnTextActive: { color: '#FFF' },

  contentPadding: { padding: 20 },
  projectCard: { backgroundColor: '#FFF', borderRadius: 16, padding: 18, marginBottom: 16, elevation: 3, shadowColor: '#000', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.08, shadowRadius: 10 },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  projectTitle: { fontSize: 16, fontWeight: '800', color: '#0F172A', flex: 1, marginRight: 10 },
  statusBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
  statusText: { fontSize: 11, fontWeight: '700' },
  projectDesc: { color: '#64748B', fontSize: 13, marginTop: 8, lineHeight: 20 },
  cardFooter: { flexDirection: 'row', marginTop: 15, alignItems: 'center', borderTopWidth: 1, borderTopColor: '#F8FAFC', paddingTop: 12 },
  locationBox: { flexDirection: 'row', alignItems: 'center', flex: 1 },
  footerText: { fontSize: 12, color: '#64748B', marginLeft: 6, flexShrink: 1 },
  budgetText: { fontSize: 14, fontWeight: '800', color: '#EC7E00', marginLeft: 12, flexShrink: 0 },
  deadlineRow: { flexDirection: 'row', alignItems: 'center', marginTop: 10 },
  deadlineText: { fontSize: 11, color: '#999', marginLeft: 5 },

  aboutCard: { backgroundColor: '#FFF', borderRadius: 16, padding: 20, elevation: 1 },
  aboutTitle: { fontSize: 16, fontWeight: '700', marginBottom: 10 },
  aboutText: { fontSize: 14, color: '#555', lineHeight: 22 },
  emptyContainer: { alignItems: 'center', marginTop: 40 },
  emptyText: { color: '#999', marginTop: 10, fontSize: 15 },
  errorText: { color: '#EF4444', textAlign: 'center', marginTop: 20 }
});