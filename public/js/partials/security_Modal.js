/**
 * Security Modal JavaScript
 * Handles password changes, email/contact updates with OTP verification.
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

        // Forms
        this.passwordForm = document.getElementById('changePasswordForm');
        this.contactForm = document.getElementById('changeContactForm');

        // Password fields
        this.currentPassword = document.getElementById('currentPassword');
        this.newPassword = document.getElementById('newPassword');
        this.confirmPassword = document.getElementById('confirmPassword');

        // Contact fields
        this.newEmail = document.getElementById('newEmail');
        this.confirmEmail = document.getElementById('confirmEmail');
        this.newContact = document.getElementById('newContact');
        this.contactPasswordConfirm = document.getElementById('contactPasswordConfirm');

        // Password strength
        this.strengthBar = document.getElementById('strengthBarFill');
        this.strengthText = document.getElementById('strengthText');

        // Messages
        this.passwordMatchMessage = document.getElementById('passwordMatchMessage');
        this.emailMatchMessage = document.getElementById('emailMatchMessage');

        // OTP state (mirrors mobile changeOtpScreen.tsx flow)
        this.otpToken = null;
        this.maskedDest = null;
        this.activePurpose = null;       // 'change_password' | 'change_email' | 'change_contact'
        this.pendingNewValue = null;      // the value to update after OTP verification
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

        // Email match checker
        if (this.newEmail && this.confirmEmail) {
            this.newEmail.addEventListener('input', () => this.checkEmailMatch());
            this.confirmEmail.addEventListener('input', () => this.checkEmailMatch());
        }

        // Form submissions
        if (this.passwordForm) {
            this.passwordForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handlePasswordChange();
            });
        }
        if (this.contactForm) {
            this.contactForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleContactChange();
            });
        }

        // Verification modal
        this.setupVerificationModal();
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
                // Allow only digits
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
            // Handle paste: distribute digits across inputs
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
        const currentEmailDisplay = document.getElementById('currentEmailDisplay');
        const currentContactDisplay = document.getElementById('currentContactDisplay');

        if (currentEmailDisplay) {
            currentEmailDisplay.textContent = this.currentUserData.email || 'Not set';
        }
        if (currentContactDisplay) {
            currentContactDisplay.textContent = this.currentUserData.contact || 'Not set';
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

        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
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
        if (metCount <= 2) {
            this.strengthBar.className = 'strength-bar-fill weak';
            this.strengthText.textContent = 'Weak password';
            this.strengthText.className = 'strength-text weak';
        } else if (metCount <= 4) {
            this.strengthBar.className = 'strength-bar-fill medium';
            this.strengthText.textContent = 'Medium password';
            this.strengthText.className = 'strength-text medium';
        } else {
            this.strengthBar.className = 'strength-bar-fill strong';
            this.strengthText.textContent = 'Strong password';
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

    checkEmailMatch() {
        const newVal = this.newEmail.value;
        const confirmVal = this.confirmEmail.value;

        if (!confirmVal) {
            this.emailMatchMessage.textContent = '';
            this.emailMatchMessage.className = 'email-match-message';
            return;
        }
        if (newVal === confirmVal) {
            this.emailMatchMessage.textContent = '✓ Email addresses match';
            this.emailMatchMessage.className = 'email-match-message match';
        } else {
            this.emailMatchMessage.textContent = '✗ Email addresses do not match';
            this.emailMatchMessage.className = 'email-match-message no-match';
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
        const json = await res.json();
        return { status: res.status, ...json };
    }

    // ─── Password Change Flow ─────────────────────────────────────────

    async handlePasswordChange() {
        const currentPwd = this.currentPassword.value;
        const newPwd = this.newPassword.value;
        const confirmPwd = this.confirmPassword.value;

        if (!currentPwd || !newPwd || !confirmPwd) {
            this.showNotification('Please fill in all password fields', 'error');
            return;
        }
        if (newPwd !== confirmPwd) {
            this.showNotification('New passwords do not match', 'error');
            return;
        }

        // Check all password requirements before sending
        const reqs = {
            length: newPwd.length >= 8,
            uppercase: /[A-Z]/.test(newPwd),
            lowercase: /[a-z]/.test(newPwd),
            number: /[0-9]/.test(newPwd),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(newPwd)
        };
        if (!Object.values(reqs).every(Boolean)) {
            this.showNotification('Password does not meet all requirements', 'error');
            return;
        }

        // Send OTP to user's email for password change verification
        this.activePurpose = 'change_password';
        this.pendingNewValue = newPwd;

        this.setFormLoading(this.passwordForm, true);

        try {
            const data = await this.postJson(this.sendOtpUrl, {
                purpose: 'change_password',
                new_value: newPwd,
                current_password: currentPwd
            });

            if (!data.success) {
                this.showNotification(data.message || 'Failed to send verification code', 'error');
                this.setFormLoading(this.passwordForm, false);
                return;
            }

            this.otpToken = data.otp_token;
            this.maskedDest = data.masked;
            this.setFormLoading(this.passwordForm, false);
            this.openVerificationModal();
        } catch (err) {
            console.error('Password change send OTP error:', err);
            this.showNotification('Network error. Please try again.', 'error');
            this.setFormLoading(this.passwordForm, false);
        }
    }

    // ─── Contact / Email Change Flow ──────────────────────────────────

    async handleContactChange() {
        const newEmailVal = this.newEmail.value.trim();
        const confirmEmailVal = this.confirmEmail.value.trim();
        const newContactVal = this.newContact.value.trim();
        const password = this.contactPasswordConfirm.value;

        if (!password) {
            this.showNotification('Please enter your password to confirm changes', 'error');
            return;
        }
        if (!newEmailVal && !newContactVal) {
            this.showNotification('Please enter at least one field to update', 'error');
            return;
        }

        // If both email and contact are provided, handle email first, then contact
        // (sequential OTP flow — one at a time)
        if (newEmailVal) {
            if (newEmailVal !== confirmEmailVal) {
                this.showNotification('Email addresses do not match', 'error');
                return;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(newEmailVal)) {
                this.showNotification('Please enter a valid email address', 'error');
                return;
            }
            await this.sendChangeEmailOtp(newEmailVal, password);
            return;
        }

        if (newContactVal) {
            if (!/^[0-9]{11}$/.test(newContactVal)) {
                this.showNotification('Please enter a valid 11-digit contact number', 'error');
                return;
            }
            await this.sendChangeContactOtp(newContactVal, password);
        }
    }

    async sendChangeEmailOtp(newEmail, currentPassword) {
        this.activePurpose = 'change_email';
        this.pendingNewValue = newEmail;

        this.setFormLoading(this.contactForm, true);

        try {
            const data = await this.postJson(this.sendOtpUrl, {
                purpose: 'change_email',
                new_value: newEmail,
                current_password: currentPassword
            });

            if (!data.success) {
                this.showNotification(data.message || 'Failed to send verification code', 'error');
                this.setFormLoading(this.contactForm, false);
                return;
            }

            this.otpToken = data.otp_token;
            this.maskedDest = data.masked;
            this.setFormLoading(this.contactForm, false);
            this.openVerificationModal();
        } catch (err) {
            console.error('Email change send OTP error:', err);
            this.showNotification('Network error. Please try again.', 'error');
            this.setFormLoading(this.contactForm, false);
        }
    }

    async sendChangeContactOtp(newContact, currentPassword) {
        this.activePurpose = 'change_contact';
        this.pendingNewValue = newContact;

        this.setFormLoading(this.contactForm, true);

        try {
            // For contact change, OTP is sent to the user's email (destination override)
            const data = await this.postJson(this.sendOtpUrl, {
                purpose: 'change_contact',
                new_value: newContact,
                current_password: currentPassword,
                destination: this.currentUserData.email   // send OTP to email, not new phone
            });

            if (!data.success) {
                this.showNotification(data.message || 'Failed to send verification code', 'error');
                this.setFormLoading(this.contactForm, false);
                return;
            }

            this.otpToken = data.otp_token;
            this.maskedDest = data.masked;
            this.setFormLoading(this.contactForm, false);
            this.openVerificationModal();
        } catch (err) {
            console.error('Contact change send OTP error:', err);
            this.showNotification('Network error. Please try again.', 'error');
            this.setFormLoading(this.contactForm, false);
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

        // Start countdown
        this.startCountdown(300); // 5 min default
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
                new_value: this.pendingNewValue
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

            // Reset forms
            if (this.activePurpose === 'change_password') {
                this.passwordForm.reset();
                this.resetPasswordRequirements();
                this.strengthBar.className = 'strength-bar-fill';
                this.strengthText.textContent = 'Enter a password';
                this.strengthText.className = 'strength-text';
                if (this.passwordMatchMessage) {
                    this.passwordMatchMessage.textContent = '';
                    this.passwordMatchMessage.className = 'password-match-message';
                }
            } else {
                this.contactForm.reset();
                if (this.emailMatchMessage) {
                    this.emailMatchMessage.textContent = '';
                    this.emailMatchMessage.className = 'email-match-message';
                }
            }

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
            // Re-send OTP using the same purpose & value
            const body = {
                purpose: this.activePurpose,
                new_value: this.pendingNewValue
            };

            // Include current_password for email changes
            if (this.activePurpose === 'change_email' && this.contactPasswordConfirm) {
                body.current_password = this.contactPasswordConfirm.value;
            }
            // For contact change, include destination override
            if (this.activePurpose === 'change_contact') {
                body.destination = this.currentUserData.email;
            }
            // For password change, include current password
            if (this.activePurpose === 'change_password' && this.currentPassword) {
                body.current_password = this.currentPassword.value;
            }

            const data = await this.postJson(this.sendOtpUrl, body);

            if (!data.success) {
                this.showNotification(data.message || 'Failed to resend code', 'error');
                if (resendBtn) resendBtn.disabled = false;
                return;
            }

            this.otpToken = data.otp_token;
            this.maskedDest = data.masked;
            this.showNotification('Verification code resent!', 'success');

            // Clear existing OTP inputs
            document.querySelectorAll('.code-input').forEach(input => { input.value = ''; });
            const firstInput = this.verificationModal?.querySelector('.code-input');
            if (firstInput) firstInput.focus();

            // Restart countdown
            this.startCountdown(300);
        } catch (err) {
            console.error('Resend OTP error:', err);
            this.showNotification('Failed to resend code. Please try again.', 'error');
            if (resendBtn) resendBtn.disabled = false;
        }
    }

    // ─── UI Helpers ──────────────────────────────────────────────────

    setFormLoading(form, loading) {
        if (!form) return;
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = loading;
            if (loading) {
                submitBtn.dataset.originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> Sending code...';
            } else {
                submitBtn.innerHTML = submitBtn.dataset.originalText || submitBtn.innerHTML;
            }
        }
        // Disable all inputs while loading
        form.querySelectorAll('input').forEach(input => { input.disabled = loading; });
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
