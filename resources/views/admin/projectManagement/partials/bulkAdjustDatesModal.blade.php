<!-- Bulk Adjust Dates Modal -->
<div id="bulkAdjustDatesModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center overflow-y-auto py-8 px-4">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl relative my-4 transform transition-all duration-300">
      
      <!-- Header -->
      <div class="bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-600 px-6 py-5 rounded-t-2xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
        <div class="flex items-center justify-between relative z-10">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <div class="text-white">
              <h3 class="text-lg font-bold tracking-wide">Bulk Date Adjustment</h3>
              <p class="text-xs opacity-90">Shift all incomplete milestone dates</p>
            </div>
          </div>
          <button onclick="hideBulkAdjustDatesModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6 max-h-[calc(100vh-12rem)] overflow-y-auto">
        
        <!-- Form -->
        <form id="bulkAdjustDatesForm" class="space-y-5">
          <input type="hidden" id="bulkAdjustProjectId" name="project_id">

          <!-- Days Input -->
          <div>
            <label for="bulkAdjustDays" class="block text-sm font-semibold text-gray-700 mb-2">
              Shift all milestones by <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center gap-3">
              <input 
                type="number" 
                id="bulkAdjustDays" 
                name="days"
                min="1"
                max="365"
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                placeholder="Enter number of days"
                required
              >
              <span class="text-sm font-semibold text-gray-700">days</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Enter a value between 1 and 365 days</p>
          </div>

          <!-- Direction -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">
              Direction <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
              <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-500 hover:bg-purple-50 transition-all">
                <input 
                  type="radio" 
                  name="direction" 
                  value="forward"
                  class="w-4 h-4 text-purple-600 focus:ring-purple-500"
                  checked
                >
                <div class="flex-1">
                  <p class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Forward
                  </p>
                  <p class="text-xs text-gray-600 mt-1">Push dates into the future</p>
                </div>
              </label>
              
              <label class="flex items-center gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-500 hover:bg-purple-50 transition-all">
                <input 
                  type="radio" 
                  name="direction" 
                  value="backward"
                  class="w-4 h-4 text-purple-600 focus:ring-purple-500"
                >
                <div class="flex-1">
                  <p class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                    </svg>
                    Backward
                  </p>
                  <p class="text-xs text-gray-600 mt-1">Pull dates earlier</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Reason -->
          <div>
            <label for="bulkAdjustReason" class="block text-sm font-semibold text-gray-700 mb-2">
              Reason for Adjustment <span class="text-red-500">*</span>
            </label>
            <textarea 
              id="bulkAdjustReason" 
              name="reason"
              rows="4"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all resize-none"
              placeholder="Provide a detailed reason for the bulk date adjustment (minimum 10 characters)..."
              required
              minlength="10"
            ></textarea>
            <p class="text-xs text-gray-500 mt-1">
              <span id="bulkReasonCharCount">0</span>/500 characters (minimum 10)
            </p>
          </div>

          <!-- Preview Section -->
          <div id="bulkAdjustPreviewSection" class="hidden">
            <div class="bg-purple-50 border-2 border-purple-200 rounded-lg p-5">
              <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview Changes
              </h4>
              
              <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-white rounded-lg p-3">
                  <p class="text-xs text-gray-600 mb-1">Affected Milestones</p>
                  <p class="text-2xl font-bold text-purple-600" id="bulkAffectedCount">0</p>
                </div>
                <div class="bg-white rounded-lg p-3">
                  <p class="text-xs text-gray-600 mb-1">New Project End Date</p>
                  <p class="text-sm font-bold text-gray-900" id="bulkNewEndDate">—</p>
                </div>
              </div>
              
              <div id="bulkPreviewList" class="bg-white rounded-lg p-4 max-h-64 overflow-y-auto space-y-2">
                <!-- Dynamically populated -->
              </div>
            </div>
          </div>

          <!-- Warning -->
          <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <div class="flex items-start gap-2 text-amber-800">
              <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <div class="text-sm">
                <p class="font-semibold mb-1">Important Notice</p>
                <p>This will adjust ALL incomplete milestone dates. Completed milestones will not be affected. This action cannot be undone.</p>
              </div>
            </div>
          </div>

          <!-- Error Display -->
          <div id="bulkAdjustError" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start gap-2 text-red-700">
              <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-sm" id="bulkAdjustErrorMessage"></p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t">
            <button 
              type="button" 
              onclick="hideBulkAdjustDatesModal()"
              class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all duration-200"
            >
              Cancel
            </button>
            <button 
              type="button"
              id="previewBulkAdjustBtn"
              onclick="previewBulkAdjustment()"
              class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <span>Preview Changes</span>
            </button>
            <button 
              type="submit"
              id="submitBulkAdjustBtn"
              class="px-6 py-2.5 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-lg hover:from-purple-600 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <span>Apply Changes</span>
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>


<!-- Bulk Adjust Confirmation Modal -->
<div id="bulkAdjustConfirmModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100">
      
      <!-- Header -->
      <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-5 rounded-t-2xl">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div class="text-white">
            <h3 class="text-lg font-bold">Confirm Bulk Adjustment</h3>
            <p class="text-xs opacity-90">This action cannot be undone</p>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-4">
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
          <p class="text-sm text-gray-700 leading-relaxed">
            You are about to adjust <span class="font-bold text-amber-700" id="confirmAffectedCount">0</span> incomplete milestone dates by <span class="font-bold text-amber-700" id="confirmDaysCount">0</span> days <span id="confirmDirection" class="font-bold text-amber-700">forward</span>.
          </p>
          <p class="text-sm text-gray-700 mt-3 leading-relaxed">
            <span class="font-semibold">Reason:</span><br>
            <span class="italic" id="confirmReason">—</span>
          </p>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex items-start gap-2 text-red-800">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-sm">
              <p class="font-semibold mb-1">Warning</p>
              <p>This will permanently adjust all incomplete milestone dates. Completed milestones will not be affected. This action cannot be undone.</p>
            </div>
          </div>
        </div>

        <p class="text-sm text-gray-600 text-center">
          Are you sure you want to proceed?
        </p>
      </div>

      <!-- Actions -->
      <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
        <button 
          type="button"
          onclick="hideBulkAdjustConfirmModal()"
          class="px-6 py-2.5 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition-all duration-200"
        >
          Cancel
        </button>
        <button 
          type="button"
          onclick="confirmBulkAdjustment()"
          class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          <span>Yes, Apply Changes</span>
        </button>
      </div>
    </div>
  </div>
</div>
