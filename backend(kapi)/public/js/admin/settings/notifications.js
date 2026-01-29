// Notifications Settings Interactivity

document.addEventListener('DOMContentLoaded', () => {
	// Defaults
	const DEFAULTS = {
		remind_before_expiration: true,
		payment_processed: true,
		payment_failed: true,
		user_registered: true,
		failed_login_attempt: true,
		project_reported: false,
		channel_email: false,
		channel_sms: false,
		channel_inapp: true
	};

	const STORAGE_KEY = 'admin_notifications_prefs_v1';
	const saveBar = document.getElementById('saveBar');
	const saveBtn = document.getElementById('saveSettingsBtn');
	const resetBtn = document.getElementById('resetDefaultsBtn');
	const previewArea = document.getElementById('previewArea');

	// Helpers
	const loadPrefs = () => {
		try {
			const raw = localStorage.getItem(STORAGE_KEY);
			return raw ? { ...DEFAULTS, ...JSON.parse(raw) } : { ...DEFAULTS };
		} catch { return { ...DEFAULTS }; }
	};
	const savePrefs = (prefs) => localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
	const showSaveBar = () => saveBar && saveBar.classList.remove('hidden');
	const hideSaveBar = () => saveBar && saveBar.classList.add('hidden');

	function renderPreview(prefs) {
		if (!previewArea) return;
		previewArea.innerHTML = '';
		const items = [];
		if (prefs.remind_before_expiration) items.push(['fi fi-ss-bell-ring', 'Reminder: Subscription for J’Lois Construction expires in 7 days.']);
		if (prefs.payment_processed) items.push(['fi fi-ss-badge-check', 'Payment processed: ₱1,999 for Gold Tier.']);
		if (prefs.payment_failed) items.push(['fi fi-ss-cross', 'Payment failed: Apex Contractors — card declined.']);
		if (prefs.user_registered) items.push(['fi fi-ss-user-add', 'New user registration: Cabonting Architects.']);
		if (prefs.failed_login_attempt) items.push(['fi fi-ss-fingerprint', 'Security: Multiple failed login attempts detected.']);
		if (prefs.project_reported) items.push(['fi fi-ss-warning', 'Alert: Project reported by a user.']);
		if (items.length === 0) {
			previewArea.innerHTML = '<div class="text-gray-500 text-sm">Enable some notifications to preview them here.</div>';
			return;
		}
		items.forEach(([icon, text]) => {
			const div = document.createElement('div');
			div.className = 'preview-chip';
			div.innerHTML = `<i class="${icon}"></i><span>${text}</span>`;
			previewArea.appendChild(div);
		});
	}

	// Initialize
	let prefs = loadPrefs();
	document.querySelectorAll('.setting-toggle').forEach(input => {
		const key = input.dataset.setting;
		input.checked = !!prefs[key];
		input.addEventListener('change', () => {
			prefs[key] = input.checked;
			showSaveBar();
			if (key.startsWith('remind_') || key.includes('payment') || key.includes('user_') || key.includes('failed_login') || key.includes('project_')) {
				renderPreview(prefs);
			}
		});
	});
	renderPreview(prefs);

	// Save
	if (saveBtn) {
		saveBtn.addEventListener('click', () => {
			savePrefs(prefs);
			hideSaveBar();
			toast('Notification preferences saved', 'success');
		});
	}

	// Reset
	if (resetBtn) {
		resetBtn.addEventListener('click', () => {
			prefs = { ...DEFAULTS };
			document.querySelectorAll('.setting-toggle').forEach(input => {
				const key = input.dataset.setting; input.checked = !!prefs[key];
			});
			renderPreview(prefs);
			showSaveBar();
			toast('Defaults restored — click Save to apply', 'info');
		});
	}
});

// Simple toast
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

