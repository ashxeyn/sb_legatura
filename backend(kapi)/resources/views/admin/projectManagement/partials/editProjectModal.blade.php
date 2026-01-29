  <!-- Edit Project Modal -->
  <div id="editProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
          <div>
            <h2 class="text-lg font-bold text-gray-900">Edit Project Information</h2>
            <p class="text-xs text-gray-500 mt-0.5">Update the project details</p>
          </div>
          <button onclick="hideEditProjectModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-rose-500 hover:text-rose-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6 overflow-y-auto flex-1">
          <!-- Project Title and Description -->
          <div class="grid grid-cols-2 gap-6">
            <div class="col-span-2">
              <label class="block text-sm font-semibold text-gray-900 mb-2">Project Title</label>
              <input type="text" id="editProjectTitle" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Greenfield Commercial Complex">
            </div>
            <div class="col-span-2">
              <label class="block text-sm font-semibold text-gray-900 mb-2">Project Description</label>
              <textarea id="editProjectDescription" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none" placeholder="Construction of a 2-story commercial complex with parking space, electrical systems, and interior finishing"></textarea>
            </div>
          </div>

          <!-- Project Photos -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Project Photos</label>
            <div id="editProjectPhotos" class="space-y-2">
              <!-- Photo items will be injected by JS -->
            </div>
            <button onclick="addProjectPhotoInput()" class="mt-2 text-sm text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Add Photo
            </button>
          </div>

          <!-- Land Title Certificate -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Land Title Certificate</label>
            <div id="editLandTitle" class="space-y-2">
              <!-- Land title file will be injected by JS -->
            </div>
          </div>

          <!-- Supporting Files -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Supporting Files</label>
            <div id="editProjectFiles" class="space-y-2">
              <!-- File items will be injected by JS -->
            </div>
            <button onclick="addProjectFileInput()" class="mt-2 text-sm text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Add File
            </button>
          </div>

          <!-- Property Details -->
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Region</label>
              <input type="text" id="editProjectRegion" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Region VII">
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Province</label>
              <input type="text" id="editProjectProvince" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Cebu">
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Municipality</label>
              <input type="text" id="editProjectMunicipality" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Mandaue City">
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Barangay</label>
              <input type="text" id="editProjectBarangay" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Centro">
            </div>
          </div>

          <!-- Timeline and Budget -->
          <div class="grid grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Project Timeline</label>
              <input type="text" id="editProjectTimeline" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="6 months">
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-900 mb-2">Project Budget</label>
              <input type="text" id="editProjectBudget" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="₱5,000,000.00">
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3 flex-shrink-0">
          <button onclick="hideEditProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
            Cancel
          </button>
          <button onclick="showEditProjectConfirmModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white transition shadow-md hover:shadow-lg">
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>
