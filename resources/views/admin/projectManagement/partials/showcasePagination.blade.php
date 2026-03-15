@if($showcases->hasPages())
<div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
    <p class="text-xs text-gray-500">
        Showing <strong>{{ $showcases->firstItem() }}</strong>–<strong>{{ $showcases->lastItem() }}</strong>
        of <strong>{{ $showcases->total() }}</strong> results
    </p>
    <div class="flex items-center gap-1">
        @if($showcases->onFirstPage())
            <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
        @else
            <a href="{{ $showcases->previousPageUrl() }}" class="showcase-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
        @endif

        @foreach($showcases->getUrlRange(max(1, $showcases->currentPage() - 2), min($showcases->lastPage(), $showcases->currentPage() + 2)) as $page => $url)
            @if($page == $showcases->currentPage())
                <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="showcase-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
            @endif
        @endforeach

        @if($showcases->hasMorePages())
            <a href="{{ $showcases->nextPageUrl() }}" class="showcase-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
        @else
            <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
        @endif
    </div>
</div>
@else
<div class="px-4 py-3 border-t border-gray-200">
    <p class="text-xs text-gray-500">
        Showing <strong>{{ $showcases->total() }}</strong> result(s)
    </p>
</div>
@endif