<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

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

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'Project Analytics'])

      <div class="analytics-container">
        <!-- Projects Analytics Card -->
        <div class="analytics-card group bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden">
          <!-- Animated background overlay -->
          <div class="absolute inset-0 bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          
          <div class="relative">
            <div class="flex items-center justify-between mb-6">
              <h2 class="analytics-card-title text-2xl font-bold text-gray-800 flex items-center gap-3">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
                <span>Projects</span>
              </h2>
              <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold group-hover:scale-105 transition-transform">Overview</span>
            </div>
          
            <div class="projects-analytics-content">
            <!-- Donut Chart -->
            <div class="projects-chart-wrapper">
              <canvas id="projectsDonutChart" 
                data-labels='{{ json_encode(array_column($projectsAnalytics["data"], "label")) }}'
                data-values='{{ json_encode(array_column($projectsAnalytics["data"], "count")) }}'>
              </canvas>
            </div>

            <!-- Legend -->
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

        <!-- Project Success Rate Card -->
        <div class="analytics-card group bg-gradient-to-br from-white to-purple-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden">
          <!-- Animated background overlay -->
          <div class="absolute inset-0 bg-gradient-to-r from-purple-400/5 to-pink-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          
          <div class="relative">
            <div class="mb-6">
              <div class="flex items-center gap-3 mb-2">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <h2 class="analytics-card-title text-2xl font-bold text-gray-800">Project Success Rate</h2>
              </div>
              <p class="analytics-card-subtitle text-sm text-gray-600 ml-14 flex items-center gap-2">
                <span class="inline-block w-1.5 h-1.5 bg-purple-400 rounded-full"></span>
                Completion rate by project type
              </p>
            </div>
          
            <div class="success-rate-content">
            <!-- Pie Chart -->
            <div class="success-rate-chart-wrapper">
              <canvas id="projectSuccessRateChart" 
                data-labels='{{ json_encode(array_column($projectSuccessRate["data"], "label")) }}'
                data-values='{{ json_encode(array_column($projectSuccessRate["data"], "count")) }}'
                data-colors='{{ json_encode(array_column($projectSuccessRate["data"], "color")) }}'>
              </canvas>
            </div>

            <!-- Legend with Lines -->
            <div class="success-rate-legend space-y-3">
              @foreach($projectSuccessRate['data'] as $index => $item)
              <div class="success-rate-legend-item group/rate cursor-pointer hover:bg-purple-50 p-3 rounded-lg transition-all duration-300 hover:scale-105 hover:shadow-md" data-index="{{ $index }}">
                <div class="flex items-center gap-3">
                  <div class="success-rate-legend-line h-1 rounded-full shadow-md group-hover/rate:shadow-lg transition-shadow duration-300" style="background: {{ $item['color'] }}; width: 40px;"></div>
                  <span class="success-rate-legend-label text-gray-700 font-medium group-hover/rate:text-purple-600 transition-colors flex-1">{{ $item['label'] }}</span>
                  <svg class="w-5 h-5 text-gray-400 group-hover/rate:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          </div>
        </div>

        <!-- Projects Timeline Chart -->
        <div class="analytics-card projects-timeline-card group bg-gradient-to-br from-white to-emerald-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden">
          <!-- Animated background overlay -->
          <div class="absolute inset-0 bg-gradient-to-r from-emerald-400/5 to-teal-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          
          <div class="relative">
          <div class="timeline-header flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                </svg>
              </div>
              <h2 class="analytics-card-title text-2xl font-bold text-gray-800">Projects Timeline</h2>
            </div>
            <div class="timeline-date-picker-wrapper">
              <div class="timeline-date-picker bg-white shadow-md hover:shadow-lg transition-shadow duration-300 rounded-lg px-4 py-2 flex items-center gap-3 cursor-pointer border border-gray-200 hover:border-emerald-300">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="timeline-date-range font-semibold text-gray-700">{{ $projectsTimeline['dateRange'] }}</span>
                <button class="timeline-dropdown-btn hover:scale-110 transition-transform" id="timelineDropdownBtn">
                  <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                    <path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
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

          <!-- Chart Legend -->
          <div class="timeline-legend flex gap-4 mb-6">
            <div class="timeline-legend-item group/legend cursor-pointer bg-white hover:bg-orange-50 px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2" data-dataset="0">
              <span class="timeline-legend-dot w-3 h-3 rounded-full shadow-md group-hover/legend:scale-125 transition-transform" style="background: #fb923c;"></span>
              <span class="timeline-legend-label text-sm font-semibold text-gray-700 group-hover/legend:text-orange-600 transition-colors">New Projects</span>
            </div>
            <div class="timeline-legend-item group/legend cursor-pointer bg-white hover:bg-indigo-50 px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2" data-dataset="1">
              <span class="timeline-legend-dot w-3 h-3 rounded-full shadow-md group-hover/legend:scale-125 transition-transform" style="background: #818cf8;"></span>
              <span class="timeline-legend-label text-sm font-semibold text-gray-700 group-hover/legend:text-indigo-600 transition-colors">Completed Projects</span>
            </div>
          </div>

          <!-- Chart Container -->
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

      </main>
  </div>

  <script src="{{ asset('js/admin/home/analytics.js') }}" defer></script>

</body>

</html>
