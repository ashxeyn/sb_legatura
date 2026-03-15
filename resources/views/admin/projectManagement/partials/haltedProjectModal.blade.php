  <!-- Halted Project Modal -->
<div id="haltedProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-5xl rounded-xl shadow-2xl relative flex flex-col" style="max-height:90vh;">
      @isset($project)
      <!-- Header with Owner Info -->
      <div class="bg-gradient-to-r from-rose-500 to-red-600 px-4 py-3 rounded-t-xl flex-shrink-0">
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
              <p class="text-[10px] opacity-80 flex items-center gap-1.5">
                <span class="inline-block w-1.5 h-1.5 bg-rose-300 rounded-full"></span>
                Halted Project
                <span class="ml-1">{{ $project->halted_at ? \Carbon\Carbon::parse($project->halted_at)->format('M j, Y') : '' }}</span>
              </p>
            </div>
          </div>
          <button type="button" onclick="hideHaltedProjectModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <style>.halted-scroll::-webkit-scrollbar{display:none}</style>
      <div class="halted-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">
        <!-- Halted Banner -->
        <div class="bg-white border border-gray-200 rounded-lg p-2.5 flex items-center gap-3">
          <div class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-rose-800">This Project is Currently HALTED</p>
            <p class="text-[10px] text-rose-600 italic">Work temporarily paused. Will resume once resolved.</p>
          </div>
          <button type="button" data-project-id="{{ $project->project_id }}" onclick="showHaltDetailsModal(Number(this.dataset.projectId))" class="flex-shrink-0 flex items-center gap-1 px-2.5 py-1 bg-rose-500 hover:bg-rose-600 text-white text-[10px] font-semibold rounded-md transition-colors">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            View Halt Details
          </button>
        </div>

        <!-- Halt Impact Analysis -->
        @php
          $haltedDate = $project->halted_at ? \Carbon\Carbon::parse($project->halted_at) : null;
          $today = \Carbon\Carbon::now();
          $haltDuration = $haltedDate ? (int) $haltedDate->diffInDays($today) : 0;

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

        <div class="bg-white border border-rose-200 rounded-lg p-2">
          <h3 class="font-bold text-gray-900 text-xs border-b border-rose-100 pb-2 mb-2 flex items-center gap-1.5">
            <div class="w-5 h-5 rounded-md bg-rose-500 flex items-center justify-center flex-shrink-0">
              <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            Halt Impact Analysis
          </h3>
          <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2">
            <div class="bg-rose-50 rounded-lg p-2.5 border border-rose-100">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] text-gray-500 font-medium">Duration Halted</p>
                <div class="w-5 h-5 rounded bg-rose-100 flex items-center justify-center">
                  <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <p class="text-sm font-bold text-rose-600">{{ $haltDuration }} days</p>
              <p class="text-[10px] text-gray-500 mt-0.5">Since {{ $haltedDate ? $haltedDate->format('M d, Y') : '—' }}</p>
            </div>
            <div class="bg-rose-50 rounded-lg p-2.5 border border-rose-100">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] text-gray-500 font-medium">Affected Milestones</p>
                <div class="w-5 h-5 rounded bg-indigo-100 flex items-center justify-center">
                  <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                </div>
              </div>
              <p class="text-sm font-bold text-gray-900">{{ $affectedMilestones }}/{{ $totalMilestones }}</p>
              <p class="text-[10px] text-gray-500 mt-0.5">{{ $totalMilestones > 0 ? number_format(($affectedMilestones / $totalMilestones) * 100, 0) : 0 }}% of total</p>
            </div>
            <div class="bg-rose-50 rounded-lg p-2.5 border border-rose-100">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] text-gray-500 font-medium">Financial Impact</p>
                <div class="w-5 h-5 rounded bg-teal-100 flex items-center justify-center">
                  <svg class="w-3 h-3 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <p class="text-sm font-bold text-rose-600">₱{{ number_format($financialImpact, 2) }}</p>
              <p class="text-[10px] text-gray-500 mt-0.5">{{ number_format($impactPercentage, 1) }}% of budget</p>
            </div>
            <div class="bg-rose-50 rounded-lg p-2.5 border border-rose-100">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] text-gray-500 font-medium">Timeline Impact</p>
                <div class="w-5 h-5 rounded bg-orange-100 flex items-center justify-center">
                  <svg class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
              </div>
              <p class="text-sm font-bold text-gray-900">+{{ $timelineImpact }} days</p>
              <p class="text-[10px] text-gray-500 mt-0.5">Extension needed</p>
            </div>
          </div>
          @if($paymentsAffected > 0)
            <div class="mt-2 bg-rose-50 rounded p-2 border border-rose-100 flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <p class="text-[10px] text-rose-700 font-semibold">{{ $paymentsAffected }} pending payment(s) frozen until project resumes</p>
            </div>
          @endif
        </div>

        <!-- Timeline Progress -->
        <div class="bg-white border border-gray-200 rounded-lg p-3">
          <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 mb-3 flex items-center gap-1.5">
            <div class="w-5 h-5 rounded-md bg-indigo-500 flex items-center justify-center flex-shrink-0">
              <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            Timeline Progress
          </h3>
          <div class="bg-indigo-50 rounded-lg p-3 border border-indigo-100">
            <div class="grid grid-cols-3 gap-3 mb-3">
              <div>
                <p class="text-[10px] text-gray-500 font-semibold mb-1">Start Date</p>
                <p class="text-sm font-bold text-gray-900">
                  {{ $project->timeline_start ? \Carbon\Carbon::parse($project->timeline_start)->format('M d, Y') : '—' }}
                </p>
              </div>
              <div>
                <p class="text-[10px] text-gray-500 font-semibold mb-1">End Date</p>
                @if($hasExtension)
                  <p class="text-[10px] text-gray-400 line-through">{{ $originalEndDate->format('M d, Y') }}</p>
                  <p class="text-sm font-bold text-blue-600">{{ $extendedEndDate->format('M d, Y') }}</p>
                  <span class="inline-block px-1.5 py-0.5 bg-blue-100 text-blue-700 text-[9px] font-semibold rounded">
                    +{{ max(1, (int) ceil($originalEndDate->diffInDays($extendedEndDate, false))) }}d
                  </span>
                @else
                  <p class="text-sm font-bold text-gray-900">
                    {{ $project->timeline_end ? \Carbon\Carbon::parse($project->timeline_end)->format('M d, Y') : '—' }}
                  </p>
                @endif
              </div>
              <div>
                <p class="text-[10px] text-gray-500 font-semibold mb-1">Days Halted</p>
                <p class="text-sm font-bold text-rose-600">{{ $haltDuration }} days</p>
                <p class="text-[10px] text-gray-500">Since {{ $haltedDate ? $haltedDate->format('M d, Y') : '—' }}</p>
              </div>
            </div>
            <div class="pt-2.5 border-t border-indigo-200 grid grid-cols-2 gap-2">
              <button type="button"
                data-project-id="{{ $project->project_id }}"
                data-end-date="{{ $project->timeline_end ?? '' }}"
                data-start-date="{{ $project->timeline_start ?? '' }}"
                onclick="showExtendTimelineModal(Number(this.dataset.projectId), this.dataset.endDate, this.dataset.startDate)"
                class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-1.5"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Extend Timeline
              </button>
              <button type="button"
                data-project-id="{{ $project->project_id }}"
                onclick="showBulkAdjustDatesModal(Number(this.dataset.projectId))"
                class="px-3 py-2 bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-50 text-xs font-semibold rounded-md transition-colors flex items-center justify-center gap-1.5"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Bulk Adjust Dates
              </button>
            </div>
          </div>
        </div>

        <!-- Pending Extension Requests (Collapsible) -->
        <div id="pendingExtensionsSection" class="hidden bg-white border border-gray-200 rounded-lg overflow-hidden">
          <button type="button" onclick="togglePendingExtensions()" class="w-full px-3 py-2.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-md bg-blue-600 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="text-left">
                <h3 class="text-xs font-bold text-gray-900">Pending Extension Requests (<span id="pendingExtensionsCount">0</span>)</h3>
                <p class="text-[10px] text-gray-500">Review and manage timeline extension requests</p>
              </div>
            </div>
            <svg id="pendingExtensionsChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div id="pendingExtensionsContent" class="hidden border-t border-gray-200">
            <div id="pendingExtensionsContainer" class="p-3 space-y-3">
              <!-- Dynamically populated by JavaScript -->
            </div>
          </div>
        </div>

        <!-- Pre-Halt vs Current Status (Collapsible) -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
          <button type="button" onclick="toggleHaltComparison()" class="w-full px-3 py-2.5 flex items-center justify-between hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-md bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
              </div>
              <div class="text-left">
                <h3 class="text-xs font-bold text-gray-900">Pre-Halt vs Current Status</h3>
                <p class="text-[10px] text-gray-500">Compare project status before and after halt</p>
              </div>
            </div>
            <svg id="haltComparisonChevron" class="w-4 h-4 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div id="haltComparisonContent" class="hidden border-t border-gray-200">
            <div class="p-2 space-y-2">
              <div class="grid md:grid-cols-2 gap-2">
                <!-- Before Halt -->
                <div class="bg-green-50 rounded p-2 border border-green-200">
                  <div class="flex items-center gap-1.5 mb-2">
                    <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h4 class="text-xs font-bold text-gray-900">Before Halt</h4>
                  </div>
                  <div class="space-y-1 text-[11px]">
                    <div class="flex justify-between py-0.5 px-1 rounded hover:bg-green-100">
                      <span class="text-gray-500">Status</span>
                      <span class="font-semibold text-green-700 text-right">Active</span>
                    </div>
                    <div class="flex justify-between py-0.5 px-1 rounded hover:bg-green-100">
                      <span class="text-gray-500">Completed Milestones</span>
                      <span class="font-semibold text-gray-900 text-right">{{ $project->pre_halt_completed ?? 0 }}/{{ $totalMilestones }}</span>
                    </div>
                    <div class="flex justify-between py-0.5 px-1 rounded hover:bg-green-100">
                      <span class="text-gray-500">Budget Utilized</span>
                      <span class="font-semibold text-gray-900 text-right">{{ number_format($project->pre_halt_budget_used ?? 0, 1) }}%</span>
                    </div>
                    <div class="flex justify-between py-0.5 px-1 rounded hover:bg-green-100">
                      <span class="text-gray-500">Timeline Progress</span>
                      <span class="font-semibold text-gray-900 text-right">On Track</span>
                    </div>
                  </div>
                </div>
                <!-- After Halt -->
                <div class="bg-rose-50 rounded p-2 border border-rose-200">
                  <div class="flex items-center gap-1.5 mb-2">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h4 class="text-xs font-bold text-gray-900">After Halt</h4>
                  </div>
                  <div class="space-y-1 text-[11px]">
                    <div class="flex justify-between py-0.5 px-1 rounded hover:bg-rose-100">
                      <span class="text-gray-500">Status</span>
                      <span class="font-semibold text-rose-600">Halted</span>
                    </div>
                    <div class="flex justify-between py-0.5 px-1 rounded hover:bg-rose-100">
                      <span class="text-gray-500">Completed Milestones</span>
                      <span class="font-semibold text-gray-900">{{ $project->pre_halt_completed ?? 0 }}/{{ $totalMilestones }}</span>
                    </div>
                    <div class="flex justify-between py-0.5 px-1 rounded hover:bg-rose-100">
                      <span class="text-gray-500">Budget Utilized</span>
                      <span class="font-semibold text-gray-900">{{ number_format($project->pre_halt_budget_used ?? 0, 1) }}%</span>
                    </div>
                    <div class="flex justify-between py-0.5 px-1 rounded hover:bg-rose-100">
                      <span class="text-gray-500">Days Halted</span>
                      <span class="font-semibold text-rose-600">{{ $haltDuration }} days</span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Impact Summary -->
              <div class="bg-amber-50 rounded p-2 border border-amber-200">
                <h5 class="text-[10px] font-bold text-amber-800 mb-1.5 flex items-center gap-1.5">
                  <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Impact Summary
                </h5>
                <ul class="text-[10px] text-gray-600 space-y-1">
                  <li class="flex items-start gap-1.5">
                    <svg class="w-3 h-3 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    All construction activities suspended
                  </li>
                  <li class="flex items-start gap-1.5">
                    <svg class="w-3 h-3 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    Milestone payments frozen until resolution
                  </li>
                  <li class="flex items-start gap-1.5">
                    <svg class="w-3 h-3 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    Timeline extension required upon resumption
                  </li>
                  <li class="flex items-start gap-1.5">
                    <svg class="w-3 h-3 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    Contractor work authorization suspended
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Project Details and Contractor Details (2-Column) -->
        <div class="grid lg:grid-cols-2 gap-3">
          <!-- Project Details -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-md bg-orange-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
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
                <span class="text-gray-500">Property Type</span>
                <span class="font-semibold text-gray-900 text-right">{{ ucfirst(str_replace('_', ' ', $project->property_type ?? '—')) }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                <span class="text-gray-500">Lot Size (sqm)</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->lot_size ? number_format($project->lot_size, 2) : '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                <span class="text-gray-500">Floor Area (sqm)</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->floor_area ? number_format($project->floor_area, 2) : '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                <span class="text-gray-500">Target Timeline</span>
                <span class="font-semibold text-gray-900 text-right">
                  @if($project->timeline_start && $project->timeline_end)
                    {{ \Carbon\Carbon::parse($project->timeline_start)->format('M j, Y') }} –
                    @if($hasExtension)
                      <span class="text-gray-400 line-through text-[9px]">{{ $originalEndDate->format('M j, Y') }}</span>
                      <span class="text-blue-600 font-bold ml-1">{{ $extendedEndDate->format('M j, Y') }}</span>
                    @else
                      {{ \Carbon\Carbon::parse($project->timeline_end)->format('M j, Y') }}
                    @endif
                  @else
                    —
                  @endif
                </span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                <span class="text-gray-500">Budget</span>
                <span class="font-semibold text-rose-600 text-right">
                  @if($project->budget_range_min && $project->budget_range_max)
                    ₱{{ number_format($project->budget_range_min, 2) }} – ₱{{ number_format($project->budget_range_max, 2) }}
                  @else
                    —
                  @endif
                </span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                <span class="text-gray-500">Bidding Deadline</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->bidding_due ? \Carbon\Carbon::parse($project->bidding_due)->format('M j, Y') : '—' }}</span>
              </div>
            </div>
            <div class="pt-1">
              <span class="text-[10px] text-gray-400 block mb-1">Supporting Files</span>
              <div class="flex flex-wrap gap-1.5">
                @forelse($project->project_files as $file)
                  <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] hover:bg-blue-100 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    {{ ucfirst(str_replace('_', ' ', $file->file_type)) }}
                  </a>
                @empty
                  <p class="text-[10px] text-gray-400">No files uploaded</p>
                @endforelse
              </div>
            </div>
          </div>

          <!-- Contractor Details -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-md bg-blue-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              Contractor Details
            </h3>
            <div class="space-y-0.5 text-[11px]">
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Contractor Name</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_name ?? '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Company Name</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->company_name ?? '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Email Address</span>
                <span class="font-semibold text-blue-600 text-right">{{ $project->contractor_email ?? '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">PCAB No.</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_pcab ?? '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">PCAB Category</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_category ?? '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">PCAB Expiration</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_pcab_expiry ? \Carbon\Carbon::parse($project->contractor_pcab_expiry)->format('M j, Y') : '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Business Permit No.</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_permit ?? '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Permit City</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_city ?? '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">Permit Expiration</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_permit_expiry ? \Carbon\Carbon::parse($project->contractor_permit_expiry)->format('M j, Y') : '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                <span class="text-gray-500">TIN Registration</span>
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
              <div class="w-5 h-5 rounded-md bg-rose-500 flex items-center justify-center flex-shrink-0">
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
              @foreach($reversedItems as $index => $itemData)
                @php
                  $item = $itemData['item'];
                  $cumulative = round($itemData['cumulative']);
                  $isCompleted = $item->item_status === 'completed';
                  $displayStatus = $isCompleted ? 'completed' : 'halt';
                  // All milestones show red since project is halted
                  $statusBadge = 'bg-rose-100 text-rose-700';
                  $cardBg = 'border border-rose-200 bg-rose-50';
                  $circleStyle = 'bg-rose-100 text-rose-700 ring-2 ring-rose-200 shadow-sm';
                @endphp
                <div class="milestone-row flex items-start gap-2 cursor-pointer hover:bg-rose-50/50 rounded p-1 transition-colors" data-item-id="{{ $item->item_id }}" onclick="showHaltedMilestoneDetail(Number(this.dataset.itemId), this)">
                  <div class="flex flex-col items-center flex-shrink-0">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-[10px] font-bold {{ $circleStyle }}">{{ $cumulative }}%</div>
                    @if($index < $totalItems - 1)
                      <div class="relative w-0.5 bg-gray-200 my-1" style="min-height:40px;">
                        {{-- All connector circles red since project is halted --}}
                        <div class="absolute left-1/2 -translate-x-1/2 -bottom-2.5 w-5 h-5 rounded-full bg-rose-500 flex items-center justify-center ring-2 ring-rose-200 shadow-sm">
                          <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                          </svg>
                        </div>
                      </div>
                    @endif
                  </div>
                  <div class="flex-1 min-w-0 mb-4">
                    <div class="{{ $cardBg }} rounded p-2">
                      <div class="flex items-start justify-between gap-1 mb-1">
                        <div class="flex-1 min-w-0">
                          <h4 class="text-xs font-bold text-gray-900 truncate">{{ $item->milestone_item_title }}</h4>
                          <div class="flex flex-wrap gap-1 mt-0.5">
                            @if($item->was_extended ?? false)
                              <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[9px] font-medium rounded bg-blue-100 text-blue-700">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Extended {{ $item->extension_count ?? 1 }}x
                              </span>
                            @endif
                            @if(($item->carry_forward_amount ?? 0) > 0)
                              <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[9px] font-medium rounded bg-orange-100 text-orange-700">+₱{{ number_format($item->carry_forward_amount, 0) }}</span>
                            @endif
                          </div>
                        </div>
                        <span class="inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded-full {{ $statusBadge }} uppercase flex-shrink-0">{{ $isCompleted ? 'DONE' : 'HALTED' }}</span>
                      </div>
                      @if($item->was_extended ?? false)
                        <p class="text-[9px] text-gray-400 line-through">{{ $item->original_date_to_finish ? \Carbon\Carbon::parse($item->original_date_to_finish)->format('M d, Y') : '—' }}</p>
                        <p class="text-[9px] text-blue-600 font-semibold">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y') : '—' }}</p>
                      @else
                        <p class="text-[9px] text-gray-500">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y') : '—' }}</p>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
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
              <button id="editHaltedMilestoneBtn" onclick="openEditMilestoneModal(window.selectedMilestoneItemId)" class="text-amber-600 hover:text-amber-700 text-[10px] font-semibold flex items-center gap-1 hidden" title="Edit Details">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
              </button>
            </div>
            <div id="haltedDetailsContent" class="space-y-2 flex-1 overflow-y-auto min-h-0">
              <div id="haltedNoSelection" class="flex items-center justify-center py-8 text-center">
                <div>
                  <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  <p class="text-xs text-gray-400 font-semibold">Click a milestone to view details</p>
                </div>
              </div>
              @foreach($project->milestone_items as $item)
                <div id="halted-milestone-detail-{{ $item->item_id }}" class="space-y-2 hidden">
                  <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-gray-900">{{ $item->milestone_item_title }}</h4>
                    @php
                      $isItemCompleted = $item->item_status === 'completed';
                      // All red since project is halted
                      $itemStatusBadge = 'bg-rose-100 text-rose-700';
                    @endphp
                    <span class="inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded-full {{ $itemStatusBadge }} uppercase">{{ $isItemCompleted ? 'COMPLETED' : 'HALTED' }}</span>
                  </div>
                  @if($item->was_extended ?? false)
                    <div class="bg-blue-50 border border-blue-200 rounded p-2">
                      <div class="flex items-center gap-1.5 mb-1">
                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[10px] font-semibold text-blue-700 uppercase">Extended {{ $item->extension_count ?? 1 }}x</span>
                      </div>
                      <div class="grid grid-cols-2 gap-2">
                        <div>
                          <p class="text-[9px] text-gray-500 mb-0.5">Original:</p>
                          <p class="text-xs font-semibold text-gray-600 line-through">{{ $item->original_date_to_finish ? \Carbon\Carbon::parse($item->original_date_to_finish)->format('M d, Y') : '—' }}</p>
                        </div>
                        <div>
                          <p class="text-[9px] text-gray-500 mb-0.5">Extended To:</p>
                          <p class="text-xs font-semibold text-blue-700">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y') : '—' }}</p>
                        </div>
                      </div>
                    </div>
                  @else
                    <div class="bg-gray-50 border border-gray-200 rounded p-2">
                      <p class="text-[9px] text-gray-500 mb-0.5">Due Date</p>
                      <p class="text-xs font-semibold text-gray-900">{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y') : '—' }}</p>
                    </div>
                  @endif
                  <p class="text-[10px] text-gray-600 leading-relaxed">{{ $item->milestone_item_description ?? 'No description' }}</p>
                  <div>
                    <h5 class="text-[10px] font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Progress Reports</h5>
                    @if(!empty($item->progress_reports))
                      <div class="space-y-1">
                        @foreach($item->progress_reports as $progress)
                          <div class="p-2 bg-gray-50 border border-gray-200 rounded">
                            <p class="text-xs font-semibold text-gray-900">{{ $progress['purpose'] }}</p>
                            <p class="text-[9px] text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($progress['submitted_at'])->format('d M Y, g:i A') }}</p>
                            @php
                              $progressStatusColors = [
                                'submitted' => 'bg-blue-100 text-blue-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'deleted' => 'bg-gray-100 text-gray-800'
                              ];
                              $progressStatusColor = $progressStatusColors[$progress['progress_status']] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex mt-1 px-1.5 py-0.5 text-[9px] font-semibold rounded {{ $progressStatusColor }}">{{ strtoupper($progress['progress_status']) }}</span>
                            @if(!empty($progress['files']))
                              <div class="mt-1.5 space-y-1">
                                @foreach($progress['files'] as $file)
                                  <a href="{{ asset('storage/' . $file['file_path']) }}" target="_blank" class="flex items-center gap-1.5 px-2 py-1 bg-amber-50 border border-amber-200 rounded text-[10px] hover:bg-amber-100 transition-colors">
                                    <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <span class="text-gray-700 truncate">{{ basename($file['file_path']) }}</span>
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
              <p class="text-[10px] text-gray-400 mt-0.5">Uploaded receipts and payment confirmations for completed milestones</p>
            </div>
          </div>
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
              <p class="text-sm font-bold text-gray-900">{{ $project->total_milestones_paid ?? 0 }}/{{ $project->total_milestone_items ?? 0 }}</p>
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
              <p class="text-sm font-bold text-teal-600">&#8369;{{ number_format($project->total_amount_paid ?? 0, 2) }}</p>
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
              <p class="text-xs font-semibold text-gray-900">{{ $project->last_payment_date ? \Carbon\Carbon::parse($project->last_payment_date)->format('M j, Y') : '—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2.5 border border-gray-200">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-[10px] text-gray-500 font-medium">Overall Status</p>
                <div class="w-5 h-5 rounded bg-rose-100 flex items-center justify-center">
                  <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <p class="text-xs font-bold text-rose-600">{{ $project->overall_payment_status ?? '—' }}</p>
            </div>
          </div>
          <div class="rounded-lg border border-gray-200 overflow-hidden">
            <table class="w-full text-[10px]">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Payment Type</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Milestone</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Period</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Amount Paid</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Proof</th>
                  <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($project->payments as $payment)
                  <tr class="hover:bg-gray-50">
                    <td class="px-2.5 py-1.5">
                      @php
                        $paymentType = $payment->payment_type ?? 'milestone';
                        $typeColors = [
                          'downpayment' => 'bg-purple-100 text-purple-800',
                          'milestone'   => 'bg-blue-100 text-blue-800',
                          'final'       => 'bg-green-100 text-green-800',
                        ];
                        $typeColor = $typeColors[$paymentType] ?? 'bg-gray-100 text-gray-800';
                      @endphp
                      <span class="inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded {{ $typeColor }}">{{ ucfirst($paymentType) }}</span>
                    </td>
                    <td class="px-2.5 py-1.5 font-semibold">{{ $payment->milestone_item_title ?? '—' }}</td>
                    <td class="px-2.5 py-1.5">{{ $payment->milestone_period ?? '—' }}</td>
                    <td class="px-2.5 py-1.5 font-semibold">&#8369;{{ number_format($payment->amount ?? 0, 2) }}</td>
                    <td class="px-2.5 py-1.5">{{ $payment->transaction_date ? \Carbon\Carbon::parse($payment->transaction_date)->format('M j, Y') : '—' }}</td>
                    <td class="px-2.5 py-1.5">
                      @if($payment->receipt_photo)
                        <a href="{{ asset('storage/' . $payment->receipt_photo) }}" target="_blank" class="text-blue-600 hover:underline">View</a>
                      @else
                        &#8212;
                      @endif
                    </td>
                    <td class="px-2.5 py-1.5">
                      @php
                        $payStatusColors = [
                          'approved' => 'bg-green-100 text-green-800',
                          'paid'     => 'bg-green-100 text-green-800',
                          'pending'  => 'bg-yellow-100 text-yellow-800',
                          'rejected' => 'bg-red-100 text-red-800',
                        ];
                        $payStatusColor = $payStatusColors[$payment->payment_status] ?? 'bg-gray-100 text-gray-800';
                      @endphp
                      <span class="inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded-full {{ $payStatusColor }}">{{ ucfirst($payment->payment_status ?? 'Unknown') }}</span>
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
          <button
            type="button"
            data-project-id="{{ $project->project_id }}"
            data-project-title="{{ $project->project_title }}"
            onclick="openAdminPaymentHistoryModal(Number(this.dataset.projectId), this.dataset.projectTitle)"
            class="w-full px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-xs font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-colors flex items-center justify-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            View Full Payment History
          </button>
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
        <button type="button" onclick="hideHaltedProjectModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 transition-colors flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Close
        </button>
      </div>

      @else
        <div class="p-8 text-center text-gray-500">
          <p class="text-xs">Loading project details...</p>
        </div>
      @endisset
    </div>
  </div>
</div>
