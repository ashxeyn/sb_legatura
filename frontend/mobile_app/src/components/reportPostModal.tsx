import React, { useEffect, useMemo, useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Modal,
  StyleSheet,
  TextInput,
  ActivityIndicator,
  ScrollView,
  Alert,
  Image,
  useWindowDimensions,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { Feather, MaterialCommunityIcons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import * as DocumentPicker from 'expo-document-picker';
import { ReportAttachment } from '../services/post_service';

const REPORT_REASONS = [
  'Spam',
  'Inappropriate Content',
  'Scam / Fraud',
  'False Information',
  'Other',
] as const;

type ReportReason = (typeof REPORT_REASONS)[number];

interface SubmitResult {
  success: boolean;
  message?: string;
}

interface ReportPostModalProps {
  visible: boolean;
  onClose: () => void;
  onSubmit: (reason: string, details?: string, attachments?: ReportAttachment[]) => Promise<SubmitResult>;
}

const MAX_ATTACHMENTS = 5;
const MAX_BYTES = 10 * 1024 * 1024;

export default function ReportPostModal({ visible, onClose, onSubmit }: ReportPostModalProps) {
  const { width: screenWidth } = useWindowDimensions();
  const [selectedReason, setSelectedReason] = useState<ReportReason | null>(null);
  const [otherDetails, setOtherDetails] = useState('');
  const [attachments, setAttachments] = useState<ReportAttachment[]>([]);
  const [submitting, setSubmitting] = useState(false);
  const [errorText, setErrorText] = useState('');
  const [successText, setSuccessText] = useState('');

  useEffect(() => {
    if (!visible) {
      setSelectedReason(null);
      setOtherDetails('');
      setAttachments([]);
      setSubmitting(false);
      setErrorText('');
      setSuccessText('');
    }
  }, [visible]);

  const canSubmit = useMemo(() => {
    if (!selectedReason) return false;
    if (selectedReason !== 'Other') return true;
    return otherDetails.trim().length > 0;
  }, [selectedReason, otherDetails]);

  const pickAttachment = async () => {
    if (attachments.length >= MAX_ATTACHMENTS) {
      Alert.alert('Limit reached', `You can attach up to ${MAX_ATTACHMENTS} files.`);
      return;
    }
    const remaining = MAX_ATTACHMENTS - attachments.length;
    Alert.alert(
      'Add Attachment',
      'Supported: JPG, PNG, GIF, PDF, DOCX Â· max 10 MB each',
      [
        {
          text: 'Image (JPG, PNG, GIF)',
          onPress: async () => {
            const result = await ImagePicker.launchImageLibraryAsync({
              mediaTypes: ImagePicker.MediaTypeOptions.Images,
              allowsMultipleSelection: true,
              quality: 0.85,
              selectionLimit: remaining,
            });
            if (result.canceled) return;
            const added: ReportAttachment[] = result.assets.map((a) => ({
              uri: a.uri,
              name: a.fileName || `image_${Date.now()}.jpg`,
              type: a.mimeType || 'image/jpeg',
            }));
            setAttachments((prev) => [...prev, ...added].slice(0, MAX_ATTACHMENTS));
          },
        },
        {
          text: 'Document (PDF, DOCX)',
          onPress: async () => {
            const result = await DocumentPicker.getDocumentAsync({
              type: [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
              ],
              multiple: true,
              copyToCacheDirectory: true,
            });
            if (result.canceled) return;
            const added: ReportAttachment[] = result.assets
              .filter((a) => (a.size || 0) <= MAX_BYTES)
              .map((a) => ({
                uri: a.uri,
                name: a.name,
                type: a.mimeType || 'application/octet-stream',
              }));
            if (added.length < result.assets.length) {
              Alert.alert('Some files skipped', 'Files over 10 MB were not added.');
            }
            setAttachments((prev) => [...prev, ...added].slice(0, MAX_ATTACHMENTS));
          },
        },
        { text: 'Cancel', style: 'cancel' },
      ],
    );
  };

  const removeAttachment = (index: number) => {
    setAttachments((prev) => prev.filter((_, i) => i !== index));
  };

  const isImage = (type: string) => type.startsWith('image/');
  const isPdf = (type: string) => type === 'application/pdf';

  const handleSubmit = async () => {
    if (!canSubmit || submitting || !selectedReason) return;
    setSubmitting(true);
    setErrorText('');
    try {
      const details = selectedReason === 'Other' ? otherDetails.trim() : undefined;
      const result = await onSubmit(selectedReason, details, attachments.length > 0 ? attachments : undefined);
      if (result?.success) {
        setSuccessText(result.message || 'Report sent successfully.');
      } else {
        setErrorText(result?.message || 'Unable to submit report right now.');
      }
    } catch {
      setErrorText('Unable to submit report right now.');
    } finally {
      setSubmitting(false);
    }
  };

  // Thumbnail size: 3 per row with gap
  const THUMB_GAP = 8;
  const CARD_H_PAD = 20;
  const thumbSize = Math.floor((screenWidth - CARD_H_PAD * 2 - THUMB_GAP * 2) / 3);

  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
      <KeyboardAvoidingView
        style={styles.overlay}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      >
        <TouchableOpacity style={styles.backdrop} activeOpacity={1} onPress={onClose} />

        <View style={styles.sheet}>
          {/* Handle bar */}
          <View style={styles.handleWrap}>
            <View style={styles.handle} />
          </View>

          <ScrollView
            showsVerticalScrollIndicator={false}
            keyboardShouldPersistTaps="handled"
            contentContainerStyle={styles.scrollContent}
          >
            {successText ? (
              <View style={styles.successWrap}>
                <View style={styles.successIcon}>
                  <Feather name="check" size={32} color="#16A34A" />
                </View>
                <Text style={styles.successTitle}>Report Submitted</Text>
                <Text style={styles.successDesc}>{successText}</Text>
                <TouchableOpacity style={styles.doneBtn} onPress={onClose}>
                  <Text style={styles.submitText}>Done</Text>
                </TouchableOpacity>
              </View>
            ) : (
              <>
                {/* Header */}
                <View style={styles.headerRow}>
                  <View>
                    <Text style={styles.title}>Report</Text>
                    <Text style={styles.subtitle}>Help us understand what's wrong</Text>
                  </View>
                  <TouchableOpacity onPress={onClose} style={styles.closeBtn} disabled={submitting}>
                    <Feather name="x" size={20} color="#6B7280" />
                  </TouchableOpacity>
                </View>

                {/* Reason selector */}
                <Text style={styles.sectionLabel}>Reason</Text>
                <View style={styles.reasonsWrap}>
                  {REPORT_REASONS.map((reason) => {
                    const isActive = selectedReason === reason;
                    return (
                      <TouchableOpacity
                        key={reason}
                        style={[styles.reasonRow, isActive && styles.reasonRowActive]}
                        onPress={() => setSelectedReason(reason)}
                        activeOpacity={0.8}
                        disabled={submitting}
                      >
                        <View style={[styles.radioOuter, isActive && styles.radioOuterActive]}>
                          {isActive && <View style={styles.radioInner} />}
                        </View>
                        <Text style={[styles.reasonText, isActive && styles.reasonTextActive]}>{reason}</Text>
                      </TouchableOpacity>
                    );
                  })}
                </View>

                {selectedReason === 'Other' && (
                  <View style={styles.otherInputWrap}>
                    <Text style={styles.sectionLabel}>Please specify</Text>
                    <TextInput
                      value={otherDetails}
                      onChangeText={setOtherDetails}
                      placeholder="Describe the issue..."
                      multiline
                      numberOfLines={4}
                      style={styles.otherInput}
                      editable={!submitting}
                      placeholderTextColor="#9CA3AF"
                    />
                  </View>
                )}

                {/* Attachments */}
                <View style={styles.attachSection}>
                  <View style={styles.attachHeaderRow}>
                    <Text style={styles.sectionLabel}>
                      Attachments{' '}
                      <Text style={styles.attachCount}>
                        {attachments.length}/{MAX_ATTACHMENTS}
                      </Text>
                    </Text>
                    {attachments.length < MAX_ATTACHMENTS && (
                      <TouchableOpacity
                        style={[styles.attachBtn, submitting && { opacity: 0.4 }]}
                        onPress={pickAttachment}
                        disabled={submitting}
                        activeOpacity={0.7}
                      >
                        <Feather name="paperclip" size={13} color="#4B5563" />
                        <Text style={styles.attachBtnText}>Add file</Text>
                      </TouchableOpacity>
                    )}
                  </View>

                  {attachments.length > 0 && (
                    <View style={styles.previewGrid}>
                      {attachments.map((a, i) => (
                        <View key={i} style={[styles.previewCell, { width: thumbSize, height: thumbSize }]}>
                          {isImage(a.type) ? (
                            <Image
                              source={{ uri: a.uri }}
                              style={styles.previewImg}
                              resizeMode="cover"
                            />
                          ) : (
                            <View style={styles.docPreview}>
                              <MaterialCommunityIcons
                                name={isPdf(a.type) ? 'file-pdf-box' : 'file-word-box'}
                                size={36}
                                color={isPdf(a.type) ? '#DC2626' : '#2563EB'}
                              />
                              <Text style={styles.docExt}>
                                {isPdf(a.type) ? 'PDF' : 'DOCX'}
                              </Text>
                            </View>
                          )}
                          {/* Filename overlay at bottom */}
                          <View style={styles.previewNameBar}>
                            <Text style={styles.previewName} numberOfLines={1}>{a.name}</Text>
                          </View>
                          {/* Remove button */}
                          <TouchableOpacity
                            style={styles.removeBtn}
                            onPress={() => removeAttachment(i)}
                            disabled={submitting}
                            hitSlop={{ top: 4, bottom: 4, left: 4, right: 4 }}
                          >
                            <Feather name="x" size={11} color="#fff" />
                          </TouchableOpacity>
                        </View>
                      ))}
                    </View>
                  )}

                  {attachments.length === 0 && (
                    <Text style={styles.attachHint}>JPG, PNG, GIF, PDF, DOCX Â· max 10 MB each</Text>
                  )}
                </View>

                {!!errorText && (
                  <View style={styles.errorBox}>
                    <Feather name="alert-circle" size={14} color="#B91C1C" />
                    <Text style={styles.errorText}>{errorText}</Text>
                  </View>
                )}

                <View style={styles.actionsRow}>
                  <TouchableOpacity style={styles.cancelBtn} onPress={onClose} disabled={submitting}>
                    <Text style={styles.cancelText}>Cancel</Text>
                  </TouchableOpacity>
                  <TouchableOpacity
                    style={[styles.submitBtn, (!canSubmit || submitting) && styles.submitBtnDisabled]}
                    onPress={handleSubmit}
                    disabled={!canSubmit || submitting}
                  >
                    {submitting
                      ? <ActivityIndicator size="small" color="#FFFFFF" />
                      : <Text style={styles.submitText}>Submit Report</Text>
                    }
                  </TouchableOpacity>
                </View>
              </>
            )}
          </ScrollView>
        </View>
      </KeyboardAvoidingView>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    justifyContent: 'flex-end',
  },
  backdrop: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.4)',
  },
  sheet: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: '92%',
    minHeight: '60%',
    paddingBottom: 24,
  },
  handleWrap: {
    alignItems: 'center',
    paddingTop: 10,
    paddingBottom: 4,
  },
  handle: {
    width: 40,
    height: 4,
    borderRadius: 2,
    backgroundColor: '#D1D5DB',
  },
  scrollContent: {
    paddingHorizontal: 20,
    paddingBottom: 8,
  },

  // Header
  headerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 18,
    marginTop: 6,
  },
  title: {
    fontSize: 20,
    fontWeight: '700',
    color: '#111827',
  },
  subtitle: {
    marginTop: 2,
    fontSize: 13,
    color: '#6B7280',
  },
  closeBtn: {
    padding: 4,
    marginTop: 2,
  },

  // Section label
  sectionLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: '#374151',
    textTransform: 'uppercase',
    letterSpacing: 0.6,
    marginBottom: 8,
  },

  // Reasons
  reasonsWrap: {
    gap: 7,
    marginBottom: 16,
  },
  reasonRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderWidth: 1.5,
    borderColor: '#E5E7EB',
    borderRadius: 10,
    paddingVertical: 11,
    paddingHorizontal: 12,
    backgroundColor: '#FAFAFA',
  },
  reasonRowActive: {
    borderColor: '#EEA24B',
    backgroundColor: '#FFF8EE',
  },
  radioOuter: {
    width: 18,
    height: 18,
    borderRadius: 9,
    borderWidth: 2,
    borderColor: '#D1D5DB',
    alignItems: 'center',
    justifyContent: 'center',
  },
  radioOuterActive: {
    borderColor: '#EEA24B',
  },
  radioInner: {
    width: 9,
    height: 9,
    borderRadius: 5,
    backgroundColor: '#EEA24B',
  },
  reasonText: {
    fontSize: 14,
    color: '#374151',
    fontWeight: '500',
  },
  reasonTextActive: {
    color: '#92400E',
    fontWeight: '600',
  },

  // Other input
  otherInputWrap: {
    marginBottom: 16,
  },
  otherInput: {
    borderWidth: 1.5,
    borderColor: '#D1D5DB',
    borderRadius: 10,
    minHeight: 90,
    textAlignVertical: 'top',
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 14,
    color: '#111827',
    backgroundColor: '#FAFAFA',
  },

  // Attachments
  attachSection: {
    marginBottom: 16,
  },
  attachHeaderRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  attachCount: {
    fontWeight: '400',
    color: '#9CA3AF',
    textTransform: 'none',
    letterSpacing: 0,
  },
  attachBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 7,
    paddingVertical: 5,
    paddingHorizontal: 10,
    backgroundColor: '#F9FAFB',
  },
  attachBtnText: {
    fontSize: 12,
    color: '#374151',
    fontWeight: '500',
  },
  attachHint: {
    fontSize: 11,
    color: '#9CA3AF',
    marginTop: 2,
  },

  // Preview grid
  previewGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  previewCell: {
    borderRadius: 10,
    overflow: 'hidden',
    backgroundColor: '#F3F4F6',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    position: 'relative',
  },
  previewImg: {
    width: '100%',
    height: '100%',
  },
  docPreview: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 4,
    paddingBottom: 20,
  },
  docExt: {
    fontSize: 11,
    fontWeight: '700',
    color: '#6B7280',
    letterSpacing: 0.5,
  },
  previewNameBar: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: 'rgba(0,0,0,0.48)',
    paddingHorizontal: 5,
    paddingVertical: 3,
  },
  previewName: {
    fontSize: 10,
    color: '#FFFFFF',
  },
  removeBtn: {
    position: 'absolute',
    top: 5,
    right: 5,
    width: 20,
    height: 20,
    borderRadius: 10,
    backgroundColor: 'rgba(0,0,0,0.55)',
    alignItems: 'center',
    justifyContent: 'center',
  },

  // Error
  errorBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: '#FEF2F2',
    borderWidth: 1,
    borderColor: '#FECACA',
    borderRadius: 8,
    paddingHorizontal: 10,
    paddingVertical: 8,
    marginBottom: 12,
  },
  errorText: {
    fontSize: 13,
    color: '#B91C1C',
    flex: 1,
  },

  // Success
  successWrap: {
    alignItems: 'center',
    paddingVertical: 32,
    paddingHorizontal: 16,
    gap: 12,
  },
  successIcon: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#DCFCE7',
    alignItems: 'center',
    justifyContent: 'center',
  },
  successTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#111827',
  },
  successDesc: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    lineHeight: 20,
  },
  doneBtn: {
    backgroundColor: '#EEA24B',
    borderRadius: 10,
    paddingVertical: 12,
    paddingHorizontal: 40,
    alignItems: 'center',
    marginTop: 4,
  },

  // Actions
  actionsRow: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 4,
  },
  cancelBtn: {
    flex: 1,
    borderWidth: 1.5,
    borderColor: '#D1D5DB',
    borderRadius: 10,
    paddingVertical: 12,
    alignItems: 'center',
  },
  cancelText: {
    fontSize: 14,
    color: '#374151',
    fontWeight: '600',
  },
  submitBtn: {
    flex: 2,
    backgroundColor: '#EEA24B',
    borderRadius: 10,
    paddingVertical: 12,
    alignItems: 'center',
  },
  submitBtnDisabled: {
    opacity: 0.45,
  },
  submitText: {
    fontSize: 14,
    color: '#FFFFFF',
    fontWeight: '700',
  },
});
