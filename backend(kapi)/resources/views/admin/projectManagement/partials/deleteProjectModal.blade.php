  <!-- Delete Project Confirmation Modal -->
  <div id="deleteProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
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
          <div class="bg-rose-50 rounded-lg p-4 mb-6">
            <p class="text-sm text-rose-900 font-medium mb-3">Are you sure you want to permanently delete this project?</p>
            <ul class="text-xs text-rose-800 space-y-1.5 ml-4 list-disc">
              <li>All project data will be removed from the system</li>
              <li>All associated bids will be deleted</li>
              <li>This action is irreversible</li>
            </ul>
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
