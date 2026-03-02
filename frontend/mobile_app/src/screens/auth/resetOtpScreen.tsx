// @ts-nocheck
import React, { useState, useRef, useEffect } from 'react';
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

interface ResetOtpScreenProps {
  email: string;
  on_back: () => void;
  on_verified: (email: string, reset_token: string) => void;
}

export default function ResetOtpScreen({ email, on_back, on_verified }: ResetOtpScreenProps) {
  const [otp_digits, set_otp_digits] = useState<string[]>(['', '', '', '', '', '']);
  const [is_loading, set_is_loading] = useState(false);
  const [is_resending, set_is_resending] = useState(false);
  const [error_message, set_error_message] = useState('');
  const [resend_countdown, set_resend_countdown] = useState(60);
  const input_refs = useRef<(TextInput | null)[]>([]);

  // Countdown timer for resend
  useEffect(() => {
    if (resend_countdown <= 0) return;
    const timer = setInterval(() => {
      set_resend_countdown((prev) => {
        if (prev <= 1) {
          clearInterval(timer);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
    return () => clearInterval(timer);
  }, [resend_countdown]);

  const handle_digit_change = (text: string, index: number) => {
    if (error_message) set_error_message('');

    const digit = text.replace(/[^0-9]/g, '');
    const new_digits = [...otp_digits];

    if (digit.length > 1) {
      // Handle paste — fill from current index
      const pasted = digit.split('');
      for (let i = 0; i < pasted.length && index + i < 6; i++) {
        new_digits[index + i] = pasted[i];
      }
      set_otp_digits(new_digits);
      const next_index = Math.min(index + pasted.length, 5);
      input_refs.current[next_index]?.focus();
    } else {
      new_digits[index] = digit;
      set_otp_digits(new_digits);
      if (digit && index < 5) {
        input_refs.current[index + 1]?.focus();
      }
    }
  };

  const handle_key_press = (e: any, index: number) => {
    if (e.nativeEvent.key === 'Backspace' && !otp_digits[index] && index > 0) {
      const new_digits = [...otp_digits];
      new_digits[index - 1] = '';
      set_otp_digits(new_digits);
      input_refs.current[index - 1]?.focus();
    }
  };

  const handle_verify = async () => {
    const otp = otp_digits.join('');
    if (otp.length !== 6) {
      set_error_message('Please enter the full 6-digit code.');
      return;
    }

    set_error_message('');
    set_is_loading(true);

    try {
      const response = await api_request(api_config.endpoints.auth.forgot_password_verify_otp, {
        method: 'POST',
        body: JSON.stringify({ email, otp }),
      });

      if (response.success && response.data?.reset_token) {
        on_verified(email, response.data.reset_token);
      } else {
        const msg = response.message || 'Invalid OTP. Please try again.';
        set_error_message(msg);
        // Clear the OTP fields on error
        set_otp_digits(['', '', '', '', '', '']);
        input_refs.current[0]?.focus();
      }
    } catch (error) {
      console.error('Verify OTP error:', error);
      set_error_message('Failed to connect to server. Please try again.');
    } finally {
      set_is_loading(false);
    }
  };

  const handle_resend = async () => {
    if (resend_countdown > 0 || is_resending) return;

    set_is_resending(true);
    set_error_message('');

    try {
      const response = await api_request(api_config.endpoints.auth.forgot_password_send_otp, {
        method: 'POST',
        body: JSON.stringify({ email }),
      });

      if (response.success) {
        Alert.alert('Code Sent', 'A new reset code has been sent to your email.');
        set_resend_countdown(60);
        set_otp_digits(['', '', '', '', '', '']);
        input_refs.current[0]?.focus();
      } else {
        set_error_message(response.message || 'Failed to resend code. Please try again.');
      }
    } catch (error) {
      console.error('Resend OTP error:', error);
      set_error_message('Failed to connect to server. Please try again.');
    } finally {
      set_is_resending(false);
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
          <Text style={styles.title}>Enter Reset Code</Text>
          <Text style={styles.subtitle}>
            We sent a 6-digit code to{' '}
            <Text style={styles.email_text}>{email}</Text>
          </Text>
        </View>

        {/* OTP Input */}
        <View style={styles.otp_container}>
          {otp_digits.map((digit, index) => (
            <TextInput
              key={index}
              ref={(ref) => (input_refs.current[index] = ref)}
              style={[
                styles.otp_input,
                digit ? styles.otp_input_filled : null,
                error_message ? styles.otp_input_error : null,
              ]}
              value={digit}
              onChangeText={(text) => handle_digit_change(text, index)}
              onKeyPress={(e) => handle_key_press(e, index)}
              keyboardType="number-pad"
              maxLength={6}
              selectTextOnFocus
              editable={!is_loading}
            />
          ))}
        </View>

        {error_message ? (
          <Text style={styles.error_text}>{error_message}</Text>
        ) : null}

        {/* Verify Button */}
        <View style={styles.button_container}>
          <TouchableOpacity
            style={[styles.submit_button, is_loading && styles.button_disabled]}
            onPress={handle_verify}
            disabled={is_loading}
          >
            {is_loading ? (
              <ActivityIndicator color="#FFFFFF" />
            ) : (
              <Text style={styles.submit_button_text}>Verify Code</Text>
            )}
          </TouchableOpacity>
        </View>

        {/* Resend */}
        <View style={styles.resend_container}>
          <Text style={styles.resend_text}>Didn't receive the code? </Text>
          {resend_countdown > 0 ? (
            <Text style={styles.resend_countdown}>Resend in {resend_countdown}s</Text>
          ) : (
            <TouchableOpacity onPress={handle_resend} disabled={is_resending}>
              <Text style={styles.resend_link}>
                {is_resending ? 'Sending...' : 'Resend Code'}
              </Text>
            </TouchableOpacity>
          )}
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
    marginBottom: 30,
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
  email_text: {
    fontWeight: '600',
    color: '#333333',
  },
  otp_container: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 10,
    marginBottom: 10,
  },
  otp_input: {
    width: 48,
    height: 56,
    borderWidth: 1.5,
    borderColor: '#E5E5E5',
    borderRadius: 12,
    fontSize: 24,
    fontWeight: '700',
    textAlign: 'center',
    color: '#333333',
    backgroundColor: '#FFFFFF',
  },
  otp_input_filled: {
    borderColor: '#EC7E00',
  },
  otp_input_error: {
    borderColor: '#DC3545',
  },
  error_text: {
    color: '#DC3545',
    fontSize: 13,
    textAlign: 'center',
    marginTop: 4,
    marginBottom: 10,
  },
  button_container: {
    marginTop: 20,
    marginBottom: 20,
  },
  submit_button: {
    backgroundColor: '#EC7E00',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
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
  resend_container: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    paddingBottom: 30,
  },
  resend_text: {
    fontSize: 15,
    color: '#666666',
  },
  resend_countdown: {
    fontSize: 15,
    color: '#999999',
  },
  resend_link: {
    fontSize: 15,
    color: '#EC7E00',
    fontWeight: '600',
  },
});
