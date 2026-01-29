/**
 * Security Modal JavaScript
 * Handles password changes, email/contact updates, and verification
 */

class SecurityModal {
    constructor() {
        this.modal = document.getElementById('securityModal');
        this.overlay = document.getElementById('securityModalOverlay');
        this.verificationModal = document.getElementById('verificationModal');
        
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
        
        // Current user data (in production, fetch from backend)
        this.currentUserData = {
            email: 'john.doe@example.com',
            contact: '+63 912 345 6789'
        };
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadCurrentUserData();
    }

    setupEventListeners() {
        // Open modal from navbar
        const securityLink = document.getElementById('securityLink');
        if (securityLink) {
            securityLink.addEventListener('click', (e) => {
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
        const closeBtn = document.getElementById('closeSecurityModalBtn');
        const cancelBtn = document.getElementById('cancelSecurityBtn');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.close());
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

        // Toggle password visibility
        const toggleBtns = document.querySelectorAll('.toggle-password-btn');
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = btn.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fi-rr-eye');
                    icon.classList.add('fi-rr-eye-crossed');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fi-rr-eye-crossed');
                    icon.classList.add('fi-rr-eye');
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

        // Password match checker
        if (this.confirmPassword) {
            this.confirmPassword.addEventListener('input', () => {
                this.checkPasswordMatch();
            });
        }

        // Email match checker
        if (this.newEmail && this.confirmEmail) {
            this.newEmail.addEventListener('input', () => {
                this.checkEmailMatch();
            });
            
            this.confirmEmail.addEventListener('input', () => {
                this.checkEmailMatch();
            });
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
        
        if (closeVerificationBtn) {
            closeVerificationBtn.addEventListener('click', () => {
                this.closeVerificationModal();
            });
        }
        
        if (cancelVerificationBtn) {
            cancelVerificationBtn.addEventListener('click', () => {
                this.closeVerificationModal();
            });
        }
        
        if (verifyCodeBtn) {
            verifyCodeBtn.addEventListener('click', () => {
                this.verifyCode();
            });
        }
        
        if (resendCodeBtn) {
            resendCodeBtn.addEventListener('click', () => {
                this.resendVerificationCode();
            });
        }

        // Code input auto-focus
        const codeInputs = document.querySelectorAll('.code-input');
        codeInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < codeInputs.length - 1) {
                    codeInputs[index + 1].focus();
                }
            });
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    codeInputs[index - 1].focus();
                }
            });
        });
    }

    loadCurrentUserData() {
        // In production, fetch from backend
        const currentEmailDisplay = document.getElementById('currentEmailDisplay');
        const currentContactDisplay = document.getElementById('currentContactDisplay');
        
        if (currentEmailDisplay) {
            currentEmailDisplay.textContent = this.currentUserData.email;
        }
        
        if (currentContactDisplay) {
            currentContactDisplay.textContent = this.currentUserData.contact;
        }
    }

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

        // Update requirement items
        Object.keys(requirements).forEach(req => {
            const item = document.querySelector(`[data-requirement="${req}"]`);
            if (item) {
                if (requirements[req]) {
                    item.classList.add('met');
                    const icon = item.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fi-rr-cross-circle');
                        icon.classList.add('fi-rr-check-circle');
                    }
                } else {
                    item.classList.remove('met');
                    const icon = item.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fi-rr-check-circle');
                        icon.classList.add('fi-rr-cross-circle');
                    }
                }
            }
        });

        // Calculate strength
        const metRequirements = Object.values(requirements).filter(r => r).length;
        
        if (metRequirements <= 2) {
            this.strengthBar.className = 'strength-bar-fill weak';
            this.strengthText.textContent = 'Weak password';
            this.strengthText.className = 'strength-text weak';
        } else if (metRequirements <= 4) {
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
        const requirementItems = document.querySelectorAll('.requirement-item');
        requirementItems.forEach(item => {
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
        const newEmailVal = this.newEmail.value;
        const confirmEmailVal = this.confirmEmail.value;
        
        if (!confirmEmailVal) {
            this.emailMatchMessage.textContent = '';
            this.emailMatchMessage.className = 'email-match-message';
            return;
        }
        
        if (newEmailVal === confirmEmailVal) {
            this.emailMatchMessage.textContent = '✓ Email addresses match';
            this.emailMatchMessage.className = 'email-match-message match';
        } else {
            this.emailMatchMessage.textContent = '✗ Email addresses do not match';
            this.emailMatchMessage.className = 'email-match-message no-match';
        }
    }

    async handlePasswordChange() {
        const currentPwd = this.currentPassword.value;
        const newPwd = this.newPassword.value;
        const confirmPwd = this.confirmPassword.value;

        // Validate
        if (!currentPwd || !newPwd || !confirmPwd) {
            this.showNotification('Please fill in all password fields', 'error');
            return;
        }

        if (newPwd !== confirmPwd) {
            this.showNotification('New passwords do not match', 'error');
            return;
        }

        // Check password strength
        const requirements = {
            length: newPwd.length >= 8,
            uppercase: /[A-Z]/.test(newPwd),
            lowercase: /[a-z]/.test(newPwd),
            number: /[0-9]/.test(newPwd),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(newPwd)
        };

        if (!Object.values(requirements).every(r => r)) {
            this.showNotification('Password does not meet all requirements', 'error');
            return;
        }

        // In production, make API call
        try {
            this.showNotification('Updating password...', 'info');
            
            // Simulate API call
            await this.simulateAPICall();
            
            this.showNotification('Password updated successfully!', 'success');
            this.passwordForm.reset();
            this.resetPasswordRequirements();
            this.strengthBar.className = 'strength-bar-fill';
            this.strengthText.textContent = 'Enter a password';
            this.strengthText.className = 'strength-text';
            
            // Close modal after success
            setTimeout(() => {
                this.close();
            }, 2000);
        } catch (error) {
            this.showNotification('Failed to update password. Please try again.', 'error');
        }
    }

    async handleContactChange() {
        const newEmailVal = this.newEmail.value;
        const confirmEmailVal = this.confirmEmail.value;
        const newContactVal = this.newContact.value;
        const password = this.contactPasswordConfirm.value;

        // Validate password
        if (!password) {
            this.showNotification('Please enter your password to confirm changes', 'error');
            return;
        }

        // Check if any field is filled
        if (!newEmailVal && !newContactVal) {
            this.showNotification('Please enter at least one field to update', 'error');
            return;
        }

        // Validate email if provided
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
        }

        // Validate contact if provided
        if (newContactVal) {
            const contactRegex = /^[0-9]{11}$/;
            if (!contactRegex.test(newContactVal)) {
                this.showNotification('Please enter a valid 11-digit contact number', 'error');
                return;
            }
        }

        try {
            this.showNotification('Updating contact information...', 'info');
            
            // Simulate API call
            await this.simulateAPICall();
            
            // Show verification modal
            this.openVerificationModal(newEmailVal, newContactVal);
        } catch (error) {
            this.showNotification('Failed to update contact information. Please try again.', 'error');
        }
    }

    openVerificationModal(email, contact) {
        if (!this.verificationModal) return;
        
        const message = document.getElementById('verificationMessage');
        if (message) {
            if (email && contact) {
                message.textContent = `We've sent a verification code to your new email (${email}) and contact number.`;
            } else if (email) {
                message.textContent = `We've sent a verification code to ${email}`;
            } else if (contact) {
                message.textContent = `We've sent a verification code to ${contact}`;
            }
        }
        
        this.verificationModal.classList.remove('hidden');
        
        // Focus first input
        const firstInput = this.verificationModal.querySelector('.code-input');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }

    closeVerificationModal() {
        if (this.verificationModal) {
            this.verificationModal.classList.add('hidden');
            
            // Clear code inputs
            const codeInputs = document.querySelectorAll('.code-input');
            codeInputs.forEach(input => {
                input.value = '';
            });
        }
    }

    async verifyCode() {
        const codeInputs = document.querySelectorAll('.code-input');
        const code = Array.from(codeInputs).map(input => input.value).join('');
        
        if (code.length !== 6) {
            this.showNotification('Please enter the complete 6-digit code', 'error');
            return;
        }

        try {
            this.showNotification('Verifying code...', 'info');
            
            // Simulate API call
            await this.simulateAPICall();
            
            this.showNotification('Contact information updated successfully!', 'success');
            
            // Update current user data display
            if (this.newEmail.value) {
                this.currentUserData.email = this.newEmail.value;
            }
            if (this.newContact.value) {
                this.currentUserData.contact = this.newContact.value;
            }
            this.loadCurrentUserData();
            
            // Reset form
            this.contactForm.reset();
            
            // Close modals
            this.closeVerificationModal();
            setTimeout(() => {
                this.close();
            }, 2000);
        } catch (error) {
            this.showNotification('Invalid verification code. Please try again.', 'error');
        }
    }

    async resendVerificationCode() {
        try {
            this.showNotification('Resending verification code...', 'info');
            
            // Simulate API call
            await this.simulateAPICall();
            
            this.showNotification('Verification code sent!', 'success');
        } catch (error) {
            this.showNotification('Failed to resend code. Please try again.', 'error');
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
            const modalBody = this.modal.querySelector('.security-modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
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
let securityModalInstance = null;

document.addEventListener('DOMContentLoaded', () => {
    securityModalInstance = new SecurityModal();
    
    // Expose globally if needed
    window.openSecurityModal = () => {
        if (securityModalInstance) {
            securityModalInstance.open();
        }
    };
    
    window.closeSecurityModal = () => {
        if (securityModalInstance) {
            securityModalInstance.close();
        }
    };
});
