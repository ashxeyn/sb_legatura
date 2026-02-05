<!-- Restore Project Confirmation Modal -->
  <div id="restoreProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300" data-project-id="{{ $project->project_id }}">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100">
        <!-- Header -->
        <div class="relative px-6 py-8 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-t-2xl">
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-4">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
            </div>
            <h2 class="text-xl font-bold">Restore Project</h2>
            <p class="text-green-100 text-sm mt-2">Reactivate this project</p>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
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
                  <span class="text-xs font-semibold text-gray-600">Current Status</span>
                  <p class="text-sm text-red-600 font-semibold">Deleted</p>
                </div>
              </div>
              @if($project->contractor_name)
              <div>
                <span class="text-xs font-semibold text-gray-600">Contractor</span>
                <p class="text-sm text-gray-900">{{ $project->contractor_name }}</p>
              </div>
              @endif
              @if($project->stat_reason)
              <div>
                <span class="text-xs font-semibold text-gray-600">Deletion Reason</span>
                <p class="text-sm text-gray-700 italic">{{ $project->stat_reason }}</p>
              </div>
              @endif
            </div>
          </div>

          <!-- Info Box -->
          <div class="bg-green-50 rounded-lg p-4 mb-4">
            <p class="text-sm text-green-900 font-medium mb-3">Restoring this project will:</p>
            <ul class="text-xs text-green-800 space-y-1.5 ml-4 list-disc">
              <li>Revert project status to its state before deletion</li>
              <li>Restore all milestones to their previous status</li>
              <li>Restore all milestone items to their previous status</li>
              <li>Clear the deletion reason</li>
            </ul>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3">
            <button onclick="hideRestoreProjectModal()" class="flex-1 px-6 py-2.5 text-sm font-semibold rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
              Cancel
            </button>
            <button onclick="confirmRestoreProject()" class="flex-1 px-6 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white transition shadow-md hover:shadow-lg">
              Restore Project
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
