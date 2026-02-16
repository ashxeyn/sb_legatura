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
    // Optional: Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.project-details-modal.show');
            if (openModal) {
                const modalId = openModal.id;
                window.closeProjectModal(modalId);
            }
        }
    });
});
