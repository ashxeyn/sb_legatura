  <!-- Completion Details Modal -->
  <div id="completionDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl relative max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-4 py-3 flex-shrink-0">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center ring-2 ring-white/30">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="text-white">
                <h2 class="text-sm font-bold">Completion Details</h2>
                <p class="text-[10px] opacity-80">Project verification and feedback information</p>
              </div>
            </div>
            <button onclick="hideCompletionDetailsModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <style>.cdm-scroll::-webkit-scrollbar{display:none}</style>
        <div class="cdm-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">
          <!-- Completion Details Section -->
          <div class="grid md:grid-cols-2 gap-3">
            <div class="space-y-2">
              <div class="bg-green-50 border border-green-100 rounded-lg p-2">
                <div class="flex items-center gap-1.5 mb-0.5">
                  <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Project Status</label>
                </div>
                <p class="text-xs font-bold text-gray-900 pl-5">{{ ucwords(str_replace('_', ' ', $completionData['project_status'] ?? 'Unknown')) }}</p>
              </div>
              <div class="bg-green-50 border border-green-100 rounded-lg p-2">
                <div class="flex items-center gap-1.5 mb-0.5">
                  <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Date Completed</label>
                </div>
                <p class="text-xs font-bold text-gray-900 pl-5">{{ $completionData['completion_date'] ?? '—' }}</p>
              </div>
              <div class="bg-green-50 border border-green-100 rounded-lg p-2">
                <div class="flex items-center gap-1.5 mb-0.5">
                  <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <label class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide">Total Duration</label>
                </div>
                <p class="text-xs font-bold text-gray-900 pl-5">{{ $completionData['duration_text'] ?? '—' }}</p>
              </div>
            </div>
            <div>
              <div class="bg-purple-50 border border-purple-100 rounded-lg p-2 flex items-start gap-2">
                <div class="w-6 h-6 rounded bg-purple-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                  <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-[10px] text-gray-600 font-semibold mb-0.5">Project Completion</p>
                  <p class="text-[10px] text-gray-500 leading-relaxed">All project milestones have been successfully completed and verified by the system administrator.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Feedbacks Section -->
          <div class="border-t border-gray-200 pt-2.5">
            <div class="flex items-center gap-1.5 mb-2">
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
              </svg>
              <h3 class="text-xs font-bold text-gray-900">Project Feedbacks</h3>
            </div>

            @if(!empty($completionData['reviews']) && count($completionData['reviews']) > 0)
              @foreach($completionData['reviews'] as $review)
                @if($review['role'] === 'property_owner')
                  <div class="bg-amber-50 border border-amber-200 rounded-lg p-2.5 mb-2">
                    <div class="flex items-start justify-between mb-2">
                      <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                          <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                          </svg>
                        </div>
                        <div>
                          <p class="text-xs font-bold text-gray-900">{{ $review['name'] }}</p>
                          <p class="text-[10px] text-amber-600 font-semibold">{{ $review['role_label'] }}</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-0.5">
                        @for ($i = 1; $i <= 5; $i++)
                          <svg class="w-3 h-3 {{ $i <= ($review['rating'] ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                          </svg>
                        @endfor
                      </div>
                    </div>
                    <textarea readonly class="w-full border border-amber-200 rounded-md px-2 py-1.5 text-[10px] text-gray-700 bg-white resize-none focus:outline-none" rows="2">{{ $review['comment'] ?? 'No feedback provided' }}</textarea>
                  </div>
                @else
                  <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5 mb-2">
                    <div class="flex items-start justify-between mb-2">
                      <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                          <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                          </svg>
                        </div>
                        <div>
                          <p class="text-xs font-bold text-gray-900">{{ $review['name'] }}</p>
                          <p class="text-[10px] text-blue-600 font-semibold">{{ $review['role_label'] }}</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-0.5">
                        @for ($i = 1; $i <= 5; $i++)
                          <svg class="w-3 h-3 {{ $i <= ($review['rating'] ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                          </svg>
                        @endfor
                      </div>
                    </div>
                    <textarea readonly class="w-full border border-blue-200 rounded-md px-2 py-1.5 text-[10px] text-gray-700 bg-white resize-none focus:outline-none" rows="2">{{ $review['comment'] ?? 'No feedback provided' }}</textarea>
                  </div>
                @endif
              @endforeach
            @else
              <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-center">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                <p class="text-xs text-gray-400">No feedback available for this project yet.</p>
              </div>
            @endif
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 rounded-b-xl flex justify-end flex-shrink-0">
          <button onclick="hideCompletionDetailsModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
