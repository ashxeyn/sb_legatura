{{-- Progress Report Detail Modal (Owner) — redesigned for consistency with mdp-* system --}}
<div id="ownerViewProgressModal" class="opr-modal" style="display:none;">
    <div class="opr-modal-overlay" id="oprOverlay"></div>
    <div class="opr-modal-content">
        {{-- Header --}}
        <div class="opr-modal-header">
            <div class="opr-modal-header-left">
                <button class="opr-back-btn" id="oprBackBtn" title="Go back">
                    <i class="fi fi-rr-arrow-left"></i>
                </button>
                <h3 class="opr-modal-title">Progress Report</h3>
            </div>
            <button class="opr-close-btn" id="oprCloseBtn" title="Close">
                <i class="fi fi-rr-cross-small"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="opr-modal-body" id="oprModalBody">

            {{-- Status Badge Row --}}
            <div class="opr-status-row" id="oprStatusRow">
                <span class="opr-status-badge" id="oprStatusBadge">
                    <i class="fi" id="oprStatusIcon"></i>
                    <span id="oprStatusText"></span>
                </span>
                <span class="opr-status-date" id="oprStatusDate">
                    <i class="fi fi-rr-calendar"></i>
                    <span id="oprDateText"></span>
                </span>
            </div>

            {{-- Hero Card — project/milestone context --}}
            <div class="opr-hero-card">
                <p class="opr-hero-project" id="oprHeroProject"></p>
                <p class="opr-hero-milestone" id="oprHeroMilestone"></p>
                <div class="opr-hero-meta">
                    <span class="opr-hero-meta-item" id="oprHeroDate"><i class="fi fi-rr-calendar"></i> <span></span></span>
                    <span class="opr-hero-meta-item" id="oprHeroSeq"><i class="fi fi-rr-layers"></i> <span></span></span>
                </div>
            </div>

            {{-- Rejection Reason --}}
            <div class="opr-rejection-card" id="oprRejectionSection" style="display:none;">
                <div class="opr-rejection-icon">
                    <i class="fi fi-rr-exclamation"></i>
                </div>
                <div>
                    <p class="opr-rejection-label">Rejection Reason</p>
                    <p class="opr-rejection-text" id="oprRejectionText"></p>
                </div>
            </div>

            {{-- Progress Percentage --}}
            <div class="opr-progress-bar-wrap" id="oprProgressSection" style="display:none;">
                <div class="opr-progress-label">
                    <span>Progress Level</span>
                    <span id="oprProgressPct">0%</span>
                </div>
                <div class="opr-progress-track">
                    <div class="opr-progress-fill" id="oprProgressFill" style="width:0%;"></div>
                </div>
            </div>

            {{-- Description --}}
            <div class="opr-section">
                <h4 class="opr-section-title">
                    <i class="fi fi-rr-document"></i>
                    <span>Description</span>
                </h4>
                <p class="opr-purpose-text" id="oprPurposeText">—</p>
            </div>

            {{-- Image Gallery --}}
            <div class="opr-section" id="oprGallerySection" style="display:none;">
                <h4 class="opr-section-title">
                    <i class="fi fi-rr-picture"></i>
                    <span>Photos</span>
                    <span class="opr-section-count" id="oprPhotoCount"></span>
                </h4>
                <div class="opr-gallery" id="oprGallery"></div>
            </div>

            {{-- File Attachments --}}
            <div class="opr-section" id="oprFilesSection" style="display:none;">
                <h4 class="opr-section-title">
                    <i class="fi fi-rr-clip"></i>
                    <span>Attachments</span>
                    <span class="opr-section-count" id="oprFileCount"></span>
                </h4>
                <div class="opr-files-list" id="oprFilesList"></div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="opr-modal-footer">
            <button class="opr-btn opr-btn-secondary" id="oprCloseFooterBtn">
                <i class="fi fi-rr-cross-small"></i>
                <span>Close</span>
            </button>
        </div>
    </div>
</div>

{{-- Image Preview Lightbox --}}
<div id="oprLightbox" class="opr-lightbox" style="display:none;">
    <div class="opr-lightbox-overlay"></div>
    <div class="opr-lightbox-content">
        <button class="opr-lightbox-close" id="oprLightboxClose"><i class="fi fi-rr-cross"></i></button>
        <button class="opr-lightbox-nav opr-lightbox-prev" id="oprLightboxPrev"><i class="fi fi-rr-angle-left"></i></button>
        <button class="opr-lightbox-nav opr-lightbox-next" id="oprLightboxNext"><i class="fi fi-rr-angle-right"></i></button>
        <img id="oprLightboxImg" src="" alt="Preview">
        <div class="opr-lightbox-counter" id="oprLightboxCounter"></div>
    </div>
</div>
