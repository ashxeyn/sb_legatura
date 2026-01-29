<!-- Security Modal -->
<div id="securityModal" class="security-modal">
    <div class="modal-overlay" id="securityModalOverlay"></div>
    <div class="security-modal-container">
        <!-- Modal Header -->
        <div class="security-modal-header">
            <h2 class="security-modal-title">
                <i class="fi fi-rr-shield-check"></i>
                Security Settings
            </h2>
            <button class="security-close-btn" id="closeSecurityModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="security-modal-body">
            <!-- Introduction -->
            <div class="security-intro">
                <p class="security-intro-text">
                    Keep your account secure by regularly updating your password and contact information.
                </p>
            </div>

            <!-- Change Password Section -->
            <div class="security-section">
                <div class="security-section-header">
                    <div class="security-section-icon password-icon">
                        <i class="fi fi-rr-lock"></i>
                    </div>
                    <div class="security-section-title-group">
                        <h3 class="security-section-title">Change Password</h3>
                        <p class="security-section-subtitle">Update your password to keep your account secure</p>
                    </div>
                </div>

                <form id="changePasswordForm" class="security-form">
                    <div class="form-group">
                        <label for="currentPassword" class="form-label">
                            <i class="fi fi-rr-lock"></i>
                            Current Password
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="currentPassword" 
                                name="currentPassword"
                                class="form-input password-input"
                                placeholder="Enter your current password"
                                required
                            >
                            <button type="button" class="toggle-password-btn" data-target="currentPassword">
                                <i class="fi fi-rr-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="newPassword" class="form-label">
                            <i class="fi fi-rr-lock"></i>
                            New Password
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="newPassword" 
                                name="newPassword"
                                class="form-input password-input"
                                placeholder="Enter your new password"
                                required
                            >
                            <button type="button" class="toggle-password-btn" data-target="newPassword">
                                <i class="fi fi-rr-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar">
                                <div class="strength-bar-fill" id="strengthBarFill"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Enter a password</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword" class="form-label">
                            <i class="fi fi-rr-lock"></i>
                            Confirm New Password
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="confirmPassword" 
                                name="confirmPassword"
                                class="form-input password-input"
                                placeholder="Confirm your new password"
                                required
                            >
                            <button type="button" class="toggle-password-btn" data-target="confirmPassword">
                                <i class="fi fi-rr-eye"></i>
                            </button>
                        </div>
                        <p class="password-match-message" id="passwordMatchMessage"></p>
                    </div>

                    <div class="password-requirements">
                        <p class="requirements-title">Password Requirements:</p>
                        <ul class="requirements-list">
                            <li class="requirement-item" data-requirement="length">
                                <i class="fi fi-rr-cross-circle"></i>
                                <span>At least 8 characters</span>
                            </li>
                            <li class="requirement-item" data-requirement="uppercase">
                                <i class="fi fi-rr-cross-circle"></i>
                                <span>One uppercase letter</span>
                            </li>
                            <li class="requirement-item" data-requirement="lowercase">
                                <i class="fi fi-rr-cross-circle"></i>
                                <span>One lowercase letter</span>
                            </li>
                            <li class="requirement-item" data-requirement="number">
                                <i class="fi fi-rr-cross-circle"></i>
                                <span>One number</span>
                            </li>
                            <li class="requirement-item" data-requirement="special">
                                <i class="fi fi-rr-cross-circle"></i>
                                <span>One special character (!@#$%^&*)</span>
                            </li>
                        </ul>
                    </div>

                    <button type="submit" class="security-btn primary-btn">
                        <i class="fi fi-rr-check"></i>
                        Update Password
                    </button>
                </form>
            </div>

            <!-- Change Email & Contacts Section -->
            <div class="security-section">
                <div class="security-section-header">
                    <div class="security-section-icon contact-icon">
                        <i class="fi fi-rr-envelope"></i>
                    </div>
                    <div class="security-section-title-group">
                        <h3 class="security-section-title">Email & Contact Information</h3>
                        <p class="security-section-subtitle">Update your email address and contact details</p>
                    </div>
                </div>

                <form id="changeContactForm" class="security-form">
                    <!-- Email Section -->
                    <div class="form-subsection">
                        <h4 class="form-subsection-title">
                            <i class="fi fi-rr-envelope"></i>
                            Email Address
                        </h4>
                        
                        <div class="current-info-display">
                            <div class="info-label">Current Email:</div>
                            <div class="info-value" id="currentEmailDisplay">john.doe@example.com</div>
                        </div>

                        <div class="form-group">
                            <label for="newEmail" class="form-label">
                                New Email Address
                            </label>
                            <input 
                                type="email" 
                                id="newEmail" 
                                name="newEmail"
                                class="form-input"
                                placeholder="Enter your new email address"
                            >
                        </div>

                        <div class="form-group">
                            <label for="confirmEmail" class="form-label">
                                Confirm New Email
                            </label>
                            <input 
                                type="email" 
                                id="confirmEmail" 
                                name="confirmEmail"
                                class="form-input"
                                placeholder="Confirm your new email address"
                            >
                            <p class="email-match-message" id="emailMatchMessage"></p>
                        </div>
                    </div>

                    <!-- Contact Number Section -->
                    <div class="form-subsection">
                        <h4 class="form-subsection-title">
                            <i class="fi fi-rr-phone-call"></i>
                            Contact Number
                        </h4>
                        
                        <div class="current-info-display">
                            <div class="info-label">Current Contact:</div>
                            <div class="info-value" id="currentContactDisplay">+63 912 345 6789</div>
                        </div>

                        <div class="form-group">
                            <label for="newContact" class="form-label">
                                New Contact Number
                            </label>
                            <input 
                                type="tel" 
                                id="newContact" 
                                name="newContact"
                                class="form-input"
                                placeholder="Enter your new contact number (e.g., 09123456789)"
                                pattern="[0-9]{11}"
                            >
                            <p class="form-hint">Format: 11 digits (e.g., 09123456789)</p>
                        </div>
                    </div>

                    <!-- Verification Notice -->
                    <div class="verification-notice">
                        <i class="fi fi-rr-info"></i>
                        <div class="verification-notice-content">
                            <p class="verification-notice-title">Verification Required</p>
                            <p class="verification-notice-text">
                                After updating your email or contact number, you'll receive a verification code to confirm the changes.
                            </p>
                        </div>
                    </div>

                    <!-- Password Confirmation for Contact Changes -->
                    <div class="form-group">
                        <label for="contactPasswordConfirm" class="form-label">
                            <i class="fi fi-rr-lock"></i>
                            Confirm Your Password
                        </label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="contactPasswordConfirm" 
                                name="contactPasswordConfirm"
                                class="form-input password-input"
                                placeholder="Enter your password to confirm changes"
                                required
                            >
                            <button type="button" class="toggle-password-btn" data-target="contactPasswordConfirm">
                                <i class="fi fi-rr-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="security-btn primary-btn">
                        <i class="fi fi-rr-check"></i>
                        Update Contact Information
                    </button>
                </form>
            </div>

            <!-- Security Tips -->
            <div class="security-tips">
                <h4 class="security-tips-title">
                    <i class="fi fi-rr-bulb"></i>
                    Security Tips
                </h4>
                <ul class="security-tips-list">
                    <li>
                        <i class="fi fi-rr-check"></i>
                        <span>Use a unique password that you don't use anywhere else</span>
                    </li>
                    <li>
                        <i class="fi fi-rr-check"></i>
                        <span>Change your password every 3-6 months</span>
                    </li>
                    <li>
                        <i class="fi fi-rr-check"></i>
                        <span>Never share your password with anyone</span>
                    </li>
                    <li>
                        <i class="fi fi-rr-check"></i>
                        <span>Keep your email and contact information up to date</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="security-modal-footer">
            <button type="button" class="security-btn cancel-btn" id="cancelSecurityBtn">
                <i class="fi fi-rr-cross"></i>
                Close
            </button>
        </div>
    </div>
</div>

<!-- Verification Code Modal -->
<div id="verificationModal" class="verification-modal hidden">
    <div class="modal-overlay" id="verificationModalOverlay"></div>
    <div class="verification-modal-container">
        <div class="verification-modal-header">
            <div class="verification-icon-wrapper">
                <i class="fi fi-rr-shield-check"></i>
            </div>
            <h3 class="verification-modal-title">Verify Your Changes</h3>
            <button class="verification-close-btn" id="closeVerificationModalBtn">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>
        <div class="verification-modal-body">
            <p class="verification-message" id="verificationMessage">
                We've sent a verification code to your new email/phone number.
            </p>
            <div class="verification-code-inputs">
                <input type="text" class="code-input" maxlength="1" data-index="0">
                <input type="text" class="code-input" maxlength="1" data-index="1">
                <input type="text" class="code-input" maxlength="1" data-index="2">
                <input type="text" class="code-input" maxlength="1" data-index="3">
                <input type="text" class="code-input" maxlength="1" data-index="4">
                <input type="text" class="code-input" maxlength="1" data-index="5">
            </div>
            <button class="resend-code-btn" id="resendCodeBtn">
                <i class="fi fi-rr-refresh"></i>
                Resend Code
            </button>
        </div>
        <div class="verification-modal-footer">
            <button type="button" class="verification-btn cancel-btn" id="cancelVerificationBtn">
                Cancel
            </button>
            <button type="button" class="verification-btn verify-btn" id="verifyCodeBtn">
                <i class="fi fi-rr-check"></i>
                Verify
            </button>
        </div>
    </div>
</div>
