// src/screens/both/EditProfileScreen.tsx
// @ts-nocheck
import React, { useState } from 'react';
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
  const [isSaving, setIsSaving] = useState(false);

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

      const MEDIA_IMAGES = (ImagePicker.MediaType && ImagePicker.MediaType.Images)
        || (ImagePicker.MediaTypeOptions && ImagePicker.MediaTypeOptions.Images)
        || 'Images';

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: MEDIA_IMAGES,
        allowsEditing: true,
        quality: 0.8,
      });

      if (!result.cancelled && result.assets && result.assets[0]) {
        if (forCover) setCoverPhoto(result.assets[0].uri);
        else setProfilePic(result.assets[0].uri);
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
