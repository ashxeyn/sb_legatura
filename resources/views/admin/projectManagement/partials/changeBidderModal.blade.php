<!-- Change Bidder Confirmation Modal -->
<div id="changeBidderModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] hidden">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative transform transition-all">
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
            <i class="fi fi-rr-refresh text-amber-600"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900">Change Selected Bidder</h3>
        </div>
        <button onclick="hideChangeBidderModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-4">
        <p class="text-sm text-gray-600 leading-relaxed">
          You are about to change the selected contractor to
          <span id="changeBidderContractorName" class="font-semibold text-gray-900"></span>.
        </p>
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800 flex items-start gap-2">
          <i class="fi fi-rr-triangle-warning mt-0.5 flex-shrink-0"></i>
          <span>This will update the selected contractor for this project. The previous contractor's bid will be marked as pending again. No milestones have been set up yet, so this change is safe.</span>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
        <button onclick="hideChangeBidderModal()" class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
          Cancel
        </button>
        <button id="confirmChangeBidderBtn" onclick="confirmChangeBidder()" class="px-4 py-2.5 text-sm font-medium rounded-lg bg-amber-600 hover:bg-amber-700 text-white transition shadow-md flex items-center gap-2">
          <i class="fi fi-rr-refresh text-sm"></i>
          Confirm Change
        </button>
      </div>
    </div>
  </div>
</div>
