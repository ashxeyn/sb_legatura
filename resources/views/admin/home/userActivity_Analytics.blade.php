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
  <aside class="bg-white shadow-xl flex flex-col">
    <div class="flex justify-center items-center">
      <img src="{{ asset('img/logo.svg') }}" alt="Legatura Logo" class="logo-img">
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1">
      <div class="nav-group">
        <button class="nav-btn">
          <div class="flex items-center gap-3">
            <i class="fi fi-ss-home" style="font-size:20px;"></i>
            <span>Home</span>
          </div>
          <span class="arrow">▼</span>
        </button>
        <div class="nav-submenu">
          <a href="{{ route('admin.dashboard') }}" class="submenu-link">Dashboard</a>
          <div class="submenu-nested">
            <button class="submenu-link submenu-nested-btn">
              <span>Analytics</span><span class="arrow-small">▼</span>
            </button>
            <div class="submenu-nested-content">
              <a href="{{ route('admin.analytics') }}"                    class="submenu-nested-link">Project Analytics</a>
              <a href="{{ route('admin.analytics.subscription') }}"       class="submenu-nested-link">Subscription Analytics</a>
              <a href="{{ route('admin.analytics.userActivity') }}"       class="submenu-nested-link active">User Activity Analytics</a>
              <a href="{{ route('admin.analytics.projectPerformance') }}" class="submenu-nested-link">Project Performance Analytics</a>
              <a href="{{ route('admin.analytics.bidCompletion') }}"      class="submenu-nested-link">Bid Completion Analytics</a>
              <a href="{{ route('admin.analytics.reports') }}"            class="submenu-nested-link">Reports and Analytics</a>
            </div>
          </div>
        </div>
      </div>

      <div class="nav-group">
        <button class="nav-btn">
          <div class="flex items-center gap-3"><i class="fi fi-ss-users-alt" style="font-size:20px;"></i><span>User Management</span></div>
          <span class="arrow">▼</span>
        </button>
        <div class="nav-submenu">
          <a href="{{ route('admin.userManagement.propertyOwner') }}"       class="submenu-link">Property Owner</a>
          <a href="{{ route('admin.userManagement.contractor') }}"           class="submenu-link">Contractor</a>
          <a href="{{ route('admin.userManagement.verificationRequest') }}"  class="submenu-link">Verification Request</a>
          <a href="{{ route('admin.userManagement.suspendedAccounts') }}"    class="submenu-link">Suspended Accounts</a>
        </div>
      </div>

      <div class="nav-group">
        <button class="nav-btn">
          <div class="flex items-center gap-3"><i class="fi fi-ss-globe" style="font-size:20px;"></i><span>Global Management</span></div>
          <span class="arrow">▼</span>
        </button>
        <div class="nav-submenu">
          <a href="{{ route('admin.globalManagement.bidManagement') }}"    class="submenu-link">Bid Management</a>
          <a href="{{ route('admin.globalManagement.proofOfpayments') }}"  class="submenu-link">Proof of Payments</a>
          <a href="{{ route('admin.globalManagement.aiManagement') }}"     class="submenu-link">AI Management</a>
          <a href="{{ route('admin.globalManagement.postingManagement') }}" class="submenu-link">Posting Management</a>
        </div>
      </div>

      <div class="nav-group">
        <button class="nav-btn">
          <div class="flex items-center gap-3"><i class="fi fi-sr-master-plan" style="font-size:20px;"></i><span>Project Management</span></div>
          <span class="arrow">▼</span>
        </button>
        <div class="nav-submenu">
          <a href="{{ route('admin.projectManagement.listOfProjects') }}"   class="submenu-link">List of Projects</a>
          <a href="{{ route('admin.projectManagement.disputesReports') }}"  class="submenu-link">Disputes/Reports</a>
          <a href="{{ route('admin.projectManagement.messages') }}"         class="submenu-link">Messages</a>
          <a href="{{ route('admin.projectManagement.subscriptions') }}"    class="submenu-link">Subscriptions &amp; Boosts</a>
        </div>
      </div>

      <div class="nav-group">
        <button class="nav-btn">
          <div class="flex items-center gap-3"><i class="fi fi-br-settings-sliders" style="font-size:20px;"></i><span>Settings</span></div>
          <span class="arrow">▼</span>
        </button>
        <div class="nav-submenu">
          <a href="{{ route('admin.settings.notifications') }}" class="submenu-link">Notifications</a>
          <a href="{{ route('admin.settings.security') }}"      class="submenu-link">Security</a>
        </div>
      </div>
    </nav>

    <div class="mt-auto p-4">
      <div class="user-card flex items-center gap-3 p-3 rounded-lg shadow-md text-white">
        <div class="w-10 h-10 rounded-full bg-white text-indigo-900 flex items-center justify-center font-bold shadow flex-shrink-0">
          ES
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-semibold text-sm truncate">Emmanuelle Santos</div>
          <div class="text-xs opacity-80 truncate">santos@Legatura.com</div>
        </div>
        <div class="relative">
          <button id="userMenuBtn" class="text-white opacity-80 hover:opacity-100 transition text-2xl w-8 h-8 flex items-center justify-center rounded-full">⋮</button>
          <div id="userMenuDropdown" class="absolute right-0 bottom-full mb-2 w-44 bg-white text-gray-800 rounded-xl shadow-2xl border border-gray-200 hidden">
            <div class="px-4 py-3 border-b border-gray-100">
              <div class="text-sm font-semibold truncate">Emmanuelle Santos</div>
              <div class="text-xs text-gray-500 truncate">santos@Legatura.com</div>
            </div>
            <ul class="py-1">
              <li>
                <a href="{{ route('admin.settings.security') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50">
                  <i class="fi fi-br-settings-sliders"></i><span>Account settings</span>
                </a>
              </li>
              <li>
                <button id="logoutBtn" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 text-red-600">
                  <i class="fi fi-ss-exit"></i><span>Logout</span>
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </aside>

  {{-- ================================================================ --}}
  {{-- MAIN                                                              --}}
  {{-- ================================================================ --}}
  <main class="flex-1">

    {{-- Header --}}
    <header class="bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-8 py-4 sticky top-0 z-30">
      <h1 class="text-2xl font-semibold text-gray-800">User Activity Analytics</h1>
      <div class="flex items-center gap-6">
        <div class="relative" style="width:600px;">
          <input type="text" placeholder="Search..."
                 class="border border-gray-300 rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full">
          <i class="fi fi-rr-search absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>

        {{-- Notification bell --}}
        <div class="relative">
          <button id="notificationBell" class="cursor-pointer w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
            <i class="fi fi-ss-bell-notification-social-media" style="font-size:20px;"></i>
          </button>
          <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" id="notifCount">3</span>
          <div id="notificationDropdown" class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-800">Notifications</span>
              <button id="clearNotifications" class="text-xs text-indigo-600 hover:text-indigo-700">Clear all</button>
            </div>
            <ul class="max-h-80 overflow-y-auto" id="notificationList">
              <li class="px-4 py-3 hover:bg-gray-50 transition">
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fi fi-ss-bell"></i></div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 truncate">New bid submitted on "GreenBelt Building".</p>
                    <p class="text-xs text-gray-500">2 mins ago</p>
                  </div>
                  <span class="inline-block px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">New</span>
                </div>
              </li>
              <li class="px-4 py-3 hover:bg-gray-50 transition">
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center"><i class="fi fi-ss-check-circle"></i></div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 truncate">Verification request approved for Cabonting Architects.</p>
                    <p class="text-xs text-gray-500">1 hour ago</p>
                  </div>
                </div>
              </li>
              <li class="px-4 py-3 hover:bg-gray-50 transition">
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center"><i class="fi fi-ss-exclamation"></i></div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 truncate">High-risk flag: Duplex Housing requires review.</p>
                    <p class="text-xs text-gray-500">Yesterday</p>
                  </div>
                </div>
              </li>
            </ul>
            <div class="px-4 py-3 border-t border-gray-100">
              <a href="{{ route('admin.settings.notifications') }}" class="text-sm text-indigo-600 hover:text-indigo-700">Notification settings</a>
            </div>
          </div>
        </div>
      </div>
    </header>

    {{-- ============================================================ --}}
    {{-- PAGE BODY                                                      --}}
    {{-- ============================================================ --}}
    <div class="p-8">

      {{-- ── TOP STAT CARDS ── --}}
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        {{-- Total Users --}}
        <div class="group bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 relative overflow-hidden p-6">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          <div class="relative">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Total Users</p>
                <h3 class="text-4xl font-bold text-gray-800 group-hover:text-blue-600 transition-colors">
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
                <h3 class="text-4xl font-bold text-gray-800 group-hover:text-emerald-600 transition-colors">
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
                <h3 class="text-4xl font-bold text-gray-800 group-hover:text-orange-600 transition-colors">
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
                <h3 class="text-4xl font-bold text-gray-800 group-hover:text-red-600 transition-colors">
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
                <h3 class="text-2xl font-bold text-gray-800 group-hover:text-cyan-600 transition-colors">
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
                <h3 class="text-2xl font-bold text-gray-800 group-hover:text-red-600 transition-colors">
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
                <h3 class="text-2xl font-bold text-gray-800 group-hover:text-green-600 transition-colors">
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

      {{-- ── RECENT ACTIVITY TABLE ── --}}
      <div class="group bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 relative overflow-hidden">
        <div class="relative p-8">
          <div class="flex items-center justify-between mb-6">
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
          </div>

          <div class="bg-white rounded-xl shadow-inner overflow-hidden">
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

  // -- User Growth Line Chart --
  new Chart(document.getElementById('userGrowthChart'), {
    type: 'line',
    data: {
      labels: growthMonths,
      datasets: [
        {
          label: 'Property Owners',
          data: growthOwners,
          borderColor: '#10b981',
          backgroundColor: 'rgba(16,185,129,0.1)',
          tension: 0.4,
          fill: true,
          pointBackgroundColor: '#10b981',
          pointRadius: 4,
        },
        {
          label: 'Contractors',
          data: growthContractors,
          borderColor: '#f97316',
          backgroundColor: 'rgba(249,115,22,0.1)',
          tension: 0.4,
          fill: true,
          pointBackgroundColor: '#f97316',
          pointRadius: 4,
        },
        {
          label: 'Total',
          data: growthTotals,
          borderColor: '#6366f1',
          backgroundColor: 'rgba(99,102,241,0.07)',
          tension: 0.4,
          fill: false,
          pointBackgroundColor: '#6366f1',
          pointRadius: 4,
          borderDash: [5,4],
        },
      ],
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } },
        tooltip: { mode: 'index' },
      },
      scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
        x: { grid: { display: false } },
      },
    },
  });

  // -- User Distribution Doughnut --
  new Chart(document.getElementById('userDistributionChart'), {
    type: 'doughnut',
    data: {
      labels: distLabels,
      datasets: [{
        data: distValues,
        backgroundColor: ['#6366f1','#f97316','#10b981','#64748b'],
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 8,
      }],
    },
    options: {
      responsive: true,
      cutout: '65%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.label}: ${ctx.parsed.toLocaleString()} users`
          },
        },
      },
    },
  });
</script>

<script src="{{ asset('js/admin/home/userActivity_Analytics.js') }}" defer></script>
</body>
</html>