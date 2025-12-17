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
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { projects_service } from '../../services/projects_service';
import DateTimePicker from '@react-native-community/datetimepicker';

const PRIMARY = '#EC7E00';
const PRIMARY_DARK = '#C96A00';
const PRIMARY_DEEP = '#B35E00';

interface MilestoneItem {
  id: number;
  percentage: string;
  title: string;
  description: string;
  date_to_finish: string;
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
}

interface MilestoneSetupProps {
  project: Project;
  userId?: number;
  onClose: () => void;
  onSave: () => void;
}

const MilestoneSetup: React.FC<MilestoneSetupProps> = ({ project, userId, onClose, onSave }) => {
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
    { id: 1, percentage: '', title: '', description: '', date_to_finish: '' },
  ]);
  const [showDatePicker, setShowDatePicker] = useState<{ [key: number]: boolean }>({});
  const [milestoneItemDates, setMilestoneItemDates] = useState<{ [key: number]: Date }>({});

  // Loading states
  const [loading, setLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  // Calculate total percentage
  const totalPercentage = milestoneItems.reduce((sum, item) => {
    const pct = parseFloat(item.percentage) || 0;
    return sum + pct;
  }, 0);

  const formatDate = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };

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
      { id: newId, percentage: '', title: '', description: '', date_to_finish: '' },
    ]);
    // Initialize date for new item to start date
    setMilestoneItemDates(prev => ({ ...prev, [newId]: startDate }));
  };

  const removeMilestoneItem = (id: number) => {
    if (milestoneItems.length > 1) {
      setMilestoneItems(milestoneItems.filter(item => item.id !== id));
      // Clean up date state for removed item
      setMilestoneItemDates(prev => {
        const newDates = { ...prev };
        delete newDates[id];
        return newDates;
      });
      setShowDatePicker(prev => {
        const newPickers = { ...prev };
        delete newPickers[id];
        return newPickers;
      });
    }
  };

  const updateMilestoneItem = (id: number, field: keyof MilestoneItem, value: string) => {
    setMilestoneItems(
      milestoneItems.map(item =>
        item.id === id ? { ...item, [field]: value } : item
      )
    );
  };

  const validateStep1 = (): boolean => {
    if (!milestoneName.trim()) {
      Alert.alert('Validation Error', 'Please enter a milestone name.');
      return false;
    }
    return true;
  };

  const validateStep2 = (): boolean => {
    if (!totalProjectCost || parseFloat(totalProjectCost) <= 0) {
      Alert.alert('Validation Error', 'Please enter a valid total project cost.');
      return false;
    }
    if (paymentMode === 'downpayment') {
      if (!downpaymentAmount || parseFloat(downpaymentAmount) <= 0) {
        Alert.alert('Validation Error', 'Please enter a valid downpayment amount.');
        return false;
      }
      if (parseFloat(downpaymentAmount) >= parseFloat(totalProjectCost)) {
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
    for (const item of milestoneItems) {
      if (!item.percentage || parseFloat(item.percentage) <= 0) {
        Alert.alert('Validation Error', 'Please enter a valid percentage for all milestone items.');
        return false;
      }
      if (!item.title.trim()) {
        Alert.alert('Validation Error', 'Please enter a title for all milestone items.');
        return false;
      }
      if (!item.date_to_finish) {
        Alert.alert('Validation Error', 'Please enter a completion date for all milestone items.');
        return false;
      }
      // Validate date is within project timeline
      const itemDate = new Date(item.date_to_finish);
      if (itemDate < startDate || itemDate > endDate) {
        Alert.alert('Validation Error', `Milestone completion date must be between ${formatDate(startDate)} and ${formatDate(endDate)}.`);
        return false;
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
        total_project_cost: parseFloat(totalProjectCost),
        downpayment_amount: paymentMode === 'downpayment' ? parseFloat(downpaymentAmount) : 0,
        items: milestoneItems.map(item => ({
          percentage: parseFloat(item.percentage),
          title: item.title,
          description: item.description || '',
          date_to_finish: item.date_to_finish,
        })),
      };

      const response = await projects_service.submit_milestones(userId, projectId, milestoneData);

      if (response.success) {
        Alert.alert('Success', 'Milestones have been set up successfully!', [
          { text: 'OK', onPress: () => onSave() },
        ]);
      } else {
        Alert.alert('Error', response.message || 'Failed to submit milestones.');
      }
    } catch (error) {
      console.error('Error submitting milestones:', error);
      Alert.alert('Error', 'An error occurred while submitting milestones.');
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
        Enter the milestone plan name and select the payment mode for this project.
      </Text>

      <View style={styles.inputGroup}>
        <Text style={styles.label}>Milestone Name *</Text>
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
            <Text style={styles.dateText}>{formatDate(startDate)}</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.dateInputGroup}>
          <Text style={styles.label}>End Date *</Text>
          <TouchableOpacity
            style={styles.dateInput}
            onPress={() => setShowEndPicker(true)}
          >
            <Ionicons name="calendar-outline" size={20} color={PRIMARY} />
            <Text style={styles.dateText}>{formatDate(endDate)}</Text>
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
          minimumDate={startDate}
        />
      )}

      <View style={styles.inputGroup}>
        <Text style={styles.label}>Total Project Cost (₱) *</Text>
        <View style={styles.currencyInput}>
          <Text style={styles.currencySymbol}>₱</Text>
          <TextInput
            style={styles.currencyTextInput}
            value={totalProjectCost}
            onChangeText={setTotalProjectCost}
            placeholder="0.00"
            placeholderTextColor="#999"
            keyboardType="decimal-pad"
          />
        </View>
      </View>

      {paymentMode === 'downpayment' && (
        <View style={styles.inputGroup}>
          <Text style={styles.label}>Downpayment Amount (₱) *</Text>
          <View style={styles.currencyInput}>
            <Text style={styles.currencySymbol}>₱</Text>
            <TextInput
              style={styles.currencyTextInput}
              value={downpaymentAmount}
              onChangeText={setDownpaymentAmount}
              placeholder="0.00"
              placeholderTextColor="#999"
              keyboardType="decimal-pad"
            />
          </View>
          {totalProjectCost && downpaymentAmount && (
            <Text style={styles.remainingBalance}>
              Remaining Balance: ₱{(parseFloat(totalProjectCost) - parseFloat(downpaymentAmount)).toLocaleString('en-PH', { minimumFractionDigits: 2 })}
            </Text>
          )}
        </View>
      )}
    </View>
  );

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

      {milestoneItems.map((item, index) => (
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

          <View style={styles.inputGroup}>
            <Text style={styles.smallLabel}>Target Completion Date *</Text>
            <TouchableOpacity
              style={styles.datePickerButton}
              onPress={() => {
                if (!milestoneItemDates[item.id]) {
                  setMilestoneItemDates(prev => ({ ...prev, [item.id]: startDate }));
                }
                setShowDatePicker(prev => ({ ...prev, [item.id]: true }));
              }}
            >
              <Ionicons name="calendar-outline" size={20} color={PRIMARY} />
              <Text style={styles.datePickerText}>
                {item.date_to_finish || 'Select Date'}
              </Text>
            </TouchableOpacity>
            {showDatePicker[item.id] && (
              <DateTimePicker
                value={milestoneItemDates[item.id] || startDate}
                mode="date"
                display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                minimumDate={startDate}
                maximumDate={endDate}
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
      ))}

      <TouchableOpacity style={styles.addButton} onPress={addMilestoneItem}>
        <Ionicons name="add-circle-outline" size={24} color={PRIMARY} />
        <Text style={styles.addButtonText}>Add Milestone Item</Text>
      </TouchableOpacity>
    </View>
  );

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity style={styles.backButton} onPress={onClose}>
          <Ionicons name="arrow-back" size={24} color="#FFF" />
        </TouchableOpacity>
        <View style={styles.headerContent}>
          <Text style={styles.headerTitle}>Setup Milestones</Text>
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
                <Text style={styles.submitButtonText}>Submit Milestones</Text>
              </>
            )}
          </TouchableOpacity>
        )}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  header: {
    backgroundColor: PRIMARY,
    paddingTop: 50,
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
    paddingVertical: 20,
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
    padding: 20,
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
    fontSize: 16,
    color: '#333',
    marginLeft: 10,
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
});

export default MilestoneSetup;
