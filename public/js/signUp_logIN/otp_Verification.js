document.addEventListener('DOMContentLoaded', () => {
	const otpInputs = document.querySelectorAll('.otp-input');
	const otpForm = document.getElementById('otpForm');
	const resendBtn = document.getElementById('resendBtn');
	const submitBtn = document.getElementById('submitBtn');
	const timerSpan = document.getElementById('timer');
	const timerLabel = document.getElementById('timerLabel');
	const progressFill = document.getElementById('progressFill');
	const digitCount = document.getElementById('digitCount');
	const successOverlay = document.getElementById('successOverlay');
	const closeOtpBtn = document.getElementById('closeOtpBtn');
	let timerInterval = null;
	let timeRemaining = 40;
	const OTP_LENGTH = 6;

	// Close modal button functionality
	if (closeOtpBtn) {
		closeOtpBtn.addEventListener('click', () => {
			// Reset form
			otpInputs.forEach(input => {
				input.value = '';
				input.disabled = false;
				input.classList.remove('otp-input-success');
			});
			successOverlay.classList.remove('show');
			updateProgress();
			console.log('OTP modal closed');
		});
	}

	const updateProgress = () => {
		const filled = Array.from(otpInputs).filter(input => input.value !== '').length;
		digitCount.textContent = filled;
		progressFill.style.width = `${(filled / OTP_LENGTH) * 100}%`;

		if (filled === OTP_LENGTH) {
			submitBtn.classList.add('otp-btn-active');
			submitBtn.disabled = false;
			// Auto-submit when all digits filled
			handleVerification();
		} else {
			submitBtn.classList.remove('otp-btn-active');
			submitBtn.disabled = true;
		}
	};

	// Flag to prevent duplicate submissions
	let isVerifying = false;

	const handleVerification = async () => {
		// Prevent duplicate submissions
		if (isVerifying) {
			console.log('Verification already in progress...');
			return;
		}

		const otp = Array.from(otpInputs).map(input => input.value).join('');

		if (otp.length === OTP_LENGTH) {
			// Set flag and disable inputs during verification
			isVerifying = true;
			otpInputs.forEach(input => {
				input.disabled = true;
			});
			submitBtn.disabled = true;

			try {
				// Detect if we're in contractor or property owner flow based on URL
				const isContractor = window.location.pathname.includes('contractor');
				const verifyEndpoint = isContractor 
					? '/accounts/signup/contractor/step3/verify-otp' 
					: '/accounts/signup/owner/step3/verify-otp';

				const response = await fetch(verifyEndpoint, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
					},
					body: JSON.stringify({ otp: otp }),
					credentials: 'include'
				});

				const result = await response.json();

				if (response.ok && result.success) {
					// Show success animation
					successOverlay.classList.add('show');

					// Add success styling to inputs
					otpInputs.forEach(input => {
						input.classList.add('otp-input-success');
					});

					console.log('OTP verification successful:', result.message);

					// Wait a moment before advancing
					setTimeout(() => {
						// Check if we're in the embedded modal (account setup page)
						if (typeof handleOtpVerified === 'function') {
							// Call the account setup handler to advance to Step 3
							handleOtpVerified();
						} else if (result.redirect_url) {
							// Use redirect URL from backend if provided
							window.location.href = result.redirect_url;
						} else if (result.next_step === 'property_owner_step4') {
							// Fallback for owner flow
							window.location.href = window.location.pathname + '?step=3';
						} else if (result.next_step === 'contractor_step3') {
							// Redirect to contractor Step 3 (resuming the multi-step form)
							window.location.href = '/contractor/account-setup?step=3';
						} else {
							window.location.reload();
						}
					}, 1500);
				} else {
					// Verification failed
					const errorMsg = result.message || 'Invalid OTP. Please try again.';
					alert(errorMsg);

					// Reset flag for retry
					isVerifying = false;

					// Reset form for retry
					otpInputs.forEach(input => {
						input.value = '';
						input.disabled = false;
						input.classList.remove('otp-input-success');
					});
					otpInputs[0].focus();
					updateProgress();

					console.error('OTP verification failed:', result.message);
				}
			} catch (error) {
				console.error('Error verifying OTP:', error);
				alert('Error: Unable to verify OTP. Please check your connection and try again.');

				// Reset flag for retry
				isVerifying = false;

				// Reset form for retry
				otpInputs.forEach(input => {
					input.disabled = false;
					input.classList.remove('otp-input-success');
				});
				updateProgress();
			}
		}
	};

	// Handle OTP input auto-focus and validation
	otpInputs.forEach((input, index) => {
		input.addEventListener('input', (e) => {
			// Only allow numbers
			e.target.value = e.target.value.replace(/[^0-9]/g, '');

			updateProgress();

			// Auto-focus next input
			if (e.target.value.length === 1 && index < otpInputs.length - 1) {
				otpInputs[index + 1].focus();
			}
		});

		input.addEventListener('keydown', (e) => {
			// Handle backspace
			if (e.key === 'Backspace' && input.value === '' && index > 0) {
				otpInputs[index - 1].focus();
			} else if (e.key === 'Backspace' && input.value !== '') {
				e.target.value = '';
				updateProgress();
			}

			// Handle arrow keys
			if (e.key === 'ArrowLeft' && index > 0) {
				otpInputs[index - 1].focus();
			}
			if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
				otpInputs[index + 1].focus();
			}
		});

		input.addEventListener('paste', (e) => {
			e.preventDefault();
			const pastedText = (e.clipboardData || window.clipboardData).getData('text');
			const digits = pastedText.replace(/[^0-9]/g, '').split('');

			digits.forEach((digit, i) => {
				if (i < otpInputs.length) {
					otpInputs[i].value = digit;
				}
			});

			updateProgress();

			// Focus last filled input or last input
			const lastFilledIndex = Math.min(digits.length - 1, otpInputs.length - 1);
			otpInputs[lastFilledIndex].focus();
		});
	});

	// Start timer
	const startTimer = () => {
		timeRemaining = 40;
		resendBtn.disabled = true;
		timerSpan.textContent = timeRemaining;

		timerInterval = setInterval(() => {
			timeRemaining--;
			timerSpan.textContent = timeRemaining;

			if (timeRemaining <= 0) {
				clearInterval(timerInterval);
				resendBtn.disabled = false;
				timerLabel.textContent = 'Resend';
			}
		}, 1000);
	};

	// Handle resend OTP
	resendBtn.addEventListener('click', (e) => {
		e.preventDefault();
		if (!resendBtn.disabled) {
			// Clear OTP inputs
			otpInputs.forEach(input => {
				input.value = '';
				input.disabled = false;
				input.classList.remove('otp-input-success');
			});
			otpInputs[0].focus();

			updateProgress();

			// Restart timer
			startTimer();
			console.log('OTP resent');
		}
	});

	// Handle form submission
	otpForm.addEventListener('submit', (e) => {
		e.preventDefault();
		handleVerification();
	});

	// Initialize timer on page load
	startTimer();
	updateProgress();
});
