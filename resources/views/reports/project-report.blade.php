<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 40px 36px; size: A4; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Helvetica, Arial, sans-serif; color: #1E3A5F; font-size: 11px; line-height: 1.5; background: #fff; }

    /* Header banner */
    .header-banner { background: #1E3A5F; color: #fff; padding: 28px 30px 22px; }
    .header-banner h1 { font-size: 20px; font-weight: 700; margin-bottom: 3px; }
    .header-banner .subtitle { font-size: 11px; color: #cbd5e1; }

    .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-inline { background: #2D5A8E; color: #fff; padding: 2px 10px; border-radius: 3px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-left: 8px; }
    .status-terminated { background: #7f1d1d !important; color: #fff !important; }
    .status-completed { background: #065f46 !important; color: #fff !important; }

    .badge-success { background: #D1FAE5; color: #10B981; }
    .badge-warning { background: #FEF3C7; color: #F59E0B; }
    .badge-info { background: #DBEAFE; color: #3B82F6; }
    .badge-error { background: #FEE2E2; color: #EF4444; }
    .badge-muted { background: #F1F5F9; color: #94A3B8; }

    /* Meta strip — use table for DomPDF */
    .meta-table { margin-top: 14px; }
    .meta-table td { padding-right: 24px; vertical-align: top; }
    .meta-label { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; }
    .meta-value { font-size: 12px; font-weight: 600; color: #fff; }

    .container { padding: 0 30px 30px; }

    /* Parties row — table */
    .parties-table { width: 100%; margin: 18px 0 14px; border: 1px solid #E2E8F0; border-radius: 4px; background: #F8FAFC; }
    .parties-table td { padding: 14px 16px; width: 50%; vertical-align: top; }
    .party-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94A3B8; margin-bottom: 3px; }
    .party-name { font-size: 13px; font-weight: 600; color: #1E3A5F; }
    .party-contact { font-size: 10px; color: #64748B; margin-top: 1px; }

    /* Phase divider */
    .phase-divider { margin: 28px 0 6px; padding: 12px 16px 10px; background: #1E3A5F; border-radius: 4px; color: #fff; }
    .phase-divider .phase-num { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; }
    .phase-divider .phase-title { font-size: 15px; font-weight: 700; margin-top: 1px; }
    .phase-divider .phase-desc { font-size: 10px; color: #94a3b8; margin-top: 2px; }

    /* Sections */
    .section { margin-top: 20px; }
    .section-title { font-size: 13px; font-weight: 700; color: #1E3A5F; border-bottom: 2px solid #1E3A5F; padding-bottom: 5px; margin-bottom: 10px; }

    /* Financial grid — table based */
    .fin-grid { width: 100%; border-collapse: collapse; border: 1px solid #E2E8F0; border-radius: 4px; margin-bottom: 10px; }
    .fin-grid td { padding: 10px 12px; border: 1px solid #E2E8F0; background: #fff; vertical-align: top; }
    .fin-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #94A3B8; }
    .fin-value { font-size: 15px; font-weight: 700; color: #1E3A5F; margin-top: 2px; }
    .fin-value-sm { font-size: 12px; font-weight: 700; color: #1E3A5F; margin-top: 2px; }
    .text-success { color: #10B981; }
    .text-warning { color: #F59E0B; }
    .text-error { color: #EF4444; }

    /* Progress bars */
    .progress-row { margin: 8px 0; }
    .progress-row table { width: 100%; }
    .progress-label { font-size: 11px; font-weight: 600; color: #1E3A5F; width: 130px; }
    .progress-track { height: 8px; background: #F1F5F9; border-radius: 4px; overflow: hidden; }
    .progress-fill { height: 8px; border-radius: 4px; }
    .bg-success { background: #10B981; }
    .bg-info { background: #3B82F6; }
    .bg-error { background: #EF4444; }
    .progress-pct { font-size: 11px; font-weight: 700; width: 40px; text-align: right; }

    /* Tables */
    .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
    .data-table th { background: #F8FAFC; text-align: left; padding: 7px 8px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748B; border-bottom: 2px solid #E2E8F0; }
    .data-table td { padding: 8px 8px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
    .data-table tr:last-child td { border-bottom: none; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .fw-bold { font-weight: 700; }
    .fw-600 { font-weight: 600; }
    .ms-seq { display: inline-block; width: 22px; height: 22px; border-radius: 11px; background: #E8EEF4; text-align: center; line-height: 22px; font-size: 10px; font-weight: 700; color: #1E3A5F; }
    .ms-title { font-weight: 600; color: #1E3A5F; }
    .ms-group { font-size: 9px; color: #94A3B8; }

    /* Pay totals */
    .pay-totals { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .pay-totals td { border: 1px solid #E2E8F0; border-radius: 4px; padding: 8px; text-align: center; width: 33%; }
    .pay-total-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; }
    .pay-total-value { font-size: 13px; font-weight: 700; margin-top: 2px; }

    /* Timeline */
    .timeline-row { padding: 6px 0; border-bottom: 1px solid #F1F5F9; }
    .timeline-action { font-weight: 600; }
    .timeline-detail { font-size: 10px; color: #64748B; margin-top: 1px; }
    .timeline-date { font-size: 9px; color: #94A3B8; margin-top: 2px; }
    .timeline-dot { display: inline-block; width: 8px; height: 8px; border-radius: 4px; margin-right: 8px; vertical-align: middle; }

    .cf-badge { display: inline-block; background: #FFF3E0; color: #e74c3c; font-size: 8px; font-weight: 700; padding: 1px 4px; border-radius: 3px; margin-left: 4px; }
    .original-cost { font-size: 9px; color: #94A3B8; text-decoration: line-through; }

    /* Footer */
    .report-footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #E2E8F0; text-align: center; font-size: 9px; color: #94A3B8; }
    .report-footer .brand { font-weight: 700; color: #1E3A5F; }
</style>
</head>
<body>

@php
    $header           = $data['header'] ?? [];
    $overview         = $data['overview'] ?? [];
    $projectPost      = $data['project_post'] ?? null;
    $biddingHistory   = $data['bidding_history'] ?? [];
    $milestoneSetups  = $data['milestone_setups'] ?? [];
    $budgetHistory    = $data['budget_history'] ?? [];
    $milestones       = $data['milestones'] ?? [];
    $changeHistory    = $data['change_history'] ?? [];
    $payments         = $data['payments'] ?? ['records' => [], 'total_approved' => 0, 'total_pending' => 0, 'total_rejected' => 0];
    $progressReports  = $data['progress_reports'] ?? [];
    $fileSummary      = $data['file_summary'] ?? null;
    $generatedAt      = $data['generated_at'] ?? now()->toIso8601String();

    $progressPercent = ($overview['total_milestones'] ?? 0) > 0
        ? round(($overview['completed_milestones'] ?? 0) / $overview['total_milestones'] * 100)
        : 0;
    $budgetUtil = ($overview['current_budget'] ?? 0) > 0
        ? round(($overview['total_paid'] ?? 0) / $overview['current_budget'] * 100)
        : 0;

    if (!function_exists('fmtPdf')) {
        function fmtPdf($v) { return '₱' . number_format((float)$v, 2); }
    }
    if (!function_exists('fmtDatePdf')) {
        function fmtDatePdf($d) { return $d ? date('M j, Y', strtotime($d)) : '—'; }
    }
    if (!function_exists('fmtDateTimePdf')) {
        function fmtDateTimePdf($d) { return $d ? date('M j, Y g:i A', strtotime($d)) : '—'; }
    }
    if (!function_exists('badgeClassPdf')) {
        function badgeClassPdf($status) {
            $s = strtolower($status ?? '');
            if (in_array($s, ['completed', 'approved', 'accepted', 'paid', 'active'])) return 'badge-success';
            if (in_array($s, ['pending', 'in_progress', 'in progress', 'ongoing', 'awaiting_payment', 'awaiting payment'])) return 'badge-warning';
            if (in_array($s, ['not_started', 'not started', 'draft'])) return 'badge-info';
            if (in_array($s, ['rejected', 'terminated', 'cancelled', 'overdue', 'failed'])) return 'badge-error';
            return 'badge-muted';
        }
    }
@endphp

{{-- ═════════ BANNER ═════════ --}}
<div class="header-banner">
    <h1>
        {{ $header['project_title'] ?? 'Project Report' }}
        @if(!empty($header['status']))
            <span class="badge-inline {{ ($header['status'] ?? '') === 'terminated' ? 'status-terminated' : (($header['status'] ?? '') === 'completed' ? 'status-completed' : '') }}">
                {{ strtoupper(str_replace('_', ' ', $header['status'])) }}
            </span>
        @endif
    </h1>
    @if(!empty($header['project_description']))
        <div class="subtitle">{{ $header['project_description'] }}</div>
    @endif

    <table class="meta-table">
        <tr>
            <td>
                <div class="meta-label">Location</div>
                <div class="meta-value">{{ $header['project_location'] ?? '—' }}</div>
            </td>
            <td>
                <div class="meta-label">Start Date</div>
                <div class="meta-value">{{ fmtDatePdf($header['original_start_date'] ?? null) }}</div>
            </td>
            <td>
                <div class="meta-label">{{ !empty($header['was_extended']) ? 'Current End Date' : 'End Date' }}</div>
                <div class="meta-value">{{ fmtDatePdf($header['current_end_date'] ?? null) }}</div>
            </td>
            @if(!empty($header['was_extended']) && ($header['original_end_date'] ?? '') !== ($header['current_end_date'] ?? ''))
                <td>
                    <div class="meta-label">Original End</div>
                    <div class="meta-value" style="text-decoration: line-through; opacity: 0.6;">{{ fmtDatePdf($header['original_end_date'] ?? null) }}</div>
                </td>
            @endif
        </tr>
    </table>
</div>

<div class="container">

{{-- Parties --}}
<table class="parties-table">
    <tr>
        <td>
            <div class="party-label">Property Owner</div>
            <div class="party-name">{{ $header['owner_name'] ?? '—' }}</div>
            @if(!empty($header['owner_email']))
                <div class="party-contact">{{ $header['owner_email'] }}</div>
            @endif
        </td>
        <td>
            <div class="party-label">Contractor</div>
            <div class="party-name">{{ $header['contractor_name'] ?? '—' }}</div>
            @if(!empty($header['contractor_company']))
                <div class="party-contact">{{ $header['contractor_company'] }}</div>
            @endif
        </td>
    </tr>
</table>

{{-- ═══════════════════════════ PHASE 1: PROJECT LIFECYCLE ═══════════════════════════ --}}
<div class="phase-divider">
    <div class="phase-num">Phase 1</div>
    <div class="phase-title">Project Lifecycle</div>
    <div class="phase-desc">How this project was created — from posting to contractor selection to milestone approval</div>
</div>

{{-- ═════════ PROJECT POSTING ═════════ --}}
@if($projectPost)
<div class="section">
    <div class="section-title">Project Posting Details</div>
    <table class="fin-grid">
        <tr>
            <td>
                <div class="fin-label">Property Type</div>
                <div class="fin-value-sm" style="text-transform:capitalize;">{{ $projectPost['property_type'] ?? '—' }}</div>
            </td>
            <td>
                <div class="fin-label">Budget Range</div>
                <div class="fin-value-sm">
                    @if(($projectPost['budget_range_min'] ?? 0) > 0)
                        {{ fmtPdf($projectPost['budget_range_min']) }} – {{ fmtPdf($projectPost['budget_range_max'] ?? 0) }}
                    @else
                        —
                    @endif
                </div>
            </td>
            @if(!empty($projectPost['lot_size']))
                <td>
                    <div class="fin-label">Lot Size</div>
                    <div class="fin-value-sm">{{ $projectPost['lot_size'] }}</div>
                </td>
            @endif
        </tr>
        <tr>
            @if(!empty($projectPost['floor_area']))
                <td>
                    <div class="fin-label">Floor Area</div>
                    <div class="fin-value-sm">{{ $projectPost['floor_area'] }}</div>
                </td>
            @endif
            @if(!empty($projectPost['to_finish']))
                <td>
                    <div class="fin-label">Target Duration</div>
                    <div class="fin-value-sm">{{ $projectPost['to_finish'] }} month{{ $projectPost['to_finish'] > 1 ? 's' : '' }}</div>
                </td>
            @endif
            <td>
                <div class="fin-label">Posted On</div>
                <div class="fin-value-sm">{{ fmtDatePdf($projectPost['posted_at'] ?? null) }}</div>
            </td>
        </tr>
    </table>
    @if(!empty($projectPost['description']))
        <div style="margin-top:8px; font-size:11px; color:#64748B; line-height:1.6;">
            <strong>Description:</strong> {{ $projectPost['description'] }}
        </div>
    @endif
</div>
@endif

{{-- ═════════ BIDDING HISTORY ═════════ --}}
@if(count($biddingHistory) > 0)
<div class="section">
    <div class="section-title">Bidding History ({{ count($biddingHistory) }} bid{{ count($biddingHistory) > 1 ? 's' : '' }})</div>
    <div style="font-size:10px; color:#64748B; margin-bottom:10px;">All contractor bids submitted for this project. The accepted bid is highlighted.</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Contractor</th>
                <th class="text-right">Proposed Cost</th>
                <th>Timeline</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @foreach($biddingHistory as $b)
                <tr style="{{ ($b['bid_status'] ?? '') === 'accepted' ? 'background:#D1FAE5;' : '' }}">
                    <td>
                        <div style="font-weight:600;">{{ $b['company_name'] ?? '—' }}</div>
                        @if(!empty($b['years_of_experience']))
                            <div style="font-size:9px; color:#94A3B8;">{{ $b['years_of_experience'] }} yrs exp. · {{ $b['completed_projects'] ?? 0 }} projects</div>
                        @endif
                    </td>
                    <td class="text-right fw-bold">{{ fmtPdf($b['proposed_cost'] ?? 0) }}</td>
                    <td>{{ !empty($b['estimated_timeline']) ? $b['estimated_timeline'] . ' mo.' : '—' }}</td>
                    <td><span class="badge {{ badgeClassPdf($b['bid_status'] ?? '') }}">{{ strtoupper(str_replace('_', ' ', $b['bid_status'] ?? '')) }}</span></td>
                    <td>{{ fmtDatePdf($b['submitted_at'] ?? null) }}</td>
                </tr>
                @if(!empty($b['contractor_notes']))
                    <tr style="{{ ($b['bid_status'] ?? '') === 'accepted' ? 'background:#D1FAE5;' : '' }}">
                        <td colspan="5" style="font-size:10px; color:#64748B; font-style:italic; padding-top:0;">"{{ $b['contractor_notes'] }}"</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ═════════ MILESTONE SETUP HISTORY ═════════ --}}
@if(count($milestoneSetups) > 0)
<div class="section">
    <div class="section-title">Milestone Setup History ({{ count($milestoneSetups) }} submission{{ count($milestoneSetups) > 1 ? 's' : '' }})</div>
    <div style="font-size:10px; color:#64748B; margin-bottom:10px;">All milestone setup proposals submitted by the contractor.</div>
    @foreach($milestoneSetups as $idx => $ms)
        <div style="margin-bottom:14px; {{ !empty($ms['is_deleted']) ? 'opacity:0.5;' : '' }}">
            <div style="margin-bottom:6px;">
                <span style="font-weight:700; font-size:12px; color:#1E3A5F;">{{ $ms['name'] ?? 'Setup #' . ($idx + 1) }}</span>
                <span class="badge {{ badgeClassPdf($ms['setup_status'] ?? '') }}" style="margin-left:8px;">{{ strtoupper(str_replace('_', ' ', $ms['setup_status'] ?? '')) }}</span>
                @if(!empty($ms['is_deleted']))
                    <span class="badge badge-muted" style="margin-left:4px;">Superseded</span>
                @endif
                <span style="font-size:10px; color:#94A3B8; margin-left:12px;">
                    {{ fmtDatePdf($ms['created_at'] ?? null) }}
                    @if(!empty($ms['start_date']))
                        · {{ fmtDatePdf($ms['start_date']) }} – {{ fmtDatePdf($ms['end_date'] ?? null) }}
                    @endif
                </span>
            </div>
            @if(!empty($ms['description']))
                <div style="font-size:10px; color:#64748B; margin-bottom:6px;">{{ $ms['description'] }}</div>
            @endif
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:30px">#</th>
                        <th>Item</th>
                        <th class="text-right">Cost</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($ms['items'] ?? []) as $i)
                        <tr>
                            <td>{{ $i['sequence'] ?? '' }}</td>
                            <td style="font-weight:500;">{{ $i['title'] ?? '' }}</td>
                            <td class="text-right fw-600">{{ fmtPdf($i['cost'] ?? 0) }}</td>
                            <td>{{ fmtDatePdf($i['due_date'] ?? null) }}</td>
                            <td><span class="badge {{ badgeClassPdf($i['status'] ?? '') }}">{{ strtoupper(str_replace('_', ' ', $i['status'] ?? '')) }}</span></td>
                        </tr>
                    @endforeach
                    <tr style="background:#F8FAFC; font-weight:700;">
                        <td colspan="2" class="text-right">Total ({{ $ms['item_count'] ?? 0 }} items)</td>
                        <td class="text-right">{{ fmtPdf($ms['total_cost'] ?? 0) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════ PHASE 2: PROJECT SUMMARY ═══════════════════════════ --}}
<div class="phase-divider" style="background: #065F46;">
    <div class="phase-num">Phase 2</div>
    <div class="phase-title">Project Summary</div>
    <div class="phase-desc">Financial overview, milestone progress, payments, and complete project records</div>
</div>

{{-- ═════════ EXECUTIVE OVERVIEW ═════════ --}}
<div class="section">
    <div class="section-title">Executive Overview</div>

    {{-- Progress bars --}}
    <div class="progress-row">
        <table>
            <tr>
                <td class="progress-label">Milestone Progress</td>
                <td style="width:100%;">
                    <div class="progress-track">
                        <div class="progress-fill bg-success" style="width:{{ $progressPercent }}%;"></div>
                    </div>
                </td>
                <td class="progress-pct">{{ $progressPercent }}%</td>
            </tr>
        </table>
    </div>
    <div style="font-size:10px; color:#64748B; margin-bottom:8px; margin-left:140px;">
        {{ $overview['completed_milestones'] ?? 0 }} of {{ $overview['total_milestones'] ?? 0 }} milestones completed
    </div>

    <div class="progress-row">
        <table>
            <tr>
                <td class="progress-label">Budget Utilization</td>
                <td style="width:100%;">
                    <div class="progress-track">
                        <div class="progress-fill {{ $budgetUtil > 100 ? 'bg-error' : 'bg-info' }}" style="width:{{ min($budgetUtil, 100) }}%;"></div>
                    </div>
                </td>
                <td class="progress-pct">{{ $budgetUtil }}%</td>
            </tr>
        </table>
    </div>

    {{-- Financial grid --}}
    <table class="fin-grid" style="margin-top: 12px;">
        <tr>
            <td>
                <div class="fin-label">Original Budget</div>
                <div class="fin-value">{{ fmtPdf($overview['original_budget'] ?? 0) }}</div>
            </td>
            <td>
                <div class="fin-label">Current Budget</div>
                <div class="fin-value {{ ($overview['current_budget'] ?? 0) != ($overview['original_budget'] ?? 0) ? 'text-warning' : '' }}">{{ fmtPdf($overview['current_budget'] ?? 0) }}</div>
            </td>
            <td>
                <div class="fin-label">Total Paid</div>
                <div class="fin-value text-success">{{ fmtPdf($overview['total_paid'] ?? 0) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="fin-label">Pending</div>
                <div class="fin-value text-warning">{{ fmtPdf($overview['total_pending'] ?? 0) }}</div>
            </td>
            <td>
                <div class="fin-label">Remaining</div>
                <div class="fin-value">{{ fmtPdf($overview['remaining_balance'] ?? 0) }}</div>
            </td>
            <td>
                <div class="fin-label">Payment Mode</div>
                <div class="fin-value-sm" style="text-transform:capitalize;">{{ str_replace('_', ' ', $overview['payment_mode'] ?? '—') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ═════════ MILESTONE BREAKDOWN ═════════ --}}
<div class="section">
    <div class="section-title">Milestone Breakdown ({{ count($milestones) }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Milestone Item</th>
                <th>Status</th>
                <th class="text-right">Budget</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Remaining</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($milestones as $m)
                @php $cf = $m['carry_forward_amount'] ?? 0; @endphp
                <tr>
                    <td><span class="ms-seq">{{ $m['sequence_order'] ?? '' }}</span></td>
                    <td>
                        <div class="ms-title">{{ $m['title'] ?? '' }}</div>
                        <div class="ms-group">{{ $m['milestone_name'] ?? '' }}</div>
                    </td>
                    <td><span class="badge {{ badgeClassPdf($m['status'] ?? '') }}">{{ strtoupper(str_replace('_', ' ', $m['status'] ?? '')) }}</span></td>
                    <td class="text-right">
                        @if($cf != 0)
                            <div class="fw-bold" style="color:#e74c3c;">{{ fmtPdf($m['current_allocation'] ?? 0) }}</div>
                            <div class="original-cost">{{ fmtPdf($m['original_allocation'] ?? 0) }}</div>
                            <span class="cf-badge">{{ $cf < 0 ? '−CF' : '+CF' }}</span>
                        @else
                            <div class="fw-bold">{{ fmtPdf($m['current_allocation'] ?? 0) }}</div>
                        @endif
                    </td>
                    <td class="text-right text-success fw-600">{{ fmtPdf($m['total_paid'] ?? 0) }}</td>
                    <td class="text-right fw-600">{{ fmtPdf($m['remaining'] ?? 0) }}</td>
                    <td>
                        {{ fmtDatePdf($m['current_due_date'] ?? null) }}
                        @if(!empty($m['was_extended']))
                            <br><span style="font-size:9px; color:#F59E0B;">Extended {{ $m['extension_count'] ?? 0 }}×</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ═════════ BUDGET HISTORY ═════════ --}}
@if(count($budgetHistory) > 0)
<div class="section">
    <div class="section-title">Budget History ({{ count($budgetHistory) }})</div>
    @foreach($budgetHistory as $bh)
        <div class="timeline-row">
            <span class="timeline-dot" style="background:#EC7E00;"></span>
            <span class="timeline-action">
                {{ $bh['change_type'] ? 'Budget ' . $bh['change_type'] : 'Timeline Update' }}
            </span>
            <span class="badge {{ badgeClassPdf($bh['status'] ?? '') }}" style="margin-left:6px;">{{ strtoupper($bh['status'] ?? '') }}</span>
            @if(isset($bh['previous_budget']) && isset($bh['updated_budget']))
                <div class="timeline-detail">{{ fmtPdf($bh['previous_budget']) }} → {{ fmtPdf($bh['updated_budget']) }}</div>
            @endif
            @if(!empty($bh['previous_end_date']) && !empty($bh['proposed_end_date']))
                <div class="timeline-detail">{{ fmtDatePdf($bh['previous_end_date']) }} → {{ fmtDatePdf($bh['proposed_end_date']) }}</div>
            @endif
            @if(!empty($bh['reason']))
                <div class="timeline-detail" style="font-style:italic;">"{{ $bh['reason'] }}"</div>
            @endif
            <div class="timeline-date">{{ fmtDatePdf($bh['date_proposed'] ?? null) }}</div>
        </div>
    @endforeach
</div>
@endif

{{-- ═════════ CHANGE LOG ═════════ --}}
@if(count($changeHistory) > 0)
<div class="section">
    <div class="section-title">Change Log ({{ count($changeHistory) }})</div>
    @foreach($changeHistory as $evt)
        <div class="timeline-row">
            <span class="timeline-dot" style="background:#3B82F6;"></span>
            <span class="timeline-action">{{ $evt['action'] ?? '' }}</span>
            @if(!empty($evt['performed_by']))
                <div class="timeline-detail">by {{ $evt['performed_by'] }}</div>
            @endif
            @if(!empty($evt['notes']))
                <div class="timeline-detail" style="font-style:italic;">"{{ $evt['notes'] }}"</div>
            @endif
            <div class="timeline-date">{{ fmtDateTimePdf($evt['date'] ?? null) }}</div>
        </div>
    @endforeach
</div>
@endif

{{-- ═════════ PAYMENTS ═════════ --}}
<div class="section">
    <div class="section-title">Payments ({{ count($payments['records'] ?? []) }})</div>
    <table class="pay-totals">
        <tr>
            <td>
                <div class="pay-total-label text-success">Approved</div>
                <div class="pay-total-value text-success">{{ fmtPdf($payments['total_approved'] ?? 0) }}</div>
            </td>
            <td>
                <div class="pay-total-label text-warning">Pending</div>
                <div class="pay-total-value text-warning">{{ fmtPdf($payments['total_pending'] ?? 0) }}</div>
            </td>
            <td>
                <div class="pay-total-label text-error">Rejected</div>
                <div class="pay-total-value text-error">{{ fmtPdf($payments['total_rejected'] ?? 0) }}</div>
            </td>
        </tr>
    </table>

    @if(count($payments['records'] ?? []) === 0)
        <div style="color:#94A3B8; font-style:italic; padding:8px 0;">No payment records.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Milestone</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th class="text-right">Amount</th>
                    <th>Reference</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments['records'] as $p)
                    <tr>
                        <td>{{ $p['milestone'] ?? '' }}</td>
                        <td style="text-transform:capitalize;">{{ str_replace('_', ' ', $p['payment_type'] ?? '') }}</td>
                        <td><span class="badge {{ badgeClassPdf($p['status'] ?? '') }}">{{ strtoupper($p['status'] ?? '') }}</span></td>
                        <td class="text-right fw-bold">{{ fmtPdf($p['amount'] ?? 0) }}</td>
                        <td>{{ $p['transaction_number'] ?? '—' }}</td>
                        <td>{{ fmtDatePdf($p['transaction_date'] ?? null) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ═════════ PROGRESS REPORTS ═════════ --}}
@if(count($progressReports) > 0)
<div class="section">
    <div class="section-title">Progress Reports ({{ count($progressReports) }})</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Report</th>
                <th>Milestone</th>
                <th>Status</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @foreach($progressReports as $pr)
                <tr>
                    <td class="fw-600">{{ $pr['report_title'] ?? 'Progress Report' }}</td>
                    <td>{{ $pr['milestone'] ?? '' }}</td>
                    <td><span class="badge {{ badgeClassPdf($pr['status'] ?? '') }}">{{ strtoupper(str_replace('_', ' ', $pr['status'] ?? '')) }}</span></td>
                    <td>{{ fmtDatePdf($pr['submitted_at'] ?? null) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ═════════ FILE SUMMARY ═════════ --}}
@if($fileSummary && ($fileSummary['grand_total'] ?? 0) > 0)
<div class="section">
    <div class="section-title">Files &amp; Documents Summary</div>
    <table class="fin-grid">
        <tr>
            @if(($fileSummary['project_files']['total'] ?? 0) > 0)
                <td>
                    <div class="fin-label">Project Files</div>
                    <div class="fin-value" style="font-size:14px;">{{ $fileSummary['project_files']['total'] }}</div>
                    @if(!empty($fileSummary['project_files']['by_type']))
                        <div style="font-size:9px; color:#94A3B8; margin-top:2px;">
                            @foreach($fileSummary['project_files']['by_type'] as $type => $count)
                                {{ str_replace('_', ' ', $type ?: 'other') }}: {{ $count }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </div>
                    @endif
                </td>
            @endif
            @if(($fileSummary['progress_files'] ?? 0) > 0)
                <td>
                    <div class="fin-label">Progress Report Files</div>
                    <div class="fin-value" style="font-size:14px;">{{ $fileSummary['progress_files'] }}</div>
                </td>
            @endif
            @if(($fileSummary['payment_receipts'] ?? 0) > 0)
                <td>
                    <div class="fin-label">Payment Receipts</div>
                    <div class="fin-value" style="font-size:14px;">{{ $fileSummary['payment_receipts'] }}</div>
                </td>
            @endif
        </tr>
        <tr>
            @if(($fileSummary['bid_files'] ?? 0) > 0)
                <td>
                    <div class="fin-label">Bid Documents</div>
                    <div class="fin-value" style="font-size:14px;">{{ $fileSummary['bid_files'] }}</div>
                </td>
            @endif
            @if(($fileSummary['item_files'] ?? 0) > 0)
                <td>
                    <div class="fin-label">Milestone Item Files</div>
                    <div class="fin-value" style="font-size:14px;">{{ $fileSummary['item_files'] }}</div>
                </td>
            @endif
            <td style="background:#F8FAFC;">
                <div class="fin-label">Total Documents</div>
                <div class="fin-value" style="font-size:16px; color:#1E3A5F;">{{ $fileSummary['grand_total'] }}</div>
            </td>
        </tr>
    </table>
</div>
@endif

{{-- Footer --}}
<div class="report-footer">
    <span class="brand">Legatura</span> &mdash; Project Report &bull; Generated {{ fmtDateTimePdf($generatedAt) }}
</div>

</div>
</body>
</html>
