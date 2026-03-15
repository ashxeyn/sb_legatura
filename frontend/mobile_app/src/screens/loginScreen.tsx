// @ts-nocheck
import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  Image,
  ScrollView,
  Modal
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons, Feather } from '@expo/vector-icons';
import { auth_service, login_data } from '../services/auth_service';
import { storage_service } from '../utils/storage';
import KeyboardAwareScrollView from '../components/KeyboardAwareScrollView';

interface LoginScreenProps {
  on_back: () => void;
  on_login_success: (userData?: any) => void;
  on_signup: () => void;
  on_forgot_password?: () => void;
  on_resubmit_documents?: (userData: any, resubmission: any[]) => void;
}


export default function LoginScreen({ on_back, on_login_success, on_signup, on_forgot_password, on_resubmit_documents }: LoginScreenProps) {
  const [username, set_username] = useState('');
  const [password, set_password] = useState('');
  const [is_loading, set_is_loading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [resubmitModal, setResubmitModal] = useState<{ visible: boolean; userData: any; resubmission: any[] }>({ visible: false, userData: null, resubmission: [] });

  const handle_login = async () => {
    setFieldErrors({});

    if (!username.trim() || !password.trim()) {
      const errs: Record<string, string> = {};
      if (!username.trim()) errs.username = 'Username is required.';
      if (!password.trim()) errs.password = 'Password is required.';
      setFieldErrors(errs);
      return;
    }

    set_is_loading(true);

    try {
      const login_credentials: login_data = {
        username: username.trim(),
        password: password.trim(),
      };

      console.log('Attempting login with:', login_credentials);
      const response = await auth_service.login(login_credentials);

      console.log('Login response:', response);
      console.log('Login response.data:', response.data);
      console.log('Login response.data?.user:', response.data?.user);

      if (response.success) {
        // Extract user data from response
        const userData = response.data?.user || response.data;
        // Save token if provided
        const token = response.data?.token || null;
        if (token) {
          try {
            await storage_service.save_auth_token(token);
            console.log('Auth token saved');
          } catch (e) {
            console.warn('Failed to save auth token:', e);
          }
        }

        // Include contractor_member context if present (for contractor users including staff)
        if (response.data?.contractor_member) {
          userData.contractor_member = response.data.contractor_member;
          console.log('Contractor member context included:', response.data.contractor_member);
        }

        // Include determinedRole if present
        if (response.data?.determinedRole) {
          userData.determinedRole = response.data.determinedRole;
        }

        // Include must_change_password flag if present
        if (response.data?.must_change_password) {
          userData.must_change_password = true;
        }

        // Check if the user needs to resubmit verification documents
        if (response.data?.requires_resubmission && response.data?.resubmission?.length > 0) {
          console.log('User requires document resubmission:', response.data.resubmission);
          setResubmitModal({ visible: true, userData, resubmission: response.data.resubmission });
          return;
        }

        console.log('Extracted userData:', userData);
        console.log('userData.profile_pic:', userData?.profile_pic);
        Alert.alert('Success', 'Login successful!', [
          { text: 'OK', onPress: () => on_login_success(userData) }
        ]);
      } else {
        // Show inline field errors if returned by the server
        const serverErrors = response.data?.errors || response.errors || null;
        if (serverErrors) {
          const errs: Record<string, string> = {};
          if (serverErrors.username) errs.username = Array.isArray(serverErrors.username) ? serverErrors.username[0] : serverErrors.username;
          if (serverErrors.password) errs.password = Array.isArray(serverErrors.password) ? serverErrors.password[0] : serverErrors.password;
          if (Object.keys(errs).length > 0) {
            setFieldErrors(errs);
          } else {
            Alert.alert('Error', response.message || 'Login failed. Please check your credentials.');
          }
        } else {
          Alert.alert('Error', response.message || 'Login failed. Please check your credentials.');
        }
      }
    } catch (error) {
      console.error('Login error:', error);
      Alert.alert('Error', 'Failed to connect to server. Please try again.');
    } finally {
      set_is_loading(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <KeyboardAwareScrollView contentContainerStyle={styles.scroll_content} showsVerticalScrollIndicator={false}>
        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity onPress={on_back} style={styles.back_button}>
            <Ionicons name="chevron-back" size={28} color="#333333" />
          </TouchableOpacity>
        </View>

        {/* Logo */}
        <View style={styles.logo_container}>
          <Image
            source={require('../../assets/images/logos/legatura-logo.png')}
            style={styles.logo}
            resizeMode="contain"
          />
        </View>

        {/* Title */}
        <View style={styles.title_container}>
          <Text style={styles.title}>Login to Legatura</Text>
          <Text style={styles.subtitle}>Welcome back! Please enter your details.</Text>
        </View>

        {/* Form */}
        <View style={styles.form_container}>
          <View style={styles.input_container}>
            <Text style={styles.label}>Username or Email *</Text>
            <TextInput
              style={[styles.input, fieldErrors.username && styles.inputError]}
              value={username}
              onChangeText={(text) => { set_username(text); setFieldErrors(prev => { const { username, ...rest } = prev; return rest; }); }}
              placeholder="Enter your username or email"
              placeholderTextColor="#999"
              autoCapitalize="none"
              autoCorrect={false}
              editable={!is_loading}
            />
            {fieldErrors.username && <Text style={styles.fieldErrorText}>{fieldErrors.username}</Text>}
          </View>

          <View style={styles.input_container}>
            <Text style={styles.label}>Password *</Text>
            <View style={{ position: 'relative' }}>
              <TextInput
                style={[styles.input, fieldErrors.password && styles.inputError]}
                value={password}
                onChangeText={(text) => { set_password(text); setFieldErrors(prev => { const { password, ...rest } = prev; return rest; }); }}
                placeholder="Enter your password"
                placeholderTextColor="#999"
                secureTextEntry={!showPassword}
                editable={!is_loading}
              />
              <TouchableOpacity
                style={styles.eye_icon}
                onPress={() => setShowPassword((prev) => !prev)}
                disabled={is_loading}
              >
                <Ionicons
                  name={showPassword ? 'eye-off' : 'eye'}
                  size={22}
                  color="#999"
                />
              </TouchableOpacity>
            </View>
            {fieldErrors.password && <Text style={styles.fieldErrorText}>{fieldErrors.password}</Text>}
          </View>

          {on_forgot_password && (
            <TouchableOpacity onPress={on_forgot_password} style={styles.forgot_password_link}>
              <Text style={styles.forgot_password_text}>Forgot Password?</Text>
            </TouchableOpacity>
          )}

          <TouchableOpacity
            style={[styles.login_button, is_loading && styles.button_disabled]}
            onPress={handle_login}
            disabled={is_loading}
          >
            <Text style={styles.login_button_text}>
              {is_loading ? 'Logging in...' : 'Login'}
            </Text>
          </TouchableOpacity>
        </View>

        {/* Footer */}
        <View style={styles.footer}>
          <Text style={styles.footer_text}>
            Don't have an account?{' '}
            <Text style={styles.link_text} onPress={on_signup}>Sign up here</Text>
          </Text>
        </View>
      </KeyboardAwareScrollView>

      {/* Resubmission Modal */}
      <Modal
        visible={resubmitModal.visible}
        transparent
        animationType="fade"
        onRequestClose={() => setResubmitModal({ visible: false, userData: null, resubmission: [] })}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            {/* Modal header */}
            <View style={styles.modalHeader}>
              <View style={styles.modalHeaderIcon}>
                <Feather name="alert-triangle" size={20} color="#F59E0B" />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={styles.modalTitle}>Resubmission Required</Text>
                <Text style={styles.modalSubtitle}>Your verification documents were rejected</Text>
              </View>
              <TouchableOpacity
                onPress={() => setResubmitModal({ visible: false, userData: null, resubmission: [] })}
                style={styles.modalCloseBtn}
              >
                <Feather name="x" size={20} color="#64748B" />
              </TouchableOpacity>
            </View>
            <View style={styles.modalDivider} />
            {/* Reason boxes */}
            {resubmitModal.resubmission.map((item: any, index: number) => (
              <View key={index} style={styles.modalReasonBox}>
                <Text style={styles.modalRoleLabel}>
                  {item.role === 'property_owner' ? 'Property Owner' : 'Contractor'}
                </Text>
                <Text style={styles.modalReasonText}>{item.reason}</Text>
              </View>
            ))}
            {/* Action buttons */}
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.modalCancelButton}
                onPress={() => setResubmitModal({ visible: false, userData: null, resubmission: [] })}
              >
                <Text style={styles.modalCancelButtonText}>Later</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={styles.modalResubmitButton}
                onPress={() => {
                  setResubmitModal({ visible: false, userData: null, resubmission: [] });
                  if (on_resubmit_documents) {
                    on_resubmit_documents(resubmitModal.userData, resubmitModal.resubmission);
                  }
                }}
              >
                <Feather name="upload-cloud" size={16} color="#FFFFFF" style={{ marginRight: 6 }} />
                <Text style={styles.modalResubmitButtonText}>Upload Now</Text>
              </TouchableOpacity>
            </View>
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
  scroll_content: {
    flexGrow: 1,
    paddingHorizontal: 30,
  },
  header: {
    paddingTop: 20,
    paddingBottom: 10,
    flexDirection: 'row',
    alignItems: 'center',
  },
  back_button: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'transparent',
    alignItems: 'center',
    justifyContent: 'center',
  },
  logo_container: {
    alignItems: 'center',
    marginTop: 40,
    marginBottom: 40,
  },
  logo: {
    width: 200,
    height: 60,
  },
  title_container: {
    alignItems: 'center',
    marginBottom: 40,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333333',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    color: '#666666',
    textAlign: 'center',
  },
  form_container: {
    marginBottom: 40,
  },
  input_container: {
    marginBottom: 20,
  },
  label: {
    fontSize: 16,
    fontWeight: '500',
    color: '#333333',
    marginBottom: 8,
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
    paddingRight: 44, // space for eye icon
  },
  inputError: {
    borderColor: '#EF4444',
  },
  fieldErrorText: {
    color: '#EF4444',
    fontSize: 13,
    marginTop: 4,
    marginLeft: 4,
  },
  eye_icon: {
    position: 'absolute',
    right: 12,
    top: 0,
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
    width: 32,
  },
  forgot_password_link: {
    alignSelf: 'flex-end',
    marginTop: 4,
    marginBottom: 4,
  },
  forgot_password_text: {
    color: '#EC7E00',
    fontSize: 14,
    fontWeight: '500',
  },
  login_button: {
    backgroundColor: '#EC7E00',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
    marginTop: 20,
    shadowColor: '#EC7E00',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  login_button_text: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: '600',
  },
  button_disabled: {
    opacity: 0.6,
  },
  footer: {
    alignItems: 'center',
    paddingBottom: 30,
  },
  footer_text: {
    fontSize: 16,
    color: '#666666',
  },
  link_text: {
    color: '#EC7E00',
    fontWeight: '600',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  modalContent: {
    backgroundColor: '#FFFFFF',
    borderRadius: 6,
    width: '100%',
    maxWidth: 400,
    overflow: 'hidden',
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    padding: 20,
  },
  modalHeaderIcon: {
    width: 36,
    height: 36,
    borderRadius: 8,
    backgroundColor: '#FEF3C7',
    alignItems: 'center',
    justifyContent: 'center',
  },
  modalTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1E3A5F',
  },
  modalSubtitle: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 1,
  },
  modalCloseBtn: {
    width: 32,
    height: 32,
    alignItems: 'center',
    justifyContent: 'center',
  },
  modalDivider: {
    height: 1,
    backgroundColor: '#E2E8F0',
  },
  modalReasonBox: {
    marginHorizontal: 20,
    marginTop: 16,
    backgroundColor: '#F1F5F9',
    borderRadius: 4,
    padding: 12,
    borderLeftWidth: 3,
    borderLeftColor: '#F59E0B',
  },
  modalRoleLabel: {
    fontSize: 11,
    fontWeight: '700',
    color: '#64748B',
    marginBottom: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },
  modalReasonText: {
    fontSize: 14,
    color: '#1E3A5F',
    lineHeight: 20,
  },
  modalActions: {
    flexDirection: 'row',
    gap: 12,
    padding: 20,
    paddingTop: 16,
  },
  modalResubmitButton: {
    flex: 1,
    backgroundColor: '#EC7E00',
    borderRadius: 8,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
  },
  modalResubmitButtonText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '700',
  },
  modalCancelButton: {
    flex: 1,
    borderRadius: 8,
    paddingVertical: 14,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: '#E2E8F0',
  },
  modalCancelButtonText: {
    color: '#64748B',
    fontSize: 15,
    fontWeight: '700',
  },
});
