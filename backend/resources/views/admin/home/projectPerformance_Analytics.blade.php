<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/projectPerformance_Analytics.css') }}">
  
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  

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
                  <a href="{{ route('admin.dashboard') }}" class="submenu-link">Dashboard</a>
                  <div class="submenu-nested">
                    <button class="submenu-link submenu-nested-btn">
                      <span>Analytics</span>
                      <span class="arrow-small">▼</span>
                    </button>
                    <div class="submenu-nested-content">
                      <a href="{{ route('admin.analytics') }}" class="submenu-nested-link">Project Analytics</a>
                      <a href="{{ route('admin.analytics.subscription') }}" class="submenu-nested-link">Subscription Analytics</a>
                      <a href="{{ route('admin.analytics.userActivity') }}" class="submenu-nested-link">User Activity Analytics</a>
                      <a href="{{ route('admin.analytics.projectPerformance') }}" class="submenu-nested-link active">Project Performance Analytics</a>
                      <a href="{{ route('admin.analytics.bidCompletion') }}" class="submenu-nested-link">Bid Completion Analytics</a>
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
            <a href="{{ route('admin.userManagement.verificationRequest') }}" class="submenu-link">Verification Request</a>
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
            <a href="{{ route('admin.globalManagement.postingManagement') }}" class="submenu-link">Posting Management</a>
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
            <a href="{{ route('admin.projectManagement.listOfprojects') }}" class="submenu-link">List of Projects</a>
            <a href="{{ route('admin.projectManagement.disputesReports') }}" class="submenu-link">Disputes/Reports</a>
            <a href="{{ route('admin.projectManagement.messages') }}" class="submenu-link">Messages</a>
            <a href="{{ route('admin.projectManagement.subscriptions') }}" class="submenu-link">Subscriptions & Boosts</a>
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
                        <i class="fi fi-br-settings-sliders"></i>
                        <span>Account settings</span>
                      </a>
                    </li>
                    <li>
                      <button id="logoutBtn" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 text-red-600">
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
      <header class="bg-white shadow-sm border-b border-gray-200 flex items-center justify-between px-8 py-4 sticky top-0 z-30">
        <h1 class="text-2xl font-semibold text-gray-800">Project Performance Analytics</h1>

        <div class="flex items-center gap-6">
          <div class="relative w-64" style="width: 600px;">
            <input 
              type="text" 
              placeholder="Search..." 
              class="border border-gray-300 rounded-lg px-4 py-2 pr-10 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full"
            >
            <i class="fi fi-rr-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          </div>


          <div class="relative">
            <button id="notificationBell" class="cursor-pointer w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
              <i class="fi fi-ss-bell-notification-social-media" style="font-size: 20px;"></i>
            </button>
            <span class="absolute -top-1 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>

            <!-- Notifications Dropdown -->
            <div id="notificationDropdown" class="absolute right-0 mt-3 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 hidden">
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
                      <p class="text-sm text-gray-800 truncate">Verification request approved for Cabonting Architects.</p>
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
                <a href="{{ route('admin.settings.notifications') }}" class="text-sm text-indigo-600 hover:text-indigo-700">Notification settings</a>
              </div>
            </div>
          </div>
        </div>
      </header>

      <div class="p-8 space-y-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total Projects Card -->
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
              <div class="text-white text-4xl font-bold mb-2 stat-number" data-target="147">0</div>
              <div class="flex items-center text-white text-sm">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-semibold">+12%</span>
                <span class="ml-1 opacity-80">from last month</span>
              </div>
            </div>
          </div>

          <!-- Completed Projects Card -->
          <div class="group relative bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-3">
                  <svg class="w-8 h-8 text-white transform group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="text-white text-sm font-medium mb-1 opacity-90">Completed</div>
              <div class="text-white text-4xl font-bold mb-2 stat-number" data-target="89">0</div>
              <div class="flex items-center text-white text-sm">
                <span class="font-semibold">60%</span>
                <span class="ml-1 opacity-80">completion rate</span>
              </div>
            </div>
          </div>

          <!-- Total Bids Card -->
          <div class="group relative bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-3">
                  <svg class="w-8 h-8 text-white transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="text-white text-sm font-medium mb-1 opacity-90">Total Bids</div>
              <div class="text-white text-4xl font-bold mb-2 stat-number" data-target="1234">0</div>
              <div class="flex items-center text-white text-sm">
                <span class="font-semibold">8.5</span>
                <span class="ml-1 opacity-80">bids/project</span>
              </div>
            </div>
          </div>

          <!-- Total Value Card -->
          <div class="group relative bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10">
              <div class="flex items-center justify-between mb-4">
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-3">
                  <svg class="w-8 h-8 text-white transform group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="text-white text-sm font-medium mb-1 opacity-90">Total Value</div>
              <div class="text-white text-4xl font-bold mb-2">₱<span class="stat-number" data-target="24.7">0</span>M</div>
              <div class="flex items-center text-white text-sm">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-semibold">+15%</span>
                <span class="ml-1 opacity-80">from Q3</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Project Completion Trends -->
          <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Project Completion Trends</h3>
                <p class="text-sm text-gray-500">Monthly completion statistics</p>
              </div>
              <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-md">
                Last 12 Months
              </div>
            </div>
            <div style="height: 350px;">
              <canvas id="completionTrendsChart"></canvas>
            </div>
          </div>

          <!-- Performance by Category -->
          <div class="bg-white rounded-2xl shadow-lg p-8 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Performance by Category</h3>
                <p class="text-sm text-gray-500">Project types distribution</p>
              </div>
            </div>
            <div style="height: 350px;">
              <canvas id="categoryPerformanceChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Project Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Average Duration -->
          <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-cyan-500">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-gradient-to-br from-cyan-400 to-cyan-500 text-white p-3 rounded-xl shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <span class="text-cyan-600 text-sm font-semibold bg-cyan-50 px-3 py-1 rounded-full">-8 days</span>
            </div>
            <h4 class="text-gray-600 text-sm font-medium mb-2">Average Duration</h4>
            <div class="text-3xl font-bold text-gray-800 mb-3"><span class="stat-number" data-target="45">0</span> days</div>
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <div class="bg-gradient-to-r from-cyan-400 to-cyan-600 h-2 rounded-full progress-bar" data-width="75" style="width: 0%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Improved from previous period</p>
          </div>

          <!-- Success Rate -->
          <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-emerald-500">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-gradient-to-br from-emerald-400 to-emerald-500 text-white p-3 rounded-xl shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <span class="text-emerald-600 text-sm font-semibold bg-emerald-50 px-3 py-1 rounded-full">+5%</span>
            </div>
            <h4 class="text-gray-600 text-sm font-medium mb-2">Success Rate</h4>
            <div class="text-3xl font-bold text-gray-800 mb-3"><span class="stat-number" data-target="92">0</span>%</div>
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full progress-bar" data-width="92" style="width: 0%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Projects completed successfully</p>
          </div>

          <!-- On-Time Delivery -->
          <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-amber-500">
            <div class="flex items-center justify-between mb-4">
              <div class="bg-gradient-to-br from-amber-400 to-amber-500 text-white p-3 rounded-xl shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
              <span class="text-amber-600 text-sm font-semibold bg-amber-50 px-3 py-1 rounded-full">+3%</span>
            </div>
            <h4 class="text-gray-600 text-sm font-medium mb-2">On-Time Delivery</h4>
            <div class="text-3xl font-bold text-gray-800 mb-3"><span class="stat-number" data-target="87">0</span>%</div>
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <div class="bg-gradient-to-r from-amber-400 to-amber-600 h-2 rounded-full progress-bar" data-width="87" style="width: 0%"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Delivered within timeline</p>
          </div>
        </div>

        <!-- Top Performers Table -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-8 py-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-2xl font-bold text-white mb-1">Top Performing Contractors</h3>
                <p class="text-indigo-100 text-sm">Based on project completion rate and quality</p>
              </div>
              <button class="bg-white text-indigo-600 px-5 py-2.5 rounded-lg font-semibold hover:bg-indigo-50 transition-colors duration-200 shadow-md hover:shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Report
              </button>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rank</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contractor</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Projects</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Success Rate</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Avg. Duration</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rating</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer">
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-yellow-400 to-yellow-500 text-white font-bold rounded-full shadow-md">
                      1
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold shadow">
                        JB
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Juan BuildCo</div>
                        <div class="text-sm text-gray-500">Premium Contractor</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                      24 completed
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div class="flex-1 bg-gray-200 rounded-full h-2 w-24">
                        <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full" style="width: 98%"></div>
                      </div>
                      <span class="text-sm font-semibold text-gray-700">98%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-gray-700 font-medium">42 days</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-1">
                      <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                      </svg>
                      <span class="text-sm font-bold text-gray-700">4.9</span>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer">
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-gray-300 to-gray-400 text-white font-bold rounded-full shadow-md">
                      2
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold shadow">
                        RC
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Reyes Construction</div>
                        <div class="text-sm text-gray-500">Standard Contractor</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                      19 completed
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div class="flex-1 bg-gray-200 rounded-full h-2 w-24">
                        <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full" style="width: 95%"></div>
                      </div>
                      <span class="text-sm font-semibold text-gray-700">95%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-gray-700 font-medium">48 days</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-1">
                      <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                      </svg>
                      <span class="text-sm font-bold text-gray-700">4.7</span>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer">
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-br from-orange-300 to-orange-400 text-white font-bold rounded-full shadow-md">
                      3
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center text-white font-semibold shadow">
                        SB
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Santos Builders</div>
                        <div class="text-sm text-gray-500">Premium Contractor</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                      17 completed
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div class="flex-1 bg-gray-200 rounded-full h-2 w-24">
                        <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full" style="width: 94%"></div>
                      </div>
                      <span class="text-sm font-semibold text-gray-700">94%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-gray-700 font-medium">43 days</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-1">
                      <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                      </svg>
                      <span class="text-sm font-bold text-gray-700">4.8</span>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer">
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-600 font-bold rounded-full">
                      4
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white font-semibold shadow">
                        DI
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Del Rosario Inc</div>
                        <div class="text-sm text-gray-500">Standard Contractor</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                      15 completed
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div class="flex-1 bg-gray-200 rounded-full h-2 w-24">
                        <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full" style="width: 93%"></div>
                      </div>
                      <span class="text-sm font-semibold text-gray-700">93%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-gray-700 font-medium">46 days</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-1">
                      <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                      </svg>
                      <span class="text-sm font-bold text-gray-700">4.6</span>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer">
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center w-8 h-8 bg-gray-200 text-gray-600 font-bold rounded-full">
                      5
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center text-white font-semibold shadow">
                        MC
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Manila Constructors</div>
                        <div class="text-sm text-gray-500">Premium Contractor</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                      14 completed
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div class="flex-1 bg-gray-200 rounded-full h-2 w-24">
                        <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full" style="width: 92%"></div>
                      </div>
                      <span class="text-sm font-semibold text-gray-700">92%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-gray-700 font-medium">44 days</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-1">
                      <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                      </svg>
                      <span class="text-sm font-bold text-gray-700">4.7</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  

  <script src="{{ asset('js/admin/home/projectPerformance_Analytics.js') }}" defer></script>

</body>

</html>