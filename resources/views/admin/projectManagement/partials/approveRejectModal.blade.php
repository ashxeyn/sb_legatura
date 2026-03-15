<!-- Approve Showcase Modal -->
<div id="approveShowcaseModal"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2 sm:p-3">
    <div
        class="modal-content bg-white w-full max-w-md rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[84vh] flex flex-col">
        <div
            class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-green-500 to-emerald-600 border-b border-green-600 text-white flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div
                    class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold leading-tight">Approve Showcase</h3>
                    <p class="text-[10px] text-green-100">Publish this showcase post</p>
                </div>
            </div>
            <button class="p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition close-modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <div class="modal-scroll-hidden flex-1 overflow-y-auto p-4 space-y-3.5">
            <div class="rounded-xl border border-green-200 bg-green-50 p-3.5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-green-700 mb-1">Showcase Title</p>
                <p class="text-base font-bold text-green-900 leading-tight" id="approveModalTitle">Showcase Title Here</p>
            </div>

            <div class="rounded-xl border border-blue-200 bg-blue-50 p-3 flex gap-2.5">
                <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-blue-800 leading-relaxed">Once approved, this showcase will be visible in the public feed and the contractor profile.</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <h4 class="text-sm font-semibold text-gray-800 mb-1">Confirm Approval</h4>
                <p class="text-xs text-gray-600">Please review the content before confirming this action.</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 px-4 py-3 bg-white border-t border-gray-200 flex-shrink-0">
            <button
                class="px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-[12px] font-medium close-modal">Cancel</button>
            <button
                class="px-3.5 py-2 rounded-lg bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 transition text-[12px] font-semibold"
                id="confirmApproveShowcase">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2 sm:p-3">
    <div
        class="modal-content bg-white w-full max-w-md rounded-2xl shadow-2xl border border-gray-200 overflow-hidden max-h-[84vh] flex flex-col">
        <div
            class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-r from-red-500 to-rose-600 border-b border-rose-600 text-white flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div
                    class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold leading-tight">Reject Showcase</h3>
                    <p class="text-[10px] text-rose-100">Decline this showcase post</p>
                </div>
            </div>
            <button class="p-1.5 rounded-xl hover:bg-white/20 text-white/80 hover:text-white transition close-modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <div class="modal-scroll-hidden flex-1 overflow-y-auto p-4 space-y-3.5">
            <div class="rounded-xl border border-red-200 bg-red-50 p-3.5">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-red-700 mb-1">Showcase Title</p>
                <p class="text-base font-bold text-red-900 leading-tight" id="rejectModalTitle">Showcase Title Here</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <h4 class="text-sm font-semibold text-gray-800 mb-1">Confirm Rejection</h4>
                <p class="text-xs text-gray-600">You are about to reject this showcase post.</p>
            </div>

            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-3 flex gap-2.5">
                <svg class="w-4 h-4 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-yellow-800 leading-relaxed">The contractor will be notified together with your rejection reason.</p>
            </div>

            <div class="text-left">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Reason for Rejection <span
                        class="text-red-500">*</span></label>
                <textarea id="rejectReason" rows="3"
                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-400 focus:border-red-400 focus:outline-none resize-none text-sm"
                    placeholder="Please provide a clear reason for rejecting this showcase..."></textarea>
                <p id="rejectReasonError" class="text-xs text-red-500 font-medium mt-1 hidden"></p>
                <p class="text-[11px] text-gray-500 mt-1.5">This reason is included in the contractor notification.</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 px-4 py-3 bg-white border-t border-gray-200 flex-shrink-0">
            <button
                class="px-3.5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-[12px] font-medium close-modal">Cancel</button>
            <button
                class="px-3.5 py-2 rounded-lg bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition text-[12px] font-semibold"
                id="confirmRejectShowcase">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    Reject Showcase
                </span>
            </button>
        </div>
    </div>
</div>
