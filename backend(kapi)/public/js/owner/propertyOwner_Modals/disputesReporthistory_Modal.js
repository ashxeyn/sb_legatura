// Report History Modal (frontend-only)

document.addEventListener('DOMContentLoaded', () => {
	const modal = document.getElementById('reportHistoryModal');
	const overlay = modal?.querySelector('.modal-overlay');
	const closeBtn = document.getElementById('reportHistoryCloseBtn');
	const refreshBtn = document.getElementById('reportHistoryRefreshBtn');
	const summaryTotal = document.getElementById('summaryTotal');
	const summaryOpen = document.getElementById('summaryOpen');
	const summaryResolved = document.getElementById('summaryResolved');
	const reportCount = document.getElementById('reportCount');
	const reportList = document.getElementById('reportHistoryList');
	const filterPills = document.querySelectorAll('.report-history-modal .filter-pill');

	const sampleReports = [
		{
			id: 88,
			title: 'Delay',
			date: 'Jan 22, 2026',
			description: 'dugay',
			project: 'Project',
			milestone: 'PHASE 1',
			status: 'open',
			icon: '⏱',
			color: '#f59e0b'
		},
		{
			id: 87,
			title: 'Payment',
			date: 'Dec 19, 2025',
			description: 'Di nagbabayad',
			project: 'Project',
			milestone: 'PHASE 1',
			status: 'resolved',
			icon: '$',
			color: '#10b981'
		},
		{
			id: 86,
			title: 'Quality',
			date: 'Nov 28, 2025',
			description: 'Finish issues on ceiling',
			project: 'Project',
			milestone: 'PHASE 2',
			status: 'open',
			icon: '✓',
			color: '#0ea5e9'
		},
		{
			id: 85,
			title: 'Delay',
			date: 'Oct 14, 2025',
			description: 'Materials arrived late',
			project: 'Project',
			milestone: 'PHASE 1',
			status: 'resolved',
			icon: '⏱',
			color: '#f59e0b'
		},
		{
			id: 84,
			title: 'Payment',
			date: 'Sep 02, 2025',
			description: 'Partial payment disputed',
			project: 'Project',
			milestone: 'PHASE 1',
			status: 'resolved',
			icon: '$',
			color: '#10b981'
		}
	];

	let activeFilter = 'all';

	const renderSummary = () => {
		const total = sampleReports.length;
		const open = sampleReports.filter(r => r.status === 'open').length;
		const resolved = sampleReports.filter(r => r.status === 'resolved').length;
		if (summaryTotal) summaryTotal.textContent = total;
		if (summaryOpen) summaryOpen.textContent = open;
		if (summaryResolved) summaryResolved.textContent = resolved;
	};

	const renderList = () => {
		if (!reportList) return;
		reportList.innerHTML = '';
		const filtered = activeFilter === 'all' ? sampleReports : sampleReports.filter(r => r.status === activeFilter);
		if (reportCount) reportCount.textContent = `(${filtered.length})`;

		filtered.forEach(report => {
			const card = document.createElement('div');
			card.className = 'report-card';
			card.innerHTML = `
				<div class="report-icon" style="background: ${report.color}1a; color: ${report.color}; border: 1px solid ${report.color}33;">${report.icon}</div>
				<div class="report-content">
					<div class="report-header-row" style="display:flex;align-items:center;justify-content:space-between;gap:0.4rem;">
						<div>
							<h5 class="report-title">${report.title}</h5>
							<div class="report-date">${report.date}</div>
						</div>
						<span class="status-pill ${report.status === 'open' ? 'status-open' : 'status-resolved'}">${report.status === 'open' ? 'Open' : 'Resolved'}</span>
					</div>
					<p class="report-desc">${report.description}</p>
					<div class="report-meta">
						<span><i class="fi fi-rr-folder"></i> ${report.project}</span>
						<span><i class="fi fi-rr-flag"></i> ${report.milestone}</span>
					</div>
					<div class="report-footer">
						<span>ID: #${report.id}</span>
					<a class="view-link" onclick="openReportDetails({id: ${report.id}, title: '${report.title}', date: '${report.date}', description: '${report.description}', project: '${report.project}', milestone: '${report.milestone}', status: '${report.status}'});">View Details <i class="fi fi-rr-angle-small-right"></i></a>
					</div>
				</div>
			`;
			reportList.appendChild(card);
		});
	};

	const openModal = () => {
		if (modal) {
			modal.classList.remove('hidden');
			document.body.style.overflow = 'hidden';
			renderSummary();
			renderList();
		}
	};

	const closeModal = () => {
		if (modal) {
			modal.classList.add('hidden');
			document.body.style.overflow = '';
		}
	};

	// Expose global open function
	window.openReportHistoryModal = openModal;

	// Event wiring
	if (overlay) overlay.addEventListener('click', closeModal);
	if (closeBtn) closeBtn.addEventListener('click', closeModal);
	if (refreshBtn) refreshBtn.addEventListener('click', () => {
		refreshBtn.classList.add('spinning');
		refreshBtn.disabled = true;
		
		// Simulate API call to refresh data
		setTimeout(() => {
			// Shuffle the reports array to show refresh
			sampleReports.sort(() => Math.random() - 0.5);
			
			// Update timestamps on some reports to show they're fresh
			const now = new Date();
			const timeOptions = { month: 'short', day: 'numeric', year: 'numeric' };
			sampleReports[0].date = 'Just now';
			sampleReports[1].date = now.toLocaleDateString('en-US', timeOptions);
			
			// Re-render with refreshed data
			renderSummary();
			renderList();
			
			// Stop spinning
			refreshBtn.classList.remove('spinning');
			refreshBtn.disabled = false;
		}, 600);
	});

	filterPills.forEach(pill => {
		pill.addEventListener('click', () => {
			filterPills.forEach(p => p.classList.remove('active'));
			pill.classList.add('active');
			activeFilter = pill.getAttribute('data-status');
			renderList();
		});
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
			closeModal();
		}
	});
});
