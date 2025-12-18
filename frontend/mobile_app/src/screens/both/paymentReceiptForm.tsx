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
  Image,
  Platform,
  Modal,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import * as DocumentPicker from 'expo-document-picker';
import { payment_service } from '../../services/payment_service';
import DateTimePicker from '@react-native-community/datetimepicker';

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

const PAYMENT_TYPES = [
  { value: 'cash', label: 'Cash' },
  { value: 'bank_transfer', label: 'Bank Transfer' },
  { value: 'online_payment', label: 'Online Payment (GCash, Maya, etc.)' },
  { value: 'check', label: 'Check' },
];

interface ReceiptFile {
  uri: string;
  name: string;
  type: string;
}

interface PaymentReceiptFormProps {
  milestoneItemId: number;
  projectId: number;
  milestoneTitle: string;
  onClose: () => void;
  onSuccess: () => void;
}

export default function paymentReceiptForm({
  milestoneItemId,
  projectId,
  milestoneTitle,
  onClose,
  onSuccess,
}: PaymentReceiptFormProps) {
  const insets = useSafeAreaInsets();
  const [amount, setAmount] = useState('');
  const [paymentType, setPaymentType] = useState('');
  const [transactionNumber, setTransactionNumber] = useState('');
  const [transactionDate, setTransactionDate] = useState<Date>(new Date());
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [receiptPhoto, setReceiptPhoto] = useState<ReceiptFile | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errors, setErrors] = useState<{ 
    amount?: string; 
    paymentType?: string; 
    transactionDate?: string;
    receiptPhoto?: string;
  }>({});

  const formatDateForDisplay = (date: Date) => {
    return date.toLocaleDateString('en-US', { 
      weekday: 'long',
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    });
  };

  const formatDateForAPI = (date: Date) => {
    // Format as YYYY-MM-DD for API
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };

  const handleDateChange = (event: any, selectedDate?: Date) => {
    setShowDatePicker(Platform.OS === 'ios'); // Keep open on iOS, close on Android
    if (selectedDate) {
      setTransactionDate(selectedDate);
      if (errors.transactionDate) {
        setErrors(prev => ({ ...prev, transactionDate: undefined }));
      }
    }
  };

  const handlePickImage = async () => {
    try {
      // Request permission
      const permissionResult = await ImagePicker.requestMediaLibraryPermissionsAsync();
      
      if (!permissionResult.granted) {
        Alert.alert('Permission Required', 'Please allow access to your photo library to upload receipt images.');
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        quality: 0.8,
      });

      if (!result.canceled && result.assets && result.assets[0]) {
        const asset = result.assets[0];
        setReceiptPhoto({
          uri: asset.uri,
          name: `receipt_${Date.now()}.jpg`,
          type: 'image/jpeg',
        });
        setErrors(prev => ({ ...prev, receiptPhoto: undefined }));
      }
    } catch (error) {
      console.error('Error picking image:', error);
      Alert.alert('Error', 'Failed to pick image. Please try again.');
    }
  };

  const handleTakePhoto = async () => {
    try {
      const permissionResult = await ImagePicker.requestCameraPermissionsAsync();
      
      if (!permissionResult.granted) {
        Alert.alert('Permission Required', 'Please allow access to your camera to take receipt photos.');
        return;
      }

      const result = await ImagePicker.launchCameraAsync({
        allowsEditing: true,
        quality: 0.8,
      });

      if (!result.canceled && result.assets && result.assets[0]) {
        const asset = result.assets[0];
        setReceiptPhoto({
          uri: asset.uri,
          name: `receipt_${Date.now()}.jpg`,
          type: 'image/jpeg',
        });
        setErrors(prev => ({ ...prev, receiptPhoto: undefined }));
      }
    } catch (error) {
      console.error('Error taking photo:', error);
      Alert.alert('Error', 'Failed to take photo. Please try again.');
    }
  };

  const handlePickDocument = async () => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ['image/*', 'application/pdf'],
        copyToCacheDirectory: true,
      });

      if (!result.canceled && result.assets && result.assets[0]) {
        const asset = result.assets[0];
        setReceiptPhoto({
          uri: asset.uri,
          name: asset.name,
          type: asset.mimeType || 'application/octet-stream',
        });
        setErrors(prev => ({ ...prev, receiptPhoto: undefined }));
      }
    } catch (error) {
      console.error('Error picking document:', error);
      Alert.alert('Error', 'Failed to pick document. Please try again.');
    }
  };

  const showImageOptions = () => {
    Alert.alert(
      'Upload Receipt',
      'Choose how to upload your payment receipt',
      [
        { text: 'Take Photo', onPress: handleTakePhoto },
        { text: 'Choose from Gallery', onPress: handlePickImage },
        { text: 'Pick Document', onPress: handlePickDocument },
        { text: 'Cancel', style: 'cancel' },
      ]
    );
  };

  const validateForm = (): boolean => {
    const newErrors: typeof errors = {};

    // Validate amount
    const amountNum = parseFloat(amount);
    if (!amount.trim() || isNaN(amountNum) || amountNum <= 0) {
      newErrors.amount = 'Please enter a valid amount';
    }

    // Validate payment type
    if (!paymentType) {
      newErrors.paymentType = 'Please select a payment type';
    }

    // Validate transaction date
    if (!transactionDate || isNaN(transactionDate.getTime())) {
      newErrors.transactionDate = 'Please select a valid transaction date';
    }

    // Receipt photo is optional but recommended
    // if (!receiptPhoto) {
    //   newErrors.receiptPhoto = 'Please upload a receipt photo';
    // }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (!validateForm()) {
      return;
    }

    setIsSubmitting(true);

    try {
      const response = await payment_service.upload_payment(
        milestoneItemId,
        projectId,
        parseFloat(amount),
        paymentType,
        transactionNumber || null,
        formatDateForAPI(transactionDate),
        receiptPhoto
      );

      if (response.success) {
        Alert.alert('Success', 'Payment receipt uploaded successfully', [
          {
            text: 'OK',
            onPress: () => {
              onSuccess();
              onClose();
            },
          },
        ]);
      } else {
        Alert.alert('Error', response.message || 'Failed to upload payment receipt');
      }
    } catch (error) {
      console.error('Error submitting payment:', error);
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
        <Text style={styles.headerTitle}>Send Payment Receipt</Text>
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

        {/* Amount Input */}
        <View style={styles.inputSection}>
          <Text style={styles.inputLabel}>
            Amount <Text style={styles.required}>*</Text>
          </Text>
          <View style={[styles.inputRow, errors.amount && styles.inputError]}>
            <Text style={styles.currencySymbol}>₱</Text>
            <TextInput
              style={styles.amountInput}
              value={amount}
              onChangeText={text => {
                setAmount(text.replace(/[^0-9.]/g, ''));
                if (errors.amount) {
                  setErrors(prev => ({ ...prev, amount: undefined }));
                }
              }}
              placeholder="0.00"
              placeholderTextColor={COLORS.textMuted}
              keyboardType="decimal-pad"
            />
          </View>
          {errors.amount && (
            <Text style={styles.errorText}>{errors.amount}</Text>
          )}
        </View>

        {/* Payment Type Selection */}
        <View style={styles.inputSection}>
          <Text style={styles.inputLabel}>
            Payment Method <Text style={styles.required}>*</Text>
          </Text>
          <View style={styles.paymentTypeGrid}>
            {PAYMENT_TYPES.map((type) => (
              <TouchableOpacity
                key={type.value}
                style={[
                  styles.paymentTypeButton,
                  paymentType === type.value && styles.paymentTypeButtonSelected,
                ]}
                onPress={() => {
                  setPaymentType(type.value);
                  if (errors.paymentType) {
                    setErrors(prev => ({ ...prev, paymentType: undefined }));
                  }
                }}
              >
                <Text style={[
                  styles.paymentTypeText,
                  paymentType === type.value && styles.paymentTypeTextSelected,
                ]}>
                  {type.label}
                </Text>
              </TouchableOpacity>
            ))}
          </View>
          {errors.paymentType && (
            <Text style={styles.errorText}>{errors.paymentType}</Text>
          )}
        </View>

        {/* Transaction Number Input */}
        <View style={styles.inputSection}>
          <Text style={styles.inputLabel}>Transaction/Reference Number</Text>
          <TextInput
            style={styles.textInput}
            value={transactionNumber}
            onChangeText={setTransactionNumber}
            placeholder="Enter transaction number (optional)"
            placeholderTextColor={COLORS.textMuted}
          />
        </View>

        {/* Transaction Date Input */}
        <View style={styles.inputSection}>
          <Text style={styles.inputLabel}>
            Transaction Date <Text style={styles.required}>*</Text>
          </Text>
          <TouchableOpacity
            style={[styles.datePickerButton, errors.transactionDate && styles.inputError]}
            onPress={() => setShowDatePicker(true)}
          >
            <Feather name="calendar" size={20} color={COLORS.text} />
            <Text style={styles.datePickerText}>
              {formatDateForDisplay(transactionDate)}
            </Text>
            <Feather name="chevron-down" size={20} color={COLORS.textSecondary} />
          </TouchableOpacity>
          {errors.transactionDate && (
            <Text style={styles.errorText}>{errors.transactionDate}</Text>
          )}
        </View>

        {/* Receipt Photo Upload */}
        <View style={styles.inputSection}>
          <Text style={styles.inputLabel}>Receipt Photo</Text>
          <Text style={styles.inputDescription}>
            Upload a photo or PDF of your payment receipt (optional but recommended)
          </Text>

          {receiptPhoto ? (
            <View style={styles.receiptPreviewContainer}>
              {receiptPhoto.type.startsWith('image/') ? (
                <Image
                  source={{ uri: receiptPhoto.uri }}
                  style={styles.receiptPreview}
                  resizeMode="cover"
                />
              ) : (
                <View style={styles.pdfPreview}>
                  <Feather name="file-text" size={40} color={COLORS.primary} />
                  <Text style={styles.pdfFileName} numberOfLines={1}>{receiptPhoto.name}</Text>
                </View>
              )}
              <TouchableOpacity
                style={styles.removeReceiptButton}
                onPress={() => setReceiptPhoto(null)}
              >
                <Feather name="x-circle" size={24} color={COLORS.error} />
              </TouchableOpacity>
            </View>
          ) : (
            <TouchableOpacity
              style={[styles.uploadButton, errors.receiptPhoto && styles.uploadButtonError]}
              onPress={showImageOptions}
            >
              <Feather name="camera" size={24} color={COLORS.accent} />
              <Text style={styles.uploadButtonText}>Upload Receipt</Text>
            </TouchableOpacity>
          )}

          {errors.receiptPhoto && (
            <Text style={styles.errorText}>{errors.receiptPhoto}</Text>
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
              <Text style={styles.submitButtonText}>Submit Payment Receipt</Text>
            </>
          )}
        </TouchableOpacity>
      </View>

      {/* Date Picker Modal/Native */}
      {showDatePicker && (
        Platform.OS === 'ios' ? (
          <Modal
            visible={showDatePicker}
            transparent={true}
            animationType="slide"
            onRequestClose={() => setShowDatePicker(false)}
          >
            <View style={styles.datePickerModalOverlay}>
              <View style={styles.datePickerModalContent}>
                <View style={styles.datePickerHeader}>
                  <TouchableOpacity onPress={() => setShowDatePicker(false)}>
                    <Text style={styles.datePickerCancelText}>Cancel</Text>
                  </TouchableOpacity>
                  <Text style={styles.datePickerHeaderTitle}>Select Date</Text>
                  <TouchableOpacity onPress={() => setShowDatePicker(false)}>
                    <Text style={styles.datePickerDoneText}>Done</Text>
                  </TouchableOpacity>
                </View>
                <DateTimePicker
                  value={transactionDate}
                  mode="date"
                  display="spinner"
                  onChange={handleDateChange}
                  maximumDate={new Date()}
                  textColor={COLORS.text}
                />
              </View>
            </View>
          </Modal>
        ) : (
          <DateTimePicker
            value={transactionDate}
            mode="date"
            display="default"
            onChange={handleDateChange}
            maximumDate={new Date()}
          />
        )
      )}
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
  inputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 12,
    backgroundColor: COLORS.surface,
  },
  currencySymbol: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
    paddingLeft: 16,
    paddingRight: 8,
  },
  amountInput: {
    flex: 1,
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
    paddingVertical: 14,
    paddingRight: 16,
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
  inputError: {
    borderColor: COLORS.error,
  },
  errorText: {
    fontSize: 13,
    color: COLORS.error,
    marginTop: 8,
  },
  paymentTypeGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  paymentTypeButton: {
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: COLORS.border,
    backgroundColor: COLORS.surface,
  },
  paymentTypeButtonSelected: {
    backgroundColor: COLORS.accent,
    borderColor: COLORS.accent,
  },
  paymentTypeText: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.textSecondary,
  },
  paymentTypeTextSelected: {
    color: COLORS.surface,
  },
  uploadButton: {
    borderWidth: 2,
    borderColor: COLORS.border,
    borderStyle: 'dashed',
    borderRadius: 12,
    padding: 32,
    alignItems: 'center',
    backgroundColor: COLORS.borderLight,
  },
  uploadButtonError: {
    borderColor: COLORS.error,
  },
  uploadButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.accent,
    marginTop: 8,
  },
  receiptPreviewContainer: {
    position: 'relative',
    borderRadius: 12,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  receiptPreview: {
    width: '100%',
    height: 200,
  },
  pdfPreview: {
    width: '100%',
    height: 120,
    backgroundColor: COLORS.borderLight,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 16,
  },
  pdfFileName: {
    marginTop: 8,
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  removeReceiptButton: {
    position: 'absolute',
    top: 8,
    right: 8,
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 4,
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
  datePickerButton: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 12,
    padding: 14,
    backgroundColor: COLORS.surface,
    gap: 12,
  },
  datePickerText: {
    flex: 1,
    fontSize: 15,
    color: COLORS.text,
    fontWeight: '500',
  },
  datePickerModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  datePickerModalContent: {
    backgroundColor: COLORS.surface,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    paddingBottom: 20,
  },
  datePickerHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  datePickerHeaderTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
  },
  datePickerCancelText: {
    fontSize: 16,
    color: COLORS.textSecondary,
  },
  datePickerDoneText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.accent,
  },
});
