/**
 * Help & Support Modal JavaScript
 * Handles the help & support modal functionality
 */

class HelpSupportModal {
    constructor() {
        this.modal = document.getElementById('helpSupportModal');
        this.overlay = document.getElementById('helpSupportModalOverlay');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupFAQToggles();
    }

    setupEventListeners() {
        // Close button (X)
        const closeBtn = document.getElementById('closeHelpSupportModalBtn');
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
        const closeFooterBtn = document.getElementById('closeHelpSupportBtn');
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

        // Help & Support link from navbar
        const helpSupportLink = document.getElementById('helpSupportLink');
        if (helpSupportLink) {
            helpSupportLink.addEventListener('click', (e) => {
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

    setupFAQToggles() {
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            if (question) {
                question.addEventListener('click', () => {
                    // Toggle active class
                    const isActive = item.classList.contains('active');
                    
                    // Close all FAQs
                    faqItems.forEach(faq => faq.classList.remove('active'));
                    
                    // Open clicked FAQ if it wasn't active
                    if (!isActive) {
                        item.classList.add('active');
                    }
                });
            }
        });
    }

    open() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Scroll to top of modal content
            const modalBody = this.modal.querySelector('.help-support-modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
        }
    }

    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = '';
            
            // Close all open FAQs
            const faqItems = document.querySelectorAll('.faq-item.active');
            faqItems.forEach(item => item.classList.remove('active'));
        }
    }

    isOpen() {
        return this.modal && this.modal.classList.contains('active');
    }
}

// Initialize when DOM is ready
let helpSupportModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    helpSupportModalInstance = new HelpSupportModal();
    
    // Expose globally if needed
    window.openHelpSupportModal = () => {
        if (helpSupportModalInstance) {
            helpSupportModalInstance.open();
        }
    };
    
    window.closeHelpSupportModal = () => {
        if (helpSupportModalInstance) {
            helpSupportModalInstance.close();
        }
    };
});
