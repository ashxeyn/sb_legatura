// Report Details Modal

document.addEventListener('DOMContentLoaded', () => {
	const detailsModal = document.getElementById('reportDetailsModal');
	const detailsOverlay = detailsModal?.querySelector('.modal-overlay');
	const detailsBackBtn = document.getElementById('reportDetailsBackBtn');
	const previewModal = document.getElementById('evidencePreviewModal');
	const previewOverlay = previewModal?.querySelector('.preview-overlay');
	const previewCloseBtn = document.getElementById('previewCloseBtn');
	const previewImage = document.getElementById('previewImage');
	const previewFileName = document.getElementById('previewFileName');

	// Sample file data with preview images
	const fileData = {
		'Messenger_creation_a2814c2222c6509f...': {
			type: 'image',
			src: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300"%3E%3Crect fill="%23ddd" width="300" height="300"/%3E%3Ctext x="50%25" y="50%25" fill="%23999" text-anchor="middle" dy=".3em" font-size="24" font-family="Arial"%3EMessenger Screenshot%3C/text%3E%3C/svg%3E'
		},
		'project_photo_2025.jpg': {
			type: 'image',
			src: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300"%3E%3Crect fill="%238b5cf6" width="400" height="300"/%3E%3Ccircle cx="100" cy="100" r="40" fill="%23ddd"/%3E%3Crect x="50" y="150" width="300" height="100" fill="%23c084fc"/%3E%3Ctext x="50%25" y="50%25" fill="white" text-anchor="middle" dy=".3em" font-size="32" font-family="Arial" font-weight="bold"%3EProject Photo%3C/text%3E%3C/svg%3E'
		},
		'Invoice_2026_Jan.pdf': {
			type: 'file',
			icon: 'file-pdf'
		}
	};

	const openReportDetails = (report) => {
		if (!detailsModal) return;

		// Populate details
		document.getElementById('detailsReportId').textContent = `#${report.id}`;
		document.getElementById('detailsStatus').textContent = report.status === 'open' ? 'Open' : 'Resolved';
		document.getElementById('detailsType').innerHTML = `<i class="fi fi-rr-${
			report.title === 'Delay' ? 'hourglass' :
			report.title === 'Payment' ? 'dollar' :
			report.title === 'Quality' ? 'info' : 'menu-dots'
		}"></i><span>${report.title}</span>`;
		
		// Update status badge styling
		const statusBadge = detailsModal.querySelector('.status-badge');
		statusBadge.classList.remove('open', 'resolved');
		statusBadge.classList.add(report.status);

		document.getElementById('detailsFiledOn').textContent = report.date + ', 7:45 AM';
		document.getElementById('detailsProject').textContent = report.project;
		document.getElementById('detailsMilestone').textContent = report.milestone;
		document.getElementById('detailsDescription').textContent = report.description;

		// Add sample evidence files
		const evidenceGrid = document.getElementById('detailsEvidenceFiles');
		evidenceGrid.innerHTML = '';
		const sampleFiles = [
			'Messenger_creation_a2814c2222c6509f...',
			'project_photo_2025.jpg',
			'Invoice_2026_Jan.pdf'
		];
		const fileCount = Math.floor(Math.random() * 3) + 1;
		document.getElementById('detailsFileCount').textContent = `(${fileCount})`;

		for (let i = 0; i < fileCount; i++) {
			const fileName = sampleFiles[i];
			const fileEl = document.createElement('div');
			fileEl.className = 'evidence-file';
			fileEl.style.cursor = 'pointer';
			fileEl.innerHTML = `
				<div class="evidence-file-icon">
					<i class="fi fi-rr-file"></i>
				</div>
				<div class="evidence-file-name">${fileName}</div>
			`;
			
			fileEl.addEventListener('click', () => openEvidencePreview(fileName));
			evidenceGrid.appendChild(fileEl);
		}

		// Show modal
		detailsModal.classList.remove('hidden');
		document.body.style.overflow = 'hidden';
	};

	const closeReportDetails = () => {
		if (detailsModal) {
			detailsModal.classList.add('hidden');
			document.body.style.overflow = '';
		}
	};

	const openEvidencePreview = (fileName) => {
		if (!previewModal) return;

		const file = fileData[fileName];
		if (!file) return;

		previewFileName.textContent = fileName;

		if (file.type === 'image') {
			previewImage.src = file.src;
			previewImage.style.display = 'block';
		} else {
			previewImage.style.display = 'none';
		}

		previewModal.classList.remove('hidden');
		document.body.style.overflow = 'hidden';
	};

	const closeEvidencePreview = () => {
		if (previewModal) {
			previewModal.classList.add('hidden');
			document.body.style.overflow = '';
		}
	};

	// Expose globally for use in report history
	window.openReportDetails = openReportDetails;

	// Event handlers
	if (detailsOverlay) detailsOverlay.addEventListener('click', closeReportDetails);
	if (detailsBackBtn) detailsBackBtn.addEventListener('click', closeReportDetails);
	if (previewOverlay) previewOverlay.addEventListener('click', closeEvidencePreview);
	if (previewCloseBtn) previewCloseBtn.addEventListener('click', closeEvidencePreview);

	// Close on ESC
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') {
			if (previewModal && !previewModal.classList.contains('hidden')) {
				closeEvidencePreview();
			} else if (detailsModal && !detailsModal.classList.contains('hidden')) {
				closeReportDetails();
			}
		}
	});
});

