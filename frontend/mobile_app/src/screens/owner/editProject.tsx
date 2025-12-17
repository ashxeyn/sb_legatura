// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  TextInput,
  ActivityIndicator,
  Alert,
  Platform,
} from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import DateTimePicker from '@react-native-community/datetimepicker';
import { Picker } from '@react-native-picker/picker';
import { projects_service } from '../../services/projects_service';

const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
  success: '#10B981',
  error: '#EF4444',
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
  project_description: string;
  project_location: string;
  budget_range_min: number;
  budget_range_max: number;
  lot_size?: number;
  floor_area?: number;
  property_type: string;
  type_name: string;
  type_id?: number;
  project_status: string;
  project_post_status: string;
  bidding_deadline?: string;
  created_at: string;
}

interface ContractorType {
  type_id: number;
  type_name: string;
}

interface EditProjectProps {
  project: Project;
  userId: number;
  onClose: () => void;
  onSave: (updatedProject: Project) => void;
}

export default function EditProject({ project, userId, onClose, onSave }: EditProjectProps) {
  const insets = useSafeAreaInsets();

  // Form state
  const [title, setTitle] = useState(project.project_title);
  const [description, setDescription] = useState(project.project_description);
  const [location, setLocation] = useState(project.project_location);
  const [budgetMin, setBudgetMin] = useState(project.budget_range_min.toString());
  const [budgetMax, setBudgetMax] = useState(project.budget_range_max.toString());
  const [lotSize, setLotSize] = useState(project.lot_size?.toString() || '');
  const [floorArea, setFloorArea] = useState(project.floor_area?.toString() || '');
  const [propertyType, setPropertyType] = useState(project.property_type);
  const [selectedTypeId, setSelectedTypeId] = useState<number | null>(project.type_id || null);
  const [biddingDeadline, setBiddingDeadline] = useState(
    project.bidding_deadline ? new Date(project.bidding_deadline) : new Date()
  );
  const [showDatePicker, setShowDatePicker] = useState(false);

  // Data state
  const [contractorTypes, setContractorTypes] = useState<ContractorType[]>([]);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);

  const propertyTypes = ['Residential', 'Commercial', 'Industrial', 'Mixed-Use'];

  useEffect(() => {
    fetchContractorTypes();
  }, []);

  const fetchContractorTypes = async () => {
    try {
      setLoading(true);
      const response = await projects_service.get_contractor_types();
      if (response.success && response.data) {
        // Handle nested data structure from API
        const typesData = response.data?.data || response.data || [];
        setContractorTypes(Array.isArray(typesData) ? typesData : []);
      } else {
        setContractorTypes([]);
      }
    } catch (error) {
      console.error('Error fetching contractor types:', error);
      setContractorTypes([]);
    } finally {
      setLoading(false);
    }
  };

  const formatNumberWithCommas = (value: string) => {
    const numericValue = value.replace(/[^0-9]/g, '');
    if (numericValue === '') return '';
    return parseInt(numericValue, 10).toLocaleString('en-US');
  };

  const parseFormattedNumber = (value: string) => {
    return value.replace(/,/g, '');
  };

  const handleBudgetMinChange = (text: string) => {
    const formatted = formatNumberWithCommas(text);
    setBudgetMin(formatted);
  };

  const handleBudgetMaxChange = (text: string) => {
    const formatted = formatNumberWithCommas(text);
    setBudgetMax(formatted);
  };

  const handleDateChange = (event: any, selectedDate?: Date) => {
    setShowDatePicker(Platform.OS === 'ios');
    if (selectedDate) {
      setBiddingDeadline(selectedDate);
    }
  };

  const formatDate = (date: Date) => {
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const validateForm = () => {
    if (!title.trim()) {
      Alert.alert('Validation Error', 'Please enter a project title.');
      return false;
    }
    if (!description.trim()) {
      Alert.alert('Validation Error', 'Please enter a project description.');
      return false;
    }
    if (!location.trim()) {
      Alert.alert('Validation Error', 'Please enter a project location.');
      return false;
    }
    if (!budgetMin || !budgetMax) {
      Alert.alert('Validation Error', 'Please enter budget range.');
      return false;
    }
    const minBudget = parseInt(parseFormattedNumber(budgetMin), 10);
    const maxBudget = parseInt(parseFormattedNumber(budgetMax), 10);
    if (minBudget >= maxBudget) {
      Alert.alert('Validation Error', 'Maximum budget must be greater than minimum budget.');
      return false;
    }
    if (!selectedTypeId) {
      Alert.alert('Validation Error', 'Please select a contractor type.');
      return false;
    }
    return true;
  };

  const handleSave = async () => {
    if (!validateForm()) return;

    try {
      setSaving(true);

      const updatedData = {
        project_id: project.project_id,
        user_id: userId,
        project_title: title.trim(),
        project_description: description.trim(),
        project_location: location.trim(),
        budget_range_min: parseInt(parseFormattedNumber(budgetMin), 10),
        budget_range_max: parseInt(parseFormattedNumber(budgetMax), 10),
        lot_size: lotSize ? parseInt(lotSize, 10) : null,
        floor_area: floorArea ? parseInt(floorArea, 10) : null,
        property_type: propertyType,
        type_id: selectedTypeId,
        bidding_deadline: biddingDeadline.toISOString().split('T')[0],
      };

      // Call API to update project
      const result = await projects_service.update_project(updatedData);

      if (result.success) {
        Alert.alert('Success', 'Project updated successfully!', [
          { text: 'OK', onPress: () => onSave({ ...project, ...updatedData }) }
        ]);
      } else {
        Alert.alert('Error', result.message || 'Failed to update project.');
      }
    } catch (error) {
      console.error('Error updating project:', error);
      Alert.alert('Error', 'Failed to update project. Please try again.');
    } finally {
      setSaving(false);
    }
  };

  const renderInput = (
    label: string,
    value: string,
    onChangeText: (text: string) => void,
    placeholder: string,
    options: {
      multiline?: boolean;
      keyboardType?: 'default' | 'numeric';
      prefix?: string;
      suffix?: string;
    } = {}
  ) => (
    <View style={styles.inputGroup}>
      <Text style={styles.inputLabel}>{label}</Text>
      <View style={styles.inputWrapper}>
        {options.prefix && <Text style={styles.inputPrefix}>{options.prefix}</Text>}
        <TextInput
          style={[
            styles.input,
            options.multiline && styles.inputMultiline,
            options.prefix && styles.inputWithPrefix,
            options.suffix && styles.inputWithSuffix,
          ]}
          value={value}
          onChangeText={onChangeText}
          placeholder={placeholder}
          placeholderTextColor={COLORS.textMuted}
          multiline={options.multiline}
          keyboardType={options.keyboardType}
          numberOfLines={options.multiline ? 4 : 1}
          textAlignVertical={options.multiline ? 'top' : 'center'}
        />
        {options.suffix && <Text style={styles.inputSuffix}>{options.suffix}</Text>}
      </View>
    </View>
  );

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onClose} style={styles.backButton}>
          <Feather name="x" size={24} color={COLORS.text} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Edit Project</Text>
        <TouchableOpacity
          onPress={handleSave}
          style={[styles.saveButton, saving && styles.saveButtonDisabled]}
          disabled={saving}
        >
          {saving ? (
            <ActivityIndicator size="small" color="#FFFFFF" />
          ) : (
            <Text style={styles.saveButtonText}>Save</Text>
          )}
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
      >
        {/* Basic Information */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Basic Information</Text>

          {renderInput('Project Title', title, setTitle, 'Enter project title')}

          {renderInput(
            'Description',
            description,
            setDescription,
            'Describe your project in detail...',
            { multiline: true }
          )}
        </View>

        {/* Location */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Location</Text>
          {renderInput('Project Location', location, setLocation, 'Enter full address')}
        </View>

        {/* Budget */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Budget Range</Text>
          <View style={styles.budgetRow}>
            <View style={styles.budgetInput}>
              {renderInput(
                'Minimum',
                budgetMin,
                handleBudgetMinChange,
                '0',
                { keyboardType: 'numeric', prefix: '₱' }
              )}
            </View>
            <View style={styles.budgetInput}>
              {renderInput(
                'Maximum',
                budgetMax,
                handleBudgetMaxChange,
                '0',
                { keyboardType: 'numeric', prefix: '₱' }
              )}
            </View>
          </View>
        </View>

        {/* Property Details */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Property Details</Text>

          {/* Property Type Picker */}
          <View style={styles.inputGroup}>
            <Text style={styles.inputLabel}>Property Type</Text>
            <View style={styles.pickerWrapper}>
              <Picker
                selectedValue={propertyType}
                onValueChange={(value) => setPropertyType(value)}
                style={styles.picker}
              >
                {propertyTypes.map((type) => (
                  <Picker.Item key={type} label={type} value={type} />
                ))}
              </Picker>
            </View>
          </View>

          {/* Contractor Type Picker */}
          <View style={styles.inputGroup}>
            <Text style={styles.inputLabel}>Contractor Type</Text>
            <View style={styles.pickerWrapper}>
              {loading ? (
                <View style={styles.loadingPicker}>
                  <ActivityIndicator size="small" color={COLORS.primary} />
                  <Text style={styles.loadingText}>Loading types...</Text>
                </View>
              ) : (
                <Picker
                  selectedValue={selectedTypeId}
                  onValueChange={(value) => setSelectedTypeId(value)}
                  style={styles.picker}
                >
                  <Picker.Item label="Select contractor type" value={null} />
                  {contractorTypes.map((type) => (
                    <Picker.Item
                      key={type.type_id}
                      label={type.type_name}
                      value={type.type_id}
                    />
                  ))}
                </Picker>
              )}
            </View>
          </View>

          <View style={styles.sizeRow}>
            <View style={styles.sizeInput}>
              {renderInput(
                'Lot Size',
                lotSize,
                setLotSize,
                '0',
                { keyboardType: 'numeric', suffix: 'sqm' }
              )}
            </View>
            <View style={styles.sizeInput}>
              {renderInput(
                'Floor Area',
                floorArea,
                setFloorArea,
                '0',
                { keyboardType: 'numeric', suffix: 'sqm' }
              )}
            </View>
          </View>
        </View>

        {/* Bidding Deadline */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Bidding Deadline</Text>
          <TouchableOpacity
            style={styles.dateButton}
            onPress={() => setShowDatePicker(true)}
            activeOpacity={0.7}
          >
            <Feather name="calendar" size={20} color={COLORS.primary} />
            <Text style={styles.dateText}>{formatDate(biddingDeadline)}</Text>
            <Feather name="chevron-down" size={20} color={COLORS.textMuted} />
          </TouchableOpacity>
        </View>

        {showDatePicker && (
          <DateTimePicker
            value={biddingDeadline}
            mode="date"
            display={Platform.OS === 'ios' ? 'spinner' : 'default'}
            onChange={handleDateChange}
            minimumDate={new Date()}
          />
        )}

        <View style={{ height: 40 }} />
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
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.text,
  },
  saveButton: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 8,
    minWidth: 70,
    alignItems: 'center',
  },
  saveButtonDisabled: {
    opacity: 0.7,
  },
  saveButtonText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    padding: 16,
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 12,
  },
  inputGroup: {
    marginBottom: 16,
  },
  inputLabel: {
    fontSize: 14,
    fontWeight: '500',
    color: COLORS.textSecondary,
    marginBottom: 8,
  },
  inputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  input: {
    flex: 1,
    fontSize: 15,
    color: COLORS.text,
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  inputMultiline: {
    minHeight: 120,
    paddingTop: 14,
  },
  inputWithPrefix: {
    paddingLeft: 8,
  },
  inputWithSuffix: {
    paddingRight: 8,
  },
  inputPrefix: {
    fontSize: 15,
    color: COLORS.textSecondary,
    paddingLeft: 16,
  },
  inputSuffix: {
    fontSize: 14,
    color: COLORS.textMuted,
    paddingRight: 16,
  },
  budgetRow: {
    flexDirection: 'row',
    gap: 12,
  },
  budgetInput: {
    flex: 1,
  },
  sizeRow: {
    flexDirection: 'row',
    gap: 12,
  },
  sizeInput: {
    flex: 1,
  },
  pickerWrapper: {
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
    overflow: 'hidden',
  },
  picker: {
    height: 50,
  },
  loadingPicker: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    height: 50,
    gap: 10,
  },
  loadingText: {
    fontSize: 14,
    color: COLORS.textMuted,
  },
  dateButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
    paddingHorizontal: 16,
    paddingVertical: 14,
    gap: 12,
  },
  dateText: {
    flex: 1,
    fontSize: 15,
    color: COLORS.text,
  },
});
