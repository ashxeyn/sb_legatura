<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/disputesReports.css') }}">
  
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
            <a href="{{ route('admin.projectManagement.listOfprojects') }}" class="submenu-link">List of Projects</a>
            <a href="{{ route('admin.projectManagement.disputesReports') }}" class="submenu-link active">Disputes/Reports</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Disputes/Reports</h1>

        <div class="flex items-center gap-6">
          <div class="relative w-64" style="width: 600px;">
            <input 
              id="globalSearch"
              type="text" 
              placeholder="Search disputes, reports, users..." 
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

      <!-- Content -->
      <section class="px-8 py-8 space-y-8">
        <!-- Statistics Cards (loaded from API) -->
        <div id="disputesStatsContainer">
          <!-- Stats loaded from API -->
        </div>

        <!-- Filter Tabs & Table -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <!-- Filter Tabs -->
          <div id="disputesFilterTabs">
            <!-- Filter tabs loaded from API -->
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dispute ID</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reporter</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Subject</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Priority</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody id="reportsTableBody" class="divide-y divide-gray-200">
                <!-- Rows will be populated by JavaScript -->
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-600">
              Showing <span class="font-semibold">1-10</span> of <span class="font-semibold">156</span> reports
            </div>
            <div class="flex items-center gap-2">
              <button class="px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Previous
              </button>
              <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                1
              </button>
              <button class="px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                2
              </button>
              <button class="px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                3
              </button>
              <button class="px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                Next
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- View Details Modal -->
      <div id="viewDetailsModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-3xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">
          <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 sticky top-0 z-10">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-file-invoice text-white text-xl"></i>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-white">Case Details</h3>
                  <p class="text-indigo-100 text-sm" id="modalCaseId">Case #DR-2025-001</p>
                </div>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          
          <div class="p-6 space-y-6">
            <!-- Case Info -->
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-3">
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Reporter</label>
                  <p class="text-sm font-semibold text-gray-800" id="modalReporter">-</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Type</label>
                  <p class="text-sm font-semibold text-gray-800" id="modalType">-</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Priority</label>
                  <div id="modalPriority"></div>
                </div>
              </div>
              <div class="space-y-3">
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Date Submitted</label>
                  <p class="text-sm font-semibold text-gray-800" id="modalDate">-</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Status</label>
                  <div id="modalStatus"></div>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-500 uppercase">Project</label>
                  <p class="text-sm font-semibold text-gray-800" id="modalProject">-</p>
                </div>
              </div>
            </div>

            <!-- Subject -->
            <div class="p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
              <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Subject</label>
              <p class="text-sm text-gray-800 font-semibold" id="modalSubject">-</p>
            </div>

            <!-- Description -->
            <div>
              <label class="text-sm font-semibold text-gray-700 block mb-2">Description</label>
              <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                <p class="text-sm text-gray-700 leading-relaxed" id="modalDescription">-</p>
              </div>
            </div>

            <!-- Dispute Details Section -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-document text-indigo-600"></i>
                Dispute Details
              </h4>
              
              <div class="space-y-4">
                <!-- Reason for Dispute -->
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                  <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Reason for Dispute</label>
                  <p class="text-sm text-gray-800 font-medium" id="modalReasonDispute">-</p>
                </div>

                <!-- Requested Action -->
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                  <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Requested Action</label>
                  <p class="text-sm text-gray-800 font-medium" id="modalRequestedAction">-</p>
                </div>

                <!-- Remarks -->
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                  <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Remarks</label>
                  <p class="text-sm text-gray-700 leading-relaxed" id="modalRemarks">-</p>
                </div>
              </div>
            </div>

            <!-- Linked Progress Report Section -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-chart-line-up text-indigo-600"></i>
                Linked Progress Report
              </h4>
              
              <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4">
                  <!-- Current Milestone -->
                  <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                    <label class="text-xs font-semibold text-blue-600 uppercase block mb-1">Current Milestone</label>
                    <p class="text-lg font-bold text-gray-800" id="modalMilestone">-</p>
                  </div>

                  <!-- Preferred Outcome -->
                  <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-xl p-4">
                    <label class="text-xs font-semibold text-purple-600 uppercase block mb-1">Preferred Outcome</label>
                    <p class="text-lg font-bold text-gray-800" id="modalOutcome">-</p>
                  </div>

                  <!-- Click to View Report -->
                  <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl p-4 flex items-center justify-center">
                    <button id="viewReportBtn" class="text-emerald-600 hover:text-emerald-700 font-semibold text-sm flex items-center gap-2 transition">
                      <i class="fi fi-rr-document"></i>
                      <span>Click to View Report</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Supporting Documents Section -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-folder-open text-indigo-600"></i>
                Supporting Documents
              </h4>
              
              <div id="modalDocumentsSection">
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                  <table class="w-full text-sm">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">File</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">User Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Position</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Action</th>
                      </tr>
                    </thead>
                    <tbody id="modalDocumentsTable" class="divide-y divide-gray-200 bg-white">
                      <!-- Documents will be rendered here -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Attachments -->
            <div id="modalAttachmentsSection" class="hidden border-t border-gray-200 pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-clip text-indigo-600"></i>
                Additional Attachments
              </h4>
              <div id="modalAttachments" class="grid grid-cols-3 gap-3">
                <!-- Attachments will be rendered here -->
              </div>
            </div>

            <!-- Resubmitted Report Panel Section -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                <i class="fi fi-sr-refresh text-indigo-600"></i>
                Resubmitted Report Panel
              </h4>
              <p class="text-xs text-gray-500 mb-4">This section contains uploaded receipts and payment confirmations related to completed milestones</p>
              
              <div id="modalResubmittedSection">
                <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                  <table class="w-full text-sm">
                    <thead class="bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Resubmitted By</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Resubmission Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date Resubmitted</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Action</th>
                      </tr>
                    </thead>
                    <tbody id="modalResubmittedTable" class="divide-y divide-gray-200 bg-white">
                      <!-- Resubmitted reports will be rendered here -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Feedback Monitoring Panel Section -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-comment-alt text-indigo-600"></i>
                Feedback Monitoring Panel
              </h4>
              
              <div id="modalFeedbackSection" class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl border border-gray-200 p-5">
                <div class="grid grid-cols-2 gap-6">
                  <!-- Left Column: Feedback Info -->
                  <div class="space-y-3">
                    <div>
                      <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Feedback From</label>
                      <p class="text-sm font-semibold text-gray-800" id="modalFeedbackFrom">-</p>
                    </div>
                    <div>
                      <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Resubmission ID</label>
                      <p class="text-sm font-semibold text-indigo-600" id="modalResubmissionId">-</p>
                    </div>
                    <div>
                      <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Response</label>
                      <span id="modalFeedbackResponse" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                        -
                      </span>
                    </div>
                    <div>
                      <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Date Submitted</label>
                      <p class="text-sm text-gray-700" id="modalFeedbackDate">-</p>
                    </div>
                  </div>

                  <!-- Right Column: Remarks -->
                  <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Remarks</label>
                    <div class="bg-white rounded-lg border border-gray-200 p-4 h-[calc(100%-28px)]">
                      <p class="text-sm text-gray-700 leading-relaxed" id="modalFeedbackRemarks">-</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between gap-3 pt-6 border-t border-gray-200">
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <i class="fi fi-rr-shield-check text-indigo-500"></i>
                <span>All actions are logged and monitored for compliance.</span>
              </div>
              <div class="flex items-center gap-3">
                <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Close</button>
                <button id="resolveBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                  Mark as Resolved
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Resolve Confirmation Modal -->
      <div id="resolveConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-check-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Resolve Case?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-emerald-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-emerald-900 font-semibold mb-1">Mark this case as resolved</p>
                <p class="text-xs text-emerald-800">This will close the case and notify all parties involved.</p>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Resolution Notes *</label>
              <textarea id="resolutionNotes" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 transition resize-none" placeholder="Provide details about how this case was resolved..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmResolveBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                Confirm Resolution
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Download Confirmation Modal -->
      <div id="downloadConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-download text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Download File?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-blue-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-blue-900 font-semibold mb-1">Download this file</p>
                <p class="text-xs text-blue-800" id="downloadFileName">File: document.pdf</p>
              </div>
            </div>
            
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
              <div class="flex items-center gap-3">
                <i class="fi fi-sr-file-pdf text-red-500 text-3xl"></i>
                <div class="flex-1">
                  <p class="text-sm font-semibold text-gray-800" id="downloadFileNameDisplay">document.pdf</p>
                  <p class="text-xs text-gray-500">Click confirm to download this file to your device</p>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmDownloadBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                <i class="fi fi-sr-download mr-2"></i>
                Download
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div id="deleteConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-trash text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Delete File?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
              <i class="fi fi-rr-triangle-warning text-red-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-red-900 font-semibold mb-1">This action cannot be undone</p>
                <p class="text-xs text-red-800">The file will be permanently removed from the system.</p>
              </div>
            </div>
            
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
              <div class="flex items-center gap-3">
                <i class="fi fi-sr-file-pdf text-red-500 text-3xl"></i>
                <div class="flex-1">
                  <p class="text-sm font-semibold text-gray-800" id="deleteFileNameDisplay">document.pdf</p>
                  <p class="text-xs text-gray-500">Uploaded by <span id="deleteFileUploader">John Doe</span> on <span id="deleteFileDate">Nov 20, 2025</span></p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Reason for Deletion (Optional)</label>
              <textarea id="deleteReason" rows="3" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Provide a reason for deleting this file..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmDeleteBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                <i class="fi fi-sr-trash mr-2"></i>
                Delete File
              </button>
            </div>
          </div>
        </div>
      </div>

      

      <!-- Resubmitted Report Details Modal -->
      <div id="resubmittedReportModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden max-h-[90vh] overflow-y-auto">
          <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 sticky top-0 z-10">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-refresh text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Resubmitted Report</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          
          <div class="p-6 space-y-6">
            <!-- Status Badge -->
            <div>
              <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Status:</label>
              <span id="resubmittedStatus" class="inline-flex px-4 py-2 rounded-full text-sm font-semibold bg-amber-100 text-amber-700 border border-amber-300">
                Under Review
              </span>
            </div>

            <!-- Report Details Grid -->
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Resubmission ID</label>
                <p class="text-sm font-semibold text-gray-800" id="resubmittedId">-</p>
              </div>
              <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Resubmitted By</label>
                <p class="text-sm font-semibold text-gray-800" id="resubmittedBy">-</p>
              </div>
              <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Resubmission Type</label>
                <p class="text-sm font-semibold text-gray-800" id="resubmittedType">-</p>
              </div>
              <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                <label class="text-xs font-semibold text-gray-500 uppercase block mb-2">Date Resubmitted</label>
                <p class="text-sm font-semibold text-gray-800" id="resubmittedDate">-</p>
              </div>
            </div>

            <!-- Remarks Section -->
            <div>
              <label class="text-sm font-semibold text-gray-700 block mb-2">Remarks</label>
              <div class="bg-gradient-to-br from-gray-50 to-indigo-50 rounded-xl border border-gray-200 p-4">
                <textarea id="resubmittedRemarks" rows="4" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none bg-white" placeholder="Write a compelling message to the client. Tell them about your expertise and why you're a great fit."></textarea>
              </div>
            </div>

            <!-- Uploaded Files Section -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fi fi-sr-folder-open text-indigo-600"></i>
                Uploaded Files
              </h4>
              
              <div class="bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                  <thead class="bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-12">
                        <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                      </th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Files</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date Submitted</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Uploaded By</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Position</th>
                      <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Action</th>
                    </tr>
                  </thead>
                  <tbody id="resubmittedFilesTable" class="divide-y divide-gray-200 bg-white">
                    <!-- Files will be rendered here -->
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Close</button>
              <button id="approveResubmittedBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                Approve
              </button>
              <button id="rejectResubmittedBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                Reject
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Approve Resubmitted Report Confirmation Modal -->
      <div id="approveResubmittedConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-check-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Approve Report?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-emerald-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-emerald-900 font-semibold mb-1">Approve this resubmitted report</p>
                <p class="text-xs text-emerald-800">This will mark the report as approved and notify all parties involved.</p>
              </div>
            </div>
            
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Resubmission ID</span>
                  <span class="text-sm font-bold text-gray-800" id="approveResubmissionId">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Resubmitted By</span>
                  <span class="text-sm font-semibold text-gray-800" id="approveResubmittedBy">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Type</span>
                  <span class="text-sm text-gray-600" id="approveResubmissionType">-</span>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Approval Notes (Optional)</label>
              <textarea id="approveNotes" rows="3" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 transition resize-none" placeholder="Add any approval notes or comments..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmApproveResubmittedBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                <i class="fi fi-sr-check-circle mr-2"></i>
                Approve Report
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Reject Resubmitted Report Confirmation Modal -->
      <div id="rejectResubmittedConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-cross-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Reject Report?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
              <i class="fi fi-rr-triangle-warning text-red-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-red-900 font-semibold mb-1">Reject this resubmitted report</p>
                <p class="text-xs text-red-800">The submitter will be notified and may need to resubmit with corrections.</p>
              </div>
            </div>
            
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Resubmission ID</span>
                  <span class="text-sm font-bold text-gray-800" id="rejectResubmissionId">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Resubmitted By</span>
                  <span class="text-sm font-semibold text-gray-800" id="rejectResubmittedBy">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold text-gray-500 uppercase">Type</span>
                  <span class="text-sm text-gray-600" id="rejectResubmissionType">-</span>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Rejection Reason *</label>
              <textarea id="rejectReason" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Explain why this report is being rejected and what needs to be corrected..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmRejectResubmittedBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                <i class="fi fi-sr-cross-circle mr-2"></i>
                Reject Report
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Download Resubmitted File Confirmation Modal -->
      <div id="downloadResubmittedFileModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                  <i class="fi fi-sr-download text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Download File?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-indigo-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-indigo-900 font-semibold mb-1">Download resubmitted file</p>
                <p class="text-xs text-indigo-800" id="downloadResubmittedFileName">File: document.pdf</p>
              </div>
            </div>
            
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
              <div class="flex items-center gap-3">
                <i class="fi fi-sr-file-pdf text-red-500 text-3xl"></i>
                <div class="flex-1">
                  <p class="text-sm font-semibold text-gray-800" id="downloadResubmittedFileNameDisplay">document.pdf</p>
                  <p class="text-xs text-gray-500">From resubmitted report: <span id="downloadResubmittedReportId" class="font-semibold text-indigo-600">RSB-1234</span></p>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Cancel</button>
              <button id="confirmDownloadResubmittedBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition">
                <i class="fi fi-sr-download mr-2"></i>
                Download
              </button>
            </div>
          </div>
        </div>
      </div>

    </main>
  

  <script src="{{ asset('js/admin/projectManagement/disputesReports.js') }}" defer></script>

</body>

</html>