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

  <style>
    .left-scroll-pane {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .left-scroll-pane::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }

    #editContractorModal .overflow-y-auto,
    #suspendAccountModal .overflow-y-auto,
    #editTeamMemberModal .edit-team-member-modal-scroll,
    #addTeamMemberModal .add-team-member-modal-scroll,
    #changeRepresentativeModal .change-representative-modal-scroll {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    #editContractorModal .overflow-y-auto::-webkit-scrollbar,
    #suspendAccountModal .overflow-y-auto::-webkit-scrollbar,
    #editTeamMemberModal .edit-team-member-modal-scroll::-webkit-scrollbar,
    #addTeamMemberModal .add-team-member-modal-scroll::-webkit-scrollbar,
    #changeRepresentativeModal .change-representative-modal-scroll::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }

    #editTeamMemberModal .form-label,
    #addTeamMemberModal .form-label {
      font-size: 0.75rem;
      margin-bottom: 0.25rem;
    }

    #editTeamMemberModal .form-input,
    #addTeamMemberModal .form-input {
      padding: 0.45rem 0.75rem;
      border-width: 1px;
      font-size: 0.75rem;
    }
  </style>


</head>

<body class="bg-gray-50 text-gray-800 font-sans" data-contractor-id="{{ $contractor->contractor_id ?? '' }}">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'Contractor Details'])

      <!-- PAGE CONTENT -->
      <div class="px-4 py-3 sm:px-5 sm:py-3 lg:px-6 lg:py-4 max-w-7xl mx-auto space-y-3">
        <!-- Top row: Back + Actions -->
        <div class="flex items-center justify-between">
          <a href="{{ route('admin.userManagement.contractor') }}" class="inline-flex items-center gap-1.5 border border-blue-400 bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white hover:shadow-md hover:-translate-y-0.5 font-medium px-2.5 py-1.5 rounded-lg transition-all active:scale-95 text-xs">
            <i class="fi fi-rr-angle-left text-xs"></i>
            <span>Back</span>
          </a>
          <div class="flex items-center gap-2">
            @if($contractor->is_active == 1)
            <button id="suspendContractorBtn" data-id="{{ $contractor->contractor_id }}" class="px-2.5 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 hover:shadow-md hover:-translate-y-0.5 text-white transition-all flex items-center gap-1.5 text-xs font-medium active:scale-95">
              <i class="fi fi-rr-ban text-xs"></i>
              <span>Suspend</span>
            </button>
            @else
            <div class="px-2.5 py-1.5 rounded-lg bg-gray-100 text-gray-500 font-medium flex items-center gap-1.5 text-xs cursor-default">
                <i class="fi fi-rr-ban text-xs"></i>
                <span>Suspended</span>
              </div>
            @endif
          </div>
        </div>

        <!-- Info grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-3 xl:items-start">
          <!-- Left 2/3 -->
          <div class="left-scroll-pane xl:col-span-2 space-y-3 xl:max-h-[calc(100vh-9rem)] xl:overflow-y-auto xl:pr-1">
            <!-- Company Profile Card -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
              <!-- Cover Photo -->
              <div class="relative h-28 bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 overflow-hidden">
                @if(isset($contractor->cover_photo) && $contractor->cover_photo)
                  <img id="companyCoverImg" src="{{ asset($contractor->cover_photo) }}" alt="Cover Photo" class="w-full h-full object-cover">
                @else
                  <img id="companyCoverImg" src="" alt="Cover Photo" class="w-full h-full object-cover hidden">
                @endif
                <!-- Decorative circles (visible when no cover photo set) -->
                <div id="coverPhotoPlaceholder" class="{{ isset($contractor->cover_photo) && $contractor->cover_photo ? 'hidden' : '' }} absolute inset-0 opacity-10 pointer-events-none">
                  <div class="absolute top-2 right-8 w-20 h-20 rounded-full border-4 border-white"></div>
                  <div class="absolute -top-4 right-16 w-32 h-32 rounded-full border-4 border-white"></div>
                  <div class="absolute bottom-2 left-1/3 w-16 h-16 rounded-full border-2 border-white"></div>
                </div>
                <!-- Section title overlay at bottom of cover -->
                <div class="absolute bottom-0 left-0 right-0 px-4 py-2 bg-gradient-to-t from-black/40 to-transparent">
                  <h2 class="text-xs font-semibold text-white flex items-center gap-1.5">
                    <i class="fi fi-sr-building text-xs"></i>
                    Company Profile
                  </h2>
                </div>
                <!-- Edit Cover Photo Button -->
                <label for="coverPhotoUpload" class="absolute top-2 right-2 flex items-center gap-1.5 bg-black bg-opacity-40 hover:bg-opacity-60 text-white px-2.5 py-1.5 rounded-lg cursor-pointer text-xs font-medium transition-all hover:shadow-lg active:scale-95 backdrop-blur-sm border border-white border-opacity-20">
                  <i class="fi fi-rr-camera text-xs"></i>
                  <span>Change Cover</span>
                  <input type="file" id="coverPhotoUpload" class="hidden" accept="image/*">
                </label>
              </div>

              <!-- Profile area below cover -->
              <div class="px-3 md:px-4 pb-3 md:pb-4">
                <!-- Avatar only overlapping the cover -->
                <div class="-mt-8 mb-3 relative z-10">
                  <div class="relative inline-block">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center overflow-hidden shadow-lg ring-[3px] ring-white">
                      @if(isset($contractor->profile_pic) && $contractor->profile_pic)
                        <img id="companyLogoImg" src="{{ asset($contractor->profile_pic) }}" alt="{{ $contractor->company_name }}" class="w-full h-full object-cover">
                        <i id="companyLogoIcon" class="fi fi-sr-building text-white text-xl hidden"></i>
                      @else
                        <img id="companyLogoImg" src="" alt="{{ $contractor->company_name }}" class="w-full h-full object-cover hidden">
                        <i id="companyLogoIcon" class="fi fi-sr-building text-white text-xl"></i>
                      @endif
                    </div>
                    <label for="companyLogoUpload" class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-1 rounded-full cursor-pointer shadow-md transition-all hover:-translate-y-0.5 active:scale-95">
                      <i class="fi fi-rr-camera text-xs"></i>
                      <input type="file" id="companyLogoUpload" class="hidden" accept="image/*">
                    </label>
                  </div>
                </div>

                <!-- Company info in white area below avatar -->
                <div class="mb-4">
                  <h3 class="text-sm font-semibold text-gray-800">{{ $contractor->company_name ?? 'N/A' }}</h3>
          
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">

                  <!-- Account Profile (Merged) - First section -->
                  <div class="md:col-span-2 p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200 shadow-sm">
                    <!-- Header row with larger avatar -->
                    <div class="flex gap-4 mb-4 pb-4 border-b border-blue-200">
                      <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow-md ring-2 ring-blue-100 flex-shrink-0">
                        @if(isset($contractor->profile_pic) && $contractor->profile_pic)
                          <img src="{{ asset($contractor->profile_pic) }}" alt="{{ $contractor->company_name }}" class="w-full h-full object-cover">
                        @else
                          <i class="fi fi-rr-user text-white text-2xl"></i>
                        @endif
                      </div>
                      <div class="flex-1 flex flex-col justify-center">
                        <h3 class="text-sm font-bold text-blue-700 flex items-center gap-1.5 mb-1">
                          <i class="fi fi-rr-user text-base text-blue-600"></i>
                          Account Profile
                        </h3>
                        <p class="text-xs text-gray-700 font-medium">{{ $contractor->username ?? 'N/A' }}</p>
                        <p class="text-[11px] text-gray-600 mt-1">{{ $contractor->company_email ?? 'N/A' }}</p>
                      </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <div>
                        <label class="block text-xs font-semibold text-blue-700 mb-1 uppercase tracking-wider">Account Status</label>
                        @if($contractor->is_active == 1)
                          <span class="inline-block px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Active</span>
                        @else
                          <span class="inline-block px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">Inactive</span>
                        @endif
                      </div>
                      <div>
                        <label class="block text-xs font-semibold text-blue-700 mb-1 uppercase tracking-wider">Member Since</label>
                        <p class="text-xs font-medium text-gray-800">{{ $contractor->created_at ? \Carbon\Carbon::parse($contractor->created_at)->format('F j, Y') : 'N/A' }}</p>
                      </div>
                      <div>
                        <label class="block text-xs font-semibold text-blue-700 mb-1 uppercase tracking-wider">Last Login</label>
                        <p class="text-xs font-medium text-gray-800">N/A</p>
                      </div>
                      <div>
                        <label class="block text-xs font-semibold text-blue-700 mb-1 uppercase tracking-wider">Account Type</label>
                        <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-600 rounded-full text-xs font-semibold">{{ $contractor->contractor_type_name ?? 'N/A' }}</span>
                      </div>
                      <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-blue-700 mb-1 uppercase tracking-wider">Bio</label>
                        <p class="text-xs text-gray-700 leading-relaxed bg-white p-2.5 rounded border border-blue-200">
                          {{ $contractor->company_description ?? 'No bio provided.' }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Company Information Fields -->
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Company Name</label>
                    <p class="text-xs font-medium text-gray-800">{{ $contractor->company_name ?? 'N/A' }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Owner
                      Name</label>
                    @php
                      // Owner is the contractor's property owner (via owner_id), not in team_members
                      $ownerName = trim(($contractor->first_name ?? '') . ' ' . ($contractor->middle_name ?? '') . ' ' . ($contractor->last_name ?? ''));
                      $ownerName = $ownerName ?: 'N/A';
                    @endphp
                    <p class="text-xs font-medium text-gray-800">{{ $ownerName }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Years of Operation</label>
                    <p class="text-xs font-medium text-gray-800">{{ $contractor->years_of_experience ?? 'N/A' }} Years</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Registration Date</label>
                    <p class="text-xs font-medium text-gray-800">{{ $contractor->created_at ? \Carbon\Carbon::parse($contractor->created_at)->format('F j, Y') : 'N/A' }}</p>
                  </div>

                  <!-- Company Website / Socials -->
                  <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">Company
                      Website / Socials</label>
                    <div class="space-y-2">
                      @if($contractor->company_website)
                        <div class="flex items-center gap-2 text-xs text-gray-700">
                          <i class="fi fi-rr-globe text-blue-500"></i>
                          <a href="{{ $contractor->company_website }}" target="_blank" class="hover:underline">{{ $contractor->company_website }}</a>
                        </div>
                      @endif
                      @if($contractor->company_social_media)
                        <div class="flex items-center gap-2 text-xs text-gray-700">
                          <i class="fi fi-brands-facebook text-blue-500"></i>
                          <a href="{{ $contractor->company_social_media }}" target="_blank" class="hover:underline">{{ $contractor->company_social_media }}</a>
                        </div>
                      @endif
                      @if(!$contractor->company_website && !$contractor->company_social_media)
                        <p class="text-xs text-gray-500">No website or social media links provided</p>
                      @endif
                    </div>
                  </div>

                  <!-- Services Offered (Merged) -->
                  <div class="md:col-span-2 pt-2 border-t border-gray-200 mt-1">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Services Offered</label>
                    @if($contractor->services_offered)
                      <p class="text-xs text-gray-700 leading-relaxed">{{ $contractor->services_offered }}</p>
                    @else
                      <p class="text-xs text-gray-500">No services listed</p>
                    @endif
                  </div>

                  <!-- Business Address (Merged) -->
                  <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Business Address</label>
                    <p class="text-xs text-gray-800 leading-relaxed">{{ $contractor->business_address ?? 'N/A' }}</p>
                  </div>
                </div>
              </div>
            </section>

            <!-- Company Representative Section -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
              <div class="px-4 py-2.5 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-transparent flex items-center justify-between">
                <h2 class="text-xs font-semibold text-blue-700 flex items-center gap-1.5">
                  <i class="fi fi-rr-id-badge text-xs"></i>
                  Company Representative Information
                </h2>
                <button id="changeRepresentativeBtn" class="px-2.5 py-1.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg font-medium text-xs shadow-sm hover:shadow-md transition-all flex items-center gap-1.5 active:scale-95">
                  <i class="fi fi-rr-refresh text-xs"></i>
                  <span>{{ $contractor->representative ? 'Change Representative' : 'Add Representative' }}</span>
                </button>
              </div>
              <div class="p-3 md:p-4">
                @if($contractor->representative)
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <!-- Representative Photo -->
                    <div class="md:col-span-2 flex items-center gap-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                      <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center overflow-hidden shadow ring-2 ring-blue-100">
                        @if($contractor->representative->rep_profile_pic)
                          <img id="repPhotoImg" src="{{ asset('storage/' . $contractor->representative->rep_profile_pic) }}" alt="Representative Photo" class="w-full h-full object-cover">
                        @else
                          <i id="repPhotoIcon" class="fi fi-rr-user text-white text-xl"></i>
                        @endif
                      </div>
                      <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-800">
                          {{ ($contractor->representative->first_name ?? '') . ' ' .
                             ($contractor->representative->middle_name ?? '') . ' ' .
                             ($contractor->representative->last_name ?? '') }}
                        </h3>
                        <p class="text-xs text-gray-600 mt-0.5">{{ ucfirst($contractor->representative->company_role ?? 'N/A') }}</p>
                      </div>
                    </div>

                    <!-- Representative Information Fields -->
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">First Name</label>
                      <p class="text-xs font-medium text-gray-800">{{ $contractor->representative->first_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Middle Name</label>
                      <p class="text-xs font-medium text-gray-800">{{ $contractor->representative->middle_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Last Name</label>
                      <p class="text-xs font-medium text-gray-800">{{ $contractor->representative->last_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Position / Role</label>
                      <p class="text-xs font-medium text-gray-800">{{ ucfirst($contractor->representative->company_role ?? 'N/A') }}</p>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Email Address</label>
                      <p class="text-xs font-medium text-gray-800">{{ $contractor->representative->rep_email ?? 'N/A' }}</p>
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Username</label>
                      <p class="text-xs font-medium text-gray-800">{{ $contractor->representative->rep_username ?? 'N/A' }}</p>
                    </div>
                  </div>
                @else
                  <div class="text-center py-6">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                      <i class="fi fi-rr-user text-gray-500 text-xl"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-1">No Representative Assigned</h3>
                    <p class="text-xs text-gray-500">Click "Add Representative" button above to assign a company representative.</p>
                  </div>
                @endif
              </div>
            </section>

            <!-- Team Members Section -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
              <div class="px-4 py-2.5 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-transparent flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <i class="fi fi-rr-users-alt text-blue-600 text-xs"></i>
                  <h2 class="text-xs font-semibold text-blue-700">Team Members</h2>
                </div>
                <button id="addTeamMemberBtn" class="flex items-center gap-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-2.5 py-1.5 rounded-lg font-medium text-xs shadow-sm hover:shadow-md transition-all active:scale-95">
                  <i class="fi fi-rr-plus text-xs"></i>
                  <span>Add Member</span>
                </button>
              </div>

              <!-- Tabs -->
              <div class="border-b border-gray-200">
                <div class="flex px-4">
                  <button class="team-tab active px-3 py-2 text-xs font-semibold border-b-2 border-blue-500 text-blue-600 transition-all" data-tab="active">
                    Active
                  </button>
                  <button class="team-tab px-3 py-2 text-xs font-semibold border-b-2 border-transparent text-gray-600 hover:text-blue-600 hover:border-blue-300 transition-all" data-tab="pending">
                    Pending Invitations
                  </button>
                  <button class="team-tab px-3 py-2 text-xs font-semibold border-b-2 border-transparent text-gray-600 hover:text-blue-600 hover:border-blue-300 transition-all" data-tab="cancelled">
                    Cancelled Invitations
                  </button>
                  <button class="team-tab px-3 py-2 text-xs font-semibold border-b-2 border-transparent text-gray-600 hover:text-blue-600 hover:border-blue-300 transition-all" data-tab="deactivated">
                    Suspended Accounts
                  </button>
                </div>
              </div>

              <!-- Team Members Table -->
              <div class="overflow-x-auto">
                <table class="w-full table-fixed">
                  <thead>
                    <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[34%]">Member</th>
                      <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Position</th>
                      <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Date Added</th>
                      <th id="statusColumnHeader" class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Status</th>
                      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Action</th>
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
                        // - active: is_active=1 (default)
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
                          $statusClass = 'team-member-row hidden';
                          $dataStatus = 'pending';
                        } else {
                          $statusClass = 'team-member-row';
                          $dataStatus = 'active';
                        }

                        $displayRole = ucfirst($member->company_role ?? 'N/A');
                        if ($member->company_role === 'others' && !empty($member->role_if_others)) {
                          $displayRole = ucfirst($member->role_if_others);
                        }
                      ?>
                      <tr class="hover:bg-indigo-50/60 transition-colors group {{ $statusClass }}"
                          data-status="{{ $dataStatus }}"
                          data-email="{{ $member->email ?? '' }}"
                          data-contact="">
                        <td class="px-2.5 py-2.5">
                          <div class="flex items-center gap-1.5 {{ $member->is_suspended ? 'opacity-60' : '' }}">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-{{ $color }}-400 to-{{ $color }}-600 flex items-center justify-center overflow-hidden shadow flex-shrink-0">
                              @if($member->profile_pic)
                                <img src="{{ asset('storage/' . $member->profile_pic) }}" alt="{{ $fname . ' ' . $lname }}" class="w-full h-full object-cover">
                              @else
                                <span class="text-white font-bold text-[11px]">{{ $initials }}</span>
                              @endif
                            </div>
                            <div class="min-w-0">
                              <p class="font-medium text-gray-800 leading-tight text-xs truncate {{ $member->is_suspended ? 'text-gray-600' : 'group-hover:text-blue-600' }} transition" title="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
                                {{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}
                              </p>
                              <p class="text-[11px] text-gray-500 truncate" title="{{ $member->email ?? 'N/A' }}">
                                {{ $member->email ?? 'N/A' }}
                              </p>
                            </div>
                          </div>
                        </td>
                        <td class="px-2.5 py-2.5 text-center text-[11px] text-gray-700">{{ $displayRole }}</td>
                        <td class="px-2.5 py-2.5 text-center whitespace-nowrap text-[11px] text-gray-700">{{ $member->created_at ? \Carbon\Carbon::parse($member->created_at)->format('F j, Y') : 'N/A' }}</td>
                        <td class="px-2.5 py-2.5 text-center status-cell">
                          @if($dataStatus == 'deactivated')
                            <span class="status-badge inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-gray-100 text-gray-600 border-gray-200 hidden">
                              Suspended
                            </span>
                            <span class="deletion-reason text-[11px] text-gray-700">{{ $member->suspension_reason ?? 'No reason provided' }}</span>
                          @elseif($dataStatus == 'active')
                            <span class="status-badge inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-green-100 text-green-700 border-green-200">
                              Active
                            </span>
                          @elseif($dataStatus == 'cancelled')
                            <span class="status-badge inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-red-100 text-red-700 border-red-200 hidden">
                              Cancelled Invitation
                            </span>
                          @else
                            <span class="status-badge inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-yellow-100 text-yellow-700 border-yellow-200 hidden">
                              Pending Invitation
                            </span>
                          @endif
                        </td>
                        <td class="px-2.5 py-2.5">
                          <div class="flex items-center gap-1">
                            @if($dataStatus == 'deactivated')
                              <button class="team-reactivate-btn p-1.5 rounded-xl border border-green-200 bg-green-50 text-green-600 hover:bg-green-100 hover:shadow-sm hover:border-green-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Reactivate Account"
                                      data-member-id="{{ $member->staff_id }}"
                                      data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
                                <i class="fi fi-rr-check-circle"></i>
                              </button>
                            @elseif($dataStatus == 'active')
                              <button class="team-edit-btn p-1.5 rounded-xl border border-orange-200 bg-orange-50 text-orange-600 hover:bg-orange-100 hover:shadow-sm hover:border-orange-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Edit Member" data-member-id="{{ $member->staff_id }}">
                                <i class="fi fi-rr-pencil"></i>
                              </button>
                              <button class="team-deactivate-btn p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Suspend Account"
                                      data-member-id="{{ $member->staff_id }}"
                                      data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
                                <i class="fi fi-rr-ban"></i>
                              </button>
                            @elseif($dataStatus == 'pending')
                              <button class="team-cancel-invitation-btn p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Cancel Invitation"
                                      data-member-id="{{ $member->staff_id }}"
                                      data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
                                <i class="fi fi-rr-cross-circle"></i>
                              </button>
                            @elseif($dataStatus == 'cancelled')
                              <button class="team-reapply-invitation-btn p-1.5 rounded-xl border border-green-200 bg-green-50 text-green-600 hover:bg-green-100 hover:shadow-sm hover:border-green-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Reapply Invitation"
                                      data-member-id="{{ $member->staff_id }}"
                                      data-member-name="{{ $fname . ' ' . ($member->middle_name ?? '') . ' ' . $lname }}">
                                <i class="fi fi-rr-rotate-right"></i>
                              </button>
                            @endif
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                          <i class="fi fi-sr-users text-3xl block mb-2"></i>
                          <p class="text-base font-medium text-gray-500">No team members found</p>
                          <p class="text-xs mt-1">Try adding a team member to populate this table.</p>
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </section>

          </div>

          <!-- Right 1/3: Documents & Services -->
          <div class="space-y-3 xl:sticky xl:top-4 self-start">
            <!-- Documents Section -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
              <div class="px-4 py-2.5 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-transparent">
                <h2 class="text-xs font-semibold text-blue-700 flex items-center gap-1.5">
                  <i class="fi fi-rr-document text-xs"></i>
                  Documents
                </h2>
              </div>
              <div class="p-3 space-y-3">
                <!-- PCAB Information -->
                <div class="space-y-3 pb-4 border-b border-gray-200">
                  <h3 class="text-xs font-semibold text-blue-700 flex items-center gap-1.5">
                    <i class="fi fi-rr-certificate"></i>
                    PCAB License
                  </h3>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">PCAB No.</label>
                    <p class="text-xs font-medium text-gray-800">{{ $contractor->picab_number ?? 'N/A' }}</p>
                  </div>
                  <div>
                    <label
                      class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Category</label>
                    <span
                      class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">{{ $contractor->picab_category ?? 'N/A' }}</span>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Expiration Date</label>
                    <p class="text-xs font-medium text-gray-800">{{ $contractor->picab_expiration_date ? \Carbon\Carbon::parse($contractor->picab_expiration_date)->format('F j, Y') : 'N/A' }}</p>
                  </div>
                </div>

                <!-- Business Permit -->
                <div class="space-y-3 pb-4 border-b border-gray-200">
                  <h3 class="text-xs font-semibold text-blue-700 flex items-center gap-1.5">
                    <i class="fi fi-rr-file-check"></i>
                    Business Permit
                  </h3>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Permit City</label>
                    <p class="text-xs font-medium text-gray-800">{{ $contractor->business_permit_city ?? 'N/A' }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Permit Number</label>
                    <p class="text-xs font-medium text-gray-800">{{ $contractor->business_permit_number ?? 'N/A' }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">Expiration Date</label>
                    <p class="text-xs font-medium text-gray-800">{{ $contractor->business_permit_expiration ? \Carbon\Carbon::parse($contractor->business_permit_expiration)->format('F j, Y') : 'N/A' }}</p>
                  </div>
                </div>

                <!-- TIN & DTI/SEC -->
                <div class="space-y-3">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">TIN Business Registration No.</label>
                    <p class="text-xs font-medium text-gray-800">{{ $contractor->tin_business_reg_number ?? 'N/A' }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wider">DTI / SEC
                      Registration</label>
                    @if($contractor->dti_sec_registration_photo)
                      <button type="button" data-doc-src="{{ asset('storage/' . $contractor->dti_sec_registration_photo) }}"
                        class="open-doc-btn inline-flex items-center gap-2 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-xs font-medium transition-all hover:shadow-sm border border-blue-200 group">
                        <i class="fi fi-rr-file-pdf text-red-500"></i>
                        <span>View Document</span>
                        <span class="text-[10px] text-gray-500 group-hover:text-blue-600">&bull; {{ $contractor->verification_status ?? 'Pending' }}</span>
                      </button>
                    @else
                      <p class="text-xs text-gray-500">No document uploaded</p>
                    @endif
                  </div>
                </div>
              </div>
            </section>

          </div>

        </div>
      </div>

    </main>
  </div>

  <!-- Suspend Account Modal -->
  <div id="suspendAccountModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-red-500 to-red-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-exclamation text-white text-sm"></i>
          </div>
          <h2 class="text-base sm:text-lg font-bold text-white">Suspend Account</h2>
        </div>
        <button id="closeSuspendModalBtn" class="text-white hover:text-red-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
          <i class="fi fi-rr-cross text-lg"></i>
        </button>
      </div>

      <!-- Modal Body - Scrollable -->
      <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-4 suspend-modal-scroll">
        <!-- Warning Message -->
        <div class="bg-red-50 border border-red-200 rounded-xl p-3 space-y-2.5">
          <div class="flex items-start gap-2.5">
            <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
              <i class="fi fi-rr-shield-exclamation text-white text-xs"></i>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-800 mb-0.5">Confirm Account Suspension</h3>
              <p class="text-xs text-gray-700 leading-relaxed">
                Are you sure you want to suspend <span class="font-bold text-red-600">{{ $contractor->company_name ?? 'this contractor' }}</span>?
              </p>
            </div>
          </div>

          <!-- Contractor Info Card -->
          <div class="bg-white rounded-lg p-2.5 border border-red-200 space-y-1.5">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white text-xs font-bold flex items-center justify-center shadow">
                {{ strtoupper(substr(str_replace(' ', '', $contractor->company_name ?? 'CO'), 0, 2)) }}
              </div>
              <div>
                <p class="font-semibold text-gray-800 text-xs">{{ $contractor->company_name ?? 'N/A' }}</p>
                <p class="text-[11px] text-gray-600">{{ $contractor->contractor_type_name ?? 'N/A' }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-1.5 pt-1.5 border-t border-gray-200">
              <div class="text-center">
                <p class="text-lg font-bold text-indigo-600">{{ $contractor->completed_projects_count ?? 0 }}</p>
                <p class="text-[10px] text-gray-600">Projects Done</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-green-600">{{ $contractor->ongoing_projects_count ?? 0 }}</p>
                <p class="text-[10px] text-gray-600">Ongoing Projects</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Reason Input -->
        <div>
          <label class="block text-xs font-semibold text-gray-800 mb-1.5 flex items-center gap-1.5">
            <i class="fi fi-rr-edit text-red-500"></i>
            Reason for Suspension <span class="text-red-500">*</span>
          </label>
          <textarea id="suspendReason" rows="3"
            placeholder="Please provide a detailed reason for suspending this account..."
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-300 focus:border-red-300 transition-all hover:border-red-300 bg-white resize-none text-xs"
          ></textarea>
          <p id="suspendReasonError" class="text-red-500 text-xs mt-1 hidden"></p>
          <p class="text-[11px] text-gray-500 mt-1 flex items-center gap-1">
            <i class="fi fi-rr-info"></i>
            This reason will be recorded and may be shared with the user.
          </p>
        </div>

        <!-- Suspension Options -->
        <div class="space-y-2">
          <label class="block text-xs font-semibold text-gray-800 mb-1.5 flex items-center gap-1.5">
            <i class="fi fi-rr-calendar text-red-500"></i>
            Suspension Duration
          </label>
          <div class="grid grid-cols-2 gap-3">
            <label class="relative cursor-pointer group">
              <input type="radio" name="suspensionDuration" value="temporary" class="peer sr-only" checked>
              <div class="border-2 border-gray-300 rounded-lg p-2.5 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300 hover:shadow-sm">
                <i class="fi fi-rr-clock text-base text-gray-400 peer-checked:text-red-500 transition-colors mb-0.5"></i>
                <p class="font-semibold text-gray-700 text-xs peer-checked:text-red-600">Temporary</p>
                <p class="text-[10px] text-gray-500 mt-0.5">Select Date</p>
              </div>
            </label>
            <label class="relative cursor-pointer group">
              <input type="radio" name="suspensionDuration" value="permanent" class="peer sr-only">
              <div class="border-2 border-gray-300 rounded-lg p-2.5 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300 hover:shadow-sm">
                <i class="fi fi-rr-ban text-base text-gray-400 peer-checked:text-red-500 transition-colors mb-0.5"></i>
                <p class="font-semibold text-gray-700 text-xs peer-checked:text-red-600">Permanent</p>
                <p class="text-[10px] text-gray-500 mt-0.5">Account deletion</p>
              </div>
            </label>
          </div>

          <!-- Date Picker for Temporary Suspension -->
          <div id="suspensionDateContainer" class="mt-2.5 transition-all duration-300 overflow-hidden">
            <label class="block text-xs font-medium text-gray-700 mb-1">Suspension Until</label>
            <input type="date" id="suspensionDate" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-300 focus:border-red-300 outline-none transition-all text-xs" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
            <p id="suspensionDateError" class="text-red-500 text-xs mt-1 hidden"></p>
            <p class="text-[11px] text-gray-500 mt-1">The account will be automatically reactivated after this date.</p>
          </div>
        </div>

        <!-- Consequences Warning -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-2.5 rounded-r-lg">
          <div class="flex gap-2">
            <i class="fi fi-rr-triangle-warning text-yellow-600 text-sm flex-shrink-0 mt-0.5"></i>
            <div class="text-xs text-gray-700 space-y-1">
              <p class="font-semibold text-gray-800 text-xs">Suspension Consequences:</p>
              <ul class="list-disc list-inside space-y-0.5 text-xs">
                <li>User will be logged out immediately</li>
                <li>All ongoing projects will be paused</li>
                <li>Account access will be restricted</li>
                <li>Email notification will be sent to user</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons - Fixed at Bottom -->
      <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
        <button id="cancelSuspendBtn" class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all font-semibold active:scale-95 text-xs">
          Cancel
        </button>
        <button id="confirmSuspendBtn" class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition-all font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5 text-xs">
          <i class="fi fi-rr-shield-check"></i>
          Suspend Account
        </button>
      </div>
    </div>
  </div>

  <!-- Add Team Member Modal -->
  <div id="addTeamMemberModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full min-h-[440px] max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content flex flex-col">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-user-add text-white text-sm"></i>
          </div>
          <h2 class="text-sm sm:text-base font-bold text-white">Add Team Member</h2>
        </div>
        <button id="closeAddTeamMemberBtn" class="text-white hover:text-orange-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
          <i class="fi fi-rr-cross text-lg"></i>
        </button>
      </div>

      <!-- Modal Body — relative z-40 ensures the absolute dropdown stacks above the footer -->
      <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-5 space-y-4 add-team-member-modal-scroll relative z-40">
        <!-- Validation Error Section -->
        <div id="addTeamMemberErrorAlert" class="hidden bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg">
          <div class="flex items-start gap-2">
            <i class="fi fi-rr-exclamation text-red-600 text-sm flex-shrink-0 mt-0.5"></i>
            <div class="flex-1">
              <p class="text-xs font-semibold text-red-800 mb-1">Validation Error</p>
              <ul id="addTeamMemberErrorList" class="text-xs text-red-700 space-y-0.5 list-disc list-inside">
                <!-- Error messages will be populated here -->
              </ul>
            </div>
          </div>
        </div>

        <!-- Property Owner Search -->
        <div>
          <label class="block text-xs font-semibold text-gray-800 mb-1.5 flex items-center gap-1.5">
            <i class="fi fi-rr-user text-orange-500"></i>
            Property Owner <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <input type="text" id="teamMemberOwnerSearch" placeholder="Search by name..."
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 transition-all hover:border-orange-300 bg-white text-xs pr-8" autocomplete="off">
            <div class="absolute right-2.5 top-1/2 transform -translate-y-1/2">
              <i class="fi fi-rr-search text-gray-400 text-xs"></i>
            </div>
            <div id="teamMemberOwnerDropdown"
              class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto hidden">
              <div id="teamMemberOwnerList" class="py-1">
                <!-- Options will be populated here -->
              </div>
              <div id="teamMemberOwnerLoading" class="p-2.5 text-center text-gray-500 text-xs hidden">
                <i class="fi fi-rr-spinner animate-spin"></i> Loading...
              </div>
              <div id="teamMemberOwnerEmpty" class="p-2.5 text-center text-gray-500 text-xs hidden">
                No available owners
              </div>
            </div>
          </div>
          <span id="teamMemberOwnerError" class="text-xs text-red-500 mt-1 hidden block"></span>

          <!-- Representative Limit Message -->
          <div id="representativeLimitMessage" class="hidden mt-2 p-2.5 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-xs text-blue-700">
              <i class="fi fi-rr-info mr-0.5"></i>
              Representative exists. Remove below to change.
            </p>
          </div>

          <p class="text-[11px] text-gray-500 mt-1 flex items-center gap-1">
            <i class="fi fi-rr-info"></i>
            Verified owners only
          </p>
        </div>

        <!-- Selected Members List -->
        <div id="selectedMembersList" class="hidden">
          <label class="block text-xs font-semibold text-gray-800 mb-1.5">
            Selected (<span id="selectedMembersCount">0</span>)
          </label>
          <div id="selectedMembersContainer"
            class="space-y-1.5 max-h-36 overflow-y-auto p-3 bg-gray-50 rounded-lg border border-gray-200">
            <!-- Selected members will appear here -->
          </div>
        </div>

        <!-- Role Selection -->
        <div id="roleSelectionSection" class="hidden space-y-3">
          <div>
            <label class="block text-xs font-semibold text-gray-800 mb-1.5 flex items-center gap-1.5">
              <i class="fi fi-rr-briefcase text-orange-500"></i>
              Role <span class="text-red-500">*</span>
            </label>
            <select id="teamMemberRole" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 outline-none transition-all hover:border-orange-300 bg-white text-xs">
              <option value="" disabled selected>Select Role</option>
              <option value="manager">Manager</option>
              <option value="engineer">Engineer</option>
              <option value="architect">Architect</option>
              <option value="others">Others</option>
            </select>
            <span id="teamMemberRoleError" class="text-xs text-red-500 mt-1 hidden block"></span>
          </div>
          <div class="hidden" id="teamMemberRoleOtherGroup">
            <label class="block text-xs font-semibold text-gray-800 mb-1.5">
              Specify Role <span class="text-red-500">*</span>
            </label>
            <input type="text" id="teamMemberRoleOther" placeholder="e.g., Consultant"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 outline-none transition-all hover:border-orange-300 bg-white text-xs">
            <span id="teamMemberRoleOtherError" class="text-xs text-red-500 mt-1 hidden block"></span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
        <button id="backToRepresentativeModalBtn" class="px-4 py-2 border-2 border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 hover:border-blue-400 hover:shadow-sm hover:-translate-y-0.5 transition-all font-semibold active:scale-95 text-xs hidden">
          Back
        </button>
        <button id="cancelAddTeamMemberBtn" class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all font-semibold active:scale-95 text-xs">
          Cancel
        </button>
        <button id="saveTeamMemberBtn" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5 text-xs">
          <i class="fi fi-rr-disk"></i>
          Add Member
        </button>
      </div>
    </div>
  </div>

  <!-- Edit Team Member Modal -->
  <div id="editTeamMemberModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-2 sm:p-3">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content relative flex flex-col">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-3 sm:px-4 py-2.5 flex items-center justify-between rounded-t-xl z-10 shadow-lg">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-edit text-white text-xs"></i>
          </div>
          <h2 class="text-sm sm:text-base font-bold text-white">Edit Team Member</h2>
        </div>
        <button id="closeEditTeamMemberBtn" class="text-white hover:text-orange-100 transition-all p-1 rounded-md hover:bg-white hover:bg-opacity-20 active:scale-95">
          <i class="fi fi-rr-cross text-base"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="flex-1 min-h-0 overflow-y-auto p-3 sm:p-4 pb-4 space-y-3 edit-team-member-modal-scroll">
        <!-- Member Information (Read-only) -->
        <div>
          <div class="flex items-center gap-2.5 mb-2.5">
            <h3 class="text-xs font-semibold text-orange-700 flex items-center gap-1.5 whitespace-nowrap px-2 py-1 rounded-full bg-orange-50 border border-orange-200 shadow-sm">
              <i class="fi fi-rr-user"></i>
              Member Information
            </h3>
            <div class="h-0.5 flex-1 rounded-full bg-gradient-to-r from-orange-400 via-orange-200 to-orange-50"></div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
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
          <div class="flex items-center gap-2.5 mb-2.5">
            <h3 class="text-xs font-semibold text-orange-700 flex items-center gap-1.5 whitespace-nowrap px-2 py-1 rounded-full bg-orange-50 border border-orange-200 shadow-sm">
              <i class="fi fi-rr-briefcase"></i>
              Role Information
            </h3>
            <div class="h-0.5 flex-1 rounded-full bg-gradient-to-r from-orange-400 via-orange-200 to-orange-50"></div>
          </div>
          <div class="grid grid-cols-1 gap-2">
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
      </div>

      <!-- Action Buttons -->
      <div class="shrink-0 bg-white border-t border-gray-200 px-3 sm:px-4 py-2.5 rounded-b-xl w-full flex items-center justify-end gap-1.5 shadow-[0_-6px_14px_rgba(17,24,39,0.08)]">
          <button id="cancelEditTeamMemberBtn" class="px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all text-[11px] font-semibold active:scale-95">
            Cancel
          </button>
          <button id="saveEditTeamMemberBtn" class="px-3 py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-lg transition-all text-[11px] font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5">
            <i class="fi fi-rr-check"></i>
            Save Changes
          </button>
      </div>
    </div>
  </div>

  <!-- Suspend Team Member Modal -->
  <div id="deactivateTeamMemberModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-red-500 to-red-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-ban text-white text-sm"></i>
          </div>
          <h2 class="text-base sm:text-lg font-bold text-white">Suspend Team Member</h2>
        </div>
        <button id="closeDeactivateTeamMemberBtn" class="text-white hover:text-red-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
          <i class="fi fi-rr-cross text-lg"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-4">
        <!-- Member Name -->
        <div class="bg-red-50 border border-red-200 rounded-xl p-3">
          <p class="text-xs text-red-600 font-semibold mb-1">Suspending Member:</p>
          <p class="text-sm font-bold text-gray-900" id="deactivateTeamMemberName">-</p>
        </div>

        <!-- Suspension Reason -->
        <div>
          <label class="block text-xs font-semibold text-gray-800 mb-1.5 flex items-center gap-1.5">
            <i class="fi fi-rr-edit text-red-500"></i>
            Reason for Suspension <span class="text-red-500">*</span>
          </label>
          <textarea
            id="deactivateTeamMemberReason"
            rows="3"
            placeholder="Provide a detailed reason for suspension..."
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-300 focus:border-red-300 transition-all hover:border-red-300 bg-white resize-none text-xs"
          ></textarea>
          <span id="deactivateReasonError" class="text-xs text-red-500 mt-1 hidden"></span>
        </div>

        <!-- Suspension Duration -->
        <div>
          <label class="block text-xs font-semibold text-gray-800 mb-2">Suspension Duration</label>
          <div class="space-y-2">
            <label class="flex items-center p-2.5 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-all">
              <input type="radio" name="suspensionDurationTeamMember" value="temporary" checked
                class="w-4 h-4 text-red-600 focus:ring-red-500">
              <span class="ml-2.5 text-xs font-medium text-gray-700">Temporary Suspension</span>
            </label>
            <label class="flex items-center p-2.5 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-all">
              <input type="radio" name="suspensionDurationTeamMember" value="permanent"
                class="w-4 h-4 text-red-600 focus:ring-red-500">
              <span class="ml-2.5 text-xs font-medium text-gray-700">Permanent Suspension</span>
            </label>
          </div>
        </div>

        <!-- Suspension Date (shown only for temporary) -->
        <div id="suspensionDateContainerTeamMember" class="transition-all duration-300 opacity-100 visible">
          <label class="block text-xs font-semibold text-gray-800 mb-1.5">
            Suspension Until <span class="text-red-500">*</span>
          </label>
          <input type="date" id="suspensionDateTeamMember"
            class="w-full px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-300 focus:border-red-300 transition-all text-xs"
            min="{{ date('Y-m-d', strtotime('+1 day')) }}">
          <span id="suspensionDateErrorTeamMember" class="text-xs text-red-500 mt-1 hidden"></span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
        <button id="cancelDeactivateTeamMemberBtn" class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all font-semibold active:scale-95 text-xs">
          Cancel
        </button>
        <button id="confirmDeactivateTeamMemberBtn" class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition-all font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5 text-xs">
          <i class="fi fi-rr-ban"></i>
          Suspend Member
        </button>
      </div>
    </div>
  </div>

  <!-- Reactivate Team Member Modal -->
  <div id="reactivateTeamMemberModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-green-500 to-green-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-user-check text-white text-sm"></i>
          </div>
          <h2 class="text-base sm:text-lg font-bold text-white">Reactivate Team Member</h2>
        </div>
        <button id="closeReactivateTeamMemberBtn" class="text-white hover:text-green-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
          <i class="fi fi-rr-cross text-lg"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-4">
        <div class="bg-green-50 border border-green-200 rounded-xl p-3 space-y-2.5">
          <div class="flex items-start gap-2.5">
            <div class="w-7 h-7 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
              <i class="fi fi-rr-user-check text-white text-xs"></i>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-800 mb-0.5">Confirm Team Member Reactivation</h3>
              <p class="text-xs text-gray-700 leading-relaxed">
                Are you sure you want to reactivate <span class="font-bold text-green-600" id="reactivateTeamMemberName">Robert Garcia</span>?
              </p>
            </div>
          </div>
          <p class="text-[11px] text-gray-600">This member will regain access to their contractor account.</p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-2.5 rounded-r-lg">
          <div class="flex gap-2">
            <i class="fi fi-rr-info text-blue-600 text-sm flex-shrink-0 mt-0.5"></i>
            <div class="text-xs text-gray-700 space-y-1">
              <p class="font-semibold text-gray-800 text-xs">Reactivation Effects:</p>
              <ul class="list-disc list-inside space-y-0.5 text-xs">
                <li>Member can log in again immediately</li>
                <li>Member returns to active team listing</li>
                <li>Previous role and profile details remain intact</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
        <button id="cancelReactivateTeamMemberBtn" class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all font-semibold active:scale-95 text-xs">
          Cancel
        </button>
        <button id="confirmReactivateTeamMemberBtn" class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg transition-all font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5 text-xs">
          <i class="fi fi-rr-user-check"></i>
          Reactivate Member
        </button>
      </div>
    </div>
  </div>

  <!-- Change Representative Modal -->
  <div id="changeRepresentativeModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-2 sm:p-3">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content flex flex-col">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-blue-500 to-blue-600 px-3 sm:px-4 py-2 flex items-center justify-between rounded-t-xl z-10 shadow-lg">
        <h2 class="text-xs sm:text-sm font-bold text-white flex items-center gap-1.5">
          <i class="fi fi-rr-refresh text-xs"></i>
          Change Representative
        </h2>
        <button id="closeChangeRepresentativeBtn" class="text-white hover:text-blue-100 transition-all p-0.5 rounded-md hover:bg-white hover:bg-opacity-20 active:scale-95">
          <i class="fi fi-rr-cross text-sm"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="flex-1 min-h-0 overflow-y-auto p-2.5 sm:p-3 pb-2.5 space-y-2 change-representative-modal-scroll">
        <!-- Current Representative Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5">
          <div class="flex items-center gap-1.5 mb-1.5">
            <i class="fi fi-rr-info text-blue-600 text-xs"></i>
            <h3 class="text-xs font-semibold text-gray-800 uppercase tracking-wider">Current Rep</h3>
          </div>
          @if($contractor->representative)
          <div class="flex items-center gap-2 p-2 bg-white rounded-lg border border-blue-200">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold shadow">
              {{ strtoupper(substr($contractor->representative->first_name ?? '', 0, 1) . substr($contractor->representative->last_name ?? '', 0, 1)) }}
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-800">
                {{ ($contractor->representative->first_name ?? '') . ' ' .
                   ($contractor->representative->middle_name ?? '') . ' ' .
                   ($contractor->representative->last_name ?? '') }}
              </p>
              <p class="text-[10px] text-gray-600 mt-0.5">{{ ucfirst($contractor->representative->company_role ?? 'Representative') }}</p>
            </div>
          </div>
          @else
          <div class="p-2 bg-white rounded-lg border border-blue-200">
            <p class="text-xs text-gray-600 text-center">No representative assigned</p>
          </div>
          @endif
        </div>

        <!-- Search Bar -->
        <div class="relative">
          <input
            type="text"
            id="searchTeamMember"
            placeholder="Search by name..."
            class="w-full px-2.5 py-1.5 pl-8 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 focus:border-blue-300 transition-all text-xs"
          >
          <i class="fi fi-rr-search absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
        </div>

        <!-- Team Members List -->
        <div>
          <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Select Member</h3>
          <div class="space-y-1 max-h-48 overflow-y-auto change-representative-modal-scroll" id="teamMembersList">
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
                <div class="team-member-option flex items-center justify-between p-2 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all cursor-pointer group"
                     data-member-id="{{ $member->staff_id }}"
                     data-member-name="{{ $fullName }}"
                     data-member-position="{{ $role }}">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br {{ $colors[$colorIndex] }} flex items-center justify-center text-white text-[10px] font-bold shadow-sm group-hover:scale-105 transition-transform">
                      {{ $initials }}
                    </div>
                    <div>
                      <p class="text-xs font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $fullName }}</p>
                      <p class="text-[10px] text-gray-600">{{ $role }}</p>
                    </div>
                  </div>
                  <i class="fi fi-rr-check-circle text-xs text-gray-300 group-hover:text-blue-500 transition-colors"></i>
                </div>
              @endforeach
            @else
              <div class="text-center py-3">
                <i class="fi fi-rr-users text-gray-300 text-lg mb-1"></i>
                <p class="text-xs text-gray-500">No team members</p>
              </div>
            @endif
          </div>
        </div>

        <!-- Warning Note -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-2 rounded-r-lg text-xs text-gray-700">
          <p class="font-semibold mb-0.5 text-gray-800 text-xs">Note:</p>
          <p class="text-[10px]">Changing representative updates official documents and communications.</p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="shrink-0 sticky bottom-0 bg-white border-t border-gray-200 px-3 sm:px-4 py-2 rounded-b-xl flex items-center justify-end gap-1 z-20 shadow-[0_-4px_8px_rgba(17,24,39,0.06)]">
        <button id="cancelChangeRepresentativeBtn" class="px-2.5 py-1 border border-gray-300 text-gray-700 rounded text-[11px] font-semibold hover:bg-gray-100 transition-all active:scale-95">
          Cancel
        </button>
        <button id="confirmChangeRepresentativeBtn" class="px-2.5 py-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded text-[11px] font-semibold shadow-sm transition-all active:scale-95 flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
          <i class="fi fi-rr-check text-xs"></i>
          Assign
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
