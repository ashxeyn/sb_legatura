<!-- Delete Showcase Modal -->
<div id="deleteShowcaseModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2">
    <div
        class="bg-white rounded-lg shadow-lg max-w-xs w-full transform transition-all duration-300 scale-95 modal-content relative">
        <button type="button"
            class="absolute top-2 right-2 w-6 h-6 rounded-md border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition flex items-center justify-center close-modal">
            <i class="fi fi-rr-cross text-[10px]"></i>
        </button>

        <div class="flex justify-center pt-3 pb-2">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center relative">
                <div class="absolute inset-0 bg-red-200 rounded-full animate-ping opacity-60"></div>
                <div class="relative w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                    <i class="fi fi-rr-trash text-white text-sm"></i>
                </div>
            </div>
        </div>

        <div class="px-3 pb-2.5 text-center">
            <h3 class="text-sm font-bold text-gray-800 mb-1.5">Delete Showcase</h3>
            <p class="text-[11px] text-gray-600 leading-relaxed mb-2.5">
                Permanently delete <span id="deleteModalTitle" class="font-bold text-gray-800">this showcase</span>? This action cannot be undone.
            </p>

            <div class="text-left">
                <label class="block text-[11px] font-medium text-gray-700 mb-1">Reason for Deletion <span
                        class="text-red-500">*</span></label>
                <textarea id="deleteReason" rows="2"
                    class="w-full px-2 py-1.5 text-[11px] border border-gray-300 rounded-md focus:ring-2 focus:ring-red-300 focus:border-red-300 focus:outline-none transition resize-none"
                    placeholder="Please provide a reason for deletion..."></textarea>
                <p id="deleteReasonError" class="text-red-500 text-[11px] mt-1 hidden"></p>
            </div>
        </div>

        <div class="px-3 pb-3 space-y-1.5">
            <button id="confirmDeleteShowcase"
                class="w-full px-3 py-1.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-md transition-all text-[11px] font-semibold shadow-sm hover:shadow-md transform hover:scale-[1.01] active:scale-95">
                <span class="flex items-center justify-center gap-1">Delete Showcase</span>
            </button>
            <button type="button"
                class="w-full px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-all text-[11px] font-semibold hover:border-gray-400 hover:shadow-sm transform hover:scale-[1.01] active:scale-95 close-modal">Cancel</button>
        </div>
    </div>
</div>

<!-- Restore Showcase Modal -->
<div id="restoreShowcaseModal"
    class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-2">
    <div
        class="bg-white rounded-lg shadow-lg max-w-xs w-full transform transition-all duration-300 scale-95 modal-content relative">
        <button type="button"
            class="absolute top-2 right-2 w-6 h-6 rounded-md border border-gray-200 text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition flex items-center justify-center close-modal">
            <i class="fi fi-rr-cross text-[10px]"></i>
        </button>

        <div class="flex justify-center pt-3 pb-2">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center relative">
                <div class="absolute inset-0 bg-green-200 rounded-full animate-ping opacity-60"></div>
                <div class="relative w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                    <i class="fi fi-rr-arrow-small-right text-white text-sm"></i>
                </div>
            </div>
        </div>

        <div class="px-3 pb-2.5 text-center">
            <h3 class="text-sm font-bold text-gray-800 mb-1.5">Restore Showcase</h3>
            <p class="text-[11px] text-gray-600 leading-relaxed mb-2.5">
                Restore <span id="restoreModalTitle" class="font-bold text-gray-800">this showcase</span> and make it visible again?
            </p>
            <p class="text-[10px] text-gray-500">
                This will set the showcase back to approved status.
            </p>
        </div>

        <div class="px-3 pb-3 space-y-1.5">
            <button id="confirmRestoreShowcase"
                class="w-full px-3 py-1.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-md transition-all text-[11px] font-semibold shadow-sm hover:shadow-md transform hover:scale-[1.01] active:scale-95">
                <span class="flex items-center justify-center gap-1">Restore Showcase</span>
            </button>
            <button type="button"
                class="w-full px-3 py-1.5 border-2 border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-all text-[11px] font-semibold hover:border-gray-400 hover:shadow-sm transform hover:scale-[1.01] active:scale-95 close-modal">Cancel</button>
        </div>
    </div>
</div>