document.addEventListener('DOMContentLoaded', () => {
	let currentStep = 1;
	const totalSteps = 3;
	const form = document.querySelector('.setup-form');
	const nextBtn = document.getElementById('nextBtn');
	const backBtn = document.getElementById('backBtn');
	const progressTabs = document.querySelectorAll('.progress-tab');
	const progressSegments = document.querySelectorAll('.progress-segment');
	const progressLabels = document.querySelectorAll('.progress-label');

	const showStep = (step) => {
		// Hide all steps
		document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));

		// Show current step
		document.getElementById(`step-${step}`).classList.add('active');

		// Update progress tabs
		progressTabs.forEach(tab => {
			if (parseInt(tab.dataset.step) === step) {
				tab.classList.add('active');
			} else {
				tab.classList.remove('active');
			}
		});

		// Update progress segments and labels
		progressSegments.forEach(seg => {
			const s = parseInt(seg.dataset.step);
			if (s <= step) {
				seg.classList.add('active');
			} else {
				seg.classList.remove('active');
			}
		});

		progressLabels.forEach(lbl => {
			const s = parseInt(lbl.dataset.step);
			if (s === step) {
				lbl.classList.add('active');
			} else {
				lbl.classList.remove('active');
			}
		});

		// Optional title/subtitle if present
		const titles = [
			'Company Information',
			'Account Setup',
			'Verification'
		];
		const subtitles = [
			"Tell us about your company and services.",
			"Create your login credentials.",
			"Verify your identity with official documents."
		];

		const titleEl = document.querySelector('.setup-title');
		const subtitleEl = document.querySelector('.setup-subtitle');
		if (titleEl) titleEl.textContent = titles[step - 1] || '';
		if (subtitleEl) subtitleEl.textContent = subtitles[step - 1] || '';

		// Update button visibility
		if (step === 1) {
			backBtn.style.display = 'none';
			nextBtn.textContent = 'Next';
		} else if (step === totalSteps) {
			backBtn.style.display = 'block';
			nextBtn.textContent = 'Next';
		} else {
			backBtn.style.display = 'block';
			nextBtn.textContent = 'Next';
		}
	};

	const validateStep = (step) => {
		const stepElement = document.getElementById(`step-${step}`);
		const inputs = stepElement.querySelectorAll('input[required], select[required], textarea[required]');

		for (let input of inputs) {
			if (!input.value.trim()) {
				input.focus();
				alert('Please fill in all required fields.');
				return false;
			}
		}
		return true;
	};

	nextBtn.addEventListener('click', () => {
		if (validateStep(currentStep)) {
			if (currentStep < totalSteps) {
				currentStep++;
				showStep(currentStep);
			} else {
				form.submit();
			}
		}
	});

	backBtn.addEventListener('click', () => {
		if (currentStep > 1) {
			currentStep--;
			showStep(currentStep);
		}
	});

	// Tab clicks (only backward navigation)
	progressTabs.forEach(tab => {
		tab.addEventListener('click', () => {
			const step = parseInt(tab.dataset.step);
			if (step < currentStep) {
				currentStep = step;
				showStep(currentStep);
			}
		});
	});

	// Upload areas - make clickable & support drag/drop
	const setupUploadAreas = () => {
		const uploadAreas = document.querySelectorAll('.upload-area');
		uploadAreas.forEach((area) => {
			const inputId = area.dataset.input;
			const input = inputId ? document.getElementById(inputId) : null;
			if (!input) return;

			area.addEventListener('click', () => input.click());

			area.addEventListener('dragover', (e) => {
				e.preventDefault();
				area.style.borderColor = 'var(--accent-end)';
				area.style.background = 'rgba(245, 124, 0, 0.05)';
			});

			area.addEventListener('dragleave', () => {
				area.style.borderColor = 'var(--border-color)';
				area.style.background = 'var(--input-bg)';
			});

			area.addEventListener('drop', (e) => {
				e.preventDefault();
				area.style.borderColor = 'var(--border-color)';
				area.style.background = 'var(--input-bg)';

				if (e.dataTransfer.files.length) {
					input.files = e.dataTransfer.files;
				}
			});
		});
	};

	showStep(currentStep);
	setupUploadAreas();
});
