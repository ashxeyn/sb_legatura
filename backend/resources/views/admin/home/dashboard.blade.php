<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

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
        <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>

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

      <div class="dashboard-container">

        <!-- Mini Stats Container (floating row) -->
        <div class="mini-stats-container">
          <div class="mini-stats-card">
            <div class="mini-stats-row">
            <div class="mini-stat-card" data-months='{{ json_encode($projectsMetrics["months"]) }}' data-data='{{ json_encode($projectsMetrics["data"]) }}' data-label="{{ $projectsMetrics['label'] }}" data-total='{{ $projectsMetrics['total'] }}' data-pct='{{ $projectsMetrics['pctChange'] }}'>
              <div class="mini-stat-left">
                <canvas class="mini-chart" width="120" height="40"></canvas>
              </div>
              <div class="mini-stat-right">
                <div class="mini-number">{{ $projectsMetrics['total'] }}</div>
                <div class="mini-label">Total Projects</div>
              </div>
              <div class="mini-change">{{ $projectsMetrics['pctChange'] >= 0 ? '+' : '' }}{{ $projectsMetrics['pctChange'] }}%</div>
            </div>

            <div class="mini-stat-card" data-months='{{ json_encode($activeBidsMetrics["months"]) }}' data-data='{{ json_encode($activeBidsMetrics["data"]) }}' data-label="{{ $activeBidsMetrics['label'] }}" data-total='{{ $activeBidsMetrics['total'] }}' data-pct='{{ $activeBidsMetrics['pctChange'] }}'>
              <div class="mini-stat-left">
                <canvas class="mini-chart" width="120" height="40"></canvas>
              </div>
              <div class="mini-stat-right">
                <div class="mini-number">{{ $activeBidsMetrics['total'] }}</div>
                <div class="mini-label">Active Bids</div>
              </div>
              <div class="mini-change">{{ $activeBidsMetrics['pctChange'] >= 0 ? '+' : '' }}{{ $activeBidsMetrics['pctChange'] }}%</div>
            </div>

            <div class="mini-stat-card" data-months='{{ json_encode($revenueMetrics["months"]) }}' data-data='{{ json_encode($revenueMetrics["data"]) }}' data-label="{{ $revenueMetrics['label'] }}" data-total='{{ number_format($revenueMetrics['total'],2) }}' data-pct='{{ $revenueMetrics['pctChange'] }}'>
              <div class="mini-stat-left">
                <canvas class="mini-chart" width="120" height="40"></canvas>
              </div>
              <div class="mini-stat-right">
                <div class="mini-number">₱{{ number_format($revenueMetrics['total'],2) }}</div>
                <div class="mini-label">Revenue</div>
              </div>
              <div class="mini-change">{{ $revenueMetrics['pctChange'] >= 0 ? '+' : '' }}{{ $revenueMetrics['pctChange'] }}%</div>
            </div>
            </div>
          </div>
        </div>
        
        <!-- Active Users Section -->
        <div class="dashboard-section active-users-section" style="grid-column: 1 / -1;">
          <h2 class="section-title" style="margin-bottom: 1.5rem;">Active Users</h2>
          
          <div class="active-users-content">
            <!-- Left Side: Stats -->
            <div class="active-users-stats">
              <div class="stats-header">
                <span class="total-number">{{ $activeUsersData['total'] }}</span>
              </div>
              <div class="stats-list">
                <div class="stat-item">
                  <i class="fi fi-ss-users-alt" style="color: #f59e0b;"></i>
                  <div class="stat-text">
                    <span class="stat-value">{{ $activeUsersData['contractors'] }}</span>
                    <span class="stat-label">Contractors</span>
                  </div>
                </div>
                <div class="stat-item">
                  <i class="fi fi-ss-user" style="color: #f59e0b;"></i>
                  <div class="stat-text">
                    <span class="stat-value">{{ $activeUsersData['property_owners'] }}</span>
                    <span class="stat-label">Property Owners</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Side: Chart -->
            <div class="active-users-chart-container">
              <div class="chart-inner">
                <canvas id="activeUsersChart" data-months='{{ json_encode($activeUsersData["months"]) }}' data-data='{{ json_encode($activeUsersData["data"]) }}'></canvas>
              </div>
            </div>
          </div>

          <!-- Dashboard Statistics Cards (moved inside Active Users card) -->
          <div class="stats-wrapper" style="margin-top: 1.5rem;">
            <div class="dashboard-stats-container">
              <div class="stat-card" data-chart-type="total-users" data-months='{{ json_encode($totalUsersChartData["months"]) }}' data-data='{{ json_encode($totalUsersChartData["data"]) }}' data-label="{{ $totalUsersChartData["label"] }}" data-breakdown='{{ json_encode($totalUsersBreakdown) }}' style="cursor: pointer;">
                <div class="stat-card-header">
                  <i class="fi fi-ss-users-alt stat-card-icon"></i>
                </div>
                <div class="stat-card-body">
                  <span class="stat-card-number">{{ $dashboardStats['totalUsers'] }}</span>
                  <span class="stat-card-label">Total Users</span>
                </div>
              </div>

              <div class="stat-card" data-chart-type="new-users" data-months='{{ json_encode($newUsersChartData["months"]) }}' data-data='{{ json_encode($newUsersChartData["data"]) }}' data-label="{{ $newUsersChartData["label"] }}" data-breakdown='{{ json_encode($newUsersBreakdown) }}' style="cursor: pointer;">
                <div class="stat-card-header">
                  <i class="fi fi-ss-user-add stat-card-icon"></i>
                </div>
                <div class="stat-card-body">
                  <span class="stat-card-number">{{ $dashboardStats['newUsers'] }}</span>
                  <span class="stat-card-label">New Users</span>
                </div>
              </div>

              <div class="stat-card" data-chart-type="active-users" data-months='{{ json_encode($activeUsersChartData["months"]) }}' data-data='{{ json_encode($activeUsersChartData["data"]) }}' data-label="{{ $activeUsersChartData["label"] }}" data-breakdown='{{ json_encode($activeUsersBreakdown) }}' style="cursor: pointer;">
                <div class="stat-card-header">
                  <i class="fi fi-ss-users stat-card-icon"></i>
                </div>
                <div class="stat-card-body">
                  <span class="stat-card-number">{{ $dashboardStats['activeUsers'] }}</span>
                  <span class="stat-card-label">Active Users</span>
                </div>
              </div>

              <div class="stat-card" data-chart-type="pending-reviews" data-months='{{ json_encode($pendingReviewsChartData["months"]) }}' data-data='{{ json_encode($pendingReviewsChartData["data"]) }}' data-label="{{ $pendingReviewsChartData["label"] }}" data-breakdown='{{ json_encode($pendingReviewsBreakdown) }}' style="cursor: pointer;">
                <div class="stat-card-header">
                  <i class="fi fi-ss-check-circle stat-card-icon"></i>
                </div>
                <div class="stat-card-body">
                  <span class="stat-card-number">{{ $dashboardStats['pendingReviews'] }}</span>
                  <span class="stat-card-label">Pending Reviews</span>
                </div>
              </div>
            </div>
          </div>

          <!-- mini-stats removed from here and moved to a dedicated container below -->
        </div>

        

        <!-- Top Contractors Section -->

        <div class="dashboard-section hoverable-card">
          <div class="section-header">
            <h2 class="section-title">Top Contractors</h2>
            <button class="view-more-btn">View more</button>
          </div>

          <!-- Contractors List -->
          <div class="items-container">
            @forelse($topContractors as $contractor)
            <div class="item-card">
              <div class="item-left">
                <!-- Avatar -->
                <div class="item-avatar avatar-contractor">
                  @if($contractor->profile_pic)
                    <img src="{{ asset('storage/' . $contractor->profile_pic) }}" alt="{{ $contractor->company_name }}">
                  @else
                    {{ strtoupper(substr($contractor->company_name, 0, 1)) }}
                  @endif
                </div>

                <!-- Company Info -->
                <div class="item-info">
                  <h3 class="item-name">{{ $contractor->company_name }}</h3>
                  <p class="item-type">{{ $contractor->type_name }}</p>
                </div>
              </div>

              <!-- Completed Projects Badge -->
              <div class="item-right">
                <p class="item-count">{{ $contractor->completed_projects }}</p>
                <p class="item-label">Completed Projects</p>
              </div>
            </div>
            @empty
            <p class="empty-state">No contractors found</p>
            @endforelse
          </div>
        </div>

        <!-- Top Property Owners Section -->
        <div class="dashboard-section hoverable-card">
          <div class="section-header">
            <h2 class="section-title">Top Property Owners</h2>
            <button class="view-more-btn">View more</button>
          </div>

          <!-- Property Owners List -->
          <div class="items-container">
            @forelse($topPropertyOwners as $owner)
            <div class="item-card">
              <div class="item-left">
                <!-- Avatar -->
                <div class="item-avatar avatar-owner">
                  @if($owner->profile_pic)
                    <img src="{{ asset('storage/' . $owner->profile_pic) }}" alt="{{ $owner->first_name }} {{ $owner->last_name }}">
                  @else
                    {{ strtoupper(substr($owner->first_name, 0, 1)) }}
                  @endif
                </div>

                <!-- Owner Info -->
                <div class="item-info">
                  <h3 class="item-name">{{ $owner->first_name }} {{ $owner->last_name }}</h3>
                  <p class="item-type">Property Owner</p>
                </div>
              </div>

              <!-- Completed Projects Badge -->
              <div class="item-right">
                <p class="item-count">{{ $owner->completed_projects }}</p>
                <p class="item-label">Completed Projects</p>
              </div>
            </div>
            @empty
            <p class="empty-state">No property owners found</p>
            @endforelse
          </div>
        </div>

        <!-- Top Projects with Bids Section -->
        <div class="top-projects-section">
          <div class="top-projects-header">
            <h2 class="top-projects-title">Top Projects with Bids</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="top-projects-table">
              <thead>
                <tr>
                  <th>Project</th>
                  <th>Owner</th>
                  <th>Bids</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($topProjects as $project)
                <tr>
                  <td>
                    <div class="project-info">
                      <span class="project-avatar">
                        {{-- No project_image in schema, always fallback to first letter --}}
                        {{ strtoupper(substr($project->project_title, 0, 1)) }}
                      </span>
                      <span class="project-name">{{ $project->project_title }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="project-owner">{{ $project->first_name }} {{ $project->last_name }}</span>
                  </td>
                  <td>
                    <span class="project-bid-count">{{ $project->bid_count }}</span>
                  </td>
                  <td>
                    <span class="project-status {{ strtolower($project->project_status) }}">{{ $project->status_label }}</span>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="4" class="empty-state">No projects found</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <!-- Earnings Section -->
        <div class="earnings-section">
          <div class="earnings-header">
            <h2 class="earnings-title">Earnings</h2>
            <div class="earnings-date-picker-wrapper">
              <div class="earnings-date-picker">
                <span class="earnings-date-range">{{ $earningsMetrics['dateRange'] }}</span>
                <button class="earnings-dropdown-btn" id="earningsDropdownBtn">
                  <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                    <path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>
              </div>
              <div class="earnings-dropdown-menu" id="earningsDropdownMenu">
                <button class="earnings-dropdown-item" data-range="today">Today</button>
                <button class="earnings-dropdown-item" data-range="yesterday">Yesterday</button>
                <button class="earnings-dropdown-item" data-range="last7days">Last 7 Days</button>
                <button class="earnings-dropdown-item active" data-range="thismonth">This Month</button>
                <button class="earnings-dropdown-item" data-range="lastmonth">Last Month</button>
                <button class="earnings-dropdown-item" data-range="last3months">Last 3 Months</button>
                <button class="earnings-dropdown-item" data-range="thisyear">This Year</button>
              </div>
            </div>
          </div>
          <div class="earnings-chart-wrapper">
            <div class="earnings-total">
              <div class="earnings-total-label">Total Earnings</div>
              <div class="earnings-total-amount">₱{{ number_format($earningsMetrics['total'], 2) }}</div>
            </div>
            <div class="earnings-chart-container">
              <canvas id="earningsChart" 
                data-days='{{ json_encode($earningsMetrics["days"]) }}' 
                data-data='{{ json_encode($earningsMetrics["data"]) }}'>
              </canvas>
            </div>
          </div>
        </div>

      </div>

    </main>
  </div>

  <script src="{{ asset('js/admin/home/dashboard.js') }}" defer></script>

</body>

</html>