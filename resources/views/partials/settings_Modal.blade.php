<!-- Settings Modal -->
<div id="settingsModal" class="settings-modal">
    <div class="modal-overlay" id="settingsModalOverlay"></div>
    <div class="settings-modal-container">
        <!-- Modal Header -->
        <div class="settings-modal-header">
            <h2 class="settings-modal-title">
                <i class="fi fi-rr-settings"></i>
                Settings
            </h2>
            <button class="settings-close-btn" id="closeSettingsModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="settings-modal-body">
            <!-- Introduction -->
            <div class="settings-intro">
                <p class="settings-intro-text">
                    Customize your Legatura experience with these preferences and settings.
                </p>
            </div>

            <!-- Notifications Settings -->
            <div class="settings-section">
                <div class="settings-section-header">
                    <div class="settings-section-icon notifications-icon">
                        <i class="fi fi-rr-bell"></i>
                    </div>
                    <div class="settings-section-title-group">
                        <h3 class="settings-section-title">Notification Preferences</h3>
                        <p class="settings-section-subtitle">Manage how you receive notifications</p>
                    </div>
                </div>

                <div class="settings-options">
                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-envelope"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Email Notifications</span>
                                <span class="setting-option-description">Receive updates via email</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="emailNotifications" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-comment-alt"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">SMS Notifications</span>
                                <span class="setting-option-description">Get text message alerts</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="smsNotifications">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-megaphone"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Push Notifications</span>
                                <span class="setting-option-description">Browser push notifications</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="pushNotifications" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-briefcase"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Project Updates</span>
                                <span class="setting-option-description">Notifications about your projects</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="projectUpdates" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-comment-info"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Message Notifications</span>
                                <span class="setting-option-description">New message alerts</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="messageNotifications" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Display Preferences -->
            <div class="settings-section">
                <div class="settings-section-header">
                    <div class="settings-section-icon display-icon">
                        <i class="fi fi-rr-computer"></i>
                    </div>
                    <div class="settings-section-title-group">
                        <h3 class="settings-section-title">Display Preferences</h3>
                        <p class="settings-section-subtitle">Customize how Legatura looks</p>
                    </div>
                </div>

                <div class="settings-options">
                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-moon-stars"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Dark Mode</span>
                                <span class="setting-option-description">Switch to dark theme</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="darkMode">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option-select">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-world"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Language</span>
                                <span class="setting-option-description">Select your preferred language</span>
                            </div>
                        </div>
                        <select id="languageSelect" class="setting-select">
                            <option value="en" selected>English</option>
                            <option value="tl">Tagalog</option>
                            <option value="ceb">Cebuano</option>
                            <option value="ilo">Ilocano</option>
                        </select>
                    </div>

                    <div class="setting-option-select">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-text-size"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Font Size</span>
                                <span class="setting-option-description">Adjust text size for better readability</span>
                            </div>
                        </div>
                        <select id="fontSizeSelect" class="setting-select">
                            <option value="small">Small</option>
                            <option value="medium" selected>Medium</option>
                            <option value="large">Large</option>
                            <option value="xlarge">Extra Large</option>
                        </select>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-magic-wand"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Animations</span>
                                <span class="setting-option-description">Enable smooth transitions and effects</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="animations" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Privacy & Data Settings -->
            <div class="settings-section">
                <div class="settings-section-header">
                    <div class="settings-section-icon privacy-icon">
                        <i class="fi fi-rr-shield-check"></i>
                    </div>
                    <div class="settings-section-title-group">
                        <h3 class="settings-section-title">Privacy & Data</h3>
                        <p class="settings-section-subtitle">Control your privacy settings</p>
                    </div>
                </div>

                <div class="settings-options">
                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-eye"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Profile Visibility</span>
                                <span class="setting-option-description">Show your profile publicly</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="profileVisibility" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-user-time"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Show Online Status</span>
                                <span class="setting-option-description">Let others see when you're online</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="onlineStatus" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-chart-histogram"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Activity Tracking</span>
                                <span class="setting-option-description">Help us improve with usage data</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="activityTracking" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-cookie"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Cookie Preferences</span>
                                <span class="setting-option-description">Allow cookies for better experience</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="cookiePreferences" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Email Preferences -->
            <div class="settings-section">
                <div class="settings-section-header">
                    <div class="settings-section-icon email-icon">
                        <i class="fi fi-rr-envelope-open-text"></i>
                    </div>
                    <div class="settings-section-title-group">
                        <h3 class="settings-section-title">Email Preferences</h3>
                        <p class="settings-section-subtitle">Manage email communications</p>
                    </div>
                </div>

                <div class="settings-options">
                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-newspaper"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Newsletter</span>
                                <span class="setting-option-description">Receive monthly updates and tips</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="newsletter" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-badge-percent"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Promotional Emails</span>
                                <span class="setting-option-description">Special offers and promotions</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="promotionalEmails">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-users-alt"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Community Updates</span>
                                <span class="setting-option-description">News from the Legatura community</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="communityUpdates" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Application Settings -->
            <div class="settings-section">
                <div class="settings-section-header">
                    <div class="settings-section-icon app-icon">
                        <i class="fi fi-rr-apps"></i>
                    </div>
                    <div class="settings-section-title-group">
                        <h3 class="settings-section-title">Application Settings</h3>
                        <p class="settings-section-subtitle">Advanced application preferences</p>
                    </div>
                </div>

                <div class="settings-options">
                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-play"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Auto-play Videos</span>
                                <span class="setting-option-description">Automatically play video content</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="autoplayVideos">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-download"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Auto-download Updates</span>
                                <span class="setting-option-description">Download app updates automatically</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="autoDownloadUpdates" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-time-fast"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Quick Actions</span>
                                <span class="setting-option-description">Enable keyboard shortcuts</span>
                            </div>
                        </div>
                        <label class="setting-toggle">
                            <input type="checkbox" id="quickActions" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="setting-option-select">
                        <div class="setting-option-left">
                            <div class="setting-option-icon">
                                <i class="fi fi-rr-clock"></i>
                            </div>
                            <div class="setting-option-content">
                                <span class="setting-option-title">Time Zone</span>
                                <span class="setting-option-description">Set your local time zone</span>
                            </div>
                        </div>
                        <select id="timezoneSelect" class="setting-select">
                            <option value="Asia/Manila" selected>Manila (GMT+8)</option>
                            <option value="Asia/Tokyo">Tokyo (GMT+9)</option>
                            <option value="Asia/Singapore">Singapore (GMT+8)</option>
                            <option value="UTC">UTC (GMT+0)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Data Management -->
            <div class="settings-section danger-section">
                <div class="settings-section-header">
                    <div class="settings-section-icon data-icon">
                        <i class="fi fi-rr-database"></i>
                    </div>
                    <div class="settings-section-title-group">
                        <h3 class="settings-section-title">Data Management</h3>
                        <p class="settings-section-subtitle">Manage your data and cache</p>
                    </div>
                </div>

                <div class="settings-actions">
                    <button class="settings-action-btn secondary-btn" id="clearCacheBtn">
                        <i class="fi fi-rr-broom"></i>
                        <div class="action-btn-content">
                            <span class="action-btn-title">Clear Cache</span>
                            <span class="action-btn-description">Remove temporary files</span>
                        </div>
                    </button>

                    <button class="settings-action-btn secondary-btn" id="downloadDataBtn">
                        <i class="fi fi-rr-download"></i>
                        <div class="action-btn-content">
                            <span class="action-btn-title">Download My Data</span>
                            <span class="action-btn-description">Export your account data</span>
                        </div>
                    </button>

                    <button class="settings-action-btn danger-btn" id="deleteAccountBtn">
                        <i class="fi fi-rr-trash"></i>
                        <div class="action-btn-content">
                            <span class="action-btn-title">Delete Account</span>
                            <span class="action-btn-description">Permanently remove your account</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="settings-modal-footer">
            <button type="button" class="settings-btn reset-btn" id="resetSettingsBtn">
                <i class="fi fi-rr-refresh"></i>
                Reset to Default
            </button>
            <button type="button" class="settings-btn save-btn" id="saveSettingsBtn">
                <i class="fi fi-rr-check"></i>
                Save Changes
            </button>
        </div>
    </div>
</div>
