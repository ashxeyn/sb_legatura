<div id="deletePaymentModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Delete Payment Validation</h2>
            <span class="close" onclick="PaymentDelete.close()">&times;</span>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 20px; font-size: 16px;">
                Are you sure you want to delete this payment validation?
            </p>

            <div class="form-group">
                <label for="delete_payment_reason">Reason for Deletion <span class="required">*</span></label>
                <textarea id="delete_payment_reason" name="reason" rows="4" placeholder="Please provide a reason for deleting this payment validation..." required style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px; font-family: inherit; resize: vertical;"></textarea>
                <small style="color: #65676b; font-size: 12px;">Maximum 500 characters</small>
            </div>

            <div id="deletePaymentErrorMessage" class="error-message" style="display: none;"></div>
            <div id="deletePaymentSuccessMessage" class="success-message" style="display: none;"></div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="PaymentDelete.close()">No, Keep It</button>
            <button type="button" class="btn btn-danger" onclick="PaymentDelete.confirm()">Yes, Delete</button>
        </div>
    </div>
</div>
