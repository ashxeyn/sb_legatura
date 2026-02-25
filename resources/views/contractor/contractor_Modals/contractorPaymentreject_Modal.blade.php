<!-- Payment Reject Modal -->
<div id="paymentRejectModal" class="payment-reject-modal">
    <div class="payment-reject-modal-overlay" id="paymentRejectModalOverlay"></div>
    <div class="modal-container" style="max-width: 500px;">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="modal-header-content">
                <h2 class="modal-title">Decline Payment Validation</h2>
            </div>
            <button class="report-menu-btn" id="closePaymentRejectModalBtn" aria-label="Close modal"
                style="border: none; background: transparent; cursor: pointer; color: #64748b; font-size: 1.25rem;">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <p class="payment-reject-instructions" style="color: #475569; font-size: 0.875rem; margin-bottom: 1rem;">
                Please provide a reason for declining this payment receipt. This will be visible to the property owner.
            </p>

            <form id="paymentRejectForm">
                <input type="hidden" id="rejectPaymentId" name="payment_id">

                <div class="form-group">
                    <label for="rejectReason" class="form-label">Reason for Decline<span
                            class="required">*</span></label>
                    <textarea id="rejectReason" name="reason" class="form-textarea"
                        placeholder="Explain why the receipt is being rejected (e.g., Image is blurry, Amount does not match, Incorrect reference number)..."
                        rows="4" required></textarea>
                    <div class="error-message" id="rejectReasonError"></div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="modal-btn close-btn" id="cancelRejectBtn">Cancel</button>
            <button type="button" class="modal-btn approve-btn" id="submitRejectBtn"
                style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white;">
                <i class="fi fi-rr-cross-circle"></i> Decline Payment
            </button>
        </div>
    </div>
</div>