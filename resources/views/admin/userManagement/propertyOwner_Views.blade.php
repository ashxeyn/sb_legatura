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


</head>

<body class="bg-gray-50 text-gray-800 font-sans">
  <div class="flex min-h-screen">

    @include('admin.layouts.sidebar')

    <main class="flex-1">
      @include('admin.layouts.topnav', ['pageTitle' => 'Property Owners'])

      <!-- PAGE CONTENT -->
      <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-6">
        <!-- Top row: Back + Actions -->
        <div class="flex items-center justify-between">
          <a href="{{ route('admin.userManagement.propertyOwner') }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium hover:bg-indigo-50 px-4 py-2 rounded-lg transition-all duration-300 hover:shadow-md hover:-translate-x-1">
            <i class="fi fi-rr-angle-left text-lg"></i>
            <span>Back</span>
          </a>
          <div class="flex items-center gap-3">
            {{-- remove due to redundancy --}}
            {{-- <button onclick="openEditModal({{ $propertyOwner->owner_id }})" class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-all duration-300 text-gray-700 flex items-center gap-2 hover:shadow-md hover:scale-105">
              <i class="fi fi-rr-edit"></i>
              <span>Edit</span>
            </button> --}}
            @if($propertyOwner->is_active == 1)
            <button id="suspendPropertyOwnerBtn" data-id="{{ $propertyOwner->owner_id }}" class="px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all duration-300 flex items-center gap-2 hover:shadow-lg hover:scale-105">
              <i class="fi fi-rr-ban"></i>
              <span>Suspend</span>
            </button>
            @else
            <div class="px-4 py-2 rounded-lg bg-red-100 text-red-600 font-medium flex items-center gap-2 cursor-default">
                <i class="fi fi-rr-ban"></i>
                <span>Suspended</span>
            </div>
            @endif
          </div>
        </div>

        <!-- Info grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Left 2/3 -->
          <div class="xl:col-span-2 space-y-6">
            <!-- Profile card -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 md:px-8 py-6">
                <h2 class="text-xl md:text-2xl font-bold text-white">Property Owner Details</h2>
              </div>
              <div class="p-6 md:p-8">
                <div class="flex flex-col md:flex-row items-start gap-6">
                  <div class="flex-shrink-0">
                    @if($propertyOwner->profile_pic)
                        <img src="{{ asset('storage/' . $propertyOwner->profile_pic) }}" alt="Profile" class="w-28 h-28 md:w-32 md:h-32 rounded-full object-cover shadow-xl ring-4 ring-blue-100 hover:ring-blue-200 hover:scale-110 transition-all duration-300 cursor-pointer">
                    @else
                        <div class="w-28 h-28 md:w-32 md:h-32 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white font-bold text-3xl md:text-4xl grid place-items-center shadow-xl ring-4 ring-blue-100 hover:ring-blue-200 hover:scale-110 transition-all duration-300 cursor-pointer">
                            {{ substr($propertyOwner->first_name, 0, 1) . substr($propertyOwner->last_name, 0, 1) }}
                        </div>
                    @endif
                  </div>
                  <div class="flex-1 w-full">
                    <div class="flex items-start justify-between gap-4">
                      <div>
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-800">{{ $propertyOwner->first_name }} {{ $propertyOwner->last_name }}</h3>
                        <p class="text-gray-600 mt-1">Occupation: {{ $propertyOwner->occupation ?? 'N/A' }}</p>
                        <div class="flex items-center gap-2 text-sm mt-2">
                          <i class="fi fi-rr-star text-yellow-500"></i>
                          <span class="font-semibold text-gray-800">N/A Rating</span>
                          <span class="text-gray-500">• {{ Str::limit($propertyOwner->address, 30) }}</span>
                        </div>
                      </div>

                    </div>

                    <!-- Quick details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                      <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-lg hover:bg-gradient-to-br hover:from-indigo-50 hover:to-indigo-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                        <div class="w-11 h-11 bg-indigo-100 rounded-full grid place-items-center hover:scale-110 transition-transform">
                          <i class="fi fi-rr-calendar text-indigo-600"></i>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Registered Date</p>
                          <p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($propertyOwner->created_at)->format('F d, Y') }}</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-lg hover:bg-gradient-to-br hover:from-green-50 hover:to-green-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                        <div class="w-11 h-11 bg-green-100 rounded-full grid place-items-center hover:scale-110 transition-transform">
                          <i class="fi fi-rr-cake-birthday text-green-600"></i>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Age</p>
                          <p class="font-semibold text-gray-800">{{ $propertyOwner->age }}</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-lg hover:bg-gradient-to-br hover:from-purple-50 hover:to-purple-100 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                        <div class="w-11 h-11 bg-purple-100 rounded-full grid place-items-center hover:scale-110 transition-transform">
                          <i class="fi fi-rr-envelope text-purple-600"></i>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Email</p>
                          <p class="font-semibold text-gray-800">{{ $propertyOwner->email }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Account Profile section (cover) -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="relative h-44 md:h-56 bg-gradient-to-r from-slate-100 to-slate-200 overflow-hidden group">
                <img src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=1200&auto=format&fit=crop" alt="Cover" class="w-full h-full object-cover opacity-90 group-hover:scale-110 transition-transform duration-500" loading="lazy">
                <div class="absolute -bottom-10 left-6">
                  <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-white grid place-items-center shadow-xl ring-4 ring-white overflow-hidden hover:ring-8 hover:ring-indigo-100 hover:scale-110 transition-all duration-300 cursor-pointer">
                    @if($propertyOwner->profile_pic)
                        <img src="{{ asset('storage/' . $propertyOwner->profile_pic) }}" alt="Avatar" class="w-full h-full object-cover hover:scale-110 transition-transform duration-300">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-600 text-white font-bold text-2xl grid place-items-center">
                            {{ substr($propertyOwner->first_name, 0, 1) . substr($propertyOwner->last_name, 0, 1) }}
                        </div>
                    @endif
                  </div>
                </div>
              </div>
              <div class="pt-14 px-6 md:px-8 pb-8">
                <h3 class="text-2xl font-bold text-gray-800">{{ $propertyOwner->first_name }} {{ $propertyOwner->last_name }}</h3>
                <div class="flex items-center gap-2 text-sm mt-1">
                  <i class="fi fi-rr-star text-yellow-500"></i>
                  <span class="font-semibold">N/A Rating</span>
                  <span class="text-gray-500">• {{ Str::limit($propertyOwner->address, 30) }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-6">
                  <div class="text-center p-6 bg-indigo-50 rounded-lg border border-indigo-200 hover:bg-indigo-100 hover:shadow-lg hover:-translate-y-2 transition-all duration-300 cursor-pointer group">
                    <p class="text-3xl md:text-4xl font-bold text-indigo-600 group-hover:scale-110 transition-transform">{{ $propertyOwner->completed_projects_count }}</p>
                    <p class="text-sm text-gray-600 mt-2">Projects done</p>
                  </div>
                  <div class="text-center p-6 bg-green-50 rounded-lg border border-green-200 hover:bg-green-100 hover:shadow-lg hover:-translate-y-2 transition-all duration-300 cursor-pointer group">
                    <p class="text-3xl md:text-4xl font-bold text-green-600 group-hover:scale-110 transition-transform">{{ $propertyOwner->ongoing_projects_count }}</p>
                    <p class="text-sm text-gray-600 mt-2">Ongoing projects</p>
                  </div>
                </div>
              </div>
            </section>

            <!-- List of Projects Section -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 md:px-8 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fi fi-rr-list text-indigo-600"></i>
                    List of Projects
                  </h2>
                  <select id="projectFilter" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none hover:border-indigo-400 transition cursor-pointer">
                    <option value="all">All Projects</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="halt">Halt</option>
                    <option value="terminated">Terminated</option>
                    <option value="completed">Completed</option>
                    <option value="bidding_closed">Bidding Closed</option>
                  </select>
                </div>
              </div>
              <div class="p-6 space-y-4" id="projectsList">
                @forelse($propertyOwner->projects as $project)
                <div class="project-card border border-gray-200 rounded-xl overflow-hidden hover:border-indigo-300 hover:shadow-lg transition-all duration-300 group" data-status="{{ $project->project_status }}">
                  <div class="flex flex-col sm:flex-row gap-4">
                    <div class="sm:w-32 h-32 sm:h-auto overflow-hidden flex-shrink-0 bg-gray-100 grid place-items-center">
                      <i class="fi fi-rr-building text-4xl text-gray-300"></i>
                    </div>
                    <div class="flex-1 p-4 space-y-2">
                      <div class="flex items-start justify-between gap-2">
                        <h3 class="font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">{{ $project->project_title }}</h3>
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
                        <span class="px-2.5 py-1 {{ $statusColor }} rounded-full text-xs font-semibold whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $project->project_status)) }}</span>
                      </div>
                      <p class="text-sm text-gray-600 line-clamp-2">{{ $project->project_description }}</p>
                      <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 pt-1">
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-marker text-indigo-600"></i>
                          {{ $project->project_location }}
                        </span>
                        <span class="flex items-center gap-1">
                          <i class="fi fi-rr-calendar text-green-600"></i>
                          {{ \Carbon\Carbon::parse($project->created_at)->format('M Y') }}
                        </span>
                      </div>
                      @if($project->contractor_first_name)
                      <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                          <div class="w-8 h-8 rounded-full bg-orange-500 text-white text-xs font-bold grid place-items-center">
                            {{ substr($project->contractor_first_name, 0, 1) . substr($project->contractor_last_name, 0, 1) }}
                          </div>
                          <span class="text-sm font-medium text-gray-700">{{ $project->contractor_first_name }} {{ $project->contractor_last_name }}</span>
                        </div>
                        <button class="px-4 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white text-sm rounded-lg transition-all hover:shadow-md hover:scale-105 active:scale-95">
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
          <div class="space-y-6">
            <!-- Company -->
            @if($propertyOwner->user_type === 'both' && isset($propertyOwner->contractor_details))
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                  <i class="fi fi-rr-building text-indigo-600"></i>
                  Company
                </h3>
              </div>
              <div class="p-6">
                <div class="space-y-3">
                  <div class="overflow-hidden rounded-lg shadow group">
                    @if($propertyOwner->cover_photo)
                      <img src="{{ asset('storage/' . $propertyOwner->cover_photo) }}" alt="Company Cover" class="w-full h-36 object-cover group-hover:scale-110 transition-transform duration-500 cursor-pointer" loading="lazy" onclick="openImageModal('{{ asset('storage/' . $propertyOwner->cover_photo) }}', 'Company Cover')">
                    @else
                        <div class="w-full h-36 bg-gray-200 flex items-center justify-center text-gray-400">
                            <i class="fi fi-rr-picture text-4xl"></i>
                        </div>
                    @endif
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-800">{{ $propertyOwner->contractor_details->company_name }}</h4>
                    <p class="text-sm text-gray-600">Position: {{ ucfirst($propertyOwner->contractor_details->position) }}</p>
                    <span class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-orange-100 text-orange-700 rounded-full text-sm">
                      <i class="fi fi-rr-briefcase"></i>
                      {{ $propertyOwner->contractor_details->contractor_type }}
                    </span>
                  </div>
                </div>
              </div>
            </section>
            @endif
            <!-- Documents -->
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300">
              <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                  <i class="fi fi-rr-folder text-indigo-600"></i>
                  Documents
                </h3>
              </div>
              <div class="p-6 space-y-4">
                <!-- Police clearance -->
                @if($propertyOwner->police_clearance)
                <div onclick="openImageModal('{{ asset('storage/' . $propertyOwner->police_clearance) }}', 'Police Clearance')" class="p-4 bg-gray-50 rounded-lg hover:bg-purple-50 transition-all duration-300 hover:shadow-md hover:-translate-y-1 group cursor-pointer">
                  <div class="flex items-start gap-3 mb-3">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg grid place-items-center group-hover:scale-110 group-hover:rotate-6 transition-all">
                      <i class="fi fi-rr-file text-purple-600 text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="font-semibold text-gray-800 group-hover:text-purple-700 transition-colors">Police Clearance</p>
                      <p class="text-sm text-gray-500">{{ basename($propertyOwner->police_clearance) }}</p>
                    </div>
                    <a href="{{ asset('storage/' . $propertyOwner->police_clearance) }}" download onclick="event.stopPropagation()" class="p-2 hover:bg-purple-200 rounded-lg transition-all hover:scale-110" aria-label="Download Police Clearance">
                      <i class="fi fi-rr-download text-gray-600 hover:text-purple-600"></i>
                    </a>
                  </div>
                  <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium inline-flex items-center gap-1 group-hover:bg-green-200 transition-colors">
                    <i class="fi fi-rr-check-circle"></i> Uploaded
                  </span>
                </div>
                @endif

                <!-- Valid ID -->
                @if($propertyOwner->valid_id_photo)
                <div onclick="openImageModal('{{ asset('storage/' . $propertyOwner->valid_id_photo) }}', 'Valid ID (Front)')" class="p-4 bg-gray-50 rounded-lg hover:bg-blue-50 transition-all duration-300 hover:shadow-md hover:-translate-y-1 group cursor-pointer">
                  <div class="flex items-start gap-3 mb-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg grid place-items-center group-hover:scale-110 group-hover:rotate-6 transition-all">
                      <i class="fi fi-rr-id-badge text-blue-600 text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="font-semibold text-gray-800 group-hover:text-blue-700 transition-colors">Valid ID ({{ $propertyOwner->valid_id_name }})</p>
                      <p class="text-sm text-gray-500">{{ basename($propertyOwner->valid_id_photo) }}</p>
                    </div>
                    <a href="{{ asset('storage/' . $propertyOwner->valid_id_photo) }}" download onclick="event.stopPropagation()" class="p-2 hover:bg-blue-200 rounded-lg transition-all hover:scale-110" aria-label="Download Valid ID">
                      <i class="fi fi-rr-download text-gray-600 hover:text-blue-600"></i>
                    </a>
                  </div>
                  <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium inline-flex items-center gap-1 group-hover:bg-green-200 transition-colors">
                    <i class="fi fi-rr-check-circle"></i> Uploaded
                  </span>
                </div>
                @endif

                <!-- Valid ID (Back) -->
                @if($propertyOwner->valid_id_back_photo)
                <div onclick="openImageModal('{{ asset('storage/' . $propertyOwner->valid_id_back_photo) }}', 'Valid ID (Back)')" class="p-4 bg-gray-50 rounded-lg hover:bg-blue-50 transition-all duration-300 hover:shadow-md hover:-translate-y-1 group cursor-pointer">
                  <div class="flex items-start gap-3 mb-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg grid place-items-center group-hover:scale-110 group-hover:rotate-6 transition-all">
                      <i class="fi fi-rr-id-badge text-blue-600 text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="font-semibold text-gray-800 group-hover:text-blue-700 transition-colors">Valid ID (Back)</p>
                      <p class="text-sm text-gray-500">{{ basename($propertyOwner->valid_id_back_photo) }}</p>
                    </div>
                    <a href="{{ asset('storage/' . $propertyOwner->valid_id_back_photo) }}" download onclick="event.stopPropagation()" class="p-2 hover:bg-blue-200 rounded-lg transition-all hover:scale-110" aria-label="Download Valid ID Back">
                      <i class="fi fi-rr-download text-gray-600 hover:text-blue-600"></i>
                    </a>
                  </div>
                  <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium inline-flex items-center gap-1 group-hover:bg-green-200 transition-colors">
                    <i class="fi fi-rr-check-circle"></i> Uploaded
                  </span>
                </div>
                @endif
              </div>
            </section>

            <!-- Staff Company Card - Show if user is staff in a contractor company -->
            @if($propertyOwner->user_type === 'owner_staff' && isset($propertyOwner->contractor_details))
            <section class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
              <div class="bg-gradient-to-r from-amber-50 to-amber-100 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                  <i class="fi fi-rr-briefcase text-amber-600"></i>
                  Company Staff
                </h3>
              </div>
              <div class="p-6 space-y-4">
                <!-- Company Name -->
                <div>
                  <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Company Name</p>
                  <h4 class="font-bold text-gray-800 text-lg">{{ $propertyOwner->contractor_details->company_name }}</h4>
                </div>

                <!-- Role -->
                <div class="bg-amber-50 rounded-lg p-3 border border-amber-200">
                  <p class="text-xs text-gray-600 font-semibold mb-1">Role</p>
                  <p class="font-bold text-amber-700">{{ ucfirst(str_replace('_', ' ', $propertyOwner->contractor_details->position)) }}</p>
                </div>

                <!-- Company Type & Years of Experience -->
                <div class="grid grid-cols-2 gap-3">
                  <div class="bg-orange-50 rounded-lg p-3 border border-orange-200">
                    <p class="text-xs text-gray-600 font-semibold mb-1">Company Type</p>
                    <p class="font-bold text-orange-700 text-sm">{{ $propertyOwner->contractor_details->contractor_type }}</p>
                  </div>
                  <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                    <p class="text-xs text-gray-600 font-semibold mb-1">Years of Experience</p>
                    <p class="font-bold text-blue-700">{{ $propertyOwner->contractor_details->years_of_experience ?? 'N/A' }} years</p>
                  </div>
                </div>

                <!-- Services Offered -->
                @if($propertyOwner->contractor_details->services_offered)
                <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                  <p class="text-xs text-gray-600 font-semibold mb-1">Services Offered</p>
                  <p class="font-semibold text-green-700 text-sm">{{ $propertyOwner->contractor_details->services_offered }}</p>
                </div>
                @endif

                <!-- Business Address -->
                @if($propertyOwner->contractor_details->business_address)
                <div class="bg-purple-50 rounded-lg p-3 border border-purple-200">
                  <p class="text-xs text-gray-600 font-semibold mb-1 flex items-center gap-1">
                    <i class="fi fi-rr-marker text-purple-600"></i>
                    Business Address
                  </p>
                  <p class="font-semibold text-purple-700 text-sm">{{ $propertyOwner->contractor_details->business_address }}</p>
                </div>
                @endif

                <!-- Company Description -->
                @if($propertyOwner->contractor_details->company_description)
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                  <p class="text-xs text-gray-600 font-semibold mb-1">Company Description</p>
                  <p class="text-gray-700 text-sm leading-relaxed">{{ $propertyOwner->contractor_details->company_description }}</p>
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
  <div id="suspendAccountModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[85vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fi fi-rr-exclamation text-white text-xl"></i>
          </div>
          <h2 class="text-xl font-bold text-white">Suspend Account</h2>
        </div>
        <button id="closeSuspendModalBtn" class="text-white hover:text-red-100 transition-all p-2 rounded-lg hover:bg-white hover:bg-opacity-20 hover:rotate-90 duration-300">
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
                Are you sure you want to suspend <span class="font-bold text-red-600">{{ $propertyOwner->first_name }} {{ $propertyOwner->last_name }}</span>?
              </p>
            </div>
          </div>

          <!-- User Info Card -->
          <div class="bg-white rounded-lg p-3 border border-red-200 space-y-2">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white font-bold flex items-center justify-center shadow-md">
                {{ substr($propertyOwner->first_name, 0, 1) . substr($propertyOwner->last_name, 0, 1) }}
              </div>
              <div>
                <p class="font-semibold text-gray-800 text-sm">{{ $propertyOwner->first_name }} {{ $propertyOwner->last_name }}</p>
                <p class="text-xs text-gray-600">{{ $propertyOwner->occupation ?? 'N/A' }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200">
              <div class="text-center">
                <p class="text-xl font-bold text-indigo-600">{{ $propertyOwner->completed_projects_count }}</p>
                <p class="text-xs text-gray-600">Projects Done</p>
              </div>
              <div class="text-center">
                <p class="text-xl font-bold text-green-600">{{ $propertyOwner->ongoing_projects_count }}</p>
                <p class="text-xs text-gray-600">Ongoing Projects</p>
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
          <textarea
            id="suspendReason"
            rows="3"
            placeholder="Please provide a detailed reason for suspending this account..."
            class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 transition-all hover:border-red-300 bg-white resize-none text-sm"
          ></textarea>
          <p id="suspendReasonError" class="text-red-500 text-xs mt-1 hidden"></p>
          <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
            <i class="fi fi-rr-info"></i>
            This reason will be recorded and may be shared with the user.
          </p>
        </div>

        <!-- Suspension Options -->
        <div class="space-y-2">
          <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
            <i class="fi fi-rr-calendar text-red-500"></i>
            Suspension Duration
          </label>
          <div class="grid grid-cols-2 gap-4">
            <label class="relative cursor-pointer group">
              <input type="radio" name="suspensionDuration" value="temporary" class="peer sr-only" checked>
              <div class="border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300 hover:shadow-md">
                <i class="fi fi-rr-clock text-xl text-gray-400 peer-checked:text-red-500 transition-colors mb-1"></i>
                <p class="font-semibold text-gray-700 text-sm peer-checked:text-red-600">Temporary</p>
                <p class="text-xs text-gray-500 mt-1">Select Date</p>
              </div>
            </label>
            <label class="relative cursor-pointer group">
              <input type="radio" name="suspensionDuration" value="permanent" class="peer sr-only">
              <div class="border-2 border-gray-300 rounded-lg p-3 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300 hover:shadow-md">
                <i class="fi fi-rr-ban text-xl text-gray-400 peer-checked:text-red-500 transition-colors mb-1"></i>
                <p class="font-semibold text-gray-700 text-sm peer-checked:text-red-600">Permanent</p>
                <p class="text-xs text-gray-500 mt-1">Account deletion</p>
              </div>
            </label>
          </div>

          <!-- Date Picker for Temporary Suspension -->
          <div id="suspensionDateContainer" class="mt-3 transition-all duration-300 overflow-hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Suspension Until</label>
            <input type="date" id="suspensionDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
            <p id="suspensionDateError" class="text-red-500 text-xs mt-1 hidden"></p>
            <p class="text-xs text-gray-500 mt-1">The account will be automatically reactivated after this date.</p>
          </div>
        </div>

        <!-- Consequences Warning -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded-r-lg">
          <div class="flex gap-2">
            <i class="fi fi-rr-triangle-warning text-yellow-600 flex-shrink-0 mt-0.5"></i>
            <div class="text-sm text-gray-700 space-y-1">
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
      <div class="sticky bottom-0 bg-white border-t-2 border-gray-200 px-6 py-4 rounded-b-2xl flex items-center justify-end gap-3">
        <button id="cancelSuspendBtn" class="px-5 py-2.5 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-semibold hover:shadow-md hover:scale-105 active:scale-95 text-sm">
          Cancel
        </button>
        <button id="confirmSuspendBtn" class="px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg transition-all font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95 flex items-center gap-2 text-sm">
          <i class="fi fi-rr-shield-check"></i>
          Suspend Account
        </button>
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

  <!-- Image Viewer Modal -->
  <div id="imageViewerModal" class="fixed inset-0 bg-black bg-opacity-90 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4" onclick="closeImageModal()">
    <div class="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center" onclick="event.stopPropagation()">
      <button onclick="closeImageModal()" class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors p-2">
        <i class="fi fi-rr-cross text-2xl"></i>
      </button>
      <h3 id="imageModalTitle" class="text-white text-xl font-bold mb-4">Document Preview</h3>
      <img id="imageModalPreview" src="" alt="Document Preview" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl transform transition-all duration-300 scale-95 opacity-0">
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

  <script src="{{ asset('js/admin/userManagement/propertyOwner.js') }}" defer></script>
  <script src="{{ asset('js/account.js') }}" defer></script>
  <script src="{{ asset('js/admin/userManagement/propertyOwner_Views.js') }}" defer></script>

</body>

</html>
