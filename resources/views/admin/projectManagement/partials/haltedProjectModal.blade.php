  <!-- Halted Project Modal -->
<div id="haltedProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center overflow-y-auto py-8 px-4">
    <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl relative my-4 transform transition-all duration-300 scale-100">
      @isset($project)
      <!-- Header with Owner Info -->
      <div class="bg-gradient-to-r from-rose-500 via-red-500 to-rose-600 px-6 py-5 rounded-t-2xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
        <div class="flex items-center justify-between relative z-10">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-white flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-white/30 transition-transform duration-300 hover:scale-110">
              @if($project->owner_profile_pic)
                <img src="{{ asset('storage/' . $project->owner_profile_pic) }}" alt="Owner" class="w-full h-full object-cover">
              @else
                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
              @endif
            </div>
            <div class="text-white">
              <h3 class="text-lg font-bold tracking-wide">{{ $project->owner_name }}</h3>
              <p class="text-xs opacity-90 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 bg-rose-100 text-rose-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                  Halted
                </span>
                <span class="text-white/90">{{ $project->halted_at ? \Carbon\Carbon::parse($project->halted_at)->format('M j, Y') : 'N/A' }}</span>
              </p>
            </div>
          </div>
          <button onclick="hideHaltedProjectModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

        <div class="p-6 space-y-6 max-h-[calc(100vh-12rem)] overflow-y-auto">
          <!-- Halted Message -->
          <div class="bg-gradient-to-br from-rose-50 to-red-50 border-2 border-rose-200 rounded-xl p-6 text-center">
            <div class="flex justify-center mb-4">
              <div class="w-20 h-20 rounded-full bg-rose-100 flex items-center justify-center">
                <svg class="w-12 h-12 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">This project is currently HALTED</h3>
            <p class="text-sm text-gray-600 italic mb-4">Work temporarily paused. Will resume once resolved.</p>
            <button onclick="showHaltDetailsModal({{ $project->project_id }})" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white font-semibold text-sm rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              View Halt Details
            </button>
          </div>

          <!-- Project Details and Contractor Details (2-Column) -->
          <div class="grid lg:grid-cols-2 gap-6">
            <!-- Project Details -->
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 space-y-3 hover:shadow-lg transition-all duration-300">
              <h3 class="font-bold text-gray-900 text-base border-b-2 border-rose-400 pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Project Details
              </h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Project Title</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->project_title ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Property Address</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->project_location ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Property Type:</span>
                  <span class="font-semibold text-gray-900 text-right">{{ ucfirst(str_replace('_', ' ', $project->property_type ?? '—')) }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Lot Size (sqm)</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->lot_size ? number_format($project->lot_size, 2) : '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Floor Area (sqm)</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->floor_area ? number_format($project->floor_area, 2) : '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Target Timeline</span>
                  <span class="font-semibold text-gray-900 text-right">
                    @if($project->timeline_start && $project->timeline_end)
                      {{ \Carbon\Carbon::parse($project->timeline_start)->format('M j, Y') }} - {{ \Carbon\Carbon::parse($project->timeline_end)->format('M j, Y') }}
                    @else
                      —
                    @endif
                  </span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Budget</span>
                  <span class="font-semibold text-rose-600 text-right">
                    @if($project->budget_range_min && $project->budget_range_max)
                      ₱{{ number_format($project->budget_range_min, 2) }} - ₱{{ number_format($project->budget_range_max, 2) }}
                    @else
                      —
                    @endif
                  </span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-rose-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Bidding Deadline</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->bidding_due ? \Carbon\Carbon::parse($project->bidding_due)->format('M j, Y') : '—' }}</span>
                </div>
              </div>
              <div>
                <span class="text-xs text-gray-500 block mb-2">Supporting Files</span>
                <div class="flex flex-wrap gap-2">
                  @forelse($project->project_files as $file)
                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs hover:bg-blue-100 transition-colors duration-200">
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

            <!-- Contractor Details -->
            <div class="bg-gradient-to-br from-white to-blue-50 border border-gray-200 rounded-xl p-5 space-y-3 hover:shadow-lg transition-all duration-300">
              <h3 class="font-bold text-gray-900 text-base border-b-2 border-blue-400 pb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Contractor Details
              </h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Contractor Name:</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_name ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Company Name:</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->company_name ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Email Address:</span>
                  <span class="font-semibold text-blue-600 text-right">{{ $project->contractor_email ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">PCAB No.:</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_pcab ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">PCAB Category:</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_category ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">PCAB Expiration Date</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_pcab_expiry ? \Carbon\Carbon::parse($project->contractor_pcab_expiry)->format('M j, Y') : '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Business Permit No.:</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_permit ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Permit City:</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_city ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">Business Permit Expiration</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_permit_expiry ? \Carbon\Carbon::parse($project->contractor_permit_expiry)->format('M j, Y') : '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
                  <span class="text-gray-600 font-medium">TIN Registration number</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_tin ?? '—' }}</span>
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
                  // Calculate cumulative percentages first
                  $itemsWithCumulative = [];
                  $cumulative = 0;
                  foreach($project->milestone_items as $item) {
                    $cumulative += $item->percentage_progress;
                    $itemsWithCumulative[] = [
                      'item' => $item,
                      'cumulative' => $cumulative
                    ];
                  }
                  // Reverse to show 100% at top
                  $reversedItems = array_reverse($itemsWithCumulative);
                  $totalItems = count($reversedItems);
                @endphp
                @foreach($reversedItems as $index => $itemData)
                  @php
                    $item = $itemData['item'];
                    $statusColors = [
                      'completed' => 'bg-green-100 text-green-700',
                      'in_progress' => 'bg-amber-100 text-amber-700',
                      'delayed' => 'bg-red-100 text-red-700',
                      'cancelled' => 'bg-gray-100 text-gray-700',
                      'not_started' => 'bg-gray-50 text-gray-600'
                    ];
                    $statusBadge = $statusColors[$item->item_status] ?? 'bg-gray-100 text-gray-700';
                    $isCompleted = $item->item_status === 'completed';
                    $isHalted = $item->item_status === 'halt';
                  @endphp

                  <div class="flex items-start gap-3">
                    <!-- Left Timeline Section -->
                    <div class="flex flex-col items-center">
                      <!-- Percentage Circle -->
                      <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 font-bold text-sm text-gray-700 mb-2">
                        {{ round($itemData['cumulative']) }}%
                      </div>

                      <!-- Status Icon -->
                      @if($isCompleted)
                        <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0 mb-2">
                          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                          </svg>
                        </div>
                      @elseif($isHalted)
                        <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0 mb-2">
                          <div class="w-4 h-4 rounded-full bg-white"></div>
                        </div>
                      @else
                        <div class="w-10 h-10 rounded-full bg-amber-400 flex items-center justify-center flex-shrink-0 mb-2">
                          <div class="w-4 h-4 rounded-full bg-white"></div>
                        </div>
                      @endif

                      <!-- Vertical Line (if not last item) -->
                      @if($index < $totalItems - 1)
                        <div class="w-0.5 h-8 bg-gray-300"></div>
                      @endif
                    </div>

                    <!-- Milestone Card -->
                    <div onclick="showHaltedMilestoneDetail({{ $item->item_id }})" class="flex-1 bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-lg p-4 mb-4 relative cursor-pointer hover:shadow-lg transition-all duration-200 hover:border-amber-300">
                      <!-- Status Badge (top right) -->
                      <span class="absolute top-3 right-3 px-3 py-1 text-xs font-semibold rounded-full {{ $statusBadge }} uppercase">
                        {{ str_replace('_', ' ', strtoupper($item->item_status)) }}
                      </span>

                      <!-- Title -->
                      <h4 class="text-base font-bold text-gray-900 mb-2 pr-24">{{ $item->milestone_item_title }}</h4>

                      <!-- Date -->
                      <p class="text-sm text-gray-600 mb-2">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('d M h:i A') : 'No due date' }}</p>

                      <!-- Description (if exists) -->
                      @if(!empty($item->milestone_item_description))
                        <p class="text-sm text-gray-700 mb-3">{{ $item->milestone_item_description }}</p>
                      @endif

                      <!-- View Details Button -->
                      <div class="text-orange-600 hover:text-orange-700 font-semibold text-sm flex items-center gap-1 transition-colors">
                        View Details
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>

            <!-- Details -->
            <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
              <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                  <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Details
                </h3>
              </div>
              <div id="haltedDetailsContent">
                <div class="text-sm text-gray-500 text-center py-8">Select a milestone to view details</div>

                @foreach($project->milestone_items as $item)
                  <div id="halted-milestone-detail-{{ $item->item_id }}" class="space-y-4 hidden">
                    <!-- Milestone Title as Heading -->
                    <h4 class="text-lg font-bold text-gray-900">{{ $item->milestone_item_title }}</h4>

                    <!-- Status Badge -->
                    @php
                      $statusColors = [
                        'completed' => 'bg-green-100 text-green-700',
                        'in_progress' => 'bg-amber-100 text-amber-700',
                        'delayed' => 'bg-red-100 text-red-700',
                        'cancelled' => 'bg-gray-100 text-gray-700',
                        'not_started' => 'bg-gray-50 text-gray-600'
                      ];
                      $statusBadge = $statusColors[$item->item_status] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full {{ $statusBadge }} uppercase">
                      {{ str_replace('_', ' ', strtoupper($item->item_status)) }}
                    </span>

                    <!-- Due Date -->
                    <p class="text-sm text-gray-600">Due: {{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('d M h:i A') : 'No due date' }}</p>

                    <!-- Description (if exists) -->
                    @if(!empty($item->milestone_item_description))
                      <p class="text-sm text-gray-700">{{ $item->milestone_item_description }}</p>
                    @endif

                    @if(!empty($item->progress_reports))
                      <!-- LIST OF REPORTS Heading -->
                      <h5 class="text-base font-bold text-gray-900 mt-6 mb-3">LIST OF REPORTS</h5>

                      <div class="space-y-3">
                        @foreach($item->progress_reports as $progress)
                          <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-lg p-4">
                            <!-- Report Title/Purpose -->
                            <h6 class="text-sm font-bold text-gray-900 mb-2">{{ $progress['purpose'] }}</h6>

                            <!-- Date and Status -->
                            <div class="flex items-center gap-3 mb-3">
                              <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($progress['submitted_at'])->format('M d, Y h:i A') }}</p>
                              @php
                                $progressStatusColors = [
                                  'submitted' => 'bg-blue-100 text-blue-700',
                                  'approved' => 'bg-green-100 text-green-700',
                                  'rejected' => 'bg-red-100 text-red-700',
                                  'deleted' => 'bg-gray-100 text-gray-700'
                                ];
                                $progressStatusColor = $progressStatusColors[$progress['progress_status']] ?? 'bg-gray-100 text-gray-700';
                              @endphp
                              <span class="px-2 py-1 rounded-md text-xs font-semibold {{ $progressStatusColor }}">
                                {{ ucfirst($progress['progress_status']) }}
                              </span>
                            </div>

                            <!-- Files -->
                            @if(!empty($progress['files']))
                              <div class="space-y-2">
                                @foreach($progress['files'] as $file)
                                  <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <a href="{{ asset('storage/' . $file['file_path']) }}" target="_blank" class="text-sm text-gray-700 hover:text-orange-600 font-medium flex-1">{{ basename($file['file_path']) }}</a>
                                  </div>
                                @endforeach
                              </div>
                            @endif
                          </div>
                        @endforeach
                      </div>
                    @else
                      <p class="text-sm text-gray-500 italic mt-4">No progress reports available</p>
                    @endif
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <!-- Payment Summary (Row) -->
          <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 hover:shadow-lg transition-all duration-300">
            <div class="flex items-center justify-between border-b-2 border-rose-400 pb-3">
              <div>
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                  <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Payment Summary
                </h3>
                <p class="text-xs text-gray-500 mt-1">This section contains uploaded receipts and payment confirmations related to completed milestones</p>
              </div>
            </div>
            <!-- Stats grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-gradient-to-br from-rose-50 to-red-50 rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Milestones Paid</p>
                  <svg class="w-5 h-5 text-rose-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-xl font-bold text-gray-900">{{ $project->total_milestones_paid ?? 0 }}/{{ $project->total_milestone_items ?? 0 }}</p>
              </div>
              <div class="bg-gradient-to-br from-rose-50 to-red-50 rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Total Amount Paid</p>
                  <svg class="w-5 h-5 text-rose-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-xl font-bold text-rose-600">₱{{ number_format($project->total_amount_paid ?? 0, 2) }}</p>
              </div>
              <div class="bg-gradient-to-br from-rose-50 to-red-50 rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Last Payment Date</p>
                  <svg class="w-5 h-5 text-rose-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900">{{ $project->last_payment_date ? \Carbon\Carbon::parse($project->last_payment_date)->format('M j, Y') : '—' }}</p>
              </div>
              <div class="bg-gradient-to-br from-rose-50 to-red-50 rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200 group">
                <div class="flex items-center justify-between mb-2">
                  <p class="text-xs text-gray-600 font-medium">Over All Payment Status</p>
                  <svg class="w-5 h-5 text-rose-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-sm font-bold text-rose-600">{{ $project->overall_payment_status ?? '—' }}</p>
              </div>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-rose-50 to-red-50 border-b border-rose-200">
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
                    <tr class="hover:bg-gray-50">
                      <td class="px-4 py-3 text-sm">{{ $payment->milestone_item_title ?? '—' }}</td>
                      <td class="px-4 py-3 text-sm">
                        @if($payment->start_date && $payment->end_date)
                          {{ \Carbon\Carbon::parse($payment->start_date)->format('M j') }} - {{ \Carbon\Carbon::parse($payment->end_date)->format('M j, Y') }}
                        @else
                          —
                        @endif
                      </td>
                      <td class="px-4 py-3 text-sm font-semibold">₱{{ number_format($payment->amount ?? 0, 2) }}</td>
                      <td class="px-4 py-3 text-sm">{{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('M j, Y') : '—' }}</td>
                      <td class="px-4 py-3 text-sm">{{ ($payment->uploader_fname ?? '') . ' ' . ($payment->uploader_lname ?? '') }}</td>
                      <td class="px-4 py-3 text-sm">
                        @if($payment->receipt_photo)
                          <a href="{{ asset('storage/' . $payment->receipt_photo) }}" target="_blank" class="text-blue-600 hover:underline">View</a>
                        @else
                          —
                        @endif
                      </td>
                      <td class="px-4 py-3">
                        @php
                          $statusColors = [
                            'approved' => 'bg-green-100 text-green-800',
                            'paid' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'rejected' => 'bg-red-100 text-red-800'
                          ];
                          $statusColor = $statusColors[$payment->payment_status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                          {{ ucfirst($payment->payment_status ?? 'Unknown') }}
                        </span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="px-4 py-8 text-center text-gray-500">No payment records available</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl flex justify-end gap-3">
          <button onclick="hideHaltedProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
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

