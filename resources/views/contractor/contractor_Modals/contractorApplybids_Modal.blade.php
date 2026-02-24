@php
    $projectId = $project->project_id ?? $project->id;
    $bidModalId = 'applyBidModal-' . $projectId;
    $projectTitle = $project->project_title ?? 'Untitled Project';
    $projectType = $project->type_name ?? $project->property_type ?? 'General';
    $maxBudget = $project->budget_range_max ?? 0;
@endphp

<!-- Apply Bid Modal for Project {{ $projectId }} -->
<div id="{{ $bidModalId }}" class="apply-bid-modal">
    <div class="modal-overlay" onclick="closeBidModal('{{ $bidModalId }}')"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <button class="modal-back-btn" onclick="closeBidModal('{{ $bidModalId }}')" aria-label="Go back">
                <i class="fi fi-rr-arrow-left"></i>
            </button>
            <h2 class="modal-title">Apply for Bid</h2>
            <button class="modal-close-btn" onclick="closeBidModal('{{ $bidModalId }}')" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Project Info -->
        <div class="modal-project-info">
            <span class="project-info-label">Project:</span>
            <span class="project-type-badge">{{ $projectType }}</span>
            <span class="project-name">{{ $projectTitle }}</span>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <form id="applyBidForm-{{ $projectId }}" method="POST" action="/contractor/bids"
                enctype="multipart/form-data" data-budget-min="{{ $project->budget_range_min ?? 0 }}"
                data-budget-max="{{ $maxBudget }}" novalidate>
                @csrf
                <input type="hidden" name="project_id" value="{{ $projectId }}">

                <!-- Success/Error Messages -->
                <div id="applyBidFormSuccess-{{ $projectId }}" class="alert alert-success hidden"></div>
                <div id="applyBidFormError-{{ $projectId }}" class="alert alert-error hidden"></div>

                <!-- Proposed Cost -->
                <div class="form-group">
                    <label class="form-label">
                        <span>Proposed cost (PHP) <span style="color: red;">*</span></span>
                    </label>
                    <input type="text" name="proposed_cost" id="modalProposedCost-{{ $projectId }}" class="form-input"
                        placeholder="Enter proposed cost" value="" inputmode="numeric" required>
                    <div class="error-message hidden" id="error_proposed_cost-{{ $projectId }}"></div>
                </div>

                <!-- Estimated Timeline -->
                <div class="form-group">
                    <label class="form-label">
                        <span>Estimated timeline (Months) <span style="color: red;">*</span></span>
                    </label>
                    <input type="number" name="estimated_timeline" id="modalEstimatedTimeline-{{ $projectId }}"
                        class="form-input" placeholder="e.g., 3" min="1" step="1" pattern="[0-9]+"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                    <div class="error-message hidden" id="error_estimated_timeline-{{ $projectId }}"></div>
                </div>

                <!-- Compelling Message -->
                <div class="form-group">
                    <label class="form-label">
                        <span>Compelling Message <span style="color: red;">*</span></span>
                    </label>
                    <textarea name="contractor_notes" id="modalCompellingMessage-{{ $projectId }}" class="form-textarea"
                        placeholder="Write a compelling message to the client. Tell them about your expertise and why you're a great fit."
                        required maxlength="1000"></textarea>
                    <div class="textarea-footer">
                        <div class="error-message hidden" id="error_contractor_notes-{{ $projectId }}"></div>
                        <div class="character-count">
                            <span id="messageCharCount-{{ $projectId }}">0</span>/1000
                        </div>
                    </div>
                </div>

                <!-- Upload Supporting Documents -->
                <div class="form-group">
                    <label class="form-label">
                        <span>Upload Supporting Documents</span>
                    </label>
                    <div class="file-upload-area" id="fileUploadArea-{{ $projectId }}">
                        <input type="file" id="modalSupportingDocuments-{{ $projectId }}" name="bid_files[]"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple class="file-input-hidden">
                        <div class="file-upload-content">
                            <i class="fi fi-rr-cloud-upload file-upload-icon"></i>
                            <span class="file-upload-text">Upload image or file</span>
                        </div>
                    </div>
                    <div class="file-preview-container" id="filePreviewContainer-{{ $projectId }}">
                        <!-- File previews will be added here -->
                    </div>
                    <small class="form-hint form-hint-disclaimer">
                        Your uploaded land title is required for verification. This document is solely for project
                        purposes, will remain confidential, and will only be visible to the admin, not contractors.
                    </small>
                    <small class="form-hint form-hint-example">
                        e.g., document, photos, certificates, permits, etc.
                    </small>
                    <div class="error-message hidden" id="error_bid_files-{{ $projectId }}"></div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel"
                        onclick="closeBidModal('{{ $bidModalId }}')">Cancel</button>
                    <button type="submit" class="btn btn-submit" id="submitApplyBidBtn-{{ $projectId }}">
                        <span>Submit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>