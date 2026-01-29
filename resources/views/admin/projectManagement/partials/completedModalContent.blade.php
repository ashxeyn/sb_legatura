<!-- Header with Owner Info -->
<div class="bg-gradient-to-r from-green-500 via-emerald-500 to-green-600 px-6 py-5 rounded-t-2xl relative overflow-hidden">
  <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
  <div class="flex items-center justify-between relative z-10">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-full bg-white flex items-center justify-center overflow-hidden shadow-xl ring-4 ring-white/30 transition-transform duration-300 hover:scale-110">
        @if($project->owner_profile_pic)
          <img src="{{ asset('storage/' . $project->owner_profile_pic) }}" alt="Owner" class="w-full h-full object-cover">
        @else
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        @endif
      </div>
      <div class="text-white">
        <h3 class="text-lg font-bold tracking-wide">{{ $project->owner_name }}</h3>
        <p class="text-xs opacity-90 flex items-center gap-2">
          <span class="inline-flex items-center gap-1 bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">
            Completed
          </span>
          @if($project->timeline_end)
            <span class="text-white/90">{{ \Carbon\Carbon::parse($project->timeline_end)->format('F d, Y') }}</span>
          @endif
        </p>
      </div>
    </div>
    <button onclick="hideCompletedProjectModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
</div>

<div class="p-6 space-y-6 max-h-[calc(100vh-12rem)] overflow-y-auto">
  <!-- Success Message -->
  <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-6 text-center">
    <div class="flex justify-center mb-4">
      <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center">
        <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
    </div>
    <h3 class="text-xl font-bold text-gray-900 mb-2">This project has been successfully COMPLETED</h3>
    <p class="text-sm text-gray-600 italic mb-4">All milestones verified and marked as completed.</p>

    <button onclick="showCompletionDetailsModal({{ $project->project_id }})" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
      </svg>
      View Details
    </button>
  </div>

  <!-- Project Details and Contractor Details (2-Column) -->
  <div id="completedDetailsSection" class="grid lg:grid-cols-2 gap-6">
    <!-- Project Details -->
    <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 space-y-3 hover:shadow-lg transition-all duration-300">
      <h3 class="font-bold text-gray-900 text-base border-b-2 border-green-400 pb-2 flex items-center gap-2">
        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Project Details
      </h3>
      <div class="space-y-2 text-sm">
        <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
          <span class="text-gray-600 font-medium">Project Title</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->project_title ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
          <span class="text-gray-600 font-medium">Property Address</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->project_location ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
          <span class="text-gray-600 font-medium">Property Type:</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->property_type ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
          <span class="text-gray-600 font-medium">Lot Size (sqm)</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->lot_size ? number_format($project->lot_size, 2) : '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
          <span class="text-gray-600 font-medium">Target Timeline</span>
          <span class="font-semibold text-gray-900 text-right">
            @if($project->timeline_start && $project->timeline_end)
              {{ \Carbon\Carbon::parse($project->timeline_start)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($project->timeline_end)->format('M d, Y') }}
            @else
              —
            @endif
          </span>
        </div>
        <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
          <span class="text-gray-600 font-medium">Budget</span>
          <span class="font-semibold text-green-600 text-right">
            @if($project->budget_range_min && $project->budget_range_max)
              ₱{{ number_format($project->budget_range_min) }} - ₱{{ number_format($project->budget_range_max) }}
            @else
              —
            @endif
          </span>
        </div>
        <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-green-50 transition-colors duration-200">
          <span class="text-gray-600 font-medium">Bidding Deadline</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->bidding_due ? \Carbon\Carbon::parse($project->bidding_due)->format('M d, Y') : '—' }}</span>
        </div>
      </div>
      <div>
        <span class="text-xs text-gray-500 block mb-2">Supporting Files</span>
        <div class="flex flex-wrap gap-2">
          <span class="text-xs text-gray-400">No files available</span>
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
          <span class="text-gray-600 font-medium">Company Name :</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_name ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-2 px-3 rounded hover:bg-blue-50 transition-colors duration-200">
          <span class="text-gray-600 font-medium">Email Address :</span>
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
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_pcab_expiry ? \Carbon\Carbon::parse($project->contractor_pcab_expiry)->format('M d, Y') : '—' }}</span>
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
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_permit_expiry ? \Carbon\Carbon::parse($project->contractor_permit_expiry)->format('M d, Y') : '—' }}</span>
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
          $totalPercentage = array_sum(array_column($project->milestone_items, 'percentage_progress'));
          $cumulative = $totalPercentage;
          $reversedItems = array_reverse($project->milestone_items);
          $totalItems = count($reversedItems);
        @endphp
        @forelse($reversedItems as $index => $item)
          <div class="flex items-start gap-4">
            <!-- Timeline left side -->
            <div class="flex flex-col items-center">
              <!-- Percentage badge -->
              <div class="flex-shrink-0 w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-gray-700 font-bold text-xs">
                {{ $cumulative }}%
              </div>
              <!-- Vertical line and checkmark -->
              @if($index < $totalItems - 1)
                <div class="relative flex-1 w-0.5 bg-gray-200 my-2" style="min-height: 60px;">
                  <div class="absolute left-1/2 -translate-x-1/2 -bottom-3 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                  </div>
                </div>
              @endif
            </div>

            <!-- Milestone card -->
            <div class="flex-1 mb-6">
              <div class="border-2 border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-4 cursor-pointer hover:shadow-md transition-all" onclick="showMilestoneDetails({{ $item['item_id'] }})">
                <div class="flex items-start justify-between mb-2">
                  <h4 class="text-base font-bold text-gray-900">{{ $item['item_name'] }}</h4>
                  <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                    COMPLETED
                  </span>
                </div>
                <p class="text-xs text-gray-500 mb-2 uppercase tracking-wide">{{ \Carbon\Carbon::parse($item['date_to_finish'])->format('d M g:i A') }}</p>
                @if($item['item_description'])
                  <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $item['item_description'] }}</p>
                @endif
                <button class="text-amber-600 hover:text-amber-700 text-sm font-semibold flex items-center gap-1">
                  View Details
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          @php $cumulative -= $item['percentage_progress']; @endphp
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
        <button class="text-amber-600 hover:text-amber-700 p-2 rounded-lg hover:bg-amber-50 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
          </svg>
        </button>
      </div>
      <div id="completedDetailsContent" class="space-y-4">
        <div class="text-sm text-gray-500 text-center py-8">Select a milestone to view details</div>

        <!-- Hidden divs for each milestone item detail -->
        @foreach($project->milestone_items as $item)
          <div id="milestone-detail-{{ $item['item_id'] }}" class="hidden space-y-4">
            <!-- Milestone header -->
            <div class="flex items-center justify-between">
              <h4 class="text-lg font-bold text-gray-900">{{ $item['item_name'] }}</h4>
              <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                COMPLETED
              </span>
            </div>

            <!-- Date -->
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($item['date_to_finish'])->format('d M g:i A') }}</p>

            <!-- Description -->
            <p class="text-sm text-gray-700 leading-relaxed">{{ $item['item_description'] ?? 'No description' }}</p>

            <!-- List of Reports Section -->
            <div class="pt-4">
              <h5 class="text-sm font-bold text-gray-900 mb-3 uppercase tracking-wide">List of Reports</h5>
              @if(count($item['progress']) > 0)
                <div class="space-y-2">
                  @foreach($item['progress'] as $prog)
                    <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                      <p class="text-sm font-semibold text-gray-900">{{ $prog['purpose'] }}</p>
                      <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($prog['submitted_at'])->format('M d, Y g:i A') }}</p>
                      <span class="inline-flex mt-2 px-2 py-1 text-xs font-semibold rounded {{ $prog['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($prog['status']) }}
                      </span>

                      <!-- Files for this specific progress report -->
                      @if(count($prog['files']) > 0)
                        <div class="mt-3 space-y-1">
                          @foreach($prog['files'] as $file)
                            <a href="{{ asset('storage/' . $file['file_path']) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-amber-100 to-orange-100 border border-amber-300 rounded-lg hover:shadow-md transition-all text-sm">
                              <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                              </svg>
                              <span class="text-xs font-medium text-gray-900">{{ $file['original_name'] }}</span>
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
    <div class="flex items-center justify-between border-b-2 border-green-400 pb-3">
      <div>
        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Payment Summary
        </h3>
        <p class="text-xs text-gray-500 mt-1">This section contains uploaded receipts and payment confirmations related to completed milestones</p>
      </div>
    </div>

    <!-- Stats grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200 group">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-gray-600 font-medium">Total Milestones Paid</p>
          <svg class="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-xl font-bold text-gray-900">{{ $project->total_milestones_paid }}</p>
      </div>
      <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200 group">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-gray-600 font-medium">Total Amount Paid</p>
          <svg class="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-xl font-bold text-green-600">₱{{ number_format($project->total_amount_paid, 2) }}</p>
      </div>
      <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200 group">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-gray-600 font-medium">Last Payment Date</p>
          <svg class="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <p class="text-sm font-semibold text-gray-900">{{ $project->last_payment_date ? \Carbon\Carbon::parse($project->last_payment_date)->format('M d, Y') : '—' }}</p>
      </div>
      <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200 group">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-gray-600 font-medium">Over All Payment Status</p>
          <svg class="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-sm font-bold text-green-600">{{ $project->overall_payment_status }}</p>
      </div>
    </div>

    <div class="rounded-lg border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-200">
          <tr>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Milestone Period</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Amount Paid</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Date of Payment</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Proof of Payment</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Verification Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          @forelse($project->payments as $payment)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm font-semibold">{{ $payment->sequence_order ?? '—' }}</td>
              <td class="px-4 py-3 text-sm">
                @if($payment->date_to_finish)
                  {{ \Carbon\Carbon::parse($payment->date_to_finish)->format('M d, Y') }}
                @else
                  —
                @endif
              </td>
              <td class="px-4 py-3 text-sm font-semibold">₱{{ number_format($payment->amount, 2) }}</td>
              <td class="px-4 py-3 text-sm">{{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('M d, Y') : '—' }}</td>
              <td class="px-4 py-3 text-sm">
                @if($payment->receipt_photo)
                  <a href="{{ asset('storage/' . $payment->receipt_photo) }}" target="_blank" class="text-blue-600 hover:underline">View</a>
                @else
                  —
                @endif
              </td>
              <td class="px-4 py-3">
                @php
                  $statusClass = $payment->payment_status === 'approved'
                    ? 'bg-green-100 text-green-800'
                    : ($payment->payment_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                @endphp
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                  {{ ucfirst($payment->payment_status) }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-4 py-8 text-center text-gray-500">No payment records available</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Footer -->
<div class="border-t border-gray-200 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl flex justify-end gap-3">
  <button onclick="hideCompletedProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    Close
  </button>
</div>
