<div class="overflow-x-auto">
  <table class="w-full table-fixed">
    <thead>
      <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[30%]">Company Name</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[20%]">Owner Name</th>
        <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Years of<br>Operations</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Date Registered</th>
        <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">Total<br>Projects</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200" id="contractorsTable">
      @forelse($contractors as $contractor)
      @php
        $companyInitials = strtoupper(substr($contractor->company_name ?? '?', 0, 2));
      @endphp
      <tr class="hover:bg-indigo-50/60 transition-colors">
        <td class="px-2.5 py-2.5">
          <div class="flex items-center gap-1.5">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center overflow-hidden shadow flex-shrink-0">
              @if(isset($contractor->company_logo) && $contractor->company_logo)
                <img src="{{ asset('storage/' . $contractor->company_logo) }}" alt="{{ $contractor->company_name }}" class="w-full h-full object-cover">
              @else
                <span class="text-white text-[10px] font-bold">{{ $companyInitials }}</span>
              @endif
            </div>
            <div class="min-w-0">
              <div class="font-medium text-gray-800 leading-tight text-xs truncate max-w-[160px]" title="{{ $contractor->company_name }}">{{ $contractor->company_name }}</div>
              <div class="text-[11px] text-gray-500 truncate max-w-[160px]" title="{{ $contractor->email ?? 'N/A' }}">{{ $contractor->email ?? 'N/A' }}</div>
            </div>
          </div>
        </td>
        <td class="px-2.5 py-2.5 text-gray-700 text-xs max-w-[130px]">
          @php
            $ownerFirstName = $contractor->authorized_rep_fname ?? $contractor->first_name ?? null;
            $ownerLastName = $contractor->authorized_rep_lname ?? $contractor->last_name ?? null;
            $ownerName = trim(($ownerFirstName ?? '') . ' ' . ($ownerLastName ?? ''));
          @endphp
          @if($ownerName !== '')
            <span class="block truncate font-medium" title="{{ $ownerName }}">
              {{ $ownerName }}
            </span>
          @else
            <span class="text-gray-400 italic text-[11px]">N/A</span>
          @endif
        </td>
        <td class="px-2.5 py-2.5 text-center whitespace-nowrap text-gray-700 text-[11px]">{{ $contractor->years_of_experience }} Years</td>
        <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px]">{{ \Carbon\Carbon::parse($contractor->created_at)->format('F j, Y') }}</td>
        <td class="px-2.5 py-2.5 text-center">
          <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-indigo-100 text-indigo-700 border-indigo-200">{{ $contractor->bids_count ?? 0 }}</span>
        </td>
        <td class="px-2.5 py-2.5 whitespace-nowrap">
          <div class="flex items-center gap-1">
            <button class="action-btn view-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all" title="View" data-id="{{ $contractor->contractor_id }}">
              <i class="fi fi-rr-eye text-[13px] leading-none"></i>
            </button>
            <button class="action-btn edit-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-orange-200 bg-orange-50 text-orange-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-orange-100 hover:shadow-sm hover:border-orange-300 hover:-translate-y-0.5 transition-all" title="Edit" data-id="{{ $contractor->contractor_id }}">
              <i class="fi fi-rr-pencil text-[13px] leading-none"></i>
            </button>
            <button class="action-btn delete-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all" title="Delete" data-id="{{ $contractor->contractor_id }}">
              <i class="fi fi-rr-trash text-[13px] leading-none"></i>
            </button>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
          <i class="fi fi-sr-users text-3xl block mb-2"></i>
          <p class="text-base font-medium text-gray-500">No contractors found</p>
          <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>

  @if($contractors->hasPages())
  <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
    <p class="text-xs text-gray-500">
      Showing <strong>{{ $contractors->firstItem() }}</strong>–<strong>{{ $contractors->lastItem() }}</strong>
      of <strong>{{ $contractors->total() }}</strong> results
    </p>
    <div class="flex items-center gap-1">
      @if($contractors->onFirstPage())
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
      @else
        <a href="{{ $contractors->previousPageUrl() }}" class="contractor-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
      @endif

      @foreach($contractors->getUrlRange(max(1, $contractors->currentPage()-2), min($contractors->lastPage(), $contractors->currentPage()+2)) as $page => $url)
        @if($page == $contractors->currentPage())
          <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
        @else
          <a href="{{ $url }}" class="contractor-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
        @endif
      @endforeach

      @if($contractors->hasMorePages())
        <a href="{{ $contractors->nextPageUrl() }}" class="contractor-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
      @else
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
      @endif
    </div>
  </div>
  @else
  <div class="px-4 py-3 border-t border-gray-200">
    <p class="text-xs text-gray-500">
      Showing <strong>{{ $contractors->total() }}</strong> result(s)
    </p>
  </div>
  @endif
</div>
