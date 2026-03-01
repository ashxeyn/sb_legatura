/**
 * Report History Modal JavaScript
 * Matches the mobile disputeHistory.tsx UI.
 * ─────────────────────────────────────────────────────────────
 *  List view  → stats bar · dispute cards
 *  Detail view → status banner · info · project context · description · evidence · admin response
 */

const RH_STATUS = {
    open:         { label: 'Open',         icon: 'fi-rr-info',          color: '#3B82F6', bg: '#DBEAFE' },
    under_review: { label: 'Under Review', icon: 'fi-rr-eye',           color: '#F59E0B', bg: '#FEF3C7' },
    resolved:     { label: 'Resolved',     icon: 'fi-rr-check-circle',  color: '#10B981', bg: '#D1FAE5' },
    closed:       { label: 'Closed',       icon: 'fi-rr-cross-circle',  color: '#64748B', bg: '#F1F5F9' },
    cancelled:    { label: 'Cancelled',    icon: 'fi-rr-ban',           color: '#EF4444', bg: '#FEE2E2' },
};

const RH_TYPE = {
    Payment: { icon: 'fi-rr-dollar',         color: '#10B981' },
    Delay:   { icon: 'fi-rr-clock',          color: '#F59E0B' },
    Quality: { icon: 'fi-rr-exclamation',    color: '#EF4444' },
    Halt:    { icon: 'fi-rr-pause-circle',   color: '#EF4444' },
    Others:  { icon: 'fi-rr-menu-dots',      color: '#3B82F6' },
};

class OwnerReportHistoryModal {
    constructor() {
        this.modal = null;
        this.overlay = null;
        this.config = window.__milestoneReportConfig || {};
        this.disputes = this.config.disputes || [];
        this.selectedDispute = null;
        this.init();
    }

    init() {
        this.modal = document.getElementById('reportHistoryModal');
        this.overlay = document.getElementById('reportHistoryModalOverlay');
        if (!this.modal || !this.overlay) return;
        this.setupEventListeners();
    }

    setupEventListeners() {
        const backBtn = document.getElementById('rhBackBtn');
        if (backBtn) backBtn.addEventListener('click', () => this.handleBack());

        if (this.overlay) this.overlay.addEventListener('click', () => this.close());

        const refreshBtn = document.getElementById('rhRefreshBtn');
        if (refreshBtn) refreshBtn.addEventListener('click', () => this.refreshDisputes());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) this.close();
        });

        // Listen for new disputes filed — do a page-level reload of data
        window.addEventListener('disputeFiled', () => {
            this.refreshDisputes();
        });
    }

    // ── Open / Close ────────────────────────────────────────────────────
    open(disputes) {
        this.disputes = disputes || [];
        this.selectedDispute = null;

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
        this.selectedDispute = null;
    }

    isOpen() { return this.modal && this.modal.classList.contains('active'); }

    handleBack() {
        if (this.selectedDispute) {
            this.selectedDispute = null;
            this.showListView();
        } else {
            this.close();
        }
    }

    showListView() {
        const list = document.getElementById('rhListView');
        const detail = document.getElementById('rhDetailView');
        if (list) list.style.display = '';
        if (detail) detail.style.display = 'none';
        const title = this.modal.querySelector('.rh-header-title');
        if (title) title.textContent = 'Report History';
        // Show refresh, hide close
        const refreshBtn = document.getElementById('rhRefreshBtn');
        if (refreshBtn) refreshBtn.style.display = '';
    }

    showDetailView() {
        const list = document.getElementById('rhListView');
        const detail = document.getElementById('rhDetailView');
        if (list) list.style.display = 'none';
        if (detail) detail.style.display = '';
        const title = this.modal.querySelector('.rh-header-title');
        if (title) title.textContent = 'Report Details';
        const refreshBtn = document.getElementById('rhRefreshBtn');
        if (refreshBtn) refreshBtn.style.display = 'none';
    }

    // ── Refresh Disputes (full page reload to get server-side data) ───────
    async refreshDisputes() {
        const refreshBtn = document.getElementById('rhRefreshBtn');
        if (refreshBtn) refreshBtn.classList.add('rh-spinning');

        try {
            // Fetch the current page HTML and extract the updated disputes from config
            const res = await fetch(window.location.href, {
                headers: { 'Accept': 'text/html' },
            });
            const html = await res.text();
            const match = html.match(/window\.__milestoneReportConfig\s*=\s*({[\s\S]*?});/);
            if (match) {
                try {
                    const newConfig = JSON.parse(match[1]);
                    if (newConfig.disputes) {
                        this.disputes = newConfig.disputes;
                        // Also update the global config so other components see fresh data
                        if (window.__milestoneReportConfig) {
                            window.__milestoneReportConfig.disputes = newConfig.disputes;
                        }
                    }
                } catch (parseErr) {
                    console.error('[ReportHistory] config parse error:', parseErr);
                }
            }
        } catch (err) {
            console.error('[ReportHistory] refresh error:', err);
        } finally {
            if (refreshBtn) refreshBtn.classList.remove('rh-spinning');
            this.renderListView();
        }
    }

    // ── List View Rendering ─────────────────────────────────────────────
    renderListView() {
        const entriesList = document.getElementById('rhEntriesList');
        const statsBar = document.getElementById('rhStatsBar');
        const sectionHeader = document.getElementById('rhSectionHeader');
        if (!entriesList) return;

        // Empty state
        if (this.disputes.length === 0) {
            if (statsBar) statsBar.innerHTML = '';
            if (sectionHeader) sectionHeader.innerHTML = '';
            entriesList.innerHTML = `
                <div class="rh-empty">
                    <div class="rh-empty-icon-wrap">
                        <i class="fi fi-rr-inbox rh-empty-icon"></i>
                    </div>
                    <span class="rh-empty-title">No Reports Yet</span>
                    <span class="rh-empty-text">You haven't filed any reports yet. When you do, they will appear here.</span>
                </div>`;
            return;
        }

        // Stats
        const total = this.disputes.length;
        const openCount = this.disputes.filter(d => d.dispute_status === 'open').length;
        const resolvedCount = this.disputes.filter(d => d.dispute_status === 'resolved').length;

        if (statsBar) {
            statsBar.innerHTML = `
                <div class="rh-stat-card">
                    <span class="rh-stat-value">${total}</span>
                    <span class="rh-stat-label">Total</span>
                </div>
                <div class="rh-stat-card">
                    <span class="rh-stat-value" style="color:#3B82F6;">${openCount}</span>
                    <span class="rh-stat-label">Open</span>
                </div>
                <div class="rh-stat-card">
                    <span class="rh-stat-value" style="color:#10B981;">${resolvedCount}</span>
                    <span class="rh-stat-label">Resolved</span>
                </div>`;
        }

        if (sectionHeader) {
            sectionHeader.innerHTML = `<span class="rh-section-title">All Reports (${total})</span>`;
        }

        // Dispute cards
        entriesList.innerHTML = this.disputes.map((d, i) => {
            const status = RH_STATUS[d.dispute_status] || RH_STATUS.open;
            const type = RH_TYPE[d.dispute_type] || RH_TYPE.Others;
            const dateStr = this.formatDate(d.dispute_created_at || d.created_at);

            return `
            <div class="rh-dispute-card" data-index="${i}">
                <div class="rh-card-header">
                    <div class="rh-type-container">
                        <div class="rh-type-icon" style="background:${type.color}20;">
                            <i class="fi ${type.icon}" style="color:${type.color};"></i>
                        </div>
                        <div class="rh-type-text">
                            <span class="rh-type-name">${this.esc(d.dispute_type)}</span>
                            <span class="rh-type-date">${this.esc(dateStr)}</span>
                        </div>
                    </div>
                    <div class="rh-status-badge" style="background:${status.bg};">
                        <i class="fi ${status.icon}" style="color:${status.color}; font-size:0.75rem;"></i>
                        <span style="color:${status.color};">${status.label}</span>
                    </div>
                </div>
                <p class="rh-card-desc">${this.esc(this.truncate(d.dispute_desc, 120))}</p>
                ${d.project_title ? `
                <div class="rh-card-context">
                    <i class="fi fi-rr-folder" style="color:#64748B; font-size:0.8rem;"></i>
                    <span>${this.esc(this.truncate(d.project_title, 50))}</span>
                </div>` : ''}
                ${d.milestone_item_title ? `
                <div class="rh-card-context">
                    <i class="fi fi-rr-flag" style="color:#64748B; font-size:0.8rem;"></i>
                    <span>${this.esc(this.truncate(d.milestone_item_title, 50))}</span>
                </div>` : ''}
                <div class="rh-card-footer">
                    <span class="rh-card-id">ID: #${d.dispute_id}</span>
                    <div class="rh-view-details">
                        <span>View Details</span>
                        <i class="fi fi-rr-angle-right" style="font-size:0.75rem;"></i>
                    </div>
                </div>
            </div>`;
        }).join('');

        // Bind clicks
        entriesList.querySelectorAll('.rh-dispute-card').forEach(card => {
            card.addEventListener('click', () => {
                const idx = parseInt(card.dataset.index);
                this.openDisputeDetail(this.disputes[idx]);
            });
        });
    }

    // ── Detail View Rendering ───────────────────────────────────────────
    openDisputeDetail(dispute) {
        if (!dispute) return;

        // All data including evidence files is already pre-loaded from the blade config
        const disputeObj = dispute;
        const evidenceFiles = dispute.files || [];
        this.selectedDispute = dispute;

        const container = document.getElementById('rhDetailContent');
        if (!container) return;

        const status = RH_STATUS[disputeObj.dispute_status] || RH_STATUS.open;
        const type = RH_TYPE[disputeObj.dispute_type] || RH_TYPE.Others;

        let html = '';

        // Status Banner
        html += `
            <div class="rh-detail-banner" style="background:${status.bg};">
                <i class="fi ${status.icon}" style="color:${status.color}; font-size:1.5rem;"></i>
                <span class="rh-detail-banner-text" style="color:${status.color};">${status.label}</span>
            </div>`;

        // Report Info Card
        html += `
            <div class="rh-detail-card">
                <h4 class="rh-detail-card-title">Report Information</h4>
                <div class="rh-detail-row">
                    <span class="rh-detail-label">Report ID:</span>
                    <span class="rh-detail-value">#${disputeObj.dispute_id}</span>
                </div>
                <div class="rh-detail-row">
                    <span class="rh-detail-label">Type:</span>
                    <div class="rh-detail-type-tag" style="background:${type.color}15;">
                        <i class="fi ${type.icon}" style="color:${type.color}; font-size:0.8rem;"></i>
                        <span style="color:${type.color};">${this.esc(disputeObj.dispute_type)}</span>
                    </div>
                </div>
                ${disputeObj.if_others_distype ? `
                <div class="rh-detail-row">
                    <span class="rh-detail-label">Specified Type:</span>
                    <span class="rh-detail-value">${this.esc(disputeObj.if_others_distype)}</span>
                </div>` : ''}
                <div class="rh-detail-row">
                    <span class="rh-detail-label">Filed On:</span>
                    <span class="rh-detail-value">${this.formatDateTime(disputeObj.dispute_created_at || disputeObj.created_at)}</span>
                </div>
                ${disputeObj.resolved_at ? `
                <div class="rh-detail-row">
                    <span class="rh-detail-label">Resolved On:</span>
                    <span class="rh-detail-value">${this.formatDateTime(disputeObj.resolved_at)}</span>
                </div>` : ''}
            </div>`;

        // Project Context Card
        if (disputeObj.project_title) {
            html += `
                <div class="rh-detail-card">
                    <h4 class="rh-detail-card-title">Project Context</h4>
                    <div class="rh-context-row">
                        <i class="fi fi-rr-folder" style="color:#EC7E00;"></i>
                        <div class="rh-context-text">
                            <span class="rh-context-label">Project</span>
                            <span class="rh-context-value">${this.esc(disputeObj.project_title)}</span>
                        </div>
                    </div>
                    ${disputeObj.milestone_item_title ? `
                    <div class="rh-context-row">
                        <i class="fi fi-rr-flag" style="color:#EC7E00;"></i>
                        <div class="rh-context-text">
                            <span class="rh-context-label">Milestone Item</span>
                            <span class="rh-context-value">${this.esc(disputeObj.milestone_item_title)}</span>
                        </div>
                    </div>` : ''}
                </div>`;
        }

        // Description Card
        html += `
            <div class="rh-detail-card">
                <h4 class="rh-detail-card-title">Description</h4>
                <p class="rh-detail-description">${this.esc(disputeObj.dispute_desc)}</p>
            </div>`;

        // Evidence Files Card
        if (evidenceFiles.length > 0) {
            html += `
                <div class="rh-detail-card">
                    <h4 class="rh-detail-card-title">Evidence Files (${evidenceFiles.length})</h4>
                    <div class="rh-evidence-grid">
                        ${evidenceFiles.map(file => {
                            const filePath = file.storage_path || file.file_path || '';
                            const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(file.original_name || filePath);
                            const fileUrl = this.getFileUrl(filePath);
                            return `
                            <div class="rh-evidence-item" ${isImage ? `onclick="window.open('${this.esc(fileUrl)}', '_blank')"` : ''}>
                                ${isImage
                                    ? `<div class="rh-evidence-img-wrap">
                                         <img src="${this.esc(fileUrl)}" alt="${this.esc(file.original_name || 'Evidence')}" class="rh-evidence-img" loading="lazy"
                                              onerror="this.parentElement.innerHTML='<i class=\\'fi fi-rr-picture\\' style=\\'font-size:2rem;color:#94A3B8;\\'></i>';">
                                         <div class="rh-evidence-overlay"><i class="fi fi-rr-expand"></i></div>
                                       </div>`
                                    : `<div class="rh-evidence-file-icon"><i class="fi fi-rr-document" style="font-size:2rem; color:#1E3A5F;"></i></div>`
                                }
                                <span class="rh-evidence-name">${this.esc(file.original_name || 'File')}</span>
                            </div>`;
                        }).join('')}
                    </div>
                </div>`;
        }

        // Admin Response Card
        if (disputeObj.admin_response) {
            html += `
                <div class="rh-detail-card rh-admin-response-card">
                    <div class="rh-admin-header">
                        <i class="fi fi-rr-comment-alt" style="color:#10B981;"></i>
                        <h4 class="rh-detail-card-title" style="color:#10B981; margin:0;">Admin Response</h4>
                    </div>
                    <p class="rh-admin-text">${this.esc(disputeObj.admin_response)}</p>
                </div>`;
        }

        container.innerHTML = html;
        this.showDetailView();
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    getFileUrl(path) {
        if (!path) return '';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        let cleaned = path.replace(/^\/+/, '');
        if (cleaned.startsWith('storage/')) cleaned = cleaned.replace(/^storage\//, '');
        if (cleaned.startsWith('public/')) cleaned = cleaned.replace(/^public\//, '');
        return `/api/files/${cleaned}`;
    }

    formatDate(dateStr) {
        if (!dateStr) return '';
        try {
            const d = new Date(String(dateStr));
            if (isNaN(d.getTime())) return String(dateStr);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        } catch (_) { return String(dateStr); }
    }

    formatDateTime(dateStr) {
        if (!dateStr) return '';
        try {
            const d = new Date(String(dateStr));
            if (isNaN(d.getTime())) return String(dateStr);
            return d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
        } catch (_) { return String(dateStr); }
    }

    truncate(str, maxLen = 100) {
        if (!str) return '';
        return str.length > maxLen ? str.substring(0, maxLen) + '...' : str;
    }

    esc(str) {
        if (str == null) return '';
        const d = document.createElement('div');
        d.textContent = String(str);
        return d.innerHTML;
    }
}

// ── Global Interface ────────────────────────────────────────────────────────
let reportHistoryModalInstance = null;

function initializeReportHistoryModal() {
    if (!reportHistoryModalInstance) {
        reportHistoryModalInstance = new OwnerReportHistoryModal();
        window.openReportHistoryModal = (disputes) => {
            if (reportHistoryModalInstance) reportHistoryModalInstance.open(disputes);
        };
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeReportHistoryModal);
} else {
    initializeReportHistoryModal();
}
