<!-- Bulk Adjust Dates Modal -->
<style>.bulk-scroll::-webkit-scrollbar{display:none}</style>
<div id="bulkAdjustDatesModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl relative flex flex-col" style="max-height:90vh;">

      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-3 rounded-t-xl flex-shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-xs font-bold text-white">Bulk Date Adjustment</h3>
              <p class="text-[10px] text-white/70">Shift all incomplete milestone dates</p>
            </div>
          </div>
          <button onclick="hideBulkAdjustDatesModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="bulk-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">
        <form id="bulkAdjustDatesForm" class="space-y-3">
          <input type="hidden" id="bulkAdjustProjectId" name="project_id">

          <!-- Days + Direction (inline row) -->
          <div class="grid grid-cols-2 gap-3">
            <!-- Days -->
            <div>
              <label for="bulkAdjustDays" class="block text-xs font-semibold text-gray-700 mb-1">
                Shift by <span class="text-rose-500">*</span>
              </label>
              <div class="flex items-center gap-1.5">
                <input
                  type="number"
                  id="bulkAdjustDays"
                  name="days"
                  min="1"
                  max="365"
                  class="flex-1 px-2.5 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                  placeholder="e.g. 7"
                  required
                >
                <span class="text-[10px] font-semibold text-gray-500 whitespace-nowrap">days</span>
              </div>
              <p class="text-[9px] text-gray-400 mt-0.5">1 – 365</p>
            </div>

            <!-- Direction -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">
                Direction <span class="text-rose-500">*</span>
              </label>
              <div class="flex flex-col gap-1.5">
                <label class="flex items-center gap-2 px-2.5 py-1.5 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-all">
                  <input type="radio" name="direction" value="forward" class="w-3 h-3 text-indigo-600 focus:ring-indigo-500" checked>
                  <span class="text-xs font-semibold text-gray-800 flex items-center gap-1">
                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Forward
                  </span>
                </label>
                <label class="flex items-center gap-2 px-2.5 py-1.5 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-all">
                  <input type="radio" name="direction" value="backward" class="w-3 h-3 text-indigo-600 focus:ring-indigo-500">
                  <span class="text-xs font-semibold text-gray-800 flex items-center gap-1">
                    <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                    </svg>
                    Backward
                  </span>
                </label>
              </div>
            </div>
          </div>

          <!-- Reason -->
          <div>
            <label for="bulkAdjustReason" class="block text-xs font-semibold text-gray-700 mb-1">
              Reason <span class="text-rose-500">*</span>
            </label>
            <textarea
              id="bulkAdjustReason"
              name="reason"
              rows="3"
              class="w-full px-2.5 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all resize-none"
              placeholder="Explain why dates are being adjusted… (min 10 chars)"
              required
              minlength="10"
            ></textarea>
            <p class="text-[9px] text-gray-400 mt-0.5"><span id="bulkReasonCharCount">0</span>/500</p>
          </div>

          <!-- Preview Section -->
          <div id="bulkAdjustPreviewSection" class="hidden">
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-2.5">
              <h4 class="text-xs font-bold text-indigo-800 mb-2 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview Changes
              </h4>
              <div class="grid grid-cols-2 gap-2 mb-2">
                <div class="bg-white rounded-lg p-2 border border-indigo-100 text-center">
                  <p class="text-[9px] text-gray-400 mb-0.5">Affected</p>
                  <p class="text-sm font-bold text-indigo-600" id="bulkAffectedCount">0</p>
                </div>
                <div class="bg-white rounded-lg p-2 border border-indigo-100 text-center">
                  <p class="text-[9px] text-gray-400 mb-0.5">New End Date</p>
                  <p class="text-xs font-semibold text-gray-900" id="bulkNewEndDate">—</p>
                </div>
              </div>
              <div id="bulkPreviewList" class="bg-white rounded-lg p-2 max-h-28 overflow-y-auto border border-indigo-100 text-[9px] space-y-1">
                <!-- Dynamically populated -->
              </div>
            </div>
          </div>

          <!-- Warning -->
          <div class="bg-amber-50 border border-amber-200 rounded-lg p-2 flex items-center gap-2 text-amber-700">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-[10px] font-semibold">Adjusts all incomplete milestones &mdash; cannot be undone.</p>
          </div>

          <!-- Error -->
          <div id="bulkAdjustError" class="hidden bg-red-50 border border-red-200 rounded-lg p-2 flex items-start gap-1.5 text-red-700">
            <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs" id="bulkAdjustErrorMessage"></p>
          </div>

        </form>
      </div>

      <!-- Footer -->
      <div class="border-t border-gray-200 px-3 py-2.5 bg-gray-50 rounded-b-xl flex justify-end items-center gap-2 flex-shrink-0">
        <button
          type="button"
          onclick="hideBulkAdjustDatesModal()"
          class="px-3 py-1.5 border border-gray-300 text-gray-700 bg-white text-xs font-semibold rounded-lg hover:bg-gray-100 transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          id="previewBulkAdjustBtn"
          onclick="previewBulkAdjustment()"
          class="px-3 py-1.5 bg-white border border-indigo-300 text-indigo-700 hover:bg-indigo-50 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
          Preview
        </button>
        <button
          type="submit"
          id="submitBulkAdjustBtn"
          form="bulkAdjustDatesForm"
          class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Apply
        </button>
      </div>

    </div>
  </div>
</div>


<!-- Bulk Adjust Confirmation Modal -->
<div id="bulkAdjustConfirmModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] hidden">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-xl shadow-2xl relative flex flex-col">

      <!-- Header -->
      <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-3 rounded-t-xl flex-shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-xs font-bold text-white">Confirm Adjustment</h3>
              <p class="text-[10px] text-white/70">This action cannot be undone</p>
            </div>
          </div>
          <button onclick="hideBulkAdjustConfirmModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-3 space-y-2.5">
        <!-- Summary -->
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-2.5 space-y-1.5">
          <div class="flex items-center gap-2">
            <span class="text-[10px] text-gray-500 w-16 flex-shrink-0">Affected</span>
            <span class="text-xs font-bold text-amber-700" id="confirmAffectedCount">0</span>
            <span class="text-[10px] text-gray-500">milestone(s)</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-[10px] text-gray-500 w-16 flex-shrink-0">Shift</span>
            <span class="text-xs font-bold text-amber-700" id="confirmDaysCount">0</span>
            <span class="text-[10px] text-gray-500">days</span>
            <span class="text-xs font-bold text-amber-700" id="confirmDirection">forward</span>
          </div>
          <div class="border-t border-amber-200 pt-1.5">
            <p class="text-[10px] text-gray-500 mb-0.5">Reason</p>
            <p class="text-xs text-gray-700 italic" id="confirmReason">—</p>
          </div>
        </div>

        <!-- Warning -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-2 flex items-center gap-2 text-red-700">
          <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <p class="text-[10px] font-semibold">All incomplete milestones will be permanently adjusted.</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t border-gray-200 px-3 py-2.5 bg-gray-50 rounded-b-xl flex justify-end items-center gap-2 flex-shrink-0">
        <button
          type="button"
          onclick="hideBulkAdjustConfirmModal()"
          class="px-3 py-1.5 border border-gray-300 text-gray-700 bg-white text-xs font-semibold rounded-lg hover:bg-gray-100 transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          onclick="confirmBulkAdjustment()"
          class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Yes, Apply
        </button>
      </div>
    </div>
  </div>
</div>
