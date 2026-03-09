import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  Modal,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  TextInput,
  ActivityIndicator,
  Platform,
  Alert,
  StatusBar,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Feather } from '@expo/vector-icons';
import DateTimePicker from '@react-native-community/datetimepicker';
import {
  update_service,
  ExtensionContext,
  ExtensionPreview,
  ExtensionRecord,
  AllocationMode,
  NewItemPayload,
  EditedItemPayload,
  MilestoneItemDetail,
  PreviewPayload,
  PreviewAllocationItem,
  BudgetChangeType,
} from '../../services/update_service';

// ─────────────────────────────────────────────────────────────────────────────
// Colors (match milestoneApproval palette)
// ─────────────────────────────────────────────────────────────────────────────
const C = {
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
  text: '#1E3A5F',
  textSecondary: '#64748B',
  textMuted: '#94A3B8',
  border: '#E2E8F0',
  borderLight: '#F1F5F9',
};

// ─────────────────────────────────────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────────────────────────────────────
interface Props {
  visible: boolean;
  onClose: () => void;
  projectId: number;
  userId: number;
  userRole: 'owner' | 'contractor';
  /** Called after a successful submit / approve / reject / withdraw */
  onActionComplete?: () => void;
}

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────
const fmtDate = (iso: string | null | undefined) => {
  if (!iso) return '—';
  const normalized = iso.replace(' ', 'T');
  const d = new Date(normalized);
  if (isNaN(d.getTime())) return iso;
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const fmtCurrency = (n: number | null | undefined) => {
  if (n == null) return '₱0';
  return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

const statusColor = (s: string) => {
  switch (s) {
    case 'pending':            return { bg: C.warningLight, fg: C.warning };
    case 'approved':           return { bg: C.successLight, fg: C.success };
    case 'rejected':           return { bg: C.errorLight,   fg: C.error   };
    case 'revision_requested': return { bg: '#FFF3E0',      fg: '#E65100' };
    case 'withdrawn':          return { bg: C.borderLight,  fg: C.textMuted };
    default:                   return { bg: C.borderLight,  fg: C.textMuted };
  }
};

/** Format a raw numeric string with thousands-commas for display inside a TextInput */
const fmtInputNum = (raw: string): string => {
  if (!raw) return '';
  const [intPart, decPart] = raw.split('.');
  const withCommas = intPart
    ? Number(intPart).toLocaleString('en-PH')
    : '';
  return decPart !== undefined ? `${withCommas}.${decPart}` : withCommas;
};

const budgetChangeLabel = (t: BudgetChangeType) => {
  switch (t) {
    case 'increase': return { label: 'INCREASE', color: C.accent };
    case 'decrease': return { label: 'DECREASE', color: C.error };
    default:         return { label: 'NO CHANGE', color: C.textMuted };
  }
};

// ─────────────────────────────────────────────────────────────────────────────
// Component
// ─────────────────────────────────────────────────────────────────────────────
export default function ProjectUpdateModal({
  visible,
  onClose,
  projectId,
  userId,
  userRole,
  onActionComplete,
}: Props) {
  const insets = useSafeAreaInsets();

  // ── Context ───────────────────────────────────────────────────────────────
  const [loadingCtx, setLoadingCtx] = useState(false);
  const [ctx, setCtx] = useState<ExtensionContext | null>(null);

  // ── Form (contractor) ────────────────────────────────────────────────────
  const [proposedDate, setProposedDate] = useState<Date | null>(null);
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [reason, setReason] = useState('');

  // Budget adjustment
  const [proposedBudget, setProposedBudget] = useState('');

  // Milestone management
  const [allocMode, setAllocMode] = useState<AllocationMode>('percentage');
  const [newItems, setNewItems] = useState<NewItemPayload[]>([]);
  const [editedItems, setEditedItems] = useState<Record<number, EditedItemPayload>>({});
  const [deletedItemIds, setDeletedItemIds] = useState<number[]>([]);

  // Add-item form
  const [showAddItem, setShowAddItem] = useState(false);
  const [newTitle, setNewTitle] = useState('');
  const [newDescription, setNewDescription] = useState('');
  const [newCost, setNewCost] = useState('');
  const [newStartDate, setNewStartDate] = useState<Date | null>(null);
  const [newShowStartDatePicker, setNewShowStartDatePicker] = useState(false);
  const [newDueDate, setNewDueDate] = useState<Date | null>(null);
  const [newShowDatePicker, setNewShowDatePicker] = useState(false);

  // Inline edit tracking
  const [editingItemId, setEditingItemId] = useState<number | null>(null);
  const [editCostStr, setEditCostStr] = useState('');
  const [editTitleStr, setEditTitleStr] = useState('');
  const [editStartDate, setEditStartDate] = useState<Date | null>(null);
  const [editShowStartDatePicker, setEditShowStartDatePicker] = useState(false);
  const [editDueDate, setEditDueDate] = useState<Date | null>(null);
  const [editShowDatePicker, setEditShowDatePicker] = useState(false);

  // ── Preview ───────────────────────────────────────────────────────────────
  const [loadingPreview, setLoadingPreview] = useState(false);
  const [preview, setPreview] = useState<ExtensionPreview | null>(null);

  // ── Owner action ─────────────────────────────────────────────────────────
  const [ownerNote, setOwnerNote] = useState('');
  const [rejectReason, setRejectReason] = useState('');
  const [showRejectInput, setShowRejectInput] = useState(false);
  const [revisionNotes, setRevisionNotes] = useState('');
  const [showRevisionInput, setShowRevisionInput] = useState(false);

  // ── Submitting ────────────────────────────────────────────────────────────
  const [submitting, setSubmitting] = useState(false);

  // ─────────────────────────────────────────────────────────────────────────
  // Load context
  // ─────────────────────────────────────────────────────────────────────────
  const loadContext = useCallback(async () => {
    if (!visible) return;
    setLoadingCtx(true);
    setCtx(null);
    setPreview(null);
    const res = await update_service.getContext(projectId);
    if (res.success && res.data) {
      setCtx(res.data);
    } else {
      Alert.alert('Error', res.message ?? 'Failed to load project info.');
    }
    setLoadingCtx(false);
  }, [visible, projectId]);

  useEffect(() => {
    if (visible) {
      // reset all form state
      setProposedDate(null);
      setShowDatePicker(false);
      setReason('');
      setProposedBudget('');
      setAllocMode('percentage');
      setNewItems([]);
      setEditedItems({});
      setDeletedItemIds([]);
      setShowAddItem(false);
      setNewTitle('');
      setNewDescription('');
      setNewCost('');
      setEditingItemId(null);
      setEditCostStr('');
      setEditTitleStr('');
      setEditStartDate(null);
      setEditShowStartDatePicker(false);
      setEditDueDate(null);
      setEditShowDatePicker(false);
      setNewStartDate(null);
      setNewShowStartDatePicker(false);
      setNewDueDate(null);
      setNewShowDatePicker(false);
      setPreview(null);
      setOwnerNote('');
      setRejectReason('');
      setShowRejectInput(false);
      setRevisionNotes('');
      setShowRevisionInput(false);
      loadContext();
    }
  }, [visible]);

  // ── Derived ──
  const pending = ctx?.pending_extension ?? null;
  const isRevisionRequested = pending?.status === 'revision_requested';
  const canSubmit = userRole === 'contractor'
    && (ctx?.project_status === 'in_progress' || ctx?.project_status === 'halt')
    && (!pending || isRevisionRequested);

  /** Items visible in the milestone table (existing - deleted) */
  const visibleItems: MilestoneItemDetail[] = (ctx?.milestone_items ?? [])
    .filter(it => !deletedItemIds.includes(it.item_id));

  /** The effective proposed budget or current budget */
  const effectiveBudget = proposedBudget ? parseFloat(proposedBudget) : (ctx?.total_cost ?? 0);

  /** Total cost of visible existing items (with edits applied) */
  const existingItemsTotal = visibleItems.reduce((sum, it) => {
    const edited = editedItems[it.item_id];
    return sum + (edited?.cost ?? it.effective_cost);
  }, 0);

  /** Total cost of new items */
  const newItemsTotal = newItems.reduce((sum, it) => sum + it.cost, 0);

  /** Grand total allocated */
  const grandAllocated = existingItemsTotal + newItemsTotal;

  /** Remaining from proposed budget */
  const remainingBudget = effectiveBudget - grandAllocated;

  /** Project date bounds for milestone item date pickers */
  const projectMinDate = ctx?.start_date
    ? new Date(ctx.start_date.replace(' ', 'T'))
    : undefined;
  const projectMaxDate = proposedDate
    ?? (ctx?.end_date ? new Date(ctx.end_date.replace(' ', 'T')) : undefined);

  // ─────────────────────────────────────────────────────────────────────────
  // Date-slot & overlap helpers  (prevent milestone item date overlapping)
  // ─────────────────────────────────────────────────────────────────────────
  const DAY_MS = 86_400_000;

  /** Collect occupied [start, end] ranges from ALL other items. */
  const getOccupiedRanges = (
    excludeItemId: number | null,
  ): { start: Date; end: Date; label: string }[] => {
    const occupied: { start: Date; end: Date; label: string }[] = [];

    for (const it of visibleItems) {
      if (it.item_id === excludeItemId) continue;
      const ed = editedItems[it.item_id];
      const sStr = ed?.start_date !== undefined ? ed.start_date : (it.start_date ?? null);
      const eStr = ed?.due_date !== undefined ? ed.due_date : (it.settlement_due_date ?? it.date_to_finish ?? null);
      if (sStr && eStr) {
        occupied.push({
          start: new Date(String(sStr).replace(' ', 'T')),
          end:   new Date(String(eStr).replace(' ', 'T')),
          label: ed?.title ?? it.title,
        });
      }
    }

    for (const ni of newItems) {
      if (ni.start_date && ni.due_date) {
        occupied.push({
          start: new Date(ni.start_date),
          end:   new Date(ni.due_date),
          label: ni.title,
        });
      }
    }

    occupied.sort((a, b) => a.start.getTime() - b.start.getTime());
    return occupied;
  };

  /**
   * Find the gap (slot) that `referenceDate` falls into among the sorted
   * occupied ranges.  Returns { slotMin, slotMax } constrained to the project
   * duration.  If referenceDate is null, returns project bounds.
   */
  const getDateSlot = (
    excludeItemId: number | null,
    referenceDate: Date | null,
  ): { slotMin: Date | undefined; slotMax: Date | undefined } => {
    const occupied = getOccupiedRanges(excludeItemId);

    if (occupied.length === 0 || !referenceDate) {
      return { slotMin: projectMinDate, slotMax: projectMaxDate };
    }

    const refTime = referenceDate.getTime();

    for (let i = 0; i <= occupied.length; i++) {
      const gapStart = i === 0
        ? projectMinDate
        : new Date(occupied[i - 1].end.getTime() + DAY_MS);
      const gapEnd = i === occupied.length
        ? projectMaxDate
        : new Date(occupied[i].start.getTime() - DAY_MS);

      const gsTime = gapStart?.getTime() ?? -Infinity;
      const geTime = gapEnd?.getTime()   ?? Infinity;

      if (refTime >= gsTime && refTime <= geTime) {
        return { slotMin: gapStart, slotMax: gapEnd };
      }
    }

    // referenceDate is inside an occupied range (shouldn't happen normally)
    return { slotMin: projectMinDate, slotMax: projectMaxDate };
  };

  /**
   * Check whether [startDate, dueDate] overlaps with any other item's range.
   * Returns the label of the first conflicting item, or null.
   */
  const checkDateOverlap = (
    excludeItemId: number | null,
    startDate: Date | null,
    dueDate: Date | null,
  ): string | null => {
    if (!startDate || !dueDate) return null;
    const s = startDate.getTime();
    const e = dueDate.getTime();

    for (const r of getOccupiedRanges(excludeItemId)) {
      const os = r.start.getTime();
      const oe = r.end.getTime();
      if (s <= oe && os <= e) return r.label;
    }
    return null;
  };

  // Derived overlap / slot values for the currently-open pickers
  const editSlot = editingItemId
    ? getDateSlot(editingItemId, editStartDate ?? editDueDate)
    : { slotMin: projectMinDate, slotMax: projectMaxDate };

  const editOverlap = editingItemId
    ? checkDateOverlap(editingItemId, editStartDate, editDueDate)
    : null;

  const newSlot = showAddItem
    ? getDateSlot(null, newStartDate ?? newDueDate)
    : { slotMin: projectMinDate, slotMax: projectMaxDate };

  const newOverlap = showAddItem
    ? checkDateOverlap(null, newStartDate, newDueDate)
    : null;

  // ─────────────────────────────────────────────────────────────────────────
  // Build preview payload
  // ─────────────────────────────────────────────────────────────────────────
  const buildPreviewPayload = (): PreviewPayload | null => {
    // At least one change must be present (date, budget, or milestone edits)
    const hasEdits = Object.keys(editedItems).length > 0;
    const hasNewItems = newItems.length > 0;
    const hasDeletes = deletedItemIds.length > 0;
    const hasBudget = !!proposedBudget;

    if (!proposedDate && !hasBudget && !hasEdits && !hasNewItems && !hasDeletes) {
      return null;
    }

    const payload: PreviewPayload = {};
    if (proposedDate) {
      payload.proposed_end_date = proposedDate.toISOString().split('T')[0];
    }

    if (proposedBudget) {
      payload.proposed_budget = parseFloat(proposedBudget);
    }
    payload.allocation_mode = allocMode;

    if (newItems.length > 0) payload.new_items = newItems;

    const edits = Object.values(editedItems);
    if (edits.length > 0) payload.edited_items = edits;
    if (deletedItemIds.length > 0) payload.deleted_item_ids = deletedItemIds;

    return payload;
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Preview handler
  // ─────────────────────────────────────────────────────────────────────────
  const handlePreview = async () => {
    const payload = buildPreviewPayload();
    if (!payload) {
      Alert.alert('No Changes', 'Please make at least one change (date, budget, or milestone items) before previewing.');
      return;
    }
    setLoadingPreview(true);
    setPreview(null);
    const res = await update_service.preview(projectId, payload);
    if (res.success && res.data) {
      setPreview(res.data);
    } else {
      Alert.alert('Preview failed', res.message ?? 'Could not simulate changes.');
    }
    setLoadingPreview(false);
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Submit handler (contractor)
  // ─────────────────────────────────────────────────────────────────────────
  /** Whether enough data has been entered to allow submission attempt */
  const hasAnyChange = !!proposedDate || !!proposedBudget || newItems.length > 0
    || Object.keys(editedItems).length > 0 || deletedItemIds.length > 0;

  const handleSubmit = async () => {
    // Validate: at least one change
    if (!hasAnyChange) {
      Alert.alert('No Changes', 'Please make at least one change (date, budget, or milestone items) before submitting.');
      return;
    }
    // Reason is required by backend
    if (reason.trim().length < 20) {
      Alert.alert('Reason Too Short', 'Please describe the reason for this request (at least 20 characters).');
      return;
    }

    const data: any = {
      user_id: userId,
      reason: reason.trim(),
      allocation_mode: allocMode,
    };

    if (proposedDate) {
      data.proposed_end_date = proposedDate.toISOString().split('T')[0];
    }

    if (proposedBudget) {
      data.proposed_budget = parseFloat(proposedBudget);
    }
    if (newItems.length > 0) data.new_items = newItems;
    const edits = Object.values(editedItems);
    if (edits.length > 0) data.edited_items = edits;
    if (deletedItemIds.length > 0) data.deleted_item_ids = deletedItemIds;

    setSubmitting(true);
    const res = await update_service.submit(projectId, data);
    setSubmitting(false);

    if (res.success) {
      Alert.alert('Submitted', 'Your extension request has been sent to the project owner.', [
        { text: 'OK', onPress: () => { onActionComplete?.(); onClose(); } },
      ]);
    } else {
      Alert.alert('Submission failed', res.message ?? 'Please try again.');
    }
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Owner: Approve
  // ─────────────────────────────────────────────────────────────────────────
  const handleApprove = async (ext: ExtensionRecord) => {
    const msg = ext.budget_change_type !== 'none'
      ? 'This will apply the new timeline AND budget changes to the project. Continue?'
      : 'This will apply the new timeline to all active milestones. Continue?';
    Alert.alert('Approve Extension?', msg, [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Approve',
        style: 'default',
        onPress: async () => {
          setSubmitting(true);
          const res = await update_service.approve(projectId, ext.extension_id, userId, ownerNote || undefined);
          setSubmitting(false);
          if (res.success) {
            Alert.alert('Approved', 'The project has been updated.', [
              { text: 'OK', onPress: () => { onActionComplete?.(); onClose(); } },
            ]);
          } else {
            Alert.alert('Error', res.message ?? 'Approval failed.');
          }
        },
      },
    ]);
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Owner: Reject
  // ─────────────────────────────────────────────────────────────────────────
  const handleReject = async (ext: ExtensionRecord) => {
    if (rejectReason.trim().length < 5) {
      Alert.alert('Missing reason', 'Please explain why you are rejecting this request.'); return;
    }
    setSubmitting(true);
    const res = await update_service.reject(projectId, ext.extension_id, userId, rejectReason.trim());
    setSubmitting(false);
    if (res.success) {
      Alert.alert('Rejected', 'The extension request has been declined.', [
        { text: 'OK', onPress: () => { onActionComplete?.(); onClose(); } },
      ]);
    } else {
      Alert.alert('Error', res.message ?? 'Rejection failed.');
    }
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Owner: Request Changes
  // ─────────────────────────────────────────────────────────────────────────
  const handleRequestChanges = async (ext: ExtensionRecord) => {
    if (revisionNotes.trim().length < 10) {
      Alert.alert('Missing notes', 'Please provide at least 10 characters explaining what changes you need.'); return;
    }
    setSubmitting(true);
    const res = await update_service.requestChanges(projectId, ext.extension_id, userId, revisionNotes.trim());
    setSubmitting(false);
    if (res.success) {
      Alert.alert('Revision Requested', 'The contractor will be notified to revise their proposal.', [
        { text: 'OK', onPress: () => { onActionComplete?.(); loadContext(); } },
      ]);
    } else {
      Alert.alert('Error', res.message ?? 'Request failed.');
    }
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Contractor: Withdraw
  // ─────────────────────────────────────────────────────────────────────────
  const handleWithdraw = async (ext: ExtensionRecord) => {
    Alert.alert('Withdraw Request?', 'Your pending extension request will be cancelled.', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Withdraw',
        style: 'destructive',
        onPress: async () => {
          setSubmitting(true);
          const res = await update_service.withdraw(projectId, ext.extension_id, userId);
          setSubmitting(false);
          if (res.success) {
            Alert.alert('Withdrawn', 'Your extension request has been withdrawn.', [
              { text: 'OK', onPress: () => { onActionComplete?.(); loadContext(); } },
            ]);
          } else {
            Alert.alert('Error', res.message ?? 'Could not withdraw request.');
          }
        },
      },
    ]);
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Add new item handler
  // ─────────────────────────────────────────────────────────────────────────
  const handleAddItem = () => {
    const rawVal = parseFloat((newCost || '0').replace(/,/g, ''));
    if (!newTitle.trim()) { Alert.alert('Missing', 'Title is required.'); return; }
    if (rawVal <= 0) { Alert.alert('Invalid', allocMode === 'percentage' ? 'Percentage must be greater than 0.' : 'Cost must be greater than 0.'); return; }

    // Date validation
    if (newStartDate && newDueDate && newStartDate > newDueDate) {
      Alert.alert('Invalid Dates', 'Start date cannot be after the due date.'); return;
    }
    const pStart = ctx?.start_date ? new Date(ctx.start_date.replace(' ', 'T')) : null;
    const pEnd = proposedDate ?? (ctx?.end_date ? new Date(ctx.end_date.replace(' ', 'T')) : null);
    if (newStartDate && pStart && newStartDate < pStart) {
      Alert.alert('Invalid Date', 'Start date cannot be before the project start date.'); return;
    }
    if (newStartDate && pEnd && newStartDate > pEnd) {
      Alert.alert('Invalid Date', 'Start date cannot be after the project end date.'); return;
    }
    if (newDueDate && pStart && newDueDate < pStart) {
      Alert.alert('Invalid Date', 'Due date cannot be before the project start date.'); return;
    }
    if (newDueDate && pEnd && newDueDate > pEnd) {
      Alert.alert('Invalid Date', 'Due date cannot be after the project end date.'); return;
    }
    // Overlap check
    const overlapNew = checkDateOverlap(null, newStartDate, newDueDate);
    if (overlapNew) {
      Alert.alert('Date Overlap', `This item's dates overlap with "${overlapNew}". Please adjust the dates.`); return;
    }

    let cost: number;
    let pct: number | undefined;

    if (allocMode === 'percentage') {
      if (rawVal > 100) { Alert.alert('Invalid', 'Percentage cannot exceed 100%.'); return; }
      // Compute remaining % already allocated
      const usedPct = effectiveBudget > 0 ? (grandAllocated / effectiveBudget) * 100 : 0;
      const availPct = Math.max(0, 100 - usedPct);
      if (rawVal > availPct + 0.01) {
        Alert.alert('Exceeds Budget', `Only ${availPct.toFixed(1)}% of the budget is still available for allocation.`);
        return;
      }
      pct = rawVal;
      cost = effectiveBudget > 0 ? Math.round((rawVal / 100) * effectiveBudget * 100) / 100 : 0;
    } else {
      cost = rawVal;
      if (cost > remainingBudget + 0.01) {
        Alert.alert('Exceeds Budget', `Only ${fmtCurrency(Math.max(0, remainingBudget))} is still available for allocation.`);
        return;
      }
    }

    setNewItems(prev => [...prev, {
      title: newTitle.trim(),
      description: newDescription.trim() || undefined,
      cost,
      percentage: pct,
      start_date: newStartDate ? newStartDate.toISOString().split('T')[0] : undefined,
      due_date: newDueDate ? newDueDate.toISOString().split('T')[0] : undefined,
    }]);
    setNewTitle('');
    setNewDescription('');
    setNewCost('');
    setNewStartDate(null);
    setNewShowStartDatePicker(false);
    setNewDueDate(null);
    setNewShowDatePicker(false);
    setShowAddItem(false);
    setPreview(null);
  };

  const handleRemoveNewItem = (index: number) => {
    setNewItems(prev => prev.filter((_, i) => i !== index));
    setPreview(null);
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Edit existing item handlers
  // ─────────────────────────────────────────────────────────────────────────
  const startEditItem = (item: MilestoneItemDetail) => {
    const existing = editedItems[item.item_id];
    setEditingItemId(item.item_id);
    setEditCostStr(String(existing?.cost ?? item.effective_cost));
    setEditTitleStr(existing?.title ?? item.title);
    // Restore start date from pending edit, or from item's current date
    const startSrc = existing?.start_date !== undefined ? existing.start_date : (item.start_date ?? null);
    setEditStartDate(startSrc ? new Date(startSrc.replace(' ', 'T')) : null);
    setEditShowStartDatePicker(false);
    // Restore due date from pending edit, or from item's current date
    const dueSrc = existing?.due_date ?? item.settlement_due_date ?? item.date_to_finish ?? null;
    setEditDueDate(dueSrc ? new Date(dueSrc.replace(' ', 'T')) : null);
    setEditShowDatePicker(false);
  };

  const saveEditItem = (item: MilestoneItemDetail) => {
    const cost = parseFloat(editCostStr.replace(/,/g, '') || '0');
    if (cost < item.min_cost) {
      Alert.alert('Invalid', `Cost cannot be less than ${fmtCurrency(item.min_cost)} (amount already paid).`);
      return;
    }

    const changes: EditedItemPayload = { item_id: item.item_id };
    if (cost !== item.effective_cost) changes.cost = cost;
    if (editTitleStr.trim() && editTitleStr.trim() !== item.title) changes.title = editTitleStr.trim();

    // Check if start date changed
    const origStart = item.start_date ?? null;
    const origStartStr = origStart ? new Date(origStart.replace(' ', 'T')).toISOString().split('T')[0] : null;
    const newStartStr = editStartDate ? editStartDate.toISOString().split('T')[0] : null;
    if (newStartStr !== origStartStr) {
      changes.start_date = newStartStr;
    }

    // Check if due date changed
    const origDue = item.settlement_due_date ?? item.date_to_finish ?? null;
    const origDueStr = origDue ? new Date(origDue.replace(' ', 'T')).toISOString().split('T')[0] : null;
    const newDueStr = editDueDate ? editDueDate.toISOString().split('T')[0] : null;
    if (newDueStr !== origDueStr) {
      changes.due_date = newDueStr;
    }

    // Validate: start_date must not be after due_date
    if (editStartDate && editDueDate && editStartDate > editDueDate) {
      Alert.alert('Invalid Dates', 'Start date cannot be after the due date.');
      return;
    }

    // Validate: dates must be within project duration
    const pStart = ctx?.start_date ? new Date(ctx.start_date.replace(' ', 'T')) : null;
    const pEnd = proposedDate ?? (ctx?.end_date ? new Date(ctx.end_date.replace(' ', 'T')) : null);
    if (editStartDate && pStart && editStartDate < pStart) {
      Alert.alert('Invalid Date', 'Start date cannot be before the project start date.');
      return;
    }
    if (editStartDate && pEnd && editStartDate > pEnd) {
      Alert.alert('Invalid Date', 'Start date cannot be after the project end date.');
      return;
    }
    if (editDueDate && pStart && editDueDate < pStart) {
      Alert.alert('Invalid Date', 'Due date cannot be before the project start date.');
      return;
    }
    if (editDueDate && pEnd && editDueDate > pEnd) {
      Alert.alert('Invalid Date', 'Due date cannot be after the project end date.');
      return;
    }
    // Overlap check
    const overlapEdit = checkDateOverlap(item.item_id, editStartDate, editDueDate);
    if (overlapEdit) {
      Alert.alert('Date Overlap', `This item's dates overlap with "${overlapEdit}". Please adjust the dates.`);
      return;
    }

    // Only save if something actually changed
    if (changes.cost !== undefined || changes.title !== undefined || changes.start_date !== undefined || changes.due_date !== undefined) {
      setEditedItems(prev => ({ ...prev, [item.item_id]: changes }));
    } else {
      // Remove edit if nothing changed
      setEditedItems(prev => {
        const copy = { ...prev };
        delete copy[item.item_id];
        return copy;
      });
    }
    setEditingItemId(null);
    setEditShowStartDatePicker(false);
    setEditShowDatePicker(false);
    setPreview(null);
  };

  const handleDeleteItem = (item: MilestoneItemDetail) => {
    Alert.alert('Delete Item?', `"${item.title}" will be removed when the extension is approved.`, [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Delete', style: 'destructive', onPress: () => {
          setDeletedItemIds(prev => [...prev, item.item_id]);
          // remove any edits for this item
          setEditedItems(prev => {
            const copy = { ...prev };
            delete copy[item.item_id];
            return copy;
          });
          setPreview(null);
        },
      },
    ]);
  };

  const handleUndeleteItem = (itemId: number) => {
    setDeletedItemIds(prev => prev.filter(id => id !== itemId));
    setPreview(null);
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Section A — Project Overview Card (enhanced)
  // ─────────────────────────────────────────────────────────────────────────
  const renderSectionA = () => {
    if (!ctx) return null;
    return (
      <View style={styles.sectionCard}>
        <Text style={styles.sectionLabel}>PROJECT OVERVIEW</Text>
        <Text style={styles.projectTitle}>{ctx.project_title}</Text>
        <View style={styles.overviewGrid}>
          <View style={styles.overviewItem}>
            <Text style={styles.overviewValue}>{fmtDate(ctx.start_date)}</Text>
            <Text style={styles.overviewKey}>Start Date</Text>
          </View>
          <View style={styles.overviewItem}>
            <Text style={[styles.overviewValue, { color: C.accent }]}>{fmtDate(ctx.end_date)}</Text>
            <Text style={styles.overviewKey}>Current End</Text>
          </View>
          <View style={styles.overviewItem}>
            <Text style={styles.overviewValue}>{fmtCurrency(ctx.total_cost)}</Text>
            <Text style={styles.overviewKey}>Contract Value</Text>
          </View>
          <View style={styles.overviewItem}>
            <Text style={[styles.overviewValue, { color: C.success }]}>{fmtCurrency(ctx.total_paid)}</Text>
            <Text style={styles.overviewKey}>Total Paid</Text>
          </View>
          <View style={styles.overviewItem}>
            <Text style={[styles.overviewValue, { color: C.info }]}>{fmtCurrency(ctx.total_allocated)}</Text>
            <Text style={styles.overviewKey}>Total Allocated</Text>
          </View>
          <View style={styles.overviewItem}>
            <Text style={[styles.overviewValue, { color: ctx.remaining_allocatable > 0 ? C.success : C.textMuted }]}>
              {fmtCurrency(ctx.remaining_allocatable)}
            </Text>
            <Text style={styles.overviewKey}>Remaining</Text>
          </View>
        </View>
      </View>
    );
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Pending Extension Summary (enhanced with budget info)
  // ─────────────────────────────────────────────────────────────────────────
  const renderPendingCard = (ext: ExtensionRecord) => {
    const sc = statusColor(ext.status);
    const bc = budgetChangeLabel(ext.budget_change_type);
    let milestoneChanges: any = null;
    if (ext.milestone_changes) {
      try { milestoneChanges = JSON.parse(ext.milestone_changes); } catch {}
    }

    // Compute timeline delta
    const currentEnd = ext.current_end_date ? new Date(ext.current_end_date.replace(' ', 'T')) : null;
    const proposedEnd = ext.proposed_end_date ? new Date(ext.proposed_end_date.replace(' ', 'T')) : null;
    const deltaDays = currentEnd && proposedEnd
      ? Math.round((proposedEnd.getTime() - currentEnd.getTime()) / (1000 * 60 * 60 * 24))
      : 0;

    // Budget difference
    const budgetDiff = (ext.proposed_budget ?? 0) - (ext.current_budget ?? 0);

    // Resolve deleted item names — prefer enriched snapshot, fall back to ctx
    const deletedItemSnapshot = milestoneChanges?._deleted_items ?? [];
    const deletedItemNames = (milestoneChanges?.deleted_item_ids ?? []).map((id: number) => {
      const snapshot = deletedItemSnapshot.find((d: any) => d.item_id === id);
      if (snapshot) return snapshot.title;
      const found = (ctx?.milestone_items ?? []).find(it => it.item_id === id);
      return found ? found.title : `Item #${id}`;
    });

    // Resolve edited item names + original values — prefer enriched _original, fall back to ctx
    const editedItemDetails = (milestoneChanges?.edited_items ?? []).map((edit: any) => {
      const orig = edit._original;
      const ctxItem = (ctx?.milestone_items ?? []).find(it => it.item_id === edit.item_id);
      return {
        ...edit,
        original_title: orig?.title ?? ctxItem?.title ?? `Item #${edit.item_id}`,
        original_cost: orig?.cost ?? ctxItem?.effective_cost ?? 0,
        original_percentage: orig?.percentage ?? ctxItem?.percentage ?? 0,
        original_start_date: orig?.start_date ?? ctxItem?.start_date ?? null,
        original_due_date: orig?.due_date ?? ctxItem?.settlement_due_date ?? ctxItem?.date_to_finish ?? null,
      };
    });

    // Proposed budget for percentage computation
    const proposedBudgetVal = ext.proposed_budget ?? ext.current_budget ?? 0;

    return (
      <View style={styles.sectionCard}>
        <View style={styles.pendingHeader}>
          <Text style={styles.sectionLabel}>EXTENSION REQUEST</Text>
          <View style={[styles.statusPill, { backgroundColor: sc.bg }]}>
            <Text style={[styles.statusPillText, { color: sc.fg }]}>
              {ext.status === 'revision_requested' ? 'REVISION REQUESTED' : ext.status.toUpperCase()}
            </Text>
          </View>
        </View>

        {/* ── Timeline Summary ── */}
        <View style={[styles.previewGrid, { marginTop: 4 }]}>
          <View style={styles.previewGridItem}>
            <Text style={styles.previewGridValue}>{fmtDate(ext.current_end_date)}</Text>
            <Text style={styles.previewGridKey}>Current End Date</Text>
          </View>
          <View style={styles.previewGridItem}>
            <Text style={[styles.previewGridValue, { color: C.primary }]}>{fmtDate(ext.proposed_end_date)}</Text>
            <Text style={styles.previewGridKey}>Proposed (+{deltaDays}d)</Text>
          </View>
        </View>

        <View style={styles.rowDetail}>
          <Feather name="clock" size={14} color={C.textMuted} />
          <Text style={styles.rowDetailLabel}>Submitted</Text>
          <Text style={styles.rowDetailValue}>{fmtDate(ext.created_at)}</Text>
        </View>

        {/* ── Budget Change Summary ── */}
        {ext.budget_change_type !== 'none' && (
          <View style={[styles.reasonBox, { backgroundColor: C.primaryLight, marginTop: 8 }]}>
            <Text style={[styles.previewSubLabel, { marginBottom: 6 }]}>BUDGET CHANGE</Text>
            <View style={styles.previewGrid}>
              <View style={styles.previewGridItem}>
                <Text style={styles.previewGridValue}>{fmtCurrency(ext.current_budget)}</Text>
                <Text style={styles.previewGridKey}>Current</Text>
              </View>
              <View style={styles.previewGridItem}>
                <Text style={[styles.previewGridValue, { color: bc.color }]}>
                  {fmtCurrency(ext.proposed_budget)}
                </Text>
                <Text style={[styles.previewGridKey, { color: bc.color }]}>
                  Proposed ({ext.budget_change_type === 'increase' ? '+' : ''}{fmtCurrency(budgetDiff)})
                </Text>
              </View>
            </View>
          </View>
        )}

        {/* ── New Items Detail ── */}
        {milestoneChanges?.new_items?.length > 0 && (
          <View style={{ marginTop: 10 }}>
            <Text style={[styles.previewSubLabel, { color: C.success }]}>
              NEW ITEMS ({milestoneChanges.new_items.length})
            </Text>
            {milestoneChanges.new_items.map((ni: any, idx: number) => {
              const cost = parseFloat(ni.cost ?? 0);
              const pct = proposedBudgetVal > 0 ? ((cost / proposedBudgetVal) * 100).toFixed(1) : '0.0';
              return (
                <View key={`new_${idx}`} style={[styles.previewItemRow, { backgroundColor: C.successLight }]}>
                  <View style={{ flex: 1, marginRight: 8 }}>
                    <Text style={styles.previewItemName} numberOfLines={1}>{ni.title}</Text>
                    <Text style={{ fontSize: 10, color: C.textMuted }}>
                      NEW{ni.due_date ? ` · Due ${fmtDate(ni.due_date)}` : ''}
                    </Text>
                    {ni.description ? (
                      <Text style={{ fontSize: 11, color: C.textSecondary, marginTop: 2 }} numberOfLines={2}>
                        {ni.description}
                      </Text>
                    ) : null}
                  </View>
                  <View style={{ alignItems: 'flex-end' }}>
                    <Text style={styles.previewItemDelta}>{fmtCurrency(cost)}</Text>
                    <Text style={{ fontSize: 10, color: C.textMuted }}>{pct}%</Text>
                  </View>
                </View>
              );
            })}
          </View>
        )}

        {/* ── Edited Items Detail ── */}
        {editedItemDetails.length > 0 && (
          <View style={{ marginTop: 10 }}>
            <Text style={[styles.previewSubLabel, { color: C.accent }]}>
              EDITED ITEMS ({editedItemDetails.length})
            </Text>
            {editedItemDetails.map((edit: any, idx: number) => {
              const newCost = edit.cost !== undefined ? parseFloat(edit.cost) : edit.original_cost;
              const costChanged = edit.cost !== undefined && Math.abs(newCost - edit.original_cost) > 0.01;
              const titleChanged = edit.title && edit.title !== edit.original_title;
              return (
                <View key={`edit_${idx}`} style={[styles.previewItemRow, { backgroundColor: C.accentLight }]}>
                  <View style={{ flex: 1, marginRight: 8 }}>
                    <Text style={styles.previewItemName} numberOfLines={1}>
                      {edit.title ?? edit.original_title}
                    </Text>
                    <View style={{ gap: 1, marginTop: 2 }}>
                      {titleChanged && (
                        <Text style={{ fontSize: 10, color: C.textMuted }}>
                          Was: {edit.original_title}
                        </Text>
                      )}
                      {costChanged && (
                        <Text style={{ fontSize: 10, color: C.textMuted }}>
                          Cost: {fmtCurrency(newCost)}
                        </Text>
                      )}
                      {edit.start_date !== undefined && edit.start_date && (
                        <Text style={{ fontSize: 10, color: C.textMuted }}>
                          Start: {fmtDate(edit.start_date)}
                        </Text>
                      )}
                      {edit.due_date !== undefined && edit.due_date && (
                        <Text style={{ fontSize: 10, color: C.textMuted }}>
                          End: {fmtDate(edit.due_date)}
                        </Text>
                      )}
                    </View>
                  </View>
                  <View style={{ alignItems: 'flex-end' }}>
                    <Text style={styles.previewItemDelta}>{fmtCurrency(newCost)}</Text>
                    {proposedBudgetVal > 0 && (
                      <Text style={{ fontSize: 10, color: C.textMuted }}>
                        {((newCost / proposedBudgetVal) * 100).toFixed(1)}%
                      </Text>
                    )}
                  </View>
                </View>
              );
            })}
          </View>
        )}

        {/* ── Deleted Items Detail ── */}
        {deletedItemNames.length > 0 && (
          <View style={{ marginTop: 10 }}>
            <Text style={[styles.previewSubLabel, { color: C.error }]}>
              ITEMS TO BE REMOVED ({deletedItemNames.length})
            </Text>
            {deletedItemNames.map((name: string, idx: number) => {
              const deletedId = milestoneChanges.deleted_item_ids[idx];
              const snapshotItem = deletedItemSnapshot.find((d: any) => d.item_id === deletedId);
              const original = snapshotItem
                ? { effective_cost: snapshotItem.cost, ...snapshotItem }
                : (ctx?.milestone_items ?? []).find(it => it.item_id === deletedId);
              return (
                <View key={`del_${idx}`} style={[styles.previewItemRow, { backgroundColor: C.errorLight }]}>
                  <View style={{ flex: 1, marginRight: 8 }}>
                    <Text style={[styles.previewItemName, { textDecorationLine: 'line-through' }]} numberOfLines={1}>
                      {name}
                    </Text>
                    <Text style={{ fontSize: 10, color: C.error }}>WILL BE REMOVED</Text>
                  </View>
                  {original && (
                    <View style={{ alignItems: 'flex-end' }}>
                      <Text style={[styles.previewItemDelta, { color: C.error, textDecorationLine: 'line-through' }]}>
                        {fmtCurrency(original.effective_cost)}
                      </Text>
                    </View>
                  )}
                </View>
              );
            })}
          </View>
        )}

        {/* ── Allocation Summary Footer ── */}
        {milestoneChanges && (milestoneChanges.new_items?.length > 0 || editedItemDetails.length > 0 || deletedItemNames.length > 0) && (
          <View style={{ marginTop: 10, paddingTop: 8, borderTopWidth: 1, borderTopColor: C.border }}>
            {ext.allocation_mode && (
              <View style={styles.footerRow}>
                <Text style={styles.footerLabel}>Allocation Mode</Text>
                <Text style={styles.footerValue}>
                  {ext.allocation_mode === 'percentage' ? 'Percentage' : 'Exact Amount'}
                </Text>
              </View>
            )}
            <View style={styles.footerRow}>
              <Text style={[styles.footerLabel, { fontWeight: '700' }]}>Contract Value</Text>
              <Text style={[styles.footerValue, { fontWeight: '700' }]}>
                {fmtCurrency(proposedBudgetVal)}
              </Text>
            </View>
          </View>
        )}

        {/* ── Reason ── */}
        <View style={[styles.reasonBox, { marginTop: 10 }]}>
          <Text style={styles.reasonLabel}>Reason</Text>
          <Text style={styles.reasonText}>{ext.reason}</Text>
        </View>

        {/* Owner response (if rejected) */}
        {ext.owner_response ? (
          <View style={[styles.reasonBox, { backgroundColor: C.errorLight }]}>
            <Text style={[styles.reasonLabel, { color: C.error }]}>Owner Response</Text>
            <Text style={styles.reasonText}>{ext.owner_response}</Text>
          </View>
        ) : null}

        {/* Revision notes (when revision requested) */}
        {ext.status === 'revision_requested' && ext.revision_notes ? (
          <View style={[styles.reasonBox, { backgroundColor: '#FFF3E0' }]}>
            <Text style={[styles.reasonLabel, { color: '#E65100' }]}>Revision Requested</Text>
            <Text style={styles.reasonText}>{ext.revision_notes}</Text>
          </View>
        ) : null}
      </View>
    );
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Extension Details — Date + Reason
  // ─────────────────────────────────────────────────────────────────────────
  const renderExtensionDetails = () => {
    const minDate = ctx?.end_date
      ? new Date(ctx.end_date.replace(' ', 'T'))
      : new Date();
    if (minDate <= new Date()) minDate.setDate(minDate.getDate() + 1);

    return (
      <View style={styles.sectionCard}>
        <Text style={styles.sectionLabel}>EXTENSION DETAILS</Text>

        {/* Proposed End Date */}
        <Text style={styles.fieldLabel}>Proposed New End Date <Text style={styles.req}>*</Text></Text>
        <TouchableOpacity
          style={styles.dateField}
          onPress={() => setShowDatePicker(!showDatePicker)}
          activeOpacity={0.7}
        >
          <Feather name="calendar" size={16} color={proposedDate ? C.text : C.textMuted} />
          <Text style={[styles.dateFieldText, !proposedDate && { color: C.textMuted }]}>
            {proposedDate
              ? proposedDate.toLocaleDateString('en-US', { weekday: 'short', month: 'long', day: 'numeric', year: 'numeric' })
              : 'Tap to select a date'}
          </Text>
          <Feather name={showDatePicker ? 'chevron-up' : 'chevron-down'} size={16} color={C.textMuted} />
        </TouchableOpacity>
        {showDatePicker && (
          <View style={styles.pickerWrap}>
            <DateTimePicker
              value={proposedDate ?? minDate}
              mode="date"
              display={Platform.OS === 'ios' ? 'spinner' : 'default'}
              minimumDate={minDate}
              onChange={(_, date) => {
                if (Platform.OS === 'android') setShowDatePicker(false);
                if (date) { setProposedDate(date); setPreview(null); }
              }}
              style={{ width: '100%' }}
            />
          </View>
        )}

        {/* Reason */}
        <Text style={[styles.fieldLabel, { marginTop: 16 }]}>Reason for Extension <Text style={styles.req}>*</Text></Text>
        <TextInput
          style={styles.textArea}
          placeholder="Describe the reason for the extension request (min 20 characters)…"
          placeholderTextColor={C.textMuted}
          multiline
          numberOfLines={4}
          value={reason}
          onChangeText={setReason}
          textAlignVertical="top"
        />
        <Text style={styles.charCount}>
          {reason.length} characters{reason.length < 20 ? ` (${20 - reason.length} more needed)` : ''}
        </Text>
      </View>
    );
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Budget Adjustment Section
  // ─────────────────────────────────────────────────────────────────────────
  const renderBudgetSection = () => {
    if (!ctx) return null;
    const currentBudget = ctx.total_cost;
    const pBudget = proposedBudget ? parseFloat(proposedBudget) : null;
    const changeType: BudgetChangeType = pBudget == null || Math.abs(pBudget - currentBudget) < 0.01
      ? 'none' : pBudget > currentBudget ? 'increase' : 'decrease';
    const bc = budgetChangeLabel(changeType);

    return (
      <View style={styles.sectionCard}>
        <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
          <Text style={styles.sectionLabel}>BUDGET ADJUSTMENT</Text>
          {changeType !== 'none' && (
            <View style={[styles.statusPill, { backgroundColor: changeType === 'increase' ? C.accentLight : C.errorLight }]}>
              <Text style={[styles.statusPillText, { color: bc.color }]}>{bc.label}</Text>
            </View>
          )}
        </View>

        <Text style={styles.fieldLabel}>
          Proposed New Total Budget (₱)
        </Text>
        <Text style={{ fontSize: 11, color: C.textMuted, marginBottom: 6 }}>
          Leave empty to keep current budget ({fmtCurrency(currentBudget)})
        </Text>
        <TextInput
          style={styles.amountInput}
          placeholder={fmtInputNum(String(Math.round(currentBudget)))}
          placeholderTextColor={C.textMuted}
          keyboardType="decimal-pad"
          value={fmtInputNum(proposedBudget)}
          onChangeText={t => {
            // Strip commas and any non-numeric chars (allow one decimal point)
            const stripped = t.replace(/,/g, '').replace(/[^0-9.]/g, '');
            const parts = stripped.split('.');
            const cleaned = parts.length > 2
              ? parts[0] + '.' + parts.slice(1).join('')
              : stripped;
            setProposedBudget(cleaned);
            setPreview(null);
          }}
        />

        {/* Read-only breakdown */}
        <View style={[styles.overviewGrid, { marginTop: 12 }]}>
          <View style={styles.overviewItem}>
            <Text style={styles.overviewValue}>{fmtCurrency(currentBudget)}</Text>
            <Text style={styles.overviewKey}>Current Budget</Text>
          </View>
          <View style={styles.overviewItem}>
            <Text style={[styles.overviewValue, { color: C.success }]}>{fmtCurrency(ctx.total_paid)}</Text>
            <Text style={styles.overviewKey}>Total Paid</Text>
          </View>
          <View style={styles.overviewItem}>
            <Text style={[styles.overviewValue, { color: C.info }]}>{fmtCurrency(grandAllocated)}</Text>
            <Text style={styles.overviewKey}>Total Allocated</Text>
          </View>
          <View style={styles.overviewItem}>
            <Text style={[styles.overviewValue, { color: remainingBudget >= 0 ? C.success : C.error }]}>
              {fmtCurrency(remainingBudget)}
            </Text>
            <Text style={styles.overviewKey}>Remaining</Text>
          </View>
        </View>

        {remainingBudget < 0 && (
          <View style={[styles.warningBanner, { marginTop: 8 }]}>
            <Feather name="alert-triangle" size={14} color={C.error} />
            <Text style={styles.warningText}>
              Allocation exceeds proposed budget by {fmtCurrency(Math.abs(remainingBudget))}
            </Text>
          </View>
        )}
      </View>
    );
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Milestone Adjustment Section
  // ─────────────────────────────────────────────────────────────────────────
  const renderMilestoneSection = () => {
    if (!ctx) return null;

    return (
      <View style={styles.sectionCard}>
        <Text style={styles.sectionLabel}>MILESTONE ITEMS</Text>

        {/* Allocation mode toggle */}
        <Text style={[styles.fieldLabel, { marginBottom: 8 }]}>Allocation Mode</Text>
        <View style={{ flexDirection: 'row', gap: 8, marginBottom: 16 }}>
          {(['percentage', 'exact'] as AllocationMode[]).map(mode => (
            <TouchableOpacity
              key={mode}
              style={[
                styles.modeChip,
                allocMode === mode && styles.modeChipActive,
              ]}
              onPress={() => { setAllocMode(mode); setPreview(null); }}
              activeOpacity={0.7}
            >
              <Feather
                name={mode === 'percentage' ? 'percent' : 'hash'}
                size={14}
                color={allocMode === mode ? '#fff' : C.textSecondary}
              />
              <Text style={[
                styles.modeChipText,
                allocMode === mode && styles.modeChipTextActive,
              ]}>
                {mode === 'percentage' ? 'Percentage' : 'Exact Amount'}
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        {/* Existing items list */}
        {visibleItems.map(item => {
          const isEditing = editingItemId === item.item_id;
          const hasEdit = !!editedItems[item.item_id];
          const displayCost = editedItems[item.item_id]?.cost ?? item.effective_cost;

          return (
            <View
              key={item.item_id}
              style={[
                styles.milestoneItemCard,
                !item.editable && styles.milestoneItemLocked,
                hasEdit && styles.milestoneItemEdited,
              ]}
            >
              {/* Item header row */}
              <View style={styles.milestoneItemHeader}>
                <View style={{ flex: 1 }}>
                  {isEditing ? (
                    <TextInput
                      style={[styles.inlineInput, { fontWeight: '600', fontSize: 13 }]}
                      value={editTitleStr}
                      onChangeText={setEditTitleStr}
                    />
                  ) : (
                    <Text style={styles.milestoneItemTitle} numberOfLines={2}>
                      {editedItems[item.item_id]?.title ?? item.title}
                    </Text>
                  )}
                  <Text style={styles.milestoneItemMeta}>
                    {item.milestone_name} · {item.item_status}
                  </Text>
                </View>

                {/* Lock or action buttons */}
                {!item.editable ? (
                  <View style={styles.lockBadge}>
                    <Feather name="lock" size={12} color={C.textMuted} />
                    <Text style={styles.lockText}>
                      {item.is_fully_paid ? 'Fully Paid' : item.editable_reason === 'completed' ? 'Completed' : item.item_status}
                    </Text>
                  </View>
                ) : (
                  <View style={{ flexDirection: 'row', gap: 6 }}>
                    {isEditing ? (
                      <>
                        <TouchableOpacity
                          style={[styles.iconBtn, { backgroundColor: C.successLight }]}
                          onPress={() => saveEditItem(item)}
                        >
                          <Feather name="check" size={14} color={C.success} />
                        </TouchableOpacity>
                        <TouchableOpacity
                          style={[styles.iconBtn, { backgroundColor: C.borderLight }]}
                          onPress={() => setEditingItemId(null)}
                        >
                          <Feather name="x" size={14} color={C.textMuted} />
                        </TouchableOpacity>
                      </>
                    ) : (
                      <>
                        <TouchableOpacity
                          style={[styles.iconBtn, { backgroundColor: C.primaryLight }]}
                          onPress={() => startEditItem(item)}
                        >
                          <Feather name="edit-2" size={14} color={C.primary} />
                        </TouchableOpacity>
                        <TouchableOpacity
                          style={[styles.iconBtn, { backgroundColor: C.errorLight }]}
                          onPress={() => handleDeleteItem(item)}
                        >
                          <Feather name="trash-2" size={14} color={C.error} />
                        </TouchableOpacity>
                      </>
                    )}
                  </View>
                )}
              </View>

              {/* Cost row */}
              <View style={styles.milestoneItemCostRow}>
                {isEditing ? (
                  <View style={{ flex: 1 }}>
                    <Text style={{ fontSize: 11, color: C.textMuted, marginBottom: 2 }}>
                      Cost (min: {fmtCurrency(item.min_cost)})
                    </Text>
                    <TextInput
                      style={styles.inlineInput}
                      value={fmtInputNum(editCostStr)}
                      onChangeText={t => {
                        const stripped = t.replace(/,/g, '').replace(/[^0-9.]/g, '');
                        const parts = stripped.split('.');
                        setEditCostStr(parts.length > 2 ? parts[0] + '.' + parts.slice(1).join('') : stripped);
                      }}
                      keyboardType="decimal-pad"
                    />
                  </View>
                ) : (
                  <>
                    <Text style={styles.milestoneItemCost}>{fmtCurrency(displayCost)}</Text>
                    {item.total_paid > 0 && (
                      <Text style={[styles.milestoneItemPaid, { color: C.success }]}>
                        {fmtCurrency(item.total_paid)} paid
                      </Text>
                    )}
                  </>
                )}
              </View>

              {/* Start date + Due date rows */}
              {isEditing ? (
                <View style={{ marginTop: 8 }}>
                  {/* Date range hint — shows slot bounds, not full project range */}
                  {(editSlot.slotMin || editSlot.slotMax) && (
                    <Text style={{ fontSize: 10, color: C.textMuted, marginBottom: 6 }}>
                      Available slot: {editSlot.slotMin ? fmtDate(editSlot.slotMin.toISOString()) : '—'} — {editSlot.slotMax ? fmtDate(editSlot.slotMax.toISOString()) : '—'}
                    </Text>
                  )}
                  {/* Start Date */}
                  <Text style={{ fontSize: 11, color: C.textMuted, marginBottom: 2 }}>Start Date</Text>
                  <TouchableOpacity
                    style={[styles.inlineInput, { flexDirection: 'row', alignItems: 'center', gap: 6 }]}
                    onPress={() => setEditShowStartDatePicker(!editShowStartDatePicker)}
                    activeOpacity={0.7}
                  >
                    <Feather name="calendar" size={14} color={editStartDate ? C.text : C.textMuted} />
                    <Text style={{ flex: 1, fontSize: 13, color: editStartDate ? C.text : C.textMuted }}>
                      {editStartDate ? fmtDate(editStartDate.toISOString()) : 'No start date set'}
                    </Text>
                    {editStartDate && (
                      <TouchableOpacity onPress={() => { setEditStartDate(null); setEditShowStartDatePicker(false); }} hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}>
                        <Feather name="x-circle" size={14} color={C.textMuted} />
                      </TouchableOpacity>
                    )}
                  </TouchableOpacity>
                  {editShowStartDatePicker && (
                    <View style={{ marginTop: 4 }}>
                      <DateTimePicker
                        value={editStartDate ?? new Date()}
                        mode="date"
                        display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                        minimumDate={editSlot.slotMin}
                        maximumDate={editDueDate ?? editSlot.slotMax}
                        onChange={(_, date) => {
                          if (Platform.OS === 'android') setEditShowStartDatePicker(false);
                          if (date) setEditStartDate(date);
                        }}
                        style={{ width: '100%' }}
                      />
                    </View>
                  )}
                  {editStartDate && editDueDate && editStartDate > editDueDate && (
                    <View style={[styles.warningBanner, { marginTop: 4 }]}>
                      <Feather name="alert-triangle" size={12} color={C.error} />
                      <Text style={styles.warningText}>Start date is after the due date</Text>
                    </View>
                  )}
                  {editOverlap && (
                    <View style={[styles.warningBanner, { marginTop: 4 }]}>
                      <Feather name="alert-triangle" size={12} color={C.error} />
                      <Text style={styles.warningText}>Overlaps with "{editOverlap}"</Text>
                    </View>
                  )}

                  {/* Due Date */}
                  <Text style={{ fontSize: 11, color: C.textMuted, marginBottom: 2, marginTop: 8 }}>Due Date</Text>
                  <TouchableOpacity
                    style={[styles.inlineInput, { flexDirection: 'row', alignItems: 'center', gap: 6 }]}
                    onPress={() => setEditShowDatePicker(!editShowDatePicker)}
                    activeOpacity={0.7}
                  >
                    <Feather name="calendar" size={14} color={editDueDate ? C.text : C.textMuted} />
                    <Text style={{ flex: 1, fontSize: 13, color: editDueDate ? C.text : C.textMuted }}>
                      {editDueDate ? fmtDate(editDueDate.toISOString()) : 'No due date set'}
                    </Text>
                    {editDueDate && (
                      <TouchableOpacity onPress={() => { setEditDueDate(null); setEditShowDatePicker(false); }} hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}>
                        <Feather name="x-circle" size={14} color={C.textMuted} />
                      </TouchableOpacity>
                    )}
                  </TouchableOpacity>
                  {editShowDatePicker && (
                    <View style={{ marginTop: 4 }}>
                      <DateTimePicker
                        value={editDueDate ?? new Date()}
                        mode="date"
                        display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                        minimumDate={editStartDate ?? editSlot.slotMin}
                        maximumDate={editSlot.slotMax}
                        onChange={(_, date) => {
                          if (Platform.OS === 'android') setEditShowDatePicker(false);
                          if (date) setEditDueDate(date);
                        }}
                        style={{ width: '100%' }}
                      />
                    </View>
                  )}
                </View>
              ) : (
                <View style={{ marginTop: 4, gap: 2 }}>
                  {(() => {
                    const displayStart = editedItems[item.item_id]?.start_date !== undefined
                      ? editedItems[item.item_id]?.start_date
                      : (item.start_date ?? null);
                    const displayDue = editedItems[item.item_id]?.due_date !== undefined
                      ? editedItems[item.item_id]?.due_date
                      : (item.settlement_due_date ?? item.date_to_finish ?? null);
                    return (
                      <>
                        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                          <Feather name="play" size={11} color={C.textMuted} />
                          <Text style={{ fontSize: 11, color: C.textSecondary }}>
                            Start: {displayStart ? fmtDate(displayStart) : 'Not set'}
                          </Text>
                        </View>
                        <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                          <Feather name="calendar" size={11} color={C.textMuted} />
                          <Text style={{ fontSize: 11, color: C.textSecondary }}>
                            End: {displayDue ? fmtDate(displayDue) : 'Not set'}
                          </Text>
                        </View>
                      </>
                    );
                  })()}
                </View>
              )}

              {hasEdit && !isEditing && (
                <View style={[styles.warningBanner, { marginTop: 6, backgroundColor: C.accentLight }]}>
                  <Feather name="edit" size={12} color={C.accent} />
                  <Text style={[styles.warningText, { color: C.accent }]}>
                    {(() => {
                      const e = editedItems[item.item_id];
                      const parts: string[] = [];
                      if (e?.cost !== undefined) parts.push(`cost: ${fmtCurrency(e.cost)}`);
                      if (e?.title !== undefined) parts.push(`title updated`);
                      if (e?.start_date !== undefined) parts.push(`start: ${e.start_date ? fmtDate(e.start_date) : 'cleared'}`);
                      if (e?.due_date !== undefined) parts.push(`end: ${e.due_date ? fmtDate(e.due_date) : 'cleared'}`);
                      return `Modified (${parts.join(', ')})`;
                    })()}
                  </Text>
                </View>
              )}
            </View>
          );
        })}

        {/* Deleted items (allow undo) */}
        {deletedItemIds.map(id => {
          const item = (ctx?.milestone_items ?? []).find(it => it.item_id === id);
          if (!item) return null;
          return (
            <View key={`del-${id}`} style={[styles.milestoneItemCard, { opacity: 0.5, backgroundColor: C.errorLight }]}>
              <View style={styles.milestoneItemHeader}>
                <View style={{ flex: 1 }}>
                  <Text style={[styles.milestoneItemTitle, { textDecorationLine: 'line-through' }]}>
                    {item.title}
                  </Text>
                  <Text style={styles.milestoneItemMeta}>Marked for deletion</Text>
                </View>
                <TouchableOpacity
                  style={[styles.iconBtn, { backgroundColor: '#fff' }]}
                  onPress={() => handleUndeleteItem(id)}
                >
                  <Feather name="rotate-ccw" size={14} color={C.info} />
                </TouchableOpacity>
              </View>
            </View>
          );
        })}

        {/* New items added */}
        {newItems.map((ni, index) => (
          <View key={`new-${index}`} style={[styles.milestoneItemCard, { backgroundColor: C.successLight, borderColor: C.success }]}>
            <View style={styles.milestoneItemHeader}>
              <View style={{ flex: 1 }}>
                <Text style={styles.milestoneItemTitle}>{ni.title}</Text>
                {ni.description ? <Text style={styles.milestoneItemMeta}>{ni.description}</Text> : null}
                <Text style={styles.milestoneItemMeta}>New item · Will be added on approval</Text>
              </View>
              <TouchableOpacity
                style={[styles.iconBtn, { backgroundColor: '#fff' }]}
                onPress={() => handleRemoveNewItem(index)}
              >
                <Feather name="x" size={14} color={C.error} />
              </TouchableOpacity>
            </View>
            <View style={styles.milestoneItemCostRow}>
              <Text style={styles.milestoneItemCost}>{fmtCurrency(ni.cost)}</Text>
              {ni.percentage != null && (
                <Text style={[styles.milestoneItemPaid, { color: C.info }]}>
                  {ni.percentage.toFixed(1)}%
                </Text>
              )}
            </View>
            {ni.due_date && (
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: 4 }}>
                <Feather name="calendar" size={11} color={C.textMuted} />
                <Text style={{ fontSize: 11, color: C.textSecondary }}>Due: {fmtDate(ni.due_date)}</Text>
              </View>
            )}
          </View>
        ))}

        {/* Add item form / button */}
        {showAddItem ? (
          <View style={[styles.addItemForm]}>
            <Text style={styles.fieldLabel}>New Milestone Item</Text>
            <TextInput
              style={[styles.amountInput, { marginBottom: 8 }]}
              placeholder="Item title *"
              placeholderTextColor={C.textMuted}
              value={newTitle}
              onChangeText={setNewTitle}
            />
            <TextInput
              style={[styles.amountInput, { marginBottom: 8 }]}
              placeholder="Description (optional)"
              placeholderTextColor={C.textMuted}
              value={newDescription}
              onChangeText={setNewDescription}
            />
            <TextInput
              style={[styles.amountInput, { marginBottom: 4 }]}
              placeholder={allocMode === 'percentage' ? 'Percentage (%) *' : 'Cost (₱) *'}
              placeholderTextColor={C.textMuted}
              keyboardType="decimal-pad"
              value={allocMode === 'exact' ? fmtInputNum(newCost) : newCost}
              onChangeText={t => {
                if (allocMode === 'exact') {
                  const stripped = t.replace(/,/g, '').replace(/[^0-9.]/g, '');
                  const parts = stripped.split('.');
                  setNewCost(parts.length > 2 ? parts[0] + '.' + parts.slice(1).join('') : stripped);
                } else {
                  setNewCost(t.replace(/[^0-9.]/g, ''));
                }
              }}
            />
            {(() => {
              const val = parseFloat(newCost || '0');
              const availPct = effectiveBudget > 0 ? Math.max(0, 100 - (grandAllocated / effectiveBudget) * 100) : 0;
              const isOverBudget = allocMode === 'percentage'
                ? val > availPct + 0.01
                : val > remainingBudget + 0.01;
              return (
                <>
                  {isOverBudget && val > 0 && (
                    <View style={[styles.warningBanner, { marginBottom: 4 }]}>  
                      <Feather name="alert-triangle" size={12} color={C.error} />
                      <Text style={styles.warningText}>
                        {allocMode === 'percentage'
                          ? `Exceeds available allocation — only ${availPct.toFixed(1)}% remaining`
                          : `Exceeds available budget — only ${fmtCurrency(Math.max(0, remainingBudget))} remaining`}
                      </Text>
                    </View>
                  )}
                  <Text style={{ fontSize: 11, color: C.textMuted, marginBottom: 8 }}>
                    {allocMode === 'percentage'
                      ? `Available: ${availPct.toFixed(1)}% of ${fmtCurrency(effectiveBudget)}${val > 0 && val <= 100 ? ` · ${fmtCurrency(Math.round((val / 100) * effectiveBudget * 100) / 100)} equivalent` : ''}`
                      : `Available: ${fmtCurrency(Math.max(0, remainingBudget))} of ${fmtCurrency(effectiveBudget)}`}
                  </Text>
                </>
              );
            })()}
            {/* Start date picker */}
            {(newSlot.slotMin || newSlot.slotMax) && (
              <Text style={{ fontSize: 10, color: C.textMuted, marginBottom: 4 }}>
                Available slot: {newSlot.slotMin ? fmtDate(newSlot.slotMin.toISOString()) : '—'} — {newSlot.slotMax ? fmtDate(newSlot.slotMax.toISOString()) : '—'}
              </Text>
            )}
            <Text style={{ fontSize: 11, color: C.textMuted, marginBottom: 2 }}>Start Date (optional)</Text>
            <TouchableOpacity
              style={[styles.amountInput, { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 4, justifyContent: 'flex-start' }]}
              onPress={() => setNewShowStartDatePicker(!newShowStartDatePicker)}
              activeOpacity={0.7}
            >
              <Feather name="play" size={14} color={newStartDate ? C.text : C.textMuted} />
              <Text style={{ flex: 1, fontSize: 13, color: newStartDate ? C.text : C.textMuted }}>
                {newStartDate ? fmtDate(newStartDate.toISOString()) : 'Tap to select a start date'}
              </Text>
              {newStartDate && (
                <TouchableOpacity onPress={() => { setNewStartDate(null); setNewShowStartDatePicker(false); }} hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}>
                  <Feather name="x-circle" size={14} color={C.textMuted} />
                </TouchableOpacity>
              )}
            </TouchableOpacity>
            {newShowStartDatePicker && (
              <View style={{ marginBottom: 8 }}>
                <DateTimePicker
                  value={newStartDate ?? new Date()}
                  mode="date"
                  display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                  minimumDate={newSlot.slotMin}
                  maximumDate={newDueDate ?? newSlot.slotMax}
                  onChange={(_, date) => {
                    if (Platform.OS === 'android') setNewShowStartDatePicker(false);
                    if (date) setNewStartDate(date);
                  }}
                  style={{ width: '100%' }}
                />
              </View>
            )}
            {newStartDate && newDueDate && newStartDate > newDueDate && (
              <View style={[styles.warningBanner, { marginBottom: 4 }]}>
                <Feather name="alert-triangle" size={12} color={C.error} />
                <Text style={styles.warningText}>Start date is after the due date</Text>
              </View>
            )}
            {newOverlap && (
              <View style={[styles.warningBanner, { marginBottom: 4 }]}>
                <Feather name="alert-triangle" size={12} color={C.error} />
                <Text style={styles.warningText}>Overlaps with "{newOverlap}"</Text>
              </View>
            )}
            {/* Due date picker */}
            <Text style={{ fontSize: 11, color: C.textMuted, marginBottom: 2 }}>Due Date (optional)</Text>
            <TouchableOpacity
              style={[styles.amountInput, { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 4, justifyContent: 'flex-start' }]}
              onPress={() => setNewShowDatePicker(!newShowDatePicker)}
              activeOpacity={0.7}
            >
              <Feather name="calendar" size={14} color={newDueDate ? C.text : C.textMuted} />
              <Text style={{ flex: 1, fontSize: 13, color: newDueDate ? C.text : C.textMuted }}>
                {newDueDate ? fmtDate(newDueDate.toISOString()) : 'Tap to select a due date'}
              </Text>
              {newDueDate && (
                <TouchableOpacity onPress={() => { setNewDueDate(null); setNewShowDatePicker(false); }} hitSlop={{ top: 8, bottom: 8, left: 8, right: 8 }}>
                  <Feather name="x-circle" size={14} color={C.textMuted} />
                </TouchableOpacity>
              )}
            </TouchableOpacity>
            {newShowDatePicker && (
              <View style={{ marginBottom: 8 }}>
                <DateTimePicker
                  value={newDueDate ?? new Date()}
                  mode="date"
                  display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                  minimumDate={newStartDate ?? newSlot.slotMin ?? new Date()}
                  maximumDate={newSlot.slotMax}
                  onChange={(_, date) => {
                    if (Platform.OS === 'android') setNewShowDatePicker(false);
                    if (date) setNewDueDate(date);
                  }}
                  style={{ width: '100%' }}
                />
              </View>
            )}
            <View style={{ flexDirection: 'row', gap: 8 }}>
              <TouchableOpacity
                style={[styles.previewBtn, { flex: 1, marginTop: 0, borderColor: C.border }]}
                onPress={() => { setShowAddItem(false); setNewTitle(''); setNewDescription(''); setNewCost(''); setNewStartDate(null); setNewShowStartDatePicker(false); setNewDueDate(null); setNewShowDatePicker(false); }}
              >
                <Text style={[styles.previewBtnText, { color: C.textSecondary }]}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.submitBtn, { flex: 1, marginBottom: 0, paddingVertical: 10 }]}
                onPress={handleAddItem}
              >
                <Text style={[styles.submitBtnText, { fontSize: 14 }]}>Add Item</Text>
              </TouchableOpacity>
            </View>
          </View>
        ) : (
          <TouchableOpacity
            style={styles.addItemBtn}
            onPress={() => setShowAddItem(true)}
            activeOpacity={0.7}
          >
            <Feather name="plus" size={16} color={C.primary} />
            <Text style={styles.addItemBtnText}>Add Milestone Item</Text>
          </TouchableOpacity>
        )}

        {/* Footer totals */}
        <View style={styles.milestoneFooter}>
          <View style={styles.footerRow}>
            <Text style={styles.footerLabel}>Existing Items ({visibleItems.length})</Text>
            <Text style={styles.footerValue}>{fmtCurrency(existingItemsTotal)}</Text>
          </View>
          {newItems.length > 0 && (
            <View style={styles.footerRow}>
              <Text style={styles.footerLabel}>New Items ({newItems.length})</Text>
              <Text style={[styles.footerValue, { color: C.success }]}>{fmtCurrency(newItemsTotal)}</Text>
            </View>
          )}
          {deletedItemIds.length > 0 && (
            <View style={styles.footerRow}>
              <Text style={styles.footerLabel}>Deleted Items ({deletedItemIds.length})</Text>
              <Text style={[styles.footerValue, { color: C.error }]}>
                −{fmtCurrency(
                  (ctx?.milestone_items ?? [])
                    .filter(it => deletedItemIds.includes(it.item_id))
                    .reduce((s, it) => s + it.effective_cost, 0)
                )}
              </Text>
            </View>
          )}
          <View style={[styles.footerRow, { borderTopWidth: 1, borderTopColor: C.border, paddingTop: 8, marginTop: 4 }]}>
            <Text style={[styles.footerLabel, { fontWeight: '700' }]}>Grand Total</Text>
            <Text style={[styles.footerValue, { fontWeight: '700', color: remainingBudget >= 0 ? C.primary : C.error }]}>
              {fmtCurrency(grandAllocated)}
            </Text>
          </View>
        </View>
      </View>
    );
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Preview Section (v2 — timeline + budget + allocation)
  // ─────────────────────────────────────────────────────────────────────────
  const renderPreview = () => {
    if (!preview) return null;
    const tl = preview.timeline;
    const bg = preview.budget;
    const al = preview.allocation;

    return (
      <View style={[styles.sectionCard, { backgroundColor: C.primaryLight }]}>
        <View style={styles.previewHeaderRow}>
          <Feather name="activity" size={16} color={C.primary} />
          <Text style={[styles.sectionLabel, { color: C.primary, marginBottom: 0, marginLeft: 6 }]}>IMPACT PREVIEW</Text>
        </View>

        {/* Timeline */}
        {tl && (
          <View style={styles.previewGrid}>
            <View style={styles.previewGridItem}>
              <Text style={styles.previewGridValue}>+{Math.round(tl.delta_days)}d</Text>
              <Text style={styles.previewGridKey}>Timeline Extension</Text>
            </View>
            <View style={styles.previewGridItem}>
              <Text style={styles.previewGridValue}>{fmtDate(tl.proposed_end_date)}</Text>
              <Text style={styles.previewGridKey}>New End Date</Text>
            </View>
          </View>
        )}

        {/* Budget */}
        {bg && bg.budget_change_type !== 'none' && (
          <View style={{ marginTop: 12 }}>
            <Text style={styles.previewSubLabel}>BUDGET CHANGE</Text>
            <View style={styles.previewGrid}>
              <View style={styles.previewGridItem}>
                <Text style={styles.previewGridValue}>{fmtCurrency(bg.current_budget)}</Text>
                <Text style={styles.previewGridKey}>Current</Text>
              </View>
              <View style={styles.previewGridItem}>
                <Text style={[styles.previewGridValue, { color: bg.budget_change_type === 'increase' ? C.accent : C.error }]}>
                  {fmtCurrency(bg.proposed_budget)}
                </Text>
                <Text style={styles.previewGridKey}>
                  Proposed ({bg.budget_change_type === 'increase' ? '+' : ''}{fmtCurrency(bg.budget_difference)})
                </Text>
              </View>
            </View>
            {bg.allocation_exceeds && bg.allocation_warning && (
              <View style={[styles.warningBanner, { marginTop: 8 }]}>
                <Feather name="alert-triangle" size={14} color={C.error} />
                <Text style={styles.warningText}>{bg.allocation_warning}</Text>
              </View>
            )}
          </View>
        )}

        {/* Allocation breakdown */}
        {al && al.items && al.items.length > 0 && (
          <View style={{ marginTop: 12 }}>
            <Text style={styles.previewSubLabel}>
              ALLOCATION ({al.mode === 'percentage' ? '%' : '₱'} MODE)
            </Text>
            {al.items.map((item: PreviewAllocationItem, idx: number) => (
              <View
                key={item.item_id ?? item.temp_id ?? idx}
                style={[
                  styles.previewItemRow,
                  !item.is_existing && { backgroundColor: C.successLight },
                  item.is_edited && { backgroundColor: C.accentLight },
                ]}
              >
                <View style={{ flex: 1, marginRight: 8 }}>
                  <Text style={styles.previewItemName} numberOfLines={1}>{item.title}</Text>
                  <Text style={{ fontSize: 10, color: C.textMuted }}>
                    {!item.is_existing ? 'NEW' : item.is_edited ? 'EDITED' : item.status}
                    {!item.editable && item.is_existing ? ' · locked' : ''}
                  </Text>
                </View>
                <View style={{ alignItems: 'flex-end' }}>
                  <Text style={styles.previewItemDelta}>{fmtCurrency(item.cost)}</Text>
                  {al.mode === 'percentage' && (
                    <Text style={{ fontSize: 10, color: C.textMuted }}>{item.percentage.toFixed(1)}%</Text>
                  )}
                </View>
              </View>
            ))}

            {/* Allocation footer */}
            <View style={[styles.footerRow, { marginTop: 8, paddingTop: 8, borderTopWidth: 1, borderTopColor: C.border }]}>
              <Text style={[styles.footerLabel, { fontWeight: '700' }]}>Total Allocated</Text>
              <Text style={[styles.footerValue, { fontWeight: '700' }]}>{fmtCurrency(al.total_allocated)}</Text>
            </View>
            <View style={styles.footerRow}>
              <Text style={styles.footerLabel}>Remaining Budget</Text>
              <Text style={[styles.footerValue, { color: al.remaining_budget >= 0 ? C.success : C.error }]}>
                {fmtCurrency(al.remaining_budget)}
              </Text>
            </View>
          </View>
        )}

        {/* Deleted items */}
        {al && al.deleted_item_ids && al.deleted_item_ids.length > 0 && (
          <View style={{ marginTop: 8 }}>
            <Text style={[styles.previewSubLabel, { color: C.error }]}>
              ITEMS TO BE DELETED ({al.deleted_item_ids.length})
            </Text>
          </View>
        )}
      </View>
    );
  };

  // ─────────────────────────────────────────────────────────────────────────
  // Preview Button
  // ─────────────────────────────────────────────────────────────────────────
  const renderPreviewButton = () => (
    <TouchableOpacity
      style={[styles.previewBtn, (!proposedDate) && styles.btnDisabled, { marginHorizontal: 0, marginBottom: 12 }]}
      onPress={handlePreview}
      disabled={!proposedDate || loadingPreview}
      activeOpacity={0.8}
    >
      {loadingPreview ? (
        <ActivityIndicator size="small" color={C.primary} />
      ) : (
        <>
          <Feather name="eye" size={15} color={C.primary} />
          <Text style={styles.previewBtnText}>Preview Impact</Text>
        </>
      )}
    </TouchableOpacity>
  );

  // ─────────────────────────────────────────────────────────────────────────
  // Owner action panel
  // ─────────────────────────────────────────────────────────────────────────
  const renderOwnerActions = (ext: ExtensionRecord) => (
    <View style={styles.sectionCard}>
      <Text style={styles.sectionLabel}>YOUR ACTION</Text>

      <TextInput
        style={styles.textArea}
        placeholder="Optional note to contractor…"
        placeholderTextColor={C.textMuted}
        multiline
        numberOfLines={3}
        value={ownerNote}
        onChangeText={setOwnerNote}
        textAlignVertical="top"
      />

      {/* Reject reason input */}
      {showRejectInput && (
        <>
          <TextInput
            style={[styles.textArea, { marginTop: 10, borderColor: C.error }]}
            placeholder="Reason for rejection (required)…"
            placeholderTextColor={C.textMuted}
            multiline
            numberOfLines={3}
            value={rejectReason}
            onChangeText={setRejectReason}
            textAlignVertical="top"
          />
          <View style={styles.actionRow}>
            <TouchableOpacity style={styles.cancelRejectBtn} onPress={() => setShowRejectInput(false)} activeOpacity={0.7}>
              <Text style={styles.cancelRejectText}>Back</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.rejectBtn, submitting && styles.btnDisabled]}
              onPress={() => handleReject(ext)}
              disabled={submitting}
              activeOpacity={0.8}
            >
              {submitting ? <ActivityIndicator size="small" color="#fff" /> : <Text style={styles.rejectBtnText}>Confirm Reject</Text>}
            </TouchableOpacity>
          </View>
        </>
      )}

      {/* Request changes input */}
      {showRevisionInput && (
        <>
          <TextInput
            style={[styles.textArea, { marginTop: 10, borderColor: '#E65100' }]}
            placeholder="Describe what changes you need (min 10 chars)…"
            placeholderTextColor={C.textMuted}
            multiline
            numberOfLines={3}
            value={revisionNotes}
            onChangeText={setRevisionNotes}
            textAlignVertical="top"
          />
          <View style={styles.actionRow}>
            <TouchableOpacity style={styles.cancelRejectBtn} onPress={() => setShowRevisionInput(false)} activeOpacity={0.7}>
              <Text style={styles.cancelRejectText}>Back</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.revisionBtn, submitting && styles.btnDisabled]}
              onPress={() => handleRequestChanges(ext)}
              disabled={submitting}
              activeOpacity={0.8}
            >
              {submitting ? <ActivityIndicator size="small" color="#fff" /> : <Text style={styles.revisionBtnText}>Send Revision Request</Text>}
            </TouchableOpacity>
          </View>
        </>
      )}

      {/* Main action buttons — shown when neither sub-input is open */}
      {!showRejectInput && !showRevisionInput && (
        <View style={{ gap: 8 }}>
          <View style={styles.actionRow}>
            <TouchableOpacity
              style={[styles.rejectBtn, submitting && styles.btnDisabled]}
              onPress={() => setShowRejectInput(true)}
              disabled={submitting}
              activeOpacity={0.8}
            >
              <Text style={styles.rejectBtnText}>Reject</Text>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.revisionBtn, submitting && styles.btnDisabled]}
              onPress={() => setShowRevisionInput(true)}
              disabled={submitting}
              activeOpacity={0.8}
            >
              <Text style={styles.revisionBtnText}>Request Changes</Text>
            </TouchableOpacity>
          </View>
          <TouchableOpacity
            style={[styles.approveBtn, { width: '100%' }, submitting && styles.btnDisabled]}
            onPress={() => handleApprove(ext)}
            disabled={submitting}
            activeOpacity={0.8}
          >
            {submitting ? <ActivityIndicator size="small" color="#fff" /> : <Text style={styles.approveBtnText}>Approve &amp; Apply</Text>}
          </TouchableOpacity>
        </View>
      )}
    </View>
  );

  // ─────────────────────────────────────────────────────────────────────────
  // Main Render
  // ─────────────────────────────────────────────────────────────────────────
  return (
    <Modal visible={visible} animationType="slide" onRequestClose={onClose}>
      <View style={[styles.container, { paddingTop: insets.top }]}>
        <StatusBar barStyle="dark-content" backgroundColor="#fff" />

        {/* Header */}
        <View style={styles.header}>
          <TouchableOpacity style={styles.backBtn} onPress={onClose}>
            <Feather name="x" size={22} color={C.text} />
          </TouchableOpacity>
          <Text style={styles.headerTitle}>Update Project</Text>
          <View style={{ width: 36 }} />
        </View>

        {loadingCtx ? (
          <View style={styles.centered}>
            <ActivityIndicator size="large" color={C.primary} />
            <Text style={styles.loadingText}>Loading project info…</Text>
          </View>
        ) : (
          <ScrollView
            style={styles.scroll}
            contentContainerStyle={styles.scrollContent}
            keyboardShouldPersistTaps="handled"
            showsVerticalScrollIndicator={false}
          >
            {/* Section A — Overview */}
            {renderSectionA()}

            {/* Pending Extension Card */}
            {pending && renderPendingCard(pending)}

            {/* Owner: approve/reject/request-changes actions for pending */}
            {pending && pending.status === 'pending' && userRole === 'owner' && renderOwnerActions(pending)}

            {/* Contractor: withdraw pending */}
            {pending && pending.status === 'pending' && userRole === 'contractor' && (
              <View style={styles.sectionCard}>
                <Text style={[styles.sectionLabel, { color: C.warning }]}>PENDING APPROVAL</Text>
                <Text style={styles.infoText}>
                  Your extension request is waiting for the owner to review. You may withdraw it if you'd like to make changes.
                </Text>
                <TouchableOpacity
                  style={[styles.withdrawBtn, submitting && styles.btnDisabled]}
                  onPress={() => handleWithdraw(pending)}
                  disabled={submitting}
                  activeOpacity={0.8}
                >
                  {submitting
                    ? <ActivityIndicator size="small" color={C.error} />
                    : <Text style={styles.withdrawBtnText}>Withdraw Request</Text>}
                </TouchableOpacity>
              </View>
            )}

            {/* Contractor: revision requested — show notes + withdraw */}
            {pending && pending.status === 'revision_requested' && userRole === 'contractor' && (
              <View style={styles.sectionCard}>
                <Text style={[styles.sectionLabel, { color: '#E65100' }]}>REVISION REQUESTED</Text>
                <Text style={styles.infoText}>
                  The property owner has requested changes to your extension proposal. Please revise the details below and resubmit.
                </Text>
                <TouchableOpacity
                  style={[styles.withdrawBtn, submitting && styles.btnDisabled]}
                  onPress={() => handleWithdraw(pending)}
                  disabled={submitting}
                  activeOpacity={0.8}
                >
                  {submitting
                    ? <ActivityIndicator size="small" color={C.error} />
                    : <Text style={styles.withdrawBtnText}>Withdraw Request</Text>}
                </TouchableOpacity>
              </View>
            )}

            {/* Contractor: new request form */}
            {canSubmit && (
              <>
                {renderExtensionDetails()}
                {renderBudgetSection()}
                {renderMilestoneSection()}
                {renderPreviewButton()}
                {renderPreview()}

                {/* Submit */}
                <TouchableOpacity
                  style={[styles.submitBtn, (submitting || !hasAnyChange) && styles.btnDisabled]}
                  onPress={() => { if (!submitting && hasAnyChange) handleSubmit(); }}
                  disabled={submitting || !hasAnyChange}
                  activeOpacity={hasAnyChange ? 0.8 : 1}
                >
                  {submitting
                    ? <ActivityIndicator size="small" color="#fff" />
                    : <Text style={styles.submitBtnText}>
                        {isRevisionRequested ? 'Resubmit Extension Request' : 'Submit Extension Request'}
                      </Text>}
                </TouchableOpacity>
              </>
            )}

            {/* Owner with no pending */}
            {!pending && userRole === 'owner' && (
              <View style={styles.emptyState}>
                <Feather name="check-circle" size={36} color={C.success} />
                <Text style={styles.emptyTitle}>No Pending Request</Text>
                <Text style={styles.emptyDesc}>The contractor has not submitted an extension request for this project.</Text>
              </View>
            )}

            {/* Contractor: project status doesn't allow request */}
            {!pending && userRole === 'contractor' && !canSubmit && ctx && (
              <View style={styles.emptyState}>
                <Feather name="lock" size={36} color={C.textMuted} />
                <Text style={styles.emptyTitle}>Unavailable</Text>
                <Text style={styles.emptyDesc}>
                  Extension requests can only be submitted while the project is active (in_progress or halted).
                  {'\n'}Current status: <Text style={{ fontWeight: '600' }}>{ctx.project_status}</Text>
                </Text>
              </View>
            )}

            <View style={{ height: 40 + insets.bottom }} />
          </ScrollView>
        )}
      </View>
    </Modal>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// Styles
// ─────────────────────────────────────────────────────────────────────────────
const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: C.background },
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    paddingHorizontal: 16, paddingVertical: 14,
    borderBottomWidth: 1, borderBottomColor: C.border,
    backgroundColor: '#fff',
  },
  backBtn: { width: 36, height: 36, alignItems: 'center', justifyContent: 'center' },
  headerTitle: { fontSize: 17, fontWeight: '700', color: C.text },
  scroll: { flex: 1 },
  scrollContent: { padding: 16, paddingBottom: 32 },
  centered: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  loadingText: { marginTop: 12, fontSize: 14, color: C.textSecondary },

  // Section card
  sectionCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: C.border,
    padding: 16,
    marginBottom: 12,
  },
  sectionLabel: {
    fontSize: 10, fontWeight: '700', color: C.textMuted,
    letterSpacing: 1.2, marginBottom: 10,
  },
  projectTitle: { fontSize: 16, fontWeight: '700', color: C.text, marginBottom: 12 },

  // Overview grid
  overviewGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 4 },
  overviewItem: { width: '48%', backgroundColor: C.borderLight, borderRadius: 8, padding: 10, marginBottom: 4 },
  overviewValue: { fontSize: 13, fontWeight: '700', color: C.text },
  overviewKey: { fontSize: 11, color: C.textMuted, marginTop: 2 },

  // Pending card
  pendingHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
  statusPill: { borderRadius: 12, paddingHorizontal: 10, paddingVertical: 4 },
  statusPillText: { fontSize: 11, fontWeight: '700', letterSpacing: 0.8 },
  rowDetail: { flexDirection: 'row', alignItems: 'center', paddingVertical: 6, borderBottomWidth: 1, borderBottomColor: C.borderLight },
  rowDetailLabel: { flex: 1, fontSize: 13, color: C.textSecondary, marginLeft: 8 },
  rowDetailValue: { fontSize: 13, fontWeight: '600', color: C.text },
  reasonBox: { backgroundColor: C.borderLight, borderRadius: 8, padding: 10, marginTop: 10 },
  reasonLabel: { fontSize: 11, fontWeight: '700', color: C.textMuted, marginBottom: 4 },
  reasonText: { fontSize: 13, color: C.text, lineHeight: 20 },

  // Form fields
  fieldLabel: { fontSize: 13, fontWeight: '600', color: C.text, marginBottom: 6 },
  req: { color: C.error },
  dateField: {
    flexDirection: 'row', alignItems: 'center', gap: 8,
    borderWidth: 1, borderColor: C.border, borderRadius: 8,
    paddingHorizontal: 12, paddingVertical: 12,
  },
  dateFieldText: { flex: 1, fontSize: 14, color: C.text },
  pickerWrap: { marginTop: 4, borderWidth: 1, borderColor: C.border, borderRadius: 8, overflow: 'hidden' },
  textArea: {
    borderWidth: 1, borderColor: C.border, borderRadius: 8,
    padding: 12, fontSize: 14, color: C.text,
    minHeight: 90,
  },
  charCount: { fontSize: 11, color: C.textMuted, textAlign: 'right', marginTop: 4 },
  amountInput: {
    borderWidth: 1, borderColor: C.border, borderRadius: 8,
    padding: 12, fontSize: 15, color: C.text,
  },

  // Mode chips (allocation mode)
  modeChip: {
    flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6,
    borderWidth: 1.5, borderColor: C.border, borderRadius: 8,
    paddingVertical: 10,
  },
  modeChipActive: { backgroundColor: C.primary, borderColor: C.primary },
  modeChipText: { fontSize: 13, fontWeight: '600', color: C.textSecondary },
  modeChipTextActive: { color: '#fff' },

  // Milestone items
  milestoneItemCard: {
    borderWidth: 1, borderColor: C.border, borderRadius: 10,
    padding: 12, marginBottom: 8, backgroundColor: '#fff',
  },
  milestoneItemLocked: { backgroundColor: C.borderLight, borderColor: C.borderLight },
  milestoneItemEdited: { borderColor: C.accent, borderWidth: 1.5 },
  milestoneItemHeader: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between' },
  milestoneItemTitle: { fontSize: 13, fontWeight: '600', color: C.text, marginBottom: 2 },
  milestoneItemMeta: { fontSize: 11, color: C.textMuted },
  milestoneItemCostRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 8 },
  milestoneItemCost: { fontSize: 14, fontWeight: '700', color: C.primary },
  milestoneItemPaid: { fontSize: 12 },

  lockBadge: { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: C.borderLight, borderRadius: 6, paddingHorizontal: 8, paddingVertical: 4 },
  lockText: { fontSize: 10, fontWeight: '600', color: C.textMuted },

  iconBtn: { width: 30, height: 30, borderRadius: 6, alignItems: 'center', justifyContent: 'center' },

  inlineInput: {
    borderWidth: 1, borderColor: C.border, borderRadius: 6,
    paddingHorizontal: 8, paddingVertical: 6, fontSize: 13, color: C.text,
  },

  // Add item
  addItemBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6,
    borderWidth: 1.5, borderColor: C.primary, borderRadius: 8, borderStyle: 'dashed',
    paddingVertical: 12, marginBottom: 12,
  },
  addItemBtnText: { fontSize: 14, fontWeight: '600', color: C.primary },
  addItemForm: {
    borderWidth: 1, borderColor: C.primary, borderRadius: 10,
    padding: 12, marginBottom: 12, backgroundColor: C.primaryLight,
  },

  // Warning banner
  warningBanner: {
    flexDirection: 'row', alignItems: 'center', gap: 6,
    backgroundColor: C.errorLight, borderRadius: 6, padding: 8,
  },
  warningText: { flex: 1, fontSize: 12, color: C.error },

  // Milestone footer
  milestoneFooter: {
    backgroundColor: C.borderLight, borderRadius: 8, padding: 10,
  },
  footerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 3 },
  footerLabel: { fontSize: 12, color: C.textSecondary },
  footerValue: { fontSize: 13, fontWeight: '600', color: C.text },

  // Preview button
  previewBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6,
    marginTop: 16, borderWidth: 1.5, borderColor: C.primary, borderRadius: 8,
    paddingVertical: 10,
  },
  previewBtnText: { fontSize: 14, fontWeight: '600', color: C.primary },

  // Preview card
  previewHeaderRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 12 },
  previewGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 4 },
  previewGridItem: { flex: 1, minWidth: '45%', backgroundColor: '#fff', borderRadius: 8, padding: 10 },
  previewGridValue: { fontSize: 15, fontWeight: '700', color: C.primary },
  previewGridKey: { fontSize: 11, color: C.textMuted, marginTop: 2 },
  previewSubLabel: { fontSize: 11, fontWeight: '700', color: C.textMuted, letterSpacing: 0.8, marginBottom: 6 },
  previewItemRow: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    paddingVertical: 6, paddingHorizontal: 4, borderBottomWidth: 1, borderBottomColor: C.border,
    borderRadius: 4,
  },
  previewItemName: { flex: 1, fontSize: 13, color: C.text, marginRight: 8 },
  previewItemDelta: { fontSize: 13, fontWeight: '600', color: C.primary },

  // Action buttons
  actionRow: { flexDirection: 'row', gap: 10, marginTop: 14 },
  rejectBtn: {
    flex: 1, backgroundColor: C.errorLight, borderRadius: 8,
    paddingVertical: 12, alignItems: 'center', justifyContent: 'center',
  },
  rejectBtnText: { fontSize: 14, fontWeight: '700', color: C.error },
  approveBtn: {
    flex: 1.5, backgroundColor: C.primary, borderRadius: 8,
    paddingVertical: 12, alignItems: 'center', justifyContent: 'center',
  },
  approveBtnText: { fontSize: 14, fontWeight: '700', color: '#fff' },
  cancelRejectBtn: {
    flex: 0.8, borderWidth: 1, borderColor: C.border, borderRadius: 8,
    paddingVertical: 12, alignItems: 'center', justifyContent: 'center',
  },
  cancelRejectText: { fontSize: 14, color: C.textSecondary },

  // Revision
  revisionBtn: {
    flex: 1, backgroundColor: '#FFF3E0', borderRadius: 8,
    paddingVertical: 12, alignItems: 'center', justifyContent: 'center',
  },
  revisionBtnText: { fontSize: 14, fontWeight: '700', color: '#E65100' },

  // Withdraw
  withdrawBtn: {
    marginTop: 12, borderWidth: 1.5, borderColor: C.error, borderRadius: 8,
    paddingVertical: 12, alignItems: 'center',
  },
  withdrawBtnText: { fontSize: 14, fontWeight: '700', color: C.error },

  // Submit
  submitBtn: {
    backgroundColor: C.primary, borderRadius: 10,
    paddingVertical: 15, alignItems: 'center', marginBottom: 12,
  },
  submitBtnText: { fontSize: 15, fontWeight: '700', color: '#fff' },
  btnDisabled: { opacity: 0.3 },

  // Info text
  infoText: { fontSize: 13, color: C.textSecondary, lineHeight: 20, marginBottom: 4 },

  // Empty state
  emptyState: { alignItems: 'center', paddingVertical: 40, gap: 10 },
  emptyTitle: { fontSize: 16, fontWeight: '700', color: C.text, marginTop: 8 },
  emptyDesc: { fontSize: 13, color: C.textSecondary, textAlign: 'center', lineHeight: 20, paddingHorizontal: 20 },
});
