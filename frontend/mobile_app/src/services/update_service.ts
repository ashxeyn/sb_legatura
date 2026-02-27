import { api_config, api_request } from '../config/api';

// ─────────────────────────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────────────────────────

/** A milestone item enriched with payment status & editability */
export interface MilestoneItemDetail {
  item_id: number;
  milestone_id: number;
  milestone_name: string;
  sequence_order: number;
  title: string;
  description: string | null;
  base_cost: number;
  adjusted_cost: number | null;
  effective_cost: number;
  carry_forward_amount: number;
  percentage: number;
  item_status: string;
  milestone_status: string;
  start_date: string | null;
  date_to_finish: string | null;
  settlement_due_date: string | null;
  total_paid: number;
  unpaid_balance: number;
  is_fully_paid: boolean;
  is_partially_paid: boolean;
  editable: boolean;
  editable_reason: string | null;
  min_cost: number;
}

export interface ExtensionContext {
  project_id: number;
  project_title: string;
  project_status: string;
  start_date: string | null;
  end_date: string | null;
  total_cost: number;
  total_paid: number;
  total_allocated: number;
  remaining_balance: number;
  remaining_allocatable: number;
  owner_user_id: number;
  contractor_user_id: number;
  milestone_items: MilestoneItemDetail[];
  pending_extension: ExtensionRecord | null;
  plan_id: number | null;
  contractor_id: number | null;
}

export type AllocationMode = 'percentage' | 'exact';
export type BudgetChangeType = 'none' | 'increase' | 'decrease';

export interface NewItemPayload {
  title: string;
  description?: string;
  cost: number;
  percentage?: number;
  start_date?: string;
  due_date?: string;
  attachments?: string[];
}

export interface EditedItemPayload {
  item_id: number;
  cost?: number;
  percentage?: number;
  title?: string;
  start_date?: string | null;
  due_date?: string | null;
}

export interface PreviewPayload {
  proposed_end_date?: string | null;
  proposed_budget?: number | null;
  allocation_mode?: AllocationMode;
  new_items?: NewItemPayload[];
  edited_items?: EditedItemPayload[];
  deleted_item_ids?: number[];
}

export interface PreviewAllocationItem {
  item_id?: number;
  temp_id?: string;
  title: string;
  cost: number;
  percentage: number;
  status: string;
  editable: boolean;
  is_existing: boolean;
  is_edited: boolean;
}

export interface ExtensionPreview {
  success: boolean;
  message?: string;
  timeline?: {
    current_end_date: string | null;
    proposed_end_date: string | null;
    delta_days: number;
  };
  budget?: {
    current_budget: number;
    proposed_budget: number;
    budget_change_type: BudgetChangeType;
    budget_difference: number;
    total_paid: number;
    allocation_exceeds?: boolean;
    allocation_warning?: string;
  };
  allocation?: {
    mode: AllocationMode;
    total_allocated: number;
    remaining_budget: number;
    proposed_budget: number;
    items: PreviewAllocationItem[];
    deleted_item_ids: number[];
  };
}

export type ExtensionStatus = 'pending' | 'approved' | 'rejected' | 'withdrawn' | 'revision_requested';

export interface ExtensionRecord {
  extension_id: number;
  project_id: number;
  contractor_user_id: number;
  owner_user_id: number;
  current_end_date: string | null;
  proposed_end_date: string | null;
  reason: string;
  current_budget: number | null;
  proposed_budget: number | null;
  budget_change_type: BudgetChangeType;
  has_additional_cost: boolean;
  additional_amount: number | null;
  milestone_changes: string | null;
  allocation_mode: AllocationMode | null;
  status: ExtensionStatus;
  owner_response: string | null;
  revision_notes: string | null;
  applied_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface SubmitExtensionData {
  user_id: number;
  proposed_end_date?: string | null;
  reason: string;
  proposed_budget?: number | null;
  allocation_mode?: AllocationMode;
  new_items?: NewItemPayload[];
  edited_items?: EditedItemPayload[];
  deleted_item_ids?: number[];
  // Backward compat
  has_additional_cost?: boolean;
  additional_amount?: number;
}

export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  status: number;
}

// ─────────────────────────────────────────────────────────────────────────────
// Service
// ─────────────────────────────────────────────────────────────────────────────

export class update_service {
  /** Load full project context (overview + milestone items). */
  static async getContext(projectId: number): Promise<ApiResponse<ExtensionContext>> {
    try {
      return await api_request(`/api/projects/${projectId}/update/context`, { method: 'GET' });
    } catch (err) {
      return { success: false, message: String(err), status: 500 };
    }
  }

  /** Fetch milestone items with payment status (standalone). */
  static async getMilestoneItems(projectId: number): Promise<ApiResponse<MilestoneItemDetail[]>> {
    try {
      return await api_request(`/api/projects/${projectId}/update/milestone-items`, { method: 'GET' });
    } catch (err) {
      return { success: false, message: String(err), status: 500 };
    }
  }

  /** Enhanced preview — supports budget + milestone changes. */
  static async preview(
    projectId: number,
    payload: PreviewPayload
  ): Promise<ApiResponse<ExtensionPreview>> {
    try {
      return await api_request(`/api/projects/${projectId}/update/preview`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(payload),
      });
    } catch (err) {
      return { success: false, message: String(err), status: 500 };
    }
  }

  /** Submit an update request. */
  static async submit(
    projectId: number,
    data: SubmitExtensionData
  ): Promise<ApiResponse<ExtensionRecord>> {
    try {
      return await api_request(`/api/projects/${projectId}/update`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(data),
      });
    } catch (err) {
      return { success: false, message: String(err), status: 500 };
    }
  }

  /** List all update requests for a project. */
  static async list(projectId: number): Promise<ApiResponse<ExtensionRecord[]>> {
    try {
      return await api_request(`/api/projects/${projectId}/updates`, { method: 'GET' });
    } catch (err) {
      return { success: false, message: String(err), status: 500 };
    }
  }

  /** Owner approves a pending update. */
  static async approve(
    projectId: number,
    extensionId: number,
    userId: number,
    note?: string
  ): Promise<ApiResponse> {
    try {
      return await api_request(`/api/projects/${projectId}/updates/${extensionId}/approve`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ user_id: userId, note: note ?? null }),
      });
    } catch (err) {
      return { success: false, message: String(err), status: 500 };
    }
  }

  /** Owner rejects a pending update. */
  static async reject(
    projectId: number,
    extensionId: number,
    userId: number,
    reason: string
  ): Promise<ApiResponse> {
    try {
      return await api_request(`/api/projects/${projectId}/updates/${extensionId}/reject`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ user_id: userId, reason }),
      });
    } catch (err) {
      return { success: false, message: String(err), status: 500 };
    }
  }

  /** Contractor withdraws a pending update. */
  static async withdraw(
    projectId: number,
    extensionId: number,
    userId: number
  ): Promise<ApiResponse> {
    try {
      return await api_request(`/api/projects/${projectId}/updates/${extensionId}/withdraw`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ user_id: userId }),
      });
    } catch (err) {
      return { success: false, message: String(err), status: 500 };
    }
  }

  /** Owner requests changes on a pending update. */
  static async requestChanges(
    projectId: number,
    extensionId: number,
    userId: number,
    notes: string
  ): Promise<ApiResponse> {
    try {
      return await api_request(`/api/projects/${projectId}/updates/${extensionId}/request-changes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ user_id: userId, notes }),
      });
    } catch (err) {
      return { success: false, message: String(err), status: 500 };
    }
  }
}
