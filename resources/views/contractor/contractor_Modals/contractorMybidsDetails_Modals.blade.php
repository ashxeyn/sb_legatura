<!-- Bid Details Modal -->
<div id="bidDetailsModal" class="bid-details-modal">
    <div class="modal-overlay" id="bidModalOverlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="modal-header-content">
                <h2 class="modal-title">
                    <i class="fi fi-rr-file-invoice"></i>
                    <span>Bid Details</span>
                </h2>
                <button class="modal-close-btn" id="closeBidModalBtn" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Bid Status Banner -->
            <div class="bid-status-banner" id="bidStatusBanner">
                <div class="status-banner-content">
                    <div class="status-banner-icon">
                        <i class="fi fi-rr-circle-small"></i>
                    </div>
                    <div class="status-banner-text">
                        <span class="status-banner-label">Bid Status:</span>
                        <span class="status-banner-value" id="modalBidStatus">Pending</span>
                    </div>
                </div>
                <div class="status-banner-date">
                    <i class="fi fi-rr-calendar"></i>
                    <span id="modalBidSubmittedDate">Submitted: January 15, 2024</span>
                </div>
            </div>

            <!-- PROJECT INFORMATION Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-info-circle"></i>
                    <h4 class="section-title">PROJECT INFORMATION</h4>
                </div>
                <div class="section-content">
                    <!-- Project Title and Image -->
                    <div class="project-info-header">
                        <div class="project-info-image">
                            <img id="modalBidProjectImage" src="" alt="Project image" class="bid-project-image">
                        </div>
                        <div class="project-info-details">
                            <h3 class="project-info-title" id="modalBidProjectTitle"></h3>
                            <div class="project-info-meta">
                                <span class="project-type-tag" id="modalBidProjectType"></span>
                            </div>
                            <div class="project-info-location" id="modalBidProjectLocation">
                                <i class="fi fi-rr-marker"></i>
                                <span id="modalBidLocationText"></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property Owner -->
                    <div class="owner-info-section">
                        <div class="owner-info-label">Property Owner</div>
                        <div class="owner-info-content">
                            <div class="owner-info-avatar" id="modalBidOwnerAvatar"></div>
                            <div class="owner-info-text">
                                <div class="owner-info-name" id="modalBidOwnerName"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Project Description -->
                    <div class="project-description-section">
                        <div class="description-label">Description</div>
                        <p class="project-description-text" id="modalBidProjectDescription"></p>
                    </div>
                </div>
            </div>

            <!-- PROJECT SPECIFICATIONS Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-settings"></i>
                    <h4 class="section-title">PROJECT SPECIFICATIONS</h4>
                </div>
                <div class="section-content">
                    <div class="specifications-grid" id="modalBidSpecifications">
                        <!-- Specifications will be populated dynamically -->
                    </div>
                    
                    <!-- Budget and Timeline -->
                    <div class="budget-timeline-section">
                        <div class="budget-timeline-item">
                            <div class="budget-timeline-icon">
                                <i class="fi fi-rr-money"></i>
                            </div>
                            <div class="budget-timeline-content">
                                <span class="budget-timeline-label">Project Budget</span>
                                <span class="budget-timeline-value" id="modalBidProjectBudget"></span>
                            </div>
                        </div>
                        <div class="budget-timeline-item">
                            <div class="budget-timeline-icon">
                                <i class="fi fi-rr-calendar-clock"></i>
                            </div>
                            <div class="budget-timeline-content">
                                <span class="budget-timeline-label">Expected Timeline</span>
                                <span class="budget-timeline-value" id="modalBidTimeline"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROJECT DOCUMENTS Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-document"></i>
                    <h4 class="section-title">PROJECT DOCUMENTS</h4>
                </div>
                <div class="section-content">
                    <div class="documents-list" id="modalBidDocuments">
                        <!-- Documents will be populated dynamically -->
                    </div>
                    <div class="no-documents-message" id="noDocumentsMessage" style="display: none;">
                        <i class="fi fi-rr-inbox"></i>
                        <p>No project documents available</p>
                    </div>
                </div>
            </div>

            <!-- YOUR BID Section -->
            <div class="details-section bid-highlight-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-badge-check"></i>
                    <h4 class="section-title">YOUR BID</h4>
                </div>
                <div class="section-content">
                    <!-- Bid Amount Display -->
                    <div class="bid-amount-display">
                        <div class="bid-amount-label">Your Bid Amount</div>
                        <div class="bid-amount-value" id="modalYourBidAmount"></div>
                        <div class="bid-amount-comparison" id="modalBidComparison"></div>
                    </div>

                    <!-- Bid Details Grid -->
                    <div class="bid-details-grid">
                        <div class="bid-detail-item">
                            <div class="bid-detail-icon">
                                <i class="fi fi-rr-calendar"></i>
                            </div>
                            <div class="bid-detail-content">
                                <span class="bid-detail-label">Submitted On</span>
                                <span class="bid-detail-value" id="modalBidDateFull"></span>
                            </div>
                        </div>
                        <div class="bid-detail-item">
                            <div class="bid-detail-icon">
                                <i class="fi fi-rr-clock"></i>
                            </div>
                            <div class="bid-detail-content">
                                <span class="bid-detail-label">Response Time</span>
                                <span class="bid-detail-value" id="modalBidResponseTime">Within 24 hours</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bid Notes/Message -->
                    <div class="bid-notes-section">
                        <div class="bid-notes-label">Your Proposal Message</div>
                        <p class="bid-notes-text" id="modalBidNotes"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button class="modal-btn secondary-btn" id="closeBidModalFooterBtn">
                <i class="fi fi-rr-cross"></i>
                Close
            </button>
            <button class="modal-btn withdraw-bid-btn hidden" id="withdrawBidBtn">
                <i class="fi fi-rr-cross-circle"></i>
                Withdraw Bid
            </button>
        </div>
    </div>
</div>

<!-- Withdraw Bid Confirmation Modal -->
<div id="withdrawConfirmationModal" class="withdraw-confirmation-modal">
    <div class="confirmation-overlay" id="withdrawConfirmationOverlay"></div>
    <div class="confirmation-container">
        <!-- Confirmation Icon -->
        <div class="confirmation-icon-wrapper">
            <div class="confirmation-icon warning">
                <i class="fi fi-rr-triangle-warning"></i>
            </div>
        </div>

        <!-- Confirmation Content -->
        <div class="confirmation-content">
            <h3 class="confirmation-title">Withdraw Bid?</h3>
            <p class="confirmation-message" id="withdrawConfirmationMessage">
                Are you sure you want to withdraw your bid? This action cannot be undone and the property owner will be notified.
            </p>
            <div class="confirmation-bid-info" id="withdrawBidInfo">
                <div class="confirmation-bid-detail">
                    <span class="confirmation-bid-label">Project:</span>
                    <span class="confirmation-bid-value" id="confirmWithdrawProjectTitle"></span>
                </div>
                <div class="confirmation-bid-detail">
                    <span class="confirmation-bid-label">Your Bid:</span>
                    <span class="confirmation-bid-value" id="confirmWithdrawBidAmount"></span>
                </div>
            </div>
        </div>

        <!-- Confirmation Actions -->
        <div class="confirmation-actions">
            <button class="confirmation-btn cancel-btn" id="cancelWithdrawBtn">
                <i class="fi fi-rr-cross"></i>
                Cancel
            </button>
            <button class="confirmation-btn confirm-btn danger-btn" id="confirmWithdrawBtn">
                <i class="fi fi-rr-check"></i>
                Yes, Withdraw Bid
            </button>
        </div>
    </div>
</div>
