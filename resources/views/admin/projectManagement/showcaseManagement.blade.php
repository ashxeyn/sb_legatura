<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Legatura</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/home/mainComponents.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/projectManagement/showcaseManagement.css') }}">

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
            @include('admin.layouts.topnav', ['pageTitle' => 'Showcase Management'])

            <div class="p-8 space-y-6">

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Showcases</p>
                                <p class="text-3xl font-extrabold text-gray-900 mt-1">{{ $stats->total }}</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fi fi-sr-layers text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <!-- Published -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Approved</p>
                                <p class="text-3xl font-extrabold text-green-600 mt-1">{{ $stats->approved }}</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                <i class="fi fi-sr-check-circle text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <!-- Under Review -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pending Review</p>
                                <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ $stats->pending }}</p>
                            </div>
                            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                                <i class="fi fi-sr-time-quarter-past text-amber-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <!-- Rejected -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Rejected</p>
                                <p class="text-3xl font-extrabold text-red-600 mt-1">{{ $stats->rejected }}</p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                                <i class="fi fi-sr-cross-circle text-red-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3 flex-wrap">
                        <div
                            class="flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                            <i class="fi fi-rr-filter text-gray-500"></i>
                            <span>Filter By</span>
                        </div>

                        <!-- Status Filter -->
                        <div class="relative">
                            <select id="statusFilter"
                                class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-10 text-sm font-medium text-gray-700 hover:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent transition cursor-pointer">
                                <option value="">All Status</option>
                                <option value="approved">Approved</option>
                                <option value="pending">Pending Review</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <i
                                class="fi fi-rr-angle-small-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"></i>
                        </div>

                        <!-- Date Range -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">From:</label>
                            <input type="date" id="dateFrom"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                            <label class="text-sm font-medium text-gray-700">To:</label>
                            <input type="date" id="dateTo"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
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
                    <div class="overflow-x-auto">
                        <table class="w-full" id="showcaseTable">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Contractor</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Showcase Title</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Linked Project</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Date Posted</th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="showcaseTableBody">
                                @include('admin.projectManagement.partials.showcaseTable')
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </main>

    </div>

    <!-- View Showcase Modal -->
    <div id="viewShowcaseModal"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div
            class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto transform transition-all duration-300 scale-95 modal-content">
            <!-- Header -->
            <div
                class="bg-gradient-to-r from-orange-500 to-amber-500 px-6 py-5 flex items-center justify-between rounded-t-2xl">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Showcase Details
                </h3>
                <button class="text-white hover:text-gray-200 transition-colors duration-200 close-modal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Body: populated by server-rendered HTML via AJAX -->
            <div class="p-6 space-y-5" id="modalBodyContent">
                <div class="flex items-center justify-center py-12">
                    <svg class="w-8 h-8 animate-spin text-orange-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    <span class="ml-3 text-gray-500 font-medium">Loading showcase details...</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                <button id="viewModalCloseBtn"
                    class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-200 close-modal">Close</button>
                <button id="viewModalRejectBtn"
                    class="hidden px-6 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200">Reject</button>
                <button id="viewModalApproveBtn"
                    class="hidden px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors duration-200">Approve</button>
            </div>
        </div>
    </div>

    <!-- Approve & Reject Modals (extracted to partial) -->
    @include('admin.projectManagement.partials.approveRejectModal')

    <!-- Delete & Restore Modals -->
    @include('admin.projectManagement.partials.deleteRestoreModal')

    <script src="{{ asset('js/admin/reusables/filters.js') }}" defer></script>
    <script src="{{ asset('js/admin/projectManagement/showcaseManagement.js') }}" defer></script>

</body>

</html>