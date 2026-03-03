@php
    $projectId = $project->project_id ?? $project->id;
    $modalId = 'projectPostDetailsModal-' . $projectId;
    
    // Owner Info
    $ownerName = $project->owner_name ?? 'Property Owner';
    $ownerInitials = collect(explode(' ', $ownerName))->filter()->map(function($w){ return strtoupper(substr($w,0,1)); })->take(2)->join('');
    $profilePic = $project->owner_profile_pic ?? null;

    // Date
    $postedDate = isset($project->created_at) ? \Carbon\Carbon::parse($project->created_at)->format('M d, Y') : '—';
    $deadline = isset($project->bidding_deadline) || isset($project->bidding_due) ? \Carbon\Carbon::parse($project->bidding_deadline ?? $project->bidding_due)->format('M d, Y') : '—';

    // Money
    $minBudget = number_format($project->budget_range_min ?? 0);
    $maxBudget = number_format($project->budget_range_max ?? 0);

    // Files Logic
    $files = $project->files ?? [];
    if (!is_iterable($files)) {
        $files = [];
    }

    $designImages = [];
    $importantDocs = [];

    // Helper to check file type (inline logic for Blade)
    foreach($files as $file) {
        $path = is_string($file) ? $file : (is_array($file) ? ($file['file_path'] ?? '') : ($file->file_path ?? ''));
        $type = is_object($file) ? ($file->file_type ?? '') : (is_array($file) ? ($file['file_type'] ?? '') : '');
        $lowerType = strtolower($type);
        $lowerPath = strtolower($path);
        
        // Critical docs check
        $isCritical = false;
        // Check for exact matches first
        if ($lowerType === 'title' || $lowerType === 'building permit') $isCritical = true;
        // Check for pattern matches
        elseif (preg_match('/building.?permit|title_of_land|title-of-land|land.?title/i', $lowerType)) $isCritical = true;
        elseif (str_contains($lowerPath, 'building') && str_contains($lowerPath, 'permit')) $isCritical = true;
        elseif (str_contains($lowerPath, 'title') && str_contains($lowerPath, 'land')) $isCritical = true;
        elseif (str_contains($lowerPath, 'project_files/titles/')) $isCritical = true;

        $fullUrl = asset('storage/' . ltrim($path, '/'));

        if ($isCritical) {
            $importantDocs[] = ['url' => $fullUrl, 'type' => $type];
        } else {
            // Assume rest are images/designs
            $designImages[] = ['url' => $fullUrl, 'type' => $type];
        }
    }

    $imgCount = count($designImages);
    $gridClass = 'grid-1-image';
    if ($imgCount == 2) $gridClass = 'grid-2-images';
    elseif ($imgCount == 3) $gridClass = 'grid-3-images';
    elseif ($imgCount >= 4) $gridClass = 'grid-4-images';
@endphp

<div id="{{ $modalId }}" class="project-details-modal hidden">
    <div class="modal-overlay" onclick="closeProjectModal('{{ $modalId }}')"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <button class="modal-back-btn" onclick="closeProjectModal('{{ $modalId }}')" aria-label="Go back">
                <i class="fi fi-rr-arrow-left"></i>
            </button>
            <h2 class="modal-title">Project Post</h2>
            <button class="modal-close-btn" onclick="closeProjectModal('{{ $modalId }}')" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="modal-body">
            <!-- Owner Info -->
            <div class="details-owner-section">
                <div class="owner-avatar-large">
                    @if($profilePic)
                        <img src="{{ asset('storage/' . $profilePic) }}" alt="{{ $ownerName }}" class="owner-image">
                    @else
                        <span class="owner-initials-large">{{ $ownerInitials }}</span>
                    @endif
                </div>
                <div class="owner-text-info">
                    <h3 class="owner-name-large">{{ $ownerName }}</h3>
                    <p class="posted-date-large">{{ $postedDate }}</p>
                </div>
            </div>

            <!-- Project Title & Description -->
            <div class="details-text-section">
                <h2 class="details-project-title">{{ $project->project_title ?? 'Untitled' }}</h2>
                <p class="details-project-description">{{ $project->project_description ?? 'No description.' }}</p>
            </div>

            <!-- Key Details Grid -->
            <div class="details-grid-section">
                <h3 class="section-subtitle">Project Details</h3>
                
                <!-- Type Badge -->
                <div class="details-type-badge">
                    <i class="fi fi-rr-briefcase"></i>
                    <span>{{ $project->type_name ?? $project->property_type ?? 'General' }}</span>
                </div>

                <div class="details-info-grid">
                    <!-- Location -->
                    <div class="detail-row">
                        <div class="detail-icon"><i class="fi fi-rr-marker"></i></div>
                        <div class="detail-content">
                            <span class="detail-label">Location</span>
                            <span class="detail-value">{{ $project->project_location ?? '—' }}</span>
                        </div>
                    </div>

                    <!-- Budget -->
                    <div class="detail-row">
                        <div class="detail-icon"><i class="fi fi-rr-dollar"></i></div>
                        <div class="detail-content">
                            <span class="detail-label">Budget Range</span>
                            <span class="detail-value">₱{{ $minBudget }} - ₱{{ $maxBudget }}</span>
                        </div>
                    </div>

                    <!-- Lot Size -->
                    @if(!empty($project->lot_size))
                    <div class="detail-row">
                        <div class="detail-icon"><i class="fi fi-rr-ruler-combined"></i></div>
                        <div class="detail-content">
                            <span class="detail-label">Lot Size</span>
                            <span class="detail-value">{{ $project->lot_size }} sqm</span>
                        </div>
                    </div>
                    @endif

                    <!-- Floor Area -->
                    @if(!empty($project->floor_area))
                    <div class="detail-row">
                        <div class="detail-icon"><i class="fi fi-rr-home"></i></div>
                        <div class="detail-content">
                            <span class="detail-label">Floor Area</span>
                            <span class="detail-value">{{ $project->floor_area }} sqm</span>
                        </div>
                    </div>
                    @endif

                    <!-- Deadline -->
                    <div class="detail-row">
                        <div class="detail-icon"><i class="fi fi-rr-calendar-clock"></i></div>
                        <div class="detail-content">
                            <span class="detail-label">Bidding Deadline</span>
                            <span class="detail-value">{{ $deadline }}</span>
                        </div>
                    </div>

                    <!-- Bids -->
                    <div class="detail-row">
                        <div class="detail-icon"><i class="fi fi-rr-gavel"></i></div>
                        <div class="detail-content">
                            <span class="detail-label">Bids Received</span>
                            <span class="detail-value">{{ $project->bids_count ?? 0 }} bids</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Design Images Section -->
            @if(count($designImages) > 0)
            <div class="details-images-section">
                <h3 class="section-subtitle">Design Images</h3>
                <div class="images-grid {{ $gridClass }}">
                    @foreach(array_slice($designImages, 0, 4) as $index => $img)
                        <div class="img-item" onclick="openDesignViewer('{{ $modalId }}', {{ $index }})">
                            <img src="{{ $img['url'] }}" alt="Project Image" onerror="this.style.display='none'">
                            @if($index === 3 && $imgCount > 4)
                                <div class="more-overlay">+{{ $imgCount - 4 }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            @endif

            <!-- Important Documents Section -->
            @if(count($importantDocs) > 0)
            <div class="details-documents-section">
                <h3 class="section-subtitle">Important Documents</h3>
                <p class="doc-notice">
                    <i class="fi fi-rr-lock"></i>
                    These documents are view-only and protected. Click to view.
                </p>
                <div class="documents-grid">
                    @foreach($importantDocs as $dIdx => $doc)
                        <div class="document-card" onclick="openDocViewer('{{ $modalId }}', {{ $dIdx }})">
                            <img src="{{ $doc['url'] }}" class="document-thumbnail" alt="Document">
                            <div class="document-watermark"></div>
                            <div class="document-eye-icon"><i class="fi fi-rr-eye"></i></div>
                            <div class="document-label-overlay">
                                <i class="fi fi-rr-lock"></i>
                                <span>{{ $doc['type'] ?: 'Document' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @endif
        </div>

        <!-- Sticky Footer Action -->
        <div class="modal-footer sticky-footer">
            <button type="button" class="btn-action-primary apply-bid-trigger" 
                    data-project-id="{{ $projectId }}"
                    data-modal-id="{{ $modalId }}"
                    onclick="triggerApplyBidFromDetails('{{ $projectId }}', '{{ $modalId }}')">
                <i class="fi fi-rr-hand-holding-usd"></i>
                <span>Place Bid</span>
            </button>
        </div>
    </div>
</div>

{{-- ======== FULLSCREEN VIEWERS (outside modal to avoid CSS transform containment) ======== --}}

@if(count($designImages) > 0)
<div id="designViewer-{{ $modalId }}" class="fullscreen-viewer design-viewer hidden">
    <div class="viewer-toolbar">
        <button class="viewer-back-btn" onclick="closeDesignViewer('{{ $modalId }}')" aria-label="Go back">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>
        <span class="viewer-counter-text"><span id="designCounter-{{ $modalId }}">1</span> / {{ $imgCount }}</span>
        <button class="viewer-close-btn" onclick="closeDesignViewer('{{ $modalId }}')" aria-label="Close">
            <i class="fi fi-rr-cross"></i>
        </button>
    </div>
    <div class="viewer-body">
        <div class="viewer-slides" id="designSlides-{{ $modalId }}">
            @foreach($designImages as $i => $img)
                <div class="viewer-slide @if($i > 0) hidden @endif" data-index="{{ $i }}">
                    <img src="{{ $img['url'] }}" alt="Design Image {{ $i+1 }}">
                </div>
            @endforeach
        </div>
        <button class="viewer-nav-btn nav-prev" onclick="navDesignViewer('{{ $modalId }}',-1)"><i class="fi fi-rr-angle-left"></i></button>
        <button class="viewer-nav-btn nav-next" onclick="navDesignViewer('{{ $modalId }}',1)"><i class="fi fi-rr-angle-right"></i></button>
    </div>
    <div class="viewer-dots" id="designDots-{{ $modalId }}">
        @foreach($designImages as $i => $img)
            <span class="viewer-dot @if($i === 0) active @endif" onclick="goToDesignSlide('{{ $modalId }}',{{ $i }})"></span>
        @endforeach
    </div>
</div>
@endif

@if(count($importantDocs) > 0)
<div id="docViewer-{{ $modalId }}" class="fullscreen-viewer doc-viewer hidden" oncontextmenu="return false;">
    <div class="viewer-toolbar">
        <button class="viewer-back-btn" onclick="closeDocViewer('{{ $modalId }}')" aria-label="Go back">
            <i class="fi fi-rr-arrow-left"></i>
            <span>Back</span>
        </button>
        <div class="doc-viewer-label">
            <i class="fi fi-rr-lock"></i>
            <span id="docLabel-{{ $modalId }}">Document</span>
        </div>
        <span class="viewer-counter-text"><span id="docCounter-{{ $modalId }}">1</span> / {{ count($importantDocs) }}</span>
    </div>
    <div class="doc-viewer-notice">View only &mdash; downloading is disabled</div>
    <div class="viewer-body">
        <div class="viewer-slides" id="docSlides-{{ $modalId }}">
            @foreach($importantDocs as $dIdx => $doc)
                <div class="viewer-slide @if($dIdx > 0) hidden @endif" data-index="{{ $dIdx }}" data-label="{{ $doc['type'] ?: 'Document' }}">
                    <div class="doc-image-container">
                        <img src="{{ $doc['url'] }}" alt="Document {{ $dIdx+1 }}" draggable="false" oncontextmenu="return false;">
                        <div class="doc-watermark-overlay"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <button class="viewer-nav-btn nav-prev" onclick="navDocViewer('{{ $modalId }}',-1)"><i class="fi fi-rr-angle-left"></i></button>
        <button class="viewer-nav-btn nav-next" onclick="navDocViewer('{{ $modalId }}',1)"><i class="fi fi-rr-angle-right"></i></button>
    </div>
    <div class="viewer-dots" id="docDots-{{ $modalId }}">
        @foreach($importantDocs as $dIdx => $doc)
            <span class="viewer-dot @if($dIdx === 0) active @endif" onclick="goToDocSlide('{{ $modalId }}',{{ $dIdx }})"></span>
        @endforeach
    </div>
</div>
@endif
