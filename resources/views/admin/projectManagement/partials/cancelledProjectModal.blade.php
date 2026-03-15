<!-- Cancelled/Terminated Project Modal -->
<div id="cancelledProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-5xl rounded-xl shadow-2xl relative flex flex-col" style="max-height:90vh;">
      @isset($project)
      <!-- Header with Owner Info -->
      <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-4 py-3 rounded-t-xl flex-shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center overflow-hidden ring-2 ring-white/30 flex-shrink-0">
              @if($project->owner_profile_pic)
                <img src="{{ asset('storage/' . $project->owner_profile_pic) }}" alt="Owner" class="w-full h-full object-cover">
              @else
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              @endif
            </div>
            <div class="text-white">
              <h3 class="text-sm font-bold leading-tight">{{ $project->owner_name ?? $project->project_title }}</h3>
              <p class="text-[10px] opacity-80 flex items-center gap-1.5">
                <span class="inline-block w-1.5 h-1.5 bg-gray-300 rounded-full"></span>
                Cancelled Project
                <span class="ml-1">{{ \Carbon\Carbon::parse($project->terminated_at)->format('M j, Y') }}</span>
              </p>
            </div>
          </div>
          <button type="button" onclick="hideCancelledProjectModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <style>.cancelled-scroll::-webkit-scrollbar{display:none}</style>
      <div class="cancelled-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">

        <!-- Termination Banner -->
        <div class="bg-white border border-gray-200 rounded-lg p-2.5 flex items-center gap-3">
          <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-gray-800">This Project Has Been Terminated</p>
            <p class="text-[10px] text-gray-500 italic">All active milestones were cancelled upon termination.</p>
          </div>
          <div class="flex-shrink-0 flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 border border-gray-200 text-[10px] font-semibold text-gray-600 rounded-md">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ \Carbon\Carbon::parse($project->terminated_at)->format('M d, Y') }}
          </div>
        </div>

        <!-- Termination Details -->
        <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
          <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
            <div class="w-5 h-5 rounded-md bg-gray-600 flex items-center justify-center flex-shrink-0">
              <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            Termination Details
          </h3>
          <div class="grid grid-cols-2 gap-x-4 text-[11px]">
            <!-- Left column -->
            <div class="space-y-0.5">
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                <span class="text-gray-500 flex-shrink-0 mr-2">Terminated By</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->terminated_by ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                <span class="text-gray-500 flex-shrink-0 mr-2">Reason</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->reason ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                <span class="text-gray-500 flex-shrink-0 mr-2">Remarks</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->remarks ?? 'N/A' }}</span>
              </div>
            </div>
            <!-- Right column -->
            <div class="space-y-0.5">
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                <span class="text-gray-500 flex-shrink-0 mr-2">Notice Sent On</span>
                <span class="font-semibold text-gray-900 text-right">{{ \Carbon\Carbon::parse($project->terminated_at)->format('M j, Y') }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                <span class="text-gray-500 flex-shrink-0 mr-2">Last Active Milestone</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->last_active_milestone }}</span>
              </div>
              <div class="py-0.5 px-1">
                <span class="text-gray-500 block mb-1">Supporting Files</span>
                <div class="space-y-1">
                  @forelse($project->termination_files as $file)
                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="flex items-center gap-1.5 text-[10px] text-blue-600 hover:text-blue-700 hover:underline">
                      <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                      </svg>
                      {{ basename($file->file_path) }}
                    </a>
                  @empty
                    <p class="text-[10px] text-gray-400 italic">No files uploaded</p>
                  @endforelse
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Project and Contractor Information in 2-Column Layout -->
        <div class="grid grid-cols-2 gap-3">
          <!-- Project Information -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-md bg-orange-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              Project Information
            </h3>
            <div class="space-y-0.5 text-[11px]">
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Project Title</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->project_title }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Property Type</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->property_type ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Property Address</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->project_location }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Lot Size (sqm)</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->lot_size ? number_format($project->lot_size, 2) : 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Floor Area (sqm)</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->floor_area ? number_format($project->floor_area, 2) : 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Timeline</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->timeline }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Project Cost</span>
                <span class="font-semibold text-orange-600 text-right">₱{{ number_format($project->project_cost, 2) }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Deadline</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->deadline }}</span>
              </div>
            </div>
            <div class="pt-1">
              <span class="text-[10px] text-gray-400 block mb-1">Uploaded Files</span>
              <div class="flex flex-wrap gap-1.5">
                @forelse($project->project_files as $file)
                  <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="flex items-center gap-1 text-[10px] text-blue-600 hover:underline bg-gray-50 border border-gray-200 rounded px-2 py-0.5">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    {{ ucfirst(str_replace('_', ' ', $file->file_type)) }}
                  </a>
                @empty
                  <p class="text-[10px] text-gray-400 italic">No files uploaded</p>
                @endforelse
              </div>
            </div>
          </div>

          <!-- Contractor Information -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-md bg-blue-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
              Contractor Information
            </h3>
            <div class="space-y-0.5 text-[11px]">
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Contractor Name</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_name ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Company</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->company_name ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Email Address</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_email ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">PCAB No.</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_pcab ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">PCAB Category</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_category ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">PCAB Expiration</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_pcab_expiry ? \Carbon\Carbon::parse($project->contractor_pcab_expiry)->format('M d, Y') : 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Business Permit No.</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_permit ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Permit City</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_city ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Permit Expiration</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_permit_expiry ? \Carbon\Carbon::parse($project->contractor_permit_expiry)->format('M d, Y') : 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">TIN Registration</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_tin ?? 'N/A' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Milestone and Details (2-Column) -->
        <div class="grid lg:grid-cols-2 gap-3">
          <!-- Milestone -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-md bg-indigo-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
              </div>
              Project's Milestone
            </h3>
            <div class="space-y-0">
              @php
                $itemsWithCumulative = [];
                $cumulative = 0;
                foreach($project->milestone_items as $item) {
                  $cumulative += ($item->percentage_progress ?? 0);
                  $itemsWithCumulative[] = ['item' => $item, 'cumulative' => $cumulative];
                }
                $reversedItems = array_reverse($itemsWithCumulative);
                $totalItems = count($reversedItems);
              @endphp
              @forelse($reversedItems as $index => $itemData)
                @php
                  $item = $itemData['item'];
                  $isLast = ($index == $totalItems - 1);
                  $cumulative = round($itemData['cumulative']);
                  $actualStatus = ($item->item_status === 'not_started' || $item->item_status === 'in_progress') ? 'terminated' : $item->item_status;
                  $circleColors = [
                    'completed'  => 'bg-green-100 text-green-700 ring-2 ring-green-200',
                    'terminated' => 'bg-gray-100 text-gray-500 ring-2 ring-gray-200',
                  ];
                  $circleCls = $circleColors[$actualStatus] ?? 'bg-gray-100 text-gray-500 ring-2 ring-gray-200';
                @endphp
                <div class="flex items-start gap-3">
                  <!-- Timeline indicator -->
                  <div class="flex flex-col items-center flex-shrink-0">
                    <div class="w-10 h-10 rounded-full {{ $circleCls }} flex items-center justify-center font-bold text-[10px]">
                      {{ $cumulative }}%
                    </div>
                    @if(!$isLast)
                      <div class="relative w-0.5 bg-gray-200 my-1" style="min-height:40px;">
                        @if($item->item_status === 'completed')
                          <div class="absolute left-1/2 -translate-x-1/2 -bottom-2.5 w-5 h-5 rounded-full bg-green-500 flex items-center justify-center shadow-sm">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                          </div>
                        @elseif($actualStatus === 'terminated')
                          <div class="absolute left-1/2 -translate-x-1/2 -bottom-2.5 w-5 h-5 rounded-full bg-gray-400 flex items-center justify-center shadow-sm">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                          </div>
                        @endif
                      </div>
                    @endif
                  </div>
                  <!-- Milestone card -->
                  <div class="flex-1 mb-4">
                    @php
                      $cardBorder = $actualStatus === 'completed' ? 'border-green-200 hover:border-green-300' : 'border-gray-200 hover:border-gray-300';
                      $cardBg     = $actualStatus === 'completed' ? 'bg-green-50/50 hover:bg-green-50' : 'bg-gray-50 hover:bg-gray-100';
                    @endphp
                    <div class="{{ $cardBg }} border {{ $cardBorder }} rounded-lg p-2 cursor-pointer transition-all" data-item-id="{{ $item->item_id }}" onclick="showTerminatedMilestoneDetails(Number(this.dataset.itemId))">
                      <div class="flex items-start justify-between gap-2 mb-1">
                        <h4 class="text-xs font-bold text-gray-800 leading-tight">{{ $item->milestone_item_title }}</h4>
                        @php
                          $statusColors = [
                            'completed'   => 'bg-green-100 text-green-700',
                            'in_progress' => 'bg-gray-200 text-gray-700',
                            'delayed'     => 'bg-gray-200 text-gray-700',
                            'cancelled'   => 'bg-gray-200 text-gray-600',
                            'terminated'  => 'bg-gray-200 text-gray-600',
                            'not_started' => 'bg-gray-100 text-gray-500',
                          ];
                          $statusColor = $statusColors[$actualStatus] ?? 'bg-gray-100 text-gray-500';
                          $displayStatus = $actualStatus === 'terminated' ? 'Terminated' : ucfirst(str_replace('_', ' ', $actualStatus));
                        @endphp
                        <span class="inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded-full {{ $statusColor }} flex-shrink-0">
                          {{ $displayStatus }}
                        </span>
                      </div>
                      <p class="text-[10px] text-gray-400 mb-1 flex items-center gap-1">
                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ \Carbon\Carbon::parse($item->date_to_finish)->format('d M g:i A') }}
                      </p>
                      @if($item->milestone_item_description)
                        <p class="text-[10px] text-gray-500 line-clamp-1">{{ $item->milestone_item_description }}</p>
                      @endif
                      <span class="text-gray-400 hover:text-gray-600 text-[10px] font-medium flex items-center gap-0.5 mt-1">
                        View Details
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                      </span>
                    </div>
                  </div>
                </div>
              @empty
                <div class="flex items-center justify-center py-6 text-center">
                  <div>
                    <svg class="w-8 h-8 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-[10px] text-gray-400">No milestone items available</p>
                  </div>
                </div>
              @endforelse
            </div>
          </div>

          <!-- Details -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1 flex flex-col">
            <div class="flex items-center justify-between pb-2 border-b border-gray-200 flex-shrink-0">
              <h3 class="font-bold text-gray-900 text-xs flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-amber-500 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                Details
              </h3>
            </div>
            <div id="terminatedDetailsContent" class="space-y-2 flex-1 overflow-y-auto min-h-0">
              <div id="cancelledNoMilestoneMsg" class="flex items-center justify-center py-8 text-center">
                <div>
                  <svg class="w-8 h-8 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                  <p class="text-[10px] text-gray-400">Click a milestone to view details</p>
                </div>
              </div>

              @foreach($project->milestone_items as $item)
                <div id="term-detail-{{ $item->item_id }}" class="hidden space-y-2">
                  <!-- Detail header -->
                  <div class="flex items-start justify-between gap-2 pb-2 border-b border-gray-100">
                    <div>
                      <h4 class="text-xs font-bold text-gray-900 leading-tight">{{ $item->milestone_item_title }}</h4>
                      <p class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($item->date_to_finish)->format('d M Y, g:i A') }}</p>
                    </div>
                    @php
                      $actualStatus = ($item->item_status === 'not_started' || $item->item_status === 'in_progress') ? 'terminated' : $item->item_status;
                      $statusColors = [
                        'completed'   => 'bg-green-100 text-green-700',
                        'in_progress' => 'bg-gray-200 text-gray-700',
                        'delayed'     => 'bg-gray-200 text-gray-700',
                        'cancelled'   => 'bg-gray-200 text-gray-600',
                        'terminated'  => 'bg-gray-200 text-gray-600',
                        'not_started' => 'bg-gray-100 text-gray-500',
                      ];
                      $statusColor = $statusColors[$actualStatus] ?? 'bg-gray-100 text-gray-500';
                      $displayStatus = $actualStatus === 'terminated' ? 'Terminated' : ucfirst(str_replace('_', ' ', $actualStatus));
                    @endphp
                    <span class="inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded-full {{ $statusColor }} flex-shrink-0">
                      {{ $displayStatus }}
                    </span>
                  </div>
                  @if($item->milestone_item_description)
                    <p class="text-[11px] text-gray-600 leading-relaxed">{{ $item->milestone_item_description }}</p>
                  @endif

                  <div class="pt-1">
                    <h5 class="text-[10px] font-bold text-gray-700 mb-1.5 uppercase tracking-wide">List of Reports</h5>
                    @if(count($item->progress_reports) > 0)
                      <div class="space-y-1.5">
                        @foreach($item->progress_reports as $prog)
                          <div class="p-2 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="text-[11px] font-semibold text-gray-900">{{ $prog['purpose'] }}</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($prog['submitted_at'])->format('M d, Y g:i A') }}</p>
                            <span class="inline-flex mt-1 px-1.5 py-0.5 text-[9px] font-semibold rounded {{ $prog['progress_status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                              {{ ucfirst($prog['progress_status']) }}
                            </span>
                            @if(isset($prog['files']) && count($prog['files']) > 0)
                              <div class="mt-1.5 space-y-1">
                                @foreach($prog['files'] as $file)
                                  <a href="{{ asset('storage/' . $file['file_path']) }}" target="_blank" class="flex items-center gap-1.5 px-2 py-1 bg-white border border-gray-200 rounded text-[10px] text-gray-700 hover:shadow-sm transition-all">
                                    <svg class="w-3 h-3 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    {{ $file['original_name'] ?? basename($file['file_path']) }}
                                  </a>
                                @endforeach
                              </div>
                            @endif
                          </div>
                        @endforeach
                      </div>
                    @else
                      <p class="text-[10px] text-gray-400 italic">No reports available</p>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <!-- Payment Summary -->
        <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-2">
          <div class="flex items-center justify-between border-b border-gray-200 pb-2">
            <div>
              <h3 class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-teal-500 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                Payment Summary
              </h3>
              <p class="text-[10px] text-gray-400 mt-0.5 ml-6.5">Payment records before project termination</p>
            </div>
          </div>

          <!-- Stats grid -->
          <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2">
            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-200">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] text-gray-500 font-medium">Milestones Paid</p>
                <div class="w-5 h-5 rounded bg-indigo-100 flex items-center justify-center">
                  <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <p class="text-sm font-bold text-gray-900">{{ $project->total_milestones_paid }}/{{ $project->total_milestone_items ?? 0 }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-200">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] text-gray-500 font-medium">Total Amount Paid</p>
                <div class="w-5 h-5 rounded bg-teal-100 flex items-center justify-center">
                  <svg class="w-3 h-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <p class="text-sm font-bold text-teal-600">₱{{ number_format($project->total_amount_paid, 2) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-200">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] text-gray-500 font-medium">Last Payment Date</p>
                <div class="w-5 h-5 rounded bg-orange-100 flex items-center justify-center">
                  <svg class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
              </div>
              <p class="text-xs font-semibold text-gray-900">{{ $project->last_payment_date ? \Carbon\Carbon::parse($project->last_payment_date)->format('M d, Y') : 'N/A' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-200">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] text-gray-500 font-medium">Overall Status</p>
                <div class="w-5 h-5 rounded bg-gray-200 flex items-center justify-center">
                  <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <p class="text-xs font-bold text-gray-600">{{ $project->overall_payment_status }}</p>
            </div>
          </div>

          <div class="rounded-lg border border-gray-200 overflow-hidden">
            <table class="w-full text-[10px]">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Milestone</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Period</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Amount Paid</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Uploaded By</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Proof</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($project->payments as $payment)
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-2.5 py-1.5 font-semibold">{{ $payment->milestone_item_title ?? 'N/A' }}</td>
                    <td class="px-2.5 py-1.5">
                      {{ $payment->date_to_finish ? \Carbon\Carbon::parse($payment->date_to_finish)->format('M d, Y') : 'N/A' }}
                    </td>
                    <td class="px-2.5 py-1.5 font-semibold text-gray-600">₱{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-2.5 py-1.5">{{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('M d, Y') : 'N/A' }}</td>
                    <td class="px-2.5 py-1.5">{{ $payment->uploaded_by ?? 'Property Owner' }}</td>
                    <td class="px-2.5 py-1.5">
                      @if($payment->receipt_photo)
                        <a href="{{ asset('storage/' . $payment->receipt_photo) }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:underline">
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                          View
                        </a>
                      @else
                        <span class="text-gray-400">No file</span>
                      @endif
                    </td>
                    <td class="px-2.5 py-1.5">
                      @php
                        $statusColors = [
                          'paid'     => 'bg-green-100 text-green-700',
                          'approved' => 'bg-green-100 text-green-700',
                          'pending'  => 'bg-yellow-100 text-yellow-700',
                          'rejected' => 'bg-red-100 text-red-700',
                        ];
                        $statusColor = $statusColors[$payment->payment_status] ?? 'bg-gray-100 text-gray-700';
                      @endphp
                      <span class="inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded-full {{ $statusColor }}">
                        {{ ucfirst($payment->payment_status) }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="px-2.5 py-6 text-center text-[10px] text-gray-400">No payment records available</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>{{-- end scrollable body --}}

      <!-- Footer -->
      <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 rounded-b-xl flex justify-between items-center gap-3 flex-shrink-0">
        <button type="button" data-project-id="{{ $project->project_id }}" onclick="showProjectSummaryModal(Number(this.dataset.projectId))" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-colors flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          View Project Summary
        </button>
        <button type="button" onclick="hideCancelledProjectModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 transition-colors flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Close
        </button>
      </div>
      @else
        <div class="p-6 text-center text-gray-500 text-sm">
          <p>Loading project details...</p>
        </div>
      @endisset
    </div>
  </div>
</div>
