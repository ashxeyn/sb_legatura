<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/userManagement/verificationRequest.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">

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
            <a href="{{ route('admin.userManagement.verificationRequest') }}" class="submenu-link active">Verification Request</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Verification Request</h1>

        <div class="flex items-center gap-6">
          <div class="relative w-64" style="width: 600px;">
            <input
              id="searchInput"
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
      <div class="p-8 space-y-6 max-w-7xl mx-auto">
        <!-- Filters Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
              <i class="fi fi-rr-filter text-gray-500"></i>
              <span>Filter By</span>
            </div>

            <!-- Date Range -->
            <div class="flex items-center gap-2">
              <label class="text-sm font-medium text-gray-700">From:</label>
              <input type="date" id="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
              <label class="text-sm font-medium text-gray-700">To:</label>
              <input type="date" id="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
            </div>
          </div>

          <button id="resetFilterBtn" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
            <i class="fi fi-rr-rotate-left"></i>
            <span>Reset Filter</span>
          </button>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="border-b border-gray-200">
            <div class="flex px-6">
              <button id="tabContractors" class="verification-tab active px-4 py-3 text-sm font-semibold border-b-2 border-orange-500 text-orange-600 transition-all">Contractors</button>
              <button id="tabOwners" class="verification-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-orange-600 hover:border-orange-300 transition-all">Property Owners</button>
            </div>
          </div>

          <!-- Contractors Table -->
          <div id="contractorsTableWrap" class="overflow-x-auto">
            @include('admin.userManagement.partials.vercontractorTable')
          </div>

          <!-- Property Owners Table -->
          <div id="ownersTableWrap" class="overflow-x-auto hidden">
            @include('admin.userManagement.partials.verownerTable')
          </div>
        </div>
      </div>

        <!-- Contractor Verification Modal -->
        <div id="contractorVerificationModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
          <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-panel">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
              <h2 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fi fi-sr-building text-orange-500"></i>
                <span>Verification Details</span>
              </h2>
              <button id="vrCloseBtn" class="text-gray-500 hover:text-gray-700 transition p-2 rounded-lg hover:bg-gray-100">
                <i class="fi fi-rr-cross text-2xl"></i>
              </button>
            </div>

            <!-- Body -->
            <div class="overflow-y-auto max-h-[calc(90vh-120px)] p-6 space-y-6">
              <!-- Top Grid: Profile & Owner Details -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Profile Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                      <span id="vrCompanyInitials">PC</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 id="vrCompanyName" class="text-lg font-bold text-gray-800 truncate">Panda Construction Company</h3>
                      <p id="vrCompanyContact" class="text-sm text-gray-600 truncate">pandaconstruction@domain.com • +63 934 567 8912</p>
                    </div>
                  </div>
                </div>

                <!-- Owner Details Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0">
                      <span id="vrOwnerInitials">OW</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 id="vrOwnerName" class="text-lg font-bold text-gray-800 truncate">Owner Name</h3>
                      <p class="text-xs text-gray-500 mb-2">Owner</p>
                      <div class="flex items-start gap-2">
                        <i class="fi fi-rr-marker text-blue-500 mt-0.5 flex-shrink-0"></i>
                        <p id="vrOwnerAddress" class="text-sm text-gray-600 line-clamp-2">Business Address</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Representative Card (Removed as per request) -->
                <!--
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-full bg-gradient-to-br from-rose-400 to-rose-600 text-white font-bold flex items-center justify-center flex-shrink-0">
                      <span id="vrRepInitials">OP</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 id="vrRepName" class="text-base font-bold text-gray-800">Olive Faith Padios</h3>
                      <p id="vrRepRole" class="text-xs text-gray-600 mb-2">Secretary/Contact person</p>
                      <ul class="space-y-1 text-sm">
                        <li class="flex items-center gap-2"><i class="fi fi-rr-phone-call text-orange-500"></i><span id="vrRepContact">+63 912 345 6789</span></li>
                        <li class="flex items-center gap-2"><i class="fi fi-rr-envelope text-orange-500"></i><span id="vrRepEmail">pcc_office@gmail.com</span></li>
                        <li class="flex items-center gap-2"><i class="fi fi-rr-phone-flip text-orange-500"></i><span id="vrRepTel">081 234 5678</span></li>
                      </ul>
                    </div>
                  </div>
                </div>
                -->
              </div>

              <!-- Bottom Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Information -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    Personal information
                  </h4>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">Contractor Type</label>
                      <div id="vrContractorType" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-800">General Contractor</div>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">Years of Operation</label>
                      <div id="vrYears" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-800">1971</div>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">Services offered</label>
                      <div id="vrServices" class="w-full px-3 py-3 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-700 leading-relaxed">
                        Project planning, construction management, material procurement, subcontractor coordination, renovations, and quality supervision for residential and commercial projects.
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Business Accreditation & Compliance -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <h4 class="text-sm font-bold text-orange-600 mb-3 flex items-center gap-2">
                    <i class="fi fi-rr-badge-check"></i>
                    Business Accreditation & Compliance
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">PCAB Number</label>
                      <div id="vrPcabNo" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-800">12345-AB-2025</div>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">Business Permit Expiration</label>
                      <div id="vrBpExp" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-800">December 31, 2025</div>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">PCAB Category</label>
                      <div id="vrPcabCategory" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-800">Category B</div>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">TIN Business Registration No.</label>
                      <div id="vrTin" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-800">123-456-789-000</div>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">PCAB Expiration Date</label>
                      <div id="vrPcabExp" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-800">August 15, 2026</div>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">PCAB Number</label>
                      <div id="vrPcabNo2" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-800">12345-AB-2025</div>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">Business Permit No.</label>
                      <div id="vrBpNo" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-800">BP-2025-0987</div>
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-600 mb-1">Business Permit City</label>
                      <div id="vrBpCity" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-800">Zamboanga City</div>
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-xs font-medium text-gray-600 mb-1">DTI / SEC Registration</label>
                      <a id="vrDtiFile" href="#" class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-sm font-medium transition">
                        <i class="fi fi-rr-file-pdf text-indigo-500"></i>
                        <span>DTIRegistration.pdf</span>
                        <span class="text-xs text-gray-500">200 KB</span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-end gap-3">
              <button id="vrRejectBtn" class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold">Reject</button>
              <button id="vrAcceptBtn" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-xl transition font-semibold shadow-md hover:shadow-lg">Accept</button>
            </div>
          </div>
        </div>

        <!-- Accept Confirmation Modal -->
        <div id="acceptConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[60] hidden items-center justify-center p-4">
          <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-200 scale-95 opacity-0 accept-panel">
            <div class="p-6 space-y-4">
              <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                <i class="fi fi-rr-check-circle text-2xl"></i>
              </div>
              <h3 class="text-xl font-bold text-gray-800">Verify Account?</h3>
              <p class="text-gray-600">The user can use and access their accounts now.</p>
            </div>
            <div class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-end gap-3">
              <button id="acceptCancelBtn" class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold">Cancel</button>
              <button id="acceptConfirmBtn" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-xl transition font-semibold shadow-md hover:shadow-lg">Confirm</button>
            </div>
          </div>
        </div>

        <!-- Reject Confirmation Modal -->
        <div id="rejectConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[60] hidden items-center justify-center p-4">
          <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all duration-200 scale-95 opacity-0 reject-panel">
            <div class="p-6 space-y-4">
              <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                <i class="fi fi-rr-cross-circle text-2xl"></i>
              </div>
              <h3 class="text-xl font-bold text-gray-800">Reject Verification</h3>
              <p class="text-gray-600">Please provide a brief reason why this verification is being rejected.</p>
              <div>
                <label for="rejectReasonInput" class="block text-sm font-semibold text-gray-800 mb-2">Reason for Rejection <span class="text-red-500">*</span></label>
                <textarea id="rejectReasonInput" rows="4" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition resize-none placeholder-gray-400" placeholder="e.g., Incomplete PCAB details or mismatched IDs."></textarea>
                <p id="rejectReasonError" class="mt-1 text-xs text-red-600 hidden">Reason is required.</p>
              </div>
            </div>
            <div class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-end gap-3">
              <button id="rejectCancelBtn" class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold">Cancel</button>
              <button id="rejectConfirmBtn" class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl transition font-semibold shadow-md hover:shadow-lg">Confirm</button>
            </div>
          </div>
        </div>

        <!-- Property Owner Verification Modal -->
        <div id="ownerVerificationModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
          <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 owner-modal-panel">
            <!-- Header -->
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
              <h2 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-3">
                <i class="fi fi-sr-user text-orange-500"></i>
                <span>Owner Verification</span>
              </h2>
              <button id="poCloseBtn" class="text-gray-500 hover:text-gray-700 transition p-2 rounded-lg hover:bg-gray-100">
                <i class="fi fi-rr-cross text-2xl"></i>
              </button>
            </div>

            <!-- Body -->
            <div class="overflow-y-auto max-h-[calc(90vh-120px)] p-6 space-y-6">
              <!-- Top: Profile & Account -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Profile Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-bold text-xl flex-shrink-0"><span id="poInitials">MM</span></div>
                    <div class="flex-1 min-w-0">
                      <h3 id="poFullName" class="text-lg font-bold text-gray-800 truncate">Mar Manon-og</h3>
                      <p id="poContactLine" class="text-sm text-gray-600 truncate">mar@example.com • 0999 123 4567</p>
                    </div>
                  </div>
                </div>
                <!-- Account Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    Account Information
                  </h4>
                  <div class="grid grid-cols-1 gap-3 text-sm">
                    <div>
                      <span class="block text-xs text-gray-500">Username</span>
                      <div id="poUsername" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">mar_owner</div>
                    </div>
                    <div>
                      <span class="block text-xs text-gray-500">Email</span>
                      <div id="poEmail" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">mar@example.com</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Bottom: Personal Info & Documents -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Information -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    Personal information
                  </h4>
                  <div class="space-y-3 text-sm">
                    <div>
                      <span class="block text-xs text-gray-500">Occupation</span>
                      <div id="poOccupation" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">Civil Engineer</div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                      <div>
                        <span class="block text-xs text-gray-500">Date of Birth</span>
                        <div id="poDob" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">1990-05-22</div>
                      </div>
                      <div>
                        <span class="block text-xs text-gray-500">Age</span>
                        <div id="poAge" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">35</div>
                      </div>
                    </div>
                    <div>
                      <span class="block text-xs text-gray-500">Address</span>
                      <div id="poAddress" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">Street, Barangay, City, Province, 7000</div>
                    </div>
                  </div>
                </div>

                <!-- Documents -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <h4 class="text-sm font-bold text-orange-600 mb-3 flex items-center gap-2">
                    <i class="fi fi-rr-folder"></i>
                    Verification Documents
                  </h4>
                  <div class="grid grid-cols-1 gap-4 text-sm">
                    <div class="grid grid-cols-1 gap-3">
                      <div>
                        <span class="block text-xs text-gray-500">Valid ID Type</span>
                        <div id="poValidIdType" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">Philippine Passport</div>
                      </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                      <div>
                        <span class="block text-xs text-gray-500 mb-1">Valid ID Photo (Front)</span>
                        <a id="poValidIdPhoto" href="#" class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg font-medium transition w-full">
                          <i class="fi fi-rr-id-badge text-indigo-500"></i>
                          <span class="truncate">Front.jpg</span>
                        </a>
                      </div>
                      <div>
                        <span class="block text-xs text-gray-500 mb-1">Valid ID Photo (Back)</span>
                        <a id="poValidIdBackPhoto" href="#" class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg font-medium transition w-full">
                          <i class="fi fi-rr-id-badge text-indigo-500"></i>
                          <span class="truncate">Back.jpg</span>
                        </a>
                      </div>
                    </div>
                    <div>
                      <span class="block text-xs text-gray-500 mb-1">Police Clearance</span>
                      <a id="poPoliceClearance" href="#" class="inline-flex items-center gap-2 px-3 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg font-medium transition">
                        <i class="fi fi-rr-file text-purple-500"></i>
                        <span>PoliceClearance.pdf</span>
                        <span class="text-xs text-gray-500">200 KB</span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-end gap-3">
              <button id="poRejectBtn" class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold">Reject</button>
              <button id="poAcceptBtn" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-xl transition font-semibold shadow-md hover:shadow-lg">Accept</button>
            </div>
          </div>
        </div>

        <!-- Delete Confirmation Modal (Shared) -->
        <div id="deleteConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[70] hidden items-center justify-center p-4">
          <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-200 scale-95 opacity-0 delete-panel">
            <div class="p-6 space-y-4 text-center">
              <div class="mx-auto w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                <i class="fi fi-rr-trash text-2xl"></i>
              </div>
              <h3 id="deleteTitle" class="text-2xl font-bold text-gray-900">Delete</h3>
              <p class="text-gray-600">Permanently delete <span id="deleteName" class="font-semibold text-gray-900">this item</span>?<br>This action cannot be undone.</p>
            </div>
            <div class="px-6 pb-6 grid grid-cols-1 gap-3">
              <button id="deleteConfirmBtn" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl transition font-semibold shadow-md hover:shadow-lg">
                <i class="fi fi-rr-trash"></i>
                Delete
              </button>
              <button id="deleteCancelBtn" class="w-full px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold">Cancel</button>
            </div>
          </div>
        </div>


    </main>


  <script src="{{ asset('js/admin/reusables/filters.js') }}" defer></script>
  <script src="{{ asset('js/admin/userManagement/verificationRequest.js') }}" defer></script>

</body>

</html>
