<!-- Admin Payment History Modal -->
<div id="adminPaymentHistoryModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[70] hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-4xl max-h-[90vh] rounded-2xl shadow-2xl relative transform transition-all duration-300 flex flex-col">
      
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 px-6 py-5 rounded-t-2xl flex-shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <div class="text-white">
              <h3 class="text-lg font-bold">Payment History</h3>
              <p class="text-xs opacity-90" id="paymentHistoryProjectTitle">Project Payment Details</p>
            </div>
          </div>
          <button onclick="closeAdminPaymentHistoryModal()" class="w-10 h-10 rounded-xl hover:bg-white/20 active:bg-white/30 flex items-center justify-center transition-all duration-200 text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="flex-1 overflow-y-auto p-6 space-y-6">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
            <p class="text-xs font-semibold text-blue-700 mb-1">Total Project Cost</p>
            <p class="text-2xl font-bold text-blue-900" id="paymentHistoryTotalCost">₱0.00</p>
          </div>
          <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
            <p class="text-xs font-semibold text-green-700 mb-1">Total Paid</p>
            <p class="text-2xl font-bold text-green-900" id="paymentHistoryTotalPaid">₱0.00</p>
          </div>
          <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 border border-amber-200">
            <p class="text-xs font-semibold text-amber-700 mb-1">Remaining Balance</p>
            <p class="text-2xl font-bold text-amber-900" id="paymentHistoryRemaining">₱0.00</p>
          </div>
        </div>

        <!-- Payment List -->
        <div>
          <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Payment Transactions
          </h4>
          
          <div id="paymentHistoryList" class="space-y-3">
            <!-- Payment items will be inserted here -->
          </div>

          <!-- Empty State -->
          <div id="paymentHistoryEmpty" class="hidden text-center py-12">
            <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
              <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <p class="text-gray-600 font-semibold mb-1">No Payment History</p>
            <p class="text-sm text-gray-500">Payment records will appear here once submitted</p>
          </div>
        </div>

      </div>

      <!-- Footer -->
      <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex-shrink-0">
        <div class="flex justify-end">
          <button 
            onclick="closeAdminPaymentHistoryModal()"
            class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg"
          >
            Close
          </button>
        </div>
      </div>

    </div>
  </div>
</div>
