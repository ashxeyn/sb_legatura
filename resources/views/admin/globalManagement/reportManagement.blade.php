<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Legatura</title>

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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Total Reports</p>
                                <h3 class="text-4xl font-bold text-gray-800" id="statTotal">{{ $counts['total'] }}</h3>
                            </div>
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center">
                                <i class="fi fi-sr-file-invoice text-2xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Pending</p>
                                <h3 class="text-4xl font-bold text-gray-800" id="statPending">{{ $counts['pending'] }}</h3>
                            </div>
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-amber-100 to-orange-200 flex items-center justify-center">
                                <i class="fi fi-sr-hourglass-end text-2xl text-amber-600"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Under Review</p>
                                <h3 class="text-4xl font-bold text-gray-800" id="statUnderReview">{{ $counts['under_review'] }}</h3>
                            </div>
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-red-100 to-rose-200 flex items-center justify-center">
                                <i class="fi fi-sr-shield-exclamation text-2xl text-red-600"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-1">Resolved / Dismissed</p>
                                <h3 class="text-4xl font-bold text-gray-800" id="statResolved">{{ $counts['resolved'] }}</h3>
                            </div>
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 flex items-center justify-center">
                                <i class="fi fi-sr-check-circle text-2xl text-emerald-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Tab Navigation & Content ── --}}
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                        <div class="flex items-center gap-2 bg-white rounded-xl p-1 shadow-sm">
                            <button class="tab-btn active" data-tab="moderationHub" id="tabModerationHub">
                                <i class="fi fi-rr-shield-check mr-1"></i> Submitted Cases
                                <span class="ml-1 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ $counts['total'] }}</span>
                            </button>
                            <button class="tab-btn" data-tab="adminAction" id="tabAdminAction">
                                <i class="fi fi-rr-megaphone mr-1"></i> Direct Admin Action
                            </button>
                        </div>
                    </div>

                    {{-- ── Tab 1: Active Moderation ── --}}
                    <div id="panelModerationHub" class="tab-panel">
                        {{-- Header with Filters --}}
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">Active Reports</h3>
                            <div class="flex flex-wrap items-center gap-3">
                                <select id="filterSource" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white font-medium text-gray-700 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <option value="all">All Source Types</option>
                                    <option value="project">Project</option>
                                    <option value="showcase">Showcase</option>
                                    <option value="review">Review</option>
                                    <option value="user">User</option>
                                    <option value="dispute">Dispute</option>
                                </select>

                                <select id="filterCaseType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white font-medium text-gray-700 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <option value="all">All Case Types</option>
                                    <option value="report">Report</option>
                                    <option value="dispute">Dispute</option>
                                </select>

                                <select id="filterStatus" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white font-medium text-gray-700 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <option value="all">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="dismissed">Dismissed</option>
                                </select>

                                <div class="relative min-w-[200px]">
                                    <input type="text" id="filterSearch" placeholder="Search reports..." class="w-full px-3 py-2 pl-9 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <i class="fi fi-rr-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                </div>

                                <button id="resetFilters" class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                                    <i class="fi fi-rr-rotate-left mr-1"></i>Reset
                                </button>
                            </div>
                        </div>

                        {{-- Reports Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Case ID</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Case Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Source Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reporter</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Target</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason / Subject</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Admin Action</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Submitted</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="reportsTableBody" class="divide-y divide-gray-200">
                                    @forelse ($reports as $report)
                                        <tr class="hover:bg-gray-50 transition"
                                            data-id="{{ $report->case_ref_id }}"
                                            data-source="{{ $report->source }}"
                                            data-status="{{ $report->status }}">
                                            <td class="px-6 py-4 text-sm font-mono text-gray-700">{{ $report->case_id }}</td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $caseColors = ['report' => 'bg-indigo-100 text-indigo-700', 'dispute' => 'bg-orange-100 text-orange-700'];
                                                @endphp
                                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $caseColors[$report->case_type] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($report->case_type) }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $srcColors = ['project' => 'bg-blue-100 text-blue-700', 'showcase' => 'bg-teal-100 text-teal-700', 'review' => 'bg-purple-100 text-purple-700', 'user' => 'bg-cyan-100 text-cyan-700', 'dispute' => 'bg-orange-100 text-orange-700'];
                                                @endphp
                                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $srcColors[$report->source_type] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst(str_replace('_', ' ', $report->source_type)) }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ $report->reporter ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $report->target }}">{{ $report->target }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $report->reason }}">{{ $report->reason }}</td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $statColors = ['pending' => 'bg-amber-100 text-amber-700 border-amber-200', 'under_review' => 'bg-blue-100 text-blue-700 border-blue-200', 'resolved' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'dismissed' => 'bg-red-100 text-red-700 border-red-200'];
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $statColors[$report->status] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">{{ strtoupper(str_replace('_', ' ', $report->status)) }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700">{{ $report->admin_action ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $report->created_at ? \Carbon\Carbon::parse($report->created_at)->format('F j, Y') : '-' }}</td>
                                            <td class="px-6 py-4 text-center">
                                                <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition view-report-btn"
                                                    data-id="{{ $report->case_ref_id }}"
                                                    data-source="{{ $report->source }}"
                                                    data-case-type="{{ $report->case_type }}">
                                                    <i class="fi fi-rr-eye mr-1"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-6 py-12 text-center text-gray-500 text-sm">No moderation cases found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div id="moderationPaginationBar" class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                            <span id="moderationPaginationInfo" class="text-sm text-gray-600"></span>
                            <div class="flex items-center gap-2">
                                <button id="moderationPrevPage" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                                    <i class="fi fi-rr-angle-left mr-1"></i> Previous
                                </button>
                                <span id="moderationPageIndicator" class="text-sm font-medium text-gray-700"></span>
                                <button id="moderationNextPage" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                                    Next <i class="fi fi-rr-angle-right ml-1"></i>
                                </button>
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
                            $statsPerPage = 15;
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
            <div id="viewReportModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">
                    {{-- Header --}}
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 sticky top-0 z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fi fi-sr-document text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Case Details</h3>
                                    <p class="text-indigo-100 text-sm" id="modalCaseId">Case #---</p>
                                </div>
                            </div>
                            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Report Overview --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Source</label>
                                    <p class="text-sm font-semibold text-gray-800" id="modalSource">-</p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Reporter</label>
                                    <p class="text-sm font-semibold text-gray-800" id="modalReporter">-</p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Reason</label>
                                    <p class="text-sm font-semibold text-gray-800" id="modalReason">-</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Content Type</label>
                                    <p class="text-sm font-semibold text-gray-800" id="modalContentType">-</p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Date Filed</label>
                                    <p class="text-sm font-semibold text-gray-800" id="modalDate">-</p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Status</label>
                                    <div id="modalStatus"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Report Details --}}
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Case Description</label>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line" id="modalDetails">-</p>
                        </div>

                        {{-- Evidence Section — dynamically populated --}}
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fi fi-sr-folder-open text-indigo-600"></i>
                                Evidence
                            </h4>
                            <div id="evidenceContainer" class="space-y-4">
                                <div class="text-center text-gray-400 text-sm py-4">Loading evidence...</div>
                            </div>
                        </div>

                        {{-- Dispute Workflow Context (only shown for dispute cases) --}}
                        <div id="disputeWorkflowSection" class="hidden border-t border-gray-200 pt-6">
                            <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <i class="fi fi-sr-briefcase text-indigo-600"></i>
                                Dispute Workflow
                            </h4>

                            <div id="disputeWorkflowBanner" class="rounded-xl border px-4 py-3 bg-gray-50 border-gray-200 mb-4">
                                <p class="text-sm font-semibold text-gray-800" id="disputeWorkflowTitle">Case Workflow</p>
                                <p class="text-xs text-gray-600 mt-1" id="disputeWorkflowMessage">Follow the dispute workflow steps to complete this case.</p>
                            </div>

                            <div id="disputeInformationHierarchy" class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
                                <h5 class="text-sm font-bold text-gray-700 uppercase mb-3">Dispute Information</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Subject</label>
                                        <p class="text-sm text-gray-800" id="disputeSubjectField">-</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Requested Action</label>
                                        <p class="text-sm text-gray-800" id="disputeRequestedActionField">-</p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Description</label>
                                    <p class="text-sm text-gray-700 whitespace-pre-line" id="disputeDescriptionField">-</p>
                                </div>

                                <div class="mt-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Initial Proofs</label>
                                    <div id="disputeInitialProofsList" class="space-y-2 text-sm text-gray-700"></div>
                                </div>

                                <div class="mt-4 border-t border-gray-200 pt-4">
                                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Resubmitted Report Panel</label>
                                    <div id="disputeResubmittedPanel" class="space-y-2 text-sm text-gray-700"></div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl border border-gray-200 p-5 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Related Project</label>
                                        <p class="text-sm font-semibold text-gray-800" id="disputeProjectTitle">-</p>
                                        <p class="text-xs text-gray-500" id="disputeProjectId">-</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Project Status</label>
                                        <p class="text-sm font-semibold text-gray-800" id="disputeProjectStatus">-</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Property Owner</label>
                                        <p class="text-sm text-gray-700" id="disputeProjectOwner">-</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Contractor</label>
                                        <p class="text-sm text-gray-700" id="disputeProjectContractor">-</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Budget</label>
                                        <p class="text-sm text-gray-700" id="disputeProjectBudget">-</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Timeline</label>
                                        <p class="text-sm text-gray-700" id="disputeProjectTimeline">-</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Required Action</label>
                                        <p class="text-sm text-gray-700" id="disputeRequiredAction">-</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Action State</label>
                                        <p class="text-sm text-gray-700" id="disputeActionState">-</p>
                                    </div>
                                </div>

                                <div id="disputeProjectActionForm" class="hidden border-t border-gray-200 pt-4 space-y-3">
                                    <p class="text-sm text-gray-700">This action will halt the project, resolve the dispute automatically, and record <span class="font-semibold">Admin Action: Halted</span>.</p>
                                    <div class="flex items-center justify-end">
                                        <button id="btnApplyDisputeProjectAction" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                            Halt Project
                                        </button>
                                    </div>
                                </div>

                                <div id="disputeResolvedProjectActions" class="hidden border-t border-gray-200 pt-4 space-y-3">
                                    <label class="text-xs font-semibold text-gray-500 uppercase block">Post-Resolution Project Decision</label>
                                    <p class="text-sm text-gray-700">This dispute is resolved and the project is halted. Choose the next project action.</p>
                                    <div class="flex items-center justify-end gap-3">
                                        <button id="btnResumeDisputeProject" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                            Resume Project
                                        </button>
                                        <button id="btnTerminateDisputeProject" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                            Terminate Project
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Admin Notes (shown for already-resolved reports) --}}
                        <div id="modalAdminNotesWrap" class="hidden border-t border-gray-200 pt-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Admin Resolution Notes</label>
                            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                                <p class="text-sm text-gray-700" id="modalAdminNotes">-</p>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-between gap-3 pt-6 border-t border-gray-200" id="modalActionsFooter">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <i class="fi fi-rr-shield-check text-indigo-500"></i>
                                <span>All actions are logged and monitored for compliance.</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Close</button>
                                <div id="modalActionBtns" class="flex items-center gap-3">
                                    <button id="btnUnderReviewReport" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                        <i class="fi fi-rr-search mr-1"></i> Mark Under Review
                                    </button>
                                    <button id="btnDismissReport" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                        <i class="fi fi-rr-cross-small mr-1"></i> Dismiss
                                    </button>
                                    <button id="btnConfirmReport" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                        <i class="fi fi-rr-check mr-1"></i> Approve Report
                                    </button>
                                </div>
                                <div id="modalDisputeActionBtns" class="hidden items-center gap-3">
                                    <button id="btnReviewDispute" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                        <i class="fi fi-rr-eye mr-1"></i> Move to Under Review
                                    </button>
                                    <button id="btnRejectDispute" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                        <i class="fi fi-rr-cross-small mr-1"></i> Dismiss
                                    </button>
                                    <button id="btnResolveDispute" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                        <i class="fi fi-rr-check mr-1"></i> Approve
                                    </button>
                                </div>
                                <div id="modalDirectActionBtns" class="hidden items-center gap-3">
                                    <button id="btnDirectHide" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                        <i class="fi fi-rr-eye-crossed mr-1"></i> Hide
                                    </button>
                                    <button id="btnDirectUnhide" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                        <i class="fi fi-rr-eye mr-1"></i> Unhide
                                    </button>
                                    <button id="btnDirectRemoveReview" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition hidden">
                                        <i class="fi fi-rr-trash mr-1"></i> Remove Review
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 DISPUTE PROJECT DECISION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="disputeProjectDecisionModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fi fi-sr-briefcase text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white" id="disputeProjectDecisionTitle">Confirm Project Decision</h3>
                            </div>
                            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="flex items-start gap-4 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
                            <i class="fi fi-rr-info-circle text-indigo-600 text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm text-indigo-900 font-semibold mb-1" id="disputeProjectDecisionLabel">Proceed with selected project decision</p>
                                <p class="text-xs text-indigo-800">This action is final and will be logged in the admin audit trail.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Admin Reason *</label>
                            <textarea id="disputeProjectDecisionReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none" placeholder="Provide reason for this decision..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Project Remarks (Optional)</label>
                            <textarea id="disputeProjectDecisionRemarks" rows="2" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none" placeholder="Optional internal remarks..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
                            <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmDisputeProjectDecisionBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 DISPUTE WARNING MODAL (NON-HALT TYPES)
                 ════════════════════════════════════════════════════════════ --}}
            <div id="disputeWarningModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-amber-600 to-orange-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fi fi-sr-bell text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Warn Reported User</h3>
                            </div>
                            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="flex items-start gap-4 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                            <i class="fi fi-rr-info-circle text-amber-600 text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm text-amber-900 font-semibold mb-1">Warning will be sent to the reported user via in-app notification and email.</p>
                                <p class="text-xs text-amber-800">Dispute status will be set to Resolved only after both sends are successful.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Warning Message *</label>
                            <textarea id="disputeWarningMessage" rows="5" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition resize-none" placeholder="Enter warning message to send to the reported user..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
                            <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmDisputeWarningBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                Send Warning & Approve
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 DISPUTE HALT CONFIRMATION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="disputeHaltConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fi fi-sr-briefcase text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Halt Project?</h3>
                            </div>
                            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="flex items-start gap-4 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
                            <i class="fi fi-rr-info-circle text-indigo-600 text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm text-indigo-900 font-semibold mb-1">This will halt the linked project and resolve this dispute.</p>
                                <p class="text-xs text-indigo-800">The system will store <span class="font-semibold">Admin Action = Halted</span> after confirmation.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Reason for Halting *</label>
                            <textarea id="disputeHaltReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none" placeholder="Explain why this project is being halted..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Project Remarks (Optional)</label>
                            <textarea id="disputeHaltRemarks" rows="2" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none" placeholder="Optional internal remarks..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
                            <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmDisputeHaltBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                Confirm Halt
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 RESOLUTION ACTION MODAL (AFTER CASE IS RESOLVED)
                 ════════════════════════════════════════════════════════════ --}}
            <div id="resolutionActionModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-blue-600 sticky top-0 z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fi fi-sr-gavel text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Resolution Action</h3>
                                    <p class="text-indigo-100 text-sm">Approve this case and apply the sanction in one step.</p>
                                </div>
                            </div>
                            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl border border-gray-200 p-5">
                            <h4 class="text-sm font-bold text-gray-600 uppercase mb-4">Reported User</h4>
                            <div class="flex items-start gap-5">
                                <div class="w-14 h-14 rounded-full bg-indigo-100 border-2 border-indigo-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <img id="resolutionProfilePic" src="" alt="Profile" class="w-full h-full object-cover hidden">
                                    <i class="fi fi-sr-user text-indigo-400 text-xl" id="resolutionProfileIcon"></i>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-gray-800" id="resolutionUserName">-</p>
                                    <p class="text-xs text-gray-600" id="resolutionUserRole">-</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div id="resolutionApprovalPrompt" class="mb-3 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-900">
                                Are you sure you want to approve this report?
                            </div>

                            <label class="block text-sm font-semibold text-gray-800 mb-3">Action Type *</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="resolutionActionType" value="warning" class="peer sr-only" checked>
                                    <div class="border-2 border-gray-300 rounded-xl p-3 text-center transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-300">
                                        <i class="fi fi-rr-exclamation text-lg text-gray-400 peer-checked:text-indigo-500"></i>
                                        <p class="text-sm font-semibold text-gray-700 mt-1">Warning</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="resolutionActionType" value="temporary_ban" class="peer sr-only">
                                    <div class="border-2 border-gray-300 rounded-xl p-3 text-center transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-300">
                                        <i class="fi fi-rr-clock-three text-lg text-gray-400 peer-checked:text-indigo-500"></i>
                                        <p class="text-sm font-semibold text-gray-700 mt-1">Temporary Ban</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="resolutionActionType" value="permanent_ban" class="peer sr-only">
                                    <div class="border-2 border-gray-300 rounded-xl p-3 text-center transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-300">
                                        <i class="fi fi-rr-ban text-lg text-gray-400 peer-checked:text-indigo-500"></i>
                                        <p class="text-sm font-semibold text-gray-700 mt-1">Permanent Ban</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="resolutionBanUntilWrap" class="hidden">
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Ban Until *</label>
                            <input type="date" id="resolutionBanUntil" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition" min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}">
                            <p class="text-xs text-gray-500 mt-1">Select the exact date when the temporary ban will be lifted.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Action Reason *</label>
                            <textarea id="resolutionActionReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none" placeholder="Provide reason for this resolution action..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
                            <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmResolutionActionBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                Confirm Approval
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 DISMISS CONFIRMATION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="dismissConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fi fi-sr-times-circle text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Dismiss Report?</h3>
                            </div>
                            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                            <i class="fi fi-rr-info-circle text-red-600 text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm text-red-900 font-semibold mb-1">This report will be marked as invalid</p>
                                <p class="text-xs text-red-800">The reporter will be notified. No action will be taken against the reported user.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Dismissal Reason *</label>
                            <textarea id="dismissReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Explain why this report is being dismissed..."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
                            <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmDismissBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                Confirm Dismiss
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 CONFIRM ACCOUNT SUSPENSION MODAL
                 (Appears when admin clicks "Confirm" on a valid report)
                 ════════════════════════════════════════════════════════════ --}}
            <div id="suspensionModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">
                    {{-- Header --}}
                    <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-600 sticky top-0 z-10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fi fi-sr-shield-exclamation text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Confirm Account Suspension</h3>
                                    <p class="text-emerald-100 text-sm">Review offender details and apply penalty</p>
                                </div>
                            </div>
                            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- User Profile Card --}}
                        <div class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl border border-gray-200 p-5">
                            <h4 class="text-sm font-bold text-gray-600 uppercase mb-4">Reported User Profile</h4>
                            <div class="flex items-start gap-5">
                                <div class="w-16 h-16 rounded-full bg-indigo-100 border-2 border-indigo-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <img id="suspProfilePic" src="" alt="Profile" class="w-full h-full object-cover hidden">
                                    <i class="fi fi-sr-user text-indigo-400 text-2xl" id="suspProfileIcon"></i>
                                </div>
                                <div class="flex-1 grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500 uppercase font-semibold">Name</label>
                                        <p class="text-sm font-bold text-gray-800" id="suspUserName">-</p>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 uppercase font-semibold">Role</label>
                                        <p class="text-sm font-semibold text-gray-800" id="suspUserRole">-</p>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 uppercase font-semibold">Projects Completed</label>
                                        <p class="text-sm font-semibold text-gray-800" id="suspProjectsDone">0</p>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 uppercase font-semibold">Ongoing Projects</label>
                                        <p class="text-sm font-semibold text-gray-800" id="suspOngoingProjects">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Suspension Reason --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Suspension Reason *</label>
                            <textarea id="suspensionReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 transition resize-none" placeholder="Provide a detailed reason for suspension..."></textarea>
                        </div>

                        {{-- Suspension Type Toggle --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-3">Suspension Type *</label>
                            <div class="flex gap-3" id="suspTypeToggle">
                                <button type="button" class="susp-type-btn flex-1 p-4 rounded-xl border-2 border-blue-500 bg-blue-50 text-sm font-semibold text-center transition" data-type="temporary">
                                    <i class="fi fi-sr-time-quarter-past text-blue-600 text-lg block mb-1"></i>
                                    Temporary Ban
                                </button>
                                <button type="button" class="susp-type-btn flex-1 p-4 rounded-xl border-2 border-gray-200 bg-white text-sm font-semibold text-center transition hover:border-gray-300" data-type="permanent">
                                    <i class="fi fi-sr-ban text-gray-400 text-lg block mb-1"></i>
                                    Permanent (Terminate)
                                </button>
                            </div>
                        </div>

                        {{-- Temporary: Date Picker --}}
                        <div id="suspDatePickerWrap">
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Suspension Until *</label>
                            <input type="date" id="suspensionUntil" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-300 focus:border-blue-300 transition"
                                min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}">
                            <p class="text-xs text-gray-500 mt-1">The user will be re-activated after this date.</p>
                        </div>

                        {{-- Consequences Checklist --}}
                        <div class="bg-amber-50 rounded-xl border border-amber-200 p-4">
                            <h4 class="text-sm font-bold text-amber-800 mb-3 flex items-center gap-2">
                                <i class="fi fi-rr-triangle-warning text-amber-600"></i>
                                Consequences of this action
                            </h4>
                            <ul class="space-y-2 text-sm text-amber-900">
                                <li class="flex items-center gap-2">
                                    <i class="fi fi-rr-check text-amber-600"></i>
                                    The user will be immediately logged out of all sessions
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fi fi-rr-check text-amber-600"></i>
                                    All ongoing projects will be paused/halted
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fi fi-rr-check text-amber-600"></i>
                                    Active bids will be withdrawn
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fi fi-rr-check text-amber-600"></i>
                                    The event will be recorded in the Suspended Accounts log
                                </li>
                                <li class="flex items-center gap-2" id="consequenceContent">
                                    <i class="fi fi-rr-check text-amber-600"></i>
                                    <span id="consequenceContentText">Offending content will be removed/hidden</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
                            <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmSuspensionBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                <i class="fi fi-sr-shield-exclamation mr-2"></i>
                                Confirm Suspension & Resolve
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 HIDE POST CONFIRMATION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="hidePostModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-amber-600 to-orange-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fi fi-sr-eye-crossed text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Hide Post?</h3>
                            </div>
                            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="flex items-start gap-4 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                            <i class="fi fi-rr-triangle-warning text-amber-600 text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm text-amber-900 font-semibold mb-1">This post will be hidden from public view</p>
                                <p class="text-xs text-amber-800">The post owner will be notified about this action with the reason provided below.</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Post ID</span>
                                    <span class="text-sm font-bold text-gray-800" id="hidePostId">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Title</span>
                                    <span class="text-sm font-semibold text-gray-800 max-w-[200px] truncate" id="hidePostTitle">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Author</span>
                                    <span class="text-sm text-gray-600" id="hidePostAuthor">-</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Reason for Hiding *</label>
                            <textarea id="hidePostReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition resize-none" placeholder="Explain why this post is being hidden..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
                            <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmHidePostBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                <i class="fi fi-rr-eye-crossed mr-2"></i>
                                Hide Post
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 HIDE REVIEW CONFIRMATION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="hideReviewModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-amber-600 to-orange-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fi fi-sr-eye-crossed text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Hide Review?</h3>
                            </div>
                            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="flex items-start gap-4 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                            <i class="fi fi-rr-triangle-warning text-amber-600 text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm text-amber-900 font-semibold mb-1">This review will be hidden from public view</p>
                                <p class="text-xs text-amber-800">The reviewer will be notified about this action with the reason provided below.</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Review ID</span>
                                    <span class="text-sm font-bold text-gray-800" id="hideReviewId">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Reviewer</span>
                                    <span class="text-sm font-semibold text-gray-800" id="hideReviewAuthor">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Rating</span>
                                    <span class="text-sm text-amber-600 font-bold" id="hideReviewRating">-</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Reason for Hiding *</label>
                            <textarea id="hideReviewReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition resize-none" placeholder="Explain why this review is being hidden..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
                            <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmHideReviewBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                <i class="fi fi-rr-eye-crossed mr-2"></i>
                                Hide Review
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════
                 REMOVE REVIEW CONFIRMATION MODAL
                 ════════════════════════════════════════════════════════════ --}}
            <div id="removeReviewModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
                <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
                    <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <i class="fi fi-sr-trash text-white text-xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-white">Remove Review?</h3>
                            </div>
                            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                            <i class="fi fi-rr-info text-red-600 text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm text-red-900 font-semibold mb-1">This review will be removed from public display</p>
                                <p class="text-xs text-red-800">Use this action for severe violations. You can still restore via Unhide if needed.</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Review ID</span>
                                    <span class="text-sm font-bold text-gray-800" id="removeReviewId">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Reviewer</span>
                                    <span class="text-sm font-semibold text-gray-800" id="removeReviewAuthor">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500 uppercase">Rating</span>
                                    <span class="text-sm text-amber-600 font-bold" id="removeReviewRating">-</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Reason for Removal *</label>
                            <textarea id="removeReviewReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Explain why this review is being removed..."></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
                            <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
                            <button id="confirmRemoveReviewBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                <i class="fi fi-rr-trash mr-2"></i>
                                Remove Review
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/admin/globalManagement/reportManagement.js') }}" defer></script>
</body>

</html>
