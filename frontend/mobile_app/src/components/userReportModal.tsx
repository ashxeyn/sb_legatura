import React, { useEffect, useMemo, useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  Modal,
  StyleSheet,
  TextInput,
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  TouchableWithoutFeedback,
  Keyboard,
} from 'react-native';
import { Feather } from '@expo/vector-icons';

interface SubmitResult {
  success: boolean;
  message?: string;
}

interface UserReportModalProps {
  visible: boolean;
  onClose: () => void;
  onSubmit: (reasons: string[], description?: string) => Promise<SubmitResult>;
  initialSelectedReasons?: string[];
  initialDescription?: string | null;
}

const CATEGORIES = [
  'Scammer',
  'Harassment',
  'Spam',
  'Fake Profile',
  'Inappropriate Behavior',
  'Other',
] as const;

export default function UserReportModal({ visible, onClose, onSubmit, initialSelectedReasons, initialDescription }: UserReportModalProps) {
  const [selectedReasons, setSelectedReasons] = useState<string[]>([]);
  const [otherText, setOtherText] = useState('');
  const [description, setDescription] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [errorText, setErrorText] = useState('');
  const [successText, setSuccessText] = useState('');
  const [showConfirm, setShowConfirm] = useState(false);

  useEffect(() => {
    if (!visible) {
      setSelectedReasons([]);
      setOtherText('');
      setDescription(initialDescription || '');
      setSubmitting(false);
      setErrorText('');
      setSuccessText('');
      setShowConfirm(false);
      return;
    }

    // Prefill from parent-provided initial values when opening
    try {
      const initial = Array.isArray(initialSelectedReasons) ? initialSelectedReasons.map((r) => String(r).trim()) : [];
      let extractedOther = '';
      const normalized = initial.map((r) => {
        const s = String(r || '').trim();
        const m = s.match(/^Other\s*[:\-]\s*(.+)$/i);
        if (m) {
          extractedOther = m[1].trim();
          return 'Other';
        }
        return s;
      }).filter(Boolean);

      setSelectedReasons(normalized);
      if (extractedOther) {
        setOtherText(extractedOther);
      } else if (normalized.includes('Other') && initialDescription) {
        setOtherText(initialDescription || '');
      }

      setDescription(initialDescription || '');
    } catch (e) {
      setSelectedReasons([]);
      setOtherText('');
      setDescription(initialDescription || '');
    }
  }, [visible]);

  const canSubmit = useMemo(() => {
    if (!selectedReasons || selectedReasons.length === 0) return false;
    if (selectedReasons.includes('Other')) return otherText.trim().length > 0;
    return true;
  }, [selectedReasons, otherText]);

  const toggleReason = (r: string) => {
    setErrorText('');
    setSelectedReasons((prev) => {
      if (prev.includes(r)) {
        return prev.filter((x) => x !== r);
      }
      return [...prev, r];
    });
  };

  const handleSubmit = async () => {
    // Show confirmation view first
    if (!canSubmit || submitting) return;
    setShowConfirm(true);
  };

  const performSubmit = async () => {
    if (submitting) return;
    setSubmitting(true);
    setErrorText('');
    setShowConfirm(false);
    try {
      const finalReasons = selectedReasons.map((r) => (r === 'Other' ? `Other: ${otherText.trim()}` : r));
      const res = await onSubmit(finalReasons, description?.trim() || undefined);
      if (res?.success) {
        setSuccessText(res.message || 'Our team will review this report.');
      } else {
        setErrorText(res?.message || 'Failed to submit report.');
      }
    } catch (e) {
      setErrorText('Failed to submit report.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
      <KeyboardAvoidingView
        style={styles.overlay}
        behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 60 : 0}
      >
        <TouchableWithoutFeedback onPress={Keyboard.dismiss} accessible={false}>
          <View style={styles.overlay}>
            <View style={styles.backdrop} />

            <View style={styles.sheet}>
              <ScrollView
                keyboardShouldPersistTaps="handled"
                keyboardDismissMode="on-drag"
                contentContainerStyle={styles.scrollContent}
              >
                {successText ? (
                  <View style={styles.successWrap}>
                    <View style={styles.successIcon}><Feather name="check" size={28} color="#16A34A" /></View>
                    <Text style={styles.successTitle}>✓ Report submitted</Text>
                    <Text style={styles.successDesc}>{successText}</Text>
                    <TouchableOpacity style={styles.doneBtn} onPress={onClose}>
                      <Text style={styles.doneText}>Done</Text>
                    </TouchableOpacity>
                  </View>
                ) : (
                  <View style={styles.content}>
                    {showConfirm ? (
                      <View style={styles.confirmWrap}>
                        <Text style={styles.confirmTitle}>Confirm report</Text>
                        <Text style={styles.confirmDesc}>You're about to submit a report. This will be reviewed by our team.</Text>
                        <View style={{ marginTop: 12 }}>
                          <Text style={[styles.label, { marginTop: 0 }]}>Selected reasons</Text>
                          {selectedReasons.map((r) => (
                            <Text key={r} style={styles.confirmReasonItem}>• {r === 'Other' ? `Other: ${otherText.trim()}` : r}</Text>
                          ))}
                          {description ? (
                            <>
                              <Text style={[styles.label, { marginTop: 8 }]}>Description</Text>
                              <Text style={styles.confirmReasonItem}>{description}</Text>
                            </>
                          ) : null}
                        </View>

                        {!!errorText && <Text style={styles.errorText}>{errorText}</Text>}

                        <View style={styles.confirmActions}>
                          <TouchableOpacity style={styles.confirmCancelBtn} onPress={() => setShowConfirm(false)} disabled={submitting}>
                            <Text style={styles.cancelText}>Cancel</Text>
                          </TouchableOpacity>
                          <TouchableOpacity style={[styles.confirmConfirmBtn, submitting && styles.submitBtnDisabled]} onPress={performSubmit} disabled={submitting}>
                            {submitting ? <ActivityIndicator color="#fff" /> : <Text style={styles.submitText}>Confirm & Submit</Text>}
                          </TouchableOpacity>
                        </View>
                      </View>
                    ) : (
                    <View>
                      <View style={styles.headerRow}>
                        <Text style={styles.title}>Report User</Text>
                        <TouchableOpacity onPress={onClose} style={styles.closeBtn} disabled={submitting}><Feather name="x" size={20} color="#6B7280" /></TouchableOpacity>
                      </View>

                      <Text style={styles.label}>Category (required)</Text>
                      <View style={{ marginTop: 6 }}>
                        {CATEGORIES.map((c) => {
                          const selected = selectedReasons.includes(c);
                          return (
                            <TouchableOpacity key={c} onPress={() => toggleReason(c)} style={[styles.checkboxRow, selected && styles.checkboxRowSelected]}>
                              <Feather name={selected ? 'check-square' : 'square'} size={20} color={selected ? '#EEA24B' : '#9ca3af'} />
                              <Text style={styles.checkboxLabel}>{c}</Text>
                            </TouchableOpacity>
                          );
                        })}
                      </View>

                      {selectedReasons.includes('Other') && (
                        <>
                          <Text style={styles.label}>Please specify</Text>
                          <TextInput
                            value={otherText}
                            onChangeText={setOtherText}
                            placeholder="Specify reason"
                            style={styles.textarea}
                            multiline
                            numberOfLines={3}
                            editable={!submitting}
                          />
                        </>
                      )}

                      <Text style={styles.label}>Description (optional)</Text>
                      <TextInput
                        value={description}
                        onChangeText={setDescription}
                        placeholder="Add details (optional)"
                        style={styles.textarea}
                        multiline
                        numberOfLines={4}
                        editable={!submitting}
                      />

                      {!!errorText && <Text style={styles.errorText}>{errorText}</Text>}

                      <View style={styles.actionsRow}>
                        <TouchableOpacity style={styles.cancelBtn} onPress={onClose} disabled={submitting}><Text style={styles.cancelText}>Cancel</Text></TouchableOpacity>
                        <TouchableOpacity style={[styles.submitBtn, (!canSubmit || submitting) && styles.submitBtnDisabled]} onPress={handleSubmit} disabled={!canSubmit || submitting}>
                          {submitting ? <ActivityIndicator color="#fff" /> : <Text style={styles.submitText}>Submit Report</Text>}
                        </TouchableOpacity>
                      </View>
                    </View>
                    )}
                  </View>
                )}
              </ScrollView>
            </View>
          </View>
        </TouchableWithoutFeedback>
      </KeyboardAvoidingView>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: { flex: 1, justifyContent: 'flex-end' },
  backdrop: { ...StyleSheet.absoluteFillObject, backgroundColor: 'rgba(0,0,0,0.4)' },
  sheet: { backgroundColor: '#fff', borderTopLeftRadius: 14, borderTopRightRadius: 14, padding: 16, maxHeight: '90%' },
  content: { paddingBottom: 12 },
  headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  title: { fontSize: 18, fontWeight: '700', color: '#111827' },
  closeBtn: { padding: 6 },
  label: { fontSize: 12, fontWeight: '700', color: '#374151', marginBottom: 6, marginTop: 6 },
  checkboxRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 8 },
  checkboxRowSelected: { backgroundColor: '#fff7ed', borderRadius: 8, paddingHorizontal: 8 },
  checkboxLabel: { marginLeft: 10, fontSize: 14, color: '#111827' },
  pickerWrap: { borderWidth: 1, borderColor: '#E5E7EB', borderRadius: 8, backgroundColor: '#FAFAFA' },
  textarea: { borderWidth: 1, borderColor: '#E5E7EB', borderRadius: 8, backgroundColor: '#FAFAFA', paddingHorizontal: 10, paddingVertical: 8, minHeight: 80, textAlignVertical: 'top' },
  actionsRow: { flexDirection: 'row', gap: 10, marginTop: 12 },
  cancelBtn: { flex: 1, borderWidth: 1, borderColor: '#D1D5DB', borderRadius: 8, paddingVertical: 10, alignItems: 'center' },
  cancelText: { color: '#374151', fontWeight: '600' },
  submitBtn: { flex: 2, backgroundColor: '#EEA24B', borderRadius: 8, paddingVertical: 10, alignItems: 'center' },
  submitBtnDisabled: { opacity: 0.5 },
  submitText: { color: '#fff', fontWeight: '700' },
  submitTextSmall: { color: '#fff' },
  errorText: { color: '#B91C1C', marginTop: 8 },

  successWrap: { alignItems: 'center', paddingVertical: 24 },
  successIcon: { width: 64, height: 64, borderRadius: 32, backgroundColor: '#DCFCE7', alignItems: 'center', justifyContent: 'center', marginBottom: 12 },
  successTitle: { fontSize: 18, fontWeight: '700', color: '#111827' },
  successDesc: { fontSize: 14, color: '#6B7280', textAlign: 'center', marginTop: 6 },
  doneBtn: { marginTop: 12, backgroundColor: '#EEA24B', paddingVertical: 10, paddingHorizontal: 28, borderRadius: 8 },
  doneText: { color: '#fff', fontWeight: '700' },
  confirmWrap: { paddingVertical: 8 },
  confirmTitle: { fontSize: 16, fontWeight: '700', color: '#111827', marginBottom: 6 },
  confirmDesc: { fontSize: 13, color: '#6B7280' },
  confirmReasonItem: { fontSize: 14, color: '#111827', marginTop: 6 },
  confirmActions: { flexDirection: 'row', gap: 10, marginTop: 14 },
  confirmCancelBtn: { flex: 1, borderWidth: 1, borderColor: '#D1D5DB', borderRadius: 8, paddingVertical: 10, alignItems: 'center' },
  confirmConfirmBtn: { flex: 2, backgroundColor: '#EEA24B', borderRadius: 8, paddingVertical: 10, alignItems: 'center' },
  scrollContent: { paddingBottom: 24 },
});
