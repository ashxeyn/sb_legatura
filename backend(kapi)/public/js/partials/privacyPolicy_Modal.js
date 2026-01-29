/**
 * Privacy Policy Modal JavaScript
 * Handles the privacy policy modal functionality
 */

class PrivacyPolicyModal {
    constructor() {
        this.modal = document.getElementById('privacyPolicyModal');
        this.overlay = document.getElementById('privacyPolicyModalOverlay');
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close button (X)
        const closeBtn = document.getElementById('closePrivacyPolicyModalBtn');
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
        const closeFooterBtn = document.getElementById('closePrivacyPolicyBtn');
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

        // Privacy Policy link from navbar
        const privacyPolicyLink = document.getElementById('privacyPolicyLink');
        if (privacyPolicyLink) {
            privacyPolicyLink.addEventListener('click', (e) => {
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
            const modalBody = this.modal.querySelector('.privacy-policy-modal-body');
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
let privacyPolicyModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    privacyPolicyModalInstance = new PrivacyPolicyModal();
    
    // Expose globally if needed
    window.openPrivacyPolicyModal = () => {
        if (privacyPolicyModalInstance) {
            privacyPolicyModalInstance.open();
        }
    };
    
    window.closePrivacyPolicyModal = () => {
        if (privacyPolicyModalInstance) {
            privacyPolicyModalInstance.close();
        }
    };
});
