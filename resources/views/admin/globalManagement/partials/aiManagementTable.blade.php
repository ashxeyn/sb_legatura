@php
    $hasFilters = request('search') || request('verdict') || request('date_from') || request('date_to');
@endphp

<div class="overflow-hidden">
    <div class="px-4 py-3 border-b border-orange-100 bg-gradient-to-r from-orange-50 to-transparent flex justify-between items-start">
        <div>
            <h3 class="text-xs font-semibold text-orange-700 flex items-center gap-1.5">
                <i class="fi fi-rr-time-past text-orange-600 text-xs"></i>
                Prediction History Logs
            </h3>
            <p class="text-[11px] text-gray-500 mt-0.5">Analysis records and project risk assessments</p>
        </div>
        <span class="text-xs font-semibold text-gray-600 bg-orange-50 px-3 py-1.5 rounded-lg border border-orange-100">Latest Results</span>
    </div>

    <table class="w-full table-fixed">
        <thead>
            <tr class="bg-gradient-to-r from-orange-50 to-amber-50 border-b border-gray-200">
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider flex-1">Analyzed</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider flex-1">Project</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider flex-1">Contractor</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider flex-1">Verdict</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider flex-1">Confidence</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider flex-shrink-0">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200" id="predictionTableBody">
            @forelse($predictionLogs as $log)
            <tr class="hover:bg-orange-50/60 transition-colors duration-200 ease-in-out"
                data-project="{{ strtolower($log->project_title) }}"
                data-verdict="{{ $log->prediction }}"
                data-contractor-type="{{ $log->type_id ?? '' }}"
                data-date="{{ $log->created_at }}">
                <td class="px-4 py-3 whitespace-nowrap text-xs flex-1">
                    <span class="text-gray-600 font-medium">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span>
                </td>
                <td class="px-4 py-3 text-xs flex-1">
                    <div class="font-semibold text-gray-900 truncate" title="{{ $log->project_title }}">{{ $log->project_title }}</div>
                    <div class="text-[11px] text-gray-500">ID: {{ $log->project_id }}</div>
                </td>
                <td class="px-4 py-3 text-xs flex-1">
                    <div class="font-semibold text-gray-900 truncate" title="{{ $log->company_name ?? 'N/A' }}">{{ $log->company_name ?? 'N/A' }}</div>
                    <div class="text-[11px] text-gray-500">Contractor</div>
                </td>
                <td class="px-4 py-3 flex-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold border
                        {{ $log->prediction === 'DELAYED'
                            ? 'bg-red-100 text-red-700 border-red-200'
                            : 'bg-green-100 text-green-700 border-green-200' }}">
                        {{ $log->prediction }}
                    </span>
                </td>
                <td class="px-4 py-3 flex-1">
                    <div class="flex items-center gap-2">
                        <svg class="w-16 h-1.5" viewBox="0 0 100 6" preserveAspectRatio="none">
                            <rect x="0" y="0" width="100" height="6" rx="3" fill="#e5e7eb"/>
                            <rect x="0" y="0" width="{{ $log->delay_probability * 100 }}" height="6" rx="3" fill="{{ $log->prediction === 'DELAYED' ? '#ef4444' : '#22c55e' }}"/>
                        </svg>
                        <span class="font-mono font-bold text-gray-700 text-xs min-w-[45px]">{{ number_format($log->delay_probability * 100, 1) }}%</span>
                    </div>
                </td>
                <td class="px-4 py-3 whitespace-nowrap flex-shrink-0">
                    <div class="flex items-center gap-1">
                        <button
                            data-ai-snapshot="{{ htmlspecialchars(json_encode(json_decode($log->ai_response_snapshot), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') }}"
                            onclick="window.aiManagement.showDetails(this.dataset.aiSnapshot)"
                            class="action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-orange-200 bg-orange-50 text-orange-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-orange-100 hover:shadow-sm hover:border-orange-300 hover:-translate-y-0.5 transition-all active:scale-95"
                            title="View Details">
                            <i class="fi fi-rr-eye text-[13px] leading-none"></i>
                        </button>
                        <button
                            data-log-id="{{ $log->id }}"
                            data-project-title="{{ $log->project_title }}"
                            onclick="window.aiManagement.confirmDelete(Number(this.dataset.logId), this.dataset.projectTitle)"
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
                    <span class="px-2.5 py-1 rounded-lg text-xs bg-orange-500 text-white font-semibold">{{ $page }}</span>
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
