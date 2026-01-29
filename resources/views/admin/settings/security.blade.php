<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/settings/security.css') }}">
  
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
            <a href="{{ route('admin.settings.security') }}" class="submenu-link active">Security</a>
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
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow">
            <i class="fi fi-ss-lock text-white text-lg"></i>
          </div>
          <h1 class="text-2xl font-semibold text-gray-800">Security</h1>
        </div>

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

      <!-- Content -->
      <section class="px-8 py-8 space-y-8">
        <!-- Intro Banner -->
        <div class="rounded-2xl border-2 border-indigo-100 bg-gradient-to-r from-indigo-50 via-purple-50 to-violet-50 p-5">
          <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow">
              <i class="fi fi-ss-shield-check text-white"></i>
            </div>
            <div class="flex-1">
              <h2 class="text-lg font-bold text-gray-800">Account Security & Access Control</h2>
              <p class="text-sm text-gray-600">Protect your admin account with multi-factor authentication, monitor active sessions, review login history, and configure security policies.</p>
            </div>
            <div class="flex items-center gap-2">
              <button id="exportSecurityLogBtn" class="px-4 py-2 rounded-lg bg-white border-2 border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fi fi-rr-download"></i>
                <span>Export Log</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Account Information Card -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b bg-gradient-to-r from-orange-500 to-amber-500 text-white">
            <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-id-badge"></i><span>Account Information</span></div>
            <p class="text-xs opacity-80 mt-1">Manage your profile details</p>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
              <!-- Avatar -->
              <div class="flex flex-col items-center gap-3">
                <div class="relative group">
                  <img id="profileAvatar" src="https://via.placeholder.com/120x120.png?text=Avatar" alt="Avatar" class="w-28 h-28 rounded-full object-cover ring-4 ring-orange-100 shadow-md">
                  <label for="avatarInput" class="absolute bottom-0 right-0 translate-x-1/4 translate-y-1/4 bg-white rounded-full p-2 shadow cursor-pointer border hover:shadow-md transition">
                    <i class="fi fi-rr-pencil text-gray-700"></i>
                  </label>
                  <input id="avatarInput" type="file" accept="image/*" class="hidden" />
                </div>
                <p class="text-xs text-gray-500">PNG/JPG up to 2MB</p>
              </div>

              <!-- Fields -->
              <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label class="block text-sm font-semibold text-gray-800 mb-2">Email</label>
                  <input id="accountEmail" type="email" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition placeholder-gray-400" placeholder="olive@legatura.com" value="olive@legatura.com">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-800 mb-2">Username</label>
                  <input id="accountUsername" type="text" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition placeholder-gray-400" placeholder="olive" value="olive">
                </div>
                <div class="md:col-span-2 flex justify-end">
                  <button id="saveProfileBtn" class="px-6 py-2.5 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Save Profile</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Security Settings Card -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
            <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-lock"></i><span>Security Settings</span></div>
            <p class="text-xs opacity-80 mt-1">Update your password and authentication</p>
          </div>
          <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                  <label class="block text-sm font-semibold text-gray-800 mb-2">New Password</label>
                  <div class="relative">
                    <input id="newPassword" type="password" class="w-full pr-12 px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition" placeholder="••••••••">
                    <button type="button" data-target="newPassword" class="toggle-visibility absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"><i class="fi fi-rr-eye"></i></button>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-800 mb-2">Confirm Password</label>
                  <div class="relative">
                    <input id="confirmPassword" type="password" class="w-full pr-12 px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition" placeholder="••••••••">
                    <button type="button" data-target="confirmPassword" class="toggle-visibility absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"><i class="fi fi-rr-eye"></i></button>
                  </div>
                </div>
                <div class="md:col-span-2">
                  <div class="flex items-center justify-between text-xs text-gray-600 mb-2"><span>Password strength</span><span id="strengthLabel" class="font-semibold">Weak</span></div>
                  <div class="strength-bar h-2 rounded-full bg-gray-200 overflow-hidden"><div id="strengthProgress" class="h-full w-1/12 bg-red-500 transition-all"></div></div>
                </div>
              </div>

              <div class="space-y-3">
                <div class="text-sm font-semibold text-gray-800">Requirements</div>
                <ul id="requirementsList" class="space-y-2 text-sm">
                  <li data-req="len" class="req-item flex items-center gap-2 text-gray-500"><i class="fi fi-rr-circle-small"></i>At least 8 characters</li>
                  <li data-req="upper" class="req-item flex items-center gap-2 text-gray-500"><i class="fi fi-rr-circle-small"></i>Contains an uppercase letter</li>
                  <li data-req="num" class="req-item flex items-center gap-2 text-gray-500"><i class="fi fi-rr-circle-small"></i>Contains a number</li>
                  <li data-req="sym" class="req-item flex items-center gap-2 text-gray-500"><i class="fi fi-rr-circle-small"></i>Contains a symbol</li>
                  <li data-req="match" class="req-item flex items-center gap-2 text-gray-500"><i class="fi fi-rr-circle-small"></i>Passwords match</li>
                </ul>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3">
              <button id="updatePasswordBtn" class="px-8 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md hover:shadow-lg transition">Update Password</button>
            </div>
          </div>
        </div>

        <!-- Two-Factor Authentication -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b bg-gradient-to-r from-emerald-500 to-teal-600 text-white">
            <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-mobile-notch"></i><span>Two-Factor Authentication</span></div>
            <p class="text-xs opacity-80 mt-1">Add an extra layer of security to your account</p>
          </div>
          <div class="p-6">
            <div class="flex items-start gap-6">
              <div class="flex-shrink-0 w-16 h-16 rounded-xl bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                <i class="fi fi-sr-shield-check text-emerald-600 text-2xl"></i>
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                  <h3 class="text-lg font-bold text-gray-800">Authenticator App (Recommended)</h3>
                  <span id="twoFaStatus" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Disabled</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">Use an authenticator app like Google Authenticator or Authy to generate secure verification codes.</p>
                <div class="flex items-center gap-3">
                  <button id="enableTwoFaBtn" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold shadow-md transition">Enable 2FA</button>
                  <button id="disableTwoFaBtn" class="hidden px-5 py-2.5 rounded-lg border-2 border-red-300 text-red-600 font-semibold hover:bg-red-50 transition">Disable 2FA</button>
                  <button id="viewRecoveryCodesBtn" class="hidden px-5 py-2.5 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition">Recovery Codes</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Session Management -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b bg-gradient-to-r from-sky-500 to-blue-600 text-white">
            <div class="flex items-center justify-between">
              <div>
                <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-devices"></i><span>Active Sessions</span></div>
                <p class="text-xs opacity-80 mt-1">Manage devices connected to your account</p>
              </div>
              <button id="logoutAllSessionsBtn" class="px-4 py-2 rounded-lg bg-white/20 hover:bg-white/30 text-white text-sm font-semibold transition">Logout All</button>
            </div>
          </div>
          <div class="p-6">
            <div class="space-y-4" id="activeSessions">
              <!-- Current Session -->
              <div class="session-item flex items-start gap-4 p-4 rounded-xl border-2 border-sky-100 bg-sky-50">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center">
                  <i class="fi fi-sr-laptop text-white text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <h4 class="font-bold text-gray-800">Windows Desktop • Chrome</h4>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Current</span>
                  </div>
                  <p class="text-sm text-gray-600">Manila, Philippines • 192.168.1.100</p>
                  <p class="text-xs text-gray-500 mt-1">Last active: Just now</p>
                </div>
              </div>

              <!-- Other Sessions -->
              <div class="session-item flex items-start gap-4 p-4 rounded-xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                  <i class="fi fi-sr-mobile text-gray-600 text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="font-bold text-gray-800 mb-1">iPhone 14 • Safari</h4>
                  <p class="text-sm text-gray-600">Quezon City, Philippines • 10.0.0.52</p>
                  <p class="text-xs text-gray-500 mt-1">Last active: 2 hours ago</p>
                </div>
                <button class="revoke-session-btn flex-shrink-0 p-2 rounded-lg text-red-600 hover:bg-red-50 transition" title="Revoke">
                  <i class="fi fi-rr-cross-circle"></i>
                </button>
              </div>

              <div class="session-item flex items-start gap-4 p-4 rounded-xl border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                  <i class="fi fi-sr-tablet text-gray-600 text-xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <h4 class="font-bold text-gray-800 mb-1">iPad Pro • Safari</h4>
                  <p class="text-sm text-gray-600">Manila, Philippines • 192.168.1.105</p>
                  <p class="text-xs text-gray-500 mt-1">Last active: Yesterday</p>
                </div>
                <button class="revoke-session-btn flex-shrink-0 p-2 rounded-lg text-red-600 hover:bg-red-50 transition" title="Revoke">
                  <i class="fi fi-rr-cross-circle"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Login History -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b bg-gradient-to-r from-violet-500 to-purple-600 text-white">
            <div class="flex items-center justify-between">
              <div>
                <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-time-past"></i><span>Login History</span></div>
                <p class="text-xs opacity-80 mt-1">Review recent authentication attempts</p>
              </div>
              <button id="clearHistoryBtn" class="px-4 py-2 rounded-lg bg-white/20 hover:bg-white/30 text-white text-sm font-semibold transition">Clear History</button>
            </div>
          </div>
          <div class="p-6">
            <div class="overflow-hidden rounded-xl border border-gray-200">
              <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr class="text-xs font-bold text-gray-600 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Date & Time</th>
                    <th class="px-4 py-3 text-left">Device</th>
                    <th class="px-4 py-3 text-left">Location</th>
                    <th class="px-4 py-3 text-left">IP Address</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="loginHistoryTable">
                  <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        <i class="fi fi-sr-check-circle mr-1"></i>Success
                      </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-800">Dec 2, 2025 09:15 AM</td>
                    <td class="px-4 py-3 text-sm text-gray-600">Windows • Chrome</td>
                    <td class="px-4 py-3 text-sm text-gray-600">Manila, PH</td>
                    <td class="px-4 py-3 text-sm text-gray-600 font-mono">192.168.1.100</td>
                  </tr>
                  <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        <i class="fi fi-sr-check-circle mr-1"></i>Success
                      </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-800">Dec 1, 2025 07:30 PM</td>
                    <td class="px-4 py-3 text-sm text-gray-600">iPhone • Safari</td>
                    <td class="px-4 py-3 text-sm text-gray-600">Quezon City, PH</td>
                    <td class="px-4 py-3 text-sm text-gray-600 font-mono">10.0.0.52</td>
                  </tr>
                  <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                        <i class="fi fi-sr-cross-circle mr-1"></i>Failed
                      </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-800">Nov 30, 2025 11:45 PM</td>
                    <td class="px-4 py-3 text-sm text-gray-600">Unknown • Unknown</td>
                    <td class="px-4 py-3 text-sm text-gray-600">Unknown</td>
                    <td class="px-4 py-3 text-sm text-gray-600 font-mono">203.0.113.42</td>
                  </tr>
                  <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        <i class="fi fi-sr-check-circle mr-1"></i>Success
                      </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-800">Nov 30, 2025 08:00 AM</td>
                    <td class="px-4 py-3 text-sm text-gray-600">iPad • Safari</td>
                    <td class="px-4 py-3 text-sm text-gray-600">Manila, PH</td>
                    <td class="px-4 py-3 text-sm text-gray-600 font-mono">192.168.1.105</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Security Preferences -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b bg-gradient-to-r from-rose-500 to-pink-600 text-white">
            <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-settings-sliders"></i><span>Security Preferences</span></div>
            <p class="text-xs opacity-80 mt-1">Configure authentication policies and alerts</p>
          </div>
          <div class="p-6 space-y-4">
            <div class="setting-row flex items-center justify-between py-3 border-b border-gray-100">
              <div>
                <div class="font-medium text-gray-800">Email notifications for new logins</div>
                <div class="text-xs text-gray-500">Get notified when your account is accessed from a new device</div>
              </div>
              <label class="switch">
                <input type="checkbox" class="security-toggle" data-setting="login_notifications" checked>
                <span class="slider"></span>
              </label>
            </div>
            <div class="setting-row flex items-center justify-between py-3 border-b border-gray-100">
              <div>
                <div class="font-medium text-gray-800">Require password for sensitive actions</div>
                <div class="text-xs text-gray-500">Re-enter password when changing security settings</div>
              </div>
              <label class="switch">
                <input type="checkbox" class="security-toggle" data-setting="password_confirmation" checked>
                <span class="slider"></span>
              </label>
            </div>
            <div class="setting-row flex items-center justify-between py-3 border-b border-gray-100">
              <div>
                <div class="font-medium text-gray-800">Auto-logout on inactivity</div>
                <div class="text-xs text-gray-500">Automatically sign out after 30 minutes of inactivity</div>
              </div>
              <label class="switch">
                <input type="checkbox" class="security-toggle" data-setting="auto_logout">
                <span class="slider"></span>
              </label>
            </div>
            <div class="setting-row flex items-center justify-between py-3 border-b border-gray-100">
              <div>
                <div class="font-medium text-gray-800">Block suspicious login attempts</div>
                <div class="text-xs text-gray-500">Prevent access from unrecognized locations or devices</div>
              </div>
              <label class="switch">
                <input type="checkbox" class="security-toggle" data-setting="block_suspicious" checked>
                <span class="slider"></span>
              </label>
            </div>
            <div class="setting-row flex items-center justify-between py-3">
              <div>
                <div class="font-medium text-gray-800">Security audit log</div>
                <div class="text-xs text-gray-500">Keep detailed logs of all security-related actions</div>
              </div>
              <label class="switch">
                <input type="checkbox" class="security-toggle" data-setting="audit_log" checked>
                <span class="slider"></span>
              </label>
            </div>
          </div>
        </div>

      </section>

    </main>
  

  <script src="{{ asset('js/admin/settings/security.js') }}" defer></script>

</body>

</html>