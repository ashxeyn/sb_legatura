<!-- Bidding Details Modal -->
<div id="biddingDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300">
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-5xl rounded-xl shadow-2xl relative flex flex-col" style="max-height:90vh;">

      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-4 py-3 rounded-t-xl flex-shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center overflow-hidden ring-2 ring-white/30 flex-shrink-0">
              @isset($project)
                @if($project->owner_profile_pic)
                  <img src="{{ asset('storage/' . $project->owner_profile_pic) }}" alt="Owner" class="w-full h-full object-cover">
                @else
                  <span class="text-xs font-bold text-white">{{ strtoupper(substr($project->owner_name ?? 'O', 0, 1)) }}</span>
                @endif
              @else
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              @endisset
            </div>
            <div class="text-white">
              <h3 class="text-sm font-bold leading-tight">
                @isset($project){{ $project->owner_name ?? 'Unknown Owner' }}@else Bidding Details @endisset
              </h3>
              <p class="text-[10px] opacity-80 flex items-center gap-1.5">
                @isset($project)
                  <span class="inline-block w-1.5 h-1.5 bg-indigo-300 rounded-full"></span>
                  @if($project->project_status === 'open')
                    <span class="inline-flex items-center bg-indigo-400/30 px-1.5 py-0.5 rounded text-[9px] font-semibold">In Bidding</span>
                  @elseif($project->project_status === 'bidding_closed')
                    <span class="inline-flex items-center bg-amber-400/30 px-1.5 py-0.5 rounded text-[9px] font-semibold">Bidding Closed</span>
                  @else
                    <span class="inline-flex items-center bg-indigo-400/30 px-1.5 py-0.5 rounded text-[9px] font-semibold">{{ ucfirst(str_replace('_', ' ', $project->project_status ?? '')) }}</span>
                  @endif
                  <span>Submitted {{ $project->submitted_at ? \Carbon\Carbon::parse($project->submitted_at)->format('M j, Y') : 'N/A' }}</span>
                @endisset
              </p>
            </div>
          </div>
          <button onclick="hideBiddingModal()" class="w-7 h-7 rounded-lg hover:bg-white/20 flex items-center justify-center transition-colors text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      @isset($project)
      <style>.bidding-scroll::-webkit-scrollbar{display:none}</style>
      <div class="bidding-scroll p-3 space-y-3 overflow-y-auto flex-1" style="scrollbar-width:none;-ms-overflow-style:none;">

        <!-- Project Details + Bidding Summary (2-Column) -->
        <div class="grid md:grid-cols-2 gap-3">
          <!-- Project Details -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
              Project Details
            </h3>
            <div class="space-y-0.5 text-[11px]">
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-gray-500">Property Type</span>
                <span class="font-semibold text-gray-900 text-right">{{ ucfirst(str_replace('_', ' ', $project->property_type ?? 'N/A')) }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-gray-500">Address</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->project_location ?? 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-gray-500">Lot Size</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->lot_size ? number_format($project->lot_size, 2) . ' sqm' : 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-gray-500">Floor Area</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->floor_area ? number_format($project->floor_area, 2) . ' sqm' : 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-gray-500">Budget</span>
                <span class="font-semibold text-indigo-600 text-right">
                  @if($project->budget_range_min && $project->budget_range_max)
                    &#x20B1;{{ number_format($project->budget_range_min, 2) }} &ndash; &#x20B1;{{ number_format($project->budget_range_max, 2) }}
                  @else
                    N/A
                  @endif
                </span>
              </div>
              <div class="flex justify-between items-start py-0.5 px-1 rounded hover:bg-indigo-50">
                <span class="text-gray-500">Months to Finish</span>
                <span class="font-semibold text-gray-900 text-right">{{ $project->to_finish ? $project->to_finish . ' months' : 'N/A' }}</span>
              </div>
            </div>
            @if($project->project_description)
              <div class="pt-1">
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide mb-1">Description</p>
                <p class="text-[11px] text-gray-600 leading-relaxed line-clamp-4">{{ $project->project_description }}</p>
              </div>
            @endif
            <div class="pt-1">
              <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide block mb-1">Uploaded Files</span>
              <div class="flex flex-wrap gap-1.5">
                @forelse($project->files ?? [] as $file)
                  <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-indigo-600 hover:underline bg-indigo-50 border border-indigo-100 rounded px-2 py-0.5">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    {{ ucfirst(str_replace('_', ' ', $file->file_type)) }}
                  </a>
                @empty
                  <p class="text-[10px] text-gray-400 italic">No files uploaded</p>
                @endforelse
              </div>
            </div>
          </div>

          <!-- Bidding Summary -->
          <div class="bg-white border border-gray-200 rounded-lg p-2 space-y-1">
            <h3 class="font-bold text-gray-900 text-xs border-b border-gray-200 pb-2 flex items-center gap-1.5">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Bidding Summary
            </h3>
            <div class="grid grid-cols-2 gap-2">
              <div class="bg-indigo-50 rounded-lg p-2">
                <p class="text-[10px] text-gray-500 mb-0.5">Start Date</p>
                <p class="text-xs font-semibold text-indigo-700">{{ $project->bid_start_date ? \Carbon\Carbon::parse($project->bid_start_date)->format('M d, Y') : 'N/A' }}</p>
              </div>
              <div class="bg-indigo-50 rounded-lg p-2">
                <p class="text-[10px] text-gray-500 mb-0.5">End Date</p>
                <p class="text-xs font-semibold text-indigo-700">{{ $project->bid_end_date ? \Carbon\Carbon::parse($project->bid_end_date)->format('M d, Y') : 'N/A' }}</p>
              </div>
              <div class="bg-indigo-50 rounded-lg p-2">
                <p class="text-[10px] text-gray-500 mb-0.5">Status</p>
                <p class="text-xs font-semibold text-indigo-700">{{ $project->bidding_status ?? 'N/A' }}</p>
              </div>
              <div class="bg-indigo-50 rounded-lg p-2">
                <p class="text-[10px] text-gray-500 mb-0.5">Winning Bidder</p>
                <p class="text-xs font-semibold text-indigo-700">{{ $project->winning_bidder ?? '—' }}</p>
              </div>
            </div>

            <!-- Owner badges -->
            <div class="pt-1 flex items-center gap-2">
              <span class="inline-flex px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-green-100 text-green-700">Verified</span>
              <span class="inline-flex px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-indigo-100 text-indigo-700">
                ID: {{ $project->owner_id ?? 'N/A' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Submitted Bids -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
          <div class="flex items-center justify-between px-3 py-2 border-b border-gray-200 bg-gray-50">
            <h3 class="font-bold text-gray-900 text-xs flex items-center gap-1.5">
              <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Submitted Bids
            </h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-[10px]">
              <thead class="bg-indigo-50 border-b border-indigo-100">
                <tr class="text-left">
                  <th class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Bidder</th>
                  <th class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Proposed Cost</th>
                  <th class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Duration</th>
                  <th class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Submitted</th>
                  <th class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-600">Status</th>
                  <th class="px-2.5 py-1.5 text-[10px] font-semibold text-gray-600 text-center">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($project->bids ?? [] as $bid)
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-2.5 py-2">
                    <p class="font-semibold text-gray-800">{{ $bid->contractor_name ?? 'Unknown' }}</p>
                    <p class="text-[9px] text-gray-400 mt-0.5">{{ $bid->contractor_category ?? 'N/A' }} &bull; {{ $bid->contractor_pcab ?? 'N/A' }}</p>
                  </td>
                  <td class="px-2.5 py-2 font-semibold text-indigo-700">&#x20B1;{{ number_format($bid->proposed_cost ?? 0, 2) }}</td>
                  <td class="px-2.5 py-2 text-gray-600">{{ $bid->estimated_timeline ? $bid->estimated_timeline . ' mo.' : 'N/A' }}</td>
                  <td class="px-2.5 py-2 text-gray-600">{{ $bid->submitted_at ? \Carbon\Carbon::parse($bid->submitted_at)->format('M d, Y') : 'N/A' }}</td>
                  <td class="px-2.5 py-2">
                    <span class="inline-flex px-1.5 py-0.5 rounded-full text-[9px] font-semibold
                      @if($bid->bid_status === 'accepted') bg-green-100 text-green-700
                      @elseif($bid->bid_status === 'rejected') bg-red-100 text-red-700
                      @else bg-yellow-100 text-yellow-700
                      @endif">
                      {{ ucfirst($bid->bid_status ?? 'Pending') }}
                    </span>
                  </td>
                  <td class="px-2.5 py-2">
                    <div class="flex items-center justify-center gap-1.5">
                      <button type="button" data-bid-id="{{ $bid->bid_id }}" onclick="showBidStatusModal(Number(this.dataset.bidId))" class="p-1.5 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="View Bid Details">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                      </button>
                      @if(empty($project->selected_contractor_id))
                        <button type="button" data-bid-id="{{ $bid->bid_id }}" onclick="showAcceptBidModal(Number(this.dataset.bidId))" class="p-1.5 rounded-md bg-green-50 text-green-600 hover:bg-green-100 transition" title="Accept Bid">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                          </svg>
                        </button>
                        <button type="button" data-bid-id="{{ $bid->bid_id }}" onclick="showRejectBidModal(Number(this.dataset.bidId))" class="p-1.5 rounded-md bg-red-50 text-red-600 hover:bg-red-100 transition" title="Reject Bid">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                          </svg>
                        </button>
                      @elseif(!$project->milestone_count || $project->milestone_count == 0)
                        @if($bid->contractor_id != $project->selected_contractor_id)
                          <button type="button"
                            data-project-id="{{ $project->project_id }}"
                            data-bid-id="{{ $bid->bid_id }}"
                            data-contractor-name="{{ addslashes($bid->contractor_name ?? 'Unknown') }}"
                            onclick="showChangeBidderModal(Number(this.dataset.projectId), Number(this.dataset.bidId), this.dataset.contractorName)"
                            class="px-2 py-1 rounded-md bg-amber-50 text-amber-700 hover:bg-amber-100 transition text-[9px] font-semibold flex items-center gap-1" title="Select as New Bidder">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Select
                          </button>
                        @else
                          <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-green-100 text-green-700">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Selected
                          </span>
                        @endif
                      @endif
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="px-2.5 py-6 text-center text-[10px] text-gray-400">No bids submitted yet</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

      </div>{{-- end scrollable body --}}

      <!-- Footer -->
      <div class="border-t border-gray-200 px-4 py-3 bg-gray-50 rounded-b-xl flex justify-between items-center gap-3 flex-shrink-0">
        @if($project->project_status === 'bidding_closed')
          <button type="button" data-project-id="{{ $project->project_id }}" onclick="showHaltProjectModal(Number(this.dataset.projectId))" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Halt Project
          </button>
        @else
          <div></div>
        @endif
        <button onclick="hideBiddingModal()" class="px-3.5 py-2 text-xs font-semibold rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-100 transition-colors flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Close
        </button>
      </div>
      @else
      <div class="p-6 text-center text-gray-500 text-sm">
        <p>Loading...</p>
      </div>
      @endisset

    </div>
  </div>
</div>