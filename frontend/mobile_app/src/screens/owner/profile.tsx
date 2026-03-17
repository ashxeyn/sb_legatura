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
  RefreshControl,
  Modal,
  TextInput,
} from 'react-native';
import { View as SafeAreaView, StatusBar, Platform, AppState } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { role_service } from '../../services/role_service';
import { api_config, api_request } from '../../config/api';
import { storage_service } from '../../utils/storage';
import ImageFallback from '../../components/imageFallback';

const DELETION_REASONS = [
  { key: 'taking_a_break', label: 'Taking a break' },
  { key: 'too_many_notifications', label: 'Too many notifications' },
  { key: 'privacy_concerns', label: 'Privacy concerns' },
  { key: 'created_second_account', label: 'Created a second account' },
  { key: 'not_useful', label: "Don't find it useful" },
  { key: 'safety_concern', label: 'Safety concern' },
  { key: 'other', label: 'Something else' },
];

const COLORS = {
  primary: '#EC7E00',
};

const defaultCoverPhoto = require('../../../assets/images/pictures/cp_default.jpg');
const defaultOwnerAvatar = require('../../../assets/images/pictures/property_owner_default.png');

interface ProfileScreenProps {
  onLogout: () => void;
  onEditProfile?: () => void;
  onViewProfile?: () => void;
  onOpenHelp?: () => void;
  onOpenSwitchRole?: () => void; // ✅ Navigate to switch role screen
  onOpenBoosts?: () => void; // Navigate to Boosts screen
  onOpenChangeOtp?: () => void;
  contractorVerified?: boolean;
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

export default function ProfileScreen({ onLogout, onViewProfile, onEditProfile, onOpenHelp, onOpenSwitchRole, onOpenBoosts, onOpenChangeOtp, contractorVerified: contractorVerifiedProp, userData }: ProfileScreenProps) {
  const insets = useSafeAreaInsets();
  const [isLoggingOut, setIsLoggingOut] = useState(false);
  const [roleLabel, setRoleLabel] = useState<string>('Property Owner');
  const [contractorVerified, setContractorVerified] = useState(contractorVerifiedProp ?? false);
  // Company name when user is a staff/representative member of a contractor company via invitation
  const [staffCompanyName, setStaffCompanyName] = useState<string | null>(null);
  const [refreshing, setRefreshing] = useState(false);
  // Account management
  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const [selectedReason, setSelectedReason] = useState<string>('');
  const [otherReasonText, setOtherReasonText] = useState('');
  const [confirmationText, setConfirmationText] = useState('');
  const [isSubmittingAction, setIsSubmittingAction] = useState(false);

  // Get status bar height (top inset)
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  // Get initials from username for avatar fallback
  const getInitials = (name: string) => {
    return name ? name.substring(0, 2).toUpperCase() : 'PO';
  };

  // Resolve storage paths returned from the backend (e.g. "profiles/...")
  const getStorageUrl = (path: string | null | undefined): string | undefined => {
    if (!path) return undefined;
    if (path.startsWith('http')) return path;
    return `${api_config.base_url}/storage/${path}`;
  };

  // Local image state — seeded from prop, then refreshed from API (mirrors contractor pattern)
  const [ownerProfilePicPath, setOwnerProfilePicPath] = useState<string | null>(userData?.profile_pic || null);
  const [ownerCoverPhotoPath, setOwnerCoverPhotoPath] = useState<string | null>(userData?.cover_photo || null);
  const [ownerFullName, setOwnerFullName] = useState<string>('');

  const loadProfile = async () => {
    try {
      const res = await api_request('/api/profile/fetch', { method: 'GET' });
      if (res?.success && res.data) {
        const payload = res.data?.data ?? res.data;
        const user = payload?.user ?? payload;
        const pic = user?.profile_pic ?? null;
        const cover = user?.cover_photo ?? null;
        const contractorStatus = payload?.contractor?.verification_status ?? null;
        if (pic) setOwnerProfilePicPath(pic);
        if (cover) setOwnerCoverPhotoPath(cover);
        if (contractorStatus === 'approved') setContractorVerified(true);
        else if (contractorVerifiedProp) setContractorVerified(true);
        const fullName = `${user?.first_name || ''} ${user?.middle_name || ''} ${user?.last_name || ''}`.replace(/\s+/g, ' ').trim();
        if (fullName) setOwnerFullName(fullName);
      }
    } catch (e) {}
  };

  useEffect(() => {
    loadProfile();
  }, []);

  const onRefresh = async () => {
    setRefreshing(true);
    await loadProfile();
    setRefreshing(false);
  };

  // Auto-refresh profile every 15 seconds
  useEffect(() => {
    const interval = setInterval(() => {
      loadProfile();
    }, 60000);

    return () => clearInterval(interval);
  }, []);

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

  const handleDeleteAccount = async () => {
    if (!selectedReason) {
      Alert.alert('Required', 'Please select a reason.');
      return;
    }
    if (confirmationText !== 'ACCOUNT DELETE') {
      Alert.alert('Confirmation Required', 'Please type "ACCOUNT DELETE" to confirm.');
      return;
    }
    setIsSubmittingAction(true);
    try {
      const res = await api_request('/api/account/delete', {
        method: 'POST',
        body: JSON.stringify({
          role: 'owner',
          reason_key: selectedReason,
          reason_text: selectedReason === 'other' ? otherReasonText : '',
          confirmation_text: confirmationText,
        }),
      });
      if (res?.success) {
        setShowDeleteModal(false);
        setSelectedReason('');
        setOtherReasonText('');
        setConfirmationText('');
        onLogout();
      } else {
        Alert.alert('Error', res?.message || 'Failed to delete account.');
      }
    } catch {
      Alert.alert('Error', 'Failed to connect to server.');
    } finally {
      setIsSubmittingAction(false);
    }
  };

  // Refresh current role on mount and focus to update badge
  useEffect(() => {
    let isMounted = true;
    const fetchRole = async () => {
      try {
        const res = await role_service.get_current_role();
        if (res?.success) {
          const data = (res as any).data || res;
          const roleVal = data.current_role || data.user_type;
          const v = String(roleVal || '').toLowerCase();
          const label = v.includes('contractor') ? 'Contractor' : v.includes('owner') ? 'Property Owner' : 'Property Owner';
          if (isMounted) setRoleLabel(label);

          // Check for active staff membership (accepted invitation)
          const staffRecord = data.staff_record || null;
          const isActiveMember = !!(data.has_active_staff_membership);
          if (isMounted) {
            if (isActiveMember && staffRecord) {
              setStaffCompanyName(
                staffRecord.contractor_name || staffRecord.company_name || 'Contractor Company'
              );
            } else {
              setStaffCompanyName(null);
            }
          }
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
          icon: 'business-outline',
          label: contractorVerified
            ? 'Switch to Contractor Company'
            : staffCompanyName
              ? `Switch to ${staffCompanyName}`
              : 'Add Company',
          subtitle: contractorVerified
            ? 'Switch to your verified contractor company'
            : staffCompanyName
              ? `Switch to ${staffCompanyName} profile`
              : 'Register or manage your company',
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
    {
      title: 'Account & Data',
      items: [
        {
          id: 'delete_account',
          icon: 'trash-outline',
          label: 'Delete Account',
          subtitle: 'Permanently delete your account',
          showArrow: true,
          danger: true,
          onPress: () => {
            setSelectedReason('');
            setOtherReasonText('');
            setConfirmationText('');
            setShowDeleteModal(true);
          },
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
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            colors={[COLORS.primary]}
            tintColor={COLORS.primary}
          />
        }
      >
        {/* Header */}
        <View style={styles.header}>
          <Text style={styles.headerTitle}>Settings</Text>
        </View>

        {/* Profile Card */}
        <View style={styles.profileCard}>
          {/* Cover Photo */}
          <View style={styles.coverPhotoContainer}>
            <Image source={defaultCoverPhoto} style={styles.coverPhoto} resizeMode="cover" />
            {(ownerCoverPhotoPath || userData?.cover_photo) && (
              <Image
                source={{ uri: getStorageUrl(ownerCoverPhotoPath || userData?.cover_photo) }}
                style={[styles.coverPhoto, { position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }]}
                resizeMode="cover"
              />
            )}
          </View>

          {/* Profile Info */}
          <View style={styles.profileInfoContainer}>
            <View style={styles.avatarContainer}>
              <ImageFallback uri={getStorageUrl(ownerProfilePicPath || userData?.profile_pic || undefined)} defaultImage={defaultOwnerAvatar} style={styles.avatar} resizeMode="cover" />
            </View>

            <Text style={styles.userName}>{ownerFullName || userData?.username || 'Property Owner'}</Text>
            {ownerFullName ? (
              <Text style={styles.userHandle}>@{userData?.username || 'user'}</Text>
            ) : null}
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

      {/* Delete Account Modal */}
      <Modal visible={showDeleteModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.accountModalContainer}>
            <View style={styles.accountModalHeader}>
              <Text style={[styles.accountModalTitle, { color: '#E74C3C' }]}>Delete Account</Text>
              <TouchableOpacity onPress={() => setShowDeleteModal(false)} hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}>
                <Ionicons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>

            <View style={styles.warningBox}>
              <Ionicons name="warning-outline" size={20} color="#E74C3C" />
              <Text style={styles.warningText}>This action will permanently delete your account, including any contractor companies you own and all associated team members. This cannot be undone.</Text>
            </View>

            <Text style={styles.reasonSectionTitle}>Why are you leaving?</Text>

            <ScrollView style={styles.reasonList} showsVerticalScrollIndicator={false}>
              {DELETION_REASONS.map((reason) => (
                <TouchableOpacity
                  key={reason.key}
                  style={[styles.reasonItem, selectedReason === reason.key && styles.reasonItemSelected]}
                  onPress={() => setSelectedReason(reason.key)}
                >
                  <Ionicons
                    name={selectedReason === reason.key ? 'radio-button-on' : 'radio-button-off'}
                    size={22}
                    color={selectedReason === reason.key ? '#E74C3C' : '#999'}
                  />
                  <Text style={[styles.reasonLabel, selectedReason === reason.key && styles.reasonLabelSelected]}>{reason.label}</Text>
                </TouchableOpacity>
              ))}
              {selectedReason === 'other' && (
                <TextInput
                  style={styles.otherReasonInput}
                  placeholder="Tell us more..."
                  placeholderTextColor="#999"
                  value={otherReasonText}
                  onChangeText={setOtherReasonText}
                  multiline
                  maxLength={500}
                />
              )}
            </ScrollView>

            <Text style={styles.reasonSectionTitle}>Type "ACCOUNT DELETE" to confirm</Text>
            <TextInput
              style={[styles.otherReasonInput, { minHeight: 44, marginTop: 0, marginBottom: 16 }]}
              placeholder="ACCOUNT DELETE"
              placeholderTextColor="#CCC"
              value={confirmationText}
              onChangeText={setConfirmationText}
              autoCapitalize="characters"
            />

            <TouchableOpacity
              style={[styles.accountActionButton, styles.deleteButton, (!selectedReason || confirmationText !== 'ACCOUNT DELETE') && styles.accountActionButtonDisabled]}
              onPress={handleDeleteAccount}
              disabled={!selectedReason || confirmationText !== 'ACCOUNT DELETE' || isSubmittingAction}
            >
              {isSubmittingAction ? (
                <ActivityIndicator color="#FFF" size="small" />
              ) : (
                <Text style={styles.accountActionButtonText}>Delete My Account</Text>
              )}
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
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
    borderRadius: 10,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 3,
  },
  coverPhotoContainer: {
    height: 100,
    backgroundColor: '#E5E7EB',
    overflow: 'hidden',
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
  userName: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333333',
    marginTop: 12,
  },
  userHandle: {
    fontSize: 14,
    color: '#999999',
    marginTop: 2,
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
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 6,
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
    borderRadius: 8,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 1,
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
    width: 38,
    height: 38,
    borderRadius: 8,
    backgroundColor: '#FFF5EB',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
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
    paddingVertical: 14,
    borderRadius: 8,
    gap: 10,
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
  // Account Management Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'flex-end',
  },
  accountModalContainer: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    padding: 24,
    maxHeight: '85%',
  },
  accountModalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  accountModalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333333',
  },
  accountModalDescription: {
    fontSize: 14,
    color: '#666666',
    lineHeight: 20,
    marginBottom: 16,
  },
  warningBox: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#FFF5F5',
    borderRadius: 8,
    padding: 12,
    marginBottom: 16,
    gap: 10,
  },
  warningText: {
    flex: 1,
    fontSize: 13,
    color: '#E74C3C',
    lineHeight: 18,
  },
  reasonSectionTitle: {
    fontSize: 15,
    fontWeight: '600',
    color: '#333333',
    marginBottom: 12,
  },
  reasonList: {
    maxHeight: 280,
    marginBottom: 16,
  },
  reasonItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: 12,
    borderRadius: 8,
    marginBottom: 4,
    gap: 12,
  },
  reasonItemSelected: {
    backgroundColor: '#FFF5EB',
  },
  reasonLabel: {
    fontSize: 15,
    color: '#333333',
  },
  reasonLabelSelected: {
    fontWeight: '600',
    color: '#EC7E00',
  },
  otherReasonInput: {
    borderWidth: 1,
    borderColor: '#E0E0E0',
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    color: '#333333',
    minHeight: 80,
    textAlignVertical: 'top',
    marginTop: 8,
    marginBottom: 8,
  },
  accountActionButton: {
    paddingVertical: 14,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  accountActionButtonDisabled: {
    opacity: 0.5,
  },
  deactivateButton: {
    backgroundColor: '#EC7E00',
  },
  deleteButton: {
    backgroundColor: '#E74C3C',
  },
  accountActionButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
  },
});

