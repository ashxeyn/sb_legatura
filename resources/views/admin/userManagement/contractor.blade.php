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


</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'Contractors'])

      <div class="p-8">
        <!-- Controls Section -->
        <!-- Controls Section -->
        <div
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
              <i class="fi fi-rr-filter text-gray-500"></i>
              <span>Filter By</span>
            </div>

            <!-- Date Range -->
            <div class="flex items-center gap-2">
              <label class="text-sm font-medium text-gray-700">From:</label>
              <input type="date" id="dateFrom"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
              <label class="text-sm font-medium text-gray-700">To:</label>
              <input type="date" id="dateTo"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
            </div>
          </div>

          <div class="flex items-center gap-4">
            <button id="resetFilterBtn"
              class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
              <i class="fi fi-rr-rotate-left"></i>
              <span>Reset Filter</span>
            </button>

            <button id="addContractorBtn"
              class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-5 py-2 rounded-lg font-medium text-sm shadow-md hover:shadow-lg transition transform hover:scale-105">
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
  <div id="addContractorModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto modal-content">
      <!-- Modal Header -->
      <div
        class="sticky top-0 bg-white border-b border-gray-200 px-8 py-5 flex items-center justify-between rounded-t-2xl z-10">
        <h2 class="text-2xl font-bold text-gray-800">Add New Contractor</h2>
        <button id="closeModalBtn"
          class="text-gray-400 hover:text-gray-600 transition p-2 rounded-lg hover:bg-gray-100">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-8">
        <!-- Profile Picture Section -->
        <div class="flex items-center gap-6 mb-8">
          <div class="relative group">
            <div
              class="w-24 h-24 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center overflow-hidden shadow-lg">
              <i class="fi fi-rr-building text-4xl text-gray-500" id="profileIcon"></i>
              <img id="profilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
            </div>
            <label for="profileUpload"
              class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition transform hover:scale-110">
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
              <input type="text" name="company_name" placeholder="Enter company name"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Email</label>
              <input type="email" name="company_email" placeholder="company@example.com"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date of Incorporation</label>
              <input type="date" name="company_start_date"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Contractor Type</label>
              <select name="contractor_type_id" id="contractorTypeSelect"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                <option value="">Select Type</option>
                @foreach($contractorTypes as $type)
                  <option value="{{ $type->type_id }}">{{ $type->type_name }}</option>
                @endforeach
              </select>
              <input type="text" name="contractor_type_other_text" id="contractorTypeOtherInput"
                placeholder="Please specify type"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition mt-2 hidden">
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Services Offered</label>
              <input type="text" name="services_offered" placeholder="e.g. Plumbing, Electrical, Roofing"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Website <span
                  class="text-gray-400">(optional)</span></label>
              <input type="url" name="company_website" placeholder="https://"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Social Media <span
                  class="text-gray-400">(optional)</span></label>
              <input type="url" name="company_social_media" placeholder="https://"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
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
              <input type="text" name="first_name" placeholder="Enter first name"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name <span
                  class="text-gray-400">(optional)</span></label>
              <input type="text" name="middle_name" placeholder="Enter middle name"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
              <input type="text" name="last_name" placeholder="Enter last name"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Company Email</label>
              <input type="email" name="company_email" placeholder="Enter email address"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
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
                        Default Password: <span
                          class="font-mono font-bold bg-blue-100 px-1 rounded">contractor123@!</span>
                      </p>
                      <p class="text-sm text-blue-600 mt-1">
                        The username will be <span
                          class="font-mono font-bold bg-blue-100 px-1 rounded">contractor_</span> followed by a random
                        4-digit number.
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
                  <select id="contractor_address_province" name="business_address_province"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                    <option value="">Select Province</option>
                    @foreach($provinces as $province)
                      <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">{{ $province['name'] }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                  <select id="contractor_address_city" name="business_address_city"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition"
                    disabled>
                    <option value="">Select City/Municipality</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                  <select id="contractor_address_barangay" name="business_address_barangay"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition"
                    disabled>
                    <option value="">Select Barangay</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Street Address / Unit No.</label>
                  <input type="text" name="business_address_street" placeholder="Enter street address"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Zip Code</label>
                  <input type="text" name="business_address_postal" placeholder="Enter zip code"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
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
              <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Number <span
                  class="text-red-500">*</span></label>
              <input type="text" name="picab_number" placeholder="Enter PCAB number"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Category <span
                  class="text-red-500">*</span></label>
              <select name="picab_category"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                <option value="">Select Category</option>
                @foreach($picabCategories as $category)
                  <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Expiration Date <span
                  class="text-red-500">*</span></label>
              <input type="date" name="picab_expiration_date"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit Number <span
                  class="text-red-500">*</span></label>
              <input type="text" name="business_permit_number" placeholder="Enter permit number"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit City <span
                  class="text-red-500">*</span></label>
              <select id="business_permit_city" name="business_permit_city"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                <option value="">Select City</option>
                @foreach($allCities as $city)
                  <option value="{{ $city['name'] }}">{{ $city['name'] }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit Expiration <span
                  class="text-red-500">*</span></label>
              <input type="date" name="business_permit_expiration"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">TIN Business Registration Number <span
                  class="text-red-500">*</span></label>
              <input type="text" name="tin_business_reg_number" placeholder="Enter TIN/Business Reg. number"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">DTI / SEC Registration <span
                  class="text-red-500">*</span></label>
              <div id="dtiDropzone"
                class="flex items-center justify-center w-full h-[110px] rounded-xl border-2 border-dashed border-orange-300 bg-orange-50 text-orange-600 hover:bg-orange-100 transition-all relative cursor-pointer">
                <input id="dtiUpload" name="dti_sec_registration_photo" type="file" accept="image/*,application/pdf"
                  class="hidden">
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
          <button id="cancelBtn"
            class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
            Cancel
          </button>
          <button id="saveBtn"
            class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg transform hover:scale-105">
            Save Contractor
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Contractor Modal -->
  <div id="editContractorModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto modal-content">
      <!-- Modal Header -->
      <div
        class="sticky top-0 bg-white border-b border-gray-200 px-8 py-5 flex items-center justify-between rounded-t-2xl z-10">
        <h2 class="text-2xl font-bold text-gray-800">Edit Contractor</h2>
        <button id="closeEditModalBtn"
          class="text-gray-400 hover:text-gray-600 transition p-2 rounded-lg hover:bg-gray-100">
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
              <div
                class="w-24 h-24 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center overflow-hidden shadow-lg">
                <i class="fi fi-rr-building text-4xl text-gray-500" id="editProfileIcon"></i>
                <img id="editProfilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
              </div>
              <label for="editProfileUpload"
                class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition transform hover:scale-110">
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
                <input type="text" id="edit_company_name" name="company_name" placeholder="Enter company name"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Company Email</label>
                <input type="email" id="edit_company_email" name="company_email" placeholder="company@example.com"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Incorporation</label>
                <input type="date" id="edit_company_start_date" name="company_start_date"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contractor Type</label>
                <select name="contractor_type_id" id="edit_contractorTypeSelect"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                  <option value="">Select Type</option>
                  @foreach($contractorTypes as $type)
                    <option value="{{ $type->type_id }}">{{ $type->type_name }}</option>
                  @endforeach
                </select>
                <input type="text" name="contractor_type_other_text" id="edit_contractorTypeOtherInput"
                  placeholder="Please specify type"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition mt-2 hidden">
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Services Offered</label>
                <input type="text" id="edit_services_offered" name="services_offered"
                  placeholder="e.g. Plumbing, Electrical, Roofing"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Company Website <span
                    class="text-gray-400">(optional)</span></label>
                <input type="url" id="edit_company_website" name="company_website" placeholder="https://"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Social Media <span
                    class="text-gray-400">(optional)</span></label>
                <input type="url" id="edit_company_social_media" name="company_social_media" placeholder="https://"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
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
                <input type="text" id="edit_first_name" name="first_name" placeholder="Enter first name"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name <span
                    class="text-gray-400">(optional)</span></label>
                <input type="text" id="edit_middle_name" name="middle_name" placeholder="Enter middle name"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                <input type="text" id="edit_last_name" name="last_name" placeholder="Enter last name"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Company Email</label>
                <input type="email" id="edit_company_email" name="company_email" placeholder="Enter email address"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
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
                    <input type="text" id="edit_username" name="username" placeholder="Enter username"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition"
                      readonly>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password <span
                        class="text-gray-400">(Optional)</span></label>
                    <input type="password" id="edit_password" name="password" placeholder="Enter new password"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
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
                    <select id="edit_contractor_address_province" name="business_address_province"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                      <option value="">Select Province</option>
                      @foreach($provinces as $province)
                        <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">
                          {{ $province['name'] }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                    <select id="edit_contractor_address_city" name="business_address_city"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition"
                      disabled>
                      <option value="">Select City/Municipality</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                    <select id="edit_contractor_address_barangay" name="business_address_barangay"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition"
                      disabled>
                      <option value="">Select Barangay</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Street Address / Unit No.</label>
                    <input type="text" id="edit_business_address_street" name="business_address_street"
                      placeholder="Enter street address"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zip Code</label>
                    <input type="text" id="edit_business_address_postal" name="business_address_postal"
                      placeholder="Enter zip code"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
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
                <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Number <span
                    class="text-red-500">*</span></label>
                <input type="text" id="edit_picab_number" name="picab_number" placeholder="Enter PCAB number"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Category <span
                    class="text-red-500">*</span></label>
                <select id="edit_picab_category" name="picab_category"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                  <option value="">Select Category</option>
                  @foreach($picabCategories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PCAB Expiration Date <span
                    class="text-red-500">*</span></label>
                <input type="date" id="edit_picab_expiration_date" name="picab_expiration_date"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit Number <span
                    class="text-red-500">*</span></label>
                <input type="text" id="edit_business_permit_number" name="business_permit_number"
                  placeholder="Enter permit number"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit City <span
                    class="text-red-500">*</span></label>
                <select id="edit_business_permit_city" name="business_permit_city"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                  <option value="">Select City</option>
                  @foreach($allCities as $city)
                    <option value="{{ $city['name'] }}">{{ $city['name'] }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Business Permit Expiration <span
                    class="text-red-500">*</span></label>
                <input type="date" id="edit_business_permit_expiration" name="business_permit_expiration"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">TIN Business Registration Number <span
                    class="text-red-500">*</span></label>
                <input type="text" id="edit_tin_business_reg_number" name="tin_business_reg_number"
                  placeholder="Enter TIN/Business Reg. number"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">DTI / SEC Registration <span
                    class="text-gray-400">(Optional)</span></label>
                <div id="editDtiDropzone"
                  class="flex items-center justify-center w-full h-[110px] rounded-xl border-2 border-dashed border-orange-300 bg-orange-50 text-orange-600 hover:bg-orange-100 transition-all relative cursor-pointer">
                  <input id="editDtiUpload" name="dti_sec_registration_photo" type="file"
                    accept="image/*,application/pdf" class="hidden">
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
            <button id="cancelEditBtn"
              class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
              Cancel
            </button>
            <button id="saveEditBtn"
              class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg transform hover:scale-105">
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Contractor Modal -->
  <div id="deleteContractorModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0 modal-content">
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
          Permanently delete <span class="font-bold text-gray-800" id="deleteContractorName">GTH Builders and
            Developers</span>? This action cannot be undone.
        </p>

        <div class="text-left">
          <label for="deletionReason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Deletion <span
              class="text-red-500">*</span></label>
          <textarea id="deletionReason" rows="3"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-transparent transition resize-none"
            placeholder="Please provide a reason for deletion..."></textarea>
          <p id="deletionReasonError" class="text-red-500 text-xs mt-1 hidden">Reason is required.</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="px-8 pb-8 space-y-3">
        <button id="confirmDeleteBtn"
          class="w-full px-6 py-3.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-xl transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
          <i class="fi fi-rr-trash"></i>
          Delete
        </button>
        <button id="cancelDeleteBtn"
          class="w-full px-6 py-3.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-semibold hover:border-gray-400 hover:shadow-md transform hover:scale-[1.02] active:scale-95">
          Cancel
        </button>
      </div>
    </div>
  </div>


  <script src="{{ asset('js/admin/userManagement/contractor.js') }}?v={{ time() }}" defer></script>

</body>

</html>