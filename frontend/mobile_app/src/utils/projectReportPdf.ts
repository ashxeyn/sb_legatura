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
  .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
  .header-left { flex: 1; }
  .header-logo { width: 60px; height: 60px; margin-left: 16px; flex-shrink: 0; background: #fff; border-radius: 12px; padding: 6px; display: flex; align-items: center; justify-content: center; }
  .header-logo svg { width: 100%; height: 100%; }
  .header-banner h1 { font-size: 20px; font-weight: 700; margin-bottom: 3px; letter-spacing: 0.3px; }
  .header-banner .subtitle { font-size: 11px; opacity: 0.8; }

  /* Phase divider */
  .phase-divider { margin: 28px 0 6px; padding: 12px 16px 10px; background: linear-gradient(135deg, #1E3A5F 0%, #2D5A8E 100%); border-radius: 4px; color: #fff; }
  .phase-divider .phase-num { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; opacity: 0.7; }
  .phase-divider .phase-title { font-size: 15px; font-weight: 700; margin-top: 1px; }
  .phase-divider .phase-desc { font-size: 10px; opacity: 0.7; margin-top: 2px; }
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
  <div class="header-top">
    <div class="header-left">
      <h1>${escHtml(header.project_title)}
        <span class="badge ${header.status === 'terminated' ? 'status-terminated' : header.status === 'completed' ? 'status-completed' : ''}">${escHtml((header.status || '').replace(/_/g, ' ').toUpperCase())}</span>
      </h1>
      ${header.project_description ? `<div class="subtitle">${escHtml(header.project_description)}</div>` : ''}
    </div>
    <div class="header-logo">
      <svg width="320" height="320" viewBox="0 0 320 320" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M167.241 262.485C165.47 262.189 164.468 261.631 160.938 258.971C159.692 258.032 157.916 256.714 156.993 256.043C156.069 255.372 155.313 254.788 155.313 254.744C155.313 254.701 155.908 253.909 156.634 252.986C158.281 250.895 159.107 249.497 159.646 247.891C160.033 246.738 160.066 246.445 160.062 244.141C160.058 241.858 160.022 241.532 159.643 240.391C159.03 238.547 158.235 237.329 156.678 235.846C155.61 234.83 154.992 234.387 153.866 233.832C152.349 233.086 150.363 232.5 149.35 232.5C148.8 232.5 148.75 232.464 148.749 232.07C148.747 231.122 148.268 228.542 147.871 227.339C146.326 222.667 141.13 219.179 136.044 219.402L134.61 219.464L134.264 218.066C132.821 212.242 128.027 208.291 122.344 208.241C120.485 208.225 119.199 208.496 117.739 209.212C117.225 209.463 116.799 209.656 116.791 209.639C116.783 209.623 116.546 209.082 116.264 208.437C114.12 203.542 109.731 200.469 104.885 200.469C103.14 200.469 102.026 200.751 100.157 201.666C98.9891 202.237 98.4363 202.629 97.5679 203.501C96.3779 204.695 94.7388 206.708 92.9857 209.129L91.8876 210.645L88.8926 207.737C85.2273 204.178 82.7748 202.295 79.8504 200.795C77.4016 199.54 77.4595 199.565 67.8426 195.53C63.8629 193.861 60.4251 192.376 60.2032 192.231C59.7204 191.914 59.0666 190.582 59.0645 189.91C59.0635 189.645 59.866 187.316 60.8473 184.733C62.7876 179.627 67.0507 168.102 68.7526 163.359C69.3385 161.727 70.3241 159.02 70.9432 157.344C72.8035 152.304 75.2945 145.504 75.9698 143.62C76.3188 142.647 76.7791 141.642 76.9929 141.388C77.7182 140.526 79.1151 140.225 80.2304 140.691C80.5232 140.813 82.191 141.752 83.9363 142.778C85.6816 143.803 88.0941 145.187 89.2973 145.853C102.116 152.947 104.372 154.123 106.34 154.739C108.765 155.498 109.578 155.548 119.037 155.528C127.197 155.51 127.834 155.528 127.77 155.771C127.689 156.083 124.845 160.292 123.013 162.812C122.294 163.801 121.129 165.408 120.424 166.385C119.718 167.362 118.965 168.381 118.75 168.651C118.535 168.921 118.289 169.255 118.204 169.393C118.118 169.532 117.787 170.038 117.468 170.518C116.558 171.89 116.194 173.313 116.288 175.133C116.539 179.994 120.29 184.12 125.86 185.661C127.277 186.053 130.944 186.208 132.627 185.947C137.718 185.156 143.756 181.833 147.729 177.634C148.327 177.002 149.719 175.553 150.822 174.414L152.829 172.344H156.796H160.764L162.687 173.883C164.451 175.295 167.413 177.553 177.266 184.996C179.243 186.489 181.985 188.599 183.36 189.686C185.717 191.547 189.393 194.4 197.735 200.842C208.505 209.159 218.034 216.669 218.449 217.167C219.154 218.013 219.983 219.794 220.187 220.897C220.524 222.717 219.812 225.043 218.441 226.6C218.007 227.093 217.397 227.532 216.6 227.926C215.459 228.49 215.34 228.516 213.829 228.516C212.32 228.516 212.198 228.49 211.086 227.935C210.446 227.616 209.114 226.715 208.125 225.933C207.137 225.152 204.817 223.342 202.969 221.912C201.121 220.482 198.426 218.395 196.98 217.273C195.534 216.152 193.302 214.426 192.021 213.437C183.969 207.225 181.658 205.414 180.399 204.334C178.434 202.649 177.567 202.457 176.369 203.445C175.996 203.752 175.717 204.176 175.544 204.697C175.257 205.56 175.316 206.04 175.803 206.83C176.073 207.268 183.413 213.088 194.599 221.734C196.312 223.057 198.456 224.72 199.365 225.428C200.273 226.136 201.908 227.389 202.998 228.212C205.084 229.788 205.488 230.21 206.136 231.491C206.868 232.935 207.078 233.961 206.997 235.692C206.934 237.024 206.849 237.427 206.441 238.318C204.906 241.672 201.31 242.936 197.856 241.334C196.972 240.924 196.186 240.356 192.086 237.165C190.612 236.017 188.545 234.41 187.493 233.594C186.442 232.777 183.244 230.281 180.387 228.047C172.149 221.605 169.351 219.479 168.596 219.09C167.645 218.6 166.295 218.664 165.691 219.229C164.771 220.088 164.561 221.409 165.196 222.35C165.599 222.948 165.864 223.167 170.69 226.875C172.647 228.379 174.892 230.105 175.679 230.712C176.466 231.317 178.762 233.08 180.782 234.627C187.272 239.601 188.815 240.791 189.499 241.352C190.821 242.435 191.563 244.139 191.563 246.094C191.563 248.694 190.236 250.852 187.969 251.936C187.023 252.389 186.836 252.422 185.235 252.422C183.697 252.422 183.422 252.378 182.623 252.003C181.855 251.643 178.83 249.422 171.172 243.594C170.227 242.875 168.976 241.853 168.393 241.324C166.477 239.586 165.386 239.327 164.176 240.324C163.14 241.178 163.038 242.502 163.929 243.518C164.427 244.084 164.846 244.418 170.253 248.559C172.067 249.949 173.741 251.335 173.973 251.639C175.005 252.993 175.473 255.007 175.161 256.756C174.617 259.797 172.424 262.02 169.498 262.494C168.399 262.673 168.365 262.673 167.241 262.485Z" fill="#022644" stroke="#022644"/>
        <path d="M175.468 143.721C168.804 140.132 169.327 140.243 158.593 140.162C149.429 140.094 148.65 140.14 145.674 140.921C144.726 141.169 143.887 141.334 143.811 141.287C143.729 141.236 143.671 120.829 143.671 92.3153V43.4303L144.093 42.9281C144.544 42.3925 145.307 42 145.897 42C146.26 42 148.463 43.2475 152.108 45.5162C153.183 46.185 155.362 47.5194 156.952 48.4825C158.542 49.4453 161.777 51.4156 164.14 52.8606C166.503 54.3053 169.491 56.1319 170.78 56.9197C177.046 60.7472 178.725 61.7978 179.186 62.1784L179.687 62.5909L179.651 69.7566C179.631 73.6975 179.613 92.4081 179.612 111.336C179.609 138.764 179.569 145.749 179.413 145.745C179.306 145.743 177.53 144.831 175.468 143.721Z" fill="#FFC100" stroke="#FFC100"/>
        <path d="M127.674 148.607C127.613 148.545 123.365 148.454 118.234 148.405C110.403 148.329 108.831 148.277 108.437 148.082C107.894 147.811 107.913 148.173 108.191 143.046C108.402 139.167 108.41 106.942 108.202 98.9596C108.063 93.6156 108.072 93.1437 108.323 92.6334C108.649 91.9681 108.827 91.8481 113.671 89.0312C115.777 87.8071 118.659 86.1224 120.078 85.2878C121.496 84.4531 124.132 82.9043 125.937 81.8465C127.742 80.7884 130.589 79.1115 132.265 78.1199C136.843 75.4112 136.92 75.3724 137.331 75.5599C137.683 75.7199 137.683 75.7215 137.786 81.0084C137.843 83.9171 137.937 99.2106 137.994 114.994L138.098 143.691L135.845 144.816C134.607 145.434 132.639 146.565 131.473 147.329C129.517 148.611 129.292 148.719 128.569 148.719C128.138 148.719 127.736 148.668 127.674 148.607Z" fill="#F9A600" stroke="#F9A600"/>
        <path d="M201.175 153.917C199.133 153.627 197.033 152.753 191.054 149.703C187.843 148.065 185.185 146.644 185.148 146.545C185.11 146.447 185.097 133.311 185.119 117.355L185.159 88.3438L186.878 89.4044C192.582 92.9238 206.63 101.389 208.675 102.54C210.377 103.498 211.178 104.039 211.368 104.363C211.619 104.79 211.65 106.372 211.795 126.219C211.882 137.987 211.953 148.621 211.954 149.85L211.956 152.085L211.374 152.328C210.435 152.721 206.818 153.613 205.143 153.865C203.436 154.122 202.688 154.132 201.175 153.917Z" fill="#022644" stroke="#022644"/>
        <path d="M220.547 211.219C219.086 210.015 217.082 208.411 216.093 207.656C215.105 206.9 212.574 204.934 210.468 203.285C202.992 197.431 195.365 191.591 190.509 188.001C185.994 184.663 183.509 182.773 177.89 178.398C172.144 173.924 169.551 171.92 167.422 170.309C166.132 169.333 164.526 168.091 163.851 167.549L162.625 166.563L160.257 166.562C158.956 166.562 156.168 166.518 154.062 166.464L150.234 166.365L147.904 168.924C144.176 173.021 141.184 175.921 139.797 176.784C135.472 179.474 130.367 180.592 126.565 179.68C123.684 178.99 122.188 177.486 122.187 175.279C122.185 173.688 122.519 173.053 125.287 169.394C126.691 167.536 129.185 164.153 130.827 161.875C135.22 155.786 135.918 154.893 137.361 153.512C140.212 150.785 144.579 148.537 148.75 147.652C150.142 147.356 151.112 147.286 155.156 147.191C157.777 147.129 161.89 147.09 164.297 147.105C170.884 147.143 171.947 147.429 178.491 150.918C184.218 153.972 190.384 157.007 192.768 157.946C195.68 159.093 197.567 159.514 200.312 159.629C205.097 159.83 208.442 158.973 214.14 156.084C223.849 151.163 229.724 147.991 234.737 144.966C236.011 144.198 237.292 143.469 237.584 143.347C238.705 142.879 239.889 143.2 240.891 144.244C241.501 144.88 241.986 145.92 244.364 151.682C247.319 158.839 252.159 170.352 254.07 174.766C255.037 177 256.159 179.602 256.562 180.547C256.965 181.492 258.079 184.032 259.038 186.191C260.849 190.268 261.104 191.13 260.786 192.094C260.403 193.253 259.645 193.825 257.031 194.922C256.022 195.345 243.406 200.889 240.361 202.248C235.215 204.542 231.584 206.643 229.053 208.788C228.414 209.329 227.328 210.225 226.64 210.778C225.953 211.331 224.974 212.156 224.465 212.611C223.957 213.065 223.464 213.431 223.372 213.424C223.279 213.416 222.007 212.424 220.547 211.219Z" fill="#F78E00" stroke="#F78E00"/>
        <path d="M97.5395 227.883C96.3661 227.567 95.5367 227.106 94.6526 226.28C92.7583 224.508 92.0467 221.815 92.8158 219.325C93.1805 218.144 93.1955 218.122 100.375 208.61C102.075 206.357 104.909 205.723 107.48 207.021C109.019 207.797 110.134 209.02 111.09 210.977C111.905 212.645 111.908 212.656 111.851 213.943C111.776 215.651 111.522 216.13 109.259 218.84C108.249 220.05 106.55 222.089 105.484 223.371C102.736 226.676 102.408 226.998 101.177 227.596C99.9467 228.193 98.9751 228.27 97.5395 227.883Z" fill="#F78E00" stroke="#F78E00"/>
        <path d="M124.264 252.117C121.564 251.273 119.695 248.616 119.689 245.614C119.687 244.264 120.003 243.254 120.818 242.011C121.523 240.934 125.441 235.882 127.405 233.516C128.048 232.742 129.388 231.009 130.384 229.665C131.379 228.321 132.497 226.942 132.868 226.6C133.84 225.704 135.007 225.312 136.704 225.312C137.974 225.312 138.172 225.357 139.202 225.872C142.672 227.608 143.96 231.674 142.161 235.207C141.797 235.922 139.661 238.847 134.63 245.521C130.603 250.863 130.198 251.271 128.195 252.021C127.187 252.398 125.309 252.444 124.264 252.117Z" fill="#F78E00" stroke="#F78E00"/>
        <path d="M139.878 260.155C138.268 259.817 136.587 258.412 135.809 256.753C135.442 255.97 135.391 255.665 135.391 254.297C135.391 252.817 135.419 252.683 135.92 251.758C136.211 251.222 137.336 249.674 138.42 248.318C139.504 246.963 141.568 244.371 143.007 242.558C145.608 239.28 146.235 238.671 147.404 238.286C148.336 237.979 150.058 238.148 151.111 238.648C152.21 239.17 153.476 240.486 154.017 241.669C154.366 242.431 154.426 242.782 154.435 244.13C154.446 245.638 154.422 245.751 153.868 246.875C153.328 247.97 151.644 250.242 146.746 256.484C145.303 258.322 144.723 258.938 144.057 259.338C142.727 260.138 141.212 260.434 139.878 260.155Z" fill="#F78E00" stroke="#F78E00"/>
        <path d="M109.51 241.2C108.224 240.762 107.177 239.922 106.38 238.69C105.504 237.334 105.233 236.319 105.247 234.453C105.256 233.214 105.332 232.7 105.617 231.971C106.006 230.975 105.953 231.046 112.022 223.404C114.294 220.544 116.487 217.776 116.896 217.254C117.948 215.911 118.946 215.037 119.973 214.561C120.732 214.209 121.074 214.149 122.347 214.145C123.716 214.141 123.918 214.181 124.87 214.65C125.591 215.004 126.202 215.477 126.881 216.203C128.931 218.395 129.414 220.899 128.263 223.37C127.954 224.033 118.59 236.667 116.83 238.795C116.489 239.207 115.76 239.865 115.208 240.259C113.393 241.554 111.476 241.871 109.51 241.2Z" fill="#F78E00" stroke="#F78E00"/>
      </svg>
    </div>
  </div>
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

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- PHASE 1: PROJECT LIFECYCLE                                             -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="phase-divider">
  <div class="phase-num">Phase 1</div>
  <div class="phase-title">Project Lifecycle</div>
  <div class="phase-desc">How this project was created — from posting to contractor selection to milestone approval</div>
</div>

${project_post ? `
<!-- ═════════ PROJECT POSTING ═════════ -->
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

${bidding_history && bidding_history.length > 0 ? `
<!-- ═════════ BIDDING HISTORY ═════════ -->
<div class="section">
  <div class="section-title">Bidding History (${bidding_history.length} bid${bidding_history.length > 1 ? 's' : ''})</div>
  <div style="font-size:10px; color:#64748B; margin-bottom:10px;">All contractor bids submitted for this project. The accepted bid is highlighted in green.</div>
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
</div>
` : ''}

${milestone_setups && milestone_setups.length > 0 ? `
<!-- ═════════ MILESTONE SETUP HISTORY ═════════ -->
<div class="section">
  <div class="section-title">Milestone Setup History (${milestone_setups.length} submission${milestone_setups.length > 1 ? 's' : ''})</div>
  <div style="font-size:10px; color:#64748B; margin-bottom:10px;">All milestone setup proposals submitted by the contractor. The approved setup became the project's work schedule.</div>
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

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- PHASE 2: PROJECT SUMMARY                                               -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="phase-divider" style="background: linear-gradient(135deg, #065F46 0%, #047857 100%);">
  <div class="phase-num">Phase 2</div>
  <div class="phase-title">Project Summary</div>
  <div class="phase-desc">Financial overview, milestone progress, payments, and complete project records</div>
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
export async function generateProjectReportPdf(data: ProjectSummaryData): Promise<void> {
  try {
    const html = buildReportHtml(data);
    const sanitizedTitle = (data.header.project_title || 'Project_Report')
      .replace(/[^a-zA-Z0-9_\- ]/g, '')
      .replace(/\s+/g, '_');
    const fileName = `${sanitizedTitle}_Report`;

    const { uri } = await Print.printToFileAsync({
      html,
      base64: false,
    });

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
