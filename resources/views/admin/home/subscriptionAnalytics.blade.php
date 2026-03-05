<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Analytics — Legatura Admin</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

  <style>
    /* Tier badges */
    .tier-gold   { background:linear-gradient(135deg,#fef08a,#fbbf24); color:#78350f; }
    .tier-silver { background:linear-gradient(135deg,#e0f2fe,#60a5fa); color:#1e3a5f; }
    .tier-bronze { background:linear-gradient(135deg,#fed7aa,#f97316); color:#7c2d12; }
    .tier-boost  { background:linear-gradient(135deg,#e9d5ff,#a855f7); color:#4c1d95; }
    .tier-other  { background:linear-gradient(135deg,#f1f5f9,#94a3b8); color:#334155; }

    /* Status badges */
    .status-active    { background:#dcfce7; color:#166534; }
    .status-expired   { background:#fee2e2; color:#991b1b; }
    .status-pending   { background:#fef9c3; color:#854d0e; }
    .status-cancelled { background:#f1f5f9; color:#475569; }

    /* Table row hover */
    .sub-row { transition: background 0.15s ease; }
    .sub-row:hover { background: rgba(99,102,241,0.04); }

    /* Skeleton pulse */
    @keyframes shimmer {
      0%   { background-position:-800px 0; }
      100% { background-position: 800px 0; }
    }
    .skeleton {
      background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
      background-size: 800px 100%;
      animation: shimmer 1.4s infinite linear;
      border-radius: 6px;
    }

    /* Revenue chart tier buttons */
    .tier-btn { transition: all 0.2s ease; }
    .tier-btn.active { box-shadow: 0 0 0 2px white, 0 0 0 4px currentColor; }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
<div class="flex min-h-screen">

  @include('admin.layouts.sidebar')

  <main class="flex-1">
    @include('admin.layouts.topnav', ['pageTitle' => 'Subscription Analytics'])

    <div class="p-8 space-y-8">

      {{-- ── KPI HERO CARDS ───────────────────────────────────────────────── --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

        {{-- Total Subscriptions --}}
        <div class="relative bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute -top-4 -right-4 w-24 h-24 bg-white opacity-10 rounded-full pointer-events-none"></div>
          <div class="relative">
            <div class="bg-white/20 w-11 h-11 rounded-xl flex items-center justify-center mb-4">
              <i class="fi fi-sr-users-alt text-white text-xl leading-none"></i>
            </div>
            <div class="text-white/80 text-sm font-medium mb-1">Total Subscriptions</div>
            <div class="text-white text-4xl font-bold mb-2 stat-counter" data-target="{{ $subscriptionMetrics['total'] }}">0</div>
            <div class="flex items-center gap-1 text-white/80 text-xs">
              @if($subscriptionMetrics['mom_growth'] >= 0)
                <svg class="w-3 h-3 text-emerald-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                <span class="text-emerald-300 font-semibold">+{{ $subscriptionMetrics['mom_growth'] }}%</span>
              @else
                <svg class="w-3 h-3 text-red-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                <span class="text-red-300 font-semibold">{{ $subscriptionMetrics['mom_growth'] }}%</span>
              @endif
              <span>vs last month</span>
            </div>
          </div>
        </div>

        {{-- Active --}}
        <div class="relative bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute -top-4 -right-4 w-24 h-24 bg-white opacity-10 rounded-full pointer-events-none"></div>
          <div class="relative">
            <div class="bg-white/20 w-11 h-11 rounded-xl flex items-center justify-center mb-4">
              <i class="fi fi-sr-badge-check text-white text-xl leading-none"></i>
            </div>
            <div class="text-white/80 text-sm font-medium mb-1">Active</div>
            <div class="text-white text-4xl font-bold mb-2 stat-counter" data-target="{{ $subscriptionMetrics['active'] }}">0</div>
            <div class="text-white/70 text-xs">
              {{ $subscriptionMetrics['total'] > 0 ? round(($subscriptionMetrics['active'] / $subscriptionMetrics['total']) * 100, 1) : 0 }}% of total
            </div>
          </div>
        </div>

        {{-- Revenue --}}
        <div class="relative bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute -top-4 -right-4 w-24 h-24 bg-white opacity-10 rounded-full pointer-events-none"></div>
          <div class="relative">
            <div class="bg-white/20 w-11 h-11 rounded-xl flex items-center justify-center mb-4">
              <i class="fi fi-sr-peso-sign text-white text-xl leading-none"></i>
            </div>
            <div class="text-white/80 text-sm font-medium mb-1">Total Revenue</div>
            <div class="text-white text-4xl font-bold mb-2">
              ₱{{ number_format($subscriptionMetrics['revenue'], 2) }}
            </div>
            <div class="text-white/70 text-xs">All approved contractor subs</div>
          </div>
        </div>

        {{-- Expiring / Expired --}}
        <div class="relative bg-gradient-to-br from-rose-500 to-pink-700 rounded-2xl p-6 shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
          <div class="absolute -top-4 -right-4 w-24 h-24 bg-white opacity-10 rounded-full pointer-events-none"></div>
          <div class="relative">
            <div class="bg-white/20 w-11 h-11 rounded-xl flex items-center justify-center mb-4">
              <i class="fi fi-sr-alarm-clock text-white text-xl leading-none"></i>
            </div>
            <div class="text-white/80 text-sm font-medium mb-1">Expiring in 7 Days</div>
            <div class="text-white text-4xl font-bold mb-2 stat-counter" data-target="{{ $subscriptionMetrics['expiring'] }}">0</div>
            <div class="text-white/70 text-xs">
              {{ $subscriptionMetrics['expired'] }} already expired
            </div>
          </div>
        </div>

      </div>

      {{-- ── REVENUE CHART + TIER BARS ────────────────────────────────────── --}}
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Revenue Line Chart --}}
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-shadow">
          <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
              <h3 class="text-xl font-bold text-gray-800">Subscription Revenue</h3>
              <p class="text-sm text-gray-500 mt-0.5" id="revenueSubtitle">
                {{ $subscriptionRevenue['dateRange'] }} &nbsp;·&nbsp; Current vs Previous Year
              </p>
            </div>
            {{-- Tier toggle --}}
            <div class="flex gap-2 flex-wrap">
              <button data-tier="all"    class="tier-btn active px-3 py-1.5 rounded-lg text-sm font-semibold bg-gray-800 text-white">All</button>
              <button data-tier="gold"   class="tier-btn px-3 py-1.5 rounded-lg text-sm font-semibold tier-gold">Gold</button>
              <button data-tier="silver" class="tier-btn px-3 py-1.5 rounded-lg text-sm font-semibold tier-silver">Silver</button>
              <button data-tier="bronze" class="tier-btn px-3 py-1.5 rounded-lg text-sm font-semibold tier-bronze">Bronze</button>
            </div>
          </div>

          {{-- Loading overlay --}}
          <div id="revenueSpinner" class="hidden absolute inset-0 bg-white/70 rounded-2xl flex items-center justify-center z-10">
            <div class="w-8 h-8 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
          </div>

          <div style="height:300px; position:relative;">
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
        <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-shadow">
          <h3 class="text-xl font-bold text-gray-800 mb-1">Tier Breakdown</h3>
          <p class="text-sm text-gray-500 mb-6">Approved subscriptions by plan</p>
          <div class="space-y-5">
            @php
              $tierStyles = [
                ['bg' => 'bg-yellow-50', 'bar' => 'bg-gradient-to-r from-yellow-300 to-yellow-500', 'text' => 'text-yellow-700'],
                ['bg' => 'bg-blue-50',   'bar' => 'bg-gradient-to-r from-blue-300   to-blue-500',   'text' => 'text-blue-700'],
                ['bg' => 'bg-orange-50', 'bar' => 'bg-gradient-to-r from-orange-300 to-orange-500', 'text' => 'text-orange-700'],
              ];
            @endphp
            @foreach($subscriptionTiers['tiers'] as $i => $tier)
              @php
                $s = $tierStyles[$i];
                $w = $subscriptionTiers['maxCount'] > 0
                      ? round(($tier['count'] / $subscriptionTiers['maxCount']) * 100)
                      : 0;
                $pct = $subscriptionTiers['total'] > 0
                        ? round(($tier['count'] / $subscriptionTiers['total']) * 100, 1)
                        : 0;
              @endphp
              <div class="{{ $s['bg'] }} rounded-xl p-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-semibold {{ $s['text'] }}">{{ $tier['label'] }}</span>
                  <span class="text-lg font-bold {{ $s['text'] }}">{{ $tier['count'] }}</span>
                </div>
                <div class="w-full bg-white/60 rounded-full h-2.5 overflow-hidden">
                  <div class="{{ $s['bar'] }} h-2.5 rounded-full tier-bar"
                       data-width="{{ $w }}" style="width:0%; transition:width 0.9s cubic-bezier(.34,1.56,.64,1)"></div>
                </div>
                <div class="text-xs {{ $s['text'] }} opacity-70 mt-1">{{ $pct }}% of all</div>
              </div>
            @endforeach
            <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
              <span class="text-sm text-gray-500">Total approved</span>
              <span class="text-xl font-bold text-gray-800">{{ $subscriptionTiers['total'] }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- ── SUBSCRIBER LIST ──────────────────────────────────────────────── --}}
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 px-8 py-6">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
              <h3 class="text-2xl font-bold text-white">Subscribers</h3>
              <p class="text-indigo-200 text-sm mt-0.5" id="subscriberMeta">
                {{ $subscribers->total() }} total &nbsp;·&nbsp;
                Showing {{ $subscribers->firstItem() ?? 0 }}–{{ $subscribers->lastItem() ?? 0 }}
              </p>
            </div>
            <button id="exportCsvBtn" class="bg-white text-indigo-600 px-5 py-2.5 rounded-xl font-semibold text-sm hover:bg-indigo-50 transition-colors shadow flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              Export CSV
            </button>
          </div>
        </div>

        {{-- Filter/Search Bar
             NOTE: No <form> submission — JS intercepts everything and does AJAX
             BUG 4 FIX: preserves scroll position via history.pushState
        --}}
        <div class="px-8 py-5 border-b border-gray-100 bg-gray-50">
          <div class="flex flex-wrap gap-3 items-end">

            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
              <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5 block">Search</label>
              <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="searchInput" type="text"
                  value="{{ $filters['search'] }}"
                  placeholder="Name, email, plan, transaction…"
                  class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition-all">
              </div>
            </div>

            {{-- Plan --}}
            <div class="min-w-[140px]">
              <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5 block">Plan</label>
              <select id="planFilter" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all">
                <option value=""       {{ $filters['plan'] === ''       ? 'selected' : '' }}>All Plans</option>
                <option value="gold"   {{ $filters['plan'] === 'gold'   ? 'selected' : '' }}>Gold</option>
                <option value="silver" {{ $filters['plan'] === 'silver' ? 'selected' : '' }}>Silver</option>
                <option value="bronze" {{ $filters['plan'] === 'bronze' ? 'selected' : '' }}>Bronze</option>
                <option value="boost"  {{ $filters['plan'] === 'boost'  ? 'selected' : '' }}>Boost</option>
              </select>
            </div>

            {{-- Status --}}
            <div class="min-w-[140px]">
              <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5 block">Status</label>
              <select id="statusFilter" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all">
                <option value=""         {{ $filters['status'] === ''         ? 'selected' : '' }}>All Statuses</option>
                <option value="active"   {{ $filters['status'] === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="expired"  {{ $filters['status'] === 'expired'  ? 'selected' : '' }}>Expired</option>
                <option value="pending"  {{ $filters['status'] === 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="cancelled"{{ $filters['status'] === 'cancelled'? 'selected' : '' }}>Cancelled</option>
              </select>
            </div>

            {{-- Sort --}}
            <div class="min-w-[160px]">
              <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5 block">Sort By</label>
              <select id="sortFilter" class="w-full px-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all">
                <option value="newest"      {{ $filters['sort'] === 'newest'      ? 'selected' : '' }}>Newest First</option>
                <option value="oldest"      {{ $filters['sort'] === 'oldest'      ? 'selected' : '' }}>Oldest First</option>
                <option value="amount_desc" {{ $filters['sort'] === 'amount_desc' ? 'selected' : '' }}>Amount ↓</option>
                <option value="amount_asc"  {{ $filters['sort'] === 'amount_asc'  ? 'selected' : '' }}>Amount ↑</option>
                <option value="name_asc"    {{ $filters['sort'] === 'name_asc'    ? 'selected' : '' }}>Name A–Z</option>
              </select>
            </div>

            {{-- Clear --}}
            <div>
              <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5 block opacity-0">x</label>
              <button id="clearFiltersBtn"
                class="{{ ($filters['search'] || $filters['plan'] || $filters['status'] || $filters['sort'] !== 'newest') ? '' : 'invisible' }} px-4 py-2.5 bg-gray-100 text-gray-500 rounded-xl text-sm font-medium hover:bg-gray-200 transition-colors">
                Clear
              </button>
            </div>

          </div>
        </div>

        {{-- Subscriber Table (server-rendered first, AJAX-replaced on filter) --}}
        <div class="p-6" id="subscriberPanel">
          @include('admin.home.subscriberTable', ['subscribers' => $subscribers, 'filters' => $filters])
        </div>

      </div>

    </div>
  </main>
</div>

{{-- Pass AJAX URL and initial filter state to JS --}}
<script>
  window.SubConfig = {
    ajaxUrl:  '{{ route("admin.analytics.subscription.subscribers") }}',
    revenueUrl: '{{ route("admin.analytics.subscription.revenue") }}',
    filters: @json($filters),
    csrfToken: '{{ csrf_token() }}'
  };
</script>
<script src="{{ asset('js/admin/home/subscriptionAnalytics.js') }}" defer></script>
</body>
</html>