<!-- Report History Modal -->
<div id="reportHistoryModal" class="rh-modal">
    <div class="rh-modal-overlay" id="reportHistoryModalOverlay"></div>
    <div class="rh-modal-container">
        <!-- Modal Header -->
        <div class="rh-modal-header">
            <button class="rh-back-btn" id="rhBackBtn" title="Back">
                <i class="fi fi-rr-angle-left"></i>
            </button>
            <div class="rh-header-content">
                <h2 class="rh-header-title">Report History</h2>
            </div>
            <button class="rh-refresh-btn" id="rhRefreshBtn" title="Refresh">
                <i class="fi fi-rr-refresh"></i>
            </button>
        </div>

        <!-- Modal Body: List View -->
        <div class="rh-modal-body" id="rhListView">
            <!-- Stats Bar -->
            <div class="rh-stats-bar" id="rhStatsBar"></div>
            <!-- Section Title -->
            <div class="rh-section-header" id="rhSectionHeader"></div>
            <!-- Dispute cards injected here by JS -->
            <div class="rh-entries-list" id="rhEntriesList"></div>
        </div>

        <!-- Modal Body: Detail View (hidden by default) -->
        <div class="rh-modal-body rh-detail-view" id="rhDetailView" style="display:none;">
            <div id="rhDetailContent">
                <!-- Detail content injected by JS -->
            </div>
        </div>
    </div>
</div>
