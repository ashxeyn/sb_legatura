<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/messages.css') }}">
  
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
            <a href="{{ route('admin.projectManagement.disputesReports') }}" class="submenu-link">Disputes/Reports</a>
            <a href="{{ route('admin.projectManagement.messages') }}" class="submenu-link active">Messages</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Messages</h1>

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

          <button id="composeBtn" class="hidden md:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold text-sm shadow-md hover:shadow-lg transition-all">
            <i class="fi fi-rr-edit"></i>
            <span>Compose</span>
          </button>
        </div>
      </header>

      <!-- Content -->
      <section class="px-8 py-8 space-y-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total Suspended Chats -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Total Suspended Chats</p>
                <h3 class="text-4xl font-bold text-gray-800">42</h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-red-100 to-rose-200 flex items-center justify-center">
                <i class="fi fi-sr-comment-slash text-2xl text-red-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-up"></i>
                <span>15%</span>
              </span>
              <span class="text-gray-400">vs last month</span>
            </div>
          </div>

          <!-- Active Conversations -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Active Conversations</p>
                <h3 class="text-4xl font-bold text-gray-800">238</h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-emerald-100 to-teal-200 flex items-center justify-center">
                <i class="fi fi-sr-messages text-2xl text-emerald-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-up"></i>
                <span>15%</span>
              </span>
              <span class="text-gray-400">vs last month</span>
            </div>
          </div>

          <!-- Flagged Messages -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Flagged Messages</p>
                <h3 class="text-4xl font-bold text-gray-800">17</h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-amber-100 to-orange-200 flex items-center justify-center">
                <i class="fi fi-sr-flag text-2xl text-amber-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-red-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-down"></i>
                <span>8%</span>
              </span>
              <span class="text-gray-400">vs last month</span>
            </div>
          </div>

          <!-- Response Time -->
          <div class="stat-card bg-white rounded-2xl shadow-md border border-gray-200 p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-start justify-between mb-4">
              <div>
                <p class="text-sm text-gray-500 font-medium mb-1">Avg Response Time</p>
                <h3 class="text-4xl font-bold text-gray-800">2.4<span class="text-xl text-gray-500">h</span></h3>
              </div>
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center">
                <i class="fi fi-sr-time-fast text-2xl text-blue-600"></i>
              </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="flex items-center gap-1 text-emerald-600 font-semibold">
                <i class="fi fi-rr-arrow-trend-up"></i>
                <span>12%</span>
              </span>
              <span class="text-gray-400">faster</span>
            </div>
          </div>
        </div>

        <!-- Messages Interface -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden" style="height: calc(100vh - 420px); min-height: 600px;">
          <div class="flex h-full">
            <!-- Conversations List -->
            <div class="w-full lg:w-1/3 border-r border-gray-200 flex flex-col">
              <!-- Filter Tabs -->
              <div class="px-4 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                <div class="flex items-center gap-2 bg-white rounded-xl p-1 shadow-sm">
                  <button class="filter-tab active flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all" data-filter="all">
                    All <span class="ml-1 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">238</span>
                  </button>
                  <button class="filter-tab flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all" data-filter="flagged">
                    Flagged <span class="ml-1 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">17</span>
                  </button>
                  <button class="filter-tab flex-1 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all" data-filter="suspended">
                    Suspended <span class="ml-1 text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">42</span>
                  </button>
                </div>
              </div>

              <!-- Search -->
              <div class="px-4 py-3 border-b border-gray-200">
                <div class="relative">
                  <input type="text" id="conversationSearch" placeholder="Search conversations..." class="w-full px-4 py-2.5 pl-10 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition text-sm">
                  <i class="fi fi-rr-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
              </div>

              <!-- Conversation Items -->
              <div class="flex-1 overflow-y-auto" id="conversationsList">
                <!-- Conversation items will be rendered here -->
              </div>
            </div>

            <!-- Message Details Panel -->
            <div class="hidden lg:flex flex-col flex-1" id="messagePanel">
              <!-- Empty State -->
              <div class="flex-1 flex items-center justify-center text-center p-8" id="emptyState">
                <div>
                  <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                    <i class="fi fi-sr-comment-info text-5xl text-indigo-500"></i>
                  </div>
                  <h3 class="text-xl font-semibold text-gray-800 mb-2">Select a Conversation</h3>
                  <p class="text-gray-500">Choose a conversation from the list to view messages and details</p>
                </div>
              </div>

              <!-- Message Content (hidden initially) -->
              <div class="hidden flex-col h-full" id="messageContent">
                <!-- Conversation Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                      <div class="relative">
                        <img id="selectedAvatar" src="" alt="Avatar" class="w-12 h-12 rounded-full object-cover ring-2 ring-white shadow">
                        <span id="selectedStatus" class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                      </div>
                      <div>
                        <h3 id="selectedName" class="font-semibold text-gray-800 text-lg"></h3>
                        <p id="selectedProject" class="text-sm text-gray-500"></p>
                      </div>
                    </div>
                    <div class="flex items-center gap-2">
                      <button id="flagConversationBtn" class="px-4 py-2 rounded-lg border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm font-semibold flex items-center gap-2">
                        <i class="fi fi-rr-flag"></i>
                        <span>Flag</span>
                      </button>
                      <button id="suspendConversationBtn" class="px-4 py-2 rounded-lg border-2 border-red-300 text-red-700 hover:bg-red-50 transition text-sm font-semibold flex items-center gap-2">
                        <i class="fi fi-rr-ban"></i>
                        <span>Suspend</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto p-6 bg-gray-50" id="messagesArea">
                  <!-- Messages will be rendered here -->
                </div>

                <!-- Message Info Footer -->
                <div class="px-6 py-4 border-t border-gray-200 bg-white">
                  <div class="flex items-center justify-between text-sm text-gray-600">
                    <div class="flex items-center gap-4">
                      <span class="flex items-center gap-2">
                        <i class="fi fi-rr-calendar text-indigo-600"></i>
                        <span id="conversationDate">Started: Nov 20, 2025</span>
                      </span>
                      <span class="flex items-center gap-2">
                        <i class="fi fi-rr-comment text-indigo-600"></i>
                        <span id="messageCount">45 messages</span>
                      </span>
                    </div>
                    <button id="viewFullHistoryBtn" class="text-indigo-600 hover:text-indigo-700 font-semibold flex items-center gap-1">
                      View Full History
                      <i class="fi fi-rr-arrow-right"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Flag Confirmation Modal -->
      <div id="flagConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-amber-500 to-orange-500">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center animate-pulse-slow">
                  <i class="fi fi-sr-flag text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Flag This Conversation?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-amber-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-amber-900 font-semibold mb-1">You are about to flag this conversation</p>
                <p class="text-xs text-amber-800">Flagged conversations will be marked for review. The users will not be notified of this action.</p>
              </div>
            </div>
            
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Reason for Flagging *</label>
                <select id="flagReason" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition">
                  <option value="">Select a reason...</option>
                  <option value="spam">Spam or Unwanted Content</option>
                  <option value="harassment">Harassment or Abuse</option>
                  <option value="inappropriate">Inappropriate Language</option>
                  <option value="scam">Potential Scam</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Additional Notes (Optional)</label>
                <textarea id="flagNotes" rows="3" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-300 focus:border-amber-300 transition resize-none" placeholder="Provide additional context about why you're flagging this conversation..."></textarea>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-all transform hover:scale-105">Cancel</button>
              <button id="confirmFlagBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                <span class="flex items-center gap-2">
                  <i class="fi fi-rr-flag"></i>
                  <span>Flag Conversation</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Suspend Confirmation Modal -->
      <div id="suspendConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-red-600 to-rose-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center animate-pulse-slow">
                  <i class="fi fi-sr-ban text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Suspend This Conversation?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
              <i class="fi fi-rr-triangle-warning text-red-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-red-900 font-semibold mb-1">Warning: This is a serious action</p>
                <p class="text-xs text-red-800">Suspending this conversation will prevent both parties from sending further messages. They will be notified of the suspension.</p>
              </div>
            </div>
            
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Reason for Suspension *</label>
                <select id="suspendReason" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition">
                  <option value="">Select a reason...</option>
                  <option value="violation">Terms of Service Violation</option>
                  <option value="harassment">Harassment or Threatening Behavior</option>
                  <option value="fraud">Fraudulent Activity</option>
                  <option value="spam">Repeated Spam</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Suspension Duration *</label>
                <select id="suspendDuration" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition">
                  <option value="24h">24 Hours</option>
                  <option value="7d">7 Days</option>
                  <option value="30d">30 Days</option>
                  <option value="permanent">Permanent</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Additional Details (Optional)</label>
                <textarea id="suspendNotes" rows="3" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-300 focus:border-red-300 transition resize-none" placeholder="Provide additional context about the suspension..."></textarea>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-all transform hover:scale-105">Cancel</button>
              <button id="confirmSuspendBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                <span class="flex items-center gap-2">
                  <i class="fi fi-rr-ban"></i>
                  <span>Suspend Conversation</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Restore Confirmation Modal -->
      <div id="restoreConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-emerald-600 to-teal-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center animate-pulse-slow">
                  <i class="fi fi-sr-check-circle text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Restore This Conversation?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-emerald-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-emerald-900 font-semibold mb-1">You are about to restore this conversation</p>
                <p class="text-xs text-emerald-800">Restoring will allow both parties to resume messaging. Any previous suspension or flag will be removed.</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Conversation ID:</span>
                  <span class="font-semibold text-gray-800" id="restoreConvId">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Participants:</span>
                  <span class="font-semibold text-gray-800" id="restoreConvName">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Current Status:</span>
                  <span class="font-semibold text-red-600" id="restoreConvStatus">Suspended</span>
                </div>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-semibold text-gray-800 mb-2">Restoration Notes (Optional)</label>
              <textarea id="restoreNotes" rows="3" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-300 focus:border-emerald-300 transition resize-none" placeholder="Add notes about why this conversation is being restored..."></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-all transform hover:scale-105">Cancel</button>
              <button id="confirmRestoreBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                <span class="flex items-center gap-2">
                  <i class="fi fi-rr-check-circle"></i>
                  <span>Restore Conversation</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Unflag Confirmation Modal -->
      <div id="unflagConfirmModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
          <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center animate-pulse-slow">
                  <i class="fi fi-sr-flag text-white text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Remove Flag?</h3>
              </div>
              <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
            </div>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
              <i class="fi fi-rr-info-circle text-indigo-600 text-xl mt-0.5"></i>
              <div class="flex-1">
                <p class="text-sm text-indigo-900 font-semibold mb-1">Remove flag from this conversation</p>
                <p class="text-xs text-indigo-800">This will clear the flag status. The conversation will no longer appear in the flagged filter.</p>
              </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
              <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Conversation:</span>
                  <span class="font-semibold text-gray-800" id="unflagConvName">-</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Current Status:</span>
                  <span class="font-semibold text-amber-600">Flagged</span>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200">
              <button class="modal-close px-6 py-2.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-all transform hover:scale-105">Cancel</button>
              <button id="confirmUnflagBtn" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                <span class="flex items-center gap-2">
                  <i class="fi fi-rr-check"></i>
                  <span>Remove Flag</span>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Compose New Message Modal -->
      <div id="composeModal" class="modal-overlay fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col">
          <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                <i class="fi fi-sr-messages text-white text-lg"></i>
              </div>
              <h3 class="text-lg font-bold text-white">Compose New Message</h3>
            </div>
            <button class="modal-close text-white/80 hover:text-white transition text-2xl leading-none">&times;</button>
          </div>
          <div class="p-6 space-y-4 overflow-y-auto flex-1">
            <!-- Recipient Type Tabs -->
            <div class="flex items-center gap-2 bg-gray-100 rounded-xl p-1">
              <button class="compose-tab active flex-1 px-4 py-2 rounded-lg text-sm font-semibold" data-type="contractor">Contractor</button>
              <button class="compose-tab flex-1 px-4 py-2 rounded-lg text-sm font-semibold" data-type="property_owner">Property Owner</button>
            </div>

            <!-- Recipients & Context -->
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">To *</label>
                <div id="composeRecipientsWrapper" class="compose-recipients-wrapper w-full flex flex-wrap gap-2 px-3 py-2 border-2 border-gray-200 rounded-xl focus-within:ring-2 focus-within:ring-indigo-300 focus-within:border-indigo-300 bg-white cursor-text">
                  <input id="composeRecipientSearch" type="text" class="flex-1 min-w-[140px] outline-none text-sm bg-transparent" placeholder="Type a name or press Enter..." autocomplete="off">
                </div>
                <div id="composeRecipientDropdown" class="compose-recipient-dropdown hidden mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto text-sm"></div>
                <p class="text-xs text-gray-500 mt-1">Add multiple recipients. Press Enter or comma to add. Max 8.</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Project / Context (Optional)</label>
                <input id="composeProject" type="text" class="w-full px-4 py-2 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition text-sm" placeholder="Project or discussion context">
              </div>
            </div>

            <!-- Message & Attachments -->
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Message *</label>
                <textarea id="composeMessage" rows="4" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition resize-none text-sm leading-relaxed" placeholder="Write your message here..."></textarea>
                <p id="composeCharCount" class="text-xs text-gray-400 mt-1">0 / 1000</p>
              </div>
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="block text-sm font-semibold text-gray-700">Attachments</label>
                  <span class="text-xs text-gray-400">Images, PDF, DOC (max 5MB each)</span>
                </div>
                <div id="composeAttachmentDrop" class="compose-attachment-drop border-2 border-dashed border-gray-300 rounded-xl p-4 flex flex-col items-center justify-center gap-2 text-center cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                  <i class="fi fi-rr-folder-upload text-xl text-indigo-500"></i>
                  <p class="text-xs text-gray-600">Drag & drop files here or click to browse</p>
                  <input id="composeAttachmentInput" type="file" class="hidden" multiple accept="image/*,.pdf,.doc,.docx">
                </div>
                <div id="composeAttachmentPreview" class="compose-attachment-preview mt-3 flex flex-wrap gap-2"></div>
              </div>
            </div>
          </div>
          
          <!-- Actions (Fixed Footer) -->
          <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <i class="fi fi-rr-shield-check text-indigo-500"></i>
                <span>Messages are monitored for policy compliance.</span>
              </div>
              <div class="flex items-center gap-3">
                <button class="modal-close px-4 py-2 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition text-sm">Cancel</button>
                <button id="sendComposeBtn" class="px-5 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 text-sm">
                  <i class="fi fi-rr-paper-plane"></i>
                  <span>Send Message</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </main>
  

  <script src="{{ asset('js/admin/projectManagement/messages.js') }}" defer></script>

</body>

</html>