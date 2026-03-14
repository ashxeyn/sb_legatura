<div class="overflow-x-auto">
  <table class="w-full table-fixed">
    <thead>
      <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[6%]">ID</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[30%]">Name / Project</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Type / Plan</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Date</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Expiry</th>
        <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @include('admin.projectManagement.partials.expiredSubscriptionsTable')
    </tbody>
  </table>
</div>
@if($expiredSubscriptions->hasPages())
  <div class="pagination px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
    <p class="text-xs text-gray-500">
      Showing <strong>{{ $expiredSubscriptions->firstItem() }}</strong>–<strong>{{ $expiredSubscriptions->lastItem() }}</strong> of <strong>{{ $expiredSubscriptions->total() }}</strong> results
    </p>
    <div class="flex items-center gap-1">
      @if($expiredSubscriptions->onFirstPage())
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
      @else
        <a href="{{ $expiredSubscriptions->previousPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
      @endif
      @foreach($expiredSubscriptions->getUrlRange(max(1, $expiredSubscriptions->currentPage()-2), min($expiredSubscriptions->lastPage(), $expiredSubscriptions->currentPage()+2)) as $page => $url)
        @if($page == $expiredSubscriptions->currentPage())
          <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
        @else
          <a href="{{ $url }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
        @endif
      @endforeach
      @if($expiredSubscriptions->hasMorePages())
        <a href="{{ $expiredSubscriptions->nextPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
      @else
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
      @endif
    </div>
  </div>
@else
  <div class="px-4 py-3 border-t border-gray-200">
    <p class="text-xs text-gray-500">Showing <strong>{{ $expiredSubscriptions->total() }}</strong> result(s)</p>
  </div>
@endif
