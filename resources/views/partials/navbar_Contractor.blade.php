<nav class="navbar-container">
    <!-- Top Bar -->
    <div class="navbar-top">
        <div class="navbar-grid">

            <!-- Column 2-3: Logo -->
            <div class="navbar-col navbar-col-2">
                <a href="/" class="navbar-logo" aria-label="Legatura Home">
                    <img src="{{ asset('img/legatura2.0.svg') }}" alt="Legatura" class="navbar-logo-img">
                </a>
            </div>

            <!-- Column 4-8: Search Bar -->
            <div class="navbar-col navbar-col-5">
                <div class="navbar-search">
                    <input type="text" class="navbar-search-input" placeholder="Search..." aria-label="Search">
                    <button class="navbar-search-btn" aria-label="Search button">
                        <i class="fi fi-rr-search"></i>
                    </button>
                </div>
            </div>

            <!-- Column 9-12: User Profile & Actions -->
            <div class="navbar-col navbar-col-4">
                <div class="navbar-right">
                    @php
                        // Use session('user') only (array or object)
                        $sess = session('user');
                        $user = null;
                        if ($sess) {
                            $user = is_object($sess) ? $sess : (object)$sess;
                        }

                        // Fetch subscription data directly for navbar logic (moved from AppServiceProvider)
                        try {
                            $modalData = \App\Http\Controllers\subs\platformPaymentController::shareModalData();
                            $subscription = $modalData['subscription'] ?? null;
                        } catch (\Exception $e) {
                            $subscription = null;
                        }

                        $displayName = $user->company_name ?? $user->name ?? $user->username ?? 'Contractor';
                        $rawHandle = $user->username ?? $user->user_name ?? null;
                        $usernameHandle = $rawHandle ? ('@' . ltrim($rawHandle, '@')) : '@contractor';
                        $profilePic = $user->profile_pic ?? null;

                        $initials = 'C';
                        if (is_string($displayName) && trim($displayName) !== '') {
                            $parts = preg_split('/\s+/', trim($displayName));
                            $initials = strtoupper(substr(($parts[0] ?? '') . ($parts[1] ?? ''), 0, 2)) ?: 'C';
                        }
                    @endphp
                    <div class="navbar-user">
                        @if($profilePic)
                            <div class="navbar-avatar">
                                <img src="{{ asset('storage/' . $profilePic) }}" alt="{{ $displayName }}" class="navbar-avatar-img">
                            </div>
                        @else
                            <div class="navbar-avatar navbar-avatar-initials">{{ $initials }}</div>
                        @endif
                        <div class="navbar-user-info">
                            <span class="navbar-user-name">{{ $displayName }}</span>
                            <span class="navbar-user-role">{{ $usernameHandle }}</span>
                        </div>
                    </div>
                    <div class="navbar-notification-container">
                        <button class="navbar-notification" id="notificationBellBtn" aria-label="Notifications">
                            <i class="fi fi-rr-bell"></i>
                            <span class="notification-badge hidden" id="notificationBadge"></span>
                        </button>
                        <div class="notification-dropdown hidden" id="notificationDropdown">
                            <div class="notification-dropdown-content">
                                <div class="notification-dropdown-header">
                                    <h3 class="notification-dropdown-title">Notifications</h3>
                                    <button type="button" class="notification-close-btn" id="notificationCloseBtn" aria-label="Close notifications">
                                        <i class="fi fi-rr-cross-small"></i>
                                    </button>
                                </div>
                                <div class="notification-tabs">
                                    <button class="notification-tab active" data-tab="all" id="notificationTabAll">
                                        All notification
                                    </button>
                                    <button class="notification-tab" data-tab="projects" id="notificationTabProjects">
                                        Projects
                                    </button>
                                    <button class="notification-tab" data-tab="bids" id="notificationTabBids">
                                        Bids
                                    </button>
                                </div>
                                <div class="notification-dropdown-body">
                                    <div class="notification-list" id="notificationListAll">
                                        <!-- All notifications will be inserted here -->
                                    </div>
                                    <div class="notification-list hidden" id="notificationListProjects">
                                        <!-- Project notifications will be inserted here -->
                                    </div>
                                    <div class="notification-list hidden" id="notificationListBids">
                                        <!-- Bid notifications will be inserted here -->
                                    </div>
                                </div>
                                <div class="notification-dropdown-footer">
                                    <button type="button" class="btn-mark-all-read" id="markAllReadBtn">
                                        <i class="fi fi-rr-check"></i>
                                        Mark as read
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="navbar-menu-toggle" id="userMenuToggle" aria-label="User menu">
                        <i class="fi fi-rr-menu-dots-vertical"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="navbar-links">
        <a href="{{ route('contractor.homepage') }}" class="navbar-link active">Home</a>
        <a href="{{ route('contractor.dashboard') }}" class="navbar-link">Dashboard</a>
        <a href="{{ route('contractor.messages') }}" class="navbar-link">Messages</a>
        @if(isset($subscription) && isset($subscription['plan_key']) && strtolower($subscription['plan_key']) === 'gold')
            <a href="{{ route('contractor.ai-analytics') }}" class="navbar-link">AI Analytics</a>
        @endif
        <a href="{{ route('contractor.profile') }}" class="navbar-link">Profile</a>
    </div>

    <!-- User Menu Dropdown (Hidden by default) -->
    <div class="navbar-user-menu hidden" id="userMenuDropdown">
        <a href="#" class="navbar-menu-item" id="accountLink">Account</a>
        <a href="#" class="navbar-menu-item" id="logoutLink">Logout</a>
    </div>

    <!-- Account Settings Modal -->
    <div id="accountSettingsModal" class="account-settings-modal">
        <div class="modal-overlay" id="accountSettingsModalOverlay"></div>
        <div class="account-settings-modal-container">
            <!-- Modal Header -->
            <div class="account-settings-modal-header">
                <h2 class="account-settings-modal-title">
                    <i class="fi fi-rr-user"></i>
                    Account Settings
                </h2>
                <button class="account-settings-close-btn" id="closeAccountSettingsModalBtn" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="account-settings-modal-body">
                <div class="account-settings-menu">
                    <a href="{{ route('contractor.profile') }}" class="account-settings-item">
                        <div class="account-settings-item-left">
                            <div class="account-settings-icon profile-icon">
                                <i class="fi fi-rr-user"></i>
                            </div>
                            <div class="account-settings-item-content">
                                <span class="account-settings-item-title">View Profile</span>
                                <span class="account-settings-item-subtitle">View your public profile</span>
                            </div>
                        </div>
                        <i class="fi fi-rr-angle-right account-settings-arrow"></i>
                    </a>

                    <a href="#" class="account-settings-item" id="editProfileLink">
                        <div class="account-settings-item-left">
                            <div class="account-settings-icon edit-icon">
                                <i class="fi fi-rr-edit"></i>
                            </div>
                            <div class="account-settings-item-content">
                                <span class="account-settings-item-title">Edit profile information</span>
                                <span class="account-settings-item-subtitle">Update your personal details</span>
                            </div>
                        </div>
                        <i class="fi fi-rr-angle-right account-settings-arrow"></i>
                    </a>

                    <div class="account-settings-item account-settings-item-toggle">
                        <div class="account-settings-item-left">
                            <div class="account-settings-icon notification-icon">
                                <i class="fi fi-rr-bell"></i>
                            </div>
                            <div class="account-settings-item-content">
                                <span class="account-settings-item-title">Notifications</span>
                                <span class="account-settings-item-subtitle">Enable or disable notifications</span>
                            </div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="notificationsToggle" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <a href="#" class="account-settings-item" id="switchAccountLink">
                        <div class="account-settings-item-left">
                            <div class="account-settings-icon switch-icon">
                                <i class="fi fi-rr-refresh"></i>
                            </div>
                            <div class="account-settings-item-content">
                                <span class="account-settings-item-title">Switch to Property Owner Account</span>
                                <span class="account-settings-item-subtitle">Change your account type</span>
                            </div>
                        </div>
                        <i class="fi fi-rr-angle-right account-settings-arrow"></i>
                    </a>

                    <a href="#" class="account-settings-item" id="subscriptionLink">
                        <div class="account-settings-item-left">
                            <div class="account-settings-icon subscription-icon">
                                <i class="fi fi-rr-crown"></i>
                            </div>
                            <div class="account-settings-item-content">
                                <span class="account-settings-item-title">Subscription</span>
                                <span class="account-settings-item-subtitle">Manage your subscription plan</span>
                            </div>
                        </div>
                        <i class="fi fi-rr-angle-right account-settings-arrow"></i>
                    </a>

                    <a href="#" class="account-settings-item" id="securityLink">
                        <div class="account-settings-item-left">
                            <div class="account-settings-icon security-icon">
                                <i class="fi fi-rr-shield-check"></i>
                            </div>
                            <div class="account-settings-item-content">
                                <span class="account-settings-item-title">Security</span>
                                <span class="account-settings-item-subtitle">Manage your account security</span>
                            </div>
                        </div>
                        <i class="fi fi-rr-angle-right account-settings-arrow"></i>
                    </a>

                    <a href="#" class="account-settings-item" id="settingsLink">
                        <div class="account-settings-item-left">
                            <div class="account-settings-icon settings-icon">
                                <i class="fi fi-rr-settings"></i>
                            </div>
                            <div class="account-settings-item-content">
                                <span class="account-settings-item-title">Settings</span>
                                <span class="account-settings-item-subtitle">Configure your preferences</span>
                            </div>
                        </div>
                        <i class="fi fi-rr-angle-right account-settings-arrow"></i>
                    </a>

                    <a href="#" class="account-settings-item" id="helpSupportLink">
                        <div class="account-settings-item-left">
                            <div class="account-settings-icon help-icon">
                                <i class="fi fi-rr-interrogation"></i>
                            </div>
                            <div class="account-settings-item-content">
                                <span class="account-settings-item-title">Help & Support</span>
                                <span class="account-settings-item-subtitle">Get help and support</span>
                            </div>
                        </div>
                        <i class="fi fi-rr-angle-right account-settings-arrow"></i>
                    </a>

                    <a href="#" class="account-settings-item" id="contactUsLink">
                        <div class="account-settings-item-left">
                            <div class="account-settings-icon contact-icon">
                                <i class="fi fi-rr-envelope"></i>
                            </div>
                            <div class="account-settings-item-content">
                                <span class="account-settings-item-title">Contact us</span>
                                <span class="account-settings-item-subtitle">Get in touch with us</span>
                            </div>
                        </div>
                        <i class="fi fi-rr-angle-right account-settings-arrow"></i>
                    </a>

                    <a href="#" class="account-settings-item" id="privacyPolicyLink">
                        <div class="account-settings-item-left">
                            <div class="account-settings-icon privacy-icon">
                                <i class="fi fi-rr-lock"></i>
                            </div>
                            <div class="account-settings-item-content">
                                <span class="account-settings-item-title">Privacy policy</span>
                                <span class="account-settings-item-subtitle">Read our privacy policy</span>
                            </div>
                        </div>
                        <i class="fi fi-rr-angle-right account-settings-arrow"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutConfirmationModal" class="confirmation-modal hidden">
        <div class="modal-overlay" id="logoutConfirmationModalOverlay"></div>
        <div class="confirmation-modal-container">
            <!-- Modal Header -->
            <div class="confirmation-modal-header">
                <div class="confirmation-icon-wrapper">
                    <div class="confirmation-icon">
                        <i class="fi fi-rr-sign-out-alt"></i>
                    </div>
                </div>
                <h2 class="confirmation-modal-title">Confirm Logout</h2>
                <button class="confirmation-close-btn" id="closeLogoutConfirmationModalBtn" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="confirmation-modal-body">
                <p class="confirmation-message" id="logoutConfirmationMessage">
                    Are you sure you want to logout?
                </p>
                <p class="confirmation-submessage" id="logoutConfirmationSubmessage">
                    You will need to login again to access your account.
                </p>
            </div>

            <!-- Modal Footer -->
            <div class="confirmation-modal-footer">
                <button class="confirmation-btn cancel-btn" id="cancelLogoutBtn">
                    Cancel
                </button>
                <button class="confirmation-btn confirm-btn" id="confirmLogoutBtn">
                    <i class="fi fi-rr-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </div>
    </div>
</nav>

<form id="logoutForm" action="/accounts/logout" method="POST" class="hidden">
    @csrf
</form>
