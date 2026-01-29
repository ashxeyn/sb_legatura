<!-- Bid Status Modal -->
<div id="bidStatusModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
  <div class="absolute inset-0 flex items-center justify-center overflow-y-auto p-4">
    <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl relative my-8">
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white rounded-t-xl">
        <h2 class="text-base font-semibold text-gray-900">Bid Status</h2>
        <button onclick="hideBidStatusModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-gray-500 hover:text-gray-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      @isset($bid)
      <div class="p-6 space-y-5 max-h-[calc(100vh-12rem)] overflow-y-auto">
        <!-- Status Badge -->
        <div class="flex items-center gap-3">
          <span class="text-sm text-gray-600 font-medium">Status:</span>
          @php
            $statusConfig = [
              'pending' => ['label' => 'Under Evaluation', 'class' => 'bg-amber-100 text-amber-800'],
              'accepted' => ['label' => 'Accepted', 'class' => 'bg-green-100 text-green-800'],
              'rejected' => ['label' => 'Rejected', 'class' => 'bg-red-100 text-red-800'],
              'withdrawn' => ['label' => 'Withdrawn', 'class' => 'bg-gray-100 text-gray-800']
            ];
            $status = $statusConfig[$bid->bid_status] ?? ['label' => ucfirst($bid->bid_status), 'class' => 'bg-gray-100 text-gray-800'];
          @endphp
          <span class="px-3 py-1 rounded-md text-xs font-semibold {{ $status['class'] }}">{{ $status['label'] }}</span>
        </div>

        <!-- Two Column Layout -->
        <div class="grid md:grid-cols-2 gap-5">
          <!-- Left Column: Bidder Information -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-900 pb-2 border-b border-gray-200">Bidder Information</h3>
            <div class="space-y-3">
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Company Name:</span>
                <span class="text-sm font-medium text-gray-900">{{ $bid->company_name ?? 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Email Address:</span>
                <span class="text-sm font-medium text-gray-900">{{ $bid->contractor_email ?? $bid->company_email ?? 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">PCAB No.:</span>
                <span class="text-sm font-medium text-gray-900">{{ $bid->picab_number ?? 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">PCAB Category:</span>
                <span class="text-sm font-medium text-gray-900">{{ $bid->picab_category ?? 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">PCAB Expiration Date:</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ $bid->picab_expiration_date ? \Carbon\Carbon::parse($bid->picab_expiration_date)->format('F j, Y') : 'N/A' }}
                </span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Business Permit No.:</span>
                <span class="text-sm font-medium text-gray-900">{{ $bid->business_permit_number ?? 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Permit City:</span>
                <span class="text-sm font-medium text-gray-900">{{ $bid->business_permit_city ?? 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Business Permit Expiration:</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ $bid->business_permit_expiration ? \Carbon\Carbon::parse($bid->business_permit_expiration)->format('F j, Y') : 'N/A' }}
                </span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">TIN Registration number:</span>
                <span class="text-sm font-medium text-gray-900">{{ $bid->tin_business_reg_number ?? 'N/A' }}</span>
              </div>
            </div>
          </div>

          <!-- Right Column: Project Information -->
          <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-900 pb-2 border-b border-gray-200">Project Information</h3>
            <div class="space-y-3">
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Project Title:</span>
                <span class="text-sm font-medium text-gray-900">{{ $bid->project_title ?? 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Project Address:</span>
                <span class="text-sm font-medium text-gray-900">{{ $bid->project_location ?? 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Project Type:</span>
                <span class="text-sm font-medium text-gray-900">{{ ucfirst($bid->property_type) ?? 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Lot Size (sqm):</span>
                <span class="text-sm font-medium text-gray-900">{{ number_format($bid->lot_size) ?? 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Project Timeline:</span>
                <span class="text-sm font-medium text-gray-900">{{ $bid->to_finish ? $bid->to_finish . ' months' : 'N/A' }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Budget:</span>
                <span class="text-sm font-medium text-gray-900">
                  @if($bid->budget_range_min && $bid->budget_range_max)
                    ₱{{ number_format($bid->budget_range_min) }} - ₱{{ number_format($bid->budget_range_max) }}
                  @else
                    N/A
                  @endif
                </span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Bidding Deadline:</span>
                <span class="text-sm font-medium text-gray-900">
                  {{ $bid->bidding_due ? \Carbon\Carbon::parse($bid->bidding_due)->format('F j, Y') : 'N/A' }}
                </span>
              </div>
              <div class="flex flex-col gap-1">
                <span class="text-xs text-gray-500">Uploaded Files:</span>
                <div class="flex flex-wrap gap-2 mt-1">
                  @forelse($bid->project_files as $file)
                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-50 text-blue-700 text-xs hover:bg-blue-100 transition">
                      <i class="fi fi-rr-{{ $file->file_type == 'image' ? 'picture' : 'document' }} text-xs"></i>
                      {{ basename($file->file_path) }}
                    </a>
                  @empty
                    <span class="text-xs text-gray-500">No files uploaded</span>
                  @endforelse
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bid Details Section -->
        <div class="space-y-4">
          <h3 class="text-sm font-semibold text-gray-900 pb-2 border-b border-gray-200">Bid Details</h3>
          <div class="grid md:grid-cols-2 gap-4">
            <div class="space-y-3">
              <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1.5">Proposed Cost (PHP)</label>
                <input type="text" readonly class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-900 focus:outline-none" value="₱{{ number_format($bid->proposed_cost) }}">
              </div>
              <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1.5">Estimated Timeline</label>
                <input type="text" readonly class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-gray-50 text-gray-900 focus:outline-none" value="{{ $bid->estimated_timeline ? $bid->estimated_timeline . ' months' : 'N/A' }}">
              </div>
            </div>
            <div class="flex flex-col">
              <label class="text-xs text-gray-500 mb-1.5">Description</label>
              <textarea readonly class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-gray-50 resize-none focus:outline-none text-gray-900" style="height: 125px;" placeholder="No description provided">{{ $bid->contractor_notes ?? '' }}</textarea>
            </div>
          </div>
        </div>

        <!-- Supporting Files Section -->
        <div class="space-y-4">
          <h3 class="text-sm font-semibold text-gray-900 pb-2 border-b border-gray-200">
            <span>Supporting Files</span>
          </h3>
          <div class="rounded-lg border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-xs text-gray-600 font-medium">
                  <th class="px-4 py-3">Files</th>
                  <th class="px-4 py-3">Date Submitted</th>
                  <th class="px-4 py-3 text-center">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($bid->bid_files as $file)
                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-900">{{ $file->file_name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($file->uploaded_at)->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-center">
                      <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition">
                        <i class="fi fi-rr-eye text-xs"></i>
                        View/Download
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">No supporting files uploaded</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @else
      <div class="p-6">
        <div class="flex items-center justify-center py-12">
          <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-4 text-sm text-gray-500">Loading bid details...</p>
          </div>
        </div>
      </div>
      @endisset

      <!-- Footer Actions -->
      <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-xl">
        <div class="flex justify-end">
          <button onclick="hideBidStatusModal()" class="px-5 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
