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
  Platform,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import * as DocumentPicker from 'expo-document-picker';
import { progress_service } from '../../services/progress_service';

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

// Allowed file types for progress report
const ALLOWED_MIME_TYPES = [
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/zip',
  'image/jpeg',
  'image/jpg',
  'image/png',
];

const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'zip', 'jpg', 'jpeg', 'png'];
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const MAX_FILES = 10;
const MIN_FILES = 1;
const MAX_PURPOSE_LENGTH = 1000;

interface ProgressFile {
  uri: string;
  name: string;
  type: string;
  size?: number;
}

interface ProgressReportFormProps {
  milestoneItemId: number;
  milestoneTitle: string;
  userId: number;
  onClose: () => void;
  onSuccess: () => void;
}

export default function ProgressReportForm({
  milestoneItemId,
  milestoneTitle,
  userId,
  onClose,
  onSuccess,
}: ProgressReportFormProps) {
  const insets = useSafeAreaInsets();
  const [purpose, setPurpose] = useState('');
  const [files, setFiles] = useState<ProgressFile[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors] = useState<{ purpose?: string; files?: string }>({});

  const getFileExtension = (filename: string) => {
    return filename.split('.').pop()?.toLowerCase() || '';
  };

  const isValidFileType = (file: { name: string; mimeType?: string }) => {
    const ext = getFileExtension(file.name);
    return ALLOWED_EXTENSIONS.includes(ext);
  };

  const formatFileSize = (bytes: number) => {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  };

  const getFileIcon = (filename: string) => {
    const ext = getFileExtension(filename);
    if (['jpg', 'jpeg', 'png'].includes(ext)) return 'image';
    if (['pdf'].includes(ext)) return 'file-text';
    if (['doc', 'docx'].includes(ext)) return 'file';
    if (['zip'].includes(ext)) return 'archive';
    return 'paperclip';
  };

  const handlePickFiles = async () => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ALLOWED_MIME_TYPES,
        multiple: true,
        copyToCacheDirectory: true,
      });

      if (result.canceled || !result.assets) {
        return;
      }

      // Validate files
      const validFiles: ProgressFile[] = [];
      const invalidFiles: string[] = [];

      for (const asset of result.assets) {
        // Check file type
        if (!isValidFileType(asset)) {
          invalidFiles.push(`${asset.name}: Invalid file type`);
          continue;
        }

        // Check file size
        if (asset.size && asset.size > MAX_FILE_SIZE) {
          invalidFiles.push(`${asset.name}: File exceeds 10MB limit`);
          continue;
        }

        validFiles.push({
          uri: asset.uri,
          name: asset.name,
          type: asset.mimeType || 'application/octet-stream',
          size: asset.size,
        });
      }

      // Check total file count
      const totalFiles = files.length + validFiles.length;
      if (totalFiles > MAX_FILES) {
        Alert.alert(
          'Too Many Files',
          `You can only upload up to ${MAX_FILES} files. Please remove some files first.`
        );
        return;
      }

      if (invalidFiles.length > 0) {
        Alert.alert(
          'Some Files Skipped',
          `The following files were not added:\n\n${invalidFiles.join('\n')}`
        );
      }

      if (validFiles.length > 0) {
        setFiles(prev => [...prev, ...validFiles]);
        setErrors(prev => ({ ...prev, files: undefined }));
      }
    } catch (error) {
      console.error('Error picking files:', error);
      Alert.alert('Error', 'Failed to pick files. Please try again.');
    }
  };

  const handleRemoveFile = (index: number) => {
    setFiles(prev => prev.filter((_, i) => i !== index));
  };

  const validateForm = (): boolean => {
    const newErrors: { purpose?: string; files?: string } = {};

    // Validate purpose
    if (!purpose.trim()) {
      newErrors.purpose = 'Purpose is required';
    } else if (purpose.length > MAX_PURPOSE_LENGTH) {
      newErrors.purpose = `Purpose must be less than ${MAX_PURPOSE_LENGTH} characters`;
    }

    // Validate files
    if (files.length < MIN_FILES) {
      newErrors.files = `At least ${MIN_FILES} file is required`;
    } else if (files.length > MAX_FILES) {
      newErrors.files = `Maximum ${MAX_FILES} files allowed`;
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
      const response = await progress_service.submit_progress(
        userId,
        milestoneItemId,
        purpose.trim(),
        files
      );

      if (response.success) {
        Alert.alert('Success', 'Progress report submitted successfully', [
          {
            text: 'OK',
            onPress: () => {
              onSuccess();
              onClose();
            },
          },
        ]);
      } else {
        Alert.alert('Error', response.message || 'Failed to submit progress report');
      }
    } catch (error) {
      console.error('Error submitting progress:', error);
      Alert.alert('Error', 'An unexpected error occurred. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="x" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Submit Progress Report</Text>
        <View style={styles.headerSpacer} />
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
      >
        {/* Milestone Info */}
        <View style={styles.milestoneInfo}>
          <Feather name="flag" size={20} color={COLORS.accent} />
          <Text style={styles.milestoneTitle} numberOfLines={2}>
            {milestoneTitle}
          </Text>
        </View>

        {/* Purpose Input */}
        <View style={styles.inputSection}>
          <Text style={styles.inputLabel}>
            Purpose <Text style={styles.required}>*</Text>
          </Text>
          <Text style={styles.inputDescription}>
            Describe the progress made on this milestone (max {MAX_PURPOSE_LENGTH} characters)
          </Text>
          <TextInput
            style={[
              styles.textArea,
              errors.purpose && styles.inputError,
            ]}
            value={purpose}
            onChangeText={text => {
              setPurpose(text);
              if (errors.purpose) {
                setErrors(prev => ({ ...prev, purpose: undefined }));
              }
            }}
            placeholder="Describe the work completed, materials used, or progress made..."
            placeholderTextColor={COLORS.textMuted}
            multiline
            numberOfLines={5}
            maxLength={MAX_PURPOSE_LENGTH}
            textAlignVertical="top"
          />
          <View style={styles.charCount}>
            <Text style={[
              styles.charCountText,
              purpose.length > MAX_PURPOSE_LENGTH * 0.9 && styles.charCountWarning,
            ]}>
              {purpose.length}/{MAX_PURPOSE_LENGTH}
            </Text>
          </View>
          {errors.purpose && (
            <Text style={styles.errorText}>{errors.purpose}</Text>
          )}
        </View>

        {/* Files Section */}
        <View style={styles.inputSection}>
          <Text style={styles.inputLabel}>
            Attachments <Text style={styles.required}>*</Text>
          </Text>
          <Text style={styles.inputDescription}>
            Upload {MIN_FILES}-{MAX_FILES} files (PDF, DOC, DOCX, ZIP, JPG, JPEG, PNG). Max 10MB each.
          </Text>

          {/* File List */}
          {files.length > 0 && (
            <View style={styles.fileList}>
              {files.map((file, index) => (
                <View key={index} style={styles.fileItem}>
                  <View style={styles.fileIcon}>
                    <Feather name={getFileIcon(file.name)} size={20} color={COLORS.primary} />
                  </View>
                  <View style={styles.fileInfo}>
                    <Text style={styles.fileName} numberOfLines={1}>
                      {file.name}
                    </Text>
                    {file.size && (
                      <Text style={styles.fileSize}>
                        {formatFileSize(file.size)}
                      </Text>
                    )}
                  </View>
                  <TouchableOpacity
                    onPress={() => handleRemoveFile(index)}
                    style={styles.removeFileButton}
                  >
                    <Feather name="x-circle" size={20} color={COLORS.error} />
                  </TouchableOpacity>
                </View>
              ))}
            </View>
          )}

          {/* Add Files Button */}
          {files.length < MAX_FILES && (
            <TouchableOpacity
              style={[styles.addFilesButton, errors.files && styles.addFilesButtonError]}
              onPress={handlePickFiles}
            >
              <Feather name="upload" size={24} color={COLORS.accent} />
              <Text style={styles.addFilesText}>
                {files.length === 0 ? 'Add Files' : 'Add More Files'}
              </Text>
              <Text style={styles.filesCount}>
                {files.length}/{MAX_FILES} files
              </Text>
            </TouchableOpacity>
          )}

          {errors.files && (
            <Text style={styles.errorText}>{errors.files}</Text>
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
              <Text style={styles.submitButtonText}>Submit Report</Text>
            </>
          )}
        </TouchableOpacity>
      </View>
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
  milestoneInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.accentLight,
    padding: 16,
    borderRadius: 12,
    marginBottom: 24,
    gap: 12,
  },
  milestoneTitle: {
    flex: 1,
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
  },
  inputSection: {
    marginBottom: 24,
  },
  inputLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 4,
  },
  required: {
    color: COLORS.error,
  },
  inputDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    marginBottom: 12,
  },
  textArea: {
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 12,
    padding: 16,
    fontSize: 15,
    color: COLORS.text,
    backgroundColor: COLORS.surface,
    minHeight: 120,
  },
  inputError: {
    borderColor: COLORS.error,
  },
  charCount: {
    alignItems: 'flex-end',
    marginTop: 8,
  },
  charCountText: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  charCountWarning: {
    color: COLORS.warning,
  },
  errorText: {
    fontSize: 13,
    color: COLORS.error,
    marginTop: 8,
  },
  fileList: {
    gap: 8,
    marginBottom: 12,
  },
  fileItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  fileIcon: {
    width: 40,
    height: 40,
    borderRadius: 8,
    backgroundColor: COLORS.primaryLight,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  fileInfo: {
    flex: 1,
  },
  fileName: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 2,
  },
  fileSize: {
    fontSize: 12,
    color: COLORS.textMuted,
  },
  removeFileButton: {
    padding: 4,
  },
  addFilesButton: {
    borderWidth: 2,
    borderColor: COLORS.border,
    borderStyle: 'dashed',
    borderRadius: 12,
    padding: 24,
    alignItems: 'center',
    backgroundColor: COLORS.borderLight,
  },
  addFilesButtonError: {
    borderColor: COLORS.error,
  },
  addFilesText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.accent,
    marginTop: 8,
  },
  filesCount: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 4,
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
});
