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
  Image,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { auth_service } from '../../services/auth_service'; // you'll add updateProfile here later
import * as ImagePicker from 'expo-image-picker';
import { storage_service } from '../../utils/storage';

export default function EditProfileScreen({ userData, onBackPress, onSaveSuccess }) {
  const [username, setUsername] = useState(userData?.username || '');
  const [email, setEmail] = useState(userData?.email || '');
  const [profilePic, setProfilePic] = useState(userData?.profile_pic || '');
  const [coverPhoto, setCoverPhoto] = useState(userData?.cover_photo || '');
  const [prefilledFields, setPrefilledFields] = useState<Record<string, boolean>>({});
  // Additional editable fields
  const [firstName, setFirstName] = useState(userData?.first_name || '');
  const [lastName, setLastName] = useState(userData?.last_name || '');
  const [phone, setPhone] = useState(userData?.phone || userData?.phone_number || '');
  const [company, setCompany] = useState(userData?.company || '');
  const [jobTitle, setJobTitle] = useState(userData?.job_title || userData?.role || '');
  const [website, setWebsite] = useState(userData?.website || '');
  const [bio, setBio] = useState(userData?.bio || '');
  const [addressLine1, setAddressLine1] = useState(userData?.address_line1 || userData?.address || '');
  const [addressLine2, setAddressLine2] = useState(userData?.address_line2 || '');
  const [city, setCity] = useState(userData?.city || '');
  const [province, setProvince] = useState(userData?.province || '');
  const [postalCode, setPostalCode] = useState(userData?.postal_code || userData?.zip || '');
  const [isSaving, setIsSaving] = useState(false);

  // Helper: mark field as edited (clears prefilled indicator)
  const onEdit = (key: string, setter: (v: string) => void) => (val: string) => {
    setter(val);
    setPrefilledFields((prev) => ({ ...prev, [key]: false }));
  };

  // Prefill values from nested user data and mark fields
  useEffect(() => {
    const existing = userData || {};
    const owner = existing.property_owner || existing.owner || {};
    const contractor = existing.contractor || {};
    const cu = existing.contractor_user || {};

    // Derive candidates
    const candUsername = username || existing.username || existing.user?.username || '';
    const candEmail = email || existing.email || existing.user?.email || '';
    const candFirst = firstName || existing.first_name || cu.authorized_rep_fname || owner.first_name || '';
    const candLast = lastName || existing.last_name || cu.authorized_rep_lname || owner.last_name || '';
    const candPhone = phone || existing.phone || existing.phone_number || owner.phone_number || cu.phone_number || existing.user?.phone_number || '';
    const candCompany = company || existing.company || contractor.company_name || existing.company_name || '';
    const candJobTitle = jobTitle || existing.job_title || cu.authorized_rep_position || existing.role || '';
    const candWebsite = website || existing.website || contractor.company_website || existing.company_website || '';
    const candBio = bio || existing.bio || '';

    // Address candidates: prefer owner.address, then contractor.business_address, then user.address
    const addrCandidate = (existing.address || owner.address || contractor.business_address || '') as string;
    let candAddr1 = addressLine1;
    let candCity = city;
    let candProvince = province;
    let candPostal = postalCode;
    if (addrCandidate && (!candAddr1 || !candCity || !candProvince || !candPostal)) {
      const parts = addrCandidate.split(',').map((s) => s.trim());
      if (parts.length >= 3) {
        candAddr1 = candAddr1 || parts[0] || '';
        // parts[1] is often barangay; skip for now
        candCity = candCity || parts[2] || '';
        // Province may be at parts[3]; postal may be at parts[4] or attached
        const lastPart = parts[4] ? parts[4] : (parts[3] || '');
        if (!candProvince && parts[3]) candProvince = parts[3];
        const m = (lastPart || '').match(/(\d{4,})/);
        if (!candPostal && m) candPostal = m[1];
      }
    }

    // Apply state only when empty to avoid overriding user edits
    if (!username && candUsername) setUsername(candUsername);
    if (!email && candEmail) setEmail(candEmail);
    if (!firstName && candFirst) setFirstName(candFirst);
    if (!lastName && candLast) setLastName(candLast);
    if (!phone && candPhone) setPhone(candPhone);
    if (!company && candCompany) setCompany(candCompany);
    if (!jobTitle && candJobTitle) setJobTitle(candJobTitle);
    if (!website && candWebsite) setWebsite(candWebsite);
    if (!bio && candBio) setBio(candBio);
    if (!addressLine1 && candAddr1) setAddressLine1(candAddr1);
    if (!city && candCity) setCity(candCity);
    if (!province && candProvince) setProvince(candProvince);
    if (!postalCode && candPostal) setPostalCode(candPostal);

    // Mark prefilled fields (based on final candidates)
    const prefilled: Record<string, boolean> = {};
    const markIf = (key: string, value?: any) => { if (value && `${value}`.trim() !== '') prefilled[key] = true; };
    markIf('username', candUsername);
    markIf('email', candEmail);
    markIf('first_name', candFirst);
    markIf('last_name', candLast);
    markIf('phone', candPhone);
    markIf('company', candCompany);
    markIf('job_title', candJobTitle);
    markIf('website', candWebsite);
    markIf('bio', candBio);
    markIf('address_line1', candAddr1);
    markIf('address_line2', addressLine2);
    markIf('city', candCity);
    markIf('province', candProvince);
    markIf('postal_code', candPostal);
    setPrefilledFields(prefilled);
  }, []);

  const handleSave = async () => {
    if (!username.trim() || !email.trim()) {
      Alert.alert('Error', 'Please fill in all fields.');
      return;
    }

    setIsSaving(true);
    try {
      const formData = new FormData();
      formData.append('username', username);
      formData.append('email', email);
      // Optional details
      if (firstName) formData.append('first_name', firstName);
      if (lastName) formData.append('last_name', lastName);
      if (phone) formData.append('phone', phone);
      if (company) formData.append('company', company);
      if (jobTitle) formData.append('job_title', jobTitle);
      if (website) formData.append('website', website);
      if (bio) formData.append('bio', bio);
      if (addressLine1) formData.append('address_line1', addressLine1);
      if (addressLine2) formData.append('address_line2', addressLine2);
      if (city) formData.append('city', city);
      if (province) formData.append('province', province);
      if (postalCode) formData.append('postal_code', postalCode);

      // Attach profile picture if it's a local uri (starts with file: or content: or http but not already a storage path)
      if (profilePic && typeof profilePic === 'string' && (profilePic.startsWith('file:') || profilePic.startsWith('content:') || profilePic.startsWith('data:') || profilePic.startsWith('blob:') || profilePic.startsWith('http')) ) {
        formData.append('profile_pic', {
          uri: profilePic,
          type: 'image/jpeg',
          name: 'profile.jpg',
        } as any);
      }

      if (coverPhoto && typeof coverPhoto === 'string' && (coverPhoto.startsWith('file:') || coverPhoto.startsWith('content:') || coverPhoto.startsWith('data:') || coverPhoto.startsWith('blob:') || coverPhoto.startsWith('http')) ) {
        formData.append('cover_photo', {
          uri: coverPhoto,
          type: 'image/jpeg',
          name: 'cover.jpg',
        } as any);
      }

      const response = await auth_service.updateProfile(formData);

      if (response.success) {
        // Update local storage user data if API returned updated user object
        const returnedUser = response.data?.data || response.data || null;
        let updatedUser = { ...userData };
        if (returnedUser) {
          // Merge returned fields
          updatedUser = { ...updatedUser, ...returnedUser };
        } else {
          // Fallback: update with provided values (use storage path only if backend stored them)
          updatedUser.username = username;
          updatedUser.email = email;
          updatedUser.first_name = firstName;
          updatedUser.last_name = lastName;
          updatedUser.phone = phone;
          updatedUser.company = company;
          updatedUser.job_title = jobTitle;
          updatedUser.website = website;
          updatedUser.bio = bio;
          updatedUser.address_line1 = addressLine1;
          updatedUser.address_line2 = addressLine2;
          updatedUser.city = city;
          updatedUser.province = province;
          updatedUser.postal_code = postalCode;
        }

        await storage_service.save_user_data(updatedUser);

        Alert.alert('Success', 'Profile updated successfully!', [
          { text: 'OK', onPress: onSaveSuccess },
        ]);
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

  const pickImage = async (forCover = false) => {
    try {
      const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (!permission.granted) {
        Alert.alert('Permission required', 'Permission to access photos is required to change profile images.');
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        quality: 0.8,
      });

      if (!result.cancelled) {
        if (forCover) setCoverPhoto(result.uri);
        else setProfilePic(result.uri);
      }
    } catch (error) {
      console.error('Image picker error:', error);
      Alert.alert('Error', 'Could not pick image.');
    }
  };

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

        {/* Profile Picture */}
        <View style={styles.avatarContainer}>
          {profilePic ? (
            <TouchableOpacity onPress={() => pickImage(false)}>
              <Image source={{ uri: profilePic }} style={styles.avatar} />
            </TouchableOpacity>
          ) : (
            <TouchableOpacity onPress={() => pickImage(false)} style={styles.avatarPlaceholder}>
              <Ionicons name="person-outline" size={50} color="#fff" />
            </TouchableOpacity>
          )}
        </View>

        {/* Cover Photo Picker */}
        <View style={{ alignItems: 'center', marginBottom: 20 }}>
          <TouchableOpacity onPress={() => pickImage(true)} style={styles.coverPicker}>
            {coverPhoto ? (
              <Image source={{ uri: coverPhoto }} style={styles.coverPreview} />
            ) : (
              <View style={styles.coverPlaceholder}>
                <Text style={{ color: '#fff' }}>Change Cover Photo</Text>
              </View>
            )}
          </TouchableOpacity>
        </View>

        {/* Form */}
        <View style={styles.formContainer}>
          <Text style={styles.label}>Username</Text>
          <TextInput
            style={[styles.input, prefilledFields.username && styles.prefilledInput]}
            value={username}
            onChangeText={onEdit('username', setUsername)}
            placeholder="Enter username"
            placeholderTextColor="#999"
          />

          <Text style={styles.label}>Email</Text>
          <TextInput
            style={[styles.input, prefilledFields.email && styles.prefilledInput]}
            value={email}
            onChangeText={onEdit('email', setEmail)}
            placeholder="Enter email"
            placeholderTextColor="#999"
            keyboardType="email-address"
          />

          <Text style={styles.label}>First Name</Text>
          <TextInput
            style={[styles.input, prefilledFields.first_name && styles.prefilledInput]}
            value={firstName}
            onChangeText={onEdit('first_name', setFirstName)}
            placeholder="Enter first name"
            placeholderTextColor="#999"
          />

          <Text style={styles.label}>Last Name</Text>
          <TextInput
            style={[styles.input, prefilledFields.last_name && styles.prefilledInput]}
            value={lastName}
            onChangeText={onEdit('last_name', setLastName)}
            placeholder="Enter last name"
            placeholderTextColor="#999"
          />

          <Text style={styles.label}>Phone</Text>
          <TextInput
            style={[styles.input, prefilledFields.phone && styles.prefilledInput]}
            value={phone}
            onChangeText={onEdit('phone', setPhone)}
            placeholder="Enter phone number"
            placeholderTextColor="#999"
            keyboardType="phone-pad"
          />

          <Text style={styles.label}>Company</Text>
          <TextInput
            style={[styles.input, prefilledFields.company && styles.prefilledInput]}
            value={company}
            onChangeText={onEdit('company', setCompany)}
            placeholder="Enter company (optional)"
            placeholderTextColor="#999"
          />

          <Text style={styles.label}>Job Title</Text>
          <TextInput
            style={[styles.input, prefilledFields.job_title && styles.prefilledInput]}
            value={jobTitle}
            onChangeText={onEdit('job_title', setJobTitle)}
            placeholder="Enter job title (optional)"
            placeholderTextColor="#999"
          />

          <Text style={styles.label}>Website</Text>
          <TextInput
            style={[styles.input, prefilledFields.website && styles.prefilledInput]}
            value={website}
            onChangeText={onEdit('website', setWebsite)}
            placeholder="https://example.com (optional)"
            placeholderTextColor="#999"
            keyboardType="url"
          />

          <Text style={styles.label}>Bio</Text>
          <TextInput
            style={[styles.input, prefilledFields.bio && styles.prefilledInput, { height: 100 } ]}
            value={bio}
            onChangeText={onEdit('bio', setBio)}
            placeholder="Tell us about yourself (optional)"
            placeholderTextColor="#999"
            multiline
          />

          <Text style={styles.label}>Address Line 1</Text>
          <TextInput
            style={[styles.input, prefilledFields.address_line1 && styles.prefilledInput]}
            value={addressLine1}
            onChangeText={onEdit('address_line1', setAddressLine1)}
            placeholder="Street address"
            placeholderTextColor="#999"
          />

          <Text style={styles.label}>Address Line 2</Text>
          <TextInput
            style={[styles.input, prefilledFields.address_line2 && styles.prefilledInput]}
            value={addressLine2}
            onChangeText={onEdit('address_line2', setAddressLine2)}
            placeholder="Apartment, suite, etc. (optional)"
            placeholderTextColor="#999"
          />

          <Text style={styles.label}>City</Text>
          <TextInput
            style={[styles.input, prefilledFields.city && styles.prefilledInput]}
            value={city}
            onChangeText={onEdit('city', setCity)}
            placeholder="Enter city"
            placeholderTextColor="#999"
          />

          <Text style={styles.label}>Province</Text>
          <TextInput
            style={[styles.input, prefilledFields.province && styles.prefilledInput]}
            value={province}
            onChangeText={onEdit('province', setProvince)}
            placeholder="Enter province"
            placeholderTextColor="#999"
          />

          <Text style={styles.label}>Postal Code</Text>
          <TextInput
            style={[styles.input, prefilledFields.postal_code && styles.prefilledInput]}
            value={postalCode}
            onChangeText={onEdit('postal_code', setPostalCode)}
            placeholder="Enter postal code"
            placeholderTextColor="#999"
            keyboardType="number-pad"
          />
        </View>

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
  avatarContainer: {
    alignItems: 'center',
    marginBottom: 30,
  },
  avatar: {
    width: 100,
    height: 100,
    borderRadius: 50,
  },
  avatarPlaceholder: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: '#EC7E00',
    justifyContent: 'center',
    alignItems: 'center',
  },
  formContainer: {
    marginBottom: 30,
  },
  label: {
    fontSize: 16,
    color: '#333',
    marginBottom: 8,
  },
  input: {
    borderWidth: 1,
    borderColor: '#E5E5E5',
    borderRadius: 12,
    padding: 14,
    marginBottom: 16,
    fontSize: 16,
  },
  prefilledInput: {
    color: '#EC7E00',
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
  coverPicker: {
    width: '90%',
  },
  coverPreview: {
    width: '100%',
    height: 120,
    borderRadius: 12,
  },
  coverPlaceholder: {
    width: '100%',
    height: 120,
    backgroundColor: '#EC7E00',
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
  },
});
