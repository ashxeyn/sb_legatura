  <!-- Halt Details Modal -->
  <div id="haltDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="px-6 py-5 flex-shrink-0 relative border-b border-gray-200 bg-gradient-to-r from-rose-50 to-red-50 rounded-t-2xl">
          <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center shadow-xl ring-4 ring-white/50">
                <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div>
                <h2 class="text-xl font-bold tracking-wide text-gray-900">Halt Details</h2>
                <p class="text-xs text-gray-500">Administrative information for halted project status</p>
              </div>
            </div>
            <button onclick="hideHaltDetailsModal()" class="w-10 h-10 rounded-xl hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition-all duration-200 text-rose-600 hover:rotate-90 transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6 overflow-y-auto flex-1">
          <div class="space-y-6">
            <!-- Primary Fields -->
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Initiated By</label>
                  <p id="haltInitiatedBy" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Cause of Halt</label>
                  <p id="haltCauseOfHalt" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Reason of Halt</label>
                  <p id="haltReasonOfHalt" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide mb-1">Remarks</label>
                  <textarea id="haltRemarks" rows="5" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none" placeholder="Add administrative remarks about the halt..."></textarea>
                </div>
              </div>
              <div class="space-y-4">
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Date of Halt Notice</label>
                  <p id="haltNoticeDate" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Affected Milestone</label>
                  <p id="haltAffectedMilestone" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Status of Issue</label>
                  <p id="haltIssueStatus" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Expected Resolution Date</label>
                  <p id="haltExpectedResolutionDate" class="text-sm font-medium text-gray-900 mt-1">—</p>
                </div>
              </div>
            </div>

            <!-- Supporting Files -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-gray-900">Supporting Files</h4>
                  <p class="text-xs text-gray-500">Documentation provided for administrative review.</p>
                </div>
                <button onclick="downloadHaltSupportingFiles()" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">Download all</button>
              </div>
              <div id="haltSupportingFiles" class="flex flex-col gap-2"></div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
          <button onclick="showCancelHaltConfirm()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition shadow-sm hover:shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Mark as Cancelled
          </button>
          <button onclick="showResumeHaltConfirm()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm hover:shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Resume Project
          </button>
        </div>
      </div>
    </div>
  </div>
