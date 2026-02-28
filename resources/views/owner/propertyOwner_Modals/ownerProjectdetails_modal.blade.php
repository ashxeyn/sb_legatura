{{-- ═══════════════════════════════════════════════════════════════
     Project Details Modal — web-first redesign
     IDs preserved exactly for ownerProjectdetails_modal.js
═══════════════════════════════════════════════════════════════ --}}

<div id="projectDetailsModal" class="pdm-modal" role="dialog" aria-modal="true" aria-labelledby="pdmDialogTitle">

    {{-- Backdrop --}}
    <div class="pdm-backdrop" id="projectModalOverlay"></div>

    {{-- Dialog shell --}}
    <div class="pdm-dialog">

        {{-- ── Header ────────────────────────────────────────────── --}}
        <div class="pdm-header">
            <button class="pdm-close-btn" id="closeProjectModalBtn" aria-label="Close modal" type="button">
                <i class="fi fi-rr-cross-small"></i>
            </button>
            <h2 class="pdm-header-title" id="pdmDialogTitle">Project Details</h2>
            <div class="w-9 shrink-0"></div>
        </div>

        {{-- ── Scrollable body ───────────────────────────────────── --}}
        <div class="pdm-body" id="pdmScrollBody">

            {{-- ── HERO: Gradient Summary Card ───────────────────── --}}
            <div class="pdm-hero" id="summaryCard">
                <div class="pdm-hero-inner">

                    {{-- Top row --}}
                    <div class="flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <span class="pdm-type-pill" id="modalTypePill"></span>
                            <h3 class="pdm-hero-title" id="modalProjectTitle"></h3>
                            <div class="pdm-hero-loc">
                                <i class="fi fi-rr-marker shrink-0"></i>
                                <span class="truncate" id="modalLocationText"></span>
                            </div>
                        </div>
                        <button class="pdm-toggle-btn" id="summaryToggleBtn" aria-expanded="false" type="button" title="Show / hide details">
                            <i class="fi fi-rr-angle-small-down" id="summaryChevron"></i>
                        </button>
                    </div>

                    {{-- Status / days pills --}}
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="pdm-pill" id="statusPill"></span>
                        <span class="pdm-pill pdm-pill-ghost" id="daysPill" style="display:none"></span>
                    </div>

                    {{-- ── Expandable details ──────────────────────── --}}
                    <div class="pdm-expanded" id="summaryExpanded">
                        <div class="pdm-hero-sep"></div>

                        {{-- Description --}}
                        <div class="pdm-exp-block">
                            <span class="pdm-exp-label">Description</span>
                            <p class="pdm-exp-text" id="expDescription"></p>
                        </div>

                        {{-- Budget --}}
                        <div class="pdm-exp-block">
                            <span class="pdm-exp-label">Budget Range</span>
                            <div class="pdm-budget-row">
                                <div class="pdm-budget-cell">
                                    <span class="pdm-budget-sub">Minimum</span>
                                    <span class="pdm-budget-val" id="expBudgetMin"></span>
                                </div>
                                <div class="pdm-budget-divider"></div>
                                <div class="pdm-budget-cell">
                                    <span class="pdm-budget-sub">Maximum</span>
                                    <span class="pdm-budget-val" id="expBudgetMax"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Specifications --}}
                        <div class="pdm-exp-block">
                            <span class="pdm-exp-label">Specifications</span>
                            <div class="pdm-spec-grid" id="expSpecGrid"></div>
                        </div>

                        {{-- Bidding Deadline --}}
                        <div class="pdm-exp-block" id="expDeadlineWrap" style="display:none">
                            <span class="pdm-exp-label">Bidding Deadline</span>
                            <p class="pdm-exp-text" id="expDeadline"></p>
                        </div>

                        {{-- Posted on --}}
                        <div class="pdm-exp-block">
                            <span class="pdm-exp-label">Posted On</span>
                            <p class="pdm-exp-text" id="expPostedOn"></p>
                        </div>

                        {{-- Contractor & Agreement --}}
                        <div id="expContractorSection" style="display:none">
                            <div class="pdm-hero-sep"></div>
                            <div class="pdm-exp-block">
                                <span class="pdm-exp-label">Contractor &amp; Agreement</span>

                                <div class="pdm-ct-row">
                                    <div class="pdm-ct-avatar" id="ctAvatarPh">
                                        <i class="fi fi-rr-user"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="pdm-ct-name" id="ctName"></p>
                                        <p class="pdm-ct-username" id="ctUser"></p>
                                    </div>
                                    <span class="pdm-ct-badge" id="ctExpBadge" style="display:none"></span>
                                </div>

                                <div class="pdm-agreed-row" id="agreedRow" style="display:none">
                                    <div class="pdm-agreed-cell">
                                        <span class="pdm-agreed-lbl">Agreed Cost</span>
                                        <span class="pdm-agreed-val" id="agreedCost"></span>
                                    </div>
                                    <div class="pdm-agreed-cell">
                                        <span class="pdm-agreed-lbl">Timeline</span>
                                        <span class="pdm-agreed-val" id="agreedTimeline"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    {{-- end expandable --}}

                </div>
            </div>
            {{-- end hero --}}

            {{-- ── Alert Banner ─────────────────────────────────── --}}
            <div class="pdm-alert" id="statusBanner" style="display:none">
                <div class="pdm-alert-inner" id="bannerInner">
                    <div class="pdm-alert-icon-wrap">
                        <i class="fi pdm-alert-icon" id="bannerIcon"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="pdm-alert-title" id="bannerTitle"></p>
                        <p class="pdm-alert-msg" id="bannerMsg"></p>
                    </div>
                </div>
            </div>


            {{-- ── Cards Grid ───────────────────────────────────── --}}
            <div class="pdm-cards-grid">

                {{-- Milestone Action Card --}}
                <div class="pdm-card" id="milestoneActionCard" style="display:none" tabindex="0" role="button" aria-label="View milestone details">
                    <div class="pdm-card-ico-wrap" id="milestoneIconWrap" style="background:#10B98118">
                        <i class="fi fi-rr-check-circle pdm-card-ico" id="milestoneActionIcon" style="color:#10B981"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="pdm-card-title" id="milestoneCardTitle"></p>
                        <p class="pdm-card-desc" id="milestoneCardDesc"></p>
                    </div>
                    <div class="pdm-chip" id="milestoneChip">
                        <span id="milestoneChipLabel"></span>
                        <i class="fi fi-rr-arrow-right text-xs"></i>
                    </div>
                </div>

                {{-- Bids Row Card --}}
                <div class="pdm-card pdm-card-bids" id="bidsRowCard" tabindex="0" role="button" aria-label="View submitted bids">
                    <div class="pdm-bids-ico-wrap">
                        <i class="fi fi-rr-users"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="pdm-card-title">Bids Received</p>
                        <p class="pdm-card-desc" id="bidsCountLabel"></p>
                    </div>
                    <div class="pdm-chip pdm-chip-bids" id="bidsCardChip">
                        <span>View All Bids</span>
                        <i class="fi fi-rr-arrow-right text-xs"></i>
                    </div>
                </div>

            </div>
            {{-- end cards grid --}}

        </div>
        {{-- end body --}}

        {{-- ── Bids Slide-in Panel ──────────────────────────────── --}}
        <div class="pdm-bids-panel" id="bidsPanel" role="region" aria-label="Submitted bids">

            {{-- Panel header --}}
            <div class="pdm-bids-panel-hdr">
                <button class="pdm-bids-back-btn" id="bidsPanelBackBtn" type="button" aria-label="Back to project details">
                    <i class="fi fi-rr-arrow-left"></i>
                </button>
                <div class="pdm-bids-panel-titles">
                    <span class="pdm-bids-panel-title">Project Bids</span>
                    <span class="pdm-bids-panel-subtitle" id="bidsPanelSubtitle"></span>
                </div>
            </div>

            {{-- Count bar --}}
            <div class="pdm-bids-count-bar" id="bidsPanelCountBar">
                <i class="fi fi-rr-users pdm-bids-count-ico"></i>
                <span class="pdm-bids-count-label" id="bidsPanelBadge"></span>
            </div>

            {{-- Panel body --}}
            <div class="pdm-bids-panel-body" id="bidsPanelBody">

                {{-- Loading state --}}
                <div class="pdm-bids-loading" id="bidsPanelLoading">
                    <div class="pdm-bids-spinner"></div>
                    <p>Loading bids&hellip;</p>
                </div>

                {{-- Bids list (populated by JS) --}}
                <div class="pdm-bids-list" id="bidsPanelList"></div>

                {{-- Empty state --}}
                <div class="pdm-bids-empty" id="bidsPanelEmpty" style="display:none">
                    <div class="pdm-bids-empty-circle">
                        <i class="fi fi-rr-inbox"></i>
                    </div>
                    <p class="pdm-bids-empty-title">No Bids Yet</p>
                    <p class="pdm-bids-empty-sub">Contractors haven't submitted any bids for this project yet. Check back later!</p>
                </div>

            </div>
        </div>
        {{-- end bids panel --}}

        {{-- ── Bid Detail Sub-Panel ─────────────────────────────────── --}}
        <div class="pdm-bid-detail-panel" id="bidDetailPanel"
             style="position:absolute;top:61px;left:0;right:0;bottom:0;display:flex;flex-direction:column;overflow:hidden;z-index:30">

            {{-- Header --}}
            <div class="pdm-bids-panel-hdr" style="flex-shrink:0">
                <button class="pdm-bids-back-btn" id="bidDetailBackBtn" type="button" aria-label="Back to bids list">
                    <i class="fi fi-rr-arrow-left"></i>
                </button>
                <div class="pdm-bids-panel-titles">
                    <span class="pdm-bids-panel-title">Bid Details</span>
                </div>
                <span class="pdm-bd-status-pill" id="bdStatusPill"></span>
            </div>

            {{-- Scrollable body --}}
            <div class="pdm-bd-body" style="flex:1 1 0%;min-height:0;overflow-y:auto;overflow-x:hidden;display:block">

                {{-- ── Hero card: avatar + info + pricing ── --}}
                <div class="pdm-bd-hero-card">
                    <div class="pdm-bd-avatar-row">
                        <div class="pdm-bd-avatar-wrap" id="bdAvatarWrap">
                            <img class="pdm-bd-avatar" id="bdAvatar" src="" alt="">
                        </div>
                        <div class="pdm-bd-contractor-info">
                            <span class="pdm-bd-company" id="bdCompanyName"></span>
                            <span class="pdm-bd-username" id="bdUsername"></span>
                        </div>
                    </div>
                    <div class="pdm-bd-price-block">
                        <span class="pdm-bd-price-lbl">Proposed Cost</span>
                        <span class="pdm-bd-price-val" id="bdCost"></span>
                        <span class="pdm-bd-price-sub" id="bdTimeline"></span>
                    </div>
                </div>

                {{-- ── Quick stats card ── --}}
                <div class="pdm-bd-stats-card">
                    <div class="pdm-bd-stat-item">
                        <span class="pdm-bd-stat-val" id="bdYearsExp">—</span>
                        <span class="pdm-bd-stat-lbl">Years Exp.</span>
                    </div>
                    <div class="pdm-bd-stat-divider"></div>
                    <div class="pdm-bd-stat-item">
                        <span class="pdm-bd-stat-val" id="bdCompletedProjects">—</span>
                        <span class="pdm-bd-stat-lbl">Projects Done</span>
                    </div>
                    <div class="pdm-bd-stat-divider" id="bdPicabDiv" style="display:none"></div>
                    <div class="pdm-bd-stat-item" id="bdPicab" style="display:none">
                        <span class="pdm-bd-stat-val pdm-bd-stat-sm" id="bdPicabItem">—</span>
                        <span class="pdm-bd-stat-lbl">PICAB</span>
                    </div>
                    <div class="pdm-bd-stat-divider" id="bdTypeDiv" style="display:none"></div>
                    <div class="pdm-bd-stat-item" id="bdType" style="display:none">
                        <span class="pdm-bd-stat-val pdm-bd-stat-sm" id="bdTypeItem">—</span>
                        <span class="pdm-bd-stat-lbl">Type</span>
                    </div>
                </div>

                {{-- ── Bid Information card ── --}}
                <div class="pdm-bd-card">
                    <div class="pdm-bd-card-hdr">
                        <i class="fi fi-rr-document-signed"></i>
                        <span>Bid Information</span>
                    </div>
                    <div class="pdm-bd-row">
                        <span class="pdm-bd-row-lbl">Submitted On</span>
                        <span class="pdm-bd-row-val" id="bdSubmittedOn">—</span>
                    </div>
                    <div class="pdm-bd-row">
                        <span class="pdm-bd-row-lbl">Proposed Cost</span>
                        <span class="pdm-bd-row-val pdm-bd-row-highlight" id="bdCost2">—</span>
                    </div>
                    <div class="pdm-bd-row">
                        <span class="pdm-bd-row-lbl">Est. Timeline</span>
                        <span class="pdm-bd-row-val" id="bdTimeline2">—</span>
                    </div>
                    <div class="pdm-bd-row pdm-bd-row--last">
                        <span class="pdm-bd-row-lbl">Status</span>
                        <span class="pdm-bd-row-val" id="bdStatusVal">—</span>
                    </div>
                </div>

                {{-- ── Contractor's Notes card ── --}}
                <div class="pdm-bd-card" id="bdNotesSection" style="display:none">
                    <div class="pdm-bd-card-hdr">
                        <i class="fi fi-rr-comment-alt"></i>
                        <span>Contractor's Notes</span>
                    </div>
                    <p class="pdm-bd-notes" id="bdNotes"></p>
                </div>

                {{-- ── Contact card ── --}}
                <div class="pdm-bd-card" id="bdContactSection" style="display:none">
                    <div class="pdm-bd-card-hdr">
                        <i class="fi fi-rr-phone-call"></i>
                        <span>Contact</span>
                    </div>
                    <div class="pdm-bd-contact-btns" id="bdContactBtns"></div>
                </div>

                {{-- ── Attachments card ── --}}
                <div class="pdm-bd-card">
                    <div class="pdm-bd-card-hdr">
                        <i class="fi fi-rr-paperclip"></i>
                        <span>Attachments</span>
                        <span class="pdm-bd-file-count" id="bdFileCount"></span>
                    </div>
                    <div class="pdm-bd-files-list" id="bdFilesList"></div>
                </div>

                {{-- ── Action buttons ── --}}
                <div class="pdm-bd-actions" id="bdActions">
                    <button class="pdm-bd-accept-btn" id="bdAcceptBtn" type="button">
                        <i class="fi fi-rr-check"></i>
                        Accept Bid
                    </button>
                    <button class="pdm-bd-decline-btn" id="bdDeclineBtn" type="button">
                        Decline Bid
                    </button>
                </div>

                <div style="height:24px"></div>
            </div>
        </div>
        {{-- end bid detail panel --}}

        {{-- ── Accept Confirmation Overlay ───────────────────────────── --}}
        <div class="pdm-accept-overlay" id="acceptOverlay" style="display:none">
            <div class="pdm-accept-dialog">
                {{-- Success icon --}}
                <div class="pdm-accept-icon-wrap">
                    <i class="fi fi-rr-check-circle"></i>
                </div>

                <p class="pdm-accept-title">Accept Bid</p>
                <p class="pdm-accept-subtitle">You are about to accept the bid from:</p>
                <p class="pdm-accept-bidder"><strong id="acceptBidRef"></strong></p>

                <div class="pdm-accept-warning">
                    <i class="fi fi-rr-info"></i>
                    <span>All other submitted bids for this project will be automatically rejected.</span>
                </div>

                <div class="pdm-accept-actions">
                    <button class="pdm-accept-cancel-btn" id="acceptCancelBtn" type="button">
                        <i class="fi fi-rr-arrow-left"></i> Go Back
                    </button>
                    <button class="pdm-accept-confirm-btn" id="acceptConfirmBtn" type="button">
                        <i class="fi fi-rr-check"></i> Accept Bid
                    </button>
                </div>
            </div>
        </div>
        {{-- end accept overlay --}}

        {{-- ── Reject Reason Overlay ────────────────────────────────── --}}
        <div class="pdm-reject-overlay" id="rejectOverlay" style="display:none">
            <div class="pdm-reject-dialog">
                {{-- Warning icon --}}
                <div class="pdm-reject-icon-wrap">
                    <i class="fi fi-rr-cross-circle"></i>
                </div>

                <p class="pdm-reject-title">Reject Bid</p>
                <p class="pdm-reject-subtitle">You are about to reject the bid from:</p>
                <p class="pdm-reject-bidder"><strong id="rejectBidRef"></strong></p>

                <div class="pdm-reject-field">
                    <label class="pdm-reject-label" for="rejectReason">Reason (optional)</label>
                    <textarea class="pdm-reject-textarea" id="rejectReason"
                              placeholder="Explain why this bid was rejected (visible to contractor)…"
                              rows="4"></textarea>
                </div>

                <div class="pdm-reject-actions">
                    <button class="pdm-reject-cancel-btn" id="rejectCancelBtn" type="button">
                        <i class="fi fi-rr-arrow-left"></i> Go Back
                    </button>
                    <button class="pdm-reject-confirm-btn" id="rejectConfirmBtn" type="button">
                        <i class="fi fi-rr-cross"></i> Reject Bid
                    </button>
                </div>
            </div>
        </div>
        {{-- end reject overlay --}}

    </div>
    {{-- end dialog --}}

</div>
{{-- end modal --}}
