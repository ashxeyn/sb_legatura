<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/userManagement/propertyOwner.css') }}">
  
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
              id="propertyOwnerSearchInput"
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
        <!-- Controls Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex items-center justify-between gap-4">
          <!-- Left Side - Dropdowns -->
          <div class="flex items-center gap-4">
            <!-- Ranking Dropdown -->
              <div class="relative">
              <select id="propertyOwnerRankingFilter" class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm font-medium text-gray-700 hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition cursor-pointer">
                <option value="ranking">Ranking</option>
                <option value="name">Name</option>
                <option value="projects">Projects</option>
                <option value="date">Date Registered</option>
              </select>
              <i class="fi fi-rr-angle-small-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 pointer-events-none"></i>
            </div>

            <!-- Time Period Dropdown -->
            <div class="relative">
              <button id="periodBtn" class="flex items-center gap-2 bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                <i class="fi fi-rr-calendar text-gray-500"></i>
                <span id="periodText">This Month</span>
                <i class="fi fi-rr-angle-small-down text-gray-500"></i>
              </button>
              <div id="periodDropdown" class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50 hidden">
                <button class="period-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">This Week</button>
                <button class="period-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">This Month</button>
                <button class="period-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">This Year</button>
                <button class="period-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">All Time</button>
              </div>
            </div>
          </div>

          <!-- Right Side - Add Button -->
          <button id="addPropertyOwnerBtn" class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-5 py-2 rounded-lg font-medium text-sm shadow-md hover:shadow-lg transition transform hover:scale-105">
            <i class="fi fi-rr-plus text-lg"></i>
            <span>Add Property Owner</span>
          </button>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Registered</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Occupation</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Number of<br>Project Posted</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Ongoing<br>Projects</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200" id="propertyOwnersTable">
                <!-- Data loaded from API -->
              </tbody>
            </table>
          </div>
          <div id="propertyOwnersPagination" class="px-4 py-3 border-t border-gray-100">
            <!-- Pagination rendered by JS -->
          </div>
        </div>
      </div>

    </main>
  </div>

  <!-- Add Property Owner Modal -->
  <div id="addPropertyOwnerModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-8 py-5 flex items-center justify-between rounded-t-2xl z-10">
        <h2 class="text-2xl font-bold text-gray-800">Add New Property Owner</h2>
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
              <i class="fi fi-rr-user text-4xl text-gray-500" id="profileIcon"></i>
              <img id="profilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
            </div>
            <label for="profileUpload" class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition transform hover:scale-110">
              <i class="fi fi-rr-pencil text-sm"></i>
              <input type="file" id="profileUpload" class="hidden" accept="image/*">
            </label>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Profile Picture</h3>
            <p class="text-sm text-gray-500">Upload a profile photo for the property owner</p>
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
              <label class="block text-sm font-medium text-gray-700 mb-2">First name</label>
              <input type="text" placeholder="Enter first name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
              <input type="text" placeholder="Enter occupation" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Middle name <span class="text-gray-400">(optional)</span></label>
              <input type="text" placeholder="Enter middle name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date of birth</label>
              <input type="date" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Last name</label>
              <input type="text" placeholder="Enter last name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Contact no.</label>
              <input type="tel" placeholder="Enter contact number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Account Setup Section -->
          <div>
            <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
              <i class="fi fi-rr-user-gear"></i>
              Account Setup
            </h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input type="text" placeholder="Enter username" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" placeholder="Enter email address" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <div class="relative">
                  <input type="password" id="passwordInput" placeholder="Enter password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition pr-10">
                  <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                    <i class="fi fi-rr-eye" id="eyeIcon"></i>
                  </button>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                <div class="relative">
                  <input type="password" id="confirmPasswordInput" placeholder="Confirm password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition pr-10">
                  <button type="button" id="toggleConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                    <i class="fi fi-rr-eye" id="eyeIconConfirm"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Business Address Section -->
          <div>
            <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
              <i class="fi fi-rr-marker"></i>
              Business Address
            </h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type of Valid ID</label>
                <select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                  <option value="">Select ID type</option>
                  <option value="passport">Passport</option>
                  <option value="drivers_license">Driver's License</option>
                  <option value="national_id">National ID</option>
                  <option value="sss">SSS ID</option>
                  <option value="umid">UMID</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Valid ID Number</label>
                <input type="text" placeholder="Enter ID number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Valid ID</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition cursor-pointer bg-gray-50 hover:bg-orange-50" id="idUploadArea">
                  <input type="file" id="idUpload" class="hidden" accept="image/*">
                  <i class="fi fi-rr-cloud-upload text-3xl text-gray-400 mb-2"></i>
                  <p class="text-sm text-gray-600 font-medium">Upload Image of file</p>
                  <p class="text-xs text-gray-400 mt-1">Click or drag file to upload</p>
                  <div id="idFileName" class="text-sm text-orange-500 mt-2 hidden font-medium"></div>
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
            Save Property Owner
          </button>
        </div>
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
              <span class="text-3xl text-white font-bold" id="editProfileInitials">CR</span>
              <img id="editProfilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
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
              <input type="text" id="editFirstName" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Middle Name <span class="text-gray-400 font-normal">(optional)</span></label>
              <input type="text" id="editMiddleName" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
              <input type="text" id="editLastName" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Date of Birth</label>
              <div class="relative">
                <input type="date" id="editDateOfBirth" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
                <i class="fi fi-rr-calendar absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
              </div>
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number</label>
              <div class="relative">
                <i class="fi fi-rr-phone-call absolute left-4 top-1/2 -translate-y-1/2 text-orange-500"></i>
                <input type="tel" id="editContactNumber" class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
              </div>
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Occupation</label>
              <input type="text" id="editOccupation" class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
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
                <input type="email" id="editEmail" class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
              </div>
            </div>
            <div class="form-group">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
              <div class="relative">
                <i class="fi fi-rr-user absolute left-4 top-1/2 -translate-y-1/2 text-orange-500"></i>
                <input type="text" id="editUsername" class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-orange-400 transition-all hover:border-orange-300 bg-white">
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

  <!-- Delete User Modal -->
  <div id="deleteUserModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
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
        <h2 class="text-2xl font-bold text-gray-800 mb-3">Delete User</h2>
        <p class="text-gray-600 leading-relaxed">
          Permanently delete <span class="font-bold text-gray-800" id="deleteUserName">Olivia Faith</span>? This action cannot be undone.
        </p>
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

  <script src="{{ asset('js/admin/userManagement/propertyOwner.js') }}" defer></script>

</body>

</html>