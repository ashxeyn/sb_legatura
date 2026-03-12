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

// Color palette (matches milestoneDetail)
const COLORS = {
  primary: '#1E3A5F',
  primaryLight: '#E8EEF4',
  accent: '#EC7E00',
  accentLight: '#FFF3E6',
  success: '#10B981',
  successLight: '#D1FAE5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
  errorLight: '#FEE2E2',
  info: '#3B82F6',
  infoLight: '#DBEAFE',
  background: '#FFFFFF',
  surface: '#FFFFFF',
  text: '#1E3A5F',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
};

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
  const [dismissedApprovedRoles, setDismissedApprovedRoles] = useState<Array<'contractor' | 'owner'>>([]);
  // Track last switched role to force UI update after switch
  const [lastSwitchedRole, setLastSwitchedRole] = useState<null | 'contractor' | 'owner'>(null);
  // Staff/member context — set when user is an active member of a contractor company via invitation
  const [staffMembership, setStaffMembership] = useState<{ contractor_id?: number; company_name?: string; company_role?: string } | null>(null);

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
    loadDismissedApprovedRoles();
    let unsub: any = null;
    if (navigation && typeof navigation.addListener === 'function') {
      unsub = navigation.addListener('focus', () => {
        loadCurrentRole();
      });
    }
    return () => { if (unsub) unsub(); };
  }, [navigation]);

  const loadDismissedApprovedRoles = async () => {
    try {
      const stored = await storage_service.get_user_data();
      const saved = stored?.dismissed_approved_roles;
      if (Array.isArray(saved)) {
        const normalized = saved
          .map((r: any) => normalizeRole(r))
          .filter((r: any) => r === 'contractor' || r === 'owner');
        setDismissedApprovedRoles(normalized as Array<'contractor' | 'owner'>);
      }
    } catch (e) {
      console.warn('Failed to load dismissed approved roles', e);
    }
  };

  const markApprovedPromptDismissed = async (role: 'contractor' | 'owner') => {
    try {
      setDismissedApprovedRoles(prev => (prev.includes(role) ? prev : [...prev, role]));
      const stored = await storage_service.get_user_data();
      if (stored) {
        const existing = Array.isArray(stored.dismissed_approved_roles) ? stored.dismissed_approved_roles : [];
        if (!existing.includes(role)) {
          stored.dismissed_approved_roles = [...existing, role];
          await storage_service.save_user_data(stored);
        }
      }
    } catch (e) {
      console.warn('Failed to persist dismissed approved role prompt', e);
    }
  };

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
        // Check for approved application for the other role.
        // Owner is the base/primary role, so only surface "approved application"
        // for contractor role additions.
        if (data.contractor && data.contractor.verification_status === 'approved' && currentNormalized !== 'contractor') {
          approved = 'contractor';
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

        // Capture staff membership so the UI can show "Switch to [Company]" for invited members
        const staffRecord = data.staff_record || null;
        const isActiveMember = !!(data.has_active_staff_membership);
        if (isActiveMember && staffRecord) {
          setStaffMembership({
            contractor_id: staffRecord.contractor_id,
            company_name: staffRecord.contractor_name || staffRecord.company_name || null,
            company_role: staffRecord.company_role,
          });
        } else {
          setStaffMembership(null);
        }
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
      Alert.alert('Pending', 'Your application is under review; you cannot switch dashboards yet.');
      return;
    }

    const roleLabel = targetRole === 'contractor' ? 'Contractor' : 'Property Owner';

    Alert.alert(
      `Switch to ${roleLabel} Dashboard`,
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
                await markApprovedPromptDismissed(targetRole);
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
                Alert.alert('Error', response.message || 'Failed to switch dashboard');
              }
            } catch (error) {
              Alert.alert('Error', 'An error occurred while switching dashboards');
            } finally {
              setSwitching(false);
            }
          },
        },
      ]
    );
  };

  const handleAddRole = () => {
    if (onStartAddRole) {
      onStartAddRole('contractor');
    }
  };

  if (loading) {
    return (
      <View style={[styles.container, { paddingTop: statusBarHeight }]}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.accent} />
          <Text style={styles.loadingText}>Loading role information...</Text>
        </View>
      </View>
    );
  }

  const visibleApprovedRole = approvedRole && !dismissedApprovedRoles.includes(approvedRole)
    ? approvedRole
    : null;
  const hasOtherRoleAvailable = !!approvedRole && approvedRole !== currentRole;

  return (
    <View style={[styles.container, { paddingTop: statusBarHeight }]}>
      <StatusBar hidden={true} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onBack} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Company Management</Text>
        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={COLORS.accent}
            colors={[COLORS.accent]}
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
                  size={24}
                  color={COLORS.surface}
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
            {pendingRoleRequest ? 'APPLICATION STATUS' : 'COMPANY MANAGEMENT'}
          </Text>

          <View style={styles.card}>
            {rejectedRoleRequest ? (
              /* --- REJECTED VIEW: User's application was rejected --- */
              <View style={styles.pendingCardContent}>
                <View style={styles.pendingHeader}>
                  <View style={[styles.roleIconContainer, { backgroundColor: COLORS.errorLight }]}>
                    <MaterialIcons
                      name="business"
                      size={24}
                      color={COLORS.error}
                    />
                  </View>
                  <View style={styles.roleInfo}>
                    <Text style={[styles.roleName, { color: COLORS.error }]}>
                      Contractor Application
                    </Text>
                    <View style={[styles.statusBadgePending, { backgroundColor: COLORS.errorLight, borderColor: COLORS.error }]}>
                      <Text style={[styles.statusBadgeText, { color: COLORS.error }]}>REJECTED</Text>
                    </View>
                  </View>
                </View>
                <View style={styles.dividerFull} />
                <View style={styles.pendingFooter}>
                  <Ionicons name="close-circle-outline" size={18} color={COLORS.error} />
                  <Text style={[styles.pendingFooterText, { color: COLORS.error }]}>
                    {rejectionReason || 'Your application was rejected.'}
                  </Text>
                </View>
                <View style={{ padding: 16 }}>
                  <TouchableOpacity
                    style={styles.reapplyButton}
                    onPress={async () => {
                      // Re-application is only for adding a company (contractor role)
                      let target: 'contractor' | 'owner' = 'contractor';
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
            ) : (visibleApprovedRole && visibleApprovedRole !== currentRole) ? (
              /* --- APPROVED VIEW: User's application for the other role is approved and not the current role --- */
              <TouchableOpacity
                style={styles.switchRoleCard}
                onPress={() => {
                  markApprovedPromptDismissed(visibleApprovedRole);
                  handleSwitchRole(visibleApprovedRole);
                }}
                disabled={switching}
              >
                <View style={[
                  styles.roleIconContainer,
                  visibleApprovedRole === 'contractor' ? styles.contractorBg : styles.ownerBg
                ]}>
                  <MaterialIcons
                    name={visibleApprovedRole === 'contractor' ? 'business' : 'home'}
                    size={24}
                    color={COLORS.surface}
                  />
                </View>
                <View style={styles.roleInfo}>
                  <Text style={styles.roleName}>
                    {visibleApprovedRole === 'contractor' ? 'Contractor' : 'Property Owner'}
                  </Text>
                  <View style={[styles.statusBadgePending, { backgroundColor: COLORS.successLight, borderColor: COLORS.success }]}>
                    <Text style={[styles.statusBadgeText, { color: COLORS.success }]}>APPROVED</Text>
                  </View>
                  <Text style={styles.roleDescription}>Your application is approved! Tap to switch to this profile.</Text>
                </View>
                {switching ? (
                  <ActivityIndicator color={COLORS.accent} />
                ) : (
                  <MaterialIcons name="chevron-right" size={22} color={COLORS.textMuted} />
                )}
              </TouchableOpacity>
            ) : pendingRoleRequest ? (
              /* --- PROACTIVE PENDING VIEW: User has applied and is waiting --- */
              <View style={styles.pendingCardContent}>
                <View style={styles.pendingHeader}>
                  <View style={[styles.roleIconContainer, { backgroundColor: COLORS.borderLight }]}>
                    <MaterialIcons
                      name="business"
                      size={24}
                      color={COLORS.textMuted}
                    />
                  </View>
                  <View style={styles.roleInfo}>
                    <Text style={[styles.roleName, { color: COLORS.textMuted }]}>
                      Contractor Application
                    </Text>
                    <View style={styles.statusBadgePending}>
                      <Text style={styles.statusBadgeText}>UNDER REVIEW</Text>
                    </View>
                  </View>
                </View>
                <View style={styles.dividerFull} />
                <View style={styles.pendingFooter}>
                  <Ionicons name="time-outline" size={18} color={COLORS.warning} />
                  <Text style={styles.pendingFooterText}>
                    The administrator is currently reviewing your application. You will be able to switch dashboards once approved.
                  </Text>
                </View>
              </View>
            ) : (canSwitchRoles || currentRole === 'contractor' || hasOtherRoleAvailable) ? (
              /* --- STANDARD SWITCH VIEW: User can switch roles --- */
              <TouchableOpacity
                style={styles.switchRoleCard}
                onPress={() => handleSwitchRole((hasOtherRoleAvailable ? approvedRole : (currentRole === 'contractor' ? 'owner' : 'contractor')) as 'contractor' | 'owner')}
                disabled={switching}
              >
                <View style={[
                  styles.roleIconContainer,
                  ((hasOtherRoleAvailable ? approvedRole : (currentRole === 'contractor' ? 'owner' : 'contractor')) === 'owner') ? styles.ownerBg : styles.contractorBg
                ]}>
                  <MaterialIcons
                    name={((hasOtherRoleAvailable ? approvedRole : (currentRole === 'contractor' ? 'owner' : 'contractor')) === 'owner') ? 'home' : 'business'}
                    size={24}
                    color={COLORS.surface}
                  />
                </View>
                <View style={styles.roleInfo}>
                  <Text style={styles.roleName}>
                    {(hasOtherRoleAvailable ? approvedRole : (currentRole === 'contractor' ? 'owner' : 'contractor')) === 'owner' ? 'Property Owner' : 'Contractor'}
                  </Text>
                  <Text style={styles.roleDescription}>Switch to {(hasOtherRoleAvailable ? approvedRole : (currentRole === 'contractor' ? 'owner' : 'contractor')) === 'owner' ? 'Property Owner' : 'Contractor'} profile</Text>
                </View>
                {switching ? (
                  <ActivityIndicator color={COLORS.accent} />
                ) : (
                  <MaterialIcons name="chevron-right" size={22} color={COLORS.textMuted} />
                )}
              </TouchableOpacity>
            ) : staffMembership ? (
              /* --- STAFF MEMBER VIEW: User accepted an invitation to join a contractor company --- */
              <TouchableOpacity
                style={styles.switchRoleCard}
                onPress={() => handleSwitchRole('contractor')}
                disabled={switching}
              >
                <View style={[styles.roleIconContainer, styles.contractorBg]}>
                  <MaterialIcons name="business" size={24} color={COLORS.surface} />
                </View>
                <View style={styles.roleInfo}>
                  <Text style={styles.roleName}>
                    {staffMembership.company_name || 'Contractor Company'}
                  </Text>
                  <Text style={styles.roleDescription}>
                    Switch to {staffMembership.company_name || 'Contractor'} profile
                  </Text>
                </View>
                {switching ? (
                  <ActivityIndicator color={COLORS.accent} />
                ) : (
                  <MaterialIcons name="chevron-right" size={22} color={COLORS.textMuted} />
                )}
              </TouchableOpacity>
            ) : (
              /* --- ADD COMPANY VIEW: User is an owner and can add a contractor company --- */
              <TouchableOpacity style={styles.switchRoleCard} onPress={handleAddRole}>
                <View style={[styles.roleIconContainer, { backgroundColor: COLORS.successLight }]}>
                  <MaterialIcons name="add-business" size={24} color={COLORS.success} />
                </View>
                <View style={styles.roleInfo}>
                  <Text style={styles.roleName}>Add Company</Text>
                  <Text style={styles.roleDescription}>
                    Register your company as a contractor
                  </Text>
                </View>
                <MaterialIcons name="chevron-right" size={22} color={COLORS.textMuted} />
              </TouchableOpacity>
            )}
          </View>
        </View>

        {/* SECTION: Account Info */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>ACCOUNT INFORMATION</Text>
          <View style={styles.card}>
            <View style={styles.infoRow}>
              <Ionicons name="person-outline" size={20} color={COLORS.textSecondary} />
              <Text style={styles.infoLabel}>Username</Text>
              <Text style={styles.infoValue}>{userData?.username || 'N/A'}</Text>
            </View>
            <View style={styles.divider} />
            <View style={styles.infoRow}>
              <Ionicons name="mail-outline" size={20} color={COLORS.textSecondary} />
              <Text style={styles.infoLabel}>Email</Text>
              <Text style={[styles.infoValue, styles.infoValueWrap]} numberOfLines={2} ellipsizeMode="middle">{userData?.email || 'N/A'}</Text>
            </View>
            <View style={styles.divider} />
            <View style={styles.infoRow}>
              <Ionicons name="shield-checkmark-outline" size={20} color={COLORS.textSecondary} />
              <Text style={styles.infoLabel}>Account Type</Text>
              <Text style={styles.infoValue}>
                {canSwitchRoles ? 'Owner + Contractor' : 'Property Owner'}
              </Text>
            </View>
          </View>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: COLORS.background },
  loadingContainer: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 16, fontSize: 14, color: COLORS.textSecondary },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 8,
    paddingVertical: 12,
  },
  headerTitle: { fontSize: 18, fontWeight: '700', color: COLORS.text },
  backButton: { width: 44, height: 44, justifyContent: 'center', alignItems: 'center' },
  headerSpacer: { width: 44 },
  scrollContent: { paddingHorizontal: 24, paddingBottom: 40 },
  section: { marginTop: 20 },
  sectionTitle: { fontSize: 11, fontWeight: '700', color: COLORS.textMuted, marginBottom: 10, marginLeft: 4, letterSpacing: 0.5, textTransform: 'uppercase' },
  card: {
    backgroundColor: COLORS.surface,
    borderRadius: 4,
    borderWidth: 1,
    borderColor: COLORS.border,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 2,
    overflow: 'hidden',
  },
  currentRoleCard: { flexDirection: 'row', alignItems: 'center', padding: 16 },
  switchRoleCard: { flexDirection: 'row', alignItems: 'center', padding: 16 },
  roleIconContainer: { width: 40, height: 40, borderRadius: 6, justifyContent: 'center', alignItems: 'center', marginRight: 12 },
  contractorBg: { backgroundColor: COLORS.primary },
  ownerBg: { backgroundColor: COLORS.accent },
  reapplyButton: {
    backgroundColor: COLORS.accent,
    paddingVertical: 12,
    paddingHorizontal: 18,
    borderRadius: 8,
    alignItems: 'center',
  },
  reapplyButtonText: { color: COLORS.surface, fontWeight: '700', fontSize: 14 },
  roleInfo: { flex: 1 },
  roleName: { fontSize: 15, fontWeight: '700', color: COLORS.text },
  roleDescription: { fontSize: 13, color: COLORS.textSecondary, marginTop: 2 },
  activeBadge: { backgroundColor: COLORS.success, paddingHorizontal: 10, paddingVertical: 4, borderRadius: 4 },
  activeBadgeText: { fontSize: 10, fontWeight: '800', color: COLORS.surface },

  // Pending Styles
  pendingCardContent: { padding: 16 },
  pendingHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 14 },
  statusBadgePending: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.warningLight,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 4,
    marginTop: 6,
    borderWidth: 1,
    borderColor: COLORS.warning,
    alignSelf: 'flex-start',
  },
  statusBadgeText: { color: COLORS.accent, fontSize: 11, fontWeight: '700' },
  pendingFooter: { flexDirection: 'row', marginTop: 14, backgroundColor: COLORS.borderLight, padding: 12, borderRadius: 4 },
  pendingFooterText: { flex: 1, fontSize: 12, color: COLORS.textSecondary, marginLeft: 8, lineHeight: 18 },

  infoRow: { flexDirection: 'row', alignItems: 'center', padding: 14 },
  infoLabel: { flex: 1, fontSize: 14, color: COLORS.textSecondary, marginLeft: 12 },
  infoValue: { fontSize: 14, fontWeight: '600', color: COLORS.text },
  infoValueWrap: { flex: 2, textAlign: 'right', flexWrap: 'wrap' },
  divider: { height: 1, backgroundColor: COLORS.border, marginLeft: 48 },
  dividerFull: { height: 1, backgroundColor: COLORS.border },
});
