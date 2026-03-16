<!-- Admin Payment History Modal -->
<div id="adminPaymentHistoryModal" class="fixed inset-0 bg-black/45 backdrop-blur-sm z-[70] hidden">
  <style>
    #adminPaymentHistoryModal .ph-scroll::-webkit-scrollbar {
      display: none;
    }
  </style>
  <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4 lg:p-6">
    <div class="bg-white w-full max-w-5xl max-h-[92vh] rounded-[1.25rem] shadow-[0_20px_60px_rgba(15,23,42,0.14)] border border-slate-200 flex flex-col overflow-hidden">
      
      <!-- Header -->
      <div class="px-4 sm:px-5 py-4 rounded-t-[1.25rem] border-b border-slate-200 bg-white flex-shrink-0">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 ring-1 ring-slate-200 shadow-sm flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <h3 class="text-sm sm:text-base font-bold text-slate-800">Payment History</h3>
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Project Ledger</span>
              </div>
              <p class="text-[10px] text-slate-500" id="paymentHistoryProjectTitle">Project Payment Details</p>
            </div>
          </div>
          <button onclick="closeAdminPaymentHistoryModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition text-slate-600 border border-slate-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="ph-scroll flex-1 overflow-y-auto p-3 sm:p-4 lg:p-5 space-y-3 bg-slate-50" style="scrollbar-width:none;-ms-overflow-style:none;">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5">
          <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total Project Cost</p>
            <p class="text-lg font-bold text-slate-900 mt-1" id="paymentHistoryTotalCost">₱0.00</p>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total Paid</p>
            <p class="text-lg font-bold text-green-700 mt-1" id="paymentHistoryTotalPaid">₱0.00</p>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Remaining Balance</p>
            <p class="text-lg font-bold text-amber-700 mt-1" id="paymentHistoryRemaining">₱0.00</p>
          </div>
        </div>

        <!-- Payment List -->
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
          <div class="px-3 py-2 border-b border-slate-200 bg-slate-50">
            <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              Payment Transactions
            </h4>
          </div>

          <div class="p-3">
            <div id="paymentHistoryList" class="space-y-2.5">
              <!-- Payment items will be inserted here -->
            </div>

            <!-- Empty State -->
            <div id="paymentHistoryEmpty" class="hidden text-center py-8">
              <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2.5">
                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
              </div>
              <p class="text-xs text-slate-600 font-semibold mb-0.5">No Payment History</p>
              <p class="text-[10px] text-slate-500">Payment records will appear here once submitted</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Footer -->
      <div class="border-t border-slate-200 px-4 sm:px-5 py-3 bg-slate-50 rounded-b-[1.25rem] flex-shrink-0">
        <div class="flex justify-end">
          <button 
            onclick="closeAdminPaymentHistoryModal()"
            class="px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-100 transition flex items-center gap-1.5 shadow-sm"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </button>
        </div>
      </div>

    </div>
  </div>
</div>
