// @ts-nocheck
import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Modal,
  Alert,
  ActivityIndicator,
  Image,
  Dimensions,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { Feather, MaterialIcons, Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { post_service, CompletedProject } from '../../services/post_service';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

/* ─── Design tokens (match checkProfile) ──────────────────────────── */
const BRAND   = '#EEA24B';
const BRAND_L = '#FFF8EE';
const BORDER  = '#E8EAED';
const BG      = '#f0f2f5';
const T1      = '#1a1a1a';
const T2      = '#6b7280';
const CARD_R  = 8;

interface CreateShowcaseProps {
  visible: boolean;
  onClose: () => void;
  onCreated: () => void;          // callback after successful creation → refresh portfolio
}

interface PickedImage {
  uri: string;
  name: string;
  type: string;
}

export default function CreateShowcase({ visible, onClose, onCreated }: CreateShowcaseProps) {
  // ── Form state ──
  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [location, setLocation] = useState('');
  const [images, setImages] = useState<PickedImage[]>([]);
  const [linkedProjectId, setLinkedProjectId] = useState<number | null>(null);

  // ── Data state ──
  const [completedProjects, setCompletedProjects] = useState<CompletedProject[]>([]);
  const [loadingProjects, setLoadingProjects] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [showProjectPicker, setShowProjectPicker] = useState(false);

  // ── Fetch completed projects when modal opens ──
  useEffect(() => {
    if (visible) {
      loadCompletedProjects();
    } else {
      // Reset form on close
      setTitle('');
      setContent('');
      setLocation('');
      setImages([]);
      setLinkedProjectId(null);
      setShowProjectPicker(false);
    }
  }, [visible]);

  const loadCompletedProjects = useCallback(async () => {
    setLoadingProjects(true);
    try {
      const res = await post_service.get_completed_projects();
      if (res.success && res.data) {
        setCompletedProjects(res.data);
      }
    } catch (e) {
      console.error('[CreateShowcase] loadCompletedProjects:', e);
    } finally {
      setLoadingProjects(false);
    }
  }, []);

  const selectedProject = completedProjects.find((p) => p.project_id === linkedProjectId);

  // ── Image functions ──
  const pickImages = async () => {
    if (images.length >= 10) {
      Alert.alert('Limit reached', 'You can upload up to 10 images.');
      return;
    }

    const perm = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!perm.granted) {
      Alert.alert('Permission Required', 'Please allow photo library access.');
      return;
    }

    const MEDIA_IMAGES =
      (ImagePicker.MediaType && ImagePicker.MediaType.Images) ||
      (ImagePicker.MediaTypeOptions && ImagePicker.MediaTypeOptions.Images) ||
      'Images';

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: MEDIA_IMAGES,
      allowsMultipleSelection: true,
      selectionLimit: 10 - images.length,
      quality: 0.8,
    });

    if (!result.canceled && result.assets) {
      const newImages: PickedImage[] = result.assets.map((asset, i) => ({
        uri: asset.uri,
        name: asset.fileName || `showcase_${Date.now()}_${i}.jpg`,
        type: asset.mimeType || 'image/jpeg',
      }));
      setImages((prev) => [...prev, ...newImages].slice(0, 10));
    }
  };

  const removeImage = (index: number) => {
    setImages((prev) => prev.filter((_, i) => i !== index));
  };

  // ── Submit ──
  const handleSubmit = async () => {
    if (!title.trim()) {
      Alert.alert('Validation', 'Please enter a title for your showcase.');
      return;
    }
    if (content.trim().length < 10) {
      Alert.alert('Validation', 'Description must be at least 10 characters.');
      return;
    }

    setSubmitting(true);
    try {
      const payload: any = {
        title: title.trim(),
        content: content.trim(),
      };

      if (linkedProjectId) {
        payload.linked_project_id = linkedProjectId;
      }
      if (location.trim()) {
        payload.location = location.trim();
      }

      const res = await post_service.create_showcase(payload, images);
      if (res.success) {
        Alert.alert('Success', 'Your showcase post has been created!', [
          { text: 'OK', onPress: () => { onCreated(); onClose(); } },
        ]);
      } else {
        Alert.alert('Error', res.message || 'Failed to create showcase post.');
      }
    } catch (e) {
      Alert.alert('Error', 'An unexpected error occurred.');
      console.error('[CreateShowcase] handleSubmit:', e);
    } finally {
      setSubmitting(false);
    }
  };

  // ── Render ──
  const formatCurrency = (amount: number | null | undefined): string => {
    if (amount == null) return '—';
    return '₱' + Number(amount).toLocaleString('en-PH', { maximumFractionDigits: 0 });
  };

  return (
    <Modal visible={visible} animationType="slide" presentationStyle="pageSheet" onRequestClose={onClose}>
      <KeyboardAvoidingView
        style={styles.container}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      >
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={onClose} disabled={submitting}>
            <Feather name="x" size={24} color={T1} />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Create Showcase</Text>
          <TouchableOpacity
            style={[styles.postBtn, (!title.trim() || content.trim().length < 10) && styles.postBtnDisabled]}
            onPress={handleSubmit}
            disabled={submitting || !title.trim() || content.trim().length < 10}
          >
            {submitting ? (
              <ActivityIndicator size="small" color="#fff" />
            ) : (
              <Text style={styles.postBtnText}>Post</Text>
            )}
          </TouchableOpacity>
        </View>

        <ScrollView style={styles.body} contentContainerStyle={styles.bodyContent} keyboardShouldPersistTaps="handled">
          {/* Link to Completed Project */}
          <View style={styles.section}>
            <Text style={styles.sectionLabel}>
              <MaterialIcons name="verified" size={14} color={BRAND} /> Link to Completed Project
            </Text>
            <Text style={styles.sectionHint}>
              Linking verifies this showcase is from a real completed project.
            </Text>

            {linkedProjectId && selectedProject ? (
              <View style={styles.linkedProjectCard}>
                <View style={styles.linkedProjectInfo}>
                  <MaterialIcons name="check-circle" size={18} color="#16a34a" />
                  <View style={{ flex: 1, marginLeft: 8 }}>
                    <Text style={styles.linkedProjectTitle} numberOfLines={1}>
                      {selectedProject.project_title}
                    </Text>
                    <Text style={styles.linkedProjectMeta}>
                      {selectedProject.owner_name} • {selectedProject.project_location || 'No location'}
                    </Text>
                  </View>
                  <TouchableOpacity onPress={() => setLinkedProjectId(null)} hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}>
                    <Feather name="x" size={18} color={T2} />
                  </TouchableOpacity>
                </View>
              </View>
            ) : (
              <TouchableOpacity style={styles.selectProjectBtn} onPress={() => setShowProjectPicker(true)}>
                <Feather name="link" size={16} color={BRAND} />
                <Text style={styles.selectProjectBtnText}>
                  {loadingProjects ? 'Loading projects…' : 'Select a completed project'}
                </Text>
              </TouchableOpacity>
            )}
          </View>

          {/* Title */}
          <View style={styles.section}>
            <Text style={styles.sectionLabel}>Title *</Text>
            <TextInput
              style={styles.textInput}
              value={title}
              onChangeText={setTitle}
              placeholder="e.g., Modern 2-Storey Residence in Cebu"
              placeholderTextColor="#a0a0a0"
              maxLength={255}
            />
          </View>

          {/* Description */}
          <View style={styles.section}>
            <Text style={styles.sectionLabel}>Description *</Text>
            <TextInput
              style={[styles.textInput, styles.textArea]}
              value={content}
              onChangeText={setContent}
              placeholder="Describe the project, what you did, challenges, results…"
              placeholderTextColor="#a0a0a0"
              multiline
              numberOfLines={5}
              maxLength={5000}
              textAlignVertical="top"
            />
            <Text style={styles.charCount}>{content.length}/5000</Text>
          </View>

          {/* Location (optional) */}
          <View style={styles.section}>
            <Text style={styles.sectionLabel}>
              <MaterialIcons name="location-on" size={14} color={BRAND} /> Location (optional)
            </Text>
            <TextInput
              style={styles.textInput}
              value={location}
              onChangeText={setLocation}
              placeholder="e.g., Cebu City, Cebu"
              placeholderTextColor="#a0a0a0"
              maxLength={500}
            />
          </View>

          {/* Photos */}
          <View style={styles.section}>
            <Text style={styles.sectionLabel}>Photos ({images.length}/10)</Text>
            <Text style={styles.sectionHint}>Add before & after photos to showcase your work.</Text>

            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.imageRow}>
              {/* Add button */}
              <TouchableOpacity style={styles.addImageBtn} onPress={pickImages}>
                <Feather name="plus" size={24} color={BRAND} />
                <Text style={styles.addImageText}>Add</Text>
              </TouchableOpacity>

              {images.map((img, i) => (
                <View key={i} style={styles.imageThumb}>
                  <Image source={{ uri: img.uri }} style={styles.imageThumbImg} />
                  <TouchableOpacity style={styles.removeImageBtn} onPress={() => removeImage(i)}>
                    <Feather name="x" size={14} color="#fff" />
                  </TouchableOpacity>
                </View>
              ))}
            </ScrollView>
          </View>
        </ScrollView>

        {/* Project Picker Bottom Sheet */}
        <Modal visible={showProjectPicker} animationType="slide" transparent onRequestClose={() => setShowProjectPicker(false)}>
          <View style={styles.pickerOverlay}>
            <View style={styles.pickerSheet}>
              <View style={styles.pickerHeader}>
                <Text style={styles.pickerHeaderTitle}>Select Completed Project</Text>
                <TouchableOpacity onPress={() => setShowProjectPicker(false)}>
                  <Feather name="x" size={22} color={T1} />
                </TouchableOpacity>
              </View>

              <ScrollView style={styles.pickerBody}>
                {loadingProjects ? (
                  <ActivityIndicator size="large" color={BRAND} style={{ marginTop: 40 }} />
                ) : completedProjects.length === 0 ? (
                  <View style={styles.pickerEmpty}>
                    <MaterialIcons name="construction" size={40} color="#d1d5db" />
                    <Text style={styles.pickerEmptyTitle}>No completed projects</Text>
                    <Text style={styles.pickerEmptyText}>
                      Complete a project first to link it to a showcase post.
                    </Text>
                  </View>
                ) : (
                  completedProjects.map((proj) => (
                    <TouchableOpacity
                      key={proj.project_id}
                      style={[styles.pickerItem, proj.already_showcased && styles.pickerItemDim]}
                      onPress={() => {
                        setLinkedProjectId(proj.project_id);
                        setShowProjectPicker(false);
                        // Auto-fill title if empty
                        if (!title.trim()) setTitle(proj.project_title);
                      }}
                    >
                      <View style={{ flex: 1 }}>
                        <Text style={styles.pickerItemTitle}>{proj.project_title}</Text>
                        <Text style={styles.pickerItemMeta}>
                          {proj.owner_name} • {proj.project_location || 'No location'}
                          {proj.budget_range_min != null ? ` • ${formatCurrency(proj.budget_range_min)}–${formatCurrency(proj.budget_range_max)}` : ''}
                        </Text>
                        {proj.already_showcased && (
                          <Text style={styles.pickerAlreadyTag}>Already showcased</Text>
                        )}
                      </View>
                      <MaterialIcons
                        name={linkedProjectId === proj.project_id ? 'radio-button-checked' : 'radio-button-unchecked'}
                        size={22}
                        color={linkedProjectId === proj.project_id ? BRAND : '#d1d5db'}
                      />
                    </TouchableOpacity>
                  ))
                )}
              </ScrollView>
            </View>
          </View>
        </Modal>
      </KeyboardAvoidingView>
    </Modal>
  );
}

/* ─── Styles ───────────────────────────────────────────────────────── */

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#fff' },

  /* Header */
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: BORDER,
  },
  headerTitle: { fontSize: 17, fontWeight: '700', color: T1 },
  postBtn: {
    backgroundColor: BRAND,
    paddingHorizontal: 20,
    paddingVertical: 8,
    borderRadius: 20,
    minWidth: 68,
    alignItems: 'center',
  },
  postBtnDisabled: { opacity: 0.5 },
  postBtnText: { color: '#fff', fontWeight: '700', fontSize: 14 },

  /* Body */
  body: { flex: 1 },
  bodyContent: { padding: 16, paddingBottom: 40 },

  /* Sections */
  section: { marginBottom: 20 },
  sectionLabel: { fontSize: 13, fontWeight: '700', color: T1, marginBottom: 6 },
  sectionHint: { fontSize: 12, color: T2, marginBottom: 8, lineHeight: 17 },

  /* Text inputs */
  textInput: {
    backgroundColor: BG,
    borderWidth: 1,
    borderColor: BORDER,
    borderRadius: CARD_R,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 14,
    color: T1,
  },
  textArea: { minHeight: 120 },
  charCount: { fontSize: 11, color: T2, textAlign: 'right', marginTop: 4 },

  /* Linked project card */
  linkedProjectCard: {
    backgroundColor: '#f0fdf4',
    borderWidth: 1,
    borderColor: '#bbf7d0',
    borderRadius: CARD_R,
    padding: 12,
  },
  linkedProjectInfo: { flexDirection: 'row', alignItems: 'center' },
  linkedProjectTitle: { fontSize: 14, fontWeight: '600', color: T1 },
  linkedProjectMeta: { fontSize: 12, color: T2, marginTop: 2 },

  /* Select project button */
  selectProjectBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderWidth: 1.5,
    borderColor: BRAND,
    borderStyle: 'dashed',
    borderRadius: CARD_R,
    paddingVertical: 14,
    paddingHorizontal: 16,
  },
  selectProjectBtnText: { fontSize: 14, color: BRAND, fontWeight: '500' },

  /* Image picker */
  imageRow: { gap: 10, paddingVertical: 4 },
  addImageBtn: {
    width: 80,
    height: 80,
    borderRadius: CARD_R,
    borderWidth: 1.5,
    borderColor: BRAND,
    borderStyle: 'dashed',
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: BRAND_L,
  },
  addImageText: { fontSize: 11, color: BRAND, fontWeight: '600', marginTop: 2 },
  imageThumb: { width: 80, height: 80, borderRadius: CARD_R, overflow: 'hidden', position: 'relative' },
  imageThumbImg: { width: '100%', height: '100%' },
  removeImageBtn: {
    position: 'absolute',
    top: 4,
    right: 4,
    width: 22,
    height: 22,
    borderRadius: 11,
    backgroundColor: 'rgba(0,0,0,0.55)',
    justifyContent: 'center',
    alignItems: 'center',
  },

  /* Project picker sheet */
  pickerOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.4)',
    justifyContent: 'flex-end',
  },
  pickerSheet: {
    backgroundColor: '#fff',
    borderTopLeftRadius: 16,
    borderTopRightRadius: 16,
    maxHeight: '75%',
  },
  pickerHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: BORDER,
  },
  pickerHeaderTitle: { fontSize: 16, fontWeight: '700', color: T1 },
  pickerBody: { paddingHorizontal: 16, paddingBottom: 30 },
  pickerEmpty: { alignItems: 'center', paddingVertical: 40 },
  pickerEmptyTitle: { fontSize: 15, fontWeight: '600', color: T2, marginTop: 12 },
  pickerEmptyText: { fontSize: 13, color: '#9ca3af', textAlign: 'center', marginTop: 4, paddingHorizontal: 20 },

  pickerItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#f3f4f6',
    gap: 10,
  },
  pickerItemDim: { opacity: 0.55 },
  pickerItemTitle: { fontSize: 14, fontWeight: '600', color: T1 },
  pickerItemMeta: { fontSize: 12, color: T2, marginTop: 2 },
  pickerAlreadyTag: { fontSize: 11, color: BRAND, fontWeight: '500', marginTop: 2 },
});
