@php
  $ownerRequests->appends(request()->except('owners_page'));
@endphp

<table class="w-full table-fixed">
  <thead>
    <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[34%]">Name</th>
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[32%]">Email</th>
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Date Registered</th>
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Action</th>
    </tr>
  </thead>
  <tbody class="divide-y divide-gray-200">
    @forelse($ownerRequests as $request)
    @php
      $ownerName = $request->first_name ? $request->first_name . ' ' . $request->last_name : $request->username;
      $ownerInitials = strtoupper(substr($request->first_name ?? $request->username ?? 'O', 0, 1) . substr($request->last_name ?? '', 0, 1));
      $ownerPalette = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-amber-500 to-orange-600'];
      $ownerColor = $ownerPalette[$request->user_id % count($ownerPalette)];
    @endphp
    <tr class="hover:bg-indigo-50/60 transition-colors">
      <td class="px-2.5 py-2.5">
        <div class="flex items-center gap-1.5">
          <div class="w-7 h-7 rounded-full bg-gradient-to-br {{ $ownerColor }} flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0">
            {{ $ownerInitials }}
          </div>
          <div class="min-w-0">
            <div class="font-medium text-gray-800 leading-tight text-xs truncate max-w-[170px]" title="{{ $ownerName }}">{{ $ownerName }}</div>
          </div>
        </div>
      </td>
      <td class="px-2.5 py-2.5 text-gray-700 text-xs max-w-[180px]">
        <span class="block truncate" title="{{ $request->email }}">{{ $request->email }}</span>
      </td>
      <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px]">{{ \Carbon\Carbon::parse($request->request_date)->format('F j, Y') }}</td>
      <td class="px-2.5 py-2.5 whitespace-nowrap">
        <div class="flex items-center gap-1">
          <button class="po-view-btn action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95" data-key="{{ $request->user_id }}" title="View">
            <i class="fi fi-rr-eye text-[13px] leading-none"></i>
          </button>
        </div>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="4" class="px-4 py-12 text-center text-gray-400">
        <i class="fi fi-sr-users text-3xl block mb-2"></i>
        <p class="text-base font-medium text-gray-500">No property owner verification requests found</p>
        <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
      </td>
    </tr>
    @endforelse
  </tbody>
</table>

@if($ownerRequests->hasPages())
<div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
  <p class="text-xs text-gray-500">
    Showing <strong>{{ $ownerRequests->firstItem() }}</strong>–<strong>{{ $ownerRequests->lastItem() }}</strong>
    of <strong>{{ $ownerRequests->total() }}</strong> results
  </p>
  <div class="flex items-center gap-1">
    @if($ownerRequests->onFirstPage())
      <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
    @else
      <a href="{{ $ownerRequests->previousPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
    @endif

    @foreach($ownerRequests->getUrlRange(max(1, $ownerRequests->currentPage()-2), min($ownerRequests->lastPage(), $ownerRequests->currentPage()+2)) as $page => $url)
      @if($page == $ownerRequests->currentPage())
        <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
      @else
        <a href="{{ $url }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
      @endif
    @endforeach

    @if($ownerRequests->hasMorePages())
      <a href="{{ $ownerRequests->nextPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
    @else
      <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
    @endif
  </div>
</div>
@else
<div class="px-4 py-3 border-t border-gray-200">
  <p class="text-xs text-gray-500">
    Showing <strong>{{ $ownerRequests->total() }}</strong> result(s)
  </p>
</div>
@endif
