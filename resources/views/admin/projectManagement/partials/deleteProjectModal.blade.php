  <!-- Delete Project Confirmation Modal -->
<div id="deleteProjectModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-3 sm:p-4" data-project-id="{{ $project->project_id }}">
  <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content">

    <!-- Modal Header -->
    <div class="sticky top-0 bg-gradient-to-r from-rose-500 to-rose-600 px-4 sm:px-5 py-3 flex items-center justify-between rounded-t-2xl shadow-lg z-10">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm flex-shrink-0">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </div>
        <div>
          <h2 class="text-base font-bold text-white leading-tight">Delete Project</h2>
          <p class="text-[10px] text-rose-200 mt-0.5">This action cannot be undone</p>
        </div>
      </div>
      <button onclick="hideDeleteProjectModal()" class="text-white hover:text-rose-100 transition-all p-1.5 rounded-lg hover:bg-white hover:bg-opacity-20 active:scale-95">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Modal Body -->
    <div class="overflow-y-auto max-h-[calc(84vh-118px)] p-4 sm:p-5 space-y-3" style="scrollbar-width:none;-ms-overflow-style:none;">

      <!-- Warning + Project Info -->
      <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 space-y-2.5">
        <div class="flex items-start gap-2.5">
          <div class="w-7 h-7 bg-rose-500 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-sm font-semibold text-gray-800 mb-0.5">Confirm Project Deletion</h3>
            <p class="text-xs text-gray-700 leading-relaxed">
              Are you sure you want to delete <span class="font-bold text-rose-600">{{ $project->project_title }}</span>?
            </p>
          </div>
        </div>

        <!-- Project Info Card -->
        <div class="bg-white rounded-lg p-2.5 border border-rose-200 space-y-1.5">
          <div>
            <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wide">Project Title</p>
            <p class="text-xs font-semibold text-gray-900">{{ $project->project_title }}</p>
          </div>
          <div class="grid grid-cols-2 gap-1.5 pt-1.5 border-t border-gray-200">
            <div>
              <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wide">Owner</p>
              <p class="text-xs text-gray-800 font-medium">{{ $project->owner_name ?? '—' }}</p>
            </div>
            <div>
              <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wide">Status</p>
              <p class="text-xs text-gray-800 font-medium">{{ ucfirst(str_replace('_', ' ', $project->project_status)) }}</p>
            </div>
            @if($project->contractor_name)
            <div class="col-span-2 pt-1.5 border-t border-gray-200">
              <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wide">Contractor</p>
              <p class="text-xs text-gray-800 font-medium">{{ $project->contractor_name }}</p>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Deletion Reason -->
      <div>
        <label for="deleteReason" class="block text-xs font-semibold text-gray-800 mb-1.5 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
          </svg>
          Deletion Reason <span class="text-rose-500">*</span>
        </label>
        <textarea
          id="deleteReason"
          rows="3"
          placeholder="Please provide a reason for deleting this project..."
          class="w-full px-2 py-1.5 text-[11px] border border-gray-300 rounded-md focus:ring-2 focus:ring-red-300 focus:border-red-300 focus:outline-none transition resize-none"
        ></textarea>
        <p class="text-red-500 text-[11px] mt-1 hidden" id="error-delete-reason">Reason is required.</p>
        <p class="text-[11px] text-gray-500 mt-1 flex items-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          This reason will be recorded in the project audit log.
        </p>
      </div>

      <!-- Consequences Warning -->
      <div class="bg-yellow-50 border-l-4 border-yellow-500 p-2.5 rounded-r-lg">
        <div class="flex gap-2">
          <svg class="w-4 h-4 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          <div class="text-xs text-gray-700 space-y-1">
            <p class="font-semibold text-gray-800 text-xs">Deletion Consequences:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
              <li>Project status will be marked as deleted</li>
              <li>All milestones will be marked as deleted</li>
              <li>All milestone items will be marked as deleted</li>
              <li>Deletion reason will be recorded in audit log</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white border-t border-gray-200 px-4 sm:px-5 py-3 rounded-b-2xl flex items-center justify-end gap-2">
      <button onclick="hideDeleteProjectModal()" class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 hover:border-gray-400 hover:shadow-sm hover:-translate-y-0.5 transition-all font-semibold active:scale-95 text-xs">
        Cancel
      </button>
      <button onclick="confirmDeleteProject()" class="px-4 py-2 bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white rounded-lg transition-all font-semibold shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 flex items-center gap-1.5 text-xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        Delete Project
      </button>
    </div>
  </div>
</div>
