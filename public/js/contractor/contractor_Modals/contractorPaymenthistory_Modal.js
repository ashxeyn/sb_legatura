/**
 * Payment History Modal JavaScript
 * Handles the display and interactions for the payment history modal
 */

class ContractorPaymenthistoryModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.paymentData = null;
        this.projectData = null;

        this.init();
    }

    init() {
        this.modal = document.getElementById('paymentHistoryModal');
        this.overlay = document.getElementById('paymentHistoryModalOverlay');

        if (!this.modal || !this.overlay) {
            console.error('Payment History Modal elements not found');
            return;
        }

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close button
        const closeBtn = document.getElementById('closePaymentHistoryModalBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        // Overlay click to close
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });

        // Use event delegation for payment details links since they are rendered by PHP
        this.modal.addEventListener('click', (e) => {
            const detailsLink = e.target.closest('.payment-details-link');
            if (detailsLink) {
                e.preventDefault();
                e.stopPropagation();
                const paymentId = detailsLink.getAttribute('data-payment-id');
                this.handlePaymentDetails(paymentId);
            }

            // Mark as read on click (if unread)
            const entry = e.target.closest('.payment-entry');
            if (entry && !detailsLink) {
                if (entry.classList.contains('unread')) {
                    const paymentId = entry.getAttribute('data-payment-id');
                    this.markPaymentAsRead(paymentId, entry);
                }
            }
        });
    }

    open(paymentData, projectData) {
        // paymentData and projectData are still passed in but mostly ignored for rendering
        // as the modal is now pre-populated by Blade.
        this.paymentData = paymentData;
        this.projectData = projectData || {};

        if (this.modal) {
            requestAnimationFrame(() => {
                this.modal.classList.add('active');
                document.body.style.overflow = 'hidden';

                // Add entrance animation
                const container = this.modal.querySelector('.payment-history-modal-container');
                if (container) {
                    container.style.animation = 'slideUpScale 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                }
            });
        }
    }

    close() {
        if (this.modal) {
            const container = this.modal.querySelector('.payment-history-modal-container');
            if (container) {
                container.style.animation = 'slideDown 0.3s ease-in';
            }

            setTimeout(() => {
                this.modal.classList.remove('active');
                document.body.style.overflow = '';
            }, 250);
        }
        this.paymentData = null;
        this.projectData = null;
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }

    handlePaymentDetails(paymentId) {
        console.log('Viewing payment details for ID:', paymentId);
        // This can be expanded to show more details or fetch via AJAX
        this.showNotification(`Viewing details for payment ${paymentId}`, 'info');
    }

    markPaymentAsRead(paymentId, entryElement) {
        if (entryElement) {
            entryElement.classList.remove('unread');

            // Update icon from pending to completed
            const statusIcon = entryElement.querySelector('.payment-status-icon');
            if (statusIcon) {
                statusIcon.classList.remove('pending');
                statusIcon.classList.add('completed');
                const icon = statusIcon.querySelector('i');
                if (icon) {
                    icon.className = 'fi fi-rr-check';
                }
            }
        }

        // In a real implementation, send request to server
        console.log('Marking payment as read:', paymentId);
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        let bgColor = '#EEA24B';
        if (type === 'success') {
            bgColor = '#10b981';
        } else if (type === 'error') {
            bgColor = '#dc2626';
        }

        toast.className = 'fixed bottom-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.style.backgroundColor = bgColor;
        toast.textContent = message;
        toast.style.cssText += `
            animation: slideUp 0.3s ease-out;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
}

// Initialize modal when DOM is ready
let paymentHistoryModalInstance = null;

function initializePaymentHistoryModal() {
    if (!paymentHistoryModalInstance) {
        paymentHistoryModalInstance = new ContractorPaymenthistoryModal();

        // Make it globally accessible
        window.openPaymentHistoryModal = (paymentData, projectData) => {
            if (paymentHistoryModalInstance) {
                paymentHistoryModalInstance.open(paymentData, projectData);
            }
        };
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePaymentHistoryModal);
} else {
    // DOM is already loaded
    initializePaymentHistoryModal();
}
