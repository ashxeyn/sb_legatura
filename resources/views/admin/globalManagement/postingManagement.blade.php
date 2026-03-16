<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Posting Management - Legatura Admin</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('img/logo2.0-favicon.svg') }}">

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
        <div class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-3.5 mb-5 flex flex-wrap items-center gap-2.5">
          <div class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-600">
              <i class="fi fi-rr-filter text-[12px]"></i>
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
                class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none min-w-[150px] border-0">
                <option value="">All Statuses</option>
                <option value="under_review">Under Review</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="deleted">Deleted</option>
                <option value="due">Due</option>
              </select>
            </div>
          </div>

          <div class="ml-auto flex items-center gap-2">
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
      <div id="viewModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2 sm:p-3 transition-opacity duration-300">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full overflow-hidden flex flex-col transform transition-all duration-300 scale-95 modal-content" style="max-height:90vh;">
          <!-- Header -->
          <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3 rounded-t-xl flex-shrink-0">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center overflow-hidden ring-2 ring-white/30 text-white font-bold text-sm" id="modalAvatar">GD</div>
                <div class="text-white min-w-0">
                  <h3 class="text-sm font-bold truncate" id="modalName">Loading...</h3>
                  <p class="text-[10px] opacity-80 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span id="modalType">Loading...</span>
                  </p>
                </div>
              </div>
              <button class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white close-modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <style>.post-detail-scroll::-webkit-scrollbar{display:none}</style>
          <div class="post-detail-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">

            <!-- Quick Meta Cards -->
            <div class="grid grid-cols-2 gap-2">
              <div class="bg-blue-50 rounded-lg p-2.5 border border-blue-100">
                <div class="flex items-center justify-between mb-1">
                  <p class="text-[10px] text-gray-500 font-medium">Date Registered</p>
                  <div class="w-5 h-5 rounded bg-blue-100 flex items-center justify-center">
                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </div>
                </div>
                <p class="text-xs font-bold text-gray-900" id="modalDate">Loading...</p>
              </div>
              <div class="bg-indigo-50 rounded-lg p-2.5 border border-indigo-100">
                <div class="flex items-center justify-between mb-1">
                  <p class="text-[10px] text-gray-500 font-medium">Account Type</p>
                  <div class="w-5 h-5 rounded bg-indigo-100 flex items-center justify-center">
                    <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                </div>
                <p class="text-xs font-bold text-gray-900" id="modalAccountType">Loading...</p>
              </div>
            </div>

            <!-- Project Description -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-blue-500 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                Project Description
              </h3>
              <div class="px-1 pt-1">
                <p class="text-sm font-bold text-gray-900 mb-1" id="modalProjectTitle">Loading title...</p>
                <p class="text-[11px] text-gray-600 leading-relaxed" id="modalDescription">Loading...</p>
              </div>
            </div>

            <!-- Project Details -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-orange-500 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                </div>
                Project Details
              </h3>
              <div class="space-y-0.5 text-[11px]">
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                  <span class="text-gray-500">Location</span>
                  <span class="font-semibold text-gray-900 text-right" id="modalLocation">Loading...</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                  <span class="text-gray-500">Property Type</span>
                  <span class="font-semibold text-gray-900 text-right" id="modalPropertyType">Loading...</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                  <span class="text-gray-500">Budget Range</span>
                  <span class="font-semibold text-gray-900 text-right" id="modalBudget">Loading...</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                  <span class="text-gray-500">Lot Size</span>
                  <span class="font-semibold text-gray-900 text-right" id="modalLotSize">Loading...</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                  <span class="text-gray-500">Floor Area</span>
                  <span class="font-semibold text-gray-900 text-right" id="modalFloorArea">Loading...</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                  <span class="text-gray-500">Timeline</span>
                  <span class="font-semibold text-gray-900 text-right" id="modalTimeline">Loading...</span>
                </div>
              </div>
            </div>

            <!-- Post Files Section -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-indigo-500 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                  </svg>
                </div>
                Post Files
              </h3>
              <div class="pt-1">
                <div id="fileViewer" class="mb-2"></div>
                <div id="postFilesContainer">
                  <p class="text-[10px] text-gray-400 text-center py-3">Loading files...</p>
                </div>
              </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-teal-500 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                Contact Information
              </h3>
              <div class="space-y-0.5 text-[11px]">
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-teal-50">
                  <span class="text-gray-500">Email</span>
                  <span class="font-semibold text-gray-900 text-right" id="modalEmail">Loading...</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-teal-50">
                  <span class="text-gray-500">Phone</span>
                  <span class="font-semibold text-gray-900 text-right" id="modalPhone">Loading...</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-teal-50">
                  <span class="text-gray-500">Post Status</span>
                  <span class="font-semibold text-gray-900 text-right" id="modalPostStatus">Loading...</span>
                </div>
              </div>
            </div>

          </div>{{-- end scrollable body --}}

          <!-- Footer -->
          <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 rounded-b-xl flex justify-end items-center gap-2 flex-shrink-0">
            <button id="viewModalCloseBtn" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 transition-colors flex items-center gap-1.5 close-modal hidden">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Close
            </button>
            <button id="viewModalDeclineBtn" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
              </svg>
              Decline
            </button>
            <button id="viewModalApproveBtn" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Approve
            </button>
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

  <!-- Universal File Viewer (UFV) -->
  <div id="documentViewerModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] hidden items-center justify-center p-4">
    <div class="bg-[#1e1e2e] rounded-[1.25rem] shadow-[0_30px_90px_rgba(0,0,0,0.75)] max-w-5xl w-full h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 flex flex-col modal-shell">
      <!-- Header -->
      <div class="flex items-center justify-between px-5 py-3 bg-[#16162a] border-b border-white/5 gap-4">
        <div class="flex items-center gap-3 min-w-0">
          <i class="fi fi-rr-file-document text-orange-500 text-lg"></i>
          <h3 id="documentViewerTitle" class="text-sm font-semibold text-gray-200 truncate">Document Viewer</h3>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <a id="documentViewerDownload" href="#" download class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-gray-400 hover:bg-orange-500/40 hover:text-white transition-all" title="Download">
            <i class="fi fi-rr-download"></i>
          </a>
          <button id="closeDocumentViewerBtn" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-gray-400 hover:bg-red-500/40 hover:text-white transition-all" title="Close">
            <i class="fi fi-rr-cross text-sm"></i>
          </button>
        </div>
      </div>
      <!-- Viewport -->
      <div class="flex-1 bg-[#0d0d18] relative flex items-center justify-center overflow-hidden p-4">
        <img id="documentViewerImg" src="" alt="Document" class="max-w-full max-h-full object-contain hidden" />
        <iframe id="documentViewerFrame" src="" class="w-full h-full hidden border-0 bg-white rounded-lg"></iframe>
      </div>
    </div>
  </div>


  <script src="{{ asset('js/admin/reusables/filters.js') }}" defer></script>
  <script src="{{ asset('js/admin/globalManagement/postingManagement.js') }}" defer></script>

</body>

</html>