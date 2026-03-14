<table class="w-full table-fixed">
  <thead>
    <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[22%]">Company Name</th>
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Email</th>
      <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Date Registered</th>
      <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Suspension Until</th>
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[22%]">Reason</th>
      <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[8%]">Total Projects</th>
      <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[6%]">Action</th>
    </tr>
  </thead>
  <tbody class="divide-y divide-gray-200">
    @if($suspendedContractors && $suspendedContractors->count() > 0)
      @foreach($suspendedContractors as $contractor)
        @php
          $companyInitials = strtoupper(substr(str_replace(' ', '', $contractor->name ?? 'C'), 0, 2));
        @endphp
        <tr class="hover:bg-indigo-50/60 transition-colors">
          <td class="px-2.5 py-2.5">
            <div class="flex items-center gap-1.5">
              <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0">
                {{ $companyInitials ?: 'C' }}
              </div>
              <div class="min-w-0">
                <div class="font-medium text-gray-800 leading-tight text-xs truncate max-w-[150px]" title="{{ $contractor->name ?? 'N/A' }}">{{ $contractor->name ?? 'N/A' }}</div>
              </div>
            </div>
          </td>
          <td class="px-2.5 py-2.5 text-gray-700 text-xs max-w-[150px]">
            <span class="block truncate" title="{{ $contractor->email ?? 'N/A' }}">{{ $contractor->email ?? 'N/A' }}</span>
          </td>
          <td class="px-2.5 py-2.5 text-center whitespace-nowrap text-gray-700 text-[11px]">{{ $contractor->date_registered ? \Carbon\Carbon::parse($contractor->date_registered)->format('m/d/y') : 'N/A' }}</td>
          <td class="px-2.5 py-2.5 text-center whitespace-nowrap text-red-600 text-[11px] font-medium">{{ $contractor->suspension_until ? \Carbon\Carbon::parse($contractor->suspension_until)->format('m/d/y') : 'N/A' }}</td>
          <td class="px-2.5 py-2.5 text-gray-700 text-[11px] max-w-[190px]">
            <span class="block truncate" title="{{ $contractor->reason ?? 'No reason provided' }}">{{ Str::limit($contractor->reason ?? 'No reason provided', 55) }}</span>
          </td>
          <td class="px-2.5 py-2.5 text-center">
            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-indigo-100 text-indigo-700 border-indigo-200">{{ $contractor->total_projects ?? 0 }}</span>
          </td>
          <td class="px-2.5 py-2.5 whitespace-nowrap">
            <div class="flex items-center gap-1">
              <button class="reactivate-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-emerald-100 hover:shadow-sm hover:border-emerald-300 hover:-translate-y-0.5 transition-all active:scale-95"
                      data-id="{{ $contractor->contractor_id }}"
                      data-user-type="contractor"
                      data-name="{{ $contractor->name }}"
                      title="Reactivate">
                <i class="fi fi-rr-refresh text-[13px] leading-none"></i>
              </button>
            </div>
          </td>
        </tr>
      @endforeach
    @else
      <tr>
        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
          <i class="fi fi-sr-users text-3xl block mb-2"></i>
          <p class="text-base font-medium text-gray-500">No suspended contractors found</p>
          <p class="text-xs mt-1">All contractor accounts are currently active.</p>
        </td>
      </tr>
    @endif
  </tbody>
</table>

@if($suspendedContractors->hasPages())
  <div class="pagination px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
    <p class="text-xs text-gray-500">
      Showing <strong>{{ $suspendedContractors->firstItem() }}</strong>-
      <strong>{{ $suspendedContractors->lastItem() }}</strong>
      of <strong>{{ $suspendedContractors->total() }}</strong> results
    </p>
    <div class="flex items-center gap-1">
      @if($suspendedContractors->onFirstPage())
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
      @else
        <a href="{{ $suspendedContractors->previousPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
      @endif

      @foreach($suspendedContractors->getUrlRange(max(1, $suspendedContractors->currentPage()-2), min($suspendedContractors->lastPage(), $suspendedContractors->currentPage()+2)) as $page => $url)
        @if($page == $suspendedContractors->currentPage())
          <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
        @else
          <a href="{{ $url }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
        @endif
      @endforeach

      @if($suspendedContractors->hasMorePages())
        <a href="{{ $suspendedContractors->nextPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
      @else
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
      @endif
    </div>
  </div>
@else
  <div class="px-4 py-3 border-t border-gray-200">
    <p class="text-xs text-gray-500">
      Showing <strong>{{ $suspendedContractors->total() }}</strong> result(s)
    </p>
  </div>
@endif
