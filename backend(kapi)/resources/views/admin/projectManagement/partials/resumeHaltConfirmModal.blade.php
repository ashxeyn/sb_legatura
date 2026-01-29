  <!-- Resume Halt Confirmation Modal -->
  <div id="resumeHaltConfirmModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Resume Halted Project</h3>
          </div>
          <button onclick="hideResumeHaltConfirm()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-sm text-gray-600">Are you sure you want to <span class="font-semibold text-gray-900">resume</span> work on this halted project? Progress tracking and milestones will proceed from the last verified state.</p>
          <p class="text-xs text-gray-500"><strong>Note:</strong> This will notify all project stakeholders that the halt has been lifted.</p>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
          <button onclick="hideResumeHaltConfirm()" class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition">No, Keep Halt</button>
          <button onclick="confirmResumeHalt()" class="px-4 py-2.5 text-sm font-medium rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-md">Yes, Resume Project</button>
        </div>
      </div>
    </div>
  </div>
