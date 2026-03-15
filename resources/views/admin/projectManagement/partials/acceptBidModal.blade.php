<!-- Accept Bid Confirmation Modal -->
<div id="acceptBidModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-xl shadow-2xl relative flex flex-col">

      <!-- Header -->
      <div class="bg-gradient-to-r from-emerald-600 to-emerald-500 px-4 py-3 rounded-t-xl flex-shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center ring-2 ring-white/30 flex-shrink-0">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div class="text-white">
              <h3 class="text-sm font-bold leading-tight">Accept Bid</h3>
              <p class="text-[10px] opacity-80">Confirm bid acceptance</p>
            </div>
          </div>
          <button onclick="hideAcceptBidModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      @isset($bid)
      <!-- Content -->
      <div class="p-3 space-y-2.5">
        <p class="text-[11px] text-gray-600 leading-relaxed">
          Are you sure you want to accept this bid from <span class="font-semibold text-gray-900">{{ $bid->company_name ?? 'N/A' }}</span>?
        </p>
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-2.5 space-y-1">
          <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-emerald-100">
            <span class="text-[11px] text-gray-500">Bid Amount</span>
            <span class="text-[11px] font-semibold text-emerald-700">&#x20B1;{{ number_format($bid->proposed_cost, 2) }}</span>
          </div>
          <div class="flex justify-between items-center py-0.5 px-1 rounded hover:bg-emerald-100">
            <span class="text-[11px] text-gray-500">Duration</span>
            <span class="text-[11px] font-semibold text-gray-900">{{ $bid->estimated_timeline ?? 'N/A' }} months</span>
          </div>
        </div>
        <p class="text-[10px] text-gray-400 leading-relaxed">
          <strong class="text-gray-500">Note:</strong> Accepting this bid will automatically update the bidding status and notify the contractor.
        </p>
      </div>

      <!-- Footer -->
      <div class="border-t border-gray-200 px-3 py-2.5 bg-gray-50 rounded-b-xl flex items-center justify-end gap-2 flex-shrink-0">
        <button onclick="hideAcceptBidModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 transition-colors">
          Cancel
        </button>
        <button type="button" data-bid-id="{{ $bid->bid_id }}" onclick="confirmAcceptBid(Number(this.dataset.bidId))" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Accept Bid
        </button>
      </div>
      @else
      <div class="p-6 text-center text-gray-500 text-sm">
        <svg class="mx-auto h-10 w-10 text-gray-300 animate-spin mb-3" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-[11px] text-gray-400">Loading bid summary...</p>
      </div>
      @endisset

    </div>
  </div>
</div>