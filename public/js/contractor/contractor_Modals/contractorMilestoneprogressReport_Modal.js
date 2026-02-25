/**
 * View Progress Report Modal JavaScript
 * Populates the modal with PHP-precomputed data from window.milestoneItemsData
 * Handles modal open/close and UI interactions only
 */

class ContractorViewProgressReportModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.currentItemId = null;

        this.init();
    }

    init() {
        this.modal = document.getElementById('viewProgressReportModal');
        this.overlay = document.getElementById('viewProgressReportModalOverlay');

        if (!this.modal || !this.overlay) {
            console.error('View Progress Report Modal elements not found');
            return;
        }

        this.setupReportMenuHandlers();
        this.setupEventListeners();
    }

    setupEventListeners() {
        const closeBtn = document.getElementById('closeViewProgressReportModalBtn');
        const closeViewBtn = document.getElementById('closeViewReportBtn');

        if (closeBtn) closeBtn.addEventListener('click', () => this.close());
        if (closeViewBtn) closeViewBtn.addEventListener('click', () => this.close());
        if (this.overlay) this.overlay.addEventListener('click', () => this.close());

        // Submit Progress Report button
        const submitBtn = document.getElementById('submitProgressReportBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', () => this.handleSubmitProgressReport());
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) this.close();
        });

        // Approve Button
        const approveBtn = document.getElementById('approveProgressBtn');
        if (approveBtn) {
            approveBtn.addEventListener('click', () => this.handleApprove());
        }

        // Reject Button
        const rejectBtn = document.getElementById('rejectProgressBtn');
        if (rejectBtn) {
            rejectBtn.addEventListener('click', () => this.handleReject());
        }
    }

    handleApprove() {
        const itemsData = window.milestoneItemsData || {};
        const item = itemsData[this.currentItemId];
        if (!item) return;

        // Open payment modal if this is the owner view
        if (window.contractorProgressreportModalInstance) {
            window.contractorProgressreportModalInstance.openPayment({
                project_id: window.currentProjectId || 0,
                milestoneItemId: item.id,
                milestoneTitle: `Milestone ${item.sequenceNumber}: ${item.title}`
            });
        }
    }

    handleReject() {
        const itemsData = window.milestoneItemsData || {};
        const item = itemsData[this.currentItemId];
        if (!item) return;

        // Find the latest progress report to reject
        const latestReport = item.progressReports && item.progressReports.length > 0
            ? item.progressReports[0]
            : null;

        if (latestReport && window.contractorProgressreportModalInstance) {
            window.contractorProgressreportModalInstance.openRejection({
                progress_id: latestReport.id,
                milestoneTitle: `Milestone ${item.sequenceNumber}: ${item.title}`
            });
        } else {
            alert('No submitted report found to reject.');
        }
    }

    async handleSubmitProgressReport() {
        const itemsData = window.milestoneItemsData || {};
        const item = itemsData[this.currentItemId];

        if (!item) {
            console.error('No item data for submit');
            return;
        }

        // If the progress report upload modal is available on this page, open it directly
        if (window.openProgressReportModal) {
            window.openProgressReportModal({
                projectTitle: window.projectTitle || 'Project',
                milestoneTitle: `Milestone ${item.sequenceNumber}: ${item.title}`,
                milestoneItemId: item.id
            });
            return;
        }

        // Fallback: Navigate to the dedicated progress report page via PRG session pattern
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            // Assume projectId is available globally or we can get it from the URL as fallback
            const urlParams = new URLSearchParams(window.location.search);
            const projectId = window.currentProjectId || urlParams.get('project_id') || 0;

            const response = await fetch('/contractor/projects/set-milestone-item', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({
                    item_id: item.id,
                    project_id: projectId
                })
            });

            const data = await response.json();

            if (data.success) {
                // Close the view modal
                this.close();
                window.location.href = '/contractor/projects/milestone-progress-report';
            } else {
                console.error('Failed to set milestone item session');
                alert('Could not navigate to progress report.');
            }
        } catch (error) {
            console.error('Error setting milestone item session:', error);
            alert('An error occurred. Please try again.');
        }
    }

    /**
     * Open the modal for a given milestone item ID.
     * Reads all data from window.milestoneItemsData (PHP-precomputed).
     */
    open(itemId) {
        const itemsData = window.milestoneItemsData || {};
        const item = itemsData[itemId];

        if (!item) {
            console.error('Milestone item data not found for ID:', itemId);
            return;
        }

        this.currentItemId = itemId;
        this.populateModal(item);

        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = '';
        }
        this.currentItemId = null;
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }

    populateModal(item) {
        const projectTitle = window.projectTitle || 'Project';

        // Header title
        this.setText('viewModalReportTitle', `Milestone ${item.sequenceNumber}: ${item.title}`);

        // Info section
        this.setText('viewModalMilestoneTitle', item.title);
        this.setText('viewModalProjectTitle', projectTitle);
        this.setText('viewModalTargetDate', item.date || 'Not specified');
        this.setText('viewModalCost', item.costFormatted || '-');

        // Status badge
        const statusEl = document.getElementById('viewModalStatus');
        if (statusEl) {
            statusEl.className = 'status-badge-view';
            const status = item.status || 'pending';
            statusEl.classList.add(`status-${status}`);
            const statusLabels = {
                'approved': 'Approved', 'pending': 'Pending', 'submitted': 'Submitted',
                'rejected': 'Rejected', 'completed': 'Completed', 'halt': 'Halted'
            };
            statusEl.textContent = statusLabels[status] || status;
        }

        // --- Alert Banners (matching TSX StatusAlerts logic) ---
        this.populateAlerts(item);

        // Description
        this.setText('viewModalDescription', item.description || 'No description provided for this milestone.');

        // Button visibility logic
        const submitBtn = document.getElementById('submitProgressReportBtn');
        const approveBtn = document.getElementById('approveProgressBtn');
        const rejectBtn = document.getElementById('rejectProgressBtn');

        // Determine if current user is owner (can check global variable or context)
        const isOwner = window.isOwnerMode || false;

        if (isOwner) {
            // Owner view: Show Approve/Reject if there's a submitted report and it's not already approved
            const canAction = item.status === 'submitted';
            if (approveBtn) approveBtn.style.display = canAction ? 'flex' : 'none';
            if (rejectBtn) rejectBtn.style.display = canAction ? 'flex' : 'none';
            if (submitBtn) submitBtn.style.display = 'none';
        } else {
            // Contractor view: Show Submit if conditions met
            if (submitBtn) submitBtn.style.display = item.canSubmitReport ? 'flex' : 'none';
            if (approveBtn) approveBtn.style.display = 'none';
            if (rejectBtn) rejectBtn.style.display = 'none';
        }

        // Progress Reports
        this.populateProgressReports(item.progressReports || [], item.canSubmitReport || false, item.id);

        // Payments
        this.populatePayments(item.payments || [], item.paymentSummary || {});
    }

    populateAlerts(item) {
        // Hide all alerts first
        const alertIds = [
            'alertLockedBanner', 'alertHaltedBanner', 'alertRejectedReportBanner',
            'alertRejectedPaymentBanner', 'alertPendingBanner', 'alertCompletedBanner'
        ];
        alertIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });

        // Show relevant alerts based on item conditions
        if (item.isCompleted) {
            this.showAlert('alertCompletedBanner');
            return; // No other alerts needed for completed items
        }

        if (item.isLocked) {
            this.showAlert('alertLockedBanner');
            return; // No other alerts for locked items
        }

        if (item.isHalted || item.isProjectHalted) {
            this.showAlert('alertHaltedBanner');
        }

        if (item.latestReportStatus === 'rejected') {
            this.showAlert('alertRejectedReportBanner');
        }

        if (item.latestPaymentStatus === 'rejected') {
            this.showAlert('alertRejectedPaymentBanner');
        }

        // Pending review (has submitted reports or payments awaiting approval)
        if (item.latestReportStatus === 'submitted' || item.latestPaymentStatus === 'submitted') {
            this.showAlert('alertPendingBanner');
        }
    }

    showAlert(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'flex';
    }



    populateProgressReports(reports, canSubmitReport = false, itemId) {
        const section = document.getElementById('progressReportsSection');
        const container = document.getElementById('viewModalReportsTimeline');

        if (!container || !section) return;

        container.innerHTML = '';

        const hasReports = reports && reports.length > 0;

        if (section) {
            section.style.display = (hasReports || canSubmitReport) ? 'block' : 'none';
        }

        if (!hasReports) return;

        if (itemId) {
            const templateDiv = document.getElementById('timeline_html_' + itemId);
            if (templateDiv) {
                container.innerHTML = templateDiv.innerHTML;
            }
        }

        const editButtons = container.querySelectorAll('.edit-progress-btn');
        editButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const progressId = btn.getAttribute('data-progress-id');
                const reportData = reports.find(r => r.id == progressId);
                if (reportData && window.contractorProgressreportModalInstance) {
                    const t = window.contractorViewProgressReportModalInstance; if (t) t.close();
                    const editData = {
                        milestoneItemId: itemId,
                        project_id: window.currentProjectId || '',
                        title: window.projectTitle || ''
                    };
                    window.contractorProgressreportModalInstance.openEdit(editData, reportData);
                }
            });
        });
    }

    setupReportMenuHandlers() {
        window.toggleReportMenu = (event, btn) => {
            event.preventDefault();
            event.stopPropagation();

            // Close all other dropdowns
            document.querySelectorAll('.report-dropdown.active').forEach(menu => {
                if (menu !== btn.nextElementSibling) {
                    menu.classList.remove('active');
                }
            });

            // Toggle this dropdown
            const dropdown = btn.nextElementSibling;
            if (dropdown) {
                dropdown.classList.toggle('active');
            }
        };

        window.handleSendReport = (event) => {
            event.preventDefault();
            event.stopPropagation();
            // Close the current dropdown
            const dropdown = event.target.closest('.report-dropdown');
            if (dropdown) dropdown.classList.remove('active');

            // Show dispute prompt matching TSX
            if (window.confirm('File a Dispute\n\nWould you like to file a dispute or report an issue?')) {
                window.alert('Info: Please go to milestone detail to file a specific dispute');
            }
        };

        window.handleReportHistory = (event) => {
            event.preventDefault();
            event.stopPropagation();
            const dropdown = event.target.closest('.report-dropdown');
            if (dropdown) dropdown.classList.remove('active');

            // For now, just show an alert as report history modal is not implemented yet
            window.alert('Report history feature coming soon');
        };

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.report-menu-container')) {
                document.querySelectorAll('.report-dropdown.active').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });
    }

    populatePayments(payments, summary) {
        const section = document.getElementById('paymentsSection');
        const divider = document.getElementById('paymentsDivider');
        const container = document.getElementById('viewModalPaymentCards');
        const summaryEl = document.getElementById('paymentBalanceSummary');

        if (!container) return;
        container.innerHTML = '';

        if (!payments || payments.length === 0) {
            if (section) section.style.display = 'none';
            if (divider) divider.style.display = 'none';
            return;
        }

        if (section) section.style.display = 'block';
        if (divider) divider.style.display = 'block';

        // Populate balance summary
        if (summaryEl && summary.expected > 0) {
            summaryEl.style.display = 'block';
            this.setText('balanceExpected', summary.expectedFormatted || '-');
            this.setText('balancePaid', summary.totalPaidFormatted || '-');
            this.setText('balanceRemaining', summary.remainingFormatted || '-');

            const pendingRow = document.getElementById('balancePendingRow');
            if (summary.totalSubmitted > 0) {
                if (pendingRow) pendingRow.style.display = 'flex';
                this.setText('balancePending', summary.totalSubmittedFormatted || '-');
            } else {
                if (pendingRow) pendingRow.style.display = 'none';
            }

            const progressFill = document.getElementById('balanceProgressFill');
            if (progressFill) {
                progressFill.style.width = `${Math.min(100, summary.progressPercent || 0)}%`;
            }

            // Color the remaining value
            const remainingEl = document.getElementById('balanceRemaining');
            if (remainingEl) {
                remainingEl.className = 'balance-value balance-value-bold';
                if (summary.remaining <= 0) remainingEl.classList.add('balance-success');
                else remainingEl.classList.add('balance-accent');
            }
        } else if (summaryEl) {
            summaryEl.style.display = 'none';
        }

        // Populate payment cards
        payments.forEach(payment => {
            const status = payment.status || 'submitted';
            const statusLabels = {
                'approved': 'Approved', 'rejected': 'Rejected', 'submitted': 'Submitted'
            };
            const statusColors = {
                'approved': 'badge-success', 'rejected': 'badge-error', 'submitted': 'badge-warning'
            };

            let cardHTML = `
                <div class="payment-card">
                    <div class="payment-card-header">
                        <div class="payment-card-title-row">
                            <i class="fi fi-rr-credit-card"></i>
                            <span class="payment-card-title">Payment Receipt</span>
                        </div>
                        <span class="payment-status-badge ${statusColors[status] || 'badge-pending'}">
                            ${statusLabels[status] || status}
                        </span>
                    </div>
                    <div class="payment-card-amount">
                        <span class="payment-amount-label">Amount</span>
                        <span class="payment-amount-value">${payment.amountFormatted || '₱0.00'}</span>
                    </div>
                    <div class="payment-card-details">
                        <div class="payment-detail-row">
                            <span class="payment-detail-label">Date:</span>
                            <span class="payment-detail-value">${payment.date || '-'}</span>
                        </div>
                        <div class="payment-detail-row">
                            <span class="payment-detail-label">Method:</span>
                            <span class="payment-detail-value">${payment.type || '-'}</span>
                        </div>
                        ${payment.transactionNumber ? `
                        <div class="payment-detail-row">
                            <span class="payment-detail-label">Reference #:</span>
                            <span class="payment-detail-value">${payment.transactionNumber}</span>
                        </div>` : ''}
                    </div>
            `;

            // Rejection reason
            if (status === 'rejected' && payment.reason) {
                cardHTML += `
                    <div class="payment-rejection-reason" style="margin-top: 1rem; padding: 0.75rem; background: #fef2f2; border-radius: 0.5rem; border: 1px solid #fee2e2;">
                        <div class="rejection-header" style="display: flex; align-items: center; gap: 0.5rem; color: #991b1b; font-weight: 600; font-size: 0.85rem; margin-bottom: 0.25rem;">
                            <i class="fi fi-rr-info"></i>
                            <span>Decline Reason:</span>
                        </div>
                        <p class="rejection-text" style="color: #b91c1c; font-size: 0.85rem; margin: 0;">${payment.reason}</p>
                    </div>
                `;
            }

            // Receipt photo
            if (payment.receiptPhoto) {
                cardHTML += `
                    <div class="payment-receipt-photo" style="margin-top: 1rem;">
                        <span class="receipt-label" style="display: block; font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem;">Receipt Photo:</span>
                        <img src="/storage/${payment.receiptPhoto}" alt="Receipt" class="receipt-image" style="width: 100%; border-radius: 0.5rem; border: 1px solid #e2e8f0; cursor: pointer;" onclick="window.open(this.src)" onerror="this.style.display='none'">
                    </div>
                `;
            }

            // Action Buttons for CONTRACTOR (when payment is submitted)
            if (status === 'submitted') {
                cardHTML += `
                    <div class="payment-card-actions" style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                        <button class="modal-btn reject-payment-btn" data-payment-id="${payment.id}" style="flex: 1; padding: 0.5rem; font-size: 0.85rem; background: #ef4444; color: white; border: none; border-radius: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.375rem;">
                            <i class="fi fi-rr-cross"></i> Decline
                        </button>
                        <button class="modal-btn approve-payment-btn" data-payment-id="${payment.id}" style="flex: 1; padding: 0.5rem; font-size: 0.85rem; background: #10b981; color: white; border: none; border-radius: 0.375rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.375rem;">
                            <i class="fi fi-rr-check"></i> Approve
                        </button>
                    </div>
                `;
            }

            cardHTML += `</div>`;

            const cardEl = document.createElement('div');
            cardEl.innerHTML = cardHTML;
            const finalCard = cardEl.firstElementChild;
            container.appendChild(finalCard);

            // Attach listeners to buttons (now via window global handlers)
            const approveBtn = finalCard.querySelector('.approve-payment-btn');
            const rejectBtn = finalCard.querySelector('.reject-payment-btn');

            if (approveBtn) {
                approveBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (window.openPaymentApproveModal) {
                        window.openPaymentApproveModal(payment.id);
                    }
                });
            }

            if (rejectBtn) {
                rejectBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (window.openPaymentRejectModal) {
                        window.openPaymentRejectModal(payment.id);
                    }
                });
            }
        });
    }

    setText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    getFileIconClass(ext) {
        const map = {
            'pdf': 'fi fi-rr-file-pdf', 'doc': 'fi fi-rr-file-word', 'docx': 'fi fi-rr-file-word',
            'jpg': 'fi fi-rr-file-image', 'jpeg': 'fi fi-rr-file-image', 'png': 'fi fi-rr-file-image',
            'gif': 'fi fi-rr-file-image', 'webp': 'fi fi-rr-file-image'
        };
        return map[ext] || 'fi fi-rr-file';
    }
}

// Initialize modal when DOM is ready
window.contractorViewProgressReportModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    window.contractorViewProgressReportModalInstance = new ContractorViewProgressReportModal();
});

// Export for use in other scripts
window.ContractorViewProgressReportModal = ContractorViewProgressReportModal;

/**
 * Opens the modal for a given milestone item ID.
 * All data is read from window.milestoneItemsData (PHP-precomputed).
 */
window.openViewProgressReportModal = (itemId) => {
    if (window.contractorViewProgressReportModalInstance) {
        window.contractorViewProgressReportModalInstance.open(itemId);
    } else {
        setTimeout(() => {
            if (window.contractorViewProgressReportModalInstance) {
                window.contractorViewProgressReportModalInstance.open(itemId);
            }
        }, 100);
    }
};
