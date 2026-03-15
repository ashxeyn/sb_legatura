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
    <button type="button" onclick="hideCompletedProjectModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
</div>

<div class="p-6 space-y-6 max-h-[calc(100vh-12rem)] overflow-y-auto">
  <!-- Completion Details (inline) -->
  @php $cd = $completionData ?? []; @endphp
  <div class="space-y-4">
    <!-- Status / Date / Duration row -->
    <div class="grid md:grid-cols-2 gap-4">
      <div class="space-y-3">
        <div class="bg-gradient-to-br from-white to-green-50 border border-green-200 rounded-xl p-4 hover:shadow-md transition-all duration-300 group">
          <div class="flex items-center gap-2 mb-1">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Project Status</label>
          </div>
          <p class="text-sm font-bold text-gray-900 pl-6">{{ ucwords(str_replace('_', ' ', $cd['project_status'] ?? 'Completed')) }}</p>
        </div>
        <div class="bg-gradient-to-br from-white to-green-50 border border-green-200 rounded-xl p-4 hover:shadow-md transition-all duration-300 group">
          <div class="flex items-center gap-2 mb-1">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Date Completed</label>
          </div>
          <p class="text-sm font-bold text-gray-900 pl-6">{{ $cd['completion_date'] ?? '—' }}</p>
        </div>
        <div class="bg-gradient-to-br from-white to-green-50 border border-green-200 rounded-xl p-4 hover:shadow-md transition-all duration-300 group">
          <div class="flex items-center gap-2 mb-1">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Total Duration</label>
          </div>
          <p class="text-sm font-bold text-gray-900 pl-6">{{ $cd['duration_text'] ?? '—' }}</p>
        </div>
      </div>
      <div>
        <div class="bg-gradient-to-br from-white to-purple-50 border border-purple-200 rounded-xl p-4 h-full flex items-start gap-3">
          <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-xs text-gray-600 font-semibold mb-1">Project Completion</p>
            <p class="text-xs text-gray-500 leading-relaxed">All project milestones have been successfully completed and verified by the system administrator.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Project Feedbacks -->
    <div class="border-t-2 border-gray-200 pt-4">
      <div class="flex items-center gap-2 mb-4">
        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
        </svg>
        <h3 class="text-base font-bold text-gray-900">Project Feedbacks</h3>
      </div>

      @if(!empty($cd['reviews']) && count($cd['reviews']) > 0)
        @foreach($cd['reviews'] as $review)
          @if(($review['role'] ?? '') === 'property_owner')
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-200 rounded-xl p-5 mb-3 hover:shadow-lg transition-all duration-300">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="font-bold text-gray-900 text-sm">{{ $review['name'] }}</p>
                    <p class="text-xs text-amber-600 font-semibold">{{ $review['role_label'] }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-0.5">
                  @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-4 h-4 {{ $i <= ($review['rating'] ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                      <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                    </svg>
                  @endfor
                </div>
              </div>
              <textarea readonly class="w-full border border-amber-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white resize-none focus:outline-none" rows="3">{{ $review['comment'] ?? 'No feedback provided' }}</textarea>
            </div>
          @else
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-200 rounded-xl p-5 mb-3 hover:shadow-lg transition-all duration-300">
              <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                  </div>
                  <div>
                    <p class="font-bold text-gray-900 text-sm">{{ $review['name'] }}</p>
                    <p class="text-xs text-blue-600 font-semibold">{{ $review['role_label'] }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-0.5">
                  @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-4 h-4 {{ $i <= ($review['rating'] ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                      <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                    </svg>
                  @endfor
                </div>
              </div>
              <textarea readonly class="w-full border border-blue-200 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white resize-none focus:outline-none" rows="3">{{ $review['comment'] ?? 'No feedback provided' }}</textarea>
            </div>
          @endif
        @endforeach
      @else
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
          <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
          </svg>
          <p class="text-sm text-gray-500">No feedback available for this project yet.</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Executive Summary Card -->
  <div class="bg-gradient-to-br from-white via-green-50 to-emerald-50 border-2 border-green-300 rounded-xl p-6 shadow-lg">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-bold text-gray-900">Executive Summary</h3>
          <p class="text-xs text-gray-600">Project completion overview and financial summary</p>
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

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <!-- Original Budget -->
      <div class="bg-white rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Original Budget</span>
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-xl font-bold text-gray-900">₱{{ number_format($originalBudget, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Initial project estimate</p>
      </div>

      <!-- Final Budget -->
      <div class="bg-white rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Final Budget</span>
          <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-xl font-bold text-green-600">₱{{ number_format($finalBudget, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Total project cost</p>
      </div>

      <!-- Payment Mode -->
      <div class="bg-white rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Payment Mode</span>
          <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <p class="text-base font-bold text-gray-900">{{ $paymentMode === 'staggered' ? 'Staggered' : 'Full Payment' }}</p>
        @if($paymentMode === 'staggered' && $downpaymentAmount > 0)
          <p class="text-xs text-gray-600 mt-1">
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
          <p class="text-xs text-gray-500 mt-1">Single payment transaction</p>
        @endif
      </div>

      <!-- Completion Rate -->
      <div class="bg-white rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Completion Rate</span>
          <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-xl font-bold text-green-600">100%</p>
        <p class="text-xs text-gray-600 mt-1">{{ $project->total_milestone_items ?? 0 }}/{{ $project->total_milestone_items ?? 0 }} milestones</p>
      </div>

      <!-- Total Paid -->
      <div class="bg-white rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Total Paid</span>
          <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <p class="text-xl font-bold text-green-600">₱{{ number_format($project->total_amount_paid ?? 0, 2) }}</p>
        <p class="text-xs text-gray-600 mt-1">All payments verified</p>
      </div>

      <!-- Project Duration -->
      <div class="bg-white rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Project Duration</span>
          <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <p class="text-xl font-bold text-gray-900">{{ $durationDays }} days</p>
        <p class="text-xs text-gray-600 mt-1">
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
  <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
    <button type="button" onclick="toggleBudgetHistory()" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors duration-200">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <div class="text-left">
          <h3 class="text-base font-bold text-gray-900">Budget & Timeline History</h3>
          <p class="text-xs text-gray-600">View all budget changes and timeline extensions</p>
        </div>
      </div>
      <svg id="budgetHistoryChevron" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <div id="budgetHistoryContent" class="hidden border-t border-gray-200">
      <div class="p-6 space-y-6">
        <!-- Budget Changes Timeline -->
        <div>
          <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Budget Changes
          </h4>
          
          @php
            $budgetHistory = $project->budget_history ?? [];
          @endphp

          @if(count($budgetHistory) > 0)
            <div class="space-y-3">
              @foreach($budgetHistory as $change)
                <div class="flex gap-4 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg border border-purple-200">
                  <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full {{ $change['change_type'] === 'increase' ? 'bg-green-100' : ($change['change_type'] === 'decrease' ? 'bg-red-100' : 'bg-gray-100') }} flex items-center justify-center">
                      @if($change['change_type'] === 'increase')
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                      @elseif($change['change_type'] === 'decrease')
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                      @else
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                      @endif
                    </div>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-start justify-between mb-2">
                      <div>
                        <p class="text-sm font-bold text-gray-900">
                          Budget {{ ucfirst($change['change_type']) }}
                          <span class="inline-flex ml-2 px-2 py-0.5 text-xs font-semibold rounded-full {{ $change['status'] === 'approved' ? 'bg-green-100 text-green-800' : ($change['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($change['status']) }}
                          </span>
                        </p>
                        <p class="text-xs text-gray-600 mt-1">{{ \Carbon\Carbon::parse($change['date_proposed'])->format('M d, Y g:i A') }}</p>
                      </div>
                      <div class="text-right">
                        <p class="text-xs text-gray-600">Previous</p>
                        <p class="text-sm font-semibold text-gray-900">₱{{ number_format($change['previous_budget'], 2) }}</p>
                      </div>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                      </svg>
                      <p class="text-sm font-bold {{ $change['change_type'] === 'increase' ? 'text-green-600' : ($change['change_type'] === 'decrease' ? 'text-red-600' : 'text-gray-900') }}">
                        ₱{{ number_format($change['updated_budget'], 2) }}
                      </p>
                    </div>
                    @if($change['reason'])
                      <p class="text-xs text-gray-700 bg-white rounded px-3 py-2 border border-purple-100">
                        <span class="font-semibold">Reason:</span> {{ $change['reason'] }}
                      </p>
                    @endif
                    @if($change['status'] === 'approved' && $change['date_approved'])
                      <p class="text-xs text-green-600 mt-2">
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
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200">
              <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <p class="text-sm text-gray-600">No budget changes recorded</p>
              <p class="text-xs text-gray-500 mt-1">Project completed with original budget</p>
            </div>
          @endif
        </div>

        <!-- Timeline Extensions -->
        <div>
          <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Timeline Extensions
          </h4>

          @php
            $timelineExtensions = $project->timeline_extensions ?? [];
          @endphp

          @if(count($timelineExtensions) > 0)
            <div class="space-y-3">
              @foreach($timelineExtensions as $extension)
                <div class="flex gap-4 p-4 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg border border-indigo-200">
                  <div class="flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                      <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </div>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-start justify-between mb-2">
                      <div>
                        <p class="text-sm font-bold text-gray-900">
                          Timeline Extension
                          <span class="inline-flex ml-2 px-2 py-0.5 text-xs font-semibold rounded-full {{ $extension['status'] === 'approved' ? 'bg-green-100 text-green-800' : ($extension['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($extension['status']) }}
                          </span>
                        </p>
                        <p class="text-xs text-gray-600 mt-1">Requested on {{ \Carbon\Carbon::parse($extension['date_proposed'])->format('M d, Y') }}</p>
                      </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-2">
                      <div>
                        <p class="text-xs text-gray-600">Previous End Date</p>
                        <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($extension['previous_end_date'])->format('M d, Y') }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-600">Proposed End Date</p>
                        <p class="text-sm font-semibold text-indigo-600">{{ \Carbon\Carbon::parse($extension['proposed_end_date'])->format('M d, Y') }}</p>
                      </div>
                    </div>
                    @if($extension['reason'])
                      <p class="text-xs text-gray-700 bg-white rounded px-3 py-2 border border-indigo-100">
                        <span class="font-semibold">Reason:</span> {{ $extension['reason'] }}
                      </p>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200">
              <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-sm text-gray-600">No timeline extensions recorded</p>
              <p class="text-xs text-gray-500 mt-1">Project completed within original timeline</p>
            </div>
          @endif
        </div>
      </div>
    </div>
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
              {{ \Carbon\Carbon::parse($project->timeline_start)->format('M d, Y') }} - 
              @if($hasExtension)
                <span class="block mt-1">
                  <span class="text-gray-400 line-through text-xs">{{ $originalEndDate->format('M d, Y') }}</span>
                  <span class="text-blue-600 font-bold ml-1">{{ $extendedEndDate->format('M d, Y') }}</span>
                  <span class="inline-block ml-1 px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded">+{{ max(1, (int) ceil($originalEndDate->diffInDays($extendedEndDate, false))) }}d</span>
                </span>
              @else
                {{ \Carbon\Carbon::parse($project->timeline_end)->format('M d, Y') }}
              @endif
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
          // Calculate CUMULATIVE percentages (sum from bottom to top)
          // Bottom milestone = its own %
          // Top milestone = 100% (sum of all)
          $itemsWithCumulative = [];
          $cumulative = 0;
          
          // First, calculate cumulative for each item
          foreach($project->milestone_items as $item) {
            $cumulative += ($item['percentage_progress'] ?? 0);
            $itemsWithCumulative[] = [
              'item' => $item,
              'cumulative' => $cumulative
            ];
          }
          
          // Reverse to show last milestone (100%) at top
          $reversedItems = array_reverse($itemsWithCumulative);
          $totalItems = count($reversedItems);
        @endphp
        @forelse($reversedItems as $index => $itemData)
          @php
            $item = $itemData['item'];
            $cumulative = round($itemData['cumulative']);
          @endphp
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
              <div class="border-2 border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-4 cursor-pointer hover:shadow-md transition-all" onclick="showMilestoneDetails({{ $itemData['item']['item_id'] }})">
                <div class="flex items-start justify-between mb-2">
                  <div class="flex-1">
                    <h4 class="text-base font-bold text-gray-900">{{ $itemData['item']['item_name'] }}</h4>
                    <!-- Extension and Payment Indicators -->
                    <div class="flex gap-2 mt-1">
                      @if($itemData['item']['was_extended'] ?? false)
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded bg-blue-100 text-blue-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Extended {{ $itemData['item']['extension_count'] ?? 1 }}x
                      </span>
                      @endif
                      @if(($itemData['item']['carry_forward_amount'] ?? 0) > 0)
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded bg-orange-100 text-orange-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        +₱{{ number_format($itemData['item']['carry_forward_amount'], 0) }}
                      </span>
                      @endif
                    </div>
                  </div>
                  <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                    COMPLETED
                  </span>
                </div>
                <!-- Date Display with Extension Info -->
                @if($itemData['item']['was_extended'] ?? false)
                  <div class="mb-2">
                    <p class="text-xs text-gray-400 line-through">Original: {{ $itemData['item']['original_date_to_finish'] ? \Carbon\Carbon::parse($itemData['item']['original_date_to_finish'])->format('M d, Y') : '—' }}</p>
                    <p class="text-xs text-blue-600 font-semibold uppercase tracking-wide">Extended: {{ \Carbon\Carbon::parse($itemData['item']['date_to_finish'])->format('M d, Y g:i A') }}</p>
                  </div>
                @else
                  <p class="text-xs text-gray-500 mb-2 uppercase tracking-wide">{{ \Carbon\Carbon::parse($itemData['item']['date_to_finish'])->format('M d, Y g:i A') }}</p>
                @endif
                @if($itemData['item']['item_description'])
                  <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $itemData['item']['item_description'] }}</p>
                @endif
                <button type="button" class="text-amber-600 hover:text-amber-700 text-sm font-semibold flex items-center gap-1">
                  View Details
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
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

            <!-- Date Display with Extension Info -->
            @if($item['was_extended'] ?? false)
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span class="text-xs font-semibold text-blue-700 uppercase">Date Extended {{ $item['extension_count'] ?? 1 }}x</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <p class="text-xs text-gray-600 mb-1">Original Date:</p>
                    <p class="text-sm font-semibold text-gray-700 line-through">{{ $item['original_date_to_finish'] ? \Carbon\Carbon::parse($item['original_date_to_finish'])->format('M d, Y') : '—' }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600 mb-1">Extended To:</p>
                    <p class="text-sm font-semibold text-blue-700">{{ \Carbon\Carbon::parse($item['date_to_finish'])->format('M d, Y g:i A') }}</p>
                  </div>
                </div>
                @if($item['original_date_to_finish'] && $item['date_to_finish'])
                  @php
                    $originalDate = \Carbon\Carbon::parse($item['original_date_to_finish']);
                    $extendedDate = \Carbon\Carbon::parse($item['date_to_finish']);
                    $daysDiff = max(1, (int) ceil($originalDate->diffInDays($extendedDate, false)));
                  @endphp
                  @if($originalDate->ne($extendedDate))
                    <p class="text-xs text-blue-600 mt-2">+{{ $daysDiff }} day{{ $daysDiff != 1 ? 's' : '' }} extension</p>
                  @endif
                @endif
              </div>
            @else
              <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                <p class="text-xs text-gray-600 mb-1">Completion Date:</p>
                <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($item['date_to_finish'])->format('M d, Y g:i A') }}</p>
              </div>
            @endif

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

  <!-- Change Audit Log (Collapsible) -->
  <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
    <button type="button" onclick="toggleChangeAuditLog()" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors duration-200">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div class="text-left">
          <h3 class="text-base font-bold text-gray-900">Change Audit Log</h3>
          <p class="text-xs text-gray-600">Complete chronological history of all project changes</p>
        </div>
      </div>
      <svg id="auditLogChevron" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <div id="auditLogContent" class="hidden border-t border-gray-200">
      <div class="p-6">
        @php
          $auditLog = $project->change_audit_log ?? [];
        @endphp

        @if(count($auditLog) > 0)
          <div class="relative">
            <!-- Vertical timeline line -->
            <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gradient-to-b from-amber-200 via-orange-200 to-amber-200"></div>

            <div class="space-y-6">
              @foreach($auditLog as $index => $log)
                <div class="relative flex gap-4">
                  <!-- Timeline dot -->
                  <div class="flex-shrink-0 w-10 h-10 rounded-full bg-white border-4 border-amber-400 flex items-center justify-center z-10 shadow-md">
                    <div class="w-3 h-3 rounded-full bg-gradient-to-br from-amber-500 to-orange-600"></div>
                  </div>

                  <!-- Event card -->
                  <div class="flex-1 bg-gradient-to-br from-white to-amber-50 rounded-lg border border-amber-200 p-4 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="flex items-start justify-between mb-2">
                      <div class="flex-1">
                        <p class="text-sm font-bold text-gray-900">{{ $log['action'] }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                          <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                          </svg>
                          {{ \Carbon\Carbon::parse($log['date'])->format('M d, Y g:i A') }}
                        </p>
                      </div>
                      @if($log['reference'])
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-amber-100 text-amber-800 border border-amber-300">
                          {{ $log['reference'] }}
                        </span>
                      @endif
                    </div>

                    @if($log['performed_by'])
                      <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <p class="text-xs text-gray-700">
                          <span class="font-semibold">Performed by:</span> {{ $log['performed_by'] }}
                        </p>
                      </div>
                    @endif

                    @if($log['notes'])
                      <div class="mt-2 p-3 bg-white rounded border border-amber-100">
                        <p class="text-xs text-gray-700">{{ $log['notes'] }}</p>
                      </div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @else
          <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-gray-600 font-semibold">No changes recorded</p>
            <p class="text-xs text-gray-500 mt-1">This project was completed without any modifications</p>
          </div>
        @endif
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
    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <!-- Payment Mode Indicator -->
      <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-200 hover:shadow-md transition-all duration-200 group">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-gray-600 font-medium">Payment Mode</p>
          <svg class="w-5 h-5 text-indigo-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <p class="text-base font-bold text-gray-900">{{ $paymentMode === 'staggered' ? 'Staggered' : 'Full Payment' }}</p>
        @if($paymentMode === 'staggered')
          <p class="text-xs text-indigo-600 mt-1">Milestone-based</p>
        @else
          <p class="text-xs text-gray-500 mt-1">Single payment</p>
        @endif
      </div>

      <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200 hover:shadow-md transition-all duration-200 group">
        <div class="flex items-center justify-between mb-2">
          <p class="text-xs text-gray-600 font-medium">Total Milestones Paid</p>
          <svg class="w-5 h-5 text-green-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-xl font-bold text-gray-900">{{ $project->total_milestones_paid }}/{{ $project->total_milestone_items ?? 0 }}</p>
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
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700">Payment Type</th>
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
              <td class="px-4 py-3">
                @php
                  $paymentType = $payment->payment_type ?? 'milestone';
                  $typeColors = [
                    'downpayment' => 'bg-purple-100 text-purple-800 border-purple-300',
                    'milestone' => 'bg-blue-100 text-blue-800 border-blue-300',
                    'final' => 'bg-green-100 text-green-800 border-green-300'
                  ];
                  $typeColor = $typeColors[$paymentType] ?? 'bg-gray-100 text-gray-800 border-gray-300';
                @endphp
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded border {{ $typeColor }}">
                  {{ ucfirst($paymentType) }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm font-semibold">{{ $payment->milestone_item_title ?? '—' }}</td>
              <td class="px-4 py-3 text-sm">{{ $payment->milestone_period ?? '—' }}</td>
              <td class="px-4 py-3 text-sm font-semibold">₱{{ number_format($payment->amount, 2) }}</td>
              <td class="px-4 py-3 text-sm">{{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('M d, Y') : '—' }}</td>
              <td class="px-4 py-3 text-sm">
                @if($payment->receipt_photo)
                  <a href="#" class="open-doc-btn text-blue-600 hover:text-blue-700 font-medium hover:underline" data-doc-src="{{ asset('storage/' . $payment->receipt_photo) }}" data-doc-title="Proof of Payment">View</a>
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
              <td colspan="7" class="px-4 py-8 text-center text-gray-500">No payment records available</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- View Full Payment History Button -->
    <button 
      onclick="openAdminPaymentHistoryModal({{ $project->project_id }}, '{{ addslashes($project->project_title) }}')"
      class="w-full px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <span>View Full Payment History</span>
    </button>
  </div>
</div>

<!-- Footer -->
<div class="border-t border-gray-200 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl flex justify-between items-center gap-3">
  <button type="button" onclick="showProjectSummaryModal({{ $project->project_id }})" class="px-5 py-2.5 text-sm font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-all duration-200 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    View Project Summary
  </button>
  <button type="button" onclick="hideCompletedProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    Close
  </button>
</div>
