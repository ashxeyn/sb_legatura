  <!-- Ongoing Project Modal -->
  <div id="ongoingProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300" data-project-id="{{ $project->project_id ?? '' }}">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl relative flex flex-col" style="max-height:90vh;">
        @isset($project)
        <!-- Header with Owner Info -->
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-3 rounded-t-xl flex-shrink-0">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center overflow-hidden ring-2 ring-white/30">
                @if($project->owner_profile_pic)
                  <img src="{{ asset('storage/' . $project->owner_profile_pic) }}" alt="Owner" class="w-full h-full object-cover">
                @else
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                @endif
              </div>
              <div class="text-white">
                <h3 class="text-sm font-bold">{{ $project->owner_name }}</h3>
                <p class="text-[10px] opacity-80 flex items-center gap-1">
                  <span class="inline-block w-1.5 h-1.5 bg-green-300 rounded-full animate-pulse"></span>
                  Ongoing Project
                </p>
              </div>
            </div>
            <button type="button" onclick="hideOngoingProjectModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <style>.ongoing-scroll::-webkit-scrollbar{display:none}</style>
        <div class="ongoing-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">
          <!-- Project Health Dashboard -->
          <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-2.5">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-indigo-600 flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                </div>
                <h3 class="text-xs font-bold text-gray-900">Project Health Dashboard</h3>
              </div>
              @php
                $originalBudget = $project->budget_range_min ?? 0;
                $currentBudget = $project->current_budget ?? $originalBudget;
                $totalPaid = $project->total_amount_paid ?? 0;
                $budgetUtilization = $currentBudget > 0 ? ($totalPaid / $currentBudget) * 100 : 0;
                
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
                
                $today = \Carbon\Carbon::now();
                $daysElapsed = $startDate ? (int) $startDate->diffInDays($today) : 0;
                $totalDays = $startDate && $endDate ? (int) $startDate->diffInDays($endDate) : 0;
                $daysRemaining = $endDate ? (int) $today->diffInDays($endDate, false) : 0;
                $timelineStatus = $daysRemaining < 0 ? 'behind' : ($daysRemaining < 7 ? 'critical' : 'on_track');
                
                $completedMilestones = $project->completed_milestones ?? 0;
                $totalMilestones = $project->total_milestone_items ?? 0;
                $completionPercentage = $totalMilestones > 0 ? ($completedMilestones / $totalMilestones) * 100 : 0;
                
                $paymentMode = $project->payment_mode ?? 'full_payment';
                $downpaymentCleared = $project->downpayment_cleared ?? false;
                $pendingPayments = $project->pending_payment_amount ?? 0;
              @endphp
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
              <!-- Budget Status -->
              <div class="bg-white rounded-md p-2 border border-indigo-100">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Budget</span>
                  <svg class="w-4 h-4 {{ $budgetUtilization > 90 ? 'text-red-500' : ($budgetUtilization > 70 ? 'text-yellow-500' : 'text-green-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-sm font-bold text-gray-900">{{ number_format($budgetUtilization, 1) }}%</p>
                <p class="text-[10px] text-gray-400 mt-0.5 truncate">₱{{ number_format($totalPaid, 0) }} / ₱{{ number_format($currentBudget, 0) }}</p>
                <div class="mt-1.5 w-full bg-gray-100 rounded-full h-1.5">
                  <svg class="block h-1.5 w-full" viewBox="0 0 100 6" preserveAspectRatio="none" aria-hidden="true">
                    <rect x="0" y="0" width="100" height="6" rx="3" class="fill-current text-gray-100"></rect>
                    <rect x="0" y="0" width="{{ min($budgetUtilization, 100) }}" height="6" rx="3" class="fill-current {{ $budgetUtilization > 90 ? 'text-red-500' : ($budgetUtilization > 70 ? 'text-yellow-500' : 'text-green-500') }}"></rect>
                  </svg>
                </div>
              </div>

              <!-- Timeline Status -->
              <div class="bg-white rounded-md p-2 border border-indigo-100">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Timeline</span>
                  <svg class="w-4 h-4 {{ $timelineStatus === 'behind' ? 'text-red-500' : ($timelineStatus === 'critical' ? 'text-yellow-500' : 'text-green-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-[11px] font-bold {{ $timelineStatus === 'behind' ? 'text-red-600' : ($timelineStatus === 'critical' ? 'text-yellow-600' : 'text-green-600') }}">
                  @if($timelineStatus === 'behind')
                    {{ abs($daysRemaining) }} days behind
                  @elseif($timelineStatus === 'critical')
                    {{ $daysRemaining }} days left
                  @else
                    On Track
                  @endif
                </p>
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $daysElapsed }} / {{ $totalDays }} days</p>
                <div class="mt-1.5 w-full bg-gray-100 rounded-full h-1.5">
                  <svg class="block h-1.5 w-full" viewBox="0 0 100 6" preserveAspectRatio="none" aria-hidden="true">
                    <rect x="0" y="0" width="100" height="6" rx="3" class="fill-current text-gray-100"></rect>
                    <rect x="0" y="0" width="{{ $totalDays > 0 ? min(($daysElapsed / $totalDays) * 100, 100) : 0 }}" height="6" rx="3" class="fill-current {{ $timelineStatus === 'behind' ? 'text-red-500' : ($timelineStatus === 'critical' ? 'text-yellow-500' : 'text-green-500') }}"></rect>
                  </svg>
                </div>
              </div>

              <!-- Milestone Progress -->
              <div class="bg-white rounded-md p-2 border border-indigo-100">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Milestones</span>
                  <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                </div>
                <p class="text-sm font-bold text-gray-900">{{ number_format($completionPercentage, 0) }}%</p>
                <p class="text-[10px] text-gray-400 mt-0.5">{{ $completedMilestones }}/{{ $totalMilestones }} done</p>
                <div class="mt-1.5 w-full bg-gray-100 rounded-full h-1.5">
                  <svg class="block h-1.5 w-full" viewBox="0 0 100 6" preserveAspectRatio="none" aria-hidden="true">
                    <rect x="0" y="0" width="100" height="6" rx="3" class="fill-current text-gray-100"></rect>
                    <rect x="0" y="0" width="{{ min($completionPercentage, 100) }}" height="6" rx="3" class="fill-current text-indigo-500"></rect>
                  </svg>
                </div>
              </div>

              <!-- Payment Status -->
              <div class="bg-white rounded-md p-2 border border-indigo-100">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Payment</span>
                  <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                  </svg>
                </div>
                <p class="text-[11px] font-bold text-gray-900">{{ $paymentMode === 'staggered' ? 'Staggered' : 'Full Payment' }}</p>
                @if($paymentMode === 'staggered')
                  <p class="text-[10px] {{ $downpaymentCleared ? 'text-green-600' : 'text-yellow-600' }} mt-0.5">
                    @if($downpaymentCleared)
                      <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                      </svg>
                      Downpayment Cleared
                    @else
                      Downpayment Pending
                    @endif
                  </p>
                @endif
                @if($pendingPayments > 0)
                  <p class="text-[10px] text-gray-400 mt-0.5">₱{{ number_format($pendingPayments, 0) }} pending</p>
                @endif
              </div>
            </div>
          </div>

          <!-- Pending Extension Requests (Collapsible) -->
          <div id="pendingExtensionsSection" class="hidden bg-white border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" onclick="togglePendingExtensions()" class="w-full px-4 py-2.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-indigo-600 flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <h3 class="text-xs font-bold text-gray-900">Pending Extension Requests (<span id="pendingExtensionsCount">0</span>)</h3>
              </div>
              <svg id="pendingExtensionsChevron" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <div id="pendingExtensionsContent" class="hidden border-t border-gray-200">
              <div id="pendingExtensionsContainer" class="p-3 space-y-2">
                <!-- Dynamically populated by JavaScript -->
              </div>
            </div>
          </div>

          <!-- Budget & Timeline Tracking (Collapsible) -->
          <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" onclick="toggleOngoingBudgetTracking()" class="w-full px-4 py-3.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-md bg-indigo-600 flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                </div>
                <h3 class="text-xs font-bold text-gray-900">Budget & Timeline Tracking</h3>
              </div>
              <svg id="ongoingBudgetChevron" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <div id="ongoingBudgetContent" class="hidden border-t border-gray-200">
              <div class="p-4 space-y-4">
                <!-- Budget Breakdown -->
                <div>
                  <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Budget Allocation
                  </h4>
                  <div class="grid md:grid-cols-3 gap-2.5">
                    <div class="bg-indigo-50 rounded p-3 border border-indigo-100">
                      <p class="text-[9px] text-indigo-400 mb-1">Original Budget</p>
                      <p class="text-sm font-bold text-gray-900">₱{{ number_format($originalBudget, 0) }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded p-3 border border-indigo-100">
                      <p class="text-[9px] text-indigo-400 mb-1">Current Budget</p>
                      <p class="text-sm font-bold text-indigo-600">₱{{ number_format($currentBudget, 0) }}</p>
                      @php $budgetChange = $currentBudget - $originalBudget; @endphp
                      @if($budgetChange != 0)
                        <p class="text-[10px] {{ $budgetChange > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                          {{ $budgetChange > 0 ? '+' : '' }}₱{{ number_format(abs($budgetChange), 0) }}
                        </p>
                      @endif
                    </div>
                    <div class="bg-indigo-50 rounded p-3 border border-indigo-100">
                      <p class="text-[9px] text-indigo-400 mb-1">Remaining Budget</p>
                      <p class="text-sm font-bold text-green-600">₱{{ number_format($currentBudget - $totalPaid, 0) }}</p>
                      <p class="text-[10px] text-gray-400 mt-1">{{ number_format(100 - $budgetUtilization, 1) }}% available</p>
                    </div>
                  </div>
                </div>

                <!-- Timeline Progress -->
                <div>
                  <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Timeline Progress
                  </h4>
                  <div class="bg-indigo-50 rounded p-3 border border-indigo-100">
                    <div class="grid md:grid-cols-3 gap-3 mb-3">
                      <div>
                        <p class="text-[10px] text-indigo-400 mb-1">Start Date</p>
                        <p class="text-sm font-bold text-gray-900">{{ $startDate ? $startDate->format('M d, Y') : '—' }}</p>
                      </div>
                      <div>
                        <p class="text-[10px] text-indigo-400 mb-1">End Date</p>
                        @if($hasExtension)
                          <div class="space-y-1">
                            <p class="text-[10px] text-gray-400 line-through">{{ $originalEndDate->format('M d, Y') }}</p>
                            <p class="text-sm font-bold text-indigo-600">{{ $extendedEndDate->format('M d, Y') }}</p>
                            <span class="inline-block px-2 py-0.5 bg-indigo-100 text-indigo-700 text-[9px] font-semibold rounded">
                              +{{ max(1, (int) ceil($originalEndDate->diffInDays($extendedEndDate, false))) }}d
                            </span>
                          </div>
                        @else
                          <p class="text-sm font-bold text-gray-900">{{ $endDate ? $endDate->format('M d, Y') : '—' }}</p>
                        @endif
                      </div>
                      <div>
                        <p class="text-[10px] text-indigo-400 mb-1">Days Remaining</p>
                        <p class="text-sm font-bold {{ $daysRemaining < 0 ? 'text-red-600' : 'text-green-600' }}">
                          {{ $daysRemaining < 0 ? abs($daysRemaining) . ' overdue' : $daysRemaining . ' left' }}
                        </p>
                      </div>
                    </div>
                    <div class="w-full bg-indigo-100 rounded-full h-2">
                      <svg class="block h-2 w-full" viewBox="0 0 100 8" preserveAspectRatio="none" aria-hidden="true">
                        <rect x="0" y="0" width="100" height="8" rx="4" class="fill-current text-indigo-100"></rect>
                        <rect x="0" y="0" width="{{ $totalDays > 0 ? min(($daysElapsed / $totalDays) * 100, 100) : 0 }}" height="8" rx="4" class="fill-current {{ $timelineStatus === 'behind' ? 'text-red-500' : ($timelineStatus === 'critical' ? 'text-yellow-500' : 'text-green-500') }}"></rect>
                      </svg>
                    </div>
                    <p class="text-[10px] text-indigo-400 mt-2 text-center font-medium">{{ $daysElapsed }} of {{ $totalDays }} days complete</p>
                    
                    <!-- Extend Timeline Button -->
                    <div class="mt-4 pt-3 border-t border-indigo-200">
                      <div class="grid grid-cols-2 gap-2.5">
                        <button type="button"
                          data-project-id="{{ $project->project_id }}"
                          data-end-date="{{ $endDate ? $endDate->format('Y-m-d') : '' }}"
                          data-start-date="{{ $startDate ? $startDate->format('Y-m-d') : '' }}"
                          onclick="showExtendTimelineModal(Number(this.dataset.projectId), this.dataset.endDate, this.dataset.startDate)"
                          class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-1.5"
                        >
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                          </svg>
                          <span>Extend Timeline</span>
                        </button>
                        <button type="button"
                          data-project-id="{{ $project->project_id }}"
                          onclick="showBulkAdjustDatesModal(Number(this.dataset.projectId))"
                          class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-1.5"
                        >
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                          </svg>
                          <span>Bulk Adjust</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Project Details and Contractor Details (Row) -->
          <div class="grid lg:grid-cols-2 gap-3">
            <!-- Project Details -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-sm border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Project Details
              </h3>
              <div class="space-y-0.5 text-[11px]">
                <div class="flex flex-col py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Project Title</span>
                  <span class="font-semibold text-gray-900">{{ $project->project_title ?? '—' }}</span>
                </div>
                <div class="flex flex-col py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Property Address</span>
                  <span class="font-semibold text-gray-900">{{ $project->project_location ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Type</span>
                  <span class="font-semibold text-gray-900">{{ $project->property_type ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Lot Size</span>
                  <span class="font-semibold text-gray-900">{{ $project->lot_size ?? '—' }} sqm</span>
                </div>
                <div class="flex flex-col py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Target Timeline</span>
                  <div class="font-semibold text-gray-900">
                    @if($project->timeline_start && $project->timeline_end)
                      {{ \Carbon\Carbon::parse($project->timeline_start)->format('M d, Y') }} - 
                      @if($hasExtension)
                        <span class="text-indigo-600">{{ $extendedEndDate->format('M d, Y') }}</span>
                        <span class="inline-block px-1 py-0.5 bg-indigo-100 text-indigo-700 text-[9px] font-semibold rounded ml-1">+{{ max(1, (int) ceil($originalEndDate->diffInDays($extendedEndDate, false))) }}d</span>
                      @else
                        {{ \Carbon\Carbon::parse($project->timeline_end)->format('M d, Y') }}
                      @endif
                    @else
                      —
                    @endif
                  </div>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Budget</span>
                  <span class="font-semibold text-indigo-600 text-right">₱{{ number_format($project->budget_range_min ?? 0, 0) }}</span>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Bid Deadline</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->bidding_due ? \Carbon\Carbon::parse($project->bidding_due)->format('M d, Y') : '—' }}</span>
                </div>
              </div>
            </div>

            <!-- Contractor Details -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-sm border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Contractor Details
              </h3>
              <div class="space-y-0.5 text-[11px]">
                <div class="flex flex-col py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Company Name</span>
                  <span class="font-semibold text-gray-900">{{ $project->contractor_name ?? '—' }}</span>
                </div>
                <div class="flex flex-col py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Email Address</span>
                  <span class="font-semibold text-indigo-600 break-all">{{ $project->contractor_email ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">PCAB No.</span>
                  <span class="font-semibold text-gray-900">{{ $project->contractor_pcab ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">PCAB Category</span>
                  <span class="font-semibold text-gray-900">{{ $project->contractor_category ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">PCAB Expiry</span>
                  <span class="font-semibold text-gray-900">{{ $project->contractor_pcab_expiry ? \Carbon\Carbon::parse($project->contractor_pcab_expiry)->format('M d, Y') : '—' }}</span>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Permit No.</span>
                  <span class="font-semibold text-gray-900">{{ $project->contractor_permit ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Permit City</span>
                  <span class="font-semibold text-gray-900">{{ $project->contractor_city ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">Permit Expiry</span>
                  <span class="font-semibold text-gray-900">{{ $project->contractor_permit_expiry ? \Carbon\Carbon::parse($project->contractor_permit_expiry)->format('M d, Y') : '—' }}</span>
                </div>
                <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-indigo-50">
                  <span class="text-gray-400 text-[10px]">TIN No.</span>
                  <span class="font-semibold text-gray-900">{{ $project->contractor_tin ?? '—' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Bidding Summary (Row) -->
          <div class="bg-white border border-gray-200 rounded-lg p-3 space-y-3">
            <h3 class="font-bold text-gray-900 text-sm border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              Bidding Summary
            </h3>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
              <div class="bg-indigo-50 rounded p-2.5 border border-indigo-100">
                <p class="text-[10px] text-indigo-400 mb-1 font-semibold">Total Bids</p>
                <span class="text-sm font-bold text-indigo-700">{{ $project->total_bids ?? 0 }}</span>
              </div>
              <div class="bg-gray-50 rounded p-2.5 border border-gray-100">
                <p class="text-[10px] text-gray-400 mb-1 font-semibold">Bidding Due</p>
                <span class="text-[10px] font-semibold text-gray-900">{{ $project->bidding_due ? \Carbon\Carbon::parse($project->bidding_due)->format('M d, Y') : '—' }}</span>
              </div>
              <div class="bg-indigo-50 rounded p-2.5 border border-indigo-100">
                <p class="text-[10px] text-indigo-400 mb-1 font-semibold">Winning Bidder</p>
                <span class="text-[10px] font-bold text-indigo-700 truncate block">{{ $project->winning_bidder ?? $project->contractor_name ?? '—' }}</span>
              </div>
              <div class="bg-gray-50 rounded p-2.5 border border-gray-100">
                <p class="text-[10px] text-gray-400 mb-1 font-semibold">Decision Date</p>
                <span class="text-[10px] font-semibold text-gray-900">{{ $project->decision_date ? \Carbon\Carbon::parse($project->decision_date)->format('M d, Y') : ($project->submitted_at ? \Carbon\Carbon::parse($project->submitted_at)->format('M d, Y') : '—') }}</span>
              </div>
              <div class="bg-indigo-50 rounded p-2.5 border border-indigo-100">
                <p class="text-[10px] text-indigo-400 mb-1 font-semibold">Proposed Cost</p>
                <span class="text-[10px] font-semibold text-indigo-700">₱{{ $project->proposed_cost ? number_format($project->proposed_cost, 0) : ($project->budget_range_min ? number_format($project->budget_range_min, 0) : '0') }}</span>
              </div>
              <div class="bg-gray-50 rounded p-2.5 border border-gray-100">
                <p class="text-[10px] text-gray-400 mb-1 font-semibold">Project ID</p>
                <span class="text-[10px] font-semibold text-gray-600">#{{ $project->project_id ?? '—' }}</span>
              </div>
            </div>
          </div>

          <!-- Project's Milestone and Details (Row) -->
          <div class="grid lg:grid-cols-2 gap-3">
            <!-- Project's Milestone -->
            <div class="bg-white border border-gray-200 rounded-lg p-3 space-y-3">
              <h3 class="font-bold text-gray-900 text-sm border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    $cumulative += ($item->percentage_progress ?? 0);
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
                  <div class="flex items-start gap-2">
                    <!-- Timeline left side -->
                    <div class="flex flex-col items-center">
                      <!-- Percentage badge -->
                      <div class="flex-shrink-0 w-8 h-8 rounded-full bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-700 font-bold text-[9px]">
                        {{ $cumulative }}%
                      </div>
                      <!-- Vertical line and status indicator -->
                      @if($index < $totalItems - 1)
                        <div class="relative flex-1 w-0.5 bg-gray-200 my-1" style="min-height: 36px;">
                          @if($item->item_status === 'completed')
                            <div class="absolute left-1/2 -translate-x-1/2 -bottom-2 w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                              <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                              </svg>
                            </div>
                          @elseif($item->item_status === 'in_progress')
                            <div class="absolute left-1/2 -translate-x-1/2 -bottom-2 w-5 h-5 rounded-full bg-indigo-400 flex items-center justify-center">
                              <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                              </svg>
                            </div>
                          @else
                            <div class="absolute left-1/2 -translate-x-1/2 -bottom-2 w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center">
                              <div class="w-2 h-2 rounded-full bg-white"></div>
                            </div>
                          @endif
                        </div>
                      @endif
                    </div>

                    <!-- Milestone card -->
                    <div class="flex-1 mb-2">
                      <div class="border border-indigo-200 bg-indigo-50 rounded-lg p-2.5 cursor-pointer hover:border-indigo-400 hover:bg-indigo-100 transition-all" data-item-id="{{ $item->item_id }}" onclick="showOngoingMilestoneDetails(Number(this.dataset.itemId)); document.getElementById('ongoingDetailsPlaceholder').style.display='none';">
                        <div class="flex items-start justify-between mb-1.5">
                          <div class="flex-1">
                            <h4 class="text-xs font-bold text-gray-900">{{ $item->item_title }}</h4>
                            <!-- Extension and Payment Indicators -->
                            <div class="flex gap-1.5 mt-0.5">
                              @if($item->was_extended ?? false)
                              <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[9px] font-medium rounded bg-indigo-100 text-indigo-700">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                +{{ $item->extension_count ?? 1 }}x
                              </span>
                              @endif
                              @if(($item->carry_forward_amount ?? 0) > 0)
                              <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[9px] font-medium rounded bg-amber-100 text-amber-700">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                +₱{{ number_format($item->carry_forward_amount, 0) }}
                              </span>
                              @endif
                            </div>
                          </div>
                          <span class="inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded
                            {{ $item->item_status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $item->item_status === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : '' }}
                            {{ $item->item_status === 'delayed' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $item->item_status === 'not_started' ? 'bg-gray-100 text-gray-600' : '' }}">
                            {{ strtoupper(str_replace('_', ' ', $item->item_status)) }}
                          </span>
                        </div>
                        <!-- Date Display with Extension Info -->
                        @if($item->was_extended ?? false)
                          <p class="text-[10px] text-gray-400 line-through">{{ $item->original_date_to_finish ? \Carbon\Carbon::parse($item->original_date_to_finish)->format('M d, Y') : '—' }}</p>
                          <p class="text-[10px] text-indigo-600 font-semibold">→ {{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y') : '—' }}</p>
                        @else
                          <p class="text-[10px] text-gray-400">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y') : '—' }}</p>
                        @endif
                        @if($item->item_description)
                          <p class="text-[10px] text-gray-500 line-clamp-1 mt-0.5">{{ $item->item_description }}</p>
                        @endif
                        <button type="button" class="text-indigo-600 hover:text-indigo-700 text-[10px] font-semibold flex items-center gap-1">
                          View Details
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="bg-white border border-gray-200 rounded-lg p-3 space-y-3">
              <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Details
                </h3>
                <button id="editOngoingMilestoneBtn" onclick="openEditMilestoneModal(window.selectedMilestoneItemId)" class="text-indigo-600 hover:text-indigo-700 p-1.5 rounded-md hover:bg-indigo-50 transition-colors hidden">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                </button>
              </div>
              <div id="ongoingDetailsContent" class="space-y-3">
                <div id="ongoingDetailsPlaceholder" class="text-xs text-gray-400 text-center py-6">Select a milestone to view details</div>

                <!-- Hidden divs for each milestone item detail -->
                @foreach($project->milestone_items as $item)
                  <div id="ongoing-milestone-detail-{{ $item->item_id }}" class="hidden space-y-2">
                    <!-- Milestone header -->
                    <div class="flex items-center justify-between gap-2">
                      <h4 class="text-xs font-bold text-gray-900">{{ $item->item_title }}</h4>
                      <span class="flex-shrink-0 inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded
                        {{ $item->item_status === 'completed' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $item->item_status === 'in_progress' ? 'bg-indigo-100 text-indigo-700' : '' }}
                        {{ $item->item_status === 'delayed' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $item->item_status === 'not_started' ? 'bg-gray-100 text-gray-600' : '' }}">
                        {{ strtoupper(str_replace('_', ' ', $item->item_status)) }}
                      </span>
                    </div>

                    <!-- Date Display with Extension Info -->
                    @if($item->was_extended ?? false)
                      <div class="bg-indigo-50 border border-indigo-200 rounded-md p-2">
                        <p class="text-[10px] font-semibold text-indigo-700 mb-1">Extended {{ $item->extension_count ?? 1 }}x</p>
                        <div class="flex gap-3">
                          <div>
                            <p class="text-[9px] text-gray-400">Original</p>
                            <p class="text-[10px] font-semibold text-gray-500 line-through">{{ $item->original_date_to_finish ? \Carbon\Carbon::parse($item->original_date_to_finish)->format('M d, Y') : '—' }}</p>
                          </div>
                          <div>
                            <p class="text-[9px] text-gray-400">Extended To</p>
                            <p class="text-[10px] font-semibold text-indigo-700">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y') : '—' }}</p>
                          </div>
                        </div>
                        @if($item->original_date_to_finish && $item->date_to_finish)
                          @php
                            $originalDate = \Carbon\Carbon::parse($item->original_date_to_finish);
                            $extendedDate = \Carbon\Carbon::parse($item->date_to_finish);
                            $daysDiff = max(1, (int) ceil($originalDate->diffInDays($extendedDate, false)));
                          @endphp
                          @if($originalDate->ne($extendedDate))
                            <p class="text-[10px] text-indigo-600 mt-1">+{{ $daysDiff }} day{{ $daysDiff != 1 ? 's' : '' }}</p>
                          @endif
                        @endif
                      </div>
                    @else
                      <div class="bg-gray-50 border border-gray-200 rounded-md p-2">
                        <p class="text-[9px] text-gray-400">Due Date</p>
                        <p class="text-[10px] font-semibold text-gray-900">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y g:i A') : '—' }}</p>
                      </div>
                    @endif

                    <!-- Description -->
                    <p class="text-[10px] text-gray-500 leading-relaxed">{{ $item->item_description ?? 'No description' }}</p>

                    <!-- List of Reports Section -->
                    <div class="pt-2 border-t border-gray-100">
                      <h5 class="text-[10px] font-bold text-gray-700 mb-2 uppercase tracking-wide">Reports</h5>
                      @if(count($item->progress) > 0)
                        <div class="space-y-1.5">
                          @foreach($item->progress as $prog)
                            <div class="p-2 bg-gray-50 border border-gray-200 rounded-md">
                              <div class="flex items-start justify-between gap-1">
                                <p class="text-[10px] font-semibold text-gray-900">{{ $prog['purpose'] }}</p>
                                <span class="flex-shrink-0 inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded
                                  {{ $prog['status'] === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                                  {{ $prog['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                  {{ $prog['status'] === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                                  {{ strtoupper($prog['status']) }}
                                </span>
                              </div>
                              <p class="text-[9px] text-gray-400 mt-0.5">{{ $prog['submitted_at'] ? \Carbon\Carbon::parse($prog['submitted_at'])->format('d M Y, g:i A') : '—' }}</p>

                              @if(count($prog['files']) > 0)
                                <div class="mt-1.5 space-y-1">
                                  @foreach($prog['files'] as $file)
                                    <a href="{{ asset('storage/' . $file['file_path']) }}" target="_blank" class="flex items-center gap-1.5 px-2 py-1 bg-indigo-50 border border-indigo-200 rounded hover:bg-indigo-100 transition-colors">
                                      <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                      </svg>
                                      <span class="text-[10px] text-indigo-700 truncate">{{ $file['original_name'] }}</span>
                                    </a>
                                  @endforeach
                                </div>
                              @endif
                            </div>
                          @endforeach
                        </div>
                      @else
                        <p class="text-[10px] text-gray-400">No reports available</p>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <!-- Payment Summary (Row) -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-2">
            <h3 class="text-[11px] font-bold text-gray-900 border-b border-gray-200 pb-1 flex items-center gap-1">
              <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Payment Summary
            </h3>

            <!-- Stats grid -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-1">
              <div class="bg-indigo-50 rounded p-1.5 border border-indigo-100 flex flex-col items-center text-center">
                <svg class="w-3 h-3 text-indigo-600 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-[8px] text-indigo-400 mb-0.5">Mode</p>
                <p class="text-[10px] font-bold text-indigo-700">{{ ($project->payment_mode ?? 'full_payment') === 'staggered' ? 'Staggered' : 'Full' }}</p>
              </div>
              <div class="bg-green-50 rounded p-1.5 border border-green-100 flex flex-col items-center text-center">
                <svg class="w-3 h-3 text-green-600 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-[8px] text-green-400 mb-0.5">Paid</p>
                <p class="text-[10px] font-bold text-green-700">{{ $project->approved_payments_count ?? 0 }}/{{ $project->total_milestone_items ?? 0 }}</p>
              </div>
              <div class="bg-blue-50 rounded p-1.5 border border-blue-100 flex flex-col items-center text-center">
                <svg class="w-3 h-3 text-blue-600 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-[8px] text-blue-400 mb-0.5">Amount</p>
                <p class="text-[10px] font-bold text-blue-700">₱{{ number_format(($project->total_amount_paid ?? 0) / 1000, 0) }}k</p>
              </div>
              <div class="bg-amber-50 rounded p-1.5 border border-amber-100 flex flex-col items-center text-center">
                <svg class="w-3 h-3 text-amber-600 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-[8px] text-amber-400 mb-0.5">Last</p>
                <p class="text-[10px] font-bold text-amber-700">{{ $project->last_payment_date ? \Carbon\Carbon::parse($project->last_payment_date)->format('M d') : '—' }}</p>
              </div>
              <div class="{{ $project->approved_payments_count > 0 ? 'bg-emerald-50 border-emerald-100' : 'bg-gray-50 border-gray-100' }} rounded p-1.5 border flex flex-col items-center text-center">
                <svg class="w-3 h-3 {{ $project->approved_payments_count > 0 ? 'text-emerald-600' : 'text-gray-400' }} mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <p class="text-[8px] {{ $project->approved_payments_count > 0 ? 'text-emerald-400' : 'text-gray-400' }} mb-0.5">Status</p>
                <p class="text-[10px] font-bold {{ $project->approved_payments_count > 0 ? 'text-emerald-700' : 'text-gray-500' }}">{{ $project->approved_payments_count > 0 ? 'Received' : 'Pending' }}</p>
              </div>
            </div>

            <!-- Payments table -->
            <div class="rounded-lg border border-gray-200 overflow-hidden bg-gray-50">
              <table class="w-full text-[10px]">
                <thead class="bg-indigo-50 border-b border-indigo-100 sticky top-0">
                  <tr>
                    <th class="text-left px-2 py-1 font-semibold text-indigo-700">Type</th>
                    <th class="text-left px-2 py-1 font-semibold text-indigo-700">Milestone</th>
                    <th class="text-left px-2 py-1 font-semibold text-indigo-700">Period</th>
                    <th class="text-left px-2 py-1 font-semibold text-indigo-700">Amount</th>
                    <th class="text-left px-2 py-1 font-semibold text-indigo-700">Date</th>
                    <th class="text-left px-2 py-1 font-semibold text-indigo-700">Proof</th>
                    <th class="text-left px-2 py-1 font-semibold text-indigo-700">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  @forelse($project->payments as $payment)
                  <tr class="hover:bg-indigo-50 transition-colors">
                    <td class="px-2 py-1">
                      @php
                        $paymentType = $payment->payment_type ?? 'milestone';
                        $typeColors = [
                          'downpayment' => 'bg-purple-100 text-purple-700 border-purple-200',
                          'milestone' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                          'final' => 'bg-green-100 text-green-700 border-green-200'
                        ];
                        $typeColor = $typeColors[$paymentType] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                      @endphp
                      <span class="inline-flex px-1 py-0.5 text-[8px] font-semibold rounded border {{ $typeColor }}">
                        {{ ucfirst($paymentType) }}
                      </span>
                    </td>
                    <td class="px-2 py-1 text-gray-900 font-medium truncate">{{ $payment->item_title }}</td>
                    <td class="px-2 py-1 text-gray-500 truncate">{{ $payment->milestone_period ?? '—' }}</td>
                    <td class="px-2 py-1 font-semibold text-indigo-600">₱{{ number_format($payment->amount ?? 0, 0) }}</td>
                    <td class="px-2 py-1 text-gray-500 whitespace-nowrap">{{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('M d') : '—' }}</td>
                    <td class="px-2 py-1">
                      @if($payment->proof_attachment)
                      <a href="{{ asset('storage/' . $payment->proof_attachment) }}" target="_blank" class="inline-flex items-center gap-0.5 px-1.5 py-0.5 bg-indigo-50 border border-indigo-200 rounded text-indigo-700 hover:bg-indigo-100 transition-colors">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span class="text-[8px] font-medium">View</span>
                      </a>
                      @else
                      <span class="text-[8px] text-gray-300">—</span>
                      @endif
                    </td>
                    <td class="px-2 py-1">
                      @php
                        $statusClass = $payment->payment_status === 'approved'
                          ? 'bg-green-100 text-green-700'
                          : ($payment->payment_status === 'pending'
                            ? 'bg-yellow-100 text-yellow-700'
                            : 'bg-gray-100 text-gray-600');
                      @endphp
                      <span class="inline-flex px-1 py-0.5 rounded text-[8px] font-semibold {{ $statusClass }}">
                        {{ ucfirst($payment->payment_status ?? 'Unknown') }}
                      </span>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="7" class="px-4 py-3 text-center text-gray-400 text-[10px]">No payment records found</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <!-- View Full Payment History Button -->
            <button 
              type="button"
              data-project-id="{{ $project->project_id }}"
              data-project-title="{{ $project->project_title }}"
              onclick="openAdminPaymentHistoryModal(Number(this.dataset.projectId), this.dataset.projectTitle)"
              class="w-full px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-semibold rounded-lg transition-colors flex items-center justify-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              <span>View History</span>
            </button>
          </div>

        </div>

        <!-- Footer (Fixed at Bottom) -->
        <div class="border-t border-gray-200 px-4 py-3 bg-white flex justify-between items-center gap-3 flex-shrink-0 z-40 rounded-b-xl w-full">
          <div class="flex items-center gap-2 pl-4">
            <button type="button" data-project-id="{{ $project->project_id }}" onclick="showProjectSummaryModal(Number(this.dataset.projectId))" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-all hover:shadow-md flex items-center gap-1.5 whitespace-nowrap">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              Summary
            </button>
            <button type="button" data-project-id="{{ $project->project_id }}" onclick="showHaltProjectModal(Number(this.dataset.projectId))" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition-all hover:shadow-md flex items-center gap-1.5 whitespace-nowrap">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Halt Project
            </button>
          </div>
          <button type="button" onclick="hideOngoingProjectModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 transition-all hover:shadow-md flex items-center gap-1.5 whitespace-nowrap pr-4">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </button>
        </div>

        @else
        <!-- Empty state for initial page load -->
        <div class="p-6 text-center text-gray-400 text-sm">
          <p>Loading project details...</p>
        </div>
        @endisset
      </div>
    </div>
  </div>
