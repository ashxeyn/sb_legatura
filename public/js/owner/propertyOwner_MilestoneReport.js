/**
 * Property Owner Milestone Report (Project Timeline)
 * Integrates with the same backend APIs as the mobile milestoneApproval.tsx
 *
 * API endpoints used:
 *  GET  /api/owner/projects?user_id=            → project list with milestones, items, payment_plans
 *  GET  /api/projects/{id}/payments              → payment history for a project
 *  POST /owner/milestones/{id}/approve           → approve milestone setup (web route)
 *  POST /owner/milestones/{id}/reject            → reject milestone setup (web route)
 *  POST /api/owner/projects/{id}/complete        → mark project completed
 *  GET  /api/projects/{id}/update/context        → pending extension for owner banner
 *  GET  /api/projects/{id}/updates               → approved extension history
 */

class PropertyOwnerMilestoneReport {
    constructor() {
        this.config = window.__milestoneReportConfig || {};
        this.projectId = this.config.projectId;
        this.userId = this.config.userId;
        this.csrfToken = this.config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
        this.routes = this.config.routes || {};

        // State
        this.project = null;
        this.milestones = [];
        this.allItems = [];
        this.paymentPlan = null;
        this.paymentHistory = [];
        this.pendingUpdate = null;
        this.approvedUpdates = [];
        this.projectStatus = '';
        this.isProjectCompleted = false;

        this.init();
    }

    // ── Initialisation ──────────────────────────────────────────────────────
    async init() {
        this.setupMenuDropdown();
        this.setupRetryButton();

        if (!this.projectId || !this.userId) {
            this.showError('Missing project or user information. Please go back and try again.');
            return;
        }

        await this.loadProjectData();
    }

    // ── Data Loading ────────────────────────────────────────────────────────
    async loadProjectData() {
        this.showLoading();
        try {
            const res = await fetch(`/api/owner/projects?user_id=${this.userId}`);
            const json = await res.json();

            if (!json.success) throw new Error(json.message || 'Failed to fetch projects');

            const projects = json.data?.data || json.data || [];
            this.project = projects.find(p => String(p.project_id) === String(this.projectId));

            if (!this.project) throw new Error('Project not found in your projects list.');

            this.milestones = this.project.milestones || [];
            this.projectStatus = (this.project.display_status || this.project.project_status || '').toLowerCase();
            this.isProjectCompleted = this.projectStatus === 'completed';

            // Flatten items
            this.allItems = [];
            this.milestones.forEach(m => {
                if (m.items && m.items.length > 0) {
                    m.items.forEach(item => {
                        this.allItems.push({
                            ...item,
                            parentMilestoneId: m.milestone_id,
                            parentSetupStatus: m.setup_status,
                            parentMilestoneStatus: m.milestone_status,
                        });
                    });
                }
            });
            this.allItems.sort((a, b) => a.sequence_order - b.sequence_order);

            // Payment plan from first milestone
            const first = this.milestones[0];
            this.paymentPlan = first?.payment_plan || null;

            // Fetch pending update & approved updates in parallel
            await Promise.allSettled([
                this.fetchPendingUpdate(),
                this.fetchApprovedUpdates(),
            ]);

            this.render();
        } catch (err) {
            console.error('[MilestoneReport] loadProjectData error:', err);
            this.showError(err.message || 'An error occurred while loading project data.');
        }
    }

    async fetchPendingUpdate() {
        try {
            const res = await fetch(`/api/projects/${this.projectId}/update/context`);
            const json = await res.json();
            if (json.success && json.data?.pending_extension) {
                this.pendingUpdate = json.data.pending_extension;
            }
        } catch (_) {}
    }

    async fetchApprovedUpdates() {
        try {
            const res = await fetch(`/api/projects/${this.projectId}/updates`);
            const json = await res.json();
            if (json.success && json.data) {
                this.approvedUpdates = (Array.isArray(json.data) ? json.data : [])
                    .filter(u => u.status === 'approved');
            }
        } catch (_) {}
    }

    async fetchPaymentHistory() {
        // Use server-side pre-fetched payments from blade config
        const serverPayments = this.config.payments || [];
        if (serverPayments.length > 0) {
            this.paymentHistory = serverPayments;
            return;
        }
        // Fallback: try API call if no server data available
        try {
            const res = await fetch(`/api/projects/${this.projectId}/payments`);
            const json = await res.json();
            if (json.success) {
                const payments = json.data?.payments || json.payments || [];
                this.paymentHistory = Array.isArray(payments) ? payments : [];
            } else {
                this.paymentHistory = [];
            }
        } catch (_) {
            this.paymentHistory = [];
        }
    }

    // ── API Actions ─────────────────────────────────────────────────────────
    async approveMilestone(milestoneId) {
        try {
            const res = await fetch(`/owner/milestones/${milestoneId}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ user_id: this.userId }),
            });
            const text = await res.text();
            let json;
            try { json = JSON.parse(text); } catch (_) { json = { success: res.ok }; }
            if (json.success || res.ok) {
                this.showToast('Milestone setup approved successfully!', 'success');
                await this.loadProjectData();
            } else {
                this.showToast(json.message || 'Failed to approve milestone', 'error');
            }
        } catch (err) {
            this.showToast('An unexpected error occurred', 'error');
        }
    }

    async rejectMilestone(milestoneId, reason) {
        try {
            const res = await fetch(`/owner/milestones/${milestoneId}/reject`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ user_id: this.userId, reason, rejection_reason: reason }),
            });
            const text = await res.text();
            let json;
            try { json = JSON.parse(text); } catch (_) { json = { success: res.ok }; }
            if (json.success || res.ok) {
                this.showToast('Change request sent to contractor', 'success');
                await this.loadProjectData();
            } else {
                this.showToast(json.message || 'Failed to request changes', 'error');
            }
        } catch (err) {
            this.showToast('An unexpected error occurred', 'error');
        }
    }

    async completeProject() {
        try {
            const res = await fetch(`/api/owner/projects/${this.projectId}/complete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken, 'X-User-Id': String(this.userId) },
                body: JSON.stringify({}),
            });
            const json = await res.json();
            if (json.success) {
                this.isProjectCompleted = true;
                this.showToast('🎉 Project completed successfully!', 'success');
                await this.loadProjectData();
            } else {
                this.showToast(json.message || 'Failed to complete project', 'error');
            }
        } catch (err) {
            this.showToast('An unexpected error occurred', 'error');
        }
    }

    // ── Milestone Item Click → navigate to progress report ──────────────
    async handleMilestoneItemClick(item) {
        const url = this.routes.setMilestoneItem || '/owner/projects/set-milestone-item';
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body: JSON.stringify({ item_id: item.item_id, project_id: this.projectId }),
            });
            const json = await res.json();
            if (json.success) {
                window.location.href = this.routes.milestoneProgressReport || '/owner/projects/milestone-progress-report';
            } else {
                this.showToast('Could not navigate to progress report.', 'error');
            }
        } catch (err) {
            this.showToast('An error occurred. Please try again.', 'error');
        }
    }

    // ── Payment History (open modal with real data) ─────────────────────
    async handleViewPaymentHistory() {
        await this.fetchPaymentHistory();

        const totalCost = this.paymentPlan?.total_project_cost || this.project?.accepted_bid?.proposed_cost || 0;
        const isDP = this.paymentPlan?.payment_mode === 'downpayment';
        const dpAmount = isDP ? (this.paymentPlan?.downpayment_amount || 0) : 0;
        const approvedPayments = this.paymentHistory.filter(p => p.payment_status === 'approved');
        const totalPaid = approvedPayments.reduce((s, p) => s + parseFloat(p.amount || 0), 0);
        const remaining = totalCost - dpAmount - totalPaid;

        // Map to the modal's expected format
        const payments = this.paymentHistory.map((p, i) => {
            const dt = this.formatDateTime(p.transaction_date);
            return {
                id: p.payment_id,
                type: this.getPaymentTypeLabel(p.payment_type),
                milestoneNumber: p.milestone_item_title || `Item ${i + 1}`,
                milestoneId: p.item_id,
                amount: this.formatCurrency(parseFloat(p.amount || 0)),
                date: dt.date,
                time: dt.time,
                status: p.payment_status,
                unread: p.payment_status === 'submitted',
                _raw: p,
            };
        });

        const paymentData = {
            payments,
            summary: {
                totalEstimated: this.formatCurrency(totalCost),
                downpayment: dpAmount > 0 ? this.formatCurrency(dpAmount) : null,
                totalPaid: totalPaid,
                totalRemaining: this.formatCurrency(remaining),
            },
        };

        const projectData = {
            projectId: this.projectId,
            projectTitle: this.project.project_title || '',
        };

        if (window.openPaymentHistoryModal) {
            window.openPaymentHistoryModal(paymentData, projectData);
        } else {
            this.showToast('Payment history modal is not available', 'error');
        }
    }

    // ── Send Report (File Dispute) ────────────────────────────────────
    handleSendReport() {
        const projectData = {
            projectId: this.projectId,
            projectTitle: this.project?.project_title || '',
        };

        if (window.openSendReportModal) {
            window.openSendReportModal(projectData, this.allItems);
        } else {
            this.showToast('Send Report modal is not available', 'error');
        }
    }

    // ── Report History ──────────────────────────────────────────────────
    handleReportHistory() {
        const disputes = this.config.disputes || [];
        if (window.openReportHistoryModal) {
            window.openReportHistoryModal(disputes);
        } else {
            this.showToast('Report History modal is not available', 'error');
        }
    }

    // ── Rendering ───────────────────────────────────────────────────────────
    render() {
        const container = document.getElementById('milestoneContentArea');
        if (!container) return;

        // Hide loading, show content
        this.hideLoading();
        container.style.display = 'block';

        const first = this.milestones[0];
        const totalCost = this.paymentPlan?.total_project_cost || this.project?.accepted_bid?.proposed_cost || 0;
        const completedCount = this.allItems.filter(i => i.item_status === 'completed').length;
        const totalCount = this.allItems.length;
        const progressPercentage = totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0;
        const allDone = totalCount > 0 && completedCount === totalCount;
        const isDP = this.paymentPlan?.payment_mode === 'downpayment';
        const dpAmount = isDP ? (this.paymentPlan?.downpayment_amount || 0) : 0;
        const isHalted = ['halt', 'on_hold', 'halted'].includes(this.projectStatus);

        const submittedMilestone = this.milestones.find(m => m.setup_status === 'submitted');

        let html = '';

        // ── Summary Card ──
        html += `<div class="milestone-summary-card">`;

        // Title row
        if (first?.milestone_name) {
            html += `
                <div class="milestone-summary-title-row">
                    <div class="milestone-summary-title-info">
                        <span class="milestone-summary-title-label">PROJECT TITLE</span>
                        <span class="milestone-summary-title-text">${this.escapeHtml(first.milestone_name)}</span>
                    </div>
                    <button class="milestone-summary-btn" id="viewProjectSummaryBtn" title="View project summary">
                        <i class="fi fi-rr-chart-histogram"></i> Summary
                    </button>
                </div>`;
        }

        // Progress section
        html += `
            <div class="milestone-progress-section">
                <div class="milestone-progress-header">
                    <span class="milestone-progress-label"><i class="fi fi-rr-flag-alt"></i> ${completedCount} of ${totalCount} milestones completed</span>
                    <span class="milestone-progress-percent">${progressPercentage}%</span>
                </div>
                <div class="milestone-progress-bar-bg">
                    <div class="milestone-progress-bar-fill" style="width:${progressPercentage}%"></div>
                </div>
            </div>`;

        // Stats grid
        html += `<div class="milestone-stats-grid">`;
        html += `
            <div class="milestone-stat-card">
                <div class="milestone-stat-icon" style="background:rgba(238,162,75,0.12); color:#EC7E00;">
                    <i class="fi fi-rr-coins"></i>
                </div>
                <div class="milestone-stat-content">
                    <span class="milestone-stat-value">${this.formatCurrency(totalCost)}</span>
                    <span class="milestone-stat-label">Total Cost</span>
                </div>
            </div>`;
        if (first?.start_date && first?.end_date) {
            html += `
            <div class="milestone-stat-card">
                <div class="milestone-stat-icon" style="background:rgba(59,130,246,0.10); color:#3B82F6;">
                    <i class="fi fi-rr-calendar"></i>
                </div>
                <div class="milestone-stat-content">
                    <span class="milestone-stat-value">${this.formatDate(first.start_date)} – ${this.formatDate(first.end_date)}</span>
                    <span class="milestone-stat-label">Timeline</span>
                </div>
            </div>`;
        }
        if (isDP && dpAmount > 0) {
            html += `
            <div class="milestone-stat-card">
                <div class="milestone-stat-icon" style="background:rgba(16,185,129,0.10); color:#10B981;">
                    <i class="fi fi-rr-hand-holding-usd"></i>
                </div>
                <div class="milestone-stat-content">
                    <span class="milestone-stat-value">${this.formatCurrency(dpAmount)}</span>
                    <span class="milestone-stat-label">Down Payment</span>
                </div>
            </div>`;
        }
        html += `</div>`;

        // Extension history indicator
        if (this.approvedUpdates.length > 0) {
            html += `
                <div class="milestone-extension-indicator" id="extensionIndicatorToggle">
                    <div class="milestone-extension-left">
                        <i class="fi fi-rr-time-past"></i>
                        <span>Timeline extended ${this.approvedUpdates.length > 1 ? this.approvedUpdates.length + ' times' : 'once'}</span>
                    </div>
                    <i class="fi fi-rr-angle-small-down extension-chevron" id="extensionChevron"></i>
                </div>
                <div class="milestone-extension-history" id="extensionHistoryPanel" style="display:none;">
                    ${this.approvedUpdates.map((upd, i) => `
                        <div class="extension-history-item">
                            <div class="extension-history-number">${i + 1}</div>
                            <div class="extension-history-info">
                                <span class="extension-history-label">Extension #${i + 1}</span>
                                <span class="extension-history-dates">
                                    <i class="fi fi-rr-arrow-right" style="font-size:9px;"></i>
                                    ${this.formatDate(upd.current_end_date)} → ${this.formatDate(upd.proposed_end_date)}
                                </span>
                                ${upd.applied_at ? `<span class="extension-history-meta"><i class="fi fi-rr-check" style="font-size:9px; color:#10B981;"></i> Approved ${this.formatDate(upd.applied_at)}</span>` : ''}
                                ${upd.reason ? `<span class="extension-history-meta"><i class="fi fi-rr-comment-alt" style="font-size:9px;"></i> ${this.escapeHtml(upd.reason)}</span>` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>`;
        }

        html += `</div>`; // end summary card

        // ── Pending Update Banner (owner) ──
        if (this.pendingUpdate) {
            const isPendingRevision = this.pendingUpdate.status === 'revision_requested';
            html += `
                <div class="pending-update-banner ${isPendingRevision ? 'revision' : 'pending'}">
                    <div class="pending-update-inner">
                        <div class="pending-update-icon">
                            <i class="fi ${isPendingRevision ? 'fi-rr-edit' : 'fi-rr-exclamation'}"></i>
                        </div>
                        <div class="pending-update-text">
                            <span class="pending-update-title">${isPendingRevision ? 'Revision Requested' : 'Pending Update Request'}</span>
                            <span class="pending-update-desc">${isPendingRevision
                                ? 'You requested changes. Waiting for the contractor to revise.'
                                : 'The contractor has submitted a proposal for review.'}</span>
                        </div>
                        <i class="fi fi-rr-angle-small-right"></i>
                    </div>
                </div>`;
        }

        // ── Project Halted Banner ──
        if (isHalted) {
            html += `
                <div class="halted-banner">
                    <div class="halted-banner-inner">
                        <div class="halted-icon"><i class="fi fi-rr-pause-circle"></i></div>
                        <div class="halted-text">
                            <span class="halted-title">Project Halted</span>
                            <span class="halted-message">This project has been halted due to a pending dispute or administrative action. Milestone progress is temporarily paused.</span>
                        </div>
                    </div>
                </div>`;
        }

        // ── Timeline ──
        html += `<div class="milestone-timeline-container"><div class="milestone-timeline">`;

        // Iterate in natural order; column-reverse CSS puts item 1 at the bottom (near Start)
        this.allItems.forEach((item, index) => {
            const milestoneNumber = index + 1;
            const isLeft = index % 2 === 0;
            const isLast = index === this.allItems.length - 1;
            const itemPercentage = Number(item.percentage_progress) || 0;
            const cumulativePercentage = this.allItems
                .slice(0, index + 1)
                .reduce((sum, m) => sum + (Number(m.percentage_progress) || 0), 0);
            const displayPercentage = Math.round(cumulativePercentage);
            const isItemCompleted = item.item_status === 'completed' || item.parentMilestoneStatus === 'completed';
            const prevItem = this.allItems.filter(i => i.sequence_order < item.sequence_order).sort((a, b) => b.sequence_order - a.sequence_order)[0];
            const isLocked = prevItem && prevItem.item_status !== 'completed';

            // Status ring color
            const hasRejectedProgress = item.latest_progress_status === 'rejected';
            const hasRejectedPayment = item.latest_payment_status === 'rejected';
            const hasNewProgress = (item.progress_submitted_count || 0) > 0;
            const hasNewPayment = (item.payment_submitted_count || 0) > 0;
            const isItemHalted = item.item_status === 'halt';
            let ringClass = '';
            if (isItemHalted) ringClass = 'ring-error';
            else if (hasRejectedProgress || hasRejectedPayment) ringClass = 'ring-error';
            else if (!isItemCompleted && (hasNewProgress || hasNewPayment)) ringClass = 'ring-info';

            // Status tags
            const tags = this.getItemStatusTags(item);

            // Cost display
            let costHtml = '';
            if (item.adjusted_cost != null && (item.carry_forward_amount || 0) > 0) {
                costHtml = `
                    <span class="milestone-cost strikethrough">${this.formatCurrency(item.milestone_item_cost || 0)}</span>
                    <span class="milestone-cost adjusted">${this.formatCurrency(parseFloat(item.adjusted_cost) || 0)} <span class="cf-badge">+CF</span></span>`;
            } else {
                costHtml = `<span class="milestone-cost">${this.formatCurrency(item.milestone_item_cost || 0)}</span>`;
            }

            const side = isLeft ? 'milestone-left' : 'milestone-right';
            const contentSide = isLeft ? 'milestone-content-left' : 'milestone-content-right';

            html += `
                <div class="milestone-timeline-item ${side} ${isLocked ? 'locked' : ''}" data-item-id="${item.item_id}">
                    <div class="milestone-node-wrapper">
                        <div class="milestone-circle-wrapper ${ringClass}">
                            <div class="milestone-node ${isItemCompleted ? 'completed' : 'pending'}">
                                ${isItemCompleted
                                    ? '<i class="fi fi-rr-check" style="color:#fff; font-size:1.25rem;"></i>'
                                    : `<span class="milestone-progress-number">${displayPercentage}</span>`
                                }
                            </div>
                        </div>
                    </div>
                    <div class="milestone-content ${contentSide}">
                        <div class="milestone-number-label">
                            Milestone ${milestoneNumber}
                            ${isLocked ? '<i class="fi fi-rr-lock" style="font-size:12px; color:#94A3B8; margin-left:4px;"></i>' : ''}
                        </div>
                        <div class="milestone-title">${this.escapeHtml(item.milestone_item_title || '')}</div>
                        ${costHtml}
                        <div class="milestone-percentage">${itemPercentage.toFixed(2)}%</div>
                        ${tags}
                    </div>
                </div>`;
        });

        // Start point — clickable if downpayment mode
        html += `
            <div class="timeline-start ${isDP ? 'has-downpayment clickable' : ''}" ${isDP ? 'id="startPointDP" role="button" tabindex="0"' : ''}>
                <div class="start-node ${isDP ? 'start-node-dp' : ''}"></div>
                <div class="start-label">
                    <div class="start-text">Start</div>
                    ${isDP
                        ? `<div class="start-percentage start-dp-amount">
                               <i class="fi fi-rr-hand-holding-usd" style="font-size:11px;"></i>
                               ${this.formatCurrency(dpAmount)}
                           </div>
                           <div class="start-dp-hint">Tap to view downpayment</div>`
                        : `<div class="start-percentage">0%</div>`
                    }
                </div>
            </div>`;

        html += `</div></div>`; // end timeline

        // ── Project Completion Banner / Button ──
        if (this.isProjectCompleted) {
            html += `
                <div class="project-completed-banner">
                    <div class="project-completed-content">
                        <div class="project-completed-icon"><i class="fi fi-rr-check-circle"></i></div>
                        <div class="project-completed-text">
                            <span class="project-completed-title">🎉 Project Completed!</span>
                            <span class="project-completed-message">This project has been successfully completed. Feel free to review the milestones, progress reports, and payment history at any time.</span>
                        </div>
                    </div>
                </div>`;
        } else if (!isHalted && allDone) {
            html += `
                <div class="complete-project-section">
                    <button class="complete-project-button" id="completeProjectBtn">
                        <i class="fi fi-rr-check-circle"></i>
                        <span>Complete Project</span>
                        <i class="fi fi-rr-angle-small-right"></i>
                    </button>
                </div>`;
        }

        // ── Rejection Indicators (owner sees rejected milestones) ──
        if (!isHalted) {
            const rejectedMilestones = this.milestones.filter(m => m.setup_status === 'rejected' && m.setup_rej_reason);
            if (rejectedMilestones.length > 0) {
                html += `<div class="rejection-indicator-section">`;
                rejectedMilestones.forEach((rm, idx) => {
                    html += `
                        <div class="rejection-indicator-card">
                            <div class="rejection-indicator-header">
                                <div class="rejection-indicator-icon"><i class="fi fi-rr-exclamation" style="color:#EF4444;"></i></div>
                                <div class="rejection-indicator-title-container">
                                    <span class="rejection-indicator-title">Milestone ${idx + 1} - Changes Requested</span>
                                    <span class="rejection-indicator-timestamp">${this.escapeHtml(rm.milestone_name || '')}</span>
                                </div>
                                <button class="edit-rejection-btn" data-milestone-id="${rm.milestone_id}" title="Edit rejection reason">
                                    <i class="fi fi-rr-edit"></i>
                                </button>
                            </div>
                            <div class="rejection-reason-container">
                                <span class="rejection-reason-label">Your Feedback:</span>
                                <p class="rejection-reason-text">${this.escapeHtml(rm.setup_rej_reason)}</p>
                            </div>
                            <div class="rejection-status-badge">
                                <span class="rejection-status-dot"></span>
                                <span class="rejection-status-text">Awaiting Contractor Response</span>
                            </div>
                        </div>`;
                });
                html += `</div>`;
            }
        }

        // ── Payment History Button ──
        html += `
            <div class="payment-history-container">
                <button class="payment-history-btn" id="paymentHistoryBtn">
                    <i class="fi fi-rr-credit-card"></i>
                    View Payment History
                    <i class="fi fi-rr-angle-small-right"></i>
                </button>
            </div>`;

        // ── Action Buttons (Approve / Request Changes) ──
        if (!isHalted && submittedMilestone) {
            html += `
                <div class="milestone-action-buttons">
                    <button class="milestone-action-btn request-changes-btn" id="requestChangesBtn" data-milestone-id="${submittedMilestone.milestone_id}">
                        Request Changes
                    </button>
                    <button class="milestone-action-btn approve-btn" id="approveMilestoneBtn" data-milestone-id="${submittedMilestone.milestone_id}">
                        Approve Milestone
                    </button>
                </div>`;
        }

        container.innerHTML = html;

        // Bind events after rendering
        this.bindEvents();
    }

    getItemStatusTags(item) {
        const tags = [];
        const isItemCompleted = item.item_status === 'completed' || item.parentMilestoneStatus === 'completed';

        if (item.item_status === 'halt') tags.push({ label: 'Halted', cls: 'tag-error', icon: 'fi-rr-pause-circle' });
        if (item.latest_progress_status === 'rejected') tags.push({ label: 'Report Rejected', cls: 'tag-error', icon: 'fi-rr-cross-circle' });
        if (item.latest_payment_status === 'rejected') tags.push({ label: 'Payment Rejected', cls: 'tag-error', icon: 'fi-rr-cross-circle' });
        if (!isItemCompleted && item.latest_progress_status === 'submitted') tags.push({ label: 'New Report', cls: 'tag-info', icon: 'fi-rr-document' });
        if (!isItemCompleted && item.latest_payment_status === 'submitted') tags.push({ label: 'New Payment', cls: 'tag-info', icon: 'fi-rr-credit-card' });
        if (item.was_extended) tags.push({ label: 'Extended', cls: 'tag-warning', icon: 'fi-rr-time-past' });

        if (tags.length === 0) return '';

        return `<div class="milestone-status-tags">${tags.map(t =>
            `<span class="milestone-status-tag ${t.cls}"><i class="fi ${t.icon}"></i> ${t.label}</span>`
        ).join('')}</div>`;
    }

    // ── Event Binding ───────────────────────────────────────────────────────
    bindEvents() {
        // Milestone item clicks → navigate to progress report
        document.querySelectorAll('.milestone-timeline-item').forEach(el => {
            el.addEventListener('click', () => {
                const itemId = parseInt(el.getAttribute('data-item-id'));
                const item = this.allItems.find(i => i.item_id === itemId);
                if (item) this.handleMilestoneItemClick(item);
            });
        });

        // Start point click → open downpayment detail modal
        const startDP = document.getElementById('startPointDP');
        if (startDP) {
            startDP.addEventListener('click', () => this.openDownpaymentModal());
            startDP.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.openDownpaymentModal(); } });
        }

        // Payment history button
        const payBtn = document.getElementById('paymentHistoryBtn');
        if (payBtn) payBtn.addEventListener('click', () => this.handleViewPaymentHistory());

        // Approve milestone
        const approveBtn = document.getElementById('approveMilestoneBtn');
        if (approveBtn) {
            approveBtn.addEventListener('click', () => {
                const msId = parseInt(approveBtn.getAttribute('data-milestone-id'));
                this.openApprovalModal('approve', msId);
            });
        }

        // Request changes
        const requestBtn = document.getElementById('requestChangesBtn');
        if (requestBtn) {
            requestBtn.addEventListener('click', () => {
                const msId = parseInt(requestBtn.getAttribute('data-milestone-id'));
                this.openApprovalModal('reject', msId);
            });
        }

        // Complete project
        const completeBtn = document.getElementById('completeProjectBtn');
        if (completeBtn) {
            completeBtn.addEventListener('click', () => this.openCompleteProjectModal());
        }

        // Edit rejection reason buttons
        document.querySelectorAll('.edit-rejection-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const msId = parseInt(btn.getAttribute('data-milestone-id'));
                const ms = this.milestones.find(m => m.milestone_id === msId);
                if (ms) this.openApprovalModal('reject', msId, ms.setup_rej_reason || '');
            });
        });

        // Extension indicator toggle
        const extToggle = document.getElementById('extensionIndicatorToggle');
        if (extToggle) {
            extToggle.addEventListener('click', () => {
                const panel = document.getElementById('extensionHistoryPanel');
                const chevron = document.getElementById('extensionChevron');
                if (panel) {
                    const visible = panel.style.display !== 'none';
                    panel.style.display = visible ? 'none' : 'block';
                    if (chevron) chevron.classList.toggle('rotated', !visible);
                }
            });
        }

        // Project summary button
        const summaryBtn = document.getElementById('viewProjectSummaryBtn');
        if (summaryBtn) {
            summaryBtn.addEventListener('click', () => {
                const psmModal = document.getElementById('projectSummaryModal');
                if (psmModal) {
                    psmModal.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }
            });
        }

        // PSM close handlers
        const closePsmHandlers = () => {
            const psmModal = document.getElementById('projectSummaryModal');
            if (!psmModal) return;
            const hide = () => { psmModal.style.display = 'none'; document.body.style.overflow = ''; };
            const closeBtn = document.getElementById('closeProjectSummaryModal');
            const cancelBtn = document.getElementById('cancelProjectSummaryModal');
            if (closeBtn) closeBtn.addEventListener('click', hide);
            if (cancelBtn) cancelBtn.addEventListener('click', hide);
            psmModal.addEventListener('click', (e) => { if (e.target === psmModal) hide(); });
        };
        closePsmHandlers();

        // PSM collapsible sections
        this.initPsmAccordion();

        // PSM refresh
        const refreshBtn = document.getElementById('refreshProjectSummary');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshProjectSummary());
        }
    }

    // ── PSM Accordion ──
    initPsmAccordion() {
        document.querySelectorAll('[data-psm-toggle]').forEach(btn => {
            const key = btn.getAttribute('data-psm-toggle');
            const body = document.querySelector(`[data-psm-body="${key}"]`);
            if (!body) return;
            // Remove old listeners by cloning
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', () => {
                body.classList.toggle('psm-collapsed');
                const chevron = newBtn.querySelector('.psm-chevron');
                if (chevron) chevron.style.transform = body.classList.contains('psm-collapsed') ? 'rotate(-90deg)' : '';
            });
        });
    }

    // ── PSM Refresh ──
    async refreshProjectSummary() {
        const config = window.__milestoneReportConfig || {};
        const projectId = config.projectId;
        const userId = config.userId;
        if (!projectId) return;

        const btn = document.getElementById('refreshProjectSummary');
        const body = document.getElementById('projectSummaryModalBody');
        if (!body) return;

        // Spin icon
        if (btn) {
            btn.disabled = true;
            const icon = btn.querySelector('i');
            if (icon) icon.classList.add('psm-spin');
        }

        try {
            const url = `/api/projects/${projectId}/summary${userId ? '?user_id=' + userId : ''}`;
            const res = await fetch(url);
            const json = await res.json();
            const data = json.data || json;
            if (!data || !data.header) throw new Error('Invalid response');

            body.innerHTML = this.buildPsmHtml(data);

            // Update timestamp
            const tsEl = document.getElementById('psmGeneratedAt');
            if (tsEl && data.generated_at) {
                const d = new Date(data.generated_at);
                tsEl.textContent = 'Report generated ' + d.toLocaleDateString('en-US', {month:'2-digit',day:'2-digit',year:'numeric'}) + ' ' + d.toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit',hour12:true});
            }

            // Re-init accordion
            this.initPsmAccordion();
        } catch (e) {
            console.error('PSM refresh error:', e);
            this.showToast('Failed to refresh summary', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                const icon = btn.querySelector('i');
                if (icon) icon.classList.remove('psm-spin');
            }
        }
    }

    // ── Build PSM HTML from JSON ──
    buildPsmHtml(data) {
        const h = data.header || {};
        const o = data.overview || {};
        const milestones = data.milestones || [];
        const budgetHistory = data.budget_history || [];
        const changeHistory = data.change_history || [];
        const payments = data.payments || {records:[], total_approved:0, total_pending:0, total_rejected:0};
        const reports = data.progress_reports || [];

        const fmt = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', {minimumFractionDigits:0});
        const fmt2 = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', {minimumFractionDigits:2});
        const fmtDate = (d) => { if (!d) return '—'; const dt = new Date(d); return dt.toLocaleDateString('en-US', {year:'numeric', month:'short', day:'numeric'}); };
        const fmtDateTime = (d) => { if (!d) return ''; const dt = new Date(d); return dt.toLocaleDateString('en-US', {month:'2-digit',day:'2-digit',year:'numeric'}) + ' ' + dt.toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit',hour12:true}); };
        const sc = (s) => {
            const m = {completed:['#D1FAE5','#10B981'],approved:['#D1FAE5','#10B981'],pending:['#FEF3C7','#F59E0B'],submitted:['#FEF3C7','#F59E0B'],active:['#DBEAFE','#3B82F6'],in_progress:['#DBEAFE','#3B82F6'],rejected:['#FEE2E2','#EF4444'],revision_requested:['#FFF3E6','#EC7E00']};
            return m[(s||'').toLowerCase()] || ['#F1F5F9','#64748B'];
        };
        const badge = (s, extra='') => { const c=sc(s); return `<span class="psm-badge${extra?' '+extra:''}" style="background:${c[0]};color:${c[1]};">${(s||'').replace(/_/g,' ')}</span>`; };
        const esc = (s) => { const el = document.createElement('span'); el.textContent = s || ''; return el.innerHTML; };

        const progressPct = o.total_milestones > 0 ? Math.round((o.completed_milestones||0)/o.total_milestones*100) : 0;
        const budgetUtil = o.current_budget > 0 ? Math.round((o.total_paid||0)/o.current_budget*100) : 0;

        let html = '';

        // A. Header Card
        html += `<div class="psm-header-card">
            <h4 class="psm-header-title">${esc(h.project_title)}</h4>
            ${h.project_description ? `<p class="psm-header-desc">${esc(h.project_description)}</p>` : ''}
            <div class="flex items-center justify-between flex-wrap gap-2 mt-3">
                <div class="flex items-center gap-1.5">
                    <i class="fi fi-rr-marker text-xs text-gray-400"></i>
                    <span class="text-xs text-gray-500">${esc(h.project_location||'—')}</span>
                </div>
                ${badge((h.status||'').toUpperCase())}
            </div>
            <div class="psm-divider"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="psm-meta-label">PROPERTY OWNER</p>
                    <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(h.owner_name||'—')}</p>
                    ${h.owner_email?`<p class="text-xs text-gray-400 mt-0.5">${esc(h.owner_email)}</p>`:''}
                </div>
                <div>
                    <p class="psm-meta-label">CONTRACTOR</p>
                    <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(h.contractor_name||'—')}</p>
                    ${h.contractor_company?`<p class="text-xs text-gray-400 mt-0.5">${esc(h.contractor_company)}</p>`:''}
                </div>
            </div>
            <div class="psm-divider"></div>
            <div class="flex items-center gap-3 flex-wrap">
                <div><p class="psm-meta-label">START</p><p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${fmtDate(h.original_start_date)}</p></div>
                <i class="fi fi-rr-arrow-right text-xs text-gray-300"></i>
                <div><p class="psm-meta-label">${h.was_extended?'CURRENT END':'END'}</p><p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${fmtDate(h.current_end_date)}</p></div>
                ${h.was_extended && h.original_end_date !== h.current_end_date ? '<span class="psm-badge" style="background:#FEF3C7;color:#F59E0B;"><i class="fi fi-rr-clock" style="font-size:0.5rem;"></i> Extended</span>' : ''}
            </div>
        </div>`;

        // B. Executive Overview
        html += this.buildPsmSection('overview', 'fi-rr-chart-histogram', 'Executive Overview', false, `
            <div class="mb-3">
                <div class="flex justify-between mb-1">
                    <span class="text-xs font-semibold text-gray-700" style="font-family:ui-sans-serif,system-ui,sans-serif;">Milestone Progress</span>
                    <span class="text-xs font-bold text-gray-900">${progressPct}%</span>
                </div>
                <div class="psm-progress-track"><div class="psm-progress-fill" style="width:${progressPct}%;background:#10B981;"></div></div>
                <p class="text-[0.6875rem] text-gray-400 mt-1">${o.completed_milestones||0} of ${o.total_milestones||0} milestones completed</p>
            </div>
            <div class="mb-4">
                <div class="flex justify-between mb-1">
                    <span class="text-xs font-semibold text-gray-700" style="font-family:ui-sans-serif,system-ui,sans-serif;">Budget Utilization</span>
                    <span class="text-xs font-bold text-gray-900">${budgetUtil}%</span>
                </div>
                <div class="psm-progress-track"><div class="psm-progress-fill" style="width:${Math.min(budgetUtil,100)}%;background:${budgetUtil>100?'#EF4444':'#3B82F6'};"></div></div>
            </div>
            <div class="psm-fin-grid">
                <div class="psm-fin-cell"><span class="psm-fin-label">ORIGINAL BUDGET</span><span class="psm-fin-value">${fmt(o.original_budget)}</span></div>
                <div class="psm-fin-cell ${o.current_budget!==o.original_budget?'psm-fin-highlight':''}"><span class="psm-fin-label">CURRENT BUDGET</span><span class="psm-fin-value">${fmt(o.current_budget)}</span></div>
                <div class="psm-fin-cell"><span class="psm-fin-label">TOTAL PAID</span><span class="psm-fin-value" style="color:#10B981;">${fmt(o.total_paid)}</span></div>
                <div class="psm-fin-cell"><span class="psm-fin-label">PENDING</span><span class="psm-fin-value" style="color:#F59E0B;">${fmt(o.total_pending)}</span></div>
                <div class="psm-fin-cell"><span class="psm-fin-label">REMAINING</span><span class="psm-fin-value">${fmt(o.remaining_balance)}</span></div>
                <div class="psm-fin-cell"><span class="psm-fin-label">PAYMENT MODE</span><span class="psm-fin-value-text">${esc((o.payment_mode||'—').replace(/_/g,' '))}</span></div>
            </div>
        `);

        // C. Milestones
        html += this.buildPsmSection('milestones', 'fi-rr-layers', `Milestones (${milestones.length})`, false,
            milestones.map(m => {
                const mc = sc(m.status);
                return `<div class="psm-milestone-card">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="psm-milestone-seq">${m.sequence_order||''}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 leading-tight" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(m.title)}</p>
                            <p class="text-[0.6875rem] text-gray-400 mt-0.5">${esc(m.milestone_name)}</p>
                        </div>
                        <span class="psm-badge" style="background:${mc[0]};color:${mc[1]};">${(m.status||'').replace(/_/g,' ')}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mb-2">
                        <div><p class="psm-meta-label">BUDGET</p><p class="text-xs font-semibold text-gray-900">${fmt(m.current_allocation)}</p></div>
                        <div><p class="psm-meta-label">PAID</p><p class="text-xs font-semibold" style="color:#10B981;">${fmt(m.total_paid)}</p></div>
                        <div><p class="psm-meta-label">DUE</p><p class="text-xs font-semibold text-gray-900">${fmtDate(m.current_due_date)}</p></div>
                    </div>
                    ${m.was_extended?`<div class="flex items-center gap-1.5 mb-2"><i class="fi fi-rr-clock text-xs text-amber-500"></i><span class="text-[0.6875rem] text-amber-500 font-medium">Extended ${m.extension_count||0}× (was ${fmtDate(m.original_due_date)})</span></div>`:''}
                    <div class="psm-progress-track" style="height:4px;"><div class="psm-progress-fill" style="width:${m.percentage_progress||0}%;background:#3B82F6;"></div></div>
                </div>`;
            }).join('')
        );

        // D. Budget History
        if (budgetHistory.length > 0) {
            html += this.buildPsmSection('budget', 'fi-rr-arrow-trend-up', `Budget History (${budgetHistory.length})`, true,
                budgetHistory.map(bh => {
                    const bhc = sc(bh.status);
                    return `<div class="psm-history-row">
                        <div class="psm-history-dot"></div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${bh.change_type?'Budget '+bh.change_type.charAt(0).toUpperCase()+bh.change_type.slice(1):'Timeline Update'}</span>
                                <span class="psm-badge" style="background:${bhc[0]};color:${bhc[1]};">${bh.status||''}</span>
                            </div>
                            ${bh.previous_budget!=null&&bh.updated_budget!=null?`<p class="text-xs text-gray-500">${fmt(bh.previous_budget)} → ${fmt(bh.updated_budget)}</p>`:''}
                            ${bh.previous_end_date&&bh.proposed_end_date?`<p class="text-xs text-gray-500">${fmtDate(bh.previous_end_date)} → ${fmtDate(bh.proposed_end_date)}</p>`:''}
                            ${bh.reason?`<p class="text-[0.6875rem] text-gray-400 italic mt-1">"${esc(bh.reason)}"</p>`:''}
                            <p class="text-[0.625rem] text-gray-300 mt-1">${fmtDate(bh.date_proposed)}</p>
                        </div>
                    </div>`;
                }).join('')
            );
        }

        // E. Change Log
        if (changeHistory.length > 0) {
            html += this.buildPsmSection('changelog', 'fi-rr-time-past', `Change Log (${changeHistory.length})`, true,
                changeHistory.map(evt => `<div class="psm-history-row">
                    <div class="psm-history-dot" style="background:#3B82F6;"></div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(evt.action)}</p>
                        ${evt.performed_by?`<p class="text-xs text-gray-500 mt-0.5">by ${esc(evt.performed_by)}</p>`:''}
                        ${evt.notes?`<p class="text-[0.6875rem] text-gray-400 italic mt-1">"${esc(evt.notes)}"</p>`:''}
                        ${evt.reference?`<p class="text-[0.625rem] text-blue-500 mt-0.5">${esc(evt.reference)}</p>`:''}
                        <p class="text-[0.625rem] text-gray-300 mt-1">${fmtDateTime(evt.date)}</p>
                    </div>
                </div>`).join('')
            );
        }

        // F. Payments
        const recs = payments.records || [];
        html += this.buildPsmSection('payments', 'fi-rr-credit-card', `Payments (${recs.length})`, true, `
            <div class="grid grid-cols-3 gap-2 mb-3">
                <div class="psm-payment-pill" style="border-color:#10B981;"><span class="psm-meta-label" style="color:#10B981;">APPROVED</span><span class="text-sm font-bold" style="color:#10B981;">${fmt(payments.total_approved)}</span></div>
                <div class="psm-payment-pill" style="border-color:#F59E0B;"><span class="psm-meta-label" style="color:#F59E0B;">PENDING</span><span class="text-sm font-bold" style="color:#F59E0B;">${fmt(payments.total_pending)}</span></div>
                <div class="psm-payment-pill" style="border-color:#EF4444;"><span class="psm-meta-label" style="color:#EF4444;">REJECTED</span><span class="text-sm font-bold" style="color:#EF4444;">${fmt(payments.total_rejected)}</span></div>
            </div>
            ${recs.length===0?'<p class="text-xs text-gray-400 italic py-3">No payment records yet.</p>':
            recs.map(p => { const pc=sc(p.status); return `<div class="psm-payment-row">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(p.milestone)}</p>
                        <p class="text-[0.6875rem] text-gray-400 capitalize">${(p.payment_type||'').replace(/_/g,' ')}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-gray-900">${fmt2(p.amount)}</p>
                        <span class="psm-badge mt-0.5" style="background:${pc[0]};color:${pc[1]};">${p.status||''}</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    ${p.transaction_number?`<span class="text-[0.625rem] text-gray-400">Ref: ${esc(p.transaction_number)}</span>`:'<span></span>'}
                    <span class="text-[0.625rem] text-gray-300">${fmtDate(p.transaction_date)}</span>
                </div>
            </div>`; }).join('')}
        `);

        // G. Progress Reports
        if (reports.length > 0) {
            html += this.buildPsmSection('progress', 'fi-rr-document', `Progress Reports (${reports.length})`, true,
                reports.map(rp => { const rc=sc(rp.status); return `<div class="psm-report-row">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900" style="font-family:ui-sans-serif,system-ui,sans-serif;">${esc(rp.report_title||'Progress Report')}</p>
                        <p class="text-[0.6875rem] text-gray-400 mt-0.5">${esc(rp.milestone||'')}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="psm-badge" style="background:${rc[0]};color:${rc[1]};">${(rp.status||'').replace(/_/g,' ')}</span>
                        <p class="text-[0.625rem] text-gray-300 mt-1">${fmtDate(rp.submitted_at)}</p>
                    </div>
                </div>`; }).join('')
            );
        }

        // Timestamp
        html += `<p class="text-center text-[0.625rem] text-gray-300 mt-4 pb-2" id="psmGeneratedAt">Report generated ${fmtDateTime(data.generated_at)}</p>`;

        return html;
    }

    buildPsmSection(key, icon, title, collapsed, content) {
        return `<div class="psm-section" data-psm-section="${key}">
            <button class="psm-section-toggle" data-psm-toggle="${key}" type="button">
                <div class="flex items-center gap-2">
                    <i class="fi ${icon} text-sm" style="color:#EEA24B;"></i>
                    <span class="psm-section-title">${title}</span>
                </div>
                <i class="fi fi-rr-angle-small-down psm-chevron" style="${collapsed?'transform:rotate(-90deg)':''}"></i>
            </button>
            <div class="psm-section-body${collapsed?' psm-collapsed':''}" data-psm-body="${key}">${content}</div>
        </div>`;
    }

    // ── Modals ──────────────────────────────────────────────────────────────
    openApprovalModal(mode, milestoneId, existingReason = '') {
        const modal = document.getElementById('milestoneApprovalModal');
        const icon = document.getElementById('approvalModalIcon');
        const title = document.getElementById('approvalModalTitle');
        const subtitle = document.getElementById('approvalModalSubtitle');
        const reasonContainer = document.getElementById('approvalReasonContainer');
        const reasonInput = document.getElementById('approvalReasonInput');
        const confirmBtn = document.getElementById('approvalConfirmBtn');
        const cancelBtn = document.getElementById('approvalCancelBtn');
        const charCount = document.getElementById('approvalCharCount');

        if (!modal) return;

        if (mode === 'approve') {
            icon.innerHTML = '<i class="fi fi-rr-check-circle" style="font-size:24px; color:#10B981;"></i>';
            title.textContent = 'Approve Milestone Setup';
            subtitle.textContent = 'Are you sure you want to approve this milestone setup?';
            reasonContainer.style.display = 'none';
            confirmBtn.textContent = 'Approve';
            confirmBtn.className = 'milestone-approval-confirm-btn approve-mode';
        } else {
            icon.innerHTML = '<i class="fi fi-rr-exclamation" style="font-size:24px; color:#EF4444;"></i>';
            title.textContent = 'Request Changes to Milestone Setup';
            subtitle.textContent = 'Please explain what needs to be changed in this milestone setup. The contractor will review your feedback and make the necessary adjustments.';
            reasonContainer.style.display = 'block';
            reasonInput.value = existingReason;
            charCount.textContent = existingReason.length;
            confirmBtn.textContent = 'Send Request';
            confirmBtn.className = 'milestone-approval-confirm-btn reject-mode';
        }

        // Show modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Char count listener
        const charHandler = () => { charCount.textContent = reasonInput.value.length; };
        reasonInput.addEventListener('input', charHandler);

        // Confirm handler
        const confirmHandler = async () => {
            if (mode === 'reject') {
                const reason = reasonInput.value.trim();
                if (!reason) {
                    this.showToast('Please provide a reason for requesting changes', 'error');
                    return;
                }
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Sending...';
                await this.rejectMilestone(milestoneId, reason);
            } else {
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Approving...';
                await this.approveMilestone(milestoneId);
            }
            closeModal();
        };

        // Cancel handler
        const closeModal = () => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            confirmBtn.disabled = false;
            reasonInput.removeEventListener('input', charHandler);
            confirmBtn.removeEventListener('click', confirmHandler);
            cancelBtn.removeEventListener('click', closeModal);
            modal.querySelector('.milestone-approval-modal-overlay')?.removeEventListener('click', closeModal);
        };

        confirmBtn.addEventListener('click', confirmHandler);
        cancelBtn.addEventListener('click', closeModal);
        modal.querySelector('.milestone-approval-modal-overlay')?.addEventListener('click', closeModal);
    }

    openCompleteProjectModal() {
        const modal = document.getElementById('completeProjectModal');
        if (!modal) return;

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        const confirmBtn = document.getElementById('completeProjectConfirmBtn');
        const cancelBtn = document.getElementById('completeProjectCancelBtn');

        const closeModal = () => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
            confirmBtn.removeEventListener('click', confirmHandler);
            cancelBtn.removeEventListener('click', closeModal);
            modal.querySelector('.milestone-approval-modal-overlay')?.removeEventListener('click', closeModal);
        };

        const confirmHandler = async () => {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Completing...';
            await this.completeProject();
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Complete Project';
            closeModal();
        };

        confirmBtn.addEventListener('click', confirmHandler);
        cancelBtn.addEventListener('click', closeModal);
        modal.querySelector('.milestone-approval-modal-overlay')?.addEventListener('click', closeModal);
    }

    // ── Downpayment Modal ────────────────────────────────────────────────────
    async openDownpaymentModal() {
        const modal = document.getElementById('downpaymentDetailModal');
        if (!modal) return;

        const totalCost = this.paymentPlan?.total_project_cost || this.project?.accepted_bid?.proposed_cost || 0;
        const dpAmount = this.paymentPlan?.downpayment_amount || 0;
        const dpPercentage = totalCost > 0 ? ((dpAmount / totalCost) * 100).toFixed(1) : '0';

        // Show modal with loading
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        const body = document.getElementById('downpaymentModalBody');
        body.innerHTML = `
            <div class="dp-info-card">
                <div class="dp-info-header">
                    <div class="dp-info-icon"><i class="fi fi-rr-hand-holding-usd"></i></div>
                    <span class="dp-info-title">Downpayment Details</span>
                </div>
                <div class="dp-info-grid">
                    <div class="dp-info-row">
                        <span class="dp-info-label">Amount</span>
                        <span class="dp-info-value dp-info-value-accent">${this.formatCurrency(dpAmount)}</span>
                    </div>
                    <div class="dp-info-row">
                        <span class="dp-info-label">Percentage</span>
                        <span class="dp-info-value">${dpPercentage}%</span>
                    </div>
                    <div class="dp-info-row">
                        <span class="dp-info-label">Total Project Cost</span>
                        <span class="dp-info-value">${this.formatCurrency(totalCost)}</span>
                    </div>
                </div>
            </div>`;

        // Close handlers
        const closeModal = () => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        };
        modal.querySelector('.dp-modal-overlay')?.addEventListener('click', closeModal, { once: true });
        modal.querySelector('.dp-modal-close-btn')?.addEventListener('click', closeModal, { once: true });
    }

    // ── Menu Dropdown ───────────────────────────────────────────────────────
    setupMenuDropdown() {
        const btn = document.getElementById('milestoneReportMenuBtn');
        const dropdown = document.getElementById('milestoneReportMenuDropdown');
        if (!btn || !dropdown) return;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', () => { dropdown.style.display = 'none'; });

        document.getElementById('menuFileDispute')?.addEventListener('click', () => {
            dropdown.style.display = 'none';
            this.handleSendReport();
        });

        document.getElementById('menuDisputeHistory')?.addEventListener('click', () => {
            dropdown.style.display = 'none';
            this.handleReportHistory();
        });
    }

    setupRetryButton() {
        document.getElementById('milestoneRetryBtn')?.addEventListener('click', () => this.loadProjectData());
    }

    // ── UI State Helpers ────────────────────────────────────────────────────
    showLoading() {
        const loading = document.getElementById('milestoneLoadingState');
        const error = document.getElementById('milestoneErrorState');
        const content = document.getElementById('milestoneContentArea');
        if (loading) loading.style.display = 'block';
        if (error) error.style.display = 'none';
        if (content) content.style.display = 'none';
    }

    hideLoading() {
        const loading = document.getElementById('milestoneLoadingState');
        if (loading) loading.style.display = 'none';
    }

    showError(message) {
        const loading = document.getElementById('milestoneLoadingState');
        const error = document.getElementById('milestoneErrorState');
        const content = document.getElementById('milestoneContentArea');
        const errMsg = document.getElementById('milestoneErrorMessage');
        if (loading) loading.style.display = 'none';
        if (content) content.style.display = 'none';
        if (error) error.style.display = 'block';
        if (errMsg) errMsg.textContent = message;
    }

    // ── Formatting Utilities ────────────────────────────────────────────────
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', minimumFractionDigits: 2 }).format(amount || 0);
    }

    formatDate(dateString) {
        if (!dateString) return '';
        try {
            const str = String(dateString).trim();
            let date;
            // Handle YYYY-MM-DD (no time component — append T00:00:00 to avoid UTC shift)
            if (/^\d{4}-\d{2}-\d{2}$/.test(str)) {
                date = new Date(str + 'T00:00:00');
            } else {
                date = new Date(str);
            }
            if (isNaN(date.getTime())) return str; // fallback: show raw value
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        } catch (_) {
            return String(dateString);
        }
    }

    formatDateTime(dateString) {
        if (!dateString) return { date: '', time: '' };
        try {
            const date = new Date(String(dateString));
            if (isNaN(date.getTime())) return { date: String(dateString), time: '' };
            return {
                date: date.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' }),
                time: date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }),
            };
        } catch (_) {
            return { date: String(dateString), time: '' };
        }
    }

    getPaymentTypeLabel(type) {
        if (!type) return 'Payment';
        return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    showToast(message, type = 'info') {
        const colors = { success: '#10B981', error: '#EF4444', warning: '#F59E0B', info: '#3B82F6' };
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            padding: 14px 24px; border-radius: 12px; color: #fff; font-size: 14px; font-weight: 500;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15); max-width: 400px;
            background: ${colors[type] || colors.info};
            animation: slideUp 0.3s ease-out;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => { if (document.body.contains(toast)) document.body.removeChild(toast); }, 300);
        }, 3000);
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new PropertyOwnerMilestoneReport();
});
