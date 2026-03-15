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
  <link rel="stylesheet" href="{{ asset('css/admin/userManagement/propertyOwner.css') }}">

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

    #editPropertyOwnerModal .edit-modal-scroll {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    #editPropertyOwnerModal .edit-modal-scroll::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }

    #addPropertyOwnerModal .add-modal-scroll {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    #addPropertyOwnerModal .add-modal-scroll::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }
  </style>

</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex h-screen overflow-hidden">

    @include('admin.layouts.sidebar')

    <main class="flex-1 flex flex-col overflow-hidden">
      <div class="flex-shrink-0 sticky top-0 z-30">
        @include('admin.layouts.topnav', ['pageTitle' => 'Property Owners'])
      </div>

      <div class="flex-1 overflow-y-auto px-4 py-4 sm:px-6 sm:py-5 lg:px-8 lg:py-6">
        <!-- Controls Section -->
        <div class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-2.5">
            <!-- Filter By label -->
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

            <button id="addPropertyOwnerBtn"
              class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-4 py-2 rounded-lg font-semibold text-sm shadow-sm hover:shadow-md transition transform hover:scale-[1.01]">
              <i class="fi fi-rr-plus"></i>
              <span>Add Property Owner</span>
            </button>
          </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" id="ownersTableWrap">
          @include('admin.userManagement.partials.ownerTable')
        </div>
      </div>
    </main>
  </div>

  <!-- Add Property Owner Modal -->
  <div id="addPropertyOwnerModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2 sm:p-3">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div
        class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-3 sm:px-4 py-2.5 flex items-center justify-between rounded-t-xl shadow-lg z-10">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-plus text-white text-xs"></i>
          </div>
          <h2 class="text-sm sm:text-base font-bold text-white">Add Property Owner</h2>
        </div>
        <button id="closeModalBtn" class="text-white hover:text-orange-100 transition-all p-1 rounded-md hover:bg-white hover:bg-opacity-20 active:scale-95">
          <i class="fi fi-rr-cross text-base"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(84vh-104px)] p-3 sm:p-4 add-modal-scroll">
        <!-- Profile Picture Section -->
        <div class="flex items-center gap-3 mb-3">
          <div class="relative group">
            <div
              class="w-16 h-16 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center overflow-hidden shadow">
              <i class="fi fi-rr-user text-2xl text-gray-500" id="profileIcon"></i>
              <img id="profilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
            </div>
            <label for="profileUpload"
              class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-1 rounded-full cursor-pointer shadow-md transition-all hover:-translate-y-0.5 active:scale-95">
              <i class="fi fi-rr-pencil text-xs"></i>
              <input type="file" id="profileUpload" name="profile_pic" class="hidden" accept="image/*">
            </label>
          </div>
          <div>
            <h3 class="text-xs font-semibold text-gray-800">Profile Picture</h3>
            <p class="text-[11px] text-gray-500">Upload profile photo for this owner</p>
          </div>
        </div>

        <!-- Personal Information Section -->
        <div class="mb-3">
          <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
            <i class="fi fi-rr-user text-orange-500 text-xs"></i>
            Personal Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">First name <span class="text-red-500">*</span></label>
              <input type="text" id="addFirstName" name="first_name" placeholder="Enter first name"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              <p id="addFirstNameError" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Occupation <span class="text-red-500">*</span></label>
              <select name="occupation_id" id="occupationSelect"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                <option value="">Select Occupation</option>
                @foreach($occupations as $occupation)
                  @if(strtolower($occupation->occupation_name) !== 'others')
                    <option value="{{ $occupation->id }}">{{ $occupation->occupation_name }}</option>
                  @endif
                @endforeach
                <option value="others">Others</option>
              </select>
              <input type="text" name="occupation_other" id="occupationOtherInput"
                placeholder="Please specify occupation"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition mt-1.5 hidden">
              <p id="addOccupationError" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Middle name <span
                  class="text-gray-400">(optional)</span></label>
              <input type="text" name="middle_name" placeholder="Enter middle name"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Date of birth <span class="text-red-500">*</span></label>
              <input type="date" name="date_of_birth" id="addDateOfBirth"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              <p id="addDateOfBirthError" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Last name <span class="text-red-500">*</span></label>
              <input type="text" id="addLastName" name="last_name" placeholder="Enter last name"
                class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              <p id="addLastNameError" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
          <!-- Account Setup & Address Section -->
          <div class="space-y-3">
            <div>
              <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                <i class="fi fi-rr-user-gear text-orange-500 text-xs"></i>
                Account Setup
              </h3>
              <div class="space-y-2">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                  <input type="email" id="addEmail" name="email" placeholder="Enter email address"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  <p id="addEmailError" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                <i class="fi fi-rr-map-marker text-orange-500 text-xs"></i>
                Address
              </h3>
              <div class="space-y-2">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Province <span class="text-red-500">*</span></label>
                  <select id="owner_address_province" name="province"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                    <option value="">Select Province</option>
                    @foreach($provinces as $province)
                      <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">{{ $province['name'] }}
                      </option>
                    @endforeach
                  </select>
                  <p id="addProvinceError" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">City/Municipality <span class="text-red-500">*</span></label>
                  <select id="owner_address_city" name="city"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition"
                    disabled>
                    <option value="">Select City/Municipality</option>
                  </select>
                  <p id="addCityError" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Barangay <span class="text-red-500">*</span></label>
                  <select id="owner_address_barangay" name="barangay"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition"
                    disabled>
                    <option value="">Select Barangay</option>
                  </select>
                  <p id="addBarangayError" class="text-red-500 text-xs mt-1 hidden"></p>
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Street Address / Unit No.</label>
                  <input type="text" name="street_address" placeholder="Enter street address"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Zip Code</label>
                  <input type="text" name="zip_code" placeholder="Enter zip code"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                </div>
              </div>
            </div>
          </div>

          <!-- Verification Documents Section -->
          <div>
            <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
              <i class="fi fi-rr-document text-orange-500 text-xs"></i>
              Verification Documents
            </h3>
            <div class="space-y-2">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Type of Valid ID <span class="text-red-500">*</span></label>
                <select id="addValidIdType" name="valid_id_id"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  <option value="">Select ID type</option>
                  @foreach($validIds as $validId)
                    <option value="{{ $validId->id }}">{{ $validId->valid_id_name }}</option>
                  @endforeach
                </select>
                <p id="addValidIdTypeError" class="text-red-500 text-xs mt-1 hidden"></p>
              </div>

              <!-- Valid ID Front -->
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Valid ID (Front) <span class="text-red-500">*</span></label>
                <div
                  class="border border-dashed border-gray-300 rounded-lg p-3 text-center hover:border-orange-300 transition cursor-pointer bg-gray-50 hover:bg-orange-50"
                  id="idFrontUploadArea">
                  <input type="file" id="idFrontUpload" name="valid_id_photo" class="hidden" accept="image/*">
                  <i class="fi fi-rr-id-card-clip-alt text-xl text-gray-400 mb-1"></i>
                  <p class="text-xs text-gray-600 font-medium">Upload Front Side</p>
                  <div id="idFrontFileName" class="text-xs text-orange-500 mt-1 hidden font-medium"></div>
                </div>
                <p id="addIdFrontError" class="text-red-500 text-xs mt-1 hidden"></p>
              </div>

              <!-- Valid ID Back -->
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Valid ID (Back) <span class="text-red-500">*</span></label>
                <div
                  class="border border-dashed border-gray-300 rounded-lg p-3 text-center hover:border-orange-300 transition cursor-pointer bg-gray-50 hover:bg-orange-50"
                  id="idBackUploadArea">
                  <input type="file" id="idBackUpload" name="valid_id_back_photo" class="hidden" accept="image/*">
                  <i class="fi fi-rr-id-card-clip-alt text-xl text-gray-400 mb-1"></i>
                  <p class="text-xs text-gray-600 font-medium">Upload Back Side</p>
                  <div id="idBackFileName" class="text-xs text-orange-500 mt-1 hidden font-medium"></div>
                </div>
                <p id="addIdBackError" class="text-red-500 text-xs mt-1 hidden"></p>
              </div>

              <!-- Police Clearance -->
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Police Clearance <span class="text-red-500">*</span></label>
                <div
                  class="border border-dashed border-gray-300 rounded-lg p-3 text-center hover:border-orange-300 transition cursor-pointer bg-gray-50 hover:bg-orange-50"
                  id="policeClearanceUploadArea">
                  <input type="file" id="policeClearanceUpload" name="police_clearance" class="hidden" accept="image/*">
                  <i class="fi fi-rr-shield-check text-xl text-gray-400 mb-1"></i>
                  <p class="text-xs text-gray-600 font-medium">Upload Police Clearance</p>
                  <div id="policeClearanceFileName" class="text-xs text-orange-500 mt-1 hidden font-medium"></div>
                </div>
                <p id="addPoliceClearanceError" class="text-red-500 text-xs mt-1 hidden"></p>
              </div>
            </div>
          </div>
        </div>
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
          Save Property Owner
        </button>
      </div>
    </div>
  </div>

  <!-- Edit Property Owner Modal -->
  <div id="editPropertyOwnerModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-2 sm:p-3">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div
        class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-3 sm:px-4 py-2.5 flex items-center justify-between rounded-t-xl shadow-lg z-10">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-edit text-white text-xs"></i>
          </div>
          <h2 class="text-sm sm:text-base font-bold text-white">Edit Property Owner</h2>
        </div>
        <button id="closeEditModalBtn" class="text-white hover:text-orange-100 transition-all p-1 rounded-md hover:bg-white hover:bg-opacity-20 active:scale-95">
          <i class="fi fi-rr-cross text-base"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(84vh-104px)] p-3 sm:p-4 edit-modal-scroll">
        <form id="editPropertyOwnerForm" class="space-y-3">
          <input type="hidden" id="edit_user_id" name="user_id">

          <!-- Profile Picture Section -->
          <div class="flex items-center gap-3">
            <div class="relative">
              <div class="w-16 h-16 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center overflow-hidden shadow">
                <i class="fi fi-rr-user text-2xl text-gray-500" id="editProfileIcon"></i>
                <img id="editProfilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
              </div>
              <label for="editProfileUpload" class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-1 rounded-full cursor-pointer shadow-md transition-all hover:-translate-y-0.5 active:scale-95">
                <i class="fi fi-rr-pencil text-xs"></i>
                <input type="file" id="editProfileUpload" name="profile_pic" class="hidden" accept="image/*">
              </label>
            </div>
            <div>
              <h3 class="text-xs font-semibold text-gray-800">Profile Picture</h3>
              <p class="text-[11px] text-gray-500">Update profile photo for this owner</p>
            </div>
          </div>

          <!-- Personal Information Section -->
          <div>
            <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
              <i class="fi fi-rr-user text-orange-500 text-xs"></i>
              Personal Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">First name</label>
                <input type="text" id="edit_first_name" name="first_name" placeholder="Enter first name"
                  style="text-transform: none;"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Occupation</label>
                <select name="occupation_id" id="edit_occupationSelect"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  <option value="">Select Occupation</option>
                  @foreach($occupations as $occupation)
                    @if(strtolower($occupation->occupation_name) !== 'others')
                      <option value="{{ $occupation->id }}">{{ $occupation->occupation_name }}</option>
                    @endif
                  @endforeach
                  <option value="others">Others</option>
                </select>
                <input type="text" name="occupation_other" id="edit_occupationOtherInput"
                  placeholder="Please specify occupation"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition mt-1.5 hidden">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Middle name <span
                    class="text-gray-400">(optional)</span></label>
                <input type="text" id="edit_middle_name" name="middle_name" placeholder="Enter middle name"
                  style="text-transform: none;"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Date of birth</label>
                <input type="date" id="edit_date_of_birth" name="date_of_birth"
                  class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Last name</label>
                <input type="text" id="edit_last_name" name="last_name" placeholder="Enter last name"
                  style="text-transform: none;"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <!-- Account Setup & Address Section -->
            <div class="space-y-3">
              <div>
                <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                  <i class="fi fi-rr-user-gear text-orange-500 text-xs"></i>
                  Account Setup
                </h3>
                <div class="space-y-2">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="edit_email" name="email" placeholder="Enter email address"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="edit_username" name="username" placeholder="Enter username"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">New Password <span class="text-gray-400">(optional)</span></label>
                    <input type="password" id="edit_password" name="password" placeholder="Enter new password"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                    <p class="text-[10px] text-gray-500 mt-1">Leave blank if you do not want to change the password.</p>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                  <i class="fi fi-rr-map-marker text-orange-500 text-xs"></i>
                  Address
                </h3>
                <div class="space-y-2">
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Province</label>
                    <select id="edit_owner_address_province" name="province"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                      <option value="">Select Province</option>
                      @foreach($provinces as $province)
                        <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">
                          {{ $province['name'] }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">City/Municipality</label>
                    <select id="edit_owner_address_city" name="city"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition"
                      disabled>
                      <option value="">Select City/Municipality</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Barangay</label>
                    <select id="edit_owner_address_barangay" name="barangay"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition"
                      disabled>
                      <option value="">Select Barangay</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Street Address / Unit No.</label>
                    <input type="text" id="edit_street_address" name="street_address" placeholder="Enter street address"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Zip Code</label>
                    <input type="text" id="edit_zip_code" name="zip_code" placeholder="Enter zip code"
                      class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  </div>
                </div>
              </div>
            </div>

            <!-- Verification Documents Section -->
            <div>
              <h3 class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                <i class="fi fi-rr-document text-orange-500 text-xs"></i>
                Verification Documents
              </h3>
              <div class="space-y-2">
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Type of Valid ID</label>
                  <select id="edit_valid_id_id" name="valid_id_id"
                    class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                    <option value="">Select ID type</option>
                    @foreach($validIds as $validId)
                      <option value="{{ $validId->id }}">{{ $validId->valid_id_name }}</option>
                    @endforeach
                  </select>
                </div>

                <!-- Valid ID Front -->
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Valid ID (Front)</label>
                  <div class="border border-dashed border-gray-300 rounded-lg p-3 text-center hover:border-orange-300 transition cursor-pointer bg-gray-50 hover:bg-orange-50" id="editIdFrontUploadArea">
                    <input type="file" id="editIdFrontUpload" name="valid_id_photo" class="hidden" accept="image/*">
                    <i class="fi fi-rr-id-card-clip-alt text-xl text-gray-400 mb-1"></i>
                    <p class="text-xs text-gray-600 font-medium">Upload Front Side</p>
                    <div id="editIdFrontFileName" class="text-xs text-orange-500 mt-1 hidden font-medium"></div>
                  </div>
                  <div id="currentIdFront" class="mt-1 text-xs text-gray-500"></div>
                </div>

                <!-- Valid ID Back -->
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Valid ID (Back)</label>
                  <div class="border border-dashed border-gray-300 rounded-lg p-3 text-center hover:border-orange-300 transition cursor-pointer bg-gray-50 hover:bg-orange-50" id="editIdBackUploadArea">
                    <input type="file" id="editIdBackUpload" name="valid_id_back_photo" class="hidden" accept="image/*">
                    <i class="fi fi-rr-id-card-clip-alt text-xl text-gray-400 mb-1"></i>
                    <p class="text-xs text-gray-600 font-medium">Upload Back Side</p>
                    <div id="editIdBackFileName" class="text-xs text-orange-500 mt-1 hidden font-medium"></div>
                  </div>
                  <div id="currentIdBack" class="mt-1 text-xs text-gray-500"></div>
                </div>

                <!-- Police Clearance -->
                <div>
                  <label class="block text-xs font-medium text-gray-700 mb-1">Police Clearance</label>
                  <div class="border border-dashed border-gray-300 rounded-lg p-3 text-center hover:border-orange-300 transition cursor-pointer bg-gray-50 hover:bg-orange-50" id="editPoliceClearanceUploadArea">
                    <input type="file" id="editPoliceClearanceUpload" name="police_clearance" class="hidden"
                      accept="image/*">
                    <i class="fi fi-rr-shield-check text-xl text-gray-400 mb-1"></i>
                    <p class="text-xs text-gray-600 font-medium">Upload Police Clearance</p>
                    <div id="editPoliceClearanceFileName" class="text-xs text-orange-500 mt-1 hidden font-medium"></div>
                  </div>
                  <div id="currentPoliceClearance" class="mt-1 text-xs text-gray-500"></div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Modal Footer -->
      <div class="bg-white border-t border-gray-200 px-3 sm:px-4 py-2.5 rounded-b-xl flex items-center justify-end gap-1.5">
        <button type="button" id="cancelEditBtn" class="px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all text-[11px] font-semibold active:scale-95">
          Cancel
        </button>
        <button type="submit" form="editPropertyOwnerForm" id="saveEditBtn" class="px-3 py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all text-[11px] font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5">
          <i class="fi fi-rr-check"></i>
          Save Changes
        </button>
      </div>
    </div>
  </div>

  <!-- Delete User Modal -->
  <div id="deleteUserModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-2">
    <div
      class="bg-white rounded-lg shadow-lg max-w-xs w-full transform transition-all duration-300 scale-95 opacity-0 modal-content relative">
      <button id="closeDeleteModalBtn" type="button" class="absolute top-2 right-2 w-6 h-6 rounded-md border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition flex items-center justify-center">
        <i class="fi fi-rr-cross text-[10px]"></i>
      </button>

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
        <h2 class="text-sm font-bold text-gray-800 mb-1.5">Delete User</h2>
        <p class="text-[11px] text-gray-600 leading-relaxed mb-2.5">
          Permanently delete <span class="font-bold text-gray-800" id="deleteUserName">Olivia Faith</span>? This action
          cannot be undone.
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

  <script src="{{ asset('js/admin/userManagement/propertyOwner.js') }}?v={{ time() }}" defer></script>
  <script src="{{ asset('js/account.js') }}?v={{ time() }}" defer></script>

</body>

</html>
