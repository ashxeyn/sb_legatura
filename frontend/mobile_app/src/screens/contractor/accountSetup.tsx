import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  Image,
  ScrollView,
  ActivityIndicator
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { CompanyInfo } from './companyInfo';
import AsyncStorage from '@react-native-async-storage/async-storage';

interface ContractorAccountSetupScreenProps {
  onBackPress: () => void;
  onNext: (accountInfo: ContractorAccountInfo) => Promise<Record<string, string[]> | void>;
  companyInfo: CompanyInfo;
  initialData?: ContractorAccountInfo | null;
}

export interface ContractorAccountInfo {
  firstName: string;
  middleName: string;
  lastName: string;
  username: string;
  companyEmail: string;
  password: string;
  confirmPassword: string;
}

export default function ContractorAccountSetupScreen({ onBackPress, onNext, companyInfo, initialData }: ContractorAccountSetupScreenProps) {
  const [username, setUsername] = useState(initialData?.username || '');
  const [firstName, setFirstName] = useState(initialData?.firstName || '');
  const [middleName, setMiddleName] = useState(initialData?.middleName || '');
  const [lastName, setLastName] = useState(initialData?.lastName || '');
  const [companyEmail, setCompanyEmail] = useState(initialData?.companyEmail || '');
  const [password, setPassword] = useState(initialData?.password || '');
  const [confirmPassword, setConfirmPassword] = useState(initialData?.confirmPassword || '');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});

  useEffect(() => {
    if (initialData && Object.keys(initialData).length > 0) {
      setUsername(initialData.username || '');
      setFirstName(initialData.firstName || '');
      setMiddleName(initialData.middleName || '');
      setLastName(initialData.lastName || '');
      setCompanyEmail(initialData.companyEmail || '');
    } else {
      AsyncStorage.getItem('signup_contractor_accountSetup').then(cached => {
        if (cached) {
          try {
            const parsed = JSON.parse(cached);
            if (parsed.username) setUsername(parsed.username);
            if (parsed.firstName) setFirstName(parsed.firstName);
            if (parsed.middleName) setMiddleName(parsed.middleName);
            if (parsed.lastName) setLastName(parsed.lastName);
            if (parsed.companyEmail) setCompanyEmail(parsed.companyEmail);
            if (parsed.password) setPassword(parsed.password);
            if (parsed.confirmPassword) setConfirmPassword(parsed.confirmPassword);
          } catch (e) {}
        }
      });
    }
  }, [initialData]);

  useEffect(() => {
    const cache = { username, firstName, middleName, lastName, companyEmail, password, confirmPassword };
    const timer = setTimeout(() => {
      AsyncStorage.setItem('signup_contractor_accountSetup', JSON.stringify(cache)).catch(() => {});
    }, 500);
    return () => clearTimeout(timer);
  }, [username, firstName, middleName, lastName, companyEmail, password, confirmPassword]);

  const passwordRules = {
    minLength: password.length >= 8,
    hasUppercase: /[A-Z]/.test(password),
    hasLowercase: /[a-z]/.test(password),
    hasNumber: /[0-9]/.test(password),
    hasSpecial: /[^A-Za-z0-9\s]/.test(password),
  };
  const allPasswordRulesMet = Object.values(passwordRules).every(Boolean);

  const handleNext = async () => {
    if (isLoading) return;

    // Clear previous errors
    setFieldErrors({});

    // Local validation
    const errors: Record<string, string[]> = {};
    if (!firstName.trim()) errors.first_name = ['First name is required.'];
    if (!lastName.trim()) errors.last_name = ['Last name is required.'];
    if (!username.trim()) errors.username = ['Username is required.'];
    if (!companyEmail.trim()) errors.company_email = ['Company email is required.'];
    if (!password.trim()) {
      errors.password = ['Password is required.'];
    } else if (!allPasswordRulesMet) {
      errors.password = ['Password does not meet all requirements.'];
    }
    if (!confirmPassword.trim()) {
      errors.password_confirmation = ['Please confirm your password.'];
    } else if (password !== confirmPassword) {
      errors.password_confirmation = ['Passwords do not match.'];
    }

    if (Object.keys(errors).length > 0) {
      setFieldErrors(errors);
      return;
    }

    setIsLoading(true);

    const accountInfo: ContractorAccountInfo = {
      username: username.trim(),
      firstName: firstName.trim(),
      middleName: middleName.trim(),
      lastName: lastName.trim(),
      companyEmail: companyEmail.trim(),
      password: password,
      confirmPassword: confirmPassword,
    };

    try {
      const serverErrors = await onNext(accountInfo);
      if (serverErrors) {
        setFieldErrors(serverErrors);
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        <View style={styles.logoContainer}>
          <Image
            source={require('../../../assets/images/logos/legatura-logo.png')}
            style={styles.logo}
            resizeMode="contain"
          />
        </View>

        <View style={styles.progressContainer}>
          <View style={styles.progressStep}>
            <View style={[styles.progressBar, styles.progressBarActive]} />
            <Text style={[styles.progressText, styles.progressTextActive]}>Company Information</Text>
          </View>
          <View style={styles.progressStep}>
            <View style={[styles.progressBar, styles.progressBarActive]} />
            <Text style={[styles.progressText, styles.progressTextActive]}>Account Setup</Text>
          </View>
          <View style={styles.progressStep}>
            <View style={styles.progressBar} />
            <Text style={styles.progressText}>Verification</Text>
          </View>
        </View>

        <View style={styles.formContainer}>
          {/* Personal Information Section */}
          <Text style={styles.sectionTitle}>Personal Information</Text>

          <View style={styles.inputContainer}>
            <TextInput
              style={[styles.input, fieldErrors.first_name && styles.inputError]}
              value={firstName}
              onChangeText={(text) => { setFirstName(text); setFieldErrors(prev => { const { first_name, ...rest } = prev; return rest; }); }}
              placeholder="First Name *"
              placeholderTextColor="#999"
              maxLength={100}
            />
            {fieldErrors.first_name && <Text style={styles.fieldErrorText}>{fieldErrors.first_name[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              style={[styles.input, fieldErrors.middle_name && styles.inputError]}
              value={middleName}
              onChangeText={(text) => { setMiddleName(text); setFieldErrors(prev => { const { middle_name, ...rest } = prev; return rest; }); }}
              placeholder="Middle Name (Optional)"
              placeholderTextColor="#999"
              maxLength={100}
            />
            {fieldErrors.middle_name && <Text style={styles.fieldErrorText}>{fieldErrors.middle_name[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              style={[styles.input, fieldErrors.last_name && styles.inputError]}
              value={lastName}
              onChangeText={(text) => { setLastName(text); setFieldErrors(prev => { const { last_name, ...rest } = prev; return rest; }); }}
              placeholder="Last Name *"
              placeholderTextColor="#999"
              maxLength={100}
            />
            {fieldErrors.last_name && <Text style={styles.fieldErrorText}>{fieldErrors.last_name[0]}</Text>}
          </View>

          {/* Account Credentials Section */}
          <Text style={styles.sectionTitle}>Account Credentials</Text>

          <View style={styles.inputContainer}>
            <TextInput
              style={[styles.input, fieldErrors.username && styles.inputError]}
              value={username}
              onChangeText={(text) => { setUsername(text); setFieldErrors(prev => { const { username, ...rest } = prev; return rest; }); }}
              placeholder="Username *"
              placeholderTextColor="#999"
              autoCapitalize="none"
            />
            {fieldErrors.username && <Text style={styles.fieldErrorText}>{fieldErrors.username[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              style={[styles.input, fieldErrors.company_email && styles.inputError]}
              value={companyEmail}
              onChangeText={(text) => { setCompanyEmail(text); setFieldErrors(prev => { const { company_email, ...rest } = prev; return rest; }); }}
              placeholder="Company Email *"
              placeholderTextColor="#999"
              keyboardType="email-address"
              autoCapitalize="none"
              autoCorrect={false}
            />
            {fieldErrors.company_email && <Text style={styles.fieldErrorText}>{fieldErrors.company_email[0]}</Text>}
          </View>

          <View style={styles.inputContainer}>
            <View style={styles.passwordContainer}>
              <TextInput
                style={[styles.input, styles.passwordInput, fieldErrors.password && styles.inputError]}
                value={password}
                onChangeText={(text) => { setPassword(text); setFieldErrors(prev => { const { password, ...rest } = prev; return rest; }); }}
                placeholder="Password *"
                placeholderTextColor="#999"
                secureTextEntry={!showPassword}
              />
              <TouchableOpacity
                style={styles.eyeIcon}
                onPress={() => setShowPassword(!showPassword)}
              >
                <Ionicons
                  name={showPassword ? "eye-off" : "eye"}
                  size={20}
                  color="#666666"
                />
              </TouchableOpacity>
            </View>
            {fieldErrors.password && <Text style={styles.fieldErrorText}>{fieldErrors.password[0]}</Text>}
            {password.length > 0 && (
              <View style={styles.rulesContainer}>
                {[
                  { key: 'minLength', label: 'At least 8 characters' },
                  { key: 'hasUppercase', label: 'At least one uppercase letter' },
                  { key: 'hasLowercase', label: 'At least one lowercase letter' },
                  { key: 'hasNumber', label: 'At least one number' },
                  { key: 'hasSpecial', label: 'At least one special character' },
                ].map(({ key, label }) => {
                  const met = passwordRules[key as keyof typeof passwordRules];
                  return (
                    <View key={key} style={styles.ruleRow}>
                      <Ionicons
                        name={met ? 'checkmark-circle' : 'ellipse-outline'}
                        size={16}
                        color={met ? '#22C55E' : '#BBBBBB'}
                      />
                      <Text style={[styles.ruleText, met && styles.ruleTextMet]}>
                        {label}
                      </Text>
                    </View>
                  );
                })}
              </View>
            )}
          </View>

          <View style={styles.inputContainer}>
            <View style={styles.passwordContainer}>
              <TextInput
                style={[styles.input, styles.passwordInput, (fieldErrors.password_confirmation || (confirmPassword && password !== confirmPassword)) && styles.inputError]}
                value={confirmPassword}
                onChangeText={(text) => { setConfirmPassword(text); setFieldErrors(prev => { const { password_confirmation, ...rest } = prev; return rest; }); }}
                placeholder="Confirm password *"
                placeholderTextColor="#999"
                secureTextEntry={!showConfirmPassword}
              />
              <TouchableOpacity
                style={styles.eyeIcon}
                onPress={() => setShowConfirmPassword(!showConfirmPassword)}
              >
                <Ionicons
                  name={showConfirmPassword ? "eye-off" : "eye"}
                  size={20}
                  color="#666666"
                />
              </TouchableOpacity>
            </View>
            {fieldErrors.password_confirmation ? (
              <Text style={styles.fieldErrorText}>{fieldErrors.password_confirmation[0]}</Text>
            ) : confirmPassword && password !== confirmPassword ? (
              <Text style={styles.errorText}>Passwords do not match</Text>
            ) : null}
          </View>
        </View>

        <View style={styles.buttonContainer}>
          <TouchableOpacity style={styles.backButton} onPress={onBackPress}>
            <Text style={styles.backButtonText}>Back</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[
              styles.nextButton,
              isLoading && styles.nextButtonDisabled
            ]}
            onPress={handleNext}
            disabled={isLoading}
          >
            {isLoading ? (
              <ActivityIndicator color="#FFFFFF" />
            ) : (
              <Text style={[
                styles.nextButtonText,
                isLoading && styles.nextButtonTextDisabled
              ]}>
                Next
              </Text>
            )}
          </TouchableOpacity>
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
  scrollContent: {
    flexGrow: 1,
    paddingHorizontal: 30,
    paddingBottom: 40,
  },
  logoContainer: {
    alignItems: 'center',
    marginTop: 40,
    marginBottom: 30,
  },
  logo: {
    width: 200,
    height: 120,
  },
  progressContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 40,
    paddingHorizontal: 10,
  },
  progressStep: {
    flex: 1,
    alignItems: 'center',
  },
  progressBar: {
    height: 4,
    backgroundColor: '#E5E5E5',
    borderRadius: 2,
    width: '100%',
    marginBottom: 8,
  },
  progressBarActive: {
    backgroundColor: '#EC7E00',
  },
  progressText: {
    fontSize: 12,
    color: '#999999',
    textAlign: 'center',
  },
  progressTextActive: {
    color: '#333333',
    fontWeight: '600',
  },
  formContainer: {
    flex: 1,
    gap: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333333',
    marginBottom: 20,
    marginTop: 10,
  },
  inputContainer: {
    marginBottom: 4,
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
  passwordContainer: {
    position: 'relative',
  },
  passwordInput: {
    paddingRight: 50,
  },
  eyeIcon: {
    position: 'absolute',
    right: 16,
    top: 16,
    padding: 4,
  },
  errorText: {
    color: '#FF3B30',
    fontSize: 14,
    marginTop: 5,
    marginLeft: 4,
  },
  fieldErrorText: {
    color: '#DC3545',
    fontSize: 12,
    marginTop: 4,
    marginLeft: 4,
  },
  inputError: {
    borderColor: '#DC3545',
  },
  buttonContainer: {
    flexDirection: 'row',
    gap: 15,
    marginTop: 40,
    paddingHorizontal: 5,
    paddingBottom: 20,
  },
  backButton: {
    flex: 1,
    backgroundColor: '#E8E8E8',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
    marginRight: 8,
  },
  backButtonText: {
    color: '#333333',
    fontSize: 18,
    fontWeight: '600',
  },
  nextButton: {
    flex: 1,
    backgroundColor: '#EC7E00',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
    marginLeft: 8,
    shadowColor: '#EC7E00',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  nextButtonText: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: '600',
  },
  nextButtonDisabled: {
    backgroundColor: '#CCCCCC',
    shadowColor: '#CCCCCC',
    shadowOpacity: 0,
    elevation: 0,
  },
  nextButtonTextDisabled: {
    color: '#999999',
  },
  rulesContainer: {
    marginTop: 10,
    paddingHorizontal: 4,
    gap: 6,
  },
  ruleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  ruleText: {
    fontSize: 13,
    color: '#BBBBBB',
  },
  ruleTextMet: {
    color: '#22C55E',
  },
});
