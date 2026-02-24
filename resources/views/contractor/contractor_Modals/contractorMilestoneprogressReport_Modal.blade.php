{{-- View Progress Report Modal - Milestone Item Details --}}
{{-- Data is populated via JS from window.milestoneItemsData (PHP-precomputed) --}}
<div id="viewProgressReportModal" class="view-progress-report-modal">
    <div class="modal-overlay" id="viewProgressReportModalOverlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="modal-header-content">
                <h2 class="modal-title" id="viewModalReportTitle">{{ $project->project_title ?? 'Milestone Details' }}
                </h2>
                <div class="report-menu-container" style="margin-left: auto;">
                    <button class="report-menu-btn" onclick="toggleReportMenu(event, this)" style="font-size: 1.25rem;">
                        <i class="fi fi-rr-menu-dots-vertical"></i>
                    </button>
                    <div class="report-dropdown">
                        <button class="report-dropdown-item" onclick="handleSendReport(event)">
                            <i class="fi fi-rr-file-edit"></i> Send Report
                        </button>
                        <button class="report-dropdown-item" onclick="handleReportHistory(event)">
                            <i class="fi fi-rr-clock-three"></i> Report History
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Report Info Section -->
            <div class="report-info-section">
                <div class="info-row">
                    <span class="info-label">Milestone:</span>
                    <span class="info-value" id="viewModalMilestoneTitle">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Project:</span>
                    <span class="info-value" id="viewModalProjectTitle">{{ $project->project_title ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge-view" id="viewModalStatus">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Target Date:</span>
                    <span class="info-value" id="viewModalTargetDate">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cost:</span>
                    <span class="info-value" id="viewModalCost">-</span>
                </div>
            </div>

            <!-- Status Alert Banners (populated by JS based on item conditions) -->
            <div id="alertLockedBanner" class="modal-alert alert-locked" style="display: none;">
                <i class="fi fi-rr-lock"></i>
                <span>Complete the previous milestone item first before working on this one.</span>
            </div>
            <div id="alertHaltedBanner" class="modal-alert alert-halted" style="display: none;">
                <i class="fi fi-rr-pause-circle"></i>
                <span>This milestone item has been halted.</span>
            </div>
            <div id="alertRejectedReportBanner" class="modal-alert alert-rejected" style="display: none;">
                <i class="fi fi-rr-cross-circle"></i>
                <span>Your latest progress report was rejected. Please review and resubmit.</span>
            </div>
            <div id="alertRejectedPaymentBanner" class="modal-alert alert-rejected" style="display: none;">
                <i class="fi fi-rr-cross-circle"></i>
                <span>Your latest payment was declined. Please check the reason below.</span>
            </div>
            <div id="alertPendingBanner" class="modal-alert alert-pending" style="display: none;">
                <i class="fi fi-rr-info"></i>
                <span>You have a report or payment pending review.</span>
            </div>
            <div id="alertCompletedBanner" class="modal-alert alert-completed" style="display: none;">
                <i class="fi fi-rr-check-circle"></i>
                <span>This milestone item has been completed.</span>
            </div>
            <div class="report-description-section">
                <h4 class="section-subtitle">
                    <i class="fi fi-rr-document"></i>
                    Description
                </h4>
                <p class="report-description-text" id="viewModalDescription">
                    No description available.
                </p>
            </div>



            <!-- Divider -->
            <div class="modal-divider"></div>

            <!-- Progress Reports Timeline Section -->
            <div class="progress-reports-section" id="progressReportsSection" style="display: none;">
                <h4 class="section-subtitle">
                    <i class="fi fi-rr-time-past"></i>
                    Progress Reports
                </h4>
                <div class="reports-timeline" id="viewModalReportsTimeline">
                    <!-- Populated by JS from PHP-precomputed data -->
                </div>
                <button class="modal-btn submit-progress-btn" id="submitProgressReportBtn"
                    style="display: none; width: 100%; margin-top: 1rem; justify-content: center;">
                    <i class="fi fi-rr-cloud-upload"></i>
                    Submit Progress Report
                </button>
            </div>

            <!-- Divider -->
            <div class="modal-divider" id="paymentsDivider" style="display: none;"></div>

            <!-- Payments Section -->
            <div class="payments-section" id="paymentsSection" style="display: none;">
                <h4 class="section-subtitle">
                    <i class="fi fi-rr-credit-card"></i>
                    Payment Receipts
                </h4>

                <!-- Payment Balance Summary -->
                <div class="payment-balance-summary" id="paymentBalanceSummary">
                    <div class="balance-row">
                        <span class="balance-label">Expected</span>
                        <span class="balance-value" id="balanceExpected">-</span>
                    </div>
                    <div class="balance-row">
                        <span class="balance-label">Paid (Approved)</span>
                        <span class="balance-value balance-success" id="balancePaid">-</span>
                    </div>
                    <div class="balance-row" id="balancePendingRow" style="display: none;">
                        <span class="balance-label">Pending</span>
                        <span class="balance-value balance-warning" id="balancePending">-</span>
                    </div>
                    <div class="balance-row balance-total-row">
                        <span class="balance-label balance-label-bold">Remaining</span>
                        <span class="balance-value balance-value-bold" id="balanceRemaining">-</span>
                    </div>
                    <!-- Progress Bar -->
                    <div class="balance-progress-bg">
                        <div class="balance-progress-fill" id="balanceProgressFill" style="width: 0%;"></div>
                    </div>
                </div>

                <!-- Payment Cards -->
                <div class="payment-cards-list" id="viewModalPaymentCards">
                    <!-- Populated by JS from PHP-precomputed data -->
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button class="modal-btn approve-btn" id="approveProgressBtn"
                style="display: none; background: linear-gradient(135deg, #10b981, #059669); color: white;">
                <i class="fi fi-rr-check"></i>
                Approve
            </button>
            <button class="modal-btn reject-btn" id="rejectProgressBtn"
                style="display: none; background: linear-gradient(135deg, #ef4444, #dc2626); color: white;">
                <i class="fi fi-rr-cross"></i>
                Reject
            </button>
            <button class="modal-btn close-btn" id="closeViewReportBtn">
                <i class="fi fi-rr-cross"></i>
                Close
            </button>
        </div>
    </div>
</div>