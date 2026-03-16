// @ts-nocheck
import React, { useState, useEffect, useMemo } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Modal,
  ScrollView,
  TextInput,
  ActivityIndicator,
  FlatList,
  Dimensions,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons, MaterialIcons, Feather } from '@expo/vector-icons';
import { search_service } from '../services/search_service';
import { auth_service } from '../services/auth_service';

const { height: SCREEN_HEIGHT } = Dimensions.get('window');

// Color palette (matching myProjects)
const COLORS = {
  primary: '#EC7E00',
  primaryLight: '#FFF3E6',
  primaryDark: '#C96A00',
  success: '#10B981',
  successLight: '#D1FAE5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
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

interface FeedFilterModalProps {
  visible: boolean;
  onClose: () => void;
  onApply: (filters: any) => void;
  userType: 'contractor' | 'property_owner';
}

export default function FeedFilterModal({
  visible,
  onClose,
  onApply,
  userType,
}: FeedFilterModalProps) {
  const insets = useSafeAreaInsets();
  const [filterOptions, setFilterOptions] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  
  // Filter states
  const [selectedTypeId, setSelectedTypeId] = useState<number | null>(null);
  const [selectedProvince, setSelectedProvince] = useState<string>('');
  const [selectedProvinceCode, setSelectedProvinceCode] = useState<string>('');
  const [selectedCity, setSelectedCity] = useState<string>('');
  const [selectedPropertyType, setSelectedPropertyType] = useState<string>('');
  const [minExperience, setMinExperience] = useState<string>('');
  const [maxExperience, setMaxExperience] = useState<string>('');
  const [picabCategory, setPicabCategory] = useState<string>('');
  const [minCompleted, setMinCompleted] = useState<string>('');
  const [budgetMin, setBudgetMin] = useState<string>('');
  const [budgetMax, setBudgetMax] = useState<string>('');
  
  // Picker states
  const [showTypePicker, setShowTypePicker] = useState(false);
  const [showProvincePicker, setShowProvincePicker] = useState(false);
  const [showCityPicker, setShowCityPicker] = useState(false);
  const [showPropertyTypePicker, setShowPropertyTypePicker] = useState(false);
  const [showPicabPicker, setShowPicabPicker] = useState(false);
  
  // Data
  const [provinces, setProvinces] = useState<any[]>([]);
  const [cities, setCities] = useState<any[]>([]);
  const [loadingProvinces, setLoadingProvinces] = useState(false);
  const [loadingCities, setLoadingCities] = useState(false);

  // Load filter options
  useEffect(() => {
    if (visible && !filterOptions) {
      setLoading(true);
      search_service.get_filter_options()
        .then(res => {
          if (res.success && res.data) {
            setFilterOptions(res.data);
          }
        })
        .finally(() => setLoading(false));
    }
  }, [visible]);

  // Load provinces
  useEffect(() => {
    if (visible && provinces.length === 0 && !loadingProvinces) {
      setLoadingProvinces(true);
      auth_service.get_provinces()
        .then(res => {
          if (res.success && res.data) {
            setProvinces(res.data.sort((a, b) => a.name.localeCompare(b.name)));
          }
        })
        .finally(() => setLoadingProvinces(false));
    }
  }, [visible]);

  // Load cities when province changes
  useEffect(() => {
    if (selectedProvinceCode) {
      setLoadingCities(true);
      setCities([]);
      setSelectedCity('');
      auth_service.get_cities_by_province(selectedProvinceCode)
        .then(res => {
          if (res.success && res.data) {
            setCities(res.data.sort((a, b) => a.name.localeCompare(b.name)));
          }
        })
        .finally(() => setLoadingCities(false));
    } else {
      setCities([]);
      setSelectedCity('');
    }
  }, [selectedProvinceCode]);

  const handleReset = () => {
    setSelectedTypeId(null);
    setSelectedProvince('');
    setSelectedProvinceCode('');
    setSelectedCity('');
    setSelectedPropertyType('');
    setMinExperience('');
    setMaxExperience('');
    setPicabCategory('');
    setMinCompleted('');
    setBudgetMin('');
    setBudgetMax('');
  };

  const handleApply = () => {
    const filters: any = {};
    if (selectedTypeId) filters.type_id = selectedTypeId;
    if (selectedProvince) filters.province = selectedProvince;
    if (selectedCity) filters.city = selectedCity;
    if (selectedPropertyType) filters.property_type = selectedPropertyType;
    if (minExperience) filters.min_experience = parseInt(minExperience, 10);
    if (maxExperience) filters.max_experience = parseInt(maxExperience, 10);
    if (picabCategory) filters.picab_category = picabCategory;
    if (minCompleted) filters.min_completed = parseInt(minCompleted, 10);
    if (budgetMin) filters.budget_min = parseFloat(budgetMin);
    if (budgetMax) filters.budget_max = parseFloat(budgetMax);
    
    onApply(filters);
    onClose();
  };

  const types = useMemo(() => {
    if (!filterOptions?.contractor_types) return [];
    return filterOptions.contractor_types;
  }, [filterOptions]);

  const selectedTypeName = useMemo(() => {
    if (!selectedTypeId) return '';
    const found = types.find((t: any) => t.type_id === selectedTypeId);
    return found?.type_name || '';
  }, [selectedTypeId, types]);

  const propertyTypes = useMemo(() => {
    if (!filterOptions?.property_types) return [];
    return filterOptions.property_types;
  }, [filterOptions]);

  const picabCategories = useMemo(() => {
    return filterOptions?.picab_categories || ['AAAA', 'AAA', 'AA', 'A', 'B', 'C', 'D', 'Trade/E'];
  }, [filterOptions]);

  const activeFilterCount = useMemo(() => {
    let count = 0;
    if (selectedTypeId) count++;
    if (selectedProvince) count++;
    if (selectedCity) count++;
    if (selectedPropertyType) count++;
    if (minExperience) count++;
    if (maxExperience) count++;
    if (picabCategory) count++;
    if (minCompleted) count++;
    if (budgetMin) count++;
    if (budgetMax) count++;
    return count;
  }, [selectedTypeId, selectedProvince, selectedCity, selectedPropertyType, minExperience, maxExperience, picabCategory, minCompleted, budgetMin, budgetMax]);

  const renderPicker = (
    visible: boolean,
    onClose: () => void,
    title: string,
    items: any[],
    onSelect: (item: any) => void,
    loading: boolean = false,
  ) => (
    <Modal visible={visible} transparent animationType="slide">
      <View style={styles.pickerOverlay}>
        <TouchableOpacity style={styles.pickerBackdrop} activeOpacity={1} onPress={onClose} />
        <View style={[styles.pickerContainer, { paddingBottom: insets.bottom + 16 }]}>
          <View style={styles.pickerHeader}>
            <Text style={styles.pickerTitle}>{title}</Text>
            <TouchableOpacity onPress={onClose}>
              <MaterialIcons name="close" size={24} color="#333" />
            </TouchableOpacity>
          </View>
          {loading ? (
            <ActivityIndicator size="small" color="#EC7E00" style={{ padding: 20 }} />
          ) : (
            <FlatList
              data={items}
              keyExtractor={(item, index) => index.toString()}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.pickerItem}
                  onPress={() => {
                    onSelect(item);
                    onClose();
                  }}
                >
                  <Text style={styles.pickerItemText}>{item.label || item.name || item}</Text>
                </TouchableOpacity>
              )}
              style={{ maxHeight: 300 }}
            />
          )}
        </View>
      </View>
    </Modal>
  );

  const renderDropdown = (label: string, value: string, placeholder: string, onPress: () => void, onClear?: () => void, disabled: boolean = false) => (
    <View style={styles.field}>
      <View style={styles.dropdownContainer}>
        <TouchableOpacity
          style={[styles.dropdown, disabled && styles.dropdownDisabled]}
          onPress={disabled ? undefined : onPress}
          disabled={disabled}
        >
          <Text style={[styles.dropdownText, !value && styles.dropdownPlaceholder]}>
            {value || placeholder}
          </Text>
          {!value && <MaterialIcons name="keyboard-arrow-down" size={22} color={COLORS.textMuted} />}
          {value && onClear && (
            <TouchableOpacity
              onPress={onClear}
              hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
            >
              <MaterialIcons name="close" size={18} color={COLORS.textMuted} />
            </TouchableOpacity>
          )}
        </TouchableOpacity>
      </View>
    </View>
  );

  const renderTextField = (label: string, value: string, onChangeText: (text: string) => void, placeholder: string) => (
    <View style={styles.field}>
      <Text style={styles.fieldLabel}>{label}</Text>
      <TextInput
        style={styles.textInput}
        value={value}
        onChangeText={onChangeText}
        placeholder={placeholder}
        placeholderTextColor={COLORS.textMuted}
        keyboardType="numeric"
      />
    </View>
  );

  return (
    <Modal
      visible={visible}
      transparent
      animationType="slide"
      onRequestClose={onClose}
    >
      <TouchableOpacity 
        style={styles.sortModalOverlay} 
        activeOpacity={1} 
        onPress={onClose}
      >
        <View style={[styles.sortModalContent, { paddingBottom: insets.bottom + 16 }]} onStartShouldSetResponder={() => true}>
          {/* Modal Header */}
          <View style={styles.modalHeader}>
            <View style={styles.modalDragHandle} />
            <View style={styles.modalTitleRow}>
              <Text style={styles.sortModalTitle}>
                Filter {userType === 'contractor' ? 'Projects' : 'Contractors'}
              </Text>
              {activeFilterCount > 0 && (
                <TouchableOpacity style={styles.resetButton} onPress={handleReset} activeOpacity={0.7}>
                  <Feather name="rotate-ccw" size={14} color={COLORS.error} />
                  <Text style={styles.resetButtonText}>Reset All</Text>
                </TouchableOpacity>
              )}
            </View>
          </View>

          {loading ? (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color={COLORS.primary} />
            </View>
          ) : (
            <ScrollView style={styles.content} showsVerticalScrollIndicator={false} bounces={false}>
              {/* Type */}
              <Text style={styles.sectionLabel}>
                {userType === 'contractor' ? 'Project Type' : 'Contractor Type'}
              </Text>
              {renderDropdown(
                userType === 'contractor' ? 'Project Type' : 'Contractor Type',
                selectedTypeName,
                `All ${userType === 'contractor' ? 'project' : 'contractor'} types`,
                () => setShowTypePicker(true),
                () => setSelectedTypeId(null)
              )}

              {/* Property Type (for contractors viewing projects) */}
              {userType === 'contractor' && (
                <>
                  <Text style={styles.sectionLabel}>Property Type</Text>
                  {renderDropdown(
                    'Property Type',
                    selectedPropertyType,
                    'All property types',
                    () => setShowPropertyTypePicker(true),
                    () => setSelectedPropertyType('')
                  )}
                </>
              )}

              {/* Location Section */}
              <Text style={styles.sectionLabel}>Location</Text>
              {renderDropdown(
                'Province',
                selectedProvince,
                'All provinces',
                () => setShowProvincePicker(true),
                () => {
                  setSelectedProvince('');
                  setSelectedProvinceCode('');
                  setSelectedCity('');
                }
              )}

              {renderDropdown(
                'City / Municipality',
                selectedCity,
                selectedProvince ? 'Select city' : 'Select province first',
                () => setShowCityPicker(true),
                () => setSelectedCity(''),
                !selectedProvinceCode
              )}

              {/* Contractor-specific filters */}
              {userType === 'property_owner' && (
                <>
                  <Text style={styles.sectionLabel}>Experience</Text>
                  <View style={styles.rangeContainer}>
                    <Text style={styles.rangeLabel}>Years of Experience</Text>
                    <View style={styles.rangeRow}>
                      <View style={{ flex: 1 }}>
                        {renderTextField('Min', minExperience, setMinExperience, '0')}
                      </View>
                      <Text style={styles.rangeDash}>–</Text>
                      <View style={{ flex: 1 }}>
                        {renderTextField('Max', maxExperience, setMaxExperience, 'Any')}
                      </View>
                    </View>
                  </View>

                  <Text style={styles.sectionLabel}>Qualifications</Text>
                  {renderDropdown(
                    'PICAB Category',
                    picabCategory,
                    'All categories',
                    () => setShowPicabPicker(true),
                    () => setPicabCategory('')
                  )}

                  {renderTextField('Min. Completed Projects', minCompleted, setMinCompleted, '0')}
                </>
              )}

              {/* Project-specific filters */}
              {userType === 'contractor' && (
                <>
                  <Text style={styles.sectionLabel}>Budget Range</Text>
                  <View style={styles.rangeContainer}>
                    <View style={styles.rangeRow}>
                      <View style={{ flex: 1 }}>
                        {renderTextField('Min (₱)', budgetMin, setBudgetMin, '0')}
                      </View>
                      <Text style={styles.rangeDash}>–</Text>
                      <View style={{ flex: 1 }}>
                        {renderTextField('Max (₱)', budgetMax, setBudgetMax, 'Any')}
                      </View>
                    </View>
                  </View>
                </>
              )}
            </ScrollView>
          )}

          {/* Apply Button - pinned at bottom */}
          <TouchableOpacity style={styles.applyButton} onPress={handleApply} activeOpacity={0.7}>
            <Text style={styles.applyButtonText}>
              Apply{activeFilterCount > 0 ? ` (${activeFilterCount})` : ''}
            </Text>
          </TouchableOpacity>
        </View>
      </TouchableOpacity>

      {/* Pickers */}
      {renderPicker(
        showTypePicker,
        () => setShowTypePicker(false),
        userType === 'contractor' ? 'Project Type' : 'Contractor Type',
        types.map((t: any) => ({ label: t.type_name, value: t.type_id })),
        (item: any) => setSelectedTypeId(item.value)
      )}

      {renderPicker(
        showProvincePicker,
        () => setShowProvincePicker(false),
        'Province',
        provinces,
        (item: any) => {
          setSelectedProvinceCode(item.code);
          setSelectedProvince(item.name);
        },
        loadingProvinces
      )}

      {renderPicker(
        showCityPicker,
        () => setShowCityPicker(false),
        'City / Municipality',
        cities,
        (item: any) => setSelectedCity(item.name),
        loadingCities
      )}

      {userType === 'contractor' && renderPicker(
        showPropertyTypePicker,
        () => setShowPropertyTypePicker(false),
        'Property Type',
        propertyTypes.map((t: string) => ({ label: t, value: t })),
        (item: any) => setSelectedPropertyType(item.value)
      )}

      {userType === 'property_owner' && renderPicker(
        showPicabPicker,
        () => setShowPicabPicker(false),
        'PICAB Category',
        picabCategories.map((c: string) => ({ label: c, value: c })),
        (item: any) => setPicabCategory(item.value)
      )}
    </Modal>
  );
}

const styles = StyleSheet.create({
  sortModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.4)',
    justifyContent: 'flex-end',
  },
  sortModalContent: {
    backgroundColor: COLORS.surface,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    paddingTop: 12,
    paddingBottom: 36,
    maxHeight: '85%',
    width: '100%',
  },
  modalHeader: {
    alignItems: 'center',
    marginBottom: 8,
    paddingHorizontal: 20,
  },
  modalTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    width: '100%',
  },
  modalDragHandle: {
    width: 40,
    height: 4,
    borderRadius: 2,
    backgroundColor: COLORS.border,
    marginBottom: 16,
  },
  sortModalTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 4,
  },
  resetButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingVertical: 4,
    paddingHorizontal: 8,
  },
  resetButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.error,
  },
  loadingContainer: {
    padding: 40,
    alignItems: 'center',
  },
  content: {
    paddingHorizontal: 0,
    paddingVertical: 8,
    width: '100%',
  },
  sectionLabel: {
    fontSize: 13,
    fontWeight: '700',
    color: COLORS.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
    marginBottom: 10,
    marginTop: 16,
    paddingHorizontal: 20,
    width: '100%',
  },
  field: {
    marginBottom: 12,
    paddingHorizontal: 20,
    width: '100%',
  },
  fieldLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  dropdownContainer: {
    position: 'relative',
    width: '100%',
  },
  dropdown: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: COLORS.background,
    borderRadius: 10,
    paddingHorizontal: 14,
    paddingVertical: 12,
    borderWidth: 1,
    borderColor: COLORS.border,
    width: '100%',
  },
  dropdownDisabled: {
    opacity: 0.5,
  },
  dropdownText: {
    fontSize: 15,
    color: COLORS.text,
    flex: 1,
  },
  dropdownPlaceholder: {
    color: COLORS.textMuted,
  },
  clearButton: {
    position: 'absolute',
    right: 40,
    top: 12,
    padding: 4,
    backgroundColor: COLORS.surface,
    borderRadius: 12,
  },
  textInput: {
    backgroundColor: COLORS.background,
    borderRadius: 10,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 15,
    color: COLORS.text,
    borderWidth: 1,
    borderColor: COLORS.border,
    width: '100%',
  },
  rangeContainer: {
    marginBottom: 12,
    paddingHorizontal: 20,
    width: '100%',
  },
  rangeLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 8,
  },
  rangeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    width: '100%',
  },
  rangeDash: {
    fontSize: 18,
    color: COLORS.textMuted,
    paddingTop: 20,
  },
  applyButton: {
    backgroundColor: COLORS.primary,
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 16,
    marginHorizontal: 20,
  },
  applyButtonText: {
    color: COLORS.surface,
    fontSize: 16,
    fontWeight: '700',
  },
  pickerOverlay: {
    flex: 1,
    justifyContent: 'flex-end',
  },
  pickerBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.4)',
  },
  pickerContainer: {
    backgroundColor: COLORS.surface,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    paddingTop: 16,
  },
  pickerHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  pickerTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: COLORS.text,
  },
  pickerItem: {
    paddingHorizontal: 20,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.borderLight,
  },
  pickerItemText: {
    fontSize: 15,
    color: COLORS.text,
  },
});
