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
  ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import KeyboardAwareScrollView from '../../components/KeyboardAwareScrollView';
import { Ionicons } from '@expo/vector-icons';
import { api_config, api_request } from '../../config/api';

interface ResetPasswordScreenProps {
  email: string;
  reset_token: string;
  on_back: () => void;
  on_success: () => void;
}

export default function ResetPasswordScreen({
  email,
  reset_token,
  on_back,
  on_success,
}: ResetPasswordScreenProps) {
  const [password, set_password] = useState('');
  const [password_confirmation, set_password_confirmation] = useState('');
  const [show_password, set_show_password] = useState(false);
  const [show_confirm, set_show_confirm] = useState(false);
  const [is_loading, set_is_loading] = useState(false);
  const [field_errors, set_field_errors] = useState<Record<string, string>>({});

  // Password strength checks
  const password_checks = {
    min_length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    number: /[0-9]/.test(password),
    special: /[!@#$%^&*(),.?":{}|<>]/.test(password),
  };

  const all_checks_passed =
    password_checks.min_length &&
    password_checks.uppercase &&
    password_checks.number &&
    password_checks.special;

  const handle_reset = async () => {
    const errors: Record<string, string> = {};

    if (!password) {
      errors.password = 'Please enter a new password.';
    } else if (!all_checks_passed) {
      errors.password = 'Password does not meet all requirements.';
    }

    if (!password_confirmation) {
      errors.password_confirmation = 'Please confirm your password.';
    } else if (password !== password_confirmation) {
      errors.password_confirmation = 'Passwords do not match.';
    }

    if (Object.keys(errors).length > 0) {
      set_field_errors(errors);
      return;
    }

    set_field_errors({});
    set_is_loading(true);

    try {
      const response = await api_request(api_config.endpoints.auth.forgot_password_reset, {
        method: 'POST',
        body: JSON.stringify({
          email,
          reset_token,
          password,
          password_confirmation,
        }),
      });

      if (response.success) {
        Alert.alert('Success', 'Your password has been reset successfully!', [
          { text: 'OK', onPress: () => on_success() },
        ]);
      } else {
        // Handle server validation errors
        if (response.data?.errors) {
          const server_errors: Record<string, string> = {};
          for (const [key, messages] of Object.entries(response.data.errors)) {
            server_errors[key] = Array.isArray(messages) ? messages[0] : String(messages);
          }
          set_field_errors(server_errors);
        } else {
          const msg = response.message || 'Failed to reset password. Please try again.';
          // If it's a token-related error, show as alert since there's no field for it
          if (msg.toLowerCase().includes('expired') || msg.toLowerCase().includes('invalid')) {
            Alert.alert('Session Expired', msg + '\n\nPlease start over.', [
              { text: 'OK', onPress: () => on_back() },
            ]);
          } else {
            set_field_errors({ password: msg });
          }
        }
      }
    } catch (error) {
      console.error('Reset password error:', error);
      Alert.alert('Error', 'Failed to connect to server. Please try again.');
    } finally {
      set_is_loading(false);
    }
  };

  const render_check = (label: string, passed: boolean) => (
    <View style={styles.check_row} key={label}>
      <Ionicons
        name={passed ? 'checkmark-circle' : 'ellipse-outline'}
        size={18}
        color={passed ? '#28A745' : '#999999'}
      />
      <Text style={[styles.check_text, passed && styles.check_text_passed]}>{label}</Text>
    </View>
  );

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
            source={require('../../../assets/images/logos/legatura-logo.png')}
            style={styles.logo}
            resizeMode="contain"
          />
        </View>

        {/* Title */}
        <View style={styles.title_container}>
          <Text style={styles.title}>Reset Password</Text>
          <Text style={styles.subtitle}>Create a strong new password for your account.</Text>
        </View>

        {/* Form */}
        <View style={styles.form_container}>
          {/* New Password */}
          <View style={styles.input_container}>
            <Text style={styles.label}>New Password *</Text>
            <View style={{ position: 'relative' }}>
              <TextInput
                style={[styles.input, field_errors.password ? styles.input_error : null]}
                value={password}
                onChangeText={(text) => {
                  set_password(text);
                  if (field_errors.password) {
                    const { password: _, ...rest } = field_errors;
                    set_field_errors(rest);
                  }
                }}
                placeholder="Enter your new password"
                placeholderTextColor="#999"
                secureTextEntry={!show_password}
                editable={!is_loading}
              />
              <TouchableOpacity
                style={styles.eye_icon}
                onPress={() => set_show_password((prev) => !prev)}
                disabled={is_loading}
              >
                <Ionicons
                  name={show_password ? 'eye-off' : 'eye'}
                  size={22}
                  color="#999"
                />
              </TouchableOpacity>
            </View>
            {field_errors.password ? (
              <Text style={styles.error_text}>{field_errors.password}</Text>
            ) : null}
          </View>

          {/* Password Requirements */}
          {password.length > 0 && (
            <View style={styles.requirements_container}>
              {render_check('At least 8 characters', password_checks.min_length)}
              {render_check('At least 1 uppercase letter', password_checks.uppercase)}
              {render_check('At least 1 number', password_checks.number)}
              {render_check('At least 1 special character', password_checks.special)}
            </View>
          )}

          {/* Confirm Password */}
          <View style={styles.input_container}>
            <Text style={styles.label}>Confirm Password *</Text>
            <View style={{ position: 'relative' }}>
              <TextInput
                style={[styles.input, field_errors.password_confirmation ? styles.input_error : null]}
                value={password_confirmation}
                onChangeText={(text) => {
                  set_password_confirmation(text);
                  if (field_errors.password_confirmation) {
                    const { password_confirmation: _, ...rest } = field_errors;
                    set_field_errors(rest);
                  }
                }}
                placeholder="Re-enter your new password"
                placeholderTextColor="#999"
                secureTextEntry={!show_confirm}
                editable={!is_loading}
              />
              <TouchableOpacity
                style={styles.eye_icon}
                onPress={() => set_show_confirm((prev) => !prev)}
                disabled={is_loading}
              >
                <Ionicons
                  name={show_confirm ? 'eye-off' : 'eye'}
                  size={22}
                  color="#999"
                />
              </TouchableOpacity>
            </View>
            {field_errors.password_confirmation ? (
              <Text style={styles.error_text}>{field_errors.password_confirmation}</Text>
            ) : null}
          </View>

          <TouchableOpacity
            style={[styles.submit_button, is_loading && styles.button_disabled]}
            onPress={handle_reset}
            disabled={is_loading}
          >
            {is_loading ? (
              <ActivityIndicator color="#FFFFFF" />
            ) : (
              <Text style={styles.submit_button_text}>Reset Password</Text>
            )}
          </TouchableOpacity>
        </View>
      </KeyboardAwareScrollView>
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
    lineHeight: 24,
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
    paddingRight: 44,
  },
  input_error: {
    borderColor: '#DC3545',
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
  error_text: {
    color: '#DC3545',
    fontSize: 13,
    marginTop: 4,
  },
  requirements_container: {
    backgroundColor: '#F8F9FA',
    borderRadius: 10,
    padding: 14,
    marginBottom: 20,
  },
  check_row: {
    flexDirection: 'row',
    alignItems: 'center',
    marginVertical: 3,
  },
  check_text: {
    fontSize: 14,
    color: '#999999',
    marginLeft: 8,
  },
  check_text_passed: {
    color: '#28A745',
  },
  submit_button: {
    backgroundColor: '#EC7E00',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
    marginTop: 20,
    shadowColor: '#EC7E00',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  submit_button_text: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: '600',
  },
  button_disabled: {
    opacity: 0.6,
  },
});
