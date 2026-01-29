<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/settings/notifications.css') }}">
  
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
            <a href="{{ route('admin.settings.notifications') }}" class="submenu-link active">Notifications</a>
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
        <div class="flex items-center gap-3">
          <h1 class="text-2xl font-semibold text-gray-800">Notifications</h1>
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
      
      <section class="px-8 py-8">
        <!-- Page Intro -->
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h2 class="text-xl font-semibold text-gray-800">Notification Preferences</h2>
            <p class="text-sm text-gray-500">Choose what to be notified about and how you want to receive it.</p>
          </div>
          <button id="resetDefaultsBtn" class="px-4 py-2 rounded-lg border-2 border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition">
            Reset to defaults
          </button>
        </div>

        <!-- Settings Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- General -->
          <div class="setting-card bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-orange-500 to-amber-500 text-white">
              <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-bell-ring"></i><span>General Notifications</span></div>
              <p class="text-xs opacity-80 mt-1">Core system alerts and billing events</p>
            </div>
            <div class="p-6 space-y-4">
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Remind Before Subscription Expiration</div>
                  <div class="text-xs text-gray-500">Send reminders 7 days before plan expires.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="remind_before_expiration">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Payment Processed</div>
                  <div class="text-xs text-gray-500">Notify when a payment is successfully processed.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="payment_processed">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Payment Failed</div>
                  <div class="text-xs text-gray-500">Alert if a payment fails or is declined.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="payment_failed">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Plan Upgraded/Downgraded</div>
                  <div class="text-xs text-gray-500">Alert when a subscription plan changes.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="plan_changed">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Upcoming Maintenance Window</div>
                  <div class="text-xs text-gray-500">Inform admins about scheduled system maintenance.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="maintenance_window">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Low Storage Threshold</div>
                  <div class="text-xs text-gray-500">Notify when storage usage exceeds 80%.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="low_storage_threshold">
                  <span class="slider"></span>
                </label>
              </div>
            </div>
          </div>

          <!-- User Activity -->
          <div class="setting-card bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gradient-to-r from-indigo-500 to-violet-600 text-white">
              <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-users"></i><span>User Activity Notifications</span></div>
              <p class="text-xs opacity-80 mt-1">Account and security related updates</p>
            </div>
            <div class="p-6 space-y-4">
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">New User Registration</div>
                  <div class="text-xs text-gray-500">Get notified when new users sign up.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="user_registered">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Failed Login Attempt</div>
                  <div class="text-xs text-gray-500">Security alert for repeated failed attempts.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="failed_login_attempt">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Project Reported</div>
                  <div class="text-xs text-gray-500">Alert when a project is reported by users.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="project_reported">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Profile Updated</div>
                  <div class="text-xs text-gray-500">Notify when a user changes account details.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="profile_updated">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Password Reset Requested</div>
                  <div class="text-xs text-gray-500">Alert for password reset requests and completions.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="password_reset">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Email Verified</div>
                  <div class="text-xs text-gray-500">Notify when a user verifies their email address.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="email_verified">
                  <span class="slider"></span>
                </label>
              </div>
              <div class="setting-row">
                <div>
                  <div class="font-medium text-gray-800">Account Suspended/Unsuspended</div>
                  <div class="text-xs text-gray-500">Alert when moderation changes account status.</div>
                </div>
                <label class="switch">
                  <input type="checkbox" class="setting-toggle" data-setting="account_status_changed">
                  <span class="slider"></span>
                </label>
              </div>
            </div>
          </div>

          <!-- Channels -->
          <div class="bg-transparent space-y-6">
            <div class="setting-card bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
              <div class="px-6 py-4 border-b bg-gradient-to-r from-emerald-500 to-teal-600 text-white">
                <div class="flex items-center gap-2 font-semibold"><i class="fi fi-ss-megaphone"></i><span>Notification Channels</span></div>
                <p class="text-xs opacity-80 mt-1">Choose how you receive alerts</p>
              </div>
              <div class="p-6 space-y-4">
                <div class="setting-row">
                  <div>
                    <div class="font-medium text-gray-800">Email Notifications</div>
                    <div class="text-xs text-gray-500">Receive important updates in your inbox.</div>
                  </div>
                  <label class="switch">
                    <input type="checkbox" class="setting-toggle" data-setting="channel_email">
                    <span class="slider"></span>
                  </label>
                </div>
                <div class="setting-row">
                  <div>
                    <div class="font-medium text-gray-800">SMS Notifications</div>
                    <div class="text-xs text-gray-500">Time-sensitive alerts via text message.</div>
                  </div>
                  <label class="switch">
                    <input type="checkbox" class="setting-toggle" data-setting="channel_sms">
                    <span class="slider"></span>
                  </label>
                </div>
                <div class="setting-row">
                  <div>
                    <div class="font-medium text-gray-800">In‑App Notifications</div>
                    <div class="text-xs text-gray-500">Show alerts in the dashboard bell.</div>
                  </div>
                  <label class="switch">
                    <input type="checkbox" class="setting-toggle" data-setting="channel_inapp">
                    <span class="slider"></span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky Save Bar -->
        <div id="saveBar" class="save-bar hidden fixed left-1/2 -translate-x-1/2 bottom-6 z-40">
          <div class="bg-white border border-gray-200 rounded-xl shadow-2xl px-4 py-3 flex items-center gap-3">
            <span class="text-sm text-gray-700">You have unsaved changes</span>
            <button id="saveSettingsBtn" class="px-4 py-2 text-sm rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold shadow-md">Save changes</button>
          </div>
        </div>
      </section>
    </main>
  

  <script src="{{ asset('js/admin/settings/notifications.js') }}" defer></script>

</body>

</html>