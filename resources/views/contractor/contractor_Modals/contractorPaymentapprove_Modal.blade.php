<!-- Payment Approve Modal -->
<div id="paymentApproveModal" class="payment-reject-modal">
    <!-- reusing base classes from reject modal for consistency -->
    <div class="payment-reject-modal-overlay" id="paymentApproveModalOverlay"></div>
    <div class="modal-container" style="max-width: 500px;">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="modal-header-content">
                <h2 class="modal-title" style="color: #166534;">Approve Payment</h2>
            </div>
            <button class="report-menu-btn" id="closePaymentApproveModalBtn" aria-label="Close modal"
                style="border: none; background: transparent; cursor: pointer; color: #64748b; font-size: 1.25rem;">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <p class="payment-reject-instructions"
                style="color: #1e293b; font-size: 1rem; margin-bottom: 0.5rem; font-weight: 500;">
                Are you sure you want to approve this payment receipt?
            </p>
            <p class="payment-reject-instructions" style="color: #475569;">
                Once approved, the payment amount will be applied to the milestone's paid balance and recorded in the
                project's financial history. This action cannot be undone.
            </p>

            <form id="paymentApproveForm">
                <input type="hidden" id="approvePaymentId" name="payment_id">
                <div class="error-message" id="approveErrorMsg"></div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="modal-btn close-btn" id="cancelApproveBtn">Cancel</button>
            <button type="button" class="modal-btn approve-btn" id="submitApproveBtn"
                style="background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);">
                <i class="fi fi-rr-check-circle"></i> Approve Payment
            </button>
        </div>
    </div>
</div>