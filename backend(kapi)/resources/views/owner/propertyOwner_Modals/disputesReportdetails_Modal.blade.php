<!-- Report Details Modal -->
<div id="reportDetailsModal" class="report-details-modal hidden">
	<div class="modal-container">
		<div class="modal-header">
			<button type="button" class="back-btn" id="reportDetailsBackBtn">
				<i class="fi fi-rr-arrow-left"></i>
			</button>
			<h3 class="modal-title">Report Details</h3>
			<div style="width: 38px;"></div>
		</div>

		<div class="modal-panel">
			<div class="modal-body">
				<!-- Status Badge -->
				<div class="status-badge" id="detailsStatusBadge">
					<i class="fi fi-rr-info"></i>
					<span id="detailsStatus">Open</span>
				</div>

				<!-- Report Information -->
				<div class="details-section">
					<h4 class="section-heading">Report Information</h4>
					<div class="info-group">
						<div class="info-row">
							<span class="info-label">Report ID:</span>
							<span class="info-value" id="detailsReportId">#88</span>
						</div>
						<div class="info-row">
							<span class="info-label">Type:</span>
							<div class="type-badge" id="detailsType">
								<i class="fi fi-rr-hourglass"></i>
								<span>Delay</span>
							</div>
						</div>
						<div class="info-row">
							<span class="info-label">Filed On:</span>
							<span class="info-value" id="detailsFiledOn">Jan 22, 2026, 7:45 AM</span>
						</div>
					</div>
				</div>

				<!-- Project Context -->
				<div class="details-section">
					<h4 class="section-heading">Project Context</h4>
					<div class="context-group">
						<div class="context-item">
							<div class="context-icon">
								<i class="fi fi-rr-folder"></i>
							</div>
							<div class="context-content">
								<span class="context-label">Project</span>
								<span class="context-value" id="detailsProject">Project</span>
							</div>
						</div>
						<div class="context-item">
							<div class="context-icon">
								<i class="fi fi-rr-flag"></i>
							</div>
							<div class="context-content">
								<span class="context-label">Milestone Item</span>
								<span class="context-value" id="detailsMilestone">PHASE 1</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Description -->
				<div class="details-section">
					<h4 class="section-heading">Description</h4>
					<p class="description-text" id="detailsDescription">dugay</p>
				</div>

				<!-- Evidence Files -->
				<div class="details-section">
					<h4 class="section-heading">Evidence Files <span id="detailsFileCount">(0)</span></h4>
					<div class="evidence-grid" id="detailsEvidenceFiles"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Evidence Preview Modal -->
<div id="evidencePreviewModal" class="evidence-preview-modal hidden">
	<div class="preview-overlay"></div>
	<div class="preview-container">
		<button type="button" class="preview-close" id="previewCloseBtn">
			<i class="fi fi-rr-cross"></i>
		</button>
		<div class="preview-image-wrapper">
			<img id="previewImage" src="" alt="Evidence Preview" class="preview-image">
		</div>
		<div class="preview-footer">
			<span id="previewFileName" class="preview-filename"></span>
		</div>
	</div>
</div>
