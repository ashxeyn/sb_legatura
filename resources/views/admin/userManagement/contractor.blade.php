<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/userManagement/contractor.css') }}">

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
            <a href="{{ route('admin.userManagement.contractor') }}" class="submenu-link active">Contractor</a>
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
        <h1 class="text-2xl font-semibold text-gray-800">Contractors</h1>

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

      <div class="p-8">
        <!-- Controls Section -->
        <!-- Controls Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
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

          <div class="flex items-center gap-4">
            <button id="resetFilterBtn" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                <i class="fi fi-rr-rotate-left"></i>
                <span>Reset Filter</span>
            </button>

            <button id="addContractorBtn" class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-5 py-2 rounded-lg font-medium text-sm shadow-md hover:shadow-lg transition transform hover:scale-105">
                <i class="fi fi-rr-plus text-lg"></i>
                <span>Add Contractor</span>
            </button>
          </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" id="contractorsTableWrap">
            @include('admin.userManagement.partials.contractorTable')
        </div>
      </div>

    </main>
  </div>

  <!-- Add Contractor Modal -->
  <div id="addContractorModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-8 py-5 flex items-center justify-between rounded-t-2xl z-10">
        <h2 class="text-2xl font-bold text-gray-800">Add New Contractor</h2>
        <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-lg hover:bg-gray-100">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-8">
        <!-- Profile Picture Section -->
        <div class="flex items-center gap-6 mb-8">
          <div class="relative group">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center overflow-hidden shadow-lg">
              <i class="fi fi-rr-building text-4xl text-gray-500" id="profileIcon"></i>
              <img id="profilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
            </div>
            <label for="profileUpload" class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition transform hover:scale-110">
              <i class="fi fi-rr-pencil text-sm"></i>
              <input type="file" id="profileUpload" name="profile_pic" class="hidden" accept="image/*">
            </label>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Company Logo</h3>
            <p class="text-sm text-gray-500">Upload a logo for the contractor company</p>
          </div>
        </div>

        <!-- Company Information Section -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
            <i class="fi fi-rr-building"></i>
            Company Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
              <input type="text" name="company_name" placeholder="Enter company name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Phone</label>
              <input type="tel" name="company_phone" placeholder="09xxxxxxxxx" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date of Incorporation</label>
              <input type="date" name="company_start_date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Contractor Type</label>
              <select name="contractor_type_id" id="contractorTypeSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                <option value="">Select Type</option>
                @foreach($contractorTypes as $type)
                  <option value="{{ $type->type_id }}">{{ $type->type_name }}</option>
                @endforeach
              </select>
              <input type="text" name="contractor_type_other_text" id="contractorTypeOtherInput" placeholder="Please specify type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition mt-2 hidden">
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Services Offered</label>
              <input type="text" name="services_offered" placeholder="e.g. Plumbing, Electrical, Roofing" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Website <span class="text-gray-400">(optional)</span></label>
              <input type="url" name="company_website" placeholder="https://" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Social Media <span class="text-gray-400">(optional)</span></label>
              <input type="url" name="company_social_media" placeholder="https://" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
          </div>
        </div>

        <!-- Representative Information Section -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
            <i class="fi fi-rr-user"></i>
            Representative Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
              <input type="text" name="first_name" placeholder="Enter first name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name <span class="text-gray-400">(optional)</span></label>
              <input type="text" name="middle_name" placeholder="Enter middle name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
              <input type="text" name="last_name" placeholder="Enter last name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Email</label>
              <input type="email" name="company_email" placeholder="Enter email address" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Account Setup Section -->
          <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
                <i class="fi fi-rr-user-gear"></i>
                Account Setup
                </h3>
                <div class="space-y-4">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fi fi-rr-info text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <span class="font-bold">Note:</span> Username and Password are automatically generated.
                            </p>
                            <p class="text-sm text-blue-600 mt-1">
                                Default Password: <span class="font-mono font-bold bg-blue-100 px-1 rounded">contractor123@!</span>
                            </p>
                            <p class="text-sm text-blue-600 mt-1">
                                The username will be <span class="font-mono font-bold bg-blue-100 px-1 rounded">contractor_</span> followed by a random 4-digit number.
                            </p>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
                <i class="fi fi-rr-map-marker"></i>
                Business Address
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                        <select id="contractor_address_province" name="business_address_province" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                            <option value="">Select Province</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">{{ $province['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                        <select id="contractor_address_city" name="business_address_city" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition" disabled>
                            <option value="">Select City/Municipality</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                        <select id="contractor_address_barangay" name="business_address_barangay" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition" disabled>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Street Address / Unit No.</label>
                        <input type="text" name="business_address_street" placeholder="Enter street address" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Zip Code</label>
                        <input type="text" name="business_address_postal" placeholder="Enter zip code" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                    </div>
                </div>
            </div>
          </div>
        </div>

        <!-- Documents Section -->
        <div class="mt-6">
          <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
            <i class="fi fi-rr-file-invoice"></i>
            Legal Documents
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Number <span class="text-red-500">*</span></label>
              <input type="text" name="picab_number" placeholder="Enter PCAB number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Category <span class="text-red-500">*</span></label>
              <select name="picab_category" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                <option value="">Select Category</option>
                @foreach($picabCategories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Expiration Date <span class="text-red-500">*</span></label>
              <input type="date" name="picab_expiration_date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit Number <span class="text-red-500">*</span></label>
              <input type="text" name="business_permit_number" placeholder="Enter permit number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit City <span class="text-red-500">*</span></label>
              <select id="business_permit_city" name="business_permit_city" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                <option value="">Select City</option>
                @foreach($allCities as $city)
                    <option value="{{ $city['name'] }}">{{ $city['name'] }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit Expiration <span class="text-red-500">*</span></label>
              <input type="date" name="business_permit_expiration" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">TIN Business Registration Number <span class="text-red-500">*</span></label>
              <input type="text" name="tin_business_reg_number" placeholder="Enter TIN/Business Reg. number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">DTI / SEC Registration <span class="text-red-500">*</span></label>
              <div id="dtiDropzone" class="flex items-center justify-center w-full h-[110px] rounded-xl border-2 border-dashed border-orange-300 bg-orange-50 text-orange-600 hover:bg-orange-100 transition-all relative cursor-pointer">
                <input id="dtiUpload" name="dti_sec_registration_photo" type="file" accept="image/*,application/pdf" class="hidden">
                <div class="text-center pointer-events-none">
                  <i class="fi fi-rr-upload text-2xl"></i>
                  <div class="text-sm font-medium mt-1">Upload image or file</div>
                  <div id="dtiFileName" class="text-xs text-orange-500 mt-1"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
          <button id="cancelBtn" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
            Cancel
          </button>
          <button id="saveBtn" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg transform hover:scale-105">
            Save Contractor
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Contractor Modal -->
  <div id="editContractorModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-8 py-5 flex items-center justify-between rounded-t-2xl z-10">
        <h2 class="text-2xl font-bold text-gray-800">Edit Contractor</h2>
        <button id="closeEditModalBtn" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-lg hover:bg-gray-100">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-8">
        <form id="editContractorForm">
        <input type="hidden" id="edit_user_id" name="user_id">
        <!-- Profile Picture Section -->
        <div class="flex items-center gap-6 mb-8">
          <div class="relative group">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center overflow-hidden shadow-lg">
              <i class="fi fi-rr-building text-4xl text-gray-500" id="editProfileIcon"></i>
              <img id="editProfilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
            </div>
            <label for="editProfileUpload" class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition transform hover:scale-110">
              <i class="fi fi-rr-pencil text-sm"></i>
              <input type="file" id="editProfileUpload" name="profile_pic" class="hidden" accept="image/*">
            </label>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Company Logo</h3>
            <p class="text-sm text-gray-500">Update logo for the contractor company</p>
          </div>
        </div>

        <!-- Company Information Section -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
            <i class="fi fi-rr-building"></i>
            Company Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
              <input type="text" id="edit_company_name" name="company_name" placeholder="Enter company name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Phone</label>
              <input type="tel" id="edit_company_phone" name="company_phone" placeholder="09xxxxxxxxx" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date of Incorporation</label>
              <input type="date" id="edit_company_start_date" name="company_start_date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Contractor Type</label>
              <select name="contractor_type_id" id="edit_contractorTypeSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                <option value="">Select Type</option>
                @foreach($contractorTypes as $type)
                  <option value="{{ $type->type_id }}">{{ $type->type_name }}</option>
                @endforeach
              </select>
              <input type="text" name="contractor_type_other_text" id="edit_contractorTypeOtherInput" placeholder="Please specify type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition mt-2 hidden">
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Services Offered</label>
              <input type="text" id="edit_services_offered" name="services_offered" placeholder="e.g. Plumbing, Electrical, Roofing" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Website <span class="text-gray-400">(optional)</span></label>
              <input type="url" id="edit_company_website" name="company_website" placeholder="https://" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Social Media <span class="text-gray-400">(optional)</span></label>
              <input type="url" id="edit_company_social_media" name="company_social_media" placeholder="https://" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
          </div>
        </div>

        <!-- Representative Information Section -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
            <i class="fi fi-rr-user"></i>
            Representative Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
              <input type="text" id="edit_first_name" name="first_name" placeholder="Enter first name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name <span class="text-gray-400">(optional)</span></label>
              <input type="text" id="edit_middle_name" name="middle_name" placeholder="Enter middle name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
              <input type="text" id="edit_last_name" name="last_name" placeholder="Enter last name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Email</label>
              <input type="email" id="edit_company_email" name="company_email" placeholder="Enter email address" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Account Setup Section -->
          <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
                <i class="fi fi-rr-user-gear"></i>
                Account Setup
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" id="edit_username" name="username" placeholder="Enter username" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password <span class="text-gray-400">(Optional)</span></label>
                        <input type="password" id="edit_password" name="password" placeholder="Enter new password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                        <p class="text-xs text-gray-500 mt-1">Leave blank if you don't want to change the password.</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
                <i class="fi fi-rr-map-marker"></i>
                Business Address
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                        <select id="edit_contractor_address_province" name="business_address_province" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                            <option value="">Select Province</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">{{ $province['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                        <select id="edit_contractor_address_city" name="business_address_city" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition" disabled>
                            <option value="">Select City/Municipality</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                        <select id="edit_contractor_address_barangay" name="business_address_barangay" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition" disabled>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Street Address / Unit No.</label>
                        <input type="text" id="edit_business_address_street" name="business_address_street" placeholder="Enter street address" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Zip Code</label>
                        <input type="text" id="edit_business_address_postal" name="business_address_postal" placeholder="Enter zip code" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                    </div>
                </div>
            </div>
          </div>
        </div>

        <!-- Documents Section -->
        <div class="mt-6">
          <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
            <i class="fi fi-rr-file-invoice"></i>
            Legal Documents
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Number <span class="text-red-500">*</span></label>
              <input type="text" id="edit_picab_number" name="picab_number" placeholder="Enter PCAB number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Category <span class="text-red-500">*</span></label>
              <select id="edit_picab_category" name="picab_category" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                <option value="">Select Category</option>
                @foreach($picabCategories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Expiration Date <span class="text-red-500">*</span></label>
              <input type="date" id="edit_picab_expiration_date" name="picab_expiration_date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit Number <span class="text-red-500">*</span></label>
              <input type="text" id="edit_business_permit_number" name="business_permit_number" placeholder="Enter permit number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit City <span class="text-red-500">*</span></label>
              <select id="edit_business_permit_city" name="business_permit_city" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                <option value="">Select City</option>
                @foreach($allCities as $city)
                    <option value="{{ $city['name'] }}">{{ $city['name'] }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit Expiration <span class="text-red-500">*</span></label>
              <input type="date" id="edit_business_permit_expiration" name="business_permit_expiration" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">TIN Business Registration Number <span class="text-red-500">*</span></label>
              <input type="text" id="edit_tin_business_reg_number" name="tin_business_reg_number" placeholder="Enter TIN/Business Reg. number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">DTI / SEC Registration <span class="text-gray-400">(Optional)</span></label>
              <div id="editDtiDropzone" class="flex items-center justify-center w-full h-[110px] rounded-xl border-2 border-dashed border-orange-300 bg-orange-50 text-orange-600 hover:bg-orange-100 transition-all relative cursor-pointer">
                <input id="editDtiUpload" name="dti_sec_registration_photo" type="file" accept="image/*,application/pdf" class="hidden">
                <div class="text-center pointer-events-none">
                  <i class="fi fi-rr-upload text-2xl"></i>
                  <div class="text-sm font-medium mt-1">Upload image or file</div>
                  <div id="editDtiFileName" class="text-xs text-orange-500 mt-1"></div>
                </div>
              </div>
              <div id="editCurrentDtiFile" class="mt-2 text-sm hidden">
                  Current: <a href="#" target="_blank" class="text-orange-600 hover:underline font-medium">View File</a>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
          <button id="cancelEditBtn" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
            Cancel
          </button>
          <button id="saveEditBtn" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg transform hover:scale-105">
            Save Changes
          </button>
        </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Contractor Modal -->
  <div id="deleteContractorModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Icon Section -->
      <div class="flex justify-center pt-8 pb-6">
        <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center relative">
          <div class="absolute inset-0 bg-red-200 rounded-full animate-ping opacity-75"></div>
          <div class="relative w-20 h-20 bg-red-500 rounded-full flex items-center justify-center">
            <i class="fi fi-rr-trash text-white text-3xl"></i>
          </div>
        </div>
      </div>

      <!-- Content Section -->
      <div class="px-8 pb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-3">Delete Contractor</h2>
        <p class="text-gray-600 leading-relaxed mb-4">
          Permanently delete <span class="font-bold text-gray-800" id="deleteContractorName">GTH Builders and Developers</span>? This action cannot be undone.
        </p>

        <div class="text-left">
            <label for="deletionReason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Deletion <span class="text-red-500">*</span></label>
            <textarea id="deletionReason" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-transparent transition resize-none" placeholder="Please provide a reason for deletion..."></textarea>
            <p id="deletionReasonError" class="text-red-500 text-xs mt-1 hidden">Reason is required.</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="px-8 pb-8 space-y-3">
        <button id="confirmDeleteBtn" class="w-full px-6 py-3.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-xl transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
          <i class="fi fi-rr-trash"></i>
          Delete
        </button>
        <button id="cancelDeleteBtn" class="w-full px-6 py-3.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-semibold hover:border-gray-400 hover:shadow-md transform hover:scale-[1.02] active:scale-95">
          Cancel
        </button>
      </div>
    </div>
  </div>


  <script src="{{ asset('js/admin/userManagement/contractor.js') }}" defer></script>

</body>

</html>
