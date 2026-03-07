// @ts-nocheck
import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  TextInput,
  ActivityIndicator,
  Alert,
  StatusBar,
  Animated,
  Platform,
  KeyboardAvoidingView,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import MaterialIcons from 'react-native-vector-icons/MaterialIcons';
import Feather from 'react-native-vector-icons/Feather';
import { Image } from 'expo-image';
import { review_service } from '../../services/review_service';
import { api_request } from '../../config/api';
import { api_config } from '../../config/api';

/* ─── Design tokens ──────────────────────────────────────────────── */
const BRAND   = '#EEA24B';
const BRAND_D = '#C96A00';
const BRAND_L = '#FFF8EE';
const SURFACE = '#ffffff';
const BG      = '#F8F9FA';
const TEXT_P  = '#1e293b';
const TEXT_S  = '#64748B';
const TEXT_M  = '#94A3B8';
const BORDER  = '#E8EAED';
const STAR_EMPTY  = '#D1D5DB';
const STAR_FILL   = '#FBBF24';
const SUCCESS     = '#22C55E';
const CARD_R = 10;

const STAR_LABELS = ['', 'Terrible', 'Poor', 'Average', 'Good', 'Excellent'];
const STAR_COLORS = ['', '#EF4444', '#F97316', '#EAB308', '#84CC16', '#22C55E'];

// Helper to build profile image URL
const getImageUrl = (filePath?: string) => {
  if (!filePath) return undefined;
  const p = String(filePath).trim();
  if (p.startsWith('http://') || p.startsWith('https://')) return p;
  if (p.includes('/storage/')) {
    return p.startsWith('/') ? `${api_config.base_url}${p}` : `${api_config.base_url}/${p}`;
  }
  if (p.includes('/')) return `${api_config.base_url}/storage/${p}`;
  return `${api_config.base_url}/storage/profiles/${p}`;
};

interface WriteReviewProps {
  projectId: number;
  revieweeUserId: number;
  onClose: () => void;
  onReviewSubmitted?: () => void;
}

export default function WriteReview({ projectId, revieweeUserId, onClose, onReviewSubmitted }: WriteReviewProps) {
  const insets = useSafeAreaInsets();

  /* ─── State ───────────────────────────────────────────────── */
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [canReview, setCanReview] = useState(true);
  const [canReviewReason, setCanReviewReason] = useState('');

  const [rating, setRating] = useState(0);
  const [comment, setComment] = useState('');
  const [hoverStar, setHoverStar] = useState(0);
  const [resolvedRevieweeId, setResolvedRevieweeId] = useState(revieweeUserId);

  const [reviewee, setReviewee] = useState<{
    username: string;
    profile_pic?: string;
    company_name?: string;
    role?: string;
  } | null>(null);
  const [projectTitle, setProjectTitle] = useState('');

  // Animations
  const starScales = useRef([1, 2, 3, 4, 5].map(() => new Animated.Value(1))).current;
  const successScale = useRef(new Animated.Value(0)).current;
  const successOpacity = useRef(new Animated.Value(0)).current;

  /* ─── Load data ───────────────────────────────────────────── */
  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    setLoading(true);
    try {
      // Determine the actual reviewee user ID — use prop, or get from can_review API as fallback
      let actualRevieweeId = revieweeUserId;

      // Check if user can review
      const canRes = await review_service.can_review(projectId);
      if (canRes.success && canRes.data) {
        setCanReview(canRes.data.can_review);
        if (!canRes.data.can_review) {
          setCanReviewReason(canRes.data.reason || 'You cannot leave a review at this time.');
        }
        // Use the reviewee_user_id from can_review if our prop is 0 or missing
        if ((!actualRevieweeId || actualRevieweeId === 0) && canRes.data.reviewee_user_id) {
          actualRevieweeId = canRes.data.reviewee_user_id;
        }
        // Always prefer the can_review reviewee_user_id (it's authoritative)
        if (canRes.data.reviewee_user_id) {
          actualRevieweeId = canRes.data.reviewee_user_id;
        }
      }

      // Store the resolved reviewee ID so handleSubmit can use it
      setResolvedRevieweeId(actualRevieweeId);

      // Get reviewee info using the profile/view endpoint
      if (actualRevieweeId && actualRevieweeId > 0) {
        const profileRes = await api_request(`/api/profile/view/${actualRevieweeId}`, {
          method: 'GET',
          headers: { Accept: 'application/json' },
        });
        if (profileRes?.success) {
          const profileData = profileRes.data?.data || profileRes.data || {};
          const header = profileData.header || {};
          setReviewee({
            username: header.display_name || profileData.username || 'User',
            profile_pic: header.profile_pic || profileData.profile_pic,
            company_name: header.company_name || profileData.company_name,
            role: profileData.role,
          });
        }
      }

      // Get project title (use public endpoint)
      const projRes = await api_request(`/api/projects/${projectId}/public`, {
        method: 'GET',
        headers: { Accept: 'application/json' },
      });
      if (projRes?.success) {
        const projData = projRes.data?.data || projRes.data || {};
        setProjectTitle(projData.project_title || projData.title || '');
      }
    } catch (err) {
      console.error('WriteReview loadData error:', err);
    } finally {
      setLoading(false);
    }
  };

  /* ─── Star press animation ───────────────────────────────── */
  const animateStar = (index: number) => {
    Animated.sequence([
      Animated.timing(starScales[index], { toValue: 1.4, duration: 100, useNativeDriver: true }),
      Animated.spring(starScales[index], { toValue: 1, friction: 3, useNativeDriver: true }),
    ]).start();
  };

  const handleStarPress = (star: number) => {
    setRating(star);
    animateStar(star - 1);
    // Animate all stars up to the selected one
    for (let i = 0; i < star; i++) {
      setTimeout(() => animateStar(i), i * 50);
    }
  };

  /* ─── Submit ──────────────────────────────────────────────── */
  const handleSubmit = async () => {
    if (rating === 0) {
      Alert.alert('Rating Required', 'Please select a star rating before submitting.');
      return;
    }
    if (comment.trim().length < 10) {
      Alert.alert('Comment Required', 'Please write at least 10 characters in your review.');
      return;
    }

    setSubmitting(true);
    try {
      const result = await review_service.submit_review({
        project_id: projectId,
        reviewee_user_id: resolvedRevieweeId,
        rating,
        comment: comment.trim(),
      });

      if (result.success) {
        setSubmitted(true);
        // Play success animation
        Animated.parallel([
          Animated.spring(successScale, { toValue: 1, friction: 4, useNativeDriver: true }),
          Animated.timing(successOpacity, { toValue: 1, duration: 300, useNativeDriver: true }),
        ]).start();

        setTimeout(() => {
          onReviewSubmitted?.();
        }, 2500);
      } else {
        Alert.alert('Error', result.message || 'Failed to submit review.');
      }
    } catch (err) {
      Alert.alert('Error', 'Something went wrong. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  /* ─── Render: Loading ─────────────────────────────────────── */
  if (loading) {
    return (
      <SafeAreaView style={[s.container, { paddingTop: insets.top }]}>
        <StatusBar barStyle="dark-content" backgroundColor={BG} />
        <View style={s.loadingBox}>
          <ActivityIndicator size="large" color={BRAND} />
          <Text style={s.loadingText}>Loading…</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* ─── Render: Cannot review ───────────────────────────────── */
  if (!canReview) {
    return (
      <SafeAreaView style={[s.container, { paddingTop: insets.top }]}>
        <StatusBar barStyle="dark-content" backgroundColor={BG} />
        <View style={s.header}>
          <TouchableOpacity onPress={onClose} style={s.headerBtn}>
            <Feather name="x" size={22} color={TEXT_P} />
          </TouchableOpacity>
          <Text style={s.headerTitle}>Write a Review</Text>
          <View style={s.headerBtn} />
        </View>
        <View style={s.cannotReviewBox}>
          <View style={s.cannotReviewIcon}>
            <MaterialIcons name="rate-review" size={48} color={TEXT_M} />
          </View>
          <Text style={s.cannotReviewTitle}>Review Not Available</Text>
          <Text style={s.cannotReviewDesc}>{canReviewReason.replace('for this project', 'at this time')}</Text>
          <TouchableOpacity onPress={onClose} style={s.cannotReviewBtn}>
            <Text style={s.cannotReviewBtnText}>Go Back</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  /* ─── Render: Success ─────────────────────────────────────── */
  if (submitted) {
    return (
      <SafeAreaView style={[s.container, { paddingTop: insets.top }]}>
        <StatusBar barStyle="dark-content" backgroundColor={BG} />
        <View style={s.successContainer}>
          <Animated.View style={[s.successCircle, { transform: [{ scale: successScale }], opacity: successOpacity }]}>
            <MaterialIcons name="check-circle" size={80} color={SUCCESS} />
          </Animated.View>
          <Animated.View style={{ opacity: successOpacity, alignItems: 'center', width: '100%' }}>
            <Text style={s.successTitle}>Thank You!</Text>
            <Text style={s.successDesc}>Your review has been submitted successfully.</Text>
            <View style={s.successStars}>
              {[1, 2, 3, 4, 5].map(i => (
                <MaterialIcons key={i} name="star" size={28} color={i <= rating ? STAR_FILL : STAR_EMPTY} />
              ))}
            </View>
            <TouchableOpacity onPress={onClose} style={s.successBtn}>
              <Text style={s.successBtnText}>Done</Text>
            </TouchableOpacity>
          </Animated.View>
        </View>
      </SafeAreaView>
    );
  }

  /* ─── Render: Review form ─────────────────────────────────── */
  const activeStar = hoverStar || rating;
  const displayName = reviewee?.company_name || reviewee?.username || 'User';
  const profilePic = reviewee?.profile_pic ? getImageUrl(reviewee.profile_pic) : null;

  return (
    <SafeAreaView style={[s.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={BG} />
      <KeyboardAvoidingView
        style={{ flex: 1 }}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      >
        {/* Header */}
        <View style={s.header}>
          <TouchableOpacity onPress={onClose} style={s.headerBtn}>
            <Feather name="x" size={22} color={TEXT_P} />
          </TouchableOpacity>
          <Text style={s.headerTitle}>Write a Review</Text>
          <View style={s.headerBtn} />
        </View>

        <ScrollView
          style={s.scroll}
          contentContainerStyle={s.scrollContent}
          showsVerticalScrollIndicator={false}
          keyboardShouldPersistTaps="handled"
        >
          {/* Reviewee Card */}
          <View style={s.revieweeCard}>
            <View style={s.revieweeAvatar}>
              {profilePic ? (
                <Image source={{ uri: profilePic }} style={s.revieweeAvatarImg} />
              ) : (
                <View style={[s.revieweeAvatarImg, s.revieweeAvatarPlaceholder]}>
                  <Feather name="user" size={28} color={TEXT_M} />
                </View>
              )}
            </View>
            <View style={s.revieweeInfo}>
              <Text style={s.revieweeName} numberOfLines={1}>{displayName}</Text>
              {reviewee?.role && (
                <View style={s.revieweeBadge}>
                  <Text style={s.revieweeBadgeText}>
                    {reviewee.role === 'contractor' ? 'Contractor' : 'Property Owner'}
                  </Text>
                </View>
              )}
            </View>
          </View>

          {/* Project context */}
          {projectTitle ? (
            <View style={s.projectContext}>
              <Feather name="briefcase" size={14} color={TEXT_M} />
              <Text style={s.projectContextText} numberOfLines={1}>
                Project: {projectTitle}
              </Text>
            </View>
          ) : null}

          {/* Divider */}
          <View style={s.divider} />

          {/* Star Rating section */}
          <View style={s.ratingSection}>
            <Text style={s.ratingSectionTitle}>How was your experience?</Text>
            <Text style={s.ratingSectionDesc}>Tap a star to rate</Text>

            <View style={s.starsRow}>
              {[1, 2, 3, 4, 5].map(star => (
                <TouchableOpacity
                  key={star}
                  onPress={() => handleStarPress(star)}
                  onPressIn={() => setHoverStar(star)}
                  onPressOut={() => setHoverStar(0)}
                  activeOpacity={0.7}
                  style={s.starTouchable}
                >
                  <Animated.View style={{ transform: [{ scale: starScales[star - 1] }] }}>
                    <MaterialIcons
                      name={star <= activeStar ? 'star' : 'star-outline'}
                      size={48}
                      color={star <= activeStar ? STAR_FILL : STAR_EMPTY}
                    />
                  </Animated.View>
                </TouchableOpacity>
              ))}
            </View>

            {/* Rating label */}
            {activeStar > 0 && (
              <View style={[s.ratingLabelBadge, { backgroundColor: STAR_COLORS[activeStar] + '18' }]}>
                <Text style={[s.ratingLabelText, { color: STAR_COLORS[activeStar] }]}>
                  {STAR_LABELS[activeStar]}
                </Text>
              </View>
            )}
          </View>

          {/* Divider */}
          <View style={s.divider} />

          {/* Comment section */}
          <View style={s.commentSection}>
            <Text style={s.commentLabel}>Share your experience</Text>
            <Text style={s.commentHint}>
              Help others by describing your experience working with {displayName}
            </Text>
            <View style={[s.commentInputBox, comment.length > 0 && s.commentInputBoxActive]}>
              <TextInput
                style={s.commentInput}
                placeholder="What did you like or dislike? How was the quality of work, communication, and professionalism?"
                placeholderTextColor={TEXT_M}
                multiline
                numberOfLines={5}
                maxLength={1000}
                value={comment}
                onChangeText={setComment}
                textAlignVertical="top"
              />
              <View style={s.commentFooter}>
                <Text style={[s.charCount, comment.trim().length < 10 && comment.length > 0 && { color: '#EF4444' }]}>
                  {comment.length}/1000
                </Text>
                {comment.trim().length < 10 && comment.length > 0 && (
                  <Text style={s.charMinWarn}>Min 10 characters</Text>
                )}
              </View>
            </View>
          </View>

          {/* Guidelines */}
          <View style={s.guidelinesCard}>
            <View style={s.guidelinesHeader}>
              <Feather name="info" size={16} color={BRAND} />
              <Text style={s.guidelinesTitle}>Review Guidelines</Text>
            </View>
            <Text style={s.guidelineItem}>• Be honest and constructive</Text>
            <Text style={s.guidelineItem}>• Focus on work quality, timeliness, and communication</Text>
            <Text style={s.guidelineItem}>• Avoid personal attacks or inappropriate language</Text>
            <Text style={s.guidelineItem}>• Your review helps build a trustworthy community</Text>
          </View>

          {/* Spacer for button */}
          <View style={{ height: 100 }} />
        </ScrollView>

        {/* Submit button - fixed at bottom */}
        <View style={[s.submitBar, { paddingBottom: Math.max(insets.bottom, 16) }]}>
          <TouchableOpacity
            onPress={handleSubmit}
            disabled={submitting || rating === 0}
            style={[
              s.submitBtn,
              (rating === 0) && s.submitBtnDisabled,
            ]}
            activeOpacity={0.8}
          >
            {submitting ? (
              <ActivityIndicator size="small" color={SURFACE} />
            ) : (
              <>
                <MaterialIcons name="rate-review" size={20} color={SURFACE} style={{ marginRight: 8 }} />
                <Text style={s.submitBtnText}>Submit Review</Text>
              </>
            )}
          </TouchableOpacity>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

/* ─── Styles ───────────────────────────────────────────────────────── */
const s = StyleSheet.create({
  container: { flex: 1, backgroundColor: BG },

  /* Loading */
  loadingBox: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  loadingText: { marginTop: 12, fontSize: 15, color: TEXT_S },

  /* Header */
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: 16, paddingVertical: 12,
    backgroundColor: SURFACE,
    borderBottomWidth: 1, borderBottomColor: BORDER,
  },
  headerBtn: { width: 40, height: 40, borderRadius: 10, backgroundColor: BG, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { fontSize: 17, fontWeight: '700', color: TEXT_P },

  /* Scroll */
  scroll: { flex: 1 },
  scrollContent: { padding: 20 },

  /* Reviewee card */
  revieweeCard: {
    flexDirection: 'row', alignItems: 'center',
    backgroundColor: SURFACE, borderRadius: CARD_R,
    padding: 16, marginBottom: 12,
    shadowColor: '#000', shadowOpacity: 0.04, shadowOffset: { width: 0, height: 2 }, shadowRadius: 8,
    elevation: 2,
  },
  revieweeAvatar: { marginRight: 14 },
  revieweeAvatarImg: { width: 56, height: 56, borderRadius: 12 },
  revieweeAvatarPlaceholder: { backgroundColor: BG, alignItems: 'center', justifyContent: 'center' },
  revieweeInfo: { flex: 1 },
  revieweeName: { fontSize: 18, fontWeight: '700', color: TEXT_P, marginBottom: 4 },
  revieweeBadge: {
    alignSelf: 'flex-start',
    backgroundColor: BRAND_L, borderRadius: 4, paddingHorizontal: 8, paddingVertical: 3,
  },
  revieweeBadgeText: { fontSize: 12, fontWeight: '600', color: BRAND_D },

  /* Project context */
  projectContext: {
    flexDirection: 'row', alignItems: 'center',
    backgroundColor: SURFACE, borderRadius: 10,
    paddingHorizontal: 14, paddingVertical: 10, marginBottom: 8,
  },
  projectContextText: { fontSize: 13, color: TEXT_S, marginLeft: 8, flex: 1 },

  /* Divider */
  divider: { height: 1, backgroundColor: BORDER, marginVertical: 16 },

  /* Rating section */
  ratingSection: { alignItems: 'center', marginBottom: 8 },
  ratingSectionTitle: { fontSize: 20, fontWeight: '700', color: TEXT_P, marginBottom: 4, textAlign: 'center' },
  ratingSectionDesc: { fontSize: 14, color: TEXT_M, marginBottom: 20 },

  starsRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
  starTouchable: { padding: 4 },

  ratingLabelBadge: {
    marginTop: 16, paddingHorizontal: 20, paddingVertical: 8, borderRadius: 8,
  },
  ratingLabelText: { fontSize: 15, fontWeight: '700' },

  /* Comment section */
  commentSection: { marginBottom: 16 },
  commentLabel: { fontSize: 16, fontWeight: '700', color: TEXT_P, marginBottom: 4 },
  commentHint: { fontSize: 13, color: TEXT_S, marginBottom: 12, lineHeight: 18 },
  commentInputBox: {
    backgroundColor: SURFACE, borderRadius: CARD_R, borderWidth: 1.5, borderColor: BORDER,
    overflow: 'hidden',
  },
  commentInputBoxActive: { borderColor: BRAND },
  commentInput: {
    padding: 16, fontSize: 15, color: TEXT_P, minHeight: 130,
    lineHeight: 22,
  },
  commentFooter: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    paddingHorizontal: 16, paddingBottom: 12,
  },
  charCount: { fontSize: 12, color: TEXT_M },
  charMinWarn: { fontSize: 12, color: '#EF4444' },

  /* Guidelines */
  guidelinesCard: {
    backgroundColor: BRAND_L, borderRadius: CARD_R, padding: 16,
    borderWidth: 1, borderColor: BRAND + '20',
  },
  guidelinesHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 10 },
  guidelinesTitle: { fontSize: 14, fontWeight: '700', color: BRAND_D, marginLeft: 8 },
  guidelineItem: { fontSize: 13, color: TEXT_S, lineHeight: 20, marginLeft: 4 },

  /* Submit bar */
  submitBar: {
    position: 'absolute', bottom: 0, left: 0, right: 0,
    backgroundColor: SURFACE,
    borderTopWidth: 1, borderTopColor: BORDER,
    paddingHorizontal: 20, paddingTop: 12,
  },
  submitBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    backgroundColor: BRAND, borderRadius: 10, paddingVertical: 16,
    shadowColor: BRAND, shadowOpacity: 0.3, shadowOffset: { width: 0, height: 4 }, shadowRadius: 12,
    elevation: 4,
  },
  submitBtnDisabled: { backgroundColor: '#D1D5DB' },
  submitBtnText: { fontSize: 16, fontWeight: '700', color: SURFACE },

  /* Cannot review */
  cannotReviewBox: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 32 },
  cannotReviewIcon: {
    width: 80, height: 80, borderRadius: 40, backgroundColor: BG,
    alignItems: 'center', justifyContent: 'center', marginBottom: 20,
  },
  cannotReviewTitle: { fontSize: 20, fontWeight: '700', color: TEXT_P, marginBottom: 8 },
  cannotReviewDesc: { fontSize: 15, color: TEXT_S, textAlign: 'center', lineHeight: 22, marginBottom: 24 },
  cannotReviewBtn: {
    backgroundColor: BRAND, borderRadius: 8, paddingHorizontal: 32, paddingVertical: 14,
  },
  cannotReviewBtnText: { fontSize: 15, fontWeight: '700', color: SURFACE },

  /* Success state */
  successContainer: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 32 },
  successCircle: { marginBottom: 24 },
  successTitle: { fontSize: 28, fontWeight: '800', color: TEXT_P, marginBottom: 8, textAlign: 'center' },
  successDesc: { fontSize: 16, color: TEXT_S, textAlign: 'center', marginBottom: 20, lineHeight: 22 },
  successStars: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', marginBottom: 32, gap: 4 },
  successBtn: {
    backgroundColor: BRAND, borderRadius: 10, paddingHorizontal: 48, paddingVertical: 16,
    shadowColor: BRAND, shadowOpacity: 0.3, shadowOffset: { width: 0, height: 4 }, shadowRadius: 12,
    elevation: 4,
  },
  successBtnText: { fontSize: 16, fontWeight: '700', color: SURFACE },
});
