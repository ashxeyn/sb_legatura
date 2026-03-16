{{-- Project Summary Modal --}}
{{-- Used for: in_progress and terminated projects --}}
<div id="projectSummaryModal" class="fixed inset-0 bg-black/45 backdrop-blur-sm z-50 hidden">
  <style>
    #projectSummaryModal .psm-modal-body::-webkit-scrollbar {
      display: none;
    }
  </style>
  <div class="absolute inset-0 flex items-center justify-center p-3 sm:p-4 lg:p-6">
    <div class="bg-white w-full max-w-5xl rounded-[1.25rem] shadow-[0_20px_60px_rgba(15,23,42,0.14)] border border-slate-200 flex flex-col max-h-[92vh] overflow-hidden">

      {{-- Header --}}
      <div class="px-4 sm:px-5 py-4 rounded-t-[1.25rem] flex-shrink-0 border-b border-slate-200 bg-white">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center flex-shrink-0 ring-1 ring-slate-200 shadow-sm">
              <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <h2 id="psmProjectTitle" class="text-sm sm:text-base font-bold text-slate-800 leading-tight">Project Summary</h2>
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">Lifecycle Report</span>
              </div>
              <p class="text-[10px] text-slate-500 mt-0.5">Milestones, finances, payments, and change history in one view</p>
            </div>
          </div>
          <button onclick="hideProjectSummaryModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition text-slate-600 border border-slate-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      {{-- Body --}}
      <div id="psmBody" class="psm-modal-body flex-1 p-3 sm:p-4 lg:p-5 space-y-2.5 overflow-y-auto bg-slate-50" style="scrollbar-width:none;-ms-overflow-style:none;">
        <div class="flex items-center justify-center py-14">
          <div class="rounded-2xl border border-slate-200 bg-white px-8 py-7 text-center shadow-sm">
            <svg class="w-8 h-8 text-slate-300 mx-auto mb-3 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-xs font-semibold text-slate-600">Loading project summary...</p>
            <p class="text-[11px] text-slate-400 mt-1">Preparing milestones, payments, and history</p>
          </div>
        </div>
      </div>

      {{-- Footer --}}
      <div class="border-t border-slate-200 px-4 sm:px-5 py-3 bg-slate-50 rounded-b-[1.25rem] flex items-center justify-between gap-3 flex-shrink-0">
        <p class="hidden sm:block text-[11px] text-slate-500">Review the full project timeline before closing.</p>
        <button onclick="hideProjectSummaryModal()" class="px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-100 transition flex items-center gap-1.5 shadow-sm">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Close Summary
        </button>
      </div>
    </div>
  </div>
</div>
