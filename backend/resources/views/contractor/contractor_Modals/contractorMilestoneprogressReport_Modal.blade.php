<!-- View Progress Report Modal -->
<div id="viewProgressReportModal" class="view-progress-report-modal">
    <div class="modal-overlay" id="viewProgressReportModalOverlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="modal-header-content">
                <h2 class="modal-title" id="viewModalReportTitle">Progress Report 1.1</h2>
                <button class="modal-close-btn" id="closeViewProgressReportModalBtn" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Report Info Section -->
            <div class="report-info-section">
                <div class="info-row">
                    <span class="info-label">Milestone:</span>
                    <span class="info-value" id="viewModalMilestoneTitle">Structural Framing</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Project:</span>
                    <span class="info-value" id="viewModalProjectTitle">Modern Residential House</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date Submitted:</span>
                    <span class="info-value" id="viewModalSubmittedDate">Tuesday, 28 May 2024</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge-view" id="viewModalStatus">Approved</span>
                </div>
            </div>

            <!-- Report Description -->
            <div class="report-description-section">
                <h4 class="section-subtitle">
                    <i class="fi fi-rr-document"></i>
                    Description
                </h4>
                <p class="report-description-text" id="viewModalDescription">
                    Complete structural framework including foundation, columns, beams, and roof structure.
                </p>
            </div>

            <!-- Attachments Section -->
            <div class="attachments-section">
                <h4 class="section-subtitle">
                    <i class="fi fi-rr-clip"></i>
                    Attachments
                </h4>
                
                <!-- File List -->
                <div class="attachments-file-list" id="viewModalFileList">
                    <!-- Files will be dynamically inserted here -->
                </div>
                
                <!-- No Files Message -->
                <div class="no-files-message" id="noFilesMessage" style="display: none;">
                    <i class="fi fi-rr-inbox"></i>
                    <p>No attachments available</p>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button class="modal-btn close-btn" id="closeViewReportBtn">
                <i class="fi fi-rr-cross"></i>
                Close
            </button>
        </div>
    </div>
</div>
