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

  // Update form fields when initialData changes (when navigating back)
  useEffect(() => {
    if (initialData) {
      setUsername(initialData.username || '');
      setFirstName(initialData.firstName || '');
      setMiddleName(initialData.middleName || '');
      setLastName(initialData.lastName || '');
      setCompanyEmail(initialData.companyEmail || '');
    }
  }, [initialData]);

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
    } else if (password.length < 8) {
      errors.password = ['Password must be at least 8 characters.'];
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
});
