  <!-- Completion Details Modal -->
  <div id="completionDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 via-emerald-500 to-green-600 px-6 py-5 flex-shrink-0 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-50"></div>
          <div class="flex items-center justify-between relative z-10">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm shadow-xl ring-4 ring-white/30">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="text-white">
                <h2 class="text-xl font-bold tracking-wide">Completion Details</h2>
                <p class="text-xs opacity-90">Project verification and feedback information</p>
              </div>
            </div>
            <button onclick="hideCompletionDetailsModal()" class="w-10 h-10 rounded-xl hover:bg-white/30 active:bg-white/40 flex items-center justify-center transition-all duration-200 text-white hover:rotate-90 transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6 overflow-y-auto flex-1">
          <!-- Completion Details Section -->
          <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div class="bg-gradient-to-br from-white to-green-50 border border-green-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-green-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Project Status</label>
                </div>
                <p class="text-sm font-bold text-gray-900 pl-6">{{ ucwords(str_replace('_', ' ', $completionData['project_status'] ?? 'Unknown')) }}</p>
              </div>
              <div class="bg-gradient-to-br from-white to-green-50 border border-green-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-green-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Date Completed</label>
                </div>
                <p class="text-sm font-bold text-gray-900 pl-6">{{ $completionData['completion_date'] ?? '—' }}</p>
              </div>
              <div class="bg-gradient-to-br from-white to-green-50 border border-green-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 group">
                <div class="flex items-center gap-2 mb-2">
                  <svg class="w-4 h-4 text-green-500 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <label class="text-xs text-gray-600 font-semibold uppercase tracking-wide">Total Duration</label>
                </div>
                <p class="text-sm font-bold text-gray-900 pl-6">{{ $completionData['duration_text'] ?? '—' }}</p>
              </div>
            </div>

            <div class="space-y-4">
              <!-- Additional Info Card -->
              <div class="bg-gradient-to-br from-white to-purple-50 border border-purple-200 rounded-xl p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-start gap-3">
                  <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600 font-semibold mb-1">Project Completion</p>
                    <p class="text-xs text-gray-500 leading-relaxed">All project milestones have been successfully completed and verified by the system administrator.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Feedbacks Section -->
          <div class="border-t-2 border-gray-200 pt-6">
            <div class="flex items-center gap-2 mb-5">
              <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
              </svg>
              <h3 class="text-lg font-bold text-gray-900">Project Feedbacks</h3>
            </div>

            @if(!empty($completionData['reviews']) && count($completionData['reviews']) > 0)
              @foreach($completionData['reviews'] as $review)
                <!-- {{ $review['role_label'] }} Feedback -->
                @if($review['role'] === 'property_owner')
                  <!-- Property Owner Feedback -->
                  <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-200 rounded-xl p-5 mb-4 hover:shadow-xl transition-all duration-300 hover:border-amber-300">
                    <div class="flex items-start justify-between mb-3">
                      <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
                          <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                          </svg>
                        </div>
                        <div>
                          <p class="font-bold text-gray-900">{{ $review['name'] }}</p>
                          <p class="text-xs text-amber-600 font-semibold">{{ $review['role_label'] }}</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                          <svg class="w-5 h-5 {{ $i <= ($review['rating'] ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                          </svg>
                        @endfor
                      </div>
                    </div>
                    <textarea readonly class="w-full border border-amber-200 rounded-lg px-4 py-3 text-sm text-gray-700 bg-white resize-none focus:outline-none focus:ring-2 focus:ring-amber-300 transition-all" rows="3" placeholder="No feedback provided">{{ $review['comment'] ?? 'No feedback provided' }}</textarea>
                  </div>
                @else
                  <!-- Contractor Feedback -->
                  <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-200 rounded-xl p-5 mb-4 hover:shadow-xl transition-all duration-300 hover:border-blue-300">
                    <div class="flex items-start justify-between mb-3">
                      <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                          </svg>
                        </div>
                        <div>
                          <p class="font-bold text-gray-900">{{ $review['name'] }}</p>
                          <p class="text-xs text-blue-600 font-semibold">{{ $review['role_label'] }}</p>
                        </div>
                      </div>
                      <div class="flex items-center gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                          <svg class="w-5 h-5 {{ $i <= ($review['rating'] ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20">
                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                          </svg>
                        @endfor
                      </div>
                    </div>
                    <textarea readonly class="w-full border border-blue-200 rounded-lg px-4 py-3 text-sm text-gray-700 bg-white resize-none focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all" rows="3" placeholder="No feedback provided">{{ $review['comment'] ?? 'No feedback provided' }}</textarea>
                  </div>
                @endif
              @endforeach
            @else
              <!-- No Reviews Message -->
              <div class="bg-gray-50 border border-gray-200 rounded-xl p-8 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                <p class="text-gray-500 font-medium">No feedback available for this project yet.</p>
              </div>
            @endif
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl flex justify-end flex-shrink-0">
          <button onclick="hideCompletionDetailsModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
