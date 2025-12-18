// @ts-nocheck
import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  StatusBar,
  LayoutAnimation,
  Platform,
  UIManager,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Ionicons, Feather, MaterialCommunityIcons } from '@expo/vector-icons';

// Enable LayoutAnimation for Android
if (Platform.OS === 'android' && UIManager.setLayoutAnimationEnabledExperimental) {
  UIManager.setLayoutAnimationEnabledExperimental(true);
}

const FAQ_DATA = [
  {
    category: 'Owner',
    items: [
      { id: 'o1', q: 'How to post a project or job', a: 'Navigate to the Home tab and click the "+" icon at the bottom. Fill in your project details, budget, and location to start receiving bids from qualified contractors.' },
      { id: 'o2', q: 'How to approve contractor progress', a: 'Go to your active projects, select the current milestone, and review the submitted report. Click "Approve" to move to the next stage.' },
      { id: 'o3', q: 'How to make a payment or upload a receipt', a: 'Payments can be made via the "Finance" section. If paying offline, upload a clear photo of your deposit slip or receipt for verification.' },
      { id: 'o4', q: 'What to do if the payment is delayed', a: 'Please allow 24-48 hours for bank processing. If it takes longer, contact our support team with your transaction ID.' },
    ],
  },
  {
    category: 'Contractor',
    items: [
      { id: 'c1', q: 'How to submit progress updates', a: 'Inside your project dashboard, select "Submit Update", attach required photos, and add a brief description of the work completed.' },
      { id: 'c2', q: 'How to confirm payment', a: 'Once an owner uploads a receipt, you will receive a notification. Check your "Earnings" tab to verify the status.' },
      { id: 'c3', q: 'How to edit or resubmit a milestone', a: 'If a report is rejected, you can edit the details and attach new evidence from the project task view.' },
      { id: 'c4', q: 'What to do if the owner rejects your report', a: 'Review the feedback provided by the owner. If you believe the rejection is unfair, you can request a dispute review.' },
    ],
  },
  {
    category: 'Troubleshoot',
    items: [
      { id: 't1', q: 'Can’t upload files / images', a: 'Ensure you have given Legatura permission to access your gallery in your phone settings. Also, check that your file size is below 5MB.' },
      { id: 't2', q: 'Not receiving verification email', a: 'Check your Spam or Junk folder. If it’s not there, you can request a new code from the login screen.' },
      { id: 't3', q: 'Payment not reflected', a: 'Verify that the transaction was successful on your banking app. Reflection times vary by payment method.' },
      { id: 't4', q: 'Account login issues', a: 'Try resetting your password. If you are still locked out, your account may be under temporary review.' },
    ],
  },
];

const AccordionItem = ({ question, answer, isOpen, onPress }) => {
  return (
    <View style={styles.accordionContainer}>
      <TouchableOpacity 
        style={styles.questionRow} 
        onPress={onPress} 
        activeOpacity={0.7}
      >
        <Text style={[styles.questionText, isOpen && styles.activeQuestionText]}>{question}</Text>
        <Ionicons 
          name={isOpen ? "chevron-up" : "chevron-down"} 
          size={18} 
          color={isOpen ? "#EC7E00" : "#999"} 
        />
      </TouchableOpacity>
      {isOpen && (
        <View style={styles.answerContainer}>
          <Text style={styles.answerText}>{answer}</Text>
        </View>
      )}
    </View>
  );
};

export default function HelpSupportScreen({ onBack }) {
  const insets = useSafeAreaInsets();
  const [expandedId, setExpandedId] = useState(null);

  const toggleAccordion = (id) => {
    LayoutAnimation.configureNext(LayoutAnimation.Presets.easeInEaseOut);
    setExpandedId(expandedId === id ? null : id);
  };

  return (
    <View style={styles.container}>
      <StatusBar barStyle="dark-content" />
      
      {/* Header */}
      <View style={[styles.header, { paddingTop: insets.top + 10 }]}>
        <TouchableOpacity onPress={onBack} style={styles.backButton}>
          <Ionicons name="chevron-back" size={26} color="#333" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Help & Support</Text>
        <View style={{ width: 40 }} /> 
      </View>

      <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={styles.scrollContent}>
        <Text style={styles.mainTitle}>FAQs</Text>

        {FAQ_DATA.map((section) => (
          <View key={section.category} style={styles.sectionContainer}>
            <Text style={styles.categoryTitle}>{section.category}</Text>
            {section.items.map((item) => (
              <AccordionItem
                key={item.id}
                question={item.q}
                answer={item.a}
                isOpen={expandedId === item.id}
                onPress={() => toggleAccordion(item.id)}
              />
            ))}
          </View>
        ))}

        {/* Footer Support Section */}
        <View style={styles.footer}>
          <Text style={styles.footerIntro}>
            Didn’t find what you’re looking for? You can reach our support team directly.
          </Text>
          
          <View style={styles.contactItem}>
            <View style={styles.iconCircle}>
                <Feather name="mail" size={16} color="#EC7E00" />
            </View>
            <Text style={styles.contactLabel}>Email us: </Text>
            <Text style={styles.contactValue}>support@legatura.com</Text>
          </View>

          <View style={styles.contactItem}>
            <View style={styles.iconCircle}>
                <Feather name="clock" size={16} color="#EC7E00" />
            </View>
            <Text style={styles.contactValue}>Mon–Fri, 9:00 AM – 6:00 PM (PHT)</Text>
          </View>
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#FFF' },
  header: { 
    flexDirection: 'row', 
    alignItems: 'center', 
    justifyContent: 'space-between', 
    paddingHorizontal: 15, 
    paddingBottom: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0'
  },
  headerTitle: { fontSize: 18, fontWeight: '700', color: '#333' },
  backButton: { padding: 5 },
  
  scrollContent: { paddingHorizontal: 25, paddingBottom: 40 },
  mainTitle: { fontSize: 28, fontWeight: '800', color: '#1A1A1A', marginTop: 25, marginBottom: 15 },
  
  sectionContainer: { marginBottom: 25 },
  categoryTitle: { fontSize: 16, fontWeight: '700', color: '#AAA', marginBottom: 10, letterSpacing: 0.5 },
  
  accordionContainer: { borderBottomWidth: 1, borderBottomColor: '#F5F5F5' },
  questionRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 18 },
  questionText: { fontSize: 15, color: '#444', fontWeight: '500', flex: 0.9 },
  activeQuestionText: { color: '#1A1A1A', fontWeight: '700' },
  
  answerContainer: { paddingBottom: 18, paddingRight: 20 },
  answerText: { fontSize: 14, color: '#666', lineHeight: 22 },

  footer: { marginTop: 20, padding: 20, backgroundColor: '#F9FAFB', borderRadius: 20 },
  footerIntro: { fontSize: 14, color: '#888', lineHeight: 20, marginBottom: 20 },
  contactItem: { flexDirection: 'row', alignItems: 'center', marginBottom: 12 },
  iconCircle: { width: 32, height: 32, borderRadius: 16, backgroundColor: '#FFF4E5', justifyContent: 'center', alignItems: 'center', marginRight: 12 },
  contactLabel: { fontSize: 14, color: '#666' },
  contactValue: { fontSize: 14, color: '#333', fontWeight: '600' },
});