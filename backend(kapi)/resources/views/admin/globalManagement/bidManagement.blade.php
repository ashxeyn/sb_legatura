<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/globalManagement/bidManagement.css') }}">
  
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
            <a href="{{ route('admin.globalManagement.bidManagement') }}" class="submenu-link active">Bid Management</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Bid Management</h1>

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

      <div class="p-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          
          <!-- Total Bids Received Card -->
          <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-blue-500">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-gray-500 text-sm font-medium">Total Bids Received</p>
                <div class="flex items-baseline gap-2 mt-2">
                  <h2 class="text-4xl font-bold text-gray-800">156k</h2>
                  <span class="text-green-500 text-sm font-semibold flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    10.0%
                  </span>
                </div>
              </div>
              <div class="bg-blue-100 p-3 rounded-lg">
                <i class="fi fi-sr-inbox-in text-blue-600 text-2xl"></i>
              </div>
            </div>
            <p class="text-xs text-gray-400">Weekly</p>
            <div class="mt-4 h-1 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-blue-500 rounded-full animate-pulse" style="width: 75%"></div>
            </div>
          </div>

          <!-- Pending Reviews Card -->
          <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-orange-500">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-gray-500 text-sm font-medium">Pending Reviews</p>
                <div class="flex items-baseline gap-2 mt-2">
                  <h2 class="text-4xl font-bold text-gray-800">1k</h2>
                  <span class="text-red-500 text-sm font-semibold flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    5.0%
                  </span>
                </div>
              </div>
              <div class="bg-orange-100 p-3 rounded-lg">
                <i class="fi fi-sr-hourglass-end text-orange-600 text-2xl"></i>
              </div>
            </div>
            <p class="text-xs text-gray-400">Weekly</p>
            <div class="mt-4 h-1 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-orange-500 rounded-full animate-pulse" style="width: 35%"></div>
            </div>
          </div>

          <!-- Approved Bids Card -->
          <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-green-500">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-gray-500 text-sm font-medium">Approved Bids</p>
                <div class="flex items-baseline gap-2 mt-2">
                  <h2 class="text-4xl font-bold text-gray-800">156k</h2>
                  <span class="text-green-500 text-sm font-semibold flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    3.2%
                  </span>
                </div>
              </div>
              <div class="bg-green-100 p-3 rounded-lg">
                <i class="fi fi-sr-check-circle text-green-600 text-2xl"></i>
              </div>
            </div>
            <p class="text-xs text-gray-400">Weekly</p>
            <div class="mt-4 h-1 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-green-500 rounded-full animate-pulse" style="width: 90%"></div>
            </div>
          </div>

          <!-- Rejected Bids Card -->
          <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 border-l-4 border-red-500">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-gray-500 text-sm font-medium">Rejected Bids</p>
                <div class="flex items-baseline gap-2 mt-2">
                  <h2 class="text-4xl font-bold text-gray-800">3,422</h2>
                  <span class="text-green-500 text-sm font-semibold flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    8.3%
                  </span>
                </div>
              </div>
              <div class="bg-red-100 p-3 rounded-lg">
                <i class="fi fi-sr-cross-circle text-red-600 text-2xl"></i>
              </div>
            </div>
            <p class="text-xs text-gray-400">Weekly</p>
            <div class="mt-4 h-1 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-red-500 rounded-full animate-pulse" style="width: 25%"></div>
            </div>
          </div>

        </div>

        <!-- Bids Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 text-gray-600">
                  <th class="text-left px-6 py-4 text-sm font-semibold tracking-wide">Bid ID</th>
                  <th class="text-left px-6 py-4 text-sm font-semibold tracking-wide">Project Title</th>
                  <th class="text-left px-6 py-4 text-sm font-semibold tracking-wide">Contractor Company</th>
                  <th class="text-left px-6 py-4 text-sm font-semibold tracking-wide">Bid Amount</th>
                  <th class="text-left px-6 py-4 text-sm font-semibold tracking-wide">Submission Date</th>
                  <th class="text-left px-6 py-4 text-sm font-semibold tracking-wide">Status</th>
                  <th class="text-left px-6 py-4 text-sm font-semibold tracking-wide">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200" id="bidsTable">
                <!-- Row 1 -->
                <tr class="hover:bg-indigo-50/60 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">#10421</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">GreenBelt Building</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-bold shadow">CA</div>
                      <div>
                        <div class="font-semibold text-gray-800 leading-5">Cabanting</div>
                        <div class="text-gray-500 text-sm -mt-0.5">Architects</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Php. 10,000,000.00</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">03/12/2021</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span data-status="Approved" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">Approved</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <button class="action-btn action-btn--view rounded-2xl" title="View"
                        data-bid-id="#10421"
                        data-project="GreenBelt Building"
                        data-contractor="Cabanting Architects"
                        data-email="cabanting@example.com"
                        data-proposed-cost="10000000"
                        data-start-date="2021-12-20"
                        data-end-date="2022-12-20"
                        data-description="Approved scope covering architecture and structural deliverables; milestone billing and weekly progress updates."
                        data-reviewed-by="Property Owner Name"
                        data-date-action="2025-10-13"
                        data-remarks="Looks good. Proceed as planned with agreed terms."
                      >
                        <i class="fi fi-rr-eye"></i>
                      </button>
                      <button class="action-btn action-btn--edit rounded-2xl" title="Edit"
                        data-bid-id="#10421"
                        data-project="GreenBelt Building"
                        data-contractor="Cabanting Architects"
                        data-email="cabanting@example.com"
                        data-proposed-cost="10000000"
                        data-start-date="2021-12-20"
                        data-end-date="2022-12-20"
                        data-description="Approved scope covering architecture and structural deliverables; milestone billing and weekly progress updates."
                        data-status="Approved"
                      >
                        <i class="fi fi-rr-edit"></i>
                      </button>
                      <button class="action-btn action-btn--delete rounded-2xl" title="Delete"
                        data-bid-id="#10421"
                        data-project="GreenBelt Building"
                        data-contractor="Cabanting Architects"
                      >
                        <i class="fi fi-rr-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Row 2 -->
                <tr class="hover:bg-indigo-50/60 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">#10422</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">GreenBelt Building</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-800 to-black flex items-center justify-center text-white text-sm font-bold shadow">ZEN</div>
                      <div>
                        <div class="font-semibold text-gray-800 leading-5">J’Lois</div>
                        <div class="text-gray-500 text-sm -mt-0.5">Construction</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Php. 10,000,000.00</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">03/12/2021</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span data-status="Under Evaluation" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">Under Evaluation</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <button class="action-btn action-btn--view rounded-2xl" title="View"
                        data-bid-id="#10422"
                        data-project="GreenBelt Building"
                        data-contractor="J’Lois Construction"
                        data-email="jlois@example.com"
                        data-proposed-cost="10000000"
                        data-start-date="2021-12-25"
                        data-end-date="2022-12-23"
                        data-description="We propose a robust plan with phased milestones, ensuring quality and on-time delivery with optimized costs and materials management."
                      >
                        <i class="fi fi-rr-eye"></i>
                      </button>
                      <button class="action-btn action-btn--edit rounded-2xl" title="Edit"
                        data-bid-id="#10422"
                        data-project="GreenBelt Building"
                        data-contractor="J'Lois Construction"
                        data-email="jlois@example.com"
                        data-proposed-cost="10000000"
                        data-start-date="2021-12-25"
                        data-end-date="2022-12-23"
                        data-description="We propose a robust plan with phased milestones, ensuring quality and on-time delivery with optimized costs and materials management."
                        data-status="Under Evaluation"
                      >
                        <i class="fi fi-rr-edit"></i>
                      </button>
                      <button class="action-btn action-btn--delete rounded-2xl" title="Delete"
                        data-bid-id="#10422"
                        data-project="GreenBelt Building"
                        data-contractor="J'Lois Construction"
                      >
                        <i class="fi fi-rr-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Row 3 -->
                <tr class="hover:bg-indigo-50/60 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">#10423</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">GreenBelt Building</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-sm font-bold shadow">GTH</div>
                      <div>
                        <div class="font-semibold text-gray-800 leading-5">GTH</div>
                        <div class="text-gray-500 text-sm -mt-0.5">Builders</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Php. 10,000,000.00</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">03/12/2021</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span data-status="Rejected" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">Rejected</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <button class="action-btn action-btn--view rounded-2xl" title="View"
                        data-bid-id="#10423"
                        data-project="GreenBelt Building"
                        data-contractor="GTH Builders"
                        data-email="gth@example.com"
                        data-proposed-cost="10000000"
                        data-start-date="2021-12-15"
                        data-end-date="2022-12-15"
                        data-description="Proposal included comprehensive construction plan with detailed cost breakdown and timeline."
                        data-reviewed-by="Property Owner: John Doe"
                        data-date-action="October 15, 2025"
                        data-remarks="The proposed budget exceeds our allocated funds. The timeline also doesn't align with our project requirements. We appreciate the effort but cannot proceed with this proposal."
                      >
                        <i class="fi fi-rr-eye"></i>
                      </button>
                      <button class="action-btn action-btn--edit rounded-2xl" title="Edit"
                        data-bid-id="#10423"
                        data-project="GreenBelt Building"
                        data-contractor="GTH Builders"
                        data-email="gth@example.com"
                        data-proposed-cost="10000000"
                        data-start-date="2021-12-15"
                        data-end-date="2022-12-15"
                        data-description="Proposal included comprehensive construction plan with detailed cost breakdown and timeline."
                        data-status="Rejected"
                      >
                        <i class="fi fi-rr-edit"></i>
                      </button>
                      <button class="action-btn action-btn--delete rounded-2xl" title="Delete"
                        data-bid-id="#10423"
                        data-project="GreenBelt Building"
                        data-contractor="GTH Builders"
                      >
                        <i class="fi fi-rr-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Row 4 -->
                <tr class="hover:bg-indigo-50/60 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">#10424</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Harbor Point Mall</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-fuchsia-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow">NBC</div>
                      <div>
                        <div class="font-semibold text-gray-800 leading-5">Nova Build</div>
                        <div class="text-gray-500 text-sm -mt-0.5">Corp</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Php. 8,450,000.00</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">03/15/2021</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span data-status="Approved" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">Approved</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <button class="action-btn action-btn--view rounded-2xl" title="View"
                        data-bid-id="#10424"
                        data-project="Harbor Point Mall"
                        data-contractor="Nova Build Corp"
                        data-email="nova@example.com"
                        data-proposed-cost="8450000"
                        data-start-date="2021-12-28"
                        data-end-date="2022-12-10"
                        data-description="Approved mall renovation package. Coordinate with tenant schedule and nighttime work windows."
                        data-reviewed-by="PO: Maria Gomez"
                        data-date-action="2025-10-14"
                        data-remarks="Ensure minimal downtime for tenants."
                      ><i class="fi fi-rr-eye"></i></button>
                      <button class="action-btn action-btn--edit rounded-2xl" title="Edit"><i class="fi fi-rr-edit"></i></button>
                      <button class="action-btn action-btn--delete rounded-2xl" title="Delete"><i class="fi fi-rr-trash"></i></button>
                    </div>
                  </td>
                </tr>

                <!-- Row 5 -->
                <tr class="hover:bg-indigo-50/60 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">#10425</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Riverside Residences</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white text-xs font-bold shadow">APX</div>
                      <div>
                        <div class="font-semibold text-gray-800 leading-5">Apex</div>
                        <div class="text-gray-500 text-sm -mt-0.5">Engineering</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Php. 5,900,000.00</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">03/18/2021</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span data-status="Under Evaluation" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">Under Evaluation</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <button class="action-btn action-btn--view rounded-2xl" title="View"
                        data-bid-id="#10425"
                        data-project="Riverside Residences"
                        data-contractor="Apex Engineering"
                        data-email="apex@example.com"
                        data-proposed-cost="5900000"
                        data-start-date="2021-12-29"
                        data-end-date="2022-11-22"
                        data-description="Our team will deliver efficiently with a lean schedule, risk mitigation plan, and transparent reporting cadence."
                      ><i class="fi fi-rr-eye"></i></button>
                      <button class="action-btn action-btn--edit rounded-2xl" title="Edit"><i class="fi fi-rr-edit"></i></button>
                      <button class="action-btn action-btn--delete rounded-2xl" title="Delete"><i class="fi fi-rr-trash"></i></button>
                    </div>
                  </td>
                </tr>

                <!-- Row 6 -->
                <tr class="hover:bg-indigo-50/60 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">#10426</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Sky Tower</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 flex items-center justify-center text-white text-xs font-bold shadow">SKY</div>
                      <div>
                        <div class="font-semibold text-gray-800 leading-5">Skyline</div>
                        <div class="text-gray-500 text-sm -mt-0.5">Developers</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Php. 12,300,000.00</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">03/20/2021</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span data-status="Rejected" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">Rejected</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <button class="action-btn action-btn--view rounded-2xl" title="View"
                        data-bid-id="#10426"
                        data-project="Sky Tower"
                        data-contractor="Skyline Developers"
                        data-email="skyline@example.com"
                        data-proposed-cost="12300000"
                        data-start-date="2021-12-18"
                        data-end-date="2022-11-18"
                        data-description="High-rise construction proposal with advanced engineering solutions and premium materials specification."
                        data-reviewed-by="PO: Sarah Williams"
                        data-date-action="October 18, 2025"
                        data-remarks="After careful review, we found that the proposal lacks sufficient detail in the structural plan and safety measures. Additionally, the cost is significantly higher than market standards."
                      ><i class="fi fi-rr-eye"></i></button>
                      <button class="action-btn action-btn--edit rounded-2xl" title="Edit"><i class="fi fi-rr-edit"></i></button>
                      <button class="action-btn action-btn--delete rounded-2xl" title="Delete"><i class="fi fi-rr-trash"></i></button>
                    </div>
                  </td>
                </tr>

                <!-- Row 7 -->
                <tr class="hover:bg-indigo-50/60 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700 font-medium">#10427</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Pioneer Heights</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-500 to-rose-500 flex items-center justify-center text-white text-xs font-bold shadow">PNR</div>
                      <div>
                        <div class="font-semibold text-gray-800 leading-5">Pioneer</div>
                        <div class="text-gray-500 text-sm -mt-0.5">Construction</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">Php. 7,750,000.00</td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-700">03/22/2021</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span data-status="Approved" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">Approved</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                      <button class="action-btn action-btn--view rounded-2xl" title="View"
                        data-bid-id="#10427"
                        data-project="Pioneer Heights"
                        data-contractor="Pioneer Construction"
                        data-email="pioneer@example.com"
                        data-proposed-cost="7750000"
                        data-start-date="2021-12-26"
                        data-end-date="2022-11-30"
                        data-description="Approved residential tower package including MEP coordination."
                        data-reviewed-by="PO: Liam Cruz"
                        data-date-action="2025-10-15"
                        data-remarks="Proceed to mobilization next week."
                      ><i class="fi fi-rr-eye"></i></button>
                      <button class="action-btn action-btn--edit rounded-2xl" title="Edit"><i class="fi fi-rr-edit"></i></button>
                      <button class="action-btn action-btn--delete rounded-2xl" title="Delete"><i class="fi fi-rr-trash"></i></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  
  <!-- View Bid Modal (Under Evaluation) -->
  <div id="viewBidModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4 view-modal-overlay">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[92vh] overflow-hidden modal-panel transform transition-all flex flex-col scale-95 opacity-0">
      <!-- Modal Header -->
      <div class="flex items-start justify-between px-6 sm:px-8 py-6 border-b-2 border-amber-100 bg-gradient-to-r from-amber-50 via-yellow-50 to-orange-50 sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg view-icon-badge">
            <i class="fi fi-sr-hourglass-end text-white text-2xl"></i>
          </div>
          <div>
            <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">Bid Under Evaluation</h2>
            <p class="text-sm text-gray-600 mt-1 flex items-center gap-2">
              <i class="fi fi-rr-search-alt text-amber-500 text-xs"></i>
              <span>Review and assess bid submission</span>
            </p>
          </div>
        </div>
        <button id="closeViewBidModal" class="text-gray-400 hover:text-gray-600 p-2.5 rounded-xl hover:bg-white hover:shadow-md transition-all view-close-btn">
          <i class="fi fi-rr-cross text-xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="px-6 sm:px-8 py-6 overflow-y-auto view-modal-content flex-1 space-y-6">
        <!-- Status Banner -->
        <div class="view-status-banner rounded-xl border-2 border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-500 shadow-md animate-pulse-slow">
                <i class="fi fi-sr-clock text-white text-xl"></i>
              </div>
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-bold text-amber-700 uppercase tracking-wide">Current Status</span>
                  <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Under Evaluation</h3>
                <p class="text-sm text-gray-600 mt-1">Bid is currently being reviewed by the evaluation team</p>
              </div>
            </div>
            <button class="view-expand-btn px-4 py-2 bg-white border border-amber-200 text-amber-700 rounded-lg hover:bg-amber-50 transition-all text-sm font-medium shadow-sm hover:shadow">
              <i class="fi fi-rr-angle-small-down"></i>
            </button>
          </div>
          <div class="view-expand-content hidden mt-4 pt-4 border-t border-amber-200">
            <div class="grid grid-cols-3 gap-4">
              <div class="bg-white/60 backdrop-blur-sm rounded-lg p-3 border border-amber-100">
                <div class="text-xs text-gray-600 mb-1">Submitted On</div>
                <div class="font-semibold text-gray-800">Nov 15, 2025</div>
              </div>
              <div class="bg-white/60 backdrop-blur-sm rounded-lg p-3 border border-amber-100">
                <div class="text-xs text-gray-600 mb-1">Days in Review</div>
                <div class="font-semibold text-gray-800">3 Days</div>
              </div>
              <div class="bg-white/60 backdrop-blur-sm rounded-lg p-3 border border-amber-100">
                <div class="text-xs text-gray-600 mb-1">Expected Response</div>
                <div class="font-semibold text-gray-800">2-5 Days</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Bidder Information -->
          <div class="view-info-card rounded-xl border-2 border-indigo-100 bg-gradient-to-br from-indigo-50 to-blue-50 p-6 shadow-sm hover:shadow-lg transition-all">
            <div class="flex items-center gap-3 mb-5">
              <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-indigo-500 shadow-md">
                <i class="fi fi-rr-user text-white text-lg"></i>
              </div>
              <h3 class="text-lg font-bold text-gray-800">Bidder Information</h3>
            </div>
            <div class="space-y-3 bg-white/60 backdrop-blur-sm rounded-lg p-4 border border-indigo-100">
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Company Name</span>
                <span id="bidderCompany" class="font-semibold text-gray-800 text-right">-</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Email Address</span>
                <span id="bidderEmail" class="font-semibold text-gray-800 text-right">-</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">PCAB No</span>
                <span class="font-semibold text-gray-800">12345-AB-2025</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">PCAB Category</span>
                <span class="font-semibold text-gray-800">Category B</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">PCAB Expiration</span>
                <span class="font-semibold text-gray-800">Aug 15, 2026</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Business Permit No</span>
                <span class="font-semibold text-gray-800">BP-2025-0987</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Permit City</span>
                <span class="font-semibold text-gray-800">Zamboanga City</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Permit Expiration</span>
                <span class="font-semibold text-gray-800">Dec 31, 2025</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">TIN Registration</span>
                <span class="font-semibold text-gray-800">123-456-789-000</span>
              </div>
            </div>
          </div>

          <!-- Project Information -->
          <div class="view-info-card rounded-xl border-2 border-purple-100 bg-gradient-to-br from-purple-50 to-pink-50 p-6 shadow-sm hover:shadow-lg transition-all">
            <div class="flex items-center gap-3 mb-5">
              <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-purple-500 shadow-md">
                <i class="fi fi-rr-building text-white text-lg"></i>
              </div>
              <h3 class="text-lg font-bold text-gray-800">Project Information</h3>
            </div>
            <div class="space-y-3 bg-white/60 backdrop-blur-sm rounded-lg p-4 border border-purple-100">
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Project Title</span>
                <span id="projectTitle" class="font-semibold text-gray-800 text-right">-</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Property Address</span>
                <span class="font-semibold text-gray-800 text-right">Tetuan Zamboanga City</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Property Type</span>
                <span class="font-semibold text-gray-800">Residential</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Lot Size (sqm)</span>
                <span class="font-semibold text-gray-800">3,000</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Target Timeline</span>
                <span class="font-semibold text-gray-800">6 months</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Budget</span>
                <span class="font-semibold text-gray-800">PHP 1,000,000</span>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-sm text-gray-600">Bidding Deadline</span>
                <span class="font-semibold text-gray-800">Nov 20, 2025</span>
              </div>
              <div class="pt-2 border-t border-purple-100">
                <div class="text-sm text-gray-600 mb-2">Attachments</div>
                <div class="space-y-1">
                  <a href="#" class="text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-2 text-sm transition-colors hover:underline">
                    <i class="fi fi-rr-paperclip"></i> sample_photo.jpeg
                  </a>
                  <a href="#" class="text-amber-600 hover:text-amber-700 inline-flex items-center gap-2 text-sm transition-colors hover:underline">
                    <i class="fi fi-rr-paperclip"></i> sample_photo2.jpeg
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bid Details -->
        <div>
          <div class="flex items-center gap-3 mb-4">
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-green-500 shadow-md">
              <i class="fi fi-rr-calculator text-white text-lg"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Bid Details</h3>
          </div>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Inputs left -->
            <div class="space-y-4 lg:col-span-1">
              <div class="view-input-group">
                <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                  <i class="fi fi-rr-coins text-indigo-500"></i>
                  Proposed Cost (PHP)
                </label>
                <div class="relative">
                  <input id="proposedCost" type="text" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="0.00" readonly>
                  <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">PHP</span>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div class="view-input-group">
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fi fi-rr-calendar-day text-green-500"></i> Start
                  </label>
                  <input id="startDate" type="date" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" readonly />
                </div>
                <div class="view-input-group">
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fi fi-rr-calendar-check text-amber-500"></i> End
                  </label>
                  <input id="endDate" type="date" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" readonly />
                </div>
              </div>
              <div class="view-input-group">
                <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                  <i class="fi fi-rr-document text-purple-500"></i>
                  Description
                </label>
                <textarea id="bidDescription" rows="6" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none" placeholder="Bid description..." readonly></textarea>
              </div>
            </div>

            <!-- Files right -->
            <div class="lg:col-span-2">
              <div class="overflow-hidden rounded-xl border-2 border-gray-200 shadow-sm view-files-table">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-5 py-4 border-b-2 border-gray-200 flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <i class="fi fi-rr-folder text-indigo-600"></i>
                    <span class="text-sm font-bold text-gray-700">Supporting Files</span>
                  </div>
                  <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full font-medium">3 Files</span>
                </div>
                <div>
                  <table class="w-full">
                    <thead class="bg-gray-50">
                      <tr class="text-gray-600 border-b border-gray-200">
                        <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wide">Files</th>
                        <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wide">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wide">User</th>
                        <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wide">Position</th>
                        <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wide">Action</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr class="hover:bg-indigo-50/30 transition-colors view-file-row">
                        <td class="px-5 py-3">
                          <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center shadow-sm">
                              <i class="fi fi-rr-file-pdf text-white text-sm"></i>
                            </div>
                            <span class="text-gray-800 font-medium">Progress Report</span>
                          </div>
                        </td>
                        <td class="px-5 py-3 text-gray-700 text-sm">Dec 23, 2022</td>
                        <td class="px-5 py-3 text-gray-700 text-sm">Carl Saludo</td>
                        <td class="px-5 py-3 text-gray-700 text-sm">Architect</td>
                        <td class="px-5 py-3">
                          <button class="w-9 h-9 rounded-full bg-blue-50 hover:bg-blue-100 flex items-center justify-center transition-all hover:scale-110" title="Download">
                            <i class="fi fi-rr-download text-blue-600"></i>
                          </button>
                        </td>
                      </tr>
                      <tr class="hover:bg-indigo-50/30 transition-colors view-file-row">
                        <td class="px-5 py-3">
                          <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                              <i class="fi fi-rr-file-pdf text-white text-sm"></i>
                            </div>
                            <span class="text-gray-800 font-medium">Specification Sheet</span>
                          </div>
                        </td>
                        <td class="px-5 py-3 text-gray-700 text-sm">Dec 23, 2022</td>
                        <td class="px-5 py-3 text-gray-700 text-sm">Carl Saludo</td>
                        <td class="px-5 py-3 text-gray-700 text-sm">Architect</td>
                        <td class="px-5 py-3">
                          <button class="w-9 h-9 rounded-full bg-blue-50 hover:bg-blue-100 flex items-center justify-center transition-all hover:scale-110" title="Download">
                            <i class="fi fi-rr-download text-blue-600"></i>
                          </button>
                        </td>
                      </tr>
                      <tr class="hover:bg-indigo-50/30 transition-colors view-file-row">
                        <td class="px-5 py-3">
                          <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center shadow-sm">
                              <i class="fi fi-rr-file-pdf text-white text-sm"></i>
                            </div>
                            <span class="text-gray-800 font-medium">Budget Breakdown</span>
                          </div>
                        </td>
                        <td class="px-5 py-3 text-gray-700 text-sm">Dec 23, 2022</td>
                        <td class="px-5 py-3 text-gray-700 text-sm">Carl Saludo</td>
                        <td class="px-5 py-3 text-gray-700 text-sm">Architect</td>
                        <td class="px-5 py-3">
                          <button class="w-9 h-9 rounded-full bg-blue-50 hover:bg-blue-100 flex items-center justify-center transition-all hover:scale-110" title="Download">
                            <i class="fi fi-rr-download text-blue-600"></i>
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

  
      </div>
    </div>
  </div>
  <!-- Approved Bid Modal -->
  <div id="approvedBidModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[92vh] overflow-hidden modal-panel transform transition-all flex flex-col">
      <!-- Header -->
      <div class="decision-header flex items-start justify-between px-6 sm:px-8 py-6 border-b-2 border-indigo-100 bg-gradient-to-r from-indigo-50 via-blue-50 to-purple-50 sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg decision-icon-badge">
            <i class="fi fi-sr-gavel text-white text-2xl"></i>
          </div>
          <div>
            <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Property Owner's Decision</h2>
            <p class="text-sm text-gray-600 mt-1 flex items-center gap-2">
              <i class="fi fi-rr-document-signed text-indigo-500 text-xs"></i>
              <span>Final approval and review details</span>
            </p>
          </div>
        </div>
        <button id="closeApprovedBidModal" class="text-gray-400 hover:text-gray-600 p-2.5 rounded-xl hover:bg-white hover:shadow-md transition-all">
          <i class="fi fi-rr-cross text-xl"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="px-6 sm:px-8 py-6 overflow-y-auto view-modal-content flex-1 space-y-6">
        <!-- Decision Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Status Card -->
          <div class="decision-status-card rounded-xl border-2 border-green-200 bg-gradient-to-br from-green-50 to-emerald-50 p-6 shadow-lg">
            <div class="flex items-center gap-3 mb-6">
              <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-500 shadow-lg">
                <i class="fi fi-sr-check-circle text-white text-xl"></i>
              </div>
              <div>
                <div class="text-xs text-green-700 font-medium uppercase tracking-wide mb-1">Decision Status</div>
                <h4 class="text-lg font-bold text-green-800">Approved</h4>
              </div>
            </div>
            
            <div class="space-y-4">
              <div class="bg-white/60 backdrop-blur-sm rounded-lg p-4 border border-green-100">
                <div class="flex items-center gap-2 mb-2">
                  <i class="fi fi-rr-user text-green-600 text-sm"></i>
                  <div class="text-xs text-gray-600 font-medium uppercase tracking-wide">Reviewed By</div>
                </div>
                <div id="approvedReviewedBy" class="font-semibold text-gray-800 text-base">-</div>
              </div>
              
              <div class="bg-white/60 backdrop-blur-sm rounded-lg p-4 border border-green-100">
                <div class="flex items-center gap-2 mb-2">
                  <i class="fi fi-rr-calendar text-green-600 text-sm"></i>
                  <div class="text-xs text-gray-600 font-medium uppercase tracking-wide">Date of Action</div>
                </div>
                <div id="approvedDateAction" class="font-semibold text-gray-800 text-base">-</div>
              </div>
            </div>
          </div>
          
          <!-- Remarks Card -->
          <div class="lg:col-span-2 decision-remarks-card rounded-xl border-2 border-indigo-200 bg-gradient-to-br from-indigo-50 to-blue-50 p-6 shadow-lg">
            <div class="flex items-center gap-3 mb-4">
              <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-indigo-500 shadow-md">
                <i class="fi fi-rr-comment-alt text-white text-lg"></i>
              </div>
              <h3 class="text-lg font-bold text-gray-800">Property Owner's Remarks</h3>
            </div>
            <textarea id="approvedRemarks" rows="6" class="w-full border-2 border-indigo-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-white shadow-inner" placeholder="Enter remarks or comments from the property owner regarding this approval..."></textarea>
            <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">
              <i class="fi fi-rr-info text-indigo-500"></i>
              <span>This section contains the property owner's feedback and instructions.</span>
            </div>
          </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Bidder Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm text-gray-700">
              <div><span class="text-gray-500">Company Name :</span> <span id="approvedBidderCompany" class="font-medium">-</span></div>
              <div><span class="text-gray-500">Email Address :</span> <span id="approvedBidderEmail" class="font-medium">-</span></div>
              <div><span class="text-gray-500">PCAB No :</span> <span class="font-medium">12345-AB-2025</span></div>
              <div><span class="text-gray-500">PCAB Category :</span> <span class="font-medium">Category B</span></div>
              <div><span class="text-gray-500">PCAB Expiration Date:</span> <span class="font-medium">August 15, 2026</span></div>
              <div><span class="text-gray-500">Business Permit No. :</span> <span class="font-medium">BP-2025-0987</span></div>
              <div><span class="text-gray-500">Permit City :</span> <span class="font-medium">Zamboanga City</span></div>
              <div><span class="text-gray-500">Business Permit Expiration:</span> <span class="font-medium">December 31, 2025</span></div>
              <div><span class="text-gray-500">TIN Registration number :</span> <span class="font-medium">123-456-789-000</span></div>
            </div>
          </div>
          <div class="rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Project Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm text-gray-700">
              <div><span class="text-gray-500">Project Title :</span> <span id="approvedProjectTitle" class="font-medium">-</span></div>
              <div><span class="text-gray-500">Property Address :</span> <span class="font-medium">Tetuan Zamboanga City 7000</span></div>
              <div><span class="text-gray-500">Property Type :</span> <span class="font-medium">Residential</span></div>
              <div><span class="text-gray-500">Lot Size (sqm) :</span> <span class="font-medium">3000</span></div>
              <div><span class="text-gray-500">Target Timeline :</span> <span class="font-medium">3000</span></div>
              <div><span class="text-gray-500">Budget :</span> <span class="font-medium">PHP 1,000,000</span></div>
              <div><span class="text-gray-500">Bidding Deadline :</span> <span class="font-medium">November 20, 2025</span></div>
              <div class="sm:col-span-2 grid grid-cols-2 gap-3 mt-2">
                <div>
                  <div class="text-gray-600 text-sm mb-1">Uploaded Photos</div>
                  <a href="#" class="text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-2 text-sm"><i class="fi fi-rr-paperclip"></i> sample_photo.jpeg</a>
                </div>
                <div>
                  <div class="text-gray-600 text-sm mb-1">Supporting Files</div>
                  <div class="space-y-1">
                    <a href="#" class="text-amber-600 hover:text-amber-700 inline-flex items-center gap-2 text-sm"><i class="fi fi-rr-paperclip"></i> sample_photo.jpeg</a>
                    <a href="#" class="text-amber-600 hover:text-amber-700 inline-flex items-center gap-2 text-sm"><i class="fi fi-rr-paperclip"></i> sample_photo2.jpeg</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bid Details + Files -->
        <div>
          <h3 class="font-semibold text-gray-800 mb-4">Bid Details</h3>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="space-y-4 lg:col-span-1">
              <div>
                <label class="block text-sm text-gray-600 mb-1">Proposed Cost (PHP)</label>
                <div class="relative">
                  <input id="approvedProposedCost" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:border-transparent" placeholder="0.00">
                  <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">PHP</span>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Start Date</label>
                  <input id="approvedStartDate" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:border-transparent" />
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">Estimated Completion</label>
                  <input id="approvedEndDate" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:border-transparent" />
                </div>
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">Description</label>
                <textarea id="approvedBidDescription" rows="5" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:border-transparent" placeholder="Write a compelling message to the client."></textarea>
              </div>
            </div>
            <div class="lg:col-span-2">
              <div class="overflow-hidden rounded-xl border border-gray-200">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 text-sm font-medium text-gray-700">Supporting Files</div>
                <div class="overflow-x-auto">
                  <table class="w-full">
                    <thead>
                      <tr class="text-gray-600">
                        <th class="text-left px-5 py-3 text-sm font-semibold">Files</th>
                        <th class="text-left px-5 py-3 text-sm font-semibold">Date Submitted</th>
                        <th class="text-left px-5 py-3 text-sm font-semibold">User Name</th>
                        <th class="text-left px-5 py-3 text-sm font-semibold">Position</th>
                        <th class="text-left px-5 py-3 text-sm font-semibold">Action</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr>
                        <td class="px-5 py-3">
                          <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-md text-gray-700 font-semibold">PDF</span>
                            <span class="text-gray-800">Progress Report</span>
                          </div>
                        </td>
                        <td class="px-5 py-3 text-gray-700">Dec 23, 2022</td>
                        <td class="px-5 py-3 text-gray-700">Carl Saludo</td>
                        <td class="px-5 py-3 text-gray-700">Architect</td>
                        <td class="px-5 py-3"><button class="action-btn rounded-2xl action-btn--view" title="Download"><i class="fi fi-rr-download"></i></button></td>
                      </tr>
                      <tr>
                        <td class="px-5 py-3">
                          <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 rounded-md text-gray-700 font-semibold">PDF</span>
                            <span class="text-gray-800">As-built Plan</span>
                          </div>
                        </td>
                        <td class="px-5 py-3 text-gray-700">Jan 09, 2023</td>
                        <td class="px-5 py-3 text-gray-700">Carl Saludo</td>
                        <td class="px-5 py-3 text-gray-700">Architect</td>
                        <td class="px-5 py-3"><button class="action-btn rounded-2xl action-btn--view" title="Download"><i class="fi fi-rr-download"></i></button></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bid History -->
        <div>
          <h3 class="font-semibold text-gray-800 mb-4">Bid History</h3>
          <div class="overflow-hidden rounded-xl border border-gray-200">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="bg-gray-50 text-gray-600">
                    <th class="text-left px-6 py-3 text-sm font-semibold">Bid ID</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Company Name</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Date Submitted</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Bid Amount</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Duration</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Status</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr>
                    <td class="px-6 py-3 text-gray-700">#234366</td>
                    <td class="px-6 py-3 text-indigo-600 hover:underline cursor-pointer">UI Design</td>
                    <td class="px-6 py-3 text-gray-700">03/12/2021</td>
                    <td class="px-6 py-3 text-gray-700">Php. 10,000</td>
                    <td class="px-6 py-3 text-gray-700">90 days</td>
                    <td class="px-6 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">Approved</span></td>
                    <td class="px-6 py-3">
                      <div class="flex items-center gap-2">
                        <button class="action-btn action-btn--view rounded-2xl" title="View"><i class="fi fi-rr-eye"></i></button>
                        <button class="action-btn action-btn--edit rounded-2xl" title="Edit"><i class="fi fi-rr-edit"></i></button>
                        <button class="action-btn action-btn--delete rounded-2xl" title="Delete"><i class="fi fi-rr-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="px-6 py-3 text-gray-700">#234316</td>
                    <td class="px-6 py-3 text-indigo-600 hover:underline cursor-pointer">UI Design</td>
                    <td class="px-6 py-3 text-gray-700">03/12/2021</td>
                    <td class="px-6 py-3 text-gray-700">Php. 10,000</td>
                    <td class="px-6 py-3 text-gray-700">90 days</td>
                    <td class="px-6 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">Rejected</span></td>
                    <td class="px-6 py-3">
                      <div class="flex items-center gap-2">
                        <button class="action-btn action-btn--view rounded-2xl" title="View"><i class="fi fi-rr-eye"></i></button>
                        <button class="action-btn action-btn--edit rounded-2xl" title="Edit"><i class="fi fi-rr-edit"></i></button>
                        <button class="action-btn action-btn--delete rounded-2xl" title="Delete"><i class="fi fi-rr-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="px-6 py-3 text-gray-700">#233446</td>
                    <td class="px-6 py-3 text-indigo-600 hover:underline cursor-pointer">UI Design</td>
                    <td class="px-6 py-3 text-gray-700">03/12/2021</td>
                    <td class="px-6 py-3 text-gray-700">Php. 10,000</td>
                    <td class="px-6 py-3 text-gray-700">90 days</td>
                    <td class="px-6 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">Rejected</span></td>
                    <td class="px-6 py-3">
                      <div class="flex items-center gap-2">
                        <button class="action-btn action-btn--view rounded-2xl" title="View"><i class="fi fi-rr-eye"></i></button>
                        <button class="action-btn action-btn--edit rounded-2xl" title="Edit"><i class="fi fi-rr-edit"></i></button>
                        <button class="action-btn action-btn--delete rounded-2xl" title="Delete"><i class="fi fi-rr-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Rejected Bid Modal -->
  <div id="rejectedBidModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[92vh] overflow-hidden modal-panel transform transition-all flex flex-col">
      <!-- Header -->
      <div class="rejected-header flex items-start justify-between px-6 sm:px-8 py-6 border-b-2 border-red-100 bg-gradient-to-r from-red-50 via-rose-50 to-pink-50 sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 shadow-lg rejected-icon-badge">
            <i class="fi fi-sr-cross-circle text-white text-2xl"></i>
          </div>
          <div>
            <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-red-600 to-rose-600 bg-clip-text text-transparent">Property Owner's Decision</h2>
            <p class="text-sm text-gray-600 mt-1 flex items-center gap-2">
              <i class="fi fi-rr-document-signed text-red-500 text-xs"></i>
              <span>Rejection details and feedback</span>
            </p>
          </div>
        </div>
        <button id="closeRejectedBidModal" class="text-gray-400 hover:text-gray-600 p-2.5 rounded-xl hover:bg-white hover:shadow-md transition-all">
          <i class="fi fi-rr-cross text-xl"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="px-6 sm:px-8 py-6 overflow-y-auto view-modal-content flex-1 space-y-6">
        <!-- Decision Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Status Card -->
          <div class="rejected-status-card rounded-xl border-2 border-red-200 bg-gradient-to-br from-red-50 to-rose-50 p-6 shadow-lg">
            <div class="flex items-center gap-3 mb-6">
              <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-500 shadow-lg">
                <i class="fi fi-sr-cross-circle text-white text-xl"></i>
              </div>
              <div>
                <div class="text-xs text-red-700 font-medium uppercase tracking-wide mb-1">Decision Status</div>
                <h4 class="text-lg font-bold text-red-800">Rejected</h4>
              </div>
            </div>
            
            <div class="space-y-4">
              <div class="bg-white/60 backdrop-blur-sm rounded-lg p-4 border border-red-100">
                <div class="flex items-center gap-2 mb-2">
                  <i class="fi fi-rr-user text-red-600 text-sm"></i>
                  <div class="text-xs text-gray-600 font-medium uppercase tracking-wide">Reviewed By</div>
                </div>
                <div id="rejectedReviewedBy" class="font-semibold text-gray-800 text-base">-</div>
              </div>
              
              <div class="bg-white/60 backdrop-blur-sm rounded-lg p-4 border border-red-100">
                <div class="flex items-center gap-2 mb-2">
                  <i class="fi fi-rr-calendar text-red-600 text-sm"></i>
                  <div class="text-xs text-gray-600 font-medium uppercase tracking-wide">Date of Action</div>
                </div>
                <div id="rejectedDateAction" class="font-semibold text-gray-800 text-base">-</div>
              </div>
            </div>
          </div>
          
          <!-- Remarks Card -->
          <div class="lg:col-span-2 rejected-remarks-card rounded-xl border-2 border-red-200 bg-gradient-to-br from-red-50 to-rose-50 p-6 shadow-lg">
            <div class="flex items-center gap-3 mb-4">
              <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-red-500 shadow-md">
                <i class="fi fi-rr-comment-exclamation text-white text-lg"></i>
              </div>
              <h3 class="text-lg font-bold text-gray-800">Reason for Rejection</h3>
            </div>
            <textarea id="rejectedRemarks" rows="6" class="w-full border-2 border-red-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all bg-white shadow-inner" placeholder="Detailed explanation for why the bid was rejected..." readonly></textarea>
            <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">
              <i class="fi fi-rr-info text-red-500"></i>
              <span>This section contains the property owner's reason for rejecting this bid.</span>
            </div>
          </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="rounded-xl border border-gray-200 p-5 bg-white shadow-sm">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
              <i class="fi fi-rr-building text-indigo-500"></i>
              <span>Bidder Information</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm text-gray-700">
              <div><span class="text-gray-500">Company Name :</span> <span id="rejectedBidderCompany" class="font-medium">-</span></div>
              <div><span class="text-gray-500">Email Address :</span> <span id="rejectedBidderEmail" class="font-medium">-</span></div>
              <div><span class="text-gray-500">PCAB No :</span> <span class="font-medium">12345-AB-2025</span></div>
              <div><span class="text-gray-500">PCAB Category :</span> <span class="font-medium">Category B</span></div>
              <div><span class="text-gray-500">PCAB Expiration Date:</span> <span class="font-medium">August 15, 2026</span></div>
              <div><span class="text-gray-500">Business Permit No. :</span> <span class="font-medium">BP-2025-0987</span></div>
              <div><span class="text-gray-500">Permit City :</span> <span class="font-medium">Zamboanga City</span></div>
              <div><span class="text-gray-500">Business Permit Expiration:</span> <span class="font-medium">December 31, 2025</span></div>
              <div><span class="text-gray-500">TIN Registration number :</span> <span class="font-medium">123-456-789-000</span></div>
            </div>
          </div>
          <div class="rounded-xl border border-gray-200 p-5 bg-white shadow-sm">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
              <i class="fi fi-rr-home text-indigo-500"></i>
              <span>Project Information</span>
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm text-gray-700">
              <div><span class="text-gray-500">Project Title :</span> <span id="rejectedProjectTitle" class="font-medium">-</span></div>
              <div><span class="text-gray-500">Property Address :</span> <span class="font-medium">Tetuan Zamboanga City 7000</span></div>
              <div><span class="text-gray-500">Property Type :</span> <span class="font-medium">Residential</span></div>
              <div><span class="text-gray-500">Lot Size (sqm) :</span> <span class="font-medium">3000</span></div>
              <div><span class="text-gray-500">Target Timeline :</span> <span class="font-medium">3000</span></div>
              <div><span class="text-gray-500">Budget :</span> <span class="font-medium">PHP 1,000,000</span></div>
              <div><span class="text-gray-500">Bidding Deadline :</span> <span class="font-medium">November 20, 2025</span></div>
              <div class="sm:col-span-2 grid grid-cols-2 gap-3 mt-2">
                <div>
                  <div class="text-gray-600 text-sm mb-1">Uploaded Photos</div>
                  <a href="#" class="text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-2 text-sm"><i class="fi fi-rr-paperclip"></i> sample_photo.jpeg</a>
                </div>
                <div>
                  <div class="text-gray-600 text-sm mb-1">Supporting Files</div>
                  <div class="space-y-1">
                    <a href="#" class="text-amber-600 hover:text-amber-700 inline-flex items-center gap-2 text-sm"><i class="fi fi-rr-paperclip"></i> sample_photo.jpeg</a>
                    <a href="#" class="text-amber-600 hover:text-amber-700 inline-flex items-center gap-2 text-sm"><i class="fi fi-rr-paperclip"></i> sample_photo2.jpeg</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bid Details + Files -->
        <div>
          <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fi fi-rr-document text-indigo-500"></i>
            <span>Bid Details</span>
          </h3>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="space-y-4 lg:col-span-1">
              <div>
                <label class="block text-sm text-gray-600 mb-1 font-medium">Proposed Cost (PHP)</label>
                <div class="relative">
                  <input id="rejectedProposedCost" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-red-400 focus:border-transparent bg-gray-50" placeholder="0.00" readonly>
                  <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">PHP</span>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm text-gray-600 mb-1 font-medium">Start Date</label>
                  <input id="rejectedStartDate" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-red-400 focus:border-transparent bg-gray-50" readonly />
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1 font-medium">Estimated Completion</label>
                  <input id="rejectedEndDate" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-red-400 focus:border-transparent bg-gray-50" readonly />
                </div>
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1 font-medium">Proposed Duration</label>
                <div class="bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-gray-700">
                  <span id="rejectedDuration" class="font-medium">mm / dd / yy</span>
                </div>
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1 font-medium">Description</label>
                <textarea id="rejectedBidDescription" rows="5" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-red-400 focus:border-transparent bg-gray-50" placeholder="Bid description..." readonly></textarea>
              </div>
            </div>
            <div class="lg:col-span-2">
              <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                <div class="bg-gradient-to-r from-red-50 to-rose-50 px-4 py-3 border-b border-red-100 text-sm font-semibold text-gray-800 flex items-center gap-2">
                  <i class="fi fi-rr-folder-open text-red-500"></i>
                  <span>Supporting Files</span>
                </div>
                <div class="overflow-x-auto">
                  <table class="w-full">
                    <thead>
                      <tr class="bg-gray-50 text-gray-600">
                        <th class="text-left px-5 py-3 text-sm font-semibold">Files</th>
                        <th class="text-left px-5 py-3 text-sm font-semibold">Date Submitted</th>
                        <th class="text-left px-5 py-3 text-sm font-semibold">User Name</th>
                        <th class="text-left px-5 py-3 text-sm font-semibold">Position</th>
                        <th class="text-left px-5 py-3 text-sm font-semibold">Action</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">
                          <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-red-100 rounded-md text-red-600 font-semibold text-xs">PDF</span>
                            <span class="text-gray-800 font-medium">Progress Report</span>
                          </div>
                        </td>
                        <td class="px-5 py-3 text-gray-700">Dec 23, 2022</td>
                        <td class="px-5 py-3 text-gray-700">Carl Saludo</td>
                        <td class="px-5 py-3 text-gray-700">Architect</td>
                        <td class="px-5 py-3"><button class="action-btn rounded-2xl action-btn--view" title="Download"><i class="fi fi-rr-download"></i></button></td>
                      </tr>
                      <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">
                          <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-red-100 rounded-md text-red-600 font-semibold text-xs">PDF</span>
                            <span class="text-gray-800 font-medium">Cost Estimate</span>
                          </div>
                        </td>
                        <td class="px-5 py-3 text-gray-700">Jan 09, 2023</td>
                        <td class="px-5 py-3 text-gray-700">Carl Saludo</td>
                        <td class="px-5 py-3 text-gray-700">Architect</td>
                        <td class="px-5 py-3"><button class="action-btn rounded-2xl action-btn--view" title="Download"><i class="fi fi-rr-download"></i></button></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bid History -->
        <div>
          <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fi fi-rr-time-past text-indigo-500"></i>
            <span>Bid History</span>
          </h3>
          <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="bg-gradient-to-r from-gray-50 to-gray-100 text-gray-600">
                    <th class="text-left px-6 py-3 text-sm font-semibold">Bid ID</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Company Name</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Date Submitted</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Bid Amount</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Duration</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Status</th>
                    <th class="text-left px-6 py-3 text-sm font-semibold">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr class="hover:bg-red-50/30 transition-colors">
                    <td class="px-6 py-3 text-gray-700 font-medium">#234366</td>
                    <td class="px-6 py-3 text-indigo-600 hover:underline cursor-pointer font-medium">UI Design</td>
                    <td class="px-6 py-3 text-gray-700">03/12/2021</td>
                    <td class="px-6 py-3 text-gray-700 font-medium">Php. 10,000</td>
                    <td class="px-6 py-3 text-gray-700">90 days</td>
                    <td class="px-6 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">Rejected</span></td>
                    <td class="px-6 py-3">
                      <div class="flex items-center gap-2">
                        <button class="action-btn action-btn--view rounded-2xl" title="View"><i class="fi fi-rr-eye"></i></button>
                        <button class="action-btn action-btn--edit rounded-2xl" title="Edit"><i class="fi fi-rr-edit"></i></button>
                        <button class="action-btn action-btn--delete rounded-2xl" title="Delete"><i class="fi fi-rr-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                  <tr class="hover:bg-red-50/30 transition-colors">
                    <td class="px-6 py-3 text-gray-700 font-medium">#234316</td>
                    <td class="px-6 py-3 text-indigo-600 hover:underline cursor-pointer font-medium">UI Design</td>
                    <td class="px-6 py-3 text-gray-700">03/12/2021</td>
                    <td class="px-6 py-3 text-gray-700 font-medium">Php. 10,000</td>
                    <td class="px-6 py-3 text-gray-700">90 days</td>
                    <td class="px-6 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">Approved</span></td>
                    <td class="px-6 py-3">
                      <div class="flex items-center gap-2">
                        <button class="action-btn action-btn--view rounded-2xl" title="View"><i class="fi fi-rr-eye"></i></button>
                        <button class="action-btn action-btn--edit rounded-2xl" title="Edit"><i class="fi fi-rr-edit"></i></button>
                        <button class="action-btn action-btn--delete rounded-2xl" title="Delete"><i class="fi fi-rr-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                  <tr class="hover:bg-red-50/30 transition-colors">
                    <td class="px-6 py-3 text-gray-700 font-medium">#233446</td>
                    <td class="px-6 py-3 text-indigo-600 hover:underline cursor-pointer font-medium">UI Design</td>
                    <td class="px-6 py-3 text-gray-700">03/12/2021</td>
                    <td class="px-6 py-3 text-gray-700 font-medium">Php. 10,000</td>
                    <td class="px-6 py-3 text-gray-700">90 days</td>
                    <td class="px-6 py-3"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">Under Review</span></td>
                    <td class="px-6 py-3">
                      <div class="flex items-center gap-2">
                        <button class="action-btn action-btn--view rounded-2xl" title="View"><i class="fi fi-rr-eye"></i></button>
                        <button class="action-btn action-btn--edit rounded-2xl" title="Edit"><i class="fi fi-rr-edit"></i></button>
                        <button class="action-btn action-btn--delete rounded-2xl" title="Delete"><i class="fi fi-rr-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Bid Modal -->
  <div id="editBidModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[92vh] overflow-hidden modal-panel transform transition-all flex flex-col">
      <!-- Header -->
      <div class="edit-header flex items-start justify-between px-6 sm:px-8 py-6 border-b-2 border-amber-100 bg-gradient-to-r from-amber-50 via-orange-50 to-yellow-50 sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg edit-icon-badge">
            <i class="fi fi-sr-edit text-white text-2xl"></i>
          </div>
          <div>
            <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">Edit Bid Details</h2>
            <p class="text-sm text-gray-600 mt-1 flex items-center gap-2">
              <i class="fi fi-rr-document-signed text-amber-500 text-xs"></i>
              <span>Update bid information and project details</span>
            </p>
          </div>
        </div>
        <button id="closeEditBidModal" class="text-gray-400 hover:text-gray-600 p-2.5 rounded-xl hover:bg-white hover:shadow-md transition-all">
          <i class="fi fi-rr-cross text-xl"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="px-6 sm:px-8 py-6 overflow-y-auto view-modal-content flex-1 space-y-6">
        <!-- Bid Status Badge -->
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl border border-indigo-200">
          <div class="flex items-center gap-3">
            <i class="fi fi-rr-info text-indigo-600 text-xl"></i>
            <div>
              <div class="text-sm text-gray-600 font-medium">Current Status</div>
              <div id="editCurrentStatus" class="font-bold text-gray-800 text-lg">-</div>
            </div>
          </div>
          <div class="text-sm text-gray-600">
            <span class="font-medium">Bid ID:</span> <span id="editBidId" class="font-bold text-gray-800">-</span>
          </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="rounded-xl border-2 border-indigo-200 p-5 bg-gradient-to-br from-white to-indigo-50 shadow-md">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-lg">
              <i class="fi fi-rr-building text-indigo-500"></i>
              <span>Bidder Information</span>
            </h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Company Name</label>
                <input type="text" id="editBidderCompany" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" placeholder="Enter company name">
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Email Address</label>
                <input type="email" id="editBidderEmail" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" placeholder="company@example.com">
              </div>
            </div>
          </div>
          <div class="rounded-xl border-2 border-purple-200 p-5 bg-gradient-to-br from-white to-purple-50 shadow-md">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-lg">
              <i class="fi fi-rr-home text-purple-500"></i>
              <span>Project Information</span>
            </h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Project Title</label>
                <input type="text" id="editProjectTitle" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" placeholder="Enter project title">
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Property Type</label>
                <select id="editPropertyType" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                  <option value="">Select property type</option>
                  <option value="Residential">Residential</option>
                  <option value="Commercial">Commercial</option>
                  <option value="Industrial">Industrial</option>
                  <option value="Mixed-Use">Mixed-Use</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Bid Details -->
        <div class="rounded-xl border-2 border-green-200 p-6 bg-gradient-to-br from-white to-green-50 shadow-md">
          <h3 class="font-bold text-gray-800 mb-5 flex items-center gap-2 text-lg">
            <i class="fi fi-rr-document text-green-600"></i>
            <span>Bid Details</span>
          </h3>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="space-y-4">
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Proposed Cost (PHP)</label>
                <div class="relative">
                  <input id="editProposedCost" type="text" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 pr-12 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" placeholder="0.00">
                  <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-semibold">PHP</span>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm text-gray-600 mb-1.5 font-medium">Start Date</label>
                  <input id="editStartDate" type="date" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" />
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1.5 font-medium">End Date</label>
                  <input id="editEndDate" type="date" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" />
                </div>
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Status</label>
                <select id="editStatus" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                  <option value="Under Evaluation">Under Evaluation</option>
                  <option value="Approved">Approved</option>
                  <option value="Rejected">Rejected</option>
                </select>
              </div>
            </div>
            <div class="lg:col-span-2">
              <label class="block text-sm text-gray-600 mb-1.5 font-medium">Description</label>
              <textarea id="editBidDescription" rows="10" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all resize-none" placeholder="Enter detailed bid description..."></textarea>
            </div>
          </div>
        </div>

        <!-- File Upload Section -->
        <div class="rounded-xl border-2 border-blue-200 p-6 bg-gradient-to-br from-white to-blue-50 shadow-md">
          <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-lg">
            <i class="fi fi-rr-folder-upload text-blue-600"></i>
            <span>Supporting Documents</span>
          </h3>
          <div class="border-2 border-dashed border-blue-300 rounded-xl p-8 text-center hover:border-blue-500 hover:bg-blue-50/50 transition-all cursor-pointer">
            <i class="fi fi-rr-cloud-upload text-blue-500 text-4xl mb-3 block"></i>
            <p class="text-gray-700 font-medium mb-1">Drop files here or click to browse</p>
            <p class="text-gray-500 text-sm">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 10MB)</p>
            <input type="file" class="hidden" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-4 pt-4 border-t-2 border-gray-200">
          <button id="cancelEditBtn" class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition-all flex items-center gap-2">
            <i class="fi fi-rr-cross-small"></i>
            <span>Cancel</span>
          </button>
          <button id="saveChangesBtn" class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold hover:from-amber-600 hover:to-orange-700 shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
            <i class="fi fi-rr-disk"></i>
            <span>Save Changes</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Save Confirmation Modal -->
  <div id="saveConfirmModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all scale-95 confirm-modal-panel">
      <div class="p-6">
        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 mx-auto mb-4">
          <i class="fi fi-rr-interrogation text-amber-600 text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Save Changes?</h3>
        <p class="text-gray-600 text-center mb-6">Are you sure you want to save the changes to this bid? This action will update the bid information.</p>
        <div class="flex items-center gap-3">
          <button id="cancelSaveBtn" class="flex-1 px-4 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition-all">
            Cancel
          </button>
          <button id="confirmSaveBtn" class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold hover:from-amber-600 hover:to-orange-700 shadow-lg hover:shadow-xl transition-all">
            Yes, Save
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Bid Modal -->
  <div id="editBidModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[92vh] overflow-hidden modal-panel transform transition-all flex flex-col">
      <!-- Header -->
      <div class="edit-header flex items-start justify-between px-6 sm:px-8 py-6 border-b-2 border-amber-100 bg-gradient-to-r from-amber-50 via-orange-50 to-yellow-50 sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg edit-icon-badge">
            <i class="fi fi-sr-edit text-white text-2xl"></i>
          </div>
          <div>
            <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent">Edit Bid Details</h2>
            <p class="text-sm text-gray-600 mt-1 flex items-center gap-2">
              <i class="fi fi-rr-document-signed text-amber-500 text-xs"></i>
              <span>Update bid information and project details</span>
            </p>
          </div>
        </div>
        <button id="closeEditBidModal" class="text-gray-400 hover:text-gray-600 p-2.5 rounded-xl hover:bg-white hover:shadow-md transition-all">
          <i class="fi fi-rr-cross text-xl"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="px-6 sm:px-8 py-6 overflow-y-auto view-modal-content flex-1 space-y-6">
        <!-- Bid Status Badge -->
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl border border-indigo-200">
          <div class="flex items-center gap-3">
            <i class="fi fi-rr-info text-indigo-600 text-xl"></i>
            <div>
              <div class="text-sm text-gray-600 font-medium">Current Status</div>
              <div id="editCurrentStatus" class="font-bold text-gray-800 text-lg">-</div>
            </div>
          </div>
          <div class="text-sm text-gray-600">
            <span class="font-medium">Bid ID:</span> <span id="editBidId" class="font-bold text-gray-800">-</span>
          </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="rounded-xl border-2 border-indigo-200 p-5 bg-gradient-to-br from-white to-indigo-50 shadow-md">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-lg">
              <i class="fi fi-rr-building text-indigo-500"></i>
              <span>Bidder Information</span>
            </h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Company Name</label>
                <input type="text" id="editBidderCompany" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" placeholder="Enter company name">
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Email Address</label>
                <input type="email" id="editBidderEmail" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" placeholder="company@example.com">
              </div>
            </div>
          </div>
          <div class="rounded-xl border-2 border-purple-200 p-5 bg-gradient-to-br from-white to-purple-50 shadow-md">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-lg">
              <i class="fi fi-rr-home text-purple-500"></i>
              <span>Project Information</span>
            </h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Project Title</label>
                <input type="text" id="editProjectTitle" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" placeholder="Enter project title">
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Property Type</label>
                <select id="editPropertyType" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                  <option value="">Select property type</option>
                  <option value="Residential">Residential</option>
                  <option value="Commercial">Commercial</option>
                  <option value="Industrial">Industrial</option>
                  <option value="Mixed-Use">Mixed-Use</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Bid Details -->
        <div class="rounded-xl border-2 border-green-200 p-6 bg-gradient-to-br from-white to-green-50 shadow-md">
          <h3 class="font-bold text-gray-800 mb-5 flex items-center gap-2 text-lg">
            <i class="fi fi-rr-document text-green-600"></i>
            <span>Bid Details</span>
          </h3>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="space-y-4">
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Proposed Cost (PHP)</label>
                <div class="relative">
                  <input id="editProposedCost" type="text" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 pr-12 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" placeholder="0.00">
                  <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-semibold">PHP</span>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm text-gray-600 mb-1.5 font-medium">Start Date</label>
                  <input id="editStartDate" type="date" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" />
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1.5 font-medium">End Date</label>
                  <input id="editEndDate" type="date" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all" />
                </div>
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1.5 font-medium">Status</label>
                <select id="editStatus" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                  <option value="Under Evaluation">Under Evaluation</option>
                  <option value="Approved">Approved</option>
                  <option value="Rejected">Rejected</option>
                </select>
              </div>
            </div>
            <div class="lg:col-span-2">
              <label class="block text-sm text-gray-600 mb-1.5 font-medium">Description</label>
              <textarea id="editBidDescription" rows="10" class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all resize-none" placeholder="Enter detailed bid description..."></textarea>
            </div>
          </div>
        </div>

        <!-- File Upload Section -->
        <div class="rounded-xl border-2 border-blue-200 p-6 bg-gradient-to-br from-white to-blue-50 shadow-md">
          <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-lg">
            <i class="fi fi-rr-folder-upload text-blue-600"></i>
            <span>Supporting Documents</span>
          </h3>
          <div class="border-2 border-dashed border-blue-300 rounded-xl p-8 text-center hover:border-blue-500 hover:bg-blue-50/50 transition-all cursor-pointer">
            <i class="fi fi-rr-cloud-upload text-blue-500 text-4xl mb-3 block"></i>
            <p class="text-gray-700 font-medium mb-1">Drop files here or click to browse</p>
            <p class="text-gray-500 text-sm">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 10MB)</p>
            <input type="file" class="hidden" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-4 pt-4 border-t-2 border-gray-200">
          <button id="cancelEditBtn" class="px-6 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition-all flex items-center gap-2">
            <i class="fi fi-rr-cross-small"></i>
            <span>Cancel</span>
          </button>
          <button id="saveChangesBtn" class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold hover:from-amber-600 hover:to-orange-700 shadow-lg hover:shadow-xl transition-all flex items-center gap-2">
            <i class="fi fi-rr-disk"></i>
            <span>Save Changes</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Save Confirmation Modal -->
  <div id="saveConfirmModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all scale-95 confirm-modal-panel">
      <div class="p-6">
        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 mx-auto mb-4">
          <i class="fi fi-rr-interrogation text-amber-600 text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 text-center mb-2">Save Changes?</h3>
        <p class="text-gray-600 text-center mb-6">Are you sure you want to save the changes to this bid? This action will update the bid information.</p>
        <div class="flex items-center gap-3">
          <button id="cancelSaveBtn" class="flex-1 px-4 py-3 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 transition-all">
            Cancel
          </button>
          <button id="confirmSaveBtn" class="flex-1 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold hover:from-amber-600 hover:to-orange-700 shadow-lg hover:shadow-xl transition-all">
            Yes, Save
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Bid Modal -->
  <div id="deleteBidModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-[60] p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all scale-95 delete-modal-panel">
      <div class="p-6 sm:p-8">
        <!-- Warning Icon -->
        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-red-100 to-rose-100 mx-auto mb-5 delete-icon-container">
          <i class="fi fi-sr-triangle-warning text-red-600 text-4xl delete-warning-icon"></i>
        </div>
        
        <!-- Title -->
        <h3 class="text-2xl font-bold text-gray-800 text-center mb-3">Delete Bid?</h3>
        
        <!-- Description -->
        <p class="text-gray-600 text-center mb-6 leading-relaxed">
          Are you sure you want to permanently delete this bid? This action cannot be undone and all associated data will be lost.
        </p>
        
        <!-- Bid Details Card -->
        <div class="bg-gradient-to-br from-red-50 to-rose-50 border-2 border-red-200 rounded-xl p-5 mb-6">
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600 font-medium">Bid ID:</span>
              <span id="deleteBidId" class="font-bold text-gray-800">-</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600 font-medium">Project:</span>
              <span id="deleteProjectTitle" class="font-bold text-gray-800 text-right">-</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600 font-medium">Contractor:</span>
              <span id="deleteContractor" class="font-bold text-gray-800 text-right">-</span>
            </div>
          </div>
        </div>
        
        <!-- Warning Message -->
        <div class="flex items-start gap-3 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg mb-6">
          <i class="fi fi-rr-info text-red-600 text-lg mt-0.5"></i>
          <div>
            <h4 class="font-semibold text-red-800 text-sm mb-1">Warning: This action is irreversible!</h4>
            <p class="text-red-700 text-xs leading-relaxed">
              All bid information, documents, and history will be permanently removed from the system.
            </p>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
          <button id="cancelDeleteBtn" class="flex-1 px-5 py-3.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all flex items-center justify-center gap-2">
            <i class="fi fi-rr-cross-small text-lg"></i>
            <span>Cancel</span>
          </button>
          <button id="confirmDeleteBtn" class="flex-1 px-5 py-3.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-semibold hover:from-red-700 hover:to-rose-700 shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2 delete-confirm-btn">
            <i class="fi fi-rr-trash text-lg"></i>
            <span>Yes, Delete</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('js/admin/globalManagement/bidManagement.js') }}" defer></script>

</body>

</html>