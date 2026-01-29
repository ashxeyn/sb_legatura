  <!-- Progress Report Detail Modal -->
  <div id="progressReportModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
            Progress Report Detail
            <button id="editReportBtn" class="text-amber-500 hover:text-amber-600 transition-colors" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
              </svg>
            </button>
          </h2>
          <button onclick="hideProgressReportModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-rose-500 hover:text-rose-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-5 max-h-[calc(100vh-12rem)] overflow-y-auto">
          <!-- Report Info -->
          <div>
            <h3 id="reportTitle" class="text-base font-bold text-gray-900 mb-1">Rooftop Building Progress Report</h3>
            <p id="reportDate" class="text-sm text-gray-500 mb-3">12 Dec 9:00 PM</p>
            <p id="reportDescription" class="text-sm text-gray-600 leading-relaxed">
              People care about how you see the world, how you think, what motivates you, what you're struggling with or afraid of.People care about how you see the world, how you think, what motivates you, what you're struggling with or afraid of.
            </p>
          </div>

          <!-- File History -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-sm font-bold text-gray-900">File History</h4>
                <p class="text-xs text-gray-500">Download your previous plan receipts and usage details.</p>
              </div>
              <button id="downloadAllBtn" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                Download all
              </button>
            </div>

            <!-- File History Table -->
            <div class="rounded-lg border border-gray-200 overflow-hidden">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr class="text-left">
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600 w-10">
                      <input type="checkbox" id="selectAllFiles" class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500 cursor-pointer">
                    </th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600">Files</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600">Date Submitted</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600">Uploaded By</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-600 text-center">Action</th>
                  </tr>
                </thead>
                <tbody id="fileHistoryTable" class="divide-y divide-gray-200 bg-white">
                  <!-- Rows will be injected by JS -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end">
          <button onclick="hideProgressReportModal()" class="px-5 py-2.5 text-sm font-medium rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
