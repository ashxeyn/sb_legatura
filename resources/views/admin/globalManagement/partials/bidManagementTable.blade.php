@if(request('search') || request('status'))
  <div class="px-4 py-3 bg-indigo-50 border-b border-indigo-100 flex items-center gap-2 text-xs text-indigo-700">
    <i class="fi fi-rr-filter"></i>
    <span>
      Showing <strong>{{ $bids->total() }}</strong> result(s)
      @if(request('search')) for "<strong>{{ request('search') }}</strong>"@endif
      @if(request('status')) with status "<strong>{{ request('status') }}</strong>"@endif
    </span>
    <a href="{{ route('admin.globalManagement.bidManagement') }}" class="clear-bids-filters ml-auto text-indigo-600 hover:underline text-[11px] font-semibold">Clear filters</a>
  </div>
@endif

<div class="overflow-x-auto">
  <table class="w-full table-fixed">
    <thead>
      <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[9%]">Bid ID</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[23%]">Project Title</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[27%]">Contractor Company</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Bid Amount</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[11%]">Submitted</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[8%]">Status</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200" id="bidsTable">
      @forelse ($bids as $bid)
        @php
          $statusMap = [
            'submitted' => ['label' => 'Submitted', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
            'under_review' => ['label' => 'Under Review', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
            'accepted' => ['label' => 'Approved', 'class' => 'bg-green-100 text-green-700 border-green-200'],
            'rejected' => ['label' => 'Rejected', 'class' => 'bg-red-100 text-red-700 border-red-200'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-gray-100 text-gray-500 border-gray-200'],
          ];
          $s = $statusMap[$bid->bid_status] ?? ['label' => ucfirst($bid->bid_status), 'class' => 'bg-gray-100 text-gray-600 border-gray-200'];
          $words = explode(' ', trim($bid->company_name ?? 'UN'));
          $initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : substr($words[0], 1, 1)));
          $gradients = ['from-blue-500 to-indigo-600', 'from-emerald-500 to-teal-600', 'from-fuchsia-500 to-purple-600', 'from-orange-500 to-rose-500', 'from-sky-500 to-blue-600', 'from-cyan-500 to-teal-500', 'from-gray-700 to-gray-900'];
          $grad = $gradients[$bid->bid_id % count($gradients)];
        @endphp

        <tr class="hover:bg-indigo-50/60 transition-colors">
          <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 font-medium text-xs">#{{ $bid->bid_id }}</td>

          <td class="px-2.5 py-2.5 text-gray-700 text-xs max-w-[180px]">
            <span class="block truncate" title="{{ $bid->project_title }}">{{ $bid->project_title }}</span>
          </td>

          <td class="px-2.5 py-2.5">
            <div class="flex items-center gap-1.5">
              <div class="w-7 h-7 rounded-full bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0">
                {{ $initials }}
              </div>
              <div class="min-w-0">
                <div class="font-medium text-gray-800 leading-tight text-xs truncate max-w-[170px]" title="{{ $bid->company_name }}">{{ $bid->company_name }}</div>
                <div class="text-[11px] text-gray-500 truncate max-w-[170px]" title="{{ $bid->company_email }}">{{ $bid->company_email }}</div>
              </div>
            </div>
          </td>

          <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px] font-semibold">₱{{ number_format($bid->bid_amount, 2) }}</td>

          <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-600 text-[11px]">{{ \Carbon\Carbon::parse($bid->bid_date)->format('F j, Y') }}</td>

          <td class="px-2.5 py-2.5 whitespace-nowrap">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $s['class'] }}">{{ $s['label'] }}</span>
          </td>

          <td class="px-2.5 py-2.5 whitespace-nowrap">
            <div class="flex items-center gap-1">
              <button class="action-btn btn-view-bid w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95" title="View"
                data-bid-id="{{ $bid->bid_id }}"
                data-bid-status="{{ $bid->bid_status }}"
                data-company-name="{{ e($bid->company_name) }}"
                data-company-email="{{ e($bid->company_email) }}"
                data-project-title="{{ e($bid->project_title) }}"
                data-proposed-cost="{{ $bid->bid_amount }}"
                data-timeline="{{ $bid->estimated_timeline }}"
                data-submitted-at="{{ \Carbon\Carbon::parse($bid->bid_date)->format('F j, Y') }}"
                data-decision-date="{{ $bid->decision_date ? \Carbon\Carbon::parse($bid->decision_date)->format('F j, Y') : '' }}"
                data-notes="{{ e($bid->contractor_notes ?? '') }}"
                data-reason="{{ e($bid->reason ?? '') }}"
                data-pcab="{{ e($bid->picab_number ?? 'N/A') }}"
                data-pcab-category="{{ e($bid->picab_category ?? 'N/A') }}"
                data-pcab-expiry="{{ $bid->picab_expiration_date ?? 'N/A' }}"
                data-bp-number="{{ e($bid->business_permit_number ?? 'N/A') }}"
                data-bp-city="{{ e($bid->business_permit_city ?? 'N/A') }}"
                data-bp-expiry="{{ $bid->business_permit_expiration ?? 'N/A' }}"
                data-tin="{{ e($bid->tin_business_reg_number ?? 'N/A') }}">
                <i class="fi fi-rr-eye text-[13px] leading-none"></i>
              </button>

              <button class="action-btn btn-edit-bid w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-orange-200 bg-orange-50 text-orange-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-orange-100 hover:shadow-sm hover:border-orange-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Edit"
                data-bid-id="{{ $bid->bid_id }}"
                data-bid-status="{{ $bid->bid_status }}"
                data-company-name="{{ e($bid->company_name) }}"
                data-company-email="{{ e($bid->company_email) }}"
                data-project-title="{{ e($bid->project_title) }}"
                data-proposed-cost="{{ $bid->bid_amount }}"
                data-timeline="{{ $bid->estimated_timeline }}"
                data-notes="{{ e($bid->contractor_notes ?? '') }}">
                <i class="fi fi-rr-pencil text-[13px] leading-none"></i>
              </button>

              <button class="action-btn btn-delete-bid w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Delete"
                data-bid-id="{{ $bid->bid_id }}"
                data-project-title="{{ e($bid->project_title) }}"
                data-company-name="{{ e($bid->company_name) }}">
                <i class="fi fi-rr-trash text-[13px] leading-none"></i>
              </button>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="px-4 py-12 text-center text-gray-400">
            <i class="fi fi-rr-inbox text-3xl block mb-2"></i>
            <p class="text-base font-medium text-gray-500">No bids found</p>
            <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  @if ($bids->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
      <p class="text-xs text-gray-500">
        Showing <strong>{{ $bids->firstItem() }}</strong>–<strong>{{ $bids->lastItem() }}</strong>
        of <strong>{{ $bids->total() }}</strong> results
      </p>
      <div class="flex items-center gap-1">
        @if ($bids->onFirstPage())
          <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
        @else
          <a href="{{ $bids->previousPageUrl() }}" class="bid-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
        @endif

        @foreach ($bids->getUrlRange(max(1, $bids->currentPage() - 2), min($bids->lastPage(), $bids->currentPage() + 2)) as $page => $url)
          @if ($page == $bids->currentPage())
            <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
          @else
            <a href="{{ $url }}" class="bid-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
          @endif
        @endforeach

        @if ($bids->hasMorePages())
          <a href="{{ $bids->nextPageUrl() }}" class="bid-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
        @else
          <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
        @endif
      </div>
    </div>
  @else
    <div class="px-4 py-3 border-t border-gray-200">
      <p class="text-xs text-gray-500">
        Showing <strong>{{ $bids->total() }}</strong> result(s)
      </p>
    </div>
  @endif
</div>
