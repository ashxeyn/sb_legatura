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

</head>

<body class="bg-gray-50 text-gray-800 font-sans">
    <div class="flex min-h-screen">

        @include('admin.layouts.sidebar')

        <main class="flex-1">
            @include('admin.layouts.topnav', ['pageTitle' => 'Review & Rating Management'])

            <div class="p-8 space-y-6">
                <!-- Filters Section -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                            <i class="fi fi-rr-filter text-gray-500"></i>
                            <span>Filter By</span>
                        </div>

                        <!-- Date Range -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">From:</label>
                            <input type="date" id="dateFrom"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                            <label class="text-sm font-medium text-gray-700">To:</label>
                            <input type="date" id="dateTo"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
                        </div>

                        <select
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 focus:outline-none bg-white font-medium text-gray-700"
                            id="ratingFilter">
                            <option value="">Rating</option>
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-4">
                        <button id="resetFilters"
                            class="flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-red-50 transition">
                            <i class="fi fi-rr-rotate-left"></i>
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Reviewer</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Reviewed User</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Rating</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Review</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="reviewsTableWrap">
                                @include('admin.globalManagement.partials.reviewManagementTable')
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50" id="paginationWrap">
                        {{ $reviews->links() }}
                    </div>
                </div>

            </div>

            <!-- View Review Modal -->
            <div id="viewReviewModal"
                class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
                <div
                    class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300 scale-95 modal-content">
                    <div
                        class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-5 flex items-center justify-between rounded-t-2xl">
                        <h3 class="text-xl font-bold text-white">Review Details</h3>
                        <button class="text-white hover:text-gray-200 transition-colors duration-200 close-modal">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-5">
                        <!-- Reviewer Info -->
                        <div class="flex items-center gap-4 pb-4 border-b">
                            <div class="w-14 h-14 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg overflow-hidden"
                                id="modalReviewerAvatar">JD</div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-800" id="modalReviewerName">Loading...</h4>
                                <p class="text-sm text-gray-500" id="modalReviewerType">Loading...</p>
                            </div>
                        </div>

                        <!-- Reviewed User Info -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h5 class="text-sm font-semibold text-gray-600 mb-2">Reviewed User</h5>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow overflow-hidden"
                                    id="modalReviewedAvatar">GB</div>
                                <div>
                                    <p class="font-semibold text-gray-800" id="modalReviewedName">Loading...</p>
                                    <p class="text-sm text-gray-500" id="modalReviewedType">Loading...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Rating -->
                        <div>
                            <h5 class="text-sm font-semibold text-gray-600 mb-2">Rating</h5>
                            <div class="flex items-center gap-2">
                                <div id="modalStarRating" class="flex items-center gap-1"></div>
                                <span class="text-lg font-bold text-gray-800" id="modalRatingValue">0</span>
                                <span class="text-sm text-gray-500">/ 5</span>
                            </div>
                        </div>

                        <!-- Review Text -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <h5 class="text-sm font-semibold text-gray-600 mb-2">Review</h5>
                            <p class="text-sm text-gray-700 leading-relaxed" id="modalReviewText">Loading...</p>
                        </div>

                        <!-- Quick Meta -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Project</p>
                                <p class="font-semibold text-gray-800" id="modalProjectTitle">Loading...</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Date Submitted</p>
                                <p class="font-semibold text-gray-800" id="modalDate">Loading...</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                        <button
                            class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-200 close-modal">Close</button>
                    </div>
                </div>
            </div>

            <!-- Delete Review Modal -->
            <div id="deleteReviewModal"
                class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
                <div
                    class="bg-white rounded-3xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 modal-content">
                    <!-- Header -->
                    <div
                        class="bg-gradient-to-r from-red-500 via-red-600 to-rose-600 px-6 py-5 flex items-center justify-between rounded-t-3xl relative overflow-hidden">
                        <div class="absolute inset-0 bg-white opacity-10">
                            <div
                                class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2">
                            </div>
                            <div
                                class="absolute bottom-0 right-0 w-40 h-40 bg-white rounded-full blur-3xl opacity-20 translate-x-1/2 translate-y-1/2">
                            </div>
                        </div>
                        <div class="relative flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white">Delete Review</h3>
                        </div>
                        <button
                            class="relative text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all duration-200 close-delete-modal">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <!-- Icon with animation -->
                        <div class="flex items-center justify-center mb-4">
                            <div class="relative">
                                <div
                                    class="w-20 h-20 bg-gradient-to-br from-rose-100 to-red-100 rounded-full flex items-center justify-center shadow-lg delete-icon-container">
                                    <svg class="w-10 h-10 text-red-600 delete-x" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </div>
                                <div class="absolute inset-0 bg-red-400 rounded-full animate-ping opacity-20"></div>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="text-center mb-4">
                            <h4 class="text-xl font-bold text-gray-800 mb-2">Confirm Deletion</h4>
                            <p class="text-gray-600 mb-3">You are about to delete this review</p>
                            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-3 mb-3">
                                <p class="text-sm text-red-800 font-medium mb-1">Review By</p>
                                <p class="text-lg font-bold text-red-900" id="deleteModalReviewerName">Loading...</p>
                            </div>
                            <div
                                class="bg-yellow-50 border border-yellow-200 rounded-lg p-2 flex items-start gap-3 mb-3">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-yellow-800 text-left">This action is irreversible. The review
                                    will be permanently removed from the system.</p>
                            </div>
                            <div class="text-left">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for Deletion <span
                                        class="text-red-500">*</span></label>
                                <textarea id="deletionReason" rows="3"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 focus:outline-none resize-none"
                                    placeholder="Please provide a reason for deleting this review..."></textarea>
                                <p class="text-xs text-gray-500 mt-2">This reason will be logged for audit purposes.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div
                        class="px-6 py-4 bg-gradient-to-b from-gray-50 to-gray-100 rounded-b-3xl flex justify-end gap-3">
                        <button
                            class="px-6 py-3 bg-white border-2 border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-all duration-200 hover:scale-105 close-delete-modal">
                            <span class="flex items-center gap-2">
                                Cancel
                            </span>
                        </button>
                        <button
                            class="px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold rounded-xl transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
                            id="confirmDelete">
                            <span class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                                <span>Delete Review</span>
                                <svg class="w-4 h-4 delete-loading hidden animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

        </main>


        <script src="{{ asset('js/admin/globalManagement/reviewManagement.js') }}" defer></script>

        <!-- The toast container is now dynamically injected via mainComponents.js / showNotification -->
</body>

</html>