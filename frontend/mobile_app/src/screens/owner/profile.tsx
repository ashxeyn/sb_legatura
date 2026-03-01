// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Image,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { View as SafeAreaView, StatusBar, Platform, AppState } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { role_service } from '../../services/role_service';
import { api_config, api_request } from '../../config/api';
import { storage_service } from '../../utils/storage';

interface ProfileScreenProps {
  onLogout: () => void;
  onEditProfile?: () => void;
  onViewProfile?: () => void;
  onOpenHelp?: () => void;
  onOpenSwitchRole?: () => void; // ✅ Navigate to switch role screen
  onOpenBoosts?: () => void; // Navigate to Boosts screen
  onOpenChangeOtp?: () => void;
  userData?: {
    username?: string;
    email?: string;
    profile_pic?: string;
    cover_photo?: string;
    user_type?: string;
    onViewProfile?: () => void;
    onEditProfile?: () => void;
  };
}

interface MenuItem {
  id: string;
  icon: string;
  label: string;
  subtitle?: string;
  onPress?: () => void;
  showArrow?: boolean;
  danger?: boolean;
}

export default function ProfileScreen({ onLogout, onViewProfile, onEditProfile, onOpenHelp, onOpenSwitchRole, onOpenBoosts, userData }: ProfileScreenProps) {
  const insets = useSafeAreaInsets();
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [roleLabel, setRoleLabel] = useState<string>('Property Owner');

  // Get status bar height (top inset)
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  // Get initials from username for avatar fallback
  const getInitials = (name: string) => {
    return name ? name.substring(0, 2).toUpperCase() : 'PO';
  };

  // Resolve storage paths returned from the backend (e.g. "profiles/..." )
  const getStorageUrl = (path: string | null | undefined) => {
    if (!path) return null;
    try {
      const raw = String(path).trim();
      // If an absolute URL is provided, attempt to extract the storage-relative path
      if (raw.startsWith('http')) {
        try {
          const parsed = new URL(raw);
          const storageIndex = parsed.pathname.indexOf('/storage/');
          if (storageIndex !== -1) {
            let p = parsed.pathname.substring(storageIndex + '/storage/'.length);
            // remove leading slashes
            p = p.replace(/^\/+/, '');
            // normalize nested profiles/cover_photos -> cover_photos
            if (p.startsWith('profiles/cover_photos/')) p = p.replace(/^profiles\/cover_photos\//, 'cover_photos/');
            if (p.startsWith('storage/')) p = p.replace(/^storage\//, '');
            const base = (api_config && api_config.base_url) || (global && (global.api_base || global.api_base_url)) || 'http://localhost:8000';
            const url = `${String(base).replace(/\/+$/,'')}/storage/${p}`;
            try { console.log('[getStorageUrl] normalized absolute url ->', url); } catch (e) {}
            return url;
          }
          // If it doesn't include /storage/, return the absolute URL as-is
          return raw;
        } catch (e) {
          // parsing failed, fallthrough to treat as relative path
        }
      }
    } catch (e) {
      return null;
    }
    // Normalize path to avoid double '/storage/storage/...' or leading slashes
    let p = raw.replace(/^\/+/, '');
    if (p.startsWith('storage/')) p = p.replace(/^storage\//, '');
    // Some DB values include nested 'profiles/cover_photos' which maps to 'cover_photos' on disk
    p = p.replace(/^profiles\/cover_photos\//, 'cover_photos/');
    const base = (api_config && api_config.base_url) || (global && (global.api_base || global.api_base_url)) || 'http://localhost:8000';
    const url = `${String(base).replace(/\/+$/,'')}/storage/${p}`;
    try { console.log('[getStorageUrl] resolved', url); } catch (e) {}
    return url;
  };

  const [ownerCoverUrl, setOwnerCoverUrl] = useState<string | null>(null);
  const [ownerProfileUrl, setOwnerProfileUrl] = useState<string | null>(null);

  useEffect(() => {
    let mounted = true;
    const resolveOwnerImages = async () => {
      try {
        // If we already have a cover/profile from props, prefer that
        if (ownerCoverPhotoPath) {
          setOwnerCoverUrl(getStorageUrl(ownerCoverPhotoPath));
        }
        if (ownerProfilePicPath) {
          setOwnerProfileUrl(getStorageUrl(ownerProfilePicPath));
        }

        // If cover missing, try fetching latest profile from API using stored user id
        if (!ownerCoverPhotoPath) {
          const stored = await storage_service.get_user_data();
          const userId = stored?.user_id || stored?.id || null;
          if (userId) {
            const resp = await api_request(`/api/profile/fetch?user_id=${encodeURIComponent(userId)}`);
            if (resp?.success && resp.data) {
              const payload = resp.data.data || resp.data;
              const u = payload.user || payload;
              const cover = u?.cover_photo || null;
              if (cover && mounted) setOwnerCoverUrl(getStorageUrl(cover));
              const pic = u?.profile_pic || null;
              if (pic && mounted) setOwnerProfileUrl(getStorageUrl(pic));
            }
          }
        }
      } catch (e) {
        console.warn('Could not resolve owner images:', e);
      }
    };
    resolveOwnerImages();
    return () => { mounted = false; };
  }, [ownerCoverPhotoPath, ownerProfilePicPath]);

  // Debug: log resolved URLs and source paths when they change
  useEffect(() => {
    try {
      console.log('[owner/profile] ownerCoverUrl:', ownerCoverUrl, 'ownerProfileUrl:', ownerProfileUrl);
      console.log('[owner/profile] ownerCoverPhotoPath:', ownerCoverPhotoPath, 'ownerProfilePicPath:', ownerProfilePicPath);
    } catch (e) {
      // no-op
    }
  }, [ownerCoverUrl, ownerProfileUrl, ownerCoverPhotoPath, ownerProfilePicPath]);

  // Prefer images from the users table for owner screen.
  // Use the `users` fields directly so owner cover/profile show correctly.
  const ownerProfilePicPath = userData?.profile_pic || null;
  const ownerCoverPhotoPath = userData?.cover_photo || null;

  let navigation: any = null;
  try {
    navigation = useNavigation();
  } catch (e) {
    navigation = null;
  }

  // Handle logout with confirmation
  const handleLogout = () => {
    Alert.alert(
      'Logout',
      'Are you sure you want to logout?',
      [
        {
          text: 'Cancel',
          style: 'cancel',
        },
        {
          text: 'Logout',
          style: 'destructive',
          onPress: async () => {
            setIsLoggingOut(true);
            // Small delay for UX
            setTimeout(() => {
              setIsLoggingOut(false);
              onLogout();
            }, 500);
          },
        },
      ]
    );
  };

  // Refresh current role on mount and focus to update badge
  useEffect(() => {
    let isMounted = true;
    const fetchRole = async () => {
      try {
        const res = await role_service.get_current_role();
        if (res?.success) {
          const roleVal = (res as any).current_role || (res as any).data?.current_role || (res as any).user_type;
          const v = String(roleVal || '').toLowerCase();
          const label = v.includes('contractor') ? 'Contractor' : v.includes('owner') ? 'Property Owner' : 'Property Owner';
          if (isMounted) setRoleLabel(label);
        }
      } catch {}
    };
    fetchRole();
    const sub = AppState.addEventListener('change', (state) => {
      if (state === 'active') fetchRole();
    });
    return () => {
      isMounted = false;
      sub.remove();
    };
  }, []);

  // Menu items configuration
  const menuSections: { title: string; items: MenuItem[] }[] = [
    {
      title: 'Account',
      items: [
        {
          id: 'view_profile',
          icon: 'eye-outline',
          label: 'View Profile',
          subtitle: 'See your public profile view',
          showArrow: true,
          onPress: onViewProfile
        },
        {
          id: 'edit_profile',
          icon: 'person-outline',
          label: 'Edit Profile',
          subtitle: 'Update your personal information',
          showArrow: true,
          onPress: onEditProfile,

        },
        {
          id: 'switch_role',
          icon: 'swap-horizontal-outline',
          label: 'Switch Role',
          subtitle: 'Manage your role settings',
          showArrow: true,
          onPress: onOpenSwitchRole || (() => Alert.alert('Coming Soon', 'This feature is under development.')),
        },
      ],
    },
    {
      title: 'Preferences',
      items: [
        {
          id: 'notifications',
          icon: 'notifications-outline',
          label: 'Notifications',
          subtitle: 'Manage notification preferences',
          showArrow: true,
          onPress: () => Alert.alert('Coming Soon', 'This feature is under development.'),
        },
        {
          id: 'privacy',
          icon: 'shield-outline',
          label: 'Privacy & Security',
          subtitle: 'Manage your privacy settings',
          showArrow: true,
          onPress: () => {
            if (typeof onOpenChangeOtp === 'function') { onOpenChangeOtp(); return; }
            try { if (navigation && typeof navigation.navigate === 'function') { navigation.navigate('ChangeOtp'); return; } } catch (e) {}
            try { // @ts-ignore
              if (typeof global.set_app_state === 'function') { // @ts-ignore
                global.set_app_state('change_otp'); return; }
            } catch (e) {}
            Alert.alert('Privacy & Security', 'Open change OTP screen.');
          },
        },
      ],
    },
    {
      title: 'Promotions',
      items: [
        {
          id: 'boosts',
          icon: 'rocket-outline',
          label: 'Boosts',
          subtitle: 'Promote your project to the top',
          showArrow: true,
          onPress: () => {
            if (typeof onOpenBoosts === 'function') {
              onOpenBoosts();
            } else {
              Alert.alert('Boosts', 'Open Boosts screen (not implemented in parent)');
            }
          },
        },
      ],
    },
    {
      title: 'Support',
      items: [
        {
          id: 'help',
          icon: 'help-circle-outline',
          label: 'Help Center',
          subtitle: 'Get help and support',
          showArrow: true,
          onPress: () => {
            if (typeof onOpenHelp === 'function') {
              onOpenHelp();
            } else {
              Alert.alert('Coming Soon', 'This feature is under development.');
            }
          },
        },
        {
          id: 'about',
          icon: 'information-circle-outline',
          label: 'About Legatura',
          subtitle: 'Version 1.0.0',
          showArrow: true,
          onPress: () => Alert.alert('About', 'Legatura v1.0.0\n\nConnecting Property Owners with Contractors'),
        },
      ],
    },

  ];

  const renderMenuItem = (item: MenuItem) => (
    <TouchableOpacity
      key={item.id}
      style={[styles.menuItem, item.danger && styles.menuItemDanger]}
      onPress={item.onPress}
      activeOpacity={0.7}
    >
      <View style={[styles.menuIconContainer, item.danger && styles.menuIconDanger]}>
        <Ionicons
          name={item.icon as any}
          size={22}
          color={item.danger ? '#E74C3C' : '#EC7E00'}
        />
      </View>
      <View style={styles.menuTextContainer}>
        <Text style={[styles.menuLabel, item.danger && styles.menuLabelDanger]}>
          {item.label}
        </Text>
        {item.subtitle && (
          <Text style={styles.menuSubtitle}>{item.subtitle}</Text>
        )}
      </View>
      {item.showArrow && (
        <MaterialIcons name="chevron-right" size={24} color="#CCCCCC" />
      )}
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={[styles.container, { paddingTop: statusBarHeight }]}>
      <StatusBar hidden={true} />
      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {/* Header */}
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Settings</Text>
        </View>

        {/* Profile Card */}
        <View style={styles.profileCard}>
          {/* Cover Photo */}
          <View style={styles.coverPhotoContainer}>
            {getStorageUrl(ownerCoverPhotoPath) ? (
              <Image
                source={{ uri: getStorageUrl(ownerCoverPhotoPath) as string }}
                style={styles.coverPhoto}
                resizeMode="cover"
              />
            ) : (
              <View style={styles.coverPhotoPlaceholder}>
                <MaterialIcons name="photo-camera" size={24} color="#FFFFFF" />
              </View>
            )}
          </View>

          {/* Profile Info */}
          <View style={styles.profileInfoContainer}>
            <View style={styles.avatarContainer}>
              {getStorageUrl(ownerProfilePicPath) ? (
                <Image
                  source={{ uri: getStorageUrl(ownerProfilePicPath) as string }}
                  style={styles.avatar}
                  resizeMode="cover"
                />
              ) : (
                <View style={styles.avatarPlaceholder}>
                  <Text style={styles.avatarText}>
                    {getInitials(userData?.username || 'User')}
                  </Text>
                </View>
              )}
              <TouchableOpacity style={styles.editAvatarButton}>
                <MaterialIcons name="camera-alt" size={16} color="#FFFFFF" />
              </TouchableOpacity>
            </View>

            <Text style={styles.userName}>{userData?.username || 'Property Owner'}</Text>
            <Text style={styles.userEmail}>{userData?.email || 'user@example.com'}</Text>

            <View style={styles.userTypeBadge}>
              <MaterialIcons name={roleLabel === 'Contractor' ? 'business' : 'home'} size={14} color="#EC7E00" />
              <Text style={styles.userTypeText}>{roleLabel}</Text>
            </View>
          </View>
        </View>

        {/* Menu Sections */}
        {menuSections.map((section) => (
          <View key={section.title} style={styles.menuSection}>
            <Text style={styles.sectionTitle}>{section.title}</Text>
            <View style={styles.menuCard}>
              {section.items.map((item, index) => (
                <View key={item.id}>
                  {renderMenuItem(item)}
                  {index < section.items.length - 1 && <View style={styles.menuDivider} />}
                </View>
              ))}
            </View>
          </View>
        ))}

        {/* Logout Button */}
        <View style={styles.logoutSection}>
          <TouchableOpacity
            style={styles.logoutButton}
            onPress={handleLogout}
            disabled={isLoggingOut}
            activeOpacity={0.8}
          >
            {isLoggingOut ? (
              <ActivityIndicator color="#FFFFFF" size="small" />
            ) : (
              <>
                <Ionicons name="log-out-outline" size={22} color="#FFFFFF" />
                <Text style={styles.logoutButtonText}>Logout</Text>
              </>
            )}
          </TouchableOpacity>
        </View>

        {/* Footer */}
        <View style={styles.footer}>
          <Text style={styles.footerText}>Legatura © 2025</Text>
          <Text style={styles.footerSubtext}>All rights reserved</Text>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 100,
  },
  header: {
    paddingHorizontal: 20,
    paddingVertical: 16,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E5E5',
  },
  headerTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333333',
  },
  profileCard: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginTop: 16,
    borderRadius: 16,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
  },
  coverPhotoContainer: {
    height: 100,
    backgroundColor: '#EC7E00',
  },
  coverPhoto: {
    width: '100%',
    height: '100%',
  },
  coverPhotoPlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#EC7E00',
  },
  profileInfoContainer: {
    alignItems: 'center',
    paddingBottom: 20,
    marginTop: -50,
  },
  avatarContainer: {
    position: 'relative',
  },
  avatar: {
    width: 100,
    height: 100,
    borderRadius: 50,
    borderWidth: 4,
    borderColor: '#FFFFFF',
  },
  avatarPlaceholder: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: '#EC7E00',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 4,
    borderColor: '#FFFFFF',
  },
  avatarText: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  editAvatarButton: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    backgroundColor: '#333333',
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: '#FFFFFF',
  },
  userName: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333333',
    marginTop: 12,
  },
  userEmail: {
    fontSize: 14,
    color: '#666666',
    marginTop: 4,
  },
  userTypeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF5EB',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
    marginTop: 12,
    gap: 6,
  },
  userTypeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#EC7E00',
  },
  menuSection: {
    marginTop: 24,
    paddingHorizontal: 16,
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#999999',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 8,
    marginLeft: 4,
  },
  menuCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingHorizontal: 16,
  },
  menuItemDanger: {
    backgroundColor: '#FFF5F5',
  },
  menuIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#FFF5EB',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 14,
  },
  menuIconDanger: {
    backgroundColor: '#FFE5E5',
  },
  menuTextContainer: {
    flex: 1,
  },
  menuLabel: {
    fontSize: 16,
    fontWeight: '500',
    color: '#333333',
  },
  menuLabelDanger: {
    color: '#E74C3C',
  },
  menuSubtitle: {
    fontSize: 13,
    color: '#999999',
    marginTop: 2,
  },
  menuDivider: {
    height: 1,
    backgroundColor: '#F0F0F0',
    marginLeft: 70,
  },
  logoutSection: {
    marginTop: 32,
    paddingHorizontal: 16,
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#E74C3C',
    paddingVertical: 16,
    borderRadius: 12,
    gap: 10,
    shadowColor: '#E74C3C',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 6,
  },
  logoutButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  footer: {
    alignItems: 'center',
    marginTop: 32,
    paddingBottom: 20,
  },
  footerText: {
    fontSize: 14,
    color: '#999999',
  },
  footerSubtext: {
    fontSize: 12,
    color: '#CCCCCC',
    marginTop: 4,
  },
});

