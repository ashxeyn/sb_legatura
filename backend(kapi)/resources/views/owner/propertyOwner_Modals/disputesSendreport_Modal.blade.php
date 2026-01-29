<!-- Send Report Modal -->
<div id="sendReportModal" class="send-report-modal hidden">
    <!-- Modal Overlay -->
    <div class="modal-overlay"></div>
    
    <!-- Modal Content -->
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="header-content">
                <h2 class="modal-title">File a Dispute</h2>
            </div>
            <button type="button" class="modal-close-btn" id="sendReportCloseBtn">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Project and Milestone Info -->
            <div class="info-card">
                <div class="info-section">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fi fi-rr-folder"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Report For:</span>
                            <span class="info-value" id="reportProjectName">Milestone Progress Report</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fi fi-rr-bookmark"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Milestone:</span>
                            <span class="info-value" id="reportMilestoneName">PHASE 1</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="info-alert">
                <div class="alert-icon">
                    <i class="fi fi-rr-info"></i>
                </div>
                <div class="alert-text">
                    <p>This report will notify the other party about the milestone progress. Ensure all information is accurate and complete.</p>
                </div>
            </div>

            <!-- Report Form -->
            <form id="sendReportForm" class="send-report-form">
                <!-- Report Type -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-text">Dispute Type</span>
                        <span class="required-asterisk">*</span>
                    </label>
                    <div class="dispute-type-options">
                        <button type="button" class="dispute-option" data-value="payment-issue">
                            <div class="option-icon payment">
                                <i class="fi fi-rr-dollar"></i>
                            </div>
                            <div class="option-content">
                                <h4 class="option-title">Payment Issue</h4>
                                <p class="option-description">Payment not received or incorrect amount</p>
                            </div>
                        </button>

                        <button type="button" class="dispute-option" data-value="project-delay">
                            <div class="option-icon delay">
                                <i class="fi fi-rr-hourglass"></i>
                            </div>
                            <div class="option-content">
                                <h4 class="option-title">Project Delay</h4>
                                <p class="option-description">Work not completed on time</p>
                            </div>
                        </button>

                        <button type="button" class="dispute-option" data-value="quality-issue">
                            <div class="option-icon quality">
                                <i class="fi fi-rr-info"></i>
                            </div>
                            <div class="option-content">
                                <h4 class="option-title">Quality Issue</h4>
                                <p class="option-description">Work quality below expectations</p>
                            </div>
                        </button>

                        <button type="button" class="dispute-option" data-value="other-issue">
                            <div class="option-icon other">
                                <i class="fi fi-rr-menu-dots"></i>
                            </div>
                            <div class="option-content">
                                <h4 class="option-title">Other Issue</h4>
                                <p class="option-description">Specify other concerns</p>
                            </div>
                        </button>
                    </div>
                    <input type="hidden" id="reportType" name="reportType" required>
                    
                    <!-- Specify Dispute Type (for Other Issue) -->
                    <div id="specifyDisputeContainer" class="specify-dispute-container hidden">
                        <label for="specifyDisputeType" class="form-label">
                            <span class="label-text">Specify Dispute Type</span>
                            <span class="required-asterisk">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="specifyDisputeType" 
                            name="specifyDisputeType" 
                            class="form-input-text" 
                            placeholder="e.g., Safety concerns, Contract violation"
                            maxlength="100"
                        >
                    </div>
                </div>

                <!-- Detailed Description -->
                <div class="form-group">
                    <label for="reportDescription" class="form-label">
                        <span class="label-text">Detailed Description</span>
                        <span class="required-asterisk">*</span>
                    </label>
                    <p class="form-label-hint">Provide a clear and detailed explanation. Include dates, amounts, milestones, or any relevant information.</p>
                    <div class="textarea-wrapper">
                        <textarea 
                            id="reportDescription" 
                            name="reportDescription" 
                            class="form-textarea" 
                            placeholder="Describe the report details..." 
                            maxlength="2000"
                            required></textarea>
                    </div>
                    <div class="character-count">
                        <span id="charCount">0</span> / 2000
                    </div>
                </div>

                <!-- Attachments (Optional) -->
                <div class="form-group">
                    <label for="reportAttachments" class="form-label">
                        <span class="label-text">Evidence Files</span>
                        <span class="optional-badge">Optional</span>
                    </label>
                    <p class="form-label-hint">Upload supporting documents, images, or screenshots (Max 10 files, 5MB each)</p>
                    <div class="file-upload-area" id="fileUploadArea">
                        <button type="button" class="upload-files-btn">
                            <svg class="upload-icon-svg" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 18C4.23858 18 2 15.7614 2 13C2 10.2386 4.23858 8 7 8C7.33333 8 7.66667 8.03333 8 8.1C8.4 5.6 10.4 4 13 4C15.7614 4 18 6.23858 18 9V9.5C20.4853 9.5 22.5 11.5147 22.5 14C22.5 16.4853 20.4853 18.5 18 18.5H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 12V21M12 12L9 15M12 12L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Upload Files</span>
                        </button>
                        <input 
                            type="file" 
                            id="reportAttachments" 
                            name="reportAttachments" 
                            multiple 
                            class="file-input"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                        >
                    </div>
                    <div id="fileList" class="file-list"></div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="sendReportCancelBtn">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fi fi-rr-paper-plane"></i>
                        <span>Send Report</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="sendReportSuccessModal" class="send-report-success-modal hidden">
    <div class="modal-overlay"></div>
    <div class="success-modal-content">
        <button type="button" class="success-close-btn" id="sendReportSuccessClose">
            <i class="fi fi-rr-cross"></i>
        </button>
        <div class="success-icon">
            <span class="success-check">✓</span>
        </div>
        <h3 class="success-title">Dispute Report Submitted</h3>
        <p class="success-message">Your dispute report has been successfully sent. We'll notify the other party and keep you updated.</p>
        <div class="success-actions">
            <button type="button" class="btn-success-primary" id="sendReportSuccessDone">Done</button>
        </div>
    </div>
</div>
