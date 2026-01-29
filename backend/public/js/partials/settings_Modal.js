/**
 * Settings Modal JavaScript
 * Handles Legatura website settings and preferences
 */

class SettingsModal {
    constructor() {
        this.modal = document.getElementById('settingsModal');
        this.overlay = document.getElementById('settingsModalOverlay');
        
        // Default settings
        this.defaultSettings = {
            // Notifications
            emailNotifications: true,
            smsNotifications: false,
            pushNotifications: true,
            projectUpdates: true,
            messageNotifications: true,
            
            // Display
            darkMode: false,
            language: 'en',
            fontSize: 'medium',
            animations: true,
            
            // Privacy
            profileVisibility: true,
            onlineStatus: true,
            activityTracking: true,
            cookiePreferences: true,
            
            // Email Preferences
            newsletter: true,
            promotionalEmails: false,
            communityUpdates: true,
            
            // Application
            autoplayVideos: false,
            autoDownloadUpdates: true,
            quickActions: true,
            timezone: 'Asia/Manila'
        };
        
        this.currentSettings = { ...this.defaultSettings };
        
        this.init();
    }

    init() {
        this.loadSettings();
        this.setupEventListeners();
        this.applySettings();
    }

    setupEventListeners() {
        // Open modal from navbar
        const settingsLink = document.getElementById('settingsLink');
        if (settingsLink) {
            settingsLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.open();
                
                // Close account settings modal if open
                const accountSettingsModal = document.getElementById('accountSettingsModal');
                if (accountSettingsModal && accountSettingsModal.classList.contains('show')) {
                    accountSettingsModal.classList.remove('show');
                }
            });
        }

        // Close buttons
        const closeBtn = document.getElementById('closeSettingsModalBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        // Overlay click
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }

        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        });

        // Save settings button
        const saveBtn = document.getElementById('saveSettingsBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveSettings());
        }

        // Reset settings button
        const resetBtn = document.getElementById('resetSettingsBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetSettings());
        }

        // Toggle listeners
        this.setupToggleListeners();

        // Select listeners
        this.setupSelectListeners();

        // Action buttons
        this.setupActionButtons();
    }

    setupToggleListeners() {
        const toggleIds = [
            'emailNotifications', 'smsNotifications', 'pushNotifications',
            'projectUpdates', 'messageNotifications', 'darkMode', 'animations',
            'profileVisibility', 'onlineStatus', 'activityTracking',
            'cookiePreferences', 'newsletter', 'promotionalEmails',
            'communityUpdates', 'autoplayVideos', 'autoDownloadUpdates', 'quickActions'
        ];

        toggleIds.forEach(id => {
            const toggle = document.getElementById(id);
            if (toggle) {
                toggle.addEventListener('change', (e) => {
                    this.currentSettings[id] = e.target.checked;
                    
                    // Apply specific settings immediately
                    if (id === 'darkMode') {
                        this.toggleDarkMode(e.target.checked);
                    } else if (id === 'animations') {
                        this.toggleAnimations(e.target.checked);
                    }
                });
            }
        });
    }

    setupSelectListeners() {
        const languageSelect = document.getElementById('languageSelect');
        if (languageSelect) {
            languageSelect.addEventListener('change', (e) => {
                this.currentSettings.language = e.target.value;
                this.changeLanguage(e.target.value);
            });
        }

        const fontSizeSelect = document.getElementById('fontSizeSelect');
        if (fontSizeSelect) {
            fontSizeSelect.addEventListener('change', (e) => {
                this.currentSettings.fontSize = e.target.value;
                this.changeFontSize(e.target.value);
            });
        }

        const timezoneSelect = document.getElementById('timezoneSelect');
        if (timezoneSelect) {
            timezoneSelect.addEventListener('change', (e) => {
                this.currentSettings.timezone = e.target.value;
            });
        }
    }

    setupActionButtons() {
        // Clear Cache
        const clearCacheBtn = document.getElementById('clearCacheBtn');
        if (clearCacheBtn) {
            clearCacheBtn.addEventListener('click', () => this.clearCache());
        }

        // Download Data
        const downloadDataBtn = document.getElementById('downloadDataBtn');
        if (downloadDataBtn) {
            downloadDataBtn.addEventListener('click', () => this.downloadData());
        }

        // Delete Account
        const deleteAccountBtn = document.getElementById('deleteAccountBtn');
        if (deleteAccountBtn) {
            deleteAccountBtn.addEventListener('click', () => this.deleteAccount());
        }
    }

    loadSettings() {
        // Load settings from localStorage
        const savedSettings = localStorage.getItem('legaturaSettings');
        if (savedSettings) {
            try {
                this.currentSettings = { ...this.defaultSettings, ...JSON.parse(savedSettings) };
            } catch (e) {
                console.error('Failed to load settings:', e);
                this.currentSettings = { ...this.defaultSettings };
            }
        }

        // Update UI with loaded settings
        this.updateUI();
    }

    updateUI() {
        // Update toggles
        Object.keys(this.currentSettings).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = this.currentSettings[key];
                } else if (element.tagName === 'SELECT') {
                    element.value = this.currentSettings[key];
                }
            }
        });
    }

    applySettings() {
        // Apply dark mode
        if (this.currentSettings.darkMode) {
            this.toggleDarkMode(true);
        }

        // Apply font size
        this.changeFontSize(this.currentSettings.fontSize);

        // Apply animations
        this.toggleAnimations(this.currentSettings.animations);
    }

    saveSettings() {
        try {
            // Save to localStorage
            localStorage.setItem('legaturaSettings', JSON.stringify(this.currentSettings));
            
            // In production, also save to backend
            // await this.saveToBackend(this.currentSettings);
            
            this.showNotification('Settings saved successfully!', 'success');
            
            // Apply settings
            this.applySettings();
            
            // Close modal after a short delay
            setTimeout(() => {
                this.close();
            }, 1500);
        } catch (e) {
            console.error('Failed to save settings:', e);
            this.showNotification('Failed to save settings. Please try again.', 'error');
        }
    }

    resetSettings() {
        if (confirm('Are you sure you want to reset all settings to default?')) {
            this.currentSettings = { ...this.defaultSettings };
            this.updateUI();
            this.applySettings();
            this.showNotification('Settings reset to default', 'success');
        }
    }

    toggleDarkMode(enabled) {
        if (enabled) {
            document.body.classList.add('dark-mode');
            this.showNotification('Dark mode enabled', 'info');
        } else {
            document.body.classList.remove('dark-mode');
            this.showNotification('Dark mode disabled', 'info');
        }
    }

    changeFontSize(size) {
        // Remove existing font size classes
        document.body.classList.remove('font-small', 'font-medium', 'font-large', 'font-xlarge');
        
        // Add new font size class
        document.body.classList.add(`font-${size}`);
    }

    toggleAnimations(enabled) {
        if (enabled) {
            document.body.classList.remove('no-animations');
        } else {
            document.body.classList.add('no-animations');
        }
    }

    changeLanguage(lang) {
        // In production, implement actual language change
        const languageNames = {
            'en': 'English',
            'tl': 'Tagalog',
            'ceb': 'Cebuano',
            'ilo': 'Ilocano'
        };
        
        this.showNotification(`Language changed to ${languageNames[lang]}`, 'info');
        
        // In production, reload page or update translations
        // window.location.reload();
    }

    async clearCache() {
        try {
            this.showNotification('Clearing cache...', 'info');
            
            // Simulate cache clearing
            await this.simulateAPICall();
            
            // Clear some localStorage items (except settings)
            const settingsBackup = localStorage.getItem('legaturaSettings');
            localStorage.clear();
            if (settingsBackup) {
                localStorage.setItem('legaturaSettings', settingsBackup);
            }
            
            this.showNotification('Cache cleared successfully!', 'success');
        } catch (e) {
            this.showNotification('Failed to clear cache', 'error');
        }
    }

    async downloadData() {
        try {
            this.showNotification('Preparing your data download...', 'info');
            
            // Simulate data preparation
            await this.simulateAPICall();
            
            // In production, make API call to download user data
            // const response = await fetch('/api/download-user-data');
            // const blob = await response.blob();
            // const url = window.URL.createObjectURL(blob);
            // const a = document.createElement('a');
            // a.href = url;
            // a.download = 'legatura-user-data.zip';
            // a.click();
            
            this.showNotification('Data download will be sent to your email', 'success');
        } catch (e) {
            this.showNotification('Failed to prepare data download', 'error');
        }
    }

    deleteAccount() {
        const confirmed = confirm(
            'WARNING: This action cannot be undone.\n\n' +
            'Are you absolutely sure you want to permanently delete your account?\n\n' +
            'All your data, projects, and messages will be lost forever.'
        );
        
        if (confirmed) {
            const doubleConfirm = prompt(
                'Type "DELETE" (in capital letters) to confirm account deletion:'
            );
            
            if (doubleConfirm === 'DELETE') {
                this.showNotification('Account deletion initiated. You will be logged out shortly.', 'error');
                
                // In production, make API call to delete account
                // await fetch('/api/delete-account', { method: 'DELETE' });
                
                setTimeout(() => {
                    // Redirect to goodbye page or logout
                    // window.location.href = '/logout';
                }, 3000);
            } else {
                this.showNotification('Account deletion cancelled', 'info');
            }
        }
    }

    simulateAPICall() {
        return new Promise((resolve) => {
            setTimeout(resolve, 1000);
        });
    }

    open() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Scroll to top
            const modalBody = this.modal.querySelector('.settings-modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }

            // Load current settings
            this.updateUI();
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

    showNotification(message, type = 'info') {
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
let settingsModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    settingsModalInstance = new SettingsModal();
    
    // Expose globally if needed
    window.openSettingsModal = () => {
        if (settingsModalInstance) {
            settingsModalInstance.open();
        }
    };
    
    window.closeSettingsModal = () => {
        if (settingsModalInstance) {
            settingsModalInstance.close();
        }
    };
});
