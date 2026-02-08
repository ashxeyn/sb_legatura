// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  StatusBar,
  Alert,
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  Modal,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Feather, Ionicons } from '@expo/vector-icons';
import * as DocumentPicker from 'expo-document-picker';
import { projects_service } from '../../services/projects_service';

// Color palette
const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
  success: '#10B981',
  successLight: '#D1FAE5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
  errorLight: '#FEE2E2',
  info: '#3B82F6',
  infoLight: '#DBEAFE',
  background: '#F8FAFC',
  surface: '#FFFFFF',
  text: '#0F172A',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
};

interface Project {
  project_id: number;
  project_title: string;
  project_description?: string;
  project_location?: string;
  budget_range_min?: number;
  budget_range_max?: number;
  property_type?: string;
  type_name?: string;
  bidding_deadline?: string;
  owner_name?: string;
  owner_profile_pic?: string;
  bids_count?: number;
  created_at?: string;
}

interface PlaceBidProps {
  project: Project;
  userId: number;
  onClose: () => void;
  onBidSubmitted?: () => void;
}

export default function PlaceBid({ project, userId, onClose, onBidSubmitted }: PlaceBidProps) {
  const insets = useSafeAreaInsets();
  const [proposedCost, setProposedCost] = useState('');
  const [estimatedTimeline, setEstimatedTimeline] = useState('');
  const [contractorNotes, setContractorNotes] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [existingBid, setExistingBid] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [bidFiles, setBidFiles] = useState<Array<any>>([]);
  const [showBudgetWarning, setShowBudgetWarning] = useState(false);
  const [budgetWarningMessage, setBudgetWarningMessage] = useState<{type: 'high' | 'low', message: string} | null>(null);
  const [pendingSubmission, setPendingSubmission] = useState(false);

  const statusBarHeight = insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44);

  // Check for existing bid
  useEffect(() => {
    checkExistingBid();
  }, []);

  const checkExistingBid = async () => {
    try {
      setIsLoading(true);
      const response = await projects_service.get_my_bid(project.project_id, userId);

      // The API response is wrapped: response.data contains the backend response
      // Backend returns { success: true, data: null } when no bid exists
      // Backend returns { success: true, data: {bid object} } when bid exists
      const backendResponse = response.data;

      if (response.success && backendResponse?.data) {
        // Only set existing bid if the data is not null
        setExistingBid(backendResponse.data);
        // Pre-fill form with existing bid data
        setProposedCost(backendResponse.data.proposed_cost?.toString() || '');
        setEstimatedTimeline(backendResponse.data.estimated_timeline?.toString() || '');
        setContractorNotes(backendResponse.data.contractor_notes || '');
      } else {
        // No existing bid found - user can submit a new one
        setExistingBid(null);
      }
    } catch (error) {
      console.error('Error checking existing bid:', error);
      setExistingBid(null);
    } finally {
      setIsLoading(false);
    }
  };

  const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(value);
  };

  // Format number with commas as user types
  const formatNumberWithCommas = (text: string): string => {
    // Remove all non-numeric characters
    const numericValue = text.replace(/[^0-9]/g, '');

    // If empty, return empty string
    if (!numericValue) return '';

    // Format with commas
    return new Intl.NumberFormat('en-PH').format(parseInt(numericValue, 10));
  };

  const formatFileSize = (bytes: number) => {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  };

  // Handle proposed cost change with formatting
  const handleProposedCostChange = (text: string) => {
    const formatted = formatNumberWithCommas(text);
    setProposedCost(formatted);
  };

  const formatBudgetRange = (): string => {
    if (!project.budget_range_min && !project.budget_range_max) return 'Not specified';
    if (!project.budget_range_min) return `Up to ${formatCurrency(project.budget_range_max!)}`;
    if (!project.budget_range_max) return `From ${formatCurrency(project.budget_range_min)}`;
    return `${formatCurrency(project.budget_range_min)} - ${formatCurrency(project.budget_range_max)}`;
  };

  const getDaysRemaining = (): number | null => {
    if (!project.bidding_deadline) return null;
    const deadline = new Date(project.bidding_deadline);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    deadline.setHours(0, 0, 0, 0);
    const diffTime = deadline.getTime() - today.getTime();
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  };

  const validateForm = (): boolean => {
    if (!proposedCost.trim()) {
      Alert.alert('Validation Error', 'Please enter your proposed cost.');
      return false;
    }
    const cost = parseFloat(proposedCost.replace(/,/g, ''));
    if (isNaN(cost) || cost <= 0) {
      Alert.alert('Validation Error', 'Please enter a valid proposed cost.');
      return false;
    }
    if (!estimatedTimeline.trim()) {
      Alert.alert('Validation Error', 'Please enter your estimated timeline.');
      return false;
    }
    const timeline = parseInt(estimatedTimeline);
    if (isNaN(timeline) || timeline < 1) {
      Alert.alert('Validation Error', 'Please enter a valid timeline (minimum 1 month).');
      return false;
    }
    return true;
  };

  const handlePickFiles = async () => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ['image/*', 'application/pdf'],
        copyToCacheDirectory: true,
        multiple: true,
      });

      if (!result.canceled && result.assets) {
        const newFiles = result.assets.map((asset) => ({
          uri: asset.uri,
          name: asset.name,
          type: asset.mimeType || 'application/octet-stream',
          size: asset.size || 0,
        }));

        const totalFiles = bidFiles.length + newFiles.length;
        if (totalFiles > 5) {
          Alert.alert('Too Many Files', 'You can upload a maximum of 5 files for a bid.');
          return;
        }

        const oversized = newFiles.filter((f) => f.size > 5 * 1024 * 1024);
        if (oversized.length > 0) {
          Alert.alert('File Too Large', `Some files exceed 5MB: ${oversized.map(f => f.name).join(', ')}`);
          return;
        }

        setBidFiles([...bidFiles, ...newFiles]);
      }
    } catch (error) {
      console.error('Error picking bid files:', error);
      Alert.alert('Error', 'Failed to pick files. Please try again.');
    }
  };

  const handleRemoveBidFile = (index: number) => {
    setBidFiles(bidFiles.filter((_, i) => i !== index));
  };

  const checkBudgetRange = (): { outOfRange: boolean; type?: 'high' | 'low'; message?: string } => {
    const cost = parseFloat(proposedCost.replace(/,/g, ''));
    const minBudget = project.budget_range_min;
    const maxBudget = project.budget_range_max;

    // Only check if budget range is defined
    if (minBudget || maxBudget) {
      if (maxBudget && cost > maxBudget) {
        return {
          outOfRange: true,
          type: 'high',
          message: `Your bid of ${formatCurrency(cost)} is higher than the maximum budget of ${formatCurrency(maxBudget)}. The property owner may prefer lower bids.`
        };
      } else if (minBudget && cost < minBudget) {
        return {
          outOfRange: true,
          type: 'low',
          message: `Your bid of ${formatCurrency(cost)} is lower than the minimum budget of ${formatCurrency(minBudget)}. This may raise concerns about quality or scope.`
        };
      }
    }

    return { outOfRange: false };
  };

  const handleSubmit = async () => {
    if (!validateForm()) return;

    // Check if already has a non-cancelled bid
    if (existingBid && existingBid.bid_status !== 'cancelled') {
      Alert.alert('Already Submitted', 'You have already submitted a bid for this project.');
      return;
    }

    // Check budget range before submitting
    const budgetCheck = checkBudgetRange();
    if (budgetCheck.outOfRange) {
      // Show warning modal and block submission
      setBudgetWarningMessage({
        type: budgetCheck.type!,
        message: budgetCheck.message!
      });
      setShowBudgetWarning(true);
      setPendingSubmission(true);
      return;
    }

    // Proceed with submission
    proceedWithSubmission();
  };

  const proceedWithSubmission = async () => {
    setIsSubmitting(true);
    setShowBudgetWarning(false);
    setPendingSubmission(false);

    try {
      const response = await projects_service.submit_bid(project.project_id, userId, {
        proposed_cost: parseFloat(proposedCost.replace(/,/g, '')),
        estimated_timeline: estimatedTimeline,
        contractor_notes: contractorNotes.trim() || undefined,
        bidFiles: bidFiles,
      });

      if (response.success) {
        Alert.alert(
          'Bid Submitted!',
          'Your bid has been submitted successfully. The property owner will review your proposal.',
          [
            {
              text: 'OK',
              onPress: () => {
                if (onBidSubmitted) onBidSubmitted();
                onClose();
              },
            },
          ]
        );
      } else {
        Alert.alert('Error', response.message || 'Failed to submit bid. Please try again.');
      }
    } catch (error) {
      console.error('Error submitting bid:', error);
      Alert.alert('Error', 'An unexpected error occurred. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleEditBid = () => {
    setShowBudgetWarning(false);
    setPendingSubmission(false);
    // User stays on the form to edit
  };

  const handleContinueSubmission = () => {
    proceedWithSubmission();
  };

  const daysRemaining = getDaysRemaining();

  if (isLoading) {
    return (
      <SafeAreaView style={[styles.container, { paddingTop: statusBarHeight }]}>
        <StatusBar hidden={true} />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Loading...</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={[styles.container, { paddingTop: statusBarHeight }]}>
      <StatusBar hidden={true} />

      {/* Budget Warning Modal */}
      <Modal
        visible={showBudgetWarning}
        transparent={true}
        animationType="fade"
        onRequestClose={() => setShowBudgetWarning(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={[styles.modalIconContainer, { backgroundColor: budgetWarningMessage?.type === 'high' ? COLORS.warningLight : COLORS.infoLight }]}>
              <MaterialIcons 
                name={budgetWarningMessage?.type === 'high' ? 'trending-up' : 'trending-down'} 
                size={32} 
                color={budgetWarningMessage?.type === 'high' ? COLORS.warning : COLORS.info} 
              />
            </View>
            
            <Text style={styles.modalTitle}>
              {budgetWarningMessage?.type === 'high' ? 'Bid Above Budget Range' : 'Bid Below Budget Range'}
            </Text>
            
            <Text style={styles.modalMessage}>
              {budgetWarningMessage?.message}
            </Text>
            
            <Text style={styles.modalHint}>
              Would you like to continue with this bid amount or go back to edit it?
            </Text>
            
            <View style={styles.modalButtonsRow}>
              <TouchableOpacity
                style={[styles.modalButton, styles.modalButtonSecondary]}
                onPress={handleEditBid}
                activeOpacity={0.8}
              >
                <Text style={[styles.modalButtonText, styles.modalButtonTextSecondary]}>Edit</Text>
              </TouchableOpacity>
              
              <TouchableOpacity
                style={[styles.modalButton, styles.modalButtonPrimary]}
                onPress={handleContinueSubmission}
                activeOpacity={0.8}
              >
                <Text style={styles.modalButtonText}>Continue</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Place Bid</Text>
        <View style={{ width: 40 }} />
      </View>

      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        style={{ flex: 1 }}
      >
        <ScrollView
          style={styles.scrollView}
          contentContainerStyle={styles.scrollContent}
          showsVerticalScrollIndicator={false}
          keyboardShouldPersistTaps="handled"
        >
          {/* Project Summary Card */}
          <View style={styles.projectCard}>
            <Text style={styles.projectTitle}>{project.project_title}</Text>

            <View style={styles.projectDetails}>
              {project.type_name && (
                <View style={styles.detailRow}>
                  <Feather name="briefcase" size={16} color={COLORS.textSecondary} />
                  <Text style={styles.detailText}>{project.type_name}</Text>
                </View>
              )}

              {project.project_location && (
                <View style={styles.detailRow}>
                  <Feather name="map-pin" size={16} color={COLORS.textSecondary} />
                  <Text style={styles.detailText}>{project.project_location}</Text>
                </View>
              )}

              <View style={styles.detailRow}>
                <Feather name="dollar-sign" size={16} color={COLORS.textSecondary} />
                <Text style={styles.detailText}>Budget: {formatBudgetRange()}</Text>
              </View>

              {daysRemaining !== null && (
                <View style={styles.detailRow}>
                  <MaterialIcons name="access-time" size={16} color={daysRemaining <= 3 ? COLORS.error : COLORS.warning} />
                  <Text style={[styles.detailText, daysRemaining <= 3 && { color: COLORS.error }]}>
                    {daysRemaining > 0 ? `${daysRemaining} days left to bid` : 'Deadline today'}
                  </Text>
                </View>
              )}
            </View>

            {project.owner_name && (
              <View style={styles.ownerRow}>
                <Feather name="user" size={14} color={COLORS.textMuted} />
                <Text style={styles.ownerText}>Posted by {project.owner_name}</Text>
              </View>
            )}
          </View>

          {/* Existing Bid Warning */}
          {existingBid && existingBid.bid_status !== 'cancelled' && (
            <View style={styles.warningCard}>
              <MaterialIcons name="info" size={20} color={COLORS.warning} />
              <View style={styles.warningContent}>
                <Text style={styles.warningTitle}>You've Already Submitted a Bid</Text>
                <Text style={styles.warningText}>
                  Your current bid: {formatCurrency(existingBid.proposed_cost)} • {existingBid.estimated_timeline} months
                </Text>
                <Text style={styles.warningStatus}>Status: {existingBid.bid_status}</Text>
              </View>
            </View>
          )}

          {/* Bid Form */}
          <View style={styles.formSection}>
            <Text style={styles.sectionTitle}>Your Bid Details</Text>

            {/* Proposed Cost */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Proposed Cost (PHP) *</Text>
              <View style={styles.inputContainer}>
                <Text style={styles.currencyPrefix}>₱</Text>
                <TextInput
                  style={styles.input}
                  value={proposedCost}
                  onChangeText={handleProposedCostChange}
                  placeholder="Enter your proposed amount"
                  placeholderTextColor={COLORS.textMuted}
                  keyboardType="numeric"
                  editable={!existingBid || existingBid.bid_status === 'cancelled'}
                />
              </View>
              <Text style={styles.inputHint}>
                Budget range: {formatBudgetRange()}
              </Text>
            </View>

            {/* Estimated Timeline */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Estimated Timeline (Months) *</Text>
              <View style={styles.inputContainer}>
                <MaterialIcons name="schedule" size={20} color={COLORS.textMuted} style={{ marginRight: 8 }} />
                <TextInput
                  style={styles.input}
                  value={estimatedTimeline}
                  onChangeText={setEstimatedTimeline}
                  placeholder="e.g., 3"
                  placeholderTextColor={COLORS.textMuted}
                  keyboardType="number-pad"
                  editable={!existingBid || existingBid.bid_status === 'cancelled'}
                />
                <Text style={styles.inputSuffix}>months</Text>
              </View>
              <Text style={styles.inputHint}>
                How long will it take to complete this project?
              </Text>
            </View>

            {/* Attachments */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Attachments (optional)</Text>
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 12 }}>
                <TouchableOpacity style={styles.addFileButton} onPress={handlePickFiles} activeOpacity={0.8}>
                  <Feather name="paperclip" size={16} color={COLORS.text} />
                  <Text style={{ marginLeft: 8, color: COLORS.text }}>Add Files</Text>
                </TouchableOpacity>
                <Text style={styles.inputHint}>Max 5 files, 5MB each</Text>
              </View>

              {bidFiles.length > 0 && (
                <View style={{ marginTop: 12, gap: 8 }}>
                  {bidFiles.map((file, idx) => (
                    <View key={idx} style={styles.fileRow}>
                      <Text style={styles.fileName}>{file.name} • {formatFileSize(file.size)}</Text>
                      <TouchableOpacity onPress={() => handleRemoveBidFile(idx)} style={styles.removeFileButton}>
                        <Feather name="x" size={16} color={COLORS.error} />
                      </TouchableOpacity>
                    </View>
                  ))}
                </View>
              )}
            </View>

            {/* Contractor Notes */}
            <View style={styles.inputGroup}>
              <Text style={styles.inputLabel}>Additional Notes (Optional)</Text>
              <TextInput
                style={styles.textArea}
                value={contractorNotes}
                onChangeText={setContractorNotes}
                placeholder="Add any additional details about your proposal, approach, or qualifications..."
                placeholderTextColor={COLORS.textMuted}
                multiline
                numberOfLines={5}
                textAlignVertical="top"
                editable={!existingBid || existingBid.bid_status === 'cancelled'}
              />
              <Text style={styles.charCount}>{contractorNotes.length}/5000</Text>
            </View>
          </View>

            {/* Tips Section */}
          <View style={styles.tipsCard}>
            <Text style={styles.tipsTitle}>
              <MaterialIcons name="lightbulb-outline" size={18} color={COLORS.info} /> Tips for a Winning Bid
            </Text>
            <View style={styles.tipsList}>
              <Text style={styles.tipItem}>• Be competitive but realistic with your pricing</Text>
              <Text style={styles.tipItem}>• Provide a clear timeline breakdown if possible</Text>
              <Text style={styles.tipItem}>• Highlight your relevant experience in the notes</Text>
              <Text style={styles.tipItem}>• Respond promptly to any owner inquiries</Text>
            </View>
          </View>
        </ScrollView>

        {/* Submit Button */}
        <View style={styles.submitContainer}>
          {(!existingBid || existingBid.bid_status === 'cancelled') ? (
            <TouchableOpacity
              style={[styles.submitButton, isSubmitting && styles.submitButtonDisabled]}
              onPress={handleSubmit}
              disabled={isSubmitting}
              activeOpacity={0.8}
            >
              {isSubmitting ? (
                <ActivityIndicator size="small" color={COLORS.surface} />
              ) : (
                <>
                  <Feather name="send" size={20} color={COLORS.surface} />
                  <Text style={styles.submitButtonText}>Submit Bid</Text>
                </>
              )}
            </TouchableOpacity>
          ) : (
            <View style={styles.alreadySubmittedContainer}>
              <MaterialIcons name="check-circle" size={24} color={COLORS.success} />
              <Text style={styles.alreadySubmittedText}>Bid Already Submitted</Text>
            </View>
          )}
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 12,
    fontSize: 16,
    color: COLORS.textSecondary,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  backButton: {
    padding: 8,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: COLORS.text,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
    paddingBottom: 100,
  },
  projectCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  projectTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 12,
  },
  projectDetails: {
    gap: 8,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  detailText: {
    fontSize: 14,
    color: COLORS.textSecondary,
  },
  ownerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.borderLight,
  },
  ownerText: {
    fontSize: 13,
    color: COLORS.textMuted,
  },
  warningCard: {
    flexDirection: 'row',
    backgroundColor: COLORS.warningLight,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    gap: 12,
  },
  warningContent: {
    flex: 1,
  },
  warningTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 4,
  },
  warningText: {
    fontSize: 13,
    color: COLORS.textSecondary,
  },
  warningStatus: {
    fontSize: 12,
    color: COLORS.warning,
    fontWeight: '500',
    marginTop: 4,
    textTransform: 'capitalize',
  },
  formSection: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 16,
  },
  inputGroup: {
    marginBottom: 20,
  },
  inputLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.text,
    marginBottom: 8,
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 8,
    paddingHorizontal: 12,
    height: 48,
  },
  currencyPrefix: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
    marginRight: 4,
  },
  input: {
    flex: 1,
    fontSize: 16,
    color: COLORS.text,
    paddingVertical: 0,
  },
  inputSuffix: {
    fontSize: 14,
    color: COLORS.textMuted,
    marginLeft: 8,
  },
  inputHint: {
    fontSize: 12,
    color: COLORS.textMuted,
    marginTop: 6,
  },
  textArea: {
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 8,
    padding: 12,
    fontSize: 15,
    color: COLORS.text,
    minHeight: 120,
  },
  charCount: {
    fontSize: 12,
    color: COLORS.textMuted,
    textAlign: 'right',
    marginTop: 4,
  },
  tipsCard: {
    backgroundColor: COLORS.infoLight,
    borderRadius: 12,
    padding: 16,
  },
  tipsTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 12,
  },
  tipsList: {
    gap: 6,
  },
  tipItem: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 20,
  },
  addFileButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
  },
  fileRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  fileName: {
    fontSize: 13,
    color: COLORS.textSecondary,
    flex: 1,
  },
  removeFileButton: {
    marginLeft: 12,
    padding: 6,
  },
  submitContainer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: COLORS.surface,
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  submitButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.primary,
    borderRadius: 12,
    paddingVertical: 16,
    gap: 8,
  },
  submitButtonDisabled: {
    backgroundColor: COLORS.textMuted,
  },
  submitButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.surface,
  },
  alreadySubmittedContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.successLight,
    borderRadius: 12,
    paddingVertical: 16,
    gap: 8,
  },
  alreadySubmittedText: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.success,
  },
  // Budget Warning Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  modalContent: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    padding: 24,
    width: '100%',
    maxWidth: 400,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  modalIconContainer: {
    width: 64,
    height: 64,
    borderRadius: 32,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    textAlign: 'center',
    marginBottom: 12,
  },
  modalMessage: {
    fontSize: 15,
    color: COLORS.textSecondary,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 12,
  },
  modalHint: {
    fontSize: 13,
    color: COLORS.textMuted,
    textAlign: 'center',
    fontStyle: 'italic',
    marginBottom: 20,
  },
  modalButtonsRow: {
    flexDirection: 'row',
    gap: 12,
    width: '100%',
  },
  modalButton: {
    flex: 1,
    paddingVertical: 12,
    paddingHorizontal: 20,
    borderRadius: 8,
    alignItems: 'center',
  },
  modalButtonPrimary: {
    backgroundColor: COLORS.primary,
  },
  modalButtonSecondary: {
    backgroundColor: COLORS.background,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  modalButtonText: {
    color: COLORS.surface,
    fontSize: 16,
    fontWeight: '600',
  },
  modalButtonTextSecondary: {
    color: COLORS.text,
  },
});
