<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>User Activity Analytics - Legatura Admin</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo2.0-favicon.svg') }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/userActivity_Analytics.css') }}">

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
    @include('admin.layouts.topnav', ['pageTitle' => 'User Activity Analytics'])

    <div class="ua-shell p-4 lg:p-5 space-y-3.5">

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
              <input type="date" id="globalDateFrom" class="bg-white text-xs text-gray-700 font-medium px-3 py-2 focus:outline-none cursor-pointer min-w-0 border-0 outline-none">
            </div>
            <span class="text-gray-300 font-bold text-base">?</span>
            <div class="date-pill flex items-center rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
              <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2 self-stretch">
                <i class="fi fi-rr-calendar text-white text-xs leading-none"></i>
                <span class="text-[10px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
              </div>
              <input type="date" id="globalDateTo" class="bg-white text-xs text-gray-700 font-medium px-3 py-2 focus:outline-none cursor-pointer min-w-0 border-0 outline-none">
            </div>
            <button id="resetGlobalDateFilter" class="px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 transition-colors flex items-center gap-1.5">
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

      {{-- TOP KPI CARDS --}}
      <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">

        {{-- Total Users --}}
        <div class="ua-kpi bg-white border border-gray-200 border-l-4 border-l-indigo-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-indigo-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
          </div>
          <div id="statTotalUsers" class="text-2xl font-bold text-gray-900">{{ number_format($userMetrics['total_users']) }}</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Total Users</div>
          <div class="text-[11px] text-gray-400 mt-0.5">vs all-time prior</div>
        </div>

        {{-- Property Owners --}}
        <div class="ua-kpi bg-white border border-gray-200 border-l-4 border-l-emerald-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-emerald-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
              </svg>
            </div>
          </div>
          <div id="statPropertyOwners" class="text-2xl font-bold text-gray-900">{{ number_format($userMetrics['property_owners']) }}</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Property Owners</div>
          <div class="text-[11px] text-gray-400 mt-0.5">registered accounts</div>
        </div>

        {{-- Contractors --}}
        <div class="ua-kpi bg-white border border-gray-200 border-l-4 border-l-amber-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-amber-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
          </div>
          <div id="statContractors" class="text-2xl font-bold text-gray-900">{{ number_format($userMetrics['contractors']) }}</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Contractors</div>
          <div class="text-[11px] text-gray-400 mt-0.5">registered companies</div>
        </div>

        {{-- Active Projects --}}
        <div class="ua-kpi bg-white border border-gray-200 border-l-4 border-l-rose-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-rose-50 p-2 rounded-lg">
              <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
          </div>
          <div id="statActiveProjects" class="text-2xl font-bold text-gray-900">{{ number_format($userMetrics['active_projects']) }}</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Active Projects</div>
          <div class="text-[11px] text-gray-400 mt-0.5">currently in-progress</div>
        </div>

      </div>

      {{-- ACCOUNT STATUS ROW --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

        {{-- Active Accounts --}}
        @php
          $activeRatio = $userMetrics['total_users'] > 0
            ? round(($userMetrics['active_users'] / $userMetrics['total_users']) * 100) : 0;
        @endphp
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2.5">
              <div class="bg-cyan-50 p-2 rounded-lg">
                <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Active Accounts</p>
                <div id="statActiveUsers" class="text-xl font-bold text-gray-900">{{ number_format($userMetrics['active_users']) }}</div>
              </div>
            </div>
            <span class="text-xs font-semibold text-cyan-600 bg-cyan-50 px-2 py-0.5 rounded-full">{{ $activeRatio }}%</span>
          </div>
          <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
            <div class="bg-cyan-400 h-1 rounded-full" style="width:{{ $activeRatio }}%"></div>
          </div>
          <p class="text-[11px] text-gray-400 mt-1.5">Users with active status</p>
        </div>

        {{-- Suspended --}}
        @php
          $suspRatio = $userMetrics['total_users'] > 0
            ? round(($userMetrics['suspended_users'] / $userMetrics['total_users']) * 100) : 0;
        @endphp
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2.5">
              <div class="bg-red-50 p-2 rounded-lg">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
              </div>
              <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Suspended</p>
                <div id="statSuspended" class="text-xl font-bold text-gray-900">{{ number_format($userMetrics['suspended_users']) }}</div>
              </div>
            </div>
            <span class="text-xs font-semibold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">{{ $suspRatio }}%</span>
          </div>
          <div class="w-full bg-gray-100 rounded-full h-1 overflow-hidden">
            <div class="bg-red-400 h-1 rounded-full" style="width:{{ $suspRatio }}%"></div>
          </div>
          <p class="text-[11px] text-gray-400 mt-1.5">Accounts with restricted access</p>
        </div>

        {{-- New This Month --}}
        @php
          $prevMonth = $userMetrics['new_last_month'];
          $curMonth  = $userMetrics['new_this_month'];
          $growthPct = $prevMonth > 0 ? round((($curMonth - $prevMonth) / $prevMonth) * 100, 1) : 0;
        @endphp
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2.5">
              <div class="bg-green-50 p-2 rounded-lg">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
              </div>
              <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">New This Month</p>
                <div id="statNewThisMonth" class="text-xl font-bold text-gray-900">{{ number_format($userMetrics['new_this_month']) }}</div>
              </div>
            </div>
            @if($growthPct >= 0)
              <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">+{{ $growthPct }}%</span>
            @else
              <span class="text-[11px] font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">{{ $growthPct }}%</span>
            @endif
          </div>
          <p class="text-[11px] text-gray-400">Registrations in {{ now()->format('F Y') }} &nbsp;-&nbsp; last month: {{ number_format($prevMonth) }}</p>
        </div>

      </div>

      {{-- CHARTS ROW --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        {{-- User Growth Chart --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <div class="mb-3">
            <h3 class="text-sm font-semibold text-gray-800">User Growth</h3>
            <p class="text-[11px] text-gray-400 mt-0.5">Monthly registrations - last 12 months</p>
          </div>
          <div style="height:230px; position:relative;">
            <canvas id="userGrowthChart"></canvas>
          </div>
        </div>

        {{-- User Distribution Chart --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <div class="mb-3">
            <h3 class="text-sm font-semibold text-gray-800">User Distribution</h3>
            <p class="text-[11px] text-gray-400 mt-0.5">Breakdown by account type</p>
          </div>
          <div class="ua-dist-wrap">
            <div style="height:180px; position:relative; width:180px; flex-shrink:0;">
              <canvas id="userDistributionChart"></canvas>
            </div>
            <div class="ua-dist-legend">
              @php
                $distColors = ['#6366f1','#f97316','#10b981','#64748b'];
                $di = 0;
              @endphp
              @foreach($userGrowth['distribution'] as $label => $count)
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $distColors[$di] }};"></span>
                  <span class="text-xs text-gray-600 truncate">{{ $label }}</span>
                  <span class="text-xs font-bold text-gray-800 ml-auto">{{ number_format($count) }}</span>
                </div>
                @php $di++ @endphp
              @endforeach
            </div>
          </div>
        </div>

      </div>

      {{-- RECENT ACTIVITY TABLE --}}
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Recent Activity</h3>
              <p class="text-[11px] text-gray-400 mt-0.5">Latest user actions from bids &amp; project posts</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <input type="text" id="activitySearch" placeholder="Search user..."
                class="px-2.5 py-1.5 rounded-lg text-xs border border-gray-200 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400 outline-none w-40">
              <div class="date-pill flex items-center rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-1.5 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-[10px] leading-none"></i>
                  <span class="text-[9px] font-bold text-indigo-100 uppercase tracking-wider select-none">From</span>
                </div>
                <input type="date" id="activityDateFrom" class="bg-white text-xs text-gray-700 font-medium px-2.5 py-1.5 focus:outline-none cursor-pointer min-w-0 border-0 outline-none">
              </div>
              <span class="text-gray-300 font-bold">?</span>
              <div class="date-pill flex items-center rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-1.5 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-[10px] leading-none"></i>
                  <span class="text-[9px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
                </div>
                <input type="date" id="activityDateTo" class="bg-white text-xs text-gray-700 font-medium px-2.5 py-1.5 focus:outline-none cursor-pointer min-w-0 border-0 outline-none">
              </div>
              <button id="activityResetBtn"
                class="px-3 py-1.5 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 transition-colors flex items-center gap-1.5">
                <i class="fi fi-rr-rotate-left text-[10px]"></i>
                Reset
              </button>
            </div>
          </div>
        </div>

        {{-- Table --}}
        <div id="activityPanel" class="overflow-x-auto">
          <table class="w-full ua-activity-table">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-100">
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">User</th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Type</th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Action</th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Time</th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              @forelse($recentActivity as $activity)
                @php
                  $initials  = collect(explode(' ', $activity->full_name ?? 'U N'))
                                 ->map(fn($w) => strtoupper(substr($w,0,1)))
                                 ->take(2)->implode('');
                  $typeLabel = match($activity->user_type ?? '') {
                      'property_owner' => 'Property Owner',
                      'contractor'     => 'Contractor',
                      'both'           => 'Both',
                      default          => ucfirst($activity->user_type ?? 'User'),
                  };
                  $typeBadge = match($activity->user_type ?? '') {
                      'property_owner' => 'bg-emerald-50 text-emerald-700',
                      'contractor'     => 'bg-amber-50 text-amber-700',
                      'both'           => 'bg-indigo-50 text-indigo-700',
                      default          => 'bg-gray-100 text-gray-600',
                  };
                  $avatarColor = match($activity->user_type ?? '') {
                      'property_owner' => 'bg-emerald-100 text-emerald-700',
                      'contractor'     => 'bg-amber-100 text-amber-700',
                      'both'           => 'bg-indigo-100 text-indigo-700',
                      default          => 'bg-gray-100 text-gray-600',
                  };
                  $isActive = (bool)($activity->is_active ?? false);
                  $timeAgo  = \Carbon\Carbon::parse($activity->activity_time)->diffForHumans();
                @endphp
                <tr class="ua-activity-row">
                  <td class="px-4 py-2.5">
                    <div class="flex items-center gap-2.5">
                      <div class="w-7 h-7 rounded-full {{ $avatarColor }} flex items-center justify-center text-[10px] font-bold shrink-0">
                        {{ $initials }}
                      </div>
                      <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-800 truncate max-w-[160px]">{{ $activity->full_name ?? 'Unknown' }}</p>
                        <p class="text-[10px] text-gray-400 truncate max-w-[160px]">{{ $activity->email ?? '' }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-2.5">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $typeBadge }}">{{ $typeLabel }}</span>
                  </td>
                  <td class="px-4 py-2.5 text-xs text-gray-700">{{ $activity->action }}</td>
                  <td class="px-4 py-2.5 text-[11px] text-gray-500">{{ $timeAgo }}</td>
                  <td class="px-4 py-2.5">
                    @if($isActive)
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-semibold">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Active
                      </span>
                    @else
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-[10px] font-semibold">
                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>Inactive
                      </span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="px-4 py-12 text-center text-xs text-gray-400">No recent activity found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        <div id="activityPagination" class="border-t border-gray-100"></div>

      </div>

    </div>{{-- /ua-shell --}}
  </main>
</div>

{{-- ================================================================ --}}
{{-- CHART DATA + FILTER LOGIC (unchanged from original)              --}}
{{-- ================================================================ --}}
<script>
  const growthMonths      = @json($userGrowth['months']);
  const growthOwners      = @json($userGrowth['owners']);
  const growthContractors = @json($userGrowth['contractors']);
  const growthTotals      = @json($userGrowth['totals']);

  const distLabels = @json(array_keys($userGrowth['distribution']));
  const distValues = @json(array_values($userGrowth['distribution']));

  let userGrowthChart = null;
  let userDistChart   = null;

  function buildUserGrowthChart(months, owners, contractors, totals) {
    const el = document.getElementById('userGrowthChart');
    if (!el) return;
    if (userGrowthChart) userGrowthChart.destroy();
    userGrowthChart = new Chart(el, {
      type: 'line',
      data: {
        labels: months,
        datasets: [
          { label: 'Property Owners', data: owners, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', tension: 0.4, fill: true, pointBackgroundColor: '#10b981', pointRadius: 3, borderWidth: 2 },
          { label: 'Contractors', data: contractors, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.08)', tension: 0.4, fill: true, pointBackgroundColor: '#f97316', pointRadius: 3, borderWidth: 2 },
          { label: 'Total', data: totals, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.04)', tension: 0.4, fill: false, pointBackgroundColor: '#6366f1', pointRadius: 3, borderWidth: 2, borderDash: [5,4] },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 7, font: { size: 11 } } }, tooltip: { mode: 'index' } },
        scales: {
          y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 } } },
          x: { grid: { display: false }, ticks: { font: { size: 10 } } },
        },
      },
    });
  }

  function buildUserDistChart(labels, values) {
    const el = document.getElementById('userDistributionChart');
    if (!el) return;
    if (userDistChart) userDistChart.destroy();
    userDistChart = new Chart(el, {
      type: 'doughnut',
      data: { labels, datasets: [{ data: values, backgroundColor: ['#6366f1','#f97316','#10b981','#64748b'], borderWidth: 2, borderColor: '#fff', hoverOffset: 4 }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString() } }
        }
      },
    });
  }

  buildUserGrowthChart(growthMonths, growthOwners, growthContractors, growthTotals);
  buildUserDistChart(distLabels, distValues);


  // -- GLOBAL DATE FILTER ----------------------------------------------
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

  function refreshUserData(dateFrom, dateTo) {
    const loading = document.getElementById('filterLoading');
    if (loading) loading.classList.remove('hidden');

    const params = new URLSearchParams();
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo)   params.set('date_to', dateTo);

    fetch('/admin/analytics/user-data?' + params.toString(), {
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
      .then(r => {
        const ct = r.headers.get('content-type') || '';
        if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t); });
        if (!ct.includes('application/json')) return r.text().then(t => { console.error('Non-JSON response:', t); throw new Error('Non-JSON response'); });
        return r.json();
      })
      .then(data => {
        const um = data.userMetrics;
        const el = id => document.getElementById(id);
        if (el('statTotalUsers'))     el('statTotalUsers').textContent     = Number(um.total_users).toLocaleString();
        if (el('statPropertyOwners')) el('statPropertyOwners').textContent = Number(um.property_owners).toLocaleString();
        if (el('statContractors'))    el('statContractors').textContent    = Number(um.contractors).toLocaleString();
        if (el('statActiveProjects')) el('statActiveProjects').textContent = Number(um.active_projects).toLocaleString();
        if (el('statActiveUsers'))    el('statActiveUsers').textContent    = Number(um.active_users).toLocaleString();
        if (el('statSuspended'))      el('statSuspended').textContent      = Number(um.suspended_users).toLocaleString();
        if (el('statNewThisMonth'))   el('statNewThisMonth').textContent   = Number(um.new_this_month).toLocaleString();

        const ug = data.userGrowth;
        buildUserGrowthChart(ug.months, ug.owners, ug.contractors, ug.totals);
        buildUserDistChart(Object.keys(ug.distribution), Object.values(ug.distribution));

        const legendItems = document.querySelectorAll('.ua-dist-legend > div');
        const distVals = Object.values(ug.distribution);
        legendItems.forEach((item, i) => {
          const strong = item.querySelector('span.font-bold, span:last-child');
          if (strong && distVals[i] !== undefined) strong.textContent = Number(distVals[i]).toLocaleString();
        });

        if (loading) loading.classList.add('hidden');
      })
      .catch(err => { console.error('User data filter error:', err); if (loading) loading.classList.add('hidden'); });
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
      document.getElementById('globalDateTo').value   = range.to;
      refreshUserData(range.from, range.to);
    });
  });


  // -- RECENT ACTIVITY FEED --------------------------------------------
  let activityPage = 1;

  function fetchActivity(page) {
    page = page || 1;
    activityPage = page;
    const params = new URLSearchParams();
    const search   = document.getElementById('activitySearch')?.value || '';
    const dateFrom = document.getElementById('activityDateFrom')?.value || '';
    const dateTo   = document.getElementById('activityDateTo')?.value || '';
    if (search)   params.set('search', search);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo)   params.set('date_to', dateTo);
    params.set('page', page);

    const panel = document.getElementById('activityPanel');
    if (panel) panel.style.opacity = '0.5';

    fetch('/admin/analytics/user-activity-feed-data?' + params.toString(), {
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
      .then(r => {
        const ct = r.headers.get('content-type') || '';
        if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t); });
        if (!ct.includes('application/json')) return r.text().then(t => { console.error('Non-JSON response:', t); throw new Error('Non-JSON response'); });
        return r.json();
      })
      .then(data => {
        renderActivityTable(data);
        renderActivityPagination(data);
        if (panel) panel.style.opacity = '1';
      })
      .catch(err => { console.error('Activity fetch error:', err); if (panel) panel.style.opacity = '1'; });
  }

  function renderActivityTable(data) {
    const panel = document.getElementById('activityPanel');
    if (!panel) return;

    if (!data.data || data.data.length === 0) {
      panel.innerHTML = '<div class="px-4 py-10 text-center text-xs text-gray-400">No recent activity found.</div>';
      return;
    }

    const typeBadge  = { property_owner: 'bg-emerald-50 text-emerald-700', contractor: 'bg-amber-50 text-amber-700', both: 'bg-indigo-50 text-indigo-700' };
    const avatarColor = { property_owner: 'bg-emerald-100 text-emerald-700', contractor: 'bg-amber-100 text-amber-700', both: 'bg-indigo-100 text-indigo-700' };

    const esc = s => s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : '';

    const rows = data.data.map(a => {
      const tb  = typeBadge[a.user_type]   || 'bg-gray-100 text-gray-600';
      const ac  = avatarColor[a.user_type] || 'bg-gray-100 text-gray-600';
      const avatar = a.profile_pic
        ? `<img src="${esc(a.profile_pic)}" class="w-7 h-7 rounded-full object-cover shrink-0">`
        : `<div class="w-7 h-7 rounded-full ${ac} flex items-center justify-center text-[10px] font-bold shrink-0">${esc(a.initials)}</div>`;
      const status = a.is_active
        ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-semibold"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Active</span>`
        : `<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-[10px] font-semibold"><span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>Inactive</span>`;

      return `<tr class="ua-activity-row">
        <td class="px-4 py-2.5"><div class="flex items-center gap-2.5">${avatar}<div class="min-w-0"><p class="text-xs font-semibold text-gray-800 truncate max-w-[160px]">${esc(a.full_name)}</p><p class="text-[10px] text-gray-400 truncate max-w-[160px]">${esc(a.email)}</p></div></div></td>
        <td class="px-4 py-2.5"><span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold ${tb}">${esc(a.type_label)}</span></td>
        <td class="px-4 py-2.5 text-xs text-gray-700">${esc(a.action)}</td>
        <td class="px-4 py-2.5 text-[11px] text-gray-500">${esc(a.time_ago)}</td>
        <td class="px-4 py-2.5">${status}</td>
      </tr>`;
    }).join('');

    panel.innerHTML = `<div class="overflow-x-auto"><table class="w-full ua-activity-table">
      <thead><tr class="bg-gray-50 border-b border-gray-100">
        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">User</th>
        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Type</th>
        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Action</th>
        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Time</th>
        <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-50">${rows}</tbody>
    </table></div>`;
  }

  function renderActivityPagination(data) {
    const wrap = document.getElementById('activityPagination');
    if (!wrap || data.last_page <= 1) { if (wrap) wrap.innerHTML = ''; return; }

    const cur = data.current_page, last = data.last_page;
    let html = `<div class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
      <div class="text-xs text-gray-500">Showing <span class="font-semibold text-gray-700">${data.from}</span>&ndash;<span class="font-semibold text-gray-700">${data.to}</span> of <span class="font-semibold text-gray-700">${data.total}</span></div>
      <div class="flex items-center gap-1">`;

    if (cur > 1) html += `<button onclick="fetchActivity(${cur-1})" class="px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">&larr; Prev</button>`;
    for (let p = Math.max(1, cur-2); p <= Math.min(last, cur+2); p++) {
      html += p === cur
        ? `<span class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white">${p}</span>`
        : `<button onclick="fetchActivity(${p})" class="px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">${p}</button>`;
    }
    if (cur < last) html += `<button onclick="fetchActivity(${cur+1})" class="px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">Next &rarr;</button>`;
    html += `</div></div>`;
    wrap.innerHTML = html;
  }

  // Auto-apply global date inputs with debounce and enforce to >= from
  let globalDateTimer;
  const scheduleGlobalRefresh = () => {
    clearTimeout(globalDateTimer);
    globalDateTimer = setTimeout(() => {
      const from = document.getElementById('globalDateFrom')?.value || '';
      const to   = document.getElementById('globalDateTo')?.value || '';
      refreshUserData(from, to);
    }, 450);
  };

  const gFromEl = document.getElementById('globalDateFrom');
  const gToEl   = document.getElementById('globalDateTo');
  if (gFromEl && gToEl) {
    gFromEl.addEventListener('input', () => {
      if (gToEl.value && gFromEl.value && gToEl.value < gFromEl.value) {
        gToEl.value = gFromEl.value;
      }
      gToEl.min = gFromEl.value || '';
      // clear preset active state when custom dates selected
      document.querySelectorAll('.date-preset-btn').forEach(b => {
        b.classList.remove('active','border-indigo-400','text-white','bg-indigo-500','font-semibold');
        b.classList.add('border-gray-200','text-gray-500','font-medium');
      });
      scheduleGlobalRefresh();
    });
    gToEl.addEventListener('input', () => {
      if (gFromEl.value && gToEl.value && gToEl.value < gFromEl.value) {
        gFromEl.value = gToEl.value;
      }
      scheduleGlobalRefresh();
    });
  }

  // Reset global filter (replaces Apply)
  document.getElementById('resetGlobalDateFilter')?.addEventListener('click', function () {
    if (gFromEl) gFromEl.value = '';
    if (gToEl) { gToEl.value = ''; gToEl.min = ''; }
    document.querySelectorAll('.date-preset-btn').forEach(b => {
      b.classList.remove('active','border-indigo-400','text-white','bg-indigo-500','font-semibold');
      b.classList.add('border-gray-200','text-gray-500','font-medium');
    });
    const allBtn = document.querySelector('.date-preset-btn[data-range="all"]');
    if (allBtn) {
      allBtn.classList.remove('border-gray-200','text-gray-500','font-medium');
      allBtn.classList.add('active','border-indigo-400','text-white','bg-indigo-500','font-semibold');
    }
    refreshUserData('', '');
  });

  // ── RECENT ACTIVITY FEED: auto-apply date filters and Reset button
  let activityDateTimer;
  const scheduleActivityFetch = () => {
    clearTimeout(activityDateTimer);
    activityDateTimer = setTimeout(() => fetchActivity(1), 450);
  };

  const aFromEl = document.getElementById('activityDateFrom');
  const aToEl   = document.getElementById('activityDateTo');
  if (aFromEl && aToEl) {
    aFromEl.addEventListener('input', () => {
      if (aToEl.value && aFromEl.value && aToEl.value < aFromEl.value) {
        aToEl.value = aFromEl.value;
      }
      aToEl.min = aFromEl.value || '';
      scheduleActivityFetch();
    });
    aToEl.addEventListener('input', () => {
      if (aFromEl.value && aToEl.value && aToEl.value < aFromEl.value) {
        aFromEl.value = aToEl.value;
      }
      scheduleActivityFetch();
    });
  }

  // Reset activity filters (replaces Filter button)
  document.getElementById('activityResetBtn')?.addEventListener('click', () => {
    const s = document.getElementById('activitySearch'); if (s) s.value = '';
    if (aFromEl) { aFromEl.value = ''; aFromEl.min = ''; }
    if (aToEl)   { aToEl.value = ''; aToEl.min = ''; }
    fetchActivity(1);
  });

  let activitySearchTimer;
  document.getElementById('activitySearch')?.addEventListener('input', function () {
    clearTimeout(activitySearchTimer);
    activitySearchTimer = setTimeout(() => fetchActivity(1), 450);
  });

  // mark that inline filter handlers are bound so bundled JS can skip rebinding
  try { document.body.dataset.uaFiltersBound = '1'; } catch (e) { /* ignore */ }
</script>

<script src="{{ asset('js/admin/home/userActivity_Analytics.js') }}" defer></script>
</body>
</html>
