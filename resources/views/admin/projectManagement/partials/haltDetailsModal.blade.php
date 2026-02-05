  <!-- Halt Details Modal -->
  <div id="haltDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300" @isset($project) data-project-id="{{ $project->project_id ?? '' }}" @endisset>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="px-6 py-5 flex-shrink-0 relative border-b border-gray-200 bg-gradient-to-r from-rose-50 to-red-50 rounded-t-2xl">
          <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center shadow-xl ring-4 ring-white/50">
                <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div>
                <h2 class="text-xl font-bold tracking-wide text-gray-900">Halt Details</h2>
                <p class="text-xs text-gray-500">Administrative information for halted project status</p>
              </div>
            </div>
            <button onclick="hideHaltDetailsModal()" class="w-10 h-10 rounded-xl hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center transition-all duration-200 text-rose-600 hover:rotate-90 transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6 overflow-y-auto flex-1">
          @isset($project)
          <div class="space-y-6">
            <!-- Primary Fields -->
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Initiated By</label>
                  <p class="text-sm font-medium text-gray-900 mt-1">{{ $project->initiated_by ?? 'Unknown' }}</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Reason of Halt</label>
                  <p class="text-sm font-medium text-gray-900 mt-1">{{ $project->dispute_desc ?? '—' }}</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide mb-1">Remarks</label>
                  <textarea rows="5" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none" placeholder="Add administrative remarks about the halt..." readonly>{{ $project->project_remarks ?? '' }}</textarea>
                </div>
              </div>
              <div class="space-y-4">
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Date of Halt Notice</label>
                  <p class="text-sm font-medium text-gray-900 mt-1">{{ $project->created_at ? \Carbon\Carbon::parse($project->created_at)->format('F j, Y') : '—' }}</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Affected Milestone</label>
                  <p class="text-sm font-medium text-gray-900 mt-1">{{ $project->milestone_item_title ?? '—' }}</p>
                </div>
                <div>
                  <label class="text-xs font-semibold text-gray-600 tracking-wide">Status of Issue</label>
                  <p class="text-sm font-medium text-gray-900 mt-1">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                      @if($project->dispute_status === 'open') bg-amber-100 text-amber-700
                      @elseif($project->dispute_status === 'under_review') bg-blue-100 text-blue-700
                      @elseif($project->dispute_status === 'resolved') bg-green-100 text-green-700
                      @elseif($project->dispute_status === 'closed') bg-gray-100 text-gray-700
                      @else bg-red-100 text-red-700
                      @endif">
                      {{ ucfirst(str_replace('_', ' ', $project->dispute_status)) }}
                    </span>
                  </p>
                </div>
              </div>
            </div>

            <!-- Supporting Files -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-bold text-gray-900">Supporting Files</h4>
                  <p class="text-xs text-gray-500">Documentation provided for administrative review.</p>
                </div>
              </div>
              <div class="flex flex-col gap-2">
                @forelse($project->supporting_files as $file)
                  <a href="{{ asset('storage/' . $file->storage_path) }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 hover:border-rose-300 transition-all duration-200 group">
                    <div class="w-10 h-10 rounded-lg bg-rose-100 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-200 transition-colors">
                      <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                      </svg>
                    </div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900">{{ $file->original_name }}</p>
                      <p class="text-xs text-gray-500">{{ $file->mime_type }} • {{ number_format($file->size / 1024, 2) }} KB</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-rose-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                  </a>
                @empty
                  <div class="text-center py-6 text-sm text-gray-500">
                    No supporting files uploaded
                  </div>
                @endforelse
              </div>
            </div>
          </div>
          @else
          <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500 text-sm">No halt details available</p>
          </div>
          @endisset
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
          <button onclick="showCancelHaltConfirm()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition shadow-sm hover:shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Mark as Cancelled
          </button>
          <button onclick="showResumeHaltConfirm()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm hover:shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Resume Project
          </button>
        </div>
      </div>
    </div>
  </div>
