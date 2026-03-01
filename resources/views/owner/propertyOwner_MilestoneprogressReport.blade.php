@extends('layouts.app')

@section('title', 'Milestone Progress Report - Legatura')

@php
    $item = $milestoneItem ?? null;
    $ps = $paymentSummary ?? [];
    $progress = $item ? round(floatval($item->percentage_progress ?? 0)) : 0;
    $effectiveRequired = floatval($ps['effective_required'] ?? ($item->adjusted_cost ?? $item->milestone_item_cost ?? 0));
    $originalCost = floatval($ps['original_cost'] ?? ($item->milestone_item_cost ?? 0));
    $adjustedCost = $ps['adjusted_cost'] ?? ($item->adjusted_cost ?? null);
    $carryForward = floatval($ps['carry_forward_amount'] ?? ($item->carry_forward_amount ?? 0));
    $totalPaid = floatval($ps['total_paid'] ?? 0);
    $totalSubmitted = floatval($ps['total_submitted'] ?? 0);
    $remainingBalance = floatval($ps['remaining_balance'] ?? 0);
    $overAmount = floatval($ps['over_amount'] ?? 0);
    $derivedStatus = $ps['derived_status'] ?? 'Unpaid';
    $settlementDueDate = $ps['settlement_due_date'] ?? ($item->settlement_due_date ?? null);
    $extensionDate = $ps['extension_date'] ?? ($item->extension_date ?? null);
    $wasExtended = $item->was_extended ?? false;
    $extensionCount = $item->extension_count ?? 0;
    $originalDateToFinish = $item->original_date_to_finish ?? null;
    $itemStatus = $item->item_status ?? '';
    $projectStatus = $item->project_status ?? '';
    $isProjectHalted = in_array($projectStatus, ['halt','on_hold','halted']);
    $paymentPct = $effectiveRequired > 0 ? min(100, round(($totalPaid / $effectiveRequired) * 100)) : 0;
    $seqNum = $item->sequence_order ?? 1;

    // Status helpers
    $statusLabel = match($itemStatus) {
        'completed' => 'Completed', 'in_progress' => 'In Progress', 'halt' => 'Halted',
        'not_started' => 'Not Started', 'delayed' => 'Delayed', default => ucfirst($itemStatus ?: 'Pending')
    };
    $statusColor = match($itemStatus) {
        'completed' => '#10B981', 'in_progress' => '#3b82f6', 'halt' => '#ef4444',
        'delayed' => '#f59e0b', default => '#94a3b8'
    };
    $paymentStatusColor = match($derivedStatus) {
        'Fully Paid' => '#22c55e', 'Partially Paid' => '#f59e0b', 'Overdue' => '#ef4444', default => '#94a3b8'
    };

    // Due date urgency
    $dueDateUrgency = null;
    $effectiveDueStr = $extensionDate ?? $settlementDueDate;
    if ($effectiveDueStr) {
        $today = \Carbon\Carbon::today();
        $dueDate = \Carbon\Carbon::parse($effectiveDueStr)->startOfDay();
        $diffDays = $today->diffInDays($dueDate, false);
        if ($diffDays < 0) $dueDateUrgency = ['label' => abs($diffDays).' day'.(abs($diffDays)!=1?'s':'').' overdue', 'color' => '#dc2626', 'urgent' => true];
        elseif ($diffDays == 0) $dueDateUrgency = ['label' => 'Due today', 'color' => '#dc2626', 'urgent' => true];
        elseif ($diffDays <= 3) $dueDateUrgency = ['label' => $diffDays.' day'.($diffDays!=1?'s':'').' left', 'color' => '#ea580c', 'urgent' => true];
        elseif ($diffDays <= 7) $dueDateUrgency = ['label' => $diffDays.' days left', 'color' => '#d97706', 'urgent' => false];
        else $dueDateUrgency = ['label' => $diffDays.' days left', 'color' => '#16a34a', 'urgent' => false];
    }

    $pendingReports = collect($progressReports)->where('progress_status', 'submitted')->count();
    $pendingPayments = collect($payments)->where('payment_status', 'submitted')->count();

    // Action button visibility (mirroring mobile milestoneDetail logic)
    $hasApprovedReport = collect($progressReports)->where('progress_status', 'approved')->count() > 0;
    $hasApprovedPayment = collect($payments)->where('payment_status', 'approved')->count() > 0;
    $isItemCompleted = $itemStatus === 'completed';
    // Check if previous sequential item is completed (for sequential enforcement)
    $prevItemComplete = true;
    if ($seqNum > 1 && !empty($allItems)) {
        foreach ($allItems as $ai) {
            $aiObj = is_object($ai) ? $ai : (object)$ai;
            if (($aiObj->sequence_order ?? 0) == $seqNum - 1) {
                $prevItemComplete = ($aiObj->item_status ?? '') === 'completed';
                break;
            }
        }
    }
    $showPaymentBtn = !$isProjectHalted && $hasApprovedReport && !$isItemCompleted && $prevItemComplete;
    $showCompleteBtn = !$isProjectHalted && $hasApprovedReport && $hasApprovedPayment && !$isItemCompleted && $prevItemComplete;
    $hasRejectedOrSubmittedReports = collect($progressReports)->whereIn('progress_status', ['submitted','rejected'])->count() > 0;
@endphp

@section('content')
    <div class="min-h-screen bg-gray-50">
        {{-- ═══ Header ═══ --}}
        <div class="milestone-report-header bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="mb-6">
                    <div class="flex items-center gap-4 mb-3">
                        <a href="{{ route('owner.projects.milestone-report') }}" class="back-button">
                            <i class="fi fi-rr-arrow-left"></i>
                            <span>Back</span>
                        </a>
                        <div class="breadcrumb">
                            <a href="{{ route('owner.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <a href="{{ route('owner.projects.milestone-report') }}" class="breadcrumb-link">Milestone Report</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <span class="breadcrumb-current">Milestone Progress</span>
                        </div>
                    </div>
                </div>
                <div class="section-header">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Milestone Progress</h1>
                        <p class="text-gray-600 mt-1">Track progress reports and payment details for this milestone</p>
                    </div>
                    <div class="milestone-report-menu-wrapper" style="position: relative;">
                        <button id="reportMenuBtn" class="milestone-report-menu-btn" title="More options">
                            <i class="fi fi-rr-menu-dots-vertical"></i>
                        </button>
                        <div id="reportDropdown" class="milestone-report-menu-dropdown" style="display:none;">
                            <button class="milestone-menu-item" data-action="summary"><i class="fi fi-rr-chart-pie-alt"></i><span>Project Summary</span></button>
                            <button class="milestone-menu-item" data-action="send-report"><i class="fi fi-rr-paper-plane"></i><span>Send Report</span></button>
                            <button class="milestone-menu-item" data-action="report-history"><i class="fi fi-rr-time-past"></i><span>Report History</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($item)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- ═══ Title Card ═══ --}}
            <div class="mdp-title-card">
                <div class="flex items-start justify-between gap-3 mb-5">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-400 tracking-wider uppercase mb-1">MILESTONE ITEM {{ $seqNum }}</p>
                        <h2 class="text-xl font-bold text-gray-900 leading-tight">{{ $item->milestone_item_title }}</h2>
                    </div>
                    <span class="mdp-status-badge" style="background:{{ $statusColor }}15; color:{{ $statusColor }};">
                        <span class="inline-block w-1.5 h-1.5 rounded-full mr-1" style="background:{{ $statusColor }};"></span>
                        {{ $statusLabel }}
                    </span>
                </div>

                {{-- Financial Grid --}}
                <div class="mdp-stats-grid">
                    <div class="mdp-stat-card">
                        <div class="mdp-stat-icon" style="background:rgba(99,102,241,.1); color:#6366f1;">
                            <i class="fi fi-rr-receipt"></i>
                        </div>
                        <div class="mdp-stat-content">
                            <span class="mdp-stat-label">REQUIRED</span>
                            @if($adjustedCost !== null && $carryForward > 0)
                                <span class="mdp-stat-value" style="color:#dc2626;">₱{{ number_format($adjustedCost, 0) }}</span>
                                <span class="mdp-stat-note line-through">₱{{ number_format($originalCost, 0) }}</span>
                            @else
                                <span class="mdp-stat-value">₱{{ number_format($effectiveRequired, 0) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="mdp-stat-card">
                        <div class="mdp-stat-icon" style="background:rgba(34,197,94,.1); color:#22c55e;">
                            <i class="fi fi-rr-check-circle"></i>
                        </div>
                        <div class="mdp-stat-content">
                            <span class="mdp-stat-label">PAID</span>
                            <span class="mdp-stat-value" style="color:#22c55e;">₱{{ number_format($totalPaid, 0) }}</span>
                        </div>
                    </div>
                    <div class="mdp-stat-card">
                        <div class="mdp-stat-icon" style="background:{{ $overAmount > 0 ? 'rgba(239,68,68,.1)' : ($remainingBalance > 0 ? 'rgba(238,162,75,.1)' : 'rgba(16,185,129,.1)') }}; color:{{ $overAmount > 0 ? '#ef4444' : ($remainingBalance > 0 ? '#EEA24B' : '#10B981') }};">
                            <i class="fi fi-rr-wallet"></i>
                        </div>
                        <div class="mdp-stat-content">
                            <span class="mdp-stat-label">REMAINING</span>
                            @if($overAmount > 0)
                                <span class="mdp-stat-value" style="color:#dc2626;">+₱{{ number_format($overAmount, 0) }}</span>
                                <span class="mdp-stat-note" style="color:#dc2626; font-weight:700;">OVER BUDGET</span>
                            @else
                                <span class="mdp-stat-value" style="color:{{ $remainingBalance > 0 ? '#EEA24B' : '#10B981' }};">₱{{ number_format($remainingBalance, 0) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mdp-progress-track">
                    <div class="mdp-progress-fill" style="width:{{ $paymentPct }}%;"></div>
                </div>

                {{-- Footer: Payment Status + Due Date --}}
                <div class="flex items-center justify-between flex-wrap gap-2 mb-4">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-2 h-2 rounded-full" style="background:{{ $paymentStatusColor }};"></span>
                        <span class="text-xs font-bold" style="color:{{ $paymentStatusColor }};">{{ $derivedStatus }}</span>
                    </div>
                    @if($settlementDueDate)
                        <div class="flex items-center gap-1.5">
                            <i class="fi fi-rr-calendar text-xs text-gray-400"></i>
                            <span class="text-xs text-gray-500">Due {{ \Carbon\Carbon::parse($settlementDueDate)->format('M j') }}</span>
                            @if($dueDateUrgency)
                                <span class="mdp-urgency-pill" style="background:{{ $dueDateUrgency['color'] }}15; color:{{ $dueDateUrgency['color'] }};">{{ $dueDateUrgency['label'] }}</span>
                            @endif
                        </div>
                    @else
                        <span class="text-xs text-gray-400">No due date</span>
                    @endif
                </div>

                {{-- View full details link --}}
                <button class="mdp-details-btn" id="viewFullDetailsBtn">
                    <span>View full details & payment history</span>
                </button>
            </div>

            {{-- ═══ Alert Banners ═══ --}}
            @if($isProjectHalted)
                <div class="mdp-alert mdp-alert-error"><i class="fi fi-rr-pause-circle"></i><span>This milestone item is currently halted</span></div>
            @endif
            @php
                $sorted = collect($progressReports)->sortByDesc('submitted_at');
                $latest = $sorted->first();
            @endphp
            @if($latest && $latest->progress_status === 'rejected')
                <div class="mdp-alert mdp-alert-error"><i class="fi fi-rr-exclamation"></i><span>Latest progress report rejected — submit a new one</span></div>
            @endif
            @if($pendingReports > 0 || $pendingPayments > 0)
                @php
                    $parts = [];
                    if ($pendingReports > 0) $parts[] = $pendingReports.' report'.($pendingReports > 1 ? 's' : '');
                    if ($pendingPayments > 0) $parts[] = $pendingPayments.' payment'.($pendingPayments > 1 ? 's' : '');
                @endphp
                <div class="mdp-alert mdp-alert-info"><i class="fi fi-rr-info"></i><span>{{ implode(' and ', $parts) }} pending review</span></div>
            @endif

            {{-- ════════════ MAIN VIEW ════════════ --}}
            <div id="mdMainView" class="mdp-view-animate">

                {{-- Progress Reports Timeline --}}
                <div class="mdp-card mdp-reports-card">
                    <div class="mdp-reports-header">
                        <div class="flex items-center gap-3">
                            <div class="mdp-reports-icon">
                                <i class="fi fi-rr-time-past"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-900 leading-tight">Progress Reports</h4>
                                <p class="text-xs text-gray-400 mt-0.5">{{ count($progressReports) }} report{{ count($progressReports) !== 1 ? 's' : '' }} submitted</p>
                            </div>
                        </div>
                        @if(count($progressReports) > 0)
                            <span class="mdp-reports-count">{{ count($progressReports) }}</span>
                        @endif
                    </div>

                    @if(count($progressReports) === 0)
                        <div class="mdp-empty-state">
                            <div class="mdp-empty-icon">
                                <i class="fi fi-rr-file-check"></i>
                            </div>
                            <p class="text-sm font-semibold text-gray-400 mt-3">No progress reports yet</p>
                            <p class="text-xs text-gray-300 mt-1">Reports will appear here once submitted</p>
                        </div>
                    @else
                        <div class="mdp-timeline">
                            @foreach($progressReports as $idx => $report)
                                @php
                                    $isLast = $idx === count($progressReports) - 1;
                                    $rStatus = $report->progress_status;
                                    $dotClass = $rStatus === 'approved' ? 'dot-approved' : ($rStatus === 'rejected' ? 'dot-rejected' : 'dot-pending');
                                    $rStatusLabel = $rStatus === 'approved' ? 'Approved' : ($rStatus === 'rejected' ? 'Rejected' : 'Pending');
                                    $rStatusColor = $rStatus === 'approved' ? '#10B981' : ($rStatus === 'rejected' ? '#EF4444' : '#F59E0B');
                                    $rStatusBg = $rStatus === 'approved' ? '#D1FAE5' : ($rStatus === 'rejected' ? '#FEE2E2' : '#FEF3C7');
                                    $nextStatus = !$isLast ? $progressReports[$idx + 1]->progress_status : null;
                                    $lineClass = $nextStatus === 'approved' ? 'line-approved' : ($nextStatus === 'rejected' ? 'line-rejected' : 'line-pending');
                                @endphp
                                <div class="mdp-timeline-item" data-report-index="{{ $idx }}">
                                    <div class="mdp-timeline-rail">
                                        <div class="mdp-timeline-dot {{ $dotClass }}">
                                            @if($rStatus === 'approved')
                                                <i class="fi fi-rr-check"></i>
                                            @elseif($rStatus === 'rejected')
                                                <i class="fi fi-rr-cross-small"></i>
                                            @endif
                                        </div>
                                        @if(!$isLast)
                                            <div class="mdp-timeline-line {{ $lineClass }}"></div>
                                        @endif
                                    </div>
                                    <div class="mdp-timeline-body">
                                        <div class="mdp-timeline-body-top">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 leading-snug line-clamp-2">{{ $report->purpose ?? 'Progress Report' }}</p>
                                                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                                    <span class="mdp-timeline-date">
                                                        <i class="fi fi-rr-calendar text-[0.5625rem]"></i>
                                                        {{ $report->submitted_at ? \Carbon\Carbon::parse($report->submitted_at)->format('M j, Y') : 'No date' }}
                                                    </span>
                                                    <span class="mdp-timeline-status" style="background:{{ $rStatusBg }}; color:{{ $rStatusColor }};">
                                                        {{ $rStatusLabel }}
                                                    </span>
                                                </div>
                                            </div>
                                            <button class="mdp-timeline-btn" data-report-index="{{ $idx }}">
                                                <span>View Details</span>
                                                <i class="fi fi-rr-angle-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ═══ Action Buttons Bar ═══ --}}
                @if($showPaymentBtn || $showCompleteBtn)
                    <div class="mdp-actions-bar">
                        @if($showPaymentBtn)
                            <button class="mdp-action-btn mdp-action-payment" id="openPaymentModalBtn" type="button">
                                <i class="fi fi-rr-credit-card"></i>
                                <span>Send Payment Receipt</span>
                            </button>
                        @endif
                        @if($showCompleteBtn)
                            <button class="mdp-action-btn mdp-action-complete" id="openCompleteModalBtn" type="button">
                                <i class="fi fi-rr-check-circle"></i>
                                <span>Set as Complete</span>
                            </button>
                        @endif
                    </div>
                @elseif($isItemCompleted)
                    <div class="mdp-completed-banner">
                        <i class="fi fi-rr-badge-check"></i>
                        <span>This milestone item has been completed</span>
                    </div>
                @endif

            </div>{{-- /mdMainView --}}
        </div>

        {{-- ════════════ FULL DETAILS MODAL ════════════ --}}
        <div id="fullDetailsModal" class="mdp-modal-overlay" style="display:none;">
            <div class="mdp-modal mdp-modal-lg">
                {{-- Modal Header --}}
                <div class="mdp-modal-header">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-content-center" style="background:rgba(238,162,75,.12); display:flex; align-items:center; justify-content:center;">
                            <i class="fi fi-rr-list-check" style="color:#EEA24B;"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900" style="font-family:ui-sans-serif,system-ui,-apple-system,sans-serif; letter-spacing:-0.01em;">Full Details</h3>
                            <p class="text-xs text-gray-400 mt-0.5 font-medium">{{ $item->milestone_item_title ?? '' }}</p>
                        </div>
                    </div>
                    <button class="mdp-modal-close" id="closeFullDetailsModal" type="button">
                        <i class="fi fi-rr-cross-small"></i>
                    </button>
                </div>

                {{-- Tab Bar Inside Modal --}}
                <div class="fdm-tab-bar">
                    <button class="fdm-tab active" data-fdm-tab="info">
                        <i class="fi fi-rr-info"></i>
                        <span>Milestone Info</span>
                    </button>
                    <button class="fdm-tab" data-fdm-tab="payments">
                        <i class="fi fi-rr-credit-card"></i>
                        <span>Payments</span>
                        @if($pendingPayments > 0)
                            <span class="fdm-tab-badge">{{ $pendingPayments }}</span>
                        @endif
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="mdp-modal-body" id="fullDetailsModalBody">

                    {{-- ════════════ TAB: MILESTONE INFO ════════════ --}}
                    <div class="fdm-tab-content active" id="fdm-tab-info">

                        {{-- Milestone Hero Card --}}
                        <div class="fdm-hero-card">
                            <div class="fdm-hero-card-inner">
                                <p class="fdm-hero-label">MILESTONE ITEM {{ $seqNum }}</p>
                                <h3 class="fdm-hero-title">{{ $item->milestone_item_title }}</h3>
                                <div class="flex items-center gap-3 mt-3 flex-wrap">
                                    <span class="fdm-status-pill" style="background:{{ $statusColor }}20; color:{{ $statusColor }}; border:1px solid {{ $statusColor }}40;">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full mr-1.5" style="background:{{ $statusColor }};"></span>
                                        {{ $statusLabel }}
                                    </span>
                                    <span class="fdm-project-name">
                                        <i class="fi fi-rr-building text-[0.5625rem]"></i>
                                        {{ $projectTitle }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Description Section --}}
                        <div class="fdm-section">
                            <div class="fdm-section-header">
                                <div class="fdm-section-icon">
                                    <i class="fi fi-rr-document"></i>
                                </div>
                                <h4 class="fdm-section-title">Description</h4>
                            </div>
                            @if($item->milestone_item_description)
                                <div class="fdm-description-card">
                                    <p class="text-sm text-gray-600 leading-relaxed" style="font-family:ui-sans-serif,system-ui,sans-serif;">{{ $item->milestone_item_description }}</p>
                                </div>
                            @else
                                <div class="fdm-empty-block">
                                    <i class="fi fi-rr-document text-gray-300"></i>
                                    <p>No description provided</p>
                                </div>
                            @endif
                        </div>

                        {{-- Attachments Section --}}
                        <div class="fdm-section">
                            <div class="fdm-section-header">
                                <div class="fdm-section-icon">
                                    <i class="fi fi-rr-clip"></i>
                                </div>
                                <h4 class="fdm-section-title">Attachments</h4>
                                @if(count($itemFiles ?? []) > 0)
                                    <span class="fdm-section-count">{{ count($itemFiles) }}</span>
                                @endif
                            </div>
                            @if(count($itemFiles ?? []) === 0)
                                <div class="fdm-empty-block">
                                    <i class="fi fi-rr-clip text-gray-300"></i>
                                    <p>No attachments</p>
                                    <span>Files will appear here when uploaded</span>
                                </div>
                            @else
                                <div class="fdm-files-grid">
                                    @foreach($itemFiles as $af)
                                        @php $afName = basename($af->file_path); $afExt = strtoupper(pathinfo($afName, PATHINFO_EXTENSION)); @endphp
                                        <a href="/storage/{{ $af->file_path }}" target="_blank" class="fdm-file-item">
                                            <div class="fdm-file-icon"><i class="fi fi-rr-file"></i></div>
                                            <div class="flex-1 min-w-0">
                                                <p class="fdm-file-name">{{ $afName }}</p>
                                                <p class="fdm-file-ext">{{ $afExt }}</p>
                                            </div>
                                            <i class="fi fi-rr-download fdm-file-dl"></i>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Quick Financial Overview --}}
                        <div class="fdm-finance-card" id="fdmGoToPaymentsTab">
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <p class="text-xs text-gray-400 font-semibold" style="font-family:ui-sans-serif,system-ui,sans-serif;">Payment Progress</p>
                                    <p class="text-base font-bold text-gray-900 mt-0.5" style="font-family:ui-sans-serif,system-ui,sans-serif;">₱{{ number_format($totalPaid, 0) }} <span class="text-gray-400 font-medium text-sm">of</span> ₱{{ number_format($effectiveRequired, 0) }}</p>
                                </div>
                                <span class="fdm-status-pill" style="background:{{ $paymentStatusColor }}18; color:{{ $paymentStatusColor }}; border:1px solid {{ $paymentStatusColor }}30;">{{ $derivedStatus }}</span>
                            </div>
                            <div class="mdp-progress-track" style="height:5px;">
                                <div class="mdp-progress-fill" style="width:{{ $paymentPct }}%;"></div>
                            </div>
                            <div class="text-center mt-3">
                                <span class="fdm-link-btn">
                                    View payment details
                                    <i class="fi fi-rr-arrow-right text-[0.5rem]"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- ════════════ TAB: PAYMENTS ════════════ --}}
                    <div class="fdm-tab-content" id="fdm-tab-payments">

                        {{-- Financial Summary Accordion --}}
                        <div class="fdm-accordion" id="fdmFinAccordion">
                            <div class="fdm-accordion-header" id="fdmFinAccordionToggle">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="fdm-section-icon"><i class="fi fi-rr-dollar"></i></div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">Financial Summary</p>
                                        <p class="text-xs text-gray-400 font-medium">₱{{ number_format($totalPaid, 0) }} / ₱{{ number_format($effectiveRequired, 0) }} paid</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span class="fdm-status-pill" style="background:{{ $paymentStatusColor }}18; color:{{ $paymentStatusColor }}; border:1px solid {{ $paymentStatusColor }}30;">{{ $derivedStatus }}</span>
                                    <i class="fi fi-rr-angle-small-down fdm-accordion-chevron"></i>
                                </div>
                            </div>
                            <div class="mdp-progress-track mx-4 mb-1" style="height:3px;">
                                <div class="mdp-progress-fill" style="width:{{ $paymentPct }}%;"></div>
                            </div>
                            <div class="fdm-accordion-body" id="fdmFinAccordionBody" style="display:none;">
                                @if($adjustedCost !== null && $carryForward > 0)
                                    <div class="fdm-fin-row"><span>Original Cost</span><span class="text-gray-400 font-semibold">₱{{ number_format($originalCost, 2) }}</span></div>
                                    <div class="fdm-fin-row"><span class="text-red-600">Carry-forward</span><span class="text-red-600 font-bold">+₱{{ number_format($carryForward, 2) }}</span></div>
                                    <div class="fdm-fin-row fdm-fin-total"><span class="font-bold text-gray-900">Adjusted Total</span><span class="font-bold text-gray-900">₱{{ number_format($adjustedCost, 2) }}</span></div>
                                @else
                                    <div class="fdm-fin-row"><span>Expected Amount</span><span class="text-gray-900 font-semibold">₱{{ number_format($effectiveRequired, 2) }}</span></div>
                                @endif
                                <div class="fdm-fin-row"><span>Paid (Approved)</span><span class="text-emerald-500 font-semibold">₱{{ number_format($totalPaid, 2) }}</span></div>
                                @if($totalSubmitted > 0)
                                    <div class="fdm-fin-row"><span>Pending Review</span><span class="text-amber-500 font-semibold">₱{{ number_format($totalSubmitted, 2) }}</span></div>
                                @endif
                                <div class="fdm-fin-row fdm-fin-total">
                                    <span class="font-bold text-gray-900">Remaining Balance</span>
                                    <span class="text-base font-bold" style="color:{{ $overAmount > 0 ? '#dc2626' : ($remainingBalance > 0 ? '#EEA24B' : '#10B981') }};">
                                        {{ $overAmount > 0 ? '₱'.number_format($overAmount, 2).' over' : '₱'.number_format($remainingBalance, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Work Deadline Card --}}
                        <div class="fdm-card">
                            <div class="flex items-center gap-3">
                                <div class="fdm-section-icon" style="background:{{ $wasExtended ? '#F59E0B15' : '#EEA24B15' }};">
                                    <i class="fi fi-rr-clock" style="color:{{ $wasExtended ? '#F59E0B' : '#EEA24B' }};"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">Work Deadline</p>
                                    <p class="text-sm text-gray-700 font-medium mt-1">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('D, M j, Y') : 'Not set' }}</p>
                                    @if($wasExtended && $originalDateToFinish)
                                        <div class="flex items-center gap-1 mt-1">
                                            <i class="fi fi-rr-arrow-right text-xs text-amber-500"></i>
                                            <span class="text-xs text-gray-400">Originally: {{ \Carbon\Carbon::parse($originalDateToFinish)->format('M j, Y') }}</span>
                                        </div>
                                        <span class="fdm-status-pill mt-1" style="background:#FEF3C7; color:#F59E0B; border:1px solid #FDE68A;">Extended{{ $extensionCount > 1 ? " {$extensionCount}×" : '' }}</span>
                                    @endif
                                </div>
                                @if($wasExtended && count($dateHistories ?? []) > 0)
                                    <button class="fdm-expand-btn" id="fdmDateHistoryToggle"><i class="fi fi-rr-angle-small-down"></i></button>
                                @endif
                            </div>
                            @if($wasExtended && count($dateHistories ?? []) > 0)
                                <div class="fdm-date-history" id="fdmDateHistoryBody" style="display:none;">
                                    <p class="text-xs font-bold text-gray-500 mb-2" style="font-family:ui-sans-serif,system-ui,sans-serif;">Date History</p>
                                    @foreach($dateHistories as $dh)
                                        <div class="flex gap-2 mb-2">
                                            <div class="w-2 h-2 rounded-full bg-amber-500 mt-1 flex-shrink-0"></div>
                                            <div>
                                                <p class="text-xs text-gray-700 font-medium">
                                                    {{ \Carbon\Carbon::parse($dh->previous_date)->format('M j, Y') }} → {{ \Carbon\Carbon::parse($dh->new_date)->format('M j, Y') }}
                                                </p>
                                                <p class="text-[0.625rem] text-gray-400">{{ $dh->change_reason }} • {{ \Carbon\Carbon::parse($dh->changed_at)->format('M j, Y') }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Payment Due Date Card --}}
                        <div class="fdm-card" id="fdmDueDateCard">
                            {{-- Display Mode --}}
                            <div id="fdmDueDateDisplay">
                                <div class="flex items-center gap-3">
                                    <div class="fdm-section-icon" style="background:{{ $dueDateUrgency ? $dueDateUrgency['color'].'15' : '#EEA24B15' }};">
                                        <i class="fi fi-rr-calendar" style="color:{{ $dueDateUrgency ? $dueDateUrgency['color'] : '#EEA24B' }};"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">Payment Due Date</p>
                                        @if($settlementDueDate)
                                            <div class="flex items-center gap-2 mt-1" id="fdmDueDateValue">
                                                <span class="text-sm text-gray-700 font-medium" id="fdmDueDateText">{{ \Carbon\Carbon::parse($settlementDueDate)->format('D, M j, Y') }}</span>
                                                @if($dueDateUrgency)
                                                    <span class="fdm-status-pill" id="fdmDueDateUrgency" style="background:{{ $dueDateUrgency['color'] }}18; color:{{ $dueDateUrgency['color'] }}; border:1px solid {{ $dueDateUrgency['color'] }}30;">{{ $dueDateUrgency['label'] }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-xs text-gray-400 mt-0.5" id="fdmDueDateNotSet">Not set yet</p>
                                        @endif
                                        @if($extensionDate)
                                            <div class="flex items-center gap-1 mt-1" id="fdmExtensionInfo">
                                                <i class="fi fi-rr-arrow-right text-xs text-amber-500"></i>
                                                <span class="text-xs text-amber-500 font-medium">Extended to {{ \Carbon\Carbon::parse($extensionDate)->format('M j, Y') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    @if(!$isItemCompleted)
                                        <button class="fdm-edit-btn" id="fdmEditDueDateBtn" type="button" title="{{ $settlementDueDate ? 'Edit due date' : 'Set due date' }}">
                                            <i class="fi fi-rr-pencil"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Edit Mode --}}
                            <div id="fdmDueDateEdit" style="display:none;">
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="fdm-section-icon" style="background:#EEA24B15;">
                                        <i class="fi fi-rr-calendar" style="color:#EEA24B;"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">{{ $settlementDueDate ? 'Edit' : 'Set' }} Payment Due Date</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Choose the deadline for payment settlement</p>
                                    </div>
                                </div>
                                <div class="fdm-edit-form">
                                    <div class="fdm-form-field">
                                        <label class="fdm-form-label">Due Date <span class="text-red-500">*</span></label>
                                        <input type="date" id="fdmDueDateInput" class="fdm-form-input"
                                               value="{{ $settlementDueDate ?? '' }}"
                                               min="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="fdm-form-field">
                                        <label class="fdm-form-label">Extension Date <span class="text-xs text-gray-400 font-normal">(optional)</span></label>
                                        <input type="date" id="fdmExtDateInput" class="fdm-form-input"
                                               value="{{ $extensionDate ?? '' }}">
                                        <p class="fdm-form-hint">Must be after the due date</p>
                                    </div>
                                    <div class="flex items-center gap-2 mt-3">
                                        <button type="button" class="fdm-btn fdm-btn-primary" id="fdmSaveDueDateBtn">
                                            <i class="fi fi-rr-check"></i>
                                            <span>Save</span>
                                        </button>
                                        <button type="button" class="fdm-btn fdm-btn-ghost" id="fdmCancelDueDateBtn">
                                            Cancel
                                        </button>
                                    </div>
                                    <div id="fdmDueDateError" class="fdm-inline-error" style="display:none;"></div>
                                    <div id="fdmDueDateSuccess" class="fdm-inline-success" style="display:none;"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Payment History --}}
                        <div class="fdm-section">
                            <div class="fdm-section-header" style="margin-bottom:16px;">
                                <div class="fdm-section-icon">
                                    <i class="fi fi-rr-receipt"></i>
                                </div>
                                <h4 class="fdm-section-title">Payment History</h4>
                                @if(count($payments) > 0)
                                    <span class="fdm-section-count">{{ count($payments) }}</span>
                                @endif
                            </div>

                            @if(count($payments) === 0)
                                <div class="fdm-empty-block" style="padding:32px 16px;">
                                    <i class="fi fi-rr-inbox text-gray-300" style="font-size:1.75rem;"></i>
                                    <p>No payment receipts yet</p>
                                    <span>Payments will appear here once submitted</span>
                                </div>
                            @else
                                <div class="fdm-payments-list">
                                    @foreach($payments as $payment)
                                        @php
                                            $pStatus = $payment->payment_status;
                                            $pColor = $pStatus === 'approved' ? '#10B981' : ($pStatus === 'rejected' ? '#EF4444' : ($pStatus === 'submitted' ? '#F59E0B' : '#94a3b8'));
                                            $pBg = $pStatus === 'approved' ? '#D1FAE5' : ($pStatus === 'rejected' ? '#FEE2E2' : ($pStatus === 'submitted' ? '#FEF3C7' : '#F1F5F9'));
                                        @endphp
                                        <div class="fdm-payment-card">
                                            <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-base font-bold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">₱{{ number_format(floatval($payment->amount), 2) }}</span>
                                                    <span class="fdm-payment-badge" style="background:{{ $pBg }}; color:{{ $pColor }};">{{ ucfirst($pStatus) }}</span>
                                                </div>
                                                <span class="text-xs text-gray-400 font-medium">{{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('l, j F Y') : '' }}</span>
                                            </div>
                                            <div class="flex items-center text-xs text-gray-500 mb-2" style="font-family:ui-sans-serif,system-ui,sans-serif;">
                                                <span>{{ ucwords(str_replace('_', ' ', $payment->payment_type ?? '')) }}</span>
                                                @if($payment->transaction_number)
                                                    <span class="mx-1.5 text-gray-300">·</span>
                                                    <span>Ref: {{ $payment->transaction_number }}</span>
                                                @endif
                                            </div>
                                            @if($pStatus === 'rejected' && $payment->reason)
                                                <div class="fdm-rejection-notice">
                                                    <i class="fi fi-rr-exclamation"></i>
                                                    <span>{{ $payment->reason }}</span>
                                                </div>
                                            @endif
                                            @if($payment->receipt_photo)
                                                <a href="/storage/{{ $payment->receipt_photo }}" target="_blank" class="fdm-receipt-img-wrap">
                                                    <img src="/storage/{{ $payment->receipt_photo }}" alt="Receipt">
                                                    <div class="fdm-receipt-overlay">
                                                        <i class="fi fi-rr-search"></i>
                                                        <span>View Receipt</span>
                                                    </div>
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($showPaymentBtn)
                                <button type="button" class="fdm-send-payment-btn" id="fdmSendPaymentBtn">
                                    <div class="fdm-send-payment-icon">
                                        <i class="fi fi-rr-plus"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">Send new payment receipt</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Upload a payment receipt for this milestone</p>
                                    </div>
                                    <i class="fi fi-rr-angle-right text-gray-300 text-xs"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="mdp-modal-footer">
                    <button type="button" class="mdp-modal-cancel" id="cancelFullDetailsModal">
                        <i class="fi fi-rr-cross-small" style="font-size:0.625rem;"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
        @else
            {{-- Empty state --}}
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
                <i class="fi fi-rr-info text-5xl text-gray-300 block mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-500 mb-2">No Milestone Data</h3>
                <p class="text-gray-400 mb-6">The milestone item could not be loaded.</p>
                <a href="{{ route('owner.projects.milestone-report') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-400 hover:bg-orange-500 text-white rounded-xl text-sm font-semibold transition-all">
                    <i class="fi fi-rr-arrow-left"></i> Back to Timeline
                </a>
            </div>
        @endif
    </div>

    {{-- Progress Report Detail Modal --}}
    @include('owner.propertyOwner_Modals.ownerProgressreport_Modal')

    {{-- Send Report Modal --}}
    @include('owner.propertyOwner_Modals.ownerSendReport_Modal')

    {{-- Report History Modal --}}
    @include('owner.propertyOwner_Modals.ownerReportHistory_Modal')

    {{-- ═══ Payment Receipt Modal ═══ --}}
    <div id="paymentReceiptModal" class="mdp-modal-overlay" style="display:none;">
        <div class="mdp-modal">
            <div class="mdp-modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(238,162,75,.12);">
                        <i class="fi fi-rr-credit-card" style="color:#EEA24B;"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Send Payment Receipt</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Upload a payment receipt for this milestone</p>
                    </div>
                </div>
                <button class="mdp-modal-close" id="closePaymentModal" type="button">
                    <i class="fi fi-rr-cross-small"></i>
                </button>
            </div>
            <form id="paymentReceiptForm" enctype="multipart/form-data">
                <div class="mdp-modal-body">
                    {{-- Amount --}}
                    <div class="mdp-form-group">
                        <label class="mdp-form-label">Amount <span class="text-red-500">*</span></label>
                        <div class="mdp-input-wrapper">
                            <span class="mdp-input-prefix">₱</span>
                            <input type="text" id="paymentAmount" name="amount" class="mdp-form-input mdp-input-has-prefix"
                                   placeholder="0.00" value="{{ number_format($remainingBalance, 2, '.', '') }}"
                                   inputmode="decimal" autocomplete="off" required>
                        </div>
                        <p class="mdp-form-hint">Remaining balance: <strong style="color:#EEA24B;">₱{{ number_format($remainingBalance, 2) }}</strong></p>
                    </div>

                    {{-- Payment Method --}}
                    <div class="mdp-form-group">
                        <label class="mdp-form-label">Payment Method <span class="text-red-500">*</span></label>
                        <div class="mdp-method-grid">
                            <label class="mdp-method-option">
                                <input type="radio" name="payment_type" value="cash" class="hidden">
                                <div class="mdp-method-card">
                                    <i class="fi fi-rr-money-bill-wave"></i>
                                    <span>Cash</span>
                                </div>
                            </label>
                            <label class="mdp-method-option">
                                <input type="radio" name="payment_type" value="bank_transfer" class="hidden" checked>
                                <div class="mdp-method-card">
                                    <i class="fi fi-rr-bank"></i>
                                    <span>Bank Transfer</span>
                                </div>
                            </label>
                            <label class="mdp-method-option">
                                <input type="radio" name="payment_type" value="online_payment" class="hidden">
                                <div class="mdp-method-card">
                                    <i class="fi fi-rr-globe"></i>
                                    <span>Online</span>
                                </div>
                            </label>
                            <label class="mdp-method-option">
                                <input type="radio" name="payment_type" value="check" class="hidden">
                                <div class="mdp-method-card">
                                    <i class="fi fi-rr-document"></i>
                                    <span>Check</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Transaction Date --}}
                    <div class="mdp-form-group">
                        <label class="mdp-form-label">Transaction Date <span class="text-red-500">*</span></label>
                        <input type="date" id="paymentDate" name="transaction_date" class="mdp-form-input"
                               value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                    </div>

                    {{-- Reference Number --}}
                    <div class="mdp-form-group">
                        <label class="mdp-form-label">Reference / Transaction Number</label>
                        <input type="text" id="paymentRef" name="transaction_number" class="mdp-form-input"
                               placeholder="Optional">
                    </div>

                    {{-- Receipt Photo --}}
                    <div class="mdp-form-group">
                        <label class="mdp-form-label">Receipt Photo <span class="text-xs text-gray-400 font-normal">(optional but recommended)</span></label>
                        <div class="mdp-file-upload" id="receiptDropZone">
                            <div class="mdp-file-upload-content" id="receiptPlaceholder">
                                <i class="fi fi-rr-cloud-upload-alt text-2xl" style="color:#EEA24B;"></i>
                                <p class="text-sm font-semibold text-gray-500 mt-2">Click or drag to upload</p>
                                <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, PDF — max 5MB</p>
                            </div>
                            <div class="mdp-file-preview" id="receiptPreview" style="display:none;">
                                <img id="receiptPreviewImg" src="" alt="Receipt" class="w-full max-h-40 object-cover rounded-lg">
                                <button type="button" class="mdp-file-remove" id="removeReceipt">
                                    <i class="fi fi-rr-cross-small"></i>
                                </button>
                            </div>
                            <input type="file" id="receiptFile" name="receipt_photo" accept="image/*,.pdf" class="hidden">
                        </div>
                    </div>

                    {{-- Over-amount warning --}}
                    <div id="overAmountWarning" class="mdp-alert mdp-alert-warning" style="display:none;">
                        <i class="fi fi-rr-exclamation"></i>
                        <span>The amount exceeds the remaining balance. You can still submit if this is intentional.</span>
                    </div>
                </div>
                <div class="mdp-modal-footer">
                    <button type="button" class="mdp-modal-cancel" id="cancelPaymentBtn">Cancel</button>
                    <button type="submit" class="mdp-modal-submit" id="submitPaymentBtn">
                        <i class="fi fi-rr-paper-plane"></i>
                        <span>Submit Payment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ Completion Confirm Modal ═══ --}}
    <div id="completeConfirmModal" class="mdp-modal-overlay" style="display:none;">
        <div class="mdp-modal mdp-modal-sm">
            <div class="mdp-modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(16,185,129,.12);">
                        <i class="fi fi-rr-badge-check" style="color:#10B981;"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Mark as Complete</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Confirm milestone completion</p>
                    </div>
                </div>
                <button class="mdp-modal-close" id="closeCompleteModal" type="button">
                    <i class="fi fi-rr-cross-small"></i>
                </button>
            </div>
            <div class="mdp-modal-body">
                @if($hasRejectedOrSubmittedReports)
                    <div class="mdp-alert mdp-alert-warning mb-4">
                        <i class="fi fi-rr-exclamation"></i>
                        <span>There are rejected or unapproved progress reports. You can still proceed with completion.</span>
                    </div>
                @endif
                <p class="text-sm text-gray-600 leading-relaxed">Are you sure you want to mark <strong class="text-gray-900">{{ $item ? $item->milestone_item_title : '' }}</strong> as complete?</p>
                <div class="mdp-confirm-checklist">
                    <div class="mdp-confirm-item">
                        <i class="fi fi-rr-check text-emerald-500"></i>
                        <span>Item status will change to <strong>Completed</strong></span>
                    </div>
                    <div class="mdp-confirm-item">
                        <i class="fi fi-rr-check text-emerald-500"></i>
                        <span>Any underpayment will be carried forward</span>
                    </div>
                    <div class="mdp-confirm-item">
                        <i class="fi fi-rr-check text-emerald-500"></i>
                        <span>The contractor will be notified</span>
                    </div>
                </div>
            </div>
            <div class="mdp-modal-footer">
                <button type="button" class="mdp-modal-cancel" id="cancelCompleteBtn">Cancel</button>
                <button type="button" class="mdp-modal-submit mdp-modal-submit-green" id="confirmCompleteBtn">
                    <i class="fi fi-rr-badge-check"></i>
                    <span>Yes, Mark Complete</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ═══ Success/Carry-forward Result Modal ═══ --}}
    <div id="resultModal" class="mdp-modal-overlay" style="display:none;">
        <div class="mdp-modal mdp-modal-sm">
            <div class="mdp-modal-body text-center py-8">
                <div class="mdp-result-icon" id="resultIcon">
                    <i class="fi fi-rr-badge-check"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mt-4" id="resultTitle">Success!</h3>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed" id="resultMessage"></p>
                <div id="resultCarryForward" class="mdp-carry-forward-card" style="display:none;">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fi fi-rr-arrow-right text-amber-500"></i>
                        <span class="text-sm font-semibold text-gray-900">Carry-forward Applied</span>
                    </div>
                    <p class="text-xs text-gray-500" id="carryForwardText"></p>
                </div>
            </div>
            <div class="mdp-modal-footer justify-center">
                <button type="button" class="mdp-modal-submit" id="resultOkBtn">
                    <span>OK</span>
                </button>
            </div>
        </div>
    </div>
    {{-- ═══ Project Summary Modal ═══ --}}
    @php
        $psSummary = $projectSummary ?? null;
        $psHeader = $psSummary['header'] ?? [];
        $psOverview = $psSummary['overview'] ?? [];
        $psMilestones = $psSummary['milestones'] ?? [];
        $psBudgetHistory = $psSummary['budget_history'] ?? [];
        $psChangeHistory = $psSummary['change_history'] ?? [];
        $psPayments = $psSummary['payments'] ?? ['records' => [], 'total_approved' => 0, 'total_pending' => 0, 'total_rejected' => 0];
        $psReports = $psSummary['progress_reports'] ?? [];
        $psGeneratedAt = $psSummary['generated_at'] ?? null;

        $psProgressPct = ($psOverview['total_milestones'] ?? 0) > 0
            ? round(($psOverview['completed_milestones'] ?? 0) / $psOverview['total_milestones'] * 100)
            : 0;
        $psBudgetUtil = ($psOverview['current_budget'] ?? 0) > 0
            ? round(($psOverview['total_paid'] ?? 0) / $psOverview['current_budget'] * 100)
            : 0;

        $psStatusColor = function($status) {
            return match(strtolower($status ?? '')) {
                'completed', 'approved' => ['bg' => '#D1FAE5', 'fg' => '#10B981'],
                'pending', 'submitted' => ['bg' => '#FEF3C7', 'fg' => '#F59E0B'],
                'active', 'in_progress' => ['bg' => '#DBEAFE', 'fg' => '#3B82F6'],
                'rejected' => ['bg' => '#FEE2E2', 'fg' => '#EF4444'],
                'revision_requested' => ['bg' => '#FFF3E6', 'fg' => '#EC7E00'],
                default => ['bg' => '#F1F5F9', 'fg' => '#64748B'],
            };
        };
    @endphp
    <div id="projectSummaryModal" class="mdp-modal-overlay" style="display:none;">
        <div class="mdp-modal mdp-modal-lg">
            {{-- Modal Header --}}
            <div class="mdp-modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(238,162,75,.12);">
                        <i class="fi fi-rr-chart-pie-alt" style="color:#EEA24B;"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900" style="font-family:ui-sans-serif,system-ui,-apple-system,sans-serif; letter-spacing:-0.01em;">Project Summary</h3>
                        <p class="text-xs text-gray-400 mt-0.5 font-medium">{{ $psHeader['project_title'] ?? $projectTitle }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="psm-refresh-btn" id="refreshProjectSummary" type="button" title="Refresh summary">
                        <i class="fi fi-rr-refresh"></i>
                    </button>
                    <button class="mdp-modal-close" id="closeProjectSummaryModal" type="button">
                        <i class="fi fi-rr-cross-small"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="mdp-modal-body" id="projectSummaryModalBody">
                @if(!$psSummary)
                    <div class="fdm-empty-block" style="padding:48px 16px;">
                        <i class="fi fi-rr-chart-pie-alt text-gray-300" style="font-size:2rem;"></i>
                        <p>Summary data not available</p>
                        <span>Could not load project summary at this time</span>
                    </div>
                @else

                {{-- ═══ A. PROJECT HEADER CARD ═══ --}}
                <div class="psm-header-card">
                    <h4 class="psm-header-title">{{ $psHeader['project_title'] ?? '' }}</h4>
                    @if(!empty($psHeader['project_description']))
                        <p class="psm-header-desc">{{ $psHeader['project_description'] }}</p>
                    @endif

                    <div class="flex items-center justify-between flex-wrap gap-2 mt-3">
                        <div class="flex items-center gap-1.5">
                            <i class="fi fi-rr-marker text-xs text-gray-400"></i>
                            <span class="text-xs text-gray-500">{{ $psHeader['project_location'] ?? '—' }}</span>
                        </div>
                        @php $hsc = $psStatusColor($psHeader['status'] ?? ''); @endphp
                        <span class="psm-badge" style="background:{{ $hsc['bg'] }}; color:{{ $hsc['fg'] }};">
                            {{ strtoupper(str_replace('_', ' ', $psHeader['status'] ?? '')) }}
                        </span>
                    </div>

                    <div class="psm-divider"></div>

                    {{-- Parties --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="psm-meta-label">PROPERTY OWNER</p>
                            <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">{{ $psHeader['owner_name'] ?? '—' }}</p>
                            @if(!empty($psHeader['owner_email']))
                                <p class="text-xs text-gray-400 mt-0.5">{{ $psHeader['owner_email'] }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="psm-meta-label">CONTRACTOR</p>
                            <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">{{ $psHeader['contractor_name'] ?? '—' }}</p>
                            @if(!empty($psHeader['contractor_company']))
                                <p class="text-xs text-gray-400 mt-0.5">{{ $psHeader['contractor_company'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="psm-divider"></div>

                    {{-- Timeline strip --}}
                    <div class="flex items-center gap-3 flex-wrap">
                        <div>
                            <p class="psm-meta-label">START</p>
                            <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">
                                {{ ($psHeader['original_start_date'] ?? null) ? \Carbon\Carbon::parse($psHeader['original_start_date'])->format('M j, Y') : '—' }}
                            </p>
                        </div>
                        <i class="fi fi-rr-arrow-right text-xs text-gray-300"></i>
                        <div>
                            <p class="psm-meta-label">{{ !empty($psHeader['was_extended']) ? 'CURRENT END' : 'END' }}</p>
                            <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">
                                {{ ($psHeader['current_end_date'] ?? null) ? \Carbon\Carbon::parse($psHeader['current_end_date'])->format('M j, Y') : '—' }}
                            </p>
                        </div>
                        @if(!empty($psHeader['was_extended']) && ($psHeader['original_end_date'] ?? '') !== ($psHeader['current_end_date'] ?? ''))
                            <span class="psm-badge" style="background:#FEF3C7; color:#F59E0B;">
                                <i class="fi fi-rr-clock text-[0.5rem]"></i> Extended
                            </span>
                        @endif
                    </div>
                </div>

                {{-- ═══ B. EXECUTIVE OVERVIEW ═══ --}}
                <div class="psm-section" data-psm-section="overview">
                    <button class="psm-section-toggle" data-psm-toggle="overview" type="button">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-chart-histogram text-sm" style="color:#EEA24B;"></i>
                            <span class="psm-section-title">Executive Overview</span>
                        </div>
                        <i class="fi fi-rr-angle-small-down psm-chevron"></i>
                    </button>
                    <div class="psm-section-body" data-psm-body="overview">
                        {{-- Progress bar --}}
                        <div class="mb-3">
                            <div class="flex justify-between mb-1">
                                <span class="text-xs font-semibold text-gray-700" style="font-family:ui-sans-serif,system-ui,sans-serif;">Milestone Progress</span>
                                <span class="text-xs font-bold text-gray-900">{{ $psProgressPct }}%</span>
                            </div>
                            <div class="psm-progress-track">
                                <div class="psm-progress-fill" style="width:{{ $psProgressPct }}%; background:#10B981;"></div>
                            </div>
                            <p class="text-[0.6875rem] text-gray-400 mt-1">{{ $psOverview['completed_milestones'] ?? 0 }} of {{ $psOverview['total_milestones'] ?? 0 }} milestones completed</p>
                        </div>

                        {{-- Budget utilization --}}
                        <div class="mb-4">
                            <div class="flex justify-between mb-1">
                                <span class="text-xs font-semibold text-gray-700" style="font-family:ui-sans-serif,system-ui,sans-serif;">Budget Utilization</span>
                                <span class="text-xs font-bold text-gray-900">{{ $psBudgetUtil }}%</span>
                            </div>
                            <div class="psm-progress-track">
                                <div class="psm-progress-fill" style="width:{{ min($psBudgetUtil, 100) }}%; background:{{ $psBudgetUtil > 100 ? '#EF4444' : '#3B82F6' }};"></div>
                            </div>
                        </div>

                        {{-- Financial grid --}}
                        <div class="psm-fin-grid">
                            <div class="psm-fin-cell">
                                <span class="psm-fin-label">ORIGINAL BUDGET</span>
                                <span class="psm-fin-value">₱{{ number_format($psOverview['original_budget'] ?? 0, 0) }}</span>
                            </div>
                            <div class="psm-fin-cell {{ ($psOverview['current_budget'] ?? 0) !== ($psOverview['original_budget'] ?? 0) ? 'psm-fin-highlight' : '' }}">
                                <span class="psm-fin-label">CURRENT BUDGET</span>
                                <span class="psm-fin-value">₱{{ number_format($psOverview['current_budget'] ?? 0, 0) }}</span>
                            </div>
                            <div class="psm-fin-cell">
                                <span class="psm-fin-label">TOTAL PAID</span>
                                <span class="psm-fin-value" style="color:#10B981;">₱{{ number_format($psOverview['total_paid'] ?? 0, 0) }}</span>
                            </div>
                            <div class="psm-fin-cell">
                                <span class="psm-fin-label">PENDING</span>
                                <span class="psm-fin-value" style="color:#F59E0B;">₱{{ number_format($psOverview['total_pending'] ?? 0, 0) }}</span>
                            </div>
                            <div class="psm-fin-cell">
                                <span class="psm-fin-label">REMAINING</span>
                                <span class="psm-fin-value">₱{{ number_format($psOverview['remaining_balance'] ?? 0, 0) }}</span>
                            </div>
                            <div class="psm-fin-cell">
                                <span class="psm-fin-label">PAYMENT MODE</span>
                                <span class="psm-fin-value-text">{{ ucwords(str_replace('_', ' ', $psOverview['payment_mode'] ?? '—')) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ═══ C. MILESTONES BREAKDOWN ═══ --}}
                <div class="psm-section" data-psm-section="milestones">
                    <button class="psm-section-toggle" data-psm-toggle="milestones" type="button">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-layers text-sm" style="color:#EEA24B;"></i>
                            <span class="psm-section-title">Milestones ({{ count($psMilestones) }})</span>
                        </div>
                        <i class="fi fi-rr-angle-small-down psm-chevron"></i>
                    </button>
                    <div class="psm-section-body" data-psm-body="milestones">
                        @foreach($psMilestones as $ms)
                            @php
                                $msObj = is_object($ms) ? $ms : (object)$ms;
                                $msc = $psStatusColor($msObj->status ?? '');
                            @endphp
                            <div class="psm-milestone-card">
                                <div class="flex items-center gap-2.5 mb-2">
                                    <div class="psm-milestone-seq">{{ $msObj->sequence_order ?? '' }}</div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 leading-tight" style="font-family:ui-sans-serif,system-ui,sans-serif;">{{ $msObj->title ?? '' }}</p>
                                        <p class="text-[0.6875rem] text-gray-400 mt-0.5">{{ $msObj->milestone_name ?? '' }}</p>
                                    </div>
                                    <span class="psm-badge" style="background:{{ $msc['bg'] }}; color:{{ $msc['fg'] }};">
                                        {{ str_replace('_', ' ', $msObj->status ?? '') }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mb-2">
                                    <div>
                                        <p class="psm-meta-label">BUDGET</p>
                                        <p class="text-xs font-semibold text-gray-900">₱{{ number_format($msObj->current_allocation ?? 0, 0) }}</p>
                                    </div>
                                    <div>
                                        <p class="psm-meta-label">PAID</p>
                                        <p class="text-xs font-semibold" style="color:#10B981;">₱{{ number_format($msObj->total_paid ?? 0, 0) }}</p>
                                    </div>
                                    <div>
                                        <p class="psm-meta-label">DUE</p>
                                        <p class="text-xs font-semibold text-gray-900">{{ $msObj->current_due_date ? \Carbon\Carbon::parse($msObj->current_due_date)->format('M j, Y') : '—' }}</p>
                                    </div>
                                </div>
                                @if(!empty($msObj->was_extended))
                                    <div class="flex items-center gap-1.5 mb-2">
                                        <i class="fi fi-rr-clock text-xs text-amber-500"></i>
                                        <span class="text-[0.6875rem] text-amber-500 font-medium">Extended {{ $msObj->extension_count ?? 0 }}× (was {{ $msObj->original_due_date ? \Carbon\Carbon::parse($msObj->original_due_date)->format('M j, Y') : '—' }})</span>
                                    </div>
                                @endif
                                <div class="psm-progress-track" style="height:4px;">
                                    <div class="psm-progress-fill" style="width:{{ $msObj->percentage_progress ?? 0 }}%; background:#3B82F6;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ═══ D. BUDGET HISTORY ═══ --}}
                @if(count($psBudgetHistory) > 0)
                <div class="psm-section" data-psm-section="budget">
                    <button class="psm-section-toggle" data-psm-toggle="budget" type="button">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-arrow-trend-up text-sm" style="color:#EEA24B;"></i>
                            <span class="psm-section-title">Budget History ({{ count($psBudgetHistory) }})</span>
                        </div>
                        <i class="fi fi-rr-angle-small-down psm-chevron"></i>
                    </button>
                    <div class="psm-section-body psm-collapsed" data-psm-body="budget">
                        @foreach($psBudgetHistory as $bh)
                            @php $bhObj = is_object($bh) ? $bh : (object)$bh; $bhsc = $psStatusColor($bhObj->status ?? ''); @endphp
                            <div class="psm-history-row">
                                <div class="psm-history-dot"></div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">{{ $bhObj->change_type ? 'Budget '.ucfirst($bhObj->change_type) : 'Timeline Update' }}</span>
                                        <span class="psm-badge" style="background:{{ $bhsc['bg'] }}; color:{{ $bhsc['fg'] }};">{{ $bhObj->status ?? '' }}</span>
                                    </div>
                                    @if(isset($bhObj->previous_budget) && isset($bhObj->updated_budget))
                                        <p class="text-xs text-gray-500">₱{{ number_format($bhObj->previous_budget, 0) }} → ₱{{ number_format($bhObj->updated_budget, 0) }}</p>
                                    @endif
                                    @if(!empty($bhObj->previous_end_date) && !empty($bhObj->proposed_end_date))
                                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($bhObj->previous_end_date)->format('M j, Y') }} → {{ \Carbon\Carbon::parse($bhObj->proposed_end_date)->format('M j, Y') }}</p>
                                    @endif
                                    @if(!empty($bhObj->reason))
                                        <p class="text-[0.6875rem] text-gray-400 italic mt-1">"{{ $bhObj->reason }}"</p>
                                    @endif
                                    <p class="text-[0.625rem] text-gray-300 mt-1">{{ $bhObj->date_proposed ? \Carbon\Carbon::parse($bhObj->date_proposed)->format('M j, Y') : '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ═══ E. CHANGE LOG ═══ --}}
                @if(count($psChangeHistory) > 0)
                <div class="psm-section" data-psm-section="changelog">
                    <button class="psm-section-toggle" data-psm-toggle="changelog" type="button">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-time-past text-sm" style="color:#EEA24B;"></i>
                            <span class="psm-section-title">Change Log ({{ count($psChangeHistory) }})</span>
                        </div>
                        <i class="fi fi-rr-angle-small-down psm-chevron"></i>
                    </button>
                    <div class="psm-section-body psm-collapsed" data-psm-body="changelog">
                        @foreach($psChangeHistory as $evt)
                            @php $evtObj = is_object($evt) ? $evt : (object)$evt; @endphp
                            <div class="psm-history-row">
                                <div class="psm-history-dot" style="background:#3B82F6;"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">{{ $evtObj->action ?? '' }}</p>
                                    @if(!empty($evtObj->performed_by))
                                        <p class="text-xs text-gray-500 mt-0.5">by {{ $evtObj->performed_by }}</p>
                                    @endif
                                    @if(!empty($evtObj->notes))
                                        <p class="text-[0.6875rem] text-gray-400 italic mt-1">"{{ $evtObj->notes }}"</p>
                                    @endif
                                    @if(!empty($evtObj->reference))
                                        <p class="text-[0.625rem] text-blue-500 mt-0.5">{{ $evtObj->reference }}</p>
                                    @endif
                                    <p class="text-[0.625rem] text-gray-300 mt-1">{{ $evtObj->date ? \Carbon\Carbon::parse($evtObj->date)->format('m/d/Y h:i A') : '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- ═══ F. PAYMENTS HISTORY ═══ --}}
                <div class="psm-section" data-psm-section="payments">
                    <button class="psm-section-toggle" data-psm-toggle="payments" type="button">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-credit-card text-sm" style="color:#EEA24B;"></i>
                            <span class="psm-section-title">Payments ({{ count($psPayments['records'] ?? []) }})</span>
                        </div>
                        <i class="fi fi-rr-angle-small-down psm-chevron"></i>
                    </button>
                    <div class="psm-section-body psm-collapsed" data-psm-body="payments">
                        {{-- Totals pills --}}
                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <div class="psm-payment-pill" style="border-color:#10B981;">
                                <span class="psm-meta-label" style="color:#10B981;">APPROVED</span>
                                <span class="text-sm font-bold" style="color:#10B981;">₱{{ number_format($psPayments['total_approved'] ?? 0, 0) }}</span>
                            </div>
                            <div class="psm-payment-pill" style="border-color:#F59E0B;">
                                <span class="psm-meta-label" style="color:#F59E0B;">PENDING</span>
                                <span class="text-sm font-bold" style="color:#F59E0B;">₱{{ number_format($psPayments['total_pending'] ?? 0, 0) }}</span>
                            </div>
                            <div class="psm-payment-pill" style="border-color:#EF4444;">
                                <span class="psm-meta-label" style="color:#EF4444;">REJECTED</span>
                                <span class="text-sm font-bold" style="color:#EF4444;">₱{{ number_format($psPayments['total_rejected'] ?? 0, 0) }}</span>
                            </div>
                        </div>

                        @if(count($psPayments['records'] ?? []) === 0)
                            <p class="text-xs text-gray-400 italic py-3">No payment records yet.</p>
                        @else
                            @foreach($psPayments['records'] as $pr)
                                @php $prObj = is_object($pr) ? $pr : (object)$pr; $prsc = $psStatusColor($prObj->status ?? ''); @endphp
                                <div class="psm-payment-row">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">{{ $prObj->milestone ?? '' }}</p>
                                            <p class="text-[0.6875rem] text-gray-400 capitalize">{{ str_replace('_', ' ', $prObj->payment_type ?? '') }}</p>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <p class="text-sm font-bold text-gray-900">₱{{ number_format($prObj->amount ?? 0, 2) }}</p>
                                            <span class="psm-badge mt-0.5" style="background:{{ $prsc['bg'] }}; color:{{ $prsc['fg'] }};">{{ $prObj->status ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        @if(!empty($prObj->transaction_number))
                                            <span class="text-[0.625rem] text-gray-400">Ref: {{ $prObj->transaction_number }}</span>
                                        @else
                                            <span></span>
                                        @endif
                                        <span class="text-[0.625rem] text-gray-300">{{ $prObj->transaction_date ? \Carbon\Carbon::parse($prObj->transaction_date)->format('M j, Y') : '' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- ═══ G. PROGRESS REPORTS ═══ --}}
                @if(count($psReports) > 0)
                <div class="psm-section" data-psm-section="progress">
                    <button class="psm-section-toggle" data-psm-toggle="progress" type="button">
                        <div class="flex items-center gap-2">
                            <i class="fi fi-rr-document text-sm" style="color:#EEA24B;"></i>
                            <span class="psm-section-title">Progress Reports ({{ count($psReports) }})</span>
                        </div>
                        <i class="fi fi-rr-angle-small-down psm-chevron"></i>
                    </button>
                    <div class="psm-section-body psm-collapsed" data-psm-body="progress">
                        @foreach($psReports as $rp)
                            @php $rpObj = is_object($rp) ? $rp : (object)$rp; $rpsc = $psStatusColor($rpObj->status ?? ''); @endphp
                            <div class="psm-report-row">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">{{ $rpObj->report_title ?? 'Progress Report' }}</p>
                                    <p class="text-[0.6875rem] text-gray-400 mt-0.5">{{ $rpObj->milestone ?? '' }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="psm-badge" style="background:{{ $rpsc['bg'] }}; color:{{ $rpsc['fg'] }};">{{ str_replace('_', ' ', $rpObj->status ?? '') }}</span>
                                    <p class="text-[0.625rem] text-gray-300 mt-1">{{ $rpObj->submitted_at ? \Carbon\Carbon::parse($rpObj->submitted_at)->format('M j, Y') : '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Generated timestamp --}}
                <p class="text-center text-[0.625rem] text-gray-300 mt-4 pb-2" id="psmGeneratedAt">
                    @if($psGeneratedAt)
                        Report generated {{ \Carbon\Carbon::parse($psGeneratedAt)->format('m/d/Y h:i A') }}
                    @endif
                </p>

                @endif
            </div>

            {{-- Modal Footer --}}
            <div class="mdp-modal-footer">
                <button type="button" class="mdp-modal-cancel" id="cancelProjectSummaryModal">
                    <i class="fi fi-rr-cross-small" style="font-size:0.625rem;"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_MilestoneReport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_MilestoneprogressReport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerProgressreport_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerSendReport_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerReportHistory_Modal.css') }}">
@endsection

@section('extra_js')
    <script>
        window.__milestoneProgressConfig = {
            itemId: @json($itemId ?? null),
            projectId: @json($projectId ?? null),
            milestoneId: @json($milestoneItem->milestone_id ?? null),
            userId: @json(session('user') ? session('user')->user_id : null),
            csrfToken: '{{ csrf_token() }}',
            progressReports: @json($progressReports ?? []),
            disputes: @json($disputes ?? []),
            projectTitle: @json($projectTitle ?? ''),
            milestoneTitle: @json($milestoneTitle ?? ''),
            allItems: @json($allItems ?? []),
            seqNum: @json($seqNum ?? 1),
            remainingBalance: @json($remainingBalance),
            showPaymentBtn: @json($showPaymentBtn),
            showCompleteBtn: @json($showCompleteBtn),
            settlementDueDate: @json($settlementDueDate ?? null),
            extensionDate: @json($extensionDate ?? null),
            routes: {
                milestoneReport: '{{ route("owner.projects.milestone-report") }}',
                setMilestoneItem: '/owner/projects/set-milestone-item',
                milestoneProgressReport: '/owner/projects/milestone-progress-report',
                paymentUpload: '/owner/payment/upload',
                milestoneItemComplete: '/owner/milestone-items/' + @json($itemId ?? 0) + '/complete',
                settlementDueDate: '/owner/milestone-items/' + @json($itemId ?? 0) + '/settlement-due-date',
            }
        };

        // Alias for modal JS that reads __milestoneReportConfig
        window.__milestoneReportConfig = {
            projectId: @json($projectId ?? null),
            milestoneId: @json($milestoneItem->milestone_id ?? null),
            userId: @json(session('user') ? session('user')->user_id : null),
            csrfToken: '{{ csrf_token() }}',
            disputes: @json($disputes ?? []),
        };
    </script>
    <script src="{{ asset('js/owner/propertyOwner_MilestoneprogressReport.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerProgressreport_Modal.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerSendReport_Modal.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerReportHistory_Modal.js') }}"></script>
@endsection
