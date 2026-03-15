<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Management - Legatura</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/globalManagement/aiManagement.css') }}">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
    <script src="{{ asset('js/admin/globalManagement/aiManagement.js') }}" defer></script>

    <style>
        /* Tom Select Dropdown Styling */
        .ts-control {
            border-radius: 0.75rem !important;
            padding: 0.625rem 1rem !important;
            background-color: #ffffff !important;
            border: 2px solid #e5e7eb !important;
            transition: all 0.2s ease !important;
        }
        .ts-wrapper.focus .ts-control {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1) !important;
            border-color: #6366f1 !important;
        }
        .ts-wrapper {
            position: relative !important;
        }
        .ts-dropdown {
            border-radius: 0.75rem !important;
            border: 2px solid #e5e7eb !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
            max-height: 180px !important;
            height: auto !important;
            overflow-y: scroll !important;
            overflow-x: hidden !important;
            z-index: 60 !important;
            position: absolute !important;
            left: 0 !important;
            right: 0 !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .ts-dropdown::-webkit-scrollbar {
            display: none !important;
        }
        .ts-dropdown .ts-dropdown-content {
            max-height: 180px !important;
            overflow-y: scroll !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .ts-dropdown .ts-dropdown-content::-webkit-scrollbar {
            display: none !important;
        }
        .ts-dropdown .option {
            padding: 0.625rem 1rem !important;
            transition: background 0.15s ease !important;
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
            height: 45px !important;
            display: flex !important;
            align-items: center !important;
        }
        .ts-dropdown .option:hover,
        .ts-dropdown .option.active {
            background: linear-gradient(90deg, #faf5ff 0%, #f5f3ff 100%) !important;
            color: #6366f1 !important;
        }
        .ts-dropdown .no-results {
            padding: 0.75rem 1rem !important;
            color: #9ca3af !important;
            font-style: italic !important;
            height: 45px !important;
        }
        /* Modal styling */
        .modal-overlay {
            z-index: 50 !important;
        }
        .modal-content {
            position: relative;
            z-index: 51 !important;
        }
        #analysisModalBody {
            position: relative !important;
            overflow: visible !important;
        }
        #projectSelectionStep {
            position: relative !important;
            z-index: 1 !important;
        }

        /* Date Filter Styling */
        .date-pill input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0.5;
            cursor: pointer;
            filter: invert(30%) sepia(80%) saturate(400%) hue-rotate(210deg);
        }

        .date-pill input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        /* Verdict Dropdown Options Styling */
        #verdictFilter option {
            padding: 10px;
            background-color: #ffffff;
        }

        #verdictFilter option:hover {
            background-color: #eef2ff;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">

      @php
        $aiStatusBadge = '<div class="px-3 py-1.5 rounded-full flex items-center gap-1.5 '
            . (($aiUsage['status'] ?? 'Offline') === 'Online' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')
            . '"><span class="w-2.5 h-2.5 rounded-full animate-pulse '
            . (($aiUsage['status'] ?? 'Offline') === 'Online' ? 'bg-green-500' : 'bg-red-500')
            . '"></span><span class="font-semibold text-xs">System: ' . e($aiUsage['status'] ?? 'Offline') . '</span></div>';
      @endphp
      @include('admin.layouts.topnav', [
          'pageTitle' => 'AI Management',
          'pageSubtitle' => 'Predictive Analytics & Risk Assessment Console',
          'hideSearch' => true,
          'beforeNotifications' => $aiStatusBadge,
      ])

        <section class="px-8 py-8 space-y-8">

            {{-- AI Features Banner --}}
            @if(!empty($aiUsage['features']))
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fi fi-rr-sparkles text-indigo-600"></i>
                    <span class="text-xs font-bold text-indigo-900 uppercase tracking-wider">AI Capabilities</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($aiUsage['features'] as $feature)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white text-indigo-700 border border-indigo-200 shadow-sm">
                            <i class="fi fi-rr-check-circle text-[10px]"></i>
                            {{ $feature }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Filter Bar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                            <i class="fi fi-rr-filter text-gray-500"></i>
                            <span>Filter By</span>
                        </div>

                        {{-- Search Input --}}
                        <div class="relative">
                            <input id="searchInput" type="text"
                                placeholder="Search project name…"
                                class="w-64 px-3.5 py-2.5 pr-10 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 focus:outline-none">
                            <i class="fi fi-rr-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-sm"></i>
                        </div>

                        {{-- Date Range --}}
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- From --}}
                            <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                                    <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                                    <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">From</span>
                                </div>
                                <input type="date" id="dateFrom"
                                    class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
                            </div>

                            <span class="text-gray-300 font-bold text-lg">→</span>

                            {{-- To --}}
                            <div>
                                <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition" id="dateToWrapper">
                                    <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                                        <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                                        <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
                                    </div>
                                    <input type="date" id="dateTo"
                                        class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
                                </div>
                                <p id="dateToError" class="hidden text-xs text-red-600 mt-1 flex items-center gap-1">
                                    <i class="fi fi-rr-exclamation text-xs"></i>
                                    <span>End date cannot be earlier than start date</span>
                                </p>
                            </div>
                        </div>

                        {{-- Verdict Filter --}}
                        <div class="flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                            <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                                <i class="fi fi-rr-chart-line-up text-white text-sm leading-none"></i>
                                <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">Verdict</span>
                            </div>
                            <div class="relative">
                                <select id="verdictFilter"
                                    class="appearance-none bg-white text-sm text-gray-700 font-medium px-3 py-2.5 pr-8 focus:outline-none cursor-pointer border-0 min-w-[120px]">
                                    <option value="">All</option>
                                    <option value="DELAYED">Delayed</option>
                                    <option value="ON_TIME">On Time</option>
                                </select>
                                <i class="fi fi-rr-angle-small-down absolute right-2 top-1/2 -translate-y-1/2 text-[13px] text-gray-400 pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button id="resetFilters"
                            class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                            <i class="fi fi-rr-rotate-left"></i>
                            <span>Reset Filter</span>
                        </button>

                        <button onclick="window.aiManagement.openAnalysisModal()"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition flex items-center gap-2">
                            <i class="fi fi-br-chart-histogram text-sm"></i>
                            Analyze Now
                        </button>
                    </div>
                </div>
            </div>

            {{-- Prediction History Logs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" id="predictionTableWrap">
                @include('admin.globalManagement.partials.aiManagementTable', ['predictionLogs' => $predictionLogs])
            </div>

        </section>
    </main>

</div>

{{-- ===================== DELETE CONFIRMATION MODAL ===================== --}}
<div id="deleteModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <i class="fi fi-sr-trash text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Delete Analysis?</h3>
                        <p class="text-red-100 text-sm">This action cannot be undone</p>
                    </div>
                </div>
                <button class="modal-close-delete text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
        </div>

        <div class="p-6 space-y-4">
            <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <i class="fi fi-rr-info-circle text-red-600 text-xl mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm text-red-900 font-semibold mb-1">Confirm deletion</p>
                    <p class="text-xs text-red-800">Are you sure you want to delete the analysis for <strong id="deleteProjectName"></strong>?</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
            <button class="modal-close-delete px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
            <button id="confirmDeleteBtn" onclick="window.aiManagement.deleteAnalysis()" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fi fi-rr-trash text-sm"></i>
                Delete Analysis
            </button>
        </div>
    </div>
</div>

{{-- ===================== ANALYSIS MODAL (Project Selection & Analysis) ===================== --}}
<div id="analysisModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <i class="fi fi-sr-chart-histogram text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Run AI Analysis</h3>
                        <p class="text-indigo-100 text-sm">Select a project for delay risk assessment</p>
                    </div>
                </div>
                <button class="modal-close-analysis text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
        </div>

        <div class="p-6 space-y-6" id="analysisModalBody">
            <!-- Step 1: Project Selection -->
            <div id="projectSelectionStep">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Select Project</label>
                <select id="projectSelectModal" name="project_id" placeholder="Start typing to search for a project..." autocomplete="off" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition text-sm">
                    <option value=""></option>
                    @foreach($projects as $project)
                        <option value="{{ $project->project_id }}">
                            {{ $project->project_id }} - {{ $project->project_title }}
                        </option>
                    @endforeach
                </select>
                <div id="projectSelectError" class="hidden mt-2 text-xs text-red-600 flex items-center gap-1">
                    <i class="fi fi-rr-exclamation text-sm"></i>
                    <span>Please select a project before starting the analysis</span>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="fi fi-rr-info text-indigo-500"></i>
                    Start typing to search and select a project for analysis
                </p>
            </div>

            <!-- Step 2: Analysis Progress/Results -->
            <div id="analysisProgressStep" class="hidden">
                <!-- Content will be dynamically inserted here -->
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
            <button class="modal-close-analysis px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
            <button id="btnStartAnalysis" onclick="window.aiManagement.startAnalysis()" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fi fi-br-play text-xs"></i>
                Start Analysis
            </button>
        </div>
    </div>
</div>

{{-- ===================== DETAILS MODAL (View Analysis Results) ===================== --}}
<div id="detailsModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-5xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <i class="fi fi-sr-chart-histogram text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">AI Strategic Project Audit</h3>
                        <p class="text-indigo-100 text-sm">Comprehensive Risk Analysis Report</p>
                    </div>
                </div>
                <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
        </div>

        <div class="p-6 space-y-6" id="modalBody">
            <!-- Content will be dynamically inserted here -->
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 sticky bottom-0">
            <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Close</button>
            <button onclick="window.print()" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fi fi-rr-print"></i> Print Report
            </button>
        </div>
    </div>
</div>

</body>
</html>
