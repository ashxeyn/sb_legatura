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
import ImageFallback from '../../components/ImageFallbackFixed';
import { contractors_service } from '../../services/contractors_service';
import { api_config } from '../../config/api';
import { role_service } from '../../services/role_service';

// Default images
const defaultCoverPhoto = require('../../../assets/images/pictures/cp_default.jpg');
const defaultContractorAvatar = require('../../../assets/images/pictures/contractor_default.png');

interface ContractorProfileScreenProps {
  onViewProfile?: () => void;
  onLogout: () => void;
  onOpenHelp?: () => void;
  onOpenSwitchRole?: () => void;
  onOpenSubscription?: () => void;
  onOpenChangeOtp?: () => void;
  onEditProfile?: () => void;
  userData?: {
    username?: string;
    email?: string;
    profile_pic?: string;
    cover_photo?: string;
    user_type?: string;
    company_name?: string;
    contractor_type?: string;
    years_of_experience?: number;
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

export default function ContractorProfileScreen({ onLogout, onViewProfile, onOpenHelp, onOpenSwitchRole, onOpenSubscription, onEditProfile, userData }: ContractorProfileScreenProps) {
  const insets = useSafeAreaInsets();
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [roleLabel, setRoleLabel] = useState<string>('Contractor');
  const [companyName, setCompanyName] = useState<string | undefined>(userData?.company_name);
  const [companyLogo, setCompanyLogo] = useState<string | undefined>(userData?.profile_pic);
  const [companyBanner, setCompanyBanner] = useState<string | undefined>(userData?.cover_photo);

  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  const getInitials = (name: string) => (name ? name.substring(0, 2).toUpperCase() : 'CO');

  let navigation: any = null;
  try {
    navigation = useNavigation();
  } catch (e) {
    navigation = null;
  }

  const handleLogout = () => {
    Alert.alert(
      'Logout',
      'Are you sure you want to logout?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Logout',
          style: 'destructive',
          onPress: async () => {
            setIsLoggingOut(true);
            setTimeout(() => {
              setIsLoggingOut(false);
              onLogout();
            }, 500);
          },
        },
      ]
    );
  };

  useEffect(() => {
    let isMounted = true;
    const fetchRole = async () => {
      try {
        const res = await role_service.get_current_role();
        if (res?.success) {
          const roleVal = (res as any).current_role || (res as any).data?.current_role || (res as any).user_type;
          const v = String(roleVal || '').toLowerCase();
          const label = v.includes('owner') ? 'Property Owner' : v.includes('contractor') ? 'Contractor' : 'Contractor';
          if (isMounted) setRoleLabel(label);
        }
      } catch (e) {
        // ignore
      }
    };
    fetchRole();
    const sub = AppState.addEventListener('change', (state) => {
      if (state === 'active') fetchRole();
    });
    return () => { isMounted = false; sub.remove(); };
  }, []);

  useEffect(() => {
    let isMounted = true;
    const loadProfile = async () => {
      try {
        const res = await contractors_service.get_my_contractor_profile();
        if (res?.success && res.data) {
          const payload = res.data as any;
          const dataRoot = payload?.data ?? payload?.contractor ?? payload;
          const name = dataRoot?.company_name ?? payload?.company_name;
          const logo = dataRoot?.company_logo ?? dataRoot?.profile_pic ?? payload?.company_logo ?? payload?.profile_pic ?? null;
          const banner = dataRoot?.company_banner ?? dataRoot?.cover_photo ?? payload?.company_banner ?? payload?.cover_photo ?? null;
          if (isMounted) {
            setCompanyName(name || userData?.company_name);
            if (logo) setCompanyLogo(logo);
            if (banner) setCompanyBanner(banner);
          }
        }
      } catch (e) {}
    };
    loadProfile();
    return () => { isMounted = false; };
  }, []);

  const getStorageUrl = (path?: string | null) => {
    if (!path) return undefined;
    const p = String(path);
    if (p.startsWith('http://') || p.startsWith('https://')) return p;
    return `${api_config.base_url}/storage/${p}`;
  };

  const menuSections: { title: string; items: MenuItem[] }[] = [
    {
      title: 'Account',
      items: [
        {
          id: 'view_company',
          icon: 'eye-outline',
          label: 'View Company Profile',
          subtitle: 'See your public profile view',
          showArrow: true,
          onPress: onViewProfile
        },
        {
          id: 'edit_profile',
          icon: 'person-outline',
          label: 'Edit Personal Profile',
          subtitle: 'Update your personal information',
          showArrow: true,
          onPress: () => {
            if (typeof onEditProfile === 'function') {
              onEditProfile();
              return;
            }
            if (navigation && typeof navigation.navigate === 'function') {
              try { navigation.navigate('EditProfile'); return; } catch (e) {}
            }
            // fallback
            Alert.alert('Coming Soon', 'This feature is under development.');
          }
        },
        {
          id: 'switch_role',
          icon: 'swap-horizontal-outline',
          label: 'Switch Role',
          subtitle: 'Manage your role settings',
          showArrow: true,
          onPress: onOpenSwitchRole || (() => Alert.alert('Coming Soon', 'This feature is under development.'))
        }
      ],
    },
    {
      title: 'Preferences',
      items: [
        { id: 'notifications', icon: 'notifications-outline', label: 'Notifications', subtitle: 'Manage notification preferences', showArrow: true, onPress: () => Alert.alert('Coming Soon', 'This feature is under development.') },
        { id: 'privacy', icon: 'shield-outline', label: 'Privacy & Security', subtitle: 'Manage your privacy settings', showArrow: true, onPress: () => {
            if (typeof onOpenChangeOtp === 'function') { onOpenChangeOtp(); return; }
            if (navigation && typeof navigation.navigate === 'function') { try { navigation.navigate('ChangeOtp'); return; } catch (e) {} }
            try { // @ts-ignore
              if (typeof global.set_app_state === 'function') { // @ts-ignore
                global.set_app_state('change_otp'); return; }
            } catch (e) {}
            Alert.alert('Privacy & Security', 'Open change OTP screen.');
          } },
      ],
    },
    {
      title: 'Promotions',
      items: [
        {
          id: 'subscription',
          icon: 'card-outline',
          label: 'Subscription',
          subtitle: 'Manage your subscription plan',
          showArrow: true,
          onPress: () => {
            if (typeof onOpenSubscription === 'function') { onOpenSubscription(); return; }
            if (navigation && typeof navigation.navigate === 'function') { try { navigation.navigate('Subscription'); return; } catch (e) {} }
            try { // @ts-ignore
              if (typeof global.set_app_state === 'function') { // @ts-ignore
                global.set_app_state('subscription'); return; }
            } catch (e) {}
            Alert.alert('Subscription', 'Open subscription screen (not available here).');
          }
        }
      ]
    },
    {
      title: 'Support',
      items: [
        { id: 'help', icon: 'help-circle-outline', label: 'Help Center', subtitle: 'Get help and support', showArrow: true, onPress: () => { if (typeof onOpenHelp === 'function') { onOpenHelp(); } else { Alert.alert('Coming Soon', 'This feature is under development.'); } } },
        { id: 'about', icon: 'information-circle-outline', label: 'About Legatura', subtitle: 'Version 1.0.0', showArrow: true, onPress: () => Alert.alert('About', 'Legatura v1.0.0\n\nConnecting Property Owners with Contractors') },
      ]
    }
  ];

  const renderMenuItem = (item: MenuItem) => (
    <TouchableOpacity key={item.id} style={[styles.menuItem, item.danger && styles.menuItemDanger]} onPress={item.onPress} activeOpacity={0.7}>
      <View style={[styles.menuIconContainer, item.danger && styles.menuIconDanger]}>
        <Ionicons name={item.icon as any} size={22} color={item.danger ? '#E74C3C' : '#1877F2'} />
      </View>
      <View style={styles.menuTextContainer}>
        <Text style={[styles.menuLabel, item.danger && styles.menuLabelDanger]}>{item.label}</Text>
        {item.subtitle && <Text style={styles.menuSubtitle}>{item.subtitle}</Text>}
      </View>
      {item.showArrow && <MaterialIcons name="chevron-right" size={24} color="#CCCCCC" />}
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={[styles.container, { paddingTop: statusBarHeight }]}>
      <StatusBar hidden={true} />
      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
        <View style={styles.header}><Text style={styles.headerTitle}>Settings</Text></View>

        <View style={styles.profileCard}>
          <View style={styles.coverPhotoContainer}>
            {companyBanner || userData?.cover_photo ? (
              <Image source={{ uri: getStorageUrl(companyBanner || userData?.cover_photo) }} style={styles.coverPhoto} resizeMode="cover" />
            ) : (
              <Image source={defaultCoverPhoto} style={styles.coverPhoto} resizeMode="cover" />
            )}
          </View>

          <View style={styles.profileInfoContainer}>
            <View style={styles.avatarContainer}>
              <ImageFallback uri={getStorageUrl(companyLogo || userData?.profile_pic || undefined)} defaultImage={defaultContractorAvatar} style={styles.avatar} resizeMode="cover" />
             </View>

            <Text style={styles.companyName}>{companyName || userData?.company_name || 'Company Name'}</Text>
            <Text style={styles.userName}>@{userData?.username || 'contractor'}</Text>
            <Text style={styles.userEmail}>{userData?.email || 'contractor@example.com'}</Text>

            <View style={styles.badgeRow}>
              <View style={styles.userTypeBadge}><MaterialIcons name={roleLabel === 'Property Owner' ? 'home' : 'business'} size={14} color="#1877F2" /><Text style={styles.userTypeText}>{roleLabel}</Text></View>
              {userData?.contractor_type && (<View style={styles.contractorTypeBadge}><MaterialIcons name="build" size={14} color="#42B883" /><Text style={styles.contractorTypeText}>{userData.contractor_type}</Text></View>)}
            </View>

            {userData?.years_of_experience !== undefined && (<Text style={styles.experienceText}>{userData.years_of_experience} {userData.years_of_experience === 1 ? 'year' : 'years'} of experience</Text>)}
          </View>
        </View>

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

        <View style={styles.logoutSection}>
          <TouchableOpacity style={styles.logoutButton} onPress={handleLogout} disabled={isLoggingOut} activeOpacity={0.8}>
            {isLoggingOut ? (<ActivityIndicator color="#FFFFFF" size="small" />) : (<><Ionicons name="log-out-outline" size={22} color="#FFFFFF" /><Text style={styles.logoutButtonText}>Logout</Text></>)}
          </TouchableOpacity>
        </View>

        <View style={styles.footer}><Text style={styles.footerText}>Legatura © 2025</Text><Text style={styles.footerSubtext}>All rights reserved</Text></View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F5F5' },
  scrollView: { flex: 1 },
  scrollContent: { paddingBottom: 100 },
  header: { paddingHorizontal: 20, paddingVertical: 16, backgroundColor: '#FFFFFF', borderBottomWidth: 1, borderBottomColor: '#E5E5E5' },
  headerTitle: { fontSize: 28, fontWeight: 'bold', color: '#333333' },
  profileCard: { backgroundColor: '#FFFFFF', marginHorizontal: 16, marginTop: 16, borderRadius: 16, overflow: 'hidden', shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 8, elevation: 4 },
  coverPhotoContainer: { height: 100, backgroundColor: '#1877F2' },
  coverPhoto: { width: '100%', height: '100%' },
  profileInfoContainer: { alignItems: 'center', paddingBottom: 20, marginTop: -50 },
  avatarContainer: { position: 'relative' },
  avatar: { width: 100, height: 100, borderRadius: 50, borderWidth: 4, borderColor: '#FFFFFF' },
  editAvatarButton: { position: 'absolute', bottom: 0, right: 0, backgroundColor: '#333333', width: 32, height: 32, borderRadius: 16, justifyContent: 'center', alignItems: 'center', borderWidth: 3, borderColor: '#FFFFFF' },
  companyName: { fontSize: 22, fontWeight: 'bold', color: '#333333', marginTop: 12 },
  userName: { fontSize: 14, color: '#1877F2', marginTop: 2 },
  userEmail: { fontSize: 14, color: '#666666', marginTop: 4 },
  badgeRow: { flexDirection: 'row', alignItems: 'center', marginTop: 12, gap: 8 },
  userTypeBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#EBF5FF', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 20, gap: 6 },
  userTypeText: { fontSize: 12, fontWeight: '600', color: '#1877F2' },
  contractorTypeBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#E8F8F0', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 20, gap: 6 },
  contractorTypeText: { fontSize: 12, fontWeight: '600', color: '#42B883' },
  experienceText: { fontSize: 13, color: '#666666', marginTop: 8 },
  menuSection: { marginTop: 24, paddingHorizontal: 16 },
  sectionTitle: { fontSize: 14, fontWeight: '600', color: '#999999', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 8, marginLeft: 4 },
  menuCard: { backgroundColor: '#FFFFFF', borderRadius: 16, overflow: 'hidden', shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.05, shadowRadius: 4, elevation: 2 },
  menuItem: { flexDirection: 'row', alignItems: 'center', paddingVertical: 14, paddingHorizontal: 16 },
  menuItemDanger: { backgroundColor: '#FFF5F5' },
  menuIconContainer: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#EBF5FF', justifyContent: 'center', alignItems: 'center', marginRight: 14 },
  menuIconDanger: { backgroundColor: '#FFE5E5' },
  menuTextContainer: { flex: 1 },
  menuLabel: { fontSize: 16, fontWeight: '500', color: '#333333' },
  menuLabelDanger: { color: '#E74C3C' },
  menuSubtitle: { fontSize: 13, color: '#999999', marginTop: 2 },
  menuDivider: { height: 1, backgroundColor: '#F0F0F0', marginLeft: 70 },
  logoutSection: { marginTop: 32, paddingHorizontal: 16 },
  logoutButton: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', backgroundColor: '#E74C3C', paddingVertical: 16, borderRadius: 12, gap: 10, shadowColor: '#E74C3C', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 8, elevation: 6 },
  logoutButtonText: { fontSize: 16, fontWeight: '600', color: '#FFFFFF' },
  footer: { alignItems: 'center', marginTop: 32, paddingBottom: 20 },
  footerText: { fontSize: 14, color: '#999999' },
  footerSubtext: { fontSize: 12, color: '#CCCCCC', marginTop: 4 },
});
