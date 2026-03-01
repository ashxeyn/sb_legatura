@extends('layouts.app')

@section('title', 'Project Timeline - Legatura')

@section('content')
    <div class="property-owner-milestone-report min-h-screen bg-gray-50">
        <!-- Header Section -->
        <div class="milestone-report-header bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Back Button and Breadcrumb -->
                <div class="mb-6">
                    <div class="flex items-center gap-4 mb-3">
                        <a href="{{ route('owner.projects') }}" class="back-button">
                            <i class="fi fi-rr-arrow-left"></i>
                            <span>Back</span>
                        </a>
                        <div class="breadcrumb">
                            <a href="{{ route('owner.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <a href="{{ route('owner.projects') }}" class="breadcrumb-link">My Projects</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <span class="breadcrumb-current">Milestone Report</span>
                        </div>
                    </div>
                </div>

                <!-- Title -->
                <div class="section-header">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Milestone Report</h1>
                        <p class="text-gray-600 mt-1">Review milestone timeline, payment breakdown, and project duration</p>
                    </div>

                    {{-- Menu dropdown for dispute actions --}}
                    <div class="milestone-report-menu-wrapper">
                        <button class="milestone-report-menu-btn" id="milestoneReportMenuBtn" title="More options">
                            <i class="fi fi-rr-menu-dots-vertical"></i>
                        </button>
                        <div class="milestone-report-menu-dropdown" id="milestoneReportMenuDropdown" style="display:none;">
                            <button class="milestone-menu-item" id="menuFileDispute">
                                <i class="fi fi-rr-flag" style="color:#EF4444;"></i>
                                <span>File a Report</span>
                            </button>
                            <button class="milestone-menu-item" id="menuDisputeHistory">
                                <i class="fi fi-rr-time-past" style="color:#3B82F6;"></i>
                                <span>Report History</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div id="milestoneReportContainer" class="bg-white rounded-xl shadow-md p-6">
                <!-- Loading state -->
                <div id="milestoneLoadingState" class="text-center py-16">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-orange-500 mb-4"></div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Loading Project Timeline...</h3>
                    <p class="text-gray-500">Fetching milestone data from the server</p>
                </div>
                <!-- Error state (hidden by default) -->
                <div id="milestoneErrorState" class="text-center py-16" style="display:none;">
                    <i class="fi fi-rr-exclamation text-6xl text-red-300 mb-4" style="display:block;"></i>
                    <h3 class="text-xl font-semibold text-red-600 mb-2">Failed to Load Data</h3>
                    <p class="text-gray-500 mb-4" id="milestoneErrorMessage">An error occurred while loading milestone data.</p>
                    <button id="milestoneRetryBtn" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                        Retry
                    </button>
                </div>
                <!-- Content (hidden until data loads) -->
                <div id="milestoneContentArea" style="display:none;"></div>
            </div>
        </div>
    </div>

    <!-- Milestone Approve/Reject Modal -->
    <div id="milestoneApprovalModal" class="milestone-approval-modal" style="display:none;">
        <div class="milestone-approval-modal-overlay"></div>
        <div class="milestone-approval-modal-content">
            <div class="milestone-approval-modal-header">
                <div class="milestone-approval-icon-container" id="approvalModalIcon">
                    <i class="fi fi-rr-check-circle" style="font-size:24px; color:#10B981;"></i>
                </div>
                <h3 class="milestone-approval-modal-title" id="approvalModalTitle">Approve Milestone Setup</h3>
                <p class="milestone-approval-modal-subtitle" id="approvalModalSubtitle">Are you sure you want to approve this milestone setup?</p>
            </div>
            <div id="approvalReasonContainer" style="display:none;">
                <label class="milestone-approval-reason-label">
                    Reason for Changes <span style="color:#EF4444;">*</span>
                </label>
                <textarea id="approvalReasonInput" class="milestone-approval-reason-input"
                    placeholder="E.g., Timeline needs adjustment, Cost breakdown unclear, Missing important tasks..."
                    maxlength="500" rows="4"></textarea>
                <p class="milestone-approval-char-count"><span id="approvalCharCount">0</span>/500</p>
            </div>
            <div class="milestone-approval-modal-actions">
                <button class="milestone-approval-cancel-btn" id="approvalCancelBtn">Cancel</button>
                <button class="milestone-approval-confirm-btn" id="approvalConfirmBtn">Approve</button>
            </div>
        </div>
    </div>

    <!-- Complete Project Modal -->
    <div id="completeProjectModal" class="milestone-approval-modal" style="display:none;">
        <div class="milestone-approval-modal-overlay"></div>
        <div class="milestone-approval-modal-content">
            <div class="milestone-approval-modal-header">
                <div class="milestone-approval-icon-container">
                    <i class="fi fi-rr-exclamation" style="font-size:48px; color:#F59E0B;"></i>
                </div>
                <h3 class="milestone-approval-modal-title">Complete This Project?</h3>
                <p class="milestone-approval-modal-subtitle">You are about to mark this entire project as completed. This action will:</p>
            </div>
            <div class="complete-project-checklist">
                <div class="complete-project-check-item"><i class="fi fi-rr-check" style="color:#10B981;"></i> Mark all milestones as finished</div>
                <div class="complete-project-check-item"><i class="fi fi-rr-check" style="color:#10B981;"></i> Close the project timeline</div>
                <div class="complete-project-check-item"><i class="fi fi-rr-check" style="color:#10B981;"></i> Archive all project data</div>
            </div>
            <p class="complete-project-warning">This action cannot be undone. Are you sure you want to proceed?</p>
            <div class="milestone-approval-modal-actions">
                <button class="milestone-approval-cancel-btn" id="completeProjectCancelBtn">Cancel</button>
                <button class="milestone-approval-confirm-btn complete-project-confirm" id="completeProjectConfirmBtn">Complete Project</button>
            </div>
        </div>
    </div>

    <!-- Downpayment Detail Modal -->
    <div id="downpaymentDetailModal" class="dp-modal" style="display:none;">
        <div class="dp-modal-overlay"></div>
        <div class="dp-modal-content">
            <div class="dp-modal-header">
                <div class="dp-modal-header-left">
                    <div class="dp-modal-header-icon"><i class="fi fi-rr-hand-holding-usd"></i></div>
                    <div>
                        <h3 class="dp-modal-title">Downpayment</h3>
                        <p class="dp-modal-subtitle" id="dpModalProjectTitle"></p>
                    </div>
                </div>
                <button class="dp-modal-close-btn" title="Close">
                    <i class="fi fi-rr-cross-small"></i>
                </button>
            </div>
            <div class="dp-modal-body" id="downpaymentModalBody">
                <!-- Content injected by JS -->
            </div>
        </div>
    </div>

    <!-- Payment History Modal -->
    @include('owner.propertyOwner_Modals.ownerPaymenthistory_Modal')

    <!-- Dispute Modals -->
    @include('owner.propertyOwner_Modals.ownerSendReport_Modal')
    @include('owner.propertyOwner_Modals.ownerReportHistory_Modal')
    {{-- ═══ Project Summary Modal ═══ --}}
    @php
        $ps = $projectSummary ?? null;
        $psHeader = $ps['header'] ?? [];
        $psOverview = $ps['overview'] ?? [];
        $psMilestones = $ps['milestones'] ?? [];
        $psBudgetHistory = $ps['budget_history'] ?? [];
        $psChangeHistory = $ps['change_history'] ?? [];
        $psPayments = $ps['payments'] ?? ['records' => [], 'total_approved' => 0, 'total_pending' => 0, 'total_rejected' => 0];
        $psReports = $ps['progress_reports'] ?? [];
        $psGeneratedAt = $ps['generated_at'] ?? null;

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
    <div id="projectSummaryModal" class="psm-modal-overlay" style="display:none;">
        <div class="psm-modal">
            {{-- Modal Header --}}
            <div class="psm-modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(238,162,75,.12);">
                        <i class="fi fi-rr-chart-pie-alt" style="color:#EEA24B;"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900" style="font-family:ui-sans-serif,system-ui,-apple-system,sans-serif; letter-spacing:-0.01em;">Project Summary</h3>
                        <p class="text-xs text-gray-400 mt-0.5 font-medium">{{ $psHeader['project_title'] ?? '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="psm-refresh-btn" id="refreshProjectSummary" type="button" title="Refresh summary">
                        <i class="fi fi-rr-refresh"></i>
                    </button>
                    <button class="psm-modal-close" id="closeProjectSummaryModal" type="button">
                        <i class="fi fi-rr-cross-small"></i>
                    </button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="psm-modal-body" id="projectSummaryModalBody">
                @if(!$ps)
                    <div class="text-center py-12">
                        <i class="fi fi-rr-chart-pie-alt text-gray-300" style="font-size:2rem; display:block; margin-bottom:0.75rem;"></i>
                        <p class="text-sm font-semibold text-gray-500">Summary data not available</p>
                        <span class="text-xs text-gray-400">Could not load project summary at this time</span>
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
            <div class="psm-modal-footer">
                <button type="button" class="psm-modal-cancel" id="cancelProjectSummaryModal">
                    <i class="fi fi-rr-cross-small" style="font-size:0.625rem;"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_MilestoneReport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Allprojects.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerPaymenthistory_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerSendReport_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owner/propertyOwner_Modals/ownerReportHistory_Modal.css') }}">
@endsection

@section('extra_js')
    <script>
        // Pass server-side data to JS
        window.__milestoneReportConfig = {
            projectId: @json($projectId ?? null),
            userId: @json(session('user') ? session('user')->user_id : null),
            csrfToken: '{{ csrf_token() }}',
            payments: @json($payments ?? []),
            disputes: @json($disputes ?? []),
            routes: {
                ownerProjects: '{{ route("owner.projects") }}',
                milestoneProgressReport: '/owner/projects/milestone-progress-report',
                setMilestoneItem: '/owner/projects/set-milestone-item',
            }
        };
    </script>
    <script src="{{ asset('js/owner/propertyOwner_MilestoneReport.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerPaymenthistory_Modal.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerSendReport_Modal.js') }}"></script>
    <script src="{{ asset('js/owner/propertyOwner_Modals/ownerReportHistory_Modal.js') }}"></script>

    <script>
        // Set Dashboard link as active when on milestone report page
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Dashboard' || link.getAttribute('href') === '{{ route("owner.dashboard") }}') {
                    link.classList.add('active');
                }
            });

            // Update navbar search placeholder
            const navbarSearchInput = document.querySelector('.navbar-search-input');
            if (navbarSearchInput) {
                navbarSearchInput.placeholder = 'Search milestones...';
            }
        });
    </script>
@endsection
