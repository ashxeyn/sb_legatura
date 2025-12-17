<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/globalManagement/proofOfpayments.css') }}">
  
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
            <a href="{{ route('admin.globalManagement.proofOfpayments') }}" class="submenu-link active">Proof of Payments</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Proof of Payments</h1>

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
        <!-- Stats loaded from API -->
        <div id="statsCardsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Stats loaded from API -->
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-20">ID</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Project</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Contractor</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-28">Amount</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-24">Date</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-20">Method</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-24">Status</th>
                  <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-28">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200" id="paymentsTable">
                <!-- Data loaded from API -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>
    
    <!-- Pending Proof of Payment Modal -->
    <div id="pendingPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>
      <div class="relative bg-white w-full max-w-5xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-7 py-5 bg-gradient-to-r from-indigo-50 via-blue-50 to-cyan-50 border-b">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow">
              <i class="fi fi-ss-bolt text-white text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Proof of Payment (Pending)</h3>
              <p class="text-xs text-gray-500">Awaiting verification</p>
            </div>
          </div>
          <button data-close-modal class="p-2 rounded-xl hover:bg-white/80 text-gray-500 hover:text-gray-700 transition"><i class="fi fi-rr-cross-small text-xl"></i></button>
        </div>

        <div class="p-7 space-y-6 max-h-[72vh] overflow-y-auto">
          <!-- Status Badge -->
          <div class="flex items-center gap-3">
            <span id="pp-status" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">Pending</span>
            <span class="text-xs text-gray-500">Submitted and under review</span>
          </div>

          <!-- Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left -->
            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6">
                <span class="text-gray-500">Payment ID</span>
                <span id="pp-payment-id" class="font-semibold text-gray-800">#—</span>
              </div>
              <div class="flex items-start justify-between gap-6">
                <!-- Stats loaded from API -->
                <div id="statsCardsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                  <!-- Stats loaded from API -->
                </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t">
          <button id="pp-reject" class="px-4 py-2 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 hover:border-red-300 transition">Reject</button>
          <button id="pp-approve" class="px-4 py-2 rounded-lg bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 hover:border-green-300 transition">Approve</button>
        </div>
      </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div id="confirmApproveModal" class="fixed inset-0 z-[110] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden modal-enter">
        <div class="px-8 pt-8 pb-6 text-center">
          <div class="confirm-hero bg-amber-100 text-amber-600 mx-auto mb-4"><i class="fi fi-ss-question"></i></div>
          <h4 class="text-2xl font-semibold text-gray-900">Approve Payment?</h4>
          <p class="mt-2 text-gray-600">Are you sure you want to approve this proof of payment?</p>
          <p class="text-gray-600">This action will update the payment status.</p>
          <p class="mt-2 text-xs text-gray-500">Reference: <span id="approveSummary" class="font-medium text-gray-700">—</span></p>
        </div>
        <div class="px-8 pb-8 flex items-center justify-center gap-4">
          <button data-close-modal class="btn-outline-neutral">Cancel</button>
          <button id="confirmApproveBtn" class="btn-gradient-orange">Yes, Approve</button>
        </div>
      </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div id="confirmRejectModal" class="fixed inset-0 z-[110] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
      <div class="relative bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden modal-enter">
        <div class="px-8 pt-8 pb-6 text-center">
          <div class="confirm-hero bg-rose-100 text-rose-600 mx-auto mb-4"><i class="fi fi-ss-question"></i></div>
          <h4 class="text-2xl font-semibold text-gray-900">Reject Payment?</h4>
          <p class="mt-2 text-gray-600">Are you sure you want to reject this proof of payment?</p>
          <p class="text-gray-600">This action will mark the proof as invalid.</p>
          <p class="mt-2 text-xs text-gray-500">Reference: <span id="rejectSummary" class="font-medium text-gray-700">—</span></p>
        </div>
        <div class="px-8 pb-8 flex items-center justify-center gap-4">
          <button data-close-modal class="btn-outline-neutral">Cancel</button>
          <button id="confirmRejectBtn" class="btn-gradient-rose">Yes, Reject</button>
        </div>
      </div>
    </div>

    <!-- Completed Proof of Payment Modal -->
    <div id="completedPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity completed-backdrop"></div>
      <div class="relative bg-white w-full max-w-6xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden h-[92vh] flex flex-col completed-modal-panel">
        <div class="flex items-center justify-between px-8 py-5 bg-gradient-to-r from-emerald-50 via-green-50 to-teal-50 border-b">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow completed-icon-badge">
              <i class="fi fi-sr-check-circle text-white text-xl"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Proof of Payment (Completed)</h3>
              <p class="text-xs text-gray-500">Transaction has been verified</p>
            </div>
          </div>
          <button data-close-modal class="p-2 rounded-xl hover:bg-white/80 text-gray-500 hover:text-gray-700 transition completed-close-btn"><i class="fi fi-rr-cross-small text-xl"></i></button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-6 completed-scrollbar">
          <!-- Status and Remarks -->
          <!-- Success Banner -->
          <div class="rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-50 p-5">
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">Approved</span>
              <span class="text-xs text-gray-500">Verified and recorded</span>
            </div>
          </div>

          <!-- Info Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment ID</span><span id="cp-payment-id" class="font-semibold text-gray-800">#—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Contractor</span><span id="cp-contractor" class="font-semibold text-gray-800 text-right truncate max-w-[240px]">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Milestone Paid</span><span id="cp-milestone" class="text-gray-800">Milestone 3 • Rooftop Building</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Reference No.</span><span id="cp-reference" class="text-gray-800">PAY_XXXX_cash</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Date</span><span id="cp-date" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Method</span><span id="cp-method" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Amount Paid</span><span id="cp-amount" class="text-gray-800 font-semibold">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Date Verified</span><span id="cp-verified" class="text-gray-800">—</span></div>
            </div>
            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Property Owner</span><span id="cp-owner" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Project Title</span><span id="cp-project" class="text-gray-800 text-right truncate max-w-[260px]">—</span></div>
              <div class="flex flex-col gap-2">
                <span class="text-gray-500">Description</span>
                <p id="cp-description" class="text-gray-700 leading-relaxed text-justify">Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing.</p>
              </div>
            </div>
          </div>

          <!-- Files -->
          <div>
            <h4 class="font-semibold text-gray-800 mb-3">Uploaded Files</h4>
            <div class="rounded-xl border border-gray-200 overflow-hidden">
              <div class="grid grid-cols-12 bg-gray-50 text-xs font-semibold text-gray-600 px-4 py-2 sticky top-0 z-10">
                <div class="col-span-6">Files</div>
                <div class="col-span-2">Date Submitted</div>
                <div class="col-span-2">Uploaded By</div>
                <div class="col-span-1">Position</div>
                <div class="col-span-1 text-right"> </div>
              </div>
              <div class="divide-y" id="cp-files">
                <div class="grid grid-cols-12 items-center px-4 py-3 hover:bg-gray-50">
                  <div class="col-span-6 flex items-center gap-3">
                    <input type="checkbox" class="rounded border-gray-300 completed-checkbox">
                    <span class="file-type">PDF</span>
                    <span class="text-gray-800">Progress Report</span>
                  </div>
                  <div class="col-span-2 text-sm text-gray-600">Dec 23, 2022</div>
                  <div class="col-span-2 text-sm text-gray-600">Carl Saludo</div>
                  <div class="col-span-1 text-sm text-gray-600">Architect</div>
                  <div class="col-span-1 flex justify-end">
                    <button class="icon-btn completed-download-btn" title="Download"><i class="fi fi-rr-download"></i></button>
                  </div>
                </div>
                <div class="grid grid-cols-12 items-center px-4 py-3 hover:bg-gray-50">
                  <div class="col-span-6 flex items-center gap-3">
                    <input type="checkbox" class="rounded border-gray-300 completed-checkbox">
                    <span class="file-type">PDF</span>
                    <span class="text-gray-800">Official Receipt</span>
                  </div>
                  <div class="col-span-2 text-sm text-gray-600">Dec 23, 2022</div>
                  <div class="col-span-2 text-sm text-gray-600">Carl Saludo</div>
                  <div class="col-span-1 text-sm text-gray-600">Architect</div>
                  <div class="col-span-1 flex justify-end">
                    <button class="icon-btn completed-download-btn" title="Download"><i class="fi fi-rr-download"></i></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Edit Payment Details Modal -->
    <div id="editPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>
      <div class="relative bg-white w-full max-w-2xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden modal-enter">
        <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 border-b">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
              <i class="fi fi-rr-edit text-white text-sm"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Edit Payment Details</h3>
          </div>
          <button data-close-modal class="p-2 rounded-full hover:bg-gray-100 text-gray-500 transition"><i class="fi fi-rr-cross-small text-xl"></i></button>
        </div>

        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-2">
            <i class="fi fi-rr-info text-blue-600 text-sm mt-0.5"></i>
            <div class="text-xs text-blue-700">
              <p class="font-semibold">Update the project</p>
              <p class="text-blue-600">Note: Only the editable components can be changed</p>
            </div>
          </div>

          <!-- Payment Reference No. -->
          <div class="form-group">
            <label class="form-label">Payment Reference No.</label>
            <input type="text" id="edit-reference" class="form-input" placeholder="Enter payment reference number">
          </div>

          <!-- Project Title -->
          <div class="form-group">
            <label class="form-label">Project Title</label>
            <input type="text" id="edit-project" class="form-input" placeholder="Enter project title" readonly>
          </div>

          <!-- Payment Method -->
          <div class="form-group">
            <label class="form-label">Payment Method</label>
            <select id="edit-method" class="form-input">
              <option value="">Select payment method</option>
              <option value="Bank">Bank Transfer</option>
              <option value="Cash">Cash</option>
              <option value="Check">Check</option>
              <option value="Online">Online Payment</option>
            </select>
          </div>

          <!-- Amount Paid -->
          <div class="form-group">
            <label class="form-label">Amount Paid</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">₱</span>
              <input type="text" id="edit-amount" class="form-input pl-8" placeholder="0.00">
            </div>
          </div>

          <!-- Status -->
          <div class="form-group">
            <label class="form-label">Status</label>
            <select id="edit-status" class="form-input">
              <option value="Pending">Pending</option>
              <option value="Completed">Completed</option>
              <option value="Invalid">Invalid</option>
            </select>
          </div>

          <!-- Remarks -->
          <div class="form-group">
            <label class="form-label">Remarks</label>
            <textarea id="edit-remarks" rows="3" class="form-input" placeholder="Add any additional notes or remarks..."></textarea>
          </div>

          <!-- Uploaded Files Section -->
          <div class="form-group">
            <label class="form-label">Uploaded Files</label>
            <div class="space-y-2" id="edit-files-list">
              <!-- Files loaded from API -->
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t">
          <button data-close-modal class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition">Cancel</button>
          <button id="saveEditBtn" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold hover:from-indigo-700 hover:to-purple-700 transition shadow-lg shadow-indigo-500/30">Done</button>
        </div>
      </div>
    </div>

    <!-- Invalid Proof of Payment Modal -->
    <div id="invalidPaymentModal" class="fixed inset-0 z-[100] hidden items-center justify-center">
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>
      <div class="relative bg-white w-full max-w-5xl mx-4 rounded-2xl shadow-2xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-7 py-5 bg-gradient-to-r from-rose-50 via-red-50 to-orange-50 border-b">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center shadow">
              <i class="fi fi-sr-triangle-warning text-white text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Proof of Payment (Invalid)</h3>
              <p class="text-xs text-gray-500">Receipt flagged as invalid</p>
            </div>
          </div>
          <button data-close-modal class="p-2 rounded-xl hover:bg-white/80 text-gray-500 hover:text-gray-700 transition"><i class="fi fi-rr-cross-small text-xl"></i></button>
        </div>

        <div class="p-7 space-y-6 max-h-[72vh] overflow-y-auto">
          <!-- Status and Remarks -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <span id="ip-status" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 border border-rose-200">Invalid Receipt</span>
            </div>
            <div class="space-y-2">
              <label class="block text-sm text-gray-600">Remarks:</label>
              <textarea id="ip-remarks" rows="2" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-400 focus:border-rose-400" placeholder="Write a compelling message to the client. Tell them about your expertise and why you're a great fit."></textarea>
            </div>
          </div>

          <!-- Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left -->
            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment ID</span><span id="ip-payment-id" class="font-semibold text-gray-800">#—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Contractor</span><span id="ip-contractor" class="font-semibold text-gray-800 text-right truncate max-w-[240px]">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Milestone Paid</span><span id="ip-milestone" class="text-gray-800">Milestone 3 • Rooftop Building</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Reference No.</span><span id="ip-reference" class="text-gray-800">PAY_XXXX_cash</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Date</span><span id="ip-date" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Payment Method</span><span id="ip-method" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Amount Paid</span><span id="ip-amount" class="text-gray-800 font-semibold">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Date Verified</span><span id="ip-verified" class="text-gray-800">—</span></div>
            </div>
            <!-- Right -->
            <div class="space-y-3 text-sm">
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Property Owner</span><span id="ip-owner" class="text-gray-800">—</span></div>
              <div class="flex items-start justify-between gap-6"><span class="text-gray-500">Project Title</span><span id="ip-project" class="text-gray-800 text-right truncate max-w-[260px]">—</span></div>
              <div class="flex flex-col gap-2">
                <span class="text-gray-500">Description</span>
                <p id="ip-description" class="text-gray-700 leading-relaxed text-justify">Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing.</p>
              </div>
            </div>
          </div>

          <!-- Uploaded Files Table-like -->
          <div>
            <h4 class="font-semibold text-gray-800 mb-3">Uploaded Files</h4>
            <div class="rounded-xl border border-gray-200 overflow-hidden">
              <div class="grid grid-cols-12 bg-gray-50 text-xs font-semibold text-gray-600 px-4 py-2">
                <div class="col-span-6">Files</div>
                <div class="col-span-2">Date Submitted</div>
                <div class="col-span-2">Uploaded By</div>
                <div class="col-span-1">Position</div>
                <div class="col-span-1 text-right"> </div>
              </div>
              <div class="divide-y" id="ip-files">
                <div class="grid grid-cols-12 items-center px-4 py-3 hover:bg-gray-50">
                  <div class="col-span-6 flex items-center gap-3">
                    <input type="checkbox" class="form-checkbox rounded border-gray-300 invalid-checkbox">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 text-xs font-bold">PDF</div>
                    <div>
                      <div class="text-sm font-medium text-gray-800">Progress Report</div>
                      <div class="text-xs text-gray-500">progress_report.pdf</div>
                    </div>
                  </div>
                  <div class="col-span-2 text-xs text-gray-600">Dec 23, 2022</div>
                  <div class="col-span-2 text-xs text-gray-600">Carl Saludo</div>
                  <div class="col-span-1 text-xs text-gray-600">Architect</div>
                  <div class="col-span-1 flex justify-end">
                    <button class="icon-btn invalid-download" title="Download"><i class="fi fi-rr-download"></i></button>
                  </div>
                </div>
                <div class="grid grid-cols-12 items-center px-4 py-3 hover:bg-gray-50">
                  <div class="col-span-6 flex items-center gap-3">
                    <input type="checkbox" class="form-checkbox rounded border-gray-300 invalid-checkbox">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600 text-xs font-bold">PDF</div>
                    <div>
                      <div class="text-sm font-medium text-gray-800">Progress Report</div>
                      <div class="text-xs text-gray-500">progress_report.pdf</div>
                    </div>
                  </div>
                  <div class="col-span-2 text-xs text-gray-600">Dec 23, 2022</div>
                  <div class="col-span-2 text-xs text-gray-600">Carl Saludo</div>
                  <div class="col-span-1 text-xs text-gray-600">Architect</div>
                  <div class="col-span-1 flex justify-end">
                    <button class="icon-btn invalid-download" title="Download"><i class="fi fi-rr-download"></i></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Delete Payment Confirmation Modal -->
    <div id="deletePaymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-[60] p-4">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all scale-95 delete-modal-panel">
        <div class="p-6 sm:p-8">
          <!-- Warning Icon -->
          <div class="flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-red-100 to-rose-100 mx-auto mb-5 delete-icon-container">
            <i class="fi fi-sr-triangle-warning text-red-600 text-4xl delete-warning-icon"></i>
          </div>
          
          <!-- Title -->
          <h3 class="text-2xl font-bold text-gray-800 text-center mb-3">Delete Payment?</h3>
          
          <!-- Description -->
          <p class="text-gray-600 text-center mb-6 leading-relaxed">
            Are you sure you want to permanently delete this proof of payment? This action cannot be undone and all associated data will be lost.
          </p>
          
          <!-- Payment Details Card -->
          <div class="bg-gradient-to-br from-red-50 to-rose-50 border-2 border-red-200 rounded-xl p-5 mb-6">
            <div class="space-y-3">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 font-medium">Payment ID:</span>
                <span id="delete-payment-id" class="font-bold text-gray-800">#—</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 font-medium">Project:</span>
                <span id="delete-project" class="font-bold text-gray-800">—</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 font-medium">Contractor:</span>
                <span id="delete-contractor" class="font-bold text-gray-800">—</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 font-medium">Amount:</span>
                <span id="delete-amount" class="font-bold text-red-600">—</span>
              </div>
            </div>
          </div>
          
          <!-- Warning Message -->
          <div class="flex items-start gap-3 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg mb-6">
            <i class="fi fi-rr-info text-red-600 text-lg mt-0.5"></i>
            <div>
              <p class="text-sm font-semibold text-red-800 mb-1">Warning: This action is irreversible!</p>
              <p class="text-xs text-red-700">All payment records, uploaded documents, and transaction history associated with this payment will be permanently removed from the system.</p>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="flex items-center gap-3">
            <button id="cancelDeletePaymentBtn" class="flex-1 px-5 py-3.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all flex items-center justify-center gap-2">
              <i class="fi fi-rr-cross-small"></i>
              <span>Cancel</span>
            </button>
            <button id="confirmDeletePaymentBtn" class="flex-1 px-5 py-3.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-semibold hover:from-red-700 hover:to-rose-700 shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2 delete-confirm-btn">
              <i class="fi fi-rr-trash"></i>
              <span>Delete</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-6 right-6 z-[120] hidden">
      <div class="toast-card"></div>
    </div>
  

  <script src="{{ asset('js/admin/globalManagement/proofOfpayments.js') }}" defer></script>

</body>

</html>