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

    <div class="p-6 space-y-8 max-w-[1600px] mx-auto">

      {{-- ═══════════════════════════════════════════════════════════
           GLOBAL DATE FILTER
      ═══════════════════════════════════════════════════════════ --}}
      <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm" id="globalDateFilter">
        <div class="flex items-center gap-4 flex-wrap">
          <span class="text-sm font-semibold text-gray-700 whitespace-nowrap">
            <i class="fi fi-sr-calendar" style="font-size:1rem; vertical-align:middle; margin-right:.35rem;"></i>
            Filter Period:
          </span>
          <div class="flex gap-2 flex-wrap" id="presetButtons">
            <button class="date-preset-btn px-3 py-1.5 rounded-full border border-gray-200 text-sm font-medium text-gray-600 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="last3months">Last 3 Months</button>
            <button class="date-preset-btn px-3 py-1.5 rounded-full border border-gray-200 text-sm font-medium text-gray-600 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="last6months">Last 6 Months</button>
            <button class="date-preset-btn px-3 py-1.5 rounded-full border border-gray-200 text-sm font-medium text-gray-600 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="thisyear">This Year</button>
            <button class="date-preset-btn px-3 py-1.5 rounded-full border border-gray-200 text-sm font-medium text-gray-600 hover:border-indigo-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="lastyear">Last Year</button>
            <button class="date-preset-btn active px-3 py-1.5 rounded-full border border-indigo-500 text-sm font-semibold text-white bg-indigo-500 transition-all" data-range="all">All Time</button>
          </div>
          <div class="flex items-center gap-2 ml-auto">
            <label class="text-xs font-medium text-gray-500">From</label>
            <input type="date" id="dateFrom" class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
            <label class="text-xs font-medium text-gray-500">To</label>
            <input type="date" id="dateTo" class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
            <button id="applyDateFilter" class="px-3 py-1.5 bg-indigo-500 text-white text-sm font-semibold rounded-lg hover:bg-indigo-600 transition-colors">Apply</button>
          </div>
          <div id="filterLoading" class="hidden flex items-center gap-1 ml-2">
            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-bounce" style="animation-delay:0s"></span>
            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-bounce" style="animation-delay:0.1s"></span>
            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-bounce" style="animation-delay:0.2s"></span>
          </div>
        </div>
      </div>

      {{-- ═══════════════════════════════════════════════════════════
           SECTION 1 — OVERVIEW (donut, success rate, timeline)
      ═══════════════════════════════════════════════════════════ --}}
      <div class="section-divider">
        <h2>Overview</h2><span></span>
      </div>

      <div class="analytics-container">
        {{-- Projects Donut --}}
        <div class="analytics-card group bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative">
            <div class="flex items-center justify-between mb-6">
              <h2 class="analytics-card-title text-2xl font-bold text-gray-800 flex items-center gap-3">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <span>Projects</span>
              </h2>
              <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">Overview</span>
            </div>
            <div class="projects-analytics-content">
              <div class="projects-chart-wrapper">
                <canvas id="projectsDonutChart"
                  data-labels='{{ json_encode(array_column($projectsAnalytics["data"], "label")) }}'
                  data-values='{{ json_encode(array_column($projectsAnalytics["data"], "count")) }}'>
                </canvas>
              </div>
              <div class="projects-legend space-y-2">
                @foreach($projectsAnalytics['data'] as $index => $item)
                <div class="legend-item group/item cursor-pointer hover:bg-blue-50 p-3 rounded-lg transition-all duration-300 hover:scale-105 hover:shadow-md" data-index="{{ $index }}">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <div class="legend-dot w-4 h-4 rounded-full shadow-md group-hover/item:scale-125 transition-transform duration-300" data-color="{{ $index }}"></div>
                      <span class="legend-label text-gray-700 font-medium group-hover/item:text-blue-600 transition-colors">{{ $item['label'] }}</span>
                    </div>
                    <span class="legend-count text-lg font-bold text-gray-800 bg-white px-3 py-1 rounded-full shadow-sm group-hover/item:bg-blue-100 group-hover/item:text-blue-700 transition-all">{{ $item['count'] }}</span>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        {{-- Success Rate Pie --}}
        <div class="analytics-card group bg-gradient-to-br from-white to-purple-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-purple-400/5 to-pink-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative">
            <div class="mb-6">
              <div class="flex items-center gap-3 mb-2">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="analytics-card-title text-2xl font-bold text-gray-800">Project Success Rate</h2>
              </div>
              <p class="analytics-card-subtitle text-sm text-gray-600 ml-14 flex items-center gap-2">
                <span class="inline-block w-1.5 h-1.5 bg-purple-400 rounded-full"></span>
                Completion rate by project type
              </p>
            </div>
            <div class="success-rate-content">
              <div class="success-rate-chart-wrapper">
                <canvas id="projectSuccessRateChart"
                  data-labels='{{ json_encode(array_column($projectSuccessRate["data"], "label")) }}'
                  data-values='{{ json_encode(array_column($projectSuccessRate["data"], "count")) }}'
                  data-colors='{{ json_encode(array_column($projectSuccessRate["data"], "color")) }}'>
                </canvas>
              </div>
              <div class="success-rate-legend space-y-3">
                @foreach($projectSuccessRate['data'] as $index => $item)
                <div class="success-rate-legend-item group/rate cursor-pointer hover:bg-purple-50 p-3 rounded-lg transition-all duration-300 hover:scale-105 hover:shadow-md" data-index="{{ $index }}">
                  <div class="flex items-center gap-3">
                    <div class="success-rate-legend-line h-1 rounded-full shadow-md" style="background: {{ $item['color'] }}; width: 40px;"></div>
                    <span class="success-rate-legend-label text-gray-700 font-medium group-hover/rate:text-purple-600 transition-colors flex-1">{{ $item['label'] }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background: {{ $item['color'] }}20; color: {{ $item['color'] }}">{{ $item['percentage'] }}%</span>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        {{-- Projects Timeline --}}
        <div class="analytics-card projects-timeline-card group bg-gradient-to-br from-white to-emerald-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-emerald-400/5 to-teal-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative">
            <div class="timeline-header flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                </div>
                <h2 class="analytics-card-title text-2xl font-bold text-gray-800">Projects Timeline</h2>
              </div>
              <div class="timeline-date-picker-wrapper">
                <div class="timeline-date-picker bg-white shadow-md hover:shadow-lg transition-shadow duration-300 rounded-lg px-4 py-2 flex items-center gap-3 cursor-pointer border border-gray-200 hover:border-emerald-300">
                  <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span class="timeline-date-range font-semibold text-gray-700">{{ $projectsTimeline['dateRange'] }}</span>
                  <button class="timeline-dropdown-btn hover:scale-110 transition-transform" id="timelineDropdownBtn">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                </div>
                <div class="timeline-dropdown-menu shadow-xl" id="timelineDropdownMenu">
                  <button class="timeline-dropdown-item hover:bg-emerald-50 transition-colors" data-range="last3months">Last 3 Months</button>
                  <button class="timeline-dropdown-item active bg-emerald-100 text-emerald-700" data-range="last6months">Last 6 Months</button>
                  <button class="timeline-dropdown-item hover:bg-emerald-50 transition-colors" data-range="thisyear">This Year</button>
                  <button class="timeline-dropdown-item hover:bg-emerald-50 transition-colors" data-range="lastyear">Last Year</button>
                </div>
              </div>
            </div>
            <div class="timeline-legend flex gap-4 mb-6">
              <div class="timeline-legend-item group/legend cursor-pointer bg-white hover:bg-orange-50 px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2" data-dataset="0">
                <span class="timeline-legend-dot w-3 h-3 rounded-full" style="background:#fb923c;"></span>
                <span class="timeline-legend-label text-sm font-semibold text-gray-700 group-hover/legend:text-orange-600 transition-colors">New Projects</span>
              </div>
              <div class="timeline-legend-item group/legend cursor-pointer bg-white hover:bg-indigo-50 px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2" data-dataset="1">
                <span class="timeline-legend-dot w-3 h-3 rounded-full" style="background:#818cf8;"></span>
                <span class="timeline-legend-label text-sm font-semibold text-gray-700 group-hover/legend:text-indigo-600 transition-colors">Completed Projects</span>
              </div>
            </div>
            <div class="timeline-chart-container bg-white rounded-xl p-4 shadow-inner">
              <canvas id="projectsTimelineChart"
                data-months='{{ json_encode($projectsTimeline["months"]) }}'
                data-new='{{ json_encode($projectsTimeline["newProjects"]) }}'
                data-completed='{{ json_encode($projectsTimeline["completedProjects"]) }}'>
              </canvas>
            </div>
          </div>
        </div>
      </div>

      {{-- ═══════════════════════════════════════════════════════════
           SECTION 2 — PERFORMANCE KPI HERO CARDS
      ═══════════════════════════════════════════════════════════ --}}
      <div class="section-divider">
        <h2>Performance KPIs</h2><span></span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- Total Projects --}}
        <div class="perf-card bg-gradient-to-br from-blue-500 to-blue-700 text-white">
          <div class="blob"></div>
          <div class="relative z-10">
            <div class="flex items-start justify-between mb-3">
              <div class="bg-white/20 rounded-xl p-2.5">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
              </div>
              @if($projectPerformance['mom_growth'] != 0)
              <span class="text-xs font-bold bg-white/20 px-2 py-1 rounded-full flex items-center gap-1">
                @if($projectPerformance['mom_growth'] > 0)
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                @else
                  <svg class="w-3 h-3 rotate-180" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                @endif
                {{ abs($projectPerformance['mom_growth']) }}%
              </span>
              @endif
            </div>
            <div class="text-3xl font-black mb-0.5 perf-counter" data-target="{{ $projectPerformance['total_projects'] }}">0</div>
            <div class="text-sm font-medium opacity-80 mb-3">Total Projects</div>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" data-width="{{ min(100, $projectPerformance['total_projects']) }}" style="width:0%"></div></div>
          </div>
        </div>

        {{-- Completed Projects --}}
        <div class="perf-card bg-gradient-to-br from-emerald-500 to-emerald-700 text-white">
          <div class="blob"></div>
          <div class="relative z-10">
            <div class="flex items-start justify-between mb-3">
              <div class="bg-white/20 rounded-xl p-2.5">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <span class="text-xs font-bold bg-white/20 px-2 py-1 rounded-full">{{ $projectPerformance['completion_rate'] }}% rate</span>
            </div>
            <div class="text-3xl font-black mb-0.5 perf-counter" data-target="{{ $projectPerformance['completed_projects'] }}">0</div>
            <div class="text-sm font-medium opacity-80 mb-3">Completed</div>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" data-width="{{ $projectPerformance['completion_rate'] }}" style="width:0%"></div></div>
          </div>
        </div>

        {{-- Total Bids --}}
        <div class="perf-card bg-gradient-to-br from-orange-500 to-orange-700 text-white">
          <div class="blob"></div>
          <div class="relative z-10">
            <div class="flex items-start justify-between mb-3">
              <div class="bg-white/20 rounded-xl p-2.5">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <span class="text-xs font-bold bg-white/20 px-2 py-1 rounded-full">{{ $bidMetrics['avg_per_project'] }} avg/project</span>
            </div>
            <div class="text-3xl font-black mb-0.5 perf-counter" data-target="{{ $bidMetrics['total'] }}">0</div>
            <div class="text-sm font-medium opacity-80 mb-3">Total Bids</div>
            <div class="progress-bar-wrap"><div class="progress-bar-fill" data-width="{{ $bidMetrics['acceptance_rate'] }}" style="width:0%"></div></div>
          </div>
        </div>
      </div>

      {{-- ═══════════════════════════════════════════════════════════
           SECTION 3 — PERFORMANCE DETAIL METRICS
      ═══════════════════════════════════════════════════════════ --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        {{-- Avg Duration --}}
        <div class="metric-sub border-cyan-400">
          <div class="flex items-center justify-between mb-3">
            <div class="bg-gradient-to-br from-cyan-400 to-cyan-500 text-white p-2.5 rounded-xl shadow-md">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-cyan-600 text-xs font-bold bg-cyan-50 px-2.5 py-1 rounded-full">Per milestone plan</span>
          </div>
          <p class="text-gray-500 text-xs font-medium mb-1">Average Project Duration</p>
          <p class="text-3xl font-black text-gray-800"><span class="perf-counter" data-target="{{ $projectPerformance['avg_duration'] }}">0</span> <span class="text-sm font-medium text-gray-500">days</span></p>
          <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3 overflow-hidden">
            <div class="bg-gradient-to-r from-cyan-400 to-cyan-600 h-1.5 rounded-full progress-bar-fill" data-width="{{ min(100, round($projectPerformance['avg_duration'])) }}" style="width:0%"></div>
          </div>
          <p class="text-xs text-gray-400 mt-1.5">Based on milestone start → end dates</p>
        </div>

        {{-- Completion Rate --}}
        <div class="metric-sub border-emerald-400">
          <div class="flex items-center justify-between mb-3">
            <div class="bg-gradient-to-br from-emerald-400 to-emerald-500 text-white p-2.5 rounded-xl shadow-md">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="text-emerald-600 text-xs font-bold bg-emerald-50 px-2.5 py-1 rounded-full">{{ $projectPerformance['completed_projects'] }} / {{ $projectPerformance['total_projects'] }} projects</span>
          </div>
          <p class="text-gray-500 text-xs font-medium mb-1">Completion Rate</p>
          <p class="text-3xl font-black text-gray-800"><span class="perf-counter" data-target="{{ $projectPerformance['completion_rate'] }}">0</span><span class="text-sm font-medium text-gray-500">%</span></p>
          <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3 overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-1.5 rounded-full progress-bar-fill" data-width="{{ $projectPerformance['completion_rate'] }}" style="width:0%"></div>
          </div>
          <p class="text-xs text-gray-400 mt-1.5">Completed out of all non-deleted projects</p>
        </div>

        {{-- On-Time Delivery --}}
        <div class="metric-sub border-amber-400">
          <div class="flex items-center justify-between mb-3">
            <div class="bg-gradient-to-br from-amber-400 to-amber-500 text-white p-2.5 rounded-xl shadow-md">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="text-amber-600 text-xs font-bold bg-amber-50 px-2.5 py-1 rounded-full">Milestone-based</span>
          </div>
          <p class="text-gray-500 text-xs font-medium mb-1">On-Time Delivery</p>
          <p class="text-3xl font-black text-gray-800"><span class="perf-counter" data-target="{{ $projectPerformance['on_time_rate'] }}">0</span><span class="text-sm font-medium text-gray-500">%</span></p>
          <div class="w-full bg-gray-100 rounded-full h-1.5 mt-3 overflow-hidden">
            <div class="bg-gradient-to-r from-amber-400 to-amber-600 h-1.5 rounded-full progress-bar-fill" data-width="{{ $projectPerformance['on_time_rate'] }}" style="width:0%"></div>
          </div>
          <p class="text-xs text-gray-400 mt-1.5">Milestones completed ≤ scheduled end date</p>
        </div>
      </div>

      {{-- ═══════════════════════════════════════════════════════════
           SECTION 4 — TREND & CATEGORY CHARTS
      ═══════════════════════════════════════════════════════════ --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Completion Trends (12-month) --}}
        <div class="chart-box">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="text-lg font-bold text-gray-800">Project Completion Trends</h3>
              <p class="text-xs text-gray-500 mt-0.5">Monthly new vs completed — last 12 months</p>
            </div>
            <span class="bg-gradient-to-br from-blue-500 to-blue-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow">Last 12 Months</span>
          </div>
          <div style="height:300px;">
            <canvas id="completionTrendsChart"
              data-months='{{ json_encode(array_column($projectPerformance["completion_trends"], "month")) }}'
              data-new='{{ json_encode(array_column($projectPerformance["completion_trends"], "new")) }}'
              data-completed='{{ json_encode(array_column($projectPerformance["completion_trends"], "completed")) }}'>
            </canvas>
          </div>
        </div>

        {{-- Performance by Property Type --}}
        <div class="chart-box">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="text-lg font-bold text-gray-800">Projects by Property Type</h3>
              <p class="text-xs text-gray-500 mt-0.5">Distribution across Residential, Commercial, etc.</p>
            </div>
          </div>
          <div style="height:300px;">
            <canvas id="categoryPerformanceChart"
              data-labels='{{ json_encode(array_keys($projectPerformance["by_property_type"])) }}'
              data-values='{{ json_encode(array_values($projectPerformance["by_property_type"])) }}'>
            </canvas>
          </div>
        </div>
      </div>

      {{-- ═══════════════════════════════════════════════════════════
           SECTION 5 — BID ANALYTICS PILLS
      ═══════════════════════════════════════════════════════════ --}}
      <div class="section-divider">
        <h2>Bid Analytics</h2><span></span>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @php
          $bidCards = [
            ['label' => 'Total Bids',    'value' => $bidMetrics['total'],            'bg' => 'bg-slate-100',   'text' => 'text-slate-700'],
            ['label' => 'Accepted',      'value' => $bidMetrics['accepted'],          'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
            ['label' => 'Rejected',      'value' => $bidMetrics['rejected'],          'bg' => 'bg-red-100',     'text' => 'text-red-700'],
            ['label' => 'Pending',       'value' => $bidMetrics['pending'],           'bg' => 'bg-amber-100',   'text' => 'text-amber-700'],
            ['label' => 'Cancelled',     'value' => $bidMetrics['cancelled'],         'bg' => 'bg-gray-100',    'text' => 'text-gray-600'],
            ['label' => 'Acceptance %',  'value' => $bidMetrics['acceptance_rate'].'%', 'bg' => 'bg-blue-100',  'text' => 'text-blue-700'],
          ];
        @endphp
        @foreach($bidCards as $card)
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center hover:shadow-md transition-shadow">
          <p class="text-2xl font-black {{ $card['text'] }}">{{ $card['value'] }}</p>
          <p class="text-xs text-gray-500 font-medium mt-1">{{ $card['label'] }}</p>
        </div>
        @endforeach
      </div>

      {{-- ═══════════════════════════════════════════════════════════
           SECTION 6 — TOP PERFORMING CONTRACTORS
      ═══════════════════════════════════════════════════════════ --}}
      <div class="section-divider">
        <h2>Top Performing Contractors</h2><span></span>
      </div>

      <div class="bg-white rounded-2xl shadow-lg overflow-hidden" id="topContractorsCard">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
          <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
              <h3 class="text-xl font-bold text-white">Top 5 Contractors</h3>
              <p class="text-indigo-100 text-sm mt-0.5">Ranked by completed projects · live data</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <input type="text" id="contractorSearch" placeholder="Search contractor..." class="px-3 py-2 rounded-lg text-sm border-0 focus:ring-2 focus:ring-indigo-300 outline-none w-44" style="background:rgba(255,255,255,.9);">
              <input type="date" id="contractorDateFrom" class="px-2.5 py-2 rounded-lg text-sm border-0 focus:ring-2 focus:ring-indigo-300 outline-none" style="background:rgba(255,255,255,.9);">
              <input type="date" id="contractorDateTo" class="px-2.5 py-2 rounded-lg text-sm border-0 focus:ring-2 focus:ring-indigo-300 outline-none" style="background:rgba(255,255,255,.9);">
              <button id="contractorFilterBtn" class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-indigo-50 transition-colors shadow-md">Filter</button>
            </div>
          </div>
        </div>

        <div id="contractorsTableWrap">
        @if(count($topContractors) > 0)
        <div class="overflow-x-auto">
          <table class="w-full contractors-table">
            <thead>
              <tr>
                <th class="text-left">Rank</th>
                <th class="text-left">Contractor</th>
                <th class="text-left">Completed</th>
                <th class="text-left">Success Rate</th>
                <th class="text-left">Experience</th>
                <th class="text-left">Avg Rating</th>
              </tr>
            </thead>
            <tbody>
              @foreach($topContractors as $contractor)
              @php
                $rankColors = [
                  1 => ['bg' => 'bg-gradient-to-br from-yellow-400 to-yellow-500', 'text' => 'text-white'],
                  2 => ['bg' => 'bg-gradient-to-br from-gray-300 to-gray-400',     'text' => 'text-white'],
                  3 => ['bg' => 'bg-gradient-to-br from-orange-300 to-orange-400', 'text' => 'text-white'],
                ];
                $rankStyle = $rankColors[$contractor['rank']] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600'];
                $avatarColors = ['from-blue-400 to-blue-600','from-purple-400 to-purple-600','from-emerald-400 to-emerald-600','from-orange-400 to-orange-600','from-red-400 to-red-600'];
                $avatarColor = $avatarColors[($contractor['rank'] - 1) % count($avatarColors)];
              @endphp
              <tr class="cursor-pointer">
                <td>
                  <div class="rank-badge {{ $rankStyle['bg'] }} {{ $rankStyle['text'] }}">{{ $contractor['rank'] }}</div>
                </td>
                <td>
                  <div class="flex items-center gap-3">
                    @if($contractor['company_logo'])
                      <img src="{{ asset('storage/' . $contractor['company_logo']) }}" alt="" class="w-10 h-10 rounded-full object-cover shadow">
                    @else
                      <div class="w-10 h-10 bg-gradient-to-br {{ $avatarColor }} rounded-full flex items-center justify-center text-white font-bold text-sm shadow">{{ $contractor['initials'] }}</div>
                    @endif
                    <div>
                      <p class="font-semibold text-gray-800">{{ $contractor['company_name'] }}</p>
                      <p class="text-xs text-gray-500">{{ $contractor['rep_name'] }}</p>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="bid-pill bg-blue-100 text-blue-700">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ $contractor['completed_projects'] }} done
                  </span>
                </td>
                <td>
                  <div class="flex items-center gap-2 min-w-[120px]">
                    <div class="flex-1 bg-gray-100 rounded-full h-1.5 w-20">
                      <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-1.5 rounded-full" style="width: {{ $contractor['success_rate'] }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-gray-700 w-10 text-right">{{ $contractor['success_rate'] }}%</span>
                  </div>
                </td>
                <td class="text-gray-600 font-medium">{{ $contractor['years_of_experience'] }} yrs</td>
                <td>
                  @if($contractor['avg_rating'] > 0)
                  <div class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span class="text-sm font-bold text-gray-700">{{ $contractor['avg_rating'] }}</span>
                    <span class="text-xs text-gray-400">({{ $contractor['review_count'] }})</span>
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
        <div class="py-16 text-center">
          <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <p class="text-gray-400 font-medium">No contractor data yet</p>
        </div>
        @endif
        </div>{{-- /contractorsTableWrap --}}
      </div>

    </div>{{-- /container --}}
  </main>
</div>

{{-- ═══════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════ --}}
<script src="{{ asset('js/admin/home/analytics.js') }}" defer></script>
</body>
</html>