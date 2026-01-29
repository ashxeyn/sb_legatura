<!-- Progress Report Modal -->
<div id="progressReportModal" class="progress-report-modal">
    <div class="modal-overlay" id="progressReportModalOverlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="modal-header-content">
                <h2 class="modal-title" id="modalMilestoneTitle">Milestone 1: Structural Framing</h2>
                <button class="modal-close-btn" id="closeProgressReportModalBtn" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Project Context -->
            <div class="project-context-section">
                <span class="project-label">Project:</span>
                <a href="#" class="project-category-link" id="modalProjectCategory">Residential</a>
                <a href="#" class="project-type-link" id="modalProjectType">House Construction</a>
            </div>

            <!-- Milestone Description -->
            <div class="milestone-description-section">
                <p class="milestone-description-text" id="modalMilestoneDescription">
                    Complete structural framework including foundation, columns, beams, and roof structure.
                </p>
            </div>

            <!-- Attachments Section -->
            <div class="attachments-section">
                <h4 class="attachments-title">Attachments</h4>
                
                <!-- Main Preview Area -->
                <div class="attachments-preview-area" id="attachmentsPreviewArea">
                    <div class="preview-placeholder">
                        <span class="preview-placeholder-text">PDF</span>
                    </div>
                </div>

                <!-- File List -->
                <div class="attachments-file-list" id="attachmentsFileList">
                    <!-- Files will be dynamically inserted here -->
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button class="modal-btn reject-btn" id="rejectReportBtn">
                Reject
            </button>
            <button class="modal-btn approve-btn" id="approveReportBtn">
                Approve
            </button>
        </div>
    </div>
</div>

<!-- Approval Confirmation Modal -->
<div id="approvalConfirmationModal" class="confirmation-modal hidden">
    <div class="modal-overlay" id="approvalConfirmationModalOverlay"></div>
    <div class="confirmation-modal-container">
        <!-- Modal Header -->
        <div class="confirmation-modal-header">
            <div class="confirmation-icon-wrapper">
                <i class="fi fi-rr-check-circle confirmation-icon"></i>
            </div>
            <h2 class="confirmation-modal-title">Confirm Approval</h2>
            <button class="confirmation-close-btn" id="closeConfirmationModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="confirmation-modal-body">
            <p class="confirmation-message">
                Are you sure you want to approve this progress report?
            </p>
            <p class="confirmation-submessage">
                This action will mark the report as approved and notify the contractor.
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="confirmation-modal-footer">
            <button class="confirmation-btn cancel-btn" id="cancelApprovalBtn">
                Cancel
            </button>
            <button class="confirmation-btn confirm-btn" id="confirmApprovalBtn">
                <i class="fi fi-rr-check"></i>
                Confirm Approval
            </button>
        </div>
    </div>
</div>

<!-- Download Confirmation Modal -->
<div id="downloadConfirmationModal" class="confirmation-modal hidden">
    <div class="modal-overlay" id="downloadConfirmationModalOverlay"></div>
    <div class="confirmation-modal-container">
        <!-- Modal Header -->
        <div class="confirmation-modal-header">
            <div class="confirmation-icon-wrapper">
                <i class="fi fi-rr-download confirmation-icon"></i>
            </div>
            <h2 class="confirmation-modal-title">Confirm Download</h2>
            <button class="confirmation-close-btn" id="closeDownloadConfirmationModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="confirmation-modal-body">
            <p class="confirmation-message" id="downloadConfirmationMessage">
                Are you sure you want to download this file?
            </p>
            <p class="confirmation-submessage" id="downloadConfirmationSubmessage">
                The file will be downloaded to your device.
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="confirmation-modal-footer">
            <button class="confirmation-btn cancel-btn" id="cancelDownloadBtn">
                Cancel
            </button>
            <button class="confirmation-btn confirm-btn" id="confirmDownloadBtn">
                <i class="fi fi-rr-download"></i>
                Download
            </button>
        </div>
    </div>
</div>

<!-- Rejection Reason Modal -->
<div id="rejectionReasonModal" class="rejection-modal">
    <div class="modal-overlay" id="rejectionReasonModalOverlay"></div>
    <div class="rejection-modal-container">
        <!-- Modal Header -->
        <div class="rejection-modal-header">
            <div class="rejection-modal-header-content">
                <h2 class="rejection-modal-title">Reason for Rejection</h2>
                <button class="rejection-close-btn" id="closeRejectionModalBtn" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>
            <div class="rejection-milestone-info">
                <span class="rejection-milestone-label">Milestone:</span>
                <span class="rejection-milestone-value" id="rejectionMilestoneValue">Milestone 1: Structural Framing</span>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="rejection-modal-body">
            <!-- Rejection Reason Textarea -->
            <div class="rejection-reason-section">
                <label for="rejectionReasonTextarea" class="rejection-label">Reason for Rejection</label>
                <textarea 
                    id="rejectionReasonTextarea" 
                    class="rejection-textarea" 
                    placeholder="Describe the reason for the rejection"
                    rows="6"
                ></textarea>
            </div>

            <!-- Upload Section -->
            <div class="rejection-upload-section">
                <h4 class="rejection-upload-title">Upload Supporting Documents</h4>
                <div class="rejection-upload-area" id="rejectionUploadArea">
                    <input type="file" id="rejectionFileInput" multiple accept="image/*,.pdf,.doc,.docx" style="display: none;">
                    <div class="upload-content">
                        <i class="fi fi-rr-cloud-upload upload-icon"></i>
                        <p class="upload-text">Upload image or file</p>
                    </div>
                </div>
                <p class="upload-hint">e.g., document, photos, etc.</p>
                
                <!-- Uploaded Files List -->
                <div class="rejection-uploaded-files" id="rejectionUploadedFiles">
                    <!-- Files will be dynamically inserted here -->
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="rejection-modal-footer">
            <button class="rejection-btn cancel-rejection-btn" id="cancelRejectionBtn">
                Cancel
            </button>
            <button class="rejection-btn submit-rejection-btn" id="submitRejectionBtn">
                Submit
            </button>
        </div>
    </div>
</div>

<!-- Milestone Payment Modal -->
<div id="milestonePaymentModal" class="payment-modal">
    <div class="modal-overlay" id="milestonePaymentModalOverlay"></div>
    <div class="payment-modal-container">
        <!-- Modal Header -->
        <div class="payment-modal-header">
            <div class="payment-modal-header-content">
                <h2 class="payment-modal-title">Milestone Payment</h2>
                <button class="payment-close-btn" id="closePaymentModalBtn" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>
            <div class="payment-milestone-info">
                <span class="payment-milestone-label">Payment for:</span>
                <span class="payment-milestone-value" id="paymentMilestoneValue">Milestone 1: Structural Framing</span>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="payment-modal-body">
            <!-- Input Fields Section -->
            <div class="payment-input-section">
                <!-- Receipt Number -->
                <div class="payment-input-group">
                    <label for="receiptNumberInput" class="payment-input-label">Receipt number</label>
                    <input 
                        type="text" 
                        id="receiptNumberInput" 
                        class="payment-input" 
                        placeholder="Enter receipt number"
                    >
                </div>

                <!-- Amount Paid -->
                <div class="payment-input-group">
                    <label for="amountPaidInput" class="payment-input-label">Amount paid</label>
                    <input 
                        type="text" 
                        id="amountPaidInput" 
                        class="payment-input" 
                        placeholder="Enter amount paid"
                    >
                </div>

                <!-- Description -->
                <div class="payment-input-group">
                    <label for="paymentDescriptionTextarea" class="payment-input-label">Description</label>
                    <textarea 
                        id="paymentDescriptionTextarea" 
                        class="payment-textarea" 
                        placeholder="Enter payment description"
                        rows="4"
                    ></textarea>
                </div>
            </div>

            <!-- Upload Receipt Section -->
            <div class="payment-upload-section">
                <h4 class="payment-upload-title">Upload Receipt</h4>
                <div class="payment-upload-area" id="paymentUploadArea">
                    <input type="file" id="paymentFileInput" accept="image/*,.pdf" style="display: none;">
                    <div class="payment-upload-content">
                        <i class="fi fi-rr-cloud-upload payment-upload-icon"></i>
                        <p class="payment-upload-text">Upload image or file</p>
                    </div>
                </div>
                
                <!-- Uploaded Receipt Preview -->
                <div class="payment-uploaded-receipt" id="paymentUploadedReceipt">
                    <!-- Receipt preview will be dynamically inserted here -->
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="payment-modal-footer">
            <button class="payment-btn cancel-payment-btn" id="cancelPaymentBtn">
                Cancel
            </button>
            <button class="payment-btn submit-payment-btn" id="submitPaymentBtn">
                Submit
            </button>
        </div>
    </div>
</div>
