// src/screens/both/EditProfileScreen.tsx
// @ts-nocheck
import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Alert,
  ActivityIndicator,
  Modal,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { auth_service } from '../../services/auth_service';
import { role_service } from '../../services/role_service';
import { storage_service } from '../../utils/storage';

export default function EditProfileScreen({ userData, onBackPress, onSaveSuccess }) {
  const [currentTab, setCurrentTab] = useState<'personal' | 'addresses'>('personal');
  const [isSaving, setIsSaving] = useState(false);
  const [showOTPModal, setShowOTPModal] = useState(false);
  const [otpCode, setOtpCode] = useState('');
  const [pendingPhone, setPendingPhone] = useState('');
  const [sendingOTP, setSendingOTP] = useState(false);

  // Personal Information Fields
  const [username, setUsername] = useState(userData?.username || '');
  const [email, setEmail] = useState(userData?.email || '');
  const [firstName, setFirstName] = useState(userData?.first_name || '');
  const [lastName, setLastName] = useState(userData?.last_name || ''); // Locked after approval
  const [phone, setPhone] = useState(userData?.phone || userData?.phone_number || '');
  const [occupation, setOccupation] = useState(userData?.occupation || '');
  const [dateOfBirth, setDateOfBirth] = useState(userData?.date_of_birth || '');

  // Contractor Business Details
  const [companyName, setCompanyName] = useState(userData?.contractor?.company_name || '');
  const [companyWebsite, setCompanyWebsite] = useState(userData?.contractor?.company_website || '');
  const [companySocialMedia, setCompanySocialMedia] = useState(userData?.contractor?.company_social_media || '');
  const [companyDescription, setCompanyDescription] = useState(userData?.contractor?.company_description || '');
  const [servicesOffered, setServicesOffered] = useState(userData?.contractor?.services_offered || '');
  const [picabNumber, setPicabNumber] = useState(userData?.contractor?.picab_number || '');
  const [businessPermitNumber, setBusinessPermitNumber] = useState(userData?.contractor?.business_permit_number || '');
  const [tinNumber, setTinNumber] = useState(userData?.contractor?.tin_business_reg_number || '');

  // System Calculated Fields (Display Only)
  const [yearsOfExperience, setYearsOfExperience] = useState(userData?.contractor?.years_of_experience || '0');
  const [completedProjects, setCompletedProjects] = useState(userData?.contractor?.completed_projects || '0');

  // Address Fields
  const [addressStreet, setAddressStreet] = useState(userData?.address_street || '');
  const [addressBarangay, setAddressBarangay] = useState(userData?.address_barangay || '');
  const [addressCity, setAddressCity] = useState(userData?.address_city || '');
  const [addressProvince, setAddressProvince] = useState(userData?.address_province || '');
  const [addressPostal, setAddressPostal] = useState(userData?.address_postal || '');

  // Verification Status Tracking
  const [pendingVerifications, setPendingVerifications] = useState<Record<string, boolean>>({});
  const [prefilledFields, setPrefilledFields] = useState<Record<string, boolean>>({});

  useEffect(() => {
    loadVerificationStatus();
  }, []);

  const loadVerificationStatus = async () => {
    try {
      const response = await role_service.get_current_role();
      if (response.success) {
        const data = response.data || response;

        // Check for pending verifications
        const pending: Record<string, boolean> = {};

        if (data.contractor?.verification_status === 'pending') {
          pending.company_name = true;
          pending.business_address = true;
          pending.picab_number = true;
          pending.business_permit_number = true;
          pending.tin_business_reg_number = true;
        }

        if (data.owner?.address_verification_pending) {
          pending.address = true;
        }

        setPendingVerifications(pending);
      }
    } catch (error) {
      console.error('Error loading verification status:', error);
    }
  };

  const handlePhoneChange = () => {
    if (phone === (userData?.phone || userData?.phone_number)) {
      return; // No change
    }

    Alert.alert(
      'Verify Phone Number',
      'Changing your phone number requires OTP verification. We will send a code to your new number.',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Continue',
          onPress: async () => {
            setSendingOTP(true);
            try {
              // Send OTP to new phone number
              const response = await auth_service.send_otp(phone);
              if (response.success) {
                setPendingPhone(phone);
                setShowOTPModal(true);
              } else {
                Alert.alert('Error', 'Failed to send OTP. Please try again.');
              }
            } catch (error) {
              Alert.alert('Error', 'Network error. Please try again.');
            } finally {
              setSendingOTP(false);
            }
          }
        }
      ]
    );
  };

  const verifyOTP = async () => {
    if (!otpCode.trim()) {
      Alert.alert('Error', 'Please enter OTP code');
      return;
    }

    setIsSaving(true);
    try {
      const response = await auth_service.verify_otp(pendingPhone, otpCode);
      if (response.success) {
        setShowOTPModal(false);
        setOtpCode('');
        Alert.alert('Success', 'Phone number verified successfully!');
        // Phone number is now updated
      } else {
        Alert.alert('Error', response.message || 'Invalid OTP code');
      }
    } catch (error) {
      Alert.alert('Error', 'Verification failed');
    } finally {
      setIsSaving(false);
    }
  };

  const handleAddressChange = () => {
    // Check if address actually changed
    const originalAddress = {
      street: userData?.address_street || '',
      barangay: userData?.address_barangay || '',
      city: userData?.address_city || '',
      province: userData?.address_province || '',
      postal: userData?.address_postal || ''
    };

    const newAddress = {
      street: addressStreet,
      barangay: addressBarangay,
      city: addressCity,
      province: addressProvince,
      postal: addressPostal
    };

    if (JSON.stringify(originalAddress) !== JSON.stringify(newAddress)) {
      Alert.alert(
        'Address Verification',
        'Changing your address will trigger address verification. You may need to provide proof of address.',
        [
          { text: 'Cancel', style: 'cancel' },
          { text: 'Proceed', onPress: () => setPendingVerifications(prev => ({ ...prev, address: true })) }
        ]
      );
    }
  };

  const handleSave = async () => {
    // Validate required fields
    if (!username.trim()) {
      Alert.alert('Error', 'Username is required');
      return;
    }

    setIsSaving(true);
    try {
      const formData = new FormData();

      // Personal Information
      formData.append('username', username);
      formData.append('email', email);
      formData.append('first_name', firstName);

      // Last name is locked - only include if no change or if it's a legal name change request
      if (lastName !== userData?.last_name) {
        Alert.alert(
          'Legal Name Change',
          'Changing your last name requires a legal name change request. Please contact support.',
          [{ text: 'OK' }]
        );
        setIsSaving(false);
        return;
      }

      // Phone - only include if verified through OTP
      if (phone !== userData?.phone && phone === pendingPhone) {
        formData.append('phone', phone);
      } else if (phone !== userData?.phone) {
        Alert.alert('Error', 'Please verify your new phone number first');
        setIsSaving(false);
        return;
      }

      // Occupation
      if (occupation) formData.append('occupation', occupation);

      // Contractor Business Details
      // Always editable fields
      if (companyWebsite) formData.append('company_website', companyWebsite);
      if (companySocialMedia) formData.append('company_social_media', companySocialMedia);
      if (companyDescription) formData.append('company_description', companyDescription);
      if (servicesOffered) formData.append('services_offered', servicesOffered);

      // Fields that trigger re-verification
      const reVerificationFields: Record<string, any> = {
        company_name: companyName,
        picab_number: picabNumber,
        business_permit_number: businessPermitNumber,
        tin_business_reg_number: tinNumber
      };

      Object.entries(reVerificationFields).forEach(([key, value]) => {
        if (value && value !== userData?.contractor?.[key]) {
          formData.append(key, value);
          // Mark for re-verification
          formData.append(`${key}_requires_verification`, 'true');
        }
      });

      // Address (triggers verification if changed)
      const addressData = {
        street: addressStreet,
        barangay: addressBarangay,
        city: addressCity,
        province: addressProvince,
        postal: addressPostal
      };

      const originalAddress = {
        street: userData?.address_street || '',
        barangay: userData?.address_barangay || '',
        city: userData?.address_city || '',
        province: userData?.address_province || '',
        postal: userData?.address_postal || ''
      };

      if (JSON.stringify(addressData) !== JSON.stringify(originalAddress)) {
        Object.entries(addressData).forEach(([key, value]) => {
          if (value) formData.append(`address_${key}`, value);
        });
        formData.append('address_requires_verification', 'true');
      }

      const response = await auth_service.updateProfile(formData);

      if (response.success) {
        const returnedUser = response.data?.data || response.data || null;
        let updatedUser = { ...userData };

        if (returnedUser) {
          updatedUser = { ...updatedUser, ...returnedUser };
        }

        await storage_service.save_user_data(updatedUser);

        Alert.alert(
          'Profile Updated',
          pendingVerifications.address ? 'Your address changes have been submitted for verification.' : 'Profile updated successfully!',
          [{ text: 'OK', onPress: onSaveSuccess }]
        );
      } else {
        Alert.alert('Error', response.message || 'Failed to update profile.');
      }
    } catch (error) {
      console.error('Update profile error:', error);
      Alert.alert('Error', 'Network error. Please try again.');
    } finally {
      setIsSaving(false);
    }
  };

  const renderVerificationBadge = (fieldName: string) => {
    if (pendingVerifications[fieldName]) {
      return (
        <View style={styles.pendingBadge}>
          <Ionicons name="time-outline" size={14} color="#F39C12" />
          <Text style={styles.pendingBadgeText}>Pending Review</Text>
        </View>
      );
    }
    return null;
  };

  const renderLockedField = (label: string, value: string) => (
    <>
      <Text style={styles.label}>
        {label}
        <Ionicons name="lock-closed" size={14} color="#999" />
      </Text>
      <View style={styles.lockedField}>
        <Text style={styles.lockedFieldText}>{value || 'Not set'}</Text>
      </View>
    </>
  );

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={onBackPress} style={styles.backButton}>
            <Ionicons name="chevron-back" size={28} color="#333" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Edit Profile</Text>
        </View>

        {/* Tabs */}
        <View style={styles.tabContainer}>
          <TouchableOpacity
            style={[styles.tabButton, currentTab === 'personal' && styles.tabButtonActive]}
            onPress={() => setCurrentTab('personal')}
          >
            <Text style={[styles.tabButtonText, currentTab === 'personal' && styles.tabButtonTextActive]}>
              Personal Information
            </Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.tabButton, currentTab === 'addresses' && styles.tabButtonActive]}
            onPress={() => setCurrentTab('addresses')}
          >
            <Text style={[styles.tabButtonText, currentTab === 'addresses' && styles.tabButtonTextActive]}>
              Addresses
            </Text>
          </TouchableOpacity>
        </View>

        {/* Personal Information Tab */}
        {currentTab === 'personal' && (
          <View style={styles.formContainer}>
            {/* Basic Info */}
            <Text style={styles.sectionTitle}>Basic Information</Text>

            <Text style={styles.label}>Username</Text>
            <TextInput
              style={styles.input}
              value={username}
              onChangeText={setUsername}
              placeholder="Enter username"
              placeholderTextColor="#999"
            />

            <Text style={styles.label}>Email</Text>
            <TextInput
              style={styles.input}
              value={email}
              onChangeText={setEmail}
              placeholder="Enter email"
              placeholderTextColor="#999"
              keyboardType="email-address"
            />

            <Text style={styles.label}>First Name</Text>
            <TextInput
              style={styles.input}
              value={firstName}
              onChangeText={setFirstName}
              placeholder="Enter first name"
              placeholderTextColor="#999"
            />

            {renderLockedField('Last Name (Locked)', lastName)}

            <Text style={styles.label}>Phone Number {pendingVerifications.phone && '(Pending Verification)'}</Text>
            <View style={styles.phoneInputContainer}>
              <TextInput
                style={[styles.input, styles.phoneInput]}
                value={phone}
                onChangeText={setPhone}
                placeholder="Enter phone number"
                placeholderTextColor="#999"
                keyboardType="phone-pad"
                onBlur={handlePhoneChange}
              />
              {pendingVerifications.phone && (
                <View style={styles.verificationBadge}>
                  <Ionicons name="time-outline" size={16} color="#F39C12" />
                </View>
              )}
            </View>

            <Text style={styles.label}>Occupation</Text>
            <TextInput
              style={styles.input}
              value={occupation}
              onChangeText={setOccupation}
              placeholder="Enter occupation"
              placeholderTextColor="#999"
            />

            <Text style={styles.label}>Date of Birth</Text>
            <TextInput
              style={styles.input}
              value={dateOfBirth}
              onChangeText={setDateOfBirth}
              placeholder="YYYY-MM-DD"
              placeholderTextColor="#999"
            />

            {/* System Calculated Fields */}
            <Text style={styles.sectionTitle}>Statistics</Text>
            <View style={styles.calculatedField}>
              <Text style={styles.calculatedLabel}>Years of Experience</Text>
              <Text style={styles.calculatedValue}>{yearsOfExperience}</Text>
            </View>

            <View style={styles.calculatedField}>
              <Text style={styles.calculatedLabel}>Completed Projects</Text>
              <Text style={styles.calculatedValue}>{completedProjects}</Text>
            </View>

            {/* Contractor Business Details */}
            {userData?.user_type === 'contractor' && (
              <>
                <Text style={styles.sectionTitle}>Contractor Business Details</Text>

                <Text style={styles.label}>
                  Company Name {renderVerificationBadge('company_name')}
                </Text>
                <TextInput
                  style={[styles.input, pendingVerifications.company_name && styles.pendingInput]}
                  value={companyName}
                  onChangeText={setCompanyName}
                  placeholder="Enter company name"
                  placeholderTextColor="#999"
                />

                <Text style={styles.label}>Company Website</Text>
                <TextInput
                  style={styles.input}
                  value={companyWebsite}
                  onChangeText={setCompanyWebsite}
                  placeholder="https://example.com"
                  placeholderTextColor="#999"
                  keyboardType="url"
                />

                <Text style={styles.label}>Social Media</Text>
                <TextInput
                  style={styles.input}
                  value={companySocialMedia}
                  onChangeText={setCompanySocialMedia}
                  placeholder="Social media links"
                  placeholderTextColor="#999"
                />

                <Text style={styles.label}>Company Description</Text>
                <TextInput
                  style={[styles.input, styles.textArea]}
                  value={companyDescription}
                  onChangeText={setCompanyDescription}
                  placeholder="Describe your company"
                  placeholderTextColor="#999"
                  multiline
                  numberOfLines={4}
                />

                <Text style={styles.label}>Services Offered</Text>
                <TextInput
                  style={[styles.input, styles.textArea]}
                  value={servicesOffered}
                  onChangeText={setServicesOffered}
                  placeholder="List your services"
                  placeholderTextColor="#999"
                  multiline
                  numberOfLines={3}
                />

                <Text style={styles.label}>
                  PICAB Number {renderVerificationBadge('picab_number')}
                </Text>
                <TextInput
                  style={[styles.input, pendingVerifications.picab_number && styles.pendingInput]}
                  value={picabNumber}
                  onChangeText={setPicabNumber}
                  placeholder="Enter PICAB number"
                  placeholderTextColor="#999"
                />

                <Text style={styles.label}>
                  Business Permit Number {renderVerificationBadge('business_permit_number')}
                </Text>
                <TextInput
                  style={[styles.input, pendingVerifications.business_permit_number && styles.pendingInput]}
                  value={businessPermitNumber}
                  onChangeText={setBusinessPermitNumber}
                  placeholder="Enter business permit number"
                  placeholderTextColor="#999"
                />

                <Text style={styles.label}>
                  TIN Number {renderVerificationBadge('tin_business_reg_number')}
                </Text>
                <TextInput
                  style={[styles.input, pendingVerifications.tin_business_reg_number && styles.pendingInput]}
                  value={tinNumber}
                  onChangeText={setTinNumber}
                  placeholder="Enter TIN number"
                  placeholderTextColor="#999"
                />
              </>
            )}
          </View>
        )}

        {/* Addresses Tab */}
        {currentTab === 'addresses' && (
          <View style={styles.formContainer}>
            <Text style={styles.sectionTitle}>
              Address Information {renderVerificationBadge('address')}
            </Text>

            <Text style={styles.label}>Street Address</Text>
            <TextInput
              style={[styles.input, pendingVerifications.address && styles.pendingInput]}
              value={addressStreet}
              onChangeText={setAddressStreet}
              onBlur={handleAddressChange}
              placeholder="Enter street address"
              placeholderTextColor="#999"
            />

            <Text style={styles.label}>Barangay</Text>
            <TextInput
              style={[styles.input, pendingVerifications.address && styles.pendingInput]}
              value={addressBarangay}
              onChangeText={setAddressBarangay}
              onBlur={handleAddressChange}
              placeholder="Enter barangay"
              placeholderTextColor="#999"
            />

            <Text style={styles.label}>City/Municipality</Text>
            <TextInput
              style={[styles.input, pendingVerifications.address && styles.pendingInput]}
              value={addressCity}
              onChangeText={setAddressCity}
              onBlur={handleAddressChange}
              placeholder="Enter city"
              placeholderTextColor="#999"
            />

            <Text style={styles.label}>Province</Text>
            <TextInput
              style={[styles.input, pendingVerifications.address && styles.pendingInput]}
              value={addressProvince}
              onChangeText={setAddressProvince}
              onBlur={handleAddressChange}
              placeholder="Enter province"
              placeholderTextColor="#999"
            />

            <Text style={styles.label}>Postal Code</Text>
            <TextInput
              style={[styles.input, pendingVerifications.address && styles.pendingInput]}
              value={addressPostal}
              onChangeText={setAddressPostal}
              onBlur={handleAddressChange}
              placeholder="Enter postal code"
              placeholderTextColor="#999"
              keyboardType="number-pad"
            />

            {pendingVerifications.address && (
              <View style={styles.verificationMessage}>
                <Ionicons name="information-circle-outline" size={20} color="#F39C12" />
                <Text style={styles.verificationMessageText}>
                  Your address changes will be reviewed by our team.
                </Text>
              </View>
            )}
          </View>
        )}

        {/* Save Button */}
        <TouchableOpacity
          style={[styles.saveButton, isSaving && styles.buttonDisabled]}
          onPress={handleSave}
          disabled={isSaving}
        >
          {isSaving ? (
            <ActivityIndicator color="#FFF" />
          ) : (
            <Text style={styles.saveButtonText}>Save Changes</Text>
          )}
        </TouchableOpacity>
      </ScrollView>

      {/* OTP Verification Modal */}
      <Modal visible={showOTPModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Verify Phone Number</Text>
              <TouchableOpacity onPress={() => setShowOTPModal(false)}>
                <Ionicons name="close" size={24} color="#333" />
              </TouchableOpacity>
            </View>

            <Text style={styles.modalText}>
              Enter the 6-digit code sent to {pendingPhone}
            </Text>

            <TextInput
              style={styles.modalInput}
              value={otpCode}
              onChangeText={setOtpCode}
              placeholder="Enter OTP code"
              placeholderTextColor="#999"
              keyboardType="number-pad"
              maxLength={6}
            />

            <TouchableOpacity
              style={[styles.modalButton, isSaving && styles.buttonDisabled]}
              onPress={verifyOTP}
              disabled={isSaving}
            >
              {isSaving ? (
                <ActivityIndicator color="#FFF" />
              ) : (
                <Text style={styles.modalButtonText}>Verify</Text>
              )}
            </TouchableOpacity>

            <TouchableOpacity onPress={handlePhoneChange}>
              <Text style={styles.resendText}>Resend Code</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FEFEFE',
  },
  scrollContent: {
    padding: 20,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
  },
  backButton: {
    marginRight: 10,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
  },
  tabContainer: {
    flexDirection: 'row',
    backgroundColor: '#F5F5F5',
    borderRadius: 12,
    padding: 4,
    marginBottom: 24,
  },
  tabButton: {
    flex: 1,
    paddingVertical: 12,
    alignItems: 'center',
    borderRadius: 10,
  },
  tabButtonActive: {
    backgroundColor: '#FFFFFF',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 2,
  },
  tabButtonText: {
    fontSize: 14,
    color: '#666',
  },
  tabButtonTextActive: {
    color: '#EC7E00',
    fontWeight: '600',
  },
  formContainer: {
    marginBottom: 30,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 16,
    marginTop: 8,
  },
  label: {
    fontSize: 14,
    color: '#666',
    marginBottom: 6,
    marginTop: 12,
  },
  input: {
    borderWidth: 1,
    borderColor: '#E5E5E5',
    borderRadius: 12,
    padding: 14,
    fontSize: 16,
    backgroundColor: '#FFFFFF',
  },
  phoneInputContainer: {
    position: 'relative',
  },
  phoneInput: {
    paddingRight: 40,
  },
  pendingInput: {
    borderColor: '#F39C12',
    backgroundColor: '#FFF8E5',
  },
  lockedField: {
    borderWidth: 1,
    borderColor: '#E5E5E5',
    borderRadius: 12,
    padding: 14,
    backgroundColor: '#F5F5F5',
  },
  lockedFieldText: {
    fontSize: 16,
    color: '#999',
  },
  textArea: {
    height: 100,
    textAlignVertical: 'top',
  },
  calculatedField: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
  },
  calculatedLabel: {
    fontSize: 14,
    color: '#666',
  },
  calculatedValue: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333',
  },
  pendingBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF8E5',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
    marginLeft: 8,
  },
  pendingBadgeText: {
    fontSize: 11,
    color: '#F39C12',
    marginLeft: 4,
  },
  verificationBadge: {
    position: 'absolute',
    right: 12,
    top: 16,
  },
  verificationMessage: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF8E5',
    padding: 12,
    borderRadius: 8,
    marginTop: 16,
  },
  verificationMessageText: {
    flex: 1,
    fontSize: 13,
    color: '#F39C12',
    marginLeft: 8,
  },
  saveButton: {
    backgroundColor: '#EC7E00',
    paddingVertical: 16,
    borderRadius: 12,
    alignItems: 'center',
  },
  saveButtonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: '600',
  },
  buttonDisabled: {
    opacity: 0.6,
  },
  // Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContainer: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 20,
    width: '90%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
  },
  modalText: {
    fontSize: 14,
    color: '#666',
    marginBottom: 16,
  },
  modalInput: {
    borderWidth: 1,
    borderColor: '#E5E5E5',
    borderRadius: 12,
    padding: 14,
    fontSize: 16,
    marginBottom: 16,
    textAlign: 'center',
    letterSpacing: 4,
  },
  modalButton: {
    backgroundColor: '#EC7E00',
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    marginBottom: 12,
  },
  modalButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  resendText: {
    color: '#EC7E00',
    fontSize: 14,
    textAlign: 'center',
    textDecorationLine: 'underline',
  },
});
