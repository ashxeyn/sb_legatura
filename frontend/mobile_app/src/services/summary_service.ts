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
  downpayment_cleared: boolean;
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
  submitted_by_owner_id?: number | null;
  uploader_name?: string | null;
  uploader_role?: string | null;
  uploaded_by_staff?: number | boolean;
}

export interface ProjectSummaryData {
  header: ProjectSummaryHeader;
  overview: ExecutiveOverview;
  project_post: ProjectPost;
  bidding_history: BidRecord[];
  milestone_setups: MilestoneSetup[];
  budget_history: BudgetHistoryRecord[];
  milestones: MilestoneBreakdownItem[];
  change_history: ChangeHistoryEvent[];
  payments: PaymentsHistory;
  progress_reports: ProgressReport[];
  file_summary: FileSummary;
  generated_at: string;
}

// ── Project Post ──

export interface ProjectPost {
  title: string;
  description: string | null;
  location: string | null;
  property_type: string | null;
  budget_range_min: number;
  budget_range_max: number;
  lot_size: string | null;
  floor_area: string | null;
  to_finish: number | null;
  posted_at: string | null;
  bidding_due: string | null;
  post_status: string | null;
  files_by_type: Record<string, { count: number; files: string[] }>;
  total_files: number;
}

// ── Bidding ──

export interface BidRecord {
  bid_id: number;
  company_name: string;
  proposed_cost: number;
  estimated_timeline: number | null;
  contractor_notes: string | null;
  bid_status: string;
  reason: string | null;
  submitted_at: string | null;
  decision_date: string | null;
  years_of_experience: number | null;
  completed_projects: number | null;
  file_count: number;
  file_names: string[];
}

// ── Milestone Setup ──

export interface MilestoneSetupItem {
  sequence: number;
  title: string;
  cost: number;
  due_date: string | null;
  status: string;
}

export interface MilestoneSetup {
  milestone_id: number;
  name: string;
  description: string | null;
  setup_status: string;
  status: string;
  start_date: string | null;
  end_date: string | null;
  is_deleted: boolean;
  created_at: string | null;
  updated_at: string | null;
  item_count: number;
  total_cost: number;
  items: MilestoneSetupItem[];
}

// ── File Summary ──

export interface FileSummary {
  project_files: { total: number; by_type: Record<string, number> };
  progress_files: number;
  payment_receipts: number;
  bid_files: number;
  item_files: number;
  grand_total: number;
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
