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

  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


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

    #addContractorModal .add-contractor-modal-scroll {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    #addContractorModal .add-contractor-modal-scroll::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }

    #editContractorModal .edit-contractor-modal-scroll {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    #editContractorModal .edit-contractor-modal-scroll::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }

    .action-btn {
      position: relative;
      overflow: hidden;
    }

    .action-btn i {
      position: relative;
      z-index: 1;
      display: block;
      line-height: 1;
      pointer-events: none;
    }

    .action-btn .ripple-effect {
      position: absolute;
      border-radius: 9999px;
      background: rgba(255, 255, 255, 0.45);
      transform: scale(0);
      animation: contractor-ripple 0.6s ease-out;
      pointer-events: none;
      z-index: 0;
    }

    @keyframes contractor-ripple {
      to {
        transform: scale(2.6);
        opacity: 0;
      }
    }

    /* Error state styling */
    .add-contractor-field.error {
      border-color: rgb(239, 68, 68) !important;
      background-color: rgb(254, 242, 242);
    }

    .add-contractor-field.error:focus {
      ring-color: rgb(248, 113, 113) !important;
      border-color: rgb(239, 68, 68) !important;
    }

    #addContractorErrorAlert {
      animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>


</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1 overflow-x-hidden">
      @include('admin.layouts.topnav', ['pageTitle' => 'Contractors'])

      <div class="px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6">
        <!-- Controls Section -->
        <div
          class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
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

          <div class="flex items-center gap-2">
            <button id="resetFilterBtn"
              class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
              <i class="fi fi-rr-rotate-left"></i>
              <span>Reset Filter</span>
            </button>

            <button id="addContractorBtn"
              class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-4 py-2 rounded-lg font-semibold text-sm shadow-sm hover:shadow-md transition transform hover:scale-[1.01]">
              <i class="fi fi-rr-plus"></i>
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
  <div id="addContractorModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2 sm:p-3">
    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div
        class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-3 sm:px-4 py-2.5 flex items-center justify-between rounded-t-xl shadow-lg z-10">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-building text-white text-xs"></i>
          </div>
          <h2 class="text-sm sm:text-base font-bold text-white">Add Contractor</h2>
        </div>
        <button id="closeModalBtn"
          class="text-white hover:text-orange-100 transition-all p-1 rounded-md hover:bg-white hover:bg-opacity-20 active:scale-95">
          <i class="fi fi-rr-cross text-base"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(84vh-104px)] p-3 sm:p-4 add-contractor-modal-scroll">
        <!-- Profile Picture Section -->
        <div class="flex items-center gap-3 mb-3">
          <div class="relative group">
            <div
              class="w-16 h-16 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center overflow-hidden shadow">
              <i class="fi fi-rr-building text-2xl text-gray-500" id="profileIcon"></i>
              <img id="profilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
            </div>
            <label for="profileUpload"
              class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-1 rounded-full cursor-pointer shadow-md transition-all hover:-translate-y-0.5 active:scale-95">
              <i class="fi fi-rr-pencil text-xs"></i>
              <input type="file" id="profileUpload" name="profile_pic" class="hidden" accept="image/*">
            </label>
          </div>
          <div>
            <h3 class="text-xs font-semibold text-gray-800">Company Logo</h3>
            <p class="text-[11px] text-gray-500">Upload logo for this contractor</p>
          </div>
        </div>

        <!-- Error Alert Section -->
        <div id="addContractorErrorAlert" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
          <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
              <i class="fi fi-rr-circle-exclamation text-red-500 text-base"></i>
            </div>
            <div class="flex-1">
              <h3 class="text-xs font-semibold text-red-800 mb-1">Validation Error</h3>
              <ul id="addContractorErrorList" class="text-[11px] text-red-700 space-y-1 list-disc list-inside"></ul>
            </div>
            <button type="button" id="closeErrorAlert" class="text-red-500 hover:text-red-700 transition p-1">
              <i class="fi fi-rr-cross text-sm"></i>
            </button>
          </div>
        </div>

        <!-- Owner Information Section -->
        <div class="mb-3">
          <div class="flex items-center gap-2.5 mb-2.5">
            <h3 class="text-xs font-semibold text-orange-700 flex items-center gap-1.5 whitespace-nowrap px-2 py-1 rounded-full bg-orange-50 border border-orange-200 shadow-sm">
              <i class="fi fi-rr-user text-orange-500 text-xs"></i>
              Owner Information
            </h3>
            <div class="h-0.5 flex-1 rounded-full bg-gradient-to-r from-orange-400 via-orange-200 to-orange-50"></div>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-2">Select Existing Verified Property Owner <span
                class="text-gray-400">(optional)</span></label>
            <input type="hidden" name="owner_id" id="selectedOwnerId">
            <div class="relative">
              <input type="text" id="ownerSearchInput" placeholder="Search by owner name or email"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 focus:outline-none transition add-contractor-field"
                data-field="owner_id">
              <div id="ownerSearchResults"
                class="absolute top-full left-0 right-0 z-10 mt-2 bg-white border border-gray-200 rounded-lg shadow-md max-h-48 overflow-y-auto hidden"></div>
            </div>
            <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
            <div id="selectedOwnerSummary" class="mt-3 hidden bg-green-50 border border-green-200 rounded-lg p-3">
              <div class="flex items-center justify-between gap-2">
                <div class="flex-1">
                  <div id="selectedOwnerName" class="text-xs font-semibold text-green-800"></div>
                  <div id="selectedOwnerEmail" class="text-[11px] text-green-700"></div>
                </div>
                <button type="button" id="clearSelectedOwner" class="text-xs text-red-600 hover:text-red-700 font-medium hover:underline transition">Remove</button>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
          <!-- Business Address Section -->
          <div class="space-y-3">
            <div>
              <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                <i class="fi fi-rr-map-marker text-orange-500 text-xs"></i>
                Business Address
              </h3>
              <div class="space-y-2">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Province</label>
                  <select id="contractor_address_province" name="business_address_province"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                    <option value="">Select Province</option>
                    @foreach($provinces as $province)
                      <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">{{ $province['name'] }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">City/Municipality</label>
                  <select id="contractor_address_city" name="business_address_city"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition"
                    disabled>
                    <option value="">Select City/Municipality</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Barangay</label>
                  <select id="contractor_address_barangay" name="business_address_barangay"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition"
                    disabled>
                    <option value="">Select Barangay</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Street Address / Unit No.</label>
                  <input type="text" name="business_address_street" placeholder="Enter street address"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Zip Code</label>
                  <input type="text" name="business_address_postal" placeholder="Enter zip code"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                </div>
              </div>
            </div>
          </div>

          <!-- Legal Documents Section -->
          <div>
            <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
              <i class="fi fi-rr-file-invoice text-orange-500 text-xs"></i>
              Legal Documents
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">PCAB Number <span
                    class="text-red-500">*</span></label>
                <input type="text" name="picab_number" placeholder="Enter PCAB number"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                  data-field="picab_number">
                <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">PCAB Category <span
                    class="text-red-500">*</span></label>
                <select name="picab_category"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                  data-field="picab_category">
                  <option value="">Select Category</option>
                  @foreach($picabCategories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                  @endforeach
                </select>
                <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">PCAB Expiration Date <span
                    class="text-red-500">*</span></label>
                <input type="date" name="picab_expiration_date"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                  data-field="picab_expiration_date">
                <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Business Permit Number <span
                    class="text-red-500">*</span></label>
                <input type="text" name="business_permit_number" placeholder="Enter permit number"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                  data-field="business_permit_number">
                <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Business Permit City <span
                    class="text-red-500">*</span></label>
                <select id="business_permit_city" name="business_permit_city"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                  data-field="business_permit_city">
                  <option value="">Select City</option>
                  @foreach($allCities as $city)
                    <option value="{{ $city['name'] }}">{{ $city['name'] }}</option>
                  @endforeach
                </select>
                <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Business Permit Expiration <span
                    class="text-red-500">*</span></label>
                <input type="date" name="business_permit_expiration"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                  data-field="business_permit_expiration">
                <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">TIN Business Registration Number <span
                    class="text-red-500">*</span></label>
                <input type="text" name="tin_business_reg_number" placeholder="Enter TIN/Business Reg. number"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                  data-field="tin_business_reg_number">
                <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">DTI / SEC Registration <span
                    class="text-red-500">*</span></label>
                <div id="dtiDropzone"
                  class="flex items-center justify-center w-full min-h-[96px] rounded-lg border border-dashed border-gray-300 bg-gray-50 text-gray-600 hover:bg-orange-50 hover:border-orange-300 transition-all relative cursor-pointer p-3 add-contractor-field"
                  data-field="dti_sec_registration_photo">
                  <input id="dtiUpload" name="dti_sec_registration_photo" type="file" accept="image/*,application/pdf"
                    class="hidden">
                  <div class="text-center pointer-events-none">
                    <i class="fi fi-rr-upload text-xl text-gray-400"></i>
                    <div class="text-xs font-medium mt-1">Upload image or file</div>
                    <div id="dtiFileName" class="text-xs text-orange-500 mt-1"></div>
                  </div>
                </div>
                <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Company Information Section -->
        <div class="mb-3">
          <div class="flex items-center gap-2.5 mb-2.5">
            <h3 class="text-xs font-semibold text-orange-700 flex items-center gap-1.5 whitespace-nowrap px-2 py-1 rounded-full bg-orange-50 border border-orange-200 shadow-sm">
              <i class="fi fi-rr-building text-orange-500 text-xs"></i>
              Company Information
            </h3>
            <div class="h-0.5 flex-1 rounded-full bg-gradient-to-r from-orange-400 via-orange-200 to-orange-50"></div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Company Name <span class="text-red-500">*</span></label>
              <input type="text" name="company_name" placeholder="Enter company name"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                data-field="company_name">
              <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Date of Incorporation <span class="text-red-500">*</span></label>
              <input type="date" name="company_start_date" max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                data-field="company_start_date">
              <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Contractor Type <span class="text-red-500">*</span></label>
              <select name="contractor_type_id" id="contractorTypeSelect"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                data-field="contractor_type_id">
                <option value="">Select Type</option>
                @foreach($contractorTypes as $type)
                  <option value="{{ $type->type_id }}">{{ $type->type_name }}</option>
                @endforeach
              </select>
              <input type="text" name="contractor_type_other_text" id="contractorTypeOtherInput"
                placeholder="Please specify type"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition mt-1.5 hidden add-contractor-field"
                data-field="contractor_type_other_text">
              <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Company Email <span class="text-red-500">*</span></label>
              <input type="email" name="company_email" placeholder="Enter email address"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition add-contractor-field"
                data-field="company_email">
              <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-medium text-gray-700 mb-1">Services Offered</label>
              <input type="text" name="services_offered" placeholder="e.g. Plumbing, Electrical, Roofing"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Company Website <span
                  class="text-gray-400">(optional)</span></label>
              <input type="url" name="company_website" placeholder="https://"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Social Media <span
                  class="text-gray-400">(optional)</span></label>
              <input type="url" name="company_social_media" placeholder="https://"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
      </div>

      <!-- Modal Footer -->
      <div class="bg-white border-t border-gray-200 px-3 sm:px-4 py-2.5 rounded-b-xl flex items-center justify-end gap-1.5 sticky bottom-0 z-10">
          <button id="cancelBtn"
            class="px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all text-[11px] font-semibold active:scale-95">
            Cancel
          </button>
          <button id="saveBtn"
            class="px-3 py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all text-[11px] font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5">
            <i class="fi fi-rr-check"></i>
            Save Contractor
          </button>
      </div>
    </div>
  </div>

  <!-- Edit Contractor Modal -->
  <div id="editContractorModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-2 sm:p-3">
    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content relative flex flex-col">
      <!-- Modal Header -->
      <div
        class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-3 sm:px-4 py-2.5 flex items-center justify-between rounded-t-xl shadow-lg z-10">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-edit text-white text-xs"></i>
          </div>
          <h2 class="text-sm sm:text-base font-bold text-white">Edit Contractor</h2>
        </div>
        <button id="closeEditModalBtn"
          class="text-white hover:text-orange-100 transition-all p-1 rounded-md hover:bg-white hover:bg-opacity-20 active:scale-95">
          <i class="fi fi-rr-cross text-base"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="flex-1 min-h-0 overflow-y-auto p-3 sm:p-4 pb-4 edit-contractor-modal-scroll">
        <form id="editContractorForm" class="space-y-3">
          <input type="hidden" id="edit_user_id" name="user_id">

          <!-- Profile Picture Section -->
          <div class="flex items-center gap-3">
            <div class="relative group">
              <div
                class="w-16 h-16 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center overflow-hidden shadow">
                <i class="fi fi-rr-building text-2xl text-gray-500" id="editProfileIcon"></i>
                <img id="editProfilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
              </div>
              <label for="editProfileUpload"
                class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-1 rounded-full cursor-pointer shadow-md transition-all hover:-translate-y-0.5 active:scale-95">
                <i class="fi fi-rr-pencil text-xs"></i>
                <input type="file" id="editProfileUpload" name="profile_pic" class="hidden" accept="image/*">
              </label>
            </div>
            <div>
              <h3 class="text-xs font-semibold text-gray-800">Company Logo</h3>
              <p class="text-[11px] text-gray-500">Update logo for this contractor</p>
            </div>
          </div>

          <!-- Error Alert Section -->
          <div id="editContractorErrorAlert" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start gap-3">
              <div class="flex-shrink-0">
                <i class="fi fi-rr-alert text-red-500 text-base"></i>
              </div>
              <div class="flex-1">
                <h3 class="text-xs font-semibold text-red-800">Validation Errors</h3>
                <ul id="editContractorErrorList" class="text-xs text-red-700 mt-2 space-y-1 list-disc list-inside"></ul>
              </div>
              <button type="button" id="closeEditErrorAlert" class="text-red-500 hover:text-red-700 transition p-1">
                <i class="fi fi-rr-cross"></i>
              </button>
            </div>
          </div>

          <!-- Owner Information Section -->
          <div class="mb-3">
            <div class="flex items-center gap-2.5 mb-2.5">
              <h3 class="text-xs font-semibold text-orange-700 flex items-center gap-1.5 whitespace-nowrap px-2 py-1 rounded-full bg-orange-50 border border-orange-200 shadow-sm">
                <i class="fi fi-rr-user"></i>
                Owner Information
              </h3>
              <div class="h-0.5 flex-1 rounded-full bg-gradient-to-r from-orange-400 via-orange-200 to-orange-50"></div>
            </div>

            <div class="mb-2">
              <label class="block text-xs font-medium text-gray-700 mb-2">Select Existing Verified Property Owner <span
                  class="text-gray-400">(optional)</span></label>
              <input type="hidden" name="owner_id" id="edit_selectedOwnerId">
              <div class="relative">
                <input type="text" id="edit_ownerSearchInput" placeholder="Search by owner name or email"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition"
                  data-field="edit_owner_id">
                <div id="edit_ownerSearchResults"
                  class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50 hidden max-h-48 overflow-y-auto">
                </div>
              </div>
              <p class="add-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              <div id="edit_selectedOwnerSummary" class="mt-3 hidden bg-green-50 border border-green-200 rounded-lg p-3">
                <div class="flex items-center justify-between gap-2">
                  <div>
                    <p class="text-xs font-medium text-gray-800"><span id="edit_ownerDisplayName"></span></p>
                    <p class="text-[11px] text-gray-600"><span id="edit_ownerDisplayEmail"></span></p>
                  </div>
                  <button type="button" id="edit_clearOwnerBtn"
                    class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition">
                    <i class="fi fi-rr-cross"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">First Name</label>
                <input type="text" id="edit_first_name" name="first_name" placeholder="Enter first name"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Middle Name <span
                    class="text-gray-400">(optional)</span></label>
                <input type="text" id="edit_middle_name" name="middle_name" placeholder="Enter middle name"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Last Name</label>
                <input type="text" id="edit_last_name" name="last_name" placeholder="Enter last name"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div class="space-y-3">
              <div>
                <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                  <i class="fi fi-rr-user-gear text-orange-500 text-xs"></i>
                  Account Setup
                </h3>
                <div class="space-y-2">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="edit_username" name="username" placeholder="Enter username"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg bg-gray-50 text-gray-500 focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition"
                      readonly>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">New Password <span
                        class="text-gray-400">(Optional)</span></label>
                    <input type="password" id="edit_password" name="password" placeholder="Enter new password"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                    <p class="text-[10px] text-gray-500 mt-1">Leave blank if you do not want to change the password.</p>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                  <i class="fi fi-rr-map-marker text-orange-500 text-xs"></i>
                  Business Address
                </h3>
                <div class="space-y-2">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Province</label>
                    <select id="edit_contractor_address_province" name="business_address_province"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                      <option value="">Select Province</option>
                      @foreach($provinces as $province)
                        <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">
                          {{ $province['name'] }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">City/Municipality</label>
                    <select id="edit_contractor_address_city" name="business_address_city"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition"
                      disabled>
                      <option value="">Select City/Municipality</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Barangay</label>
                    <select id="edit_contractor_address_barangay" name="business_address_barangay"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition"
                      disabled>
                      <option value="">Select Barangay</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Street Address / Unit No.</label>
                    <input type="text" id="edit_business_address_street" name="business_address_street"
                      placeholder="Enter street address"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Zip Code</label>
                    <input type="text" id="edit_business_address_postal" name="business_address_postal"
                      placeholder="Enter zip code"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  </div>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                <i class="fi fi-rr-file-invoice text-orange-500 text-xs"></i>
                Legal Documents
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">PCAB Number <span
                    class="text-red-500">*</span></label>
                <input type="text" id="edit_picab_number" name="picab_number" placeholder="Enter PCAB number"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition edit-contractor-field"
                  data-field="picab_number">
                <p class="edit-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">PCAB Category <span
                    class="text-red-500">*</span></label>
                <select id="edit_picab_category" name="picab_category"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition edit-contractor-field"
                  data-field="picab_category">
                  <option value="">Select Category</option>
                  @foreach($picabCategories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                  @endforeach
                </select>
                <p class="edit-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">PCAB Expiration Date <span
                    class="text-red-500">*</span></label>
                <input type="date" id="edit_picab_expiration_date" name="picab_expiration_date"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Business Permit Number <span
                    class="text-red-500">*</span></label>
                <input type="text" id="edit_business_permit_number" name="business_permit_number"
                  placeholder="Enter permit number"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Business Permit City <span
                    class="text-red-500">*</span></label>
                <select id="edit_business_permit_city" name="business_permit_city"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  <option value="">Select City</option>
                  @foreach($allCities as $city)
                    <option value="{{ $city['name'] }}">{{ $city['name'] }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Business Permit Expiration <span
                    class="text-red-500">*</span></label>
                <input type="date" id="edit_business_permit_expiration" name="business_permit_expiration"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">TIN Business Registration Number <span
                    class="text-red-500">*</span></label>
                <input type="text" id="edit_tin_business_reg_number" name="tin_business_reg_number"
                  placeholder="Enter TIN/Business Reg. number"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">DTI / SEC Registration <span
                    class="text-gray-400">(Optional)</span></label>
                <div id="editDtiDropzone"
                  class="flex items-center justify-center w-full min-h-[96px] rounded-lg border border-dashed border-gray-300 bg-gray-50 text-gray-600 hover:bg-orange-50 hover:border-orange-300 transition-all relative cursor-pointer p-3">
                  <input id="editDtiUpload" name="dti_sec_registration_photo" type="file"
                    accept="image/*,application/pdf" class="hidden">
                  <div class="text-center pointer-events-none">
                    <i class="fi fi-rr-upload text-xl text-gray-400"></i>
                    <div class="text-xs font-medium mt-1">Upload image or file</div>
                    <div id="editDtiFileName" class="text-xs text-orange-500 mt-1"></div>
                  </div>
                </div>
                <div id="editCurrentDtiFile" class="mt-1.5 text-[11px] text-gray-500 hidden">
                  Current: <a href="#" target="_blank" class="text-orange-600 hover:underline font-medium">View File</a>
                </div>
              </div>
            </div>
          </div>
          </div>

          <!-- Company Information Section -->
          <div class="mb-3">
            <div class="flex items-center gap-2.5 mb-2.5">
              <h3 class="text-xs font-semibold text-orange-700 flex items-center gap-1.5 whitespace-nowrap px-2 py-1 rounded-full bg-orange-50 border border-orange-200 shadow-sm">
                <i class="fi fi-rr-building text-orange-500 text-xs"></i>
                Company Information
              </h3>
              <div class="h-0.5 flex-1 rounded-full bg-gradient-to-r from-orange-400 via-orange-200 to-orange-50"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Company Name</label>
                <input type="text" id="edit_company_name" name="company_name" placeholder="Enter company name"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition edit-contractor-field"
                  data-field="company_name">
                <p class="edit-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Date of Incorporation</label>
                <input type="date" id="edit_company_start_date" name="company_start_date" max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition edit-contractor-field"
                  data-field="company_start_date">
                <p class="edit-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Contractor Type</label>
                <select name="contractor_type_id" id="edit_contractorTypeSelect"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition edit-contractor-field"
                  data-field="contractor_type_id">
                  <option value="">Select Type</option>
                  @foreach($contractorTypes as $type)
                    <option value="{{ $type->type_id }}">{{ $type->type_name }}</option>
                  @endforeach
                </select>
                <p class="edit-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
                <input type="text" name="contractor_type_other_text" id="edit_contractorTypeOtherInput"
                  placeholder="Please specify type"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition mt-1.5 hidden">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Company Email</label>
                <input type="email" id="edit_company_email" name="company_email" placeholder="Enter email address"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition edit-contractor-field"
                  data-field="company_email">
                <p class="edit-contractor-error text-red-500 text-[11px] mt-1 hidden"></p>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-700 mb-1">Services Offered</label>
                <input type="text" id="edit_services_offered" name="services_offered"
                  placeholder="e.g. Plumbing, Electrical, Roofing"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Company Website <span
                    class="text-gray-400">(optional)</span></label>
                <input type="url" id="edit_company_website" name="company_website" placeholder="https://"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Social Media <span
                    class="text-gray-400">(optional)</span></label>
                <input type="url" id="edit_company_social_media" name="company_social_media" placeholder="https://"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Modal Footer -->
      <div class="bg-white border-t border-gray-200 px-3 sm:px-4 py-2.5 rounded-b-xl w-full flex items-center justify-end gap-1.5 sticky bottom-0 z-20 shadow-[0_-6px_14px_rgba(17,24,39,0.08)]">
        <button type="button" id="cancelEditBtn"
          class="px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all text-[11px] font-semibold active:scale-95">
          Cancel
        </button>
        <button type="button" id="saveEditBtn"
          class="px-3 py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all text-[11px] font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5">
          <i class="fi fi-rr-check"></i>
          Save Changes
        </button>
      </div>
    </div>
  </div>

  <!-- Delete Contractor Modal -->
  <div id="deleteContractorModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4" style="z-index:99999;">
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0 modal-content" style="z-index:100000;">
      <!-- Icon Section -->
      <div class="flex justify-center pt-3 pb-2">
        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center relative">
          <div class="absolute inset-0 bg-red-200 rounded-full animate-ping opacity-60"></div>
          <div class="relative w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
            <i class="fi fi-rr-trash text-white text-base"></i>
          </div>
        </div>
      </div>

      <!-- Content Section -->
      <div class="px-3 pb-3 text-center">
        <h2 class="text-sm font-bold text-gray-800 mb-1.5">Delete Contractor</h2>
        <p class="text-[11px] text-gray-600 leading-relaxed mb-2.5">
          Permanently delete <span class="font-bold text-gray-800" id="deleteContractorName">GTH Builders and
            Developers</span>? This action cannot be undone.
        </p>

        <div class="text-left">
          <label for="deletionReason" class="block text-[11px] font-medium text-gray-700 mb-1">Reason for Deletion <span
              class="text-red-500">*</span></label>
          <textarea id="deletionReason" rows="2"
            class="w-full px-2 py-1.5 text-[11px] border border-gray-300 rounded-md focus:ring-2 focus:ring-red-300 focus:border-red-300 focus:outline-none transition resize-none"
            placeholder="Please provide a reason for deletion..."></textarea>
          <p id="deletionReasonError" class="text-red-500 text-[11px] mt-1 hidden">Reason is required.</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="px-3 pb-3 space-y-1.5">
        <button id="confirmDeleteBtn"
          class="w-full px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-md transition-all text-[11px] font-semibold shadow-sm hover:shadow-md transform hover:scale-[1.01] active:scale-95 flex items-center justify-center gap-1">
          <i class="fi fi-rr-trash"></i>
          Delete
        </button>
        <button id="cancelDeleteBtn"
          class="w-full px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-all text-[11px] font-semibold hover:border-gray-400 hover:shadow-sm transform hover:scale-[1.01] active:scale-95">
          Cancel
        </button>
      </div>
    </div>
  </div>




  <script>
    // Error alert close button handler
    document.addEventListener('DOMContentLoaded', function() {
      const closeErrorBtn = document.getElementById('closeErrorAlert');
      if (closeErrorBtn) {
        closeErrorBtn.addEventListener('click', function() {
          const errorAlert = document.getElementById('addContractorErrorAlert');
          if (errorAlert) {
            errorAlert.classList.add('hidden');
          }
        });
      }

      // Clear field errors on input
      const fields = document.querySelectorAll('.add-contractor-field');
      fields.forEach(field => {
        field.addEventListener('input', function() {
          this.classList.remove('error');
          const errorMsg = this.parentElement.querySelector('.add-contractor-error');
          if (errorMsg) {
            errorMsg.classList.add('hidden');
          }
        });

        field.addEventListener('change', function() {
          this.classList.remove('error');
          const errorMsg = this.parentElement.querySelector('.add-contractor-error');
          if (errorMsg) {
            errorMsg.classList.add('hidden');
          }
        });
      });
    });
  </script>

  <script src="{{ asset('js/admin/userManagement/contractor.js') }}?v={{ time() }}" defer></script>

</body>

</html>
