@php
    $hasFilters = request('search') || request('verdict') || request('date_from') || request('date_to');
@endphp

<div class="overflow-hidden">
    <div class="px-4 py-3 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-transparent flex justify-between items-start">
        <div>
            <h3 class="text-xs font-semibold text-blue-700 flex items-center gap-1.5">
                <i class="fi fi-rr-time-past text-blue-600 text-xs"></i>
                Prediction History Logs
            </h3>
            <p class="text-[11px] text-gray-500 mt-0.5">Analysis records and project risk assessments</p>
        </div>
        <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-3 py-1.5 rounded-lg">Latest Results</span>
    </div>

    <table class="w-full table-fixed">
        <thead>
            <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[15%]">Analyzed</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[30%]">Project</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Verdict</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Probability</th>
                <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Confidence</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[13%]">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200" id="predictionTableBody">
            @forelse($predictionLogs as $log)
            <tr class="hover:bg-indigo-50/60 transition-colors duration-200 ease-in-out"
                data-project="{{ strtolower($log->project_title) }}"
                data-verdict="{{ $log->prediction }}"
                data-date="{{ $log->created_at }}">
                <td class="px-4 py-3 whitespace-nowrap text-xs">
                    <span class="text-gray-600 font-medium">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span>
                </td>
                <td class="px-4 py-3 text-xs">
                    <div class="font-semibold text-gray-900 truncate max-w-[280px]" title="{{ $log->project_title }}">{{ $log->project_title }}</div>
                    <div class="text-[11px] text-gray-500">ID: {{ $log->project_id }}</div>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold border
                        {{ $log->prediction === 'DELAYED'
                            ? 'bg-red-100 text-red-700 border-red-200'
                            : 'bg-green-100 text-green-700 border-green-200' }}">
                        {{ $log->prediction }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full {{ $log->prediction === 'DELAYED' ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ $log->delay_probability * 100 }}%"></div>
                        </div>
                        <span class="font-mono font-bold text-gray-700 text-xs min-w-[45px]">{{ number_format($log->delay_probability * 100, 1) }}%</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <div class="w-1.5 h-1.5 rounded-full {{ $i <= round($log->delay_probability * 5) ? ($log->prediction === 'DELAYED' ? 'bg-red-500' : 'bg-green-500') : 'bg-gray-300' }}"></div>
                        @endfor
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center gap-1">
                        <button
                            onclick="window.aiManagement.showDetails({{ json_encode(json_decode($log->ai_response_snapshot), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }})"
                            class="action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95"
                            title="View Details">
                            <i class="fi fi-rr-eye text-[13px] leading-none"></i>
                        </button>
                        <button
                            onclick="window.aiManagement.confirmDelete({{ $log->id }}, '{{ addslashes($log->project_title) }}')"
                            class="action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all active:scale-95"
                            title="Delete">
                            <i class="fi fi-rr-trash text-[13px] leading-none"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr id="noResultsRow" style="display: none;">
                <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                    <i class="fi fi-sr-document text-3xl block mb-2"></i>
                    <p class="text-base font-medium text-gray-500">No matching results found</p>
                    <p class="text-xs mt-1">Try adjusting your filter criteria.</p>
                </td>
            </tr>
            <tr>
                <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                    <i class="fi fi-sr-document text-3xl block mb-2"></i>
                    <p class="text-base font-medium text-gray-500">No analysis history found</p>
                    <p class="text-xs mt-1">Try running a new analysis or adjusting your filter criteria.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($predictionLogs->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
        <p class="text-xs text-gray-500">
            Showing <strong>{{ $predictionLogs->firstItem() }}</strong>–<strong>{{ $predictionLogs->lastItem() }}</strong>
            of <strong>{{ $predictionLogs->total() }}</strong> results
        </p>
        <div class="flex items-center gap-1">
            @if($predictionLogs->onFirstPage())
                <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
            @else
                <a href="{{ $predictionLogs->previousPageUrl() }}" class="prediction-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
            @endif

            @foreach($predictionLogs->getUrlRange(max(1, $predictionLogs->currentPage() - 2), min($predictionLogs->lastPage(), $predictionLogs->currentPage() + 2)) as $page => $url)
                @if($page == $predictionLogs->currentPage())
                    <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="prediction-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
                @endif
            @endforeach

            @if($predictionLogs->hasMorePages())
                <a href="{{ $predictionLogs->nextPageUrl() }}" class="prediction-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
            @else
                <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
            @endif
        </div>
    </div>
    @else
    <div class="px-4 py-3 border-t border-gray-200">
        <p class="text-xs text-gray-500">
            Showing <strong>{{ $predictionLogs->total() }}</strong> result(s)
        </p>
    </div>
    @endif
</div>
