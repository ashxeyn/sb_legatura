document.addEventListener('DOMContentLoaded', () => {
	const otpInputs = document.querySelectorAll('.otp-input');
	const otpForm = document.getElementById('otpForm');
	const resendBtn = document.getElementById('resendBtn');
	const timerSpan = document.getElementById('timer');
	let timerInterval = null;
	let timeRemaining = 40;

	// Handle OTP input auto-focus and validation
	otpInputs.forEach((input, index) => {
		input.addEventListener('input', (e) => {
			// Only allow numbers
			e.target.value = e.target.value.replace(/[^0-9]/g, '');

			// Auto-focus next input
			if (e.target.value.length === 1 && index < otpInputs.length - 1) {
				otpInputs[index + 1].focus();
			}
		});

		input.addEventListener('keydown', (e) => {
			// Handle backspace
			if (e.key === 'Backspace' && input.value === '' && index > 0) {
				otpInputs[index - 1].focus();
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
				resendBtn.textContent = 'Resend';
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
			});
			otpInputs[0].focus();

			// Restart timer
			startTimer();
			console.log('OTP resent');
		}
	});

	// Handle form submission
	otpForm.addEventListener('submit', (e) => {
		e.preventDefault();

		// Get OTP value
		const otp = Array.from(otpInputs).map(input => input.value).join('');

		if (otp.length === 6) {
			console.log('OTP submitted:', otp);
			// TODO: Send OTP to backend for verification
			alert('OTP submitted: ' + otp);
		} else {
			alert('Please enter all 6 digits');
			otpInputs[0].focus();
		}
	});

	// Initialize timer on page load
	startTimer();
});
