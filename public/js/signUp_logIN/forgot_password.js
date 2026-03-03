document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let userEmail = '';
    let resetToken = '';

    // Elements
    const stepEmail = document.getElementById('step-email');
    const stepOtp = document.getElementById('step-otp');
    const stepPassword = document.getElementById('step-password');

    const emailInput = document.getElementById('resetEmail');
    const emailError = document.getElementById('emailError');
    const sendOtpBtn = document.getElementById('sendOtpBtn');

    const otpDigits = document.querySelectorAll('.otp-digit');
    const otpError = document.getElementById('otpError');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const otpSubtitle = document.getElementById('otpSubtitle');
    const resendTimer = document.getElementById('resendTimer');
    const timerCount = document.getElementById('timerCount');
    const resendOtpBtn = document.getElementById('resendOtpBtn');
    const backToEmail = document.getElementById('backToEmail');

    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const confirmError = document.getElementById('confirmError');
    const resetPasswordBtn = document.getElementById('resetPasswordBtn');
    const passwordRequirements = document.getElementById('passwordRequirements');

    const toast = document.getElementById('toast');

    // Toast helper
    function showToast(message, type = 'info', duration = 4000) {
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'toast-notification toast-' + type;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, duration);
    }

    // Show step helper
    function showStep(stepEl) {
        [stepEmail, stepOtp, stepPassword].forEach(s => s.classList.remove('active'));
        stepEl.classList.add('active');
    }

    // ===================== STEP 1: Send OTP =====================
    sendOtpBtn.addEventListener('click', async () => {
        const email = emailInput.value.trim();
        emailError.style.display = 'none';

        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            emailError.textContent = 'Please enter a valid email address.';
            emailError.style.display = 'block';
            emailInput.focus();
            return;
        }

        sendOtpBtn.disabled = true;
        sendOtpBtn.textContent = 'Sending...';

        try {
            const resp = await fetch('/accounts/password/send-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email })
            });

            const data = await resp.json();

            if (data.success) {
                userEmail = email;
                otpSubtitle.innerHTML = `We sent a 6-digit code to <strong>${email}</strong>`;
                showStep(stepOtp);
                otpDigits[0].focus();
                startResendTimer();
                showToast('Reset code sent!', 'success');
            } else {
                emailError.textContent = data.message || 'Error sending code.';
                emailError.style.display = 'block';
            }
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
        } finally {
            sendOtpBtn.disabled = false;
            sendOtpBtn.textContent = 'Send Reset Code';
        }
    });

    // ===================== STEP 2: OTP Input & Verify =====================
    otpDigits.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            otpError.style.display = 'none';

            if (e.target.value.length === 1 && index < otpDigits.length - 1) {
                otpDigits[index + 1].focus();
            }

            // Auto-verify when all filled
            const otp = Array.from(otpDigits).map(d => d.value).join('');
            verifyOtpBtn.disabled = otp.length !== 6;
            if (otp.length === 6) {
                verifyOtp(otp);
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && input.value === '' && index > 0) {
                otpDigits[index - 1].focus();
            }
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            pasted.split('').forEach((char, i) => {
                if (i < otpDigits.length) otpDigits[i].value = char;
            });
            const otp = Array.from(otpDigits).map(d => d.value).join('');
            verifyOtpBtn.disabled = otp.length !== 6;
            if (otp.length === 6) verifyOtp(otp);
        });
    });

    verifyOtpBtn.addEventListener('click', () => {
        const otp = Array.from(otpDigits).map(d => d.value).join('');
        if (otp.length === 6) verifyOtp(otp);
    });

    async function verifyOtp(otp) {
        verifyOtpBtn.disabled = true;
        verifyOtpBtn.textContent = 'Verifying...';
        otpDigits.forEach(d => d.disabled = true);

        try {
            const resp = await fetch('/accounts/password/verify-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email: userEmail, otp })
            });

            const data = await resp.json();

            if (data.success) {
                resetToken = data.reset_token;
                otpDigits.forEach(d => d.classList.add('success'));
                showToast('Code verified!', 'success');
                setTimeout(() => showStep(stepPassword), 800);
                newPassword.focus();
            } else {
                otpError.textContent = data.message || 'Invalid code.';
                otpError.style.display = 'block';
                otpDigits.forEach(d => { d.value = ''; d.disabled = false; });
                otpDigits[0].focus();
            }
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            otpDigits.forEach(d => d.disabled = false);
        } finally {
            verifyOtpBtn.disabled = false;
            verifyOtpBtn.textContent = 'Verify Code';
        }
    }

    // Resend timer
    let timerInterval = null;
    function startResendTimer() {
        let seconds = 60;
        resendTimer.style.display = 'inline';
        resendOtpBtn.style.display = 'none';
        timerCount.textContent = seconds;

        clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            seconds--;
            timerCount.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(timerInterval);
                resendTimer.style.display = 'none';
                resendOtpBtn.style.display = 'inline';
            }
        }, 1000);
    }

    resendOtpBtn.addEventListener('click', async () => {
        resendOtpBtn.textContent = 'Sending...';
        resendOtpBtn.setAttribute('disabled', true);

        try {
            const resp = await fetch('/accounts/password/send-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ email: userEmail })
            });

            const data = await resp.json();
            if (data.success) {
                showToast('New code sent!', 'success');
                otpDigits.forEach(d => { d.value = ''; d.disabled = false; d.classList.remove('success'); });
                otpDigits[0].focus();
                startResendTimer();
            } else {
                showToast(data.message || 'Failed to resend.', 'error');
            }
        } catch (err) {
            showToast('Network error.', 'error');
        } finally {
            resendOtpBtn.textContent = 'Resend Code';
            resendOtpBtn.removeAttribute('disabled');
        }
    });

    backToEmail.addEventListener('click', (e) => {
        e.preventDefault();
        showStep(stepEmail);
        emailInput.focus();
    });

    // ===================== STEP 3: Reset Password =====================
    // Password requirements validation
    newPassword.addEventListener('focus', () => { passwordRequirements.style.display = 'block'; });
    newPassword.addEventListener('input', () => {
        if (newPassword.value.length > 0) passwordRequirements.style.display = 'block';
        validatePasswordReqs(newPassword.value);
    });
    newPassword.addEventListener('blur', () => {
        if (newPassword.value.length === 0) passwordRequirements.style.display = 'none';
    });

    function validatePasswordReqs(pw) {
        const reqs = {
            min8: pw.length >= 8,
            uppercase: /[A-Z]/.test(pw),
            number: /[0-9]/.test(pw),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(pw)
        };
        Object.keys(reqs).forEach(key => {
            const el = passwordRequirements.querySelector(`[data-req="${key}"]`);
            if (el) {
                el.style.color = reqs[key] ? '#22c55e' : '#ef4444';
                el.textContent = (reqs[key] ? '✓ ' : '✕ ') + el.textContent.substring(2);
            }
        });
        return Object.values(reqs).every(Boolean);
    }

    // Confirm password match
    confirmPassword.addEventListener('input', () => {
        confirmError.style.display = (confirmPassword.value && newPassword.value !== confirmPassword.value) ? 'block' : 'none';
    });

    // Toggle visibility
    document.getElementById('toggleNew')?.addEventListener('click', () => {
        newPassword.type = newPassword.type === 'password' ? 'text' : 'password';
    });
    document.getElementById('toggleConfirm')?.addEventListener('click', () => {
        confirmPassword.type = confirmPassword.type === 'password' ? 'text' : 'password';
    });

    // Submit
    resetPasswordBtn.addEventListener('click', async () => {
        // Validate
        if (!validatePasswordReqs(newPassword.value)) {
            showToast('Password does not meet all requirements.', 'error');
            return;
        }
        if (newPassword.value !== confirmPassword.value) {
            confirmError.style.display = 'block';
            confirmPassword.focus();
            return;
        }

        resetPasswordBtn.disabled = true;
        resetPasswordBtn.textContent = 'Resetting...';

        try {
            const resp = await fetch('/accounts/password/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    email: userEmail,
                    reset_token: resetToken,
                    password: newPassword.value,
                    password_confirmation: confirmPassword.value
                })
            });

            const data = await resp.json();

            if (data.success) {
                showToast('Password updated! Redirecting to login...', 'success', 3000);
                setTimeout(() => { window.location.href = '/accounts/login'; }, 2000);
            } else {
                showToast(data.message || 'Failed to reset password.', 'error');
                resetPasswordBtn.disabled = false;
                resetPasswordBtn.textContent = 'Reset Password';
            }
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            resetPasswordBtn.disabled = false;
            resetPasswordBtn.textContent = 'Reset Password';
        }
    });

    // Enter key on email
    emailInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendOtpBtn.click();
    });
});
