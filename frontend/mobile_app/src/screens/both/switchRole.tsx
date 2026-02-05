// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Alert,
  ActivityIndicator,
  TextInput,
} from 'react-native';
import { StatusBar, Platform } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import { role_service } from '../../services/role_service';

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

export default function SwitchRoleScreen({ onBack, onRoleChanged, onStartAddRole, userData }: SwitchRoleScreenProps) {
  const insets = useSafeAreaInsets();
  const [loading, setLoading] = useState(true);
  const [switching, setSwitching] = useState(false);
  const [currentRole, setCurrentRole] = useState<'contractor' | 'owner' | null>(null);
  const [canSwitchRoles, setCanSwitchRoles] = useState(false);
  // Registration UI moved to a dedicated screen. Local form states removed.

  const normalizeRole = (val: any): 'contractor' | 'owner' | null => {
    if (val === null || val === undefined) return null;
    const v = String(val).toLowerCase().trim();
    if (v === 'contractor') return 'contractor';
    if (v === 'owner' || v === 'property_owner' || v === 'property owner') return 'owner';
    return null;
  };

  // Get status bar height
  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  useEffect(() => {
    loadCurrentRole();
  }, []);

  const loadCurrentRole = async () => {
    try {
      setLoading(true);
      const response = await role_service.get_current_role();

      // Log exactly what this component receives from the service
      console.log('Component received:', response);

      if (response.success) {
        const roleValue = (response as any).current_role
          || (response as any).data?.current_role
          || (response as any).user_type;

        const canSwitch = ((response as any).can_switch_roles
          ?? (response as any).data?.can_switch_roles
          ?? false) as boolean;

        const normalized = normalizeRole(roleValue);
        setCurrentRole(normalized);
        setCanSwitchRoles(!!canSwitch);
      } else {
        Alert.alert('Error', 'Failed to load current role information');
      }
    } catch (error) {
      console.error('Load role error:', error);
      Alert.alert('Error', 'An error occurred while loading role information');
    } finally {
      setLoading(false);
    }
  };

  const handleSwitchRole = async (targetRole: 'contractor' | 'owner') => {
    if (switching) return;

    const roleLabel = targetRole === 'contractor' ? 'Contractor' : 'Property Owner';

    Alert.alert(
      `Switch to ${roleLabel}`,
      `Would you like to switch to the ${roleLabel} dashboard?`,
      [
        {
          text: 'Cancel',
          style: 'cancel',
        },
        {
          text: 'Switch',
          onPress: async () => {
            setSwitching(true);
            try {
              const response = await role_service.switch_role(targetRole);

              if (response.success) {
                setCurrentRole(targetRole);
                // Re-fetch from server to ensure consistency
                await loadCurrentRole();
                Alert.alert(
                  'Success',
                  response.message || `Successfully switched to ${roleLabel} role`,
                  [
                    {
                      text: 'OK',
                      onPress: () => {
                        // Navigate to appropriate dashboard
                        onRoleChanged();
                        onBack();
                      },
                    },
                  ]
                );
              } else {
                Alert.alert('Error', response.message || 'Failed to switch role');
              }
            } catch (error) {
              console.error('Switch role error:', error);
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
    } else {
      Alert.alert('Info', 'Registration form will open on a dedicated screen.');
    }
  };

  if (loading) {
    return (
      <View style={[styles.container, { paddingTop: statusBarHeight }]}>
        <StatusBar hidden={true} />
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
        <Text style={styles.headerTitle}>Switch Role</Text>
        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {/* Current Role Card */}
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
                  {currentRole === 'contractor' ? 'Contractor' : currentRole === 'owner' ? 'Property Owner' : 'Role Unknown'}
                </Text>
                <Text style={styles.roleDescription}>
                  {currentRole === 'contractor'
                    ? 'Bid on projects and manage contracts'
                    : currentRole === 'owner'
                    ? 'Post projects and manage properties'
                    : 'We could not determine your role yet'}
                </Text>
              </View>
              <View style={styles.activeBadge}>
                <Text style={styles.activeBadgeText}>ACTIVE</Text>
              </View>
            </View>
          </View>
        </View>

        {/* Switch Role Section */}
        {canSwitchRoles ? (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>SWITCH TO</Text>
            <View style={styles.card}>
              <TouchableOpacity
                style={styles.switchRoleCard}
                onPress={() => handleSwitchRole(currentRole === 'contractor' ? 'owner' : 'contractor')}
                disabled={switching}
                activeOpacity={0.7}
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
                  <Text style={styles.roleDescription}>
                    {currentRole === 'contractor'
                      ? 'Post projects and manage properties'
                      : 'Bid on projects and manage contracts'}
                  </Text>
                </View>
                {switching ? (
                  <ActivityIndicator color="#EC7E00" />
                ) : (
                  <MaterialIcons name="chevron-right" size={28} color="#CCCCCC" />
                )}
              </TouchableOpacity>
            </View>

            {/* Info Box */}
            <View style={styles.infoBox}>
              <Ionicons name="information-circle-outline" size={20} color="#1877F2" />
              <Text style={styles.infoText}>
                Switching roles will change your dashboard view. Your account data for both roles will remain intact.
              </Text>
            </View>
          </View>
        ) : (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>ADD ANOTHER ROLE</Text>
            <View style={styles.card}>
              <TouchableOpacity
                style={styles.addRoleCard}
                onPress={handleAddRole}
                activeOpacity={0.7}
              >
                {/** Compute next role/label dynamically from currentRole */}


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
                  <Text style={styles.roleDescription}>
                    Register as {currentRole === 'contractor' ? 'a property owner' : 'a contractor'} to access both roles
                  </Text>
                </View>
                <View style={styles.addIconContainer}>
                  <MaterialIcons name="add-circle" size={28} color="#42B883" />
                </View>
              </TouchableOpacity>
            </View>

            {/* Info Box */}
            <View style={styles.infoBoxWarning}>
              <Ionicons name="alert-circle-outline" size={20} color="#F39C12" />
              <Text style={styles.infoText}>
                You currently only have one role. Add another role to switch between contractor and property owner views.
              </Text>
            </View>

            {/* Registration form is now a separate screen; no inline form here. */}
          </View>
        )}

        {/* User Info */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>ACCOUNT INFORMATION</Text>
          <View style={styles.card}>
            <View style={styles.infoRow}>
              <Ionicons name="person-outline" size={20} color="#666666" />
              <Text style={styles.infoLabel}>Username</Text>
              <Text style={styles.infoValue}>{userData?.username || 'N/A'}</Text>
            </View>
            <View style={styles.divider} />
            <View style={styles.infoRow}>
              <Ionicons name="mail-outline" size={20} color="#666666" />
              <Text style={styles.infoLabel}>Email</Text>
              <Text style={styles.infoValue}>{userData?.email || 'N/A'}</Text>
            </View>
            <View style={styles.divider} />
            <View style={styles.infoRow}>
              <Ionicons name="shield-checkmark-outline" size={20} color="#666666" />
              <Text style={styles.infoLabel}>Account Type</Text>
              <Text style={styles.infoValue}>
                {userData?.user_type === 'both' ? 'Dual Role' : 'Single Role'}
              </Text>
            </View>
          </View>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 16,
    fontSize: 16,
    color: '#666666',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingVertical: 16,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E5E5',
  },
  backButton: {
    padding: 4,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333333',
  },
  headerSpacer: {
    width: 32,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: 40,
  },
  section: {
    marginTop: 24,
    paddingHorizontal: 16,
  },
  sectionTitle: {
    fontSize: 12,
    fontWeight: '600',
    color: '#999999',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 12,
    marginLeft: 4,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 3,
  },
  currentRoleCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 20,
  },
  switchRoleCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 20,
  },
  addRoleCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 20,
  },
  roleIconContainer: {
    width: 60,
    height: 60,
    borderRadius: 30,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  contractorBg: {
    backgroundColor: '#1877F2',
  },
  ownerBg: {
    backgroundColor: '#EC7E00',
  },
  roleInfo: {
    flex: 1,
  },
  roleName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333333',
    marginBottom: 4,
  },
  roleDescription: {
    fontSize: 14,
    color: '#666666',
    lineHeight: 20,
  },
  activeBadge: {
    backgroundColor: '#42B883',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 12,
  },
  activeBadgeText: {
    fontSize: 11,
    fontWeight: 'bold',
    color: '#FFFFFF',
    letterSpacing: 0.5,
  },
  addIconContainer: {
    marginLeft: 8,
  },
  infoBox: {
    flexDirection: 'row',
    backgroundColor: '#EBF5FF',
    padding: 16,
    borderRadius: 12,
    marginTop: 16,
    alignItems: 'flex-start',
  },
  infoBoxWarning: {
    flexDirection: 'row',
    backgroundColor: '#FFF8E5',
    padding: 16,
    borderRadius: 12,
    marginTop: 16,
    alignItems: 'flex-start',
  },
  infoText: {
    flex: 1,
    fontSize: 13,
    color: '#666666',
    lineHeight: 20,
    marginLeft: 12,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
  },
  infoLabel: {
    flex: 1,
    fontSize: 15,
    color: '#666666',
    marginLeft: 12,
  },
  infoValue: {
    fontSize: 15,
    fontWeight: '500',
    color: '#333333',
  },
  divider: {
    height: 1,
    backgroundColor: '#F0F0F0',
    marginLeft: 48,
  },
  // Form input styles removed as form is on another screen
});




