<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/bidCompletion_Analytics.css') }}">

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
      @include('admin.layouts.topnav', ['pageTitle' => 'Bid Completion Analytics'])

      {{-- ── GLOBAL DATE FILTER ─────────────────────────────────── --}}
      <div id="globalDateFilter" style="background:linear-gradient(135deg,#f0f4ff 0%,#e8ecff 100%);border-bottom:1px solid #ddd8fe;padding:16px 32px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <span style="font-weight:700;color:#4338ca;font-size:14px;margin-right:4px;">📅 Filter Period:</span>
        <div id="presetButtons" style="display:flex;gap:6px;flex-wrap:wrap;">
          <button class="date-preset-btn active" data-range="all" style="padding:6px 16px;border-radius:9999px;font-size:13px;border:1.5px solid #6366f1;cursor:pointer;transition:all .2s;font-weight:600;color:#fff;background:#6366f1;">All Time</button>
          <button class="date-preset-btn" data-range="last3months" style="padding:6px 16px;border-radius:9999px;font-size:13px;border:1.5px solid #e5e7eb;cursor:pointer;transition:all .2s;font-weight:500;color:#4b5563;background:#fff;">Last 3 Months</button>
          <button class="date-preset-btn" data-range="last6months" style="padding:6px 16px;border-radius:9999px;font-size:13px;border:1.5px solid #e5e7eb;cursor:pointer;transition:all .2s;font-weight:500;color:#4b5563;background:#fff;">Last 6 Months</button>
          <button class="date-preset-btn" data-range="thisyear" style="padding:6px 16px;border-radius:9999px;font-size:13px;border:1.5px solid #e5e7eb;cursor:pointer;transition:all .2s;font-weight:500;color:#4b5563;background:#fff;">This Year</button>
          <button class="date-preset-btn" data-range="lastyear" style="padding:6px 16px;border-radius:9999px;font-size:13px;border:1.5px solid #e5e7eb;cursor:pointer;transition:all .2s;font-weight:500;color:#4b5563;background:#fff;">Last Year</button>
        </div>
        <div style="display:flex;align-items:center;gap:6px;margin-left:auto;">
          <input type="date" id="globalDateFrom" style="padding:6px 10px;border:1.5px solid #c7d2fe;border-radius:8px;font-size:13px;color:#374151;">
          <span style="color:#6b7280;">to</span>
          <input type="date" id="globalDateTo" style="padding:6px 10px;border:1.5px solid #c7d2fe;border-radius:8px;font-size:13px;color:#374151;">
          <button id="applyGlobalDateFilter" style="padding:6px 18px;background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Apply</button>
          <span id="filterLoading" class="hidden" style="font-size:13px;color:#6366f1;">⏳ Loading...</span>
        </div>
      </div>

      <div class="p-8 space-y-8">

        {{-- ── HERO STAT CARDS ──────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

          {{-- Total Projects --}}
          <div class="group relative bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-3">
                  <svg class="w-8 h-8 text-white transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                  </svg>
                </div>
              </div>
              <div class="text-white text-sm font-medium mb-1 opacity-90">Total Projects</div>
              <div class="text-white text-4xl font-bold mb-2 stat-number" data-target="{{ $totalProjects }}">0</div>
              <div class="flex items-center text-white text-sm">
                @if($projectsMoM >= 0)
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path></svg>
                  <span class="font-semibold">+{{ $projectsMoM }}%</span>
                @else
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd"></path></svg>
                  <span class="font-semibold">{{ $projectsMoM }}%</span>
                @endif
                <span class="ml-1 opacity-80">from last month</span>
              </div>
            </div>
          </div>

          {{-- Active Contractors --}}
          <div class="group relative bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-3">
                  <svg class="w-8 h-8 text-white transform group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="text-white text-sm font-medium mb-1 opacity-90">Active Contractors</div>
              <div class="text-white text-4xl font-bold mb-2 stat-number" data-target="{{ $activeContractors }}">0</div>
              <div class="flex items-center text-white text-sm">
                <span class="font-semibold">{{ $contractorCompletionRate }}%</span>
                <span class="ml-1 opacity-80">completion rate</span>
              </div>
            </div>
          </div>

          {{-- Total Bids Value --}}
          <div class="group relative bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-3">
                  <svg class="w-8 h-8 text-white transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </div>
              </div>
              <div class="text-white text-sm font-medium mb-1 opacity-90">Total Contracted Value</div>
              <div class="text-white text-4xl font-bold mb-2">₱<span class="stat-number" data-target="{{ $totalValueM }}">0</span>M</div>
              <div class="flex items-center text-white text-sm">
                <span class="font-semibold">{{ $totalBids }}</span>
                <span class="ml-1 opacity-80">total bids submitted</span>
              </div>
            </div>
          </div>

          {{-- Completion Rate --}}
          <div class="group relative bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-3">
                  <svg class="w-8 h-8 text-white transform group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
              </div>
              <div class="text-white text-sm font-medium mb-1 opacity-90">Bid Acceptance Rate</div>
              <div class="text-white text-4xl font-bold mb-2"><span class="stat-number" data-target="{{ $completionRate }}">0</span>%</div>
              <div class="flex items-center text-white text-sm">
                @if($completionRateMoM >= 0)
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path></svg>
                  <span class="font-semibold">+{{ $completionRateMoM }}%</span>
                @else
                  <span class="font-semibold">{{ $completionRateMoM }}%</span>
                @endif
                <span class="ml-1 opacity-80">vs last month</span>
              </div>
            </div>
          </div>
        </div>

        {{-- ── CHARTS ROW ───────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          {{-- Bid Timeline Analysis --}}
          <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Bid Timeline Analysis</h3>
                <p class="text-sm text-gray-500">Monthly bid submissions vs acceptances</p>
              </div>
              <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-md">
                Last 12 Months
              </div>
            </div>
            <div style="height: 350px;">
              <canvas id="bidTimelineChart"
                data-months='@json($timelineMonths)'
                data-submitted='@json($timelineSubmitted)'
                data-accepted='@json($timelineAccepted)'></canvas>
            </div>
          </div>

          {{-- Bid Status Distribution --}}
          <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Bid Status Distribution</h3>
                <p class="text-sm text-gray-500">Current bid statuses breakdown</p>
              </div>
            </div>
            <div style="height: 350px;">
              <canvas id="bidStatusChart"
                data-labels='@json(array_keys($bidStatusCounts))'
                data-values='@json(array_values($bidStatusCounts))'></canvas>
            </div>
          </div>
        </div>

        {{-- ── BID METRIC CARDS ─────────────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          {{-- Average Bid Value --}}
          <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-emerald-500">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-gradient-to-br from-emerald-400 to-emerald-500 text-white p-3 rounded-xl shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <span class="text-emerald-600 text-sm font-semibold bg-emerald-50 px-3 py-1 rounded-full">Avg Value</span>
            </div>
            <h4 class="text-gray-600 text-sm font-medium mb-2">Average Bid Value</h4>
            <div class="text-3xl font-bold text-gray-800 mb-3">
              @if($avgBidValueK >= 1000)
                ₱<span class="stat-number" data-target="{{ round($avgBidValueK / 1000, 1) }}">0</span>M
              @else
                ₱<span class="stat-number" data-target="{{ $avgBidValueK }}">0</span>K
              @endif
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full progress-bar" data-width="{{ $avgBidBarWidth }}" style="width: 0%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Based on {{ $totalBids }} total bids</p>
          </div>

          {{-- Avg Response Time --}}
          <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-blue-500">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-gradient-to-br from-blue-400 to-blue-500 text-white p-3 rounded-xl shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <span class="text-blue-600 text-sm font-semibold bg-blue-50 px-3 py-1 rounded-full">Submit → Decision</span>
            </div>
            <h4 class="text-gray-600 text-sm font-medium mb-2">Avg Response Time</h4>
            <div class="text-3xl font-bold text-gray-800 mb-3">
              <span class="stat-number" data-target="{{ $avgResponseHours }}">0</span> hrs
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-2 rounded-full progress-bar" data-width="{{ $responseBarWidth }}" style="width: 0%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">From submission to accept/reject decision</p>
          </div>

          {{-- Bid Win Rate --}}
          <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-gradient-to-br from-indigo-400 to-indigo-500 text-white p-3 rounded-xl shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
              </div>
              <span class="text-indigo-600 text-sm font-semibold bg-indigo-50 px-3 py-1 rounded-full">Accepted / Total</span>
            </div>
            <h4 class="text-gray-600 text-sm font-medium mb-2">Bid Win Rate</h4>
            <div class="text-3xl font-bold text-gray-800 mb-3">
              <span class="stat-number" data-target="{{ $bidWinRate }}">0</span>%
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <div class="bg-gradient-to-r from-indigo-400 to-indigo-600 h-2 rounded-full progress-bar" data-width="{{ $winRateBarWidth }}" style="width: 0%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Platform-wide bid acceptance rate</p>
          </div>
        </div>

        {{-- ── GEOGRAPHIC DISTRIBUTION ──────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
          <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Geographic Distribution — Zamboanga City Districts</h3>
            <p class="text-sm text-gray-500">Project distribution across city districts (based on project_location)</p>
          </div>

          <div style="height: 350px;" class="mb-8">
            <canvas id="geographicDistributionChart"
              data-labels='@json($geoLabels)'
              data-values='@json($geoCounts)'></canvas>
          </div>

          {{-- District Cards (4) --}}
          <div id="districtCardsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
              $districtStyles = [
                'Tetuan'    => ['bg' => 'from-green-50 to-green-100',   'border' => 'border-green-200',   'text' => 'text-green-600',  'sub' => 'text-green-700',  'val' => 'text-green-600'],
                'Tumaga'    => ['bg' => 'from-blue-50 to-blue-100',     'border' => 'border-blue-200',     'text' => 'text-blue-600',   'sub' => 'text-blue-700',   'val' => 'text-blue-600'],
                'Malagutay' => ['bg' => 'from-orange-50 to-orange-100', 'border' => 'border-orange-200',   'text' => 'text-orange-600', 'sub' => 'text-orange-700', 'val' => 'text-orange-600'],
                'Others'    => ['bg' => 'from-pink-50 to-pink-100',     'border' => 'border-pink-200',     'text' => 'text-pink-600',   'sub' => 'text-pink-700',   'val' => 'text-pink-600'],
              ];
            @endphp
            @foreach($fourDistricts as $name => $info)
              @php $s = $districtStyles[$name]; @endphp
              <div class="bg-gradient-to-br {{ $s['bg'] }} rounded-xl p-6 border-2 {{ $s['border'] }} hover:shadow-lg transition-all duration-300">
                <h4 class="text-gray-700 font-semibold text-lg mb-2">{{ $name }}</h4>
                <div class="text-4xl font-bold {{ $s['text'] }} mb-2 stat-number" data-target="{{ $info['count'] }}">0</div>
                <div class="text-sm {{ $s['sub'] }} font-medium mb-1">Active Projects</div>
                <div class="text-xs {{ $s['val'] }}">
                  @if($info['value'] > 0)
                    ₱{{ $info['value'] }}M contracted value
                  @else
                    No accepted bids yet
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- ── RECENT BID ACTIVITY TABLE ────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-violet-500 to-purple-600 px-8 py-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-2xl font-bold text-white mb-1">Recent Bid Activity</h3>
                <p class="text-violet-100 text-sm">Latest bid submissions and updates</p>
              </div>
              <button class="bg-white text-violet-600 px-5 py-2.5 rounded-lg font-semibold hover:bg-violet-50 transition-colors duration-200 shadow-md hover:shadow-lg flex items-center gap-2" id="exportBidsBtn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Data
              </button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contractor</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Bid Amount</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Submitted</th>
                  <!--<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>-->
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                @forelse($recentBids as $bid)
                  @php
                    $statusConfig = [
                      'accepted'     => ['bg' => 'bg-green-100',  'text' => 'text-green-800',  'dot' => 'bg-green-500',  'pulse' => true,  'label' => 'Accepted'],
                      'submitted'    => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'dot' => 'bg-yellow-500', 'pulse' => true,  'label' => 'Pending'],
                      'under_review' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-800',   'dot' => 'bg-blue-500',   'pulse' => false, 'label' => 'Under Review'],
                      'rejected'     => ['bg' => 'bg-red-100',    'text' => 'text-red-800',    'dot' => 'bg-red-500',    'pulse' => false, 'label' => 'Rejected'],
                      'cancelled'    => ['bg' => 'bg-gray-100',   'text' => 'text-gray-700',   'dot' => 'bg-gray-400',   'pulse' => false, 'label' => 'Cancelled'],
                    ];
                    $sc = $statusConfig[$bid->bid_status] ?? $statusConfig['submitted'];

                    // Avatar gradient colors cycling
                    $gradients = ['from-blue-400 to-blue-600','from-purple-400 to-purple-600','from-emerald-400 to-emerald-600','from-orange-400 to-orange-600','from-red-400 to-red-600','from-indigo-400 to-indigo-600'];
                    $grad = $gradients[$loop->index % count($gradients)];
                  @endphp
                  <tr class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer">
                    <td class="px-6 py-4">
                      <div class="font-semibold text-gray-800">{{ $bid->project_title }}</div>
                      <div class="text-sm text-gray-500">{{ $bid->project_location }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-3">
                        @if($bid->company_logo)
                          <img src="{{ asset('storage/' . $bid->company_logo) }}"
                               class="w-10 h-10 rounded-full object-cover shadow" alt="{{ $bid->company_name }}">
                        @else
                          <div class="w-10 h-10 bg-gradient-to-br {{ $grad }} rounded-full flex items-center justify-center text-white font-semibold shadow text-sm">
                            {{ $bid->initials }}
                          </div>
                        @endif
                        <div>
                          <div class="font-semibold text-gray-800">{{ $bid->company_name }}</div>
                          <div class="text-sm text-gray-500">{{ $bid->subscription_tier }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="font-bold text-gray-800">₱{{ number_format($bid->proposed_cost) }}</div>
                      <div class="text-sm text-gray-500">
                        @php
                          $diff = $bid->proposed_cost - ($avgBidValueK * 1000);
                          $sign = $diff >= 0 ? '+' : '';
                          $diffK = round(abs($diff) / 1000, 1);
                        @endphp
                        {{ $sign }}{{ $diff >= 0 ? '' : '-' }}₱{{ $diffK }}K vs avg
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $sc['bg'] }} {{ $sc['text'] }}">
                        <span class="w-2 h-2 {{ $sc['dot'] }} rounded-full mr-2 {{ $sc['pulse'] ? 'animate-pulse' : '' }}"></span>
                        {{ $sc['label'] }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-700">{{ \Carbon\Carbon::parse($bid->submitted_at)->format('M j, Y') }}</div>
                      <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($bid->submitted_at)->diffForHumans() }}</div>
                    </td>
                    <!--<td class="px-6 py-4">
                      <button class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">View Details</button>
                    </td>-->
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                      <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                      No bid activity yet
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- ── PROPERTY OWNERS ACTIVITY + PAYMENT ANALYTICS ────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          {{-- Property Owners Activity --}}
          <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-teal-500 to-cyan-600 px-6 py-5">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xl font-bold text-white mb-1">Recent Bid Activity by Project</h3>
                  <p class="text-teal-100 text-sm">Latest accepted & pending bids with contractor info</p>
                </div>
              </div>
            </div>

            <div class="p-6">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="border-b-2 border-gray-200">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Project</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contractor</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Bid Value</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    @forelse($ownerActivity as $activity)
                      @php
                        $sc2 = [
                          'accepted'     => 'bg-green-100 text-green-800',
                          'submitted'    => 'bg-yellow-100 text-yellow-800',
                          'under_review' => 'bg-purple-100 text-purple-800',
                          'rejected'     => 'bg-red-100 text-red-800',
                          'cancelled'    => 'bg-gray-100 text-gray-700',
                        ][$activity->bid_status] ?? 'bg-gray-100 text-gray-700';

                        $label2 = [
                          'accepted'     => 'Accepted',
                          'submitted'    => 'Pending',
                          'under_review' => 'Under Review',
                          'rejected'     => 'Rejected',
                          'cancelled'    => 'Cancelled',
                        ][$activity->bid_status] ?? 'Unknown';
                      @endphp
                      <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-4 py-4">
                          <div class="font-semibold text-gray-800 text-sm">{{ Str::limit($activity->project_title, 30) }}</div>
                          <div class="text-xs text-gray-500">{{ $activity->project_location }}</div>
                        </td>
                        <td class="px-4 py-4">
                          <div class="font-medium text-gray-700 text-sm">{{ $activity->company_name }}</div>
                        </td>
                        <td class="px-4 py-4">
                          <div class="font-bold text-gray-800 text-sm">
                            @if($activity->proposed_cost >= 1_000_000)
                              ₱{{ round($activity->proposed_cost / 1_000_000, 1) }}M
                            @else
                              ₱{{ number_format($activity->proposed_cost / 1000, 0) }}K
                            @endif
                          </div>
                        </td>
                        <td class="px-4 py-4">
                          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc2 }}">
                            {{ $label2 }}
                          </span>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">No recent activity</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          {{-- Payment Analytics --}}
          <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
              <div>
                <h3 class="text-xl font-bold text-white mb-1">Payment Analytics</h3>
                <p class="text-indigo-100 text-sm">Milestone payment financial metrics</p>
              </div>
            </div>

            <div id="paymentAnalyticsCards" class="p-6 space-y-6">
              {{-- Total Payments Released --}}
              <div class="bg-gray-50 rounded-xl p-5 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-600 mb-1">Total Payments Released</div>
                    <div class="text-xs text-gray-500">This month (approved)</div>
                  </div>
                  <div class="text-3xl font-bold text-teal-600">
                    ₱<span class="stat-number" data-target="{{ $paymentsReleasedM }}">0</span>M
                  </div>
                </div>
              </div>

              {{-- Pending Payments --}}
              <div class="bg-gray-50 rounded-xl p-5 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-600 mb-1">Pending Payments</div>
                    <div class="text-xs text-gray-500">Awaiting approval</div>
                  </div>
                  <div class="text-3xl font-bold text-orange-600">
                    ₱<span class="stat-number" data-target="{{ $pendingPaymentsM }}">0</span>M
                  </div>
                </div>
              </div>

              {{-- Average Payment Processing Time --}}
              <div class="bg-gray-50 rounded-xl p-5 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-600 mb-1">Avg Processing Time</div>
                    <div class="text-xs text-gray-500">Transaction date → Approval</div>
                  </div>
                  <div class="text-3xl font-bold text-blue-600">
                    <span class="stat-number" data-target="{{ $avgPaymentDays }}">0</span> days
                  </div>
                </div>
              </div>

              {{-- Payment Success Rate --}}
              <div class="bg-gray-50 rounded-xl p-5 hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm font-medium text-gray-600 mb-1">Payment Success Rate</div>
                    <div class="text-xs text-gray-500">Approved vs Rejected</div>
                  </div>
                  <div class="text-3xl font-bold text-emerald-600">
                    <span class="stat-number" data-target="{{ $paymentSuccessRate }}">0</span>%
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>{{-- end grid --}}
      </div>{{-- end p-8 --}}
    </main>

  </div>

  <script src="{{ asset('js/admin/home/bidCompletion_Analytics.js') }}" defer></script>
</body>
</html>