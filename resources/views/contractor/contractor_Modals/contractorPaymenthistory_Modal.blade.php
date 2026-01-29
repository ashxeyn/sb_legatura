<!-- Payment History Modal -->
<div id="paymentHistoryModal" class="payment-history-modal">
    <div class="modal-overlay" id="paymentHistoryModalOverlay"></div>
    <div class="payment-history-modal-container">
        <!-- Modal Header -->
        <div class="payment-history-modal-header">
            <div class="payment-history-header-content">
                <h2 class="payment-history-modal-title">Payment history</h2>
                <a href="#" class="mark-all-read-link" id="markAllReadLink">Mark all as read</a>
            </div>
            <button class="payment-history-close-btn" id="closePaymentHistoryModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="payment-history-modal-body">
            <!-- Payment Entries List -->
            <div class="payment-entries-list" id="paymentEntriesList">
                <!-- Payment entries will be dynamically inserted here -->
            </div>
        </div>

        <!-- Modal Footer - Summary -->
        <div class="payment-history-modal-footer">
            <div class="payment-summary-section">
                <div class="payment-summary-item">
                    <span class="payment-summary-label">Total Estimated Project Amount:</span>
                    <span class="payment-summary-value estimated" id="totalEstimatedAmount">₱36,000,000</span>
                </div>
                <div class="payment-summary-item">
                    <span class="payment-summary-label">Total Amount Paid:</span>
                    <span class="payment-summary-value paid" id="totalAmountPaid">-₱32,400,000</span>
                </div>
                <div class="payment-summary-item">
                    <span class="payment-summary-label">Total Remaining Amount:</span>
                    <span class="payment-summary-value remaining" id="totalRemainingAmount">₱3,600,000</span>
                </div>
            </div>
        </div>
    </div>
</div>
