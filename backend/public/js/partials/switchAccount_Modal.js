/**
 * Switch Account Modal JavaScript
 * Handles the account switching functionality between Property Owner and Contractor
 */

class SwitchAccountModal {
    constructor() {
        this.modal = document.getElementById('switchAccountModal');
        this.overlay = document.getElementById('switchAccountModalOverlay');
        this.loadingOverlay = document.getElementById('switchAccountLoading');
        this.currentAccountType = null; // Will be set based on current page
        this.init();
    }

    init() {
        this.detectCurrentAccount();
        this.setupEventListeners();
        this.updateCurrentAccountUI();
    }

    detectCurrentAccount() {
        // Detect current account type based on URL or page context
        const path = window.location.pathname;
        
        if (path.includes('/contractor/') || path.includes('contractor')) {
            this.currentAccountType = 'contractor';
        } else if (path.includes('/owner/') || path.includes('owner') || path.includes('property')) {
            this.currentAccountType = 'owner';
        } else {
            // Default to owner if unclear
            this.currentAccountType = 'owner';
        }
    }

    setupEventListeners() {
        // Close button (X)
        const closeBtn = document.getElementById('closeSwitchAccountModalBtn');
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

        // Cancel button in footer
        const cancelBtn = document.getElementById('cancelSwitchAccountBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.close();
            });
        }

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });

        // Switch Account link from navbar
        const switchAccountLink = document.getElementById('switchAccountLink');
        if (switchAccountLink) {
            switchAccountLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.open();
                
                // Close account settings modal if open
                const accountSettingsModal = document.getElementById('accountSettingsModal');
                if (accountSettingsModal && accountSettingsModal.classList.contains('show')) {
                    accountSettingsModal.classList.remove('show');
                }
            });
        }

        // Switch to Owner button
        const switchToOwnerBtn = document.querySelectorAll('[data-target="owner"]');
        switchToOwnerBtn.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (this.currentAccountType !== 'owner') {
                    // Show owner form modal instead of switching directly
                    this.showOwnerFormModal();
                } else {
                    this.switchAccount('owner');
                }
            });
        });

        // Switch to Contractor button
        const switchToContractorBtn = document.querySelectorAll('[data-target="contractor"]');
        switchToContractorBtn.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (this.currentAccountType !== 'contractor') {
                    // Show contractor form modal instead of switching directly
                    this.showContractorFormModal();
                } else {
                    this.switchAccount('contractor');
                }
            });
        });

        // Card click to switch
        const ownerCard = document.getElementById('switchToOwner');
        const contractorCard = document.getElementById('switchToContractor');

        if (ownerCard) {
            ownerCard.addEventListener('click', (e) => {
                // Only if not clicking the button directly
                if (!e.target.closest('.account-switch-btn') && this.currentAccountType !== 'owner') {
                    this.switchAccount('owner');
                }
            });
        }

        if (contractorCard) {
            contractorCard.addEventListener('click', (e) => {
                // Only if not clicking the button directly
                if (!e.target.closest('.account-switch-btn') && this.currentAccountType !== 'contractor') {
                    this.switchAccount('contractor');
                }
            });
        }
    }

    updateCurrentAccountUI() {
        const ownerCard = document.getElementById('switchToOwner');
        const contractorCard = document.getElementById('switchToContractor');
        const ownerBadge = document.getElementById('ownerCurrentBadge');
        const contractorBadge = document.getElementById('contractorCurrentBadge');

        // Reset all
        if (ownerCard) ownerCard.classList.remove('current-account');
        if (contractorCard) contractorCard.classList.remove('current-account');
        if (ownerBadge) ownerBadge.style.display = 'none';
        if (contractorBadge) contractorBadge.style.display = 'none';

        // Set current
        if (this.currentAccountType === 'owner') {
            if (ownerCard) ownerCard.classList.add('current-account');
            if (ownerBadge) ownerBadge.style.display = 'block';
        } else if (this.currentAccountType === 'contractor') {
            if (contractorCard) contractorCard.classList.add('current-account');
            if (contractorBadge) contractorBadge.style.display = 'block';
        }

        // Update button states
        const ownerBtn = document.querySelector('[data-target="owner"]');
        const contractorBtn = document.querySelector('[data-target="contractor"]');

        if (ownerBtn) {
            if (this.currentAccountType === 'owner') {
                ownerBtn.disabled = true;
                ownerBtn.innerHTML = '<i class="fi fi-rr-check"></i><span>Current Account</span>';
            } else {
                ownerBtn.disabled = false;
                ownerBtn.innerHTML = '<i class="fi fi-rr-arrow-right"></i><span>Switch to Property Owner</span>';
            }
        }

        if (contractorBtn) {
            if (this.currentAccountType === 'contractor') {
                contractorBtn.disabled = true;
                contractorBtn.innerHTML = '<i class="fi fi-rr-check"></i><span>Current Account</span>';
            } else {
                contractorBtn.disabled = false;
                contractorBtn.innerHTML = '<i class="fi fi-rr-arrow-right"></i><span>Switch to Contractor</span>';
            }
        }
    }

    switchAccount(targetAccountType) {
        if (this.currentAccountType === targetAccountType) {
            this.showNotification('You are already on this account type', 'info');
            return;
        }

        // Show loading overlay
        this.showLoading();
        this.close();

        // Simulate account switch - In production, make API call
        setTimeout(() => {
            // Redirect to the appropriate dashboard
            let redirectUrl = '';
            
            if (targetAccountType === 'owner') {
                redirectUrl = '/owner/homepage'; // Update with your actual route
                this.showNotification('Switching to Property Owner account...', 'success');
            } else if (targetAccountType === 'contractor') {
                redirectUrl = '/contractor/homepage'; // Update with your actual route
                this.showNotification('Switching to Contractor account...', 'success');
            }

            // In production, make actual API call to switch account
            // const response = await fetch('/api/switch-account', {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            //     },
            //     body: JSON.stringify({ accountType: targetAccountType })
            // });

            setTimeout(() => {
                this.hideLoading();
                // Redirect to the new account dashboard
                window.location.href = redirectUrl;
            }, 1500);
        }, 500);
    }

    showLoading() {
        if (this.loadingOverlay) {
            this.loadingOverlay.classList.remove('hidden');
        }
    }

    hideLoading() {
        if (this.loadingOverlay) {
            this.loadingOverlay.classList.add('hidden');
        }
    }

    open() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Scroll to top of modal content
            const modalBody = this.modal.querySelector('.switch-account-modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }

            // Update UI based on current account
            this.updateCurrentAccountUI();
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

    showContractorFormModal() {
        // Close the main switch account modal
        this.close();
        
        // Show the contractor form modal
        const contractorFormModal = document.getElementById('switchToContractorModal');
        if (contractorFormModal) {
            contractorFormModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    showOwnerFormModal() {
        // Close the main switch account modal
        this.close();
        
        // Show the owner form modal
        const ownerFormModal = document.getElementById('switchToOwnerModal');
        if (ownerFormModal) {
            ownerFormModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    showNotification(message, type = 'info') {
        // Create toast notification
        const toast = document.createElement('div');
        let bgColor = '#EEA24B';
        if (type === 'success') {
            bgColor = '#10b981';
        } else if (type === 'error') {
            bgColor = '#ef4444';
        }
        
        toast.className = 'fixed bottom-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        toast.style.backgroundColor = bgColor;
        toast.style.zIndex = '9999';
        toast.textContent = message;
        toast.style.cssText += `
            animation: slideUp 0.3s ease-out;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
}

// Initialize when DOM is ready
let switchAccountModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    switchAccountModalInstance = new SwitchAccountModal();
    
    // Expose globally if needed
    window.openSwitchAccountModal = () => {
        if (switchAccountModalInstance) {
            switchAccountModalInstance.open();
        }
    };
    
    window.closeSwitchAccountModal = () => {
        if (switchAccountModalInstance) {
            switchAccountModalInstance.close();
        }
    };
});
