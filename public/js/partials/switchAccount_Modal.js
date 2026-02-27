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
        this.isPendingOwner = false; // Track if owner profile is pending
        this.isApprovedOwner = false; // Track if owner profile is approved for switch
        this.init();
    }

    init() {
        this.detectCurrentAccount();
        this.setupEventListeners();
        // UI will be updated after fetching status in open()
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

        // Owner switch logic moved to switchAccount_OWNER_Modal.js

        // Switch to Contractor button
        const switchToContractorBtn = document.querySelectorAll('[data-target="contractor"]');
        switchToContractorBtn.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                if (this.currentAccountType !== 'contractor') {
                    // Switch directly to contractor (assuming already exists for users in this switcher)
                    this.switchAccount('contractor');
                } else {
                    this.showNotification('You are already viewing the Contractor side', 'info');
                }
            });
        });

        // Card click to switch
        const ownerCard = document.getElementById('switchToOwner');
        const contractorCard = document.getElementById('switchToContractor');

        // Card click handling for owner moved to switchAccount_OWNER_Modal.js

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
        const ownerPendingBadge = document.getElementById('ownerPendingBadge');

        // Reset all
        if (ownerCard) ownerCard.classList.remove('current-account');
        if (contractorCard) contractorCard.classList.remove('current-account');
        if (ownerBadge) ownerBadge.style.display = 'none';
        if (contractorBadge) contractorBadge.style.display = 'none';
        if (ownerPendingBadge) ownerPendingBadge.style.display = 'none';

        // Set current
        if (this.currentAccountType === 'owner') {
            if (ownerCard) ownerCard.classList.add('current-account');
            if (ownerBadge) ownerBadge.style.display = 'block';
        } else if (this.currentAccountType === 'contractor') {
            if (contractorCard) contractorCard.classList.add('current-account');
            if (contractorBadge) contractorBadge.style.display = 'block';

            // Show pending badge if contractor has a pending owner profile
            if (this.isPendingOwner && ownerPendingBadge) {
                ownerPendingBadge.style.display = 'block';
            }
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

    async switchAccount(targetAccountType) {
        if (this.currentAccountType === targetAccountType) {
            this.showNotification('You are already on this account type', 'info');
            return;
        }

        // Show loading overlay
        this.showLoading();
        this.close();

        try {
            const response = await fetch('/api/role/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ role: targetAccountType })
            });

            const json = await response.json();

            if (json.success) {
                this.showNotification(json.message || `Switching to ${targetAccountType} account...`, 'success');
                setTimeout(() => {
                    window.location.href = json.redirect_url || (targetAccountType === 'owner' ? '/owner/homepage' : '/contractor/homepage');
                }, 1000);
            } else {
                this.hideLoading();
                this.showNotification(json.message || 'Failed to switch role', 'error');
            }
        } catch (err) {
            this.hideLoading();
            console.error('Role switch error:', err);
            this.showNotification('An error occurred during role switch', 'error');
        }
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

    async open() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Scroll to top of modal content
            const modalBody = this.modal.querySelector('.switch-account-modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }

            // Fetch account status to check for pending owner
            try {
                const resp = await fetch('/accounts/switch', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await resp.json();
                this.isPendingOwner = !!json.is_pending_owner;
                this.isApprovedOwner = !!json.is_approved_owner;
            } catch (err) {
                console.error('Failed to check account status:', err);
            }

            // Update UI based on current account and pending status
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
