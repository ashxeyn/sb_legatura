// @ts-nocheck
import * as Print from 'expo-print';
import * as Sharing from 'expo-sharing';
import { Alert, Platform } from 'react-native';
import type { ProjectSummaryData, MilestoneBreakdownItem, PaymentRecord, BudgetHistoryRecord, ChangeHistoryEvent, ProgressReport } from '../services/summary_service';

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────
const fmt = (n: number) =>
  new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 }).format(n);

const fmtDate = (d: string | null | undefined) => {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const fmtDateTime = (d: string | null | undefined) => {
  if (!d) return '—';
  const dt = new Date(d);
  return `${dt.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })} ${dt.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}`;
};

const escHtml = (s: any) =>
  String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

const statusClass = (status: string) => {
  const s = (status || '').toLowerCase();
  if (['completed', 'approved'].includes(s)) return 'badge-success';
  if (['pending', 'submitted'].includes(s)) return 'badge-warning';
  if (['active', 'in_progress'].includes(s)) return 'badge-info';
  if (['rejected'].includes(s)) return 'badge-error';
  if (['terminated'].includes(s)) return 'badge-error';
  return 'badge-muted';
};

// ─────────────────────────────────────────────────────────────────────────────
// HTML Builder
// ─────────────────────────────────────────────────────────────────────────────
function buildReportHtml(data: ProjectSummaryData): string {
  const { header, overview, milestones, budget_history, change_history, payments, progress_reports,
          project_post, bidding_history, milestone_setups, file_summary } = data;

  const progressPercent = overview.total_milestones > 0
    ? Math.round((overview.completed_milestones / overview.total_milestones) * 100)
    : 0;
  const budgetUtil = overview.current_budget > 0
    ? Math.round((overview.total_paid / overview.current_budget) * 100)
    : 0;

  return `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { margin: 40px 36px; size: A4; }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1E3A5F; font-size: 11px; line-height: 1.5; background: #fff; }

  .header-banner { background: linear-gradient(135deg, #1E3A5F 0%, #0A1628 100%); color: #fff; padding: 28px 30px 22px; border-radius: 0; }
  .header-banner h1 { font-size: 20px; font-weight: 700; margin-bottom: 3px; letter-spacing: 0.3px; }
  .header-banner .subtitle { font-size: 11px; opacity: 0.8; }

  /* Phase divider */
  .phase-divider { margin: 28px 0 4px; padding: 10px 16px 8px; border-radius: 4px; color: #fff; }
  .phase-divider-dark { background: linear-gradient(135deg, #1E3A5F 0%, #2D5A8E 100%); }
  .phase-divider-green { background: linear-gradient(135deg, #065F46 0%, #047857 100%); }
  .phase-divider .phase-num { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.6; }
  .phase-divider .phase-title { font-size: 14px; font-weight: 700; margin-top: 1px; }
  .phase-divider .phase-desc { font-size: 10px; opacity: 0.7; margin-top: 2px; }

  /* Accepted highlight box */
  .accepted-highlight { background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 4px; padding: 10px 14px; margin-top: 10px; }
  .accepted-highlight .ah-label { font-size: 9px; font-weight: 700; color: #10B981; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 4px; }
  .accepted-highlight .ah-name { font-size: 13px; font-weight: 700; color: #065F46; }
  .accepted-highlight .ah-detail { font-size: 10px; color: #047857; margin-top: 2px; }
  .header-banner .badge { display: inline-block; background: rgba(255,255,255,0.2); color: #fff; padding: 2px 10px; border-radius: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-left: 8px; vertical-align: middle; }
  .status-terminated { background: rgba(239,68,68,0.3) !important; }
  .status-completed { background: rgba(16,185,129,0.3) !important; }

  .meta-strip { display: flex; gap: 24px; margin-top: 14px; }
  .meta-strip .meta-item { }
  .meta-strip .meta-label { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.6; }
  .meta-strip .meta-value { font-size: 12px; font-weight: 600; }

  .container { padding: 0 30px 30px; }

  /* Parties */
  .parties-row { display: flex; gap: 24px; margin: 18px 0 14px; padding: 14px 16px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 4px; }
  .party-box { flex: 1; }
  .party-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94A3B8; margin-bottom: 3px; }
  .party-name { font-size: 13px; font-weight: 600; color: #1E3A5F; }
  .party-contact { font-size: 10px; color: #64748B; margin-top: 1px; }

  /* Section */
  .section { margin-top: 20px; page-break-inside: avoid; }
  .section-title { font-size: 13px; font-weight: 700; color: #1E3A5F; border-bottom: 2px solid #1E3A5F; padding-bottom: 5px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
  .section-title .icon { display: inline-block; width: 18px; height: 18px; background: #E8EEF4; border-radius: 3px; text-align: center; line-height: 18px; font-size: 10px; }

  /* Financial grid */
  .fin-grid { display: flex; flex-wrap: wrap; gap: 1px; background: #E2E8F0; border-radius: 4px; overflow: hidden; margin-bottom: 10px; }
  .fin-cell { flex: 1 1 32%; background: #fff; padding: 10px 12px; min-width: 140px; }
  .fin-cell .label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #94A3B8; }
  .fin-cell .value { font-size: 15px; font-weight: 700; color: #1E3A5F; margin-top: 2px; }
  .fin-cell .value.success { color: #10B981; }
  .fin-cell .value.warning { color: #F59E0B; }
  .fin-cell .value.error { color: #EF4444; }
  .fin-cell .value.cf-adjusted { color: #e74c3c; }
  .fin-cell .original { font-size: 9px; color: #94A3B8; text-decoration: line-through; margin-top: 1px; }
  .cf-badge { display: inline-block; background: #FFF3E0; color: #e74c3c; font-size: 8px; font-weight: 700; padding: 1px 4px; border-radius: 3px; margin-left: 4px; }

  /* Progress bar */
  .progress-row { display: flex; align-items: center; gap: 10px; margin: 8px 0; }
  .progress-label { font-size: 11px; font-weight: 600; color: #1E3A5F; min-width: 130px; }
  .progress-track { flex: 1; height: 8px; background: #F1F5F9; border-radius: 4px; overflow: hidden; }
  .progress-fill { height: 100%; border-radius: 4px; }
  .progress-fill-success { background: #10B981; }
  .progress-fill-info { background: #3B82F6; }
  .progress-fill-error { background: #EF4444; }
  .progress-pct { font-size: 11px; font-weight: 700; min-width: 36px; text-align: right; }

  /* Milestone table */
  .ms-table { width: 100%; border-collapse: collapse; font-size: 10px; }
  .ms-table th { background: #F8FAFC; text-align: left; padding: 7px 8px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748B; border-bottom: 2px solid #E2E8F0; }
  .ms-table td { padding: 8px 8px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
  .ms-table tr:last-child td { border-bottom: none; }
  .ms-seq { display: inline-flex; width: 22px; height: 22px; border-radius: 11px; background: #E8EEF4; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #1E3A5F; }
  .ms-title { font-weight: 600; color: #1E3A5F; }
  .ms-group { font-size: 9px; color: #94A3B8; }

  /* Badges */
  .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
  .badge-success { background: #D1FAE5; color: #10B981; }
  .badge-warning { background: #FEF3C7; color: #F59E0B; }
  .badge-info { background: #DBEAFE; color: #3B82F6; }
  .badge-error { background: #FEE2E2; color: #EF4444; }
  .badge-muted { background: #F1F5F9; color: #94A3B8; }

  /* Payment table */
  .pay-table { width: 100%; border-collapse: collapse; font-size: 10px; }
  .pay-table th { background: #F8FAFC; text-align: left; padding: 6px 8px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748B; border-bottom: 2px solid #E2E8F0; }
  .pay-table td { padding: 7px 8px; border-bottom: 1px solid #F1F5F9; }
  .pay-table .amount { font-weight: 700; text-align: right; }
  .pay-totals { display: flex; gap: 12px; margin-bottom: 10px; }
  .pay-total-box { flex: 1; border: 1px solid #E2E8F0; border-radius: 4px; padding: 8px; text-align: center; }
  .pay-total-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; }
  .pay-total-value { font-size: 13px; font-weight: 700; margin-top: 2px; }

  /* History timeline */
  .timeline-row { display: flex; padding: 6px 0; border-bottom: 1px solid #F1F5F9; }
  .timeline-dot { width: 8px; height: 8px; border-radius: 4px; margin-top: 4px; margin-right: 10px; flex-shrink: 0; }
  .timeline-content { flex: 1; }
  .timeline-action { font-weight: 600; }
  .timeline-detail { font-size: 10px; color: #64748B; margin-top: 1px; }
  .timeline-date { font-size: 9px; color: #94A3B8; margin-top: 2px; }

  /* Footer */
  .report-footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #E2E8F0; text-align: center; font-size: 9px; color: #94A3B8; }
  .report-footer .brand { font-weight: 700; color: #1E3A5F; }

  .cf-note { display: flex; align-items: center; gap: 6px; margin-top: 6px; padding: 6px 10px; background: #FEF2F2; border-radius: 4px; font-size: 10px; color: #e74c3c; font-weight: 600; }
</style>
</head>
<body>

<!-- ═════════ BANNER ═════════ -->
<div class="header-banner">
  <h1>${escHtml(header.project_title)}
    <span class="badge ${header.status === 'terminated' ? 'status-terminated' : header.status === 'completed' ? 'status-completed' : ''}">${escHtml((header.status || '').replace(/_/g, ' ').toUpperCase())}</span>
  </h1>
  ${header.project_description ? `<div class="subtitle">${escHtml(header.project_description)}</div>` : ''}
  <div class="meta-strip">
    <div class="meta-item">
      <div class="meta-label">Location</div>
      <div class="meta-value">${escHtml(header.project_location || '—')}</div>
    </div>
    <div class="meta-item">
      <div class="meta-label">Start Date</div>
      <div class="meta-value">${fmtDate(header.original_start_date)}</div>
    </div>
    <div class="meta-item">
      <div class="meta-label">${header.was_extended ? 'Current End Date' : 'End Date'}</div>
      <div class="meta-value">${fmtDate(header.current_end_date)}</div>
    </div>
    ${header.was_extended && header.original_end_date !== header.current_end_date ? `
    <div class="meta-item">
      <div class="meta-label">Original End</div>
      <div class="meta-value" style="text-decoration: line-through; opacity: 0.6;">${fmtDate(header.original_end_date)}</div>
    </div>` : ''}
  </div>
</div>

<div class="container">

<!-- Parties -->
<div class="parties-row">
  <div class="party-box">
    <div class="party-label">Property Owner</div>
    <div class="party-name">${escHtml(header.owner_name)}</div>
    ${header.owner_email ? `<div class="party-contact">${escHtml(header.owner_email)}</div>` : ''}
  </div>
  <div class="party-box">
    <div class="party-label">Contractor</div>
    <div class="party-name">${escHtml(header.contractor_name)}</div>
    ${header.contractor_company ? `<div class="party-contact">${escHtml(header.contractor_company)}</div>` : ''}
  </div>
</div>

<!-- ═════ PHASE 1: PROJECT POSTING DETAILS ═════ -->
<div class="phase-divider phase-divider-dark">
  <div class="phase-num">Section 1</div>
  <div class="phase-title">Project Posting Details</div>
  <div class="phase-desc">Original project listing and requirements as posted by the property owner</div>
</div>

${project_post ? `
<div class="section">
  <div class="section-title">Project Posting Details</div>
  <div class="fin-grid">
    <div class="fin-cell"><div class="label">Property Type</div><div class="value" style="font-size:12px; text-transform:capitalize;">${escHtml(project_post.property_type || '—')}</div></div>
    <div class="fin-cell"><div class="label">Budget Range</div><div class="value" style="font-size:12px;">${project_post.budget_range_min > 0 ? `${fmt(project_post.budget_range_min)} – ${fmt(project_post.budget_range_max)}` : '—'}</div></div>
    ${project_post.lot_size ? `<div class="fin-cell"><div class="label">Lot Size</div><div class="value" style="font-size:12px;">${escHtml(project_post.lot_size)}</div></div>` : ''}
    ${project_post.floor_area ? `<div class="fin-cell"><div class="label">Floor Area</div><div class="value" style="font-size:12px;">${escHtml(project_post.floor_area)}</div></div>` : ''}
    ${project_post.to_finish ? `<div class="fin-cell"><div class="label">Target Duration</div><div class="value" style="font-size:12px;">${project_post.to_finish} month${project_post.to_finish > 1 ? 's' : ''}</div></div>` : ''}
    <div class="fin-cell"><div class="label">Posted On</div><div class="value" style="font-size:12px;">${fmtDate(project_post.posted_at)}</div></div>
    ${project_post.bidding_due ? `<div class="fin-cell"><div class="label">Bidding Deadline</div><div class="value" style="font-size:12px;">${fmtDate(project_post.bidding_due)}</div></div>` : ''}
    ${project_post.total_files > 0 ? `<div class="fin-cell"><div class="label">Attached Files</div><div class="value" style="font-size:12px;">${project_post.total_files} file${project_post.total_files > 1 ? 's' : ''}</div></div>` : ''}
  </div>
  ${project_post.description ? `<div style="margin-top:8px; font-size:11px; color:#64748B; line-height:1.6;"><strong>Description:</strong> ${escHtml(project_post.description)}</div>` : ''}
  ${Object.keys(project_post.files_by_type || {}).length > 0 ? `
  <div style="margin-top:8px;">
    <div style="font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:#94A3B8; margin-bottom:4px;">Attached Documents</div>
    ${Object.entries(project_post.files_by_type).map(([type, info]) =>
      `<div style="font-size:10px; color:#64748B; margin-bottom:2px;">
        <span style="text-transform:capitalize; font-weight:600;">${escHtml(type.replace(/_/g, ' '))}:</span> ${(info as any).count} file${(info as any).count > 1 ? 's' : ''} — ${((info as any).files as string[]).map((f: string) => escHtml(f)).join(', ')}
      </div>`
    ).join('')}
  </div>` : ''}
</div>
` : ''}

<!-- ═════ PHASE 2: BIDDING HISTORY ═════ -->
<div class="phase-divider phase-divider-dark">
  <div class="phase-num">Section 2</div>
  <div class="phase-title">Bidding History</div>
  <div class="phase-desc">All contractor bids received for this project &mdash; the accepted bid is highlighted</div>
</div>

${bidding_history && bidding_history.length > 0 ? `
<div class="section">
  <div class="section-title">Bidding History (${bidding_history.length} bid${bidding_history.length > 1 ? 's' : ''})</div>
  <table class="ms-table">
    <thead>
      <tr>
        <th>Contractor</th>
        <th style="text-align:right">Proposed Cost</th>
        <th>Timeline</th>
        <th>Status</th>
        <th>Submitted</th>
        <th>Files</th>
      </tr>
    </thead>
    <tbody>
${bidding_history.map(b => `
      <tr style="${b.bid_status === 'accepted' ? 'background:#D1FAE5;' : ''}">
        <td>
          <div style="font-weight:600;">${escHtml(b.company_name)}</div>
          ${b.years_of_experience ? `<div style="font-size:9px; color:#94A3B8;">${b.years_of_experience} yrs exp. · ${b.completed_projects ?? 0} projects</div>` : ''}
        </td>
        <td style="text-align:right; font-weight:700;">${fmt(b.proposed_cost)}</td>
        <td>${b.estimated_timeline ? `${b.estimated_timeline} mo.` : '—'}</td>
        <td><span class="badge ${statusClass(b.bid_status)}">${escHtml((b.bid_status || '').replace(/_/g, ' '))}</span></td>
        <td>${fmtDate(b.submitted_at)}</td>
        <td>${b.file_count > 0 ? `${b.file_count} file${b.file_count > 1 ? 's' : ''}` : '—'}</td>
      </tr>
      ${b.contractor_notes ? `<tr style="${b.bid_status === 'accepted' ? 'background:#D1FAE5;' : ''}"><td colspan="6" style="font-size:10px; color:#64748B; font-style:italic; padding-top:0; border-bottom:1px solid #E2E8F0;">"${escHtml(b.contractor_notes)}"</td></tr>` : ''}
`).join('')}
    </tbody>
  </table>
${(() => {
  const accepted = bidding_history.find(b => b.bid_status === 'accepted');
  return accepted ? `
  <div class="accepted-highlight">
    <div class="ah-label">Accepted Bid</div>
    <div class="ah-name">${escHtml(accepted.company_name)}</div>
    <div class="ah-detail">Proposed Cost: ${fmt(accepted.proposed_cost)} &bull; Timeline: ${accepted.estimated_timeline ? accepted.estimated_timeline + ' months' : 'N/A'}${accepted.contractor_notes ? ` &bull; "${escHtml(accepted.contractor_notes)}"` : ''}</div>
  </div>` : '';
})()}
</div>
` : ''}

<!-- ═════ PHASE 3: MILESTONE SETUP HISTORY ═════ -->
<div class="phase-divider phase-divider-dark">
  <div class="phase-num">Section 3</div>
  <div class="phase-title">Milestone Setup History</div>
  <div class="phase-desc">Milestone proposals submitted by the contractor &mdash; the approved setup became the project work plan</div>
</div>

${milestone_setups && milestone_setups.length > 0 ? `
<div class="section">
  <div class="section-title">Milestone Setup History (${milestone_setups.length} submission${milestone_setups.length > 1 ? 's' : ''})</div>
  ${milestone_setups.map((ms, idx) => `
  <div style="margin-bottom:14px; ${ms.is_deleted ? 'opacity:0.5;' : ''}">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
      <div>
        <span style="font-weight:700; font-size:12px; color:#1E3A5F;">${escHtml(ms.name || `Setup #${idx + 1}`)}</span>
        <span class="badge ${statusClass(ms.setup_status)}" style="margin-left:8px;">${escHtml((ms.setup_status || '').replace(/_/g, ' '))}</span>
        ${ms.is_deleted ? '<span class="badge badge-muted" style="margin-left:4px;">Superseded</span>' : ''}
      </div>
      <div style="font-size:10px; color:#94A3B8;">
        ${fmtDate(ms.created_at)}${ms.start_date ? ` · ${fmtDate(ms.start_date)} – ${fmtDate(ms.end_date)}` : ''}
      </div>
    </div>
    ${ms.description ? `<div style="font-size:10px; color:#64748B; margin-bottom:6px;">${escHtml(ms.description)}</div>` : ''}
    <table class="pay-table">
      <thead><tr><th style="width:30px">#</th><th>Item</th><th style="text-align:right">Cost</th><th>Due Date</th><th>Status</th></tr></thead>
      <tbody>
      ${ms.items.map(i => `
        <tr>
          <td>${i.sequence}</td>
          <td style="font-weight:500;">${escHtml(i.title)}</td>
          <td style="text-align:right; font-weight:600;">${fmt(i.cost)}</td>
          <td>${fmtDate(i.due_date)}</td>
          <td><span class="badge ${statusClass(i.status)}">${escHtml((i.status || '').replace(/_/g, ' '))}</span></td>
        </tr>`).join('')}
        <tr style="background:#F8FAFC; font-weight:700;">
          <td colspan="2" style="text-align:right;">Total (${ms.item_count} items)</td>
          <td style="text-align:right;">${fmt(ms.total_cost)}</td>
          <td colspan="2"></td>
        </tr>
      </tbody>
    </table>
  </div>
  `).join('')}
</div>
` : ''}

<!-- ═════ PHASE 4: PROJECT SUMMARY ═════ -->
<div class="phase-divider phase-divider-green">
  <div class="phase-num">Section 4</div>
  <div class="phase-title">Project Summary</div>
  <div class="phase-desc">The finalized project based on the approved milestone setup &mdash; financial overview, progress, and records</div>
</div>

<!-- ═════════ EXECUTIVE OVERVIEW ═════════ -->
<div class="section">
  <div class="section-title">Executive Overview</div>

  <div class="progress-row">
    <span class="progress-label">Milestone Progress</span>
    <div class="progress-track"><div class="progress-fill progress-fill-success" style="width:${progressPercent}%"></div></div>
    <span class="progress-pct">${progressPercent}%</span>
  </div>
  <div style="font-size:10px; color:#64748B; margin-bottom:8px; margin-left:140px;">${overview.completed_milestones} of ${overview.total_milestones} milestones completed</div>

  <div class="progress-row">
    <span class="progress-label">Budget Utilization</span>
    <div class="progress-track"><div class="progress-fill ${budgetUtil > 100 ? 'progress-fill-error' : 'progress-fill-info'}" style="width:${Math.min(budgetUtil, 100)}%"></div></div>
    <span class="progress-pct">${budgetUtil}%</span>
  </div>

  <div class="fin-grid" style="margin-top: 12px;">
    <div class="fin-cell"><div class="label">Original Budget</div><div class="value">${fmt(overview.original_budget)}</div></div>
    <div class="fin-cell"><div class="label">Current Budget</div><div class="value${overview.current_budget !== overview.original_budget ? ' warning' : ''}">${fmt(overview.current_budget)}</div></div>
    <div class="fin-cell"><div class="label">Total Paid</div><div class="value success">${fmt(overview.total_paid)}</div></div>
    <div class="fin-cell"><div class="label">Pending</div><div class="value warning">${fmt(overview.total_pending)}</div></div>
    <div class="fin-cell"><div class="label">Remaining</div><div class="value">${fmt(overview.remaining_balance)}</div></div>
    <div class="fin-cell"><div class="label">Payment Mode</div><div class="value" style="font-size:12px; text-transform:capitalize;">${escHtml((overview.payment_mode || '').replace(/_/g, ' '))}</div></div>
  </div>
</div>

<!-- ═════════ MILESTONE BREAKDOWN ═════════ -->
<div class="section">
  <div class="section-title">Milestone Breakdown (${milestones.length})</div>
  <table class="ms-table">
    <thead>
      <tr>
        <th style="width:30px">#</th>
        <th>Milestone Item</th>
        <th>Status</th>
        <th style="text-align:right">Budget</th>
        <th style="text-align:right">Paid</th>
        <th style="text-align:right">Remaining</th>
        <th>Due Date</th>
      </tr>
    </thead>
    <tbody>
${milestones.map(m => {
  const cf = m.carry_forward_amount ?? 0;
  return `      <tr>
        <td><span class="ms-seq">${m.sequence_order}</span></td>
        <td><div class="ms-title">${escHtml(m.title)}</div><div class="ms-group">${escHtml(m.milestone_name)}</div></td>
        <td><span class="badge ${statusClass(m.status)}">${escHtml((m.status || '').replace(/_/g, ' '))}</span></td>
        <td style="text-align:right">
          ${cf !== 0 ? `
            <div style="font-weight:700; color:#e74c3c;">${fmt(m.current_allocation)}</div>
            <div style="font-size:9px; color:#94A3B8; text-decoration:line-through;">${fmt(m.original_allocation)}</div>
            <span class="cf-badge">${cf < 0 ? '−CF' : '+CF'}</span>
          ` : `<div style="font-weight:700;">${fmt(m.current_allocation)}</div>`}
        </td>
        <td style="text-align:right; color:#10B981; font-weight:600;">${fmt(m.total_paid)}</td>
        <td style="text-align:right; font-weight:600;">${fmt(m.remaining)}</td>
        <td>${fmtDate(m.current_due_date)}${m.was_extended ? `<br><span style="font-size:9px; color:#F59E0B;">Extended ${m.extension_count}×</span>` : ''}</td>
      </tr>`;
}).join('\n')}
    </tbody>
  </table>
</div>

${budget_history.length > 0 ? `
<!-- ═════════ BUDGET HISTORY ═════════ -->
<div class="section">
  <div class="section-title">Budget History (${budget_history.length})</div>
  ${budget_history.map(bh => `
  <div class="timeline-row">
    <div class="timeline-dot" style="background:#EC7E00;"></div>
    <div class="timeline-content">
      <div class="timeline-action">${escHtml(bh.change_type ? `Budget ${bh.change_type}` : 'Timeline Update')}
        <span class="badge ${statusClass(bh.status)}" style="margin-left:6px;">${escHtml(bh.status)}</span>
      </div>
      ${bh.previous_budget != null && bh.updated_budget != null ? `<div class="timeline-detail">${fmt(bh.previous_budget)} → ${fmt(bh.updated_budget)}</div>` : ''}
      ${bh.previous_end_date && bh.proposed_end_date ? `<div class="timeline-detail">${fmtDate(bh.previous_end_date)} → ${fmtDate(bh.proposed_end_date)}</div>` : ''}
      ${bh.reason ? `<div class="timeline-detail" style="font-style:italic;">"${escHtml(bh.reason)}"</div>` : ''}
      <div class="timeline-date">${fmtDate(bh.date_proposed)}</div>
    </div>
  </div>`).join('')}
</div>
` : ''}

${change_history.length > 0 ? `
<!-- ═════════ CHANGE LOG ═════════ -->
<div class="section">
  <div class="section-title">Change Log (${change_history.length})</div>
  ${change_history.map(evt => `
  <div class="timeline-row">
    <div class="timeline-dot" style="background:#3B82F6;"></div>
    <div class="timeline-content">
      <div class="timeline-action">${escHtml(evt.action)}</div>
      ${evt.performed_by ? `<div class="timeline-detail">by ${escHtml(evt.performed_by)}</div>` : ''}
      ${evt.notes ? `<div class="timeline-detail" style="font-style:italic;">"${escHtml(evt.notes)}"</div>` : ''}
      <div class="timeline-date">${fmtDateTime(evt.date)}</div>
    </div>
  </div>`).join('')}
</div>
` : ''}

<!-- ═════════ PAYMENTS ═════════ -->
<div class="section">
  <div class="section-title">Payments (${payments.records.length})</div>
  <div class="pay-totals">
    <div class="pay-total-box"><div class="pay-total-label" style="color:#10B981;">Approved</div><div class="pay-total-value" style="color:#10B981;">${fmt(payments.total_approved)}</div></div>
    <div class="pay-total-box"><div class="pay-total-label" style="color:#F59E0B;">Pending</div><div class="pay-total-value" style="color:#F59E0B;">${fmt(payments.total_pending)}</div></div>
    <div class="pay-total-box"><div class="pay-total-label" style="color:#EF4444;">Rejected</div><div class="pay-total-value" style="color:#EF4444;">${fmt(payments.total_rejected)}</div></div>
  </div>
  ${payments.records.length === 0 ? '<div style="color:#94A3B8; font-style:italic; padding:8px 0;">No payment records.</div>' : `
  <table class="pay-table">
    <thead>
      <tr>
        <th>Milestone</th>
        <th>Type</th>
        <th>Status</th>
        <th style="text-align:right">Amount</th>
        <th>Reference</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
${payments.records.map(p => `
      <tr>
        <td>${escHtml(p.milestone)}</td>
        <td style="text-transform:capitalize;">${escHtml((p.payment_type || '').replace(/_/g, ' '))}</td>
        <td><span class="badge ${statusClass(p.status)}">${escHtml(p.status)}</span></td>
        <td class="amount">${fmt(p.amount)}</td>
        <td>${escHtml(p.transaction_number || '—')}</td>
        <td>${fmtDate(p.transaction_date)}</td>
      </tr>`).join('')}
    </tbody>
  </table>`}
</div>

${progress_reports.length > 0 ? `
<!-- ═════════ PROGRESS REPORTS ═════════ -->
<div class="section">
  <div class="section-title">Progress Reports (${progress_reports.length})</div>
  <table class="pay-table">
    <thead>
      <tr>
        <th>Report</th>
        <th>Milestone</th>
        <th>Status</th>
        <th>Submitted</th>
      </tr>
    </thead>
    <tbody>
${progress_reports.map(pr => `
      <tr>
        <td style="font-weight:600;">${escHtml(pr.report_title || 'Progress Report')}</td>
        <td>${escHtml(pr.milestone)}</td>
        <td><span class="badge ${statusClass(pr.status)}">${escHtml((pr.status || '').replace(/_/g, ' '))}</span></td>
        <td>${fmtDate(pr.submitted_at)}</td>
      </tr>`).join('')}
    </tbody>
  </table>
</div>
` : ''}

${file_summary && file_summary.grand_total > 0 ? `
<!-- ═════════ FILE & DOCUMENT SUMMARY ═════════ -->
<div class="section">
  <div class="section-title">Files & Documents Summary</div>
  <div class="fin-grid">
    ${file_summary.project_files.total > 0 ? `<div class="fin-cell"><div class="label">Project Files</div><div class="value" style="font-size:14px;">${file_summary.project_files.total}</div>
      <div style="font-size:9px; color:#94A3B8; margin-top:2px;">${Object.entries(file_summary.project_files.by_type).map(([t, c]) => `${(t || 'other').replace(/_/g, ' ')}: ${c}`).join(', ')}</div>
    </div>` : ''}
    ${file_summary.progress_files > 0 ? `<div class="fin-cell"><div class="label">Progress Report Files</div><div class="value" style="font-size:14px;">${file_summary.progress_files}</div></div>` : ''}
    ${file_summary.payment_receipts > 0 ? `<div class="fin-cell"><div class="label">Payment Receipts</div><div class="value" style="font-size:14px;">${file_summary.payment_receipts}</div></div>` : ''}
    ${file_summary.bid_files > 0 ? `<div class="fin-cell"><div class="label">Bid Documents</div><div class="value" style="font-size:14px;">${file_summary.bid_files}</div></div>` : ''}
    ${file_summary.item_files > 0 ? `<div class="fin-cell"><div class="label">Milestone Item Files</div><div class="value" style="font-size:14px;">${file_summary.item_files}</div></div>` : ''}
    <div class="fin-cell" style="background:#F8FAFC;"><div class="label">Total Documents</div><div class="value" style="font-size:16px; color:#1E3A5F;">${file_summary.grand_total}</div></div>
  </div>
</div>
` : ''}

<!-- Footer -->
<div class="report-footer">
  <span class="brand">Legatura</span> &mdash; Project Report &bull; Generated ${fmtDateTime(data.generated_at)}
</div>

</div>
</body>
</html>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// Public API
// ─────────────────────────────────────────────────────────────────────────────
export async function generateProjectReportPdf(
  data: ProjectSummaryData,
  onProgress?: (step: string) => void,
): Promise<void> {
  try {
    onProgress?.('Building report…');
    const html = buildReportHtml(data);
    const sanitizedTitle = (data.header.project_title || 'Project_Report')
      .replace(/[^a-zA-Z0-9_\- ]/g, '')
      .replace(/\s+/g, '_');
    const fileName = `${sanitizedTitle}_Report`;

    onProgress?.('Generating PDF…');
    const { uri } = await Print.printToFileAsync({
      html,
      base64: false,
    });

    onProgress?.('Preparing to share…');
    if (await Sharing.isAvailableAsync()) {
      await Sharing.shareAsync(uri, {
        mimeType: 'application/pdf',
        dialogTitle: `${data.header.project_title} - Project Report`,
        UTI: 'com.adobe.pdf',
      });
    } else {
      Alert.alert('PDF Saved', `Report saved to:\n${uri}`);
    }
  } catch (err: any) {
    console.error('PDF generation error:', err);
    Alert.alert('Error', 'Failed to generate PDF report. Please try again.');
  }
}
