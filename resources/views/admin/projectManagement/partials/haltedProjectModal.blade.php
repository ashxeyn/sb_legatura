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
          <button type="button" onclick="hideHaltedProjectModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
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
            <button type="button" onclick="showHaltDetailsModal({{ $project->project_id }})" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white font-semibold text-sm rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              View Halt Details
            </button>
          </div>

          <!-- Halt Impact Analysis -->
          <div class="bg-gradient-to-br from-white via-rose-50 to-red-50 border-2 border-rose-300 rounded-xl p-6 shadow-lg">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-bold text-gray-900">Halt Impact Analysis</h3>
                <p class="text-xs text-gray-600">Assessment of project disruption and financial impact</p>
              </div>
            </div>

            @php
              $haltedDate = $project->halted_at ? \Carbon\Carbon::parse($project->halted_at) : null;
              $today = \Carbon\Carbon::now();
              $haltDuration = $haltedDate ? (int) $haltedDate->diffInDays($today) : 0;
              
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
              
              $affectedMilestones = (int) ($project->affected_milestones ?? 0);
              $totalMilestones = (int) ($project->total_milestone_items ?? 0);
              
              $financialImpact = (float) ($project->financial_impact ?? 0);
              $currentBudget = (float) ($project->current_budget ?? $project->budget_range_min ?? 0);
              $impactPercentage = $currentBudget > 0 ? round(($financialImpact / $currentBudget) * 100, 1) : 0;
              
              $timelineImpact = (int) ($project->timeline_impact_days ?? 0);
              $paymentsAffected = (int) ($project->payments_affected ?? 0);
            @endphp

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Halt Duration -->
              <div class="bg-white rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Duration Halted</span>
                  <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-xl font-bold text-rose-600">{{ $haltDuration }} days</p>
                <p class="text-xs text-gray-600 mt-1">Since {{ $haltedDate ? $haltedDate->format('M d, Y') : '—' }}</p>
              </div>

              <!-- Affected Milestones -->
              <div class="bg-white rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Affected Milestones</span>
                  <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                </div>
                <p class="text-xl font-bold text-gray-900">{{ $affectedMilestones }}/{{ $totalMilestones }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ $totalMilestones > 0 ? number_format(($affectedMilestones / $totalMilestones) * 100, 0) : 0 }}% of total</p>
              </div>

              <!-- Financial Impact -->
              <div class="bg-white rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Financial Impact</span>
                  <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p class="text-xl font-bold text-rose-600">₱{{ number_format($financialImpact, 2) }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ number_format($impactPercentage, 1) }}% of budget</p>
              </div>

              <!-- Timeline Impact -->
              <div class="bg-white rounded-lg p-4 border border-rose-200 hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Timeline Impact</span>
                  <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <p class="text-xl font-bold text-gray-900">+{{ $timelineImpact }} days</p>
                <p class="text-xs text-gray-600 mt-1">Extension needed</p>
              </div>
            </div>

            <!-- Payments Affected -->
            @if($paymentsAffected > 0)
              <div class="mt-4 bg-white rounded-lg p-4 border border-rose-200">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                  </svg>
                  <p class="text-sm font-bold text-gray-900">{{ $paymentsAffected }} pending payment(s) affected by halt</p>
                </div>
                <p class="text-xs text-gray-600">All milestone payments are frozen until project resumes</p>
              </div>
            @endif
          </div>

          <!-- Timeline Progress Card -->
          <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm p-6">
            <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              Timeline Progress
            </h3>
            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-4 border border-indigo-200">
              <div class="grid md:grid-cols-3 gap-4 mb-4">
                <div>
                  <p class="text-xs text-gray-600 font-semibold mb-1">Start Date</p>
                  <p class="text-sm font-bold text-gray-900">
                    {{ $project->timeline_start ? \Carbon\Carbon::parse($project->timeline_start)->format('M d, Y') : '—' }}
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-600 font-semibold mb-1">End Date</p>
                  @if($hasExtension)
                    <!-- Show original and extended dates from project_updates -->
                    <div class="space-y-1">
                      <p class="text-xs text-gray-500 line-through">Original: {{ $originalEndDate->format('M d, Y') }}</p>
                      <p class="text-sm font-bold text-blue-600">Extended: {{ $extendedEndDate->format('M d, Y') }}</p>
                      <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded">
                        +{{ max(1, (int) ceil($originalEndDate->diffInDays($extendedEndDate, false))) }} days
                      </span>
                    </div>
                  @else
                    <!-- Show normal end date -->
                    <p class="text-sm font-bold text-gray-900">
                      {{ $project->timeline_end ? \Carbon\Carbon::parse($project->timeline_end)->format('M d, Y') : '—' }}
                    </p>
                  @endif
                </div>
                <div>
                  <p class="text-xs text-gray-600 font-semibold mb-1">Days Halted</p>
                  <p class="text-sm font-bold text-rose-600">{{ $haltDuration }} days</p>
                  <p class="text-xs text-gray-600 mt-1">Since {{ $haltedDate ? $haltedDate->format('M d, Y') : '—' }}</p>
                </div>
              </div>
              
              <!-- Action Buttons -->
              <div class="pt-4 border-t border-indigo-200">
                <div class="grid grid-cols-2 gap-3">
                  <button type="button"
                    onclick="showExtendTimelineModal({{ $project->project_id }}, '{{ $project->timeline_end ?? '' }}', '{{ $project->timeline_start ?? '' }}')"
                    class="px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>Extend Timeline</span>
                  </button>
                  <button type="button"
                    onclick="showBulkAdjustDatesModal({{ $project->project_id }})"
                    class="px-4 py-2.5 bg-gradient-to-r from-purple-500 to-pink-600 text-white font-semibold rounded-lg hover:from-purple-600 hover:to-pink-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Bulk Adjust Dates</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Extension Requests (Collapsible) -->
          <div id="pendingExtensionsSection" class="hidden bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <button type="button" onclick="togglePendingExtensions()" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors duration-200">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="text-left">
                  <h3 class="text-base font-bold text-gray-900">Pending Extension Requests (<span id="pendingExtensionsCount">0</span>)</h3>
                  <p class="text-xs text-gray-600">Review and manage timeline extension requests</p>
                </div>
              </div>
              <svg id="pendingExtensionsChevron" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <div id="pendingExtensionsContent" class="hidden border-t border-gray-200">
              <div id="pendingExtensionsContainer" class="p-6 space-y-4">
                <!-- Dynamically populated by JavaScript -->
              </div>
            </div>
          </div>

          <!-- Pre-Halt vs Current Status (Collapsible) -->
          <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <button type="button" onclick="toggleHaltComparison()" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors duration-200">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                  </svg>
                </div>
                <div class="text-left">
                  <h3 class="text-base font-bold text-gray-900">Pre-Halt vs Current Status</h3>
                  <p class="text-xs text-gray-600">Compare project status before and after halt</p>
                </div>
              </div>
              <svg id="haltComparisonChevron" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <div id="haltComparisonContent" class="hidden border-t border-gray-200">
              <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6">
                  <!-- Before Halt -->
                  <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 border border-green-200">
                    <div class="flex items-center gap-2 mb-4">
                      <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      <h4 class="text-sm font-bold text-gray-900">Before Halt</h4>
                    </div>
                    <div class="space-y-3 text-sm">
                      <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-semibold text-green-600">{{ $project->pre_halt_status ?? 'In Progress' }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Completed Milestones:</span>
                        <span class="font-semibold text-gray-900">{{ $project->pre_halt_completed ?? 0 }}/{{ $totalMilestones }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Budget Utilized:</span>
                        <span class="font-semibold text-gray-900">{{ number_format($project->pre_halt_budget_used ?? 0, 1) }}%</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Timeline Progress:</span>
                        <span class="font-semibold text-gray-900">{{ number_format($project->pre_halt_timeline_progress ?? 0, 0) }}%</span>
                      </div>
                    </div>
                  </div>

                  <!-- After Halt (Current) -->
                  <div class="bg-gradient-to-br from-rose-50 to-red-50 rounded-lg p-5 border border-rose-200">
                    <div class="flex items-center gap-2 mb-4">
                      <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      <h4 class="text-sm font-bold text-gray-900">Current (Halted)</h4>
                    </div>
                    <div class="space-y-3 text-sm">
                      <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-semibold text-rose-600">Halted</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Completed Milestones:</span>
                        <span class="font-semibold text-gray-900">{{ $project->pre_halt_completed ?? 0 }}/{{ $totalMilestones }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Budget Utilized:</span>
                        <span class="font-semibold text-gray-900">{{ number_format($project->pre_halt_budget_used ?? 0, 1) }}%</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Days Halted:</span>
                        <span class="font-semibold text-rose-600">{{ $haltDuration }} days</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Impact Summary -->
                <div class="mt-6 bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg p-4 border border-amber-200">
                  <h5 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Impact Summary
                  </h5>
                  <ul class="text-xs text-gray-700 space-y-2">
                    <li class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                      </svg>
                      <span>All construction activities suspended</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                      </svg>
                      <span>Milestone payments frozen until resolution</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                      </svg>
                      <span>Timeline extension required upon resumption</span>
                    </li>
                    <li class="flex items-start gap-2">
                      <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                      </svg>
                      <span>Contractor work authorization suspended</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
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
                      {{ \Carbon\Carbon::parse($project->timeline_start)->format('M j, Y') }} - 
                      @if($hasExtension)
                        <span class="block mt-1">
                          <span class="text-gray-400 line-through text-xs">{{ $originalEndDate->format('M j, Y') }}</span>
                          <span class="text-blue-600 font-bold ml-1">{{ $extendedEndDate->format('M j, Y') }}</span>
                          <span class="inline-block ml-1 px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs font-semibold rounded">+{{ max(1, (int) ceil($originalEndDate->diffInDays($extendedEndDate, false))) }}d</span>
                        </span>
                      @else
                        {{ \Carbon\Carbon::parse($project->timeline_end)->format('M j, Y') }}
                      @endif
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
                @foreach($reversedItems as $index => $itemData)
                  @php
                    $item = $itemData['item'];
                    $cumulative = round($itemData['cumulative']);
                    
                    // Determine actual status to display
                    // If project is halted and milestone is not completed, show as HALTED
                    $isCompleted = $item->item_status === 'completed';
                    $displayStatus = $isCompleted ? 'completed' : 'halt';
                    
                    $statusColors = [
                      'completed' => 'bg-green-100 text-green-700',
                      'halt' => 'bg-rose-100 text-rose-700',
                    ];
                    $statusBadge = $statusColors[$displayStatus];
                    $isHalted = !$isCompleted;
                  @endphp

                  <div class="flex items-start gap-4">
                    <!-- Timeline left side -->
                    <div class="flex flex-col items-center">
                      <!-- Percentage badge -->
                      <div class="flex-shrink-0 w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-gray-700 font-bold text-xs">
                        {{ $cumulative }}%
                      </div>
                      <!-- Vertical line and status indicator -->
                      @if($index < $totalItems - 1)
                        <div class="relative flex-1 w-0.5 bg-gray-200 my-2" style="min-height: 60px;">
                          @if($isCompleted)
                            <!-- Green checkmark for completed -->
                            <div class="absolute left-1/2 -translate-x-1/2 -bottom-3 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                              </svg>
                            </div>
                          @else
                            <!-- Rose pause icon for halted -->
                            <div class="absolute left-1/2 -translate-x-1/2 -bottom-3 w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center">
                              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                              </svg>
                            </div>
                          @endif
                        </div>
                      @endif
                    </div>

                    <!-- Milestone card -->
                    <div class="flex-1 mb-6">
                      <div class="border-2 border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-4 cursor-pointer hover:shadow-md transition-all" onclick="showHaltedMilestoneDetail({{ $item->item_id }})">
                        <div class="flex items-start justify-between mb-2">
                          <div class="flex-1">
                            <h4 class="text-base font-bold text-gray-900">{{ $item->milestone_item_title }}</h4>
                            <!-- Extension and Payment Indicators -->
                            <div class="flex gap-2 mt-1">
                              @if($item->was_extended ?? false)
                              <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded bg-blue-100 text-blue-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Extended {{ $item->extension_count ?? 1 }}x
                              </span>
                              @endif
                              @if(($item->carry_forward_amount ?? 0) > 0)
                              <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded bg-orange-100 text-orange-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                +₱{{ number_format($item->carry_forward_amount, 0) }}
                              </span>
                              @endif
                            </div>
                          </div>
                          <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $statusBadge }} uppercase">
                            {{ $displayStatus === 'halt' ? 'HALTED' : 'COMPLETED' }}
                          </span>
                        </div>
                        <!-- Date Display with Extension Info -->
                        @if($item->was_extended ?? false)
                          <div class="mb-2">
                            <p class="text-xs text-gray-400 line-through">Original: {{ $item->original_date_to_finish ? \Carbon\Carbon::parse($item->original_date_to_finish)->format('M d, Y') : '—' }}</p>
                            <p class="text-xs text-blue-600 font-semibold uppercase tracking-wide">Extended: {{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y g:i A') : '—' }}</p>
                          </div>
                        @else
                          <p class="text-xs text-gray-500 mb-2 uppercase tracking-wide">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y g:i A') : '—' }}</p>
                        @endif
                        @if($item->milestone_item_description)
                          <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $item->milestone_item_description }}</p>
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
                <button id="editHaltedMilestoneBtn" onclick="openEditMilestoneModal(window.selectedMilestoneItemId)" class="text-amber-600 hover:text-amber-700 p-2 rounded-lg hover:bg-amber-50 transition-colors hidden">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                </button>
              </div>
              <div id="haltedDetailsContent">
                <div class="text-sm text-gray-500 text-center py-8">Select a milestone to view details</div>

                @foreach($project->milestone_items as $item)
                  <div id="halted-milestone-detail-{{ $item->item_id }}" class="space-y-4 hidden">
                    <!-- Milestone header -->
                    <div class="flex items-center justify-between">
                      <h4 class="text-lg font-bold text-gray-900">{{ $item->milestone_item_title }}</h4>
                      @php
                        $isItemCompleted = $item->item_status === 'completed';
                        $itemDisplayStatus = $isItemCompleted ? 'completed' : 'halt';
                        $statusColors = [
                          'completed' => 'bg-green-100 text-green-700 border border-green-200',
                          'halt' => 'bg-rose-100 text-rose-700 border border-rose-200',
                        ];
                        $statusBadge = $statusColors[$itemDisplayStatus];
                      @endphp
                      <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $statusBadge }} uppercase">
                        {{ $itemDisplayStatus === 'halt' ? 'HALTED' : 'COMPLETED' }}
                      </span>
                    </div>

                    <!-- Date Display with Extension Info -->
                    @if($item->was_extended ?? false)
                      <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex items-center gap-2 mb-2">
                          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                          </svg>
                          <span class="text-xs font-semibold text-blue-700 uppercase">Date Extended {{ $item->extension_count ?? 1 }}x</span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                          <div>
                            <p class="text-xs text-gray-600 mb-1">Original Date:</p>
                            <p class="text-sm font-semibold text-gray-700 line-through">{{ $item->original_date_to_finish ? \Carbon\Carbon::parse($item->original_date_to_finish)->format('M d, Y') : '—' }}</p>
                          </div>
                          <div>
                            <p class="text-xs text-gray-600 mb-1">Extended To:</p>
                            <p class="text-sm font-semibold text-blue-700">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y g:i A') : '—' }}</p>
                          </div>
                        </div>
                        @if($item->original_date_to_finish && $item->date_to_finish)
                          @php
                            $originalDate = \Carbon\Carbon::parse($item->original_date_to_finish);
                            $extendedDate = \Carbon\Carbon::parse($item->date_to_finish);
                            $daysDiff = max(1, (int) ceil($originalDate->diffInDays($extendedDate, false)));
                          @endphp
                          @if($originalDate->ne($extendedDate))
                            <p class="text-xs text-blue-600 mt-2">+{{ $daysDiff }} day{{ $daysDiff != 1 ? 's' : '' }} extension</p>
                          @endif
                        @endif
                      </div>
                    @else
                      <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <p class="text-xs text-gray-600 mb-1">Due Date:</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y g:i A') : '—' }}</p>
                      </div>
                    @endif

                    <!-- Description -->
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $item->milestone_item_description ?? 'No description' }}</p>

                    <!-- List of Reports Section -->
                    <div class="pt-4">
                      <h5 class="text-sm font-bold text-gray-900 mb-3 uppercase tracking-wide">List of Reports</h5>
                      @if(!empty($item->progress_reports))
                        <div class="space-y-2">
                          @foreach($item->progress_reports as $progress)
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                              <p class="text-sm font-semibold text-gray-900">{{ $progress['purpose'] }}</p>
                              <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($progress['submitted_at'])->format('d M Y, g:i A') }}</p>
                              @php
                                $progressStatusColors = [
                                  'submitted' => 'bg-blue-100 text-blue-800',
                                  'approved' => 'bg-green-100 text-green-800',
                                  'rejected' => 'bg-red-100 text-red-800',
                                  'deleted' => 'bg-gray-100 text-gray-800'
                                ];
                                $progressStatusColor = $progressStatusColors[$progress['progress_status']] ?? 'bg-gray-100 text-gray-800';
                              @endphp
                              <span class="inline-flex mt-2 px-2 py-1 text-xs font-semibold rounded {{ $progressStatusColor }}">
                                {{ strtoupper($progress['progress_status']) }}
                              </span>

                              <!-- Files for this specific progress report -->
                              @if(!empty($progress['files']))
                                <div class="mt-3 space-y-1">
                                  @foreach($progress['files'] as $file)
                                    <a href="{{ asset('storage/' . $file['file_path']) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-amber-100 to-orange-100 border border-amber-300 rounded-lg hover:shadow-md transition-all text-sm">
                                      <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                      </svg>
                                      <span class="text-xs font-medium text-gray-900">{{ basename($file['file_path']) }}</span>
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
                      <td class="px-4 py-3 text-sm font-semibold">₱{{ number_format($payment->amount ?? 0, 2) }}</td>
                      <td class="px-4 py-3 text-sm">{{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('M j, Y') : '—' }}</td>
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

