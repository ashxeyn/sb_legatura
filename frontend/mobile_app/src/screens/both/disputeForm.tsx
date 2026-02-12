// @ts-nocheck
import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  TextInput,
  StatusBar,
  Alert,
  ActivityIndicator,
  Modal,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import * as DocumentPicker from 'expo-document-picker';
import { dispute_service } from '../../services/dispute_service';

// Color palette
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

const DISPUTE_TYPES = [
  { value: 'Payment', label: 'Payment Issue', icon: 'dollar-sign', description: 'Payment not received or incorrect amount' },
  { value: 'Delay', label: 'Project Delay', icon: 'clock', description: 'Work not completed on time' },
  { value: 'Quality', label: 'Quality Issue', icon: 'alert-circle', description: 'Work quality below expectations' },
  { value: 'Halt', label: 'Request to Halt', icon: 'pause-circle', description: 'Request to halt or pause the project' },
  { value: 'Others', label: 'Other Issue', icon: 'more-horizontal', description: 'Specify other concerns' },
];

interface EvidenceFile {
  uri: string;
  name: string;
  type: string;
  size: number;
}

interface DisputeFormProps {
  projectId: number;
  projectTitle: string;
  milestoneId: number;
  milestoneTitle: string;
  milestoneItemId: number;
  milestoneItemTitle: string;
  onClose: () => void;
  onSuccess: () => void;
}

export default function DisputeForm({
  projectId,
  projectTitle,
  milestoneId,
  milestoneTitle,
  milestoneItemId,
  milestoneItemTitle,
  onClose,
  onSuccess,
}: DisputeFormProps) {
  const insets = useSafeAreaInsets();
  const [disputeType, setDisputeType] = useState<string>('');
  const [ifOthersDistype, setIfOthersDistype] = useState('');
  const [disputeDesc, setDisputeDesc] = useState('');
  const [evidenceFiles, setEvidenceFiles] = useState<EvidenceFile[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showTypeModal, setShowTypeModal] = useState(false);
  const [errors, setErrors] = useState<{
    disputeType?: string;
    ifOthersDistype?: string;
    disputeDesc?: string;
  }>({});

  // Debug log to check what values are passed
  React.useEffect(() => {
    console.log('DisputeForm props:', {
      projectId,
      projectTitle,
      milestoneId,
      milestoneTitle,
      milestoneItemId,
      milestoneItemTitle,
    });
  }, []);

  const handlePickFiles = async () => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        copyToCacheDirectory: true,
        multiple: true,
      });

      if (!result.canceled && result.assets) {
        const newFiles: EvidenceFile[] = result.assets.map((asset) => ({
          uri: asset.uri,
          name: asset.name,
          type: asset.mimeType || 'application/octet-stream',
          size: asset.size || 0,
        }));

        const totalFiles = evidenceFiles.length + newFiles.length;
        if (totalFiles > 10) {
          Alert.alert('Too Many Files', 'You can upload a maximum of 10 evidence files.');
          return;
        }

        // Check file sizes
        const oversized = newFiles.filter(f => f.size > 5 * 1024 * 1024);
        if (oversized.length > 0) {
          Alert.alert('File Too Large', `Some files exceed 5MB: ${oversized.map(f => f.name).join(', ')}`);
          return;
        }

        setEvidenceFiles([...evidenceFiles, ...newFiles]);
      }
    } catch (error) {
      console.error('Error picking files:', error);
      Alert.alert('Error', 'Failed to pick files. Please try again.');
    }
  };

  const handleRemoveFile = (index: number) => {
    setEvidenceFiles(evidenceFiles.filter((_, i) => i !== index));
  };

  const formatFileSize = (bytes: number) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  };

  const getFileIcon = (fileName: string) => {
    const ext = fileName.split('.').pop()?.toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext || '')) return 'image';
    if (ext === 'pdf') return 'file-text';
    if (['doc', 'docx'].includes(ext || '')) return 'file';
    return 'paperclip';
  };

  const validateForm = (): boolean => {
    const newErrors: typeof errors = {};

    if (!disputeType) {
      newErrors.disputeType = 'Please select a dispute type';
    }

    if (disputeType === 'Others' && !ifOthersDistype.trim()) {
      newErrors.ifOthersDistype = 'Please specify the dispute type';
    }

    if (!disputeDesc.trim()) {
      newErrors.disputeDesc = 'Please provide a detailed description';
    } else if (disputeDesc.length > 2000) {
      newErrors.disputeDesc = 'Description cannot exceed 2000 characters';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) {
      return;
    }

    setIsSubmitting(true);

    try {
      console.log('Submitting dispute with:', {
        projectId,
        milestoneId,
        milestoneItemId,
        disputeType,
        disputeDescLength: disputeDesc.length,
        filesCount: evidenceFiles.length,
      });

      const response = await dispute_service.file_dispute(
        projectId,
        milestoneId,
        milestoneItemId,
        disputeType as any,
        disputeDesc,
        disputeType === 'Others' ? ifOthersDistype : undefined,
        evidenceFiles
      );

      if (response.success) {
        Alert.alert('Dispute Filed', 'Your dispute has been submitted successfully', [
          {
            text: 'OK',
            onPress: () => {
              onSuccess();
              onClose();
            },
          },
        ]);
      } else {
        Alert.alert('Error', response.message || 'Failed to file dispute');
      }
    } catch (error) {
      console.error('Error submitting dispute:', error);
      Alert.alert('Error', 'An unexpected error occurred. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const selectedType = DISPUTE_TYPES.find(t => t.value === disputeType);

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="x" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>File a Dispute</Text>
        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
      >
        {/* Project Info */}
        <View style={styles.infoCard}>
          <View style={styles.infoRow}>
            <Feather name="folder" size={16} color={COLORS.textSecondary} />
            <Text style={styles.infoLabel}>Project:</Text>
            <Text style={styles.infoValue}>{projectTitle}</Text>
          </View>
          <View style={styles.infoRow}>
            <Feather name="flag" size={16} color={COLORS.textSecondary} />
            <Text style={styles.infoLabel}>Milestone:</Text>
            <Text style={styles.infoValue}>{milestoneItemTitle}</Text>
          </View>
        </View>

        {/* Alert Message */}
        <View style={styles.alertBox}>
          <Feather name="info" size={20} color={COLORS.info} />
          <Text style={styles.alertText}>
            Filing a dispute will notify the other party and an admin will review your case. Please provide accurate information.
          </Text>
        </View>

        {/* Dispute Type Selection */}
        <View style={styles.inputSection}>
          <Text style={styles.inputLabel}>
            Dispute Type <Text style={styles.required}>*</Text>
          </Text>
          <TouchableOpacity
            style={[styles.typeSelector, errors.disputeType && styles.inputError]}
            onPress={() => setShowTypeModal(true)}
          >
            {selectedType ? (
              <View style={styles.selectedTypeContainer}>
                <View style={styles.selectedTypeIconContainer}>
                  <Feather name={selectedType.icon as any} size={20} color={COLORS.accent} />
                </View>
                <View style={styles.selectedTypeTextContainer}>
                  <Text style={styles.selectedTypeLabel}>{selectedType.label}</Text>
                  <Text style={styles.selectedTypeDesc}>{selectedType.description}</Text>
                </View>
              </View>
            ) : (
              <Text style={styles.typeSelectorPlaceholder}>Select dispute type</Text>
            )}
            <Feather name="chevron-down" size={20} color={COLORS.textSecondary} />
          </TouchableOpacity>
          {errors.disputeType && (
            <Text style={styles.errorText}>{errors.disputeType}</Text>
          )}
        </View>

        {/* If Others field */}
        {disputeType === 'Others' && (
          <View style={styles.inputSection}>
            <Text style={styles.inputLabel}>
              Specify Dispute Type <Text style={styles.required}>*</Text>
            </Text>
            <TextInput
              style={[styles.textInput, errors.ifOthersDistype && styles.inputError]}
              value={ifOthersDistype}
              onChangeText={(text) => {
                setIfOthersDistype(text);
                if (errors.ifOthersDistype) {
                  setErrors(prev => ({ ...prev, ifOthersDistype: undefined }));
                }
              }}
              placeholder="e.g., Safety concerns, Contract violation"
              placeholderTextColor={COLORS.textMuted}
              maxLength={255}
            />
            {errors.ifOthersDistype && (
              <Text style={styles.errorText}>{errors.ifOthersDistype}</Text>
            )}
          </View>
        )}

        {/* Description */}
        <View style={styles.inputSection}>
          <Text style={styles.inputLabel}>
            Detailed Description <Text style={styles.required}>*</Text>
          </Text>
          <Text style={styles.inputDescription}>
            Provide a clear and detailed explanation of the issue. Include dates, amounts, or any relevant information.
          </Text>
          <TextInput
            style={[styles.textareaInput, errors.disputeDesc && styles.inputError]}
            value={disputeDesc}
            onChangeText={(text) => {
              setDisputeDesc(text);
              if (errors.disputeDesc) {
                setErrors(prev => ({ ...prev, disputeDesc: undefined }));
              }
            }}
            placeholder="Describe the issue in detail..."
            placeholderTextColor={COLORS.textMuted}
            multiline
            numberOfLines={6}
            maxLength={2000}
            textAlignVertical="top"
          />
          <Text style={styles.charCount}>{disputeDesc.length} / 2000</Text>
          {errors.disputeDesc && (
            <Text style={styles.errorText}>{errors.disputeDesc}</Text>
          )}
        </View>

        {/* Evidence Files */}
        <View style={styles.inputSection}>
          <Text style={styles.inputLabel}>Evidence Files (Optional)</Text>
          <Text style={styles.inputDescription}>
            Upload supporting documents, images, or screenshots (Max 10 files, 5MB each)
          </Text>

          {evidenceFiles.length > 0 && (
            <View style={styles.filesContainer}>
              {evidenceFiles.map((file, index) => (
                <View key={index} style={styles.fileItem}>
                  <View style={styles.fileIconContainer}>
                    <Feather name={getFileIcon(file.name)} size={20} color={COLORS.primary} />
                  </View>
                  <View style={styles.fileInfo}>
                    <Text style={styles.fileName} numberOfLines={1}>{file.name}</Text>
                    <Text style={styles.fileSize}>{formatFileSize(file.size)}</Text>
                  </View>
                  <TouchableOpacity
                    style={styles.removeFileButton}
                    onPress={() => handleRemoveFile(index)}
                  >
                    <Feather name="x" size={18} color={COLORS.error} />
                  </TouchableOpacity>
                </View>
              ))}
            </View>
          )}

          {evidenceFiles.length < 10 && (
            <TouchableOpacity
              style={styles.uploadButton}
              onPress={handlePickFiles}
            >
              <Feather name="upload" size={20} color={COLORS.accent} />
              <Text style={styles.uploadButtonText}>
                {evidenceFiles.length > 0 ? 'Add More Files' : 'Upload Files'}
              </Text>
            </TouchableOpacity>
          )}
        </View>

        <View style={{ height: 120 }} />
      </ScrollView>

      {/* Submit Button */}
      <View style={[styles.bottomButtonContainer, { paddingBottom: insets.bottom + 16 }]}>
        <TouchableOpacity
          style={[styles.submitButton, isSubmitting && styles.submitButtonDisabled]}
          onPress={handleSubmit}
          disabled={isSubmitting}
        >
          {isSubmitting ? (
            <ActivityIndicator color={COLORS.surface} size="small" />
          ) : (
            <>
              <Feather name="send" size={20} color={COLORS.surface} style={{ marginRight: 8 }} />
              <Text style={styles.submitButtonText}>Submit Dispute</Text>
            </>
          )}
        </TouchableOpacity>
      </View>

      {/* Type Selection Modal */}
      <Modal
        visible={showTypeModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowTypeModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Dispute Type</Text>
              <TouchableOpacity onPress={() => setShowTypeModal(false)}>
                <Feather name="x" size={24} color={COLORS.text} />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalScroll}>
              {DISPUTE_TYPES.map((type) => (
                <TouchableOpacity
                  key={type.value}
                  style={[
                    styles.typeOption,
                    disputeType === type.value && styles.typeOptionSelected,
                  ]}
                  onPress={() => {
                    setDisputeType(type.value);
                    if (errors.disputeType) {
                      setErrors(prev => ({ ...prev, disputeType: undefined }));
                    }
                    setShowTypeModal(false);
                  }}
                >
                  <View style={[
                    styles.typeOptionIcon,
                    disputeType === type.value && styles.typeOptionIconSelected,
                  ]}>
                    <Feather name={type.icon as any} size={24} color={
                      disputeType === type.value ? COLORS.surface : COLORS.accent
                    } />
                  </View>
                  <View style={styles.typeOptionText}>
                    <Text style={[
                      styles.typeOptionLabel,
                      disputeType === type.value && styles.typeOptionLabelSelected,
                    ]}>
                      {type.label}
                    </Text>
                    <Text style={styles.typeOptionDescription}>{type.description}</Text>
                  </View>
                  {disputeType === type.value && (
                    <Feather name="check" size={24} color={COLORS.accent} />
                  )}
                </TouchableOpacity>
              ))}
            </ScrollView>
          </View>
        </View>
      </Modal>
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
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  backButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
  },
  headerSpacer: {
    width: 44,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 24,
  },
  infoCard: {
    backgroundColor: COLORS.primaryLight,
    borderRadius: 12,
    padding: 16,
    marginBottom: 20,
    gap: 12,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  infoLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.textSecondary,
  },
  infoValue: {
    flex: 1,
    fontSize: 14,
    color: COLORS.text,
  },
  alertBox: {
    flexDirection: 'row',
    backgroundColor: COLORS.infoLight,
    borderRadius: 12,
    padding: 16,
    marginBottom: 24,
    gap: 12,
  },
  alertText: {
    flex: 1,
    fontSize: 14,
    color: COLORS.info,
    lineHeight: 20,
  },
  inputSection: {
    marginBottom: 24,
  },
  inputLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  inputDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginBottom: 12,
  },
  required: {
    color: COLORS.error,
  },
  typeSelector: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 12,
    padding: 16,
    backgroundColor: COLORS.surface,
  },
  typeSelectorPlaceholder: {
    fontSize: 15,
    color: COLORS.textMuted,
  },
  selectedTypeContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  selectedTypeIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.accentLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  selectedTypeTextContainer: {
    flex: 1,
  },
  selectedTypeLabel: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.text,
  },
  selectedTypeDesc: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginTop: 2,
  },
  textInput: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 12,
    padding: 14,
    fontSize: 15,
    color: COLORS.text,
    backgroundColor: COLORS.surface,
  },
  textareaInput: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 12,
    padding: 14,
    fontSize: 15,
    color: COLORS.text,
    backgroundColor: COLORS.surface,
    minHeight: 150,
  },
  charCount: {
    fontSize: 12,
    color: COLORS.textMuted,
    textAlign: 'right',
    marginTop: 4,
  },
  inputError: {
    borderColor: COLORS.error,
  },
  errorText: {
    fontSize: 13,
    color: COLORS.error,
    marginTop: 8,
  },
  filesContainer: {
    gap: 8,
    marginBottom: 12,
  },
  fileItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.borderLight,
    borderRadius: 12,
    padding: 12,
    gap: 12,
  },
  fileIconContainer: {
    width: 40,
    height: 40,
    borderRadius: 8,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  fileInfo: {
    flex: 1,
  },
  fileName: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.text,
  },
  fileSize: {
    fontSize: 12,
    color: COLORS.textSecondary,
    marginTop: 2,
  },
  removeFileButton: {
    width: 32,
    height: 32,
    justifyContent: 'center',
    alignItems: 'center',
  },
  uploadButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: COLORS.border,
    borderStyle: 'dashed',
    borderRadius: 12,
    padding: 20,
    backgroundColor: COLORS.borderLight,
    gap: 8,
  },
  uploadButtonText: {
    fontSize: 15,
    fontWeight: '600',
    color: COLORS.accent,
  },
  bottomButtonContainer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: COLORS.surface,
    paddingHorizontal: 24,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  submitButton: {
    backgroundColor: COLORS.accent,
    borderRadius: 12,
    paddingVertical: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  submitButtonDisabled: {
    opacity: 0.6,
  },
  submitButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.surface,
  },

  // Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: COLORS.surface,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    maxHeight: '80%',
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 24,
    paddingVertical: 20,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
  },
  modalScroll: {
    padding: 16,
  },
  typeOption: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
    marginBottom: 12,
    backgroundColor: COLORS.surface,
    gap: 12,
  },
  typeOptionSelected: {
    borderColor: COLORS.accent,
    backgroundColor: COLORS.accentLight,
  },
  typeOptionIcon: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: COLORS.accentLight,
    justifyContent: 'center',
    alignItems: 'center',
  },
  typeOptionIconSelected: {
    backgroundColor: COLORS.accent,
  },
  typeOptionText: {
    flex: 1,
  },
  typeOptionLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
  },
  typeOptionLabelSelected: {
    color: COLORS.accent,
  },
  typeOptionDescription: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginTop: 4,
  },
});
