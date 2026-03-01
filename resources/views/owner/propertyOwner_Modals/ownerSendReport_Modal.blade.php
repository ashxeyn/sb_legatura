<!-- Send Report (File Dispute) Modal -->
<div id="sendReportModal" class="sr-modal">
    <div class="sr-modal-overlay" id="sendReportModalOverlay"></div>
    <div class="sr-modal-container">
        <!-- Modal Header -->
        <div class="sr-modal-header">
            <button class="sr-back-btn" id="srBackBtn" title="Back">
                <i class="fi fi-rr-angle-left"></i>
            </button>
            <div class="sr-header-content">
                <h2 class="sr-header-title">File a Report</h2>
            </div>
            <button class="sr-close-btn" id="closeSendReportModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross-small"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="sr-modal-body" id="srFormView">
            <!-- Project / Milestone Context Card (populated by JS) -->
            <div class="sr-context-card" id="srContextCard" style="display:none;">
                <div class="sr-context-row">
                    <i class="fi fi-rr-folder" style="color:#EC7E00;"></i>
                    <div class="sr-context-text">
                        <span class="sr-context-label">Project</span>
                        <span class="sr-context-value" id="srContextProject"></span>
                    </div>
                </div>
                <div class="sr-context-row" id="srContextItemRow" style="display:none;">
                    <i class="fi fi-rr-flag" style="color:#EC7E00;"></i>
                    <div class="sr-context-text">
                        <span class="sr-context-label">Milestone Item</span>
                        <span class="sr-context-value" id="srContextItem"></span>
                    </div>
                </div>
            </div>

            <!-- Info Banner -->
            <div class="sr-info-banner">
                <i class="fi fi-rr-info"></i>
                <span>Filing a dispute will notify the other party and an admin will review your case.</span>
            </div>

            <!-- Form -->
            <form id="sendReportForm" class="sr-form" novalidate>
                <!-- Report Type -->
                <div class="sr-form-group">
                    <label class="sr-form-label">
                        Report Type <span class="sr-required">*</span>
                    </label>
                    <div class="sr-type-grid" id="srTypeGrid">
                        <button type="button" class="sr-type-option" data-value="Payment">
                            <i class="fi fi-rr-dollar"></i>
                            <span>Payment</span>
                        </button>
                        <button type="button" class="sr-type-option" data-value="Delay">
                            <i class="fi fi-rr-clock"></i>
                            <span>Delay</span>
                        </button>
                        <button type="button" class="sr-type-option" data-value="Quality">
                            <i class="fi fi-rr-exclamation"></i>
                            <span>Quality</span>
                        </button>
                        <button type="button" class="sr-type-option" data-value="Halt">
                            <i class="fi fi-rr-pause-circle"></i>
                            <span>Halt</span>
                        </button>
                        <button type="button" class="sr-type-option" data-value="Others">
                            <i class="fi fi-rr-menu-dots"></i>
                            <span>Others</span>
                        </button>
                    </div>
                    <input type="hidden" name="dispute_type" id="srDisputeType">
                </div>

                <!-- Others Specify (hidden by default) -->
                <div class="sr-form-group" id="srOthersGroup" style="display:none;">
                    <label class="sr-form-label">
                        Specify Type <span class="sr-required">*</span>
                    </label>
                    <input type="text" name="if_others_distype" id="srOthersInput"
                           class="sr-form-input" placeholder="Please specify the report type" maxlength="255">
                </div>

                <!-- Milestone Selection -->
                <div class="sr-form-group">
                    <label class="sr-form-label">
                        Milestone Item <span class="sr-required">*</span>
                    </label>
                    <select name="milestone_item_id" id="srMilestoneSelect" class="sr-form-select">
                        <option value="">Select a milestone item</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="sr-form-group">
                    <label class="sr-form-label">
                        Description <span class="sr-required">*</span>
                    </label>
                    <textarea name="dispute_desc" id="srDescription" class="sr-form-textarea"
                              placeholder="Describe the issue in detail. Include specific dates, amounts, or other relevant information..."
                              maxlength="2000" rows="5"></textarea>
                    <div class="sr-char-count">
                        <span id="srCharCount">0</span>/2000
                    </div>
                </div>

                <!-- Evidence Files -->
                <div class="sr-form-group">
                    <label class="sr-form-label">Evidence Files</label>
                    <div class="sr-file-upload" id="srFileUpload">
                        <i class="fi fi-rr-cloud-upload-alt"></i>
                        <span class="sr-file-title">Upload Evidence</span>
                        <span class="sr-file-hint">JPG, PNG, PDF, DOC — Max 5MB each, up to 10 files</span>
                        <input type="file" name="evidence_files[]" id="srFileInput"
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" multiple class="sr-file-hidden">
                    </div>
                    <div class="sr-file-list" id="srFileList"></div>
                </div>

                <!-- Submit Button -->
                <div class="sr-form-actions">
                    <button type="button" class="sr-cancel-btn" id="srCancelBtn">Cancel</button>
                    <button type="submit" class="sr-submit-btn" id="srSubmitBtn">
                        <i class="fi fi-rr-paper-plane"></i>
                        Submit Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Success View (hidden by default) -->
        <div class="sr-modal-body sr-success-view" id="srSuccessView" style="display:none;">
            <div class="sr-success-content">
                <div class="sr-success-icon">
                    <i class="fi fi-rr-check-circle"></i>
                </div>
                <h3 class="sr-success-title">Report Submitted!</h3>
                <p class="sr-success-text">Your report has been submitted successfully. Our team will review it and take appropriate action.</p>
                <button class="sr-success-btn" id="srSuccessCloseBtn">Done</button>
            </div>
        </div>
    </div>
</div>
