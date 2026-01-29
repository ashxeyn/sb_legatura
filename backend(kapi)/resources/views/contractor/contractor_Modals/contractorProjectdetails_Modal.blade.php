<!-- Project Details Modal -->
<div id="projectDetailsModal" class="project-details-modal">
    <div class="modal-overlay" id="projectModalOverlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="modal-header-content">
                <h2 class="modal-title">
                    <i class="fi fi-rr-file-document"></i>
                    <span>Project Details</span>
                </h2>
                <button class="modal-close-btn" id="closeProjectModalBtn" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Project Title and Location -->
            <div class="project-header-section">
                <h3 class="project-title-modal" id="modalProjectTitle"></h3>
                <div class="project-location-modal" id="modalProjectLocation">
                    <i class="fi fi-rr-marker"></i>
                    <span id="modalLocationText"></span>
                </div>
            </div>

            <!-- Project Image -->
            <div class="project-image-section">
                <img id="modalProjectImage" src="" alt="Project image" class="project-image-modal">
            </div>

            <!-- DESCRIPTION Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-document"></i>
                    <h4 class="section-title">DESCRIPTION</h4>
                </div>
                <div class="section-content">
                    <p class="project-description-modal" id="modalProjectDescription"></p>
                </div>
            </div>

            <!-- SPECIFICATIONS Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-settings"></i>
                    <h4 class="section-title">SPECIFICATIONS</h4>
                </div>
                <div class="section-content">
                    <div class="specifications-grid" id="modalSpecifications">
                        <!-- Specifications will be populated dynamically -->
                    </div>
                </div>
            </div>

            <!-- Lot Size and Floor Area Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-ruler-combined"></i>
                    <h4 class="section-title">LOT SIZE & FLOOR AREA</h4>
                </div>
                <div class="section-content">
                    <div class="measurements-grid">
                        <div class="measurement-item">
                            <div class="measurement-icon">
                                <i class="fi fi-rr-square"></i>
                            </div>
                            <div class="measurement-content">
                                <span class="measurement-label">Lot Size</span>
                                <span class="measurement-value" id="modalLotSize"></span>
                            </div>
                        </div>
                        <div class="measurement-item">
                            <div class="measurement-icon">
                                <i class="fi fi-rr-home"></i>
                            </div>
                            <div class="measurement-content">
                                <span class="measurement-label">Floor Area</span>
                                <span class="measurement-value" id="modalFloorArea"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ORIGINAL BUDGET RANGE Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-money"></i>
                    <h4 class="section-title">ORIGINAL BUDGET RANGE</h4>
                </div>
                <div class="section-content">
                    <div class="budget-display">
                        <div class="budget-icon">
                            <i class="fi fi-rr-money"></i>
                        </div>
                        <div class="budget-content">
                            <span class="budget-label">Budget Range</span>
                            <span class="budget-value" id="modalBudgetRange"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROPERTY OWNER Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-user"></i>
                    <h4 class="section-title">PROPERTY OWNER</h4>
                </div>
                <div class="section-content">
                    <div class="contractor-card">
                        <div class="contractor-header">
                            <div class="contractor-avatar-modal" id="modalOwnerAvatar">
                                <!-- Avatar initials -->
                            </div>
                            <div class="contractor-info">
                                <h5 class="contractor-name-modal" id="modalOwnerName"></h5>
                                <p class="contractor-role-modal" id="modalOwnerRole">Property Owner</p>
                            </div>
                        </div>
                        <div class="contractor-details">
                            <div class="detail-row">
                                <i class="fi fi-rr-envelope"></i>
                                <span id="modalOwnerEmail"></span>
                            </div>
                            <div class="detail-row">
                                <i class="fi fi-rr-phone-call"></i>
                                <span id="modalOwnerPhone"></span>
                            </div>
                            <div class="detail-row">
                                <i class="fi fi-rr-calendar"></i>
                                <span>Project Posted: <span id="modalProjectPosted"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Status and Progress -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-chart-line-up"></i>
                    <h4 class="section-title">PROJECT STATUS</h4>
                </div>
                <div class="section-content">
                    <div class="status-progress-container">
                        <div class="status-badge-modal" id="modalStatusBadge">
                            <span class="status-dot-modal"></span>
                            <span class="status-text-modal" id="modalStatusText"></span>
                        </div>
                        <div class="progress-section-modal">
                            <div class="progress-header">
                                <span class="progress-label">Overall Progress</span>
                                <span class="progress-percentage-modal" id="modalProgressPercentage"></span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-modal" id="modalProgressBar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check Milestone Setup Section -->
            <div class="details-section" id="milestoneSection">
                <div class="section-header-modal milestone-header">
                    <div class="milestone-header-left">
                        <i class="fi fi-rr-folder-check"></i>
                        <h4 class="section-title">Check Milestone Setup</h4>
                    </div>
                    <div class="milestone-header-right">
                        <span class="milestone-indicator" id="modalMilestoneCount">0</span>
                        <i class="fi fi-rr-angle-right"></i>
                    </div>
                </div>
                <div class="section-content">
                    <p class="milestone-description">
                        View the complete milestone timeline, payment breakdown, and project duration for this project.
                    </p>
                    <div class="milestone-metrics-grid">
                        <div class="milestone-metric">
                            <span class="metric-label">Total Milestones</span>
                            <span class="metric-value" id="modalTotalMilestones">0</span>
                        </div>
                        <div class="milestone-metric-divider"></div>
                        <div class="milestone-metric">
                            <span class="metric-label">Completed</span>
                            <span class="metric-value" id="modalCompletedMilestones">0</span>
                        </div>
                        <div class="milestone-metric-divider"></div>
                        <div class="milestone-metric">
                            <span class="metric-label">Total Cost</span>
                            <span class="metric-value" id="modalTotalCost">₱0</span>
                        </div>
                    </div>
                    <button class="milestone-review-btn" id="reviewMilestoneBtn">
                        <i class="fi fi-rr-arrow-right"></i>
                        <span>Tap to check milestone setup</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button class="btn-secondary" id="closeModalBtn">
                <i class="fi fi-rr-cross"></i>
                <span>Close</span>
            </button>
        </div>
    </div>
</div>
