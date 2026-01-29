<!-- Report History Modal -->
<div id="reportHistoryModal" class="report-history-modal hidden">
	<div class="modal-overlay"></div>
	<div class="modal-container">
		<div class="modal-header">
			<button type="button" class="icon-btn" id="reportHistoryCloseBtn">
				<i class="fi fi-rr-cross"></i>
			</button>
			<h3 class="modal-title">Report History</h3>
			<button type="button" class="icon-btn" id="reportHistoryRefreshBtn" title="Refresh">
				<i class="fi fi-rr-rotate-right"></i>
			</button>
		</div>
		<div class="modal-panel">
			<div class="modal-body">
				<div class="summary-grid" id="reportHistorySummary">
					<div class="summary-card total">
						<div class="summary-number" id="summaryTotal">5</div>
						<div class="summary-label">Total Reports</div>
					</div>
					<div class="summary-card open">
						<div class="summary-number" id="summaryOpen">2</div>
						<div class="summary-label">Open</div>
					</div>
					<div class="summary-card resolved">
						<div class="summary-number" id="summaryResolved">2</div>
						<div class="summary-label">Resolved</div>
					</div>
				</div>

				<div class="reports-section">
					<div class="section-header">
						<h4 class="section-title">All Reports <span id="reportCount">(0)</span></h4>
						<div class="filters">
							<button class="filter-pill active" data-status="all">All</button>
							<button class="filter-pill" data-status="open">Open</button>
							<button class="filter-pill" data-status="resolved">Resolved</button>
						</div>
					</div>

					<div id="reportHistoryList" class="report-list"></div>
				</div>
			</div>
		</div>
	</div>
</div>
