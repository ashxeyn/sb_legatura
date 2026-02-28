<!-- Contractor Report History Modal -->
<div id="reportHistoryModal" class="report-history-modal hidden">
    <div class="modal-overlay"></div>
    <div class="modal-container">
        <div class="modal-header">
            <button type="button" class="icon-btn" id="reportHistoryCloseBtn">
                <i class="fi fi-rr-cross"></i>
            </button>
            <h3 class="modal-title">Dispute & Report History</h3>
            <button type="button" class="icon-btn" id="reportHistoryRefreshBtn" title="Refresh">
                <i class="fi fi-rr-rotate-right"></i>
            </button>
        </div>
        <div class="modal-panel">
            <div class="modal-body">
                <div class="summary-grid" id="reportHistorySummary">
                    <div class="summary-card total">
                        <div class="summary-number" id="summaryTotal">0</div>
                        <div class="summary-label">Total Reports</div>
                    </div>
                    <div class="summary-card open">
                        <div class="summary-number" id="summaryOpen">0</div>
                        <div class="summary-label">Open</div>
                    </div>
                    <div class="summary-card resolved">
                        <div class="summary-number" id="summaryResolved">0</div>
                        <div class="summary-label">Resolved</div>
                    </div>
                </div>

                <div class="reports-section">
                    <div class="section-header"
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h4 class="section-title" style="margin: 0; font-size: 1rem; font-weight: 600; color: #1f2937;">
                            All Reports <span id="reportCount">(0)</span></h4>
                        <div class="filters" style="display: flex; gap: 0.5rem;">
                            <button class="filter-pill active" data-status="all">All</button>
                            <button class="filter-pill" data-status="open">Open</button>
                            <button class="filter-pill" data-status="resolved">Resolved</button>
                        </div>
                    </div>

                    <div id="reportHistoryList" class="report-list">
                        <!-- Populated by JS -->
                        <div class="empty-history" style="text-align: center; padding: 3rem 1rem; color: #6b7280;">
                            <i class="fi fi-rr-folder-open"
                                style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                            <p>No report history found for this item.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>