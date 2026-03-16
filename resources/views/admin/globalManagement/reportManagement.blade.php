<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Report Management - Legatura Admin</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo2.0-favicon.svg') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">

    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

    <style>
        html {
            scrollbar-gutter: stable;
        }

        .report-tabs-shell {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem;
            border-radius: 0.9rem;
            border: 1px solid #dbeafe;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 12px 30px rgba(79, 70, 229, 0.12);
            backdrop-filter: blur(2px);
        }

        .tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.75rem;
            border-radius: 0.75rem;
            border: 1px solid transparent;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .tab-btn:hover {
            border-color: #c7d2fe;
            background: #eef2ff;
            color: #3730a3;
        }

        .tab-btn.active {
            color: #ffffff;
            border-color: #4f46e5;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 52%, #8b5cf6 100%);
            box-shadow: 0 8px 18px rgba(79, 70, 229, 0.28);
        }

        .tab-btn .tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.3rem;
            padding: 0.15rem 0.35rem;
            border-radius: 9999px;
            border: 1px solid #c7d2fe;
            background: #e0e7ff;
            color: #4338ca;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
        }

        .tab-btn.active .tab-count {
            border-color: rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .report-filter-surface {
            border-color: #dbeafe;
            background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%);
            box-shadow: 0 10px 26px rgba(30, 64, 175, 0.08);
        }

        /* Modal: hide scrollbar, match proofOfpayments */
        .modal-scroll-hidden {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .modal-scroll-hidden::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }

        .report-view-modal {
            --report-modal-from: #4f46e5;
            --report-modal-to: #7c3aed;
            --report-modal-border: #c7d2fe;
            --report-modal-soft: #eef2ff;
            --report-modal-soft-text: #dbeafe;
            --report-modal-accent: #4f46e5;
            --report-modal-footer: #f8faff;
            --report-modal-header-border: rgba(255, 255, 255, 0.18);
            border-color: var(--report-modal-border) !important;
        }

        .report-view-modal[data-status-theme="pending"] {
            --report-modal-from: #d97706;
            --report-modal-to: #f59e0b;
            --report-modal-border: #fcd34d;
            --report-modal-soft: #fff7ed;
            --report-modal-soft-text: #fde68a;
            --report-modal-accent: #d97706;
            --report-modal-footer: #fffbeb;
        }

        .report-view-modal[data-status-theme="under_review"] {
            --report-modal-from: #2563eb;
            --report-modal-to: #4f46e5;
            --report-modal-border: #93c5fd;
            --report-modal-soft: #eff6ff;
            --report-modal-soft-text: #dbeafe;
            --report-modal-accent: #2563eb;
            --report-modal-footer: #f8fbff;
        }

        .report-view-modal[data-status-theme="resolved"] {
            --report-modal-from: #059669;
            --report-modal-to: #14b8a6;
            --report-modal-border: #a7f3d0;
            --report-modal-soft: #ecfdf5;
            --report-modal-soft-text: #d1fae5;
            --report-modal-accent: #059669;
            --report-modal-footer: #f0fdf4;
        }

        .report-view-modal[data-status-theme="dismissed"] {
            --report-modal-from: #dc2626;
            --report-modal-to: #f43f5e;
            --report-modal-border: #fecaca;
            --report-modal-soft: #fef2f2;
            --report-modal-soft-text: #fecdd3;
            --report-modal-accent: #dc2626;
            --report-modal-footer: #fff1f2;
        }

        .report-view-modal-header {
            background: linear-gradient(135deg, var(--report-modal-from) 0%, var(--report-modal-to) 100%);
            border-bottom-color: var(--report-modal-header-border) !important;
        }

        #modalCaseId {
            color: var(--report-modal-soft-text);
        }

        .report-view-modal-body {
            background: linear-gradient(180deg, var(--report-modal-soft) 0%, #ffffff 26%);
        }

        .report-view-modal-body > .grid:first-child {
            padding: 0.9rem;
            border: 1px solid var(--report-modal-border);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.92);
        }

        .report-view-modal-body > .rounded-xl.border,
        .report-view-modal-body > #disputeWorkflowSection #disputeWorkflowBanner,
        .report-view-modal-body > #disputeWorkflowSection #disputeInformationHierarchy,
        .report-view-modal-body > #disputeWorkflowSection > .bg-gradient-to-br,
        .report-view-modal-body > #modalAdminNotesWrap > .bg-gray-50 {
            border-color: var(--report-modal-border) !important;
            background: linear-gradient(180deg, #ffffff 0%, var(--report-modal-soft) 140%) !important;
        }

        .report-view-modal-body > .border-t,
        .report-view-modal-body > #disputeWorkflowSection,
        .report-view-modal-body > #modalAdminNotesWrap,
        .report-view-modal-body > #disputeWorkflowSection .border-t {
            border-color: var(--report-modal-border) !important;
        }

        .report-view-modal-body > .border-t h4 i,
        .report-view-modal-body > #disputeWorkflowSection h4 i,
        .report-view-modal #modalActionsFooter > div:first-child > i {
            color: var(--report-modal-accent) !important;
        }

        .report-view-modal #modalActionsFooter button i {
            color: inherit !important;
        }

        .report-view-modal #modalActionsFooter {
            background: #ffffff;
            border-top-color: var(--report-modal-border) !important;
        }
    </style>

    <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
    <div class="flex min-h-screen">

        @include('admin.layouts.sidebar')

        <main class="flex-1">
            @include('admin.layouts.topnav', ['pageTitle' => 'Report Management'])

            <section class="px-8 py-8 space-y-8">

                {{-- ── Stat Cards ── --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Total Reports</p>
                                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number" id="statTotal">{{ $counts['total'] }}</h2>
                                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-blue-100 mt-2 w-fit">
                                    <i class="fi fi-sr-database text-[10px] text-blue-600"></i>
                                    <span class="text-[11px] font-semibold text-blue-600">All records</span>
                                </div>
                            </div>
                            <div class="stat-icon-wrap bg-blue-100 p-2.5 rounded-lg"><i class="fi fi-sr-file-invoice text-lg text-blue-600"></i></div>
                        </div>
                        <p class="text-[11px] text-gray-400">All time</p>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Pending</p>
                                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number" id="statPending">{{ $counts['pending'] }}</h2>
                                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-orange-100 mt-2 w-fit">
                                    <i class="fi fi-sr-time-check text-[10px] text-orange-600"></i>
                                    <span class="text-[11px] font-semibold text-orange-600">Awaiting review</span>
                                </div>
                            </div>
                            <div class="stat-icon-wrap bg-orange-100 p-2.5 rounded-lg"><i class="fi fi-sr-hourglass-end text-lg text-amber-600"></i></div>
                        </div>
                        <p class="text-[11px] text-gray-400">Unresolved cases</p>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Under Review</p>
                                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number" id="statUnderReview">{{ $counts['under_review'] }}</h2>
                                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-red-100 mt-2 w-fit">
                                    <i class="fi fi-sr-shield-exclamation text-[10px] text-red-600"></i>
                                    <span class="text-[11px] font-semibold text-red-600">In progress</span>
                                </div>
                            </div>
                            <div class="stat-icon-wrap bg-red-100 p-2.5 rounded-lg"><i class="fi fi-sr-shield-exclamation text-lg text-red-600"></i></div>
                        </div>
                        <p class="text-[11px] text-gray-400">Active status</p>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Resolved / Dismissed</p>
                                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number" id="statResolved">{{ $counts['resolved'] }}</h2>
                                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-green-100 mt-2 w-fit">
                                    <i class="fi fi-sr-check-circle text-[10px] text-green-600"></i>
                                    <span class="text-[11px] font-semibold text-green-600">Closed cases</span>
                                </div>
                            </div>
                            <div class="stat-icon-wrap bg-green-100 p-2.5 rounded-lg"><i class="fi fi-sr-check-circle text-lg text-green-600"></i></div>
                        </div>
                        <p class="text-[11px] text-gray-400">Completed status</p>
                    </div>

                </div>

                {{-- ── Tab Navigation & Content ── --}}
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 via-blue-50 to-purple-50">
                        <div class="report-tabs-shell">
                            <button class="tab-btn active" data-tab="moderationHub" id="tabModerationHub">
                                <i class="fi fi-rr-shield-check"></i>
                                <span>Submitted Cases</span>
                                <span class="tab-count">{{ $counts['total'] }}</span>
                            </button>
                            <button class="tab-btn" data-tab="adminAction" id="tabAdminAction">
                                <i class="fi fi-rr-megaphone"></i>
                                <span>Direct Admin Action</span>
                            </button>
                        </div>
                    </div>

                    {{-- ── Tab 1: Active Moderation ── --}}
                    <div id="panelModerationHub" class="tab-panel">
                        {{-- Header with Filters --}}
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                                <h3 class="text-lg font-semibold text-gray-800">Active Reports</h3>
                                <p class="text-[11px] font-medium text-gray-500">Review and moderate submitted cases.</p>
                            </div>
                            <div class="controls-wrapper report-filter-surface rounded-xl border p-4 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                                        <i class="fi fi-rr-filter text-gray-500"></i>
                                        <span>Filter By</span>
                                    </div>

                                    {{-- Source Type Filter --}}
                                    <div class="relative">
                                        <select id="filterSource" class="appearance-none bg-white border border-indigo-200 rounded-lg px-3 py-2 pr-8 text-xs font-medium text-gray-700 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition cursor-pointer shadow-sm">
                                            <option value="all">All</option>
                                            <option value="project">Project</option>
                                            <option value="showcase">Showcase</option>
                                            <option value="review">Review</option>
                                            <option value="user">User</option>
                                            <option value="dispute">Dispute</option>
                                        </select>
                                        <i class="fi fi-rr-angle-small-down absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[11px]"></i>
                                    </div>

                                    {{-- Case Type Filter --}}
                                    <div class="relative">
                                        <select id="filterCaseType" class="appearance-none bg-white border border-indigo-200 rounded-lg px-3 py-2 pr-8 text-xs font-medium text-gray-700 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition cursor-pointer shadow-sm">
                                            <option value="all">All</option>
                                            <option value="report">Report</option>
                                            <option value="dispute">Dispute</option>
                                        </select>
                                        <i class="fi fi-rr-angle-small-down absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[11px]"></i>
                                    </div>

                                    {{-- Status Filter --}}
                                    <div class="flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                                        <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                                            <i class="fi fi-rr-badge-check text-white text-xs leading-none"></i>
                                            <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">Status</span>
                                        </div>
                                        <select id="filterStatus" class="bg-white text-xs text-gray-700 font-medium px-2.5 py-2 focus:outline-none cursor-pointer border-0">
                                            <option value="all">All</option>
                                            <option value="pending">Pending</option>
                                            <option value="under_review">Under Review</option>
                                            <option value="resolved">Resolved</option>
                                            <option value="dismissed">Dismissed</option>
                                        </select>
                                    </div>

                                    {{-- Search Filter --}}
                                    <div class="relative">
                                        <input type="text" id="filterSearch" placeholder="Search reports..."
                                            class="appearance-none bg-white border border-indigo-200 rounded-lg px-3 py-2 pl-8 text-xs font-medium text-gray-700 w-44 placeholder-gray-400 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition">
                                        <i class="fi fi-rr-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[11px]"></i>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button id="resetFilters" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                                        <i class="fi fi-rr-rotate-left"></i>
                                        <span>Reset Filter</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Reports Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed">
                                <thead>
                                    <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[9%]">Case ID</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[7%]">Case Type</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[8%]">Source</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Reporter</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[13%]">Target</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[15%]">Reason / Subject</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">Status</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">Admin Action</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">Date Submitted</th>
                                        <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[6%]">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="reportsTableBody" class="divide-y divide-gray-200">
                                    @forelse ($reports as $report)
                                        <tr class="hover:bg-indigo-50/60 transition-colors"
                                            data-id="{{ $report->case_ref_id }}"
                                            data-source="{{ $report->source }}"
                                            data-status="{{ $report->status }}">
                                            <td class="px-2.5 py-2.5 text-[11px] font-mono text-gray-700 truncate" title="{{ $report->case_id }}">{{ $report->case_id }}</td>
                                            <td class="px-2.5 py-2.5">
                                                @php
                                                    $caseColors = ['report' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'dispute' => 'bg-orange-100 text-orange-700 border-orange-200'];
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $caseColors[$report->case_type] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">{{ ucfirst($report->case_type) }}</span>
                                            </td>
                                            <td class="px-2.5 py-2.5">
                                                @php
                                                    $srcColors = ['project' => 'bg-blue-100 text-blue-700 border-blue-200', 'showcase' => 'bg-teal-100 text-teal-700 border-teal-200', 'review' => 'bg-purple-100 text-purple-700 border-purple-200', 'user' => 'bg-cyan-100 text-cyan-700 border-cyan-200', 'dispute' => 'bg-orange-100 text-orange-700 border-orange-200'];
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $srcColors[$report->source_type] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">{{ ucfirst(str_replace('_', ' ', $report->source_type)) }}</span>
                                            </td>
                                            <td class="px-2.5 py-2.5 text-[11px] text-gray-700 font-medium truncate" title="{{ $report->reporter ?? '' }}">{{ $report->reporter ?? '-' }}</td>
                                            <td class="px-2.5 py-2.5 text-[11px] text-gray-600 truncate" title="{{ $report->target }}">{{ $report->target }}</td>
                                            <td class="px-2.5 py-2.5 text-[11px] text-gray-600 truncate" title="{{ $report->reason }}">{{ $report->reason }}</td>
                                            <td class="px-2.5 py-2.5">
                                                @php
                                                    $statColors = ['pending' => 'bg-amber-100 text-amber-700 border-amber-200', 'under_review' => 'bg-blue-100 text-blue-700 border-blue-200', 'resolved' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'dismissed' => 'bg-red-100 text-red-700 border-red-200'];
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $statColors[$report->status] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">{{ strtoupper(str_replace('_', ' ', $report->status)) }}</span>
                                            </td>
                                            <td class="px-2.5 py-2.5">
                                                @php
                                                    $action = $report->admin_action ?? null;
                                                    $actionColors = [
                                                        'warned' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                        'terminated' => 'bg-red-100 text-red-700 border-red-200',
                                                        'halted' => 'bg-rose-100 text-rose-700 border-rose-200',
                                                        'suspended' => 'bg-red-100 text-red-700 border-red-200',
                                                        'resumed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                    ];
                                                    $actionKey = $action ? strtolower(str_replace(' ', '_', trim($action))) : '';
                                                    $actionCls = $actionColors[$actionKey] ?? 'bg-gray-100 text-gray-500 border-gray-200';
                                                    $actionLabel = $action ? ucfirst($action) : '—';
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $actionCls }}" title="{{ $report->admin_action ?? '' }}">{{ $actionLabel }}</span>
                                            </td>
                                            <td class="px-2.5 py-2.5 text-[11px] text-gray-500 whitespace-nowrap">{{ $report->created_at ? \Carbon\Carbon::parse($report->created_at)->format('M j, Y') : '-' }}</td>
                                            <td class="px-2.5 py-2.5 text-center">
                                                <button class="action-btn view-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all view-report-btn"
                                                    title="View"
                                                    data-id="{{ $report->case_ref_id }}"
                                                    data-source="{{ $report->source }}"
                                                    data-case-type="{{ $report->case_type }}">
                                                    <i class="fi fi-rr-eye text-[13px] leading-none"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                                                <i class="fi fi-sr-file-invoice text-3xl block mb-2"></i>
                                                <p class="text-base font-medium text-gray-500">No moderation cases found</p>
                                                <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div id="moderationPaginationBar" class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
                            <p id="moderationPaginationInfo" class="text-xs text-gray-500"></p>
                            <div class="flex items-center gap-1">
                                <button id="moderationPrevPage" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition disabled:text-gray-400 disabled:cursor-not-allowed" disabled>‹ Prev</button>
                                <span id="moderationPageIndicator" class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold"></span>
                                <button id="moderationNextPage" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition disabled:text-gray-400 disabled:cursor-not-allowed" disabled>Next ›</button>
                            </div>
                        </div>
                    </div>

                    {{-- ── Tab 2: Report History ── --}}
                    <div id="panelReportHistory" class="tab-panel hidden">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Report Activity Overview</h3>
                            <select id="historyFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white font-medium text-gray-700">
                                <option value="all">All Reporters</option>
                                <option value="super">Super Reporters (10+)</option>
                                <option value="abusers">Potential Abusers (50%+ dismissed)</option>
                            </select>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed">
                                <thead class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Reporter</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Total Reports</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Pending</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Resolved</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Dismissed</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Dismiss Rate</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Sources</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Flags</th>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Latest Report</th>
                                    </tr>
                                </thead>
                                <tbody id="reporterStatsBody" class="divide-y divide-gray-200">
                                    @forelse ($reporterStats as $stat)
                                        <tr class="hover:bg-indigo-50/60 transition-colors reporter-row"
                                            data-super="{{ $stat->is_super_reporter ? '1' : '0' }}"
                                            data-abuser="{{ $stat->is_potential_abuser ? '1' : '0' }}">
                                            <td class="px-2.5 py-2.5 text-xs text-gray-700 font-medium">{{ $stat->reporter_username }}</td>
                                            <td class="px-2.5 py-2.5 text-xs text-gray-800 font-bold">{{ $stat->total_reports }}</td>
                                            <td class="px-2.5 py-2.5 text-xs text-amber-600 font-medium">{{ $stat->pending_count }}</td>
                                            <td class="px-2.5 py-2.5 text-xs text-emerald-600 font-medium">{{ $stat->resolved_count }}</td>
                                            <td class="px-2.5 py-2.5 text-xs text-red-600 font-medium">{{ $stat->dismissed_count }}</td>
                                            <td class="px-2.5 py-2.5">
                                                @php $rateColor = $stat->dismiss_rate >= 50 ? 'text-red-700 bg-red-100 border-red-200' : ($stat->dismiss_rate >= 25 ? 'text-amber-700 bg-amber-100 border-amber-200' : 'text-emerald-700 bg-emerald-100 border-emerald-200'); @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $rateColor }}">{{ $stat->dismiss_rate }}%</span>
                                            </td>
                                            <td class="px-2.5 py-2.5">
                                                <div class="flex gap-1 flex-wrap">
                                                    @foreach ($stat->sources as $src)
                                                        @php $srcC = ['post' => 'bg-blue-50 text-blue-600 border-blue-200', 'review' => 'bg-purple-50 text-purple-600 border-purple-200', 'content' => 'bg-teal-50 text-teal-600 border-teal-200', 'dispute' => 'bg-orange-50 text-orange-600 border-orange-200']; @endphp
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium border {{ $srcC[$src] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">{{ ucfirst($src) }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-2.5 py-2.5">
                                                <div class="flex gap-1 flex-wrap">
                                                    @if ($stat->is_super_reporter)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">Super Reporter</span>
                                                    @endif
                                                    @if ($stat->is_potential_abuser)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 border border-red-200">Potential Abuser</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-2.5 py-2.5 text-[11px] text-gray-500 whitespace-nowrap">{{ $stat->latest_report ? \Carbon\Carbon::parse($stat->latest_report)->format('F j, Y') : '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-4 py-10 text-center text-gray-500 text-xs">No reporter data available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @php
                            $statsTotal = count($reporterStats ?? []);
                            $statsPerPage = 10;
                            $statsPage = 1;
                            $statsLastPage = ceil($statsTotal / $statsPerPage);
                            $statsFrom = $statsTotal > 0 ? 1 : 0;
                            $statsTo = $statsTotal > 0 ? min($statsPerPage, $statsTotal) : 0;
                        @endphp

                        <div id="reporterStatsPaginationBar" class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2 {{ $statsTotal > 0 ? '' : 'hidden' }}">
                            <p id="reporterStatsPaginationInfo" class="text-xs text-gray-500">
                                @if($statsTotal > 0)
                                    Showing <strong id="statsFrom">{{ $statsFrom }}</strong>–<strong id="statsTo">{{ $statsTo }}</strong> of <strong id="statsTotal">{{ $statsTotal }}</strong> reporters
                                @else
                                    Showing <strong>0</strong> reporters
                                @endif
                            </p>
                            <div class="flex items-center gap-1">
                                <button id="reporterStatsPrevPage" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition disabled:text-gray-400 disabled:cursor-not-allowed" disabled>‹ Prev</button>
                                <span id="reporterStatsPageIndicator" class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">Page 1 of {{ $statsLastPage }}</span>
                                <button id="reporterStatsNextPage" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition disabled:text-gray-400 disabled:cursor-not-allowed" {{ $statsLastPage <= 1 ? 'disabled' : '' }}>Next ›</button>
                            </div>
                        </div>
                    </div>

                    {{-- ── Tab 3: Direct Admin Action ── --}}
                    <div id="panelAdminAction" class="tab-panel hidden">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Direct Admin Action</h3>
                            <div class="flex flex-wrap items-center gap-3">
                                <select id="adminSearchType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white font-medium text-gray-700 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <option value="user">Users</option>
                                    <option value="showcase">Showcase Posts</option>
                                    <option value="project">Project Posts</option>
                                    <option value="review">Reviews</option>
                                </select>

                                <div class="relative flex-1 min-w-[250px]">
                                    <input type="text" id="adminSearchInput" placeholder="Search by name, username, email, title, or content..." class="w-full px-3 py-2 pl-9 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <i class="fi fi-rr-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                </div>

                                <button id="adminSearchBtn" class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-sm font-semibold shadow-sm hover:shadow-md transition">
                                    <i class="fi fi-rr-search mr-1"></i> Search
                                </button>
                            </div>
                        </div>

                        {{-- Search Results --}}
                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed" id="adminSearchTable">
                                <thead class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200" id="adminSearchThead">
                                    <tr>
                                        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider" colspan="6">
                                            <span class="text-gray-400">Browse or search users, posts, or reviews below.</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="adminSearchBody" class="divide-y divide-gray-200">
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">
                                            <i class="fi fi-rr-loading-spinner text-3xl block mb-2 text-gray-300 animate-spin"></i>
                                            Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div id="adminPaginationBar" class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2 hidden">
                            <p id="adminPaginationInfo" class="text-xs text-gray-500"></p>
                            <div class="flex items-center gap-1">
                                <button id="adminPrevPage" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition disabled:text-gray-400 disabled:cursor-not-allowed" disabled>‹ Prev</button>
                                <span id="adminPageIndicator" class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold"></span>
                                <button id="adminNextPage" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition disabled:text-gray-400 disabled:cursor-not-allowed" disabled>Next ›</button>
                            </div>
                        </div>
                    </div>

                </div>

            </section>

            {{-- ════════════════════════════════════════════════════════════
                 VIEW REPORT MODAL — Evidence + Dismiss / Confirm
                 ════════════════════════════════════════════════════════════ --}}
            <div id="viewReportModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="modal-content report-view-modal relative bg-white w-full max-w-3xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[78vh] flex flex-col" data-status-theme="default">
                    <div class="report-view-modal-header flex items-center justify-between px-4 py-3.5 border-b text-white flex-shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow"><i class="fi fi-sr-document text-white text-base"></i></div>
                            <div>
                                <h3 class="text-[15px] font-bold text-white leading-tight">Case Details</h3>
                                <p class="text-[10px]" id="modalCaseId">Case #---</p>
                            </div>
                        </div>
                        <button class="modal-close p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
                    </div>

                    <div class="report-view-modal-body modal-scroll-hidden flex-1 overflow-y-auto p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-2">
                                <div><label class="text-[10px] font-semibold text-gray-500 uppercase">Source</label><p class="text-[12px] font-semibold text-gray-800" id="modalSource">-</p></div>
                                <div><label class="text-[10px] font-semibold text-gray-500 uppercase">Reporter</label><p class="text-[12px] font-semibold text-gray-800" id="modalReporter">-</p></div>
                                <div><label class="text-[10px] font-semibold text-gray-500 uppercase">Reason</label><p class="text-[12px] font-semibold text-gray-800" id="modalReason">-</p></div>
                            </div>
                            <div class="space-y-2">
                                <div><label class="text-[10px] font-semibold text-gray-500 uppercase">Content Type</label><p class="text-[12px] font-semibold text-gray-800" id="modalContentType">-</p></div>
                                <div><label class="text-[10px] font-semibold text-gray-500 uppercase">Date Filed</label><p class="text-[12px] font-semibold text-gray-800" id="modalDate">-</p></div>
                                <div><label class="text-[10px] font-semibold text-gray-500 uppercase">Status</label><div id="modalStatus"></div></div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 p-3.5">
                            <label class="text-[10px] font-semibold text-gray-500 uppercase block mb-1.5">Case Description</label>
                            <p class="text-[12px] text-gray-700 leading-relaxed whitespace-pre-line" id="modalDetails">-</p>
                        </div>

                        <div class="border-t border-gray-200 pt-3">
                            <h4 class="text-[13px] font-bold text-gray-800 mb-2.5 flex items-center gap-2"><i class="fi fi-sr-folder-open text-indigo-600 text-sm"></i>Evidence</h4>
                            <div id="evidenceContainer" class="space-y-2.5">
                                <div class="text-center text-gray-400 text-[12px] py-3">Loading evidence...</div>
                            </div>
                        </div>

                        <div id="disputeWorkflowSection" class="hidden border-t border-gray-200 pt-3">
                            <h4 class="text-[13px] font-bold text-gray-800 mb-2.5 flex items-center gap-2"><i class="fi fi-sr-briefcase text-indigo-600 text-sm"></i>Dispute Workflow</h4>
                            <div id="disputeWorkflowBanner" class="rounded-xl border px-3.5 py-2.5 bg-gray-50 border-gray-200 mb-3">
                                <p class="text-[12px] font-semibold text-gray-800" id="disputeWorkflowTitle">Case Workflow</p>
                                <p class="text-[10px] text-gray-600 mt-0.5" id="disputeWorkflowMessage">Follow the dispute workflow steps to complete this case.</p>
                            </div>
                            <div id="disputeInformationHierarchy" class="rounded-xl border border-gray-200 p-3.5 mb-3">
                                <h5 class="text-[11px] font-bold text-gray-700 uppercase mb-2">Dispute Information</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Subject</label><p class="text-[12px] text-gray-800" id="disputeSubjectField">-</p></div>
                                    <div><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Requested Action</label><p class="text-[12px] text-gray-800" id="disputeRequestedActionField">-</p></div>
                                </div>
                                <div class="mt-3"><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Description</label><p class="text-[12px] text-gray-700 whitespace-pre-line" id="disputeDescriptionField">-</p></div>
                                <div class="mt-3"><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-1">Initial Proofs</label><div id="disputeInitialProofsList" class="space-y-1.5 text-[12px] text-gray-700"></div></div>
                                <div class="mt-3 border-t border-gray-200 pt-3"><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-1">Resubmitted Report Panel</label><div id="disputeResubmittedPanel" class="space-y-1.5 text-[12px] text-gray-700"></div></div>
                            </div>
                            <div class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl border border-gray-200 p-3.5 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Related Project</label><p class="text-[12px] font-semibold text-gray-800" id="disputeProjectTitle">-</p><p class="text-[10px] text-gray-500" id="disputeProjectId">-</p></div>
                                    <div><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Project Status</label><p class="text-[12px] font-semibold text-gray-800" id="disputeProjectStatus">-</p></div>
                                    <div><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Property Owner</label><p class="text-[12px] text-gray-700" id="disputeProjectOwner">-</p></div>
                                    <div><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Contractor</label><p class="text-[12px] text-gray-700" id="disputeProjectContractor">-</p></div>
                                    <div><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Budget</label><p class="text-[12px] text-gray-700" id="disputeProjectBudget">-</p></div>
                                    <div><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Timeline</label><p class="text-[12px] text-gray-700" id="disputeProjectTimeline">-</p></div>
                                    <div><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Required Action</label><p class="text-[12px] text-gray-700" id="disputeRequiredAction">-</p></div>
                                    <div><label class="text-[10px] font-semibold text-gray-500 uppercase block mb-0.5">Action State</label><p class="text-[12px] text-gray-700" id="disputeActionState">-</p></div>
                                </div>
                                <div id="disputeProjectActionForm" class="hidden border-t border-gray-200 pt-3 space-y-2">
                                    <p class="text-[12px] text-gray-700">This action will halt the project, resolve the dispute automatically, and record <span class="font-semibold">Admin Action: Halted</span>.</p>
                                    <div class="flex items-center justify-end"><button id="btnApplyDisputeProjectAction" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-[12px] font-semibold transition">Halt Project</button></div>
                                </div>
                                <div id="disputeResolvedProjectActions" class="hidden border-t border-gray-200 pt-3 space-y-2">
                                    <label class="text-[10px] font-semibold text-gray-500 uppercase block">Post-Resolution Project Decision</label>
                                    <p class="text-[12px] text-gray-700">This dispute is resolved and the project is halted. Choose the next project action.</p>
                                    <div class="flex items-center justify-end gap-2">
                                        <button id="btnResumeDisputeProject" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-[12px] font-semibold transition">Resume Project</button>
                                        <button id="btnTerminateDisputeProject" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-[12px] font-semibold transition">Terminate Project</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="modalAdminNotesWrap" class="hidden border-t border-gray-200 pt-3">
                            <label class="text-[10px] font-semibold text-gray-500 uppercase block mb-1.5">Admin Resolution Notes</label>
                            <div class="bg-gray-50 rounded-xl border border-gray-200 p-3.5"><p class="text-[12px] text-gray-700" id="modalAdminNotes">-</p></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 px-4 py-3 bg-gray-50 border-t flex-shrink-0" id="modalActionsFooter">
                        <div class="flex items-center gap-1.5 text-[10px] text-gray-500"><i class="fi fi-rr-shield-check text-indigo-500"></i><span>All actions are logged.</span></div>
                        <div class="flex items-center gap-2">
                            <button class="modal-close px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-50 transition">Close</button>
                            <div id="modalActionBtns" class="flex items-center gap-2">
                                <button id="btnUnderReviewReport" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-search mr-1"></i>Under Review</button>
                                <button id="btnDismissReport" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-cross-small mr-1"></i>Dismiss</button>
                                <button id="btnConfirmReport" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-check mr-1"></i>Approve</button>
                            </div>
                            <div id="modalDisputeActionBtns" class="hidden items-center gap-2">
                                <button id="btnReviewDispute" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-eye mr-1"></i>Under Review</button>
                                <button id="btnRejectDispute" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-cross-small mr-1"></i>Dismiss</button>
                                <button id="btnResolveDispute" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-check mr-1"></i>Approve</button>
                            </div>
                            <div id="modalDirectActionBtns" class="hidden items-center gap-2">
                                <button id="btnDirectHide" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-eye-crossed mr-1"></i>Hide</button>
                                <button id="btnDirectUnhide" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-eye mr-1"></i>Unhide</button>
                                <button id="btnDirectRemoveReview" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-[12px] font-semibold transition hidden"><i class="fi fi-rr-trash mr-1"></i>Remove Review</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 DISPUTE PROJECT DECISION MODAL (Terminate / Resume)
                 ════════════════════════════════════════════════════════════ --}}
            <div id="disputeProjectDecisionModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="modal-content relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div id="disputeProjectDecisionHeader" class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-red-600 to-rose-600 border-b border-red-600 text-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow"><i class="fi fi-sr-briefcase text-white text-base"></i></div>
                            <h3 class="text-[15px] font-bold text-white leading-tight" id="disputeProjectDecisionTitle">Confirm Project Decision</h3>
                        </div>
                        <button class="modal-close p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div id="disputeProjectDecisionBanner" class="flex items-start gap-3 p-3 bg-red-50 border-l-4 border-red-500 rounded-lg">
                            <i class="fi fi-rr-info-circle text-red-600 text-base mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-[12px] text-red-900 font-semibold mb-0.5" id="disputeProjectDecisionLabel">Proceed with selected project decision</p>
                                <p class="text-[10px] text-red-800">This action is final and will be logged in the admin audit trail.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Admin Reason *</label>
                            <textarea id="disputeProjectDecisionReason" rows="3" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Provide reason for this decision..."></textarea>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Project Remarks (Optional)</label>
                            <textarea id="disputeProjectDecisionRemarks" rows="2" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Optional internal remarks..."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                            <button class="modal-close px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmDisputeProjectDecisionBtn" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-[12px] font-semibold transition">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 DISPUTE WARNING MODAL (NON-HALT TYPES)
                 ════════════════════════════════════════════════════════════ --}}
            <div id="disputeWarningModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="modal-content relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 border-b border-emerald-600 text-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow"><i class="fi fi-sr-bell text-white text-base"></i></div>
                            <h3 class="text-[15px] font-bold text-white leading-tight">Warn Reported User</h3>
                        </div>
                        <button class="modal-close p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg">
                            <i class="fi fi-rr-info-circle text-emerald-600 text-base mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-[12px] text-emerald-900 font-semibold mb-0.5">Warning will be sent via in-app notification and email.</p>
                                <p class="text-[10px] text-emerald-800">Dispute status set to Resolved only after both sends succeed.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Warning Message *</label>
                            <textarea id="disputeWarningMessage" rows="4" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 transition resize-none" placeholder="Enter warning message..."></textarea>
                            <p id="disputeWarningMessageError" class="text-red-500 text-[11px] mt-1 hidden">Warning message must be at least 10 characters.</p>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                            <button class="modal-close px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmDisputeWarningBtn" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-[12px] font-semibold transition">Send Warning & Approve</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 DISPUTE HALT CONFIRMATION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="disputeHaltConfirmModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="modal-content relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-red-600 to-rose-600 border-b border-red-600 text-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow"><i class="fi fi-sr-briefcase text-white text-base"></i></div>
                            <h3 class="text-[15px] font-bold text-white leading-tight">Halt Project?</h3>
                        </div>
                        <button class="modal-close p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-red-50 border-l-4 border-red-500 rounded-lg">
                            <i class="fi fi-rr-info-circle text-red-600 text-base mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-[12px] text-red-900 font-semibold mb-0.5">This will halt the linked project and resolve this dispute.</p>
                                <p class="text-[10px] text-red-800">System will store <span class="font-semibold">Admin Action = Halted</span> after confirmation.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Reason for Halting *</label>
                            <textarea id="disputeHaltReason" rows="3" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Explain why this project is being halted..."></textarea>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Project Remarks (Optional)</label>
                            <textarea id="disputeHaltRemarks" rows="2" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Optional internal remarks..."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                            <button class="modal-close px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmDisputeHaltBtn" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-[12px] font-semibold transition">Confirm Halt</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 RESOLUTION ACTION MODAL (AFTER CASE IS RESOLVED)
                 ════════════════════════════════════════════════════════════ --}}
            <div id="resolutionActionModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="modal-content relative bg-white w-full max-w-xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[78vh] flex flex-col">
                    <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-indigo-600 to-blue-600 border-b border-indigo-600 text-white flex-shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow"><i class="fi fi-sr-gavel text-white text-base"></i></div>
                            <div>
                                <h3 class="text-[15px] font-bold text-white leading-tight">Resolution Action</h3>
                                <p class="text-[10px] text-indigo-100">Approve case and apply sanction in one step.</p>
                            </div>
                        </div>
                        <button class="modal-close p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
                    </div>
                    <div class="modal-scroll-hidden flex-1 overflow-y-auto p-4 space-y-3">
                        <div class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl border border-gray-200 p-3.5">
                            <h4 class="text-[11px] font-bold text-gray-600 uppercase mb-2">Reported User</h4>
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 border-2 border-indigo-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <img id="resolutionProfilePic" src="" alt="Profile" class="w-full h-full object-cover hidden">
                                    <i class="fi fi-sr-user text-indigo-400 text-base" id="resolutionProfileIcon"></i>
                                </div>
                                <div class="space-y-0.5">
                                    <p class="text-[12px] font-bold text-gray-800" id="resolutionUserName">-</p>
                                    <p class="text-[10px] text-gray-600" id="resolutionUserRole">-</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div id="resolutionApprovalPrompt" class="mb-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2.5 text-[12px] font-semibold text-indigo-900">Are you sure you want to approve this report?</div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-2">Action Type *</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="resolutionActionType" value="warning" class="peer sr-only" checked>
                                    <div class="border-2 border-gray-300 rounded-lg p-2.5 text-center transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-300">
                                        <i class="fi fi-rr-exclamation text-base text-gray-400 peer-checked:text-indigo-500"></i>
                                        <p class="text-[12px] font-semibold text-gray-700 mt-0.5">Warning</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="resolutionActionType" value="temporary_ban" class="peer sr-only">
                                    <div class="border-2 border-gray-300 rounded-lg p-2.5 text-center transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-300">
                                        <i class="fi fi-rr-clock-three text-base text-gray-400 peer-checked:text-indigo-500"></i>
                                        <p class="text-[12px] font-semibold text-gray-700 mt-0.5">Temporary Ban</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="resolutionActionType" value="permanent_ban" class="peer sr-only">
                                    <div class="border-2 border-gray-300 rounded-lg p-2.5 text-center transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-300">
                                        <i class="fi fi-rr-ban text-base text-gray-400 peer-checked:text-indigo-500"></i>
                                        <p class="text-[12px] font-semibold text-gray-700 mt-0.5">Permanent Ban</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div id="resolutionBanUntilWrap" class="hidden">
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Ban Until *</label>
                            <input type="date" id="resolutionBanUntil" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition" min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}">
                            <p class="text-[10px] text-gray-500 mt-0.5">Date when the temporary ban will be lifted.</p>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Action Reason *</label>
                            <textarea id="resolutionActionReason" rows="3" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none" placeholder="Provide reason for this resolution action..."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                            <button class="modal-close px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmResolutionActionBtn" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-[12px] font-semibold transition">Confirm Approval</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 DISMISS CONFIRMATION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="dismissConfirmModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="modal-content relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-amber-600 to-orange-600 border-b border-amber-600 text-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow"><i class="fi fi-rr-cross-small text-white text-xl"></i></div>
                            <h3 class="text-[15px] font-bold text-white leading-tight">Dismiss Report?</h3>
                        </div>
                        <button class="modal-close p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                            <i class="fi fi-rr-info-circle text-amber-600 text-base mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-[12px] text-amber-900 font-semibold mb-0.5">This report will be marked as invalid</p>
                                <p class="text-[10px] text-amber-800">Reporter will be notified. No action against the reported user.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Dismissal Reason *</label>
                            <textarea id="dismissReason" rows="3" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition resize-none" placeholder="Explain why this report is being dismissed..."></textarea>
                            <p id="dismissReasonError" class="text-red-500 text-[11px] mt-1 hidden">Reason is required.</p>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                            <button class="modal-close px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmDismissBtn" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-[12px] font-semibold transition">Confirm Dismiss</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 CONFIRM ACCOUNT SUSPENSION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="suspensionModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="modal-content relative bg-white w-full max-w-2xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[78vh] flex flex-col">
                    <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-red-600 to-rose-600 border-b border-red-600 text-white flex-shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow"><i class="fi fi-sr-shield-exclamation text-white text-base"></i></div>
                            <div>
                                <h3 class="text-[15px] font-bold text-white leading-tight">Confirm Account Suspension</h3>
                                <p class="text-[10px] text-red-100">Review offender and apply penalty</p>
                            </div>
                        </div>
                        <button class="modal-close p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
                    </div>
                    <div class="modal-scroll-hidden flex-1 overflow-y-auto p-4 space-y-3">
                        <div class="bg-gradient-to-br from-gray-50 to-red-50 rounded-xl border border-gray-200 p-3.5">
                            <h4 class="text-[11px] font-bold text-gray-600 uppercase mb-2">Reported User Profile</h4>
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-full bg-red-100 border-2 border-red-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <img id="suspProfilePic" src="" alt="Profile" class="w-full h-full object-cover hidden">
                                    <i class="fi fi-sr-user text-red-500 text-base" id="suspProfileIcon"></i>
                                </div>
                                <div class="flex-1 grid grid-cols-2 gap-2">
                                    <div><label class="text-[10px] text-gray-500 uppercase font-semibold">Name</label><p class="text-[12px] font-bold text-gray-800" id="suspUserName">-</p></div>
                                    <div><label class="text-[10px] text-gray-500 uppercase font-semibold">Role</label><p class="text-[12px] font-semibold text-gray-800" id="suspUserRole">-</p></div>
                                    <div><label class="text-[10px] text-gray-500 uppercase font-semibold">Projects Done</label><p class="text-[12px] font-semibold text-gray-800" id="suspProjectsDone">0</p></div>
                                    <div><label class="text-[10px] text-gray-500 uppercase font-semibold">Ongoing</label><p class="text-[12px] font-semibold text-gray-800" id="suspOngoingProjects">0</p></div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Suspension Reason *</label>
                            <textarea id="suspensionReason" rows="3" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Provide a detailed reason for suspension..."></textarea>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-2">Suspension Type *</label>
                            <div class="flex gap-2" id="suspTypeToggle">
                                <button type="button" class="susp-type-btn flex-1 p-3 rounded-lg border-2 border-red-500 bg-red-50 text-[12px] font-semibold text-center transition text-red-700" data-type="temporary">
                                    <i class="fi fi-sr-time-quarter-past text-red-600 text-base block mb-0.5"></i>Temporary Ban
                                </button>
                                <button type="button" class="susp-type-btn flex-1 p-3 rounded-lg border-2 border-gray-200 bg-white text-[12px] font-semibold text-center transition hover:border-gray-300 text-gray-700" data-type="permanent">
                                    <i class="fi fi-sr-ban text-gray-400 text-base block mb-0.5"></i>Permanent (Terminate)
                                </button>
                            </div>
                        </div>
                        <div id="suspDatePickerWrap">
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Suspension Until *</label>
                            <input type="date" id="suspensionUntil" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-red-300 focus:border-red-300 transition" min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}">
                            <p class="text-[10px] text-gray-500 mt-0.5">User re-activated after this date.</p>
                        </div>
                        <div class="bg-red-50 rounded-xl border border-red-200 p-3">
                            <h4 class="text-[11px] font-bold text-red-800 mb-2 flex items-center gap-1.5"><i class="fi fi-rr-triangle-warning text-red-600 text-xs"></i>Consequences</h4>
                            <ul class="space-y-1.5 text-[11px] text-red-900">
                                <li class="flex items-center gap-1.5"><i class="fi fi-rr-check text-red-600 text-[10px]"></i>User logged out of all sessions</li>
                                <li class="flex items-center gap-1.5"><i class="fi fi-rr-check text-red-600 text-[10px]"></i>Ongoing projects paused/halted</li>
                                <li class="flex items-center gap-1.5"><i class="fi fi-rr-check text-red-600 text-[10px]"></i>Active bids withdrawn</li>
                                <li class="flex items-center gap-1.5"><i class="fi fi-rr-check text-red-600 text-[10px]"></i>Recorded in Suspended Accounts log</li>
                                <li class="flex items-center gap-1.5" id="consequenceContent"><i class="fi fi-rr-check text-red-600 text-[10px]"></i><span id="consequenceContentText">Offending content removed/hidden</span></li>
                            </ul>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                            <button class="modal-close px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmSuspensionBtn" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-[12px] font-semibold transition"><i class="fi fi-sr-shield-exclamation mr-1.5"></i>Confirm Suspension & Resolve</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 HIDE POST CONFIRMATION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="hidePostModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="modal-content relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-amber-600 to-orange-600 border-b border-amber-600 text-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow"><i class="fi fi-sr-eye-crossed text-white text-base"></i></div>
                            <h3 class="text-[15px] font-bold text-white leading-tight">Hide Post?</h3>
                        </div>
                        <button class="modal-close p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                            <i class="fi fi-rr-triangle-warning text-amber-600 text-base mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-[12px] text-amber-900 font-semibold mb-0.5">Post will be hidden from public view</p>
                                <p class="text-[10px] text-amber-800">Owner will be notified with the reason below.</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl border border-gray-200 p-3">
                            <div class="space-y-1.5 text-[12px]">
                                <div class="flex items-center justify-between"><span class="text-[10px] font-semibold text-gray-500 uppercase">Post ID</span><span class="font-bold text-gray-800" id="hidePostId">-</span></div>
                                <div class="flex items-center justify-between"><span class="text-[10px] font-semibold text-gray-500 uppercase">Title</span><span class="font-semibold text-gray-800 max-w-[180px] truncate" id="hidePostTitle">-</span></div>
                                <div class="flex items-center justify-between"><span class="text-[10px] font-semibold text-gray-500 uppercase">Author</span><span class="text-gray-600" id="hidePostAuthor">-</span></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Reason for Hiding *</label>
                            <textarea id="hidePostReason" rows="3" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition resize-none" placeholder="Explain why this post is being hidden..."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                            <button class="modal-close px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmHidePostBtn" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-eye-crossed mr-1.5"></i>Hide Post</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 HIDE REVIEW CONFIRMATION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="hideReviewModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="modal-content relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-amber-600 to-orange-600 border-b border-amber-600 text-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow"><i class="fi fi-sr-eye-crossed text-white text-base"></i></div>
                            <h3 class="text-[15px] font-bold text-white leading-tight">Hide Review?</h3>
                        </div>
                        <button class="modal-close p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                            <i class="fi fi-rr-triangle-warning text-amber-600 text-base mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-[12px] text-amber-900 font-semibold mb-0.5">Review will be hidden from public view</p>
                                <p class="text-[10px] text-amber-800">Reviewer will be notified with the reason below.</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl border border-gray-200 p-3">
                            <div class="space-y-1.5 text-[12px]">
                                <div class="flex items-center justify-between"><span class="text-[10px] font-semibold text-gray-500 uppercase">Review ID</span><span class="font-bold text-gray-800" id="hideReviewId">-</span></div>
                                <div class="flex items-center justify-between"><span class="text-[10px] font-semibold text-gray-500 uppercase">Reviewer</span><span class="font-semibold text-gray-800" id="hideReviewAuthor">-</span></div>
                                <div class="flex items-center justify-between"><span class="text-[10px] font-semibold text-gray-500 uppercase">Rating</span><span class="text-amber-600 font-bold" id="hideReviewRating">-</span></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Reason for Hiding *</label>
                            <textarea id="hideReviewReason" rows="3" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition resize-none" placeholder="Explain why this review is being hidden..."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                            <button class="modal-close px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmHideReviewBtn" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-eye-crossed mr-1.5"></i>Hide Review</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 REMOVE REVIEW CONFIRMATION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="removeReviewModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
                <div class="modal-content relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-red-600 to-rose-600 border-b border-red-600 text-white">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow"><i class="fi fi-sr-trash text-white text-base"></i></div>
                            <h3 class="text-[15px] font-bold text-white leading-tight">Remove Review?</h3>
                        </div>
                        <button class="modal-close p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition"><i class="fi fi-rr-cross-small text-lg"></i></button>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex items-start gap-3 p-3 bg-red-50 border-l-4 border-red-500 rounded-lg">
                            <i class="fi fi-rr-info text-red-600 text-base mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-[12px] text-red-900 font-semibold mb-0.5">Review will be removed from public display</p>
                                <p class="text-[10px] text-red-800">Use for severe violations. Restore via Unhide if needed.</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl border border-gray-200 p-3">
                            <div class="space-y-1.5 text-[12px]">
                                <div class="flex items-center justify-between"><span class="text-[10px] font-semibold text-gray-500 uppercase">Review ID</span><span class="font-bold text-gray-800" id="removeReviewId">-</span></div>
                                <div class="flex items-center justify-between"><span class="text-[10px] font-semibold text-gray-500 uppercase">Reviewer</span><span class="font-semibold text-gray-800" id="removeReviewAuthor">-</span></div>
                                <div class="flex items-center justify-between"><span class="text-[10px] font-semibold text-gray-500 uppercase">Rating</span><span class="text-amber-600 font-bold" id="removeReviewRating">-</span></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-gray-800 mb-1.5">Reason for Removal *</label>
                            <textarea id="removeReviewReason" rows="3" class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-[12px] focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Explain why this review is being removed..."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                            <button class="modal-close px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 text-[12px] font-medium hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmRemoveReviewBtn" class="px-3.5 py-2 rounded-lg bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-[12px] font-semibold transition"><i class="fi fi-rr-trash mr-1.5"></i>Remove Review</button>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/admin/globalManagement/reportManagement.js') }}" defer></script>
</body>

</html>


