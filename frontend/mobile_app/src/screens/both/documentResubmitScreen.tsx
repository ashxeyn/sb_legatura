// @ts-nocheck
import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Alert,
  Image,
  ActivityIndicator,
  ScrollView,
  StatusBar,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { api_config, api_request } from '../../config/api';

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
  background: '#FFFFFF',
  surface: '#FFFFFF',
  text: '#1E3A5F',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
};

interface ResubmissionItem {
  role: 'property_owner' | 'contractor';
  reason: string;
  fields: string[];
}

interface DocumentResubmitScreenProps {
  resubmission: ResubmissionItem[];
  onBack: () => void;
  onComplete: () => void;
}

const FIELD_LABELS: Record<string, string> = {
  valid_id_photo: 'Valid ID (Front)',
  valid_id_back_photo: 'Valid ID (Back)',
  police_clearance: 'Police Clearance',
  dti_sec_registration: 'DTI / SEC Registration',
};

const FIELD_ICONS: Record<string, string> = {
  valid_id_photo: 'credit-card',
  valid_id_back_photo: 'credit-card',
  police_clearance: 'shield',
  dti_sec_registration: 'file-text',
};

export default function DocumentResubmitScreen({ resubmission, onBack, onComplete }: DocumentResubmitScreenProps) {
  const insets = useSafeAreaInsets();
  const [uploads, setUploads] = useState<Record<string, any>>({});
  const [submitting, setSubmitting] = useState(false);

  const pickImage = async (field: string) => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission Required', 'Please allow access to your photo library.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: false,
      quality: 0.8,
    });

    if (!result.canceled && result.assets?.[0]) {
      setUploads(prev => ({ ...prev, [field]: result.assets[0] }));
    }
  };

  const takePhoto = async (field: string) => {
    const { status } = await ImagePicker.requestCameraPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission Required', 'Please allow access to your camera.');
      return;
    }

    const result = await ImagePicker.launchCameraAsync({
      allowsEditing: false,
      quality: 0.8,
    });

    if (!result.canceled && result.assets?.[0]) {
      setUploads(prev => ({ ...prev, [field]: result.assets[0] }));
    }
  };

  const showImageOptions = (field: string) => {
    Alert.alert('Upload Document', 'Choose an option', [
      { text: 'Take Photo', onPress: () => takePhoto(field) },
      { text: 'Choose from Gallery', onPress: () => pickImage(field) },
      { text: 'Cancel', style: 'cancel' },
    ]);
  };

  const handleSubmit = async (item: ResubmissionItem) => {
    const hasUploads = item.fields.some(f => uploads[f]);
    if (!hasUploads) {
      Alert.alert('No Documents', 'Please upload at least one document before submitting.');
      return;
    }

    setSubmitting(true);
    try {
      const formData = new FormData();
      formData.append('role', item.role);

      item.fields.forEach(field => {
        if (uploads[field]) {
          const asset = uploads[field];
          const fileName = asset.fileName || `${field}.jpg`;
          const mimeType = asset.mimeType || 'image/jpeg';
          formData.append(field, {
            uri: asset.uri,
            name: fileName,
            type: mimeType,
          } as any);
        }
      });

      const response = await api_request(api_config.endpoints.verification.resubmit, {
        method: 'POST',
        body: formData,
      });

      if (response.success) {
        Alert.alert(
          'Documents Submitted',
          'Your documents have been resubmitted and are now pending review. You will be notified once a decision is made.',
          [{ text: 'OK', onPress: onComplete }]
        );
      } else {
        Alert.alert('Error', response.message || 'Failed to submit documents. Please try again.');
      }
    } catch (error) {
      console.error('Resubmission error:', error);
      Alert.alert('Error', 'Failed to connect to server. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onBack} style={styles.backButton}>
          <Feather name="chevron-left" size={28} color={COLORS.text} />
        </TouchableOpacity>
        <View style={{ width: 44 }} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* Page title */}
        <Text style={styles.pageTitle}>Resubmit Documents</Text>
        <Text style={styles.pageSubtitle}>
          Your verification was rejected. Upload the corrected documents below.
        </Text>

        {/* Warning banner */}
        <View style={styles.alertBanner}>
          <Feather name="alert-triangle" size={16} color="#92400E" />
          <Text style={styles.alertBannerText}>
            Review the rejection reason carefully before uploading new documents.
          </Text>
        </View>

        {resubmission.map((item, index) => (
          <View key={index}>
            {/* Section label */}
            <Text style={styles.sectionLabel}>
              {item.role === 'property_owner' ? 'Property Owner' : 'Contractor'} Verification
            </Text>

            {/* Rejection reason card */}
            <View style={styles.card}>
              <View style={styles.cardHeader}>
                <View style={[styles.cardHeaderIcon, { backgroundColor: COLORS.errorLight }]}>
                  <Feather name="x-circle" size={16} color={COLORS.error} />
                </View>
                <Text style={styles.cardHeaderTitle}>Rejection Reason</Text>
              </View>
              <View style={styles.cardDivider} />
              <Text style={styles.rejectionText}>{item.reason}</Text>
            </View>

            {/* Documents card */}
            <View style={[styles.card, { marginTop: 12 }]}>
              <View style={styles.cardHeader}>
                <View style={[styles.cardHeaderIcon, { backgroundColor: COLORS.primaryLight }]}>
                  <Feather name="upload-cloud" size={16} color={COLORS.primary} />
                </View>
                <Text style={styles.cardHeaderTitle}>Required Documents</Text>
              </View>
              <View style={styles.cardDivider} />

              {item.fields.map((field, fi) => (
                <View key={field}>
                  {fi > 0 && <View style={styles.fieldDivider} />}
                  <View style={styles.fieldRow}>
                    <View style={styles.fieldIconWrap}>
                      <Feather
                        name={FIELD_ICONS[field] || 'file'}
                        size={15}
                        color={uploads[field] ? COLORS.success : COLORS.textMuted}
                      />
                    </View>
                    <Text style={styles.fieldLabel}>{FIELD_LABELS[field] || field}</Text>
                    {uploads[field] ? (
                      <TouchableOpacity
                        style={styles.changeBtn}
                        onPress={() => showImageOptions(field)}
                        activeOpacity={0.7}
                      >
                        <Feather name="refresh-cw" size={13} color={COLORS.accent} />
                        <Text style={styles.changeBtnText}>Change</Text>
                      </TouchableOpacity>
                    ) : (
                      <TouchableOpacity
                        style={styles.uploadBtn}
                        onPress={() => showImageOptions(field)}
                        activeOpacity={0.7}
                      >
                        <Feather name="upload" size={13} color={COLORS.surface} />
                        <Text style={styles.uploadBtnText}>Upload</Text>
                      </TouchableOpacity>
                    )}
                  </View>
                  {uploads[field] && (
                    <View style={styles.previewWrap}>
                      <Image source={{ uri: uploads[field].uri }} style={styles.previewImage} />
                    </View>
                  )}
                </View>
              ))}
            </View>

            {/* Submit button */}
            <TouchableOpacity
              style={[styles.submitButton, submitting && styles.submitButtonDisabled]}
              onPress={() => handleSubmit(item)}
              disabled={submitting}
              activeOpacity={0.85}
            >
              {submitting ? (
                <ActivityIndicator color={COLORS.surface} size="small" />
              ) : (
                <>
                  <Feather name="send" size={17} color={COLORS.surface} style={{ marginRight: 8 }} />
                  <Text style={styles.submitButtonText}>Submit Documents</Text>
                </>
              )}
            </TouchableOpacity>
          </View>
        ))}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 8,
    paddingVertical: 12,
  },
  backButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: 24,
    paddingBottom: 40,
  },
  pageTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 6,
  },
  pageSubtitle: {
    fontSize: 15,
    color: COLORS.textSecondary,
    lineHeight: 22,
    marginBottom: 20,
  },
  alertBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 24,
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 4,
    backgroundColor: COLORS.warningLight,
    borderWidth: 1,
    borderColor: '#FDE68A',
  },
  alertBannerText: {
    flex: 1,
    fontSize: 13,
    fontWeight: '500',
    color: '#92400E',
    lineHeight: 18,
  },
  sectionLabel: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
    marginBottom: 10,
  },
  card: {
    backgroundColor: COLORS.surface,
    borderRadius: 4,
    borderWidth: 1,
    borderColor: COLORS.border,
    overflow: 'hidden',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 1,
  },
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  cardHeaderIcon: {
    width: 30,
    height: 30,
    borderRadius: 6,
    alignItems: 'center',
    justifyContent: 'center',
  },
  cardHeaderTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
  },
  cardDivider: {
    height: 1,
    backgroundColor: COLORS.border,
  },
  rejectionText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 22,
    paddingHorizontal: 14,
    paddingVertical: 14,
  },
  fieldDivider: {
    height: 1,
    backgroundColor: COLORS.borderLight,
    marginHorizontal: 14,
  },
  fieldRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 14,
    paddingVertical: 14,
    gap: 10,
  },
  fieldIconWrap: {
    width: 28,
    alignItems: 'center',
  },
  fieldLabel: {
    flex: 1,
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },
  uploadBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    backgroundColor: COLORS.accent,
    borderRadius: 6,
    paddingHorizontal: 12,
    paddingVertical: 7,
  },
  uploadBtnText: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.surface,
  },
  changeBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    backgroundColor: COLORS.surface,
    borderRadius: 6,
    borderWidth: 1.5,
    borderColor: COLORS.accent,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  changeBtnText: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.accent,
  },
  previewWrap: {
    marginHorizontal: 14,
    marginBottom: 14,
    borderRadius: 4,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  previewImage: {
    width: '100%',
    height: 190,
    resizeMode: 'cover',
  },
  submitButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.accent,
    borderRadius: 8,
    paddingVertical: 16,
    marginTop: 16,
    marginBottom: 28,
  },
  submitButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.surface,
  },
  submitButtonDisabled: {
    opacity: 0.5,
  },
});
