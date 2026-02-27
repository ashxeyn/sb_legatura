import { api_request } from '../config/api';

// ─────────────────────────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────────────────────────

export interface ProjectSummaryHeader {
  project_id: number;
  project_title: string;
  project_description: string | null;
  project_location: string | null;
  status: string;
  property_type: string | null;
  owner_name: string;
  owner_email: string | null;
  owner_phone: string | null;
  contractor_company: string | null;
  contractor_name: string;
  contractor_email: string | null;
  original_start_date: string | null;
  original_end_date: string | null;
  current_end_date: string | null;
  was_extended: boolean;
  extension_approved_at: string | null;
  project_created_at: string | null;
}

export interface ExecutiveOverview {
  original_budget: number;
  current_budget: number;
  payment_mode: string;
  downpayment: number;
  total_milestones: number;
  completed_milestones: number;
  total_paid: number;
  total_pending: number;
  remaining_balance: number;
}

export interface BudgetHistoryRecord {
  extension_id: number;
  change_type: string | null;
  previous_budget: number | null;
  updated_budget: number | null;
  previous_end_date: string | null;
  proposed_end_date: string | null;
  reason: string | null;
  status: string;
  date_proposed: string | null;
  date_approved: string | null;
}

export interface MilestoneBreakdownItem {
  item_id: number;
  title: string;
  milestone_name: string;
  original_allocation: number;
  current_allocation: number;
  adjusted_cost: number | null;
  carry_forward_amount: number | null;
  percentage_progress: number;
  status: string;
  current_due_date: string | null;
  original_due_date: string | null;
  was_extended: boolean;
  extension_count: number;
  settlement_due_date: string | null;
  sequence_order: number;
  total_paid: number;
  remaining: number;
  payment_status: string;
}

export interface ChangeHistoryEvent {
  date: string;
  action: string;
  performed_by: string;
  notes: string | null;
  reference: string | null;
}

export interface PaymentRecord {
  payment_id: number;
  milestone: string;
  amount: number;
  payment_type: string;
  transaction_number: string | null;
  transaction_date: string;
  status: string;
  reason: string | null;
}

export interface PaymentsHistory {
  records: PaymentRecord[];
  total_approved: number;
  total_pending: number;
  total_rejected: number;
}

export interface ProgressReport {
  progress_id: number;
  report_title: string;
  milestone: string;
  status: string;
  submitted_at: string;
  updated_at: string | null;
}

export interface ProjectSummaryData {
  header: ProjectSummaryHeader;
  overview: ExecutiveOverview;
  budget_history: BudgetHistoryRecord[];
  milestones: MilestoneBreakdownItem[];
  change_history: ChangeHistoryEvent[];
  payments: PaymentsHistory;
  progress_reports: ProgressReport[];
  generated_at: string;
}

// ── Milestone Summary types ──

export interface MilestoneSummaryHeader {
  item_id: number;
  title: string;
  description: string | null;
  status: string;
  milestone_name: string;
  milestone_status: string;
  original_allocation: number;
  current_allocation: number;
  carry_forward_amount: number;
  original_due_date: string | null;
  current_due_date: string | null;
  was_extended: boolean;
  extension_count: number;
  settlement_due_date: string | null;
  sequence_order: number;
  percentage_progress: number;
}

export interface MilestoneFinancial {
  allocated_budget: number;
  original_budget: number;
  paid_amount: number;
  pending_amount: number;
  remaining_balance: number;
  over_amount: number;
}

export interface DateHistoryRecord {
  id: number;
  previous_date: string;
  new_date: string;
  extension_id: number | null;
  changed_at: string;
  change_reason: string | null;
  extension_reason: string | null;
  changed_by_name: string | null;
}

export interface MilestoneSummaryData {
  header: MilestoneSummaryHeader;
  financial: MilestoneFinancial;
  date_history: DateHistoryRecord[];
  payments: PaymentRecord[];
  progress_reports: ProgressReport[];
  generated_at: string;
}

// ─────────────────────────────────────────────────────────────────────────────
// Service
// ─────────────────────────────────────────────────────────────────────────────

interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
}

export const summary_service = {
  /**
   * Fetch full project lifecycle summary.
   */
  getProjectSummary: async (projectId: number): Promise<ApiResponse<ProjectSummaryData>> => {
    try {
      return await api_request(`/api/projects/${projectId}/summary`, { method: 'GET' });
    } catch (error: any) {
      console.error('Error fetching project summary:', error);
      return { success: false, message: error.message || 'Failed to load project summary' };
    }
  },

  /**
   * Fetch single milestone item lifecycle summary.
   */
  getMilestoneSummary: async (
    projectId: number,
    itemId: number,
  ): Promise<ApiResponse<MilestoneSummaryData>> => {
    try {
      return await api_request(`/api/projects/${projectId}/milestones/${itemId}/summary`, {
        method: 'GET',
      });
    } catch (error: any) {
      console.error('Error fetching milestone summary:', error);
      return { success: false, message: error.message || 'Failed to load milestone summary' };
    }
  },
};
