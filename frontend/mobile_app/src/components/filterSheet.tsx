// @ts-nocheck
import React, { useState, useEffect, useMemo, useCallback } from 'react';
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
  Platform,
  StatusBar,
  Keyboard,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import { auth_service } from '../services/auth_service';
import {
  search_service,
  ContractorFilters,
  ProjectFilters,
  FilterOptions,
  ContractorType,
} from '../services/search_service';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

/* ===================================================================
 * Props
 * =================================================================== */

interface FilterSheetProps {
  visible: boolean;
  onClose: () => void;
  onApply: (filters: ContractorFilters | ProjectFilters) => void;
  searchType: 'contractors' | 'projects';
  initialFilters?: ContractorFilters | ProjectFilters;
}

/* ===================================================================
 * Province / City types (from auth_service / PSGC)
 * =================================================================== */

interface Province {
  code: string;
  name: string;
}

interface City {
  code: string;
  name: string;
}

/* ===================================================================
 * Component
 * =================================================================== */

export default function FilterSheet({
  visible,
  onClose,
  onApply,
  searchType,
  initialFilters = {},
}: FilterSheetProps) {
  const insets = useSafeAreaInsets();

  // ── Filter options from API ──────────────────────────────────────
  const [filterOptions, setFilterOptions] = useState<FilterOptions | null>(null);
  const [loadingOptions, setLoadingOptions] = useState(false);

  // ── Address data ──────────────────────────────────────────────────
  const [provinces, setProvinces] = useState<Province[]>([]);
  const [cities, setCities] = useState<City[]>([]);
  const [loadingProvinces, setLoadingProvinces] = useState(false);
  const [loadingCities, setLoadingCities] = useState(false);

  // ── Local filter state ────────────────────────────────────────────
  const [selectedTypeId, setSelectedTypeId] = useState<number | undefined>(undefined);
  const [selectedProvince, setSelectedProvince] = useState<string>('');
  const [selectedProvinceCode, setSelectedProvinceCode] = useState<string>('');
  const [selectedCity, setSelectedCity] = useState<string>('');
  const [minExperience, setMinExperience] = useState<string>('');
  const [maxExperience, setMaxExperience] = useState<string>('');
  const [picabCategory, setPicabCategory] = useState<string>('');
  const [minCompleted, setMinCompleted] = useState<string>('');
  const [selectedPropertyType, setSelectedPropertyType] = useState<string>('');
  const [budgetMin, setBudgetMin] = useState<string>('');
  const [budgetMax, setBudgetMax] = useState<string>('');
  const [projectStatus, setProjectStatus] = useState<string>('open');

  // ── Picker modals ─────────────────────────────────────────────────
  const [showTypePicker, setShowTypePicker] = useState(false);
  const [showProvincePicker, setShowProvincePicker] = useState(false);
  const [showCityPicker, setShowCityPicker] = useState(false);
  const [showPropertyTypePicker, setShowPropertyTypePicker] = useState(false);
  const [showPicabPicker, setShowPicabPicker] = useState(false);
  const [showStatusPicker, setShowStatusPicker] = useState(false);
  const [pickerSearchText, setPickerSearchText] = useState('');

  // ── Load filter options once the sheet becomes visible ────────────
  useEffect(() => {
    if (visible && !filterOptions && !loadingOptions) {
      setLoadingOptions(true);
      search_service.get_filter_options().then(res => {
        if (res.success && res.data) {
          setFilterOptions(res.data);
        }
      }).finally(() => setLoadingOptions(false));
    }
  }, [visible]);

  // ── Load provinces once ───────────────────────────────────────────
  useEffect(() => {
    if (visible && provinces.length === 0 && !loadingProvinces) {
      setLoadingProvinces(true);
      auth_service.get_provinces().then(res => {
        if (res.success && res.data) {
          const sorted = res.data.sort((a, b) => a.name.localeCompare(b.name));
          setProvinces(sorted);
        }
      }).finally(() => setLoadingProvinces(false));
    }
  }, [visible]);

  // ── Load cities when province changes ─────────────────────────────
  useEffect(() => {
    if (selectedProvinceCode) {
      setLoadingCities(true);
      setCities([]);
      setSelectedCity('');
      auth_service.get_cities_by_province(selectedProvinceCode).then(res => {
        if (res.success && res.data) {
          const sorted = res.data.sort((a, b) => a.name.localeCompare(b.name));
          setCities(sorted);
        }
      }).finally(() => setLoadingCities(false));
    } else {
      setCities([]);
      setSelectedCity('');
    }
  }, [selectedProvinceCode]);

  // ── Initialize from initialFilters when sheet opens ───────────────
  useEffect(() => {
    if (visible) {
      const f = initialFilters as any;
      setSelectedTypeId(f.type_id || undefined);
      setSelectedProvince(f.province || '');
      setSelectedCity(f.city || '');
      setMinExperience(f.min_experience?.toString() || '');
      setMaxExperience(f.max_experience?.toString() || '');
      setPicabCategory(f.picab_category || '');
      setMinCompleted(f.min_completed?.toString() || '');
      setSelectedPropertyType(f.property_type || '');
      setBudgetMin(f.budget_min?.toString() || '');
      setBudgetMax(f.budget_max?.toString() || '');
      setProjectStatus(f.project_status || 'open');
    }
  }, [visible]);

  // ── Count active filters ──────────────────────────────────────────
  const activeFilterCount = useMemo(() => {
    let count = 0;
    if (selectedTypeId) count++;
    if (selectedProvince) count++;
    if (selectedCity) count++;
    if (searchType === 'contractors') {
      if (minExperience) count++;
      if (maxExperience) count++;
      if (picabCategory) count++;
      if (minCompleted) count++;
    } else {
      if (selectedPropertyType) count++;
      if (budgetMin) count++;
      if (budgetMax) count++;
      if (projectStatus && projectStatus !== 'open') count++;
    }
    return count;
  }, [selectedTypeId, selectedProvince, selectedCity, minExperience, maxExperience, picabCategory, minCompleted, selectedPropertyType, budgetMin, budgetMax, projectStatus, searchType]);

  // ── Reset all filters ─────────────────────────────────────────────
  const handleReset = () => {
    setSelectedTypeId(undefined);
    setSelectedProvince('');
    setSelectedProvinceCode('');
    setSelectedCity('');
    setMinExperience('');
    setMaxExperience('');
    setPicabCategory('');
    setMinCompleted('');
    setSelectedPropertyType('');
    setBudgetMin('');
    setBudgetMax('');
    setProjectStatus('open');
  };

  // ── Apply filters ─────────────────────────────────────────────────
  const handleApply = () => {
    Keyboard.dismiss();
    if (searchType === 'contractors') {
      const filters: ContractorFilters = {};
      if (selectedTypeId) filters.type_id = selectedTypeId;
      if (selectedProvince) filters.province = selectedProvince;
      if (selectedCity) filters.city = selectedCity;
      if (minExperience) filters.min_experience = parseInt(minExperience, 10);
      if (maxExperience) filters.max_experience = parseInt(maxExperience, 10);
      if (picabCategory) filters.picab_category = picabCategory;
      if (minCompleted) filters.min_completed = parseInt(minCompleted, 10);
      onApply(filters);
    } else {
      const filters: ProjectFilters = {};
      if (selectedTypeId) filters.type_id = selectedTypeId;
      if (selectedPropertyType) filters.property_type = selectedPropertyType;
      if (selectedProvince) filters.province = selectedProvince;
      if (selectedCity) filters.city = selectedCity;
      if (budgetMin) filters.budget_min = parseFloat(budgetMin);
      if (budgetMax) filters.budget_max = parseFloat(budgetMax);
      if (projectStatus) filters.project_status = projectStatus as any;
      onApply(filters);
    }
    onClose();
  };

  // ── Generic picker modal ──────────────────────────────────────────
  const renderPickerModal = (
    visible: boolean,
    onDismiss: () => void,
    title: string,
    items: { label: string; value: string }[],
    selectedValue: string,
    onSelect: (value: string, label: string) => void,
    loading: boolean = false,
    searchable: boolean = false,
  ) => {
    const filtered = useMemo(() => {
      if (!searchable || !pickerSearchText.trim()) return items;
      const q = pickerSearchText.toLowerCase();
      return items.filter(i => i.label.toLowerCase().includes(q));
    }, [items, pickerSearchText, searchable]);

    return (
      <Modal visible={visible} animationType="slide" transparent statusBarTranslucent>
        <View style={styles.pickerOverlay}>
          <View style={[styles.pickerContainer, { paddingBottom: insets.bottom + 10 }]}>
            {/* Header */}
            <View style={styles.pickerHeader}>
              <Text style={styles.pickerTitle}>{title}</Text>
              <TouchableOpacity onPress={() => { onDismiss(); setPickerSearchText(''); }}>
                <MaterialIcons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>

            {/* Search bar (if searchable) */}
            {searchable && (
              <View style={styles.pickerSearchBar}>
                <MaterialIcons name="search" size={20} color="#999" />
                <TextInput
                  style={styles.pickerSearchInput}
                  placeholder={`Search ${title.toLowerCase()}...`}
                  placeholderTextColor="#999"
                  value={pickerSearchText}
                  onChangeText={setPickerSearchText}
                  autoFocus={false}
                />
                {pickerSearchText.length > 0 && (
                  <TouchableOpacity onPress={() => setPickerSearchText('')}>
                    <Ionicons name="close-circle" size={18} color="#999" />
                  </TouchableOpacity>
                )}
              </View>
            )}

            {/* Loading */}
            {loading ? (
              <View style={styles.pickerLoading}>
                <ActivityIndicator size="small" color="#EC7E00" />
                <Text style={styles.pickerLoadingText}>Loading...</Text>
              </View>
            ) : (
              <FlatList
                data={filtered}
                keyExtractor={(item) => item.value}
                renderItem={({ item }) => {
                  const isSelected = item.value === selectedValue;
                  return (
                    <TouchableOpacity
                      style={[styles.pickerItem, isSelected && styles.pickerItemSelected]}
                      onPress={() => {
                        onSelect(item.value, item.label);
                        onDismiss();
                        setPickerSearchText('');
                      }}
                    >
                      <Text style={[styles.pickerItemText, isSelected && styles.pickerItemTextSelected]}>
                        {item.label}
                      </Text>
                      {isSelected && <MaterialIcons name="check" size={20} color="#EC7E00" />}
                    </TouchableOpacity>
                  );
                }}
                ListEmptyComponent={
                  <Text style={styles.pickerEmpty}>No options available</Text>
                }
                keyboardShouldPersistTaps="handled"
                showsVerticalScrollIndicator={false}
                style={{ maxHeight: 350 }}
              />
            )}

            {/* Clear selection button */}
            {selectedValue ? (
              <TouchableOpacity
                style={styles.pickerClearButton}
                onPress={() => {
                  onSelect('', '');
                  onDismiss();
                  setPickerSearchText('');
                }}
              >
                <Text style={styles.pickerClearText}>Clear Selection</Text>
              </TouchableOpacity>
            ) : null}
          </View>
        </View>
      </Modal>
    );
  };

  // ── Dropdown field helper ─────────────────────────────────────────
  const renderDropdownField = (
    label: string,
    value: string,
    placeholder: string,
    onPress: () => void,
    disabled: boolean = false,
    hint?: string,
  ) => (
    <View style={styles.filterField}>
      <Text style={styles.fieldLabel}>{label}</Text>
      <TouchableOpacity
        style={[styles.dropdownButton, disabled && styles.dropdownButtonDisabled]}
        onPress={disabled ? undefined : onPress}
        activeOpacity={disabled ? 1 : 0.7}
      >
        <Text
          style={[
            styles.dropdownText,
            !value && styles.dropdownPlaceholder,
            disabled && styles.dropdownTextDisabled,
          ]}
          numberOfLines={1}
        >
          {value || placeholder}
        </Text>
        {disabled ? (
          <MaterialIcons name="lock" size={18} color="#BCC0C4" />
        ) : (
          <MaterialIcons name="keyboard-arrow-down" size={22} color="#666" />
        )}
      </TouchableOpacity>
      {hint && <Text style={styles.fieldHint}>{hint}</Text>}
    </View>
  );

  // ── Text input field helper ───────────────────────────────────────
  const renderTextField = (
    label: string,
    value: string,
    onChangeText: (t: string) => void,
    placeholder: string,
    keyboardType: 'default' | 'numeric' = 'default',
  ) => (
    <View style={styles.filterField}>
      <Text style={styles.fieldLabel}>{label}</Text>
      <TextInput
        style={styles.textInput}
        value={value}
        onChangeText={onChangeText}
        placeholder={placeholder}
        placeholderTextColor="#999"
        keyboardType={keyboardType}
      />
    </View>
  );

  // ── Chip group helper ─────────────────────────────────────────────
  const renderChipGroup = (
    label: string,
    options: { label: string; value: string }[],
    selected: string,
    onSelect: (v: string) => void,
  ) => (
    <View style={styles.filterField}>
      <Text style={styles.fieldLabel}>{label}</Text>
      <View style={styles.chipRow}>
        {options.map(opt => {
          const isActive = opt.value === selected;
          return (
            <TouchableOpacity
              key={opt.value}
              style={[styles.chip, isActive && styles.chipActive]}
              onPress={() => onSelect(isActive ? '' : opt.value)}
            >
              <Text style={[styles.chipText, isActive && styles.chipTextActive]}>
                {opt.label}
              </Text>
            </TouchableOpacity>
          );
        })}
      </View>
    </View>
  );

  // ── Prepare picker data ───────────────────────────────────────────
  const typePickerItems = useMemo(() => {
    if (!filterOptions?.contractor_types) return [];
    return filterOptions.contractor_types.map(t => ({
      label: t.type_name,
      value: t.type_id.toString(),
    }));
  }, [filterOptions]);

  const selectedTypeName = useMemo(() => {
    if (!selectedTypeId || !filterOptions?.contractor_types) return '';
    const found = filterOptions.contractor_types.find(t => t.type_id === selectedTypeId);
    return found?.type_name || '';
  }, [selectedTypeId, filterOptions]);

  const provincePickerItems = useMemo(() => {
    return provinces.map(p => ({ label: p.name, value: p.code }));
  }, [provinces]);

  const cityPickerItems = useMemo(() => {
    return cities.map(c => ({ label: c.name, value: c.name }));
  }, [cities]);

  const propertyTypeItems = useMemo(() => {
    if (!filterOptions?.property_types) return [];
    return filterOptions.property_types.map(t => ({ label: t, value: t }));
  }, [filterOptions]);

  const statusItems = useMemo(() => {
    return [
      { label: 'Open for Bidding', value: 'open' },
      { label: 'Completed', value: 'completed' },
      { label: 'All Projects', value: 'all' },
    ];
  }, []);

  const picabItems = useMemo(() => {
    const categories = filterOptions?.picab_categories || ['AAAA', 'AAA', 'AA', 'A', 'B', 'C', 'D', 'Trade/E'];
    return categories.map(c => ({ label: c, value: c }));
  }, [filterOptions]);

  // ── Main render ───────────────────────────────────────────────────
  return (
    <Modal visible={visible} animationType="slide" transparent={false} statusBarTranslucent>
      <View style={[styles.container, { paddingTop: insets.top || (Platform.OS === 'android' ? StatusBar.currentHeight || 24 : 44) }]}>
        <StatusBar hidden={true} />

        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={onClose} style={styles.headerButton}>
            <Ionicons name="close" size={24} color="#333" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>
            Filters {activeFilterCount > 0 ? `(${activeFilterCount})` : ''}
          </Text>
          <TouchableOpacity onPress={handleReset} style={styles.headerButton}>
            <Text style={styles.resetText}>Reset</Text>
          </TouchableOpacity>
        </View>

        {/* Loading state */}
        {loadingOptions ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#EC7E00" />
            <Text style={styles.loadingText}>Loading filter options...</Text>
          </View>
        ) : (
          <ScrollView
            style={styles.scrollContent}
            contentContainerStyle={styles.scrollContentInner}
            showsVerticalScrollIndicator={false}
            keyboardShouldPersistTaps="handled"
          >
            {/* == Contractor type (shared) == */}
            {renderDropdownField(
              searchType === 'contractors' ? 'Contractor Type' : 'Project Type',
              selectedTypeName,
              searchType === 'contractors' ? 'All contractor types' : 'All project types',
              () => setShowTypePicker(true),
            )}

            {/* == Project-specific: Property Type == */}
            {searchType === 'projects' && renderDropdownField(
              'Property Type',
              selectedPropertyType,
              'All property types',
              () => setShowPropertyTypePicker(true),
            )}

            {/* == Project-specific: Status == */}
            {searchType === 'projects' && renderChipGroup(
              'Project Status',
              statusItems,
              projectStatus,
              setProjectStatus,
            )}

            {/* == Location: Province == */}
            {renderDropdownField(
              'Province',
              selectedProvince,
              'All provinces',
              () => setShowProvincePicker(true),
            )}

            {/* == Location: City/Municipality == */}
            {renderDropdownField(
              'City / Municipality',
              selectedCity,
              selectedProvince ? 'Select city' : 'Select a province first',
              () => setShowCityPicker(true),
              !selectedProvinceCode,
              !selectedProvinceCode ? 'Choose a province above to enable this field' : undefined,
            )}

            {/* == Contractor-specific filters == */}
            {searchType === 'contractors' && (
              <>
                {/* Experience range */}
                <Text style={styles.sectionTitle}>Years of Experience</Text>
                <View style={styles.rangeRow}>
                  {renderTextField('Min', minExperience, setMinExperience, '0', 'numeric')}
                  <Text style={styles.rangeDash}>–</Text>
                  {renderTextField('Max', maxExperience, setMaxExperience, 'Any', 'numeric')}
                </View>

                {/* PICAB Category */}
                {renderChipGroup(
                  'PICAB Category',
                  picabItems,
                  picabCategory,
                  setPicabCategory,
                )}

                {/* Minimum completed projects */}
                {renderTextField(
                  'Min. Completed Projects',
                  minCompleted,
                  setMinCompleted,
                  '0',
                  'numeric',
                )}
              </>
            )}

            {/* == Project-specific: Budget Range == */}
            {searchType === 'projects' && (
              <>
                <Text style={styles.sectionTitle}>Budget Range (₱)</Text>
                <View style={styles.rangeRow}>
                  {renderTextField('Min', budgetMin, setBudgetMin, '0', 'numeric')}
                  <Text style={styles.rangeDash}>–</Text>
                  {renderTextField('Max', budgetMax, setBudgetMax, 'Any', 'numeric')}
                </View>
              </>
            )}
          </ScrollView>
        )}

        {/* Apply button */}
        <View style={[styles.bottomBar, { paddingBottom: Math.max(insets.bottom, 16) }]}>
          <TouchableOpacity style={styles.applyButton} onPress={handleApply}>
            <Text style={styles.applyButtonText}>
              Apply Filters{activeFilterCount > 0 ? ` (${activeFilterCount})` : ''}
            </Text>
          </TouchableOpacity>
        </View>
      </View>

      {/* ── Picker Modals ─────────────────────────────────────────── */}
      {renderPickerModal(
        showTypePicker,
        () => setShowTypePicker(false),
        searchType === 'contractors' ? 'Contractor Type' : 'Project Type',
        typePickerItems,
        selectedTypeId?.toString() || '',
        (val) => setSelectedTypeId(val ? parseInt(val, 10) : undefined),
        false,
        true,
      )}
      {renderPickerModal(
        showProvincePicker,
        () => setShowProvincePicker(false),
        'Province',
        provincePickerItems,
        selectedProvinceCode,
        (val, label) => { setSelectedProvinceCode(val); setSelectedProvince(label); },
        loadingProvinces,
        true,
      )}
      {renderPickerModal(
        showCityPicker,
        () => setShowCityPicker(false),
        'City / Municipality',
        cityPickerItems,
        selectedCity,
        (val) => setSelectedCity(val),
        loadingCities,
        true,
      )}
      {searchType === 'projects' && renderPickerModal(
        showPropertyTypePicker,
        () => setShowPropertyTypePicker(false),
        'Property Type',
        propertyTypeItems,
        selectedPropertyType,
        (val) => setSelectedPropertyType(val),
      )}
      {searchType === 'contractors' && renderPickerModal(
        showPicabPicker,
        () => setShowPicabPicker(false),
        'PICAB Category',
        picabItems,
        picabCategory,
        (val) => setPicabCategory(val),
      )}
    </Modal>
  );
}

/* ===================================================================
 * Styles
 * =================================================================== */

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#EEEEEE',
  },
  headerButton: {
    padding: 4,
    minWidth: 50,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1A1A1A',
  },
  resetText: {
    fontSize: 15,
    color: '#EC7E00',
    fontWeight: '600',
    textAlign: 'right',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    fontSize: 14,
    color: '#999',
    marginTop: 12,
  },
  scrollContent: {
    flex: 1,
  },
  scrollContentInner: {
    paddingHorizontal: 16,
    paddingTop: 16,
    paddingBottom: 24,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: '#1A1A1A',
    marginTop: 16,
    marginBottom: 8,
  },
  filterField: {
    marginBottom: 16,
  },
  fieldLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 6,
  },
  dropdownButton: {
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
  dropdownText: {
    fontSize: 15,
    color: '#1A1A1A',
    flex: 1,
  },
  dropdownPlaceholder: {
    color: '#999',
  },
  dropdownButtonDisabled: {
    backgroundColor: '#ECECEC',
    borderColor: '#DCDCDC',
    opacity: 0.6,
  },
  dropdownTextDisabled: {
    color: '#ADADAD',
  },
  fieldHint: {
    fontSize: 12,
    color: '#999',
    marginTop: 4,
    marginLeft: 2,
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
  rangeRow: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    gap: 0,
  },
  rangeDash: {
    fontSize: 18,
    color: '#666',
    marginHorizontal: 10,
    paddingBottom: 12,
  },
  chipRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  chip: {
    paddingHorizontal: 14,
    paddingVertical: 8,
    borderRadius: 20,
    backgroundColor: '#F5F5F5',
    borderWidth: 1,
    borderColor: '#E5E5E5',
  },
  chipActive: {
    backgroundColor: '#FFF3E0',
    borderColor: '#EC7E00',
  },
  chipText: {
    fontSize: 13,
    color: '#666',
    fontWeight: '500',
  },
  chipTextActive: {
    color: '#EC7E00',
    fontWeight: '600',
  },
  bottomBar: {
    paddingHorizontal: 16,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#EEEEEE',
    backgroundColor: '#FFFFFF',
  },
  applyButton: {
    backgroundColor: '#EC7E00',
    borderRadius: 12,
    paddingVertical: 15,
    alignItems: 'center',
  },
  applyButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
  // Picker modal styles
  pickerOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.4)',
    justifyContent: 'flex-end',
  },
  pickerContainer: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    paddingTop: 16,
    maxHeight: '70%',
  },
  pickerHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingBottom: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#EEEEEE',
  },
  pickerTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: '#1A1A1A',
  },
  pickerSearchBar: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F5F5F5',
    borderRadius: 10,
    marginHorizontal: 20,
    marginTop: 12,
    marginBottom: 8,
    paddingHorizontal: 12,
    height: 40,
  },
  pickerSearchInput: {
    flex: 1,
    fontSize: 15,
    color: '#333',
    marginLeft: 8,
    paddingVertical: 0,
  },
  pickerLoading: {
    padding: 30,
    alignItems: 'center',
  },
  pickerLoadingText: {
    fontSize: 13,
    color: '#999',
    marginTop: 8,
  },
  pickerItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#F5F5F5',
  },
  pickerItemSelected: {
    backgroundColor: '#FFF8F0',
  },
  pickerItemText: {
    fontSize: 15,
    color: '#333',
    flex: 1,
  },
  pickerItemTextSelected: {
    color: '#EC7E00',
    fontWeight: '600',
  },
  pickerEmpty: {
    textAlign: 'center',
    fontSize: 14,
    color: '#999',
    padding: 30,
  },
  pickerClearButton: {
    marginHorizontal: 20,
    marginTop: 10,
    paddingVertical: 12,
    alignItems: 'center',
    borderRadius: 10,
    backgroundColor: '#F5F5F5',
  },
  pickerClearText: {
    fontSize: 15,
    color: '#E74C3C',
    fontWeight: '600',
  },
});
