<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo2.0-favicon.svg') }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/projectPerformance_Analytics.css') }}">

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
      @include('admin.layouts.topnav', ['pageTitle' => 'Project Performance Analytics'])

      <div class="pa-shell p-4 lg:p-5 space-y-3.5">

        <!-- Page Header -->
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 flex items-center justify-between gap-2">
          <div>
            <h2 class="text-sm font-semibold text-gray-800 leading-tight">Performance Overview</h2>
            <p class="text-[11px] text-gray-400 mt-0.5">Analytics summary — projects, trends, and contractor outcomes.</p>
          </div>
          <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full">Analytics</span>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">

          <!-- Total Projects -->
          <div class="pa-kpi bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between mb-3">
              <div class="bg-slate-100 p-2 rounded-lg">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
              </div>
              <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">+12%</span>
            </div>
            <div class="text-2xl font-bold text-gray-900 stat-number" data-target="147">0</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">Total Projects</div>
            <div class="text-[11px] text-gray-400 mt-0.5">from last month</div>
          </div>

          <!-- Completed -->
          <div class="pa-kpi bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between mb-3">
              <div class="bg-emerald-50 p-2 rounded-lg">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <span class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">60% rate</span>
            </div>
            <div class="text-2xl font-bold text-gray-900 stat-number" data-target="89">0</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">Completed</div>
            <div class="text-[11px] text-gray-400 mt-0.5">completion rate</div>
          </div>

          <!-- Total Bids -->
          <div class="pa-kpi bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between mb-3">
              <div class="bg-amber-50 p-2 rounded-lg">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <span class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">8.5 avg</span>
            </div>
            <div class="text-2xl font-bold text-gray-900 stat-number" data-target="1234">0</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">Total Bids</div>
            <div class="text-[11px] text-gray-400 mt-0.5">bids per project</div>
          </div>

          <!-- Total Value -->
          <div class="pa-kpi bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
            <div class="flex items-start justify-between mb-3">
              <div class="bg-violet-50 p-2 rounded-lg">
                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">+15%</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">&#8369;<span class="stat-number" data-target="24.7">0</span>M</div>
            <div class="text-xs font-medium text-gray-500 mt-0.5">Total Value</div>
            <div class="text-[11px] text-gray-400 mt-0.5">from Q3</div>
          </div>

        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

          <div class="pa-panel bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
              <div>
                <h3 class="text-sm font-semibold text-gray-800">Project Completion Trends</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Monthly completion statistics</p>
              </div>
              <span class="text-[11px] font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-lg">Last 12 Months</span>
            </div>
            <div style="height: 250px;">
              <canvas id="completionTrendsChart"></canvas>
            </div>
          </div>

          <div class="pa-panel bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
              <div>
                <h3 class="text-sm font-semibold text-gray-800">Performance by Category</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Project types distribution</p>
              </div>
            </div>
            <div style="height: 250px;">
              <canvas id="categoryPerformanceChart"></canvas>
            </div>
          </div>

        </div>

        <!-- Metric Cards Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

          <div class="pa-metric bg-white border border-gray-200 border-l-4 border-l-cyan-400 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2.5">
              <div class="bg-cyan-50 p-2 rounded-lg">
                <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <span class="text-[11px] font-semibold text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded-full">-8 days</span>
            </div>
            <div class="text-[11px] text-gray-500 font-medium mb-1">Average Duration</div>
            <div class="text-xl font-bold text-gray-800"><span class="stat-number" data-target="45">0</span> <span class="text-sm font-normal text-gray-400">days</span></div>
            <div class="w-full bg-gray-100 rounded-full h-1 mt-2.5 overflow-hidden">
              <div class="bg-cyan-400 h-1 rounded-full progress-bar" data-width="75" style="width: 0%"></div>
            </div>
            <p class="text-[11px] text-gray-400 mt-1.5">Improved from previous period</p>
          </div>

          <div class="pa-metric bg-white border border-gray-200 border-l-4 border-l-emerald-400 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2.5">
              <div class="bg-emerald-50 p-2 rounded-lg">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">+5%</span>
            </div>
            <div class="text-[11px] text-gray-500 font-medium mb-1">Success Rate</div>
            <div class="text-xl font-bold text-gray-800"><span class="stat-number" data-target="92">0</span><span class="text-sm font-normal text-gray-400">%</span></div>
            <div class="w-full bg-gray-100 rounded-full h-1 mt-2.5 overflow-hidden">
              <div class="bg-emerald-400 h-1 rounded-full progress-bar" data-width="92" style="width: 0%"></div>
            </div>
            <p class="text-[11px] text-gray-400 mt-1.5">Projects completed successfully</p>
          </div>

          <div class="pa-metric bg-white border border-gray-200 border-l-4 border-l-amber-400 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2.5">
              <div class="bg-amber-50 p-2 rounded-lg">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
              <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">+3%</span>
            </div>
            <div class="text-[11px] text-gray-500 font-medium mb-1">On-Time Delivery</div>
            <div class="text-xl font-bold text-gray-800"><span class="stat-number" data-target="87">0</span><span class="text-sm font-normal text-gray-400">%</span></div>
            <div class="w-full bg-gray-100 rounded-full h-1 mt-2.5 overflow-hidden">
              <div class="bg-amber-400 h-1 rounded-full progress-bar" data-width="87" style="width: 0%"></div>
            </div>
            <p class="text-[11px] text-gray-400 mt-1.5">Delivered within timeline</p>
          </div>

        </div>

        <!-- Top Performers Table -->
        <div class="pa-table bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Top Performing Contractors</h3>
              <p class="text-[11px] text-gray-400">Based on project completion rate and quality</p>
            </div>
            <button class="flex items-center gap-1.5 bg-white border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-50 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Export
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="pa-perf-table w-full">
              <thead>
                <tr class="bg-white border-b border-gray-100">
                  <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Rank</th>
                  <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Contractor</th>
                  <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Projects</th>
                  <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Success Rate</th>
                  <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Avg. Duration</th>
                  <th class="text-left px-5 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Rating</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer">
                  <td class="px-5 py-3">
                    <div class="w-6 h-6 rounded-full bg-amber-400 text-white font-bold text-xs flex items-center justify-center">1</div>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-2.5">
                      <div class="w-8 h-8 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">JB</div>
                      <div>
                        <div class="text-sm font-semibold text-gray-800">Juan BuildCo</div>
                        <div class="text-[11px] text-gray-400">Premium Contractor</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-3">
                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">24 completed</span>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-20 bg-gray-100 rounded-full h-1.5"><div class="bg-emerald-400 h-1.5 rounded-full" style="width: 98%"></div></div>
                      <span class="text-xs font-semibold text-gray-700">98%</span>
                    </div>
                  </td>
                  <td class="px-5 py-3 text-xs text-gray-600 font-medium">42 days</td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-1">
                      <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                      <span class="text-xs font-bold text-gray-700">4.9</span>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer">
                  <td class="px-5 py-3">
                    <div class="w-6 h-6 rounded-full bg-gray-300 text-white font-bold text-xs flex items-center justify-center">2</div>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-2.5">
                      <div class="w-8 h-8 bg-purple-100 text-purple-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">RC</div>
                      <div>
                        <div class="text-sm font-semibold text-gray-800">Reyes Construction</div>
                        <div class="text-[11px] text-gray-400">Standard Contractor</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-3">
                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">19 completed</span>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-20 bg-gray-100 rounded-full h-1.5"><div class="bg-emerald-400 h-1.5 rounded-full" style="width: 95%"></div></div>
                      <span class="text-xs font-semibold text-gray-700">95%</span>
                    </div>
                  </td>
                  <td class="px-5 py-3 text-xs text-gray-600 font-medium">48 days</td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-1">
                      <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                      <span class="text-xs font-bold text-gray-700">4.7</span>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer">
                  <td class="px-5 py-3">
                    <div class="w-6 h-6 rounded-full bg-orange-300 text-white font-bold text-xs flex items-center justify-center">3</div>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-2.5">
                      <div class="w-8 h-8 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">SB</div>
                      <div>
                        <div class="text-sm font-semibold text-gray-800">Santos Builders</div>
                        <div class="text-[11px] text-gray-400">Premium Contractor</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-3">
                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">17 completed</span>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-20 bg-gray-100 rounded-full h-1.5"><div class="bg-emerald-400 h-1.5 rounded-full" style="width: 94%"></div></div>
                      <span class="text-xs font-semibold text-gray-700">94%</span>
                    </div>
                  </td>
                  <td class="px-5 py-3 text-xs text-gray-600 font-medium">43 days</td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-1">
                      <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                      <span class="text-xs font-bold text-gray-700">4.8</span>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer">
                  <td class="px-5 py-3">
                    <div class="w-6 h-6 rounded-full bg-gray-200 text-gray-500 font-bold text-xs flex items-center justify-center">4</div>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-2.5">
                      <div class="w-8 h-8 bg-orange-100 text-orange-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">DI</div>
                      <div>
                        <div class="text-sm font-semibold text-gray-800">Del Rosario Inc</div>
                        <div class="text-[11px] text-gray-400">Standard Contractor</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-3">
                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">15 completed</span>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-20 bg-gray-100 rounded-full h-1.5"><div class="bg-emerald-400 h-1.5 rounded-full" style="width: 93%"></div></div>
                      <span class="text-xs font-semibold text-gray-700">93%</span>
                    </div>
                  </td>
                  <td class="px-5 py-3 text-xs text-gray-600 font-medium">46 days</td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-1">
                      <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                      <span class="text-xs font-bold text-gray-700">4.6</span>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer">
                  <td class="px-5 py-3">
                    <div class="w-6 h-6 rounded-full bg-gray-200 text-gray-500 font-bold text-xs flex items-center justify-center">5</div>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-2.5">
                      <div class="w-8 h-8 bg-red-100 text-red-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">MC</div>
                      <div>
                        <div class="text-sm font-semibold text-gray-800">Manila Constructors</div>
                        <div class="text-[11px] text-gray-400">Premium Contractor</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-5 py-3">
                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">14 completed</span>
                  </td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                      <div class="w-20 bg-gray-100 rounded-full h-1.5"><div class="bg-emerald-400 h-1.5 rounded-full" style="width: 92%"></div></div>
                      <span class="text-xs font-semibold text-gray-700">92%</span>
                    </div>
                  </td>
                  <td class="px-5 py-3 text-xs text-gray-600 font-medium">44 days</td>
                  <td class="px-5 py-3">
                    <div class="flex items-center gap-1">
                      <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                      <span class="text-xs font-bold text-gray-700">4.7</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>

  </div>

  <script src="{{ asset('js/admin/home/projectPerformance_Analytics.js') }}" defer></script>

</body>

</html>
