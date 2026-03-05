<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/subscriptionAnalytics.css') }}">
  
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
      @include('admin.layouts.topnav', ['pageTitle' => 'Subscription Analytics'])

      <div class="p-8">
        <!-- Subscription Stats Cards -->
        <div class="subscription-stats-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Subscriptions Card -->
          <div class="stat-card group bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative overflow-hidden p-6">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl opacity-0 group-hover:opacity-20 blur transition duration-500"></div>
            
            <div class="relative">
              <div class="stat-card-header flex items-start gap-4 mb-4">
                <div class="stat-icon bg-gradient-to-br from-blue-500 to-blue-600 p-3 rounded-xl shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                  <i class="fi fi-sr-users-alt text-white text-2xl"></i>
                </div>
                <div class="stat-info flex-1">
                  <h3 class="stat-label text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Total Subscriptions</h3>
                  <div class="stat-value-row flex items-baseline gap-2">
                    <p class="stat-value text-4xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $subscriptionMetrics['active'] }}</p>
                    <span class="stat-total text-xl text-gray-500 font-semibold">/{{ $subscriptionMetrics['total'] }}</span>
                  </div>
                </div>
              </div>
              <div class="stat-progress-bar bg-blue-100 rounded-full h-2 overflow-hidden shadow-inner">
                <div class="stat-progress-fill bg-gradient-to-r from-blue-500 to-indigo-500 h-full rounded-full transition-all duration-1000 ease-out shadow-md" 
                  style="width: {{ $subscriptionMetrics['total'] > 0 ? round(($subscriptionMetrics['active'] / $subscriptionMetrics['total']) * 100) : 0 }}%"></div>
              </div>
              <div class="mt-2 text-xs text-gray-600 font-medium text-right">
                {{ $subscriptionMetrics['total'] > 0 ? round(($subscriptionMetrics['active'] / $subscriptionMetrics['total']) * 100) : 0 }}% Active
              </div>
            </div>
          </div>

          <!-- Total Revenue Card -->
          <div class="stat-card group bg-gradient-to-br from-white to-emerald-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative overflow-hidden p-6">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-400/5 to-teal-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl opacity-0 group-hover:opacity-20 blur transition duration-500"></div>
            
            <div class="relative">
              <div class="stat-card-header flex items-start gap-4 mb-4">
                <div class="stat-icon bg-gradient-to-br from-emerald-500 to-emerald-600 p-3 rounded-xl shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                  <i class="fi fi-sr-peso-sign text-white text-2xl"></i>
                </div>
                <div class="stat-info flex-1">
                  <h3 class="stat-label text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Total Revenue</h3>
                  <p class="stat-value text-4xl font-bold text-gray-800 group-hover:text-emerald-600 transition-colors">₱{{ number_format($subscriptionMetrics['revenue'], 2) }}</p>
                </div>
              </div>
              <div class="flex items-center gap-2 mt-3">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <p class="stat-description text-sm text-gray-600 font-medium">From commission payments</p>
              </div>
            </div>
          </div>

          <!-- Expiring Soon Card -->
          <div class="stat-card group bg-gradient-to-br from-white to-orange-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative overflow-hidden p-6">
            <div class="absolute inset-0 bg-gradient-to-r from-orange-400/5 to-amber-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="absolute -inset-0.5 bg-gradient-to-r from-orange-500 to-amber-500 rounded-2xl opacity-0 group-hover:opacity-20 blur transition duration-500"></div>
            
            <div class="relative">
              <div class="stat-card-header flex items-start gap-4 mb-4">
                <div class="stat-icon bg-gradient-to-br from-orange-500 to-orange-600 p-3 rounded-xl shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                  <i class="fi fi-sr-alarm-clock text-white text-2xl"></i>
                </div>
                <div class="stat-info flex-1">
                  <h3 class="stat-label text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Expiring Soon</h3>
                  <p class="stat-value text-4xl font-bold text-gray-800 group-hover:text-orange-600 transition-colors">{{ $subscriptionMetrics['expiring'] }}</p>
                </div>
              </div>
              <div class="flex items-center gap-2 mt-3">
                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="stat-description text-sm text-gray-600 font-medium">Next 7 days</p>
              </div>
            </div>
          </div>

          <!-- Expired Card -->
          <div class="stat-card group bg-gradient-to-br from-white to-red-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative overflow-hidden p-6">
            <div class="absolute inset-0 bg-gradient-to-r from-red-400/5 to-pink-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="absolute -inset-0.5 bg-gradient-to-r from-red-500 to-pink-500 rounded-2xl opacity-0 group-hover:opacity-20 blur transition duration-500"></div>
            
            <div class="relative">
              <div class="stat-card-header flex items-start gap-4 mb-4">
                <div class="stat-icon bg-gradient-to-br from-red-500 to-red-600 p-3 rounded-xl shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                  <i class="fi fi-sr-time-past text-white text-2xl"></i>
                </div>
                <div class="stat-info flex-1">
                  <h3 class="stat-label text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Expired</h3>
                  <p class="stat-value text-4xl font-bold text-gray-800 group-hover:text-red-600 transition-colors">{{ $subscriptionMetrics['expired'] }}</p>
                </div>
              </div>
              <div class="flex items-center gap-2 mt-3">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <p class="stat-description text-sm text-gray-600 font-medium">Past subscription period</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Subscriptions Chart -->
        <div class="subscription-chart-card group bg-gradient-to-br from-white to-purple-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden p-8 mb-8">
          <div class="absolute inset-0 bg-gradient-to-r from-purple-400/5 to-pink-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          
          <div class="relative">
            <div class="chart-header flex flex-wrap items-center justify-between gap-4 mb-8">
              <div>
                <div class="flex items-center gap-3 mb-2">
                  <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                  <h2 class="chart-title text-2xl font-bold text-gray-800">Total Subscriptions</h2>
                </div>
                <p class="chart-subtitle text-sm text-gray-600 ml-14 flex items-center gap-2">
                  <span class="inline-block w-1.5 h-1.5 bg-purple-400 rounded-full"></span>
                  Distribution by contractor tier
                </p>
              </div>
              <div class="chart-legend flex flex-wrap gap-3">
                @foreach($subscriptionTiers['tiers'] as $tier)
                <div class="legend-item-inline group/legend cursor-pointer bg-white hover:bg-purple-50 px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2">
                  <span class="legend-dot-inline w-3 h-3 rounded-full shadow-md group-hover/legend:scale-125 transition-transform" style="background: {{ $tier['color'] }};"></span>
                  <span class="legend-text text-sm font-semibold text-gray-700 group-hover/legend:text-purple-600 transition-colors">{{ $tier['label'] }}</span>
                </div>
                @endforeach
              </div>
            </div>

          <div class="chart-container relative bg-white rounded-xl p-6 shadow-inner">
            <div class="bar-chart flex items-end justify-around gap-4 h-80 relative">
              @foreach($subscriptionTiers['tiers'] as $index => $tier)
              <div class="bar-item group/bar flex-1 flex flex-col items-center cursor-pointer" data-tier="{{ $index }}">
                <div class="bar-wrapper relative w-full flex-1 flex items-end justify-center">
                  <div class="bar-fill relative w-full max-w-20 rounded-t-lg shadow-lg group-hover/bar:shadow-2xl transition-all duration-700 ease-out group-hover/bar:scale-105" 
                    data-count="{{ $tier['count'] }}" 
                    data-max="{{ $subscriptionTiers['maxCount'] }}"
                    style="background: {{ $tier['gradient'] }}; height: 0%;">
                    <div class="bar-value absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-3 py-1.5 rounded-lg text-sm font-bold shadow-lg opacity-0 group-hover/bar:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                      {{ number_format($tier['count']) }}K
                      <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                    </div>
                  </div>
                </div>
                <div class="bar-label mt-4 text-sm font-semibold text-gray-700 group-hover/bar:text-purple-600 transition-colors text-center">{{ $tier['name'] }}</div>
              </div>
              @endforeach
            </div>

            <!-- Y-axis labels -->
            <div class="y-axis absolute left-0 top-0 h-full flex flex-col justify-between py-6 pr-4 text-xs font-medium text-gray-500">
              <span class="y-label">30K</span>
              <span class="y-label">20K</span>
              <span class="y-label">10K</span>
              <span class="y-label">0</span>
            </div>
          </div>
          </div>
        </div>

        <!-- Total Revenue Chart -->
        <div class="revenue-chart-card group bg-gradient-to-br from-white to-emerald-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden p-8">
          <div class="absolute inset-0 bg-gradient-to-r from-emerald-400/5 to-teal-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          
          <div class="relative">
            <div class="revenue-header flex flex-wrap items-center justify-between gap-4 mb-6">
              <div class="revenue-titles">
                <div class="flex items-center gap-3 mb-2">
                  <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <h2 class="revenue-title text-2xl font-bold text-gray-800">Total Revenue</h2>
                </div>
                <p class="revenue-subtitle text-sm text-gray-600 ml-14 flex items-center gap-2">
                  <span class="inline-block w-1.5 h-1.5 bg-emerald-400 rounded-full"></span>
                  Monthly revenue comparison (current vs previous year)
                </p>
              </div>
              <div class="revenue-controls flex flex-wrap items-center gap-3 bg-white px-4 py-3 rounded-xl shadow-md">
                <label class="revenue-tier-label text-sm font-semibold text-gray-700" for="revenueTierSelect">Tier:</label>
                <select id="revenueTierSelect" class="revenue-tier-select px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:outline-none text-sm font-medium text-gray-700 cursor-pointer hover:border-emerald-400 transition-colors">
                  <option value="all" selected>All Tiers</option>
                  <option value="gold">Gold</option>
                  <option value="silver">Silver</option>
                  <option value="bronze">Bronze</option>
                </select>
                <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 rounded-lg">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <span class="revenue-date-range text-sm font-semibold text-emerald-700">{{ $subscriptionRevenue['dateRange'] ?? '' }}</span>
                </div>
              </div>
            </div>
            <div class="revenue-chart-wrapper bg-white rounded-xl p-6 shadow-inner relative">
              <canvas id="subscriptionRevenueChart" height="140"></canvas>
              <div id="revenueLoading" class="revenue-loading absolute inset-0 flex items-center justify-center bg-white bg-opacity-90 rounded-xl" hidden>
                <div class="flex flex-col items-center gap-3">
                  <div class="animate-spin rounded-full h-12 w-12 border-4 border-emerald-200 border-t-emerald-600"></div>
                  <span class="text-sm font-medium text-gray-600">Loading...</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      </main>
  </div>
  <script id="initialRevenueData" type="application/json">{!! json_encode($subscriptionRevenue ?? []) !!}</script>
  <script src="{{ asset('js/admin/home/subscriptionAnalytics.js') }}" defer></script>

</body>

</html>
