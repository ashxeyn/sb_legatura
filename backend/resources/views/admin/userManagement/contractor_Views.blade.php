<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/userManagement/contractor_Views.css') }}">
  
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
        <h1 class="text-2xl font-semibold text-gray-800">Contractor Details</h1>

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
          <a href="{{ route('admin.userManagement.contractor') }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium hover:bg-indigo-50 px-4 py-2 rounded-lg transition-all duration-300 hover:shadow-md hover:-translate-x-1">
            <i class="fi fi-rr-angle-left text-lg"></i>
            <span>Back</span>
          </a>
          <div class="flex items-center gap-3">
            <button id="editContractorBtn" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-all duration-300 text-gray-700 flex items-center gap-2 hover:shadow-md hover:scale-105">
              <i class="fi fi-rr-edit"></i>
              <span>Edit</span>
            </button>
            <button id="suspendContractorBtn" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all duration-300 flex items-center gap-2 hover:shadow-lg hover:scale-105">
              <i class="fi fi-rr-ban"></i>
              <span>Suspend</span>
            </button>
          </div>
        </div>

        <!-- Account Profile Section (Full Width) -->
        <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
          <div class="relative h-44 md:h-56 bg-gradient-to-r from-slate-700 via-slate-800 to-slate-900 overflow-hidden group">
            <img src="" alt="Account Banner" class="w-full h-full object-cover opacity-40 group-hover:scale-105 transition-transform duration-500">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
          </div>
          <div class="relative pt-20 px-6 md:px-8 pb-8">
            <div class="absolute -top-16 left-8">
              <div class="w-32 h-32 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 p-1 shadow-2xl ring-4 ring-white">
                <div class="w-full h-full rounded-full bg-white flex items-center justify-center overflow-hidden">
                  <span class="text-3xl font-bold text-orange-600">JL</span>
                </div>
              </div>
            </div>

            <div class="mb-6">
              <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-1">Account Profile</h2>
              <p class="text-sm text-gray-600">Contractor Account Information</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Username</label>
                <p class="text-base font-medium text-gray-800">jlois_construction</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Account Status</label>
                <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">Active</span>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Member Since</label>
                <p class="text-base font-medium text-gray-800">January 15, 2023</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Last Login</label>
                <p class="text-base font-medium text-gray-800">November 20, 2025 at 3:45 PM</p>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Bio</label>
                <p class="text-base text-gray-700 leading-relaxed">
                  Leading construction company specializing in residential and commercial projects with over 98 years of excellence in the industry.
                </p>
              </div>
            </div>
          </div>
        </section>

        <!-- Info grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Left 2/3 -->
          <div class="xl:col-span-2 space-y-6">
            <!-- Company Profile Card -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 md:px-8 py-6">
                <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                  <i class="fi fi-sr-building"></i>
                  Company Profile
                </h2>
              </div>
              <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Company Logo -->
                  <div class="md:col-span-2 flex items-center gap-6 p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border-2 border-orange-200">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-orange-200">
                      <img id="companyLogoImg" src="" alt="Company Logo" class="w-full h-full object-cover hidden">
                      <i id="companyLogoIcon" class="fi fi-sr-building text-white text-4xl"></i>
                    </div>
                    <div class="flex-1">
                      <h3 class="text-2xl font-bold text-gray-800">J'Lois Construction</h3>
                      <p class="text-sm text-gray-600 mt-1">URL: construction@gmail.com</p>
                      <p class="text-sm text-orange-600 mt-1 font-semibold">Experience: 98 Years</p>
                    </div>
                  </div>

                  <!-- Company Information Fields -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Company Name</label>
                    <p class="text-base font-medium text-gray-800">J'Lois Construction</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Years of Operation</label>
                    <p class="text-base font-medium text-gray-800">98 Years</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Account Type</label>
                    <span class="inline-block px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-semibold">Construction</span>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Contact Number</label>
                    <p class="text-base font-medium text-gray-800">+63 912 345 6789</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">License Number</label>
                    <p class="text-base font-medium text-gray-800">LIC-2023-12345</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Registration Date</label>
                    <p class="text-base font-medium text-gray-800">January 15, 2023</p>
                  </div>

                  <!-- Company Website / Socials -->
                  <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">Company Website / Socials</label>
                    <div class="space-y-2">
                      <div class="flex items-center gap-2 text-sm text-gray-700 hover:text-orange-600 transition-colors">
                        <i class="fi fi-rr-globe text-orange-500"></i>
                        <a href="https://jloisconstruction.com" target="_blank" class="hover:underline">https://jloisconstruction.com</a>
                      </div>
                      <div class="flex items-center gap-2 text-sm text-gray-700 hover:text-orange-600 transition-colors">
                        <i class="fi fi-brands-facebook text-orange-500"></i>
                        <a href="https://facebook.com/jloisconstruction" target="_blank" class="hover:underline">https://facebook.com/jloisconstruction</a>
                      </div>
                      <div class="flex items-center gap-2 text-sm text-gray-700 hover:text-orange-600 transition-colors">
                        <i class="fi fi-brands-linkedin text-orange-500"></i>
                        <a href="https://linkedin.com/company/jloisconstruction" target="_blank" class="hover:underline">https://linkedin.com/company/jloisconstruction</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Company Representative Section -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 md:px-8 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                  <i class="fi fi-rr-id-badge"></i>
                  Company Representative Information
                </h2>
                <button id="changeRepresentativeBtn" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg font-medium text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2 hover:scale-105">
                  <i class="fi fi-rr-refresh"></i>
                  <span>Change Representative</span>
                </button>
              </div>
              <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Representative Photo -->
                  <div class="md:col-span-2 flex items-center gap-6 p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border-2 border-blue-200">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-blue-200">
                      <img id="repPhotoImg" src="" alt="Representative Photo" class="w-full h-full object-cover hidden">
                      <i id="repPhotoIcon" class="fi fi-rr-user text-white text-3xl"></i>
                    </div>
                    <div class="flex-1">
                      <h3 class="text-xl font-bold text-gray-800">John Michael Santos</h3>
                      <p class="text-sm text-gray-600 mt-1">Project Manager</p>
                    </div>
                  </div>

                  <!-- Representative Information Fields -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">First Name</label>
                    <p class="text-base font-medium text-gray-800">John</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Middle Name</label>
                    <p class="text-base font-medium text-gray-800">Michael</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Last Name</label>
                    <p class="text-base font-medium text-gray-800">Santos</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Position / Role</label>
                    <p class="text-base font-medium text-gray-800">Project Manager</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Email Address</label>
                    <p class="text-base font-medium text-gray-800">john.santos@jlois.com</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Contact Number</label>
                    <p class="text-base font-medium text-gray-800">+63 918 765 4321</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Username</label>
                    <p class="text-base font-medium text-gray-800">johnsantos</p>
                  </div>
                </div>
              </div>
            </section>

            <!-- Account Setup & Business Address -->
            
          </div>

          <!-- Right 1/3: Documents & Services -->
          <div class="space-y-6">
            <!-- Documents Section -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                  <i class="fi fi-rr-document"></i>
                  Documents
                </h2>
              </div>
              <div class="p-6 space-y-4">
                <!-- PCAB Information -->
                <div class="space-y-3 pb-4 border-b border-gray-200">
                  <h3 class="text-sm font-bold text-orange-600 flex items-center gap-2">
                    <i class="fi fi-rr-certificate"></i>
                    PCAB License
                  </h3>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">PCAB No.</label>
                    <p class="text-sm font-medium text-gray-800">23345-AB-2025</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Category</label>
                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">Building</span>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Expiration Date</label>
                    <p class="text-sm font-medium text-gray-800">December 31, 2025</p>
                  </div>
                </div>

                <!-- Business Permit -->
                <div class="space-y-3 pb-4 border-b border-gray-200">
                  <h3 class="text-sm font-bold text-orange-600 flex items-center gap-2">
                    <i class="fi fi-rr-file-check"></i>
                    Business Permit
                  </h3>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Permit City</label>
                    <p class="text-sm font-medium text-gray-800">Zamboanga City</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Permit Number</label>
                    <p class="text-sm font-medium text-gray-800">BP-2025-7907</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Expiration Date</label>
                    <p class="text-sm font-medium text-gray-800">December 31, 2025</p>
                  </div>
                </div>

                <!-- TIN & DTI/SEC -->
                <div class="space-y-3">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">TIN Business Registration No.</label>
                    <p class="text-sm font-medium text-gray-800">123-456-789-000</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">DTI / SEC Registration</label>
                    <a href="#" class="inline-flex items-center gap-2 px-3 py-2 bg-orange-50 hover:bg-orange-100 text-orange-600 rounded-lg text-sm font-medium transition-all hover:shadow-md group">
                      <i class="fi fi-rr-file-pdf text-red-500"></i>
                      <span>DTIRegistration.pdf</span>
                      <span class="text-xs text-gray-500 group-hover:text-orange-500">• Approved</span>
                    </a>
                  </div>
                </div>
              </div>
            </section>

            <!-- Specialties Section -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                  <i class="fi fi-rr-tools"></i>
                  Services Offered
                </h2>
              </div>
              <div class="p-6">
                <div class="flex flex-wrap gap-2">
                  <span class="px-3 py-1.5 bg-gradient-to-r from-orange-100 to-orange-200 text-orange-700 rounded-full text-xs font-semibold shadow-sm hover:shadow-md transition-all cursor-default">
                    Residential Building Construction
                  </span>
                  <span class="px-3 py-1.5 bg-gradient-to-r from-orange-100 to-orange-200 text-orange-700 rounded-full text-xs font-semibold shadow-sm hover:shadow-md transition-all cursor-default">
                    Design and Build Services
                  </span>
                  <span class="px-3 py-1.5 bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700 rounded-full text-xs font-semibold shadow-sm hover:shadow-md transition-all cursor-default">
                    Modern
                  </span>
                  <span class="px-3 py-1.5 bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700 rounded-full text-xs font-semibold shadow-sm hover:shadow-md transition-all cursor-default">
                    Building
                  </span>
                  <span class="px-3 py-1.5 bg-gradient-to-r from-green-100 to-green-200 text-green-700 rounded-full text-xs font-semibold shadow-sm hover:shadow-md transition-all cursor-default">
                    Warehouse
                  </span>
                  <span class="px-3 py-1.5 bg-gradient-to-r from-green-100 to-green-200 text-green-700 rounded-full text-xs font-semibold shadow-sm hover:shadow-md transition-all cursor-default">
                    Facilities
                  </span>
                  <span class="px-3 py-1.5 bg-gradient-to-r from-purple-100 to-purple-200 text-purple-700 rounded-full text-xs font-semibold shadow-sm hover:shadow-md transition-all cursor-default">
                    Large-scale
                  </span>
                </div>
              </div>
            </section>

            <!-- Business Address Section -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                  <i class="fi fi-rr-marker"></i>
                  Business Address
                </h2>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Street Address</label>
                    <p class="text-base text-gray-800">123 Construction Avenue, Building 5</p>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">City</label>
                      <p class="text-base text-gray-800">Zamboanga City</p>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Province</label>
                      <p class="text-base text-gray-800">Zamboanga del Sur</p>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Postal Code</label>
                      <p class="text-base text-gray-800">7000</p>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Country</label>
                      <p class="text-base text-gray-800">Philippines</p>
                    </div>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>

        <!-- Team Members Section (Full Width) -->
        <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
          <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <i class="fi fi-rr-users-alt text-gray-800 text-xl"></i>
              <h2 class="text-xl font-bold text-gray-800">Team Members</h2>
            </div>
            <button id="addTeamMemberBtn" class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-4 py-2 rounded-lg font-medium text-sm shadow-md hover:shadow-lg transition transform hover:scale-105">
              <i class="fi fi-rr-plus text-sm"></i>
              <span>Add Member</span>
            </button>
          </div>

          <!-- Tabs -->
          <div class="border-b border-gray-200">
            <div class="flex px-6">
              <button class="team-tab active px-4 py-3 text-sm font-semibold border-b-2 border-orange-500 text-orange-600 transition-all" data-tab="all">
                All
              </button>
              <button class="team-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-orange-600 hover:border-orange-300 transition-all" data-tab="deactivated">
                Deactivated Accounts
              </button>
            </div>
          </div>

          <!-- Team Members Table -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Member</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Position</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Added</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200" id="teamMembersTable">
                <!-- Team Member 1 -->
                <tr class="hover:bg-gray-50 transition-all duration-200 group team-member-row" data-status="active" data-email="olive.podios@jlois.com" data-contact="+63 912 345 6789">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center overflow-hidden shadow-md group-hover:shadow-lg transition-all group-hover:scale-110">
                        <img src="" alt="Olive Faith Podios" class="w-full h-full object-cover hidden">
                        <span class="text-white font-bold text-sm">OF</span>
                      </div>
                      <span class="font-medium text-gray-800 group-hover:text-orange-600 transition">Olive Faith Podios</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Secretary</td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Aug 30, 2025</td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 transition-all duration-200 hover:scale-110 hover:shadow-md">
                      Active
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                      <button class="team-edit-btn p-2 rounded-lg hover:bg-orange-50 transition-all group/btn" title="Edit Member">
                        <i class="fi fi-rr-pencil text-orange-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                      <button class="team-deactivate-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn" title="Deactivate Account">
                        <i class="fi fi-rr-ban text-red-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Team Member 2 -->
                <tr class="hover:bg-gray-50 transition-all duration-200 group team-member-row" data-status="active" data-email="shane.jimenez@jlois.com" data-contact="+63 918 765 4321">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow-md group-hover:shadow-lg transition-all group-hover:scale-110">
                        <img src="" alt="Shane Hart Jimenez" class="w-full h-full object-cover hidden">
                        <span class="text-white font-bold text-sm">SH</span>
                      </div>
                      <span class="font-medium text-gray-800 group-hover:text-orange-600 transition">Shane Hart Jimenez</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Site Engineer</td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Aug 30, 2025</td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 transition-all duration-200 hover:scale-110 hover:shadow-md">
                      Active
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                      <button class="team-edit-btn p-2 rounded-lg hover:bg-orange-50 transition-all group/btn" title="Edit Member">
                        <i class="fi fi-rr-pencil text-orange-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                      <button class="team-deactivate-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn" title="Deactivate Account">
                        <i class="fi fi-rr-ban text-red-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Team Member 3 -->
                <tr class="hover:bg-gray-50 transition-all duration-200 group team-member-row" data-status="active" data-email="maria.santos@jlois.com" data-contact="+63 923 456 7890">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center overflow-hidden shadow-md group-hover:shadow-lg transition-all group-hover:scale-110">
                        <img src="" alt="Maria Santos Cruz" class="w-full h-full object-cover hidden">
                        <span class="text-white font-bold text-sm">MS</span>
                      </div>
                      <span class="font-medium text-gray-800 group-hover:text-orange-600 transition">Maria Santos Cruz</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Project Coordinator</td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Sep 15, 2025</td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 transition-all duration-200 hover:scale-110 hover:shadow-md">
                      Active
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                      <button class="team-edit-btn p-2 rounded-lg hover:bg-orange-50 transition-all group/btn" title="Edit Member">
                        <i class="fi fi-rr-pencil text-orange-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                      <button class="team-deactivate-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn" title="Deactivate Account">
                        <i class="fi fi-rr-ban text-red-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Team Member 4 -->
                <tr class="hover:bg-gray-50 transition-all duration-200 group team-member-row" data-status="active" data-email="jason.reyes@jlois.com" data-contact="+63 917 234 5678">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center overflow-hidden shadow-md group-hover:shadow-lg transition-all group-hover:scale-110">
                        <img src="" alt="Carlos Rivera Lopez" class="w-full h-full object-cover hidden">
                        <span class="text-white font-bold text-sm">CR</span>
                      </div>
                      <span class="font-medium text-gray-800 group-hover:text-orange-600 transition">Carlos Rivera Lopez</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Safety Officer</td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Oct 10, 2025</td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 transition-all duration-200 hover:scale-110 hover:shadow-md">
                      Active
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                      <button class="team-edit-btn p-2 rounded-lg hover:bg-orange-50 transition-all group/btn" title="Edit Member">
                        <i class="fi fi-rr-pencil text-orange-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                      <button class="team-deactivate-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn" title="Deactivate Account">
                        <i class="fi fi-rr-ban text-red-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Team Member 5 -->
                <tr class="hover:bg-gray-50 transition-all duration-200 group team-member-row" data-status="active" data-email="lisa.garcia@jlois.com" data-contact="+63 919 876 5432">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center overflow-hidden shadow-md group-hover:shadow-lg transition-all group-hover:scale-110">
                        <img src="" alt="Anna Marie Reyes" class="w-full h-full object-cover hidden">
                        <span class="text-white font-bold text-sm">AM</span>
                      </div>
                      <span class="font-medium text-gray-800 group-hover:text-orange-600 transition">Anna Marie Reyes</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Architect</td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Nov 01, 2025</td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 transition-all duration-200 hover:scale-110 hover:shadow-md">
                      Active
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                      <button class="team-edit-btn p-2 rounded-lg hover:bg-orange-50 transition-all group/btn" title="Edit Member">
                        <i class="fi fi-rr-pencil text-orange-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                      <button class="team-deactivate-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn" title="Deactivate Account">
                        <i class="fi fi-rr-ban text-red-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Team Member 6 - Deactivated -->
                <tr class="hover:bg-gray-50 transition-all duration-200 group team-member-row hidden" data-status="deactivated" data-email="mark.lopez@jlois.com" data-contact="+63 920 111 2233">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3 opacity-60">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center overflow-hidden shadow-md">
                        <img src="" alt="Robert Garcia" class="w-full h-full object-cover hidden">
                        <span class="text-white font-bold text-sm">RG</span>
                      </div>
                      <span class="font-medium text-gray-600">Robert Garcia</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Foreman</td>
                  <td class="px-6 py-4 text-center text-sm text-gray-600">Jul 20, 2025</td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                      Deactivated
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                      <button class="team-reactivate-btn p-2 rounded-lg hover:bg-green-50 transition-all group/btn" title="Reactivate Account">
                        <i class="fi fi-rr-check-circle text-green-600 group-hover/btn:scale-110 transition-transform"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
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
                You are about to suspend this contractor account. This will immediately restrict their access to the platform and all active projects.
              </p>
            </div>
          </div>
          
          <!-- Contractor Info Card -->
          <div class="bg-white rounded-lg p-3 border border-red-200 space-y-2">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 text-white font-bold flex items-center justify-center shadow-md">
                JL
              </div>
              <div>
                <p class="font-bold text-gray-800">J'Lois Construction</p>
                <p class="text-xs text-gray-600">jlois_construction</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200">
              <div class="text-center">
                <p class="text-xs text-gray-500">Active Projects</p>
                <p class="text-lg font-bold text-gray-800">12</p>
              </div>
              <div class="text-center">
                <p class="text-xs text-gray-500">Member Since</p>
                <p class="text-sm font-semibold text-gray-800">Jan 2023</p>
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
            This reason will be recorded and may be shared with the contractor.
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
              <div class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-red-500 peer-checked:bg-red-50 transition-all hover:border-red-300 group-hover:shadow-md">
                <p class="font-semibold text-gray-800 text-sm">Temporary</p>
                <p class="text-xs text-gray-600">Can be reactivated</p>
              </div>
            </label>
            <label class="relative cursor-pointer group">
              <input type="radio" name="suspensionDuration" value="permanent" class="peer sr-only">
              <div class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-red-500 peer-checked:bg-red-50 transition-all hover:border-red-300 group-hover:shadow-md">
                <p class="font-semibold text-gray-800 text-sm">Permanent</p>
                <p class="text-xs text-gray-600">Account deletion</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Consequences Warning -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded-r-lg">
          <div class="flex gap-2">
            <i class="fi fi-rr-triangle-warning text-yellow-600 flex-shrink-0 mt-0.5"></i>
            <div class="text-sm text-gray-700 space-y-1">
              <p class="font-semibold">This action will:</p>
              <ul class="list-disc list-inside space-y-0.5 text-xs">
                <li>Immediately lock the contractor out of their account</li>
                <li>Pause all active projects and bids</li>
                <li>Send a notification to the contractor</li>
                <li>Create an audit log entry</li>
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

  <!-- Edit Contractor Modal -->
  <div id="editContractorModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 flex items-center justify-between rounded-t-2xl z-10 shadow-lg">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
          <i class="fi fi-rr-edit"></i>
          Edit Contractor
        </h2>
        <button id="closeEditModalBtn" class="text-white hover:text-orange-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(90vh-80px)] p-6 md:p-8 space-y-6">
        
        <!-- Edit Type Selector -->
        <div class="flex gap-3 p-4 bg-gray-50 rounded-xl border-2 border-gray-200">
          <button id="editCompanyTab" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg font-semibold shadow-md transition-all hover:shadow-lg flex items-center justify-center gap-2">
            <i class="fi fi-rr-building"></i>
            <span>Company Information</span>
          </button>
          <button id="editRepresentativeTab" class="flex-1 px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-semibold transition-all hover:bg-gray-50 hover:shadow-md flex items-center justify-center gap-2">
            <i class="fi fi-rr-user"></i>
            <span>Representative Information</span>
          </button>
        </div>

        <!-- Company Information Form -->
        <div id="companyFormSection" class="space-y-6">
        <!-- Company Logo Section -->
        <div class="flex items-center gap-6 p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border-2 border-orange-200 hover:shadow-lg transition-all duration-300">
          <div class="relative group">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-orange-200 hover:ring-orange-300 transition-all duration-300">
              <img id="editCompanyLogoPreview" src="" alt="Company Logo" class="w-full h-full object-cover hidden">
              <i id="editCompanyLogoIcon" class="fi fi-sr-building text-white text-4xl"></i>
            </div>
            <label for="editCompanyLogoUpload" class="absolute -bottom-1 -right-1 bg-orange-500 hover:bg-orange-600 text-white p-2.5 rounded-full cursor-pointer shadow-lg transition-all transform hover:scale-110 hover:rotate-12">
              <i class="fi fi-rr-camera"></i>
            </label>
            <input type="file" id="editCompanyLogoUpload" accept="image/*" class="hidden">
          </div>
          <div>
            <h3 class="text-lg font-bold text-gray-800">Company Logo</h3>
            <p class="text-sm text-gray-600 mt-1">Click the camera icon to update logo</p>
            <p class="text-xs text-orange-600 mt-1 font-medium">• JPG, PNG • Max 5MB</p>
          </div>
        </div>

        <!-- Company Information Section -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-building"></i>
            Company Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
              <label class="form-label">Company Name</label>
              <input type="text" value="J'Lois Construction" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Years of Operation</label>
              <input type="number" value="98" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Account Type</label>
              <select class="form-input">
                <option>General Contractor</option>
                <option selected>Construction</option>
                <option>Specialty Contractor</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Contact Number</label>
              <input type="tel" value="+63 912 345 6789" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">License Number</label>
              <input type="text" value="LIC-2023-12345" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Registration Date</label>
              <input type="date" value="2023-01-15" class="form-input">
            </div>
          </div>
        </div>

        <!-- Company Website / Socials -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-globe"></i>
            Company Website / Socials
          </h3>
          <div class="space-y-3">
            <div class="form-group">
              <input type="url" value="https://jloisconstruction.com" placeholder="https://" class="form-input">
            </div>
            <div class="form-group">
              <input type="url" value="https://facebook.com/jloisconstruction" placeholder="https://" class="form-input">
            </div>
            <div class="form-group">
              <input type="url" value="https://linkedin.com/company/jloisconstruction" placeholder="https://" class="form-input">
            </div>
          </div>
        </div>

        <!-- Action Buttons for Company -->
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

        <!-- Company Representative Information Form -->
        <div id="representativeFormSection" class="space-y-6 hidden">
        <!-- Company Representative Information -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-id-badge"></i>
            Company Representative Information
          </h3>
          
          <!-- Representative Photo -->
          <div class="flex items-center gap-6 p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border-2 border-blue-200 hover:shadow-lg transition-all duration-300 mb-4">
            <div class="relative group">
              <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-blue-200 hover:ring-blue-300 transition-all duration-300">
                <img id="editRepPhotoPreview" src="" alt="Representative Photo" class="w-full h-full object-cover hidden">
                <i id="editRepPhotoIcon" class="fi fi-rr-user text-white text-3xl"></i>
              </div>
              <label for="editRepPhotoUpload" class="absolute -bottom-1 -right-1 bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition-all transform hover:scale-110 hover:rotate-12">
                <i class="fi fi-rr-camera text-sm"></i>
              </label>
              <input type="file" id="editRepPhotoUpload" accept="image/*" class="hidden">
            </div>
            <div>
              <h3 class="text-base font-bold text-gray-800">Representative Photo</h3>
              <p class="text-sm text-gray-600 mt-1">Optional • JPG/PNG • 400x400 recommended</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
              <label class="form-label">First Name</label>
              <input type="text" value="John" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Position / Role</label>
              <input type="text" value="Project Manager" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Middle Name</label>
              <input type="text" value="Michael" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input type="email" value="john.santos@jlois.com" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Last Name</label>
              <input type="text" value="Santos" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Contact Number</label>
              <input type="tel" value="+63 918 765 4321" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" value="johnsantos" class="form-input">
            </div>
          </div>
        </div>

        <!-- Business Address -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-marker"></i>
            Business Address
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group md:col-span-2">
              <label class="form-label">Street Address</label>
              <input type="text" value="123 Construction Avenue, Building 5" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">City</label>
              <input type="text" value="Zamboanga City" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Province</label>
              <input type="text" value="Zamboanga del Sur" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Postal Code</label>
              <input type="text" value="7000" class="form-input">
            </div>
          </div>
        </div>

        <!-- Action Buttons for Representative -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t-2 border-gray-200">
          <button class="cancel-edit-rep-btn px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95">
            Cancel
          </button>
          <button class="save-edit-rep-btn px-8 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2">
            <i class="fi fi-rr-disk"></i>
            Save Changes
          </button>
        </div>
        </div>

      </div>
    </div>
  </div>

  </div>

  <!-- Add Team Member Modal -->
  <div id="addTeamMemberModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 flex items-center justify-between rounded-t-2xl z-10 shadow-lg">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
          <i class="fi fi-rr-user-add"></i>
          Add Team Member
        </h2>
        <button id="closeAddTeamMemberBtn" class="text-white hover:text-orange-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(90vh-80px)] p-6 md:p-8 space-y-6">
        <!-- Profile Picture -->
        <div class="flex items-center gap-6 p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border-2 border-orange-200">
          <div class="relative group">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center overflow-hidden shadow-md">
              <i class="fi fi-rr-user text-3xl text-gray-500" id="teamMemberIcon"></i>
              <img id="teamMemberPreview" class="w-full h-full object-cover hidden" alt="Member Preview">
            </div>
            <label for="teamMemberUpload" class="absolute -bottom-1 -right-1 bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition transform hover:scale-110">
              <i class="fi fi-rr-camera text-sm"></i>
              <input type="file" id="teamMemberUpload" class="hidden" accept="image/*">
            </label>
          </div>
          <div>
            <h3 class="text-base font-semibold text-gray-800">Member Photo</h3>
            <p class="text-sm text-gray-600 mt-1">Optional • JPG/PNG • 400x400 recommended</p>
          </div>
        </div>

        <!-- Personal Information -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-user"></i>
            Personal Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
              <label class="form-label">First Name <span class="text-red-500">*</span></label>
              <input type="text" id="teamMemberFirstName" placeholder="Enter first name" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Last Name <span class="text-red-500">*</span></label>
              <input type="text" id="teamMemberLastName" placeholder="Enter last name" class="form-input">
            </div>
            <div class="form-group md:col-span-2">
              <label class="form-label">Position / Role <span class="text-red-500">*</span></label>
              <input type="text" id="teamMemberPosition" placeholder="e.g., Site Engineer, Architect" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Email Address <span class="text-red-500">*</span></label>
              <input type="email" id="teamMemberEmail" placeholder="name@company.com" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Contact Number</label>
              <input type="tel" id="teamMemberContact" placeholder="+63 9xx xxx xxxx" class="form-input">
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t-2 border-gray-200">
          <button id="cancelAddTeamMemberBtn" class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95">
            Cancel
          </button>
          <button id="saveTeamMemberBtn" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2">
            <i class="fi fi-rr-disk"></i>
            Add Member
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Team Member Modal -->
  <div id="editTeamMemberModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 flex items-center justify-between rounded-t-2xl z-10 shadow-lg">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
          <i class="fi fi-rr-edit"></i>
          Edit Team Member
        </h2>
        <button id="closeEditTeamMemberBtn" class="text-white hover:text-orange-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(90vh-80px)] p-6 md:p-8 space-y-6">
        <!-- Profile Picture -->
        <div class="flex items-center gap-6 p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border-2 border-orange-200">
          <div class="relative group">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center overflow-hidden shadow-md">
              <span class="text-white font-bold text-lg" id="editTeamMemberInitials">OF</span>
              <img id="editTeamMemberPreview" class="w-full h-full object-cover hidden" alt="Member Preview">
            </div>
            <label for="editTeamMemberUpload" class="absolute -bottom-1 -right-1 bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition transform hover:scale-110">
              <i class="fi fi-rr-camera text-sm"></i>
              <input type="file" id="editTeamMemberUpload" class="hidden" accept="image/*">
            </label>
          </div>
          <div>
            <h3 class="text-base font-semibold text-gray-800">Member Photo</h3>
            <p class="text-sm text-gray-600 mt-1">Optional • JPG/PNG • 400x400 recommended</p>
          </div>
        </div>

        <!-- Personal Information -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-user"></i>
            Personal Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
              <label class="form-label">First Name <span class="text-red-500">*</span></label>
              <input type="text" id="editTeamMemberFirstName" value="Olive Faith" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Last Name <span class="text-red-500">*</span></label>
              <input type="text" id="editTeamMemberLastName" value="Podios" class="form-input">
            </div>
            <div class="form-group md:col-span-2">
              <label class="form-label">Position / Role <span class="text-red-500">*</span></label>
              <input type="text" id="editTeamMemberPosition" value="Secretary" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Email Address <span class="text-red-500">*</span></label>
              <input type="email" id="editTeamMemberEmail" value="olive.podios@jlois.com" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label">Contact Number</label>
              <input type="tel" id="editTeamMemberContact" value="+63 912 345 6789" class="form-input">
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t-2 border-gray-200">
          <button id="cancelEditTeamMemberBtn" class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95">
            Cancel
          </button>
          <button id="saveEditTeamMemberBtn" class="px-8 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2">
            <i class="fi fi-rr-disk"></i>
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Deactivate Team Member Modal -->
  <div id="deactivateTeamMemberModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 hidden items-center justify-center p-4 animate-fadeIn">
    <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0 modal-content overflow-hidden">
      <!-- Icon Section with Gradient Background -->
      <div class="flex justify-center pt-12 pb-8 bg-gradient-to-b from-red-50 to-white relative">
        <div class="w-32 h-32 bg-gradient-to-br from-red-100 to-red-50 rounded-full flex items-center justify-center relative animate-pulse-slow">
          <div class="absolute inset-0 bg-red-200 rounded-full animate-ping opacity-30"></div>
          <div class="relative w-24 h-24 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center shadow-xl transform hover:scale-110 transition-transform duration-300">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
              <i class="fi fi-sr-cross-circle text-white text-4xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Section -->
      <div class="px-8 pb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">Deactivate User</h2>
        <p class="text-gray-600 leading-relaxed text-base mb-2">
          Are you sure you want to deactivate
        </p>
        <p class="text-xl font-bold text-gray-900 mb-1" id="deactivateTeamMemberName">Olive Faith Podios</p>
        <p class="text-sm text-gray-500">This action can be reversed later.</p>
      </div>

      <!-- Action Buttons -->
      <div class="px-8 pb-8 space-y-3">
        <button id="confirmDeactivateTeamMemberBtn" class="w-full px-6 py-4 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-2xl transition-all font-bold shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 text-base">
          <i class="fi fi-rr-ban"></i>
          Deactivate
        </button>
        <button id="cancelDeactivateTeamMemberBtn" class="w-full px-6 py-4 bg-white border-2 border-gray-200 text-gray-700 rounded-2xl hover:bg-gray-50 transition-all font-semibold hover:border-gray-300 hover:shadow-md transform hover:scale-[1.02] active:scale-95 text-base">
          Cancel
        </button>
      </div>
    </div>
  </div>

  <!-- Reactivate Team Member Modal -->
  <div id="reactivateTeamMemberModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 hidden items-center justify-center p-4 animate-fadeIn">
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
        <p class="text-xl font-bold text-gray-900 mb-1" id="reactivateTeamMemberName">Robert Garcia</p>
        <p class="text-sm text-gray-500">They will regain access to their account.</p>
      </div>

      <!-- Action Buttons -->
      <div class="px-8 pb-8 space-y-3">
        <button id="confirmReactivateTeamMemberBtn" class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-2xl transition-all font-bold shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 text-base">
          <i class="fi fi-rr-user-check"></i>
          Reactivate
        </button>
        <button id="cancelReactivateTeamMemberBtn" class="w-full px-6 py-4 bg-white border-2 border-gray-200 text-gray-700 rounded-2xl hover:bg-gray-50 transition-all font-semibold hover:border-gray-300 hover:shadow-md transform hover:scale-[1.02] active:scale-95 text-base">
          Cancel
        </button>
      </div>
    </div>
  </div>

  <!-- Change Representative Modal -->
  <div id="changeRepresentativeModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[85vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-5 flex items-center justify-between rounded-t-2xl z-10 shadow-lg">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
          <i class="fi fi-rr-refresh"></i>
          Change Company Representative
        </h2>
        <button id="closeChangeRepresentativeBtn" class="text-white hover:text-blue-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(85vh-140px)] p-6 space-y-4">
        <!-- Current Representative Info -->
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
          <div class="flex items-center gap-3 mb-2">
            <i class="fi fi-rr-info text-blue-600 text-lg"></i>
            <h3 class="font-bold text-gray-800">Current Representative</h3>
          </div>
          <div class="flex items-center gap-4 p-3 bg-white rounded-lg border border-blue-200">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold shadow-md">
              JS
            </div>
            <div>
              <p class="font-semibold text-gray-800">John Michael Santos</p>
              <p class="text-sm text-gray-600">Project Manager • john.santos@jlois.com</p>
            </div>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="relative">
          <input 
            type="text" 
            id="searchTeamMember"
            placeholder="Search team members by name or position..." 
            class="w-full px-4 py-3 pl-11 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"
          >
          <i class="fi fi-rr-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>

        <!-- Team Members List -->
        <div>
          <h3 class="text-sm font-bold text-gray-600 uppercase tracking-wider mb-3">Select New Representative</h3>
          <div class="space-y-2 max-h-96 overflow-y-auto" id="teamMembersList">
            
            <!-- Team Member Option 1 -->
            <div class="team-member-option flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer group" data-member-id="1" data-member-name="Olive Faith Podios" data-member-position="Secretary" data-member-email="olive.podios@jlois.com">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-md group-hover:scale-110 transition-transform">
                  OF
                </div>
                <div>
                  <p class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Olive Faith Podios</p>
                  <p class="text-sm text-gray-600">Secretary • olive.podios@jlois.com</p>
                </div>
              </div>
              <i class="fi fi-rr-check-circle text-2xl text-gray-300 group-hover:text-blue-500 transition-colors"></i>
            </div>

            <!-- Team Member Option 2 -->
            <div class="team-member-option flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer group" data-member-id="2" data-member-name="Shane Hart Jimenez" data-member-position="Site Engineer" data-member-email="shane.jimenez@jlois.com">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold shadow-md group-hover:scale-110 transition-transform">
                  SH
                </div>
                <div>
                  <p class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Shane Hart Jimenez</p>
                  <p class="text-sm text-gray-600">Site Engineer • shane.jimenez@jlois.com</p>
                </div>
              </div>
              <i class="fi fi-rr-check-circle text-2xl text-gray-300 group-hover:text-blue-500 transition-colors"></i>
            </div>

            <!-- Team Member Option 3 -->
            <div class="team-member-option flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer group" data-member-id="3" data-member-name="Maria Santos Cruz" data-member-position="Project Coordinator" data-member-email="maria.cruz@jlois.com">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white font-bold shadow-md group-hover:scale-110 transition-transform">
                  MS
                </div>
                <div>
                  <p class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Maria Santos Cruz</p>
                  <p class="text-sm text-gray-600">Project Coordinator • maria.cruz@jlois.com</p>
                </div>
              </div>
              <i class="fi fi-rr-check-circle text-2xl text-gray-300 group-hover:text-blue-500 transition-colors"></i>
            </div>

            <!-- Team Member Option 4 -->
            <div class="team-member-option flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer group" data-member-id="4" data-member-name="Carlos Rivera Lopez" data-member-position="Safety Officer" data-member-email="carlos.lopez@jlois.com">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white font-bold shadow-md group-hover:scale-110 transition-transform">
                  CR
                </div>
                <div>
                  <p class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Carlos Rivera Lopez</p>
                  <p class="text-sm text-gray-600">Safety Officer • carlos.lopez@jlois.com</p>
                </div>
              </div>
              <i class="fi fi-rr-check-circle text-2xl text-gray-300 group-hover:text-blue-500 transition-colors"></i>
            </div>

            <!-- Team Member Option 5 -->
            <div class="team-member-option flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer group" data-member-id="5" data-member-name="Anna Marie Reyes" data-member-position="Architect" data-member-email="anna.reyes@jlois.com">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-500 to-yellow-600 flex items-center justify-center text-white font-bold shadow-md group-hover:scale-110 transition-transform">
                  AM
                </div>
                <div>
                  <p class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">Anna Marie Reyes</p>
                  <p class="text-sm text-gray-600">Architect • anna.reyes@jlois.com</p>
                </div>
              </div>
              <i class="fi fi-rr-check-circle text-2xl text-gray-300 group-hover:text-blue-500 transition-colors"></i>
            </div>

          </div>
        </div>

        <!-- Warning Note -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded-r-lg">
          <div class="flex gap-2">
            <i class="fi fi-rr-triangle-warning text-yellow-600 flex-shrink-0 mt-0.5"></i>
            <div class="text-sm text-gray-700">
              <p class="font-semibold mb-1">Note:</p>
              <p>Changing the company representative will update all official documents and communication channels. The current representative will be notified of this change.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="sticky bottom-0 bg-white border-t-2 border-gray-200 px-6 py-4 rounded-b-2xl flex items-center justify-end gap-3">
        <button id="cancelChangeRepresentativeBtn" class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95">
          Cancel
        </button>
        <button id="confirmChangeRepresentativeBtn" class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
          <i class="fi fi-rr-check"></i>
          Assign Representative
        </button>
      </div>
    </div>
  </div>

  <script src="{{ asset('js/admin/userManagement/contractor_Views.js') }}" defer></script>

</body>

</html>