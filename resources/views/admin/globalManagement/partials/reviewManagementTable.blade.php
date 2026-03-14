@forelse($reviews as $review)
    <tr class="hover:bg-indigo-50/60 transition-colors duration-200 ease-in-out">
        <td class="px-2.5 py-2.5 w-[25%]">
            <div class="flex items-center gap-1.5">
                <div class="w-7 h-7 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0 overflow-hidden">
                    @if($review->reviewer_pic)
                        <img src="{{ asset('storage/' . $review->reviewer_pic) }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                        @php
                            $revInitials = '';
                            if ($review->reviewer_name) {
                                $parts = explode(' ', trim($review->reviewer_name));
                                $revInitials = substr($parts[0], 0, 1);
                                if (count($parts) > 1) {
                                    $revInitials .= substr(end($parts), 0, 1);
                                }
                            }
                        @endphp
                        {{ strtoupper($revInitials ?: '??') }}
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="font-medium text-gray-800 text-xs truncate max-w-[120px]" title="{{ $review->reviewer_name }}">{{ $review->reviewer_name }}</div>
                    <div class="text-[11px] text-gray-500 truncate max-w-[120px]">{{ ucfirst(str_replace('_', ' ', $review->reviewer_type)) }}</div>
                </div>
            </div>
        </td>
        <td class="px-2.5 py-2.5 w-[25%]">
            <div class="flex items-center gap-1.5">
                <div class="w-7 h-7 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0 overflow-hidden">
                    @if($review->reviewed_pic)
                        <img src="{{ asset('storage/' . $review->reviewed_pic) }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                        @php
                            $reeInitials = '';
                            if ($review->reviewed_name) {
                                $parts = explode(' ', trim($review->reviewed_name));
                                $reeInitials = substr($parts[0], 0, 1);
                                if (count($parts) > 1) {
                                    $reeInitials .= substr(end($parts), 0, 1);
                                }
                            }
                        @endphp
                        {{ strtoupper($reeInitials ?: '??') }}
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="font-medium text-gray-800 text-xs truncate max-w-[120px]" title="{{ $review->reviewed_name }}">{{ $review->reviewed_name }}</div>
                    <div class="text-[11px] text-gray-500 truncate max-w-[120px]">{{ ucfirst(str_replace('_', ' ', $review->reviewed_type)) }}</div>
                </div>
            </div>
        </td>
        <td class="px-2.5 py-2.5 w-[12%]">
            <div class="flex items-center gap-0.5">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $review->rating)
                        <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    @else
                        <svg class="w-3.5 h-3.5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    @endif
                @endfor
                <span class="ml-1 text-xs font-semibold text-gray-700 whitespace-nowrap">{{ $review->rating }}.0</span>
            </div>
        </td>
        <td class="px-2.5 py-2.5 text-xs text-gray-700 w-[18%]">
            <p class="truncate max-w-[160px]" title="{{ $review->review_text }}">{{ Str::limit($review->review_text, 40) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">{{ $review->project_title }}</p>
        </td>
        <td class="px-2.5 py-2.5 text-xs text-gray-700 whitespace-nowrap w-[10%]">{{ \Carbon\Carbon::parse($review->created_at)->format('m/d/y') }}</td>
        <td class="px-2.5 py-2.5 whitespace-nowrap w-[10%]">
            <div class="flex items-center gap-1">
                <button
                    class="action-btn view-review-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95"
                    title="View"
                    data-id="{{ $review->review_id }}" data-reviewer-name="{{ $review->reviewer_name }}"
                    data-reviewer-pic="{{ $review->reviewer_pic ? asset('storage/' . $review->reviewer_pic) : '' }}"
                    data-reviewer-type="{{ ucfirst(str_replace('_', ' ', $review->reviewer_type)) }}"
                    data-reviewed-name="{{ $review->reviewed_name }}"
                    data-reviewed-pic="{{ $review->reviewed_pic ? asset('storage/' . $review->reviewed_pic) : '' }}"
                    data-reviewed-type="{{ ucfirst(str_replace('_', ' ', $review->reviewed_type)) }}"
                    data-rating="{{ $review->rating }}" data-review-text="{{ e($review->review_text) }}"
                    data-project-title="{{ $review->project_title }}"
                    data-date="{{ \Carbon\Carbon::parse($review->created_at)->format('d M, Y') }}">
                    <i class="fi fi-rr-eye text-[13px] leading-none"></i>
                </button>
                <button
                    class="action-btn delete-review-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all active:scale-95"
                    title="Delete"
                    data-id="{{ $review->review_id }}" data-reviewer-name="{{ $review->reviewer_name }}">
                    <i class="fi fi-rr-trash text-[13px] leading-none"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
            <i class="fi fi-sr-search-alt text-3xl block mb-2"></i>
            <p class="text-base font-medium text-gray-500">No reviews found</p>
            <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
        </td>
    </tr>
@endforelse