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

  <style>
    .date-pill input[type="date"]::-webkit-calendar-picker-indicator {
      opacity: 0.5;
      cursor: pointer;
      filter: invert(30%) sepia(80%) saturate(400%) hue-rotate(210deg);
    }

    .date-pill input[type="date"]::-webkit-calendar-picker-indicator:hover {
      opacity: 1;
    }

    .modal-scroll-hidden {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .modal-scroll-hidden::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }
  </style>

  
</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

        @include('admin.layouts.sidebar')

    <main class="flex-1">
            @include('admin.layouts.topnav', ['pageTitle' => 'Posting Management'])

      <div class="p-8 space-y-6">
        <!-- Filters Section -->
        <div class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
              <i class="fi fi-rr-filter text-gray-500"></i>
              <span>Filter By</span>
            </div>

            <!-- Date Range -->
            <div class="flex flex-wrap items-center gap-2">
              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                  <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">From</span>
                </div>
                <input type="date" id="dateFrom"
                  class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
              </div>

              <span class="text-gray-300 font-bold text-lg">→</span>

              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                  <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
                </div>
                <input type="date" id="dateTo"
                  class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
              </div>
            </div>

            <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
              <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                <i class="fi fi-rr-filter text-white text-sm leading-none"></i>
                <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">Status</span>
              </div>
              <select id="statusFilter"
                class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none min-w-[170px] border-0">
                <option value="">All Statuses</option>
                <option value="under_review">Under Review</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="deleted">Deleted</option>
                <option value="due">Due</option>
              </select>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button id="resetFilters" class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                <i class="fi fi-rr-rotate-left"></i>
                <span>Reset Filter</span>
            </button>
          </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" id="ownersTableWrap">
          @include('admin.globalManagement.partials.postManagementTable')
        </div>

      </div>

      <!-- View Modal -->
      <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-2 sm:p-3">
        <div class="bg-white rounded-xl shadow-2xl max-w-xl w-full max-h-[84vh] overflow-hidden flex flex-col transform transition-all duration-300 scale-95 modal-content">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 sm:px-5 py-3 sm:py-3.5 flex items-center justify-between rounded-t-xl sticky top-0 z-10">
            <h3 class="text-base sm:text-lg font-bold text-white">Post Details</h3>
            <button class="text-white hover:text-gray-200 transition-colors duration-200 close-modal p-1.5 rounded-md hover:bg-white hover:bg-opacity-20">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="p-4 sm:p-5 space-y-3.5 flex-1 min-h-0 overflow-y-auto modal-scroll-hidden">
            <!-- Header Info -->
            <div class="flex items-center gap-3 pb-3 border-b">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg overflow-hidden" id="modalAvatar">GD</div>
              <div>
                <h4 class="text-base sm:text-lg font-bold text-gray-800" id="modalName">Loading...</h4>
                <div class="flex items-center gap-2">
                  <p class="text-xs text-gray-500" id="modalType">Loading...</p>
                </div>
              </div>
            </div>

            <!-- Quick meta -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <p class="text-xs text-gray-600 mb-1">Date Registered</p>
                <p class="text-sm font-semibold text-gray-800" id="modalDate">Loading...</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-1">Account Type</p>
                <p class="text-sm font-semibold text-gray-800" id="modalAccountType">Loading...</p>
              </div>
            </div>

            <!-- Project Description -->
            <div class="bg-gray-50 rounded-xl p-3">
              <h5 class="text-xs font-semibold text-gray-700 mb-1.5">Project Description</h5>
              <p class="text-base font-bold text-gray-800 mb-1.5" id="modalProjectTitle">Loading title...</p>
              <p class="text-xs text-gray-700" id="modalDescription">Loading...</p>
            </div>

            <!-- Project Details -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <p class="text-xs text-gray-600 mb-1">Location</p>
                <p class="text-sm font-semibold text-gray-800" id="modalLocation">Loading...</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-1">Property Type</p>
                <p class="text-sm font-semibold text-gray-800" id="modalPropertyType">Loading...</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-1">Budget Range</p>
                <p class="text-sm font-semibold text-gray-800" id="modalBudget">Loading...</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-1">Lot Size</p>
                <p class="text-sm font-semibold text-gray-800" id="modalLotSize">Loading...</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-1">Floor Area</p>
                <p class="text-sm font-semibold text-gray-800" id="modalFloorArea">Loading...</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-1">Timeline</p>
                <p class="text-sm font-semibold text-gray-800" id="modalTimeline">Loading...</p>
              </div>
            </div>

            <!-- Post Files Section -->
            <div class="rounded-xl border border-gray-200 shadow-sm overflow-hidden">
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-2.5 border-b border-gray-200">
                <h5 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                  </svg>
                  Post Files
                </h5>
              </div>
              <div class="px-4 py-3">
                <div id="fileViewer" class="mb-3"></div>
                <div id="postFilesContainer">
                  <p class="text-xs text-gray-500 text-center py-3">Loading files...</p>
                </div>
              </div>
            </div>

            <!-- Additional info -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <p class="text-xs text-gray-600 mb-1">Email</p>
                <p class="text-sm font-semibold text-gray-800" id="modalEmail">Loading...</p>
              </div>
              <div>
                <p class="text-xs text-gray-600 mb-1">Phone</p>
                <p class="text-sm font-semibold text-gray-800" id="modalPhone">Loading...</p>
              </div>
              <div class="col-span-2">
                <p class="text-xs text-gray-600 mb-1">Post Status</p>
                <p class="text-sm font-semibold text-gray-800" id="modalPostStatus">Loading...</p>
              </div>
            </div>
          </div>
          <div class="px-4 sm:px-5 py-3 bg-gray-50 rounded-b-xl flex justify-end gap-2 sticky bottom-0 z-10 border-t border-gray-200">
            <button id="viewModalCloseBtn" class="px-3.5 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-semibold rounded-lg transition-colors duration-200 close-modal hidden">Close</button>
            <button id="viewModalDeclineBtn" class="px-3.5 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors duration-200">Decline</button>
            <button id="viewModalApproveBtn" class="px-3.5 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg transition-colors duration-200">Approve</button>
          </div>
        </div>
      </div>

      <!-- Approve Modal -->
      <div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2 sm:p-3">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[84vh] overflow-y-auto modal-scroll-hidden transform transition-all duration-300 scale-95 modal-content">
          <!-- Header -->
          <div class="bg-gradient-to-r from-green-500 via-green-600 to-emerald-600 px-4 sm:px-5 py-3.5 sm:py-4 flex items-center justify-between rounded-t-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-white opacity-10">
              <div class="absolute top-0 left-0 w-32 h-32 bg-white rounded-full blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
              <div class="absolute bottom-0 right-0 w-32 h-32 bg-white rounded-full blur-3xl opacity-20 translate-x-1/2 translate-y-1/2"></div>
            </div>
            <div class="relative flex items-center gap-3">
              <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-bold text-white">Approve Post</h3>
            </div>
            <button class="relative text-white hover:bg-white hover:bg-opacity-20 rounded-full p-1.5 transition-all duration-200 close-modal">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- Content -->
          <div class="p-4 sm:p-5">
            <!-- Icon with animation -->
            <div class="flex items-center justify-center mb-4">
              <div class="relative">
                <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center shadow-lg approve-icon-container">
                  <svg class="w-8 h-8 text-green-600 approve-checkmark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
                <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-20"></div>
              </div>
            </div>
            
            <!-- Message -->
            <div class="text-center mb-4">
              <h4 class="text-lg sm:text-xl font-bold text-gray-800 mb-1.5">Confirm Approval</h4>
              <p class="text-sm text-gray-600 mb-3">You are about to approve this post submission</p>
              <div class="bg-green-50 border-2 border-green-200 rounded-xl p-3 mb-3">
                <p class="text-xs text-green-800 font-medium mb-1">Account Name</p>
                <p class="text-base font-bold text-green-900" id="approveModalName">GTH Builders and Developers</p>
              </div>
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-xs text-blue-800 text-left">Once approved, the user will receive a confirmation, and their post will be published.</p>
              </div>
            </div>
          </div>
          
          <!-- Footer Actions -->
          <div class="px-4 sm:px-5 py-3.5 bg-gradient-to-b from-gray-50 to-gray-100 rounded-b-2xl flex justify-end gap-2">
            <button class="px-4 py-2 bg-white border-2 border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition-all duration-200 hover:scale-105 close-modal">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
              </span>
            </button>
            <button class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl" id="confirmApprove">
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
      <div id="declineModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2 sm:p-3">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[84vh] overflow-y-auto modal-scroll-hidden transform transition-all duration-300 scale-95 modal-content">
          <!-- Header -->
          <div class="bg-gradient-to-r from-red-500 via-red-600 to-rose-600 px-4 sm:px-5 py-3.5 sm:py-4 flex items-center justify-between rounded-t-2xl relative overflow-hidden">
            <div class="absolute inset-0 bg-white opacity-10">
              <div class="absolute top-0 left-0 w-32 h-32 bg-white rounded-full blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
              <div class="absolute bottom-0 right-0 w-32 h-32 bg-white rounded-full blur-3xl opacity-20 translate-x-1/2 translate-y-1/2"></div>
            </div>
            <div class="relative flex items-center gap-3">
              <h3 class="text-lg font-bold text-white">Decline Post</h3>
            </div>
            <button class="relative text-white hover:bg-white hover:bg-opacity-20 rounded-full p-1.5 transition-all duration-200 close-modal">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- Content -->
          <div class="p-4 sm:p-5">
            <!-- Icon with animation -->
            <div class="flex items-center justify-center mb-4">
              <div class="relative">
                <div class="w-16 h-16 bg-gradient-to-br from-rose-100 to-red-100 rounded-full flex items-center justify-center shadow-lg decline-icon-container">
                  <svg class="w-8 h-8 text-red-600 decline-x" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </div>
                <div class="absolute inset-0 bg-red-400 rounded-full animate-ping opacity-20"></div>
              </div>
            </div>
            
            <!-- Message -->
            <div class="text-center mb-4">
              <h4 class="text-lg sm:text-xl font-bold text-gray-800 mb-1.5">Confirm Decline</h4>
              <p class="text-sm text-gray-600 mb-3">You are about to decline this post submission</p>
              <div class="bg-red-50 border-2 border-red-200 rounded-xl p-3 mb-3">
                <p class="text-xs text-red-800 font-medium mb-1">Account Name</p>
                <p class="text-base font-bold text-red-900" id="declineModalName">GTH Builders and Developers</p>
              </div>
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-2 flex items-start gap-2.5 mb-3">
                <svg class="w-4 h-4 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-xs text-yellow-800 text-left">Declining will notify the user via email with your provided reason.</p>
              </div>
              <div class="text-left">
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Reason for Decline <span class="text-red-500">*</span></label>
                <textarea id="declineReason" rows="3" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 focus:outline-none resize-none" placeholder="Please provide a brief, clear reason for declining this post..."></textarea>
                <p class="text-xs text-gray-500 mt-2">This reason will be included in the email notification.</p>
              </div>
            </div>
          </div>
          
          <!-- Footer Actions -->
          <div class="px-4 sm:px-5 py-3.5 bg-gradient-to-b from-gray-50 to-gray-100 rounded-b-2xl flex justify-end gap-2">
            <button class="px-4 py-2 bg-white border-2 border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition-all duration-200 hover:scale-105 close-modal">
              <span class="flex items-center gap-2">
                Cancel
              </span>
            </button>
            <button class="px-4 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white text-sm font-semibold rounded-xl transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl" id="confirmDecline">
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