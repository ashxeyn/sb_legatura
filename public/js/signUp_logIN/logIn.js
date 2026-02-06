// Password visibility toggle with icon swap
document.addEventListener('DOMContentLoaded', () => {
	const toggle = document.querySelector('.toggle-visibility');
	const passwordInput = document.querySelector('input[type="password"]');
	const eyeOpen = toggle?.querySelector('.eye-open');
	const eyeClosed = toggle?.querySelector('.eye-closed');

	if (toggle && passwordInput && eyeOpen && eyeClosed) {
		toggle.addEventListener('click', () => {
			const isHidden = passwordInput.type === 'password';
			passwordInput.type = isHidden ? 'text' : 'password';
			eyeOpen.style.display = isHidden ? 'none' : 'inline-block';
			eyeClosed.style.display = isHidden ? 'inline-block' : 'none';
			toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
		});
	}

	// Keep error messages visible - don't auto-hide them
	// Error messages will persist until user corrects the form and resubmits
	const alerts = document.querySelectorAll('.alert');
	alerts.forEach(alert => {
		// Messages will stay visible for the user to read and act on
		alert.style.display = 'flex';
	});
});
