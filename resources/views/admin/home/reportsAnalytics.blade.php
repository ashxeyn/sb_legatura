<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/home/reportsAnalytics.css') }}">
  
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
  

  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

  
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'Reports and Analytics'])

      <div class="p-8 space-y-8">
        <!-- Export Reports Section -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-2xl font-bold text-gray-800 mb-1">Export Reports</h2>
              <p class="text-sm text-gray-500">Generate and download comprehensive reports</p>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-sm text-gray-600 font-medium">Period:</span>
              <select class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white">
                <option>Last 30 days</option>
                <option>Last 60 days</option>
                <option>Last 90 days</option>
                <option>This Quarter</option>
                <option>This Year</option>
                <option>Custom Range</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- PDF Report Card -->
            <div class="border-2 border-gray-200 rounded-2xl p-6 hover:border-red-300 hover:shadow-xl transition-all duration-300 group">
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-red-100 p-3 rounded-xl group-hover:bg-red-200 transition-colors duration-300">
                  <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-bold text-gray-800 text-lg">PDF Report</h3>
                  <p class="text-xs text-gray-500">Formatted document</p>
                </div>
              </div>

              <div class="space-y-2 mb-6">
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500" checked>
                  <span>Property Owner Data</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500" checked>
                  <span>Contractor Analytics</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                  <span>Project Statistics</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                  <span>Financial Summary</span>
                </label>
              </div>

              <button class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg export-btn" data-type="pdf">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export PDF</span>
              </button>
            </div>

            <!-- Excel Report Card -->
            <div class="border-2 border-gray-200 rounded-2xl p-6 hover:border-green-300 hover:shadow-xl transition-all duration-300 group">
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-green-100 p-3 rounded-xl group-hover:bg-green-200 transition-colors duration-300">
                  <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-bold text-gray-800 text-lg">Excel Report</h3>
                  <p class="text-xs text-gray-500">Spreadsheet format</p>
                </div>
              </div>

              <div class="space-y-2 mb-6">
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500" checked>
                  <span>Raw Data Tables</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500" checked>
                  <span>Pivot Tables</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                  <span>Charts & Graphs</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                  <span>Calculated Fields</span>
                </label>
              </div>

              <button class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg export-btn" data-type="excel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export Excel</span>
              </button>
            </div>

            <!-- CSV Report Card -->
            <div class="border-2 border-gray-200 rounded-2xl p-6 hover:border-blue-300 hover:shadow-xl transition-all duration-300 group">
              <div class="flex items-center gap-3 mb-4">
                <div class="bg-blue-100 p-3 rounded-xl group-hover:bg-blue-200 transition-colors duration-300">
                  <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4zm7 5a1 1 0 10-2 0v1H8a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="font-bold text-gray-800 text-lg">CSV Report</h3>
                  <p class="text-xs text-gray-500">Spreadsheet format</p>
                </div>
              </div>

              <div class="space-y-2 mb-6">
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                  <span>User Data Export</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                  <span>Transaction Records</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                  <span>Activity Logs</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                  <input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                  <span>Performance Metrics</span>
                </label>
              </div>

              <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg export-btn" data-type="csv">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export CSV</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Detailed Report Configuration -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-1">Detailed Report Configuration</h2>
            <p class="text-sm text-gray-500">Customize your report with advanced filters and metrics</p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Report Filters Section -->
            <div>
              <h3 class="text-lg font-bold text-gray-800 mb-4">Report Filters</h3>
              
              <!-- Date Range -->
              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Date Range</label>
                <div class="grid grid-cols-2 gap-3">
                  <input type="date" class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full text-sm" placeholder="Start Date">
                  <input type="date" class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full text-sm" placeholder="End Date">
                </div>
              </div>

              <!-- User Type -->
              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">User Type</label>
                <select class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white w-full text-sm">
                  <option value="">Contractor Type</option>
                  <option value="property-owner">Property Owner</option>
                  <option value="contractor">Contractor</option>
                  <option value="premium">Premium Tier</option>
                  <option value="standard">Standard Tier</option>
                  <option value="all">All Users</option>
                </select>
              </div>

              <!-- Location -->
              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Location</label>
                <select class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white w-full text-sm">
                  <option value="">All Location</option>
                  <option value="tetuan">Tetuan</option>
                  <option value="tumaga">Tumaga</option>
                  <option value="sinunuc">Sinunuc</option>
                  <option value="malagutay">Malagutay</option>
                  <option value="baliwasan">Baliwasan</option>
                  <option value="upper-calarian">Upper Calarian</option>
                  <option value="others">Others</option>
                </select>
              </div>

              <!-- Project Status -->
              <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Project Status</label>
                <select class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-400 focus:outline-none bg-white w-full text-sm">
                  <option value="">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="completed">Completed</option>
                  <option value="pending">Pending</option>
                  <option value="under-review">Under Review</option>
                  <option value="cancelled">Cancelled</option>
                  <option value="on-hold">On Hold</option>
                </select>
              </div>
            </div>

            <!-- Include Metrics Section -->
            <div>
              <h3 class="text-lg font-bold text-gray-800 mb-4">Include Metrics</h3>
              
              <div class="space-y-3">
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" checked>
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">AI Recommendation Performance</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" checked>
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Bid Acceptance Rates</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Project Completion Times</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Payment Processing Data</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">User Engagement Metrics</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Location-based Analytics</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Milestone Verification Rates</span>
                  </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors duration-200 group">
                  <input type="checkbox" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">Contractor Rating Trends</span>
                  </div>
                </label>
              </div>
            </div>
          </div>

          <!-- Export Options -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Export Options</h3>
            <div class="flex items-center gap-4">
              <button class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg detailed-export-btn" data-type="pdf">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export PDF</span>
              </button>
              <button class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg detailed-export-btn" data-type="excel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export Excel</span>
              </button>
              <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg detailed-export-btn" data-type="csv">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span>Export CSV</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Analytics Summary -->
  

  <script src="{{ asset('js/admin/home/reportsAnalytics.js') }}" defer></script>

</body>

</html>
