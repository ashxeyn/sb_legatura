/**
 * Contractor Milestone Report JavaScript
 * Handles UI interactions only (click handlers, modals)
 * All data is rendered server-side via Blade PHP
 */

class ContractorMilestoneReport {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Milestone item click handlers (items rendered by Blade)
        const milestoneItems = document.querySelectorAll('.milestone-timeline-item');
        milestoneItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();

                // Block clicks on locked items
                if (item.classList.contains('milestone-locked')) {
                    this.showToast('Complete the previous milestone item first.', 'warning');
                    return;
                }

                const milestoneId = parseInt(item.getAttribute('data-milestone-id'));
                this.handleMilestoneClick(milestoneId);
            });
        });

        // Payment history button
        const paymentHistoryBtn = document.getElementById('paymentHistoryBtn');
        if (paymentHistoryBtn) {
            paymentHistoryBtn.addEventListener('click', () => {
                this.handlePaymentHistory();
            });
        }

        // Edit milestone button
        const editMilestoneBtn = document.getElementById('editMilestoneBtn');
        if (editMilestoneBtn) {
            editMilestoneBtn.addEventListener('click', () => {
                this.handleEditMilestone(editMilestoneBtn);
            });
        }
    }

    handleEditMilestone(button) {
        const milestoneId = button.getAttribute('data-milestone-id');
        const planName = button.getAttribute('data-plan-name');
        const paymentMode = button.getAttribute('data-payment-mode');
        const startDate = button.getAttribute('data-start-date');
        const endDate = button.getAttribute('data-end-date');
        const totalBudget = button.getAttribute('data-total-budget');
        const downpayment = button.getAttribute('data-downpayment');

        const milestoneData = {
            id: milestoneId,
            project_id: window.currentProjectId,
            title: planName,
            payment_mode: paymentMode,
            start_date: startDate,
            end_date: endDate,
            proposed_cost: totalBudget, // Use proposed_cost to match openModal logic
            downpayment_amount: downpayment
        };

        // Get milestone items from window.milestoneItemsData
        // (This was pre-populated by Blade)
        const itemsData = window.milestoneItemsData || {};

        if (window.openMilestoneSetupModal) {
            if (window.milestoneSetupModal && window.milestoneSetupModal.openEditModal) {
                window.milestoneSetupModal.openEditModal(milestoneData, itemsData);
            } else {
                window.openMilestoneSetupModal(milestoneData);
            }
        } else {
            console.error('Milestone Setup Modal not initialized');
        }
    }

    handleMilestoneClick(milestoneId) {
        // Open the view progress report modal — passes the item_id directly.
        // The modal reads all data from window.milestoneItemsData (PHP-precomputed).
        if (window.openViewProgressReportModal) {
            window.openViewProgressReportModal(milestoneId);
        } else {
            console.error('View Progress Report Modal not initialized');
        }
    }

    handlePaymentHistory() {
        // Read PHP-precomputed payment data
        const paymentData = window.paymentHistoryData || {
            payments: [],
            summary: { totalEstimated: '₱0', totalPaid: 0, totalRemaining: '₱0' }
        };

        const projectData = {
            projectTitle: window.projectTitle || 'Project'
        };

        if (window.openPaymentHistoryModal) {
            window.openPaymentHistoryModal(paymentData, projectData);
        } else {
            console.error('Payment History Modal not initialized');
        }
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `milestone-toast milestone-toast-${type}`;
        toast.innerHTML = `<i class="fi fi-rr-${type === 'warning' ? 'lock' : 'info'}"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ContractorMilestoneReport();
});
