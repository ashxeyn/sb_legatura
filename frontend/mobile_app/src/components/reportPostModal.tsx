import React, { useEffect, useMemo, useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Modal,
  StyleSheet,
  TextInput,
  ActivityIndicator,
} from 'react-native';

const REPORT_REASONS = [
  'Spam',
  'Inappropriate Content',
  'Scam / Fraud',
  'False Information',
  'Other',
] as const;

type ReportReason = (typeof REPORT_REASONS)[number];

interface SubmitResult {
  success: boolean;
  message?: string;
}

interface ReportPostModalProps {
  visible: boolean;
  onClose: () => void;
  onSubmit: (reason: string, details?: string) => Promise<SubmitResult>;
}

export default function ReportPostModal({ visible, onClose, onSubmit }: ReportPostModalProps) {
  const [selectedReason, setSelectedReason] = useState<ReportReason | null>(null);
  const [otherDetails, setOtherDetails] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [errorText, setErrorText] = useState('');
  const [successText, setSuccessText] = useState('');

  useEffect(() => {
    if (!visible) {
      setSelectedReason(null);
      setOtherDetails('');
      setSubmitting(false);
      setErrorText('');
      setSuccessText('');
    }
  }, [visible]);

  const canSubmit = useMemo(() => {
    if (!selectedReason) return false;
    if (selectedReason !== 'Other') return true;
    return otherDetails.trim().length > 0;
  }, [selectedReason, otherDetails]);

  const handleSubmit = async () => {
    if (!canSubmit || submitting || !selectedReason) return;

    setSubmitting(true);
    setErrorText('');
    try {
      const details = selectedReason === 'Other' ? otherDetails.trim() : undefined;
      const result = await onSubmit(selectedReason, details);
      if (result?.success) {
        setSuccessText(result.message || 'Report sent successfully.');
      } else {
        setErrorText(result?.message || 'Unable to submit report right now.');
      }
    } catch {
      setErrorText('Unable to submit report right now.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onClose}>
      <View style={styles.overlay}>
        <View style={styles.card}>
          {successText ? (
            <>
              <Text style={styles.title}>Report Submitted</Text>
              <Text style={styles.successText}>{successText}</Text>
              <View style={styles.actionsRow}>
                <TouchableOpacity style={styles.submitBtn} onPress={onClose}>
                  <Text style={styles.submitText}>Done</Text>
                </TouchableOpacity>
              </View>
            </>
          ) : (
            <>
              <Text style={styles.title}>Report Post</Text>
              <Text style={styles.subtitle}>Why are you reporting this post?</Text>

              <View style={styles.reasonsWrap}>
                {REPORT_REASONS.map((reason) => {
                  const isActive = selectedReason === reason;
                  return (
                    <TouchableOpacity
                      key={reason}
                      style={[styles.reasonRow, isActive && styles.reasonRowActive]}
                      onPress={() => setSelectedReason(reason)}
                      activeOpacity={0.8}
                      disabled={submitting}
                    >
                      <Text style={[styles.reasonText, isActive && styles.reasonTextActive]}>{reason}</Text>
                    </TouchableOpacity>
                  );
                })}
              </View>

              {selectedReason === 'Other' && (
                <View style={styles.otherInputWrap}>
                  <Text style={styles.otherLabel}>Please specify</Text>
                  <TextInput
                    value={otherDetails}
                    onChangeText={setOtherDetails}
                    placeholder="Type your reason"
                    multiline
                    numberOfLines={3}
                    style={styles.otherInput}
                    editable={!submitting}
                    placeholderTextColor="#9CA3AF"
                  />
                </View>
              )}

              {!!errorText && <Text style={styles.errorText}>{errorText}</Text>}

              <View style={styles.actionsRow}>
                <TouchableOpacity style={styles.cancelBtn} onPress={onClose} disabled={submitting}>
                  <Text style={styles.cancelText}>Cancel</Text>
                </TouchableOpacity>
                <TouchableOpacity
                  style={[styles.submitBtn, (!canSubmit || submitting) && styles.submitBtnDisabled]}
                  onPress={handleSubmit}
                  disabled={!canSubmit || submitting}
                >
                  {submitting ? <ActivityIndicator size="small" color="#FFFFFF" /> : <Text style={styles.submitText}>Submit</Text>}
                </TouchableOpacity>
              </View>
            </>
          )}
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.28)',
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  card: {
    width: '100%',
    maxWidth: 420,
    backgroundColor: '#FFFFFF',
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    paddingHorizontal: 14,
    paddingTop: 14,
    paddingBottom: 12,
  },
  title: {
    fontSize: 17,
    fontWeight: '700',
    color: '#111827',
  },
  subtitle: {
    marginTop: 4,
    marginBottom: 10,
    fontSize: 13,
    color: '#6B7280',
  },
  reasonsWrap: {
    gap: 6,
  },
  reasonRow: {
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 8,
    paddingVertical: 9,
    paddingHorizontal: 10,
  },
  reasonRowActive: {
    borderColor: '#EEA24B',
    backgroundColor: '#FFF8EE',
  },
  reasonText: {
    fontSize: 13,
    color: '#1F2937',
    fontWeight: '500',
  },
  reasonTextActive: {
    color: '#A35D00',
  },
  otherInputWrap: {
    marginTop: 10,
  },
  otherLabel: {
    fontSize: 12,
    color: '#4B5563',
    marginBottom: 6,
    fontWeight: '500',
  },
  otherInput: {
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    minHeight: 72,
    textAlignVertical: 'top',
    paddingHorizontal: 10,
    paddingVertical: 8,
    fontSize: 13,
    color: '#111827',
  },
  errorText: {
    marginTop: 8,
    fontSize: 12,
    color: '#B91C1C',
  },
  successText: {
    marginTop: 8,
    fontSize: 13,
    color: '#166534',
  },
  actionsRow: {
    marginTop: 12,
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: 8,
  },
  cancelBtn: {
    borderWidth: 1,
    borderColor: '#D1D5DB',
    borderRadius: 8,
    paddingHorizontal: 14,
    paddingVertical: 8,
  },
  cancelText: {
    fontSize: 13,
    color: '#374151',
    fontWeight: '600',
  },
  submitBtn: {
    backgroundColor: '#EEA24B',
    borderRadius: 8,
    paddingHorizontal: 14,
    paddingVertical: 8,
    minWidth: 74,
    alignItems: 'center',
  },
  submitBtnDisabled: {
    opacity: 0.5,
  },
  submitText: {
    fontSize: 13,
    color: '#FFFFFF',
    fontWeight: '700',
  },
});
