<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project Analytics — Legatura Admin</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/projectAnalytics.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">

  @include('admin.layouts.sidebar')

  <main class="flex-1 overflow-x-hidden">
    @include('admin.layouts.topnav', ['pageTitle' => 'Project Analytics'])

    <div class="an-shell p-4 lg:p-5 space-y-3.5">

      {{-- DATE FILTER --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm" id="globalDateFilter">
        <div class="flex items-center gap-3 flex-wrap">
          <span class="text-xs font-semibold text-gray-600 whitespace-nowrap flex items-center gap-1.5">
            <i class="fi fi-sr-calendar" style="font-size:.85rem; vertical-align:middle;"></i>
            Filter Period
          </span>
          <div class="flex gap-1.5 flex-wrap" id="presetButtons">
            <button class="date-preset-btn px-2.5 py-1 rounded-full border border-gray-200 text-xs font-medium text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="last3months">Last 3 Months</button>
            <button class="date-preset-btn px-2.5 py-1 rounded-full border border-gray-200 text-xs font-medium text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="last6months">Last 6 Months</button>
            <button class="date-preset-btn px-2.5 py-1 rounded-full border border-gray-200 text-xs font-medium text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="thisyear">This Year</button>
            <button class="date-preset-btn px-2.5 py-1 rounded-full border border-gray-200 text-xs font-medium text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="lastyear">Last Year</button>
            <button class="date-preset-btn active px-2.5 py-1 rounded-full border border-indigo-400 text-xs font-semibold text-white bg-indigo-500 transition-all" data-range="all">All Time</button>
          </div>
          <div class="flex items-center gap-2 ml-auto flex-wrap">
            <div class="date-pill flex items-center rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
              <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2 self-stretch">
                <i class="fi fi-rr-calendar text-white text-xs leading-none"></i>
                <span class="text-[10px] font-bold text-indigo-100 uppercase tracking-wider select-none">From</span>
              </div>
              <input type="date" id="dateFrom" class="bg-white text-xs text-gray-700 font-medium px-3 py-2 focus:outline-none cursor-pointer min-w-0 border-0 outline-none">
            </div>
            <span class="text-gray-300 font-bold text-base">→</span>
            <div class="date-pill flex items-center rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
              <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2 self-stretch">
                <i class="fi fi-rr-calendar text-white text-xs leading-none"></i>
                <span class="text-[10px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
              </div>
              <input type="date" id="dateTo" class="bg-white text-xs text-gray-700 font-medium px-3 py-2 focus:outline-none cursor-pointer min-w-0 border-0 outline-none">
            </div>
            <button id="resetDateFilter" class="px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 transition-colors flex items-center gap-1.5">
              <i class="fi fi-rr-rotate-left text-[10px]"></i>
              Reset
            </button>
          </div>
          <div id="filterLoading" class="hidden flex items-center gap-1 ml-1">
            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0s"></span>
            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0.1s"></span>
            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0.2s"></span>
          </div>
        </div>
      </div>

      {{-- SECTION: OVERVIEW --}}
      <div class="an-section-label">
        <span>Overview</span>
        <div class="an-section-line"></div>
      </div>

      <div class="an-grid-3 grid grid-cols-1 lg:grid-cols-3 gap-3">

        {{-- Projects Donut --}}
        <div class="an-card bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <div class="bg-slate-100 p-2 rounded-lg">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              </div>
              <h2 class="text-sm font-semibold text-gray-800">Projects</h2>
            </div>
            <span class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">Overview</span>
          </div>
          <div class="projects-analytics-content">
            <div class="projects-chart-wrapper">
              <canvas id="projectsDonutChart"
                data-labels='{{ json_encode(array_column($projectsAnalytics["data"], "label")) }}'
                data-values='{{ json_encode(array_column($projectsAnalytics["data"], "count")) }}'>
              </canvas>
            </div>
            <div class="projects-legend space-y-1.5">
              @foreach($projectsAnalytics['data'] as $index => $item)
              <div class="legend-item cursor-pointer hover:bg-gray-50 px-2 py-1.5 rounded-lg transition-colors" data-index="{{ $index }}">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <div class="legend-dot w-3 h-3 rounded-full" data-color="{{ $index }}"></div>
                    <span class="legend-label text-xs text-gray-600 font-medium">{{ $item['label'] }}</span>
                  </div>
                  <span class="legend-count text-sm font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded-full">{{ $item['count'] }}</span>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Success Rate --}}
        <div class="an-card bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <div class="flex items-center gap-2 mb-3">
            <div class="bg-slate-100 p-2 rounded-lg">
              <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
              <h2 class="text-sm font-semibold text-gray-800">Project Success Rate</h2>
              <p class="text-[11px] text-gray-400">Completion rate by project type</p>
            </div>
          </div>
          <div class="success-rate-content">
            <div class="success-rate-chart-wrapper">
              <canvas id="projectSuccessRateChart"
                data-labels='{{ json_encode(array_column($projectSuccessRate["data"], "label")) }}'
                data-values='{{ json_encode(array_column($projectSuccessRate["data"], "count")) }}'
                data-colors='{{ json_encode(array_column($projectSuccessRate["data"], "color")) }}'>
              </canvas>
            </div>
            <div class="success-rate-legend space-y-1.5">
              @foreach($projectSuccessRate['data'] as $index => $item)
              <div class="success-rate-legend-item cursor-pointer hover:bg-gray-50 px-2 py-1.5 rounded-lg transition-colors" data-index="{{ $index }}">
                <div class="flex items-center gap-2">
                  <div class="success-rate-legend-line h-1 rounded-full" style="background: {{ $item['color'] }}; width: 32px;"></div>
                  <span class="success-rate-legend-label text-xs text-gray-600 font-medium flex-1">{{ $item['label'] }}</span>
                  <span class="text-[11px] font-bold px-1.5 py-0.5 rounded-full" style="background: {{ $item['color'] }}20; color: {{ $item['color'] }}">{{ $item['percentage'] }}%</span>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Projects Timeline --}}
        <div class="an-card bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <div class="bg-slate-100 p-2 rounded-lg">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
              </div>
              <h2 class="text-sm font-semibold text-gray-800">Projects Timeline</h2>
            </div>
            <div class="timeline-date-picker-wrapper">
              <div class="timeline-date-picker bg-white border border-gray-200 rounded-lg px-3 py-1.5 flex items-center gap-2 cursor-pointer hover:border-gray-300 transition-colors">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="timeline-date-range text-xs font-medium text-gray-600">{{ $projectsTimeline['dateRange'] }}</span>
                <button class="timeline-dropdown-btn" id="timelineDropdownBtn">
                  <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
              </div>
              <div class="timeline-dropdown-menu" id="timelineDropdownMenu">
                <button class="timeline-dropdown-item" data-range="last3months">Last 3 Months</button>
                <button class="timeline-dropdown-item active" data-range="last6months">Last 6 Months</button>
                <button class="timeline-dropdown-item" data-range="thisyear">This Year</button>
                <button class="timeline-dropdown-item" data-range="lastyear">Last Year</button>
              </div>
            </div>
          </div>
          <div class="timeline-legend flex gap-3 mb-3">
            <div class="timeline-legend-item cursor-pointer flex items-center gap-1.5 px-2 py-1 rounded-lg hover:bg-gray-50 transition-colors" data-dataset="0">
              <span class="timeline-legend-dot w-2.5 h-2.5 rounded-full" style="background:#fb923c;"></span>
              <span class="timeline-legend-label text-xs font-medium text-gray-600">New Projects</span>
            </div>
            <div class="timeline-legend-item cursor-pointer flex items-center gap-1.5 px-2 py-1 rounded-lg hover:bg-gray-50 transition-colors" data-dataset="1">
              <span class="timeline-legend-dot w-2.5 h-2.5 rounded-full" style="background:#818cf8;"></span>
              <span class="timeline-legend-label text-xs font-medium text-gray-600">Completed</span>
            </div>
          </div>
          <div class="timeline-chart-container">
            <canvas id="projectsTimelineChart"
              data-months='{{ json_encode($projectsTimeline["months"]) }}'
              data-new='{{ json_encode($projectsTimeline["newProjects"]) }}'
              data-completed='{{ json_encode($projectsTimeline["completedProjects"]) }}'>
            </canvas>
          </div>
        </div>

      </div>

      {{-- SECTION: PERFORMANCE KPIs --}}
      <div class="an-section-label">
        <span>Performance KPIs</span>
        <div class="an-section-line"></div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

        {{-- Total Projects KPI --}}
        <div class="an-kpi bg-white border border-gray-200 border-l-4 border-l-indigo-400 rounded-xl p-4 shadow-sm">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-indigo-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            @if($projectPerformance['mom_growth'] != 0)
            <span class="text-[11px] font-bold {{ $projectPerformance['mom_growth'] > 0 ? 'text-emerald-600 bg-emerald-50' : 'text-red-500 bg-red-50' }} px-2 py-0.5 rounded-full flex items-center gap-1">
              @if($projectPerformance['mom_growth'] > 0)
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
              @else
                <svg class="w-3 h-3 rotate-180" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
              @endif
              {{ abs($projectPerformance['mom_growth']) }}%
            </span>
            @endif
          </div>
          <div class="text-2xl font-bold text-gray-900 perf-counter" data-target="{{ $projectPerformance['total_projects'] }}">0</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Total Projects</div>
          <div class="an-progress-wrap mt-2.5">
            <div class="an-progress-fill progress-bar-fill" data-width="{{ min(100, $projectPerformance['total_projects']) }}" style="width:0%; background:#818cf8;"></div>
          </div>
        </div>

        {{-- Completed Projects KPI --}}
        <div class="an-kpi bg-white border border-gray-200 border-l-4 border-l-emerald-400 rounded-xl p-4 shadow-sm">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-emerald-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-[11px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $projectPerformance['completion_rate'] }}% rate</span>
          </div>
          <div class="text-2xl font-bold text-gray-900 perf-counter" data-target="{{ $projectPerformance['completed_projects'] }}">0</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Completed</div>
          <div class="an-progress-wrap mt-2.5">
            <div class="an-progress-fill progress-bar-fill" data-width="{{ $projectPerformance['completion_rate'] }}" style="width:0%; background:#34d399;"></div>
          </div>
        </div>

        {{-- Total Bids KPI --}}
        <div class="an-kpi bg-white border border-gray-200 border-l-4 border-l-amber-400 rounded-xl p-4 shadow-sm">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-amber-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-[11px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $bidMetrics['avg_per_project'] }} avg/project</span>
          </div>
          <div class="text-2xl font-bold text-gray-900 perf-counter" data-target="{{ $bidMetrics['total'] }}">0</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Total Bids</div>
          <div class="an-progress-wrap mt-2.5">
            <div class="an-progress-fill progress-bar-fill" data-width="{{ $bidMetrics['acceptance_rate'] }}" style="width:0%; background:#fbbf24;"></div>
          </div>
        </div>

      </div>

      {{-- PERFORMANCE METRIC DETAILS --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

        <div class="an-metric bg-white border border-gray-200 border-l-4 border-l-cyan-400 rounded-xl p-4 shadow-sm">
          <div class="flex items-center justify-between mb-2.5">
            <div class="bg-cyan-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-[11px] font-semibold text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded-full">Per milestone plan</span>
          </div>
          <div class="text-[11px] text-gray-500 font-medium mb-1">Average Project Duration</div>
          <div class="text-xl font-bold text-gray-800"><span class="perf-counter" data-target="{{ $projectPerformance['avg_duration'] }}">0</span> <span class="text-sm font-normal text-gray-400">days</span></div>
          <div class="an-progress-wrap mt-2.5">
            <div class="an-progress-fill progress-bar-fill" data-width="{{ min(100, round($projectPerformance['avg_duration'])) }}" style="width:0%; background:#22d3ee;"></div>
          </div>
          <p class="text-[11px] text-gray-400 mt-1.5">Based on milestone start → end dates</p>
        </div>

        <div class="an-metric bg-white border border-gray-200 border-l-4 border-l-emerald-400 rounded-xl p-4 shadow-sm">
          <div class="flex items-center justify-between mb-2.5">
            <div class="bg-emerald-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">{{ $projectPerformance['completed_projects'] }} / {{ $projectPerformance['total_projects'] }}</span>
          </div>
          <div class="text-[11px] text-gray-500 font-medium mb-1">Completion Rate</div>
          <div class="text-xl font-bold text-gray-800"><span class="perf-counter" data-target="{{ $projectPerformance['completion_rate'] }}">0</span><span class="text-sm font-normal text-gray-400">%</span></div>
          <div class="an-progress-wrap mt-2.5">
            <div class="an-progress-fill progress-bar-fill" data-width="{{ $projectPerformance['completion_rate'] }}" style="width:0%; background:#34d399;"></div>
          </div>
          <p class="text-[11px] text-gray-400 mt-1.5">Completed out of all non-deleted projects</p>
        </div>

        <div class="an-metric bg-white border border-gray-200 border-l-4 border-l-amber-400 rounded-xl p-4 shadow-sm">
          <div class="flex items-center justify-between mb-2.5">
            <div class="bg-amber-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">Milestone-based</span>
          </div>
          <div class="text-[11px] text-gray-500 font-medium mb-1">On-Time Delivery</div>
          <div class="text-xl font-bold text-gray-800"><span class="perf-counter" data-target="{{ $projectPerformance['on_time_rate'] }}">0</span><span class="text-sm font-normal text-gray-400">%</span></div>
          <div class="an-progress-wrap mt-2.5">
            <div class="an-progress-fill progress-bar-fill" data-width="{{ $projectPerformance['on_time_rate'] }}" style="width:0%; background:#fbbf24;"></div>
          </div>
          <p class="text-[11px] text-gray-400 mt-1.5">Milestones completed within scheduled date</p>
        </div>

      </div>

      {{-- TREND & CATEGORY CHARTS --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        <div class="an-chart-box bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Project Completion Trends</h3>
              <p class="text-[11px] text-gray-400 mt-0.5">Monthly new vs completed — last 12 months</p>
            </div>
            <span class="text-[11px] font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-lg">Last 12 Months</span>
          </div>
          <div style="height:260px;">
            <canvas id="completionTrendsChart"
              data-months='{{ json_encode(array_column($projectPerformance["completion_trends"], "month")) }}'
              data-new='{{ json_encode(array_column($projectPerformance["completion_trends"], "new")) }}'
              data-completed='{{ json_encode(array_column($projectPerformance["completion_trends"], "completed")) }}'>
            </canvas>
          </div>
        </div>

        <div class="an-chart-box bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <div class="flex items-center justify-between mb-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Projects by Property Type</h3>
              <p class="text-[11px] text-gray-400 mt-0.5">Distribution across Residential, Commercial, etc.</p>
            </div>
          </div>
          <div style="height:260px;">
            <canvas id="categoryPerformanceChart"
              data-labels='{{ json_encode(array_keys($projectPerformance["by_property_type"])) }}'
              data-values='{{ json_encode(array_values($projectPerformance["by_property_type"])) }}'>
            </canvas>
          </div>
        </div>

      </div>

      {{-- SECTION: BID ANALYTICS --}}
      <div class="an-section-label">
        <span>Bid Analytics</span>
        <div class="an-section-line"></div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @php
          $bidCards = [
            ['label' => 'Total Bids',    'value' => $bidMetrics['total'],               'accent' => 'text-slate-700',   'bg' => 'bg-slate-50'],
            ['label' => 'Accepted',      'value' => $bidMetrics['accepted'],             'accent' => 'text-emerald-700', 'bg' => 'bg-emerald-50'],
            ['label' => 'Rejected',      'value' => $bidMetrics['rejected'],             'accent' => 'text-red-600',     'bg' => 'bg-red-50'],
            ['label' => 'Pending',       'value' => $bidMetrics['pending'],              'accent' => 'text-amber-700',   'bg' => 'bg-amber-50'],
            ['label' => 'Cancelled',     'value' => $bidMetrics['cancelled'],            'accent' => 'text-gray-600',    'bg' => 'bg-gray-50'],
            ['label' => 'Acceptance %',  'value' => $bidMetrics['acceptance_rate'].'%',  'accent' => 'text-indigo-700',  'bg' => 'bg-indigo-50'],
          ];
        @endphp
        @foreach($bidCards as $card)
        <div class="bg-white border border-gray-200 rounded-xl p-3.5 shadow-sm text-center hover:shadow-md transition-shadow">
          <p class="text-xl font-bold {{ $card['accent'] }}">{{ $card['value'] }}</p>
          <p class="text-[11px] text-gray-400 font-medium mt-0.5">{{ $card['label'] }}</p>
        </div>
        @endforeach
      </div>

      {{-- SECTION: TOP PERFORMING CONTRACTORS --}}
      <div class="an-section-label">
        <span>Top Performing Contractors</span>
        <div class="an-section-line"></div>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" id="topContractorsCard">

        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
          <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Top 5 Contractors</h3>
              <p class="text-[11px] text-gray-400">Ranked by completed projects · live data</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <input type="text" id="contractorSearch" placeholder="Search contractor..." class="px-3 py-1.5 rounded-lg text-xs border border-gray-200 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 outline-none w-40">
              <div class="date-pill flex items-center rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-1.5 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-[10px] leading-none"></i>
                  <span class="text-[9px] font-bold text-indigo-100 uppercase tracking-wider select-none">From</span>
                </div>
                <input type="date" id="contractorDateFrom" class="bg-white text-xs text-gray-700 font-medium px-2.5 py-1.5 focus:outline-none cursor-pointer min-w-0 border-0 outline-none">
              </div>
              <span class="text-gray-300 font-bold">→</span>
              <div class="date-pill flex items-center rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-1.5 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-[10px] leading-none"></i>
                  <span class="text-[9px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
                </div>
                <input type="date" id="contractorDateTo" class="bg-white text-xs text-gray-700 font-medium px-2.5 py-1.5 focus:outline-none cursor-pointer min-w-0 border-0 outline-none">
              </div>
              <button id="contractorFilterBtn" class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-red-600 transition-colors flex items-center gap-1.5">
                <i class="fi fi-rr-rotate-left text-[10px]"></i>
                Reset
              </button>
            </div>
          </div>
        </div>

        <div id="contractorsTableWrap">
        @if(count($topContractors) > 0)
        <div class="overflow-x-auto">
          <table class="an-contractors-table w-full">
            <thead>
              <tr>
                <th>Rank</th>
                <th>Contractor</th>
                <th>Completed</th>
                <th>Success Rate</th>
                <th>Experience</th>
                <th>Avg Rating</th>
              </tr>
            </thead>
            <tbody>
              @foreach($topContractors as $contractor)
              @php
                $rankColors = [
                  1 => 'bg-amber-400 text-white',
                  2 => 'bg-gray-300 text-white',
                  3 => 'bg-orange-300 text-white',
                ];
                $rankStyle = $rankColors[$contractor['rank']] ?? 'bg-gray-100 text-gray-500';
                $avatarColors = ['bg-blue-100 text-blue-700','bg-purple-100 text-purple-700','bg-emerald-100 text-emerald-700','bg-orange-100 text-orange-700','bg-red-100 text-red-700'];
                $avatarColor = $avatarColors[($contractor['rank'] - 1) % count($avatarColors)];
              @endphp
              <tr class="cursor-pointer">
                <td>
                  <div class="an-rank-badge {{ $rankStyle }}">{{ $contractor['rank'] }}</div>
                </td>
                <td>
                  <div class="flex items-center gap-2.5">
                    @if($contractor['company_logo'])
                      <img src="{{ asset('storage/' . $contractor['company_logo']) }}" alt="" class="w-8 h-8 rounded-full object-cover">
                    @else
                      <div class="w-8 h-8 {{ $avatarColor }} rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0">{{ $contractor['initials'] }}</div>
                    @endif
                    <div>
                      <p class="text-sm font-semibold text-gray-800">{{ $contractor['company_name'] }}</p>
                      <p class="text-[11px] text-gray-400">{{ $contractor['rep_name'] }}</p>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="bid-pill bg-blue-50 text-blue-600">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ $contractor['completed_projects'] }} done
                  </span>
                </td>
                <td>
                  <div class="flex items-center gap-2 min-w-[110px]">
                    <div class="flex-1 bg-gray-100 rounded-full h-1.5 w-20">
                      <div class="bg-emerald-400 h-1.5 rounded-full" style="width: {{ $contractor['success_rate'] }}%"></div>
                    </div>
                    <span class="text-xs font-bold text-gray-700 w-9 text-right">{{ $contractor['success_rate'] }}%</span>
                  </div>
                </td>
                <td class="text-sm text-gray-600 font-medium">{{ $contractor['years_of_experience'] }} yrs</td>
                <td>
                  @if($contractor['avg_rating'] > 0)
                  <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span class="text-xs font-bold text-gray-700">{{ $contractor['avg_rating'] }}</span>
                    <span class="text-[11px] text-gray-400">({{ $contractor['review_count'] }})</span>
                  </div>
                  @else
                    <span class="text-xs text-gray-400 italic">No reviews</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="py-12 text-center">
          <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <p class="text-sm text-gray-400">No contractor data yet</p>
        </div>
        @endif
        </div>

      </div>

    </div>
  </main>
</div>

<script src="{{ asset('js/admin/home/analytics.js') }}" defer></script>
</body>
</html>
