<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/reportsAnalytics.css') }}">
  
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
                      <a href="{{ route('admin.analytics.projectPerformance') }}" class="submenu-nested-link">Project Performance Analytics</a>
                      <a href="{{ route('admin.analytics.bidCompletion') }}" class="submenu-nested-link">Bid Completion Analytics</a>
                      <a href="{{ route('admin.analytics.reports') }}" class="submenu-nested-link active">Reports and Analytics</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Reports and Analytics</h1>

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
        <!-- Export Reports Section -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-2xl font-bold text-gray-800 mb-1">Export Reports</h2>
              <p class="text-sm text-gray-500">Generate and download comprehensive reports</p>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-sm text-gray-600 font-medium">Period:</span>
              <select class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white">
                <option>Last 30 days</option>
                <option>Last 60 days</option>
                <option>Last 90 days</option>
                <option>This Quarter</option>
                <option>This Year</option>
                <option>Custom Range</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- PDF Report Card -->
            <div class="border-2 border-gray-200 rounded-2xl p-6 hover:border-red-300 hover:shadow-xl transition-all duration-300 group">
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-red-100 p-3 rounded-xl group-hover:bg-red-200 transition-colors duration-300">
                  <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-bold text-gray-800 text-lg">PDF Report</h3>
                  <p class="text-xs text-gray-500">Formatted document</p>
                </div>
              </div>

              <div class="space-y-2 mb-6">
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500" checked>
                  <span>Property Owner Data</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500" checked>
                  <span>Contractor Analytics</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                  <span>Project Statistics</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                  <span>Financial Summary</span>
                </label>
              </div>

              <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg export-btn" data-type="pdf">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export PDF</span>
              </button>
            </div>

            <!-- Excel Report Card -->
            <div class="border-2 border-gray-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-xl transition-all duration-300 group">
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-green-100 p-3 rounded-xl group-hover:bg-green-200 transition-colors duration-300">
                  <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-bold text-gray-800 text-lg">Excel Report</h3>
                  <p class="text-xs text-gray-500">Spreadsheet format</p>
                </div>
              </div>

              <div class="space-y-2 mb-6">
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500" checked>
                  <span>Raw Data Tables</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500" checked>
                  <span>Pivot Tables</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                  <span>Charts & Graphs</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                  <span>Calculated Fields</span>
                </label>
              </div>

              <button class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg export-btn" data-type="excel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export Excel</span>
              </button>
            </div>

            <!-- CSV Report Card -->
            <div class="border-2 border-gray-200 rounded-2xl p-6 hover:border-blue-300 hover:shadow-xl transition-all duration-300 group">
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-blue-100 p-3 rounded-xl group-hover:bg-blue-200 transition-colors duration-300">
                  <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4zm7 5a1 1 0 10-2 0v1H8a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-bold text-gray-800 text-lg">CSV Report</h3>
                  <p class="text-xs text-gray-500">Spreadsheet format</p>
                </div>
              </div>

              <div class="space-y-2 mb-6">
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                  <span>User Data Export</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                  <span>Transaction Records</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                  <span>Activity Logs</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                  <span>Performance Metrics</span>
                </label>
              </div>

              <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg export-btn" data-type="csv">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export CSV</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Detailed Report Configuration -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-1">Detailed Report Configuration</h2>
            <p class="text-sm text-gray-500">Customize your report with advanced filters and metrics</p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Report Filters Section -->
            <div>
              <h3 class="text-lg font-bold text-gray-800 mb-4">Report Filters</h3>
              
              <!-- Date Range -->
              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Date Range</label>
                <div class="grid grid-cols-2 gap-3">
                  <input type="date" class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full text-sm" placeholder="Start Date">
                  <input type="date" class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full text-sm" placeholder="End Date">
                </div>
              </div>

              <!-- User Type -->
              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">User Type</label>
                <select class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white w-full text-sm">
                  <option value="">Contractor Type</option>
                  <option value="property-owner">Property Owner</option>
                  <option value="contractor">Contractor</option>
                  <option value="premium">Premium Tier</option>
                  <option value="standard">Standard Tier</option>
                  <option value="all">All Users</option>
                </select>
              </div>

              <!-- Location -->
              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Location</label>
                <select class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white w-full text-sm">
                  <option value="">All Location</option>
                  <option value="tetuan">Tetuan</option>
                  <option value="tumaga">Tumaga</option>
                  <option value="sinunuc">Sinunuc</option>
                  <option value="malagutay">Malagutay</option>
                  <option value="baliwasan">Baliwasan</option>
                  <option value="upper-calarian">Upper Calarian</option>
                  <option value="others">Others</option>
                </select>
              </div>

              <!-- Project Status -->
              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Project Status</label>
                <select class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white w-full text-sm">
                  <option value="">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="completed">Completed</option>
                  <option value="pending">Pending</option>
                  <option value="under-review">Under Review</option>
                  <option value="cancelled">Cancelled</option>
                  <option value="on-hold">On Hold</option>
                </select>
              </div>
            </div>

            <!-- Include Metrics Section -->
            <div>
              <h3 class="text-lg font-bold text-gray-800 mb-4">Include Metrics</h3>
              
              <div class="space-y-3">
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" checked>
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">AI Recommendation Performance</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" checked>
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Bid Acceptance Rates</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Project Completion Times</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Payment Processing Data</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">User Engagement Metrics</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Location-based Analytics</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Milestone Verification Rates</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Contractor Rating Trends</span>
                  </div>
                </label>
              </div>
            </div>
          </div>

          <!-- Export Options -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Export Options</h3>
            <div class="flex items-center gap-4">
              <button class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg detailed-export-btn" data-type="pdf">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export PDF</span>
              </button>
              <button class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg detailed-export-btn" data-type="excel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export Excel</span>
              </button>
              <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg detailed-export-btn" data-type="csv">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export CSV</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Analytics Summary -->
  

  <script src="{{ asset('js/admin/home/reportsAnalytics.js') }}" defer></script>

</body>

</html>