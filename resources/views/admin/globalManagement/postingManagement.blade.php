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
  <link rel="stylesheet" href="{{ asset('css/admin/globalManagement/postingManagement.css') }}">
  
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
            @include('admin.layouts.topnav', ['pageTitle' => 'Posting Management'])

      <div class="p-8 space-y-6">
        <!-- Filters Section -->
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

            <select class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 focus:outline-none bg-white font-medium text-gray-700" id="statusFilter">
                <option value="">Status</option>
                <option value="under_review">Under Review</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="deleted">Deleted</option>
                <option value="due">Due</option>
            </select>
          </div>

          <div class="flex items-center gap-4">
            <button id="resetFilters" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                <i class="fi fi-rr-rotate-left"></i>
                <span>Reset Filter</span>
            </button>
          </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
          
          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Owner Name</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Post Title</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Posted</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200" id="ownersTableWrap">
                @include('admin.globalManagement.partials.postManagementTable')
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- View Modal -->
      <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 {{ $postDetails ? 'flex' : 'hidden' }} items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300 scale-95 modal-content">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-5 flex items-center justify-between rounded-t-2xl">
            <h3 class="text-xl font-bold text-white">Post Details</h3>
            <a href="{{ route('admin.globalManagement.postingManagement', request()->except('view')) }}" class="text-white hover:text-gray-200 transition-colors duration-200">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </a>
          </div>
          <div class="p-6 space-y-5">
            <!-- Header Info -->
            <div class="flex items-center gap-4 pb-4 border-b">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-lg overflow-hidden" id="modalAvatar">
                @if($postDetails && $postDetails['owner']['profile_pic'])
                  <img src="{{ asset('storage/' . $postDetails['owner']['profile_pic']) }}" alt="Profile" class="w-full h-full object-cover rounded-full">
                @elseif($postDetails)
                  {{ strtoupper(substr($postDetails['owner']['name'], 0, 2)) }}
                @else
                  GD
                @endif
              </div>
              <div>
                <h4 class="text-xl font-bold text-gray-800" id="modalName">{{ $postDetails['owner']['name'] ?? 'Loading...' }}</h4>
                <div class="flex items-center gap-2">
                  <p class="text-sm text-gray-500" id="modalType">{{ $postDetails['owner']['username'] ?? 'Loading...' }}</p>
                </div>
              </div>
            </div>

            <!-- Quick meta -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600 mb-1">Date Registered</p>
                <p class="font-semibold text-gray-800" id="modalDate">
                  @if($postDetails && $postDetails['owner']['registered_at'])
                    {{ \Carbon\Carbon::parse($postDetails['owner']['registered_at'])->format('d M, Y') }}
                  @else
                    Loading...
                  @endif
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Account Type</p>
                <p class="font-semibold text-gray-800" id="modalAccountType">{{ $postDetails['owner']['type'] ?? 'Loading...' }}</p>
              </div>
            </div>

            <!-- Project Description -->
            <div class="bg-gray-50 rounded-xl p-4">
              <h5 class="text-sm font-semibold text-gray-700 mb-2">Project Description</h5>
              <p class="text-lg font-bold text-gray-800 mb-2" id="modalProjectTitle">{{ $postDetails['project']['title'] ?? 'Loading title...' }}</p>
              <p class="text-sm text-gray-700" id="modalDescription">{{ $postDetails['project']['description'] ?? 'Loading...' }}</p>
            </div>

            <!-- Project Details -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-600 mb-1">Location</p>
                <p class="font-semibold text-gray-800" id="modalLocation">{{ $postDetails['project']['project_location'] ?? 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Property Type</p>
                <p class="font-semibold text-gray-800" id="modalPropertyType">{{ $postDetails['project']['property_type'] ?? 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Budget Range</p>
                <p class="font-semibold text-gray-800" id="modalBudget">
                  @if($postDetails && $postDetails['project']['budget_range_min'] && $postDetails['project']['budget_range_max'])
                    ₱{{ number_format($postDetails['project']['budget_range_min'], 2) }} - ₱{{ number_format($postDetails['project']['budget_range_max'], 2) }}
                  @else
                    N/A
                  @endif
                </p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Lot Size</p>
                <p class="font-semibold text-gray-800" id="modalLotSize">{{ $postDetails && $postDetails['project']['lot_size'] ? $postDetails['project']['lot_size'] . ' sqm' : 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Floor Area</p>
                <p class="font-semibold text-gray-800" id="modalFloorArea">{{ $postDetails && $postDetails['project']['floor_area'] ? $postDetails['project']['floor_area'] . ' sqm' : 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-600 mb-1">Timeline</p>
                <p class="font-semibold text-gray-800" id="modalTimeline">{{ $postDetails['project']['to_finish'] ?? 'N/A' }}</p>
              </div>
            </div>

            <!-- Post Files Section -->
            <div class="rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-3 border-b border-gray-200">
                <h5 class="font-semibold text-gray-900 flex items-center gap-2">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                  </svg>
                  Post Files
                </h5>
              </div>
              <div class="px-5 py-4">
                <div id="postFilesContainer">
                  @if($postDetails && count($postDetails['files']) > 0)
                    <div class="grid grid-cols-2 gap-3">
                      @foreach($postDetails['files'] as $file)
                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="flex items-center gap-2 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                          </svg>
                          <span class="text-sm text-gray-700 truncate">{{ $file->file_name }}</span>
                        </a>
                      @endforeach
                    </div>
                  @else
                    <p class="text-sm text-gray-500 text-center py-4">No files attached</p>
                  @endif
                </div>  
              </div>
            </div>

            <!-- Additional info -->
            <div class="grid grid-cols-1 gap-4">
              <div>
                <p class="text-sm text-gray-600 mb-1">Email</p>
                <p class="font-semibold text-gray-800" id="modalEmail">{{ $postDetails['owner']['email'] ?? 'N/A' }}</p>
              </div>
              <div class="col-span-1">
                <p class="text-sm text-gray-600 mb-1">Post Status</p>
                <p class="font-semibold text-gray-800" id="modalPostStatus">{{ ucfirst(str_replace('_', ' ', $postDetails['project']['status'] ?? 'N/A')) }}</p>
              </div>
            </div>
          </div>
          <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
            @if($postDetails && $postDetails['project']['status'] === 'under_review')
              <button id="viewModalDeclineBtn" data-project-id="{{ $postDetails['project']['id'] }}" data-name="{{ $postDetails['owner']['name'] }}" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200">Decline</button>
              <button id="viewModalApproveBtn" data-project-id="{{ $postDetails['project']['id'] }}" data-name="{{ $postDetails['owner']['name'] }}" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors duration-200">Approve</button>
            @else
              <a href="{{ route('admin.globalManagement.postingManagement', request()->except('view')) }}" id="viewModalCloseBtn" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-200">Close</a>
            @endif
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
                Cancel
              </span>
            </button>
            <button class="px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold rounded-xl transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl" id="confirmDecline">
              <span class="flex items-center gap-2">
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


  <script src="{{ asset('js/admin/reusables/filters.js') }}" defer></script>
  <script src="{{ asset('js/admin/globalManagement/postingManagement.js') }}" defer></script>

</body>

</html>