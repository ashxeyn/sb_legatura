<!-- Bid Status Modal -->
<div id="bidStatusModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl relative flex flex-col" style="max-height:90vh;">

      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-3 rounded-t-xl flex-shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center ring-2 ring-white/30 flex-shrink-0">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
            <div class="text-white">
              <h3 class="text-sm font-bold leading-tight">Bid Status</h3>
              <p class="text-[10px] opacity-80 flex items-center gap-1.5">
                @isset($bid)
                  <span class="inline-block w-1.5 h-1.5 bg-indigo-300 rounded-full"></span>
                  @php
                    $statusConfig = [
                      'pending'   => ['label' => 'Under Evaluation', 'class' => 'bg-amber-400/30'],
                      'accepted'  => ['label' => 'Accepted',         'class' => 'bg-green-400/30'],
                      'rejected'  => ['label' => 'Rejected',         'class' => 'bg-red-400/30'],
                      'withdrawn' => ['label' => 'Withdrawn',        'class' => 'bg-gray-400/30'],
                    ];
                    $statusInfo = $statusConfig[$bid->bid_status] ?? ['label' => ucfirst($bid->bid_status ?? ''), 'class' => 'bg-indigo-400/30'];
                  @endphp
                  <span class="inline-flex items-center {{ $statusInfo['class'] }} px-1.5 py-0.5 rounded text-[9px] font-semibold">{{ $statusInfo['label'] }}</span>
                  <span>{{ $bid->project_title ?? 'Bid Details' }}</span>
                @else
                  <span>Loading bid details...</span>
                @endisset
              </p>
            </div>
          </div>
          <button onclick="hideBidStatusModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      @isset($bid)
      <style>.bid-status-scroll::-webkit-scrollbar{display:none}</style>
      <div class="bid-status-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">

        <!-- Bidder + Project Information (2-Column) -->
        <div class="grid md:grid-cols-2 gap-3">
          <!-- Bidder Information -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Bidder Information
            </h3>
            <div class="space-y-0.5">
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Company Name</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->company_name ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Email Address</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->contractor_email ?? $bid->company_email ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">PCAB No.</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->picab_number ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">PCAB Category</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->picab_category ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">PCAB Expiration</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->picab_expiration_date ? \Carbon\Carbon::parse($bid->picab_expiration_date)->format('M j, Y') : 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Business Permit No.</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->business_permit_number ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Permit City</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->business_permit_city ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Permit Expiration</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->business_permit_expiration ? \Carbon\Carbon::parse($bid->business_permit_expiration)->format('M j, Y') : 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">TIN Reg. No.</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->tin_business_reg_number ?? 'N/A' }}</span>
              </div>
            </div>
          </div>

          <!-- Project Information -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
              Project Information
            </h3>
            <div class="space-y-0.5">
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Project Title</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->project_title ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Address</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->project_location ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Project Type</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ ucfirst($bid->property_type ?? 'N/A') }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Lot Size</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->lot_size ? number_format($bid->lot_size) . ' sqm' : 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Project Timeline</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->to_finish ? $bid->to_finish . ' months' : 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Budget</span>
                <span class="text-[11px] font-semibold text-indigo-600 text-right">
                  @if($bid->budget_range_min && $bid->budget_range_max)
                    &#x20B1;{{ number_format($bid->budget_range_min) }} &ndash; &#x20B1;{{ number_format($bid->budget_range_max) }}
                  @else N/A @endif
                </span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-[11px] text-gray-500">Bidding Deadline</span>
                <span class="text-[11px] font-semibold text-gray-900 text-right">{{ $bid->bidding_due ? \Carbon\Carbon::parse($bid->bidding_due)->format('M j, Y') : 'N/A' }}</span>
              </div>
            </div>
            <div class="pt-1">
              <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide block mb-1">Uploaded Files</span>
              <div class="flex flex-wrap gap-1.5">
                @forelse($bid->project_files as $file)
                  <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-indigo-600 hover:underline bg-indigo-50 border border-indigo-100 rounded px-2 py-0.5">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    {{ basename($file->file_path) }}
                  </a>
                @empty
                  <p class="text-[10px] text-gray-400 italic">No files uploaded</p>
                @endforelse
              </div>
            </div>
          </div>
        </div>

        <!-- Bid Details -->
        <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-2">
          <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Bid Details
          </h3>
          <div class="grid md:grid-cols-2 gap-3">
            <div class="space-y-2">
              <div>
                <label class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide block mb-1">Proposed Cost (PHP)</label>
                <input type="text" readonly class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-[11px] bg-gray-50 text-indigo-700 font-semibold focus:outline-none" value="&#x20B1;{{ number_format($bid->proposed_cost ?? 0) }}">
              </div>
              <div>
                <label class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide block mb-1">Estimated Timeline</label>
                <input type="text" readonly class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-[11px] bg-gray-50 text-gray-800 focus:outline-none" value="{{ $bid->estimated_timeline ? $bid->estimated_timeline . ' months' : 'N/A' }}">
              </div>
            </div>
            <div>
              <label class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide block mb-1">Description</label>
              <textarea readonly class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-[11px] bg-gray-50 text-gray-700 resize-none focus:outline-none leading-relaxed" style="height:80px;" placeholder="No description provided">{{ $bid->contractor_notes ?? '' }}</textarea>
            </div>
          </div>
        </div>

        <!-- Supporting Files -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
          <div class="px-3 py-2 border-b border-gray-200 bg-gray-50">
            <h3 class="font-bold text-gray-900 text-xs flex items-center gap-1.5">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
              </svg>
              Supporting Files
            </h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-[10px]">
              <thead class="bg-indigo-50 border-b border-indigo-100">
                <tr class="text-left">
                  <th class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">File Name</th>
                  <th class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Date Submitted</th>
                  <th class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 text-center">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($bid->bid_files as $file)
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-2.5 py-1.5 text-gray-800 font-medium">{{ $file->file_name }}</td>
                    <td class="px-2.5 py-1.5 text-gray-500">{{ \Carbon\Carbon::parse($file->uploaded_at)->format('M j, Y') }}</td>
                    <td class="px-2.5 py-1.5 text-center">
                      <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-[10px] font-medium transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="px-2.5 py-6 text-center text-[10px] text-gray-400">No supporting files uploaded</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>{{-- end scrollable body --}}

      <!-- Footer -->
      <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 rounded-b-xl flex justify-end items-center flex-shrink-0">
        <button onclick="hideBidStatusModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 transition-colors flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Close
        </button>
      </div>
      @else
      <div class="p-6 text-center text-gray-500 text-sm">
        <svg class="mx-auto h-10 w-10 text-gray-300 animate-spin mb-3" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-[11px] text-gray-400">Loading bid details...</p>
      </div>
      @endisset

    </div>
  </div>
</div>