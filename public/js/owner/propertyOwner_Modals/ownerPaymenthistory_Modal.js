/**
 * Payment History Modal JavaScript
 * Matches the mobile milestoneApproval.tsx Payment History UI.
 * ─────────────────────────────────────────────────────────────
 *  List view  → header · "Mark all as read" · payment items · summary card
 *  Detail view → status badge · amount card · milestone info · transaction details · receipt · actions
 */

class OwnerPaymenthistoryModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.paymentData = null;
        this.projectData = null;
        this.selectedPayment = null;
        this.init();
    }

    // ── Initialisation ──────────────────────────────────────────────────
    init() {
        this.modal = document.getElementById('paymentHistoryModal');
        this.overlay = document.getElementById('paymentHistoryModalOverlay');
        if (!this.modal || !this.overlay) return;
        this.setupEventListeners();
    }

    setupEventListeners() {
        const closeBtn = document.getElementById('closePaymentHistoryModalBtn');
        if (closeBtn) closeBtn.addEventListener('click', () => this.close());

        const backBtn = document.getElementById('phBackBtn');
        if (backBtn) backBtn.addEventListener('click', () => this.handleBack());

        if (this.overlay) this.overlay.addEventListener('click', () => this.close());

        const markAll = document.getElementById('markAllReadLink');
        if (markAll) markAll.addEventListener('click', (e) => { e.preventDefault(); this.handleMarkAllAsRead(); });

        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && this.isOpen()) this.close(); });
    }

    // ── Open / Close ────────────────────────────────────────────────────
    open(paymentData, projectData) {
        if (!paymentData) return;
        this.paymentData = paymentData;
        this.projectData = projectData || {};
        this.selectedPayment = null;

        this.renderListView();
        this.showListView();

        requestAnimationFrame(() => {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    close() {
        if (!this.modal) return;
        this.modal.classList.remove('active');
        document.body.style.overflow = '';
        this.paymentData = null;
        this.projectData = null;
        this.selectedPayment = null;
    }

    isOpen() { return this.modal && this.modal.classList.contains('active'); }

    handleBack() {
        if (this.selectedPayment) {
            this.selectedPayment = null;
            this.showListView();
        } else {
            this.close();
        }
    }

    showListView() {
        const list = document.getElementById('phListView');
        const detail = document.getElementById('phDetailView');
        if (list) list.style.display = '';
        if (detail) detail.style.display = 'none';
        const title = this.modal.querySelector('.ph-header-title');
        if (title) title.textContent = 'Payment History';
    }

    showDetailView() {
        const list = document.getElementById('phListView');
        const detail = document.getElementById('phDetailView');
        if (list) list.style.display = 'none';
        if (detail) detail.style.display = '';
        const title = this.modal.querySelector('.ph-header-title');
        if (title) title.textContent = 'Payment Details';
    }

    // ── List View Rendering ─────────────────────────────────────────────
    renderListView() {
        const payments = this.paymentData?.payments || [];
        const entriesList = document.getElementById('paymentEntriesList');
        const summaryCard = document.getElementById('phSummaryCard');
        if (!entriesList) return;

        // Empty state
        if (payments.length === 0) {
            entriesList.innerHTML = `
                <div class="ph-empty">
                    <div style="width:80px;height:80px;background:#F1F5F9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:8px;">
                        <i class="fi fi-rr-credit-card" style="font-size:2.5rem;color:#94A3B8;"></i>
                    </div>
                    <span class="ph-empty-title">No Payment History</span>
                    <span class="ph-empty-text">Payment receipts will appear here once submitted</span>
                </div>`;
            if (summaryCard) summaryCard.innerHTML = '';
            return;
        }

        // Payment items
        entriesList.innerHTML = payments.map((p, i) => {
            const statusIcon = p.status === 'approved' ? 'fi-rr-check'
                             : p.status === 'rejected' ? 'fi-rr-cross-small'
                             : 'fi-rr-time-half-past';
            const statusColor = p.status === 'approved' ? '#10B981'
                              : p.status === 'rejected' ? '#EF4444'
                              : '#EC7E00';
            const statusBg = statusColor + '15';
            return `
            <div class="ph-payment-item${p.unread ? ' ph-unread' : ''}" data-index="${i}">
                <div class="ph-payment-row">
                    <div class="ph-status-icon" style="background:${statusBg};">
                        <i class="fi ${statusIcon}" style="color:${statusColor};"></i>
                    </div>
                    <div class="ph-payment-main">
                        <div class="ph-payment-top">
                            <div class="ph-payment-title-wrap">
                                <span class="ph-payment-type">${this.esc(p.type)}: </span>
                                <span class="ph-payment-milestone">${this.esc(p.milestoneNumber)}</span>
                            </div>
                            <div class="ph-payment-date-wrap">
                                <span class="ph-payment-date">${this.esc(p.date)}</span>
                                <span class="ph-payment-time">${this.esc(p.time)}</span>
                            </div>
                        </div>
                        <div class="ph-payment-bottom">
                            <span class="ph-payment-amount">${this.esc(p.amount)}</span>
                            <button class="ph-details-link" data-index="${i}">Details</button>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join('');

        // Summary card
        if (summaryCard) {
            const s = this.paymentData.summary || {};
            const dpAmount = this.paymentData.summary?.downpayment;
            summaryCard.innerHTML = `
                <div class="ph-summary-row">
                    <span class="ph-summary-label">Total Estimated Project Amount:</span>
                    <span class="ph-summary-value">${this.esc(s.totalEstimated || '₱0')}</span>
                </div>
                ${dpAmount ? `
                <div class="ph-summary-row">
                    <span class="ph-summary-label">Downpayment Amount:</span>
                    <span class="ph-summary-value">${this.esc(dpAmount)}</span>
                </div>` : ''}
                <div class="ph-summary-row">
                    <span class="ph-summary-label">Total Amount Paid:</span>
                    <span class="ph-summary-value ph-value-success">${this.formatPaid(s.totalPaid)}</span>
                </div>
                <div class="ph-summary-divider"></div>
                <div class="ph-summary-row ph-summary-row-last">
                    <span class="ph-summary-label ph-label-bold">Total Remaining Amount:</span>
                    <span class="ph-summary-value ph-value-danger">${this.esc(s.totalRemaining || '₱0')}</span>
                </div>`;
        }

        // Bind click events
        entriesList.querySelectorAll('.ph-payment-item').forEach(el => {
            el.addEventListener('click', (e) => {
                if (!e.target.closest('.ph-details-link')) {
                    const idx = parseInt(el.dataset.index);
                    if (payments[idx]?.unread) {
                        el.classList.remove('ph-unread');
                        payments[idx].unread = false;
                    }
                }
            });
        });

        entriesList.querySelectorAll('.ph-details-link').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const idx = parseInt(btn.dataset.index);
                this.openPaymentDetail(payments[idx]);
            });
        });
    }

    // ── Detail View Rendering ───────────────────────────────────────────
    openPaymentDetail(payment) {
        if (!payment) return;
        this.selectedPayment = payment;
        const raw = payment._raw || payment;
        const container = document.getElementById('phDetailContent');
        if (!container) return;

        const statusColor = raw.payment_status === 'approved' ? '#10B981'
                          : raw.payment_status === 'rejected' ? '#EF4444'
                          : '#F59E0B';
        const statusLabel = (raw.payment_status || 'pending').charAt(0).toUpperCase() + (raw.payment_status || 'pending').slice(1);

        const amount = payment.amount || this.formatCur(parseFloat(raw.amount || 0));
        const milestoneTitle = raw.milestone_item_title || payment.milestoneNumber || '';
        const progress = raw.percentage_progress != null ? `${raw.percentage_progress}%` : '';
        const transDate = payment.date || '';
        const paymentMethod = payment.type || this.getPaymentTypeLabel(raw.payment_type);
        const refNumber = raw.transaction_number || '';
        const ownerName = raw.owner_name || '';
        const receiptPhoto = raw.receipt_photo || '';
        const rejectionReason = (raw.payment_status === 'rejected' && raw.reason) ? raw.reason : '';

        let html = '';

        // Status Badge
        html += `
            <div class="ph-detail-status-container">
                <span class="ph-detail-status-badge" style="background:${statusColor};">${this.esc(statusLabel)}</span>
            </div>`;

        // Amount Card
        html += `
            <div class="ph-detail-amount-card">
                <span class="ph-detail-amount-label">PAYMENT AMOUNT</span>
                <span class="ph-detail-amount-value">${this.esc(amount)}</span>
            </div>`;

        // Milestone Info Card
        html += `
            <div class="ph-detail-card">
                <div class="ph-detail-card-header">
                    <i class="fi fi-rr-flag" style="color:#EC7E00;"></i>
                    <span class="ph-detail-card-title">Milestone Information</span>
                </div>
                <div class="ph-detail-card-body">
                    <div class="ph-detail-milestone-title">${this.esc(milestoneTitle)}</div>
                    ${progress ? `
                    <div class="ph-detail-info-row">
                        <span class="ph-detail-info-label">Progress:</span>
                        <span class="ph-detail-info-value">${this.esc(progress)}</span>
                    </div>` : ''}
                </div>
            </div>`;

        // Transaction Details Card
        html += `
            <div class="ph-detail-card">
                <div class="ph-detail-card-header">
                    <i class="fi fi-rr-info" style="color:#EC7E00;"></i>
                    <span class="ph-detail-card-title">Transaction Details</span>
                </div>
                <div class="ph-detail-card-body">
                    <div class="ph-detail-info-row">
                        <span class="ph-detail-info-label">Date:</span>
                        <span class="ph-detail-info-value">${this.esc(transDate)}</span>
                    </div>
                    <div class="ph-detail-info-row">
                        <span class="ph-detail-info-label">Payment Method:</span>
                        <span class="ph-detail-info-value">${this.esc(paymentMethod)}</span>
                    </div>
                    ${refNumber ? `
                    <div class="ph-detail-info-row">
                        <span class="ph-detail-info-label">Reference #:</span>
                        <span class="ph-detail-info-value">${this.esc(refNumber)}</span>
                    </div>` : ''}
                    ${ownerName ? `
                    <div class="ph-detail-info-row">
                        <span class="ph-detail-info-label">Submitted By:</span>
                        <span class="ph-detail-info-value">${this.esc(ownerName)}</span>
                    </div>` : ''}
                </div>
            </div>`;

        // Rejection Reason Card
        if (rejectionReason) {
            html += `
                <div class="ph-detail-card ph-detail-card-error">
                    <div class="ph-detail-card-header">
                        <i class="fi fi-rr-exclamation" style="color:#EF4444;"></i>
                        <span class="ph-detail-card-title" style="color:#EF4444;">Rejection Reason</span>
                    </div>
                    <div class="ph-detail-card-body">
                        <p class="ph-detail-rejection-text">${this.esc(rejectionReason)}</p>
                    </div>
                </div>`;
        }

        // Receipt Photo Card
        if (receiptPhoto) {
            const receiptUrl = this.getReceiptUrl(receiptPhoto);
            html += `
                <div class="ph-detail-card">
                    <div class="ph-detail-card-header">
                        <i class="fi fi-rr-picture" style="color:#EC7E00;"></i>
                        <span class="ph-detail-card-title">Payment Receipt</span>
                    </div>
                    <div class="ph-detail-card-body">
                        <div class="ph-receipt-image-container">
                            <img src="${this.esc(receiptUrl)}" alt="Receipt" class="ph-receipt-image" loading="lazy"
                                 onerror="this.parentElement.innerHTML='<div class=\\'ph-receipt-error\\'><i class=\\'fi fi-rr-picture\\'></i><span>Image not available</span></div>';">
                            <button class="ph-receipt-expand" title="View full size" onclick="window.open('${this.esc(receiptUrl)}', '_blank')">
                                <i class="fi fi-rr-expand"></i>
                            </button>
                        </div>
                        <p class="ph-receipt-hint">Click the expand icon to view full size</p>
                    </div>
                </div>`;
        }

        container.innerHTML = html;
        this.showDetailView();
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    handleMarkAllAsRead() {
        const items = document.querySelectorAll('.ph-payment-item.ph-unread');
        items.forEach(el => el.classList.remove('ph-unread'));
        if (this.paymentData?.payments) {
            this.paymentData.payments.forEach(p => p.unread = false);
        }
        this.showToast('All payments marked as read', 'success');
    }

    getReceiptUrl(path) {
        if (!path) return '';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        let cleaned = path.replace(/^\/+/, '');
        if (cleaned.startsWith('storage/')) cleaned = cleaned.replace(/^storage\//, '');
        if (cleaned.startsWith('public/')) cleaned = cleaned.replace(/^public\//, '');
        return `/api/files/${cleaned}`;
    }

    getPaymentTypeLabel(type) {
        if (!type) return 'Payment';
        return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    formatPaid(val) {
        if (typeof val === 'number') {
            return `₱${val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }
        return val || '₱0';
    }

    formatCur(num) {
        return `₱${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    esc(str) {
        if (str == null) return '';
        const d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = 'ph-toast';
        const bg = type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#EC7E00';
        toast.style.background = bg;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('ph-toast-hide'); setTimeout(() => toast.remove(), 300); }, 3000);
    }
}

// ── Global Interface ────────────────────────────────────────────────────────
let paymentHistoryModalInstance = null;

function initializePaymentHistoryModal() {
    if (!paymentHistoryModalInstance) {
        paymentHistoryModalInstance = new OwnerPaymenthistoryModal();
        window.openPaymentHistoryModal = (paymentData, projectData) => {
            if (paymentHistoryModalInstance) paymentHistoryModalInstance.open(paymentData, projectData);
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
