<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard - Legatura</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo2.0-favicon.svg') }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/dashboard.css') }}">

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
      @include('admin.layouts.topnav', ['pageTitle' => 'Dashboard'])

      <div class="p-6 lg:p-7 space-y-6">

        {{-- ── Global Date Filter (consistent with Verification Request) ── --}}
        <div class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
              <i class="fi fi-rr-filter text-gray-500"></i>
              <span>Filter By</span>
            </div>

            {{-- Preset range pills ── --}}
            <div class="flex flex-wrap items-center gap-1.5" id="globalFilterOptions">
              <button type="button" class="global-filter-btn active px-3 py-1.5 rounded-lg text-xs font-semibold border-2 border-indigo-500 bg-indigo-50 text-indigo-700 transition hover:bg-indigo-100" data-range="thisyear">This Year</button>
              <button type="button" class="global-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 hover:border-indigo-300 hover:bg-indigo-50/50 hover:text-indigo-600 transition" data-range="lastyear">Last Year</button>
              <button type="button" class="global-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 hover:border-indigo-300 hover:bg-indigo-50/50 hover:text-indigo-600 transition" data-range="last6months">Last 6 Months</button>
              <button type="button" class="global-filter-btn px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 hover:border-indigo-300 hover:bg-indigo-50/50 hover:text-indigo-600 transition" data-range="last3months">Last 3 Months</button>
            </div>

            {{-- Custom date range (date-pill style like Verification Request) ── --}}
            <div class="flex flex-wrap items-center gap-2" id="dashboardCustomRangeWrap">
              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-[11px]"></i>
                </div>
                <input type="date" id="customRangeStart" class="dashboard-date-input bg-white text-xs px-2.5 py-1.5 focus:outline-none cursor-pointer min-w-0 border-0">
              </div>
              <span class="text-gray-300 font-bold text-lg">→</span>
              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-2.5 py-2 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-[11px]"></i>
                </div>
                <input type="date" id="customRangeEnd" class="dashboard-date-input bg-white text-xs px-2.5 py-1.5 focus:outline-none cursor-pointer min-w-0 border-0">
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <div class="global-filter-loading items-center gap-1" id="globalFilterLoading">
              <span class="filter-loading-dot w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
              <span class="filter-loading-dot w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
              <span class="filter-loading-dot w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
            </div>
            <button type="button" id="dashboardResetFilterBtn" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
              <i class="fi fi-rr-rotate-left"></i>
              <span>Reset Filter</span>
            </button>
          </div>
        </div>

        {{-- ── Mini Stats (Projects, Bids, Revenue) — same design as proofOfpayments stat cards ── --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="stat-card mini-stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer" data-months='{{ json_encode($projectsMetrics["months"]) }}' data-data='{{ json_encode($projectsMetrics["data"]) }}' data-label="{{ $projectsMetrics['label'] }}" data-total='{{ $projectsMetrics['total'] }}' data-pct='{{ $projectsMetrics['pctChange'] }}'>
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Total Projects</p>
                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number mini-number tabular-nums">{{ $projectsMetrics['total'] }}</h2>
                <div class="flex items-center gap-2 mt-2 flex-wrap">
                  <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-indigo-100 w-fit">
                    <i class="fi fi-ss-folder text-[10px] text-indigo-600"></i>
                    <span class="text-[11px] font-semibold text-indigo-600 mini-label">Total Projects</span>
                  </div>
                </div>
              </div>
              <div class="stat-icon-wrap bg-indigo-100 p-2.5 rounded-lg"><i class="fi fi-ss-folder text-lg text-indigo-600"></i></div>
            </div>
            <div class="mini-stat-left w-full h-9 flex items-center">
              <canvas class="mini-chart w-full h-9 block" width="120" height="36"></canvas>
            </div>
            <p class="text-[11px] text-gray-400 mt-1.5">Trend in selected period</p>
          </div>

          <div class="stat-card mini-stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer" data-months='{{ json_encode($activeBidsMetrics["months"]) }}' data-data='{{ json_encode($activeBidsMetrics["data"]) }}' data-label="{{ $activeBidsMetrics['label'] }}" data-total='{{ $activeBidsMetrics['total'] }}' data-pct='{{ $activeBidsMetrics['pctChange'] }}'>
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Active Bids</p>
                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number mini-number tabular-nums">{{ $activeBidsMetrics['total'] }}</h2>
                <div class="flex items-center gap-2 mt-2 flex-wrap">
                  <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-blue-100 w-fit">
                    <i class="fi fi-ss-handshake text-[10px] text-blue-600"></i>
                    <span class="text-[11px] font-semibold text-blue-600 mini-label">Active Bids</span>
                  </div>
                </div>
              </div>
              <div class="stat-icon-wrap bg-blue-100 p-2.5 rounded-lg"><i class="fi fi-ss-handshake text-lg text-blue-600"></i></div>
            </div>
            <div class="mini-stat-left w-full h-9 flex items-center">
              <canvas class="mini-chart w-full h-9 block" width="120" height="36"></canvas>
            </div>
            <p class="text-[11px] text-gray-400 mt-1.5">Trend in selected period</p>
          </div>

          <div class="stat-card mini-stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer" data-months='{{ json_encode($revenueMetrics["months"]) }}' data-data='{{ json_encode($revenueMetrics["data"]) }}' data-label="{{ $revenueMetrics['label'] }}" data-total='{{ number_format($revenueMetrics['total'],2) }}' data-pct='{{ $revenueMetrics['pctChange'] }}'>
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Revenue</p>
                <h2 class="text-2xl font-bold leading-none text-orange-500 stat-number mini-number tabular-nums">₱{{ number_format($revenueMetrics['total'],2) }}</h2>
                <div class="flex items-center gap-2 mt-2 flex-wrap">
                  <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-amber-100 w-fit">
                    <i class="fi fi-ss-badge-dollar text-[10px] text-amber-600"></i>
                    <span class="text-[11px] font-semibold text-amber-600 mini-label">Revenue</span>
                  </div>
                </div>
              </div>
              <div class="stat-icon-wrap bg-amber-100 p-2.5 rounded-lg"><i class="fi fi-ss-badge-dollar text-lg text-amber-600"></i></div>
            </div>
            <div class="mini-stat-left w-full h-9 flex items-center">
              <canvas class="mini-chart w-full h-9 block" width="120" height="36"></canvas>
            </div>
            <p class="text-[11px] text-gray-400 mt-1.5">Trend in selected period</p>
          </div>
        </div>

        {{-- ── Active Users + Stats ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="px-4 py-3.5 border-b border-gray-200 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-amber-100 border border-amber-200 flex items-center justify-center">
              <i class="fi fi-ss-users-alt text-amber-600 text-base"></i>
            </div>
            <div>
              <h2 class="text-base font-semibold text-gray-800 leading-tight">Active Users</h2>
              <p class="text-[11px] text-gray-500 mt-0.5">Current period activity</p>
            </div>
          </div>
          <div class="p-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="flex flex-col gap-4">
              <div class="rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 p-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-700/80 mb-1">Total active</p>
                <div class="text-3xl font-bold leading-none text-orange-500 tabular-nums total-number">{{ $activeUsersData['total'] }}</div>
              </div>
              <div class="space-y-2 stats-list">
                <div class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50/50 px-3 py-2.5 stat-item">
                  <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                    <i class="fi fi-ss-users-alt text-sm"></i>
                  </div>
                  <div class="min-w-0">
                    <span class="block text-sm font-bold text-gray-800 tabular-nums stat-value">{{ $activeUsersData['contractors'] }}</span>
                    <span class="text-[11px] text-gray-500">Contractors</span>
                  </div>
                </div>
                <div class="flex items-center gap-3 rounded-lg border border-gray-100 bg-gray-50/50 px-3 py-2.5 stat-item">
                  <div class="w-9 h-9 rounded-lg bg-violet-100 flex items-center justify-center text-violet-600 shrink-0">
                    <i class="fi fi-ss-user text-sm"></i>
                  </div>
                  <div class="min-w-0">
                    <span class="block text-sm font-bold text-gray-800 tabular-nums stat-value">{{ $activeUsersData['property_owners'] }}</span>
                    <span class="text-[11px] text-gray-500">Property Owners</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="lg:col-span-2 relative rounded-xl border border-gray-200 bg-gray-50/80 flex items-center justify-center min-h-[200px] shadow-inner">
              <div class="w-full h-[200px] px-2" id="activeUsersChartWrap">
                <canvas id="activeUsersChart" data-months='{{ json_encode($activeUsersData["months"]) }}' data-data='{{ json_encode($activeUsersData["data"]) }}'></canvas>
              </div>
            </div>
          </div>

          {{-- Dashboard Stats (Total Users, New Users, etc.) ── --}}
          <div class="px-4 pb-4 pt-3 border-t border-gray-100 bg-gray-50/30">
            <div class="flex items-center gap-2 mb-3">
              <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">User & review metrics</span>
              <span class="h-px flex-1 max-w-[80px] bg-gray-200 rounded-full"></span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
              <div class="stat-card group bg-white rounded-xl border border-gray-200 border-t-4 border-t-blue-500 p-3.5 hover:shadow-md hover:border-blue-200 hover:-translate-y-0.5 transition-all duration-200 cursor-pointer" data-chart-type="total-users" data-months='{{ json_encode($totalUsersChartData["months"]) }}' data-data='{{ json_encode($totalUsersChartData["data"]) }}' data-label="{{ $totalUsersChartData["label"] }}" data-breakdown='{{ json_encode($totalUsersBreakdown) }}'>
                <div class="stat-card-header w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center mb-2 group-hover:bg-blue-200/80 transition-colors">
                  <i class="fi fi-ss-users-alt stat-card-icon text-blue-600 text-base"></i>
                </div>
                <div class="stat-card-body">
                  <span class="stat-card-number block text-2xl font-bold leading-none text-gray-800 tabular-nums">{{ $dashboardStats['totalUsers'] }}</span>
                  <span class="stat-card-label text-[11px] text-gray-500 font-medium">Total Users</span>
                </div>
              </div>
              <div class="stat-card group bg-white rounded-xl border border-gray-200 border-t-4 border-t-emerald-500 p-3.5 hover:shadow-md hover:border-emerald-200 hover:-translate-y-0.5 transition-all duration-200 cursor-pointer" data-chart-type="new-users" data-months='{{ json_encode($newUsersChartData["months"]) }}' data-data='{{ json_encode($newUsersChartData["data"]) }}' data-label="{{ $newUsersChartData["label"] }}" data-breakdown='{{ json_encode($newUsersBreakdown) }}'>
                <div class="stat-card-header w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center mb-2 group-hover:bg-emerald-200/80 transition-colors">
                  <i class="fi fi-ss-user-add stat-card-icon text-emerald-600 text-base"></i>
                </div>
                <div class="stat-card-body">
                  <span class="stat-card-number block text-2xl font-bold leading-none text-gray-800 tabular-nums">{{ $dashboardStats['newUsers'] }}</span>
                  <span class="stat-card-label text-[11px] text-gray-500 font-medium">New Users</span>
                </div>
              </div>
              <div class="stat-card group bg-white rounded-xl border border-gray-200 border-t-4 border-t-amber-500 p-3.5 hover:shadow-md hover:border-amber-200 hover:-translate-y-0.5 transition-all duration-200 cursor-pointer" data-chart-type="active-users" data-months='{{ json_encode($activeUsersChartData["months"]) }}' data-data='{{ json_encode($activeUsersChartData["data"]) }}' data-label="{{ $activeUsersChartData["label"] }}" data-breakdown='{{ json_encode($activeUsersBreakdown) }}'>
                <div class="stat-card-header w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center mb-2 group-hover:bg-amber-200/80 transition-colors">
                  <i class="fi fi-ss-users stat-card-icon text-amber-600 text-base"></i>
                </div>
                <div class="stat-card-body">
                  <span class="stat-card-number block text-2xl font-bold leading-none text-gray-800 tabular-nums">{{ $dashboardStats['activeUsers'] }}</span>
                  <span class="stat-card-label text-[11px] text-gray-500 font-medium">Active Users</span>
                </div>
              </div>
              <div class="stat-card group bg-white rounded-xl border border-gray-200 border-t-4 border-t-indigo-500 p-3.5 hover:shadow-md hover:border-indigo-200 hover:-translate-y-0.5 transition-all duration-200 cursor-pointer" data-chart-type="pending-reviews" data-months='{{ json_encode($pendingReviewsChartData["months"]) }}' data-data='{{ json_encode($pendingReviewsChartData["data"]) }}' data-label="{{ $pendingReviewsChartData["label"] }}" data-breakdown='{{ json_encode($pendingReviewsBreakdown) }}'>
                <div class="stat-card-header w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center mb-2 group-hover:bg-indigo-200/80 transition-colors">
                  <i class="fi fi-ss-check-circle stat-card-icon text-indigo-600 text-base"></i>
                </div>
                <div class="stat-card-body">
                  <span class="stat-card-number block text-2xl font-bold leading-none text-gray-800 tabular-nums">{{ $dashboardStats['pendingReviews'] }}</span>
                  <span class="stat-card-label text-[11px] text-gray-500 font-medium">Pending Reviews</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- ── Top Contractors & Top Property Owners ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 leaderboard-grid">
          <div class="leaderboard-card leaderboard-card-contractors bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden min-h-[380px] flex flex-col">
            <div class="leaderboard-head px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
              <div class="flex items-center gap-3 min-w-0">
                <div class="leaderboard-icon leaderboard-icon-contractors w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                  <i class="fi fi-ss-handshake text-[15px]"></i>
                </div>
                <div class="min-w-0">
                  <h2 class="text-sm font-semibold text-gray-800 leading-tight">Top Contractors</h2>
                  <p class="text-[10px] text-gray-500 mt-0.5 truncate">Projects in selected period</p>
                </div>
              </div>
              <span class="leaderboard-chip">{{ count($topContractors) }} Listed</span>
            </div>
            <div class="items-container leaderboard-items p-3 space-y-2 min-h-[300px] flex-1" data-list="top-contractors">
              @forelse($topContractors as $contractor)
              <div class="item-card contractor-item flex items-center justify-between p-2.5 rounded-xl border border-gray-100 transition">
                <div class="item-left flex items-center gap-2.5 min-w-0">
                  <span class="item-rank">#{{ $loop->iteration }}</span>
                  <div class="item-avatar avatar-contractor w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-white text-xs font-bold overflow-hidden">
                    @if($contractor->profile_pic)
                      <img src="{{ asset('storage/' . $contractor->profile_pic) }}" alt="{{ $contractor->company_name }}" class="w-full h-full object-cover">
                    @else
                      {{ strtoupper(substr($contractor->company_name, 0, 1)) }}
                    @endif
                  </div>
                  <div class="item-info min-w-0">
                    <h3 class="item-name text-[13px] font-semibold text-gray-800 truncate">{{ $contractor->company_name }}</h3>
                    <p class="item-type text-[11px] text-gray-500">{{ $contractor->type_name }}</p>
                  </div>
                </div>
                <div class="item-right text-right flex-shrink-0">
                  <p class="item-count text-sm font-bold text-gray-800">{{ $contractor->completed_projects }}</p>
                  <p class="item-label text-[10px] text-gray-500">Projects in Period</p>
                </div>
              </div>
              @empty
              <p class="empty-state text-[12px] text-gray-500 py-5 text-center">No contractors found</p>
              @endforelse
            </div>
          </div>

          <div class="leaderboard-card leaderboard-card-owners bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden min-h-[380px] flex flex-col">
            <div class="leaderboard-head px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
              <div class="flex items-center gap-3 min-w-0">
                <div class="leaderboard-icon leaderboard-icon-owners w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                  <i class="fi fi-ss-user text-[15px]"></i>
                </div>
                <div class="min-w-0">
                  <h2 class="text-sm font-semibold text-gray-800 leading-tight">Top Property Owners</h2>
                  <p class="text-[10px] text-gray-500 mt-0.5 truncate">Projects in selected period</p>
                </div>
              </div>
              <span class="leaderboard-chip">{{ count($topPropertyOwners) }} Listed</span>
            </div>
            <div class="items-container leaderboard-items p-3 space-y-2 min-h-[300px] flex-1" data-list="top-owners">
              @forelse($topPropertyOwners as $owner)
              <div class="item-card owner-item flex items-center justify-between p-2.5 rounded-xl border border-gray-100 transition">
                <div class="item-left flex items-center gap-2.5 min-w-0">
                  <span class="item-rank">#{{ $loop->iteration }}</span>
                  <div class="item-avatar avatar-owner w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-white text-xs font-bold overflow-hidden">
                    @if($owner->profile_pic)
                      <img src="{{ asset('storage/' . $owner->profile_pic) }}" alt="{{ $owner->first_name }} {{ $owner->last_name }}" class="w-full h-full object-cover">
                    @else
                      {{ strtoupper(substr($owner->first_name, 0, 1)) }}
                    @endif
                  </div>
                  <div class="item-info min-w-0">
                    <h3 class="item-name text-[13px] font-semibold text-gray-800 truncate">{{ $owner->first_name }} {{ $owner->last_name }}</h3>
                    <p class="item-type text-[11px] text-gray-500">Property Owner</p>
                  </div>
                </div>
                <div class="item-right text-right flex-shrink-0">
                  <p class="item-count text-sm font-bold text-gray-800">{{ $owner->completed_projects }}</p>
                  <p class="item-label text-[10px] text-gray-500">Projects in Period</p>
                </div>
              </div>
              @empty
              <p class="empty-state text-[12px] text-gray-500 py-5 text-center">No property owners found</p>
              @endforelse
            </div>
          </div>
        </div>

        {{-- ── Top Projects with Bids ── --}}
        <div class="top-projects-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="top-projects-head px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <div class="top-projects-icon w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fi fi-ss-folder text-[15px]"></i>
              </div>
              <div class="min-w-0">
                <h2 class="text-sm font-semibold text-gray-800 leading-tight">Top Projects with Bids</h2>
                <p class="text-[10px] text-gray-500 mt-0.5 truncate">By bid count in selected period</p>
              </div>
            </div>
            <span class="top-projects-chip" id="topProjectsCountChip">{{ count($topProjects) }} Ranked</span>
          </div>
          <div class="top-projects-table-wrap overflow-x-auto">
            <table class="top-projects-table w-full min-w-[640px] text-left">
              <thead class="top-projects-thead bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-3 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                  <th class="px-3 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Owner</th>
                  <th class="px-3 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Bids</th>
                  <th class="px-3 py-2.5 text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody id="topProjectsTbody" class="divide-y divide-gray-100">
                @forelse($topProjects as $project)
                <tr class="top-project-row transition">
                  <td class="px-3 py-2.5">
                    <div class="project-info flex items-center gap-2.5">
                      <span class="project-rank">#{{ $loop->iteration }}</span>
                      <span class="project-avatar w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($project->project_title, 0, 1)) }}
                      </span>
                      <span class="project-name text-[12px] font-semibold text-gray-800 truncate max-w-[180px]">{{ $project->project_title }}</span>
                    </div>
                  </td>
                  <td class="px-3 py-2.5">
                    <div class="project-owner inline-flex items-center gap-2">
                      <span class="owner-dot"></span>
                      <span class="text-[12px] font-medium text-gray-700">{{ $project->first_name }} {{ $project->last_name }}</span>
                    </div>
                  </td>
                  <td class="px-3 py-2.5">
                    <span class="project-bid-count text-[12px] font-semibold text-indigo-600">{{ $project->bid_count }} Bids</span>
                  </td>
                  <td class="px-3 py-2.5">
                    <span class="project-status {{ strtolower($project->project_status) }} inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $project->project_status === 'active' ? 'bg-emerald-100 text-emerald-700' : ($project->project_status === 'completed' ? 'bg-indigo-100 text-indigo-700' : ($project->project_status === 'cancelled' ? 'bg-red-100 text-red-700' : ($project->project_status === 'pending' ? 'bg-amber-100 text-amber-700' : ($project->project_status === 'ongoing' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600')))) }}">{{ $project->status_label }}</span>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="px-3 py-10 text-center">
                    <div class="top-projects-empty text-[12px] text-gray-500">No projects found</div>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        {{-- ── Earnings ── --}}
        <div class="earnings-section bg-gradient-to-r from-orange-500 to-amber-600 rounded-xl shadow-sm border border-orange-200 p-4 text-white">
          <div class="earnings-header flex flex-wrap items-center justify-between gap-3 mb-3">
            <h2 class="earnings-title text-sm font-semibold text-white m-0">Earnings</h2>
            <div class="earnings-date-picker-wrapper relative">
              <div class="earnings-date-picker inline-flex items-center gap-2 bg-white/20 px-3 py-2 rounded-lg cursor-pointer hover:bg-white/30 transition">
                <span class="earnings-date-range text-[12px] font-semibold text-white" id="earningsDateRangeSpan">{{ $earningsMetrics['dateRange'] }}</span>
                <button class="earnings-dropdown-btn p-0.5 text-white hover:bg-white/20 rounded transition" id="earningsDropdownBtn" type="button">
                  <svg width="10" height="10" viewBox="0 0 12 12" fill="none" class="transition transform"><path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
              </div>
              <div class="earnings-dropdown-menu absolute top-full right-0 mt-1 bg-white rounded-xl shadow-lg border border-gray-200 py-1 min-w-[160px] z-50 opacity-0 invisible transition-all" id="earningsDropdownMenu">
                <button class="earnings-dropdown-item block w-full text-left px-3 py-2 text-[12px] text-gray-700 hover:bg-indigo-50 transition" data-range="today">Today</button>
                <button class="earnings-dropdown-item block w-full text-left px-3 py-2 text-[12px] text-gray-700 hover:bg-indigo-50 transition" data-range="yesterday">Yesterday</button>
                <button class="earnings-dropdown-item block w-full text-left px-3 py-2 text-[12px] text-gray-700 hover:bg-indigo-50 transition" data-range="last7days">Last 7 Days</button>
                <button class="earnings-dropdown-item active block w-full text-left px-3 py-2 text-[12px] font-semibold bg-indigo-100 text-indigo-700" data-range="thismonth">This Month</button>
                <button class="earnings-dropdown-item block w-full text-left px-3 py-2 text-[12px] text-gray-700 hover:bg-indigo-50 transition" data-range="lastmonth">Last Month</button>
                <button class="earnings-dropdown-item block w-full text-left px-3 py-2 text-[12px] text-gray-700 hover:bg-indigo-50 transition" data-range="last3months">Last 3 Months</button>
                <button class="earnings-dropdown-item block w-full text-left px-3 py-2 text-[12px] text-gray-700 hover:bg-indigo-50 transition" data-range="thisyear">This Year</button>
              </div>
            </div>
          </div>
          <div class="earnings-chart-wrapper flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="earnings-total flex flex-col gap-0.5 min-w-0">
              <div class="earnings-total-label text-[11px] font-medium text-white/90">Total Earnings</div>
              <div class="earnings-total-amount text-xl sm:text-2xl font-bold text-white leading-none">₱{{ number_format($earningsMetrics['total'], 2) }}</div>
            </div>
            <div class="earnings-chart-container flex-1 w-full h-[140px] sm:h-[160px] bg-white/10 rounded-xl p-3">
              <canvas id="earningsChart" data-days='{{ json_encode($earningsMetrics["days"]) }}' data-data='{{ json_encode($earningsMetrics["data"]) }}'></canvas>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script src="{{ asset('js/admin/home/dashboard.js') }}" defer></script>
  <script>
    window.storageBaseUrl = '{{ asset("storage") }}';
  </script>
</body>

</html>
