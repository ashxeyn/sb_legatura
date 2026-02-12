/**
 * Navbar JavaScript
 * Handles notification dropdown and user menu interactions
 */

class Navbar {
    constructor() {
        this.notifications = [];
        this.currentTab = 'all';
        this.init();
    }

    init() {
        this.loadNotifications();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Notification bell button
        const notificationBellBtn = document.getElementById('notificationBellBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationCloseBtn = document.getElementById('notificationCloseBtn');

        if (notificationBellBtn && notificationDropdown) {
            notificationBellBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleNotificationDropdown();
            });
        }

        if (notificationCloseBtn) {
            notificationCloseBtn.addEventListener('click', () => {
                this.closeNotificationDropdown();
            });
        }

        // Notification tabs
        const notificationTabs = document.querySelectorAll('.notification-tab');
        notificationTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabName = tab.getAttribute('data-tab');
                this.switchNotificationTab(tabName);
            });
        });

        // Mark all as read button
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                this.markAllAsRead();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (notificationDropdown &&
                !notificationDropdown.contains(e.target) &&
                notificationBellBtn &&
                !notificationBellBtn.contains(e.target)) {
                this.closeNotificationDropdown();
            }
        });

        // User menu toggle (existing functionality)
        const userMenuToggle = document.getElementById('userMenuToggle');
        const userMenuDropdown = document.getElementById('userMenuDropdown');

        if (userMenuToggle && userMenuDropdown) {
            userMenuToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenuDropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', (e) => {
                if (!userMenuToggle.contains(e.target) && !userMenuDropdown.contains(e.target)) {
                    userMenuDropdown.classList.add('hidden');
                }
            });
        }

        // Account settings modal
        const accountLink = document.getElementById('accountLink');
        const accountSettingsModal = document.getElementById('accountSettingsModal');
        const accountSettingsModalOverlay = document.getElementById('accountSettingsModalOverlay');
        const closeAccountSettingsModalBtn = document.getElementById('closeAccountSettingsModalBtn');
        const notificationsToggle = document.getElementById('notificationsToggle');

        if (accountLink) {
            accountLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.showAccountSettingsModal();
            });
        }

        if (closeAccountSettingsModalBtn) {
            closeAccountSettingsModalBtn.addEventListener('click', () => {
                this.closeAccountSettingsModal();
            });
        }

        if (accountSettingsModalOverlay) {
            accountSettingsModalOverlay.addEventListener('click', () => {
                this.closeAccountSettingsModal();
            });
        }

        if (notificationsToggle) {
            notificationsToggle.addEventListener('change', (e) => {
                this.handleNotificationsToggle(e.target.checked);
            });
        }

        // Account settings menu item clicks
        const editProfileLink = document.getElementById('editProfileLink');
        const switchAccountLink = document.getElementById('switchAccountLink');
        const securityLink = document.getElementById('securityLink');
        const settingsLink = document.getElementById('settingsLink');
        const helpSupportLink = document.getElementById('helpSupportLink');
        const contactUsLink = document.getElementById('contactUsLink');
        const privacyPolicyLink = document.getElementById('privacyPolicyLink');

        if (editProfileLink) {
            editProfileLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleEditProfile();
            });
        }

        if (switchAccountLink) {
            switchAccountLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSwitchAccount();
            });
        }

        const boostLink = document.getElementById('boostLink');
        if (boostLink) {
            boostLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleBoost();
            });
        }

        if (securityLink) {
            securityLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSecurity();
            });
        }

        if (settingsLink) {
            settingsLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSettings();
            });
        }

        if (helpSupportLink) {
            helpSupportLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleHelpSupport();
            });
        }

        if (contactUsLink) {
            contactUsLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleContactUs();
            });
        }

        if (privacyPolicyLink) {
            privacyPolicyLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handlePrivacyPolicy();
            });
        }

        // Close account settings modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && accountSettingsModal && accountSettingsModal.classList.contains('active')) {
                this.closeAccountSettingsModal();
            }
        });

        // Logout confirmation modal
        const logoutLink = document.getElementById('logoutLink');
        const logoutModal = document.getElementById('logoutConfirmationModal');
        const logoutModalOverlay = document.getElementById('logoutConfirmationModalOverlay');
        const closeLogoutModalBtn = document.getElementById('closeLogoutConfirmationModalBtn');
        const cancelLogoutBtn = document.getElementById('cancelLogoutBtn');
        const confirmLogoutBtn = document.getElementById('confirmLogoutBtn');

        if (logoutLink) {
            logoutLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.showLogoutConfirmationModal();
            });
        }

        if (closeLogoutModalBtn) {
            closeLogoutModalBtn.addEventListener('click', () => {
                this.closeLogoutConfirmationModal();
            });
        }

        if (logoutModalOverlay) {
            logoutModalOverlay.addEventListener('click', () => {
                this.closeLogoutConfirmationModal();
            });
        }

        if (cancelLogoutBtn) {
            cancelLogoutBtn.addEventListener('click', () => {
                this.closeLogoutConfirmationModal();
            });
        }

        if (confirmLogoutBtn) {
            confirmLogoutBtn.addEventListener('click', () => {
                this.handleLogout();
            });
        }

        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && logoutModal && logoutModal.classList.contains('active')) {
                this.closeLogoutConfirmationModal();
            }
        });
    }

    showAccountSettingsModal() {
        const accountSettingsModal = document.getElementById('accountSettingsModal');
        const userMenuDropdown = document.getElementById('userMenuDropdown');

        if (accountSettingsModal) {
            accountSettingsModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close user menu dropdown if open
        if (userMenuDropdown) {
            userMenuDropdown.classList.add('hidden');
        }
    }

    closeAccountSettingsModal() {
        const accountSettingsModal = document.getElementById('accountSettingsModal');
        if (accountSettingsModal) {
            accountSettingsModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    handleNotificationsToggle(enabled) {
        // In a real implementation, save notification preference to server
        console.log('Notifications:', enabled ? 'enabled' : 'disabled');
        this.showNotification(`Notifications ${enabled ? 'enabled' : 'disabled'}`, enabled ? 'success' : 'info');
    }

    handleEditProfile() {
        this.closeAccountSettingsModal();
        // Open edit profile modal
        if (window.openEditProfileModal) {
            window.openEditProfileModal();
        } else {
            this.showNotification('Opening edit profile...', 'info');
        }
    }

    handleSwitchAccount() {
        this.closeAccountSettingsModal();
        // In a real implementation, show confirmation and switch account type
        this.showNotification('Switching to Contractor Account...', 'info');
        // window.location.href = '/contractor/dashboard';
    }

    handleBoost() {
        this.closeAccountSettingsModal();
        // In a real implementation, open boost modal or navigate to boost page
        this.showNotification('Opening boost options...', 'info');
        // window.location.href = '/owner/boost';
    }

    handleSecurity() {
        this.closeAccountSettingsModal();
        // In a real implementation, navigate to security settings
        this.showNotification('Opening security settings...', 'info');
        // window.location.href = '/owner/security';
    }

    handleSettings() {
        this.closeAccountSettingsModal();
        // In a real implementation, navigate to settings page
        this.showNotification('Opening settings...', 'info');
        // window.location.href = '/owner/settings';
    }

    handleHelpSupport() {
        this.closeAccountSettingsModal();
        // In a real implementation, navigate to help & support page
        this.showNotification('Opening help & support...', 'info');
        // window.location.href = '/help-support';
    }

    handleContactUs() {
        this.closeAccountSettingsModal();
        // In a real implementation, navigate to contact page or open modal
        this.showNotification('Opening contact form...', 'info');
        // window.location.href = '/contact';
    }

    handlePrivacyPolicy() {
        this.closeAccountSettingsModal();
        // In a real implementation, navigate to privacy policy page
        this.showNotification('Opening privacy policy...', 'info');
        // window.location.href = '/privacy-policy';
    }

    showLogoutConfirmationModal() {
        const logoutModal = document.getElementById('logoutConfirmationModal');
        if (logoutModal) {
            logoutModal.classList.remove('hidden');
            logoutModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closeLogoutConfirmationModal() {
        const logoutModal = document.getElementById('logoutConfirmationModal');
        if (logoutModal) {
            logoutModal.classList.remove('active');
            logoutModal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    handleLogout() {
        // Show loading state and submit hidden logout form (server will redirect)
        const confirmBtn = document.getElementById('confirmLogoutBtn');
        if (confirmBtn) {
            confirmBtn.dataset.origHtml = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fi fi-rr-spinner animate-spin"></i> Logging out...';
            confirmBtn.disabled = true;
        }

        const form = document.getElementById('logoutForm');
        // Show immediate toast so user sees confirmation even if redirect fails
        this.showToast('Successfully logged out', 'success');
        if (form) {
            try {
                form.submit();
                setTimeout(() => { window.location.href = '/accounts/login'; }, 200);
                return;
            } catch (e) {
                console.warn('form.submit() failed, will use fetch fallback', e);
            }
        }

        // Fallback: use fetch with CSRF token
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : null;
        fetch('/accounts/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({})
        }).then(resp => {
            window.location.href = '/accounts/login';
        }).catch(err => {
            console.error('Logout fetch failed', err);
            window.location.href = '/accounts/login';
        });
    }

    showToast(message, type = 'info', duration = 3500) {
        try {
            const toast = document.createElement('div');
            toast.className = 'site-toast site-toast-' + type;
            toast.style.position = 'fixed';
            toast.style.right = '20px';
            toast.style.top = '20px';
            toast.style.zIndex = 9999;
            toast.style.padding = '12px 16px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 2px 10px rgba(0,0,0,0.12)';
            toast.style.background = type === 'success' ? '#16a34a' : '#374151';
            toast.style.color = '#fff';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px)';
                setTimeout(() => toast.remove(), 350);
            }, duration);
        } catch (e) {
            console.debug('showToast failed', e);
        }
    }

    loadNotifications() {
        // Sample notifications data - Replace with actual API call
        this.notifications = [
            {
                id: 1,
                type: 'project',
                title: 'New Project Update',
                message: 'Milestone 2 has been completed for "Modern Residential House Construction"',
                time: '5 minutes ago',
                read: false
            },
            {
                id: 2,
                type: 'bid',
                title: 'New Bid Received',
                message: 'You received a new bid from Panda Construction Company for "Commercial Building Renovation"',
                time: '1 hour ago',
                read: false
            },
            {
                id: 3,
                type: 'project',
                title: 'Payment Received',
                message: 'Payment for Milestone 1 of "Luxury Villa Construction" has been received',
                time: '2 hours ago',
                read: true
            },
            {
                id: 4,
                type: 'bid',
                title: 'Bid Accepted',
                message: 'Your bid for "Apartment Complex Development" has been accepted',
                time: '3 hours ago',
                read: false
            },
            {
                id: 5,
                type: 'project',
                title: 'Project Started',
                message: 'Construction has started for "Office Building Construction"',
                time: '1 day ago',
                read: true
            },
            {
                id: 6,
                type: 'bid',
                title: 'Bid Rejected',
                message: 'Your bid for "Residential Complex" was not selected',
                time: '2 days ago',
                read: true
            }
        ];

        this.updateNotificationBadge();
        this.renderNotifications();
    }

    toggleNotificationDropdown() {
        const notificationDropdown = document.getElementById('notificationDropdown');
        if (notificationDropdown) {
            notificationDropdown.classList.toggle('hidden');
            notificationDropdown.classList.toggle('active');
        }
    }

    closeNotificationDropdown() {
        const notificationDropdown = document.getElementById('notificationDropdown');
        if (notificationDropdown) {
            notificationDropdown.classList.add('hidden');
            notificationDropdown.classList.remove('active');
        }
    }

    switchNotificationTab(tabName) {
        this.currentTab = tabName;

        // Update tab buttons
        const tabs = document.querySelectorAll('.notification-tab');
        tabs.forEach(tab => {
            tab.classList.remove('active');
            if (tab.getAttribute('data-tab') === tabName) {
                tab.classList.add('active');
            }
        });

        // Update notification lists
        const allList = document.getElementById('notificationListAll');
        const projectsList = document.getElementById('notificationListProjects');
        const bidsList = document.getElementById('notificationListBids');

        if (allList) allList.classList.add('hidden');
        if (projectsList) projectsList.classList.add('hidden');
        if (bidsList) bidsList.classList.add('hidden');

        switch(tabName) {
            case 'all':
                if (allList) allList.classList.remove('hidden');
                break;
            case 'projects':
                if (projectsList) projectsList.classList.remove('hidden');
                break;
            case 'bids':
                if (bidsList) bidsList.classList.remove('hidden');
                break;
        }

        this.renderNotifications();
    }

    renderNotifications() {
        let filteredNotifications = [];

        switch(this.currentTab) {
            case 'all':
                filteredNotifications = [...this.notifications];
                break;
            case 'projects':
                filteredNotifications = this.notifications.filter(n => n.type === 'project');
                break;
            case 'bids':
                filteredNotifications = this.notifications.filter(n => n.type === 'bid');
                break;
        }

        // Render for current tab
        let list;
        switch(this.currentTab) {
            case 'all':
                list = document.getElementById('notificationListAll');
                break;
            case 'projects':
                list = document.getElementById('notificationListProjects');
                break;
            case 'bids':
                list = document.getElementById('notificationListBids');
                break;
        }

        if (!list) return;

        list.innerHTML = '';

        if (filteredNotifications.length === 0) {
            list.innerHTML = `
                <div class="notification-empty-state">
                    <i class="fi fi-rr-bell"></i>
                    <p>No notifications</p>
                </div>
            `;
            return;
        }

        filteredNotifications.forEach((notification, index) => {
            const notificationItem = this.createNotificationItem(notification);
            list.appendChild(notificationItem);

            // Staggered animation
            setTimeout(() => {
                notificationItem.style.opacity = '0';
                notificationItem.style.transform = 'translateX(-10px)';
                notificationItem.style.transition = 'all 0.3s ease';

                requestAnimationFrame(() => {
                    notificationItem.style.opacity = '1';
                    notificationItem.style.transform = 'translateX(0)';
                });
            }, index * 50);
        });
    }

    createNotificationItem(notification) {
        const item = document.createElement('div');
        item.className = `notification-item ${notification.read ? '' : 'unread'}`;
        item.setAttribute('data-notification-id', notification.id);

        const iconClass = notification.type === 'project' ? 'project' :
                         notification.type === 'bid' ? 'bid' : 'general';
        const icon = notification.type === 'project' ? 'fi-rr-briefcase' :
                    notification.type === 'bid' ? 'fi-rr-handshake' : 'fi-rr-bell';

        item.innerHTML = `
            <div class="notification-icon ${iconClass}">
                <i class="fi ${icon}"></i>
            </div>
            <div class="notification-content">
                <h4 class="notification-title">${notification.title}</h4>
                <p class="notification-message">${notification.message}</p>
                <span class="notification-time">${notification.time}</span>
            </div>
        `;

        // Add click handler
        item.addEventListener('click', () => {
            this.markAsRead(notification.id);
        });

        return item;
    }

    markAsRead(notificationId) {
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification && !notification.read) {
            notification.read = true;
            this.updateNotificationBadge();
            this.renderNotifications();
        }
    }

    markAllAsRead() {
        this.notifications.forEach(notification => {
            notification.read = true;
        });
        this.updateNotificationBadge();
        this.renderNotifications();
        this.showNotification('All notifications marked as read', 'success');
    }

    updateNotificationBadge() {
        const badge = document.getElementById('notificationBadge');
        const unreadCount = this.notifications.filter(n => !n.read).length;

        if (badge) {
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }

    showNotification(message, type = 'info') {
        // Simple notification - can be enhanced
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new Navbar();
});
