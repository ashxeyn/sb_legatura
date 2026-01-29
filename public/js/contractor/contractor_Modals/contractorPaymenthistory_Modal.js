/**
 * Payment History Modal JavaScript
 * Handles the display and interactions for the payment history modal
 */

class OwnerPaymenthistoryModal {
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

        // Mark all as read link
        const markAllReadLink = document.getElementById('markAllReadLink');
        if (markAllReadLink) {
            markAllReadLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleMarkAllAsRead();
            });
        }

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });
    }

    open(paymentData, projectData) {
        if (!paymentData) {
            console.error('No payment data provided');
            return;
        }

        this.paymentData = paymentData;
        this.projectData = projectData || {};

        this.populateModal(paymentData, projectData);
        
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

    populateModal(paymentData, projectData) {
        // Populate payment entries
        this.populatePaymentEntries(paymentData.payments || []);

        // Populate summary
        this.populateSummary(paymentData.summary || {});
    }

    populatePaymentEntries(payments) {
        const entriesList = document.getElementById('paymentEntriesList');
        if (!entriesList) return;

        entriesList.innerHTML = '';

        if (!payments || payments.length === 0) {
            entriesList.innerHTML = '<p class="text-gray-500 text-center py-8">No payment history available</p>';
            return;
        }

        payments.forEach((payment, index) => {
            const entry = this.createPaymentEntry(payment, index);
            entriesList.appendChild(entry);
        });
    }

    createPaymentEntry(payment, index) {
        const entry = document.createElement('div');
        entry.className = `payment-entry ${payment.unread ? 'unread' : ''}`;
        entry.setAttribute('data-payment-id', payment.id);

        const statusIcon = payment.status === 'pending' || payment.unread 
            ? 'fi-rr-minus' 
            : 'fi-rr-check';
        const statusClass = payment.status === 'pending' || payment.unread 
            ? 'pending' 
            : 'completed';

        const paymentType = payment.type || 'Bank Payment';
        const milestoneNumber = payment.milestoneNumber || payment.milestoneId || index + 1;
        const amount = payment.amount || '₱0';
        const date = payment.date || '';
        const time = payment.time || '';

        entry.innerHTML = `
            <div class="payment-status-icon ${statusClass}">
                <i class="fi ${statusIcon}"></i>
            </div>
            <div class="payment-entry-content">
                <div class="payment-entry-header">
                    <div class="payment-entry-description">
                        <p class="payment-entry-type">
                            ${paymentType}: <span class="payment-entry-milestone">Milestone ${milestoneNumber}</span>
                        </p>
                        <p class="payment-entry-amount">${amount}</p>
                    </div>
                    <div class="payment-entry-meta">
                        <p class="payment-entry-date">${date}</p>
                        <p class="payment-entry-time">${time}</p>
                        <a href="#" class="payment-details-link" data-payment-id="${payment.id}">Details</a>
                    </div>
                </div>
            </div>
        `;

        // Add click handlers
        const detailsLink = entry.querySelector('.payment-details-link');
        if (detailsLink) {
            detailsLink.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handlePaymentDetails(payment.id);
            });
        }

        // Mark as read on click
        entry.addEventListener('click', (e) => {
            if (!e.target.closest('.payment-details-link')) {
                if (payment.unread) {
                    this.markPaymentAsRead(payment.id, entry);
                }
            }
        });

        return entry;
    }

    populateSummary(summary) {
        const totalEstimated = document.getElementById('totalEstimatedAmount');
        const totalPaid = document.getElementById('totalAmountPaid');
        const totalRemaining = document.getElementById('totalRemainingAmount');

        if (totalEstimated) {
            totalEstimated.textContent = summary.totalEstimated || '₱0';
        }

        if (totalPaid) {
            const paidAmount = summary.totalPaid || 0;
            totalPaid.textContent = paidAmount > 0 ? `-₱${this.formatNumber(paidAmount)}` : '₱0';
        }

        if (totalRemaining) {
            totalRemaining.textContent = summary.totalRemaining || '₱0';
        }
    }

    formatNumber(num) {
        if (typeof num === 'string') {
            // Remove currency symbols and commas, then parse
            num = num.replace(/[₱,]/g, '');
            num = parseFloat(num);
        }
        return num.toLocaleString('en-US');
    }

    handlePaymentDetails(paymentId) {
        const payment = this.paymentData.payments?.find(p => p.id === paymentId);
        if (payment) {
            console.log('Viewing payment details:', payment);
            // In a real implementation, open a detailed view or modal
            this.showNotification(`Viewing details for payment ${paymentId}`, 'info');
        }
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

    handleMarkAllAsRead() {
        const unreadEntries = document.querySelectorAll('.payment-entry.unread');
        
        unreadEntries.forEach(entry => {
            const paymentId = entry.getAttribute('data-payment-id');
            this.markPaymentAsRead(paymentId, entry);
        });

        this.showNotification('All payments marked as read', 'success');
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
        paymentHistoryModalInstance = new OwnerPaymenthistoryModal();
        
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
