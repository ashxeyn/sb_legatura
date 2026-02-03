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
import { Feather, Ionicons, MaterialIcons } from '@expo/vector-icons';
import { api_config, api_request } from '../../config/api';
import { storage_service } from '../../utils/storage';
import * as ImagePicker from 'expo-image-picker';

const COLORS = {
  primary: '#FB8C00',
  background: '#FAFBFC',
  surface: '#FFFFFF',
  text: '#0F172A',
  textSecondary: '#6B7280',
  border: '#E6E9EE',
};

const mockMembers = [
  {
    id: '1',
    name: 'Olivia Faith',
    role: 'Secretary / Contact person',
    phone: '+63 912 345 6789',
    email: 'olivia_faith@gmail.com',
    avatar: 'https://randomuser.me/api/portraits/women/44.jpg',
  },
  { id: '2', name: 'Juan Dela Cruz', role: 'Project Manager', phone: '+63 912 111 2222', email: 'juan@example.com', avatar: '' },
];

export default function Members({ userData, onClose }: { userData?: any; onClose?: () => void }) {
  const insets = useSafeAreaInsets();
  const [members, setMembers] = useState(mockMembers);
  const [loading, setLoading] = useState(false);
  const [modalVisible, setModalVisible] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);

  const [form, setForm] = useState({
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    phone_number: '',
    role: 'manager',
    username: '',
    password: '',
    password_confirm: '',
    role_other: '',
    profile_pic: null,
    _pickedFile: null,
  });

  useEffect(() => {
    fetchMembers();
  }, []);

  const fetchMembers = async () => {
    setLoading(true);
    try {
      const res = await api_request(api_config.endpoints.contractor_members.list, { method: 'GET' });
      if (res.success && res.data) {
        setMembers(res.data.data || res.data);
      }
    } catch (e) {
      console.warn('Failed fetching members', e);
    } finally {
      setLoading(false);
    }
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
          return <MemberImage uri={uri} style={styles.avatar} fallback={require('../../../assets/images/pictures/members_default.png')} />;
        })()
      }

      <View style={styles.memberInfo}>
        <Text style={styles.memberName}>{`${item.first_name || ''}${item.middle_name ? ' ' + item.middle_name : ''}${item.last_name ? ' ' + item.last_name : ''}`.trim()}</Text>
        <Text style={styles.memberRole}>{item.role ? (item.role.charAt(0).toUpperCase() + item.role.slice(1)) : item.role}</Text>

        <View style={styles.metaCompact}>
          <MaterialIcons name="chat-bubble-outline" size={13} color={COLORS.primary} />
          <Text style={styles.metaValueCompact}>{item.phone}</Text>
        </View>
      </View>

      <View style={styles.actionsRight}>
        <TouchableOpacity style={styles.iconBtn} accessibilityLabel="edit" onPress={() => openEdit(item)}>
          <Ionicons name="pencil" size={18} color={COLORS.primary} />
        </TouchableOpacity>
        <TouchableOpacity style={styles.iconBtn} accessibilityLabel="remove" onPress={() => confirmDelete(item)}>
          <Ionicons name="trash" size={18} color="#E53935" />
        </TouchableOpacity>
      </View>
    </View>
  );
  const openCreate = () => {
    setForm({
      first_name: '',
      middle_name: '',
      last_name: '',
      email: '',
      phone_number: '',
      role: 'manager',
      role_other: '',
      profile_pic: null,
    });
    setEditingId(null);
    setModalVisible(true);
  };

  const openEdit = (item) => {
    setEditingId(item.id || item.contractor_user_id || null);
    setForm({
      first_name: item.first_name || item.name?.split(' ')[0] || '',
      middle_name: item.middle_name || '',
      last_name: item.last_name || item.name?.split(' ').slice(1).join(' ') || '',
      email: item.email || '',
      phone_number: item.phone || item.phone_number || '',
      role: item.role || 'manager',
      role_other: item.role_other || '',
      profile_pic: item.profile_pic || item.avatar || null,
      _pickedFile: null,
      username: item.username || '',
      password: '',
      password_confirm: ''
    });
    setModalVisible(true);
  };

  const confirmDelete = (item) => {
    const id = item.id || item.contractor_user_id;
    Alert.alert('Delete member', 'Are you sure you want to delete this member?', [
      { text: 'Cancel', style: 'cancel' },
      { text: 'Delete', style: 'destructive', onPress: () => deleteMember(id) }
    ]);
  };

  const deleteMember = async (id) => {
    try {
      setLoading(true);
      const res = await api_request(api_config.endpoints.contractor_members.delete(id), { method: 'DELETE' });
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
    if (!form.first_name || !form.last_name || !form.email) {
      Alert.alert('Validation', 'Please fill in first name, last name and email.');
      return;
    }

    setSubmitting(true);
    try {
      const payload = {
        first_name: form.first_name,
        middle_name: form.middle_name,
        last_name: form.last_name,
        email: form.email,
        phone_number: form.phone_number,
        role: form.role,
        role_other: form.role_other,
        profile_pic: form.profile_pic,
      };

      let res;

      // If user picked a local file, send multipart/form-data
      if (form._pickedFile && form._pickedFile.uri) {
        const fd = new FormData();
        fd.append('first_name', payload.first_name);
        fd.append('middle_name', payload.middle_name || '');
        fd.append('last_name', payload.last_name);
        fd.append('email', payload.email);
        fd.append('phone_number', payload.phone_number || '');
        fd.append('role', payload.role);
        fd.append('role_other', payload.role_other || '');

        const uri = form._pickedFile.uri;
        const split = uri.split('/');
        const name = form._pickedFile.name || split[split.length - 1];
        const type = form._pickedFile.type || 'image/jpeg';

        // @ts-ignore
        fd.append('profile_pic', { uri, name, type });

        if (editingId) {
          res = await api_request(api_config.endpoints.contractor_members.update(editingId), {
            method: 'PUT',
            body: fd,
          });
        } else {
          res = await api_request(api_config.endpoints.contractor_members.create, {
            method: 'POST',
            body: fd,
          });
        }
      } else {
        if (editingId) {
          res = await api_request(api_config.endpoints.contractor_members.update(editingId), {
            method: 'PUT',
            body: JSON.stringify(payload),
          });
        } else {
          res = await api_request(api_config.endpoints.contractor_members.create, {
            method: 'POST',
            body: JSON.stringify(payload),
          });
        }
      }

      if (res.success) {
        setModalVisible(false);
        setEditingId(null);
        fetchMembers();
        const username = res.data && (res.data.username || res.data.user && res.data.user.username) ? (res.data.username || res.data.user.username) : null;
        if (isEditing) {
          // Edited
          Alert.alert('Changes Saved', 'Member updated successfully.');
        } else {
          // Created
          Alert.alert('Success', username ? `Member created. Username: ${username}` : 'Member created successfully.');
        }
      } else {
        Alert.alert('Error', res.message || (isEditing ? 'Failed to save changes' : 'Failed to create member'));
      }
    } catch (e) {
      console.error(e);
      Alert.alert('Error', 'Network error');
    } finally {
      setSubmitting(false);
    }
  };

  const pickImage = async () => {
    try {
      const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (!permission.granted) {
        Alert.alert('Permission required', 'Permission to access photos is required.');
        return;
      }

      const mediaTypes = ImagePicker.MediaTypeOptions?.Images ?? ImagePicker.MediaType?.Images ?? ImagePicker.MediaTypeOptions?.All;
      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes,
        allowsEditing: true,
        quality: 0.7,
      });

      // Support both legacy API (result.cancelled, result.uri)
      // and new API (result.canceled, result.assets)
      let uri = null;
      if (result && typeof result.canceled !== 'undefined') {
        // new API: result.canceled (boolean) and result.assets (array)
        if (!result.canceled && result.assets && result.assets.length > 0) {
          uri = result.assets[0].uri;
        }
      } else if (result && typeof result.cancelled !== 'undefined') {
        // legacy API
        if (!result.cancelled && result.uri) {
          uri = result.uri;
        }
      }

      if (uri) {
        const name = uri.split('/').pop();
        setForm({ ...form, profile_pic: uri, _pickedFile: { uri, name, type: 'image/jpeg' } });
      }
    } catch (e) {
      console.warn('Image pick failed', e);
    }
  };

  return (
    <View style={[styles.container, { paddingTop: 12 }]}> 
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="arrow-left" size={20} color={COLORS.text} />
        </TouchableOpacity>

        <Text style={styles.headerTitle}>Members</Text>
        <View style={styles.headerSpacer} />
      </View>

      <View style={styles.searchRow}>
        <TextInput placeholder="Search members" placeholderTextColor="#9AA0A6" style={styles.searchInput} />
        <TouchableOpacity style={[styles.newUserBtn, styles.newUserBtnWithIcon]} accessibilityLabel="new-member" onPress={openCreate}>
          <Feather name="plus" size={16} color="#fff" style={{ marginRight: 8 }} />
          <Text style={styles.newUserText}>Add Member</Text>
        </TouchableOpacity>
      </View>

      {loading ? (
        <View style={{ padding: 16 }}>
          <ActivityIndicator />
        </View>
      ) : (
        <FlatList
          data={members}
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
              <Text style={{ fontWeight: '700', fontSize: 16, marginBottom: 8 }}>{editingId ? 'Edit Member' : 'New Member'}</Text>

              <View style={{ alignItems: 'center', marginBottom: 12 }}>
                <TouchableOpacity onPress={pickImage} style={styles.avatarLargeWrap} accessibilityLabel="edit-photo">
                  <MemberImage uri={form.profile_pic} style={styles.avatarLarge} fallback={require('../../../assets/images/pictures/members_default.png')} />
                  <View style={styles.avatarEditWrap} pointerEvents="none">
                    <View style={styles.avatarEditBtn}>
                      <Ionicons name="pencil" size={14} color="#fff" />
                    </View>
                  </View>
                </TouchableOpacity>
                <Text style={{ marginTop: 8, color: COLORS.textSecondary }}>Add profile picture (optional)</Text>
              </View>

              <Text style={styles.sectionHeader}>Personal Information</Text>
              <TextInput placeholder="First name" value={form.first_name} onChangeText={(t) => setForm({ ...form, first_name: t })} style={styles.input} />
              <TextInput placeholder="Middle name (Optional)" value={form.middle_name} onChangeText={(t) => setForm({ ...form, middle_name: t })} style={styles.input} />
              <TextInput placeholder="Last name" value={form.last_name} onChangeText={(t) => setForm({ ...form, last_name: t })} style={styles.input} />
              <TextInput placeholder="Email" keyboardType="email-address" value={form.email} onChangeText={(t) => setForm({ ...form, email: t })} style={styles.input} />
              <TextInput placeholder="Phone" keyboardType="phone-pad" value={form.phone_number} onChangeText={(t) => setForm({ ...form, phone_number: t })} style={styles.input} />
              {/* Role picker implemented as simple inline options to avoid extra deps */}
              <View style={{ marginBottom: 8 }}>
                <Text style={styles.sectionHeader}>Role</Text>
                <TouchableOpacity onPress={() => setForm({ ...form, showRoleOptions: !form.showRoleOptions })} style={[styles.input, { justifyContent: 'space-between', flexDirection: 'row', alignItems: 'center' }] }>
                  <Text style={{ color: COLORS.text }}>{form.role ? (form.role.charAt(0).toUpperCase() + form.role.slice(1)) : 'Select role'}</Text>
                  <Ionicons name="chevron-down" size={18} color={COLORS.textSecondary} />
                </TouchableOpacity>
                {form.showRoleOptions && (
                  <View style={{ marginTop: 6, borderWidth: 1, borderColor: COLORS.border, borderRadius: 6, overflow: 'hidden' }}>
                    {['owner','manager','engineer','architect','representative','others'].map((r) => (
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
                    <Text style={styles.notePrimary}>Note: Username and Password are automatically generated.</Text>
                    <Text style={styles.noteSecondary}>Default Password: <Text style={{ fontWeight: '700' }}>teammember123@!</Text></Text>
                    <Text style={styles.noteSecondary}>The username will be <Text style={{ fontWeight: '700' }}>staff_</Text> followed by a random 4-digit number.</Text>
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
                    <Text style={styles.newUserText}>{editingId ? 'Save Changes' : 'Create'}</Text>
                  )}
                </TouchableOpacity>
              </View>
            </ScrollView>
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
  return `${api_config.base_url}/storage/${path}`;
}

function MemberImage({ uri, style, fallback }: { uri?: string | null; style?: any; fallback: any }) {
  const [errored, setErrored] = useState(false);
  React.useEffect(() => {
    // Reset error state when the source uri changes so a previously errored image
    // doesn't force the fallback to show for a new valid selection.
    setErrored(false);
  }, [uri]);

  if (!uri || errored) {
    return <Image source={fallback} style={style} />;
  }

  return (
    <Image
      source={{ uri: getProfileImageUrl(uri) }}
      style={style}
      onError={() => setErrored(true)}
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
    marginRight: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
    fontSize: 13,
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
    marginBottom: 6,
    fontSize: 12,
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
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
    marginLeft: 12,
  },
  iconBtn: {
    padding: 6,
    marginLeft: 8,
  },
});
