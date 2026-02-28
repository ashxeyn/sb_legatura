/**
 * Contractor Payment Validation Modal JavaScript
 * Handles the approval and rejection of payment receipts
 */

class ContractorPaymentvalidationModal {
    constructor() {
        this.approveModal = null;
        this.rejectModal = null;
        this.currentPaymentId = null;
        this.isSubmitting = false;

        this.init();
    }

    init() {
        this.approveModal = document.getElementById('paymentApproveModal');
        this.rejectModal = document.getElementById('paymentRejectModal');

        if (!this.approveModal || !this.rejectModal) {
            console.warn('Payment validation modal elements not found');
            return;
        }

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Approve Modal
        const closeApproveBtn = document.getElementById('closePaymentApproveModalBtn');
        const cancelApproveBtn = document.getElementById('cancelApproveBtn');
        const submitApproveBtn = document.getElementById('submitApproveBtn');

        if (closeApproveBtn) closeApproveBtn.addEventListener('click', () => this.closeApprove());
        if (cancelApproveBtn) cancelApproveBtn.addEventListener('click', () => this.closeApprove());
        if (submitApproveBtn) submitApproveBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.handleApproveSubmit();
        });

        // Reject Modal
        const closeRejectBtn = document.getElementById('closePaymentRejectModalBtn');
        const cancelRejectBtn = document.getElementById('cancelRejectBtn');
        const submitRejectBtn = document.getElementById('submitRejectBtn');

        if (closeRejectBtn) closeRejectBtn.addEventListener('click', () => this.closeReject());
        if (cancelRejectBtn) cancelRejectBtn.addEventListener('click', () => this.closeReject());
        if (submitRejectBtn) submitRejectBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.handleRejectSubmit();
        });
    }

    openApprove(paymentId) {
        this.currentPaymentId = paymentId;
        const idInput = document.getElementById('approvePaymentId');
        if (idInput) idInput.value = paymentId;

        if (this.approveModal) {
            this.approveModal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Clear any previous errors
            const errorMsg = document.getElementById('approveErrorMsg');
            if (errorMsg) errorMsg.textContent = '';
        }
    }

    closeApprove() {
        if (this.approveModal) {
            this.approveModal.classList.remove('active');
            this.checkAndClearOverflow();
        }
        this.currentPaymentId = null;
    }

    openReject(paymentId) {
        this.currentPaymentId = paymentId;
        const idInput = document.getElementById('rejectPaymentId');
        if (idInput) idInput.value = paymentId;

        if (this.rejectModal) {
            this.rejectModal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Clear any previous errors
            const reasonInput = document.getElementById('rejectReason');
            if (reasonInput) {
                reasonInput.value = '';
                reasonInput.classList.remove('is-invalid');
            }
            const errorMsg = document.getElementById('rejectReasonError');
            if (errorMsg) errorMsg.textContent = '';
        }
    }

    closeReject() {
        if (this.rejectModal) {
            this.rejectModal.classList.remove('active');
            this.checkAndClearOverflow();
        }
        this.currentPaymentId = null;
    }

    checkAndClearOverflow() {
        const activeModals = document.querySelectorAll('.payment-reject-modal.active, .progress-report-modal.active, .modal.active');
        if (activeModals.length === 0) {
            document.body.style.overflow = '';
        }
    }

    async handleApproveSubmit() {
        if (this.isSubmitting || !this.currentPaymentId) return;

        const submitBtn = document.getElementById('submitApproveBtn');
        const originalContent = submitBtn.innerHTML;

        this.isSubmitting = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fi fi-rr-spinner-alt animate-spin"></i> Approving...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch(`/contractor/payments/${this.currentPaymentId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Payment approved successfully', 'success');
                this.closeApprove();

                // Close details modal if it exists
                if (window.contractorViewProgressReportModalInstance) {
                    window.contractorViewProgressReportModalInstance.close();
                }

                setTimeout(() => window.location.reload(), 1000);
            } else {
                this.showNotification(result.message || 'Failed to approve payment', 'error');
            }
        } catch (error) {
            console.error('Error approving payment:', error);
            this.showNotification('An unexpected error occurred.', 'error');
        } finally {
            this.isSubmitting = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalContent;
        }
    }

    async handleRejectSubmit() {
        if (this.isSubmitting || !this.currentPaymentId) return;

        const reasonInput = document.getElementById('rejectReason');
        const reason = reasonInput ? reasonInput.value.trim() : '';
        const errorMsg = document.getElementById('rejectReasonError');

        if (!reason || reason.length < 5) {
            if (errorMsg) errorMsg.textContent = 'Please provide a valid reason (min 5 characters).';
            if (reasonInput) reasonInput.classList.add('is-invalid');
            return;
        }

        // Clear error state if valid
        if (errorMsg) errorMsg.textContent = '';
        if (reasonInput) reasonInput.classList.remove('is-invalid');

        const submitBtn = document.getElementById('submitRejectBtn');
        const originalContent = submitBtn.innerHTML;

        this.isSubmitting = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fi fi-rr-spinner-alt animate-spin"></i> Declining...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch(`/contractor/payments/${this.currentPaymentId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Payment declined successfully', 'success');
                this.closeReject();

                // Close details modal
                if (window.contractorViewProgressReportModalInstance) {
                    window.contractorViewProgressReportModalInstance.close();
                }

                setTimeout(() => window.location.reload(), 1000);
            } else {
                this.showNotification(result.message || 'Failed to decline payment', 'error');
            }
        } catch (error) {
            console.error('Error declining payment:', error);
            this.showNotification('An unexpected error occurred.', 'error');
        } finally {
            this.isSubmitting = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalContent;
        }
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        const bgColors = {
            error: 'background: linear-gradient(135deg, #ef4444, #dc2626); color: white;',
            success: 'background: linear-gradient(135deg, #10b981, #059669); color: white;',
            info: 'background: linear-gradient(135deg, #EEA24B, #F57C00); color: white;',
        };
        toast.style.cssText = `
            position: fixed; top: 2rem; right: 2rem;
            padding: 0.875rem 1.5rem; border-radius: 0.75rem;
            font-size: 0.875rem; font-weight: 600; z-index: 9999;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            transition: opacity 0.3s ease;
            ${bgColors[type] || bgColors.info}
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
}

// Initialize when DOM is ready
window.contractorPaymentvalidationModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    window.contractorPaymentvalidationModalInstance = new ContractorPaymentvalidationModal();
});

// Global functions for triggering modals from other scripts
window.openPaymentApproveModal = (paymentId) => {
    if (window.contractorPaymentvalidationModalInstance) {
        window.contractorPaymentvalidationModalInstance.openApprove(paymentId);
    }
};

window.openPaymentRejectModal = (paymentId) => {
    if (window.contractorPaymentvalidationModalInstance) {
        window.contractorPaymentvalidationModalInstance.openReject(paymentId);
    }
};
