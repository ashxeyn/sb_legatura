/**
 * Security Modal JavaScript
 * Tabbed UI matching mobile changeOtpScreen.tsx flow.
 * Handles password, email, and contact changes with OTP verification.
 * Connected to OTPChangeController backend endpoints.
 */

class SecurityModal {
    constructor() {
        this.modal = document.getElementById('securityModal');
        this.overlay = document.getElementById('securityModalOverlay');
        this.verificationModal = document.getElementById('verificationModal');

        // Read backend URLs and user data from data attributes
        this.sendOtpUrl = this.modal?.dataset.sendOtpUrl || '';
        this.verifyOtpUrl = this.modal?.dataset.verifyOtpUrl || '';
        this.csrfToken = this.modal?.dataset.csrfToken || '';

        // Unified form
        this.form = document.getElementById('securityChangeForm');

        // Field groups
        this.emailFields = document.getElementById('emailChangeFields');
        this.contactFields = document.getElementById('contactChangeFields');
        this.passwordFields = document.getElementById('passwordChangeFields');

        // Email change fields
        this.emailCurrentPassword = document.getElementById('emailCurrentPassword');
        this.newEmail = document.getElementById('newEmail');

        // Contact change fields
        this.newContact = document.getElementById('newContact');

        // Password change fields
        this.newPassword = document.getElementById('newPassword');
        this.confirmPassword = document.getElementById('confirmPassword');

        // Password strength
        this.strengthBar = document.getElementById('strengthBarFill');
        this.strengthText = document.getElementById('strengthText');

        // Messages
        this.passwordMatchMessage = document.getElementById('passwordMatchMessage');

        // Section header elements
        this.sectionIcon = document.getElementById('securitySectionIcon');
        this.sectionIconI = document.getElementById('securitySectionIconI');
        this.sectionTitle = document.getElementById('securitySectionTitle');
        this.sectionSubtitle = document.getElementById('securitySectionSubtitle');
        this.verificationNoticeText = document.getElementById('verificationNoticeText');
        this.submitBtn = document.getElementById('securitySubmitBtn');

        // OTP state (mirrors mobile changeOtpScreen.tsx flow)
        this.otpToken = null;
        this.maskedDest = null;
        this.activePurpose = 'change_email';   // default tab
        this.pendingNewValue = null;
        this.pendingNewPassword = null;  // separate storage for password (mobile sends email as new_value in send, password in verify)
        this.ttlSeconds = 900;  // default, overridden by server response
        this.countdownTimer = null;
        this.secondsLeft = 0;

        // Current user data
        this.currentUserData = {
            email: this.modal?.dataset.userEmail || '',
            contact: this.modal?.dataset.userContact || ''
        };

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadCurrentUserData();
        this.switchPurpose('change_email');
    }

    // ─── Event Listeners ──────────────────────────────────────────────

    setupEventListeners() {
        // Open modal from navbar
        const securityLink = document.getElementById('securityLink');
        if (securityLink) {
            securityLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.open();
                const accountSettingsModal = document.getElementById('accountSettingsModal');
                if (accountSettingsModal && accountSettingsModal.classList.contains('show')) {
                    accountSettingsModal.classList.remove('show');
                }
            });
        }

        // Close buttons
        const closeBtn = document.getElementById('closeSecurityModalBtn');
        const cancelBtn = document.getElementById('cancelSecurityBtn');
        if (closeBtn) closeBtn.addEventListener('click', () => this.close());
        if (cancelBtn) cancelBtn.addEventListener('click', () => this.close());
        if (this.overlay) this.overlay.addEventListener('click', () => this.close());

        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen()) this.close();
        });

        // Purpose tab switching
        document.querySelectorAll('.security-purpose-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                const purpose = tab.dataset.purpose;
                if (purpose) this.switchPurpose(purpose);
            });
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.getAttribute('data-target'));
                const icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fi-rr-eye', 'fi-rr-eye-crossed');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fi-rr-eye-crossed', 'fi-rr-eye');
                }
            });
        });

        // Password strength checker
        if (this.newPassword) {
            this.newPassword.addEventListener('input', () => {
                this.checkPasswordStrength();
                this.checkPasswordMatch();
            });
        }
        if (this.confirmPassword) {
            this.confirmPassword.addEventListener('input', () => this.checkPasswordMatch());
        }

        // Form submission
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleSubmit();
            });
        }

        // Verification modal
        this.setupVerificationModal();
    }

    // ─── Tab Switching ────────────────────────────────────────────────

    switchPurpose(purpose) {
        this.activePurpose = purpose;

        // Update tab active state
        document.querySelectorAll('.security-purpose-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.purpose === purpose);
        });

        // Show/hide field groups
        if (this.emailFields) this.emailFields.style.display = purpose === 'change_email' ? '' : 'none';
        if (this.contactFields) this.contactFields.style.display = purpose === 'change_contact' ? '' : 'none';
        if (this.passwordFields) this.passwordFields.style.display = purpose === 'change_password' ? '' : 'none';

        // Update section header
        const configs = {
            change_email: {
                iconClass: 'contact-icon',
                iconI: 'fi fi-rr-envelope',
                title: 'Change Email',
                subtitle: 'We will send an OTP to your new email to confirm the change.',
                notice: 'After submitting, you\'ll receive a verification code at your new email address.',
            },
            change_contact: {
                iconClass: 'contact-icon',
                iconI: 'fi fi-rr-phone-call',
                title: 'Change Contact Number',
                subtitle: 'We will send an OTP to your email to confirm the contact change.',
                notice: 'After submitting, you\'ll receive a verification code at your registered email.',
            },
            change_password: {
                iconClass: 'password-icon',
                iconI: 'fi fi-rr-lock',
                title: 'Change Password',
                subtitle: 'We will send an OTP to your email to confirm the password change.',
                notice: 'After submitting, you\'ll receive a verification code at your registered email.',
            }
        };

        const cfg = configs[purpose] || configs.change_email;

        if (this.sectionIcon) {
            this.sectionIcon.className = 'security-section-icon ' + cfg.iconClass;
        }
        if (this.sectionIconI) {
            this.sectionIconI.className = cfg.iconI;
        }
        if (this.sectionTitle) this.sectionTitle.textContent = cfg.title;
        if (this.sectionSubtitle) this.sectionSubtitle.textContent = cfg.subtitle;
        if (this.verificationNoticeText) this.verificationNoticeText.textContent = cfg.notice;

        // Reset form fields when switching tabs
        this.resetFormFields();
    }

    resetFormFields() {
        // Reset email fields
        if (this.emailCurrentPassword) this.emailCurrentPassword.value = '';
        if (this.newEmail) this.newEmail.value = '';

        // Reset contact fields
        if (this.newContact) this.newContact.value = '';

        // Reset password fields
        if (this.newPassword) this.newPassword.value = '';
        if (this.confirmPassword) this.confirmPassword.value = '';
        if (this.passwordMatchMessage) {
            this.passwordMatchMessage.textContent = '';
            this.passwordMatchMessage.className = 'password-match-message';
        }

        // Reset password strength
        if (this.strengthBar) this.strengthBar.className = 'strength-bar-fill';
        if (this.strengthText) {
            this.strengthText.textContent = 'Enter a password';
            this.strengthText.className = 'strength-text';
        }
        this.resetPasswordRequirements();
    }

    setupVerificationModal() {
        const closeVerificationBtn = document.getElementById('closeVerificationModalBtn');
        const cancelVerificationBtn = document.getElementById('cancelVerificationBtn');
        const verifyCodeBtn = document.getElementById('verifyCodeBtn');
        const resendCodeBtn = document.getElementById('resendCodeBtn');

        if (closeVerificationBtn) closeVerificationBtn.addEventListener('click', () => this.closeVerificationModal());
        if (cancelVerificationBtn) cancelVerificationBtn.addEventListener('click', () => this.closeVerificationModal());
        if (verifyCodeBtn) verifyCodeBtn.addEventListener('click', () => this.verifyCode());
        if (resendCodeBtn) resendCodeBtn.addEventListener('click', () => this.resendVerificationCode());

        // Code input auto-focus & paste support
        const codeInputs = document.querySelectorAll('.code-input');
        codeInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                if (e.target.value.length === 1 && index < codeInputs.length - 1) {
                    codeInputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    codeInputs[index - 1].focus();
                }
            });
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasted = (e.clipboardData.getData('text') || '').replace(/[^0-9]/g, '');
                for (let i = 0; i < codeInputs.length && i < pasted.length; i++) {
                    codeInputs[i].value = pasted[i];
                }
                const nextIdx = Math.min(pasted.length, codeInputs.length - 1);
                codeInputs[nextIdx].focus();
            });
        });
    }

    // ─── Current User Data ────────────────────────────────────────────

    loadCurrentUserData() {
        const currentContactDisplay = document.getElementById('currentContactDisplay');
        const registeredEmailDisplay = document.getElementById('registeredEmailDisplay');

        if (currentContactDisplay) {
            currentContactDisplay.textContent = this.currentUserData.contact || 'Not set';
        }
        if (registeredEmailDisplay) {
            registeredEmailDisplay.textContent = this.currentUserData.email || 'Not set';
        }
    }

    // ─── Password Strength ───────────────────────────────────────────

    checkPasswordStrength() {
        const password = this.newPassword.value;

        if (!password) {
            this.strengthBar.className = 'strength-bar-fill';
            this.strengthText.textContent = 'Enter a password';
            this.strengthText.className = 'strength-text';
            this.resetPasswordRequirements();
            return;
        }

        // Match mobile rules: length, uppercase, number, special (no lowercase)
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        Object.keys(requirements).forEach(req => {
            const item = document.querySelector(`[data-requirement="${req}"]`);
            if (item) {
                item.classList.toggle('met', requirements[req]);
                const icon = item.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fi-rr-check-circle', requirements[req]);
                    icon.classList.toggle('fi-rr-cross-circle', !requirements[req]);
                }
            }
        });

        const metCount = Object.values(requirements).filter(Boolean).length;
        if (metCount <= 1) {
            this.strengthBar.className = 'strength-bar-fill weak';
            this.strengthText.textContent = 'Weak';
            this.strengthText.className = 'strength-text weak';
        } else if (metCount <= 3) {
            this.strengthBar.className = 'strength-bar-fill medium';
            this.strengthText.textContent = 'Medium';
            this.strengthText.className = 'strength-text medium';
        } else {
            this.strengthBar.className = 'strength-bar-fill strong';
            this.strengthText.textContent = 'Strong';
            this.strengthText.className = 'strength-text strong';
        }
    }

    resetPasswordRequirements() {
        document.querySelectorAll('.requirement-item').forEach(item => {
            item.classList.remove('met');
            const icon = item.querySelector('i');
            if (icon) {
                icon.classList.remove('fi-rr-check-circle');
                icon.classList.add('fi-rr-cross-circle');
            }
        });
    }

    checkPasswordMatch() {
        const newPwd = this.newPassword.value;
        const confirmPwd = this.confirmPassword.value;

        if (!confirmPwd) {
            this.passwordMatchMessage.textContent = '';
            this.passwordMatchMessage.className = 'password-match-message';
            return;
        }
        if (newPwd === confirmPwd) {
            this.passwordMatchMessage.textContent = '✓ Passwords match';
            this.passwordMatchMessage.className = 'password-match-message match';
        } else {
            this.passwordMatchMessage.textContent = '✗ Passwords do not match';
            this.passwordMatchMessage.className = 'password-match-message no-match';
        }
    }

    // ─── API Helper ───────────────────────────────────────────────────

    async postJson(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify(body)
        });

        // Handle non-JSON responses (CSRF 419, session timeout redirect, 500 error page)
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            // 419 = CSRF token mismatch (session expired)
            if (res.status === 419) {
                return { status: 419, success: false, message: 'Session expired. Please refresh the page and try again.' };
            }
            return { status: res.status, success: false, message: 'Server error. Please refresh the page and try again.' };
        }

        const json = await res.json();
        return { status: res.status, ...json };
    }

    // ─── Unified Form Submit ──────────────────────────────────────────

    async handleSubmit() {
        switch (this.activePurpose) {
            case 'change_email':
                return this.handleEmailChange();
            case 'change_contact':
                return this.handleContactChange();
            case 'change_password':
                return this.handlePasswordChange();
        }
    }

    // ─── Email Change Flow (matches mobile: current password + new email) ──

    async handleEmailChange() {
        const currentPwd = this.emailCurrentPassword?.value?.trim();
        const newEmailVal = this.newEmail?.value?.trim();

        if (!currentPwd) {
            this.showNotification('Please enter your current password', 'error');
            return;
        }
        if (!newEmailVal) {
            this.showNotification('Please enter your new email address', 'error');
            return;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(newEmailVal)) {
            this.showNotification('Please enter a valid email address', 'error');
            return;
        }

        this.pendingNewValue = newEmailVal;
        this.setFormLoading(true);

        try {
            const data = await this.postJson(this.sendOtpUrl, {
                purpose: 'change_email',
                new_value: newEmailVal,
                current_password: currentPwd
            });

            if (!data.success) {
                this.showNotification(data.message || 'Failed to send verification code', 'error');
                this.setFormLoading(false);
                return;
            }

            this.otpToken = data.otp_token;
            this.maskedDest = data.masked;
            if (data.ttl_seconds) this.ttlSeconds = data.ttl_seconds;
            this.setFormLoading(false);
            this.openVerificationModal();
        } catch (err) {
            console.error('Email change send OTP error:', err);
            this.showNotification('Network error. Please try again.', 'error');
            this.setFormLoading(false);
        }
    }

    // ─── Contact Change Flow (matches mobile: current contact disabled + new contact) ──

    async handleContactChange() {
        const newContactVal = this.newContact?.value?.trim();

        if (!newContactVal) {
            this.showNotification('Please enter your new contact number', 'error');
            return;
        }
        if (!/^[0-9]{11}$/.test(newContactVal)) {
            this.showNotification('Please enter a valid 11-digit contact number', 'error');
            return;
        }

        this.pendingNewValue = newContactVal;
        this.setFormLoading(true);

        try {
            // OTP is sent to the user's registered email (not the phone)
            const data = await this.postJson(this.sendOtpUrl, {
                purpose: 'change_contact',
                new_value: newContactVal,
                destination: this.currentUserData.email
            });

            if (!data.success) {
                this.showNotification(data.message || 'Failed to send verification code', 'error');
                this.setFormLoading(false);
                return;
            }

            this.otpToken = data.otp_token;
            this.maskedDest = data.masked;
            if (data.ttl_seconds) this.ttlSeconds = data.ttl_seconds;
            this.setFormLoading(false);
            this.openVerificationModal();
        } catch (err) {
            console.error('Contact change send OTP error:', err);
            this.showNotification('Network error. Please try again.', 'error');
            this.setFormLoading(false);
        }
    }

    // ─── Password Change Flow (matches mobile: registered email disabled + new/confirm pw) ──

    async handlePasswordChange() {
        const newPwd = this.newPassword?.value;
        const confirmPwd = this.confirmPassword?.value;

        if (!newPwd || !confirmPwd) {
            this.showNotification('Please enter and confirm your new password', 'error');
            return;
        }
        if (newPwd !== confirmPwd) {
            this.showNotification('Passwords do not match', 'error');
            return;
        }

        // Check password requirements (matching mobile: length, uppercase, number, special)
        const reqs = {
            length: newPwd.length >= 8,
            uppercase: /[A-Z]/.test(newPwd),
            number: /[0-9]/.test(newPwd),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(newPwd)
        };
        if (!Object.values(reqs).every(Boolean)) {
            this.showNotification('Password does not meet all requirements', 'error');
            return;
        }

        // Match mobile: send user's email as new_value (destination), store password separately for verify
        const userEmail = this.currentUserData.email;
        if (!userEmail) {
            this.showNotification('Registered email not available', 'error');
            return;
        }

        this.pendingNewValue = userEmail;
        this.pendingNewPassword = newPwd;
        this.setFormLoading(true);

        try {
            // Match mobile: { purpose: 'change_password', new_value: userEmail }
            const data = await this.postJson(this.sendOtpUrl, {
                purpose: 'change_password',
                new_value: userEmail
            });

            if (!data.success) {
                this.showNotification(data.message || 'Failed to send verification code', 'error');
                this.setFormLoading(false);
                return;
            }

            this.otpToken = data.otp_token;
            this.maskedDest = data.masked;
            if (data.ttl_seconds) this.ttlSeconds = data.ttl_seconds;
            this.setFormLoading(false);
            this.openVerificationModal();
        } catch (err) {
            console.error('Password change send OTP error:', err);
            this.showNotification('Network error. Please try again.', 'error');
            this.setFormLoading(false);
        }
    }

    // ─── Verification Modal ──────────────────────────────────────────

    openVerificationModal() {
        if (!this.verificationModal) return;

        const message = document.getElementById('verificationMessage');
        if (message) {
            const dest = this.maskedDest || 'your registered email';
            if (this.activePurpose === 'change_password') {
                message.textContent = `We've sent a verification code to ${dest} to confirm your password change.`;
            } else if (this.activePurpose === 'change_email') {
                message.textContent = `We've sent a verification code to ${dest}. Enter it below to confirm your new email.`;
            } else if (this.activePurpose === 'change_contact') {
                message.textContent = `We've sent a verification code to ${dest}. Enter it below to confirm your new contact number.`;
            }
        }

        // Clear previous OTP input
        document.querySelectorAll('.code-input').forEach(input => { input.value = ''; });

        this.verificationModal.classList.remove('hidden');

        // Focus first input
        const firstInput = this.verificationModal.querySelector('.code-input');
        if (firstInput) setTimeout(() => firstInput.focus(), 100);

        // Start countdown using server-provided TTL
        this.startCountdown(this.ttlSeconds || 900);
    }

    closeVerificationModal() {
        if (this.verificationModal) {
            this.verificationModal.classList.add('hidden');
            document.querySelectorAll('.code-input').forEach(input => { input.value = ''; });
        }
        this.stopCountdown();
    }

    startCountdown(seconds) {
        this.stopCountdown();
        this.secondsLeft = seconds;
        const resendBtn = document.getElementById('resendCodeBtn');

        this.countdownTimer = setInterval(() => {
            this.secondsLeft--;
            if (resendBtn) {
                if (this.secondsLeft > 0) {
                    const mins = Math.floor(this.secondsLeft / 60);
                    const secs = this.secondsLeft % 60;
                    resendBtn.innerHTML = `<i class="fi fi-rr-refresh"></i> Resend Code (${mins}:${String(secs).padStart(2, '0')})`;
                    resendBtn.disabled = true;
                } else {
                    resendBtn.innerHTML = `<i class="fi fi-rr-refresh"></i> Resend Code`;
                    resendBtn.disabled = false;
                    this.stopCountdown();
                }
            }
        }, 1000);
    }

    stopCountdown() {
        if (this.countdownTimer) {
            clearInterval(this.countdownTimer);
            this.countdownTimer = null;
        }
    }

    async verifyCode() {
        const codeInputs = document.querySelectorAll('.code-input');
        const otp = Array.from(codeInputs).map(i => i.value).join('');

        if (otp.length !== 6) {
            this.showNotification('Please enter the complete 6-digit code', 'error');
            return;
        }

        const verifyBtn = document.getElementById('verifyCodeBtn');
        if (verifyBtn) {
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> Verifying...';
        }

        try {
            const data = await this.postJson(this.verifyOtpUrl, {
                purpose: this.activePurpose,
                otp: otp,
                otp_token: this.otpToken,
                // Match mobile: for password change, send the actual new password as new_value
                // For email/contact, send the new email/contact
                new_value: this.activePurpose === 'change_password' ? this.pendingNewPassword : this.pendingNewValue
            });

            if (!data.success) {
                const msg = data.message || 'Verification failed';
                const extra = data.attempts_left !== undefined ? ` (${data.attempts_left} attempts left)` : '';
                this.showNotification(msg + extra, 'error');
                this.resetVerifyBtn(verifyBtn);
                return;
            }

            // Success!
            this.showNotification(data.message || 'Updated successfully!', 'success');

            // Update displayed data
            if (this.activePurpose === 'change_email') {
                this.currentUserData.email = this.pendingNewValue;
            } else if (this.activePurpose === 'change_contact') {
                this.currentUserData.contact = this.pendingNewValue;
            }
            this.loadCurrentUserData();

            // Reset form fields
            this.resetFormFields();

            // Close modals
            this.closeVerificationModal();
            this.resetVerifyBtn(verifyBtn);
            setTimeout(() => this.close(), 1500);

        } catch (err) {
            console.error('Verify OTP error:', err);
            this.showNotification('Network error during verification. Please try again.', 'error');
            this.resetVerifyBtn(verifyBtn);
        }
    }

    resetVerifyBtn(btn) {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fi fi-rr-check"></i> Verify';
        }
    }

    async resendVerificationCode() {
        const resendBtn = document.getElementById('resendCodeBtn');
        if (resendBtn) resendBtn.disabled = true;

        try {
            const body = {
                purpose: this.activePurpose,
                new_value: this.pendingNewValue
            };

            // Include current_password for email changes
            if (this.activePurpose === 'change_email' && this.emailCurrentPassword) {
                body.current_password = this.emailCurrentPassword.value;
            }
            // For contact change, include destination override
            if (this.activePurpose === 'change_contact') {
                body.destination = this.currentUserData.email;
            }

            const data = await this.postJson(this.sendOtpUrl, body);

            if (!data.success) {
                this.showNotification(data.message || 'Failed to resend code', 'error');
                if (resendBtn) resendBtn.disabled = false;
                return;
            }

            this.otpToken = data.otp_token;
            this.maskedDest = data.masked;
            if (data.ttl_seconds) this.ttlSeconds = data.ttl_seconds;
            this.showNotification('Verification code resent!', 'success');

            // Clear existing OTP inputs
            document.querySelectorAll('.code-input').forEach(input => { input.value = ''; });
            const firstInput = this.verificationModal?.querySelector('.code-input');
            if (firstInput) firstInput.focus();

            // Restart countdown
            this.startCountdown(this.ttlSeconds || 900);
        } catch (err) {
            console.error('Resend OTP error:', err);
            this.showNotification('Failed to resend code. Please try again.', 'error');
            if (resendBtn) resendBtn.disabled = false;
        }
    }

    // ─── UI Helpers ──────────────────────────────────────────────────

    setFormLoading(loading) {
        if (!this.submitBtn) return;
        this.submitBtn.disabled = loading;
        if (loading) {
            this.submitBtn.dataset.originalText = this.submitBtn.innerHTML;
            this.submitBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> Sending code...';
        } else {
            this.submitBtn.innerHTML = this.submitBtn.dataset.originalText || '<i class="fi fi-rr-check"></i> Send OTP';
        }
        // Disable all inputs in the active field group while loading
        if (this.form) {
            this.form.querySelectorAll('input').forEach(input => { input.disabled = loading; });
        }
    }

    open() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            const modalBody = this.modal.querySelector('.security-modal-body');
            if (modalBody) modalBody.scrollTop = 0;
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
        // Remove any existing toasts first
        document.querySelectorAll('.security-toast').forEach(t => t.remove());

        const toast = document.createElement('div');
        toast.className = 'security-toast';
        let bgColor = '#EEA24B';
        if (type === 'success') bgColor = '#10b981';
        else if (type === 'error') bgColor = '#ef4444';

        toast.style.cssText = `
            position: fixed; bottom: 24px; right: 24px;
            color: #fff; padding: 12px 24px; border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15); z-index: 99999;
            font-size: 14px; font-weight: 500;
            background-color: ${bgColor};
            animation: slideUp 0.3s ease-out;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => {
                if (document.body.contains(toast)) document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }
}

// Initialize when DOM is ready
let securityModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    securityModalInstance = new SecurityModal();

    window.openSecurityModal = () => {
        if (securityModalInstance) securityModalInstance.open();
    };
    window.closeSecurityModal = () => {
        if (securityModalInstance) securityModalInstance.close();
    };
});
