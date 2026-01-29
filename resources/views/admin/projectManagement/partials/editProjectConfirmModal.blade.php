  <!-- Edit Project Confirmation Modal -->
  <div id="editProjectConfirmModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100">
        <!-- Header -->
        <div class="relative px-6 py-8 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-t-2xl">
          <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-4">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h2 class="text-xl font-bold">Confirm Changes</h2>
            <p class="text-orange-100 text-sm mt-2">Are you sure you want to save the changes to this project?</p>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="bg-orange-50 rounded-lg p-4 mb-6">
            <p class="text-sm text-orange-900 leading-relaxed">
              The project details will be updated and all stakeholders will be notified of the changes.
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3">
            <button onclick="hideEditProjectConfirmModal()" class="flex-1 px-6 py-2.5 text-sm font-semibold rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
              Cancel
            </button>
            <button onclick="confirmEditProject()" class="flex-1 px-6 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white transition shadow-md hover:shadow-lg">
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
