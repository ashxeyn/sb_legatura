<!-- Reject Bid Confirmation Modal -->
<div id="rejectBidModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl relative transform transition-all">
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900">Reject Bid</h3>
        </div>
        <button onclick="hideRejectBidModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      @isset($bid)
      <!-- Content -->
      <div class="p-6 space-y-4">
        <p class="text-sm text-gray-600 leading-relaxed">
          Are you sure you want to reject this bid from <span class="font-semibold text-gray-900">{{ $bid->company_name ?? 'N/A' }}</span>?
        </p>
        <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Bid Amount:</span>
            <span class="font-semibold text-gray-900">₱{{ number_format($bid->proposed_cost, 2) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Duration:</span>
            <span class="font-semibold text-gray-900">{{ $bid->estimated_timeline ?? 'N/A' }} months</span>
          </div>
        </div>
        <div class="space-y-2">
          <label class="text-xs font-medium text-gray-700">Reason for Rejection (Optional)</label>
          <textarea id="rejectReason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none" placeholder="Provide a reason for rejecting this bid..."></textarea>
        </div>
        <p class="text-xs text-gray-500">
          <strong>Note:</strong> This action cannot be undone. The contractor will be notified of the rejection.
        </p>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
        <button onclick="hideRejectBidModal()" class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
          Cancel
        </button>
        <button onclick="confirmRejectBid({{ $bid->bid_id }})" class="px-4 py-2.5 text-sm font-medium rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition shadow-md">
          Reject Bid
        </button>
      </div>
      @else
      <div class="p-6">
        <div class="flex items-center justify-center py-12">
          <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-4 text-sm text-gray-500">Loading bid summary...</p>
          </div>
        </div>
      </div>
      @endisset
    </div>
  </div>
</div>
