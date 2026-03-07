import React from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Modal,
  StyleSheet,
  Dimensions,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

interface RegistrationSuccessModalProps {
  visible: boolean;
  onDismiss: () => void;
  userType?: 'property_owner' | 'contractor';
}

const RegistrationSuccessModal: React.FC<RegistrationSuccessModalProps> = ({
  visible,
  onDismiss,
  userType = 'property_owner',
}) => {
  return (
    <Modal
      visible={visible}
      transparent
      animationType="fade"
      statusBarTranslucent
      onRequestClose={onDismiss}
    >
      <View style={styles.overlay}>
        <View style={styles.card}>
          {/* Success icon */}
          <View style={styles.iconCircle}>
            <Ionicons name="checkmark-sharp" size={36} color="#fff" />
          </View>

          {/* Title */}
          <Text style={styles.title}>Registration Complete</Text>

          {/* Divider */}
          <View style={styles.divider} />

          {/* Body */}
          <Text style={styles.body}>
            Your account has been submitted for review. Please check your email
            for a confirmation message.
          </Text>

          <View style={styles.infoBox}>
            <Ionicons
              name="time-outline"
              size={20}
              color="#3B82F6"
              style={styles.infoIcon}
            />
            <Text style={styles.infoText}>
              Account verification typically takes{' '}
              <Text style={styles.bold}>1 – 3 business days</Text>. You'll
              receive an email once your account has been approved.
            </Text>
          </View>

          <View style={styles.infoBox}>
            <Ionicons
              name="mail-outline"
              size={20}
              color="#3B82F6"
              style={styles.infoIcon}
            />
            <Text style={styles.infoText}>
              If you don't see the email, please check your spam or junk folder.
            </Text>
          </View>

          {/* CTA */}
          <TouchableOpacity
            style={styles.button}
            activeOpacity={0.85}
            onPress={onDismiss}
          >
            <Text style={styles.buttonText}>Got It</Text>
          </TouchableOpacity>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.55)',
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 24,
  },
  card: {
    width: SCREEN_WIDTH - 48,
    maxWidth: 400,
    backgroundColor: '#FFFFFF',
    borderRadius: 8,
    paddingTop: 36,
    paddingHorizontal: 24,
    paddingBottom: 24,
    alignItems: 'center',
    // subtle shadow
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.15,
    shadowRadius: 24,
    elevation: 12,
  },
  iconCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#22C55E',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
  },
  title: {
    fontSize: 22,
    fontWeight: '700',
    color: '#111827',
    textAlign: 'center',
    marginBottom: 12,
  },
  divider: {
    width: 48,
    height: 3,
    backgroundColor: '#E5E7EB',
    borderRadius: 2,
    marginBottom: 20,
  },
  body: {
    fontSize: 14,
    lineHeight: 21,
    color: '#4B5563',
    textAlign: 'center',
    marginBottom: 20,
  },
  infoBox: {
    flexDirection: 'row',
    backgroundColor: '#EFF6FF',
    borderRadius: 6,
    padding: 14,
    marginBottom: 12,
    alignItems: 'flex-start',
    width: '100%',
  },
  infoIcon: {
    marginRight: 10,
    marginTop: 1,
  },
  infoText: {
    flex: 1,
    fontSize: 13,
    lineHeight: 19,
    color: '#374151',
  },
  bold: {
    fontWeight: '700',
    color: '#1E40AF',
  },
  button: {
    marginTop: 12,
    width: '100%',
    backgroundColor: '#3B82F6',
    paddingVertical: 14,
    borderRadius: 6,
    alignItems: 'center',
  },
  buttonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
    letterSpacing: 0.3,
  },
});

export default RegistrationSuccessModal;
