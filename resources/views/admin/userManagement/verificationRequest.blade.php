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

  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    .date-pill input[type="date"]::-webkit-calendar-picker-indicator {
      opacity: 0.5;
      cursor: pointer;
      filter: invert(30%) sepia(80%) saturate(400%) hue-rotate(210deg);
    }

    .date-pill input[type="date"]::-webkit-calendar-picker-indicator:hover {
      opacity: 1;
    }

    #contractorVerificationModal .contractor-verification-modal-scroll {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    #contractorVerificationModal .contractor-verification-modal-scroll::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }

    #ownerVerificationModal .owner-verification-modal-scroll {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    #ownerVerificationModal .owner-verification-modal-scroll::-webkit-scrollbar {
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
      @include('admin.layouts.topnav', ['pageTitle' => 'Verification Request'])
      <div class="p-8 space-y-6 max-w-7xl mx-auto">
        <!-- Filters Bar -->
        <div class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
              <i class="fi fi-rr-filter text-gray-500"></i>
              <span>Filter By</span>
            </div>

            <!-- Date Range -->
            <div class="flex flex-wrap items-center gap-2">
              <!-- From -->
              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                  <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">From</span>
                </div>
                <input type="date" id="dateFrom"
                  class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
              </div>

              <span class="text-gray-300 font-bold text-lg">→</span>

              <!-- To -->
              <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                  <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                  <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
                </div>
                <input type="date" id="dateTo"
                  class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
              </div>
            </div>
          </div>

          <button id="resetFilterBtn"
            class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
            <i class="fi fi-rr-rotate-left"></i>
            <span>Reset Filter</span>
          </button>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="border-b border-gray-200">
            <div class="flex px-6">
              <button id="tabContractors"
                class="verification-tab active px-4 py-3 text-sm font-semibold border-b-2 border-orange-500 text-orange-600 transition-all">Contractors</button>
              <button id="tabOwners"
                class="verification-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-orange-600 hover:border-orange-300 transition-all">Property
                Owners</button>
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
      <div id="contractorVerificationModal"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-40 hidden items-center justify-center p-2 sm:p-3">
        <div
          class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-panel">
          <!-- Header -->
          <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-3 sm:px-4 py-2.5 flex items-center justify-between rounded-t-xl shadow-lg z-10">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <i class="fi fi-rr-building text-white text-xs"></i>
              </div>
              <h2 class="text-sm sm:text-base font-bold text-white">Verification Details</h2>
            </div>
            <button id="vrCloseBtn"
              class="text-white hover:text-orange-100 transition-all p-1 rounded-md hover:bg-white hover:bg-opacity-20 active:scale-95">
              <i class="fi fi-rr-cross text-base"></i>
            </button>
          </div>

          <!-- Body -->
          <div class="overflow-y-auto max-h-[calc(84vh-104px)] p-3 sm:p-4 space-y-3 contractor-verification-modal-scroll">
            <!-- Top Grid: Profile & Owner Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <!-- Profile Card -->
              <div class="bg-white rounded-xl border border-gray-200 p-3">
                <div class="flex items-start gap-3">
                  <div
                    class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow">
                    <span id="vrCompanyInitials">PC</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 id="vrCompanyName" class="text-sm font-bold text-gray-800 truncate">Panda Construction Company
                    </h3>
                    <p id="vrCompanyContact" class="text-[11px] text-gray-600 truncate">pandaconstruction@domain.com • +63
                      934 567 8912</p>
                  </div>
                </div>
              </div>

              <!-- Owner Details Card -->
              <div class="bg-white rounded-xl border border-gray-200 p-3">
                <div class="flex items-start gap-3">
                  <div
                    class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow">
                    <span id="vrOwnerInitials">OW</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 id="vrOwnerName" class="text-sm font-bold text-gray-800 truncate">Owner Name</h3>
                    <p class="text-[10px] text-gray-500 mb-1.5">Owner</p>
                    <div class="flex items-start gap-1.5">
                      <i class="fi fi-rr-marker text-blue-500 text-[11px] mt-0.5 flex-shrink-0"></i>
                      <p id="vrOwnerAddress" class="text-[11px] text-gray-600 line-clamp-2">Business Address</p>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <!-- Personal Information -->
              <div class="bg-white rounded-xl border border-gray-200 p-3">
                <h4 class="text-xs font-semibold text-gray-800 mb-2 flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                  Personal information
                </h4>
                <div class="space-y-2">
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Contractor Type</label>
                    <div id="vrContractorType"
                      class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-800">
                      General Contractor</div>
                  </div>
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Years of Operation</label>
                    <div id="vrYears"
                      class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-800">1971
                    </div>
                  </div>
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Services offered</label>
                    <div id="vrServices"
                      class="w-full px-2.5 py-2 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-700 leading-relaxed">
                      Project planning, construction management, material procurement, subcontractor coordination,
                      renovations, and quality supervision for residential and commercial projects.
                    </div>
                  </div>
                </div>
              </div>

              <!-- Business Accreditation & Compliance -->
              <div class="bg-white rounded-xl border border-gray-200 p-3">
                <h4 class="text-xs font-semibold text-orange-600 mb-2 flex items-center gap-1.5">
                  <i class="fi fi-rr-badge-check"></i>
                  Business Accreditation & Compliance
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">PCAB Number</label>
                    <div id="vrPcabNo"
                      class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-800">
                      12345-AB-2025</div>
                  </div>
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Business Permit Expiration</label>
                    <div id="vrBpExp"
                      class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-800">
                      December 31, 2025</div>
                  </div>
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">PCAB Category</label>
                    <div id="vrPcabCategory"
                      class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-800">
                      Category B</div>
                  </div>
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">TIN Business Registration No.</label>
                    <div id="vrTin"
                      class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-800">
                      123-456-789-000</div>
                  </div>
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">PCAB Expiration Date</label>
                    <div id="vrPcabExp"
                      class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-800">
                      August 15, 2026</div>
                  </div>
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">PCAB Number</label>
                    <div id="vrPcabNo2"
                      class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-800">
                      12345-AB-2025</div>
                  </div>
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Business Permit No.</label>
                    <div id="vrBpNo"
                      class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-800">
                      BP-2025-0987</div>
                  </div>
                  <div>
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">Business Permit City</label>
                    <div id="vrBpCity"
                      class="w-full px-2.5 py-1.5 border border-gray-200 rounded-lg bg-gray-50 text-xs text-gray-800">
                      Zamboanga City</div>
                  </div>
                  <div class="md:col-span-2">
                    <label class="block text-[11px] font-medium text-gray-600 mb-1">DTI / SEC Registration</label>
                    <a id="vrDtiFile" href="#"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">
                      <i class="fi fi-rr-file-pdf text-indigo-500 text-[11px]"></i>
                      <span>DTIRegistration.pdf</span>
                      <span class="text-[10px] text-gray-500">200 KB</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="sticky bottom-0 bg-white border-t border-gray-200 px-3 sm:px-4 py-2.5 rounded-b-xl flex items-center justify-end gap-1.5">
            <button id="vrRejectBtn"
              class="px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all text-[11px] font-semibold active:scale-95">Reject</button>
            <button id="vrAcceptBtn"
              class="px-3 py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all text-[11px] font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95">Accept</button>
          </div>
        </div>
      </div>

      <!-- Accept Confirmation Modal -->
      <div id="acceptConfirmModal"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[80] hidden items-center justify-center p-2 sm:p-3">
        <div
          class="relative z-[81] bg-white rounded-lg shadow-lg max-w-xs w-full overflow-hidden transform transition-all duration-200 scale-95 opacity-0 accept-panel">
          <div class="p-5 space-y-3 text-center">
            <div class="mx-auto w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
              <i class="fi fi-rr-check-circle text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800">Verify Account?</h3>
            <p class="text-[11px] text-gray-600 leading-relaxed">The user can use and access their accounts now.</p>
          </div>
          <div class="px-3 pb-3 space-y-1.5">
            <button id="acceptConfirmBtn"
              class="w-full px-3 py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-md transition-all text-[11px] font-semibold shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-95">Confirm</button>
            <button id="acceptCancelBtn"
              class="w-full px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-all text-[11px] font-semibold hover:border-gray-400 hover:shadow-sm hover:scale-[1.01] active:scale-95">Cancel</button>
          </div>
        </div>
      </div>

      <!-- Reject Confirmation Modal -->
      <div id="rejectConfirmModal"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[80] hidden items-center justify-center p-2 sm:p-3">
        <div
          class="relative z-[81] bg-white rounded-lg shadow-lg max-w-md w-full overflow-hidden transform transition-all duration-200 scale-95 opacity-0 reject-panel">
          <div class="p-5 space-y-3">
            <div class="mx-auto w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
              <i class="fi fi-rr-cross-circle text-xl"></i>
            </div>
            <div class="text-center space-y-1">
              <h3 class="text-base font-bold text-gray-800">Reject Verification</h3>
              <p class="text-[11px] text-gray-600 leading-relaxed">Please provide a brief reason why this verification is being rejected.</p>
            </div>
            <div>
              <label for="rejectReasonInput" class="block text-[11px] font-semibold text-gray-800 mb-1.5">Reason for Rejection
                <span class="text-red-500">*</span></label>
              <textarea id="rejectReasonInput" rows="3"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-2 focus:ring-red-300 focus:border-red-300 focus:outline-none transition resize-none placeholder-gray-400"
                placeholder="e.g., Incomplete PCAB details or mismatched IDs."></textarea>
              <p id="rejectReasonError" class="mt-1 text-xs text-red-600 hidden">Reason is required.</p>
            </div>
            <div class="flex items-start gap-2 pt-1">
              <input type="checkbox" id="tagResubmissionCheckbox"
                class="mt-0.5 h-3.5 w-3.5 rounded border-gray-300 text-orange-500 focus:ring-orange-400 cursor-pointer flex-shrink-0">
              <label for="tagResubmissionCheckbox" class="text-[11px] text-gray-700 cursor-pointer leading-relaxed">
                Tag for Resubmission
                <span class="block text-[10px] text-gray-400 mt-0.5">User will be asked to log in and resubmit their documents instead of a full rejection.</span>
              </label>
            </div>
          </div>
          <div class="px-3 pb-3 space-y-1.5">
            <button id="rejectConfirmBtn"
              class="w-full px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-md transition-all text-[11px] font-semibold shadow-sm hover:shadow-md hover:scale-[1.01] active:scale-95">Confirm</button>
            <button id="rejectCancelBtn"
              class="w-full px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-all text-[11px] font-semibold hover:border-gray-400 hover:shadow-sm hover:scale-[1.01] active:scale-95">Cancel</button>
          </div>
        </div>
      </div>

      <!-- Property Owner Verification Modal -->
      <div id="ownerVerificationModal"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-40 hidden items-center justify-center p-2 sm:p-3">
        <div
          class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 owner-modal-panel">
          <!-- Header -->
          <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-3 sm:px-4 py-2.5 flex items-center justify-between rounded-t-xl shadow-lg z-10">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <i class="fi fi-rr-user text-white text-xs"></i>
              </div>
              <h2 class="text-sm sm:text-base font-bold text-white">Owner Verification</h2>
            </div>
            <button id="poCloseBtn"
              class="text-white hover:text-orange-100 transition-all p-1 rounded-md hover:bg-white hover:bg-opacity-20 active:scale-95">
              <i class="fi fi-rr-cross text-base"></i>
            </button>
          </div>

          <!-- Body -->
          <div class="overflow-y-auto max-h-[calc(84vh-104px)] p-3 sm:p-4 space-y-3 owner-verification-modal-scroll">
            <!-- Top: Profile & Account -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <!-- Profile Card -->
              <div class="bg-white rounded-xl border border-gray-200 p-3">
                <div class="flex items-start gap-3">
                  <div
                    class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow">
                    <span id="poInitials">MM</span></div>
                  <div class="flex-1 min-w-0">
                    <h3 id="poFullName" class="text-sm font-bold text-gray-800 truncate">Mar Manon-og</h3>
                    <p id="poContactLine" class="text-[11px] text-gray-600 truncate">mar@example.com • 0999 123 4567</p>
                  </div>
                </div>
              </div>
              <!-- Account Card -->
              <div class="bg-white rounded-xl border border-gray-200 p-3">
                <h4 class="text-xs font-semibold text-gray-800 mb-2 flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                  Account Information
                </h4>
                <div class="grid grid-cols-1 gap-2">
                  <div>
                    <span class="block text-[11px] text-gray-500 mb-1">Username</span>
                    <div id="poUsername" class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-800">mar_owner
                    </div>
                  </div>
                  <div>
                    <span class="block text-[11px] text-gray-500 mb-1">Email</span>
                    <div id="poEmail" class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-800">mar@example.com
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Bottom: Personal Info & Documents -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <!-- Personal Information -->
              <div class="bg-white rounded-xl border border-gray-200 p-3">
                <h4 class="text-xs font-semibold text-gray-800 mb-2 flex items-center gap-1.5">
                  <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                  Personal information
                </h4>
                <div class="space-y-2">
                  <div>
                    <span class="block text-[11px] text-gray-500 mb-1">Occupation</span>
                    <div id="poOccupation" class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-800">Civil
                      Engineer</div>
                  </div>
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <span class="block text-[11px] text-gray-500 mb-1">Date of Birth</span>
                      <div id="poDob" class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-800">1990-05-22</div>
                    </div>
                    <div>
                      <span class="block text-[11px] text-gray-500 mb-1">Age</span>
                      <div id="poAge" class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-800">35</div>
                    </div>
                  </div>
                  <div>
                    <span class="block text-[11px] text-gray-500 mb-1">Address</span>
                    <div id="poAddress" class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-800">Street,
                      Barangay, City, Province, 7000</div>
                  </div>
                </div>
              </div>

              <!-- Documents -->
              <div class="bg-white rounded-xl border border-gray-200 p-3">
                <h4 class="text-xs font-semibold text-orange-600 mb-2 flex items-center gap-1.5">
                  <i class="fi fi-rr-folder"></i>
                  Verification Documents
                </h4>
                <div class="grid grid-cols-1 gap-2">
                  <div class="grid grid-cols-1 gap-2">
                    <div>
                      <span class="block text-[11px] text-gray-500 mb-1">Valid ID Type</span>
                      <div id="poValidIdType" class="px-2.5 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-800">
                        Philippine Passport</div>
                    </div>
                  </div>
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <span class="block text-[11px] text-gray-500 mb-1">Valid ID Photo (Front)</span>
                      <a id="poValidIdPhoto" href="#"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition w-full">
                        <i class="fi fi-rr-id-badge text-indigo-500 text-[11px]"></i>
                        <span class="truncate">Front.jpg</span>
                      </a>
                    </div>
                    <div>
                      <span class="block text-[11px] text-gray-500 mb-1">Valid ID Photo (Back)</span>
                      <a id="poValidIdBackPhoto" href="#"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition w-full">
                        <i class="fi fi-rr-id-badge text-indigo-500 text-[11px]"></i>
                        <span class="truncate">Back.jpg</span>
                      </a>
                    </div>
                  </div>
                  <div>
                    <span class="block text-[11px] text-gray-500 mb-1">Police Clearance</span>
                    <a id="poPoliceClearance" href="#"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-lg text-xs font-medium transition">
                      <i class="fi fi-rr-file text-purple-500 text-[11px]"></i>
                      <span>PoliceClearance.pdf</span>
                      <span class="text-[10px] text-gray-500">200 KB</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="sticky bottom-0 bg-white border-t border-gray-200 px-3 sm:px-4 py-2.5 rounded-b-xl flex items-center justify-end gap-1.5">
            <button id="poRejectBtn"
              class="px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all text-[11px] font-semibold active:scale-95">Reject</button>
            <button id="poAcceptBtn"
              class="px-3 py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all text-[11px] font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95">Accept</button>
          </div>
        </div>

      </div>

      <!-- Delete Confirmation Modal (Shared) -->
      <div id="deleteConfirmModal"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-[70] hidden items-center justify-center p-4">
        <div
          class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-200 scale-95 opacity-0 delete-panel">
          <div class="p-6 space-y-4 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
              <i class="fi fi-rr-trash text-2xl"></i>
            </div>
            <h3 id="deleteTitle" class="text-2xl font-bold text-gray-900">Delete</h3>
            <p class="text-gray-600">Permanently delete <span id="deleteName" class="font-semibold text-gray-900">this
                item</span>?<br>This action cannot be undone.</p>
          </div>
          <div class="px-6 pb-6 grid grid-cols-1 gap-3">
            <button id="deleteConfirmBtn"
              class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl transition font-semibold shadow-md hover:shadow-lg">
              <i class="fi fi-rr-trash"></i>
              Delete
            </button>
            <button id="deleteCancelBtn"
              class="w-full px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition font-semibold">Cancel</button>
          </div>
        </div>
      </div>

      <!-- Document Viewer Modal (moved here) -->
      <div id="docViewerModal" class="fixed inset-0 bg-black bg-opacity-50 z-[90] hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all">
          <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Document Viewer</h3>
            <button id="docViewerCloseBtn" class="text-gray-500 hover:text-gray-800 p-2">
              <i class="fi fi-rr-cross text-2xl"></i>
            </button>
          </div>
          <div class="p-4 flex items-center justify-center min-h-[60vh]">
            <img id="docViewerImg" src="" alt="Document" class="max-w-full max-h-[70vh] object-contain hidden" />
            <iframe id="docViewerIframe" src="" class="w-full h-[70vh] hidden border-0"></iframe>
          </div>
        </div>
      </div>

    </main>


    <script src="{{ asset('js/admin/reusables/filters.js') }}?v={{ time() }}" defer></script>
    <script src="{{ asset('js/admin/userManagement/verificationRequest.js') }}?v={{ time() }}" defer></script>

</body>

</html>
