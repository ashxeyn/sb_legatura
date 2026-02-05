<!-- Bidding Details Modal -->
<div id="biddingDetailsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
  <div class="absolute inset-0 flex items-start justify-center overflow-y-auto py-10">
    <div class="bg-white w-full max-w-5xl rounded-2xl shadow-xl border border-gray-200 relative">
      <div class="flex items-center justify-between px-8 py-5 border-b bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-t-2xl">
        <h2 class="text-xl font-semibold text-white flex items-center gap-2">
          <i class="fi fi-rr-hammer text-white/90"></i>
          <span>Bidding Details</span>
        </h2>
        <button onclick="hideBiddingModal()" class="w-10 h-10 rounded-xl bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">
          <i class="fi fi-rr-cross-small text-lg"></i>
        </button>
      </div>

@isset($project)
      <div class="p-8 space-y-8">
        <!-- Property Owner Info -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
          <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center overflow-hidden shadow">
              @if($project->owner_profile_pic)
                <img src="{{ asset('storage/' . $project->owner_profile_pic) }}" alt="Owner" class="w-full h-full object-cover">
              @else
                <span class="text-2xl font-bold">{{ strtoupper(substr($project->owner_name ?? 'O', 0, 1)) }}</span>
              @endif
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-800">{{ $project->owner_name ?? 'Unknown Owner' }}</h3>
              <p class="text-sm text-gray-500">Property Owner • <span class="font-medium text-gray-700">ID: {{ $project->owner_id ?? 'N/A' }}</span></p>
              <p class="text-xs text-gray-400 mt-1">Submitted: {{ $project->submitted_at ? \Carbon\Carbon::parse($project->submitted_at)->format('M d, Y') : 'N/A' }}</p>
            </div>
            <div class="ml-auto flex flex-col gap-2">
              <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Verified</span>
              <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                @if($project->project_status === 'open') bg-indigo-100 text-indigo-700
                @elseif($project->project_status === 'bidding_closed') bg-amber-100 text-amber-700
                @endif">
                {{ $project->project_status === 'open' ? 'In Bidding' : ($project->project_status === 'bidding_closed' ? 'Bidding Closed' : ucfirst(str_replace('_', ' ', $project->project_status ?? ''))) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Project Details -->
        <div class="grid md:grid-cols-2 gap-6">
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
              <i class="fi fi-rr-layer-plus text-indigo-600"></i>
              Project Details
            </h3>
            <div class="space-y-3 text-sm">
              <p><span class="text-gray-500">Property Type:</span> <span class="font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $project->property_type ?? 'N/A')) }}</span></p>
              <p><span class="text-gray-500">Address:</span> <span class="font-medium text-gray-800">{{ $project->project_location ?? 'N/A' }}</span></p>
              <p><span class="text-gray-500">Lot Size:</span> <span class="font-medium text-gray-800">{{ $project->lot_size ? number_format($project->lot_size, 2) . ' sqm' : 'N/A' }}</span></p>
              <p><span class="text-gray-500">Floor Area:</span> <span class="font-medium text-gray-800">{{ $project->floor_area ? number_format($project->floor_area, 2) . ' sqm' : 'N/A' }}</span></p>
              <p><span class="text-gray-500">Budget:</span> <span class="font-medium text-gray-800">
                @if($project->budget_range_min && $project->budget_range_max)
                  ₱{{ number_format($project->budget_range_min, 2) }} - ₱{{ number_format($project->budget_range_max, 2) }}
                @else
                  N/A
                @endif
              </span></p>
              <p><span class="text-gray-500">Months to Finish:</span> <span class="font-medium text-gray-800">{{ $project->to_finish ? $project->to_finish . ' months' : 'N/A' }}</span></p>
            </div>
            <div>
              <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Description</h4>
              <p class="text-sm text-gray-700 leading-relaxed line-clamp-5">{{ $project->project_description ?? 'No description available' }}</p>
            </div>
            <div>
              <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Uploaded Files</h4>
              <div class="flex flex-wrap gap-2">
                @forelse($project->files ?? [] as $file)
                  <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-medium hover:bg-indigo-100 transition">
                    <i class="fi fi-rr-document text-xs"></i>
                    <span>{{ ucfirst(str_replace('_', ' ', $file->file_type)) }}</span>
                  </a>
                @empty
                  <p class="text-xs text-gray-500">No files uploaded</p>
                @endforelse
              </div>
            </div>
          </div>
          <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
              <i class="fi fi-rr-clock-three text-indigo-600"></i>
              Bidding Summary
            </h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="bg-indigo-50 rounded-lg p-3">
                <p class="text-xs text-gray-500">Start Date</p>
                <p class="font-semibold text-indigo-700">{{ $project->bid_start_date ? \Carbon\Carbon::parse($project->bid_start_date)->format('M d, Y') : 'N/A' }}</p>
              </div>
              <div class="bg-indigo-50 rounded-lg p-3">
                <p class="text-xs text-gray-500">End Date</p>
                <p class="font-semibold text-indigo-700">{{ $project->bid_end_date ? \Carbon\Carbon::parse($project->bid_end_date)->format('M d, Y') : 'N/A' }}</p>
              </div>
              <div class="bg-indigo-50 rounded-lg p-3">
                <p class="text-xs text-gray-500">Status</p>
                <p class="font-semibold text-indigo-700">{{ $project->bidding_status ?? 'N/A' }}</p>
              </div>
              <div class="bg-indigo-50 rounded-lg p-3">
                <p class="text-xs text-gray-500">Winning Bidder</p>
                <p class="font-semibold text-indigo-700">{{ $project->winning_bidder ?? '—' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Submitted Bids -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider flex items-center gap-2">
              <i class="fi fi-rr-handshake text-indigo-600"></i>
              Submitted Bids
            </h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-indigo-50">
                <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                  <th class="px-6 py-3">Bidder</th>
                  <th class="px-6 py-3">Proposed Cost</th>
                  <th class="px-6 py-3">Duration</th>
                  <th class="px-6 py-3">Submitted</th>
                  <th class="px-6 py-3">Status</th>
                  <th class="px-6 py-3 text-center">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                @forelse($project->bids ?? [] as $bid)
                <tr class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4">
                    <div>
                      <p class="font-medium text-gray-800">{{ $bid->contractor_name ?? 'Unknown' }}</p>
                      <p class="text-xs text-gray-500">{{ $bid->contractor_category ?? 'N/A' }} • {{ $bid->contractor_pcab ?? 'N/A' }}</p>
                    </div>
                  </td>
                  <td class="px-6 py-4 font-semibold text-indigo-700">₱{{ number_format($bid->proposed_cost ?? 0, 2) }}</td>
                  <td class="px-6 py-4">{{ $bid->estimated_timeline ? $bid->estimated_timeline . ' months' : 'N/A' }}</td>
                  <td class="px-6 py-4 text-gray-600">{{ $bid->submitted_at ? \Carbon\Carbon::parse($bid->submitted_at)->format('M d, Y') : 'N/A' }}</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold
                      @if($bid->bid_status === 'accepted') bg-green-100 text-green-700
                      @elseif($bid->bid_status === 'rejected') bg-red-100 text-red-700
                      @else bg-yellow-100 text-yellow-700
                      @endif">
                      {{ ucfirst($bid->bid_status ?? 'Pending') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <button onclick="showBidStatusModal({{ $bid->bid_id }})" class="p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="View Bid Details">
                        <i class="fi fi-rr-eye text-sm"></i>
                      </button>
                      <button onclick="showAcceptBidModal({{ $bid->bid_id }})" class="p-2 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition" title="Accept Bid">
                        <i class="fi fi-rr-check text-sm"></i>
                      </button>
                      <button onclick="showRejectBidModal({{ $bid->bid_id }})" class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Reject Bid">
                        <i class="fi fi-rr-cross text-sm"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500">No bids submitted yet</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="flex justify-between items-center gap-3 pt-4">
          @if($project->project_status === 'bidding_closed')
          <button onclick="showHaltProjectModal({{ $project->project_id }})" class="px-5 py-2 text-sm font-medium rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white transition shadow-md hover:shadow-lg flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Halt Project
          </button>
          @else
          <div></div>
          @endif
          <button onclick="hideBiddingModal()" class="px-5 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">Close</button>
        </div>
      </div>
@else
      <div class="p-8">
        <p class="text-gray-500 text-center">Loading...</p>
      </div>
@endisset
    </div>
  </div>
</div>
