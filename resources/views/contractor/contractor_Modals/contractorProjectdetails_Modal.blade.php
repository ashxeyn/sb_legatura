{{-- Project Details Modal --}}
{{-- Pre-compute project display data from PHP for modal population --}}
@php
    $projectDetailsMap = [];
    if (isset($projects) && count($projects) > 0) {
        foreach ($projects as $pr) {
            $p = is_array($pr) ? (object) $pr : $pr;
            $pid = $p->project_id ?? null;
            if (!$pid)
                continue;

            // Owner info
            $ownerInfo = null;
            if (isset($p->owner_info)) {
                $ownerInfo = is_array($p->owner_info) ? (object) $p->owner_info : $p->owner_info;
            }
            $ownerName = '';
            $ownerEmail = '';
            $ownerPhone = '';
            if ($ownerInfo) {
                $ownerName = $ownerInfo->username ?? trim(($ownerInfo->first_name ?? '') . ' ' . ($ownerInfo->last_name ?? ''));
                $ownerEmail = $ownerInfo->email ?? '';
                $ownerPhone = $ownerInfo->phone_number ?? '';
            }
            if (!$ownerName) {
                $ownerName = $p->owner_name ?? '';
            }
            $ownerInitials = collect(explode(' ', $ownerName))->filter()->map(function ($w) {
                return strtoupper(substr($w, 0, 1));
            })->take(2)->join('');

            // Image
            $imageSrc = '';
            if (!empty($p->files)) {
                $files = $p->files;
                if (is_array($files) && count($files) > 0) {
                    $first = $files[0];
                } elseif (is_object($files) && method_exists($files, 'first')) {
                    $first = $files->first();
                } else {
                    $first = null;
                }
                if ($first) {
                    $fPath = is_string($first) ? $first : (is_array($first) ? ($first['file_path'] ?? '') : ($first->file_path ?? ''));
                    if ($fPath)
                        $imageSrc = asset('storage/' . ltrim($fPath, '/'));
                }
            }
            if (!$imageSrc && !empty($p->image_path)) {
                $imageSrc = asset('storage/' . ltrim($p->image_path, '/'));
            }
            if (!$imageSrc && !empty($p->project_image)) {
                $imageSrc = $p->project_image;
            }

            // Budget
            $budgetDisplay = 'Not specified';
            if (!empty($p->budget_range_min) && !empty($p->budget_range_max)) {
                $budgetDisplay = '₱' . number_format($p->budget_range_min) . ' - ₱' . number_format($p->budget_range_max);
            }

            // Status
            $displayStatus = $p->display_status ?? ($p->project_status ?? '');
            $statusText = $displayStatus ? ucfirst(str_replace('_', ' ', $displayStatus)) : '';
            if ($displayStatus === 'waiting_milestone_setup')
                $statusText = 'Needs Setup';
            elseif ($displayStatus === 'waiting_for_approval')
                $statusText = 'Waiting for Approval';

            // Milestones
            $milestones = [];
            if (isset($p->milestones)) {
                $milestones = is_array($p->milestones) ? $p->milestones : (is_object($p->milestones) && method_exists($p->milestones, 'toArray') ? $p->milestones->toArray() : []);
            }
            // Count milestone ITEMS (individual tasks), not milestone plan records
            $totalMilestoneItems = 0;
            $completedMilestoneItems = 0;
            $totalCost = 0;
            foreach ($milestones as $m) {
                $m = is_array($m) ? (object) $m : $m;
                $items = [];
                if (isset($m->items)) {
                    $items = is_array($m->items) ? $m->items : (is_object($m->items) && method_exists($m->items, 'toArray') ? $m->items->toArray() : []);
                }
                foreach ($items as $item) {
                    $item = is_array($item) ? (object) $item : $item;
                    $totalMilestoneItems++;
                    $itemStatus = $item->item_status ?? '';
                    if ($itemStatus === 'completed') {
                        $completedMilestoneItems++;
                    }
                    $totalCost += floatval($item->milestone_item_cost ?? 0);
                }
            }
            $progress = $totalMilestoneItems > 0 ? round(($completedMilestoneItems / $totalMilestoneItems) * 100) : 0;
            $totalCostDisplay = '₱' . number_format($totalCost, 2);

            // Project posted date
            $postedDate = $p->created_at ?? '';
            if ($postedDate) {
                try {
                    $postedDate = \Carbon\Carbon::parse($postedDate)->format('F j, Y');
                } catch (\Exception $e) {
                    // keep raw value
                }
            }

            $projectDetailsMap[$pid] = [
                'id' => $pid,
                'title' => $p->project_title ?? '—',
                'description' => $p->project_description ?? '',
                'location' => $p->project_location ?? '—',
                'image' => $imageSrc,
                'type' => $p->type_name ?? ($p->property_type ?? ''),
                'lotSize' => !empty($p->lot_size) ? $p->lot_size . ' sqm' : 'Not specified',
                'floorArea' => !empty($p->floor_area) ? $p->floor_area . ' sqm' : 'Not specified',
                'budget' => $budgetDisplay,
                'status' => $displayStatus,
                'statusText' => $statusText,
                'progress' => $progress,
                'owner' => [
                    'name' => $ownerName ?: '—',
                    'initials' => $ownerInitials ?: '—',
                    'email' => $ownerEmail ?: 'Not provided',
                    'phone' => $ownerPhone ?: 'Not provided',
                ],
                'postedDate' => $postedDate ?: 'Not specified',
                'milestones' => [
                    'total' => $totalMilestoneItems,
                    'completed' => $completedMilestoneItems,
                    'totalCost' => $totalCostDisplay,
                ],
            ];
        }
    }
@endphp

<!-- Project Details Modal -->
<div id="projectDetailsModal" class="project-details-modal">
    <div class="modal-overlay" id="projectModalOverlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="modal-header-content">
                <h2 class="modal-title">
                    <i class="fi fi-rr-file-document"></i>
                    <span>Project Details</span>
                </h2>
                <button class="modal-close-btn" id="closeProjectModalBtn" aria-label="Close modal">
                    <i class="fi fi-rr-cross"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Project Title and Location -->
            <div class="project-header-section">
                <h3 class="project-title-modal" id="modalProjectTitle"></h3>
                <div class="project-location-modal" id="modalProjectLocation">
                    <i class="fi fi-rr-marker"></i>
                    <span id="modalLocationText"></span>
                </div>
            </div>

            <!-- Project Image -->
            <div class="project-image-section">
                <img id="modalProjectImage" src="" alt="Project image" class="project-image-modal">
            </div>

            <!-- DESCRIPTION Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-document"></i>
                    <h4 class="section-title">DESCRIPTION</h4>
                </div>
                <div class="section-content">
                    <p class="project-description-modal" id="modalProjectDescription"></p>
                </div>
            </div>

            <!-- SPECIFICATIONS Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-settings"></i>
                    <h4 class="section-title">SPECIFICATIONS</h4>
                </div>
                <div class="section-content">
                    <div class="specifications-grid" id="modalSpecifications">
                        <!-- Specifications will be populated dynamically -->
                    </div>
                </div>
            </div>

            <!-- Lot Size and Floor Area Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-ruler-combined"></i>
                    <h4 class="section-title">LOT SIZE & FLOOR AREA</h4>
                </div>
                <div class="section-content">
                    <div class="measurements-grid">
                        <div class="measurement-item">
                            <div class="measurement-icon">
                                <i class="fi fi-rr-square"></i>
                            </div>
                            <div class="measurement-content">
                                <span class="measurement-label">Lot Size</span>
                                <span class="measurement-value" id="modalLotSize"></span>
                            </div>
                        </div>
                        <div class="measurement-item">
                            <div class="measurement-icon">
                                <i class="fi fi-rr-home"></i>
                            </div>
                            <div class="measurement-content">
                                <span class="measurement-label">Floor Area</span>
                                <span class="measurement-value" id="modalFloorArea"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ORIGINAL BUDGET RANGE Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-money"></i>
                    <h4 class="section-title">ORIGINAL BUDGET RANGE</h4>
                </div>
                <div class="section-content">
                    <div class="budget-display">
                        <div class="budget-icon">
                            <i class="fi fi-rr-money"></i>
                        </div>
                        <div class="budget-content">
                            <span class="budget-label">Budget Range</span>
                            <span class="budget-value" id="modalBudgetRange"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROPERTY OWNER Section -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-user"></i>
                    <h4 class="section-title">PROPERTY OWNER</h4>
                </div>
                <div class="section-content">
                    <div class="contractor-card">
                        <div class="contractor-header">
                            <div class="contractor-avatar-modal" id="modalOwnerAvatar">
                                <!-- Avatar initials -->
                            </div>
                            <div class="contractor-info">
                                <h5 class="contractor-name-modal" id="modalOwnerName"></h5>
                                <p class="contractor-role-modal" id="modalOwnerRole">Property Owner</p>
                            </div>
                        </div>
                        <div class="contractor-details">
                            <div class="detail-row">
                                <i class="fi fi-rr-envelope"></i>
                                <span id="modalOwnerEmail"></span>
                            </div>
                            <div class="detail-row">
                                <i class="fi fi-rr-phone-call"></i>
                                <span id="modalOwnerPhone"></span>
                            </div>
                            <div class="detail-row">
                                <i class="fi fi-rr-calendar"></i>
                                <span>Project Posted: <span id="modalProjectPosted"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Status and Progress -->
            <div class="details-section">
                <div class="section-header-modal">
                    <i class="fi fi-rr-chart-line-up"></i>
                    <h4 class="section-title">PROJECT STATUS</h4>
                </div>
                <div class="section-content">
                    <div class="status-progress-container">
                        <div class="status-badge-modal" id="modalStatusBadge">
                            <span class="status-dot-modal"></span>
                            <span class="status-text-modal" id="modalStatusText"></span>
                        </div>
                        <div class="progress-section-modal">
                            <div class="progress-header">
                                <span class="progress-label">Overall Progress</span>
                                <span class="progress-percentage-modal" id="modalProgressPercentage"></span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-modal" id="modalProgressBar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check Milestone Setup Section -->
            <div class="details-section" id="milestoneSection">
                <div class="section-header-modal milestone-header">
                    <div class="milestone-header-left">
                        <i class="fi fi-rr-folder-check"></i>
                        <h4 class="section-title">Check Milestone Setup</h4>
                    </div>
                    <div class="milestone-header-right">
                        <span class="milestone-indicator" id="modalMilestoneCount">0</span>
                        <i class="fi fi-rr-angle-right"></i>
                    </div>
                </div>
                <div class="section-content">
                    <p class="milestone-description">
                        View the complete milestone timeline, payment breakdown, and project duration for this project.
                    </p>
                    <div class="milestone-metrics-grid">
                        <div class="milestone-metric">
                            <span class="metric-label">Total Milestones</span>
                            <span class="metric-value" id="modalTotalMilestones">0</span>
                        </div>
                        <div class="milestone-metric-divider"></div>
                        <div class="milestone-metric">
                            <span class="metric-label">Completed</span>
                            <span class="metric-value" id="modalCompletedMilestones">0</span>
                        </div>
                        <div class="milestone-metric-divider"></div>
                        <div class="milestone-metric">
                            <span class="metric-label">Total Cost</span>
                            <span class="metric-value" id="modalTotalCost">₱0</span>
                        </div>
                    </div>
                    <button class="milestone-review-btn" id="reviewMilestoneBtn">
                        <i class="fi fi-rr-arrow-right"></i>
                        <span>Tap to check milestone setup</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button class="btn-secondary" id="closeModalBtn">
                <i class="fi fi-rr-cross"></i>
                <span>Close</span>
            </button>
        </div>
    </div>
</div>

{{-- Expose pre-computed project details data from PHP for the modal JS --}}
<script>
    window.projectDetailsData = @json($projectDetailsMap);
</script>