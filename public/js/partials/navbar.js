/**
 * Navbar JavaScript
 * Handles notification dropdown and user menu interactions
 */

class Navbar {
    constructor() {
        this.notifications = [];
        this.currentTab = 'all';
        this.pollingInterval = null;
        this.init();
    }

    init() {
        this.loadNotifications();
        this.setupEventListeners();
        this.startPolling();
    }

    startPolling() {
        // Poll for new notifications every 5 seconds
        this.pollingInterval = setInterval(() => {
            this.loadNotifications(true); // true = silent load (no UI flickering)
        }, 5000);
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
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

        const subscriptionLink = document.getElementById('subscriptionLink');
        if (subscriptionLink) {
            subscriptionLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSubscription();
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
        // this.showNotification('Opening boost options...', 'info');
        // window.location.href = '/owner/boost';
        const boostModal = document.getElementById('boostModal');
        if (boostModal) boostModal.classList.add('active');
    }

    handleSubscription() {
        this.closeAccountSettingsModal();
        const subscriptionModal = document.getElementById('subscriptionModal');
        if (subscriptionModal) subscriptionModal.classList.add('active');
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
        console.debug('handleLogout called, logout form:', form);
        // Show immediate toast so user sees confirmation even if redirect fails
        this.showToast('Successfully logged out', 'success');
        if (form) {
            try {
                form.submit();
                // Ensure user is redirected to login in case the native submit doesn't navigate (some setups)
                setTimeout(() => { window.location.href = '/accounts/login'; }, 200);
                return;
            } catch (e) {
                console.warn('form.submit() failed, will use fetch fallback', e);
            }
        }

        // If no form or submit failed, use fetch fallback
        this.fetchLogoutFallback();
    }

    fetchLogoutFallback() {
        console.debug('fetchLogoutFallback invoked');
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : null;
        const payload = {};
        fetch('/accounts/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        }).then(resp => {
            if (resp.redirected) {
                window.location.href = resp.url;
                return;
            }
            // On success, force navigate to login
            window.location.href = '/accounts/login';
        }).catch(err => {
            console.error('Logout fetch failed', err);
            // As last resort, navigate to login
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

    async loadNotifications(silent = false) {
        try {
            const response = await fetch('/notifications/json', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Failed to load notifications');
            }

            const data = await response.json();

            // Format notifications for frontend
            this.notifications = ((data.data && data.data.notifications) || []).map(notif => {
                return {
                    id: notif.id,
                    type: this.mapNotificationTypeToCategory(notif.type, notif.title, notif.reference_type),
                    title: notif.title || 'Notification',
                    message: notif.message,
                    time: this.formatTime(notif.created_at),
                    read: notif.is_read,
                    priority: notif.priority || 'normal'
                };
            });

            this.updateNotificationBadge();
            if (!silent) {
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            if (!silent) {
                this.showToast('Failed to load notifications', 'error');
            }
        }
    }

    mapNotificationTypeToCategory(type, title = '', referenceType = '') {
        // Check if it's a message notification by title or reference type
        if (title.includes('💬') || title.includes('Message') || referenceType === 'conversation') {
            return 'message';
        }

        // Map backend notification types to frontend categories for filtering
        const typeMap = {
            'Bid Status': 'bid',
            'Project Alert': 'project',
            'Milestone Update': 'project',
            'Progress Update': 'project',
            'Payment Status': 'payment',
            'Payment Reminder': 'payment',
            'Dispute Update': 'project'
        };
        return typeMap[type] || 'project';
    }

    formatTime(timestamp) {
        if (!timestamp) return 'Just now';

        const now = new Date();
        const notifDate = new Date(timestamp);
        const diffMs = now - notifDate;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
        if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

        return notifDate.toLocaleDateString();
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
        const paymentsList = document.getElementById('notificationListPayments');
        const messagesList = document.getElementById('notificationListMessages');

        if (allList) allList.classList.add('hidden');
        if (projectsList) projectsList.classList.add('hidden');
        if (bidsList) bidsList.classList.add('hidden');
        if (paymentsList) paymentsList.classList.add('hidden');
        if (messagesList) messagesList.classList.add('hidden');

        switch (tabName) {
            case 'all':
                if (allList) allList.classList.remove('hidden');
                break;
            case 'projects':
                if (projectsList) projectsList.classList.remove('hidden');
                break;
            case 'bids':
                if (bidsList) bidsList.classList.remove('hidden');
                break;
            case 'payments':
                if (paymentsList) paymentsList.classList.remove('hidden');
                break;
            case 'messages':
                if (messagesList) messagesList.classList.remove('hidden');
                break;
        }

        this.renderNotifications();
    }

    renderNotifications() {
        let filteredNotifications = [];

        switch (this.currentTab) {
            case 'all':
                filteredNotifications = [...this.notifications];
                break;
            case 'projects':
                filteredNotifications = this.notifications.filter(n => n.type === 'project');
                break;
            case 'bids':
                filteredNotifications = this.notifications.filter(n => n.type === 'bid');
                break;
            case 'payments':
                filteredNotifications = this.notifications.filter(n => n.type === 'payment');
                break;
            case 'messages':
                filteredNotifications = this.notifications.filter(n => n.type === 'message');
                break;
        }

        // Render for current tab
        let list;
        switch (this.currentTab) {
            case 'all':
                list = document.getElementById('notificationListAll');
                break;
            case 'projects':
                list = document.getElementById('notificationListProjects');
                break;
            case 'bids':
                list = document.getElementById('notificationListBids');
                break;
            case 'payments':
                list = document.getElementById('notificationListPayments');
                break;
            case 'messages':
                list = document.getElementById('notificationListMessages');
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
        item.style.cursor = 'pointer';

        const iconClass = notification.type === 'project' ? 'project' :
            notification.type === 'bid' ? 'bid' :
            notification.type === 'payment' ? 'payment' :
            notification.type === 'message' ? 'message' : 'general';
        const icon = notification.type === 'project' ? 'fi-rr-briefcase' :
            notification.type === 'bid' ? 'fi-rr-handshake' :
            notification.type === 'payment' ? 'fi-rr-wallet' :
            notification.type === 'message' ? 'fi-rr-comment' : 'fi-rr-bell';

        // Priority indicator
        const priorityClass = notification.priority === 'critical' ? 'notification-critical' :
                             notification.priority === 'high' ? 'notification-high' : '';

        item.innerHTML = `
            <div class="notification-icon ${iconClass}">
                <i class="fi ${icon}"></i>
            </div>
            <div class="notification-content">
                <h4 class="notification-title ${priorityClass}">${notification.title}</h4>
                <p class="notification-message">${notification.message}</p>
                <span class="notification-time">${notification.time}</span>
            </div>
        `;

        // Add click handler to redirect
        item.addEventListener('click', () => {
            this.handleNotificationClick(notification.id);
        });

        return item;
    }

    async handleNotificationClick(notificationId) {
        try {
            // Close dropdown immediately for better UX
            this.closeNotificationDropdown();

            // Redirect via backend endpoint which marks as read and returns proper URL
            window.location.href = `/notifications/${notificationId}/redirect`;
        } catch (error) {
            console.error('Error handling notification click:', error);
            this.showToast('Failed to open notification', 'error');
        }
    }

    async markAsRead(notificationId) {
        try {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

            const response = await fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Failed to mark as read');
            }

            // Update local state
            const notification = this.notifications.find(n => n.id === notificationId);
            if (notification) {
                notification.read = true;
                this.updateNotificationBadge();
                this.renderNotifications();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

            const response = await fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Failed to mark all as read');
            }

            // Update local state
            this.notifications.forEach(notification => {
                notification.read = true;
            });
            this.updateNotificationBadge();
            this.renderNotifications();
            this.showToast('All notifications marked as read', 'success');
        } catch (error) {
            console.error('Error marking all as read:', error);
            this.showToast('Failed to mark notifications as read', 'error');
        }
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
