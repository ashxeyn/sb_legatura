<!-- Payment History Modal -->
<div id="paymentHistoryModal" class="ph-modal">
    <div class="ph-modal-overlay" id="paymentHistoryModalOverlay"></div>
    <div class="ph-modal-container">
        <!-- Modal Header -->
        <div class="ph-modal-header">
            <button class="ph-back-btn" id="phBackBtn" title="Close">
                <i class="fi fi-rr-angle-left"></i>
            </button>
            <div class="ph-header-content">
                <h2 class="ph-header-title">Payment History</h2>
            </div>
            <button class="ph-close-btn" id="closePaymentHistoryModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross-small"></i>
            </button>
        </div>

        <!-- Modal Body: Payment List View -->
        <div class="ph-modal-body" id="phListView">
            <!-- Mark All as Read -->
            <div class="ph-mark-all-container">
                <a href="#" class="ph-mark-all-link" id="markAllReadLink">Mark all as read</a>
            </div>
            <!-- Payment entries injected here by JS -->
            <div class="ph-entries-list" id="paymentEntriesList"></div>

            <!-- Summary Card (injected by JS) -->
            <div class="ph-summary-card" id="phSummaryCard"></div>
        </div>

        <!-- Modal Body: Payment Detail View (hidden by default) -->
        <div class="ph-modal-body ph-detail-view" id="phDetailView" style="display:none;">
            <div id="phDetailContent">
                <!-- Detail content injected by JS -->
            </div>
        </div>
    </div>
</div>

