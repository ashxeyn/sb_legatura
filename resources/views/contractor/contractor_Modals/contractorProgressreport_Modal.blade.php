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
                <input type="hidden" name="item_id" id="formItemId" value="">
                <input type="hidden" id="editingProgressId" value="">
                <input type="hidden" name="deleted_file_ids" id="deletedFileIds" value="">

                <!-- Purpose (matches TSX "purpose" field) -->
                <div class="form-group">
                    <label for="reportPurpose" class="form-label">
                        <i class="fi fi-rr-document"></i>
                        Purpose <span style="color: #ef4444;">*</span>
                    </label>
                    <p class="form-hint">Describe the progress made on this milestone (max 1000 characters)</p>
                    <textarea id="reportPurpose" name="purpose" class="form-textarea" rows="5" maxlength="1000"
                        placeholder="Describe the work completed, materials used, or progress made..."
                        required></textarea>
                    <div class="char-counter">
                        <span id="purposeCharCount">0</span>/1000
                    </div>
                    <div class="error-message" id="error_purpose"
                        style="display: none; color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;"></div>
                </div>

                <!-- File Upload (matches TSX: required, 1-10 files, PDF/DOC/DOCX/ZIP/JPG/PNG, 10MB each) -->
                <div class="form-group">
                    <label for="reportFiles" class="form-label">
                        <i class="fi fi-rr-clip"></i>
                        Attachments <span style="color: #ef4444;">*</span>
                    </label>
                    <p class="form-hint">Upload 1-10 files (PDF, DOC, DOCX, ZIP, JPG, JPEG, PNG). Max 10MB each.</p>
                    <div class="file-upload-area">
                        <input type="file" id="reportFiles" name="progress_files[]" class="file-input" multiple
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.zip">
                        <label for="reportFiles" class="file-upload-label">
                            <i class="fi fi-rr-cloud-upload"></i>
                            <span>Click to upload or drag and drop</span>
                            <span class="file-upload-hint">PDF, Images, Documents, or ZIP (Max 10MB each)</span>
                        </label>
                    </div>
                    <div class="files-counter" id="filesCounter" style="display: none;">
                        <span id="fileCount">0</span>/10 files
                    </div>
                    <div id="filePreviewContainer" class="file-preview-container">
                        <!-- Selected files will appear here -->
                    </div>
                    <div class="error-message" id="error_progress_files"
                        style="display: none; color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;"></div>
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

<!-- Submission Success Modal -->
<div id="progressUploadSuccessModal" class="upload-success-modal">
    <div class="modal-overlay" id="progressUploadSuccessModalOverlay"></div>
    <div class="upload-success-modal-container">
        <!-- Modal Header -->
        <div class="upload-success-modal-header">
            <div class="success-icon-wrapper">
                <i class="fi fi-rr-check-circle success-icon"></i>
            </div>
            <h2 class="upload-success-modal-title">Report Submitted Successfully</h2>
            <button class="success-close-btn" id="closeUploadSuccessBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="upload-success-modal-body">
            <p class="success-message">
                Your progress report has been submitted successfully!
            </p>
            <p class="success-submessage">
                The property owner will be notified and can review your report.
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="upload-success-modal-footer">
            <button class="success-btn done-btn" id="doneSuccessBtn">
                <i class="fi fi-rr-check"></i>
                Done
            </button>
        </div>
    </div>
</div>

<!-- Removes approval and download confirmation modals from upload form -->

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
                <span class="rejection-milestone-value" id="rejectionMilestoneValue">Milestone 1: Structural
                    Framing</span>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="rejection-modal-body">
            <!-- Rejection Reason Textarea -->
            <div class="rejection-reason-section">
                <label for="rejectionReasonTextarea" class="rejection-label">Reason for Rejection</label>
                <textarea id="rejectionReasonTextarea" class="rejection-textarea"
                    placeholder="Describe the reason for the rejection" rows="6"></textarea>
            </div>

            <!-- Upload Section -->
            <div class="rejection-upload-section">
                <h4 class="rejection-upload-title">Upload Supporting Documents</h4>
                <div class="rejection-upload-area" id="rejectionUploadArea">
                    <input type="file" id="rejectionFileInput" multiple accept="image/*,.pdf,.doc,.docx"
                        style="display: none;">
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
                    <input type="text" id="receiptNumberInput" class="payment-input" placeholder="Enter receipt number">
                </div>

                <!-- Amount Paid -->
                <div class="payment-input-group">
                    <label for="amountPaidInput" class="payment-input-label">Amount paid</label>
                    <input type="text" id="amountPaidInput" class="payment-input" placeholder="Enter amount paid">
                </div>

                <!-- Description -->
                <div class="payment-input-group">
                    <label for="paymentDescriptionTextarea" class="payment-input-label">Description</label>
                    <textarea id="paymentDescriptionTextarea" class="payment-textarea"
                        placeholder="Enter payment description" rows="4"></textarea>
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