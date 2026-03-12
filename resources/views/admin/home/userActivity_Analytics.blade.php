<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>User Activity Analytics – Legatura Admin</title>

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

  {{-- ================================================================ --}}
  {{-- SIDEBAR                                                           --}}
  {{-- ================================================================ --}}
  @include('admin.layouts.sidebar')

  {{-- ================================================================ --}}
  {{-- MAIN                                                              --}}
  {{-- ================================================================ --}}
  <main class="flex-1">

    {{-- Header --}}
    @include('admin.layouts.topnav', ['pageTitle' => 'User Activity Analytics'])

    {{-- ============================================================ --}}
    {{-- PAGE BODY                                                      --}}
    {{-- ============================================================ --}}
    <div class="p-8">

      {{-- ── GLOBAL DATE FILTER ── --}}
      <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm mb-8" id="globalDateFilter">
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
            <input type="date" id="globalDateFrom" class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
            <label class="text-xs font-medium text-gray-500">To</label>
            <input type="date" id="globalDateTo" class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
            <button id="applyGlobalDateFilter" class="px-3 py-1.5 bg-indigo-500 text-white text-sm font-semibold rounded-lg hover:bg-indigo-600 transition-colors">Apply</button>
          </div>
          <div id="filterLoading" class="hidden flex items-center gap-1 ml-2">
            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-bounce" style="animation-delay:0s"></span>
            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-bounce" style="animation-delay:0.1s"></span>
            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-bounce" style="animation-delay:0.2s"></span>
          </div>
        </div>
      </div>

      {{-- ── TOP STAT CARDS ── --}}
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        {{-- Total Users --}}
        <div class="group bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative overflow-hidden p-6">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Total Users</p>
                <h3 id="statTotalUsers" class="text-4xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors">
                  {{ number_format($userMetrics['total_users']) }}
                </h3>
              </div>
              <div class="bg-gradient-to-br from-blue-400 to-blue-500 p-4 rounded-2xl shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
              </div>
            </div>
            <div class="flex items-center gap-2 mt-3">
              @if($userMetrics['mom_change'] >= 0)
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span class="text-sm font-bold text-emerald-600">+{{ $userMetrics['mom_change'] }}%</span>
              @else
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17H5m0 0V9m0 8l8-8 4 4 6-6"/></svg>
                <span class="text-sm font-bold text-red-500">{{ $userMetrics['mom_change'] }}%</span>
              @endif
              <span class="text-sm text-gray-600">vs all-time prior</span>
            </div>
          </div>
        </div>

        {{-- Property Owners --}}
        <div class="group bg-gradient-to-br from-white to-emerald-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative overflow-hidden p-6">
          <div class="relative">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Property Owners</p>
                <h3 id="statPropertyOwners" class="text-4xl font-bold text-gray-800 group-hover:text-emerald-600 transition-colors">
                  {{ number_format($userMetrics['property_owners']) }}
                </h3>
              </div>
              <div class="bg-gradient-to-br from-emerald-400 to-emerald-500 p-4 rounded-2xl shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
              </div>
            </div>
            <div class="flex items-center gap-2 mt-3">
              <span class="text-sm text-gray-600">Registered property owners</span>
            </div>
          </div>
        </div>

        {{-- Contractors --}}
        <div class="group bg-gradient-to-br from-white to-orange-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative overflow-hidden p-6">
          <div class="relative">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Contractors</p>
                <h3 id="statContractors" class="text-4xl font-bold text-gray-800 group-hover:text-orange-600 transition-colors">
                  {{ number_format($userMetrics['contractors']) }}
                </h3>
              </div>
              <div class="bg-gradient-to-br from-orange-400 to-orange-500 p-4 rounded-2xl shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
            </div>
            <div class="flex items-center gap-2 mt-3">
              <span class="text-sm text-gray-600">Registered contractor companies</span>
            </div>
          </div>
        </div>

        {{-- Active Projects --}}
        <div class="group bg-gradient-to-br from-white to-red-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative overflow-hidden p-6">
          <div class="relative">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Active Projects</p>
                <h3 id="statActiveProjects" class="text-4xl font-bold text-gray-800 group-hover:text-red-600 transition-colors">
                  {{ number_format($userMetrics['active_projects']) }}
                </h3>
              </div>
              <div class="bg-gradient-to-br from-red-400 to-red-500 p-4 rounded-2xl shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
              </div>
            </div>
            <div class="flex items-center gap-2 mt-3">
              <span class="text-sm text-gray-600">Currently in-progress projects</span>
            </div>
          </div>
        </div>
      </div>

      {{-- ── ACCOUNT STATUS ROW ── --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        {{-- Active Accounts --}}
        <div class="group bg-gradient-to-br from-white to-cyan-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden p-6">
          <div class="relative">
            <div class="flex items-center gap-3 mb-4">
              <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Active Accounts</p>
                <h3 id="statActiveUsers" class="text-2xl font-bold text-gray-800 group-hover:text-cyan-600 transition-colors">
                  {{ number_format($userMetrics['active_users']) }}
                </h3>
              </div>
            </div>
            <p class="text-sm text-gray-600">Users with active status</p>
            @php
              $activeRatio = $userMetrics['total_users'] > 0
                ? round(($userMetrics['active_users'] / $userMetrics['total_users']) * 100)
                : 0;
            @endphp
            <div class="mt-4 bg-cyan-100 rounded-full h-2 overflow-hidden">
              <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-full rounded-full" style="width:{{ $activeRatio }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $activeRatio }}% of total users</p>
          </div>
        </div>

        {{-- Suspended Accounts --}}
        <div class="group bg-gradient-to-br from-white to-red-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden p-6">
          <div class="relative">
            <div class="flex items-center gap-3 mb-4">
              <div class="bg-gradient-to-br from-red-500 to-red-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Suspended</p>
                <h3 id="statSuspended" class="text-2xl font-bold text-gray-800 group-hover:text-red-600 transition-colors">
                  {{ number_format($userMetrics['suspended_users']) }}
                </h3>
              </div>
            </div>
            <p class="text-sm text-gray-600">Accounts with restricted access</p>
            @php
              $suspRatio = $userMetrics['total_users'] > 0
                ? round(($userMetrics['suspended_users'] / $userMetrics['total_users']) * 100)
                : 0;
            @endphp
            <div class="mt-4 bg-red-100 rounded-full h-2 overflow-hidden">
              <div class="bg-gradient-to-r from-red-500 to-pink-500 h-full rounded-full" style="width:{{ $suspRatio }}%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $suspRatio }}% of total users</p>
          </div>
        </div>

        {{-- New Users This Month --}}
        <div class="group bg-gradient-to-br from-white to-green-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden p-6">
          <div class="relative">
            <div class="flex items-center gap-3 mb-4">
              <div class="bg-gradient-to-br from-green-500 to-green-600 p-3 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">New This Month</p>
                <h3 id="statNewThisMonth" class="text-2xl font-bold text-gray-800 group-hover:text-green-600 transition-colors">
                  {{ number_format($userMetrics['new_this_month']) }}
                </h3>
              </div>
            </div>
            <p class="text-sm text-gray-600">Registrations in {{ now()->format('F Y') }}</p>
            @php
              $prevMonth   = $userMetrics['new_last_month'];
              $curMonth    = $userMetrics['new_this_month'];
              $growthPct   = $prevMonth > 0 ? round((($curMonth - $prevMonth) / $prevMonth) * 100, 1) : 0;
            @endphp
            <div class="flex items-center gap-1 mt-4">
              @if($growthPct >= 0)
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                <span class="text-sm font-bold text-emerald-600">+{{ $growthPct }}%</span>
              @else
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17H5m0 0V9m0 8l8-8 4 4 6-6"/></svg>
                <span class="text-sm font-bold text-red-500">{{ $growthPct }}%</span>
              @endif
              <span class="text-xs text-gray-500">vs last month ({{ number_format($prevMonth) }})</span>
            </div>
          </div>
        </div>
      </div>

      {{-- ── CHARTS ROW ── --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        {{-- User Growth Line Chart --}}
        <div class="group bg-gradient-to-br from-white to-purple-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden p-8">
          <div class="relative">
            <div class="flex items-center justify-between mb-6">
              <div>
                <div class="flex items-center gap-3 mb-2">
                  <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                  <h2 class="text-2xl font-bold text-gray-800">User Growth</h2>
                </div>
                <p class="text-sm text-gray-600 ml-14">Monthly registrations (last 12 months)</p>
              </div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-inner">
              <canvas id="userGrowthChart" height="250"></canvas>
            </div>
          </div>
        </div>

        {{-- User Type Distribution Doughnut --}}
        <div class="group bg-gradient-to-br from-white to-indigo-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden p-8">
          <div class="relative">
            <div class="flex items-center justify-between mb-6">
              <div>
                <div class="flex items-center gap-3 mb-2">
                  <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                  </div>
                  <h2 class="text-2xl font-bold text-gray-800">User Distribution</h2>
                </div>
                <p class="text-sm text-gray-600 ml-14">Breakdown by account type</p>
              </div>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-inner">
              <canvas id="userDistributionChart" height="250"></canvas>
            </div>
            {{-- Legend --}}
            <div class="mt-4 grid grid-cols-2 gap-2">
              @php
                $distColors = ['#6366f1','#f97316','#10b981','#64748b'];
                $di = 0;
              @endphp
              @foreach($userGrowth['distribution'] as $label => $count)
                <div class="flex items-center gap-2 text-sm text-gray-700">
                  <span class="inline-block w-3 h-3 rounded-full" style="background:{{ $distColors[$di] }};"></span>
                  {{ $label }}: <strong>{{ number_format($count) }}</strong>
                </div>
                @php $di++ @endphp
              @endforeach
            </div>
          </div>
        </div>
      </div>

      {{-- ── RECENT ACTIVITY TABLE (AJAX-driven) ── --}}
      <div class="group bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 relative overflow-hidden">
        <div class="relative p-8">
          <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
            <div>
              <div class="flex items-center gap-3 mb-2">
                <div class="bg-gradient-to-br from-gray-700 to-gray-800 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Recent Activity</h2>
              </div>
              <p class="text-sm text-gray-600 ml-14">Latest user actions from bids &amp; project posts</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <input type="text" id="activitySearch" placeholder="Search user..." class="px-3 py-2 rounded-lg text-sm border border-gray-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none w-44">
              <input type="date" id="activityDateFrom" class="px-2.5 py-2 rounded-lg text-sm border border-gray-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
              <input type="date" id="activityDateTo" class="px-2.5 py-2 rounded-lg text-sm border border-gray-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
              <button id="activityFilterBtn" class="px-3 py-2 bg-indigo-500 text-white text-sm font-semibold rounded-lg hover:bg-indigo-600 transition-colors">Filter</button>
            </div>
          </div>

          <div id="activityPanel" class="bg-white rounded-xl shadow-inner overflow-hidden">
            {{-- Server-rendered initial table --}}
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Time</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
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
                        'property_owner' => 'bg-emerald-100 text-emerald-700',
                        'contractor'     => 'bg-orange-100 text-orange-700',
                        'both'           => 'bg-indigo-100 text-indigo-700',
                        default          => 'bg-gray-100 text-gray-700',
                    };
                    $avatarGrad = match($activity->user_type ?? '') {
                        'property_owner' => 'from-emerald-400 to-emerald-500',
                        'contractor'     => 'from-orange-400 to-orange-500',
                        'both'           => 'from-indigo-400 to-indigo-500',
                        default          => 'from-gray-400 to-gray-500',
                    };
                    $isActive = (bool)($activity->is_active ?? false);
                    $timeAgo  = \Carbon\Carbon::parse($activity->activity_time)->diffForHumans();
                  @endphp
                  <tr class="hover:bg-blue-50 transition-colors cursor-pointer">
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $avatarGrad }} flex items-center justify-center text-white font-bold shadow-md text-sm">
                          {{ $initials }}
                        </div>
                        <div>
                          <p class="font-semibold text-gray-800">{{ $activity->full_name ?? 'Unknown' }}</p>
                          <p class="text-sm text-gray-500">{{ $activity->email ?? '' }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="px-3 py-1 {{ $typeBadge }} rounded-full text-sm font-semibold">{{ $typeLabel }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-700">{{ $activity->action }}</td>
                    <td class="px-6 py-4 text-gray-600 text-sm">{{ $timeAgo }}</td>
                    <td class="px-6 py-4">
                      @if($isActive)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold flex items-center gap-1 w-fit">
                          <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>Active
                        </span>
                      @else
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-semibold">Inactive</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">No recent activity found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div id="activityPagination" class="mt-4 flex items-center justify-between"></div>
        </div>
      </div>

    </div>{{-- /p-8 --}}
  </main>
</div>

{{-- ================================================================ --}}
{{-- CHART DATA (passed from controller, no hardcoded numbers)         --}}
{{-- ================================================================ --}}
<script>
  // -- Data injected from PHP --
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
          { label: 'Property Owners', data: owners, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.4, fill: true, pointBackgroundColor: '#10b981', pointRadius: 4 },
          { label: 'Contractors', data: contractors, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)', tension: 0.4, fill: true, pointBackgroundColor: '#f97316', pointRadius: 4 },
          { label: 'Total', data: totals, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.07)', tension: 0.4, fill: false, pointBackgroundColor: '#6366f1', pointRadius: 4, borderDash: [5,4] },
        ],
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } }, tooltip: { mode: 'index' } },
        scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } },
      },
    });
  }

  function buildUserDistChart(labels, values) {
    const el = document.getElementById('userDistributionChart');
    if (!el) return;
    if (userDistChart) userDistChart.destroy();
    userDistChart = new Chart(el, {
      type: 'doughnut',
      data: { labels, datasets: [{ data: values, backgroundColor: ['#6366f1','#f97316','#10b981','#64748b'], borderWidth: 2, borderColor: '#fff', hoverOffset: 8 }] },
      options: { responsive: true, cutout: '65%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString() + ' users' } } } },
    });
  }

  // Initial render
  buildUserGrowthChart(growthMonths, growthOwners, growthContractors, growthTotals);
  buildUserDistChart(distLabels, distValues);


  // ═══════════════════════════════════════════════════════════════════
  // GLOBAL DATE FILTER
  // ═══════════════════════════════════════════════════════════════════
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

    fetch('/admin/analytics/user-data?' + params.toString())
      .then(r => r.json())
      .then(data => {
        // Update KPI stat cards
        const um = data.userMetrics;
        const el = id => document.getElementById(id);
        if (el('statTotalUsers'))     el('statTotalUsers').textContent     = Number(um.total_users).toLocaleString();
        if (el('statPropertyOwners')) el('statPropertyOwners').textContent = Number(um.property_owners).toLocaleString();
        if (el('statContractors'))    el('statContractors').textContent    = Number(um.contractors).toLocaleString();
        if (el('statActiveProjects')) el('statActiveProjects').textContent = Number(um.active_projects).toLocaleString();

        // Update account status cards
        if (el('statActiveUsers'))  el('statActiveUsers').textContent  = Number(um.active_users).toLocaleString();
        if (el('statSuspended'))    el('statSuspended').textContent    = Number(um.suspended_users).toLocaleString();
        if (el('statNewThisMonth')) el('statNewThisMonth').textContent = Number(um.new_this_month).toLocaleString();

        // Update charts
        const ug = data.userGrowth;
        buildUserGrowthChart(ug.months, ug.owners, ug.contractors, ug.totals);
        buildUserDistChart(Object.keys(ug.distribution), Object.values(ug.distribution));

        // Update distribution legend
        const legendItems = document.querySelectorAll('.mt-4.grid.grid-cols-2 > div');
        const distKeys = Object.keys(ug.distribution);
        const distVals = Object.values(ug.distribution);
        legendItems.forEach((item, i) => {
          if (distKeys[i] !== undefined) {
            const strong = item.querySelector('strong');
            if (strong) strong.textContent = Number(distVals[i]).toLocaleString();
          }
        });

        if (loading) loading.classList.add('hidden');
      })
      .catch(err => { console.error('User data filter error:', err); if (loading) loading.classList.add('hidden'); });
  }

  document.querySelectorAll('.date-preset-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.date-preset-btn').forEach(b => {
        b.classList.remove('active', 'border-indigo-500', 'text-white', 'bg-indigo-500', 'font-semibold');
        b.classList.add('border-gray-200', 'text-gray-600', 'font-medium');
      });
      this.classList.add('active', 'border-indigo-500', 'text-white', 'bg-indigo-500', 'font-semibold');
      this.classList.remove('border-gray-200', 'text-gray-600', 'font-medium');
      const range = getDateRange(this.dataset.range);
      document.getElementById('globalDateFrom').value = range.from;
      document.getElementById('globalDateTo').value   = range.to;
      refreshUserData(range.from, range.to);
    });
  });

  document.getElementById('applyGlobalDateFilter')?.addEventListener('click', function () {
    document.querySelectorAll('.date-preset-btn').forEach(b => {
      b.classList.remove('active', 'border-indigo-500', 'text-white', 'bg-indigo-500', 'font-semibold');
      b.classList.add('border-gray-200', 'text-gray-600', 'font-medium');
    });
    refreshUserData(document.getElementById('globalDateFrom').value, document.getElementById('globalDateTo').value);
  });


  // ═══════════════════════════════════════════════════════════════════
  // RECENT ACTIVITY FEED — AJAX WITH SEARCH, DATE FILTER, PAGINATION
  // ═══════════════════════════════════════════════════════════════════
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

    fetch('/admin/analytics/user-activity-feed?' + params.toString())
      .then(r => r.json())
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
      panel.innerHTML = '<div class="px-6 py-10 text-center text-gray-500">No recent activity found.</div>';
      return;
    }

    const typeBadge = { property_owner: 'bg-emerald-100 text-emerald-700', contractor: 'bg-orange-100 text-orange-700', both: 'bg-indigo-100 text-indigo-700' };
    const avatarGrad = { property_owner: 'from-emerald-400 to-emerald-500', contractor: 'from-orange-400 to-orange-500', both: 'from-indigo-400 to-indigo-500' };

    const esc = s => s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : '';

    let rows = data.data.map(a => {
      const tb = typeBadge[a.user_type] || 'bg-gray-100 text-gray-700';
      const ag = avatarGrad[a.user_type] || 'from-gray-400 to-gray-500';
      const avatar = a.profile_pic
        ? '<img src="'+esc(a.profile_pic)+'" class="w-10 h-10 rounded-full object-cover shadow-md">'
        : '<div class="w-10 h-10 rounded-full bg-gradient-to-br '+ag+' flex items-center justify-center text-white font-bold shadow-md text-sm">'+esc(a.initials)+'</div>';
      const status = a.is_active
        ? '<span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold flex items-center gap-1 w-fit"><span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>Active</span>'
        : '<span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-semibold">Inactive</span>';

      return '<tr class="hover:bg-blue-50 transition-colors cursor-pointer">' +
        '<td class="px-6 py-4"><div class="flex items-center gap-3">'+avatar+'<div><p class="font-semibold text-gray-800">'+esc(a.full_name)+'</p><p class="text-sm text-gray-500">'+esc(a.email)+'</p></div></div></td>' +
        '<td class="px-6 py-4"><span class="px-3 py-1 '+tb+' rounded-full text-sm font-semibold">'+esc(a.type_label)+'</span></td>' +
        '<td class="px-6 py-4 text-gray-700">'+esc(a.action)+'</td>' +
        '<td class="px-6 py-4 text-gray-600 text-sm">'+esc(a.time_ago)+'</td>' +
        '<td class="px-6 py-4">'+status+'</td></tr>';
    }).join('');

    panel.innerHTML = '<table class="w-full"><thead class="bg-gray-50 border-b border-gray-200"><tr>' +
      '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>' +
      '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>' +
      '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>' +
      '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Time</th>' +
      '<th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>' +
      '</tr></thead><tbody class="divide-y divide-gray-200">' + rows + '</tbody></table>';
  }

  function renderActivityPagination(data) {
    const wrap = document.getElementById('activityPagination');
    if (!wrap || data.last_page <= 1) { if (wrap) wrap.innerHTML = ''; return; }

    const cur = data.current_page, last = data.last_page;
    let html = '<div class="text-sm text-gray-500">Showing <b>'+data.from+'</b>–<b>'+data.to+'</b> of <b>'+data.total+'</b></div><div class="flex items-center gap-1">';

    if (cur > 1) html += '<button onclick="fetchActivity('+(cur-1)+')" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 hover:bg-indigo-50">&larr; Prev</button>';
    for (let p = Math.max(1, cur-2); p <= Math.min(last, cur+2); p++) {
      html += p === cur
        ? '<span class="px-3 py-1.5 rounded-lg text-sm font-semibold bg-indigo-600 text-white">'+p+'</span>'
        : '<button onclick="fetchActivity('+p+')" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 hover:bg-indigo-50">'+p+'</button>';
    }
    if (cur < last) html += '<button onclick="fetchActivity('+(cur+1)+')" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 hover:bg-indigo-50">Next &rarr;</button>';
    html += '</div>';
    wrap.innerHTML = html;
  }

  // Wire activity filter button
  document.getElementById('activityFilterBtn')?.addEventListener('click', () => fetchActivity(1));

  // Debounce search
  let activitySearchTimer;
  document.getElementById('activitySearch')?.addEventListener('input', function () {
    clearTimeout(activitySearchTimer);
    activitySearchTimer = setTimeout(() => fetchActivity(1), 450);
  });
</script>

<script src="{{ asset('js/admin/home/userActivity_Analytics.js') }}" defer></script>
</body>
</html>
