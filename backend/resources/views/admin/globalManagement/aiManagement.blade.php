<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/globalManagement/aiManagement.css') }}">
  
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
            <a href="{{ route('admin.globalManagement.aiManagement') }}" class="submenu-link active">AI Management</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">AI Management</h1>

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

      <!-- AI Management Stats (loaded from API) -->
      <section class="px-8 py-8">
        <div id="aiStatsContainer" class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <!-- Stats loaded from API -->
        </div>
      </section>
      <!-- End AI Management Stats Cards Section -->

      <!-- AI Management Charts Row -->
      <section class="px-8 pb-8">
        <div class="flex flex-col md:flex-row gap-6">
          <!-- Line Chart Card -->
          <div class="bg-gray-800 rounded-2xl p-6 flex-1 shadow-lg flex flex-col justify-between min-w-0">
            <div class="flex items-center justify-between mb-2">
              <span class="text-gray-100 font-semibold text-base">Line Chart showing if AI-predicted risks are rising or falling weekly.</span>
              <span class="text-gray-300 text-sm flex items-center gap-2">
                <i class="fi fi-ss-calendar text-lg"></i>
                January - July 2025
              </span>
            </div>
            <canvas id="aiRiskLineChart" class="w-full h-56" style="max-height: 220px;"></canvas>
          </div>
          <!-- Donut Chart Card -->
          <div class="bg-gray-800 rounded-2xl p-6 w-full md:w-80 flex flex-col items-center shadow-lg min-w-0">
            <div class="flex items-center justify-between w-full mb-2">
              <span class="text-gray-100 font-semibold text-base">Project's Status</span>
              <button class="text-gray-400 hover:text-gray-200 text-xl"><i class="fi fi-br-menu-dots"></i></button>
            </div>
            <canvas id="projectStatusDonut" class="w-40 h-40 mb-2"></canvas>
            <div class="flex gap-4 mt-2 text-sm">
              <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> <span class="text-gray-100">On Track</span></div>
              <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-orange-400 inline-block"></span> <span class="text-gray-100">At Risk</span></div>
              <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-900 inline-block"></span> <span class="text-gray-100">Delayed</span></div>
            </div>
          </div>
        </div>
      </section>
      <!-- End AI Management Charts Row -->

      <!-- AI Management Activity Table -->
      <section class="px-8 pb-12">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
          <table class="min-w-full table-fixed">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 tracking-wider">Date</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 tracking-wider">Action</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 tracking-wider">Description</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 tracking-wider">Result</th>
                <th class="px-6 py-4"></th>
              </tr>
            </thead>
            <tbody id="aiActivityTable" class="divide-y divide-gray-100">
              <!-- Data loaded from API -->
            </tbody>
          </table>
        </div>
      </section>
      <!-- End AI Management Activity Table -->

      <!-- AI Risk Projects Table -->
      <section class="px-8 pb-12">
        <div class="bg-white rounded-2xl shadow-lg overflow-x-auto">
          <table class="min-w-full table-fixed">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 tracking-wider">Project ID</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 tracking-wider">Project Title</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 tracking-wider">Contractor Company</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 tracking-wider">Property Owner</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 tracking-wider">AI Risk Level</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 tracking-wider">Status</th>
                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody id="aiProjectsTable" class="divide-y divide-gray-100">
              <!-- Data loaded from API -->
            </tbody>
          </table>
        </div>
      </section>
      <!-- End AI Risk Projects Table -->

      <!-- Detailed AI Analysis Modal -->
      <div id="aiAnalysisModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 transform transition-all">
          <div class="modal-header flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Detailed AI Analysis</h3>
            <button id="closeAiAnalysisModal" class="text-gray-400 hover:text-gray-600 transition text-2xl">&times;</button>
          </div>
          <div class="modal-body p-6 max-h-96 overflow-y-auto">
            <div class="mb-4">
              <span class="text-gray-600 text-sm font-medium">Status:</span>
              <span id="aiModalStatus" class="ml-2 inline-block px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">Pending Review</span>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <div class="text-gray-500 text-xs font-medium mb-1">Project ID</div>
                <div id="aiModalProjectId" class="text-gray-800 text-sm font-semibold">PJ-123104</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs font-medium mb-1">Expected Completion (Milestone)</div>
                <div id="aiModalCompletion" class="text-gray-800 text-sm font-semibold">70%</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs font-medium mb-1">Project Title</div>
                <div id="aiModalTitle" class="text-gray-800 text-sm font-semibold">Duplex Housing Project</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs font-medium mb-1">Actual progress</div>
                <div id="aiModalProgress" class="text-gray-800 text-sm font-semibold">50%</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs font-medium mb-1">Property Owner</div>
                <div id="aiModalOwner" class="text-gray-800 text-sm font-semibold">Cori Homes</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs font-medium mb-1">Variance</div>
                <div id="aiModalVariance" class="text-gray-800 text-sm font-semibold">20%</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs font-medium mb-1">Contractor</div>
                <div id="aiModalContractor" class="text-gray-800 text-sm font-semibold">Saludo Construction</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs font-medium mb-1">AI Risk Prediction</div>
                <div id="aiModalRisk" class="text-gray-800 text-sm font-semibold">High Risk of Delay</div>
              </div>
              <div class="col-span-2">
                <div class="text-gray-500 text-xs font-medium mb-1">AI Confidence level</div>
                <div id="aiModalConfidence" class="text-gray-800 text-sm font-semibold">85%</div>
              </div>
            </div>
            <div class="mb-4">
              <div class="text-gray-700 text-sm font-semibold mb-2">AI Recommendation</div>
              <div id="aiModalRecommendation" class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-gray-600 text-sm leading-relaxed">
                "The project is behind schedule. Contractor should submit a recovery plan within 3 days. Owner should review milestone allocations and consider a progress meeting."
              </div>
            </div>
          </div>
          <div class="modal-footer flex gap-3 p-6 border-t border-gray-200">
            <button id="reanalyzeBtn" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 rounded-lg transition transform hover:scale-105">
              Re-analyze Project
            </button>
            <button id="implementedBtn" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition transform hover:scale-105">
              Recommendation Implemented
            </button>
          </div>
        </div>
      </div>
      <!-- End Detailed AI Analysis Modal -->

      <!-- Re-analyze Confirmation Modal -->
      <div id="reanalyzeConfirmModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all animate-modal-entrance">
          <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
              <i class="fi fi-rr-refresh text-yellow-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Re-analyze Project?</h3>
            <p class="text-gray-600 text-sm mb-6">This will trigger a new AI analysis for the selected project. The current recommendation will be updated based on the latest project data.</p>
            <div class="flex gap-3">
              <button id="cancelReanalyze" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg transition">
                Cancel
              </button>
              <button id="confirmReanalyze" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-3 rounded-lg transition transform hover:scale-105 flex items-center justify-center gap-2">
                <span>Confirm</span>
                <span class="loader hidden"></span>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- End Re-analyze Confirmation Modal -->

      <!-- Recommendation Implemented Confirmation Modal -->
      <div id="implementedConfirmModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all animate-modal-entrance">
          <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
              <i class="fi fi-rr-check-circle text-green-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Mark as Implemented?</h3>
            <p class="text-gray-600 text-sm mb-6">This will mark the AI recommendation as implemented and update the project status. This action can be undone later if needed.</p>
            <div class="flex gap-3">
              <button id="cancelImplemented" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg transition">
                Cancel
              </button>
              <button id="confirmImplemented" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-lg transition transform hover:scale-105 flex items-center justify-center gap-2">
                <span>Confirm</span>
                <span class="loader hidden"></span>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- End Recommendation Implemented Confirmation Modal -->

      <!-- Delete AI Project Confirmation Modal -->
      <div id="deleteAiProjectModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all animate-modal-entrance">
          <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4 animate-shake">
              <i class="fi fi-rr-trash text-red-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Delete AI Analysis?</h3>
            <p class="text-gray-600 text-sm mb-2">Are you sure you want to delete the AI analysis for:</p>
            <p id="deleteProjectInfo" class="text-gray-800 font-semibold text-sm mb-6">#10421 - GreenBelt Building</p>
            <p class="text-gray-500 text-xs mb-6">This action cannot be undone. All AI recommendations and analysis data for this project will be permanently removed.</p>
            <div class="flex gap-3">
              <button id="cancelDeleteAi" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg transition">
                Cancel
              </button>
              <button id="confirmDeleteAi" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-lg transition transform hover:scale-105 flex items-center justify-center gap-2">
                <span>Delete</span>
                <span class="loader hidden"></span>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- End Delete AI Project Confirmation Modal -->

      <!-- Delete Activity Confirmation Modal -->
      <div id="deleteActivityModal" class="modal-overlay fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform transition-all animate-modal-entrance">
          <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4 animate-shake">
              <i class="fi fi-rr-trash text-red-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Delete Activity Record?</h3>
            <p class="text-gray-600 text-sm mb-2">Are you sure you want to delete this activity:</p>
            <p id="deleteActivityInfo" class="text-gray-800 font-semibold text-sm mb-6">October 15, 2025 - AI Scan Completed</p>
            <p class="text-gray-500 text-xs mb-6">This action cannot be undone. The activity record will be permanently removed from the system.</p>
            <div class="flex gap-3">
              <button id="cancelDeleteActivity" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg transition">
                Cancel
              </button>
              <button id="confirmDeleteActivity" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-lg transition transform hover:scale-105 flex items-center justify-center gap-2">
                <span>Delete</span>
                <span class="loader hidden"></span>
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- End Delete Activity Confirmation Modal -->

    </main>
  
  <script src="{{ asset('js/admin/globalManagement/aiManagement.js') }}" defer></script>
</body>

</html>