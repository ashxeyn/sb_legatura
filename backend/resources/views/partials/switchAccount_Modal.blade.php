<!-- Switch Account Modal -->
<div id="switchAccountModal" class="switch-account-modal">
    <div class="modal-overlay" id="switchAccountModalOverlay"></div>
    <div class="switch-account-modal-container">
        <!-- Modal Header -->
        <div class="switch-account-modal-header">
            <h2 class="switch-account-modal-title">
                <i class="fi fi-rr-refresh"></i>
                Switch Account
            </h2>
            <button class="switch-account-close-btn" id="closeSwitchAccountModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="switch-account-modal-body">
            <!-- Introduction Section -->
            <div class="switch-intro">
                <div class="switch-intro-icon">
                    <i class="fi fi-rr-user-gear"></i>
                </div>
                <h3 class="switch-intro-title">Choose Account Type</h3>
                <p class="switch-intro-description">
                    Select which account you'd like to switch to. Your data and progress will be saved.
                </p>
            </div>

            <!-- Account Options -->
            <div class="account-options">
                <!-- Property Owner Account Card -->
                <div class="account-option-card owner-card" id="switchToOwner" data-account-type="owner">
                    <div class="account-card-header">
                        <div class="account-card-icon owner-icon">
                            <i class="fi fi-rr-home"></i>
                        </div>
                        <span class="account-card-badge current" id="ownerCurrentBadge">Current Account</span>
                    </div>
                    <div class="account-card-body">
                        <h3 class="account-card-title">Property Owner</h3>
                        <p class="account-card-description">
                            Post construction projects, review bids from contractors, and manage ongoing work.
                        </p>
                        <ul class="account-card-features">
                            <li>
                                <i class="fi fi-rr-check"></i>
                                <span>Post unlimited projects</span>
                            </li>
                            <li>
                                <i class="fi fi-rr-check"></i>
                                <span>Review and compare contractor bids</span>
                            </li>
                            <li>
                                <i class="fi fi-rr-check"></i>
                                <span>Track project milestones</span>
                            </li>
                            <li>
                                <i class="fi fi-rr-check"></i>
                                <span>Manage payments securely</span>
                            </li>
                        </ul>
                    </div>
                    <div class="account-card-footer">
                        <button class="account-switch-btn owner-btn" data-target="owner">
                            <i class="fi fi-rr-arrow-right"></i>
                            <span>Switch to Property Owner</span>
                        </button>
                    </div>
                </div>

                <!-- Contractor Account Card -->
                <div class="account-option-card contractor-card" id="switchToContractor" data-account-type="contractor">
                    <div class="account-card-header">
                        <div class="account-card-icon contractor-icon">
                            <i class="fi fi-rr-hard-hat"></i>
                        </div>
                        <span class="account-card-badge current" id="contractorCurrentBadge">Current Account</span>
                    </div>
                    <div class="account-card-body">
                        <h3 class="account-card-title">Contractor</h3>
                        <p class="account-card-description">
                            Browse available projects, submit competitive bids, and manage your construction work.
                        </p>
                        <ul class="account-card-features">
                            <li>
                                <i class="fi fi-rr-check"></i>
                                <span>Browse available projects</span>
                            </li>
                            <li>
                                <i class="fi fi-rr-check"></i>
                                <span>Submit competitive bids</span>
                            </li>
                            <li>
                                <i class="fi fi-rr-check"></i>
                                <span>Showcase your portfolio</span>
                            </li>
                            <li>
                                <i class="fi fi-rr-check"></i>
                                <span>Receive secure payments</span>
                            </li>
                        </ul>
                    </div>
                    <div class="account-card-footer">
                        <button class="account-switch-btn contractor-btn" data-target="contractor">
                            <i class="fi fi-rr-arrow-right"></i>
                            <span>Switch to Contractor</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Information Note -->
            <div class="switch-info-note">
                <i class="fi fi-rr-info"></i>
                <div class="switch-info-content">
                    <p class="switch-info-title">Switching Accounts</p>
                    <p class="switch-info-text">
                        When you switch accounts, your current session will be saved. You can switch back anytime without losing any data.
                    </p>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="switch-account-modal-footer">
            <button type="button" class="switch-account-btn cancel-btn" id="cancelSwitchAccountBtn">
                <i class="fi fi-rr-cross"></i>
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Switch Confirmation Loading Overlay -->
<div id="switchAccountLoading" class="switch-loading-overlay hidden">
    <div class="switch-loading-content">
        <div class="switch-loading-spinner"></div>
        <h3 class="switch-loading-title">Switching Account...</h3>
        <p class="switch-loading-text">Please wait while we prepare your workspace.</p>
    </div>
</div>
