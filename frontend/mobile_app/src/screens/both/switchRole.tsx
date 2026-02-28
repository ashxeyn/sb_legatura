// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  RefreshControl,
  Alert,
  ActivityIndicator,
  DeviceEventEmitter,
} from 'react-native';
import { StatusBar, Platform } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import { role_service } from '../../services/role_service';
import { storage_service } from '../../utils/storage';

interface SwitchRoleScreenProps {
  onBack: () => void;
  onRoleChanged: () => void;
  onStartAddRole?: (targetRole: 'contractor' | 'owner') => void;
  userData?: {
    username?: string;
    email?: string;
    user_type?: string;
  };
}

export default function SwitchRoleScreen({ onBack, onRoleChanged, onStartAddRole, userData, navigation }: SwitchRoleScreenProps & { navigation?: any }) {
  const insets = useSafeAreaInsets();
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [switching, setSwitching] = useState(false);
  const [currentRole, setCurrentRole] = useState<'contractor' | 'owner' | null>(null);
  const [canSwitchRoles, setCanSwitchRoles] = useState(false);
  const [pendingRoleRequest, setPendingRoleRequest] = useState(false);
  const [rejectedRoleRequest, setRejectedRoleRequest] = useState(false);
  const [rejectionReason, setRejectionReason] = useState('');
  const [approvedRole, setApprovedRole] = useState<null | 'contractor' | 'owner'>(null);
  // Track last switched role to force UI update after switch
  const [lastSwitchedRole, setLastSwitchedRole] = useState<null | 'contractor' | 'owner'>(null);

  const normalizeRole = (val: any): 'contractor' | 'owner' | null => {
    if (val === null || val === undefined) return null;
    const v = String(val).toLowerCase().trim();
    if (v === 'contractor') return 'contractor';
    if (v === 'owner' || v === 'property_owner' || v === 'property owner') return 'owner';
    return null;
  };

  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  useEffect(() => {
    loadCurrentRole();
    let unsub: any = null;
    if (navigation && typeof navigation.addListener === 'function') {
      unsub = navigation.addListener('focus', () => {
        loadCurrentRole();
      });
    }
    return () => { if (unsub) unsub(); };
  }, [navigation]);

  const loadCurrentRole = async (showLoader = true) => {
    try {
      if (showLoader) setLoading(true);
      else setRefreshing(true);
      const response = await role_service.get_current_role();
      console.log('[DEBUG] get_current_role response:', response);
      // Helpful debug: surface nested role objects and top-level pending flag
      try {
        const d = response.data || response;
        console.log('[DEBUG] contractor:', d.contractor, 'owner:', d.owner, 'pending_role_request:', d.pending_role_request);
      } catch (e) {}
      if (response.success) {
        const data = response.data || response;
        const canSwitch = data.can_switch_roles ?? false;
        const pending = data.pending_role_request ?? false;
        // Determine a reliable current role for display. Prefer explicit current_role; if absent and user_type is 'both',
        // infer from which role is already approved (owner preferred if approved), otherwise fall back to null.
        let inferredRoleRaw: any = data.current_role ?? data.user_type;
        if ((inferredRoleRaw === null || inferredRoleRaw === undefined || String(inferredRoleRaw).toLowerCase() === 'both') && data.user_type === 'both') {
          if (data.owner_role_approved) inferredRoleRaw = 'owner';
          else if (data.contractor_role_approved) inferredRoleRaw = 'contractor';
          else inferredRoleRaw = null;
        }
        const roleValue = inferredRoleRaw;
        // Detect rejected state (check both contractor and owner for 'rejected')
        let rejected = false;
        let rejectionMsg = '';
        let approved: null | 'contractor' | 'owner' = null;
        const currentNormalized = normalizeRole(roleValue);
        // Check for approved application for the other role (use normalized current role we just derived)
        if (data.contractor && data.contractor.verification_status === 'approved' && currentNormalized !== 'contractor') {
          approved = 'contractor';
        }
        if (data.owner && data.owner.verification_status === 'approved' && currentNormalized !== 'owner') {
          approved = 'owner';
        }
        // Check for rejected status in both tables
        if (data.contractor && data.contractor.verification_status === 'rejected') {
          rejected = true;
          rejectionMsg = data.contractor.rejection_reason || 'Your contractor application was rejected.';
        }
        if (data.owner && data.owner.verification_status === 'rejected') {
          rejected = true;
          rejectionMsg = data.owner.rejection_reason || 'Your property owner application was rejected.';
        }

        // If the application was rejected, do not treat it as a pending request for the header/title.
        const showPending = !!pending && !rejected;

        setCurrentRole(currentNormalized);
        setCanSwitchRoles(!!canSwitch);
        setPendingRoleRequest(showPending);
        setRejectedRoleRequest(rejected);
        setRejectionReason(rejectionMsg);
        setApprovedRole(approved);
      } else {
        Alert.alert('Error', 'Failed to load role information');
      }
    } catch (error) {
      console.error('Load role error:', error);
    } finally {
      if (showLoader) setLoading(false);
      else setRefreshing(false);
    }
  };

  const onRefresh = async () => {
    try {
      await loadCurrentRole(false);
    } catch (e) {
      console.warn('Refresh failed', e);
    }
  };

  const handleSwitchRole = async (targetRole: 'contractor' | 'owner') => {
    console.log('handleSwitchRole called', { targetRole, switching, pendingRoleRequest, canSwitchRoles });
    if (switching) return;
    if (pendingRoleRequest && !canSwitchRoles) {
      Alert.alert('Pending', 'Your application is under review; you cannot switch roles yet.');
      return;
    }

    const roleLabel = targetRole === 'contractor' ? 'Contractor' : 'Property Owner';

    Alert.alert(
      `Switch to ${roleLabel}`,
      `Would you like to switch to the ${roleLabel} dashboard?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Switch',
          onPress: async () => {
            setSwitching(true);
            try {
              const response = await role_service.switch_role(targetRole);
              if (response.success) {
                setApprovedRole(null); // Clear approved status after switching
                setLastSwitchedRole(targetRole); // Track last switched
                try {
                  // Refresh current role immediately so UI updates without waiting for navigation
                  await loadCurrentRole();
                } catch (e) {
                  console.warn('Failed to refresh role after switch', e);
                }
                try {
                  // Update stored user preferred_role immediately so other screens
                  // that read cached user synchronously (get_user_data_sync) update
                  const stored = await storage_service.get_user_data();
                  if (stored) {
                    stored.preferred_role = targetRole;
                    // also set a lightweight current_role/determinedRole to help views
                    stored.determinedRole = targetRole === 'contractor' ? 'contractor' : 'owner';
                    await storage_service.save_user_data(stored);
                    console.log('Updated stored user preferred_role after switch:', targetRole);
                  }
                } catch (e) {
                  console.warn('Failed to update stored user after role switch', e);
                }
                // Emit a global event so other screens can react to the change
                try {
                  DeviceEventEmitter.emit('roleChanged', { role: targetRole });
                } catch (e) {}
                onRoleChanged();
                onBack();
              } else {
                Alert.alert('Error', response.message || 'Failed to switch role');
              }
            } catch (error) {
              Alert.alert('Error', 'An error occurred while switching roles');
            } finally {
              setSwitching(false);
            }
          },
        },
      ]
    );
  };

  const handleAddRole = () => {
    const nextRole = currentRole === 'contractor' ? 'owner' : 'contractor';
    if (onStartAddRole) {
      onStartAddRole(nextRole);
    }
  };

  if (loading) {
    return (
      <View style={[styles.container, { paddingTop: statusBarHeight }]}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#EC7E00" />
          <Text style={styles.loadingText}>Loading role information...</Text>
        </View>
      </View>
    );
  }

  return (
    <View style={[styles.container, { paddingTop: statusBarHeight }]}>
      <StatusBar hidden={true} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onBack} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color="#333333" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Account Settings</Text>
        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor="#EC7E00"
            colors={["#EC7E00"]}
          />
        }
      >
        {/* SECTION: Current Status */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>CURRENT ROLE</Text>
          <View style={styles.card}>
            <View style={styles.currentRoleCard}>
              <View style={[
                styles.roleIconContainer,
                currentRole === 'contractor' ? styles.contractorBg : styles.ownerBg
              ]}>
                <MaterialIcons
                  name={currentRole === 'contractor' ? 'business' : 'home'}
                  size={32}
                  color="#FFFFFF"
                />
              </View>
              <View style={styles.roleInfo}>
                <Text style={styles.roleName}>
                  {currentRole === 'contractor' ? 'Contractor' : 'Property Owner'}
                </Text>
                <Text style={styles.roleDescription}>Active Dashboard</Text>
              </View>
              <View style={styles.activeBadge}>
                <Text style={styles.activeBadgeText}>ACTIVE</Text>
              </View>
            </View>
          </View>
        </View>

        {/* SECTION: Switch / Application Status */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>
            {pendingRoleRequest ? 'APPLICATION STATUS' : 'SWITCH ROLE'}
          </Text>

          <View style={styles.card}>
            {rejectedRoleRequest ? (
              /* --- REJECTED VIEW: User's application was rejected --- */
              <View style={styles.pendingCardContent}>
                <View style={styles.pendingHeader}>
                  <View style={[styles.roleIconContainer, { backgroundColor: '#FDECEA' }]}>
                    <MaterialIcons
                      name={currentRole === 'contractor' ? 'home' : 'business'}
                      size={32}
                      color="#E53935"
                    />
                  </View>
                  <View style={styles.roleInfo}>
                    <Text style={[styles.roleName, { color: '#E53935' }]}>
                      {currentRole === 'contractor' ? 'Property Owner' : 'Contractor'}
                    </Text>
                    <View style={[styles.statusBadgePending, { backgroundColor: '#FDECEA', borderColor: '#FFCDD2' }]}>
                      <Text style={[styles.statusBadgeText, { color: '#E53935' }]}>REJECTED</Text>
                    </View>
                  </View>
                </View>
                <View style={styles.dividerFull} />
                <View style={styles.pendingFooter}>
                  <Ionicons name="close-circle-outline" size={18} color="#E53935" />
                  <Text style={[styles.pendingFooterText, { color: '#E53935' }]}>
                    {rejectionReason || 'Your application was rejected.'}
                  </Text>
                </View>
                <View style={{ padding: 16 }}>
                  <TouchableOpacity
                    style={styles.reapplyButton}
                    onPress={async () => {
                      // Determine which role was rejected
                      let target: 'contractor' | 'owner' = (currentRole === 'contractor') ? 'owner' : 'contractor';
                      try {
                        // Fetch existing prefill data from server
                        const res = await role_service.get_switch_form_data();
                        // If API returned validation errors or unsuccessful, show them and abort navigation
                        if (!res?.success) {
                          const errs = res?.errors || (res?.data && res.data.errors) || null;
                          const msg = errs ? (Array.isArray(errs) ? errs.join('\n') : String(errs)) : (res?.message || 'Failed to fetch prefill data');
                          Alert.alert('Error', msg);
                          return;
                        }

                        const existing = res?.data?.existing_data || res?.data?.existing_data_raw || res?.data?.existing_data || null;
                        // Navigate specifically to the roleReapplyScreen route
                        if (navigation && typeof navigation.navigate === 'function') {
                          navigation.navigate('roleReapplyScreen', { targetRole: target, existingData: existing });
                        } else if (onStartAddRole) {
                          // Fallback: reuse add-role flow for re-application without popups
                          onStartAddRole(target);
                        } else {
                          console.warn('Unable to reapply: navigation not available');
                        }
                      } catch (err) {
                        console.error('Reapply navigation error:', err);
                        const msg = err?.message || 'An error occurred while preparing re-application';
                        Alert.alert('Error', msg);
                      }
                    }}
                  >
                    <Text style={styles.reapplyButtonText}>Re-apply</Text>
                  </TouchableOpacity>
                </View>
              </View>
            ) : (approvedRole && approvedRole !== currentRole) ? (
              /* --- APPROVED VIEW: User's application for the other role is approved and not the current role --- */
              <TouchableOpacity
                style={styles.switchRoleCard}
                onPress={() => handleSwitchRole(approvedRole)}
                disabled={switching}
              >
                <View style={[
                  styles.roleIconContainer,
                  approvedRole === 'contractor' ? styles.contractorBg : styles.ownerBg
                ]}>
                  <MaterialIcons
                    name={approvedRole === 'contractor' ? 'business' : 'home'}
                    size={32}
                    color="#FFFFFF"
                  />
                </View>
                <View style={styles.roleInfo}>
                  <Text style={styles.roleName}>
                    {approvedRole === 'contractor' ? 'Contractor' : 'Property Owner'}
                  </Text>
                  <View style={[styles.statusBadgePending, { backgroundColor: '#E8F5E9', borderColor: '#B2DFDB' }]}>
                    <Text style={[styles.statusBadgeText, { color: '#42B883' }]}>APPROVED</Text>
                  </View>
                  <Text style={styles.roleDescription}>Your application is approved! Tap to switch.</Text>
                </View>
                {switching ? (
                  <ActivityIndicator color="#EC7E00" />
                ) : (
                  <MaterialIcons name="chevron-right" size={28} color="#CCCCCC" />
                )}
              </TouchableOpacity>
            ) : pendingRoleRequest ? (
              /* --- PROACTIVE PENDING VIEW: User has applied and is waiting --- */
              <View style={styles.pendingCardContent}>
                <View style={styles.pendingHeader}>
                  <View style={[styles.roleIconContainer, { backgroundColor: '#F0F0F0' }]}>
                    <MaterialIcons
                      name={currentRole === 'contractor' ? 'home' : 'business'}
                      size={32}
                      color="#999"
                    />
                  </View>
                  <View style={styles.roleInfo}>
                    <Text style={[styles.roleName, { color: '#999' }]}>
                      {currentRole === 'contractor' ? 'Property Owner' : 'Contractor'}
                    </Text>
                    <View style={styles.statusBadgePending}>
                      <Text style={styles.statusBadgeText}>UNDER REVIEW</Text>
                    </View>
                  </View>
                </View>
                <View style={styles.dividerFull} />
                <View style={styles.pendingFooter}>
                  <Ionicons name="time-outline" size={18} color="#F39C12" />
                  <Text style={styles.pendingFooterText}>
                    The administrator is currently reviewing your application. You will be able to switch roles once approved.
                  </Text>
                </View>
              </View>
            ) : canSwitchRoles ? (
              /* --- STANDARD SWITCH VIEW: User is approved for both --- */
              <TouchableOpacity
                style={styles.switchRoleCard}
                onPress={() => handleSwitchRole(currentRole === 'contractor' ? 'owner' : 'contractor')}
                disabled={switching}
              >
                <View style={[
                  styles.roleIconContainer,
                  currentRole === 'contractor' ? styles.ownerBg : styles.contractorBg
                ]}>
                  <MaterialIcons
                    name={currentRole === 'contractor' ? 'home' : 'business'}
                    size={32}
                    color="#FFFFFF"
                  />
                </View>
                <View style={styles.roleInfo}>
                  <Text style={styles.roleName}>
                    {currentRole === 'contractor' ? 'Property Owner' : 'Contractor'}
                  </Text>
                  <Text style={styles.roleDescription}>Switch to dashboard</Text>
                </View>
                {switching ? (
                  <ActivityIndicator color="#EC7E00" />
                ) : (
                  <MaterialIcons name="chevron-right" size={28} color="#CCCCCC" />
                )}
              </TouchableOpacity>
            ) : (
              /* --- ADD ROLE VIEW: User has only one role --- */
              <TouchableOpacity style={styles.switchRoleCard} onPress={handleAddRole}>
                <View style={[styles.roleIconContainer, { backgroundColor: '#E8F5E9' }]}>
                  <MaterialIcons name="add" size={32} color="#42B883" />
                </View>
                <View style={styles.roleInfo}>
                  <Text style={styles.roleName}>Apply for Dual Role</Text>
                  <Text style={styles.roleDescription}>
                    Become a {currentRole === 'contractor' ? 'Property Owner' : 'Contractor'}
                  </Text>
                </View>
                <MaterialIcons name="chevron-right" size={28} color="#CCCCCC" />
              </TouchableOpacity>
            )}
          </View>
        </View>

        {/* SECTION: Account Info */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>ACCOUNT INFORMATION</Text>
          <View style={styles.card}>
            <View style={styles.infoRow}>
              <Ionicons name="person-outline" size={20} color="#666" />
              <Text style={styles.infoLabel}>Username</Text>
              <Text style={styles.infoValue}>{userData?.username || 'N/A'}</Text>
            </View>
            <View style={styles.divider} />
            <View style={styles.infoRow}>
              <Ionicons name="mail-outline" size={20} color="#666" />
              <Text style={styles.infoLabel}>Email</Text>
              <Text style={[styles.infoValue, styles.infoValueWrap]} numberOfLines={2} ellipsizeMode="middle">{userData?.email || 'N/A'}</Text>
            </View>
            <View style={styles.divider} />
            <View style={styles.infoRow}>
              <Ionicons name="shield-checkmark-outline" size={20} color="#666" />
              <Text style={styles.infoLabel}>Role Access</Text>
              <Text style={styles.infoValue}>
                {canSwitchRoles ? 'Dual Role' : 'Single Role'}
              </Text>
            </View>
          </View>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F5F5F5' },
  loadingContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 16, fontSize: 14, color: '#666' },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingVertical: 16,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#EEE'
  },
  headerTitle: { fontSize: 18, fontWeight: 'bold', color: '#333' },
  scrollContent: { paddingBottom: 40 },
  section: { marginTop: 24, paddingHorizontal: 16 },
  sectionTitle: { fontSize: 12, fontWeight: '700', color: '#999', marginBottom: 12, marginLeft: 4, letterSpacing: 1 },
  card: { backgroundColor: '#FFFFFF', borderRadius: 16, elevation: 3, shadowColor: '#000', shadowOpacity: 0.05, shadowRadius: 10, overflow: 'hidden' },
  currentRoleCard: { flexDirection: 'row', alignItems: 'center', padding: 20 },
  switchRoleCard: { flexDirection: 'row', alignItems: 'center', padding: 20 },
  roleIconContainer: { width: 56, height: 56, borderRadius: 28, justifyContent: 'center', alignItems: 'center', marginRight: 16 },
  contractorBg: { backgroundColor: '#1877F2' },
  ownerBg: { backgroundColor: '#EC7E00' },
  reapplyButton: {
    backgroundColor: '#EC7E00',
    paddingVertical: 12,
    paddingHorizontal: 18,
    borderRadius: 10,
    alignItems: 'center'
  },
  reapplyButtonText: { color: '#FFF', fontWeight: '700' },
  roleInfo: { flex: 1 },
  roleName: { fontSize: 17, fontWeight: 'bold', color: '#333' },
  roleDescription: { fontSize: 13, color: '#777', marginTop: 2 },
  activeBadge: { backgroundColor: '#42B883', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
  activeBadgeText: { fontSize: 10, fontWeight: '800', color: '#FFF' },

  // Pending Styles
  pendingCardContent: { padding: 20 },
  pendingHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 16 },
  statusBadgePending: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF8E5',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 6,
    marginTop: 6,
    borderWidth: 1,
    borderColor: '#FFE0B2',
    alignSelf: 'flex-start'
  },
  statusBadgeText: { color: '#EC7E00', fontSize: 11, fontWeight: '700' },
  pendingFooter: { flexDirection: 'row', marginTop: 16, backgroundColor: '#FAFAFA', padding: 12, borderRadius: 8 },
  pendingFooterText: { flex: 1, fontSize: 12, color: '#666', marginLeft: 8, lineHeight: 18 },

  infoRow: { flexDirection: 'row', alignItems: 'center', padding: 16 },
  infoLabel: { flex: 1, fontSize: 14, color: '#666', marginLeft: 12 },
  infoValue: { fontSize: 14, fontWeight: '600', color: '#333' },
  infoValueWrap: { flex: 2, textAlign: 'right', flexWrap: 'wrap' },
  divider: { height: 1, backgroundColor: '#F0F0F0', marginLeft: 48 },
  dividerFull: { height: 1, backgroundColor: '#F0F0F0' },
});
