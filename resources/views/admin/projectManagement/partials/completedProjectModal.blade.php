  <!-- Completed Project Modal -->
  <div id="completedProjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden" data-project-id="{{ $project->project_id ?? '' }}">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-5xl rounded-xl shadow-2xl relative flex flex-col" style="max-height:90vh;">
        <!-- Header with Owner Info -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-4 py-3 rounded-t-xl flex-shrink-0">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div id="completedOwnerAvatar" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center overflow-hidden ring-2 ring-white/30"></div>
              <div class="text-white">
                <h3 id="completedOwnerName" class="text-sm font-bold">Property Owner</h3>
                <p class="text-[10px] opacity-80 flex items-center gap-1.5">
                  <span class="inline-block w-1.5 h-1.5 bg-green-300 rounded-full"></span>
                  Completed Project
                  <span id="completedDate" class="ml-1">—</span>
                </p>
              </div>
            </div>
            <button type="button" onclick="hideCompletedProjectModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <style>.completed-scroll::-webkit-scrollbar{display:none}</style>
        <div class="completed-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">

          <!-- Success Banner -->
          <div class="bg-white border border-gray-200 rounded-lg p-2.5 flex items-center gap-3">
            <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-green-800">Project Successfully Completed</p>
              <p class="text-[10px] text-green-600 italic">All milestones verified and marked as completed.</p>
            </div>
            <button onclick="toggleCompletedDetails()" class="flex-shrink-0 flex items-center gap-1 px-2.5 py-1 bg-green-500 hover:bg-green-600 text-white text-[10px] font-semibold rounded-md transition-colors">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              View Details
            </button>
          </div>

          <!-- Project Details and Contractor Details (2-Column) -->
          <div id="completedDetailsSection" class="grid lg:grid-cols-2 gap-3">
            <!-- Project Details -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Project Details
              </h3>
              <div class="space-y-0.5 text-[11px]">
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                  <span class="text-gray-500">Project Title</span>
                  <span id="completedProjectTitle" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                  <span class="text-gray-500">Property Address</span>
                  <span id="completedProjectAddress" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                  <span class="text-gray-500">Property Type</span>
                  <span id="completedProjectType" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                  <span class="text-gray-500">Lot Size (sqm)</span>
                  <span id="completedLotSize" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                  <span class="text-gray-500">Target Timeline</span>
                  <span id="completedTimeline" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                  <span class="text-gray-500">Budget</span>
                  <span id="completedBudget" class="font-semibold text-green-600 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-gray-50">
                  <span class="text-gray-500">Bidding Deadline</span>
                  <span id="completedDeadline" class="font-semibold text-gray-900 text-right">—</span>
                </div>
              </div>
              <div class="pt-1">
                <span class="text-[10px] text-gray-400 block mb-1">Uploaded Photos</span>
                <div id="completedPhotos" class="flex flex-wrap gap-1.5"></div>
              </div>
              <div>
                <span class="text-[10px] text-gray-400 block mb-1">Supporting Files</span>
                <div id="completedFiles" class="flex flex-wrap gap-1.5"></div>
              </div>
            </div>

            <!-- Contractor Details -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Contractor Details
              </h3>
              <div class="space-y-0.5 text-[11px]">
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                  <span class="text-gray-500">Company Name</span>
                  <span id="completedContractorName" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                  <span class="text-gray-500">Email Address</span>
                  <span id="completedContractorEmail" class="font-semibold text-blue-600 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                  <span class="text-gray-500">PCAB No.</span>
                  <span id="completedContractorPcab" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                  <span class="text-gray-500">PCAB Category</span>
                  <span id="completedContractorCategory" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                  <span class="text-gray-500">PCAB Expiration</span>
                  <span id="completedContractorPcabExpiry" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                  <span class="text-gray-500">Business Permit No.</span>
                  <span id="completedContractorPermit" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                  <span class="text-gray-500">Permit City</span>
                  <span id="completedContractorCity" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                  <span class="text-gray-500">Permit Expiration</span>
                  <span id="completedContractorPermitExpiry" class="font-semibold text-gray-900 text-right">—</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-blue-50">
                  <span class="text-gray-500">TIN Registration</span>
                  <span id="completedContractorTin" class="font-semibold text-gray-900 text-right">—</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Project's Milestone and Details (Row) -->
          <div class="grid lg:grid-cols-2 gap-3">
            <!-- Project's Milestone -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Project's Milestone
              </h3>
              <div id="completedMilestoneTimeline" class="space-y-0">
                <!-- Milestone items will be injected by JS -->
              </div>
            </div>

            <!-- Details -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1 flex flex-col">
              <div class="flex items-center justify-between pb-2 border-b border-gray-200 flex-shrink-0">
                <h3 class="font-bold text-gray-900 text-xs flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Details
                </h3>
                <button id="editMilestoneBtn" onclick="openEditMilestoneModal(window.selectedMilestoneItemId)" class="text-green-600 hover:text-green-700 text-[10px] font-semibold flex items-center gap-1 hidden" title="Edit Details">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                </button>
              </div>
              <div id="completedDetails" class="space-y-2 flex-1 overflow-y-auto min-h-0">
                <div class="flex items-center justify-center py-8 text-center" id="noMilestoneSelected">
                  <div>
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-xs text-gray-400 font-semibold">Click a milestone to view details</p>
                  </div>
                </div>
                <div id="milestoneDetailsContent" class="hidden space-y-2">
                  <div class="space-y-1">
                    <h4 id="milestoneDetailName" class="text-xs font-bold text-gray-900 border-b border-gray-200 pb-1"></h4>
                    <p id="milestoneDetailDates" class="text-[10px] text-gray-500"></p>
                    <p id="milestoneDetailDescription" class="text-[10px] text-gray-600 leading-relaxed"></p>
                  </div>

                  <div id="milestoneItemsSection" class="hidden">
                    <h5 class="text-[10px] font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Milestone Items</h5>
                    <div id="milestoneItemsList" class="space-y-1"></div>
                  </div>

                  <div id="progressReportsSection" class="hidden">
                    <h5 class="text-[10px] font-bold text-gray-700 mb-1.5 uppercase tracking-wide">Progress Reports</h5>
                    <div id="progressReportsList" class="space-y-1"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Summary -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-2">
            <div class="flex items-center justify-between border-b border-gray-200 pb-2">
              <div>
                <h3 class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                  <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  Payment Summary
                </h3>
                <p class="text-[10px] text-gray-400 mt-0.5">Uploaded receipts and payment confirmations for completed milestones</p>
              </div>
            </div>

            <!-- Stats grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2">
              <div class="bg-white rounded p-2 border border-gray-200">
                <div class="flex items-center justify-between mb-1">
                  <p class="text-[10px] text-gray-500 font-medium">Milestones Paid</p>
                  <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="completedPaidCount" class="text-sm font-bold text-gray-900">—</p>
              </div>
              <div class="bg-white rounded p-2 border border-gray-200">
                <div class="flex items-center justify-between mb-1">
                  <p class="text-[10px] text-gray-500 font-medium">Total Amount Paid</p>
                  <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="completedTotalAmount" class="text-sm font-bold text-green-600">—</p>
              </div>
              <div class="bg-white rounded p-2 border border-gray-200">
                <div class="flex items-center justify-between mb-1">
                  <p class="text-[10px] text-gray-500 font-medium">Last Payment Date</p>
                  <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <p id="completedLastPaymentDate" class="text-xs font-semibold text-gray-900">—</p>
              </div>
              <div class="bg-white rounded p-2 border border-gray-200">
                <div class="flex items-center justify-between mb-1">
                  <p class="text-[10px] text-gray-500 font-medium">Overall Status</p>
                  <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <p id="completedOverallStatus" class="text-xs font-bold text-green-600">—</p>
              </div>
            </div>

            <div class="rounded-lg border border-gray-200 overflow-hidden">
              <table class="w-full text-[10px]">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Milestone</th>
                    <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Period</th>
                    <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Amount Paid</th>
                    <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Date</th>
                    <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Uploaded By</th>
                    <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Proof</th>
                    <th class="text-left px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Status</th>
                  </tr>
                </thead>
                <tbody id="completedPaymentTable" class="divide-y divide-gray-200 bg-white">
                  <!-- Rows injected by JS -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- Project Summary Section (collapsible) -->
          <div id="completedProjectSummarySection" class="hidden space-y-2">
            <div class="flex items-center gap-1.5 border-b border-gray-200 pb-2">
              <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              <h3 class="text-xs font-bold text-gray-900">Project Summary</h3>
            </div>
            <div id="completedProjectSummaryContent" class="space-y-2">
              <div class="flex items-center justify-center py-6">
                <p class="text-xs text-gray-400">Loading summary…</p>
              </div>
            </div>
          </div>

        </div>{{-- end scrollable body --}}

        <!-- Footer -->
        <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 rounded-b-xl flex justify-between items-center gap-3 flex-shrink-0">
          <button onclick="toggleCompletedProjectSummary()" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span id="completedSummaryToggleLabel">View Project Summary</span>
          </button>
          <button onclick="hideCompletedProjectModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
