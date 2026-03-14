{{-- Project Summary Modal (mirrors mobile projectSummary.tsx) --}}
{{-- Used for: in_progress and terminated projects --}}
<div id="projectSummaryModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center overflow-y-auto py-8 px-4">
    <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl relative my-4">

      {{-- Header --}}
      <div id="psmHeader" class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 px-6 py-5 rounded-t-2xl relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
        <div class="flex items-center justify-between relative z-10">
          <div>
            <h2 id="psmProjectTitle" class="text-lg font-bold text-white tracking-wide">Project Summary</h2>
            <p class="text-xs text-white/80 mt-0.5">Full lifecycle overview</p>
          </div>
          <button onclick="hideProjectSummaryModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      {{-- Body --}}
      <div id="psmBody" class="p-6 space-y-4 max-h-[calc(100vh-12rem)] overflow-y-auto">
        <div class="flex items-center justify-center py-12">
          <div class="text-center">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-sm text-gray-500">Loading summary…</p>
          </div>
        </div>
      </div>

      {{-- Footer --}}
      <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end">
        <button onclick="hideProjectSummaryModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Close
        </button>
      </div>
    </div>
  </div>
</div>
