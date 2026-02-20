// Project Details Modal - PHP Populated Version

// Global function to open a specific project modal
window.openProjectDetailsModal = function (project) {
    // Project object might come from JS loop, but we need the ID to find the DOM element
    const projectId = project.project_id || project.id;
    const modalId = `projectPostDetailsModal-${projectId}`;
    const modal = document.getElementById(modalId);

    if (modal) {
        modal.classList.remove('hidden');
        // Small delay for transition
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        document.body.style.overflow = 'hidden';
    } else {
        console.error(`Modal not found for project ${projectId}`);
    }
};

// Global function to close a specific project modal
window.closeProjectModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    }
};

// Handle "Place Bid" from within the details modal
window.triggerApplyBidFromDetails = function (projectId, modalId) {
    // 1. Close the details modal
    window.closeProjectModal(modalId);

    // 2. Find the "Apply Bid" button on the card (or trigger logic directly)
    // We can use the existing global logic if available, or simulate click
    // The contractor_Homepage.js attaches listeners to .apply-bid-button

    setTimeout(() => {
        const cardBtn = document.querySelector(`.apply-bid-button[data-project-id="${projectId}"]`);
        if (cardBtn) {
            cardBtn.click();
        } else {
            console.warn("Card bid button not found, falling back to global modal trigger if possible");
            // If we have access to the global function:
            // if (window.applyBidModal) window.applyBidModal.openModal({id: projectId}); 
        }
    }, 350); // Wait for close animation
};

document.addEventListener('DOMContentLoaded', function () {
    // Close on Escape key — also close any open viewer first
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            // Close any open design viewer first
            const openDesign = document.querySelector('.design-viewer:not(.hidden)');
            if (openDesign) {
                const mid = openDesign.id.replace('designViewer-', '');
                closeDesignViewer(mid);
                return;
            }
            // Close any open doc viewer
            const openDoc = document.querySelector('.doc-viewer:not(.hidden)');
            if (openDoc) {
                const mid = openDoc.id.replace('docViewer-', '');
                closeDocViewer(mid);
                return;
            }
            // Then close the details modal
            const openModal = document.querySelector('.project-details-modal.show');
            if (openModal) {
                window.closeProjectModal(openModal.id);
            }
        }
        // Arrow keys for active viewers
        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            const dir = e.key === 'ArrowLeft' ? -1 : 1;
            const openDesign = document.querySelector('.design-viewer:not(.hidden)');
            if (openDesign) {
                navDesignViewer(openDesign.id.replace('designViewer-', ''), dir);
                return;
            }
            const openDoc = document.querySelector('.doc-viewer:not(.hidden)');
            if (openDoc) {
                navDocViewer(openDoc.id.replace('docViewer-', ''), dir);
            }
        }
    });

    // Prevent drag-saving images inside doc viewers
    document.addEventListener('dragstart', function(e) {
        if (e.target.closest('.doc-viewer')) e.preventDefault();
    });
});

/* ==========================================
 * DESIGN IMAGE VIEWER  (no watermark)
 * ========================================== */

window._designViewerIndex = {};

window.openDesignViewer = function(modalId, startIndex) {
    const viewer = document.getElementById('designViewer-' + modalId);
    if (!viewer) return;
    window._designViewerIndex[modalId] = startIndex || 0;
    showSlide('designSlides-' + modalId, 'designCounter-' + modalId, 'designDots-' + modalId, window._designViewerIndex[modalId]);
    viewer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};

window.closeDesignViewer = function(modalId) {
    const viewer = document.getElementById('designViewer-' + modalId);
    if (viewer) viewer.classList.add('hidden');
    // Don't restore overflow — the details modal is still open
};

window.navDesignViewer = function(modalId, dir) {
    const slides = document.getElementById('designSlides-' + modalId);
    if (!slides) return;
    const total = slides.querySelectorAll('.viewer-slide').length;
    let idx = (window._designViewerIndex[modalId] || 0) + dir;
    if (idx < 0) idx = total - 1;
    if (idx >= total) idx = 0;
    window._designViewerIndex[modalId] = idx;
    showSlide('designSlides-' + modalId, 'designCounter-' + modalId, 'designDots-' + modalId, idx);
};

window.goToDesignSlide = function(modalId, idx) {
    window._designViewerIndex[modalId] = idx;
    showSlide('designSlides-' + modalId, 'designCounter-' + modalId, 'designDots-' + modalId, idx);
};

/* ==========================================
 * DOCUMENT VIEWER  (watermark protected)
 * ========================================== */

window._docViewerIndex = {};

window.openDocViewer = function(modalId, startIndex) {
    const viewer = document.getElementById('docViewer-' + modalId);
    if (!viewer) return;
    window._docViewerIndex[modalId] = startIndex || 0;
    showSlide('docSlides-' + modalId, 'docCounter-' + modalId, 'docDots-' + modalId, window._docViewerIndex[modalId]);
    updateDocLabel(modalId);
    viewer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};

window.closeDocViewer = function(modalId) {
    const viewer = document.getElementById('docViewer-' + modalId);
    if (viewer) viewer.classList.add('hidden');
};

window.navDocViewer = function(modalId, dir) {
    const slides = document.getElementById('docSlides-' + modalId);
    if (!slides) return;
    const total = slides.querySelectorAll('.viewer-slide').length;
    let idx = (window._docViewerIndex[modalId] || 0) + dir;
    if (idx < 0) idx = total - 1;
    if (idx >= total) idx = 0;
    window._docViewerIndex[modalId] = idx;
    showSlide('docSlides-' + modalId, 'docCounter-' + modalId, 'docDots-' + modalId, idx);
    updateDocLabel(modalId);
};

window.goToDocSlide = function(modalId, idx) {
    window._docViewerIndex[modalId] = idx;
    showSlide('docSlides-' + modalId, 'docCounter-' + modalId, 'docDots-' + modalId, idx);
    updateDocLabel(modalId);
};

/* ==========================================
 * SHARED HELPERS
 * ========================================== */

function showSlide(slidesId, counterId, dotsId, idx) {
    const container = document.getElementById(slidesId);
    const counter = document.getElementById(counterId);
    const dotsContainer = document.getElementById(dotsId);
    if (!container) return;

    const slides = container.querySelectorAll('.viewer-slide');
    slides.forEach((s, i) => {
        s.classList.toggle('hidden', i !== idx);
    });
    if (counter) counter.textContent = idx + 1;
    if (dotsContainer) {
        dotsContainer.querySelectorAll('.viewer-dot').forEach((d, i) => {
            d.classList.toggle('active', i === idx);
        });
    }
}

function updateDocLabel(modalId) {
    const slides = document.getElementById('docSlides-' + modalId);
    const label = document.getElementById('docLabel-' + modalId);
    if (!slides || !label) return;
    const idx = window._docViewerIndex[modalId] || 0;
    const active = slides.querySelectorAll('.viewer-slide')[idx];
    if (active) label.textContent = active.getAttribute('data-label') || 'Document';
}
