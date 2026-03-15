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
  Modal,
  FlatList,
  Alert,
  ActivityIndicator,
  Image,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import DateTimePicker, { DateTimePickerEvent } from '@react-native-community/datetimepicker';
import { auth_service } from '../../services/auth_service';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface PersonalInfoScreenProps {
  onBackPress: () => void;
  onNext: (personalInfo: PropertyOwnerPersonalInfo) => Promise<Record<string, string[]> | void>;
  formData: any; // Pre-loaded form data from Laravel backend
  initialData?: PropertyOwnerPersonalInfo | null;
}

export interface PropertyOwnerPersonalInfo {
  first_name: string;
  middle_name?: string;
  last_name: string;
  occupation_id: string;
  occupation_other?: string;
  date_of_birth: string;
  // Address fields
  owner_address_street: string;
  owner_address_province: string;
  owner_address_city: string;
  owner_address_barangay: string;
  owner_address_postal: string;
}

export default function PersonalInfoScreen({ onBackPress, onNext, formData, initialData }: PersonalInfoScreenProps) {
  // Personal Information fields (matching Laravel backend)
  const [firstName, setFirstName] = useState(initialData?.first_name || '');
  const [middleName, setMiddleName] = useState(initialData?.middle_name || '');
  const [lastName, setLastName] = useState(initialData?.last_name || '');
  const [occupationId, setOccupationId] = useState(initialData?.occupation_id || '');
  const [occupationOther, setOccupationOther] = useState(initialData?.occupation_other || '');
  const [dateOfBirth, setDateOfBirth] = useState(initialData?.date_of_birth || '');

  // Address fields (matching Laravel backend)
  const [addressStreet, setAddressStreet] = useState(initialData?.owner_address_street || '');
  const [addressProvince, setAddressProvince] = useState(initialData?.owner_address_province || '');
  const [addressCity, setAddressCity] = useState(initialData?.owner_address_city || '');
  const [addressBarangay, setAddressBarangay] = useState(initialData?.owner_address_barangay || '');
  const [addressPostal, setAddressPostal] = useState(initialData?.owner_address_postal || '');

  useEffect(() => {
    if (!initialData || Object.keys(initialData).length === 0) {
      AsyncStorage.getItem('signup_po_personalInfo').then(cached => {
        if (cached) {
          try {
            const parsed = JSON.parse(cached);
            if (parsed.firstName) setFirstName(parsed.firstName);
            if (parsed.middleName) setMiddleName(parsed.middleName);
            if (parsed.lastName) setLastName(parsed.lastName);
            if (parsed.occupationId) setOccupationId(parsed.occupationId);
            if (parsed.occupationOther) setOccupationOther(parsed.occupationOther);
            if (parsed.dateOfBirth) { setDateOfBirth(parsed.dateOfBirth); setSelectedDate(new Date(parsed.dateOfBirth)); }
            if (parsed.addressStreet) setAddressStreet(parsed.addressStreet);
            if (parsed.addressProvince) setAddressProvince(parsed.addressProvince);
            if (parsed.addressCity) setAddressCity(parsed.addressCity);
            if (parsed.addressBarangay) setAddressBarangay(parsed.addressBarangay);
            if (parsed.addressPostal) setAddressPostal(parsed.addressPostal);
          } catch (e) {}
        }
      });
    }
  }, [initialData]);

  useEffect(() => {
    const cache = {
      firstName, middleName, lastName, occupationId, occupationOther, dateOfBirth,
      addressStreet, addressProvince, addressCity, addressBarangay, addressPostal
    };
    const timer = setTimeout(() => {
      AsyncStorage.setItem('signup_po_personalInfo', JSON.stringify(cache)).catch(() => {});
    }, 500);
    return () => clearTimeout(timer);
  }, [firstName, middleName, lastName, occupationId, occupationOther, dateOfBirth, addressStreet, addressProvince, addressCity, addressBarangay, addressPostal]);

  // UI states
  const [isLoading, setIsLoading] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [showOccupationModal, setShowOccupationModal] = useState(false);
  const [showProvinceModal, setShowProvinceModal] = useState(false);
  const [showCityModal, setShowCityModal] = useState(false);
  const [showBarangayModal, setShowBarangayModal] = useState(false);
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [selectedDate, setSelectedDate] = useState<Date | undefined>(
    initialData?.date_of_birth ? new Date(initialData.date_of_birth) : undefined
  );

  // Data arrays from backend
  const [occupations, setOccupations] = useState(formData?.occupations || []);
  const [provinces, setProvinces] = useState(formData?.provinces || []);

  // Helper: check if the currently selected occupation is "Others"
  const isOthersOccupation = () => {
    const selected = occupations.find((o: any) => o.id.toString() === occupationId);
    const name = selected?.occupation_name?.toLowerCase() || '';
    return name === 'others' || name === 'other';
  };
  const [cities, setCities] = useState([]);
  const [barangays, setBarangays] = useState([]);

  // Load provinces on component mount if not in formData
  useEffect(() => {
    const loadProvinces = async () => {
      if (!provinces.length) {
        try {
          console.log('Loading provinces from API...');
          const response = await auth_service.get_provinces();
          console.log('Provinces response:', response);
          if (response.success && response.data) {
            setProvinces(response.data);
          } else {
            console.error('Failed to load provinces:', response.message);
            Alert.alert('Error', 'Failed to load provinces. Please try again.');
          }
        } catch (error) {
          console.error('Failed to load provinces:', error);
          Alert.alert('Error', 'Unable to connect to server. Please check your internet connection.');
        }
      }
    };

    const loadOccupations = async () => {
      if (!occupations.length) {
        try {
          console.log('Loading occupations from API...');
          const response = await auth_service.get_signup_form_data();
          console.log('Signup form data response:', response);

          if (response.success) {
            // Handle the nested data structure: response.data.data.occupations
            let occupationsData = null;

            if (response.data?.data?.occupations) {
              occupationsData = response.data.data.occupations;
            }

            if (occupationsData && Array.isArray(occupationsData)) {
              console.log('Setting occupations:', occupationsData);
              setOccupations(occupationsData);
            } else {
              console.error('Occupations data not found in expected format:', response);
              Alert.alert('Error', 'Occupation data format error. Please try again.');
            }
          } else {
            console.error('Failed to load occupations - API returned success: false', response);
            Alert.alert('Error', response.message || 'Failed to load occupations. Please try again.');
          }
        } catch (error) {
          console.error('Failed to load occupations:', error);
          Alert.alert('Error', 'Unable to connect to server. Please check your internet connection.');
        }
      }
    };

    loadProvinces();
    loadOccupations();
  }, []);

  // Date handling - using text input instead of picker

  // Load cities when province changes
  useEffect(() => {
    if (addressProvince) {
      loadCities(addressProvince);
    } else {
      setCities([]);
      setAddressCity('');
      setBarangays([]);
      setAddressBarangay('');
    }
  }, [addressProvince]);

  // Load barangays when city changes
  useEffect(() => {
    if (addressCity) {
      loadBarangays(addressCity);
    } else {
      setBarangays([]);
      setAddressBarangay('');
    }
  }, [addressCity]);

  // Load cities from backend
  const loadCities = async (provinceCode: string) => {
    try {
      console.log('Loading cities for province:', provinceCode);
      const response = await auth_service.get_cities_by_province(provinceCode);
      console.log('Cities response:', response);
      if (response.success && response.data) {
        setCities(response.data);
      } else {
        console.error('Failed to load cities:', response.message);
        setCities([]);
        Alert.alert('Error', 'Failed to load cities. Please try again.');
      }
    } catch (error) {
      console.error('Failed to load cities:', error);
      setCities([]);
      Alert.alert('Error', 'Unable to connect to server. Please check your internet connection.');
    }
  };

  // Load barangays from backend
  const loadBarangays = async (cityCode: string) => {
    try {
      console.log('Loading barangays for city:', cityCode);
      const response = await auth_service.get_barangays_by_city(cityCode);
      console.log('Barangays response:', response);
      if (response.success && response.data) {
        setBarangays(response.data);
      } else {
        console.error('Failed to load barangays:', response.message);
        setBarangays([]);
        Alert.alert('Error', 'Failed to load barangays. Please try again.');
      }
    } catch (error) {
      console.error('Failed to load barangays:', error);
      setBarangays([]);
      Alert.alert('Error', 'Unable to connect to server. Please check your internet connection.');
    }
  };

  // Date picker handler
  const handleDateChange = (event: DateTimePickerEvent, date?: Date) => {
    setShowDatePicker(false);
    if (event.type === 'set' && date) {
      setSelectedDate(date);
      // Format as YYYY-MM-DD for backend
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      setDateOfBirth(`${year}-${month}-${day}`);
    }
  };

  // Format date for display
  const formatDateForDisplay = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  // Calculate max date (must be 18 years old)
  const getMaxDate = () => {
    const today = new Date();
    return new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
  };

  // Calculate min date (reasonable age limit)
  const getMinDate = () => {
    return new Date(1930, 0, 1);
  };

  const isFormValid = () => {
    // Age validation is now handled by the date picker's maximumDate constraint
    return firstName.trim() !== '' &&
      lastName.trim() !== '' &&
      occupationId.trim() !== '' &&
      (!isOthersOccupation() || occupationOther.trim() !== '') &&
      dateOfBirth.trim() !== '' &&
      addressStreet.trim() !== '' &&
      addressProvince.trim() !== '' &&
      addressCity.trim() !== '' &&
      addressBarangay.trim() !== '' &&
      addressPostal.trim() !== '';
  };

  const handleNext = async () => {
    if (isLoading) return;

    // Clear previous errors
    setFieldErrors({});

    // Local validation — build inline errors
    const errors: Record<string, string[]> = {};
    if (!firstName.trim()) errors.first_name = ['First name is required.'];
    if (!lastName.trim()) errors.last_name = ['Last name is required.'];
    if (!occupationId.trim()) errors.occupation_id = ['Please select an occupation.'];
    if (isOthersOccupation() && !occupationOther.trim()) errors.occupation_other_text = ['Please specify your occupation.'];
    if (!dateOfBirth.trim()) errors.date_of_birth = ['Date of birth is required.'];
    if (!addressStreet.trim()) errors.owner_address_street = ['Street address is required.'];
    if (!addressProvince.trim()) errors.owner_address_province = ['Please select a province.'];
    if (!addressCity.trim()) errors.owner_address_city = ['Please select a city.'];
    if (!addressBarangay.trim()) errors.owner_address_barangay = ['Please select a barangay.'];
    if (!addressPostal.trim()) errors.owner_address_postal = ['Postal code is required.'];

    if (Object.keys(errors).length > 0) {
      setFieldErrors(errors);
      return;
    }

    setIsLoading(true);

    const personalInfo: PropertyOwnerPersonalInfo = {
      first_name: firstName.trim(),
      middle_name: middleName.trim() || undefined,
      last_name: lastName.trim(),
      occupation_id: occupationId,
      occupation_other: isOthersOccupation() ? occupationOther.trim() : undefined,
      date_of_birth: dateOfBirth,
      // Address fields
      owner_address_street: addressStreet.trim(),
      owner_address_province: addressProvince,
      owner_address_city: addressCity,
      owner_address_barangay: addressBarangay,
      owner_address_postal: addressPostal.trim(),
    };

    try {
      const serverErrors = await onNext(personalInfo);
      if (serverErrors) {
        setFieldErrors(serverErrors);
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <KeyboardAvoidingView
        style={{ flex: 1 }}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 100 : 80}
      >
        <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false} keyboardShouldPersistTaps="handled">
        <View style={styles.logoContainer}>
          <Image
            source={require('../../../assets/images/logos/legatura-logo.png')}
            style={styles.logo}
            resizeMode="contain"
          />
        </View>

        <View style={styles.progressContainer}>
          <View style={styles.progressStep}>
            <View style={[styles.progressBar, styles.progressBarActive]} />
            <Text style={[styles.progressText, styles.progressTextActive]}>Personal Information</Text>
          </View>
          <View style={styles.progressStep}>
            <View style={styles.progressBar} />
            <Text style={styles.progressText}>Account Setup</Text>
          </View>
          <View style={styles.progressStep}>
            <View style={styles.progressBar} />
            <Text style={styles.progressText}>Verification</Text>
          </View>
        </View>

        <View style={styles.formContainer}>
          {/* Personal Information Section */}
          <Text style={styles.sectionTitle}>Personal Information</Text>

          <View style={styles.inputContainer}>
            <TextInput
              style={[styles.input, fieldErrors.first_name && styles.inputError]}
              value={firstName}
              onChangeText={(text) => { setFirstName(text); setFieldErrors(prev => { const { first_name, ...rest } = prev; return rest; }); }}
              placeholder="First Name *"
              placeholderTextColor="#999"
              maxLength={100}
            />
            {fieldErrors.first_name && <Text style={styles.fieldErrorText}>{fieldErrors.first_name[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              style={[styles.input, fieldErrors.middle_name && styles.inputError]}
              value={middleName}
              onChangeText={(text) => { setMiddleName(text); setFieldErrors(prev => { const { middle_name, ...rest } = prev; return rest; }); }}
              placeholder="Middle Name (Optional)"
              placeholderTextColor="#999"
              maxLength={100}
            />
            {fieldErrors.middle_name && <Text style={styles.fieldErrorText}>{fieldErrors.middle_name[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              style={[styles.input, fieldErrors.last_name && styles.inputError]}
              value={lastName}
              onChangeText={(text) => { setLastName(text); setFieldErrors(prev => { const { last_name, ...rest } = prev; return rest; }); }}
              placeholder="Last Name *"
              placeholderTextColor="#999"
              maxLength={100}
            />
            {fieldErrors.last_name && <Text style={styles.fieldErrorText}>{fieldErrors.last_name[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <TouchableOpacity style={[styles.dropdownContainer, fieldErrors.occupation_id && styles.dropdownError]} onPress={() => { setShowOccupationModal(true); setFieldErrors(prev => { const { occupation_id, ...rest } = prev; return rest; }); }}>
              <View style={styles.dropdownInputWrapper}>
                <Text style={[styles.dropdownInputText, !occupationId && styles.placeholderText]}>
                  {occupations.find(o => o.id.toString() === occupationId)?.occupation_name || 'Occupation *'}
                </Text>
                <MaterialIcons name="keyboard-arrow-down" size={24} color="#666666" style={styles.dropdownIcon} />
              </View>
            </TouchableOpacity>
            {fieldErrors.occupation_id && <Text style={styles.fieldErrorText}>{fieldErrors.occupation_id[0]}</Text>}
          </View>

          {isOthersOccupation() && (
            <View style={styles.inputContainer}>
              <TextInput
                style={[styles.input, fieldErrors.occupation_other_text && styles.inputError]}
                value={occupationOther}
                onChangeText={(text) => { setOccupationOther(text); setFieldErrors(prev => { const { occupation_other_text, ...rest } = prev; return rest; }); }}
                placeholder="Specify Occupation *"
                placeholderTextColor="#999"
              />
              {fieldErrors.occupation_other_text && <Text style={styles.fieldErrorText}>{fieldErrors.occupation_other_text[0]}</Text>}
            </View>
          )}

          <View style={styles.inputContainer}>
            <TouchableOpacity
              style={[styles.dropdownContainer, fieldErrors.date_of_birth && styles.dropdownError]}
              onPress={() => { setShowDatePicker(true); setFieldErrors(prev => { const { date_of_birth, ...rest } = prev; return rest; }); }}
            >
              <View style={styles.dropdownInputWrapper}>
                <Text style={[styles.dropdownInputText, !dateOfBirth && styles.placeholderText]}>
                  {dateOfBirth ? formatDateForDisplay(dateOfBirth) : 'Date of Birth *'}
                </Text>
                <MaterialIcons name="calendar-today" size={24} color="#666666" style={styles.dropdownIcon} />
              </View>
            </TouchableOpacity>
            {fieldErrors.date_of_birth ? <Text style={styles.fieldErrorText}>{fieldErrors.date_of_birth[0]}</Text> : <Text style={styles.fieldHint}>Must be 18+ years old</Text>}

            {showDatePicker && (
              <DateTimePicker
                value={selectedDate || getMaxDate()}
                mode="date"
                display="spinner"
                onChange={handleDateChange}
                maximumDate={getMaxDate()}
                minimumDate={getMinDate()}
              />
            )}
          </View>

          {/* Address Section */}
          <Text style={styles.sectionTitle}>Address</Text>

          <View style={styles.inputContainer}>
            <TouchableOpacity style={[styles.dropdownContainer, fieldErrors.owner_address_province && styles.dropdownError]} onPress={() => { setShowProvinceModal(true); setFieldErrors(prev => { const { owner_address_province, ...rest } = prev; return rest; }); }}>
              <View style={styles.dropdownInputWrapper}>
                <Text style={[styles.dropdownInputText, !addressProvince && styles.placeholderText]}>
                  {provinces.find(p => p.code === addressProvince)?.name || 'Province *'}
                </Text>
                <MaterialIcons name="keyboard-arrow-down" size={24} color="#666666" style={styles.dropdownIcon} />
              </View>
            </TouchableOpacity>
            {fieldErrors.owner_address_province && <Text style={styles.fieldErrorText}>{fieldErrors.owner_address_province[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <TouchableOpacity
              style={[styles.dropdownContainer, !addressProvince && styles.dropdownDisabled, fieldErrors.owner_address_city && styles.dropdownError]}
              onPress={() => { if (addressProvince) { setShowCityModal(true); setFieldErrors(prev => { const { owner_address_city, ...rest } = prev; return rest; }); } }}
              disabled={!addressProvince}
            >
              <View style={styles.dropdownInputWrapper}>
                <Text style={[styles.dropdownInputText, !addressCity && styles.placeholderText]}>
                  {cities.find(c => c.code === addressCity)?.name || (addressProvince ? 'City/Municipality *' : 'Select Province First')}
                </Text>
                <MaterialIcons name="keyboard-arrow-down" size={24} color="#666666" style={styles.dropdownIcon} />
              </View>
            </TouchableOpacity>
            {fieldErrors.owner_address_city && <Text style={styles.fieldErrorText}>{fieldErrors.owner_address_city[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <TouchableOpacity
              style={[styles.dropdownContainer, !addressCity && styles.dropdownDisabled, fieldErrors.owner_address_barangay && styles.dropdownError]}
              onPress={() => { if (addressCity) { setShowBarangayModal(true); setFieldErrors(prev => { const { owner_address_barangay, ...rest } = prev; return rest; }); } }}
              disabled={!addressCity}
            >
              <View style={styles.dropdownInputWrapper}>
                <Text style={[styles.dropdownInputText, !addressBarangay && styles.placeholderText]}>
                  {barangays.find(b => b.code === addressBarangay)?.name || (addressCity ? 'Barangay *' : 'Select City First')}
                </Text>
                <MaterialIcons name="keyboard-arrow-down" size={24} color="#666666" style={styles.dropdownIcon} />
              </View>
            </TouchableOpacity>
            {fieldErrors.owner_address_barangay && <Text style={styles.fieldErrorText}>{fieldErrors.owner_address_barangay[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              style={[styles.input, fieldErrors.owner_address_street && styles.inputError]}
              value={addressStreet}
              onChangeText={(text) => { setAddressStreet(text); setFieldErrors(prev => { const { owner_address_street, ...rest } = prev; return rest; }); }}
              placeholder="Street/Building No. * (e.g., 456 Oak Avenue)"
              placeholderTextColor="#999"
            />
            {fieldErrors.owner_address_street && <Text style={styles.fieldErrorText}>{fieldErrors.owner_address_street[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              style={[styles.input, fieldErrors.owner_address_postal && styles.inputError]}
              value={addressPostal}
              onChangeText={(text) => { setAddressPostal(text); setFieldErrors(prev => { const { owner_address_postal, ...rest } = prev; return rest; }); }}
              placeholder="Postal Code * (e.g., 1000)"
              placeholderTextColor="#999"
              keyboardType="numeric"
            />
            {fieldErrors.owner_address_postal && <Text style={styles.fieldErrorText}>{fieldErrors.owner_address_postal[0]}</Text>}
          </View>
        </View>

        <View style={styles.buttonContainer}>
          <TouchableOpacity
            style={styles.backButton}
            onPress={onBackPress}
          >
            <Text style={styles.backButtonText}>Back</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[
              styles.nextButton,
              isLoading && styles.nextButtonDisabled
            ]}
            onPress={handleNext}
            disabled={isLoading}
          >
            {isLoading ? (
              <ActivityIndicator color="#FFFFFF" size="small" />
            ) : (
              <Text style={styles.nextButtonText}>Next</Text>
            )}
          </TouchableOpacity>
        </View>
      </ScrollView>

      {/* Occupation Selector Modal */}
      <Modal
        visible={showOccupationModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowOccupationModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Occupation</Text>
              <TouchableOpacity
                onPress={() => setShowOccupationModal(false)}
                style={styles.closeButton}
              >
                <MaterialIcons name="close" size={24} color="#333333" />
              </TouchableOpacity>
            </View>

            <FlatList
              data={[...occupations].sort((a, b) => {
                const aIsOther = a.occupation_name.toLowerCase() === 'other' || a.occupation_name.toLowerCase() === 'others';
                const bIsOther = b.occupation_name.toLowerCase() === 'other' || b.occupation_name.toLowerCase() === 'others';
                if (aIsOther) return 1;
                if (bIsOther) return -1;
                return a.occupation_name.localeCompare(b.occupation_name);
              })}
              keyExtractor={(item) => item.id.toString()}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    setOccupationId(item.id.toString());
                    setShowOccupationModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.occupation_name}</Text>
                </TouchableOpacity>
              )}
              showsVerticalScrollIndicator={false}
            />
          </View>
        </View>
      </Modal>

      {/* Province Selector Modal */}
      <Modal
        visible={showProvinceModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowProvinceModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Province</Text>
              <TouchableOpacity
                onPress={() => setShowProvinceModal(false)}
                style={styles.closeButton}
              >
                <MaterialIcons name="close" size={24} color="#333333" />
              </TouchableOpacity>
            </View>

            <FlatList
              data={provinces}
              keyExtractor={(item) => item.code}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    setAddressProvince(item.code);
                    setAddressCity(''); // Reset city when province changes
                    setAddressBarangay(''); // Reset barangay when province changes
                    setShowProvinceModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
              showsVerticalScrollIndicator={false}
            />
          </View>
        </View>
      </Modal>

      {/* City Selector Modal */}
      <Modal
        visible={showCityModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowCityModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select City/Municipality</Text>
              <TouchableOpacity
                onPress={() => setShowCityModal(false)}
                style={styles.closeButton}
              >
                <MaterialIcons name="close" size={24} color="#333333" />
              </TouchableOpacity>
            </View>

            <FlatList
              data={cities}
              keyExtractor={(item) => item.code}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    setAddressCity(item.code);
                    setAddressBarangay(''); // Reset barangay when city changes
                    setShowCityModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
              showsVerticalScrollIndicator={false}
            />
          </View>
        </View>
      </Modal>

      {/* Barangay Selector Modal */}
      <Modal
        visible={showBarangayModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowBarangayModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Barangay</Text>
              <TouchableOpacity
                onPress={() => setShowBarangayModal(false)}
                style={styles.closeButton}
              >
                <MaterialIcons name="close" size={24} color="#333333" />
              </TouchableOpacity>
            </View>

            <FlatList
              data={barangays}
              keyExtractor={(item) => item.code}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    setAddressBarangay(item.code);
                    setShowBarangayModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                </TouchableOpacity>
              )}
              showsVerticalScrollIndicator={false}
            />
          </View>
        </View>
      </Modal>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FEFEFE',
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: 30,
    paddingBottom: 40,
  },
  logoContainer: {
    alignItems: 'center',
    marginTop: 40,
    marginBottom: 30,
  },
  logo: {
    width: 200,
    height: 120,
  },
  progressContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 40,
    paddingHorizontal: 10,
  },
  progressStep: {
    flex: 1,
    alignItems: 'center',
  },
  progressBar: {
    height: 4,
    backgroundColor: '#E5E5E5',
    borderRadius: 2,
    width: '100%',
    marginBottom: 8,
  },
  progressBarActive: {
    backgroundColor: '#EC7E00',
  },
  progressText: {
    fontSize: 12,
    color: '#999999',
    textAlign: 'center',
  },
  progressTextActive: {
    color: '#333333',
    fontWeight: '600',
  },
  formContainer: {
    flex: 1,
    gap: 20,
  },
  inputContainer: {
    marginBottom: 4,
  },
  input: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E5E5',
    borderRadius: 12,
    paddingHorizontal: 16,
    paddingVertical: 16,
    fontSize: 16,
    color: '#333333',
  },
  dropdownContainer: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#E5E5E5',
    borderRadius: 12,
  },
  dropdownInputWrapper: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 16,
    justifyContent: 'space-between',
  },
  dropdownInputText: {
    fontSize: 16,
    color: '#333333',
    flex: 1,
  },
  dropdownIcon: {
    marginLeft: 10,
  },
  placeholderText: {
    color: '#999999',
  },
  nextButton: {
    backgroundColor: '#EC7E00',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
    flex: 1,
    marginLeft: 8,
  },
  nextButtonText: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: '600',
  },
  nextButtonDisabled: {
    backgroundColor: '#CCCCCC',
    shadowOpacity: 0,
    elevation: 0,
  },
  nextButtonTextDisabled: {
    color: '#999999',
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333333',
    marginBottom: 20,
    marginTop: 10,
  },
  fieldHint: {
    fontSize: 12,
    color: '#666666',
    marginTop: 5,
    marginLeft: 4,
  },
  fieldErrorText: {
    color: '#DC3545',
    fontSize: 12,
    marginTop: 4,
    marginLeft: 4,
  },
  inputError: {
    borderColor: '#DC3545',
  },
  dropdownError: {
    borderColor: '#DC3545',
  },
  buttonContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 30,
    paddingHorizontal: 5,
    paddingBottom: 20,
    gap: 16,
  },
  backButton: {
    backgroundColor: '#E8E8E8',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
    flex: 1,
    marginRight: 8,
  },
  backButtonText: {
    fontSize: 18,
    color: '#333333',
    fontWeight: '600',
  },
  dropdownDisabled: {
    backgroundColor: '#F5F5F5',
    borderColor: '#DDDDDD',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContainer: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    width: '90%',
    maxHeight: '80%',
    paddingVertical: 20,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    marginBottom: 15,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333333',
  },
  closeButton: {
    padding: 4,
  },
  modalItem: {
    paddingVertical: 15,
    paddingHorizontal: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  modalItemText: {
    fontSize: 16,
    color: '#333333',
  },
});
