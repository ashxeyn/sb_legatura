// @ts-nocheck
// View Profile Screen for both Property Owners and Contractors - eme lang to carl
import React from 'react';
import {
  View,
  Text,
  Image,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  StatusBar,
} from 'react-native';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

interface ViewProfileProps {
  onBack: () => void;
  userData?: {
    username?: string;
    email?: string;
    profile_pic?: string;
    cover_photo?: string;
    user_type?: string;
  };
}

export default function ViewProfileScreen({ onBack, userData }: ViewProfileProps) {
  const insets = useSafeAreaInsets();

  // Debug: log incoming image URIs
  console.log('ViewProfileScreen - profile_pic URI:', userData?.profile_pic);
  console.log('ViewProfileScreen - cover_photo URI:', userData?.cover_photo);

  // Ensure we always use an object for user data to avoid accidentally rendering
  // a plain string as a child (which causes the "Text strings must be rendered within a <Text> component" error).
  const ud: NonNullable<ViewProfileProps['userData']> = (userData && typeof userData === 'object')
    ? userData
    : { username: typeof userData === 'string' ? userData : undefined };

  const getInitials = (name: string) => {
    return name ? name.substring(0, 2).toUpperCase() : 'PO';
  };

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar hidden={true} />
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={onBack} style={styles.backButton}>
            <Ionicons name="chevron-back" size={28} color="#333333" />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>View Profile</Text>
          <View style={{ width: 40 }} /> {/* Spacer for balance */}
        </View>

        {/* Cover Photo */}
        <View style={styles.coverPhotoContainer}>
          {ud?.cover_photo ? (
            <Image
              source={{ uri: ud.cover_photo }}
              style={styles.coverPhoto}
              resizeMode="cover"
            />
          ) : (
            <View style={styles.coverPhotoPlaceholder}>
              <MaterialIcons name="photo-camera" size={30} color="#FFFFFF" />
            </View>
          )}
        </View>

        {/* Profile Info */}
        <View style={styles.profileInfoContainer}>
          <View style={styles.avatarContainer}>
            {ud?.profile_pic ? (
              <Image
                source={{ uri: ud.profile_pic }}
                style={styles.avatar}
                resizeMode="cover"
              />
            ) : (
              <View style={styles.avatarPlaceholder}>
                <Text style={styles.avatarText}>{getInitials(ud?.username || 'U')}</Text>
              </View>
            )}
          </View>

          <Text style={styles.username}>{ud?.username || 'Property Owner'}</Text>
          <Text style={styles.email}>{ud?.email || 'user@example.com'}</Text>

          <View style={styles.userTypeBadge}>
            <MaterialIcons name="home" size={14} color="#EC7E00" />
            <Text style={styles.userTypeText}>
              {ud?.user_type === 'contractor' ? 'Contractor' : 'Property Owner'}
            </Text>
          </View>
        </View>

        {/* Details Card */}
        <View style={styles.detailsCard}>
          <Text style={styles.sectionTitle}>Account Details</Text>
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Username:</Text>
            <Text style={styles.detailValue}>{ud?.username || 'N/A'}</Text>
          </View>
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Email:</Text>
            <Text style={styles.detailValue}>{ud?.email || 'N/A'}</Text>
          </View>
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Account Type:</Text>
            <Text style={styles.detailValue}>
              {ud?.user_type === 'contractor' ? 'Contractor' : 'Property Owner'}
            </Text>
          </View>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#FEFEFE',
  },
  scrollContent: {
    paddingBottom: 40,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 16,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E5E5',
  },
  backButton: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333333',
  },
  coverPhotoContainer: {
    height: 140,
    backgroundColor: '#EC7E00',
  },
  coverPhoto: {
    width: '100%',
    height: '100%',
  },
  coverPhotoPlaceholder: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  profileInfoContainer: {
    alignItems: 'center',
    marginTop: -50,
    marginBottom: 16,
  },
  avatarContainer: {
    position: 'relative',
  },
  avatar: {
    width: 100,
    height: 100,
    borderRadius: 50,
    borderWidth: 4,
    borderColor: '#FFFFFF',
  },
  avatarPlaceholder: {
    width: 100,
    height: 100,
    borderRadius: 50,
    backgroundColor: '#EC7E00',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 4,
    borderColor: '#FFFFFF',
  },
  avatarText: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  username: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#333333',
    marginTop: 10,
  },
  email: {
    fontSize: 14,
    color: '#777777',
    marginTop: 4,
  },
  userTypeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#FFF5EB',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 20,
    marginTop: 10,
  },
  userTypeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#EC7E00',
    marginLeft: 5,
  },
  detailsCard: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    borderRadius: 12,
    padding: 16,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  sectionTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#333333',
    marginBottom: 12,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  detailLabel: {
    fontSize: 15,
    color: '#666666',
  },
  detailValue: {
    fontSize: 15,
    fontWeight: '500',
    color: '#333333',
  },
});
