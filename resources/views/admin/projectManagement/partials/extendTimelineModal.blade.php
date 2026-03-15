<!-- Extend Timeline Modal -->
<div id="extendTimelineModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl relative flex flex-col" style="max-height:90vh;">
      
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-4 py-3 rounded-t-xl flex-shrink-0 relative overflow-hidden">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <div class="text-white">
              <h3 class="text-sm font-bold">Extend Project Timeline</h3>
              <p class="text-[10px] opacity-80">Adjust project end date</p>
            </div>
          </div>
          <button onclick="hideExtendTimelineModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <style>.extend-scroll::-webkit-scrollbar{display:none}</style>
      <div class="extend-scroll p-4 space-y-4 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">
        
        <!-- Current Timeline Info -->
        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-3">
          <h4 class="text-xs font-bold text-gray-900 mb-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Current Timeline
          </h4>
          <div class="grid grid-cols-3 gap-2 text-[10px]">
            <div class="bg-white rounded p-2 border border-indigo-100">
              <p class="text-gray-400 mb-0.5">Start Date</p>
              <p class="font-semibold text-gray-900 truncate" id="extendCurrentStart">—</p>
            </div>
            <div class="bg-white rounded p-2 border border-indigo-100">
              <p class="text-gray-400 mb-0.5">End Date</p>
              <p class="font-semibold text-gray-900 truncate" id="extendCurrentEnd">—</p>
            </div>
            <div class="bg-white rounded p-2 border border-indigo-100">
              <p class="text-gray-400 mb-0.5">Duration</p>
              <p class="font-semibold text-indigo-600 truncate" id="extendCurrentDuration">—</p>
            </div>
          </div>
        </div>

        <!-- Extension Form -->
        <form id="extendTimelineForm" class="space-y-3">
          <input type="hidden" id="extendProjectId" name="project_id">
          <input type="hidden" id="extendCurrentEndDate" name="current_end_date">

          <!-- New End Date -->
          <div>
            <label for="extendNewEndDate" class="block text-xs font-semibold text-gray-700 mb-1.5">
              New End Date <span class="text-red-500">*</span>
            </label>
            <input 
              type="date" 
              id="extendNewEndDate" 
              name="new_end_date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
              required
            >
            <p class="text-[9px] text-gray-400 mt-0.5">Must be after current end date</p>
          </div>

          <!-- Extension Duration Display -->
          <div id="extensionDurationDisplay" class="hidden bg-indigo-50 border border-indigo-200 rounded-lg p-2.5">
            <div class="flex items-center gap-1.5 text-indigo-700 text-xs font-semibold">
              <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span>Adding <span id="extensionDays">0</span> days</span>
            </div>
          </div>

          <!-- Reason -->
          <div>
            <label for="extendReason" class="block text-xs font-semibold text-gray-700 mb-1.5">
              Reason <span class="text-red-500">*</span>
            </label>
            <textarea 
              id="extendReason" 
              name="reason"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none"
              placeholder="Why extend the timeline? (min 10 chars)"
              required
              minlength="10"
            ></textarea>
            <p class="text-[9px] text-gray-400 mt-0.5">
              <span id="reasonCharCount">0</span>/500
            </p>
          </div>

          <!-- Affected Milestones -->
          <div id="affectedMilestonesSection" class="hidden">
            <h4 class="text-xs font-bold text-gray-900 mb-1.5 flex items-center gap-1">
              <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              Affected Milestones: <span id="affectedMilestonesCount">0</span>
            </h4>
            <div id="affectedMilestonesList" class="bg-amber-50 border border-amber-200 rounded-lg p-2.5 max-h-32 overflow-y-auto text-[10px]">
              <!-- Dynamically populated -->
            </div>
          </div>

          <!-- Extension Type -->
          <div>
            <label class="block text-xs font-semibold text-gray-700 mb-2">
              Submit As <span class="text-red-500">*</span>
            </label>
            <div class="space-y-2">
              <label class="flex items-start gap-2 p-2.5 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 transition-all">
                <input 
                  type="radio" 
                  name="extension_type" 
                  value="admin_override"
                  class="mt-0.5 w-4 h-4 text-indigo-600 focus:ring-indigo-500"
                  checked
                >
                <div class="flex-1">
                  <p class="text-xs font-semibold text-gray-900">Admin Override</p>
                  <p class="text-[9px] text-gray-600 mt-0.5">Takes effect immediately</p>
                </div>
              </label>
              
              <label class="flex items-start gap-2 p-2.5 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 transition-all">
                <input 
                  type="radio" 
                  name="extension_type" 
                  value="request_behalf"
                  class="mt-0.5 w-4 h-4 text-indigo-600 focus:ring-indigo-500"
                >
                <div class="flex-1">
                  <p class="text-xs font-semibold text-gray-900">Request on Behalf</p>
                  <p class="text-[9px] text-gray-600 mt-0.5">Requires owner approval</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Error Display -->
          <div id="extendTimelineError" class="hidden bg-red-50 border border-red-200 rounded-lg p-2.5">
            <div class="flex items-start gap-1.5 text-red-700">
              <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-xs" id="extendTimelineErrorMessage"></p>
            </div>
          </div>

        </form>

      </div>

      <!-- Footer (Fixed at Bottom) -->
      <div class="border-t border-gray-200 px-4 py-3 bg-white flex justify-end items-center gap-3 flex-shrink-0 rounded-b-xl">
        <button 
          type="button" 
          onclick="hideExtendTimelineModal()"
          class="px-3.5 py-2 border border-gray-300 text-gray-700 bg-white text-xs font-semibold rounded-lg hover:bg-gray-100 transition-all hover:shadow-md flex items-center gap-1.5"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Cancel
        </button>
        <button 
          type="submit"
          id="submitExtensionBtn"
          form="extendTimelineForm"
          class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-all hover:shadow-md flex items-center gap-1.5 whitespace-nowrap"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          <span>Submit</span>
        </button>
      </div>

    </div>
  </div>
</div>
