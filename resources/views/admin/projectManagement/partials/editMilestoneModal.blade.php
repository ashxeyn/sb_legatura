  <!-- Edit Milestone Details Modal -->
<style>
#editMilestoneModal       { transition: opacity 0.25s ease, backdrop-filter 0.25s ease; }
#editMilestoneModal.em-enter { opacity: 0; }
#editMilestoneModal.em-visible { opacity: 1; }
#editMilestoneModal.em-leave  { opacity: 0; }
#editMilestoneModal .em-panel { transition: opacity 0.25s ease; }
#editMilestoneModal.em-enter .em-panel { opacity: 0; }
#editMilestoneModal.em-visible .em-panel { opacity: 1; }
#editMilestoneModal.em-leave .em-panel  { opacity: 0; }
</style>
  <div id="editMilestoneModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden" data-item-id="{{ $item->item_id }}">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="em-panel bg-white w-full max-w-md rounded-2xl shadow-2xl relative max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 flex-shrink-0">
          <div>
            <h2 class="text-sm font-bold text-gray-900">Edit Milestone Details</h2>
            <p class="text-[10px] text-gray-500 mt-0.5">Update the project</p>
          </div>
          <button onclick="hideEditMilestoneModal()" class="w-7 h-7 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-rose-500 hover:text-rose-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-4 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">
          <style>#editMilestoneModal .em-panel::-webkit-scrollbar{display:none}</style>

          <!-- Validation Error Message -->
          <div id="editMilestoneValidationError" class="hidden p-2.5 rounded-lg border border-red-300 bg-red-50">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
              </svg>
              <p class="text-xs font-semibold text-red-700">Please make at least one change to save.</p>
            </div>
          </div>

          <!-- Extension Info Badge (if extended) -->
          @if($item->was_extended)
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5 flex items-start gap-2">
            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
              <p class="text-xs font-semibold text-blue-900">Date Extended {{ $item->extension_count }} time(s)</p>
              <p class="text-[9px] text-blue-700 mt-0.5">
                Original: {{ $item->original_date_to_finish ? \Carbon\Carbon::parse($item->original_date_to_finish)->format('M d, Y') : 'N/A' }}
                → Current: {{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('M d, Y') : 'N/A' }}
              </p>
            </div>
          </div>
          @endif

          <!-- Milestone Group Name (Read-only) -->
          <div>
            <label class="block text-xs font-semibold text-gray-900 mb-1.5">Milestone Group Name</label>
            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs bg-gray-50 cursor-not-allowed" value="{{ $item->milestone_name }}" readonly>
          </div>

          <!-- Item Title -->
          <div>
            <label class="block text-xs font-semibold text-gray-900 mb-1.5">Item Title <span class="text-rose-600">*</span></label>
            <input type="text" id="editMilestoneItemTitle" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent milestone-change-detector" value="{{ $item->milestone_item_title }}">
            <p class="text-[9px] text-red-600 mt-0.5 hidden" id="error-milestone-item-title"></p>
          </div>

          <!-- Item Description -->
          <div>
            <label class="block text-xs font-semibold text-gray-900 mb-1.5">Item Description <span class="text-rose-600">*</span></label>
            <textarea id="editMilestoneItemDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none milestone-change-detector">{{ $item->milestone_item_description }}</textarea>
            <p class="text-[9px] text-red-600 mt-0.5 hidden" id="error-milestone-item-description"></p>
          </div>

          <!-- Date to Finish -->
          <div>
            <label class="block text-xs font-semibold text-gray-900 mb-1.5">Date to Finish <span class="text-rose-600">*</span></label>
            <input type="date" id="editMilestoneItemDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent milestone-change-detector" value="{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('Y-m-d') : '' }}">
            <p class="text-[9px] text-red-600 mt-0.5 hidden" id="error-date-to-finish"></p>
          </div>

          <!-- Estimated Cost -->
          <div>
            <label class="block text-xs font-semibold text-gray-900 mb-1.5">Estimated Cost <span class="text-rose-600">*</span></label>
            <input type="number" id="editMilestoneItemCost" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent milestone-change-detector" value="{{ $item->milestone_item_cost }}">
            <p class="text-[9px] text-red-600 mt-0.5 hidden" id="error-milestone-item-cost"></p>
          </div>

          <!-- Item Status -->
          <div>
            <label class="block text-xs font-semibold text-gray-900 mb-1.5">Item Status <span class="text-rose-600">*</span></label>
            <select id="editMilestoneItemStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent milestone-change-detector">
              <option value="not_started" {{ $item->item_status === 'not_started' ? 'selected' : '' }}>Not Started</option>
              <option value="in_progress" {{ $item->item_status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
              <option value="delayed" {{ $item->item_status === 'delayed' ? 'selected' : '' }}>Delayed</option>
              <option value="completed" {{ $item->item_status === 'completed' ? 'selected' : '' }}>Completed</option>
              <option value="cancelled" {{ $item->item_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              <option value="halt" {{ $item->item_status === 'halt' ? 'selected' : '' }}>Halt</option>
            </select>
            <p class="text-[9px] text-red-600 mt-0.5 hidden" id="error-item-status"></p>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 rounded-b-2xl flex justify-end flex-shrink-0">
          <button onclick="saveMilestoneEdit()" class="px-4 py-2 text-xs font-semibold rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white transition shadow-md hover:shadow-lg">
            Done
          </button>
        </div>
      </div>
    </div>
  </div>

<script>
// Toggle collapsible sections in edit milestone modal
function toggleSection(sectionId) {
  const content = document.getElementById(sectionId + 'Content');
  const chevron = document.getElementById(sectionId + 'Chevron');
  
  if (content && chevron) {
    if (content.classList.contains('hidden')) {
      content.classList.remove('hidden');
      chevron.style.transform = 'rotate(180deg)';
    } else {
      content.classList.add('hidden');
      chevron.style.transform = 'rotate(0deg)';
    }
  }
}
</script>
