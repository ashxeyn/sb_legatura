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


</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    <aside class="bg-white shadow-xl flex flex-col">

      <div class="flex justify-center items-center">
        <img src="{{ asset('img/logo.svg') }}" alt="Legatura Logo" class="logo-img">
      </div>



      <nav class="flex-1 px-3 py-4 space-y-1">
        <div class="nav-group">
          <button class="nav-btn">
            <div class="flex items-center gap-3">
              <i class="fi fi-ss-home" style="font-size: 20px;"></i>
              <span>Home</span>
            </div>
            <span class="arrow">▼</span>
          </button>
          <div class="nav-submenu">
            <a href="{{ route('admin.dashboard') }}" class="submenu-link active">Dashboard</a>
            <div class="submenu-nested">
              <button class="submenu-link submenu-nested-btn">
                <span>Analytics</span>
                <span class="arrow-small">▼</span>
              </button>
              <div class="submenu-nested-content">
                <a href="{{ route('admin.analytics') }}" class="submenu-nested-link">Project Analytics</a>
                <a href="{{ route('admin.analytics.subscription') }}" class="submenu-nested-link">Subscription
                  Analytics</a>
                <a href="{{ route('admin.analytics.userActivity') }}" class="submenu-nested-link">User Activity
                  Analytics</a>
                <a href="{{ route('admin.analytics.projectPerformance') }}" class="submenu-nested-link">Project
                  Performance Analytics</a>
                <a href="{{ route('admin.analytics.bidCompletion') }}" class="submenu-nested-link">Bid Completion
                  Analytics</a>
                <a href="{{ route('admin.analytics.reports') }}" class="submenu-nested-link">Reports and Analytics</a>
              </div>
            </div>
          </div>
        </div>


        <div class="nav-group">
          <button class="nav-btn">
            <div class="flex items-center gap-3">
              <i class="fi fi-ss-users-alt" style="font-size: 20px;"></i>
              <span>User Management</span>
            </div>
            <span class="arrow">▼</span>
          </button>

          <div class="nav-submenu">
            <a href="{{ route('admin.userManagement.propertyOwner') }}" class="submenu-link">Property Owner</a>
            <a href="{{ route('admin.userManagement.contractor') }}" class="submenu-link">Contractor</a>
            <a href="{{ route('admin.userManagement.verificationRequest') }}" class="submenu-link">Verification
              Request</a>
            <a href="{{ route('admin.userManagement.suspendedAccounts') }}" class="submenu-link">Suspended Accounts</a>
          </div>
        </div>


        <div class="nav-group">
          <button class="nav-btn">
            <div class="flex items-center gap-3">
              <i class="fi fi-ss-globe" style="font-size: 20px;"></i>

              <span>Global Management</span>
            </div>
            <span class="arrow">▼</span>
          </button>
          <div class="nav-submenu">
            <a href="{{ route('admin.globalManagement.bidManagement') }}" class="submenu-link">Bid Management</a>
            <a href="{{ route('admin.globalManagement.proofOfpayments') }}" class="submenu-link">Proof of Payments</a>
            <a href="{{ route('admin.globalManagement.aiManagement') }}" class="submenu-link">AI Management</a>
            <a href="{{ route('admin.globalManagement.postingManagement') }}" class="submenu-link">Posting
              Management</a>
          </div>
        </div>

        <div class="nav-group">
          <button class="nav-btn">
            <div class="flex items-center gap-3">
              <i class="fi fi-sr-master-plan" style="font-size: 20px;"></i>
              <span>Project Management</span>
            </div>
            <span class="arrow">▼</span>
          </button>
          <div class="nav-submenu">
            <a href="{{ route('admin.projectManagement.listOfProjects') }}" class="submenu-link">List of Projects</a>
            <a href="{{ route('admin.projectManagement.disputesReports') }}" class="submenu-link">Disputes/Reports</a>
            <a href="{{ route('admin.projectManagement.messages') }}" class="submenu-link">Messages</a>
            <a href="{{ route('admin.projectManagement.subscriptions') }}" class="submenu-link active">Subscriptions &
              Boosts</a>
          </div>
        </div>

        <div class="nav-group">
          <button class="nav-btn">
            <div class="flex items-center gap-3">
              <i class="fi fi-br-settings-sliders" style="font-size: 20px;"></i>
              <span>Settings</span>
            </div>
            <span class="arrow">▼</span>
          </button>
          <div class="nav-submenu">
            <a href="{{ route('admin.settings.notifications') }}" class="submenu-link">Notifications</a>
            <a href="{{ route('admin.settings.security') }}" class="submenu-link">Security</a>
          </div>
        </div>
      </nav>

      <div class="mt-auto p-4">
        <div class="user-card flex items-center gap-3 p-3 rounded-lg shadow-md text-white">
          <div
            class="w-10 h-10 rounded-full bg-white text-indigo-900 flex items-center justify-center font-bold shadow flex-shrink-0">
            ES
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm truncate">Emmanuelle Santos</div>
            <div class="text-xs opacity-80 truncate">santos@Legatura.com</div>
          </div>
          <div class="relative">
            <button id="userMenuBtn"
              class="text-white opacity-80 hover:opacity-100 transition text-2xl w-8 h-8 flex items-center justify-center rounded-full">⋮</button>
            <div id="userMenuDropdown"
              class="absolute right-0 bottom-full mb-2 w-44 bg-white text-gray-800 rounded-xl shadow-2xl border border-gray-200 hidden">
              <div class="px-4 py-3 border-b border-gray-100">
                <div class="text-sm font-semibold truncate">Emmanuelle Santos</div>
                <div class="text-xs text-gray-500 truncate">santos@Legatura.com</div>
              </div>
              <ul class="py-1">
                <li>
                  <a href="{{ route('admin.settings.security') }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50">
                    <i class="fi fi-br-settings-sliders"></i>
                    <span>Account settings</span>
                  </a>
                </li>
                <li>
                  <button id="logoutBtn"
                    class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 text-red-600">
                    <i class="fi fi-ss-exit"></i>
                    <span>Logout</span>
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

    </aside>

    <main class="flex-1">
      <header
        class="bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-8 py-4 sticky top-0 z-30">
        <h1 class="text-2xl font-semibold text-gray-800">Subscriptions & Boosts</h1>

        <div class="flex items-center gap-6">
          <div class="relative w-64" style="width: 600px;">
            <input type="text" placeholder="Search..."
              class="border border-gray-300 rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full">
            <i class="fi fi-rr-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          </div>


          <div class="relative">
            <button id="notificationBell"
              class="cursor-pointer w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
              <i class="fi fi-ss-bell-notification-social-media" style="font-size: 20px;"></i>
            </button>
            <span
              class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>

            <!-- Notifications Dropdown -->
            <div id="notificationDropdown"
              class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 hidden">
              <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-800">Notifications</span>
                <button id="clearNotifications" class="text-xs text-indigo-600 hover:text-indigo-700">Clear all</button>
              </div>
              <ul class="max-h-80 overflow-y-auto" id="notificationList">
                <li class="px-4 py-3 hover:bg-gray-50 transition">
                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                      <i class="fi fi-ss-bell"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-800 truncate">New bid submitted on “GreenBelt Building”.</p>
                      <p class="text-xs text-gray-500">2 mins ago</p>
                    </div>
                    <span class="inline-block px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">New</span>
                  </div>
                </li>
                <li class="px-4 py-3 hover:bg-gray-50 transition">
                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center">
                      <i class="fi fi-ss-check-circle"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-800 truncate">Verification request approved for Cabonting Architects.
                      </p>
                      <p class="text-xs text-gray-500">1 hour ago</p>
                    </div>
                  </div>
                </li>
                <li class="px-4 py-3 hover:bg-gray-50 transition">
                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center">
                      <i class="fi fi-ss-exclamation"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-800 truncate">High-risk flag: Duplex Housing requires review.</p>
                      <p class="text-xs text-gray-500">Yesterday</p>
                    </div>
                  </div>
                </li>
              </ul>
              <div class="px-4 py-3 border-t border-gray-100">
                <a href="{{ route('admin.settings.notifications') }}"
                  class="text-sm text-indigo-600 hover:text-indigo-700">Notification settings</a>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Subscriptions Section -->
      <section class="px-8 py-8">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-gray-800">Subscription Plans</h2>
          <button
            class="add-subscription-btn bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3 rounded-lg transition-all transform hover:scale-105 hover:shadow-lg flex items-center gap-2">
            <span class="text-xl">+</span>
            <span>Add Subscription</span>
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          @forelse($plans as $plan)
            @php
              // Assign gradient color based on plan key
              $gradientColor = 'bg-gradient-to-br from-indigo-400 to-purple-600';
              $hoverColor = 'hover:border-indigo-400';
              $borderColor = 'border-transparent';
              if (stripos($plan->plan_key, 'gold') !== false) {
                $gradientColor = 'bg-gradient-to-br from-yellow-400 to-yellow-600';
                $hoverColor = 'hover:border-yellow-400';
              } elseif (stripos($plan->plan_key, 'silver') !== false) {
                $gradientColor = 'bg-gradient-to-br from-gray-300 to-gray-500';
                $hoverColor = 'hover:border-gray-400';
              } elseif (stripos($plan->plan_key, 'bronze') !== false) {
                $gradientColor = 'bg-gradient-to-br from-orange-600 to-orange-800';
                $hoverColor = 'hover:border-orange-700';
              }
              $benefits = json_decode($plan->benefits, true) ?? [];
            @endphp
            <div
              class="subscription-card bg-white rounded-2xl shadow-lg p-6 transition-all hover:shadow-2xl hover:-translate-y-2 cursor-pointer border-2 {{ $borderColor }} {{ $hoverColor }} relative overflow-hidden group"
              data-id="{{ $plan->id }}" data-name="{{ $plan->name }}" data-price="{{ $plan->amount / 100 }}"
              data-billing-cycle="{{ $plan->billing_cycle }}" data-duration="{{ $plan->duration_days }}"
              data-benefits="{{ json_encode($benefits) }}">

              <div class="absolute top-4 right-4 flex gap-2 z-10">
                <button
                  class="edit-icon text-gray-400 hover:text-indigo-600 transition p-1.5 rounded-md hover:bg-indigo-50"
                  title="Edit Plan">
                  <i class="fi fi-rr-edit text-lg"></i>
                </button>
                <button
                  class="delete-icon w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-red-500 opacity-0 group-hover:opacity-100 transition-all hover:bg-red-100 hover:scale-110">
                  <i class="fi fi-rr-trash text-sm"></i>
                </button>
              </div>

              <div class="flex items-center gap-2 mb-4">
                <div class="w-10 h-10 rounded-lg {{ $gradientColor }} flex items-center justify-center shadow-md">
                  <i class="fi fi-ss-star text-white text-lg"></i>
                </div>
                @if($plan->for_contractor)
                  <div class="text-xs font-semibold bg-blue-100 text-blue-800 px-2 py-1 rounded inline-flex items-center">
                    <i class="fi fi-ss-building mr-1"></i> Contractor
                  </div>
                @else
                  <div
                    class="text-xs font-semibold bg-purple-100 text-purple-800 px-2 py-1 rounded inline-flex items-center">
                    <i class="fi fi-ss-home mr-1"></i> Owner
                  </div>
                @endif
              </div>

              <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ $plan->name }}</h3>
              <p class="text-sm text-gray-500 mb-4">{{ ucfirst($plan->billing_cycle) }} Charge</p>

              <div class="mb-6">
                <span class="text-5xl font-extrabold text-gray-800">₱ {{ number_format($plan->amount / 100, 2) }}</span>
              </div>

              <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Benefits:</h4>
                <ul class="space-y-2">
                  @foreach($benefits as $benefit)
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                      <i class="fi fi-ss-check-circle text-green-500 text-base mt-0.5"></i>
                      <span>{{ $benefit }}</span>
                    </li>
                  @endforeach
                </ul>
              </div>

              <div
                class="absolute bottom-0 left-0 w-full h-1 {{ $gradientColor }} transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left">
              </div>
            </div>
          @empty
            <div class="col-span-3 text-center py-10 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-300">
              <i class="fi fi-rr-box-open text-4xl text-gray-400 mb-3"></i>
              <h3 class="text-lg font-semibold text-gray-600">No active plans</h3>
              <p class="text-sm text-gray-500">Click "Add Subscription" to create one.</p>
            </div>
          @endforelse
        </div>
      </section>
      <!-- End Subscriptions Section -->

      <!-- Subscription Statistics Section -->
      <section class="px-8 pb-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <!-- Total Subscriptions Card -->
          <div
            class="stats-card bg-white rounded-xl shadow-md p-6 transition-all hover:shadow-xl hover:-translate-y-1 cursor-pointer border-l-4 border-indigo-500 group relative overflow-hidden">
            <div
              class="absolute top-0 right-0 w-20 h-20 bg-indigo-50 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150">
            </div>
            <div class="relative z-10">
              <p class="text-sm text-gray-600 font-medium mb-2">Total Subscriptions</p>
              <div class="flex items-baseline gap-2 mb-1">
                <span class="text-4xl font-extrabold text-gray-800">{{ $stats['total'] ?? 0 }}</span>
                <span class="text-lg text-gray-400 font-medium">/300</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                <div class="bg-indigo-500 h-2 rounded-full transition-all duration-500 group-hover:bg-indigo-600"
                  style="width: {{ min(($stats['total'] ?? 0) / 300 * 100, 100) }}%"></div>
              </div>
            </div>
          </div>

          <!-- Total Revenue Card -->
          <div
            class="stats-card bg-white rounded-xl shadow-md p-6 transition-all hover:shadow-xl hover:-translate-y-1 cursor-pointer border-l-4 border-green-500 group relative overflow-hidden">
            <div
              class="absolute top-0 right-0 w-20 h-20 bg-green-50 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150">
            </div>
            <div class="relative z-10">
              <p class="text-sm text-gray-600 font-medium mb-2">Total Revenue</p>
              <div class="flex items-baseline gap-1 mb-1">
                <span class="text-4xl font-extrabold text-gray-800">₱{{ $stats['revenue'] ?? '0.00' }}</span>
              </div>
              <div class="flex items-center gap-1 mt-2">
                <i class="fi fi-rr-arrow-trend-up text-green-500 text-sm"></i>
                <span class="text-xs text-green-600 font-semibold">+12.5% from last month</span>
              </div>
            </div>
          </div>

          <!-- Expiring Soon Card -->
          <div
            class="stats-card bg-white rounded-xl shadow-md p-6 transition-all hover:shadow-xl hover:-translate-y-1 cursor-pointer border-l-4 border-yellow-500 group relative overflow-hidden">
            <div
              class="absolute top-0 right-0 w-20 h-20 bg-yellow-50 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150">
            </div>
            <div class="relative z-10">
              <p class="text-sm text-gray-600 font-medium mb-2">Expiring Soon</p>
              <div class="flex items-baseline gap-1 mb-1">
                <span class="text-4xl font-extrabold text-gray-800">{{ $stats['expiring_soon'] ?? 0 }}</span>
              </div>
              <div class="flex items-center gap-1 mt-2">
                <i class="fi fi-rr-clock text-yellow-500 text-sm"></i>
                <span class="text-xs text-yellow-600 font-semibold">Within 7 days</span>
              </div>
            </div>
          </div>

          <!-- Expired Card -->
          <div
            class="stats-card bg-white rounded-xl shadow-md p-6 transition-all hover:shadow-xl hover:-translate-y-1 cursor-pointer border-l-4 border-red-500 group relative overflow-hidden">
            <div
              class="absolute top-0 right-0 w-20 h-20 bg-red-50 rounded-full -mr-10 -mt-10 transition-transform group-hover:scale-150">
            </div>
            <div class="relative z-10">
              <p class="text-sm text-gray-600 font-medium mb-2">Expired</p>
              <div class="flex items-baseline gap-1 mb-1">
                <span class="text-4xl font-extrabold text-gray-800">{{ $stats['expired'] ?? 0 }}</span>
              </div>
              <div class="flex items-center gap-1 mt-2">
                <i class="fi fi-rr-exclamation text-red-500 text-sm"></i>
                <span class="text-xs text-red-600 font-semibold">Needs renewal</span>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- End Subscription Statistics Section -->

      <!-- Subscriptions Management Table -->
      <section class="px-8 pb-12">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <!-- Tabs Header -->
          <div class="border-b border-gray-200 overflow-x-auto">
            <div class="flex px-2 sm:px-6 whitespace-nowrap">
              <button id="tabActiveSubscriptions"
                class="subscription-tab active px-4 py-3 text-sm font-semibold border-b-2 border-orange-500 text-orange-600 transition-all">
                <i class="fi fi-rr-calendar-check mr-2"></i>
                ACTIVE SUBSCRIPTIONS
              </button>
              <button id="tabExpiredSubscriptions"
                class="subscription-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-orange-600 hover:border-orange-300 transition-all">
                <i class="fi fi-rr-calendar-clock mr-2"></i>
                EXPIRED SUBSCRIPTIONS
              </button>
              <button id="tabCancelledSubscriptions"
                class="subscription-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-orange-600 hover:border-orange-300 transition-all">
                <i class="fi fi-rr-cross-circle mr-2"></i>
                CANCELLED SUBSCRIPTIONS
              </button>
            </div>
          </div>

          <!-- Filters Bar -->
          <div class="bg-gray-50 border-b border-gray-200 p-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
              <div
                class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white">
                <i class="fi fi-rr-filter text-gray-500"></i>
                <span>Filter By</span>
              </div>

              <button
                class="filter-dropdown relative flex items-center gap-2 bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400 transition">
                <span>Plan Type</span>
                <i class="fi fi-rr-angle-small-down text-gray-500"></i>
              </button>
            </div>

            <button
              class="reset-filter-btn flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
              <i class="fi fi-rr-rotate-left"></i>
              <span>Reset Filter</span>
            </button>
          </div>

          <!-- Active Subscriptions Table -->
          <div id="activeSubscriptionsTable" class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Name/Project</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type /
                    Plan</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Expiry
                  </th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions
                  </th>
                </tr>
              </thead>
              <tbody id="activeSubscriptionsTbody" class="divide-y divide-gray-200">
                @forelse($activeSubscriptions as $sub)
                  @php
                    $isBoost = $sub->plan_key === 'boost' || stripos($sub->plan_key, 'boost') !== false;
                    $personName = $isBoost ? ($sub->first_name . ' ' . $sub->last_name . ' (' . $sub->project_title . ')') : ($sub->company_name ?? 'Contractor');
                    $initials = substr(preg_replace('/[^A-Za-z0-9]/', '', $personName), 0, 2);
                    $planName = $sub->plan_name ?? ucwords(str_replace('_', ' ', $sub->plan_key));

                    // Simple badge color generator
                    $badgeColor = 'bg-blue-100 text-blue-800';
                    if (stripos($planName, 'gold') !== false)
                      $badgeColor = 'bg-yellow-100 text-yellow-800';
                    elseif (stripos($planName, 'silver') !== false)
                      $badgeColor = 'bg-gray-200 text-gray-800';
                    elseif (stripos($planName, 'bronze') !== false)
                      $badgeColor = 'bg-orange-100 text-orange-800';
                  @endphp
                  <tr class="hover:bg-gray-50 transition duration-150 ease-in-out group">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                      {{ str_pad($sub->platform_payment_id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-3">
                        <div
                          class="w-10 h-10 rounded-full {{ $isBoost ? 'bg-gradient-to-br from-green-400 to-green-600' : 'bg-gradient-to-br from-blue-400 to-blue-600' }} flex items-center justify-center text-white font-bold shadow-md uppercase">
                          {{ $initials ?: 'U' }}
                        </div>
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs">{{ $personName }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badgeColor }}">
                        {{ $planName }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ \Carbon\Carbon::parse($sub->transaction_date)->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ $sub->expiration_date ? \Carbon\Carbon::parse($sub->expiration_date)->format('Y-m-d') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <div class="flex items-center gap-2 justify-center">
                        <button
                          class="view-subscription-btn w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 hover:bg-blue-100 text-blue-600 transition-all hover:shadow-md"
                          title="View Details" data-id="{{ $sub->platform_payment_id }}"
                          data-user="{{ $isBoost ? ($sub->first_name . ' ' . $sub->last_name) : ($sub->company_name ?? 'Contractor') }}"
                          data-project="{{ $isBoost ? ($sub->project_title ?? '') : '' }}" data-plan="{{ $planName }}"
                          data-plan-key="{{ $sub->plan_key }}" data-amount="{{ number_format($sub->amount, 2) }}"
                          data-date="{{ \Carbon\Carbon::parse($sub->transaction_date)->format('Y-m-d') }}"
                          data-expiry="{{ $sub->expiration_date ? \Carbon\Carbon::parse($sub->expiration_date)->format('Y-m-d') : 'N/A' }}"
                          data-type="{{ $isBoost ? 'Boost' : 'Subscription' }}"
                          data-billing="{{ $sub->billing_cycle ?? 'N/A' }}"
                          data-txn="{{ $sub->transaction_number ?? 'N/A' }}"
                          data-duration="{{ $sub->duration_days ?? '' }}" data-status="active">
                          <i class="fi fi-rr-eye text-base"></i>
                        </button>
                        <button
                          class="deactivate-subscription-btn w-10 h-10 flex items-center justify-center rounded-full bg-orange-50 hover:bg-orange-100 text-orange-500 transition-all hover:shadow-md"
                          title="Deactivate" data-id="{{ $sub->platform_payment_id }}" data-name="{{ $personName }}">
                          <i class="fi fi-rr-ban text-base"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                      <i class="fi fi-rr-box-open text-4xl mb-3 block text-gray-300"></i>
                      <p class="text-lg">No active subscriptions found.</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <!-- Expired Subscriptions Table -->
          <div id="expiredSubscriptionsTable" class="overflow-x-auto hidden">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Name/Project</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type /
                    Plan</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Expiry
                  </th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions
                  </th>
                </tr>
              </thead>
              <tbody id="expiredSubscriptionsTbody" class="divide-y divide-gray-200">
                @forelse($expiredSubscriptions as $sub)
                  @php
                    $isBoost = $sub->plan_key === 'boost' || stripos($sub->plan_key, 'boost') !== false;
                    $personName = $isBoost ? ($sub->first_name . ' ' . $sub->last_name . ' (' . $sub->project_title . ')') : ($sub->company_name ?? 'Contractor');
                    $initials = substr(preg_replace('/[^A-Za-z0-9]/', '', $personName), 0, 2);
                    $planName = $sub->plan_name ?? ucwords(str_replace('_', ' ', $sub->plan_key));
                  @endphp
                  <tr class="hover:bg-gray-50 transition duration-150 ease-in-out group bg-red-50/30">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                      {{ str_pad($sub->platform_payment_id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-3">
                        <div
                          class="w-10 h-10 rounded-full {{ $isBoost ? 'bg-gradient-to-br from-green-400 to-green-600' : 'bg-gradient-to-br from-blue-400 to-blue-600' }} flex items-center justify-center text-white font-bold shadow-md uppercase">
                          {{ $initials ?: 'U' }}
                        </div>
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs">{{ $personName }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                        {{ $planName }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ \Carbon\Carbon::parse($sub->transaction_date)->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-semibold">
                      {{ $sub->expiration_date ? \Carbon\Carbon::parse($sub->expiration_date)->format('Y-m-d') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <div class="flex items-center gap-2 justify-center">
                        <button
                          class="view-subscription-btn w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 hover:bg-blue-100 text-blue-600 transition-all hover:shadow-md"
                          title="View Details" data-id="{{ $sub->platform_payment_id }}"
                          data-user="{{ $isBoost ? ($sub->first_name . ' ' . $sub->last_name) : ($sub->company_name ?? 'Contractor') }}"
                          data-project="{{ $isBoost ? ($sub->project_title ?? '') : '' }}" data-plan="{{ $planName }}"
                          data-plan-key="{{ $sub->plan_key }}" data-amount="{{ number_format($sub->amount, 2) }}"
                          data-date="{{ \Carbon\Carbon::parse($sub->transaction_date)->format('Y-m-d') }}"
                          data-expiry="{{ $sub->expiration_date ? \Carbon\Carbon::parse($sub->expiration_date)->format('Y-m-d') : 'N/A' }}"
                          data-type="{{ $isBoost ? 'Boost' : 'Subscription' }}"
                          data-billing="{{ $sub->billing_cycle ?? 'N/A' }}"
                          data-txn="{{ $sub->transaction_number ?? 'N/A' }}"
                          data-duration="{{ $sub->duration_days ?? '' }}">
                          <i class="fi fi-rr-eye text-base"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                      <i class="fi fi-rr-box-open text-4xl mb-3 block text-gray-300"></i>
                      <p class="text-lg">No expired subscriptions found.</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <!-- Cancelled Subscriptions Table -->
          <div id="cancelledSubscriptionsTable" class="overflow-x-auto hidden">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Name/Project</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type /
                    Plan</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions
                  </th>
                </tr>
              </thead>
              <tbody id="cancelledSubscriptionsTbody" class="divide-y divide-gray-200">
                @forelse($cancelledSubscriptions as $sub)
                  @php
                    $isBoost = $sub->plan_key === 'boost' || stripos($sub->plan_key, 'boost') !== false;
                    $personName = $isBoost ? ($sub->first_name . ' ' . $sub->last_name . ' (' . $sub->project_title . ')') : ($sub->company_name ?? 'Contractor');
                    $initials = substr(preg_replace('/[^A-Za-z0-9]/', '', $personName), 0, 2);
                    $planName = $sub->plan_name ?? ucwords(str_replace('_', ' ', $sub->plan_key));
                  @endphp
                  <tr class="hover:bg-gray-50 transition duration-150 ease-in-out group bg-orange-50/20">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                      {{ str_pad($sub->platform_payment_id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center gap-3">
                        <div
                          class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center text-white font-bold shadow-md uppercase">
                          {{ $initials ?: 'U' }}
                        </div>
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs">{{ $personName }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                        {{ $planName }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ \Carbon\Carbon::parse($sub->transaction_date)->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                      <div class="flex items-center gap-2 justify-center">
                        <button
                          class="reactivate-subscription-btn w-10 h-10 flex items-center justify-center rounded-full bg-green-50 hover:bg-green-100 text-green-600 transition-all hover:shadow-md"
                          title="Reactivate" data-id="{{ $sub->platform_payment_id }}" data-name="{{ $personName }}">
                          <i class="fi fi-rr-undo text-base"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                      <i class="fi fi-rr-box-open text-4xl mb-3 block text-gray-300"></i>
                      <p class="text-lg">No cancelled subscriptions found.</p>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </section>
      <!-- End Subscriptions Management Table -->



    </main>

    <!-- Add Subscription Plan Modal -->
    <div id="addSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-300 scale-95 opacity-0 add-subscription-panel">
        <!-- Header -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4 flex items-center justify-between">
          <h2 class="text-xl font-bold text-white">Add Subscription Plan</h2>
          <button id="closeAddSubscriptionBtn"
            class="text-white hover:text-gray-200 transition p-1 rounded-lg hover:bg-white/10">
            <i class="fi fi-rr-cross text-2xl"></i>
          </button>
        </div>

        <!-- Body -->
        <form id="addSubscriptionForm" class="p-6 space-y-5">
          <!-- Subscription Name -->
          <div>
            <label for="subscriptionName" class="block text-sm font-semibold text-gray-800 mb-2">Subscription Name <span
                class="text-red-500">*</span></label>
            <input type="text" id="subscriptionName" name="subscription_name"
              class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition placeholder-gray-400"
              placeholder="Enter subscription name" required>
            <p id="subscriptionNameError" class="mt-1 text-xs text-red-600 hidden">Subscription name is required.</p>
          </div>

          <!-- Benefits -->
          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-2">Benefits</label>
            <div id="benefitsContainer" class="space-y-2">
              <div class="flex items-center gap-2 benefit-item">
                <input type="checkbox"
                  class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 benefit-checkbox"
                  checked>
                <input type="text"
                  class="flex-1 px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition text-sm benefit-input"
                  placeholder="Enter benefit">
                <button type="button" class="text-red-500 hover:text-red-700 transition p-1 remove-benefit-btn hidden">
                  <i class="fi fi-rr-cross-small text-xl"></i>
                </button>
              </div>
            </div>
            <button type="button" id="addBenefitBtn"
              class="mt-3 text-orange-600 hover:text-orange-700 text-sm font-semibold flex items-center gap-1 transition">
              <span>+</span> Add another
            </button>
          </div>

          <!-- Price -->
          <div>
            <label for="subscriptionPrice" class="block text-sm font-semibold text-gray-800 mb-2">Price <span
                class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">₱</span>
              <input type="number" id="subscriptionPrice" name="subscription_price"
                class="w-full pl-8 pr-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition placeholder-gray-400"
                placeholder="0.00" min="0" step="0.01" required>
            </div>
            <p id="subscriptionPriceError" class="mt-1 text-xs text-red-600 hidden">Price is required.</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <!-- Plan Key -->
            <div>
              <label for="planKey" class="block text-sm font-semibold text-gray-800 mb-2">Plan Key <span
                  class="text-red-500">*</span></label>
              <input type="text" id="planKey" name="plan_key"
                class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition placeholder-gray-400"
                placeholder="e.g. basic, premium, one-time-boost" required>
              <p id="planKeyError" class="mt-1 text-xs text-red-600 hidden">Plan key is required.</p>
            </div>

            <!-- Target Audience -->
            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Target Audience <span
                  class="text-red-500">*</span></label>
              <div class="flex items-center gap-4 mt-2">
                <label class="inline-flex items-center cursor-pointer">
                  <input type="radio" name="for_contractor" value="1"
                    class="form-radio text-orange-500 focus:ring-orange-400 w-4 h-4" checked>
                  <span class="ml-2 text-sm text-gray-700">Contractors</span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                  <input type="radio" name="for_contractor" value="0"
                    class="form-radio text-orange-500 focus:ring-orange-400 w-4 h-4">
                  <span class="ml-2 text-sm text-gray-700">Property Owners</span>
                </label>
              </div>
            </div>

            <!-- Billing Cycle -->
            <div class="col-span-2 sm:col-span-1">
              <label for="billingCycle" class="block text-sm font-semibold text-gray-800 mb-2">Billing Cycle <span
                  class="text-red-500">*</span></label>
              <select id="billingCycle" name="billing_cycle"
                class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition bg-white"
                required>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
                <option value="quarterly">Quarterly</option>
                <option value="one-time">One-time</option>
              </select>
            </div>

            <!-- Duration Days (hidden by default) -->
            <div class="col-span-2 sm:col-span-1 hidden" id="durationDaysContainer">
              <label for="durationDays" class="block text-sm font-semibold text-gray-800 mb-2">Duration (Days) <span
                  class="text-red-500">*</span></label>
              <input type="number" id="durationDays" name="duration_days"
                class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition placeholder-gray-400"
                placeholder="e.g. 30" min="1">
              <p id="durationDaysError" class="mt-1 text-xs text-red-600 hidden">Duration is required.</p>
            </div>
          </div>

          <!-- Footer Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4">
            <button type="button" id="cancelAddSubscriptionBtn"
              class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">
              Cancel
            </button>
            <button type="submit"
              class="px-8 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition font-semibold shadow-md hover:shadow-lg">
              Save
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Subscription Plan Modal -->
    <div id="editSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-300 scale-95 opacity-0 edit-subscription-panel">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4 flex items-center justify-between">
          <h2 class="text-xl font-bold text-white">Edit Subscription Plan</h2>
          <button id="closeEditSubscriptionBtn"
            class="text-white hover:text-gray-200 transition p-1 rounded-lg hover:bg-white/10">
            <i class="fi fi-rr-cross text-2xl"></i>
          </button>
        </div>

        <!-- Body -->
        <form id="editSubscriptionForm" class="p-6 space-y-5">
          <!-- Subscription Name -->
          <div>
            <label for="editSubscriptionName" class="block text-sm font-semibold text-gray-800 mb-2">Subscription Name
              <span class="text-red-500">*</span></label>
            <input type="text" id="editSubscriptionName" name="edit_subscription_name"
              class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-gray-400"
              placeholder="Enter subscription name" required>
            <p id="editSubscriptionNameError" class="mt-1 text-xs text-red-600 hidden">Subscription name is required.
            </p>
          </div>

          <!-- Benefits -->
          <div>
            <label class="block text-sm font-semibold text-gray-800 mb-2">Benefits</label>
            <div id="editBenefitsContainer" class="space-y-2">
              <!-- Dynamically injected benefit rows -->
            </div>
            <button type="button" id="editAddBenefitBtn"
              class="mt-3 text-indigo-600 hover:text-indigo-700 text-sm font-semibold flex items-center gap-1 transition">
              <span>+</span> Add another
            </button>
          </div>

          <!-- Price -->
          <div>
            <label for="editSubscriptionPrice" class="block text-sm font-semibold text-gray-800 mb-2">Price <span
                class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">₱</span>
              <input type="number" id="editSubscriptionPrice" name="edit_subscription_price"
                class="w-full pl-8 pr-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-gray-400"
                placeholder="0.00" min="0" step="0.01" required>
            </div>
            <p id="editSubscriptionPriceError" class="mt-1 text-xs text-red-600 hidden">Price is required.</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <!-- Billing Cycle -->
            <div class="col-span-2 sm:col-span-1">
              <label for="editBillingCycle" class="block text-sm font-semibold text-gray-800 mb-2">Billing Cycle <span
                  class="text-red-500">*</span></label>
              <select id="editBillingCycle" name="edit_billing_cycle"
                class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition bg-white"
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
              <label for="editDurationDays" class="block text-sm font-semibold text-gray-800 mb-2">Duration (Days) <span
                  class="text-red-500">*</span></label>
              <input type="number" id="editDurationDays" name="edit_duration_days"
                class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 transition placeholder-gray-400"
                placeholder="e.g. 30" min="1">
              <p id="editDurationDaysError" class="mt-1 text-xs text-red-600 hidden">Duration is required.</p>
            </div>
          </div>

          <!-- Footer Buttons -->
          <div class="flex items-center justify-end gap-3 pt-4">
            <button type="button" id="cancelEditSubscriptionBtn"
              class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">
              Cancel
            </button>
            <button type="submit"
              class="px-8 py-2.5 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white rounded-lg transition font-semibold shadow-md hover:shadow-lg">
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Subscription Plan Modal -->
    <div id="deleteSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
      <div
        class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden transform transition-all duration-300 scale-95 opacity-0 delete-subscription-panel">
        <!-- Header / Close -->
        <div class="flex items-start justify-end p-3">
          <button id="closeDeleteSubscriptionBtn"
            class="text-gray-400 hover:text-gray-600 rounded-lg p-1 hover:bg-gray-100 transition">
            <i class="fi fi-rr-cross text-xl"></i>
          </button>
        </div>
        <!-- Body -->
        <div class="px-6 pb-6 -mt-4 space-y-5">
          <div
            class="w-14 h-14 rounded-full flex items-center justify-center bg-gradient-to-br from-red-100 to-red-200 relative overflow-hidden shadow-inner">
            <div class="absolute inset-0 animate-pulse bg-red-50/40"></div>
            <i class="fi fi-rr-trash text-2xl text-red-600"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900">Delete Subscription Plan?</h3>
          <p class="text-gray-600 text-sm leading-relaxed">Are you sure you want to delete the <span id="deletePlanName"
              class="font-semibold text-gray-800">selected plan</span>?<br>This action cannot be undone.</p>

          <div class="space-y-2 text-left">
            <label for="deleteSubscriptionReason"
              class="text-sm font-semibold text-gray-800 flex items-center gap-1">Reason
              <span class="text-red-500">*</span></label>
            <input type="text" id="deleteSubscriptionReason"
              class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition text-sm"
              placeholder="Provide a generic reason" required>
            <p id="deleteSubscriptionReasonError" class="text-xs text-red-600 hidden">A reason is required.</p>
          </div>
          <!-- Footer -->
          <div class="pt-2 flex flex-col gap-3">
            <button id="confirmDeleteSubscriptionBtn"
              class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-red-600 to-red-600 hover:from-red-700 hover:to-red-700 text-white font-semibold shadow-md hover:shadow-lg transition">
              <i class="fi fi-rr-trash text-sm"></i>
              Delete
            </button>
            <button id="cancelDeleteSubscriptionBtn"
              class="w-full px-6 py-3 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Subscription (Row) Details Modal -->
    <div id="rowEditSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden transform transition-all duration-300 scale-95 opacity-0 row-edit-subscription-panel">
        <!-- Header -->
        <div
          class="px-6 pt-5 pb-4 border-b border-gray-200 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between">
          <h2 class="text-lg md:text-xl font-bold text-white flex items-center gap-2"><i
              class="fi fi-rr-edit"></i><span>Edit Subscription Details</span></h2>
          <button id="rowEditCloseBtn"
            class="text-white hover:text-gray-200 transition p-2 rounded-lg hover:bg-white/10"><i
              class="fi fi-rr-cross text-xl"></i></button>
        </div>

        <!-- Body -->
        <form id="rowEditSubscriptionForm" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Contractor -->
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Contractor</label>
              <input id="rowEditContractor" type="text"
                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 transition bg-white text-sm"
                readonly>
            </div>
            <!-- Status -->
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Status</label>
              <div id="rowEditStatusBadge"
                class="px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 border-2 border-gray-200 bg-gray-50 text-gray-700">
              </div>
            </div>
            <!-- Plan -->
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Plan</label>
              <select id="rowEditPlan"
                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 transition bg-white text-sm">
                <option value="Gold Tier">Gold Tier</option>
                <option value="Silver Tier">Silver Tier</option>
                <option value="Bronze Tier">Bronze Tier</option>
              </select>
            </div>
            <!-- Total Revenue -->
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Total Revenue</label>
              <input id="rowEditRevenue" type="text"
                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 transition bg-white text-sm"
                readonly>
            </div>
            <!-- Start Date -->
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Start Date</label>
              <input id="rowEditStartDate" type="date"
                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 transition bg-white text-sm">
            </div>
            <!-- Expiry Date -->
            <div class="space-y-2">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-600">Expiry Date</label>
              <input id="rowEditExpiryDate" type="date"
                class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-300 transition bg-white text-sm">
            </div>
          </div>

          <!-- Divider -->
          <div class="border-t border-dashed border-gray-200"></div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3">
            <button type="button" id="rowEditCancelBtn"
              class="px-6 py-2.5 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
            <button type="submit"
              class="px-8 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition flex items-center gap-2"><i
                class="fi fi-rr-check"></i>Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Deactivate Subscription Modal -->
    <div id="deactivateSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all duration-300 scale-95 opacity-0 deactivate-subscription-panel">
        <!-- Header -->
        <div
          class="px-6 pt-5 pb-4 border-b border-gray-200 bg-gradient-to-r from-red-600 to-orange-600 flex items-center justify-between">
          <h2 class="text-lg md:text-xl font-bold text-white flex items-center gap-2"><i
              class="fi fi-rr-ban"></i><span>Deactivate Subscription</span></h2>
          <button id="closeDeactivateSubscriptionBtn"
            class="text-white hover:text-gray-200 transition p-2 rounded-lg hover:bg-white/10"><i
              class="fi fi-rr-cross text-xl"></i></button>
        </div>
        <!-- Body -->
        <form id="deactivateSubscriptionForm" class="p-6 space-y-6">
          <div class="space-y-4">
            <p class="text-sm text-gray-600 leading-relaxed">You are about to deactivate the subscription for <span
                id="deactivateContractorName" class="font-semibold text-gray-800">Selected Contractor</span>. This will
              immediately revoke active benefits.</p>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
              <i class="fi fi-rr-info text-red-500 text-xl mt-0.5"></i>
              <div class="text-xs md:text-sm text-red-700">Deactivation cannot be undone automatically. A manual renewal
                or new purchase will be required to restore benefits.</div>
            </div>
            <div class="space-y-2">
              <label for="deactivateReason" class="text-sm font-semibold text-gray-800 flex items-center gap-1">Reason
                <span class="text-red-500">*</span></label>
              <textarea id="deactivateReason"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition text-sm resize-none min-h-[110px]"
                placeholder="Provide a clear reason (e.g. policy violation, duplicate account)..."></textarea>
              <p id="deactivateReasonError" class="text-xs text-red-600 hidden">A reason is required.</p>
            </div>
          </div>
          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" id="cancelDeactivateSubscriptionBtn"
              class="px-6 py-2.5 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
            <button type="submit" id="confirmDeactivateSubscriptionBtn"
              class="px-8 py-2.5 rounded-lg bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white font-semibold shadow-md hover:shadow-lg transition flex items-center gap-2"><i
                class="fi fi-rr-ban"></i>Deactivate</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Reactivate Subscription Modal -->
    <div id="reactivateSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all duration-300 scale-95 opacity-0 reactivate-subscription-panel">
        <!-- Header -->
        <div
          class="px-6 pt-5 pb-4 border-b border-gray-200 bg-gradient-to-r from-emerald-600 to-green-600 flex items-center justify-between">
          <h2 class="text-lg md:text-xl font-bold text-white flex items-center gap-2"><i
              class="fi fi-rr-undo"></i><span>Reactivate Subscription</span></h2>
          <button id="closeReactivateSubscriptionBtn"
            class="text-white hover:text-gray-200 transition p-2 rounded-lg hover:bg-white/10"><i
              class="fi fi-rr-cross text-xl"></i></button>
        </div>
        <!-- Body -->
        <div class="p-6 space-y-6">
          <div class="space-y-4">
            <p class="text-sm text-gray-600 leading-relaxed">You are about to reactivate the subscription for <span
                id="reactivateContractorName" class="font-semibold text-gray-800">Selected Contractor</span>. This will
              immediately restore all active benefits associated with their plan.</p>
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-start gap-3">
              <i class="fi fi-rr-info text-emerald-500 text-xl mt-0.5"></i>
              <div class="text-xs md:text-sm text-emerald-700">Reactivating will set the status back to 'Approved' and
                clear any previous deactivation records. The user will be notified of the restoration.</div>
            </div>
          </div>
          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" id="cancelReactivateSubscriptionBtn"
              class="px-6 py-2.5 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
            <button type="button" id="confirmReactivateSubscriptionBtn"
              class="px-8 py-2.5 rounded-lg bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold shadow-md hover:shadow-lg transition flex items-center gap-2"><i
                class="fi fi-rr-undo text-sm"></i>Reactivate Now</button>
          </div>
        </div>
      </div>
    </div>

    <!-- View Subscription Details Modal -->
    <div id="viewSubscriptionModal"
      class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden transform transition-all duration-300 scale-95 opacity-0 view-subscription-panel">
        <!-- Header -->
        <div
          class="px-6 pt-5 pb-4 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-between">
          <h2 class="text-lg md:text-xl font-bold text-white flex items-center gap-2"><i
              class="fi fi-rr-eye"></i><span>Subscription Details</span></h2>
          <button id="closeViewSubscriptionBtn"
            class="text-white hover:text-gray-200 transition p-2 rounded-lg hover:bg-white/10"><i
              class="fi fi-rr-cross text-xl"></i></button>
        </div>
        <!-- Body -->
        <div class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- ID -->
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Payment ID</label>
              <p id="viewSubId"
                class="text-sm font-semibold text-gray-800 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200">—
              </p>
            </div>
            <!-- Name -->
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Name</label>
              <p id="viewSubName"
                class="text-sm font-semibold text-gray-800 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200">—
              </p>
            </div>
            <!-- Project (shown only for boost/owner) -->
            <div class="space-y-1 hidden" id="viewSubProjectContainer">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Project</label>
              <p id="viewSubProject"
                class="text-sm font-semibold text-gray-800 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200">—
              </p>
            </div>
            <!-- Plan -->
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Plan</label>
              <p id="viewSubPlan"
                class="text-sm font-semibold text-gray-800 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200">—
              </p>
            </div>
            <!-- Plan Key -->
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Plan Key</label>
              <p id="viewSubPlanKey"
                class="text-sm font-semibold text-gray-800 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200">—
              </p>
            </div>
            <!-- Type -->
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Type</label>
              <p id="viewSubType"
                class="text-sm font-semibold text-gray-800 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200">—
              </p>
            </div>
            <!-- Billing Cycle -->
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Billing Cycle</label>
              <p id="viewSubBilling"
                class="text-sm font-semibold text-gray-800 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200">—
              </p>
            </div>
            <!-- Amount -->
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Amount Paid</label>
              <p id="viewSubAmount"
                class="text-sm font-bold text-emerald-700 bg-emerald-50 px-4 py-2.5 rounded-lg border border-emerald-200">
                —</p>
            </div>
            <!-- Transaction Number -->
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Transaction Number</label>
              <p id="viewSubTxn"
                class="text-sm font-semibold text-gray-800 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200 truncate"
                title="">—</p>
            </div>
            <!-- Transaction Date -->
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Transaction Date</label>
              <p id="viewSubDate"
                class="text-sm font-semibold text-gray-800 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200">—
              </p>
            </div>
            <!-- Expiry Date -->
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Expiry Date</label>
              <p id="viewSubExpiry"
                class="text-sm font-bold text-red-600 bg-red-50 px-4 py-2.5 rounded-lg border border-red-200">—</p>
            </div>
            <!-- Duration (shown only for one-time) -->
            <div class="space-y-1 hidden" id="viewSubDurationContainer">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Duration (Days)</label>
              <p id="viewSubDuration"
                class="text-sm font-semibold text-gray-800 bg-gray-50 px-4 py-2.5 rounded-lg border border-gray-200">—
              </p>
            </div>
          </div>

          <!-- Status Badge -->
          <div id="viewSubStatusBanner" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
            <i id="viewSubStatusIcon" class="fi fi-rr-calendar-clock text-red-500 text-xl mt-0.5"></i>
            <div id="viewSubStatusText" class="text-xs md:text-sm text-red-700">This subscription has
              <strong>expired</strong>. The user would need to purchase a new subscription or boost to restore benefits.
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" id="cancelViewSubscriptionBtn"
              class="px-8 py-2.5 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Close</button>
          </div>
        </div>
      </div>
    </div>

    <script src="{{ asset('js/admin/projectManagement/subscriptions.js') }}" defer></script>

</body>

</html>