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
import { Ionicons } from '@expo/vector-icons';
import { api_config, api_request } from '../../config/api';

interface ForgotPasswordScreenProps {
  on_back: () => void;
  on_otp_sent: (email: string) => void;
}

export default function ForgotPasswordScreen({ on_back, on_otp_sent }: ForgotPasswordScreenProps) {
  const [email, set_email] = useState('');
  const [is_loading, set_is_loading] = useState(false);
  const [field_error, set_field_error] = useState('');

  const handle_send_otp = async () => {
    // Clear previous error
    set_field_error('');

    // Local validation
    const trimmed = email.trim();
    if (!trimmed) {
      set_field_error('Please enter your email address.');
      return;
    }

    const email_regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email_regex.test(trimmed)) {
      set_field_error('Please enter a valid email address.');
      return;
    }

    set_is_loading(true);

    try {
      const response = await api_request(api_config.endpoints.auth.forgot_password_send_otp, {
        method: 'POST',
        body: JSON.stringify({ email: trimmed }),
      });

      if (response.success) {
        Alert.alert('Code Sent', 'A 6-digit reset code has been sent to your email.', [
          { text: 'OK', onPress: () => on_otp_sent(trimmed) },
        ]);
      } else {
        // Show server error inline
        const msg = response.message || 'Failed to send reset code. Please try again.';
        set_field_error(msg);
      }
    } catch (error) {
      console.error('Send OTP error:', error);
      set_field_error('Failed to connect to server. Please try again.');
    } finally {
      set_is_loading(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.scroll_content} showsVerticalScrollIndicator={false}>
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
          <Text style={styles.title}>Forgot Password</Text>
          <Text style={styles.subtitle}>
            Enter your email address and we'll send you a code to reset your password.
          </Text>
        </View>

        {/* Form */}
        <View style={styles.form_container}>
          <View style={styles.input_container}>
            <Text style={styles.label}>Email Address *</Text>
            <TextInput
              style={[styles.input, field_error ? styles.input_error : null]}
              value={email}
              onChangeText={(text) => {
                set_email(text);
                if (field_error) set_field_error('');
              }}
              placeholder="Enter your email address"
              placeholderTextColor="#999"
              autoCapitalize="none"
              autoCorrect={false}
              keyboardType="email-address"
              editable={!is_loading}
            />
            {field_error ? (
              <Text style={styles.error_text}>{field_error}</Text>
            ) : null}
          </View>

          <TouchableOpacity
            style={[styles.submit_button, is_loading && styles.button_disabled]}
            onPress={handle_send_otp}
            disabled={is_loading}
          >
            {is_loading ? (
              <ActivityIndicator color="#FFFFFF" />
            ) : (
              <Text style={styles.submit_button_text}>Send Reset Code</Text>
            )}
          </TouchableOpacity>
        </View>

        {/* Footer */}
        <View style={styles.footer}>
          <Text style={styles.footer_text}>
            Remember your password?{' '}
            <Text style={styles.link_text} onPress={on_back}>
              Back to Login
            </Text>
          </Text>
        </View>
      </ScrollView>
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
  },
  input_error: {
    borderColor: '#DC3545',
  },
  error_text: {
    color: '#DC3545',
    fontSize: 13,
    marginTop: 4,
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
});
