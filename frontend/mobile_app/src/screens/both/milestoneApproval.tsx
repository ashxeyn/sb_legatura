// @ts-nocheck
import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  StatusBar,
  ActivityIndicator,
  Alert,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import { milestones_service } from '../../services/milestones_service';
import MilestoneDetail from './milestoneDetail';

// Color palette
const COLORS = {
  primary: '#1E3A5F',
  primaryLight: '#E8EEF4',
  accent: '#EC7E00',
  accentLight: '#FFF3E6',
  success: '#10B981',
  successLight: '#D1FAE5',
  warning: '#F59E0B',
  warningLight: '#FEF3C7',
  error: '#EF4444',
  errorLight: '#FEE2E2',
  info: '#3B82F6',
  infoLight: '#DBEAFE',
  background: '#FFFFFF',
  surface: '#FFFFFF',
  text: '#1E3A5F',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
  darkBlue: '#0A1628',
};

interface MilestoneItem {
  item_id: number;
  sequence_order: number;
  percentage_progress: number;
  milestone_item_title: string;
  milestone_item_description: string;
  milestone_item_cost: number;
  date_to_finish: string;
  item_status?: string;
}

interface PaymentPlan {
  plan_id: number;
  payment_mode: string;
  total_project_cost: number;
  downpayment_amount: number;
  is_confirmed: number;
}

interface Milestone {
  milestone_id: number;
  plan_id: number;
  milestone_name: string;
  milestone_description: string;
  milestone_status: string;
  setup_status: string;
  start_date: string;
  end_date: string;
  created_at: string;
  updated_at: string;
  items?: MilestoneItem[];
  payment_plan?: PaymentPlan;
}

interface MilestoneApprovalProps {
  route: {
    params: {
      projectTitle: string;
      projectDescription?: string;
      projectLocation?: string;
      contractorName: string;
      propertyType: string;
      projectStartDate: string;
      projectEndDate: string;
      totalCost: number;
      paymentMethod: string;
      milestones: Milestone[];
      userId: number;
      userRole: 'owner' | 'contractor';
      onApprovalComplete: () => void;
    };
  };
  navigation: any;
}

export default function MilestoneApproval({ route, navigation }: MilestoneApprovalProps) {
  const insets = useSafeAreaInsets();
  const {
    projectTitle,
    projectDescription,
    projectLocation,
    contractorName,
    propertyType,
    projectStartDate,
    projectEndDate,
    totalCost,
    paymentMethod,
    milestones,
    userId,
    userRole,
    onApprovalComplete,
  } = route.params;

  const [approvingMilestone, setApprovingMilestone] = useState<number | null>(null);
  const [rejectingMilestone, setRejectingMilestone] = useState<number | null>(null);
  const [selectedMilestoneDetail, setSelectedMilestoneDetail] = useState<{
    item: MilestoneItem & { parentMilestoneId: number; parentSetupStatus: string; parentMilestoneStatus: string };
    milestoneNumber: number;
    cumulativePercentage: number;
  } | null>(null);

  // Flatten all milestone items from all milestones into one array for the timeline
  const allMilestoneItems: (MilestoneItem & { parentMilestoneId: number; parentSetupStatus: string; parentMilestoneStatus: string })[] = [];
  milestones.forEach(milestone => {
    if (milestone.items && milestone.items.length > 0) {
      milestone.items.forEach(item => {
        allMilestoneItems.push({
          ...item,
          parentMilestoneId: milestone.milestone_id,
          parentSetupStatus: milestone.setup_status,
          parentMilestoneStatus: milestone.milestone_status,
        });
      });
    }
  });

  // Sort by sequence order
  allMilestoneItems.sort((a, b) => a.sequence_order - b.sequence_order);

  const formatCurrency = (amount: number) => {
    return `PHP ${amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  };

  const handleMilestonePress = (item: MilestoneItem & { parentMilestoneId: number; parentSetupStatus: string; parentMilestoneStatus: string }, milestoneNumber: number, cumulativePercentage: number) => {
    // Show milestone detail view
    setSelectedMilestoneDetail({
      item,
      milestoneNumber,
      cumulativePercentage,
    });
  };

  const handleApproveMilestone = (milestoneId: number) => {
    Alert.alert(
      'Approve Milestone Setup',
      'Are you sure you want to approve this milestone setup?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Approve',
          onPress: async () => {
            setApprovingMilestone(milestoneId);
            try {
              const response = await milestones_service.approve_milestone(milestoneId, userId);

              if (response.success) {
                Alert.alert('Success', 'Milestone setup approved successfully', [
                  {
                    text: 'OK',
                    onPress: () => {
                      if (onApprovalComplete) onApprovalComplete();
                      navigation.goBack();
                    },
                  },
                ]);
              } else {
                Alert.alert('Error', response.message || 'Failed to approve milestone');
              }
            } catch (error) {
              Alert.alert('Error', 'An unexpected error occurred');
            } finally {
              setApprovingMilestone(null);
            }
          },
        },
      ]
    );
  };

  const handleRequestChanges = (milestoneId: number) => {
    Alert.alert(
      'Request Changes',
      'Are you sure you want to request changes to this milestone setup?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Request Changes',
          style: 'destructive',
          onPress: async () => {
            setRejectingMilestone(milestoneId);
            try {
              const response = await milestones_service.reject_milestone(milestoneId, userId);

              if (response.success) {
                Alert.alert('Success', 'Change request sent to contractor', [
                  {
                    text: 'OK',
                    onPress: () => {
                      if (onApprovalComplete) onApprovalComplete();
                      navigation.goBack();
                    },
                  },
                ]);
              } else {
                Alert.alert('Error', response.message || 'Failed to request changes');
              }
            } catch (error) {
              Alert.alert('Error', 'An unexpected error occurred');
            } finally {
              setRejectingMilestone(null);
            }
          },
        },
      ]
    );
  };

  // Calculate progress - count completed milestone items
  const completedCount = allMilestoneItems.filter(item => item.item_status === 'completed').length;
  const totalCount = allMilestoneItems.length;
  const progressPercentage = totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0;

  // Find the first submitted milestone (for approval)
  const submittedMilestone = milestones.find(m => m.setup_status === 'submitted');

  // If a milestone detail is selected, show the detail view
  if (selectedMilestoneDetail) {
    return (
      <MilestoneDetail
        route={{
          params: {
            milestoneItem: selectedMilestoneDetail.item,
            milestoneNumber: selectedMilestoneDetail.milestoneNumber,
            cumulativePercentage: selectedMilestoneDetail.cumulativePercentage,
            projectTitle,
            totalMilestones: allMilestoneItems.length,
            isApproved: selectedMilestoneDetail.item.parentSetupStatus === 'approved',
            isCompleted: selectedMilestoneDetail.item.parentMilestoneStatus === 'completed',
            userRole,
            userId,
          },
        }}
        navigation={{
          goBack: () => setSelectedMilestoneDetail(null),
        }}
      />
    );
  }

  return (
    <View style={[styles.container, { paddingTop: insets.top }]}>
      <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backButton}>
          <Feather name="chevron-left" size={28} color={COLORS.text} />
        </TouchableOpacity>
        <TouchableOpacity style={styles.menuButton}>
          <Feather name="more-vertical" size={24} color={COLORS.text} />
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.scrollView} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {/* Project Info Section */}
        <View style={styles.projectSection}>
          <Text style={styles.projectTitle}>{projectTitle}</Text>
          {projectDescription && (
            <Text style={styles.projectDescription}>
              {projectDescription}
            </Text>
          )}

          {/* Budget */}
          <View style={styles.budgetRow}>
            <View style={styles.budgetIcon}>
              <Feather name="credit-card" size={16} color={COLORS.accent} />
            </View>
            <Text style={styles.budgetText}>{formatCurrency(totalCost)}</Text>
          </View>

          {/* Location */}
          {projectLocation && (
            <View style={styles.locationRow}>
              <Feather name="map-pin" size={16} color={COLORS.accent} />
              <Text style={styles.locationText}>{projectLocation}</Text>
            </View>
          )}
        </View>

        {/* Timeline Section */}
        <View style={styles.timelineSection}>
          {/* Milestones - displayed from top (highest %) to bottom (lowest %) */}
          {allMilestoneItems.slice().reverse().map((item, index) => {
            const actualIndex = allMilestoneItems.length - 1 - index;
            const milestoneNumber = actualIndex + 1;
            const isLeft = index % 2 === 0; // Alternate left and right
            const isLast = index === allMilestoneItems.length - 1;

            // Safe percentage value (handle undefined/null)
            const itemPercentage = Number(item.percentage_progress) || 0;

            // Calculate cumulative percentage up to this milestone
            const cumulativePercentage = allMilestoneItems
              .slice(0, actualIndex + 1)
              .reduce((sum, m) => sum + (Number(m.percentage_progress) || 0), 0);

            // Round to whole number for display in circle
            const displayPercentage = Math.round(cumulativePercentage);

            const isApproved = item.parentSetupStatus === 'approved';

            return (
              <TouchableOpacity
                key={item.item_id}
                style={styles.timelineItem}
                onPress={() => handleMilestonePress(item, milestoneNumber, cumulativePercentage)}
                activeOpacity={0.7}
              >
                {/* Left Content */}
                <View style={[styles.timelineSide, styles.timelineLeft]}>
                  {isLeft && (
                    <View style={styles.milestoneContent}>
                      <Text style={styles.milestoneLabel}>Milestone {milestoneNumber}</Text>
                      <Text style={styles.milestoneTitle}>{item.milestone_item_title}</Text>
                      <Text style={styles.milestoneCost}>{formatCurrency(item.milestone_item_cost || 0)}</Text>
                      <Text style={styles.milestonePercent}>{itemPercentage.toFixed(2)}%</Text>
                    </View>
                  )}
                </View>

                {/* Center - Circle and Line */}
                <View style={styles.timelineCenter}>
                  <View
                    style={[
                      styles.milestoneCircle,
                      (item.item_status === 'completed' || item.parentMilestoneStatus === 'completed')
                        ? styles.milestoneCircleApproved
                        : styles.milestoneCirclePending,
                    ]}
                  >
                    {item.parentMilestoneStatus === 'completed' ? (
                      <Feather name="check" size={20} color={COLORS.surface} />
                    ) : (
                      <Text
                        style={[
                          styles.circleText,
                          item.item_status === 'completed'
                            ? styles.circleTextApproved
                            : styles.circleTextPending,
                        ]}
                      >
                        {displayPercentage}
                      </Text>
                    )}
                  </View>
                  {!isLast && <View style={styles.timelineLine} />}
                </View>

                {/* Right Content */}
                <View style={[styles.timelineSide, styles.timelineRight]}>
                  {!isLeft && (
                    <View style={styles.milestoneContent}>
                      <Text style={styles.milestoneLabel}>Milestone {milestoneNumber}</Text>
                      <Text style={styles.milestoneTitle}>{item.milestone_item_title}</Text>
                      <Text style={styles.milestoneCost}>{formatCurrency(item.milestone_item_cost || 0)}</Text>
                      <Text style={styles.milestonePercent}>{itemPercentage.toFixed(2)}%</Text>
                    </View>
                  )}
                </View>
              </TouchableOpacity>
            );
          })}

          {/* Start Point */}
          <View style={styles.timelineItem}>
            <View style={[styles.timelineSide, styles.timelineLeft]}>
              <View style={styles.startContent}>
                <Text style={styles.startLabel}>Start</Text>
                <Text style={styles.startPercent}>0%</Text>
              </View>
            </View>
            <View style={styles.timelineCenter}>
              <View style={styles.startCircle} />
            </View>
            <View style={[styles.timelineSide, styles.timelineRight]} />
          </View>
        </View>

        <View style={{ height: 140 }} />
      </ScrollView>

      {/* Action Buttons - Fixed at Bottom */}
      {submittedMilestone && (
        <View style={[styles.actionButtonsContainer, { paddingBottom: insets.bottom + 16 }]}>
          <TouchableOpacity
            style={styles.requestChangesBtn}
            onPress={() => handleRequestChanges(submittedMilestone.milestone_id)}
            disabled={rejectingMilestone !== null || approvingMilestone !== null}
          >
            {rejectingMilestone === submittedMilestone.milestone_id ? (
              <ActivityIndicator size="small" color={COLORS.textSecondary} />
            ) : (
              <Text style={styles.requestChangesBtnText}>Request Changes</Text>
            )}
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.approveBtn}
            onPress={() => handleApproveMilestone(submittedMilestone.milestone_id)}
            disabled={approvingMilestone !== null || rejectingMilestone !== null}
          >
            {approvingMilestone === submittedMilestone.milestone_id ? (
              <ActivityIndicator size="small" color={COLORS.surface} />
            ) : (
              <Text style={styles.approveBtnText}>Approve Milestone</Text>
            )}
          </TouchableOpacity>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 8,
    paddingVertical: 8,
  },
  backButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  menuButton: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: 24,
  },

  // Project Section
  projectSection: {
    marginBottom: 32,
  },
  projectTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 8,
  },
  projectDescription: {
    fontSize: 14,
    color: COLORS.textSecondary,
    lineHeight: 20,
    marginBottom: 16,
  },
  budgetRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
    gap: 8,
  },
  budgetIcon: {
    width: 28,
    height: 20,
    backgroundColor: COLORS.accentLight,
    borderRadius: 4,
    justifyContent: 'center',
    alignItems: 'center',
  },
  budgetText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.text,
  },
  locationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  locationText: {
    fontSize: 14,
    color: COLORS.text,
  },

  // Timeline Section
  timelineSection: {
    paddingVertical: 16,
  },
  timelineItem: {
    flexDirection: 'row',
    minHeight: 120,
  },
  timelineSide: {
    flex: 1,
    justifyContent: 'flex-start',
    paddingTop: 8,
  },
  timelineLeft: {
    alignItems: 'flex-end',
    paddingRight: 16,
  },
  timelineRight: {
    alignItems: 'flex-start',
    paddingLeft: 16,
  },
  timelineCenter: {
    alignItems: 'center',
    width: 70,
  },
  milestoneCircle: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: COLORS.accent,
    justifyContent: 'center',
    alignItems: 'center',
    zIndex: 1,
  },
  milestoneCircleApproved: {
    backgroundColor: COLORS.accent,
    borderColor: COLORS.accent,
    borderWidth: 2,
  },
  milestoneCirclePending: {
    backgroundColor: COLORS.surface,
    borderColor: COLORS.accent,
    borderWidth: 2,
  },
  circleText: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.surface,
  },
  circleTextApproved: {
    color: COLORS.surface,
    fontWeight: '700',
  },
  circleTextPending: {
    color: COLORS.accent,
    fontWeight: '700',
  },
  timelineLine: {
    width: 3,
    flex: 1,
    backgroundColor: COLORS.border,
    marginTop: -4,
    marginBottom: -4,
    borderStyle: 'dotted',
  },
  milestoneContent: {
    maxWidth: 140,
  },
  milestoneLabel: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
    marginBottom: 2,
  },
  milestoneTitle: {
    fontSize: 13,
    color: COLORS.textSecondary,
    lineHeight: 18,
    marginBottom: 4,
  },
  milestoneCost: {
    fontSize: 13,
    fontWeight: '500',
    fontStyle: 'italic',
    color: COLORS.text,
    marginBottom: 2,
  },
  milestonePercent: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.accent,
  },

  // Start Point
  startContent: {
    alignItems: 'flex-end',
  },
  startLabel: {
    fontSize: 14,
    fontWeight: '700',
    color: COLORS.text,
  },
  startPercent: {
    fontSize: 13,
    fontWeight: '600',
    color: COLORS.accent,
  },
  startCircle: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: COLORS.darkBlue,
    zIndex: 1,
  },

  // Action Buttons
  actionButtonsContainer: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: COLORS.surface,
    paddingHorizontal: 16,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    gap: 12,
  },
  approveBtn: {
    backgroundColor: COLORS.accent,
    borderRadius: 12,
    paddingVertical: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  approveBtnText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.surface,
  },
  requestChangesBtn: {
    backgroundColor: COLORS.borderLight,
    borderRadius: 12,
    paddingVertical: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  requestChangesBtnText: {
    fontSize: 16,
    fontWeight: '700',
    color: COLORS.textSecondary,
  },
});

