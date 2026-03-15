<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/subscriptions.css') }}">

  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
  <script src="{{ asset('js/admin/reusables/filters.js') }}" defer></script>

  <style>
    .date-pill input[type="date"]::-webkit-calendar-picker-indicator {
      opacity: 0.5;
      cursor: pointer;
      filter: invert(30%) sepia(80%) saturate(400%) hue-rotate(210deg);
    }
    .date-pill input[type="date"]::-webkit-calendar-picker-indicator:hover {
      opacity: 1;
    }
  </style>

</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'Subscriptions & Boosts', 'searchPlaceholder' => 'Search by name or ID...'])

      <!-- Subscriptions Section -->
      <section class="px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6">
        <div class="flex justify-end items-center mb-4">
          <button class="add-subscription-btn flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-4 py-2 rounded-lg font-semibold text-sm shadow-sm hover:shadow-md transition transform hover:scale-[1.01]">
            <i class="fi fi-rr-plus"></i>
            <span>Add Plan</span>
          </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
          @forelse($plans as $plan)
            @php
              $accentColor = 'border-indigo-200';
              $iconGradient = 'from-indigo-500 to-blue-600';
              $badgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
              $priceBg = 'bg-indigo-50';
              $priceText = 'text-indigo-700';
              if (stripos($plan->plan_key, 'gold') !== false) {
                $accentColor = 'border-yellow-200';
                $iconGradient = 'from-yellow-400 to-yellow-600';
                $badgeClass = 'bg-yellow-50 text-yellow-700 border-yellow-200';
                $priceBg = 'bg-yellow-50';
                $priceText = 'text-yellow-700';
              } elseif (stripos($plan->plan_key, 'silver') !== false) {
                $accentColor = 'border-gray-200';
                $iconGradient = 'from-gray-400 to-gray-500';
                $badgeClass = 'bg-gray-100 text-gray-700 border-gray-200';
                $priceBg = 'bg-gray-50';
                $priceText = 'text-gray-700';
              } elseif (stripos($plan->plan_key, 'bronze') !== false) {
                $accentColor = 'border-orange-200';
                $iconGradient = 'from-orange-500 to-orange-700';
                $badgeClass = 'bg-orange-50 text-orange-700 border-orange-200';
                $priceBg = 'bg-orange-50';
                $priceText = 'text-orange-700';
              }
              $benefits = json_decode($plan->benefits, true) ?? [];
            @endphp
            <div class="subscription-card bg-white rounded-lg border {{ $accentColor }} shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer relative flex flex-col"
              data-id="{{ $plan->id }}" data-name="{{ $plan->name }}" data-price="{{ $plan->amount / 100 }}"
              data-billing-cycle="{{ $plan->billing_cycle }}" data-duration="{{ $plan->duration_days }}"
              data-benefits="{{ json_encode($benefits) }}">

              <!-- Header: Icon + Name + Badge -->
              <div class="px-3 pt-3 pb-2">
                <div class="flex items-start justify-between gap-1.5">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br {{ $iconGradient }} flex items-center justify-center shadow-sm flex-shrink-0">
                      <i class="fi fi-ss-star text-white text-[10px]"></i>
                    </div>
                    <div class="min-w-0">
                      <h3 class="text-xs font-bold text-gray-800 leading-tight truncate">{{ $plan->name }}</h3>
                      <p class="text-[10px] text-gray-400">{{ ucfirst($plan->billing_cycle) }} charge</p>
                    </div>
                  </div>
                  <div class="flex-shrink-0">
                    @if($plan->for_contractor)
                      <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-semibold border {{ $badgeClass }}">
                        <i class="fi fi-ss-building text-[8px]"></i> Contractor
                      </span>
                    @else
                      <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-semibold border bg-purple-50 text-purple-700 border-purple-200">
                        <i class="fi fi-ss-home text-[8px]"></i> Owner
                      </span>
                    @endif
                  </div>
                </div>
              </div>

              <!-- Price -->
              <div class="mx-3 px-2.5 py-1.5 rounded-md {{ $priceBg }}">
                <span class="text-lg font-extrabold {{ $priceText }}">₱{{ number_format($plan->amount / 100, 2) }}</span>
              </div>

              <!-- Benefits (flex-1 to push footer down) -->
              <div class="px-3 pt-2 pb-2 flex-1">
                <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Benefits</p>
                <ul class="space-y-1">
                  @foreach($benefits as $benefit)
                    <li class="flex items-start gap-1 text-[10px] text-gray-600">
                      <i class="fi fi-ss-check-circle text-emerald-500 text-[10px] mt-0.5 flex-shrink-0"></i>
                      <span>{{ $benefit }}</span>
                    </li>
                  @endforeach
                </ul>
              </div>

              <!-- Actions (always at bottom) -->
              <div class="px-3 pb-2.5 pt-1.5 flex gap-1.5 justify-end border-t border-gray-100 mt-auto">
                <button class="edit-icon w-6 h-6 inline-flex items-center justify-center rounded-md border border-indigo-200 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition active:scale-95" title="Edit Plan">
                  <i class="fi fi-rr-edit text-[10px] leading-none"></i>
                </button>
                <button class="delete-icon w-6 h-6 inline-flex items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 transition active:scale-95" title="Delete Plan">
                  <i class="fi fi-rr-trash text-[10px] leading-none"></i>
                </button>
              </div>
            </div>
          @empty
            <div class="col-span-4 text-center py-8 bg-white rounded-lg border border-dashed border-gray-300">
              <i class="fi fi-rr-box-open text-2xl text-gray-300 block mb-1.5"></i>
              <p class="text-xs font-medium text-gray-500">No active plans</p>
              <p class="text-[10px] text-gray-400 mt-0.5">Click "Add Plan" to create one.</p>
            </div>
          @endforelse
        </div>
      </section>
      <!-- End Subscriptions Section -->

      <!-- Subscription Statistics Section -->
      <section class="px-4 pb-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Total Subscriptions -->
          <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Total Subscriptions</p>
                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($stats['total'] ?? 0) }}</h2>
                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-blue-100 mt-2 w-fit">
                  <i class="fi fi-sr-database text-[10px] text-blue-600"></i>
                  <span class="text-[11px] font-semibold text-blue-600">All records</span>
                </div>
              </div>
              <div class="stat-icon-wrap bg-blue-100 p-2.5 rounded-lg"><i class="fi fi-sr-calendar-check text-lg text-blue-600"></i></div>
            </div>
            <p class="text-[11px] text-gray-400">All time</p>
          </div>

          <!-- Total Revenue -->
          <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Total Revenue</p>
                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">₱{{ $stats['revenue'] ?? '0.00' }}</h2>
                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-emerald-100 mt-2 w-fit">
                  <i class="fi fi-sr-coins text-[10px] text-emerald-600"></i>
                  <span class="text-[11px] font-semibold text-emerald-600">Earnings</span>
                </div>
              </div>
              <div class="stat-icon-wrap bg-emerald-100 p-2.5 rounded-lg"><i class="fi fi-sr-coins text-lg text-emerald-600"></i></div>
            </div>
            <p class="text-[11px] text-gray-400">Accumulated</p>
          </div>

          <!-- Expiring Soon -->
          <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Expiring Soon</p>
                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($stats['expiring_soon'] ?? 0) }}</h2>
                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-orange-100 mt-2 w-fit">
                  <i class="fi fi-sr-time-check text-[10px] text-orange-600"></i>
                  <span class="text-[11px] font-semibold text-orange-600">Within 7 days</span>
                </div>
              </div>
              <div class="stat-icon-wrap bg-orange-100 p-2.5 rounded-lg"><i class="fi fi-sr-time-check text-lg text-orange-600"></i></div>
            </div>
            <p class="text-[11px] text-gray-400">Needs attention</p>
          </div>

          <!-- Expired -->
          <div class="stat-card bg-white rounded-xl shadow-sm p-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-pointer">
            <div class="flex justify-between items-start gap-3 mb-3">
              <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-500 mb-1.5">Expired Subscriptions</p>
                <h2 class="text-3xl font-bold leading-none text-orange-500 stat-number">{{ number_format($stats['expired'] ?? 0) }}</h2>
                <div class="stat-badge flex items-center gap-1.5 px-2 py-1 rounded-full bg-red-100 mt-2 w-fit">
                  <i class="fi fi-sr-cross-circle text-[10px] text-red-600"></i>
                  <span class="text-[11px] font-semibold text-red-600">Needs renewal</span>
                </div>
              </div>
              <div class="stat-icon-wrap bg-red-100 p-2.5 rounded-lg"><i class="fi fi-sr-cross-circle text-lg text-red-600"></i></div>
            </div>
            <p class="text-[11px] text-gray-400">Expired status</p>
          </div>
        </div>
      </section>
      <!-- End Subscription Statistics Section -->

      <!-- Subscriptions Management Table -->
      <section class="px-4 pb-6 sm:px-6 lg:px-8 space-y-4">

        <!-- Filters Bar -->
        <div class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
              <i class="fi fi-rr-filter text-gray-500"></i>
              <span>Filter By</span>
            </div>

            <!-- Date Range -->
            <div class="flex flex-wrap items-center gap-2">
              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                  <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">From</span>
                </div>
                <input type="date" id="dateFrom"
                  class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
              </div>

              <span class="text-gray-300 font-bold text-lg">→</span>

              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                  <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
                </div>
                <input type="date" id="dateTo"
                  class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
              </div>
            </div>

            <!-- Plan Type Filter -->
            <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
              <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                <i class="fi fi-rr-filter text-white text-sm leading-none"></i>
                <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">Plan Type</span>
              </div>
              <select id="filterPlanType"
                class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none min-w-[150px] border-0">
                <option value="">All Plan Types</option>
                @foreach($allPlanKeys as $key)
                  <option value="{{ $key }}" {{ request('plan_type') === $key ? 'selected' : '' }}>{{ ucfirst($key) }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <button id="resetFilterBtn" class="reset-filter-btn flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
            <i class="fi fi-rr-rotate-left"></i>
            <span>Reset Filter</span>
          </button>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <!-- Tabs Header -->
          <div class="border-b border-gray-200">
            <div class="flex px-6">
              <button id="tabActiveSubscriptions"
                class="subscription-tab active px-4 py-3 text-sm font-semibold border-b-2 border-orange-500 text-gray-700 transition-all">
                Active Subscriptions
              </button>
              <button id="tabExpiredSubscriptions"
                class="subscription-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:border-orange-300 transition-all">
                Expired Subscriptions
              </button>
              <button id="tabCancelledSubscriptions"
                class="subscription-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:border-orange-300 transition-all">
                Cancelled Subscriptions
              </button>
            </div>
          </div>

          <!-- Active Subscriptions Table -->
          <div id="activeSubscriptionsTable" class="{{ request()->has('page_expired') || request()->has('page_cancelled') ? '' : '' }}">
            <div id="activeSubscriptionsWrap">
              <div class="overflow-x-auto">
                <table class="w-full table-fixed">
                  <thead>
                    <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[6%]">ID</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[30%]">Name / Project</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Type / Plan</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Date</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Expiry</th>
                      <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="activeSubscriptionsTbody" class="divide-y divide-gray-200">
                    @include('admin.projectManagement.partials.activeSubscriptionsTable')
                  </tbody>
                </table>
              </div>
              @if($activeSubscriptions->hasPages())
                <div class="pagination px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
                  <p class="text-xs text-gray-500">
                    Showing <strong>{{ $activeSubscriptions->firstItem() }}</strong>–<strong>{{ $activeSubscriptions->lastItem() }}</strong> of <strong>{{ $activeSubscriptions->total() }}</strong> results
                  </p>
                  <div class="flex items-center gap-1">
                    @if($activeSubscriptions->onFirstPage())
                      <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
                    @else
                      <a href="{{ $activeSubscriptions->previousPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
                    @endif
                    @foreach($activeSubscriptions->getUrlRange(max(1, $activeSubscriptions->currentPage()-2), min($activeSubscriptions->lastPage(), $activeSubscriptions->currentPage()+2)) as $page => $url)
                      @if($page == $activeSubscriptions->currentPage())
                        <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
                      @else
                        <a href="{{ $url }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
                      @endif
                    @endforeach
                    @if($activeSubscriptions->hasMorePages())
                      <a href="{{ $activeSubscriptions->nextPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
                    @else
                      <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
                    @endif
                  </div>
                </div>
              @else
                <div class="px-4 py-3 border-t border-gray-200">
                  <p class="text-xs text-gray-500">Showing <strong>{{ $activeSubscriptions->total() }}</strong> result(s)</p>
                </div>
              @endif
            </div>
          </div>

          <!-- Expired Subscriptions Table -->
          <div id="expiredSubscriptionsTable" class="hidden">
            <div id="expiredSubscriptionsWrap">
              <div class="overflow-x-auto">
                <table class="w-full table-fixed">
                  <thead>
                    <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[6%]">ID</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[30%]">Name / Project</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Type / Plan</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Date</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Expiry</th>
                      <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="expiredSubscriptionsTbody" class="divide-y divide-gray-200">
                    @include('admin.projectManagement.partials.expiredSubscriptionsTable')
                  </tbody>
                </table>
              </div>
              @if($expiredSubscriptions->hasPages())
                <div class="pagination px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
                  <p class="text-xs text-gray-500">
                    Showing <strong>{{ $expiredSubscriptions->firstItem() }}</strong>–<strong>{{ $expiredSubscriptions->lastItem() }}</strong> of <strong>{{ $expiredSubscriptions->total() }}</strong> results
                  </p>
                  <div class="flex items-center gap-1">
                    @if($expiredSubscriptions->onFirstPage())
                      <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
                    @else
                      <a href="{{ $expiredSubscriptions->previousPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
                    @endif
                    @foreach($expiredSubscriptions->getUrlRange(max(1, $expiredSubscriptions->currentPage()-2), min($expiredSubscriptions->lastPage(), $expiredSubscriptions->currentPage()+2)) as $page => $url)
                      @if($page == $expiredSubscriptions->currentPage())
                        <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
                      @else
                        <a href="{{ $url }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
                      @endif
                    @endforeach
                    @if($expiredSubscriptions->hasMorePages())
                      <a href="{{ $expiredSubscriptions->nextPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
                    @else
                      <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
                    @endif
                  </div>
                </div>
              @else
                <div class="px-4 py-3 border-t border-gray-200">
                  <p class="text-xs text-gray-500">Showing <strong>{{ $expiredSubscriptions->total() }}</strong> result(s)</p>
                </div>
              @endif
            </div>
          </div>

          <!-- Cancelled Subscriptions Table -->
          <div id="cancelledSubscriptionsTable" class="hidden">
            <div id="cancelledSubscriptionsWrap">
              <div class="overflow-x-auto">
                <table class="w-full table-fixed">
                  <thead>
                    <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[6%]">ID</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[36%]">Name / Project</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[20%]">Type / Plan</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[20%]">Date</th>
                      <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="cancelledSubscriptionsTbody" class="divide-y divide-gray-200">
                    @include('admin.projectManagement.partials.cancelledSubscriptionsTable')
                  </tbody>
                </table>
              </div>
              @if($cancelledSubscriptions->hasPages())
                <div class="pagination px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
                  <p class="text-xs text-gray-500">
                    Showing <strong>{{ $cancelledSubscriptions->firstItem() }}</strong>–<strong>{{ $cancelledSubscriptions->lastItem() }}</strong> of <strong>{{ $cancelledSubscriptions->total() }}</strong> results
                  </p>
                  <div class="flex items-center gap-1">
                    @if($cancelledSubscriptions->onFirstPage())
                      <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
                    @else
                      <a href="{{ $cancelledSubscriptions->previousPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
                    @endif
                    @foreach($cancelledSubscriptions->getUrlRange(max(1, $cancelledSubscriptions->currentPage()-2), min($cancelledSubscriptions->lastPage(), $cancelledSubscriptions->currentPage()+2)) as $page => $url)
                      @if($page == $cancelledSubscriptions->currentPage())
                        <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
                      @else
                        <a href="{{ $url }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
                      @endif
                    @endforeach
                    @if($cancelledSubscriptions->hasMorePages())
                      <a href="{{ $cancelledSubscriptions->nextPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
                    @else
                      <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
                    @endif
                  </div>
                </div>
              @else
                <div class="px-4 py-3 border-t border-gray-200">
                  <p class="text-xs text-gray-500">Showing <strong>{{ $cancelledSubscriptions->total() }}</strong> result(s)</p>
                </div>
              @endif
            </div>
          </div>
        </div>
      </section>
      <!-- End Subscriptions Management Table -->



    </main>

    <!-- Add Subscription Plan Modal -->
    <div id="addSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 add-subscription-panel">
        <!-- Header -->
        <div class="sticky top-0 z-10 bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
              <i class="fi fi-rr-plus-small text-white text-sm"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-white">Add Subscription Plan</h2>
          </div>
          <button id="closeAddSubscriptionBtn" class="text-white hover:text-orange-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
            <i class="fi fi-rr-cross text-lg"></i>
          </button>
        </div>

        <!-- Body -->
        <form id="addSubscriptionForm">
          <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-4">
            <!-- Subscription Name -->
            <div>
              <label for="subscriptionName" class="block text-xs font-semibold text-gray-800 mb-1.5">Subscription Name <span class="text-red-500">*</span></label>
              <input type="text" id="subscriptionName" name="subscription_name"
                class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition text-xs"
                placeholder="Enter subscription name" required>
              <p id="subscriptionNameError" class="mt-1 text-xs text-red-600 hidden">Subscription name is required.</p>
            </div>

            <!-- Benefits -->
            <div>
              <label class="block text-xs font-semibold text-gray-800 mb-1.5">Benefits</label>
              <div id="benefitsContainer" class="space-y-2">
                <div class="flex items-center gap-2 benefit-item">
                  <input type="checkbox" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 benefit-checkbox" checked>
                  <input type="text" class="flex-1 px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition text-xs benefit-input" placeholder="Enter benefit">
                  <button type="button" class="text-red-500 hover:text-red-700 transition p-1 remove-benefit-btn hidden">
                    <i class="fi fi-rr-cross-small text-lg"></i>
                  </button>
                </div>
              </div>
              <button type="button" id="addBenefitBtn" class="mt-2 text-orange-600 hover:text-orange-700 text-xs font-semibold flex items-center gap-1 transition">
                <span>+</span> Add another
              </button>
              <p id="benefitsError" class="mt-1 text-xs text-red-600 hidden">At least one benefit is required.</p>
            </div>

            <!-- Price -->
            <div>
              <label for="subscriptionPrice" class="block text-xs font-semibold text-gray-800 mb-1.5">Price <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium text-xs">₱</span>
                <input type="number" id="subscriptionPrice" name="subscription_price"
                  class="w-full pl-7 pr-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition text-xs"
                  placeholder="0.00" min="0" step="0.01" required>
              </div>
              <p id="subscriptionPriceError" class="mt-1 text-xs text-red-600 hidden">Price is required.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <!-- Plan Key -->
              <div>
                <label for="planKey" class="block text-xs font-semibold text-gray-800 mb-1.5">Plan Key <span class="text-red-500">*</span></label>
                <input type="text" id="planKey" name="plan_key"
                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition text-xs"
                  placeholder="e.g. basic, premium" required>
                <p id="planKeyError" class="mt-1 text-xs text-red-600 hidden">Plan key is required.</p>
              </div>

              <!-- Target Audience -->
              <div>
                <label class="block text-xs font-semibold text-gray-800 mb-1.5">Target Audience <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-3 mt-1.5">
                  <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="for_contractor" value="1" class="form-radio text-orange-500 focus:ring-orange-400 w-3.5 h-3.5">
                    <span class="ml-1.5 text-xs text-gray-700">Contractors</span>
                  </label>
                  <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="for_contractor" value="0" class="form-radio text-orange-500 focus:ring-orange-400 w-3.5 h-3.5">
                    <span class="ml-1.5 text-xs text-gray-700">Owners</span>
                  </label>
                </div>
                <p id="forContractorError" class="mt-1 text-xs text-red-600 hidden">Please select a target audience.</p>
              </div>

              <!-- Billing Cycle -->
              <div class="col-span-2 sm:col-span-1">
                <label for="billingCycle" class="block text-xs font-semibold text-gray-800 mb-1.5">Billing Cycle <span class="text-red-500">*</span></label>
                <select id="billingCycle" name="billing_cycle"
                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition bg-white text-xs" required>
                  <option value="monthly">Monthly</option>
                  <option value="yearly">Yearly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="one-time">One-time</option>
                </select>
              </div>

              <!-- Duration Days -->
              <div class="col-span-2 sm:col-span-1 hidden" id="durationDaysContainer">
                <label for="durationDays" class="block text-xs font-semibold text-gray-800 mb-1.5">Duration (Days) <span class="text-red-500">*</span></label>
                <input type="number" id="durationDays" name="duration_days"
                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition text-xs"
                  placeholder="e.g. 30" min="1">
                <p id="durationDaysError" class="mt-1 text-xs text-red-600 hidden">Duration is required.</p>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
            <button type="button" id="cancelAddSubscriptionBtn"
              class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
            <button type="submit"
              class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition font-semibold shadow-md hover:shadow-lg active:scale-95 text-xs">Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Subscription Plan Modal -->
    <div id="editSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 edit-subscription-panel">
        <!-- Header -->
        <div class="sticky top-0 z-10 bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
              <i class="fi fi-rr-edit text-white text-sm"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-white">Edit Subscription Plan</h2>
          </div>
          <button id="closeEditSubscriptionBtn"
            class="text-white hover:text-indigo-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
            <i class="fi fi-rr-cross text-lg"></i>
          </button>
        </div>

        <!-- Body -->
        <form id="editSubscriptionForm">
          <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-4">
            <!-- Subscription Name -->
            <div>
              <label for="editSubscriptionName" class="block text-xs font-semibold text-gray-800 mb-1.5">Subscription Name
                <span class="text-red-500">*</span></label>
              <input type="text" id="editSubscriptionName" name="edit_subscription_name"
                class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition text-xs"
                placeholder="Enter subscription name" required>
              <p id="editSubscriptionNameError" class="mt-1 text-xs text-red-600 hidden">Subscription name is required.</p>
            </div>

            <!-- Benefits -->
            <div>
              <label class="block text-xs font-semibold text-gray-800 mb-1.5">Benefits</label>
              <div id="editBenefitsContainer" class="space-y-2">
                <!-- Dynamically injected benefit rows -->
              </div>
              <button type="button" id="editAddBenefitBtn"
                class="mt-2 text-indigo-600 hover:text-indigo-700 text-xs font-semibold flex items-center gap-1 transition">
                <span>+</span> Add another
              </button>
              <p id="editBenefitsError" class="mt-1 text-xs text-red-600 hidden">At least one benefit is required.</p>
            </div>

            <!-- Price -->
            <div>
              <label for="editSubscriptionPrice" class="block text-xs font-semibold text-gray-800 mb-1.5">Price <span
                  class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium text-xs">₱</span>
                <input type="number" id="editSubscriptionPrice" name="edit_subscription_price"
                  class="w-full pl-7 pr-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition text-xs"
                  placeholder="0.00" min="0" step="0.01" required>
              </div>
              <p id="editSubscriptionPriceError" class="mt-1 text-xs text-red-600 hidden">Price is required.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <!-- Billing Cycle -->
              <div class="col-span-2 sm:col-span-1">
                <label for="editBillingCycle" class="block text-xs font-semibold text-gray-800 mb-1.5">Billing Cycle <span
                    class="text-red-500">*</span></label>
                <select id="editBillingCycle" name="edit_billing_cycle"
                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition bg-white text-xs"
                  required>
                  <option value="" disabled selected>Select cycle</option>
                  <option value="monthly">Monthly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="yearly">Yearly</option>
                  <option value="one-time">One-time</option>
                </select>
                <p id="editBillingCycleError" class="mt-1 text-xs text-red-600 hidden">Billing cycle is required.</p>
              </div>

              <!-- Edit Duration Days (hidden by default) -->
              <div class="col-span-2 sm:col-span-1 hidden" id="editDurationDaysContainer">
                <label for="editDurationDays" class="block text-xs font-semibold text-gray-800 mb-1.5">Duration (Days) <span
                    class="text-red-500">*</span></label>
                <input type="number" id="editDurationDays" name="edit_duration_days"
                  class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition text-xs"
                  placeholder="e.g. 30" min="1">
                <p id="editDurationDaysError" class="mt-1 text-xs text-red-600 hidden">Duration is required.</p>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
            <button type="button" id="cancelEditSubscriptionBtn"
              class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">
              Cancel
            </button>
            <button type="submit"
              class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition font-semibold shadow-md hover:shadow-lg active:scale-95 text-xs">
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Subscription Plan Modal -->
    <div id="deleteSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 delete-subscription-panel">
        <!-- Header -->
        <div class="sticky top-0 z-10 bg-gradient-to-r from-red-500 to-red-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
              <i class="fi fi-rr-trash text-white text-sm"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-white">Delete Subscription Plan</h2>
          </div>
          <button id="closeDeleteSubscriptionBtn"
            class="text-white hover:text-red-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
            <i class="fi fi-rr-cross text-lg"></i>
          </button>
        </div>
        <!-- Body -->
        <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-4">
          <div class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2.5">
            <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
              <i class="fi fi-rr-shield-exclamation text-white text-xs"></i>
            </div>
            <p class="text-xs text-gray-700 leading-relaxed">Are you sure you want to delete the <span id="deletePlanName"
                class="font-bold text-red-600">selected plan</span>? This action cannot be undone.</p>
          </div>

          <div class="space-y-1.5">
            <label for="deleteSubscriptionReason"
              class="text-xs font-semibold text-gray-800 flex items-center gap-1.5">
              <i class="fi fi-rr-edit text-red-500"></i>
              Reason <span class="text-red-500">*</span></label>
            <textarea id="deleteSubscriptionReason" rows="3"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-300 focus:border-red-300 transition text-xs resize-none"
              placeholder="Provide a reason"></textarea>
            <p id="deleteSubscriptionReasonError" class="text-xs text-red-600 hidden">A reason is required.</p>
          </div>
        </div>
        <!-- Footer -->
        <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
          <button id="cancelDeleteSubscriptionBtn"
            class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
          <button id="confirmDeleteSubscriptionBtn"
          class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition active:scale-95 flex items-center gap-1.5 text-xs">
            <i class="fi fi-rr-trash text-xs"></i>Delete
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Subscription (Row) Details Modal -->
    <div id="rowEditSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 row-edit-subscription-panel">
        <!-- Header -->
        <div class="sticky top-0 z-10 bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
              <i class="fi fi-rr-edit text-white text-sm"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-white">Edit Subscription Details</h2>
          </div>
          <button id="rowEditCloseBtn"
            class="text-white hover:text-indigo-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
            <i class="fi fi-rr-cross text-lg"></i>
          </button>
        </div>

        <!-- Body -->
        <form id="rowEditSubscriptionForm">
          <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Contractor -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Contractor</label>
                <input id="rowEditContractor" type="text"
                  class="w-full px-3 py-1.5 rounded-lg border border-gray-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 transition bg-white text-xs"
                  readonly>
              </div>
              <!-- Status -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Status</label>
                <div id="rowEditStatusBadge"
                  class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-2 border border-gray-200 bg-gray-50 text-gray-700">
                </div>
              </div>
              <!-- Plan -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Plan</label>
                <select id="rowEditPlan"
                  class="w-full px-3 py-1.5 rounded-lg border border-gray-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 transition bg-white text-xs">
                  <option value="Gold Tier">Gold Tier</option>
                  <option value="Silver Tier">Silver Tier</option>
                  <option value="Bronze Tier">Bronze Tier</option>
                </select>
              </div>
              <!-- Total Revenue -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Total Revenue</label>
                <input id="rowEditRevenue" type="text"
                  class="w-full px-3 py-1.5 rounded-lg border border-gray-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 transition bg-white text-xs"
                  readonly>
              </div>
              <!-- Start Date -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Start Date</label>
                <input id="rowEditStartDate" type="date"
                  class="w-full px-3 py-1.5 rounded-lg border border-gray-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 transition bg-white text-xs">
              </div>
              <!-- Expiry Date -->
              <div class="space-y-1.5">
                <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Expiry Date</label>
                <input id="rowEditExpiryDate" type="date"
                  class="w-full px-3 py-1.5 rounded-lg border border-gray-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 transition bg-white text-xs">
              </div>
            </div>
            <p id="rowEditDatesError" class="text-xs text-red-600 hidden">Start and expiry dates are required.</p>
          </div>

          <!-- Footer -->
          <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
            <button type="button" id="rowEditCancelBtn"
              class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
            <button type="submit"
              class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition active:scale-95 flex items-center gap-1.5 text-xs">
              <i class="fi fi-rr-check text-xs"></i>Save
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Deactivate Subscription Modal -->
    <div id="deactivateSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 deactivate-subscription-panel">
        <!-- Header -->
        <div class="sticky top-0 z-10 bg-gradient-to-r from-red-500 to-red-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
              <i class="fi fi-rr-ban text-white text-sm"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-white">Deactivate Subscription</h2>
          </div>
          <button id="closeDeactivateSubscriptionBtn"
            class="text-white hover:text-red-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
            <i class="fi fi-rr-cross text-lg"></i>
          </button>
        </div>
        <!-- Body -->
        <form id="deactivateSubscriptionForm">
          <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-4">
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 space-y-2.5">
              <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                  <i class="fi fi-rr-shield-exclamation text-white text-xs"></i>
                </div>
                <div>
                  <h3 class="text-xs font-semibold text-gray-800 mb-0.5">Confirm Deactivation</h3>
                  <p class="text-xs text-gray-700 leading-relaxed">You are about to deactivate the subscription for <span
                      id="deactivateContractorName" class="font-bold text-red-600">Selected Contractor</span>. This will immediately revoke active benefits.</p>
                </div>
              </div>
            </div>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-2.5 rounded-r-lg">
              <div class="flex gap-2">
                <i class="fi fi-rr-triangle-warning text-yellow-600 text-sm flex-shrink-0 mt-0.5"></i>
                <p class="text-xs text-gray-700">Deactivation cannot be undone automatically. A manual renewal or new purchase will be required to restore benefits.</p>
              </div>
            </div>
            <div>
              <label for="deactivateReason" class="block text-xs font-semibold text-gray-800 mb-1.5 flex items-center gap-1.5">
                <i class="fi fi-rr-edit text-red-500"></i>
                Reason <span class="text-red-500">*</span>
              </label>
              <textarea id="deactivateReason" rows="3"
                class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-300 focus:border-red-300 transition text-xs resize-none"
                placeholder="Provide a clear reason (e.g. policy violation, duplicate account)..."></textarea>
              <p id="deactivateReasonError" class="text-xs text-red-600 hidden mt-1">A reason is required.</p>
            </div>
          </div>
          <!-- Footer -->
          <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
            <button type="button" id="cancelDeactivateSubscriptionBtn"
              class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
            <button type="submit" id="confirmDeactivateSubscriptionBtn"
              class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition active:scale-95 flex items-center gap-1.5 text-xs">
              <i class="fi fi-rr-ban text-xs"></i>Deactivate
            </button>
          </div>
        </form>
      </div>
    </div>
    <!-- Reactivate Subscription Modal -->
    <div id="reactivateSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 reactivate-subscription-panel">
        <!-- Header -->
        <div class="sticky top-0 z-10 bg-gradient-to-r from-green-500 to-green-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
              <i class="fi fi-rr-undo text-white text-sm"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-white">Reactivate Subscription</h2>
          </div>
          <button id="closeReactivateSubscriptionBtn"
            class="text-white hover:text-emerald-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
            <i class="fi fi-rr-cross text-lg"></i>
          </button>
        </div>
        <!-- Body -->
        <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-4">
          <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 space-y-2.5">
            <div class="flex items-start gap-2.5">
              <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fi fi-rr-check text-white text-xs"></i>
              </div>
              <div>
                <h3 class="text-xs font-semibold text-gray-800 mb-0.5">Confirm Reactivation</h3>
                <p class="text-xs text-gray-700 leading-relaxed">You are about to reactivate the subscription for <span
                    id="reactivateContractorName" class="font-bold text-emerald-600">Selected Contractor</span>. This will immediately restore all active benefits.</p>
              </div>
            </div>
          </div>
          <div class="bg-yellow-50 border-l-4 border-yellow-500 p-2.5 rounded-r-lg">
            <div class="flex gap-2">
              <i class="fi fi-rr-triangle-warning text-yellow-600 text-sm flex-shrink-0 mt-0.5"></i>
              <p class="text-xs text-gray-700">Reactivating will set the status back to 'Approved' and clear any previous deactivation records. The user will be notified.</p>
            </div>
          </div>
        </div>
        <!-- Footer -->
        <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
          <button type="button" id="cancelReactivateSubscriptionBtn"
            class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Cancel</button>
          <button type="button" id="confirmReactivateSubscriptionBtn"
            class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition active:scale-95 flex items-center gap-1.5 text-xs">
            <i class="fi fi-rr-undo text-xs"></i>Reactivate Now
          </button>
        </div>
      </div>
    </div>

    <!-- View Subscription Details Modal -->
    <div id="viewSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 view-subscription-panel">
        <!-- Header -->
        <div class="sticky top-0 z-10 bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
              <i class="fi fi-rr-eye text-white text-sm"></i>
            </div>
            <h2 class="text-sm sm:text-base font-bold text-white">Subscription Details</h2>
          </div>
          <button id="closeViewSubscriptionBtn"
            class="text-white hover:text-orange-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
            <i class="fi fi-rr-cross text-lg"></i>
          </button>
        </div>
        <!-- Body -->
        <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <!-- ID -->
            <div class="space-y-1">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Payment ID</label>
              <p id="viewSubId" class="text-xs font-semibold text-gray-800 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">—</p>
            </div>
            <!-- Name -->
            <div class="space-y-1">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Name</label>
              <p id="viewSubName" class="text-xs font-semibold text-gray-800 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">—</p>
            </div>
            <!-- Project (shown only for boost/owner) -->
            <div class="space-y-1 hidden" id="viewSubProjectContainer">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Project</label>
              <p id="viewSubProject" class="text-xs font-semibold text-gray-800 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">—</p>
            </div>
            <!-- Plan -->
            <div class="space-y-1">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Plan</label>
              <p id="viewSubPlan" class="text-xs font-semibold text-gray-800 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">—</p>
            </div>
            <!-- Plan Key -->
            <div class="space-y-1">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Plan Key</label>
              <p id="viewSubPlanKey" class="text-xs font-semibold text-gray-800 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">—</p>
            </div>
            <!-- Type -->
            <div class="space-y-1">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Type</label>
              <p id="viewSubType" class="text-xs font-semibold text-gray-800 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">—</p>
            </div>
            <!-- Billing Cycle -->
            <div class="space-y-1">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Billing Cycle</label>
              <p id="viewSubBilling" class="text-xs font-semibold text-gray-800 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">—</p>
            </div>
            <!-- Amount -->
            <div class="space-y-1">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Amount Paid</label>
              <p id="viewSubAmount" class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">—</p>
            </div>
            <!-- Transaction Number -->
            <div class="space-y-1">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Transaction Number</label>
              <p id="viewSubTxn" class="text-xs font-semibold text-gray-800 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200 truncate" title="">—</p>
            </div>
            <!-- Transaction Date -->
            <div class="space-y-1">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Transaction Date</label>
              <p id="viewSubDate" class="text-xs font-semibold text-gray-800 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">—</p>
            </div>
            <!-- Expiry Date -->
            <div class="space-y-1">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Expiry Date</label>
              <p id="viewSubExpiry" class="text-xs font-bold text-red-600 bg-red-50 px-3 py-1.5 rounded-lg border border-red-200">—</p>
            </div>
            <!-- Duration (shown only for one-time) -->
            <div class="space-y-1 hidden" id="viewSubDurationContainer">
              <label class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">Duration (Days)</label>
              <p id="viewSubDuration" class="text-xs font-semibold text-gray-800 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">—</p>
            </div>
          </div>

          <!-- Status Banner -->
          <div id="viewSubStatusBanner" class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2.5">
            <i id="viewSubStatusIcon" class="fi fi-rr-calendar-clock text-red-500 text-base mt-0.5"></i>
            <div id="viewSubStatusText" class="text-xs text-red-700">This subscription has
              <strong>expired</strong>. The user would need to purchase a new subscription or boost to restore benefits.
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
          <button type="button" id="cancelViewSubscriptionBtn"
            class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition font-semibold active:scale-95 text-xs">Close</button>
        </div>
      </div>
    </div>

    <script src="{{ asset('js/admin/projectManagement/subscriptions.js') }}" defer></script>

</body>

</html>
