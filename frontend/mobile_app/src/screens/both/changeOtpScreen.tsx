// @ts-nocheck
import React, { useEffect, useState, useRef, useCallback, useMemo } from 'react';
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
import { api_request } from '../../config/api';
import { useNavigation } from '@react-navigation/native';
import { storage_service } from '../../utils/storage';


interface ChangeOtpScreenProps {
  token?: string;
  purpose: 'change_email' | 'change_contact' | 'change_password';
  onSuccess?: () => void;
  onBack?: () => void;
}

/**
 * Password rules:
 * - At least 8 characters
 * - At least one uppercase letter
 * - At least one number
 * - At least one special character (!@#$%^&*(),.?":{}|<>)
 */

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

export default function ChangeOtpScreen({ token, purpose = 'change_email', onSuccess, onBack }: ChangeOtpScreenProps) {
  const [newValue, setNewValue] = useState('');
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [selectedPurpose, setSelectedPurpose] = useState(purpose);
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [otp, setOtp] = useState('');
  const [otpSent, setOtpSent] = useState(false);
  const [maskedDest, setMaskedDest] = useState<string | null>(null);
  const [otpToken, setOtpToken] = useState<string | null>(null);
  const [secondsLeft, setSecondsLeft] = useState<number | null>(null);
  const [currentPhone, setCurrentPhone] = useState<string | null>(null);
  const [activeRole, setActiveRole] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const timerRef = useRef<number | null>(null);
  let navigation: any = null;
  try {
    // may throw if not inside a NavigationContainer
    navigation = useNavigation();
  } catch (e) {
    navigation = null;
  }

  const activePurpose = selectedPurpose;

  // Label and titles
  const getTitle = () => {
    if (activePurpose === 'change_email') return 'Change Email';
    if (activePurpose === 'change_contact') return 'Change Contact Number';
    return 'Change Password';
  };

  const getSubtitle = () => {
    if (activePurpose === 'change_email') return 'We will send an OTP to your new email to confirm the change.';
    if (activePurpose === 'change_contact') return 'We will send an OTP to your new contact number to confirm the change.';
    return 'We will send an OTP to your email to confirm the change.';
  };

  // Send OTP
  const handleSendOtp = async () => {
    try {
      setIsSubmitting(true);
      // For password change: validate new password and confirm match before sending OTP
      if (activePurpose === 'change_password') {
        if (!newPassword || !confirmPassword) {
          Alert.alert('Error', 'Please enter and confirm your new password before sending OTP.');
          return;
        }
        if (newPassword !== confirmPassword) {
          Alert.alert('Error', 'Passwords do not match');
          return;
        }
        // ensure email is available for destination display
        if (!newValue) {
          try {
            const stored = await storage_service.get_user_data();
            if (stored && stored.email) setNewValue(stored.email);
          } catch (e) { console.warn('Could not auto-fill email before sending OTP:', e); }
        }
        if (!newValue) {
          Alert.alert('Error', 'Registered email not available');
          return;
        }
      } else {
        if (!newValue) {
          Alert.alert('Error', 'Please provide a destination before sending OTP.');
          return;
        }
      }

      // For email change, include current password validation (sent to server for verification)
      let sendBody: any = { purpose: activePurpose, new_value: newValue };
      if (activePurpose === 'change_email') {
        if (!currentPassword) {
          Alert.alert('Error', 'Please re-enter your current password to continue.');
          return;
        }
        sendBody.current_password = currentPassword;
      }

      // For contact change: send OTP to user's registered email (email-delivered OTP)
      if (activePurpose === 'change_contact') {
        try {
          const stored = await storage_service.get_user_data();
          const userEmail = stored?.email || stored?.user_email || null;
          if (userEmail) sendBody.destination = userEmail;
        } catch (e) { console.warn('Could not read stored user email for OTP delivery:', e); }
      }

      const res = await api_request('/api/change-otp/send', {
        method: 'POST',
        body: JSON.stringify(sendBody),
      });

      if (!res.success) {
        if (res.status === 429) {
          Alert.alert('Rate limit', res.message || 'Too many requests');
          return;
        }
        // If this is an email-change flow, surface password errors more clearly
        if (activePurpose === 'change_email' && (res.status === 422 || /password/i.test(res.message || ''))) {
          Alert.alert('Incorrect password', res.message || 'The password you entered is incorrect.');
          return;
        }
        throw new Error(res.message || 'Failed to send OTP');
      }

      const masked = res.data?.masked ?? null;
      setMaskedDest(masked);
      const otpToken = res.data?.otp_token ?? null;
      const ttl = res.data?.ttl_seconds ?? 900;
      // Prepare payload for app-level verification screen
      try {
        // @ts-ignore
        global.change_otp_verify_payload = {
          email: masked ?? newValue,
          otpToken,
          purpose: activePurpose,
          newValue: activePurpose === 'change_password' ? newPassword : newValue,
          ttl_seconds: ttl,
        };
      } catch (e) { /* ignore */ }

      if (navigation && typeof navigation.navigate === 'function') {
        // Navigate to the full-page EmailVerification screen when navigation is available
        navigation.navigate('EmailVerification', global.change_otp_verify_payload ?? {
          email: masked ?? newValue,
          otpToken,
          purpose: activePurpose,
          newValue: activePurpose === 'change_password' ? newPassword : newValue,
          ttl_seconds: ttl,
        });
      } else {
        // Fallback to inline OTP UI when not inside NavigationContainer
        setOtpSent(true);
        setOtpToken(otpToken);
        setSecondsLeft(ttl);
        if (timerRef.current) clearInterval(timerRef.current as any);
        timerRef.current = setInterval(() => {
          setSecondsLeft(prev => {
            if (!prev || prev <= 1) {
              if (timerRef.current) clearInterval(timerRef.current as any);
              return 0;
            }
            return prev - 1;
          });
        }, 1000) as any;
        Alert.alert('OTP sent', `Code sent to ${masked ?? 'destination'}`);
      }
        // If app-level state setter exists, open the dedicated verification screen
      try {
        // @ts-ignore
        if (typeof global.set_app_state === 'function') global.set_app_state('change_otp_verify');
      } catch (e) { /* ignore */ }
    } catch (err: any) {
      console.log(err.response?.data || err);
      Alert.alert('Error', err.response?.data?.message || err.message || 'Failed to send OTP');
    } finally {
      setIsSubmitting(false);
    }
  };

  // Verify OTP
  const handleVerifyOtp = async (providedCode?: string) => {
    try {
      setIsSubmitting(true);
      const code = providedCode ?? otp;
      const body: any = { purpose: activePurpose, otp: code, otp_token: otpToken } as any;
      // include new password in verify request for password change
      if (activePurpose === 'change_password') {
        body.new_value = newPassword;
      } else {
        body.new_value = newValue;
      }

      const res = await api_request('/api/change-otp/verify', {
        method: 'POST',
        body: JSON.stringify(body),
      });

      if (!res.success) {
        if (res.status === 429) {
          Alert.alert('Rate limit', res.message || 'Too many attempts');
          return;
        }
        throw new Error(res.message || 'OTP verification failed');
      }

      Alert.alert('Success', `${activePurpose.replace('_', ' ')} updated successfully`);
      onSuccess && onSuccess();
    } catch (err: any) {
      console.log(err.response?.data || err);
      Alert.alert('Error', err.response?.data?.message || err.message || 'OTP verification failed');
    } finally {
      setIsSubmitting(false);
    }
  };

  // Auto-fill email when purpose is change_password
  useEffect(() => {
    let mounted = true;
    const fill = async () => {
      if (activePurpose === 'change_password') {
        try {
          const stored = await storage_service.get_user_data();
          if (mounted && stored && stored.email) {
            setNewValue(stored.email);
          }
        } catch (e) {
          console.warn('Could not read stored user email:', e);
        }
      }
    };
    fill();
    return () => { mounted = false; if (timerRef.current) clearInterval(timerRef.current as any); };
  }, [activePurpose]);

  // Fetch profile (current phone) and resolve active role for contact changes
  useEffect(() => {
    let mounted = true;
    const loadProfile = async () => {
      try {
        const stored = await storage_service.get_user_data();
        const userType = (stored?.user_type || stored?.userType || '').toString().toLowerCase();
        const preferred = (stored?.preferred_role || stored?.preferredRole || '').toString().toLowerCase();
        const resolvedRole = userType === 'both' ? (preferred || 'owner') : (userType === 'property_owner' ? 'owner' : userType);
        if (mounted && resolvedRole) setActiveRole(resolvedRole);
        const roleQuery = resolvedRole ? `?role=${encodeURIComponent(resolvedRole)}` : '';
        const resp = await api_request(`/api/profile/fetch${roleQuery}`, { method: 'GET' });
        if (mounted && resp && resp.success && resp.data) {
          const payload = resp.data.data || resp.data || {};
          const owner = payload.owner || {};
          const contractor = payload.contractor || {};
          if (resolvedRole && String(resolvedRole).includes('contractor') && contractor && contractor.company_phone) {
            setCurrentPhone(contractor.company_phone);
          } else if (resolvedRole && String(resolvedRole).includes('owner') && owner && owner.phone_number) {
            setCurrentPhone(owner.phone_number);
          } else if (payload.user && payload.user.phone_number) {
            setCurrentPhone(payload.user.phone_number);
          }
        }
      } catch (e) {
        console.warn('Failed to fetch profile for contact change:', e);
      }
    };

    // Only fetch once on mount (or when purpose is contact)
    if (activePurpose === 'change_contact') loadProfile();
    return () => { mounted = false; };
  }, [activePurpose]);

  // Ensure newValue is empty when switching to contact change so email isn't shown
  useEffect(() => {
    if (activePurpose === 'change_contact') {
      setNewValue('');
    }
  }, [activePurpose]);

  const passwordsMatch = newPassword && newPassword === confirmPassword;
  const passwordRules = useMemo(() => {
    return {
      length: !!newPassword && newPassword.length >= 8,
      uppercase: /[A-Z]/.test(newPassword),
      number: /\d/.test(newPassword),
      special: /[!@#$%^&*(),.?":{}|<>]/.test(newPassword),
    };
  }, [newPassword]);

  const strengthCount = Object.values(passwordRules).filter(Boolean).length;
  const strengthLabel = strengthCount <= 1 ? 'Weak' : strengthCount === 2 || strengthCount === 3 ? 'Medium' : 'Strong';
  const strengthColor = strengthCount <= 1 ? COLORS.error : strengthCount === 2 || strengthCount === 3 ? '#F59E0B' : COLORS.success;
  const submitLabel = activePurpose === 'change_email' ? 'Continue' : 'Send OTP';
  const canSend = activePurpose === 'change_password'
    ? (passwordsMatch && !!newValue && !isSubmitting)
    : activePurpose === 'change_email'
      ? (!!newValue && !!currentPassword && !isSubmitting)
      : activePurpose === 'change_contact'
        ? (!!newValue && !!currentPhone && !isSubmitting)
        : (!!newValue && !isSubmitting);
  const canVerify = otpSent && otp.length >= 4 && !isSubmitting;

  const handleBack = useCallback(async () => {
  try {
    // Use provided onBack callback if available
    if (typeof onBack === 'function') {
      onBack();
      return;
    }

    // Use navigation if available
    if (navigation?.canGoBack?.()) {
      navigation.goBack();
      return;
    }

    // Fallback: determine profile type from stored user
    const stored = await storage_service.get_user_data();
    const role = stored?.user_type ?? stored?.determinedRole ?? null;

    // @ts-ignore - global state navigation fallback
    if (typeof global.set_app_state === 'function') {
      if (role === 'property_owner' || role === 'owner') {
        global.set_app_state('owner_profile');
      } else {
        global.set_app_state('contractor_profile');
      }
    }
  } catch (error) {
    // Ultimate fallback
    // @ts-ignore
    if (typeof global.set_app_state === 'function') {
      global.set_app_state('main');
    }
  }
}, [navigation, onBack]);

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity
          onPress={handleBack}
          style={styles.backButton}
          accessibilityLabel="Go back"
          hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
        >
          <Ionicons name="chevron-back" size={28} color={COLORS.text} />
          <Text style={styles.backText}>Back</Text>
        </TouchableOpacity>
      </View>
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        <View style={styles.iconContainer}>
          <View style={styles.iconCircle}>
            <Feather name="lock" size={40} color={COLORS.primary} />
          </View>
        </View>

        <Text style={styles.title}>{getTitle()}</Text>
        <Text style={styles.subtitle}>{getSubtitle()}</Text>

        <View style={{flexDirection: 'row', justifyContent: 'center', marginBottom: 18}}>
          <TouchableOpacity
            style={[styles.purposeButton, activePurpose === 'change_email' && styles.purposeButtonActive]}
            onPress={() => setSelectedPurpose('change_email')}
          >
            <Text style={[styles.purposeButtonText, activePurpose === 'change_email' && styles.purposeButtonTextActive]}>Email</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.purposeButton, activePurpose === 'change_contact' && styles.purposeButtonActive]}
            onPress={() => setSelectedPurpose('change_contact')}
          >
            <Text style={[styles.purposeButtonText, activePurpose === 'change_contact' && styles.purposeButtonTextActive]}>Contact</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.purposeButton, activePurpose === 'change_password' && styles.purposeButtonActive]}
            onPress={() => setSelectedPurpose('change_password')}
          >
            <Text style={[styles.purposeButtonText, activePurpose === 'change_password' && styles.purposeButtonTextActive]}>Password</Text>
          </TouchableOpacity>
        </View>

        {!otpSent ? (
          <View style={styles.inputGroup}>
            {activePurpose === 'change_password' ? (
              // Show registered email (disabled) and new password fields
              <>
                <Text style={styles.label}>Registered Email</Text>
                <View style={styles.inputWrapper}>
                  <TextInput
                    style={[styles.input, styles.disabledInput]}
                    value={newValue}
                    onChangeText={() => {}}
                    placeholder="you@example.com"
                    autoCapitalize="none"
                    editable={false}
                  />
                </View>

                <Text style={styles.label}>New Password *</Text>
                <View style={styles.inputWrapper}>
                  <TextInput
                    style={styles.input}
                    value={newPassword}
                    onChangeText={setNewPassword}
                    placeholder="Enter new password"
                    autoCapitalize="none"
                    secureTextEntry={!showNewPassword}
                    editable={!isSubmitting}
                  />
                  <TouchableOpacity
                    style={styles.eyeIcon}
                    onPress={() => setShowNewPassword(prev => !prev)}
                  >
                    <Ionicons name={showNewPassword ? 'eye-off' : 'eye'} size={22} color="#999" />
                  </TouchableOpacity>
                </View>

                <View style={styles.rulesContainer}>
                  <View style={{marginBottom: 8}}>
                    <View style={{flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between'}}>
                      <Text style={{fontWeight: '700', color: COLORS.text}}>Password strength</Text>
                      <Text style={[styles.subtitle, {fontSize: 13, color: strengthColor}]}>{strengthLabel}</Text>
                    </View>
                    <View style={styles.strengthBarContainer}>
                      <View style={[styles.strengthBar, {backgroundColor: strengthColor, width: `${(strengthCount / 4) * 100}%`}]} />
                    </View>
                  </View>

                  <View>
                    <View style={styles.ruleRow}>
                      <Ionicons name={passwordRules.length ? 'checkmark-circle' : 'ellipse-outline'} size={18} color={passwordRules.length ? COLORS.success : COLORS.textSecondary} />
                      <Text style={[styles.ruleText, {color: passwordRules.length ? COLORS.text : COLORS.textSecondary}]}>At least 8 characters</Text>
                    </View>
                    <View style={styles.ruleRow}>
                      <Ionicons name={passwordRules.uppercase ? 'checkmark-circle' : 'ellipse-outline'} size={18} color={passwordRules.uppercase ? COLORS.success : COLORS.textSecondary} />
                      <Text style={[styles.ruleText, {color: passwordRules.uppercase ? COLORS.text : COLORS.textSecondary}]}>One uppercase letter</Text>
                    </View>
                    <View style={styles.ruleRow}>
                      <Ionicons name={passwordRules.number ? 'checkmark-circle' : 'ellipse-outline'} size={18} color={passwordRules.number ? COLORS.success : COLORS.textSecondary} />
                      <Text style={[styles.ruleText, {color: passwordRules.number ? COLORS.text : COLORS.textSecondary}]}>One number</Text>
                    </View>
                    <View style={styles.ruleRow}>
                      <Ionicons name={passwordRules.special ? 'checkmark-circle' : 'ellipse-outline'} size={18} color={passwordRules.special ? COLORS.success : COLORS.textSecondary} />
                      <Text style={[styles.ruleText, {color: passwordRules.special ? COLORS.text : COLORS.textSecondary}]}>One special character</Text>
                    </View>
                  </View>
                </View>

                <Text style={styles.label}>Confirm Password *</Text>
                <View style={styles.inputWrapper}>
                  <TextInput
                    style={styles.input}
                    value={confirmPassword}
                    onChangeText={setConfirmPassword}
                    placeholder="Confirm new password"
                    autoCapitalize="none"
                    secureTextEntry={!showConfirmPassword}
                    editable={!isSubmitting}
                  />
                  <TouchableOpacity
                    style={styles.eyeIcon}
                    onPress={() => setShowConfirmPassword(prev => !prev)}
                  >
                    <Ionicons name={showConfirmPassword ? 'eye-off' : 'eye'} size={22} color="#999" />
                  </TouchableOpacity>
                </View>
              </>
            ) : (
              // change_email or change_contact
              <>
                {activePurpose === 'change_email' ? (
                  <>
                    <Text style={styles.label}>Current Password *</Text>
                    <View style={styles.inputWrapper}>
                      <TextInput
                        style={styles.input}
                        value={currentPassword}
                        onChangeText={setCurrentPassword}
                        placeholder="Enter your current password"
                        secureTextEntry
                        autoCapitalize="none"
                        editable={!isSubmitting}
                      />
                    </View>

                    <Text style={styles.label}>New Email Address *</Text>
                    <View style={styles.inputWrapper}>
                      <TextInput
                        style={styles.input}
                        value={newValue}
                        onChangeText={setNewValue}
                        placeholder="you@example.com"
                        keyboardType="default"
                        autoCapitalize="none"
                        editable={!isSubmitting}
                      />
                    </View>
                  </>
                ) : (
                  <>
                    <Text style={styles.label}>Current Contact Number</Text>
                    <View style={styles.inputWrapper}>
                      <TextInput
                        style={[styles.input, styles.disabledInput]}
                        value={currentPhone ?? ''}
                        onChangeText={() => {}}
                        placeholder="Not available"
                        keyboardType="phone-pad"
                        autoCapitalize="none"
                        editable={false}
                      />
                    </View>

                    <Text style={styles.label}>New Contact Number *</Text>
                    <View style={styles.inputWrapper}>
                      <TextInput
                        style={styles.input}
                        value={newValue}
                        onChangeText={setNewValue}
                        placeholder="09xxxxxxxxx"
                        keyboardType="phone-pad"
                        autoCapitalize="none"
                        editable={!isSubmitting}
                      />
                    </View>
                  </>
                )}
              </>
            )}

            <TouchableOpacity
              style={[styles.submitButton, !canSend && styles.submitButtonDisabled]}
              onPress={handleSendOtp}
              disabled={!canSend}
              activeOpacity={0.8}
            >
              {isSubmitting ? <ActivityIndicator color="#FFF" size="small" /> : <Text style={styles.submitButtonText}>Send OTP</Text>}
            </TouchableOpacity>
          </View>
        ) : null}


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
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 10,
    paddingBottom: 6,
  },

  backButton: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  backText: {
    fontSize: 16,
    color: COLORS.text,
    marginLeft: 4,
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
  eyeIcon: {
    position: 'absolute',
    right: 14,
    top: 14,
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
  disabledInput: {
    backgroundColor: '#F3F4F6',
    color: COLORS.textSecondary,
  },
  rulesContainer: {
    backgroundColor: COLORS.surface,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: COLORS.border,
    padding: 14,
    marginBottom: 20,
  },
  info: {
    marginBottom: 10,
    fontStyle: 'italic',
    color: COLORS.textSecondary,
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
  purposeButton: {
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: COLORS.border,
    marginHorizontal: 6,
    backgroundColor: 'transparent',
  },
  purposeButtonActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  purposeButtonText: {
    color: COLORS.textSecondary,
    fontWeight: '600',
  },
  purposeButtonTextActive: {
    color: '#FFF',
  },
  strengthBarContainer: {
    height: 8,
    backgroundColor: '#F3F4F6',
    borderRadius: 6,
    overflow: 'hidden',
    marginTop: 8,
  },
  strengthBar: {
    height: '100%',
  },
  ruleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  ruleText: {
    marginLeft: 8,
    color: '#6B7280',
    fontSize: 13,
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
