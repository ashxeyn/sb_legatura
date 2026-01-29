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
});
