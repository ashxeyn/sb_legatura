<div class="overflow-x-auto">
    <table class="w-full table-fixed">
        <thead>
            <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[28%]">Owner Name</th>
                <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[32%]">Post Title</th>
                <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Date Posted</th>
                <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Status</th>
                <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200" id="postingsTable">
            @forelse($postings as $post)
            @php
                $ownerInitials = strtoupper(substr($post->first_name ?? '?', 0, 1) . substr($post->last_name ?? '', 0, 1));
                $statusColors = [
                    'under_review' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'approved' => 'bg-green-100 text-green-700 border-green-200',
                    'rejected' => 'bg-red-100 text-red-700 border-red-200',
                    'deleted' => 'bg-gray-100 text-gray-700 border-gray-200',
                    'due' => 'bg-orange-100 text-orange-700 border-orange-200',
                ];
                $statusLabel = [
                    'under_review' => 'Under Review',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'deleted' => 'Deleted',
                    'due' => 'Due',
                ];
            @endphp
            <tr class="hover:bg-indigo-50/60 transition-colors duration-200 ease-in-out">
                <td class="px-2.5 py-2.5">
                    <div class="flex items-center gap-1.5">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0 overflow-hidden">
                            @if($post->profile_pic)
                                <img src="{{ asset('storage/' . $post->profile_pic) }}" alt="Owner Profile" class="w-full h-full object-cover">
                            @else
                                {{ $ownerInitials }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium text-gray-800 leading-tight text-xs truncate max-w-[170px]" title="{{ $post->first_name }} {{ $post->last_name }}">{{ $post->first_name }} {{ $post->last_name }}</div>
                            <div class="text-[11px] text-gray-500 truncate max-w-[170px]" title="{{ $post->owner_email ?? 'N/A' }}">{{ $post->owner_email ?? 'N/A' }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-2.5 py-2.5 text-gray-700 text-xs">
                    <span class="block truncate max-w-[240px]" title="{{ $post->project_title }}">{{ $post->project_title }}</span>
                </td>
                <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px]">{{ \Carbon\Carbon::parse($post->created_at)->format('m/d/y') }}</td>
                <td class="px-2.5 py-2.5 text-center">
                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusColors[$post->project_post_status] ?? 'bg-gray-100 text-gray-700 border-gray-200' }}">
                        {{ $statusLabel[$post->project_post_status] ?? ucfirst($post->project_post_status) }}
                    </span>
                </td>
                <td class="px-2.5 py-2.5 whitespace-nowrap">
                    <div class="flex items-center gap-1">
                        <button
                            class="action-btn view-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95"
                            title="View"
                            data-id="{{ $post->project_id }}"
                            data-name="{{ $post->first_name }} {{ $post->last_name }}"
                            data-title="{{ $post->project_title }}"
                            data-date="{{ \Carbon\Carbon::parse($post->created_at)->format('d M, Y') }}"
                            data-status="{{ $post->project_post_status }}"
                            data-type="{{ ucfirst($post->user_type ?? 'Property Owner') }}"
                            data-pic="{{ $post->profile_pic ? asset('storage/' . $post->profile_pic) : '' }}"
                            data-description="{{ e($post->project_description ?? '') }}"
                            data-location="{{ e($post->project_location ?? '') }}"
                            data-property-type="{{ e($post->property_type ?? '') }}"
                            data-budget-min="{{ $post->budget_range_min ?? '' }}"
                            data-budget-max="{{ $post->budget_range_max ?? '' }}"
                            data-lot-size="{{ $post->lot_size ?? '' }}"
                            data-floor-area="{{ $post->floor_area ?? '' }}"
                            data-to-finish="{{ $post->to_finish ?? '' }}"
                            data-email="{{ $post->owner_email ?? '' }}"
                            data-phone="{{ $post->owner_phone ?? '' }}"
                            data-post-status="{{ $post->project_post_status ?? '' }}"
                        >
                            <i class="fi fi-rr-eye text-[13px] leading-none"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                    <i class="fi fi-rr-document text-3xl block mb-2"></i>
                    <p class="text-base font-medium text-gray-500">No posts found</p>
                    <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($postings->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
        <p class="text-xs text-gray-500">
            Showing <strong>{{ $postings->firstItem() }}</strong>–<strong>{{ $postings->lastItem() }}</strong>
            of <strong>{{ $postings->total() }}</strong> results
        </p>
        <div class="flex items-center gap-1">
            @if($postings->onFirstPage())
                <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
            @else
                <a href="{{ $postings->previousPageUrl() }}" class="posting-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
            @endif

            @foreach($postings->getUrlRange(max(1, $postings->currentPage() - 2), min($postings->lastPage(), $postings->currentPage() + 2)) as $page => $url)
                @if($page == $postings->currentPage())
                    <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="posting-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
                @endif
            @endforeach

            @if($postings->hasMorePages())
                <a href="{{ $postings->nextPageUrl() }}" class="posting-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
            @else
                <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
            @endif
        </div>
    </div>
    @else
    <div class="px-4 py-3 border-t border-gray-200">
        <p class="text-xs text-gray-500">
            Showing <strong>{{ $postings->total() }}</strong> result(s)
        </p>
    </div>
    @endif
</div>
