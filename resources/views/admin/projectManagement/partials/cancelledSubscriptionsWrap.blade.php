<div class="overflow-x-auto">
  <table class="w-full table-fixed">
    <thead>
      <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[6%]">ID</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[36%]">Name / Project</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[20%]">Type / Plan</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[20%]">Date</th>
        <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      @include('admin.projectManagement.partials.cancelledSubscriptionsTable')
    </tbody>
  </table>
</div>
@if($cancelledSubscriptions->hasPages())
  <div class="pagination px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
    <p class="text-xs text-gray-500">
      Showing <strong>{{ $cancelledSubscriptions->firstItem() }}</strong>–<strong>{{ $cancelledSubscriptions->lastItem() }}</strong> of <strong>{{ $cancelledSubscriptions->total() }}</strong> results
    </p>
    <div class="flex items-center gap-1">
      @if($cancelledSubscriptions->onFirstPage())
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
      @else
        <a href="{{ $cancelledSubscriptions->previousPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
      @endif
      @foreach($cancelledSubscriptions->getUrlRange(max(1, $cancelledSubscriptions->currentPage()-2), min($cancelledSubscriptions->lastPage(), $cancelledSubscriptions->currentPage()+2)) as $page => $url)
        @if($page == $cancelledSubscriptions->currentPage())
          <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
        @else
          <a href="{{ $url }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
        @endif
      @endforeach
      @if($cancelledSubscriptions->hasMorePages())
        <a href="{{ $cancelledSubscriptions->nextPageUrl() }}" class="px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
      @else
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
      @endif
    </div>
  </div>
@else
  <div class="px-4 py-3 border-t border-gray-200">
    <p class="text-xs text-gray-500">Showing <strong>{{ $cancelledSubscriptions->total() }}</strong> result(s)</p>
  </div>
@endif
