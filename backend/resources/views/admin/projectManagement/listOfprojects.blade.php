<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/listOfprojects.css') }}">
  
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
            <a href="{{ route('admin.projectManagement.listOfprojects') }}" class="submenu-link active">List of Projects</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">List of Projects</h1>

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

      <div class="p-8 space-y-6">
        <!-- Controls Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-4 flex-wrap">
            <!-- Verification Status Filter -->
            <div class="relative">
              <select id="verificationFilter" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm font-medium text-gray-700 hover:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition cursor-pointer">
                <option value="all">All Verification Status</option>
                <option value="Approved">Approved</option>
                <option value="Pending">Pending</option>
                <option value="Rejected">Rejected</option>
              </select>
              <i class="fi fi-rr-angle-small-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
            </div>
            <!-- Progress Status Filter -->
            <div class="relative">
              <select id="progressFilter" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm font-medium text-gray-700 hover:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition cursor-pointer">
                <option value="all">All Progress Status</option>
                <option value="Completed">Completed</option>
                <option value="In Bidding">In Bidding</option>
                <option value="Ongoing">Ongoing</option>
                <option value="Halted">Halted</option>
                <option value="Cancelled">Cancelled</option>
              </select>
              <i class="fi fi-rr-angle-small-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
            </div>
            <!-- Date Range Placeholder -->
            <div class="relative">
              <button id="dateRangeBtn" class="flex items-center gap-2 bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400 transition">
                <i class="fi fi-rr-calendar"></i>
                <span id="dateRangeLabel">All Dates</span>
                <i class="fi fi-rr-angle-small-down text-gray-500"></i>
              </button>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <button id="exportProjectsBtn" class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-5 py-2 rounded-lg font-medium text-sm shadow-md hover:shadow-lg transition transform hover:scale-105">
              <i class="fi fi-rr-download"></i>
              <span>Export</span>
            </button>
          </div>
        </div>

        <!-- Projects Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full" id="projectsTable">
              <thead class="bg-gradient-to-r from-orange-50 to-orange-100">
                <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  <th class="px-6 py-4">Property Owner</th>
                  <th class="px-6 py-4">Project ID</th>
                  <th class="px-6 py-4">Verification Status</th>
                  <th class="px-6 py-4">Progress Status</th>
                  <th class="px-6 py-4">Date Submitted</th>
                  <th class="px-6 py-4">Last Updated</th>
                  <th class="px-6 py-4 text-center">Action</th>
                </tr>
              </thead>
              <tbody id="projectsTableBody" class="divide-y divide-gray-200 text-sm">
                <!-- Rows injected by JS -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
  
  <!-- Bidding Details Modal -->
  <div id="biddingDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-start justify-center overflow-y-auto py-10">
      <div class="bg-white w-full max-w-5xl rounded-2xl shadow-xl border border-gray-200 relative">
        <div class="flex items-center justify-between px-8 py-5 border-b bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-t-2xl">
          <h2 class="text-xl font-semibold text-white flex items-center gap-2">
            <i class="fi fi-rr-hammer text-white/90"></i>
            <span>Bidding Details</span>
          </h2>
          <button onclick="hideBiddingModal()" class="w-10 h-10 rounded-xl bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">
            <i class="fi fi-rr-cross-small text-lg"></i>
          </button>
        </div>

        <div class="p-8 space-y-8">
          <!-- Property Owner Info -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-center gap-5">
              <div id="modalOwnerAvatar" class="w-16 h-16 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center overflow-hidden shadow"></div>
              <div>
                <h3 id="modalOwnerName" class="text-lg font-semibold text-gray-800">Owner Name</h3>
                <p class="text-sm text-gray-500">Property Owner • <span id="modalProjectId" class="font-medium text-gray-700"></span></p>
                <p id="modalSubmittedAt" class="text-xs text-gray-400 mt-1"></p>
              </div>
              <div class="ml-auto flex flex-col gap-2">
                <span id="modalVerificationBadge" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold"></span>
                <span id="modalProgressBadge" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold"></span>
              </div>
            </div>
          </div>

          <!-- Project Details -->
          <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fi fi-rr-layer-plus text-indigo-600"></i>
                Project Details
              </h3>
              <div class="space-y-3 text-sm">
                <p><span class="text-gray-500">Property Type:</span> <span id="modalPropertyType" class="font-medium text-gray-800"></span></p>
                <p><span class="text-gray-500">Address:</span> <span id="modalAddress" class="font-medium text-gray-800"></span></p>
                <p><span class="text-gray-500">Lot Size:</span> <span id="modalLotSize" class="font-medium text-gray-800"></span></p>
                <p><span class="text-gray-500">Timeline:</span> <span id="modalTimeline" class="font-medium text-gray-800"></span></p>
                <p><span class="text-gray-500">Budget:</span> <span id="modalBudget" class="font-medium text-gray-800"></span></p>
                <p><span class="text-gray-500">Deadline:</span> <span id="modalDeadline" class="font-medium text-gray-800"></span></p>
              </div>
              <div>
                <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Description</h4>
                <p id="modalDescription" class="text-sm text-gray-700 leading-relaxed line-clamp-5"></p>
              </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fi fi-rr-clock-three text-indigo-600"></i>
                Bidding Summary
              </h3>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-indigo-50 rounded-lg p-3">
                  <p class="text-xs text-gray-500">Start Date</p>
                  <p id="modalBidStart" class="font-semibold text-indigo-700"></p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-3">
                  <p class="text-xs text-gray-500">End Date</p>
                  <p id="modalBidEnd" class="font-semibold text-indigo-700"></p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-3">
                  <p class="text-xs text-gray-500">Status</p>
                  <p id="modalBidStatus" class="font-semibold text-indigo-700"></p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-3">
                  <p class="text-xs text-gray-500">Winning Bidder</p>
                  <p id="modalWinningBidder" class="font-semibold text-indigo-700"></p>
                </div>
              </div>
              <div>
                <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Uploaded Files</h4>
                <div id="modalFiles" class="flex flex-wrap gap-2"></div>
              </div>
            </div>
          </div>

          <!-- Submitted Bids -->
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b">
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
                <i class="fi fi-rr-handshake text-indigo-600"></i>
                Submitted Bids
              </h3>
              <button id="exportBidsBtn" class="flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-medium shadow-md transition">
                <i class="fi fi-rr-download"></i>
                <span>Export Bids</span>
              </button>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-indigo-50">
                  <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-6 py-3">Bidder</th>
                    <th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Duration</th>
                    <th class="px-6 py-3">Submitted</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-center">Action</th>
                  </tr>
                </thead>
                <tbody id="bidsTableBody" class="divide-y divide-gray-200"></tbody>
              </table>
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="flex justify-end gap-3 pt-4">
            <button onclick="hideBiddingModal()" class="px-5 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bid Status Modal -->
  <div id="bidStatusModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-center justify-center overflow-y-auto p-4">
      <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl relative my-8">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white rounded-t-xl">
          <h2 class="text-base font-semibold text-gray-900">Bid Status</h2>
          <button onclick="hideBidStatusModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div class="p-6 space-y-5 max-h-[calc(100vh-12rem)] overflow-y-auto">
          <!-- Status Badge -->
          <div class="flex items-center gap-3">
            <span class="text-sm text-gray-600 font-medium">Status:</span>
            <span id="bidStatusBadge" class="px-3 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-800">Under Evaluation</span>
          </div>

          <!-- Two Column Layout -->
          <div class="grid md:grid-cols-2 gap-5">
            <!-- Left Column: Bidder Information -->
            <div class="space-y-4">
              <h3 class="text-sm font-semibold text-gray-900 pb-2 border-b border-gray-200">Bidder Information</h3>
              <div class="space-y-3">
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Company Name:</span>
                  <span id="bidCompanyName" class="text-sm font-medium text-gray-900">Panda Construction Company</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Email Address:</span>
                  <span id="bidEmail" class="text-sm font-medium text-gray-900">pandaconstruction@gmail.com</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">PCAB No.:</span>
                  <span id="bidPcabNo" class="text-sm font-medium text-gray-900">12345 AB-2026</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">PCAB Category:</span>
                  <span id="bidPcabCategory" class="text-sm font-medium text-gray-900">Category B</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">PCAB Expiration Date:</span>
                  <span id="bidPcabExpiry" class="text-sm font-medium text-gray-900">August 13, 2026</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Business Permit No.:</span>
                  <span id="bidBusinessPermit" class="text-sm font-medium text-gray-900">BP-2024-12345</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Permit City:</span>
                  <span id="bidPermitCity" class="text-sm font-medium text-gray-900">Zamboanga City</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Business Permit Expiration:</span>
                  <span id="bidBusinessPermitExpiry" class="text-sm font-medium text-gray-900">December 31, 2025</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">TIN Registration number:</span>
                  <span id="bidTin" class="text-sm font-medium text-gray-900">123-456-789-000</span>
                </div>
              </div>
            </div>

            <!-- Right Column: Project Information -->
            <div class="space-y-4">
              <h3 class="text-sm font-semibold text-gray-900 pb-2 border-b border-gray-200">Project Information</h3>
              <div class="space-y-3">
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Project Title:</span>
                  <span id="bidProjectTitle" class="text-sm font-medium text-gray-900">Greenfield Commercial Complex</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Project Address:</span>
                  <span id="bidProjectAddress" class="text-sm font-medium text-gray-900">Tetuan Zamboanga City 7000</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Project Type:</span>
                  <span id="bidProjectType" class="text-sm font-medium text-gray-900">Residential</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Lot Size (sqm):</span>
                  <span id="bidLotSize" class="text-sm font-medium text-gray-900">3000</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Project Timeline:</span>
                  <span id="bidProjectTimeline" class="text-sm font-medium text-gray-900">3000</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Budget:</span>
                  <span id="bidProjectBudget" class="text-sm font-medium text-gray-900">PHP 1,000,000</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Bidding Deadline:</span>
                  <span id="bidDeadline" class="text-sm font-medium text-gray-900">November 20, 2025</span>
                </div>
                <div class="flex flex-col gap-1">
                  <span class="text-xs text-gray-500">Uploaded Photos:</span>
                  <div id="bidUploadedPhotos" class="flex flex-wrap gap-2 mt-1">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-50 text-blue-700 text-xs">
                      <i class="fi fi-rr-picture text-xs"></i>
                      sample_photo.jpeg
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bid Details Section -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-900 pb-2 border-b border-gray-200">Bid Details</h3>
            <div class="grid md:grid-cols-2 gap-4">
              <div class="space-y-3">
                <div class="flex flex-col">
                  <label class="text-xs text-gray-500 mb-1.5">Proposed Cost (PHP)</label>
                  <input type="text" id="bidProposedCost" readonly class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-900 focus:outline-none" value="3,500,000">
                </div>
                <div class="flex flex-col">
                  <label class="text-xs text-gray-500 mb-1.5">Proposed Duration</label>
                  <div class="grid grid-cols-2 gap-2">
                    <input type="text" id="bidDurationStart" readonly placeholder="mm / dd / yy" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-900 focus:outline-none" value="">
                    <input type="text" id="bidDurationEnd" readonly placeholder="mm / dd / yy" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-900 focus:outline-none" value="">
                  </div>
                </div>
              </div>
              <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1.5">Description</label>
                <textarea id="bidDescription" readonly class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-gray-50 resize-none focus:outline-none text-gray-900" style="height: 125px;" placeholder="Write a compelling message to the client, Tell them about your expertise and why you're a great fit.">A well-organized project schedule, detailing key milestones and completion timelines.</textarea>
              </div>
            </div>
          </div>

          <!-- Supporting Files Section -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-900 pb-2 border-b border-gray-200">
              <span>Supporting Files</span>
            </h3>
            <div class="rounded-lg border border-gray-200 overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr class="text-left text-xs text-gray-600 font-medium">
                    <th class="px-4 py-3">Files</th>
                    <th class="px-4 py-3">Date Submitted</th>
                    <th class="px-4 py-3">User Name</th>
                    <th class="px-4 py-3">Position</th>
                    <th class="px-4 py-3 text-center">Action</th>
                  </tr>
                </thead>
                <tbody id="bidSupportingFilesTable" class="divide-y divide-gray-200 bg-white">
                  <!-- Rows injected by JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-xl">
          <div class="flex justify-end">
            <button onclick="hideBidStatusModal()" class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Accept Bid Confirmation Modal -->
  <div id="acceptBidModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative transform transition-all">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Accept Bid</h3>
          </div>
          <button onclick="hideAcceptBidModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600 leading-relaxed">
            Are you sure you want to accept this bid from <span id="acceptBidCompany" class="font-semibold text-gray-900"></span>?
          </p>
          <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Bid Amount:</span>
              <span id="acceptBidAmount" class="font-semibold text-gray-900"></span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Duration:</span>
              <span id="acceptBidDuration" class="font-semibold text-gray-900"></span>
            </div>
          </div>
          <p class="text-xs text-gray-500">
            <strong>Note:</strong> Accepting this bid will automatically update the bidding status and notify the contractor.
          </p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
          <button onclick="hideAcceptBidModal()" class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
            Cancel
          </button>
          <button onclick="confirmAcceptBid()" class="px-4 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-md">
            Accept Bid
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Reject Bid Confirmation Modal -->
  <div id="rejectBidModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative transform transition-all">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Reject Bid</h3>
          </div>
          <button onclick="hideRejectBidModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600 leading-relaxed">
            Are you sure you want to reject this bid from <span id="rejectBidCompany" class="font-semibold text-gray-900"></span>?
          </p>
          <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Bid Amount:</span>
              <span id="rejectBidAmount" class="font-semibold text-gray-900"></span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Duration:</span>
              <span id="rejectBidDuration" class="font-semibold text-gray-900"></span>
            </div>
          </div>
          <div class="space-y-2">
            <label class="text-xs font-medium text-gray-700">Reason for Rejection (Optional)</label>
            <textarea id="rejectReason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none" placeholder="Provide a reason for rejecting this bid..."></textarea>
          </div>
          <p class="text-xs text-gray-500">
            <strong>Note:</strong> This action cannot be undone. The contractor will be notified of the rejection.
          </p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
          <button onclick="hideRejectBidModal()" class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
            Cancel
          </button>
          <button onclick="confirmRejectBid()" class="px-4 py-2.5 text-sm font-medium rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition shadow-md">
            Reject Bid
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Ongoing Project Modal -->
  <div id="ongoingProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-start justify-center overflow-y-auto py-8 px-4">
      <div class="bg-white w-full max-w-6xl rounded-2xl shadow-2xl relative my-4 transform transition-all duration-300 scale-100 hover:shadow-3xl">
        <!-- Header with Owner Info -->
        <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600 px-6 py-5 rounded-t-2xl relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
          <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-4">
              <div id="ongoingOwnerAvatar" class="w-14 h-14 rounded-full bg-white flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-white/30 transition-transform duration-300 hover:scale-110"></div>
              <div class="text-white">
                <h3 id="ongoingOwnerName" class="text-lg font-bold tracking-wide">Property Owner</h3>
                <p class="text-xs opacity-90 flex items-center gap-2">
                  <span class="inline-block w-2 h-2 bg-green-300 rounded-full animate-pulse"></span>
                  Ongoing Project
                </p>
              </div>
            </div>
            <button onclick="hideOngoingProjectModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="p-6 space-y-6 max-h-[calc(100vh-12rem)] overflow-y-auto">
          <!-- Project Details and Contractor Details (Row) -->
          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Project Details -->
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 space-y-3 hover:shadow-lg transition-all duration-300 hover:border-amber-300">
              <h3 class="font-bold text-gray-900 text-sm border-b-2 border-amber-400 pb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Project Details
              </h3>
              <div class="space-y-2 text-sm">
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-amber-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Project Title</span> <span id="ongoingProjectTitle" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-amber-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Property Address</span> <span id="ongoingProjectAddress" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-amber-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Property Type</span> <span id="ongoingProjectType" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-amber-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Lot Size (sqm)</span> <span id="ongoingLotSize" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-amber-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Target Timeline</span> <span id="ongoingTimeline" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-amber-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Budget</span> <span id="ongoingBudget" class="font-semibold text-amber-600">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-amber-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Bidding Deadline</span> <span id="ongoingDeadline" class="font-semibold text-gray-900">—</span></p>
              </div>
              <div>
                <span class="text-xs text-gray-500 block mb-2">Uploaded Photos</span>
                <div id="ongoingPhotos" class="flex flex-wrap gap-1"></div>
              </div>
              <div>
                <span class="text-xs text-gray-500 block mb-2">Supporting Files</span>
                <div id="ongoingFiles" class="flex flex-wrap gap-1"></div>
              </div>
            </div>

            <!-- Contractor Details -->
            <div class="bg-gradient-to-br from-white to-blue-50 border border-gray-200 rounded-xl p-5 space-y-3 hover:shadow-lg transition-all duration-300 hover:border-blue-300">
              <div class="flex items-center justify-between border-b-2 border-blue-400 pb-2">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                  <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                  Contractor Details
                </h3>
                <button onclick="viewOngoingBidDetails()" class="text-amber-600 hover:text-amber-700 hover:scale-105 transition-transform text-xs font-semibold flex items-center gap-1">
                  View Details
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
              </div>
              <div class="space-y-2 text-sm">
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-blue-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Company Name</span> <span id="ongoingContractorName" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-blue-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Email Address</span> <span id="ongoingContractorEmail" class="font-semibold text-blue-600">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-blue-50 transition-colors duration-200"><span class="text-gray-600 font-medium">PCAB No.</span> <span id="ongoingContractorPcab" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-blue-50 transition-colors duration-200"><span class="text-gray-600 font-medium">PCAB Category</span> <span id="ongoingContractorCategory" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-blue-50 transition-colors duration-200"><span class="text-gray-600 font-medium">PCAB Expiration Date</span> <span id="ongoingContractorPcabExpiry" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-blue-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Business Permit No.</span> <span id="ongoingContractorPermit" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-blue-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Permit City</span> <span id="ongoingContractorCity" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-blue-50 transition-colors duration-200"><span class="text-gray-600 font-medium">Business Permit Expiration</span> <span id="ongoingContractorPermitExpiry" class="font-semibold text-gray-900">—</span></p>
                <p class="flex justify-between items-center py-1 px-2 rounded hover:bg-blue-50 transition-colors duration-200"><span class="text-gray-600 font-medium">TIN Registration number</span> <span id="ongoingContractorTin" class="font-semibold text-gray-900">—</span></p>
              </div>
            </div>
          </div>

          <!-- Bidding Summary (Row) -->
          <div class="bg-gradient-to-br from-white to-purple-50 border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300 hover:border-purple-300">
            <h3 class="font-bold text-gray-900 text-sm border-b-2 border-purple-400 pb-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              Bidding Summary
            </h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <div class="bg-white rounded-lg p-4 border border-purple-100 hover:border-purple-300 hover:shadow-md transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs text-gray-500 font-medium">Total Bids</span>
                  <svg class="w-5 h-5 text-purple-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                  </svg>
                </div>
                <span id="ongoingTotalBids" class="text-2xl font-bold text-gray-900 block">—</span>
              </div>
              <div class="bg-white rounded-lg p-4 border border-green-100 hover:border-green-300 hover:shadow-md transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs text-gray-500 font-medium">Status</span>
                  <svg class="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <span id="ongoingBidStatus" class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-300">—</span>
              </div>
              <div class="bg-white rounded-lg p-4 border border-amber-100 hover:border-amber-300 hover:shadow-md transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs text-gray-500 font-medium">Winning Bidder</span>
                  <svg class="w-5 h-5 text-amber-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                  </svg>
                </div>
                <span id="ongoingWinningBidder" class="text-sm font-bold text-amber-600">—</span>
              </div>
              <div class="bg-white rounded-lg p-4 border border-blue-100 hover:border-blue-300 hover:shadow-md transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs text-gray-500 font-medium">Start Date</span>
                  <svg class="w-5 h-5 text-blue-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <span id="ongoingBidStart" class="text-sm font-semibold text-gray-900">—</span>
              </div>
              <div class="bg-white rounded-lg p-4 border border-red-100 hover:border-red-300 hover:shadow-md transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs text-gray-500 font-medium">End Date</span>
                  <svg class="w-5 h-5 text-red-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <span id="ongoingBidEnd" class="text-sm font-semibold text-gray-900">—</span>
              </div>
              <div class="bg-white rounded-lg p-4 border border-indigo-100 hover:border-indigo-300 hover:shadow-md transition-all duration-200 cursor-pointer group">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs text-gray-500 font-medium">Last Update</span>
                  <svg class="w-5 h-5 text-indigo-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <span id="ongoingLastUpdate" class="text-sm font-semibold text-gray-900">—</span>
              </div>
            </div>
          </div>

          <!-- Project's Milestone and Details (Row) -->
          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Project's Milestone -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <h3 class="font-bold text-gray-900 text-base pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Project's Milestone
              </h3>
              <div id="ongoingMilestoneTimeline" class="space-y-0">
                <!-- Milestone items will be injected by JS -->
              </div>
            </div>

            <!-- Details -->
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                  <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Details
                </h3>
                <button onclick="openEditMilestoneModal()" class="text-amber-600 hover:text-amber-700 hover:scale-105 transition-transform text-xs font-semibold flex items-center gap-1" title="Edit Details">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                </button>
              </div>
              <div id="ongoingDetails" class="space-y-3">
                <div class="text-sm text-gray-500 text-center py-8">Select a milestone to view details</div>
              </div>
            </div>
          </div>

          <!-- Payment Summary (Row) -->
          <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
            <div class="flex items-center justify-between border-b-2 border-amber-400 pb-3">
              <div>
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Payment Summary
                </h3>
                <p class="text-xs text-gray-500 mt-1">This section contains uploaded receipts and payment confirmations related to completed milestones</p>
              </div>
            </div>
            
            <!-- Stats grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-4 border border-amber-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Milestones Paid</p>
                  <svg class="w-5 h-5 text-amber-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="ongoingPaidCount" class="text-xl font-bold text-gray-900">—</p>
              </div>
              <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-4 border border-amber-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Amount Paid</p>
                  <svg class="w-5 h-5 text-amber-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="ongoingTotalAmount" class="text-xl font-bold text-amber-600">—</p>
              </div>
              <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-4 border border-amber-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Last Payment Date</p>
                  <svg class="w-5 h-5 text-amber-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <p id="ongoingLastPaymentDate" class="text-sm font-semibold text-gray-900">—</p>
              </div>
              <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-4 border border-amber-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Over All Payment Status</p>
                  <svg class="w-5 h-5 text-amber-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="ongoingOverallStatus" class="text-sm font-bold text-amber-600">—</p>
              </div>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-200">
                  <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone Period</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Amount Paid</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Date of Payment</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Uploaded By</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Proof of Payment</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Verification Status</th>
                  </tr>
                </thead>
                <tbody id="ongoingPaymentTable" class="divide-y divide-gray-200 bg-white">
                  <!-- Rows injected by JS -->
                </tbody>
              </table>
            </div>
          </div>

        
        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl flex justify-end gap-3">
          <button onclick="hideOngoingProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </button>
        </div>

        </div>
      </div>
    </div>
  </div>


  <!-- Progress Report Detail Modal -->
  <div id="progressReportModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
            Progress Report Detail
            <button id="editReportBtn" class="text-amber-500 hover:text-amber-600 transition-colors" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
              </svg>
            </button>
          </h2>
          <button onclick="hideProgressReportModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-rose-500 hover:text-rose-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-5 max-h-[calc(100vh-12rem)] overflow-y-auto">
          <!-- Report Info -->
          <div>
            <h3 id="reportTitle" class="text-base font-bold text-gray-900 mb-1">Rooftop Building Progress Report</h3>
            <p id="reportDate" class="text-sm text-gray-500 mb-3">12 Dec 9:00 PM</p>
            <p id="reportDescription" class="text-sm text-gray-600 leading-relaxed">
              People care about how you see the world, how you think, what motivates you, what you're struggling with or afraid of.People care about how you see the world, how you think, what motivates you, what you're struggling with or afraid of.
            </p>
          </div>

          <!-- File History -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-sm font-bold text-gray-900">File History</h4>
                <p class="text-xs text-gray-500">Download your previous plan receipts and usage details.</p>
              </div>
              <button id="downloadAllBtn" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                Download all
              </button>
            </div>

            <!-- File History Table -->
            <div class="rounded-lg border border-gray-200 overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr class="text-left">
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600 w-10">
                      <input type="checkbox" id="selectAllFiles" class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500 cursor-pointer">
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600">Files</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600">Date Submitted</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600">Uploaded By</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600 text-center">Action</th>
                  </tr>
                </thead>
                <tbody id="fileHistoryTable" class="divide-y divide-gray-200 bg-white">
                  <!-- Rows will be injected by JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end">
          <button onclick="hideProgressReportModal()" class="px-5 py-2.5 text-sm font-medium rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Completed Project Modal -->
  <div id="completedProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center overflow-y-auto py-8 px-4">
      <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl relative my-4 transform transition-all duration-300 scale-100">
        <!-- Header with Owner Info -->
        <div class="bg-gradient-to-r from-green-500 via-emerald-500 to-green-600 px-6 py-5 rounded-t-2xl relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
          <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-4">
              <div id="completedOwnerAvatar" class="w-14 h-14 rounded-full bg-white flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-white/30 transition-transform duration-300 hover:scale-110"></div>
              <div class="text-white">
                <h3 id="completedOwnerName" class="text-lg font-bold tracking-wide">Property Owner</h3>
                <p class="text-xs opacity-90 flex items-center gap-2">
                  <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                    Completed
                  </span>
                  <span id="completedDate" class="text-white/90">November 20, 2025</span>
                </p>
              </div>
            </div>
            <button onclick="hideCompletedProjectModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="p-6 space-y-6 max-h-[calc(100vh-12rem)] overflow-y-auto">
          <!-- Success Message -->
          <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-6 text-center">
            <div class="flex justify-center mb-4">
              <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">This project has been successfully COMPLETED</h3>
            <p class="text-sm text-gray-600 italic mb-4">All milestones verified and marked as completed.</p>
            
            <button onclick="toggleCompletedDetails()" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              View Details
            </button>
          </div>

          <!-- Project Details and Contractor Details (2-Column) -->
          <div id="completedDetailsSection" class="grid lg:grid-cols-2 gap-6">
            <!-- Project Details -->
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 space-y-3 hover:shadow-lg transition-all duration-300">
              <h3 class="font-bold text-gray-900 text-base border-b-2 border-green-400 pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Project Details
              </h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Project Title</span>
                  <span id="completedProjectTitle" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Propert Address</span>
                  <span id="completedProjectAddress" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Propert Type:</span>
                  <span id="completedProjectType" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Lot Size (sqm)</span>
                  <span id="completedLotSize" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Target Timeline</span>
                  <span id="completedTimeline" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Budget</span>
                  <span id="completedBudget" class="font-semibold text-green-600 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Bidding Deadline</span>
                  <span id="completedDeadline" class="font-semibold text-gray-900 text-right">—</span>
                </div>
              </div>
              <div>
                <span class="text-xs text-gray-500 block mb-2">Uploaded Photos</span>
                <div id="completedPhotos" class="flex flex-wrap gap-2"></div>
              </div>
              <div>
                <span class="text-xs text-gray-500 block mb-2">Supporting Files</span>
                <div id="completedFiles" class="flex flex-wrap gap-2"></div>
              </div>
            </div>

            <!-- Contractor Details -->
            <div class="bg-gradient-to-br from-white to-blue-50 border border-gray-200 rounded-xl p-5 space-y-3 hover:shadow-lg transition-all duration-300">
              <h3 class="font-bold text-gray-900 text-base border-b-2 border-blue-400 pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Contractor Details
              </h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Company Name :</span>
                  <span id="completedContractorName" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Email Address :</span>
                  <span id="completedContractorEmail" class="font-semibold text-blue-600 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">PCAB No.:</span>
                  <span id="completedContractorPcab" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">PCAB Category:</span>
                  <span id="completedContractorCategory" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">PCAB Expiration Date</span>
                  <span id="completedContractorPcabExpiry" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Business Permit No.:</span>
                  <span id="completedContractorPermit" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Permit City:</span>
                  <span id="completedContractorCity" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Business Permit Expiration</span>
                  <span id="completedContractorPermitExpiry" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">TIN Registration number</span>
                  <span id="completedContractorTin" class="font-semibold text-gray-900 text-right">—</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Project's Milestone and Details (Row) -->
          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Project's Milestone -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <h3 class="font-bold text-gray-900 text-base pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Project's Milestone
              </h3>
              <div id="completedMilestoneTimeline" class="space-y-0">
                <!-- Milestone items will be injected by JS -->
              </div>
            </div>

            <!-- Details -->
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                  <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Details
                </h3>
                <button onclick="openEditCompletedMilestoneModal()" class="text-amber-600 hover:text-amber-700 hover:scale-105 transition-transform text-xs font-semibold flex items-center gap-1" title="Edit Details">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                </button>
              </div>
              <div id="completedDetails" class="space-y-3">
                <div class="text-sm text-gray-500 text-center py-8">Select a milestone to view details</div>
              </div>
            </div>
          </div>

          <!-- Payment Summary (Row) -->
          <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
            <div class="flex items-center justify-between border-b-2 border-green-400 pb-3">
              <div>
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Payment Summary
                </h3>
                <p class="text-xs text-gray-500 mt-1">This section contains uploaded receipts and payment confirmations related to completed milestones</p>
              </div>
            </div>
            
            <!-- Stats grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Milestones Paid</p>
                  <svg class="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="completedPaidCount" class="text-xl font-bold text-gray-900">—</p>
              </div>
              <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Amount Paid</p>
                  <svg class="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="completedTotalAmount" class="text-xl font-bold text-green-600">—</p>
              </div>
              <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Last Payment Date</p>
                  <svg class="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <p id="completedLastPaymentDate" class="text-sm font-semibold text-gray-900">—</p>
              </div>
              <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Over All Payment Status</p>
                  <svg class="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="completedOverallStatus" class="text-sm font-bold text-green-600">—</p>
              </div>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-200">
                  <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone Period</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Amount Paid</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Date of Payment</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Uploaded By</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Proof of Payment</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Verification Status</th>
                  </tr>
                </thead>
                <tbody id="completedPaymentTable" class="divide-y divide-gray-200 bg-white">
                  <!-- Rows injected by JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl flex justify-end gap-3">
          <button onclick="hideCompletedProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Halted Project Modal -->
  <div id="haltedProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center overflow-y-auto py-8 px-4">
      <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl relative my-4 transform transition-all duration-300 scale-100">
        <!-- Header with Owner Info -->
        <div class="bg-gradient-to-r from-rose-500 via-red-500 to-rose-600 px-6 py-5 rounded-t-2xl relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
          <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-4">
              <div id="haltedOwnerAvatar" class="w-14 h-14 rounded-full bg-white flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-white/30 transition-transform duration-300 hover:scale-110"></div>
              <div class="text-white">
                <h3 id="haltedOwnerName" class="text-lg font-bold tracking-wide">Property Owner</h3>
                <p class="text-xs opacity-90 flex items-center gap-2">
                  <span class="inline-flex items-center gap-1 bg-rose-100 text-rose-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                    Halted
                  </span>
                  <span id="haltedDate" class="text-white/90">November 20, 2025</span>
                </p>
              </div>
            </div>
            <button onclick="hideHaltedProjectModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="p-6 space-y-6 max-h-[calc(100vh-12rem)] overflow-y-auto">
          <!-- Halted Message -->
          <div class="bg-gradient-to-br from-rose-50 to-red-50 border-2 border-rose-200 rounded-xl p-6 text-center">
            <div class="flex justify-center mb-4">
              <div class="w-20 h-20 rounded-full bg-rose-100 flex items-center justify-center">
                <svg class="w-12 h-12 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">This project is currently HALTED</h3>
            <p class="text-sm text-gray-600 italic mb-4" id="haltedReason">Reason: Pending payment verification. Work will resume once cleared.</p>
            
            <button onclick="showHaltDetailsModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              View Halt Details
            </button>
          </div>

          <!-- Project Details and Contractor Details (2-Column) -->
          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Project Details -->
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 space-y-3 hover:shadow-lg transition-all duration-300">
              <h3 class="font-bold text-gray-900 text-base border-b-2 border-rose-400 pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Project Details
              </h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Project Title</span>
                  <span id="haltedProjectTitle" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Property Address</span>
                  <span id="haltedProjectAddress" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Property Type:</span>
                  <span id="haltedProjectType" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Lot Size (sqm)</span>
                  <span id="haltedLotSize" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Target Timeline</span>
                  <span id="haltedTimeline" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Budget</span>
                  <span id="haltedBudget" class="font-semibold text-rose-600 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Bidding Deadline</span>
                  <span id="haltedDeadline" class="font-semibold text-gray-900 text-right">—</span>
                </div>
              </div>
              <div>
                <span class="text-xs text-gray-500 block mb-2">Uploaded Photos</span>
                <div id="haltedPhotos" class="flex flex-wrap gap-2"></div>
              </div>
              <div>
                <span class="text-xs text-gray-500 block mb-2">Supporting Files</span>
                <div id="haltedFiles" class="flex flex-wrap gap-2"></div>
              </div>
            </div>

            <!-- Contractor Details -->
            <div class="bg-gradient-to-br from-white to-blue-50 border border-gray-200 rounded-xl p-5 space-y-3 hover:shadow-lg transition-all duration-300">
              <h3 class="font-bold text-gray-900 text-base border-b-2 border-blue-400 pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Contractor Details
              </h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Company Name :</span>
                  <span id="haltedContractorName" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Email Address :</span>
                  <span id="haltedContractorEmail" class="font-semibold text-blue-600 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">PCAB No.:</span>
                  <span id="haltedContractorPcab" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">PCAB Category:</span>
                  <span id="haltedContractorCategory" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">PCAB Expiration Date</span>
                  <span id="haltedContractorPcabExpiry" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Business Permit No.:</span>
                  <span id="haltedContractorPermit" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Permit City:</span>
                  <span id="haltedContractorCity" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Business Permit Expiration</span>
                  <span id="haltedContractorPermitExpiry" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">TIN Registration number</span>
                  <span id="haltedContractorTin" class="font-semibold text-gray-900 text-right">—</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Project's Milestone and Details (Row) -->
          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Project's Milestone -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <h3 class="font-bold text-gray-900 text-base pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Project's Milestone
              </h3>
              <div id="haltedMilestoneTimeline" class="space-y-0">
                <!-- Milestone items will be injected by JS -->
              </div>
            </div>

            <!-- Details -->
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                  <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Details
                </h3>
                <button onclick="openEditHaltedMilestoneModal()" class="text-amber-600 hover:text-amber-700 hover:scale-105 transition-transform text-xs font-semibold flex items-center gap-1" title="Edit Details">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                </button>
              </div>
              <div id="haltedDetails" class="space-y-3">
                <div class="text-sm text-gray-500 text-center py-8">Select a milestone to view details</div>
              </div>
            </div>
          </div>

          <!-- Payment Summary (Row) -->
          <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
            <div class="flex items-center justify-between border-b-2 border-rose-400 pb-3">
              <div>
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Payment Summary
                </h3>
                <p class="text-xs text-gray-500 mt-1">This section contains uploaded receipts and payment confirmations related to completed milestones</p>
              </div>
            </div>
            <!-- Stats grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-gradient-to-br from-rose-50 to-red-50 rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Milestones Paid</p>
                  <svg class="w-5 h-5 text-rose-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="haltedPaidCount" class="text-xl font-bold text-gray-900">—</p>
              </div>
              <div class="bg-gradient-to-br from-rose-50 to-red-50 rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Amount Paid</p>
                  <svg class="w-5 h-5 text-rose-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="haltedTotalAmount" class="text-xl font-bold text-rose-600">—</p>
              </div>
              <div class="bg-gradient-to-br from-rose-50 to-red-50 rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Last Payment Date</p>
                  <svg class="w-5 h-5 text-rose-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <p id="haltedLastPaymentDate" class="text-sm font-semibold text-gray-900">—</p>
              </div>
              <div class="bg-gradient-to-br from-rose-50 to-red-50 rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Over All Payment Status</p>
                  <svg class="w-5 h-5 text-rose-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="haltedOverallStatus" class="text-sm font-bold text-rose-600">—</p>
              </div>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-rose-50 to-red-50 border-b border-rose-200">
                  <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone Period</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Amount Paid</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Date of Payment</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Uploaded By</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Proof of Payment</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Verification Status</th>
                  </tr>
                </thead>
                <tbody id="haltedPaymentTable" class="divide-y divide-gray-200 bg-white">
                  <!-- Rows injected by JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl flex justify-end gap-3">
          <button onclick="hideHaltedProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Halt Details Modal -->
  <div id="haltDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="px-6 py-5 flex-shrink-0 relative border-b border-gray-200 bg-gradient-to-r from-rose-50 to-red-50 rounded-t-2xl">
          <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center shadow-xl ring-4 ring-white/50">
                <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div>
                <h2 class="text-xl font-bold tracking-wide text-gray-900">Halt Details</h2>
                <p class="text-xs text-gray-500">Administrative information for halted project status</p>
              </div>
            </div>
            <button onclick="hideHaltDetailsModal()" class="w-10 h-10 rounded-xl hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition-all duration-200 text-rose-600 hover:rotate-90 transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6 overflow-y-auto flex-1">
          <div class="space-y-6">
            <!-- Primary Fields -->
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Initiated By</label>
                  <p id="haltInitiatedBy" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Cause of Halt</label>
                  <p id="haltCauseOfHalt" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Reason of Halt</label>
                  <p id="haltReasonOfHalt" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide mb-1">Remarks</label>
                  <textarea id="haltRemarks" rows="5" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none" placeholder="Add administrative remarks about the halt..."></textarea>
                </div>
              </div>
              <div class="space-y-4">
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Date of Halt Notice</label>
                  <p id="haltNoticeDate" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Affected Milestone</label>
                  <p id="haltAffectedMilestone" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Status of Issue</label>
                  <p id="haltIssueStatus" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Expected Resolution Date</label>
                  <p id="haltExpectedResolutionDate" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
              </div>
            </div>

            <!-- Supporting Files -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-gray-900">Supporting Files</h4>
                  <p class="text-xs text-gray-500">Documentation provided for administrative review.</p>
                </div>
                <button onclick="downloadHaltSupportingFiles()" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">Download all</button>
              </div>
              <div id="haltSupportingFiles" class="flex flex-col gap-2"></div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
          <button onclick="showCancelHaltConfirm()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition shadow-sm hover:shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Mark as Cancelled
          </button>
          <button onclick="showResumeHaltConfirm()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm hover:shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Resume Project
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Cancel Halt Confirmation Modal -->
  <div id="cancelHaltConfirmModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Cancel Halted Project</h3>
          </div>
          <button onclick="hideCancelHaltConfirm()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600">Are you sure you want to mark this halted project as <span class="font-semibold text-gray-900">cancelled</span>? This action is irreversible and will archive all related data.</p>
          <p class="text-xs text-gray-500"><strong>Note:</strong> Stakeholders will be notified about the cancellation status update.</p>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
          <button onclick="hideCancelHaltConfirm()" class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition">No, Keep Halt</button>
          <button onclick="confirmCancelHalt()" class="px-4 py-2.5 text-sm font-medium rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition shadow-md">Yes, Cancel Project</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Resume Halt Confirmation Modal -->
  <div id="resumeHaltConfirmModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Resume Halted Project</h3>
          </div>
          <button onclick="hideResumeHaltConfirm()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600">Are you sure you want to <span class="font-semibold text-gray-900">resume</span> work on this halted project? Progress tracking and milestones will proceed from the last verified state.</p>
          <p class="text-xs text-gray-500"><strong>Note:</strong> This will notify all project stakeholders that the halt has been lifted.</p>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
          <button onclick="hideResumeHaltConfirm()" class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition">No, Keep Halt</button>
          <button onclick="confirmResumeHalt()" class="px-4 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-md">Yes, Resume Project</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Completion Details Modal -->
  <div id="completionDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 via-emerald-500 to-green-600 px-6 py-5 flex-shrink-0 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
          <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm shadow-xl ring-4 ring-white/30">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="text-white">
                <h2 class="text-xl font-bold tracking-wide">Completion Details</h2>
                <p class="text-xs opacity-90">Project verification and feedback information</p>
              </div>
            </div>
            <button onclick="hideCompletionDetailsModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6 overflow-y-auto flex-1">
          <!-- Completion Details Section -->
          <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div class="bg-gradient-to-br from-white to-green-50 border border-green-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-green-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Project Status</label>
                </div>
                <p id="completionStatus" class="text-sm font-bold text-gray-900 pl-6">Approved - Verified</p>
              </div>
              <div class="bg-gradient-to-br from-white to-green-50 border border-green-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-green-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Date Completed</label>
                </div>
                <p id="completionDateCompleted" class="text-sm font-bold text-gray-900 pl-6">October 8, 2025</p>
              </div>
              <div class="bg-gradient-to-br from-white to-green-50 border border-green-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-green-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Total Duration</label>
                </div>
                <p id="completionDuration" class="text-sm font-bold text-gray-900 pl-6">85 days (Started: August 15, 2025)</p>
              </div>
              <div class="bg-gradient-to-br from-white to-green-50 border border-green-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-green-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                  <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Final Progress</label>
                </div>
                <p id="completionProgress" class="text-sm font-bold text-green-600 pl-6">100%</p>
              </div>
            </div>

            <div class="space-y-4">
              <div class="bg-gradient-to-br from-white to-blue-50 border border-blue-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-blue-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Verified By</label>
                </div>
                <p id="completionVerifiedBy" class="text-sm font-bold text-gray-900 pl-6">Approved - Verified</p>
              </div>
              <div class="bg-gradient-to-br from-white to-blue-50 border border-blue-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-blue-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                  </svg>
                  <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Verification Date</label>
                </div>
                <p id="completionVerificationDate" class="text-sm font-bold text-gray-900 pl-6">Approved - Verified</p>
              </div>
              
              <!-- Additional Info Card -->
              <div class="bg-gradient-to-br from-white to-purple-50 border border-purple-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-start gap-3">
                  <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600 font-semibold mb-1">Project Completion</p>
                    <p class="text-xs text-gray-500 leading-relaxed">All project milestones have been successfully completed and verified by the system administrator.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Feedbacks Section -->
          <div class="border-t-2 border-gray-200 pt-6">
            <div class="flex items-center gap-2 mb-5">
              <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
              </svg>
              <h3 class="text-lg font-bold text-gray-900">Project Feedbacks</h3>
            </div>
            
            <!-- Property Owner Feedback -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-200 rounded-xl p-5 mb-4 hover:shadow-xl transition-all duration-300 hover:border-amber-300">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="font-bold text-gray-900" id="completionOwnerName">Carlos Saludo (Camelia Holmes)</p>
                    <p class="text-xs text-amber-600 font-semibold">Property Owner</p>
                  </div>
                </div>
                <div class="flex items-center gap-1">
                  <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                  </svg>
                  <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                  </svg>
                  <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                  </svg>
                  <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                  </svg>
                  <svg class="w-5 h-5 text-gray-300 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                  </svg>
                </div>
              </div>
              <textarea id="completionOwnerFeedback" readonly class="w-full border border-amber-200 rounded-lg px-4 py-3 text-sm text-gray-700 bg-white resize-none focus:outline-none focus:ring-2 focus:ring-amber-300 transition-all" rows="3" placeholder="No feedback provided">Project cancelled due to unresolved financial verification and lack of response from the property owner. Contractor instructed to withdraw materials and manpower. Project archived for documentation purposes.</textarea>
            </div>

            <!-- Contractor Feedback -->
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-200 rounded-xl p-5 hover:shadow-xl transition-all duration-300 hover:border-blue-300">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                  </div>
                  <div>
                    <p class="font-bold text-gray-900" id="completionContractorName">Carlos Saludo (WaoWao Builders)</p>
                    <p class="text-xs text-blue-600 font-semibold">Contractor</p>
                  </div>
                </div>
                <div class="flex items-center gap-1">
                  <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                  </svg>
                  <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                  </svg>
                  <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                  </svg>
                  <svg class="w-5 h-5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                  </svg>
                  <svg class="w-5 h-5 text-gray-300 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                  </svg>
                </div>
              </div>
              <textarea id="completionContractorFeedback" readonly class="w-full border border-blue-200 rounded-lg px-4 py-3 text-sm text-gray-700 bg-white resize-none focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all" rows="3" placeholder="No feedback provided">Project cancelled due to unresolved financial verification and lack of response from the property owner. Contractor instructed to withdraw materials and manpower. Project archived for documentation purposes.</textarea>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl flex justify-end flex-shrink-0">
          <button onclick="hideCompletionDetailsModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Milestone Details Modal -->
  <div id="editMilestoneModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
          <div>
            <h2 class="text-lg font-bold text-gray-900">Edit Milestone Details</h2>
            <p class="text-xs text-gray-500 mt-0.5">Update the project</p>
          </div>
          <button onclick="hideEditMilestoneModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-rose-500 hover:text-rose-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4 overflow-y-auto flex-1">
          <!-- Milestone Title -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Milestone Title</label>
            <input type="text" id="editMilestoneTitle" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Greenfield Commercial Complex">
          </div>

          <!-- Milestone Description -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Milestone Description</label>
            <textarea id="editMilestoneDescription" rows="5" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none" placeholder="Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing"></textarea>
          </div>

          <!-- Date -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Date</label>
            <input type="text" id="editMilestoneDate" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Greenfield Commercial Complex">
          </div>

          <!-- Uploaded Photos -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Uploaded Photos</label>
            <div id="editMilestonePhotos" class="space-y-2">
              <!-- Photo items will be injected by JS -->
            </div>
            <button onclick="addMilestonePhotoInput()" class="mt-2 text-sm text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Add Photo
            </button>
          </div>

          <!-- Supporting Files -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Supporting Files</label>
            <div id="editMilestoneFiles" class="space-y-2">
              <!-- File items will be injected by JS -->
            </div>
            <button onclick="addMilestoneFileInput()" class="mt-2 text-sm text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Add File
            </button>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end flex-shrink-0">
          <button onclick="saveMilestoneEdit()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white transition shadow-md hover:shadow-lg">
            Done
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Progress Report Details Modal -->
  <div id="editProgressReportModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
          <div>
            <h2 class="text-lg font-bold text-gray-900">Edit Progress Report Details</h2>
            <p class="text-xs text-gray-500 mt-0.5">Update the project</p>
          </div>
          <button onclick="hideEditProgressReportModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-rose-500 hover:text-rose-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4 overflow-y-auto flex-1">
          <!-- Report Title -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Report Title</label>
            <input type="text" id="editReportTitle" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Greenfield Commercial Complex">
          </div>

          <!-- Report Description -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Report Description</label>
            <textarea id="editReportDescription" rows="5" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none" placeholder="Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing"></textarea>
          </div>

          <!-- Date -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Date</label>
            <input type="text" id="editReportDate" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Greenfield Commercial Complex">
          </div>

          <!-- Uploaded Photos -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Uploaded Photos</label>
            <div id="editUploadedPhotos" class="space-y-2">
              <!-- Photo items will be injected by JS -->
            </div>
            <button onclick="addPhotoInput()" class="mt-2 text-sm text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Add Photo
            </button>
          </div>

          <!-- Supporting Files -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Supporting Files</label>
            <div id="editSupportingFiles" class="space-y-2">
              <!-- File items will be injected by JS -->
            </div>
            <button onclick="addFileInput()" class="mt-2 text-sm text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Add File
            </button>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end flex-shrink-0">
          <button onclick="saveProgressReportEdit()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white transition shadow-md hover:shadow-lg">
            Done
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Cancelled Project Modal -->
  <div id="cancelledProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center overflow-y-auto py-8 px-4">
      <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl relative my-4 transform transition-all duration-300 scale-100">
        <!-- Header with Owner Info -->
        <div class="bg-gradient-to-r from-gray-500 via-gray-600 to-gray-700 px-6 py-5 rounded-t-2xl relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
          <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-4">
              <div id="cancelledOwnerAvatar" class="w-14 h-14 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-bold text-lg shadow-lg overflow-hidden">
                PO
              </div>
              <div>
                <h2 id="cancelledOwnerName" class="text-xl font-bold text-white">John Dela Cruz</h2>
                <p class="text-white/80 text-sm">Property Owner</p>
              </div>
            </div>
            <button onclick="hideCancelledProjectModal()" class="w-10 h-10 rounded-xl bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition backdrop-blur-sm shadow-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="p-6 space-y-6 max-h-[calc(100vh-12rem)] overflow-y-auto">
          <!-- Cancellation Message with Termination Details -->
          <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-6">
            <div class="flex justify-center mb-4">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center shadow-lg">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2 text-center">Project Officially TERMINATED</h3>
            <p class="text-gray-600 text-sm leading-relaxed max-w-2xl mx-auto text-center mb-6">
              This project was officially cancelled on <span id="cancelledDate" class="font-semibold text-gray-800">November 20, 2025</span>. 
              All work has been stopped, and the project records are archived for documentation purposes.
            </p>

            <!-- Termination Details -->
            <div class="border-t border-gray-300 pt-6 mt-6">
              <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Termination Details
              </h4>
              
              <div class="space-y-4">
                <!-- Two Column Layout -->
                <div class="grid md:grid-cols-2 gap-4">
                  <div class="space-y-1">
                    <label class="text-xs font-semibold text-gray-600">Terminated By</label>
                    <p id="terminationInitiatedBy" class="text-sm font-medium text-gray-900 bg-white px-3 py-2 rounded-lg border border-gray-200">—</p>
                  </div>
                  <div class="space-y-1">
                    <label class="text-xs font-semibold text-gray-600">Cause of Termination</label>
                    <p id="terminationCause" class="text-sm font-medium text-gray-900 bg-white px-3 py-2 rounded-lg border border-gray-200">—</p>
                  </div>
                </div>

                <div class="space-y-1">
                  <label class="text-xs font-semibold text-gray-600">Reason of Termination</label>
                  <p id="terminationReason" class="text-sm font-medium text-gray-900 bg-white px-3 py-2 rounded-lg border border-gray-200">—</p>
                </div>

                <div class="space-y-1">
                  <label class="text-xs font-semibold text-gray-600">Termination Remarks</label>
                  <textarea id="terminationRemarks" readonly class="w-full px-3 py-2 text-sm text-gray-900 bg-white rounded-lg border border-gray-200 resize-none focus:outline-none" rows="4">—</textarea>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                  <div class="space-y-1">
                    <label class="text-xs font-semibold text-gray-600">Termination Date</label>
                    <p id="terminationNoticeDate" class="text-sm font-medium text-gray-900 bg-white px-3 py-2 rounded-lg border border-gray-200">—</p>
                  </div>
                  <div class="space-y-1">
                    <label class="text-xs font-semibold text-gray-600">Last Active Milestone</label>
                    <p id="terminationAffectedMilestone" class="text-sm font-medium text-gray-900 bg-white px-3 py-2 rounded-lg border border-gray-200">—</p>
                  </div>
                </div>

                <div class="space-y-1">
                  <label class="text-xs font-semibold text-gray-600">Final Status</label>
                  <p id="terminationFinalStatus" class="text-sm font-medium text-gray-900 bg-white px-3 py-2 rounded-lg border border-gray-200">—</p>
                </div>

                <!-- Supporting Files Section -->
                <div class="border-t border-gray-300 pt-4 mt-4">
                  <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-semibold text-gray-600">Supporting Files</label>
                    <button onclick="downloadTerminationFiles()" class="text-xs text-gray-600 hover:text-gray-800 font-semibold flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                      </svg>
                      Download All
                    </button>
                  </div>
                  <div id="terminationSupportingFiles" class="flex flex-wrap gap-2">
                    <!-- Files will be injected here -->
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Project Details and Contractor Details (2-Column) -->
          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Project Details Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                  Project Details
                </h3>
              </div>
              <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Project Title</span>
                  <span id="cancelledProjectTitle" class="text-gray-900 font-semibold text-right">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Project Address</span>
                  <span id="cancelledProjectAddress" class="text-gray-900 font-semibold text-right">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Property Type</span>
                  <span id="cancelledProjectType" class="text-gray-900 font-semibold">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Lot Size</span>
                  <span id="cancelledLotSize" class="text-gray-900 font-semibold">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Timeline</span>
                  <span id="cancelledTimeline" class="text-gray-900 font-semibold">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Budget</span>
                  <span id="cancelledBudget" class="text-gray-900 font-semibold">—</span>
                </div>
                <div class="flex justify-between py-2">
                  <span class="text-gray-500 font-medium">Deadline</span>
                  <span id="cancelledDeadline" class="text-gray-900 font-semibold">—</span>
                </div>
              </div>
              <div class="pt-3 border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-2 font-medium">Uploaded Photos</p>
                <div id="cancelledPhotos" class="flex flex-wrap gap-2">—</div>
              </div>
              <div class="pt-3 border-t border-gray-200">
                <p class="text-xs text-gray-500 mb-2 font-medium">Supporting Files</p>
                <div id="cancelledFiles" class="flex flex-wrap gap-2">—</div>
              </div>
            </div>

            <!-- Contractor Details Card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  Contractor Details
                </h3>
              </div>
              <div class="space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Company Name</span>
                  <span id="cancelledContractorName" class="text-gray-900 font-semibold text-right">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Email</span>
                  <span id="cancelledContractorEmail" class="text-gray-900 font-semibold text-right">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">PCAB License No.</span>
                  <span id="cancelledContractorPcab" class="text-gray-900 font-semibold">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Category</span>
                  <span id="cancelledContractorCategory" class="text-gray-900 font-semibold">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">PCAB Validity</span>
                  <span id="cancelledContractorPcabExpiry" class="text-gray-900 font-semibold">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Business Permit No.</span>
                  <span id="cancelledContractorPermit" class="text-gray-900 font-semibold">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Permit City</span>
                  <span id="cancelledContractorCity" class="text-gray-900 font-semibold">—</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100">
                  <span class="text-gray-500 font-medium">Permit Validity</span>
                  <span id="cancelledContractorPermitExpiry" class="text-gray-900 font-semibold">—</span>
                </div>
                <div class="flex justify-between py-2">
                  <span class="text-gray-500 font-medium">TIN</span>
                  <span id="cancelledContractorTin" class="text-gray-900 font-semibold">—</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Project's Milestone and Details (Row) -->
          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Project's Milestone -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <h3 class="text-sm font-bold text-gray-900">Project's Milestone</h3>
              </div>
              <div id="cancelledMilestoneTimeline" class="space-y-3 pr-2">
                <!-- Milestone timeline items will be rendered here -->
              </div>
            </div>

            <!-- Milestone Details -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <h3 class="text-sm font-bold text-gray-900">Details</h3>
              </div>
              <div id="cancelledDetails" class="space-y-4 pr-2">
                <!-- Milestone details will be rendered here -->
              </div>
            </div>
          </div>

          <!-- Payment Summary (Row) -->
          <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
            <div class="flex items-center justify-between border-b-2 border-gray-400 pb-3">
              <div>
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Payment Summary
                </h3>
                <p class="text-xs text-gray-500 mt-1">This section contains uploaded receipts and payment confirmations before project termination</p>
              </div>
            </div>
            
            <!-- Stats grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Milestones Paid</p>
                  <svg class="w-5 h-5 text-gray-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="cancelledPaidCount" class="text-xl font-bold text-gray-900">—</p>
              </div>
              <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Amount Paid</p>
                  <svg class="w-5 h-5 text-gray-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="cancelledTotalAmount" class="text-xl font-bold text-gray-600">—</p>
              </div>
              <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Last Payment Date</p>
                  <svg class="w-5 h-5 text-gray-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <p id="cancelledLastPaymentDate" class="text-sm font-semibold text-gray-900">—</p>
              </div>
              <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Over All Payment Status</p>
                  <svg class="w-5 h-5 text-gray-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="cancelledOverallStatus" class="text-sm font-bold text-gray-600">—</p>
              </div>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                  <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone Period</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Amount Paid</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Date of Payment</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Uploaded By</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Proof of Payment</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Verification Status</th>
                  </tr>
                </thead>
                <tbody id="cancelledPaymentTable" class="divide-y divide-gray-200 bg-white">
                  <!-- Rows injected by JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl flex justify-end gap-3">
          <button onclick="hideCancelledProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Project Modal -->
  <div id="editProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl relative my-8 transform transition-all duration-300 scale-100">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 to-orange-100">
          <div>
            <h2 class="text-xl font-bold text-gray-900">Edit project</h2>
            <p class="text-xs text-gray-500 mt-0.5">Update the project</p>
          </div>
          <button onclick="hideEditProjectModal()" class="w-8 h-8 rounded-lg hover:bg-white/50 flex items-center justify-center transition text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-5 max-h-[calc(100vh-16rem)] overflow-y-auto">
          <!-- Two Column Layout -->
          <div class="grid md:grid-cols-2 gap-5">
            <!-- Left Column -->
            <div class="space-y-4">
              <!-- Project Title -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Project Title</label>
                <input type="text" id="editProjectTitle" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Greenfield Commercial Complex">
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                <textarea id="editProjectDescription" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none transition" placeholder="Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing..."></textarea>
              </div>

              <!-- Uploaded Photos -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Uploaded Photos</label>
                <div id="editProjectPhotos" class="space-y-2 mb-2">
                  <!-- Photo items will be injected here -->
                </div>
                <button type="button" onclick="addPhotoField()" class="text-sm text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Add Photo
                </button>
              </div>

              <!-- Land Title -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Land Title</label>
                <div id="editProjectLandTitle" class="space-y-2 mb-2">
                  <!-- Land title items will be injected here -->
                </div>
                <button type="button" onclick="addLandTitleField()" class="text-sm text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Add Document
                </button>
              </div>

              <!-- Supporting Files -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Supporting Files</label>
                <div id="editProjectSupportingFiles" class="space-y-2 mb-2">
                  <!-- Supporting file items will be injected here -->
                </div>
                <button type="button" onclick="addSupportingFileField()" class="text-sm text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Add File
                </button>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
              <!-- Property Address -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Property Address</label>
                <input type="text" id="editPropertyAddress" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="123 / House No.">
              </div>

              <!-- City / Municipality -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">City / Municipality</label>
                <input type="text" id="editCityMunicipality" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="City / Municipality">
              </div>

              <!-- Province / State / Region -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Province / State / Region</label>
                <input type="text" id="editProvince" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Province / State / Region">
              </div>

              <!-- Postal Code -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Postal Code</label>
                <input type="text" id="editPostalCode" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Postal Code">
              </div>

              <!-- Property Details -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Property Details</label>
                <input type="text" id="editPropertyType" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent mb-2 transition" placeholder="Property type">
                <input type="text" id="editLotSize" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Lot Size (sqm)">
              </div>

              <!-- Target Timeline -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Target Timeline</label>
                <div class="grid grid-cols-2 gap-2">
                  <input type="text" id="editTimelineMin" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Min. (eg. 2 Months)">
                  <input type="text" id="editTimelineMax" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Max. (eg. 12 Months)">
                </div>
              </div>

              <!-- Budget -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Budget</label>
                <div class="grid grid-cols-2 gap-2">
                  <input type="text" id="editBudgetMin" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Min. (Philippine Peso)">
                  <input type="text" id="editBudgetMax" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Max. (Philippine Peso)">
                </div>
              </div>

              <!-- Bidding Deadline -->
              <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Bidding Deadline</label>
                <div class="relative">
                  <input type="text" id="editBiddingDeadline" class="w-full px-4 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" placeholder="Select your bidding end date">
                  <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end">
          <button onclick="showEditConfirmation()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white transition shadow-md hover:shadow-lg transform hover:scale-105 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Done
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Project Confirmation Modal -->
  <div id="editProjectConfirmModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Confirm Changes</h3>
          </div>
          <button onclick="hideEditConfirmation()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600 leading-relaxed">
            Are you sure you want to save the changes to project <span id="confirmProjectId" class="font-semibold text-gray-900"></span>?
          </p>
          <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 space-y-2">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div class="text-sm text-gray-700">
                <p class="font-semibold mb-1">Changes will be applied to:</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                  <li>Project details and property information</li>
                  <li>Budget and timeline specifications</li>
                  <li>Uploaded files and documents</li>
                </ul>
              </div>
            </div>
          </div>
          <p class="text-xs text-gray-500">
            <strong>Note:</strong> This action will update the project information and notify relevant stakeholders.
          </p>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
          <button onclick="hideEditConfirmation()" class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
            Cancel
          </button>
          <button onclick="confirmEditProject()" class="px-4 py-2.5 text-sm font-medium rounded-lg bg-orange-600 hover:bg-orange-700 text-white transition shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Project Confirmation Modal -->
  <div id="deleteProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Delete Project</h3>
          </div>
          <button onclick="hideDeleteProjectModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600 leading-relaxed">
            Are you sure you want to permanently delete project <span id="deleteProjectId" class="font-semibold text-gray-900"></span>?
          </p>
          <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 space-y-2">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-rose-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <div class="text-sm text-gray-700">
                <p class="font-semibold mb-1">This action cannot be undone:</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                  <li>All project data will be permanently deleted</li>
                  <li>Associated bids and files will be removed</li>
                  <li>Project history will be lost</li>
                </ul>
              </div>
            </div>
          </div>
          <p class="text-xs text-gray-500">
            <strong>Warning:</strong> Stakeholders will be notified about the project deletion.
          </p>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
          <button onclick="hideDeleteProjectModal()" class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
            Cancel
          </button>
          <button onclick="confirmDeleteProject()" class="px-4 py-2.5 text-sm font-medium rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Delete Project
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('js/admin/projectManagement/listOfprojects.js') }}" defer></script>

</body>

</html>