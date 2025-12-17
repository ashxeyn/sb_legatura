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
  Platform,
} from 'react-native';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import DateTimePicker from '@react-native-community/datetimepicker';
import * as ImagePicker from 'expo-image-picker';

interface CreateProjectScreenProps {
  onBackPress: () => void;
  onSubmit: (projectData: ProjectFormData) => void;
  contractorTypes?: ContractorType[];
}

interface ContractorType {
  type_id: number;
  type_name: string;
}

interface ProjectFormData {
  project_title: string;
  project_description: string;
  barangay: string;
  street_address: string;
  project_location: string;
  budget_range_min: string;
  budget_range_max: string;
  lot_size: string;
  floor_area: string;
  property_type: string;
  type_id: string;
  if_others_ctype?: string;
  bidding_deadline: string;
  building_permit?: any;
  title_of_land?: any;
  blueprint?: any[];
  desired_design?: any[];
  others?: any[];
}

const PROPERTY_TYPES = [
  { id: 'Residential', name: 'Residential' },
  { id: 'Commercial', name: 'Commercial' },
  { id: 'Industrial', name: 'Industrial' },
  { id: 'Agricultural', name: 'Agricultural' },
];

export default function CreateProjectScreen({ onBackPress, onSubmit, contractorTypes = [] }: CreateProjectScreenProps) {
  // Form state
  const [projectTitle, setProjectTitle] = useState('');
  const [projectDescription, setProjectDescription] = useState('');
  const [barangay, setBarangay] = useState('');
  const [streetAddress, setStreetAddress] = useState('');
  const [budgetMin, setBudgetMin] = useState('');
  const [budgetMax, setBudgetMax] = useState('');
  const [lotSize, setLotSize] = useState('');
  const [floorArea, setFloorArea] = useState('');
  const [propertyType, setPropertyType] = useState('');
  const [contractorTypeId, setContractorTypeId] = useState('');
  const [otherContractorType, setOtherContractorType] = useState('');
  const [biddingDeadline, setBiddingDeadline] = useState<Date | null>(null);

  // File uploads
  const [buildingPermit, setBuildingPermit] = useState<any>(null);
  const [titleOfLand, setTitleOfLand] = useState<any>(null);
  const [blueprints, setBlueprints] = useState<any[]>([]);
  const [desiredDesigns, setDesiredDesigns] = useState<any[]>([]);
  const [otherFiles, setOtherFiles] = useState<any[]>([]);

  // UI state
  const [isLoading, setIsLoading] = useState(false);
  const [showPropertyTypeModal, setShowPropertyTypeModal] = useState(false);
  const [showContractorTypeModal, setShowContractorTypeModal] = useState(false);
  const [showBarangayModal, setShowBarangayModal] = useState(false);
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [barangays, setBarangays] = useState<any[]>([]);
  const [loadingBarangays, setLoadingBarangays] = useState(true);

  // Ensure contractorTypes is always an array
  const safeContractorTypes = Array.isArray(contractorTypes) ? contractorTypes : [];

  // Check if selected contractor type is "Others"
  const isOthersSelected = () => {
    const selectedType = safeContractorTypes.find(t => t.type_id?.toString() === contractorTypeId);
    return selectedType?.type_name?.toLowerCase().trim() === 'others';
  };

  // Load barangays for Zamboanga City
  useEffect(() => {
    loadBarangays();
  }, []);

  const loadBarangays = async () => {
    try {
      setLoadingBarangays(true);
      // Zamboanga City code: 097332000
      const response = await fetch('https://psgc.gitlab.io/api/cities-municipalities/097332000/barangays/');
      if (response.ok) {
        const data = await response.json();
        const sortedBarangays = data
          .map((b: any) => ({ code: b.code, name: b.name }))
          .sort((a: any, b: any) => a.name.localeCompare(b.name));
        setBarangays(sortedBarangays);
      }
    } catch (error) {
      console.error('Failed to load barangays:', error);
    } finally {
      setLoadingBarangays(false);
    }
  };

  // Date picker handler
  const onDateChange = (event: any, selectedDate?: Date) => {
    setShowDatePicker(Platform.OS === 'ios');
    if (selectedDate) {
      setBiddingDeadline(selectedDate);
    }
  };

  // Format number with commas (e.g., 1000000 -> 1,000,000)
  const formatNumberWithCommas = (value: string): string => {
    // Remove all non-digit characters
    const numericValue = value.replace(/[^0-9]/g, '');
    if (!numericValue) return '';
    // Add commas
    return numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  };

  // Remove commas to get raw number (for submission)
  const removeCommas = (value: string): string => {
    return value.replace(/,/g, '');
  };

  // Handle budget input change
  const handleBudgetMinChange = (value: string) => {
    setBudgetMin(formatNumberWithCommas(value));
  };

  const handleBudgetMaxChange = (value: string) => {
    setBudgetMax(formatNumberWithCommas(value));
  };

  // Format date for display
  const formatDate = (date: Date | null) => {
    if (!date) return '';
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  // Image picker for required photos
  const pickImage = async (setter: (value: any) => void) => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission Required', 'Please grant camera roll permissions to upload images.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: false,
      quality: 0.8,
    });

    if (!result.canceled && result.assets[0]) {
      setter(result.assets[0]);
    }
  };

  // Image picker for optional files (multiple)
  const pickOptionalImage = async (currentFiles: any[], setter: (files: any[]) => void) => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission Required', 'Please grant camera roll permissions to upload images.');
      return;
    }

    if (currentFiles.length >= 10) {
      Alert.alert('Limit Reached', 'You can only upload up to 10 images.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: false,
      quality: 0.8,
    });

    if (!result.canceled && result.assets[0]) {
      setter([...currentFiles, result.assets[0]]);
    }
  };

  // Remove file from array
  const removeFile = (index: number, files: any[], setter: (files: any[]) => void) => {
    const newFiles = [...files];
    newFiles.splice(index, 1);
    setter(newFiles);
  };

  // Validate form
  const validateForm = () => {
    if (!projectTitle.trim()) {
      Alert.alert('Required', 'Please enter a project title.');
      return false;
    }
    if (!projectDescription.trim()) {
      Alert.alert('Required', 'Please enter a project description.');
      return false;
    }
    if (!barangay) {
      Alert.alert('Required', 'Please select a barangay.');
      return false;
    }
    if (!streetAddress.trim()) {
      Alert.alert('Required', 'Please enter the street address.');
      return false;
    }
    const minBudgetRaw = parseFloat(removeCommas(budgetMin));
    const maxBudgetRaw = parseFloat(removeCommas(budgetMax));
    if (!budgetMin || isNaN(minBudgetRaw) || minBudgetRaw < 0) {
      Alert.alert('Required', 'Please enter a valid minimum budget.');
      return false;
    }
    if (!budgetMax || isNaN(maxBudgetRaw) || maxBudgetRaw < minBudgetRaw) {
      Alert.alert('Required', 'Maximum budget must be greater than or equal to minimum budget.');
      return false;
    }
    if (!lotSize || parseInt(lotSize) < 1) {
      Alert.alert('Required', 'Please enter a valid lot size.');
      return false;
    }
    if (!floorArea || parseInt(floorArea) < 1) {
      Alert.alert('Required', 'Please enter a valid floor area.');
      return false;
    }
    if (!propertyType) {
      Alert.alert('Required', 'Please select a property type.');
      return false;
    }
    if (!contractorTypeId) {
      Alert.alert('Required', 'Please select a contractor type.');
      return false;
    }
    if (isOthersSelected() && !otherContractorType.trim()) {
      Alert.alert('Required', 'Please specify the contractor type.');
      return false;
    }
    if (!biddingDeadline) {
      Alert.alert('Required', 'Please select a bidding deadline.');
      return false;
    }
    if (biddingDeadline <= new Date()) {
      Alert.alert('Invalid Date', 'Bidding deadline must be in the future.');
      return false;
    }
    if (!buildingPermit) {
      Alert.alert('Required', 'Please upload the building permit.');
      return false;
    }
    if (!titleOfLand) {
      Alert.alert('Required', 'Please upload the title of the land.');
      return false;
    }
    return true;
  };

  // Handle form submission
  const handleSubmit = () => {
    if (!validateForm()) return;

    const barangayName = barangays.find(b => b.code === barangay)?.name || '';
    const projectLocation = `${streetAddress}, ${barangayName}, Zamboanga City, Zamboanga del Sur`;

    const projectData: ProjectFormData = {
      project_title: projectTitle.trim(),
      project_description: projectDescription.trim(),
      barangay,
      street_address: streetAddress.trim(),
      project_location: projectLocation,
      budget_range_min: removeCommas(budgetMin),
      budget_range_max: removeCommas(budgetMax),
      lot_size: lotSize,
      floor_area: floorArea,
      property_type: propertyType,
      type_id: contractorTypeId,
      bidding_deadline: biddingDeadline?.toISOString().split('T')[0] || '',
      building_permit: buildingPermit,
      title_of_land: titleOfLand,
      blueprint: blueprints,
      desired_design: desiredDesigns,
      others: otherFiles,
    };

    if (isOthersSelected()) {
      projectData.if_others_ctype = otherContractorType.trim();
    }

    onSubmit(projectData);
  };

  // Get selected names for display
  const getPropertyTypeName = () => PROPERTY_TYPES.find(p => p.id === propertyType)?.name || '';
  const getContractorTypeName = () => safeContractorTypes.find(t => t.type_id?.toString() === contractorTypeId)?.type_name || '';
  const getBarangayName = () => barangays.find(b => b.code === barangay)?.name || '';

  return (
    <SafeAreaView style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={onBackPress} style={styles.backButton}>
          <Ionicons name="arrow-back" size={24} color="#1A1A1A" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Create Project Post</Text>
        <View style={{ width: 40 }} />
      </View>

      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        <View style={styles.formContainer}>
          {/* Project Title */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Project Title <Text style={styles.required}>*</Text></Text>
            <TextInput
              style={styles.input}
              value={projectTitle}
              onChangeText={setProjectTitle}
              placeholder="Enter project title"
              placeholderTextColor="#999"
              maxLength={200}
            />
          </View>

          {/* Project Description */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Project Description <Text style={styles.required}>*</Text></Text>
            <TextInput
              style={[styles.input, styles.textArea]}
              value={projectDescription}
              onChangeText={setProjectDescription}
              placeholder="Describe your project in detail..."
              placeholderTextColor="#999"
              multiline
              numberOfLines={4}
              textAlignVertical="top"
            />
          </View>

          {/* Location Section */}
          <View style={styles.sectionHeader}>
            <Ionicons name="location" size={20} color="#EC7E00" />
            <Text style={styles.sectionTitle}>Project Location</Text>
          </View>

          {/* Barangay */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Barangay <Text style={styles.required}>*</Text></Text>
            <TouchableOpacity
              style={styles.dropdown}
              onPress={() => setShowBarangayModal(true)}
              disabled={loadingBarangays}
            >
              <Text style={[styles.dropdownText, !barangay && styles.placeholder]}>
                {loadingBarangays ? 'Loading...' : (getBarangayName() || 'Select Barangay')}
              </Text>
              <Ionicons name="chevron-down" size={20} color="#666" />
            </TouchableOpacity>
          </View>

          {/* Street Address */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Street / Barangay Details <Text style={styles.required}>*</Text></Text>
            <TextInput
              style={styles.input}
              value={streetAddress}
              onChangeText={setStreetAddress}
              placeholder="Street, Purok, House No. etc"
              placeholderTextColor="#999"
              maxLength={255}
            />
            <Text style={styles.helperText}>
              City and Province are fixed to Zamboanga City, Zamboanga del Sur.
            </Text>
          </View>

          {/* Budget Section */}
          <View style={styles.sectionHeader}>
            <Ionicons name="cash" size={20} color="#EC7E00" />
            <Text style={styles.sectionTitle}>Budget Range</Text>
          </View>

          <View style={styles.row}>
            <View style={[styles.inputGroup, { flex: 1, marginRight: 8 }]}>
              <Text style={styles.label}>Minimum (₱) <Text style={styles.required}>*</Text></Text>
              <TextInput
                style={styles.input}
                value={budgetMin}
                onChangeText={handleBudgetMinChange}
                placeholder="0"
                placeholderTextColor="#999"
                keyboardType="number-pad"
              />
            </View>
            <View style={[styles.inputGroup, { flex: 1, marginLeft: 8 }]}>
              <Text style={styles.label}>Maximum (₱) <Text style={styles.required}>*</Text></Text>
              <TextInput
                style={styles.input}
                value={budgetMax}
                onChangeText={handleBudgetMaxChange}
                placeholder="0"
                placeholderTextColor="#999"
                keyboardType="number-pad"
              />
            </View>
          </View>

          {/* Property Details Section */}
          <View style={styles.sectionHeader}>
            <Ionicons name="home" size={20} color="#EC7E00" />
            <Text style={styles.sectionTitle}>Property Details</Text>
          </View>

          <View style={styles.row}>
            <View style={[styles.inputGroup, { flex: 1, marginRight: 8 }]}>
              <Text style={styles.label}>Lot Size (sqm) <Text style={styles.required}>*</Text></Text>
              <TextInput
                style={styles.input}
                value={lotSize}
                onChangeText={setLotSize}
                placeholder="0"
                placeholderTextColor="#999"
                keyboardType="number-pad"
              />
            </View>
            <View style={[styles.inputGroup, { flex: 1, marginLeft: 8 }]}>
              <Text style={styles.label}>Floor Area (sqm) <Text style={styles.required}>*</Text></Text>
              <TextInput
                style={styles.input}
                value={floorArea}
                onChangeText={setFloorArea}
                placeholder="0"
                placeholderTextColor="#999"
                keyboardType="number-pad"
              />
            </View>
          </View>

          {/* Property Type */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Property Type <Text style={styles.required}>*</Text></Text>
            <TouchableOpacity
              style={styles.dropdown}
              onPress={() => setShowPropertyTypeModal(true)}
            >
              <Text style={[styles.dropdownText, !propertyType && styles.placeholder]}>
                {getPropertyTypeName() || 'Select Property Type'}
              </Text>
              <Ionicons name="chevron-down" size={20} color="#666" />
            </TouchableOpacity>
          </View>

          {/* Contractor Type */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Contractor Type Required <Text style={styles.required}>*</Text></Text>
            <TouchableOpacity
              style={styles.dropdown}
              onPress={() => setShowContractorTypeModal(true)}
            >
              <Text style={[styles.dropdownText, !contractorTypeId && styles.placeholder]}>
                {getContractorTypeName() || 'Select Contractor Type'}
              </Text>
              <Ionicons name="chevron-down" size={20} color="#666" />
            </TouchableOpacity>
          </View>

          {/* Other Contractor Type (conditional) */}
          {isOthersSelected() && (
            <View style={styles.inputGroup}>
              <Text style={styles.label}>Specify Contractor Type <Text style={styles.required}>*</Text></Text>
              <TextInput
                style={styles.input}
                value={otherContractorType}
                onChangeText={setOtherContractorType}
                placeholder="Specify contractor type"
                placeholderTextColor="#999"
                maxLength={200}
              />
            </View>
          )}

          {/* Bidding Deadline */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Bidding Deadline <Text style={styles.required}>*</Text></Text>
            <TouchableOpacity
              style={styles.dropdown}
              onPress={() => setShowDatePicker(true)}
            >
              <Text style={[styles.dropdownText, !biddingDeadline && styles.placeholder]}>
                {biddingDeadline ? formatDate(biddingDeadline) : 'Select Deadline Date'}
              </Text>
              <Ionicons name="calendar" size={20} color="#666" />
            </TouchableOpacity>
          </View>

          {showDatePicker && (
            <DateTimePicker
              value={biddingDeadline || new Date()}
              mode="date"
              display="default"
              minimumDate={new Date()}
              onChange={onDateChange}
            />
          )}

          {/* Documents Section */}
          <View style={styles.sectionHeader}>
            <Ionicons name="document-attach" size={20} color="#EC7E00" />
            <Text style={styles.sectionTitle}>Required Documents</Text>
          </View>

          {/* Building Permit */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Building Permit <Text style={styles.required}>*</Text></Text>
            <TouchableOpacity
              style={styles.uploadButton}
              onPress={() => pickImage(setBuildingPermit)}
            >
              {buildingPermit ? (
                <View style={styles.uploadedFile}>
                  <Image source={{ uri: buildingPermit.uri }} style={styles.thumbnailImage} />
                  <Text style={styles.fileName} numberOfLines={1}>{buildingPermit.fileName || 'Image selected'}</Text>
                  <TouchableOpacity onPress={() => setBuildingPermit(null)}>
                    <Ionicons name="close-circle" size={24} color="#E74C3C" />
                  </TouchableOpacity>
                </View>
              ) : (
                <View style={styles.uploadPlaceholder}>
                  <Ionicons name="cloud-upload" size={32} color="#EC7E00" />
                  <Text style={styles.uploadText}>Tap to upload image</Text>
                  <Text style={styles.uploadHint}>JPG, JPEG, PNG (Max 10MB)</Text>
                </View>
              )}
            </TouchableOpacity>
          </View>

          {/* Title of Land */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Title of the Land <Text style={styles.required}>*</Text></Text>
            <TouchableOpacity
              style={styles.uploadButton}
              onPress={() => pickImage(setTitleOfLand)}
            >
              {titleOfLand ? (
                <View style={styles.uploadedFile}>
                  <Image source={{ uri: titleOfLand.uri }} style={styles.thumbnailImage} />
                  <Text style={styles.fileName} numberOfLines={1}>{titleOfLand.fileName || 'Image selected'}</Text>
                  <TouchableOpacity onPress={() => setTitleOfLand(null)}>
                    <Ionicons name="close-circle" size={24} color="#E74C3C" />
                  </TouchableOpacity>
                </View>
              ) : (
                <View style={styles.uploadPlaceholder}>
                  <Ionicons name="cloud-upload" size={32} color="#EC7E00" />
                  <Text style={styles.uploadText}>Tap to upload image</Text>
                  <Text style={styles.uploadHint}>JPG, JPEG, PNG (Max 10MB)</Text>
                </View>
              )}
            </TouchableOpacity>
          </View>

          {/* Optional Documents Section */}
          <View style={styles.sectionHeader}>
            <Ionicons name="folder-open" size={20} color="#666" />
            <Text style={styles.sectionTitle}>Optional Documents</Text>
          </View>

          {/* Blueprint */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Blueprint Images</Text>
            <TouchableOpacity
              style={styles.uploadButtonSmall}
              onPress={() => pickOptionalImage(blueprints, setBlueprints)}
            >
              <Ionicons name="add-circle" size={24} color="#EC7E00" />
              <Text style={styles.addFileText}>Add Blueprint Images</Text>
            </TouchableOpacity>
            {blueprints.map((file, index) => (
              <View key={index} style={styles.fileItem}>
                <Image source={{ uri: file.uri }} style={styles.thumbnailSmall} />
                <Text style={styles.fileItemName} numberOfLines={1}>{file.fileName || 'Image'}</Text>
                <TouchableOpacity onPress={() => removeFile(index, blueprints, setBlueprints)}>
                  <Ionicons name="close-circle" size={22} color="#E74C3C" />
                </TouchableOpacity>
              </View>
            ))}
            <Text style={styles.uploadHint}>JPG, JPEG, PNG (Max 10 images)</Text>
          </View>

          {/* Desired Design */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Desired Design Images</Text>
            <TouchableOpacity
              style={styles.uploadButtonSmall}
              onPress={() => pickOptionalImage(desiredDesigns, setDesiredDesigns)}
            >
              <Ionicons name="add-circle" size={24} color="#EC7E00" />
              <Text style={styles.addFileText}>Add Design Images</Text>
            </TouchableOpacity>
            {desiredDesigns.map((file, index) => (
              <View key={index} style={styles.fileItem}>
                <Image source={{ uri: file.uri }} style={styles.thumbnailSmall} />
                <Text style={styles.fileItemName} numberOfLines={1}>{file.fileName || 'Image'}</Text>
                <TouchableOpacity onPress={() => removeFile(index, desiredDesigns, setDesiredDesigns)}>
                  <Ionicons name="close-circle" size={22} color="#E74C3C" />
                </TouchableOpacity>
              </View>
            ))}
            <Text style={styles.uploadHint}>JPG, JPEG, PNG (Max 10 images)</Text>
          </View>

          {/* Others */}
          <View style={styles.inputGroup}>
            <Text style={styles.label}>Other Images</Text>
            <TouchableOpacity
              style={styles.uploadButtonSmall}
              onPress={() => pickOptionalImage(otherFiles, setOtherFiles)}
            >
              <Ionicons name="add-circle" size={24} color="#EC7E00" />
              <Text style={styles.addFileText}>Add Other Images</Text>
            </TouchableOpacity>
            {otherFiles.map((file, index) => (
              <View key={index} style={styles.fileItem}>
                <Image source={{ uri: file.uri }} style={styles.thumbnailSmall} />
                <Text style={styles.fileItemName} numberOfLines={1}>{file.fileName || 'Image'}</Text>
                <TouchableOpacity onPress={() => removeFile(index, otherFiles, setOtherFiles)}>
                  <Ionicons name="close-circle" size={22} color="#E74C3C" />
                </TouchableOpacity>
              </View>
            ))}
            <Text style={styles.uploadHint}>JPG, JPEG, PNG (Max 10 images)</Text>
          </View>

          {/* Submit Button */}
          <TouchableOpacity
            style={[styles.submitButton, isLoading && styles.submitButtonDisabled]}
            onPress={handleSubmit}
            disabled={isLoading}
          >
            {isLoading ? (
              <ActivityIndicator color="#FFFFFF" />
            ) : (
              <>
                <Ionicons name="paper-plane" size={20} color="#FFFFFF" />
                <Text style={styles.submitButtonText}>Post Project</Text>
              </>
            )}
          </TouchableOpacity>
        </View>
      </ScrollView>

      {/* Barangay Modal */}
      <Modal visible={showBarangayModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Barangay</Text>
              <TouchableOpacity onPress={() => setShowBarangayModal(false)}>
                <Ionicons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={barangays}
              keyExtractor={(item, index) => `${item.code}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    setBarangay(item.code);
                    setShowBarangayModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                  {barangay === item.code && (
                    <Ionicons name="checkmark" size={20} color="#EC7E00" />
                  )}
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* Property Type Modal */}
      <Modal visible={showPropertyTypeModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Property Type</Text>
              <TouchableOpacity onPress={() => setShowPropertyTypeModal(false)}>
                <Ionicons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={PROPERTY_TYPES}
              keyExtractor={(item) => item.id}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    setPropertyType(item.id);
                    setShowPropertyTypeModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.name}</Text>
                  {propertyType === item.id && (
                    <Ionicons name="checkmark" size={20} color="#EC7E00" />
                  )}
                </TouchableOpacity>
              )}
            />
          </View>
        </View>
      </Modal>

      {/* Contractor Type Modal */}
      <Modal visible={showContractorTypeModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Select Contractor Type</Text>
              <TouchableOpacity onPress={() => setShowContractorTypeModal(false)}>
                <Ionicons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>
            <FlatList
              data={safeContractorTypes}
              keyExtractor={(item, index) => `${item.type_id}-${index}`}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={styles.modalItem}
                  onPress={() => {
                    setContractorTypeId(item.type_id?.toString() || '');
                    setShowContractorTypeModal(false);
                  }}
                >
                  <Text style={styles.modalItemText}>{item.type_name}</Text>
                  {contractorTypeId === item.type_id?.toString() && (
                    <Ionicons name="checkmark" size={20} color="#EC7E00" />
                  )}
                </TouchableOpacity>
              )}
              ListEmptyComponent={
                <View style={styles.emptyList}>
                  <Text style={styles.emptyListText}>No contractor types available</Text>
                </View>
              }
            />
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#EEEEEE',
  },
  backButton: {
    padding: 8,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1A1A1A',
  },
  scrollView: {
    flex: 1,
  },
  formContainer: {
    padding: 16,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 24,
    marginBottom: 12,
    paddingBottom: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#EEEEEE',
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#1A1A1A',
    marginLeft: 8,
  },
  inputGroup: {
    marginBottom: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333333',
    marginBottom: 8,
  },
  required: {
    color: '#E74C3C',
  },
  input: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#DDDDDD',
    borderRadius: 8,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 15,
    color: '#1A1A1A',
  },
  textArea: {
    minHeight: 100,
    textAlignVertical: 'top',
  },
  dropdown: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#DDDDDD',
    borderRadius: 8,
    paddingHorizontal: 14,
    paddingVertical: 14,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  dropdownText: {
    fontSize: 15,
    color: '#1A1A1A',
    flex: 1,
  },
  placeholder: {
    color: '#999999',
  },
  helperText: {
    fontSize: 12,
    color: '#666666',
    marginTop: 6,
  },
  row: {
    flexDirection: 'row',
  },
  uploadButton: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#DDDDDD',
    borderRadius: 8,
    borderStyle: 'dashed',
    padding: 20,
    alignItems: 'center',
  },
  uploadPlaceholder: {
    alignItems: 'center',
  },
  uploadText: {
    fontSize: 14,
    color: '#333333',
    marginTop: 8,
  },
  uploadHint: {
    fontSize: 11,
    color: '#999999',
    marginTop: 4,
  },
  uploadedFile: {
    flexDirection: 'row',
    alignItems: 'center',
    width: '100%',
  },
  thumbnailImage: {
    width: 50,
    height: 50,
    borderRadius: 6,
    marginRight: 12,
  },
  thumbnailSmall: {
    width: 36,
    height: 36,
    borderRadius: 4,
  },
  fileName: {
    flex: 1,
    fontSize: 14,
    color: '#333333',
  },
  uploadButtonSmall: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF5EB',
    borderWidth: 1,
    borderColor: '#EC7E00',
    borderRadius: 8,
    padding: 12,
    marginBottom: 8,
  },
  addFileText: {
    fontSize: 14,
    color: '#EC7E00',
    fontWeight: '500',
    marginLeft: 8,
  },
  fileItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F5F5F5',
    borderRadius: 6,
    padding: 10,
    marginBottom: 6,
  },
  fileItemName: {
    flex: 1,
    fontSize: 13,
    color: '#333333',
    marginLeft: 8,
  },
  submitButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EC7E00',
    borderRadius: 8,
    paddingVertical: 16,
    marginTop: 24,
    marginBottom: 32,
  },
  submitButtonDisabled: {
    backgroundColor: '#CCCCCC',
  },
  submitButtonText: {
    fontSize: 16,
    fontWeight: '700',
    color: '#FFFFFF',
    marginLeft: 8,
  },
  // Modal styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: '70%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#EEEEEE',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1A1A1A',
  },
  modalItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  modalItemText: {
    fontSize: 15,
    color: '#333333',
  },
  emptyList: {
    padding: 20,
    alignItems: 'center',
  },
  emptyListText: {
    fontSize: 14,
    color: '#999999',
  },
});
