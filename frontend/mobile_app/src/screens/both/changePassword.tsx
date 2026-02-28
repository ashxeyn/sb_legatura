// @ts-nocheck
import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ScrollView,
  ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons, Feather } from '@expo/vector-icons';
import { api_config, api_request } from '../../config/api';

interface ChangePasswordScreenProps {
  userData: any;
  onPasswordChanged: () => void;
  onLogout: () => void;
}

const COLORS = {
  primary: '#FB8C00',
  background: '#FAFBFC',
  surface: '#FFFFFF',
  text: '#0F172A',
  textSecondary: '#6B7280',
  border: '#E6E9EE',
  error: '#EF4444',
  success: '#16A34A',
};

/**
 * Password rules:
 * - At least 8 characters
 * - At least one uppercase letter
 * - At least one number
 * - At least one special character (!@#$%^&*(),.?":{}|<>)
 */
const PASSWORD_RULES = [
  { key: 'length', label: 'At least 8 characters', test: (p: string) => p.length >= 8 },
  { key: 'uppercase', label: 'At least one uppercase letter', test: (p: string) => /[A-Z]/.test(p) },
  { key: 'number', label: 'At least one number', test: (p: string) => /[0-9]/.test(p) },
  { key: 'special', label: 'At least one special character', test: (p: string) => /[!@#$%^&*(),.?":{}|<>]/.test(p) },
];

export default function ChangePasswordScreen({ userData, onPasswordChanged, onLogout }: ChangePasswordScreenProps) {
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const allRulesPassed = PASSWORD_RULES.every(rule => rule.test(newPassword));
  const passwordsMatch = newPassword === confirmPassword && confirmPassword.length > 0;
  const canSubmit = allRulesPassed && passwordsMatch && !isSubmitting;

  const handleSubmit = async () => {
    if (!canSubmit) return;

    setIsSubmitting(true);
    try {
      const userId = userData?.user_id || userData?.id;
      const response = await api_request(api_config.endpoints.auth.force_change_password, {
        method: 'POST',
        body: JSON.stringify({
          user_id: userId,
          new_password: newPassword,
          new_password_confirmation: confirmPassword,
        }),
      });

      if (response.success) {
        Alert.alert('Success', 'Your password has been changed successfully.', [
          { text: 'Continue', onPress: onPasswordChanged },
        ]);
      } else {
        Alert.alert('Error', response.message || 'Failed to change password. Please try again.');
      }
    } catch (error) {
      console.error('Change password error:', error);
      Alert.alert('Error', 'Failed to connect to server. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Header icon */}
        <View style={styles.iconContainer}>
          <View style={styles.iconCircle}>
            <Feather name="lock" size={40} color={COLORS.primary} />
          </View>
        </View>

        {/* Title */}
        <Text style={styles.title}>Change Your Password</Text>
        <Text style={styles.subtitle}>
          For your security, you must set a new password before continuing.
        </Text>

        {/* Password input */}
        <View style={styles.inputGroup}>
          <Text style={styles.label}>New Password *</Text>
          <View style={styles.inputWrapper}>
            <TextInput
              style={styles.input}
              value={newPassword}
              onChangeText={setNewPassword}
              placeholder="Enter new password"
              placeholderTextColor="#999"
              secureTextEntry={!showNewPassword}
              autoCapitalize="none"
              editable={!isSubmitting}
            />
            <TouchableOpacity
              style={styles.eyeIcon}
              onPress={() => setShowNewPassword(prev => !prev)}
            >
              <Ionicons name={showNewPassword ? 'eye-off' : 'eye'} size={22} color="#999" />
            </TouchableOpacity>
          </View>
        </View>

        {/* Password rules checklist */}
        <View style={styles.rulesContainer}>
          {PASSWORD_RULES.map(rule => {
            const passed = rule.test(newPassword);
            return (
              <View key={rule.key} style={styles.ruleRow}>
                <Ionicons
                  name={passed ? 'checkmark-circle' : 'ellipse-outline'}
                  size={18}
                  color={passed ? COLORS.success : COLORS.textSecondary}
                />
                <Text style={[styles.ruleText, passed && styles.ruleTextPassed]}>
                  {rule.label}
                </Text>
              </View>
            );
          })}
        </View>

        {/* Confirm password input */}
        <View style={styles.inputGroup}>
          <Text style={styles.label}>Confirm Password *</Text>
          <View style={styles.inputWrapper}>
            <TextInput
              style={styles.input}
              value={confirmPassword}
              onChangeText={setConfirmPassword}
              placeholder="Re-enter new password"
              placeholderTextColor="#999"
              secureTextEntry={!showConfirmPassword}
              autoCapitalize="none"
              editable={!isSubmitting}
            />
            <TouchableOpacity
              style={styles.eyeIcon}
              onPress={() => setShowConfirmPassword(prev => !prev)}
            >
c            </TouchableOpacity>
          </View>
          {confirmPassword.length > 0 && !passwordsMatch && (
            <Text style={styles.errorText}>Passwords do not match</Text>
          )}
          {passwordsMatch && (
            <Text style={styles.matchText}>Passwords match</Text>
          )}
        </View>

        {/* Submit button */}
        <TouchableOpacity
          style={[styles.submitButton, !canSubmit && styles.submitButtonDisabled]}
          onPress={handleSubmit}
          disabled={!canSubmit}
          activeOpacity={0.8}
        >
          {isSubmitting ? (
            <ActivityIndicator color="#FFF" size="small" />
          ) : (
            <Text style={styles.submitButtonText}>Change Password</Text>
          )}
        </TouchableOpacity>

        {/* Logout option */}
        <TouchableOpacity style={styles.logoutLink} onPress={onLogout}>
          <Text style={styles.logoutText}>Logout instead</Text>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: 24,
    paddingTop: 40,
    paddingBottom: 40,
  },
  iconContainer: {
    alignItems: 'center',
    marginBottom: 24,
  },
  iconCircle: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: '#FFF3E0',
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.text,
    textAlign: 'center',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textAlign: 'center',
    marginBottom: 32,
    lineHeight: 20,
  },
  inputGroup: {
    marginBottom: 16,
  },
  label: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
    marginBottom: 6,
  },
  inputWrapper: {
    position: 'relative',
  },
  input: {
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: 10,
    paddingHorizontal: 16,
    paddingVertical: 14,
    fontSize: 15,
    color: COLORS.text,
    paddingRight: 48,
  },
  eyeIcon: {
    position: 'absolute',
    right: 14,
    top: 14,
  },
  rulesContainer: {
    backgroundColor: COLORS.surface,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: COLORS.border,
    padding: 14,
    marginBottom: 20,
  },
  ruleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  ruleText: {
    fontSize: 13,
    color: COLORS.textSecondary,
    marginLeft: 8,
  },
  ruleTextPassed: {
    color: COLORS.success,
  },
  errorText: {
    color: COLORS.error,
    fontSize: 12,
    marginTop: 4,
    marginLeft: 4,
  },
  matchText: {
    color: COLORS.success,
    fontSize: 12,
    marginTop: 4,
    marginLeft: 4,
  },
  submitButton: {
    backgroundColor: COLORS.primary,
    borderRadius: 12,
    paddingVertical: 16,
    alignItems: 'center',
    marginTop: 8,
  },
  submitButtonDisabled: {
    backgroundColor: '#D1D5DB',
  },
  submitButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '700',
  },
  logoutLink: {
    alignItems: 'center',
    marginTop: 20,
  },
  logoutText: {
    fontSize: 14,
    color: COLORS.textSecondary,
    textDecorationLine: 'underline',
  },
});
