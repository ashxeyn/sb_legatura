<!-- Approve Showcase Modal -->
<div id="approveShowcaseModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div
        class="bg-white rounded-3xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 modal-content">
        <div
            class="bg-gradient-to-r from-green-500 via-green-600 to-emerald-600 px-6 py-6 flex items-center justify-between rounded-t-3xl relative overflow-hidden">
            <div class="absolute inset-0 bg-white opacity-10">
                <div
                    class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2">
                </div>
            </div>
            <div class="relative flex items-center gap-3">
                <div
                    class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white">Approve Showcase</h3>
            </div>
            <button
                class="relative text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all duration-200 close-modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-8">
            <div class="flex items-center justify-center mb-6">
                <div class="relative">
                    <div
                        class="w-24 h-24 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-20"></div>
                </div>
            </div>
            <div class="text-center mb-6">
                <h4 class="text-2xl font-bold text-gray-800 mb-2">Confirm Approval</h4>
                <p class="text-gray-600 mb-4">You are about to approve this showcase post</p>
                <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 mb-4">
                    <p class="text-sm text-green-800 font-medium mb-1">Showcase Title</p>
                    <p class="text-lg font-bold text-green-900" id="approveModalTitle">Showcase Title Here</p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-blue-800 text-left">Once approved, this showcase will be visible in the
                        public feed and the contractor's profile.</p>
                </div>
            </div>
        </div>
        <div class="px-8 py-6 bg-gradient-to-b from-gray-50 to-gray-100 rounded-b-3xl flex justify-end gap-3">
            <button
                class="px-6 py-3 bg-white border-2 border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-all duration-200 close-modal">Cancel</button>
            <button
                class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl"
                id="confirmApproveShowcase">
                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    Approve Showcase
                </span>
            </button>
        </div>
    </div>
</div>

<!-- Reject Showcase Modal -->
<div id="rejectShowcaseModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div
        class="bg-white rounded-3xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 modal-content">
        <div
            class="bg-gradient-to-r from-red-500 via-red-600 to-rose-600 px-6 py-5 flex items-center justify-between rounded-t-3xl relative overflow-hidden">
            <div class="absolute inset-0 bg-white opacity-10">
                <div
                    class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2">
                </div>
            </div>
            <div class="relative flex items-center gap-3">
                <h3 class="text-xl font-bold text-white">Reject Showcase</h3>
            </div>
            <button
                class="relative text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-all duration-200 close-modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-center mb-4">
                <div class="relative">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-rose-100 to-red-100 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <div class="absolute inset-0 bg-red-400 rounded-full animate-ping opacity-20"></div>
                </div>
            </div>
            <div class="text-center mb-4">
                <h4 class="text-xl font-bold text-gray-800 mb-2">Confirm Rejection</h4>
                <p class="text-gray-600 mb-3">You are about to reject this showcase post</p>
                <div class="bg-red-50 border-2 border-red-200 rounded-xl p-3 mb-3">
                    <p class="text-sm text-red-800 font-medium mb-1">Showcase Title</p>
                    <p class="text-lg font-bold text-red-900" id="rejectModalTitle">Showcase Title Here</p>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-2 flex items-start gap-3 mb-3">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-yellow-800 text-left">The contractor will be notified about this
                        rejection along with your reason.</p>
                </div>
                <div class="text-left">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for Rejection <span
                            class="text-red-500">*</span></label>
                    <textarea id="rejectReason" rows="3"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-red-400 focus:border-red-400 focus:outline-none resize-none"
                        placeholder="Please provide a clear reason for rejecting this showcase..."></textarea>
                    <p id="rejectReasonError" class="text-xs text-red-500 font-medium mt-1 hidden"></p>
                    <p class="text-xs text-gray-500 mt-2">This reason will be included in the notification to the
                        contractor.</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 bg-gradient-to-b from-gray-50 to-gray-100 rounded-b-3xl flex justify-end gap-3">
            <button
                class="px-6 py-3 bg-white border-2 border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl transition-all duration-200 close-modal">Cancel</button>
            <button
                class="px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-semibold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl"
                id="confirmRejectShowcase">
                <span class="flex items-center gap-2">
                    Reject Showcase
                </span>
            </button>
        </div>
    </div>
</div>