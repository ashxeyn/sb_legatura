// @ts-nocheck
import React, { useState, useEffect, useRef } from 'react';
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
    Animated,
    Dimensions,
} from 'react-native';
import { Image } from 'react-native';
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
    const [expandedPlan, setExpandedPlan] = useState<PlanTier | null>('gold');
    const tierImageUri = (tier: PlanTier) => `http://192.168.100.27:8000/img/${tier}-tier.png`;
    const [loading, setLoading] = useState(false);
    const [activeTab, setActiveTab] = useState<'overview' | 'plans'>('overview');
    const [subscription, setSubscription] = useState<Subscription | null>(null);
    const [fetching, setFetching] = useState(false);
    const [refreshing, setRefreshing] = useState(false);
    const tabTranslate = useRef(new Animated.Value(0)).current; // 0 for overview, -width for plans
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
                    <View style={styles.activeSubscriptionCard}>
                        <View style={styles.activeSubscriptionHeader}>
                                    {subscription.plan_key ? (
                                        <Image source={{ uri: tierImageUri(subscription.plan_key as PlanTier) }} style={styles.activeTierLarge} />
                                    ) : (
                                        <Ionicons name={PLANS[subscription.plan_key as PlanTier]?.icon || 'trophy'} size={64} color={PLANS[subscription.plan_key as PlanTier]?.color || PLANS.gold.color} />
                                    )}

                                <View style={styles.planInfo}>
                                <Text style={styles.planLabel}>You are currently Subscribed to</Text>
                                <Text style={styles.planNameLarge}>{subscription.plan_name || PLANS[subscription.plan_key as PlanTier]?.name || 'Gold Tier'}</Text>
                            </View>
                        </View>

                        <View style={styles.dateContainer}>
                            <View style={styles.dateRow}>
                                <Ionicons name="calendar-outline" size={18} color="#6B7280" />
                                <Text style={styles.dateLabel}>Subscription will end in:</Text>
                            </View>
                            <Text style={styles.dateValue}>{formatDate(subscription.expires_at || subscription.expiration_date || subscription.expires_on)}</Text>
                        </View>

                        <Text style={styles.benefitsTitle}>Benefits being enjoyed:</Text>
                        <View style={styles.benefitsCard}>
                            <View style={styles.benefitsList}>
                                {(subscription.benefits || []).map((benefit, idx) => (
                                    <View key={idx} style={styles.benefitItem}>
                                        <View style={styles.benefitIcon}><Ionicons name="star" size={16} color="#F59E0B" /></View>
                                        <Text style={styles.benefitText}>{benefit}</Text>
                                    </View>
                                ))}
                            </View>
                        </View>
                    </View>

                    <Text style={styles.otherPlansTitle}>Other plans:</Text>
                    <View style={styles.otherPlansList}>
                        {Object.values(PLANS).filter(p => p.tier !== subscription.plan_key).map(plan => (
                            <TouchableOpacity key={plan.tier} style={styles.otherPlanPill} onPress={() => { setSelectedPlan(plan.tier); setActiveTab('plans'); }}>
                                <Text style={styles.otherPlanNamePill}>{plan.name.toUpperCase()}</Text>
                                <Text style={styles.otherPlanPricePill}>₱ {plan.price.toLocaleString()}</Text>
                            </TouchableOpacity>
                        ))}
                    </View>

                    <TouchableOpacity style={styles.cancelButtonLarge} onPress={() => setShowConfirmCancel(true)} disabled={loading}>
                        <Text style={styles.cancelButtonLargeText}>Cancel Subscription</Text>
                    </TouchableOpacity>
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
                {Object.values(PLANS).map((plan) => {
                    const isSelected = selectedPlan === plan.tier;
                    const isExpanded = expandedPlan === plan.tier;
                    const isActive = isSelected || isExpanded;

                    return (
                        <View
                            key={plan.tier}
                            style={[
                                styles.planCard,
                                isSelected && styles.selectedPlanCard,
                            ]}
                        >
                            {/* overlapping icon outside the pill - only show when card is selected (clicked) */}
                            {isSelected && (
                                <Image
                                    source={{ uri: tierImageUri(plan.tier) }}
                                    style={[styles.tierImageOverlap, isSelected && styles.tierImageOverlapSelected]}
                                />
                            )}

                            <TouchableOpacity
                                style={styles.planHeaderRow}
                                onPress={() => {
                                    setSelectedPlan(plan.tier);
                                    setExpandedPlan(prev => (prev === plan.tier ? null : plan.tier));
                                }}
                                activeOpacity={0.85}
                            >
                                <View style={[styles.planContentCompactRow, isActive && styles.planContentCompactRowActive]}>
                                    <Text style={[styles.planNamePill, isActive && styles.planNameCentered, isSelected && styles.planNamePillSelected]}>{plan.name.toUpperCase()}</Text>

                                    <View style={[styles.priceRight, isActive && styles.priceRightAbsolute]}>
                                        <Text style={[styles.planPricePill, isSelected && styles.planPricePillSelected]}>₱ {plan.price.toLocaleString()}</Text>
                                    </View>
                                </View>
                            </TouchableOpacity>

                            {plan.tier === 'gold' && (
                                <View style={styles.starBadge}>
                                    <Ionicons name="star" size={14} color="#FFFFFF" />
                                </View>
                            )}
                        </View>
                    );
                })}
            </View>

            {expandedPlan && (
                <View style={styles.benefitsPanel}>
                    <Text style={styles.benefitsTitle}>You'll get:</Text>
                    <View style={styles.benefitsListCompact}>
                        {(PLANS[expandedPlan].features || []).map((feature, index) => (
                            <View key={index} style={styles.planFeatureItem}>
                                <Ionicons name="checkmark" size={16} color="#10B981" />
                                <Text style={styles.planFeatureText}>{feature}</Text>
                            </View>
                        ))}
                    </View>
                </View>
            )}


        </View>
    );

    return (
        <SafeAreaView style={styles.container}>
            <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />

            <View style={styles.headerContent}>
                <TouchableOpacity style={styles.backButton} onPress={onBack}>
                    <Ionicons name="arrow-back" size={24} color="#1F2937" />
                </TouchableOpacity>

                <View style={styles.headerTextContainer}>
                    <Text style={styles.headerTitle}>Subscription</Text>
                    <Text style={styles.headerSubtitle}>Manage your plan & billing</Text>
                </View>
            </View>

            <View style={styles.tabBar}>
                <TouchableOpacity
                    style={styles.tab}
                    onPress={() => {
                        setActiveTab('overview');
                        Animated.timing(tabTranslate, { toValue: 0, duration: 280, useNativeDriver: true }).start();
                    }}
                >
                    <Text
                        style={[
                            styles.tabText,
                            activeTab === 'overview' && styles.activeTabText,
                        ]}
                    >
                        Overview
                    </Text>
                </TouchableOpacity>

                <TouchableOpacity
                    style={styles.tab}
                    onPress={() => {
                        setActiveTab('plans');
                        Animated.timing(tabTranslate, { toValue: -width, duration: 280, useNativeDriver: true }).start();
                    }}
                >
                    <Text
                        style={[
                            styles.tabText,
                            activeTab === 'plans' && styles.activeTabText,
                        ]}
                    >
                        Plans
                    </Text>
                </TouchableOpacity>
            </View>

            <View style={styles.content}>
                <Animated.View style={{ width: width * 2, flexDirection: 'row', transform: [{ translateX: tabTranslate }] }}>
                    <ScrollView
                        style={{ width }}
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
                        {renderOverviewTab()}
                    </ScrollView>

                    <ScrollView
                        style={{ width }}
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
                        {renderPlansTab()}
                    </ScrollView>
                </Animated.View>
            </View>

            {activeTab === 'plans' && (
                <View style={styles.footerWrapper}>
                    <TouchableOpacity
                        style={[
                            styles.cancelButtonLarge,
                            (loading || isAlreadySubscribed) && styles.subscribeButtonDisabled,
                        ]}
                        onPress={() => setShowConfirmSubscribe(true)}
                        disabled={loading || isAlreadySubscribed}
                    >
                        {loading ? (
                            <View style={{ paddingVertical: 14 }}>
                                <ActivityIndicator color={PLANS[selectedPlan].color} />
                            </View>
                        ) : isAlreadySubscribed ? (
                            <Text style={styles.cancelButtonLargeText}>Already Subscribed</Text>
                        ) : (
                            <View style={{ alignItems: 'center' }}>
                                <Text style={styles.cancelButtonLargeText}>Subscribe Now</Text>

                            </View>
                        )}
                    </TouchableOpacity>
                </View>
            )}

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
                        {subscription ? (
                            <Text style={styles.modalMessage}>
                                You're about to subscribe to the {PLANS[selectedPlan].name} plan. This will replace your current subscription. You will be redirected to complete your payment.
                            </Text>
                        ) : (
                            <Text style={styles.modalMessage}>
                                You're about to subscribe to the {PLANS[selectedPlan].name} plan. You will be redirected to complete your payment.
                            </Text>
                        )}
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
        justifyContent: 'center',
        backgroundColor: '#FFFFFF',
        borderBottomWidth: 1,
        borderBottomColor: '#E5E7EB',
    },
    tab: {
        flex: 1,
        alignItems: 'center',
        paddingVertical: 12,
        position: 'relative',
    },
    tabText: {
        fontSize: 16,
        fontWeight: '600',
        color: '#6B7280',
    },
    activeTabText: {
        color: '#1F2937',
        borderBottomWidth: 3,
        borderBottomColor: '#F59E0B',
        paddingBottom: 6,
    },
    content: {
        flex: 1,
    },
    contentContainer: {
        padding: 16,
        paddingBottom: 140,
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
        marginBottom: 24,
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
    activeTierImage: {
        width: 28,
        height: 28,
        resizeMode: 'contain',
    },
    activeTierLarge: {
        width: 84,
        height: 84,
        resizeMode: 'contain',
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
        color: '#F59E0B',
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
    tierImage: {
        width: 28,
        height: 28,
        resizeMode: 'contain',
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

    // Tier text content (center)
    planHeader: {
        flex: 1,
        justifyContent: 'center',
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
        backgroundColor: '#EEF6FF',
        borderRadius: 20,
        paddingVertical: 16,
        paddingHorizontal: 20,
        marginBottom: 16,
        borderWidth: 0,
        shadowColor: 'transparent',
        elevation: 0,
        flexDirection: 'row',
        alignItems: 'center',
        position: 'relative',
    },
    selectedPlanCard: {
        backgroundColor: '#F59E0B',
        borderColor: 'transparent',
    },
    // Tier icon (left side)
    planGradient: {
        width: 64,
        height: 64,
        borderRadius: 16,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 16,
    },
    tierImageCompact: {
        width: 60,
        height: 60,
        resizeMode: 'contain',
    },
    tierImageOverlap: {
        width: 90,
        height: 90,
        resizeMode: 'contain',
        position: 'absolute',
        left: 12,
        top: -12,
        zIndex: 4,
    },
    tierImageOverlapSelected: {
        opacity: 1,
    },
    planHeaderRow: {
        flexDirection: 'row',
        alignItems: 'center',
        position: 'relative',
    },
    planContentCompactRow: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
    },
    planContentCompactRowActive: {
        justifyContent: 'center',
    },
    // Tier text content (center)
    planHeader: {
        flex: 1,
        justifyContent: 'center',
    },

    planName: {
        fontSize: 18,
        fontWeight: '700',
        color: '#1F2937',
        textAlign: 'left', // default left aligned
    },
    planNameCentered: {
        textAlign: 'center', // center aligned when icon is shown or card is clicked
        flex: 1,
    },

    priceRight: {
        alignItems: 'flex-end',
        justifyContent: 'center',
        minWidth: 100,
    },


    planNamePill: {
        fontSize: 18,
        fontWeight: '700',
        color: '#0F172A',
    },
    planNamePillSelected: {
        color: '#FFFFFF',
    },
    planPricePill: {
        fontSize: 18,
        fontWeight: '800',
        color: '#0F172A',
    },

    planPricePillSelected: {
        color: '#FFFFFF',
    },
    priceRight: {
        alignItems: 'flex-end',
        minWidth: 140,
    },
    priceRightAbsolute: {
        position: 'absolute',
        right: 20,
    },
    planPeriod: {
        fontSize: 14,
        fontWeight: '400',
        color: '#6B7280',
    },
    selectedBadge: {
        marginLeft: 12,
    },
    headerRightCompact: {
        flexDirection: 'row',
        alignItems: 'center',
        marginLeft: 8,
    },
    planGradientSelected: {
        opacity: 0.95,
    },
    // Adjust star badge so it doesn’t overlap price
    starBadge: {
        position: 'absolute',
        right: 8,
        top: 8,
        backgroundColor: '#D97706',
        width: 28,
        height: 28,
        borderRadius: 14,
        alignItems: 'center',
        justifyContent: 'center',
        elevation: 3,
        zIndex: 2,
    },
    planFeaturesExpanded: {
        marginTop: 12,
        paddingTop: 8,
        borderTopWidth: 1,
        borderTopColor: '#F3F4F6',
    },
    benefitsPanel: {
        backgroundColor: '#FFFFFF',
        borderRadius: 12,
        padding: 16,
        marginTop: 12,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.03,
        shadowRadius: 6,
        elevation: 2,
        borderWidth: 1,
        borderColor: '#F3F4F6',
    },
    benefitsListCompact: {
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
    /* New styles matching HTML design */
    tierIconLarge: {
        width: 64,
        height: 64,
        borderRadius: 18,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 16,
        shadowColor: '#F59E0B',
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.12,
        shadowRadius: 15,
        elevation: 4,
    },
    planInfo: {
        flex: 1,
    },
    planLabel: {
        fontSize: 13,
        color: '#6B7280',
        marginBottom: 4,
        letterSpacing: 0.3,
    },
    planNameLarge: {
        fontSize: 22,
        fontWeight: '800',
        color: '#F59E0B',
        letterSpacing: -0.5,
    },
    dateContainer: {
        backgroundColor: '#F9FAFB',
        borderRadius: 16,
        padding: 16,
        marginBottom: 20,
        borderWidth: 1,
        borderColor: '#F3F4F6',
    },
    dateRow: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 8,
    },
    dateLabel: {
        fontSize: 13,
        color: '#6B7280',
        marginLeft: 8,
    },
    dateValue: {
        fontSize: 15,
        fontWeight: '600',
        color: '#1F2937',
        marginTop: 4,
    },
    benefitsCard: {
        backgroundColor: '#F3F7FB',
        borderRadius: 12,
        padding: 16,
        marginBottom: 12,
    },
    benefitIcon: {
        width: 28,
        height: 28,
        borderRadius: 10,
        backgroundColor: '#FEF3C7',
        alignItems: 'center',
        justifyContent: 'center',
    },
    otherPlansTitle: {
        fontSize: 18,
        fontWeight: '700',
        color: '#1F2937',
        marginTop: 8,
        marginBottom: 12,
    },
    otherPlansList: {
        flexDirection: 'column',
        gap: 12,
        marginBottom: 18,
    },
    otherPlanPill: {
        backgroundColor: '#EEF6FF',
        borderRadius: 16,
        paddingVertical: 18,
        paddingHorizontal: 20,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
    },
    otherPlanNamePill: {
        fontSize: 16,
        fontWeight: '700',
        color: '#0F172A',
    },
    otherPlanPricePill: {
        fontSize: 20,
        fontWeight: '800',
        color: '#0F172A',
    },
    cancelButtonLarge: {
        width: '100%',
        padding: 16,
        borderRadius: 16,
        borderWidth: 2,
        borderColor: '#F59E0B',
        backgroundColor: '#FFFFFF',
        alignItems: 'center',
        marginTop: 8,
    },
    cancelButtonLargeText: {
        color: '#F59E0B',
        fontSize: 16,
        fontWeight: '700',
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
        backgroundColor: '#F3F4F6',
        borderRadius: 16,
    },
    subscribeDisabledText: {
        fontSize: 16,
        fontWeight: '600',
    },
    subscribeButtonText: {
        fontSize: 18,
        fontWeight: '700',
        marginBottom: 4,
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
    headerContent: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#FFFFFF',
        paddingHorizontal: 16,
        paddingVertical: 12,
        borderBottomWidth: 1,
        borderBottomColor: '#F3F4F6',
        elevation: 2,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.05,
        shadowRadius: 2,
    },
    headerTextContainer: {
        flex: 1,
        marginLeft: 8,
    },
    headerSubtitle: {
        fontSize: 13,
        color: '#6B7280',
        marginTop: 2,
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
    footerWrapper: {
        position: 'absolute',
        left: 0,
        right: 0,
        bottom: 0,
        padding: 16,
        backgroundColor: '#FFFFFF',
        borderTopWidth: 1,
        borderTopColor: '#F3F4F6',
        elevation: 8,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: -2 },
        shadowOpacity: 0.06,
        shadowRadius: 8,
        zIndex: 20,
    },
});
