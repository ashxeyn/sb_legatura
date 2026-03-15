<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard - Legatura</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin/userManagement/propertyOwner_Views.css') }}">

  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>


  <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

  <style>
    #editPropertyOwnerModal .edit-modal-scroll,
    #suspendAccountModal .suspend-modal-scroll,
    .column-scroll-hidden {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    #editPropertyOwnerModal .edit-modal-scroll::-webkit-scrollbar,
    #suspendAccountModal .suspend-modal-scroll::-webkit-scrollbar,
    .column-scroll-hidden::-webkit-scrollbar {
      display: none;
      width: 0;
      height: 0;
    }
  </style>


</head>

<body class="bg-gray-50 text-gray-800 font-sans" data-owner-id="{{ $propertyOwner->owner_id }}">

  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'Property Owners'])

      <!-- PAGE CONTENT -->
      <div class="px-4 py-3 sm:px-5 sm:py-3 lg:px-6 lg:py-4 max-w-7xl mx-auto space-y-3">
        <!-- Top row: Back + Actions -->
        <div class="flex items-center justify-between sticky top-16 z-20 bg-gray-50/95 backdrop-blur-sm py-1">
          <a href="{{ route('admin.userManagement.propertyOwner') }}" class="inline-flex items-center gap-1.5 border border-blue-400 bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white hover:shadow-md hover:-translate-y-0.5 font-medium px-2.5 py-1.5 rounded-lg transition-all active:scale-95 text-xs">
            <i class="fi fi-rr-angle-left text-xs"></i>
            <span>Back</span>
          </a>
          <div class="flex items-center gap-2">
            {{-- Edit button removed due to redundancy --}}
            @if($propertyOwner->is_active == 1)
            <button id="suspendPropertyOwnerBtn" data-id="{{ $propertyOwner->owner_id }}" class="px-2.5 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 hover:shadow-md hover:-translate-y-0.5 text-white transition-all flex items-center gap-1.5 text-xs font-medium active:scale-95">
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
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-3 items-start">
          <!-- Left 2/3 -->
          <div class="xl:col-span-2 space-y-3 xl:sticky xl:top-32 xl:self-start xl:max-h-[calc(100vh-8.5rem)] xl:overflow-y-auto xl:pr-1 column-scroll-hidden">
            <!-- Profile card -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
              <!-- Cover Photo -->
              <div class="relative h-28 bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 overflow-hidden">
                @if(isset($propertyOwner->cover_photo) && $propertyOwner->cover_photo)
                  <img id="ownerCoverImg" src="{{ asset('storage/' . $propertyOwner->cover_photo) }}" alt="Profile Cover" class="w-full h-full object-cover">

                @else
                  <img id="ownerCoverImg" src="" alt="Profile Cover" class="w-full h-full object-cover hidden">
                @endif

                <div id="ownerCoverPlaceholder" class="{{ isset($propertyOwner->cover_photo) && $propertyOwner->cover_photo ? 'hidden' : '' }} absolute inset-0 opacity-10 pointer-events-none">
                  <div class="absolute top-2 right-8 w-20 h-20 rounded-full border-4 border-white"></div>
                  <div class="absolute -top-4 right-16 w-32 h-32 rounded-full border-4 border-white"></div>
                  <div class="absolute bottom-2 left-1/3 w-16 h-16 rounded-full border-2 border-white"></div>
                </div>

                <div class="absolute bottom-0 left-0 right-0 px-4 py-2 bg-gradient-to-t from-black/40 to-transparent">
                  <h2 class="text-xs font-semibold text-white flex items-center gap-1.5">
                    <i class="fi fi-rr-user text-xs"></i>
                    Property Owner Profile
                  </h2>
                </div>

                <label for="ownerCoverUpload" class="absolute top-2 right-2 flex items-center gap-1.5 bg-black bg-opacity-40 hover:bg-opacity-60 text-white px-2.5 py-1.5 rounded-lg cursor-pointer text-xs font-medium transition-all hover:shadow-lg active:scale-95 backdrop-blur-sm border border-white border-opacity-20">
                  <i class="fi fi-rr-camera text-xs"></i>
                  <span>Change Cover</span>
                  <input type="file" id="ownerCoverUpload" class="hidden" accept="image/*">
                </label>
              </div>

              <!-- Profile area below cover -->
              <div class="px-3 md:px-4 pb-3 md:pb-4">
                <div class="-mt-8 mb-3 relative z-10">
                  <div class="relative inline-block">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center overflow-hidden shadow-lg ring-[3px] ring-white">
                      @if($propertyOwner->profile_pic)
                        <img id="ownerProfileImg" src="{{ asset('storage/' . $propertyOwner->profile_pic) }}" alt="Profile" class="w-full h-full object-cover">

                        <span id="ownerProfileInitials" class="hidden text-white font-bold text-lg">
                          {{ substr($propertyOwner->first_name, 0, 1) . substr($propertyOwner->last_name, 0, 1) }}
                        </span>
                      @else
                        <img id="ownerProfileImg" src="" alt="Profile" class="w-full h-full object-cover hidden">
                        <span id="ownerProfileInitials" class="text-white font-bold text-lg">
                          {{ substr($propertyOwner->first_name, 0, 1) . substr($propertyOwner->last_name, 0, 1) }}
                        </span>
                      @endif
                    </div>
                    <label for="ownerProfileUpload" class="absolute bottom-0 right-0 bg-orange-500 hover:bg-orange-600 text-white p-1 rounded-full cursor-pointer shadow-md transition-all hover:-translate-y-0.5 active:scale-95">
                      <i class="fi fi-rr-camera text-xs"></i>
                      <input type="file" id="ownerProfileUpload" class="hidden" accept="image/*">
                    </label>
                  </div>
                </div>

                <div class="flex items-start justify-between gap-4 mb-4">
                  <div>
                    <h3 class="text-base md:text-lg font-bold text-gray-800">{{ $propertyOwner->first_name }} {{ $propertyOwner->last_name }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $propertyOwner->occupation ?? 'N/A' }}</p>
                    <div class="flex items-center gap-1.5 text-xs mt-1.5">
                      <i class="fi fi-rr-star text-yellow-500"></i>
                      <span class="font-semibold text-gray-700">N/A Rating</span>
                      <span class="text-gray-400">• {{ Str::limit($propertyOwner->address, 30) }}</span>
                    </div>
                  </div>
                  <a href="{{ route('admin.projectManagement.messages') }}?compose=1&recipient_id={{ $propertyOwner->user_id }}&recipient_name={{ urlencode($propertyOwner->first_name . ' ' . $propertyOwner->last_name) }}" class="flex items-center gap-1.5 bg-orange-500 hover:bg-orange-600 hover:shadow-md hover:-translate-y-0.5 text-white px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all active:scale-95">
                    <i class="fi fi-rr-comment-alt text-sm"></i>
                    <span>Message</span>
                  </a>
                </div>

                <!-- Quick details -->
                <div class="grid grid-cols-2 gap-2 mt-3">
                  <div class="flex items-center gap-2 border border-blue-200 bg-blue-50 p-2 rounded-lg">
                    <div class="w-6 h-6 bg-white border border-blue-300 rounded-md grid place-items-center flex-shrink-0">
                      <i class="fi fi-rr-calendar text-blue-600 text-[10px]"></i>
                    </div>
                    <div>
                      <p class="text-[10px] text-blue-600 font-medium">Registered</p>
                      <p class="text-xs font-semibold text-blue-900">{{ \Carbon\Carbon::parse($propertyOwner->created_at)->format('F j, Y') }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-2 border border-blue-200 bg-blue-50 p-2 rounded-lg">
                    <div class="w-6 h-6 bg-white border border-blue-300 rounded-md grid place-items-center flex-shrink-0">
                      <i class="fi fi-rr-cake-birthday text-blue-600 text-[10px]"></i>
                    </div>
                    <div>
                      <p class="text-[10px] text-blue-600 font-medium">Age</p>
                      <p class="text-xs font-semibold text-blue-900">{{ $propertyOwner->age }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-2 border border-blue-200 bg-blue-50 p-2 rounded-lg">
                    <div class="w-6 h-6 bg-white border border-blue-300 rounded-md grid place-items-center flex-shrink-0">
                      <i class="fi fi-rr-phone-call text-blue-600 text-[10px]"></i>
                    </div>
                    <div>
                      <p class="text-[10px] text-blue-600 font-medium">Contact</p>
                      <p class="text-xs font-semibold text-blue-900">{{ $propertyOwner->phone_number ?? $propertyOwner->phone ?? 'N/A' }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-2 border border-blue-200 bg-blue-50 p-2 rounded-lg">
                    <div class="w-6 h-6 bg-white border border-blue-300 rounded-md grid place-items-center flex-shrink-0">
                      <i class="fi fi-rr-envelope text-blue-600 text-[10px]"></i>
                    </div>
                    <div>
                      <p class="text-[10px] text-blue-600 font-medium">Email</p>
                      <p class="text-xs font-semibold text-blue-900 truncate max-w-[130px]">{{ $propertyOwner->email }}</p>
                    </div>
                  </div>
                </div>

                <div class="mt-3 pt-3 border-t border-gray-200">
                  <div class="grid grid-cols-2 gap-2">
                    <div class="text-center p-2 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg border border-emerald-300">
                      <p class="text-xl font-bold text-emerald-700">{{ $propertyOwner->completed_projects_count }}</p>
                      <p class="text-[10px] text-emerald-600 font-medium">Projects done</p>
                    </div>
                    <div class="text-center p-2 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg border border-orange-300">
                      <p class="text-xl font-bold text-orange-700">{{ $propertyOwner->ongoing_projects_count }}</p>
                      <p class="text-[10px] text-orange-600 font-medium">Ongoing</p>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- List of Projects Section -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
              <div class="px-4 py-2.5 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-transparent flex items-center justify-between">
                  <h2 class="text-xs font-semibold text-blue-700 flex items-center gap-1.5">
                    <i class="fi fi-rr-list text-blue-600 text-xs"></i>
                    List of Projects
                  </h2>
                  <select id="projectFilter" class="px-2.5 py-1 text-[11px] border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none transition cursor-pointer bg-white">
                    <option value="all">All Projects</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="halt">Halt</option>
                    <option value="terminated">Terminated</option>
                    <option value="completed">Completed</option>
                    <option value="bidding_closed">Bidding Closed</option>
                  </select>
              </div>
              <div class="p-3 space-y-2" id="projectsList">
                @forelse($propertyOwner->projects as $project)
                <div class="project-card border border-gray-200 rounded-lg overflow-hidden hover:border-blue-300 hover:bg-blue-50 hover:shadow-sm transition" data-status="{{ $project->project_status }}">
                  <div class="flex gap-0">
                    <div class="w-14 h-auto overflow-hidden flex-shrink-0 bg-gradient-to-br from-blue-100 to-blue-50 grid place-items-center">
                      <i class="fi fi-rr-building text-2xl text-blue-400"></i>
                    </div>
                    <div class="flex-1 p-2.5 space-y-1">
                      <div class="flex items-start justify-between gap-2">
                        <h3 class="text-xs font-semibold text-gray-800">{{ $project->project_title }}</h3>
                        @php
                            $statusColors = [
                                'completed' => 'bg-green-100 text-green-700',
                                'in_progress' => 'bg-blue-100 text-blue-700',
                                'open' => 'bg-green-100 text-green-700',
                                'bidding_closed' => 'bg-yellow-100 text-yellow-700',
                                'terminated' => 'bg-red-100 text-red-700',
                            ];
                            $statusColor = $statusColors[$project->project_status] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="px-2 py-0.5 {{ $statusColor }} rounded-full text-[10px] font-semibold whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $project->project_status)) }}</span>
                      </div>
                      <p class="text-[11px] text-gray-500 line-clamp-1">{{ $project->project_description }}</p>
                      <div class="flex flex-wrap items-center gap-3 text-[10px] text-gray-400">
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-marker text-gray-400"></i>
                          {{ $project->project_location }}
                        </span>
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-calendar text-gray-400"></i>
                          {{ \Carbon\Carbon::parse($project->created_at)->format('M Y') }}
                        </span>
                      </div>
                      @if($project->contractor_first_name)
                      <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-1.5">
                          <div class="w-5 h-5 rounded-full bg-orange-500 text-white text-[9px] font-bold grid place-items-center">
                            {{ substr($project->contractor_first_name, 0, 1) . substr($project->contractor_last_name, 0, 1) }}
                          </div>
                          <span class="text-[11px] font-medium text-gray-600">{{ $project->contractor_first_name }} {{ $project->contractor_last_name }}</span>
                        </div>
                        <button class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 hover:shadow-sm hover:border-blue-300 hover:-translate-y-0.5 text-blue-600 text-[10px] font-medium rounded border border-blue-200 transition-all active:scale-95">
                          View
                        </button>
                      </div>
                      @endif
                    </div>
                  </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    No projects found.
                </div>
                @endforelse
              </div>
            </section>
          </div>

          <!-- Right 1/3: Company + Documents -->
          <div class="space-y-3 xl:sticky xl:top-32 xl:self-start xl:max-h-[calc(100vh-8.5rem)] xl:overflow-y-auto xl:pr-1 column-scroll-hidden">
            <!-- Company -->
            @if($propertyOwner->user_type === 'both' && isset($propertyOwner->contractor_details))
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
              <div class="px-4 py-2.5 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-transparent">
                <h3 class="text-xs font-semibold text-blue-700 flex items-center gap-1.5">
                  <i class="fi fi-rr-building text-blue-600 text-xs"></i>
                  Company
                </h3>
              </div>
              <div class="p-3">
                <div class="space-y-3">
                  <div class="overflow-hidden rounded-md">
                    @if($propertyOwner->cover_photo)
                      <img src="{{ asset('storage/' . $propertyOwner->cover_photo) }}" alt="Company Cover" class="w-full h-36 object-cover group-hover:scale-110 transition-transform duration-500 cursor-pointer" loading="lazy" data-image="{{ asset('storage/' . $propertyOwner->cover_photo) }}" data-title="Company Cover" onclick="openImageModal(this.dataset.image, this.dataset.title)">
                    @else
                        <div class="w-full h-20 bg-gray-200 flex items-center justify-center text-gray-400">
                            <i class="fi fi-rr-picture text-2xl"></i>
                        </div>
                    @endif
                  </div>
                  <div>
                    <h4 class="text-sm font-semibold text-gray-800">{{ $propertyOwner->contractor_details->company_name }}</h4>
                    <p class="text-xs text-gray-500">{{ ucfirst($propertyOwner->contractor_details->position) }}</p>
                    <span class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-[10px] font-medium">
                      <i class="fi fi-rr-briefcase text-[10px]"></i>
                      {{ $propertyOwner->contractor_details->contractor_type }}
                    </span>
                  </div>
                </div>
              </div>
            </section>
            @endif
            <!-- Documents -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
              <div class="px-4 py-2.5 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-transparent">
                <h3 class="text-xs font-semibold text-blue-700 flex items-center gap-1.5">
                  <i class="fi fi-rr-folder text-blue-600 text-xs"></i>
                  Documents
                </h3>
              </div>
              <div class="p-3 space-y-2">
                <!-- Police clearance -->
                @if($propertyOwner->police_clearance)
                <div class="open-doc-btn flex items-center gap-2 p-2 border border-blue-200 bg-blue-50 rounded-lg hover:bg-blue-100 hover:border-blue-300 transition cursor-pointer" data-doc-src="{{ asset('storage/' . $propertyOwner->police_clearance) }}" data-doc-title="Police Clearance">
                  <div class="w-7 h-7 bg-blue-100 rounded-md grid place-items-center flex-shrink-0">
                    <i class="fi fi-rr-file text-blue-600 text-xs"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-800">Police Clearance</p>
                    <p class="text-[10px] text-gray-400 truncate">{{ basename($propertyOwner->police_clearance) }}</p>
                  </div>
                  <a href="{{ asset('storage/' . $propertyOwner->police_clearance) }}" download onclick="event.stopPropagation()" class="p-1 hover:bg-gray-200 rounded-md transition" aria-label="Download Police Clearance">
                    <i class="fi fi-rr-download text-gray-400 text-xs"></i>
                  </a>
                </div>
                @endif

                <!-- Valid ID -->
                @if($propertyOwner->valid_id_photo)
                <div class="open-doc-btn flex items-center gap-2 p-2 border border-blue-200 bg-blue-50 rounded-lg hover:bg-blue-100 hover:border-blue-300 transition cursor-pointer" data-doc-src="{{ asset('storage/' . $propertyOwner->valid_id_photo) }}" data-doc-title="Valid ID ({{ $propertyOwner->valid_id_name }})">
                  <div class="w-7 h-7 bg-blue-100 rounded-md grid place-items-center flex-shrink-0">
                    <i class="fi fi-rr-id-badge text-blue-600 text-xs"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-800">Valid ID ({{ $propertyOwner->valid_id_name }})</p>
                    <p class="text-[10px] text-gray-400 truncate">{{ basename($propertyOwner->valid_id_photo) }}</p>
                  </div>
                  <a href="{{ asset('storage/' . $propertyOwner->valid_id_photo) }}" download onclick="event.stopPropagation()" class="p-1 hover:bg-gray-200 rounded-md transition" aria-label="Download Valid ID">
                    <i class="fi fi-rr-download text-gray-400 text-xs"></i>
                  </a>
                </div>
                @endif

                <!-- Valid ID (Back) -->
                @if($propertyOwner->valid_id_back_photo)
                <div class="open-doc-btn flex items-center gap-2 p-2 border border-blue-200 bg-blue-50 rounded-lg hover:bg-blue-100 hover:border-blue-300 transition cursor-pointer" data-doc-src="{{ asset('storage/' . $propertyOwner->valid_id_back_photo) }}" data-doc-title="Valid ID (Back)">
                  <div class="w-7 h-7 bg-blue-100 rounded-md grid place-items-center flex-shrink-0">
                    <i class="fi fi-rr-id-badge text-blue-600 text-xs"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-800">Valid ID (Back)</p>
                    <p class="text-[10px] text-gray-400 truncate">{{ basename($propertyOwner->valid_id_back_photo) }}</p>
                  </div>
                  <a href="{{ asset('storage/' . $propertyOwner->valid_id_back_photo) }}" download onclick="event.stopPropagation()" class="p-1 hover:bg-gray-200 rounded-md transition" aria-label="Download Valid ID Back">
                    <i class="fi fi-rr-download text-gray-400 text-xs"></i>
                  </a>
                </div>
                @endif
              </div>
            </section>

            <!-- Staff Company Card - Show if user is staff in a contractor company -->
            @if($propertyOwner->user_type === 'owner_staff' && isset($propertyOwner->contractor_details))
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
              <div class="px-4 py-2.5 border-b border-orange-200 bg-orange-50">
                <h3 class="text-xs font-semibold text-orange-700 flex items-center gap-1.5">
                  <i class="fi fi-rr-briefcase text-orange-600 text-xs"></i>
                  Company Staff
                </h3>
              </div>
              <div class="p-3 space-y-2">
                <!-- Company Name -->
                <div>
                  <p class="text-[10px] text-gray-600 font-semibold uppercase tracking-wide">Company</p>
                  <p class="text-xs font-bold text-gray-800">{{ $propertyOwner->contractor_details->company_name }}</p>
                </div>

                <!-- Role -->
                <div class="bg-orange-50 rounded-lg p-2.5 border border-orange-200">
                  <p class="text-[10px] text-gray-600 font-semibold mb-0.5">Role</p>
                  <p class="text-xs font-bold text-gray-800">{{ ucfirst(str_replace('_', ' ', $propertyOwner->contractor_details->position)) }}</p>
                </div>

                <!-- Company Type & Years of Experience -->
                <div class="grid grid-cols-2 gap-2">
                  <div class="bg-orange-50 rounded-lg p-2.5 border border-orange-200">
                    <p class="text-[10px] text-gray-600 font-semibold mb-0.5">Company Type</p>
                    <p class="text-xs font-bold text-gray-800">{{ $propertyOwner->contractor_details->contractor_type }}</p>
                  </div>
                  <div class="bg-orange-50 rounded-lg p-2.5 border border-orange-200">
                    <p class="text-[10px] text-gray-600 font-semibold mb-0.5">Experience</p>
                    <p class="text-xs font-bold text-gray-800">{{ $propertyOwner->contractor_details->years_of_experience ?? 'N/A' }} yrs</p>
                  </div>
                </div>

                <!-- Services Offered -->
                @if($propertyOwner->contractor_details->services_offered)
                <div class="bg-orange-50 rounded-lg p-2.5 border border-orange-200">
                  <p class="text-[10px] text-gray-600 font-semibold mb-0.5">Services</p>
                  <p class="text-xs font-semibold text-gray-800 line-clamp-2">{{ $propertyOwner->contractor_details->services_offered }}</p>
                </div>
                @endif

                <!-- Business Address -->
                @if($propertyOwner->contractor_details->business_address)
                <div class="bg-orange-50 rounded-lg p-2.5 border border-orange-200">
                  <p class="text-[10px] text-gray-600 font-semibold mb-0.5 flex items-center gap-1">
                    <i class="fi fi-rr-marker text-orange-600 text-[9px]"></i>
                    Address
                  </p>
                  <p class="text-xs font-semibold text-gray-800 line-clamp-1">{{ $propertyOwner->contractor_details->business_address }}</p>
                </div>
                @endif

                <!-- Company Description -->
                @if($propertyOwner->contractor_details->company_description)
                <div class="bg-orange-50 rounded-lg p-2.5 border border-orange-200">
                  <p class="text-[10px] text-gray-600 font-semibold mb-0.5">Description</p>
                  <p class="text-xs text-gray-800 line-clamp-2">{{ $propertyOwner->contractor_details->company_description }}</p>
                </div>
                @endif
              </div>
            </section>
            @endif
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
                Are you sure you want to suspend <span class="font-bold text-red-600">{{ $propertyOwner->first_name }} {{ $propertyOwner->last_name }}</span>?
              </p>
            </div>
          </div>

          <!-- User Info Card -->
          <div class="bg-white rounded-lg p-2.5 border border-red-200 space-y-1.5">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white text-xs font-bold flex items-center justify-center shadow">
                {{ substr($propertyOwner->first_name, 0, 1) . substr($propertyOwner->last_name, 0, 1) }}
              </div>
              <div>
                <p class="font-semibold text-gray-800 text-xs">{{ $propertyOwner->first_name }} {{ $propertyOwner->last_name }}</p>
                <p class="text-[11px] text-gray-600">{{ $propertyOwner->occupation ?? 'N/A' }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-1.5 pt-1.5 border-t border-gray-200">
              <div class="text-center">
                <p class="text-lg font-bold text-indigo-600">{{ $propertyOwner->completed_projects_count }}</p>
                <p class="text-[10px] text-gray-600">Projects Done</p>
              </div>
              <div class="text-center">
                <p class="text-lg font-bold text-green-600">{{ $propertyOwner->ongoing_projects_count }}</p>
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
          <textarea
            id="suspendReason"
            rows="3"
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

  <!-- Edit Property Owner Modal -->
  <div id="editPropertyOwnerModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-2 sm:p-3">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-orange-600 px-3 sm:px-4 py-2.5 flex items-center justify-between rounded-t-xl shadow-lg z-10">
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
                <input type="text" id="edit_first_name" name="first_name" placeholder="Enter first name" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Occupation</label>
                <select name="occupation_id" id="edit_occupationSelect" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  <option value="">Select Occupation</option>
                  @foreach($occupations as $occupation)
                    @if(strtolower($occupation->occupation_name) !== 'others')
                      <option value="{{ $occupation->id }}">{{ $occupation->occupation_name }}</option>
                    @endif
                  @endforeach
                  <option value="others">Others</option>
                </select>
                <input type="text" name="occupation_other" id="edit_occupationOtherInput" placeholder="Please specify occupation" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition mt-1.5 hidden">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Middle name <span class="text-gray-400">(optional)</span></label>
                <input type="text" id="edit_middle_name" name="middle_name" placeholder="Enter middle name" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Date of birth</label>
                <input type="date" id="edit_date_of_birth" name="date_of_birth" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Last name</label>
                <input type="text" id="edit_last_name" name="last_name" placeholder="Enter last name" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
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
                    <input type="email" id="edit_email" name="email" placeholder="Enter email address" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="edit_username" name="username" placeholder="Enter username" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">New Password <span class="text-gray-400">(optional)</span></label>
                    <input type="password" id="edit_password" name="password" placeholder="Enter new password" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
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
                    <select id="edit_owner_address_province" name="province" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                      <option value="">Select Province</option>
                      @foreach($provinces as $province)
                        <option value="{{ $province['code'] }}" data-name="{{ $province['name'] }}">{{ $province['name'] }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">City/Municipality</label>
                    <select id="edit_owner_address_city" name="city" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition" disabled>
                      <option value="">Select City/Municipality</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Barangay</label>
                    <select id="edit_owner_address_barangay" name="barangay" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition" disabled>
                      <option value="">Select Barangay</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Street Address / Unit No.</label>
                    <input type="text" id="edit_street_address" name="street_address" placeholder="Enter street address" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Zip Code</label>
                    <input type="text" id="edit_zip_code" name="zip_code" placeholder="Enter zip code" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
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
                  <select id="edit_valid_id_id" name="valid_id_id" class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-300 focus:border-orange-300 focus:outline-none transition">
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
                    <input type="file" id="editPoliceClearanceUpload" name="police_clearance" class="hidden" accept="image/*">
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

  <!-- Universal File Viewer (UFV) -->
  <div id="documentViewerModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
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


  {{-- <script>
    function openImageModal(src, title) {
      const modal = document.getElementById('imageViewerModal');
      const img = document.getElementById('imageModalPreview');
      const titleEl = document.getElementById('imageModalTitle');

      // Reset state first
      img.classList.remove('scale-100', 'opacity-100');
      img.classList.add('scale-95', 'opacity-0');

      img.src = src;
      titleEl.textContent = title;
      modal.classList.remove('hidden');
      modal.classList.add('flex');

      // Animate in with a small delay
      setTimeout(() => {
        img.classList.remove('scale-95', 'opacity-0');
        img.classList.add('scale-100', 'opacity-100');
      }, 50);
    }

    function closeImageModal() {
      const modal = document.getElementById('imageViewerModal');
      const img = document.getElementById('imageModalPreview');

      img.classList.remove('scale-100', 'opacity-100');
      img.classList.add('scale-95', 'opacity-0');

      setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        img.src = ''; // Clear src to stop loading/playing
      }, 300);
    }
  </script> --}}

  <script src="{{ asset('js/account.js') }}" defer></script>
  <script src="{{ asset('js/admin/userManagement/propertyOwner_Views.js') }}?v={{ time() }}" defer></script>

  <!-- Suspend Account Modal Validation -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const confirmSuspendBtn = document.getElementById('confirmSuspendBtn');
      const suspendReasonInput = document.getElementById('suspendReason');
      const suspensionDurationRadios = document.querySelectorAll('input[name="suspensionDuration"]');
      const suspensionDateInput = document.getElementById('suspensionDate');
      const suspendReasonError = document.getElementById('suspendReasonError');
      const suspensionDateError = document.getElementById('suspensionDateError');
      const suspensionDateContainer = document.getElementById('suspensionDateContainer');

      // Show/hide date picker based on suspension duration
      suspensionDurationRadios.forEach(radio => {
        radio.addEventListener('change', function() {
          if (this.value === 'temporary') {
            suspensionDateContainer.style.display = 'block';
          } else {
            suspensionDateContainer.style.display = 'none';
            suspensionDateInput.value = '';
            if (suspensionDateError) {
              suspensionDateError.classList.add('hidden');
            }
          }
          // Clear date error when toggling
          if (suspensionDateError) {
            suspensionDateError.classList.add('hidden');
          }
        });
      });

      // Clear error when user starts typing in reason
      if (suspendReasonInput) {
        suspendReasonInput.addEventListener('input', function() {
          if (suspendReasonError) {
            suspendReasonError.classList.add('hidden');
          }
        });
      }

      // Clear error when user selects a date
      if (suspensionDateInput) {
        suspensionDateInput.addEventListener('change', function() {
          if (suspensionDateError) {
            suspensionDateError.classList.add('hidden');
          }
        });
      }

      // Validate form on submit
      if (confirmSuspendBtn) {
        confirmSuspendBtn.addEventListener('click', function(e) {
          let isValid = true;
          
          // Clear previous errors
          if (suspendReasonError) {
            suspendReasonError.classList.add('hidden');
          }
          if (suspensionDateError) {
            suspensionDateError.classList.add('hidden');
          }

          // Validate reason
          if (!suspendReasonInput || !suspendReasonInput.value.trim()) {
            isValid = false;
            if (suspendReasonError) {
              suspendReasonError.textContent = 'Reason for suspension is required';
              suspendReasonError.classList.remove('hidden');
            }
          }

          // Validate date if temporary suspension is selected
          const selectedDuration = document.querySelector('input[name="suspensionDuration"]:checked');
          if (selectedDuration && selectedDuration.value === 'temporary') {
            if (!suspensionDateInput || !suspensionDateInput.value) {
              isValid = false;
              if (suspensionDateError) {
                suspensionDateError.textContent = 'Please select a suspension date';
                suspensionDateError.classList.remove('hidden');
              }
            }
          }

          // Prevent submission if validation fails
          if (!isValid) {
            e.preventDefault();
            e.stopPropagation();
          }
        });
      }
    });
  </script>

  <!-- Upload Confirmation Modal -->
  <div id="uploadConfirmModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4 animate-fadeIn">
    <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0 modal-content overflow-hidden border border-gray-200">
      <div class="px-6 py-4 flex items-center gap-3 border-b border-gray-100 bg-gray-50">
        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shadow-sm border border-blue-200">
          <i class="fi fi-rr-cloud-upload mt-1"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-800">Confirm Upload</h2>
      </div>
      <div class="px-6 py-5 bg-white text-center">
        <p id="uploadConfirmMessage" class="text-gray-600 text-sm mb-4">Are you sure you want to update this image?</p>
        <div class="flex flex-col items-center">
          <div class="w-32 h-32 rounded-lg border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden bg-gray-50 mb-2">
            <img id="uploadConfirmPreview" src="" alt="Preview" class="max-w-full max-h-full object-contain">
          </div>
          <p class="text-[10px] text-gray-400">New Image Preview</p>
        </div>
      </div>
      <div class="bg-gray-50 px-6 py-4 flex items-center justify-center gap-3 border-t border-gray-200">
        <button id="cancelUploadBtn" class="flex-1 px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-all font-semibold active:scale-95">
          Cancel
        </button>
        <button id="confirmUploadBtn" class="flex-1 px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all font-semibold shadow-md active:scale-95">
          Upload
        </button>
      </div>
    </div>
  </div>

</body>


</html>
