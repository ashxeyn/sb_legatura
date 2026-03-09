<!-- Extend Timeline Modal -->
<div id="extendTimelineModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center overflow-y-auto py-8 px-4">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl relative my-4 transform transition-all duration-300">
      
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-600 px-6 py-5 rounded-t-2xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
        <div class="flex items-center justify-between relative z-10">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <div class="text-white">
              <h3 class="text-lg font-bold tracking-wide">Extend Project Timeline</h3>
              <p class="text-xs opacity-90">Adjust project end date and affected milestones</p>
            </div>
          </div>
          <button onclick="hideExtendTimelineModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6 max-h-[calc(100vh-12rem)] overflow-y-auto">
        
        <!-- Current Timeline Info -->
        <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl p-5 border border-blue-200">
          <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Current Timeline
          </h4>
          <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
              <p class="text-xs text-gray-600 mb-1">Start Date</p>
              <p class="font-semibold text-gray-900" id="extendCurrentStart">—</p>
            </div>
            <div>
              <p class="text-xs text-gray-600 mb-1">End Date</p>
              <p class="font-semibold text-gray-900" id="extendCurrentEnd">—</p>
            </div>
            <div>
              <p class="text-xs text-gray-600 mb-1">Duration</p>
              <p class="font-semibold text-gray-900" id="extendCurrentDuration">—</p>
            </div>
          </div>
        </div>

        <!-- Extension Form -->
        <form id="extendTimelineForm" class="space-y-5">
          <input type="hidden" id="extendProjectId" name="project_id">
          <input type="hidden" id="extendCurrentEndDate" name="current_end_date">

          <!-- New End Date -->
          <div>
            <label for="extendNewEndDate" class="block text-sm font-semibold text-gray-700 mb-2">
              New End Date <span class="text-red-500">*</span>
            </label>
            <input 
              type="date" 
              id="extendNewEndDate" 
              name="new_end_date"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
              required
            >
            <p class="text-xs text-gray-500 mt-1">Must be after the current end date</p>
          </div>

          <!-- Extension Duration Display -->
          <div id="extensionDurationDisplay" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center gap-2 text-blue-700">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="font-semibold">Extension Duration: <span id="extensionDays">0</span> days</span>
            </div>
          </div>

          <!-- Reason -->
          <div>
            <label for="extendReason" class="block text-sm font-semibold text-gray-700 mb-2">
              Reason for Extension <span class="text-red-500">*</span>
            </label>
            <textarea 
              id="extendReason" 
              name="reason"
              rows="4"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"
              placeholder="Provide a detailed reason for the timeline extension (minimum 10 characters)..."
              required
              minlength="10"
            ></textarea>
            <p class="text-xs text-gray-500 mt-1">
              <span id="reasonCharCount">0</span>/500 characters (minimum 10)
            </p>
          </div>

          <!-- Affected Milestones -->
          <div id="affectedMilestonesSection" class="hidden">
            <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
              <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              Affected Milestones: <span id="affectedMilestonesCount">0</span>
            </h4>
            <div id="affectedMilestonesList" class="bg-amber-50 border border-amber-200 rounded-lg p-4 max-h-48 overflow-y-auto">
              <!-- Dynamically populated -->
            </div>
          </div>

          <!-- Extension Type -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">
              Submit As <span class="text-red-500">*</span>
            </label>
            <div class="space-y-3">
              <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                <input 
                  type="radio" 
                  name="extension_type" 
                  value="admin_override"
                  class="mt-1 w-4 h-4 text-blue-600 focus:ring-blue-500"
                  checked
                >
                <div class="flex-1">
                  <p class="font-semibold text-gray-900">Admin Override</p>
                  <p class="text-xs text-gray-600 mt-1">Extension takes effect immediately. Use admin authority to extend timeline without approval.</p>
                </div>
              </label>
              
              <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all">
                <input 
                  type="radio" 
                  name="extension_type" 
                  value="request_behalf"
                  class="mt-1 w-4 h-4 text-blue-600 focus:ring-blue-500"
                >
                <div class="flex-1">
                  <p class="font-semibold text-gray-900">Request on Behalf</p>
                  <p class="text-xs text-gray-600 mt-1">Submit extension request that requires property owner approval. Follows normal approval workflow.</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Error Display -->
          <div id="extendTimelineError" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start gap-2 text-red-700">
              <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-sm" id="extendTimelineErrorMessage"></p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <button 
              type="button" 
              onclick="hideExtendTimelineModal()"
              class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all duration-200"
            >
              Cancel
            </button>
            <button 
              type="submit"
              id="submitExtensionBtn"
              class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>Submit Extension</span>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
