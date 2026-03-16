<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Bid Completion Analytics - Legatura Admin</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo2.0-favicon.svg') }}">

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

    <div class="bc-shell p-4 lg:p-5 space-y-3.5">

      {{-- DATE FILTER --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm" id="globalDateFilter">
        <div class="flex items-center gap-3 flex-wrap">
          <span class="text-xs font-semibold text-gray-600 whitespace-nowrap flex items-center gap-1.5">
            <i class="fi fi-sr-calendar" style="font-size:.85rem;vertical-align:middle;"></i>
            Filter Period
          </span>
          <div class="flex gap-1.5 flex-wrap" id="presetButtons">
            <button class="date-preset-btn active px-2.5 py-1 rounded-full border border-indigo-400 text-xs font-semibold text-white bg-indigo-500 transition-all" data-range="all">All Time</button>
            <button class="date-preset-btn px-2.5 py-1 rounded-full border border-gray-200 text-xs font-medium text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="last3months">Last 3 Months</button>
            <button class="date-preset-btn px-2.5 py-1 rounded-full border border-gray-200 text-xs font-medium text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="last6months">Last 6 Months</button>
            <button class="date-preset-btn px-2.5 py-1 rounded-full border border-gray-200 text-xs font-medium text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="thisyear">This Year</button>
            <button class="date-preset-btn px-2.5 py-1 rounded-full border border-gray-200 text-xs font-medium text-gray-500 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" data-range="lastyear">Last Year</button>
          </div>
          <div class="flex items-center gap-2 ml-auto flex-wrap">
            <div class="date-pill flex items-center rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
              <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2 self-stretch">
                <i class="fi fi-rr-calendar text-white text-xs leading-none"></i>
                <span class="text-[10px] font-bold text-indigo-100 uppercase tracking-wider select-none">From</span>
              </div>
              <input type="date" id="globalDateFrom" class="bg-white text-xs text-gray-700 font-medium px-3 py-2 focus:outline-none cursor-pointer min-w-0 border-0 outline-none">
            </div>
            <span class="text-gray-300 font-bold text-base">&rarr;</span>
            <div class="date-pill flex items-center rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
              <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2 self-stretch">
                <i class="fi fi-rr-calendar text-white text-xs leading-none"></i>
                <span class="text-[10px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
              </div>
              <input type="date" id="globalDateTo" class="bg-white text-xs text-gray-700 font-medium px-3 py-2 focus:outline-none cursor-pointer min-w-0 border-0 outline-none">
            </div>
            <button id="resetGlobalDateFilter" type="button" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
              <i class="fi fi-rr-rotate-left"></i>
              <span>Reset Filter</span>
            </button>
          </div>
          <div id="filterLoading" class="hidden flex items-center gap-1 ml-1">
            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0s"></span>
            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0.1s"></span>
            <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-bounce" style="animation-delay:0.2s"></span>
          </div>
        </div>
      </div>

      {{-- KPI CARDS --}}
      <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">

        {{-- Total Projects --}}
        <div class="bc-kpi bg-white border border-gray-200 border-l-4 border-l-blue-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-blue-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 stat-number" data-target="{{ $totalProjects }}">0</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Total Projects</div>
          <div class="text-[11px] text-gray-400 mt-0.5">vs last month</div>
        </div>

        {{-- Active Contractors --}}
        <div class="bc-kpi bg-white border border-gray-200 border-l-4 border-l-cyan-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-cyan-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 stat-number" data-target="{{ $activeContractors }}">0</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Active Contractors</div>
          <div class="text-[11px] text-gray-400 mt-0.5">{{ $contractorCompletionRate }}% completion rate</div>
        </div>

        {{-- Total Contracted Value --}}
        <div class="bc-kpi bg-white border border-gray-200 border-l-4 border-l-orange-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-orange-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900">&#8369;<span class="stat-number" data-target="{{ $totalValueM }}">0</span>M</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Total Contracted Value</div>
          <div class="text-[11px] text-gray-400 mt-0.5">{{ $totalBids }} bids submitted</div>
        </div>

        {{-- Bid Acceptance Rate --}}
        <div class="bc-kpi bg-white border border-gray-200 border-l-4 border-l-pink-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-pink-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900"><span class="stat-number" data-target="{{ $completionRate }}">0</span>%</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Bid Acceptance Rate</div>
          <div class="text-[11px] text-gray-400 mt-0.5">vs last month</div>
        </div>

      </div>

      {{-- CHARTS ROW --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        {{-- Bid Timeline Analysis --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <div class="mb-3">
            <div class="text-sm font-semibold text-gray-800">Bid Timeline Analysis</div>
            <div class="text-[11px] text-gray-400">Monthly bid submissions vs acceptances</div>
          </div>
          <div style="height:230px;">
            <canvas id="bidTimelineChart"
              data-months='@json($timelineMonths)'
              data-submitted='@json($timelineSubmitted)'
              data-accepted='@json($timelineAccepted)'></canvas>
          </div>
        </div>

        {{-- Bid Status Distribution --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <div class="mb-3">
            <div class="text-sm font-semibold text-gray-800">Bid Status Distribution</div>
            <div class="text-[11px] text-gray-400">Current bid statuses breakdown</div>
          </div>
          <div style="height:230px;">
            <canvas id="bidStatusChart"
              data-labels='@json(array_keys($bidStatusCounts))'
              data-values='@json(array_values($bidStatusCounts))'></canvas>
          </div>
        </div>

      </div>

      {{-- BID METRIC CARDS --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

        {{-- Average Bid Value --}}
        <div class="bg-white border border-gray-200 border-l-4 border-l-emerald-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-emerald-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <span class="text-[11px] text-gray-400 font-medium">Avg Value</span>
          </div>
          <div class="text-xl font-bold text-gray-900 mb-1">
            @if($avgBidValueK >= 1000)
              ?<span class="stat-number" data-target="{{ round($avgBidValueK / 1000, 1) }}">0</span>M
            @else
              ?<span class="stat-number" data-target="{{ $avgBidValueK }}">0</span>K
            @endif
          </div>
          <div class="text-xs font-medium text-gray-500 mb-2">Average Bid Value</div>
          <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
            <div class="bg-emerald-400 h-1 rounded-full progress-bar" data-width="{{ $avgBidBarWidth }}" style="width:0%"></div>
          </div>
          <p class="text-[11px] text-gray-400 mt-1.5">Based on {{ $totalBids }} total bids</p>
        </div>

        {{-- Avg Response Time --}}
        <div class="bg-white border border-gray-200 border-l-4 border-l-blue-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-blue-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <span class="text-[11px] text-gray-400 font-medium">Submit ? Decision</span>
          </div>
          <div class="text-xl font-bold text-gray-900 mb-1">
            <span class="stat-number" data-target="{{ $avgResponseHours }}">0</span> hrs
          </div>
          <div class="text-xs font-medium text-gray-500 mb-2">Avg Response Time</div>
          <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
            <div class="bg-blue-400 h-1 rounded-full progress-bar" data-width="{{ $responseBarWidth }}" style="width:0%"></div>
          </div>
          <p class="text-[11px] text-gray-400 mt-1.5">From submission to decision</p>
        </div>

        {{-- Bid Win Rate --}}
        <div class="bg-white border border-gray-200 border-l-4 border-l-indigo-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-indigo-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
              </svg>
            </div>
            <span class="text-[11px] text-gray-400 font-medium">Accepted / Total</span>
          </div>
          <div class="text-xl font-bold text-gray-900 mb-1">
            <span class="stat-number" data-target="{{ $bidWinRate }}">0</span>%
          </div>
          <div class="text-xs font-medium text-gray-500 mb-2">Bid Win Rate</div>
          <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
            <div class="bg-indigo-400 h-1 rounded-full progress-bar" data-width="{{ $winRateBarWidth }}" style="width:0%"></div>
          </div>
          <p class="text-[11px] text-gray-400 mt-1.5">Platform-wide bid acceptance rate</p>
        </div>

      </div>

      {{-- GEOGRAPHIC DISTRIBUTION --}}
      <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
        <div class="mb-3">
          <div class="text-sm font-semibold text-gray-800">Geographic Distribution - Zamboanga City Districts</div>
          <div class="text-[11px] text-gray-400">Project distribution across city districts</div>
        </div>

        <div style="height:200px;" class="mb-3">
          <canvas id="geographicDistributionChart"
            data-labels='@json($geoLabels)'
            data-values='@json($geoCounts)'></canvas>
        </div>

        {{-- District Cards --}}
        <div id="districtCardsGrid" class="grid grid-cols-2 lg:grid-cols-4 gap-3 pt-3 border-t border-gray-100">
          @php
            $dColors = [
              'Tetuan'    => ['border' => 'border-l-emerald-400', 'text' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
              'Tumaga'    => ['border' => 'border-l-blue-400',    'text' => 'text-blue-600',    'bg' => 'bg-blue-50'],
              'Malagutay' => ['border' => 'border-l-orange-400',  'text' => 'text-orange-600',  'bg' => 'bg-orange-50'],
              'Others'    => ['border' => 'border-l-gray-400',    'text' => 'text-gray-600',    'bg' => 'bg-gray-50'],
            ];
          @endphp
          @foreach($fourDistricts as $name => $info)
            @php $dc = $dColors[$name] ?? $dColors['Others']; @endphp
            <div class="border border-gray-200 border-l-4 {{ $dc['border'] }} rounded-xl p-3">
              <div class="text-[11px] font-medium text-gray-500 mb-1">{{ $name }}</div>
              <div class="text-xl font-bold {{ $dc['text'] }} stat-number" data-target="{{ $info['count'] }}">0</div>
              <div class="text-[11px] text-gray-400 mt-0.5">Active Projects</div>
              <div class="text-[11px] text-gray-500 mt-0.5">
                @if($info['value'] > 0)
                  ?{{ $info['value'] }}M contracted
                @else
                  No accepted bids yet
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>

      {{-- RECENT BID ACTIVITY TABLE --}}
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
          <div>
            <div class="text-sm font-semibold text-gray-800">Recent Bid Activity</div>
            <div class="text-[11px] text-gray-400">Latest bid submissions and updates</div>
          </div>
          <button id="exportBidsBtn" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="bc-bids-table w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
              <tr>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Contractor</th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Bid Amount</th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Submitted</th>
              </tr>
            </thead>
            <tbody id="recentBidsBody" class="divide-y divide-gray-100">
              @forelse($recentBids as $bid)
                @php
                  $statusCfg = [
                    'accepted'     => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'dot' => 'bg-green-400',  'pulse' => true,  'label' => 'Accepted'],
                    'submitted'    => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'dot' => 'bg-amber-400',  'pulse' => true,  'label' => 'Pending'],
                    'under_review' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'dot' => 'bg-blue-400',   'pulse' => false, 'label' => 'Under Review'],
                    'rejected'     => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'dot' => 'bg-red-400',    'pulse' => false, 'label' => 'Rejected'],
                    'cancelled'    => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'dot' => 'bg-gray-300',   'pulse' => false, 'label' => 'Cancelled'],
                  ];
                  $sc = $statusCfg[$bid->bid_status] ?? $statusCfg['submitted'];
                  $avatarColors = ['bg-blue-100 text-blue-700','bg-indigo-100 text-indigo-700','bg-emerald-100 text-emerald-700','bg-orange-100 text-orange-700','bg-rose-100 text-rose-700','bg-violet-100 text-violet-700'];
                  $avatarCls = $avatarColors[$loop->index % count($avatarColors)];
                @endphp
                <tr class="bc-bid-row hover:bg-gray-50 transition-colors">
                  <td class="px-4 py-2.5">
                    <div class="text-xs font-semibold text-gray-800">{{ $bid->project_title }}</div>
                    <div class="text-[11px] text-gray-400">{{ $bid->project_location }}</div>
                  </td>
                  <td class="px-4 py-2.5">
                    <div class="flex items-center gap-2">
                      @if($bid->company_logo)
                        <img src="{{ asset('storage/' . $bid->company_logo) }}" class="w-7 h-7 rounded-full object-cover" alt="{{ $bid->company_name }}">
                      @else
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold {{ $avatarCls }}">{{ $bid->initials }}</div>
                      @endif
                      <div class="text-xs font-medium text-gray-800">{{ $bid->company_name }}</div>
                    </div>
                  </td>
                  <td class="px-4 py-2.5">
                    <div class="text-xs font-semibold text-gray-800">?{{ number_format($bid->proposed_cost) }}</div>
                    <div class="text-[11px] text-gray-400">
                      @php
                        $diff = $bid->proposed_cost - ($avgBidValueK * 1000);
                        $sign = $diff >= 0 ? '+' : '';
                        $diffK = round(abs($diff) / 1000, 1);
                      @endphp
                      {{ $sign }}{{ $diff >= 0 ? '' : '-' }}?{{ $diffK }}K vs avg
                    </div>
                  </td>
                  <td class="px-4 py-2.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $sc['bg'] }} {{ $sc['text'] }}">
                      <span class="w-1.5 h-1.5 {{ $sc['dot'] }} rounded-full {{ $sc['pulse'] ? 'animate-pulse' : '' }}"></span>
                      {{ $sc['label'] }}
                    </span>
                  </td>
                  <td class="px-4 py-2.5">
                    <div class="text-xs text-gray-700">{{ \Carbon\Carbon::parse($bid->submitted_at)->format('M j, Y') }}</div>
                    <div class="text-[11px] text-gray-400">{{ \Carbon\Carbon::parse($bid->submitted_at)->diffForHumans() }}</div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-4 py-10 text-center text-gray-400">
                    <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div class="text-xs">No bid activity yet</div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- OWNER ACTIVITY + PAYMENT ANALYTICS --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        {{-- Recent Bid Activity by Project --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100">
            <div class="text-sm font-semibold text-gray-800">Recent Bid Activity by Project</div>
            <div class="text-[11px] text-gray-400">Latest accepted &amp; pending bids with contractor info</div>
          </div>
          <div class="overflow-x-auto">
            <table class="bc-owner-table w-full">
              <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                  <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                  <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Contractor</th>
                  <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Bid Value</th>
                  <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody id="ownerActivityBody" class="divide-y divide-gray-100">
                @forelse($ownerActivity as $activity)
                  @php
                    $sc2cls = [
                      'accepted'     => 'bg-green-100 text-green-700',
                      'submitted'    => 'bg-amber-100 text-amber-700',
                      'under_review' => 'bg-purple-100 text-purple-700',
                      'rejected'     => 'bg-red-100 text-red-700',
                      'cancelled'    => 'bg-gray-100 text-gray-600',
                    ][$activity->bid_status] ?? 'bg-gray-100 text-gray-600';
                    $label2 = [
                      'accepted'     => 'Accepted',
                      'submitted'    => 'Pending',
                      'under_review' => 'Under Review',
                      'rejected'     => 'Rejected',
                      'cancelled'    => 'Cancelled',
                    ][$activity->bid_status] ?? 'Unknown';
                  @endphp
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-2.5">
                      <div class="text-xs font-semibold text-gray-800">{{ Str::limit($activity->project_title, 30) }}</div>
                      <div class="text-[11px] text-gray-400">{{ $activity->project_location }}</div>
                    </td>
                    <td class="px-4 py-2.5">
                      <div class="text-xs font-medium text-gray-700">{{ $activity->company_name }}</div>
                    </td>
                    <td class="px-4 py-2.5">
                      <div class="text-xs font-semibold text-gray-800">
                        @if($activity->proposed_cost >= 1_000_000)
                          ?{{ round($activity->proposed_cost / 1_000_000, 1) }}M
                        @else
                          ?{{ number_format($activity->proposed_cost / 1000, 0) }}K
                        @endif
                      </div>
                    </td>
                    <td class="px-4 py-2.5">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $sc2cls }}">{{ $label2 }}</span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-xs text-gray-400">No recent activity</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- Payment Analytics --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100">
            <div class="text-sm font-semibold text-gray-800">Payment Analytics</div>
            <div class="text-[11px] text-gray-400">Milestone payment financial metrics</div>
          </div>
          <div id="paymentAnalyticsCards" class="p-4 space-y-2.5">

            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
              <div>
                <div class="text-xs font-medium text-gray-600">Total Payments Released</div>
                <div class="text-[11px] text-gray-400">This month (approved)</div>
              </div>
              <div class="text-xl font-bold text-teal-600">&#8369;<span class="stat-number" data-target="{{ $paymentsReleasedM }}">0</span>M</div>
            </div>

            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
              <div>
                <div class="text-xs font-medium text-gray-600">Pending Payments</div>
                <div class="text-[11px] text-gray-400">Awaiting approval</div>
              </div>
              <div class="text-xl font-bold text-orange-500">&#8369;<span class="stat-number" data-target="{{ $pendingPaymentsM }}">0</span>M</div>
            </div>

            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
              <div>
                <div class="text-xs font-medium text-gray-600">Avg Processing Time</div>
                <div class="text-[11px] text-gray-400">Transaction date &rarr; Approval</div>
              </div>
              <div class="text-xl font-bold text-blue-500"><span class="stat-number" data-target="{{ $avgPaymentDays }}">0</span> days</div>
            </div>

            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3">
              <div>
                <div class="text-xs font-medium text-gray-600">Payment Success Rate</div>
                <div class="text-[11px] text-gray-400">Approved vs Rejected</div>
              </div>
              <div class="text-xl font-bold text-emerald-600"><span class="stat-number" data-target="{{ $paymentSuccessRate }}">0</span>%</div>
            </div>

          </div>
        </div>

      </div>{{-- end owner+payment grid --}}

    </div>{{-- end bc-shell --}}
  </main>

</div>

<script src="{{ asset('js/admin/home/bidCompletion_Analytics.js') }}" defer></script>

<script>
  // ── AUTO-FILTER ON DATE CHANGE ─────────────────────────────────────
  function validateAndApplyDateFilter() {
    const fromInput = document.getElementById('globalDateFrom');
    const toInput = document.getElementById('globalDateTo');
    const fromVal = fromInput?.value || '';
    const toVal = toInput?.value || '';

    // Validate: to date should not be earlier than from date
    if (fromVal && toVal && toVal < fromVal) {
      toInput.value = fromVal;
      return;
    }

    // Clear preset button active state when custom dates are used
    document.querySelectorAll('.date-preset-btn').forEach(b => {
      b.classList.remove('active', 'border-indigo-400', 'text-white', 'bg-indigo-500', 'font-semibold');
      b.classList.add('border-gray-200', 'text-gray-500', 'font-medium');
    });

    // Trigger filter
    refreshBidData(fromVal, toVal);
  }

  // Validate on both change and input events for real-time validation
  document.getElementById('globalDateFrom')?.addEventListener('change', validateAndApplyDateFilter);
  document.getElementById('globalDateFrom')?.addEventListener('input', function() {
    const fromVal = this.value || '';
    const toInput = document.getElementById('globalDateTo');
    const toVal = toInput?.value || '';
    
    // Prevent to date from being earlier than from date
    if (fromVal && toVal && toVal < fromVal) {
      toInput.value = fromVal;
    }
  });

  document.getElementById('globalDateTo')?.addEventListener('change', validateAndApplyDateFilter);
  document.getElementById('globalDateTo')?.addEventListener('input', function() {
    const toVal = this.value || '';
    const fromInput = document.getElementById('globalDateFrom');
    const fromVal = fromInput?.value || '';
    
    // Prevent to date from being earlier than from date
    if (fromVal && toVal && toVal < fromVal) {
      this.value = fromVal;
    }
  });

  // ── PRESET BUTTONS ─────────────────────────────────────────────────
  function getDateRange(preset) {
    const now = new Date();
    let from = '', to = now.toISOString().split('T')[0];
    switch (preset) {
      case 'last3months': { const d = new Date(now); d.setMonth(d.getMonth() - 3); from = d.toISOString().split('T')[0]; break; }
      case 'last6months': { const d = new Date(now); d.setMonth(d.getMonth() - 6); from = d.toISOString().split('T')[0]; break; }
      case 'thisyear':    from = now.getFullYear() + '-01-01'; break;
      case 'lastyear':    from = (now.getFullYear() - 1) + '-01-01'; to = (now.getFullYear() - 1) + '-12-31'; break;
      case 'all':         from = ''; to = ''; break;
    }
    return { from, to };
  }

  document.querySelectorAll('.date-preset-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.date-preset-btn').forEach(b => {
        b.classList.remove('active', 'border-indigo-400', 'text-white', 'bg-indigo-500', 'font-semibold');
        b.classList.add('border-gray-200', 'text-gray-500', 'font-medium');
      });
      this.classList.add('active', 'border-indigo-400', 'text-white', 'bg-indigo-500', 'font-semibold');
      this.classList.remove('border-gray-200', 'text-gray-500', 'font-medium');
      const range = getDateRange(this.dataset.range);
      document.getElementById('globalDateFrom').value = range.from;
      document.getElementById('globalDateTo').value = range.to;
      refreshBidData(range.from, range.to);
    });
  });

  // ── RESET BUTTON ───────────────────────────────────────────────────
  document.getElementById('resetGlobalDateFilter')?.addEventListener('click', function () {
    document.getElementById('globalDateFrom').value = '';
    document.getElementById('globalDateTo').value = '';
    
    // Set "All Time" as active
    document.querySelectorAll('.date-preset-btn').forEach(b => {
      b.classList.remove('active', 'border-indigo-400', 'text-white', 'bg-indigo-500', 'font-semibold');
      b.classList.add('border-gray-200', 'text-gray-500', 'font-medium');
    });
    document.querySelector('[data-range="all"]').classList.add('active', 'border-indigo-400', 'text-white', 'bg-indigo-500', 'font-semibold');
    document.querySelector('[data-range="all"]').classList.remove('border-gray-200', 'text-gray-500', 'font-medium');
    
    refreshBidData('', '');
  });

  // ── REFRESH BID DATA ────────────────────────────────────────────────
  function initCharts(data) {
    // Charts are already managed by bidCompletion_Analytics.js
    // Just call the existing build functions
    buildTimelineChart(data.timelineMonths, data.timelineSubmitted, data.timelineAccepted);
    buildStatusChart(Object.keys(data.bidStatusCounts), Object.values(data.bidStatusCounts));
    buildGeoChart(data.geoLabels, data.geoCounts);
  }

  function refreshBidData(dateFrom, dateTo) {
    const loading = document.getElementById('filterLoading');
    if (loading) loading.classList.remove('hidden');

    const params = new URLSearchParams();
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo)   params.set('date_to', dateTo);

    fetch('/admin/analytics/bid-data?' + params.toString())
      .then(r => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      })
      .then(data => {
        // ── UPDATE KPI CARDS ──────────────────────────────────────────
        const updateStat = (selector, value) => {
          const el = document.querySelector(selector);
          if (el) {
            el.setAttribute('data-target', value);
            animateNumber(el);
          }
        };

        // Hero cards
        updateStat('.bc-kpi:nth-child(1) .stat-number', data.totalProjects);
        updateStat('.bc-kpi:nth-child(2) .stat-number', data.activeContractors);
        updateStat('.bc-kpi:nth-child(3) .stat-number', data.totalValueM);
        updateStat('.bc-kpi:nth-child(4) .stat-number', data.completionRate);

        // ── UPDATE CHARTS ─────────────────────────────────────────────
        initCharts(data);

        // ── UPDATE BID METRIC CARDS ───────────────────────────────────
        const metricCards = document.querySelectorAll('.border-l-4.border-l-emerald-400, .border-l-4.border-l-blue-400, .border-l-4.border-l-indigo-400');
        if (metricCards[0]) {
          const avgVal = data.avgBidValueK >= 1000 ? (data.avgBidValueK / 1000).toFixed(1) : data.avgBidValueK;
          updateStat('.border-l-emerald-400 .stat-number', avgVal);
        }
        if (metricCards[1]) {
          updateStat('.border-l-blue-400 .stat-number', data.avgResponseHours);
        }
        if (metricCards[2]) {
          updateStat('.border-l-indigo-400 .stat-number', data.bidWinRate);
        }

        // ── UPDATE PROGRESS BARS ──────────────────────────────────────
        const progressBars = document.querySelectorAll('.progress-bar');
        if (progressBars[0]) progressBars[0].style.width = data.avgBidBarWidth + '%';
        if (progressBars[1]) progressBars[1].style.width = data.responseBarWidth + '%';
        if (progressBars[2]) progressBars[2].style.width = data.winRateBarWidth + '%';

        // ── UPDATE RECENT BIDS TABLE ──────────────────────────────────
        updateRecentBidsTable(data.recentBids, data.avgBidValueK);

        // ── UPDATE OWNER ACTIVITY TABLE ───────────────────────────────
        updateOwnerActivityTable(data.ownerActivity);

        // ── UPDATE PAYMENT ANALYTICS CARDS ────────────────────────────
        const paymentCards = document.querySelectorAll('#paymentAnalyticsCards .stat-number');
        if (paymentCards[0]) updateStat('#paymentAnalyticsCards .stat-number:nth-of-type(1)', data.paymentsReleasedM);
        if (paymentCards[1]) updateStat('#paymentAnalyticsCards .stat-number:nth-of-type(2)', data.pendingPaymentsM);
        if (paymentCards[2]) updateStat('#paymentAnalyticsCards .stat-number:nth-of-type(3)', data.avgPaymentDays);
        if (paymentCards[3]) updateStat('#paymentAnalyticsCards .stat-number:nth-of-type(4)', data.paymentSuccessRate);

        // ── UPDATE DISTRICT CARDS ─────────────────────────────────────
        const districtCards = document.querySelectorAll('#districtCardsGrid > div');
        const districtNames = ['Tetuan', 'Tumaga', 'Malagutay', 'Others'];
        districtCards.forEach((card, i) => {
          const districtName = districtNames[i];
          const idx = data.geoLabels ? data.geoLabels.indexOf(districtName) : -1;
          if (idx !== -1 && data.geoCounts) {
            const statNum = card.querySelector('.stat-number');
            if (statNum) {
              statNum.setAttribute('data-target', data.geoCounts[idx]);
              animateNumber(statNum);
            }
            // Update the value text if it exists
            const valueText = card.querySelector('.text-[11px].text-gray-500');
            if (valueText && data.fourDistricts && data.fourDistricts[districtName]) {
              const value = data.fourDistricts[districtName].value;
              valueText.textContent = value > 0 ? '₱' + value + 'M contracted' : 'No accepted bids yet';
            }
          }
        });

        if (loading) loading.classList.add('hidden');
      })
      .catch(err => { 
        console.error('Bid data filter error:', err); 
        if (loading) loading.classList.add('hidden'); 
      });
  }
</script>
</body>
</html>




