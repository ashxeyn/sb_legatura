{{-- Project Summary Modal --}}
{{-- Used for: in_progress and terminated projects --}}
<div id="projectSummaryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
  <div class="absolute inset-0 flex items-center justify-center py-4 px-4">
    <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl flex flex-col max-h-[90vh]">

      {{-- Header --}}
      <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-4 py-3 rounded-t-xl flex-shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
              <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div>
              <h2 id="psmProjectTitle" class="text-sm font-bold text-white leading-tight">Project Summary</h2>
              <p class="text-[10px] text-indigo-200 mt-0.5">Full lifecycle overview</p>
            </div>
          </div>
          <button onclick="hideProjectSummaryModal()" class="w-7 h-7 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      {{-- Body --}}
      <div id="psmBody" class="flex-1 p-3 space-y-2.5 overflow-y-auto" style="scrollbar-width:none;-ms-overflow-style:none;">
        <style>#psmBody::-webkit-scrollbar{display:none}</style>
        <div class="flex items-center justify-center py-8">
          <div class="text-center">
            <svg class="w-5 h-5 text-gray-300 mx-auto mb-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-[10px] text-gray-400">Loading summary…</p>
          </div>
        </div>
      </div>

      {{-- Footer --}}
      <div class="border-t border-gray-200 px-4 py-2.5 bg-gray-50 rounded-b-xl flex justify-end flex-shrink-0">
        <button onclick="hideProjectSummaryModal()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 transition flex items-center gap-1.5">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Close
        </button>
      </div>
    </div>
  </div>
</div>
