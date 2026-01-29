// Security page interactivity

document.addEventListener('DOMContentLoaded', () => {
	// Avatar preview
	const avatarInput = document.getElementById('avatarInput');
	const avatarImg = document.getElementById('profileAvatar');
	if (avatarInput && avatarImg) {
		avatarInput.addEventListener('change', () => {
			const file = avatarInput.files && avatarInput.files[0];
			if (!file) return;
			if (file.size > 2 * 1024 * 1024) { toast('Image must be under 2MB', 'error'); return; }
			const reader = new FileReader();
			reader.onload = e => { avatarImg.src = e.target.result; toast('Avatar updated (not saved)', 'info'); };
			reader.readAsDataURL(file);
		});
	}

	// Save profile (client only)
	const saveProfileBtn = document.getElementById('saveProfileBtn');
	if (saveProfileBtn) {
		saveProfileBtn.addEventListener('click', () => {
			toast('Profile saved', 'success');
			// TODO: send to backend via AJAX
		});
	}

	// Password strength and validation
	const newPwd = document.getElementById('newPassword');
	const confirmPwd = document.getElementById('confirmPassword');
	const strengthProgress = document.getElementById('strengthProgress');
	const strengthLabel = document.getElementById('strengthLabel');
	const reqList = document.getElementById('requirementsList');

	function evaluateStrength(value) {
		const checks = {
			len: value.length >= 8,
			upper: /[A-Z]/.test(value),
			num: /[0-9]/.test(value),
			sym: /[^A-Za-z0-9]/.test(value),
		};
		const score = Object.values(checks).filter(Boolean).length;
		return { checks, score };
	}

	function updateRequirements() {
		if (!newPwd || !confirmPwd || !reqList) return;
		const { checks, score } = evaluateStrength(newPwd.value);
		const match = newPwd.value.length > 0 && newPwd.value === confirmPwd.value;
		const reqMap = { ...checks, match };
		reqList.querySelectorAll('.req-item').forEach(li => {
			const key = li.dataset.req;
			if (reqMap[key]) li.classList.add('met'); else li.classList.remove('met');
		});

		// Bar + label
		const widths = ['12%', '40%', '70%', '100%'];
		const labels = ['Weak', 'Fair', 'Good', 'Strong'];
		const classes = ['strength-weak','strength-fair','strength-good','strength-strong'];
		const idx = Math.max(0, Math.min(score - 1, 3));
		strengthProgress.style.width = widths[idx];
		classes.forEach(c => strengthProgress.classList.remove(c));
		strengthProgress.classList.add(classes[idx]);
		strengthLabel.textContent = labels[idx];
	}

	if (newPwd) newPwd.addEventListener('input', updateRequirements);
	if (confirmPwd) confirmPwd.addEventListener('input', updateRequirements);
	updateRequirements();

	// Toggle visibility
	document.querySelectorAll('.toggle-visibility').forEach(btn => {
		btn.addEventListener('click', () => {
			const targetId = btn.dataset.target;
			const input = document.getElementById(targetId);
			if (!input) return;
			if (input.type === 'password') { input.type = 'text'; btn.innerHTML = '<i class="fi fi-rr-eye-crossed"></i>'; }
			else { input.type = 'password'; btn.innerHTML = '<i class="fi fi-rr-eye"></i>'; }
		});
	});

	// Update password action
	const updateBtn = document.getElementById('updatePasswordBtn');
	if (updateBtn) {
		updateBtn.addEventListener('click', () => {
			if (!newPwd.value || !confirmPwd.value) { toast('Fill in both password fields', 'error'); return; }
			const { score } = evaluateStrength(newPwd.value);
			if (score < 3) { toast('Use a stronger password', 'error'); return; }
			if (newPwd.value !== confirmPwd.value) { toast('Passwords do not match', 'error'); return; }
			toast('Password updated', 'success');
			newPwd.value = '';
			confirmPwd.value = '';
			updateRequirements();
			// TODO: POST to backend
		});
	}

	// 2FA toggle and sessions
	const twoFactorToggle = document.getElementById('twoFactorToggle');
	const twoFactorNote = document.getElementById('twoFactorNote');
	if (twoFactorToggle && twoFactorNote) {
		twoFactorToggle.addEventListener('change', () => {
			twoFactorNote.textContent = twoFactorToggle.checked ? '2FA is enabled (demo).' : '2FA is currently disabled.';
			toast(twoFactorToggle.checked ? 'Two‑Factor enabled' : 'Two‑Factor disabled', 'info');
		});
	}

	const logoutAllBtn = document.getElementById('logoutAllBtn');
	if (logoutAllBtn) {
		logoutAllBtn.addEventListener('click', () => {
			toast('All other sessions logged out (demo)', 'success');
		});
	}
});

// Simple toast helper
function toast(message, type = 'info') {
	const existing = document.querySelector('.toast');
	if (existing) existing.remove();
	const t = document.createElement('div');
	t.className = 'toast fixed bottom-8 right-8 px-5 py-3 rounded-lg shadow-xl text-white font-semibold z-50 transition-transform';
	if (type === 'success') t.style.background = 'linear-gradient(135deg,#10b981,#059669)';
	else if (type === 'error') t.style.background = 'linear-gradient(135deg,#ef4444,#dc2626)';
	else t.style.background = 'linear-gradient(135deg,#3b82f6,#2563eb)';
	t.textContent = message;
	document.body.appendChild(t);
	setTimeout(() => t.style.transform = 'translateY(-6px)', 50);
	setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(10px)'; setTimeout(()=>t.remove(), 300); }, 2800);
}

