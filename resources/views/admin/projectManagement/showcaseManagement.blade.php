<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Legatura</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/showcaseManagement.css') }}">

    <link rel='stylesheet'
        href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
    <link rel='stylesheet'
        href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet'
        href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


    <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

    <style>
        .date-pill input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0.5;
            cursor: pointer;
            filter: invert(30%) sepia(80%) saturate(400%) hue-rotate(210deg);
        }

        .date-pill input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }
    </style>


</head>

<body class="bg-gray-50 text-gray-800 font-sans">
    <div class="flex min-h-screen">

        @include('admin.layouts.sidebar')

        <main class="flex-1">
            @include('admin.layouts.topnav', ['pageTitle' => 'Showcase Management'])

            <div class="p-8 space-y-6">

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Total -->
                    <div
                        class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Total Showcases</p>
                                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($stats->total) }}</h2>
                                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-blue-100 mt-2 w-fit">
                                    <i class="fi fi-sr-database text-[10px] text-blue-600"></i>
                                    <span class="text-[11px] font-semibold text-blue-600">All records</span>
                                </div>
                            </div>
                            <div class="stat-icon-wrap bg-blue-100 p-2.5 rounded-lg">
                                <i class="fi fi-sr-layers text-lg text-blue-600"></i>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-400">All time</p>
                    </div>

                    <!-- Approved -->
                    <div
                        class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Approved Showcases</p>
                                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($stats->approved) }}</h2>
                                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-green-100 mt-2 w-fit">
                                    <i class="fi fi-sr-check-circle text-[10px] text-green-600"></i>
                                    <span class="text-[11px] font-semibold text-green-600">Published posts</span>
                                </div>
                            </div>
                            <div class="stat-icon-wrap bg-green-100 p-2.5 rounded-lg">
                                <i class="fi fi-sr-check-circle text-lg text-green-600"></i>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-400">Approved status</p>
                    </div>

                    <!-- Pending -->
                    <div
                        class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Pending Review</p>
                                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($stats->pending) }}</h2>
                                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-orange-100 mt-2 w-fit">
                                    <i class="fi fi-sr-time-check text-[10px] text-orange-600"></i>
                                    <span class="text-[11px] font-semibold text-orange-600">Awaiting review</span>
                                </div>
                            </div>
                            <div class="stat-icon-wrap bg-orange-100 p-2.5 rounded-lg">
                                <i class="fi fi-sr-time-quarter-past text-lg text-orange-600"></i>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-400">Pending status</p>
                    </div>

                    <!-- Rejected -->
                    <div
                        class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Rejected Showcases</p>
                                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($stats->rejected) }}</h2>
                                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-red-100 mt-2 w-fit">
                                    <i class="fi fi-sr-cross-circle text-[10px] text-red-600"></i>
                                    <span class="text-[11px] font-semibold text-red-600">Marked invalid</span>
                                </div>
                            </div>
                            <div class="stat-icon-wrap bg-red-100 p-2.5 rounded-lg">
                                <i class="fi fi-sr-cross-circle text-lg text-red-600"></i>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-400">Rejected status</p>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div
                    class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div
                            class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                            <i class="fi fi-rr-filter text-gray-500"></i>
                            <span>Filter By</span>
                        </div>

                        <!-- Status Filter -->
                        <div class="relative">
                            <div
                                class="flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                                <div
                                    class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                                    <i class="fi fi-rr-apps text-white text-sm leading-none"></i>
                                    <span
                                        class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">Status</span>
                                </div>
                                <select id="statusFilter"
                                    class="appearance-none bg-white text-sm text-gray-700 font-medium pl-3 pr-8 py-2.5 focus:outline-none cursor-pointer min-w-[140px] border-0">
                                    <option value="">All Status</option>
                                    <option value="approved">Approved</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                                <i
                                    class="fi fi-rr-angle-small-down absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- From -->
                            <div
                                class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                                <div
                                    class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                                    <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                                    <span
                                        class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">From</span>
                                </div>
                                <input type="date" id="dateFrom"
                                    class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
                            </div>

                            <span class="text-gray-300 font-bold text-lg">→</span>

                            <!-- To -->
                            <div
                                class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                                <div
                                    class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                                    <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                                    <span
                                        class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
                                </div>
                                <input type="date" id="dateTo"
                                    class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
                            </div>
                        </div>
                    </div>

                    <button id="resetFilters"
                        class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                        <i class="fi fi-rr-rotate-left"></i>
                        <span>Reset Filter</span>
                    </button>
                </div>

                <!-- Table Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed" id="showcaseTable">
                            <thead>
                                <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[22%]">
                                        Contractor</th>
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[28%]">
                                        Showcase Title</th>
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">
                                        Linked Project</th>
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">
                                        Date Posted</th>
                                    <th
                                        class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">
                                        Status</th>
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="showcaseTableBody">
                                @include('admin.projectManagement.partials.showcaseTable')
                            </tbody>
                        </table>
                    </div>

                    <div id="paginationLinks">
                        @include('admin.projectManagement.partials.showcasePagination', ['showcases' => $showcases])
                    </div>
                </div>

            </div>

        </main>

    </div>

    <!-- Universal File Viewer (UFV) -->
    <div id="documentViewerModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] hidden items-center justify-center p-4">
        <div class="bg-[#1e1e2e] rounded-[1.25rem] shadow-[0_30px_90px_rgba(0,0,0,0.75)] max-w-5xl w-full h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 flex flex-col modal-shell">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-3 bg-[#16162a] border-b border-white/5 gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <i class="fi fi-rr-file-document text-orange-500 text-lg"></i>
                    <h3 id="documentViewerTitle" class="text-sm font-semibold text-gray-200 truncate">Document Viewer</h3>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a id="documentViewerDownload" href="#" download class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-gray-400 hover:bg-orange-500/40 hover:text-white transition-all" title="Download">
                        <i class="fi fi-rr-download"></i>
                    </a>
                    <button id="closeDocumentViewerBtn" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-gray-400 hover:bg-red-500/40 hover:text-white transition-all" title="Close">
                        <i class="fi fi-rr-cross text-sm"></i>
                    </button>
                </div>
            </div>
            <!-- Viewport -->
            <div class="flex-1 bg-[#0d0d18] relative flex items-center justify-center overflow-hidden p-4">
                <img id="documentViewerImg" src="" alt="Document" class="max-w-full max-h-full object-contain hidden" />
                <iframe id="documentViewerFrame" src="" class="w-full h-full hidden border-0 bg-white rounded-lg"></iframe>
            </div>
        </div>
    </div>

    <!-- View Showcase Modal -->
    <div id="viewShowcaseModal" data-status="pending"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2 sm:p-3">
        <div
            class="modal-content bg-white w-full max-w-3xl rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[84vh] flex flex-col">
            <!-- Header -->
            <div id="viewShowcaseHeader"
                class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-orange-500 to-orange-600 border-b border-orange-600 text-white flex-shrink-0">
                <div class="flex items-center gap-2.5">
                    <div id="viewShowcaseHeaderIcon"
                        class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow-sm">
                        <i class="fi fi-rr-picture text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold leading-tight">Showcase Details</h3>
                        <p id="viewShowcaseHeaderSubtitle" class="text-[10px] text-orange-100">Review showcase information</p>
                    </div>
                </div>
                <button id="viewShowcaseHeaderClose"
                    class="p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition close-modal">
                    <i class="fi fi-rr-cross-small text-lg"></i>
                </button>
            </div>

            <!-- Body: populated by server-rendered HTML via AJAX -->
            <div class="modal-scroll-hidden flex-1 min-h-0 overflow-y-auto p-3.5 sm:p-4 space-y-3" id="modalBodyContent">
                <div class="flex items-center justify-center py-10">
                    <span class="text-sm text-gray-500 font-medium">Loading showcase details...</span>
                </div>
            </div>

            <!-- Footer -->
            <div id="viewShowcaseFooter"
                class="flex items-center justify-end gap-2 px-4 py-3 bg-gray-50 border-t border-gray-200 flex-shrink-0">
                <button id="viewModalCloseBtn"
                    class="px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-[12px] font-medium close-modal">Close</button>
                <button id="viewModalRejectBtn"
                    class="hidden px-3.5 py-2 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition text-[12px] font-medium">Reject</button>
                <button id="viewModalApproveBtn"
                    class="hidden px-3.5 py-2 rounded-lg bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 transition text-[12px] font-medium">Approve</button>
            </div>
        </div>
    </div>

    <!-- Approve & Reject Modals (extracted to partial) -->
    @include('admin.projectManagement.partials.approveRejectModal')

    <!-- Delete & Restore Modals -->
    @include('admin.projectManagement.partials.deleteRestoreModal')

    <script src="{{ asset('js/admin/reusables/filters.js') }}" defer></script>
    <script src="{{ asset('js/admin/projectManagement/showcaseManagement.js') }}" defer></script>

</body>

</html>