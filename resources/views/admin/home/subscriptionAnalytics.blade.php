<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Analytics — Legatura Admin</title>

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

    <div class="sa-shell p-4 lg:p-5 space-y-3.5">

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
            <span class="text-gray-300 font-bold text-base">→</span>
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

      {{-- KPI CARDS --}}
      <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">

        {{-- Total Subscriptions --}}
        <div class="sa-kpi bg-white border border-gray-200 border-l-4 border-l-indigo-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-indigo-50 p-2 rounded-lg">
              <i class="fi fi-sr-users-alt text-indigo-500" style="font-size:1rem; line-height:1;"></i>
            </div>
            @if($subscriptionMetrics['mom_growth'] >= 0)
              <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-0.5">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                +{{ $subscriptionMetrics['mom_growth'] }}%
              </span>
            @else
              <span class="text-[11px] font-bold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">{{ $subscriptionMetrics['mom_growth'] }}%</span>
            @endif
          </div>
          <div class="text-2xl font-bold text-gray-900 stat-counter" data-target="{{ $subscriptionMetrics['total'] }}">0</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Total Subscriptions</div>
          <div class="text-[11px] text-gray-400 mt-0.5">vs last month</div>
        </div>

        {{-- Active --}}
        <div class="sa-kpi bg-white border border-gray-200 border-l-4 border-l-emerald-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-emerald-50 p-2 rounded-lg">
              <i class="fi fi-sr-badge-check text-emerald-500" style="font-size:1rem; line-height:1;"></i>
            </div>
            <span class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
              {{ $subscriptionMetrics['total'] > 0 ? round(($subscriptionMetrics['active'] / $subscriptionMetrics['total']) * 100, 1) : 0 }}% of total
            </span>
          </div>
          <div class="text-2xl font-bold text-gray-900 stat-counter" data-target="{{ $subscriptionMetrics['active'] }}">0</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Active</div>
          <div class="text-[11px] text-gray-400 mt-0.5">currently subscribed</div>
        </div>

        {{-- Revenue --}}
        <div id="revenueCard" class="sa-kpi bg-white border border-gray-200 border-l-4 border-l-amber-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-amber-50 p-2 rounded-lg">
              <i class="fi fi-sr-peso-sign text-amber-500" style="font-size:1rem; line-height:1;"></i>
            </div>
          </div>
          <div id="revenueValue" class="text-2xl font-bold text-gray-900">₱{{ number_format($subscriptionMetrics['revenue'], 2) }}</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Total Revenue</div>
          <div class="text-[11px] text-gray-400 mt-0.5">all approved contractor subs</div>
        </div>

        {{-- Expiring --}}
        <div id="expiringCard" class="sa-kpi bg-white border border-gray-200 border-l-4 border-l-rose-400 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="bg-rose-50 p-2 rounded-lg">
              <i class="fi fi-sr-alarm-clock text-rose-500" style="font-size:1rem; line-height:1;"></i>
            </div>
          </div>
          <div class="text-2xl font-bold text-gray-900 stat-counter" data-target="{{ $subscriptionMetrics['expiring'] }}">0</div>
          <div class="text-xs font-medium text-gray-500 mt-0.5">Expiring in 7 Days</div>
          <div id="expiredCount" class="text-[11px] text-gray-400 mt-0.5">{{ $subscriptionMetrics['expired'] }} already expired</div>
        </div>

      </div>

      {{-- REVENUE CHART + TIER BARS --}}
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-3">

        {{-- Revenue Chart --}}
        <div class="xl:col-span-2 bg-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow" style="position:relative;">
          <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Subscription Revenue</h3>
              <p class="text-[11px] text-gray-400 mt-0.5" id="revenueSubtitle">
                {{ $subscriptionRevenue['dateRange'] }} &nbsp;·&nbsp; Current vs Previous Year
              </p>
            </div>
            <div class="flex gap-1.5 flex-wrap">
              <button data-tier="all"    class="tier-btn active px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-800 text-white">All</button>
              <button data-tier="gold"   class="tier-btn px-2.5 py-1 rounded-lg text-xs font-semibold tier-gold">Gold</button>
              <button data-tier="silver" class="tier-btn px-2.5 py-1 rounded-lg text-xs font-semibold tier-silver">Silver</button>
              <button data-tier="bronze" class="tier-btn px-2.5 py-1 rounded-lg text-xs font-semibold tier-bronze">Bronze</button>
            </div>
          </div>

          <div id="revenueSpinner" class="hidden absolute inset-0 bg-white/70 rounded-xl flex items-center justify-center z-10">
            <div class="w-6 h-6 border-4 border-indigo-200 border-t-indigo-500 rounded-full animate-spin"></div>
          </div>

          <div style="height:260px; position:relative;">
            <canvas id="revenueChart"
              data-months='@json($subscriptionRevenue["months"])'
              data-current='@json($subscriptionRevenue["currentYearData"])'
              data-previous='@json($subscriptionRevenue["previousYearData"])'
              data-current-year="{{ $subscriptionRevenue['currentYear'] }}"
              data-previous-year="{{ $subscriptionRevenue['previousYear'] }}">
            </canvas>
          </div>
        </div>

        {{-- Tier Breakdown --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-800 mb-0.5">Tier Breakdown</h3>
          <p class="text-[11px] text-gray-400 mb-4">Approved subscriptions by plan</p>
          <div class="space-y-3">
            @php
              $tierMeta = [
                ['bar' => '#fbbf24', 'text' => 'text-amber-700', 'bg' => 'bg-amber-50'],
                ['bar' => '#60a5fa', 'text' => 'text-blue-700',  'bg' => 'bg-blue-50'],
                ['bar' => '#f97316', 'text' => 'text-orange-700','bg' => 'bg-orange-50'],
              ];
            @endphp
            @foreach($subscriptionTiers['tiers'] as $i => $tier)
              @php
                $m = $tierMeta[$i] ?? $tierMeta[0];
                $w = $subscriptionTiers['maxCount'] > 0
                      ? round(($tier['count'] / $subscriptionTiers['maxCount']) * 100) : 0;
                $pct = $subscriptionTiers['total'] > 0
                        ? round(($tier['count'] / $subscriptionTiers['total']) * 100, 1) : 0;
              @endphp
              <div>
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs font-semibold {{ $m['text'] }}">{{ $tier['label'] }}</span>
                  <span class="text-xs font-bold text-gray-700">{{ $tier['count'] }} <span class="text-gray-400 font-normal">({{ $pct }}%)</span></span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                  <div class="h-1.5 rounded-full tier-bar"
                       data-width="{{ $w }}" style="width:0%; background:{{ $m['bar'] }}; transition:width 0.9s cubic-bezier(.34,1.56,.64,1)"></div>
                </div>
              </div>
            @endforeach
            <div class="pt-2.5 border-t border-gray-100 flex items-center justify-between">
              <span class="text-xs text-gray-500">Total approved</span>
              <span class="text-lg font-bold text-gray-800">{{ $subscriptionTiers['total'] }}</span>
            </div>
          </div>
        </div>

      </div>

      {{-- SUBSCRIBER LIST --}}
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Subscribers</h3>
              <p class="text-[11px] text-gray-400" id="subscriberMeta">
                {{ $subscribers->total() }} total &nbsp;·&nbsp;
                Showing {{ $subscribers->firstItem() ?? 0 }}–{{ $subscribers->lastItem() ?? 0 }}
              </p>
            </div>
            <button id="exportCsvBtn" class="flex items-center gap-1.5 bg-white border border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-50 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              Export CSV
            </button>
          </div>
        </div>

        {{-- Filter Bar --}}
        <div class="px-5 py-3 border-b border-gray-100 bg-white">
          <div class="flex flex-wrap gap-2 items-end">

            <div class="flex-1 min-w-[180px]">
              <label class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1 block">Search</label>
              <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="searchInput" type="text"
                  value="{{ $filters['search'] }}"
                  placeholder="Name, email, plan, transaction…"
                  class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-200 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition-all">
              </div>
            </div>

            <div class="min-w-[120px]">
              <label class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1 block">Plan</label>
              <select id="planFilter" class="w-full px-2.5 py-2 rounded-lg border border-gray-200 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all">
                <option value=""       {{ $filters['plan'] === ''       ? 'selected' : '' }}>All Plans</option>
                <option value="gold"   {{ $filters['plan'] === 'gold'   ? 'selected' : '' }}>Gold</option>
                <option value="silver" {{ $filters['plan'] === 'silver' ? 'selected' : '' }}>Silver</option>
                <option value="bronze" {{ $filters['plan'] === 'bronze' ? 'selected' : '' }}>Bronze</option>
                <option value="boost"  {{ $filters['plan'] === 'boost'  ? 'selected' : '' }}>Boost</option>
              </select>
            </div>

            <div class="min-w-[120px]">
              <label class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1 block">Status</label>
              <select id="statusFilter" class="w-full px-2.5 py-2 rounded-lg border border-gray-200 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all">
                <option value=""         {{ $filters['status'] === ''         ? 'selected' : '' }}>All Statuses</option>
                <option value="active"   {{ $filters['status'] === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="expired"  {{ $filters['status'] === 'expired'  ? 'selected' : '' }}>Expired</option>
                <option value="pending"  {{ $filters['status'] === 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="cancelled"{{ $filters['status'] === 'cancelled'? 'selected' : '' }}>Cancelled</option>
              </select>
            </div>

            <div class="min-w-[140px]">
              <label class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1 block">Sort By</label>
              <select id="sortFilter" class="w-full px-2.5 py-2 rounded-lg border border-gray-200 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-indigo-200 transition-all">
                <option value="newest"      {{ $filters['sort'] === 'newest'      ? 'selected' : '' }}>Newest First</option>
                <option value="oldest"      {{ $filters['sort'] === 'oldest'      ? 'selected' : '' }}>Oldest First</option>
                <option value="amount_desc" {{ $filters['sort'] === 'amount_desc' ? 'selected' : '' }}>Amount ↓</option>
                <option value="amount_asc"  {{ $filters['sort'] === 'amount_asc'  ? 'selected' : '' }}>Amount ↑</option>
                <option value="name_asc"    {{ $filters['sort'] === 'name_asc'    ? 'selected' : '' }}>Name A–Z</option>
              </select>
            </div>

            <div>
              <label class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1 block opacity-0">x</label>
              <button id="clearFiltersBtn"
                class="{{ ($filters['search'] || $filters['plan'] || $filters['status'] || $filters['sort'] !== 'newest') ? '' : 'invisible' }} px-3 py-2 bg-gray-100 text-gray-500 rounded-lg text-xs font-medium hover:bg-gray-200 transition-colors">
                Clear
              </button>
            </div>

          </div>
        </div>

        {{-- Subscriber Table --}}
        <div id="subscriberPanel">
          @include('admin.home.subscriberTable', ['subscribers' => $subscribers, 'filters' => $filters])
        </div>

      </div>

    </div>
  </main>
</div>

<script>
  window.SubConfig = {
    ajaxUrl:    '{{ route("admin.analytics.subscription.subscribers") }}',
    revenueUrl: '{{ route("admin.analytics.subscription.revenue") }}',
    filters:    @json($filters),
    csrfToken:  '{{ csrf_token() }}'
  };
</script>
<script src="{{ asset('js/admin/home/subscriptionAnalytics.js') }}" defer></script>
</body>
</html>
