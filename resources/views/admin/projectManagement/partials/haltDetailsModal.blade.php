  <!-- Halt Details Modal -->
  <div id="haltDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300" @isset($project) data-project-id="{{ $project->project_id ?? '' }}" @endisset>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl relative transform transition-all duration-300 scale-100 flex flex-col" style="max-height:90vh;">
        <!-- Header -->
        <div class="bg-gradient-to-r from-rose-500 to-red-600 px-4 py-3 rounded-t-xl flex-shrink-0">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center ring-2 ring-white/30">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div class="text-white">
                <h2 class="text-sm font-bold">Halt Details</h2>
                <p class="text-[10px] opacity-80">Administrative information for halted project</p>
              </div>
            </div>
            <button onclick="hideHaltDetailsModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <style>.halt-detail-scroll::-webkit-scrollbar{display:none}</style>
        <div class="halt-detail-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">
          @isset($project)
          <!-- Primary Fields -->
          <div class="grid md:grid-cols-2 gap-3">
            <!-- Left Column -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-rose-500 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                Halt Information
              </h3>
              <div class="space-y-0.5 text-[11px]">
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-rose-50">
                  <span class="text-gray-500">Initiated By</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->initiated_by ?? 'Unknown' }}</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-rose-50">
                  <span class="text-gray-500">Date of Halt Notice</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->created_at ? \Carbon\Carbon::parse($project->created_at)->format('M j, Y') : '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-rose-50">
                  <span class="text-gray-500">Affected Milestone</span>
                  <span class="font-semibold text-gray-900 text-right">{{ $project->milestone_item_title ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-rose-50">
                  <span class="text-gray-500">Status of Issue</span>
                  <span class="text-right">
                    <span class="inline-flex px-1.5 py-0.5 text-[9px] font-semibold rounded-full
                      @if($project->dispute_status === 'open') bg-amber-100 text-amber-700
                      @elseif($project->dispute_status === 'under_review') bg-blue-100 text-blue-700
                      @elseif($project->dispute_status === 'resolved') bg-green-100 text-green-700
                      @elseif($project->dispute_status === 'closed') bg-gray-100 text-gray-700
                      @else bg-red-100 text-red-700
                      @endif">
                      {{ ucfirst(str_replace('_', ' ', $project->dispute_status)) }}
                    </span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
              <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-md bg-amber-500 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                Reason & Remarks
              </h3>
              <div class="space-y-1.5">
                <div class="px-1">
                  <span class="text-[10px] text-gray-500 block mb-0.5">Reason of Halt</span>
                  <p class="text-[11px] font-semibold text-gray-900 leading-relaxed">{{ $project->dispute_desc ?? '—' }}</p>
                </div>
                <div class="px-1">
                  <span class="text-[10px] text-gray-500 block mb-0.5">Remarks</span>
                  <div class="bg-gray-50 border border-gray-200 rounded-lg px-2 py-1.5">
                    <p class="text-[11px] text-gray-700 leading-relaxed">{{ $project->project_remarks ?? 'No remarks provided' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Supporting Files -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-md bg-blue-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
              </div>
              Supporting Files
            </h3>
            <div class="space-y-1">
              @forelse($project->supporting_files as $file)
                <a href="{{ asset('storage/' . $file->storage_path) }}" target="_blank" class="flex items-center gap-2.5 p-1.5 rounded-lg border border-gray-200 hover:bg-rose-50 hover:border-rose-200 transition-all group">
                  <div class="w-7 h-7 rounded-md bg-rose-100 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-200 transition-colors">
                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold text-gray-900 truncate">{{ $file->original_name }}</p>
                    <p class="text-[10px] text-gray-400">{{ $file->mime_type }} • {{ number_format($file->size / 1024, 2) }} KB</p>
                  </div>
                  <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-rose-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                  </svg>
                </a>
              @empty
                <div class="flex items-center justify-center py-4 text-center">
                  <div>
                    <svg class="w-6 h-6 text-gray-200 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-[10px] text-gray-400">No supporting files uploaded</p>
                  </div>
                </div>
              @endforelse
            </div>
          </div>
          @else
          <div class="flex items-center justify-center py-8 text-center">
            <div>
              <svg class="w-8 h-8 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <p class="text-[10px] text-gray-400">No halt details available</p>
            </div>
          </div>
          @endisset
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 rounded-b-xl flex justify-end gap-2 flex-shrink-0">
          <button onclick="showCancelHaltConfirm()" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Mark as Terminated
          </button>
          <button onclick="showResumeHaltConfirm()" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Resume Project
          </button>
          </button>
        </div>
      </div>
    </div>
  </div>
