<!-- Apply Bid Modal -->
<div id="applyBidModal" class="apply-bid-modal">
    <div class="modal-overlay" id="applyBidModalOverlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <button class="modal-back-btn" id="applyBidBackBtn" aria-label="Go back">
                <i class="fi fi-rr-arrow-left"></i>
            </button>
            <h2 class="modal-title">Apply for Bid</h2>
            <button class="modal-close-btn" id="closeApplyBidModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Project Info -->
        <div class="modal-project-info">
            <span class="project-info-label">Project:</span>
            <span class="project-type-badge" id="modalProjectType">Residential</span>
            <span class="project-name" id="modalProjectName">House Construction</span>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <form id="applyBidForm" method="POST" action="/contractor/bids" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="project_id" id="modalProjectId" value="">

                <!-- Success/Error Messages -->
                <div id="applyBidFormSuccess" class="alert alert-success hidden"></div>
                <div id="applyBidFormError" class="alert alert-error hidden"></div>

                <!-- Proposed Cost -->
                <div class="form-group">
                    <input type="text" name="proposed_cost" id="modalProposedCost" class="form-input" placeholder="Proposed cost (PHP)" required>
                    <div class="error-message hidden" id="error_proposed_cost"></div>
                </div>

                <!-- Estimated Timeline -->
                <div class="form-group">
                    <label class="form-label">
                        <span>Estimated timeline</span>
                    </label>
                    <input type="text" name="estimated_timeline" id="modalEstimatedTimeline" class="form-input" placeholder="In Months" required>
                    <div class="error-message hidden" id="error_estimated_timeline"></div>
                </div>

                <!-- Compelling Message -->
                <div class="form-group">
                    <textarea name="compelling_message" id="modalCompellingMessage" class="form-textarea" placeholder="Write a compelling message to the client. Tell them about your expertise and why you're a great fit." required maxlength="1000"></textarea>
                    <div class="textarea-footer">
                        <div class="error-message hidden" id="error_compelling_message"></div>
                        <div class="character-count">
                            <span id="messageCharCount">0</span>/1000
                        </div>
                    </div>
                </div>

                <!-- Upload Supporting Documents -->
                <div class="form-group">
                    <label class="form-label">
                        <span>Upload Supporting Documents</span>
                    </label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <input type="file" id="modalSupportingDocuments" name="supporting_documents[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple class="file-input-hidden">
                        <div class="file-upload-content">
                            <i class="fi fi-rr-cloud-upload file-upload-icon"></i>
                            <span class="file-upload-text">Upload image or file</span>
                        </div>
                    </div>
                    <div class="file-preview-container" id="filePreviewContainer">
                        <!-- File previews will be added here -->
                    </div>
                    <small class="form-hint form-hint-disclaimer">
                        Your uploaded land title is required for verification. This document is solely for project purposes, will remain confidential, and will only be visible to the admin, not contractors.
                    </small>
                    <small class="form-hint form-hint-example">
                        e.g., document, photos, certificates, permits, etc.
                    </small>
                    <div class="error-message hidden" id="error_supporting_documents"></div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" id="cancelApplyBidBtn">Cancel</button>
                    <button type="submit" class="btn btn-submit" id="submitApplyBidBtn">
                        <span>Submit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
