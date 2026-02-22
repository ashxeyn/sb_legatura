// @ts-nocheck
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  SafeAreaView,
  StatusBar,
  Alert,
  Linking,
  ActivityIndicator,
  Modal,
  ScrollView,
  RefreshControl,
  Dimensions,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { api_request } from '../../config/api';

const { width } = Dimensions.get('window');

interface Props {
  onBack: () => void;
}

type PlanTier = 'gold' | 'silver' | 'bronze';

interface Plan {
  tier: PlanTier;
  name: string;
  price: number;
  icon: keyof typeof Ionicons.glyphMap;
  color: string;
  gradient: string[];
  features: string[];
}

interface Subscription {
  name: string;
  plan_name: string;
  plan_key: string;
  expires_at: string;
  benefits: string[];
}

const PLANS: Record<PlanTier, Plan> = {
  gold: {
    tier: 'gold',
    name: 'Gold Tier',
    price: 1999,
    icon: 'trophy',
    color: '#F59E0B',
    gradient: ['#FBBF24', '#F59E0B'],
    features: ['Unlock AI-driven analytics', 'Unlimited bids', 'Boost bids for 3 months (Stay at the top'],
  },
  silver: {
    tier: 'silver',
    name: 'Silver Tier',
    price: 1499,
    icon: 'star',
    color: '#6B7280',
    gradient: ['#9CA3AF', '#6B7280'],
    features: ['25 Bids per month', 'Boosted Bid (Stay at the top)'],
  },
  bronze: {
    tier: 'bronze',
    name: 'Bronze Tier',
    price: 999,
    icon: 'leaf',
    color: '#B45309',
    gradient: ['#D97706', '#B45309'],
    features: ['10 Bids per month'],
  },
};

export default function SubscriptionScreen({ onBack }: Props) {
  const [selectedPlan, setSelectedPlan] = useState<PlanTier>('gold');
  const [loading, setLoading] = useState(false);
  const [activeTab, setActiveTab] = useState<'overview' | 'plans'>('overview');
  const [subscription, setSubscription] = useState<Subscription | null>(null);
  const [fetching, setFetching] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [showConfirmSubscribe, setShowConfirmSubscribe] = useState(false);
  const [showConfirmCancel, setShowConfirmCancel] = useState(false);
  const [showSuccess, setShowSuccess] = useState(false);

  const isAlreadySubscribed = subscription && subscription.plan_key === selectedPlan;

  useEffect(() => {
    fetchSubscriptionData();
  }, []);

  const fetchSubscriptionData = async () => {
    setFetching(true);
    try {
      const response = await api_request('/subs/modal-data', { method: 'GET' });
      if (response.success && response.data) {
        setSubscription(response.data.subscription || null);
      }
    } catch (error) {
      console.error('Failed to fetch subscription:', error);
    } finally {
      setFetching(false);
    }
  };

  const handleSubscribe = async () => {
    setLoading(true);
    try {
      const returnUrl = `exp://192.168.100.27:8081/--/payment-callback?subscription=1`;

      const response = await api_request('/api/subscribe/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          plan_tier: selectedPlan,
          return_url: returnUrl
        }),
      });

      if (response.success && response.data?.checkout_url) {
        await Linking.openURL(response.data.checkout_url);
        pollForSubscriptionUpdate();
      } else {
        Alert.alert('Checkout Error', response.message || 'Unable to create checkout session');
      }
    } catch (error) {
      Alert.alert('Error', 'Could not initiate subscription checkout.');
    } finally {
      setLoading(false);
      setShowConfirmSubscribe(false);
    }
  };

  const pollForSubscriptionUpdate = async () => {
    let attempts = 0;
    const interval = setInterval(async () => {
      attempts++;

      if (attempts > 20) {
        clearInterval(interval);
        Alert.alert('Timeout', 'Subscription not detected yet. Please check your payments.');
        return;
      }

      try {
        const response = await api_request('/subs/modal-data', { method: 'GET' });
        if (response.success && response.data?.subscription) {
          clearInterval(interval);
          setSubscription(response.data.subscription);
          setShowSuccess(true);
          setActiveTab('overview');
        }
      } catch (error) {
        console.error('Polling error:', error);
      }
    }, 3000);
  };

  const onRefresh = async () => {
    try {
      setRefreshing(true);
      await fetchSubscriptionData();
    } catch (e) {
      console.warn('Refresh failed', e);
    } finally {
      setRefreshing(false);
    }
  };

  const handleCancelSubscription = async () => {
    setLoading(true);
    try {
      const response = await api_request('/api/subscribe/cancel', { method: 'POST' });
      if (response.success) {
        Alert.alert('Cancelled', 'Subscription cancelled successfully.');
        await fetchSubscriptionData();
        setShowConfirmCancel(false);
      } else {
        Alert.alert('Error', response.message || 'Unable to cancel subscription');
      }
    } catch (error) {
      Alert.alert('Error', 'Cancellation failed');
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString: any) => {
    if (!dateString) return 'N/A';

    let d: any = dateString;

    // If already a Date
    if (d instanceof Date) {
      if (isNaN(d.getTime())) return 'N/A';
    }

    // Numeric timestamps (seconds or ms)
    if (typeof d === 'number') {
      d = d.toString().length === 10 ? new Date(d * 1000) : new Date(d);
    } else if (typeof d === 'string') {
      const s = d.trim();

      // /Date(1600000000000)/ or Date(1600000000000)
      const msMatch = s.match(/\/?\(?Date\(?(\d{10,13})\)?\)?\/?/);
      if (msMatch) {
        d = new Date(parseInt(msMatch[1], 10));
      } else if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/.test(s)) {
        // replace space between date and time with T to ensure parsing
        d = new Date(s.replace(' ', 'T'));
      } else {
        // Try Date constructor first
        d = new Date(s);
        if (isNaN(d.getTime())) {
          // Fallback: parse MonthName DD, YYYY (e.g., March 22, 2026)
          const m = s.match(/^([A-Za-z]+)\s+(\d{1,2}),\s*(\d{4})$/);
          if (m) {
            const monthNames: Record<string, number> = {
              january: 0, february: 1, march: 2, april: 3, may: 4, june: 5,
              july: 6, august: 7, september: 8, october: 9, november: 10, december: 11,
            };
            const monthIndex = monthNames[m[1].toLowerCase()];
            if (monthIndex !== undefined) {
              d = new Date(parseInt(m[3], 10), monthIndex, parseInt(m[2], 10));
            }
          }
        }
      }
    }

    if (!d || isNaN(d.getTime())) return 'N/A';

    return d.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  const renderOverviewTab = () => (
    <View style={styles.tabContent}>
      {fetching ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={PLANS.gold.color} />
        </View>
      ) : subscription ? (
        <View>
          <LinearGradient
            colors={['#FFFFFF', '#F9FAFB']}
            style={styles.activeSubscriptionCard}
          >
            <View style={styles.activeSubscriptionHeader}>
              <View style={[styles.activeBadge, { backgroundColor: PLANS[subscription.plan_key as PlanTier]?.color || PLANS.gold.color }]}>
                <Ionicons
                  name={PLANS[subscription.plan_key as PlanTier]?.icon || 'trophy'}
                  size={24}
                  color="#FFFFFF"
                />
              </View>

            </View>

            <View style={styles.activeDates}>
              <View style={styles.dateItem}>
                <Ionicons name="calendar-outline" size={16} color="#6B7280" />
                <Text style={styles.dateLabel}>Expires on</Text>
              </View>
              <Text style={styles.dateValue}>
                {formatDate(subscription.expires_at || subscription.expiration_date || subscription.expires_on)}
              </Text>
            </View>

            <View style={styles.divider} />

            <Text style={styles.benefitsTitle}>Your Benefits</Text>
            <View style={styles.benefitsList}>
              {(subscription.benefits || []).length > 0 ? (
                subscription.benefits.map((benefit, index) => (
                  <View key={index} style={styles.benefitItem}>
                    <Ionicons name="checkmark-circle" size={20} color="#10B981" />
                    <Text style={styles.benefitText}>{benefit}</Text>
                  </View>
                ))
              ) : (
                <Text style={styles.noBenefits}>No benefits listed</Text>
              )}
            </View>

            <TouchableOpacity
              style={styles.cancelButton}
              onPress={() => setShowConfirmCancel(true)}
              disabled={loading}
            >
              <Text style={styles.cancelButtonText}>Cancel Subscription</Text>
            </TouchableOpacity>
          </LinearGradient>

          <View style={styles.otherPlansSection}>
            <Text style={styles.sectionTitle}>Explore Other Plans</Text>
            <ScrollView horizontal showsHorizontalScrollIndicator={false}>
              {Object.values(PLANS)
                .filter(plan => plan.tier !== subscription.plan_key)
                .map(plan => (
                  <TouchableOpacity
                    key={plan.tier}
                    style={styles.otherPlanCard}
                    onPress={() => {
                      setSelectedPlan(plan.tier);
                      setActiveTab('plans');
                    }}
                  >
                    <LinearGradient
                      colors={plan.gradient}
                      style={styles.otherPlanGradient}
                      start={{ x: 0, y: 0 }}
                      end={{ x: 1, y: 1 }}
                    >
                      <Ionicons name={plan.icon} size={24} color="#FFFFFF" />
                    </LinearGradient>
                    <Text style={styles.otherPlanName}>{plan.name}</Text>
                    <Text style={styles.otherPlanPrice}>₱ {plan.price.toLocaleString()}</Text>
                  </TouchableOpacity>
                ))}
            </ScrollView>
          </View>
        </View>
      ) : (
        <View style={styles.emptyState}>
          <Ionicons name="card-outline" size={64} color="#D1D5DB" />
          <Text style={styles.emptyStateTitle}>No Active Subscription</Text>
          <Text style={styles.emptyStateText}>
            Choose a plan that fits your needs and start enjoying premium features
          </Text>
          <TouchableOpacity
            style={styles.browsePlansButton}
            onPress={() => setActiveTab('plans')}
          >
            <Text style={styles.browsePlansButtonText}>Browse Plans</Text>
          </TouchableOpacity>
        </View>
      )}
    </View>
  );

  const renderPlansTab = () => (
    <View style={styles.tabContent}>
      <View style={styles.plansHeader}>
        <Text style={styles.plansTitle}>Choose Your Plan</Text>
      </View>

      <View style={styles.plansList}>
        {Object.values(PLANS).map((plan) => (
          <TouchableOpacity
            key={plan.tier}
            style={[
              styles.planCard,
              selectedPlan === plan.tier && styles.selectedPlanCard,
            ]}
            onPress={() => setSelectedPlan(plan.tier)}
            activeOpacity={0.7}
          >
            <LinearGradient
              colors={plan.gradient}
              style={styles.planGradient}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
            >
              <Ionicons name={plan.icon} size={28} color="#FFFFFF" />
            </LinearGradient>

            <View style={styles.planContent}>
              <View style={styles.planHeader}>
                <Text style={styles.planName}>{plan.name}</Text>
                <Text style={styles.planPrice}>
                  ₱ {plan.price.toLocaleString()}
                  <Text style={styles.planPeriod}>/mo</Text>
                </Text>
              </View>

              {selectedPlan === plan.tier && (
                <View style={styles.selectedBadge}>
                  <Ionicons name="checkmark-circle" size={20} color="#10B981" />
                </View>
              )}
            </View>

            <View style={styles.planFeatures}>
              {plan.features.map((feature, index) => (
                <View key={index} style={styles.planFeatureItem}>
                  <Ionicons name="checkmark" size={16} color="#10B981" />
                  <Text style={styles.planFeatureText}>{feature}</Text>
                </View>
              ))}
            </View>
          </TouchableOpacity>
        ))}
      </View>

      <TouchableOpacity
        style={[
          styles.subscribeButton,
          (loading || isAlreadySubscribed) && styles.subscribeButtonDisabled,
        ]}
        onPress={() => setShowConfirmSubscribe(true)}
        disabled={loading || isAlreadySubscribed}
      >
        {loading ? (
          <View style={styles.subscribeGradient}>
            <ActivityIndicator color="#FFFFFF" />
          </View>
        ) : isAlreadySubscribed ? (
          <View style={styles.subscribeDisabledContent}>
            <Text style={styles.subscribeButtonSubtext}>
            </Text>
          </View>
        ) : (
          <LinearGradient
            colors={['#F59E0B', '#D97706']}
            style={styles.subscribeGradient}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
          >
            <Text style={styles.subscribeButtonText}>Subscribe Now</Text>
            <Text style={styles.subscribeButtonSubtext}>
              {PLANS[selectedPlan].name} • ₱ {PLANS[selectedPlan].price.toLocaleString()}/mo
            </Text>
          </LinearGradient>
        )}
      </TouchableOpacity>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#EC7E00" />

      <LinearGradient
        colors={["#EC7E00", "#F9A826"]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.headerGradient}
      >
        <View style={styles.headerContent}>
          <TouchableOpacity style={styles.backButton} onPress={onBack}>
            <Ionicons name="arrow-back" size={24} color="#FFF" />
          </TouchableOpacity>

          <View style={styles.headerTextContainer}>
            <Text style={styles.headerTitle}>Subscription</Text>
            <Text style={styles.headerSubtitle}>Manage your plan & billing</Text>
          </View>

        </View>


      </LinearGradient>

      <View style={styles.tabBar}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'overview' && styles.activeTab]}
          onPress={() => setActiveTab('overview')}
        >
          <Text style={[styles.tabText, activeTab === 'overview' && styles.activeTabText]}>
            Overview
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'plans' && styles.activeTab]}
          onPress={() => setActiveTab('plans')}
        >
          <Text style={[styles.tabText, activeTab === 'plans' && styles.activeTabText]}>
            Plans
          </Text>
        </TouchableOpacity>
      </View>

      <ScrollView
        style={styles.content}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.contentContainer}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={PLANS[selectedPlan].color}
            colors={[PLANS[selectedPlan].color]}
          />
        }
      >
        {activeTab === 'overview' ? renderOverviewTab() : renderPlansTab()}
      </ScrollView>

      {/* Confirmation Modals */}
      <Modal visible={showConfirmSubscribe} transparent animationType="fade">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalIconContainer}>
              <LinearGradient
                colors={['#F59E0B', '#D97706']}
                style={styles.modalIcon}
              >
                <Ionicons name="card" size={32} color="#FFFFFF" />
              </LinearGradient>
            </View>
            <Text style={styles.modalTitle}>Confirm Subscription</Text>
            <Text style={styles.modalMessage}>
              You're about to subscribe to the {PLANS[selectedPlan].name} plan.
              You'll be redirected to complete your payment.
            </Text>
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.modalCancelButton}
                onPress={() => setShowConfirmSubscribe(false)}
              >
                <Text style={styles.modalCancelText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.modalConfirmButton, loading && styles.modalConfirmButtonDisabled]}
                onPress={handleSubscribe}
                disabled={loading}
              >
                <LinearGradient
                  colors={['#F59E0B', '#D97706']}
                  style={[styles.modalConfirmGradient, { justifyContent: 'center' }]}
                >
                  {loading ? (
                    <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'center' }}>
                      <ActivityIndicator color="#FFFFFF" />
                      <Text style={[styles.modalConfirmText, { marginLeft: 8 }]}>Processing...</Text>
                    </View>
                  ) : (
                    <Text style={styles.modalConfirmText}>Proceed to Payment</Text>
                  )}
                </LinearGradient>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      <Modal visible={showConfirmCancel} transparent animationType="fade">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={[styles.modalIconContainer, styles.warningIcon]}>
              <Ionicons name="warning" size={32} color="#DC2626" />
            </View>
            <Text style={styles.modalTitle}>Cancel Subscription?</Text>
            <Text style={styles.modalMessage}>
              You'll lose access to premium features at the end of your current billing period.
              This action cannot be undone.
            </Text>
            <View style={styles.modalActions}>
              <TouchableOpacity
                style={styles.modalCancelButton}
                onPress={() => setShowConfirmCancel(false)}
              >
                <Text style={styles.modalCancelText}>Keep Subscription</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.modalConfirmButton, styles.dangerButton]}
                onPress={handleCancelSubscription}
              >
                <Text style={styles.dangerButtonText}>Yes, Cancel</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      <Modal visible={showSuccess} transparent animationType="fade">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={[styles.modalIconContainer, styles.successIcon]}>
              <Ionicons name="checkmark-circle" size={48} color="#10B981" />
            </View>
            <Text style={styles.modalTitle}>Subscription Successful!</Text>
            <Text style={styles.modalMessage}>
              Welcome to {subscription?.name || PLANS[selectedPlan].name}!
              You now have access to all premium features.
            </Text>
            <TouchableOpacity
              style={styles.gotItButton}
              onPress={() => setShowSuccess(false)}
            >
              <LinearGradient
                colors={['#F59E0B', '#D97706']}
                style={styles.gotItGradient}
              >
                <Text style={styles.gotItText}>Got It</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  backButton: {
    padding: 8,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1F2937',
  },
  headerRight: {
    width: 40,
  },
  tabBar: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  tab: {
    flex: 1,
    paddingVertical: 10,
    alignItems: 'center',
    borderRadius: 8,
  },
  activeTab: {
    backgroundColor: '#FEF3C7',
  },
  tabText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6B7280',
  },
  activeTabText: {
    color: '#F59E0B',
  },
  content: {
    flex: 1,
  },
  contentContainer: {
    padding: 16,
    paddingBottom: 32,
  },
  tabContent: {
    flex: 1,
  },
  loadingContainer: {
    padding: 40,
    alignItems: 'center',
  },

  // Overview Tab Styles
  activeSubscriptionCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 20,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 15,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#F3F4F6',
  },
  activeSubscriptionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  activeBadge: {
    width: 56,
    height: 56,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  activeInfo: {
    flex: 1,
  },
  activeLabel: {
    fontSize: 12,
    color: '#6B7280',
    marginBottom: 4,
  },
 activePlanName: {
  fontSize: 20,
  fontWeight: '800',
  color: '#F59E0B', // Orange color matching your accent
},
  activeDates: {
    backgroundColor: '#F9FAFB',
    borderRadius: 12,
    padding: 12,
    marginBottom: 16,
  },
  dateItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 4,
  },
  dateLabel: {
    fontSize: 12,
    color: '#6B7280',
    marginLeft: 4,
  },
  dateValue: {
    fontSize: 14,
    fontWeight: '600',
    color: '#1F2937',
  },
  divider: {
    height: 1,
    backgroundColor: '#F3F4F6',
    marginVertical: 16,
  },
  benefitsTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1F2937',
    marginBottom: 12,
  },
  benefitsList: {
    marginBottom: 20,
  },
  benefitItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  benefitText: {
    fontSize: 14,
    color: '#374151',
    marginLeft: 12,
    flex: 1,
  },
  noBenefits: {
    fontSize: 14,
    color: '#9CA3AF',
    fontStyle: 'italic',
  },
  cancelButton: {
    paddingVertical: 14,
    alignItems: 'center',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#FEE2E2',
    backgroundColor: '#FEF2F2',
  },
  cancelButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#DC2626',
  },
  otherPlansSection: {
    marginTop: 24,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1F2937',
    marginBottom: 16,
  },
  otherPlanCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 16,
    marginRight: 12,
    width: width * 0.4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
    borderWidth: 1,
    borderColor: '#F3F4F6',
  },
  otherPlanGradient: {
    width: 48,
    height: 48,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 12,
  },
  otherPlanName: {
    fontSize: 16,
    fontWeight: '700',
    color: '#1F2937',
    marginBottom: 4,
  },
  otherPlanPrice: {
    fontSize: 14,
    fontWeight: '600',
    color: '#F59E0B',
  },
  emptyState: {
    alignItems: 'center',
    padding: 40,
  },
  emptyStateTitle: {
    fontSize: 20,
    fontWeight: '700',
    color: '#1F2937',
    marginTop: 16,
    marginBottom: 8,
  },
  emptyStateText: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: 24,
  },
  browsePlansButton: {
    backgroundColor: '#F59E0B',
    paddingHorizontal: 24,
    paddingVertical: 12,
    borderRadius: 12,
  },
  browsePlansButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
  },

  // Plans Tab Styles
  plansHeader: {
    marginBottom: 20,
  },
  plansTitle: {
    fontSize: 24,
    fontWeight: '800',
    color: '#1F2937',
    marginBottom: 4,
  },
  plansSubtitle: {
    fontSize: 14,
    color: '#10B981',
    fontWeight: '600',
  },
  plansList: {
    marginBottom: 20,
  },
  planCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 20,
    padding: 20,
    marginBottom: 12,
    borderWidth: 2,
    borderColor: 'transparent',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 10,
    elevation: 3,
  },
  selectedPlanCard: {
    borderColor: '#F59E0B',
    backgroundColor: '#FFFBEB',
  },
  planGradient: {
    width: 56,
    height: 56,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 16,
  },
  planContent: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  planHeader: {
    flex: 1,
  },
  planName: {
    fontSize: 18,
    fontWeight: '700',
    color: '#1F2937',
    marginBottom: 4,
  },
  planPrice: {
    fontSize: 24,
    fontWeight: '800',
    color: '#1F2937',
  },
  planPeriod: {
    fontSize: 14,
    fontWeight: '400',
    color: '#6B7280',
  },
  selectedBadge: {
    marginLeft: 12,
  },
  planFeatures: {
    marginTop: 8,
  },
  planFeatureItem: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  planFeatureText: {
    fontSize: 14,
    color: '#374151',
    marginLeft: 8,
  },
  subscribeButton: {
    marginTop: 8,
    borderRadius: 16,
    overflow: 'hidden',
  },
  subscribeGradient: {
    paddingVertical: 18,
    alignItems: 'center',
  },
  subscribeButtonDisabled: {
    opacity: 0.9,
  },
  subscribeDisabledContent: {
    paddingVertical: 18,
    alignItems: 'center',
    width: '100%',
  },
  subscribeDisabledText: {
    fontSize: 18,
    fontWeight: '700',
    color: '#374151',
    marginBottom: 4,
  },
  subscribeButtonText: {
    fontSize: 18,
    fontWeight: '700',
    color: '#FFFFFF',
    marginBottom: 4,
  },
  subscribeButtonSubtext: {
    fontSize: 12,
    color: '#FEF3C7',
  },

  // Modal Styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  modalContent: {
    backgroundColor: '#FFFFFF',
    borderRadius: 24,
    padding: 24,
    width: '100%',
    maxWidth: 340,
    alignItems: 'center',
  },
  modalIconContainer: {
    marginBottom: 16,
  },
  modalIcon: {
    width: 64,
    height: 64,
    borderRadius: 32,
    justifyContent: 'center',
    alignItems: 'center',
  },
  warningIcon: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#FEF2F2',
    justifyContent: 'center',
    alignItems: 'center',
  },
  successIcon: {
    marginBottom: 16,
  },
  modalTitle: {
    fontSize: 20,
    fontWeight: '800',
    color: '#1F2937',
    marginBottom: 8,
    textAlign: 'center',
  },
  modalMessage: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: 24,
    lineHeight: 20,
  },
  modalActions: {
    flexDirection: 'row',
    gap: 12,
    width: '100%',
  },
  modalCancelButton: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#E5E7EB',
    alignItems: 'center',
  },
  modalCancelText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6B7280',
  },
  modalConfirmButton: {
    flex: 1,
    borderRadius: 12,
    overflow: 'hidden',
  },
  modalConfirmGradient: {
    paddingVertical: 14,
    alignItems: 'center',
  },
  modalConfirmButtonDisabled: {
    opacity: 0.8,
  },
  modalConfirmText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  /* Header gradient styles */
  headerGradient: {
    paddingBottom: 12,
  },
  headerContent: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 12,
  },
  headerTextContainer: {
    flex: 1,
    paddingHorizontal: 8,
  },
  headerSubtitle: {
    fontSize: 12,
    color: '#FFF8E1',
    marginTop: 2,
  },
  headerRight: {
    width: 56,
    alignItems: 'flex-end',
  },
  priceBadge: {
    backgroundColor: 'rgba(255,255,255,0.12)',
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 12,
  },
  priceBadgeText: {
    color: '#FFF',
    fontWeight: '700',
  },
  statsPreviewCard: {
    margin: 16,
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    paddingVertical: 12,
    paddingHorizontal: 8,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 2,
  },
  statItem: {
    flex: 1,
    alignItems: 'center',
  },
  statValue: {
    fontSize: 18,
    fontWeight: '800',
    color: '#1F2937',
  },
  statLabel: {
    fontSize: 12,
    color: '#6B7280',
    marginTop: 4,
  },
  statDivider: {
    width: 1,
    height: 36,
    backgroundColor: '#F3F4F6',
    marginHorizontal: 8,
  },
  dangerButton: {
    backgroundColor: '#DC2626',
    paddingVertical: 14,
    alignItems: 'center',
  },
  dangerButtonText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  gotItButton: {
    width: '100%',
    borderRadius: 12,
    overflow: 'hidden',
  },
  gotItGradient: {
    paddingVertical: 14,
    alignItems: 'center',
  },
  gotItText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
  },
});
