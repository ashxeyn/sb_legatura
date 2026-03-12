// @ts-nocheck
import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  FlatList,
  Image,
  TextInput,
  Modal,
  ActivityIndicator,
  ScrollView,
  Alert,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather, Ionicons } from '@expo/vector-icons';
import { api_config, api_request } from '../../config/api';
import { storage_service } from '../../utils/storage';
import { useContractorAuth } from '../../hooks/useContractorAuth';

const COLORS = {
  primary: '#FB8C00',
  background: '#FAFBFC',
  surface: '#FFFFFF',
  text: '#0F172A',
  textSecondary: '#6B7280',
  border: '#E6E9EE',
  error: '#EF4444',
};

export default function Members({ userData, onClose }: { userData?: any; onClose?: () => void }) {
  const insets = useSafeAreaInsets();
  const [members, setMembers] = useState([]);
  const [filteredMembers, setFilteredMembers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [modalVisible, setModalVisible] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [viewModalVisible, setViewModalVisible] = useState(false);
  const [selectedMember, setSelectedMember] = useState<any | null>(null);
  
  // Search and filter states
  const [searchQuery, setSearchQuery] = useState('');
  const [roleFilter, setRoleFilter] = useState('all');
  const [statusFilter, setStatusFilter] = useState('all');
  const [showFilters, setShowFilters] = useState(false);
  const [ownerSearchQuery, setOwnerSearchQuery] = useState('');
  const [ownerSearchResults, setOwnerSearchResults] = useState([]);
  const [searchingOwners, setSearchingOwners] = useState(false);
  const [selectedOwner, setSelectedOwner] = useState<any | null>(null);

  // Authorization hook - member management is owner-only; all active members can view
  const { canManageMembers, canViewMembers, isLoading: authLoading, role } = useContractorAuth();
  const isOwnerRole = role === 'owner';
  // Representatives are view-only on the members page (same as other staff)
  const canManageMemberActions = canManageMembers && isOwnerRole;
  const canAddMembers = canManageMemberActions;

  const canManageTargetMember = (member: any): boolean => {
    // Only owner can edit members.
    return isOwnerRole && !!member;
  };

  const canDeleteTargetMember = (member: any): boolean => {
    return isOwnerRole && !!member;
  };

  const [form, setForm] = useState({
    role: 'others',
    role_other: '',
  });

  const isMemberActive = (value: any) => {
    if (typeof value === 'boolean') return value;
    if (typeof value === 'number') return value === 1;
    if (typeof value === 'string') {
      const normalized = value.trim().toLowerCase();
      return normalized === '1' || normalized === 'true' || normalized === 'active' || normalized === 'yes';
    }
    return false;
  };

  const resolveMemberActive = (member: any) => {
    const rawStatus =
      member?.is_active ??
      member?.isActive ??
      member?.active ??
      member?.member_active ??
      member?.status;

    // Handle text status values like "Active" / "Inactive"
    if (typeof rawStatus === 'string') {
      const v = rawStatus.trim().toLowerCase();
      if (v === 'inactive' || v === 'disabled' || v === '0' || v === 'false' || v === 'no') {
        return false;
      }
      if (v === 'active' || v === 'enabled' || v === '1' || v === 'true' || v === 'yes') {
        return true;
      }
    }

    return isMemberActive(rawStatus);
  };

  // Define functions before any early returns
  const fetchMembers = async () => {
    setLoading(true);
    try {
      // Get user_id from stored user data
      const storedUser = await storage_service.get_user_data();
      const userId = storedUser?.user_id || storedUser?.id;
      
      if (!userId) {
        console.warn('No user_id found');
        return;
      }
      
      const endpoint = `${api_config.endpoints.contractor_members.list}?user_id=${userId}`;
      const res = await api_request(endpoint, { method: 'GET' });
      if (res.success && res.data) {
        const rawMembers = Array.isArray(res.data?.data)
          ? res.data.data
          : Array.isArray(res.data)
            ? res.data
            : [];

        const membersData = rawMembers.map((m: any) => ({
          ...m,
          is_active: resolveMemberActive(m),
        }));

        console.log('Fetched members:', membersData.map(m => ({
          id: m.id,
          name: m.first_name,
          is_active_raw: m.isActive ?? m.active ?? m.status,
          is_active_normalized: m.is_active,
        })));

        setMembers(membersData);
        setFilteredMembers(membersData);
      }
    } catch (e) {
      console.warn('Failed fetching members', e);
    } finally {
      setLoading(false);
    }
  };

  const applyFilters = () => {
    let filtered = [...members];

    // Apply search filter
    if (searchQuery.trim()) {
      const query = searchQuery.toLowerCase();
      filtered = filtered.filter(member => {
        const fullName = `${member.first_name || ''} ${member.middle_name || ''} ${member.last_name || ''}`.toLowerCase();
        const email = (member.email || '').toLowerCase();
        const phone = (member.phone || member.phone_number || '').toLowerCase();
        const username = (member.username || '').toLowerCase();
        
        return fullName.includes(query) || 
               email.includes(query) || 
               phone.includes(query) ||
               username.includes(query);
      });
    }

    // Apply role filter
    if (roleFilter !== 'all') {
      filtered = filtered.filter(member => member.role === roleFilter);
    }

    // Apply status filter
    if (statusFilter !== 'all') {
      const isActive = statusFilter === 'active';
      filtered = filtered.filter(member => isMemberActive(member.is_active) === isActive);
    }

    setFilteredMembers(filtered);
  };

  useEffect(() => {
    // Only fetch members if authorized
    if (!authLoading && canViewMembers) {
      fetchMembers();
    }
  }, [authLoading, canViewMembers]);

  // Apply filters whenever search query, role filter, or status filter changes
  useEffect(() => {
    applyFilters();
  }, [searchQuery, roleFilter, statusFilter, members]);

  // Authorization guard - block unauthorized access
  if (authLoading) {
    return (
      <View style={[styles.container, { paddingTop: insets.top }]}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Checking permissions...</Text>
        </View>
      </View>
    );
  }

  if (!canViewMembers) {
    return (
      <View style={[styles.container, { paddingTop: insets.top }]}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={onClose} style={styles.headerBack}>
            <Feather name="arrow-left" size={22} color={COLORS.text} />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Members</Text>
          <View style={{ width: 40 }} />
        </View>

        {/* Unauthorized Access Message */}
        <View style={styles.unauthorizedContainer}>
          <View style={styles.unauthorizedIcon}>
            <Feather name="lock" size={48} color={COLORS.error} />
          </View>
          <Text style={styles.unauthorizedTitle}>Access Restricted</Text>
          <Text style={styles.unauthorizedMessage}>
            You don't have permission to view contractor members.
            {'\n\n'}
            Please contact your company owner.
          </Text>
          {role && (
            <Text style={styles.unauthorizedRole}>
              Your current role: <Text style={styles.roleHighlight}>{role}</Text>
            </Text>
          )}
          <TouchableOpacity
            style={styles.backButton}
            onPress={onClose}
            activeOpacity={0.7}
          >
            <Text style={styles.backButtonText}>Go Back</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  const clearFilters = () => {
    setSearchQuery('');
    setRoleFilter('all');
    setStatusFilter('all');
  };

  const renderMember = ({ item }) => (
    <View style={styles.memberCard}>
      {
        (() => {
          let uri = item.member_profile_pic || item.profile_pic || item.avatar;
          // If backend returned an updated_at timestamp, append it as a cache buster for storage images
          if (uri && item.updated_at && !String(uri).startsWith('http') && !String(uri).startsWith('data:') && !String(uri).startsWith('file:') && !String(uri).startsWith('content:')) {
            const ts = new Date(item.updated_at).getTime();
            uri = `${uri}?t=${ts}`;
          }
          return <MemberImage key={uri} uri={uri} style={styles.avatar} fallback={require('../../../assets/images/pictures/members_default.png')} />;
        })()
      }

      <View style={styles.memberInfo}>
        <Text style={styles.memberName}>{`${item.first_name || ''}${item.middle_name ? ' ' + item.middle_name : ''}${item.last_name ? ' ' + item.last_name : ''}`.trim()}</Text>
        <View style={styles.memberMetaRow}>
          <Text style={styles.memberUsername}>{`@${item.username || 'unknown'}`}</Text>

          <View style={styles.memberInlineActions}>
            <View style={[styles.statusBadge, { backgroundColor: isMemberActive(item.is_active) ? '#DCFCE7' : '#F3F4F6' }]}> 
              <Text style={[styles.statusText, { color: isMemberActive(item.is_active) ? '#16A34A' : '#6B7280' }]}>
                {isMemberActive(item.is_active) ? 'Active' : 'Inactive'}
              </Text>
            </View>

            <TouchableOpacity
              style={styles.inlineActionBtn}
              accessibilityLabel="view"
              onPress={() => openView(item)}
            >
              <Ionicons name="eye-outline" size={17} color={COLORS.primary} />
            </TouchableOpacity>

            {/* Owner/representative actions are further restricted per target role. */}
            {canManageTargetMember(item) && (
              <>
                <TouchableOpacity style={styles.inlineActionBtn} accessibilityLabel="edit" onPress={() => openEdit(item)}>
                  <Ionicons name="pencil" size={16} color={COLORS.primary} />
                </TouchableOpacity>
                {canDeleteTargetMember(item) && (
                  <TouchableOpacity style={styles.inlineActionBtn} accessibilityLabel="remove" onPress={() => confirmDelete(item)}>
                    <Ionicons name="trash" size={16} color="#E53935" />
                  </TouchableOpacity>
                )}
              </>
            )}
          </View>
        </View>
        <Text style={styles.memberRole}>{item.role ? (item.role.charAt(0).toUpperCase() + item.role.slice(1)) : item.role}</Text>
      </View>
    </View>
  );

  const openCreate = () => {
    if (!canAddMembers) {
      Alert.alert('Access Restricted', 'Only the company owner can invite new members.');
      return;
    }

    setForm({
      role: 'others',
      role_other: '',
    });
    setOwnerSearchQuery('');
    setOwnerSearchResults([]);
    setSelectedOwner(null);
    setEditingId(null);
    setModalVisible(true);
  };

  const openView = (item) => {
    setSelectedMember(item);
    setViewModalVisible(true);
  };

  const closeView = () => {
    setViewModalVisible(false);
    setSelectedMember(null);
  };

  const openEdit = (item) => {
    if (!canManageTargetMember(item)) {
      Alert.alert('Access Restricted', 'You cannot edit this member.');
      return;
    }

    setEditingId(item.id || item.contractor_user_id || null);
    
    setForm({
      role: item.role || 'others',
      role_other: item.role_other || '',
      _originalItem: item,
    });
    setModalVisible(true);
  };

  const searchVerifiedOwners = async (query: string) => {
    setOwnerSearchQuery(query);
    if (query.trim().length < 2) {
      setOwnerSearchResults([]);
      return;
    }

    try {
      setSearchingOwners(true);
      const storedUser = await storage_service.get_user_data();
      const userId = storedUser?.user_id || storedUser?.id;
      const endpoint = `${api_config.endpoints.contractor_members.search_owners}?user_id=${userId}&q=${encodeURIComponent(query.trim())}`;
      const res = await api_request(endpoint, { method: 'GET' });

      if (res.success) {
        setOwnerSearchResults(Array.isArray(res.data?.data) ? res.data.data : []);
      } else {
        setOwnerSearchResults([]);
      }
    } catch (e) {
      console.warn('Search verified owners failed', e);
      setOwnerSearchResults([]);
    } finally {
      setSearchingOwners(false);
    }
  };

  const confirmDelete = (item) => {
    if (!canDeleteTargetMember(item)) {
      Alert.alert('Access Restricted', 'You cannot remove this member.');
      return;
    }

    const id = item.id || item.contractor_user_id;
    Alert.alert('Delete member', 'Are you sure you want to delete this member?', [
      { text: 'Cancel', style: 'cancel' },
      { text: 'Delete', style: 'destructive', onPress: () => deleteMember(id) }
    ]);
  };

  const deleteMember = async (id) => {
    try {
      setLoading(true);
      // Get user_id from stored user data
      const storedUser = await storage_service.get_user_data();
      const userId = storedUser?.user_id || storedUser?.id;
      
      const endpoint = `${api_config.endpoints.contractor_members.delete(id)}?user_id=${userId}`;
      const res = await api_request(endpoint, { method: 'DELETE' });
      if (res.success) {
        fetchMembers();
        Alert.alert('Deleted', 'Member deleted');
      } else {
        Alert.alert('Error', res.message || 'Failed to delete');
      }
    } catch (e) {
      console.warn('Delete failed', e);
      Alert.alert('Error', 'Network error');
    } finally {
      setLoading(false);
    }
  };

  const submitForm = async () => {
    const isEditing = !!editingId;

    if (!form.role) {
      Alert.alert('Validation', 'Please select a role.');
      return;
    }
    if (form.role === 'others' && !String(form.role_other || '').trim()) {
      Alert.alert('Validation', 'Please provide the custom role name.');
      return;
    }
    if (!isEditing && !selectedOwner) {
      Alert.alert('Validation', 'Please search and select a verified property owner to invite.');
      return;
    }

    setSubmitting(true);
    try {
      // Get user_id from stored user data
      const storedUser = await storage_service.get_user_data();
      const userId = storedUser?.user_id || storedUser?.id;
      
      if (!userId) {
        Alert.alert('Error', 'User not authenticated');
        setSubmitting(false);
        return;
      }

      const payload: any = {
        role: form.role,
      };
      if (form.role === 'others') {
        payload.role_other = String(form.role_other || '').trim();
      }

      if (!isEditing) {
        payload.owner_id = selectedOwner.owner_id;
      }

      const res = await api_request(
        `${isEditing ? api_config.endpoints.contractor_members.update(editingId) : api_config.endpoints.contractor_members.create}?user_id=${userId}`,
        {
          method: isEditing ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(payload),
        }
      );

      if (res.success) {
        setModalVisible(false);
        setEditingId(null);
        setSelectedOwner(null);
        setOwnerSearchQuery('');
        setOwnerSearchResults([]);
        
        fetchMembers();

        if (isEditing) {
          Alert.alert('Changes Saved', res.message || 'Member role updated successfully.');
        } else {
          Alert.alert('Invitation Sent', res.message || 'Invitation sent successfully.');
        }
      } else {
        Alert.alert('Error', res.message || (isEditing ? 'Failed to update member' : 'Failed to send invitation'));
      }
    } catch (e) {
      console.error(e);
      Alert.alert('Error', 'Network error');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <View style={[styles.container, { paddingTop: 12 }]}> 
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.headerBack}>
          <Feather name="arrow-left" size={20} color={COLORS.text} />
        </TouchableOpacity>

        <Text style={styles.headerTitle}>Members</Text>
        <View style={styles.headerSpacer} />
      </View>

      <View style={styles.searchRow}>
        <View style={{ flex: 1, marginRight: 8 }}>
          <TextInput 
            placeholder="Search members" 
            placeholderTextColor="#9AA0A6" 
            style={styles.searchInput}
            value={searchQuery}
            onChangeText={setSearchQuery}
          />
        </View>
        <TouchableOpacity 
          style={[styles.filterBtn, (roleFilter !== 'all' || statusFilter !== 'all') && styles.filterBtnActive]} 
          onPress={() => setShowFilters(!showFilters)}
          accessibilityLabel="toggle-filters"
        >
          <Feather name="filter" size={18} color={(roleFilter !== 'all' || statusFilter !== 'all') ? '#fff' : COLORS.text} />
        </TouchableOpacity>
        {canAddMembers && (
          <TouchableOpacity 
            style={[styles.newUserBtn, styles.newUserBtnWithIcon]} 
            accessibilityLabel="new-member" 
            onPress={openCreate}
          >
            <Feather name="plus" size={16} color="#fff" style={{ marginRight: 8 }} />
            <Text style={styles.newUserText}>Add</Text>
          </TouchableOpacity>
        )}
      </View>

      {!canManageMemberActions && (
        <View style={styles.readOnlyBanner}>
          <Ionicons name="information-circle-outline" size={16} color={COLORS.info} />
          <Text style={styles.readOnlyBannerText}>
            View-only access. Only owner/representative can manage members, and only owner can add new members.
          </Text>
        </View>
      )}



      {/* Filter Panel */}
      {showFilters && (
        <View style={styles.filterPanel}>
          <View style={styles.filterHeader}>
            <Text style={styles.filterTitle}>Filters</Text>
            <TouchableOpacity onPress={clearFilters}>
              <Text style={styles.clearFiltersText}>Clear All</Text>
            </TouchableOpacity>
          </View>

          <View style={styles.filterSection}>
            <Text style={styles.filterLabel}>Role</Text>
            <View style={styles.filterChips}>
              {[
                { label: 'All', value: 'all' },
                { label: 'Representative', value: 'representative' },
                { label: 'Owner', value: 'owner' },
                { label: 'Engineer', value: 'engineer' },
                { label: 'Architect', value: 'architect' },
                { label: 'Others', value: 'others' },
              ].map(({ label, value }) => (
                <TouchableOpacity
                  key={value}
                  style={[
                    styles.filterChip,
                    roleFilter === value && styles.filterChipActive
                  ]}
                  onPress={() => setRoleFilter(value)}
                >
                  <Text style={[
                    styles.filterChipText,
                    roleFilter === value && styles.filterChipTextActive
                  ]}>
                    {label}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>

          <View style={styles.filterSection}>
            <Text style={styles.filterLabel}>Status</Text>
            <View style={styles.filterChips}>
              {[
                { label: 'All', value: 'all' },
                { label: 'Active', value: 'active' },
                { label: 'Inactive', value: 'inactive' },
              ].map(({ label, value }) => (
                <TouchableOpacity
                  key={value}
                  style={[
                    styles.filterChip,
                    statusFilter === value && styles.filterChipActive
                  ]}
                  onPress={() => setStatusFilter(value)}
                >
                  <Text style={[
                    styles.filterChipText,
                    statusFilter === value && styles.filterChipTextActive
                  ]}>
                    {label}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>

          <View style={styles.filterResults}>
            <Text style={styles.filterResultsText}>
              Showing {filteredMembers.length} of {members.length} members
            </Text>
          </View>
        </View>
      )}

      {loading ? (
        <View style={{ padding: 16 }}>
          <ActivityIndicator />
        </View>
      ) : filteredMembers.length === 0 ? (
        <View style={styles.emptyState}>
          <Feather name="users" size={48} color={COLORS.border} />
          <Text style={styles.emptyStateText}>
            {members.length === 0 ? 'No members yet. Start by inviting verified property owners.' : 'No members match your filters'}
          </Text>
          {members.length > 0 && (
            <TouchableOpacity onPress={clearFilters} style={styles.clearFiltersButton}>
              <Text style={styles.clearFiltersButtonText}>Clear Filters</Text>
            </TouchableOpacity>
          )}
        </View>
      ) : (
        <FlatList
          data={filteredMembers}
          keyExtractor={(item) => (item.user_id ? String(item.user_id) : item.id)}
          contentContainerStyle={{ padding: 8 }}
          renderItem={renderMember}
          ItemSeparatorComponent={() => <View style={{ height: 6 }} />}
        />
      )}

      <Modal visible={modalVisible} animationType="slide" transparent>
        <View style={{ flex: 1, backgroundColor: 'rgba(0,0,0,0.4)', justifyContent: 'center' }}>
          <View style={{ margin: 20, backgroundColor: '#fff', borderRadius: 8, padding: 16 }}>
            <ScrollView>
              <Text style={{ fontWeight: '700', fontSize: 16, marginBottom: 8 }}>{editingId ? 'Edit Member' : 'Invite Member'}</Text>

              {!editingId && (
                <View style={{ marginBottom: 12 }}>
                  <Text style={styles.sectionHeader}>Invite Verified Property Owner</Text>
                  <TextInput
                    placeholder="Search by name, email, or username"
                    value={ownerSearchQuery}
                    onChangeText={searchVerifiedOwners}
                    style={styles.input}
                  />
                  {searchingOwners && <ActivityIndicator style={{ marginTop: 8 }} />}
                  {!searchingOwners && ownerSearchQuery.trim().length >= 2 && ownerSearchResults.length === 0 && (
                    <Text style={styles.noteSecondary}>No verified owners found.</Text>
                  )}
                  {ownerSearchResults.length > 0 && (
                    <View style={styles.ownerResultList}>
                      {ownerSearchResults.map((owner: any) => {
                        const isSelected = selectedOwner?.owner_id === owner.owner_id;
                        return (
                          <TouchableOpacity
                            key={owner.owner_id}
                            style={[styles.ownerResultItem, isSelected && styles.ownerResultItemSelected]}
                            onPress={() => setSelectedOwner(owner)}
                          >
                            <Text style={styles.ownerResultName}>
                              {`${owner.first_name || ''} ${owner.middle_name || ''} ${owner.last_name || ''}`.replace(/\s+/g, ' ').trim()}
                            </Text>
                            <Text style={styles.ownerResultSub}>{owner.email || owner.username}</Text>
                          </TouchableOpacity>
                        );
                      })}
                    </View>
                  )}
                </View>
              )}

              {selectedOwner && !editingId && (
                <View style={styles.selectedOwnerBox}>
                  <Text style={styles.selectedOwnerTitle}>Selected Invitee</Text>
                  <Text style={styles.selectedOwnerText}>
                    {`${selectedOwner.first_name || ''} ${selectedOwner.middle_name || ''} ${selectedOwner.last_name || ''}`.replace(/\s+/g, ' ').trim()}
                  </Text>
                  <Text style={styles.selectedOwnerSub}>{selectedOwner.email || selectedOwner.username}</Text>
                </View>
              )}

              {/* Role picker implemented as simple inline options to avoid extra deps */}
              <View style={{ marginBottom: 8 }}>
                <Text style={styles.sectionHeader}>Role</Text>
                <TouchableOpacity onPress={() => setForm({ ...form, showRoleOptions: !form.showRoleOptions })} style={[styles.input, { justifyContent: 'space-between', flexDirection: 'row', alignItems: 'center' }] }>
                  <Text style={{ color: COLORS.text }}>{form.role ? (form.role.charAt(0).toUpperCase() + form.role.slice(1)) : 'Select role'}</Text>
                  <Ionicons name="chevron-down" size={18} color={COLORS.textSecondary} />
                </TouchableOpacity>
                {form.showRoleOptions && (
                  <View style={{ marginTop: 6, borderWidth: 1, borderColor: COLORS.border, borderRadius: 6, overflow: 'hidden' }}>
                    {['representative', 'manager', 'engineer', 'architect', 'others']
                      .map((r) => (
                      <TouchableOpacity key={r} onPress={() => setForm({ ...form, role: r, showRoleOptions: false })} style={{ padding: 10, backgroundColor: form.role === r ? '#F5F7FA' : '#FFF' }}>
                        <Text style={{ color: COLORS.text }}>{r.charAt(0).toUpperCase() + r.slice(1)}</Text>
                      </TouchableOpacity>
                    ))}
                  </View>
                )}
              </View>


              {form.role === 'others' && (
                <TextInput placeholder="Role (other)" value={form.role_other} onChangeText={(t) => setForm({ ...form, role_other: t })} style={styles.input} />
              )}

              <View style={styles.noteBox}>
                <View style={{ flexDirection: 'row', alignItems: 'flex-start' }}>
                  <Ionicons name="information-circle-outline" size={20} color="#1E90FF" style={{ marginRight: 8 }} />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.notePrimary}>Invitations are sent only to verified property owners.</Text>
                    <Text style={styles.noteSecondary}>They will appear as pending members until they accept the invitation.</Text>
                  </View>
                </View>
              </View>

              {/* image picker moved to avatar tap */}

              <View style={{ flexDirection: 'row', justifyContent: 'flex-end', marginTop: 12 }}>
                <TouchableOpacity onPress={() => { setModalVisible(false); setEditingId(null); }} style={[styles.newUserBtn, { backgroundColor: '#E6E9EE', marginRight: 8 }] }>
                  <Text style={{ color: '#0F172A', fontWeight: '700' }}>Cancel</Text>
                </TouchableOpacity>
                <TouchableOpacity onPress={submitForm} style={styles.newUserBtn} accessibilityLabel="submit-member">
                  {submitting ? (
                    <ActivityIndicator color="#fff" />
                  ) : (
                    <Text style={styles.newUserText}>{editingId ? 'Save Changes' : 'Send Invitation'}</Text>
                  )}
                </TouchableOpacity>
              </View>
            </ScrollView>
          </View>
        </View>
      </Modal>

      <Modal visible={viewModalVisible} animationType="fade" transparent>
        <View style={styles.viewModalOverlay}>
          <View style={styles.viewModalCard}>
            <Text style={styles.viewModalTitle}>Member Details</Text>

            <View style={styles.viewAvatarWrap}>
              <MemberImage
                key={selectedMember?.profile_pic || selectedMember?.member_profile_pic || selectedMember?.avatar}
                uri={selectedMember?.member_profile_pic || selectedMember?.profile_pic || selectedMember?.avatar}
                style={styles.viewAvatar}
                fallback={require('../../../assets/images/pictures/members_default.png')}
              />
            </View>

            <View style={styles.viewFieldRow}>
              <Text style={styles.viewFieldLabel}>Name</Text>
              <Text style={styles.viewFieldValue}>
                {`${selectedMember?.first_name || ''} ${selectedMember?.middle_name || ''} ${selectedMember?.last_name || ''}`.replace(/\s+/g, ' ').trim() || 'N/A'}
              </Text>
            </View>

            <View style={styles.viewFieldRow}>
              <Text style={styles.viewFieldLabel}>Username</Text>
              <Text style={styles.viewFieldValue}>@{selectedMember?.username || 'unknown'}</Text>
            </View>

            <View style={styles.viewFieldRow}>
              <Text style={styles.viewFieldLabel}>Email</Text>
              <Text style={styles.viewFieldValue}>{selectedMember?.email || 'N/A'}</Text>
            </View>

            <View style={styles.viewFieldRow}>
              <Text style={styles.viewFieldLabel}>Role</Text>
              <Text style={styles.viewFieldValue}>
                {selectedMember?.role ? (selectedMember.role.charAt(0).toUpperCase() + selectedMember.role.slice(1)) : 'N/A'}
              </Text>
            </View>

            <View style={styles.viewFieldRow}>
              <Text style={styles.viewFieldLabel}>Status</Text>
              <Text style={styles.viewFieldValue}>{isMemberActive(selectedMember?.is_active) ? 'Active' : 'Inactive'}</Text>
            </View>

            <TouchableOpacity style={styles.viewCloseBtn} onPress={closeView}>
              <Text style={styles.viewCloseBtnText}>Close</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </View>
  );
}

function getProfileImageUrl(path?: string) {
  if (!path) return 'https://via.placeholder.com/64';
  if (path.startsWith('http') || path.startsWith('data:') || path.startsWith('file:') || path.startsWith('content:')) return path;
  // Backend stores images in `storage/` and api_config.base_url points to server root
  const fullUrl = `${api_config.base_url}/storage/${path}`;
  console.log('Profile image URL:', { path, fullUrl });
  return fullUrl;
}

function MemberImage({ uri, style, fallback }: { uri?: string | null; style?: any; fallback: any }) {
  const [errored, setErrored] = useState(false);
  React.useEffect(() => {
    // Reset error state when the source uri changes so a previously errored image
    // doesn't force the fallback to show for a new valid selection.
    setErrored(false);
  }, [uri]);

  console.log('MemberImage render:', { uri, errored, finalUrl: uri ? getProfileImageUrl(uri) : 'none' });

  if (!uri || errored) {
    return <Image source={fallback} style={style} />;
  }

  return (
    <Image
      source={{ uri: getProfileImageUrl(uri) }}
      style={style}
      onError={(error) => {
        console.log('Image load error:', { uri, finalUrl: getProfileImageUrl(uri), error });
        setErrored(true);
      }}
    />
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.surface,
    paddingTop: 0,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    height: 56,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderColor: COLORS.border,
  },
  backButton: {
    width: 40,
    alignItems: 'flex-start',
  },
  title: {
    flex: 1,
    textAlign: 'left',
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    paddingLeft: 4,
  },
  headerTitle: {
    flex: 1,
    textAlign: 'left',
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
  },
  headerBack: {
    width: 40,
    alignItems: 'flex-start',
    padding: 6,
    backgroundColor: 'transparent',
  },
  headerSpacer: {
    width: 40,
  },
  headerRight: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  searchRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 6,
    backgroundColor: COLORS.background,
  },
  searchInput: {
    height: 40,
    flex: 1,
    backgroundColor: '#F5F7FA',
    borderRadius: 8,
    paddingHorizontal: 10,
    borderWidth: 1,
    borderColor: COLORS.border,
    fontSize: 13,
  },
  filterBtn: {
    width: 40,
    height: 40,
    backgroundColor: '#F5F7FA',
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  filterBtnActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  filterPanel: {
    backgroundColor: COLORS.surface,
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  filterHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  filterTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.text,
  },
  clearFiltersText: {
    fontSize: 13,
    color: COLORS.primary,
    fontWeight: '600',
  },
  filterSection: {
    marginBottom: 12,
  },
  filterLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  filterChips: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  filterChip: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: '#F5F7FA',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  filterChipActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  filterChipText: {
    fontSize: 12,
    color: COLORS.text,
    fontWeight: '600',
  },
  filterChipTextActive: {
    color: '#fff',
  },
  filterResults: {
    marginTop: 8,
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  filterResultsText: {
    fontSize: 12,
    color: COLORS.textSecondary,
    textAlign: 'center',
  },
  emptyState: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 60,
  },
  emptyStateText: {
    fontSize: 15,
    color: COLORS.textSecondary,
    marginTop: 12,
    textAlign: 'center',
  },
  clearFiltersButton: {
    marginTop: 16,
    paddingHorizontal: 20,
    paddingVertical: 10,
    backgroundColor: COLORS.primary,
    borderRadius: 8,
  },
  clearFiltersButtonText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 14,
  },
  input: {
    height: 44,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 6,
    paddingHorizontal: 10,
    marginBottom: 8,
    backgroundColor: '#FFF'
  },
  newUserBtn: {
    backgroundColor: COLORS.primary,
    borderRadius: 8,
    height: 40,
    paddingHorizontal: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  readOnlyBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 8,
    backgroundColor: '#EFF6FF',
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  readOnlyBannerText: {
    marginLeft: 8,
    color: COLORS.textSecondary,
    fontSize: 12,
    flex: 1,
  },
  newUserBtnWithIcon: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
  },
  newUserText: {
    color: '#fff',
    fontWeight: '700',
    fontSize: 13,
  },
  content: {
    padding: 12,
  },
  subtitle: {
    color: COLORS.textSecondary,
    marginBottom: 8,
  },
  memberCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    paddingVertical: 10,
    paddingHorizontal: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  avatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    marginRight: 12,
    backgroundColor: '#F0F2F5',
  },
  avatarLargeWrap: {
    width: 120,
    height: 120,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarLarge: {
    width: 120,
    height: 120,
    borderRadius: 60,
    borderWidth: 6,
    borderColor: '#D1D5DB',
    backgroundColor: '#F0F2F5'
  },
  avatarPlaceholder: {
    width: 120,
    height: 120,
    borderRadius: 60,
    borderWidth: 6,
    borderColor: '#D1D5DB',
    backgroundColor: '#F0F2F5',
    alignItems: 'center',
    justifyContent: 'center'
  },
  avatarEditWrap: {
    position: 'absolute',
    right: 6,
    bottom: 6,
  },
  avatarEditBtn: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#0F172A',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: '#fff'
  },
  ownerResultList: {
    marginTop: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 8,
    maxHeight: 180,
    overflow: 'hidden',
  },
  ownerResultItem: {
    paddingVertical: 10,
    paddingHorizontal: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    backgroundColor: '#FFFFFF',
  },
  ownerResultItemSelected: {
    backgroundColor: '#FFF7ED',
  },
  ownerResultName: {
    color: COLORS.text,
    fontSize: 14,
    fontWeight: '600',
  },
  ownerResultSub: {
    marginTop: 2,
    color: COLORS.textSecondary,
    fontSize: 12,
  },
  selectedOwnerBox: {
    marginBottom: 10,
    borderRadius: 8,
    backgroundColor: '#F8FAFC',
    borderWidth: 1,
    borderColor: COLORS.border,
    padding: 10,
  },
  selectedOwnerTitle: {
    color: COLORS.textSecondary,
    fontSize: 12,
    marginBottom: 4,
  },
  selectedOwnerText: {
    color: COLORS.text,
    fontSize: 14,
    fontWeight: '700',
  },
  selectedOwnerSub: {
    color: COLORS.textSecondary,
    fontSize: 12,
    marginTop: 2,
  },
  noteBox: {
    backgroundColor: '#EFF8FF',
    padding: 10,
    borderRadius: 6,
    marginBottom: 8
  },
  notePrimary: {
    color: '#0F172A',
    fontWeight: '700',
    marginBottom: 4
  },
  noteSecondary: {
    color: COLORS.textSecondary,
    fontSize: 13
  },
  sectionHeader: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  memberInfo: {
    flex: 1,
    justifyContent: 'center',
  },
  memberName: {
    fontSize: 15,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 2,
  },
  memberRole: {
    color: COLORS.textSecondary,
    marginTop: 6,
    fontSize: 12,
  },
  memberMetaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: 2,
  },
  memberUsername: {
    color: COLORS.textSecondary,
    fontSize: 12,
    fontWeight: '600',
  },
  memberInlineActions: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  inlineActionBtn: {
    marginLeft: 8,
    paddingVertical: 3,
    paddingHorizontal: 3,
  },
  metaRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 6,
  },
  metaLabel: {
    marginLeft: 8,
    color: COLORS.text,
    fontWeight: '600',
    fontSize: 13,
  },
  metaValue: {
    marginLeft: 8,
    color: COLORS.text,
    fontSize: 13,
    fontWeight: '400',
  },
  metaCompact: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 4,
  },
  metaValueCompact: {
    marginLeft: 6,
    color: COLORS.textSecondary,
    fontSize: 12,
  },
  actionsRight: {
    display: 'none',
  },
  toggleContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 8,
  },
  toggleLabel: {
    fontSize: 11,
    fontWeight: '600',
    marginRight: 4,
  },
  iconBtn: {
    padding: 6,
    marginLeft: 8,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    marginRight: 8,
  },
  statusText: {
    fontSize: 11,
    fontWeight: '600',
  },
  activationSection: {
    marginBottom: 12,
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  activationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: '#F9FAFB',
    padding: 12,
    borderRadius: 8,
    marginTop: 8,
  },
  // Authorization guard styles
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  unauthorizedContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  unauthorizedIcon: {
    width: 96,
    height: 96,
    borderRadius: 48,
    backgroundColor: '#FEE2E2',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 24,
  },
  unauthorizedTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
    textAlign: 'center',
  },
  unauthorizedMessage: {
    fontSize: 15,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 16,
  },
  unauthorizedRole: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginBottom: 24,
  },
  roleHighlight: {
    fontWeight: '700',
    color: COLORS.text,
    textTransform: 'capitalize',
  },
  backButton: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 32,
    paddingVertical: 14,
    borderRadius: 8,
  },
  backButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  viewModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.45)',
    justifyContent: 'center',
    paddingHorizontal: 20,
  },
  viewModalCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
  },
  viewModalTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
  },
  viewAvatarWrap: {
    alignItems: 'center',
    marginBottom: 12,
  },
  viewAvatar: {
    width: 72,
    height: 72,
    borderRadius: 36,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  viewFieldRow: {
    marginBottom: 8,
  },
  viewFieldLabel: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginBottom: 2,
  },
  viewFieldValue: {
    fontSize: 14,
    color: COLORS.text,
    fontWeight: '600',
  },
  viewCloseBtn: {
    marginTop: 10,
    backgroundColor: COLORS.primary,
    borderRadius: 8,
    alignItems: 'center',
    paddingVertical: 10,
  },
  viewCloseBtnText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 14,
  },
});
