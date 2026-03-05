// @ts-nocheck
import React, { useState, useRef, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  Alert,
  Image,
  Animated,
  Platform,
  ScrollView,
  Dimensions
} from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';

interface ProfilePictureScreenProps {
  onBackPress: () => void;
  onComplete: (profileData: ProfileData) => void;
  onSkip: () => void;
  userType?: 'contractor' | 'property_owner';
}

interface ProfileData {
  profileImageUri?: string;
  coverImageUri?: string;
}

const { width } = Dimensions.get('window');
const COVER_HEIGHT = 180;

export default function ProfilePictureScreen({
  onBackPress,
  onComplete,
  onSkip,
  userType = 'property_owner',
}: ProfilePictureScreenProps) {
  const [profileImage, setProfileImage] = useState<string | null>(null);
  const [coverImage, setCoverImage] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const isContractor = userType === 'contractor';

  // Animation values
  const scaleAnim = useRef(new Animated.Value(0.95)).current;
  const fadeAnim = useRef(new Animated.Value(0)).current;

  React.useEffect(() => {
    // Entrance animation
    Animated.parallel([
      Animated.spring(scaleAnim, {
        toValue: 1,
        tension: 100,
        friction: 8,
        useNativeDriver: true,
      }),
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 500,
        useNativeDriver: true,
      }),
    ]).start();
  }, []);

  const requestPermissions = useCallback(async () => {
    const [cameraStatus, libraryStatus] = await Promise.all([
      ImagePicker.requestCameraPermissionsAsync(),
      ImagePicker.requestMediaLibraryPermissionsAsync(),
    ]);

    if (cameraStatus.status !== 'granted' || libraryStatus.status !== 'granted') {
      Alert.alert(
        'Permissions Required',
        'Please grant camera and photo library permissions to set your profile picture.',
        [{ text: 'OK' }]
      );
      return false;
    }
    return true;
  }, []);

  const selectImage = useCallback(async (target: 'profile' | 'cover') => {
    const hasPermissions = await requestPermissions();
    if (!hasPermissions) return;

    const label = target === 'profile'
      ? (isContractor ? 'Company Logo' : 'Profile Picture')
      : (isContractor ? 'Cover Image' : 'Cover Photo');

    Alert.alert(
      `Select ${label}`,
      `Choose how you want to add your ${label.toLowerCase()}`,
      [
        {
          text: 'Take Photo',
          onPress: () => openCamera(target),
          style: 'default'
        },
        {
          text: 'Choose from Library',
          onPress: () => openGallery(target),
          style: 'default'
        },
        {
          text: 'Cancel',
          style: 'cancel'
        }
      ],
      { cancelable: true }
    );
  }, [isContractor]);

  const openCamera = useCallback(async (target: 'profile' | 'cover') => {
    setIsLoading(true);
    try {
      const MEDIA_IMAGES = (ImagePicker.MediaType && ImagePicker.MediaType.Images)
        || (ImagePicker.MediaTypeOptions && ImagePicker.MediaTypeOptions.Images)
        || 'Images';

      const aspect: [number, number] = target === 'cover' ? [16, 9] : [1, 1];

      const result = await ImagePicker.launchCameraAsync({
        mediaTypes: MEDIA_IMAGES,
        allowsEditing: true,
        aspect,
        quality: 0.8,
        exif: false,
      });

      if (!result.canceled && result.assets[0]) {
        if (target === 'profile') {
          setProfileImage(result.assets[0].uri);
        } else {
          setCoverImage(result.assets[0].uri);
        }

        // Success animation
        Animated.sequence([
          Animated.spring(scaleAnim, {
            toValue: 1.05,
            tension: 100,
            friction: 3,
            useNativeDriver: true,
          }),
          Animated.spring(scaleAnim, {
            toValue: 1,
            tension: 100,
            friction: 8,
            useNativeDriver: true,
          }),
        ]).start();
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to take photo. Please try again.');
    } finally {
      setIsLoading(false);
    }
  }, [scaleAnim]);

  const openGallery = useCallback(async (target: 'profile' | 'cover') => {
    setIsLoading(true);
    try {
      const MEDIA_IMAGES = (ImagePicker.MediaType && ImagePicker.MediaType.Images)
        || (ImagePicker.MediaTypeOptions && ImagePicker.MediaTypeOptions.Images)
        || 'Images';

      const aspect: [number, number] = target === 'cover' ? [16, 9] : [1, 1];

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: MEDIA_IMAGES,
        allowsEditing: true,
        aspect,
        quality: 0.8,
        exif: false,
      });

      if (!result.canceled && result.assets[0]) {
        if (target === 'profile') {
          setProfileImage(result.assets[0].uri);
        } else {
          setCoverImage(result.assets[0].uri);
        }

        // Success animation
        Animated.sequence([
          Animated.spring(scaleAnim, {
            toValue: 1.05,
            tension: 100,
            friction: 3,
            useNativeDriver: true,
          }),
          Animated.spring(scaleAnim, {
            toValue: 1,
            tension: 100,
            friction: 8,
            useNativeDriver: true,
          }),
        ]).start();
      }
    } catch (error) {
      Alert.alert('Error', 'Failed to select image. Please try again.');
    } finally {
      setIsLoading(false);
    }
  }, [scaleAnim]);


  const handleContinue = useCallback(() => {
    const profileData: ProfileData = {
      profileImageUri: profileImage || undefined,
      coverImageUri: coverImage || undefined,
    };
    onComplete(profileData);
  }, [profileImage, coverImage, onComplete]);

  const handleSkip = useCallback(() => {
    Alert.alert(
      'Skip Profile Setup?',
      'You can always complete your profile later in the settings.',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Skip',
          style: 'destructive',
          onPress: onSkip
        }
      ]
    );
  }, [onSkip]);


  // Progress: 0 = nothing, 50 = one image, 100 = both
  const progressPct = (profileImage ? 50 : 0) + (coverImage ? 50 : 0);
  const progressLabel = profileImage && coverImage
    ? 'Both images added'
    : profileImage
      ? (isContractor ? 'Logo added — add a cover image' : 'Profile picture added — add a cover photo')
      : coverImage
        ? (isContractor ? 'Cover added — add your company logo' : 'Cover photo added — add a profile picture')
        : (isContractor ? 'Add your company logo and cover image (optional)' : 'Add a profile picture and cover photo (optional)');

  return (
    <SafeAreaView style={styles.container}>

      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity
            onPress={onBackPress}
            style={styles.backButton}
            activeOpacity={0.7}
          >
            <MaterialIcons name="arrow-back-ios" size={24} color="#1F2937" />
          </TouchableOpacity>
        </View>

        {/* Content */}
        <Animated.View
          style={[
            styles.content,
            {
              opacity: fadeAnim,
              transform: [{ scale: scaleAnim }]
            }
          ]}
        >
          {/* Title Section */}
          <View style={styles.titleContainer}>
            <Text style={styles.title}>Complete Your Profile</Text>
            <Text style={styles.subtitle}>
              {isContractor
                ? 'Add your company logo and cover image to make your profile stand out'
                : 'Add a profile picture and cover photo to personalise your account'}
            </Text>
          </View>

          {/* ─── Cover Photo Section ─── */}
          <View style={styles.coverSection}>
            <Text style={styles.sectionTitle}>
              {isContractor ? 'Cover Image' : 'Cover Photo'}
            </Text>

            <TouchableOpacity
              style={styles.coverContainer}
              onPress={() => selectImage('cover')}
              disabled={isLoading}
              activeOpacity={0.85}
            >
              {coverImage ? (
                <Image source={{ uri: coverImage }} style={styles.coverImage} resizeMode="cover" />
              ) : (
                <View style={styles.coverPlaceholder}>
                  <MaterialIcons name="panorama" size={40} color="#9CA3AF" />
                  <Text style={styles.coverPlaceholderText}>
                    Tap to add {isContractor ? 'cover image' : 'cover photo'}
                  </Text>
                </View>
              )}

              {/* Edit / Add icon */}
              <TouchableOpacity
                style={styles.coverEditButton}
                onPress={() => selectImage('cover')}
                disabled={isLoading}
                activeOpacity={0.8}
              >
                <MaterialIcons
                  name={coverImage ? 'edit' : 'add-a-photo'}
                  size={18}
                  color="#FFFFFF"
                />
              </TouchableOpacity>
            </TouchableOpacity>
          </View>

          {/* ─── Profile Picture / Company Logo Section ─── */}
          <View style={styles.profileContainer}>
            <Text style={styles.sectionTitle}>
              {isContractor ? 'Company Logo' : 'Profile Picture'}
            </Text>
            <View style={styles.profileCircleContainer}>
              <Animated.View
                style={[
                  styles.profileCircle,
                  { transform: [{ scale: scaleAnim }] }
                ]}
              >
                {profileImage ? (
                  <Image source={{ uri: profileImage }} style={styles.profileImage} />
                ) : (
                  <View style={styles.placeholderProfile}>
                    <MaterialIcons
                      name={isContractor ? 'business' : 'person'}
                      size={80}
                      color="#3B82F6"
                    />
                  </View>
                )}

                {/* Edit Button */}
                <TouchableOpacity
                  style={styles.editButton}
                  onPress={() => selectImage('profile')}
                  disabled={isLoading}
                  activeOpacity={0.8}
                >
                  <MaterialIcons
                    name={profileImage ? 'edit' : 'add-a-photo'}
                    size={20}
                    color="#FFFFFF"
                  />
                </TouchableOpacity>
              </Animated.View>

              <Text style={styles.imageHint}>
                {profileImage
                  ? 'Tap the edit button to change'
                  : `Tap the camera icon to add ${isContractor ? 'a logo' : 'a photo'}`}
              </Text>
            </View>
          </View>


          {/* Progress Indicator */}
          <View style={styles.progressContainer}>
            <View style={styles.progressBar}>
              <View
                style={[
                  styles.progressFill,
                  {
                    width: `${progressPct}%`
                  }
                ]}
              />
            </View>
            <Text style={styles.progressText}>
              {progressLabel}
            </Text>
          </View>
        </Animated.View>

      </ScrollView>

      {/* Action Buttons */}
      <View style={styles.buttonContainer}>
        <TouchableOpacity
          style={[
            styles.continueButton,
            (profileImage || coverImage) && styles.continueButtonActive
          ]}
          onPress={handleContinue}
          disabled={isLoading}
          activeOpacity={0.8}
        >
          <Text style={[
            styles.continueButtonText,
            (profileImage || coverImage) && styles.continueButtonTextActive
          ]}>
            Continue
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.skipButton}
          onPress={handleSkip}
          disabled={isLoading}
          activeOpacity={0.7}
        >
          <Text style={styles.skipButtonText}>Skip for Now</Text>
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FFFFFF',
  },
  scrollView: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 10,
    paddingBottom: 20,
  },
  backButton: {
    padding: 12,
    marginLeft: -12,
    borderRadius: 12,
  },
  content: {
    paddingHorizontal: 24,
  },
  titleContainer: {
    marginBottom: 40,
  },
  title: {
    fontSize: 32,
    fontWeight: '700',
    color: '#1F2937',
    marginBottom: 12,
    letterSpacing: -0.5,
  },
  subtitle: {
    fontSize: 16,
    color: '#6B7280',
    lineHeight: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#1F2937',
    marginBottom: 16,
  },
  coverSection: {
    marginBottom: 32,
  },
  coverContainer: {
    width: '100%',
    height: COVER_HEIGHT,
    borderRadius: 12,
    borderWidth: 2,
    borderColor: '#E5E7EB',
    overflow: 'hidden',
    backgroundColor: '#F9FAFB',
    position: 'relative',
  },
  coverImage: {
    width: '100%',
    height: '100%',
  },
  coverPlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F3F4F6',
  },
  coverPlaceholderText: {
    marginTop: 8,
    fontSize: 14,
    color: '#9CA3AF',
    fontWeight: '500',
  },
  coverEditButton: {
    position: 'absolute',
    bottom: 10,
    right: 10,
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: '#3B82F6',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#3B82F6',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.4,
    shadowRadius: 4,
    elevation: 6,
  },
  profileContainer: {
    marginBottom: 40,
  },
  profileCircleContainer: {
    alignItems: 'center',
  },
  profileCircle: {
    position: 'relative',
    width: 160,
    height: 160,
    borderRadius: 80,
    borderWidth: 4,
    borderColor: '#E5E7EB',
    overflow: 'hidden',
    backgroundColor: '#F9FAFB',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 4,
  },
  placeholderProfile: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F3F4F6',
  },
  profileImage: {
    width: '100%',
    height: '100%',
  },
  editButton: {
    position: 'absolute',
    bottom: 8,
    right: 8,
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#3B82F6',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#3B82F6',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.4,
    shadowRadius: 4,
    elevation: 6,
  },
  imageHint: {
    marginTop: 12,
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
  },
  dateContainer: {
    marginBottom: 40,
  },
  dateButton: {
    backgroundColor: '#F9FAFB',
    borderWidth: 2,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    padding: 16,
  },
  dateButtonFilled: {
    backgroundColor: '#EBF8FF',
    borderColor: '#3B82F6',
  },
  dateButtonContent: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  dateTextContainer: {
    marginLeft: 12,
    flex: 1,
  },
  dateText: {
    fontSize: 16,
    color: '#9CA3AF',
    fontWeight: '500',
  },
  dateTextFilled: {
    color: '#1F2937',
  },
  ageText: {
    fontSize: 14,
    color: '#6B7280',
    marginTop: 2,
  },
  progressContainer: {
    alignItems: 'center',
    marginBottom: 20,
  },
  progressBar: {
    width: '100%',
    height: 6,
    backgroundColor: '#E5E7EB',
    borderRadius: 3,
    overflow: 'hidden',
    marginBottom: 8,
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#3B82F6',
    borderRadius: 3,
  },
  progressText: {
    fontSize: 12,
    color: '#6B7280',
    fontWeight: '500',
  },
  buttonContainer: {
    paddingHorizontal: 24,
    paddingBottom: Platform.OS === 'ios' ? 34 : 24,
    backgroundColor: '#FFFFFF',
    borderTopWidth: 1,
    borderTopColor: '#F3F4F6',
  },
  continueButton: {
    backgroundColor: '#E5E7EB',
    borderRadius: 12,
    paddingVertical: 16,
    alignItems: 'center',
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 1,
    },
    shadowOpacity: 0.05,
    shadowRadius: 2,
    elevation: 1,
  },
  continueButtonActive: {
    backgroundColor: '#3B82F6',
    shadowColor: '#3B82F6',
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 4,
  },
  continueButtonText: {
    color: '#9CA3AF',
    fontSize: 16,
    fontWeight: '600',
  },
  continueButtonTextActive: {
    color: '#FFFFFF',
  },
  skipButton: {
    backgroundColor: 'transparent',
    borderRadius: 12,
    paddingVertical: 16,
    alignItems: 'center',
  },
  skipButtonText: {
    color: '#6B7280',
    fontSize: 16,
    fontWeight: '500',
  },
});