<!-- Header with Owner Info -->
<div class="bg-gradient-to-r from-green-500 to-emerald-600 px-4 py-3 rounded-t-xl flex-shrink-0">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center overflow-hidden ring-2 ring-white/30">
        @if($project->owner_profile_pic)
          <img src="{{ asset('storage/' . $project->owner_profile_pic) }}" alt="Owner" class="w-full h-full object-cover">
        @else
          <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        @endif
      </div>
      <div class="text-white">
        <h3 class="text-sm font-bold">{{ $project->owner_name }}</h3>
        <p class="text-[10px] opacity-80 flex items-center gap-1.5">
          <span class="inline-block w-1.5 h-1.5 bg-green-300 rounded-full"></span>
          Completed Project
          @if($project->timeline_end)
            <span class="ml-1">{{ \Carbon\Carbon::parse($project->timeline_end)->format('M d, Y') }}</span>
          @endif
        </p>
      </div>
    </div>
    <button type="button" onclick="hideCompletedProjectModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
</div>

<style>.cmc-scroll::-webkit-scrollbar{display:none}</style>
<div class="cmc-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">
  <!-- Completion Details (inline) -->
  @php $cd = $completionData ?? []; @endphp
  <div class="space-y-3">
    <!-- Status / Date / Duration row -->
    <div class="grid md:grid-cols-2 gap-3">
      <div class="bg-white border border-gray-200 rounded-lg p-2">
        <div class="flex items-center gap-1.5 mb-0.5">
          <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Project Status</label>
        </div>
        <p class="text-xs font-bold text-gray-900 pl-5">{{ ucwords(str_replace('_', ' ', $cd['project_status'] ?? 'Completed')) }}</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-lg p-3 flex items-start gap-2">
        <div class="w-8 h-8 rounded-lg bg-green-600 flex items-center justify-center flex-shrink-0 mt-0.5">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <p class="text-xs font-bold text-gray-900 mb-1">Project Completion Status</p>
          <p class="text-[10px] text-gray-600 leading-relaxed">All project milestones have been successfully completed and verified by the system administrator.</p>
        </div>
      </div>
      <div class="bg-white border border-gray-200 rounded-lg p-2">
        <div class="flex items-center gap-1.5 mb-0.5">
          <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Total Duration</label>
        </div>
        <p class="text-xs font-bold text-gray-900 pl-5">{{ $cd['duration_text'] ?? '—' }}</p>
      </div>
      <div class="bg-white border border-gray-200 rounded-lg p-2">
        <div class="flex items-center gap-1.5 mb-0.5">
          <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Date Completed</label>
        </div>
        <p class="text-xs font-bold text-gray-900 pl-5">{{ $cd['completion_date'] ?? '—' }}</p>
      </div>
    </div>

    <!-- Project Feedbacks -->
    <div class="border-t border-gray-200 pt-3">
      <div class="flex items-center gap-2 mb-3">
        <div class="w-6 h-6 rounded-md bg-green-600 flex items-center justify-center flex-shrink-0">
          <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
          </svg>
        </div>
        <h3 class="text-xs font-bold text-gray-900">Project Feedbacks</h3>
      </div>

      @if(!empty($cd['reviews']) && count($cd['reviews']) > 0)
        <div class="grid md:grid-cols-2 gap-3">
          @foreach($cd['reviews'] as $review)
            @if(($review['role'] ?? '') === 'property_owner')
              <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </div>
                    <div>
                      <p class="text-xs font-bold text-gray-900">{{ $review['name'] }}</p>
                      <p class="text-[10px] text-green-700 font-semibold">{{ $review['role_label'] }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-0.5">
                    @for ($i = 1; $i <= 5; $i++)
                      <svg class="w-3.5 h-3.5 {{ $i <= ($review['rating'] ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                      </svg>
                    @endfor
                  </div>
                </div>
                <textarea readonly class="w-full border border-gray-200 rounded-md px-3 py-2 text-[10px] text-gray-700 bg-white resize-none focus:outline-none" rows="4">{{ $review['comment'] ?? 'No feedback provided' }}</textarea>
              </div>
            @else
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                      </svg>
                    </div>
                    <div>
                      <p class="text-xs font-bold text-gray-900">{{ $review['name'] }}</p>
                      <p class="text-[10px] text-blue-700 font-semibold">{{ $review['role_label'] }}</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-0.5">
                    @for ($i = 1; $i <= 5; $i++)
                      <svg class="w-3.5 h-3.5 {{ $i <= ($review['rating'] ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                      </svg>
                    @endfor
                  </div>
                </div>
                <textarea readonly class="w-full border border-blue-200 rounded-md px-3 py-2 text-[10px] text-gray-700 bg-white resize-none focus:outline-none" rows="4">{{ $review['comment'] ?? 'No feedback provided' }}</textarea>
              </div>
            @endif
          @endforeach
        </div>
      @else
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
          <svg class="w-8 h-8 text-gray-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
          </svg>
          <p class="text-xs text-gray-400">No feedback available for this project yet.</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Executive Summary Card -->
  <div class="bg-white border border-gray-200 rounded-lg p-2.5">
    <div class="flex items-center justify-between mb-2">
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg bg-green-600 flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-xs font-bold text-gray-900">Executive Summary</h3>
          <p class="text-[10px] text-gray-500">Project completion overview and financial summary</p>
        </div>
      </div>
      @php
        $originalBudget = $project->budget_range_min ?? 0;
        $finalBudget = $project->total_amount_paid ?? 0;
        $paymentMode = $project->payment_mode ?? 'full_payment';
        $downpaymentAmount = $project->downpayment_amount ?? 0;
        $downpaymentCleared = $project->downpayment_cleared ?? false;
      @endphp
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
      <!-- Original Budget -->
      <div class="bg-white rounded-md p-2 border border-gray-100">
        <div class="flex items-center justify-between mb-1">
          <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Original Budget</span>
          <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-sm font-bold text-gray-900">₱{{ number_format($originalBudget, 2) }}</p>
        <p class="text-[10px] text-gray-400">Initial project estimate</p>
      </div>

      <!-- Final Budget -->
      <div class="bg-white rounded-md p-2 border border-gray-100">
        <div class="flex items-center justify-between mb-1">
          <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Final Budget</span>
          <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-sm font-bold text-green-600">₱{{ number_format($finalBudget, 2) }}</p>
        <p class="text-[10px] text-gray-400">Total project cost</p>
      </div>

      <!-- Payment Mode -->
      <div class="bg-white rounded-md p-2 border border-gray-100">
        <div class="flex items-center justify-between mb-1">
          <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Payment Mode</span>
          <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <p class="text-xs font-bold text-gray-900">{{ $paymentMode === 'staggered' ? 'Staggered' : 'Full Payment' }}</p>
        @if($paymentMode === 'staggered' && $downpaymentAmount > 0)
          <p class="text-[10px] text-gray-500">
            Downpayment: ₱{{ number_format($downpaymentAmount, 2) }}
            @if($downpaymentCleared)
              <span class="inline-flex items-center ml-1 text-green-600">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Cleared
              </span>
            @endif
          </p>
        @else
          <p class="text-[10px] text-gray-400">Single payment transaction</p>
        @endif
      </div>

      <!-- Completion Rate -->
      <div class="bg-white rounded-md p-2 border border-gray-100">
        <div class="flex items-center justify-between mb-1">
          <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Completion Rate</span>
          <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-sm font-bold text-green-600">100%</p>
        <p class="text-[10px] text-gray-400">{{ $project->total_milestone_items ?? 0 }}/{{ $project->total_milestone_items ?? 0 }} milestones</p>
      </div>

      <!-- Total Paid -->
      <div class="bg-white rounded-md p-2 border border-gray-100">
        <div class="flex items-center justify-between mb-1">
          <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Total Paid</span>
          <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <p class="text-sm font-bold text-green-600">₱{{ number_format($project->total_amount_paid ?? 0, 2) }}</p>
        <p class="text-[10px] text-gray-400">All payments verified</p>
      </div>

      <!-- Project Duration -->
      <div class="bg-white rounded-md p-2 border border-gray-100">
        <div class="flex items-center justify-between mb-1">
          <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Project Duration</span>
          <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        @php
          $startDate = $project->timeline_start ? \Carbon\Carbon::parse($project->timeline_start) : null;
          $endDate = $project->timeline_end ? \Carbon\Carbon::parse($project->timeline_end) : null;
          
          // Get the latest approved timeline extension
          $latestExtension = DB::table('project_updates')
              ->where('project_id', $project->project_id)
              ->where('status', 'approved')
              ->whereNotNull('proposed_end_date')
              ->orderBy('applied_at', 'desc')
              ->first();
          
          $originalEndDate = $latestExtension && $latestExtension->current_end_date 
              ? \Carbon\Carbon::parse($latestExtension->current_end_date) 
              : null;
          $extendedEndDate = $latestExtension && $latestExtension->proposed_end_date 
              ? \Carbon\Carbon::parse($latestExtension->proposed_end_date) 
              : null;
          $hasExtension = $latestExtension && $originalEndDate && $extendedEndDate && $originalEndDate->ne($extendedEndDate);
          
          $durationDays = $startDate && $endDate ? (int) $startDate->diffInDays($endDate) : 0;
        @endphp
        <p class="text-sm font-bold text-gray-900">{{ $durationDays }} days</p>
        <p class="text-[10px] text-gray-400">
          @if($startDate && $endDate)
            {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}
          @else
            Timeline not available
          @endif
        </p>
      </div>
    </div>
  </div>

  <!-- Budget & Timeline History (Collapsible) -->
  <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <button type="button" onclick="toggleBudgetHistory()" class="w-full px-3 py-2.5 flex items-center justify-between hover:bg-gray-50 transition-colors duration-200">
      <div class="flex items-center gap-2">
        <div class="w-6 h-6 rounded-md bg-purple-600 flex items-center justify-center">
          <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <div class="text-left">
          <h3 class="text-xs font-bold text-gray-900">Budget & Timeline History</h3>
          <p class="text-[10px] text-gray-500">View all budget changes and timeline extensions</p>
        </div>
      </div>
      <svg id="budgetHistoryChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <div id="budgetHistoryContent" class="hidden border-t border-gray-200">
      <div class="p-3 space-y-3">
        <!-- Budget Changes Timeline -->
        <div>
          <h4 class="text-xs font-bold text-gray-900 mb-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Budget Changes
          </h4>
          
          @php
            $budgetHistory = $project->budget_history ?? [];
          @endphp

          @if(count($budgetHistory) > 0)
            <div class="space-y-2">
              @foreach($budgetHistory as $change)
                <div class="flex gap-2 p-2.5 bg-purple-50 rounded-md border border-purple-100">
                  <div class="flex-shrink-0">
                    <div class="w-6 h-6 rounded-full {{ $change['change_type'] === 'increase' ? 'bg-green-100' : ($change['change_type'] === 'decrease' ? 'bg-red-100' : 'bg-gray-100') }} flex items-center justify-center">
                      @if($change['change_type'] === 'increase')
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                      @elseif($change['change_type'] === 'decrease')
                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                      @else
                        <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                      @endif
                    </div>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-start justify-between mb-1">
                      <div>
                        <p class="text-xs font-bold text-gray-900">
                          Budget {{ ucfirst($change['change_type']) }}
                          <span class="inline-flex ml-1 px-1.5 py-0.5 text-[10px] font-semibold rounded-full {{ $change['status'] === 'approved' ? 'bg-green-100 text-green-800' : ($change['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($change['status']) }}
                          </span>
                        </p>
                        <p class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($change['date_proposed'])->format('M d, Y g:i A') }}</p>
                      </div>
                      <div class="text-right">
                        <p class="text-[10px] text-gray-500">Previous</p>
                        <p class="text-xs font-semibold text-gray-900">₱{{ number_format($change['previous_budget'], 2) }}</p>
                      </div>
                    </div>
                    <div class="flex items-center gap-1.5 mb-1">
                      <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                      </svg>
                      <p class="text-xs font-bold {{ $change['change_type'] === 'increase' ? 'text-green-600' : ($change['change_type'] === 'decrease' ? 'text-red-600' : 'text-gray-900') }}">
                        ₱{{ number_format($change['updated_budget'], 2) }}
                      </p>
                    </div>
                    @if($change['reason'])
                      <p class="text-[10px] text-gray-700 bg-white rounded px-2 py-1.5 border border-purple-100">
                        <span class="font-semibold">Reason:</span> {{ $change['reason'] }}
                      </p>
                    @endif
                    @if($change['status'] === 'approved' && $change['date_approved'])
                      <p class="text-[10px] text-green-600 mt-1">
                        <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Approved on {{ \Carbon\Carbon::parse($change['date_approved'])->format('M d, Y') }}
                      </p>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-4 bg-gray-50 rounded-md border border-gray-200">
              <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <p class="text-xs text-gray-500">No budget changes recorded</p>
              <p class="text-[10px] text-gray-400">Project completed with original budget</p>
            </div>
          @endif
        </div>

        <!-- Timeline Extensions -->
        <div>
          <h4 class="text-xs font-bold text-gray-900 mb-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Timeline Extensions
          </h4>

          @php
            $timelineExtensions = $project->timeline_extensions ?? [];
          @endphp

          @if(count($timelineExtensions) > 0)
            <div class="space-y-2">
              @foreach($timelineExtensions as $extension)
                <div class="flex gap-2 p-2.5 bg-indigo-50 rounded-md border border-indigo-100">
                  <div class="flex-shrink-0">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center">
                      <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </div>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-start justify-between mb-1">
                      <div>
                        <p class="text-xs font-bold text-gray-900">
                          Timeline Extension
                          <span class="inline-flex ml-1 px-1.5 py-0.5 text-[10px] font-semibold rounded-full {{ $extension['status'] === 'approved' ? 'bg-green-100 text-green-800' : ($extension['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($extension['status']) }}
                          </span>
                        </p>
                        <p class="text-[10px] text-gray-500">Requested on {{ \Carbon\Carbon::parse($extension['date_proposed'])->format('M d, Y') }}</p>
                      </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-1">
                      <div>
                        <p class="text-[10px] text-gray-500">Previous End Date</p>
                        <p class="text-xs font-semibold text-gray-900">{{ \Carbon\Carbon::parse($extension['previous_end_date'])->format('M d, Y') }}</p>
                      </div>
                      <div>
                        <p class="text-[10px] text-gray-500">Proposed End Date</p>
                        <p class="text-xs font-semibold text-indigo-600">{{ \Carbon\Carbon::parse($extension['proposed_end_date'])->format('M d, Y') }}</p>
                      </div>
                    </div>
                    @if($extension['reason'])
                      <p class="text-[10px] text-gray-700 bg-white rounded px-2 py-1.5 border border-indigo-100">
                        <span class="font-semibold">Reason:</span> {{ $extension['reason'] }}
                      </p>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-4 bg-gray-50 rounded-md border border-gray-200">
              <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-xs text-gray-500">No timeline extensions recorded</p>
              <p class="text-[10px] text-gray-400">Project completed within original timeline</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Project Details and Contractor Details (2-Column) -->
  <div id="completedDetailsSection" class="grid lg:grid-cols-2 gap-3">
    <!-- Project Details -->
    <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
      <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Project Details
      </h3>
      <div class="space-y-0.5 text-[11px]">
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
          <span class="text-gray-500">Project Title</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->project_title ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
          <span class="text-gray-500">Property Address</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->project_location ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
          <span class="text-gray-500">Property Type:</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->property_type ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
          <span class="text-gray-500">Lot Size (sqm)</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->lot_size ? number_format($project->lot_size, 2) : '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
          <span class="text-gray-500">Target Timeline</span>
          <span class="font-semibold text-gray-900 text-right">
            @if($project->timeline_start && $project->timeline_end)
              {{ \Carbon\Carbon::parse($project->timeline_start)->format('M d, Y') }} - 
              @if($hasExtension)
                <span class="block mt-0.5">
                  <span class="text-gray-400 line-through text-[10px]">{{ $originalEndDate->format('M d, Y') }}</span>
                  <span class="text-blue-600 font-bold ml-1">{{ $extendedEndDate->format('M d, Y') }}</span>
                  <span class="inline-block ml-1 px-1 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-semibold rounded">+{{ max(1, (int) ceil($originalEndDate->diffInDays($extendedEndDate, false))) }}d</span>
                </span>
              @else
                {{ \Carbon\Carbon::parse($project->timeline_end)->format('M d, Y') }}
              @endif
            @else
              —
            @endif
          </span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
          <span class="text-gray-500">Budget</span>
          <span class="font-semibold text-green-600 text-right">
            @if($project->budget_range_min && $project->budget_range_max)
              ₱{{ number_format($project->budget_range_min) }} - ₱{{ number_format($project->budget_range_max) }}
            @else
              —
            @endif
          </span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
          <span class="text-gray-500">Bidding Deadline</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->bidding_due ? \Carbon\Carbon::parse($project->bidding_due)->format('M d, Y') : '—' }}</span>
        </div>
      </div>
      <div>
        <span class="text-[10px] text-gray-400 block mb-1">Supporting Files</span>
        <div class="flex flex-wrap gap-1.5">
          <span class="text-[10px] text-gray-400">No files available</span>
        </div>
      </div>
    </div>

    <!-- Contractor Details -->
    <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
      <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Contractor Details
      </h3>
      <div class="space-y-0.5 text-[11px]">
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
          <span class="text-gray-500">Company Name :</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_name ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
          <span class="text-gray-500">Email Address :</span>
          <span class="font-semibold text-blue-600 text-right">{{ $project->contractor_email ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
          <span class="text-gray-500">PCAB No.:</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_pcab ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
          <span class="text-gray-500">PCAB Category:</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_category ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
          <span class="text-gray-500">PCAB Expiration Date</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_pcab_expiry ? \Carbon\Carbon::parse($project->contractor_pcab_expiry)->format('M d, Y') : '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
          <span class="text-gray-500">Business Permit No.:</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_permit ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
          <span class="text-gray-500">Permit City:</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_city ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
          <span class="text-gray-500">Business Permit Expiration</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_permit_expiry ? \Carbon\Carbon::parse($project->contractor_permit_expiry)->format('M d, Y') : '—' }}</span>
        </div>
        <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
          <span class="text-gray-500">TIN Registration number</span>
          <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_tin ?? '—' }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Project's Milestone and Details (Row) -->
  <div class="grid lg:grid-cols-2 gap-3">
    <!-- Project's Milestone -->
    <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
      <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        Project's Milestone
      </h3>
      <div class="space-y-0">
        @php
          $itemsWithCumulative = [];
          $cumulative = 0;
          foreach($project->milestone_items as $item) {
            $cumulative += ($item['percentage_progress'] ?? 0);
            $itemsWithCumulative[] = ['item' => $item, 'cumulative' => $cumulative];
          }
          $reversedItems = array_reverse($itemsWithCumulative);
          $totalItems = count($reversedItems);
        @endphp
        @forelse($reversedItems as $index => $itemData)
          @php
            $item = $itemData['item'];
            $cumulative = round($itemData['cumulative']);
          @endphp
          <div class="flex items-start gap-2">
            <!-- Timeline left side -->
            <div class="flex flex-col items-center">
              <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-700 font-bold text-[10px]">
                {{ $cumulative }}%
              </div>
              @if($index < $totalItems - 1)
                <div class="relative flex-1 w-0.5 bg-gray-200 my-1.5" style="min-height:40px;">
                  <div class="absolute left-1/2 -translate-x-1/2 -bottom-3 w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                  </div>
                </div>
              @endif
            </div>

            <!-- Milestone card -->
            <div class="flex-1 mb-3">
              <div class="border border-green-200 bg-green-50 rounded-md p-2.5 cursor-pointer hover:shadow-sm transition-all" data-item-id="{{ $itemData['item']['item_id'] }}" onclick="showMilestoneDetails(Number(this.dataset.itemId))">
                <div class="flex items-start justify-between mb-1">
                  <div class="flex-1">
                    <h4 class="text-xs font-bold text-gray-900">{{ $itemData['item']['item_name'] }}</h4>
                    <div class="flex gap-1.5 mt-0.5">
                      @if($itemData['item']['was_extended'] ?? false)
                      <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-medium rounded bg-blue-100 text-blue-700">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Extended {{ $itemData['item']['extension_count'] ?? 1 }}x
                      </span>
                      @endif
                      @if(($itemData['item']['carry_forward_amount'] ?? 0) > 0)
                      <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[10px] font-medium rounded bg-orange-100 text-orange-700">
                        +₱{{ number_format($itemData['item']['carry_forward_amount'], 0) }}
                      </span>
                      @endif
                    </div>
                  </div>
                  <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                    COMPLETED
                  </span>
                </div>
                @if($itemData['item']['was_extended'] ?? false)
                  <div class="mb-1">
                    <p class="text-[10px] text-gray-400 line-through">Original: {{ $itemData['item']['original_date_to_finish'] ? \Carbon\Carbon::parse($itemData['item']['original_date_to_finish'])->format('M d, Y') : '—' }}</p>
                    <p class="text-[10px] text-blue-600 font-semibold uppercase">Extended: {{ \Carbon\Carbon::parse($itemData['item']['date_to_finish'])->format('M d, Y g:i A') }}</p>
                  </div>
                @else
                  <p class="text-[10px] text-gray-500 mb-1 uppercase tracking-wide">{{ \Carbon\Carbon::parse($itemData['item']['date_to_finish'])->format('M d, Y g:i A') }}</p>
                @endif
                @if($itemData['item']['item_description'])
                  <p class="text-[10px] text-gray-600 mb-1 line-clamp-2">{{ $itemData['item']['item_description'] }}</p>
                @endif
                <button type="button" class="text-green-600 hover:text-green-700 text-[10px] font-semibold flex items-center gap-1">
                  View Details
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        @empty
          <p class="text-xs text-gray-400 text-center py-4">No milestone items available</p>
        @endforelse
      </div>
    </div>

    <!-- Details -->
    <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
      <div class="flex items-center justify-between pb-2 border-b border-gray-200">
        <h3 class="font-bold text-gray-900 text-xs flex items-center gap-1.5">
          <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Details
        </h3>
      </div>
      <div id="completedDetailsContent" class="space-y-2">
        <div class="text-xs text-gray-400 text-center py-4">Select a milestone to view details</div>

        @foreach($project->milestone_items as $item)
          <div id="milestone-detail-{{ $item['item_id'] }}" class="hidden space-y-2">
            <div class="flex items-center justify-between">
              <h4 class="text-xs font-bold text-gray-900">{{ $item['item_name'] }}</h4>
              <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                COMPLETED
              </span>
            </div>

            @if($item['was_extended'] ?? false)
              <div class="bg-blue-50 border border-blue-200 rounded-md p-2">
                <div class="flex items-center gap-1.5 mb-1">
                  <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span class="text-[10px] font-semibold text-blue-700 uppercase">Date Extended {{ $item['extension_count'] ?? 1 }}x</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <p class="text-[10px] text-gray-500 mb-0.5">Original Date:</p>
                    <p class="text-xs font-semibold text-gray-700 line-through">{{ $item['original_date_to_finish'] ? \Carbon\Carbon::parse($item['original_date_to_finish'])->format('M d, Y') : '—' }}</p>
                  </div>
                  <div>
                    <p class="text-[10px] text-gray-500 mb-0.5">Extended To:</p>
                    <p class="text-xs font-semibold text-blue-700">{{ \Carbon\Carbon::parse($item['date_to_finish'])->format('M d, Y g:i A') }}</p>
                  </div>
                </div>
                @if($item['original_date_to_finish'] && $item['date_to_finish'])
                  @php
                    $originalDate = \Carbon\Carbon::parse($item['original_date_to_finish']);
                    $extendedDate = \Carbon\Carbon::parse($item['date_to_finish']);
                    $daysDiff = max(1, (int) ceil($originalDate->diffInDays($extendedDate, false)));
                  @endphp
                  @if($originalDate->ne($extendedDate))
                    <p class="text-[10px] text-blue-600 mt-1">+{{ $daysDiff }} day{{ $daysDiff != 1 ? 's' : '' }} extension</p>
                  @endif
                @endif
              </div>
            @else
              <div class="bg-gray-50 border border-gray-200 rounded-md p-2">
                <p class="text-[10px] text-gray-500 mb-0.5">Completion Date:</p>
                <p class="text-xs font-semibold text-gray-900">{{ \Carbon\Carbon::parse($item['date_to_finish'])->format('M d, Y g:i A') }}</p>
              </div>
            @endif

            <p class="text-[10px] text-gray-600">{{ $item['item_description'] ?? 'No description' }}</p>

            <div class="pt-2">
              <h5 class="text-xs font-bold text-gray-900 mb-2 uppercase tracking-wide">List of Reports</h5>
              @if(count($item['progress']) > 0)
                <div class="space-y-1.5">
                  @foreach($item['progress'] as $prog)
                    <div class="p-2 bg-gray-50 border border-gray-200 rounded-md">
                      <p class="text-xs font-semibold text-gray-900">{{ $prog['purpose'] }}</p>
                      <p class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($prog['submitted_at'])->format('M d, Y g:i A') }}</p>
                      <span class="inline-flex mt-1 px-1.5 py-0.5 text-[10px] font-semibold rounded {{ $prog['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($prog['status']) }}
                      </span>
                      @if(count($prog['files']) > 0)
                        <div class="mt-2 space-y-1">
                          @foreach($prog['files'] as $file)
                            <a href="{{ asset('storage/' . $file['file_path']) }}" target="_blank" class="flex items-center gap-1.5 px-2 py-1.5 bg-amber-50 border border-amber-200 rounded hover:shadow-sm transition-all text-[10px]">
                              <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                              </svg>
                              <span class="font-medium text-gray-900">{{ $file['original_name'] }}</span>
                            </a>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  @endforeach
                </div>
              @else
                <p class="text-xs text-gray-400">No reports available</p>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <!-- Change Audit Log (Collapsible) -->
  <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <button type="button" onclick="toggleChangeAuditLog()" class="w-full px-3 py-2.5 flex items-center justify-between hover:bg-gray-50 transition-colors duration-200">
      <div class="flex items-center gap-2">
        <div class="w-6 h-6 rounded-md bg-amber-500 flex items-center justify-center flex-shrink-0">
          <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div class="text-left">
          <h3 class="text-xs font-bold text-gray-900">Change Audit Log</h3>
          <p class="text-[10px] text-gray-500">Complete chronological history of all project changes</p>
        </div>
      </div>
      <svg id="auditLogChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <div id="auditLogContent" class="hidden border-t border-gray-200">
      <div class="p-3">
        @php
          $auditLog = $project->change_audit_log ?? [];
        @endphp

        @if(count($auditLog) > 0)
          <div class="relative">
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-amber-200"></div>
            <div class="space-y-3">
              @foreach($auditLog as $index => $log)
                <div class="relative flex gap-3">
                  <div class="flex-shrink-0 w-7 h-7 rounded-full bg-white border-2 border-amber-400 flex items-center justify-center z-10">
                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                  </div>
                  <div class="flex-1 bg-white rounded-md border border-amber-100 p-2.5">
                    <div class="flex items-start justify-between mb-1">
                      <div class="flex-1">
                        <p class="text-xs font-bold text-gray-900">{{ $log['action'] }}</p>
                        <p class="text-[10px] text-gray-500 mt-0.5 flex items-center gap-1">
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                          </svg>
                          {{ \Carbon\Carbon::parse($log['date'])->format('M d, Y g:i A') }}
                        </p>
                      </div>
                      @if($log['reference'])
                        <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded bg-amber-100 text-amber-800 border border-amber-200">
                          {{ $log['reference'] }}
                        </span>
                      @endif
                    </div>
                    @if($log['performed_by'])
                      <div class="flex items-center gap-1.5 mb-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <p class="text-[10px] text-gray-700"><span class="font-semibold">Performed by:</span> {{ $log['performed_by'] }}</p>
                      </div>
                    @endif
                    @if($log['notes'])
                      <div class="p-2 bg-gray-50 rounded border border-amber-100">
                        <p class="text-[10px] text-gray-700">{{ $log['notes'] }}</p>
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @else
          <div class="text-center py-6 bg-gray-50 rounded-md border border-gray-200">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-xs text-gray-500 font-semibold">No changes recorded</p>
            <p class="text-[10px] text-gray-400">This project was completed without any modifications</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Payment Summary (Row) -->
  <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-2">
    <div class="flex items-center justify-between border-b border-gray-200 pb-2">
      <div>
        <h3 class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
          <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Payment Summary
        </h3>
        <p class="text-[10px] text-gray-400 mt-0.5">Uploaded receipts and payment confirmations related to completed milestones</p>
      </div>
    </div>

    <!-- Stats grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-2">
      <div class="bg-green-50 rounded-md p-2 border border-green-100">
        <div class="flex items-center justify-between mb-1">
          <p class="text-[10px] font-semibold text-gray-500 uppercase">Payment Mode</p>
          <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <p class="text-xs font-bold text-gray-900">{{ $paymentMode === 'staggered' ? 'Staggered' : 'Full Payment' }}</p>
        @if($paymentMode === 'staggered')
          <p class="text-[10px] text-green-600">Milestone-based</p>
        @else
          <p class="text-[10px] text-gray-400">Single payment</p>
        @endif
      </div>

      <div class="bg-green-50 rounded-md p-2 border border-green-100">
        <div class="flex items-center justify-between mb-1">
          <p class="text-[10px] font-semibold text-gray-500 uppercase">Milestones Paid</p>
          <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-sm font-bold text-gray-900">{{ $project->total_milestones_paid }}/{{ $project->total_milestone_items ?? 0 }}</p>
      </div>

      <div class="bg-green-50 rounded-md p-2 border border-green-100">
        <div class="flex items-center justify-between mb-1">
          <p class="text-[10px] font-semibold text-gray-500 uppercase">Total Paid</p>
          <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-sm font-bold text-green-600">₱{{ number_format($project->total_amount_paid, 2) }}</p>
      </div>

      <div class="bg-green-50 rounded-md p-2 border border-green-100">
        <div class="flex items-center justify-between mb-1">
          <p class="text-[10px] font-semibold text-gray-500 uppercase">Last Payment</p>
          <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <p class="text-xs font-bold text-gray-900">{{ $project->last_payment_date ? \Carbon\Carbon::parse($project->last_payment_date)->format('M d, Y') : '—' }}</p>
      </div>

      <div class="bg-green-50 rounded-md p-2 border border-green-100">
        <div class="flex items-center justify-between mb-1">
          <p class="text-[10px] font-semibold text-gray-500 uppercase">Status</p>
          <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-xs font-bold text-green-600">{{ $project->overall_payment_status }}</p>
      </div>
    </div>

    <div class="rounded-md border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-green-50 border-b border-green-200">
          <tr>
            <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Payment Type</th>
            <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Milestone</th>
            <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Period</th>
            <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Amount</th>
            <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Date</th>
            <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Proof</th>
            <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          @forelse($project->payments as $payment)
            <tr class="hover:bg-gray-50">
              <td class="px-2.5 py-1.5">
                @php
                  $paymentType = $payment->payment_type ?? 'milestone';
                  $typeColors = [
                    'downpayment' => 'bg-purple-100 text-purple-800 border-purple-200',
                    'milestone' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'final' => 'bg-green-100 text-green-800 border-green-200'
                  ];
                  $typeColor = $typeColors[$paymentType] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                @endphp
                <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded border {{ $typeColor }}">
                  {{ ucfirst($paymentType) }}
                </span>
              </td>
              <td class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-900">{{ $payment->milestone_item_title ?? '—' }}</td>
              <td class="px-2.5 py-1.5 text-[10px] text-gray-700">{{ $payment->milestone_period ?? '—' }}</td>
              <td class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-900">₱{{ number_format($payment->amount, 2) }}</td>
              <td class="px-2.5 py-1.5 text-[10px] text-gray-700">{{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('M d, Y') : '—' }}</td>
              <td class="px-2.5 py-1.5 text-[10px]">
                @if($payment->receipt_photo)
                  <a href="#" class="open-doc-btn text-blue-600 hover:text-blue-700 font-medium hover:underline" data-doc-src="{{ asset('storage/' . $payment->receipt_photo) }}" data-doc-title="Proof of Payment">View</a>
                @else
                  —
                @endif
              </td>
              <td class="px-2.5 py-1.5">
                @php
                  $statusClass = $payment->payment_status === 'approved'
                    ? 'bg-green-100 text-green-800'
                    : ($payment->payment_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                @endphp
                <span class="inline-flex px-1.5 py-0.5 text-[10px] font-semibold rounded-full {{ $statusClass }}">
                  {{ ucfirst($payment->payment_status) }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-2.5 py-4 text-center text-xs text-gray-400">No payment records available</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <button 
      type="button"
      data-project-id="{{ $project->project_id }}"
      data-project-title="{{ $project->project_title }}"
      onclick="openAdminPaymentHistoryModal(Number(this.dataset.projectId), this.dataset.projectTitle)"
      class="w-full px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center gap-1.5"
    >
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <span>View Full Payment History</span>
    </button>
  </div>
</div>

<!-- Footer -->
<div class="border-t border-gray-200 px-4 py-3 bg-gray-50 rounded-b-xl flex justify-between items-center gap-3 flex-shrink-0">
  <button type="button" data-project-id="{{ $project->project_id }}" onclick="showProjectSummaryModal(Number(this.dataset.projectId))" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-colors flex items-center gap-1.5">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    View Project Summary
  </button>
  <button type="button" onclick="hideCompletedProjectModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors flex items-center gap-1.5">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    Close
  </button>
</div>
