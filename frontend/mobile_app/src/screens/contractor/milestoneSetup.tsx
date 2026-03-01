// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
  TextInput,
  ActivityIndicator,
  Alert,
  Platform,
  StatusBar,
} from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import * as DocumentPicker from 'expo-document-picker';
import { projects_service } from '../../services/projects_service';
import DateTimePicker from '@react-native-community/datetimepicker';

const PRIMARY = '#EC7E00';
const PRIMARY_DARK = '#C96A00';
const PRIMARY_DEEP = '#B35E00';

interface FileItem {
  uri: string;
  name: string;
  type: string;
}

interface MilestoneItem {
  id: number;
  percentage: string;
  title: string;
  description: string;
  start_date: string;
  date_to_finish: string;
  files: FileItem[];
}

interface Project {
  project_id: number;
  project_title: string;
  project_description?: string;
  project_location?: string;
  budget_range_min?: number;
  budget_range_max?: number;
  property_type?: string;
  type_name?: string;
  project_status?: string;
  owner_name?: string;
  accepted_bid_amount?: number;
  proposed_cost?: number; // Direct field from API
  accepted_bid?: {
    bid_id: number;
    proposed_cost: number;
    estimated_timeline: string;
    contractor_notes: string;
  };
}

interface MilestoneSetupProps {
  project: Project;
  userId?: number;
  onClose: () => void;
  onSave: () => void;
  editMode?: boolean;
  existingMilestone?: any;
}

const MilestoneSetup: React.FC<MilestoneSetupProps> = ({ project, userId, onClose, onSave, editMode = false, existingMilestone }) => {
  const insets = useSafeAreaInsets();
  const projectId = project.project_id;
  const projectTitle = project.project_title;

  // Current step (1, 2, or 3)
  const [currentStep, setCurrentStep] = useState(1);

  // Step 1 data
  const [milestoneName, setMilestoneName] = useState('');
  const [paymentMode, setPaymentMode] = useState<'downpayment' | 'full_payment'>('downpayment');

  // Step 2 data
  const [startDate, setStartDate] = useState(new Date());
  const [endDate, setEndDate] = useState(new Date());
  const [totalProjectCost, setTotalProjectCost] = useState('');
  const [downpaymentAmount, setDownpaymentAmount] = useState('');
  const [showStartPicker, setShowStartPicker] = useState(false);
  const [showEndPicker, setShowEndPicker] = useState(false);

  // Step 3 data
  const [milestoneItems, setMilestoneItems] = useState<MilestoneItem[]>([
    { id: 1, percentage: '', title: '', description: '', start_date: '', date_to_finish: '', files: [] },
  ]);
  const [showDatePicker, setShowDatePicker] = useState<{ [key: number]: boolean }>({});
  const [showStartDatePicker, setShowStartDatePicker] = useState<{ [key: number]: boolean }>({});
  const [milestoneItemDates, setMilestoneItemDates] = useState<{ [key: number]: Date }>({});
  const [milestoneItemStartDates, setMilestoneItemStartDates] = useState<{ [key: number]: Date }>({});

  // Loading states
  const [loading, setLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [isPrefilledFromBid, setIsPrefilledFromBid] = useState(false);
  const [hasPrefilledOnce, setHasPrefilledOnce] = useState(false);

  // Helper functions defined early so they can be used in useEffects
  const formatDate = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };

  const displayDate = (date: Date): string => {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  };

  // Format number with commas as user types
  const formatNumberWithCommas = (text: string): string => {
    // Remove all non-numeric characters except decimal point
    const numericValue = text.replace(/[^0-9.]/g, '');

    // If empty, return empty string
    if (!numericValue) return '';

    // Split by decimal point to handle decimal values
    const parts = numericValue.split('.');
    
    // Format the integer part with commas
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    // Rejoin with decimal point (limit to 2 decimal places)
    return parts.length > 1 ? parts[0] + '.' + parts[1].substring(0, 2) : parts[0];
  };

  // Remove commas from formatted string to get numeric value
  const removeCommas = (text: string): string => {
    return text.replace(/,/g, '');
  };

  // Load existing milestone data if in edit mode
  useEffect(() => {
    if (editMode && existingMilestone) {
      setMilestoneName(existingMilestone.milestone_name || '');
      setPaymentMode(existingMilestone.payment_plan?.payment_mode || 'downpayment');
      setStartDate(existingMilestone.start_date ? new Date(existingMilestone.start_date) : new Date());
      setEndDate(existingMilestone.end_date ? new Date(existingMilestone.end_date) : new Date());
      setTotalProjectCost(existingMilestone.payment_plan?.total_project_cost ? formatNumberWithCommas(existingMilestone.payment_plan.total_project_cost.toString()) : '');
      setDownpaymentAmount(existingMilestone.payment_plan?.downpayment_amount ? formatNumberWithCommas(existingMilestone.payment_plan.downpayment_amount.toString()) : '');
      
      if (existingMilestone.items && existingMilestone.items.length > 0) {
        const loadedItems = existingMilestone.items.map((item: any, index: number) => ({
          id: index + 1,
          percentage: item.percentage_progress?.toString() || '',
          title: item.milestone_item_title || '',
          description: item.milestone_item_description || '',
          start_date: item.start_date || '',
          date_to_finish: item.date_to_finish || '',
          files: [],
        }));
        setMilestoneItems(loadedItems);
      }
    }
  }, [editMode, existingMilestone]);

  // Pre-fill total project cost from winning bid amount (only for new setup, not edit mode)
  useEffect(() => {
    // Get the bid amount from multiple possible sources
    const bidAmount = project.accepted_bid_amount 
      || project.proposed_cost 
      || project.accepted_bid?.proposed_cost;

    console.log('MilestoneSetup - Pre-fill check:', {
      editMode,
      accepted_bid_amount: project.accepted_bid_amount,
      proposed_cost: project.proposed_cost,
      accepted_bid_proposed_cost: project.accepted_bid?.proposed_cost,
      bidAmount,
      totalProjectCost,
      hasPrefilledOnce
    });

    if (!editMode && bidAmount && !totalProjectCost && !hasPrefilledOnce) {
      console.log('MilestoneSetup - Pre-filling with bid amount:', bidAmount);
      setTotalProjectCost(formatNumberWithCommas(bidAmount.toString()));
      setIsPrefilledFromBid(true);
      setHasPrefilledOnce(true);
    }
  }, [editMode, project.accepted_bid_amount, project.proposed_cost, project.accepted_bid?.proposed_cost, totalProjectCost, hasPrefilledOnce]);

  // Calculate total percentage
  const totalPercentage = milestoneItems.reduce((sum, item) => {
    const pct = parseFloat(item.percentage) || 0;
    return sum + pct;
  }, 0);

  const handleStartDateChange = (event: any, selectedDate?: Date) => {
    setShowStartPicker(false);
    if (selectedDate) {
      setStartDate(selectedDate);
    }
  };

  const handleEndDateChange = (event: any, selectedDate?: Date) => {
    setShowEndPicker(false);
    if (selectedDate) {
      setEndDate(selectedDate);
    }
  };

  const addMilestoneItem = () => {
    const newId = milestoneItems.length > 0 ? Math.max(...milestoneItems.map(i => i.id)) + 1 : 1;
    setMilestoneItems([
      ...milestoneItems,
      { id: newId, percentage: '', title: '', description: '', start_date: '', date_to_finish: '', files: [] },
    ]);
    setMilestoneItemDates(prev => ({ ...prev, [newId]: startDate }));
    setMilestoneItemStartDates(prev => ({ ...prev, [newId]: startDate }));
  };

  const removeMilestoneItem = (id: number) => {
    if (milestoneItems.length > 1) {
      setMilestoneItems(milestoneItems.filter(item => item.id !== id));
      setMilestoneItemDates(prev => { const n = { ...prev }; delete n[id]; return n; });
      setMilestoneItemStartDates(prev => { const n = { ...prev }; delete n[id]; return n; });
      setShowDatePicker(prev => { const n = { ...prev }; delete n[id]; return n; });
      setShowStartDatePicker(prev => { const n = { ...prev }; delete n[id]; return n; });
    }
  };

  const updateMilestoneItem = (id: number, field: keyof MilestoneItem, value: string) => {
    setMilestoneItems(
      milestoneItems.map(item =>
        item.id === id ? { ...item, [field]: value } : item
      )
    );
  };

  const MAX_ITEM_FILES = 5;

  const pickFile = async (id: number) => {
    const item = milestoneItems.find(i => i.id === id);
    if (!item) return;
    if (item.files.length >= MAX_ITEM_FILES) {
      Alert.alert('Limit Reached', `You can attach up to ${MAX_ITEM_FILES} files per milestone.`);
      return;
    }
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ['image/*', 'application/pdf', 'application/msword',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'],
        multiple: true,
      });
      if (!result.canceled && result.assets) {
        const slotsLeft = MAX_ITEM_FILES - item.files.length;
        const newFiles: FileItem[] = result.assets.slice(0, slotsLeft).map(a => ({
          uri: a.uri,
          name: a.name || a.uri.split('/').pop() || 'file',
          type: a.mimeType || 'application/octet-stream',
        }));
        setMilestoneItems(prev =>
          prev.map(i => i.id === id ? { ...i, files: [...i.files, ...newFiles] } : i)
        );
      }
    } catch (err) {
      console.error('File pick error:', err);
    }
  };

  const removeFile = (id: number, fileIndex: number) => {
    setMilestoneItems(prev =>
      prev.map(i => i.id === id ? { ...i, files: i.files.filter((_, fi) => fi !== fileIndex) } : i)
    );
  };

  const validateStep1 = (): boolean => {
    if (!milestoneName.trim()) {
      Alert.alert('Validation Error', 'Please enter a project name.');
      return false;
    }
    return true;
  };

  const validateStep2 = (): boolean => {
    if (!totalProjectCost || parseFloat(removeCommas(totalProjectCost)) <= 0) {
      Alert.alert('Validation Error', 'Please enter a valid total project cost.');
      return false;
    }
    if (paymentMode === 'downpayment') {
      if (!downpaymentAmount || parseFloat(removeCommas(downpaymentAmount)) <= 0) {
        Alert.alert('Validation Error', 'Please enter a valid downpayment amount.');
        return false;
      }
      if (parseFloat(removeCommas(downpaymentAmount)) >= parseFloat(removeCommas(totalProjectCost))) {
        Alert.alert('Validation Error', 'Downpayment must be less than total project cost.');
        return false;
      }
    }
    if (endDate <= startDate) {
      Alert.alert('Validation Error', 'End date must be after start date.');
      return false;
    }
    return true;
  };

  const validateStep3 = (): boolean => {
    for (let i = 0; i < milestoneItems.length; i++) {
      const item = milestoneItems[i];
      const label = `Milestone ${i + 1}`;

      if (!item.percentage || parseFloat(item.percentage) <= 0) {
        Alert.alert('Validation Error', `${label}: Please enter a valid percentage.`);
        return false;
      }
      if (!item.title.trim()) {
        Alert.alert('Validation Error', `${label}: Please enter a title.`);
        return false;
      }
      if (!item.start_date) {
        Alert.alert('Validation Error', `${label}: Please select a start date.`);
        return false;
      }
      if (!item.date_to_finish) {
        Alert.alert('Validation Error', `${label}: Please select an end date.`);
        return false;
      }

      const itemStart = new Date(item.start_date);
      const itemEnd = new Date(item.date_to_finish);

      if (itemEnd <= itemStart) {
        Alert.alert('Validation Error', `${label}: End date must be after the start date.`);
        return false;
      }

      // Sequential check: must start after the previous milestone's end date
      if (i > 0) {
        const prev = milestoneItems[i - 1];
        if (prev.date_to_finish && itemStart <= new Date(prev.date_to_finish)) {
          Alert.alert('Validation Error', `${label}: Start date must be after Milestone ${i}'s end date (${displayDate(new Date(prev.date_to_finish))}).`);
          return false;
        }
      }
    }
    if (Math.abs(totalPercentage - 100) > 0.01) {
      Alert.alert('Validation Error', `Total percentage must equal 100%. Current: ${totalPercentage.toFixed(1)}%`);
      return false;
    }
    return true;
  };

  const handleNext = () => {
    if (currentStep === 1 && validateStep1()) {
      setCurrentStep(2);
    } else if (currentStep === 2 && validateStep2()) {
      setCurrentStep(3);
    }
  };

  const handleBack = () => {
    if (currentStep > 1) {
      setCurrentStep(currentStep - 1);
    }
  };

  const handleSubmit = async () => {
    if (!validateStep3()) return;

    setSubmitting(true);
    try {
      const milestoneData = {
        milestone_name: milestoneName,
        payment_mode: paymentMode,
        start_date: formatDate(startDate),
        end_date: formatDate(endDate),
        total_project_cost: parseFloat(removeCommas(totalProjectCost)),
        downpayment_amount: paymentMode === 'downpayment' ? parseFloat(removeCommas(downpaymentAmount)) : 0,
        items: milestoneItems.map(item => ({
          percentage: parseFloat(item.percentage),
          title: item.title,
          description: item.description || '',
          start_date: item.start_date,
          date_to_finish: item.date_to_finish,
          files: item.files,
        })),
      };

      let response;
      if (editMode && existingMilestone) {
        // Update existing milestone
        response = await projects_service.update_milestone(userId, projectId, existingMilestone.milestone_id, milestoneData);
      } else {
        // Create new milestone
        response = await projects_service.submit_milestones(userId, projectId, milestoneData);
      }

      if (response.success) {
        Alert.alert('Success', editMode ? 'Milestone has been updated successfully!' : 'Milestones have been set up successfully!', [
          { text: 'OK', onPress: () => onSave() },
        ]);
      } else {
        Alert.alert('Error', response.message || (editMode ? 'Failed to update milestone.' : 'Failed to submit milestones.'));
      }
    } catch (error) {
      console.error(editMode ? 'Error updating milestone:' : 'Error submitting milestones:', error);
      Alert.alert('Error', editMode ? 'An error occurred while updating the milestone.' : 'An error occurred while submitting milestones.');
    } finally {
      setSubmitting(false);
    }
  };

  const renderStepIndicator = () => (
    <View style={styles.stepIndicatorContainer}>
      {[1, 2, 3].map((step, index) => (
        <React.Fragment key={step}>
          <View style={styles.stepWrapper}>
            <View
              style={[
                styles.stepCircle,
                currentStep >= step && styles.stepCircleActive,
              ]}
            >
              {currentStep > step ? (
                <Ionicons name="checkmark" size={16} color="#FFF" />
              ) : (
                <Text
                  style={[
                    styles.stepNumber,
                    currentStep >= step && styles.stepNumberActive,
                  ]}
                >
                  {step}
                </Text>
              )}
            </View>
            <Text style={[styles.stepLabel, currentStep >= step && styles.stepLabelActive]}>
              {step === 1 ? 'Basic Info' : step === 2 ? 'Payment Details' : 'Milestones'}
            </Text>
          </View>
          {index < 2 && (
            <View
              style={[
                styles.stepLine,
                currentStep > step && styles.stepLineActive,
              ]}
            />
          )}
        </React.Fragment>
      ))}
    </View>
  );

  const renderStep1 = () => (
    <View style={styles.stepContent}>
      <Text style={styles.sectionTitle}>Basic Information</Text>
      <Text style={styles.sectionDescription}>
        Enter the project name and select the payment mode.
      </Text>

      <View style={styles.inputGroup}>
        <Text style={styles.label}>Project Name *</Text>
        <TextInput
          style={styles.input}
          value={milestoneName}
          onChangeText={setMilestoneName}
          placeholder="e.g., Project Construction Milestone"
          placeholderTextColor="#999"
        />
      </View>

      <View style={styles.inputGroup}>
        <Text style={styles.label}>Payment Mode *</Text>
        <View style={styles.paymentModeContainer}>
          <TouchableOpacity
            style={[
              styles.paymentModeButton,
              paymentMode === 'downpayment' && styles.paymentModeButtonActive,
            ]}
            onPress={() => setPaymentMode('downpayment')}
          >
            <Ionicons
              name={paymentMode === 'downpayment' ? 'radio-button-on' : 'radio-button-off'}
              size={20}
              color={paymentMode === 'downpayment' ? PRIMARY : '#666'}
            />
            <View style={styles.paymentModeTextContainer}>
              <Text
                style={[
                  styles.paymentModeLabel,
                  paymentMode === 'downpayment' && styles.paymentModeLabelActive,
                ]}
              >
                Downpayment
              </Text>
              <Text style={styles.paymentModeDescription}>
                Owner pays initial downpayment, then milestone-based payments
              </Text>
            </View>
          </TouchableOpacity>

          <TouchableOpacity
            style={[
              styles.paymentModeButton,
              paymentMode === 'full_payment' && styles.paymentModeButtonActive,
            ]}
            onPress={() => setPaymentMode('full_payment')}
          >
            <Ionicons
              name={paymentMode === 'full_payment' ? 'radio-button-on' : 'radio-button-off'}
              size={20}
              color={paymentMode === 'full_payment' ? PRIMARY : '#666'}
            />
            <View style={styles.paymentModeTextContainer}>
              <Text
                style={[
                  styles.paymentModeLabel,
                  paymentMode === 'full_payment' && styles.paymentModeLabelActive,
                ]}
              >
                Full Payment
              </Text>
              <Text style={styles.paymentModeDescription}>
                Owner pays full amount upon project completion
              </Text>
            </View>
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );

  const renderStep2 = () => (
    <View style={styles.stepContent}>
      <Text style={styles.sectionTitle}>Payment Details</Text>
      <Text style={styles.sectionDescription}>
        Set the project timeline and financial details.
      </Text>

      <View style={styles.dateRow}>
        <View style={styles.dateInputGroup}>
          <Text style={styles.label}>Start Date *</Text>
          <TouchableOpacity
            style={styles.dateInput}
            onPress={() => setShowStartPicker(true)}
          >
            <Ionicons name="calendar-outline" size={20} color={PRIMARY} />
            <Text style={styles.dateText} numberOfLines={1}>{displayDate(startDate)}</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.dateInputGroup}>
          <Text style={styles.label}>End Date *</Text>
          <TouchableOpacity
            style={styles.dateInput}
            onPress={() => setShowEndPicker(true)}
          >
            <Ionicons name="calendar-outline" size={20} color={PRIMARY} />
            <Text style={styles.dateText} numberOfLines={1}>{displayDate(endDate)}</Text>
          </TouchableOpacity>
        </View>
      </View>

      {showStartPicker && (
        <DateTimePicker
          value={startDate}
          mode="date"
          display={Platform.OS === 'ios' ? 'spinner' : 'default'}
          onChange={handleStartDateChange}
          minimumDate={new Date()}
        />
      )}

      {showEndPicker && (
        <DateTimePicker
          value={endDate}
          mode="date"
          display={Platform.OS === 'ios' ? 'spinner' : 'default'}
          onChange={handleEndDateChange}
          minimumDate={new Date(Math.max(startDate.getTime(), new Date().setHours(0,0,0,0)))}
        />
      )}

      <View style={styles.inputGroup}>
        <View style={styles.labelWithBadge}>
          <Text style={styles.label}>Total Project Cost (₱) *</Text>
          {isPrefilledFromBid && (
            <View style={styles.bidBadge}>
              <Ionicons name="checkmark-circle" size={14} color="#10B981" />
              <Text style={styles.bidBadgeText}>From winning bid</Text>
            </View>
          )}
        </View>
        <View style={styles.currencyInput}>
          <Text style={styles.currencySymbol}>₱</Text>
          <TextInput
            style={styles.currencyTextInput}
            value={totalProjectCost}
            onChangeText={(value) => {
              const formatted = formatNumberWithCommas(value);
              setTotalProjectCost(formatted);
              // Clear the badge when user manually edits the value
              const bidAmount = project.accepted_bid_amount 
                || project.proposed_cost 
                || project.accepted_bid?.proposed_cost;
              if (isPrefilledFromBid && bidAmount && removeCommas(formatted) !== bidAmount.toString()) {
                setIsPrefilledFromBid(false);
              }
            }}
            placeholder="0.00"
            placeholderTextColor="#999"
            keyboardType="decimal-pad"
          />
        </View>
        {isPrefilledFromBid && (
          <Text style={styles.prefillHint}>
            This amount was automatically filled from the accepted bid. You can edit it if needed.
          </Text>
        )}
      </View>

      {paymentMode === 'downpayment' && (
        <View style={styles.inputGroup}>
          <Text style={styles.label}>Downpayment Amount (₱) *</Text>
          <View style={styles.currencyInput}>
            <Text style={styles.currencySymbol}>₱</Text>
            <TextInput
              style={styles.currencyTextInput}
              value={downpaymentAmount}
              onChangeText={(value) => setDownpaymentAmount(formatNumberWithCommas(value))}
              placeholder="0.00"
              placeholderTextColor="#999"
              keyboardType="decimal-pad"
            />
          </View>
          {totalProjectCost && downpaymentAmount && (
            <Text style={styles.remainingBalance}>
              Remaining Balance: ₱{(parseFloat(removeCommas(totalProjectCost)) - parseFloat(removeCommas(downpaymentAmount))).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
            </Text>
          )}
        </View>
      )}
    </View>
  );

  // ── Date range helpers for sequential non-overlapping milestones ──
  const addDays = (date: Date, days: number): Date => {
    const d = new Date(date);
    d.setDate(d.getDate() + days);
    return d;
  };

  const today = new Date(); today.setHours(0, 0, 0, 0);

  // Minimum start date for milestone at `index`: day after previous milestone's end date, floored to today
  const getMinStartDate = (index: number): Date => {
    const base = index === 0
      ? startDate
      : (milestoneItems[index - 1].date_to_finish ? addDays(new Date(milestoneItems[index - 1].date_to_finish), 1) : startDate);
    return new Date(Math.max(base.getTime(), today.getTime()));
  };

  // Maximum end date for milestone at `index`: day before next milestone's start date
  const getMaxEndDate = (index: number): Date => {
    if (index === milestoneItems.length - 1) return endDate;
    const next = milestoneItems[index + 1];
    return next.start_date ? addDays(new Date(next.start_date), -1) : endDate;
  };

  const renderStep3 = () => (
    <View style={styles.stepContent}>
      <Text style={styles.sectionTitle}>Milestone Items</Text>
      <Text style={styles.sectionDescription}>
        Break down the project into milestones. Total percentage must equal 100%.
      </Text>

      <View style={styles.percentageIndicator}>
        <Text style={styles.percentageLabel}>Total Progress:</Text>
        <View style={styles.percentageBarContainer}>
          <View
            style={[
              styles.percentageBar,
              { width: `${Math.min(totalPercentage, 100)}%` },
              totalPercentage === 100 && styles.percentageBarComplete,
              totalPercentage > 100 && styles.percentageBarOver,
            ]}
          />
        </View>
        <Text
          style={[
            styles.percentageValue,
            totalPercentage === 100 && styles.percentageValueComplete,
            totalPercentage > 100 && styles.percentageValueOver,
          ]}
        >
          {totalPercentage.toFixed(1)}%
        </Text>
      </View>

      {milestoneItems.map((item, index) => {
        const minStart = getMinStartDate(index);
        const maxEnd   = getMaxEndDate(index);
        return (
        <View key={item.id} style={styles.milestoneItemCard}>
          <View style={styles.milestoneItemHeader}>
            <Text style={styles.milestoneItemTitle}>Milestone {index + 1}</Text>
            {milestoneItems.length > 1 && (
              <TouchableOpacity
                style={styles.removeButton}
                onPress={() => removeMilestoneItem(item.id)}
              >
                <Ionicons name="trash-outline" size={18} color="#FF4444" />
              </TouchableOpacity>
            )}
          </View>

          <View style={styles.milestoneItemRow}>
            <View style={styles.percentageInput}>
              <Text style={styles.smallLabel}>Percentage *</Text>
              <View style={styles.percentageInputContainer}>
                <TextInput
                  style={styles.percentageTextInput}
                  value={item.percentage}
                  onChangeText={(value) => updateMilestoneItem(item.id, 'percentage', value)}
                  placeholder="0"
                  placeholderTextColor="#999"
                  keyboardType="decimal-pad"
                />
                <Text style={styles.percentageSign}>%</Text>
              </View>
            </View>

            <View style={styles.titleInput}>
              <Text style={styles.smallLabel}>Title *</Text>
              <TextInput
                style={styles.input}
                value={item.title}
                onChangeText={(value) => updateMilestoneItem(item.id, 'title', value)}
                placeholder="e.g., Foundation Complete"
                placeholderTextColor="#999"
              />
            </View>
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.smallLabel}>Description</Text>
            <TextInput
              style={[styles.input, styles.textArea]}
              value={item.description}
              onChangeText={(value) => updateMilestoneItem(item.id, 'description', value)}
              placeholder="Describe the milestone requirements..."
              placeholderTextColor="#999"
              multiline
              numberOfLines={3}
            />
          </View>

          {/* Attachments */}
          <View style={styles.inputGroup}>
            <View style={styles.attachmentHeader}>
              <Text style={styles.smallLabel}>Attachments</Text>
              <Text style={styles.attachmentCount}>{item.files.length}/{MAX_ITEM_FILES}</Text>
            </View>
            {item.files.map((file, fi) => (
              <View key={fi} style={styles.attachmentRow}>
                <Ionicons name="document-outline" size={16} color={PRIMARY} style={{ marginRight: 6 }} />
                <Text style={styles.attachmentName} numberOfLines={1}>{file.name}</Text>
                <TouchableOpacity onPress={() => removeFile(item.id, fi)} hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}>
                  <Ionicons name="close-circle" size={18} color="#FF4444" />
                </TouchableOpacity>
              </View>
            ))}
            <TouchableOpacity style={styles.attachButton} onPress={() => pickFile(item.id)}>
              <Ionicons name="attach" size={18} color={PRIMARY} />
              <Text style={styles.attachButtonText}>Add File</Text>
            </TouchableOpacity>
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.smallLabel}>Start Date *</Text>
            <TouchableOpacity
              style={styles.datePickerButton}
              onPress={() => {
                if (!milestoneItemStartDates[item.id]) {
                  setMilestoneItemStartDates(prev => ({ ...prev, [item.id]: minStart }));
                }
                setShowStartDatePicker(prev => ({ ...prev, [item.id]: true }));
              }}
            >
              <Ionicons name="calendar-outline" size={20} color={PRIMARY} />
              <Text style={styles.datePickerText} numberOfLines={1}>
                {item.start_date ? displayDate(new Date(item.start_date)) : 'Select Start Date'}
              </Text>
            </TouchableOpacity>
            {index > 0 && (
              <Text style={styles.dateHint}>From {displayDate(minStart)} onwards</Text>
            )}
            {showStartDatePicker[item.id] && (
              <DateTimePicker
                value={milestoneItemStartDates[item.id] || minStart}
                mode="date"
                display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                minimumDate={new Date(Math.max(minStart.getTime(), today.getTime()))}
                maximumDate={maxEnd}
                onChange={(event, selectedDate) => {
                  setShowStartDatePicker(prev => ({ ...prev, [item.id]: false }));
                  if (selectedDate) {
                    setMilestoneItemStartDates(prev => ({ ...prev, [item.id]: selectedDate }));
                    updateMilestoneItem(item.id, 'start_date', formatDate(selectedDate));
                    // Clear end date if it's now before the new start
                    if (item.date_to_finish && new Date(item.date_to_finish) <= selectedDate) {
                      updateMilestoneItem(item.id, 'date_to_finish', '');
                    }
                  }
                }}
              />
            )}
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.smallLabel}>Target Completion Date *</Text>
            <TouchableOpacity
              style={styles.datePickerButton}
              onPress={() => {
                const itemMinStart = item.start_date ? new Date(item.start_date) : minStart;
                if (!milestoneItemDates[item.id]) {
                  setMilestoneItemDates(prev => ({ ...prev, [item.id]: itemMinStart }));
                }
                setShowDatePicker(prev => ({ ...prev, [item.id]: true }));
              }}
            >
              <Ionicons name="calendar-outline" size={20} color={PRIMARY} />
              <Text style={styles.datePickerText} numberOfLines={1}>
                {item.date_to_finish ? displayDate(new Date(item.date_to_finish)) : 'Select End Date'}
              </Text>
            </TouchableOpacity>
            {index < milestoneItems.length - 1 && maxEnd >= today && (
              <Text style={styles.dateHint}>Up to {displayDate(maxEnd)}</Text>
            )}
            {showDatePicker[item.id] && (
              <DateTimePicker
                value={milestoneItemDates[item.id] || (item.start_date ? new Date(item.start_date) : minStart)}
                mode="date"
                display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                minimumDate={new Date(Math.max(
                  (item.start_date ? addDays(new Date(item.start_date), 1) : minStart).getTime(),
                  today.getTime()
                ))}
                maximumDate={maxEnd}
                onChange={(event, selectedDate) => {
                  setShowDatePicker(prev => ({ ...prev, [item.id]: false }));
                  if (selectedDate) {
                    setMilestoneItemDates(prev => ({ ...prev, [item.id]: selectedDate }));
                    updateMilestoneItem(item.id, 'date_to_finish', formatDate(selectedDate));
                  }
                }}
              />
            )}
          </View>
        </View>
        );
      })}

      <TouchableOpacity style={styles.addButton} onPress={addMilestoneItem}>
        <Ionicons name="add-circle-outline" size={24} color={PRIMARY} />
        <Text style={styles.addButtonText}>Add Milestone Item</Text>
      </TouchableOpacity>
    </View>
  );

  return (
    <SafeAreaView style={styles.container} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={PRIMARY} />
      {/* Header */}
      <View style={[styles.header, { paddingTop: insets.top + 12 }]}>
        <TouchableOpacity style={styles.backButton} onPress={onClose}>
          <Ionicons name="arrow-back" size={24} color="#FFF" />
        </TouchableOpacity>
        <View style={styles.headerContent}>
          <Text style={styles.headerTitle}>{editMode ? 'Edit' : 'Setup'} Project</Text>
          <Text style={styles.headerSubtitle} numberOfLines={1}>
            {projectTitle}
          </Text>
        </View>
      </View>

      {/* Step Indicator */}
      {renderStepIndicator()}

      {/* Content */}
      <ScrollView style={styles.content} contentContainerStyle={styles.contentContainer}>
        {currentStep === 1 && renderStep1()}
        {currentStep === 2 && renderStep2()}
        {currentStep === 3 && renderStep3()}
      </ScrollView>

      {/* Footer Buttons */}
      <View style={styles.footer}>
        {currentStep > 1 && (
          <TouchableOpacity style={styles.backStepButton} onPress={handleBack}>
            <Ionicons name="arrow-back" size={20} color={PRIMARY} />
            <Text style={styles.backStepButtonText}>Back</Text>
          </TouchableOpacity>
        )}

        {currentStep < 3 ? (
          <TouchableOpacity
            style={[styles.nextButton, currentStep === 1 && styles.fullWidthButton]}
            onPress={handleNext}
          >
            <Text style={styles.nextButtonText}>Next</Text>
            <Ionicons name="arrow-forward" size={20} color="#FFF" />
          </TouchableOpacity>
        ) : (
          <TouchableOpacity
            style={[styles.submitButton, submitting && styles.submitButtonDisabled]}
            onPress={handleSubmit}
            disabled={submitting}
          >
            {submitting ? (
              <ActivityIndicator size="small" color="#FFF" />
            ) : (
              <>
                <Ionicons name="checkmark-circle" size={20} color="#FFF" />
                <Text style={styles.submitButtonText}>{editMode ? 'Update' : 'Submit'} Project Setup</Text>
              </>
            )}
          </TouchableOpacity>
        )}
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  header: {
    backgroundColor: PRIMARY,
    paddingBottom: 20,
    paddingHorizontal: 20,
    flexDirection: 'row',
    alignItems: 'center',
  },
  backButton: {
    marginRight: 15,
  },
  headerContent: {
    flex: 1,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#FFF',
  },
  headerSubtitle: {
    fontSize: 14,
    color: 'rgba(255,255,255,0.8)',
    marginTop: 2,
  },
  stepIndicatorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingTop: 12,
    paddingBottom: 12,
    paddingHorizontal: 30,
    backgroundColor: '#FFF',
    borderBottomWidth: 1,
    borderBottomColor: '#EEE',
  },
  stepWrapper: {
    alignItems: 'center',
  },
  stepCircle: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#E0E0E0',
    alignItems: 'center',
    justifyContent: 'center',
  },
  stepCircleActive: {
    backgroundColor: PRIMARY,
  },
  stepNumber: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#666',
  },
  stepNumberActive: {
    color: '#FFF',
  },
  stepLabel: {
    fontSize: 11,
    color: '#999',
    marginTop: 5,
  },
  stepLabelActive: {
    color: PRIMARY,
    fontWeight: '600',
  },
  stepLine: {
    width: 40,
    height: 3,
    backgroundColor: '#E0E0E0',
    marginHorizontal: 5,
    marginBottom: 20,
  },
  stepLineActive: {
    backgroundColor: PRIMARY,
  },
  content: {
    flex: 1,
  },
  contentContainer: {
    padding: 16,
    paddingTop: 14,
    paddingBottom: 30,
  },
  stepContent: {
    backgroundColor: '#FFF',
    borderRadius: 12,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 5,
    elevation: 2,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 5,
  },
  sectionDescription: {
    fontSize: 14,
    color: '#666',
    marginBottom: 20,
  },
  inputGroup: {
    marginBottom: 20,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  labelWithBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
    gap: 10,
  },
  bidBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#D1FAE5',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    gap: 4,
  },
  bidBadgeText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#059669',
  },
  prefillHint: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 6,
    fontStyle: 'italic',
  },
  smallLabel: {
    fontSize: 12,
    fontWeight: '600',
    color: '#333',
    marginBottom: 6,
  },
  input: {
    borderWidth: 1,
    borderColor: '#DDD',
    borderRadius: 8,
    paddingHorizontal: 15,
    paddingVertical: 12,
    fontSize: 16,
    color: '#333',
    backgroundColor: '#FAFAFA',
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
  },
  paymentModeContainer: {
    gap: 12,
  },
  paymentModeButton: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    padding: 15,
    borderWidth: 1,
    borderColor: '#DDD',
    borderRadius: 10,
    backgroundColor: '#FAFAFA',
  },
  paymentModeButtonActive: {
    borderColor: PRIMARY,
    backgroundColor: 'rgba(236, 126, 0, 0.05)',
  },
  paymentModeTextContainer: {
    flex: 1,
    marginLeft: 12,
  },
  paymentModeLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
  },
  paymentModeLabelActive: {
    color: PRIMARY,
  },
  paymentModeDescription: {
    fontSize: 13,
    color: '#666',
    marginTop: 3,
  },
  dateRow: {
    flexDirection: 'row',
    gap: 15,
    marginBottom: 20,
  },
  dateInputGroup: {
    flex: 1,
  },
  dateInput: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#DDD',
    borderRadius: 8,
    paddingHorizontal: 15,
    paddingVertical: 12,
    backgroundColor: '#FAFAFA',
  },
  dateText: {
    fontSize: 14,
    color: '#333',
    marginLeft: 8,
    flex: 1,
  },
  currencyInput: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#DDD',
    borderRadius: 8,
    backgroundColor: '#FAFAFA',
    overflow: 'hidden',
  },
  currencySymbol: {
    fontSize: 18,
    fontWeight: 'bold',
    color: PRIMARY,
    paddingHorizontal: 15,
    backgroundColor: '#F0F0F0',
    paddingVertical: 12,
  },
  currencyTextInput: {
    flex: 1,
    fontSize: 16,
    color: '#333',
    paddingHorizontal: 15,
    paddingVertical: 12,
  },
  remainingBalance: {
    fontSize: 13,
    color: PRIMARY,
    marginTop: 8,
    fontWeight: '600',
  },
  percentageIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
    padding: 15,
    backgroundColor: '#F5F5F5',
    borderRadius: 10,
  },
  percentageLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginRight: 10,
  },
  percentageBarContainer: {
    flex: 1,
    height: 12,
    backgroundColor: '#E0E0E0',
    borderRadius: 6,
    overflow: 'hidden',
  },
  percentageBar: {
    height: '100%',
    backgroundColor: PRIMARY,
    borderRadius: 6,
  },
  percentageBarComplete: {
    backgroundColor: '#4CAF50',
  },
  percentageBarOver: {
    backgroundColor: '#FF4444',
  },
  percentageValue: {
    fontSize: 14,
    fontWeight: 'bold',
    color: PRIMARY,
    marginLeft: 10,
    minWidth: 50,
    textAlign: 'right',
  },
  percentageValueComplete: {
    color: '#4CAF50',
  },
  percentageValueOver: {
    color: '#FF4444',
  },
  milestoneItemCard: {
    backgroundColor: '#F9F9F9',
    borderRadius: 10,
    padding: 15,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: '#EEE',
  },
  milestoneItemHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
  },
  milestoneItemTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: PRIMARY,
  },
  removeButton: {
    padding: 5,
  },
  milestoneItemRow: {
    flexDirection: 'row',
    gap: 12,
    marginBottom: 12,
  },
  percentageInput: {
    width: 100,
  },
  percentageInputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#DDD',
    borderRadius: 8,
    backgroundColor: '#FFF',
    overflow: 'hidden',
  },
  percentageTextInput: {
    flex: 1,
    fontSize: 16,
    color: '#333',
    paddingHorizontal: 12,
    paddingVertical: 10,
    textAlign: 'center',
  },
  percentageSign: {
    fontSize: 16,
    fontWeight: 'bold',
    color: PRIMARY,
    paddingRight: 12,
  },
  titleInput: {
    flex: 1,
  },
  addButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 15,
    borderWidth: 2,
    borderColor: PRIMARY,
    borderStyle: 'dashed',
    borderRadius: 10,
    marginTop: 5,
  },
  addButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: PRIMARY,
    marginLeft: 8,
  },
  datePickerButton: {
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#DDD',
    borderRadius: 8,
    backgroundColor: '#FFF',
    paddingHorizontal: 12,
    paddingVertical: 12,
    gap: 10,
  },
  datePickerText: {
    fontSize: 16,
    color: '#333',
    flex: 1,
  },
  dateHint: {
    fontSize: 11,
    color: '#999',
    marginTop: 4,
    marginLeft: 2,
  },
  footer: {
    flexDirection: 'row',
    padding: 20,
    paddingBottom: 30,
    backgroundColor: '#FFF',
    borderTopWidth: 1,
    borderTopColor: '#EEE',
    gap: 15,
  },
  backStepButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 15,
    paddingHorizontal: 20,
    borderWidth: 2,
    borderColor: PRIMARY,
    borderRadius: 10,
  },
  backStepButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: PRIMARY,
    marginLeft: 5,
  },
  nextButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: PRIMARY,
    paddingVertical: 15,
    borderRadius: 10,
    gap: 8,
  },
  fullWidthButton: {
    flex: 1,
  },
  nextButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#FFF',
  },
  submitButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#4CAF50',
    paddingVertical: 15,
    borderRadius: 10,
    gap: 8,
  },
  submitButtonDisabled: {
    backgroundColor: '#A5D6A7',
  },
  submitButtonText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#FFF',
  },
  attachmentHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6,
  },
  attachmentCount: {
    fontSize: 12,
    color: '#999',
  },
  attachmentRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF',
    borderWidth: 1,
    borderColor: '#E0E0E0',
    borderRadius: 8,
    paddingHorizontal: 10,
    paddingVertical: 8,
    marginBottom: 6,
  },
  attachmentName: {
    flex: 1,
    fontSize: 13,
    color: '#333',
  },
  attachButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderWidth: 1,
    borderColor: PRIMARY,
    borderStyle: 'dashed',
    borderRadius: 8,
    gap: 6,
    marginTop: 2,
  },
  attachButtonText: {
    fontSize: 14,
    color: PRIMARY,
    fontWeight: '500',
  },
});

export default MilestoneSetup;
