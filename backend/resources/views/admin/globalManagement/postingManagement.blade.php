<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/globalManagement/postingManagement.css') }}">
  
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
            <a href="{{ route('admin.globalManagement.postingManagement') }}" class="submenu-link active">Posting Management</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Posting Management</h1>

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
              <tbody class="divide-y divide-gray-200" id="contractorsTable">
                <!-- Data loaded from API -->
              </tbody>
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">GTH Builders and Developers</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">10 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Contractor</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="GTH Builders and Developers" data-date="10 Oct, 2025" data-type="Contractor">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="GTH Builders and Developers">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="GTH Builders and Developers">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        CA
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Cabanating Architects Design & Construction</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">09 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Contractor</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Cabanating Architects Design & Construction" data-date="09 Oct, 2025" data-type="Contractor">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Cabanating Architects Design & Construction">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Cabanating Architects Design & Construction">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        RC
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">RCDG Construction Corporation</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">08 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Contractor</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="RCDG Construction Corporation" data-date="08 Oct, 2025" data-type="Contractor">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="RCDG Construction Corporation">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="RCDG Construction Corporation">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        SB
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Summit Builders Inc.</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">07 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Contractor</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Summit Builders Inc." data-date="07 Oct, 2025" data-type="Contractor">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Summit Builders Inc.">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Summit Builders Inc.">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-pink-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        PE
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Prime Engineering Solutions</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">06 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Contractor</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Prime Engineering Solutions" data-date="06 Oct, 2025" data-type="Contractor">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Prime Engineering Solutions">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Prime Engineering Solutions">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        MC
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Metro Construction Group</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">05 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Contractor</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Metro Construction Group" data-date="05 Oct, 2025" data-type="Contractor">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Metro Construction Group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Metro Construction Group">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-teal-400 to-teal-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        AB
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Apex Building Contractors</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">04 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Contractor</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Apex Building Contractors" data-date="04 Oct, 2025" data-type="Contractor">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Apex Building Contractors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Apex Building Contractors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-rose-400 to-rose-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        HD
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Horizon Development Corp</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">03 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Contractor</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Horizon Development Corp" data-date="03 Oct, 2025" data-type="Contractor">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Horizon Development Corp">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Horizon Development Corp">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-lime-400 to-lime-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        VB
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Vertex Builders & Associates</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">02 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Contractor</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Vertex Builders & Associates" data-date="02 Oct, 2025" data-type="Contractor">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Vertex Builders & Associates">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Vertex Builders & Associates">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        FC
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Foundation Concepts Ltd</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">01 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Contractor</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Foundation Concepts Ltd" data-date="01 Oct, 2025" data-type="Contractor">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Foundation Concepts Ltd">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Foundation Concepts Ltd">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
              <tbody class="divide-y divide-gray-200 hidden" id="propertyOwnersTable">
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        JD
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Juan Dela Cruz</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">01 Nov, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Property Owner</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Juan Dela Cruz" data-date="01 Nov, 2025" data-type="Property Owner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Juan Dela Cruz">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Juan Dela Cruz">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-violet-400 to-violet-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        MR
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Maria Rodriguez</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">30 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Property Owner</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Maria Rodriguez" data-date="30 Oct, 2025" data-type="Property Owner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Maria Rodriguez">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Maria Rodriguez">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        RT
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Roberto Tan</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">29 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Property Owner</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Roberto Tan" data-date="29 Oct, 2025" data-type="Property Owner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Roberto Tan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Roberto Tan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        AS
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Anna Santos</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">28 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Property Owner</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Anna Santos" data-date="28 Oct, 2025" data-type="Property Owner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Anna Santos">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Anna Santos">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        DG
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">David Garcia</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">27 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Property Owner</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="David Garcia" data-date="27 Oct, 2025" data-type="Property Owner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="David Garcia">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="David Garcia">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-fuchsia-400 to-fuchsia-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        LC
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Linda Chen</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">26 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Property Owner</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Linda Chen" data-date="26 Oct, 2025" data-type="Property Owner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Linda Chen">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Linda Chen">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-slate-400 to-slate-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        PM
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Peter Martinez</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">25 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Property Owner</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Peter Martinez" data-date="25 Oct, 2025" data-type="Property Owner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Peter Martinez">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Peter Martinez">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        SL
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">Sarah Lee</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">24 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Property Owner</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="Sarah Lee" data-date="24 Oct, 2025" data-type="Property Owner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="Sarah Lee">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="Sarah Lee">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-full flex items-center justify-center text-white font-bold shadow">
                        JW
                      </div>
                      <div>
                        <div class="font-semibold text-gray-800">James Wilson</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">23 Oct, 2025</td>
                  <td class="px-6 py-4 text-sm text-gray-700">Property Owner</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                      Pending
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <button class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-btn" data-name="James Wilson" data-date="23 Oct, 2025" data-type="Property Owner">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-green-50 hover:bg-green-100 text-green-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 approve-btn" data-name="James Wilson">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      <button class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 decline-btn" data-name="James Wilson">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- View Modal -->
      <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300 scale-95 modal-content">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-5 flex items-center justify-between rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">Post Details</h3>
            <button class="text-white hover:text-gray-200 transition-colors duration-200 close-modal">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="p-6 space-y-5">
            <!-- Header Info -->
            <div class="flex items-center gap-4 pb-4 border-b">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-lg" id="modalAvatar">GD</div>
              <div>
                <h4 class="text-xl font-bold text-gray-800" id="modalName">Panda Construction Company</h4>
                <div class="flex items-center gap-2">
                  <p class="text-sm text-gray-500" id="modalType">Contractor</p>
                  <span class="text-gray-300">•</span>
                  <button id="copyHandleBtn" class="text-xs px-2 py-0.5 bg-white border border-gray-200 rounded-md hover:bg-gray-50 transition">Copy handle</button>
                  <span id="copyHandleTip" class="text-xs text-green-600 hidden">Copied</span>
                </div>
              </div>
            </div>

            <!-- Quick meta -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600 mb-1">Date Registered</p>
                <p class="font-semibold text-gray-800" id="modalDate">10 Oct, 2025</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Account Type</p>
                <p class="font-semibold text-gray-800" id="modalAccountType">Contractor</p>
              </div>
            </div>

            <!-- Social-style preview card -->
            <div class="rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
              <!-- Card header -->
              <div class="px-5 pt-4 pb-3 flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-gray-200 overflow-hidden flex items-center justify-center">
                  <img id="viewCardAvatar" src="https://i.pravatar.cc/80?img=5" alt="avatar" class="w-full h-full object-cover"/>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-semibold text-gray-900 truncate" id="viewCardCompany">Panda Construction Company</p>
                  <p class="text-sm text-gray-500 truncate"><span id="viewCardHandle">@pcc_official</span></p>
                </div>
                <div class="flex items-center gap-2">
                  <button id="downloadImageBtn" class="p-2 rounded-full hover:bg-gray-100" title="Download image">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12v9m0-9l-3 3m3-3l3 3M12 3v9"/></svg>
                  </button>
                </div>
              </div>
              <!-- Card image -->
              <div id="viewImageWrapper" class="relative bg-gray-100">
                <img id="viewCardImage" src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?q=80&w=1600&auto=format&fit=crop" alt="project" class="view-image w-full max-h-80 object-cover select-none"/>
                <div class="absolute bottom-3 right-3 text-xs bg-black bg-opacity-60 text-white px-2 py-1 rounded-md pointer-events-none">Click to zoom</div>
              </div>
              <!-- Card body -->
              <div class="px-5 py-4">
                <p class="font-semibold text-gray-900 mb-1" id="viewCardTitle">Modern Two-Storey House Project</p>
                <button id="viewMoreToggle" class="text-sky-600 hover:underline text-sm">More details...</button>
                <div id="viewMoreContent" class="mt-3 hidden">
                  <p class="text-sm text-gray-700">A contemporary residential build featuring open-plan living, floor-to-ceiling glazing, and energy-efficient materials. Includes 3 bedrooms, 2.5 baths, and a roof deck.</p>
                </div>
                <p class="text-xs text-gray-500 mt-4" id="viewCardTimestamp">1:12 PM • June 3, 2021</p>
              </div>
            </div>

            <!-- Additional info -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600 mb-1">Email</p>
                <p class="font-semibold text-gray-800">gth.builders@example.com</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Phone</p>
                <p class="font-semibold text-gray-800">+63 912 345 6789</p>
              </div>
              <div class="col-span-2">
                <p class="text-sm text-gray-600 mb-1">Address</p>
                <p class="font-semibold text-gray-800">Tetuan District, Zamboanga City</p>
              </div>
            </div>
          </div>
          <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end">
            <button class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-200 close-modal">Close</button>
          </div>
        </div>
      </div>

      <!-- Approve Modal -->
      <div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 modal-content">
          <!-- Header -->
          <div class="bg-gradient-to-r from-green-500 via-green-600 to-emerald-600 px-6 py-6 flex items-center justify-between rounded-t-3xl relative overflow-hidden">
            <div class="absolute inset-0 bg-white opacity-10">
              <div class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
              <div class="absolute bottom-0 right-0 w-40 h-40 bg-white rounded-full blur-3xl opacity-20 translate-x-1/2 translate-y-1/2"></div>
            </div>
            <div class="relative flex items-center gap-3">
              <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <h3 class="text-xl font-bold text-white">Approve Post</h3>
            </div>
            <button class="relative text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all duration-200 close-modal">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- Content -->
          <div class="p-8">
            <!-- Icon with animation -->
            <div class="flex items-center justify-center mb-6">
              <div class="relative">
                <div class="w-24 h-24 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center shadow-lg approve-icon-container">
                  <svg class="w-12 h-12 text-green-600 approve-checkmark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
                <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-20"></div>
              </div>
            </div>
            
            <!-- Message -->
            <div class="text-center mb-6">
              <h4 class="text-2xl font-bold text-gray-800 mb-2">Confirm Approval</h4>
              <p class="text-gray-600 mb-4">You are about to approve this post submission</p>
              <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 mb-4">
                <p class="text-sm text-green-800 font-medium mb-1">Account Name</p>
                <p class="text-lg font-bold text-green-900" id="approveModalName">GTH Builders and Developers</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-blue-800 text-left">Once approved, the user will receive a confirmation, and their post will be published.</p>
              </div>
            </div>
          </div>
          
          <!-- Footer Actions -->
          <div class="px-8 py-6 bg-gradient-to-b from-gray-50 to-gray-100 rounded-b-3xl flex justify-end gap-3">
            <button class="px-6 py-3 bg-white border-2 border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-all duration-200 hover:scale-105 close-modal">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
              </span>
            </button>
            <button class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl" id="confirmApprove">
              <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Approve Post</span>
                <svg class="w-4 h-4 approve-loading hidden animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
            </button>
          </div>
        </div>
      </div>

      <!-- Decline Modal -->
      <div id="declineModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 modal-content">
          <!-- Header -->
          <div class="bg-gradient-to-r from-red-500 via-red-600 to-rose-600 px-6 py-5 flex items-center justify-between rounded-t-3xl relative overflow-hidden">
            <div class="absolute inset-0 bg-white opacity-10">
              <div class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
              <div class="absolute bottom-0 right-0 w-40 h-40 bg-white rounded-full blur-3xl opacity-20 translate-x-1/2 translate-y-1/2"></div>
            </div>
            <div class="relative flex items-center gap-3">
              <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </div>
              <h3 class="text-xl font-bold text-white">Decline Post</h3>
            </div>
            <button class="relative text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all duration-200 close-modal">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- Content -->
          <div class="p-6">
            <!-- Icon with animation -->
            <div class="flex items-center justify-center mb-4">
              <div class="relative">
                <div class="w-20 h-20 bg-gradient-to-br from-rose-100 to-red-100 rounded-full flex items-center justify-center shadow-lg decline-icon-container">
                  <svg class="w-10 h-10 text-red-600 decline-x" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </div>
                <div class="absolute inset-0 bg-red-400 rounded-full animate-ping opacity-20"></div>
              </div>
            </div>
            
            <!-- Message -->
            <div class="text-center mb-4">
              <h4 class="text-xl font-bold text-gray-800 mb-2">Confirm Decline</h4>
              <p class="text-gray-600 mb-3">You are about to decline this post submission</p>
              <div class="bg-red-50 border-2 border-red-200 rounded-xl p-3 mb-3">
                <p class="text-sm text-red-800 font-medium mb-1">Account Name</p>
                <p class="text-lg font-bold text-red-900" id="declineModalName">GTH Builders and Developers</p>
              </div>
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-2 flex items-start gap-3 mb-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-yellow-800 text-left">Declining will notify the user via email with your provided reason.</p>
              </div>
              <div class="text-left">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for Decline <span class="text-red-500">*</span></label>
                <textarea id="declineReason" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 focus:outline-none resize-none" placeholder="Please provide a brief, clear reason for declining this post..."></textarea>
                <p class="text-xs text-gray-500 mt-2">This reason will be included in the email notification.</p>
              </div>
            </div>
          </div>
          
          <!-- Footer Actions -->
          <div class="px-6 py-4 bg-gradient-to-b from-gray-50 to-gray-100 rounded-b-3xl flex justify-end gap-3">
            <button class="px-6 py-3 bg-white border-2 border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-all duration-200 hover:scale-105 close-modal">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
              </span>
            </button>
            <button class="px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold rounded-xl transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl" id="confirmDecline">
              <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span>Decline Post</span>
                <svg class="w-4 h-4 decline-loading hidden animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
            </button>
          </div>
        </div>
      </div>
    </main>


  <script src="{{ asset('js/admin/globalManagement/postingManagement.js') }}" defer></script>

</body>

</html>