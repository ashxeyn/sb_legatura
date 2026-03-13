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
  <link rel="stylesheet" href="{{ asset('css/admin/userManagement/contractor_Views.css') }}">

  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>


</head>

<body class="bg-gray-50 text-gray-800 font-sans" data-contractor-id="{{ $contractor->contractor_id ?? '' }}">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'Contractor Details'])

      <!-- PAGE CONTENT -->
      <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-6">
        <!-- Top row: Back + Actions -->
        <div class="flex items-center justify-between">
          <a href="{{ route('admin.userManagement.contractor') }}"
            class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium hover:bg-indigo-50 px-4 py-2 rounded-lg transition-all duration-300 hover:shadow-md hover:-translate-x-1">
            <i class="fi fi-rr-angle-left text-lg"></i>
            <span>Back</span>
          </a>
          <div class="flex items-center gap-3">
            <button onclick="openEditModal({{ $contractor->contractor_id }})"
              class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-all duration-300 text-gray-700 flex items-center gap-2 hover:shadow-md hover:scale-105">
              <i class="fi fi-rr-edit"></i>
              <span>Edit</span>
            </button>
            @if($contractor->is_active == 1)
              <button id="suspendContractorBtn" data-id="{{ $contractor->contractor_id }}"
                class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all duration-300 flex items-center gap-2 hover:shadow-lg hover:scale-105">
                <i class="fi fi-rr-ban"></i>
                <span>Suspend</span>
              </button>
            @else
              <div
                class="px-4 py-2 rounded-lg bg-red-100 text-red-600 font-medium flex items-center gap-2 cursor-default">
                <i class="fi fi-rr-ban"></i>
                <span>Suspended</span>
              </div>
            @endif
          </div>
        </div>

        <!-- Account Profile Section (Full Width) -->
        <section
          class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
          <div
            class="relative h-44 md:h-56 bg-gradient-to-r from-slate-700 via-slate-800 to-slate-900 overflow-hidden group">
            @if(isset($contractor->cover_photo) && $contractor->cover_photo)
              <img src="{{ asset('storage/' . $contractor->cover_photo) }}" alt="Account Banner"
                class="w-full h-full object-cover opacity-40 group-hover:scale-105 transition-transform duration-500">
            @else
              <img src="" alt="Account Banner"
                class="w-full h-full object-cover opacity-40 group-hover:scale-105 transition-transform duration-500">
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
          </div>
          <div class="relative px-6 md:px-8 pb-8 pt-8">

            <div class="mb-6">
              <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-1">Account Profile</h2>
              <p class="text-sm text-gray-600">Contractor Account Information</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Username</label>
                <p class="text-base font-medium text-gray-800">{{ $contractor->username ?? 'N/A' }}</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Account
                  Status</label>
                @if($contractor->is_active == 1)
                  <span
                    class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">Active</span>
                @else
                  <span
                    class="inline-block px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold">Inactive</span>
                @endif
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Member
                  Since</label>
                <p class="text-base font-medium text-gray-800">
                  {{ $contractor->created_at ? \Carbon\Carbon::parse($contractor->created_at)->format('F d, Y') : 'N/A' }}
                </p>
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Bio</label>
                <p class="text-base text-gray-700 leading-relaxed">
                  {{ $contractor->company_description ?? 'No bio provided.' }}
                </p>
              </div>
            </div>
          </div>
        </section>

        <!-- Info grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Left 2/3 -->
          <div class="xl:col-span-2 space-y-6">
            <!-- Company Profile Card -->
            <section
              class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 md:px-8 py-6">
                <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                  <i class="fi fi-sr-building"></i>
                  Company Profile
                </h2>
              </div>
              <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Company Logo -->
                  <div
                    class="md:col-span-2 flex items-center gap-6 p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border-2 border-orange-200">
                    <div
                      class="w-24 h-24 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-orange-200">
                      @if(isset($contractor->profile_pic) && $contractor->profile_pic)
                        <img id="companyLogoImg" src="{{ asset($contractor->profile_pic) }}"
                          alt="{{ $contractor->company_name }}" class="w-full h-full object-cover">
                      @else
                        <i id="companyLogoIcon" class="fi fi-sr-building text-white text-4xl"></i>
                      @endif
                    </div>
                    <div class="flex-1">
                      <h3 class="text-2xl font-bold text-gray-800">{{ $contractor->company_name ?? 'N/A' }}</h3>
                      <p class="text-sm text-gray-600 mt-1">{{ $contractor->company_email ?? 'N/A' }}</p>
                      <p class="text-sm text-orange-600 mt-1 font-semibold">Experience:
                        {{ $contractor->years_of_experience ?? 'N/A' }} Years</p>
                    </div>
                  </div>

                  <!-- Company Information Fields -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Company
                      Name</label>
                    <p class="text-base font-medium text-gray-800">{{ $contractor->company_name ?? 'N/A' }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Owner
                      Name</label>
                    @php
                      // Owner is the contractor's property owner (via owner_id), not in team_members
                      $ownerName = trim(($contractor->first_name ?? '') . ' ' . ($contractor->middle_name ?? '') . ' ' . ($contractor->last_name ?? ''));
                      $ownerName = $ownerName ?: 'N/A';
                    @endphp
                    <p class="text-base font-medium text-gray-800">{{ $ownerName }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Years of
                      Operation</label>
                    <p class="text-base font-medium text-gray-800">{{ $contractor->years_of_experience ?? 'N/A' }} Years
                    </p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Account
                      Type</label>
                    <span
                      class="inline-block px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-semibold">{{ $contractor->contractor_type_name ?? 'N/A' }}</span>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Registration
                      Date</label>
                    <p class="text-base font-medium text-gray-800">
                      {{ $contractor->created_at ? \Carbon\Carbon::parse($contractor->created_at)->format('F d, Y') : 'N/A' }}
                    </p>
                  </div>

                  <!-- Company Website / Socials -->
                  <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">Company
                      Website / Socials</label>
                    <div class="space-y-2">
                      @if($contractor->company_website)
                        <div
                          class="flex items-center gap-2 text-sm text-gray-700 hover:text-orange-600 transition-colors">
                          <i class="fi fi-rr-globe text-orange-500"></i>
                          <a href="{{ $contractor->company_website }}" target="_blank"
                            class="hover:underline">{{ $contractor->company_website }}</a>
                        </div>
                      @endif
                      @if($contractor->company_social_media)
                        <div
                          class="flex items-center gap-2 text-sm text-gray-700 hover:text-orange-600 transition-colors">
                          <i class="fi fi-brands-facebook text-orange-500"></i>
                          <a href="{{ $contractor->company_social_media }}" target="_blank"
                            class="hover:underline">{{ $contractor->company_social_media }}</a>
                        </div>
                      @endif
                      @if(!$contractor->company_website && !$contractor->company_social_media)
                        <p class="text-sm text-gray-500">No website or social media links provided</p>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Company Representative Section -->
            <section
              class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div
                class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 md:px-8 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                  <i class="fi fi-rr-id-badge"></i>
                  Company Representative Information
                </h2>
                <button id="changeRepresentativeBtn"
                  class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg font-medium text-sm shadow-md hover:shadow-lg transition-all flex items-center gap-2 hover:scale-105">
                  <i class="fi fi-rr-refresh"></i>
                  <span>{{ $contractor->representative ? 'Change Representative' : 'Add Representative' }}</span>
                </button>
              </div>
              <div class="p-6 md:p-8">
                @if($contractor->representative)
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Representative Photo -->
                                <div
                                  class="md:col-span-2 flex items-center gap-6 p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border-2 border-blue-200">
                                  <div
                                    class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-blue-200">
                                    @if($contractor->representative->rep_profile_pic)
                                      <img id="repPhotoImg" src="{{ asset('storage/' . $contractor->representative->rep_profile_pic) }}"
                                        alt="Representative Photo" class="w-full h-full object-cover">
                                    @else
                                      <i id="repPhotoIcon" class="fi fi-rr-user text-white text-3xl"></i>
                                    @endif
                                  </div>
                                  <div class="flex-1">
                                    <h3 class="text-xl font-bold text-gray-800">
                                      {{ ($contractor->representative->first_name ?? '') . ' ' .
                  ($contractor->representative->middle_name ?? '') . ' ' .
                  ($contractor->representative->last_name ?? '') }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-1">Company Representative</p>
                                  </div>
                                </div>

                                <!-- Representative Information Fields -->
                                <div>
                                  <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Email
                                    Address</label>
                                  <p class="text-base font-medium text-gray-800">{{ $contractor->representative->rep_email ?? 'N/A' }}
                                  </p>
                                </div>
                                <div>
                                  <label
                                    class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Username</label>
                                  <p class="text-base font-medium text-gray-800">
                                    {{ $contractor->representative->rep_username ?? 'N/A' }}</p>
                                </div>
                              </div>
                @else
                  <div class="text-center py-12">
                    <div
                      class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                      <i class="fi fi-rr-user text-gray-500 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Representative Assigned</h3>
                    <p class="text-sm text-gray-500">Click "Add Representative" button above to assign a company
                      representative.</p>
                  </div>
                @endif
              </div>
            </section>

            <!-- Account Setup & Business Address -->

          </div>

          <!-- Right 1/3: Documents & Services -->
          <div class="space-y-6">
            <!-- Documents Section -->
            <section
              class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                  <i class="fi fi-rr-document"></i>
                  Documents
                </h2>
              </div>
              <div class="p-6 space-y-4">
                <!-- PCAB Information -->
                <div class="space-y-3 pb-4 border-b border-gray-200">
                  <h3 class="text-sm font-bold text-orange-600 flex items-center gap-2">
                    <i class="fi fi-rr-certificate"></i>
                    PCAB License
                  </h3>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">PCAB
                      No.</label>
                    <p class="text-sm font-medium text-gray-800">{{ $contractor->picab_number ?? 'N/A' }}</p>
                  </div>
                  <div>
                    <label
                      class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Category</label>
                    <span
                      class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">{{ $contractor->picab_category ?? 'N/A' }}</span>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Expiration
                      Date</label>
                    <p class="text-sm font-medium text-gray-800">
                      {{ $contractor->picab_expiration_date ? \Carbon\Carbon::parse($contractor->picab_expiration_date)->format('F d, Y') : 'N/A' }}
                    </p>
                  </div>
                </div>

                <!-- Business Permit -->
                <div class="space-y-3 pb-4 border-b border-gray-200">
                  <h3 class="text-sm font-bold text-orange-600 flex items-center gap-2">
                    <i class="fi fi-rr-file-check"></i>
                    Business Permit
                  </h3>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Permit
                      City</label>
                    <p class="text-sm font-medium text-gray-800">{{ $contractor->business_permit_city ?? 'N/A' }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Permit
                      Number</label>
                    <p class="text-sm font-medium text-gray-800">{{ $contractor->business_permit_number ?? 'N/A' }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Expiration
                      Date</label>
                    <p class="text-sm font-medium text-gray-800">
                      {{ $contractor->business_permit_expiration ? \Carbon\Carbon::parse($contractor->business_permit_expiration)->format('F d, Y') : 'N/A' }}
                    </p>
                  </div>
                </div>

                <!-- TIN & DTI/SEC -->
                <div class="space-y-3">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">TIN Business
                      Registration No.</label>
                    <p class="text-sm font-medium text-gray-800">{{ $contractor->tin_business_reg_number ?? 'N/A' }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">DTI / SEC
                      Registration</label>
                    @if($contractor->dti_sec_registration_photo)
                      <button type="button" data-doc-src="{{ asset('storage/' . $contractor->dti_sec_registration_photo) }}"
                        class="open-doc-btn inline-flex items-center gap-2 px-3 py-2 bg-orange-50 hover:bg-orange-100 text-orange-600 rounded-lg text-sm font-medium transition-all hover:shadow-md group">
                        <i class="fi fi-rr-file-pdf text-red-500"></i>
                        <span>View Document</span>
                        <span class="text-xs text-gray-500 group-hover:text-orange-500">•
                          {{ $contractor->verification_status ?? 'Pending' }}</span>
                      </button>
                    @else
                      <p class="text-sm text-gray-500">No document uploaded</p>
                    @endif
                  </div>
                </div>
              </div>
            </section>

            <!-- Specialties Section -->
            <section
              class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                  <i class="fi fi-rr-tools"></i>
                  Services Offered
                </h2>
              </div>
              <div class="p-6">
                @if($contractor->services_offered)
                  <p class="text-sm text-gray-700 leading-relaxed">{{ $contractor->services_offered }}</p>
                @else
                  <p class="text-sm text-gray-500">No services listed</p>
                @endif
              </div>
            </section>

            <!-- Business Address Section -->
            <section
              class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-3">
                  <i class="fi fi-rr-marker"></i>
                  Business Address
                </h2>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Complete
                      Address</label>
                    <p class="text-base text-gray-800">{{ $contractor->business_address ?? 'N/A' }}</p>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>

        <!-- Team Members Section (Full Width) -->
        <section
          class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
          <div
            class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <i class="fi fi-rr-users-alt text-gray-800 text-xl"></i>
              <h2 class="text-xl font-bold text-gray-800">Team Members</h2>
            </div>
            <button id="addTeamMemberBtn"
              class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-4 py-2 rounded-lg font-medium text-sm shadow-md hover:shadow-lg transition transform hover:scale-105">
              <i class="fi fi-rr-plus text-sm"></i>
              <span>Add Member</span>
            </button>
          </div>

          <!-- Tabs -->
          <div class="border-b border-gray-200">
            <div class="flex px-6">
              <button
                class="team-tab active px-4 py-3 text-sm font-semibold border-b-2 border-orange-500 text-orange-600 transition-all"
                data-tab="active">
                Active
              </button>
              <button
                class="team-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-orange-600 hover:border-orange-300 transition-all"
                data-tab="pending">
                Pending Invitations
              </button>
              <button
                class="team-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-orange-600 hover:border-orange-300 transition-all"
                data-tab="cancelled">
                Cancelled Invitations
              </button>
              <button
                class="team-tab px-4 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-600 hover:text-orange-600 hover:border-orange-300 transition-all"
                data-tab="deactivated">
                Suspended Accounts
              </button>
            </div>
          </div>

          <!-- Team Members Table -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Member
                  </th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    Position</th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Date
                    Added</th>
                  <th id="statusColumnHeader"
                    class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status
                  </th>
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200" id="teamMembersTable">
                @forelse($contractor->team_members ?? [] as $index => $member)
                                <?php
                  // Generate color based on index
                  $colors = ['purple', 'blue', 'green', 'red', 'yellow', 'pink', 'indigo', 'orange'];
                  $color = $colors[$index % count($colors)];

                  // Generate initials
                  $fname = $member->first_name ?? '';
                  $lname = $member->last_name ?? '';
                  $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));

                  // Determine status based on user requirements:
                  // - active: (default logic if none of the below match, assumed is_active=1)
                  // - pending: is_active = 0 AND deletion_reason is NULL
                  // - cancelled: is_active = 0 AND deletion_reason IS NOT NULL
                  // - deactivated: is_active = 0 AND is_suspended = 1 AND suspension_until IS NOT NULL AND suspension_reason IS NOT NULL

                  if ($member->is_active == 0 && $member->is_suspended == 1 && !empty($member->suspension_until) && !empty($member->suspension_reason)) {
                    $statusClass = 'team-member-row hidden';
                    $dataStatus = 'deactivated';
                  } elseif ($member->is_active == 0 && !empty($member->deletion_reason)) {
                    $statusClass = 'team-member-row hidden';
                    $dataStatus = 'cancelled';
                  } elseif ($member->is_active == 0 && empty($member->deletion_reason)) {
                    // Assuming this also means they are not deactivated based on order of checks, or deactivated is a stronger state
                    // To be strictly sticking to "If pending invitations, the users shown here are users in contractor staff that are is_active = 0 AND deletion_reason is NULL!"
                    $statusClass = 'team-member-row hidden';
                    $dataStatus = 'pending';
                  } else {
                    // Default to active if none of the above conditions for inactive states are met
                    $statusClass = 'team-member-row';
                    $dataStatus = 'active';
                  }
                                  ?>
                                <tr class="hover:bg-gray-50 transition-all duration-200 group {{ $statusClass }}"
                                  data-status="{{ $dataStatus }}" data-email="{{ $member->email ?? '' }}" data-contact="">
                                  <td class="px-6 py-4">
                                    <div class="flex items-center gap-3 {{ $member->is_suspended ? 'opacity-60' : '' }}">
                                      <div
                                        class="w-10 h-10 rounded-full bg-gradient-to-br from-{{ $color }}-400 to-{{ $color }}-600 flex items-center justify-center overflow-hidden shadow-md group-hover:shadow-lg transition-all group-hover:scale-110">
                                        @if($member->profile_pic)
                                          <img src="{{ asset('storage/' . $member->profile_pic) }}" alt="{{ $fname . ' ' . $lname }}"
                                            class="w-full h-full object-cover">
                                        @else
                                          <span class="text-white font-bold text-sm">{{ $initials }}</span>
                                        @endif
                                      </div>
                                      <span
                                        class="font-medium {{ $member->is_suspended ? 'text-gray-600' : 'text-gray-800 group-hover:text-orange-600' }} transition">
                                        {{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}
                                      </span>
                                    </div>
                                  </td>
                                  <td class="px-6 py-4 text-center text-sm text-gray-600">
                                    @php
                                      $displayRole = ucfirst($member->company_role ?? 'N/A');
                                      if ($member->company_role === 'others' && !empty($member->role_if_others)) {
                                        $displayRole = ucfirst($member->role_if_others);
                                      }
                                    @endphp
                                    {{ $displayRole }}
                                  </td>
                                  <td class="px-6 py-4 text-center text-sm text-gray-600">
                                    {{ $member->created_at ? \Carbon\Carbon::parse($member->created_at)->format('M d, Y') : 'N/A' }}
                                  </td>
                                  <td class="px-6 py-4 text-center status-cell">
                      @if($dataStatus == 'deactivated')
                        <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hidden">
                          Suspended
                        </span>
                        <span class="deletion-reason text-sm text-gray-700">{{ $member->suspension_reason ?? 'No reason provided' }}</span>
                      @elseif($dataStatus == 'active')
                        <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 transition-all duration-200 hover:scale-110 hover:shadow-md">
                          Active
                        </span>
                      @elseif($dataStatus == 'cancelled')
                        <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 hidden">
                          Cancelled Invitation
                        </span>
                      @else
                        <span class="status-badge inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 hidden">
                          Pending Invitation
                        </span>
                      @endif
                                  </td>
                                  <td class="px-6 py-4">
                      <div class="flex items-center justify-center gap-2">
                        @if($dataStatus == 'deactivated')
                          <button class="team-reactivate-btn p-2 rounded-lg hover:bg-green-50 transition-all group/btn" title="Reactivate Account"
                                  data-member-id="{{ $member->staff_id }}"
                                  data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
                            <i class="fi fi-rr-check-circle text-green-600 group-hover/btn:scale-110 transition-transform"></i>
                          </button>
                        @elseif($dataStatus == 'active')
                          <button class="team-edit-btn p-2 rounded-lg hover:bg-orange-50 transition-all group/btn" title="Edit Member" data-member-id="{{ $member->staff_id }}">
                            <i class="fi fi-rr-pencil text-orange-600 group-hover/btn:scale-110 transition-transform"></i>
                          </button>
                          <button class="team-deactivate-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn" title="Suspend Account"
                                  data-member-id="{{ $member->staff_id }}"
                                  data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
                            <i class="fi fi-rr-ban text-red-600 group-hover/btn:scale-110 transition-transform"></i>
                          </button>
                        @elseif($dataStatus == 'pending')
                          <button class="team-cancel-invitation-btn p-2 rounded-lg hover:bg-red-50 transition-all group/btn" title="Cancel Invitation"
                                  data-member-id="{{ $member->staff_id }}"
                                  data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
                            <i class="fi fi-rr-cross-circle text-red-600 group-hover/btn:scale-110 transition-transform"></i>
                          </button>
                        @elseif($dataStatus == 'cancelled')
                          <button class="team-reapply-invitation-btn p-2 rounded-lg hover:bg-green-50 transition-all group/btn" title="Reapply Invitation"
                                  data-member-id="{{ $member->staff_id }}"
                                  data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
                            <i class="fi fi-rr-rotate-right text-green-600 group-hover/btn:scale-110 transition-transform"></i>
                          </button>
                        @endif
                      </div>
                    </td>
                                </tr>
                @empty
                  <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                      No team members found
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </section>
      </div>

    </main>
  </div>

  <!-- Suspend Account Modal -->
  <div id="suspendAccountModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[85vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div
        class="sticky top-0 bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-exclamation text-white text-xl"></i>
          </div>
          <h2 class="text-xl font-bold text-white">Suspend Account</h2>
        </div>
        <button id="closeSuspendModalBtn"
          class="text-white hover:text-red-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-xl"></i>
        </button>
      </div>

      <!-- Modal Body - Scrollable -->
      <div class="overflow-y-auto max-h-[calc(85vh-140px)] p-6 space-y-5">
        <!-- Warning Message -->
        <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 space-y-3">
          <div class="flex items-start gap-3">
            <div class="w-9 h-9 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 animate-pulse">
              <i class="fi fi-rr-shield-exclamation text-white"></i>
            </div>
            <div class="flex-1">
              <h3 class="font-bold text-gray-800 mb-1">Confirm Account Suspension</h3>
              <p class="text-gray-700 text-sm leading-relaxed">
                You are about to suspend this contractor account. This will immediately restrict their access to the
                platform and all active projects.
              </p>
            </div>
          </div>

          <!-- Contractor Info Card -->
          <div class="bg-white rounded-lg p-3 border border-red-200 space-y-2">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 text-white font-bold flex items-center justify-center shadow-md">
                {{ strtoupper(substr($contractor->company_name ?? 'C', 0, 1) . substr($contractor->company_name ?? 'C', strpos($contractor->company_name ?? ' ', ' ') + 1, 1)) }}
              </div>
              <div>
                <p class="font-bold text-gray-800">{{ $contractor->company_name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-600">{{ $contractor->username ?? 'N/A' }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200">
              <div class="text-center">
                <p class="text-xs text-gray-500">Active Projects</p>
                <p class="text-lg font-bold text-gray-800">{{ $contractor->active_projects_count ?? 0 }}</p>
              </div>
              <div class="text-center">
                <p class="text-xs text-gray-500">Member Since</p>
                <p class="text-sm font-semibold text-gray-800">
                  {{ date('M Y', strtotime($contractor->created_at ?? now())) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Reason Input -->
        <div>
          <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
            <i class="fi fi-rr-edit text-red-500"></i>
            Reason for Suspension <span class="text-red-500">*</span>
          </label>
          <textarea id="suspendReason" rows="3"
            placeholder="Please provide a detailed reason for suspending this account..."
            class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition-all hover:border-red-300 bg-white resize-none text-sm"></textarea>
          <p id="suspendReasonError" class="text-red-500 text-xs mt-1 hidden"></p>
          <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
            <i class="fi fi-rr-info"></i>
            This reason will be recorded and may be shared with the contractor.
          </p>
        </div>

        <!-- Suspension Options -->
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
            <i class="fi fi-rr-calendar text-red-500"></i>
            Suspension Duration
          </label>
          <div class="grid grid-cols-2 gap-2">
            <label class="relative cursor-pointer group">
              <input type="radio" name="suspensionDuration" value="temporary" class="peer sr-only" checked>
              <div
                class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-red-500 peer-checked:bg-red-50 transition-all hover:border-red-300 group-hover:shadow-md">
                <p class="font-semibold text-gray-800 text-sm">Temporary</p>
                <p class="text-xs text-gray-600">Can be reactivated</p>
              </div>
            </label>
            <label class="relative cursor-pointer group">
              <input type="radio" name="suspensionDuration" value="permanent" class="peer sr-only">
              <div
                class="p-3 border-2 border-gray-200 rounded-lg peer-checked:border-red-500 peer-checked:bg-red-50 transition-all hover:border-red-300 group-hover:shadow-md">
                <p class="font-semibold text-gray-800 text-sm">Permanent</p>
                <p class="text-xs text-gray-600">Account deletion</p>
              </div>
            </label>
          </div>

          <!-- Date Picker for Temporary Suspension -->
          <div id="suspensionDateContainer" class="mt-3 transition-all duration-300 overflow-hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Suspension Until</label>
            <input type="date" id="suspensionDate"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm"
              min="{{ date('Y-m-d', strtotime('+1 day')) }}">
            <p id="suspensionDateError" class="text-red-500 text-xs mt-1 hidden"></p>
            <p class="text-xs text-gray-500 mt-1">The account will be automatically reactivated after this date.</p>
          </div>
        </div>

        <!-- Consequences Warning -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded-r-lg">
          <div class="flex gap-2">
            <i class="fi fi-rr-triangle-warning text-yellow-600 flex-shrink-0 mt-0.5"></i>
            <div class="text-sm text-gray-700 space-y-1">
              <p class="font-semibold">This action will:</p>
              <ul class="list-disc list-inside space-y-0.5 text-xs">
                <li>Immediately lock the contractor out of their account</li>
                <li>Pause all active projects and bids</li>
                <li>Send a notification to the contractor</li>
                <li>Create an audit log entry</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons - Fixed at Bottom -->
      <div
        class="sticky bottom-0 bg-white border-t-2 border-gray-200 px-6 py-4 rounded-b-2xl flex items-center justify-end gap-3">
        <button id="cancelSuspendBtn"
          class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95 text-sm">
          Cancel
        </button>
        <button id="confirmSuspendBtn"
          class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2 text-sm">
          <i class="fi fi-rr-shield-check"></i>
          Suspend Account
        </button>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Incorporation</label>
                <input type="date" id="edit_company_start_date" name="company_start_date"
                  max="{{ date('Y-m-d', strtotime('-1 day')) }}"
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
                  Current: <button type="button" data-doc-src="{{ asset('storage/' . ($contractor->dti_sec_registration_photo ?? '')) }}" class="open-doc-btn text-orange-600 hover:underline font-medium">View File</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
            <button type="button" id="cancelEditBtn"
              class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
              Cancel
            </button>
            <button type="button" id="saveEditBtn"
              class="px-6 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition font-medium shadow-md hover:shadow-lg transform hover:scale-105">
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  </div>

  <!-- Add Team Member Modal -->
  <div id="addTeamMemberModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div
        class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 flex items-center justify-between rounded-t-2xl z-10 shadow-lg">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
          <i class="fi fi-rr-user-add"></i>
          Add Team Member
        </h2>
        <button id="closeAddTeamMemberBtn"
          class="text-white hover:text-orange-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(90vh-80px)] p-6 md:p-8 space-y-6">
        <!-- Team Member Selection -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-user"></i>
            Select Property Owner
          </h3>

          <div class="grid grid-cols-1 gap-4">
            <!-- Property Owner Selection -->
            <div class="form-group">
              <label class="form-label">Search Property Owners <span class="text-red-500">*</span></label>
              <div class="relative">
                <input type="text" id="teamMemberOwnerSearch" placeholder="Search by name or username..."
                  class="form-input pr-10" autocomplete="off">
                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                  <i class="fi fi-rr-search text-gray-400"></i>
                </div>
                <div id="teamMemberOwnerDropdown"
                  class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                  <div id="teamMemberOwnerList" class="py-1">
                    <!-- Options will be populated here -->
                  </div>
                  <div id="teamMemberOwnerLoading" class="p-4 text-center text-gray-500 hidden">
                    <i class="fi fi-rr-spinner animate-spin"></i> Loading...
                  </div>
                  <div id="teamMemberOwnerEmpty" class="p-4 text-center text-gray-500 hidden">
                    No available property owners found
                  </div>
                </div>
              </div>
              <span id="teamMemberOwnerError" class="text-xs text-red-500 mt-1 hidden"></span>

              <!-- Representative Limit Message -->
              <div id="representativeLimitMessage" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-700">
                  <i class="fi fi-rr-info mr-1"></i>
                  Representative already selected. To change, remove the selected user below.
                </p>
              </div>

              <p class="text-xs text-gray-500 mt-1">Only verified property owners not linked to any contractor will
                appear</p>
            </div>

            <!-- Selected Members List -->
            <div id="selectedMembersList" class="hidden">
              <label class="form-label">Selected Members (<span id="selectedMembersCount">0</span>)</label>
              <div id="selectedMembersContainer"
                class="space-y-2 max-h-60 overflow-y-auto p-3 bg-gray-50 rounded-lg border border-gray-200">
                <!-- Selected members will appear here -->
              </div>
            </div>
          </div>
        </div>

        <!-- Role Selection -->
        <div id="roleSelectionSection" class="hidden">
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-briefcase"></i>
            Assign Role
          </h3>
          <div class="grid grid-cols-1 gap-4">
            <div class="form-group">
              <label class="form-label">Role <span class="text-red-500">*</span></label>
              <select id="teamMemberRole" class="form-input">
                <option value="" disabled selected>Select Role</option>
                <option value="manager">Manager</option>
                <option value="engineer">Engineer</option>
                <option value="architect">Architect</option>
                <option value="others">Others</option>
              </select>
              <span id="teamMemberRoleError" class="text-xs text-red-500 mt-1 hidden"></span>
            </div>
            <div class="form-group hidden" id="teamMemberRoleOtherGroup">
              <label class="form-label">Specify Role <span class="text-red-500">*</span></label>
              <input type="text" id="teamMemberRoleOther" placeholder="e.g., Consultant, Surveyor"
                class="form-input">
              <span id="teamMemberRoleOtherError" class="text-xs text-red-500 mt-1 hidden"></span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between gap-3 pt-6 border-t-2 border-gray-200">
          <button id="backToRepresentativeModalBtn"
            class="px-6 py-3 border-2 border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95 flex items-center gap-2 hidden">
            Back
          </button>
          <div class="flex items-center gap-3 ml-auto">
            <button id="cancelAddTeamMemberBtn"
              class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95">
              Cancel
            </button>
            <button id="saveTeamMemberBtn"
              class="px-8 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2">
              <i class="fi fi-rr-disk"></i>
              Add Member
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Team Member Modal -->
  <div id="editTeamMemberModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div
        class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 flex items-center justify-between rounded-t-2xl z-10 shadow-lg">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
          <i class="fi fi-rr-edit"></i>
          Edit Team Member
        </h2>
        <button id="closeEditTeamMemberBtn"
          class="text-white hover:text-orange-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(90vh-80px)] p-6 md:p-8 space-y-6">
        <!-- Member Information (Read-only) -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-user"></i>
            Member Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" id="editTeamMemberContractorUserId">
            <div class="form-group">
              <label class="form-label">First Name</label>
              <p class="text-base font-medium text-gray-800" id="editTeamMemberFirstNameDisplay">-</p>
            </div>
            <div class="form-group">
              <label class="form-label">Middle Name</label>
              <p class="text-base font-medium text-gray-800" id="editTeamMemberMiddleNameDisplay">-</p>
            </div>
            <div class="form-group">
              <label class="form-label">Last Name</label>
              <p class="text-base font-medium text-gray-800" id="editTeamMemberLastNameDisplay">-</p>
            </div>
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <p class="text-base font-medium text-gray-800" id="editTeamMemberEmailDisplay">-</p>
            </div>
            <div class="form-group">
              <label class="form-label">Username</label>
              <p class="text-base font-medium text-gray-800" id="editTeamMemberUsernameDisplay">-</p>
            </div>
          </div>
        </div>

        <!-- Role Information -->
        <div>
          <h3 class="text-lg font-bold text-orange-600 mb-4 flex items-center gap-2 pb-2 border-b-2 border-orange-200">
            <i class="fi fi-rr-briefcase"></i>
            Role Information
          </h3>
          <div class="grid grid-cols-1 gap-4">
            <div class="form-group">
              <label class="form-label">Role <span class="text-red-500">*</span></label>
              <select id="editTeamMemberRole" class="form-input">
                <option value="" disabled>Select Role</option>
                <option value="manager">Manager</option>
                <option value="engineer">Engineer</option>
                <option value="architect">Architect</option>
                <option value="representative">Representative</option>
                <option value="others">Others</option>
              </select>
              <span id="editRoleError" class="text-xs text-red-500 mt-1 hidden"></span>
            </div>
            <div class="form-group hidden" id="editRoleOtherDiv">
              <label class="form-label">Specify Role <span class="text-red-500">*</span></label>
              <input type="text" id="editTeamMemberRoleOther" placeholder="e.g., Consultant, Surveyor"
                class="form-input">
              <span id="editRoleOtherError" class="text-xs text-red-500 mt-1 hidden"></span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t-2 border-gray-200">
          <button id="cancelEditTeamMemberBtn"
            class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95">
            Cancel
          </button>
          <button id="saveEditTeamMemberBtn"
            class="px-8 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2">
            <i class="fi fi-rr-disk"></i>
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Suspend Team Member Modal -->
  <div id="deactivateTeamMemberModal"
    class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0 modal-content overflow-hidden">

      <!-- Modal Header -->
      <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-5 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
          <i class="fi fi-rr-ban"></i>
          Suspend Team Member
        </h2>
        <button id="closeDeactivateTeamMemberBtn"
          class="text-white hover:text-red-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 space-y-4">
        <!-- Member Name -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <p class="text-sm text-red-600 font-semibold mb-1">Suspending Member:</p>
          <p class="text-lg font-bold text-gray-900" id="deactivateTeamMemberName">-</p>
        </div>

        <!-- Suspension Reason -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Reason for Suspension <span class="text-red-500">*</span>
          </label>
          <textarea id="deactivateTeamMemberReason" rows="3"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition resize-none text-sm"
            placeholder="Provide a detailed reason for suspension..."></textarea>
          <span id="deactivateReasonError" class="text-xs text-red-500 mt-1 hidden"></span>
        </div>

        <!-- Suspension Duration -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-3">Suspension Duration</label>
          <div class="space-y-3">
            <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-all">
              <input type="radio" name="suspensionDurationTeamMember" value="temporary" checked
                class="w-4 h-4 text-red-600 focus:ring-red-500">
              <span class="ml-3 text-sm font-medium text-gray-700">Temporary Suspension</span>
            </label>
            <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-all">
              <input type="radio" name="suspensionDurationTeamMember" value="permanent"
                class="w-4 h-4 text-red-600 focus:ring-red-500">
              <span class="ml-3 text-sm font-medium text-gray-700">Permanent Suspension</span>
            </label>
          </div>
        </div>

        <!-- Suspension Date (shown only for temporary) -->
        <div id="suspensionDateContainerTeamMember" class="transition-all duration-300 opacity-100 visible">
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Suspension Until <span class="text-red-500">*</span>
          </label>
          <input type="date" id="suspensionDateTeamMember"
            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition text-sm"
            min="{{ date('Y-m-d', strtotime('+1 day')) }}">
          <span id="suspensionDateErrorTeamMember" class="text-xs text-red-500 mt-1 hidden"></span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
        <button id="cancelDeactivateTeamMemberBtn"
          class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-all font-semibold">
          Cancel
        </button>
        <button id="confirmDeactivateTeamMemberBtn"
          class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl flex items-center gap-2">
          <i class="fi fi-rr-ban"></i>
          Suspend Member
        </button>
      </div>
    </div>
  </div>

  <!-- Reactivate Team Member Modal -->
  <div id="reactivateTeamMemberModal"
    class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 hidden items-center justify-center p-4 animate-fadeIn">
    <div
      class="bg-white rounded-3xl shadow-2xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0 modal-content overflow-hidden">
      <!-- Icon Section with Gradient Background -->
      <div class="flex justify-center pt-12 pb-8 bg-gradient-to-b from-green-50 to-white relative">
        <div
          class="w-32 h-32 bg-gradient-to-br from-green-100 to-green-50 rounded-full flex items-center justify-center relative animate-pulse-slow">
          <div class="absolute inset-0 bg-green-200 rounded-full animate-ping opacity-30"></div>
          <div
            class="relative w-24 h-24 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center shadow-xl transform hover:scale-110 transition-transform duration-300">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
              <i class="fi fi-rr-user-check text-white text-4xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Section -->
      <div class="px-8 pb-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">Reactivate User</h2>
        <p class="text-gray-600 leading-relaxed text-base mb-2">
          Are you sure you want to reactivate
        </p>
        <p class="text-xl font-bold text-gray-900 mb-1" id="reactivateTeamMemberName">Robert Garcia</p>
        <p class="text-sm text-gray-500">They will regain access to their account.</p>
      </div>

      <!-- Action Buttons -->
      <div class="px-8 pb-8 space-y-3">
        <button id="confirmReactivateTeamMemberBtn"
          class="w-full px-6 py-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-2xl transition-all font-bold shadow-xl hover:shadow-2xl transform hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2 text-base">
          <i class="fi fi-rr-user-check"></i>
          Reactivate
        </button>
        <button id="cancelReactivateTeamMemberBtn"
          class="w-full px-6 py-4 bg-white border-2 border-gray-200 text-gray-700 rounded-2xl hover:bg-gray-50 transition-all font-semibold hover:border-gray-300 hover:shadow-md transform hover:scale-[1.02] active:scale-95 text-base">
          Cancel
        </button>
      </div>
    </div>
  </div>

  <!-- Change Representative Modal -->
  <div id="changeRepresentativeModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[85vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div
        class="sticky top-0 bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-5 flex items-center justify-between rounded-t-2xl z-10 shadow-lg">
        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
          <i class="fi fi-rr-refresh"></i>
          Change Company Representative
        </h2>
        <button id="closeChangeRepresentativeBtn"
          class="text-white hover:text-blue-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
          <i class="fi fi-rr-cross text-2xl"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(85vh-140px)] p-6 space-y-4">
        <!-- Current Representative Info -->
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
          <div class="flex items-center gap-3 mb-2">
            <i class="fi fi-rr-info text-blue-600 text-lg"></i>
            <h3 class="font-bold text-gray-800">Current Representative</h3>
          </div>
          @if($contractor->representative)
                  <div class="flex items-center gap-4 p-3 bg-white rounded-lg border border-blue-200">
                    <div
                      class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold shadow-md">
                      {{ strtoupper(substr($contractor->representative->first_name ?? '', 0, 1) . substr($contractor->representative->last_name ?? '', 0, 1)) }}
                    </div>
                    <div>
                      <p class="font-semibold text-gray-800">
                        {{ ($contractor->representative->first_name ?? '') . ' ' .
            ($contractor->representative->middle_name ?? '') . ' ' .
            ($contractor->representative->last_name ?? '') }}
                      </p>
                      <p class="text-sm text-gray-600">
                        {{ ucfirst($contractor->representative->company_role ?? 'Representative') }}</p>
                    </div>
                  </div>
          @else
            <div class="p-3 bg-white rounded-lg border border-blue-200">
              <p class="text-sm text-gray-600 text-center">No representative assigned</p>
            </div>
          @endif
        </div>

        <!-- Search Bar -->
        <div class="relative">
          <input type="text" id="searchTeamMember" placeholder="Search team members by name or position..."
            class="w-full px-4 py-3 pl-11 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all">
          <i class="fi fi-rr-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>

        <!-- Team Members List -->
        <div>
          <h3 class="text-sm font-bold text-gray-600 uppercase tracking-wider mb-3">Select from Existing Team Members
          </h3>
          <div class="space-y-2 max-h-96 overflow-y-auto" id="teamMembersList">
            @php
              $teamMembers = collect($contractor->team_members ?? [])->filter(function ($member) {
                return $member->company_role !== 'representative' && $member->is_active == 1;
              });
            @endphp

            @if($teamMembers->count() > 0)
              @foreach($teamMembers as $member)
                @php
                  $initials = strtoupper(substr($member->first_name ?? '', 0, 1) . substr($member->last_name ?? '', 0, 1));
                  $fullName = trim(($member->first_name ?? '') . ' ' . ($member->middle_name ?? '') . ' ' . ($member->last_name ?? ''));
                  $role = $member->company_role === 'others' ? ($member->role_if_others ?? 'Staff') : ucfirst($member->company_role ?? 'Staff');
                  $colors = ['from-purple-500 to-purple-600', 'from-blue-500 to-blue-600', 'from-green-500 to-green-600', 'from-red-500 to-red-600', 'from-yellow-500 to-yellow-600'];
                  $colorIndex = ord($initials[0]) % count($colors);
                @endphp
                <div
                  class="team-member-option flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all cursor-pointer group"
                  data-member-id="{{ $member->staff_id }}" data-member-name="{{ $fullName }}"
                  data-member-position="{{ $role }}">
                  <div class="flex items-center gap-4">
                    <div
                      class="w-12 h-12 rounded-full bg-gradient-to-br {{ $colors[$colorIndex] }} flex items-center justify-center text-white font-bold shadow-md group-hover:scale-110 transition-transform">
                      {{ $initials }}
                    </div>
                    <div>
                      <p class="font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $fullName }}</p>
                      <p class="text-sm text-gray-600">{{ $role }}</p>
                    </div>
                  </div>
                  <i class="fi fi-rr-check-circle text-2xl text-gray-300 group-hover:text-blue-500 transition-colors"></i>
                </div>
              @endforeach
            @else
              <div class="text-center py-6">
                <i class="fi fi-rr-users text-gray-300 text-3xl mb-2"></i>
                <p class="text-gray-500">No team members available</p>
              </div>
            @endif
          </div>
        </div>

        <!-- Warning Note -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded-r-lg">
          <div class="flex gap-2">
            <i class="fi fi-rr-triangle-warning text-yellow-600 flex-shrink-0 mt-0.5"></i>
            <div class="text-sm text-gray-700">
              <p class="font-semibold mb-1">Note:</p>
              <p>Changing the company representative will update all official documents and communication channels. The
                current representative will be notified of this change.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div
        class="sticky bottom-0 bg-white border-t-2 border-gray-200 px-6 py-4 rounded-b-2xl flex items-center justify-end gap-3">
        <button id="cancelChangeRepresentativeBtn"
          class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95">
          Cancel
        </button>
        <button id="confirmChangeRepresentativeBtn"
          class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          disabled>
          <i class="fi fi-rr-check"></i>
          Assign Representative
        </button>
      </div>
    </div>
  </div>

  <!-- Cancel Invitation Modal -->
  <div id="cancelInvitationModal"
    class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 hidden items-center justify-center p-4 animate-fadeIn">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all duration-300 scale-95 opacity-0 modal-content overflow-hidden border border-gray-200">

      <!-- Modal Header -->
      <div class="px-6 py-4 flex items-center justify-between border-b border-red-100 bg-red-50">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 shadow-sm border border-red-200">
            <i class="fi fi-rr-trash mt-1"></i>
          </div>
          <h2 class="text-lg font-bold text-red-800">Cancel Invitation</h2>
        </div>
      </div>

      <!-- Modal Content -->
      <div class="px-6 py-5 border-b border-gray-100 bg-white">
        <p class="text-gray-600 text-sm mb-4">Are you sure you want to cancel this team member invitation? Please provide a reason below.</p>

        <!-- Member Info -->
        <div class="bg-gray-50 rounded bg-opacity-50 p-3 mb-4 text-left border border-gray-200 flex items-center gap-3 shadow-inner">
          <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-gray-500 shadow-sm border border-gray-200">
            <i class="fi fi-rr-user mt-1"></i>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase font-semibold">Member</p>
            <p class="font-bold text-gray-800 text-sm" id="cancelMemberName">-</p>
          </div>
        </div>

        <!-- Reason Input -->
        <div class="mb-1">
          <label class="block text-sm font-semibold text-gray-700 mb-1">Reason for Cancellation <span class="text-red-500">*</span></label>
          <textarea id="cancelInvitationReason" placeholder="Enter reason for canceling this invitation..."
            class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-red-500 outline-none transition-all resize-none text-sm shadow-inner bg-gray-50 focus:bg-white"
            rows="3"></textarea>
          <span id="cancelReasonError" class="text-xs text-red-500 mt-1 hidden"></span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
        <button id="closeCancelInvitationBtn"
          class="px-5 py-2 text-sm border border-gray-300 text-gray-700 rounded hover:bg-gray-100 transition-all font-semibold shadow-sm flex items-center gap-2">
          Keep Invitation
        </button>
        <button id="confirmCancelInvitationBtn"
          class="px-5 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition-all font-semibold shadow-sm flex items-center gap-2 transform active:scale-95">
          <i class="fi fi-rr-trash"></i>
          Cancel Invitation
        </button>
      </div>
    </div>
  </div>

  <!-- Reapply Invitation Modal -->
  <div id="reapplyInvitationModal"
    class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-50 hidden items-center justify-center p-4 animate-fadeIn">
    <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0 modal-content overflow-hidden border border-gray-200">

      <!-- Modal Header -->
      <div class="px-6 py-4 flex items-center justify-between border-b border-green-100 bg-green-50">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 shadow-sm border border-green-200">
            <i class="fi fi-rr-rotate-right mt-1"></i>
          </div>
          <h2 class="text-lg font-bold text-green-800">Reapply Invitation</h2>
        </div>
      </div>

      <!-- Modal Content -->
      <div class="px-6 py-5 border-b border-gray-100 bg-white">
        <p class="text-gray-600 text-sm mb-4">Are you sure you want to reapply the invitation for this team member?</p>

        <!-- Member Info -->
        <div class="bg-gray-50 rounded bg-opacity-50 p-3 mb-1 text-left border border-gray-200 flex items-center gap-3 shadow-inner">
           <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-gray-500 shadow-sm border border-gray-200">
            <i class="fi fi-rr-user mt-1"></i>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase font-semibold">Member</p>
            <p class="font-bold text-gray-800 text-sm" id="reapplyMemberName">-</p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
        <button id="closeReapplyInvitationBtn"
          class="px-5 py-2 text-sm border border-gray-300 text-gray-700 rounded hover:bg-gray-100 transition-all font-semibold shadow-sm flex items-center gap-2">
          Cancel
        </button>
        <button id="confirmReapplyInvitationBtn"
          class="px-5 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded transition-all font-semibold shadow-sm flex items-center gap-2 transform active:scale-95">
          <i class="fi fi-rr-rotate-right"></i>
          Reapply
        </button>
      </div>
    </div>
  </div>

  <!-- Document Viewer Modal -->
  <div id="documentViewerModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2"><i class="fi fi-rr-file-document text-orange-500"></i> Document Viewer</h3>
        <div class="flex items-center gap-2">
          <button id="closeDocumentViewerBtn" class="text-gray-500 hover:text-gray-700 p-2 rounded-lg">
            <i class="fi fi-rr-cross text-xl"></i>
          </button>
        </div>
      </div>

      <div class="p-4 max-h-[calc(90vh-120px)] overflow-auto flex items-center justify-center bg-gray-50">
        <iframe id="documentViewerFrame" src="" class="w-full h-[70vh] border-0 hidden"></iframe>
        <img id="documentViewerImg" src="" alt="Document" class="max-w-full max-h-[70vh] object-contain hidden" />
      </div>
    </div>
  </div>

  <script src="{{ asset('js/admin/userManagement/contractor.js') }}" defer></script>
  <script src="{{ asset('js/admin/userManagement/contractor_Views.js') }}" defer></script>

</body>

</html>
