import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  Image,
  ScrollView,
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { PropertyOwnerPersonalInfo } from './personalInfo';

interface AccountSetupScreenProps {
  onBackPress: () => void;
  onNext: (accountInfo: AccountInfo) => Promise<Record<string, string[]> | void>;
  personalInfo: PropertyOwnerPersonalInfo;
  initialData?: AccountInfo | null;
}

export interface AccountInfo {
  username: string;
  email: string;
  password: string;
  confirmPassword: string;
}

export default function AccountSetupScreen({ onBackPress, onNext, personalInfo, initialData }: AccountSetupScreenProps) {
  const [username, setUsername] = useState(initialData?.username || '');
  const [email, setEmail] = useState(initialData?.email || '');
  const [password, setPassword] = useState(initialData?.password || '');
  const [confirmPassword, setConfirmPassword] = useState(initialData?.confirmPassword || '');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});

  const handleNext = async () => {
    if (isLoading) return;

    // Clear previous errors
    setFieldErrors({});

    // Local validation
    const errors: Record<string, string[]> = {};
    if (!username.trim()) errors.username = ['Username is required.'];
    if (!email.trim()) errors.email = ['Email is required.'];
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

    const accountInfo: AccountInfo = {
      username: username.trim(),
      email: email.trim(),
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
      <KeyboardAvoidingView
        style={{ flex: 1 }}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 100 : 80}
      >
        <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false} keyboardShouldPersistTaps="handled">
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
            <Text style={[styles.progressText, styles.progressTextActive]}>Personal Information</Text>
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
              style={[styles.input, fieldErrors.email && styles.inputError]}
              value={email}
              onChangeText={(text) => { setEmail(text); setFieldErrors(prev => { const { email, ...rest } = prev; return rest; }); }}
              placeholder="Email *"
              placeholderTextColor="#999"
              keyboardType="email-address"
              autoCapitalize="none"
              autoCorrect={false}
            />
            {fieldErrors.email && <Text style={styles.fieldErrorText}>{fieldErrors.email[0]}</Text>}
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
                style={[styles.input, styles.passwordInput, fieldErrors.password_confirmation && styles.inputError]}
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
            {fieldErrors.password_confirmation && <Text style={styles.fieldErrorText}>{fieldErrors.password_confirmation[0]}</Text>}
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
      </KeyboardAvoidingView>
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
  inputError: {
    borderColor: '#DC3545',
  },
  fieldErrorText: {
    color: '#DC3545',
    fontSize: 12,
    marginTop: 4,
    marginLeft: 4,
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
  buttonContainer: {
    flexDirection: 'row',
    gap: 15,
    marginTop: 40,
  },
  backButton: {
    flex: 1,
    backgroundColor: '#F5F5F5',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
  },
  backButtonText: {
    color: '#666666',
    fontSize: 18,
    fontWeight: '600',
  },
  nextButton: {
    flex: 1,
    backgroundColor: '#EC7E00',
    borderRadius: 12,
    paddingVertical: 18,
    alignItems: 'center',
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
  },
  nextButtonTextDisabled: {
    color: '#999999',
  },
});
