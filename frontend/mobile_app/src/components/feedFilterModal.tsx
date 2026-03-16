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
import { Ionicons, MaterialIcons } from '@expo/vector-icons';
import { search_service } from '../services/search_service';
import { auth_service } from '../services/auth_service';

const { height: SCREEN_HEIGHT } = Dimensions.get('window');

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
      <Text style={styles.fieldLabel}>{label}</Text>
      <View style={styles.dropdownContainer}>
        <TouchableOpacity
          style={[styles.dropdown, disabled && styles.dropdownDisabled]}
          onPress={disabled ? undefined : onPress}
          disabled={disabled}
        >
          <Text style={[styles.dropdownText, !value && styles.dropdownPlaceholder]}>
            {value || placeholder}
          </Text>
          <MaterialIcons name="keyboard-arrow-down" size={22} color="#666" />
        </TouchableOpacity>
        {value && onClear && (
          <TouchableOpacity
            style={styles.clearButton}
            onPress={onClear}
            hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}
          >
            <MaterialIcons name="close" size={18} color="#999" />
          </TouchableOpacity>
        )}
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
        placeholderTextColor="#999"
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
      <View style={styles.overlay}>
        <TouchableOpacity 
          style={styles.backdrop} 
          activeOpacity={1} 
          onPress={onClose}
        />
        
        <View style={[styles.container, { paddingBottom: insets.bottom + 16 }]}>
          <View style={styles.header}>
            <Text style={styles.title}>
              Filter {userType === 'contractor' ? 'Projects' : 'Contractors'}
            </Text>
            <TouchableOpacity onPress={onClose} style={styles.closeButton}>
              <Ionicons name="close" size={24} color="#333" />
            </TouchableOpacity>
          </View>

          {loading ? (
            <View style={styles.loadingContainer}>
              <ActivityIndicator size="large" color="#EC7E00" />
            </View>
          ) : (
            <ScrollView style={styles.content} showsVerticalScrollIndicator={false}>
              {/* Type */}
              {renderDropdown(
                userType === 'contractor' ? 'Project Type' : 'Contractor Type',
                selectedTypeName,
                `All ${userType === 'contractor' ? 'project' : 'contractor'} types`,
                () => setShowTypePicker(true),
                () => setSelectedTypeId(null)
              )}

              {/* Property Type (for contractors viewing projects) */}
              {userType === 'contractor' && renderDropdown(
                'Property Type',
                selectedPropertyType,
                'All property types',
                () => setShowPropertyTypePicker(true),
                () => setSelectedPropertyType('')
              )}

              {/* Province */}
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

              {/* City */}
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
                  <View style={styles.rangeContainer}>
                    <Text style={styles.sectionLabel}>Years of Experience</Text>
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
                <View style={styles.rangeContainer}>
                  <Text style={styles.sectionLabel}>Budget Range (₱)</Text>
                  <View style={styles.rangeRow}>
                    <View style={{ flex: 1 }}>
                      {renderTextField('Min', budgetMin, setBudgetMin, '0')}
                    </View>
                    <Text style={styles.rangeDash}>–</Text>
                    <View style={{ flex: 1 }}>
                      {renderTextField('Max', budgetMax, setBudgetMax, 'Any')}
                    </View>
                  </View>
                </View>
              )}
            </ScrollView>
          )}

          <View style={styles.footer}>
            <TouchableOpacity style={styles.resetButton} onPress={handleReset}>
              <Text style={styles.resetButtonText}>Reset</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.applyButton} onPress={handleApply}>
              <Text style={styles.applyButtonText}>
                Apply{activeFilterCount > 0 ? ` (${activeFilterCount})` : ''}
              </Text>
            </TouchableOpacity>
          </View>
        </View>
      </View>

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
  overlay: {
    flex: 1,
    justifyContent: 'flex-end',
  },
  backdrop: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
  },
  container: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: SCREEN_HEIGHT * 0.8,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  title: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1A1A1A',
  },
  closeButton: {
    padding: 4,
  },
  loadingContainer: {
    padding: 40,
    alignItems: 'center',
  },
  content: {
    paddingHorizontal: 20,
    paddingVertical: 16,
  },
  field: {
    marginBottom: 16,
  },
  fieldLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  dropdownContainer: {
    position: 'relative',
  },
  dropdown: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: '#F5F5F5',
    borderRadius: 10,
    paddingHorizontal: 14,
    paddingVertical: 12,
    borderWidth: 1,
    borderColor: '#E5E5E5',
  },
  dropdownDisabled: {
    opacity: 0.5,
  },
  dropdownText: {
    fontSize: 15,
    color: '#1A1A1A',
    flex: 1,
  },
  dropdownPlaceholder: {
    color: '#999',
  },
  clearButton: {
    position: 'absolute',
    right: 40,
    top: 12,
    padding: 4,
    backgroundColor: '#FFF',
    borderRadius: 12,
  },
  textInput: {
    backgroundColor: '#F5F5F5',
    borderRadius: 10,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 15,
    color: '#1A1A1A',
    borderWidth: 1,
    borderColor: '#E5E5E5',
  },
  sectionLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  rangeContainer: {
    marginBottom: 16,
  },
  rangeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  rangeDash: {
    fontSize: 18,
    color: '#666',
    paddingTop: 20,
  },
  footer: {
    flexDirection: 'row',
    paddingHorizontal: 20,
    paddingTop: 16,
    gap: 12,
    borderTopWidth: 1,
    borderTopColor: '#F0F0F0',
  },
  resetButton: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 10,
    backgroundColor: '#F5F5F5',
    alignItems: 'center',
  },
  resetButtonText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#666',
  },
  applyButton: {
    flex: 2,
    paddingVertical: 14,
    borderRadius: 10,
    backgroundColor: '#EC7E00',
    alignItems: 'center',
  },
  applyButtonText: {
    fontSize: 15,
    fontWeight: '700',
    color: '#FFFFFF',
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
    backgroundColor: '#FFFFFF',
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
    borderBottomColor: '#F0F0F0',
  },
  pickerTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: '#1A1A1A',
  },
  pickerItem: {
    paddingHorizontal: 20,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#F5F5F5',
  },
  pickerItemText: {
    fontSize: 15,
    color: '#333',
  },
});
