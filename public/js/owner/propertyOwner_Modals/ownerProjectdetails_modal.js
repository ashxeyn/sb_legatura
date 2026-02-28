/**
 * Project Details Modal — web-first redesign
 *
 * Key improvements vs previous version:
 *  • Expand/collapse via max-height CSS transition (.is-open class)
 *  • Ripple effect on interactive cards
 *  • Updated class names to match new blade/CSS (pdm-*)
 *  • All IDs unchanged — JS ↔ blade contract preserved
 */

class ProjectDetailsModal {

    constructor() {
        this.modal                       = null;
        this.overlay                     = null;
        this.currentProject              = null;
        this.currentBids                 = [];
        this.currentBid                  = null;
        this.pendingRejectBidId          = null;
        this.pendingProjectId            = null;
        this._currentBidsPanelProjectId  = null;
        this.init();
    }

    /* ── Bootstrap ───────────────────────────────────────────── */

    init() {
        this.modal   = document.getElementById('projectDetailsModal');
        this.overlay = document.getElementById('projectModalOverlay');
        if (!this.modal) return;
        this._bindEvents();
    }

    _bindEvents() {
        // Close triggers
        const closeBtn = document.getElementById('closeProjectModalBtn');
        if (closeBtn)   closeBtn.addEventListener('click',  () => this.close());
        if (this.overlay) this.overlay.addEventListener('click', () => this.close());

        // Collapse / expand hero card — button only (not whole card, web UX)
        const toggleBtn  = document.getElementById('summaryToggleBtn');
        if (toggleBtn) toggleBtn.addEventListener('click', (e) => { e.stopPropagation(); this.toggleSummary(); });

        // Allow clicking on the hero title/top area to also expand
        const heroCard = document.getElementById('summaryCard');
        if (heroCard) {
            heroCard.addEventListener('click', (e) => {
                // Only toggle if clicking outside the expanded text area
                const expanded = document.getElementById('summaryExpanded');
                if (expanded && expanded.classList.contains('is-open')) {
                    if (expanded.contains(e.target)) return; // don't collapse when selecting text
                }
                this.toggleSummary();
            });
        }

        // Milestone action card
        const milestoneCard = document.getElementById('milestoneActionCard');
        if (milestoneCard) {
            milestoneCard.addEventListener('click', async (e) => {
                this._ripple(milestoneCard, e);
                if (!this.currentProject) return;
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const res  = await fetch('/owner/projects/set-milestone', {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body:    JSON.stringify({ project_id: this.currentProject.id }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (data.success) window.location.href = '/owner/projects/milestone-report';
                } catch (err) { console.error('Milestone nav error:', err); }
            });
            milestoneCard.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); milestoneCard.click(); }
            });
        }

        // Bids row card
        const bidsCard = document.getElementById('bidsRowCard');
        if (bidsCard) {
            bidsCard.addEventListener('click', (e) => {
                this._ripple(bidsCard, e);
                this._openBidsPanel();
            });
            bidsCard.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); bidsCard.click(); }
            });
        }

        // Bids panel back button
        const bidsPanelBackBtn = document.getElementById('bidsPanelBackBtn');
        if (bidsPanelBackBtn) {
            bidsPanelBackBtn.addEventListener('click', () => this._closeBidsPanel());
        }

        // Bid detail panel back
        const bidDetailBackBtn = document.getElementById('bidDetailBackBtn');
        if (bidDetailBackBtn) bidDetailBackBtn.addEventListener('click', () => this._closeBidDetail());

        // Reject modal buttons
        const rejectCancelBtn  = document.getElementById('rejectCancelBtn');
        const rejectConfirmBtn = document.getElementById('rejectConfirmBtn');
        if (rejectCancelBtn)  rejectCancelBtn.addEventListener('click',  () => this._closeRejectModal());
        if (rejectConfirmBtn) rejectConfirmBtn.addEventListener('click', () => this._confirmReject());

        // Accept modal buttons
        const acceptCancelBtn  = document.getElementById('acceptCancelBtn');
        const acceptConfirmBtn = document.getElementById('acceptConfirmBtn');
        if (acceptCancelBtn)  acceptCancelBtn.addEventListener('click',  () => this._closeAcceptModal());
        if (acceptConfirmBtn) acceptConfirmBtn.addEventListener('click', () => this._confirmAccept());

        // Bid detail accept / decline
        const bdAcceptBtn  = document.getElementById('bdAcceptBtn');
        const bdDeclineBtn = document.getElementById('bdDeclineBtn');
        if (bdAcceptBtn) {
            bdAcceptBtn.addEventListener('click', () => {
                if (!this.currentBid || !this._currentBidsPanelProjectId) return;
                this._closeBidDetail();
                this._openAcceptModal(this.currentBid, this._currentBidsPanelProjectId);
            });
        }
        if (bdDeclineBtn) {
            bdDeclineBtn.addEventListener('click', () => {
                if (!this.currentBid || !this._currentBidsPanelProjectId) return;
                this._closeBidDetail();
                this._openRejectModal(this.currentBid, this._currentBidsPanelProjectId);
            });
        }

        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) this.close();
        });
    }

    /* ── Expand / Collapse ───────────────────────────────────── */

    toggleSummary() {
        const expanded  = document.getElementById('summaryExpanded');
        const toggleBtn = document.getElementById('summaryToggleBtn');
        if (!expanded) return;

        const nowOpen = !expanded.classList.contains('is-open');
        expanded.classList.toggle('is-open', nowOpen);
        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', String(nowOpen));
    }

    /* ── Open / Close ────────────────────────────────────────── */

    open(project) {
        if (!project) return;
        this.currentProject = project;
        this._populate(project);
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            // Focus the dialog for keyboard users
            const dialog = this.modal.querySelector('.pdm-dialog');
            if (dialog) { dialog.setAttribute('tabindex', '-1'); dialog.focus({ preventScroll: true }); }
        }
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = '';
        }
        // Reset expand state for next open
        const expanded  = document.getElementById('summaryExpanded');
        if (expanded) expanded.classList.remove('is-open');
        const toggleBtn = document.getElementById('summaryToggleBtn');
        if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
        // Reset bids panel
        this._closeBidsPanel();
        this.currentProject = null;
    }

    isOpen() {
        return this.modal?.classList.contains('active') ?? false;
    }

    /* ── Ripple effect ───────────────────────────────────────── */

    _ripple(el, evt) {
        const rect   = el.getBoundingClientRect();
        const size   = Math.max(rect.width, rect.height);
        const x      = (evt.clientX - rect.left) - size / 2;
        const y      = (evt.clientY - rect.top)  - size / 2;
        const ripple = document.createElement('span');
        ripple.className = 'pdm-ripple';
        ripple.style.cssText = `width:${size}px;height:${size}px;left:${x}px;top:${y}px;`;
        el.appendChild(ripple);
        ripple.addEventListener('animationend', () => ripple.remove());
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    _formatCurrency(amount) {
        const n = Number(amount) || 0;
        if (n >= 1_000_000) return '₱' + (n / 1_000_000).toFixed(n % 1_000_000 === 0 ? 0 : 1) + 'M';
        if (n >= 1_000)     return '₱' + (n / 1_000).toFixed(n % 1_000 === 0 ? 0 : 1) + 'K';
        return '₱' + n.toLocaleString('en-PH');
    }

    _formatCurrencyFull(amount) {
        const n = Number(amount) || 0;
        return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    _formatDate(ds) {
        if (!ds) return '';
        return new Date(ds).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    _daysRemaining(ds) {
        return Math.ceil((new Date(ds).getTime() - Date.now()) / 86_400_000);
    }

    _setEl(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text ?? '';
    }
    _show(id) { const el = document.getElementById(id); if (el) el.style.display = ''; }
    _hide(id) { const el = document.getElementById(id); if (el) el.style.display = 'none'; }

    /* ── Status & milestone config ───────────────────────────── */

    _statusConfig(ps, pps) {
        if (pps === 'under_review')           return { label: 'Under Review',                icon: 'fi fi-rr-clock',        color: '#F59E0B' };
        if (pps === 'rejected')               return { label: 'Rejected',                    icon: 'fi fi-rr-cross-circle', color: '#EF4444' };
        if (ps  === 'open')                   return { label: 'Open for Bidding',             icon: 'fi fi-rr-check-circle', color: '#10B981' };
        if (ps  === 'bidding_closed')         return { label: 'Bidding Closed',               icon: 'fi fi-rr-lock',         color: '#3B82F6' };
        if (ps  === 'waiting_milestone_setup') return { label: 'Waiting for Milestone Setup', icon: 'fi fi-rr-alarm-clock',  color: '#F59E0B' };
        if (ps  === 'in_progress')            return { label: 'In Progress',                  icon: 'fi fi-rr-hammer',       color: '#3B82F6' };
        if (ps  === 'completed')              return { label: 'Completed',                    icon: 'fi fi-rr-check-circle', color: '#10B981' };
        return { label: ps || 'Pending', icon: 'fi fi-rr-circle', color: '#94A3B8' };
    }

    _milestoneCardConfig(milestones) {
        const list        = Array.isArray(milestones) ? milestones : [];
        const hasApproved = list.some(m => (m.setup_status || m.milestone_status) === 'approved');
        const hasPending  = list.some(m => (m.setup_status || m.milestone_status) === 'submitted');
        if (hasApproved) return {
            title: 'Check Project Progress',
            desc:  'Track milestone completion, review progress reports, and monitor payment history.',
            label: 'View Progress',
            icon:  'fi-rr-check-circle',
            color: '#10B981',
        };
        if (hasPending) return {
            title: 'Review Milestone Setup',
            desc:  'The contractor has submitted a milestone proposal. Review and approve the breakdown.',
            label: 'Review & Approve',
            icon:  'fi-rr-clock',
            color: '#F59E0B',
        };
        return {
            title: 'Milestone Setup Pending',
            desc:  'The milestone timeline and payment breakdown are being prepared by the contractor.',
            label: 'View Details',
            icon:  'fi-rr-briefcase',
            color: '#3B82F6',
        };
    }

    /* ── Main populate ───────────────────────────────────────── */

    _populate(p) {
        // ── Reset all conditional elements before every populate ──
        // Prevents state from a previous project leaking into the next one.
        [
            'daysPill',
            'expDeadlineWrap',
            'expContractorSection',
            'agreedRow',
            'ctExpBadge',
            'statusBanner',
            'milestoneActionCard',
        ].forEach(id => this._hide(id));

        // Clear banner inner styles so colours don't bleed across projects
        const bannerIn = document.getElementById('bannerInner');
        if (bannerIn) bannerIn.removeAttribute('style');

        const ps  = p.project_status      || p.status || '';
        const pps = p.project_post_status || '';
        const raw = p._raw || p;

        /* ── Hero title / location ── */
        this._setEl('modalTypePill',    p.type || raw.type_name || '—');
        this._setEl('modalProjectTitle', p.title || raw.project_title || 'Untitled');
        this._setEl('modalLocationText', p.location || raw.project_location || '—');

        /* ── Status pill ── */
        const sc         = this._statusConfig(ps, pps);
        const statusPill = document.getElementById('statusPill');
        if (statusPill) statusPill.innerHTML = `<i class="${sc.icon}"></i> ${sc.label}`;

        /* ── Days pill ── */
        const deadline = p.bidding_due || raw.bidding_due || raw.bidding_deadline || p.bidding_deadline;
        const daysPill = document.getElementById('daysPill');
        if (deadline && daysPill) {
            const d = this._daysRemaining(deadline);
            if (d > 0) {
                daysPill.style.display = '';
                daysPill.innerHTML = `<i class="fi fi-rr-clock"></i> ${d}d left`;
            }
        }

        /* ── Expanded: description ── */
        this._setEl('expDescription', p.description || raw.project_description || '—');

        /* ── Expanded: budget ── */
        const bMin = raw.budget_range_min ?? p.budget_range_min;
        const bMax = raw.budget_range_max ?? p.budget_range_max;
        this._setEl('expBudgetMin', this._formatCurrencyFull(bMin));
        this._setEl('expBudgetMax', this._formatCurrencyFull(bMax));

        /* ── Expanded: spec grid ── */
        const specGrid = document.getElementById('expSpecGrid');
        if (specGrid) {
            const specs = [
                { label: 'Property Type', value: raw.property_type  || p.property_type },
                { label: 'Category',      value: raw.type_name      || p.type },
                { label: 'Lot Size',      value: (raw.lot_size   || p.lotSize)   ? `${raw.lot_size  || ''} sqm`.trim() : null },
                { label: 'Floor Area',    value: (raw.floor_area || p.floorArea) ? `${raw.floor_area || ''} sqm`.trim() : null },
            ].filter(s => s.value);
            specGrid.innerHTML = specs.map(s => `
                <div class="pdm-spec-cell">
                    <div class="pdm-spec-lbl">${s.label}</div>
                    <div class="pdm-spec-val">${s.value}</div>
                </div>`).join('');
        }

        /* ── Expanded: deadline ── */
        if (deadline) {
            this._show('expDeadlineWrap');
            this._setEl('expDeadline', this._formatDate(deadline));
        }

        /* ── Expanded: posted on ── */
        this._setEl('expPostedOn', this._formatDate(raw.created_at || p.created_at || p.rawDate));

        /* ── Expanded: contractor ── */
        const ci  = raw.contractor_info || (p.contractor?.name !== 'No contractor yet' ? p.contractor : null);
        const hasContractor = !!(raw.selected_contractor_id || (ci?.company_name));
        const ctSection = document.getElementById('expContractorSection');
        if (hasContractor && ci && ctSection) {
            ctSection.style.display = '';
            const name    = ci.company_name || ci.name || '—';
            const initials = name.slice(0, 2).toUpperCase();
            document.getElementById('ctAvatarPh').innerHTML =
                initials ? `<span style="font-weight:800;font-size:.85rem;color:#fff">${initials}</span>`
                         : '<i class="fi fi-rr-user"></i>';
            this._setEl('ctName', name);
            this._setEl('ctUser', ci.username ? `@${ci.username}` : '');
            const expBadge = document.getElementById('ctExpBadge');
            if (ci.years_of_experience != null && expBadge) {
                expBadge.style.display = '';
                expBadge.textContent = `${ci.years_of_experience} yrs exp`;
            }
            // Agreed bid
            const ab = raw.accepted_bid;
            const agreedRow = document.getElementById('agreedRow');
            if (ab && agreedRow) {
                agreedRow.style.display = '';
                this._setEl('agreedCost',     this._formatCurrencyFull(ab.proposed_cost));
                this._setEl('agreedTimeline', ab.estimated_timeline ? `${ab.estimated_timeline} months` : '—');
            }
        }

        /* ── Status banner ── */
        const milestones = raw.milestones || p.milestones || [];
        const pending    = milestones.filter(m => (m.setup_status || m.milestone_status) === 'submitted');
        const banner     = document.getElementById('statusBanner');

        let bColor = null, bBg = null, bIcon = null, bTitle = null, bMsg = null;

        if (pps === 'under_review') {
            bColor = '#F59E0B'; bBg = '#FFFBEB';
            bIcon  = 'fi-rr-info';
            bTitle = 'Under Review';
            bMsg   = 'Your project is currently under review. You will be notified once it\'s approved.';
        } else if (ps === 'waiting_milestone_setup') {
            bColor = '#F59E0B'; bBg = '#FFFBEB';
            bIcon  = 'fi-rr-alarm-clock';
            bTitle = 'Waiting for Milestone Setup';
            bMsg   = 'The contractor is preparing the milestone proposal for this project.';
        } else if (pending.length > 0) {
            bColor = '#3B82F6'; bBg = '#EFF6FF';
            bIcon  = 'fi-rr-bell';
            bTitle = 'Action Required';
            bMsg   = `${pending.length} milestone${pending.length > 1 ? 's' : ''} waiting for your approval.`;
        }

        if (bColor && banner && bannerIn) {
            banner.style.display = '';
            bannerIn.style.cssText = `
                border-left-color:${bColor};
                background:${bBg};
                border-left-width:4px;
                border-left-style:solid;
            `;
            const iconEl  = document.getElementById('bannerIcon');
            const titleEl = document.getElementById('bannerTitle');
            if (iconEl)  { iconEl.className = `fi ${bIcon} pdm-alert-icon`; iconEl.style.color = bColor; }
            if (titleEl) titleEl.style.color = bColor;
            // Update alert icon wrap color
            const wrap = iconEl?.parentElement;
            if (wrap) wrap.style.background = bColor + '1A';
            this._setEl('bannerTitle', bTitle);
            this._setEl('bannerMsg',   bMsg);
        }

        /* ── Milestone action card ── */
        const milestoneCard = document.getElementById('milestoneActionCard');
        if (hasContractor && milestoneCard) {
            milestoneCard.style.display = '';
            const mc       = this._milestoneCardConfig(milestones);
            const iconWrap = document.getElementById('milestoneIconWrap');
            const iconEl   = document.getElementById('milestoneActionIcon');
            const chip     = document.getElementById('milestoneChip');
            if (iconWrap) iconWrap.style.background = mc.color + '18';
            if (iconEl)   { iconEl.className = `fi ${mc.icon} pdm-card-ico`; iconEl.style.color = mc.color; }
            if (chip)     { chip.style.background = mc.color + '18'; chip.style.color = mc.color; }
            this._setEl('milestoneCardTitle', mc.title);
            this._setEl('milestoneCardDesc',  mc.desc);
            this._setEl('milestoneChipLabel', mc.label);
        }

        /* ── Bids count ── */
        const bidsCount = raw.bids_count ?? p.bids_count ?? 0;
        this._setEl('bidsCountLabel', `${bidsCount} ${bidsCount === 1 ? 'bid' : 'bids'} submitted`);
    }

    /* ── Bids Panel ──────────────────────────────────────────── */

    _openBidsPanel() {
        const panel = document.getElementById('bidsPanel');
        if (!panel || !this.currentProject) return;

        const raw       = this.currentProject._raw || this.currentProject;
        const projectId = raw.project_id || this.currentProject.id;
        const title     = raw.project_title || this.currentProject.title || 'Project';
        const subtitle  = document.getElementById('bidsPanelSubtitle');
        if (subtitle) subtitle.textContent = title;

        this._currentBidsPanelProjectId = projectId;
        panel.classList.add('is-open');
        this._loadBids(projectId);
    }

    async _loadBids(projectId) {
        const list    = document.getElementById('bidsPanelList');
        const loading = document.getElementById('bidsPanelLoading');
        const empty   = document.getElementById('bidsPanelEmpty');
        const badge   = document.getElementById('bidsPanelBadge');

        if (list)    list.innerHTML        = '';
        if (loading) loading.style.display = '';
        if (empty)   empty.style.display   = 'none';
        if (badge)   badge.textContent     = '';

        try {
            const res  = await fetch(`/owner/projects/${projectId}/bids`, {
                headers: { 'Accept': 'text/html, */*' }
            });
            const html = await res.text();
            if (loading) loading.style.display = 'none';

            if (!res.ok) {
                if (list) list.innerHTML = html;
                return;
            }

            if (list) list.innerHTML = html;

            // Count injected rows for the badge
            const rows = list ? list.querySelectorAll('.pdm-bid-row') : [];
            if (badge) badge.textContent = `${rows.length} Bid${rows.length !== 1 ? 's' : ''} Received`;

            if (rows.length === 0) {
                if (empty) empty.style.display = '';
                return;
            }

            // Wire card click → detail panel (skip action button area)
            rows.forEach(row => {
                const bidData = this._parseBidAttr(row);
                row.addEventListener('click', (e) => {
                    if (e.target.closest('.pdm-bdr-actions')) return;
                    if (bidData) this._openBidDetail(bidData);
                });
            });

            // Wire accept buttons
            list.querySelectorAll('.pdm-bdr-accept-btn[data-bid-id]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const row = btn.closest('.pdm-bid-row');
                    const bid = row ? this._parseBidAttr(row) : null;
                    if (bid) this._openAcceptModal(bid, projectId, btn);
                });
            });

            // Wire reject buttons
            list.querySelectorAll('.pdm-bdr-reject-btn[data-bid-id]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const row = btn.closest('.pdm-bid-row');
                    const bid = row ? this._parseBidAttr(row) : null;
                    if (bid) this._openRejectModal(bid, projectId);
                });
            });

        } catch (err) {
            if (loading) loading.style.display = 'none';
            if (list) list.innerHTML = '<p style="text-align:center;color:var(--pdm-danger);padding:32px 16px">Failed to load bids. Please try again.</p>';
        }
    }

    /** Safely parse the data-bid JSON attribute from a row element. */
    _parseBidAttr(row) {
        try { return row.dataset.bid ? JSON.parse(row.dataset.bid) : null; }
        catch { return null; }
    }

    _closeBidsPanel() {
        this._closeBidDetail();
        document.getElementById('bidsPanel')?.classList.remove('is-open');
    }

    /** Safely parse the data-bid JSON attribute from a row element. */
    _parseBidAttr(row) {
        try { return row.dataset.bid ? JSON.parse(row.dataset.bid) : null; }
        catch { return null; }
    }

    async _acceptBid(bidId, projectId, btn) {
        if (btn && btn.classList.contains('is-loading')) return;
        let orig = '';
        if (btn) {
            btn.classList.add('is-loading');
            btn.disabled  = true;
            orig          = btn.innerHTML;
            btn.innerHTML = '<span class="pdm-bids-spinner" style="width:18px;height:18px;border-width:2px;margin:0 auto;"></span>';
        }

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res  = await fetch(`/owner/projects/${projectId}/bids/${bidId}/accept`, {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed');

            // Refresh the bids list to reflect accepted/rejected states
            await this._loadBids(projectId);
        } catch (err) {
            if (btn) {
                btn.classList.remove('is-loading');
                btn.disabled  = false;
                btn.innerHTML = orig;
            }
            alert(err.message || 'Something went wrong. Please try again.');
        }
    }

    /* ── Bid Detail Panel ─────────────────────────────────────── */

    _openBidDetail(bid) {
        this.currentBid = bid;
        this._populateBidDetail(bid);
        document.getElementById('bidDetailPanel')?.classList.add('is-open');
    }

    _closeBidDetail() {
        this.currentBid = null;
        document.getElementById('bidDetailPanel')?.classList.remove('is-open');
    }

    _populateBidDetail(bid) {
        const status   = bid.bid_status || 'submitted';
        const name     = (bid.company_name || bid.username || 'Contractor').trim();
        const initials = name.slice(0, 2).toUpperCase();

        // Status pill
        const pill = document.getElementById('bdStatusPill');
        if (pill) {
            pill.className   = `pdm-bd-status-pill status-${status}`;
            pill.textContent = status.replace('_', ' ');
        }

        // Avatar
        const avatarWrap = document.getElementById('bdAvatarWrap');
        const avatar     = document.getElementById('bdAvatar');
        if (avatarWrap) {
            avatarWrap.querySelectorAll('.pdm-bd-avatar-initials').forEach(el => el.remove());
            const imgSrc = bid.profile_pic ? `/storage/${bid.profile_pic}` : '/img/defaults/contractor_default.png';
            if (avatar) {
                avatar.src = imgSrc;
                avatar.style.display = '';
                avatar.onerror = function() { this.onerror = null; this.src = '/img/defaults/contractor_default.png'; };
            }
        }
        this._setText('bdCompanyName', name);
        this._setText('bdUsername', bid.username ? `@${bid.username}` : '');

        // Pricing
        const tlNum = bid.estimated_timeline;
        this._setText('bdCost',     this._formatCurrencyFull(bid.proposed_cost));
        this._setText('bdTimeline', tlNum ? `${tlNum} ${tlNum == 1 ? 'month' : 'months'} timeline` : '—');

        // Stats
        this._setText('bdYearsExp',         bid.years_of_experience ?? '—');
        this._setText('bdCompletedProjects', bid.completed_projects ?? '—');

        const picabDiv  = document.getElementById('bdPicabDiv');
        const picabItem = document.getElementById('bdPicabItem');
        const picabWrap = document.getElementById('bdPicab');
        if (bid.picab_category) {
            if (picabItem) picabItem.textContent = bid.picab_category;
            [picabDiv, picabWrap].forEach(el => el && (el.style.display = ''));
        } else {
            [picabDiv, picabWrap].forEach(el => el && (el.style.display = 'none'));
        }

        const typeDiv  = document.getElementById('bdTypeDiv');
        const typeItem = document.getElementById('bdTypeItem');
        const typeWrap = document.getElementById('bdType');
        if (bid.contractor_type) {
            if (typeItem) typeItem.textContent = bid.contractor_type;
            [typeDiv, typeWrap].forEach(el => el && (el.style.display = ''));
        } else {
            [typeDiv, typeWrap].forEach(el => el && (el.style.display = 'none'));
        }

        // Info table
        const submDate = bid.submitted_at
            ? new Date(bid.submitted_at).toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' })
            : '—';
        this._setText('bdSubmittedOn', submDate);
        this._setText('bdCost2',       this._formatCurrencyFull(bid.proposed_cost));
        this._setText('bdTimeline2',   tlNum ? `${tlNum} ${tlNum == 1 ? 'month' : 'months'}` : '—');
        const statusLabel = status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        const statusValEl = document.getElementById('bdStatusVal');
        if (statusValEl) {
            statusValEl.textContent = statusLabel;
            statusValEl.className   = 'pdm-bd-row-val' + (status === 'accepted' ? ' pdm-bd-row-highlight' : '');
        }

        // Notes
        const notesSection = document.getElementById('bdNotesSection');
        const notesEl      = document.getElementById('bdNotes');
        if (bid.contractor_notes && bid.contractor_notes.trim()) {
            if (notesEl)     notesEl.textContent    = bid.contractor_notes.trim();
            if (notesSection) notesSection.style.display = '';
        } else {
            if (notesSection) notesSection.style.display = 'none';
        }

        // Contact buttons
        const contactSection = document.getElementById('bdContactSection');
        const contactBtns    = document.getElementById('bdContactBtns');
        const contactLinks   = [];
        if (bid.company_email)   contactLinks.push(`<a class="pdm-bd-contact-btn" href="mailto:${bid.company_email}"><i class="fi fi-rr-envelope"></i>Email</a>`);
        if (bid.company_phone)   contactLinks.push(`<a class="pdm-bd-contact-btn" href="tel:${bid.company_phone}"><i class="fi fi-rr-phone-call"></i>Call</a>`);
        if (bid.company_website) contactLinks.push(`<a class="pdm-bd-contact-btn" href="${bid.company_website}" target="_blank" rel="noopener"><i class="fi fi-rr-globe"></i>Website</a>`);
        if (contactLinks.length) {
            if (contactBtns)    contactBtns.innerHTML        = contactLinks.join('');
            if (contactSection) contactSection.style.display = '';
        } else {
            if (contactSection) contactSection.style.display = 'none';
        }

        // Files
        const filesList  = document.getElementById('bdFilesList');
        const fileCount  = document.getElementById('bdFileCount');
        const files      = bid.files || [];
        if (fileCount)  fileCount.textContent = files.length ? `${files.length}` : '';
        if (filesList) {
            if (files.length) {
                filesList.innerHTML = files.map(f => `
                    <div class="pdm-bd-file-row" onclick="window.open('/storage/${f.file_path}','_blank')">
                        <i class="fi fi-rr-file pdm-bd-file-ico"></i>
                        <span class="pdm-bd-file-name">${f.file_name || f.file_path?.split('/').pop() || 'File'}</span>
                        <i class="fi fi-rr-download" style="color:#94a3b8;font-size:.75rem;"></i>
                    </div>`).join('');
            } else {
                filesList.innerHTML = '<div class="pdm-bd-no-files"><i class="fi fi-rr-box-open"></i>No attachments uploaded.</div>';
            }
        }

        // Action buttons
        const bdActions = document.getElementById('bdActions');
        if (bdActions) bdActions.style.display = (status === 'submitted' || status === 'under_review') ? '' : 'none';
    }

    _setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value ?? '';
    }

    /* ── Accept Modal ────────────────────────────────────────── */

    _openAcceptModal(bid, projectId, btn = null) {
        this.pendingAcceptBidId  = bid.bid_id;
        this.pendingAcceptProjectId = projectId;
        this._pendingAcceptBtn  = btn;
        const ref = document.getElementById('acceptBidRef');
        if (ref) ref.textContent = bid.company_name || bid.username || 'this contractor';
        const overlay = document.getElementById('acceptOverlay');
        if (overlay) overlay.style.display = '';
    }

    _closeAcceptModal() {
        const overlay = document.getElementById('acceptOverlay');
        if (overlay) overlay.style.display = 'none';
        this.pendingAcceptBidId     = null;
        this.pendingAcceptProjectId = null;
        this._pendingAcceptBtn      = null;
    }

    _confirmAccept() {
        const bidId     = this.pendingAcceptBidId;
        const projectId = this.pendingAcceptProjectId;
        const btn       = this._pendingAcceptBtn;
        if (!bidId || !projectId) { this._closeAcceptModal(); return; }
        this._closeAcceptModal();
        this._acceptBid(bidId, projectId, btn);
    }

    /* ── Reject Modal ─────────────────────────────────────────── */

    _openRejectModal(bid, projectId) {
        this.pendingRejectBidId = bid.bid_id;
        this.pendingProjectId   = projectId;
        const ref = document.getElementById('rejectBidRef');
        if (ref) ref.textContent = bid.company_name || bid.username || 'this contractor';
        const ta = document.getElementById('rejectReason');
        if (ta) ta.value = '';
        const overlay = document.getElementById('rejectOverlay');
        if (overlay) overlay.style.display = '';
    }

    _closeRejectModal() {
        const overlay = document.getElementById('rejectOverlay');
        if (overlay) overlay.style.display = 'none';
        this.pendingRejectBidId = null;
        this.pendingProjectId   = null;
    }

    async _confirmReject() {
        const bidId     = this.pendingRejectBidId;
        const projectId = this.pendingProjectId;
        if (!bidId || !projectId) { this._closeRejectModal(); return; }

        const reason = document.getElementById('rejectReason')?.value?.trim() || '';
        const btn    = document.getElementById('rejectConfirmBtn');
        if (btn) { btn.disabled = true; btn.textContent = 'Rejecting…'; }

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res  = await fetch(`/owner/projects/${projectId}/bids/${bidId}/reject`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body:    JSON.stringify({ reason }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Failed');

            this._closeRejectModal();
            await this._loadBids(projectId);
        } catch (err) {
            alert(err.message || 'Failed to reject bid.');
            if (btn) { btn.disabled = false; btn.textContent = 'Reject'; }
        }
    }
}

/* ── Singleton init ──────────────────────────────────────────── */
let _pdmInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    _pdmInstance = new ProjectDetailsModal();
});

/* ── Public API ──────────────────────────────────────────────── */
window.ProjectDetailsModal     = ProjectDetailsModal;
window.openProjectDetailsModal = (project) => {
    if (_pdmInstance) {
        _pdmInstance.open(project);
    } else {
        // DOM not ready yet — retry once
        setTimeout(() => _pdmInstance?.open(project), 150);
    }
};
