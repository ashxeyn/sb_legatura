  <!-- Delete Project Confirmation Modal -->
  <div id="deleteProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300" data-project-id="{{ $project->project_id }}">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100">
        <!-- Header -->
        <div class="relative px-6 py-8 bg-gradient-to-r from-rose-500 to-rose-600 text-white rounded-t-2xl">
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-4">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <h2 class="text-xl font-bold">Delete Project</h2>
            <p class="text-rose-100 text-sm mt-2">This action cannot be undone</p>
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

          <!-- Warning Box -->
          <div class="bg-rose-50 rounded-lg p-4 mb-4">
            <p class="text-sm text-rose-900 font-medium mb-3">Deleting this project will:</p>
            <ul class="text-xs text-rose-800 space-y-1.5 ml-4 list-disc">
              <li>Mark the project status as deleted</li>
              <li>Mark all milestones as deleted</li>
              <li>Mark all milestone items as deleted</li>
              <li>Record the deletion reason</li>
            </ul>
          </div>

          <!-- Deletion Reason -->
          <div class="mb-4">
            <label for="deleteReason" class="block text-sm font-semibold text-gray-900 mb-2">
              Deletion Reason <span class="text-rose-600">*</span>
            </label>
            <textarea
              id="deleteReason"
              rows="3"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none"
              placeholder="Please provide a reason for deleting this project (minimum 10 characters)..."
            ></textarea>
            <p class="text-xs text-red-600 mt-1 hidden" id="error-delete-reason"></p>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3">
            <button onclick="hideDeleteProjectModal()" class="flex-1 px-6 py-2.5 text-sm font-semibold rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
              Cancel
            </button>
            <button onclick="confirmDeleteProject()" class="flex-1 px-6 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white transition shadow-md hover:shadow-lg">
              Delete Project
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
