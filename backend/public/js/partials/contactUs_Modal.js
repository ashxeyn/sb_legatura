/**
 * Contact Us Modal JavaScript
 * Handles the contact us modal functionality
 */

class ContactUsModal {
    constructor() {
        this.modal = document.getElementById('contactUsModal');
        this.overlay = document.getElementById('contactUsModalOverlay');
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close button (X)
        const closeBtn = document.getElementById('closeContactUsModalBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.close();
            });
        }

        // Overlay click
        if (this.overlay) {
            this.overlay.addEventListener('click', () => {
                this.close();
            });
        }

        // Close button in footer
        const closeFooterBtn = document.getElementById('closeContactUsBtn');
        if (closeFooterBtn) {
            closeFooterBtn.addEventListener('click', () => {
                this.close();
            });
        }

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });

        // Contact Us link from navbar
        const contactUsLink = document.getElementById('contactUsLink');
        if (contactUsLink) {
            contactUsLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.open();
                
                // Close account settings modal if open
                const accountSettingsModal = document.getElementById('accountSettingsModal');
                if (accountSettingsModal && accountSettingsModal.classList.contains('show')) {
                    accountSettingsModal.classList.remove('show');
                }
            });
        }
    }

    open() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Scroll to top of modal content
            const modalBody = this.modal.querySelector('.contact-us-modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
        }
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }
}

// Initialize when DOM is ready
let contactUsModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    contactUsModalInstance = new ContactUsModal();
    
    // Expose globally if needed
    window.openContactUsModal = () => {
        if (contactUsModalInstance) {
            contactUsModalInstance.open();
        }
    };
    
    window.closeContactUsModal = () => {
        if (contactUsModalInstance) {
            contactUsModalInstance.close();
        }
    };
});
