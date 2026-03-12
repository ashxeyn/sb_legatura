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
                                <i class="fi fi-rr-shield-check mr-1"></i> Active Moderation
                                <span class="ml-1 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ $counts['total'] }}</span>
                            </button>
                            <button class="tab-btn" data-tab="reportHistory" id="tabReportHistory">
                                <i class="fi fi-rr-time-past mr-1"></i> Report History
                            </button>
                            <button class="tab-btn" data-tab="adminAction" id="tabAdminAction">
                                <i class="fi fi-rr-megaphone mr-1"></i> Direct Admin Action
                            </button>
                        </div>
                    </div>

                    {{-- ── Tab 1: Active Moderation ── --}}
                    <div id="panelModerationHub" class="tab-panel">
                        {{-- Filters --}}
                        <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                                    <i class="fi fi-rr-filter text-gray-500"></i>
                                    <span>Filter By</span>
                                </div>

                                <select id="filterSource" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white font-medium text-gray-700 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <option value="all">All Sources</option>
                                    <option value="post">Post Reports</option>
                                    <option value="review">Review Reports</option>
                                    <option value="content">Content Reports</option>
                                    <option value="dispute">User Disputes</option>
                                </select>

                                <select id="filterStatus" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white font-medium text-gray-700 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <option value="all">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="under_review">Under Review</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="dismissed">Dismissed</option>
                                </select>

                                <div class="flex items-center gap-2">
                                    <label class="text-sm font-medium text-gray-700">From:</label>
                                    <input type="date" id="filterDateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <label class="text-sm font-medium text-gray-700">To:</label>
                                    <input type="date" id="filterDateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                </div>

                                <div class="relative">
                                    <input type="text" id="filterSearch" placeholder="Search..." class="px-3 py-2 pl-9 border border-gray-300 rounded-lg text-sm w-48 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <i class="fi fi-rr-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                </div>
                            </div>

                            <button id="resetFilters" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                                <i class="fi fi-rr-rotate-left"></i>
                                <span>Reset Filter</span>
                            </button>
                        </div>

                        {{-- Reports Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Source</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reporter</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reason</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="reportsTableBody" class="divide-y divide-gray-200">
                                    @forelse ($reports as $report)
                                        <tr class="hover:bg-gray-50 transition"
                                            data-id="{{ $report->report_id }}"
                                            data-source="{{ $report->report_source }}"
                                            data-status="{{ $report->status }}">
                                            <td class="px-6 py-4 text-sm font-mono text-gray-700">#{{ $report->report_id }}</td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $srcColors = ['post' => 'bg-blue-100 text-blue-700', 'review' => 'bg-purple-100 text-purple-700', 'content' => 'bg-teal-100 text-teal-700', 'dispute' => 'bg-orange-100 text-orange-700'];
                                                    $srcLabels = ['post' => 'Post', 'review' => 'Review', 'content' => 'Content', 'dispute' => 'Dispute'];
                                                @endphp
                                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $srcColors[$report->report_source] ?? 'bg-gray-100 text-gray-700' }}">{{ $srcLabels[$report->report_source] ?? ucfirst($report->report_source) }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst($report->content_type) }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ $report->reporter_username ?? '-' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $report->reason }}">{{ $report->reason }}</td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $statColors = ['pending' => 'bg-amber-100 text-amber-700', 'under_review' => 'bg-blue-100 text-blue-700', 'resolved' => 'bg-emerald-100 text-emerald-700', 'dismissed' => 'bg-red-100 text-red-700'];
                                                @endphp
                                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $statColors[$report->status] ?? 'bg-gray-100 text-gray-700' }}">{{ strtoupper(str_replace('_', ' ', $report->status)) }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $report->created_at ? \Carbon\Carbon::parse($report->created_at)->format('M d, Y') : '-' }}</td>
                                            <td class="px-6 py-4 text-center">
                                                <button class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-xs font-semibold shadow-sm hover:shadow-md transition view-report-btn"
                                                    data-id="{{ $report->report_id }}"
                                                    data-source="{{ $report->report_source }}">
                                                    <i class="fi fi-rr-eye mr-1"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 text-sm">No reports found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reporter</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Reports</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Pending</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Resolved</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dismissed</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dismiss Rate</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Sources</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Flags</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Latest Report</th>
                                    </tr>
                                </thead>
                                <tbody id="reporterStatsBody">
                                    @forelse ($reporterStats as $stat)
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition reporter-row"
                                            data-super="{{ $stat->is_super_reporter ? '1' : '0' }}"
                                            data-abuser="{{ $stat->is_potential_abuser ? '1' : '0' }}">
                                            <td class="px-6 py-4 text-sm text-gray-700 font-medium">{{ $stat->reporter_username }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-800 font-bold">{{ $stat->total_reports }}</td>
                                            <td class="px-6 py-4 text-sm text-amber-600 font-medium">{{ $stat->pending_count }}</td>
                                            <td class="px-6 py-4 text-sm text-emerald-600 font-medium">{{ $stat->resolved_count }}</td>
                                            <td class="px-6 py-4 text-sm text-red-600 font-medium">{{ $stat->dismissed_count }}</td>
                                            <td class="px-6 py-4">
                                                @php $rateColor = $stat->dismiss_rate >= 50 ? 'text-red-700 bg-red-100' : ($stat->dismiss_rate >= 25 ? 'text-amber-700 bg-amber-100' : 'text-emerald-700 bg-emerald-100'); @endphp
                                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $rateColor }}">{{ $stat->dismiss_rate }}%</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex gap-1 flex-wrap">
                                                    @foreach ($stat->sources as $src)
                                                        @php $srcC = ['post' => 'bg-blue-50 text-blue-600', 'review' => 'bg-purple-50 text-purple-600', 'content' => 'bg-teal-50 text-teal-600', 'dispute' => 'bg-orange-50 text-orange-600']; @endphp
                                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $srcC[$src] ?? 'bg-gray-50 text-gray-600' }}">{{ ucfirst($src) }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex gap-1 flex-wrap">
                                                    @if ($stat->is_super_reporter)
                                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">Super Reporter</span>
                                                    @endif
                                                    @if ($stat->is_potential_abuser)
                                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Potential Abuser</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $stat->latest_report ? \Carbon\Carbon::parse($stat->latest_report)->format('M d, Y') : '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-6 py-12 text-center text-gray-500 text-sm">No reporter data available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                    {{-- ── Tab 3: Direct Admin Action ── --}}
                    <div id="panelAdminAction" class="tab-panel hidden">
                        <div class="px-6 py-5 border-b border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">Direct Admin Action</h3>
                                    <p class="text-sm text-gray-500">Search for any user, post, or review and take action directly.</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <select id="adminSearchType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white font-medium text-gray-700 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                                    <option value="user">Users</option>
                                    <option value="post">Showcase Posts</option>
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
                            <table class="w-full" id="adminSearchTable">
                                <thead class="bg-gray-50 border-b border-gray-200" id="adminSearchThead">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider" colspan="6">
                                            <span class="text-gray-400">Browse or search users, posts, or reviews below.</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="adminSearchBody">
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">
                                            <i class="fi fi-rr-loading-spinner text-3xl block mb-2 text-gray-300 animate-spin"></i>
                                            Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div id="adminPaginationBar" class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between hidden">
                            <span id="adminPaginationInfo" class="text-sm text-gray-600"></span>
                            <div class="flex items-center gap-2">
                                <button id="adminPrevPage" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                                    <i class="fi fi-rr-angle-left mr-1"></i> Previous
                                </button>
                                <span id="adminPageIndicator" class="text-sm font-medium text-gray-700"></span>
                                <button id="adminNextPage" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                                    Next <i class="fi fi-rr-angle-right ml-1"></i>
                                </button>
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
                                    <h3 class="text-xl font-bold text-white">Report Details</h3>
                                    <p class="text-indigo-100 text-sm" id="modalCaseId">Report #---</p>
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
                            <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Reporter's Details</label>
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
                                    <button id="btnDismissReport" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                        <i class="fi fi-rr-cross-small mr-1"></i> Dismiss
                                    </button>
                                    <button id="btnConfirmReport" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                                        <i class="fi fi-rr-check mr-1"></i> Confirm
                                    </button>
                                </div>
                            </div>
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

        </main>
    </div>

    <script src="{{ asset('js/admin/globalManagement/reportManagement.js') }}" defer></script>
</body>

</html>
