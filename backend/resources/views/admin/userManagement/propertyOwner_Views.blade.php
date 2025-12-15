<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/userManagement/propertyOwner_Views.css') }}">

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
            <a href="{{ route('admin.userManagement.propertyOwner') }}" class="submenu-link active">Property Owner</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Property Owners</h1>

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

      <!-- PAGE CONTENT -->
      <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-6">
        <!-- Top row: Back + Actions -->
        <div class="flex items-center justify-between">
          <a href="{{ route('admin.userManagement.propertyOwner') }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium hover:bg-indigo-50 px-4 py-2 rounded-lg transition-all duration-300 hover:shadow-md hover:-translate-x-1">
            <i class="fi fi-rr-angle-left text-lg"></i>
            <span>Back</span>
          </a>
          <div class="flex items-center gap-3">
            <button id="editPropertyOwnerBtn" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-all duration-300 text-gray-700 flex items-center gap-2 hover:shadow-md hover:scale-105">
              <i class="fi fi-rr-edit"></i>
              <span>Edit</span>
            </button>
            <button id="suspendPropertyOwnerBtn" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all duration-300 flex items-center gap-2 hover:shadow-lg hover:scale-105">
              <i class="fi fi-rr-ban"></i>
              <span>Suspend</span>
            </button>
          </div>
        </div>

        <!-- Info grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Left 2/3 -->
          <div class="xl:col-span-2 space-y-6">
            <!-- Profile card -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 md:px-8 py-6">
                <h2 class="text-xl md:text-2xl font-bold text-white">Property Owner Details</h2>
              </div>
              <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-start gap-6">
                  <div class="flex-shrink-0">
                    <div class="w-28 h-28 md:w-32 md:h-32 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white font-bold text-3xl md:text-4xl grid place-items-center shadow-xl ring-4 ring-blue-100 hover:ring-blue-200 hover:scale-110 transition-all duration-300 cursor-pointer">ES</div>
                  </div>
                  <div class="flex-1 w-full">
                    <div class="flex items-start justify-between gap-4">
                      <div>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-800">Emmanuelle Santos</h3>
                        <p class="text-gray-600 mt-1">Occupation: Civil Engineer</p>
                        <div class="flex items-center gap-2 text-sm mt-2">
                          <i class="fi fi-rr-star text-yellow-500"></i>
                          <span class="font-semibold text-gray-800">4.7 Rating</span>
                          <span class="text-gray-500">• Zamboanga City</span>
                        </div>
                      </div>
                      <button class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 md:px-5 py-2.5 rounded-lg transition-all duration-300 shadow-md hover:shadow-xl hover:scale-105 active:scale-95">
                        <i class="fi fi-rr-comment-alt"></i>
                        <span>Send Message</span>
                      </button>
                    </div>

                    <!-- Quick details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                      <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-lg hover:bg-gradient-to-br hover:from-indigo-50 hover:to-indigo-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                        <div class="w-11 h-11 bg-indigo-100 rounded-full grid place-items-center hover:scale-110 transition-transform">
                          <i class="fi fi-rr-calendar text-indigo-600"></i>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Registered Date</p>
                          <p class="font-semibold text-gray-800">January 12, 2023</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-lg hover:bg-gradient-to-br hover:from-green-50 hover:to-green-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                        <div class="w-11 h-11 bg-green-100 rounded-full grid place-items-center hover:scale-110 transition-transform">
                          <i class="fi fi-rr-cake-birthday text-green-600"></i>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Age</p>
                          <p class="font-semibold text-gray-800">36</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-lg hover:bg-gradient-to-br hover:from-blue-50 hover:to-blue-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                        <div class="w-11 h-11 bg-blue-100 rounded-full grid place-items-center hover:scale-110 transition-transform">
                          <i class="fi fi-rr-phone-call text-blue-600"></i>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Contact No.</p>
                          <p class="font-semibold text-gray-800">0988 765 4321</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-lg hover:bg-gradient-to-br hover:from-purple-50 hover:to-purple-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                        <div class="w-11 h-11 bg-purple-100 rounded-full grid place-items-center hover:scale-110 transition-transform">
                          <i class="fi fi-rr-envelope text-purple-600"></i>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Email</p>
                          <p class="font-semibold text-gray-800">criscel@gmail.com</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Account Profile section (cover) -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="relative h-44 md:h-56 bg-gradient-to-r from-slate-100 to-slate-200 overflow-hidden group">
                <img src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=1200&auto=format&fit=crop" alt="Cover" class="w-full h-full object-cover opacity-90 group-hover:scale-110 transition-transform duration-500" loading="lazy">
                <div class="absolute -bottom-10 left-6">
                  <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-white grid place-items-center shadow-xl ring-4 ring-white overflow-hidden hover:ring-8 hover:ring-indigo-100 hover:scale-110 transition-all duration-300 cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1544006659-f0b21884ce1d?q=80&w=400&auto=format&fit=crop" alt="Avatar" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                  </div>
                </div>
              </div>
              <div class="pt-14 px-6 md:px-8 pb-8">
                <h3 class="text-2xl font-bold text-gray-800">Emmanuelle Santos</h3>
                <div class="flex items-center gap-2 text-sm mt-1">
                  <i class="fi fi-rr-star text-yellow-500"></i>
                  <span class="font-semibold">4.7 Rating</span>
                  <span class="text-gray-500">• Zamboanga City</span>
                </div>
                <p class="text-gray-700 mt-4 leading-relaxed">
                  Dedicated property owner committed to building sustainable, modern, and cost-efficient spaces. Open to bids from trusted professionals in construction and design.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                  <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 flex items-center gap-3 hover:bg-orange-100 hover:shadow-md hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-orange-500 grid place-items-center text-white hover:scale-110 transition-transform">
                      <i class="fi fi-rr-phone-call"></i>
                    </div>
                    <div>
                      <p class="text-xs text-orange-600">Contact No.</p>
                      <p class="font-bold">0988 765 4321</p>
                    </div>
                  </div>
                  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center gap-3 hover:bg-blue-100 hover:shadow-md hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-blue-500 grid place-items-center text-white hover:scale-110 transition-transform">
                      <i class="fi fi-rr-envelope"></i>
                    </div>
                    <div>
                      <p class="text-xs text-blue-600">Email</p>
                      <p class="font-bold text-sm truncate">Santos@gmail.com</p>
                    </div>
                  </div>
                  <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3 hover:bg-green-100 hover:shadow-md hover:-translate-y-1 transition-all duration-300 cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-green-500 grid place-items-center text-white hover:scale-110 transition-transform">
                      <i class="fi fi-rr-briefcase"></i>
                    </div>
                    <div>
                      <p class="text-xs text-green-600">Occupation</p>
                      <p class="font-bold">Civil Engineer</p>
                    </div>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-6">
                  <div class="text-center p-6 bg-indigo-50 rounded-lg border border-indigo-200 hover:bg-indigo-100 hover:shadow-lg hover:-translate-y-2 transition-all duration-300 cursor-pointer group">
                    <p class="text-3xl md:text-4xl font-bold text-indigo-600 group-hover:scale-110 transition-transform">48</p>
                    <p class="text-sm text-gray-600 mt-2">Projects done</p>
                  </div>
                  <div class="text-center p-6 bg-green-50 rounded-lg border border-green-200 hover:bg-green-100 hover:shadow-lg hover:-translate-y-2 transition-all duration-300 cursor-pointer group">
                    <p class="text-3xl md:text-4xl font-bold text-green-600 group-hover:scale-110 transition-transform">5</p>
                    <p class="text-sm text-gray-600 mt-2">Ongoing projects</p>
                  </div>
                </div>
              </div>
            </section>

            <!-- List of Projects Section -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 md:px-8 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fi fi-rr-list text-indigo-600"></i>
                    List of Projects
                  </h2>
                  <select class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none hover:border-indigo-400 transition cursor-pointer">
                    <option value="all">All</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                  </select>
                </div>
              </div>
              <div class="p-6 space-y-4">
                <!-- Project 1 -->
                <div class="border border-gray-200 rounded-xl overflow-hidden hover:border-indigo-300 hover:shadow-lg transition-all duration-300 group">
                  <div class="flex flex-col sm:flex-row gap-4">
                    <div class="sm:w-32 h-32 sm:h-auto overflow-hidden flex-shrink-0">
                      <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=400&auto=format&fit=crop" alt="Project" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="flex-1 p-4 space-y-2">
                      <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">Residential House Construction</h3>
                        <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold whitespace-nowrap">100% Complete</span>
                      </div>
                      <p class="text-sm text-gray-600 line-clamp-2">A complete residential building project featuring modern architecture, quality materials, and sustainable design for family living.</p>
                      <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 pt-1">
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-marker text-indigo-600"></i>
                          Zamboanga City
                        </span>
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-calendar text-green-600"></i>
                          August 2024
                        </span>
                      </div>
                      <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                          <div class="w-8 h-8 rounded-full bg-orange-500 text-white text-xs font-bold grid place-items-center">JL</div>
                          <span class="text-sm font-medium text-gray-700">JL Homes</span>
                        </div>
                        <button class="px-4 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white text-sm rounded-lg transition-all hover:shadow-md hover:scale-105 active:scale-95">
                          View
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Project 2 -->
                <div class="border border-gray-200 rounded-xl overflow-hidden hover:border-orange-300 hover:shadow-lg transition-all duration-300 group">
                  <div class="flex flex-col sm:flex-row gap-4">
                    <div class="sm:w-32 h-32 sm:h-auto overflow-hidden flex-shrink-0">
                      <img src="https://images.unsplash.com/photo-1582268611958-ebfd161ef9cf?q=80&w=400&auto=format&fit=crop" alt="Project" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="flex-1 p-4 space-y-2">
                      <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-gray-800 group-hover:text-orange-600 transition-colors">Retail Store Fit-Out and Renovation</h3>
                        <span class="px-2.5 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-semibold whitespace-nowrap">50% Complete</span>
                      </div>
                      <p class="text-sm text-gray-600 line-clamp-2">Complete interior fit-out for a retail space including flooring, electrical, HVAC, and custom fixtures for an upscale shopping experience.</p>
                      <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 pt-1">
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-marker text-indigo-600"></i>
                          Manila City
                        </span>
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-calendar text-orange-600"></i>
                          In Progress
                        </span>
                      </div>
                      <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-orange-500 h-2 rounded-full transition-all duration-500" style="width: 50%"></div>
                      </div>
                      <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                          <div class="w-8 h-8 rounded-full bg-blue-500 text-white text-xs font-bold grid place-items-center">AB</div>
                          <span class="text-sm font-medium text-gray-700">ABC Builders</span>
                        </div>
                        <button class="px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded-lg transition-all hover:shadow-md hover:scale-105 active:scale-95">
                          View
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Project 3 -->
                <div class="border border-gray-200 rounded-xl overflow-hidden hover:border-blue-300 hover:shadow-lg transition-all duration-300 group">
                  <div class="flex flex-col sm:flex-row gap-4">
                    <div class="sm:w-32 h-32 sm:h-auto overflow-hidden flex-shrink-0">
                      <img src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?q=80&w=400&auto=format&fit=crop" alt="Project" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="flex-1 p-4 space-y-2">
                      <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-gray-800 group-hover:text-blue-600 transition-colors">Residential Swimming Pool & Landscaping</h3>
                        <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold whitespace-nowrap">25% Complete</span>
                      </div>
                      <p class="text-sm text-gray-600 line-clamp-2">Luxury swimming pool installation with complete landscaping, outdoor lighting, and premium finishing materials for a modern residential property.</p>
                      <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 pt-1">
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-marker text-indigo-600"></i>
                          Cebu City
                        </span>
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-calendar text-blue-600"></i>
                          April 2025
                        </span>
                      </div>
                      <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" style="width: 25%"></div>
                      </div>
                      <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                          <div class="w-8 h-8 rounded-full bg-green-500 text-white text-xs font-bold grid place-items-center">PL</div>
                          <span class="text-sm font-medium text-gray-700">Prime Landscape</span>
                        </div>
                        <button class="px-4 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg transition-all hover:shadow-md hover:scale-105 active:scale-95">
                          View
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Project 4 -->
                <div class="border border-gray-200 rounded-xl overflow-hidden hover:border-purple-300 hover:shadow-lg transition-all duration-300 group">
                  <div class="flex flex-col sm:flex-row gap-4">
                    <div class="sm:w-32 h-32 sm:h-auto overflow-hidden flex-shrink-0">
                      <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=400&auto=format&fit=crop" alt="Project" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="flex-1 p-4 space-y-2">
                      <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-gray-800 group-hover:text-purple-600 transition-colors">Commercial Office Renovation</h3>
                        <span class="px-2.5 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold whitespace-nowrap">Pending</span>
                      </div>
                      <p class="text-sm text-gray-600 line-clamp-2">Full office renovation including partitioning, electrical upgrades, modern lighting fixtures, and contemporary interior design for a professional workspace.</p>
                      <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 pt-1">
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-marker text-indigo-600"></i>
                          Davao City
                        </span>
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-calendar text-yellow-600"></i>
                          Jan 2025
                        </span>
                      </div>
                      <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                          <div class="w-8 h-8 rounded-full bg-purple-500 text-white text-xs font-bold grid place-items-center">MC</div>
                          <span class="text-sm font-medium text-gray-700">Modern Co.</span>
                        </div>
                        <button class="px-4 py-1.5 bg-purple-500 hover:bg-purple-600 text-white text-sm rounded-lg transition-all hover:shadow-md hover:scale-105 active:scale-95">
                          View
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>

          <!-- Right 1/3: Company + Documents -->
          <div class="space-y-6">
            <!-- Company -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                  <i class="fi fi-rr-building text-indigo-600"></i>
                  Company
                </h3>
              </div>
              <div class="p-6">
                <div class="space-y-3">
                  <div class="overflow-hidden rounded-lg shadow group">
                    <img src="https://images.unsplash.com/photo-1496307653780-42ee777d4833?q=80&w=800&auto=format&fit=crop" alt="Company" class="w-full h-36 object-cover group-hover:scale-110 transition-transform duration-500 cursor-pointer" loading="lazy">
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-800">J Lois Construction</h4>
                    <p class="text-sm text-gray-600">Position: Site Engineer</p>
                    <span class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-orange-100 text-orange-700 rounded-full text-sm">
                      <i class="fi fi-rr-briefcase"></i>
                      General Contractor
                    </span>
                  </div>
                </div>
              </div>
            </section>

            <!-- Documents -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm sticky top-24 hover:shadow-xl transition-all duration-300">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                  <i class="fi fi-rr-folder text-indigo-600"></i>
                  Documents
                </h3>
              </div>
              <div class="p-6 space-y-4">
                <!-- Police clearance -->
                <div class="p-4 bg-gray-50 rounded-lg hover:bg-purple-50 transition-all duration-300 hover:shadow-md hover:-translate-y-1 group cursor-pointer">
                  <div class="flex items-start gap-3 mb-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg grid place-items-center group-hover:scale-110 group-hover:rotate-6 transition-all">
                      <i class="fi fi-rr-file text-purple-600 text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="font-semibold text-gray-800 group-hover:text-purple-700 transition-colors">Police Clearance</p>
                      <p class="text-sm text-gray-500">PRC.jpg <span class="text-xs text-gray-400">• 200 KB</span></p>
                    </div>
                    <button class="p-2 hover:bg-purple-200 rounded-lg transition-all hover:scale-110" aria-label="Download Police Clearance">
                      <i class="fi fi-rr-download text-gray-600 hover:text-purple-600"></i>
                    </button>
                  </div>
                  <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium inline-flex items-center gap-1 group-hover:bg-green-200 transition-colors">
                    <i class="fi fi-rr-check-circle"></i> Approved
                  </span>
                </div>

                <!-- Valid ID -->
                <div class="p-4 bg-gray-50 rounded-lg hover:bg-blue-50 transition-all duration-300 hover:shadow-md hover:-translate-y-1 group cursor-pointer">
                  <div class="flex items-start gap-3 mb-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg grid place-items-center group-hover:scale-110 group-hover:rotate-6 transition-all">
                      <i class="fi fi-rr-id-badge text-blue-600 text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="font-semibold text-gray-800 group-hover:text-blue-700 transition-colors">Valid ID</p>
                      <p class="text-sm text-gray-500">National_ID.jpg <span class="text-xs text-gray-400">• 350 KB</span></p>
                    </div>
                    <button class="p-2 hover:bg-blue-200 rounded-lg transition-all hover:scale-110" aria-label="Download Valid ID">
                      <i class="fi fi-rr-download text-gray-600 hover:text-blue-600"></i>
                    </button>
                  </div>
                  <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium inline-flex items-center gap-1 group-hover:bg-green-200 transition-colors">
                    <i class="fi fi-rr-check-circle"></i> Approved
                  </span>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>

    </main>
  </div>

  <!-- Suspend Account Modal -->
  <div id="suspendAccountModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[85vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-exclamation text-white text-xl"></i>
          </div>
          <h2 class="text-xl font-bold text-white">Suspend Account</h2>
        </div>
        <button id="closeSuspendModalBtn" class="text-white hover:text-red-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-xl"></i>
        </button>
      </div>

      <!-- Modal Body - Scrollable -->
      <div class="overflow-y-auto max-h-[calc(85vh-140px)] p-6 space-y-5">
        <!-- Warning Message -->
        <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 space-y-3">
          <div class="flex items-start gap-3">
            <div class="w-9 h-9 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 animate-pulse">
              <i class="fi fi-rr-shield-exclamation text-white"></i>
            </div>
            <div class="flex-1">
              <h3 class="font-bold text-gray-800 mb-1">Confirm Account Suspension</h3>
              <p class="text-gray-700 text-sm leading-relaxed">
                Are you sure you want to suspend <span class="font-bold text-red-600">Emmanuelle Santos</span>?
              </p>
            </div>
          </div>

          <!-- User Info Card -->
          <div class="bg-white rounded-lg p-3 border border-red-200 space-y-2">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white font-bold flex items-center justify-center shadow-md">
                ES
              </div>
              <div>
                <p class="font-semibold text-gray-800 text-sm">Emmanuelle Santos</p>
                <p class="text-xs text-gray-600">Civil Engineer</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200">
              <div class="text-center">
                <p class="text-xl font-bold text-indigo-600">48</p>
                <p class="text-xs text-gray-600">Projects Done</p>
              </div>
              <div class="text-center">
                <p class="text-xl font-bold text-green-600">5</p>
                <p class="text-xs text-gray-600">Ongoing Projects</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Reason Input -->
        <div>
          <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
            <i class="fi fi-rr-edit text-red-500"></i>
            Reason for Suspension <span class="text-red-500">*</span>
          </label>
          <textarea
            id="suspendReason"
            rows="3"
            placeholder="Please provide a detailed reason for suspending this account..."
            class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition-all hover:border-red-300 bg-white resize-none text-sm"
          ></textarea>
          <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
            <i class="fi fi-rr-info"></i>
            This reason will be recorded and may be shared with the user.
          </p>
        </div>

        <!-- Suspension Options -->
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
            <i class="fi fi-rr-calendar text-red-500"></i>
            Suspension Duration
          </label>
          <div class="grid grid-cols-2 gap-2">
            <label class="relative cursor-pointer group">
              <input type="radio" name="suspensionDuration" value="temporary" class="peer sr-only" checked>
              <div class="border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300 hover:shadow-md">
                <i class="fi fi-rr-clock text-xl text-gray-400 peer-checked:text-red-500 transition-colors mb-1"></i>
                <p class="font-semibold text-gray-700 text-sm peer-checked:text-red-600">Temporary</p>
                <p class="text-xs text-gray-500 mt-1">30 days</p>
              </div>
            </label>
            <label class="relative cursor-pointer group">
              <input type="radio" name="suspensionDuration" value="permanent" class="peer sr-only">
              <div class="border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300 hover:shadow-md">
                <i class="fi fi-rr-ban text-xl text-gray-400 peer-checked:text-red-500 transition-colors mb-1"></i>
                <p class="font-semibold text-gray-700 text-sm peer-checked:text-red-600">Permanent</p>
                <p class="text-xs text-gray-500 mt-1">Indefinite</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Consequences Warning -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded-r-lg">
          <div class="flex gap-2">
            <i class="fi fi-rr-triangle-warning text-yellow-600 flex-shrink-0 mt-0.5"></i>
            <div class="text-sm text-gray-700 space-y-1">
              <p class="font-semibold text-gray-800 text-xs">Suspension Consequences:</p>
              <ul class="list-disc list-inside space-y-0.5 text-xs">
                <li>User will be logged out immediately</li>
                <li>All ongoing projects will be paused</li>
                <li>Account access will be restricted</li>
                <li>Email notification will be sent to user</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons - Fixed at Bottom -->
      <div class="sticky bottom-0 bg-white border-t-2 border-gray-200 px-6 py-4 rounded-b-2xl flex items-center justify-end gap-3">
        <button id="cancelSuspendBtn" class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95 text-sm">
          Cancel
        </button>
        <button id="confirmSuspendBtn" class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2 text-sm">
          <i class="fi fi-rr-shield-check"></i>
          Suspend Account
        </button>
      </div>
    </div>
  </div>

  <!-- Edit Property Owner Modal -->
  <div id="editPropertyOwnerModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 flex items-center justify-between rounded-t-2xl z-10 shadow-lg">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
          <i class="fi fi-rr-edit"></i>
          Edit Property Owner
        </h2>
        <button id="closeEditModalBtn" class="text-white hover:text-orange-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(90vh-80px)] p-6 md:p-8 space-y-6">
        <!-- Profile Picture Section -->
        <div class="flex items-center gap-6 p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border-2 border-orange-200 hover:shadow-lg transition-all duration-300">
          <div class="relative group">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-orange-200 hover:ring-orange-300 transition-all duration-300">
              <i class="fi fi-rr-user text-4xl text-white" id="editProfileIcon"></i>
              <img id="editProfilePreview" src="https://images.unsplash.com/photo-1544006659-f0b21884ce1d?q=80&w=400&auto=format&fit=crop" class="w-full h-full object-cover" alt="Profile Preview">
            </div>
            <label for="editProfileUpload" class="absolute -bottom-1 -right-1 bg-orange-500 hover:bg-orange-600 text-white p-2.5 rounded-full cursor-pointer shadow-lg transition-all transform hover:scale-110 hover:rotate-12">
              <i class="fi fi-rr-camera text-sm"></i>
              <input type="file" id="editProfileUpload" class="hidden" accept="image/*">
            </label>
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-800">Profile Picture</h3>
            <p class="text-sm text-gray-600 mt-1">Click the camera icon to update photo</p>
            <p class="text-xs text-orange-600 mt-1 font-medium">• JPG, PNG • Max 5MB</p>
          </div>
        </div>

        <!-- Personal Information Section -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-user"></i>
            Personal Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
              <input type="text" value="Emmanuelle" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Middle Name <span class="text-gray-400 font-normal">(optional)</span></label>
              <input type="text" value="Delgado" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
              <input type="text" value="Santos" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Date of Birth</label>
              <div class="relative">
                <input type="date" value="1989-02-16" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
                <i class="fi fi-rr-calendar absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
              </div>
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number</label>
              <div class="relative">
                <i class="fi fi-rr-phone-call absolute left-4 top-1/2 -translate-y-1/2 text-orange-500"></i>
                <input type="tel" value="0998 765 4321" class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
              </div>
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Occupation</label>
              <input type="text" value="Civil Engineer" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
            </div>
          </div>
        </div>

        <!-- Account Information Section -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-at"></i>
            Account Information
          </h3>
          <div class="grid grid-cols-1 gap-4">
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
              <div class="relative">
                <i class="fi fi-rr-envelope absolute left-4 top-1/2 -translate-y-1/2 text-orange-500"></i>
                <input type="email" value="Santos@gmail.com" class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
              </div>
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
              <div class="relative">
                <i class="fi fi-rr-user absolute left-4 top-1/2 -translate-y-1/2 text-orange-500"></i>
                <input type="text" value="emmanuelleSantos" class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t-2 border-gray-200">
          <button id="cancelEditBtn" class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95">
            Cancel
          </button>
          <button id="saveEditBtn" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2">
            <i class="fi fi-rr-disk"></i>
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('js/admin/userManagement/propertyOwner_Views.js') }}" defer></script>

</body>

</html>
