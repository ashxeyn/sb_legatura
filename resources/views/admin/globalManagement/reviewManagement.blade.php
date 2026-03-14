<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Legatura</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/globalManagement/reviewManagement.css') }}">

    <link rel='stylesheet'
        href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-straight/css/uicons-solid-straight.css'>
    <link rel='stylesheet'
        href='https://cdn-uicons.flaticon.com/3.0.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/3.0.0/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <link rel='stylesheet'
        href='https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>

    <script src="{{ asset('js/admin/home/mainComponents.js') }}" defer></script>

    <style>
        .date-pill input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0.5;
            cursor: pointer;
            filter: invert(30%) sepia(80%) saturate(400%) hue-rotate(210deg);
        }

        .date-pill input[type="date"]::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        /* Hide scrollbars but keep scrolling enabled */
        .scrollbar-hidden {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .scrollbar-hidden::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }

        /* Modal animation */
        #deleteReviewModal.show .modal-content {
            animation: modalShowAnimation 300ms ease-in-out forwards;
        }

        #viewReviewModal.show .modal-content {
            animation: modalShowAnimation 300ms ease-in-out forwards;
        }

        @keyframes modalShowAnimation {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>

</head>

<body class="bg-gray-50 text-gray-800 font-sans">
    <div class="flex min-h-screen">

        @include('admin.layouts.sidebar')

        <main class="flex-1">
            @include('admin.layouts.topnav', ['pageTitle' => 'Review & Rating Management'])

            <div class="p-8 space-y-6">
                <!-- Filters Section -->
                <div class="controls-wrapper bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <div class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                            <i class="fi fi-rr-filter text-gray-500"></i>
                            <span>Filter By</span>
                        </div>

                        <!-- Date Range -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- From -->
                            <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                                    <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                                    <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">From</span>
                                </div>
                                <input type="date" id="dateFrom"
                                    class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
                            </div>

                            <span class="text-gray-300 font-bold text-lg">→</span>

                            <!-- To -->
                            <div class="date-pill flex items-center gap-0 rounded-xl border border-indigo-200 bg-white shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-indigo-400 focus-within:border-indigo-400 transition">
                                <div class="flex items-center gap-1.5 bg-gradient-to-br from-indigo-500 to-indigo-600 px-3 py-2.5 self-stretch">
                                    <i class="fi fi-rr-calendar text-white text-sm leading-none"></i>
                                    <span class="text-[11px] font-bold text-indigo-100 uppercase tracking-wider select-none">To</span>
                                </div>
                                <input type="date" id="dateTo"
                                    class="bg-white text-sm text-gray-700 font-medium px-3 py-2.5 focus:outline-none cursor-pointer min-w-0 border-0">
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div class="relative">
                            <select id="ratingFilter"
                                class="appearance-none rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 pr-9 text-sm text-gray-700 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                                <option value="">Rating</option>
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                            <i class="fi fi-rr-angle-small-down absolute right-3 top-1/2 -translate-y-1/2 text-[13px] text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <button id="resetFilters"
                        class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                        <i class="fi fi-rr-rotate-left"></i>
                        <span>Reset Filter</span>
                    </button>
                </div>

                <!-- Table Section -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed">
                            <thead class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[25%]">
                                        Reviewer</th>
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[25%]">
                                        Reviewed User</th>
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">
                                        Rating</th>
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">
                                        Review</th>
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">
                                        Date</th>
                                    <th
                                        class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[10%]">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="reviewsTableWrap">
                                @include('admin.globalManagement.partials.reviewManagementTable')
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2" id="paginationWrap">
                        <p class="text-xs text-gray-500">Showing <strong>{{ $reviews->firstItem() ?? 0 }}</strong>–<strong>{{ $reviews->lastItem() ?? 0 }}</strong> of <strong>{{ $reviews->total() }}</strong> results</p>
                        <div class="flex items-center gap-1">
                            @if($reviews->onFirstPage())
                                <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
                            @else
                                <a href="{{ $reviews->previousPageUrl() }}" class="review-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
                            @endif

                            @foreach($reviews->getUrlRange(max(1, $reviews->currentPage()-2), min($reviews->lastPage(), $reviews->currentPage()+2)) as $page => $url)
                                @if($page == $reviews->currentPage())
                                    <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="review-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
                                @endif
                            @endforeach

                            @if($reviews->hasMorePages())
                                <a href="{{ $reviews->nextPageUrl() }}" class="review-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
                            @else
                                <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            <!-- View Review Modal -->
            <div id="viewReviewModal"
                class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
                <div
                    class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[84vh] overflow-hidden transform transition-all duration-300 scale-95 opacity-0 modal-content flex flex-col">
                    <div
                        class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-4 py-3.5 flex items-center justify-between border-b border-indigo-700 text-white flex-shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow">
                                <i class="fi fi-rr-star text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-[15px] font-bold leading-tight">Review Details</h3>
                                <p class="text-[10px] text-indigo-100">User feedback and ratings</p>
                            </div>
                        </div>
                        <button class="text-white hover:text-indigo-100 transition-all p-1 rounded-md hover:bg-white hover:bg-opacity-20 active:scale-95 close-modal">
                            <i class="fi fi-rr-cross-small text-lg"></i>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto scrollbar-hidden p-4 space-y-3.5">
                        <!-- Reviewer Info -->
                        <div class="rounded-xl border border-indigo-200 bg-indigo-50/60 p-3.5">
                            <div class="flex items-center gap-2 mb-2.5">
                                <div class="w-6 h-6 rounded-lg bg-indigo-500 text-indigo-50 flex items-center justify-center">
                                    <i class="fi fi-rr-user text-[10px]"></i>
                                </div>
                                <h4 class="text-xs font-semibold text-indigo-800">Reviewer Information</h4>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-[11px] shadow flex-shrink-0 overflow-hidden" id="modalReviewerAvatar">JD</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-800" id="modalReviewerName">Loading...</p>
                                    <p class="text-[11px] text-gray-600" id="modalReviewerType">Loading...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Reviewed User Info -->
                        <div class="rounded-xl border border-blue-200 bg-blue-50/60 p-3.5">
                            <div class="flex items-center gap-2 mb-2.5">
                                <div class="w-6 h-6 rounded-lg bg-blue-500 text-blue-50 flex items-center justify-center">
                                    <i class="fi fi-rr-circle-user text-[10px]"></i>
                                </div>
                                <h4 class="text-xs font-semibold text-blue-800">Reviewed User</h4>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-[11px] shadow flex-shrink-0 overflow-hidden" id="modalReviewedAvatar">GB</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-800" id="modalReviewedName">Loading...</p>
                                    <p class="text-[11px] text-gray-600" id="modalReviewedType">Loading...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Rating -->
                        <div class="rounded-xl border border-yellow-200 bg-yellow-50/60 p-3.5">
                            <div class="flex items-center gap-2 mb-2.5">
                                <div class="w-6 h-6 rounded-lg bg-yellow-500 text-yellow-50 flex items-center justify-center">
                                    <i class="fi fi-rr-star text-[10px]"></i>
                                </div>
                                <h4 class="text-xs font-semibold text-yellow-800">Rating</h4>
                            </div>
                            <div class="flex items-center gap-2">
                                <div id="modalStarRating" class="flex items-center gap-0.5"></div>
                                <span class="text-xs font-semibold text-gray-800 ml-1" id="modalRatingValue">0</span>
                                <span class="text-[10px] text-gray-600">/ 5</span>
                            </div>
                        </div>

                        <!-- Review Text -->
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3.5">
                            <div class="flex items-center gap-2 mb-2.5">
                                <div class="w-6 h-6 rounded-lg bg-gray-500 text-gray-50 flex items-center justify-center">
                                    <i class="fi fi-rr-comment-dots text-[10px]"></i>
                                </div>
                                <h4 class="text-xs font-semibold text-gray-800">Review Text</h4>
                            </div>
                            <p class="text-[12px] text-gray-700 leading-relaxed" id="modalReviewText">Loading...</p>
                        </div>

                        <!-- Meta Info -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3.5">
                                <p class="text-[10px] text-gray-600 mb-1.5">Project</p>
                                <p class="text-xs font-semibold text-gray-800" id="modalProjectTitle">Loading...</p>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3.5">
                                <p class="text-[10px] text-gray-600 mb-1.5">Date Submitted</p>
                                <p class="text-xs font-semibold text-gray-800" id="modalDate">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-2.5 border-t border-gray-200 bg-gray-50 flex-shrink-0 flex items-center justify-end">
                        <button
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold text-[12px] rounded-lg transition-colors duration-200 close-modal">Close</button>
                    </div>
                </div>
            </div>

            <!-- Delete Review Modal -->
            <div id="deleteReviewModal"
                class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2">
                <div
                    class="bg-white rounded-lg shadow-lg max-w-xs w-full transform transition-all duration-300 scale-95 opacity-0 modal-content relative">
                    <button id="closeDeleteModalBtn" type="button" class="absolute top-2 right-2 w-6 h-6 rounded-md border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition flex items-center justify-center close-delete-modal">
                        <i class="fi fi-rr-cross text-[10px]"></i>
                    </button>

                    <!-- Icon Section -->
                    <div class="flex justify-center pt-3 pb-2">
                        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center relative">
                            <div class="absolute inset-0 bg-red-200 rounded-full animate-ping opacity-60"></div>
                            <div class="relative w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                                <i class="fi fi-rr-trash text-white text-base"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Content Section -->
                    <div class="px-3 pb-3 text-center">
                        <h2 class="text-sm font-bold text-gray-800 mb-1.5">Delete Review</h2>
                        <p class="text-[11px] text-gray-600 leading-relaxed mb-2.5">
                            Permanently delete the review from <span class="font-semibold" id="deleteModalReviewerName">this user</span>? This action cannot be undone.
                        </p>

                        <div class="text-left">
                            <label for="deletionReason" class="block text-[11px] font-medium text-gray-700 mb-1">Reason for Deletion <span
                                    class="text-red-500">*</span></label>
                            <textarea id="deletionReason" rows="2"
                                class="w-full px-2 py-1.5 text-[11px] border border-gray-300 rounded-md focus:ring-2 focus:ring-red-300 focus:border-red-300 focus:outline-none transition resize-none"
                                placeholder="Please provide a reason for deletion..."></textarea>

                            <!-- Validation Error Section -->
                            <div id="deleteReviewErrorAlert" class="hidden bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg mt-2 text-left">
                              <div class="flex items-start gap-2">
                                <i class="fi fi-rr-exclamation text-red-600 text-sm flex-shrink-0 mt-0.5"></i>
                                <div class="flex-1">
                                  <p class="text-xs font-semibold text-red-800 mb-1">Validation Error</p>
                                  <ul id="deleteReviewErrorList" class="text-xs text-red-700 space-y-0.5 list-disc list-inside">
                                    <!-- Error messages will be populated here -->
                                  </ul>
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="px-3 pb-3 space-y-1.5">
                        <button id="confirmDelete"
                            class="w-full px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-md transition-all text-[11px] font-semibold shadow-sm hover:shadow-md transform hover:scale-[1.01] active:scale-95 flex items-center justify-center gap-1">
                            <i class="fi fi-rr-trash"></i>
                            Delete
                        </button>
                        <button id="cancelDelete"
                            class="w-full px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-all text-[11px] font-semibold hover:border-gray-400 hover:shadow-sm transform hover:scale-[1.01] active:scale-95 close-delete-modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

        </main>


        <script src="{{ asset('js/admin/globalManagement/reviewManagement.js') }}" defer></script>

        <!-- The toast container is now dynamically injected via mainComponents.js / showNotification -->
</body>

</html>