  <!-- Halt Project Confirmation Modal -->
  <div id="haltProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300" data-project-id="{{ $project->project_id }}">
    <div class="absolute inset-0 flex items-center justify-center p-4 overflow-y-auto">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 my-8">
        <!-- Header -->
        <div class="relative px-6 py-8 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-t-2xl">
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-4">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h2 class="text-xl font-bold">Halt Project</h2>
            <p class="text-amber-100 text-sm mt-2">Temporarily pause project progress</p>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 max-h-[calc(100vh-12rem)] overflow-y-auto">
          <!-- Project Info -->
          <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <div class="space-y-2">
              <div>
                <span class="text-xs font-semibold text-gray-600">Project Title</span>
                <p class="text-sm text-gray-900 font-medium">{{ $project->project_title }}</p>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <span class="text-xs font-semibold text-gray-600">Owner</span>
                  <p class="text-sm text-gray-900">{{ $project->owner_name ?? '—' }}</p>
                </div>
                <div>
                  <span class="text-xs font-semibold text-gray-600">Status</span>
                  <p class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $project->project_status)) }}</p>
                </div>
              </div>
              @if($project->contractor_name)
              <div>
                <span class="text-xs font-semibold text-gray-600">Contractor</span>
                <p class="text-sm text-gray-900">{{ $project->contractor_name }}</p>
              </div>
              @endif
            </div>
          </div>

          <!-- Info Box -->
          <div class="bg-amber-50 rounded-lg p-4 mb-4">
            <p class="text-sm text-amber-900 font-medium mb-3">Halting this project will:</p>
            <ul class="text-xs text-amber-800 space-y-1.5 ml-4 list-disc">
              <li>Pause all milestone progress tracking</li>
              <li>Suspend contractor work authorization</li>
              <li>Mark project status as halted</li>
              <li>Record the halt reason and administrative remarks</li>
              <li>Allow project to be resumed or terminated later</li>
            </ul>
          </div>

          <!-- Associated Dispute -->
          <div class="mb-4">
            <label for="haltDispute" class="block text-sm font-semibold text-gray-900 mb-2">
              Associated Halt Dispute <span class="text-amber-600">*</span>
            </label>
            @if($disputes && count($disputes) > 0)
              <select
                id="haltDispute"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
              >
                <option value="">Select a dispute...</option>
                @foreach($disputes as $dispute)
                  <option value="{{ $dispute->dispute_id }}">
                    #{{ $dispute->dispute_id }} - {{ $dispute->title ?? Str::limit($dispute->dispute_desc, 50) }}
                    (by {{ $dispute->reporter_username }})
                  </option>
                @endforeach
              </select>
              <p class="text-xs text-gray-600 mt-1">Select the halt-type dispute that triggered this project halt</p>
            @else
              <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-sm text-red-800 font-medium mb-2">No Halt Disputes Found</p>
                <p class="text-xs text-red-700">There are no open halt-type disputes for this project. A halt dispute must be created before the project can be halted.</p>
              </div>
              <input type="hidden" id="haltDispute" value="">
            @endif
            <p class="text-xs text-red-600 mt-1 hidden" id="error-halt-dispute"></p>
          </div>

          <!-- Halt Reason -->
          <div class="mb-4">
            <label for="haltReason" class="block text-sm font-semibold text-gray-900 mb-2">
              Halt Reason <span class="text-amber-600">*</span>
            </label>
            <textarea
              id="haltReason"
              rows="3"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
              placeholder="Please provide a reason for halting this project (minimum 10 characters)..."
            ></textarea>
            <p class="text-xs text-red-600 mt-1 hidden" id="error-halt-reason"></p>
          </div>

          <!-- Administrative Remarks (Optional) -->
          <div class="mb-4">
            <label for="haltRemarks" class="block text-sm font-semibold text-gray-900 mb-2">
              Administrative Remarks <span class="text-gray-400 text-xs font-normal">(Optional)</span>
            </label>
            <textarea
              id="haltRemarks"
              rows="2"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
              placeholder="Add any internal notes or administrative remarks..."
            ></textarea>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3">
            <button onclick="hideHaltProjectModal()" class="flex-1 px-6 py-2.5 text-sm font-semibold rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
              Cancel
            </button>
            <button onclick="confirmHaltProject()" class="flex-1 px-6 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white transition shadow-md hover:shadow-lg">
              Halt Project
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
