<!-- Submit Progress Report Modal -->
<div id="progressReportModal" class="progress-report-modal">
    <div class="modal-overlay" id="progressReportModalOverlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="modal-header-content">
                <h2 class="modal-title">
                    <i class="fi fi-rr-file-add"></i>
                    <span>Submit Progress Report</span>
                </h2>
                <button class="modal-close-btn" id="closeProgressReportModalBtn" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Project & Milestone Info -->
            <div class="info-section">
                <div class="info-item">
                    <span class="info-label">Project:</span>
                    <span class="info-value" id="modalProjectTitle">-</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Milestone:</span>
                    <span class="info-value" id="modalMilestoneTitle">-</span>
                </div>
            </div>

            <!-- Report Form -->
            <form id="progressReportForm" class="progress-report-form">
                <!-- Report Title -->
                <div class="form-group">
                    <label for="reportTitle" class="form-label">
                        <i class="fi fi-rr-edit"></i>
                        Report Title
                    </label>
                    <input 
                        type="text" 
                        id="reportTitle" 
                        name="reportTitle" 
                        class="form-input" 
                        placeholder="e.g., Progress Report 1.1 - Foundation Complete"
                        required
                    >
                </div>

                <!-- Report Description -->
                <div class="form-group">
                    <label for="reportDescription" class="form-label">
                        <i class="fi fi-rr-document"></i>
                        Description
                    </label>
                    <textarea 
                        id="reportDescription" 
                        name="reportDescription" 
                        class="form-textarea" 
                        rows="5" 
                        placeholder="Describe the progress made, work completed, and any relevant details..."
                        required
                    ></textarea>
                </div>

                <!-- File Upload -->
                <div class="form-group">
                    <label for="reportFiles" class="form-label">
                        <i class="fi fi-rr-clip"></i>
                        Attachments (Optional)
                    </label>
                    <div class="file-upload-area">
                        <input 
                            type="file" 
                            id="reportFiles" 
                            name="reportFiles[]" 
                            class="file-input" 
                            multiple 
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        >
                        <label for="reportFiles" class="file-upload-label">
                            <i class="fi fi-rr-cloud-upload"></i>
                            <span>Click to upload or drag and drop</span>
                            <span class="file-upload-hint">PDF, Images, or Documents (Max 10MB each)</span>
                        </label>
                    </div>
                    <div id="filePreviewContainer" class="file-preview-container">
                        <!-- Selected files will appear here -->
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button class="modal-btn secondary-btn" id="cancelReportBtn">
                <i class="fi fi-rr-cross-small"></i>
                Cancel
            </button>
            <button class="modal-btn submit-btn" id="submitReportBtn">
                <i class="fi fi-rr-check"></i>
                Submit Report
            </button>
        </div>
    </div>
</div>

<!-- Submission Confirmation Modal -->
<div id="submissionConfirmationModal" class="confirmation-modal">
    <div class="modal-overlay" id="submissionConfirmationModalOverlay"></div>
    <div class="confirmation-modal-container">
        <!-- Modal Header -->
        <div class="confirmation-modal-header">
            <div class="confirmation-icon-wrapper">
                <i class="fi fi-rr-check-circle confirmation-icon"></i>
            </div>
            <h2 class="confirmation-modal-title">Report Submitted Successfully</h2>
            <button class="confirmation-close-btn" id="closeSubmissionConfirmationBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="confirmation-modal-body">
            <p class="confirmation-message">
                Your progress report has been submitted successfully!
            </p>
            <p class="confirmation-submessage">
                The property owner will be notified and can review your report.
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="confirmation-modal-footer">
            <button class="confirmation-btn confirm-btn" id="closeSubmissionBtn">
                <i class="fi fi-rr-check"></i>
                Done
            </button>
        </div>
    </div>
</div>

<!-- Original Approval Confirmation Modal (kept for reference/viewing existing reports) -->
<div id="approvalConfirmationModal" class="confirmation-modal">
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
<div id="downloadConfirmationModal" class="confirmation-modal">
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
