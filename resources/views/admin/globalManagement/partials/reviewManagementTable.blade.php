@forelse($reviews as $review)
    <tr class="hover:bg-gray-50 transition-colors duration-150">
        <td class="px-6 py-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-12 h-12 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold shadow overflow-hidden">
                    @if($review->reviewer_pic)
                        <img src="{{ asset('storage/' . $review->reviewer_pic) }}" alt="Profile"
                            class="w-full h-full object-cover">
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
                <div>
                    <div class="font-semibold text-gray-800">{{ $review->reviewer_name }}</div>
                    <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $review->reviewer_type)) }}</div>
                </div>
            </div>
        </td>
        <td class="px-6 py-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow overflow-hidden">
                    @if($review->reviewed_pic)
                        <img src="{{ asset('storage/' . $review->reviewed_pic) }}" alt="Profile"
                            class="w-full h-full object-cover">
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
                <div>
                    <div class="font-semibold text-gray-800 text-sm">{{ $review->reviewed_name }}</div>
                    <div class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $review->reviewed_type)) }}</div>
                </div>
            </div>
        </td>
        <td class="px-6 py-4">
            <div class="flex items-center gap-1">
                @for($i = 1; $i <= 5; $i++)
                    @if($i <= $review->rating)
                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                            </path>
                        </svg>
                    @endif
                @endfor
                <span class="ml-1 text-sm font-semibold text-gray-700">{{ $review->rating }}.0</span>
            </div>
        </td>
        <td class="px-6 py-4 text-sm text-gray-700 max-w-xs">
            <p class="truncate" title="{{ $review->review_text }}">{{ Str::limit($review->review_text, 60) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $review->project_title }}</p>
        </td>
        <td class="px-6 py-4 text-sm text-gray-700">{{ \Carbon\Carbon::parse($review->created_at)->format('d M, Y') }}</td>
        <td class="px-6 py-4">
            <div class="flex items-center gap-3">
                <button
                    class="w-10 h-10 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 view-review-btn"
                    data-id="{{ $review->review_id }}" data-reviewer-name="{{ $review->reviewer_name }}"
                    data-reviewer-pic="{{ $review->reviewer_pic ? asset('storage/' . $review->reviewer_pic) : '' }}"
                    data-reviewer-type="{{ ucfirst(str_replace('_', ' ', $review->reviewer_type)) }}"
                    data-reviewed-name="{{ $review->reviewed_name }}"
                    data-reviewed-pic="{{ $review->reviewed_pic ? asset('storage/' . $review->reviewed_pic) : '' }}"
                    data-reviewed-type="{{ ucfirst(str_replace('_', ' ', $review->reviewed_type)) }}"
                    data-rating="{{ $review->rating }}" data-review-text="{{ e($review->review_text) }}"
                    data-project-title="{{ $review->project_title }}"
                    data-date="{{ \Carbon\Carbon::parse($review->created_at)->format('d M, Y') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </button>
                <button
                    class="w-10 h-10 bg-red-50 hover:bg-red-100 text-red-600 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 delete-review-btn"
                    data-id="{{ $review->review_id }}" data-reviewer-name="{{ $review->reviewer_name }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
            <div class="flex flex-col items-center gap-2">
                <i class="fi fi-rr-search-alt text-4xl"></i>
                <p class="font-medium">No reviews found matching your criteria</p>
            </div>
        </td>
    </tr>
@endforelse