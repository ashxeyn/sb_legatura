  <!-- Halt Project Confirmation Modal -->
  <div id="haltProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300" data-project-id="{{ $project->project_id }}">
    <div class="absolute inset-0 flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative transform transition-all duration-300 scale-100 my-2 flex flex-col" style="max-height:90vh;">
        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-4 py-3 rounded-t-xl flex-shrink-0">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center ring-2 ring-white/30">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="text-white">
                <h2 class="text-sm font-bold">Halt Project</h2>
                <p class="text-[10px] opacity-80">Temporarily pause project progress</p>
              </div>
            </div>
            <button type="button" onclick="hideHaltProjectModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <style>.halt-scroll::-webkit-scrollbar{display:none}</style>
        <div class="halt-scroll p-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">
          <!-- Project Info -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 mb-2">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 mb-1.5 flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-md bg-orange-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              Project Info
            </h3>
            <div class="space-y-0.5 text-[11px]">
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Project Title</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->project_title }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Owner</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->owner_name ?? '—' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Status</span>
                <span class="font-semibold text-gray-900 text-right">{{ ucfirst(str_replace('_', ' ', $project->project_status)) }}</span>
              </div>
              @if($project->contractor_name)
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-orange-50">
                <span class="text-gray-500">Contractor</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->contractor_name }}</span>
              </div>
              @endif
            </div>
          </div>

          <!-- Info Box -->
          <div class="bg-amber-50 border border-amber-200 rounded-lg p-2 mb-2">
            <div class="flex items-center gap-1.5 mb-1.5">
              <div class="w-4 h-4 rounded bg-amber-400 flex items-center justify-center flex-shrink-0">
                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <p class="text-[10px] text-amber-900 font-bold">Halting this project will:</p>
            </div>
            <ul class="text-[9px] text-amber-800 space-y-1 ml-3 list-disc">
              <li>Pause all milestone progress tracking</li>
              <li>Suspend contractor work authorization</li>
              <li>Mark project status as halted</li>
              <li>Record the halt reason and administrative remarks</li>
              <li>Allow project to be resumed or terminated later</li>
            </ul>
          </div>

          <!-- Associated Dispute -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 mb-2">
            <label for="haltDispute" class="text-xs font-bold text-gray-900 mb-1.5 flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-md bg-red-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              Associated Halt Dispute <span class="text-amber-600">*</span>
            </label>
            @if($disputes && count($disputes) > 0)
              <select
                id="haltDispute"
                class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
              >
                <option value="">Select a dispute...</option>
                @foreach($disputes as $dispute)
                  <option value="{{ $dispute->dispute_id }}">
                    #{{ $dispute->dispute_id }} - {{ $dispute->title ?? Str::limit($dispute->dispute_desc, 50) }}
                    (by {{ $dispute->reporter_username }})
                  </option>
                @endforeach
              </select>
              <p class="text-[9px] text-gray-600 mt-0.5">Select the halt-type dispute that triggered this project halt</p>
            @else
              <div class="bg-red-50 border border-red-200 rounded-lg p-2">
                <p class="text-xs text-red-800 font-medium mb-1">No Halt Disputes Found</p>
                <p class="text-[9px] text-red-700">There are no open halt-type disputes for this project. A halt dispute must be created before the project can be halted.</p>
              </div>
              <input type="hidden" id="haltDispute" value="">
            @endif
            <p class="text-[9px] text-red-600 mt-0.5 hidden" id="error-halt-dispute"></p>
          </div>

          <!-- Halt Reason -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 mb-2">
            <label for="haltReason" class="text-xs font-bold text-gray-900 mb-1.5 flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-md bg-amber-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              Halt Reason <span class="text-amber-600">*</span>
            </label>
            <textarea
              id="haltReason"
              rows="2"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
              placeholder="Please provide a reason for halting this project..."
            ></textarea>
            <p class="text-[9px] text-red-600 mt-0.5 hidden" id="error-halt-reason"></p>
          </div>

          <!-- Administrative Remarks (Optional) -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 mb-2">
            <label for="haltRemarks" class="text-xs font-bold text-gray-900 mb-1.5 flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-md bg-gray-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
              </div>
              Administrative Remarks <span class="text-gray-400 text-[9px] font-normal">(Optional)</span>
            </label>
            <textarea
              id="haltRemarks"
              rows="2"
              class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
              placeholder="Add any internal notes..."
            ></textarea>
          </div>
        </div>

        <!-- Footer (Fixed at Bottom) -->
        <div class="border-t border-gray-200 px-3 py-2.5 bg-gray-50 rounded-b-xl flex-shrink-0 flex gap-2">
          <button onclick="hideHaltProjectModal()" class="flex-1 px-4 py-1.5 text-xs font-semibold rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
            Cancel
          </button>
          <button onclick="confirmHaltProject()" class="flex-1 px-4 py-1.5 text-xs font-semibold rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white transition">
            Halt Project
          </button>
        </div>
      </div>
    </div>
  </div>
