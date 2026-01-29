<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/userManagement/suspendedAccounts.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
  <script src="{{ asset('js/admin/reusables/filters.js') }}" defer></script>


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
            <a href="{{ route('admin.userManagement.suspendedAccounts') }}" class="submenu-link active">Suspended Accounts</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Suspended Accounts</h1>

        <div class="flex items-center gap-6">
          <div class="relative w-64" style="width: 600px;">
            <input
              type="text"
              id="searchInput"
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-4">
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
              <button id="saTabContractors" class="sa-tab active px-4 py-3 text-sm font-semibold border-b-2 border-orange-500 text-orange-600 transition-all">Contractors</button>
              <button id="saTabOwners" class="sa-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-orange-600 hover:border-orange-300 transition-all">Property Owners</button>
            </div>
          </div>

          <!-- Contractors Table -->
          <div id="contractorsTableWrap" class="overflow-x-auto">
            @include('admin.userManagement.partials.suspendedContractorsTable')
          </div>

          <!-- Property Owners Table -->
          <div id="ownersTableWrap" class="overflow-x-auto hidden">
            @include('admin.userManagement.partials.suspendedOwnersTable')
          </div>
        </div>
      </div>

      <!-- Contractor View Modal -->
      <div id="saContractorModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 sa-modal-panel">
          <!-- Header -->
          <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
            <div class="flex items-center gap-3 min-w-0">
              <i class="fi fi-sr-building text-orange-500 text-xl"></i>
              <h2 class="text-xl md:text-2xl font-bold text-gray-800 truncate" id="saCName">HorizonBuild Corporation</h2>
            </div>
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-full bg-red-50 text-red-700 border border-red-200">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                <span>Suspended</span>
              </span>
              <button id="saContractorClose" class="text-gray-500 hover:text-gray-700 transition p-2 rounded-lg hover:bg-gray-100">
                <i class="fi fi-rr-cross text-2xl"></i>
              </button>
            </div>
          </div>

          <!-- Body -->
          <div class="overflow-y-auto max-h-[calc(90vh-120px)] p-6 space-y-6">
            <!-- Top grid: Profile + Right column -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
              <!-- Left: Company Card -->
              <div class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                      <span id="saCInitials">HC</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-600 truncate"><span id="saCEmail">hb@corp.com</span>
                        <button class="ml-2 text-gray-400 hover:text-gray-600 align-middle" data-copy-target="#saCEmail" title="Copy email">
                          <i class="fi fi-rr-copy-alt"></i>
                        </button>
                      </p>
                      <p class="text-sm text-gray-600 truncate"><span id="saCPhone">+63 912 345 6789</span>
                        <button class="ml-2 text-gray-400 hover:text-gray-600 align-middle" data-copy-target="#saCPhone" title="Copy phone">
                          <i class="fi fi-rr-copy-alt"></i>
                        </button>
                      </p>
                    </div>
                  </div>
                  <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                    <div class="bg-gray-50 rounded-lg p-2">
                      <div class="text-xs text-gray-500">Experience</div>
                      <div id="saCExp" class="text-sm font-semibold text-gray-800">7 yrs</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2">
                      <div class="text-xs text-gray-500">Total</div>
                      <div id="saCTotal" class="text-sm font-semibold text-gray-800">7</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2">
                      <div class="text-xs text-gray-500">Finished</div>
                      <div id="saCFinished" class="text-sm font-semibold text-gray-800">2</div>
                    </div>
                  </div>
                  <button class="mt-3 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition shadow-sm">
                    <i class="fi fi-rr-paper-plane"></i>
                    Send Message
                  </button>
                </div>

                <!-- Team Members -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    Team Members
                  </h4>
                  <div class="flex items-start gap-3">
                    <img id="saCRepAvatar" class="w-10 h-10 rounded-full object-cover" src="https://i.pravatar.cc/80?img=17" alt="rep">
                    <div class="min-w-0">
                      <div id="saCRepName" class="font-semibold text-gray-900 truncate">Sofia Delgado</div>
                      <div id="saCRepRole" class="text-sm text-gray-600 truncate">Secretary / Contact person</div>
                      <div class="mt-1 text-xs text-gray-600">
                        <div>Contact: <span id="saCRepPhone">0928-555-1209</span></div>
                        <div>Email: <span id="saCRepEmail">sofia.ops@example.com</span></div>
                      </div>
                    </div>
                  </div>
                  <div class="mt-3 flex items-center gap-2">
                    <img class="w-8 h-8 rounded-full object-cover" src="https://i.pravatar.cc/80?img=30" alt="tm">
                    <img class="w-8 h-8 rounded-full object-cover" src="https://i.pravatar.cc/80?img=31" alt="tm">
                    <img class="w-8 h-8 rounded-full object-cover" src="https://i.pravatar.cc/80?img=32" alt="tm">
                    <div id="saCTeamMore" class="hidden items-center gap-2">
                      <img class="w-8 h-8 rounded-full object-cover" src="https://i.pravatar.cc/80?img=33" alt="tm">
                      <img class="w-8 h-8 rounded-full object-cover" src="https://i.pravatar.cc/80?img=34" alt="tm">
                    </div>
                    <button id="saCTeamToggle" class="ml-auto text-sm font-medium text-orange-600 hover:text-orange-700">Show All</button>
                  </div>
                </div>
              </div>

              <!-- Right: Details, Suspension, Documents -->
              <div class="lg:col-span-2 space-y-4">
                <!-- Suspension Details -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                      <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                      Suspension Details
                    </h4>
                    <span class="text-xs text-gray-500">Date: <span id="saCDate">18 Jul, 2025</span></span>
                  </div>
                  <div class="mt-3 text-sm">
                    <div class="text-xs text-gray-500">Reason</div>
                    <div id="saCReason" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">Multiple policy violations</div>
                  </div>
                </div>

                <!-- Company Details -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                    Company Details
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div><div class="text-xs text-gray-500">Registered Date</div><div id="saCRegDate" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">January 12, 2023</div></div>
                    <div><div class="text-xs text-gray-500">PCAB No.</div><div id="saCPCABNo" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">7100581</div></div>
                    <div><div class="text-xs text-gray-500">PCAB Category</div><div id="saCPCABCat" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">General Building</div></div>
                    <div><div class="text-xs text-gray-500">Business Permit No.</div><div id="saCBizNo" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">PAS-BP-0925-330</div></div>
                    <div><div class="text-xs text-gray-500">Business Permit Expiration</div><div id="saCBizExp" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">Dec 31, 2025</div></div>
                    <div><div class="text-xs text-gray-500">TIN</div><div id="saCTIN" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">009-224-119-007</div></div>
                  </div>
                </div>

                <!-- Documents -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                      <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                      Documents
                    </h4>
                    <span class="text-xs text-gray-500">Approved • Latest first</span>
                  </div>
                  <div id="saCDocs" class="space-y-2">
                    <!-- populated by JS -->
                  </div>
                </div>
              </div>
            </div>

            <!-- Account Profile (full width) -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
              <div class="relative h-36 md:h-44 w-full">
                <img id="saCBanner" class="absolute inset-0 w-full h-full object-cover" src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=1400&auto=format&fit=crop" alt="banner">
                <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-black/0"></div>
              </div>
              <div class="p-5 space-y-3">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                  <i class="fi fi-rr-marker"></i>
                  <span id="saCLocation">Zamboanga City</span>
                  <span class="mx-2 text-gray-300">•</span>
                  <i class="fi fi-rr-star text-amber-400"></i>
                  <span id="saCRating">2.4 Rating</span>
                </div>
                <p id="saCDescription" class="text-gray-700 text-sm leading-relaxed">The company focuses on innovative designs, project management, and turnkey solutions within Metro Manila.</p>
                <div id="saCSpecialties" class="flex flex-wrap gap-2"></div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-end gap-3">
            <button id="saContractorClose2" class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold">Close</button>
            <button id="saReactivateBtn" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition font-semibold inline-flex items-center gap-2">
              <i class="fi fi-rr-rotate-right"></i>
              Reactivate
            </button>
          </div>
        </div>
      </div>

      <!-- Reactivate Modal (options) -->
      <div id="saReactivateModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[60] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden transform transition-all duration-200 scale-95 opacity-0 sa-reactivate-panel">
          <div class="relative">
            <div class="h-32 bg-[url('https://images.unsplash.com/photo-1529429612779-c8e40ef2f36e?q=80&w=1400&auto=format&fit=crop')] bg-cover bg-center"></div>
            <div class="absolute -bottom-7 left-1/2 -translate-x-1/2 w-20 h-20 rounded-full ring-4 ring-white bg-white flex items-center justify-center shadow">
              <i class="fi fi-sr-building text-orange-500 text-2xl"></i>
            </div>
          </div>
          <div class="pt-10 px-6 pb-6 text-center space-y-3">
            <h3 id="saReactivateTitle" class="text-xl md:text-2xl font-bold text-gray-900">Reactivate</h3>
            <p class="text-sm text-gray-600">Would you like to keep <span id="saReactivateName" class="font-semibold text-gray-900">this account</span>'s existing credentials or edit details before reactivation?</p>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
              <button id="saReactivateKeep" class="inline-flex items-center justify-center gap-2 px-4 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold">
                <i class="fi fi-rr-shield-check"></i>
                Keep Same Credentials
              </button>
              <button id="saReactivateEdit" class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition font-semibold">
                <i class="fi fi-rr-edit"></i>
                Edit Before Reactivation
              </button>
            </div>
            <button id="saReactivateCancel" class="mt-4 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
          </div>
        </div>
      </div>

      <!-- Reactivated Success Modal -->
      <div id="saReactivateSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[70] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full overflow-hidden transform transition-all duration-200 scale-95 opacity-0 sa-reactivate-success-panel">
          <div class="p-6 md:p-8">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-12 rounded-full bg-emerald-50 ring-8 ring-emerald-100 text-emerald-600 flex items-center justify-center">
                <i class="fi fi-rr-check"></i>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-xl md:text-2xl font-bold text-gray-900">Account Reactivated!</h3>
                <p class="mt-1 text-gray-600">Account has been reinstated and access is now restored.</p>
              </div>
            </div>
            <div class="mt-6 flex justify-end">
              <button id="saReactivateSuccessConfirm" class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-semibold shadow-sm">
                <i class="fi fi-rr-check-circle"></i>
                Confirm
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Reactivate - Edit Contractor Modal -->
      <div id="saReactivateEditModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[65] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-200 scale-95 opacity-0 sa-reactivate-edit-panel">
          <!-- Header -->
          <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 flex items-center justify-between rounded-t-2xl z-10 shadow-lg">
            <h2 class="text-2xl font-bold text-white flex items-center gap-3">
              <i class="fi fi-rr-edit"></i>
              Edit Contractor Before Reactivation
            </h2>
            <button id="saReactivateEditClose" class="text-white hover:text-orange-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
              <i class="fi fi-rr-cross text-2xl"></i>
            </button>
          </div>
          <!-- Body -->
          <div class="overflow-y-auto overscroll-contain max-h-[calc(90vh-160px)] p-6 md:p-8 space-y-6">
            <!-- Tabs -->
            <div class="flex gap-3 p-3 bg-gray-50 rounded-xl border-2 border-gray-200">
              <button id="saRETabCompany" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg font-semibold shadow-md transition-all hover:shadow-lg flex items-center justify-center gap-2">
                <i class="fi fi-rr-building"></i>
                <span>Company Information</span>
              </button>
              <button id="saRETabRep" class="flex-1 px-4 py-2.5 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-semibold transition-all hover:bg-gray-50 hover:shadow-md flex items-center justify-center gap-2">
                <i class="fi fi-rr-user"></i>
                <span>Representative</span>
              </button>
            </div>

            <!-- Company form -->
            <section id="saRECompanySection" class="space-y-6">
              <div class="flex items-center gap-6 p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border-2 border-orange-200">
                <div class="relative group">
                  <div class="w-24 h-24 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-orange-200">
                    <img id="saRECompanyLogoPreview" class="w-full h-full object-cover hidden" alt="Company Logo">
                    <i id="saRECompanyLogoIcon" class="fi fi-sr-building text-white text-4xl"></i>
                  </div>
                  <label for="saRECompanyLogoUpload" class="absolute -bottom-1 -right-1 bg-orange-500 hover:bg-orange-600 text-white p-2.5 rounded-full cursor-pointer shadow-lg transition-all transform hover:scale-110 hover:rotate-12">
                    <i class="fi fi-rr-camera"></i>
                  </label>
                  <input type="file" id="saRECompanyLogoUpload" accept="image/*" class="hidden">
                </div>
                <div>
                  <h3 class="text-lg font-bold text-gray-800">Company Logo</h3>
                  <p class="text-sm text-gray-600 mt-1">Click the camera icon to update logo</p>
                </div>
              </div>


              <div>
                <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
                  <i class="fi fi-rr-building"></i>
                  Company Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Company Name</label>
                    <input id="saRECompanyName" type="text" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="HorizonBuild Corporation">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Years of Operation</label>
                    <input id="saREYears" type="number" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="7">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Contact Number</label>
                    <input id="saREContact" type="tel" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="+63 912 345 6789">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Website</label>
                    <input id="saREWebsite" type="url" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" placeholder="https://">
                  </div>
                </div>
              </div>
            </section>

            <!-- Representative form -->
            <section id="saRERepSection" class="space-y-6 hidden">
              <div class="flex items-center gap-6 p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border-2 border-blue-200">
                <div class="relative group">
                  <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-blue-200">
                    <img id="saRERepPhotoPreview" class="w-full h-full object-cover hidden" alt="Representative Photo">
                    <i id="saRERepPhotoIcon" class="fi fi-rr-user text-white text-3xl"></i>
                  </div>
                  <label for="saRERepPhotoUpload" class="absolute -bottom-1 -right-1 bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition-all transform hover:scale-110 hover:rotate-12">
                    <i class="fi fi-rr-camera text-sm"></i>
                  </label>
                  <input type="file" id="saRERepPhotoUpload" accept="image/*" class="hidden">
                </div>
                <div>
                  <h3 class="text-base font-bold text-gray-800">Representative Photo</h3>
                  <p class="text-sm text-gray-600 mt-1">Optional • JPG/PNG</p>
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">First Name</label>
                  <input id="saRERepFirst" type="text" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="Sofia">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Last Name</label>
                  <input id="saRERepLast" type="text" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="Delgado">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Email Address</label>
                  <input id="saRERepEmail" type="email" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="sofia.ops@example.com">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Contact Number</label>
                  <input id="saRERepPhone" type="tel" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="0928-555-1209">
                </div>
              </div>
            </section>
          </div>

          <!-- Footer -->
          <div class="sticky bottom-0 bg-white border-t-2 border-gray-200 px-6 py-4 flex items-center justify-end gap-3">
            <button id="saReactivateEditCancel" class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">Cancel</button>
            <button id="saReactivateEditSubmit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition font-semibold inline-flex items-center gap-2">
              <i class="fi fi-rr-rotate-right"></i>
              Reactivate
            </button>
          </div>
        </div>
      </div>

      <!-- Reactivate - Edit Property Owner Modal (moved to top level) -->
      <div id="saREOwnerModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[65] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-200 scale-95 opacity-0 sa-reactivate-owner-edit-panel">
          <!-- Header -->
          <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 flex items-center justify-between rounded-t-2xl z-10 shadow-lg">
            <h2 class="text-2xl font-bold text-white flex items-center gap-3">
              <i class="fi fi-rr-edit"></i>
              Edit Property Owner Before Reactivation
            </h2>
            <button id="saREOwnerClose" class="text-white hover:text-orange-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
              <i class="fi fi-rr-cross text-2xl"></i>
            </button>
          </div>
          <!-- Body -->
          <div class="overflow-y-auto overscroll-contain max-h-[calc(90vh-160px)] p-6 md:p-8 space-y-6">
            <!-- Profile Picture -->
            <div class="flex items-center gap-6 p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border-2 border-orange-200">
              <div class="relative group">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-orange-200">
                  <i class="fi fi-rr-user text-4xl text-white"></i>
                </div>
                <label class="absolute -bottom-1 -right-1 bg-orange-500 hover:bg-orange-600 text-white p-2.5 rounded-full cursor-pointer shadow-lg transition-all transform hover:scale-110 hover:rotate-12">
                  <i class="fi fi-rr-camera"></i>
                  <input type="file" accept="image/*" class="hidden">
                </label>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-800">Profile Picture</h3>
                <p class="text-sm text-gray-600 mt-1">Click the camera icon to update photo</p>
                <p class="text-xs text-orange-600 mt-1 font-medium">• JPG, PNG • Max 5MB</p>
              </div>
            </div>

            <!-- Personal Information -->
            <section>
              <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
                <i class="fi fi-rr-user"></i>
                Personal Information
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">First Name</label>
                  <input id="saREOwnerFirst" type="text" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="Jerome">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Middle Name <span class="text-gray-400 font-normal normal-case">(optional)</span></label>
                  <input type="text" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" placeholder="">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Last Name</label>
                  <input type="text" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="Castillo">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Date of Birth</label>
                  <input type="date" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="1991-08-20">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Contact Number</label>
                  <input type="tel" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="0999 123 4567">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Occupation</label>
                  <input type="text" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" placeholder="">
                </div>
              </div>
            </section>

            <!-- Account Information -->
            <section>
              <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
                <i class="fi fi-rr-at"></i>
                Account Information
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Email Address</label>
                  <input type="email" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="jerome@example.com">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Username</label>
                  <input type="text" class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition" value="jeromeCastillo">
                </div>
              </div>
            </section>
          </div>

          <!-- Footer -->
          <div class="sticky bottom-0 bg-white border-t-2 border-gray-200 px-6 py-4 flex items-center justify-end gap-3">
            <button id="saREOwnerCancel" class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-semibold">Cancel</button>
            <button id="saREOwnerSubmit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition font-semibold inline-flex items-center gap-2">
              <i class="fi fi-rr-rotate-right"></i>
              Reactivate
            </button>
          </div>
        </div>
      </div>

      <!-- Owner View Modal -->
      <div id="saOwnerModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 sa-owner-panel">
          <!-- Header -->
          <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
            <div class="flex items-center gap-3 min-w-0">
              <i class="fi fi-sr-user text-orange-500 text-xl"></i>
              <h2 class="text-xl md:text-2xl font-bold text-gray-800 truncate" id="saOName">Suspended Owner</h2>
            </div>
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-full bg-red-50 text-red-700 border border-red-200">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                <span>Suspended</span>
              </span>
              <button id="saOwnerClose" class="text-gray-500 hover:text-gray-700 transition p-2 rounded-lg hover:bg-gray-100">
                <i class="fi fi-rr-cross text-2xl"></i>
              </button>
            </div>
          </div>

          <!-- Body -->
          <div class="overflow-y-auto max-h-[calc(90vh-120px)] p-6 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
              <!-- Left: Profile Card -->
              <div class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <div class="flex items-start gap-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                      <span id="saOInitials">JC</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-600 truncate"><span id="saOEmail">jerome@example.com</span>
                        <button class="ml-2 text-gray-400 hover:text-gray-600 align-middle" data-copy-target="#saOEmail" title="Copy email">
                          <i class="fi fi-rr-copy-alt"></i>
                        </button>
                      </p>
                      <p class="text-sm text-gray-600 truncate"><span id="saOPhone">0999 123 4567</span>
                        <button class="ml-2 text-gray-400 hover:text-gray-600 align-middle" data-copy-target="#saOPhone" title="Copy phone">
                          <i class="fi fi-rr-copy-alt"></i>
                        </button>
                      </p>
                    </div>
                  </div>
                  <button class="mt-3 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl transition shadow-sm">
                    <i class="fi fi-rr-paper-plane"></i>
                    Send Message
                  </button>
                </div>
              </div>

              <!-- Right: Details + Documents -->
              <div class="lg:col-span-2 space-y-4">
                <!-- Suspension Details -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                      <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                      Suspension Details
                    </h4>
                    <span class="text-xs text-gray-500">Date: <span id="saODate">12 Jan, 2025</span></span>
                  </div>
                  <div class="mt-3 text-sm">
                    <div class="text-xs text-gray-500">Reason</div>
                    <div id="saOReason" class="px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">Policy violation</div>
                  </div>
                </div>

                <!-- Documents -->
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                  <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                      <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                      Documents
                    </h4>
                    <span class="text-xs text-gray-500">Approved • Latest first</span>
                  </div>
                  <div id="saODocs" class="space-y-2"></div>
                </div>
              </div>
            </div>

            <!-- Account Profile (full width) -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
              <div class="relative h-36 md:h-44 w-full">
                <img id="saOBanner" class="absolute inset-0 w-full h-full object-cover" src="https://images.unsplash.com/photo-1496307653780-42ee777d4833?q=80&w=1400&auto=format&fit=crop" alt="banner">
                <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-black/0"></div>
              </div>
              <div class="p-5 space-y-3">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                  <i class="fi fi-rr-marker"></i>
                  <span id="saOLocation">Quezon City</span>
                  <span class="mx-2 text-gray-300">•</span>
                  <i class="fi fi-rr-star text-amber-400"></i>
                  <span id="saORating">4.2 Rating</span>
                </div>
                <p id="saODescription" class="text-gray-700 text-sm leading-relaxed">Homeowner and property investor focusing on residential developments and renovations.</p>
                <div id="saOSpecialties" class="flex flex-wrap gap-2"></div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-end gap-3">
            <button id="saOwnerClose2" class="px-6 py-2.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold">Close</button>
            <button id="saOwnerReactivateBtn" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition font-semibold inline-flex items-center gap-2">
              <i class="fi fi-rr-rotate-right"></i>
              Reactivate
            </button>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal (Shared) -->
      <div id="saDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[70] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-200 scale-95 opacity-0 sa-delete-panel">
          <div class="p-6 space-y-4 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
              <i class="fi fi-rr-trash text-2xl"></i>
            </div>
            <h3 id="saDeleteTitle" class="text-2xl font-bold text-gray-900">Delete</h3>
            <p class="text-gray-600">Permanently delete <span id="saDeleteName" class="font-semibold text-gray-900">this item</span>?<br>This action cannot be undone.</p>
          </div>
          <div class="px-6 pb-6 grid grid-cols-1 gap-3">
            <button id="saDeleteConfirm" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl transition font-semibold shadow-md hover:shadow-lg">
              <i class="fi fi-rr-trash"></i>
              Delete
            </button>
            <button id="saDeleteCancel" class="w-full px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold">Cancel</button>
          </div>
        </div>
      </div>

  <!-- Reactivate Suspended Account Modal -->
  <div id="reactivateSuspendedAccountModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 hidden items-center justify-center p-4 animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0 modal-content overflow-hidden">
      <!-- Icon Section with Gradient Background -->
      <div class="flex justify-center pt-12 pb-8 bg-gradient-to-b from-green-50 to-white relative">
        <div class="w-32 h-32 bg-gradient-to-br from-green-100 to-green-50 rounded-full flex items-center justify-center relative animate-pulse-slow">
          <div class="absolute inset-0 bg-green-200 rounded-full animate-ping opacity-30"></div>
          <div class="relative w-24 h-24 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center shadow-xl transform hover:scale-110 transition-transform duration-300">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
              <i class="fi fi-rr-user-check text-white text-4xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Section -->
      <div class="px-8 pb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">Reactivate User</h2>
        <p class="text-gray-600 leading-relaxed text-base mb-2">
          Are you sure you want to reactivate
        </p>
        <p class="text-xl font-bold text-gray-900 mb-1" id="reactivateSuspendedAccountName">User Name</p>
        <p class="text-sm text-gray-500">They will regain access to their account.</p>
      </div>

      <!-- Action Buttons -->
      <div class="px-8 pb-8 space-y-3">
        <button id="confirmReactivateSuspendedAccountBtn" class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-2xl transition-all font-bold shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 text-base">
          <i class="fi fi-rr-user-check"></i>
          Reactivate
        </button>
        <button id="cancelReactivateSuspendedAccountBtn" class="w-full px-6 py-4 bg-white border-2 border-gray-200 text-gray-700 rounded-2xl hover:bg-gray-50 transition-all font-semibold hover:border-gray-300 hover:shadow-md transform hover:scale-[1.02] active:scale-95 text-base">
          Cancel
        </button>
      </div>
    </div>
  </div>

    </main>


  <script src="{{ asset('js/admin/userManagement/suspendedAccounts.js') }}" defer></script>

</body>

</html>
