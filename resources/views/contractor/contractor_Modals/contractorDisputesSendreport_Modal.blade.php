<!-- Contractor Send Report Modal -->
<div id="sendReportModal" class="send-report-modal hidden">
    <!-- Modal Overlay -->
    <div class="modal-overlay"></div>

    <!-- Modal Content -->
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="header-content">
                <h2 class="modal-title">File a Dispute</h2>
                <p class="modal-subtitle">Report an issue or request a project halt</p>
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
                            <span class="info-label">Project:</span>
                            <span class="info-value"
                                id="reportProjectName">{{ $project->project_title ?? 'Project' }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fi fi-rr-bookmark"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">Milestone Item:</span>
                            <span class="info-value" id="reportMilestoneName">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Form -->
            <form id="sendReportForm" class="send-report-form" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" id="reportMilestoneId" name="milestone_id">
                <input type="hidden" id="reportItemId" name="milestone_item_id">
                <input type="hidden" id="reportProjectId" name="project_id" value="{{ $project->project_id ?? '' }}">

                <!-- Report Type -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-text">Select Dispute Type</span>
                        <span class="required-asterisk">*</span>
                    </label>
                    <div class="dispute-type-options">
                        <button type="button" class="dispute-option" data-value="Payment">
                            <div class="option-icon payment">
                                <i class="fi fi-rr-dollar"></i>
                            </div>
                            <div class="option-content">
                                <h4 class="option-title">Payment Issue</h4>
                                <p class="option-description">Issue with settlement or payment verification</p>
                            </div>
                        </button>

                        <button type="button" class="dispute-option" data-value="Delay">
                            <div class="option-icon delay">
                                <i class="fi fi-rr-hourglass"></i>
                            </div>
                            <div class="option-content">
                                <h4 class="option-title">Project Delay</h4>
                                <p class="option-description">Significant delays in project progress</p>
                            </div>
                        </button>

                        <button type="button" class="dispute-option" data-value="Quality">
                            <div class="option-icon quality">
                                <i class="fi fi-rr-info"></i>
                            </div>
                            <div class="option-content">
                                <h4 class="option-title">Quality Issue</h4>
                                <p class="option-description">Concerns about specs or craftsmanship</p>
                            </div>
                        </button>

                        <button type="button" class="dispute-option" data-value="Halt">
                            <div class="option-icon halt">
                                <i class="fi fi-rr-pause-circle"></i>
                            </div>
                            <div class="option-content">
                                <h4 class="option-title">Request to Halt</h4>
                                <p class="option-description">Request to pause work on this project</p>
                            </div>
                        </button>

                        <button type="button" class="dispute-option" data-value="Others">
                            <div class="option-icon other">
                                <i class="fi fi-rr-menu-dots"></i>
                            </div>
                            <div class="option-content">
                                <h4 class="option-title">Other Issue</h4>
                                <p class="option-description">Other concerns not listed above</p>
                            </div>
                        </button>
                    </div>
                    <input type="hidden" id="reportType" name="dispute_type">
                    <div class="error-message" id="error-dispute_type"></div>

                    <!-- Specify Dispute Type (Visible when Others is selected) -->
                    <div id="specifyDisputeContainer" class="specify-dispute-container hidden">
                        <label for="if_others_distype" class="form-label">
                            <span class="label-text">Please specify</span>
                            <span class="required-asterisk">*</span>
                        </label>
                        <input type="text" id="if_others_distype" name="if_others_distype" class="form-input-text"
                            placeholder="Specify other issue type..." maxlength="255">
                        <div class="error-message" id="error-if_others_distype"></div>
                    </div>
                </div>

                <!-- Detailed Description -->
                <div class="form-group">
                    <label for="reportDescription" class="form-label">
                        <span class="label-text">Description</span>
                        <span class="required-asterisk">*</span>
                    </label>
                    <textarea id="reportDescription" name="dispute_desc" class="form-textarea"
                        placeholder="Explain the issue in detail..." maxlength="2000"></textarea>
                    <div class="error-message" id="error-dispute_desc"></div>
                    <div class="character-count">
                        <span id="charCount">0</span> / 2000
                    </div>
                </div>

                <!-- Proof of Issue (File Upload) -->
                <div class="form-group">
                    <label class="form-label">
                        <span class="label-text">Proof of Issue</span>
                        <span class="required-asterisk">*</span>
                    </label>
                    <p class="form-label-hint">Upload photos or documents as evidence (Max 5 files, 5MB each)</p>

                    <div class="file-upload-area" id="disputeFileUploadArea">
                        <input type="file" id="disputeEvidenceFiles" name="evidence_files[]" class="file-input" multiple
                            accept="image/*,.pdf,.doc,.docx">
                        <div class="upload-placeholder">
                            <i class="fi fi-rr-cloud-upload"></i>
                            <span>Click or drag files to upload</span>
                        </div>
                    </div>

                    <div id="disputeFileList" class="file-list">
                        <!-- Selected files will be listed here -->
                    </div>
                    <div class="error-message" id="error-evidence_files"></div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="sendReportCancelBtn">Cancel</button>
                    <button type="submit" class="btn-submit" id="submitDisputeBtn">
                        <i class="fi fi-rr-paper-plane"></i>
                        <span>Submit Dispute</span>
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
        <div class="success-icon">
            <span class="success-check">✓</span>
        </div>
        <h3 class="success-title">Dispute Submitted</h3>
        <p class="success-message">Your report has been received and will be reviewed. We'll notify the project owner.
        </p>
        <div class="success-actions">
            <button type="button" class="btn-success-primary" id="sendReportSuccessDone">Done</button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="sendReportErrorModal" class="send-report-error-modal hidden">
    <div class="modal-overlay"></div>
    <div class="error-modal-content">
        <div class="error-icon">
            <span class="error-mark">!</span>
        </div>
        <h3 class="error-title">Submission Failed</h3>
        <p class="error-message" id="sendReportErrorMessage">An error occurred while submitting your dispute.</p>
        <div class="error-actions">
            <button type="button" class="btn-error-primary" id="sendReportErrorDone">Understood</button>
        </div>
    </div>
</div>