<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/projectAnalytics.css') }}">
  
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
                      <a href="{{ route('admin.analytics') }}" class="submenu-nested-link active">Project Analytics</a>
                      <a href="{{ route('admin.analytics.subscription') }}" class="submenu-nested-link">Subscription Analytics</a>
                      <a href="{{ route('admin.analytics.userActivity') }}" class="submenu-nested-link">User Activity Analytics</a>
                      <a href="{{ route('admin.analytics.projectPerformance') }}" class="submenu-nested-link">Project Performance Analytics</a>
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
            <a href="{{ route('admin.projectManagement.listOfProjects') }}" class="submenu-link">List of Projects</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Project Analytics</h1>

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

      <div class="analytics-container">
        <!-- Projects Analytics Card -->
        <div class="analytics-card group bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden">
          <!-- Animated background overlay -->
          <div class="absolute inset-0 bg-gradient-to-r from-blue-400/5 to-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          
          <div class="relative">
            <div class="flex items-center justify-between mb-6">
              <h2 class="analytics-card-title text-2xl font-bold text-gray-800 flex items-center gap-3">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
                <span>Projects</span>
              </h2>
              <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold group-hover:scale-105 transition-transform">Overview</span>
            </div>
          
            <div class="projects-analytics-content">
            <!-- Donut Chart -->
            <div class="projects-chart-wrapper">
              <canvas id="projectsDonutChart" 
                data-labels='{{ json_encode(array_column($projectsAnalytics["data"], "label")) }}'
                data-values='{{ json_encode(array_column($projectsAnalytics["data"], "count")) }}'>
              </canvas>
            </div>

            <!-- Legend -->
            <div class="projects-legend space-y-2">
              @foreach($projectsAnalytics['data'] as $index => $item)
              <div class="legend-item group/item cursor-pointer hover:bg-blue-50 p-3 rounded-lg transition-all duration-300 hover:scale-105 hover:shadow-md" data-index="{{ $index }}">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="legend-dot w-4 h-4 rounded-full shadow-md group-hover/item:scale-125 transition-transform duration-300" data-color="{{ $index }}"></div>
                    <span class="legend-label text-gray-700 font-medium group-hover/item:text-blue-600 transition-colors">{{ $item['label'] }}</span>
                  </div>
                  <span class="legend-count text-lg font-bold text-gray-800 bg-white px-3 py-1 rounded-full shadow-sm group-hover/item:bg-blue-100 group-hover/item:text-blue-700 transition-all">{{ $item['count'] }}</span>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          </div>
        </div>

        <!-- Project Success Rate Card -->
        <div class="analytics-card group bg-gradient-to-br from-white to-purple-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden">
          <!-- Animated background overlay -->
          <div class="absolute inset-0 bg-gradient-to-r from-purple-400/5 to-pink-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          
          <div class="relative">
            <div class="mb-6">
              <div class="flex items-center gap-3 mb-2">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <h2 class="analytics-card-title text-2xl font-bold text-gray-800">Project Success Rate</h2>
              </div>
              <p class="analytics-card-subtitle text-sm text-gray-600 ml-14 flex items-center gap-2">
                <span class="inline-block w-1.5 h-1.5 bg-purple-400 rounded-full"></span>
                Completion rate by project type
              </p>
            </div>
          
            <div class="success-rate-content">
            <!-- Pie Chart -->
            <div class="success-rate-chart-wrapper">
              <canvas id="projectSuccessRateChart" 
                data-labels='{{ json_encode(array_column($projectSuccessRate["data"], "label")) }}'
                data-values='{{ json_encode(array_column($projectSuccessRate["data"], "count")) }}'
                data-colors='{{ json_encode(array_column($projectSuccessRate["data"], "color")) }}'>
              </canvas>
            </div>

            <!-- Legend with Lines -->
            <div class="success-rate-legend space-y-3">
              @foreach($projectSuccessRate['data'] as $index => $item)
              <div class="success-rate-legend-item group/rate cursor-pointer hover:bg-purple-50 p-3 rounded-lg transition-all duration-300 hover:scale-105 hover:shadow-md" data-index="{{ $index }}">
                <div class="flex items-center gap-3">
                  <div class="success-rate-legend-line h-1 rounded-full shadow-md group-hover/rate:shadow-lg transition-shadow duration-300" style="background: {{ $item['color'] }}; width: 40px;"></div>
                  <span class="success-rate-legend-label text-gray-700 font-medium group-hover/rate:text-purple-600 transition-colors flex-1">{{ $item['label'] }}</span>
                  <svg class="w-5 h-5 text-gray-400 group-hover/rate:text-purple-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          </div>
        </div>

        <!-- Projects Timeline Chart -->
        <div class="analytics-card projects-timeline-card group bg-gradient-to-br from-white to-emerald-50 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-1 relative overflow-hidden">
          <!-- Animated background overlay -->
          <div class="absolute inset-0 bg-gradient-to-r from-emerald-400/5 to-teal-400/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
          
          <div class="relative">
          <div class="timeline-header flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-2.5 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                </svg>
              </div>
              <h2 class="analytics-card-title text-2xl font-bold text-gray-800">Projects Timeline</h2>
            </div>
            <div class="timeline-date-picker-wrapper">
              <div class="timeline-date-picker bg-white shadow-md hover:shadow-lg transition-shadow duration-300 rounded-lg px-4 py-2 flex items-center gap-3 cursor-pointer border border-gray-200 hover:border-emerald-300">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="timeline-date-range font-semibold text-gray-700">{{ $projectsTimeline['dateRange'] }}</span>
                <button class="timeline-dropdown-btn hover:scale-110 transition-transform" id="timelineDropdownBtn">
                  <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                    <path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
              </div>
              <div class="timeline-dropdown-menu shadow-xl" id="timelineDropdownMenu">
                <button class="timeline-dropdown-item hover:bg-emerald-50 transition-colors" data-range="last3months">Last 3 Months</button>
                <button class="timeline-dropdown-item active bg-emerald-100 text-emerald-700" data-range="last6months">Last 6 Months</button>
                <button class="timeline-dropdown-item hover:bg-emerald-50 transition-colors" data-range="thisyear">This Year</button>
                <button class="timeline-dropdown-item hover:bg-emerald-50 transition-colors" data-range="lastyear">Last Year</button>
              </div>
            </div>
          </div>

          <!-- Chart Legend -->
          <div class="timeline-legend flex gap-4 mb-6">
            <div class="timeline-legend-item group/legend cursor-pointer bg-white hover:bg-orange-50 px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2" data-dataset="0">
              <span class="timeline-legend-dot w-3 h-3 rounded-full shadow-md group-hover/legend:scale-125 transition-transform" style="background: #fb923c;"></span>
              <span class="timeline-legend-label text-sm font-semibold text-gray-700 group-hover/legend:text-orange-600 transition-colors">New Projects</span>
            </div>
            <div class="timeline-legend-item group/legend cursor-pointer bg-white hover:bg-indigo-50 px-4 py-2 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105 flex items-center gap-2" data-dataset="1">
              <span class="timeline-legend-dot w-3 h-3 rounded-full shadow-md group-hover/legend:scale-125 transition-transform" style="background: #818cf8;"></span>
              <span class="timeline-legend-label text-sm font-semibold text-gray-700 group-hover/legend:text-indigo-600 transition-colors">Completed Projects</span>
            </div>
          </div>

          <!-- Chart Container -->
          <div class="timeline-chart-container bg-white rounded-xl p-4 shadow-inner">
            <canvas id="projectsTimelineChart"
              data-months='{{ json_encode($projectsTimeline["months"]) }}'
              data-new='{{ json_encode($projectsTimeline["newProjects"]) }}'
              data-completed='{{ json_encode($projectsTimeline["completedProjects"]) }}'>
            </canvas>
          </div>
          </div>
        </div>
      </div>

      </main>
  </div>

  <script src="{{ asset('js/admin/home/analytics.js') }}" defer></script>

</body>

</html>