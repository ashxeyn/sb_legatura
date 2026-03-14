@php
  $contractorRequests->appends(request()->except('contractors_page'));
@endphp

<table class="w-full table-fixed">
  <thead>
    <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[30%]">Company Name</th>
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[22%]">Owner Name</th>
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[22%]">Email</th>
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Date Registered</th>
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Action</th>
    </tr>
  </thead>
  <tbody class="divide-y divide-gray-200">
    @forelse($contractorRequests as $request)
    @php
      $companyInitials = strtoupper(substr($request->company_name ?? 'C', 0, 2));
      $ownerFirstName = $request->authorized_rep_fname ?? $request->first_name ?? null;
      $ownerLastName = $request->authorized_rep_lname ?? $request->last_name ?? null;
      $ownerName = trim(($ownerFirstName ?? '') . ' ' . ($ownerLastName ?? ''));
      if ($ownerName === '') {
          $ownerName = $request->username ?? 'N/A';
      }
    @endphp
    <tr class="hover:bg-indigo-50/60 transition-colors">
      <td class="px-2.5 py-2.5">
        <div class="flex items-center gap-1.5">
          <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0">
            {{ $companyInitials }}
          </div>
          <div class="min-w-0">
            <div class="font-medium text-gray-800 leading-tight text-xs truncate max-w-[170px]" title="{{ $request->company_name }}">{{ $request->company_name }}</div>
          </div>
        </div>
      </td>
      <td class="px-2.5 py-2.5 text-gray-700 text-xs max-w-[140px]">
        <span class="block truncate font-medium" title="{{ $ownerName }}">{{ $ownerName }}</span>
      </td>
      <td class="px-2.5 py-2.5 text-gray-700 text-xs max-w-[160px]">
        <span class="block truncate" title="{{ $request->email }}">{{ $request->email }}</span>
      </td>
      <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px]">{{ \Carbon\Carbon::parse($request->request_date)->format('F j, Y') }}</td>
      <td class="px-2.5 py-2.5 whitespace-nowrap">
        <div class="flex items-center gap-1">
          <button class="vr-view-btn action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95" data-key="{{ $request->user_id }}" title="View">
            <i class="fi fi-rr-eye text-[13px] leading-none"></i>
          </button>
        </div>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="5" class="px-4 py-12 text-center text-gray-400">
        <i class="fi fi-sr-users text-3xl block mb-2"></i>
        <p class="text-base font-medium text-gray-500">No contractor verification requests found</p>
        <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
      </td>
    </tr>
    @endforelse
  </tbody>
</table>

@if($contractorRequests->hasPages())
<div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
  <p class="text-xs text-gray-500">
    Showing <strong>{{ $contractorRequests->firstItem() }}</strong>–<strong>{{ $contractorRequests->lastItem() }}</strong>
    of <strong>{{ $contractorRequests->total() }}</strong> results
  </p>
  <div class="flex items-center gap-1">
    @if($contractorRequests->onFirstPage())
      <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
    @else
      <a href="{{ $contractorRequests->previousPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
    @endif

    @foreach($contractorRequests->getUrlRange(max(1, $contractorRequests->currentPage()-2), min($contractorRequests->lastPage(), $contractorRequests->currentPage()+2)) as $page => $url)
      @if($page == $contractorRequests->currentPage())
        <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
      @else
        <a href="{{ $url }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
      @endif
    @endforeach

    @if($contractorRequests->hasMorePages())
      <a href="{{ $contractorRequests->nextPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
    @else
      <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
    @endif
  </div>
</div>
@else
<div class="px-4 py-3 border-t border-gray-200">
  <p class="text-xs text-gray-500">
    Showing <strong>{{ $contractorRequests->total() }}</strong> result(s)
  </p>
</div>
@endif
