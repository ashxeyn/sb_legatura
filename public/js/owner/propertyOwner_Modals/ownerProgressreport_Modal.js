/**
 * Owner Progress Report Detail Modal — Enhanced & Interactive
 * Consistent with mdp-* design system. Features:
 * — Hero card with project context
 * — Status badge with animated entrance
 * — Rejection reason card (when rejected)
 * — Progress percentage bar with animation
 * — Image gallery with zoom overlays + lightbox navigation (prev/next/keyboard)
 * — File list with icon-mapped file types
 * — Section counts
 * — Smooth staggered entrance animations
 */
class OwnerProgressReportModal {
    constructor() {
        this.modal = document.getElementById('ownerViewProgressModal');
        this.lightbox = document.getElementById('oprLightbox');
        if (!this.modal) return;

        this.overlay = document.getElementById('oprOverlay');
        this.currentReport = null;
        this.imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        this.lightboxImages = [];
        this.lightboxIndex = 0;

        this.bindEvents();
    }

    bindEvents() {
        const closeBtn = document.getElementById('oprCloseBtn');
        const closeFooter = document.getElementById('oprCloseFooterBtn');
        const backBtn = document.getElementById('oprBackBtn');
        const lbClose = document.getElementById('oprLightboxClose');
        const lbPrev = document.getElementById('oprLightboxPrev');
        const lbNext = document.getElementById('oprLightboxNext');

        if (closeBtn) closeBtn.addEventListener('click', () => this.close());
        if (closeFooter) closeFooter.addEventListener('click', () => this.close());
        if (backBtn) backBtn.addEventListener('click', () => this.close());
        if (this.overlay) this.overlay.addEventListener('click', () => this.close());

        // Lightbox controls
        if (lbClose) lbClose.addEventListener('click', () => this.closeLightbox());
        if (lbPrev) lbPrev.addEventListener('click', () => this.navigateLightbox(-1));
        if (lbNext) lbNext.addEventListener('click', () => this.navigateLightbox(1));
        if (this.lightbox) {
            this.lightbox.querySelector('.opr-lightbox-overlay')?.addEventListener('click', () => this.closeLightbox());
        }

        // Keyboard events
        document.addEventListener('keydown', (e) => {
            if (this.lightbox && this.lightbox.style.display !== 'none') {
                if (e.key === 'Escape') this.closeLightbox();
                else if (e.key === 'ArrowLeft') this.navigateLightbox(-1);
                else if (e.key === 'ArrowRight') this.navigateLightbox(1);
            } else if (this.isOpen()) {
                if (e.key === 'Escape') this.close();
            }
        });
    }

    /**
     * Open the modal with a progress report.
     * @param {Object} report  – { progress_id, purpose, progress_status, submitted_at, rejection_reason, percentage_progress, files }
     * @param {Object} context – { milestoneTitle, sequenceNumber }
     */
    open(report, context = {}) {
        if (!this.modal || !report) return;
        this.currentReport = report;
        this.lightboxImages = [];
        this.lightboxIndex = 0;

        const config = window.__milestoneProgressConfig || {};
        const status = report.progress_status || 'submitted';

        // ── Status badge ──
        const statusBadge = document.getElementById('oprStatusBadge');
        const statusIcon = document.getElementById('oprStatusIcon');
        const statusText = document.getElementById('oprStatusText');
        const dateText = document.getElementById('oprDateText');
        const statusConfigs = {
            submitted: { label: 'Pending Review', icon: 'fi-rr-clock',       cls: 'opr-status-submitted' },
            approved:  { label: 'Approved',       icon: 'fi-rr-check',       cls: 'opr-status-approved' },
            rejected:  { label: 'Rejected',       icon: 'fi-rr-cross-small', cls: 'opr-status-rejected' },
            deleted:   { label: 'Deleted',        icon: 'fi-rr-trash',       cls: 'opr-status-deleted' },
        };
        const sc = statusConfigs[status] || statusConfigs.submitted;
        if (statusBadge) statusBadge.className = 'opr-status-badge ' + sc.cls;
        if (statusIcon) statusIcon.className = 'fi ' + sc.icon;
        if (statusText) statusText.textContent = sc.label;
        if (dateText) dateText.textContent = report.submitted_at ? this.formatDate(report.submitted_at) : '';

        // ── Hero card ──
        const heroPrj = document.getElementById('oprHeroProject');
        const heroMs = document.getElementById('oprHeroMilestone');
        const heroDate = document.querySelector('#oprHeroDate span');
        const heroSeq = document.querySelector('#oprHeroSeq span');
        if (heroPrj) heroPrj.textContent = config.milestoneTitle || context.milestoneTitle || 'Project';
        if (heroMs) heroMs.textContent = context.milestoneTitle || `Milestone Item ${context.sequenceNumber || ''}`;
        if (heroDate) heroDate.textContent = report.submitted_at ? this.formatShortDate(report.submitted_at) : 'N/A';
        if (heroSeq) heroSeq.textContent = `Item #${context.sequenceNumber || config.seqNum || 1}`;

        // ── Rejection Reason ──
        const rejSection = document.getElementById('oprRejectionSection');
        const rejText = document.getElementById('oprRejectionText');
        if (rejSection) {
            const reason = report.rejection_reason || report.reason || '';
            if (status === 'rejected' && reason) {
                rejSection.style.display = '';
                if (rejText) rejText.textContent = reason;
            } else {
                rejSection.style.display = 'none';
            }
        }

        // ── Progress % ──
        const progSection = document.getElementById('oprProgressSection');
        const progPct = document.getElementById('oprProgressPct');
        const progFill = document.getElementById('oprProgressFill');
        const percentage = parseFloat(report.percentage_progress || report.progress_percentage || 0);
        if (progSection) {
            if (percentage > 0) {
                progSection.style.display = '';
                if (progPct) progPct.textContent = Math.round(percentage) + '%';
                if (progFill) {
                    progFill.style.width = '0%';
                    // Animate after modal opens
                    setTimeout(() => { progFill.style.width = Math.min(100, percentage) + '%'; }, 400);
                }
            } else {
                progSection.style.display = 'none';
            }
        }

        // ── Purpose ──
        const purposeEl = document.getElementById('oprPurposeText');
        if (purposeEl) {
            if (report.purpose) {
                purposeEl.textContent = report.purpose;
                purposeEl.classList.remove('opr-no-content');
            } else {
                purposeEl.textContent = 'No description provided.';
                purposeEl.classList.add('opr-no-content');
            }
        }

        // ── Sort files: images vs other ──
        const files = report.files || [];
        const images = files.filter(f => {
            const ext = (f.original_name || f.file_path || '').split('.').pop().toLowerCase();
            return this.imageExts.includes(ext);
        });
        const otherFiles = files.filter(f => {
            const ext = (f.original_name || f.file_path || '').split('.').pop().toLowerCase();
            return !this.imageExts.includes(ext);
        });

        // ── Image Gallery ──
        const gallerySection = document.getElementById('oprGallerySection');
        const gallery = document.getElementById('oprGallery');
        const photoCount = document.getElementById('oprPhotoCount');
        if (gallerySection && gallery) {
            if (images.length > 0) {
                gallerySection.style.display = '';
                if (photoCount) photoCount.textContent = images.length;
                this.lightboxImages = images.map(f => f.file_path ? `/storage/${f.file_path}` : '#');
                gallery.innerHTML = images.map((f, i) => {
                    const url = f.file_path ? `/storage/${f.file_path}` : '#';
                    return `<div class="opr-gallery-thumb" data-url="${this.escapeHtml(url)}" data-index="${i}">
                        <img src="${this.escapeHtml(url)}" alt="Photo ${i + 1}" loading="lazy">
                        <span class="opr-gallery-zoom-icon"><i class="fi fi-rr-search"></i></span>
                    </div>`;
                }).join('');
                // Bind clicks
                gallery.querySelectorAll('.opr-gallery-thumb').forEach(thumb => {
                    thumb.addEventListener('click', () => {
                        const idx = parseInt(thumb.dataset.index) || 0;
                        this.openLightbox(thumb.dataset.url, idx);
                    });
                });
            } else {
                gallerySection.style.display = 'none';
                gallery.innerHTML = '';
            }
        }

        // ── File list ──
        const filesSection = document.getElementById('oprFilesSection');
        const filesList = document.getElementById('oprFilesList');
        const fileCount = document.getElementById('oprFileCount');
        if (filesSection && filesList) {
            if (otherFiles.length > 0) {
                filesSection.style.display = '';
                if (fileCount) fileCount.textContent = otherFiles.length;
                filesList.innerHTML = otherFiles.map(f => {
                    const name = f.original_name || f.file_path?.split('/').pop() || 'File';
                    const ext = name.split('.').pop().toLowerCase();
                    const icon = this.getFileIcon(ext);
                    const url = f.file_path ? `/storage/${f.file_path}` : '#';
                    return `<a href="${this.escapeHtml(url)}" target="_blank" class="opr-file-item" title="${this.escapeHtml(name)}">
                        <div class="opr-file-icon-wrap"><i class="fi ${icon}"></i></div>
                        <div class="opr-file-info">
                            <span class="opr-file-name">${this.escapeHtml(name)}</span>
                            <span class="opr-file-ext">${ext.toUpperCase()}</span>
                        </div>
                        <i class="fi fi-rr-download opr-file-dl"></i>
                    </a>`;
                }).join('');
            } else {
                filesSection.style.display = 'none';
                filesList.innerHTML = '';
            }
        }

        // ── No attachments at all ──
        if (images.length === 0 && otherFiles.length === 0 && filesSection) {
            filesSection.style.display = '';
            if (fileCount) fileCount.textContent = '0';
            const sec = filesSection.querySelector('.opr-section-title span:first-of-type');
            if (sec) sec.textContent = 'Attachments';
            if (filesList) {
                filesList.innerHTML = `
                    <div class="opr-empty-attachments">
                        <i class="fi fi-rr-clip"></i>
                        <p>No attachments</p>
                        <span>Files and photos will appear here when uploaded</span>
                    </div>`;
            }
        }

        // ── Show modal ──
        this.modal.style.display = '';
        // Scroll body to top
        const body = document.getElementById('oprModalBody');
        if (body) body.scrollTop = 0;
        requestAnimationFrame(() => {
            this.modal.classList.add('opr-active');
            document.body.style.overflow = 'hidden';
        });
    }

    close() {
        if (!this.modal) return;
        this.modal.classList.remove('opr-active');
        setTimeout(() => {
            this.modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 350);
        this.currentReport = null;
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('opr-active');
    }

    // ── Lightbox ──
    openLightbox(url, index = 0) {
        if (!this.lightbox) return;
        this.lightboxIndex = index;
        this.updateLightbox();
        this.lightbox.style.display = '';
        requestAnimationFrame(() => this.lightbox.classList.add('opr-lb-active'));
    }

    closeLightbox() {
        if (!this.lightbox) return;
        this.lightbox.classList.remove('opr-lb-active');
        setTimeout(() => {
            this.lightbox.style.display = 'none';
            const img = document.getElementById('oprLightboxImg');
            if (img) img.src = '';
        }, 300);
    }

    navigateLightbox(dir) {
        if (this.lightboxImages.length <= 1) return;
        this.lightboxIndex = (this.lightboxIndex + dir + this.lightboxImages.length) % this.lightboxImages.length;
        this.updateLightbox();
    }

    updateLightbox() {
        const img = document.getElementById('oprLightboxImg');
        const counter = document.getElementById('oprLightboxCounter');
        const prevBtn = document.getElementById('oprLightboxPrev');
        const nextBtn = document.getElementById('oprLightboxNext');
        if (img && this.lightboxImages[this.lightboxIndex]) {
            img.src = this.lightboxImages[this.lightboxIndex];
        }
        if (counter) {
            counter.textContent = `${this.lightboxIndex + 1} / ${this.lightboxImages.length}`;
            counter.style.display = this.lightboxImages.length > 1 ? '' : 'none';
        }
        // Hide nav when single image
        if (prevBtn) prevBtn.style.display = this.lightboxImages.length > 1 ? '' : 'none';
        if (nextBtn) nextBtn.style.display = this.lightboxImages.length > 1 ? '' : 'none';
    }

    // ── Helpers ──
    formatDate(dateStr) {
        if (!dateStr) return '';
        try {
            const d = new Date(dateStr);
            return d.toLocaleDateString('en-US', {
                weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
            });
        } catch { return dateStr; }
    }

    formatShortDate(dateStr) {
        if (!dateStr) return '';
        try {
            return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        } catch { return dateStr; }
    }

    getFileIcon(ext) {
        const map = {
            pdf: 'fi-rr-file-pdf', doc: 'fi-rr-file-word', docx: 'fi-rr-file-word',
            xls: 'fi-rr-file-excel', xlsx: 'fi-rr-file-excel',
            zip: 'fi-rr-file-zip', rar: 'fi-rr-file-zip',
            ppt: 'fi-rr-file-powerpoint', pptx: 'fi-rr-file-powerpoint',
            txt: 'fi-rr-file-alt', csv: 'fi-rr-file-excel',
        };
        return map[ext] || 'fi-rr-file';
    }

    escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }
}

// Init and expose global opener
document.addEventListener('DOMContentLoaded', () => {
    const instance = new OwnerProgressReportModal();
    window.ownerProgressReportModalInstance = instance;
    window.openOwnerProgressReportModal = (report, context) => {
        instance.open(report, context);
    };
});
