  <!-- Cancel Halt Confirmation Modal -->
  <div id="cancelHaltConfirmModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Cancel Halted Project</h3>
          </div>
          <button onclick="hideCancelHaltConfirm()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600">Are you sure you want to mark this halted project as <span class="font-semibold text-gray-900">terminated</span>? This action is irreversible and will archive all related data.</p>
          <p class="text-xs text-gray-500 mb-3"><strong>Note:</strong> Stakeholders will be notified about the termination status update.</p>
          <div>
            <label for="cancelHaltRemarks" class="block text-sm font-semibold text-gray-700 mb-2">Reason for Termination <span class="text-red-500">*</span></label>
            <textarea id="cancelHaltRemarks" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none" placeholder="Provide a detailed reason for terminating this halted project..." required></textarea>
            <p id="cancelHaltRemarksError" class="text-xs text-red-600 mt-1 hidden"></p>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
          <button onclick="hideCancelHaltConfirm()" class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition">No, Keep Halt</button>
          <button onclick="confirmCancelHalt()" class="px-4 py-2.5 text-sm font-medium rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition shadow-md">Yes, Terminate Project</button>
        </div>
      </div>
    </div>
  </div>
