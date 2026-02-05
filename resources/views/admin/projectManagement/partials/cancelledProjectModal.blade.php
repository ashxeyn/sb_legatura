  <!-- Cancelled/Terminated Project Modal -->
<div id="cancelledProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
      @isset($project)
      <!-- Header -->
      <div class="relative px-6 py-10 bg-gradient-to-r from-gray-600 to-gray-700 text-white flex-shrink-0">
        <button onclick="hideCancelledProjectModal()" class="absolute top-6 right-6 w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 backdrop-blur-sm flex items-center justify-center transition-all duration-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        <div class="flex items-start gap-4">
          <div class="w-14 h-14 rounded-xl bg-white/10 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h2 class="text-2xl font-bold">{{ $project->project_title }}</h2>
            <p class="text-gray-100 text-sm mt-1">{{ $project->project_location }}</p>
            <div class="flex items-center gap-2 mt-3">
              <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-500/20 backdrop-blur-sm">Cancelled</span>
              <span class="text-xs text-gray-200">Terminated on <span class="font-medium">{{ \Carbon\Carbon::parse($project->terminated_at)->format('F j, Y') }}</span></span>
            </div>
          </div>
        </div>
      </div>

        <!-- Content -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6">
          <!-- Termination Information Section -->
          <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Termination Details
            </h3>
            <div class="grid grid-cols-2 gap-6">
              <!-- Left Column -->
              <div class="space-y-4">
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Terminated By</p>
                  <p class="text-sm text-gray-900 font-medium">{{ $project->terminated_by ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Reason</p>
                  <p class="text-sm text-gray-900">{{ $project->reason ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Remarks</p>
                  <p class="text-sm text-gray-900">{{ $project->remarks ?? 'N/A' }}</p>
                </div>
              </div>

              <!-- Right Column -->
              <div class="space-y-4">
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Notice Sent On</p>
                  <p class="text-sm text-gray-900 font-medium">{{ \Carbon\Carbon::parse($project->terminated_at)->format('F j, Y') }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Last Active Milestone</p>
                  <p class="text-sm text-gray-900 font-medium">{{ $project->last_active_milestone }}</p>
                </div>
                <!-- Supporting Files -->
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-2">Supporting Files</p>
                  <div class="space-y-2">
                    @forelse($project->termination_files as $file)
                      <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="flex items-center gap-2 text-xs text-blue-600 hover:text-blue-700 hover:underline">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        {{ basename($file->file_path) }}
                      </a>
                    @empty
                      <p class="text-xs text-gray-400">No files uploaded</p>
                    @endforelse
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Project and Contractor Information in 2-Column Layout -->
          <div class="grid grid-cols-2 gap-6">
            <!-- Project Information Card -->
            <div class="bg-gradient-to-br from-orange-50 to-white rounded-xl border border-orange-200/50 overflow-hidden">
              <div class="px-4 py-3 bg-gradient-to-r from-orange-500/10 to-orange-600/5 border-b border-orange-200/30">
                <h3 class="text-sm font-bold text-orange-900 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                  Project Information
                </h3>
              </div>
              <div class="p-4 space-y-3">
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Project Title</p>
                  <p class="text-sm text-gray-900 font-medium">{{ $project->project_title }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Property Type</p>
                  <p class="text-sm text-gray-900">{{ $project->property_type ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Property Address</p>
                  <p class="text-sm text-gray-900">{{ $project->project_location }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Lot Size (sqm)</p>
                  <p class="text-sm text-gray-900">{{ $project->lot_size ? number_format($project->lot_size, 2) : 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Floor Area (sqm)</p>
                  <p class="text-sm text-gray-900">{{ $project->floor_area ? number_format($project->floor_area, 2) : 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Timeline</p>
                  <p class="text-sm text-gray-900">{{ $project->timeline }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Project Cost</p>
                  <p class="text-sm text-gray-900 font-semibold text-orange-600">₱{{ number_format($project->project_cost, 2) }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Deadline</p>
                  <p class="text-sm text-gray-900">{{ $project->deadline }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-2">Uploaded Files</p>
                  <div class="space-y-1">
                    @forelse($project->project_files as $file)
                      <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="flex items-center gap-2 text-xs text-blue-600 hover:text-blue-700 hover:underline">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        {{ ucfirst(str_replace('_', ' ', $file->file_type)) }}
                      </a>
                    @empty
                      <p class="text-xs text-gray-400">No files uploaded</p>
                    @endforelse
                  </div>
                </div>
              </div>
            </div>

            <!-- Contractor Information Card -->
            <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-200/50 overflow-hidden">
              <div class="px-4 py-3 bg-gradient-to-r from-blue-500/10 to-blue-600/5 border-b border-blue-200/30">
                <h3 class="text-sm font-bold text-blue-900 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  Contractor Information
                </h3>
              </div>
              <div class="p-4 space-y-3">
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Contractor Name</p>
                  <p class="text-sm text-gray-900 font-medium">{{ $project->contractor_name ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Company</p>
                  <p class="text-sm text-gray-900">{{ $project->company_name ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Email Address</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_email ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">PCAB No.</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_pcab ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">PCAB Category</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_category ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">PCAB Expiration Date</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_pcab_expiry ? \Carbon\Carbon::parse($project->contractor_pcab_expiry)->format('M d, Y') : 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Business Permit No.</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_permit ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Permit City</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_city ?? 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">Business Permit Expiration</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_permit_expiry ? \Carbon\Carbon::parse($project->contractor_permit_expiry)->format('M d, Y') : 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs font-semibold text-gray-500 mb-1">TIN Registration Number</p>
                  <p class="text-sm text-gray-900">{{ $project->contractor_tin ?? 'N/A' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Project's Milestone and Details (Row) -->
          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Project's Milestone -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <h3 class="font-bold text-gray-900 text-base pb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Project's Milestone
              </h3>
              <div class="space-y-0">
                @php
                  $totalPercentage = array_sum(array_column($project->milestone_items, 'percentage_progress'));
                  $cumulative = $totalPercentage;
                  $reversedItems = array_reverse($project->milestone_items);
                  $totalItems = count($reversedItems);
                @endphp
                @forelse($reversedItems as $index => $item)
                  @php
                    $isLast = ($index == $totalItems - 1);
                  @endphp
                  <div class="flex items-start gap-4">
                    <!-- Timeline left side -->
                    <div class="flex flex-col items-center">
                      <!-- Percentage badge -->
                      <div class="flex-shrink-0 w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-gray-700 font-bold text-xs">
                        {{ min(round($cumulative), 100) }}%
                      </div>
                      <!-- Vertical line and checkmark -->
                      @if(!$isLast)
                        <div class="relative flex-1 w-0.5 bg-gray-200 my-2" style="min-height: 60px;">
                          @php
                            // For terminated projects, check actual status
                            $actualStatus = ($item->item_status === 'not_started' || $item->item_status === 'in_progress') ? 'terminated' : $item->item_status;
                          @endphp
                          @if($item->item_status === 'completed')
                            <div class="absolute left-1/2 -translate-x-1/2 -bottom-3 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                              </svg>
                            </div>
                          @elseif($actualStatus === 'terminated')
                            <div class="absolute left-1/2 -translate-x-1/2 -bottom-3 w-8 h-8 rounded-full bg-red-500 flex items-center justify-center">
                              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                              </svg>
                            </div>
                          @endif
                        </div>
                      @endif
                    </div>

                    <!-- Milestone card -->
                    <div class="flex-1 mb-6">
                      <div class="border-2 border-gray-200 bg-gradient-to-br from-gray-50 to-slate-50 rounded-lg p-4 cursor-pointer hover:shadow-md transition-all" onclick="showTerminatedMilestoneDetails({{ $item->item_id }})">
                        <div class="flex items-start justify-between mb-2">
                          <h4 class="text-base font-bold text-gray-900">{{ $item->milestone_item_title }}</h4>
                          @php
                            // For terminated projects, show 'terminated' for incomplete milestones
                            $actualStatus = ($item->item_status === 'not_started' || $item->item_status === 'in_progress') ? 'terminated' : $item->item_status;
                            $statusColors = [
                              'completed' => 'bg-green-100 text-green-800 border-green-200',
                              'in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
                              'delayed' => 'bg-red-100 text-red-800 border-red-200',
                              'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
                              'terminated' => 'bg-red-100 text-red-800 border-red-200',
                              'not_started' => 'bg-gray-50 text-gray-600 border-gray-200'
                            ];
                            $statusColor = $statusColors[$actualStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                            $displayStatus = $actualStatus === 'terminated' ? 'Terminated' : ucfirst(str_replace('_', ' ', $actualStatus));
                          @endphp
                          <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $statusColor }} border">
                            {{ $displayStatus }}
                          </span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2 uppercase tracking-wide">{{ \Carbon\Carbon::parse($item->date_to_finish)->format('d M g:i A') }}</p>
                        @if($item->milestone_item_description)
                          <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $item->milestone_item_description }}</p>
                        @endif
                        <button class="text-gray-600 hover:text-gray-700 text-sm font-semibold flex items-center gap-1">
                          View Details
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                  @php $cumulative -= $item->percentage_progress; @endphp
                @empty
                  <p class="text-sm text-gray-500 text-center py-8">No milestone items available</p>
                @endforelse
              </div>
            </div>

            <!-- Details -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                  <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Details
                </h3>
              </div>
              <div id="terminatedDetailsContent" class="space-y-4">
                <div class="text-sm text-gray-500 text-center py-8">Select a milestone to view details</div>

                <!-- Hidden divs for each milestone item detail -->
                @foreach($project->milestone_items as $item)
                  <div id="term-detail-{{ $item->item_id }}" class="hidden space-y-4">
                    <!-- Milestone header -->
                    <div class="flex items-center justify-between">
                      <h4 class="text-lg font-bold text-gray-900">{{ $item->milestone_item_title }}</h4>
                      @php
                        // For terminated projects, show 'terminated' for incomplete milestones
                        $actualStatus = ($item->item_status === 'not_started' || $item->item_status === 'in_progress') ? 'terminated' : $item->item_status;
                        $statusColors = [
                          'completed' => 'bg-green-100 text-green-800 border-green-200',
                          'in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
                          'delayed' => 'bg-red-100 text-red-800 border-red-200',
                          'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
                          'terminated' => 'bg-red-100 text-red-800 border-red-200',
                          'not_started' => 'bg-gray-50 text-gray-600 border-gray-200'
                        ];
                        $statusColor = $statusColors[$actualStatus] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                        $displayStatus = $actualStatus === 'terminated' ? 'Terminated' : ucfirst(str_replace('_', ' ', $actualStatus));
                      @endphp
                      <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $statusColor }} border">
                        {{ $displayStatus }}
                      </span>
                    </div>

                    <!-- Date -->
                    <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($item->date_to_finish)->format('d M g:i A') }}</p>

                    <!-- Description -->
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $item->milestone_item_description ?? 'No description' }}</p>

                    <!-- List of Reports Section -->
                    <div class="pt-4">
                      <h5 class="text-sm font-bold text-gray-900 mb-3 uppercase tracking-wide">List of Reports</h5>
                      @if(count($item->progress_reports) > 0)
                        <div class="space-y-2">
                          @foreach($item->progress_reports as $prog)
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                              <p class="text-sm font-semibold text-gray-900">{{ $prog['purpose'] }}</p>
                              <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($prog['submitted_at'])->format('M d, Y g:i A') }}</p>
                              <span class="inline-flex mt-2 px-2 py-1 text-xs font-semibold rounded {{ $prog['progress_status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($prog['progress_status']) }}
                              </span>

                              <!-- Files for this specific progress report -->
                              @if(isset($prog['files']) && count($prog['files']) > 0)
                                <div class="mt-3 space-y-1">
                                  @foreach($prog['files'] as $file)
                                    <a href="{{ asset('storage/' . $file['file_path']) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-gray-100 to-slate-100 border border-gray-300 rounded-lg hover:shadow-md transition-all text-sm">
                                      <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                      </svg>
                                      <span class="text-xs font-medium text-gray-900">{{ $file['original_name'] ?? basename($file['file_path']) }}</span>
                                    </a>
                                  @endforeach
                                </div>
                              @endif
                            </div>
                          @endforeach
                        </div>
                      @else
                        <p class="text-sm text-gray-400">No reports available</p>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <!-- Payment Summary (Row) -->
          <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
            <div class="flex items-center justify-between border-b-2 border-gray-400 pb-3">
              <div>
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Payment Summary
                </h3>
                <p class="text-xs text-gray-500 mt-1">Payment records before project termination</p>
              </div>
            </div>

            <!-- Stats grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Milestones Paid</p>
                  <svg class="w-5 h-5 text-gray-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-xl font-bold text-gray-900">{{ $project->total_milestones_paid }}/{{ $project->total_milestone_items ?? 0 }}</p>
              </div>
              <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Amount Paid</p>
                  <svg class="w-5 h-5 text-gray-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-xl font-bold text-gray-600">₱{{ number_format($project->total_amount_paid, 2) }}</p>
              </div>
              <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Last Payment Date</p>
                  <svg class="w-5 h-5 text-gray-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900">{{ $project->last_payment_date ? \Carbon\Carbon::parse($project->last_payment_date)->format('M d, Y') : 'N/A' }}</p>
              </div>
              <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Over All Payment Status</p>
                  <svg class="w-5 h-5 text-gray-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-sm font-bold text-gray-600">{{ $project->overall_payment_status }}</p>
              </div>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-gray-50 to-slate-50 border-b border-gray-200">
                  <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone Period</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Amount Paid</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Date of Payment</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Uploaded By</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Proof of Payment</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Verification Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  @forelse($project->payments as $payment)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                      <td class="px-4 py-3 text-sm font-semibold">{{ $payment->milestone_item_title ?? 'N/A' }}</td>
                      <td class="px-4 py-3 text-sm">
                        @if($payment->date_to_finish)
                          {{ \Carbon\Carbon::parse($payment->date_to_finish)->format('M d, Y') }}
                        @else
                          —
                        @endif
                      </td>
                      <td class="px-4 py-3 text-sm font-semibold text-gray-600">₱{{ number_format($payment->amount, 2) }}</td>
                      <td class="px-4 py-3 text-sm">
                        {{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('M d, Y') : '—' }}
                      </td>
                      <td class="px-4 py-3 text-sm">{{ $payment->uploaded_by ?? 'Property Owner' }}</td>
                      <td class="px-4 py-3">
                        @if($payment->receipt_photo)
                          <a href="{{ asset('storage/' . $payment->receipt_photo) }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 hover:underline text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View
                          </a>
                        @else
                          <span class="text-xs text-gray-400">No file</span>
                        @endif
                      </td>
                      <td class="px-4 py-3">
                        @php
                          $statusColors = [
                            'paid' => 'bg-green-100 text-green-700',
                            'approved' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'rejected' => 'bg-red-100 text-red-700'
                          ];
                          $statusColor = $statusColors[$payment->payment_status] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                          {{ ucfirst($payment->payment_status) }}
                        </span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">
                        No payment records available
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end flex-shrink-0">
          <button onclick="hideCancelledProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
            Close
          </button>
        </div>
      @else
        <!-- Empty state when no project data -->
        <div class="p-8 text-center text-gray-500">
          <p>Loading project details...</p>
        </div>
      @endisset
    </div>
  </div>
</div>
