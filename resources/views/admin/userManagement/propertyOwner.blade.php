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
      @include('admin.layouts.topnav', ['pageTitle' => 'Property Owners'])

      <div class="p-8">
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

            <button id="addPropertyOwnerBtn" class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-5 py-2 rounded-lg font-medium text-sm shadow-md hover:shadow-lg transition transform hover:scale-105">
                <i class="fi fi-rr-plus text-lg"></i>
                <span>Add Property Owner</span>
            </button>
          </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" id="ownersTableWrap">
            @include('admin.userManagement.partials.ownerTable')
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
              <input type="file" id="profileUpload" name="profile_pic" class="hidden" accept="image/*">
            </label>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-800">Profile Picture</h3>
            <p class="text-sm text-gray-500">Upload a profile photo for the property owner</p>
          </div>
        </div>

        <!-- Personal Information Section -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
            <i class="fi fi-rr-user"></i>
            Personal Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">First name</label>
              <input type="text" name="first_name" placeholder="Enter first name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
              <select name="occupation_id" id="occupationSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                <option value="">Select Occupation</option>
                @foreach($occupations as $occupation)
                    @if(strtolower($occupation->occupation_name) !== 'others')
                        <option value="{{ $occupation->id }}">{{ $occupation->occupation_name }}</option>
                    @endif
                @endforeach
                <option value="others">Others</option>
              </select>
              <input type="text" name="occupation_other" id="occupationOtherInput" placeholder="Please specify occupation" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition mt-2 hidden">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Middle name <span class="text-gray-400">(optional)</span></label>
              <input type="text" name="middle_name" placeholder="Enter middle name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Date of birth</label>
              <input type="date" name="date_of_birth" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Last name</label>
              <input type="text" name="last_name" placeholder="Enter last name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Contact no.</label>
              <input type="tel" name="phone_number" placeholder="Enter contact number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Account Setup & Address Section -->
          <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
                <i class="fi fi-rr-user-gear"></i>
                Account Setup
                </h3>
                <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" placeholder="Enter email address" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                </div>
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
                                Default Password: <span class="font-mono font-bold bg-blue-100 px-1 rounded">owner123@!</span>
                            </p>
                            <p class="text-sm text-blue-600 mt-1">
                                The username will be <span class="font-mono font-bold bg-blue-100 px-1 rounded">owner_</span> followed by a random 4-digit number.
                            </p>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
                <i class="fi fi-rr-map-marker"></i>
                Address
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                        <select id="owner_address_province" name="province" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                            <option value="">Select Province</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">{{ $province['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                        <select id="owner_address_city" name="city" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition" disabled>
                            <option value="">Select City/Municipality</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                        <select id="owner_address_barangay" name="barangay" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition" disabled>
                            <option value="">Select Barangay</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Street Address / Unit No.</label>
                        <input type="text" name="street_address" placeholder="Enter street address" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Zip Code</label>
                        <input type="text" name="zip_code" placeholder="Enter zip code" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                    </div>
                </div>
            </div>
          </div>

          <!-- Verification Documents Section -->
          <div>
            <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
              <i class="fi fi-rr-document"></i>
              Verification Documents
            </h3>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type of Valid ID</label>
                <select name="valid_id_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                  <option value="">Select ID type</option>
                  @foreach($validIds as $validId)
                    <option value="{{ $validId->id }}">{{ $validId->valid_id_name }}</option>
                  @endforeach
                </select>
              </div>

              <!-- Valid ID Front -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Valid ID (Front)</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition cursor-pointer bg-gray-50 hover:bg-orange-50" id="idFrontUploadArea">
                  <input type="file" id="idFrontUpload" name="valid_id_photo" class="hidden" accept="image/*">
                  <i class="fi fi-rr-id-card-clip-alt text-3xl text-gray-400 mb-2"></i>
                  <p class="text-sm text-gray-600 font-medium">Upload Front Side</p>
                  <div id="idFrontFileName" class="text-sm text-orange-500 mt-2 hidden font-medium"></div>
                </div>
              </div>

              <!-- Valid ID Back -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Valid ID (Back)</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition cursor-pointer bg-gray-50 hover:bg-orange-50" id="idBackUploadArea">
                  <input type="file" id="idBackUpload" name="valid_id_back_photo" class="hidden" accept="image/*">
                  <i class="fi fi-rr-id-card-clip-alt text-3xl text-gray-400 mb-2"></i>
                  <p class="text-sm text-gray-600 font-medium">Upload Back Side</p>
                  <div id="idBackFileName" class="text-sm text-orange-500 mt-2 hidden font-medium"></div>
                </div>
              </div>

              <!-- Police Clearance -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Police Clearance</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition cursor-pointer bg-gray-50 hover:bg-orange-50" id="policeClearanceUploadArea">
                  <input type="file" id="policeClearanceUpload" name="police_clearance" class="hidden" accept="image/*">
                  <i class="fi fi-rr-shield-check text-3xl text-gray-400 mb-2"></i>
                  <p class="text-sm text-gray-600 font-medium">Upload Police Clearance</p>
                  <div id="policeClearanceFileName" class="text-sm text-orange-500 mt-2 hidden font-medium"></div>
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
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-8 py-5 flex items-center justify-between rounded-t-2xl z-10">
        <h2 class="text-2xl font-bold text-gray-800">Edit Property Owner</h2>
        <button id="closeEditModalBtn" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-lg hover:bg-gray-100">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-8">
        <form id="editPropertyOwnerForm">
            <input type="hidden" id="edit_user_id" name="user_id">
            <!-- Profile Picture Section -->
            <div class="flex items-center gap-6 mb-8">
            <div class="relative group">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center overflow-hidden shadow-lg">
                <i class="fi fi-rr-user text-4xl text-gray-500" id="editProfileIcon"></i>
                <img id="editProfilePreview" class="w-full h-full object-cover hidden" alt="Profile Preview">
                </div>
                <label for="editProfileUpload" class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition transform hover:scale-110">
                <i class="fi fi-rr-pencil text-sm"></i>
                <input type="file" id="editProfileUpload" name="profile_pic" class="hidden" accept="image/*">
                </label>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Profile Picture</h3>
                <p class="text-sm text-gray-500">Update profile photo for the property owner</p>
            </div>
            </div>

            <!-- Personal Information Section -->
            <div class="mb-6">
            <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
                <i class="fi fi-rr-user"></i>
                Personal Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">First name</label>
                <input type="text" id="edit_first_name" name="first_name" placeholder="Enter first name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                </div>
                <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                <select name="occupation_id" id="edit_occupationSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                    <option value="">Select Occupation</option>
                    @foreach($occupations as $occupation)
                        @if(strtolower($occupation->occupation_name) !== 'others')
                            <option value="{{ $occupation->id }}">{{ $occupation->occupation_name }}</option>
                        @endif
                    @endforeach
                    <option value="others">Others</option>
                </select>
                <input type="text" name="occupation_other" id="edit_occupationOtherInput" placeholder="Please specify occupation" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition mt-2 hidden">
                </div>
                <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Middle name <span class="text-gray-400">(optional)</span></label>
                <input type="text" id="edit_middle_name" name="middle_name" placeholder="Enter middle name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                </div>
                <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of birth</label>
                <input type="date" id="edit_date_of_birth" name="date_of_birth" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                </div>
                <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Last name</label>
                <input type="text" id="edit_last_name" name="last_name" placeholder="Enter last name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                </div>
                <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact no.</label>
                <input type="tel" id="edit_phone_number" name="phone_number" placeholder="Enter contact number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                </div>
            </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Account Setup & Address Section -->
            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
                    <i class="fi fi-rr-user-gear"></i>
                    Account Setup
                    </h3>
                    <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="edit_email" name="email" placeholder="Enter email address" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" id="edit_username" name="username" placeholder="Enter username" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
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
                    Address
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Province</label>
                            <select id="edit_owner_address_province" name="province" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                                <option value="">Select Province</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">{{ $province['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City/Municipality</label>
                            <select id="edit_owner_address_city" name="city" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition" disabled>
                                <option value="">Select City/Municipality</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Barangay</label>
                            <select id="edit_owner_address_barangay" name="barangay" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition" disabled>
                                <option value="">Select Barangay</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Street Address / Unit No.</label>
                            <input type="text" id="edit_street_address" name="street_address" placeholder="Enter street address" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Zip Code</label>
                            <input type="text" id="edit_zip_code" name="zip_code" placeholder="Enter zip code" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification Documents Section -->
            <div>
                <h3 class="text-lg font-semibold text-orange-500 mb-4 flex items-center gap-2">
                <i class="fi fi-rr-document"></i>
                Verification Documents
                </h3>
                <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type of Valid ID</label>
                    <select id="edit_valid_id_id" name="valid_id_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 focus:border-transparent transition">
                    <option value="">Select ID type</option>
                    @foreach($validIds as $validId)
                        <option value="{{ $validId->id }}">{{ $validId->valid_id_name }}</option>
                    @endforeach
                    </select>
                </div>

                <!-- Valid ID Front -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Valid ID (Front)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition cursor-pointer bg-gray-50 hover:bg-orange-50" id="editIdFrontUploadArea">
                    <input type="file" id="editIdFrontUpload" name="valid_id_photo" class="hidden" accept="image/*">
                    <i class="fi fi-rr-id-card-clip-alt text-3xl text-gray-400 mb-2"></i>
                    <p class="text-sm text-gray-600 font-medium">Upload Front Side</p>
                    <div id="editIdFrontFileName" class="text-sm text-orange-500 mt-2 hidden font-medium"></div>
                    </div>
                    <div id="currentIdFront" class="mt-2 text-sm text-gray-500"></div>
                </div>

                <!-- Valid ID Back -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Valid ID (Back)</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition cursor-pointer bg-gray-50 hover:bg-orange-50" id="editIdBackUploadArea">
                    <input type="file" id="editIdBackUpload" name="valid_id_back_photo" class="hidden" accept="image/*">
                    <i class="fi fi-rr-id-card-clip-alt text-3xl text-gray-400 mb-2"></i>
                    <p class="text-sm text-gray-600 font-medium">Upload Back Side</p>
                    <div id="editIdBackFileName" class="text-sm text-orange-500 mt-2 hidden font-medium"></div>
                    </div>
                    <div id="currentIdBack" class="mt-2 text-sm text-gray-500"></div>
                </div>

                <!-- Police Clearance -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Police Clearance</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition cursor-pointer bg-gray-50 hover:bg-orange-50" id="editPoliceClearanceUploadArea">
                    <input type="file" id="editPoliceClearanceUpload" name="police_clearance" class="hidden" accept="image/*">
                    <i class="fi fi-rr-shield-check text-3xl text-gray-400 mb-2"></i>
                    <p class="text-sm text-gray-600 font-medium">Upload Police Clearance</p>
                    <div id="editPoliceClearanceFileName" class="text-sm text-orange-500 mt-2 hidden font-medium"></div>
                    </div>
                    <div id="currentPoliceClearance" class="mt-2 text-sm text-gray-500"></div>
                </div>
                </div>
            </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
            <button type="button" id="cancelEditBtn" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                Cancel
            </button>
            <button type="submit" id="saveEditBtn" class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg transform hover:scale-105">
                Save Changes
            </button>
            </div>
        </form>
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
        <p class="text-gray-600 leading-relaxed mb-4">
          Permanently delete <span class="font-bold text-gray-800" id="deleteUserName">Olivia Faith</span>? This action cannot be undone.
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

  <script src="{{ asset('js/admin/userManagement/propertyOwner.js') }}" defer></script>
  <script src="{{ asset('js/account.js') }}" defer></script>

</body>

</html>
