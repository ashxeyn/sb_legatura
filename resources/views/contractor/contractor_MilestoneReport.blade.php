@extends('layouts.appContractor')

@section('title', 'Milestone Report - Legatura')

@section('content')
    <div class="contractor-milestone-report min-h-screen bg-gray-50">
        <!-- Header Section -->
        <div class="milestone-report-header bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <!-- Back Button and Breadcrumb -->
                <div class="mb-6">
                    <div class="flex items-center gap-4 mb-3">
                        <a href="{{ route('contractor.myprojects') }}" class="back-button">
                            <i class="fi fi-rr-arrow-left"></i>
                            <span>Back</span>
                        </a>
                        <div class="breadcrumb">
                            <a href="{{ route('contractor.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                            <i class="fi fi-rr-angle-right breadcrumb-separator"></i>
                            <a href="{{ route('contractor.myprojects') }}" class="breadcrumb-link">My Projects</a>
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
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                @php
                    $milestoneItemsData = [];
                    $paymentHistoryData = null;
                @endphp

                @if(isset($project) && isset($milestones) && count($milestones) > 0)
                    @php
                        $milestonePlan = $milestones[0];
                        $milestoneItemsList = $milestonePlan->items ?? [];
                        $totalCost = 0;
                        $pendingApproval = 0;
                        $cumulativePercentage = 0;

                        foreach ($milestoneItemsList as $mi) {
                            $totalCost += floatval($mi->milestone_item_cost ?? 0);
                            $miStatus = $mi->item_status ?? 'pending';
                            if ($miStatus === 'submitted' || $miStatus === 'pending') {
                                $pendingApproval++;
                            }
                        }

                        $projectTotalCost = (isset($milestonePlan->total_project_cost) && floatval($milestonePlan->total_project_cost) > 0)
                            ? floatval($milestonePlan->total_project_cost)
                            : $totalCost;

                        // Build detail data for modals
                        // $milestoneItemsData = []; // Removed: initialized above
                        // $paymentHistoryData = null; // Removed: initialized above

                        if (isset($milestonePlan->items)) {
                            $seqIndex = 0;
                            $previousItemCompleted = true; // First item always unlocked

                            // Determine project-level halted state
                            $isProjectHalted = ($project->project_status ?? '') === 'halt';
                            // Determine milestone-level approved state
                            $milestoneStatus = $milestonePlan->milestone_status ?? 'pending';
                            $isMilestoneApproved = in_array($milestoneStatus, ['approved', 'active', 'completed', 'in_progress']);

                            foreach ($milestonePlan->items as $item) {
                                $seqIndex++;
                                $itemStatus = $item->item_status ?? 'pending';
                                $isCompleted = $itemStatus === 'completed';
                                $isHalted = $itemStatus === 'halt';
                                $isLocked = !$previousItemCompleted && !$isCompleted;
                                $itemCostVal = floatval($item->milestone_item_cost ?? 0);

                                // Compute payment summary for this item
                                $itemPayments = $item->payments ?? collect();
                                $itemTotalPaid = 0;
                                $itemTotalSubmitted = 0;
                                foreach ($itemPayments as $p) {
                                    $pAmount = floatval($p->amount ?? 0);
                                    if (($p->payment_status ?? '') === 'approved')
                                        $itemTotalPaid += $pAmount;
                                    if (($p->payment_status ?? '') === 'submitted')
                                        $itemTotalSubmitted += $pAmount;
                                }
                                $itemRemaining = max(0, $itemCostVal - $itemTotalPaid);

                                $milestoneItemsData[$item->item_id] = [
                                    'id' => $item->item_id,
                                    'milestone_id' => $item->milestone_id,
                                    'sequenceNumber' => $seqIndex,
                                    'title' => $item->milestone_item_title ?? 'Untitled',
                                    'description' => $item->milestone_item_description ?? '',
                                    'status' => $item->item_status ?? 'pending',
                                    'date' => isset($item->date_to_finish) ? date('Y-m-d', strtotime($item->date_to_finish)) : '',
                                    'percentage' => floatval($item->percentage_progress ?? 0),
                                    'cost' => $itemCostVal,
                                    'costFormatted' => '₱' . number_format($itemCostVal, 2),
                                    'attachmentName' => $item->attachment_name ?? null,
                                    'attachmentPath' => $item->attachment_path ?? null,
                                    'progressReports' => collect($item->progress_reports ?? [])->map(function ($pr) {
                                        return [
                                            'id' => $pr->progress_id ?? null,
                                            'title' => $pr->progress_title ?? ($pr->purpose ?? 'Progress Report'),
                                            'description' => $pr->progress_description ?? ($pr->purpose ?? ''),
                                            'status' => $pr->progress_status ?? 'pending',
                                            'date' => isset($pr->submitted_at)
                                                ? \Carbon\Carbon::parse($pr->submitted_at)->format('l, d F Y')
                                                : 'Not specified',
                                            'files' => collect($pr->files ?? [])->map(function ($f) {
                                                return [
                                                    'id' => $f->file_id ?? null,
                                                    'name' => $f->original_name ?? basename($f->file_path),
                                                    'path' => asset('storage/' . ltrim($f->file_path, '/')),
                                                    'rawPath' => ltrim($f->file_path, '/')
                                                ];
                                            })->toArray(),
                                        ];
                                    })->toArray(),
                                    'payments' => collect($itemPayments)->map(function ($p) {
                                        return [
                                            'id' => $p->payment_id ?? null,
                                            'amount' => floatval($p->amount ?? 0),
                                            'amountFormatted' => '₱' . number_format(floatval($p->amount ?? 0), 2),
                                            'type' => ucfirst(str_replace('_', ' ', $p->payment_type ?? 'Payment')),
                                            'transactionNumber' => $p->transaction_number ?? null,
                                            'receiptPhoto' => $p->receipt_photo ?? null,
                                            'date' => isset($p->transaction_date)
                                                ? \Carbon\Carbon::parse($p->transaction_date)->format('l, d F Y')
                                                : 'Not specified',
                                            'status' => $p->payment_status ?? 'submitted',
                                            'reason' => $p->reason ?? null,
                                        ];
                                    })->toArray(),
                                    'paymentSummary' => [
                                        'expected' => $itemCostVal,
                                        'expectedFormatted' => '₱' . number_format($itemCostVal, 2),
                                        'totalPaid' => $itemTotalPaid,
                                        'totalPaidFormatted' => '₱' . number_format($itemTotalPaid, 2),
                                        'totalSubmitted' => $itemTotalSubmitted,
                                        'totalSubmittedFormatted' => '₱' . number_format($itemTotalSubmitted, 2),
                                        'remaining' => $itemRemaining,
                                        'remainingFormatted' => '₱' . number_format($itemRemaining, 2),
                                        'progressPercent' => $itemCostVal > 0 ? min(100, (($itemTotalPaid + $itemTotalSubmitted) / $itemCostVal) * 100) : 0,
                                    ],
                                    'disputeSummary' => $item->dispute_summary ?? ['total' => 0, 'open' => 0, 'resolved' => 0],

                                    // --- Condition flags (matching TSX milestoneDetail logic) ---
                                    'isCompleted' => $isCompleted,
                                    'isPreviousItemComplete' => $previousItemCompleted,
                                    'isLocked' => $isLocked,
                                    'isHalted' => $isHalted,
                                    'isProjectHalted' => $isProjectHalted,
                                    'isMilestoneApproved' => $isMilestoneApproved,
                                    'hasApprovedReport' => collect($item->progress_reports ?? [])->contains(fn($pr) => ($pr->progress_status ?? '') === 'approved'),
                                    'hasActiveReport' => collect($item->progress_reports ?? [])->contains(fn($pr) => ($pr->progress_status ?? '') === 'submitted'),
                                    'latestReportStatus' => optional(collect($item->progress_reports ?? [])->first())->progress_status ?? null,
                                    'latestPaymentStatus' => optional(collect($itemPayments)->first())->payment_status ?? null,
                                    'canSubmitReport' => (
                                        $isMilestoneApproved
                                        && !$isCompleted
                                        && !$isHalted
                                        && !$isProjectHalted
                                        && $previousItemCompleted
                                        && !collect($item->progress_reports ?? [])->contains(fn($pr) => ($pr->progress_status ?? '') === 'submitted')
                                    ),
                                ];

                                // Track for sequential locking: next item is unlocked only if this one is completed
                                $previousItemCompleted = $isCompleted;
                            }
                        }

                        // Build payment history data for the payment modal
                        $paymentEntries = [];
                        $totalPaid = 0;
                        if (isset($allPayments)) {
                            foreach ($allPayments as $payment) {
                                $amount = floatval($payment->amount ?? 0);
                                if (($payment->payment_status ?? '') === 'approved') {
                                    $totalPaid += $amount;
                                }

                                $paymentEntries[] = [
                                    'id' => $payment->payment_id,
                                    'type' => ucfirst(str_replace('_', ' ', $payment->payment_type ?? 'payment')),
                                    'milestoneNumber' => $payment->sequence_order ?? 0,
                                    'milestoneTitle' => $payment->milestone_item_title ?? '',
                                    'amount' => '₱' . number_format($amount, 0),
                                    'date' => $payment->transaction_date
                                        ? \Carbon\Carbon::parse($payment->transaction_date)->format('m/d/Y')
                                        : '',
                                    'time' => $payment->transaction_date
                                        ? \Carbon\Carbon::parse($payment->transaction_date)->format('h:i A')
                                        : '',
                                    'status' => $payment->payment_status ?? 'submitted',
                                    'unread' => (($payment->payment_status ?? '') === 'submitted'),
                                ];
                            }
                        }

                        $paymentHistoryData = [
                            'payments' => $paymentEntries,
                            'summary' => [
                                'totalEstimated' => '₱' . number_format($projectTotalCost, 0),
                                'totalPaid' => $totalPaid,
                                'totalRemaining' => '₱' . number_format(max(0, $projectTotalCost - $totalPaid), 0),
                            ],
                        ];
                    @endphp

                    <div class="milestone-report-content">
                        <!-- Project Header -->
                        <div class="report-header mb-6">
                            <div class="flex items-start justify-between mb-3">
                                <h2 class="text-2xl font-bold text-gray-900">
                                    {{ $project->project_title ?? 'Milestone Report' }}
                                </h2>

                                @if(in_array($milestonePlan->setup_status ?? '', ['not_started', 'rejected', 'submitted']) || in_array($milestonePlan->milestone_status ?? '', ['not_started', 'rejected']))
                                    <button type="button" 
                                            class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 rounded-lg transition-all text-sm font-medium border border-blue-100" 
                                            id="editMilestoneBtn"
                                            title="Edit Milestone Setup"
                                            data-milestone-id="{{ $milestonePlan->milestone_id }}"
                                            data-plan-name="{{ $milestonePlan->milestone_name ?? '' }}"
                                            data-payment-mode="{{ $milestonePlan->payment_mode ?? '' }}"
                                            data-start-date="{{ isset($milestonePlan->start_date) ? date('Y-m-d', strtotime($milestonePlan->start_date)) : '' }}"
                                            data-end-date="{{ isset($milestonePlan->end_date) ? date('Y-m-d', strtotime($milestonePlan->end_date)) : '' }}"
                                            data-total-budget="{{ $projectTotalCost }}"
                                            data-downpayment="{{ $milestonePlan->downpayment_amount ?? 0 }}"
                                    >
                                        <i class="fi fi-rr-pencil"></i>
                                    </button>
                                @endif
                            </div>

                            @if(!empty($project->project_description))
                                <div class="project-description-section mb-6">
                                    <h3 class="description-title">Project Description</h3>
                                    <p class="description-text">{{ $project->project_description }}</p>
                                </div>
                            @endif

                            <div class="project-stats flex gap-4 text-sm text-gray-600 mb-6 pb-6 border-b border-gray-200">
                                @if(!empty($project->project_location))
                                    <span class="location-stat">
                                        <i class="fi fi-rr-marker"></i>
                                        {{ $project->project_location }}
                                    </span>
                                @endif
                                <span><i class="fi fi-rr-calendar"></i> Total Milestones:
                                    {{ count($milestoneItemsList) }}</span>
                                <span><i class="fi fi-rr-clock"></i> Pending Approval: {{ $pendingApproval }}</span>
                                <span><i class="fi fi-rr-money"></i> Total Cost:
                                    ₱{{ number_format($projectTotalCost, 0) }}</span>
                            </div>
                        </div>

                        <!-- Milestone Timeline -->
                        <div class="milestone-timeline-container">
                            <div class="milestone-timeline">
                                @php $itemIndex = 0;
                                $cumulativePercentage = 0; @endphp
                                        @foreach($milestoneItemsList as $item)
                                            @php
                                                $itemIndex++;
                                                $cumulativePercentage += floatval($item->percentage_progress ?? 0);
                                                $isEven = ($itemIndex - 1) % 2 === 0;
                                                $itemCost = floatval($item->milestone_item_cost ?? 0);
                                                $itemStatus = $item->item_status ?? 'pending';
                                                $isItemCompleted = $itemStatus === 'completed';
                                                $isItemHalted = $itemStatus === 'halt';

                                                // Sequential locking (first item always unlocked)
                                                if ($itemIndex === 1) {
                                                    $prevComplete = true;
                                                }
                                                $isItemLocked = !$prevComplete && !$isItemCompleted;

                                                // Node gradient based on state
                                                if ($isItemCompleted) {
                                                    $nodeGradient = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                                                    $nodeIcon = '✓';
                                                } elseif ($isItemHalted) {
                                                    $nodeGradient = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                                                    $nodeIcon = '⏸';
                                                } elseif ($isItemLocked) {
                                                    $nodeGradient = 'linear-gradient(135deg, #9ca3af 0%, #6b7280 100%)';
                                                    $nodeIcon = '🔒';
                                                } else {
                                                    $nodeGradient = 'linear-gradient(135deg, #EEA24B 0%, #F57C00 100%)';
                                                    $nodeIcon = $cumulativePercentage;
                                                }

                                                // Track for next iteration
                                                $prevComplete = $isItemCompleted;
                                            @endphp
                                            <div class="milestone-timeline-item {{ $isEven ? 'milestone-right' : 'milestone-left' }} {{ $isItemLocked ? 'milestone-locked' : '' }} {{ $isItemCompleted ? 'milestone-completed' : '' }} {{ $isItemHalted ? 'milestone-halted' : '' }}"
                                                data-milestone-id="{{ $item->item_id }}"
                                                title="{{ $isItemLocked ? 'Complete the previous milestone first' : 'Click to view progress reports' }}"
                                                style="cursor: {{ $isItemLocked ? 'not-allowed' : 'pointer' }};">
                                                <div class="milestone-node"
                                                    style="background: {{ $nodeGradient }};">
                                                    @if($isItemCompleted)
                                                        <span class="milestone-progress-number" style="font-size: 1rem;">✓</span>
                                                    @elseif($isItemHalted)
                                                        <i class="fi fi-rr-pause" style="color: white; font-size: 0.8rem;"></i>
                                                    @elseif($isItemLocked)
                                                        <i class="fi fi-rr-lock" style="color: white; font-size: 0.8rem;"></i>
                                                    @else
                                                        <span class="milestone-progress-number">{{ $cumulativePercentage }}</span>
                                                    @endif
                                                </div>
                                                <div
                                                    class="milestone-content {{ $isEven ? 'milestone-content-right' : 'milestone-content-left' }}">
                                                    <div class="milestone-number">Milestone {{ $itemIndex }}</div>
                                                    <div class="milestone-title">{{ $item->milestone_item_title ?? 'Untitled' }}</div>
                                                    <div class="milestone-cost">₱{{ number_format($itemCost, 0) }}</div>
                                                    <div class="milestone-percentage">{{ floatval($item->percentage_progress ?? 0) }}%</div>

                                                    @if($isItemCompleted)
                                                        <div class="milestone-status-badge badge-completed">
                                                            <i class="fi fi-rr-check-circle"></i> Completed
                                                        </div>
                                                    @elseif($isItemHalted)
                                                        <div class="milestone-status-badge badge-halted">
                                                            <i class="fi fi-rr-pause-circle"></i> Halted
                                                        </div>
                                                    @elseif($isItemLocked)
                                                        <div class="milestone-status-badge badge-locked">
                                                            <i class="fi fi-rr-lock"></i> Locked
                                                        </div>
                                                    @else
                                                        <div style="font-size: 0.75rem; color: #f97316; margin-top: 0.5rem; font-weight: 600;">
                                                            <i class="fi fi-rr-arrow-right"></i> Click to view details
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach

                                        <!-- Start Point (at bottom) -->
                                        <div class="timeline-start">
                                            <div class="start-node"></div>
                                            <div class="start-label">
                                                <div class="start-text">Start</div>
                                                <div class="start-percentage">0%</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment History Button -->
                                    <div class="payment-history-container">
                                        <button class="payment-history-btn" id="paymentHistoryBtn">
                                            Payment history
                                        </button>
                                    </div>
                                </div>
                            </div>
                @else
                    <div class="text-center py-16">
                        <i class="fi fi-rr-file-chart text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Milestone Data</h3>
                        <p class="text-gray-500">No milestones have been set up for this project yet.</p>
                    </div>
                @endif
                </div>
            </div>
        </div>

        <!-- Payment History Modal -->
        @include('contractor.contractor_Modals.contractorPaymenthistory_Modal')

        <!-- Hidden Timeline Templates for JS to copy -->
        <div id="hidden-timeline-templates" style="display: none;">
            @if(isset($milestones[0]->items))
                @foreach($milestones[0]->items as $item)
                    <div id="timeline_html_{{ $item->item_id }}">
                        @php $reports = collect($item->progress_reports ?? [])->sortByDesc('progress_id')->values()->all();
                        $totalReports = count($reports); @endphp
                        @foreach($reports as $index => $report)
                            @php
                                $status = $report->progress_status ?? 'pending';
                                $statusLabels = ['approved' => 'Approved', 'pending' => 'Pending', 'submitted' => 'Submitted', 'rejected' => 'Rejected'];
                                $statusColors = ['approved' => 'badge-success', 'rejected' => 'badge-error', 'submitted' => 'badge-warning', 'pending' => 'badge-pending'];
                                $isLast = $index === $totalReports - 1;

                                // Status dot classes
                                $dotClass = 'report-dot';
                                if ($status === 'approved')
                                    $dotClass .= ' report-dot-approved';
                                elseif ($status === 'rejected')
                                    $dotClass .= ' report-dot-rejected';
                                else
                                    $dotClass .= ' report-dot-pending';

                                // Status icon
                                $dotIconHtml = '';
                                if ($status === 'approved')
                                    $dotIconHtml = '<i class="fi fi-rr-check"></i>';
                                elseif ($status === 'rejected')
                                    $dotIconHtml = '<i class="fi fi-rr-cross-small"></i>';
                                elseif ($status === 'submitted')
                                    $dotIconHtml = '';
                                else
                                    $dotIconHtml = '<i class="fi fi-rr-time-past"></i>';

                                // Line color
                                $lineClass = 'report-line';
                                if (!$isLast) {
                                    $nextStatus = $reports[$index + 1]->progress_status ?? 'pending';
                                    if ($nextStatus === 'approved')
                                        $lineClass .= ' report-line-approved';
                                    elseif ($nextStatus === 'rejected')
                                        $lineClass .= ' report-line-rejected';
                                    else
                                        $lineClass .= ' report-line-pending';
                                }
                            @endphp
                            <div class="report-timeline-item">
                                <div class="report-timeline-left">
                                    <div class="{{ $dotClass }}">{!! $dotIconHtml !!}</div>
                                    @if(!$isLast) <div class="{{ $lineClass }}"></div> @endif
                                </div>
                                <div class="report-timeline-content">
                                    <div class="report-title-row">
                                        <span class="report-title">{{ $report->progress_title ?? 'Progress Report' }}</span>
                                        <div class="report-actions-wrapper">
                                            <span class="report-status-badge {{ $statusColors[$status] ?? 'badge-pending' }}">
                                                {{ $statusLabels[$status] ?? $status }}
                                            </span>
                                            @if($status === 'submitted')
                                                <!-- Pencil icon for editing, JS will attach listener -->
                                                <button class="edit-progress-btn" data-progress-id="{{ $report->progress_id }}" data-item-id="{{ $item->item_id }}" style="background: #f3f4f6; border: 1px solid #e5e7eb; cursor: pointer; color: #6b7280; font-size: 0.9rem; padding: 6px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: background-color 0.2s;" title="Edit Report" onmouseover="this.style.background='#e5e7eb'; this.style.color='#1f2937'" onmouseout="this.style.background='#f3f4f6'; this.style.color='#6b7280'">
                                                    <i class="fi fi-rr-pencil"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    @if(!empty($report->purpose) || !empty($report->progress_description))
                                        <p class="report-description">{{ $report->progress_description ?? $report->purpose }}</p>
                                    @endif
                                    @if(!empty($report->files))
                                        <div class="report-files-list" style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.35rem;">
                                            @foreach($report->files as $file)
                                                @php
                                                    $viewerUrl = '/contractor/progress/document/view?file=' . urlencode(ltrim($file->file_path, '/')) . '&name=' . urlencode($file->original_name ?? basename($file->file_path));
                                                @endphp
                                                <a href="{{ $viewerUrl }}" target="_blank" class="report-file-link" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #3b82f6; text-decoration: none; padding: 0.25rem 0.5rem; background: #f3f4f6; border-radius: 0.375rem; width: fit-content; border: 1px solid #e5e7eb; transition: background-color 0.2s;">
                                                    <i class="fi fi-rr-clip" style="color: #6b7280;"></i>
                                                    <span>{{ $file->original_name ?? basename($file->file_path) }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    <span class="report-date" style="margin-top: 0.5rem; display: block; color: #6b7280; font-size: 0.875rem;">
                                        {{ isset($report->submitted_at) ? \Carbon\Carbon::parse($report->submitted_at)->format('l, d F Y') : 'Not specified' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="dispute_history_html_{{ $item->item_id }}">
                        @php $disputes = $item->disputes ?? []; @endphp
                        @if(count($disputes) === 0)
                            <div class="empty-history" style="text-align: center; padding: 3rem 1rem; color: #6b7280;">
                                <i class="fi fi-rr-folder-open" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                                <p>No dispute history found for this item.</p>
                            </div>
                        @else
                            @foreach($disputes as $d)
                                @php
                                    $date = isset($d->created_at) ? \Carbon\Carbon::parse($d->created_at)->format('M d, Y h:i A') : '-';
                                    $status = $d->dispute_status ?? 'open';
                                    $statusLabel = ucfirst(str_replace('_', ' ', $status));
                                    $statusClass = 'status-' . $status;
                                @endphp
                                <div class="report-item" data-status="{{ $status }}" style="padding: 1.25rem; border-bottom: 1px solid #f3f4f6;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                        <div>
                                            <span class="report-type-badge" style="background: #f3f4f6; color: #4b5563; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">
                                                {{ $d->dispute_type ?? 'Dispute' }}
                                            </span>
                                            <div style="margin-top: 0.25rem; font-size: 0.75rem; color: #9ca3af;">{{ $date }}</div>
                                        </div>
                                        <span class="dispute-status-pill {{ $statusClass }}" style="font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 100px;">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                    
                                    <p style="font-size: 0.875rem; color: #374151; margin: 0 0 0.75rem 0; line-height: 1.5;">
                                        {{ $d->dispute_desc ?? 'No description provided.' }}
                                    </p>

                                    @if(!empty($d->if_others_distype))
                                        <div style="font-size: 0.8125rem; color: #6b7280; margin-bottom: 0.75rem;"><strong>Specified Issue:</strong> {{ $d->if_others_distype }}</div>
                                    @endif

                                    @if(!empty($d->admin_response))
                                        <div class="admin-response" style="background: #fdf2f8; border-left: 3px solid #db2777; padding: 0.75rem; margin-top: 0.75rem; border-radius: 0 0.375rem 0.375rem 0;">
                                            <div style="font-size: 0.7rem; font-weight: 700; color: #db2777; margin-bottom: 0.25rem; text-transform: uppercase;">Admin Response</div>
                                            <div style="font-size: 0.8125rem; color: #be185d;">{{ $d->admin_response }}</div>
                                        </div>
                                    @endif

                                    @if(isset($d->files) && count($d->files) > 0)
                                        <div class="dispute-files-list" style="margin-top: 0.75rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                            @foreach($d->files as $file)
                                                <a href="{{ asset('storage/' . ltrim($file->storage_path, '/')) }}" target="_blank" class="dispute-file-link" style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; color: #3b82f6; background: #eff6ff; border: 1px solid #dbeafe; padding: 0.25rem 0.5rem; border-radius: 0.375rem; text-decoration: none;">
                                                    <i class="fi fi-rr-clip" style="font-size: 0.7rem;"></i>
                                                    <span>{{ $file->original_name ?? basename($file->storage_path) }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        <!-- View Progress Report Modal -->
        @include('contractor.contractor_Modals.contractorMilestoneprogressReport_Modal')


        <script>
            // Data for JS modal interactions only (not page rendering)
            window.milestoneItemsData = @json($milestoneItemsData);
            window.paymentHistoryData = @json($paymentHistoryData);
            window.projectTitle = @json($project->project_title ?? 'Project');
            window.currentProjectId = @json($project->project_id ?? 0);
        </script>
    <!-- View Progress Report Modal (Detail modal for milestone item) -->
    @include('contractor.contractor_Modals.contractorMilestoneprogressReport_Modal')

    <!-- Contractor Dispute Modal -->
    @include('contractor.contractor_Modals.contractorDisputesSendreport_Modal')

    <!-- Contractor Report History Modal -->
    @include('contractor.contractor_Modals.contractorDisputesReporthistory_Modal')

    <!-- Progress Report Modal (for submitting) -->
    @include('contractor.contractor_Modals.contractorProgressreport_Modal')

    <!-- Milestone Setup Modal (for editing) -->
    @include('contractor.contractor_Modals.contractorMilestonesetup_modal')

    <!-- Payment Action Modals -->
    @include('contractor.contractor_Modals.contractorPaymentapprove_Modal')
    @include('contractor.contractor_Modals.contractorPaymentreject_Modal')
@endsection

@section('extra_css')
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_MilestoneReport.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Myprojects.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorMilestonesetup_modal.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorPaymenthistory_Modal.css') }}">
    <link rel="stylesheet"
        href="{{ asset('css/contractor/contractor_Modals/contractorMilestoneprogressReport_Modal.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorProgressreport_Modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorDisputes.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/contractor/contractor_Modals/contractorDisputesHistory.css') }}?v={{ time() }}">
@endsection

@section('extra_js')
    <script src="{{ asset('js/contractor/contractor_MilestoneReport.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorMilestonesetup_modal.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorMilestoneprogressReport_Modal.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorProgressreport_Modal.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorDisputes.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorPaymenthistory_Modal.js') }}"></script>
    <script src="{{ asset('js/contractor/contractor_Modals/contractorPaymentvalidation_Modal.js') }}"></script>
    <script>
        // Set Dashboard link as active when on milestone report page
        document.addEventListener('DOMContentLoaded', () => {
            const navbarLinks = document.querySelectorAll('.navbar-link');
            navbarLinks.forEach(link => {
                link.classList.remove('active');
                if (link.textContent.trim() === 'Dashboard' || link.getAttribute('href') === '{{ route("contractor.dashboard") }}') {
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