  <!-- Edit Milestone Details Modal -->
  <div id="editMilestoneModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300" data-item-id="{{ $item->item_id }}">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
          <div>
            <h2 class="text-lg font-bold text-gray-900">Edit Milestone Details</h2>
            <p class="text-xs text-gray-500 mt-0.5">Update the project</p>
          </div>
          <button onclick="hideEditMilestoneModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-rose-500 hover:text-rose-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4 overflow-y-auto flex-1">
          <!-- Milestone Group Name (Read-only) -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Milestone Group Name</label>
            <input type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-gray-50 cursor-not-allowed" value="{{ $item->milestone_name }}" readonly>
          </div>

          <!-- Item Title -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Item Title <span class="text-rose-600">*</span></label>
            <input type="text" id="editMilestoneItemTitle" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="{{ $item->milestone_item_title }}">
            <p class="text-xs text-red-600 mt-1 hidden" id="error-milestone-item-title"></p>
          </div>

          <!-- Item Description -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Item Description <span class="text-rose-600">*</span></label>
            <textarea id="editMilestoneItemDescription" rows="5" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none">{{ $item->milestone_item_description }}</textarea>
            <p class="text-xs text-red-600 mt-1 hidden" id="error-milestone-item-description"></p>
          </div>

          <!-- Date to Finish -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Date to Finish <span class="text-rose-600">*</span></label>
            <input type="date" id="editMilestoneItemDate" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="{{ $item->date_to_finish ? \Carbon\Carbon::parse($item->date_to_finish)->format('Y-m-d') : '' }}">
            <p class="text-xs text-red-600 mt-1 hidden" id="error-date-to-finish"></p>
          </div>

          <!-- Estimated Cost -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Estimated Cost <span class="text-rose-600">*</span></label>
            <input type="number" id="editMilestoneItemCost" step="0.01" min="0" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" value="{{ $item->milestone_item_cost }}">
            <p class="text-xs text-red-600 mt-1 hidden" id="error-milestone-item-cost"></p>
          </div>

          <!-- Item Status -->
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Item Status <span class="text-rose-600">*</span></label>
            <select id="editMilestoneItemStatus" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
              <option value="pending" {{ $item->item_status === 'pending' ? 'selected' : '' }}>Pending</option>
              <option value="not_started" {{ $item->item_status === 'not_started' ? 'selected' : '' }}>Not Started</option>
              <option value="in_progress" {{ $item->item_status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
              <option value="delayed" {{ $item->item_status === 'delayed' ? 'selected' : '' }}>Delayed</option>
              <option value="completed" {{ $item->item_status === 'completed' ? 'selected' : '' }}>Completed</option>
              <option value="cancelled" {{ $item->item_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
              <option value="halt" {{ $item->item_status === 'halt' ? 'selected' : '' }}>Halt</option>
              <option value="deleted" {{ $item->item_status === 'deleted' ? 'selected' : '' }}>Deleted</option>
            </select>
            <p class="text-xs text-red-600 mt-1 hidden" id="error-item-status"></p>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end flex-shrink-0">
          <button onclick="saveMilestoneEdit()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white transition shadow-md hover:shadow-lg">
            Done
          </button>
        </div>
      </div>
    </div>
  </div>
